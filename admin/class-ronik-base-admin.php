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
		$rbp_media_cleaner_api_key = get_option('rbp_media_cleaner_api_key') ? get_option('rbp_media_cleaner_api_key') : "";
		$rbp_optimization_api_key = get_option('rbp_optimization_api_key') ? get_option('rbp_optimization_api_key') : "";
		$rbp_media_cleaner_validation = get_option('rbp_media_cleaner_api_key_validation') ? get_option('rbp_media_cleaner_api_key_validation') : "invalid";
		$rbp_optimization_validation = get_option('rbp_optimization_api_key_validation') ? get_option('rbp_optimization_api_key_validation') : "invalid";

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
			'options-ronik-base_integrations',
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
		if($rbp_media_cleaner_api_key && ($rbp_media_cleaner_validation !== "invalid")){
			// Add Media Cleaner page.
			add_submenu_page(
				'options-ronik-base', // parent page slug
				'Media Cleaner',
				'Media Cleaner',
				'manage_options',
				'options-ronik-base_media_cleaner', //
				'ronikbase_media_cleaner_callback',
				4 // menu position
			);
			function ronikbase_media_cleaner_callback(){
				echo '<div id="ronik-base_media_cleaner">Media Cleaner</div>';

				$rbp_media_cleaner_counter = get_option('rbp_media_cleaner_counter') ? get_option('rbp_media_cleaner_counter') : "";	
				$rbp_media_cleaner_increment = update_option('rbp_media_cleaner_increment', 1);	

				if($rbp_media_cleaner_counter){
					$numbers = range(0, ($rbp_media_cleaner_counter - 1));
				?>
					<style>
						table, th, td {
							border:1px solid black;
						}
						td{
							padding: 1em;
							max-width: 20%;
							width: 100%;
						}
					</style>
					<table style="width:100%">
						<input class="ronik-user-exporter_increment" value="<?= $rbp_media_cleaner_increment; ?>">
						<tr>
							<th>Thumbnail Image</th>
							<th>File Size</th>
							<th>Image ID</th>
							<th>Image Url</th>
							<th>Remove Row <br> <sup>Clicking the button will not delete the image it will just exclude the selected image from the media list temporarily.</sup></th>
						</tr>
						<?php foreach($numbers as $number){
							$rbp_media_cleaner_file_size = get_option('rbp_media_cleaner_'.$number.'_file_size') ? get_option('rbp_media_cleaner_'.$number.'_file_size') : "";
							$rbp_media_cleaner_image_id = get_option('rbp_media_cleaner_'.$number.'_image_id') ? get_option('rbp_media_cleaner_'.$number.'_image_id') : "";
							$rbp_media_cleaner_image_url = get_option('rbp_media_cleaner_'.$number.'_image_url') ? get_option('rbp_media_cleaner_'.$number.'_image_url') : "";

							if($rbp_media_cleaner_image_id){ ?>
								<tr data-media-id="<?= $rbp_media_cleaner_image_id; ?>">
									<td><?= wp_get_attachment_image( $rbp_media_cleaner_image_id  );  ?></td>
									<td><?= $rbp_media_cleaner_file_size; ?> </td>
									<td><?= $rbp_media_cleaner_image_id; ?> </td>
									<td><?= $rbp_media_cleaner_image_url; ?> </td>
									<td><button data-media-row="<?= $number; ?>">Remove Row</button></td>
								</tr>
							<?php }?>
								
						<?php } ?>
					</table>
				<?php }
			}
		}
		if($rbp_optimization_api_key && ($rbp_optimization_validation !== "invalid")){
			// Add Optimizationr page.
			add_submenu_page(
				'options-ronik-base', // parent page slug
				'Optimization',
				'Optimization',
				'manage_options',
				'options-ronik-base_optimization', //
				'ronikbase_optimization_callback',
				5 // menu position
			);
			function ronikbase_optimization_callback(){
				echo '
					<div id="ronik-base_optimization">Optimization</div>
				';
			}
		}

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
			$rbp_media_cleaner_api_key = get_option('rbp_media_cleaner_api_key') ? get_option('rbp_media_cleaner_api_key') : "";
			$rbp_optimization_api_key = get_option('rbp_optimization_api_key') ? get_option('rbp_optimization_api_key') : "";
			$rbp_media_cleaner_validation = get_option('rbp_media_cleaner_api_key_validation') ? get_option('rbp_media_cleaner_api_key_validation') : "invalid";
			$rbp_optimization_validation = get_option('rbp_optimization_api_key_validation') ? get_option('rbp_optimization_api_key_validation') : "invalid";
			echo '
				<div id="ronik-base_integrations"></div>
				<div id="ronik_media_cleaner_api_key" data-api='.$rbp_media_cleaner_api_key.'></div>
				<div id="ronik_optimization_api_key" data-api='.$rbp_optimization_api_key.'></div>
				<div id="ronik_media_cleaner_api_key_validation" data-api-validation='.$rbp_media_cleaner_validation.'></div>
				<div id="ronik_optimization_api_key_validation" data-api-validation='.$rbp_optimization_validation.'></div>
			';
		}		




		// add_filter( 'wp_get_attachment_image_attributes', 'my_attachment_filter', 10, 3 );
		// function my_attachment_filter($attr, $attachment, $size){
		// 	error_log(print_r( 'TESTTST', true));
		// 	if (is_admin()){
		// 		if (array_key_exists( 'src' , $attr)){
		// 			$old_src = $attr['src'];
		// 			$new_src = strtr($old_src, array('_featured' => '_featured_eighth', '_portrait' => '_portrait_204', '_og.' => '_og_320.', '.jpg' => '.webp'));
		// 			$attr['src'] = $new_src;
		// 		}
		// 	}
		// 	return $attr;
		// }

		
		// Image Dimensions.
		function rmc_media_column_dimensions( $cols ) {
			$cols["rmc_dimensions"] = "Dimensions (width, height)";
			return $cols;
		}
		// Image Isdetached.
		function rmc_media_column_isdetached( $cols ) {
			$cols["rmc_detached"] = "Ronik Media Cleaner Detached";
			return $cols;
		}
		add_filter( 'manage_media_columns', 'rmc_media_column_isdetached' );
		add_filter( 'manage_media_columns', 'rmc_media_column_dimensions' );

		function rmc_media_column_value( $column_name, $id ) {
			if( $column_name == 'rmc_detached' ){
				echo 'detached';
			}
			if( $column_name == 'rmc_dimensions' ){
				$meta = wp_get_attachment_metadata($id);
				if(isset($meta['width'])){
					echo $meta['width'].' x '.$meta['height'];
				}
			}
		}
		add_action( 'manage_media_custom_column', 'rmc_media_column_value', 10, 2 );










		function media_add_author_dropdown(){
			var_dump('eeee');
			$scr = get_current_screen();
			if ( $scr->base !== 'upload' ) return;
		
			$author   = filter_input(INPUT_GET, 'author', FILTER_SANITIZE_STRING );
			$selected = (int)$author > 0 ? $author : '-1';
			$args = array(
				'show_option_none'   => 'All Authors',
				'name'               => 'author',
				'selected'           => $selected
			);
			wp_dropdown_users( $args );
		}
		add_action('restrict_manage_posts', 'media_add_author_dropdown');

		function author_filter($query) {
			if ( is_admin() && $query->is_main_query() ) {
				if (isset($_GET['author']) && $_GET['author'] == -1) {
					$query->set('author', '');
				}
			}
		}
		add_action('pre_get_posts','author_filter');




	



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







	// This will setup all custom fields via php scripts.
	public function rmc_acf_op_init_fields(){
		// Include the ACF Fields
		foreach (glob(dirname(__FILE__) . '/media-cleaner/acf-fields/*.php') as $file) {
			include $file;
		}
	}

	// Setup additional functionality.
	public function rmc_acf_op_init_functions(){
		// Include the acf-additions.
		foreach (glob(dirname(__FILE__) . '/media-cleaner/acf-additions/*.php') as $file) {
			include $file;
		}
		// Include the wp-functions.
		foreach (glob(dirname(__FILE__) . '/media-cleaner/wp-functions/*.php') as $file) {
			include $file;
		}
	}









	
	// These files contain ajax functions.
	// Init Remove Unused Media
	public function rmc_ajax_media_cleaner(){
		include dirname(__FILE__) . '/media-cleaner/ajax/media-cleaner.php';
	}
	// // Init Unused Media Migration
	// public function rmc_ajax_media_cleaner_remove(){
	// 	include dirname(__FILE__)  . '/media-cleaner/ajax/media-cleaner-remove.php';

	// }










}
