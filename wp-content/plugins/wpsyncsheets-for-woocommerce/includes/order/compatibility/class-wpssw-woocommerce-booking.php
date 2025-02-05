<?php
/**
 * Main WPSyncSheets_For_WooCommerce namespace.
 *
 * @since 1.0.0
 * @package wpsyncsheets-for-woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }
if ( ! class_exists( 'WPSSW_WooCommerce_Booking' ) ) :
	/**
	 * Class WPSSW_WooCommerce_Booking.
	 */
	class WPSSW_WooCommerce_Booking extends WPSSW_Order_Utils {
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
		 * Check if WooCommerce Booking plugin is active or not.
		 */
		public static function wpssw_is_pugin_active() {
			if ( ! class_exists( 'WC_Bookings' ) ) {
				return false;
			}
			return true;
		}
		/**
		 * Prepare Header List.
		 */
		protected function prepare_headers() {
			self::$wpssw_headers = array( 'Booking Start Date', 'Booking End Date', 'Booking Resource', 'Booking Cost', 'Booking Status', 'Booking Persons', 'Booking Start Time', 'Booking End Time' );
		}
		/**
		 * Get Header List.
		 *
		 * @param array $headers .
		 */
		public static function get_header_list( $headers = array() ) {
			if ( ! empty( self::$wpssw_headers ) ) {
				$headers['WPSSW_WooCommerce_Booking'] = self::$wpssw_headers;
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
			$wpssw_items = $wpssw_order->get_items();
			/*WooCommerce Booking*/
			$wpssw_b_count      = 0;
			$wpssw_bookingquery = new WP_Query(
				array(
					'post_parent'    => (int) $wpssw_order->get_id(),
					'post_type'      => 'wc_booking',
					'posts_per_page' => -1,
					'post_status'    => 'Any',
					'fields'         => 'ids', // Fetch only ids.
				)
			);

			$wpssw_bkids = array();
			if ( ! empty( $wpssw_bookingquery->posts ) ) {
				$wpssw_bkids = $wpssw_bookingquery->posts;
				$wpssw_bkids = array_reverse( $wpssw_bkids );
				$wpssw_bkids = array_values( $wpssw_bkids );
			}

			$wpssw_booking_start_date = '';
			$wpssw_booking_end_date   = '';
			$wpssw_booking_resource   = '';
			$wpssw_booking_cost       = '';
			$wpssw_booking_status     = '';
			$wpssw_booking_persons    = '';
			$wpssw_booking_start_time = '';
			$wpssw_booking_end_time   = '';
			$bkcount                  = 0;
			$product_list             = array();
			$wpssw_product            = '';
			foreach ( $wpssw_items as $wpssw_id => $wpssw_item ) {
				$wpssw_booking_persons = '';
				$wpssw_product         = '';
				if ( $wpssw_item['variation_id'] ) {
					$wpssw_product = wc_get_product( $wpssw_item['variation_id'] );
				} elseif ( $wpssw_item['product_id'] ) {
					$wpssw_product = wc_get_product( $wpssw_item['product_id'] );
				}
				if ( ( ! empty( $wpssw_product ) && ( $wpssw_product->is_type( 'accommodation-booking' ) || $wpssw_product->is_type( 'booking' ) ) || empty( $wpssw_product ) ) && ! empty( $wpssw_bkids ) && isset( $wpssw_bkids[ $bkcount ] ) ) {
					$wpssw_bkid    = $wpssw_bkids[ $bkcount ];
					$wpssw_booking = get_wc_booking( $wpssw_bkid );

					$wpssw_booking_start_date = date_i18n( WPSSW_Setting::wpssw_option( 'date_format' ), $wpssw_booking->start );
					$wpssw_booking_end_date   = date_i18n( WPSSW_Setting::wpssw_option( 'date_format' ), $wpssw_booking->end );
					$wpssw_booking_start_time = date_i18n( 'H:i', $wpssw_booking->start ) . ', ';
					$wpssw_booking_end_time   = date_i18n( 'H:i', $wpssw_booking->end ) . ', ';
					$wpssw_booking_cost       = $wpssw_booking->cost;
					$wpssw_booking_status     = $wpssw_booking->status;
					if ( isset( $wpssw_booking->persons ) && ! empty( $wpssw_booking->persons ) ) {
						foreach ( $wpssw_booking->persons as $wpssw_bkey => $wpssw_bval ) {
							if ( $wpssw_bkey ) {
								$wpssw_booking_persons .= get_the_title( $wpssw_bkey ) . ' : ' . $wpssw_bval . ', ';
							} else {
								$wpssw_booking_persons .= $wpssw_bval . ', ';
							}
						}
					} else {
						$wpssw_booking_persons = 0;
					}
					if ( ! empty( $wpssw_booking->resource_id ) ) {
						$wpssw_booking_resource = get_the_title( $wpssw_booking->resource_id );
					}
						$bkcount++;

					if ( 'Booking Start Date' === (string) $wpssw_headers_name ) {
						$wpssw_value[] = $wpssw_booking_start_date;
					}
					if ( 'Booking End Date' === (string) $wpssw_headers_name ) {
						$wpssw_value[] = $wpssw_booking_end_date;
					}
					if ( 'Booking Resource' === (string) $wpssw_headers_name ) {
						$wpssw_value[] = $wpssw_booking_resource;
					}
					if ( 'Booking Cost' === (string) $wpssw_headers_name ) {
						$wpssw_value[] = $wpssw_booking_cost;
					}
					if ( 'Booking Status' === (string) $wpssw_headers_name ) {
						$wpssw_value[] = ucfirst( $wpssw_booking_status );
					}
					if ( 'Booking Persons' === (string) $wpssw_headers_name ) {
						$wpssw_value[] = rtrim( trim( $wpssw_booking_persons ), ',' );
					}
					if ( 'Booking Start Time' === (string) $wpssw_headers_name ) {
						$wpssw_value[] = rtrim( trim( $wpssw_booking_start_time ), ',' );
					}
					if ( 'Booking End Time' === (string) $wpssw_headers_name ) {
						$wpssw_value[] = rtrim( trim( $wpssw_booking_end_time ), ',' );
					}
					$wpssw_b_count++;
				} else {
					if ( 'Booking Start Date' === (string) $wpssw_headers_name ) {
						$wpssw_value[] = '';
					}
					if ( 'Booking End Date' === (string) $wpssw_headers_name ) {
						$wpssw_value[] = '';
					}
					if ( 'Booking Resource' === (string) $wpssw_headers_name ) {
						$wpssw_value[] = '';
					}
					if ( 'Booking Cost' === (string) $wpssw_headers_name ) {
						$wpssw_value[] = '';
					}
					if ( 'Booking Status' === (string) $wpssw_headers_name ) {
						$wpssw_value[] = '';
					}
					if ( 'Booking Persons' === (string) $wpssw_headers_name ) {
						$wpssw_value[] = '';
					}
					if ( 'Booking Start Time' === (string) $wpssw_headers_name ) {
						$wpssw_value[] = '';
					}
					if ( 'Booking End Time' === (string) $wpssw_headers_name ) {
						$wpssw_value[] = '';
					}
				}
			}
			return $wpssw_value;
		}
	}
	new WPSSW_WooCommerce_Booking();
endif;
