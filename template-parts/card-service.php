<?php
/**
 * Service card.
 *
 * @package Spec9
 *
 * @var array $args {
 *     @type string $link      Card URL. Defaults to the service's own page.
 *     @type string $link_text Call to action under the excerpt.
 *     @type string $ph_mod    Extra .ph modifier classes for the photo box.
 * }
 */

$spec9_link      = isset( $args['link'] ) && $args['link'] ? $args['link'] : get_permalink();
$spec9_link_text = isset( $args['link_text'] ) ? $args['link_text'] : '';
$spec9_ph_mod    = isset( $args['ph_mod'] ) ? $args['ph_mod'] : '';
?>
<a href="<?php echo esc_url( $spec9_link ); ?>">
	<div>
		<div class="ph<?php echo esc_attr( $spec9_ph_mod ); ?>"><?php the_post_thumbnail( 'spec9-card' ); ?></div>
	</div>
	<h3><?php the_title(); ?></h3>
	<p><?php echo esc_html( get_the_excerpt() ); ?></p>
	<?php if ( $spec9_link_text ) : ?>
		<span><?php echo esc_html( $spec9_link_text ); ?></span>
	<?php endif; ?>
</a>
