<?php
/**
 * Main WPSyncSheets_For_WooCommerce namespace.
 *
 * @since 1.0.0
 * @package wpsyncsheets-for-woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }

if ( ! class_exists( 'WPSSW_Customer_Headers' ) ) :
	/**
	 * Class WPSSW_Customer_Headers.
	 */
	class WPSSW_Customer_Headers extends WPSSW_Customer_Utils {
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
			$this->prepare_headers();
			add_filter( 'wpsyncsheets_customer_headers', __CLASS__ . '::get_header_list', 10, 1 );
		}

		/**
		 * Prepare Header List.
		 */
		protected function prepare_headers() {
			self::$wpssw_headers = array(
				'user_login'          => 'Customer Username',
				'role'                => 'Customer Role',
				'first_name'          => 'Customer FirstName',
				'last_name'           => 'Customer LastName',
				'nickname'            => 'Customer Nickname',
				'user_email'          => 'Customer EmailID',
				'user_url'            => 'Customer Website',
				'description'         => 'Customer Biographical Info',
				'profile_image'       => 'Customer Profile Image',
				'billing_first_name'  => 'Billing FirstName',
				'billing_last_name'   => 'Billing LastName',
				'billing_company'     => 'Billing Company Name',
				'billing_address_1'   => 'Billing Address1',
				'billing_address_2'   => 'Billing Address2',
				'billing_city'        => 'Billing City',
				'billing_postcode'    => 'Billing Postcode / ZIP',
				'billing_country'     => 'Billing Country',
				'billing_state'       => 'Billing State',
				'billing_phone'       => 'Billing Phone Number',
				'billing_email'       => 'Billing EmailID',
				'shipping_first_name' => 'Shipping FirstName',
				'shipping_last_name'  => 'Shipping LastName',
				'shipping_company'    => 'Shipping Company Name',
				'shipping_address_1'  => 'Shipping Address1',
				'shipping_address_2'  => 'Shipping Address2',
				'shipping_city'       => 'Shipping City',
				'shipping_postcode'   => 'Shipping Postcode / ZIP',
				'shipping_country'    => 'Shipping Country',
				'shipping_state'      => 'Shipping State',
			);
		}

		/**
		 * Get Header List.
		 *
		 * @param array $headers .
		 */
		public static function get_header_list( $headers = array() ) {
			if ( ! empty( self::$wpssw_headers ) ) {
				$headers['WPSSW_Customer_Headers'] = self::$wpssw_headers;
			}
			return $headers;
		}

		/**
		 * Get Value for given header name.
		 *
		 * @param string $wpssw_headers_name Header name.
		 * @param array  $customers_metadata metadata array of customer.
		 * @param int    $customer_id id of the customer being processed.
		 * @param mix    $wpssw_customer customers data.
		 * @param array  $wpssw_custom_value custom value.
		 */
		public static function get_value( $wpssw_headers_name, $customers_metadata, $customer_id, $wpssw_customer, $wpssw_custom_value = array() ) {
			return self::prepare_value( $wpssw_headers_name, $customers_metadata, $customer_id, $wpssw_customer, $wpssw_custom_value );
		}

		/**
		 * Prepare Value for given header name.
		 *
		 * @param string $wpssw_headers_name Header name.
		 * @param array  $customers_metadata metadata array of customer.
		 * @param int    $customer_id id of the customer being processed.
		 * @param mix    $wpssw_customer customers data.
		 * @param array  $wpssw_custom_value custom value.
		 */
		public static function prepare_value( $wpssw_headers_name, $customers_metadata, $customer_id, $wpssw_customer, $wpssw_custom_value ) {

			$wpssw_value   = '';
			$countries_obj = new WC_Countries();
			$countries     = $countries_obj->get_countries();

			/** Static header dropdown wpssw_static_header_name */
			if ( ! empty( $wpssw_custom_value ) ) {
				$wpssw_static_header_name = array_column( $wpssw_custom_value, 0 );

				if ( in_array( $wpssw_headers_name, $wpssw_static_header_name, true ) ) {
					$search_key         = array_search( $wpssw_headers_name, $wpssw_static_header_name, true );
					$wpssw_search_array = $wpssw_custom_value[ $search_key ];

					if ( 'blank' === (string) $wpssw_search_array[1] ) {
						$wpssw_insert_val = Google_Model::NULL_VALUE;
						$wpssw_value      = $wpssw_insert_val;
						return $wpssw_value;
					}
				}
			}

			if ( 'Customer Username' === (string) $wpssw_headers_name ) {
				$wpssw_value = (string) $customers_metadata['user_login'];
				return $wpssw_value;
			}
			if ( 'Customer Role' === (string) $wpssw_headers_name ) {
				$role        = $customers_metadata['roles'];
				$wpssw_value = $role[0];
				return $wpssw_value;
			}
			if ( 'Customer FirstName' === (string) $wpssw_headers_name ) {
				$wpssw_value = (string) $customers_metadata['first_name'][0];
				return $wpssw_value;
			}
			if ( 'Customer LastName' === (string) $wpssw_headers_name ) {
				$wpssw_value = (string) $customers_metadata['last_name'][0];
				return $wpssw_value;
			}
			if ( 'Customer Nickname' === (string) $wpssw_headers_name ) {
				$wpssw_value = (string) $customers_metadata['nickname'][0];
				return $wpssw_value;
			}
			if ( 'Customer EmailID' === (string) $wpssw_headers_name ) {
				$wpssw_value = (string) $customers_metadata['user_email'];
				return $wpssw_value;
			}
			if ( 'Customer Website' === (string) $wpssw_headers_name ) {
				$wpssw_value = (string) html_entity_decode( $customers_metadata['user_url'] );
				return $wpssw_value;
			}
			if ( 'Customer Biographical Info' === (string) $wpssw_headers_name ) {
				$wpssw_value = (string) $customers_metadata['description'][0];
				return $wpssw_value;
			}
			if ( 'Customer Profile Image' === (string) $wpssw_headers_name ) {
				$wpssw_value = $customers_metadata['profile_image'];
				return $wpssw_value;
			}
			if ( 'Billing FirstName' === (string) $wpssw_headers_name ) {
				if ( isset( $customers_metadata['billing_first_name'] ) && ! empty( $customers_metadata['billing_first_name'] ) ) {
					$wpssw_value = (string) $customers_metadata['billing_first_name'][0];
				} else {
					$wpssw_value = '';
				}
				return $wpssw_value;
			}
			if ( 'Billing LastName' === (string) $wpssw_headers_name ) {
				if ( isset( $customers_metadata['billing_last_name'] ) && ! empty( $customers_metadata['billing_last_name'] ) ) {
					$wpssw_value = (string) $customers_metadata['billing_last_name'][0];
				} else {
					$wpssw_value = '';
				}
				return $wpssw_value;
			}
			if ( 'Billing Company Name' === (string) $wpssw_headers_name ) {
				if ( isset( $customers_metadata['billing_company'] ) && ! empty( $customers_metadata['billing_company'] ) ) {
					$wpssw_value = (string) $customers_metadata['billing_company'][0];
				} else {
					$wpssw_value = '';
				}
				return $wpssw_value;
			}
			if ( 'Billing Address1' === (string) $wpssw_headers_name ) {
				if ( isset( $customers_metadata['billing_address_1'] ) && ! empty( $customers_metadata['billing_address_1'] ) ) {
					$wpssw_value = (string) $customers_metadata['billing_address_1'][0];
				} else {
					$wpssw_value = '';
				}
				return $wpssw_value;
			}
			if ( 'Billing Address2' === (string) $wpssw_headers_name ) {
				if ( isset( $customers_metadata['billing_address_2'] ) && ! empty( $customers_metadata['billing_address_2'] ) ) {
					$wpssw_value = (string) $customers_metadata['billing_address_2'][0];
				} else {
					$wpssw_value = '';
				}
				return $wpssw_value;
			}
			if ( 'Billing City' === (string) $wpssw_headers_name ) {
				if ( isset( $customers_metadata['billing_city'] ) && ! empty( $customers_metadata['billing_city'] ) ) {
					$wpssw_value = (string) $customers_metadata['billing_city'][0];
				} else {
					$wpssw_value = '';
				}
				return $wpssw_value;
			}
			if ( 'Billing Postcode / ZIP' === (string) $wpssw_headers_name ) {
				if ( isset( $customers_metadata['billing_postcode'] ) && ! empty( $customers_metadata['billing_postcode'] ) ) {
					$wpssw_value = (string) $customers_metadata['billing_postcode'][0];
				} else {
					$wpssw_value = '';
				}
				return $wpssw_value;
			}
			if ( 'Billing Country' === (string) $wpssw_headers_name ) {
				if ( isset( $customers_metadata['billing_country'] ) && ! empty( $customers_metadata['billing_country'] ) ) {
					if ( array_key_exists( $customers_metadata['billing_country'][0], $countries ) ) {
						$wpssw_value = $countries[ $customers_metadata['billing_country'][0] ];
					} else {
						$wpssw_value = '';
					}
				} else {
					$wpssw_value = '';
				}
				return $wpssw_value;
			}
			if ( 'Billing State' === (string) $wpssw_headers_name ) {
				if ( isset( $customers_metadata['billing_state'] ) && ! empty( $customers_metadata['billing_state'] ) ) {
					if ( array_key_exists( $customers_metadata['billing_country'][0], $countries ) ) {
						$states = $countries_obj->get_states( $customers_metadata['billing_country'][0] );
						if ( ! empty( $states ) && array_key_exists( $customers_metadata['billing_state'][0], $states ) ) {
							$wpssw_value = $states[ $customers_metadata['billing_state'][0] ];
						} elseif ( empty( $states ) ) {
							$wpssw_value = (string) $customers_metadata['billing_state'][0];
						} else {
							$wpssw_value = '';
						}
					}
				} else {
					$wpssw_value = '';
				}
				return $wpssw_value;
			}
			if ( 'Billing Phone Number' === (string) $wpssw_headers_name ) {
				if ( isset( $customers_metadata['billing_phone'] ) && ! empty( $customers_metadata['billing_phone'] ) ) {
					$wpssw_value = (string) $customers_metadata['billing_phone'][0];
				} else {
					$wpssw_value = '';
				}
				return $wpssw_value;
			}
			if ( 'Billing EmailID' === (string) $wpssw_headers_name ) {
				if ( isset( $customers_metadata['billing_email'] ) && ! empty( $customers_metadata['billing_email'] ) ) {
					$wpssw_value = (string) $customers_metadata['billing_email'][0];
				} else {
					$wpssw_value = '';
				}
				return $wpssw_value;
			}
			if ( 'Shipping FirstName' === (string) $wpssw_headers_name ) {
				if ( isset( $customers_metadata['shipping_first_name'] ) && ! empty( $customers_metadata['shipping_first_name'] ) ) {
					$wpssw_value = (string) $customers_metadata['shipping_first_name'][0];
				} else {
					$wpssw_value = '';
				}
				return $wpssw_value;
			}
			if ( 'Shipping LastName' === (string) $wpssw_headers_name ) {
				if ( isset( $customers_metadata['shipping_last_name'] ) && ! empty( $customers_metadata['shipping_last_name'] ) ) {
					$wpssw_value = (string) $customers_metadata['shipping_last_name'][0];
				} else {
					$wpssw_value = '';
				}
				return $wpssw_value;
			}
			if ( 'Shipping Company Name' === (string) $wpssw_headers_name ) {
				if ( isset( $customers_metadata['shipping_company'] ) && ! empty( $customers_metadata['shipping_company'] ) ) {
					$wpssw_value = (string) $customers_metadata['shipping_company'][0];
				} else {
					$wpssw_value = '';
				}
				return $wpssw_value;
			}
			if ( 'Shipping Address1' === (string) $wpssw_headers_name ) {
				if ( isset( $customers_metadata['shipping_address_1'] ) && ! empty( $customers_metadata['shipping_address_1'] ) ) {
					$wpssw_value = (string) $customers_metadata['shipping_address_1'][0];
				} else {
					$wpssw_value = '';
				}
				return $wpssw_value;
			}
			if ( 'Shipping Address2' === (string) $wpssw_headers_name ) {
				if ( isset( $customers_metadata['shipping_address_2'] ) && ! empty( $customers_metadata['shipping_address_2'] ) ) {
					$wpssw_value = (string) $customers_metadata['shipping_address_2'][0];
				} else {
					$wpssw_value = '';
				}
				return $wpssw_value;
			}
			if ( 'Shipping City' === (string) $wpssw_headers_name ) {
				if ( isset( $customers_metadata['shipping_city'] ) && ! empty( $customers_metadata['shipping_city'] ) ) {
					$wpssw_value = (string) $customers_metadata['shipping_city'][0];
				} else {
					$wpssw_value = '';
				}
				return $wpssw_value;
			}
			if ( 'Shipping Postcode / ZIP' === (string) $wpssw_headers_name ) {
				if ( isset( $customers_metadata['shipping_postcode'] ) && ! empty( $customers_metadata['shipping_postcode'] ) ) {
					$wpssw_value = (string) $customers_metadata['shipping_postcode'][0];
				} else {
					$wpssw_value = '';
				}
				return $wpssw_value;
			}
			if ( 'Shipping Country' === (string) $wpssw_headers_name ) {
				if ( isset( $customers_metadata['shipping_country'] ) && ! empty( $customers_metadata['shipping_country'] ) ) {
					if ( array_key_exists( $customers_metadata['shipping_country'][0], $countries ) ) {
						$wpssw_value = $countries[ $customers_metadata['shipping_country'][0] ];
					} else {
						$wpssw_value = '';
					}
				} else {
					$wpssw_value = '';
				}
				return $wpssw_value;
			}
			if ( 'Shipping State' === (string) $wpssw_headers_name ) {
				if ( isset( $customers_metadata['shipping_state'] ) && ! empty( $customers_metadata['shipping_state'] ) ) {
					if ( array_key_exists( $customers_metadata['shipping_country'][0], $countries ) ) {
						$states = $countries_obj->get_states( $customers_metadata['shipping_country'][0] );
						if ( ! empty( $states ) && array_key_exists( $customers_metadata['shipping_state'][0], $states ) ) {
							$wpssw_value = $states[ $customers_metadata['shipping_state'][0] ];
						} elseif ( empty( $states ) ) {
							$wpssw_value = (string) $customers_metadata['shipping_state'][0];
						} else {
							$wpssw_value = '';
						}
					}
				} else {
					$wpssw_value = '';
				}
				return $wpssw_value;
			}
		}
	}
	new WPSSW_Customer_Headers();
endif;
