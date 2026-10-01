<?php
/**
 * Section eyebrow label and heading.
 *
 * @package Spec9
 *
 * @var array $args {
 *     @type string $label   Eyebrow text.
 *     @type string $heading Heading text.
 * }
 */

$spec9_label   = isset( $args['label'] ) ? $args['label'] : '';
$spec9_heading = isset( $args['heading'] ) ? $args['heading'] : '';

if ( $spec9_label ) {
	printf( '<div class="lab">%s</div>', esc_html( $spec9_label ) );
}

if ( $spec9_heading ) {
	printf( '<h2>%s</h2>', esc_html( $spec9_heading ) );
}
