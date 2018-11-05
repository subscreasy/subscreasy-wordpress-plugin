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
	}

	new Subscreasy_Shortcodes;

endif;