<?php
/**
 * Main WPSyncSheets_For_WooCommerce namespace.
 *
 * @since 1.0.0
 * @package wpsyncsheets-for-woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }
if ( ! class_exists( 'WPSSW_Custom_Headers_For_Product' ) ) :
	/**
	 * Class WPSSW_Custom_Headers_For_Product.
	 */
	class WPSSW_Custom_Headers_For_Product extends WPSSW_Product_Utils {
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
			add_filter( 'wpsyncsheets_product_headers', __CLASS__ . '::get_header_list', 10, 1 );
		}
		/**
		 * Prepare Header List.
		 */
		public static function prepare_headers() {
			$wpssw_custom_headers = apply_filters( 'wpssw_custom_headers_for_product', array() ); // use this.
			self::$wpssw_headers  = $wpssw_custom_headers;
		}
		/**
		 * Get Header List.
		 *
		 * @param array $headers .
		 */
		public static function get_header_list( $headers = array() ) {

			if ( ! empty( self::$wpssw_headers ) ) {
				$headers['WPSSW_Custom_Headers_For_Product'] = self::$wpssw_headers;
			}
			return $headers;
		}
		/**
		 * Get Value for given header name.
		 *
		 * @param string $wpssw_headers_name Header name.
		 * @param object $wpssw_product product object.
		 */
		public static function get_value( $wpssw_headers_name, $wpssw_product ) {
			return self::prepare_value( $wpssw_headers_name, $wpssw_product );
		}
		/**
		 * Prepare Value for given header name.
		 *
		 * @param string $wpssw_headers_name Header name.
		 * @param object $wpssw_product product object.
		 */
		public static function prepare_value( $wpssw_headers_name, $wpssw_product ) {
			$wpssw_value             = '';
			$wpssw_extra_headers_val = apply_filters( 'wpssw_custom_values_for_product', '', $wpssw_product->get_id(), $wpssw_headers_name ); // use this.

			if ( is_object( $wpssw_extra_headers_val ) ) {
				$wpssw_value = '';
			} elseif ( is_array( $wpssw_extra_headers_val ) ) {
				$wpssw_value = implode( ',', $wpssw_extra_headers_val );
			} else {
				$wpssw_value = $wpssw_extra_headers_val;
			}
			return $wpssw_value;
		}
	}
	new WPSSW_Custom_Headers_For_Product();
endif;
