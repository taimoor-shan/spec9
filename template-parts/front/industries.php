<?php
/**
 * Industries: the seven-icon row.
 *
 * Icons are inlined rather than printed as <img> because .ind svg strokes them
 * with the brand colour (inc/helpers.php, spec9_inline_svg()).
 *
 * @package Spec9
 *
 * @var array $args {
 *     @type int $home_id Front page ID.
 * }
 */

$spec9_home_id = isset( $args['home_id'] ) ? (int) $args['home_id'] : 0;
$spec9_items   = carbon_get_post_meta( $spec9_home_id, 'industries_items' );

if ( ! $spec9_items ) {
	return;
}
?>
<section class="sec" id="industries">
	<div class="w">
		<?php
		get_template_part(
			'template-parts/section-head',
			null,
			array(
				'label'   => carbon_get_post_meta( $spec9_home_id, 'industries_label' ),
				'heading' => carbon_get_post_meta( $spec9_home_id, 'industries_heading' ),
			)
		);
		?>
		<div class="ind">
			<?php foreach ( $spec9_items as $spec9_item ) : ?>
				<div>
					<?php echo spec9_inline_svg( isset( $spec9_item['icon'] ) ? $spec9_item['icon'] : 0 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Sanitized against the theme's SVG allowlist. ?>
					<b><?php echo esc_html( isset( $spec9_item['title'] ) ? $spec9_item['title'] : '' ); ?></b>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
