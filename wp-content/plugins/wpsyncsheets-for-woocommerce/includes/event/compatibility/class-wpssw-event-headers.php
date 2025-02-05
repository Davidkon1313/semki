<?php
/**
 * Main WPSyncSheets_For_WooCommerce namespace.
 *
 * @since 1.0.0
 * @package wpsyncsheets-for-woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }
if ( ! class_exists( 'WPSSW_Event_Headers' ) ) :
	/**
	 * Class WPSSW_Event_Headers.
	 */
	class WPSSW_Event_Headers extends WPSSW_Event_Utils {
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
				add_filter( 'wpsyncsheets_event_headers', __CLASS__ . '::get_header_list', 10, 1 );
			}
		}
		/**
		 * Check if Flexible Checkout Fields for WooCommerce Plugin is active or not.
		 */
		public static function wpssw_is_pugin_active() {
			if ( ! class_exists( 'Tribe__Tickets_Plus__Main' ) || ! class_exists( 'Tribe__Events__Pro__Main' ) ) {
				return false;
			}
			return true;
		}
		/**
		 * Prepare Header List.
		 */
		protected function prepare_headers() {
			self::$wpssw_headers = array( 'Event Id', 'Event Title', 'Event Start Date', 'Event End Date', 'Event Series', 'Event Location Venue', 'Organiser Name', 'Event Website URL' );
		}
		/**
		 * Get Header List.
		 *
		 * @param array $headers .
		 */
		public static function get_header_list( $headers = array() ) {
			if ( ! empty( self::$wpssw_headers ) ) {
				$headers['WPSSW_Event_Headers'] = self::$wpssw_headers;
			}
			return $headers;
		}
		/**
		 * Get Value for given header name.
		 *
		 * @param string $wpssw_headers_name Header name.
		 * @param object $wpssw_order order object.
		 */
		public static function get_value( $wpssw_headers_name, $wpssw_order ) {
			return self::prepare_value( $wpssw_headers_name, $wpssw_order );
		}
		/**
		 * Prepare Value for given header name.
		 *
		 * @param string $wpssw_headers_name Header name.
		 * @param object $wpssw_order order object.
		 */
		public static function prepare_value( $wpssw_headers_name, $wpssw_order ) {
			$wpssw_value                     = array();
			$wpssw_items                     = $wpssw_order->get_items();
			$wpssw_date_format               = apply_filters( 'wpssw_date_format', WPSSW_Setting::wpssw_option( 'date_format' ) . ' ' . WPSSW_Setting::wpssw_option( 'time_format' ) );
			$wpssw_event_spreadsheet_setting = WPSSW_Setting::wpssw_option( 'wpssw_event_spreadsheet_setting' );
			if ( 'yes' === (string) $wpssw_event_spreadsheet_setting ) {
				$wpssw_header_type = WPSSW_Order::wpssw_is_productwise();
				foreach ( $wpssw_items as $wpssw_item ) {

					$wpssw_subvalue    = '';
					$wpssw_ticket      = wc_get_product( $wpssw_item['product_id'] );
					$wpssw_tickettitle = $wpssw_item['name'];
					$wpssw_ticket_meta = get_post_meta( $wpssw_item['product_id'] );

					if ( isset( $wpssw_ticket_meta['_tribe_wooticket_for_event'] ) ) {
						$wpssw_eventid = $wpssw_ticket_meta['_tribe_wooticket_for_event'][0];

						$wpssw_eventdata  = get_post( $wpssw_eventid );
						$wpssw_meta_value = get_post_meta( $wpssw_eventid );

						if ( 'Event Id' === (string) $wpssw_headers_name ) {
							$wpssw_subvalue = $wpssw_eventid;
							$wpssw_value[]  = self::wpssw_check_is_array( $wpssw_subvalue );
							continue;
						}
						if ( 'Event Title' === (string) $wpssw_headers_name ) {
							$wpssw_subvalue = $wpssw_eventdata->post_title;
							$wpssw_value[]  = self::wpssw_check_is_array( $wpssw_subvalue );
							continue;
						}
						if ( 'Event Start Date' === (string) $wpssw_headers_name ) {
							if ( isset( $wpssw_meta_value['_EventStartDate'] ) ) {
								$wpssw_subvalue = $wpssw_meta_value['_EventStartDate'][0];
								if ( ! empty( $wpssw_subvalue ) ) {
									$wpssw_subvalue = gmdate( $wpssw_date_format, strtotime( $wpssw_subvalue ) );
								}
							}
							$wpssw_value[] = self::wpssw_check_is_array( $wpssw_subvalue );
							continue;
						}
						if ( 'Event End Date' === (string) $wpssw_headers_name ) {
							if ( isset( $wpssw_meta_value['_EventEndDate'] ) ) {
								$wpssw_subvalue = $wpssw_meta_value['_EventEndDate'][0];
								if ( ! empty( $wpssw_subvalue ) ) {
									$wpssw_subvalue = gmdate( $wpssw_date_format, strtotime( $wpssw_subvalue ) );
								}
							}
							$wpssw_value[] = self::wpssw_check_is_array( $wpssw_subvalue );
							continue;
						}
						if ( 'Event Series' === (string) $wpssw_headers_name ) {
							if ( isset( $wpssw_meta_value['_EventRecurrence'] ) ) {
								$wpssw_series = $wpssw_meta_value['_EventRecurrence'][0];

								$wpssw_unserialize_series = maybe_unserialize( $wpssw_series );
								$start_date               = $wpssw_meta_value['_EventStartDate'][0];
								$rules                    = $wpssw_unserialize_series['rules'];
								$rule_textarray           = array();
								foreach ( $rules as $rule ) {
									$rule_textarray[] = Tribe__Events__Pro__Recurrence__Meta::recurrenceToText( $rule, $start_date, $wpssw_eventid );
								}

								$wpssw_subvalue = implode( ',', $rule_textarray );
							}
							$wpssw_value[] = self::wpssw_check_is_array( $wpssw_subvalue );
							continue;
						}
						if ( 'Event Location Venue' === (string) $wpssw_headers_name ) {
							if ( isset( $wpssw_meta_value['_EventVenueID'] ) ) {
								$wpssw_vanueid = $wpssw_meta_value['_EventVenueID'][0];
								$wpssw_vanue   = get_post( $wpssw_vanueid );

								$wpssw_subvalue = $wpssw_vanue->post_title;
							}
							$wpssw_value[] = self::wpssw_check_is_array( $wpssw_subvalue );
							continue;
						}
						if ( 'Organiser Name' === (string) $wpssw_headers_name ) {
							if ( isset( $wpssw_meta_value['_EventOrganizerID'] ) ) {
								$wpssw_organizer_title = array();
								if ( is_array( $wpssw_meta_value['_EventOrganizerID'] ) ) {
									foreach ( $wpssw_meta_value['_EventOrganizerID'] as $organizerid ) {
										$wpssw_organizerid       = $organizerid;
										$wpssw_organizer         = get_post( $wpssw_organizerid );
										$wpssw_organizer_title[] = $wpssw_organizer->post_title;
									}
								}
								$wpssw_subvalue = $wpssw_organizer_title;
							}
							$wpssw_value[] = self::wpssw_check_is_array( $wpssw_subvalue );
							continue;
						}
						if ( 'Event Website URL' === (string) $wpssw_headers_name ) {
							if ( isset( $wpssw_meta_value['_EventURL'] ) ) {
								$wpssw_subvalue = $wpssw_meta_value['_EventURL'][0];
							}
							$wpssw_value[] = self::wpssw_check_is_array( $wpssw_subvalue );
							continue;
						}
					}
					$wpssw_value[] = '';
				}
				if ( $wpssw_header_type ) {
					return $wpssw_value;
				} else {
					$wpssw_ordervalue[] = implode( ',', $wpssw_value );
					return $wpssw_ordervalue;
				}
			} else {
				$wpssw_value[] = '';
				return $wpssw_value;
			}
		}
		/**
		 * Check value is array or not
		 *
		 * @param string|array $wpssw_value value to check if it is array or not.
		 * @return string
		 */
		public static function wpssw_check_is_array( $wpssw_value ) {
			if ( is_array( $wpssw_value ) ) {
				$wpssw_order_value = implode( ',', $wpssw_value );
				return $wpssw_order_value;
			}
			return ucfirst( $wpssw_value );
		}
	}
	new WPSSW_Event_Headers();
endif;
