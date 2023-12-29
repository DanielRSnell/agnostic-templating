<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Agnostic_Template_Element' ) ) {
  class Agnostic_Template_Element extends Tailor_Element {
      /**
       * Registers element settings, sections and controls.
       *
       * @access protected
       */
      protected function register_controls() {
          $this->add_section( 'template', array(
              'title'                 =>  __( 'Template', 'agnostic-templates' ),
              'priority'              =>  10,
          ) );

          $this->add_setting( 'id', array(
              'sanitize_callback'     =>  'tailor_sanitize_number',
          ) );

          $upper_limit = intval( apply_filters( 'agnostic_upper_limit', 200 ) );

          $args = array(
      			'post_type'				=> array( 'agnostic_template' ),
      			'post_status'			=> array( 'publish' ),
      			'order'						=> 'ASC',
      			'orderby'					=> 'menu_order',
      			'posts_per_page' 	=> $upper_limit,
      		);

          $query = get_posts( $args );

          $posts = array(
            '0' => __( 'Please select a template', 'agnostic-templates' )
          );

          foreach( $query as $post ) {
            $posts[$post->ID] = $post->post_title;
          }

          $this->add_control( 'id', array(
            'label'     => __( 'Template', 'agnostic-templates' ),
            'type'      => 'select',
            'choices'   => $posts,
            'priority'  =>  10,
            'section'   =>  'template',
          ) );

      }

  }
}
