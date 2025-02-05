<?php
/**
 * Main WPSyncSheets_For_WooCommerce namespace.
 *
 * @since 1.0.0
 * @package wpsyncsheets-for-woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }
if ( ! class_exists( 'WPSSW_WP_WooCommerce_Custom_Product_Addon' ) ) :
	/**
	 * Class WPSSW_WP_WooCommerce_Custom_Product_Addon.
	 */
	class WPSSW_WP_WooCommerce_Custom_Product_Addon extends WPSSW_Order_Utils {
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
		 * Check if WooCommerce Custom Product Addons (Free) plugin is active or not.
		 */
		public static function wpssw_is_pugin_active() {
			if ( ! class_exists( 'WCPA_Form' ) ) {
				return false;
			}
			return true;
		}
		/**
		 * Prepare Header List.
		 */
		protected function prepare_headers() {
			self::$wpssw_headers = self::wpssw_wcpa_product_field();
		}
		/**
		 * Get Header List.
		 *
		 * @param array $headers .
		 */
		public static function get_header_list( $headers = array() ) {
			if ( ! empty( self::$wpssw_headers ) ) {
				$headers['WPSSW_WP_WooCommerce_Custom_Product_Addon'] = self::$wpssw_headers;
			}
			return $headers;
		}
		/**
		 * WooCommerce Custom Product Addons (Free) headers
		 *
		 * @param int $wpssw_withkey .
		 * @return array $wpssw_wcpa_headers headers array.
		 */
		public static function wpssw_wcpa_product_field( $wpssw_withkey = 0 ) {
			global $wpdb;
			$table_name  = $wpdb->prefix;
			$meta_key    = '_wcpa_fb-editor-data';
			$post_status = 'publish';
			// @codingStandardsIgnoreStart.
			$wpssw_querystr             = "SELECT {$wpdb->prefix}postmeta.* FROM {$wpdb->prefix}postmeta INNER JOIN {$wpdb->prefix}posts ON ( {$wpdb->prefix}posts.ID = {$wpdb->prefix}postmeta.post_id ) WHERE 1=1 AND ( {$wpdb->prefix}postmeta.meta_key = '_wcpa_fb-editor-data' ) AND {$wpdb->prefix}posts.post_status='publish'"; //db call ok.
			$wpssw_postsmeta            = $wpdb->get_results( $wpssw_querystr, ARRAY_A );
			// @codingStandardsIgnoreEnd.
			$wpssw_wcpa_headers         = array();
			$wpssw_wcpa_headers_withkey = array();
			foreach ( $wpssw_postsmeta as $wpssw_meta ) {
				$wpssw_json_encoded = json_decode( $wpssw_meta['meta_value'] );
				if ( $wpssw_json_encoded && is_array( $wpssw_json_encoded ) ) {
					foreach ( $wpssw_json_encoded as $wpssw_field_label ) {
						if ( in_array( $wpssw_field_label->type, array( 'paragraph', 'header' ), true ) ) {
							continue;
						}
						if ( isset( $wpssw_field_label->label ) && ! empty( $wpssw_field_label->label ) ) {
							$wpssw_wcpa_headers_withkey[ $wpssw_field_label->name ] = $wpssw_field_label->label;
						} elseif ( isset( $wpssw_field_label->name ) && ! empty( $wpssw_field_label->name ) ) {
							$wpssw_wcpa_headers_withkey[ $wpssw_field_label->name ] = $wpssw_field_label->name;
						}
					}
				}
			}
			return $wpssw_wcpa_headers_withkey;
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
			/*WooCommerce Custom Product Addons (Free)*/
			$wpssw_items              = $wpssw_order->get_items();
			$wpssw_value              = array();
			$wpssw_wcpa_product_field = self::wpssw_wcpa_product_field();
			foreach ( $wpssw_items as $wpssw_item ) {
				$wpssw_metadata    = $wpssw_item->get_meta( '_WCPA_order_meta_data' );
				$wpssw_wcpametaval = '';
				$wpssw_wcpakey     = array_search( $wpssw_headers_name, $wpssw_wcpa_product_field, true );
				if ( $wpssw_wcpakey ) {
					if ( $wpssw_metadata ) {
						foreach ( $wpssw_metadata as $wpssw_meta ) {
							if ( strtolower( $wpssw_meta['name'] ) === strtolower( $wpssw_wcpakey ) ) {
								if ( is_array( $wpssw_meta['value'] ) ) {
									$data = array();
									foreach ( $wpssw_meta['value'] as $val ) {
										if ( isset( $val['value'] ) ) {
											$data[] = $val['value'];
										}
									}
									$wpssw_wcpametaval .= implode( ',', WPSSW_Setting::wpssw_array_flatten( $data ) );
								} else {
									$wpssw_wcpametaval .= trim( $wpssw_meta['value'] ) . ',';
								}
							}
						}
					}
				}
				$wpssw_wcpametaval = rtrim( $wpssw_wcpametaval, ',' );
				if ( is_array( $wpssw_wcpametaval ) ) {
					$wpssw_wcpametaval = WPSSW_Setting::wpssw_array_flatten( $wpssw_wcpametaval );
					$wpssw_value[]     = implode( ',', $wpssw_wcpametaval );
				} else {
					$wpssw_value[] = $wpssw_wcpametaval;
				}
			}
			return $wpssw_value;
		}
	}
	new WPSSW_WP_WooCommerce_Custom_Product_Addon();
endif;
