<?php
/**
 * Main WPSyncSheets_For_WooCommerce namespace.
 *
 * @since 1.0.0
 * @package wpsyncsheets-for-woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }
if ( ! class_exists( 'WPSSW_Custom_Productmeta_Headers' ) ) :
	/**
	 * Class WPSSW_Custom_Productmeta_Headers.
	 */
	class WPSSW_Custom_Productmeta_Headers extends WPSSW_Product_Utils {
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
			$headers          = array();
			$wpssw_post_metas = WPSSW_Setting::wpssw_get_all_post_metas( 'product' );
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
				$headers['WPSSW_Custom_Productmeta_Headers'] = self::$wpssw_headers;
			}
			return $headers;
		}
		/**
		 * Get Value for given header name.
		 *
		 * @param string $wpssw_headers_name Header name.
		 * @param object $wpssw_product product object.
		 * @param bool   $wpssw_child true if child product.
		 */
		public static function get_value( $wpssw_headers_name, $wpssw_product, $wpssw_child ) {
			return self::prepare_value( $wpssw_headers_name, $wpssw_product, $wpssw_child );
		}
		/**
		 * Prepare Value for given header name.
		 *
		 * @param string $wpssw_headers_name Header name.
		 * @param object $wpssw_product product object.
		 * @param bool   $wpssw_child true if child product.
		 */
		public static function prepare_value( $wpssw_headers_name, $wpssw_product, $wpssw_child ) {
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
			$wpssw_value = '';
			if ( in_array( $wpssw_headers_name, $wpssw_post_metas, true ) && true !== (bool) $wpssw_child ) {
				$metakey = array_search( $wpssw_headers_name, $wpssw_post_metas, true );

				$wpssw_value = get_post_meta( $wpssw_product->get_id(), $metakey, true );
			}
			return $wpssw_value;
		}
	}
	new WPSSW_Custom_Productmeta_Headers();
endif;
