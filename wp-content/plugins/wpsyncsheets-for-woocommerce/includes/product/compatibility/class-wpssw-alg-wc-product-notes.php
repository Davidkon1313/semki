<?php
/**
 * Main WPSyncSheets_For_WooCommerce namespace.
 *
 * @package wpsyncsheets-for-woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }

if ( ! class_exists( 'WPSSW_Alg_WC_Product_Notes' ) ) :

	/**
	 * Class WPSSW_Alg_WC_Product_Notes.
	 */
	class WPSSW_Alg_WC_Product_Notes extends WPSSW_Product_Utils {

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
		 * Check if Product Notes for WooCommerce by Algoritmika Ltd plugin is active or not.
		 */
		public static function wpssw_is_pugin_active() {
			if ( class_exists( 'Alg_WC_Product_Notes' ) ) {
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
				$headers['WPSSW_Alg_WC_Product_Notes'] = self::$wpssw_headers;
			}
			return $headers;
		}

		/**
		 * Prepare Header List.
		 */
		protected function prepare_headers() {
			$headers             = array(
				'_alg_wc_internal_product_note' => 'Product Private Notes',
				'_alg_wc_public_product_note'   => 'Product Public Notes',
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
			if ( in_array( $wpssw_headers_name, $wpssw_headerlist['WPSSW_Alg_WC_Product_Notes'], true ) ) {
				$wpssw_field_name = array_search( $wpssw_headers_name, $wpssw_headerlist['WPSSW_Alg_WC_Product_Notes'], true );
				$wpssw_val        = get_post_meta( $wpssw_product->get_id(), $wpssw_field_name, true );
				if ( is_array( $wpssw_val ) && ! empty( $wpssw_val ) ) {
					$wpssw_value = implode( ' , ', array_column( $wpssw_val, 'value' ) );
				} else {
					$wpssw_value = '';
				}
			}
			return $wpssw_value;
		}
	}
	new WPSSW_Alg_WC_Product_Notes();
endif;
