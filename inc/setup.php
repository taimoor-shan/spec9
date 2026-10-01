<?php
/**
 * Theme setup: supports, menus and image sizes.
 *
 * @package Spec9
 */

/**
 * Register theme defaults.
 */
function spec9_setup() {
	load_theme_textdomain( 'spec9', get_template_directory() . '/languages' );

	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );

	add_theme_support(
		'html5',
		array(
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
			'style',
			'script',
		)
	);

	add_theme_support(
		'custom-logo',
		array(
			'flex-width'  => true,
			'flex-height' => true,
		)
	);

	register_nav_menus(
		array(
			'primary' => esc_html__( 'Primary', 'spec9' ),
			'footer'  => esc_html__( 'Footer', 'spec9' ),
			'legal'   => esc_html__( 'Legal', 'spec9' ),
		)
	);

	// Card size for service and project cards.
	add_image_size( 'spec9-card', 800, 600, true );
}
add_action( 'after_setup_theme', 'spec9_setup' );

/**
 * Set the content width, based on the theme's layout.
 *
 * @global int $content_width
 */
function spec9_content_width() {
	$GLOBALS['content_width'] = apply_filters( 'spec9_content_width', 1240 );
}
add_action( 'after_setup_theme', 'spec9_content_width', 0 );
