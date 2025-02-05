<?php
/**
 * Main WPSyncSheets_For_WooCommerce namespace.
 *
 * @since 1.0.0
 * @package wpsyncsheets-for-woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }
if ( ! class_exists( 'WPSSW_Custom_Headers' ) ) :
	/**
	 * Class WPSSW_Custom_Headers.
	 */
	class WPSSW_Custom_Headers extends WPSSW_Order_Utils {
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
			self::prepare_headers();
			add_filter( 'wpsyncsheets_order_headers', __CLASS__ . '::get_header_list', 10, 1 );
		}
		/**
		 * Prepare Header List.
		 */
		public static function prepare_headers() {
			$wpssw_custom_headers = apply_filters( 'wpssw_custom_headers', array() ); // use this.
			if ( empty( $wpssw_custom_headers ) ) {
				$wpssw_custom_headers = apply_filters( 'woosheets_custom_headers', array() ); // depreciated.
			}
			self::$wpssw_headers = $wpssw_custom_headers;
		}
		/**
		 * Get Header List.
		 *
		 * @param array $headers .
		 */
		public static function get_header_list( $headers = array() ) {

			if ( ! empty( self::$wpssw_headers ) ) {
				$headers['WPSSW_Custom_Headers'] = self::$wpssw_headers;
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
			$wpssw_value             = array();
			$wpssw_extra_headers_val = apply_filters( 'wpssw_custom_values', '', $wpssw_order->get_id(), $wpssw_headers_name ); // use this.
			if ( empty( $wpssw_extra_headers_val ) ) {
				$wpssw_extra_headers_val = apply_filters( 'woosheets_custom_values', '', $wpssw_order->get_id(), $wpssw_headers_name ); // depreciated.
			}
			if ( is_object( $wpssw_extra_headers_val ) ) {
				$wpssw_value = array();
			} elseif ( is_array( $wpssw_extra_headers_val ) ) {
				$wpssw_value[] = implode( ',', $wpssw_extra_headers_val );
			} else {
				$wpssw_value[] = $wpssw_extra_headers_val;
			}
			return $wpssw_value;
		}
	}
	new WPSSW_Custom_Headers();
endif;
