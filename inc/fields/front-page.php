<?php
/**
 * Carbon Fields: the Front Page container.
 *
 * Global content (logos, contact details, the CTA band and the quote modal)
 * lives in the Customizer — see inc/customizer.php. Only the sections that
 * belong to the front page itself are registered here.
 *
 * Carbon Fields has no "group" field, so fields are organised one tab per
 * section, and every field name is prefixed with its section
 * (about_heading, stats_items, ...) so names never collide.
 *
 * @package Spec9
 */

use Carbon_Fields\Container;
use Carbon_Fields\Field;

if ( ! class_exists( Container::class ) ) {
	return;
}

Container::make( 'post_meta', __( 'Front Page', 'spec9' ) )
	->where( 'post_id', '=', (int) get_option( 'page_on_front' ) )
	->add_tab(
		__( 'Industries', 'spec9' ),
		array(
			Field::make( 'text', 'industries_label', __( 'Eyebrow label', 'spec9' ) ),
			Field::make( 'text', 'industries_heading', __( 'Heading', 'spec9' ) ),
			Field::make( 'complex', 'industries_items', __( 'Industries', 'spec9' ) )
				->set_layout( 'grid' )
				->add_fields(
					array(
						Field::make( 'image', 'icon', __( 'Icon', 'spec9' ) )
							->help_text( __( 'SVG or PNG. Icons are drawn in the brand red by the theme.', 'spec9' ) ),
						Field::make( 'text', 'title', __( 'Title', 'spec9' ) ),
					)
				),
		)
	)
	->add_tab(
		__( 'About', 'spec9' ),
		array(
			Field::make( 'text', 'about_label', __( 'Eyebrow label', 'spec9' ) ),
			Field::make( 'text', 'about_heading', __( 'Heading', 'spec9' ) ),
			Field::make( 'textarea', 'about_lead', __( 'Lead paragraph', 'spec9' ) ),
			Field::make( 'complex', 'about_points', __( 'Points', 'spec9' ) )
				->set_layout( 'grid' )
				->add_fields(
					array(
						Field::make( 'text', 'title', __( 'Bold lead-in', 'spec9' ) ),
						Field::make( 'text', 'text', __( 'Text', 'spec9' ) ),
					)
				),
			Field::make( 'image', 'about_image', __( 'Image', 'spec9' ) ),
		)
	)
	->add_tab(
		__( 'Services', 'spec9' ),
		array(
			Field::make( 'text', 'services_label', __( 'Eyebrow label', 'spec9' ) ),
			Field::make( 'text', 'services_heading', __( 'Heading', 'spec9' ) ),
			Field::make( 'text', 'services_card_link_text', __( 'Card link text', 'spec9' ) )
				->set_default_value( __( 'Request pricing', 'spec9' ) )
				->help_text( __( 'The cards themselves come from the Services post type, ordered by the Order field.', 'spec9' ) ),
		)
	)
	->add_tab(
		__( 'Projects', 'spec9' ),
		array(
			Field::make( 'text', 'projects_label', __( 'Eyebrow label', 'spec9' ) ),
			Field::make( 'text', 'projects_heading', __( 'Heading', 'spec9' ) ),
			Field::make( 'association', 'projects_featured', __( 'Featured projects', 'spec9' ) )
				->set_types(
					array(
						array(
							'type'      => 'post',
							'post_type' => 'project',
						),
					)
				)
				->set_max( 5 )
				->help_text( __( 'Up to five projects, in the order they appear in the grid.', 'spec9' ) ),
		)
	)
	->add_tab(
		__( 'Stats', 'spec9' ),
		array(
			Field::make( 'image', 'stats_bg', __( 'Background image', 'spec9' ) ),
			Field::make( 'complex', 'stats_items', __( 'Stats', 'spec9' ) )
				->set_layout( 'grid' )
				->add_fields(
					array(
						Field::make( 'text', 'number', __( 'Number', 'spec9' ) ),
						Field::make( 'text', 'suffix', __( 'Suffix', 'spec9' ) )
							->help_text( __( 'Shown in red after the number, for example +.', 'spec9' ) ),
						Field::make( 'text', 'label', __( 'Label', 'spec9' ) ),
					)
				),
		)
	)
	->add_tab(
		__( 'Testimonials', 'spec9' ),
		array(
			Field::make( 'text', 'testimonials_label', __( 'Eyebrow label', 'spec9' ) ),
			Field::make( 'text', 'testimonials_heading', __( 'Heading', 'spec9' ) ),
			Field::make( 'complex', 'testimonials_items', __( 'Testimonials', 'spec9' ) )
				->set_layout( 'grid' )
				->add_fields(
					array(
						Field::make( 'textarea', 'quote', __( 'Quote', 'spec9' ) ),
						Field::make( 'text', 'cite', __( 'Attribution', 'spec9' ) ),
					)
				),
		)
	);
