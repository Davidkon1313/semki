<?php
/**
 * Main WPSyncSheets_For_WooCommerce namespace.
 *
 * @since 1.0.0
 * @package wpsyncsheets-for-woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }
if ( ! class_exists( 'WPSSW_WooCommerce_Checkout_Fields' ) ) :
	/**
	 * Class WooCommerce_Checkout_Fields.
	 */
	class WPSSW_WooCommerce_Checkout_Fields extends WPSSW_Order_Utils {
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
		 * Check if Checkout Field Editor for WooCommerce plugin is active or not.
		 */
		public static function wpssw_is_pugin_active() {
			if ( class_exists( 'WCFE_Checkout_Fields_Utils' ) ) {
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
				$headers['WPSSW_WooCommerce_Checkout_Fields'] = self::$wpssw_headers;
			}
			return $headers;
		}
		/**
		 * Prepare Header List.
		 */
		public static function prepare_headers() {
			$headers                         = array();
			$wpssw_checkout_field            = WPSSW_Setting::wpssw_option( 'thwcfe_sections', array() );
			$wpssw_billingcheckout_field     = array();
			$wpssw_shipping_checkout_field   = array();
			$wpssw_additional_checkout_field = array();
			if ( ! empty( $wpssw_checkout_field ) ) {
				if ( array_key_exists( 'billing', $wpssw_checkout_field ) ) {
					$wpssw_billingcheckout_field = self::wpssw_get_checkout_field_pro( $wpssw_checkout_field, 'billing' );
				}
				if ( array_key_exists( 'shipping', $wpssw_checkout_field ) ) {
					$wpssw_shipping_checkout_field = self::wpssw_get_checkout_field_pro( $wpssw_checkout_field, 'shipping' );
				}
				if ( array_key_exists( 'additional', $wpssw_checkout_field ) ) {
					$wpssw_additional_checkout_field = self::wpssw_get_checkout_field_pro( $wpssw_checkout_field, 'additional' );
				}
			}
			self::$wpssw_headers = array_merge( $wpssw_billingcheckout_field, $wpssw_shipping_checkout_field, $wpssw_additional_checkout_field );
		}
		/**
		 * WooCommerce Checkout Field Pro headers.
		 *
		 * @param array  $wpssw_checkout_fields .
		 * @param string $wpssw_type .
		 * @return array $wpssw_headers
		 */
		public static function wpssw_get_checkout_field_pro( $wpssw_checkout_fields = array(), $wpssw_type = '' ) {
			$wpssw_headers = array();
			if ( ! empty( $wpssw_checkout_fields[ $wpssw_type ] ) ) {
				foreach ( $wpssw_checkout_fields[ $wpssw_type ]->fields as $wpssw_field ) {
					if ( isset( $wpssw_field->property_set['custom'] ) && isset( $wpssw_field->property_set['type'] ) && is_numeric( $wpssw_field->property_set['custom'] ) && 1 !== (int) $wpssw_field->property_set['custom'] && ! in_array( $wpssw_field->property_set['type'], array( 'label', 'heading' ), true ) ) {
						if ( ! empty( $wpssw_field->property_set['label'] ) ) {
							$wpssw_headers[ $wpssw_field->property_set['name'] ] = $wpssw_field->property_set['label'];
						} else {
							$wpssw_headers[ $wpssw_field->property_set['name'] ] = $wpssw_field->property_set['name'];
						}
					}
				}
			}
			return $wpssw_headers;
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
			$wpssw_checkout_fields_pro = self::$wpssw_headers;
			$wpssw_fieldkey            = array_search( $wpssw_headers_name, $wpssw_checkout_fields_pro, true );
			$hpos_order_enabled        = WPSSW_Setting::wpssw_check_hpos_order_setting_enabled();
			if ( $hpos_order_enabled ) {
				$wpssw_fieldkeydata = $wpssw_order->get_meta( $wpssw_fieldkey, true );
			} else {
				$wpssw_fieldkeydata = get_post_meta( $wpssw_order->get_id(), $wpssw_fieldkey, true );
			}
			if ( is_array( $wpssw_fieldkeydata ) ) {
				$wpssw_insert_val = implode( ',', $wpssw_fieldkeydata );
				$wpssw_value[]    = $wpssw_insert_val;
			} else {
				if ( strpos( $wpssw_fieldkeydata, 'name' ) !== false && strpos( $wpssw_fieldkeydata, 'url' ) !== false && strpos( $wpssw_fieldkeydata, 'http' ) !== false ) {
					$wpssw_string = explode( ',', $wpssw_fieldkeydata );
					foreach ( $wpssw_string as $wpssw_s ) {
						if ( strpos( $wpssw_s, 'url' ) !== false && strpos( $wpssw_s, 'http' ) !== false ) {
							$wpssw_url          = explode( '":"', $wpssw_s );
							$wpssw_remove[]     = "'";
							$wpssw_remove[]     = '"';
							$wpssw_remove[]     = ',';
							$wpssw_fieldkeydata = str_replace( $wpssw_remove, '', $wpssw_url[1] );
						}
					}
				}
				$wpssw_value[] = $wpssw_fieldkeydata;
			}
			return $wpssw_value;
		}
	}
	new WPSSW_WooCommerce_Checkout_Fields();
endif;
