<?php
/**
 * Main WPSyncSheets_For_WooCommerce namespace.
 *
 * @since 1.0.0
 * @package wpsyncsheets-for-woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }
if ( ! class_exists( 'WPSSW_WP_Crowdfunding' ) ) :
	/**
	 * Class WPSSW_WP_Crowdfunding.
	 */
	class WPSSW_WP_Crowdfunding extends WPSSW_Order_Utils {
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
		 * Check if WP Crowdfunding Plugin is active or not.
		 */
		public static function wpssw_is_pugin_active() {
			if ( ! class_exists( '\WPCF\Crowdfunding' ) ) {
				return false;
			}
			return true;
		}
		/**
		 * Prepare Header List.
		 */
		protected function prepare_headers() {
			self::$wpssw_headers = array( 'Video URL', 'Start Date', 'End Date', 'Minimum Price', 'Maximum Price', 'Predefined Pledge Amount', 'Funding Goal', 'Campaign End Method', 'Country', 'Location', 'Reward Pledge Amount', 'Reward Image', 'Reward Estimated Delivery Month', 'Reward Estimated Delivery Year', 'Reward Quantity', 'Reward Description' );
		}
		/**
		 * Get Header List.
		 *
		 * @param array $headers .
		 */
		public static function get_header_list( $headers = array() ) {
			if ( ! empty( self::$wpssw_headers ) ) {
				$headers['WPSSW_WP_Crowdfunding'] = self::$wpssw_headers;
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
				$wpssw_subvalue   = '';
				$wpssw_product_id = $wpssw_item['product_id'] ? $wpssw_item['product_id'] : '';
				if ( 'Video URL' === (string) $wpssw_headers_name ) {
					$wpssw_subvalue = get_post_meta( $wpssw_product_id, 'wpneo_funding_video', true );
				}
				if ( 'Start Date' === (string) $wpssw_headers_name ) {
					$wpssw_subvalue = get_post_meta( $wpssw_product_id, '_nf_duration_start', true );
				}
				if ( 'End Date' === (string) $wpssw_headers_name ) {
					$wpssw_subvalue = get_post_meta( $wpssw_product_id, '_nf_duration_end', true );
				}
				if ( 'Minimum Price' === (string) $wpssw_headers_name ) {
					$wpssw_subvalue = get_post_meta( $wpssw_product_id, 'wpneo_funding_minimum_price', true );
				}
				if ( 'Maximum Price' === (string) $wpssw_headers_name ) {
					$wpssw_subvalue = get_post_meta( $wpssw_product_id, 'wpneo_funding_maximum_price', true );
				}
				if ( 'Predefined Pledge Amount' === (string) $wpssw_headers_name ) {
					$wpssw_subvalue = get_post_meta( $wpssw_product_id, 'wpcf_predefined_pledge_amount', true );
				}
				if ( 'Funding Goal' === (string) $wpssw_headers_name ) {
					$wpssw_subvalue = get_post_meta( $wpssw_product_id, '_nf_funding_goal', true );
				}
				if ( 'Campaign End Method' === (string) $wpssw_headers_name ) {
					$wpssw_subvalue = get_post_meta( $wpssw_product_id, 'wpneo_campaign_end_method', true );
				}
				if ( 'Country' === (string) $wpssw_headers_name ) {
					$wpssw_subvalue = get_post_meta( $wpssw_product_id, 'wpneo_country', true );
				}
				if ( 'Location' === (string) $wpssw_headers_name ) {
					$wpssw_subvalue = get_post_meta( $wpssw_product_id, '_nf_location', true );
				}
				$wpssw_wpneo_reward = get_post_meta( $wpssw_product_id, 'wpneo_reward', true );
				if ( ! empty( $wpssw_wpneo_reward ) ) {
					$wpssw_wpneo_reward = json_decode( $wpssw_wpneo_reward );
					if ( 'Reward Pledge Amount' === (string) $wpssw_headers_name ) {
						$wpssw_subvalue = $wpssw_wpneo_reward[0]->wpneo_rewards_pladge_amount ? $wpssw_wpneo_reward[0]->wpneo_rewards_pladge_amount : '';
					}
					if ( 'Reward Image' === (string) $wpssw_headers_name ) {
						$wpssw_subvalue = $wpssw_wpneo_reward[0]->wpneo_rewards_image_field ? $wpssw_wpneo_reward[0]->wpneo_rewards_image_field : '';
						if ( $wpssw_subvalue ) {
							$wpssw_subvalue = '=IMAGE("' . wp_get_attachment_url( $wpssw_subvalue ) . '")';
						}
					}
					if ( 'Reward Estimated Delivery Month' === (string) $wpssw_headers_name ) {
						$wpssw_subvalue = $wpssw_wpneo_reward[0]->wpneo_rewards_endmonth ? $wpssw_wpneo_reward[0]->wpneo_rewards_endmonth : '';
					}
					if ( 'Reward Estimated Delivery Year' === (string) $wpssw_headers_name ) {
						$wpssw_subvalue = $wpssw_wpneo_reward[0]->wpneo_rewards_endyear ? $wpssw_wpneo_reward[0]->wpneo_rewards_endyear : '';
					}
					if ( 'Reward Quantity' === (string) $wpssw_headers_name ) {
						$wpssw_subvalue = $wpssw_wpneo_reward[0]->wpneo_rewards_item_limit ? $wpssw_wpneo_reward[0]->wpneo_rewards_item_limit : '';
					}
					if ( 'Reward Description' === (string) $wpssw_headers_name ) {
						$wpssw_subvalue = $wpssw_wpneo_reward[0]->wpneo_rewards_description ? $wpssw_wpneo_reward[0]->wpneo_rewards_description : '';
					}
				}
				if ( is_array( $wpssw_subvalue ) ) {
					$wpssw_value[] = implode( ',', $wpssw_subvalue );
				} else {
					$wpssw_value[] = ucfirst( $wpssw_subvalue );
				}
			}
			return $wpssw_value;
		}
	}
	new WPSSW_WP_Crowdfunding();
endif;
