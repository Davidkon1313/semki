<?php
/**
 * Main WPSyncSheets_For_WooCommerce namespace.
 *
 * @package wpsyncsheets-for-woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }
if ( ! class_exists( 'WPSSW_Suppliers_Management_For_Woocommerce' ) ) :
	/**
	 * Class WPSSW_Suppliers_Management_For_Woocommerce.
	 */
	class WPSSW_Suppliers_Management_For_Woocommerce extends WPSSW_Order_Utils {
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
				self::prepare_headers();
				add_filter( 'wpsyncsheets_order_headers', __CLASS__ . '::get_header_list', 10, 1 );
			}
		}
		/**
		 * Check if Supplier Management for Woocommerce by P5Cure plugin is active or not.
		 */
		public static function wpssw_is_pugin_active() {
			if ( class_exists( 'Suppliers_Management_For_Woocommerce' ) ) {
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
				$headers['WPSSW_Suppliers_Management_For_Woocommerce'] = self::$wpssw_headers;
			}
			return $headers;
		}
		/**
		 * Prepare Header List.
		 */
		public static function prepare_headers() {
			self::$wpssw_headers = self::wpssw_supplier_headers();
		}
		/**
		 * Supplier Management for Woocommerce by P5Cure headers.
		 *
		 * @param bool $with_key check whether to return headers with key or without key.
		 */
		public static function wpssw_supplier_headers( $with_key = false ) {

			$keys = array( 'ft_smfw_supplier', 'smfw_supplier_sku', 'ft_smfw_supplier_price' );
			$val  = array( 'Supplier', 'Supplier SKU', 'Supplier Price' );
			if ( $with_key ) {
				return array_combine( $keys, $val );
			}
			return $val;
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
			$wpssw_value            = array();
			$wpssw_items            = $wpssw_order->get_items();
			$wpssw_supplier_headers = self::wpssw_supplier_headers( true );
			$meta_key               = array_search( $wpssw_headers_name, $wpssw_supplier_headers, true );

			foreach ( $wpssw_items as $item_id => $wpssw_item ) {
				$wpssw_metaval = '';
				$product_id    = $wpssw_item->get_product_id();
				if ( $product_id ) {
					$wpssw_metaval = get_post_meta( $product_id, $meta_key, true );
					if ( 'Supplier' === (string) $wpssw_headers_name && ! empty( $wpssw_metaval ) ) {
						$supplier = new SMFW_Supplier( $wpssw_metaval );
						if ( ! empty( $supplier ) && ! is_wp_error( $supplier ) ) {
							$wpssw_value[] = $supplier->get_name();
							continue;
						} else {
							$wpssw_value[] = '';
						}
					}
					$wpssw_value[] = $wpssw_metaval;
				} else {
					$wpssw_value[] = $wpssw_metaval;
				}
			}
			return $wpssw_value;
		}
	}
	new WPSSW_Suppliers_Management_For_Woocommerce();
endif;
