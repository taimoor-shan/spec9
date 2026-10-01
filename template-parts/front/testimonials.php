<?php
/**
 * Testimonials: three quotes from the front page fields.
 *
 * The <cite> sits inline right after the quote, matching the static site —
 * the stylesheet turns it into its own line.
 *
 * @package Spec9
 *
 * @var array $args {
 *     @type int $home_id Front page ID.
 * }
 */

$spec9_home_id = isset( $args['home_id'] ) ? (int) $args['home_id'] : 0;
$spec9_items   = carbon_get_post_meta( $spec9_home_id, 'testimonials_items' );

if ( ! $spec9_items ) {
	return;
}
?>
<section class="sec">
	<div class="w">
		<?php
		get_template_part(
			'template-parts/section-head',
			null,
			array(
				'label'   => carbon_get_post_meta( $spec9_home_id, 'testimonials_label' ),
				'heading' => carbon_get_post_meta( $spec9_home_id, 'testimonials_heading' ),
			)
		);
		?>
		<div class="q">
			<?php foreach ( $spec9_items as $spec9_item ) : ?>
				<blockquote><?php echo wp_kses_post( isset( $spec9_item['quote'] ) ? $spec9_item['quote'] : '' ); ?><cite><?php echo esc_html( isset( $spec9_item['cite'] ) ? $spec9_item['cite'] : '' ); ?></cite></blockquote>
			<?php endforeach; ?>
		</div>
	</div>
</section>
