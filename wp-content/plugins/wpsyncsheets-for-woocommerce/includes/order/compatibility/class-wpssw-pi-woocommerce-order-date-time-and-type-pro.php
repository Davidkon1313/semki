<?php
/**
 * Main WPSyncSheets_For_WooCommerce namespace.
 *
 * @since 1.0.0
 * @package wpsyncsheets-for-woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }

if ( ! class_exists( 'WPSSW_Pi_Woocommerce_Order_Date_Time_And_Type_Pro' ) ) :

	/**
	 * Class WPSSW_Pi_Woocommerce_Order_Date_Time_And_Type_Pro.
	 */
	class WPSSW_Pi_Woocommerce_Order_Date_Time_And_Type_Pro extends WPSSW_Order_Utils {

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
				self::prepare_headers();
				add_filter( 'wpsyncsheets_order_headers', __CLASS__ . '::get_header_list', 10, 1 );
			}
		}

		/**
		 * Check if Order date, Order pickup, Order date time, Pickup Location, delivery date for WooCommerce plugin is active or not.
		 */
		public static function wpssw_is_pugin_active() {
			if ( ! class_exists( 'pi_dtt_order' ) ) {
				return false;
			}
			return true;
		}

		/**
		 * Prepare Header List.
		 */
		public static function prepare_headers() {
			$pikeys              = array( 'pi_delivery_date', 'pi_delivery_type', 'pi_delivery_time', 'pi_system_delivery_date', 'pickup_location' );
			$pival               = array( 'PI Delivery Date', 'PI Delivery Type', 'PI Delivery Time', 'PI System Delivery Date', 'PI Pickup Location' );
			self::$wpssw_headers = array_combine( $pikeys, $pival );
		}

		/**
		 * Get Header List.
		 *
		 * @param array $headers .
		 */
		public static function get_header_list( $headers = array() ) {
			if ( ! empty( self::$wpssw_headers ) ) {
				$headers['WPSSW_Pi_Woocommerce_Order_Date_Time_And_Type_Pro'] = self::$wpssw_headers;
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
			$wpssw_insert_val = '';
			$wpssw_value      = array();
			self::prepare_headers();
			$pi_delivery_date_headers = self::$wpssw_headers;
			$pikey                    = array_search( $wpssw_headers_name, $pi_delivery_date_headers, true );
			if ( $pikey ) {
				$wpssw_insert_val = $wpssw_order->get_meta( $pikey );
			}
			$breaks           = array( '<br />', '<br>', '<br/>' );
			$wpssw_insert_val = wp_strip_all_tags( str_ireplace( $breaks, "\r\n", $wpssw_insert_val ) );
			$wpssw_value[]    = $wpssw_insert_val;
			return $wpssw_value;
		}
	}
	new WPSSW_Pi_Woocommerce_Order_Date_Time_And_Type_Pro();
endif;
