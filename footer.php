<?php
/**
 * Footer: the four-column footer, the legal bar and the quote dialog.
 *
 * @package Spec9
 */

$spec9_logo = (int) get_theme_mod( 'spec9_logo_dark' );

if ( ! $spec9_logo ) {
	$spec9_logo = (int) get_theme_mod( 'custom_logo' );
}

$spec9_phone   = get_theme_mod( 'spec9_contact_phone' );
$spec9_email   = get_theme_mod( 'spec9_contact_email' );
$spec9_address = get_theme_mod( 'spec9_contact_address' );
?>
<footer>
	<div class="w">
		<div class="g">
			<div>
				<?php
				echo spec9_logo_img( '', $spec9_logo ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_get_attachment_image() escapes its own attributes.
				?>
				<p><?php echo esc_html( get_bloginfo( 'description' ) ); ?></p>
			</div>
			<div>
				<h4><?php esc_html_e( 'Quick links', 'spec9' ); ?></h4>
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'footer',
						'container'      => false,
						'fallback_cb'    => false,
						'depth'          => 1,
					)
				);
				?>
			</div>
			<div>
				<h4><?php esc_html_e( 'Services', 'spec9' ); ?></h4>
				<ul>
					<?php
					// The four most-ordered services, as the static footer lists four.
					$spec9_services = new WP_Query(
						array(
							'post_type'      => 'service',
							'posts_per_page' => 4,
							'orderby'        => 'menu_order',
							'order'          => 'ASC',
							'no_found_rows'  => true,
						)
					);

					while ( $spec9_services->have_posts() ) :
						$spec9_services->the_post();
						?>
						<li><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></li>
						<?php
					endwhile;

					wp_reset_postdata();
					?>
				</ul>
			</div>
			<div>
				<h4><?php esc_html_e( 'Contact', 'spec9' ); ?></h4>
				<ul>
					<?php if ( $spec9_address ) : ?>
						<li><?php echo nl2br( esc_html( $spec9_address ) ); ?></li>
					<?php endif; ?>
					<?php if ( $spec9_phone ) : ?>
						<li><a href="<?php echo esc_url( spec9_tel_href( $spec9_phone ) ); ?>"><?php echo esc_html( $spec9_phone ); ?></a></li>
					<?php endif; ?>
					<?php if ( $spec9_email ) : ?>
						<li><?php echo spec9_email_link( $spec9_email ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- antispambot() returns entities that must not be escaped again. ?></li>
					<?php endif; ?>
				</ul>
			</div>
		</div>

		<div class="legal">
			<span><?php
				printf(
					/* translators: 1: current year, 2: site name. */
					esc_html__( '© %1$s %2$s. All rights reserved.', 'spec9' ),
					esc_html( wp_date( 'Y' ) ),
					esc_html( get_bloginfo( 'name' ) )
				);
			?></span>
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'legal',
					'container'      => false,
					'items_wrap'     => '<span class="legal-links">%3$s</span>',
					'fallback_cb'    => false,
					'depth'          => 1,
					'walker'         => new Spec9_Bare_Walker(),
				)
			);
			?>
		</div>
	</div>
</footer>
<?php
get_template_part( 'template-parts/quote-modal' );

wp_footer();
