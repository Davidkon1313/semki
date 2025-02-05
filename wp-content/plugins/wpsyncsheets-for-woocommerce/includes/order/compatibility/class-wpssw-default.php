<?php
/**
 * Main WPSyncSheets_For_WooCommerce namespace.
 *
 * @since 1.0.0
 * @package wpsyncsheets-for-woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }
if ( ! class_exists( 'WPSSW_Default' ) ) :
	/**
	 * Class WPSSW_Default.
	 */
	class WPSSW_Default extends WPSSW_Order_Utils {
		/**
		 * Store header list.
		 *
		 * @var array $wpssw_headers.
		 */
		public static $wpssw_orderwise_headers = array();
		/**
		 * Store header list.
		 *
		 * @var array $wpssw_headers.
		 */
		public static $wpssw_productwise_headers = array();
		/**
		 * Store header list.
		 *
		 * @var array $wpssw_headers.
		 */
		public static $wpssw_essential_headers = array();
		/**
		 * Class Contructor.
		 */
		public function __construct() {
			$this->prepare_headers();
			add_filter( 'wpsyncsheets_order_headers', __CLASS__ . '::get_header_list', 10, 1 );
		}
		/**
		 * Prepare Header List.
		 */
		protected function prepare_headers() {
			self::$wpssw_essential_headers   = array(
				'orderid_with_link'       => 'Order Id with Link',
				'order_number'            => 'Order Number',
				'_item_id'                => 'Order Item ID',
				'product_name'            => 'Product Name',
				'sku'                     => 'SKU',
				'product_quantity'        => 'Product Quantity',
				'_weight'                 => 'Product Weight',
				'_status'                 => 'Order Status',
				'_prices_include_tax'     => 'Prices Include Tax',
				'_order_currency'         => 'Order Currency',
				'_order_tax'              => 'Tax Total',
				'_order_total'            => 'Order Total',
				'order_fees'              => 'Order Fees',
				'_discount_total'         => 'Order Discount Total',
				'_discount_tax'           => 'Order Discount Tax',
				'_payment_method_title'   => 'Payment Method',
				'_transaction_id'         => 'Transaction ID',
				'_billing_first_name'     => 'Billing First name',
				'_billing_last_name'      => 'Billing Last Name',
				'_billing_address_1'      => 'Billing Address 1',
				'_billing_address_2'      => 'Billing Address 2',
				'_billing_city'           => 'Billing City',
				'_billing_state'          => 'Billing State',
				'_billing_postcode'       => 'Billing Postcode',
				'_billing_country'        => 'Billing Country',
				'_billing_address_index'  => 'Billing Address',
				'_billing_company'        => 'Billing Company Name',
				'_shipping_first_name'    => 'Shipping First Name',
				'_shipping_last_name'     => 'Shipping Last Name',
				'_shipping_address_1'     => 'Shipping Address 1',
				'_shipping_address_2'     => 'Shipping Address 2',
				'_shipping_city'          => 'Shipping City',
				'_shipping_state'         => 'Shipping State',
				'_shipping_postcode'      => 'Shipping Postcode',
				'_shipping_country'       => 'Shipping Country',
				'_shipping_address_index' => 'Shipping Address',
				'shipping_method'         => 'Shipping Method Title',
				'_order_shipping'         => 'Shipping Total',
				'_shipping_company'       => 'Shipping Company Name',
				'coupons'                 => 'Coupons Codes',
				'_billing_email'          => 'Email',
				'_billing_phone'          => 'Phone',
				'customer_id'             => 'Customer ID',
				'customer_role'           => 'Client Role',
				'_customer_note'          => 'Customer Note',
				'order_url'               => 'Order URL',
				'post_date'               => 'Created Date',
				'date_modified'           => 'Status Updated Date',
				'_completed_date'         => 'Order Completion Date',
				'_paid_date'              => 'Order Paid Date',
				'order_note'              => 'Order Notes',
				'refund_amount'           => 'Refund Total',
			);
			self::$wpssw_orderwise_headers   = array( 'Product name(QTY)(SKU)', 'Product QTY Total' );
			self::$wpssw_productwise_headers = array( 'Product ID', 'Product Image', 'Product Categories', 'Product Meta', 'Product Base Price', 'Product Total', 'Product Price Total' );
		}
		/**
		 * Get Header List.
		 *
		 * @param array $headers .
		 */
		public static function get_header_list( $headers = array() ) {
			if ( ! empty( self::$wpssw_orderwise_headers ) ) {
				$headers['WPSSW_Default']['OrderWise'] = self::$wpssw_orderwise_headers;
			}
			if ( ! empty( self::$wpssw_essential_headers ) ) {
				$headers['WPSSW_Default']['Essential'] = self::$wpssw_essential_headers;
			}
			if ( ! empty( self::$wpssw_productwise_headers ) ) {
				$headers['WPSSW_Default']['ProductWise'] = self::$wpssw_productwise_headers;
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
			$wpssw_items = $wpssw_order->get_items();

			$wpssw_order_data  = $wpssw_order->get_data();
			$wpssw_arr         = explode( ' ', trim( $wpssw_headers_name ) );
			$wpssw_inputoption = WPSSW_Setting::wpssw_option( 'wpssw_inputoption' );
			$wpssw_value       = array();
			$wpssw_header_type = WPSSW_Order::wpssw_is_productwise();

			$hpos_order_enabled = WPSSW_Setting::wpssw_check_hpos_order_setting_enabled();

			if ( is_array( $wpssw_product_headers ) && in_array( $wpssw_headers_name, $wpssw_product_headers, true ) ) {
				$wpssw_insert_val = 0;
				$find             = array( '/-/', '/’/', '/“/', '/”/', '/„/', '/“/' );
				$replace          = array( '–', "'", '"', '"', '"', '"' );
				if ( $wpssw_header_type ) {
					foreach ( $wpssw_items as $wpssw_item ) {
						$wpssw_insert_val        = '';
						$wpssw_item_product_name = $wpssw_item->get_name();
						$wpssw_product           = wc_get_product( $wpssw_item['product_id'] );
						if ( ! empty( $wpssw_product ) && ! is_wp_error( $wpssw_product ) ) {
							$wpssw_item_product_name = $wpssw_product->get_title();
						}
						if ( preg_replace( $find, $replace, $wpssw_item_product_name, 1 ) === preg_replace( $find, $replace, $wpssw_headers_name, 1 ) ) {
							$wpssw_insert_val = $wpssw_item->get_quantity();
						}
						$wpssw_value[] = $wpssw_insert_val;
					}
					return $wpssw_value;
				} else {
					foreach ( $wpssw_items as $wpssw_item ) {
						$wpssw_item_product_name = $wpssw_item->get_name();
						$wpssw_product           = wc_get_product( $wpssw_item['product_id'] );
						if ( ! empty( $wpssw_product ) && ! is_wp_error( $wpssw_product ) ) {
							$wpssw_item_product_name = $wpssw_product->get_title();
						}
						if ( preg_replace( $find, $replace, $wpssw_item_product_name, 1 ) === preg_replace( $find, $replace, $wpssw_headers_name, 1 ) ) {
							$wpssw_insert_val = (int) $wpssw_insert_val + (int) $wpssw_item->get_quantity();
						}
					}
					$wpssw_value[] = $wpssw_insert_val;
					return $wpssw_value;
				}
			}
			if ( 'Order Status' === (string) $wpssw_headers_name ) {
				$wpssw_insert_val = ucfirst( $wpssw_order->get_status() );
				$wpssw_value[]    = $wpssw_insert_val;
				return $wpssw_value;
			}
			if ( 'Order Number' === (string) $wpssw_headers_name ) {
				$wpssw_value[] = $wpssw_order->get_order_number() ? $wpssw_order->get_order_number() : '';
				return $wpssw_value;
			}
			if ( 'Order Id with Link' === (string) $wpssw_headers_name ) {
				$wpssw_value[] = $wpssw_order->get_edit_order_url() ? '=HYPERLINK("' . $wpssw_order->get_edit_order_url() . '"; ' . $wpssw_order->get_id() . ')' : '';
				return $wpssw_value;
			}
			if ( 'Prices Include Tax' === (string) $wpssw_headers_name ) {
				$wpssw_insert_val = $wpssw_order_data['prices_include_tax'] ? 'Yes' : 'No';
				$wpssw_value[]    = $wpssw_insert_val;
				return $wpssw_value;
			}
			if ( 'Billing' === (string) $wpssw_arr[0] ) {
				$wpssw_strs = trim( strtolower( substr( $wpssw_headers_name, 8 ) ) );
				if ( 'insert' === (string) $wpssw_operation ) {
					$wpssw_name = str_replace( ' ', '_', $wpssw_strs );
					if ( 'Billing Postcode' === (string) $wpssw_headers_name ) {
						if ( 'RAW' === (string) $wpssw_inputoption ) {
							$wpssw_insert_val = $wpssw_order_data['billing'][ $wpssw_name ] ? $wpssw_order_data['billing'][ $wpssw_name ] : '';
						} else {
							$wpssw_insert_val = $wpssw_order_data['billing'][ $wpssw_name ] ? "'" . $wpssw_order_data['billing'][ $wpssw_name ] : '';
						}
					} elseif ( 'Billing Address' === (string) $wpssw_headers_name ) {
						$wpssw_states          = WC()->countries->get_states( $wpssw_order->get_billing_country() );
						$wpssw_country         = $wpssw_order->get_billing_country();
						$wpssw_billing_country = empty( $wpssw_country ) ? '' : WC()->countries->countries[ $wpssw_country ];
						$wpssw_states_name     = ! empty( $wpssw_states[ $wpssw_order->get_billing_state() ] ) ? $wpssw_states[ $wpssw_order->get_billing_state() ] : $wpssw_order_data['billing']['state'];
						$wpssw_insert_val      = $wpssw_order_data['billing']['address_1'] . '
' . $wpssw_order_data['billing']['address_2'] . '
' . $wpssw_order_data['billing']['city'] . '
' . $wpssw_order_data['billing']['postcode'] . '
' . $wpssw_states_name . '
' . $wpssw_billing_country;
					} elseif ( 'Billing Company Name' === (string) $wpssw_headers_name ) {
						$wpssw_insert_val = $wpssw_order_data['billing']['company'] ? $wpssw_order_data['billing']['company'] : '';
					} elseif ( 'Billing Country' === (string) $wpssw_headers_name ) {
						if ( $wpssw_order->get_billing_country() ) {
							$wpssw_insert_val = WC()->countries->countries[ $wpssw_order->get_billing_country() ];
						} else {
							$wpssw_insert_val = '';
						}
					} elseif ( 'Billing State' === (string) $wpssw_headers_name ) {
						if ( $wpssw_order->get_billing_country() ) {
							$wpssw_states     = WC()->countries->get_states( $wpssw_order->get_billing_country() );
							$wpssw_insert_val = ! empty( $wpssw_states[ $wpssw_order->get_billing_state() ] ) ? $wpssw_states[ $wpssw_order->get_billing_state() ] : '';
						} else {
							$wpssw_insert_val = $wpssw_order->get_billing_state();
						}
					} else {
						$wpssw_insert_val = $wpssw_order_data['billing'][ $wpssw_name ] ? $wpssw_order_data['billing'][ $wpssw_name ] : '';
					}
					$wpssw_value[] = $wpssw_insert_val;
					return $wpssw_value;
				} else {
					$wpssw_name = '_billing_' . str_replace( ' ', '_', $wpssw_strs );
					if ( 'Billing Postcode' === (string) $wpssw_headers_name ) {
						if ( 'RAW' === (string) $wpssw_inputoption ) {
							// @codingStandardsIgnoreStart.
							$wpssw_insert_val = isset( $_REQUEST[ $wpssw_name ] ) ? sanitize_text_field( wp_unslash( $_REQUEST[ $wpssw_name ] ) ) : '';
							// @codingStandardsIgnoreEnd.
						} else {
							// @codingStandardsIgnoreStart.
							$wpssw_insert_val = isset( $_REQUEST[ $wpssw_name ] ) ? "'" . sanitize_text_field( wp_unslash( $_REQUEST[ $wpssw_name ] ) ) : '';
							// @codingStandardsIgnoreEnd.
						}
					} elseif ( 'Billing Address' === (string) $wpssw_headers_name ) {
						$wpssw_states          = WC()->countries->get_states( $wpssw_order->get_billing_country() );
						$wpssw_country         = $wpssw_order->get_billing_country();
						$wpssw_billing_country = empty( $wpssw_country ) ? '' : WC()->countries->countries[ $wpssw_country ];
						// @codingStandardsIgnoreStart.
						$wpssw_billing_state   = isset( $_REQUEST['_billing_state'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_billing_state'] ) ) : '';
						// @codingStandardsIgnoreEnd.
						$wpssw_states_name = isset( $wpssw_states[ $wpssw_order->get_billing_state() ] ) ? $wpssw_states[ $wpssw_order->get_billing_state() ] : $wpssw_billing_state;
						$wpssw_insert_val  = $wpssw_order_data['billing']['address_1'] . '
' . $wpssw_order_data['billing']['address_2'] . '
' . $wpssw_order_data['billing']['city'] . '
' . $wpssw_order_data['billing']['postcode'] . '
' . $wpssw_states_name . '
' . $wpssw_billing_country;
					} elseif ( 'Billing Company Name' === (string) $wpssw_headers_name ) {
						// phpcs:ignore.
						$wpssw_insert_val = isset( $_REQUEST['_billing_company'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_billing_company'] ) ): '';
					} elseif ( 'Billing Country' === (string) $wpssw_headers_name ) {
						$wpssw_country    = $wpssw_order->get_billing_country();
						$wpssw_insert_val = empty( $wpssw_country ) ? '' : WC()->countries->countries[ $wpssw_country ];
					} elseif ( 'Billing State' === (string) $wpssw_headers_name ) {
						if ( $wpssw_order->get_billing_country() ) {
							$wpssw_states = WC()->countries->get_states( $wpssw_order->get_billing_country() );
							// phpcs:ignore.
							$wpssw_insert_val = ! empty( $wpssw_states[ $_REQUEST['_billing_state'] ] ) ? $wpssw_states[ sanitize_text_field( wp_unslash( $_REQUEST['_billing_state'] ) ) ] : '';
						} else {
							$wpssw_insert_val = $wpssw_order->get_billing_state();
						}
					} else {
						// phpcs:ignore.
						$wpssw_insert_val = isset( $_REQUEST[ $wpssw_name ] ) ? sanitize_text_field( wp_unslash( $_REQUEST[ $wpssw_name ] ) ) : '';
					}
					$wpssw_value[] = trim( $wpssw_insert_val );
					return $wpssw_value;
				}
			}
			if ( 'Shipping' === (string) $wpssw_arr[0] ) {
				$wpssw_shipping_method_title = '';
				$wpssw_strs                  = trim( strtolower( substr( $wpssw_headers_name, 9 ) ) );
				$wpssw_name                  = str_replace( ' ', '_', $wpssw_strs );
				if ( 'Shipping Method Title' === (string) $wpssw_headers_name ) {
					foreach ( $wpssw_order->get_items( 'shipping' ) as $wpssw_item_id => $wpssw_shipping_item_obj ) {
						$wpssw_shipping_method_title = $wpssw_shipping_item_obj->get_method_title();
					}
					$wpssw_insert_val = $wpssw_shipping_method_title ? $wpssw_shipping_method_title : '';
				} elseif ( 'Shipping Total' === (string) $wpssw_headers_name ) {
					$wpssw_shipping_method_total = '';
					foreach ( $wpssw_order->get_items( 'shipping' ) as $wpssw_item_id => $wpssw_shipping_item_obj ) {
						$wpssw_shipping_method_total = $wpssw_shipping_item_obj->get_total();
					}
					$wpssw_insert_val = $wpssw_shipping_method_total ? $wpssw_shipping_method_total : '';
					$wpssw_insert_val = WPSSW_Order::wpssw_get_formatted_values( $wpssw_insert_val );
				} elseif ( 'Shipping Postcode' === (string) $wpssw_headers_name ) {
					if ( 'RAW' === (string) $wpssw_inputoption ) {
						$wpssw_insert_val = $wpssw_order_data['shipping'][ $wpssw_name ] ? $wpssw_order_data['shipping'][ $wpssw_name ] : '';
					} else {
						$wpssw_insert_val = $wpssw_order_data['shipping'][ $wpssw_name ] ? "'" . $wpssw_order_data['shipping'][ $wpssw_name ] : '';
					}
				} elseif ( 'Shipping Address' === (string) $wpssw_headers_name ) {
					$wpssw_states      = WC()->countries->get_states( $wpssw_order->get_shipping_country() );
					$wpssw_states_name = isset( $wpssw_states[ $wpssw_order->get_shipping_state() ] ) ? $wpssw_states[ $wpssw_order->get_shipping_state() ] : $wpssw_order_data['shipping']['state'];
					if ( isset( WC()->countries->countries[ $wpssw_order->get_shipping_country() ] ) ) {
						$wpssw_insert_val = $wpssw_order_data['billing']['address_1'] . '
' . $wpssw_order_data['shipping']['address_2'] . '
' . $wpssw_order_data['shipping']['city'] . '
' . $wpssw_order_data['shipping']['postcode'] . '
' . $wpssw_states_name . '
' . WC()->countries->countries[ $wpssw_order->get_shipping_country() ];
					} else {
						$wpssw_insert_val = $wpssw_order_data['billing']['address_1'] . '
' . $wpssw_order_data['shipping']['address_2'] . '
' . $wpssw_order_data['shipping']['city'] . '
' . $wpssw_order_data['shipping']['postcode'] . '
' . $wpssw_states_name . '
';
					}
				} elseif ( 'Shipping Company Name' === (string) $wpssw_headers_name ) {
					$wpssw_insert_val = $wpssw_order_data['shipping']['company'] ? $wpssw_order_data['shipping']['company'] : '';
				} elseif ( 'Shipping Country' === (string) $wpssw_headers_name ) {
					$wpssw_insert_val = isset( WC()->countries->countries[ $wpssw_order->get_shipping_country() ] ) ? WC()->countries->countries[ $wpssw_order->get_shipping_country() ] : '';
				} elseif ( 'Shipping State' === (string) $wpssw_headers_name ) {
					$wpssw_states     = WC()->countries->get_states( $wpssw_order->get_shipping_country() );
					$wpssw_insert_val = ! empty( $wpssw_states[ $wpssw_order->get_shipping_state() ] ) ? $wpssw_states[ $wpssw_order->get_shipping_state() ] : $wpssw_order_data['shipping']['state'];
				} else {
					$wpssw_insert_val = $wpssw_order_data['shipping'][ $wpssw_name ] ? $wpssw_order_data['shipping'][ $wpssw_name ] : '';
				}
				$wpssw_value[] = trim( $wpssw_insert_val );
				return $wpssw_value;
			}
			if ( 'Order Currency' === (string) $wpssw_headers_name ) {
				$wpssw_insert_val = $wpssw_order_data['currency'] ? $wpssw_order_data['currency'] : '';
				$wpssw_value[]    = $wpssw_insert_val;
				return $wpssw_value;
			}
			if ( 'Transaction ID' === (string) $wpssw_headers_name ) {
				$wpssw_insert_val = $wpssw_order_data['transaction_id'] ? $wpssw_order_data['transaction_id'] : '';
				$wpssw_value[]    = $wpssw_insert_val;
				return $wpssw_value;
			}
			if ( 'Tax Total' === (string) $wpssw_headers_name ) {
				$wpssw_taxes_total = $wpssw_order->get_total_tax();
				$wpssw_insert_val  = $wpssw_taxes_total ? $wpssw_taxes_total : 0;
				$wpssw_insert_val  = WPSSW_Order::wpssw_get_formatted_values( $wpssw_insert_val );
				$wpssw_value[]     = $wpssw_insert_val;
				return $wpssw_value;
			}
			if ( 'Coupons Codes' === (string) $wpssw_headers_name ) {
				$wpssw_version     = '3.7.0';
				$wpssw_coupon_code = '';
				global $woocommerce;
				if ( version_compare( $woocommerce->version, $wpssw_version, '>=' ) ) {
					$wpssw_get_coupon_codes = implode( ',', $wpssw_order->get_coupon_codes() );
					$wpssw_insert_val       = $wpssw_get_coupon_codes ? $wpssw_get_coupon_codes : '';
				} else {
					$wpssw_get_used_coupons = implode( ',', $wpssw_order->get_used_coupons() );
					$wpssw_insert_val       = $wpssw_get_used_coupons ? $wpssw_get_used_coupons : '';
				}
				$wpssw_value[] = $wpssw_insert_val;
				return $wpssw_value;
			}
			if ( 'Order URL' === (string) $wpssw_headers_name ) {
				$wpssw_get_view_order_url = $wpssw_order->get_edit_order_url();
				$wpssw_insert_val         = $wpssw_get_view_order_url ? $wpssw_get_view_order_url : '';
				$wpssw_value[]            = $wpssw_insert_val;
				return $wpssw_value;
			}
			if ( 'Customer Note' === (string) $wpssw_headers_name ) {
				$wpssw_insert_val = $wpssw_order_data['customer_note'] ? $wpssw_order_data['customer_note'] : '';
				$wpssw_value[]    = $wpssw_insert_val;
				return $wpssw_value;
			}
			if ( 'Customer ID' === (string) $wpssw_headers_name ) {
				$wpssw_insert_val = $wpssw_order_data['customer_id'] ? $wpssw_order_data['customer_id'] : '';
				$wpssw_value[]    = $wpssw_insert_val;
				return $wpssw_value;
			}
			if ( 'Client Role' === (string) $wpssw_headers_name ) {
				$wpssw_insert_val = '';
				if ( $wpssw_order_data['customer_id'] ) {
					$wpssw_customer       = new WP_User( $wpssw_order_data['customer_id'] );
					$wpssw_customer_roles = array_filter( $wpssw_customer->roles );
					$wpssw_insert_val     = $wpssw_customer_roles[0] ? $wpssw_customer_roles[0] : '';
				}
				$wpssw_value[] = $wpssw_insert_val;
				return $wpssw_value;
			}
			if ( 'Order Total' === (string) $wpssw_headers_name ) {
				$wpssw_order_total = (float) $wpssw_order_data['total'] - (float) $wpssw_order->get_total_refunded();
				$wpssw_total       = WPSSW_Order::wpssw_get_formatted_values( $wpssw_order_total );
				$wpssw_insert_val  = $wpssw_total ? $wpssw_total : 0;
				$wpssw_value[]     = $wpssw_insert_val;
				return $wpssw_value;
			}
			if ( 'Order Discount Total' === (string) $wpssw_headers_name ) {
				$wpssw_insert_val = WPSSW_Order::wpssw_get_formatted_values( $wpssw_order_data['discount_total'] );
				$wpssw_value[]    = $wpssw_insert_val;
				return $wpssw_value;
			}
			if ( 'Order Discount Tax' === (string) $wpssw_headers_name ) {
				$wpssw_insert_val = WPSSW_Order::wpssw_get_formatted_values( $wpssw_order_data['discount_tax'] );
				$wpssw_value[]    = $wpssw_insert_val;
				return $wpssw_value;
			}
			if ( 'Payment Method' === (string) $wpssw_headers_name ) {
				$wpssw_insert_val = $wpssw_order_data['payment_method_title'] ? $wpssw_order_data['payment_method_title'] : '';
				$wpssw_value[]    = $wpssw_insert_val;
				return $wpssw_value;
			}
			if ( 'Email' === (string) $wpssw_headers_name ) {
				if ( 'insert' === (string) $wpssw_operation ) {
					$wpssw_insert_val = $wpssw_order_data['billing']['email'] ? $wpssw_order_data['billing']['email'] : '';
				} else {
					// phpcs:ignore.
					$wpssw_insert_val = isset( $_REQUEST['_billing_email'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_billing_email'] ) ) : '';
				}
				$wpssw_value[] = $wpssw_insert_val;
				return $wpssw_value;
			}
			if ( 'Phone' === (string) $wpssw_headers_name ) {
				if ( 'RAW' === (string) $wpssw_inputoption ) {
					if ( 'insert' === (string) $wpssw_operation ) {
						$wpssw_insert_val = $wpssw_order_data['billing']['phone'] ? $wpssw_order_data['billing']['phone'] : '';
					} else {
						// phpcs:ignore.
						$wpssw_insert_val = isset( $_REQUEST['_billing_phone'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_billing_phone'] ) ) : '';
					}
				} else {
					if ( 'insert' === (string) $wpssw_operation ) {
						$wpssw_insert_val = $wpssw_order_data['billing']['phone'] ? "'" . $wpssw_order_data['billing']['phone'] : '';
					} else {
						// phpcs:ignore.
						$wpssw_insert_val = isset( $_REQUEST['_billing_phone'] ) ? "'" . sanitize_text_field( wp_unslash( $_REQUEST['_billing_phone'] ) ) : '';
					}
				}
				$wpssw_value[] = $wpssw_insert_val;
				return $wpssw_value;
			}
			if ( 'Created Date' === (string) $wpssw_headers_name ) {
				$wpssw_insert_val = $wpssw_order_data['date_created']->format( WPSSW_Setting::wpssw_option( 'date_format' ) . ' ' . WPSSW_Setting::wpssw_option( 'time_format' ) );
				$wpssw_value[]    = $wpssw_insert_val;
				return $wpssw_value;
			}
			if ( 'Status Updated Date' === (string) $wpssw_headers_name ) {
				if ( isset( $wpssw_order_data['date_modified'] ) ) {
					$wpssw_insert_val = $wpssw_order_data['date_modified']->format( WPSSW_Setting::wpssw_option( 'date_format' ) . ' ' . WPSSW_Setting::wpssw_option( 'time_format' ) );
				} else {
					$wpssw_insert_val = '';
				}
				$wpssw_value[] = $wpssw_insert_val;
				return $wpssw_value;
			}
			if ( 'Order Completion Date' === (string) $wpssw_headers_name ) {
				if ( isset( $wpssw_order_data['date_completed'] ) ) {
					$wpssw_insert_val = $wpssw_order_data['date_completed']->format( WPSSW_Setting::wpssw_option( 'date_format' ) . ' ' . WPSSW_Setting::wpssw_option( 'time_format' ) );
				} else {
					$wpssw_insert_val = '';
				}
				$wpssw_value[] = $wpssw_insert_val;
				return $wpssw_value;
			}
			if ( 'Order Paid Date' === (string) $wpssw_headers_name ) {
				if ( isset( $wpssw_order_data['date_paid'] ) ) {
					$wpssw_insert_val = $wpssw_order_data['date_paid']->format( WPSSW_Setting::wpssw_option( 'date_format' ) . ' ' . WPSSW_Setting::wpssw_option( 'time_format' ) );
				} else {
					$wpssw_insert_val = '';
				}
				$wpssw_value[] = $wpssw_insert_val;
				return $wpssw_value;
			}
			if ( 'Product name(QTY)(SKU)' === (string) $wpssw_headers_name ) {
				$wpssw_prod_qty = '';
				$wpssw_items    = $wpssw_order->get_items();
				foreach ( $wpssw_items as $wpssw_item ) {
					$wpssw_product_variation_id = $wpssw_item['variation_id'];
					if ( $wpssw_product_variation_id ) {
						$wpssw_product = wc_get_product( $wpssw_item['variation_id'] );
					} else {
						$wpssw_product = wc_get_product( $wpssw_item['product_id'] );
					}
					$wpssw_sku = '';
					if ( ! empty( $wpssw_product ) && ! is_wp_error( $wpssw_product ) ) {
						$wpssw_sku = '(' . $wpssw_product->get_sku() . ')';
					}
					$wpssw_product_name = $wpssw_item['name'] . '(' . $wpssw_item['quantity'] . ')' ? $wpssw_item['name'] . '(' . $wpssw_item['quantity'] . ')' . $wpssw_sku : '';
					$wpssw_prod_qty    .= ',' . $wpssw_product_name;
				}
				$wpssw_value[] = ltrim( $wpssw_prod_qty, ',' );
				return $wpssw_value;
			}
			if ( 'Product QTY Total' === (string) $wpssw_headers_name ) {
				$wpssw_prod_qty = 0;
				$wpssw_items    = $wpssw_order->get_items();
				foreach ( $wpssw_items as $wpssw_item ) {
					$wpssw_prod_qty = $wpssw_prod_qty + $wpssw_item['quantity'];
				}
				$wpssw_value[] = (int) $wpssw_prod_qty;
				return $wpssw_value;
			}
			if ( 'Order Notes' === (string) $wpssw_headers_name ) {
				$wpssw_insert_val = WPSSW_Order::wpssw_get_all_order_notes( $wpssw_order->get_id() );
				$wpssw_insert_val = implode( ',', $wpssw_insert_val );
				$wpssw_value[]    = $wpssw_insert_val;
				return $wpssw_value;
			}
			if ( 'Order Fees' === (string) $wpssw_headers_name ) {
				$wpssw_order_fees = 0;
				foreach ( $wpssw_order->get_items( 'fee' ) as $item_id => $item_fee ) {
					// The fee total amount.
					$wpssw_order_fees = $wpssw_order_fees + $item_fee->get_total();
				}
				$wpssw_order_fees = WPSSW_Order::wpssw_get_formatted_values( $wpssw_order_fees );
				$wpssw_value[]    = $wpssw_order_fees;
				return $wpssw_value;
			}
			if ( 'Refund Total' === (string) $wpssw_headers_name ) {
				$wpssw_order_total = (float) $wpssw_order->get_total_refunded();
				$wpssw_total       = WPSSW_Order::wpssw_get_formatted_values( $wpssw_order_total );
				$wpssw_insert_val  = $wpssw_total ? $wpssw_total : 0;
				$wpssw_value[]     = $wpssw_insert_val;
				return $wpssw_value;
			}

			/** Static header dropdown wpssw_static_header_name */
			if ( ! empty( $wpssw_custom_value ) ) {
				$wpssw_static_header_name = array_column( $wpssw_custom_value, 0 );
				if ( in_array( $wpssw_headers_name, $wpssw_static_header_name, true ) ) {
					$search_key         = array_search( $wpssw_headers_name, $wpssw_static_header_name, true );
					$wpssw_search_array = $wpssw_custom_value[ $search_key ];
					if ( 'IP_address' === (string) $wpssw_search_array[1] ) {
						if ( isset( $_SERVER['HTTP_CLIENT_IP'] ) && ! empty( sanitize_text_field( wp_unslash( $_SERVER['HTTP_CLIENT_IP'] ) ) ) ) {
							$wpssw_insert_val = sanitize_text_field( wp_unslash( $_SERVER['HTTP_CLIENT_IP'] ) );
						} elseif ( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) && ! empty( sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) ) ) {
							$wpssw_insert_val = sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) );
						} else {
							$wpssw_insert_val = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
						}
						if ( $hpos_order_enabled ) {
							$wpssw_saved_ip_address = $wpssw_order->get_meta( 'wpssw_ip_address', true );
						} else {
							$wpssw_saved_ip_address = get_post_meta( $wpssw_order->get_id(), 'wpssw_ip_address', true );
						}
						if ( ! empty( $wpssw_saved_ip_address ) ) {
							$wpssw_insert_val = $wpssw_saved_ip_address;
						}
						$wpssw_value[] = $wpssw_insert_val;
						return $wpssw_value;
					}
					if ( 'user_agent' === (string) $wpssw_search_array[1] ) {
						$wpssw_insert_val = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '';
						if ( $hpos_order_enabled ) {
							$wpssw_saved_user_agent = $wpssw_order->get_meta( 'wpssw_user_agent', true );
						} else {
							$wpssw_saved_user_agent = get_post_meta( $wpssw_order->get_id(), 'wpssw_user_agent', true );
						}
						if ( ! empty( $wpssw_saved_user_agent ) ) {
							$wpssw_insert_val = $wpssw_saved_user_agent;
						}
						$wpssw_value[] = $wpssw_insert_val;
						return $wpssw_value;
					}
					if ( 'user_name' === (string) $wpssw_search_array[1] ) {
						$wpssw_user       = wp_get_current_user();
						$wpssw_user_name  = $wpssw_user->data;
						$wpssw_insert_val = $wpssw_user_name->display_name;

						if ( $hpos_order_enabled ) {
							$wpssw_saved_username = $wpssw_order->get_meta( 'wpssw_username', true );
						} else {
							$wpssw_saved_username = get_post_meta( $wpssw_order->get_id(), 'wpssw_username', true );
						}
						if ( ! empty( $wpssw_saved_username ) ) {
							$wpssw_insert_val = $wpssw_saved_username;
						}

						$wpssw_value[] = $wpssw_insert_val;
						return $wpssw_value;
					}
					if ( 'site_name' === (string) $wpssw_search_array[1] ) {
						$wpssw_insert_val = get_bloginfo();
						$wpssw_value[]    = $wpssw_insert_val;
						return $wpssw_value;
					}
					if ( 'site_URL' === (string) $wpssw_search_array[1] ) {
						$wpssw_insert_val = get_site_url();
						$wpssw_value[]    = $wpssw_insert_val;
						return $wpssw_value;
					}
					if ( 'blank' === (string) $wpssw_search_array[1] ) {
						$wpssw_insert_val = Google_Model::NULL_VALUE;
						$wpssw_value[]    = $wpssw_insert_val;
						return $wpssw_value;
					}
				}
			}
			foreach ( $wpssw_items as $wpssw_id => $wpssw_item ) {

				if ( 'Product ID' === (string) $wpssw_headers_name ) {
					$wpssw_product_id = $wpssw_item['product_id'] ? $wpssw_item['product_id'] : '';
					$wpssw_value[]    = $wpssw_product_id;
					continue;
				}
				if ( 'Product Categories' === (string) $wpssw_headers_name ) {
					$wpssw_product_id = $wpssw_item['product_id'] ? $wpssw_item['product_id'] : '';
					$product_cats     = '';
					if ( ! empty( $wpssw_product_id ) ) {
						$product_cats = wp_get_post_terms( $wpssw_product_id, 'product_cat', array( 'fields' => 'names' ) );
					}
					$product_category = array();
					if ( is_array( $product_cats ) && ! empty( $product_cats ) ) {
						$product_category = $product_cats;
					}
					$wpssw_value[] = implode( ', ', $product_category );
					continue;
				}
				if ( 'Product Name' === (string) $wpssw_headers_name ) {
					$wpssw_product_name = $wpssw_item['name'] ? $wpssw_item['name'] : '';
					$wpssw_value[]      = $wpssw_product_name;
					continue;
				}
				if ( 'Product Meta' === (string) $wpssw_headers_name ) {
					$wpssw_product_id = $wpssw_item['product_id'] ? $wpssw_item['product_id'] : '';
					$wpssw_product    = wc_get_product( $wpssw_product_id );
					$wpssw_meta_html  = '';
					
					if ( ! empty( $wpssw_product_id ) &&  ! empty( $wpssw_product ) && ! is_wp_error( $wpssw_product ) ) {
						if ( $wpssw_product->is_type( 'variable' ) ) {
							$wpssw_meta_html = WPSSW_Order::wpssw_getItemmeta( $wpssw_item );
						}
					}
					$wpssw_value[] = $wpssw_meta_html;
					continue;
				}
				if ( 'SKU' === (string) $wpssw_headers_name ) {
					// Check if product has variation.
					$wpssw_product_variation_id = $wpssw_item['variation_id'];
					if ( $wpssw_product_variation_id ) {
						$wpssw_product = wc_get_product( $wpssw_item['variation_id'] );
					} else {
						$wpssw_product = wc_get_product( $wpssw_item['product_id'] );
					}
					$wpssw_sku = '';
					if ( ! empty( $wpssw_product ) && ! is_wp_error( $wpssw_product ) ) {
						$wpssw_sku = $wpssw_product->get_sku();
					}
					$wpssw_value[] = $wpssw_sku;
					continue;
				}
				if ( 'Product Quantity' === (string) $wpssw_headers_name ) {
					$wpssw_quantity = $wpssw_item['quantity'] ? $wpssw_item['quantity'] : '';
					$wpssw_value[]  = $wpssw_quantity;
					continue;
				}
				if ( 'Product Base Price' === (string) $wpssw_headers_name ) {
					$wpssw_total = '';
					if ( $wpssw_item->get_quantity() > 0 ) {
						$wpssw_total = ( $wpssw_item->get_subtotal() / $wpssw_item->get_quantity() );
					}
					$wpssw_value[] = WPSSW_Order::wpssw_get_formatted_values( $wpssw_total );
					continue;
				}
				if ( 'Product Total' === (string) $wpssw_headers_name ) {
					$wpssw_total   = 0;
					$wpssw_total   = $wpssw_item->get_subtotal();
					$wpssw_value[] = WPSSW_Order::wpssw_get_formatted_values( $wpssw_total );
					continue;
				}
				if ( 'Product Price Total' === (string) $wpssw_headers_name ) {
					$wpssw_total   = '';
					$wpssw_total   = $wpssw_item->get_total();
					$wpssw_value[] = WPSSW_Order::wpssw_get_formatted_values( $wpssw_total );
					continue;
				}
				if ( 'Product Image' === (string) $wpssw_headers_name ) {
					$wpssw_image_src = '';
					if ( $wpssw_item['variation_id'] > 0 ) {
						$wpssw_variable_product = wc_get_product( $wpssw_item['variation_id'] );
						if ( ! empty( $wpssw_variable_product ) && ! is_wp_error( $wpssw_variable_product ) ) {
							$wpssw_image_id         = $wpssw_variable_product->get_image_id();
							$wpssw_image_array      = wp_get_attachment_image_src( $wpssw_image_id, 'thumbnail' );
							$wpssw_image_src        = isset( $wpssw_image_array[0] ) ? $wpssw_image_array[0] : '';
						}
					} else {
						$wpssw_product_id = $wpssw_item['product_id'] ? $wpssw_item['product_id'] : '';
						if ( $wpssw_product_id ) {
							$wpssw_variable_product = wc_get_product( $wpssw_product_id );
							if ( ! empty( $wpssw_variable_product ) && ! is_wp_error( $wpssw_variable_product ) ) {
								$wpssw_image_id         = $wpssw_variable_product->get_image_id();
								$wpssw_image_array      = wp_get_attachment_image_src( $wpssw_image_id, 'thumbnail' );
								$wpssw_image_src        = isset( $wpssw_image_array[0] ) ? $wpssw_image_array[0] : '';
							}
						}
					}
					if ( $wpssw_header_type ) {
						$wpssw_value[] = '=IMAGE("' . $wpssw_image_src . '")';
					} else {
						if ( count( $wpssw_items ) > 1 ) {
							$wpssw_value[] = $wpssw_image_src;
						} else {
							$wpssw_value[] = '=IMAGE("' . $wpssw_image_src . '")';
						}
					}
					continue;
				}
				if ( 'Order Item ID' === (string) $wpssw_headers_name ) {
					$wpssw_value[] = $wpssw_id;
					continue;
				}
				if ( 'Product Weight' === (string) $wpssw_headers_name ) {
					$wpssw_product_id = $wpssw_item['product_id'] ? $wpssw_item['product_id'] : '';
					if ( $wpssw_item['variation_id'] > 0 ) {
						$wpssw_product_id = $wpssw_item['variation_id'] ? $wpssw_item['variation_id'] : '';
					}
					$wpssw_product = wc_get_product( $wpssw_product_id );
					if($wpssw_product && !is_wp_error($wpssw_product)){
						$weight        = $wpssw_product->get_weight();	
					}else{
						$weight = 0;
					}					
					$wpssw_value[] = $weight ? $weight : 0;
					continue;
				}
			}
			return $wpssw_value;
		}
	}
	new WPSSW_Default();
endif;
