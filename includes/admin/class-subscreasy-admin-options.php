<?php
/**
 * The Subscreasy_Admin_Options class.
 *
 * @package Subscreasy/Admin
 * @author  subscrEASY
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'Subscreasy_Admin_Options' ) ) :

	/**
	 * Generates the options page.
	 */
	class Subscreasy_Admin_Options {
		/**
		 * The constructor.
		 * 
		 * @since 1.0.0
		 */
		public function __construct() {
			add_action( 'admin_menu', array( $this, 'add_page' ) );
			add_action( 'admin_init', array( $this, 'register_settings' ) );
			add_action( 'wp_ajax_subscreasy_test_connectivity', array( $this, 'test_connectivity' ) );

            add_action( 'show_user_profile', array($this, 'extra_user_profile_fields' ) );
            add_action( 'edit_user_profile', array($this, 'extra_user_profile_fields' ) );

            add_action( 'personal_options_update', array($this, 'save_extra_user_profile_fields' ) );
            add_action( 'edit_user_profile_update', array($this, 'save_extra_user_profile_fields' ) );
		}

		/**
		 * Registers option page.
		 *
		 * @since 1.0.0
		 */
		public function add_page() {
			add_options_page(
				'subscrEASY',
				'subscrEASY',
				'manage_options',
				'subscreasy',
				array( $this, 'render' )
			);
		}

		/**
		 * Registers page settings.
		 *
		 * @since 1.0.0
		 */
		public function register_settings() {
			register_setting( 'subscreasy', 'subscreasy' );
		}

		/**
		 * Renders page's HTML.
		 *
		 * @since 1.0.0
		 */
		public function render() {
			require_once SUBSCREASY_ROOT_PATH . '/includes/admin/views/options.php';
		}

		/**
		 * Test's API connectivity
		 *
		 * @since 1.0.0
		 */
		public function test_connectivity() {
			// Settings.
			$settings = get_option( 'subscreasy' );

			// API URL.
			$api_url = 'production' === $settings['environment'] ? 'https://prod.subscreasy.com/api/offers' : 'https://sandbox.subscreasy.com/api/offers';

			// HTTP headers.
			$headers  = array(
				'Accept: application/json, text/plain, */*',
				'Authorization: Apikey ' . $settings['api_key'],
			);

			/*
			 * cURL request.
			 */
			$ch = curl_init();

			// cURL options.
			curl_setopt( $ch, CURLOPT_HTTPHEADER,     $headers );
			curl_setopt( $ch, CURLOPT_URL,            $api_url );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
			
			$response = curl_exec( $ch );

			curl_close( $ch );

			echo $response;

			wp_die();
		}

        /**
         * Add extra user profile fields
         *
         * @param $user
         */
        function extra_user_profile_fields( $user ) {
		    require_once SUBSCREASY_ROOT_PATH . '/includes/admin/views/user-fields.php';
        }

        /**
         * Save the extra user profile fields and return true/false
         *
         * @param $user_id
         * @return bool
         */
        function save_extra_user_profile_fields( $user_id ) {
            if ( !current_user_can( 'edit_user', $user_id ) ) {
                return false;
            }
            update_user_meta( $user_id, 'user_phone', $_POST['phone'] );
            update_user_meta( $user_id, 'user_secureID', $_POST['secureID'] );
        }
	}

	// Initialize the class
	new Subscreasy_Admin_Options;

endif;