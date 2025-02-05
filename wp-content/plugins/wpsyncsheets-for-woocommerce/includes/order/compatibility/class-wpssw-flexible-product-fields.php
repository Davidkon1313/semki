<?php
/**
 * Main WPSyncSheets_For_WooCommerce namespace.
 *
 * @since 1.0.0
 * @package wpsyncsheets-for-woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }
if ( ! class_exists( 'WPSSW_Flexible_Product_Fields' ) ) :
	/**
	 * Class WPSSW_Flexible_Product_Fields.
	 */
	class WPSSW_Flexible_Product_Fields extends WPSSW_Order_Utils {
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
		 * Check if Flexible Product Fields plugin is active or not.
		 */
		public static function wpssw_is_pugin_active() {
			if ( class_exists( 'Flexible_Product_Fields_Plugin' ) || class_exists( 'Flexible_Product_Fields_PRO_Plugin' ) ) {
				return true;
			}
			return false;
		}
		/**
		 * Prepare Header List.
		 */
		protected function prepare_headers() {
			self::$wpssw_headers = $this->wpssw_flexible_product_fields();
		}
		/**
		 * Get Header List.
		 *
		 * @param array $headers .
		 */
		public static function get_header_list( $headers = array() ) {
			if ( ! empty( self::$wpssw_headers ) ) {
				$headers['WPSSW_Flexible_Product_Fields'] = self::$wpssw_headers;
			}
			return $headers;
		}
		/**
		 *
		 * Flexible Product Fields headers.
		 */
		public static function wpssw_flexible_product_fields() {
			global $wpdb;
			// @codingStandardsIgnoreStart.
			$wpssw_querystr  = "SELECT {$wpdb->prefix}postmeta.* FROM {$wpdb->prefix}postmeta INNER JOIN {$wpdb->prefix}posts ON ( {$wpdb->prefix}posts.ID = {$wpdb->prefix}postmeta.post_id ) WHERE {$wpdb->prefix}postmeta.meta_key = '_fields' AND {$wpdb->prefix}posts.post_type='fpf_fields' AND {$wpdb->prefix}posts.post_status='publish'";// db call ok.
        	$wpssw_postsmeta = $wpdb->get_results( $wpssw_querystr);
			// @codingStandardsIgnoreEnd.
			$wpssw_flexible_fields = array();
			foreach ( $wpssw_postsmeta as $wpssw_meta ) {
				// phpcs:ignore
				$fields = unserialize( $wpssw_meta->meta_value );
				if ( is_array( $fields ) ) {
					foreach ( $fields as $field ) {
						if ( in_array( $field['type'], array( 'html', 'image', 'heading' ), true ) ) {
							continue;
						}
						$wpssw_flexible_fields[] = $field['title'];
					}
				}
			}
			$wpssw_flexible_fields = array_values( array_unique( $wpssw_flexible_fields ) );
			return $wpssw_flexible_fields;
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
			// Flexible Product Fields for WooCommerce.
			$wpssw_value = array();
			$wpssw_items = $wpssw_order->get_items();
			foreach ( $wpssw_items as $wpssw_item ) {
				$wpssw_metadata = $wpssw_item->get_formatted_meta_data();
				$wpssw_metaval  = '';
				foreach ( $wpssw_metadata as $wpssw_meta ) {
					if ( html_entity_decode( $wpssw_meta->key, ENT_QUOTES ) === $wpssw_headers_name ) {
						$wpssw_metaval .= html_entity_decode( $wpssw_meta->value, ENT_QUOTES ) . ',';
					}
				}
				$wpssw_metaval = rtrim( $wpssw_metaval, ',' );

				if ( is_array( $wpssw_metaval ) ) {
					$wpssw_val = implode( ',', $wpssw_metaval );
				} else {
					$wpssw_val = $wpssw_metaval;
				}
				if ( false !== strpos( $wpssw_val, '<a ' ) ) {
					preg_match_all( '/<a[^>]+href=([\'"])(?<href>.+?)\1[^>]*>/i', $wpssw_val, $result );
					if ( isset( $result['href'] ) && ! empty( $result['href'] ) ) {
						$wpssw_val = implode( ',', $result['href'] );
					} else {
						$wpssw_val = '';
					}
				}
				$wpssw_value[] = $wpssw_val;
			}
			return $wpssw_value;
		}
	}
	new WPSSW_Flexible_Product_Fields();
endif;
