<?php
/**
 * Main WPSyncSheets_For_WooCommerce namespace.
 *
 * @since 1.0.0
 * @package wpsyncsheets-for-woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }
if ( ! class_exists( 'WPSSW_WooCommerce_Checkout_Field_Editor' ) ) :
	/**
	 * Class WPSSW_WooCommerce_Checkout_Field_Editor.
	 */
	class WPSSW_WooCommerce_Checkout_Field_Editor extends WPSSW_Order_Utils {
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
		 * Check if WooCommerce Checkout Field plugin is active or not.
		 */
		public static function wpssw_is_pugin_active() {
			if ( class_exists( 'WC_Checkout_Field_Editor' ) && class_exists( 'WC_Checkout_Field_Editor_PIP_Integration' ) ) {
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
				$headers['WPSSW_WooCommerce_Checkout_Field_Editor'] = self::$wpssw_headers;
			}
			return $headers;
		}
		/**
		 * Prepare Header List.
		 */
		public static function prepare_headers() {
			$wpssw_billing_fields    = WPSSW_Setting::wpssw_option( 'wc_fields_billing', array() );
			$wpssw_billing_fields    = self::wpssw_get_checkout_field( $wpssw_billing_fields );
			$wpssw_shipping_fields   = WPSSW_Setting::wpssw_option( 'wc_fields_shipping', array() );
			$wpssw_shipping_fields   = self::wpssw_get_checkout_field( $wpssw_shipping_fields );
			$wpssw_additional_fields = WPSSW_Setting::wpssw_option( 'wc_fields_additional', array() );
			$wpssw_additional_fields = self::wpssw_get_checkout_field( $wpssw_additional_fields );
			self::$wpssw_headers     = array_merge( $wpssw_billing_fields, $wpssw_shipping_fields, $wpssw_additional_fields );
		}
		/**
		 * WooCommerce Checkout Field headers.
		 *
		 * @param array $wpssw_checkout_fields .
		 * @param int   $withkey .
		 * @return array $wpssw_checkout_fields
		 */
		public static function wpssw_get_checkout_field( $wpssw_checkout_fields = array(), $withkey = 0 ) {
			if ( ! empty( $wpssw_checkout_fields ) ) {
				$wpssw_checkout_fields = array_map(
					function( $element ) {
						if ( isset( $element['custom'] ) && 1 === (int) $element['custom'] ) {
							return $element['label'] ? $element['label'] : $element['name'];
						}
					},
					$wpssw_checkout_fields
				);
			}
			if ( $withkey ) {
				$wpssw_checkout_fields = array_filter(
					$wpssw_checkout_fields,
					function( $wpssw_value ) {
						return ! is_null( $wpssw_value ) && '' !== $wpssw_value;
					}
				);
			} else {
				$wpssw_checkout_fields = array_values(
					array_filter(
						$wpssw_checkout_fields,
						function( $wpssw_value ) {
							return ! is_null( $wpssw_value ) && '' !== $wpssw_value;
						}
					)
				);
			}
			return $wpssw_checkout_fields;
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
			/*Checkout Fields*/
			$wpssw_value          = array();
			$wpssw_checkoutfields = wc_get_custom_checkout_fields( $wpssw_order );
			foreach ( $wpssw_checkoutfields as $wpssw_name => $wpssw_options ) {
				if ( $wpssw_options['label'] === $wpssw_headers_name || $wpssw_options['name'] === $wpssw_headers_name ) {
					$wpssw_value[] = wc_get_checkout_field_value( $wpssw_order, $wpssw_name, $wpssw_options );
				}
			}
			return $wpssw_value;
		}
	}
	new WPSSW_WooCommerce_Checkout_Field_Editor();
endif;
