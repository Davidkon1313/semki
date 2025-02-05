<?php
/**
 * Main WPSyncSheets_For_WooCommerce namespace.
 *
 * @since 1.0.0
 * @package wpsyncsheets-for-woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }
if ( ! class_exists( 'WPSSW_WC_Superlabelstore' ) ) :
	/**
	 * Class WPSSW_WC_Superlabelstore.
	 */
	class WPSSW_WC_Superlabelstore extends WPSSW_Order_Utils {
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
				add_filter( 'wpsyncsheets_order_headers', __CLASS__ . '::get_header_list', 10, 1 );
			}
		}
		/**
		 * Check if WooCommerce Product Add-Ons Ultimate plugin is active or not.
		 */
		public static function wpssw_is_pugin_active() {
			if ( ! class_exists( 'WC_Superlabelstore' ) ) {
				return false;
			}
			return true;
		}
		/**
		 * Prepare Header List.
		 */
		protected function prepare_headers() {
			self::$wpssw_headers = array( 'backgroundFill', 'fold', 'applicationMethod', 'fabric', 'dimensions', 'Content', 'State', 'Result', 'photoProof', 'diaPositive', 'size', 'symbols', 'rope', 'backside', 'contentFill' );
		}
		/**
		 * Get Header List.
		 *
		 * @param array $headers .
		 */
		public static function get_header_list( $headers = array() ) {
			if ( ! empty( self::$wpssw_headers ) ) {
				$headers['WPSSW_WC_Superlabelstore'] = self::$wpssw_headers;
			}
			return $headers;
		}
		/**
		 * Get Value for given header name.
		 *
		 * @param string $wpssw_headers_name Header name.
		 * @param object $wpssw_order order object.
		 * @param string $wpssw_operation operation to perfom on sheet.
		 * @param array  $wpssw_custom_value .
		 * @param array  $wpssw_product_headers .
		 */
		public static function get_value( $wpssw_headers_name, $wpssw_order, $wpssw_operation = 'insert', $wpssw_custom_value = array(), $wpssw_product_headers = array() ) {
			return self::prepare_value( $wpssw_headers_name, $wpssw_order, $wpssw_operation, $wpssw_custom_value, $wpssw_product_headers );
		}
		/**
		 * Prepare Value for given header name.
		 *
		 * @param string $wpssw_headers_name Header name.
		 * @param object $wpssw_order order object.
		 * @param string $wpssw_operation operation to perfom on sheet.
		 * @param array  $wpssw_custom_value .
		 * @param array  $wpssw_product_headers .
		 */
		public static function prepare_value( $wpssw_headers_name, $wpssw_order, $wpssw_operation, $wpssw_custom_value = array(), $wpssw_product_headers = array() ) {
			// WooCommerce Product Add-Ons Ultimate.
			$wpssw_value = array();
			$wpssw_items = $wpssw_order->get_items();
			foreach ( $wpssw_items as $wpssw_item ) {
				$wpssw_metadata = $wpssw_item->get_formatted_meta_data();
				$wpssw_metaval  = '';
				foreach ( $wpssw_metadata as $wpssw_meta ) {
					if ( strtolower( $wpssw_meta->key ) === strtolower( $wpssw_headers_name ) ) {
						$wpssw_metaval .= ! empty( $wpssw_meta->value ) ? $wpssw_meta->value : wp_strip_all_tags( $wpssw_meta->display_value ) . ',';
					}
				}
				$wpssw_metaval = rtrim( $wpssw_metaval, ',' );
				if ( is_array( $wpssw_metaval ) ) {
					$wpssw_value[] = implode( ',', $wpssw_metaval );
				} else {
					$wpssw_value[] = $wpssw_metaval;
				}
			}
			return $wpssw_value;
		}
	}
	new WPSSW_WC_Superlabelstore();
endif;
