<?php
/**
 * Template helpers and SVG support.
 *
 * @package Spec9
 */

/**
 * Nav menu walker that outputs bare anchors.
 *
 * The static site's menus are plain <a> elements with no <li> wrappers, so the
 * default walker's list markup would break parity.
 */
class Spec9_Bare_Walker extends Walker_Nav_Menu {

	/**
	 * No sub-levels: menus are flat.
	 *
	 * @param string   $output Passed by reference.
	 * @param int      $depth  Depth of menu item.
	 * @param stdClass $args   Menu arguments.
	 */
	public function start_lvl( &$output, $depth = 0, $args = null ) {}

	/**
	 * No sub-levels: menus are flat.
	 *
	 * @param string   $output Passed by reference.
	 * @param int      $depth  Depth of menu item.
	 * @param stdClass $args   Menu arguments.
	 */
	public function end_lvl( &$output, $depth = 0, $args = null ) {}

	/**
	 * Output a single anchor.
	 *
	 * @param string   $output Passed by reference.
	 * @param WP_Post  $item   Menu item data object.
	 * @param int      $depth  Depth of menu item.
	 * @param stdClass $args   Menu arguments.
	 * @param int      $id     Current item ID.
	 */
	public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
		$classes     = empty( $item->classes ) ? array() : (array) $item->classes;
		$classes     = array_filter( array_map( 'sanitize_html_class', $classes ) );
		$class_names = $classes ? ' class="' . esc_attr( implode( ' ', $classes ) ) . '"' : '';

		$atts = array(
			'href'   => ! empty( $item->url ) ? $item->url : '',
			'title'  => ! empty( $item->attr_title ) ? $item->attr_title : '',
			'target' => ! empty( $item->target ) ? $item->target : '',
			'rel'    => ! empty( $item->xfn ) ? $item->xfn : '',
		);

		/** This filter is documented in wp-includes/class-walker-nav-menu.php */
		$atts = apply_filters( 'nav_menu_link_attributes', $atts, $item, $args, $depth );

		$attributes = '';
		foreach ( $atts as $attr => $value ) {
			if ( is_scalar( $value ) && '' !== $value && false !== $value ) {
				$value       = ( 'href' === $attr ) ? esc_url( $value ) : esc_attr( $value );
				$attributes .= ' ' . $attr . '="' . $value . '"';
			}
		}

		/** This filter is documented in wp-includes/class-walker-nav-menu.php */
		$title = apply_filters( 'the_title', $item->title, $item->ID );
		/** This filter is documented in wp-includes/class-walker-nav-menu.php */
		$title = apply_filters( 'nav_menu_item_title', $title, $item, $args, $depth );

		$output .= '<a' . $class_names . $attributes . '>' . esc_html( $title ) . '</a>';
	}

	/**
	 * Closing tag is written by start_el, so nothing to close here.
	 *
	 * @param string   $output Passed by reference.
	 * @param WP_Post  $item   Menu item data object.
	 * @param int      $depth  Depth of menu item.
	 * @param stdClass $args   Menu arguments.
	 */
	public function end_el( &$output, $item, $depth = 0, $args = null ) {}
}

/**
 * Build a tel: href from a human-formatted phone number.
 *
 * @param string $phone Phone number as typed.
 * @return string tel: URL, or an empty string.
 */
function spec9_tel_href( $phone ) {
	$digits = preg_replace( '/[^0-9+]/', '', (string) $phone );

	return $digits ? 'tel:' . $digits : '';
}

/**
 * Build an obfuscated mailto link.
 *
 * antispambot() returns HTML entities, which are already safe in an attribute
 * and in text context; escaping them again would double-encode the address.
 * The address itself is sanitized with sanitize_email() when it is saved.
 *
 * @param string $email Email address.
 * @return string Anchor markup, or an empty string.
 */
