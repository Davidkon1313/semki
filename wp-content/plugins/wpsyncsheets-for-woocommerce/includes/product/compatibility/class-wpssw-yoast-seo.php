<?php
/**
 * Main WPSyncSheets_For_WooCommerce namespace.
 *
 * @since 1.0.0
 * @package wpsyncsheets-for-woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }

if ( ! class_exists( 'WPSSW_Yoast_SEO' ) ) :

	/**
	 * Class WPSSW_Yoast_SEO.
	 */
	class WPSSW_Yoast_SEO extends WPSSW_Product_Utils {

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
		 * Check if Yoast SEO Plugin is active or not.
		 */
		public static function wpssw_is_pugin_active() {
			if ( class_exists( 'WPSEO_Admin' ) ) {
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
				$headers['WPSSW_Yoast_SEO'] = self::$wpssw_headers;
			}
			return $headers;
		}

		/**
		 * Prepare Header List.
		 */
		protected function prepare_headers() {
			$headers             = array(
				'_yoast_wpseo_title'    => 'Yoast SEO Title',
				'_yoast_wpseo_metadesc' => 'Yoast SEO Description',
				'_yoast_wpseo_focuskw'  => 'Yoast SEO Focus Keyword',
			);
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
			if ( 'variation' === (string) $wpssw_product->get_type() && in_array( $wpssw_headers_name, array( 'Yoast SEO Title', 'Yoast SEO Description', 'Yoast SEO Focus Keyword' ), true ) ) {
				return $wpssw_value;
			}
			if ( 'Yoast SEO Title' === (string) $wpssw_headers_name ) {

				$wpssw_value = WPSEO_Meta::get_value( 'title', $wpssw_product->get_id() );
				$post        = get_post( $wpssw_product->get_id() );
				if ( empty( $wpssw_value ) ) {
					$wpseo_titles = get_option( 'wpseo_titles', array() );
					$wpssw_value  = isset( $wpseo_titles[ 'title-' . $post->post_type ] ) ? $wpseo_titles[ 'title-' . $post->post_type ] : $post->post_title;
				}
				// @codingStandardsIgnoreStart.
				if ( isset( $_POST['yoast_wpseo_title'] ) && ! empty( sanitize_text_field( wp_unslash( $_POST['yoast_wpseo_title'] ) ) ) ) {
					$wpssw_value = sanitize_text_field( wp_unslash($_POST['yoast_wpseo_title']));
				}
				// @codingStandardsIgnoreEnd.
				$replacer = new WPSEO_Replace_Vars();
				$val      = $replacer->replace( $wpssw_value, $post );

				return $val;
			}
			if ( 'Yoast SEO Description' === (string) $wpssw_headers_name ) {
				$wpssw_value = $wpssw_product->get_meta( '_yoast_wpseo_metadesc' );
				// @codingStandardsIgnoreStart.
				if ( isset( $_POST['yoast_wpseo_metadesc'] ) && ! empty( sanitize_text_field( wp_unslash( $_POST['yoast_wpseo_metadesc'] ) ) ) ) {
					$wpssw_value = sanitize_text_field( wp_unslash($_POST['yoast_wpseo_metadesc']));
				}
				// @codingStandardsIgnoreEnd.
				return $wpssw_value;
			}
			if ( 'Yoast SEO Focus Keyword' === (string) $wpssw_headers_name ) {
				$wpssw_value = $wpssw_product->get_meta( '_yoast_wpseo_focuskw' );
				// @codingStandardsIgnoreStart.
				if ( isset( $_POST['yoast_wpseo_focuskw'] ) && ! empty( sanitize_text_field( wp_unslash( $_POST['yoast_wpseo_focuskw'] ) ) ) ) {
					$wpssw_value = sanitize_text_field( wp_unslash($_POST['yoast_wpseo_focuskw']));
				}
				// @codingStandardsIgnoreEnd.
				return $wpssw_value;
			}
			return $wpssw_value;
		}
	}
	new WPSSW_Yoast_SEO();
endif;
