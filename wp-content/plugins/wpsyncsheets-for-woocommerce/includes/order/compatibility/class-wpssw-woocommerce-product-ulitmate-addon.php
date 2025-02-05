<?php
/**
 * Main WPSyncSheets_For_WooCommerce namespace.
 *
 * @since 1.0.0
 * @package wpsyncsheets-for-woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }
if ( ! class_exists( 'WPSSW_WooCommerce_Product_Ulitmate_Addon' ) ) :
	/**
	 * Class WPSSW_WooCommerce_Product_Ulitmate_Addon.
	 */
	class WPSSW_WooCommerce_Product_Ulitmate_Addon extends WPSSW_Order_Utils {
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
			if ( ! class_exists( 'PEWC_Product_Extra_Post_Type' ) ) {
				return false;
			}
			return true;
		}
		/**
		 * Prepare Header List.
		 */
		protected function prepare_headers() {
			self::$wpssw_headers = $this->wpssw_product_field_extra_ultimate();
		}
		/**
		 * Get Header List.
		 *
		 * @param array $headers .
		 */
		public static function get_header_list( $headers = array() ) {
			if ( ! empty( self::$wpssw_headers ) ) {
				$headers['WPSSW_WooCommerce_Product_Ulitmate_Addon'] = self::$wpssw_headers;
			}
			return $headers;
		}
		/**
		 *
		 * WooCommerce Product Add-Ons Ultimate headers.
		 */
		public function wpssw_product_field_extra_ultimate() {
			// @codingStandardsIgnoreStart.
			$posts       = get_posts(
				array(
					'post_type'      => 'product',
					'meta_key'       => 'group_order',
					'posts_per_page' => -1,
					'fields' 		 => 'ids' //Fetch only ids.
				)
			);
			// @codingStandardsIgnoreEnd.
			$meta_values = array();
			foreach ( $posts as $post_id ) {
				$meta_values[] = get_post_meta( $post_id, 'group_order', true );
			}
			$field_key_label = array();
			foreach ( $meta_values as $m ) {
				$mm = explode( ',', $m );
				if ( is_array( $mm ) ) {
					foreach ( $mm as $key => $value ) {
						$field_key = get_post_meta( $value, 'field_ids', true );
						if ( $field_key && is_array( $field_key ) ) {
							foreach ( $field_key as $k => $v ) {
								$field_label = get_post_meta( $v, 'all_params', true );
								if ( isset( $field_label['field_label'] ) ) {
									$field_key_label[] = $field_label['field_label'];
								}
							}
						}
					}
				}
			}
			return array_unique( $field_key_label );
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
				$wpssw_metadata = $wpssw_item->get_meta_data();
				$wpssw_metaval  = '';
				foreach ( $wpssw_metadata as $wpssw_meta ) {
					if ( strtolower( $wpssw_meta->key ) === strtolower( '_' . $wpssw_headers_name ) ) {
						if ( ! empty( $wpssw_meta->value ) ) {
							$wpssw_metaval .= html_entity_decode( wp_strip_all_tags( $wpssw_meta->value ) ) . ',';
						}
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
	new WPSSW_WooCommerce_Product_Ulitmate_Addon();
endif;
