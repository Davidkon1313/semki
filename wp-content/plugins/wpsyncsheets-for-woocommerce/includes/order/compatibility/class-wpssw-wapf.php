<?php
/**
 * Main WPSyncSheets_For_WooCommerce namespace.
 *
 * @since 1.0.0
 * @package wpsyncsheets-for-woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }
if ( ! class_exists( 'WPSSW_WAPF' ) ) :
	/**
	 * Class WPSSW_WAPF.
	 */
	class WPSSW_WAPF extends WPSSW_Order_Utils {
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
			if ( class_exists( 'SW_WAPF\\WAPF' ) || class_exists( 'SW_WAPF_PRO\\WAPF' ) ) {
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
				$headers['WPSSW_WAPF'] = self::$wpssw_headers;
			}
			return $headers;
		}
		/**
		 * Prepare Header List.
		 */
		protected function prepare_headers() {

			$args    = array(
				'numberposts'            => -1,
				'post_type'              => 'wapf_product',
				'posts_per_page'         => -1,
				'post_status'            => 'publish',
				'update_post_meta_cache' => false,
			);
			$posts   = get_posts( $args );
			$headers = array();
			foreach ( $posts as $post ) {
				$unserialized = maybe_unserialize( $post->post_content );
				foreach ( $unserialized['fields'] as $field ) {
					$headers[] = $field['label'];
				}
			}

			global $wpdb;
			$table_name = $wpdb->prefix;
			$meta_key   = '_wapf_fieldgroup';
			// @codingStandardsIgnoreStart.
			$wpssw_querystr  = "SELECT {$wpdb->prefix}posts.* FROM {$wpdb->prefix}posts INNER JOIN {$wpdb->prefix}postmeta ON ( {$wpdb->prefix}posts.ID = {$wpdb->prefix}postmeta.post_id ) WHERE 1=1 AND ( {$wpdb->prefix}postmeta.meta_key = '_wapf_fieldgroup' )"; //db call ok.
			$wpssw_postsmeta = $wpdb->get_results( $wpssw_querystr, ARRAY_A );
			if( is_array( $wpssw_postsmeta  ) ){
				foreach ($wpssw_postsmeta as $key => $value) {
					$product_field_group = get_post_meta($value['ID'],'_wapf_fieldgroup', true);
					foreach($product_field_group['fields'] as $field) {
	                	$headers[] = $field['label'];
	                }
				}
			}
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
			$wpssw_value = array();

			$wpssw_items = $wpssw_order->get_items();
			foreach ( $wpssw_items as $item_id => $wpssw_item ) {
				$wpssw_metadata = $wpssw_item->get_formatted_meta_data();
				$wpssw_metaval  = '';
				foreach ( $wpssw_metadata as $wpssw_meta ) {
					if ( strtolower( $wpssw_meta->key ) === strtolower( $wpssw_headers_name ) ) {
						$wpssw_metaval .= $wpssw_meta->value . ',';
					}
				}
				$wpssw_metaval = rtrim( $wpssw_metaval, ',' );
				if ( is_array( $wpssw_metaval ) ) {
					$wpssw_value[] = implode( ',', $wpssw_metaval );
				} else {
					$wpssw_value[] = $wpssw_metaval;
				}
			}
			return $wpssw_value;
		}
	}
	new WPSSW_WAPF();
endif;
