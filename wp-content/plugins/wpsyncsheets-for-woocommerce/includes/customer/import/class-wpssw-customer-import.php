<?php
/**
 * Main WPSyncSheets_For_WooCommerce namespace.
 *
 * @since 1.0.0
 * @package wpsyncsheets-for-woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! function_exists( 'wp_delete_user' ) ) {
	require_once ABSPATH . 'wp-admin/includes/user.php';
}
if ( ! class_exists( 'WPSSW_Customer_Import' ) ) :
	/**
	 * Class WPSSW_Customer_Import.
	 */
	class WPSSW_Customer_Import extends WPSSW_Setting {
		/**
		 * Instance of WPSSW_Google_API_Functions
		 *
		 * @var $instance_api
		 */
		protected static $instance_api = null;
		/**
		 * Initialization
		 */
		public function __construct() {
			self::wpssw_google_api();
			$wpssw_include = new WPSSW_Include_Action();
			$wpssw_include->wpssw_include_customer_import_ajax_hook();
		}
		/**
		 * Create Google Api Instance.
		 */
		public static function wpssw_google_api() {
			if ( null === self::$instance_api ) {
				self::$instance_api = new \WPSSW_Google_API_Functions();
			}
			return self::$instance_api;
		}
		/**
		 * Get customers count for syncronization
		 */
		public static function wpssw_get_customer_import_count() {
			if ( ! isset( $_POST['wpssw_customer_settings'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wpssw_customer_settings'] ) ), 'save_customer_settings' ) ) {
				echo esc_html__( 'Sorry, your nonce did not verify.', 'wpssw' );
				die();
			}
			if ( ! self::$instance_api->checkcredenatials() ) {
				return;
			}
			$wpssw_customer_spreadsheet_setting = parent::wpssw_option( 'wpssw_customer_spreadsheet_setting' );
			$wpssw_spreadsheetid                = parent::wpssw_option( 'wpssw_customer_spreadsheet_id' );
			$wpssw_sheetname                    = 'All Customers';
			$wpssw_checked                      = '';
			if ( 'yes' !== (string) $wpssw_customer_spreadsheet_setting ) {
				return;
			}
			$wpssw_sheet    = "'" . $wpssw_sheetname . "'!A:A";
			$wpssw_allentry = self::$instance_api->get_row_list( $wpssw_spreadsheetid, $wpssw_sheetname );
			$wpssw_data     = $wpssw_allentry->getValues();
			$wpssw_headers  = array_shift( $wpssw_data );

			$wpssw_insert_customers = array();
			$wpssw_update_customers = array();
			$wpssw_delete_customers = array();
			if ( in_array( 'Insert', $wpssw_headers, true ) ) {
				$wpssw_insert_key       = array_search( 'Insert', $wpssw_headers, true );
				$wpssw_insert_customers = array_values( array_filter( array_column( $wpssw_data, $wpssw_insert_key ) ) );
			}
			if ( in_array( 'Update', $wpssw_headers, true ) ) {
				$wpssw_update_key       = array_search( 'Update', $wpssw_headers, true );
				$wpssw_update_customers = array_values( array_filter( array_column( $wpssw_data, $wpssw_update_key ) ) );
			}
			if ( in_array( 'Delete', $wpssw_headers, true ) ) {
				$wpssw_delete_key       = array_search( 'Delete', $wpssw_headers, true );
				$wpssw_delete_customers = array_values( array_filter( array_column( $wpssw_data, $wpssw_delete_key ) ) );
			}
			$wpssw_result_array = array();
			if ( count( $wpssw_insert_customers ) > 0 ) {
				$wpssw_result_array['insertcustomers'] = count( $wpssw_insert_customers );
			}
			if ( count( $wpssw_update_customers ) > 0 ) {
				$wpssw_result_array['updatecustomers'] = count( $wpssw_update_customers );
			}
			if ( count( $wpssw_delete_customers ) > 0 ) {
				$wpssw_result_array['deletecustomers'] = count( $wpssw_delete_customers );
			}
			$totalimportcustomers                       = array_sum( array_values( $wpssw_result_array ) );
			$wpssw_result_array['totalimportcustomers'] = $totalimportcustomers;
			echo wp_json_encode( $wpssw_result_array );
			die;
		}
		/**
		 * Import customer
		 */
		public static function wpssw_customer_import() {

			$wpssw_cron_function_call = false;
			if ( ! isset( $_POST['wpssw_customer_settings'] ) ) {
				$wpssw_cron_function_call = true;
			}
			if ( ! $wpssw_cron_function_call ) {
				if ( ! isset( $_POST['wpssw_customer_settings'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wpssw_customer_settings'] ) ), 'save_customer_settings' ) ) {
					echo esc_html__( 'Sorry, your nonce did not verify.', 'wpssw' );
					die();
				}
			}

			if ( ! self::$instance_api->checkcredenatials() ) {
				return;
			}
			$wpssw_customer_spreadsheet_setting = parent::wpssw_option( 'wpssw_customer_spreadsheet_setting' );
			$wpssw_spreadsheetid                = parent::wpssw_option( 'wpssw_customer_spreadsheet_id' );
			$wpssw_woo_selections               = stripslashes_deep( parent::wpssw_option( 'wpssw_woo_customer_headers' ) );
			if ( ! $wpssw_woo_selections ) {
				return;
			}
			array_unshift( $wpssw_woo_selections, 'Customer Id' );
			$wpssw_sheetname = 'All Customers';
			$wpssw_checked   = '';
			if ( 'yes' !== (string) $wpssw_customer_spreadsheet_setting || ! WPSSW_Setting::wpssw_check_sheet_exist( $wpssw_spreadsheetid, $wpssw_sheetname ) ) {
				return;
			}

			$wpssw_sheet        = "'" . $wpssw_sheetname . "'!A:A";
			$wpssw_allentry     = self::$instance_api->get_row_list( $wpssw_spreadsheetid, $wpssw_sheetname );
			$wpssw_data         = $wpssw_allentry->getValues();
			$wpssw_allentry     = null;
			$wpssw_customer_ids = array_map(
				function( $wpssw_element ) {
					if ( isset( $wpssw_element[0] ) ) {
						return $wpssw_element[0];
					} else {
						return '';
					}
				},
				$wpssw_data
			);

			$wpssw_headers = array_shift( $wpssw_data );

			$wpssw_insert_customers = array();
			$wpssw_update_customers = array();
			$wpssw_delete_customers = array();
			if ( in_array( 'Insert', $wpssw_headers, true ) ) {
				$wpssw_insert_key       = array_search( 'Insert', $wpssw_headers, true );
				$wpssw_insert_customers = array_map(
					function( $wpssw_element ) use ( $wpssw_insert_key ) {
						if ( isset( $wpssw_element[ $wpssw_insert_key ] ) ) {
							return $wpssw_element[ $wpssw_insert_key ];
						} else {
							return '';
						}
					},
					$wpssw_data
				);
				$wpssw_insert_customers = array_filter( $wpssw_insert_customers );
			}
			if ( in_array( 'Update', $wpssw_headers, true ) ) {
				$wpssw_update_key       = array_search( 'Update', $wpssw_headers, true );
				$wpssw_update_customers = array_map(
					function( $wpssw_element ) use ( $wpssw_update_key ) {
						if ( isset( $wpssw_element[ $wpssw_update_key ] ) ) {
							return $wpssw_element[ $wpssw_update_key ];
						} else {
							return '';
						}
					},
					$wpssw_data
				);

				$wpssw_update_customers = array_filter( $wpssw_update_customers );
			}
			if ( in_array( 'Delete', $wpssw_headers, true ) ) {
				$wpssw_delete_key       = array_search( 'Delete', $wpssw_headers, true );
				$wpssw_delete_customers = array_map(
					function( $wpssw_element ) use ( $wpssw_delete_key ) {
						if ( isset( $wpssw_element[ $wpssw_delete_key ] ) ) {
							return $wpssw_element[ $wpssw_delete_key ];
						} else {
							return '';
						}
					},
					$wpssw_data
				);
				$wpssw_delete_customers = array_filter( $wpssw_delete_customers );
			}

			if ( $wpssw_cron_function_call ) {
				$wpssw_importcustomerlimit = apply_filters( 'wpssw_customer_autoimport_limit', 500 );
			} else {
				$wpssw_importcustomerlimit = isset( $_POST['importcustomerlimit'] ) ? sanitize_text_field( wp_unslash( $_POST['importcustomerlimit'] ) ) : '';
			}
			$deleterequestarray = array();
			$newimportcustomer  = 0;

			$inserted_ids = array();
			$updated_ids  = array();
			$deleted_ids  = array();
			$message      = '';

			$wpssw_response            = self::$instance_api->get_sheet_listing( $wpssw_spreadsheetid );
			$wpssw_existingsheetsnames = self::$instance_api->get_sheet_list( $wpssw_response );
			$wpssw_sheetid             = $wpssw_existingsheetsnames[ $wpssw_sheetname ];

			if ( ! empty( $wpssw_update_customers ) ) {
				foreach ( $wpssw_update_customers as $wpssw_customerid => $wpssw_val ) {
					if ( 1 !== (int) $wpssw_val || ! isset( $wpssw_data[ $wpssw_customerid ] ) ) {
						continue;
					}
					if ( $newimportcustomer > $wpssw_importcustomerlimit ) {
						break;
					}
					set_time_limit( 999 );
					$wpssw_customer_index = $wpssw_customerid + 1;

					if ( isset( $wpssw_customer_ids[ $wpssw_customer_index ] ) && ! empty( $wpssw_customer_ids[ $wpssw_customer_index ] ) ) {
						$cust_id = $wpssw_customer_ids[ $wpssw_customer_index ];

						$wpssw_customer_values       = $wpssw_data[ $wpssw_customerid ];
						$wpssw_customer_email_key    = array_search( 'Customer EmailID', $wpssw_woo_selections, true );
						$wpssw_customer_nickname_key = array_search( 'Customer Nickname', $wpssw_woo_selections, true );

						$user = new WP_User( $cust_id );
						if ( ! $user->exists() ) {
							if ( empty( $message ) ) {
								$message = 'customernotexists';
							}
							continue;
						}
						$user_data               = $user->data;
						$user_email              = $user_data->user_email;
						$wpssw_customer_email    = '';
						$wpssw_customer_nickname = '';
						if ( false !== $wpssw_customer_email_key ) {
							$wpssw_customer_email = isset( $wpssw_customer_values[ $wpssw_customer_email_key ] ) ? $wpssw_customer_values[ $wpssw_customer_email_key ] : '';
						} else {
							$wpssw_customer_email = $user_email;
						}
						if ( false !== $wpssw_customer_nickname_key ) {
							$wpssw_customer_nickname = isset( $wpssw_customer_values[ $wpssw_customer_nickname_key ] ) ? $wpssw_customer_values[ $wpssw_customer_nickname_key ] : '';
						} else {
							$wpssw_customer_nickname = get_user_meta( $cust_id, 'nickname' );
						}
						$exists = email_exists( $wpssw_customer_email );
						if ( trim( $user_email ) !== trim( $wpssw_customer_email ) && $exists ) {
							if ( empty( $message ) ) {
								$message = 'customeremailexist';
							}
							continue;
						} elseif ( empty( $wpssw_customer_email ) ) {
							if ( empty( $message ) ) {
								$message = 'addcustomeremail';
							}
							continue;
						} elseif ( empty( $wpssw_customer_nickname ) ) {
							if ( empty( $message ) ) {
								$message = 'addcustomernickname';
							}
							continue;
						} else {
							self::wpssw_update_customer( $cust_id, $wpssw_data[ $wpssw_customerid ] );

							$wpssw_customer_role_key = array_search( 'Customer Role', $wpssw_woo_selections, true );
							$wpssw_customer_role     = '';

							if ( false !== $wpssw_customer_role_key ) {
								if ( isset( $wpssw_customer_values[ $wpssw_customer_role_key ] ) && empty( $wpssw_customer_values[ $wpssw_customer_role_key ] ) ) {
									$wpssw_customer_values[ $wpssw_customer_role_key ] = 'customer';
								}
								$wpssw_customer_role = isset( $wpssw_customer_values[ $wpssw_customer_role_key ] ) ? $wpssw_customer_values[ $wpssw_customer_role_key ] : '';
							} else {
								$wpssw_customer_roles = array_values( $user->roles );
								$wpssw_customer_role  = $wpssw_customer_roles[0];
							}
							if ( ! empty( $wpssw_customer_role ) && 'customer' === (string) $wpssw_customer_role ) {

								$updated_ids[] = $cust_id;
							} else {
								if ( $wpssw_sheetid ) {
									$param                = array();
									$param                = self::$instance_api->prepare_param( $wpssw_sheetid, $wpssw_customer_index, $wpssw_customer_index + 1 );
									$deleterequestarray[] = self::$instance_api->deleteDimensionrequests( $param, 'ROWS' );
								}
							}
							$newimportcustomer++;
						}
					} else {
						if ( empty( $message ) ) {
							$message = 'addcustomerId';
						}
						continue;
					}
				}
			}

			if ( ! empty( $wpssw_insert_customers ) ) {
				foreach ( $wpssw_insert_customers as $wpssw_customer_index => $wpssw_val ) {
					if ( 1 !== (int) $wpssw_val || ! isset( $wpssw_data[ $wpssw_customer_index ] ) ) {
						continue;
					}
					if ( $newimportcustomer > $wpssw_importcustomerlimit ) {
						break;
					}
					set_time_limit( 999 );
					$wpssw_customer_values = $wpssw_data[ $wpssw_customer_index ];

					$wpssw_customer_username_key = array_search( 'Customer Username', $wpssw_woo_selections, true );
					$wpssw_customer_email_key    = array_search( 'Customer EmailID', $wpssw_woo_selections, true );
					$wpssw_customer_role_key     = array_search( 'Customer Role', $wpssw_woo_selections, true );
					$wpssw_customer_username     = isset( $wpssw_customer_values[ $wpssw_customer_username_key ] ) ? $wpssw_customer_values[ $wpssw_customer_username_key ] : '';
					$wpssw_customer_email        = isset( $wpssw_customer_values[ $wpssw_customer_email_key ] ) ? $wpssw_customer_values[ $wpssw_customer_email_key ] : '';
					$wpssw_customer_role         = '';

					if ( false !== $wpssw_customer_role_key ) {
						if ( isset( $wpssw_customer_values[ $wpssw_customer_role_key ] ) && empty( $wpssw_customer_values[ $wpssw_customer_role_key ] ) ) {
							$wpssw_customer_values[ $wpssw_customer_role_key ] = 'customer';
						}

						$wpssw_customer_role = isset( $wpssw_customer_values[ $wpssw_customer_role_key ] ) ? $wpssw_customer_values[ $wpssw_customer_role_key ] : '';
					}
					$wpssw_customer_id = isset( $wpssw_customer_values[0] ) ? $wpssw_customer_values[0] : '';
					if ( ! empty( $wpssw_customer_id ) ) {
						$customer = get_userdata( $wpssw_customer_id );
					}
					if ( ! empty( $wpssw_customer_username ) && ! empty( $wpssw_customer_email ) && empty( $wpssw_customer_id ) ) {

						$exists = email_exists( $wpssw_customer_email );
						if ( username_exists( $wpssw_customer_username ) ) {
							if ( empty( $message ) ) {
								$message = 'customernameexist';
							}
							continue;
						} elseif ( $exists ) {
							if ( empty( $message ) ) {
								$message = 'customeremailexist';
							}
							continue;
						} else {
							if ( $wpssw_sheetid ) {
								$param                = array();
								$startindex           = $wpssw_customer_index + 1;
								$endindex             = $wpssw_customer_index + 2;
								$param                = self::$instance_api->prepare_param( $wpssw_sheetid, $startindex, $endindex );
								$deleterequestarray[] = self::$instance_api->deleteDimensionrequests( $param, 'ROWS' );
							}
							$userdata = array(
								'user_login' => $wpssw_customer_username,   // (string) The user's login username.
								'user_pass'  => $wpssw_customer_username,   // (string) The user's password same as username.
								'user_email' => $wpssw_customer_email,
							);
							$user_id  = wp_insert_user( $userdata );
							$user     = new WP_User( $user_id );
							if ( empty( $wpssw_customer_role ) ) {
								$wpssw_customer_role = 'customer';
								$user->set_role( 'customer' );
								$user->save();
							}
							self::wpssw_update_customer( $user_id, $wpssw_customer_values );
							if ( ! empty( $wpssw_customer_role ) && 'customer' === (string) $wpssw_customer_role ) {
								$inserted_ids[] = $user_id;
							}
						}
						$newimportcustomer++;
					} elseif ( ! empty( $wpssw_customer_id ) ) {
						if ( empty( $message ) ) {
							$message = 'customerIdexist';
						}
						continue;
					} elseif ( false === $wpssw_customer_username_key ) {
						if ( empty( $message ) ) {
							$message = 'addcustomernamecolumn';
						}
						continue;
					} elseif ( false === $wpssw_customer_email_key ) {
						if ( empty( $message ) ) {
							$message = 'addcustomeremailcolumn';
						}
						continue;
					} elseif ( empty( $wpssw_customer_email ) ) {
						if ( empty( $message ) ) {
							$message = 'addcustomeremail';
						}
						continue;
					} else {
						if ( empty( $message ) ) {
							$message = 'addcustomername';
						}
						continue;
					}
				}
			}
			if ( ! empty( $deleterequestarray ) ) {
				global $keys;
				$keys = array();
				array_walk_recursive(
					$deleterequestarray,
					function( $value, $key ) {
						global $keys;
						if ( 'startIndex' === (string) $key ) {
							$keys[] = $value;
						}
					}
				);

				if ( count( $keys ) === count( $deleterequestarray ) ) {
					array_multisort( $keys, SORT_DESC, $deleterequestarray );
				}
				try {
					$param                  = array();
					$param['spreadsheetid'] = $wpssw_spreadsheetid;
					$param['requestarray']  = $deleterequestarray;

					$wpssw_response = self::$instance_api->updatebachrequests( $param );
				} catch ( Exception $e ) {
					if ( ! $wpssw_cron_function_call ) {
						echo esc_html( 'Message: ' . $e->getMessage() );
						die;
					}
				}
			}
			if ( ! empty( $wpssw_delete_customers ) ) {
				foreach ( $wpssw_delete_customers as $wpssw_customerid => $wpssw_val ) {
					if ( 1 !== (int) $wpssw_val || ! isset( $wpssw_data[ $wpssw_customerid ] ) ) {
						continue;
					}
					if ( $newimportcustomer > $wpssw_importcustomerlimit ) {
						break;
					}
					set_time_limit( 999 );
					$wpssw_customer_index = $wpssw_customerid + 1;

					if ( isset( $wpssw_customer_ids[ $wpssw_customer_index ] ) && ! empty( $wpssw_customer_ids[ $wpssw_customer_index ] ) ) {
						$cust_id = $wpssw_customer_ids[ $wpssw_customer_index ];

						$user = new WP_User( $cust_id );

						if ( ! $user->exists() ) {
							if ( empty( $message ) ) {
								$message = 'customernotexists';
							}
							continue;
						} else {
							remove_action( 'delete_user', 'WPSSW_Customer::wpssw_delete_user' );
							$deleted_ids[] = $user->ID;
							wp_delete_user( $user->ID );
						}
						$newimportcustomer++;
					} else {
						if ( empty( $message ) ) {
							$message = 'addcustomerId';
						}
						continue;
					}
				}
			}

			$settings = array(
				'setting'        => 'customer',
				'setting_enable' => $wpssw_customer_spreadsheet_setting,
				'spreadsheet_id' => $wpssw_spreadsheetid,
				'sheetname'      => $wpssw_sheetname,
			);

			if ( ! empty( $deleted_ids ) ) {
				WPSSW_Setting::wpssw_multiple_update_data( $deleted_ids, $settings, true, 'delete' );
			}
			if ( ! empty( $updated_ids ) ) {
				WPSSW_Setting::wpssw_multiple_update_data( $updated_ids, $settings, true, 'update' );
			}
			if ( ! empty( $inserted_ids ) ) {
				WPSSW_Setting::wpssw_multiple_update_data( $inserted_ids, $settings, true, 'insert' );
			}
			if ( ! $wpssw_cron_function_call ) {
				if ( ! empty( $message ) ) {
					echo esc_html( $message );
					die;
				}
				echo esc_html__( 'successful', 'wpssw' );
				die;
			}
		}
		/**
		 * Update imported customer
		 *
		 * @param int    $wpssw_customerid customer id.
		 * @param array  $wpssw_data customer data array.
		 * @param string $wpssw_opration opration to perform on customer.
		 */
		public static function wpssw_update_customer( $wpssw_customerid, $wpssw_data, $wpssw_opration = 'update' ) {

			if ( ! self::$instance_api->checkcredenatials() ) {
				return;
			}
			if ( (int) $wpssw_customerid < 1 ) {
				return;
			}
			$wpssw_woo_selections = stripslashes_deep( parent::wpssw_option( 'wpssw_woo_customer_headers' ) );
			if ( ! $wpssw_woo_selections ) {
				return;
			}
			array_unshift( $wpssw_woo_selections, 'Customer Id' );
			$wpssw_include = new WPSSW_Include_Action();
			$wpssw_include->wpssw_include_customer_compatibility_files();
			$wpssw_woocustomer_headers = apply_filters( 'wpsyncsheets_customer_headers', array() );
			$wpssw_default_header      = $wpssw_woocustomer_headers['WPSSW_Customer_Headers'];
			$wpssw_custom_header       = isset( $wpssw_woocustomer_headers['WPSSW_Custom_Headers_For_Customer'] ) ? $wpssw_woocustomer_headers['WPSSW_Custom_Headers_For_Customer'] : array();
			$wpssw_exclude_headers     = array( 'Customer Username', 'Customer Profile Image' );
			$wpssw_user_data_headers   = array( 'Customer EmailID', 'Customer Website', 'Customer Biographical Info' );
			$wpssw_updated_array       = array();
			$wpssw_crud_operation      = array( 'Insert', 'Update', 'Delete' );

			$wpssw_customer     = new WP_User( $wpssw_customerid );
			$customers_metadata = get_user_meta( $wpssw_customerid );

			$roles_obj              = new WP_Roles();
			$wpssw_predefined_roles = $roles_obj->get_names();
			$shipping_country       = '';
			$billing_country        = '';

			global $woocommerce;
			$countries_obj         = new WC_Countries();
			$countries             = $countries_obj->get_countries();
			$billing_country_key   = array_search( 'Billing Country', $wpssw_woo_selections, true );
			$billing_country_blank = 0;

			if ( false !== $billing_country_key ) {
				if ( array_key_exists( strtoupper( trim( $wpssw_data[ $billing_country_key ] ) ), $countries ) ) {
					$billing_country                    = strtoupper( trim( $wpssw_data[ $billing_country_key ] ) );
					$wpssw_data[ $billing_country_key ] = $billing_country;
				} else {
					$billing_country = isset( $wpssw_data[ $billing_country_key ] ) ? ucfirst( strtolower( trim( $wpssw_data[ $billing_country_key ] ) ) ) : '';
					if ( ! empty( $billing_country ) && in_array( $billing_country, $countries, true ) ) {
						$billing_country                    = array_search( $billing_country, $countries, true );
						$wpssw_data[ $billing_country_key ] = $billing_country;
					} elseif ( empty( $billing_country ) ) {
						$billing_country_blank = 1;
					} else {
						$billing_country = '';
					}
				}
			} else {
				$billing_country = get_user_meta( $wpssw_customerid, 'billing_country' );
			}
			$shipping_country_key   = array_search( 'Shipping Country', $wpssw_woo_selections, true );
			$shipping_country_blank = 0;
			if ( false !== $shipping_country_key ) {
				if ( array_key_exists( strtoupper( trim( $wpssw_data[ $shipping_country_key ] ) ), $countries ) ) {
					$shipping_country                    = strtoupper( trim( $wpssw_data[ $shipping_country_key ] ) );
					$wpssw_data[ $shipping_country_key ] = $shipping_country;
				} else {
					$shipping_country = isset( $wpssw_data[ $shipping_country_key ] ) ? ucfirst( strtolower( trim( $wpssw_data[ $shipping_country_key ] ) ) ) : '';
					if ( ! empty( $shipping_country ) && in_array( $shipping_country, $countries, true ) ) {
						$shipping_country                    = array_search( $shipping_country, $countries, true );
						$wpssw_data[ $shipping_country_key ] = $shipping_country;
					} elseif ( empty( $shipping_country ) ) {
						$shipping_country_blank = 1;
					} else {
						$shipping_country = '';
					}
				}
			} else {
				$shipping_country = get_user_meta( $wpssw_customerid, 'shipping_country' );
			}

			foreach ( $wpssw_woo_selections as $wpssw_key => $wpssw_header ) {
				if ( in_array( $wpssw_header, $wpssw_crud_operation, true ) ) {
					continue;
				}
				if ( in_array( $wpssw_header, $wpssw_default_header, true ) ) {
					$wpssw_meta_key           = array_search( $wpssw_header, $wpssw_default_header, true );
					$wpssw_data[ $wpssw_key ] = isset( $wpssw_data[ $wpssw_key ] ) ? $wpssw_data[ $wpssw_key ] : '';

					if ( isset( $wpssw_data[ $wpssw_key ] ) && $wpssw_meta_key ) {
						if ( is_numeric( $wpssw_data[ $wpssw_key ] ) ) {
							$wpssw_data[ $wpssw_key ] = (int) $wpssw_data[ $wpssw_key ];
						}
					}

					if ( in_array( $wpssw_header, $wpssw_exclude_headers, true ) ) {
						continue;
					}
					if ( in_array( $wpssw_header, $wpssw_user_data_headers, true ) ) {
						if ( ( 'Customer EmailID' === (string) $wpssw_header ) ) {
							$user       = new WP_User( $wpssw_customerid );
							$user_data  = $user->data;
							$user_email = $user_data->user_email;
							if ( trim( $user_email ) === trim( $wpssw_data[ $wpssw_key ] ) ) {
								continue;
							} elseif ( email_exists( $wpssw_data[ $wpssw_key ] ) ) {
								continue;
							} else {
								$wpssw_updated_array[ $wpssw_meta_key ] = $wpssw_data[ $wpssw_key ];
								continue;
							}
						}
						$wpssw_updated_array[ $wpssw_meta_key ] = $wpssw_data[ $wpssw_key ];
						continue;
					}
					if ( ( 'billing_country' === (string) $wpssw_meta_key && empty( $billing_country ) && 0 === (int) $billing_country_blank ) || ( 'shipping_country' === (string) $wpssw_meta_key && empty( $shipping_country ) && 0 === (int) $shipping_country_blank ) ) {
							continue;
					}
					if ( 'billing_state' === (string) $wpssw_meta_key ) {
						if ( ! empty( $wpssw_data[ $wpssw_key ] ) ) {
							$state_name = ucfirst( strtolower( trim( $wpssw_data[ $wpssw_key ] ) ) );
							if ( ! empty( $billing_country ) ) {
								$states = $countries_obj->get_states( $billing_country );
								if ( ! empty( $states ) && array_key_exists( strtoupper( trim( $wpssw_data[ $wpssw_key ] ) ), $states ) ) {
									self::wpssw_update_user_meta( $wpssw_customerid, $wpssw_meta_key, strtoupper( trim( $wpssw_data[ $wpssw_key ] ) ) );
								} elseif ( ! empty( $states ) && in_array( $state_name, $states, true ) ) {
									$wpssw_data[ $wpssw_key ] = array_search( $state_name, $states, true );
									self::wpssw_update_user_meta( $wpssw_customerid, $wpssw_meta_key, $wpssw_data[ $wpssw_key ] );
								} elseif ( empty( $states ) ) {
									self::wpssw_update_user_meta( $wpssw_customerid, $wpssw_meta_key, $wpssw_data[ $wpssw_key ] );
								}
							}
						} else {
							$wpssw_data[ $wpssw_key ] = '';
							self::wpssw_update_user_meta( $wpssw_customerid, $wpssw_meta_key, $wpssw_data[ $wpssw_key ] );
						}
						continue;
					}
					if ( 'shipping_state' === (string) $wpssw_meta_key ) {
						if ( ! empty( $wpssw_data[ $wpssw_key ] ) ) {
							$state_name = ucfirst( strtolower( trim( $wpssw_data[ $wpssw_key ] ) ) );

							if ( ! empty( $shipping_country ) ) {
								$states = $countries_obj->get_states( $shipping_country );
								if ( ! empty( $states ) && array_key_exists( strtoupper( trim( $wpssw_data[ $wpssw_key ] ) ), $states ) ) {
									self::wpssw_update_user_meta( $wpssw_customerid, $wpssw_meta_key, strtoupper( trim( $wpssw_data[ $wpssw_key ] ) ) );
								} elseif ( ! empty( $states ) && in_array( $state_name, $states, true ) ) {
									$wpssw_data[ $wpssw_key ] = array_search( $state_name, $states, true );
									self::wpssw_update_user_meta( $wpssw_customerid, $wpssw_meta_key, $wpssw_data[ $wpssw_key ] );
								} elseif ( empty( $states ) ) {
									self::wpssw_update_user_meta( $wpssw_customerid, $wpssw_meta_key, $wpssw_data[ $wpssw_key ] );
								}
							}
						} else {
							$wpssw_data[ $wpssw_key ] = '';
							self::wpssw_update_user_meta( $wpssw_customerid, $wpssw_meta_key, $wpssw_data[ $wpssw_key ] );
						}
						continue;
					}
					if ( ( 'Customer Role' === (string) $wpssw_header ) ) {
						$user = new WP_User( $wpssw_customerid );
						if ( array_key_exists( strtolower( trim( $wpssw_data[ $wpssw_key ] ) ), $wpssw_predefined_roles ) ) {
							$user->set_role( strtolower( trim( $wpssw_data[ $wpssw_key ] ) ) );
							$user->save();
						}
					}
					self::wpssw_update_user_meta( $wpssw_customerid, $wpssw_meta_key, $wpssw_data[ $wpssw_key ] );
				}
				if ( in_array( $wpssw_header, $wpssw_custom_header, true ) ) {
					do_action( 'wpssw_custom_headers_for_customer_doimport', $wpssw_customerid, $wpssw_header, $wpssw_data );
					continue;
				}
			}
			$wpssw_updated_array['ID'] = $wpssw_customerid;
			wp_update_user( $wpssw_updated_array );
			$wpssw_customer = new WP_User( $wpssw_customerid );
			$wpssw_customer->save();
		}
		/**
		 * Update post meta
		 *
		 * @param int          $wpssw_customerid post id to update post meta.
		 * @param string       $wpssw_meta_key meta key to update.
		 * @param string|array $wpssw_data value for meta key.
		 */
		public static function wpssw_update_user_meta( $wpssw_customerid, $wpssw_meta_key, $wpssw_data ) {
			if ( ! $wpssw_customerid ) {
				return;
			}
			update_user_meta( $wpssw_customerid, $wpssw_meta_key, $wpssw_data );
		}
	}
	new WPSSW_Customer_Import();
endif;
