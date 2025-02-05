<?php
/**
 * Main WPSyncSheets_For_WooCommerce namespace.
 *
 * @since 1.0.0
 * @package wpsyncsheets-for-woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }
if ( ! class_exists( 'WPSSW_WPO_Product_Options' ) ) :
	/**
	 * Class WPSSW_WPO_Product_Options.
	 */
	class WPSSW_WPO_Product_Options extends WPSSW_Product_Utils {
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
				add_filter( 'wpsyncsheets_product_headers', __CLASS__ . '::get_header_list', 10, 1 );
				//add_filter( 'wpssw_custom_headers_for_product_import', __CLASS__ . '::wpssw_custom_headers_product_meta', 10, 1 );
				//add_action( 'wpssw_custom_headers_for_product_doimport', __CLASS__ . '::wpssw_custom_headers_for_product_doimport', 10, 3 );
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
		protected function prepare_headers() {
			self::$wpssw_headers = self::wpssw_get_wpo_product_option_field();
		}
		/**
		 * Get Header List.
		 *
		 * @param array $headers .
		 */
		public static function get_header_list( $headers = array() ) {
			if ( ! empty( self::$wpssw_headers ) ) {
				$headers['WPSSW_WPO_Product_Options'] = self::$wpssw_headers;
			}
			return $headers;
		}
		/**
		 * WooCommerce Product Options by barn2.
		 * 
		 * @param bool $with_choices decide whether to return headers with choices or without
		 */
		public static function wpssw_get_wpo_product_option_field($with_choices=false) {
			$wpssw_wpo_headers = array();
			global $wpdb; // Access the global $wpdb object.

			// Define the table name, considering the WordPress database prefix.
			$table_name = $wpdb->prefix . 'wpo_options'; // Adjust the table name if needed.

			// Fetch all rows from the table.
			$results = $wpdb->get_results("SELECT group_id, name, type, choices FROM $table_name", ARRAY_A);

			$wpssw_wpo_headers = array();
			$wpssw_wpo_headers_with_options = array();
			if ( ! empty( $results ) ) {
				foreach ( $results as $value ) {
					if(in_array($value['type'], array('product', 'wysiwyg', 'html'), true)){
						continue;
					}
					if($with_choices){
						$wpssw_wpo_headers_with_options[ 'wpo_' . $value['group_id'] .'_'. $value['name'] ]['name'] = 'WPO: '.$value['name'];
						$wpssw_wpo_headers_with_options[ 'wpo_' . $value['group_id'] .'_'. $value['name'] ]['choices'] = $value['choices'];
					}else{
						$wpssw_wpo_headers[ 'wpo_' . $value['group_id'] .'_'. $value['name'] ] = 'WPO: '.$value['name'];
					}
				}
			}
			if($with_choices){
				return $wpssw_wpo_headers_with_options;
			}
			return $wpssw_wpo_headers;
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
			
			$wpssw_wpo_headers_with_options = self::wpssw_get_wpo_product_option_field(true);
			foreach ($wpssw_wpo_headers_with_options as $key => $item) {
			    // Check if the name matches the header
			    if ($item['name'] === $wpssw_headers_name) {
			        // Decode the choices JSON string into an array
			        $choices = json_decode($item['choices'], true);
			        
			        // Create an array to store each choice in the specified format
			        $formatted_choices = array(); 

			        // Iterate over choices and format each one
			        foreach ($choices as $choice) {
			        	$price = $choice['pricing'] ?: 0;
			            $formatted_choices[] = "{$choice['label']}:{$price}:{$choice['price_type']}";
			        }

			        // Join all formatted choices into a single string separated by commas
			        $wpssw_value = implode(', ', $formatted_choices);
			        break; // Exit loop once match is found
			    }
			}

			return $wpssw_value;
		}
	}
	new WPSSW_WPO_Product_Options();
endif;
