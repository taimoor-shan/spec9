<?php
/**
 * Project tile for the bento grid.
 *
 * The <figure> stays the grid child so its span classes keep working; the
 * whole tile is made clickable by an empty anchor stretched over it.
 *
 * @package Spec9
 *
 * @var array $args {
 *     @type string $ph_mod Extra .ph modifier classes for the tile.
 * }
 */

$spec9_ph_mod = isset( $args['ph_mod'] ) ? $args['ph_mod'] : '';
$spec9_title  = get_the_title();
?>
<figure class="ph <?php echo esc_attr( $spec9_ph_mod ); ?>">
	<?php the_post_thumbnail( 'large' ); ?>
	<figcaption><?php echo esc_html( $spec9_title ); ?></figcaption>
	<a class="cover" href="<?php echo esc_url( get_permalink() ); ?>" aria-label="<?php echo esc_attr( $spec9_title ); ?>"></a>
</figure>
