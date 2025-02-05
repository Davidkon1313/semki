<?php
/**
 * Main WPSyncSheets_For_WooCommerce namespace.
 *
 * @since 1.0.0
 * @package wpsyncsheets-for-woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }
if ( ! class_exists( 'WPSSW_Product_Attribute' ) ) :
	/**
	 * Class WPSSW_Product_Attribute.
	 */
	class WPSSW_Product_Attribute extends WPSSW_Order_Utils {
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
			$this->prepare_headers();
			add_filter( 'wpsyncsheets_order_headers', __CLASS__ . '::get_header_list', 10, 1 );
		}
		/**
		 * Prepare Header List.
		 */
		protected function prepare_headers() {
			global $wpdb;
			$wpssw_attribute_taxonomies = array();
			$table_name                 = $wpdb->prefix;
			$attribute_name             = '';
			// @codingStandardsIgnoreStart.
			$wpssw_attribute_taxonomies = $wpdb->get_results( 'SELECT * FROM ' . $wpdb->prefix . "woocommerce_attribute_taxonomies WHERE attribute_name != '' ORDER BY attribute_name ASC;" );
			set_transient( 'wc_attribute_taxonomies', $wpssw_attribute_taxonomies ); //db call ok.
			// @codingStandardsIgnoreEnd.
			$wpssw_attribute_taxonomies = array_column( array_filter( $wpssw_attribute_taxonomies ), 'attribute_name' );
			$wpssw_attribute_taxonomies = array_map(
				function( $e ) {
					return str_replace( '-', ' ', ucfirst( str_replace( 'pa_', '', $e ) ) );
				},
				$wpssw_attribute_taxonomies
			);
			$wpssw_product_attr         = $this->wpssw_get_meta_values( '_product_attributes' );

			if ( is_array( $wpssw_product_attr ) && ! empty( $wpssw_product_attr ) ) {
				$wpssw_product_attr = array_map(
					function( $e ) {
						if ( is_array( $e ) ) {
							return array_keys( $e );
						}
					},
					$wpssw_product_attr
				);
			} else {
				$wpssw_product_attr = array();
			}
			$wpssw_product_attr         = $this->wpssw_array_flatten( $wpssw_product_attr );
			$wpssw_product_attr         = array_map(
				function( $e ) {
					return str_replace( '-', ' ', ucfirst( str_replace( 'pa_', '', rawurldecode( $e ) ) ) );
				},
				$wpssw_product_attr
			);
			$wpssw_product_attr         = array_filter( array_unique( $wpssw_product_attr ) );
			$wpssw_attribute_taxonomies = array_values( array_unique( array_merge( $wpssw_attribute_taxonomies, $wpssw_product_attr ) ) );
			self::$wpssw_headers        = $wpssw_attribute_taxonomies;
		}
		/**
		 * Convert a multi-dimensional array into a single-dimensional array.
		 *
		 * @param array $wpssw_array .
		 * @return array $wpssw_result
		 */
		public function wpssw_array_flatten( $wpssw_array ) {
			if ( ! is_array( $wpssw_array ) ) {
				return false;
			}
			$wpssw_result = array();
			foreach ( $wpssw_array as $wpssw_key => $wpssw_value ) {
				if ( is_array( $wpssw_value ) ) {
					$wpssw_result = array_merge( $wpssw_result, $this->wpssw_array_flatten( $wpssw_value ) );
				} else {
					$wpssw_result[ $wpssw_key ] = trim( $wpssw_value );
				}
			}
			return $wpssw_result;
		}
		/**
		 * Get product meta value for wpssw_get_all_attributes function
		 *
		 * @param string $wpssw_meta_key .
		 * @param string $wpssw_post_type .
		 */
		public function wpssw_get_meta_values( $wpssw_meta_key, $wpssw_post_type = 'product' ) {
			// @codingStandardsIgnoreStart.
			$wpssw_posts = get_posts(
				array(
					'post_type'      => $wpssw_post_type,
					'meta_key'       => $wpssw_meta_key,
					'posts_per_page' => -1,
					'fields'         => 'ids'
				)
			);
			// @codingStandardsIgnoreEnd.
			$wpssw_meta_values = array();
			foreach ( $wpssw_posts as $wpssw_post ) {
				$wpssw_meta_values[] = get_post_meta( $wpssw_post, $wpssw_meta_key, true );
			}
			return $wpssw_meta_values;
		}
		/**
		 * Get Header List.
		 *
		 * @param array $headers .
		 */
		public static function get_header_list( $headers = array() ) {
			if ( ! empty( self::$wpssw_headers ) ) {
				$headers['WPSSW_Product_Attribute'] = self::$wpssw_headers;
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
			$wpssw_items = $wpssw_order->get_items();
			$wpssw_value = array();
			foreach ( $wpssw_items as $wpssw_item ) {
				$wpssw_metadata    = $wpssw_item->get_meta_data();
				$wpssw_attrmetaval = '';
				foreach ( $wpssw_metadata as $wpssw_meta ) {
					$wpssw_temp = str_replace( '-', ' ', trim( $wpssw_meta->key ) );
					if ( strtolower( str_replace( 'pa_', '', $wpssw_temp ) ) === strtolower( $wpssw_headers_name ) ) {
						if ( ! empty( $wpssw_meta->value ) ) {
							$wpssw_term = get_term_by( 'slug', $wpssw_meta->value, $wpssw_meta->key );
							if ( isset( $wpssw_term->name ) ) {
								$wpssw_attrmetaval .= $wpssw_term->name . ',';
							} else {
								$wpssw_attrmetaval .= $wpssw_meta->value . ',';
							}
						}
					}
				}
				$wpssw_attrmetaval = rtrim( $wpssw_attrmetaval, ',' );
				if ( is_array( $wpssw_attrmetaval ) ) {
					$wpssw_value[] = implode( ',', $wpssw_attrmetaval );
				} else {
					$wpssw_value[] = $wpssw_attrmetaval;
				}
			}
			return $wpssw_value;
		}
	}
	new WPSSW_Product_Attribute();
endif;
