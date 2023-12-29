<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Agnostic_Tailor' ) ) {
  class Agnostic_Tailor {

    private $plugin_name;
  	private $version;

    public function __construct( $plugin_name, $version ) {

  		$this->plugin_name = $plugin_name;
  		$this->version = $version;

  	}

    public function load_tailor_element() {
      include 'class-agnostic-template-element.php';
    }

    public function register_tailor_element( $element_manager ) {
      $element_manager->add_element( 'agnostic_template', array(
    		'label'       =>  __( 'Agnostic Template', 'agnostic-templates' ),
    		'description' =>  __( 'Renders a Agnostic Template.', 'agnostic-templates' ),
    		'type'        =>  'content',
    		'badge'       =>  __( 'Template', 'agnostic-templates' ),
        'dynamic'     =>  true
    	) );
    }

  }
}
