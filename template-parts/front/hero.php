<?php
/**
 * Hero: the home page's opening section.
 *
 * The heading, paragraph and buttons are the page's own block content, so they
 * stay editable in the editor; the background comes from the featured image.
 *
 * @package Spec9
 *
 * @var array $args {
 *     @type int $home_id Front page ID.
 * }
 */

$spec9_home_id = isset( $args['home_id'] ) ? (int) $args['home_id'] : 0;
$spec9_bg_style = spec9_bg_img_style( get_post_thumbnail_id( $spec9_home_id ) );
?>
<section class="hero" id="top"<?php if ( $spec9_bg_style ) { printf( ' style="%s"', esc_attr( $spec9_bg_style ) ); } ?>>
	<div class="w">
		<div class="hero_text">
			<?php the_content(); ?>
		</div>
	</div>
</section>
