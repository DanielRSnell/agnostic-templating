<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! function_exists( 'agnostic_fancy_implode') ) {

	function agnostic_fancy_implode( $array, $word = false ) {
		if ( ! is_string( $word ) ) $word = __( 'and', 'agnostic-templates' );

		$last  = array_slice( $array, -1 );
		$first = implode( ', ', array_slice( $array, 0, -1 ) );
		$both  = array_filter( array_merge( array( $first ), $last ), 'strlen' );

		return implode( sprintf( ' %s ', $word ), $both );
	}
}

if ( ! function_exists( 'agnostic_allowed_hooks' ) ) {

	function agnostic_allowed_hooks( $hook ) {
		$allowed_hooks = (array) apply_filters( 'agnostic_data_sources', array(
			'the_content'	=> __( 'the_content', 'agnostic-templates' ),
			'woocommerce_short_description'	=> __( 'woocommerce_short_description', 'Agnostic_Templates' )
		) );

		if ( array_key_exists( $hook, $allowed_hooks ) ) {
			return $hook;
		} else {
			return 'the_content';
		}
	}

}

if ( ! function_exists( 'agnostic_allowed_methods' ) ) {

	function agnostic_allowed_methods( $method ) {
		$allowed_methods = (array) apply_filters( 'agnostic_data_sources', array(
			'shortcode'	=> __( 'Shortcode', 'agnostic-templates' ),
			'autoload'	=> __( 'Autoload', 'agnostic-templates'),
			'prerender'	=> __( 'Prerender', 'agnostic-templates' )
		) );

		if ( array_key_exists( $method, $allowed_methods ) ) {
			return $method;
		} else {
			return 'shortcode';
		}
	}

}

if ( ! function_exists( 'agnostic_allowed_data_sources' ) ) {

	function agnostic_allowed_data_sources( $data_source ) {
		$allowed_data_sources = (array) apply_filters( 'agnostic_data_sources', array(
			'single'	=> __( 'Single', 'agnostic' ),
			'query'		=> __( 'Query', 'agnostic' )
		) );

		if ( array_key_exists( $data_source, $allowed_data_sources ) ) {
			return $data_source;
		} else {
			return 'single';
		}
	}

}

if ( ! function_exists( 'agnostic_allowed_html' ) ) {

	function agnostic_allowed_html() {
		$allowed = wp_kses_allowed_html( 'post' );

		// iframe
		$allowed['iframe'] = array(
			'src'             => array(),
			'height'          => array(),
			'width'           => array(),
			'frameborder'     => array(),
			'allowfullscreen' => array(),
		);

		// form fields - input
		$allowed['input'] = array(
			'class' => array(),
			'id'    => array(),
			'name'  => array(),
			'value' => array(),
			'type'  => array(),
		);

		// select
		$allowed['select'] = array(
			'class'  => array(),
			'id'     => array(),
			'name'   => array(),
			'value'  => array(),
			'type'   => array(),
		);

		// select options
		$allowed['option'] = array(
			'selected' => array(),
		);

		// style
		$allowed['style'] = array(
			'types' => array(),
		);

		return $allowed;
	}

}

if ( ! function_exists( 'agnostic_kses' ) ) {
	function agnostic_kses( $content ) {
		$content = preg_replace( '#<script(.*?)>(.*?)</script>#is', '', $content );
		// $content = wp_kses( $content, agnostic_allowed_html() );
		// return trim( $content );
		return $content;
	}
}

function disable_kses_for_agnostic_template( $post_id ) {
    // Check if the post being saved is of the 'agnostic_template' post type
    if ( 'agnostic_template' === get_post_type( $post_id ) ) {
        kses_remove_filters(); // Disable kses filters
    }
}

function reenable_kses_after_agnostic_template( $post_id ) {
    // Check if the post being saved is of the 'agnostic_template' post type
    if ( 'agnostic_template' === get_post_type( $post_id ) ) {
        kses_init_filters(); // Re-enable kses filters
    }
}

add_action( 'save_post', 'disable_kses_for_agnostic_template', 10, 1 );
add_action( 'saved_post', 'reenable_kses_after_agnostic_template', 10, 1 );