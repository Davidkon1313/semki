<?php
/**
 * Main WPSyncSheets_For_WooCommerce namespace.
 *
 * @since 1.0.0
 * @package wpsyncsheets-for-woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }

if ( ! class_exists( 'WPSSW_EAN_For_WooCommerce' ) ) :

	/**
	 * Class WPSSW_EAN_For_WooCommerce.
	 */
	class WPSSW_EAN_For_WooCommerce extends WPSSW_Product_Utils {

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
				add_filter( 'wpssw_headers_for_variation_product_import', __CLASS__ . '::get_variation_product_import_header_list', 10, 1 );
			}
		}

		/**
		 * Check if EAN for WooCommerce plugin is active or not.
		 */
		public static function wpssw_is_pugin_active() {
			if ( class_exists( 'Alg_WC_EAN' ) ) {
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
				$headers['WPSSW_EAN_For_WooCommerce'] = self::$wpssw_headers;
			}
			return $headers;
		}

		/**
		 * Prepare Header List.
		 */
		protected function prepare_headers() {
			$headers             = array();
			$headers['_alg_ean'] = 'EAN';
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
			if ( ! empty( $wpssw_product->get_meta( '_alg_ean' ) ) ) {
				$wpssw_value = $wpssw_product->get_meta( '_alg_ean' );
			}
			return $wpssw_value;
		}
		/**
		 * Get Header List for variation product import.
		 *
		 * @param array $headers .
		 * @return array $headers
		 */
		public static function get_variation_product_import_header_list( $headers = array() ) {
			$headers['_alg_ean'] = 'EAN';
			return $headers;
		}
	}
	new WPSSW_EAN_For_WooCommerce();
endif;
