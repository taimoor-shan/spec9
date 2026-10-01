<?php
/**
 * Customizer: global site content (logos, contact details, CTA, quote modal).
 *
 * Everything here is site-wide, so it lives in theme mods rather than in the
 * front page meta. All settings are prefixed with "spec9_" so they never
 * collide with core or plugin settings.
 *
 * @package Spec9
 */

/**
 * Register a text-like setting and control.
 *
 * @param WP_Customize_Manager $wp_customize Customizer instance.
 * @param string               $id           Setting id.
 * @param string               $label        Control label.
 * @param string               $section      Section id.
 * @param string               $sanitize     Sanitize callback.
 * @param string               $type         Control type.
 * @param string               $description  Optional control description.
 */
function spec9_add_text_setting( $wp_customize, $id, $label, $section, $sanitize = 'sanitize_text_field', $type = 'text', $description = '' ) {
	$wp_customize->add_setting(
		$id,
		array(
			'default'           => '',
			'sanitize_callback' => $sanitize,
			'transport'         => 'refresh',
		)
	);

	$wp_customize->add_control(
		$id,
		array(
			'label'       => $label,
			'description' => $description,
			'section'     => $section,
			'type'        => $type,
		)
	);
}

/**
 * Register an image setting and media control.
 *
 * @param WP_Customize_Manager $wp_customize Customizer instance.
 * @param string               $id           Setting id.
 * @param string               $label        Control label.
 * @param string               $section      Section id.
 * @param int                  $priority     Control priority.
 * @param string               $description  Optional control description.
 */
function spec9_add_media_setting( $wp_customize, $id, $label, $section, $priority = 10, $description = '' ) {
	$wp_customize->add_setting(
		$id,
		array(
			'default'           => '',
			'sanitize_callback' => 'absint',
			'transport'         => 'refresh',
		)
	);

	$wp_customize->add_control(
		new WP_Customize_Media_Control(
			$wp_customize,
			$id,
			array(
				'label'       => $label,
				'description' => $description,
				'section'     => $section,
				'mime_type'   => 'image',
				'priority'    => $priority,
			)
		)
	);
}

/**
 * Register all SPEC9 customizer settings.
 *
 * @param WP_Customize_Manager $wp_customize Customizer instance.
 */
function spec9_customize_register( $wp_customize ) {
	$wp_customize->add_panel(
		'spec9_panel',
		array(
			'title'       => __( 'SPEC9 Theme', 'spec9' ),
			'description' => __( 'Contact details, the call-to-action band and the quote modal.', 'spec9' ),
			'priority'    => 160,
		)
	);

	// Dark-mode logo sits in Site Identity, right below the core Logo control.
	spec9_add_media_setting(
		$wp_customize,
		'spec9_logo_dark',
		__( 'Dark-mode logo', 'spec9' ),
		'title_tagline',
		9,
		__( 'Used in the header while dark mode is active, and in the footer.', 'spec9' )
	);

	// Contact details.
	$wp_customize->add_section(
		'spec9_contact',
		array(
			'title' => __( 'Contact details', 'spec9' ),
			'panel' => 'spec9_panel',
		)
	);

	spec9_add_text_setting( $wp_customize, 'spec9_contact_phone', __( 'Phone', 'spec9' ), 'spec9_contact', 'sanitize_text_field' );
	spec9_add_text_setting( $wp_customize, 'spec9_contact_email', __( 'Email', 'spec9' ), 'spec9_contact', 'sanitize_email' );
	spec9_add_text_setting( $wp_customize, 'spec9_contact_address', __( 'Address', 'spec9' ), 'spec9_contact', 'sanitize_textarea_field', 'textarea' );

	// Call to action.
	$wp_customize->add_section(
		'spec9_cta',
		array(
			'title' => __( 'Call to action', 'spec9' ),
			'panel' => 'spec9_panel',
		)
	);

	spec9_add_text_setting( $wp_customize, 'spec9_cta_heading', __( 'Heading', 'spec9' ), 'spec9_cta' );
	spec9_add_media_setting( $wp_customize, 'spec9_cta_bg', __( 'Background image', 'spec9' ), 'spec9_cta' );

	spec9_add_text_setting( $wp_customize, 'spec9_cta_btn1_text', __( 'Button 1 text', 'spec9' ), 'spec9_cta' );
	spec9_add_text_setting( $wp_customize, 'spec9_cta_btn1_url', __( 'Button 1 link', 'spec9' ), 'spec9_cta', 'esc_url_raw', 'url', __( 'Use #quote to open the quote modal.', 'spec9' ) );

	spec9_add_text_setting( $wp_customize, 'spec9_cta_btn2_text', __( 'Button 2 text', 'spec9' ), 'spec9_cta' );
	spec9_add_text_setting( $wp_customize, 'spec9_cta_btn2_url', __( 'Button 2 link', 'spec9' ), 'spec9_cta', 'esc_url_raw', 'url', __( 'Use #quote to open the quote modal.', 'spec9' ) );

	// Quote modal.
	$wp_customize->add_section(
		'spec9_quote',
		array(
			'title' => __( 'Quote modal', 'spec9' ),
			'panel' => 'spec9_panel',
		)
	);

	spec9_add_text_setting( $wp_customize, 'spec9_quote_label', __( 'Eyebrow label', 'spec9' ), 'spec9_quote' );
	spec9_add_text_setting( $wp_customize, 'spec9_quote_heading', __( 'Heading', 'spec9' ), 'spec9_quote' );
	spec9_add_text_setting( $wp_customize, 'spec9_quote_lead', __( 'Intro text', 'spec9' ), 'spec9_quote', 'sanitize_textarea_field', 'textarea' );
	spec9_add_text_setting(
		$wp_customize,
		'spec9_quote_form_shortcode',
		__( 'Form shortcode', 'spec9' ),
		'spec9_quote',
		'sanitize_text_field',
		'text',
		__( 'Paste the Contact Form 7 shortcode, for example [contact-form-7 id="123"].', 'spec9' )
	);
}
add_action( 'customize_register', 'spec9_customize_register' );
