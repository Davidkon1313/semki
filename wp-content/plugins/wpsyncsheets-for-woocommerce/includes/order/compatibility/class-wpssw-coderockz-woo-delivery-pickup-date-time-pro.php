<?php
/**
 * Main WPSyncSheets_For_WooCommerce namespace.
 *
 * @package wpsyncsheets-for-woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }
if ( ! class_exists( 'WPSSW_Coderockz_Woo_Delivery_Pickup_Date_Time_Pro' ) ) :
	/**
	 * Class WPSSW_Coderockz_Woo_Delivery_Pickup_Date_Time_Pro.
	 */
	class WPSSW_Coderockz_Woo_Delivery_Pickup_Date_Time_Pro extends WPSSW_Order_Utils {
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
		 * Check if WooCommerce Delivery & Pickup Date Time Pro By CodeRockz plugin is active or not.
		 */
		public static function wpssw_is_pugin_active() {
			if ( ! class_exists( 'Coderockz_Woo_Delivery' ) ) {
				return false;
			}
			return true;
		}
		/**
		 * Prepare Header List.
		 */
		protected function prepare_headers() {
			self::$wpssw_headers = self::wpssw_coderockz_delivery_date_headers();
		}
		/**
		 * Get Header List.
		 *
		 * @param array $headers .
		 */
		public static function get_header_list( $headers = array() ) {
			if ( ! empty( self::$wpssw_headers ) ) {
				$headers['WPSSW_Coderockz_Woo_Delivery_Pickup_Date_Time_Pro'] = self::$wpssw_headers;
			}
			return $headers;
		}
		/**
		 * WooCommerce Delivery & Pickup Date Time headers.
		 *
		 * @param bool $with_key check whether to return headers with key or without key.
		 */
		public static function wpssw_coderockz_delivery_date_headers( $with_key = false ) {

			$keys = array( 'delivery_type', 'delivery_date', 'delivery_time', 'pickup_date', 'pickup_time', 'delivery_pickup', 'delivery_status' );
			$val  = array( 'CR Delivery Type', 'CR Delivery Date', 'CR Delivery Time', 'CR Pickup Date', 'CR Pickup Time', 'CR Pickup Location', 'CR Delivery Status' );
			if ( $with_key ) {
				return array_combine( $keys, $val );
			}
			return $val;
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
			// WooCommerce Delivery & Pickup Date Time Pro By CodeRockz.
			$order_id                              = $wpssw_order->get_id();
			$wpssw_value                           = array();
			$wpssw_insert_val                      = '';
			$wpssw_coderockz_delivery_date_headers = self::wpssw_coderockz_delivery_date_headers( true );
			$key                                   = array_search( $wpssw_headers_name, $wpssw_coderockz_delivery_date_headers, true );

			if ( false !== $key ) {

				$wpssw_insert_val = $wpssw_order->get_meta( $key, true );

				if ( 'delivery_type' === (string) $key && '' !== (string) $wpssw_insert_val ) {
					$wpssw_value[] = ucfirst( $wpssw_insert_val );
					return $wpssw_value;
				}
				if ( 'pickup_date' === (string) $key && '' !== (string) $wpssw_insert_val ) {
					$pickup_date_settings = WPSSW_Setting::wpssw_option( 'coderockz_woo_delivery_pickup_date_settings', array() );

					$pickup_date_format = ( isset( $pickup_date_settings['date_format'] ) && ! empty( $pickup_date_settings['date_format'] ) ) ? $pickup_date_settings['date_format'] : 'F j, Y';

					$pickup_add_weekday_name = ( isset( $pickup_date_settings['add_weekday_name'] ) && ! empty( $pickup_date_settings['add_weekday_name'] ) ) ? $pickup_date_settings['add_weekday_name'] : false;

					if ( $pickup_add_weekday_name ) {
						$pickup_date_format = 'l ' . $pickup_date_format;
					}

					$wpssw_insert_val = date_i18n( $pickup_date_format, strtotime( $wpssw_insert_val ) );
					$wpssw_value[]    = $wpssw_insert_val;
					return $wpssw_value;
				}

				if ( 'pickup_time' === (string) $key && '' !== (string) $wpssw_insert_val ) {
					$pickup_time_settings = WPSSW_Setting::wpssw_option( 'coderockz_woo_delivery_pickup_settings', array() );

					$pickup_time_format = ( isset( $pickup_time_settings['time_format'] ) && ! empty( $pickup_time_settings['time_format'] ) ) ? $pickup_time_settings['time_format'] : '12';
					if ( 12 === (int) $pickup_time_format ) {
						$pickup_time_format = 'h:i A';
					} elseif ( 24 === (int) $pickup_time_format ) {
						$pickup_time_format = 'H:i';
					}

					$pickup_minutes = explode( ' - ', $wpssw_insert_val );

					if ( ! isset( $pickup_minutes[1] ) ) {
						$wpssw_insert_val = date_i18n( $pickup_time_format, strtotime( $pickup_minutes[0] ) );
					} else {
						$wpssw_insert_val = date_i18n( $pickup_time_format, strtotime( $pickup_minutes[0] ) ) . ' - ' . date_i18n( $pickup_time_format, strtotime( $pickup_minutes[1] ) );
					}
					$wpssw_value[] = $wpssw_insert_val;
					return $wpssw_value;
				}

				if ( 'delivery_date' === (string) $key && '' !== (string) $wpssw_insert_val ) {
					$delivery_date_settings = WPSSW_Setting::wpssw_option( 'coderockz_woo_delivery_date_settings', array() );

					$delivery_date_format = ( isset( $delivery_date_settings['date_format'] ) && ! empty( $delivery_date_settings['date_format'] ) ) ? $delivery_date_settings['date_format'] : 'F j, Y';
					$add_weekday_name     = ( isset( $delivery_date_settings['add_weekday_name'] ) && ! empty( $delivery_date_settings['add_weekday_name'] ) ) ? $delivery_date_settings['add_weekday_name'] : false;

					if ( $add_weekday_name ) {
						$delivery_date_format = 'l ' . $delivery_date_format;
					}

					$wpssw_insert_val = date_i18n( $delivery_date_format, strtotime( $wpssw_insert_val ) );
					$wpssw_value[]    = $wpssw_insert_val;
					return $wpssw_value;
				}

				if ( 'delivery_time' === (string) $key && '' !== (string) $wpssw_insert_val ) {
					$delivery_time_settings = WPSSW_Setting::wpssw_option( 'coderockz_woo_delivery_time_settings', array() );

					$time_format = ( isset( $delivery_time_settings['time_format'] ) && ! empty( $delivery_time_settings['time_format'] ) ) ? $delivery_time_settings['time_format'] : '12';
					if ( 12 === (int) $time_format ) {
						$time_format = 'h:i A';
					} elseif ( 24 === (int) $time_format ) {
						$time_format = 'H:i';
					}

					if ( 'as-soon-as-possible' === (string) $wpssw_insert_val ) {
						$as_soon_as_possible_text = ( isset( $delivery_time_settings['as_soon_as_possible_text'] ) && ! empty( $delivery_time_settings['as_soon_as_possible_text'] ) ) ? stripslashes( $delivery_time_settings['as_soon_as_possible_text'] ) : __( 'As Soon As Possible', 'wpssw' );
						$wpssw_insert_val         = $as_soon_as_possible_text;
					} else {
						$minutes = explode( ' - ', $wpssw_insert_val );

						if ( ! isset( $minutes[1] ) ) {
							$wpssw_insert_val = date_i18n( $time_format, strtotime( $minutes[0] ) );
						} else {
							$wpssw_insert_val = date_i18n( $time_format, strtotime( $minutes[0] ) ) . ' - ' . date_i18n( $time_format, strtotime( $minutes[1] ) );
						}
					}
					$wpssw_value[] = $wpssw_insert_val;
					return $wpssw_value;
				}
				if ( 'delivery_status' === (string) $key ) {
					$localization_settings = WPSSW_Setting::wpssw_option( 'coderockz_woo_delivery_localization_settings', array() );

					$delivery_status_not_delivered_text = ( isset( $localization_settings['delivery_status_not_delivered_text'] ) && ! empty( $localization_settings['delivery_status_not_delivered_text'] ) ) ? stripslashes( $localization_settings['delivery_status_not_delivered_text'] ) : __( 'Not Delivered', 'wpssw' );
					$delivery_status_delivered_text     = ( isset( $localization_settings['delivery_status_delivered_text'] ) && ! empty( $localization_settings['delivery_status_delivered_text'] ) ) ? stripslashes( $localization_settings['delivery_status_delivered_text'] ) : __( 'Delivery Completed', 'wpssw' );
					$pickup_status_not_picked_text      = ( isset( $localization_settings['pickup_status_not_picked_text'] ) && ! empty( $localization_settings['pickup_status_not_picked_text'] ) ) ? stripslashes( $localization_settings['pickup_status_not_picked_text'] ) : __( 'Not Picked', 'wpssw' );
					$pickup_status_picked_text          = ( isset( $localization_settings['pickup_status_picked_text'] ) && ! empty( $localization_settings['pickup_status_picked_text'] ) ) ? stripslashes( $localization_settings['pickup_status_picked_text'] ) : __( 'Pickup Completed', 'wpssw' );

					$delivery_type      = '';
					$hpos_order_enabled = WPSSW_Setting::wpssw_check_hpos_order_setting_enabled();
					if ( $hpos_order_enabled ) {
						$delivery_type = $wpssw_order->get_meta( 'delivery_type', true );
					} else {
						$delivery_type = get_post_meta( $order_id, 'delivery_type', true );
					}
					if ( 'delivered' === (string) $wpssw_insert_val ) {
						if ( 'pickup' === $delivery_type ) {
							$wpssw_insert_val = $pickup_status_picked_text;
						} else {
							$wpssw_insert_val = $delivery_status_delivered_text;
						}
					} else {
						if ( 'pickup' === $delivery_type ) {
							$wpssw_insert_val = $pickup_status_not_picked_text;
						} else {
							$wpssw_insert_val = $delivery_status_not_delivered_text;
						}
					}
					$wpssw_value[] = $wpssw_insert_val;
					return $wpssw_value;
				}
			}
			$wpssw_value[] = $wpssw_insert_val;
			return $wpssw_value;
		}
	}
	new WPSSW_Coderockz_Woo_Delivery_Pickup_Date_Time_Pro();
endif;