function spec9_email_link( $email ) {
	if ( ! $email ) {
		return '';
	}

	$safe = antispambot( (string) $email );

	return '<a href="mailto:' . $safe . '">' . $safe . '</a>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * Build the inline style that feeds a section's background image.
 *
 * Output the result through esc_attr() at the call site.
 *
 * @param int $attachment_id Attachment ID.
 * @return string CSS custom property declaration, or an empty string.
 */
function spec9_bg_img_style( $attachment_id ) {
	$url = $attachment_id ? wp_get_attachment_image_url( (int) $attachment_id, 'full' ) : '';

	if ( ! $url ) {
		return '';
	}

	return "--bg-img:url('" . esc_url( $url ) . "')";
}

/**
 * Build the markup for one site logo image.
 *
 * The header swaps between a light and a dark variant with CSS, so it needs
 * two plain <img> tags it can class. get_custom_logo() prints its own anchor
 * and classes and knows nothing about the dark variant, hence this.
 *
 * @param string $class CSS class for the image; omitted when empty.
 * @param int    $id    Attachment ID. 0 renders nothing.
 * @param bool   $eager Whether to load eagerly instead of lazily.
 * @return string
 */
function spec9_logo_img( $class, $id, $eager = false ) {
	$id = (int) $id;

	if ( ! $id ) {
		return '';
	}

	$attr = array(
		'alt' => get_bloginfo( 'name' ),
	);

	if ( $class ) {
		$attr['class'] = $class;
	}

	if ( $eager ) {
		$attr['loading'] = 'eager';
	}

	return wp_get_attachment_image( $id, 'full', false, $attr );
}

/**
 * Elements and attributes allowed in an uploaded SVG.
 *
 * Attribute names are lowercase because that is how the HTML parser and
 * wp_kses match them; browsers map them back to camelCase (viewBox and
 * friends) when SVG is inlined into an HTML document.
 *
 * @return array
 */
function spec9_svg_allowed() {
	return array(
		'svg'      => array(
			'xmlns'               => true,
			'xmlns:xlink'         => true,
			'viewbox'             => true,
			'width'               => true,
			'height'              => true,
			'fill'                => true,
			'stroke'              => true,
			'stroke-width'        => true,
			'stroke-linecap'      => true,
			'stroke-linejoin'     => true,
			'class'               => true,
			'role'                => true,
			'aria-hidden'         => true,
			'aria-label'          => true,
			'focusable'           => true,
			'preserveaspectratio' => true,
			'version'             => true,
		),
		'g'        => array(
			'fill'        => true,
			'stroke'      => true,
			'stroke-width' => true,
			'transform'   => true,
			'clip-path'   => true,
			'opacity'     => true,
		),
		'path'     => array(
			'd'                => true,
			'fill'             => true,
			'fill-rule'        => true,
			'clip-rule'        => true,
			'stroke'           => true,
			'stroke-width'     => true,
			'stroke-linecap'   => true,
			'stroke-linejoin'  => true,
			'transform'        => true,
			'opacity'          => true,
		),
		'line'     => array(
			'x1'           => true,
			'y1'           => true,
			'x2'           => true,
			'y2'           => true,
			'stroke'       => true,
			'stroke-width' => true,
		),
		'circle'   => array(
			'cx'           => true,
			'cy'           => true,
			'r'            => true,
			'fill'         => true,
			'stroke'       => true,
			'stroke-width' => true,
		),
		'ellipse'  => array(
			'cx'           => true,
			'cy'           => true,
			'rx'           => true,
			'ry'           => true,
			'fill'         => true,
			'stroke'       => true,
			'stroke-width' => true,
		),
		'rect'     => array(
			'x'            => true,
			'y'            => true,
			'width'        => true,
			'height'       => true,
			'rx'           => true,
			'ry'           => true,
			'fill'         => true,
			'stroke'       => true,
			'stroke-width' => true,
		),
		'polyline' => array(
			'points' => true,
			'fill'   => true,
			'stroke' => true,
		),
		'polygon'  => array(
			'points' => true,
			'fill'   => true,
			'stroke' => true,
		),
		'use'      => array(
			'href'       => true,
			'xlink:href' => true,
			'x'          => true,
			'y'          => true,
		),
		'defs'     => array(),
		'clippath' => array( 'id' => true ),
		'lineargradient' => array(
			'id'            => true,
			'x1'            => true,
			'y1'            => true,
			'x2'            => true,
			'y2'            => true,
			'gradientunits' => true,
			'gradienttransform' => true,
		),
		'radialgradient' => array(
			'id'    => true,
			'cx'    => true,
			'cy'    => true,
			'r'     => true,
			'fx'    => true,
			'fy'    => true,
		),
		'stop'     => array(
			'offset'       => true,
			'stop-color'   => true,
			'stop-opacity' => true,
		),
		'title'    => array(),
		'desc'     => array(),
	);
}

/**
 * Check SVG source for constructs that must never reach the browser.
 *
 * WordPress does not sanitize SVG uploads, and the raw file stays reachable at
 * its uploads URL, so a stored-XSS payload would run there even if the theme
 * only ever printed a sanitized copy. Files that fail this check are rejected
 * outright rather than rewritten, so what lands on disk is always the original,
 * valid SVG.
 *
 * @param string $contents File contents.
 * @return bool True when the SVG is safe to store.
 */
function spec9_svg_is_safe( $contents ) {
	$contents = (string) $contents;

	// Comments can hide markup from naive checks, so drop them first.
	$source = preg_replace( '#<!--.*?-->#s', '', $contents );

	$blocked = array(
		'/<script/i',
		'/<foreignobject/i',
		'/<iframe/i',
		'/<embed/i',
		'/<object/i',
		'/<audio/i',
		'/<video/i',
		'/<image/i',
		'/<animate/i',
		'/<set[\s>\/]/i',
		'/<handler/i',
		'/<a[\s>\/]/i',
		'/<!entity/i',
		'/<!doctype[^>]*\[/i',
		'/\son[a-z]+\s*=/i',
		'/javascript:/i',
		'/data:text\/html/i',
		'/xlink:href\s*=\s*["\']?(?!#)/i',
		'/\shref\s*=\s*["\']?(?!#)/i',
	);

	foreach ( $blocked as $pattern ) {
		if ( preg_match( $pattern, $source ) ) {
			return false;
		}
	}

	return true;
}

/**
 * Allow SVG uploads for administrators only.
 *
 * @param array $mimes Allowed mime types.
 * @return array
 */
function spec9_allow_svg_mimes( $mimes ) {
	if ( current_user_can( 'manage_options' ) || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
		$mimes['svg'] = 'image/svg+xml';
	}

	return $mimes;
}
add_filter( 'upload_mimes', 'spec9_allow_svg_mimes' );

/**
 * Fill in the SVG file type when WordPress cannot detect it.
 *
 * getimagesize() cannot read SVG, so the extension and mime type are restored
 * here when the upload is an SVG and nothing else identified it.
 *
 * @param array  $data     File data: ext, type, proper_filename.
 * @param string $file     Full path to the file.
 * @param string $filename The name of the file.
 * @param array  $mimes    Allowed mime types keyed by extension regex.
 * @return array
 */
function spec9_fix_svg_filetype( $data, $file, $filename, $mimes ) {
	if ( ! empty( $data['ext'] ) && ! empty( $data['type'] ) ) {
		return $data;
	}

	if ( 'svg' === strtolower( pathinfo( $filename, PATHINFO_EXTENSION ) ) ) {
		$data['ext']  = 'svg';
		$data['type'] = 'image/svg+xml';
	}

	return $data;
}
add_filter( 'wp_check_filetype_and_ext', 'spec9_fix_svg_filetype', 10, 4 );

/**
 * Reject SVG uploads that contain scripts or external references.
 *
 * Hooked to the upload and the sideload filter both, so every route into the
 * media library — wp-cli's media import included — is checked the same way.
 *
 * @param array $file Uploaded file data.
 * @return array
 */
function spec9_svg_upload_prefilter( $file ) {
	$name   = isset( $file['name'] ) ? (string) $file['name'] : '';
	$is_svg = ( isset( $file['type'] ) && 'image/svg+xml' === $file['type'] )
		|| 'svg' === strtolower( pathinfo( $name, PATHINFO_EXTENSION ) );

	if ( ! $is_svg ) {
		return $file;
	}

	if ( ! current_user_can( 'manage_options' ) && ! ( defined( 'WP_CLI' ) && WP_CLI ) ) {
		$file['error'] = __( 'Only administrators can upload SVG files.', 'spec9' );

		return $file;
	}

	$contents = isset( $file['tmp_name'] ) && is_readable( $file['tmp_name'] ) ? file_get_contents( $file['tmp_name'] ) : false;

	if ( false === $contents || ! spec9_svg_is_safe( $contents ) ) {
		$file['error'] = __( 'This SVG contains code that is not allowed.', 'spec9' );
	}

	return $file;
}
add_filter( 'wp_handle_upload_prefilter', 'spec9_svg_upload_prefilter' );
add_filter( 'wp_handle_sideload_prefilter', 'spec9_svg_upload_prefilter' );

/**
 * Print an SVG attachment inline.
 *
 * The industry icons are stroked with CSS (.ind svg { stroke: var(--red) }),
 * which cannot reach inside an <img>, so the markup is inlined and sanitized
 * against the theme's SVG allowlist on the way out.
 *
 * @param int    $attachment_id Attachment ID.
 * @param string $class         Optional CSS class for the svg element.
 * @return string
 */
function spec9_inline_svg( $attachment_id, $class = '' ) {
	$attachment_id = (int) $attachment_id;

	if ( ! $attachment_id ) {
		return '';
	}

	$path = get_attached_file( $attachment_id );

	if ( ! $path || ! file_exists( $path ) || 'image/svg+xml' !== get_post_mime_type( $attachment_id ) ) {
		return wp_get_attachment_image( $attachment_id, 'medium' );
	}

	$svg = file_get_contents( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Local theme asset.

	if ( false === $svg ) {
		return '';
	}

	// A file that fails the upload scan is never inlined, no matter which route
	// it took into the library; an <img> renders it in the browser's own
	// restricted SVG context instead.
	if ( ! spec9_svg_is_safe( $svg ) ) {
		return wp_get_attachment_image( $attachment_id, 'medium' );
	}

	// wp_kses strips the prolog and doctype anyway; removing them first keeps
	// the sanitized output identical to the source.
	$svg = preg_replace( '/<\?xml.*?\?>/s', '', $svg );
	$svg = preg_replace( '/<!DOCTYPE.*?>/si', '', $svg );
	$svg = wp_kses( (string) $svg, spec9_svg_allowed() );

	if ( $class ) {
		$svg = preg_replace( '/<svg\b/', '<svg class="' . esc_attr( $class ) . '"', $svg, 1 );
	}

	return trim( (string) $svg );
}
