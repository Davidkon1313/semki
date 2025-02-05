<?php
/**
 * Main WPSyncSheets_For_WooCommerce namespace.
 *
 * @since 1.0.0
 * @package wpsyncsheets-for-woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }

if ( ! class_exists( 'WPSSW_Rank_Math' ) ) :

	/**
	 * Class WPSSW_Rank_Math.
	 */
	class WPSSW_Rank_Math extends WPSSW_Product_Utils {

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
		 * Check if WooCommerce Custom Product Addons (Free) plugin is active or not.
		 */
		public static function wpssw_is_pugin_active() {
			if ( class_exists( 'RankMath' ) ) {
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
				$headers['WPSSW_Rank_Math'] = self::$wpssw_headers;
			}
			return $headers;
		}

		/**
		 * Prepare Header List.
		 */
		protected function prepare_headers() {
			$headers             = array(
				'rank_math_title'         => 'SEO Title',
				'rank_math_description'   => 'SEO Description',
				'rank_math_focus_keyword' => 'SEO Focus Keyword',
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
			if ( 'SEO Title' === (string) $wpssw_headers_name ) {
				$wpssw_value = $wpssw_product->get_meta( 'rank_math_title' );
				return $wpssw_value;
			}
			if ( 'SEO Description' === (string) $wpssw_headers_name ) {
				$wpssw_value = $wpssw_product->get_meta( 'rank_math_description' );
				return $wpssw_value;
			}
			if ( 'SEO Focus Keyword' === (string) $wpssw_headers_name ) {
				$wpssw_value = $wpssw_product->get_meta( 'rank_math_focus_keyword' );
				return $wpssw_value;
			}
			return $wpssw_value;
		}
	}
	new WPSSW_Rank_Math();
endif;
