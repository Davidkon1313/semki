<?php
/**
 * Main WPSyncSheets_For_WooCommerce namespace.
 *
 * @since 1.0.0
 * @package wpsyncsheets-for-woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }
if ( ! class_exists( 'WPSSW_CPO_Product_Options' ) ) :
	/**
	 * Class WPSSW_CPO_Product_Options.
	 */
	class WPSSW_CPO_Product_Options extends WPSSW_Order_Utils {
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
		 * Check if Product Options and Price Calculation Formulas – Uni CPO (Premium) plugin is active or not.
		 */
		public static function wpssw_is_pugin_active() {
			if ( ! class_exists( 'Uni_Cpo' ) ) {
				return false;
			}
			return true;
		}
		/**
		 * Prepare Header List.
		 */
		public static function prepare_headers() {
			self::$wpssw_headers = self::wpssw_get_cpo_product_option_field();
		}
		/**
		 * Get Header List.
		 *
		 * @param array $headers .
		 */
		public static function get_header_list( $headers = array() ) {
			if ( ! empty( self::$wpssw_headers ) ) {
				$headers['WPSSW_CPO_Product_Options'] = self::$wpssw_headers;
			}
			return $headers;
		}
		/**
		 *
		 * Product Options and Price Calculation Formulas – Uni CPO headers.
		 */
		public static function wpssw_get_cpo_product_option_field() {
			$wpssw_cpo_headers = array();
			$wpssw_query       = new WP_Query(
				array(
					'post_type'      => 'uni_cpo_option',
					'posts_per_page' => - 1,
					'orderby'        => 'created_date',
					'order'          => 'ASC',
					'post_status'    => 'publish',
				)
			);
			if ( ! empty( $wpssw_query->posts ) ) {
				$wpssw_slugs_list = wp_list_pluck( $wpssw_query->posts, 'post_name', 'ID' );
				if ( ! empty( $wpssw_slugs_list ) ) {
					foreach ( $wpssw_slugs_list as $wpssw_k => $wpssw_v ) {
						$wpssw_cpolabel = get_post_meta( $wpssw_k, '_cpo_general', true );
						if ( isset( $wpssw_cpolabel['advanced']['cpo_label'] ) && ! empty( $wpssw_cpolabel['advanced']['cpo_label'] ) ) {
							$wpssw_cpo_headers[ '_' . $wpssw_v ] = $wpssw_cpolabel['advanced']['cpo_label'];
						} else {
							$wpssw_cpo_headers[ '_' . $wpssw_v ] = $wpssw_v;
						}
					}
				}
			}
			return $wpssw_cpo_headers;
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
			$wpssw_cpo_product_option = self::$wpssw_headers;
			$wpssw_items              = $wpssw_order->get_items();
			foreach ( $wpssw_items as $wpssw_item ) {
				$wpssw_metadata   = $wpssw_item->get_formatted_meta_data();
				$wpssw_cpometaval = '';
				$wpssw_cpokey     = array_search( stripslashes( $wpssw_headers_name ), $wpssw_cpo_product_option, true );
				if ( $wpssw_cpokey ) {
					foreach ( $wpssw_metadata as $wpssw_meta ) {
						if ( strtolower( $wpssw_meta->key ) === strtolower( $wpssw_cpokey ) ) {
							$wpssw_cpometaval .= trim( wp_strip_all_tags( $wpssw_meta->display_value ) ) . ',';
						}
					}
				}
				$wpssw_cpometaval = rtrim( $wpssw_cpometaval, ',' );
				if ( is_array( $wpssw_cpometaval ) ) {
					$wpssw_value[] = implode( ',', $wpssw_cpometaval );
				} else {
					$wpssw_value[] = $wpssw_cpometaval;
				}
			}
			return $wpssw_value;
		}
	}
	new WPSSW_CPO_Product_Options();
endif;
