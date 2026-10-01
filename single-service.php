<?php
/**
 * Single service.
 *
 * @package Spec9
 */

get_header();

while ( have_posts() ) :
	the_post();

	$spec9_bg_style = spec9_bg_img_style( get_post_thumbnail_id() );
	?>
	<section class="hero band"<?php if ( $spec9_bg_style ) { printf( ' style="%s"', esc_attr( $spec9_bg_style ) ); } ?>>
		<div class="w">
			<div class="hero_text">
				<h1><?php the_title(); ?></h1>
			</div>
		</div>
	</section>

	<div class="sec">
		<div class="w entry-content">
			<?php the_content(); ?>
		</div>
	</div>
	<?php
endwhile;

get_template_part( 'template-parts/cta' );

get_footer();
