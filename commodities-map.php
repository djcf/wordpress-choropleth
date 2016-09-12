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
function activate_plugin_name() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-commodities-map-activator.php';
	Plugin_Name_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-plugin-name-deactivator.php
 */
function deactivate_plugin_name() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-commodities-map-deactivator.php';
	Plugin_Name_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_commodities-map' );
register_deactivation_hook( __FILE__, 'deactivate_commodities-map' );

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

// Our custom post type function
function create_posttype() {

	register_post_type( 'commodities',
	// CPT Options
		array(
			'labels' => array(
				'name' => __( 'Commodities' ),
				'singular_name' => __( 'Commodity' )
			),
			'public' => true,
			'has_archive' => true,
			'rewrite' => array('slug' => 'commodities'),
			'supports' => array(
			  'title',
			  'editor',
			  'excerpt',
			  'thumbnail',
			  'custom-fields',
			  'revisions'
			)
		)
	);

	register_post_type( 'countries',
	// CPT Options
		array(
			'labels' => array(
				'name' => __( 'Countries' ),
				'singular_name' => __( 'Country' )
			),
			'public' => true,
			'has_archive' => false,
			'rewrite' => array('slug' => 'countries'),
			'supports' => array(
			  'title',
			  'editor',
			  'thumbnail',
			  'revisions'
			)
		)
	);

}

function commodities_add_shortcode( $atts ){
	return '<div id="commodities-map" style="border:1px dotted blue; width: 100%; height: 365px; position: relative;"></div>';
}

function print_inline_script() {
    $area_series = array();
    $area_series_pc = array();
    $area_series_js = array();
    $area_series_nav = array();
    $area_series_labels = array();

    $args = array(
        'posts_per_page' => -1,
        'post_type' => 'commodities',
				'orderby'=> 'title',
				'order' => 'ASC'
    );

    $posts = get_posts($args);

    foreach($posts as $post) {
        $post->fields  = get_post_custom($post->ID);
        $post->permalink = get_permalink($post->ID);

        if (isset($post->fields['country']) && is_array($post->fields['country'])) {
            foreach($post->fields['country'] as $country) {
                $post->country = strtoupper($country);
                if (isset($area_series[$post->country])) {
                    $area_series[$post->country]++;
                } else {
                    $area_series[$post->country] = 1;
                }

                if(!isset($area_series_nav[$post->country])) {
                    $area_series_nav[$post->country] = array();
                }
                $area_series_nav[$post->country][] = array(
                    'url'  =>  $post->permalink,
                    'title'=> $post->post_title
                );
            }
        }
    }

    $onepc = 100 / sizeof($posts);
    foreach($area_series as $region_name => $region_total) {
        $area_series_pc[$region_name] = intval($onepc * $region_total);
        $area_series_js_pc[] = sprintf('["%s",%s]', $region_name, $area_series_pc[$region_name]);
        $area_series_js[] = sprintf('["%s",%s]', $region_name, $area_series[$region_name]);
        $area_series_labels[] = "'$region_name': '$region_total'";
    }

    $args = array(
        'posts_per_page' => -1,
        'post_type' => 'countries'
    );

    $posts = get_posts($args);

    $countries = array();

    foreach($posts as $post) {
        $countries[$post->post_title] = $post->post_content;
    }

    #if ( wp_script_is( 'datamaps', 'done' ) ) {
       include __DIR__ . "/commodities-map-script.php";
    #}
}

wp_enqueue_style( 'commodities-map-styles', plugins_url( 'public/css/commodities-map-public.css', __FILE__ ) );
//wp_enqueue_script( 'commodities-map-js', plugins_url( 'commodities-map-script.php', __FILE__ ) );

add_shortcode( 'commodities-map', 'commodities_add_shortcode' );

// Hooking up our function to theme setup
add_action( 'init', 'create_posttype' );

add_action( 'wp_footer', 'print_inline_script' );
