<?php
/**
 * Main WPSyncSheets_For_WooCommerce namespace.
 *
 * @since 1.0.0
 * @package wpsyncsheets-for-woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }
if ( ! class_exists( 'WPSSW_Custom_Headers_For_Customer' ) ) :
	/**
	 * Class WPSSW_Custom_Headers_For_Customer.
	 */
	class WPSSW_Custom_Headers_For_Customer extends WPSSW_Customer_Utils {
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
			add_filter( 'wpsyncsheets_customer_headers', __CLASS__ . '::get_header_list', 10, 1 );
		}
		/**
		 * Prepare Header List.
		 */
		public static function prepare_headers() {
			$wpssw_custom_headers = apply_filters( 'wpssw_custom_headers_for_customer', array() ); // use this.
			self::$wpssw_headers  = $wpssw_custom_headers;
		}
		/**
		 * Get Header List.
		 *
		 * @param array $headers .
		 */
		public static function get_header_list( $headers = array() ) {

			if ( ! empty( self::$wpssw_headers ) ) {
				$headers['WPSSW_Custom_Headers_For_Customer'] = self::$wpssw_headers;
			}
			return $headers;
		}
		/**
		 * Get Value for given header name.
		 *
		 * @param string $wpssw_headers_name Header name.
		 * @param array  $customers_metadata metadata array of customer.
		 * @param int    $customer_id id of the customer being processed.
		 * @param mix    $wpssw_customer customers data.
		 * @param array  $wpssw_custom_value custom value.
		 */
		public static function get_value( $wpssw_headers_name, $customers_metadata, $customer_id, $wpssw_customer, $wpssw_custom_value = array() ) {
			return self::prepare_value( $wpssw_headers_name, $customers_metadata, $customer_id, $wpssw_customer, $wpssw_custom_value );
		}
		/**
		 * Prepare Value for given header name.
		 *
		 * @param string $wpssw_headers_name Header name.
		 * @param array  $customers_metadata metadata array of customer.
		 * @param int    $customer_id id of the customer being processed.
		 * @param mix    $wpssw_customer customers data.
		 * @param array  $wpssw_custom_value custom value.
		 */
		public static function prepare_value( $wpssw_headers_name, $customers_metadata, $customer_id, $wpssw_customer, $wpssw_custom_value ) {

			$wpssw_value             = '';
			$wpssw_extra_headers_val = apply_filters( 'wpssw_custom_values_for_customer', '', $customer_id, $wpssw_customer, $customers_metadata, $wpssw_headers_name ); // use this.

			if ( is_array( $wpssw_extra_headers_val ) ) {
				$wpssw_value = implode( ',', $wpssw_extra_headers_val );
			} else {
				$wpssw_value = $wpssw_extra_headers_val;
			}
			return $wpssw_value;
		}
	}
	new WPSSW_Custom_Headers_For_Customer();
endif;
