<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Agnostic_Debug_Bar' ) ) {
  /**
   * @since 1.3.0
   */
  class Agnostic_Debug_Bar extends Debug_Bar_Panel {
    private $plugin_name;
  	private $version;

    private $renderings;

    private $conditions;

    public function __construct( $plugin_name, $version ) {
      $this->plugin_name = $plugin_name;
  		$this->version = $version;

  		parent::__construct();
  	}

    function init() {
  		$this->renderings = array();
  		$this->title( __( 'Agnostic Templates', 'agnostic-templates' ) );

      add_filter( 'agnostic_conditions', array( $this, 'set_conditions' ) );
      add_action( 'agnostic_rendering', array( $this, 'add_rendering' ), 10, 1 );
    }

    function prerender() {
			if ( ! is_admin() ) {
				$this->set_visible( true );
			} else {
				$this->set_visible( false );
			}
  	}

    function add_rendering( $rendering ) {
      $this->renderings[] = $rendering;
    }

    function set_conditions( $conditions ) {
      $this->conditions = $conditions;
      return $conditions;
    }

    function render() {
      if ( isset( $this->conditions ) && ! empty( $this->conditions ) ) {
        printf( '<h3>%1$s</h3>', __( 'Global Conditions', 'agnostic-templates' ) );

        echo '<p>';
        foreach( $this->conditions as $condition ) {
          printf( '%1$s<br>', $condition );
        }
        echo '</p><hr>';
      }
  		if ( isset( $this->renderings ) && ! empty( $this->renderings ) ) {
        $this->renderings = array_unique( $this->renderings, SORT_REGULAR );

        foreach( $this->renderings as $rendering ) {
          printf( '<h3><u>%2$s</u>: %1$s</h3>', $rendering['post_title'], __( 'Rendered on', 'agnostic-templates' ) );
          printf( '<p><strong>%3$s</strong><br>%1$s (ID: %2$s)', $rendering['template_name'], $rendering['template_id'], __( 'Template', 'agnostic-templates' ) );
          printf( '<p><strong>%2$s</strong><br>%1$s', ucfirst( var_export( $rendering['autoload'], true ) ), __( 'Autoload?', 'agnostic-templates' ) );
          printf( '<p><strong>%1$s</strong><br>', __( 'Matched conditions', 'agnostic-templates' ) );

          if ( empty( $rendering['conditions'] ) ) {
            _e( 'No conditions.', 'agnostic-templates' );
          }

          foreach( $rendering['conditions'] as $condition ) {
            printf( '%1$s<br>', $condition );
          }

          echo '</p>';

          echo '<hr>';
        }

  		} else {
        printf( '<h3>%1$s</h3>', __( 'No templates rendered', 'agnostic-templates' ) );
        _e( 'It seems no templates were rendered on this page.', 'agnostic-templates' );
      }
  	}
  }
}
