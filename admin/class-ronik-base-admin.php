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
	 * @param      string    $plugin_name       The name of this plugin.
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
			wp_enqueue_script($this->plugin_name.'-admin', plugin_dir_url(__FILE__) . 'js/ronik-base-admin.js', array($scriptName), $this->version, false);
		} else {
			wp_enqueue_script($this->plugin_name.'-admin', plugin_dir_url(__FILE__) . 'js/ronik-base-admin.js', array(), $this->version, false);
		}

		// Ajax & Nonce
		wp_localize_script($this->plugin_name, 'wpVars', array(
			'ajaxURL' => admin_url('admin-ajax.php'),
			'nonce'	  => wp_create_nonce('ajax-nonce'),
			'betaMode' => $this->beta_mode_state ? true : false,
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
			// deactivate_plugins( 'ronik-media-cleaner/ronik-media-cleaner.php' );
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
			// Include the interface Fields
			foreach (glob(dirname(__FILE__) . '/media-cleaner/interface-layout/*.php') as $file) {
				include $file;
			}

		if($this->media_cleaner_state){
			$_POST['media_cleaner_state'] = 'valid';
		} else {
			$_POST['media_cleaner_state'] = 'invalid';
		}
		if($this->optimization_state){
			$_POST['optimization_state'] = 'valid';
			// Include the interface Fields
			foreach (glob(dirname(__FILE__) . '/optimization/interface-layout/*.php') as $file) {
				include $file;
			}
		} else{
			$_POST['optimization_state'] = 'invalid';
		}


		function ronikbase_support_general(){
			echo '
				<div id="ronik-base_general"></div>
			';
		}
		function ronikbase_support_settings(){
			echo '
			<div id="ronik-base_settings">Ronik Base Settings</div>
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
			// This is critical we setup some variables that will help with js & php communication.
			echo '
				<div id="ronik-base_integrations"></div>
				<div id="ronik_media_cleaner_api_key" data-api='.$rbp_media_cleaner_api_key.'></div>
				<div id="ronik_optimization_api_key" data-api='.$rbp_optimization_api_key.'></div>
				<div id="ronik_media_cleaner_api_key_validation" data-api-validation='.$rbp_media_cleaner_validation.'></div>
				<div id="ronik_optimization_api_key_validation" data-api-validation='.$rbp_optimization_validation.'></div>
			';
		}
	}

	// The API Checkpoint.
	public function api_checkpoint(){
		include dirname(__FILE__)  . '/ajax/api-checkpoint.php';
	}

	// Media Cleaner Chunk
		public function rmc_classes(){
			// Include the wp-functions.
			foreach (glob(dirname(__FILE__) . '/media-cleaner/classes/*.php') as $file) {
				include $file;
			}
		}
		public function rmc_functions(){
			// Include the wp-functions.
			foreach (glob(dirname(__FILE__) . '/media-cleaner/functions/*.php') as $file) {
				include $file;
			}
		}
		// These files contain ajax functions.
		public function rmc_ajax_media_cleaner(){
			include dirname(__FILE__) . '/media-cleaner/ajax/media-cleaner.php';
		}

		public function rmc_ajax_media_swap(){
			include dirname(__FILE__) . '/media-cleaner/ajax/media-swap.php';
		}

		// These files contain ajax functions.
		public function rmc_ajax_media_cleaner_settings(){
			include dirname(__FILE__) . '/media-cleaner/ajax/media-cleaner-settings.php';
		}

		public function rmc_media_sync_save( $post_id ){
			$rbpHelper = new RbpHelper;
			$rbpHelper->ronikdesigns_write_log_devmode('Media Cleaner: Ref 2a, rmc_media_sync_save update media sync logic for on save posts. ', 'low', 'rbp_media_cleaner');

			// Detect if the sync is already running.
			$rbp_media_cleaner_sync_running = get_option('rbp_media_cleaner_sync_running' , 'not-running');
			if($rbp_media_cleaner_sync_running == 'running'){
				return false;
			}
			$transient_rmc_media_cleaner_media_data_collectors_image_id_array_finalized = get_transient( 'rmc_media_cleaner_media_data_collectors_image_id_array_finalized' );
			if( ! empty( $transient_rmc_media_cleaner_media_data_collectors_image_id_array_finalized ) ) {
				$rmc_media_cleaner_media_data_collectors_image_id_array_finalized = $transient_rmc_media_cleaner_media_data_collectors_image_id_array_finalized;
			} else {
				return false;
			}

			// Update the memory option.
			$helper = new RonikBaseHelper;
			$helper->ronikdesigns_increase_memory();

			$RmcDataGathering = new RmcDataGathering;
			$f_sync = get_option('rbp_media_cleaner_sync-time');
			if($f_sync){
				$date = new DateTime(); // For today/now, don't pass an arg.
				$date->modify("-1 day");
				update_option('rbp_media_cleaner_sync-time', date($date->format("m/d/Y h:ia")));
			}

			$rmc_media_cleaner_media_data_collectors_image_thumbnail_auditor_array = $RmcDataGathering->specificImageThumbnailAuditor( $post_id, $rmc_media_cleaner_media_data_collectors_image_id_array_finalized );
			set_transient( 'rmc_media_cleaner_media_data_collectors_image_thumbnail_auditor_array' , $rmc_media_cleaner_media_data_collectors_image_thumbnail_auditor_array , DAY_IN_SECONDS );
			$rbpHelper->ronikdesigns_write_log_devmode('Media Cleaner: Ref 2b, specificImageThumbnailAuditor ' . count($rmc_media_cleaner_media_data_collectors_image_thumbnail_auditor_array) , 'low', 'rbp_media_cleaner');

			$rmc_media_cleaner_media_data_collectors_image_post_auditor_array = $RmcDataGathering->specificImagePostAuditor( $rmc_media_cleaner_media_data_collectors_image_thumbnail_auditor_array,  $post_id  );
			set_transient( 'rmc_media_cleaner_media_data_collectors_image_post_auditor_array' , $rmc_media_cleaner_media_data_collectors_image_post_auditor_array , DAY_IN_SECONDS );
			$rbpHelper->ronikdesigns_write_log_devmode('Media Cleaner: Ref 2b, specificImagePostAuditor ' . count($rmc_media_cleaner_media_data_collectors_image_post_auditor_array) , 'low', 'rbp_media_cleaner');

			$rmc_media_cleaner_media_data_collectors_image_post_content_auditor_array = $RmcDataGathering->specificImagePostContentAuditor( $rmc_media_cleaner_media_data_collectors_image_post_auditor_array, $post_id );
			set_transient( 'rmc_media_cleaner_media_data_collectors_image_post_content_auditor_array' , $rmc_media_cleaner_media_data_collectors_image_post_content_auditor_array , DAY_IN_SECONDS );
			$rbpHelper->ronikdesigns_write_log_devmode('Media Cleaner: Ref 2c, specificImagePostContentAuditor ' . count($rmc_media_cleaner_media_data_collectors_image_post_content_auditor_array) , 'low', 'rbp_media_cleaner');

			$rmc_media_cleaner_media_data_collectors_image_filesystem_auditor_array = $RmcDataGathering->imageFilesystemAudit( $rmc_media_cleaner_media_data_collectors_image_post_content_auditor_array );
			set_transient( 'rmc_media_cleaner_media_data_collectors_image_filesystem_auditor_array' , $rmc_media_cleaner_media_data_collectors_image_filesystem_auditor_array , DAY_IN_SECONDS );
			$rbpHelper->ronikdesigns_write_log_devmode('Media Cleaner: Ref 2d, imageFilesystemAudit ' . count($rmc_media_cleaner_media_data_collectors_image_filesystem_auditor_array) , 'low', 'rbp_media_cleaner');

			$rmc_media_cleaner_media_data_collectors_image_id_array_not_preserve_finalized = $RmcDataGathering->imagePreserveAudit( $rmc_media_cleaner_media_data_collectors_image_filesystem_auditor_array );
			set_transient( 'rmc_media_cleaner_media_data_collectors_image_id_array_not_preserve' , $rmc_media_cleaner_media_data_collectors_image_id_array_not_preserve_finalized , DAY_IN_SECONDS );
			$rbpHelper->ronikdesigns_write_log_devmode('Media Cleaner: Ref 2e, imagePreserveAudit ' . count($rmc_media_cleaner_media_data_collectors_image_id_array_not_preserve_finalized) , 'low', 'rbp_media_cleaner');

			set_transient( 'rmc_media_cleaner_media_data_collectors_image_id_array_finalized' , $rmc_media_cleaner_media_data_collectors_image_id_array_not_preserve_finalized , DAY_IN_SECONDS );
			$RmcDataGathering->imageMarker( $rmc_media_cleaner_media_data_collectors_image_id_array_not_preserve_finalized );

		}

			/**
			* Register helper classes!
			* This is critical for AUTHORIZATION to work properly!
		*/
		public function rbp_helper_functions(){
			foreach (glob(dirname(__FILE__) . '/helper/rbp_helper.php') as $file) {
				include_once $file;
			}
		}
		public function rbp_helper_functions_cookies(){
			foreach (glob(dirname(__FILE__) . '/helper/rbp_helper_cookies.php') as $file) {
				include_once $file;
			}
		}

		public function rmc_media_sync(){
			$rbpHelper = new RbpHelper;

			$rbpHelper->ronikdesigns_write_log_devmode('Media Cleaner: Ref 1a, rmc_media_sync', 'low', 'rbp_media_cleaner');
			$date = new DateTime(); // For today/now, don't pass an arg.

			// Detect if the sync is already running.
			$rbp_media_cleaner_sync_running = get_option('rbp_media_cleaner_sync_running' , 'not-running');
			if($rbp_media_cleaner_sync_running == 'running'){
				$rbpHelper->ronikdesigns_write_log_devmode('Media Cleaner: Ref 1b, rmc_media_sync already running', 'low', 'rbp_media_cleaner');
				return false;
			}
			// Update the sync status to running..
				update_option('rbp_media_cleaner_sync_running-time', date($date->format("m/d/Y h:ia")));
				update_option('rbp_media_cleaner_sync_running', 'running');
				$rbp_media_cleaner_sync_running = get_option('rbp_media_cleaner_sync_running' , 'not-running');
				$rbpHelper->ronikdesigns_write_log_devmode('Media Cleaner: Ref 1b, rmc_media_sync running, options: rbp_media_cleaner_sync_running' .$rbp_media_cleaner_sync_running , 'low', 'rbp_media_cleaner');
				set_transient( 'rmc_media_cleaner_media_data_collectors_image_id_array_progress' , '0%' , DAY_IN_SECONDS );

			// Update the memory option.
				$helper = new RonikBaseHelper;
				$helper->ronikdesigns_increase_memory();

			// Default settings...
				$RmcDataGathering = new RmcDataGathering;
				// We retreive the follow post status.
				$select_post_status = array('publish', 'pending', 'draft', 'private', 'future', 'archive');
				// Dynamically retrieve the post types for entire site including custom post types.
				$select_post_type = $RmcDataGathering->postTypesRetrieval();
			// Mime_type.
				$select_attachment_type = cleaner_post_mime_type('all');
				// error_log(print_r($_POST['mime_type'], true));
			// Overall media counter...
				$throttle_detector =  databaseScannerMedia__allMedia(array('count', $select_attachment_type));
			// Set numberposts to a number that wont destroy the server resources.
				$select_numberposts = 35;
			// We get the overall number of posts and divide it by the numberposts and round up that will allow us to page correctly. Then we plus by 1 for odd errors.
				$maxIncrement = ceil($throttle_detector/$select_numberposts);
			// File Size
				// $targetFileSize = get_option('rbp_media_cleaner_file_size') ? get_option('rbp_media_cleaner_file_size') : .1;
				$targetFileSize = (get_option('rbp_media_cleaner_file_size') === '0') 
				? 0 
				: (get_option('rbp_media_cleaner_file_size') ?: .1);


				error_log(print_r('$targetFileSize' , true));
				error_log(print_r($targetFileSize , true));

			// Gather all the posts ID of the entire database...
				$transient_rmc_media_cleaner_media_data_collectors_posts_array = get_transient( 'rmc_media_cleaner_media_data_collectors_posts_array' );
				if( ! empty( $transient_rmc_media_cleaner_media_data_collectors_posts_array ) ) {
					$rmc_media_cleaner_media_data_collectors_posts_array = $transient_rmc_media_cleaner_media_data_collectors_posts_array;
				} else {
					$rmc_media_cleaner_media_data_collectors_posts_array = $RmcDataGathering->postIDCollector($select_post_status, $select_post_type);
					// Save the response so we don't have to call again until tomorrow.
					set_transient( 'rmc_media_cleaner_media_data_collectors_posts_array' , $rmc_media_cleaner_media_data_collectors_posts_array , DAY_IN_SECONDS );
				}
				set_transient( 'rmc_media_cleaner_media_data_collectors_image_id_array_progress' , '20%' , DAY_IN_SECONDS );
				$rbpHelper->ronikdesigns_write_log_devmode('Media Cleaner: Ref 1b, rmc_media_sync running, transient: Gather all the posts ID of the entire database ' .count($rmc_media_cleaner_media_data_collectors_posts_array) , 'low', 'rbp_media_cleaner');
				sleep(1);

			// Gather all the image ids.
				$transient_rmc_media_cleaner_media_data_collectors_image_id_array = get_transient( 'rmc_media_cleaner_media_data_collectors_image_id_array' );
				if( ! empty( $transient_rmc_media_cleaner_media_data_collectors_image_id_array ) ) {
					$rmc_media_cleaner_media_data_collectors_image_id_array = $transient_rmc_media_cleaner_media_data_collectors_image_id_array;
				} else {
					$rmc_media_cleaner_media_data_collectors_image_id_array = $RmcDataGathering->imageIDCollector($select_attachment_type, $select_numberposts, $targetFileSize, $maxIncrement);
					// Save the response so we don't have to call again until tomorrow.
					set_transient( 'rmc_media_cleaner_media_data_collectors_image_id_array' , $rmc_media_cleaner_media_data_collectors_image_id_array , DAY_IN_SECONDS );
				}
				set_transient( 'rmc_media_cleaner_media_data_collectors_image_id_array_progress' , '30%' , DAY_IN_SECONDS );
				$rbpHelper->ronikdesigns_write_log_devmode('Media Cleaner: Ref 1b, rmc_media_sync running, transient: Gather all the image ids. ' .count($rmc_media_cleaner_media_data_collectors_image_id_array) , 'low', 'rbp_media_cleaner');

				sleep(1);



			// Check if images have the preserved attributes.
				// $transient_rmc_media_cleaner_media_data_collectors_image_id_array_not_preserve = get_transient( 'rmc_media_cleaner_media_data_collectors_image_id_array_not_preserve' );
				// if( ! empty( $transient_rmc_media_cleaner_media_data_collectors_image_id_array_not_preserve ) ) {
				// 	$rmc_media_cleaner_media_data_collectors_image_id_array_not_preserve_finalized = $transient_rmc_media_cleaner_media_data_collectors_image_id_array_not_preserve;
				// } else {
				// }
				$rmc_media_cleaner_media_data_collectors_image_id_array_not_preserved = $RmcDataGathering->imagePreserveAudit( $rmc_media_cleaner_media_data_collectors_image_id_array );
				// Save the response so we don't have to call again until tomorrow.
				set_transient( 'rmc_media_cleaner_media_data_collectors_image_id_array_not_preserve' , $rmc_media_cleaner_media_data_collectors_image_id_array_not_preserved , DAY_IN_SECONDS );

				$rbpHelper->ronikdesigns_write_log_devmode('Media Cleaner: Ref 1b, rmc_media_sync running, transient: Check if images have the preserved attributes. ' .count($rmc_media_cleaner_media_data_collectors_image_id_array_not_preserved) , 'low', 'rbp_media_cleaner');





			// Image Id Thumbnail Auditor.
				$transient_rmc_media_cleaner_media_data_collectors_image_thumbnail_auditor_array = get_transient( 'rmc_media_cleaner_media_data_collectors_image_thumbnail_auditor_array' );
				if( ! empty( $transient_rmc_media_cleaner_media_data_collectors_image_thumbnail_auditor_array ) ) {
					$rmc_media_cleaner_media_data_collectors_image_thumbnail_auditor_array = $transient_rmc_media_cleaner_media_data_collectors_image_thumbnail_auditor_array;
				} else {
					$rmc_media_cleaner_media_data_collectors_image_thumbnail_auditor_array = $RmcDataGathering->imageThumbnailAuditor( $rmc_media_cleaner_media_data_collectors_posts_array, $rmc_media_cleaner_media_data_collectors_image_id_array_not_preserved, $select_attachment_type );
					// Save the response so we don't have to call again until tomorrow.
					set_transient( 'rmc_media_cleaner_media_data_collectors_image_thumbnail_auditor_array' , $rmc_media_cleaner_media_data_collectors_image_thumbnail_auditor_array , DAY_IN_SECONDS );
				}
				set_transient( 'rmc_media_cleaner_media_data_collectors_image_id_array_progress' , '40%' , DAY_IN_SECONDS );
				$rbpHelper->ronikdesigns_write_log_devmode('Media Cleaner: Ref 1b, rmc_media_sync running, transient: Image Id Thumbnail Auditor. ' .count($rmc_media_cleaner_media_data_collectors_image_thumbnail_auditor_array) , 'low', 'rbp_media_cleaner');

				sleep(1);

			// Check image id within all posts.
				$transient_rmc_media_cleaner_media_data_collectors_image_post_auditor_array = get_transient( 'rmc_media_cleaner_media_data_collectors_image_post_auditor_array' );
				if( ! empty( $transient_rmc_media_cleaner_media_data_collectors_image_post_auditor_array ) ) {
					$rmc_media_cleaner_media_data_collectors_image_post_auditor_array = $transient_rmc_media_cleaner_media_data_collectors_image_post_auditor_array;
				} else {
					$rmc_media_cleaner_media_data_collectors_image_post_auditor_array = $RmcDataGathering->imagePostAuditor( $rmc_media_cleaner_media_data_collectors_image_thumbnail_auditor_array, $rmc_media_cleaner_media_data_collectors_posts_array, $select_post_status, $select_post_type );
					// Save the response so we don't have to call again until tomorrow.
					set_transient( 'rmc_media_cleaner_media_data_collectors_image_post_auditor_array' , $rmc_media_cleaner_media_data_collectors_image_post_auditor_array , DAY_IN_SECONDS );
				}
				set_transient( 'rmc_media_cleaner_media_data_collectors_image_id_array_progress' , '50%' , DAY_IN_SECONDS );
				$rbpHelper->ronikdesigns_write_log_devmode('Media Cleaner: Ref 1b, rmc_media_sync running, transient: Check image id within all posts. ' .count($rmc_media_cleaner_media_data_collectors_image_post_auditor_array) , 'low', 'rbp_media_cleaner');

				sleep(1);

			// Check image basename within the post content primarily this is for gutenberg editior.
				$transient_rmc_media_cleaner_media_data_collectors_image_post_content_auditor_array = get_transient( 'rmc_media_cleaner_media_data_collectors_image_post_content_auditor_array' );
				if( ! empty( $transient_rmc_media_cleaner_media_data_collectors_image_post_content_auditor_array ) ) {
					$rmc_media_cleaner_media_data_collectors_image_post_content_auditor_array = $transient_rmc_media_cleaner_media_data_collectors_image_post_content_auditor_array;
				} else {
					$rmc_media_cleaner_media_data_collectors_image_post_content_auditor_array = $RmcDataGathering->imagePostContentAuditor( $rmc_media_cleaner_media_data_collectors_image_post_auditor_array, $rmc_media_cleaner_media_data_collectors_posts_array );
					// Save the response so we don't have to call again until tomorrow.
					set_transient( 'rmc_media_cleaner_media_data_collectors_image_post_content_auditor_array' , $rmc_media_cleaner_media_data_collectors_image_post_content_auditor_array , DAY_IN_SECONDS );
				}
				set_transient( 'rmc_media_cleaner_media_data_collectors_image_id_array_progress' , '80%' , DAY_IN_SECONDS );
				$rbpHelper->ronikdesigns_write_log_devmode('Media Cleaner: Ref 1b, rmc_media_sync running, transient: Check image basename within the post content primarily this is for gutenberg editior. ' .count($rmc_media_cleaner_media_data_collectors_image_post_content_auditor_array) , 'low', 'rbp_media_cleaner');

				sleep(1);





					// Check image basename within the post content primarily this is for gutenberg editior.
					$transient_rmc_media_cleaner_media_data_collectors_image_option_auditor_array = get_transient( 'rmc_media_cleaner_media_data_collectors_image_option_auditor_array' );
					if( ! empty( $transient_rmc_media_cleaner_media_data_collectors_image_option_auditor_array ) ) {
						$rmc_media_cleaner_media_data_collectors_image_option_auditor_array = $transient_rmc_media_cleaner_media_data_collectors_image_option_auditor_array;
					} else {
						$rmc_media_cleaner_media_data_collectors_image_option_auditor_array = $RmcDataGathering->imagOptionAuditor( $rmc_media_cleaner_media_data_collectors_image_post_content_auditor_array, $rmc_media_cleaner_media_data_collectors_posts_array, $rmc_media_cleaner_media_data_collectors_posts_array, $select_post_status, $select_post_type  );
						// Save the response so we don't have to call again until tomorrow.
						set_transient( 'rmc_media_cleaner_media_data_collectors_image_option_auditor_array' , $rmc_media_cleaner_media_data_collectors_image_option_auditor_array , DAY_IN_SECONDS );
					}
					set_transient( 'rmc_media_cleaner_media_data_collectors_image_id_array_progress' , '90%' , DAY_IN_SECONDS );
					$rbpHelper->ronikdesigns_write_log_devmode('Media Cleaner: Ref 1b, rmc_media_sync running, transient: Check image with options pages. ' .count($rmc_media_cleaner_media_data_collectors_image_post_content_auditor_array) , 'low', 'rbp_media_cleaner');
	
					sleep(1);






			// Check the image inside the filesystem. This checks if the image hardcoded into any of the files.
				$transient_rmc_media_cleaner_media_data_collectors_image_filesystem_auditor_array = get_transient( 'rmc_media_cleaner_media_data_collectors_image_filesystem_auditor_array' );
				if( ! empty( $transient_rmc_media_cleaner_media_data_collectors_image_filesystem_auditor_array ) ) {
					$rmc_media_cleaner_media_data_collectors_image_filesystem_auditor_array = $transient_rmc_media_cleaner_media_data_collectors_image_filesystem_auditor_array;
				} else {
					$rmc_media_cleaner_media_data_collectors_image_filesystem_auditor_array = $RmcDataGathering->imageFilesystemAudit( $rmc_media_cleaner_media_data_collectors_image_option_auditor_array );
					// Save the response so we don't have to call again until tomorrow.
					set_transient( 'rmc_media_cleaner_media_data_collectors_image_filesystem_auditor_array' , $rmc_media_cleaner_media_data_collectors_image_filesystem_auditor_array , DAY_IN_SECONDS );
				}
				$rbpHelper->ronikdesigns_write_log_devmode('Media Cleaner: Ref 1b, rmc_media_sync running, transient: Check the image inside the filesystem. This checks if the image hardcoded into any of the files. ' .count($rmc_media_cleaner_media_data_collectors_image_filesystem_auditor_array) , 'low', 'rbp_media_cleaner');


				set_transient( 'rmc_media_cleaner_media_data_collectors_image_id_array_progress' , '99%' , DAY_IN_SECONDS );
				sleep(1);

				set_transient( 'rmc_media_cleaner_media_data_collectors_image_id_array_finalized' , $rmc_media_cleaner_media_data_collectors_image_filesystem_auditor_array , DAY_IN_SECONDS );
					$RmcDataGathering->imageMarker( $rmc_media_cleaner_media_data_collectors_image_filesystem_auditor_array );
				sleep(1);
				set_transient( 'rmc_media_cleaner_media_data_collectors_image_id_array_progress' , 'DONE' , DAY_IN_SECONDS );
				$rbpHelper->ronikdesigns_write_log_devmode('Media Cleaner: Ref 1b, rmc_media_sync running, transient: FINISHED SYNC ' , 'low', 'rbp_media_cleaner');

			// Update the sync status
			update_option('rbp_media_cleaner_sync_running', 'not-running');
			update_option('rbp_media_cleaner_cron_last-ran', date('Y-m-d'));
			update_option('rbp_media_cleaner_sync-time',  date("m/d/Y h:ia"));
		}
}
