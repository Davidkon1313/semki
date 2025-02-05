<?php
/**
 * Main WPSyncSheets_For_WooCommerce namespace.
 *
 * @since 1.0.0
 * @package wpsyncsheets-for-woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }
if ( ! class_exists( 'WPSSW_Coupon' ) ) :
	/**
	 * Class WPSSW_Coupon.
	 */
	class WPSSW_Coupon extends WPSSW_Setting {
		/**
		 * Initialization
		 */
		public function __construct() {
			$wpssw_include = new WPSSW_Include_Action();
			$wpssw_include->wpssw_include_coupon_hook();
			$wpssw_include->wpssw_include_coupon_ajax_hook();

		}
		/**
		 * Save Settings of Coupon settings tab.
		 */
		public static function wpssw_update_coupon_settings() {
			if ( ! isset( $_POST['wpssw_coupon_settings'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wpssw_coupon_settings'] ) ), 'save_coupon_settings' ) ) {
				echo esc_html__( 'Sorry, your nonce did not verify.', 'wpssw' );
				die();
			}
			if ( isset( $_POST['woocoupon_header_list'] ) && isset( $_POST['woocoupon_custom'] ) ) {
				$wpssw_woo_coupon_headers        = array_map( 'sanitize_text_field', wp_unslash( $_POST['woocoupon_header_list'] ) );
				$wpssw_woo_coupon_headers_custom = array_map( 'sanitize_text_field', wp_unslash( $_POST['woocoupon_custom'] ) );
				if ( isset( $_POST['coupon_settings_checkbox'] ) ) {
					if ( isset( $_POST['coupon_inherit_style'] ) ) {
						$wpssw_coupon_inherit_style = sanitize_text_field( wp_unslash( $_POST['coupon_inherit_style'] ) );
						parent::wpssw_update_option( 'wpssw_coupon_inherit_style', $wpssw_coupon_inherit_style );
					} else {
						parent::wpssw_update_option( 'wpssw_coupon_inherit_style', 'none' );
					}
					if ( isset( $_POST['couponsheetselection'] ) && 'new' === (string) sanitize_text_field( wp_unslash( $_POST['couponsheetselection'] ) ) ) {
						$wpssw_newsheetname = isset( $_POST['coupon_spreadsheet_name'] ) ? trim( sanitize_text_field( wp_unslash( $_POST['coupon_spreadsheet_name'] ) ) ) : '';

						/*
						*Create new spreadsheet
						*/
						$wpssw_requestbody   = self::$instance_api->createspreadsheetobject( $wpssw_newsheetname );
						$wpssw_response      = self::$instance_api->createspreadsheet( $wpssw_requestbody );
						$wpssw_spreadsheetid = $wpssw_response['spreadsheetId'];
					} else {
						$wpssw_spreadsheetid = isset( $_POST['coupon_spreadsheet'] ) ? sanitize_text_field( wp_unslash( $_POST['coupon_spreadsheet'] ) ) : '';
					}
					parent::wpssw_update_option( 'wpssw_coupon_spreadsheet_id', $wpssw_spreadsheetid );
					parent::wpssw_update_option( 'wpssw_coupon_spreadsheet_setting', 'yes' );
				} else {
					parent::wpssw_update_option( 'wpssw_coupon_spreadsheet_setting', 'no' );
					parent::wpssw_update_option( 'wpssw_coupon_spreadsheet_id', '' );

					$_POST['coupon_autosync_scheduling_enable']   = 0;
					$_POST['coupon_autoimport_scheduling_enable'] = 0;

					self::wpssw_update_coupon_schedule_data( $_POST, 'auto_sync' );
					self::wpssw_update_coupon_schedule_data( $_POST, 'auto_import' );
					return;
				}
				if ( isset( $_POST['import_coupon_checkbox'] ) ) {
					parent::wpssw_update_option( 'wpssw_coupon_import', 1 );
				} else {
					parent::wpssw_update_option( 'wpssw_coupon_import', '' );
					$coupon_scheduling_enable = parent::wpssw_option( 'wpssw_coupon_autoimport_scheduling_enable' );
					if ( 1 === (int) $coupon_scheduling_enable ) {
						$_POST['coupon_autoimport_scheduling_enable'] = 0;
					}
				}
				if ( isset( $_POST['insert_coupon_checkbox'] ) ) {
					parent::wpssw_update_option( 'wpssw_coupon_insert', 1 );
				} else {
					parent::wpssw_update_option( 'wpssw_coupon_insert', '' );
				}
				if ( isset( $_POST['update_coupon_checkbox'] ) ) {
					parent::wpssw_update_option( 'wpssw_coupon_update', 1 );
				} else {
					parent::wpssw_update_option( 'wpssw_coupon_update', '' );
				}
				if ( isset( $_POST['delete_coupon_checkbox'] ) ) {
					parent::wpssw_update_option( 'wpssw_coupon_delete', 1 );
				} else {
					parent::wpssw_update_option( 'wpssw_coupon_delete', '' );
				}
				$wpssw_sheetname           = 'All Coupons';
				$requestarray              = array();
				$deleterequestarray        = array();
				$wpssw_response            = self::$instance_api->get_sheet_listing( $wpssw_spreadsheetid );
				$wpssw_existingsheetsnames = self::$instance_api->get_sheet_list( $wpssw_response );
				$wpssw_existingsheets      = array_flip( $wpssw_existingsheetsnames );
				$wpssw_inputoption         = parent::wpssw_option( 'wpssw_inputoption' );
				if ( ! $wpssw_inputoption ) {
					$wpssw_inputoption = 'USER_ENTERED';
				}
				if ( count( $wpssw_woo_coupon_headers ) > 0 ) {
					array_unshift( $wpssw_woo_coupon_headers, 'Coupon Id' );
				}
				if ( count( $wpssw_woo_coupon_headers_custom ) > 0 ) {
					array_unshift( $wpssw_woo_coupon_headers_custom, 'Coupon Id' );
				}
				$wpssw_old_header_coupon = parent::wpssw_option( 'wpssw_woo_coupon_headers' );
				if ( empty( $wpssw_old_header_coupon ) ) {
					$wpssw_old_header_coupon = array();
				}
				if ( count( $wpssw_old_header_coupon ) > 0 ) {
					array_unshift( $wpssw_old_header_coupon, 'Coupon Id' );
				}
				if ( ! in_array( $wpssw_sheetname, $wpssw_existingsheets, true ) ) {
					$param                  = array();
					$param['spreadsheetid'] = $wpssw_spreadsheetid;
					$param['sheetname']     = $wpssw_sheetname;
					$wpssw_response         = self::$instance_api->newsheetobject( $param );
					$wpssw_range            = trim( $wpssw_sheetname ) . '!A1';
					$wpssw_requestbody      = self::$instance_api->valuerangeobject( array( $wpssw_woo_coupon_headers_custom ) );
					$wpssw_params           = array( 'valueInputOption' => $wpssw_inputoption );
					$param                  = self::$instance_api->setparamater( $wpssw_spreadsheetid, $wpssw_range, $wpssw_requestbody, $wpssw_params );
					$wpssw_response         = self::$instance_api->appendentry( $param );
				}
				if ( 'new' === (string) sanitize_text_field( wp_unslash( $_POST['couponsheetselection'] ) ) ) {
					$param                  = array();
					$param['spreadsheetid'] = $wpssw_spreadsheetid;
					$wpssw_response         = self::$instance_api->deletesheetobject( $param );
				}
				if ( $wpssw_old_header_coupon !== $wpssw_woo_coupon_headers && in_array( $wpssw_sheetname, $wpssw_existingsheets, true ) ) {
					$wpssw_existingsheets      = array();
					$wpssw_response            = self::$instance_api->get_sheet_listing( $wpssw_spreadsheetid );
					$wpssw_existingsheetsnames = self::$instance_api->get_sheet_list( $wpssw_response );
					$wpssw_existingsheets      = array_flip( $wpssw_existingsheetsnames );
					// Delete deactivate column from sheet.
					$wpssw_column = array_diff( $wpssw_old_header_coupon, $wpssw_woo_coupon_headers );
					if ( ! empty( $wpssw_column ) ) {
						$wpssw_column = array_reverse( $wpssw_column, true );
						foreach ( $wpssw_column as $columnindex => $columnval ) {
							unset( $wpssw_old_header_coupon[ $columnindex ] );
							$wpssw_old_header_coupon = array_values( $wpssw_old_header_coupon );
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
				if ( $wpssw_old_header_coupon !== $wpssw_woo_coupon_headers ) {
					foreach ( $wpssw_woo_coupon_headers as $key => $hname ) {
						if ( 'Coupon Id' === (string) $hname ) {
							continue;
						}
						$wpssw_startindex = array_search( (string) $hname, parent::wpssw_convert_string( $wpssw_old_header_coupon ), true );

						if ( false !== $wpssw_startindex && ( isset( $wpssw_old_header_coupon[ $key ] ) && $wpssw_old_header_coupon[ $key ] !== $hname ) ) {
							unset( $wpssw_old_header_coupon[ $wpssw_startindex ] );
							$wpssw_old_header_coupon = array_merge( array_slice( $wpssw_old_header_coupon, 0, $key ), array( 0 => $hname ), array_slice( $wpssw_old_header_coupon, $key, count( $wpssw_old_header_coupon ) - $key ) );
							$wpssw_endindex          = $wpssw_startindex + 1;
							$wpssw_destindex         = $key;
							if ( in_array( $wpssw_sheetname, $wpssw_existingsheets, true ) ) {
								$wpssw_sheetid = array_search( (string) $wpssw_sheetname, parent::wpssw_convert_string( $wpssw_existingsheets ), true );
								if ( $wpssw_sheetid ) {
									$param              = array();
									$param              = self::$instance_api->prepare_param( $wpssw_sheetid, $wpssw_startindex, $wpssw_endindex );
									$param['destindex'] = $wpssw_destindex;
									$requestarray[]     = self::$instance_api->moveDimensionrequests( $param );
								}
							}
						} elseif ( false === (bool) $wpssw_startindex ) {
							$wpssw_old_header_coupon = array_merge( array_slice( $wpssw_old_header_coupon, 0, $key ), array( 0 => $hname ), array_slice( $wpssw_old_header_coupon, $key, count( $wpssw_old_header_coupon ) - $key ) );
							if ( in_array( $wpssw_sheetname, $wpssw_existingsheets, true ) ) {
								$wpssw_sheetid = array_search( (string) $wpssw_sheetname, parent::wpssw_convert_string( $wpssw_existingsheets ), true );
								if ( $wpssw_sheetid ) {
									$param                = array();
									$wpssw_startindex     = $key;
									$wpssw_endindex       = $key + 1;
									$param                = self::$instance_api->prepare_param( $wpssw_sheetid, $wpssw_startindex, $wpssw_endindex );
									$coupon_inherit_style = self::wpssw_option( 'wpssw_coupon_inherit_style' );
									if ( 'no' === (string) $coupon_inherit_style ) {
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
				$freeze_header             = parent::wpssw_option( 'freeze_header' );
				$wpssw_existingsheets      = array();
				$wpssw_response            = self::$instance_api->get_sheet_listing( $wpssw_spreadsheetid );
				$wpssw_existingsheetsnames = self::$instance_api->get_sheet_list( $wpssw_response );
				$wpssw_existingsheets      = array_flip( $wpssw_existingsheetsnames );
				if ( 'yes' === (string) $freeze_header ) {
					$wpssw_freeze = 1;
				} else {
					$wpssw_freeze = 0;
				}
				if ( in_array( $wpssw_sheetname, $wpssw_existingsheets, true ) ) {
					$wpssw_sheetid = array_search( $wpssw_sheetname, $wpssw_existingsheets, true );
					// freeze coupon headers.
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
					$wpssw_requestbody = self::$instance_api->valuerangeobject( array( $wpssw_woo_coupon_headers_custom ) );
					$wpssw_params      = array( 'valueInputOption' => $wpssw_inputoption );
					$param             = self::$instance_api->setparamater( $wpssw_spreadsheetid, $wpssw_range, $wpssw_requestbody, $wpssw_params );
					$wpssw_response    = self::$instance_api->updateentry( $param );
				}
				parent::wpssw_update_option( 'wpssw_woo_coupon_headers', array_map( 'sanitize_text_field', wp_unslash( $_POST['woocoupon_header_list'] ) ) );
				parent::wpssw_update_option( 'wpssw_woo_coupon_headers_custom', array_map( 'sanitize_text_field', wp_unslash( $_POST['woocoupon_custom'] ) ) );
				/* Schedule Auto Sync */
				if ( isset( $_POST['coupon_autosync_scheduling_enable'] ) ) {
					self::wpssw_update_coupon_schedule_data( $_POST, 'auto_sync' );
				}
				/* Schedule Auto Import */
				if ( isset( $_POST['coupon_autoimport_scheduling_enable'] ) ) {
					self::wpssw_update_coupon_schedule_data( $_POST, 'auto_import' );
				}
			}
		}
		/**
		 * Coupon headers
		 *
		 * @retun array $headers
		 */
		public static function wpssw_woo_coupon_headers() {
			$wpssw_include = new WPSSW_Include_Action();
			$wpssw_include->wpssw_include_coupon_compatibility_files();
			$headers = WPSSW_Coupon_Headers::get_header_list( array() );
			return $headers['WPSSW_Coupon_Headers'];
		}
		/**
		 * Insert / Update coupon data into sheet on coupon update
		 *
		 * @param object $coupon .
		 */
		public static function wpssw_coupon_object_updated_props( $coupon ) {

			$wpssw_coupon_spreadsheet_setting = parent::wpssw_option( 'wpssw_coupon_spreadsheet_setting' );
			if ( 'yes' !== (string) $wpssw_coupon_spreadsheet_setting ) {
				return;
			}

			// @codingStandardsIgnoreStart.
			if ( ( isset( $_REQUEST['post'] ) && is_array( $_REQUEST['post'] ) && count( $_REQUEST['post'] ) > 0 && isset( $_REQUEST['paged'] ) && ! empty( sanitize_text_field( wp_unslash( $_REQUEST['paged'] ) ) ) ) || ( isset( $_REQUEST['doaction'] ) && 'undo' === sanitize_text_field( wp_unslash( $_REQUEST['doaction'] ) ) && isset( $_REQUEST['ids'] ) && ! empty( $_REQUEST['ids'] ) ) ) {
				if ( isset( $_REQUEST['doaction'] ) && 'undo' === sanitize_text_field( wp_unslash( $_REQUEST['doaction'] ) ) && isset( $_REQUEST['ids'] ) && ! empty( $_REQUEST['ids'] ) ) {
					$changed_posts = explode( ',', sanitize_text_field( wp_unslash( $_REQUEST['ids'] ) ) );
				} else {
					$changed_posts = array_map( 'sanitize_text_field', wp_unslash( $_REQUEST['post'] ) );
				}
				
				// @codingStandardsIgnoreEnd.
				if ( (int) $coupon->get_id() === (int) $changed_posts[ count( $changed_posts ) - 1 ] ) {
					$wpssw_spreadsheetid = parent::wpssw_option( 'wpssw_coupon_spreadsheet_id' );
					$wpssw_sheetname     = 'All Coupons';

					$settings = array(
						'setting'        => 'coupon',
						'setting_enable' => $wpssw_coupon_spreadsheet_setting,
						'spreadsheet_id' => $wpssw_spreadsheetid,
						'sheetname'      => $wpssw_sheetname,
					);
					WPSSW_Setting::wpssw_multiple_update_data( $changed_posts, $settings, false, 'update' );

				}
				return;
			}

			self::wpssw_insert_coupon_data_into_sheet( $coupon );
		}
		/**
		 * Clear Coupon sheet
		 */
		public static function wpssw_clear_couponsheet() {

			$wpssw_coupon_spreadsheet_setting = parent::wpssw_option( 'wpssw_coupon_spreadsheet_setting' );
			$wpssw_spreadsheetid              = parent::wpssw_option( 'wpssw_coupon_spreadsheet_id' );

			$wpssw_sheetname = 'All Coupons';
			if ( 'yes' !== (string) $wpssw_coupon_spreadsheet_setting || ! parent::wpssw_check_sheet_exist( $wpssw_spreadsheetid, $wpssw_sheetname ) ) {
				echo esc_html__( 'Please save settings.', 'wpssw' );
				die();
			}

			$requestbody               = self::$instance_api->clearobject();
			$total_headers             = count( parent::wpssw_option( 'wpssw_woo_coupon_headers' ) ) + 1;
			$last_column               = parent::wpssw_get_column_index( $total_headers );
			$wpssw_existingsheetsnames = array();
			$wpssw_response            = self::$instance_api->get_sheet_listing( $wpssw_spreadsheetid );
			$wpssw_existingsheetsnames = self::$instance_api->get_sheet_list( $wpssw_response );
			$wpssw_existingsheetsnames = array_flip( $wpssw_existingsheetsnames );

			if ( in_array( $wpssw_sheetname, $wpssw_existingsheetsnames, true ) ) {
				try {
					$range                  = $wpssw_sheetname . '!A2:' . $last_column . '100000';
					$param                  = array();
					$param['spreadsheetid'] = $wpssw_spreadsheetid;
					$param['sheetname']     = $range;
					$param['requestbody']   = $requestbody;
					$response               = self::$instance_api->clear( $param );
				} catch ( Exception $e ) {
					echo esc_html( 'Message: ' . $e->getMessage() );
				}
			}
			echo 'successful';
			die();
		}
		/**
		 * Get coupons count for syncronization
		 */
		public static function wpssw_get_coupon_count() {

			if ( ! isset( $_POST['wpssw_coupon_settings'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wpssw_coupon_settings'] ) ), 'save_coupon_settings' ) ) {
				echo esc_html__( 'Sorry, your nonce did not verify.', 'wpssw' );
				die();
			}
			if ( ! self::$instance_api->checkcredenatials() ) {
				return;
			}
			$wpssw_sheetname = 'All Coupons';
			$wpssw_fromdate  = isset( $_POST['coupon_sync_all_fromdate'] ) ? sanitize_text_field( wp_unslash( $_POST['coupon_sync_all_fromdate'] ) ) : '';
			$wpssw_todate    = isset( $_POST['coupon_sync_all_todate'] ) ? sanitize_text_field( wp_unslash( $_POST['coupon_sync_all_todate'] ) ) : '';
			$wpssw_syncall   = isset( $_POST['coupon_sync_all'] ) ? sanitize_text_field( wp_unslash( $_POST['coupon_sync_all'] ) ) : '';

			if ( isset( $wpssw_fromdate ) && isset( $wpssw_todate ) ) {
				$wpssw_query_args = array(
					'post_type'      => 'shop_coupon',
					'posts_per_page' => -1,
					'order'          => 'ASC',
					'date_query'     => array(
						array(
							'after'     => $wpssw_fromdate,
							'before'    => $wpssw_todate,
							'inclusive' => true,
						),
					),
				);

			} else {
				$wpssw_query_args = array(
					'post_type'      => 'shop_coupon',
					'posts_per_page' => -1,
					'order'          => 'ASC',
				);
			}
			$wpssw_query_args['fields']       = 'ids'; // Fetch only ids.
			$wpssw_coupon_spreadsheet_setting = parent::wpssw_option( 'wpssw_coupon_spreadsheet_setting' );
			$wpssw_spreadsheetid              = parent::wpssw_option( 'wpssw_coupon_spreadsheet_id' );

			if ( 'yes' !== (string) $wpssw_coupon_spreadsheet_setting || ! parent::wpssw_check_sheet_exist( $wpssw_spreadsheetid, $wpssw_sheetname ) ) {
				echo esc_html__( 'Please save settings.', 'wpssw' );
				die();
			}
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
			if ( is_array( $wpssw_data ) && ! empty( $wpssw_data ) ) {
				$wpssw_query_args['post__not_in'] = array_values( array_filter( array_unique( $wpssw_data ) ) );
				$wpssw_data                       = null;
			}
			$wpssw_all_coupons = new WP_Query( $wpssw_query_args );
			$couponcount       = 0;
			$couponlimit       = apply_filters( 'wpssw_coupon_sync_limit', 500 );
			$couponcount       = $wpssw_all_coupons->found_posts;
			echo wp_json_encode(
				array(
					'totalcoupons' => $couponcount,
					'couponlimit'  => $couponlimit,
				)
			);
			die;
		}
		/**
		 * Sync coupon data to spreadsheet
		 */
		public static function wpssw_sync_coupons() {

			if ( ! isset( $_POST['couponnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['couponnonce'] ) ), 'save_coupon_settings' ) ) {
				echo esc_html__( 'Sorry, your nonce did not verify.', 'wpssw' );
				return;
			}
			$wpssw_coupon_spreadsheet_setting = parent::wpssw_option( 'wpssw_coupon_spreadsheet_setting' );
			$wpssw_spreadsheetid              = parent::wpssw_option( 'wpssw_coupon_spreadsheet_id' );

			if ( 'yes' !== (string) $wpssw_coupon_spreadsheet_setting ) {
				return;
			}
			$wpssw_inputoption = parent::wpssw_option( 'wpssw_inputoption' );
			if ( ! $wpssw_inputoption ) {
				$wpssw_inputoption = 'USER_ENTERED';
			}
			$wpssw_sheetname   = 'All Coupons';
			$wpssw_couponcount = isset( $_POST['couponcount'] ) ? sanitize_text_field( wp_unslash( $_POST['couponcount'] ) ) : '';
			$wpssw_couponlimit = isset( $_POST['couponlimit'] ) ? sanitize_text_field( wp_unslash( $_POST['couponlimit'] ) ) : '';
			$wpssw_fromdate    = isset( $_POST['coupon_sync_all_fromdate'] ) ? sanitize_text_field( wp_unslash( $_POST['coupon_sync_all_fromdate'] ) ) : '';
			$wpssw_todate      = isset( $_POST['coupon_sync_all_todate'] ) ? sanitize_text_field( wp_unslash( $_POST['coupon_sync_all_todate'] ) ) : '';
			$wpssw_syncall     = isset( $_POST['coupon_sync_all'] ) ? sanitize_text_field( wp_unslash( $_POST['coupon_sync_all'] ) ) : '';

			if ( isset( $wpssw_fromdate ) && isset( $wpssw_todate ) ) {
				$wpssw_query_args = array(
					'post_type'      => 'shop_coupon',
					'posts_per_page' => -1,
					'order'          => 'ASC',
					'date_query'     => array(
						array(
							'after'     => $wpssw_fromdate,
							'before'    => $wpssw_todate,
							'inclusive' => true,
						),
					),
				);

			} else {
				$wpssw_query_args = array(
					'post_type'      => 'shop_coupon',
					'posts_per_page' => -1,
					'order'          => 'ASC',
				);
			}
			$wpssw_query_args['fields']         = 'ids'; // Fetch only ids.
			$wpssw_query_args['posts_per_page'] = $wpssw_couponlimit;

			$wpssw_sheet = "'" . $wpssw_sheetname . "'!A:Z";

			$wpssw_allentry   = self::$instance_api->get_row_list( $wpssw_spreadsheetid, $wpssw_sheet );
			$wpssw_data       = $wpssw_allentry->getValues();
			$wpssw_allentry   = null;
			$wpssw_data       = array_map(
				function( $wpssw_element ) {
					if ( isset( $wpssw_element['0'] ) ) {
						return $wpssw_element['0'];
					} else {
						return '';
					}
				},
				$wpssw_data
			);
			$wpssw_data_count = count( $wpssw_data );
			if ( is_array( $wpssw_data ) && ! empty( $wpssw_data ) ) {
				$wpssw_query_args['post__not_in'] = array_values( array_filter( array_unique( $wpssw_data ) ) );
				$wpssw_data                       = null;
			}
			$wpssw_all_coupons    = new WP_Query( $wpssw_query_args );
			$wpssw_all_coupon_ids = $wpssw_all_coupons->posts;
			$wpssw_all_coupons    = null;
			if ( empty( $wpssw_all_coupon_ids ) ) {
				die();
			}
			$rangetofind        = $wpssw_sheetname . '!A' . ( $wpssw_data_count + 1 );
			$wpssw_values_array = array();
			$newcoupon          = 0;
			foreach ( $wpssw_all_coupon_ids as $wpssw_coupon_id ) {
				if ( ! empty( $wpssw_coupon_id ) && $newcoupon < $wpssw_couponlimit ) {
					set_time_limit( 999 );
					$wpssw_value        = self::wpssw_make_coupon_value_array( 'insert', $wpssw_coupon_id );
					$wpssw_values_array = array_merge( $wpssw_values_array, $wpssw_value );
					$newcoupon++;
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
					parent::wpssw_inherit_row_format_style( 'coupon', $wpssw_inherit_params );

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
		 *  Prepare array value of coupon data to insert into sheet.
		 *
		 * @param string $wpssw_operation operation to perfom on sheet.
		 * @param string $wpssw_coupon_code Coupon Code.
		 * @return array $coupon_value_array
		 */
		public static function wpssw_make_coupon_value_array( $wpssw_operation = 'insert', $wpssw_coupon_code = '' ) {
			if ( ! $wpssw_coupon_code ) {
				return array();
			}
			$wpssw_include = new WPSSW_Include_Action();
			$wpssw_include->wpssw_include_coupon_compatibility_files();
			$wpssw_headers              = apply_filters( 'wpsyncsheets_coupon_headers', array() );
			$wpssw_coupon               = new WC_Coupon( $wpssw_coupon_code );
			$wpssw_coupon_row           = array();
			$wpssw_coupon_row[]         = $wpssw_coupon->get_id();
			$wpssw_woo_selections       = stripslashes_deep( parent::wpssw_option( 'wpssw_woo_coupon_headers' ) );
			$wpssw_classarray           = array();
			$wpssw_woo_selections_count = count( $wpssw_woo_selections );
			for ( $i = 0; $i < $wpssw_woo_selections_count; $i++ ) {
				$wpssw_classarray[ $wpssw_woo_selections[ $i ] ] = parent::wpssw_find_class( $wpssw_headers, $wpssw_woo_selections[ $i ] );
			}

			foreach ( $wpssw_classarray as $headername => $classname ) {
				if ( ! empty( $classname ) ) {
					$wpssw_coupon_row[] = $classname::get_value( $headername, $wpssw_coupon );
				} else {
					$wpssw_coupon_row[] = '';
				}
			}
			$wpssw_coupon_row = self::wpssw_couponcleanArray( $wpssw_coupon_row );
			return array( $wpssw_coupon_row );
		}
		/**
		 *  Insert coupon data into sheet
		 *
		 * @param object $wpssw_coupon .
		 */
		public static function wpssw_insert_coupon_data_into_sheet( $wpssw_coupon ) {
			try {
				if ( ! self::$instance_api->checkcredenatials() ) {
					return;
				}
				if ( ! $wpssw_coupon ) {
					return;
				}
				$wpssw_spreadsheetid = parent::wpssw_option( 'wpssw_coupon_spreadsheet_id' );
				$wpssw_sheetname     = 'All Coupons';
				if ( ! parent::wpssw_check_sheet_exist( $wpssw_spreadsheetid, $wpssw_sheetname ) ) {
					return;
				}
				$wpssw_inputoption = parent::wpssw_option( 'wpssw_inputoption' );
				if ( ! $wpssw_inputoption ) {
					$wpssw_inputoption = 'USER_ENTERED';
				}
				$wpssw_headers_name        = parent::wpssw_option( 'wpssw_woo_coupon_headers' );
				$wpssw_sheet               = "'" . $wpssw_sheetname . "'!A:A";
				$wpssw_allentry            = self::$instance_api->get_row_list( $wpssw_spreadsheetid, $wpssw_sheet );
				$wpssw_data                = $wpssw_allentry->getValues();
				$wpssw_data                = array_map(
					function( $wpssw_element ) {
						if ( isset( $wpssw_element['0'] ) ) {
							return $wpssw_element['0'];
						} else {
							return '';
						}
					},
					$wpssw_data
				);
				$wpssw_response            = self::$instance_api->get_sheet_listing( $wpssw_spreadsheetid );
				$wpssw_existingsheetsnames = self::$instance_api->get_sheet_list( $wpssw_response );
				$wpssw_sheetid             = $wpssw_existingsheetsnames[ $wpssw_sheetname ];
				$is_exists                 = array_search(
					(int) $wpssw_coupon->get_id(),
					parent::wpssw_convert_int( $wpssw_data ),
					true
				);
				$wpssw_values_array        = self::wpssw_make_coupon_value_array( 'update', $wpssw_coupon->get_id() );
				$wpssw_append              = 0;
				if ( $is_exists > 0 ) {
					if ( 0 === (int) $wpssw_append ) {
						$wpssw_append   = 1;
						$rownum         = $is_exists + 1;
						$rangetoupdate  = $wpssw_sheetname . '!A' . $rownum;
						$params         = array( 'valueInputOption' => 'USER_ENTERED' );
						$requestbody    = self::$instance_api->valuerangeobject( $wpssw_values_array );
						$param          = self::$instance_api->setparamater( $wpssw_spreadsheetid, $rangetoupdate, $requestbody, $params );
						$wpssw_response = self::$instance_api->updateentry( $param );
					}
				} else {
					$coupon_inherit_style = self::wpssw_option( 'wpssw_coupon_inherit_style' );
					foreach ( $wpssw_data as $wpssw_key => $wpssw_value ) {
						if ( ! empty( $wpssw_value ) ) {
							if ( ( (int) $wpssw_coupon->get_id() < (int) $wpssw_value ) ) {
								$wpssw_append     = 1;
								$wpssw_startindex = $wpssw_key;
								$wpssw_endindex   = $wpssw_key + 1;
								$param            = array();
								$param            = self::$instance_api->prepare_param( $wpssw_sheetid, $wpssw_startindex, $wpssw_endindex );
								if ( 'no' === (string) $coupon_inherit_style ) {
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
								$wpssw_params                   = array( 'valueInputOption' => $wpssw_inputoption );
								$wpssw_requestbody              = self::$instance_api->valuerangeobject( $wpssw_values_array );
								$param                          = self::$instance_api->setparamater( $wpssw_spreadsheetid, $wpssw_rangetoupdate, $wpssw_requestbody, $wpssw_params );
								$wpssw_response                 = self::$instance_api->updateentry( $param );
								break;
							}
						}
					}
				}
				if ( 0 === (int) $wpssw_append ) {
					// If there is no sheet_id available, leave the sheet_id field blank. Otherwise, if there is a sheet_id, leave the sheet_name field blank.
					$wpssw_inherit_params = array(
						'sheet_id'       => '',
						'sheet_name'     => $wpssw_sheetname,
						'start_index'    => count( $wpssw_data ),
						'end_index'      => count( $wpssw_data ) + count( $wpssw_values_array ),
						'spreadsheet_id' => $wpssw_spreadsheetid,
					);
					parent::wpssw_inherit_row_format_style( 'coupon', $wpssw_inherit_params );

					$wpssw_isupdated   = 0;
					$wpssw_requestbody = self::$instance_api->valuerangeobject( $wpssw_values_array );
					$wpssw_params      = array( 'valueInputOption' => $wpssw_inputoption );
					$rangetofind       = $wpssw_sheetname . '!A' . ( count( $wpssw_data ) + 1 );
					$param             = self::$instance_api->setparamater( $wpssw_spreadsheetid, $rangetofind, $wpssw_requestbody, $wpssw_params );
					$wpssw_response    = self::$instance_api->appendentry( $param );

				}
			} catch ( Exception $e ) {
				echo esc_html( 'Message: ' . $e->getMessage() );
			}
		}
		/**
		 * Clean coupon data array.
		 *
		 * @param array $wpssw_array coupon data array.
		 * @return array $wpssw_array
		 */
		public static function wpssw_couponcleanArray( $wpssw_array ) {
			$wpssw_max   = count( parent::wpssw_option( 'wpssw_woo_coupon_headers' ) ) + 1;
			$wpssw_array = parent::wpssw_cleanarray( $wpssw_array, $wpssw_max );
			return $wpssw_array;
		}
		/**
		 * Get all product categories.
		 *
		 * @return array $wpssw_categories
		 */
		public static function wpssw_get_all_product_categories() {
			global $wpdb;
			// @codingStandardsIgnoreStart.
			$query            = "SELECT t.term_id AS ID, t.name AS title  
				FROM {$wpdb->prefix}terms AS t
				LEFT JOIN {$wpdb->prefix}term_taxonomy AS ta ON ta.term_id = t.term_id
				WHERE ta.taxonomy='product_cat'
				ORDER BY t.name ASC"; // db call ok.
			$cats             = $wpdb->get_results( $query );
			// @codingStandardsIgnoreEnd.
			$wpssw_categories = array();
			foreach ( $cats as $cat ) {
				$wpssw_categories[ $cat->ID ] = $cat->title;
			}
			return $wpssw_categories;
		}
		/**
		 * Update sync schedule data.
		 *
		 * @param array  $data .
		 * @param string $schedule_type .
		 */
		public static function wpssw_update_coupon_schedule_data( $data = array(), $schedule_type = 'auto_sync' ) {

			if ( ! isset( $_POST['wpssw_coupon_settings'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wpssw_coupon_settings'] ) ), 'save_coupon_settings' ) ) {
				echo esc_html__( 'Sorry, your nonce did not verify.', 'wpssw' );
				die();
			}
			if ( 'auto_sync' === (string) $schedule_type ) {
				$option_prefix = 'coupon_autosync_';
				$id_prefix     = 'coupon_autosync_';

				parent::wpssw_schedule_data_update( 'coupon', $schedule_type, $id_prefix, $option_prefix );
			}
			if ( 'auto_import' === (string) $schedule_type ) {
				$option_prefix = 'coupon_autoimport_';
				$id_prefix     = 'coupon_autoimport_';

				parent::wpssw_schedule_data_update( 'coupon', $schedule_type, $id_prefix, $option_prefix );
			}
		}
		/**
		 * Coupon autosync cron run.
		 */
		public static function wpssw_autosync_coupons_cron_run() {
			if ( ! self::$instance_api->checkcredenatials() ) {
				return;
			}
			$wpssw_coupon_spreadsheet_setting = parent::wpssw_option( 'wpssw_coupon_spreadsheet_setting' );
			$wpssw_spreadsheetid              = parent::wpssw_option( 'wpssw_coupon_spreadsheet_id' );
			$wpssw_sheetname                  = 'All Coupons';

			if ( 'yes' !== (string) $wpssw_coupon_spreadsheet_setting || ! parent::wpssw_check_sheet_exist( $wpssw_spreadsheetid, $wpssw_sheetname ) ) {
				return;
			}
			$wpssw_query_args                   = array(
				'post_type'      => 'shop_coupon',
				'posts_per_page' => -1,
				'order'          => 'ASC',
				'post_status'    => 'any',
			);
			$wpssw_query_args['posts_per_page'] = apply_filters( 'wpssw_coupon_sync_limit', 500 );
			$wpssw_query_args['fields']         = 'ids'; // Fetch only ids.

			$wpssw_sheet      = "'" . $wpssw_sheetname . "'!A:Z";
			$wpssw_allentry   = self::$instance_api->get_row_list( $wpssw_spreadsheetid, $wpssw_sheet );
			$wpssw_data       = $wpssw_allentry->getValues();
			$wpssw_allentry   = null;
			$wpssw_data       = array_map(
				function( $wpssw_element ) {
					if ( isset( $wpssw_element['0'] ) ) {
						return $wpssw_element['0'];
					} else {
						return '';
					}
				},
				$wpssw_data
			);
			$wpssw_data_count = count( $wpssw_data );
			if ( is_array( $wpssw_data ) && ! empty( $wpssw_data ) ) {
				$wpssw_query_args['post__not_in'] = array_values( array_filter( array_unique( $wpssw_data ) ) );
				$wpssw_data                       = null;
			}
			$wpssw_all_coupons    = new WP_Query( $wpssw_query_args );
			$wpssw_all_coupon_ids = $wpssw_all_coupons->posts;
			if ( empty( $wpssw_all_coupon_ids ) ) {
				return;
			}
			$wpssw_all_coupons  = null;
			$wpssw_values_array = array();
			foreach ( $wpssw_all_coupon_ids as $wpssw_coupon_id ) {
				if ( ! empty( $wpssw_coupon_id ) ) {
					set_time_limit( 999 );
					$wpssw_value        = self::wpssw_make_coupon_value_array( 'insert', $wpssw_coupon_id );
					$wpssw_values_array = array_merge( $wpssw_values_array, $wpssw_value );
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
					parent::wpssw_inherit_row_format_style( 'coupon', $wpssw_inherit_params );

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
	new WPSSW_Coupon();
endif;
