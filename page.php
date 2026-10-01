<?php
/**
 * Default page template.
 *
 * @package Spec9
 */

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<section class="sec">
		<div class="w entry-content">
			<h1><?php the_title(); ?></h1>
			<?php the_content(); ?>
		</div>
	</section>
	<?php
endwhile;

get_footer();
