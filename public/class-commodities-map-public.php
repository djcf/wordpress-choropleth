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
  		wp_enqueue_style( 'commodities-map-styles', plugins_url( 'public/css/commodities-map-public.css', __FILE__ ) );

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

				 //wp_enqueue_script( 'commodities-map-js', plugins_url( 'commodities-map-script.php', __FILE__ ) );

		 #wp_enqueue_script( "jquery-qtip", "http://cdn.jsdelivr.net/qtip2/2.2.1/basic/jquery.qtip.min.js", array( 'jquery' ), $this->version);
		 wp_enqueue_script( "mballoon", "$plugin_dir/components/jquery.mb.balloon.js", array('jquery'), $this->version);
		 wp_enqueue_script( "d3",       "$plugin_dir/components/d3/d3.min.js", array(), $this->version);
		 wp_enqueue_script( "topojson", "$plugin_dir/components/topojson/topojson.js", array("d3"), $this->version);
		 wp_enqueue_script( "datamaps", "$plugin_dir/datamaps.world.js", array("d3", "topojson"), $this->version);
	}

	// Our custom post type function
	public function create_posttype() {

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

	public static function commodities_add_shortcode( $atts ){
		return '<div id="commodities-map" style="border:1px dotted blue; width: 100%; height: 365px; position: relative;"></div>';
	}

	public function print_inline_script() {
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
	       include __DIR__ . "/../commodities-map-script.php";
	    #}
	}

}
