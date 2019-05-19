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
         * Add variables to the Wordpress query that are needed for the plugin
         *
         * @param $query_vars
         * @return array
         */
        function subscreasy_query_vars( $query_vars )
        {
            $query_vars[] = 'paymentId';
            $query_vars[] = 'authCode';
            $query_vars[] = 'payment';
            $query_vars[] = 'offerID';
            $query_vars[] = 'subscriptionId';
            $query_vars[] = 'nextChargingDate';
            $query_vars[] = 'trial';
            return $query_vars;
        }


        /**
         * Parse the Wordpress request and check if the payment or thank you variables are set
         * and show those pages accordingly
         *
         * @param $wp
         */
        function subscreasy_parse_request( &$wp )
        {
            // Get Subscreasy options
            $options = get_option( 'subscreasy', array() );

            // Extract the callback URL from the options
            $callbackUrl = $options['callback_url'];

            // Check if the callback URL ends with '/' and remove it if it does
            $callbackUrl = strpos($callbackUrl, '/') == 0 ? substr($callbackUrl, 1, strlen($callbackUrl) - 1) : $callbackUrl;

            // Check if the variable payment in the request is set and show the subscreasy form
            if ( array_key_exists( 'payment', $wp->query_vars ) ) {
                // Get the page's id
                $page = get_page_by_path( 'subscribe-form' );
                // Get the page's permalink
                $page_permalink = get_permalink( $page );

                // Set the cookie for the offerID
                setcookie('offerID', $wp->query_vars['offerID'], time() + (86400 * 30), "/"); // 86400 = 1 day

                // Redirect to the form's page
                wp_redirect( $page_permalink );
                exit();
            }
            else if (array_key_exists('subscriptionId', $wp->query_vars) && ($wp->request == $callbackUrl || get_home_url() . '/' . $wp->request == $callbackUrl || strpos($callbackUrl, $wp->request) >= 0) ) {
                // Check if the request contains subscriptionID which would mean that the subscription is successfully made
                // Get the page's id
                $page = get_page_by_path( 'subscreasy-thank-you' );

                // Set cookies for the paymentID, authCode, subscriptionID, nextChargingDate and the trial, all of which are sent from the SubscrEasy API
                setcookie('paymentId', $wp->query_vars['paymentId'], time() + (86400 * 30), "/");
                setcookie('authCode', $wp->query_vars['authCode'], time() + (86400 * 30), "/");
                setcookie('subscriptionId', $wp->query_vars['subscriptionId'], time() + (86400 * 30), "/");
                setcookie('nextChargingDate', $wp->query_vars['nextChargingDate'], time() + (86400 * 30), "/");
                setcookie('trial', $wp->query_vars['trial'], time() + (86400 * 30), "/");

                // Get the thank you page and display it
                require_once SUBSCREASY_ROOT_PATH . '/includes/views/subscreasy-success.php';

                exit();
            }
            // Return if none of the variables is set
            return;

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

            add_filter( 'query_vars', array($this, 'subscreasy_query_vars' ) );
            add_action( 'parse_request', array($this, 'subscreasy_parse_request' ) );

            add_filter( 'display_post_states', array($this, 'ecs_add_post_state'), 10, 2 );
		}

        /**
         * Add pages state when looking in the pages from the dashboard
         *
         * @param $post_states
         * @param $post
         * @return array
         */
        function ecs_add_post_state( $post_states, $post ) {
            if( $post->post_name == 'subscribe-form' ) {
                $post_states[] = 'Subscribe form - SubscrEASY';
            }
            if( $post->post_name == 'subscreasy-thank-you' ) {
                $post_states[] = 'Thank you page - SubscrEASY';
            }
            return $post_states;
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
					'textId'      => __( 'Id', 'susbcreasy' ),
					'textName'    => __( 'Name', 'susbcreasy' ),
					'textPrice'   => __( 'Price', 'susbcreasy' ),
					'textShortcode'   => __( 'Shortcode', 'susbcreasy' ),
					'textCurr'    => __( 'TL', 'susbcreasy' ),
					'textErr'     => __( 'Something went wrong. Please try again.', 'subscreasy' ),
					'textUnauth'  => __( 'Unauthorized: access denied.', 'subscreasy' ),
					'textUnsaved' => __( 'Unsaved changes!', 'subscreasy' ),
				) );
			else :

                // Payment form style and scripts
                wp_enqueue_style( 'payment-form', SUBSCREASY_ROOT_URL . 'assets/css/app.css');

                wp_enqueue_style('bootstrap', SUBSCREASY_ROOT_URL . 'assets/css/bootstrap.min.css');

                wp_enqueue_style('ubuntu-family', SUBSCREASY_ROOT_URL . 'assets/css/ubuntu.css');

                wp_enqueue_style('font-awesome', SUBSCREASY_ROOT_URL . 'assets/css/font-awesome.min.css');

                wp_enqueue_script( 'jquery' );
                wp_enqueue_script( 'jquery-payment', 'https://cdnjs.cloudflare.com/ajax/libs/jquery.payment/1.2.3/jquery.payment.min.js', array( 'jquery', 'subscreasy_scripts' ) );
                wp_enqueue_script( 'payment-form-lang', SUBSCREASY_ROOT_URL . '/assets/js/langDictionary.js', array( 'jquery' ));

				// Front-end includes.
				wp_enqueue_script( 'js-cookie', SUBSCREASY_ROOT_URL . 'assets/vendor/js.cookie.js', array( 'jquery' ), true );
				wp_enqueue_script( 'jquery-redirect', SUBSCREASY_ROOT_URL . 'assets/vendor/jquery.redirect.js', array( 'js-cookie' ), true );

				wp_enqueue_script( 'subscreasy_scripts', SUBSCREASY_ROOT_URL . 'assets/js/public/scripts.js', array( 'jquery-redirect' ), false );

				wp_enqueue_script('subscreasy_form_script', SUBSCREASY_ROOT_URL . '/assets/js/app.js');

                // Old API url
