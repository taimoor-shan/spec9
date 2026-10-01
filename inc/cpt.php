<?php
/**
 * Custom post types: service and project.
 *
 * @package Spec9
 */

/**
 * Register the service and project post types.
 */
function spec9_register_post_types() {
	register_post_type(
		'service',
		array(
			'labels'       => array(
				'name'          => __( 'Services', 'spec9' ),
				'singular_name' => __( 'Service', 'spec9' ),
				'add_new_item'  => __( 'Add New Service', 'spec9' ),
				'edit_item'     => __( 'Edit Service', 'spec9' ),
				'search_items'  => __( 'Search Services', 'spec9' ),
				'not_found'     => __( 'No services found.', 'spec9' ),
			),
			'public'       => true,
			'has_archive'  => true,
			'rewrite'      => array(
				'slug'       => 'services',
				'with_front' => false,
			),
			'supports'     => array( 'title', 'editor', 'thumbnail', 'excerpt', 'page-attributes' ),
			'show_in_rest' => true,
			'menu_icon'    => 'dashicons-hammer',
			'menu_position' => 20,
		)
	);

	register_post_type(
		'project',
		array(
			'labels'       => array(
				'name'          => __( 'Projects', 'spec9' ),
				'singular_name' => __( 'Project', 'spec9' ),
				'add_new_item'  => __( 'Add New Project', 'spec9' ),
				'edit_item'     => __( 'Edit Project', 'spec9' ),
				'search_items'  => __( 'Search Projects', 'spec9' ),
				'not_found'     => __( 'No projects found.', 'spec9' ),
			),
			'public'       => true,
			'has_archive'  => true,
			'rewrite'      => array(
				'slug'       => 'projects',
				'with_front' => false,
			),
			'supports'     => array( 'title', 'editor', 'thumbnail', 'excerpt', 'page-attributes' ),
			'show_in_rest' => true,
			'menu_icon'    => 'dashicons-images-alt2',
			'menu_position' => 21,
		)
	);
}
add_action( 'init', 'spec9_register_post_types' );
