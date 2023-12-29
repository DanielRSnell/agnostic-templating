<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://agnosticblocks.com
 * @since      1.0.0
 *
 * @package    Agnostic_Templates
 * @subpackage Agnostic_Templates/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Agnostic_Templates
 * @subpackage Agnostic_Templates/admin
 * @author     Daniel Snell <hello@agnosticblocks.com>
 */
class Agnostic_Templates_Admin {

	private $plugin_name;
	private $version;

	public $conditions_reference = array();
	public $conditions_headings = array();

	public $post_types = array();

	public $upper_limit;

	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->upper_limit = intval( apply_filters( 'agnostic_upper_limit', 200 ) );

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		global $post_type;
    if( 'agnostic_template' == $post_type )
			wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/agnostic-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		global $post_type;
    if( 'agnostic_template' == $post_type ) {
			wp_enqueue_script( 'vue', plugin_dir_url( __FILE__ ) . 'js/vue.min.js', array(), $this->version, false );

			wp_enqueue_script( 'ace', plugin_dir_url( __FILE__ ) . 'js/ace/ace.js', array(), $this->version, false );

	    	wp_enqueue_script( 'ace-mode-twig', plugin_dir_url( __FILE__ ) . 'js/ace/mode-twig.js', array( 'ace' ), $this->version, false );

			wp_enqueue_script( 'ace-mode-json', plugin_dir_url( __FILE__ ) . 'js/ace/mode-json.js', array( 'ace' ), $this->version, false );

			wp_enqueue_script( 'ace-ext-langtools', plugin_dir_url( __FILE__ ) . 'js/ace/ext-language_tools.js', array( 'ace' ), $this->version, false );

			wp_enqueue_script( 'ace-theme-monokai', plugin_dir_url( __FILE__ ) . 'js/ace/theme-monokai.js', array( 'ace' ), $this->version, false );

			wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/agnostic-admin.js', array( 'vue', 'ace', 'ace-mode-twig', 'ace-theme-monokai', 'ace-mode-json' ), $this->version, false );
		}

	}

	public function get_post_types() {
		if ( empty( $this->post_types ) ) {
			$this->post_types = (array) apply_filters( 'agnostic_prerender_post_types', array() );
		}
		return $this->post_types;
	}

	public function prerender_post_types( $post_types ) {
		$public_post_types = get_post_types( array(
			'public'	=> true
		), 'objects' );

		foreach( $public_post_types as $post_type ) {
			if ( post_type_supports( $post_type->name, 'agnostic_prerender' ) || $post_type->name == 'post' || $post_type->name == 'page' ) {
				$post_types[$post_type->name] = $post_type->labels->name;
			};
		}

		$post_types = array_unique( $post_types );

		return $post_types;
	}

	private function setup_default_conditions_reference() {
		$conditions_headings = array();
		$conditions = array();

		// Get an array of the different post status labels, in case we need it later.
		$post_statuses = get_post_statuses();

		$args = array(
					'show_ui' => true,
					'public' => true,
					'publicly_queryable' => true,
					'_builtin' => false
					);

		$post_types = get_post_types( $args, 'object' );

		// Set certain post types that aren't allowed to have custom sidebars.
		$disallowed_types = array( 'slide' );

		// Make the array filterable.
		$disallowed_types = apply_filters( 'agnostic_disallowed_post_types', $disallowed_types );

		if ( count( $post_types ) ) {
			foreach ( $post_types as $k => $v ) {
				if ( in_array( $k, $disallowed_types ) ) {
					unset( $post_types[$k] );
				}
			}
		}

		// Add per-post support for any post type that supports it.
		$args = array(
				'show_ui' => true,
				'public' => true,
				'publicly_queryable' => true,
				'_builtin' => true
				);

		$built_in_post_types = get_post_types( $args, 'object' );

		foreach ( $built_in_post_types as $k => $v ) {
			if ( $k == 'post' ) {
				$post_types[$k] = $v;
				break;
			}
		}

		foreach ( $post_types as $k => $v ) {
			if ( ! post_type_supports( $k, 'agnostic-template' ) ) { continue; }

			$conditions_headings[$k] = $v->labels->name;

			$query_args = array( 'numberposts' => intval( $this->upper_limit ), 'post_type' => $k, 'meta_key' => '_enable_sidebar', 'meta_value' => 'yes', 'meta_compare' => '=', 'post_status' => 'any', 'suppress_filters' => 'false' );

			$posts = get_posts( $query_args );

			if ( count( $posts ) > 0 ) {
				foreach ( $posts as $i => $j ) {
					$label = $j->post_title;
					if ( 'publish' != $j->post_status ) {
						$label .= ' <strong>(' . $post_statuses[$j->post_status] . ')</strong>';
					}
					$conditions[$k]['post' . '-' . $j->ID] = array(
										'label' => $label,
										'description' => sprintf( __( 'A custom sidebar for "%s"', 'agnostic-templates' ), esc_attr( $j->post_title ) )
										);
				}
			}
		}

		// Page Templates
		$conditions['templates'] = array();

		$page_templates = get_page_templates();

		if ( count( $page_templates ) > 0 ) {

			$conditions_headings['templates'] = __( 'Page Templates', 'agnostic-templates' );

			foreach ( $page_templates as $k => $v ) {
				$token = str_replace( '.php', '', 'page-template-' . $v );
				$conditions['templates'][$token] = array(
									'label' => $k,
									'description' => sprintf( __( 'The "%s" page template', 'agnostic-templates' ), $k )
									);
			}
		}

		// Post Type Archives
		$conditions['post_types'] = array();

		if ( count( $post_types ) > 0 ) {

			$conditions_headings['post_types'] = __( 'Post Types', 'agnostic-templates' );

			foreach ( $post_types as $k => $v ) {
				$token = 'post-type-archive-' . $k;

				if ( $v->has_archive ) {
					$conditions['post_types'][$token] = array(
										'label' => sprintf( __( '"%s" Post Type Archive', 'agnostic-templates' ), $v->labels->name ),
										'description' => sprintf( __( 'The "%s" post type archive', 'agnostic-templates' ), $v->labels->name )
										);
				}
			}

			foreach ( $post_types as $k => $v ) {
				$token = 'post-type-' . $k;
				$conditions['post_types'][$token] = array(
									'label' => sprintf( __( 'Each Individual %s', 'agnostic-templates' ), $v->labels->singular_name ),
									'description' => sprintf( __( 'Entries in the "%s" post type', 'agnostic-templates' ), $v->labels->name )
									);
			}

		}

		// Taxonomies and Taxonomy Terms
		$conditions['taxonomies'] = array();

		$args = array(
					'public' => true
					);

		$taxonomies = get_taxonomies( $args, 'objects' );

		if ( count( $taxonomies ) > 0 ) {

			$conditions_headings['taxonomies'] = __( 'Taxonomy Archives', 'agnostic-templates' );

			foreach ( $taxonomies as $k => $v ) {
				$taxonomy = $v;

				if ( $taxonomy->public == true ) {
					$conditions['taxonomies']['archive-' . $k] = array(
										'label' => esc_html( $taxonomy->labels->name ) . ' (' . esc_html( $k ) . ')',
										'description' => sprintf( __( 'The default "%s" archives', 'agnostic-templates' ), strtolower( $taxonomy->labels->name ) )
										);

					// Setup each individual taxonomy's terms as well.
					$conditions_headings['taxonomy-' . $k] = $taxonomy->labels->name;
					$terms = get_terms( $k );
					if ( count( $terms ) > 0 ) {
						$conditions['taxonomy-' . $k] = array();
						foreach ( $terms as $i => $j ) {
							$conditions['taxonomy-' . $k]['term-' . $j->term_id] = array( 'label' => esc_html( $j->name ), 'description' => sprintf( __( 'The %s %s archive', 'agnostic-templates' ), esc_html( $j->name ), strtolower( $taxonomy->labels->name ) ) );
							if ( $k == 'category' ) {
								$conditions['taxonomy-' . $k]['in-term-' . $j->term_id] = array( 'label' => sprintf( __( 'All posts in "%s"', 'agnostic-templates' ), esc_html( $j->name ) ), 'description' => sprintf( __( 'All posts in the %s %s archive', 'agnostic-templates' ), esc_html( $j->name ), strtolower( $taxonomy->labels->name ) ) );
							}
							if ( $k == 'post_tag' ) {
								$conditions['taxonomy-' . $k]['has-term-' . $j->term_id] = array( 'label' => sprintf( __( 'All posts tagged "%s"', 'agnostic-templates' ), esc_html( $j->name ) ), 'description' => sprintf( __( 'All posts tagged %s', 'agnostic-templates' ), esc_html( $j->name ) ) );
							}
							if ( $k == 'post_format' ) {
								$conditions['taxonomy-' . $k]['is-term-' . $j->term_id] = array( 'label' => sprintf( __( 'All posts with "%s" post format', 'agnostic-templates' ), esc_html( $j->name ) ), 'description' => sprintf( __( 'All posts with %s post format', 'agnostic-templates' ), esc_html( $j->name ) ) );
							}
						}
					}

				}
			}
		}

		$conditions_headings['hierarchy'] = __( 'Template Hierarchy', 'agnostic-templates' );

		$conditions['hierarchy']['page'] = array(
									'label' => __( 'Pages', 'agnostic-templates' ),
									'description' => __( 'Displayed on all pages that don\'t have a more specific widget area.', 'agnostic-templates' )
									);

		$conditions['hierarchy']['search'] = array(
									'label' => __( 'Search Results', 'agnostic-templates' ),
									'description' => __( 'Displayed on search results screens.', 'agnostic-templates' )
									);

		$conditions['hierarchy']['home'] = array(
									'label' => __( 'Default "Your Latest Posts" Screen', 'agnostic-templates' ),
									'description' => __( 'Displayed on the default "Your Latest Posts" screen.', 'agnostic-templates' )
									);

		$conditions['hierarchy']['front_page'] = array(
									'label' => __( 'Front Page', 'agnostic-templates' ),
									'description' => __( 'Displayed on any front page, regardless of the settings under the "Settings -> Reading" admin screen.', 'agnostic-templates' )
									);

		$conditions['hierarchy']['single'] = array(
									'label' => __( 'Single Entries', 'agnostic-templates' ),
									'description' => __( 'Displayed on single entries of any public post type other than "Pages".', 'agnostic-templates' )
									);

		$conditions['hierarchy']['archive'] = array(
									'label' => __( 'All Archives', 'agnostic-templates' ),
									'description' => __( 'Displayed on all archives (category, tag, taxonomy, post type, dated, author and search).', 'agnostic-templates' )
									);

		$conditions['hierarchy']['author'] = array(
									'label' => __( 'Author Archives', 'agnostic-templates' ),
									'description' => __( 'Displayed on all author archive screens (that don\'t have a more specific sidebar).', 'agnostic-templates' )
									);

		$conditions['hierarchy']['date'] = array(
									'label' => __( 'Date Archives', 'agnostic-templates' ),
									'description' => __( 'Displayed on all date archives.', 'agnostic-templates' )
									);

		$conditions['hierarchy']['404'] = array(
									'label' => __( '404 Error Screens', 'agnostic-templates' ),
									'description' => __( 'Displayed on all 404 error screens.', 'agnostic-templates' )
									);

		$this->conditions_headings = (array) apply_filters( 'agnostic_conditions_headings', $conditions_headings );
		$this->conditions_reference = (array) apply_filters( 'agnostic_conditions_reference', $conditions );
	}

	public function add_metaboxes() {
		add_meta_box(
	    'agnostic_template_code-mb',
	    __( 'Template Code', 'agnostic-templates' ),
	    array( $this, 'render_code_mb' ),
	    'agnostic_template',
	    'advanced',
	    'high'
    );

    add_meta_box(
	    'agnostic-template-attributes-mb',
	    __( 'Conditions', 'agnostic-templates' ),
	    array( $this, 'render_attributes_mb' ),
	    'agnostic_template',
	    'side',
	    'default'
    );

		add_meta_box(
	    'agnostic-template-data-mb',
	    __( 'Data', 'agnostic-templates' ),
	    array( $this, 'render_data_mb' ),
	    'agnostic_template',
	    'advanced',
	    'default'
    );

		foreach( $this->get_post_types() as $post_type => $label ) {
			add_meta_box(
		    'agnostic-template-prerender-mb',
		    __( 'Prerender Template', 'agnostic-templates' ),
		    array( $this, 'prerender_template_mb' ),
		    $post_type,
		    'side',
		    'default'
	    );
		}
	}

	public function prerender_template_mb( $post, $metabox ) {
		wp_nonce_field( 'agnostic_template_prerender_nonce', 'agnostic_template_prerender_nonce_field' );

		$prerender_template_value = (int) get_post_meta( $post->ID, '_agnostic_prerender_template', true );

		global $post;

		$original_post = $post;

		// Get all the relevant templates.
		$args = array(
			'post_type'		=> 'agnostic_template',
			'meta_query' 	=> array(
				array(
					'key'				=> '_agnostic_attribute_method',
					'value'			=> 'prerender',
					'compare' 	=> '='
				)
			),
			'orderby'		=> 'menu_order, title',
			'order'			=> 'ASC'
		);

		$query = new WP_Query( $args );

		if ( $query->have_posts() ) {
			?>
<p class="agnostic_prerender_template_wrapper">
    <label for="agnostic_prerender_template"
        class="agnostic_prerender_template_label"><?php _e( 'Select template', 'agnostic-templates' ); ?></label>
</p>
<select name="agnostic_prerender_template" id="agnostic_prerender_template">
    <option value="0" <?php if ( $prerender_template_value == 0 ) echo ' selected="selected"'; ?>>
        <?php _e( 'None', 'agnostic-templates' ); ?>
    </option>
    <?php
			while ( $query->have_posts() ) :  $query->the_post(); ?>
    <option value="<?php echo get_the_ID(); ?>"
        <?php if ( $prerender_template_value == get_the_ID() ) echo ' selected="selected"'; ?>>
        <?php echo esc_attr( get_the_title() ); ?>
    </option>
    <?php endwhile; ?>
</select>
<p class="agnostic_prerender_warning">
    <?php _e( '<b>Warning:</b> This will replace the current content upon saving/updating.', 'agnostic-templates' ); ?>
</p>
<?php
			wp_reset_postdata();
			wp_reset_query();

		} else {

			printf( '<p>%1$s</p>', __( 'No templates to prerender.', 'agnostic-templates' ) );

		}

		$post = $original_post;

		setup_postdata( $post );
	}

	public function save_prerender_template( $post_id, $post, $update ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
      return $post_id;
    }

		if ( ! isset( $_POST['post_type'] ) ) {
			return $post_id;
		}

    if ( ! array_key_exists( $_POST['post_type'], $this->get_post_types() ) ) {
      return $post_id;
    }

		if ( isset( $_POST['agnostic_template_prerender_nonce_field'] ) && wp_verify_nonce( $_POST['agnostic_template_prerender_nonce_field'], 'agnostic_template_prerender_nonce' ) ) {

			update_post_meta( $post_id, '_agnostic_prerender_template', filter_var( $_POST['agnostic_prerender_template'], FILTER_SANITIZE_NUMBER_INT ) );

		}
	}

	public function render_the_prerendered_template( $post_id, $post, $update ) {
		if ( wp_is_post_revision( $post_id ) )
			return $post_id;

		if ( ! array_key_exists( 'agnostic_prerender_template', $_POST ) )
			return $post_id;

		$template_id = filter_var( $_POST['agnostic_prerender_template'], FILTER_SANITIZE_NUMBER_INT );

		if ( $template_id == 0 )
			return $post_id;

		$post_data = array(
			'ID'						=> $post_id,
			'post_content'	=> agnostic_shortcode( array( 'id' => $template_id ), null, 'agnostic_template', $this )
		);

		wp_update_post( $post_data, true );

		return $post_id;
	}


