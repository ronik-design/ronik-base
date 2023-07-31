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

	// We create our own option page due to ACF in-effective
	public function rbp_plugin_interface() {
		add_menu_page(
			'General - Ronik Base', // page <title>Title</title>
			'Ronik Base', // link text
			'manage_options', // user capabilities
			'options-ronik-base', // page slug
			'ronikbase_support_general', // this function prints the page content
			'dashicons-visibility', // icon (from Dashicons for example)
			6 // menu position
		);
		// Add Settings page.
		add_submenu_page(
			'options-ronik-base', // parent page slug
			'Ronik Base Settings',
			'Settings',
			'manage_options',
			'options-ronik-base_settings', //
			'ronikbase_support_settings',
			1 // menu position
		);
		// Add Integrations page.
		add_submenu_page(
			'options-ronik-base', // parent page slug
			'Integrations',
			'Integrations',
			'manage_options',
			'options-ronik-base_integrations', //
			'ronikbase_integrations_callback',
			2 // menu position
		);
		// Add Support page.
		add_submenu_page(
			'options-ronik-base', // parent page slug
			'Support',
			'Support',
			'manage_options',
			'options-ronik-base_support', //
			'ronikbase_support_callback',
			3 // menu position
		);

		function ronikbase_support_general(){
			echo '
				<div id="ronik-base_general"></div>
			';
		}
		function ronikbase_support_settings(){
			echo '
				<div id="ronik-base_settings"></div>
			';
		}
		function ronikbase_support_callback(){
			echo '
				<div id="ronik-base_support"></div>
			';
		}
		function ronikbase_integrations_callback(){
			echo '
				<div id="ronik-base_integrations"></div>
				<div id="ronik_media_cleaner_api_key" data-api='.get_option('rbp_media_cleaner_api_key').'></div>
				<div id="ronik_optimization_api_key" data-api='.get_option('rbp_optimization_api_key').'></div>
			';
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
	// The API Checkpoint.
	public function api_checkpoint(){
		include dirname(__FILE__)  . '/ajax/api-checkpoint.php';
	}
}
