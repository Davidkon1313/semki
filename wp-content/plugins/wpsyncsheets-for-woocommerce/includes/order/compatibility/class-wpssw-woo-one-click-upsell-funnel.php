<?php
/**
 * Main WPSyncSheets_For_WooCommerce namespace.
 *
 * @since 1.0.0
 * @package wpsyncsheets-for-woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }
if ( ! class_exists( 'WPSSW_Woo_One_Click_Upsell_Funnel' ) ) :
	/**
	 * Class WPSSW_Woo_One_Click_Upsell_Funnel.
	 */
	class WPSSW_Woo_One_Click_Upsell_Funnel extends WPSSW_Order_Utils {
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
			if ( ! function_exists( 'is_plugin_active' ) ) {
				require_once ABSPATH . '/wp-admin/includes/plugin.php';
			}
			if ( class_exists( 'Woocommerce_One_Click_Upsell_Funnel_Admin' ) || is_plugin_active( 'woo-one-click-upsell-funnel/woocommerce-one-click-upsell-funnel.php' ) ) {
				return true;
			}
			return false;
		}
		/**
		 * Prepare Header List.
		 */
		protected function prepare_headers() {
			self::$wpssw_headers = array( 'Upsell Order' );
		}
		/**
		 * Get Header List.
		 *
		 * @param array $headers .
		 */
		public static function get_header_list( $headers = array() ) {
			if ( ! empty( self::$wpssw_headers ) ) {
				$headers['WPSSW_Woo_One_Click_Upsell_Funnel'] = self::$wpssw_headers;
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
			if ( 'Upsell Order' === (string) $wpssw_headers_name ) {
				$hpos_order_enabled = WPSSW_Setting::wpssw_check_hpos_order_setting_enabled();
				if ( $hpos_order_enabled ) {
					$upsell_order = $wpssw_order->get_meta( 'wps_wocuf_upsell_order', true );
				} else {
					$upsell_order = get_post_meta( $wpssw_order->get_id(), 'wps_wocuf_upsell_order', true );
				}
				$wpssw_value[] = ( true === (bool) $upsell_order || 'true' === (string) $upsell_order ) ? 'Yes' : 'No';
			}
			return $wpssw_value;
		}
	}
	new WPSSW_Woo_One_Click_Upsell_Funnel();
endif;

