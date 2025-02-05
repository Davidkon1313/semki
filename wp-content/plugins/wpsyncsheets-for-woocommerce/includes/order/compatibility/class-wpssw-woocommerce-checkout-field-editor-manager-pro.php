<?php
/**
 * Main WPSyncSheets_For_WooCommerce namespace.
 *
 * @package wpsyncsheets-for-woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }
if ( ! class_exists( 'WPSSW_WooCommerce_Checkout_Field_Editor_Manager_Pro' ) ) :
	/**
	 * Class WPSSW_WooCommerce_Checkout_Field_Editor_Manager_Pro.
	 */
	class WPSSW_WooCommerce_Checkout_Field_Editor_Manager_Pro extends WPSSW_Order_Utils {
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
		 * Check if Checkout Field Editor and Manager for WooCommerce - Pro by Acowebs plugin is active or not.
		 */
		public static function wpssw_is_pugin_active() {
			if ( class_exists( 'AWCFE_Backend' ) ) {
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
				$headers['WPSSW_WooCommerce_Checkout_Field_Editor_Manager_Pro'] = self::$wpssw_headers;
			}
			return $headers;
		}
		/**
		 * Prepare Header List.
		 *
		 * @param int $with_key .
		 */
		public static function prepare_headers( $with_key = 0 ) {

			$wpssw_fields = WPSSW_Setting::wpssw_option( 'awcfe_fields', array() );

			$wpssw_billing_fields  = array();
			$wpssw_shipping_fields = array();
			$wpssw_order_fields    = array();
			if ( isset( $wpssw_fields['fields'] ) ) {
				foreach ( $wpssw_fields['fields'] as $key => $fields ) {
					if ( 'billing' === (string) $key && isset( $fields['fields'] ) ) {
						$wpssw_billing_fields = self::wpssw_get_checkout_field( $fields['fields'] );
					}
					if ( 'shipping' === (string) $key && isset( $fields['fields'] ) ) {
						$wpssw_shipping_fields = self::wpssw_get_checkout_field( $fields['fields'] );
					}
					if ( 'order' === (string) $key && isset( $fields['fields'] ) ) {
						$wpssw_order_fields = self::wpssw_get_checkout_field( $fields['fields'] );
					}
				}
			}
			self::$wpssw_headers = array_merge( $wpssw_billing_fields, $wpssw_shipping_fields, $wpssw_order_fields );
			if ( $with_key ) {
				$headers_withkey = array(
					'billing'  => $wpssw_billing_fields,
					'shipping' => $wpssw_shipping_fields,
					'order'    => $wpssw_order_fields,
				);
				return $headers_withkey;
			}
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
						if ( isset( $element[0] ) ) {
							if ( 1 === (int) $element[0]['custom'] ) {
								return $element[0]['label'] ? $element[0]['label'] : $element[0]['name'];
							}
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
			$wpssw_checkoutfields = self::prepare_headers( 1 );
			$awcf_data            = $wpssw_order->get_meta( '_awcfe_order_meta_key', true );
			$is_break             = 0;
			foreach ( $wpssw_checkoutfields as $checkoutfield_key => $checkoutfield ) {
				if ( 1 === (int) $is_break ) {
					break;
				}
				if ( in_array( $wpssw_headers_name, $checkoutfield, true ) && isset( $awcf_data[ $checkoutfield_key ] ) ) {
					foreach ( $awcf_data[ $checkoutfield_key ] as $wpssw_options ) {
						if ( $wpssw_options['label'] === $wpssw_headers_name || $wpssw_options['name'] === $wpssw_headers_name ) {
							$wpssw_insertval = '';
							if ( 'file' === (string) $wpssw_options['type'] ) {
								$file_det        = json_decode( $wpssw_options['value'], true );
								$wpssw_insertval = isset( $file_det['url'] ) ? $file_det['url'] : '';
							} else {
								if ( is_array( $wpssw_options['value'] ) ) {
									$wpssw_insertval = implode( ', ', $wpssw_options['value'] );
								} else {
									$wpssw_insertval = $wpssw_options['value'];
								}
							}
							$wpssw_value[] = $wpssw_insertval;
							$is_break      = 1;
							break;
						}
					}
				}
			}
			return $wpssw_value;
		}
	}
	new WPSSW_WooCommerce_Checkout_Field_Editor_Manager_Pro();
endif;
