<?php
/**
 * Fallback template.
 *
 * Serves the service and project archives, and anything without a template
 * of its own — search results, categories, dates. The archives lead to the
 * single pages, which is how a visitor reaches them: the cards on the front
 * page open the quote modal instead.
 *
 * @package Spec9
 */

get_header();

$spec9_archive = '';

if ( is_post_type_archive( 'service' ) ) {
	$spec9_archive = 'service';
} elseif ( is_post_type_archive( 'project' ) ) {
	$spec9_archive = 'project';
}

if ( 'service' === $spec9_archive ) {
	$spec9_link_text = carbon_get_post_meta( (int) get_option( 'page_on_front' ), 'services_card_link_text' );

	if ( ! $spec9_link_text ) {
		$spec9_link_text = __( 'Request pricing', 'spec9' );
	}
}

if ( $spec9_archive ) :
	?>
	<section class="sec">
		<div class="w">
			<?php
			get_template_part(
				'template-parts/section-head',
				null,
				array( 'heading' => get_the_archive_title() )
			);
			?>
			<div class="<?php echo 'service' === $spec9_archive ? 'svc' : 'gal'; ?>">
				<?php
				$spec9_mods = 'service' === $spec9_archive ? array( '', ' b', ' c' ) : array( 'c2', 'd', 'e' );
				$spec9_i    = 0;

				while ( have_posts() ) :
					the_post();

					$spec9_args = array( 'ph_mod' => $spec9_mods[ $spec9_i % count( $spec9_mods ) ] );

					if ( 'service' === $spec9_archive ) {
						$spec9_args['link_text'] = $spec9_link_text;
					}

					get_template_part( 'template-parts/card-' . $spec9_archive, null, $spec9_args );

					++$spec9_i;
				endwhile;
				?>
			</div>
			<?php the_posts_pagination(); ?>
		</div>
	</section>
	<?php
else :
	?>
	<section class="sec">
		<div class="w entry-content">
			<?php if ( is_search() ) : ?>
				<h1><?php printf( esc_html__( 'Search results for “%s”', 'spec9' ), esc_html( get_search_query() ) ); ?></h1>
			<?php elseif ( is_archive() || is_home() ) : ?>
				<h1><?php echo wp_kses_post( get_the_archive_title() ); ?></h1>
			<?php else : ?>
				<h1><?php echo esc_html( get_bloginfo( 'name' ) ); ?></h1>
			<?php endif; ?>

			<?php if ( have_posts() ) : ?>
				<?php
				while ( have_posts() ) :
					the_post();
					?>
					<article>
						<h2><a href="<?php the_permalink(); ?>"><?php echo esc_html( get_the_title() ); ?></a></h2>
						<p><?php echo esc_html( get_the_excerpt() ); ?></p>
					</article>
					<?php
				endwhile;
				?>

				<?php the_posts_pagination(); ?>
			<?php else : ?>
				<p><?php esc_html_e( 'Nothing found here.', 'spec9' ); ?></p>
			<?php endif; ?>
		</div>
	</section>
	<?php
endif;

get_footer();
