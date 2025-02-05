<?php
/**
 * Main WPSyncSheets_For_WooCommerce namespace.
 *
 * @since 1.0.0
 * @package wpsyncsheets-for-woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }
if ( ! class_exists( 'WPSSW_Gravity_Form_Product_Options' ) ) :
	/**
	 * Class WPSSW_Gravity_Form_Product_Options.
	 */
	class WPSSW_Gravity_Form_Product_Options extends WPSSW_Order_Utils {
		/**
		 * Store header list.
		 *
		 * @var array $wpssw_headers.
		 */
		public static $wpssw_headers = array();
		/**
		 * Class Contructor.
		 */
		public function __construct() {
			if ( $this->wpssw_is_pugin_active() ) {
				$this->prepare_headers();
				add_filter( 'wpsyncsheets_order_headers', __CLASS__ . '::get_header_list', 10, 1 );
			}
		}
		/**
		 * Check if Gravity form plugin is active.
		 */
		public static function wpssw_is_pugin_active() {
			if ( class_exists( 'GFForms' ) && class_exists( 'WC_GFPA_Main' ) ) {
				return true;
			}
			return false;
		}
		/**
		 * Prepare Header List.
		 */
		protected function prepare_headers() {
			$headers         = array();
			$forms           = GFAPI::get_forms();
			$gravityformhead = array();
			foreach ( $forms as $form ) {
				foreach ( $form['fields'] as $formfield ) {
					$gravityformhead[] = 'GF ' . $formfield->label;
				}
			}
			if ( ! empty( $gravityformhead ) ) {
				$headers = array_filter( array_values( array_unique( $gravityformhead ) ) );
			}
			self::$wpssw_headers = $headers;
		}
		/**
		 * Get Header List.
		 *
		 * @param array $headers .
		 */
		public static function get_header_list( $headers = array() ) {
			if ( ! empty( self::$wpssw_headers ) ) {
				$headers['WPSSW_Gravity_Form_Product_Options'] = self::$wpssw_headers;
			}
			return $headers;
		}
		/**
		 * Get Value for given header name.
		 *
		 * @param string $wpssw_headers_name Header name.
		 * @param object $wpssw_order order object.
		 * @param string $wpssw_operation operation to perfom on sheet.
		 * @param array  $wpssw_custom_value .
		 * @param array  $wpssw_product_headers .
		 */
		public static function get_value( $wpssw_headers_name, $wpssw_order, $wpssw_operation = 'insert', $wpssw_custom_value = array(), $wpssw_product_headers = array() ) {
			return self::prepare_value( $wpssw_headers_name, $wpssw_order, $wpssw_operation, $wpssw_custom_value, $wpssw_product_headers );
		}
		/**
		 * Prepare Value for given header name.
		 *
		 * @param string $wpssw_headers_name Header name.
		 * @param object $wpssw_order order object.
		 * @param string $wpssw_operation operation to perfom on sheet.
		 * @param array  $wpssw_custom_value .
		 * @param array  $wpssw_product_headers .
		 */
		public static function prepare_value( $wpssw_headers_name, $wpssw_order, $wpssw_operation, $wpssw_custom_value = array(), $wpssw_product_headers = array() ) {
			if ( 0 === strpos( $wpssw_headers_name, 'GF ' ) ) {
				$wpssw_headers_name = trim( str_replace( 'GF ', '', $wpssw_headers_name ) );
			}
			$wpssw_value = array();
			$wpssw_items = $wpssw_order->get_items();

			foreach ( $wpssw_items as $wpssw_item ) {
				$wpssw_metadata       = $wpssw_item->get_formatted_meta_data();
				$wpssw_gravitymetaval = '';
				foreach ( $wpssw_metadata as $wpssw_meta ) {
					if ( (string) $wpssw_meta->key === (string) $wpssw_headers_name ) {
						preg_match_all( '/href="(.*?)"/i', $wpssw_meta->value, $matches );
						if ( isset( $matches[1] ) && ! empty( $matches[1] ) ) {

							$wpssw_gravitymetaval .= implode( ', ', $matches[1] );
						} else {
							$wpssw_gravitymetaval .= $wpssw_meta->value . ',';
						}
					}
				}
				$wpssw_gravitymetaval = rtrim( $wpssw_gravitymetaval, ',' );
				if ( is_array( $wpssw_gravitymetaval ) ) {
					$wpssw_value[] = implode( ',', $wpssw_gravitymetaval );
				} else {
					$wpssw_value[] = $wpssw_gravitymetaval;
				}
			}
			return $wpssw_value;
		}
	}
	new WPSSW_Gravity_Form_Product_Options();
endif;
