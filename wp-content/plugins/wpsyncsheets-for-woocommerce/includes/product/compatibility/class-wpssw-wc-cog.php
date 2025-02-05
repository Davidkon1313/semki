<?php
/**
 * Main WPSyncSheets_For_WooCommerce namespace.
 *
 * @since 1.0.0
 * @package wpsyncsheets-for-woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }

if ( ! class_exists( 'WPSSW_WC_COG' ) ) :

	/**
	 * Class WPSSW_WC_COG.
	 */
	class WPSSW_WC_COG extends WPSSW_Product_Utils {

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
				add_filter( 'wpssw_custom_headers_for_variation_product_import', __CLASS__ . '::get_cog_variation_product_import_header_list', 10, 1 );
			}
		}

		/**
		 * Check if WooCommerce Cost of Goods by SkyVerge plugin is active or not.
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
				$headers['WPSSW_WC_COG'] = self::$wpssw_headers;
			}
			return $headers;
		}

		/**
		 * Prepare Header List.
		 */
		protected function prepare_headers() {
			$headers             = array(
				'_wc_cog_cost'          => 'Cost of Good (COG)',
				'_wc_cog_cost_variable' => 'Variations Default COG',
				'_wc_cog_default_cost'  => 'Use default COG',
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
			if ( in_array( $wpssw_headers_name, $wpssw_headerlist['WPSSW_WC_COG'], true ) ) {
				$wpssw_field_name = array_search( $wpssw_headers_name, $wpssw_headerlist['WPSSW_WC_COG'], true );
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
		public static function get_cog_variation_product_import_header_list( $headers = array() ) {
			$headers = array(
				'_wc_cog_cost'         => 'Cost of Good (COG)',
				'_wc_cog_default_cost' => 'Use default COG',
			);
			return $headers;
		}
	}
	new WPSSW_WC_COG();
endif;
