<?php
/**
 * Quote modal.
 *
 * The form itself is a Contact Form 7 shortcode saved in the Customizer, so
 * the fields stay editable in the form editor. The backdrop's id is "quote"
 * because that is what the #quote links — and the theme's script — look for.
 *
 * @package Spec9
 */

$spec9_label   = get_theme_mod( 'spec9_quote_label', '' );
$spec9_heading = get_theme_mod( 'spec9_quote_heading', '' );
$spec9_lead    = get_theme_mod( 'spec9_quote_lead', '' );
$spec9_form    = get_theme_mod( 'spec9_quote_form_shortcode', '' );
?>
<div class="modal-backdrop" id="quote" role="dialog" aria-modal="true"<?php echo $spec9_heading ? ' aria-labelledby="quote-modal-title"' : ''; ?> aria-hidden="true">
	<div class="modal">
		<div class="modal-head">
			<div>
				<?php if ( $spec9_label ) : ?>
					<div class="lab"><?php echo esc_html( $spec9_label ); ?></div>
				<?php endif; ?>

				<?php if ( $spec9_heading ) : ?>
					<h2 id="quote-modal-title"><?php echo esc_html( $spec9_heading ); ?></h2>
				<?php endif; ?>
			</div>
			<button class="modal-close" type="button" data-modal-close aria-label="<?php esc_attr_e( 'Close dialog', 'spec9' ); ?>">✕</button>
		</div>

		<?php if ( $spec9_lead ) : ?>
			<p class="lead"><?php echo esc_html( $spec9_lead ); ?></p>
		<?php endif; ?>

		<?php echo do_shortcode( wp_kses_post( $spec9_form ) ); ?>
	</div>
</div>
