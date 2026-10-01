<?php
/**
 * The closing call to action, shared by the front page and the single pages.
 *
 * Everything here is a theme mod, so the band can be turned off by emptying
 * the heading and both buttons.
 *
 * @package Spec9
 */

$spec9_heading = get_theme_mod( 'spec9_cta_heading', '' );
$spec9_btn1    = get_theme_mod( 'spec9_cta_btn1_text', '' );
$spec9_btn2    = get_theme_mod( 'spec9_cta_btn2_text', '' );

if ( ! $spec9_heading && ! $spec9_btn1 && ! $spec9_btn2 ) {
	return;
}

$spec9_bg_style = spec9_bg_img_style( (int) get_theme_mod( 'spec9_cta_bg', 0 ) );
?>
<section class="cta" id="contact"<?php if ( $spec9_bg_style ) { printf( ' style="%s"', esc_attr( $spec9_bg_style ) ); } ?>>
	<div class="w">
		<?php if ( $spec9_heading ) : ?>
			<h2><?php echo esc_html( $spec9_heading ); ?></h2>
		<?php endif; ?>
		<?php
		/*
		 * Printed from one block on purpose: the buttons are inline-blocks, and
		 * any whitespace between them in the markup would add a gap the static
		 * site does not have.
		 */
		if ( $spec9_btn1 ) {
			printf(
				'<a class="btn p" href="%s">%s</a>',
				esc_url( get_theme_mod( 'spec9_cta_btn1_url', '#quote' ) ),
				esc_html( $spec9_btn1 )
			);
		}

		if ( $spec9_btn2 ) {
			printf(
				'<a class="btn g" href="%s">%s</a>',
				esc_url( get_theme_mod( 'spec9_cta_btn2_url', '' ) ),
				esc_html( $spec9_btn2 )
			);
		}
		?>
	</div>
</section>
