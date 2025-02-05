<?php
/**
 * Main WPSyncSheets_For_WooCommerce namespace.
 *
 * @since 1.0.0
 * @package wpsyncsheets-for-woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }
if ( ! class_exists( 'WPSSW_TM_Extra_Product_Options' ) ) :
	/**
	 * Class WPSSW_TM_Extra_Product_Options.
	 */
	class WPSSW_TM_Extra_Product_Options extends WPSSW_Order_Utils {
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
		 * Check if WooCommerce Checkout Field Editor Pro – By ThemeHigh plugin is active or not.
		 */
		public static function wpssw_is_pugin_active() {
			if ( ! class_exists( 'TM_Extra_Product_Options' ) && ! class_exists( 'THEMECOMPLETE_Extra_Product_Options' ) ) {
				return false;
			}
			return true;
		}
		/**
		 * Prepare Header List.
		 */
		protected function prepare_headers() {
			$headers       = array();
			$wpssw_filterd = array();
			$wpssw_filterd = self::wpssw_get_product_option_field();
			if ( ! empty( $wpssw_filterd ) ) {
				$headers = $wpssw_filterd;
			}
			self::$wpssw_headers = $headers;
		}
		/**
		 * Get Header List.
		 *
		 * @param array $headers .
		 */
		public static function get_header_list( $headers = array() ) {
			if ( ! empty( self::$wpssw_headers ) ) {
				$headers['WPSSW_TM_Extra_Product_Options'] = self::$wpssw_headers;
			}
			return $headers;
		}
		/**
		 *
		 * WooCommerce TM Extra Product Options headers.
		 */
		public static function wpssw_get_product_option_field() {
			global $wpdb;
			$wpssw_meta_key = 'tm_meta';
			// @codingStandardsIgnoreStart.
			$wpssw_tm_metas = $wpdb->get_results( "SELECT pm.meta_value FROM $wpdb->postmeta as pm,$wpdb->posts as p WHERE pm.meta_key ='$wpssw_meta_key' AND pm.post_id=p.ID AND p.post_status = 'publish'" ); //db call ok.
			// @codingStandardsIgnoreEnd.
			$wpssw_tm_metas = maybe_unserialize( $wpssw_tm_metas );
			$wpssw_filterd  = array();
			foreach ( $wpssw_tm_metas as $wpssw_metas ) {
				$wpssw_field    = maybe_unserialize( $wpssw_metas->meta_value );
				$wpssw_is_value = false;
				$wpssw_is_key   = false;
				if ( isset( $wpssw_field['tmfbuilder'] ) ) {
					foreach ( $wpssw_field['tmfbuilder'] as $wpssw_key => $wpssw_val ) {
						if ( ( 'sections_uniqid' !== (string) $wpssw_key ) && ( 'header_uniqid' !== (string) $wpssw_key ) && ( 'divider_uniqid' !== (string) $wpssw_key ) && ( 'variations_uniqid' !== (string) $wpssw_key ) ) {
							if ( strpos( $wpssw_key, '_uniqid' ) > 0 ) {
								$wpssw_pairkey = $wpssw_val;
								$wpssw_is_key  = true;
							}
						}
						if ( ( 'sections_internal_name' !== (string) $wpssw_key ) && ( 'header_internal_name' !== (string) $wpssw_key ) && ( 'divider_internal_name' !== (string) $wpssw_key ) && ( 'variations_internal_name' !== (string) $wpssw_key ) ) {
							if ( preg_match( '/_header_title$/', $wpssw_key ) ) {
								$wpssw_val_count = count( $wpssw_val );
								for ( $i = 0; $i < $wpssw_val_count; $i++ ) {
									if ( empty( $wpssw_val[ $i ] ) ) {
										if ( isset( $wpssw_tempname[ $i ] ) ) {
											$wpssw_val[ $i ] = trim( $wpssw_tempname[ $i ] );
										} else {
											$wpssw_val[ $i ] = '';
										}
									}
								}
								$wpssw_pairvalue = $wpssw_val;
								$wpssw_is_value  = true;
							}
							if ( preg_match( '/_internal_name$/', $wpssw_key ) ) {
								$wpssw_tempname = $wpssw_val;
							}
						}
						if ( $wpssw_is_value && $wpssw_is_key ) {
							$wpssw_pairkey_count = count( $wpssw_pairkey );
							for ( $i = 0; $i < $wpssw_pairkey_count; $i++ ) {
								$wpssw_filterd[ $wpssw_pairkey[ $i ] ] = trim( $wpssw_pairvalue[ $i ] );
							}
							$wpssw_is_value = false;
							$wpssw_is_key   = false;
						}
					}
				}
			}
			return $wpssw_filterd;
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

			$wpssw_value   = array();
			$wpssw_filterd = self::wpssw_get_product_option_field();
			if ( empty( $wpssw_filterd ) ) {
				return $wpssw_value;
			}
			$wpssw_line_items = $wpssw_order->get_items( apply_filters( 'woocommerce_admin_order_item_types', 'line_item' ) );
			foreach ( $wpssw_line_items as $wpssw_item_id => $wpssw_item ) {
				$wpssw_item_meta = function_exists( 'wc_get_order_item_meta' ) ? wc_get_order_item_meta( $wpssw_item_id, '', false ) : $wpssw_order->get_item_meta( $wpssw_item_id );
				if ( isset( $wpssw_item_meta['_tmcartepo_data'] ) ) {
					// phpcs:ignore
					$wpssw_producttmdata = unserialize( $wpssw_item_meta['_tmcartepo_data'][0] );
				} else {
					$wpssw_producttmdata = array();
				}
				$wpssw_val = '';

				if ( ! is_array( $wpssw_producttmdata ) ) {
					$wpssw_producttmdata = array();
				}
				foreach ( $wpssw_producttmdata as $wpssw_pvalue ) {
					if ( false !== array_key_exists( $wpssw_pvalue['section'], $wpssw_filterd ) && $wpssw_filterd[ $wpssw_pvalue['section'] ] === $wpssw_headers_name ) {
						$wpssw_val .= $wpssw_pvalue['value'] . ',';
					}
				}
				$wpssw_val = rtrim( $wpssw_val, ',' );
				if ( is_array( $wpssw_val ) ) {
					$wpssw_value[] = implode( ',', $wpssw_val );
				} else {
					$wpssw_value[] = $wpssw_val;
				}
			}
			return $wpssw_value;
		}
	}
	new WPSSW_TM_Extra_Product_Options();
endif;
