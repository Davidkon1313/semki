<?php
/**
 * Main WPSyncSheets_For_WooCommerce namespace.
 *
 * @since 1.0.0
 * @package wpsyncsheets-for-woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }
if ( ! class_exists( 'WPSSW_Tour_Travel_Booking_Manager' ) ) :
	/**
	 * Class WPSSW_Tour_Travel_Booking_Manager.
	 */
	class WPSSW_Tour_Travel_Booking_Manager extends WPSSW_Order_Utils {
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
		 * Check if WooCommerce Pdf Plugin is active or not.
		 */
		public static function wpssw_is_pugin_active() {
			if ( ! class_exists( 'TTBM_Woocommerce_Plugin_Pro' ) ) {
				return false;
			}
			return true;
		}
		/**
		 * Prepare Header List.
		 */
		protected function prepare_headers() {
			self::$wpssw_headers = array( 'Customer Details 1', 'Customer Details 2', 'Customer Details 3', 'Customer Details 4', 'Customer Details 5' );
		}
		/**
		 * Get Header List.
		 *
		 * @param array $headers .
		 */
		public static function get_header_list( $headers = array() ) {
			if ( ! empty( self::$wpssw_headers ) ) {
				$headers['WPSSW_Tour_Travel_Booking_Manager'] = self::$wpssw_headers;
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

			if ( $wpssw_order ) {
				// Loop through order items.
				$all_meta_groups = array();
				foreach ( $wpssw_order->get_items() as $item_id => $item ) {
					// Get product details.
					$product_name     = $item->get_name();
					$product_quantity = $item->get_quantity();
					// Initialize an array to store grouped metadata.
					$grouped_meta = array(
						'Nome'        => '',
						'Cognome'     => '',
						'Telefono'    => '',
						'Partenza da' => '',
					);

					$item_meta_data = array();
					// Get all metadata for the item.
					$meta_data = $item->get_meta_data();

					// Group metadata by key.
					foreach ( $meta_data as $meta ) {
						$meta_key   = $meta->key;
						$meta_value = $meta->value;

						if ( ! isset( $item_meta_data[ $meta_key ] ) ) {
							$item_meta_data[ $meta_key ] = array();
						}
						$item_meta_data[ $meta_key ][] = $meta_value;
					}
					// Combine related keys into groups.
					$num_entries = isset( $item_meta_data['Nome'] ) ? count( $item_meta_data['Nome'] ) : 0;
					for ( $i = 0; $i < $num_entries; $i++ ) {
						$group['Nome']        = isset( $item_meta_data['Nome'][ $i ] ) ? 'Nome: ' . $item_meta_data['Nome'][ $i ] : '';
						$group['Cognome']     = isset( $item_meta_data['Cognome'][ $i ] ) ? 'Cognome: ' . $item_meta_data['Cognome'][ $i ] : '';
						$group['Telefono']    = isset( $item_meta_data['Telefono'][ $i ] ) ? 'Telefono: ' . $item_meta_data['Telefono'][ $i ] : '';
						$group['Partenza da'] = isset( $item_meta_data['Partenza da'][ $i ] ) ? 'Partenza da: ' . $item_meta_data['Partenza da'][ $i ] : '';
						// Store the group.
						$all_meta_groups[] = $group;
					}
					// Remove duplicate entries.
					$unique_groups = array_map( 'unserialize', array_unique( array_map( 'serialize', $all_meta_groups ) ) );
					$headers       = self::$wpssw_headers;
					foreach ( $headers as $index => $header ) {
						if ( $header === (string) $wpssw_headers_name && isset( $unique_groups[ $index ] ) ) {
							$wpssw_value[] = $unique_groups[ $index ];
							break; // Stop iterating after finding the matching header.
						}
					}
				}
			}
			return $wpssw_value;
		}
	}
	new WPSSW_Tour_Travel_Booking_Manager();
endif;

