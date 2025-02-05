<?php
/**
 * Main WPSyncSheets_For_WooCommerce namespace.
 *
 * @since 1.0.0
 * @package wpsyncsheets-for-woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }
if ( ! class_exists( 'WPSSW_YITH_WooCommerce_Delivery_Date' ) ) :
	/**
	 * Class WPSSW_YITH_WooCommerce_Delivery_Date.
	 */
	class WPSSW_YITH_WooCommerce_Delivery_Date extends WPSSW_Order_Utils {
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
		 * Check if YITH WooCommerce Delivery Date plugin is active or not.
		 */
		public static function wpssw_is_pugin_active() {
			if ( ! class_exists( 'YITH_Delivery_Date' ) ) {
				return false;
			}
			return true;
		}
		/**
		 * Prepare Header List.
		 */
		protected function prepare_headers() {
			self::$wpssw_headers = self::wpssw_yith_delivery_date_headers();
		}
		/**
		 * Get Header List.
		 *
		 * @param array $headers .
		 */
		public static function get_header_list( $headers = array() ) {
			if ( ! empty( self::$wpssw_headers ) ) {
				$headers['WPSSW_YITH_WooCommerce_Delivery_Date'] = self::$wpssw_headers;
			}
			return $headers;
		}
		/**
		 * YITH WooCommerce Delivery Date headers.
		 */
		public static function wpssw_yith_delivery_date_headers() {
			$yithkeys = array( 'ywcdd_order_delivery_date', 'ywcdd_order_shipping_date', 'ywcdd_order_slot_from', 'ywcdd_order_slot_to', 'ywcdd_order_carrier' );
			$yithval  = array( 'YITH Order Delivery Date', 'YITH Order Shipping Date', 'YITH Time From', 'YITH Time To', 'YITH Order Carrier' );
			return array_combine( $yithkeys, $yithval );
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
			// YITH Woocommerce Delivery Date.
			$wpssw_value                = array();
			$wpssw_insert_val           = '';
			$yith_delivery_date_headers = self::wpssw_yith_delivery_date_headers();
			$yithkey                    = array_search( $wpssw_headers_name, $yith_delivery_date_headers, true );
			if ( $yithkey ) {
				$wpssw_insert_val = $wpssw_order->get_meta( $yithkey );
			}
			$wpssw_value[] = $wpssw_insert_val;
			return $wpssw_value;
		}
	}
	new WPSSW_YITH_WooCommerce_Delivery_Date();
endif;
