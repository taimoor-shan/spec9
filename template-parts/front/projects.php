<?php
/**
 * Projects: the bento grid of hand-picked projects.
 *
 * Each figure is a grid child, so the span classes (.a, .b2, .c2, .d, .e)
 * have to stay on the <figure> itself; the link to the project is a
 * full-cover anchor inside it.
 *
 * @package Spec9
 *
 * @var array $args {
 *     @type int $home_id Front page ID.
 * }
 */

$spec9_home_id  = isset( $args['home_id'] ) ? (int) $args['home_id'] : 0;
$spec9_featured = carbon_get_post_meta( $spec9_home_id, 'projects_featured' );
$spec9_ids      = $spec9_featured ? wp_list_pluck( $spec9_featured, 'id' ) : array();
$spec9_ids      = array_values( array_filter( array_map( 'intval', $spec9_ids ) ) );

if ( ! $spec9_ids ) {
	return;
}

$spec9_query = new WP_Query(
	array(
		'post_type'           => 'project',
		'post__in'            => $spec9_ids,
		'orderby'             => 'post__in',
		'posts_per_page'      => count( $spec9_ids ),
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
	)
);

if ( ! $spec9_query->have_posts() ) {
	return;
}

$spec9_mods = array( 'a', 'b b2', 'c c2', 'd', 'b e' );
?>
<section class="sec warm" id="projects">
	<div class="w">
		<?php
		get_template_part(
			'template-parts/section-head',
			null,
			array(
				'label'   => carbon_get_post_meta( $spec9_home_id, 'projects_label' ),
				'heading' => carbon_get_post_meta( $spec9_home_id, 'projects_heading' ),
			)
		);
		?>
		<div class="gal">
			<?php
			$spec9_index = 0;

			while ( $spec9_query->have_posts() ) :
				$spec9_query->the_post();

				get_template_part(
					'template-parts/card-project',
					null,
					array( 'ph_mod' => $spec9_mods[ $spec9_index % count( $spec9_mods ) ] )
				);

				++$spec9_index;
			endwhile;
			?>
		</div>
	</div>
</section>
<?php
wp_reset_postdata();
