<?php
/**
 * Main WPSyncSheets_For_WooCommerce namespace.
 *
 * @since 1.0.0
 * @package wpsyncsheets-for-woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }
if ( ! class_exists( 'WPSSW_Order_Import' ) ) :
	/**
	 * Class WPSSW_Order_Import.
	 */
	class WPSSW_Order_Import extends WPSSW_Setting {
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
			$wpssw_include->wpssw_include_order_import_ajax_hook();
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
		 * Get products count for syncronization
		 */
		public static function wpssw_get_order_import_count() {
			$wpssw_order_spreadsheet_setting = WPSSW_Order::wpssw_check_order_spreadsheet_setting();
			if ( 'yes' !== (string) $wpssw_order_spreadsheet_setting ) {
				echo 'spreadsheetnotexist';
				die;
			}
			if ( ! self::$instance_api->checkcredenatials() ) {
				return;
			}

			$wpssw_spreadsheetid = parent::wpssw_option( 'wpssw_woocommerce_spreadsheet' );
			$spreadsheets_list   = self::$instance_api->get_spreadsheet_listing();
			if ( ! empty( $wpssw_spreadsheetid ) && ! array_key_exists( $wpssw_spreadsheetid, $spreadsheets_list ) ) {
				echo 'spreadsheetnotexist';
				die;
			}
			$wpssw_order_status_array = self::$wpssw_default_status_slug;
			/* Custom Order Status*/
			$wpssw_status_array             = wc_get_order_statuses();
			$wpssw_status_array['wc-trash'] = 'Trash';
			$wpssw_existingsheetsnames      = array();
			$wpssw_response                 = self::$instance_api->get_sheet_listing( $wpssw_spreadsheetid );
			$wpssw_existingsheetsnames      = self::$instance_api->get_sheet_list( $wpssw_response );
			$wpssw_existingsheetsnames      = array_flip( $wpssw_existingsheetsnames );
			$wpssw_status_array['wc-all']   = 'All';
			$wpssw_getactivesheets          = array();
			$wpssw_activesheets             = array();
			$wpssw_sheets                   = array_filter( (array) parent::wpssw_option( 'wpssw_sheets' ) );
			foreach ( $wpssw_status_array as $wpssw_key => $wpssw_val ) {
				$wpssw_status      = substr( $wpssw_key, strpos( $wpssw_key, '-' ) + 1 );
				$wpssw_orderstatus = str_replace( '-', ' ', $wpssw_status );
				$wpssw_orderstatus = ucwords( $wpssw_orderstatus ) . ' Orders';
				if ( array_key_exists( $wpssw_orderstatus, $wpssw_sheets ) && in_array( $wpssw_sheets[ $wpssw_orderstatus ], $wpssw_existingsheetsnames, true ) ) {
					if ( 'wc-trash' === (string) $wpssw_key ) {
						$wpssw_activesheets['trash'] = $wpssw_sheets[ $wpssw_orderstatus ];
					} elseif ( 'wc-all' === (string) $wpssw_key ) {
						$wpssw_activesheets['all_order'] = $wpssw_sheets[ $wpssw_orderstatus ];
					} else {
						$wpssw_activesheets[ $wpssw_key ] = $wpssw_sheets[ $wpssw_orderstatus ];
					}
					$wpssw_getactivesheets[] = "'" . $wpssw_sheets[ $wpssw_orderstatus ] . "'!A:A";

				}
			}

			/*Get First Column Value from all sheets*/
			try {
				$param                  = array();
				$param['spreadsheetid'] = $wpssw_spreadsheetid;
				$param['ranges']        = array( 'ranges' => $wpssw_activesheets );
				$wpssw_response         = self::$instance_api->getbatchvalues( $param );
			} catch ( Exception $e ) {
				echo esc_html( 'Message: ' . $e->getMessage() );
			}
			$wpssw_existingorders = array();
			$response             = array();

			foreach ( $wpssw_response->getValueRanges() as $wpssw_order ) {
				$wpssw_rangetitle = explode( "'!A", $wpssw_order->range );
				$wpssw_sheettitle = str_replace( "'", '', $wpssw_rangetitle[0] );

				$wpssw_data = $wpssw_order->values;

				$wpssw_existingorders[ $wpssw_sheettitle ] = $wpssw_data;
				$wpssw_headers                             = array_shift( $wpssw_data );
				$wpssw_update_orders                       = array();
				$wpssw_delete_orders                       = array();
				$wpssw_insert_orders                       = array();
				if ( in_array( 'Insert', $wpssw_headers, true ) ) {
					$wpssw_insert_key    = array_search( 'Insert', $wpssw_headers, true );
					$wpssw_insert_orders = array_values( array_filter( array_column( $wpssw_data, $wpssw_insert_key ) ) );

					$wpssw_insert_orders = array_keys( parent::wpssw_convert_int( $wpssw_insert_orders ), 1, true );
				}

				if ( in_array( 'Update', $wpssw_headers, true ) ) {
					$wpssw_update_key    = array_search( 'Update', $wpssw_headers, true );
					$wpssw_update_orders = array_values( array_filter( array_column( $wpssw_data, $wpssw_update_key ) ) );
				}
				if ( in_array( 'Delete', $wpssw_headers, true ) ) {
					$wpssw_delete_key    = array_search( 'Delete', $wpssw_headers, true );
					$wpssw_delete_orders = array_values( array_filter( array_column( $wpssw_data, $wpssw_delete_key ) ) );
				}
				$wpssw_result_array = array();
				if ( count( $wpssw_insert_orders ) > 0 ) {
					$wpssw_result_array['insertorders'] = count( $wpssw_insert_orders );
				}
				if ( count( $wpssw_update_orders ) > 0 ) {
					$wpssw_result_array['updateorders'] = count( $wpssw_update_orders );
				}
				if ( count( $wpssw_delete_orders ) > 0 ) {
					$wpssw_result_array['deleteorders'] = count( $wpssw_delete_orders );
				}
				if ( count( $wpssw_insert_orders ) > 0 || count( $wpssw_update_orders ) > 0 || count( $wpssw_delete_orders ) > 0 ) {
					$wpssw_result_array['sheet_name']        = $wpssw_sheettitle;
					$wpssw_result_array['totalimportorders'] = array_sum( array_values( $wpssw_result_array ) );
					$response[]                              = $wpssw_result_array;
				}
			}
			echo wp_json_encode( $response );
			die;
		}
		/**
		 * Import order
		 */
		public static function wpssw_order_import() {

			if ( ! isset( $_POST['wpssw_general_settings'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wpssw_general_settings'] ) ), 'save_general_settings' ) ) {
				echo 'error';
				die();
			}
			if ( ! self::$instance_api->checkcredenatials() ) {
				return;
			}

			$wpssw_spreadsheetid = parent::wpssw_option( 'wpssw_woocommerce_spreadsheet' );

			$wpssw_sheetname = isset( $_POST['sheetname'] ) ? sanitize_text_field( wp_unslash( $_POST['sheetname'] ) ) : '';

			$wpssw_response            = self::$instance_api->get_sheet_listing( $wpssw_spreadsheetid );
			$wpssw_existingsheetsnames = self::$instance_api->get_sheet_list( $wpssw_response );

			$wpssw_sheetid = '';
			if ( false !== array_key_exists( $wpssw_sheetname, $wpssw_existingsheetsnames ) ) {
				$wpssw_sheetid = $wpssw_existingsheetsnames[ $wpssw_sheetname ];
			}

			$wpssw_response            = null;
			$wpssw_existingsheetsnames = null;

			$wpssw_checked  = '';
			$wpssw_sheet    = "'" . $wpssw_sheetname . "'!A:A";
			$wpssw_allentry = self::$instance_api->get_row_list( $wpssw_spreadsheetid, $wpssw_sheetname );
			$wpssw_data     = $wpssw_allentry->getValues();
			$wpssw_allentry = null;
			$wpssw_headers  = array_shift( $wpssw_data );

			$wpssw_order_ids = array_map(
				function( $wpssw_element ) {
					if ( isset( $wpssw_element[0] ) ) {
						return $wpssw_element[0];
					} else {
						return '';
					}
				},
				$wpssw_data
			);

			$wpssw_update_orders = array();
			$wpssw_delete_orders = array();
			$wpssw_insert_orders = array();

			$hpos_order_enabled = parent::wpssw_check_hpos_order_setting_enabled();

			if ( in_array( 'Update', $wpssw_headers, true ) ) {
				$wpssw_update_key    = array_search( 'Update', $wpssw_headers, true );
				$wpssw_update_orders = array_map(
					function( $wpssw_element ) use ( $wpssw_update_key ) {
						if ( isset( $wpssw_element[ $wpssw_update_key ] ) ) {
							return $wpssw_element[ $wpssw_update_key ];
						} else {
							return '';
						}
					},
					$wpssw_data
				);

				$wpssw_update_orders = array_filter( $wpssw_update_orders );
			}
			if ( in_array( 'Delete', $wpssw_headers, true ) ) {
				$wpssw_delete_key    = array_search( 'Delete', $wpssw_headers, true );
				$wpssw_delete_orders = array_map(
					function( $wpssw_element ) use ( $wpssw_delete_key ) {
						if ( isset( $wpssw_element[ $wpssw_delete_key ] ) ) {
							return $wpssw_element[ $wpssw_delete_key ];
						} else {
							return '';
						}
					},
					$wpssw_data
				);

				$wpssw_delete_orders = array_filter( $wpssw_delete_orders );
			}

			$wpssw_insert_orders_items = array();
			if ( in_array( 'Insert', $wpssw_headers, true ) ) {
				$wpssw_insert_key    = array_search( 'Insert', $wpssw_headers, true );
				$wpssw_insert_orders = array_map(
					function( $wpssw_element ) use ( $wpssw_insert_key ) {
						if ( isset( $wpssw_element[ $wpssw_insert_key ] ) ) {
							return $wpssw_element[ $wpssw_insert_key ];
						} else {
							return '';
						}
					},
					$wpssw_data
				);

				$wpssw_insert_orders = array_filter( $wpssw_insert_orders );

				$order_items = $wpssw_insert_orders;
				krsort( $order_items );
				$order_items_key = array();
				foreach ( $order_items as $wpssw_orderitem_index => $wpssw_val ) {
					if ( 2 === (int) $wpssw_val ) {
						$order_items_key[] = $wpssw_orderitem_index;
					}
					if ( 1 === (int) $wpssw_val ) {
						$wpssw_insert_orders_items[ $wpssw_orderitem_index ] = $order_items_key;
						$order_items_key                                     = array();
					}
				}
			}
			$wpssw_insert_orders_items = array_filter( $wpssw_insert_orders_items );
			ksort( $wpssw_insert_orders_items );

			$message = '';

			$imported_ids       = array();
			$delete_row_indexes = array();
			$deleterequestarray = array();
			$newimportorder     = 0;

			$wpssw_importorderlimit = isset( $_POST['importorderlimit'] ) ? sanitize_text_field( wp_unslash( $_POST['importorderlimit'] ) ) : '';

			if ( ! empty( $wpssw_insert_orders ) ) {
				foreach ( $wpssw_insert_orders as $wpssw_order_index => $wpssw_val ) {
					if ( 1 !== (int) $wpssw_val ) {
						continue;
					}
					if ( $newimportorder >= $wpssw_importorderlimit ) {
						break;
					}
					if ( ! isset( $wpssw_data[ $wpssw_order_index ] ) ) {
						continue;
					}

					set_time_limit( 999 );
					$wpssw_order_id = isset( $wpssw_data[ $wpssw_order_index ][0] ) ? $wpssw_data[ $wpssw_order_index ][0] : '';
					if ( ! empty( $wpssw_order_id ) ) {

						if ( empty( $message ) ) {
							$message = 'orderIdexist';
						}
								continue;
					}
					$wpssw_order_items   = array();
					$wpssw_order_items[] = $wpssw_data[ $wpssw_order_index ];
					if ( array_key_exists( $wpssw_order_index, $wpssw_insert_orders_items ) ) {
						foreach ( $wpssw_insert_orders_items[ $wpssw_order_index ] as $items ) {
							$wpssw_order_items[] = $wpssw_data[ $items ];
						}
					}
					$total_products                        = count( $wpssw_order_items );
					$wpssw_order_ids[ $wpssw_order_index ] = self::wpssw_insert_order( $wpssw_data[ $wpssw_order_index ], $wpssw_order_items, $total_products );
					if ( $wpssw_sheetid ) {
						$delete_row_indexes[] = array(
							'start_index' => $wpssw_order_index + 1,
							'end_index'   => $wpssw_order_index + 1 + $total_products,
						);
					}
					$newimportorder++;

					$ord_id = $wpssw_order_ids[ $wpssw_order_index ];
					if ( $ord_id ) {
						self::wpssw_update_order( $ord_id, $wpssw_data[ $wpssw_order_index ] );
						$order                   = wc_get_order( $ord_id );
						$imported_ids[ $ord_id ] = array(
							'old_staus'  => 'new',
							'new_status' => $order->get_status(),
						);
						$order                   = null;
					}
				}

				$wpssw_insert_orders       = null;
				$wpssw_insert_orders_items = null;

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
					$delete_row_indexes = null;
					array_multisort( array_column( $requests, 'startIndex' ), SORT_DESC, $requests );
					foreach ( $requests as $request ) {
						$param                = array();
						$param                = self::$instance_api->prepare_param( $wpssw_sheetid, $request['startIndex'], $request['endIndex'] );
						$deleterequestarray[] = self::$instance_api->deleteDimensionrequests( $param, 'ROWS' );
					}
					$requests = null;
				}
				if ( ! empty( $deleterequestarray ) ) {
					self::$instance_api->updatebachrequests(
						array(
							'spreadsheetid' => $wpssw_spreadsheetid,
							'requestarray'  => $deleterequestarray,
						)
					);
					$deleterequestarray = null;
				}
			}
			if ( ! empty( $wpssw_update_orders ) ) {
				foreach ( $wpssw_update_orders as $wpssw_order_index => $wpssw_val ) {
					if ( 1 !== (int) $wpssw_val ) {
						continue;
					}
					if ( $newimportorder >= $wpssw_importorderlimit ) {
						break;
					}
					if ( ! isset( $wpssw_data[ $wpssw_order_index ] ) ) {
						continue;
					}
					if ( isset( $wpssw_order_ids[ $wpssw_order_index ] ) && ! empty( $wpssw_order_ids[ $wpssw_order_index ] ) ) {
						$ord_id = $wpssw_order_ids[ $wpssw_order_index ];
						$order  = wc_get_order( $ord_id );
						if ( ! empty( $order ) ) {
							$status = $order->get_status();
							if ( ! empty( $status ) && 'trash' === (string) $status ) {
								if ( empty( $message ) ) {
									$message = 'orderintrash';
								}
								continue;
							} else {
								self::wpssw_update_order( $ord_id, $wpssw_data[ $wpssw_order_index ] );
								$order                   = wc_get_order( $ord_id );
								$imported_ids[ $ord_id ] = array(
									'old_staus'  => $status,
									'new_status' => $order->get_status(),
								);
								$newimportorder++;
							}
						} else {

							if ( empty( $message ) ) {
								$message = 'ordernotexists';
							}
								continue;
						}
					} else {

						if ( empty( $message ) ) {
							$message = 'addorderId';
						}
								continue;
					}
				}
				$wpssw_update_orders = null;
			}
			if ( ! empty( $wpssw_delete_orders ) ) {
				foreach ( $wpssw_delete_orders as $wpssw_order_index => $wpssw_val ) {
					if ( 1 !== (int) $wpssw_val ) {
						continue;
					}
					if ( $newimportorder >= $wpssw_importorderlimit ) {
						break;
					}
					if ( ! isset( $wpssw_data[ $wpssw_order_index ] ) ) {
						continue;
					}
					if ( isset( $wpssw_order_ids[ $wpssw_order_index ] ) && ! empty( $wpssw_order_ids[ $wpssw_order_index ] ) ) {

						$ord_id      = $wpssw_order_ids[ $wpssw_order_index ];
						$wpssw_order = wc_get_order( $ord_id );
						if ( ! empty( $wpssw_order ) ) {
							$wpssw_old_status = $wpssw_order->get_status();
							if ( ! empty( $wpssw_old_status ) && 'trash' === (string) $wpssw_old_status ) {

								if ( empty( $message ) ) {
									$message = 'orderintrash';
								}
								continue;

							} else {

								$imported_ids[ $ord_id ] = array(
									'old_staus'  => $wpssw_old_status,
									'new_status' => 'trash',
								);
								$newimportorder++;
								if ( $hpos_order_enabled ) {
									remove_action( 'woocommerce_trash_order', 'WPSSW_Order::wpssw_wcgs_trash_order' );
									$wpssw_status_array = wc_get_order_statuses();
									$wpssw_status_array = array_map( 'strtolower', $wpssw_status_array );
									if ( in_array( strtolower( $wpssw_old_status ), array( 'pending', 'pending payment' ), true ) ) {
										$wpssw_order->update_meta_data( '_wp_trash_meta_status', 'wc-pending' );
									} elseif ( in_array( strtolower( $wpssw_old_status ), $wpssw_status_array, true ) ) {
										$wpssw_old_status_key = array_search( strtolower( $wpssw_old_status ), $wpssw_status_array, true );
										$wpssw_order->update_meta_data( '_wp_trash_meta_status', $wpssw_old_status_key );
									}
									$wpssw_order->update_status( 'trash' );
									$wpssw_order->save();
								} else {
									remove_action( 'wp_trash_post', 'WPSSW_Setting::wpssw_wcgs_trash' );
									wp_trash_post( $ord_id );
								}
							}
						} else {

							if ( empty( $message ) ) {
								$message = 'ordernotexists';
							}
								continue;
						}
					} else {

						if ( empty( $message ) ) {
							$message = 'addorderId';
						}
								continue;
					}
				}
				$wpssw_delete_orders = null;
			}

			$wpssw_order_ids = null;
			$wpssw_data      = null;

			if ( ! empty( $imported_ids ) ) {
				WPSSW_Order::wpssw_multiple_order_update( $imported_ids );
			}
			$imported_ids = null;
			if ( ! empty( $message ) ) {
				echo esc_html( $message );
				die;
			}
			echo esc_html__( 'successful', 'wpssw' );
			die;
		}
		/**
		 * Update imported order
		 *
		 * @param int    $wpssw_orderid order id.
		 * @param array  $wpssw_data order data array.
		 * @param string $wpssw_opration opration to perform on order.
		 */
		public static function wpssw_update_order( $wpssw_orderid, $wpssw_data, $wpssw_opration = 'update' ) {

			if ( ! self::$instance_api->checkcredenatials() ) {
				return;
			}
			if ( (int) $wpssw_orderid < 1 ) {
				return;
			}
			$wpssw_woo_selections = stripslashes_deep( parent::wpssw_option( 'wpssw_sheet_headers_list' ) );
			if ( ! $wpssw_woo_selections ) {
				return;
			}
			array_unshift( $wpssw_woo_selections, 'Order Id' );
			$wpssw_include = new WPSSW_Include_Action();
			$wpssw_include->wpssw_include_order_compatibility_files();
			$wpssw_wooorder_headers = apply_filters( 'wpsyncsheets_order_headers', array() );
			$wpssw_default_header   = $wpssw_wooorder_headers['WPSSW_Default']['Essential'];

			$wpssw_exclude_headers = array( 'Order Number', 'SKU', 'Product Quantity', 'Prices Include Tax', 'Order Currency', 'Tax Total', 'Order Total', 'Order Discount Total', 'Order Discount Tax', 'Transaction ID', 'Shipping Method Title', 'Shipping Total', 'Coupons Codes', 'Client Role', 'Order URL', 'Created Date', 'Status Updated Date', 'Order Completion Date', 'Order Paid Date', 'Order Notes', 'Order Id with Link', 'Order Fees', 'Order Item ID' );// 'Product Name'

			$wpssw_updated_array      = array();
			$wpssw_crud_operation     = array( 'Update', 'Delete' );
			$wpssw_old_order_status   = '';
			$wpssw_order_status       = '';
			$wpssw_order              = wc_get_order( $wpssw_orderid );
			$shipping_country         = '';
			$billing_country          = '';
			$wpssw_attributes_headers = array();

			if ( isset( $wpssw_wooorder_headers['WPSSW_Product_Attribute'] ) && ! empty( $wpssw_wooorder_headers['WPSSW_Product_Attribute'] ) ) {
				$wpssw_attributes_headers = $wpssw_wooorder_headers['WPSSW_Product_Attribute'];
			}

			global $woocommerce;
			$countries_obj         = new WC_Countries();
			$countries             = $countries_obj->get_countries();
			$billing_country_key   = array_search( 'Billing Country', $wpssw_woo_selections, true );
			$billing_country_blank = 0;
			if ( false !== $billing_country_key ) {
				$billing_country = isset( $wpssw_data[ $billing_country_key ] ) ? ucfirst( strtolower( trim( $wpssw_data[ $billing_country_key ] ) ) ) : '';
				if ( ! empty( $billing_country ) && in_array( $billing_country, $countries, true ) ) {
					$billing_country                    = array_search( $billing_country, $countries, true );
					$wpssw_data[ $billing_country_key ] = $billing_country;
				} elseif ( empty( $billing_country ) ) {
					$billing_country_blank = 1;
				} else {
					$billing_country = '';
				}
			} else {
				$billing_country = $wpssw_order->get_billing_country();
			}
			$shipping_country_key   = array_search( 'Shipping Country', $wpssw_woo_selections, true );
			$shipping_country_blank = 0;
			if ( false !== $shipping_country_key ) {
				$shipping_country = isset( $wpssw_data[ $shipping_country_key ] ) ? ucfirst( strtolower( trim( $wpssw_data[ $shipping_country_key ] ) ) ) : '';
				if ( ! empty( $shipping_country ) && in_array( $shipping_country, $countries, true ) ) {
					$shipping_country                    = array_search( $shipping_country, $countries, true );
					$wpssw_data[ $shipping_country_key ] = $shipping_country;
				} elseif ( empty( $shipping_country ) ) {
					$shipping_country_blank = 1;
				} else {
					$shipping_country = '';
				}
			} else {
				$shipping_country = $wpssw_order->get_shipping_country();
			}
			$meta_key_to_setter = array(
				'_billing_first_name'  => 'set_billing_first_name',
				'_billing_last_name'   => 'set_billing_last_name',
				'_billing_company'     => 'set_billing_company',
				'_billing_address_1'   => 'set_billing_address_1',
				'_billing_address_2'   => 'set_billing_address_2',
				'_billing_city'        => 'set_billing_city',
				'_billing_state'       => 'set_billing_state',
				'_billing_postcode'    => 'set_billing_postcode',
				'_billing_country'     => 'set_billing_country',
				'_billing_email'       => 'set_billing_email',
				'_billing_phone'       => 'set_billing_phone',
				'_shipping_first_name' => 'set_shipping_first_name',
				'_shipping_last_name'  => 'set_shipping_last_name',
				'_shipping_company'    => 'set_shipping_company',
				'_shipping_address_1'  => 'set_shipping_address_1',
				'_shipping_address_2'  => 'set_shipping_address_2',
				'_shipping_city'       => 'set_shipping_city',
				'_shipping_state'      => 'set_shipping_state',
				'_shipping_postcode'   => 'set_shipping_postcode',
				'_shipping_country'    => 'set_shipping_country',
			);
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
					if ( ( '_billing_country' === (string) $wpssw_meta_key && empty( $billing_country ) && 0 === (int) $billing_country_blank ) || ( '_shipping_country' === (string) $wpssw_meta_key && empty( $shipping_country ) && 0 === (int) $shipping_country_blank ) ) {
							continue;
					}
					if ( '_billing_state' === (string) $wpssw_meta_key ) {
						if ( ! empty( $wpssw_data[ $wpssw_key ] ) ) {
							$state_name = ucfirst( strtolower( trim( $wpssw_data[ $wpssw_key ] ) ) );
							if ( ! empty( $billing_country ) ) {
								$states = $countries_obj->get_states( $billing_country );

								if ( ! empty( $states ) && in_array( $state_name, $states, true ) ) {
									$wpssw_data[ $wpssw_key ] = array_search( $state_name, $states, true );
								}
							}
						} else {
							$wpssw_data[ $wpssw_key ] = '';
						}
						$wpssw_order->set_billing_state( $wpssw_data[ $wpssw_key ] );
						continue;
					}
					if ( '_shipping_state' === (string) $wpssw_meta_key ) {
						if ( ! empty( $wpssw_data[ $wpssw_key ] ) ) {
							$state_name = ucfirst( strtolower( trim( $wpssw_data[ $wpssw_key ] ) ) );

							if ( ! empty( $shipping_country ) ) {
								$states = $countries_obj->get_states( $shipping_country );
								if ( ! empty( $states ) && in_array( $state_name, $states, true ) ) {
									$wpssw_data[ $wpssw_key ] = array_search( $state_name, $states, true );
								}
							}
						} else {
							$wpssw_data[ $wpssw_key ] = '';
						}
						$wpssw_order->set_shipping_state( $wpssw_data[ $wpssw_key ] );
						continue;
					}
					if ( '_status' === (string) $wpssw_meta_key ) {
						$wpssw_old_order_status = strtolower( $wpssw_order->get_status() );
						$wpssw_status_array     = wc_get_order_statuses();
						$wpssw_status           = array();
						foreach ( $wpssw_status_array as $key => $val ) {
							$wpssw_status[] = strtolower( trim( substr( $key, strpos( $key, '-' ) + 1 ) ) );
						}
						if ( ! empty( $wpssw_data[ $wpssw_key ] ) ) {
							$wpssw_order_status = strtolower( trim( $wpssw_data[ $wpssw_key ] ) );

							if ( in_array( $wpssw_order_status, $wpssw_status, true ) ) {
								$wpssw_order->set_status( $wpssw_order_status );
								remove_action( 'woocommerce_order_status_changed', 'WPSSW_Order::wpssw_woo_order_status_change_custom', 40, 3 );
							}
							$wpssw_order_status = strtolower( $wpssw_order->get_status() );
						} else {
							$wpssw_order_status = $wpssw_old_order_status;
						}
						continue;
					}
					if ( '_customer_note' === (string) $wpssw_meta_key ) {
						if ( ! empty( $wpssw_data[ $wpssw_key ] ) ) {
							$wpssw_customer_note = trim( $wpssw_data[ $wpssw_key ] );
						} else {
							$wpssw_customer_note = '';
						}
						$wpssw_order->set_customer_note( $wpssw_customer_note );
						continue;
					}					
					if ( 'product_name' === (string) $wpssw_meta_key ) {
						// Access the line items.
						$wpssw_item_key = array_search( 'Order Item ID', $wpssw_woo_selections, true );
						$line_item_id   = isset( $wpssw_data[ $wpssw_item_key ] ) ? $wpssw_data[ $wpssw_item_key ] : '';

						if ( $wpssw_item_key > 0 && '' !== $line_item_id ) {
							$line_items = $wpssw_order->get_items();

							// Iterate through line items.
							foreach ( $line_items as $item_id => $item ) {
								if ( (int) $item->get_id() === (int) $line_item_id ) {
									// Update the line item name.
									$item->set_name( $wpssw_data[ $wpssw_key ] );
									// Save changes.
									$item->save();
									break; // Exit the loop if the item is found.
								}
							}
						}
						continue;
					}
					if ( 'Customer ID' === (string) $wpssw_header ) {
						$customer_id   = isset( $wpssw_data[ $wpssw_key ] ) ? $wpssw_data[ $wpssw_key ] : '';
						$current_customer_id = $wpssw_order->get_customer_id();

					    // Check if the current customer ID is different from the new customer ID.
					    if ( (int) $current_customer_id !== (int) $customer_id ) {
						    if($customer_id){
								$user = get_user_by('id', $customer_id);
							}else{
								$user = wp_get_current_user();
							}
							if(!$user || is_wp_error($user)){
								$user = wp_get_current_user();
							}
							$wpssw_order->set_customer_id( $user->ID );
					    }						
						continue;
					}
					if( 'Payment Method' === (string) $wpssw_header ){
					    $desired_payment_title   = isset( $wpssw_data[ $wpssw_key ] ) ? trim($wpssw_data[ $wpssw_key ]) : '';
					    
					    if($desired_payment_title){

					        $available_gateways = WC()->payment_gateways->payment_gateways();

					        // Initialize a variable to store the payment method slug.
					        $payment_method_slug = '';

					        // Loop through the available payment gateways to find the one matching the desired title.
					        foreach ($available_gateways as $gateway) {
					            if ($gateway->get_title() === $desired_payment_title) {
					                $payment_method_slug = $gateway->id;
					                break;
					            }
					        }

					        $available_gateways = null;

					        if ($payment_method_slug) {
					            // Get the current payment method slug.
					            $current_payment_method_slug = $wpssw_order->get_payment_method();

					           
					            // Check if the current payment method is different from the desired one.
					            if ($current_payment_method_slug !== $payment_method_slug) {

					                // Set the payment method using the slug.
					                $wpssw_order->set_payment_method($payment_method_slug);

					                // Set the payment method title to match the desired title.
					                $wpssw_order->set_payment_method_title($desired_payment_title);
					            }
					        }
					    }
					    continue;
					}
					if ( array_key_exists( $wpssw_meta_key, $meta_key_to_setter ) ) {
						$setter_method = $meta_key_to_setter[ $wpssw_meta_key ];
						if ( method_exists( $wpssw_order, $setter_method ) ) {
							$wpssw_order->$setter_method( $wpssw_data[ $wpssw_key ] );
						}
						continue;
					}
					self::wpssw_update_post_meta( $wpssw_orderid, $wpssw_meta_key, $wpssw_data[ $wpssw_key ] );
				}
				if ( in_array( $wpssw_header, $wpssw_attributes_headers, true ) ) {
					$wpssw_meta_key           = array_search( $wpssw_header, $wpssw_default_header, true );
					$wpssw_data[ $wpssw_key ] = isset( $wpssw_data[ $wpssw_key ] ) ? $wpssw_data[ $wpssw_key ] : '';
					// Access the line items.
					$wpssw_item_key = array_search( 'Order Item ID', $wpssw_woo_selections, true );
					$line_item_id   = isset( $wpssw_data[ $wpssw_item_key ] ) ? $wpssw_data[ $wpssw_item_key ] : '';

					if ( $wpssw_item_key > 0 && '' !== $line_item_id ) {
						$line_items = $wpssw_order->get_items();
						if ( isset( $line_items[ $line_item_id ] ) ) {
							$target_line_item = $line_items[ $line_item_id ];

							// Modify the attribute value of the line item.
							$attribute_key = sanitize_title( $wpssw_header );

							$attribute_key       = 'pa_' . $attribute_key;
							$new_attribute_value = $wpssw_data[ $wpssw_key ];

							$target_line_item->update_meta_data( $attribute_key, $new_attribute_value );

							// Save the changes to the order.
							$target_line_item->save();
						}
					}
				}
			}
			$wpssw_order->save();
		}
		/**
		 * Update post meta
		 *
		 * @param int          $wpssw_orderid post id to update post meta.
		 * @param string       $wpssw_meta_key meta key to update.
		 * @param string|array $wpssw_data value for meta key.
		 */
		public static function wpssw_update_post_meta( $wpssw_orderid, $wpssw_meta_key, $wpssw_data ) {

			if ( ! $wpssw_orderid ) {
				return;
			}
			$hpos_order_enabled = parent::wpssw_check_hpos_order_setting_enabled();
			if ( $hpos_order_enabled ) {
				$wpssw_order = wc_get_order( $wpssw_orderid );
				$wpssw_order->update_meta_data( $wpssw_meta_key, $wpssw_data );
				$wpssw_order->save();
			} else {
				update_post_meta( $wpssw_orderid, $wpssw_meta_key, $wpssw_data );
			}
		}

		/**
		 * Insert new order
		 *
		 * @param array $wpssw_order_data order data to insert new order.
		 * @param array $wpssw_order_items items value to insert into order.
		 * @param int   $total_items total items count.
		 * @return int $wpssw_orderid newly created order id.
		 */
		public static function wpssw_insert_order( $wpssw_order_data, $wpssw_order_items = array(), $total_items = 1 ) {
			if ( empty( $wpssw_order_data ) ) {
				return;
			}

			$wpssw_include_headers = array( 'Coupons Codes', 'Order Currency', 'Prices Include Tax', 'Customer ID', 'Order Total', 'Payment Method' );

			$wpssw_woo_selections = stripslashes_deep( parent::wpssw_option( 'wpssw_sheet_headers_list' ) );
			if ( ! $wpssw_woo_selections ) {
				return;
			}
			array_unshift( $wpssw_woo_selections, 'Order Id' );

			$product_ids         = array();
			$wpssw_product_data  = array();
			$product_items_array = array();
			if ( WPSSW_Order::wpssw_is_productwise() ) {
				$wpssw_product_id_key  = array_search( 'Product ID', $wpssw_woo_selections, true );
				$wpssw_product_qty_key = array_search( 'Product Quantity', $wpssw_woo_selections, true );
				if ( false !== $wpssw_product_id_key ) {
					foreach ( $wpssw_order_items as $order_item ) {
						$product_id        = isset( $order_item[ $wpssw_product_id_key ] ) ? $order_item[ $wpssw_product_id_key ] : '';
						$wpssw_product_qty = '';
						if ( false !== $wpssw_product_qty_key ) {
							$wpssw_product_qty_str  = isset( $order_item[ $wpssw_product_qty_key ] ) ? $order_item[ $wpssw_product_qty_key ] : 1;
							$wpssw_product_val      = explode( '(', $wpssw_product_qty_str );
							$wpssw_product_qty      = isset( $wpssw_product_val[0] ) ? trim( $wpssw_product_val[0] ) : '';
							$wpssw_product_discount = isset( $wpssw_product_val[1] ) ? trim( str_replace( ')', '', $wpssw_product_val[1] ) ) : '';
						}
						if ( $product_id ) {
							$product_ids[] = array(
								'product_id'  => $product_id,
								'product_qty' => $wpssw_product_qty,
							);
							if ( $wpssw_product_discount ) {
								$product_items_array[ $product_id ] = array(
									'product_id'       => $product_id,
									'product_discount' => $wpssw_product_discount,
								);
							}
						}
					}
				} else {
					echo 'addproductidcolumn';
					die;
				}
				$product_ids = array_filter( $product_ids );
				if ( empty( $product_ids ) ) {
					echo 'addproductid';
					die;
				}
			} else {
				$wpssw_product_name_key = array_search( 'Product name(QTY)(SKU)', $wpssw_woo_selections, true );

				$wpssw_product_name = isset( $wpssw_order_data[ $wpssw_product_name_key ] ) ? $wpssw_order_data[ $wpssw_product_name_key ] : '';
				if ( empty( $wpssw_product_name ) ) {
					echo 'addproductname';
					die;
				}
				$wpssw_products_name = explode( '),', $wpssw_product_name );
				if ( false !== $wpssw_product_name_key ) {
					foreach ( $wpssw_products_name as $product_name ) {
						$wpssw_product_val = array();
						$wpssw_product_val = array_reverse( explode( '(', $product_name ) );

						$wpssw_product_discount = isset( $wpssw_product_val[0] ) ? str_replace( ')', '', $wpssw_product_val[0] ) : '';
						$wpssw_product_sku      = isset( $wpssw_product_val[1] ) ? str_replace( ')', '', $wpssw_product_val[1] ) : '';
						$wpssw_product_qty      = isset( $wpssw_product_val[2] ) ? str_replace( ')', '', $wpssw_product_val[2] ) : 1;
						$wpssw_product_name     = isset( $wpssw_product_val[3] ) ? $wpssw_product_val[3] : '';

						$wpssw_product_data[] = array(
							'product_name'     => $wpssw_product_name,
							'product_sku'      => $wpssw_product_sku,
							'product_qty'      => $wpssw_product_qty,
							'product_discount' => $wpssw_product_discount,
						);

					}
				} else {
					echo 'addproductnamecolumn';
					die;
				}
			}
			$wpssw_order        = wc_create_order();
			$wpssw_orderid      = $wpssw_order->get_id();
			$wpssw_product_data = array_filter( $wpssw_product_data );
			$update_order_total = 0;
			$ordertotal_value   = '';

			if ( ! empty( $product_ids ) ) {
				foreach ( $product_ids as $prd ) {
					if ( ! empty( $prd['product_id'] ) ) {
						$wpssw_product = wc_get_product( $prd['product_id'] );
						$wpssw_order->add_product( $wpssw_product, $prd['product_qty'] );
					}
				}
			} elseif ( ! empty( $wpssw_product_data ) ) {
				foreach ( $wpssw_product_data as $prd_data ) {
					$product_id             = '';
					$wpssw_product_name     = '';
					$wpssw_product_sku      = '';
					$wpssw_product_qty      = '';
					$wpssw_product_name     = $prd_data['product_name'];
					$wpssw_product_sku      = $prd_data['product_sku'];
					$wpssw_product_qty      = $prd_data['product_qty'];
					$wpssw_product_discount = $prd_data['product_discount'];
					$product_args           = array(
						'title'     => $wpssw_product_name,
						'post_type' => 'product',
					);
					if ( ! empty( $wpssw_product_sku ) && ! empty( $wpssw_product_name ) ) {
						$product_args['meta_query'] = array( // @codingStandardsIgnoreLine.
							array(
								'key'     => '_sku',
								'value'   => $wpssw_product_sku,
								'compare' => '=',
							),
						);
						$products                   = get_posts( $product_args );
						if ( isset( $products[0] ) && ! empty( $products[0] ) ) {
							$product_id = $products[0]->ID;
						}
					}
					if ( ! empty( $wpssw_product_sku ) && ! $product_id ) {
						$product_id = wc_get_product_id_by_sku( $wpssw_product_sku );
					}
					if ( ! empty( $wpssw_product_name ) && ! $product_id ) {
						$products = get_posts( $product_args );
						if ( isset( $products[0] ) && ! empty( $products[0] ) ) {
							$product_id = $products[0]->ID;
						}
					}
					if ( $product_id ) {
						$wpssw_product = wc_get_product( $product_id );

						$wpssw_order->add_product( $wpssw_product, $wpssw_product_qty );
						if ( $wpssw_product_discount ) {
							$product_items_array[ $product_id ] = array(
								'product_id'       => $product_id,
								'product_discount' => $wpssw_product_discount,
							);
						}
					}
				}
			}

			foreach ( $wpssw_woo_selections as $wpssw_key => $wpssw_header ) {
				if ( ! in_array( $wpssw_header, $wpssw_include_headers, true ) ) {
					continue;
				}

				if ( 'Coupons Codes' === (string) $wpssw_header ) {
					$coupons      = isset( $wpssw_order_data[ $wpssw_key ] ) ? $wpssw_order_data[ $wpssw_key ] : '';
					$coupon_codes = array_map( 'trim', explode( ',', $coupons ) );
					foreach ( $coupon_codes as $code ) {
						$wpssw_order->apply_coupon( $code );
					}
					continue;
				}
				if ( 'Order Currency' === (string) $wpssw_header ) {
					$wpssw_order->set_currency( get_woocommerce_currency() );
					continue;
				}
				if ( 'Prices Include Tax' === (string) $wpssw_header ) {
					$wpssw_order->set_prices_include_tax( 'yes' === get_option( 'woocommerce_prices_include_tax' ) );
					continue;
				}
				if ( 'Customer ID' === (string) $wpssw_header ) {
					$customer_id   = isset( $wpssw_order_data[ $wpssw_key ] ) ? $wpssw_order_data[ $wpssw_key ] : '';
					if($customer_id){
						$user = get_user_by('id', $customer_id);
					}else{
						$user = wp_get_current_user();
					}
					if(!$user || is_wp_error($user)){
						$user = wp_get_current_user();
					}
					$wpssw_order->set_customer_id( $user->ID );
					continue;
				}
				if( 'Payment Method' === (string) $wpssw_header ){
				    $desired_payment_title   = isset( $wpssw_order_data[ $wpssw_key ] ) ? trim($wpssw_order_data[ $wpssw_key ]) : '';
				    if($desired_payment_title){

				        $available_gateways = WC()->payment_gateways->payment_gateways();

				        // Initialize a variable to store the payment method slug.
				        $payment_method_slug = '';

				        // Loop through the available payment gateways to find the one matching the desired title.
				        foreach ($available_gateways as $gateway) {
				            if ($gateway->get_title() === $desired_payment_title) {
				                $payment_method_slug = $gateway->id;
				                break;
				            }
				        }
				        $available_gateways = null;

				        if ($payment_method_slug) {
				            // Get the current payment method slug.
				            $current_payment_method_slug = $wpssw_order->get_payment_method();

				            // Check if the current payment method is different from the desired one.
				            if ($current_payment_method_slug !== $payment_method_slug) {

				                // Set the payment method using the slug.
				                $wpssw_order->set_payment_method($payment_method_slug);

				                // Set the payment method title to match the desired title.
				                $wpssw_order->set_payment_method_title($desired_payment_title);
				                continue;

				            }
				        }
				    }
				}
			}

			$wpssw_order->calculate_totals();
			$wpssw_order->save();
			$wpssw_items = $wpssw_order->get_items();

			foreach ( $wpssw_items as $wpssw_itemid => $wpssw_item ) {

				$item_productid = '';
				if ( isset( $wpssw_item['variation_id'] ) && $wpssw_item['variation_id'] > 0 ) {
					$item_productid = $wpssw_item['variation_id'];
				} else {
					$item_productid = isset( $wpssw_item['product_id'] ) ? $wpssw_item['product_id'] : '';
				}

				if ( ! empty( $item_productid ) && false !== array_key_exists( $item_productid, $product_items_array ) ) {
					$prd_data   = $product_items_array[ $item_productid ];
					$total      = $prd_data['product_discount'];
					$item_total = $wpssw_item->get_total();
					$prd_total  = $item_total - $total;
					$wpssw_item->set_total( $prd_total );
					$wpssw_item->save();
				}
			}
			$wpssw_order->calculate_taxes();
			$wpssw_order->calculate_totals();
			$wpssw_order->save();
			return $wpssw_orderid;
		}



	}
	new WPSSW_Order_Import();
endif;
