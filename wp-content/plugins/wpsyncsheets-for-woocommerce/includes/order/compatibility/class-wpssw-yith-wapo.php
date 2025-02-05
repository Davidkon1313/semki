<?php
/**
 * Main WPSyncSheets_For_WooCommerce namespace.
 *
 * @since 1.0.0
 * @package wpsyncsheets-for-woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }
if ( ! class_exists( 'WPSSW_YITH_WAPO' ) ) :
	/**
	 * Class WPSSW_YITH_WAPO.
	 */
	class WPSSW_YITH_WAPO extends WPSSW_Order_Utils {
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
		 * Check if YITH WooCommerce Product Add-Ons plugin is active or not.
		 */
		public static function wpssw_is_pugin_active() {
			if ( defined( 'YITH_WAPO' ) ) {
				return true;
			}
			return false;
		}
		/**
		 * Prepare Header List.
		 */
		protected function prepare_headers() {
			$headers = array();

			// Check if headers are already cached.
			$cached_headers = wp_cache_get( 'wpssw_yith_wapo_headers', 'wpssw_orders_yith_wapo_headers' );
			if ( false !== $cached_headers ) {
				self::$wpssw_headers = $cached_headers;
				return;
			}

			global $wpdb;
			/*YITH*/
			$types_table_name  = $wpdb->prefix . 'yith_wapo_types';
			$addons_table_name = $wpdb->prefix . 'yith_wapo_addons';

			if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $types_table_name ) ) === $types_table_name ) {
		        // @codingStandardsIgnoreStart.
		        $wpssw_rows = $wpdb->get_results( $wpdb->prepare("SELECT * FROM {$wpdb->prefix}yith_wapo_types WHERE del='0' ORDER BY priority ASC") ); //db call ok.
		        // @codingStandardsIgnoreEnd.

				if ( ! empty( $wpssw_rows ) ) {
					foreach ( $wpssw_rows as $wpssw_key => $wpssw_value ) {
						if ( 'labels' === $wpssw_value->type || 'multiple_labels' === $wpssw_value->type ) {
							continue;
						}

						if ( ! in_array( $wpssw_value->type, array( 'checkbox', 'select', 'radio' ), true ) ) {
							if ( ! empty( $wpssw_value->options ) ) {
								$coloroption = maybe_unserialize( $wpssw_value->options );
								if ( ! empty( $coloroption['label'] ) ) {
									$headers = array_merge( $headers, $coloroption['label'] );
								}
							}
						} else {
							if ( ! empty( $wpssw_value->options ) ) {
								if ( ! empty( $wpssw_value->label ) ) {
									$headers[] = $wpssw_value->label;
								}
							}
						}
					}
				}
			} elseif ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $addons_table_name ) ) === $addons_table_name ) {
		        // @codingStandardsIgnoreStart.
		        $wpssw_rows = $wpdb->get_results( $wpdb->prepare("SELECT * FROM {$wpdb->prefix}yith_wapo_addons WHERE visibility='1' ORDER BY priority ASC") ); //db call ok.
		        // @codingStandardsIgnoreEnd.

				if ( ! empty( $wpssw_rows ) ) {
					foreach ( $wpssw_rows as $wpssw_key => $wpssw_value ) {
						$settings = maybe_unserialize( $wpssw_value->settings );
						$type     = $settings['type'];
						if ( 'labels' === (string) $type || 'multiple_labels' === (string) $type ) {
							continue;
						}

						$title = $settings['title'];
						if ( isset( $settings['title_in_cart'] ) && 'no' === (string) $settings['title_in_cart'] ) {
							$title = isset( $settings['title_in_cart_opt'] ) ? $settings['title_in_cart_opt'] : '';
						}

						if ( empty( $title ) ) {
							continue;
						}

						$headers[] = $title;
					}
				}
			}

			// Store headers in cache.
			wp_cache_set( 'wpssw_yith_wapo_headers', $headers, 'wpssw_orders_yith_wapo_headers', 60 ); // Adjust the expiration time (60 seconds = 1 min).

			self::$wpssw_headers = $headers;
		}
		/**
		 * Get Header List.
		 *
		 * @param array $headers .
		 */
		public static function get_header_list( $headers = array() ) {
			if ( ! empty( self::$wpssw_headers ) ) {
				$headers['WPSSW_YITH_WAPO'] = self::$wpssw_headers;
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
			foreach ( $wpssw_items as $wpssw_item ) {
				$wpssw_metadata    = $wpssw_item->get_formatted_meta_data();
				$wpssw_yithmetaval = '';
				foreach ( $wpssw_metadata as $wpssw_meta ) {
					if ( strtolower( $wpssw_meta->key ) === strtolower( $wpssw_headers_name ) ) {
						$wpssw_yithmetaval .= html_entity_decode( wp_strip_all_tags( $wpssw_meta->value ) ) . ',';
					}
				}
				$wpssw_yithmetaval = rtrim( $wpssw_yithmetaval, ',' );
				if ( is_array( $wpssw_yithmetaval ) ) {
					$wpssw_value[] = implode( ',', $wpssw_yithmetaval );
				} else {
					$wpssw_value[] = $wpssw_yithmetaval;
				}
			}
			return $wpssw_value;
		}
	}
	new WPSSW_YITH_WAPO();
endif;
