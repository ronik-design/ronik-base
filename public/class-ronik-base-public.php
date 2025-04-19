<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://www.ronikdesign.com/
 * @since      1.0.0
 *
 * @package    Ronik_Base
 * @subpackage Ronik_Base/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Ronik_Base
 * @subpackage Ronik_Base/public
 * @author     Kevin Mancuso <kevin@ronikdesign.com>
 */
class Ronik_Base_Public {

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

	/**
	 * The media_cleaner.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $media_cleaner
	 */
	private $media_cleaner_state;

	/**
	 * The optimization_state.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $optimization_state
	 */
	private $optimization_state;


	/**
	 * The beta_mode_state.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $beta_mode_state
	 */
	private $beta_mode_state;


	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct($plugin_name, $version,  $media_cleaner_state, $optimization_state, $beta_mode_state ) {
		$this->beta_mode_state = $beta_mode_state;
		$this->media_cleaner_state = $media_cleaner_state;
		$this->optimization_state = $optimization_state;
		$this->plugin_name = $plugin_name;
		$this->version = $version;
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
		 * defined in Ronik_Base_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Ronik_Base_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'assets/dist/main.css', array(), $this->version, 'all' );
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
		 * defined in Ronik_Base_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Ronik_Base_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'assets/dist/app.js', array( 'jquery' ), $this->version, false );
	}

	// Public facing rest api.
	public function ronikdesignsbase_rest_api_init(){

		if($this->media_cleaner_state){
			// Include the dynamic rest api.
			foreach (glob(dirname(__FILE__) . '/rest-api/media_cleaner/*.php') as $file) {
				include $file;
			}
		}
		if($this->optimization_state){
			// Include the dynamic rest api.
			foreach (glob(dirname(__FILE__) . '/rest-api/optimization/*.php') as $file) {
				include $file;
			}
		}

		if(!$this->optimization_state && $this->beta_mode_state){
			// Include the dynamic rest api.
			foreach (glob(dirname(__FILE__) . '/rest-api/optimization/*.php') as $file) {
				include $file;
			}
		}
		if(!$this->media_cleaner_state && $this->beta_mode_state){
			// Include the dynamic rest api.
			foreach (glob(dirname(__FILE__) . '/rest-api/media_cleaner/*.php') as $file) {
				include $file;
			}
		}

	}
}
