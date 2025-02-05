<?php
/**
 * Main WPSyncSheets_For_WooCommerce namespace.
 *
 * @since 1.0.0
 * @package wpsyncsheets-for-woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }
if ( ! class_exists( 'WPSSW_WC_Product_Addons' ) ) :
	/**
	 * Class WPSSW_WC_Product_Addons.
	 */
	class WPSSW_WC_Product_Addons extends WPSSW_Order_Utils {
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
		 * Check if WooCommerce Product Add-Ons plugin is active or not.
		 */
		public static function wpssw_is_pugin_active() {
			if ( class_exists( 'WC_Product_Addons' ) ) {
				return true;
			}
			return false;
		}
		/**
		 * Get Header List.
		 *
		 * @param array $headers .
		 */
		public static function get_header_list( $headers = array() ) {
			if ( ! empty( self::$wpssw_headers ) ) {
				$headers['WPSSW_WC_Product_Addons'] = self::$wpssw_headers;
			}
			return $headers;
		}
		/**
		 * Prepare Header List.
		 */
		protected function prepare_headers() {
			$headers = array();
			global $wpdb;
			$table_name = $wpdb->prefix;
			$meta_key   = '_product_addons';
			// @codingStandardsIgnoreStart.
			$wpssw_querystr  = "SELECT {$wpdb->prefix}posts.* FROM {$wpdb->prefix}posts INNER JOIN {$wpdb->prefix}postmeta ON ( {$wpdb->prefix}posts.ID = {$wpdb->prefix}postmeta.post_id ) WHERE 1=1 AND ( {$wpdb->prefix}postmeta.meta_key = '_product_addons' )"; //db call ok.
			$wpssw_postsmeta = $wpdb->get_results( $wpssw_querystr, ARRAY_A );
			// @codingStandardsIgnoreEnd.
			foreach ( $wpssw_postsmeta as $wpssw_cfield ) {
				$wpssw_value = get_post_meta( $wpssw_cfield['ID'], '_product_addons', true );
				if ( ! empty( $wpssw_value ) ) {
					foreach ( $wpssw_value as $wpssw_val ) {
						if ( 'heading' === (string) $wpssw_val['type'] ) {
							continue;
						}
						$headers[] = $wpssw_val['name'];
					}
				}
			}
			self::$wpssw_headers = $headers;
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
			foreach ( $wpssw_items as $item_id => $wpssw_item ) {
				$wpssw_metadata = $wpssw_item->get_formatted_meta_data();
				$wpssw_metaval  = '';

				$wpssw_headers_field = wc_get_order_item_meta( $item_id, 'wpssw_headers', true );

				if ( ! empty( $wpssw_headers_field ) ) {
					if ( in_array( $wpssw_headers_name, $wpssw_headers_field, true ) ) {
						$wpssw_headers_name = array_search( $wpssw_headers_name, $wpssw_headers_field, true );
					}
				}

				foreach ( $wpssw_metadata as $wpssw_meta ) {
					if ( strtolower( $wpssw_meta->key ) === strtolower( $wpssw_headers_name ) ) {
						$wpssw_metaval .= $wpssw_meta->value . ',';
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
	new WPSSW_WC_Product_Addons();
endif;
