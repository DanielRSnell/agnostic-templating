<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://agnosticblocks.com
 * @since      1.0.0
 *
 * @package    Agnostic_Templates
 * @subpackage Agnostic_Templates/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Agnostic_Templates
 * @subpackage Agnostic_Templates/public
 * @author     Daniel Snell <hello@agnosticblocks.com>
 */
class Agnostic_Templates_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	public $conditions = array();

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_sidebar_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Agnostic_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Agnostic_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/sidebar.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Agnostic_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Agnostic_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/agnostic-public.js', array( 'jquery' ), $this->version, false );

	}

	public function register_cpt() {
		$capabilities = apply_filters( 'agnostic_capabilites', 'switch_themes' );

		$labels = array(
			'name'                  => _x( 'Agnostic', 'Post Type General Name', 'agnostic-templates' ),
			'singular_name'         => _x( 'Agnostic Template', 'Post Type Singular Name', 'agnostic-templates' ),
			'menu_name'             => __( 'Agnostic', 'agnostic-templates' ),
			'name_admin_bar'        => __( 'Agnostic', 'agnostic-templates' ),
			'archives'              => __( 'Template Archives', 'agnostic-templates' ),
			'attributes'            => __( 'Template Attributes', 'agnostic-templates' ),
			'parent_item_colon'     => __( 'Parent Template:', 'agnostic-templates' ),
			'all_items'             => __( 'All Templates', 'agnostic-templates' ),
			'add_new_item'          => __( 'Add New Template', 'agnostic-templates' ),
			'add_new'               => __( 'Add New', 'agnostic-templates' ),
			'new_item'              => __( 'New Template', 'agnostic-templates' ),
			'edit_item'             => __( 'Edit Template', 'agnostic-templates' ),
			'update_item'           => __( 'Update Template', 'agnostic-templates' ),
			'view_item'             => __( 'View Template', 'agnostic-templates' ),
			'view_items'            => __( 'View Templates', 'agnostic-templates' ),
			'search_items'          => __( 'Search Template', 'agnostic-templates' ),
			'not_found'             => __( 'No templates found', 'agnostic-templates' ),
			'not_found_in_trash'    => __( 'No templates found in Trash', 'agnostic-templates' ),
			'insert_into_item'      => __( 'Insert into template', 'agnostic-templates' ),
			'uploaded_to_this_item' => __( 'Uploaded to this template', 'agnostic-templates' ),
			'items_list'            => __( 'Templates list', 'agnostic-templates' ),
			'items_list_navigation' => __( 'Templates list navigation', 'agnostic-templates' ),
			'filter_items_list'     => __( 'Filter templates list', 'agnostic-templates' ),
		);

		$args = array(
			'label'                 => __( 'Agnostic Template', 'agnostic-templates' ),
			'description'           => __( 'Agnostic for Content.', 'agnostic-templates' ),
			'labels'                => $labels,
			'supports'              => array( 'title', 'page-attributes' ),
			'hierarchical'          => true,
			'public'                => false,
			'show_ui'               => true,
			'show_in_menu'          => true,
			'menu_position'         => 100,
			'menu_icon'             => 'dashicons-editor-code',
			'show_in_admin_bar'     => true,
			'show_in_nav_menus'     => false,
			'can_export'            => true,
			'has_archive'           => false,
			'exclude_from_search'   => true,
			'publicly_queryable'    => false,
			'capability_type'       => 'page',
			'capabilities' => array(
		    'edit_post'          => $capabilities,
		    'read_post'          => $capabilities,
		    'delete_post'        => $capabilities,
		    'edit_posts'         => $capabilities,
		    'edit_others_posts'  => $capabilities,
		    'delete_posts'       => $capabilities,
		    'publish_posts'      => $capabilities,
		    'read_private_posts' => $capabilities
			)
		);

		register_post_type( 'agnostic_template', $args );
	}

	public function content( $content = NULL ) {
		// Let's not make an endless loop?!
		$priority = intval( apply_filters( 'agnostic_template_priority', 1984 ) );
		remove_all_filters( 'the_content', $priority );

		if ( empty( $this->conditions ) ) $this->get_conditions();

		if ( empty( $content ) ) {
			ob_start();
			the_content();
			$content = ob_get_clean();
		}

		return agnostic_shortcode( array( 'conditions' => $this->conditions, 'hook' => 'the_content' ), $content, NULL, $this );
	}

	public function wc_short_description( $content ) {
		$priority = intval( apply_filters( 'agnostic_template_priority', 1984 ) );
		remove_all_filters( 'woocommerce_short_description', $priority );

		if ( empty( $this->conditions ) ) $this->get_conditions();

		return agnostic_shortcode( array( 'conditions' => $this->conditions, 'hook' => 'woocommerce_short_description' ), $content, NULL, $this );
	}

	public function get_conditions() {
		$this->determine_conditions();

		$this->conditions = apply_filters( 'agnostic_conditions', $this->conditions );

		$this->conditions = array_reverse( $this->conditions );
	}

	public function determine_conditions() {
		$this->is_hierarchy();
		$this->is_taxonomy();
		$this->is_post_type_archive();
		$this->is_page_template();
	}

	function is_hierarchy() {
		if ( is_front_page() && ! is_home() ) {
			$this->conditions[] = 'static_front_page';
		}

		if ( ! is_front_page() && is_home() ) {
			$this->conditions[] = 'inner_posts_page';
		}

		if ( is_front_page() ) {
			$this->conditions[] = 'front_page';
		}

		if ( is_home() ) {
			$this->conditions[] = 'home';
		}

		if ( is_singular() ) {
			$this->conditions[] = 'singular';
		}

		if ( is_single() ) {
			$this->conditions[] = 'single';
		}

		if ( is_single() || is_singular() ) {
			$this->conditions[] = 'post-type-' . get_post_type();
			$this->conditions[] = get_post_type();

			// In Category conditions.
			$categories = get_the_category( get_the_ID() );

			if ( is_array( $categories ) && ! is_wp_error( $categories ) && ( 0 < count( $categories ) ) ) {
				foreach ( $categories as $k => $v ) {
					$this->conditions[] = 'in-term-' . $v->term_id;
				}
			}

			// Has Tag conditions.
			$tags = get_the_tags( get_the_ID() );

			if ( is_array( $tags ) && ! is_wp_error( $tags ) && ( 0 < count( $tags ) ) ) {
				foreach ( $tags as $k => $v ) {
					$this->conditions[] = 'has-term-' . $v->term_id;
				}
			}

			// Post format.
			$format = get_the_terms( get_the_ID(), 'post_format' );

			if ( ! empty( $format ) ) {
				$format = reset( $format );
				$this->conditions[] = 'is-term-' . $format->term_id;
			}

			// Post-specific condition.
			$this->conditions[] = 'post' . '-' . get_the_ID();
		}

		if ( is_search() ) {
			$this->conditions[] = 'search';
		}

		if ( is_home() ) {
			$this->conditions[] = 'home';
		}

		if ( is_front_page() ) {
			$this->conditions[] = 'front_page';
		}

		if ( is_archive() ) {
			$this->conditions[] = 'archive';
		}

		if ( is_author() ) {
			$this->conditions[] = 'author';
		}

		if ( is_date() ) {
			$this->conditions[] = 'date';
		}

		if ( is_404() ) {
			$this->conditions[] = '404';
		}
	}

	public function is_taxonomy () {
		if ( ( is_tax() || is_archive() ) && ! is_post_type_archive() ) {
			$obj = get_queried_object();

			if ( ! is_category() && ! is_tag() ) {
				$this->conditions[] = 'taxonomies';
			}

			if ( is_object( $obj ) ) {
				$this->conditions[] = 'archive-' . $obj->taxonomy;
				$this->conditions[] = 'term-' . $obj->term_id;
			}
		}
	}

	public function is_post_type_archive () {
		if ( is_post_type_archive() ) {

			$post_type = get_query_var( 'post_type' );
			if ( is_array( $post_type ) ){
				$post_type = reset( $post_type );
			}

			$this->conditions[] = 'post-type-archive-' . $post_type;
		}
	}

	public function is_page_template () {
		if ( is_singular() ) {
			global $post;
			$template = get_post_meta( $post->ID, '_wp_page_template', true );

			if ( $template != '' && $template != 'default' ) {
				$this->conditions[] = str_replace( '.php', '', 'page-template-' . $template );
			}
		}
	}

}