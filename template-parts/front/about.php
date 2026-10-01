<?php
/**
 * About: two-column introduction with the differentiator list.
 *
 * @package Spec9
 *
 * @var array $args {
 *     @type int $home_id Front page ID.
 * }
 */

$spec9_home_id = isset( $args['home_id'] ) ? (int) $args['home_id'] : 0;
$spec9_heading = carbon_get_post_meta( $spec9_home_id, 'about_heading' );
$spec9_lead    = carbon_get_post_meta( $spec9_home_id, 'about_lead' );
$spec9_points  = carbon_get_post_meta( $spec9_home_id, 'about_points' );
$spec9_image   = (int) carbon_get_post_meta( $spec9_home_id, 'about_image' );

if ( ! $spec9_heading && ! $spec9_lead && ! $spec9_points && ! $spec9_image ) {
	return;
}

// Without an image the two-column grid would leave half the row empty.
$spec9_layout = $spec9_image ? 'w about' : 'w';
?>
<section class="sec warm" id="about">
	<div class="<?php echo esc_attr( $spec9_layout ); ?>">
		<div>
			<?php
			get_template_part(
				'template-parts/section-head',
				null,
				array(
					'label'   => carbon_get_post_meta( $spec9_home_id, 'about_label' ),
					'heading' => $spec9_heading,
				)
			);
			?>

			<?php if ( $spec9_lead ) : ?>
				<p class="lead"><?php echo wp_kses_post( $spec9_lead ); ?></p>
			<?php endif; ?>

			<?php if ( $spec9_points ) : ?>
				<div class="dif">
					<?php foreach ( $spec9_points as $spec9_point ) : ?>
						<p>
							<b><?php echo esc_html( isset( $spec9_point['title'] ) ? $spec9_point['title'] : '' ); ?></b>
							<?php echo esc_html( isset( $spec9_point['text'] ) ? $spec9_point['text'] : '' ); ?>
						</p>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>

		<?php if ( $spec9_image ) : ?>
			<div class="ph b"><?php echo wp_get_attachment_image( $spec9_image, 'large' ); ?></div>
		<?php endif; ?>
	</div>
</section>
