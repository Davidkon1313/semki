<?php
/**
 * Main WPSyncSheets_For_WooCommerce namespace.
 *
 * @since 1.0.0
 * @package wpsyncsheets-for-woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }
if ( ! class_exists( 'WPSSW_Flexible_Checkout_Fields' ) ) :
	/**
	 * Class WPSSW_Flexible_Checkout_Fields.
	 */
	class WPSSW_Flexible_Checkout_Fields extends WPSSW_Order_Utils {
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
		 * Check if Flexible Checkout Fields for WooCommerce Plugin is active or not.
		 */
		public static function wpssw_is_pugin_active() {
			if ( ! class_exists( 'Flexible_Checkout_Fields_Plugin' ) ) {
				return false;
			}
			return true;
		}
		/**
		 * Prepare Header List.
		 */
		public static function prepare_headers() {
			$headers                                  = array();
			$wpssw_checkout_field_flexible            = WPSSW_Setting::wpssw_option( 'inspire_checkout_fields_settings', array() );
			$wpssw_billing_checkout_field_flexible    = array();
			$wpssw_shipping_checkout_field_flexible   = array();
			$wpssw_additional_checkout_field_flexible = array();
			if ( ! empty( $wpssw_checkout_field_flexible ) ) {
				if ( array_key_exists( 'billing', $wpssw_checkout_field_flexible ) ) {
					$wpssw_billing_checkout_field_flexible = self::wpssw_get_checkout_field_flexible( $wpssw_checkout_field_flexible, 'billing', 1 );
				}
				if ( array_key_exists( 'shipping', $wpssw_checkout_field_flexible ) ) {
					$wpssw_shipping_checkout_field_flexible = self::wpssw_get_checkout_field_flexible( $wpssw_checkout_field_flexible, 'shipping', 1 );
				}
				if ( array_key_exists( 'order', $wpssw_checkout_field_flexible ) ) {
					$wpssw_additional_checkout_field_flexible = self::wpssw_get_checkout_field_flexible( $wpssw_checkout_field_flexible, 'order', 1 );
				}
			}
			self::$wpssw_headers = array_merge( $wpssw_billing_checkout_field_flexible, $wpssw_shipping_checkout_field_flexible, $wpssw_additional_checkout_field_flexible );
		}
		/**
		 *
		 * Flexible Checkout Fields for WooCommerce headers.
		 *
		 * @param array  $wpssw_checkout_fields_flexible Field Array.
		 * @param string $wpssw_type Field type.
		 * @param int    $wpssw_include_key With Key / Without Key.
		 */
		public static function wpssw_get_checkout_field_flexible( $wpssw_checkout_fields_flexible = array(), $wpssw_type = '', $wpssw_include_key = 0 ) {
			$wpssw_headers = array();
			if ( isset( $wpssw_checkout_fields_flexible[ $wpssw_type ] ) && ! empty( $wpssw_checkout_fields_flexible[ $wpssw_type ] ) ) {
				foreach ( $wpssw_checkout_fields_flexible[ $wpssw_type ] as $wpssw_field ) {
					if ( isset( $wpssw_field['custom_field'] ) && ! in_array( $wpssw_field['type'], array( 'info', 'heading' ), true ) ) {
						if ( $wpssw_include_key ) {
							if ( ! empty( $wpssw_field['label'] ) ) {
								$wpssw_headers[ '_' . $wpssw_field['name'] ] = trim( $wpssw_field['label'] );
							} else {
								$wpssw_headers[ '_' . $wpssw_field['name'] ] = $wpssw_field['name'];
							}
						} else {
							if ( ! empty( $wpssw_field['label'] ) ) {
								$wpssw_headers[] = trim( $wpssw_field['label'] );
							} else {
								$wpssw_headers[] = $wpssw_field['name'];
							}
						}
					}
				}
			}
			return $wpssw_headers;
		}
		/**
		 * Get Header List.
		 *
		 * @param array $headers .
		 */
		public static function get_header_list( $headers = array() ) {
			if ( ! empty( self::$wpssw_headers ) ) {
				$headers['WPSSW_Flexible_Checkout_Fields'] = self::$wpssw_headers;
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
			$wpssw_value = array();
			self::prepare_headers();
			$wpssw_checkout_fields_flexible = self::$wpssw_headers;
			// Flexible Checkout Fields for WooCommerce.
			$wpssw_fieldkey     = array_search( $wpssw_headers_name, $wpssw_checkout_fields_flexible, true );
			$hpos_order_enabled = WPSSW_Setting::wpssw_check_hpos_order_setting_enabled();
			if ( $hpos_order_enabled ) {
				$wpssw_fieldkeydata = $wpssw_order->get_meta( $wpssw_fieldkey, true );
			} else {
				$wpssw_fieldkeydata = get_post_meta( $wpssw_order->get_id(), $wpssw_fieldkey, true );
			}
			if ( is_array( $wpssw_fieldkeydata ) ) {
				$wpssw_insert_val = implode( ',', $wpssw_fieldkeydata );
			} else {
				$wpssw_insert_val = $wpssw_fieldkeydata;
			}
			$wpssw_value[] = $wpssw_insert_val;
			return $wpssw_value;
		}
	}
	new WPSSW_Flexible_Checkout_Fields();
endif;
