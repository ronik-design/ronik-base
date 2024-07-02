<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://www.ronikdesign.com/
 * @since      1.0.0
 *
 * @package    Ronik_Base
 * @subpackage Ronik_Base/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Ronik_Base
 * @subpackage Ronik_Base/includes
 * @author     Kevin Mancuso <kevin@ronikdesign.com>
 */
class Ronik_Base {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Ronik_Base_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'RONIK_BASE_VERSION' ) ) {
			$this->version = RONIK_BASE_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'ronik-base';

		$this->load_dependencies();
		$this->set_locale();


			/* Checks to see if "is_plugin_active" function exists and if not load the php file that includes that function */
			if (!function_exists('is_plugin_active')) {
				include_once(ABSPATH . 'wp-admin/includes/plugin.php');
			}

			function ronik_admin_notice__error() { ?>
				<div class="notice notice-error ">
					<p><?php _e( 'Warning: Advanced Custom Fields PRO needs to be installed and activated!', 'sample-text-domain' ); ?></p>
				</div>
			<?php }

			//plugin is activated ACF,
			if (is_plugin_active('advanced-custom-fields-pro/acf.php')) {
				$this->define_admin_hooks();
				$this->define_public_hooks();
			} else {
				
				if(!empty(get_mu_plugins()['acf.php'])){
					$this->define_admin_hooks();
					$this->define_public_hooks();
				} else {
					deactivate_plugins( 'ronik-base/ronik-base.php' );
					add_action( 'admin_notices', 'ronik_admin_notice__error' );
				}
			}

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Ronik_Base_Loader. Orchestrates the hooks of the plugin.
	 * - Ronik_Base_i18n. Defines internationalization functionality.
	 * - Ronik_Base_Admin. Defines all hooks for the admin area.
	 * - Ronik_Base_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-ronik-base-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-ronik-base-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-ronik-base-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-ronik-base-public.php';

		$this->loader = new Ronik_Base_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Ronik_Base_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Ronik_Base_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {
		$plugin_admin = new Ronik_Base_Admin( $this->get_plugin_name(), $this->get_version(), $this->get_media_cleaner_state(), $this->get_optimization_state() );
		// Lets check to see if dependencies are met before continuing...
		$this->loader->add_action( 'admin_init', $plugin_admin, 'rbp_plugin_dependencies' );
		// Let us load the plugin interface.
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'rbp_plugin_interface' );
		// Enque Scripts
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		// Add Ajax
		$this->loader->add_action('wp_ajax_nopriv_api_checkpoint', $plugin_admin, 'api_checkpoint');
		$this->loader->add_action('wp_ajax_api_checkpoint', $plugin_admin, 'api_checkpoint');
		

		// rmc_ajax_media_cleaner
			// Let us run the get_media_cleaner_state that will determine if a valid key is present.
		if( $this->get_media_cleaner_state() ){
			
			$this->loader->add_action('acf/init', $plugin_admin, 'rmc_classes', 30);

			$this->loader->add_action('acf/init', $plugin_admin, 'rmc_functions', 30);
			$this->loader->add_action('wp_ajax_nopriv_do_init_remove_unused_media', $plugin_admin, 'rmc_ajax_media_cleaner_remove');
			$this->loader->add_action('wp_ajax_do_init_remove_unused_media', $plugin_admin, 'rmc_ajax_media_cleaner_remove');

			$this->loader->add_action('wp_ajax_nopriv_rmc_ajax_media_swap', $plugin_admin, 'rmc_ajax_media_swap');
			$this->loader->add_action('wp_ajax_rmc_ajax_media_swap', $plugin_admin, 'rmc_ajax_media_swap');
			
			$this->loader->add_action('wp_ajax_nopriv_rmc_ajax_media_cleaner', $plugin_admin, 'rmc_ajax_media_cleaner');
			$this->loader->add_action('wp_ajax_rmc_ajax_media_cleaner', $plugin_admin, 'rmc_ajax_media_cleaner');

			$this->loader->add_action('wp_ajax_nopriv_rmc_ajax_media_cleaner_settings', $plugin_admin, 'rmc_ajax_media_cleaner_settings');
			$this->loader->add_action('wp_ajax_rmc_ajax_media_cleaner_settings', $plugin_admin, 'rmc_ajax_media_cleaner_settings');



			
			// ronikdesigns_cron_auth
			$this->loader->add_action( 'rmc_media_sync', $plugin_admin, 'rmc_media_sync' );
			if (!wp_next_scheduled('rmc_media_sync')) {
				wp_schedule_event(strtotime('04:00:00'), 'daily', 'rmc_media_sync');
			}

			$this->loader->add_action( 'save_post', $plugin_admin, 'rmc_media_sync_save' );

			// 			//* delete transient
			// function ronikdesigns_delete_custom_transient(){
			//     delete_transient('loop-news-arch');
			//     delete_transient('loop-teams-arch');
			// }
			// add_action('update option', 'ronikdesigns_delete_custom_transient');
			// add_action('save_post', 'ronikdesigns_delete_custom_transient');
			// add_action('delete_post', 'ronikdesigns_delete_custom_transient');

		}
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {
		$plugin_public = new Ronik_Base_Public( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

		$this->loader->add_action('rest_api_init', $plugin_public, 'ronikdesignsbase_rest_api_init');
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Ronik_Base_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

	/**
	 * Retrieve the valid auth of media cleaner.
	 *
	 * @since     1.0.0
	 * @return    string    .
	 */
	public function get_media_cleaner_state() {
		// Down the line we should fetch the key from the marketing site on every load if the key gets invalidated.
		$rbp_media_cleaner_api_key = get_option('rbp_media_cleaner_api_key') ? get_option('rbp_media_cleaner_api_key') : "";
		$rbp_media_cleaner_validation = get_option('rbp_media_cleaner_api_key_validation') ? get_option('rbp_media_cleaner_api_key_validation') : "invalid";
		if($rbp_media_cleaner_api_key && ($rbp_media_cleaner_validation !== "invalid")){
			$media_cleaner_state = true;
		} else {
			$media_cleaner_state = false;
		}
		return $media_cleaner_state;
	}

	/**
	 * Retrieve the valid auth of media cleaner.
	 *
	 * @since     1.0.0
	 * @return    string    .
	 */
	public function get_optimization_state() {
		// Down the line we should fetch the key from the marketing site on every load if the key gets invalidated.
		$rbp_optimization_api_key = get_option('rbp_optimization_api_key') ? get_option('rbp_optimization_api_key') : "";
		$rbp_optimization_validation = get_option('rbp_optimization_api_key_validation') ? get_option('rbp_optimization_api_key_validation') : "invalid";
		if($rbp_optimization_api_key && ($rbp_optimization_validation !== "invalid")){
			$optimization_state = true;
		} else{
			$optimization_state = false;
		}
		return $optimization_state;
	}

}
