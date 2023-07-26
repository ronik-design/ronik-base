<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://www.ronikdesign.com/
 * @since      1.0.0
 *
 * @package    Ronik_Base
 * @subpackage Ronik_Base/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Ronik_Base
 * @subpackage Ronik_Base/admin
 * @author     Kevin Mancuso <kevin@ronikdesign.com>
 */
class Ronik_Base_Admin {

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
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
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

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'interface/dist/main.css', array(), $this->version, 'all' );

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/ronik-base-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
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

		//
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'interface/dist/index.js', array( 'jquery' ), $this->version, false );		



		if ( ! wp_script_is( 'jquery', 'enqueued' )) {
			wp_enqueue_script($this->plugin_name.'jquery', 'https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js', array(), null, true);
			$scriptName = $this->plugin_name.'jquery';
			wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/ronik-base-admin.js', array($scriptName), $this->version, false);
		} else {
			wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/ronik-base-admin.js', array(), $this->version, false);
		}

		// Ajax & Nonce
		wp_localize_script($this->plugin_name, 'wpVars', array(
			'ajaxURL' => admin_url('admin-ajax.php'),
			'nonce'	  => wp_create_nonce('ajax-nonce')
		));
	}
	/**
	 * Deactive if the dependent plugin is not install & activated.
	 *
	 * @since    1.0.0
	 */
	public function rbp_plugin_dependencies() {
		if ( is_admin() && current_user_can( 'activate_plugins' ) && !class_exists('ACF') ) {
			add_action( 'admin_notices', 'child_plugin_notice' );
			deactivate_plugins( 'ronik-media-cleaner/ronik-media-cleaner.php' ); 
			if ( isset( $_GET['activate'] ) ) {
				unset( $_GET['activate'] );
			}
		}
		function child_plugin_notice(){
			?><div class="error"><p>Sorry, but this Plugin requires the ACF plugin to be installed and active to work properly.</p></div><?php
		}
	}




	// This will setup all options pages.
	public function rbp_acf_op_init(){
		// Check function exists.
		if (function_exists('acf_add_options_page')) {

			// Add parent General page.
			$parent = acf_add_options_page(array(
				'page_title'  => __('General - Ronik Base'),
				'menu_title'  => __('Ronik Base'),
				'redirect'    => false,
				'icon_url' => 'dashicons-visibility', // Add this line and replace the second inverted commas with class of the icon you like
				'position' => 7
			));
	
			// Add Settings page.
			$child = acf_add_options_sub_page(array(
				'page_title'  => __('Settings'),
				'menu_title'  => __('Settings'),
				'parent_slug' => $parent['menu_slug'],
			));
	
			// Add Integrations page.
			$child = acf_add_options_sub_page(array(
				'page_title'  => __('Integrations'),
				'menu_title'  => __('Integrations'),
				'parent_slug' => $parent['menu_slug'],
			));

	
			// Add Support page.
			$child = acf_add_options_sub_page(array(
				'page_title'  => __('Support'),
				'menu_title'  => __('Support'),
				'parent_slug' => $parent['menu_slug'],
			));
						
		}
	}









	// This will setup all custom fields via php scripts.
	public function rbp_acf_op_init_fields(){
		// Include the ACF Fields
		foreach (glob(dirname(__FILE__) . '/acf-fields/*.php') as $file) {
			include $file;
		}
	}

	// Setup additional functionality.
	public function rbp_acf_op_init_functions(){
		// Include the acf-additions.
		// foreach (glob(dirname(__FILE__) . '/acf-additions/*.php') as $file) {
		// 	include $file;
		// }
		// // Include the wp-functions.
		// foreach (glob(dirname(__FILE__) . '/wp-functions/*.php') as $file) {
		// 	include $file;
		// }
	}


	// These files contain ajax functions.
	// Init Unused Media Migration
	public function rbp_ajax_media_cleaner_remove(){
		// include dirname(__FILE__)  . '/ajax/media-cleaner-remove.php';

	}
	// Init Remove Unused Media
	public function rbp_ajax_media_cleaner(){
		// include dirname(__FILE__) . '/ajax/media-cleaner.php';
	}
}
