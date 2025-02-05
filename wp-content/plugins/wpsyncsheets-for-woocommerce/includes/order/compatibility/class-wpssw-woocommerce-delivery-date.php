<?php
/**
 * Main WPSyncSheets_For_WooCommerce namespace.
 *
 * @since 1.0.0
 * @package wpsyncsheets-for-woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }
if ( ! class_exists( 'WPSSW_WooCommerce_Delivery_Date' ) ) :
	/**
	 * Class WPSSW_WooCommerce_Delivery_Date.
	 */
	class WPSSW_WooCommerce_Delivery_Date extends WPSSW_Order_Utils {
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
		 * Check if WooCommerce Order Delivery Date plugin is active or not.
		 */
		public static function wpssw_is_pugin_active() {
			if ( self::wpssw_is_pro_pugin_active() || self::wpssw_is_lite_pugin_active() ) {
				return true;
			}
			return false;
		}
		/**
		 * Check if WooCommerce Order Delivery Date Pro plugin is active or not.
		 */
		public static function wpssw_is_pro_pugin_active() {
			if ( class_exists( 'order_delivery_date' ) ) {
				return true;
			}
			return false;
		}
		/**
		 * Check if WooCommerce Order Delivery Date Lite plugin is active or not.
		 */
		public static function wpssw_is_lite_pugin_active() {
			if ( class_exists( 'Order_Delivery_Date_Lite' ) ) {
				return true;
			}
			return false;
		}
		/**
		 * Prepare Header List.
		 */
		protected function prepare_headers() {
			$time_slot       = '';
			$locations_label = '';
			if ( self::wpssw_is_pro_pugin_active() ) {
				self::$wpssw_headers = WPSSW_Setting::wpssw_option( 'orddd_delivery_date_field_label' );
				$time_slot           = WPSSW_Setting::wpssw_option( 'orddd_delivery_timeslot_field_label' );
				$locations_label     = WPSSW_Setting::wpssw_option( 'orddd_location_field_label' );
			} elseif ( self::wpssw_is_lite_pugin_active() ) {
				self::$wpssw_headers = WPSSW_Setting::wpssw_option( 'orddd_lite_delivery_date_field_label' );
				$time_slot           = WPSSW_Setting::wpssw_option( 'orddd_lite_delivery_timeslot_field_label' );
			}
			if ( ! empty( self::$wpssw_headers ) && ! is_array( self::$wpssw_headers ) ) {
				self::$wpssw_headers = array( self::$wpssw_headers );
			}
			if ( ! empty( $time_slot ) && ! is_array( $time_slot ) ) {
				self::$wpssw_headers = array_merge( self::$wpssw_headers, array( $time_slot ) );
			}
			if ( ! empty( $locations_label ) && ! is_array( $locations_label ) ) {
				self::$wpssw_headers = array_merge( self::$wpssw_headers, array( $locations_label ) );
			}
		}
		/**
		 * Get Header List.
		 *
		 * @param array $headers .
		 */
		public static function get_header_list( $headers = array() ) {
			if ( ! empty( self::$wpssw_headers ) ) {
				$headers['WPSSW_WooCommerce_Delivery_Date'] = self::$wpssw_headers;
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
			$wpssw_value        = array();
			$hpos_order_enabled = WPSSW_Setting::wpssw_check_hpos_order_setting_enabled();
			if ( $hpos_order_enabled ) {
				$wpssw_value[] = $wpssw_order->get_meta( $wpssw_headers_name, true );
			} else {
				$wpssw_value[] = get_post_meta( $wpssw_order->get_id(), $wpssw_headers_name, true );
			}
			return $wpssw_value;
		}
	}
	new WPSSW_WooCommerce_Delivery_Date();
endif;
