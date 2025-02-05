<?php
/**
 * Main WPSyncSheets_For_WooCommerce namespace.
 *
 * @since 1.0.0
 * @package wpsyncsheets-for-woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }

if ( ! class_exists( 'WPSSW_WPML' ) ) :

	/**
	 * Class WPSSW_WPML.
	 */
	class WPSSW_WPML extends WPSSW_Product_Utils {

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
		 * Check if WPML Multilingual CMS by OnTheGoSystems plugin is active or not
		 */
		public static function wpssw_is_pugin_active() {
			if ( class_exists( 'SitePress' ) ) {
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
				$headers['WPSSW_WPML'] = self::$wpssw_headers;
			}
			return $headers;
		}

		/**
		 * Prepare Header List.
		 */
		protected function prepare_headers() {
			$headers             = array(
				'wpml_lang'           => 'WPML Language Code',
				'wpml_translation_id' => 'WPML Translation ID',
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
			$args        = array(
				'element_id'   => $wpssw_product->get_id(),
				'element_type' => 'product',
			);
			$lang        = apply_filters( 'wpml_element_language_details', null, $args );

			if ( 'WPML Language Code' === (string) $wpssw_headers_name ) {
				if ( null !== $lang && ! empty( $lang ) ) {
					$wpssw_value = $lang->language_code ? $lang->language_code : '';
				}
			}

			if ( 'WPML Translation ID' === (string) $wpssw_headers_name ) {
				if ( function_exists( 'wpml_get_content_trid' ) ) {
					$wpssw_value = wpml_get_content_trid( 'post_product', $wpssw_product->get_id() );
				} else {
					if ( null !== $lang && ! empty( $lang ) ) {
						$wpssw_value = $lang->trid ? $lang->trid : '';
					}
				}
			}

			return $wpssw_value;
		}
	}
	new WPSSW_WPML();
endif;
