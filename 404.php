<?php
/**
 * 404 template.
 *
 * @package Spec9
 */

get_header();
?>
<section class="sec">
	<div class="w entry-content">
		<h1><?php esc_html_e( 'Page not found', 'spec9' ); ?></h1>
		<p><?php esc_html_e( 'The page you were looking for is not here. Head back to the home page, or get in touch and we will point you in the right direction.', 'spec9' ); ?></p>
		<p><a class="btn p" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Back to home', 'spec9' ); ?></a></p>
	</div>
</section>
<?php
get_footer();
