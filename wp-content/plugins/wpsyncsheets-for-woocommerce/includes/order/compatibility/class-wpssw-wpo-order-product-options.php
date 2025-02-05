<?php
/**
 * Main WPSyncSheets_For_WooCommerce namespace.
 *
 * @since 1.0.0
 * @package wpsyncsheets-for-woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }
if ( ! class_exists( 'WPSSW_WPO_Order_Product_Options' ) ) :
	/**
	 * Class WPSSW_WPO_Order_Product_Options.
	 */
	class WPSSW_WPO_Order_Product_Options extends WPSSW_Order_Utils {
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
		 * Check if WooCommerce Product Options by barn2 plugin is active or not.
		 */
		public static function wpssw_is_pugin_active() {
			$active_plugins = array();
			if ( is_multisite() ) {
				if ( ! function_exists( 'is_plugin_active_for_network' ) ) {
					require_once ABSPATH . '/wp-admin/includes/plugin.php';
				}
				// Check WooCommerce Product Option active at the network site.
				if ( is_plugin_active_for_network( 'woocommerce-product-options/woocommerce-product-options.php' ) ) {
					$active_plugins = (array) get_site_option( 'active_sitewide_plugins', array() );
				} else { // Check WooCommerce Product Option active at the network individual site.
					$active_plugins = (array) get_option( 'active_plugins', array() );
				}
			} else {
				$active_plugins = (array) get_option( 'active_plugins', array() );
			}
			if ( in_array( 'woocommerce-product-options/woocommerce-product-options.php', $active_plugins, true ) || array_key_exists( 'woocommerce-product-options/woocommerce-product-options.php', $active_plugins ) ) {
				return true;
			}
			return false;
		}
		/**
		 * Prepare Header List.
		 */
		public static function prepare_headers() {
			self::$wpssw_headers = self::wpssw_get_wpo_product_option_field();
		}
		/**
		 * Get Header List.
		 *
		 * @param array $headers .
		 */
		public static function get_header_list( $headers = array() ) {
			if ( ! empty( self::$wpssw_headers ) ) {
				$headers['WPSSW_WPO_Order_Product_Options'] = self::$wpssw_headers;
			}
			return $headers;
		}
		/**
		 * WooCommerce Product Options by barn2.
		 */
		public static function wpssw_get_wpo_product_option_field() {
			$wpssw_wpo_headers = array();
			global $wpdb; // Access the global $wpdb object.

			// Define the table name, considering the WordPress database prefix.
			$table_name = $wpdb->prefix . 'wpo_options'; // Adjust the table name if needed.

			// Fetch all rows from the table.
			$results = $wpdb->get_results("SELECT group_id, name, type FROM $table_name", ARRAY_A);

			$wpssw_wpo_headers = array();
			if ( ! empty( $results ) ) {
				foreach ( $results as $value ) {
					if(in_array($value['type'], array('product', 'wysiwyg', 'html'), true)){
						continue;
					}
					$wpssw_wpo_headers[ 'wpo_' . $value['group_id'] .'_'. $value['name'] ] = 'WPO: '.$value['name'];
				}
			}
			return $wpssw_wpo_headers;
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
			self::prepare_headers();
			$wpssw_wpo_product_option = self::$wpssw_headers;

			$wpssw_items              = $wpssw_order->get_items();
			$wpssw_headers_name = str_replace("WPO: ","",$wpssw_headers_name);
			foreach ( $wpssw_items as $item_id => $wpssw_item ) {
				$wpssw_metadata   = wc_get_order_item_meta($item_id, '_wpo_options', true); 
				
				if(empty($wpssw_metadata) || ! is_array($wpssw_metadata) ){
					continue;
				}
				$wpssw_wpometaval = array();
				foreach ( $wpssw_metadata as $wpssw_meta ) {
					$key = 'wpo_' . $wpssw_meta['group_id'] . '_' . $wpssw_headers_name;					
					if( $wpssw_meta['name'] === $wpssw_headers_name && false !== array_key_exists( $key, $wpssw_wpo_product_option ) ){
						if( 'file_upload' === $wpssw_meta['type'] ){
							$wpssw_wpometaval =  $wpssw_meta['value'];
						}else{
							$wpssw_wpometaval =  array_column($wpssw_meta['choice_data'], 'label');
						}
						break;
					}
				}				
				$wpssw_value[] = implode( ',', $wpssw_wpometaval );
			}
			return $wpssw_value;
		}
	}
	new WPSSW_WPO_Order_Product_Options();
endif;
