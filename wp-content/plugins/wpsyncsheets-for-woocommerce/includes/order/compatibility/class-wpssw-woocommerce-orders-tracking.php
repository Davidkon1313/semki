<?php
/**
 * Main WPSyncSheets_For_WooCommerce namespace.
 *
 * @since 1.0.0
 * @package wpsyncsheets-for-woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }
if ( ! class_exists( 'WPSSW_WOOCOMMERCE_ORDERS_TRACKING' ) ) :
	/**
	 * Class WPSSW_WOOCOMMERCE_ORDERS_TRACKING.
	 */
	class WPSSW_WOOCOMMERCE_ORDERS_TRACKING extends WPSSW_Order_Utils {
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
		 * Check if WooCommerce Orders Tracking Premium Plugin is active or not.
		 */
		public static function wpssw_is_pugin_active() {
			if ( ! class_exists( 'WOOCOMMERCE_ORDERS_TRACKING' ) ) {
				return false;
			}
			return true;
		}
		/**
		 * Prepare Header List.
		 */
		protected function prepare_headers() {
			self::$wpssw_headers = array(
				'Tracking Number',
				'Carrier Slug',
				'Carrier Url',
				'Carrier Name',
				'Carrier Type',
			);
		}
		/**
		 * Get Header List.
		 *
		 * @param array $headers .
		 */
		public static function get_header_list( $headers = array() ) {
			if ( ! empty( self::$wpssw_headers ) ) {
				$headers['WPSSW_WOOCOMMERCE_ORDERS_TRACKING'] = self::$wpssw_headers;
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
			$wpssw_value = array();
			$wpssw_items = $wpssw_order->get_items();

			if ( $wpssw_order ) {
				$wpssw_items = $wpssw_order->get_items();
				foreach ( $wpssw_items as $wpssw_itemid => $wpssw_item ) {
					$wpssw_insert_val   = '';
					$wpssw_metadata     = $wpssw_item->get_meta( '_vi_wot_order_item_tracking_data' );
					$wpssw_metadata_val = $wpssw_metadata ? json_decode( $wpssw_metadata, true ) : array();
					$wpssw_metadata_val = isset( $wpssw_metadata_val[0] ) ? $wpssw_metadata_val[0] : array();
					if ( 'Tracking Number' === (string) $wpssw_headers_name ) {
						$wpssw_insert_val = isset( $wpssw_metadata_val['tracking_number'] ) ? $wpssw_metadata_val['tracking_number'] : '';
					} elseif ( 'Carrier Slug' === (string) $wpssw_headers_name ) {
						$wpssw_insert_val = isset( $wpssw_metadata_val['carrier_slug'] ) ? $wpssw_metadata_val['carrier_slug'] : '';
					} elseif ( 'Carrier Url' === (string) $wpssw_headers_name ) {
						$wpssw_insert_val = isset( $wpssw_metadata_val['carrier_url'] ) ? $wpssw_metadata_val['carrier_url'] : '';
					} elseif ( 'Carrier Name' === (string) $wpssw_headers_name ) {
						$wpssw_insert_val = isset( $wpssw_metadata_val['carrier_name'] ) ? $wpssw_metadata_val['carrier_name'] : '';
					} elseif ( 'Carrier Type' === (string) $wpssw_headers_name ) {
						$wpssw_insert_val = isset( $wpssw_metadata_val['carrier_type'] ) ? $wpssw_metadata_val['carrier_type'] : '';
					}
					$wpssw_value[] = $wpssw_insert_val ? $wpssw_insert_val : '';
				}
			}
			return $wpssw_value;
		}
	}
	new WPSSW_WOOCOMMERCE_ORDERS_TRACKING();
endif;

