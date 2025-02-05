<?php
/**
 * Main WPSyncSheets_For_WooCommerce namespace.
 *
 * @package wpsyncsheets-for-woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }

if ( ! class_exists( 'WPSSW_Cost_Goods' ) ) :

	/**
	 * Class WPSSW_Cost_Good.
	 */
	class WPSSW_Cost_Goods extends WPSSW_Product_Utils {

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
				add_filter( 'wpssw_custom_headers_for_variation_product_import', __CLASS__ . '::wpssw_custom_headers_variation_product', 10, 1 );
				add_filter( 'wpssw_custom_headers_for_product_import', __CLASS__ . '::wpssw_custom_headers_product_meta', 10, 1 );
				add_action( 'wpssw_custom_headers_for_product_doimport', __CLASS__ . '::wpssw_custom_headers_for_product_doimport', 10, 3 );
			}
		}
		/**
		 * Check if Product Notes for WooCommerce by Algoritmika Ltd plugin is active or not.
		 */
		public static function wpssw_is_pugin_active() {
			if ( class_exists( 'WC_COG' ) ) {
				return true;
			}
			return false;
		}

		/**
		 * Get Header List.
		 *
		 * @param array $headers .
		 * @return array $headers
		 */
		public static function get_header_list( $headers = array() ) {
			if ( ! empty( self::$wpssw_headers ) ) {
				$headers['WPSSW_Cost_Goods'] = self::$wpssw_headers;
			}
			return $headers;
		}
		/**
		 * Get Variable Product Header List.
		 *
		 * @return array $headers
		 */
		public static function wpssw_custom_headers_variation_product() {
			$headers = array(
				'_wc_cog_cost' => 'Cost of Good',
			);
			return $headers;
		}
		/**
		 * Get Variable Product Header List.
		 *
		 * @param array $headers .
		 * @return array $headers
		 */
		public static function wpssw_custom_headers_product_meta( $headers = array() ) {
			$headers = array_merge( $headers, array( '_wc_cog_cost' => 'Cost of Good' ) );
			return $headers;
		}
		/**
		 * Prepare Header List.
		 */
		protected function prepare_headers() {
			$headers             = array(
				'_wc_cog_cost' => 'Cost of Good',
			);
			self::$wpssw_headers = $headers;
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
			$wpssw_value = '';
			if ( 'Cost of Good' === (string) $wpssw_headers_name ) {
				$wpssw_value = get_post_meta( $wpssw_product->get_id(), '_wc_cog_cost', true );
			}
			return $wpssw_value;
		}


		/**
		 * Import custom header for product.
		 *
		 * @param int    $wpssw_productid product id.
		 * @param string $wpssw_header header currently being processed.
		 * @param array  $wpssw_data data currently being processed for import.
		 */
		public static function wpssw_custom_headers_for_product_doimport( $wpssw_productid, $wpssw_header, $wpssw_data ) {
			$wpssw_woo_selections = stripslashes_deep( WPSSW_Setting::wpssw_option( 'wpssw_woo_product_headers' ) );
			array_unshift( $wpssw_woo_selections, 'Product Id', 'Product Variation Id' );
			$wpssw_key = array_search( $wpssw_header, $wpssw_woo_selections, true );
			if ( ! $wpssw_woo_selections ) {
				return;
			}
			if ( 'Cost of Good' === (string) $wpssw_header ) {
				if ( ! empty( $wpssw_data[ $wpssw_key ] ) ) {
					$wpssw_val = $wpssw_data[ $wpssw_key ];
				} else {
					$wpssw_val = '';
				}
				update_post_meta( $wpssw_productid, '_wc_cog_cost', $wpssw_val );
			}
		}
	}
	new WPSSW_Cost_Goods();
endif;
