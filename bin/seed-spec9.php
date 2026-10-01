<?php
/**
 * Seed the SPEC9 demo content.
 *
 * Run in two passes. Carbon Fields evaluates a container's `where` rule while
 * the container registers, early in the request, so on the first pass
 * `page_on_front` is still unset and the Front Page fields have nowhere to go.
 * Pass 1 creates the content; pass 2 writes the Front Page fields once the
 * option exists.
 *
 *   wp eval-file bin/seed-spec9.php 1
 *   wp eval-file bin/seed-spec9.php 2
 *   wp eval-file bin/seed-spec9.php reset   # remove everything this created
 *
 * Every object is looked up by slug or title before it is created, so
 * re-running a pass updates rather than duplicates. Run it once on a fresh
 * install; it is not meant to run on a site that already has content.
 *
 * @package Spec9
 */

defined( 'WP_CLI' ) || exit;

require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

/**
 * Media staged outside the web root by the build step.
 */
define( 'SPEC9_SEED_MEDIA_DIR', '/tmp/spec9-media' );

/* ------------------------------------------------------------------ output */

/**
 * Print one aligned line.
 *
 * @param string $label Left column.
 * @param string $value Right column.
 */
function spec9_seed_ok( $label, $value = 'ok' ) {
	WP_CLI::line( sprintf( '  %-26s %s', $label, $value ) );
}

/**
 * Print a section heading.
 *
 * @param string $title Heading text.
 */
function spec9_seed_head( $title ) {
	WP_CLI::line( '' );
	WP_CLI::line( $title );
}

/* ----------------------------------------------------------------- lookups */

/**
 * Find a post by slug, whatever its status.
 *
 * @param string $post_type Post type.
 * @param string $slug      Post slug.
 * @return int Post ID, or 0.
 */
function spec9_seed_find( $post_type, $slug ) {
	$found = get_posts(
		array(
			'post_type'        => $post_type,
			'name'             => $slug,
			'post_status'      => 'any',
			'posts_per_page'   => 1,
			'fields'           => 'ids',
			'suppress_filters' => false,
		)
	);

	return $found ? (int) $found[0] : 0;
}

/**
 * Create or update a post, keyed by slug.
 *
 * @param array $args Post fields; `post_name` is the lookup key.
 * @return int Post ID.
 */
function spec9_seed_store( $args ) {
	$existing = spec9_seed_find( $args['post_type'], $args['post_name'] );

	if ( $existing ) {
		$args['ID'] = $existing;
	}

	$id = wp_insert_post( $args, true );

	if ( is_wp_error( $id ) ) {
		WP_CLI::error( sprintf( 'Could not save %s "%s": %s', $args['post_type'], $args['post_name'], $id->get_error_message() ) );
	}

	update_post_meta( $id, '_spec9_seed', 1 );

	return (int) $id;
}

/* ------------------------------------------------------------------- media */

/**
 * Imported attachment IDs, keyed by a short name.
 *
 * The map is kept in an option so a later pass — or a re-run — reuses the
 * attachments that already exist instead of importing them again.
 *
 * @return array
 */
function spec9_seed_media_map() {
	$map = get_option( 'spec9_seed_media', array() );

	return is_array( $map ) ? $map : array();
}

/**
 * Import one staged file, or return the attachment already imported for it.
 *
 * @param string $key   Short name used by templates and later passes.
 * @param string $file  File name inside the staging directory.
 * @param string $title Attachment title.
 * @param string $alt   Alt text.
 * @return int Attachment ID, or 0.
 */
