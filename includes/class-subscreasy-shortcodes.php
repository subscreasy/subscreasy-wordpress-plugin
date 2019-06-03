<?php
/**
 * The Subscreasy_Shortcodes class.
 *
 * @package Subscreasy
 * @author  subscrEASY
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'Subscreasy_Shortcodes' ) ) :

	/**
	 * Generates plugin shortcodes.
	 */
	class Subscreasy_Shortcodes {
		/**
		 * The constructor.
		 *
		 * @since 1.0.0
		 */
		public function __construct() {
			add_shortcode( 'subscreasy_button', array( $this, 'button' ) );

			add_shortcode( 'subscreasy_subscribe_form', array( $this, 'subscribe_form') );

			add_shortcode('subsceasy_user_subs', array( $this, 'subscreasy_user_subscriptions' ) );
		}

		/**
		 * Renders the [subscreasy_button] shortcode.
		 *
		 * Example [subscreasy_button title="Click Me!" offer_id="100"].
		 *
		 * @param array $atts Shortcode attributes.
		 *
		 * @return string HTML output to render.
		 */
		public function button( $atts ) {
			$atts = shortcode_atts(
				array(
					'title'    => __( 'Click Me!', 'subscreasy' ),
					'offer_id' => 0,
					'class'    => '',
				),
				$atts,
				'subscreasy_button'
			);
			
			ob_start();
			require SUBSCREASY_ROOT_PATH . '/includes/views/subscreasy-button.php';
			return ob_get_clean();
		}

        /**
         * Renders the [subscreasy_subscribe_form] shortcode.
         *
         * @param array $atts Shortcode attributes.
         *
         * @return string HTML output to render.
         */
        public function subscribe_form ( $atts ) {

            ob_start();
            require SUBSCREASY_ROOT_PATH . '/includes/views/subscreasy-form.php';
            return ob_get_clean();
        }

        /**
         * A shortcode that displays the users subscriptions using the secureID stored in the database for each user
         * @param $atts
         * @return string
         */
        public function subscreasy_user_subscriptions($atts) {
            // SecureID
            $user = wp_get_current_user();

            $secureID = get_user_meta($user->ID, 'user_secureID', true);
            $options = get_option( 'subscreasy', array() );

            // API URL.
            $api_url_user_subs = 'production' === $options['environment'] ? 'https://prod.subscreasy.com//api/invoices/subscriber/' . $secureID : 'https://sandbox.subscreasy.com/api/invoices/subscriber/' . $secureID;

            // HTTP headers.
            $headers  = array(
                'Accept: application/json, text/plain, */*',
                'Authorization: Apikey ' . $options['api_key'],
            );

            /*
             * cURL request.
             */
            $ch = curl_init();

            // cURL options.
            curl_setopt( $ch, CURLOPT_HTTPHEADER,     $headers );
            curl_setopt( $ch, CURLOPT_URL,            $api_url_user_subs );
            curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );

            $response = curl_exec( $ch );

            curl_close( $ch );

            // Get user data
            $user_subscriptions = json_decode($response);

            // Should return a link with user's id
            return $user_subscriptions;
        }
	}

	new Subscreasy_Shortcodes;

endif;