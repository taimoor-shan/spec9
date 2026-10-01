<?php
/**
 * Front page: the one-page marketing layout.
 *
 * The section order matches the static site. Each partial reads its own
 * Carbon Fields values, so an empty section drops out on its own; the home
 * page ID is passed down because the card loops below change the current post.
 *
 * @package Spec9
 */

get_header();

$spec9_home_id = (int) get_queried_object_id();

while ( have_posts() ) :
	the_post();

	get_template_part( 'template-parts/front/hero', null, array( 'home_id' => $spec9_home_id ) );
endwhile;

get_template_part( 'template-parts/front/industries', null, array( 'home_id' => $spec9_home_id ) );
get_template_part( 'template-parts/front/about', null, array( 'home_id' => $spec9_home_id ) );
get_template_part( 'template-parts/front/services', null, array( 'home_id' => $spec9_home_id ) );
get_template_part( 'template-parts/front/projects', null, array( 'home_id' => $spec9_home_id ) );
get_template_part( 'template-parts/front/stats', null, array( 'home_id' => $spec9_home_id ) );
get_template_part( 'template-parts/front/testimonials', null, array( 'home_id' => $spec9_home_id ) );

get_template_part( 'template-parts/cta' );

get_footer();
