<?php
/**
 * Main WPSyncSheets_For_WooCommerce namespace.
 *
 * @since 1.0.0
 * @package wpsyncsheets-for-woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }
if ( ! class_exists( 'WPSSW_WooCommerce_Order_Delivery' ) ) :
	/**
	 * Class WPSSW_WooCommerce_Order_Delivery.
	 */
	class WPSSW_WooCommerce_Order_Delivery extends WPSSW_Order_Utils {
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
		 * Check if WooCommerce order delivery by Themesquad plugin is active or not.
		 */
		public static function wpssw_is_pugin_active() {
			if ( ! class_exists( 'WC_Order_Delivery' ) ) {
				return false;
			}
			return true;
		}
		/**
		 * Prepare Header List.
		 */
		protected function prepare_headers() {
			self::$wpssw_headers = array( 'Delivery Date', 'Schedule Time' );
		}
		/**
		 * Get Header List.
		 *
		 * @param array $headers .
		 */
		public static function get_header_list( $headers = array() ) {
			if ( ! empty( self::$wpssw_headers ) ) {
				$headers['WPSSW_WooCommerce_Order_Delivery'] = self::$wpssw_headers;
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
			// WooCommerce order delivery by Themesquad.
			$wpssw_value      = array();
			$wpssw_insert_val = '';
			if ( 'Delivery Date' === (string) $wpssw_headers_name ) {
				$wpssw_insert_val = $wpssw_order->get_meta( '_delivery_date' );
			}
			if ( 'Schedule Time' === (string) $wpssw_headers_name ) {
				$delivery_time = $wpssw_order->get_meta( '_delivery_time_frame' );
				$time_to       = '';
				$time_from     = '';
				if ( isset( $delivery_time['time_from'] ) ) {
					$time_from = $delivery_time['time_from'];
				}
				if ( isset( $delivery_time['time_to'] ) ) {
					$time_to = $delivery_time['time_to'];
				}
				$wpssw_insert_val = $time_from . ' - ' . $time_to;
			}
			$wpssw_value[] = $wpssw_insert_val;
			return $wpssw_value;
		}
	}
	new WPSSW_WooCommerce_Order_Delivery();
endif;
