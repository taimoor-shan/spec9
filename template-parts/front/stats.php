<?php
/**
 * Stats: the dark band of numbers.
 *
 * @package Spec9
 *
 * @var array $args {
 *     @type int $home_id Front page ID.
 * }
 */

$spec9_home_id  = isset( $args['home_id'] ) ? (int) $args['home_id'] : 0;
$spec9_items    = carbon_get_post_meta( $spec9_home_id, 'stats_items' );
$spec9_bg_style = spec9_bg_img_style( (int) carbon_get_post_meta( $spec9_home_id, 'stats_bg' ) );

if ( ! $spec9_items ) {
	return;
}
?>
<section class="stats sec"<?php if ( $spec9_bg_style ) { printf( ' style="%s"', esc_attr( $spec9_bg_style ) ); } ?>>
	<div class="w">
		<div class="g">
			<?php foreach ( $spec9_items as $spec9_item ) : ?>
				<div>
					<strong><?php echo esc_html( isset( $spec9_item['number'] ) ? $spec9_item['number'] : '' ); ?><?php if ( ! empty( $spec9_item['suffix'] ) ) : ?><em><?php echo esc_html( $spec9_item['suffix'] ); ?></em><?php endif; ?></strong>
					<span><?php echo esc_html( isset( $spec9_item['label'] ) ? $spec9_item['label'] : '' ); ?></span>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