function spec9_seed_media( $key, $file, $title, $alt = '' ) {
	$map = spec9_seed_media_map();

	if ( ! empty( $map[ $key ] ) && get_post( (int) $map[ $key ] ) ) {
		return (int) $map[ $key ];
	}

	$source = SPEC9_SEED_MEDIA_DIR . '/' . $file;

	if ( ! file_exists( $source ) ) {
		WP_CLI::warning( "Missing staged file: $source" );

		return 0;
	}

	// media_handle_sideload() moves the file it is given, so hand it a copy.
	$tmp = wp_tempnam( $file );

	if ( ! copy( $source, $tmp ) ) {
		WP_CLI::warning( "Could not stage $source" );

		return 0;
	}

	$id = media_handle_sideload(
		array(
			'name'     => $file,
			'tmp_name' => $tmp,
		),
		0,
		$title
	);

	if ( is_wp_error( $id ) ) {
		@unlink( $tmp ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged -- Cleanup only.
		WP_CLI::warning( sprintf( 'Import failed for %s: %s', $file, $id->get_error_message() ) );

		return 0;
	}

	$id = (int) $id;

	update_post_meta( $id, '_spec9_seed', 1 );
	update_post_meta( $id, '_wp_attachment_image_alt', $alt );

	$map[ $key ] = $id;
	update_option( 'spec9_seed_media', $map, false );

	return $id;
}

/* ------------------------------------------------------------------ blocks */

/**
 * Build a core Gallery block from attachment IDs.
 *
 * The markup is what the block editor itself writes, so the gallery stays
 * editable and a re-save does not mark the post as broken.
 *
 * @param int[] $ids Attachment IDs, in display order.
 * @return string Block markup.
 */
function spec9_seed_gallery( $ids ) {
	$items = array();

	foreach ( $ids as $id ) {
		$url = wp_get_attachment_image_url( $id, 'large' );

		if ( ! $url ) {
			continue;
		}

		$alt = (string) get_post_meta( $id, '_wp_attachment_image_alt', true );

		$items[] = sprintf(
			"<!-- wp:image {\"id\":%1\$d,\"sizeSlug\":\"large\",\"linkDestination\":\"none\"} -->\n"
			. "<figure class=\"wp-block-image size-large\"><img src=\"%2\$s\" alt=\"%3\$s\" class=\"wp-image-%1\$d\"/></figure>\n"
			. '<!-- /wp:image -->',
			$id,
			esc_url( $url ),
			esc_attr( $alt )
		);
	}

	if ( ! $items ) {
		return '';
	}

	return "<!-- wp:gallery {\"linkTo\":\"none\"} -->\n"
		. "<figure class=\"wp-block-gallery has-nested-images columns-default is-cropped\">\n"
		. implode( "\n", $items )
		. "\n</figure>\n"
		. '<!-- /wp:gallery -->';
}

/**
 * The Home page hero, as block markup.
 *
 * @return string
 */
function spec9_seed_hero_blocks() {
	return <<<'HTML'
<!-- wp:heading {"level":1} -->
<h1 class="wp-block-heading">Commercial flooring contractors in Central Florida</h1>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>From Brevard County to Orlando, SPEC9 installs flooring for schools, hotels, apartments, government buildings and offices, on schedule and built to last.</p>
<!-- /wp:paragraph -->

<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button {"className":"is-style-primary"} -->
<div class="wp-block-button is-style-primary"><a class="wp-block-button__link wp-element-button" href="#quote">Get a Quote</a></div>
<!-- /wp:button -->

<!-- wp:button {"className":"is-style-ghost"} -->
<div class="wp-block-button is-style-ghost"><a class="wp-block-button__link wp-element-button" href="#services">Explore Services</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons -->
HTML;
}

/* -------------------------------------------------------------- pass one */

/**
 * Install the plugin prerequisites and clear the stock content.
 */
function spec9_seed_prereqs() {
	spec9_seed_head( 'Prerequisites' );

	if ( 'spec9' !== get_stylesheet() ) {
		WP_CLI::error( 'The active theme is ' . get_stylesheet() . ', not spec9.' );
	}
	spec9_seed_ok( 'theme', 'spec9' );

	if ( ! class_exists( 'WPCF7_ContactForm' ) ) {
		WP_CLI::error( 'Contact Form 7 is not active. Run: wp plugin install contact-form-7 --activate' );
	}
	spec9_seed_ok( 'contact form 7', defined( 'WPCF7_VERSION' ) ? WPCF7_VERSION : 'active' );

	foreach ( array( 'hello-world' => 'post', 'sample-page' => 'page' ) as $slug => $type ) {
		$id = spec9_seed_find( $type, $slug );

		if ( $id && ! get_post_meta( $id, '_spec9_seed', true ) ) {
			wp_delete_post( $id, true );
			spec9_seed_ok( 'deleted', "$type: $slug" );
		}
	}
}

/**
 * Site title and tagline.
 */
function spec9_seed_identity() {
	spec9_seed_head( 'Site identity' );

	update_option( 'blogname', 'SPEC9 Flooring' );
	update_option( 'blogdescription', 'Commercial flooring contractors serving Melbourne, Orlando and Central Florida since 1995.' );
	spec9_seed_ok( 'blogname', get_option( 'blogname' ) );
}

/**
 * Import every staged image and icon.
 *
 * @return array Attachment IDs keyed by short name.
 */
function spec9_seed_import_media() {
	spec9_seed_head( 'Media' );

	$files = array(
		'hero'           => array( 'hero.jpg', 'Commercial flooring installation', '' ),
		'carpet-tile'    => array( 'carpet-tile.jpg', 'Carpet tile installation', 'Carpet tile installation' ),
		'luxury-vinyl'   => array( 'luxury-vinyl.jpg', 'Luxury vinyl flooring', 'Luxury vinyl flooring' ),
		'resilient'      => array( 'resilient.jpg', 'Resilient flooring installation', 'Resilient flooring installation' ),
		'hotel-lobby'    => array( 'hotel-lobby.jpg', 'Hotel lobby flooring, Orlando', 'Hotel lobby flooring, Orlando' ),
		'lobby-corridor' => array( 'lobby-corridor.jpg', 'Lobby or corridor flooring', 'Lobby or corridor flooring' ),
		'cta'            => array( 'cta.jpg', 'Commercial flooring project', '' ),
		'tiles-pattern'  => array( 'tiles-pattern.jpg', 'Flooring tile pattern', '' ),
		'logo'           => array( 'spec9-logo.png', 'SPEC9 Flooring logo', 'SPEC9 Flooring' ),
		'logo-dark'      => array( 'spec9-logo-dark.png', 'SPEC9 Flooring logo (dark)', 'SPEC9 Flooring' ),
		'icon-education'   => array( 'icons/industry-education.svg', 'Education icon', '' ),
		'icon-government'  => array( 'icons/industry-government.svg', 'Government icon', '' ),
		'icon-hospitality' => array( 'icons/industry-hospitality.svg', 'Hospitality icon', '' ),
		'icon-multifamily' => array( 'icons/industry-multifamily.svg', 'Multifamily icon', '' ),
		'icon-commercial'  => array( 'icons/industry-commercial.svg', 'Commercial icon', '' ),
		'icon-renovation'  => array( 'icons/industry-renovation.svg', 'Renovation icon', '' ),
		'icon-demo'        => array( 'icons/industry-demo.svg', 'Demo icon', '' ),
	);

	$ids = array();

	foreach ( $files as $key => $spec ) {
		$ids[ $key ] = spec9_seed_media( $key, $spec[0], $spec[1], $spec[2] );
	}

	$imported = count( array_filter( $ids ) );
	spec9_seed_ok( 'attachments', "$imported of " . count( $files ) );

	return $ids;
}

/**
 * The Home page, its hero and the front-page options.
 *
 * @param array $media Attachment IDs.
 * @return int Home page ID.
 */
function spec9_seed_home( $media ) {
	spec9_seed_head( 'Home page' );

	$home_id = spec9_seed_store(
		array(
			'post_type'    => 'page',
			'post_name'    => 'home',
			'post_title'   => 'Home',
			'post_content' => spec9_seed_hero_blocks(),
			'post_status'  => 'publish',
		)
	);

	if ( ! empty( $media['hero'] ) ) {
		set_post_thumbnail( $home_id, $media['hero'] );
	}

	update_option( 'show_on_front', 'page' );
	update_option( 'page_on_front', $home_id );

	spec9_seed_ok( 'page_on_front', "$home_id (home)" );

	// Only now that the front page no longer points at it.
	$sample = spec9_seed_find( 'page', 'sample-page' );

	if ( $sample && ! get_post_meta( $sample, '_spec9_seed', true ) ) {
		wp_delete_post( $sample, true );
		spec9_seed_ok( 'deleted', 'page: sample-page' );
	}

	return $home_id;
}

/**
 * The six services, in the order the front page lists them.
 *
 * @param array $media Attachment IDs.
 * @return int[] Service post IDs.
 */
function spec9_seed_services( $media ) {
	spec9_seed_head( 'Services' );

	$services = array(
		array( 'carpet-carpet-tile', 'Carpet & carpet tile', 'Durable, quiet flooring for offices, schools and hotels.', 'carpet-tile', 'We install broadloom and carpet tile for offices, schools and hotels. Carpet tile is the usual choice for an occupied building: it goes down quickly, single tiles can be replaced without touching the rest, and most commercial lines carry the backing and warranties a project asks for.' ),
		array( 'luxury-vinyl', 'Luxury vinyl', 'Realistic wood and stone looks that stand up to traffic.', 'luxury-vinyl', 'Luxury vinyl gives you the look of wood or stone with a surface that stands up to trolleys, chairs and constant foot traffic. We lay plank and tile over a prepared subfloor and finish the edges and transitions to match.' ),
		array( 'resilient-sheet-flooring', 'Resilient & sheet flooring', 'Seamless, easy-to-clean surfaces for healthcare and education.', 'resilient', 'Sheet vinyl and other resilient surfaces give healthcare, education and food-service spaces a seamless floor that is easy to clean and hard to damage. Heat-welded seams are available where hygiene matters.' ),
		array( 'tile-stone', 'Tile & stone', 'Ceramic and porcelain for lobbies, baths and common areas.', 'carpet-tile', 'Ceramic and porcelain tile for lobbies, restrooms, corridors and common areas. We handle layout, cutting and grouting, and set the tile over a substrate prepared for it so the floor stays flat and quiet.' ),
		array( 'renovation', 'Renovation', 'Phased upgrades that keep your building open.', 'luxury-vinyl', 'Replacing a floor in a working building takes planning. We phase the work around your hours, protect what stays in place, and keep a clean route in and out so tenants and staff are not disrupted.' ),
		array( 'demo-prep', 'Demo & prep', 'Removal, moisture testing and subfloor preparation.', 'resilient', 'Every floor is only as good as what sits under it. We remove the old material, test the slab for moisture, then grind or patch as needed before the installation starts.' ),
	);

	$ids = array();
	$pos = 0;

	foreach ( $services as $service ) {
		list( $slug, $title, $excerpt, $image, $body ) = $service;

		++$pos;

		$id = spec9_seed_store(
			array(
				'post_type'    => 'service',
				'post_name'    => $slug,
				'post_title'   => $title,
				'post_excerpt' => $excerpt,
				'post_content' => $body,
				'post_status'  => 'publish',
				'menu_order'   => $pos,
			)
		);

		if ( ! empty( $media[ $image ] ) ) {
			set_post_thumbnail( $id, $media[ $image ] );
		}

		$ids[] = $id;
	}

	spec9_seed_ok( 'services', count( $ids ) . ' created' );
	spec9_seed_ok( 'order', 'menu_order 1-' . count( $ids ) );

	return $ids;
}

/**
 * The five featured projects, in bento order.
 *
 * @param array $media Attachment IDs.
 * @return int[] Project post IDs.
 */
function spec9_seed_projects( $media ) {
	spec9_seed_head( 'Projects' );

	$projects = array(
		array( 'hotel-lobby-orlando', 'Hotel lobby, Orlando', 'hotel-lobby', 'A 180-room hotel refurbishment completed floor by floor while the property stayed open. Carpet tile through the guest corridors, porcelain in the lobby and public restrooms, and luxury vinyl in the back-of-house areas.', array( 'hotel-lobby', 'lobby-corridor', 'carpet-tile' ) ),
		array( 'elementary-school-brevard-county', 'Elementary school, Brevard County', 'resilient', 'A summer-break replacement of resilient flooring across classrooms and corridors, finished before staff returned for the new term. Moisture testing and slab preparation were done the week the students left.', array( 'resilient', 'carpet-tile' ) ),
		array( 'apartment-community-melbourne', 'Apartment community, Melbourne', 'lobby-corridor', 'Common areas and unit turnovers across a 240-unit community. We worked around occupied units, matched the existing specification, and handled the stair and corridor work in phases.', array( 'lobby-corridor', 'luxury-vinyl', 'resilient' ) ),
		array( 'government-office-central-florida', 'Government office, Central Florida', 'carpet-tile', 'Carpet tile throughout an occupied county office building, installed in the evenings and over weekends so departments never lost a working day.', array( 'carpet-tile', 'resilient' ) ),
		array( 'office-renovation-orlando', 'Office renovation, Orlando', 'luxury-vinyl', 'A full-floor office renovation: demolition and removal of the old carpet, subfloor preparation, then luxury vinyl plank through the open plan and carpet tile in the meeting rooms.', array( 'luxury-vinyl', 'carpet-tile', 'lobby-corridor' ) ),
	);

	$ids = array();
	$pos = 0;

	foreach ( $projects as $project ) {
		list( $slug, $title, $image, $intro, $gallery ) = $project;

		++$pos;

		$gallery_ids = array();

		foreach ( $gallery as $key ) {
			if ( ! empty( $media[ $key ] ) ) {
				$gallery_ids[] = $media[ $key ];
			}
		}

		$content = '<p>' . $intro . '</p>' . "\n\n" . spec9_seed_gallery( $gallery_ids );

		$id = spec9_seed_store(
			array(
				'post_type'    => 'project',
				'post_name'    => $slug,
				'post_title'   => $title,
				'post_excerpt' => $intro,
				'post_content' => $content,
				'post_status'  => 'publish',
				'menu_order'   => $pos,
			)
		);

		if ( ! empty( $media[ $image ] ) ) {
			set_post_thumbnail( $id, $media[ $image ] );
		}

		$ids[] = $id;
	}

	spec9_seed_ok( 'projects', count( $ids ) . ' created' );

	return $ids;
}

/**
 * The Privacy Policy and Terms pages.
 *
 * @return array Page IDs keyed by role.
 */
function spec9_seed_legal_pages() {
	spec9_seed_head( 'Legal pages' );

	$privacy = spec9_seed_find( 'page', 'privacy-policy' );

	if ( $privacy ) {
		wp_update_post(
			array(
				'ID'          => $privacy,
				'post_status' => 'publish',
			)
		);
	} else {
		$privacy = spec9_seed_store(
			array(
				'post_type'    => 'page',
				'post_name'    => 'privacy-policy',
				'post_title'   => 'Privacy Policy',
				'post_content' => "<p>We collect only the information you send us through the quote form — your name, email, phone, company and project details — and use it to answer your enquiry. We do not sell or share it.</p>\n\n<p>Replace this page with your own policy before launch.</p>",
				'post_status'  => 'publish',
			)
		);
	}

	$terms = spec9_seed_store(
		array(
			'post_type'    => 'page',
			'post_name'    => 'terms',
			'post_title'   => 'Terms',
			'post_content' => "<p>Terms of use for this website.</p>\n\n<p>Replace this page with your own terms before launch.</p>",
			'post_status'  => 'publish',
		)
	);

	spec9_seed_ok( 'privacy policy', $privacy );
	spec9_seed_ok( 'terms', $terms );

	return array(
		'privacy' => (int) $privacy,
		'terms'   => (int) $terms,
	);
}

/**
 * The Contact Form 7 quote form.
 *
 * @return int Form ID.
 */
function spec9_seed_form() {
	spec9_seed_head( 'Contact form' );

	$existing = WPCF7_ContactForm::find( array( 'title' => 'Quote request' ) );
	$form     = $existing ? reset( $existing ) : WPCF7_ContactForm::get_template();

	$template = <<<'HTML'
<div class="f"><label for="qf-name">Full name <em>*</em></label>[text* your-name id:qf-name autocomplete:name placeholder "Jane Smith"]</div>
<div class="f"><label for="qf-email">Email <em>*</em></label>[email* your-email id:qf-email autocomplete:email placeholder "jane@company.com"]</div>
<div class="f"><label for="qf-phone">Phone</label>[tel your-phone id:qf-phone autocomplete:tel placeholder "(321) 555-0100"]</div>
<div class="f"><label for="qf-company">Company</label>[text your-company id:qf-company autocomplete:organization placeholder "ABC Builders"]</div>
<div class="f wide"><label for="qf-service">What do you need?</label>[select your-service id:qf-service first_as_label "Select a service…" "Carpet & carpet tile" "Luxury vinyl" "Resilient & sheet flooring" "Tile & stone" "Renovation" "Demo & prep" "General enquiry"]</div>
<div class="f wide"><label for="qf-message">Project details <em>*</em></label>[textarea* your-message id:qf-message x4 placeholder "e.g. 2nd floor offices, ~5,000 sq ft carpet tile — we need a quote by March."]</div>
<div class="actions">[submit class:btn class:p "Request a quote"]<p class="note">No obligation — we usually reply within one business day.</p></div>
HTML;

	$body = <<<'TEXT'
From: [your-name] <[your-email]>
Phone: [your-phone]
Company: [your-company]
Service: [your-service]

[your-message]


--
Sent from the SPEC9 Flooring website.
TEXT;

	$mail = array(
		'subject'            => 'Quote request from [your-name]',
		'sender'             => 'SPEC9 Flooring <wordpress@spec9.local>',
		'recipient'          => 'info@spec9.com',
		'body'               => $body,
		'additional_headers' => 'Reply-To: [your-email]',
		'attachments'        => '',
		'use_html'           => 0,
		'exclude_blank'      => 1,
	);

	// set_properties() merges at the top level only, so a partial "messages"
	// array would drop every other message. Start from the defaults and
	// change the keys that differ.
	$properties = $form->get_properties();

	$properties['form']       = $template;
	$properties['mail']       = array_merge( $properties['mail'], $mail );
	$properties['mail_2']['active'] = 0;
	$properties['messages']['mail_sent_ok'] = 'Thank you. Your enquiry has been received — we usually reply within one business day.';

	$form->set_title( 'Quote request' );
	$form->set_properties( $properties );
	$form->save();

	$id = (int) $form->id();

	spec9_seed_ok( 'form id', $id );
	spec9_seed_ok( 'title', $form->title() );

	return $id;
}

/**
 * The three menus, their items and their locations.
 *
 * @param array $pages Legal page IDs.
 * @return array Menu IDs keyed by location.
 */
function spec9_seed_menus( $pages ) {
	spec9_seed_head( 'Menus' );

	$menus = array();

	foreach ( array( 'Primary' => 'primary', 'Footer' => 'footer', 'Legal' => 'legal' ) as $name => $location ) {
		$menu = wp_get_nav_menu_object( $name );

		if ( ! $menu ) {
			$menu_id = wp_create_nav_menu( $name );

			if ( is_wp_error( $menu_id ) ) {
				WP_CLI::error( 'Could not create menu ' . $name . ': ' . $menu_id->get_error_message() );
			}
		} else {
			$menu_id = (int) $menu->term_id;

			foreach ( wp_get_nav_menu_items( $menu_id ) as $item ) {
				wp_delete_post( $item->ID, true );
			}
		}

		$menus[ $location ] = $menu_id;
	}

	$custom = array(
		'primary' => array(
			array( 'Home', home_url( '/' ) ),
			array( 'About', home_url( '/#about' ) ),
			array( 'Services', home_url( '/#services' ) ),
			array( 'Industries', home_url( '/#industries' ) ),
			array( 'Projects', home_url( '/#projects' ) ),
			array( 'Contact', home_url( '/#contact' ) ),
		),
		'footer'  => array(
			array( 'About', home_url( '/#about' ) ),
			array( 'Industries', home_url( '/#industries' ) ),
			array( 'Projects', home_url( '/#projects' ) ),
			array( 'Contact', home_url( '/#contact' ) ),
		),
	);

	foreach ( $custom as $location => $items ) {
		$position = 0;

		foreach ( $items as $item ) {
			++$position;

			wp_update_nav_menu_item(
				$menus[ $location ],
				0,
				array(
					'menu-item-title'    => $item[0],
					'menu-item-url'      => $item[1],
					'menu-item-type'     => 'custom',
					'menu-item-status'   => 'publish',
					'menu-item-position' => $position,
				)
			);
		}
	}

	$legal = array(
		array( 'Privacy Policy', $pages['privacy'] ),
		array( 'Terms', $pages['terms'] ),
	);

	$position = 0;

	foreach ( $legal as $item ) {
		++$position;

		wp_update_nav_menu_item(
			$menus['legal'],
			0,
			array(
				'menu-item-title'     => $item[0],
				'menu-item-object'    => 'page',
				'menu-item-object-id' => $item[1],
				'menu-item-type'      => 'post_type',
				'menu-item-status'    => 'publish',
				'menu-item-position'  => $position,
			)
		);
	}

	set_theme_mod(
		'nav_menu_locations',
		array(
			'primary' => $menus['primary'],
			'footer'  => $menus['footer'],
			'legal'   => $menus['legal'],
		)
	);

	spec9_seed_ok( 'primary', $menus['primary'] . ' (6 items)' );
	spec9_seed_ok( 'footer', $menus['footer'] . ' (4 items)' );
	spec9_seed_ok( 'legal', $menus['legal'] . ' (2 items)' );

	return $menus;
}

/**
 * Logos, contact details, the CTA band and the quote modal.
 *
 * @param array $media   Attachment IDs.
 * @param int   $form_id Contact Form 7 form ID.
 */
function spec9_seed_mods( $media, $form_id ) {
	spec9_seed_head( 'Customizer' );

	$mods = array(
		'spec9_logo_dark'              => isset( $media['logo-dark'] ) ? $media['logo-dark'] : '',
		'spec9_contact_phone'          => '(321) 555-0100',
		'spec9_contact_email'          => 'info@spec9.com',
		'spec9_contact_address'        => "Melbourne, Florida\nServing Brevard, Orange and the surrounding counties",
		'spec9_cta_heading'            => 'Planning a commercial flooring project?',
		'spec9_cta_bg'                 => isset( $media['cta'] ) ? $media['cta'] : '',
		'spec9_cta_btn1_text'          => 'Request a Quote',
		'spec9_cta_btn1_url'           => '#quote',
		'spec9_cta_btn2_text'          => 'Contact Us',
		'spec9_cta_btn2_url'           => 'tel:+13215550100',
		'spec9_quote_label'            => 'Request a quote',
		'spec9_quote_heading'          => 'Tell us about your project',
		'spec9_quote_lead'             => 'Fill in the form and we’ll get back to you within one business day.',
		'spec9_quote_form_shortcode'   => '[contact-form-7 id="' . $form_id . '" title="Quote request"]',
	);

	foreach ( $mods as $mod => $value ) {
		set_theme_mod( $mod, $value );
	}

	if ( ! empty( $media['logo'] ) ) {
		set_theme_mod( 'custom_logo', $media['logo'] );
	}

	spec9_seed_ok( 'theme mods', count( $mods ) + 1 . ' set' );
}

/* -------------------------------------------------------------- pass two */

/**
 * Write the Front Page section fields.
 *
 * @param int   $home_id Home page ID.
 * @param array $media   Attachment IDs.
 */
function spec9_seed_front_page_fields( $home_id, $media ) {
	spec9_seed_head( 'Front Page fields' );

	$icon = static function ( $key ) use ( $media ) {
		return isset( $media[ $key ] ) ? (int) $media[ $key ] : 0;
	};

	$fields = array(
		'industries_label'   => 'Who we build for',
		'industries_heading' => 'Floors for the spaces Florida works in',
		'industries_items'   => array(
			array( 'icon' => $icon( 'icon-education' ), 'title' => 'Education' ),
			array( 'icon' => $icon( 'icon-government' ), 'title' => 'Government' ),
			array( 'icon' => $icon( 'icon-hospitality' ), 'title' => 'Hospitality' ),
			array( 'icon' => $icon( 'icon-multifamily' ), 'title' => 'Multifamily' ),
			array( 'icon' => $icon( 'icon-commercial' ), 'title' => 'Commercial' ),
			array( 'icon' => $icon( 'icon-renovation' ), 'title' => 'Renovation' ),
			array( 'icon' => $icon( 'icon-demo' ), 'title' => 'Demo' ),
		),
		'about_label'        => 'About SPEC9',
		'about_heading'      => 'Serving Melbourne and Orlando since 1995',
		'about_lead'         => 'SPEC9 is an established commercial flooring partner. For three decades we have installed quality floors across Central Florida, and many of our clients keep coming back for the next project.',
		'about_points'       => array(
			array( 'title' => 'Regional knowledge.', 'text' => 'We know local codes, suppliers and climate.' ),
			array( 'title' => 'Commercial focus.', 'text' => 'We plan around your schedule and occupied spaces.' ),
			array( 'title' => 'Quality work.', 'text' => 'Careful preparation and clean, lasting installations.' ),
		),
		'about_image'        => $icon( 'lobby-corridor' ),
		'services_label'     => 'Services',
		'services_heading'   => 'Flooring installation and related services',
		'services_card_link_text' => 'Request pricing',
		'projects_label'     => 'Featured projects',
		'projects_heading'   => 'Recent work across the region',
		'stats_bg'           => $icon( 'tiles-pattern' ),
		'stats_items'        => array(
			array( 'number' => '30', 'suffix' => '+', 'label' => 'Years in business' ),
			array( 'number' => '500', 'suffix' => '+', 'label' => 'Projects completed' ),
			array( 'number' => '7', 'suffix' => '', 'label' => 'Markets served' ),
			array( 'number' => '2', 'suffix' => '', 'label' => 'Metro areas, Melbourne and Orlando' ),
		),
		'testimonials_label'   => 'Testimonials',
		'testimonials_heading' => 'What our clients say',
		'testimonials_items'   => array(
			array( 'quote' => '“They finished ahead of schedule and the floors look great. We’d hire them again.”', 'cite' => 'Project manager, general contractor' ),
			array( 'quote' => '“Clear communication from bid to punch list. Our tenants never noticed the work.”', 'cite' => 'Property manager, multifamily' ),
			array( 'quote' => '“A reliable partner for our school renovations across the county.”', 'cite' => 'Facilities director, education' ),
		),
	);

	$failed = array();

	foreach ( $fields as $name => $value ) {
		carbon_set_post_meta( $home_id, $name, $value );

		$read = carbon_get_post_meta( $home_id, $name );
		$want = $value;

		if ( is_array( $value ) && 'about_image' !== $name && 'stats_bg' !== $name ) {
			// Complex and association fields read back as arrays of rows.
			$want = count( $value );
			$read = is_array( $read ) ? count( $read ) : 0;
		} elseif ( is_array( $read ) ) {
			$read = (int) reset( $read );
		}

		if ( (string) $read !== (string) $want ) {
			$failed[] = $name;
			spec9_seed_ok( $name, 'MISMATCH (want ' . wp_json_encode( $want ) . ', got ' . wp_json_encode( $read ) . ')' );
		}
	}

	spec9_seed_ok( 'fields written', ( count( $fields ) - count( $failed ) ) . ' of ' . count( $fields ) );

	if ( $failed ) {
		WP_CLI::warning( 'Fields that did not read back: ' . implode( ', ', $failed ) );
	}
}

/**
 * Store the featured projects, falling back to raw meta if the field misbehaves.
 *
 * Carbon Fields stores an association as one meta row per entry, in the form
 * "post:project:123". Writing those rows directly produces exactly what the
 * field reads, so the fallback is not a hack — it is the same storage.
 *
 * @param int   $home_id  Home page ID.
 * @param int[] $projects Project IDs in bento order.
 */
function spec9_seed_featured( $home_id, $projects ) {
	$projects = array_values( array_filter( array_map( 'intval', $projects ) ) );

	if ( ! $projects ) {
		WP_CLI::warning( 'No projects to feature — run pass 1 first.' );

		return;
	}

	$rows = array();

	foreach ( $projects as $id ) {
		$rows[] = array(
			'id'      => $id,
			'type'    => 'post',
			'subtype' => 'project',
		);
	}

	carbon_set_post_meta( $home_id, 'projects_featured', $rows );

	$read = array_filter( array_map( 'intval', wp_list_pluck( (array) carbon_get_post_meta( $home_id, 'projects_featured' ), 'id' ) ) );

	if ( $read !== $projects ) {
		WP_CLI::warning( 'Association field did not read back; writing the meta rows directly.' );

		delete_post_meta( $home_id, '_projects_featured' );

		foreach ( $projects as $id ) {
			add_post_meta( $home_id, '_projects_featured', 'post:project:' . $id );
		}

		$read = array_filter( array_map( 'intval', wp_list_pluck( (array) carbon_get_post_meta( $home_id, 'projects_featured' ), 'id' ) ) );
	}

	if ( $read === $projects ) {
		spec9_seed_ok( 'projects_featured', implode( ', ', $read ) );
	} else {
		spec9_seed_ok( 'projects_featured', 'FAILED — got ' . implode( ', ', $read ) );
	}
}

/* ------------------------------------------------------------------ reset */

/**
 * Remove everything this script created.
 */
function spec9_seed_reset() {
	spec9_seed_head( 'Reset' );

	$ids = get_posts(
		array(
			'post_type'      => 'any',
			'post_status'    => 'any',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'meta_key'       => '_spec9_seed', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- One-off CLI cleanup.
			'meta_value'     => '1', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value -- One-off CLI cleanup.
		)
	);

	foreach ( $ids as $id ) {
		if ( 'attachment' === get_post_type( $id ) ) {
			wp_delete_attachment( $id, true );
		} else {
			wp_delete_post( $id, true );
		}
	}

	spec9_seed_ok( 'posts removed', count( $ids ) );

	foreach ( array( 'Primary', 'Footer', 'Legal' ) as $name ) {
		$menu = wp_get_nav_menu_object( $name );

		if ( $menu ) {
			wp_delete_nav_menu( $menu->term_id );
			spec9_seed_ok( 'menu removed', $name );
		}
	}

	$forms = WPCF7_ContactForm::find( array( 'title' => 'Quote request' ) );

	foreach ( $forms as $form ) {
		$form->delete();
		spec9_seed_ok( 'form removed', $form->id() );
	}

	foreach ( array(
		'spec9_logo_dark', 'spec9_contact_phone', 'spec9_contact_email', 'spec9_contact_address',
		'spec9_cta_heading', 'spec9_cta_bg', 'spec9_cta_btn1_text', 'spec9_cta_btn1_url',
		'spec9_cta_btn2_text', 'spec9_cta_btn2_url', 'spec9_quote_label', 'spec9_quote_heading',
		'spec9_quote_lead', 'spec9_quote_form_shortcode', 'custom_logo', 'nav_menu_locations',
	) as $mod ) {
		remove_theme_mod( $mod );
	}

	delete_option( 'spec9_seed_media' );

	if ( ! get_post( (int) get_option( 'page_on_front' ) ) ) {
		update_option( 'show_on_front', 'posts' );
		update_option( 'page_on_front', 0 );
	}

	flush_rewrite_rules();

	spec9_seed_ok( 'done', 'everything the seed created is gone' );
}

/* -------------------------------------------------------------- dispatcher */

$spec9_seed_pass  = '1';
$spec9_seed_reset = false;

foreach ( (array) $args as $spec9_seed_arg ) {
	if ( 'reset' === $spec9_seed_arg ) {
		$spec9_seed_reset = true;
	} elseif ( in_array( (string) $spec9_seed_arg, array( '1', '2' ), true ) ) {
		$spec9_seed_pass = (string) $spec9_seed_arg;
	}
}

if ( $spec9_seed_reset ) {
	spec9_seed_reset();
	spec9_seed_head( 'Done' );
	return;
}

if ( '2' === $spec9_seed_pass ) {
	$spec9_seed_home_id = (int) get_option( 'page_on_front' );

	if ( ! $spec9_seed_home_id || 'page' !== get_option( 'show_on_front' ) ) {
		WP_CLI::error( 'No front page set. Run pass 1 first.' );
	}

	spec9_seed_front_page_fields( $spec9_seed_home_id, spec9_seed_media_map() );

	$spec9_seed_projects = get_posts(
		array(
			'post_type'      => 'project',
			'posts_per_page' => 5,
			'orderby'        => 'menu_order',
			'order'          => 'ASC',
			'fields'         => 'ids',
		)
	);

	spec9_seed_featured( $spec9_seed_home_id, $spec9_seed_projects );

	flush_rewrite_rules();

	spec9_seed_head( 'Done' );
	spec9_seed_ok( 'front page', $spec9_seed_home_id );
	return;
}

spec9_seed_prereqs();
spec9_seed_identity();
$spec9_seed_media_ids = spec9_seed_import_media();
$spec9_seed_home_id   = spec9_seed_home( $spec9_seed_media_ids );
spec9_seed_services( $spec9_seed_media_ids );
spec9_seed_projects( $spec9_seed_media_ids );
$spec9_seed_pages = spec9_seed_legal_pages();
$spec9_seed_form  = spec9_seed_form();
spec9_seed_menus( $spec9_seed_pages );
spec9_seed_mods( $spec9_seed_media_ids, $spec9_seed_form );

flush_rewrite_rules();

spec9_seed_head( 'Done' );
spec9_seed_ok( 'home page', $spec9_seed_home_id );
spec9_seed_ok( 'services', count( get_posts( array( 'post_type' => 'service', 'posts_per_page' => -1, 'fields' => 'ids' ) ) ) );
spec9_seed_ok( 'projects', count( get_posts( array( 'post_type' => 'project', 'posts_per_page' => -1, 'fields' => 'ids' ) ) ) );
spec9_seed_ok( 'next', 'wp eval-file bin/seed-spec9.php 2' );
