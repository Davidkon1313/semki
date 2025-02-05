<?php
/**
 * Main WPSyncSheets_For_WooCommerce namespace.
 *
 * @package wpsyncsheets-for-woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }

if ( ! class_exists( 'WPSSW_Expiry_Date' ) ) :

	/**
	 * Class WPSSW_Expiry_Date.
	 */
	class WPSSW_Expiry_Date extends WPSSW_Product_Utils {

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
			if ( class_exists( 'SOFT79_WCXD' ) ) {
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
				$headers['WPSSW_Expiry_Date'] = self::$wpssw_headers;
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
				'_j79_wcxd_expiries' => 'Expiry Date',
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
			$headers = array_merge( $headers, array( 'j79_wcxd_expiry_type' => 'Expiry Rule' ) );
			return $headers;
		}
		/**
		 * Prepare Header List.
		 */
		protected function prepare_headers() {
			$headers             = array(
				'_j79_wcxd_expiries'   => 'Expiry Date',
				'j79_wcxd_expiry_type' => 'Expiry Rule',
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
			$wpssw_value      = '';
			$wpssw_headerlist = self::get_header_list();
			if ( in_array( $wpssw_headers_name, $wpssw_headerlist['WPSSW_Expiry_Date'], true ) ) {

				$wpssw_field_name = array_search( $wpssw_headers_name, $wpssw_headerlist['WPSSW_Expiry_Date'], true );
				if ( ! $wpssw_product->is_type( 'variation' ) && 'j79_wcxd_expiry_type' === $wpssw_field_name ) {
					$expiry_rule_ids = get_the_terms( $wpssw_product->get_id(), $wpssw_field_name );
					if ( $expiry_rule_ids && ! is_wp_error( $expiry_rule_ids ) ) {
						$wpssw_value = reset( $expiry_rule_ids )->slug;
					}
				} else {
					$wpssw_val = get_post_meta( $wpssw_product->get_id(), $wpssw_field_name, true );
					if ( ! empty( $wpssw_val ) ) {
						$wpssw_value = $wpssw_val;
					} else {
						$wpssw_value = '';
					}
				}
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
			if ( 'Expiry Rule' === (string) $wpssw_header ) {
				if ( ! empty( $wpssw_data[ $wpssw_key ] ) ) {
					$wpssw_terms = array( $wpssw_data[ $wpssw_key ] );
				} else {
					$wpssw_terms = array();
				}
				wp_set_object_terms( $wpssw_productid, $wpssw_terms, 'j79_wcxd_expiry_type' );
			}
		}
	}
	new WPSSW_Expiry_Date();
endif;
