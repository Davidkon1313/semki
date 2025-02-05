<?php
/**
 * Main WPSyncSheets_For_WooCommerce namespace.
 *
 * @since 1.0.0
 * @package wpsyncsheets-for-woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }
if ( ! class_exists( 'WPSSW_WooCommerce_Subscription' ) ) :
	/**
	 * Class WPSSW_WooCommerce_Subscription.
	 */
	class WPSSW_WooCommerce_Subscription extends WPSSW_Order_Utils {
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
		 *
		 * Check if WooCommerce Subscriptions plugin is active or not..
		 */
		public static function wpssw_is_pugin_active() {
			if ( class_exists( 'WC_Subscriptions_Product' ) ) {
				return true;
			}
			return false;
		}
		/**
		 * Prepare Header List.
		 */
		protected function prepare_headers() {
			self::$wpssw_headers = array( 'Subscription Price', 'Subscription Sign Up Fee', 'Subscription Period', 'Subscription Period Interval', 'Subscription Length', 'Subscription Trial Period', 'Subscription Trial Length', 'Subscription Limit', 'Subscription One Time Shipping', 'Subscription Payment Sync Date', 'Subscription Next Payment Date', 'Subscription End Date' );
		}
		/**
		 * Get Header List.
		 *
		 * @param array $headers .
		 */
		public static function get_header_list( $headers = array() ) {
			if ( ! empty( self::$wpssw_headers ) ) {
				$headers['WPSSW_WooCommerce_Subscription'] = self::$wpssw_headers;
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
			/* WooCommerce Subscriptions */
			$wpssw_order_id = $wpssw_order->get_id();
			$wpssw_value    = array();
			$wpssw_items    = $wpssw_order->get_items();
			if ( wcs_order_contains_subscription( $wpssw_order_id, 'any' ) ) {
				foreach ( $wpssw_items as $wpssw_item ) {
					$wpssw_custom_temp       = array();
					$wpssw_temp_headers      = array();
					$wpssw_product_id        = $wpssw_item['product_id'] ? $wpssw_item['product_id'] : '';
					$wpssw_renewal_time      = '';
					$wpssw_interval          = '';
					$wpssw_period            = '';
					$wpssw_length            = '';
					$wpssw_trial_period      = '';
					$wpssw_trial_length      = '';
					$wpssw_sign_up_fee       = '';
					$wpssw_one_time_shipping = '';
					$wpssw_payment_sync_date = '';
					if ( class_exists( 'WC_Subscriptions_Product' ) && WC_Subscriptions_Product::is_subscription( $wpssw_product_id ) ) {
						$wpssw_is_exist = true;
					} else {
						$wpssw_is_exist = false;
						$wpssw_value[]  = '';
					}
					if ( $wpssw_is_exist ) {
						$wpssw_subvalue = '';
						if ( 'Subscription Price' === (string) $wpssw_headers_name ) {
							if ( $wpssw_item['variation_id'] > 0 ) {
								$wpssw_variable_product = wc_get_product( $wpssw_item['variation_id'] );
								$wpssw_price            = $wpssw_variable_product->get_price();
							} else {
								$wpssw_price = WC_Subscriptions_Product::get_price( $wpssw_product_id );
							}
							$wpssw_subvalue = $wpssw_price * $wpssw_item['quantity'];
						}
						if ( 'Subscription Limit' === (string) $wpssw_headers_name ) {
							$wpssw_subvalue = get_post_meta( $wpssw_product_id, '_subscription_limit', true );
						}
						if ( 'Subscription Period Interval' === (string) $wpssw_headers_name ) {
							$wpssw_subvalue = WC_Subscriptions_Product::get_interval( $wpssw_product_id );
						}
						if ( 'Subscription Period' === (string) $wpssw_headers_name ) {
							$wpssw_subvalue = WC_Subscriptions_Product::get_period( $wpssw_product_id );
						}
						if ( 'Subscription Length' === (string) $wpssw_headers_name ) {
							$wpssw_subvalue = WC_Subscriptions_Product::get_length( $wpssw_product_id );
						}
						if ( 'Subscription Trial Period' === (string) $wpssw_headers_name ) {
							$wpssw_subvalue = WC_Subscriptions_Product::get_trial_period( $wpssw_product_id );
						}
						if ( 'Subscription Trial Length' === (string) $wpssw_headers_name ) {
							$wpssw_subvalue = WC_Subscriptions_Product::get_trial_length( $wpssw_product_id );
						}
						if ( 'Subscription Sign Up Fee' === (string) $wpssw_headers_name ) {
							$wpssw_subvalue = WC_Subscriptions_Product::get_sign_up_fee( $wpssw_product_id );
						}
						if ( 'Subscription One Time Shipping' === (string) $wpssw_headers_name ) {
							$wpssw_one_time_shipping = WC_Subscriptions_Product::needs_one_time_shipping( $wpssw_product_id );
							if ( $wpssw_one_time_shipping ) {
								$wpssw_subvalue = 'Yes';
							} else {
								$wpssw_subvalue = 'No';
							}
						}
						if ( 'Subscription Payment Sync Date' === (string) $wpssw_headers_name ) {
							$wpssw_subvalue = WC_Subscriptions_Synchroniser::get_products_payment_day( $wpssw_product_id );
							if ( is_array( $wpssw_subvalue ) ) {
								$wpssw_month_num  = $wpssw_subvalue['month'];
								$wpssw_date_obj   = DateTime::createFromFormat( '!m', $wpssw_month_num );
								$wpssw_month_name = $wpssw_date_obj->format( 'F' ); // March.
								$wpssw_subvalue   = $wpssw_subvalue['day'] . ', ' . $wpssw_month_name;
							} else {
								$wpssw_subvalue = 'N/A';
							}
						}
						if ( 'Subscription Next Payment Date' === (string) $wpssw_headers_name ) {
							$wpssw_subvalue = WC_Subscriptions_Order::get_next_payment_date( $wpssw_order, $wpssw_product_id );
						}
						if ( 'Subscription End Date' === (string) $wpssw_headers_name ) {
							$subscription_end_date = '';
							$subscriptions_ids     = wcs_get_subscriptions_for_order( $wpssw_order_id );
							foreach ( $subscriptions_ids as $subscription_id => $subscription_obj ) {
								if ( $subscription_obj->order->id === $wpssw_order_id ) {
									$subscription_end_date = $subscription_obj->schedule_end;
									break;
								} // Stop the loop.
							}
							$wpssw_subvalue = $subscription_end_date;
						}
						if ( is_array( $wpssw_subvalue ) ) {
							$wpssw_value[] = implode( ',', $wpssw_subvalue );
						} else {
							$wpssw_value[] = ucfirst( $wpssw_subvalue );
						}
					}
				}
			}
			return $wpssw_value;
		}
	}
	new WPSSW_WooCommerce_Subscription();
endif;
