<?php
/**
 * Front-end assets.
 *
 * @package Spec9
 */

/**
 * Print the anti-flash theme script and the font preconnects.
 *
 * Runs at the very top of wp_head so the theme is applied before first paint.
 */
function spec9_head_start() {
	?>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<script>
		/* Set theme before first paint to avoid a light/dark flash. */
		(function () {
			try {
				var t = localStorage.getItem('spec9-theme');
				if (t !== 'light' && t !== 'dark') {
					t = (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) ? 'dark' : 'light';
				}
				document.documentElement.setAttribute('data-theme', t);
			} catch (e) {
				document.documentElement.setAttribute('data-theme', 'light');
			}
		})();
	</script>
	<?php
}
add_action( 'wp_head', 'spec9_head_start', 0 );

/**
 * Version an asset by its modification time, falling back to the theme version.
 *
 * @param string $relative Path relative to the theme root.
 * @return string
 */
function spec9_asset_version( $relative ) {
	$path = get_template_directory() . '/' . ltrim( $relative, '/' );

	return file_exists( $path ) ? (string) filemtime( $path ) : SPEC9_VERSION;
}

/**
 * Enqueue styles and scripts.
 */
function spec9_scripts() {
	wp_enqueue_style(
		'spec9-fonts',
		'https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@500;600;700&family=Barlow:wght@400;500&display=swap',
		array(),
		null // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion -- Google Fonts URL is versioned by the vendor.
	);

	// style.css carries the theme header and no rules, so it is never enqueued.
	wp_enqueue_style( 'spec9-tokens', get_template_directory_uri() . '/assets/css/tokens.css', array(), spec9_asset_version( 'assets/css/tokens.css' ) );
	wp_enqueue_style( 'spec9-main', get_template_directory_uri() . '/assets/css/main.css', array( 'spec9-tokens' ), spec9_asset_version( 'assets/css/main.css' ) );

	wp_enqueue_script(
		'spec9-main',
		get_template_directory_uri() . '/assets/js/main.js',
		array(),
		spec9_asset_version( 'assets/js/main.js' ),
		array(
			'in_footer' => true,
			'strategy'  => 'defer',
		)
	);
}
add_action( 'wp_enqueue_scripts', 'spec9_scripts' );

/**
 * Register the button block styles used by the hero.
 */
function spec9_register_block_styles() {
	register_block_style(
		'core/button',
		array(
			'name'  => 'primary',
			'label' => __( 'Primary', 'spec9' ),
		)
	);

	register_block_style(
		'core/button',
		array(
			'name'  => 'ghost',
			'label' => __( 'Ghost', 'spec9' ),
		)
	);
}
add_action( 'init', 'spec9_register_block_styles' );

/**
 * Contact Form 7 integration.
 *
 * The theme styles the quote form itself, so CF7's stylesheet is not needed,
 * and the form markup in the modal is already hand-spaced, so autop is off.
 */
function spec9_cf7_setup() {
	if ( ! class_exists( 'WPCF7' ) ) {
		return;
	}

	add_filter( 'wpcf7_load_css', '__return_false' );
	add_filter( 'wpcf7_autop_or_not', '__return_false' );
}
add_action( 'init', 'spec9_cf7_setup' );
