<?php
/**
 * Main WPSyncSheets_For_WooCommerce namespace.
 *
 * @since 1.0.0
 * @package wpsyncsheets-for-woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }
if ( ! class_exists( 'WPSSW_Custom_Ordermeta_Headers' ) ) :
	/**
	 * Class WPSSW_Custom_Ordermeta_Headers.
	 */
	class WPSSW_Custom_Ordermeta_Headers extends WPSSW_Order_Utils {
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
			$headers          = array();
			$wpssw_post_metas = WPSSW_Setting::wpssw_get_all_post_metas( 'shop_order' );
			if ( ! empty( $wpssw_post_metas ) ) {
				$headers = array_filter( array_unique( $wpssw_post_metas ) );
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
				$headers['WPSSW_Custom_Ordermeta_Headers'] = self::$wpssw_headers;
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
			$wpssw_sheet_headers = self::$wpssw_headers;
			if ( ! is_array( $wpssw_sheet_headers ) ) {
				$wpssw_sheet_headers = array();
			}
			$wpssw_post_metas = array();
			foreach ( $wpssw_sheet_headers as $key => $value ) {
				if ( false !== strpos( $key, 'wpsyncsheets_' ) && 1 === strpos( $key, 'psyncsheets_' ) ) {
					$wpssw_post_metas[ $key ] = $value;
				}
			}
			if ( ! is_array( $wpssw_post_metas ) ) {
				$wpssw_post_metas = array();
			}
			$wpssw_value        = array();
			$hpos_order_enabled = WPSSW_Setting::wpssw_check_hpos_order_setting_enabled();
			if ( in_array( $wpssw_headers_name, $wpssw_post_metas, true ) ) {
				$metakey = array_search( $wpssw_headers_name, $wpssw_post_metas, true );
				if ( $hpos_order_enabled ) {
					$wpssw_value[] = $wpssw_order->get_meta( $metakey, true );
				} else {
					$wpssw_value[] = get_post_meta( $wpssw_order->get_id(), $metakey, true );
				}
			}
			return $wpssw_value;
		}
	}
	new WPSSW_Custom_Ordermeta_Headers();
endif;
