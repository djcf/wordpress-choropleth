<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://djcf.github.com
 * @since             1.0.0
 * @package           Commodites_Map
 *
 * @wordpress-plugin
 * Plugin Name:       Commodities Map
 * Plugin URI:        http://djcf.github.com
 * Description:       Displays a chloropleth navigational area to display custom post types. Use the [commodities-map] short code to display.
 * Version:           0.0.1
 * Author:            Daniel James
 * Author URI:        http://djcf.github.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       commodities-map
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-plugin-name-activator.php
 */
function activate_commodities_map() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-commodities-map-activator.php';
	Commodities_Map_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-plugin-name-deactivator.php
 */
function deactivate_commodities_map() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-commodities-map-deactivator.php';
	Commodities_Map_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_commodities_map' );
register_deactivation_hook( __FILE__, 'deactivate_commodities_map' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-commodities-map.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_commodities_map() {

	$plugin = new Commodities_Map();
	$plugin->run();

}
run_commodities_map();
