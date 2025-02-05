<?php
/**
 * Main WPSyncSheets_For_WooCommerce namespace.
 *
 * @since 1.0.0
 * @package wpsyncsheets-for-woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }
if ( ! class_exists( 'WPSSW_WooCommerce_Stripe_Payment' ) ) :
	/**
	 * Class WPSSW_WooCommerce_Stripe_Payment.
	 */
	class WPSSW_WooCommerce_Stripe_Payment extends WPSSW_Order_Utils {
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
		 * Check if WooCommerce Stripe Payment Gateway plugin is active or not.
		 */
		public static function wpssw_is_pugin_active() {
			if ( ! class_exists( 'WC_Stripe' ) ) {
				return false;
			}
			return true;
		}
		/**
		 * Prepare Header List.
		 */
		protected function prepare_headers() {
			self::$wpssw_headers = array( 'Stripe Fee', 'Stripe Net', 'Stripe Charge Captured', 'Net Revenue From Stripe', 'Stripe Transaction Id' );
		}
		/**
		 * Get Header List.
		 *
		 * @param array $headers .
		 */
		public static function get_header_list( $headers = array() ) {
			if ( ! empty( self::$wpssw_headers ) ) {
				$headers['WPSSW_WooCommerce_Stripe_Payment'] = self::$wpssw_headers;
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
			$wpssw_value       = array();
			$wpssw_stripe_data = '';
			if ( 'Stripe Fee' === (string) $wpssw_headers_name ) {
				$wpssw_stripe_data = WC_Stripe_Helper::get_stripe_fee( $wpssw_order );
			}
			if ( 'Stripe Net' === (string) $wpssw_headers_name ) {
				$wpssw_stripe_data = WC_Stripe_Helper::get_stripe_net( $wpssw_order );
			}
			$hpos_order_enabled = WPSSW_Setting::wpssw_check_hpos_order_setting_enabled();
			if ( $hpos_order_enabled && in_array( $wpssw_headers_name, array( 'Stripe Charge Captured', 'Net Revenue From Stripe', 'Stripe Transaction Id' ), true ) ) {
				if ( 'Stripe Charge Captured' === (string) $wpssw_headers_name ) {
					$wpssw_stripe_data = $wpssw_order->get_meta( '_stripe_charge_captured', true );
				}
				if ( 'Net Revenue From Stripe' === (string) $wpssw_headers_name ) {
					$wpssw_stripe_data = $wpssw_order->get_meta( 'Net Revenue From Stripe', true );
				}
				if ( 'Stripe Transaction Id' === (string) $wpssw_headers_name ) {
					$wpssw_stripe_data = WC_Stripe_Helper::is_wc_lt( '3.0' ) ? $wpssw_order->get_meta( '_transaction_id', true ) : $wpssw_order->get_transaction_id();
				}
			} else {
				if ( 'Stripe Charge Captured' === (string) $wpssw_headers_name ) {
					$wpssw_stripe_data = WC_Stripe_Helper::is_wc_lt( '3.0' ) ? get_post_meta( $wpssw_order->get_id(), '_stripe_charge_captured', true ) : $wpssw_order->get_meta( '_stripe_charge_captured', true );
				}
				if ( 'Net Revenue From Stripe' === (string) $wpssw_headers_name ) {
					$wpssw_stripe_data = WC_Stripe_Helper::is_wc_lt( '3.0' ) ? get_post_meta( $wpssw_order->get_id(), 'Net Revenue From Stripe', true ) : $wpssw_order->get_meta( 'Net Revenue From Stripe', true );
				}
				if ( 'Stripe Transaction Id' === (string) $wpssw_headers_name ) {
					$wpssw_stripe_data = WC_Stripe_Helper::is_wc_lt( '3.0' ) ? get_post_meta( $wpssw_order->get_id(), '_transaction_id', true ) : $wpssw_order->get_transaction_id();
				}
			}
			$wpssw_value[] = $wpssw_stripe_data;
			return $wpssw_value;
		}
	}
	new WPSSW_WooCommerce_Stripe_Payment();
endif;
