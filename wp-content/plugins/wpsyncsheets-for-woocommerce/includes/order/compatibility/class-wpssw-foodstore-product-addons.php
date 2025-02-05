<?php
/**
 * Main WPSyncSheets_For_WooCommerce namespace.
 *
 * @since 1.0.0
 * @package wpsyncsheets-for-woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }
if ( ! class_exists( 'WPSSW_FoodStore_Product_Addons' ) ) :
	/**
	 * Class WPSSW_FoodStore_Product_Addons.
	 */
	class WPSSW_FoodStore_Product_Addons extends WPSSW_Order_Utils {
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
		 * Check if WooCommerce Product Add-Ons plugin is active or not.
		 */
		public static function wpssw_is_pugin_active() {
			if ( class_exists( 'FoodStore' ) ) {
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
				$headers['WPSSW_FoodStore_Product_Addons'] = self::$wpssw_headers;
			}
			return $headers;
		}
		/**
		 * Prepare Header List.
		 */
		protected function prepare_headers() {
			$headers             = array(
				'_addon_items'      => 'Addon Items',
				'_special_note'     => 'Special Note',
				'_wfs_service_type' => 'Service Type',
				'_wfs_service_date' => 'Service Date',
				'_wfs_service_time' => 'Service Time',
			);
			self::$wpssw_headers = $headers;
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
			$wpssw_value        = array();
			$wpssw_items        = $wpssw_order->get_items();
			$wpssw_oredrid      = $wpssw_order->get_id();
			$hpos_order_enabled = WPSSW_Setting::wpssw_check_hpos_order_setting_enabled();
			if ( in_array( $wpssw_headers_name, array( 'Service Type', 'Service Date', 'Service Time' ), true ) ) {
				if ( $hpos_order_enabled ) {
					if ( 'Service Type' === (string) $wpssw_headers_name ) {
						$wpssw_value[] = $wpssw_order->get_meta( '_wfs_service_type', true );
					} elseif ( 'Service Date' === (string) $wpssw_headers_name ) {
						$service_date          = $wpssw_order->get_meta( '_wfs_service_date', true );
						$formated_service_date = ! empty( $service_date ) ? gmdate( WPSSW_Setting::wpssw_option( 'date_format' ), strtotime( $service_date ) ) : '';
						$wpssw_value[]         = $formated_service_date;
					} elseif ( 'Service Time' === (string) $wpssw_headers_name ) {
						$service_time          = $wpssw_order->get_meta( '_wfs_service_time', true );
						$formated_service_time = ! empty( $service_time ) ? gmdate( WPSSW_Setting::wpssw_option( 'time_format' ), strtotime( $service_time ) ) : '';
						$wpssw_value[]         = $formated_service_time;
					}
				} else {
					if ( 'Service Type' === (string) $wpssw_headers_name ) {
						$wpssw_value[] = get_post_meta( $wpssw_oredrid, '_wfs_service_type', true );
					} elseif ( 'Service Date' === (string) $wpssw_headers_name ) {
						$service_date          = get_post_meta( $wpssw_oredrid, '_wfs_service_date', true );
						$formated_service_date = ! empty( $service_date ) ? gmdate( WPSSW_Setting::wpssw_option( 'date_format' ), strtotime( $service_date ) ) : '';
						$wpssw_value[]         = $formated_service_date;
					} elseif ( 'Service Time' === (string) $wpssw_headers_name ) {
						$service_time          = get_post_meta( $wpssw_oredrid, '_wfs_service_time', true );
						$formated_service_time = ! empty( $service_time ) ? gmdate( WPSSW_Setting::wpssw_option( 'time_format' ), strtotime( $service_time ) ) : '';
						$wpssw_value[]         = $formated_service_time;
					}
				}
			} else {
				foreach ( $wpssw_items as $item_id => $wpssw_item ) {
					$wpssw_metaval = '';
					if ( 'Addon Items' === (string) $wpssw_headers_name ) {
						$wpssw_addon_items = wc_get_order_item_meta( $item_id, '_addon_items', true );
						if ( ! empty( $wpssw_addon_items ) ) {
							foreach ( $wpssw_addon_items as $key => $addons ) {
								if ( isset( $addons['addon_item']['value'] ) && ! empty( isset( $addons['addon_item']['value'] ) ) ) {
									$addon_item_name = $addons['addon_item']['value'];
									$addon_item      = get_term_by( 'slug', $addon_item_name, 'product_addon' );
									$addon_quantity  = isset( $addons['quantity'] ) ? $addons['quantity'] : 1;
									if ( ! empty( $addon_item ) ) {
										$addon_price    = isset( $addons['price'] ) ? $addons['price'] : 0;
										$wpssw_metaval .= $addon_item->name . ' - ' . $addon_price . '(' . $addon_quantity . ') | ';
									}
								}
							}
						}
						$wpssw_metaval = rtrim( $wpssw_metaval, ' |' );
						$wpssw_value[] = $wpssw_metaval;
					} elseif ( 'Special Note' === (string) $wpssw_headers_name ) {
						$wpssw_service_note = '';
						$wpssw_service_note = wc_get_order_item_meta( $item_id, '_special_note', true );
						$wpssw_value[]      = $wpssw_service_note;
					}
				}
			}
			return $wpssw_value;
		}
	}
	new WPSSW_FoodStore_Product_Addons();
endif;
