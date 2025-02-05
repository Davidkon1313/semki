<?php
/**
 * Main WPSyncSheets_For_WooCommerce namespace.
 *
 * @since 1.0.0
 * @package wpsyncsheets-for-woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }
if ( ! class_exists( 'WPSSW_Customer' ) ) :
	/**
	 * Class WPSSW_Customer.
	 */
	class WPSSW_Customer extends WPSSW_Setting {
		/**
		 * Initialization
		 */
		public function __construct() {
			$wpssw_include = new WPSSW_Include_Action();
			$wpssw_include->wpssw_include_customer_hook();
			$wpssw_include->wpssw_include_customer_ajax_hook();
		}
		/**
		 *
		 * Save Customer settings tab's setting
		 */
		public static function wpssw_update_customer_settings() {

			if ( ! isset( $_POST['wpssw_customer_settings'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wpssw_customer_settings'] ) ), 'save_customer_settings' ) ) {
				echo esc_html__( 'Sorry, your nonce did not verify.', 'wpssw' );
				die();
			}
			if ( isset( $_POST['woocustomer_header_list'] ) && isset( $_POST['woocustomer_custom'] ) ) {
				$wpssw_woo_customer_headers        = array_map( 'sanitize_text_field', wp_unslash( $_POST['woocustomer_header_list'] ) );
				$wpssw_woo_customer_headers_custom = array_map( 'sanitize_text_field', wp_unslash( $_POST['woocustomer_custom'] ) );
				if ( isset( $_POST['customer_settings_checkbox'] ) ) {
					if ( isset( $_POST['cust_inherit_style'] ) ) {
						$wpssw_cust_inherit_style = sanitize_text_field( wp_unslash( $_POST['cust_inherit_style'] ) );
						parent::wpssw_update_option( 'wpssw_customer_inherit_style', $wpssw_cust_inherit_style );
					} else {
						parent::wpssw_update_option( 'wpssw_customer_inherit_style', 'none' );
					}
					if ( isset( $_POST['custsheetselection'] ) && 'new' === (string) sanitize_text_field( wp_unslash( $_POST['custsheetselection'] ) ) ) {
						$wpssw_newsheetname = isset( $_POST['customer_spreadsheet_name'] ) ? trim( sanitize_text_field( wp_unslash( $_POST['customer_spreadsheet_name'] ) ) ) : '';

						/*
						 *Create new spreadsheet
						 */
						$wpssw_requestbody   = self::$instance_api->createspreadsheetobject( $wpssw_newsheetname );
						$wpssw_response      = self::$instance_api->createspreadsheet( $wpssw_requestbody );
						$wpssw_spreadsheetid = $wpssw_response['spreadsheetId'];
					} else {
						$wpssw_spreadsheetid = isset( $_POST['customer_spreadsheet'] ) ? sanitize_text_field( wp_unslash( $_POST['customer_spreadsheet'] ) ) : '';
					}
					parent::wpssw_update_option( 'wpssw_customer_spreadsheet_id', $wpssw_spreadsheetid );
					parent::wpssw_update_option( 'wpssw_customer_spreadsheet_setting', 'yes' );
				} else {
					parent::wpssw_update_option( 'wpssw_customer_spreadsheet_setting', 'no' );
					parent::wpssw_update_option( 'wpssw_customer_spreadsheet_id', '' );

					$_POST['cust_autosync_scheduling_enable']   = 0;
					$_POST['cust_autoimport_scheduling_enable'] = 0;

					self::wpssw_update_customer_schedule_data( $_POST, 'auto_sync' );
					self::wpssw_update_customer_schedule_data( $_POST, 'auto_import' );
					return;
				}
				if ( isset( $_POST['import_customer_checkbox'] ) ) {
					parent::wpssw_update_option( 'wpssw_customer_import', 1 );

				} else {

					parent::wpssw_update_option( 'wpssw_customer_import', '' );

					$customer_scheduling_enable = parent::wpssw_option( 'wpssw_customer_autoimport_scheduling_enable' );
					if ( 1 === (int) $customer_scheduling_enable ) {
						$_POST['cust_autoimport_scheduling_enable'] = 0;
					}
				}

				if ( isset( $_POST['insert_customer_checkbox'] ) ) {

					parent::wpssw_update_option( 'wpssw_customer_insert', 1 );

				} else {

					parent::wpssw_update_option( 'wpssw_customer_insert', '' );

				}

				if ( isset( $_POST['update_customer_checkbox'] ) ) {

					parent::wpssw_update_option( 'wpssw_customer_update', 1 );

				} else {

					parent::wpssw_update_option( 'wpssw_customer_update', '' );

				}

				if ( isset( $_POST['delete_customer_checkbox'] ) ) {

					parent::wpssw_update_option( 'wpssw_customer_delete', 1 );

				} else {

					parent::wpssw_update_option( 'wpssw_customer_delete', '' );

				}

				$wpssw_sheetname      = 'All Customers';
				$requestarray         = array();
				$deleterequestarray   = array();
				$response             = self::$instance_api->get_sheet_listing( $wpssw_spreadsheetid );
				$wpssw_existingsheets = self::$instance_api->get_sheet_list( $response );
				$wpssw_existingsheets = array_flip( $wpssw_existingsheets );
				$wpssw_inputoption    = parent::wpssw_option( 'wpssw_inputoption' );
				if ( ! $wpssw_inputoption ) {
					$wpssw_inputoption = 'USER_ENTERED';
				}
				if ( count( $wpssw_woo_customer_headers ) > 0 ) {
					array_unshift( $wpssw_woo_customer_headers, 'Customer Id' );
				}
				if ( count( $wpssw_woo_customer_headers_custom ) > 0 ) {
					array_unshift( $wpssw_woo_customer_headers_custom, 'Customer Id' );
				}
				$wpssw_old_header_customer = parent::wpssw_option( 'wpssw_woo_customer_headers' );
				if ( empty( $wpssw_old_header_customer ) ) {
					$wpssw_old_header_customer = array();
				}
				if ( count( $wpssw_old_header_customer ) > 0 ) {
					array_unshift( $wpssw_old_header_customer, 'Customer Id' );
				}
				if ( ! in_array( $wpssw_sheetname, $wpssw_existingsheets, true ) ) {
					$param                  = array();
					$param['spreadsheetid'] = $wpssw_spreadsheetid;
					$param['sheetname']     = $wpssw_sheetname;
					$wpssw_response         = self::$instance_api->newsheetobject( $param );
					$wpssw_range            = trim( $wpssw_sheetname ) . '!A1';
					$wpssw_requestbody      = self::$instance_api->valuerangeobject( array( $wpssw_woo_customer_headers_custom ) );
					$wpssw_params           = array( 'valueInputOption' => $wpssw_inputoption );
					$param                  = self::$instance_api->setparamater( $wpssw_spreadsheetid, $wpssw_range, $wpssw_requestbody, $wpssw_params );
					$wpssw_response         = self::$instance_api->appendentry( $param );
				}
				if ( 'new' === (string) sanitize_text_field( wp_unslash( $_POST['custsheetselection'] ) ) ) {
					$param                  = array();
					$param['spreadsheetid'] = $wpssw_spreadsheetid;
					$wpssw_response         = self::$instance_api->deletesheetobject( $param );
				}
				if ( $wpssw_old_header_customer !== $wpssw_woo_customer_headers && in_array( $wpssw_sheetname, $wpssw_existingsheets, true ) ) {
					$wpssw_existingsheets = array();
					$response             = self::$instance_api->get_sheet_listing( $wpssw_spreadsheetid );
					$wpssw_existingsheets = self::$instance_api->get_sheet_list( $response );
					$wpssw_existingsheets = array_flip( $wpssw_existingsheets );
					// Delete deactivate column from sheet.
					$wpssw_column = array_diff( $wpssw_old_header_customer, $wpssw_woo_customer_headers );
					if ( ! empty( $wpssw_column ) ) {
						$wpssw_column = array_reverse( $wpssw_column, true );
						foreach ( $wpssw_column as $columnindex => $columnval ) {
							unset( $wpssw_old_header_customer[ $columnindex ] );
							$wpssw_old_header_customer = array_values( $wpssw_old_header_customer );
							if ( in_array( $wpssw_sheetname, $wpssw_existingsheets, true ) ) {
								$wpssw_sheetid = array_search( $wpssw_sheetname, $wpssw_existingsheets, true );
								if ( $wpssw_sheetid ) {
									$param                = array();
									$startindex           = $columnindex;
									$endindex             = $columnindex + 1;
									$param                = self::$instance_api->prepare_param( $wpssw_sheetid, $startindex, $endindex );
									$deleterequestarray[] = self::$instance_api->deleteDimensionrequests( $param );
								}
							}
						}
					}
					try {
						if ( ! empty( $deleterequestarray ) ) {
							$param                  = array();
							$param['spreadsheetid'] = $wpssw_spreadsheetid;
							$param['requestarray']  = $deleterequestarray;
							$wpssw_response         = self::$instance_api->updatebachrequests( $param );
						}
					} catch ( Exception $e ) {
						echo esc_html( 'Message: ' . $e->getMessage() );
					}
				}
				if ( $wpssw_old_header_customer !== $wpssw_woo_customer_headers ) {
					foreach ( $wpssw_woo_customer_headers as $key => $hname ) {
						if ( 'Customer Id' === (string) $hname ) {
							continue;
						}
						$wpssw_startindex = array_search( $hname, $wpssw_old_header_customer, true );
						if ( false !== $wpssw_startindex && ( isset( $wpssw_old_header_customer[ $key ] ) && $wpssw_old_header_customer[ $key ] !== $hname ) ) {
							unset( $wpssw_old_header_customer[ $wpssw_startindex ] );
							$wpssw_old_header_customer = array_merge( array_slice( $wpssw_old_header_customer, 0, $key ), array( 0 => $hname ), array_slice( $wpssw_old_header_customer, $key, count( $wpssw_old_header_customer ) - $key ) );
							$wpssw_endindex            = $wpssw_startindex + 1;
							$wpssw_destindex           = $key;
							if ( in_array( $wpssw_sheetname, $wpssw_existingsheets, true ) ) {
								$wpssw_sheetid = array_search( $wpssw_sheetname, $wpssw_existingsheets, true );
								if ( $wpssw_sheetid ) {
									$param              = array();
									$param              = self::$instance_api->prepare_param( $wpssw_sheetid, $wpssw_startindex, $wpssw_endindex );
									$param['destindex'] = $wpssw_destindex;
									$requestarray[]     = self::$instance_api->moveDimensionrequests( $param );
								}
							}
						} elseif ( false === (bool) $wpssw_startindex ) {
							$wpssw_old_header_customer = array_merge( array_slice( $wpssw_old_header_customer, 0, $key ), array( 0 => $hname ), array_slice( $wpssw_old_header_customer, $key, count( $wpssw_old_header_customer ) - $key ) );
							if ( in_array( $wpssw_sheetname, $wpssw_existingsheets, true ) ) {
								$wpssw_sheetid = array_search( $wpssw_sheetname, $wpssw_existingsheets, true );
								if ( $wpssw_sheetid ) {
									$param                  = array();
									$wpssw_startindex       = $key;
									$wpssw_endindex         = $key + 1;
									$param                  = self::$instance_api->prepare_param( $wpssw_sheetid, $wpssw_startindex, $wpssw_endindex );
									$customer_inherit_style = parent::wpssw_option( 'wpssw_customer_inherit_style' );
									if ( 'no' === (string) $customer_inherit_style ) {
										$requestarray[] = self::$instance_api->insertdimensionrequests( $param, 'COLUMNS', false );
									} else {
										$requestarray[] = self::$instance_api->insertdimensionrequests( $param, 'COLUMNS', true );
									}
								}
							}
						}
					}
					if ( ! empty( $requestarray ) ) {
						$param                  = array();
						$param['spreadsheetid'] = $wpssw_spreadsheetid;
						$param['requestarray']  = $requestarray;
						$wpssw_response         = self::$instance_api->updatebachrequests( $param );
					}
				}
				$freeze_header        = parent::wpssw_option( 'freeze_header' );
				$wpssw_existingsheets = array();
				$response             = self::$instance_api->get_sheet_listing( $wpssw_spreadsheetid );
				$wpssw_existingsheets = self::$instance_api->get_sheet_list( $response );
				$wpssw_existingsheets = array_flip( $wpssw_existingsheets );
				if ( 'yes' === (string) $freeze_header ) {
					$wpssw_freeze = 1;
				} else {
					$wpssw_freeze = 0;
				}
				if ( in_array( $wpssw_sheetname, $wpssw_existingsheets, true ) ) {
					$wpssw_sheetid = array_search( $wpssw_sheetname, $wpssw_existingsheets, true );
					// freeze customer headers.
					$wpssw_requestbody = self::$instance_api->freezeobject( $wpssw_sheetid, $wpssw_freeze );
					try {
						$requestbody                    = new Google_Service_Sheets_BatchUpdateSpreadsheetRequest(
							array( 'requests' => $wpssw_requestbody )
						);
						$requestobject                  = array();
						$requestobject['spreadsheetid'] = $wpssw_spreadsheetid;
						$requestobject['requestbody']   = $requestbody;
						$wpssw_response                 = self::$instance_api->formatsheet( $requestobject );
					} catch ( Exception $e ) {
						echo esc_html( 'Message: ' . $e->getMessage() );
					}
				}
				if ( in_array( $wpssw_sheetname, $wpssw_existingsheets, true ) ) {
					$wpssw_range       = trim( $wpssw_sheetname ) . '!A1';
					$wpssw_requestbody = self::$instance_api->valuerangeobject( array( $wpssw_woo_customer_headers_custom ) );
					$wpssw_params      = array( 'valueInputOption' => $wpssw_inputoption );
					$param             = self::$instance_api->setparamater( $wpssw_spreadsheetid, $wpssw_range, $wpssw_requestbody, $wpssw_params );
					$wpssw_response    = self::$instance_api->updateentry( $param );
				}
				parent::wpssw_update_option( 'wpssw_woo_customer_headers', array_map( 'sanitize_text_field', wp_unslash( $_POST['woocustomer_header_list'] ) ) );
				parent::wpssw_update_option( 'wpssw_woo_customer_headers_custom', array_map( 'sanitize_text_field', wp_unslash( $_POST['woocustomer_custom'] ) ) );

				/*
				 * Update Static Headers
				 */
				if ( isset( $_POST['customer_header_fields_static'] ) && is_array( $_POST['customer_header_fields_static'] ) ) {
					$wpssw_customer_static_header = array_map( 'sanitize_text_field', wp_unslash( $_POST['customer_header_fields_static'] ) );
					parent::wpssw_update_option( 'wpssw_customer_static_header', $wpssw_customer_static_header );
				}
				if ( isset( $_POST['wpssw_static_customer_header_values'] ) && is_array( $_POST['wpssw_static_customer_header_values'] ) ) {
					$wpssw_static_customer_header_values = array_map( 'sanitize_text_field', wp_unslash( $_POST['wpssw_static_customer_header_values'] ) );
					parent::wpssw_update_option( 'wpssw_static_customer_header_values', $wpssw_static_customer_header_values );
				}
				/* Schedule Auto Sync */
				if ( isset( $_POST['cust_autosync_scheduling_enable'] ) ) {
					self::wpssw_update_customer_schedule_data( $_POST, 'auto_sync' );
				}
				/* Schedule Auto Import */
				if ( isset( $_POST['cust_autoimport_scheduling_enable'] ) ) {
					self::wpssw_update_customer_schedule_data( $_POST, 'auto_import' );
				}
			}
		}
		/**
		 * Add new user (created while checkout) data into sheet
		 *
		 * @param int   $customer_id .
		 * @param array $data contains user data.
		 */
		public static function action_woocommerce_checkout_update_customer( $customer_id, $data ) {
			self::wpssw_insert_customer_data_into_sheet( $customer_id );
		}
		/**
		 * Insert / Update user data into sheet on user update
		 *
		 * @param int $user_id user id.
		 */
		public static function edit_user_profile_update( $user_id ) {

			$user = new WP_User( $user_id );
			// @codingStandardsIgnoreStart.
			$data = array_map( 'sanitize_text_field', wp_unslash( $_POST ) );
			// @codingStandardsIgnoreEnd.
			if ( isset( $data['action'] ) && 'update' === (string) $data['action'] ) {
				$role = $data['role'];
			}
			if ( isset( $data['action'] ) && 'save_account_details' === (string) $data['action'] ) {
				$user                 = new WP_User( $user_id );
				$wpssw_customer_roles = array_values( $user->roles );
				$role                 = $wpssw_customer_roles[0];
				$data['user_id']      = $user_id;
				$data['role']         = $role;
			}
			if ( isset( $data['action'] ) && in_array( $data['action'], array( 'update', 'save_account_details' ), true ) ) {
				self::wpssw_insert_customer_data_into_sheet( $user_id, $data );
				return;
			}
			self::wpssw_insert_customer_data_into_sheet( $user_id );
		}
		/**
		 * Add new user data into sheet
		 *
		 * @param int $user_id user id.
		 */
		public static function wpssw_user_registration_save( $user_id ) {
			$data = array();
			// @codingStandardsIgnoreStart.
			$data = array_map( 'sanitize_text_field', wp_unslash( $_POST ) );
			// @codingStandardsIgnoreEnd.
			if ( isset( $data['action'] ) && 'createuser' === (string) $data['action'] ) {
				$role            = $data['role'];
				$data['user_id'] = $user_id;
				self::wpssw_insert_customer_data_into_sheet( $user_id, $data );
				return;
			}
			self::wpssw_insert_customer_data_into_sheet( $user_id );
		}
		/**
		 * Delete customer's data from sheet on trashing user
		 *
		 * @param int $user_id user id.
		 */
		public static function wpssw_delete_user( $user_id ) {
			if ( ! self::$instance_api->checkcredenatials() ) {
				return;
			}

			// @codingStandardsIgnoreStart.
			if ( isset( $_POST['users'] ) && is_array( $_POST['users'] ) && count( $_POST['users'] ) > 0 && isset( $_POST['action'] ) && 'dodelete' === sanitize_text_field( wp_unslash( $_POST['action'] )  ) ) {
				$changed_users = array_map( 'sanitize_text_field', wp_unslash( $_POST['users'] ) );
				// @codingStandardsIgnoreEnd.

				if ( (int) $user_id === (int) $changed_users[ count( $changed_users ) - 1 ] ) {
					$wpssw_spreadsheetid                = parent::wpssw_option( 'wpssw_customer_spreadsheet_id' );
					$wpssw_customer_spreadsheet_setting = parent::wpssw_option( 'wpssw_customer_spreadsheet_setting' );
					$wpssw_sheetname                    = 'All Customers';

					$settings = array(
						'setting'        => 'customer',
						'setting_enable' => $wpssw_customer_spreadsheet_setting,
						'spreadsheet_id' => $wpssw_spreadsheetid,
						'sheetname'      => $wpssw_sheetname,
					);
					parent::wpssw_multiple_update_data( $changed_users, $settings, false, 'delete' );
				}
				return;
			}

			$wpssw_customer_spreadsheet_setting = parent::wpssw_option( 'wpssw_customer_spreadsheet_setting' );

			if ( 'yes' !== (string) $wpssw_customer_spreadsheet_setting ) {
				return;
			}

			$wpssw_spreadsheetid = parent::wpssw_option( 'wpssw_customer_spreadsheet_id' );
			$wpssw_sheetname     = 'All Customers';
			if ( ! self::wpssw_check_sheet_exist( $wpssw_spreadsheetid, $wpssw_sheetname ) ) {
				return;
			}
			$customer             = get_userdata( $user_id );
			$wpssw_customer_roles = array_values( $customer->roles );
			$role                 = $wpssw_customer_roles[0];

			$wpssw_sheet    = "'" . $wpssw_sheetname . "'!A:A";
			$wpssw_allentry = self::$instance_api->get_row_list( $wpssw_spreadsheetid, $wpssw_sheet );
			$wpssw_data     = $wpssw_allentry->getValues();
			$wpssw_allentry = null;
			$wpssw_data     = array_map(
				function( $wpssw_element ) {
					if ( isset( $wpssw_element['0'] ) ) {
						return $wpssw_element['0'];
					} else {
						return '';
					}
				},
				$wpssw_data
			);
			$wpssw_num      = array_search( (int) $user_id, parent::wpssw_convert_int( $wpssw_data ), true );
			if ( $wpssw_num > 0 ) {
				$response                  = self::$instance_api->get_sheet_listing( $wpssw_spreadsheetid );
				$wpssw_existingsheetsnames = self::$instance_api->get_sheet_list( $response );
				$wpssw_sheetid             = $wpssw_existingsheetsnames[ $wpssw_sheetname ];
				$wpssw_startindex          = $wpssw_num;
				$wpssw_endindex            = $wpssw_num + 1;
				$param                     = self::$instance_api->prepare_param( $wpssw_sheetid, $wpssw_startindex, $wpssw_endindex );
				$deleterequest             = self::$instance_api->deleteDimensionrequests( $param, 'ROWS' );
				try {
					if ( ! empty( $deleterequest ) ) {
						$param                  = array();
						$param['spreadsheetid'] = $wpssw_spreadsheetid;
						$param['requestarray']  = $deleterequest;
						self::$instance_api->updatebachrequests( $param );
					}
				} catch ( Exception $e ) {
					echo esc_html( 'Message: ' . $e->getMessage() );
				}
			}
		}
		/**
		 * Clean Customer data array.
		 *
		 * @param array $wpssw_array customer data array.
		 */
		public static function wpssw_customercleanArray( $wpssw_array ) {
			$wpssw_max = count( parent::wpssw_option( 'wpssw_woo_customer_headers' ) ) + 1;
			for ( $i = 0; $i < $wpssw_max; $i++ ) {
				if ( ! isset( $wpssw_array[ $i ] ) || is_null( $wpssw_array[ $i ] ) ) {
					$wpssw_array[ $i ] = '';
				} else {
					$wpssw_array[ $i ] = trim( $wpssw_array[ $i ] );
				}
			}
			ksort( $wpssw_array );
			return $wpssw_array;
		}
		/**
		 *
		 * Get customers count for syncronization
		 */
		public static function wpssw_get_customer_count() {

			if ( ! isset( $_POST['wpssw_customer_settings'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wpssw_customer_settings'] ) ), 'save_customer_settings' ) ) {
				echo 'error';
				die();
			}
			if ( ! self::$instance_api->checkcredenatials() ) {
				echo 'error';
				die();
			}
			$wpssw_customer_spreadsheet_setting = parent::wpssw_option( 'wpssw_customer_spreadsheet_setting' );
			$wpssw_spreadsheetid                = parent::wpssw_option( 'wpssw_customer_spreadsheet_id' );

			if ( 'yes' !== (string) $wpssw_customer_spreadsheet_setting ) {
				return;
			}
			$wpssw_sheetname = 'All Customers';
			$wpssw_sheet     = "'" . $wpssw_sheetname . "'!A:A";
			$wpssw_allentry  = self::$instance_api->get_row_list( $wpssw_spreadsheetid, $wpssw_sheet );
			$wpssw_data      = $wpssw_allentry->getValues();
			$wpssw_allentry  = null;
			$wpssw_data      = array_map(
				function( $wpssw_element ) {
					if ( isset( $wpssw_element['0'] ) ) {
						return (int) $wpssw_element['0'];
					} else {
						return '';
					}
				},
				$wpssw_data
			);

			$wpssw_fromdate = isset( $_POST['cust_sync_all_fromdate'] ) ? sanitize_text_field( wp_unslash( $_POST['cust_sync_all_fromdate'] ) ) : '';
			$wpssw_todate   = isset( $_POST['cust_sync_all_todate'] ) ? sanitize_text_field( wp_unslash( $_POST['cust_sync_all_todate'] ) ) : '';
			$wpssw_syncall  = isset( $_POST['cust_sync_all'] ) ? sanitize_text_field( wp_unslash( $_POST['cust_sync_all'] ) ) : '';

			if ( isset( $wpssw_fromdate ) && isset( $wpssw_todate ) ) {
				$args = array(
					'role'       => 'customer',
					'orderby'    => 'ID',
					'order'      => 'ASC',
					'date_query' => array(
						array(
							'after'     => $wpssw_fromdate,
							'before'    => $wpssw_todate,
							'inclusive' => true,
						),
					),
				);
			} else {
				$args = array(
					'role'    => 'customer',
					'orderby' => 'ID',
					'order'   => 'ASC',
				);
			}
			$args['fields']  = 'ID'; // Fetch only ids.
			$args['exclude'] = $wpssw_data;
			$wpssw_data      = null;
			$customers       = get_users( $args );

			if ( empty( $customers ) ) {
				echo 'notfound';
				die();
			}

			$total         = count( $customers );
			$customerlimit = apply_filters( 'wpssw_customer_sync_limit', 500 );
			echo wp_json_encode(
				array(
					'totalcustomers' => $total,
					'customerlimit'  => $customerlimit,
				)
			);
			die;
		}
		/**
		 *
		 * Sync Customers
		 */
		public static function wpssw_sync_customers() {
			if ( ! isset( $_POST['wpssw_customer_settings'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wpssw_customer_settings'] ) ), 'save_customer_settings' ) ) {
				echo esc_html__( 'Sorry, your nonce did not verify.', 'wpssw' );
				die();
			}
			$wpssw_spreadsheetid = parent::wpssw_option( 'wpssw_customer_spreadsheet_id' );

			$wpssw_sheetname = 'All Customers';
			$wpssw_sheet     = "'" . $wpssw_sheetname . "'!A:A";
			$wpssw_allentry  = self::$instance_api->get_row_list( $wpssw_spreadsheetid, $wpssw_sheet );
			$wpssw_data      = $wpssw_allentry->getValues();
			$wpssw_data      = array_map(
				function( $wpssw_element ) {
					if ( isset( $wpssw_element['0'] ) ) {
						return (int) $wpssw_element['0'];
					} else {
						return '';
					}
				},
				$wpssw_data
			);

			$wpssw_customercount = isset( $_POST['customercount'] ) ? sanitize_text_field( wp_unslash( $_POST['customercount'] ) ) : '';
			$wpssw_customerlimit = isset( $_POST['customerlimit'] ) ? sanitize_text_field( wp_unslash( $_POST['customerlimit'] ) ) : '';

			$wpssw_fromdate = isset( $_POST['cust_sync_all_fromdate'] ) ? sanitize_text_field( wp_unslash( $_POST['cust_sync_all_fromdate'] ) ) : '';
			$wpssw_todate   = isset( $_POST['cust_sync_all_todate'] ) ? sanitize_text_field( wp_unslash( $_POST['cust_sync_all_todate'] ) ) : '';
			$wpssw_syncall  = isset( $_POST['cust_sync_all'] ) ? sanitize_text_field( wp_unslash( $_POST['cust_sync_all'] ) ) : '';

			if ( isset( $wpssw_fromdate ) && isset( $wpssw_todate ) ) {
				$args = array(
					'role'       => 'customer',
					'orderby'    => 'ID',
					'order'      => 'ASC',
					'date_query' => array(
						array(
							'after'     => $wpssw_fromdate,
							'before'    => $wpssw_todate,
							'inclusive' => true,
						),
					),
				);
			} else {
				$args = array(
					'role'    => 'customer',
					'orderby' => 'ID',
					'order'   => 'ASC',
				);
			}
			$args['fields']  = 'ID'; // Fetch only ids.
			$args['exclude'] = $wpssw_data;
			$args['number']  = $wpssw_customerlimit;

			$wpssw_data_count = count( $wpssw_data );

			$wpssw_allentry = null;
			$wpssw_data     = null;

			$customers = get_users( $args );

			if ( empty( $customers ) ) {
				die();
			}

			$rangetofind        = $wpssw_sheetname . '!A' . ( $wpssw_data_count + 1 );
			$wpssw_values_array = array();
			$newcustomer        = 0;
			foreach ( $customers as $customer_id ) {
				if ( ! empty( $customer_id ) && $newcustomer < $wpssw_customerlimit ) {
					set_time_limit( 999 );
					$customer           = new WP_User( $customer_id );
					$wpssw_value        = self::wpssw_make_customer_value_array( 'insert', $customer );
					$wpssw_values_array = array_merge( $wpssw_values_array, $wpssw_value );
					$newcustomer++;
				}
			}
			$wpssw_sheet = "'" . $wpssw_sheetname . "'!A:A2";
			if ( ! empty( $wpssw_values_array ) ) {
				try {
					// If there is no sheet_id available, leave the sheet_id field blank. Otherwise, if there is a sheet_id, leave the sheet_name field blank.
					$wpssw_inherit_params = array(
						'sheet_id'       => '',
						'sheet_name'     => $wpssw_sheetname,
						'start_index'    => $wpssw_data_count,
						'end_index'      => $wpssw_data_count + count( $wpssw_values_array ),
						'spreadsheet_id' => $wpssw_spreadsheetid,
					);
					parent::wpssw_inherit_row_format_style( 'customer', $wpssw_inherit_params );

					$wpssw_inputoption = parent::wpssw_option( 'wpssw_inputoption' );
					if ( ! $wpssw_inputoption ) {
						$wpssw_inputoption = 'USER_ENTERED';
					}
					$wpssw_requestbody = self::$instance_api->valuerangeobject( $wpssw_values_array );
					$wpssw_params      = array( 'valueInputOption' => $wpssw_inputoption );
					$param             = self::$instance_api->setparamater( $wpssw_spreadsheetid, $rangetofind, $wpssw_requestbody, $wpssw_params );
					$wpssw_response    = self::$instance_api->appendentry( $param );

				} catch ( Exception $e ) {
					echo esc_html( 'Message: ' . $e->getMessage() );
				}
			}
			echo 'successful';
			die;
		}
		/**
		 * Prepare array value of customer data to insert into sheet.
		 *
		 * @param string       $wpssw_operation operation to perform on sheet.
		 * @param array|object $wpssw_customer customer data.
		 * @return array $customer_value_array
		 */
		public static function wpssw_make_customer_value_array( $wpssw_operation = 'insert', $wpssw_customer = '' ) {
			if ( ! $wpssw_customer ) {
				return array();
			}
			$wpssw_include = new WPSSW_Include_Action();
			$wpssw_include->wpssw_include_customer_compatibility_files();
			$wpssw_headers = apply_filters( 'wpsyncsheets_customer_headers', array() );

			$wpssw_static_customer_header_values = stripslashes_deep( parent::wpssw_option( 'wpssw_static_customer_header_values' ) );
			$wpssw_custom_value                  = array();
			$wpssw_static_header_name            = array();
			if ( ! empty( $wpssw_static_customer_header_values ) ) {
				foreach ( $wpssw_static_customer_header_values as $wpssw_static_header_value ) {
					if ( strpos( $wpssw_static_header_value, ',(static_header),' ) ) {
						$wpssw_static_header_value = str_replace( ',(static_header),', ',', $wpssw_static_header_value );
						$wpssw_custom_value[]      = explode( ',', $wpssw_static_header_value );
					}
				}
				if ( ! empty( $wpssw_custom_value ) ) {
					$wpssw_static_header_name = array_column( $wpssw_custom_value, 0 );
				}
			}

			$wpssw_profile_image = '';
			$customer_id         = '';
			if ( 'object' === gettype( $wpssw_customer ) ) {
				$wpssw_customer_row[]  = $wpssw_customer->ID;
				$customer_id           = $wpssw_customer->ID;
				$customers_metadata    = get_user_meta( $wpssw_customer->ID );
				$wpssw_customer_roles  = array_values( $wpssw_customer->roles );
				$wpssw_customer_values = $wpssw_customer->data;
				$wpssw_profile_image   = '=IMAGE("' . get_avatar_url( $wpssw_customer->ID ) . '")';
			}
			if ( 'array' === gettype( $wpssw_customer ) ) {
				$wpssw_customer_row[]  = $wpssw_customer['user_id'];
				$customer_id           = $wpssw_customer['user_id'];
				$user                  = new WP_User( $wpssw_customer['user_id'] );
				$customers_metadata    = get_user_meta( $wpssw_customer['user_id'] );
				$wpssw_customer_values = $user->data;
				$wpssw_customer_roles  = array( $wpssw_customer['role'] );
				$wpssw_profile_image   = '=IMAGE("' . get_avatar_url( $wpssw_customer['user_id'] ) . '")';
				if ( 'save_account_details' === (string) $wpssw_customer['action'] ) {
					$customers_metadata['first_name'][0] = $wpssw_customer['account_first_name'];
					$customers_metadata['last_name'][0]  = $wpssw_customer['account_last_name'];
					$wpssw_customer_values->user_email   = $wpssw_customer['account_email'];
				} elseif ( 'update' === (string) $wpssw_customer['action'] ) {
					$wpssw_customer_values->user_email            = $wpssw_customer['email'];
					$wpssw_customer_values->user_url              = $wpssw_customer['url'];
					$customers_metadata['first_name'][0]          = $wpssw_customer['first_name'];
					$customers_metadata['last_name'][0]           = $wpssw_customer['last_name'];
					$customers_metadata['nickname'][0]            = $wpssw_customer['nickname'];
					$customers_metadata['description'][0]         = $wpssw_customer['description'];
					$customers_metadata['billing_first_name'][0]  = $wpssw_customer['billing_first_name'];
					$customers_metadata['billing_last_name'][0]   = $wpssw_customer['billing_last_name'];
					$customers_metadata['billing_company'][0]     = $wpssw_customer['billing_company'];
					$customers_metadata['billing_address_1'][0]   = $wpssw_customer['billing_address_1'];
					$customers_metadata['billing_address_2'][0]   = $wpssw_customer['billing_address_2'];
					$customers_metadata['billing_city'][0]        = $wpssw_customer['billing_city'];
					$customers_metadata['billing_postcode'][0]    = $wpssw_customer['billing_postcode'];
					$customers_metadata['billing_country'][0]     = $wpssw_customer['billing_country'];
					$customers_metadata['billing_state'][0]       = $wpssw_customer['billing_state'];
					$customers_metadata['billing_phone'][0]       = $wpssw_customer['billing_phone'];
					$customers_metadata['billing_email'][0]       = $wpssw_customer['billing_email'];
					$customers_metadata['shipping_first_name'][0] = $wpssw_customer['shipping_first_name'];
					$customers_metadata['shipping_last_name'][0]  = $wpssw_customer['shipping_last_name'];
					$customers_metadata['shipping_company'][0]    = $wpssw_customer['shipping_company'];
					$customers_metadata['shipping_address_1'][0]  = $wpssw_customer['shipping_address_1'];
					$customers_metadata['shipping_address_2'][0]  = $wpssw_customer['shipping_address_2'];
					$customers_metadata['shipping_city'][0]       = $wpssw_customer['shipping_city'];
					$customers_metadata['shipping_postcode'][0]   = $wpssw_customer['shipping_postcode'];
					$customers_metadata['shipping_country'][0]    = $wpssw_customer['shipping_country'];
					$customers_metadata['shipping_state'][0]      = $wpssw_customer['shipping_state'];
				} elseif ( 'createuser' === (string) $wpssw_customer['action'] ) {
					$customers_metadata['first_name'][0] = $wpssw_customer['first_name'];
					$customers_metadata['last_name'][0]  = $wpssw_customer['last_name'];
				}
			}
			$customers_metadata['profile_image'] = $wpssw_profile_image;
			$customers_metadata['user_url']      = $wpssw_customer_values->user_url;
			$customers_metadata['user_email']    = $wpssw_customer_values->user_email;
			$customers_metadata['roles']         = $wpssw_customer_roles;
			$customers_metadata['user_login']    = $wpssw_customer_values->user_login;
			$wpssw_woo_selections                = stripslashes_deep( parent::wpssw_option( 'wpssw_woo_customer_headers' ) );
			$wpssw_classarray                    = array();
			$wpssw_woo_selections_count          = count( $wpssw_woo_selections );
			for ( $i = 0; $i < $wpssw_woo_selections_count; $i++ ) {

				if ( in_array( $wpssw_woo_selections[ $i ], $wpssw_static_header_name, true ) ) {
					$wpssw_classarray[ $wpssw_woo_selections[ $i ] ] = 'WPSSW_Customer_Headers';
					continue;
				}

				$wpssw_classarray[ $wpssw_woo_selections[ $i ] ] = parent::wpssw_find_class( $wpssw_headers, $wpssw_woo_selections[ $i ] );
			}

			foreach ( $wpssw_classarray as $headername => $classname ) {
				if ( ! empty( $classname ) ) {
					$wpssw_customer_row[] = $classname::get_value( $headername, $customers_metadata, $customer_id, $wpssw_customer, $wpssw_custom_value );
				} else {
					$wpssw_customer_row[] = '';
				}
			}
			$wpssw_customer_row = self::wpssw_customercleanArray( $wpssw_customer_row );
			return array( $wpssw_customer_row );
		}
		/**
		 * Insert customers data into sheet.
		 *
		 * @param int   $user_id customer id.
		 * @param array $data customer data array.
		 */
		public static function wpssw_insert_customer_data_into_sheet( $user_id, $data = array() ) {
			try {
				if ( ! $user_id ) {
					return;
				}
				if ( ! self::$instance_api->checkcredenatials() ) {
					return;
				}
				$wpssw_spreadsheetid = parent::wpssw_option( 'wpssw_customer_spreadsheet_id' );
				$wpssw_sheetname     = 'All Customers';
				if ( ! parent::wpssw_check_sheet_exist( $wpssw_spreadsheetid, $wpssw_sheetname ) ) {
					return;
				}
				$wpssw_inputoption = parent::wpssw_option( 'wpssw_inputoption' );
				if ( ! $wpssw_inputoption ) {
					$wpssw_inputoption = 'USER_ENTERED';
				}
				$wpssw_headers_name = parent::wpssw_option( 'wpssw_woo_customer_headers' );
				if ( ! empty( $data ) && ( isset( $data['action'] ) && 'wpssw_customer_import' !== (string) $data['action'] && 'wpssw_autoimport_customers_cron_run' !== (string) $data['action'] ) ) {
					$customer = $data;
					$role     = isset( $data['role'] ) ? $data['role'] : '';
				} else {
					$customer             = get_userdata( $user_id );
					$wpssw_customer_roles = ( false !== $customer && null !== $customer && ! is_wp_error( $customer ) ) ? array_values( $customer->roles ) : array();
					$role                 = isset( $wpssw_customer_roles[0] ) ? $wpssw_customer_roles[0] : '';
				}
				if ( empty( $role ) || false === $customer || null === $customer || is_wp_error( $customer ) ) {
					return;
				}
				$wpssw_sheetname = 'All Customers';
				$wpssw_sheet     = "'" . $wpssw_sheetname . "'!A:A";
				$wpssw_allentry  = self::$instance_api->get_row_list( $wpssw_spreadsheetid, $wpssw_sheet );
				$wpssw_data      = $wpssw_allentry->getValues();
				$wpssw_allentry  = null;
				$wpssw_data      = array_map(
					function( $wpssw_element ) {
						if ( isset( $wpssw_element['0'] ) ) {
							return $wpssw_element['0'];
						} else {
							return '';
						}
					},
					$wpssw_data
				);

				$is_exists = array_search( (int) $user_id, parent::wpssw_convert_int( $wpssw_data ), true );
				if ( $is_exists > 0 && 'customer' !== (string) $role ) {
					self::wpssw_delete_user( $user_id );
					return;
				}
				$wpssw_response            = self::$instance_api->get_sheet_listing( $wpssw_spreadsheetid );
				$wpssw_existingsheetsnames = self::$instance_api->get_sheet_list( $wpssw_response );
				$wpssw_sheetid             = $wpssw_existingsheetsnames[ $wpssw_sheetname ];

				if ( $is_exists > 0 ) {
					$wpssw_values_array = self::wpssw_make_customer_value_array( 'update', $customer );
					$rownum             = $is_exists + 1;
					$rangetoupdate      = $wpssw_sheetname . '!A' . $rownum;
					$params             = array( 'valueInputOption' => 'USER_ENTERED' );
					$requestbody        = self::$instance_api->valuerangeobject( $wpssw_values_array );
					$param              = self::$instance_api->setparamater( $wpssw_spreadsheetid, $rangetoupdate, $requestbody, $params );
					$wpssw_response     = self::$instance_api->updateentry( $param );
				} else {
					if ( 'customer' !== (string) $role ) {
						return;
					}
					$wpssw_values_array = self::wpssw_make_customer_value_array( 'insert', $customer );

					$wpssw_requestbody      = self::$instance_api->valuerangeobject( $wpssw_values_array );
					$wpssw_params           = array( 'valueInputOption' => 'USER_ENTERED' );
					$wpssw_append           = 0;
					$customer_inherit_style = parent::wpssw_option( 'wpssw_customer_inherit_style' );
					foreach ( $wpssw_data as $wpssw_key => $wpssw_value ) {
						if ( ! empty( $wpssw_value ) ) {
							if ( ( (int) $user_id < (int) $wpssw_value ) && $is_exists < 1 ) {
								$wpssw_append = 1;

								$wpssw_startindex = $wpssw_key;
								$wpssw_endindex   = $wpssw_key + 1;

								$param = array();
								$param = self::$instance_api->prepare_param( $wpssw_sheetid, $wpssw_startindex, $wpssw_endindex );

								if ( 'no' === (string) $customer_inherit_style ) {
									$wpssw_batchupdaterequest = self::$instance_api->insertdimensionobject( $param, false );
								} else {
									$wpssw_batchupdaterequest = self::$instance_api->insertdimensionobject( $param, true );
								}
								$requestobject                  = array();
								$requestobject['spreadsheetid'] = $wpssw_spreadsheetid;
								$requestobject['requestbody']   = $wpssw_batchupdaterequest;
								$wpssw_response                 = self::$instance_api->formatsheet( $requestobject );
								$wpssw_start_index              = $wpssw_startindex + 1;
								$wpssw_rangetoupdate            = $wpssw_sheetname . '!A' . $wpssw_start_index;
								$param                          = array();
								$param                          = self::$instance_api->setparamater( $wpssw_spreadsheetid, $wpssw_rangetoupdate, $wpssw_requestbody, $wpssw_params );
								$wpssw_response                 = self::$instance_api->updateentry( $param );
								break;
							}
						}
					}
					if ( 0 === (int) $wpssw_append ) {
						// If there is no sheet_id available, leave the sheet_id field blank. Otherwise, if there is a sheet_id, leave the sheet_name field blank.
						$wpssw_inherit_params = array(
							'sheet_id'       => $wpssw_sheetid,
							'sheet_name'     => '',
							'start_index'    => count( $wpssw_data ),
							'end_index'      => count( $wpssw_data ) + count( $wpssw_values_array ),
							'spreadsheet_id' => $wpssw_spreadsheetid,
						);
						parent::wpssw_inherit_row_format_style( 'customer', $wpssw_inherit_params );

						$wpssw_sheetname . '!A' . ( count( $wpssw_data ) + 1 );
						$param          = self::$instance_api->setparamater( $wpssw_spreadsheetid, $wpssw_sheetname, $wpssw_requestbody, $wpssw_params );
						$wpssw_response = self::$instance_api->appendentry( $param );

					}
				}
			} catch ( Exception $e ) {
				echo esc_html( 'Message: ' . $e->getMessage() );
			}
		}
		/**
		 * Clear Customer settings sheet
		 */
		public static function wpssw_clear_customersheet() {
			$wpssw_client                       = self::$instance_api->getClient();
			$wpssw_service                      = new Google_Service_Sheets( $wpssw_client );
			$wpssw_customer_spreadsheet_setting = WPSSW_Setting::wpssw_option( 'wpssw_customer_spreadsheet_setting' );
			$wpssw_spreadsheetid                = WPSSW_Setting::wpssw_option( 'wpssw_customer_spreadsheet_id' );

			if ( 'yes' !== (string) $wpssw_customer_spreadsheet_setting ) {
				echo esc_html__( 'Please save settings.', 'wpssw' );
				die();
			}
			$requestbody               = new Google_Service_Sheets_ClearValuesRequest();
			$total_headers             = count( WPSSW_Setting::wpssw_option( 'wpssw_woo_customer_headers' ) ) + 1;
			$last_column               = WPSSW_Setting::wpssw_get_column_index( $total_headers );
			$wpssw_existingsheetsnames = array();
			$wpssw_response            = self::$instance_api->get_sheet_listing( $wpssw_spreadsheetid );
			$wpssw_existingsheetsnames = self::$instance_api->get_sheet_list( $wpssw_response );
			$wpssw_existingsheetsnames = array_flip( $wpssw_existingsheetsnames );
			$wpssw_sheetname           = 'All Customers';
			if ( in_array( $wpssw_sheetname, $wpssw_existingsheetsnames, true ) ) {
				try {
					$range    = $wpssw_sheetname . '!A2:' . $last_column . '100000';
					$response = $wpssw_service->spreadsheets_values->clear( $wpssw_spreadsheetid, $range, $requestbody );
				} catch ( Exception $e ) {
					echo esc_html( 'Message: ' . $e->getMessage() );
				}
			}
			echo 'successful';
			die();
		}
		/**
		 * Update sync schedule data.
		 *
		 * @param array  $data .
		 * @param string $schedule_type .
		 */
		public static function wpssw_update_customer_schedule_data( $data = array(), $schedule_type = 'auto_sync' ) {

			if ( ! isset( $_POST['wpssw_customer_settings'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wpssw_customer_settings'] ) ), 'save_customer_settings' ) ) {
				echo esc_html__( 'Sorry, your nonce did not verify.', 'wpssw' );
				die();
			}
			if ( 'auto_sync' === (string) $schedule_type ) {
				$option_prefix = 'customer_autosync_';
				$id_prefix     = 'cust_autosync_';

				parent::wpssw_schedule_data_update( 'customer', $schedule_type, $id_prefix, $option_prefix );
			}
			if ( 'auto_import' === (string) $schedule_type ) {
				$option_prefix = 'customer_autoimport_';
				$id_prefix     = 'cust_autoimport_';

				parent::wpssw_schedule_data_update( 'customer', $schedule_type, $id_prefix, $option_prefix );
			}
		}
		/**
		 * Customer autosync cron run.
		 */
		public static function wpssw_autosync_customers_cron_run() {
			if ( ! self::$instance_api->checkcredenatials() ) {
				return;
			}
			$wpssw_customer_spreadsheet_setting = parent::wpssw_option( 'wpssw_customer_spreadsheet_setting' );
			$wpssw_spreadsheetid                = parent::wpssw_option( 'wpssw_customer_spreadsheet_id' );
			$wpssw_sheetname                    = 'All Customers';
			if ( 'yes' !== (string) $wpssw_customer_spreadsheet_setting || ! parent::wpssw_check_sheet_exist( $wpssw_spreadsheetid, $wpssw_sheetname ) ) {
				return;
			}

			$wpssw_sheet         = "'" . $wpssw_sheetname . "'!A:Z";
			$wpssw_allentry      = self::$instance_api->get_row_list( $wpssw_spreadsheetid, $wpssw_sheet );
			$wpssw_data          = $wpssw_allentry->getValues();
			$wpssw_allentry      = null;
			$wpssw_data          = array_map(
				function( $wpssw_element ) {
					if ( isset( $wpssw_element['0'] ) ) {
						return (int) $wpssw_element['0'];
					} else {
						return '';
					}
				},
				$wpssw_data
			);
			$wpssw_data_count    = count( $wpssw_data );
			$wpssw_customerlimit = apply_filters( 'wpssw_customer_sync_limit', 500 );

			$args            = array(
				'role'    => 'customer',
				'orderby' => 'ID',
				'order'   => 'ASC',
				'fields'  => 'ID', // Fetch only ids.
			);
			$args['exclude'] = $wpssw_data;
			$args['number']  = $wpssw_customerlimit;
			$wpssw_data      = null;
			$customers       = get_users( $args );

			if ( empty( $customers ) ) {
				return;
			}

			$wpssw_values_array = array();
			$newcustomer        = 0;
			foreach ( $customers as $customer_id ) {
				if ( ! empty( $customer_id ) && $newcustomer < $wpssw_customerlimit ) {
					set_time_limit( 999 );
					$customer           = new WP_User( $customer_id );
					$wpssw_value        = self::wpssw_make_customer_value_array( 'insert', $customer );
					$wpssw_values_array = array_merge( $wpssw_values_array, $wpssw_value );
					$newcustomer++;
				}
			}
			if ( ! empty( $wpssw_values_array ) ) {
				try {
					// If there is no sheet_id available, leave the sheet_id field blank. Otherwise, if there is a sheet_id, leave the sheet_name field blank.
					$wpssw_inherit_params = array(
						'sheet_id'       => '',
						'sheet_name'     => $wpssw_sheetname,
						'start_index'    => $wpssw_data_count,
						'end_index'      => $wpssw_data_count + count( $wpssw_values_array ),
						'spreadsheet_id' => $wpssw_spreadsheetid,
					);
					parent::wpssw_inherit_row_format_style( 'customer', $wpssw_inherit_params );

					$rangetofind       = $wpssw_sheetname . '!A' . ( $wpssw_data_count + 1 );
					$wpssw_inputoption = parent::wpssw_option( 'wpssw_inputoption' );
					if ( ! $wpssw_inputoption ) {
						$wpssw_inputoption = 'USER_ENTERED';
					}
					$wpssw_requestbody = self::$instance_api->valuerangeobject( $wpssw_values_array );
					$wpssw_params      = array( 'valueInputOption' => $wpssw_inputoption );
					$param             = self::$instance_api->setparamater( $wpssw_spreadsheetid, $rangetofind, $wpssw_requestbody, $wpssw_params );
					$wpssw_response    = self::$instance_api->appendentry( $param );

				} catch ( Exception $e ) {
					return;
				}
			}
		}

	}
	new WPSSW_Customer();
endif;
