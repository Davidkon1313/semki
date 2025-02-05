<?php
/**
 * Main WPSyncSheets_For_WooCommerce namespace.
 *
 * @since 1.0.0
 * @package wpsyncsheets-for-woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }
if ( ! class_exists( 'WPSSW_WooCommerce_Advance_Product_Field' ) ) :
	/**
	 * Class WPSSW_WooCommerce_Advance_Product_Field.
	 */
	class WPSSW_WooCommerce_Advance_Product_Field extends WPSSW_Order_Utils {
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
		 * Check if WooCommerce Advance Product Fields Pro plugin is active or not.
		 */
		public static function wpssw_is_pugin_active() {
			if ( ! class_exists( 'SW_WAPF_PRO\WAPF' ) ) {
				return false;
			}
			return true;
		}
		/**
		 * Prepare Header List.
		 */
		protected function prepare_headers() {
			self::$wpssw_headers = $this->wpssw_advanced_product_fields_pro();
		}
		/**
		 * Get Header List.
		 *
		 * @param array $headers .
		 */
		public static function get_header_list( $headers = array() ) {
			if ( ! empty( self::$wpssw_headers ) ) {
				$headers['WPSSW_WooCommerce_Advance_Product_Field'] = self::$wpssw_headers;
			}
			return $headers;
		}
		/**
		 *
		 * Advanced Product Fields Pro headers.
		 */
		public static function wpssw_advanced_product_fields_pro() {
			$wpssw_advanced_field = array();
			global $wpdb;
			$table_name  = $wpdb->prefix;
			$meta_key    = '_wapf_fieldgroup';
			$post_status = 'publish';
			// @codingStandardsIgnoreStart.
			$wpssw_querystr  = "SELECT {$wpdb->prefix}postmeta.* FROM {$wpdb->prefix}postmeta INNER JOIN {$wpdb->prefix}posts ON ( {$wpdb->prefix}posts.ID = {$wpdb->prefix}postmeta.post_id ) WHERE 1=1 AND ( {$wpdb->prefix}postmeta.meta_key = '_wapf_fieldgroup' ) AND {$wpdb->prefix}posts.post_status='publish'";// db call ok.
			$wpssw_postsmeta = $wpdb->get_results( $wpssw_querystr, ARRAY_A );
			// @codingStandardsIgnoreEnd.
			$wpssw_advanced_field = array();
			foreach ( $wpssw_postsmeta as $wpssw_meta ) {
				// @codingStandardsIgnoreStart.
				if ( is_string( unserialize( $wpssw_meta['meta_value'] ) ) ) {
					$fields = unserialize( unserialize( $wpssw_meta['meta_value'] ) );
				} else {
					$fields = unserialize( $wpssw_meta['meta_value'] );
				}
				// @codingStandardsIgnoreEnd.
				if ( is_array( $fields ) ) {
					$wpssw_advanced_field = array_merge( $wpssw_advanced_field, array_column( $fields['fields'], 'label' ) );
				}
			}
			$wpssw_advanced_field = array_values( array_unique( $wpssw_advanced_field ) );
			return $wpssw_advanced_field;
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
			// Advanced Product Fields Pro for WooCommerce.
			$wpssw_value = array();
			$wpssw_items = $wpssw_order->get_items();
			foreach ( $wpssw_items as $wpssw_item ) {
				$wpssw_metadata = $wpssw_item->get_formatted_meta_data();
				$wpssw_metaval  = '';
				foreach ( $wpssw_metadata as $wpssw_meta ) {
					if ( html_entity_decode( $wpssw_meta->key, ENT_QUOTES ) === $wpssw_headers_name ) {
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
	new WPSSW_WooCommerce_Advance_Product_Field();
endif;