public function render_code_mb( $post, $metabox ) {
		wp_nonce_field( 'agnostic_template_code_nonce', 'agnostic_template_code_nonce_field' );
    $code_template_value = agnostic_kses( get_post_meta( $post->ID, '_agnostic_template_code', true ) );
    ?>
<div id="agnostic-editor">
    <div id="agnostic_code_editor" v-cloak>{{ editorCode }}</div>
    <input type="hidden" name="agnostic_template_code" id="agnostic_template_code" v-model="code">
    <p><?php printf( __( 'The above code is rendered with Timber. Please read the <a href="%1$s" target="_blank">Timber documentation</a> for further information.', 'agnostic-templates' ), 'https://timber.github.io/docs/' ); ?>
    </p>
</div>
<script>
window.agnosticEditorCode = `<?php echo $code_template_value; ?>`;
</script>
<?php
	}


  public function render_attributes_mb( $post, $metabox ) {
    wp_nonce_field( 'agnostic_attribute_nonce', 'agnostic_attribute_nonce_field' );

		if ( count( $this->conditions_reference ) <= 0 )
			$this->setup_default_conditions_reference();

		$selected_conditions = get_post_meta( $post->ID, '_agnostic_condition', false );

    $available_placements = array(
      array(
        'value' => 'prepend',
        'label' => __( 'Above content', 'agnostic-templates' )
      ),
      array(
        'value' => 'append',
        'label' => __( 'Below content', 'agnostic-templates' )
      ),
      array(
        'value' => 'replace',
        'label' => __( 'Replace content', 'agnostic-templates' )
      )
    );

    $attr_placement_value = esc_js( get_post_meta( $post->ID, '_agnostic_attribute_placement', true ) );

		if ( empty( $attr_placement_value ) ) {
			$attr_placement_value = 'replace';
		}

		$available_hooks = array(
      'the_content' => __( 'the_content', 'agnostic-templates' ),
      'woocommerce_short_description' => __( 'woocommerce_short_description', 'agnostic-templates' )
    );

    $attr_hook_value = esc_js( agnostic_allowed_hooks( get_post_meta( $post->ID, '_agnostic_attribute_hook', true ) ) );

		if ( empty( $attr_hook_value ) ) {
			$attr_hook_value = 'the_content';
		}

		$available_methods = array(
			'shortcode'	=> __( 'Shortcode', 'agnostic-templates' ),
			'autoload'	=> __( 'Autoload', 'agnostic-templates'),
			'prerender'	=> __( 'Prerender', 'agnostic-templates' )
		);

		$attr_method_value = esc_js( agnostic_allowed_methods( get_post_meta( $post->ID, '_agnostic_attribute_method', true ) ) );

		if ( empty( $attr_method_value ) ) {
			$attr_method_value = 'shortcode';
		}
		?>
<div id="agnostic-conditions">
    <div class="agnostic-wrapper" v-cloak>
        <p class="agnostic_attribute_method_wrapper">
            <label for="agnostic_attribute_method"
                class="agnostic_attribute_method_label"><?php _e( 'Method', 'agnostic-templates' ); ?></label>
        </p>
        <select name="agnostic_attribute_method" id="agnostic_attribute_method" v-model="method">
            <?php foreach( $available_methods as $value => $method ) : ?>
            <option value="<?php echo esc_attr( $value ); ?>">
                <?php echo esc_attr( $method ); ?>
            </option>
            <?php endforeach; ?>
        </select>
        <div class="agnostic_placement" v-if="method=='autoload' || method=='shortcode'">
            <p class="agnostic_attribute_placement_wrapper">
                <label for="agnostic_attribute_placement"
                    class="agnostic_attribute_placement_label"><?php _e( 'Placement', 'agnostic-templates' ); ?></label>
            </p>
            <select name="agnostic_attribute_placement" id="agnostic_attribute_placement" v-model="placement">
                <?php foreach( $available_placements as $placement ) : ?>
                <option value="<?php echo esc_attr( $placement['value'] ); ?>">
                    <?php echo esc_attr( $placement['label'] ); ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="agnostic_hook" v-if="method=='autoload'">
            <p class="agnostic_attribute_hook_wrapper">
                <label for="agnostic_attribute_hook"
                    class="agnostic_attribute_hook_label"><?php _e( 'Hook', 'agnostic-templates' ); ?></label>
            </p>
            <select name="agnostic_attribute_hook" id="agnostic_attribute_hook" v-model="hook">
                <?php foreach( $available_hooks as $value => $hook ) : ?>
                <option value="<?php echo esc_attr( $value ); ?>">
                    <?php echo esc_attr( $hook ); ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="autoloaded_options" v-if="method=='autoload'">
            <h3><?php _e( 'Load on...', 'agnostic-templates' ); ?></h3>
            <div id="autoload-conditions">
                <?php foreach( $this->conditions_headings as $k => $heading ) : ?>
                <?php if ( array_key_exists( $k, $this->conditions_reference ) ) : ?>
                <h4><?php echo $heading; ?></h4>
                <div>
                    <?php foreach( $this->conditions_reference[$k] as $i => $condition ) : ?>
                    <p>
                        <label title="<?php echo esc_attr( $condition['description'] ); ?>"><input type="checkbox"
                                name="agnostic_conditions[]" value="<?php echo $i; ?>" <?php
											if ( in_array( $i, $selected_conditions ) ) {
												echo ' checked="checked"';
											}
											?>> <?php echo $condition['label']; ?></label>
                    </p>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="agnostic_prerender" v-if="method=='prerender'">
            <h4><?php _e( 'Instructions', 'agnostic-templates' ); ?></h4>
            <p>Go to your
                <?php echo agnostic_fancy_implode( $this->get_post_types(), __( 'or', 'agnostic-templates' ) ); ?> and
                select this template to prerender the template upon saving.</p>
        </div>
        <div class="agnostic_shortcode" v-if="method=='shortcode'">
            <h4><?php _e( 'Instructions', 'agnostic-templates' ); ?></h4>
            <p>Use this shortcode where you want this template: <b>[agnostic_template
                    id=&quot;<?php echo $post->ID; ?>&quot;][/agnostic_template]</b></p>
        </div>
    </div>
</div>
<script>
window.agnosticConditions = {
    placement: '<?php echo $attr_placement_value; ?>',
    hook: '<?php echo $attr_hook_value; ?>',
    method: '<?php echo $attr_method_value; ?>'
};
</script>
<?php
  }

	public function render_data_mb( $post, $metabox ) {
    wp_nonce_field( 'agnostic_data_nonce', 'agnostic_data_nonce_field' );

		$data_sources = array(
			'single'	=> __( 'Single', 'agnostic-templates' ),
			'query'		=> __( 'Query', 'agnostic-templates' )
		);
    $data_source_value = esc_js( get_post_meta( $post->ID, '_agnostic_data_source', true ) );

		if( empty( $data_source_value ) ) {
			$data_source_value = 'single';
		}

		$data_query_value = json_encode( get_post_meta( $post->ID, '_agnostic_data_query', true ), JSON_PRETTY_PRINT );

		if ( $data_query_value == '""' ) {
			$data_query_value = json_encode(
				array(
					'post_type'              => array( 'post' ),
					'post_status'            => array( 'publish' ),
					'posts_per_page'         => '10',
					'order'                  => 'DESC',
					'orderby'                => 'date',
				),
				JSON_PRETTY_PRINT
			);
		}
		?>
<div id="agnostic-data">
    <p class="agnostic_data_sources_wrapper">
        <label for="agnostic_data_sources"
            class="agnostic_data_sources_label"><?php _e( 'Source', 'agnostic-templates' ); ?></label>
    </p>
    <select name="agnostic_data_sources" id="agnostic_data_sources" v-model="source">
        <?php foreach( $data_sources as $key => $source ) : ?>
        <option value="<?php echo esc_attr( $key ); ?>">
            <?php echo esc_attr( $source ); ?>
        </option>
        <?php endforeach; ?>
    </select>
    <p v-if="source === 'single'">
        <?php _e( 'This exposes a <b>post</b> object referencing the current post object in WordPress.', 'agnostic-templates' ); ?>
    </p>
    <div id="agnostic_query" v-show="source === 'query'">
        <p><?php _e( 'This exposes a <b>posts</b> array in your code referencing the post objects as a result from the query below.', 'agnostic-templates' ); ?>
        </p>
        <p class="agnostic_data_query_wrapper">
            <label for="agnostic_data_query"
                class="agnostic_data_query_label"><?php _e( 'Query (in JSON)', 'agnostic-templates' ); ?></label>
        </p>
        <div id="agnostic_query_editor">{{ query }}</div>
        <input type="hidden" name="agnostic_template_query" id="agnostic_template_query" v-model="query">
    </div>
</div>
<script>
window.agnosticData = {
    source: '<?php echo $data_source_value; ?>',
    query: `<?php echo $data_query_value; ?>`
};
</script>
<?php
  }

 public function save_data( $post_id, $post, $update ) {
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
      return $post_id;
    }

    if ( ! isset( $_POST['post_type'] ) || 'agnostic_template' !== $_POST['post_type'] ) {
      return $post_id;
    }

		$capabilities = apply_filters( 'agnostic_capabilites', 'switch_themes' );

    if ( ! current_user_can( $capabilities ) ) {
      return $post_id;
    }

    if ( isset( $_POST['agnostic_attribute_nonce_field'] ) && wp_verify_nonce( $_POST['agnostic_attribute_nonce_field'], 'agnostic_attribute_nonce' ) ) {

			if ( isset( $_POST['agnostic_attribute_placement'] ) ) {
				update_post_meta( $post_id, '_agnostic_attribute_placement', sanitize_text_field( $_POST['agnostic_attribute_placement'] ) );
			}

			update_post_meta( $post_id, '_agnostic_attribute_hook', sanitize_text_field( $_POST['agnostic_attribute_hook'] ) );

			update_post_meta( $post_id, '_agnostic_attribute_method', sanitize_text_field( $_POST['agnostic_attribute_method'] ) );

			delete_post_meta( $post_id, '_agnostic_condition' );

			if ( isset( $_POST['agnostic_conditions'] ) && ( 0 < count( $_POST['agnostic_conditions'] ) ) ) {
				foreach ( $_POST['agnostic_conditions'] as $k => $v ) {
					add_post_meta( $post_id, '_agnostic_condition', sanitize_text_field( $v ), false );
				}
			}
    }

    if ( isset( $_POST['agnostic_template_code_nonce_field'] ) && wp_verify_nonce( $_POST['agnostic_template_code_nonce_field'], 'agnostic_template_code_nonce' ) ) {
      update_post_meta( $post_id, '_agnostic_template_code',  agnostic_kses( $_POST['agnostic_template_code'] ) );
    }

		if ( isset( $_POST['agnostic_data_nonce_field'] ) && wp_verify_nonce( $_POST['agnostic_data_nonce_field'], 'agnostic_data_nonce' ) ) {
      update_post_meta( $post_id, '_agnostic_data_source', sanitize_text_field(  $_POST['agnostic_data_sources'] ) );

			update_post_meta( $post_id, '_agnostic_data_query', (array) json_decode( stripslashes( $_POST['agnostic_template_query'] ) ) );
    }

  }

}