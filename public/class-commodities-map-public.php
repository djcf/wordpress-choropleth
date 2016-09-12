<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Plugin_Name
 * @subpackage Plugin_Name/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Plugin_Name
 * @subpackage Plugin_Name/public
 * @author     Your Name <email@example.com>
 */
class Commodities_Map_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name = "commodities-map";

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version = 0.1;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( ) {

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Plugin_Name_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Plugin_Name_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

         $plugin_dir = plugin_dir_url( __FILE__ ) . '/css';
         
		 wp_enqueue_style( 'mb.balloon', "$plugin_dir/mb.balloon.css", array(), $this->version);
		 #wp_enqueue_style( "jquery-qtip", 'http://cdnjs.cloudflare.com/ajax/libs/qtip2/2.2.1/basic/jquery.qtip.min.css', array(), $this->version, 'all' );
        
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
		 * defined in Plugin_Name_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Plugin_Name_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
         $plugin_dir = plugin_dir_url( __FILE__ ) . "/js";
         
		 #wp_enqueue_script( "jquery-qtip", "http://cdn.jsdelivr.net/qtip2/2.2.1/basic/jquery.qtip.min.js", array( 'jquery' ), $this->version);
		 wp_enqueue_script( "mballoon", "$plugin_dir/components/jquery.mb.balloon.js", array('jquery'), $this->version);
		 wp_enqueue_script( "d3",       "$plugin_dir/components/d3/d3.min.js", array(), $this->version);
		 wp_enqueue_script( "topojson", "$plugin_dir/components/topojson/topojson.js", array("d3"), $this->version);
		 wp_enqueue_script( "datamaps", "$plugin_dir/datamaps.world.js", array("d3", "topojson"), $this->version);
	}

}