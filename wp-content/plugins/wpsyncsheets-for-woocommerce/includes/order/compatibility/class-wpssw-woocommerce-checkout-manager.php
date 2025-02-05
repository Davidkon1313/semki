<?php
/**
 * Main WPSyncSheets_For_WooCommerce namespace.
 *
 * @since 1.0.0
 * @package wpsyncsheets-for-woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }
if ( ! class_exists( 'WPSSW_Woocommerce_Checkout_Manager' ) ) :
	/**
	 * Class WPSSW_Woocommerce_Checkout_Manager.
	 */
	class WPSSW_Woocommerce_Checkout_Manager extends WPSSW_Order_Utils {
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
		 * Check if WooCommerce Checkout Manager – By QuadLayers plugin is active or not.
		 */
		public static function wpssw_is_pugin_active() {
			if ( class_exists( 'QuadLayers\WOOCCM\Plugin' ) || class_exists( 'WOOCCM' ) ) {
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
				$headers['WPSSW_Woocommerce_Checkout_Manager'] = self::$wpssw_headers;
			}
			return $headers;
		}
		/**
		 * Prepare Header List.
		 */
		public static function prepare_headers() {
			$headers                     = array();
			$wpssw_withoutkey            = 0;
			$wpssw_wcm_billing_fields    = self::wpssw_woocommerce_checkout_manager_field( 'billing', 1 );
			$wpssw_wcm_shipping_fields   = self::wpssw_woocommerce_checkout_manager_field( 'shipping', 1 );
			$wpssw_wcm_additional_fields = self::wpssw_woocommerce_checkout_manager_field( 'additional', 1 );
			$headers                     = array_merge( $wpssw_wcm_billing_fields, $wpssw_wcm_shipping_fields, $wpssw_wcm_additional_fields );
			self::$wpssw_headers         = $headers;
		}
		/**
		 * WooCommerce Checkout Manager By QuadLayers Headers
		 *
		 * @param string $wpssw_field Field Type.
		 * @param int    $wpssw_withkey For with key or without key.
		 */
		public static function wpssw_woocommerce_checkout_manager_field( $wpssw_field = '', $wpssw_withkey = 0 ) {
			if ( 'billing' === (string) $wpssw_field ) {
				$wpssw_wooccm_field = WPSSW_Setting::wpssw_option( 'wooccm_billing', true );
			}
			if ( 'shipping' === (string) $wpssw_field ) {
				$wpssw_wooccm_field = WPSSW_Setting::wpssw_option( 'wooccm_shipping', true );
			}
			if ( 'additional' === (string) $wpssw_field ) {
				$wpssw_wooccm_field = WPSSW_Setting::wpssw_option( 'wooccm_additional', true );
			}
			$wpssw_field_list          = array();
			$wpssw_field_list_withkey  = array();
			$wpssw_field_list_withtype = array();
			if ( ! empty( $wpssw_wooccm_field ) && is_array( $wpssw_wooccm_field ) ) {
				foreach ( $wpssw_wooccm_field as $wpssw_fields ) {
					if ( 'heading' === (string) $wpssw_fields['type'] ) {
						continue;
					}
					$wpssw_is_wcmfield = WPSSW_Setting::wpssw_startswith( $wpssw_fields['name'], 'wooccm' );
					if ( $wpssw_is_wcmfield ) {
						$wpssw_field_key = '_' . $wpssw_field . '_' . $wpssw_fields['name'];
						if ( ! empty( $wpssw_fields['label'] ) ) {
							$wpssw_field_list[]                                  = $wpssw_fields['label'];
							$wpssw_field_list_withkey[ $wpssw_field_key ]        = $wpssw_fields['label'];
							$wpssw_field_list_withtype[ $wpssw_fields['label'] ] = $wpssw_fields['type'];
						} else {
							$wpssw_field_list[]                                  = ucfirst( $wpssw_fields['type'] );
							$wpssw_field_list_withkey[ $wpssw_field_key ]        = ucfirst( $wpssw_fields['type'] );
							$wpssw_field_list_withtype[ $wpssw_fields['label'] ] = $wpssw_fields['type'];
						}
					}
				}
			}
			if ( 2 === (int) $wpssw_withkey ) {
				return $wpssw_field_list_withtype;
			} elseif ( 1 === (int) $wpssw_withkey ) {
				return $wpssw_field_list_withkey;
			} else {
				return $wpssw_field_list;
			}
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
			$wpssw_wcm_billing_fields        = self::wpssw_woocommerce_checkout_manager_field( 'billing', 2 );
			$wpssw_wcm_shipping_fields       = self::wpssw_woocommerce_checkout_manager_field( 'shipping', 2 );
			$wpssw_wcm_additional_fields     = self::wpssw_woocommerce_checkout_manager_field( 'additional', 2 );
			$wpssw_wcm_headers_list_withtype = array_merge( $wpssw_wcm_billing_fields, $wpssw_wcm_shipping_fields, $wpssw_wcm_additional_fields );
			$wpssw_wcm_headers_list          = self::$wpssw_headers;
			$wpssw_fieldkey                  = array_search( $wpssw_headers_name, $wpssw_wcm_headers_list, true );
			$hpos_order_enabled              = WPSSW_Setting::wpssw_check_hpos_order_setting_enabled();
			if ( $hpos_order_enabled ) {
				$wpssw_fieldkeydata = $wpssw_order->get_meta( $wpssw_fieldkey, true );
			} else {
				$wpssw_fieldkeydata = get_post_meta( $wpssw_order->get_id(), $wpssw_fieldkey, true );
			}
			$wpssw_fieldtype = '';
			if ( isset( $wpssw_wcm_headers_list_withtype[ $wpssw_headers_name ] ) ) {
				$wpssw_fieldtype = $wpssw_wcm_headers_list_withtype[ $wpssw_headers_name ];
			}
			if ( is_array( $wpssw_fieldkeydata ) ) {
				$wpssw_insert_val = implode( ',', $wpssw_fieldkeydata );
				$wpssw_value[]    = $wpssw_insert_val;
			} else {
				if ( 'file' === (string) $wpssw_fieldtype ) {
					$wpssw_insert_val = wp_get_attachment_url( $wpssw_fieldkeydata ) ? wp_get_attachment_url( $wpssw_fieldkeydata ) : '';
				} else {
					$wpssw_insert_val = $wpssw_fieldkeydata;
				}
				$wpssw_value[] = $wpssw_insert_val;
			}
			return $wpssw_value;
		}
	}
	new WPSSW_Woocommerce_Checkout_Manager();
endif;
