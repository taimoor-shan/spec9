<?php
/**
 * Services: the card grid, built from the service post type.
 *
 * The six photo-box modifiers come from the static site rather than a
 * repeating pattern — the third and fourth cards share '.ph c' and the
 * fifth repeats the first. The cards link to the quote modal, so the
 * archive and footer are what lead to the single service pages.
 *
 * @package Spec9
 *
 * @var array $args {
 *     @type int $home_id Front page ID.
 * }
 */

$spec9_home_id   = isset( $args['home_id'] ) ? (int) $args['home_id'] : 0;
$spec9_link_text = carbon_get_post_meta( $spec9_home_id, 'services_card_link_text' );

if ( ! $spec9_link_text ) {
	$spec9_link_text = __( 'Request pricing', 'spec9' );
}

$spec9_query = new WP_Query(
	array(
		'post_type'      => 'service',
		'posts_per_page' => -1,
		'orderby'        => 'menu_order',
		'order'          => 'ASC',
		'no_found_rows'  => true,
	)
);

if ( ! $spec9_query->have_posts() ) {
	return;
}

$spec9_mods = array( '', ' b', ' c', ' c', '', ' b' );
?>
<section class="sec" id="services">
	<div class="w">
		<?php
		get_template_part(
			'template-parts/section-head',
			null,
			array(
				'label'   => carbon_get_post_meta( $spec9_home_id, 'services_label' ),
				'heading' => carbon_get_post_meta( $spec9_home_id, 'services_heading' ),
			)
		);
		?>
		<div class="svc">
			<?php
			$spec9_index = 0;

			while ( $spec9_query->have_posts() ) :
				$spec9_query->the_post();

				get_template_part(
					'template-parts/card-service',
					null,
					array(
						'link'      => '#quote',
						'link_text' => $spec9_link_text,
						'ph_mod'    => $spec9_mods[ $spec9_index % count( $spec9_mods ) ],
					)
				);

				++$spec9_index;
			endwhile;
			?>
		</div>
	</div>
</section>
<?php
wp_reset_postdata();
