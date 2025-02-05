<?php
/**
 * Main WPSyncSheets_For_WooCommerce namespace.
 *
 * @since 1.0.0
 * @package wpsyncsheets-for-woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }

if ( ! class_exists( 'WPSSW_ALG_WC_COG' ) ) :

	/**
	 * Class WPSSW_ALG_WC_COG.
	 */
	class WPSSW_ALG_WC_COG extends WPSSW_Product_Utils {

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
				add_filter( 'wpssw_headers_for_variation_product_import', __CLASS__ . '::get_alg_cog_variation_product_import_header_list', 10, 1 );
			}
		}

		/**
		 * Check if Cost of Goods for WooCommerce by WPFactory plugin is active or not.
		 */
		public static function wpssw_is_pugin_active() {
			if ( class_exists( 'Alg_WC_Cost_of_Goods' ) ) {
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
				$headers['WPSSW_ALG_WC_COG'] = self::$wpssw_headers;
			}
			return $headers;
		}

		/**
		 * Prepare Header List.
		 */
		protected function prepare_headers() {
			$headers             = array(
				'_alg_wc_cog_cost' => 'WP Cost of Good (COG)',
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
			if ( in_array( $wpssw_headers_name, $wpssw_headerlist['WPSSW_ALG_WC_COG'], true ) ) {
				$wpssw_field_name = array_search( $wpssw_headers_name, $wpssw_headerlist['WPSSW_ALG_WC_COG'], true );
				$wpssw_value      = get_post_meta( $wpssw_product->get_id(), $wpssw_field_name, true );
			}
			return $wpssw_value;
		}
		/**
		 * Get Header List for variation product import.
		 *
		 * @param array $headers .
		 * @return array $headers
		 */
		public static function get_alg_cog_variation_product_import_header_list( $headers = array() ) {
			$headers = array(
				'_alg_wc_cog_cost' => 'WP Cost of Good (COG)',
			);
			return $headers;
		}
	}
	new WPSSW_ALG_WC_COG();
endif;
