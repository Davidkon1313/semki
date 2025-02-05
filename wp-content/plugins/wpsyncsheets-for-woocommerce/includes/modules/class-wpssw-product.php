<?php
/**
 * Main WPSyncSheets_For_WooCommerce namespace.
 *
 * @since 1.0.0
 * @package wpsyncsheets-for-woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }
if ( ! class_exists( 'WPSSW_Product' ) ) :
	/**
	 * Class WPSSW_Product.
	 */
	class WPSSW_Product extends WPSSW_Setting {
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
			$wpssw_include->wpssw_include_product_hook();
			$wpssw_include->wpssw_include_product_ajax_hook();
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
		 * Attributes Headers
		 */
		public static function wpssw_attribute_headers() {
			return array( 'Attribute ID', 'Attribute Name', 'Attribute Label', 'Attribute Type', 'Attribute Orderby', 'Attribute Terms', 'Insert', 'Update', 'Delete' );
		}
		/**
		 *
		 * Save Product settings tab's settings.
		 */
		public static function wpssw_update_product_settings() {

			if ( ! isset( $_POST['wpssw_product_settings'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wpssw_product_settings'] ) ), 'save_product_settings' ) ) {
				echo esc_html__( 'Sorry, your nonce did not verify.', 'wpssw' );
				die();
			}
			if ( isset( $_POST['wooproduct_header_list'] ) && isset( $_POST['wooproduct_custom'] ) ) {
				$wpssw_woo_product_headers        = array_map( 'sanitize_text_field', wp_unslash( $_POST['wooproduct_header_list'] ) );
				$wpssw_woo_product_headers_custom = array_map( 'sanitize_text_field', wp_unslash( $_POST['wooproduct_custom'] ) );
				if ( isset( $_POST['product_settings_checkbox'] ) ) {
					if ( isset( $_POST['prd_inherit_style'] ) ) {
						$wpssw_prd_inherit_style = sanitize_text_field( wp_unslash( $_POST['prd_inherit_style'] ) );
						parent::wpssw_update_option( 'wpssw_product_inherit_style', $wpssw_prd_inherit_style );
					} else {
						parent::wpssw_update_option( 'wpssw_product_inherit_style', 'none' );
					}
					if ( isset( $_POST['prdsheetselection'] ) && 'new' === (string) sanitize_text_field( wp_unslash( $_POST['prdsheetselection'] ) ) ) {
						$wpssw_newsheetname = isset( $_POST['product_spreadsheet_name'] ) ? trim( sanitize_text_field( wp_unslash( $_POST['product_spreadsheet_name'] ) ) ) : '';

						/*
						 *Create new spreadsheet
						 */
						$wpssw_newsheetname  = trim( $wpssw_newsheetname );
						$requestbody         = self::$instance_api->createspreadsheetobject( $wpssw_newsheetname );
						$wpssw_response      = self::$instance_api->createspreadsheet( $requestbody );
						$wpssw_spreadsheetid = $wpssw_response['spreadsheetId'];
					} else {
						$wpssw_spreadsheetid = isset( $_POST['product_spreadsheet'] ) ? sanitize_text_field( wp_unslash( $_POST['product_spreadsheet'] ) ) : '';
					}
					parent::wpssw_update_option( 'wpssw_product_spreadsheet_id', $wpssw_spreadsheetid );
					parent::wpssw_update_option( 'wpssw_product_spreadsheet_setting', 'yes' );
				} else {
					parent::wpssw_update_option( 'wpssw_product_spreadsheet_setting', 'no' );
					parent::wpssw_update_option( 'wpssw_product_spreadsheet_id', '' );
					parent::wpssw_update_option( 'wpssw_bulk_insert_products', '' );
					parent::wpssw_update_option( 'wpssw_import_attribute_checkbox', '' );
					$_POST['product_scheduling_enable']      = 0;
					$_POST['prd_autosync_scheduling_enable'] = 0;

					self::wpssw_update_product_schedule_data( $_POST );
					self::wpssw_update_product_schedule_data( $_POST, 'auto_sync' );
					return;
				}

				if ( isset( $_POST['import_checkbox'] ) ) {
					parent::wpssw_update_option( 'wpssw_product_import', 1 );

				} else {

					parent::wpssw_update_option( 'wpssw_product_import', '' );
					$product_scheduling_enable = parent::wpssw_option( 'wpssw_product_scheduling_enable' );
					if ( 1 === (int) $product_scheduling_enable ) {
						$_POST['product_scheduling_enable'] = 0;
					}
				}

				if ( isset( $_POST['insert_checkbox'] ) ) {

					parent::wpssw_update_option( 'wpssw_product_insert', 1 );

				} else {

					parent::wpssw_update_option( 'wpssw_product_insert', '' );

				}

				if ( isset( $_POST['update_checkbox'] ) ) {

					parent::wpssw_update_option( 'wpssw_product_update', 1 );

				} else {

					parent::wpssw_update_option( 'wpssw_product_update', '' );

				}

				if ( isset( $_POST['delete_checkbox'] ) ) {

					parent::wpssw_update_option( 'wpssw_product_delete', 1 );

				} else {

					parent::wpssw_update_option( 'wpssw_product_delete', '' );

				}

				if ( isset( $_POST['prd_sync_lang_all'] ) && 1 === (int) $_POST['prd_sync_lang_all'] ) {

					parent::wpssw_update_option( 'wpssw_prd_sync_lang_all', 1 );

				} else {

					parent::wpssw_update_option( 'wpssw_prd_sync_lang_all', 0 );

				}
				if ( isset( $_POST['productlang_filter'] ) ) {

					parent::wpssw_update_option( 'wpssw_prd_sync_custom_langs', array_map( 'sanitize_text_field', wp_unslash( $_POST['productlang_filter'] ) ) );

				} else {

					parent::wpssw_update_option( 'wpssw_prd_sync_custom_langs', array() );

				}

				$response             = self::$instance_api->get_sheet_listing( $wpssw_spreadsheetid );
				$wpssw_existingsheets = self::$instance_api->get_sheet_list( $response );
				$wpssw_existingsheets = array_flip( $wpssw_existingsheets );

				$wpssw_sheets = array( 'All Products' );

				if ( isset( $_POST['prd_inputoption'] ) ) {
					$wpssw_prd_inputoption = sanitize_text_field( wp_unslash( $_POST['prd_inputoption'] ) );
					parent::wpssw_update_option( 'wpssw_prd_inputoption', $wpssw_prd_inputoption );
				}
				$wpssw_inputoption = self::wpssw_get_product_inputoption();
				/*Attribute*/
				if ( isset( $_POST['import_attribute_checkbox'] ) ) {
					parent::wpssw_update_option( 'wpssw_import_attribute_checkbox', 1 );
					$wpssw_attr_sheets = 'Product Attributes';
					if ( ! in_array( $wpssw_attr_sheets, $wpssw_existingsheets, true ) ) {

						$wpssw_attr_headers     = self::wpssw_attribute_headers();
						$param                  = array();
						$param['spreadsheetid'] = $wpssw_spreadsheetid;
						$param['sheetname']     = $wpssw_attr_sheets;
						$wpssw_response         = self::$instance_api->newsheetobject( $param );
						$wpssw_range            = trim( $wpssw_attr_sheets ) . '!A1';
						$wpssw_requestbody      = self::$instance_api->valuerangeobject( array( $wpssw_attr_headers ) );
						$wpssw_params           = array( 'valueInputOption' => $wpssw_inputoption );
						$param                  = self::$instance_api->setparamater( $wpssw_spreadsheetid, $wpssw_range, $wpssw_requestbody, $wpssw_params );
						$wpssw_response         = self::$instance_api->appendentry( $param );
					}
				} else {
					parent::wpssw_update_option( 'wpssw_import_attribute_checkbox', '' );
				}

				$requestarray       = array();
				$deleterequestarray = array();

				if ( count( $wpssw_woo_product_headers ) > 0 ) {
					array_unshift( $wpssw_woo_product_headers, 'Product Id', 'Product Variation Id' );
				}
				if ( count( $wpssw_woo_product_headers_custom ) > 0 ) {
					array_unshift( $wpssw_woo_product_headers_custom, 'Product Id', 'Product Variation Id' );
				}

				$wpssw_old_header_product = parent::wpssw_option( 'wpssw_woo_product_headers' );
				if ( empty( $wpssw_old_header_product ) ) {
					$wpssw_old_header_product = array();
				}
				if ( count( $wpssw_old_header_product ) > 0 ) {
					array_unshift( $wpssw_old_header_product, 'Product Id', 'Product Variation Id' );
				}

				/*****Product category name as sheet code start*/

				$wpssw_old_sheets = (array) parent::wpssw_option( 'wpssw_product_setting_sheets' );
				$wpssw_old_sheets = $wpssw_old_sheets ? array_filter( array_unique( $wpssw_old_sheets ) ) : array();

				$wpssw_sheets = array( 'all_products' => 'All Products' );
				if ( ! $wpssw_old_sheets ) {
					$wpssw_old_sheets = array();
				} else {
					$wpssw_sheets = $wpssw_old_sheets;
				}
				$wpssw_sheets = array_filter( array_unique( $wpssw_sheets ) );
				if ( empty( $wpssw_sheets )) {
					$wpssw_sheets = array( 'all_products' => 'All Products' );
				}

				$wpssw_old_product_categories_sheets = array();
				$wpssw_productsheets_list            = array();
				$wpssw_remove_sheet                  = array();

				$wpssw_sheetnames  = array();
				$wpssw_order_array = array();

				$wpssw_order_array[] = 1;
				$wpssw_sheetnames    = array( 'All Products' );

				$wpssw_existingsheets_strlower = array_map( 'strtolower', array_flip( $wpssw_existingsheets ) );

				if ( isset( $_POST['product_categories_as_sheet'] ) ) {
					$wpssw_old_product_categories_sheets = parent::wpssw_get_product_category_saved_sheets();

					if ( ! $wpssw_old_product_categories_sheets ) {
						$wpssw_old_product_categories_sheets = array();
					}

					$args       = array(
						'taxonomy'   => 'product_cat',
						'orderby'    => 'name',
						'order'      => 'ASC',
						'hide_empty' => false,
					);
					$categories = get_terms( $args );

					$prdcat_as_sheets = isset( $_POST['prdcat_as_sheets'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['prdcat_as_sheets'] ) ) : array();

					if ( $categories && $prdcat_as_sheets ) {
						foreach ( $categories as $category ) {
							$category_id = $category->term_id;

							if ( ! in_array( (int) $category_id, parent::wpssw_convert_int( $prdcat_as_sheets ), true ) ) {
								continue;
							}
							$product_category_title                   = $category->name;
							$wpssw_productsheets_list[ $category_id ] = $product_category_title;
							$product_sheets_strlower[ $category_id ]  = strtolower( $product_category_title );

							if ( in_array( strtolower( $product_category_title ), $wpssw_existingsheets_strlower, true ) ) {
								$key                                      = array_search( strtolower( $product_category_title ), $wpssw_existingsheets_strlower, true );
								$wpssw_productsheets_list[ $category_id ] = array_search( $key, $wpssw_existingsheets, true );
							} elseif ( in_array( strtolower( $product_category_title ), $product_sheets_strlower, true ) ) {
								$key                                      = array_search( strtolower( $product_category_title ), $product_sheets_strlower, true );
								$wpssw_productsheets_list[ $category_id ] = $wpssw_productsheets_list[ $key ];
							}
						}
					}
				}
				if ( 'new' !== (string) sanitize_text_field( wp_unslash( $_POST['prdsheetselection'] ) ) ) {
					if ( ! empty( $wpssw_productsheets_list ) ) {
						foreach ( $wpssw_productsheets_list as $category_id => $category_name ) {
							if ( array_key_exists( $category_id, $wpssw_old_product_categories_sheets ) ) {
								if ( strtolower( $category_name ) !== strtolower( $wpssw_old_product_categories_sheets[ $category_id ] ) ) {
									if ( ! in_array( strtolower( $category_name ), $wpssw_existingsheets_strlower, true ) ) {
										$wpssw_sheetid = isset( $wpssw_existingsheets[ $wpssw_old_product_categories_sheets[ $category_id ] ] ) ? $wpssw_existingsheets[ $wpssw_old_product_categories_sheets[ $category_id ] ] : '';
										if ( $wpssw_sheetid && $category_name ) {
											$param                  = array();
											$param['spreadsheetid'] = $wpssw_spreadsheetid;
											$param['sheetid']       = $wpssw_sheetid;
											$param['newsheetname']  = trim( $category_name );
											$wpssw_response         = self::$instance_api->updatesheetnameobject( $param );
											$wpssw_old_product_categories_sheets[ $category_id ] = $category_name;
										}
									} else {
										$wpssw_remove_sheet[] = $wpssw_old_product_categories_sheets[ $category_id ];
									}
								}
							}
						}
					}
				}

				if ( isset( $_POST['product_categories_as_sheet'] ) ) {
					$remove_product_sheet = array_filter( array_unique( array_values( array_diff_key( $wpssw_old_product_categories_sheets, $wpssw_productsheets_list ) ) ) );
					$wpssw_remove_sheet   = ! empty( $remove_product_sheet ) ? array_merge( $wpssw_remove_sheet, $remove_product_sheet ) : $wpssw_remove_sheet;
					$product_sheets       = array_filter( array_unique( array_values( $wpssw_productsheets_list ) ) );

					$product_sheets_strlower_val = array_map( 'strtolower', $product_sheets );

					$wpssw_sheetnames = array_merge( $wpssw_sheetnames, $product_sheets );
					foreach ( $product_sheets as $product_name ) {
						if ( in_array( $product_name, $wpssw_existingsheets, true ) ) {
							$wpssw_order_array[] = 0;
						} else {
							$wpssw_order_array[] = 1;
						}
					}
					parent::wpssw_update_option( 'wpssw_product_category_sheets', $wpssw_productsheets_list );
					parent::wpssw_update_option( 'product_categories_as_sheet', 'yes' );
				} elseif ( ! isset( $_POST['product_categories_as_sheet'] ) && 'yes' === parent::wpssw_option( 'product_categories_as_sheet' ) ) {
					$wpssw_productsheets_list = parent::wpssw_get_product_category_saved_sheets();
					if ( ! empty( $wpssw_productsheets_list ) ) {
						$wpssw_remove_sheet = array_merge( $wpssw_remove_sheet, $wpssw_productsheets_list );
					}
					parent::wpssw_update_option( 'wpssw_product_category_sheets', array() );
					parent::wpssw_update_option( 'product_categories_as_sheet', '' );
				}

				if ( 'new' !== (string) sanitize_text_field( wp_unslash( $_POST['prdsheetselection'] ) ) ) {
					$wpssw_sheetnames_count = count( $wpssw_sheetnames );
					for ( $i = 0; $i < $wpssw_sheetnames_count; $i++ ) {
						if ( in_array( $wpssw_sheetnames[ $i ], $wpssw_existingsheets, true ) ) {
							$wpssw_order_array[ $i ] = 0;
						} else {
							if ( 1 === (int) $wpssw_order_array[ $i ] && ! in_array( $wpssw_sheetnames[ $i ], $wpssw_existingsheets, true ) ) {
								$wpssw_order_array[ $i ] = 1;
							}
						}
					}
				}
				$wpssw_newsheet = 0;

				$wpssw_order_array_count = count( $wpssw_order_array );
				for ( $i = 0; $i < $wpssw_order_array_count; $i++ ) {
					$i               = (int) $i;
					$wpssw_sheetname = $wpssw_sheetnames[ $i ];
					if ( 1 === (int) $wpssw_order_array[ $i ] ) {
						$param                  = array();
						$param['spreadsheetid'] = $wpssw_spreadsheetid;
						$param['sheetname']     = $wpssw_sheetname;
						$wpssw_response         = self::$instance_api->newsheetobject( $param );
						$wpssw_range            = trim( $wpssw_sheetname ) . '!A1';
						$wpssw_params           = array( 'valueInputOption' => $wpssw_inputoption );
						$wpssw_requestbody      = self::$instance_api->valuerangeobject( array( $wpssw_woo_product_headers_custom ) );
						$param                  = array();
						$param                  = self::$instance_api->setparamater( $wpssw_spreadsheetid, $wpssw_range, $wpssw_requestbody, $wpssw_params );
						$wpssw_response         = self::$instance_api->appendentry( $param );
						$wpssw_newsheet         = 1;
					}
				}

				/*****Product category name as sheet code end*/

				if ( 'new' === (string) sanitize_text_field( wp_unslash( $_POST['prdsheetselection'] ) ) ) {
					$param                  = array();
					$param['spreadsheetid'] = $wpssw_spreadsheetid;
					$wpssw_response         = self::$instance_api->deletesheetobject( $param );
				}

				if ( $wpssw_old_header_product !== $wpssw_woo_product_headers ) {
					$update_sheets = array();
					foreach ( $wpssw_sheets as $wpssw_sheetname ) {
						if ( in_array( $wpssw_sheetname, $wpssw_existingsheets, true ) ) {
							$update_sheets[] = $wpssw_sheetname;
						}
					}

					if ( ! empty( $update_sheets ) ) {
						$wpssw_existingsheets = array();
						$response             = self::$instance_api->get_sheet_listing( $wpssw_spreadsheetid );
						$wpssw_existingsheets = self::$instance_api->get_sheet_list( $response );
						$wpssw_existingsheets = array_flip( $wpssw_existingsheets );
						// Delete deactivate column from sheet.
						$wpssw_column = array_diff( $wpssw_old_header_product, $wpssw_woo_product_headers );
						if ( ! empty( $wpssw_column ) ) {
							$wpssw_column = array_reverse( $wpssw_column, true );
							foreach ( $wpssw_column as $columnindex => $columnval ) {
								unset( $wpssw_old_header_product[ $columnindex ] );
								$wpssw_old_header_product = array_values( $wpssw_old_header_product );
								foreach ( $update_sheets as $wpssw_sheetname ) {
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
				}
				if ( $wpssw_old_header_product !== $wpssw_woo_product_headers ) {
					foreach ( $wpssw_woo_product_headers as $key => $hname ) {
						if ( 'Product Id' === (string) $hname || 'Product Variation Id' === (string) $hname ) {
							continue;
						}
						$wpssw_startindex = array_search( $hname, $wpssw_old_header_product, true );
						if ( false !== $wpssw_startindex && ( isset( $wpssw_old_header_product[ $key ] ) && $wpssw_old_header_product[ $key ] !== $hname ) ) {
							unset( $wpssw_old_header_product[ $wpssw_startindex ] );
							$wpssw_old_header_product = array_merge( array_slice( $wpssw_old_header_product, 0, $key ), array( 0 => $hname ), array_slice( $wpssw_old_header_product, $key, count( $wpssw_old_header_product ) - $key ) );
							$wpssw_endindex           = $wpssw_startindex + 1;
							$wpssw_destindex          = $key;
							foreach ( $wpssw_sheets as $wpssw_sheetname ) {
								if ( in_array( $wpssw_sheetname, $wpssw_existingsheets, true ) ) {
									$wpssw_sheetid = array_search( $wpssw_sheetname, $wpssw_existingsheets, true );
									if ( $wpssw_sheetid ) {
										$param              = array();
										$param              = self::$instance_api->prepare_param( $wpssw_sheetid, $wpssw_startindex, $wpssw_endindex );
										$param['destindex'] = $wpssw_destindex;
										$requestarray[]     = self::$instance_api->moveDimensionrequests( $param );
									}
								}
							}
						} elseif ( false === (bool) $wpssw_startindex ) {
							$wpssw_old_header_product = array_merge( array_slice( $wpssw_old_header_product, 0, $key ), array( 0 => $hname ), array_slice( $wpssw_old_header_product, $key, count( $wpssw_old_header_product ) - $key ) );
							$product_inherit_style    = self::wpssw_option( 'wpssw_product_inherit_style' );
							foreach ( $wpssw_sheets as $wpssw_sheetname ) {
								if ( in_array( $wpssw_sheetname, $wpssw_existingsheets, true ) ) {
									$wpssw_sheetid = array_search( $wpssw_sheetname, $wpssw_existingsheets, true );
									if ( $wpssw_sheetid ) {
										$param            = array();
										$wpssw_startindex = $key;
										$wpssw_endindex   = $key + 1;
										$param            = self::$instance_api->prepare_param( $wpssw_sheetid, $wpssw_startindex, $wpssw_endindex );
										if ( 'no' === (string) $product_inherit_style ) {
											$requestarray[] = self::$instance_api->insertdimensionrequests( $param, 'COLUMNS', false );
										} else {
											$requestarray[] = self::$instance_api->insertdimensionrequests( $param, 'COLUMNS', true );
										}
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
				if ( isset( $_POST['prd_freeze_header'] ) ) {
					$wpssw_prd_freeze_header = sanitize_text_field( wp_unslash( $_POST['prd_freeze_header'] ) );
					parent::wpssw_update_option( 'prd_freeze_header', $wpssw_prd_freeze_header );
				} else {
					parent::wpssw_update_option( 'prd_freeze_header', 'no' );
				}

				// Delete sheet from spreadsheet on deactivate order status.
				if ( ! empty( $wpssw_remove_sheet ) ) {
					foreach ( $wpssw_remove_sheet as $key => $name ) {
						if ( ! in_array( $name, $wpssw_existingsheets, true ) ) {
							unset( $wpssw_remove_sheet[ $key ] );
						}
					}
					$wpssw_remove_sheet = array_values( $wpssw_remove_sheet );
				}
				if ( ! empty( $wpssw_remove_sheet ) && 'new' === (string) sanitize_text_field( wp_unslash( $_POST['prdsheetselection'] ) ) ) {
					parent::wpssw_delete_sheet( $wpssw_spreadsheetid, $wpssw_remove_sheet, $wpssw_existingsheets );
				}

				$freeze_header        = parent::wpssw_option( 'freeze_header' );
				$wpssw_existingsheets = array();
				$response             = self::$instance_api->get_sheet_listing( $wpssw_spreadsheetid );
				$wpssw_existingsheets = self::$instance_api->get_sheet_list( $response );
				$wpssw_existingsheets = array_flip( $wpssw_existingsheets );
				if ( ! $freeze_header ) {
					$freeze_header = parent::wpssw_option( 'prd_freeze_header' );
				}

				if ( 'yes' === (string) $freeze_header ) {
					$wpssw_freeze = 1;
				} else {
					$wpssw_freeze = 0;
				}
				if ( ( $wpssw_freeze && 'yes' === (string) $freeze_header ) || ( ! $wpssw_freeze && 'no' === (string) $freeze_header ) ) {
					$wpssw_freeze_header = 0;
				}
				if ( 'new' === (string) sanitize_text_field( wp_unslash( $_POST['prdsheetselection'] ) ) || $wpssw_newsheet ) {
					$wpssw_freeze_header = 1;
					$wpssw_color         = 1;
				}

				// Freeze headers.
				parent::wpssw_freeze_header( $wpssw_spreadsheetid, $wpssw_freeze, '', '', 0, $wpssw_freeze_header, true, $wpssw_sheetnames );

				foreach ( $wpssw_sheets as $wpssw_sheetname ) {
					if ( in_array( $wpssw_sheetname, $wpssw_existingsheets, true ) ) {
						$wpssw_range       = trim( $wpssw_sheetname ) . '!A1';
						$wpssw_requestbody = self::$instance_api->valuerangeobject( array( $wpssw_woo_product_headers_custom ) );
						$wpssw_params      = array( 'valueInputOption' => $wpssw_inputoption );
						$param             = self::$instance_api->setparamater( $wpssw_spreadsheetid, $wpssw_range, $wpssw_requestbody, $wpssw_params );
						$wpssw_response    = self::$instance_api->updateentry( $param );
					}
				}
				parent::wpssw_update_option( 'wpssw_woo_product_headers', array_map( 'sanitize_text_field', wp_unslash( $_POST['wooproduct_header_list'] ) ) );
				parent::wpssw_update_option( 'wpssw_woo_product_headers_custom', array_map( 'sanitize_text_field', wp_unslash( $_POST['wooproduct_custom'] ) ) );

				/*
				 * Update Static Headers
				 */
				if ( isset( $_POST['product_header_fields_static'] ) && is_array( $_POST['product_header_fields_static'] ) ) {
					$wpssw_product_static_header = array_map( 'sanitize_text_field', wp_unslash( $_POST['product_header_fields_static'] ) );
					parent::wpssw_update_option( 'wpssw_product_static_header', $wpssw_product_static_header );
				}
				if ( isset( $_POST['wpssw_static_product_header_values'] ) && is_array( $_POST['wpssw_static_product_header_values'] ) ) {
					$wpssw_static_product_header_values = array_map( 'sanitize_text_field', wp_unslash( $_POST['wpssw_static_product_header_values'] ) );
					parent::wpssw_update_option( 'wpssw_static_product_header_values', $wpssw_static_product_header_values );
				}
				if ( isset( $_POST['include_child_categories'] ) ) {
					parent::wpssw_update_option( 'wpssw_include_child_categories', 'yes' );
				} else {
					parent::wpssw_update_option( 'wpssw_include_child_categories', '' );
				}

				/* Schedule Product Import */
				if ( isset( $_POST['product_scheduling_enable'] ) ) {
					self::wpssw_update_product_schedule_data( $_POST );
				}
				/* Schedule Auto Sync */
				if ( isset( $_POST['prd_autosync_scheduling_enable'] ) ) {
					self::wpssw_update_product_schedule_data( $_POST, 'auto_sync' );
				}
				parent::wpssw_update_option( 'wpssw_product_setting_sheets', $wpssw_sheets );
			}
		}
		/**
		 * Update sync schedule data.
		 *
		 * @param array  $data .
		 * @param string $schedule_type .
		 */
		public static function wpssw_update_product_schedule_data( $data = array(), $schedule_type = 'import_products' ) {

			if ( ! isset( $_POST['wpssw_product_settings'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wpssw_product_settings'] ) ), 'save_product_settings' ) ) {
				echo esc_html__( 'Sorry, your nonce did not verify.', 'wpssw' );
				die();
			}

			if ( 'import_products' === (string) $schedule_type || 'auto_sync' === (string) $schedule_type ) {
				$option_prefix = '';
				$id_prefix     = '';
				if ( 'import_products' === (string) $schedule_type ) {
					$option_prefix = 'product_';
					$id_prefix     = 'product_';
				} elseif ( 'auto_sync' === (string) $schedule_type ) {
					$option_prefix = 'product_autosync_';
					$id_prefix     = 'prd_autosync_';
				}

				parent::wpssw_schedule_data_update( 'product', $schedule_type, $id_prefix, $option_prefix );

			}
		}
		/**
		 * Update Products
		 *
		 * @param int    $product_id .
		 * @param object $wpssw_product .
		 */
		public static function wpssw_woocommerce_update_product( $product_id, $wpssw_product ) {

			$wpssw_product_spreadsheet_setting = parent::wpssw_option( 'wpssw_product_spreadsheet_setting' );
			if ( 'yes' !== (string) $wpssw_product_spreadsheet_setting ) {
				return;
			}
			// phpcs:ignore.
			if ( ( isset( $_REQUEST['post_status'] ) && 'trash' === sanitize_text_field( wp_unslash( $_REQUEST['post_status'] ) ) ) || ( isset( $_REQUEST['action'] ) && 'untrash' === sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) && isset( $_REQUEST['post'] ) && is_array( $_REQUEST['post'] )) ) {
				return;
			}

			$wpssw_rsync = apply_filters( 'wpssw_realtime_sync', false );
			if ( $wpssw_rsync ) {
				// @codingStandardsIgnoreStart.
				if ( isset( $_POST['post_type'] ) && ( 'shop_order' === sanitize_text_field( wp_unslash( $_POST['post_type'] ) ) || 'product' === sanitize_text_field( wp_unslash( $_POST['post_type'] ) ) ) ) {
					return;
				}
				if ( isset( $_POST['action'] ) && 'wpssw_product_import' !== sanitize_text_field( wp_unslash( $_POST['action'] ) ) ) {
					return;
				}
				// @codingStandardsIgnoreEnd.
			}

			$wpssw_spreadsheetid = parent::wpssw_option( 'wpssw_product_spreadsheet_id' );

			$wpssw_sheetname = 'All Products';

			// @codingStandardsIgnoreStart.
			if ( ( isset( $_REQUEST['post'] ) && is_array( $_REQUEST['post'] ) && count( $_REQUEST['post'] ) > 0 && isset( $_REQUEST['paged'] ) && ! empty( sanitize_text_field( wp_unslash( $_REQUEST['paged'] ) ) ) ) || ( isset( $_REQUEST['doaction'] ) && 'undo' === sanitize_text_field( wp_unslash( $_REQUEST['doaction'] ) ) && isset( $_REQUEST['ids'] ) && ! empty( $_REQUEST['ids'] ) ) ) {
				if ( isset( $_REQUEST['doaction'] ) && 'undo' === sanitize_text_field( wp_unslash( $_REQUEST['doaction'] ) ) && isset( $_REQUEST['ids'] ) && ! empty( $_REQUEST['ids'] ) ) {
					$changed_posts = explode( ',', sanitize_text_field( wp_unslash( $_REQUEST['ids'] ) ) );
				} else {
					$changed_posts = array_map( 'sanitize_text_field', wp_unslash( $_REQUEST['post'] ) );
				}
				
				// @codingStandardsIgnoreEnd.
				if ( (int) $product_id === (int) $changed_posts[ count( $changed_posts ) - 1 ] ) {
					$settings = array(
						'setting'        => 'product',
						'setting_enable' => $wpssw_product_spreadsheet_setting,
						'spreadsheet_id' => $wpssw_spreadsheetid,
						'sheetname'      => $wpssw_sheetname,
					);
					WPSSW_Setting::wpssw_multiple_update_data( $changed_posts, $settings, false, 'update' );
				}
				return;
			}
			if ( parent::wpssw_is_event_calender_ticket_active() ) {
				// @codingStandardsIgnoreStart.
				if ( isset( $_POST['post_id'] ) && 'tribe_events' === (string) get_post_type( sanitize_text_field( wp_unslash( $_POST['post_id'] ) ) ) ) {
				// @codingStandardsIgnoreEnd.
					return;
				}
			}
			if ( ! self::$instance_api->checkcredenatials() ) {
				return;
			}

			$wpssw_inputoption = self::wpssw_get_product_inputoption();

			$deleterequestarray = array();
			if ( ! empty( $wpssw_spreadsheetid ) ) {
				$wpssw_spreadsheets_list = self::$instance_api->get_spreadsheet_listing();

				$wpssw_response            = self::$instance_api->get_sheet_listing( $wpssw_spreadsheetid );
				$wpssw_existingsheetsnames = self::$instance_api->get_sheet_list( $wpssw_response );

				$wpssw_product_setting_sheets = array_filter( (array) parent::wpssw_option( 'wpssw_product_setting_sheets' ) );
				if ( ! $wpssw_product_setting_sheets ) {
					$wpssw_product_setting_sheets = array( 'all_products' => 'All Products' );
				}

				$wpssw_product_categories_as_sheet = parent::wpssw_option( 'product_categories_as_sheet' );
				$wpssw_productsheets_list          = array();

				$wpssw_activesheets = array();

				foreach ( $wpssw_product_setting_sheets as $sheet_slug => $sheet_name ) {
					if ( ! empty( $sheet_name ) && false !== array_key_exists( $sheet_name, $wpssw_existingsheetsnames ) ) {
						$wpssw_activesheets[ $sheet_slug ] = $sheet_name;
					}
				}
				if ( 'yes' === (string) $wpssw_product_categories_as_sheet ) {
					$wpssw_productsheets_list = parent::wpssw_get_product_category_saved_sheets();

					if ( ! $wpssw_productsheets_list ) {
						$wpssw_productsheets_list = array();
					}

					foreach ( $wpssw_productsheets_list as $cat_id => $cat_name ) {
						if ( ! empty( $cat_name ) && false !== array_key_exists( $cat_name, $wpssw_existingsheetsnames ) ) {
							$wpssw_activesheets[ $cat_id ]           = $cat_name;
							$wpssw_product_setting_sheets[ $cat_id ] = $cat_name;
						}
					}
				}
				if ( empty( $wpssw_activesheets ) ) {
					return;
				}
				if ( count( $wpssw_activesheets ) > 1 ) {

					$settings_data = array(
						'setting'             => 'product',
						'setting_enable'      => $wpssw_product_spreadsheet_setting,
						'spreadsheet_id'      => $wpssw_spreadsheetid,
						'sheetname'           => 'All Products',
						'existingsheetsnames' => $wpssw_existingsheetsnames,
						'activesheets'        => $wpssw_activesheets,
					);
					self::wpssw_multiple_sheets_update_data( array( $product_id ), $settings_data, false, 'update' );
					return;
				}

				$product              = wc_get_product( $product_id );
				$wpssw_woo_selections = stripslashes_deep( parent::wpssw_option( 'wpssw_woo_product_headers' ) );
				if ( ! $wpssw_woo_selections ) {
					$wpssw_woo_selections = array();
				}
				array_unshift( $wpssw_woo_selections, 'Product Id', 'Product Variation Id' );
				$wpssw_product_name_key = array_search( 'Product Name', $wpssw_woo_selections, true );
				$rangetofind            = $wpssw_product_name_key ? parent::wpssw_get_column_index( $wpssw_product_name_key + 1 ) : 'A';

				foreach ( $wpssw_activesheets as $wpssw_sheetslug => $wpssw_sheetname ) {
					$wpssw_total = self::$instance_api->get_row_list( $wpssw_spreadsheetid, $wpssw_sheetname );

					$wpssw_sheetid           = $wpssw_existingsheetsnames[ $wpssw_sheetname ];
					$wpssw_total_values      = $wpssw_total->getValues();
					$variation_productid     = array_search( 'Product Id', $wpssw_total_values[0], true );
					$variation_product_index = array_column( $wpssw_total_values, $variation_productid );

					$product_added_childs = array();
					$add_varaition_row    = 0;
					$remove_varaition_row = 0;
					$product_keys         = array_filter( array_keys( parent::wpssw_convert_int( $variation_product_index ), (int) $product_id, true ) );
					$lastkey_value        = ( array_slice( $product_keys, -1, 1 ) );
					$lastkey              = '';
					if ( isset( $lastkey_value[0] ) ) {
						$lastkey = $lastkey_value[0];
					}
					if ( ! empty( $product->get_children() ) && 'grouped' !== (string) $product->get_type() ) {
						$product_childs = $product->get_children();
						if ( ! empty( $product_keys ) ) {
							if ( count( $product_childs ) > count( $product_keys ) ) {
								$add_varaition_row = count( $product_childs ) - count( $product_keys ) + 1;
							} elseif ( count( $product_childs ) < count( $product_keys ) ) {
								$remove_varaition_row = count( $product_keys ) - count( $product_childs ) - 1;
							}
							if ( $remove_varaition_row > 0 ) {
								if ( $wpssw_sheetid ) {
									$param                = array();
									$startindex           = $product_keys[0];
									$endindex             = $product_keys[0] + $remove_varaition_row;
									$param                = self::$instance_api->prepare_param( $wpssw_sheetid, $startindex, $endindex );
									$deleterequestarray[] = self::$instance_api->deleteDimensionrequests( $param, 'ROWS' );
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
						}
						$wpssw_array_value   = array();
						$wpssw_array_value[] = self::wpssw_make_product_value_array( 'insert', $product_id );
						foreach ( $product->get_children() as $child ) {
							$wpssw_child_array   = self::wpssw_make_product_value_array( 'insert', $child, true );
							$wpssw_array_value[] = $wpssw_child_array;
						}
					} else {
						if ( 'variable' !== (string) $product->get_type() || ( empty( $product->get_children() ) && 'variable' === (string) $product->get_type() ) ) {
							if ( count( $product_keys ) > 1 ) {
								if ( $wpssw_sheetid ) {
									$param                = array();
									$deleterequestarray   = array();
									$startindex           = $product_keys[0];
									$endindex             = $product_keys[0] + count( $product_keys ) - 1;
									$param                = self::$instance_api->prepare_param( $wpssw_sheetid, $startindex, $endindex );
									$deleterequestarray[] = self::$instance_api->deleteDimensionrequests( $param, 'ROWS' );
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
						}
						$wpssw_values = self::wpssw_make_product_value_array( 'insert', $product_id );
					}
					if ( $add_varaition_row > 0 && ! empty( $product_keys ) ) {
						$insert      = 0;
						$start_index = $lastkey + 1;
						$end_index   = $lastkey + $add_varaition_row + 1;
						if ( $wpssw_sheetid ) {
							$param                 = array();
							$param                 = self::$instance_api->prepare_param( $wpssw_sheetid, $start_index, $end_index );
							$product_inherit_style = self::wpssw_option( 'wpssw_product_inherit_style' );
							if ( 'no' === (string) $product_inherit_style ) {
								$requestarray[] = self::$instance_api->insertdimensionrequests( $param, 'ROWS', false );
							} else {
								$requestarray[] = self::$instance_api->insertdimensionrequests( $param, 'ROWS', true );
							}
						}
						try {
							if ( ! empty( $requestarray ) ) {
								$param                  = array();
								$param['spreadsheetid'] = $wpssw_spreadsheetid;
								$param['requestarray']  = $requestarray;
								$wpssw_response         = self::$instance_api->updatebachrequests( $param );
							}
						} catch ( Exception $e ) {
							echo esc_html( 'Message: ' . $e->getMessage() );
						}
					}

					$wpssw_rangetofind = $wpssw_sheetname . '!A:' . $rangetofind;
					$wpssw_allentry    = self::$instance_api->get_row_list( $wpssw_spreadsheetid, $wpssw_rangetofind );
					$wpssw_data        = $wpssw_allentry->getValues();
					$wpssw_data        = array_map(
						function( $element ) {
							if ( isset( $element['0'] ) ) {
								return $element['0'];
							} else {
								return '';
							}
						},
						$wpssw_data
					);
					$wpssw_num         = array_search( (int) $product_id, parent::wpssw_convert_int( $wpssw_data ), true );
					if ( $wpssw_num > 0 ) {
						if ( isset( $wpssw_array_value ) && ! empty( $wpssw_array_value ) ) {
							$wpssw_rangenum      = $wpssw_num + 1;
							$wpssw_rangetoupdate = $wpssw_sheetname . '!A' . $wpssw_rangenum;
							$wpssw_requestbody   = self::$instance_api->valuerangeobject( $wpssw_array_value );
							$wpssw_params        = array( 'valueInputOption' => $wpssw_inputoption ); // USER_ENTERED.
							$param               = self::$instance_api->setparamater( $wpssw_spreadsheetid, $wpssw_rangetoupdate, $wpssw_requestbody, $wpssw_params );
							$wpssw_response      = self::$instance_api->updateentry( $param );
						} else {
							$wpssw_rangenum      = $wpssw_num + 1;
							$wpssw_rangetoupdate = $wpssw_sheetname . '!A' . $wpssw_rangenum;
							$wpssw_requestbody   = self::$instance_api->valuerangeobject( array( $wpssw_values ) );
							$wpssw_params        = array( 'valueInputOption' => $wpssw_inputoption ); // USER_ENTERED.
							$param               = self::$instance_api->setparamater( $wpssw_spreadsheetid, $wpssw_rangetoupdate, $wpssw_requestbody, $wpssw_params );
							$wpssw_response      = self::$instance_api->updateentry( $param );
						}
					} else {
						$wpssw_isupdated = 0;
						if ( isset( $wpssw_array_value ) && ! empty( $wpssw_array_value ) ) {
							$requestarray     = array();
							$wpssw_startindex = '';
							$wpssw_endindex   = '';
							if ( count( $wpssw_data ) > 1 ) {
								$wpssw_startindex = count( $wpssw_data );
							}
							$wpssw_startindex  = parent::wpssw_insert_blankrow( $wpssw_spreadsheetid, $wpssw_sheetid, $product_id, $wpssw_array_value, $wpssw_data, $wpssw_startindex, 'product' );
							$wpssw_requestbody = self::$instance_api->valuerangeobject( $wpssw_array_value );
							$wpssw_params      = array( 'valueInputOption' => $wpssw_inputoption );
							if ( count( $wpssw_data ) > 1 ) {
								if ( ! $wpssw_startindex ) {
									$wpssw_startindex = count( $wpssw_data );
								}
								$wpssw_rangetoupdate = $wpssw_sheetname . '!A' . ( $wpssw_startindex + 1 );
								$param               = self::$instance_api->setparamater( $wpssw_spreadsheetid, $wpssw_rangetoupdate, $wpssw_requestbody, $wpssw_params );
								$wpssw_response      = self::$instance_api->updateentry( $param );
								$wpssw_isupdated     = 1;
							}
							if ( 0 === (int) $wpssw_isupdated ) {
								$param          = self::$instance_api->setparamater( $wpssw_spreadsheetid, $wpssw_sheetname, $wpssw_requestbody, $wpssw_params );
								$wpssw_response = self::$instance_api->appendentry( $param );
							}
						} else {
							$requestarray     = array();
							$wpssw_startindex = '';
							$wpssw_endindex   = '';
							if ( count( $wpssw_data ) > 1 ) {
								$wpssw_startindex = count( $wpssw_data );
							}
							$wpssw_startindex  = parent::wpssw_insert_blankrow( $wpssw_spreadsheetid, $wpssw_sheetid, $product_id, $wpssw_values, $wpssw_data, $wpssw_startindex, 'product' );
							$wpssw_requestbody = self::$instance_api->valuerangeobject( array( $wpssw_values ) );
							$wpssw_params      = array( 'valueInputOption' => $wpssw_inputoption );
							if ( $wpssw_startindex ) {
								$wpssw_rangetoupdate = $wpssw_sheetname . '!A' . ( $wpssw_startindex + 1 );
								$param               = self::$instance_api->setparamater( $wpssw_spreadsheetid, $wpssw_rangetoupdate, $wpssw_requestbody, $wpssw_params );
								$wpssw_response      = self::$instance_api->updateentry( $param );
								$wpssw_isupdated     = 1;
							}
							if ( 0 === (int) $wpssw_isupdated ) {
								$param          = array();
								$param          = self::$instance_api->setparamater( $wpssw_spreadsheetid, $wpssw_sheetname, $wpssw_requestbody, $wpssw_params );
								$wpssw_response = self::$instance_api->appendentry( $param );
							}
						}
					}
				}
			}
			remove_action( 'woocommerce_update_product', __CLASS__ . '::wpssw_woocommerce_update_product', 10, 2 );
		}
		/**
		 * Get products count for syncronization
		 */
		public static function wpssw_get_product_count() {

			if ( ! isset( $_POST['wpssw_product_settings'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wpssw_product_settings'] ) ), 'save_product_settings' ) ) {
				echo esc_html__( 'Sorry, your nonce did not verify.', 'wpssw' );
				die();
			}
			if ( ! self::$instance_api->checkcredenatials() ) {
				return;
			}
			$wpssw_product_spreadsheet_setting = parent::wpssw_option( 'wpssw_product_spreadsheet_setting' );
			$wpssw_spreadsheetid               = parent::wpssw_option( 'wpssw_product_spreadsheet_id' );

			if ( 'yes' !== (string) $wpssw_product_spreadsheet_setting ) {
				return;
			}
			$wpssw_fromdate = isset( $_POST['prd_sync_all_fromdate'] ) ? sanitize_text_field( wp_unslash( $_POST['prd_sync_all_fromdate'] ) ) : '';
			$wpssw_todate   = isset( $_POST['prd_sync_all_todate'] ) ? sanitize_text_field( wp_unslash( $_POST['prd_sync_all_todate'] ) ) : '';

			$product_sale_fromdate = isset( $_POST['prd_sync_all_sale_fromdate'] ) ? sanitize_text_field( wp_unslash( $_POST['prd_sync_all_sale_fromdate'] ) ) : '';
			$product_sale_todate   = isset( $_POST['prd_sync_all_sale_todate'] ) ? sanitize_text_field( wp_unslash( $_POST['prd_sync_all_sale_todate'] ) ) : '';

			$wpssw_syncall = isset( $_POST['prd_sync_all'] ) ? sanitize_text_field( wp_unslash( $_POST['prd_sync_all'] ) ) : '';

			$wpssw_existingsheetsnames = array();
			$wpssw_response            = self::$instance_api->get_sheet_listing( $wpssw_spreadsheetid );
			$wpssw_existingsheetsnames = self::$instance_api->get_sheet_list( $wpssw_response );
			$wpssw_existingsheetsnames = array_flip( $wpssw_existingsheetsnames );

			$wpssw_product_setting_sheets = array_filter( (array) parent::wpssw_option( 'wpssw_product_setting_sheets' ) );

			if ( ! $wpssw_product_setting_sheets ) {
				$wpssw_product_setting_sheets = array( 'all_products' => 'All Products' );
			}

			$wpssw_product_categories_as_sheet = parent::wpssw_option( 'product_categories_as_sheet' );

			$wpssw_productsheets_list = array();

			foreach ( $wpssw_product_setting_sheets as $sheet_slug => $sheet_name ) {
				if ( ! empty( $sheet_name ) && in_array( $sheet_name, $wpssw_existingsheetsnames, true ) ) {
					$wpssw_activesheets[ $sheet_slug ] = $sheet_name;
					$wpssw_getactivesheets[]           = "'" . $sheet_name . "'!A:A";
				}
			}
			if ( 'yes' === (string) $wpssw_product_categories_as_sheet ) {
				$wpssw_productsheets_list = parent::wpssw_get_product_category_saved_sheets();
				if ( ! $wpssw_productsheets_list ) {
					$wpssw_productsheets_list = array();
				}
				foreach ( $wpssw_productsheets_list as $cat_id => $cat_name ) {
					if ( ! empty( $cat_name ) && in_array( $cat_name, $wpssw_existingsheetsnames, true ) ) {
						$wpssw_activesheets[ $cat_id ]           = $cat_name;
						$wpssw_getactivesheets[]                 = "'" . $cat_name . "'!A:A";
						$wpssw_product_setting_sheets[ $cat_id ] = $cat_name;
					}
				}
			}

			/*Get First Column Value from all sheets*/
			try {
				$param                  = array();
				$param['spreadsheetid'] = $wpssw_spreadsheetid;
				$param['ranges']        = array( 'ranges' => $wpssw_getactivesheets );
				$wpssw_response         = self::$instance_api->getbatchvalues( $param );
			} catch ( Exception $e ) {
				echo esc_html( 'Message: ' . $e->getMessage() );
			}
			$wpssw_existingdata = array();

			foreach ( $wpssw_response->getValueRanges() as $wpssw_response_val ) {
				if ( strpos( $wpssw_response_val->range, "'!A" ) ) {
					$wpssw_rangetitle = explode( "'!A", $wpssw_response_val->range );
				} else {
					$wpssw_rangetitle = explode( '!A', $wpssw_response_val->range );
				}
				$wpssw_sheettitle = str_replace( "'", '', $wpssw_rangetitle[0] );
				if ( ! is_array( $wpssw_response_val->values ) ) {
					$wpssw_data = array();
				} else {
					$wpssw_data = array_map(
						function( $wpssw_element ) {
							if ( isset( $wpssw_element['0'] ) ) {
								return $wpssw_element['0'];
							} else {
								return '';
							}
						},
						$wpssw_response_val->values
					);
				}

				$wpssw_existingdata[ $wpssw_sheettitle ] = $wpssw_data;
			}

			$wpssw_dataarray = array();
			$wpssw_isexecute = 0;
			$response        = array();

			foreach ( $wpssw_activesheets as $wpssw_sheet_slug => $wpssw_sheetname ) {
				$is_product_sheet = 0;
				if ( in_array( $wpssw_sheetname, $wpssw_productsheets_list, true ) ) {
					$is_product_sheet = 1;
				}

				// Exclude existing order IDs if applicable.
				$wpssw_oldids = $wpssw_existingdata[ $wpssw_product_setting_sheets[ $wpssw_sheet_slug ] ] ? parent::wpssw_convert_int( $wpssw_existingdata[ $wpssw_product_setting_sheets[ $wpssw_sheet_slug ] ] ) : array();

				$wpssw_all_orders = array();
				$wpssw_query_args = array(
					'post_type'      => 'product',
					'posts_per_page' => -1,
					'orderby'        => 'ID',
					'order'          => 'ASC',
				);
				if ( isset( $wpssw_fromdate ) && isset( $wpssw_todate ) && ! empty( $wpssw_fromdate ) && ! empty( $wpssw_todate ) ) {
					$wpssw_query_args['date_query'] = array(
						array(
							'after'     => $wpssw_fromdate,
							'before'    => $wpssw_todate,
							'inclusive' => true,
						),
					);
				}

				if ( is_array( $wpssw_oldids ) && ! empty( $wpssw_oldids ) ) {
					$wpssw_query_args['post__not_in'] = $wpssw_oldids;
					$wpssw_oldids                     = null;
				}
				if ( $is_product_sheet ) {
					$include_child                  = false;
					$wpssw_include_child_categories = self::wpssw_option( 'wpssw_include_child_categories' );
					if ( 'yes' === (string) $wpssw_include_child_categories ) {
						$include_child = true;
					}
					// phpcs:ignore.
					$wpssw_query_args['tax_query'] = array(
						array(
							'taxonomy'         => 'product_cat',
							'field'            => 'term_id',
							'terms'            => $wpssw_sheet_slug,
							'include_children' => $include_child,
						),
					);
				}

				$is_wpml_sitepress_active = self::wpssw_is_wpml_sitepress_active();

				$wpml_filters_removed = 0;
				if ( $is_wpml_sitepress_active ) {
					$wpssw_sync_custom_langs = isset( $_POST['prd_sync_custom_langs'] ) ? sanitize_text_field( wp_unslash( $_POST['prd_sync_custom_langs'] ) ) : '';
					$wpssw_syncall_langs     = isset( $_POST['prd_sync_all_langs'] ) ? sanitize_text_field( wp_unslash( $_POST['prd_sync_all_langs'] ) ) : '';

					if ( 'true' === (string) $wpssw_syncall_langs ) {
						$wpssw_query_args['suppress_filters'] = 1;
					} else {
						global $wpml_query_filter;
						if ( has_filter( 'posts_join', array( $wpml_query_filter, 'posts_join_filter' ) ) ) {
							self::wpssw_remove_wpml_filters();
							$wpml_filters_removed = 1;
						}
						$languages_arr = array_filter( explode( ',', $wpssw_sync_custom_langs ) );
						if ( ! empty( $languages_arr ) ) {
							$languages                                   = "'" . implode( "','", array_map( 'trim', $languages_arr ) ) . "'";
							$wpssw_query_args['wpssw_custom_wpml_langs'] = $languages;
						}
					}
				}
				$wpssw_query_args['fields'] = 'ids'; // Fetch only ids.

				$variation_product_ids = array();

				if ( isset( $product_sale_fromdate ) && isset( $product_sale_todate ) && ! empty( $product_sale_fromdate ) && ! empty( $product_sale_todate ) ) {
					$sale_from_timestamp = strtotime( $product_sale_fromdate . ' 00:00:00' );
					$sale_to_timestamp   = strtotime( $product_sale_todate . ' 23:59:59' );
					// phpcs:ignore.
					$wpssw_query_args['meta_query'] = array(
						'relation' => 'AND',
						array(
							'key'     => '_sale_price_dates_from',
							'value'   => $sale_from_timestamp,
							'compare' => '>=',
							'type'    => 'NUMERIC',
						),
						array(
							'key'     => '_sale_price_dates_to',
							'value'   => $sale_to_timestamp,
							'compare' => '<=',
							'type'    => 'NUMERIC',
						),
					);
					$wpssw_variation_query_args              = $wpssw_query_args;
					$wpssw_variation_query_args['post_type'] = 'product_variation';

					if ( $is_product_sheet ) {
						if ( isset( $wpssw_variation_query_args['tax_query'] ) ) {
							unset( $wpssw_variation_query_args['tax_query'] );
						}
					}

					$wpsswcustom_variation_query = new WP_Query( $wpssw_variation_query_args );

					$wpssw_all_variation_prd_arr = $wpsswcustom_variation_query->posts;
					$variation_prdcount          = 0;
					$variation_prdcount          = $wpsswcustom_variation_query->found_posts;

					if ( ! empty( $wpssw_all_variation_prd_arr ) && $variation_prdcount > 0 ) {
						foreach ( $wpssw_all_variation_prd_arr as $variation_id ) {
							$variation_post_type = get_post_type( $variation_id );
							if ( 'product_variation' === (string) $variation_post_type ) {
								$parent_id = wp_get_post_parent_id( $variation_id );
								if ( $parent_id && ! in_array( $parent_id, $variation_product_ids, true ) ) {
									if ( $is_product_sheet ) {
										$cat_terms = wp_get_post_terms( $parent_id, 'product_cat' );
										if ( ! empty( $cat_terms ) && ! is_wp_error( $cat_terms ) ) {
											$cat_terms_id_arr = wp_list_pluck( $cat_terms, 'term_id' );
											if ( in_array( $wpssw_sheet_slug, $cat_terms_id_arr, true ) ) {
												$variation_product_ids[] = $parent_id;
											}
										}
										continue;
									}
									$variation_product_ids[] = $parent_id;
								}
							}
						}
					}
					$wpsswcustom_variation_query = null;
					$wpssw_all_variation_prd_arr = null;
				}

				$wpsswcustom_query = new WP_Query( $wpssw_query_args );

				$wpssw_all_orders = $wpsswcustom_query->posts;
				$productcount     = 0;
				$productcount     = $wpsswcustom_query->found_posts;

				$wpsswcustom_query = null;

				if ( 1 === (int) $wpml_filters_removed ) {
					self::wpssw_add_wpml_filters();
				}

				if ( ! empty( $variation_product_ids ) ) {
					if ( ! empty( $wpssw_all_orders ) && is_array( $wpssw_all_orders ) ) {
						$wpssw_all_orders = array_merge( $wpssw_all_orders, $variation_product_ids );
					} else {
						$wpssw_all_orders = $variation_product_ids;
					}
					$wpssw_all_orders = array_filter( array_unique( $wpssw_all_orders ) );

					if ( isset( $wpssw_variation_query_args['post__not_in'] ) && ! empty( $wpssw_variation_query_args['post__not_in'] ) ) {
						$intersection = array_intersect( $wpssw_all_orders, $wpssw_variation_query_args['post__not_in'] );
						if ( ! empty( $intersection ) ) {
							$wpssw_all_orders = array_diff( $wpssw_all_orders, $intersection );
						}
					}
					$wpssw_all_orders = array_filter( array_unique( $wpssw_all_orders ) );
					$productcount     = count( $wpssw_all_orders );
				}

				if ( empty( $wpssw_all_orders ) ) {
					continue;
				}
				$wpssw_values_array = array();

				if ( $productcount > 0 ) {

					$productlimit = apply_filters( 'wpssw_product_sync_limit', 500 );
					$response[]   = array(
						'sheet_name'    => $wpssw_product_setting_sheets[ $wpssw_sheet_slug ],
						'sheet_slug'    => $wpssw_sheet_slug,
						'totalproducts' => $productcount,
						'productlimit'  => $productlimit,
					);
				}
			}

			echo wp_json_encode( $response );
			die;

		}
		/**
		 * Get attribute count for syncronization
		 */
		public static function wpssw_get_attribute_count() {
			if ( ! isset( $_POST['wpssw_attribute_settings'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wpssw_attribute_settings'] ) ), 'save_product_settings' ) ) {
				echo esc_html__( 'Sorry, your nonce did not verify.', 'wpssw' );
				die();
			}
			if ( ! self::$instance_api->checkcredenatials() ) {
				return;
			}
			global $wpdb;
			// @codingStandardsIgnoreStart.
			$attribute_taxonomies = $wpdb->get_results( 'SELECT * FROM ' . $wpdb->prefix . "woocommerce_attribute_taxonomies WHERE attribute_name != '' ORDER BY attribute_name ASC;" ); //db call ok.
			// @codingStandardsIgnoreEnd.
			$wpssw_sheetname = 'Product Attributes';

			$wpssw_product_spreadsheet_setting = parent::wpssw_option( 'wpssw_product_spreadsheet_setting' );
			$wpssw_spreadsheetid               = parent::wpssw_option( 'wpssw_product_spreadsheet_id' );

			if ( 'yes' !== (string) $wpssw_product_spreadsheet_setting ) {
				return;
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
			$attributecount = 0;
			foreach ( $attribute_taxonomies as $key => $attribute ) {
				if ( in_array( (int) $attribute->attribute_id, parent::wpssw_convert_int( $wpssw_data ), true ) ) {
					continue;
				}
				$attributecount++;
			}

			$attributelimit = apply_filters( 'wpssw_attribute_sync_limit', 500 );
			echo wp_json_encode(
				array(
					'totalattributes' => $attributecount,
					'attributelimit'  => $attributelimit,
				)
			);
			die;
		}
		/**
		 * Sync Products
		 */
		public static function wpssw_sync_products() {

			if ( ! isset( $_POST['wpssw_product_settings'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wpssw_product_settings'] ) ), 'save_product_settings' ) ) {
				echo esc_html__( 'Sorry, your nonce did not verify.', 'wpssw' );
				die();
			}
			if ( ! self::$instance_api->checkcredenatials() ) {
				return;
			}
			$wpssw_product_spreadsheet_setting = parent::wpssw_option( 'wpssw_product_spreadsheet_setting' );
			$wpssw_spreadsheetid               = parent::wpssw_option( 'wpssw_product_spreadsheet_id' );

			if ( 'yes' !== (string) $wpssw_product_spreadsheet_setting ) {
				return;
			}

			$wpssw_inputoption = self::wpssw_get_product_inputoption();

			$wpssw_fromdate = isset( $_POST['prd_sync_all_fromdate'] ) ? sanitize_text_field( wp_unslash( $_POST['prd_sync_all_fromdate'] ) ) : '';
			$wpssw_todate   = isset( $_POST['prd_sync_all_todate'] ) ? sanitize_text_field( wp_unslash( $_POST['prd_sync_all_todate'] ) ) : '';

			$product_sale_fromdate = isset( $_POST['prd_sync_all_sale_fromdate'] ) ? sanitize_text_field( wp_unslash( $_POST['prd_sync_all_sale_fromdate'] ) ) : '';
			$product_sale_todate   = isset( $_POST['prd_sync_all_sale_todate'] ) ? sanitize_text_field( wp_unslash( $_POST['prd_sync_all_sale_todate'] ) ) : '';

			$wpssw_syncall = isset( $_POST['prd_sync_all'] ) ? sanitize_text_field( wp_unslash( $_POST['prd_sync_all'] ) ) : '';

			$wpssw_sheet_slug   = isset( $_POST['sheetslug'] ) ? sanitize_text_field( wp_unslash( $_POST['sheetslug'] ) ) : '';
			$wpssw_sheetname    = isset( $_POST['sheetname'] ) ? sanitize_text_field( wp_unslash( $_POST['sheetname'] ) ) : '';
			$wpssw_productcount = isset( $_POST['productcount'] ) ? sanitize_text_field( wp_unslash( $_POST['productcount'] ) ) : '';
			$wpssw_productlimit = isset( $_POST['productlimit'] ) ? sanitize_text_field( wp_unslash( $_POST['productlimit'] ) ) : '';

			$args = array(
				'post_type'      => 'product',
				'posts_per_page' => -1,
				'orderby'        => 'ID',
				'order'          => 'ASC',
			);
			if ( isset( $wpssw_fromdate ) && isset( $wpssw_todate ) && ! empty( $wpssw_fromdate ) && ! empty( $wpssw_todate ) ) {
				$args['date_query'] = array(
					array(
						'after'     => $wpssw_fromdate,
						'before'    => $wpssw_todate,
						'inclusive' => true,
					),
				);
			}
			$wpssw_productsheets_list          = array();
			$wpssw_product_categories_as_sheet = parent::wpssw_option( 'product_categories_as_sheet' );
			$is_product_sheet                  = 0;
			if ( 'yes' === (string) $wpssw_product_categories_as_sheet ) {
				$wpssw_productsheets_list = parent::wpssw_get_product_category_saved_sheets();
				if ( ! $wpssw_productsheets_list ) {
					$wpssw_productsheets_list = array();
				}
				if ( ! empty( $wpssw_sheetname ) && in_array( $wpssw_sheetname, $wpssw_productsheets_list, true ) ) {
					$is_product_sheet = 1;
				}
			}

			if ( 1 === (int) $is_product_sheet ) {
				$include_child                  = false;
				$wpssw_include_child_categories = self::wpssw_option( 'wpssw_include_child_categories' );
				if ( 'yes' === (string) $wpssw_include_child_categories ) {
					$include_child = true;
				}
				// phpcs:ignore.
				$args['tax_query'] = array(
					array(
						'taxonomy'         => 'product_cat',
						'field'            => 'term_id',
						'terms'            => $wpssw_sheet_slug,
						'include_children' => $include_child,
					),
				);
			}

			$is_wpml_sitepress_active = self::wpssw_is_wpml_sitepress_active();

			$wpml_filters_removed = 0;
			if ( $is_wpml_sitepress_active ) {
				$wpssw_sync_custom_langs = isset( $_POST['prd_sync_custom_langs'] ) ? sanitize_text_field( wp_unslash( $_POST['prd_sync_custom_langs'] ) ) : '';
				$wpssw_syncall_langs     = isset( $_POST['prd_sync_all_langs'] ) ? sanitize_text_field( wp_unslash( $_POST['prd_sync_all_langs'] ) ) : '';

				if ( 'true' === (string) $wpssw_syncall_langs ) {
					$args['suppress_filters'] = 1;
				} else {
					global $wpml_query_filter;
					if ( has_filter( 'posts_join', array( $wpml_query_filter, 'posts_join_filter' ) ) ) {
						self::wpssw_remove_wpml_filters();
						$wpml_filters_removed = 1;
					}
					$languages_arr = array_filter( explode( ',', $wpssw_sync_custom_langs ) );
					if ( ! empty( $languages_arr ) ) {
						$languages                       = "'" . implode( "','", array_map( 'trim', $languages_arr ) ) . "'";
						$args['wpssw_custom_wpml_langs'] = $languages;
					}
				}
			}
			$args['fields']         = 'ids'; // Fetch only ids.
			$args['posts_per_page'] = $wpssw_productlimit;

			$wpssw_sheet      = "'" . $wpssw_sheetname . "'!A:A";
			$wpssw_allentry   = self::$instance_api->get_row_list( $wpssw_spreadsheetid, $wpssw_sheet );
			$wpssw_data       = $wpssw_allentry->getValues();
			$wpssw_allentry   = null;
			$wpssw_data       = array_map(
				function( $wpssw_element ) {
					if ( isset( $wpssw_element['0'] ) ) {
						return (int) $wpssw_element['0'];
					} else {
						return '';
					}
				},
				$wpssw_data
			);
			$wpssw_data_count = count( $wpssw_data );
			if ( is_array( $wpssw_data ) && ! empty( $wpssw_data ) ) {
				$args['post__not_in'] = array_values( array_filter( array_unique( $wpssw_data ) ) );
				$wpssw_data           = null;
			}

			$variation_product_ids = array();
			if ( isset( $product_sale_fromdate ) && isset( $product_sale_todate ) && ! empty( $product_sale_fromdate ) && ! empty( $product_sale_todate ) ) {
				$sale_from_timestamp = strtotime( $product_sale_fromdate . ' 00:00:00' );
				$sale_to_timestamp   = strtotime( $product_sale_todate . ' 23:59:59' );
				// phpcs:ignore.
				$args['meta_query']  = array(
					'relation' => 'AND',
					array(
						'key'     => '_sale_price_dates_from',
						'value'   => $sale_from_timestamp,
						'compare' => '>=',
						'type'    => 'NUMERIC',
					),
					array(
						'key'     => '_sale_price_dates_to',
						'value'   => $sale_to_timestamp,
						'compare' => '<=',
						'type'    => 'NUMERIC',
					),
				);
				$wpssw_variation_query_args              = $args;
				$wpssw_variation_query_args['post_type'] = 'product_variation';
				if ( $is_product_sheet ) {
					if ( isset( $wpssw_variation_query_args['tax_query'] ) ) {
						unset( $wpssw_variation_query_args['tax_query'] );
					}
				}

				$wpsswcustom_variation_query = new WP_Query( $wpssw_variation_query_args );

				$wpssw_all_variation_prd_arr = $wpsswcustom_variation_query->posts;
				$variation_prdcount          = 0;
				$variation_prdcount          = $wpsswcustom_variation_query->found_posts;

				if ( ! empty( $wpssw_all_variation_prd_arr ) && $variation_prdcount > 0 ) {
					foreach ( $wpssw_all_variation_prd_arr as $variation_id ) {
						$variation_post_type = get_post_type( $variation_id );
						if ( 'product_variation' === (string) $variation_post_type ) {
							$parent_id = wp_get_post_parent_id( $variation_id );
							if ( $parent_id && ! in_array( $parent_id, $variation_product_ids, true ) ) {
								if ( $is_product_sheet ) {
									$cat_terms = wp_get_post_terms( $parent_id, 'product_cat' );
									if ( ! empty( $cat_terms ) && ! is_wp_error( $cat_terms ) ) {
										$cat_terms_id_arr = wp_list_pluck( $cat_terms, 'term_id' );
										if ( in_array( (int) $wpssw_sheet_slug, $cat_terms_id_arr, true ) ) {
											$variation_product_ids[] = $parent_id;
										}
									}
									continue;
								}
								$variation_product_ids[] = $parent_id;
							}
						}
					}
				}
				$wpsswcustom_variation_query = null;
				$wpssw_all_variation_prd_arr = null;
			}

			$products = new WP_Query( $args );

			if ( 1 === (int) $wpml_filters_removed ) {
				self::wpssw_add_wpml_filters();
			}

			if ( empty( $products ) || is_wp_error( $products ) ) {
				die();
			}

			$rangetofind        = $wpssw_sheetname . '!A' . ( $wpssw_data_count + 1 );
			$wpssw_values_array = array();
			$newproduct         = 0;

			$product_ids = $products->posts;
			if ( ! empty( $variation_product_ids ) ) {
				if ( ! empty( $product_ids ) && is_array( $product_ids ) ) {
					$product_ids = array_merge( $product_ids, $variation_product_ids );
				} else {
					$product_ids = $variation_product_ids;
				}
				$product_ids = array_filter( array_unique( $product_ids ) );

				if ( isset( $wpssw_variation_query_args['post__not_in'] ) && ! empty( $wpssw_variation_query_args['post__not_in'] ) ) {
					$intersection = array_intersect( $product_ids, $wpssw_variation_query_args['post__not_in'] );
					if ( ! empty( $intersection ) ) {
						$product_ids = array_diff( $product_ids, $intersection );
					}
				}
				$product_ids = array_filter( array_unique( $product_ids ) );
			}

			if ( empty( $product_ids ) ) {
				die();
			} else {
				sort( $product_ids );
			}
			foreach ( $product_ids as $product_id ) {
				if ( $newproduct < $wpssw_productlimit ) {
					set_time_limit( 999 );
					$product = wc_get_product( $product_id );

					if ( ! empty( $product->get_children() ) && 'grouped' !== (string) $product->get_type() ) {
						$wpssw_values_array[] = self::wpssw_make_product_value_array( 'insert', $product_id );
						foreach ( $product->get_children() as $child ) {
							$wpssw_values_array[] = self::wpssw_make_product_value_array( 'insert', $child, true );
						}
					} else {
						$wpssw_values_array[] = self::wpssw_make_product_value_array( 'insert', $product_id );
					}
					$newproduct++;
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
					parent::wpssw_inherit_row_format_style( 'product', $wpssw_inherit_params );

					$wpssw_requestbody = self::$instance_api->valuerangeobject( $wpssw_values_array );
					$wpssw_params      = array( 'valueInputOption' => $wpssw_inputoption );

					$param          = self::$instance_api->setparamater( $wpssw_spreadsheetid, $rangetofind, $wpssw_requestbody, $wpssw_params );
					$wpssw_response = self::$instance_api->appendentry( $param );

				} catch ( Exception $e ) {
					echo esc_html( 'Message: ' . $e->getMessage() );
				}
			}
			echo 'successful';
			die;
		}
		/**
		 * Sync Attributes
		 */
		public static function wpssw_sync_attributes() {
			if ( ! isset( $_POST['wpssw_attributes_settings'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wpssw_attributes_settings'] ) ), 'save_product_settings' ) ) {
				echo esc_html__( 'Sorry, your nonce did not verify.', 'wpssw' );
				die();
			}
			if ( ! self::$instance_api->checkcredenatials() ) {
				return;
			}
			$wpssw_product_spreadsheet_setting = parent::wpssw_option( 'wpssw_product_spreadsheet_setting' );
			$wpssw_spreadsheetid               = parent::wpssw_option( 'wpssw_product_spreadsheet_id' );

			if ( 'yes' !== (string) $wpssw_product_spreadsheet_setting ) {
				return;
			}

			$wpssw_inputoption    = self::wpssw_get_product_inputoption();
			$wpssw_sheetname      = 'Product Attributes';
			$wpssw_attributecount = isset( $_POST['attributecount'] ) ? sanitize_text_field( wp_unslash( $_POST['attributecount'] ) ) : '';
			$wpssw_attributelimit = isset( $_POST['attributelimit'] ) ? sanitize_text_field( wp_unslash( $_POST['attributelimit'] ) ) : '';

			$wpssw_sheet        = "'" . $wpssw_sheetname . "'!A:A";
			$wpssw_allentry     = self::$instance_api->get_row_list( $wpssw_spreadsheetid, $wpssw_sheet );
			$wpssw_data         = $wpssw_allentry->getValues();
			$wpssw_allentry     = null;
			$wpssw_data         = array_map(
				function( $wpssw_element ) {
					if ( isset( $wpssw_element['0'] ) ) {
						return $wpssw_element['0'];
					} else {
						return '';
					}
				},
				$wpssw_data
			);
			$rangetofind        = $wpssw_sheetname . '!A' . ( count( $wpssw_data ) + 1 );
			$wpssw_values_array = array();
			$newattribute       = 0;
			$wpssw_attr_headers = self::wpssw_attribute_headers();
			global $wpdb;
			// @codingStandardsIgnoreStart.
			$attribute_taxonomies = $wpdb->get_results( 'SELECT * FROM ' . $wpdb->prefix . "woocommerce_attribute_taxonomies WHERE attribute_name != '' ORDER BY attribute_id ASC;" ); //db call ok.
			// @codingStandardsIgnoreEnd.
			foreach ( $attribute_taxonomies as $key => $attribute ) {
				if ( in_array( (int) $attribute->attribute_id, parent::wpssw_convert_int( $wpssw_data ), true ) ) {
					continue;
				}
				$wpssw_attrval = array();
				foreach ( $wpssw_attr_headers as $key => $header ) {
					if ( 'Attribute ID' === (string) $header ) {
						$wpssw_attrval[] = $attribute->attribute_id;
						continue;
					}
					if ( 'Attribute Name' === (string) $header ) {
						$wpssw_attrval[] = $attribute->attribute_name;
						continue;
					}
					if ( 'Attribute Label' === (string) $header ) {
						$wpssw_attrval[] = $attribute->attribute_label;
						continue;
					}
					if ( 'Attribute Type' === (string) $header ) {
						$wpssw_attrval[] = $attribute->attribute_type;
						continue;
					}
					if ( 'Attribute Orderby' === (string) $header ) {
						$wpssw_attrval[] = $attribute->attribute_orderby;
						continue;
					}
					if ( 'Attribute Terms' === (string) $header ) {
						$slug        = 'pa_' . $attribute->attribute_name;
						$terms       = get_terms(
							array(
								'taxonomy'   => $slug,
								'hide_empty' => 0,
							)
						);
						$termsstring = array();
						foreach ( $terms as $term ) {
							$termsstring[] = $term->name;
						}
						$wpssw_attrval[] = implode( ',', $termsstring );
						continue;
					}
					$newattribute++;
				}
				$wpssw_values_array[] = $wpssw_attrval;
			}
			if ( ! empty( $wpssw_values_array ) ) {
				try {

					// If there is no sheet_id available, leave the sheet_id field blank. Otherwise, if there is a sheet_id, leave the sheet_name field blank.
					$wpssw_inherit_params = array(
						'sheet_id'       => '',
						'sheet_name'     => $wpssw_sheetname,
						'start_index'    => count( $wpssw_data ),
						'end_index'      => count( $wpssw_data ) + count( $wpssw_values_array ),
						'spreadsheet_id' => $wpssw_spreadsheetid,
					);
					parent::wpssw_inherit_row_format_style( 'product', $wpssw_inherit_params );

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
		 * Clear Product settings sheet
		 */
		public static function wpssw_clear_productsheet() {
			$wpssw_product_spreadsheet_setting = parent::wpssw_option( 'wpssw_product_spreadsheet_setting' );
			$wpssw_spreadsheetid               = parent::wpssw_option( 'wpssw_product_spreadsheet_id' );

			if ( 'yes' !== (string) $wpssw_product_spreadsheet_setting ) {
				echo 'Please save settings.';
				die();
			}
			$requestbody               = self::$instance_api->clearobject();
			$total_headers             = count( parent::wpssw_option( 'wpssw_woo_product_headers' ) ) + 2;
			$last_column               = parent::wpssw_get_column_index( $total_headers );
			$wpssw_existingsheetsnames = array();
			$wpssw_response            = self::$instance_api->get_sheet_listing( $wpssw_spreadsheetid );
			$wpssw_existingsheetsnames = self::$instance_api->get_sheet_list( $wpssw_response );
			$wpssw_existingsheetsnames = array_flip( $wpssw_existingsheetsnames );

			$wpssw_product_setting_sheets = array_filter( (array) parent::wpssw_option( 'wpssw_product_setting_sheets' ) );
			if ( ! $wpssw_product_setting_sheets ) {
				$wpssw_product_setting_sheets = array( 'all_products' => 'All Products' );
			}

			$wpssw_product_categories_as_sheet = parent::wpssw_option( 'product_categories_as_sheet' );
			$wpssw_productsheets_list          = array();

			$wpssw_sheetnames = array();

			foreach ( $wpssw_product_setting_sheets as $sheet_slug => $sheet_name ) {
				if ( ! empty( $sheet_name ) && in_array( $sheet_name, $wpssw_existingsheetsnames, true ) ) {
					$wpssw_sheetnames[] = $sheet_name;
				}
			}
			if ( 'yes' === (string) $wpssw_product_categories_as_sheet ) {
				$wpssw_productsheets_list = parent::wpssw_get_product_category_saved_sheets();
				if ( ! $wpssw_productsheets_list ) {
					$wpssw_productsheets_list = array();
				}
				foreach ( $wpssw_productsheets_list as $cat_id => $cat_name ) {
					if ( ! empty( $cat_name ) && in_array( $cat_name, $wpssw_existingsheetsnames, true ) ) {
						$wpssw_sheetnames[] = $cat_name;
					}
				}
			}

			foreach ( $wpssw_sheetnames as $wpssw_sheetname ) {
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
		 * Prepare array value of product data to insert into sheet.
		 *
		 * @param string  $wpssw_operation operation to perfom on sheet.
		 * @param int     $wpssw_product_id Produt ID.
		 * @param boolean $wpssw_child True if child product.
		 * @return array $product_value_array
		 */
		public static function wpssw_make_product_value_array( $wpssw_operation = 'insert', $wpssw_product_id = 0, $wpssw_child = false ) {
			if ( ! $wpssw_product_id ) {
				return array();
			}
			$wpssw_include = new WPSSW_Include_Action();
			$wpssw_include->wpssw_include_product_compatibility_files();
			$wpssw_product                          = wc_get_product( $wpssw_product_id );
			$wpssw_woo_selections                   = stripslashes_deep( parent::wpssw_option( 'wpssw_woo_product_headers' ) );
			$wpssw_headers                          = apply_filters( 'wpsyncsheets_product_headers', array() );
			$wpssw_classarray                       = array();
			$wpssw_headers['WPSSW_Default_Headers'] = parent::wpssw_array_flatten( $wpssw_headers['WPSSW_Default_Headers'] );

			$wpssw_static_product_header_values = stripslashes_deep( parent::wpssw_option( 'wpssw_static_product_header_values' ) );
			$wpssw_custom_value                 = array();
			$wpssw_static_header_name           = array();
			if ( ! empty( $wpssw_static_product_header_values ) ) {
				foreach ( $wpssw_static_product_header_values as $wpssw_static_header_value ) {
					if ( strpos( $wpssw_static_header_value, ',(static_header),' ) ) {
						$wpssw_static_header_value = str_replace( ',(static_header),', ',', $wpssw_static_header_value );
						$wpssw_custom_value[]      = explode( ',', $wpssw_static_header_value );
					}
				}
				if ( ! empty( $wpssw_custom_value ) ) {
					$wpssw_static_header_name = array_column( $wpssw_custom_value, 0 );
				}
			}

			$wpssw_woo_selections_count = count( $wpssw_woo_selections );
			for ( $i = 0; $i < $wpssw_woo_selections_count; $i++ ) {

				if ( in_array( $wpssw_woo_selections[ $i ], $wpssw_static_header_name, true ) ) {
					$wpssw_classarray[ $wpssw_woo_selections[ $i ] ] = 'WPSSW_Default_Headers';
					continue;
				}

				$wpssw_classarray[ $wpssw_woo_selections[ $i ] ] = parent::wpssw_find_class( $wpssw_headers, $wpssw_woo_selections[ $i ] );
			}
			$wpssw_product_row = array();
			if ( ! empty( $wpssw_product->get_parent_id() ) && 'grouped' !== (string) $wpssw_product->get_type() ) {
				$pid                 = $wpssw_product->get_parent_id();
				$wpssw_product_row[] = $pid;
			} else {
				$wpssw_product_row[] = $wpssw_product_id;
			}
			if ( ! empty( $wpssw_product->get_parent_id() ) && preg_match( '/variation/', (string) $wpssw_product->get_type() ) ) {
				$wpssw_product_row[] = $wpssw_product->get_id();
			} else {
				$wpssw_product_row[] = '';
			}

			foreach ( $wpssw_classarray as $headername => $classname ) {
				if ( ! empty( $classname ) ) {
					$wpssw_product_row[] = $classname::get_value( $headername, $wpssw_product, $wpssw_child, $wpssw_custom_value );
				} else {
					$wpssw_product_row[] = '';
				}
			}
			$wpssw_product_row = parent::wpssw_cleanarray( $wpssw_product_row, count( $wpssw_woo_selections ) + 1 );
			return $wpssw_product_row;
		}
		/**
		 * Get products all attributes.
		 */
		public static function wpssw_get_all_attributes() {
			global $wpdb;
			$wpssw_attribute_taxonomies = array();
			$table_name                 = $wpdb->prefix;
			$attribute_name             = '';
			// @codingStandardsIgnoreStart.
			$wpssw_attribute_taxonomies = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . "woocommerce_attribute_taxonomies WHERE attribute_name != '' ORDER BY attribute_name ASC;" ); // db call ok.
			// @codingStandardsIgnoreEnd.
			set_transient( 'wc_attribute_taxonomies', $wpssw_attribute_taxonomies );
			$wpssw_attribute_taxonomies = array_column( array_filter( $wpssw_attribute_taxonomies ), 'attribute_name' );
			$wpssw_attribute_taxonomies = array_map(
				function( $e ) {
					return str_replace( '-', ' ', ucfirst( str_replace( 'pa_', '', $e ) ) );
				},
				$wpssw_attribute_taxonomies
			);
			$wpssw_product_attr         = self::wpssw_get_meta_values( '_product_attributes' );
			$wpssw_product_attr         = array_map(
				function( $e ) {
					return array_keys( $e );
				},
				$wpssw_product_attr
			);
			$wpssw_product_attr         = self::wpssw_array_flatten( $wpssw_product_attr );
			$wpssw_product_attr         = array_map(
				function( $e ) {
					return str_replace( '-', ' ', ucfirst( str_replace( 'pa_', '', rawurldecode( $e ) ) ) );
				},
				$wpssw_product_attr
			);
			$wpssw_product_attr         = array_unique( $wpssw_product_attr );
			$wpssw_attribute_taxonomies = array_unique( array_merge( $wpssw_attribute_taxonomies, $wpssw_product_attr ) );
			return $wpssw_attribute_taxonomies;
		}
		/**
		 * Get product meta value for wpssw_get_all_attributes function
		 *
		 * @param string $wpssw_meta_key .
		 * @param string $wpssw_post_type .
		 */
		public static function wpssw_get_meta_values( $wpssw_meta_key, $wpssw_post_type = 'product' ) {
			// @codingStandardsIgnoreStart.
			$wpssw_posts       = get_posts(
				array(
					'post_type'      => $wpssw_post_type,
					'meta_key'       => $wpssw_meta_key,
					'posts_per_page' => -1,
					'fields'         => 'ids'
				)
			);
			// @codingStandardsIgnoreEnd.
			$wpssw_meta_values = array();
			foreach ( $wpssw_posts as $wpssw_post ) {
				$wpssw_meta = get_post_meta( $wpssw_post, $wpssw_meta_key, true );
				if ( $wpssw_meta ) {
					$wpssw_meta_values[] = $wpssw_meta;
				}
			}
			return $wpssw_meta_values;
		}
		/**
		 * Insert WPSyncSheets column at Product page
		 *
		 * @param array $columns product page columns array.
		 * @return array $columns
		 */
		public static function wpssw_product_column_syncbtn( $columns ) {
			$reordered_columns   = array();
			$wpssw_spreadsheetid = parent::wpssw_option( 'wpssw_product_spreadsheet_id' );
			$spreadsheets_list   = self::$instance_api->get_spreadsheet_listing();
			if ( ! self::$instance_api->checkcredenatials() || empty( $wpssw_spreadsheetid ) || ( ! empty( $wpssw_spreadsheetid ) && ! array_key_exists( $wpssw_spreadsheetid, $spreadsheets_list ) ) ) {
				return $columns;
			}
			// @codingStandardsIgnoreStart.
			if ( ! isset( $_GET['post_status'] ) || ( isset( $_GET['post_status'] ) && 'trash' !== sanitize_text_field( wp_unslash( $_GET['post_status'] ) ) ) ) {
			// @codingStandardsIgnoreEnd.
				// Inserting columns to a specific location.
				foreach ( $columns as $key => $column ) {
					$reordered_columns[ $key ] = $column;
					if ( 'date' === (string) $key ) {
						// Inserting after "Total" column.
						$reordered_columns['wpssw_syncbtn_column'] = __( 'WPSyncSheets', 'wpssw' );
					}
				}
				return $reordered_columns;
			}
			return $columns;
		}
		/**
		 * Insert Sync button in WPSyncSheets column at product page
		 *
		 * @param string $column products WPSyncSheets column.
		 * @param int    $post_id .
		 */
		public static function wpssw_product_page_syncbtn( $column, $post_id ) {
			if ( 'wpssw_syncbtn_column' === (string) $column ) {
				echo '<a href="#" class="button wpssw_single_product_sync_btn">' . esc_html__( 'Click to Sync', 'wpssw' ) . '</a>
					<img src="' . esc_url( admin_url( 'images/spinner.gif' ) ) . '" class="syncbtnloader">';
			}
		}
		/**
		 * Sync single product
		 */
		public static function wpssw_sync_single_product_data() {

			if ( ! isset( $_POST['product_sync_nonce_token'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['product_sync_nonce_token'] ) ), 'sync_nonce' )
			) {
				echo 'nonceerror';
				die();
			}
			if ( ! self::$instance_api->checkcredenatials() ) {
				return;
			}
			$product_id                        = isset( $_POST['product_id'] ) ? sanitize_text_field( wp_unslash( $_POST['product_id'] ) ) : '';
			$wpssw_product_spreadsheet_setting = parent::wpssw_option( 'wpssw_product_spreadsheet_setting' );
			$wpssw_spreadsheetid               = parent::wpssw_option( 'wpssw_product_spreadsheet_id' );

			if ( 'yes' !== (string) $wpssw_product_spreadsheet_setting ) {
				echo 'savesettings';
				die;
			}
			$wpssw_sheetname = 'All Products';

			$wpssw_inputoption = self::wpssw_get_product_inputoption();
			if ( ! empty( $wpssw_spreadsheetid ) ) {
				$wpssw_spreadsheets_list = self::$instance_api->get_spreadsheet_listing();

				if ( ! array_key_exists( $wpssw_spreadsheetid, $wpssw_spreadsheets_list ) ) {
					echo 'savesettings';
					die;
				}

				$wpssw_response            = self::$instance_api->get_sheet_listing( $wpssw_spreadsheetid );
				$wpssw_existingsheetsnames = self::$instance_api->get_sheet_list( $wpssw_response );

				$wpssw_product_setting_sheets = array_filter( (array) parent::wpssw_option( 'wpssw_product_setting_sheets' ) );
				if ( ! $wpssw_product_setting_sheets ) {
					$wpssw_product_setting_sheets = array( 'all_products' => 'All Products' );
				}

				$wpssw_product_categories_as_sheet = parent::wpssw_option( 'product_categories_as_sheet' );
				$wpssw_productsheets_list          = array();

				$wpssw_activesheets = array();

				foreach ( $wpssw_product_setting_sheets as $sheet_slug => $sheet_name ) {
					if ( ! empty( $sheet_name ) && false !== array_key_exists( $sheet_name, $wpssw_existingsheetsnames ) ) {
						$wpssw_activesheets[ $sheet_slug ] = $sheet_name;
					}
				}
				if ( 'yes' === (string) $wpssw_product_categories_as_sheet ) {
					$wpssw_productsheets_list = parent::wpssw_get_product_category_saved_sheets();

					if ( ! $wpssw_productsheets_list ) {
						$wpssw_productsheets_list = array();
					}

					foreach ( $wpssw_productsheets_list as $cat_id => $cat_name ) {
						if ( ! empty( $cat_name ) && false !== array_key_exists( $cat_name, $wpssw_existingsheetsnames ) ) {
							$wpssw_activesheets[ $cat_id ]           = $cat_name;
							$wpssw_product_setting_sheets[ $cat_id ] = $cat_name;
						}
					}
				}
				if ( empty( $wpssw_activesheets ) ) {
					echo 'savesettings';
					die;
				}
				if ( count( $wpssw_activesheets ) > 1 ) {

					$settings_data = array(
						'setting'             => 'product',
						'setting_enable'      => $wpssw_product_spreadsheet_setting,
						'spreadsheet_id'      => $wpssw_spreadsheetid,
						'sheetname'           => 'All Products',
						'existingsheetsnames' => $wpssw_existingsheetsnames,
						'activesheets'        => $wpssw_activesheets,
					);
					self::wpssw_multiple_sheets_update_data( array( $product_id ), $settings_data, false, 'update' );
					echo 'successful';
					die;
				}

				$product     = wc_get_product( $product_id );
				$wpssw_total = self::$instance_api->get_row_list( $wpssw_spreadsheetid, $wpssw_sheetname );

				$wpssw_sheetid           = $wpssw_existingsheetsnames[ $wpssw_sheetname ];
				$wpssw_total_values      = $wpssw_total->getValues();
				$variation_productid     = array_search( 'Product Id', $wpssw_total_values[0], true );
				$variation_product_index = array_column( $wpssw_total_values, $variation_productid );
				$product_added_childs    = array();
				$add_varaition_row       = 0;
				$remove_varaition_row    = 0;
				$product_keys            = array_filter( array_keys( parent::wpssw_convert_int( $variation_product_index ), (int) $product_id, true ) );
				$lastkey_value           = ( array_slice( $product_keys, -1, 1 ) );
				$lastkey                 = '';
				if ( isset( $lastkey_value[0] ) ) {
					$lastkey = $lastkey_value[0];
				}
				if ( ! empty( $product->get_children() ) && 'grouped' !== (string) $product->get_type() ) {
					$product_childs = $product->get_children();
					if ( ! empty( $product_keys ) ) {
						if ( count( $product_childs ) > count( $product_keys ) ) {
							$add_varaition_row = count( $product_childs ) - count( $product_keys ) + 1;
						} elseif ( count( $product_childs ) < count( $product_keys ) ) {
							$remove_varaition_row = count( $product_keys ) - count( $product_childs ) - 1;
						}
						if ( $remove_varaition_row > 0 ) {
							if ( $wpssw_sheetid ) {
								$param                = array();
								$startindex           = $product_keys[0];
								$endindex             = $product_keys[0] + $remove_varaition_row;
								$param                = self::$instance_api->prepare_param( $wpssw_sheetid, $startindex, $endindex );
								$deleterequestarray[] = self::$instance_api->deleteDimensionrequests( $param, 'ROWS' );
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
					}
					$wpssw_array_value   = array();
					$wpssw_array_value[] = self::wpssw_make_product_value_array( 'insert', $product_id );
					foreach ( $product->get_children() as $child ) {
						$wpssw_child_array   = self::wpssw_make_product_value_array( 'insert', $child, true );
						$wpssw_array_value[] = $wpssw_child_array;
					}
				} else {
					if ( 'variable' !== (string) $product->get_type() || ( empty( $product->get_children() ) && 'variable' === (string) $product->get_type() ) ) {
						if ( count( $product_keys ) > 1 ) {
							if ( $wpssw_sheetid ) {
								$param                = array();
								$deleterequestarray   = array();
								$startindex           = $product_keys[0];
								$endindex             = $product_keys[0] + count( $product_keys ) - 1;
								$param                = self::$instance_api->prepare_param( $wpssw_sheetid, $startindex, $endindex );
								$deleterequestarray[] = self::$instance_api->deleteDimensionrequests( $param, 'ROWS' );
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
					}
					$wpssw_values = self::wpssw_make_product_value_array( 'insert', $product_id );
				}
				if ( $add_varaition_row > 0 && ! empty( $product_keys ) ) {
					$insert      = 0;
					$start_index = $lastkey + 1;
					$end_index   = $lastkey + $add_varaition_row + 1;
					if ( $wpssw_sheetid ) {
						$param                 = array();
						$param                 = self::$instance_api->prepare_param( $wpssw_sheetid, $start_index, $end_index );
						$product_inherit_style = self::wpssw_option( 'wpssw_product_inherit_style' );
						if ( 'no' === (string) $product_inherit_style ) {
							$requestarray[] = self::$instance_api->insertdimensionrequests( $param, 'ROWS', false );
						} else {
							$requestarray[] = self::$instance_api->insertdimensionrequests( $param, 'ROWS', true );
						}
					}
					try {
						if ( ! empty( $requestarray ) ) {
							$param                  = array();
							$param['spreadsheetid'] = $wpssw_spreadsheetid;
							$param['requestarray']  = $requestarray;
							$wpssw_response         = self::$instance_api->updatebachrequests( $param );
						}
					} catch ( Exception $e ) {
						echo esc_html( 'Message: ' . $e->getMessage() );
					}
				}
				$wpssw_woo_selections = stripslashes_deep( parent::wpssw_option( 'wpssw_woo_product_headers' ) );
				if ( ! $wpssw_woo_selections ) {
					$wpssw_woo_selections = array();
				}
				array_unshift( $wpssw_woo_selections, 'Product Id', 'Product Variation Id' );
				$wpssw_product_name_key = array_search( 'Product Name', $wpssw_woo_selections, true );
				$rangetofind            = $wpssw_product_name_key ? parent::wpssw_get_column_index( $wpssw_product_name_key + 1 ) : 'A';
				$wpssw_rangetofind      = $wpssw_sheetname . '!A:' . $rangetofind;
				$wpssw_allentry         = self::$instance_api->get_row_list( $wpssw_spreadsheetid, $wpssw_rangetofind );
				$wpssw_data             = $wpssw_allentry->getValues();
				$wpssw_data             = array_map(
					function( $element ) {
						if ( isset( $element['0'] ) ) {
							return $element['0'];
						} else {
							return '';
						}
					},
					$wpssw_data
				);
				$wpssw_num              = array_search( (int) $product_id, parent::wpssw_convert_int( $wpssw_data ), true );
				if ( $wpssw_num > 0 ) {
					if ( isset( $wpssw_array_value ) && ! empty( $wpssw_array_value ) ) {
						$wpssw_rangenum      = $wpssw_num + 1;
						$wpssw_rangetoupdate = $wpssw_sheetname . '!A' . $wpssw_rangenum;
						$wpssw_requestbody   = self::$instance_api->valuerangeobject( $wpssw_array_value );
						$wpssw_params        = array( 'valueInputOption' => $wpssw_inputoption ); // USER_ENTERED.
						$param               = self::$instance_api->setparamater( $wpssw_spreadsheetid, $wpssw_rangetoupdate, $wpssw_requestbody, $wpssw_params );
						$wpssw_response      = self::$instance_api->updateentry( $param );
					} else {
						$wpssw_rangenum      = $wpssw_num + 1;
						$wpssw_rangetoupdate = $wpssw_sheetname . '!A' . $wpssw_rangenum;
						$wpssw_requestbody   = self::$instance_api->valuerangeobject( array( $wpssw_values ) );
						$wpssw_params        = array( 'valueInputOption' => $wpssw_inputoption ); // USER_ENTERED.
						$param               = self::$instance_api->setparamater( $wpssw_spreadsheetid, $wpssw_rangetoupdate, $wpssw_requestbody, $wpssw_params );
						$wpssw_response      = self::$instance_api->updateentry( $param );
					}
				} else {
					$wpssw_isupdated = 0;
					if ( isset( $wpssw_array_value ) && ! empty( $wpssw_array_value ) ) {
						$requestarray     = array();
						$wpssw_startindex = '';
						$wpssw_endindex   = '';
						if ( count( $wpssw_data ) > 1 ) {
							$wpssw_startindex = count( $wpssw_data );
						}
						$wpssw_startindex  = parent::wpssw_insert_blankrow( $wpssw_spreadsheetid, $wpssw_sheetid, $product_id, $wpssw_array_value, $wpssw_data, $wpssw_startindex, 'product' );
						$wpssw_requestbody = self::$instance_api->valuerangeobject( $wpssw_array_value );
						$wpssw_params      = array( 'valueInputOption' => $wpssw_inputoption );
						if ( count( $wpssw_data ) > 1 ) {
							if ( ! $wpssw_startindex ) {
								$wpssw_startindex = count( $wpssw_data );
							}
							$wpssw_rangetoupdate = $wpssw_sheetname . '!A' . ( $wpssw_startindex + 1 );
							$param               = self::$instance_api->setparamater( $wpssw_spreadsheetid, $wpssw_rangetoupdate, $wpssw_requestbody, $wpssw_params );
							$wpssw_response      = self::$instance_api->updateentry( $param );
							$wpssw_isupdated     = 1;
						}
						if ( 0 === (int) $wpssw_isupdated ) {
							$param          = self::$instance_api->setparamater( $wpssw_spreadsheetid, $wpssw_sheetname, $wpssw_requestbody, $wpssw_params );
							$wpssw_response = self::$instance_api->appendentry( $param );
						}
					} else {
						$requestarray     = array();
						$wpssw_startindex = '';
						$wpssw_endindex   = '';
						if ( count( $wpssw_data ) > 1 ) {
							$wpssw_startindex = count( $wpssw_data );
						}
						$wpssw_startindex  = parent::wpssw_insert_blankrow( $wpssw_spreadsheetid, $wpssw_sheetid, $product_id, $wpssw_values, $wpssw_data, $wpssw_startindex, 'product' );
						$wpssw_requestbody = self::$instance_api->valuerangeobject( array( $wpssw_values ) );
						$wpssw_params      = array( 'valueInputOption' => $wpssw_inputoption );
						if ( $wpssw_startindex ) {
							$wpssw_rangetoupdate = $wpssw_sheetname . '!A' . ( $wpssw_startindex + 1 );
							$param               = self::$instance_api->setparamater( $wpssw_spreadsheetid, $wpssw_rangetoupdate, $wpssw_requestbody, $wpssw_params );
							$wpssw_response      = self::$instance_api->updateentry( $param );
							$wpssw_isupdated     = 1;
						}
						if ( 0 === (int) $wpssw_isupdated ) {
							$param          = array();
							$param          = self::$instance_api->setparamater( $wpssw_spreadsheetid, $wpssw_sheetname, $wpssw_requestbody, $wpssw_params );
							$wpssw_response = self::$instance_api->appendentry( $param );
						}
					}
				}
			}
			echo 'successful';
			die;
		}
		/**
		 * Insert Meta box in product page
		 */
		public static function wpssw_add_product_syncbtn_meta_box() {
			$wpssw_spreadsheetid = parent::wpssw_option( 'wpssw_product_spreadsheet_id' );
			$spreadsheets_list   = self::$instance_api->get_spreadsheet_listing();
			if ( ! self::$instance_api->checkcredenatials() || empty( $wpssw_spreadsheetid ) || ( ( ! empty( $wpssw_spreadsheetid ) && ! array_key_exists( $wpssw_spreadsheetid, $spreadsheets_list ) ) ) ) {
				return;
			}
			add_meta_box( 'wpssw_product_syncbtn_meta_box', 'WPSyncSheets', __CLASS__ . '::wpssw_product_syncbtn_meta_box_content', 'product', 'side', 'default', null );
		}
		/**
		 * Add Sync button in meta box.
		 */
		public static function wpssw_product_syncbtn_meta_box_content() {
			echo '<a href="#" id="wpssw_product_metabox_sync_btn" class="button wpssw_single_product_sync_btn">' . esc_html__( 'Click to Sync', 'wpssw' ) . '</a>
					<img src="' . esc_url( admin_url( 'images/spinner.gif' ) ) . '" id="wpssw_metabox_syncbtnloader" class="syncbtnloader">';
		}
		/**
		 * Product autosync cron run.
		 */
		public static function wpssw_autosync_products_cron_run() {

			if ( ! self::$instance_api->checkcredenatials() ) {
				return;
			}
			$wpssw_product_spreadsheet_setting = parent::wpssw_option( 'wpssw_product_spreadsheet_setting' );
			$wpssw_spreadsheetid               = parent::wpssw_option( 'wpssw_product_spreadsheet_id' );
			$wpssw_sheetname                   = 'All Products';

			if ( 'yes' !== (string) $wpssw_product_spreadsheet_setting || ! parent::wpssw_check_sheet_exist( $wpssw_spreadsheetid, $wpssw_sheetname ) ) {
				return;
			}

			$productlimit = apply_filters( 'wpssw_product_sync_limit', 500 );

			$args = array(
				'post_type'      => 'product',
				'posts_per_page' => $productlimit,
				'orderby'        => 'ID',
				'order'          => 'ASC',
				'post_status'    => 'any',
			);

			$is_wpml_sitepress_active = self::wpssw_is_wpml_sitepress_active();

			$wpml_filters_removed = 0;
			if ( $is_wpml_sitepress_active ) {
				$wpssw_prd_sync_lang_all     = self::wpssw_option( 'wpssw_prd_sync_lang_all' );
				$wpssw_prd_sync_custom_langs = self::wpssw_option( 'wpssw_prd_sync_custom_langs' );

				if ( 1 === (int) $wpssw_prd_sync_lang_all || '' === (string) $wpssw_prd_sync_lang_all ) {
					$args['suppress_filters'] = 1;
				} else {
					global $wpml_query_filter;
					if ( has_filter( 'posts_join', array( $wpml_query_filter, 'posts_join_filter' ) ) ) {
						self::wpssw_remove_wpml_filters();
						$wpml_filters_removed = 1;
					}
					if ( ! empty( $wpssw_prd_sync_custom_langs ) ) {
						$languages                       = "'" . implode( "','", array_map( 'trim', $wpssw_prd_sync_custom_langs ) ) . "'";
						$args['wpssw_custom_wpml_langs'] = $languages;
					}
				}
			}
			$args['fields'] = 'ids'; // Fetch only ids.

			$wpssw_sheet    = "'" . $wpssw_sheetname . "'!A:Z";
			$wpssw_allentry = self::$instance_api->get_row_list( $wpssw_spreadsheetid, $wpssw_sheet );
			$wpssw_data     = $wpssw_allentry->getValues();
			$wpssw_allentry = null;
			$wpssw_data     = array_map(
				function( $wpssw_element ) {
					if ( isset( $wpssw_element['0'] ) ) {
						return (int) $wpssw_element['0'];
					} else {
						return '';
					}
				},
				$wpssw_data
			);

			$wpssw_data_count = count( $wpssw_data );

			if ( is_array( $wpssw_data ) && ! empty( $wpssw_data ) ) {
				$args['post__not_in'] = array_values( array_filter( array_unique( $wpssw_data ) ) );
				$wpssw_data           = null;
			}

			$products = new WP_Query( $args );

			if ( 1 === (int) $wpml_filters_removed ) {
				self::wpssw_add_wpml_filters();
			}

			if ( empty( $products ) ) {
				return;
			}

			$wpssw_values_array = array();

			$newproduct  = 0;
			$product_ids = $products->posts;
			$products    = null;
			foreach ( $product_ids as $product_id ) {

				if ( $newproduct < $productlimit ) {
					set_time_limit( 999 );
					$product = wc_get_product( $product_id );

					if ( ! empty( $product->get_children() ) && 'grouped' !== (string) $product->get_type() ) {
						$wpssw_values_array[] = self::wpssw_make_product_value_array( 'insert', $product_id );
						foreach ( $product->get_children() as $child ) {
							$wpssw_values_array[] = self::wpssw_make_product_value_array( 'insert', $child, true );
						}
					} else {
						$wpssw_values_array[] = self::wpssw_make_product_value_array( 'insert', $product_id );
					}
					$newproduct++;
				} else {
					break;
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
					parent::wpssw_inherit_row_format_style( 'product', $wpssw_inherit_params );

					$rangetofind = $wpssw_sheetname . '!A' . ( $wpssw_data_count + 1 );

					$wpssw_inputoption = self::wpssw_get_product_inputoption();
					$wpssw_requestbody = self::$instance_api->valuerangeobject( $wpssw_values_array );
					$wpssw_params      = array( 'valueInputOption' => $wpssw_inputoption );
					$param             = self::$instance_api->setparamater( $wpssw_spreadsheetid, $rangetofind, $wpssw_requestbody, $wpssw_params );
					$wpssw_response    = self::$instance_api->appendentry( $param );

				} catch ( Exception $e ) {
					return;
				}
			}
		}
		/**
		 * Check WPML Multilingual CMS by OnTheGoSystems plugin is active or not.
		 */
		public static function wpssw_is_wpml_sitepress_active() {
			if ( class_exists( 'SitePress' ) ) {
				return true;
			}
			return false;
		}
		/**
		 * Posts query join filter for wpml custom language.
		 *
		 * @param string   $join .
		 * @param WP_Query $wp_query .
		 *
		 * @return string
		 */
		public static function wpssw_posts_join_filter( $join, $wp_query ) {
			global $wpdb;
			if ( '' !== (string) $wp_query->get( 'wpssw_custom_wpml_langs' ) ) {
				$join .= "LEFT JOIN {$wpdb->prefix}icl_translations wpml_translations
							ON {$wpdb->posts}.ID = wpml_translations.element_id
								AND wpml_translations.element_type = 'post_product' ";
			}

			return $join;
		}
		/**
		 * Posts query where filter for wpml custom language.
		 *
		 * @param string   $where .
		 * @param WP_Query $wp_query .
		 *
		 * @return string
		 */
		public static function wpssw_posts_where_filter( $where, $wp_query ) {
			global $wpdb;
			$custom_wpml_langs = $wp_query->get( 'wpssw_custom_wpml_langs' );
			if ( '' !== (string) $custom_wpml_langs ) {
				$where .= ' AND wpml_translations.language_code IN (' . $custom_wpml_langs . ') ';
			}
			return $where;
		}
		/**
		 * Add WPML posts filters.
		 */
		public static function wpssw_add_wpml_filters() {
			global $wpml_query_filter;
			add_filter( 'posts_join', array( $wpml_query_filter, 'posts_join_filter' ), 10, 2 );
			add_filter( 'posts_where', array( $wpml_query_filter, 'posts_where_filter' ), 10, 2 );
		}
		/**
		 * Remove WPML posts filters.
		 */
		public static function wpssw_remove_wpml_filters() {
			global $wpml_query_filter;
			remove_filter( 'posts_join', array( $wpml_query_filter, 'posts_join_filter' ), 10, 2 );
			remove_filter( 'posts_where', array( $wpml_query_filter, 'posts_where_filter' ), 10, 2 );
		}
		/**
		 * Get inputoption for product settings.
		 */
		public static function wpssw_get_product_inputoption() {
			$wpssw_inputoption = parent::wpssw_option( 'wpssw_inputoption' );
			if ( ! $wpssw_inputoption ) {
				$wpssw_prdinputoption = parent::wpssw_option( 'wpssw_prd_inputoption' );
				if ( ! $wpssw_prdinputoption ) {
					$wpssw_inputoption = 'USER_ENTERED';
				} else {
					$wpssw_inputoption = $wpssw_prdinputoption;
				}
			}
			return $wpssw_inputoption;
		}
		/**
		 * Update sheet on multiple products update.
		 *
		 * @param array  $wpssw_multiple_product_data updated products data.
		 * @param bool   $settings_check settings checked or not.
		 * @param string $oparation oparation type.
		 */
		public static function wpssw_multiple_product_update( $wpssw_multiple_product_data, $settings_check = false, $oparation = '' ) {
			if ( empty( $wpssw_multiple_product_data ) ) {
				return;
			}

			$wpssw_spreadsheetid = parent::wpssw_option( 'wpssw_product_spreadsheet_id' );

			if ( false === $settings_check ) {
				$wpssw_spreadsheets_list = self::$instance_api->get_spreadsheet_listing();
				if ( ! array_key_exists( $wpssw_spreadsheetid, $wpssw_spreadsheets_list ) ) {
					return;
				}
			}
			$response                  = self::$instance_api->get_sheet_listing( $wpssw_spreadsheetid );
			$wpssw_existingsheetsnames = self::$instance_api->get_sheet_list( $response );
			$wpssw_existingsheetsnames = array_flip( $wpssw_existingsheetsnames );
			$wpssw_sheetname           = 'All Products';

			$sheet_id = array_search( $wpssw_sheetname, $wpssw_existingsheetsnames, true );
			if ( false === $sheet_id ) {
				return;
			}
			$wpssw_sheet    = "'" . $wpssw_sheetname . "'!A:A";
			$wpssw_allentry = self::$instance_api->get_row_list( $wpssw_spreadsheetid, $wpssw_sheet );
			$wpssw_data     = $wpssw_allentry->getValues();
			$wpssw_allentry = null;
			$wpssw_data     = array_map(
				function( $wpssw_element ) {
					if ( isset( $wpssw_element['0'] ) ) {
						return (int) $wpssw_element['0'];
					} else {
						return '';
					}
				},
				$wpssw_data
			);

			$delete_row_indexes = array();
			$deleterequestarray = array();

			foreach ( $wpssw_multiple_product_data as $product_id ) {
				if ( in_array( (int) $product_id, $wpssw_data, true ) ) {
					$wpssw_num  = array_search( (int) $product_id, $wpssw_data, true );
					$item_count = count( array_keys( $wpssw_data, (int) $product_id, true ) );

					$delete_row_indexes[] = array(
						'start_index' => $wpssw_num,
						'end_index'   => $wpssw_num + $item_count,
					);
				}
			}

			if ( ! empty( $delete_row_indexes ) ) {
				$requests                 = array();
				$delete_row_indexes_count = count( $delete_row_indexes );
				array_multisort( array_column( $delete_row_indexes, 'start_index' ), SORT_ASC, $delete_row_indexes );
				for ( $i = 0;$i < $delete_row_indexes_count;$i++ ) {
					if ( 0 === (int) $i ) {
						$startindex = $delete_row_indexes[0]['start_index'];
					} elseif ( $delete_row_indexes[ $i ]['start_index'] - $delete_row_indexes[ $i - 1 ]['end_index'] > 0 ) {
						$requests[] = array(
							'startIndex' => $startindex,
							'endIndex'   => $delete_row_indexes[ $i - 1 ]['end_index'],
						);
						$startindex = $delete_row_indexes[ $i ]['start_index'];
					}
					if ( $delete_row_indexes_count - 1 === (int) $i ) {
						$requests[] = array(
							'startIndex' => $startindex,
							'endIndex'   => $delete_row_indexes[ $i ]['end_index'],
						);
					}
				}
				array_multisort( array_column( $requests, 'startIndex' ), SORT_DESC, $requests );
				foreach ( $requests as $request ) {
					$param                = array();
					$param                = self::$instance_api->prepare_param( $sheet_id, $request['startIndex'], $request['endIndex'] );
					$deleterequestarray[] = self::$instance_api->deleteDimensionrequests( $param, 'ROWS' );
				}
			}

			if ( ! empty( $deleterequestarray ) ) {
				$param                  = array();
				$param['spreadsheetid'] = $wpssw_spreadsheetid;
				$param['requestarray']  = $deleterequestarray;
				$wpssw_response         = self::$instance_api->updatebachrequests( $param );
			}

			if ( 'delete' === (string) $oparation ) {
				return;
			}

			if ( ! empty( $deleterequestarray ) ) {
				$delete_row_indexes = null;
				$deleterequestarray = null;
				$wpssw_response     = null;

				$wpssw_allentry = self::$instance_api->get_row_list( $wpssw_spreadsheetid, $wpssw_sheet );

				$wpssw_data     = $wpssw_allentry->getValues();
				$wpssw_allentry = null;
				$wpssw_data     = array_map(
					function( $wpssw_element ) {
						if ( isset( $wpssw_element['0'] ) ) {
							return (int) $wpssw_element['0'];
						} else {
							return '';
						}
					},
					$wpssw_data
				);

			}
			$wpssw_data_count = count( $wpssw_data );

			$highest_product_id = max( $wpssw_data );

			foreach ( $wpssw_multiple_product_data as $product_id ) {
				$wpssw_append = 0;
				$product      = wc_get_product( $product_id );
				if ( false === $product || null === $product && is_wp_error( $product ) ) {
					continue;
				}

				$wpssw_prdcount = 1;
				if ( ! empty( $product->get_children() ) && 'grouped' !== (string) $product->get_type() ) {
					$wpssw_prdcount += count( $product->get_children() );
				}

				if ( ( $highest_product_id ) && $product_id < $highest_product_id ) {
					foreach ( $wpssw_data as $wpssw_key => $wpssw_value ) {
						if ( ! empty( $wpssw_value ) ) {

							if ( ( (int) $product_id < (int) $wpssw_value ) ) {
								$wpssw_startindex = $wpssw_key + 1;

								$wpssw_append         = 1;
								$insert_row_indexes[] = array(
									'start_index' => $wpssw_key,
									'end_index'   => $wpssw_key + $wpssw_prdcount,
								);

								$update_row_indexes[] = array(
									'start_index'  => $wpssw_startindex,
									'end_index'    => $wpssw_startindex + $wpssw_prdcount,
									'product_id'   => $product_id,

									'values_count' => $wpssw_prdcount,
								);

								break;
							}
						}
					}
				}

				if ( 0 === (int) $wpssw_append ) {

					$wpssw_startindex = $wpssw_data_count + 1;

					$insert_row_indexes[] = array(
						'start_index' => $wpssw_data_count,
						'end_index'   => $wpssw_data_count + $wpssw_prdcount,
					);
					$update_row_indexes[] = array(
						'start_index'  => $wpssw_startindex,
						'end_index'    => $wpssw_startindex + $wpssw_prdcount,
						'product_id'   => $product_id,

						'values_count' => $wpssw_prdcount,
					);

				}
			}
			$wpssw_data = null;

			if ( ! empty( $insert_row_indexes ) ) {
				$requests           = array();
				$insertrequestarray = array();

				$product_inherit_style = self::wpssw_option( 'wpssw_product_inherit_style' );
				array_multisort( array_column( $insert_row_indexes, 'start_index' ), SORT_DESC, $insert_row_indexes );
				foreach ( $insert_row_indexes as $request ) {
					$param = array();
					$param = self::$instance_api->prepare_param( $sheet_id, $request['start_index'], $request['end_index'] );

					if ( 'no' === (string) $product_inherit_style ) {
						$insertrequestarray[] = self::$instance_api->insertdimensionrequests( $param, 'ROWS', false );
					} else {
						$insertrequestarray[] = self::$instance_api->insertdimensionrequests( $param, 'ROWS', true );
					}
				}

				if ( ! empty( $insertrequestarray ) ) {
					$param                  = array();
					$param['spreadsheetid'] = $wpssw_spreadsheetid;
					$param['requestarray']  = $insertrequestarray;
					$wpssw_response         = self::$instance_api->updatebachrequests( $param );

					$insert_row_indexes = null;
					$insertrequestarray = null;
					$wpssw_response     = null;
				}
			}

			$update_row_data = array();

			$update_row_indexes_new = array();
			if ( ! empty( $update_row_indexes ) ) {
				array_multisort( array_column( $update_row_indexes, 'start_index' ), SORT_ASC, $update_row_indexes );

				$requests                 = array();
				$update_row_indexes_count = count( $update_row_indexes );
				$dimension                = 'ROWS';
				for ( $i = 0;$i < $update_row_indexes_count;$i++ ) {
					$new_start               = $update_row_indexes[ $i ]['start_index'];
					$same_startindex_rowkeys = array_keys( parent::wpssw_convert_int( array_column( $update_row_indexes, 'start_index' ) ), (int) $update_row_indexes[ $i ]['start_index'], true );

					if ( count( $same_startindex_rowkeys ) > 1 && $same_startindex_rowkeys[0] < $i ) {
						continue;
					}
					for ( $j = 0;$j < $update_row_indexes_count;$j++ ) {

						if ( $j < $i ) {
							$new_start = $new_start + $update_row_indexes[ $j ]['values_count'];
						}
						if ( ( 0 === (int) $i || ( $i === $update_row_indexes_count - 1 && $j === $i ) ) && count( $same_startindex_rowkeys ) < 2 ) {
							$param = array();
							if ( 0 === (int) $i ) {
								$param['range'] = $wpssw_sheetname . '!A' . $update_row_indexes[ $i ]['start_index'];
							} else {
								$param['range'] = $wpssw_sheetname . '!A' . $new_start;
							}
							$product            = wc_get_product( $update_row_indexes[ $i ]['product_id'] );
							$wpssw_values_array = array();
							if ( ! empty( $product->get_children() ) && 'grouped' !== (string) $product->get_type() ) {
								$wpssw_values_array[] = self::wpssw_make_product_value_array( 'insert', $product->get_id() );
								foreach ( $product->get_children() as $child ) {
									$wpssw_values_array[] = self::wpssw_make_product_value_array( 'insert', $child, true );
								}
							} else {
								$wpssw_values_array[] = self::wpssw_make_product_value_array( 'insert', $product->get_id() );
							}
							$update_row_data[]  = new \Google_Service_Sheets_ValueRange(
								array(
									'range'          => $param['range'],
									'majorDimension' => $dimension,
									'values'         => $wpssw_values_array,
								)
							);
							$wpssw_values_array = null;
							break;
						}
						if ( (int) $update_row_indexes[ $i ]['start_index'] === (int) $update_row_indexes[ $j ]['start_index'] && $update_row_indexes[ $i ]['product_id'] !== $update_row_indexes[ $j ]['product_id'] ) {

							break;
						}

						if ( $update_row_indexes[ $i ]['start_index'] < $update_row_indexes[ $j ]['start_index'] ) {
							$param          = array();
							$param['range'] = $wpssw_sheetname . '!A' . $new_start;

							$product            = wc_get_product( $update_row_indexes[ $i ]['product_id'] );
							$wpssw_values_array = array();
							if ( ! empty( $product->get_children() ) && 'grouped' !== (string) $product->get_type() ) {
								$wpssw_values_array[] = self::wpssw_make_product_value_array( 'insert', $product->get_id() );
								foreach ( $product->get_children() as $child ) {
									$wpssw_values_array[] = self::wpssw_make_product_value_array( 'insert', $child, true );
								}
							} else {
								$wpssw_values_array[] = self::wpssw_make_product_value_array( 'insert', $product->get_id() );
							}
							$update_row_data[]  = new \Google_Service_Sheets_ValueRange(
								array(
									'range'          => $param['range'],
									'majorDimension' => $dimension,
									'values'         => $wpssw_values_array,
								)
							);
							$wpssw_values_array = null;
							break;
						}
						if ( count( $same_startindex_rowkeys ) > 1 ) {
							$same_row_start = $update_row_indexes[ $i ]['start_index'];
							for ( $k = 0;$k < $i;$k++ ) {
								$same_row_start = $same_row_start + $update_row_indexes[ $k ]['values_count'];
							}
							$new_start = $same_row_start;

							$same_rows = array();
							foreach ( $same_startindex_rowkeys as $same_rowkey ) {
								$same_rows[ $update_row_indexes[ $same_rowkey ]['product_id'] ] = $update_row_indexes[ $same_rowkey ];
							}
							ksort( $same_rows );
							$same_startindex_rowkeys = null;

							$same_rows_value = array();
							foreach ( $same_rows as $same_row ) {
								$product            = wc_get_product( $same_row['product_id'] );
								$wpssw_values_array = array();
								if ( ! empty( $product->get_children() ) && 'grouped' !== (string) $product->get_type() ) {
									$wpssw_values_array[] = self::wpssw_make_product_value_array( 'insert', $product->get_id() );
									foreach ( $product->get_children() as $child ) {
										$wpssw_values_array[] = self::wpssw_make_product_value_array( 'insert', $child, true );
									}
								} else {
									$wpssw_values_array[] = self::wpssw_make_product_value_array( 'insert', $product->get_id() );
								}

								$same_rows_value = array_merge( $same_rows_value, $wpssw_values_array );
							}
							$wpssw_values_array = null;

							$param          = array();
							$param['range'] = $wpssw_sheetname . '!A' . $new_start;

							$update_row_data[] = new \Google_Service_Sheets_ValueRange(
								array(
									'range'          => $param['range'],
									'majorDimension' => $dimension,
									'values'         => $same_rows_value,
								)
							);
							$same_rows_value   = null;
							break;
						}
					}
				}
			}

			if ( ! empty( $update_row_data ) ) {

				$wpssw_inputoption = self::wpssw_get_product_inputoption();
				if ( ! $wpssw_inputoption ) {
					$wpssw_inputoption = 'USER_ENTERED';
				}

				$requestobject                  = array();
				$requestobject['spreadsheetid'] = $wpssw_spreadsheetid;

				$requestobject['requestbody'] = new Google_Service_Sheets_BatchUpdateValuesRequest(
					array(
						'valueInputOption' => $wpssw_inputoption,
						'data'             => $update_row_data,
					)
				);

				$wpssw_response = self::$instance_api->multirangevalueupdate( $requestobject );

			}

		}
		/**
		 * Get attachment id from url
		 *
		 * @param string $url attachment url.
		 * @param int    $product_id .
		 */
		public static function wpssw_get_file_attachment_id_from_url( $url, $product_id ) {
			if ( empty( $url ) ) {
				return 0;
			}
			$id         = 0;
			$upload_dir = wp_upload_dir( null, false );
			$base_url   = $upload_dir['baseurl'] . '/';
			// Check first if attachment is inside the WordPress uploads directory, or we're given a filename only.
			if ( false !== strpos( $url, $base_url ) || false === strpos( $url, '://' ) ) {
				// Search for yyyy/mm/slug.extension or slug.extension - remove the base URL.
				$file = str_replace( $base_url, '', $url );
				$args = array(
					'post_type'   => 'attachment',
					'post_status' => 'any',
					'fields'      => 'ids',
					'meta_query' => array(// @codingStandardsIgnoreLine.
						'relation' => 'OR',
						array(
							'key'     => '_wp_attached_file',
							'value'   => '^' . $file,
							'compare' => 'REGEXP',
						),
						array(
							'key'     => '_wp_attached_file',
							'value'   => '/' . $file,
							'compare' => 'LIKE',
						),
						array(
							'key'     => '_wpssw_attachment_source',
							'value'   => '/' . $file,
							'compare' => 'LIKE',
						),
					),
				);
			} else {
				// This is an external URL, so compare to source.
				$args = array(
					'post_type'   => 'attachment',
					'post_status' => 'any',
					'fields'      => 'ids',
					'meta_query' => array(// @codingStandardsIgnoreLine.
						array(
							'value' => $url,
							'key'   => '_wpssw_attachment_source',
						),
					),
				);
			}
			$ids = get_posts($args); // @codingStandardsIgnoreLine.
			if ( $ids ) {
				$id = current( $ids );
			}
			// Upload if attachment does not exists.
			if ( ! $id && stristr( $url, '://' ) ) {
				add_filter( 'https_ssl_verify', '__return_false' );

				// it allows us to use download_url() and wp_handle_sideload() functions.
				require_once ABSPATH . 'wp-admin/includes/file.php';

				// download to temp dir.

				$temp_file = download_url( $url );

				if ( is_wp_error( $temp_file ) ) {
					return;
				}

				// move the temp file into the uploads directory.
				$file     = array(
					'name'     => basename( $url ),
					'type'     => mime_content_type( $temp_file ),
					'tmp_name' => $temp_file,
					'size'     => filesize( $temp_file ),
				);
				$sideload = wp_handle_sideload(
					$file,
					array(
						'test_form' => false, // no needs to check 'action' parameter.
					)
				);

				if ( ! empty( $sideload['error'] ) ) {
					// you may return error message if you want.
					return;
				}

				// it is time to add our uploaded image into WordPress media library.
				$attachment_id = wp_insert_attachment(
					array(
						'guid'           => $sideload['url'],
						'post_mime_type' => $sideload['type'],
						'post_title'     => basename( $sideload['file'] ),
						'post_content'   => '',
						'post_status'    => 'inherit',
					),
					$sideload['file'],
					$product_id
				);

				if ( is_wp_error( $attachment_id ) || ! $attachment_id ) {
					return;
				}

				// update metadata, regenerate image sizes.
				require_once ABSPATH . 'wp-admin/includes/image.php';

				wp_update_attachment_metadata(
					$attachment_id,
					wp_generate_attachment_metadata( $attachment_id, $sideload['file'] )
				);

				return $attachment_id;

			}
			if ( ! $id ) {
				return;
			}
			return $id;
		}

		/**
		 * Output Product category list for product category name as sheets option.
		 *
		 * @param int $flag .
		 */
		public static function wpssw_get_product_categories_as_sheet( $flag = 0 ) {
			$wpssw_product_categories_as_sheet = parent::wpssw_option( 'product_categories_as_sheet' );

			if ( 'yes' !== (string) $wpssw_product_categories_as_sheet && 1 === (int) $flag ) {
				echo '<div class="td-prd-cat-sheets-wpssw" style="display: none;"></div>';
				if ( $flag ) {
					return;
				}
				die;
			}

			$wpssw_productsheets_list = parent::wpssw_get_product_category_saved_sheets();

			$wpssw_category_filter_ids   = array_keys( $wpssw_productsheets_list );
			$wpssw_product_category_list = WPSSW_Order::wpssw_get_product_categories();

			if ( is_array( $wpssw_category_filter_ids ) && ! empty( $wpssw_category_filter_ids ) ) {
				$wpssw_category_filter_ids = parent::wpssw_convert_int( $wpssw_category_filter_ids );
			}

			ob_start(); // Start output buffering.

			if ( ! empty( $wpssw_product_category_list ) ) {
				?>
				<div class="td-prd-cat-sheets-wpssw">
					<div class="td-wpssw-headers">
						<div class='wpssw_headers'>
							<label for="sheet_headers"></label>
							<div class="selectall-btnset">
								<button type="button" class="wpssw-button wpssw-button-primary" id="prdcat_sheets_selectall" 
								<?php if ( ! empty( $wpssw_category_filter_ids ) ) { ?>
									class="wpssw-button wpssw-button-secondary wpssw-prdcat-sheets-select"
								<?php } else { ?>
									class="wpssw-button wpssw-button-secondary"
								<?php } ?>>
								<?php esc_html_e( 'Select All', 'wpssw' ); ?></button>
								<button class="wpssw-button wpssw-button-secondary" type="button" id="prdcat_sheets_selectnone" 
								<?php if ( ! empty( $wpssw_category_filter_ids ) ) { ?>
									class="wpssw-button wpssw-button-secondary wpssw-prdcat-sheets-select"
								<?php } else { ?>
									class="wpssw-button wpssw-button-secondary"
								<?php } ?>>
								<?php esc_html_e( 'Select None', 'wpssw' ); ?></button>
							</div>
							<ul id="productcategory-sheets-sortable" class="sheetHeaders-box sortable-headers-list">
								<?php
								foreach ( $wpssw_product_category_list as $wpssw_key => $wpssw_val ) {
									$wpssw_checked = '';
									if ( is_array( $wpssw_category_filter_ids ) && in_array( (int) $wpssw_key, $wpssw_category_filter_ids, true ) ) {
										$wpssw_checked = 'checked';
									}
									?>
									<li class="default-order-sheet ui-state-default">
										<label for="<?php echo esc_html( 'prdcat_' . $wpssw_val ); ?>">
											<span class="orderSheet-left">
												<span class="wootextfield"><?php echo esc_html( $wpssw_val ); ?></span>
											</span>
											<span class="orderSheet-right">
												<input type="checkbox" name="prdcat_as_sheets[]" value="<?php echo esc_attr( $wpssw_key ); ?>" id="<?php echo esc_attr( 'prdcat_' . $wpssw_val ); ?>" class="prdcat_sheets_chk" <?php echo esc_html( $wpssw_checked ); ?>>
												<span class="checkbox-switch-new"></span>
											</span>
										</label>
									</li>
								<?php } ?>
							</ul>
						</div>
					</div>
				</div>
				<?php
			} else {
				?>
				<div class="td-prd-cat-sheets-wpssw">
					<div class="td-wpssw-headers">
						<div class='wpssw_headers'>
							<ul id="productcategory-sheets-sortable" class="sheetHeaders-box">
								<li class="default-order-sheet ui-state-default">
									<label for="not-found">
										<span class="orderSheet-left">
											<span class="wootextfield"><?php echo esc_html( 'No product categories found.' ); ?></span>
										</span>
									</label>
								</li>
							</ul>
						</div>
					</div>
				</div>
				<?php
			}

			ob_end_flush(); // End output buffering and flush output.
			if ( $flag ) {
				return;
			}
			die;
		}
	}
	new WPSSW_Product();
endif;
