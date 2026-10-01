<?php
/**
 * Header: document head, body tag and the sticky site header.
 *
 * @package Spec9
 */

$spec9_logo_light = (int) get_theme_mod( 'custom_logo' );
$spec9_logo_dark  = (int) get_theme_mod( 'spec9_logo_dark' );

// A site that only has one logo still needs something to show in dark mode.
if ( ! $spec9_logo_dark ) {
	$spec9_logo_dark = $spec9_logo_light;
}
$spec9_home_url = home_url( '/' );
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<header>
	<div class="w">
		<?php if ( $spec9_logo_light ) : ?>
			<a href="<?php echo esc_url( $spec9_home_url ); ?>">
				<?php
				echo spec9_logo_img( 'header-logo-light', $spec9_logo_light, true ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_get_attachment_image() escapes its own attributes.
				echo spec9_logo_img( 'header-logo-dark', $spec9_logo_dark, true ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Same as above.
				?>
			</a>
		<?php else : ?>
			<a href="<?php echo esc_url( $spec9_home_url ); ?>"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></a>
		<?php endif; ?>

		<?php
		wp_nav_menu(
			array(
				'theme_location' => 'primary',
				'container'      => false,
				'items_wrap'     => '<nav>%3$s</nav>',
				'fallback_cb'    => false,
				'depth'          => 1,
				'walker'         => new Spec9_Bare_Walker(),
			)
		);
		?>

		<div class="h-actions">
			<button class="theme-toggle" type="button" id="theme-toggle" aria-pressed="false" aria-label="<?php esc_attr_e( 'Switch to dark mode', 'spec9' ); ?>" title="<?php esc_attr_e( 'Switch dark / light', 'spec9' ); ?>"><span class="icon-moon" aria-hidden="true">☾</span><span class="icon-sun" aria-hidden="true">☀</span></button>
			<a class="btn p" href="#quote"><?php esc_html_e( 'Get a Quote', 'spec9' ); ?></a>
		</div>
	</div>
</header>
