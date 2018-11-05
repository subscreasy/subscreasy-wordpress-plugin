<?php
/**
 * Plugin Name: subscrEASY
 * Plugin URI: https://www.subscreasy.com/
 * Description: subscrEASY's official plugin.
 * Version: 1.0.0
 * Author: subscrEASY
 * Author URI: https://www.subscreasy.com/
 * Requires at least: 4.4.0
 * Tested up to: 4.9.8
 *
 * Text Domain: subscreasy
 * Domain Path: /languages/
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package Subscreasy
 * @author  subscrEASY
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/*
 * Globals constants.
 */
define( 'SUBSCREASY_MIN_PHP_VER', '5.6.0' );
define( 'SUBSCREASY_MIN_WP_VER',  '4.4.0' );
define( 'SUBSCREASY_ROOT_PATH',   dirname( __FILE__ ) );
define( 'SUBSCREASY_ROOT_URL',    plugin_dir_url( __FILE__ ) );

if( ! class_exists( 'Subscreasy' ) ) :

	/**
	 * The main class.
	 *
	 * @since 1.0.0
	 */
	class Subscreasy {
		/**
		 * Plugin version.
		 *
		 * @since 1.0.0
		 *
		 * @var string
		 */
		public $version = '1.0.0';

		/**
		 * The singleton instance of Subscreasy.
		 *
		 * @since 1.0.0
		 *
		 * @var Subscreasy
		 */
		private static $instance = null;

		/**
		 * Returns the singleton instance of Subscreasy.
		 *
		 * Ensures only one instance of Subscreasy is/can be loaded.
		 *
		 * @since 1.0.0
		 *
		 * @return Subscreasy
		 */
		public static function get_instance() {
			if( null === self::$instance ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * The constructor.
		 *
		 * Private constructor to make sure it can not be called directly from outside the class.
		 *
		 * @since 1.0.0
		 */
		private function __construct() {
			$this->includes();
			$this->hooks();

			// Plugin has just loaded.
			do_action( 'subscreasy_loaded' );
		}

		/**
		 * Update plugin settings.
		 *
		 * @since 1.0.0
		 */
		private static function settings() {
			$settings = get_option( 'subscreasy', array() );

			if ( empty( $settings ) ) {
				$settings['environment'] = 'sandbox';
				
				update_option( 'subscreasy', $settings );
			}
		}

		/**
		 * Includes the required files.
		 *
		 * @since 1.0.0
		 */
		public function includes() {
			/**
			 * Global includes.
			 */
			include_once SUBSCREASY_ROOT_PATH . '/includes/class-subscreasy-shortcodes.php';

			/*
			 * Back-end includes.
			 */
			if ( is_admin() ) {
				include_once SUBSCREASY_ROOT_PATH . '/includes/admin/class-subscreasy-admin-options.php';
			}
		}

		/**
		 * Plugin hooks.
		 *
		 * @since 1.0.0
		 */
		public function hooks() {
			add_action( 'admin_enqueue_scripts', array( $this, 'scripts' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'scripts' ) );
		}

		/**
		 * Loads plugin styles and scripts.
		 *
		 * @since 1.0.0
		 */
		public function scripts() {
			$is_admin = ( 'admin_enqueue_scripts' === current_action() );

			if ( $is_admin ) :
				// Back-end styles and scripts.
				wp_enqueue_style( 'subscreasy_admin_style', SUBSCREASY_ROOT_URL . 'assets/css/admin/style.css' );
				wp_enqueue_script( 'subscreasy_admin_scripts', SUBSCREASY_ROOT_URL . 'assets/js/admin/scripts.js', array( 'jquery' ), true );
				wp_localize_script( 'subscreasy_admin_scripts', 'subscreasyAdminParams', array(
					'ajaxURL'     => admin_url( 'admin-ajax.php' ),
					'textName'    => __( 'Name', 'susbcreasy' ),
					'textPrice'   => __( 'Price', 'susbcreasy' ),
					'textCurr'    => __( 'LT', 'susbcreasy' ),
					'textErr'     => __( 'Something went wrong. Please try again.', 'subscreasy' ),
					'textUnauth'  => __( 'Unauthorized: access denied.', 'subscreasy' ),
					'textUnsaved' => __( 'Unsaved changes!', 'subscreasy' ),
				) );
			else :
				// Front-end includes.
				wp_enqueue_script( 'js-cookie', SUBSCREASY_ROOT_URL . 'assets/vendor/js.cookie.js', array( 'jquery' ), true );
				wp_enqueue_script( 'jquery-redirect', SUBSCREASY_ROOT_URL . 'assets/vendor/jquery.redirect.js', array( 'js-cookie' ), true );
				wp_enqueue_script( 'subscreasy_scripts', SUBSCREASY_ROOT_URL . 'assets/js/public/scripts.js', array( 'jquery-redirect' ), true );

				if ( 'production' === get_option( 'subscreasy' )['environment'] ) {
					$api_url = 'https://' . get_option( 'subscreasy' )['site_name'] . '.abone.io/payment3ds/';
				} else {
					$api_url = 'https://' . get_option( 'subscreasy' )['site_name'] . '.aboneliks.xyz/payment3ds/';
				}
				
				wp_localize_script( 'subscreasy_scripts', 'subscreasyParams', array(
					'ajaxURL'    => admin_url( 'admin-ajax.php' ),
					'apiURL'    => $api_url,
					'loginURL'   => wp_login_url( get_permalink() ),
					'isLoggedIn' => is_user_logged_in(),
					'name'       => is_user_logged_in() ? get_userdata( get_current_user_id() )->first_name : '',
					'surname'    => is_user_logged_in() ? get_userdata( get_current_user_id() )->last_name : '',
					'email'      => is_user_logged_in() ? get_userdata( get_current_user_id() )->user_email : '',
					'phone'      => is_user_logged_in() ? get_userdata( get_current_user_id() )->phone : '',
				) );
			endif;
		}

		/**
		 * Activation hooks.
		 *
		 * @since 1.0.0
		 */
		public static function activate() {
			self::settings();
		}
		
		/**
		 * Deactivation hooks.
		 *
		 * @since 1.0.0
		 */
		public static function deactivate() {
			// Nothing to do for now.
		}

		/**
		 * Uninstall hooks.
		 *
		 * @since 1.0.0
		 */
		public static function uninstall() {
			include_once SUBSCREASY_ROOT_PATH . 'uninstall.php';
		}
	}

	/**
	 * Main instance of Subscreasy.
	 *
	 * Returns the main instance of Subscreasy.
	 *
	 * @since 1.0.0
	 *
	 * @return Subscreasy
	 */
	function init_subscreasy() {
		return Subscreasy::get_instance();
	}

	// Global for backwards compatibility.
	$GLOBALS['subscreasy'] = init_subscreasy();

	// Plugin hooks.
	register_activation_hook( __FILE__,   array( 'Subscreasy', 'activate' ) );
	register_deactivation_hook( __FILE__, array( 'Subscreasy', 'deactivate' ) );
	register_uninstall_hook( __FILE__,    array( 'Subscreasy', 'uninstall' ) );

endif;