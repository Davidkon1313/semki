<?php
/**
 * Main WPSyncSheets_For_WooCommerce namespace.
 *
 * @since 1.0.0
 * @package wpsyncsheets-for-woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }

if ( ! class_exists( 'WPSSW_WP_Product_Crowdfunding' ) ) :

	/**
	 * Class WPSSW_WP_Product_Crowdfunding.
	 */
	class WPSSW_WP_Product_Crowdfunding extends WPSSW_Product_Utils {

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
		 * Check if WP Crowdfunding Plugin is active or not.
		 */
		public static function wpssw_is_pugin_active() {
			if ( class_exists( '\WPCF\Crowdfunding' ) ) {
				return true;
			}
			return false;
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
				$headers['WPSSW_WP_Product_Crowdfunding'] = self::$wpssw_headers;
			}
			return $headers;
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
			$wpssw_subvalue   = '';
			$wpssw_product_id = $wpssw_product->get_id();
			$wpssw_value      = '';

			if ( 'crowdfunding' === (string) $wpssw_product->get_type() ) {

				if ( 'Video URL' === (string) $wpssw_headers_name ) {
					$wpssw_subvalue = get_post_meta( $wpssw_product_id, 'wpneo_funding_video', true );
					$wpssw_value    = self::check_is_array( $wpssw_subvalue );
					return $wpssw_value;
				}
				if ( 'Start Date' === (string) $wpssw_headers_name ) {
					$wpssw_subvalue = get_post_meta( $wpssw_product_id, '_nf_duration_start', true );
					$wpssw_value    = self::check_is_array( $wpssw_subvalue );
					return $wpssw_value;
				}
				if ( 'End Date' === (string) $wpssw_headers_name ) {
					$wpssw_subvalue = get_post_meta( $wpssw_product_id, '_nf_duration_end', true );
					$wpssw_value    = self::check_is_array( $wpssw_subvalue );
					return $wpssw_value;
				}
				if ( 'Minimum Price' === (string) $wpssw_headers_name ) {
					$wpssw_subvalue = get_post_meta( $wpssw_product_id, 'wpneo_funding_minimum_price', true );
					$wpssw_value    = self::check_is_array( $wpssw_subvalue );
					return $wpssw_value;
				}
				if ( 'Maximum Price' === (string) $wpssw_headers_name ) {
					$wpssw_subvalue = get_post_meta( $wpssw_product_id, 'wpneo_funding_maximum_price', true );
					$wpssw_value    = self::check_is_array( $wpssw_subvalue );
					return $wpssw_value;
				}
				if ( 'Predefined Pledge Amount' === (string) $wpssw_headers_name ) {
					$wpssw_subvalue = get_post_meta( $wpssw_product_id, 'wpcf_predefined_pledge_amount', true );
					$wpssw_value    = self::check_is_array( $wpssw_subvalue );
					return $wpssw_value;
				}
				if ( 'Funding Goal' === (string) $wpssw_headers_name ) {
					$wpssw_subvalue = get_post_meta( $wpssw_product_id, '_nf_funding_goal', true );
					return $wpssw_subvalue;
				}
				if ( 'Campaign End Method' === (string) $wpssw_headers_name ) {
					$wpssw_subvalue = get_post_meta( $wpssw_product_id, 'wpneo_campaign_end_method', true );
					$wpssw_value    = self::check_is_array( $wpssw_subvalue );
					return $wpssw_value;
				}
				if ( 'Country' === (string) $wpssw_headers_name ) {
					$wpssw_subvalue = get_post_meta( $wpssw_product_id, 'wpneo_country', true );
					$wpssw_value    = self::check_is_array( $wpssw_subvalue );
					return $wpssw_value;
				}
				if ( 'Location' === (string) $wpssw_headers_name ) {
					$wpssw_subvalue = get_post_meta( $wpssw_product_id, '_nf_location', true );
					$wpssw_value    = self::check_is_array( $wpssw_subvalue );
					return $wpssw_value;
				}

				$wpssw_wpneo_reward = get_post_meta( $wpssw_product_id, 'wpneo_reward', true );
				$wpssw_wpneo_reward = json_decode( $wpssw_wpneo_reward );
				if ( ! empty( $wpssw_wpneo_reward ) ) {

					if ( 'Reward Pledge Amount' === (string) $wpssw_headers_name ) {
						$wpssw_subvalue = $wpssw_wpneo_reward[0]->wpneo_rewards_pladge_amount ? $wpssw_wpneo_reward[0]->wpneo_rewards_pladge_amount : '';
						$wpssw_value    = self::check_is_array( $wpssw_subvalue );
						return $wpssw_value;
					}

					if ( 'Reward Image' === (string) $wpssw_headers_name ) {
						$wpssw_subvalue = $wpssw_wpneo_reward[0]->wpneo_rewards_image_field ? $wpssw_wpneo_reward[0]->wpneo_rewards_image_field : '';
						if ( $wpssw_subvalue ) {
							$wpssw_subvalue = '=IMAGE("' . wp_get_attachment_url( $wpssw_subvalue ) . '")';
						}
						$wpssw_value = self::check_is_array( $wpssw_subvalue );
						return $wpssw_value;
					}

					if ( 'Reward Estimated Delivery Month' === (string) $wpssw_headers_name ) {
						$wpssw_subvalue = $wpssw_wpneo_reward[0]->wpneo_rewards_endmonth ? $wpssw_wpneo_reward[0]->wpneo_rewards_endmonth : '';
						$wpssw_value    = self::check_is_array( $wpssw_subvalue );
						return $wpssw_value;
					}

					if ( 'Reward Estimated Delivery Year' === (string) $wpssw_headers_name ) {
						$wpssw_subvalue = $wpssw_wpneo_reward[0]->wpneo_rewards_endyear ? $wpssw_wpneo_reward[0]->wpneo_rewards_endyear : '';
						return $wpssw_subvalue;
					}
					if ( 'Reward Quantity' === (string) $wpssw_headers_name ) {
						$wpssw_subvalue = $wpssw_wpneo_reward[0]->wpneo_rewards_item_limit ? $wpssw_wpneo_reward[0]->wpneo_rewards_item_limit : '';
						$wpssw_value    = self::check_is_array( $wpssw_subvalue );
						return $wpssw_value;
					}
					if ( 'Reward Description' === (string) $wpssw_headers_name ) {
						$wpssw_subvalue = $wpssw_wpneo_reward[0]->wpneo_rewards_description ? $wpssw_wpneo_reward[0]->wpneo_rewards_description : '';
						$wpssw_value    = self::check_is_array( $wpssw_subvalue );
						return $wpssw_value;
					}
				}
			}
		}
		/**
		 * Check value is array or not
		 *
		 * @param string|array $wpssw_value value to check if it is array or not.
		 * @return string
		 */
		public static function check_is_array( $wpssw_value ) {
			if ( is_array( $wpssw_value ) ) {
				$wpssw_product_value = implode( ',', $wpssw_value );
				return $wpssw_product_value;
			}
			return ucfirst( $wpssw_value );
		}
	}
	new WPSSW_WP_Product_Crowdfunding();
endif;
