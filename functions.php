<?php
/**
 * SPEC9 theme bootstrap.
 *
 * @package Spec9
 */

if ( ! defined( 'SPEC9_VERSION' ) ) {
	define( 'SPEC9_VERSION', '1.0.0' );
}

$spec9_autoload = get_template_directory() . '/vendor/autoload.php';

if ( file_exists( $spec9_autoload ) ) {
	require $spec9_autoload;
} else {
	/**
	 * Warn in wp-admin when the bundled Composer dependencies are missing.
	 */
	function spec9_missing_vendor_notice() {
		printf(
			'<div class="notice notice-error"><p>%s</p></div>',
			esc_html__( 'SPEC9: vendor/autoload.php is missing. Run "composer install --no-dev" inside the theme directory.', 'spec9' )
		);
	}
	add_action( 'admin_notices', 'spec9_missing_vendor_notice' );
}

/**
 * Boot Carbon Fields.
 *
 * Booted on after_setup_theme, before any container registration runs.
 */
add_action(
	'after_setup_theme',
	function () {
		if ( class_exists( '\Carbon_Fields\Carbon_Fields' ) ) {
			\Carbon_Fields\Carbon_Fields::boot();
		}
	}
);

/**
 * Register the front page field container.
 *
 * Required inside the hook so Carbon Fields classes are always loaded when
 * the container is defined.
 */
add_action(
	'carbon_fields_register_fields',
	function () {
		require_once get_template_directory() . '/inc/fields/front-page.php';
	}
);

require get_template_directory() . '/inc/setup.php';
require get_template_directory() . '/inc/enqueue.php';
require get_template_directory() . '/inc/cpt.php';
require get_template_directory() . '/inc/customizer.php';
require get_template_directory() . '/inc/helpers.php';