//				if ( 'production' === get_option( 'subscreasy' )['environment'] ) {
//					$api_url = 'https://' . get_option( 'subscreasy' )['site_name'] . '.abone.io/payment3ds/';
//				} else {
//					$api_url = 'https://' . get_option( 'subscreasy' )['site_name'] . '.aboneliks.xyz/payment3ds/';
//				}

                $api_url = get_home_url() . '/?payment=1';

				wp_localize_script( 'subscreasy_scripts', 'subscreasyParams', array(
					'ajaxURL'    => admin_url( 'admin-ajax.php' ),
					'apiURL'    => $api_url,
					'loginURL'   => wp_login_url( $api_url ),
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
            $page = get_page_by_path( 'subscribe-form' , OBJECT );
            if ( !isset($page) ) {
                $my_post = array(
                    'post_title' => wp_strip_all_tags('Subscribe Form'),
                    'post_name' => 'subscribe-form',
                    'post_content' => '[subscreasy_subscribe_form]',
                    'post_status' => 'publish',
                    'post_author' => 1,
                    'post_type' => 'page',
                );

                // Insert the post into the database
                wp_insert_post($my_post);
            }

            $page = get_page_by_path( 'subscreasy-thank-you' , OBJECT );
            if ( !isset($page) ) {
                $my_post = array(
                    'post_title' => wp_strip_all_tags('Thank you'),
                    'post_name' => 'subscreasy-thank-you',
                    'post_content' => '<h1>for your subscription</h1>',
                    'post_status' => 'publish',
                    'post_author' => 1,
                    'post_type' => 'page',
                );

                // Insert the post into the database
                wp_insert_post($my_post);
            }
			self::settings();
		}
		
		/**
		 * Deactivation hooks.
		 *
		 * @since 1.0.0
		 */
		public static function deactivate() {
			// Delete the page for subscription form.
            $page = get_page_by_path( 'subscribe-form' , OBJECT );
            if ( isset( $page ) ) {
                wp_delete_post($page->ID);
            }

            $page = get_page_by_path( 'subscreasy-thank-you' , OBJECT );
            if ( isset( $page ) ) {
                wp_delete_post($page->ID);
            }

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