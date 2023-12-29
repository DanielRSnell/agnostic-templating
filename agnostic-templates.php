<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 *
 * @link              https://agnosticblocks.com
 * @since             1.0.0
 * @package           Agnostic_Templates
 *
 * @wordpress-plugin
 * Plugin Name:       Agnostic Templates
 * Plugin URI:        https://agnosticblocks.com
 * Description:       Easily write templates filled with custom data that loads across your site.
 * Version:           1.5.0
 * Author:            Daniel Snell
 * Author URI:        https://broke.dev
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       agnostic-templates
 * Domain Path:       /languages
 * GitHub Plugin URI: DanielRSnell/agnostic-templates
 * GitHub Plugin URI: https://github.com/DanielRSnell/agnostic-templates
 */

function Agnostic_Templates_deactivate() {
  deactivate_plugins( plugin_basename( __FILE__ ) );
}

function Agnostic_Templates_dependency_admin_notice() {
  echo '<div class="updated"><p><strong>Agnostic Templates</strong> requires the plugin <a href="https://wordpress.org/plugins/timber-library/" target="_blank">Timber</a> to be activated; the plug-in has been <strong>deactivated</strong>.</p></div>';
  if ( isset( $_GET['activate'] ) )
    unset( $_GET['activate'] );
}

function Agnostic_Templates_check_dependencies() {
  if ( ! class_exists( '\Timber\Timber' ) ) {
    add_action( 'admin_init', 'Agnostic_Templates_deactivate' );
    add_action( 'admin_notices', 'Agnostic_Templates_dependency_admin_notice' );
  } else {
  	/**
  	 * The core plugin class that is used to define internationalization,
  	 * admin-specific hooks, and public-facing site hooks.
  	 */
  	require plugin_dir_path( __FILE__ ) . 'includes/class-agnostic.php';

  	/**
  	 * Begins execution of the plugin.
  	 *
  	 * Since everything within the plugin is registered via hooks,
  	 * then kicking off the plugin from this point in the file does
  	 * not affect the page life cycle.
  	 *
  	 * @since    1.0.0
  	 */
  	function run_Agnostic_Templates() {

  		$plugin = new Agnostic_Templates();
  		$plugin->run();

  	}
  	run_Agnostic_Templates();
  }
}
add_action( 'plugins_loaded', 'Agnostic_Templates_check_dependencies', 2 );

function modify_agnostic_template_post_type_args( $args, $post_type ) {
    if ( 'agnostic_template' === $post_type ) {
        $args['public'] = true;
		$args['publicly_queryable'] = true;
	}
	return $args;
}

add_filter( 'register_post_type_args', 'modify_agnostic_template_post_type_args', 10, 2 );