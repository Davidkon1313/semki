<?php
/**
 * Main WPSyncSheets_For_WooCommerce namespace.
 *
 * @since 1.0.0
 * @package wpsyncsheets-for-woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }
if ( ! class_exists( 'WPSSW_WooCommerce_Product_Brand' ) ) :
	/**
	 * Class WPSSW_WooCommerce_Product_Brand.
	 */
	class WPSSW_WooCommerce_Product_Brand extends WPSSW_Product_Utils {
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
				add_filter( 'wpsyncsheets_product_headers', __CLASS__ . '::get_header_list', 10, 1 );
			}
		}
		/**
		 * Check if WooCommerce Brands Plugin is active or not.
		 */
		public static function wpssw_is_pugin_active() {
			if ( ! class_exists( 'WC_Brands' ) ) {
				return false;
			}
			return true;
		}
		/**
		 * Prepare Header List.
		 */
		protected function prepare_headers() {
			self::$wpssw_headers = array( 'Brand name' );
		}
		/**
		 * Get Header List.
		 *
		 * @param array $headers .
		 */
		public static function get_header_list( $headers = array() ) {
			if ( ! empty( self::$wpssw_headers ) ) {
				$headers['WPSSW_WooCommerce_Product_Brand'] = self::$wpssw_headers;
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

			/*WooCommerce Brand*/
			$wpssw_value       = '';
			$wpssw_wapfmetaval = wp_get_post_terms( $wpssw_product->get_id(), 'product_brand', array( 'fields' => 'names' ) );
			if ( is_array( $wpssw_wapfmetaval ) ) {
				$wpssw_value = implode( ',', $wpssw_wapfmetaval );
			} else {
				$wpssw_value = $wpssw_wapfmetaval;
			}
			return $wpssw_value;
		}
	}
	new WPSSW_WooCommerce_Product_Brand();
endif;
