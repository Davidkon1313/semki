<?php
/**
 * Main WPSyncSheets_For_WooCommerce namespace.
 *
 * @since 1.0.0
 * @package wpsyncsheets-for-woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }

if ( ! class_exists( 'WPSSW_Custom_Product_Addons' ) ) :

	/**
	 * Class WPSSW_Custom_Product_Addons.
	 */
	class WPSSW_Custom_Product_Addons extends WPSSW_Product_Utils {

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
				add_filter( 'wpsyncsheets_product_headers', __CLASS__ . '::get_header_list', 10, 1 );
			}
		}

		/**
		 * Check if WooCommerce Custom Product Addons (Free) plugin is active or not.
		 */
		public static function wpssw_is_pugin_active() {
			if ( class_exists( 'WCPA_Form' ) ) {
				return true;
			}
			return false;
		}

		/**
		 * Get Header List.
		 *
		 * @param array $headers .
		 * @return array $headers
		 */
		public static function get_header_list( $headers = array() ) {
			if ( ! empty( self::$wpssw_headers ) ) {
				$headers['WPSSW_Custom_Product_Addons'] = self::$wpssw_headers;
			}
			return $headers;
		}

		/**
		 * Prepare Header List.
		 */
		protected function prepare_headers() {
			$headers = array();
			global $wpdb;
			$table_name  = $wpdb->prefix;
			$meta_key    = '_wcpa_fb-editor-data';
			$post_status = 'publish';
			// @codingStandardsIgnoreStart.
			$wpssw_querystr     = "SELECT {$wpdb->prefix}postmeta.* FROM {$wpdb->prefix}postmeta INNER JOIN {$wpdb->prefix}posts ON ( {$wpdb->prefix}posts.ID = {$wpdb->prefix}postmeta.post_id ) WHERE 1=1 AND ( {$wpdb->prefix}postmeta.meta_key = '_wcpa_fb-editor-data' ) AND {$wpdb->prefix}posts.post_status='publish'"; //db call ok.
			$wpssw_postsmeta    = $wpdb->get_results( $wpssw_querystr, ARRAY_A );
			// @codingStandardsIgnoreEnd.
			foreach ( $wpssw_postsmeta as $wpssw_meta ) {
				$wpssw_json_encoded = json_decode( $wpssw_meta['meta_value'] );
				if ( $wpssw_json_encoded && is_array( $wpssw_json_encoded ) ) {
					foreach ( $wpssw_json_encoded as $wpssw_field_label ) {
						if ( in_array( $wpssw_field_label->type, array( 'paragraph', 'header' ), true ) ) {
							continue;
						}
						if ( isset( $wpssw_field_label->label ) && ! empty( $wpssw_field_label->label ) ) {
							$headers[] = $wpssw_field_label->label;
						} elseif ( isset( $wpssw_field_label->name ) && ! empty( $wpssw_field_label->name ) ) {
							$headers[] = $wpssw_field_label->name;
						}
					}
				}
			}
			self::$wpssw_headers = $headers;
		}

		/**
		 * Get Value for given header name.
		 *
		 * @param string $wpssw_headers_name Header name.
		 * @param object $wpssw_product product object.
		 */
		public static function get_value( $wpssw_headers_name, $wpssw_product ) {
			return self::prepare_value( $wpssw_headers_name, $wpssw_product );
		}

		/**
		 * Prepare Value for given header name.
		 *
		 * @param string $wpssw_headers_name Header name.
		 * @param object $wpssw_product product object.
		 */
		public static function prepare_value( $wpssw_headers_name, $wpssw_product ) {
			$wpssw_value = '';
			if ( ! empty( $wpssw_product->get_meta( '_wcpa_product_meta' ) ) ) {
				$i = 0;
				foreach ( $wpssw_product->get_meta( '_wcpa_product_meta' ) as $product_form_id ) {
					foreach ( get_post_meta( $product_form_id, '_wcpa_fb-editor-data' ) as $post_meta ) {
						$wpssw_post_metas = json_decode( $post_meta );
					}
					$wpssw_post_metas_count = count( $wpssw_post_metas );
					for ( $x = 0;$x < $wpssw_post_metas_count; $x++ ) {

						$a = (array) $wpssw_post_metas[ $x ];

						$v = array_keys( $a, $wpssw_headers_name, true );

						if ( (string) $a['label'] === $wpssw_headers_name ) {

							$i = 1;

							$v1 = array_key_exists( 'values', $a );
							$v2 = array();
							if ( true === (bool) $v1 ) {
								foreach ( (array) $a['values'] as $values ) {
									$v2[] = $values->value;
								}
								$wpssw_value = implode( ', ', $v2 );
							} else {
								if ( ! empty( $a['value'] ) ) {
									$wpssw_value = $a['value'];
								} else {
									$wpssw_value = 'Yes';
								}
							}
							break;
						}
					}
				}
				if ( 0 === (int) $i ) {
					$wpssw_value = '';
				}
			}
			return $wpssw_value;
		}
	}
	new WPSSW_Custom_Product_Addons();
endif;
