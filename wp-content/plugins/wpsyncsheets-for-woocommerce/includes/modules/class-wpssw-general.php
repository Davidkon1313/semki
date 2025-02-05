<?php
/**
 * Main WPSyncSheets_For_WooCommerce namespace.
 *
 * @since 1.0.0
 * @package wpsyncsheets-for-woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }
if ( ! class_exists( 'WPSSW_General' ) ) :
	/**
	 * Class WPSSW_General.
	 */
	class WPSSW_General extends WPSSW_Setting {
		/**
		 * Initialization
		 */
		public function __construct() {
			$wpssw_include = new WPSSW_Include_Action();
			$wpssw_include->wpssw_include_general_ajax_hook();
		}
		/**
		 * Save Settings of General settings tab.
		 */
		public static function wpssw_update_general_settings() {

			if ( ! isset( $_POST['wpssw_general_settings'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wpssw_general_settings'] ) ), 'save_general_settings' ) ) {
				echo esc_html__( 'Sorry, your nonce did not verify.', 'wpssw' );
				die();
			}

			// Row Input Format Option.
			if ( isset( $_POST['wpssw_price_format'] ) ) {
				$wpssw_price_format = sanitize_text_field( wp_unslash( $_POST['wpssw_price_format'] ) );
				parent::wpssw_update_option( 'wpssw_price_format', $wpssw_price_format );
			}

			/* Chart Display Function Start */
			$wpssw_data = $_POST;

			if ( isset( $_POST['graph_sheet'] ) ) {
				parent::wpssw_update_option( 'graph_sheet', sanitize_text_field( wp_unslash( $_POST['graph_sheet'] ) ) );
				$graphsheets_list = array();
				if ( isset( $_POST['graphsheets_list'] ) ) {
					$graphsheets_list = array_map( 'sanitize_text_field', wp_unslash( $_POST['graphsheets_list'] ) );
				}
				parent::wpssw_update_option( 'wpssw_graphsheets_list', $graphsheets_list );
			} else {
				parent::wpssw_update_option( 'graph_sheet', 'no' );
				parent::wpssw_update_option( 'wpssw_graphsheets_list', array() );
				$wpssw_data['graphsheets_list'] = array();
			}

			$graph_types = array();

			if ( isset( $_POST['sales_orders_graph_type'] ) ) {
				$graph_types['Sales Orders Graph'] = sanitize_text_field( wp_unslash( $_POST['sales_orders_graph_type'] ) );
				parent::wpssw_update_option( 'wpssw_sales_orders_graph_type', sanitize_text_field( wp_unslash( $_POST['sales_orders_graph_type'] ) ) );
			}
			if ( isset( $_POST['total_orders_graph_type'] ) ) {
				$graph_types['Total Orders Graph'] = sanitize_text_field( wp_unslash( $_POST['total_orders_graph_type'] ) );
				parent::wpssw_update_option( 'wpssw_total_orders_graph_type', sanitize_text_field( wp_unslash( $_POST['total_orders_graph_type'] ) ) );
			}
			if ( isset( $_POST['products_sold_graph_type'] ) ) {
				$graph_types['Products Sold Graph'] = sanitize_text_field( wp_unslash( $_POST['products_sold_graph_type'] ) );
				parent::wpssw_update_option( 'wpssw_products_sold_graph_type', sanitize_text_field( wp_unslash( $_POST['products_sold_graph_type'] ) ) );
			}
			if ( isset( $_POST['total_customers_graph_type'] ) ) {
				$graph_types['Total Customers Graph'] = sanitize_text_field( wp_unslash( $_POST['total_customers_graph_type'] ) );
				parent::wpssw_update_option( 'wpssw_total_customers_graph_type', sanitize_text_field( wp_unslash( $_POST['total_customers_graph_type'] ) ) );
			}
			if ( isset( $_POST['total_used_coupons_graph_type'] ) ) {
				$graph_types['Total Used Coupons Graph'] = sanitize_text_field( wp_unslash( $_POST['total_used_coupons_graph_type'] ) );
				parent::wpssw_update_option( 'wpssw_total_used_coupons_graph_type', sanitize_text_field( wp_unslash( $_POST['total_used_coupons_graph_type'] ) ) );
			}

			// Row Background Color.
			if ( isset( $_POST['color_code'] ) && isset( $_POST['oddcolor'] ) && isset( $_POST['evencolor'] ) ) {
				$oddcolor  = sanitize_text_field( wp_unslash( $_POST['oddcolor'] ) );
				$evencolor = sanitize_text_field( wp_unslash( $_POST['evencolor'] ) );
				parent::wpssw_update_option( 'wpssw_color_code', 1 );
			} else {
				$oddcolor  = '#ffffff';
				$evencolor = '#ffffff';
				parent::wpssw_update_option( 'wpssw_color_code', 0 );
			}
			if ( isset( $_POST['freeze_header'] ) ) {
				$wpssw_freeze = 1;
				parent::wpssw_update_option( 'freeze_header', 'yes' );
			} else {
				$wpssw_freeze = 0;
				parent::wpssw_update_option( 'freeze_header', 'no' );
			}
			if ( isset( $_POST['color_code'] ) ) {
				$oddcolor          = isset( $_POST['oddcolor'] ) ? sanitize_text_field( wp_unslash( $_POST['oddcolor'] ) ) : '#ffffff';
				$evencolor         = isset( $_POST['evencolor'] ) ? sanitize_text_field( wp_unslash( $_POST['evencolor'] ) ) : '#ffffff';
				$rowcolor_disabled = false;
			} else {
				$oddcolor          = '#ffffff';
				$evencolor         = '#ffffff';
				$rowcolor_disabled = true;
			}
			$wpssw_freeze_header = 1;
			$wpssw_color         = 1;

			$wpssw_settings          = array(
				'wpssw_coupon_spreadsheet_setting'   => array(
					'spreadsheet' => 'wpssw_coupon_spreadsheet_id',
					'settings'    => 'wpssw_coupon_override',
					'sheetname'   => 'All Coupons',
				),
				'wpssw_product_spreadsheet_setting'  => array(
					'spreadsheet' => 'wpssw_product_spreadsheet_id',
					'settings'    => 'wpssw_product_override',
					'sheetname'   => 'All Products',
				),
				'wpssw_customer_spreadsheet_setting' => array(
					'spreadsheet' => 'wpssw_customer_spreadsheet_id',
					'settings'    => 'wpssw_customer_override',
					'sheetname'   => 'All Customers',
				),
				'wpssw_event_spreadsheet_setting'    => array(
					'spreadsheet' => 'wpssw_woocommerce_spreadsheet',
					'settings'    => 'wpssw_event_override',
					'sheetname'   => 'wpssw_eventsheets_list',
				),
				'wpssw_order_spreadsheet_setting'    => array(
					'spreadsheet' => 'wpssw_woocommerce_spreadsheet',
					'settings'    => 'wpssw_order_override',
					'sheetname'   => 'wpssw_sheets',
				),
			);
			$wpssw_spreadsheets_list = self::$instance_api->get_spreadsheet_listing();
			foreach ( $wpssw_settings as $key => $values ) {
				$is_active = parent::wpssw_option( $key );
				$settings  = $values['settings'];

				if ( 'wpssw_order_spreadsheet_setting' === (string) $key || 'wpssw_event_spreadsheet_setting' === (string) $key ) {
					$is_active = WPSSW_Order::wpssw_check_order_spreadsheet_setting();
				}
				$wpssw_spreadsheetid = parent::wpssw_option( $values['spreadsheet'] );
				if ( 'yes' !== (string) $is_active || 'yes' === (string) $settings || '' === (string) $wpssw_spreadsheetid || 'new' === $wpssw_spreadsheetid || ! array_key_exists( $wpssw_spreadsheetid, $wpssw_spreadsheets_list ) ) {
					continue;
				}

				$sheetname = $values['sheetname'];
				if ( 'wpssw_sheets' === (string) $sheetname || 'wpssw_eventsheets_list' === (string) $sheetname ) {
					$wpssw_sheets = array_filter( (array) parent::wpssw_option( $sheetname ) );
					if ( 'wpssw_sheets' === (string) $sheetname && ! $wpssw_sheets ) {
						$wpssw_sheets = WPSSW_Order::wpssw_prepare_sheets();
					}
				} else {
					$wpssw_sheets = array( $sheetname );
				}
				$wpssw_order_spreadsheet = parent::wpssw_freeze_header( $wpssw_spreadsheetid, $wpssw_freeze, $oddcolor, $evencolor, $wpssw_color, $wpssw_freeze_header, $rowcolor_disabled, $wpssw_sheets );
			}

			parent::wpssw_update_option( 'wpssw_oddcolor', $oddcolor );
			parent::wpssw_update_option( 'wpssw_evencolor', $evencolor );

			// Row Input Format Option.
			if ( isset( $_POST['inputoption'] ) ) {
				$wpssw_inputoption = sanitize_text_field( wp_unslash( $_POST['inputoption'] ) );
				parent::wpssw_update_option( 'wpssw_inputoption', $wpssw_inputoption );
			}

			// Price Format.
			if ( isset( $_POST['wpssw_price_format'] ) ) {
				$wpssw_price_format = sanitize_text_field( wp_unslash( $_POST['wpssw_price_format'] ) );
				parent::wpssw_update_option( 'wpssw_price_format', $wpssw_price_format );
			}

			$wpssw_order_spreadsheet_setting = WPSSW_Order::wpssw_check_order_spreadsheet_setting();
			$wpssw_graphsheets_list          = array();
			$wpssw_spreadsheetid             = parent::wpssw_option( 'wpssw_woocommerce_spreadsheet' );

			if ( 'yes' === (string) $wpssw_order_spreadsheet_setting && '' !== (string) $wpssw_spreadsheetid && 'new' !== $wpssw_spreadsheetid && array_key_exists( $wpssw_spreadsheetid, $wpssw_spreadsheets_list ) ) {
				$wpssw_remove_sheet = array();
				if ( isset( $wpssw_data['graphsheets_list'] ) ) {
					$wpssw_graphsheets_list = $wpssw_data['graphsheets_list'];
				}
				foreach ( $wpssw_graphsheets_list as $list ) {
					$wpssw_data[ $list ] = 1;
				}
				if ( isset( $wpssw_data['sales_orders_graph'] ) ) {
					$wpssw_sales_orders_graph = $wpssw_data['sales_orders_graph'];
				} else {
					$wpssw_sales_orders_graph = 0;
					$wpssw_remove_sheet[]     = 'Sales Orders Graph';
				}
				if ( isset( $wpssw_data['total_orders_graph'] ) ) {
					$wpssw_total_orders_graph = $wpssw_data['total_orders_graph'];
				} else {
					$wpssw_total_orders_graph = 0;
					$wpssw_remove_sheet[]     = 'Total Orders Graph';
				}
				if ( isset( $wpssw_data['products_sold_graph'] ) ) {
					$wpssw_products_sold_graph = $wpssw_data['products_sold_graph'];
				} else {
					$wpssw_products_sold_graph = 0;
					$wpssw_remove_sheet[]      = 'Products Sold Graph';
				}
				if ( isset( $wpssw_data['total_customers_graph'] ) ) {
					$wpssw_total_customers_graph = $wpssw_data['total_customers_graph'];
				} else {
					$wpssw_total_customers_graph = 0;
					$wpssw_remove_sheet[]        = 'Total Customers Graph';
				}
				if ( isset( $wpssw_data['total_used_coupons_graph'] ) ) {
					$wpssw_total_used_coupons_graph = $wpssw_data['total_used_coupons_graph'];
				} else {
					$wpssw_total_used_coupons_graph = 0;
					$wpssw_remove_sheet[]           = 'Total Used Coupons Graph';
				}
				$wpssw_graphsheets_array      = array( $wpssw_sales_orders_graph, $wpssw_total_orders_graph, $wpssw_products_sold_graph, $wpssw_total_customers_graph, $wpssw_total_used_coupons_graph );
				$wpssw_graph_sheetnames       = array( 'Sales Orders Graph', 'Total Orders Graph', 'Products Sold Graph', 'Total Customers Graph', 'Total Used Coupons Graph' );
				$wpssw_graph_sheetnames_count = count( $wpssw_graph_sheetnames );

				$response             = self::$instance_api->get_sheet_listing( $wpssw_spreadsheetid );
				$wpssw_existingsheets = self::$instance_api->get_sheet_list( $response );
				$wpssw_existingsheets = array_flip( $wpssw_existingsheets );

				for ( $j = 0; $j < $wpssw_graph_sheetnames_count; $j++ ) {
					if ( in_array( $wpssw_graph_sheetnames[ $j ], $wpssw_existingsheets, true ) ) {
						$wpssw_graphsheets_array[ $j ] = 0;
					} else {
						if ( 1 === (int) $wpssw_graphsheets_array[ $j ] && ! in_array( $wpssw_graph_sheetnames[ $j ], $wpssw_existingsheets, true ) ) {
							$wpssw_graphsheets_array[ $j ] = 1;
						}
					}
				}

				$wpssw_graphsheets_array_count = count( $wpssw_graphsheets_array );
				for ( $j = 0; $j < $wpssw_graphsheets_array_count; $j++ ) {
					$j = (int) $j;
					if ( 0 === $j ) {
						$wpssw_graphsheetname = 'Sales Orders Graph';
					}
					if ( 1 === $j ) {
						$wpssw_graphsheetname = 'Total Orders Graph';
					}
					if ( 2 === $j ) {
						$wpssw_graphsheetname = 'Products Sold Graph';
					}
					if ( 3 === $j ) {
						$wpssw_graphsheetname = 'Total Customers Graph';
					}
					if ( 4 === $j ) {
						$wpssw_graphsheetname = 'Total Used Coupons Graph';
					}
					if ( 1 === (int) $wpssw_graphsheets_array[ $j ] ) {
						$param                  = array();
						$param['spreadsheetid'] = $wpssw_spreadsheetid;
						$param['sheetname']     = $wpssw_graphsheetname;
						$wpssw_response         = self::$instance_api->newsheetobject( $param );
						$graph_type             = isset( $graph_types[ $wpssw_graphsheetname ] ) ? $graph_types[ $wpssw_graphsheetname ] : 'column';
						self::wpssw_add_graph( $wpssw_graphsheetname, $graph_type, $wpssw_spreadsheetid );
					}
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
				if ( ! empty( $wpssw_remove_sheet ) ) {
					parent::wpssw_delete_sheet( $wpssw_spreadsheetid, $wpssw_remove_sheet, $wpssw_existingsheets );
				}
			}

			/* Chart Display Function End */
		}
		/**
		 * Add Graph(Chart) into sheet
		 *
		 * @param string $wpssw_graphsheetname .
		 * @param string $graph_type .
		 * @param string $wpssw_spreadsheetid .
		 */
		public static function wpssw_add_graph( $wpssw_graphsheetname = '', $graph_type = '', $wpssw_spreadsheetid = '' ) {
			$wpssw_spreadsheetid = ! empty( $wpssw_spreadsheetid ) ? $wpssw_spreadsheetid : parent::wpssw_option( 'wpssw_woocommerce_spreadsheet' );
			$wpssw_inputoption   = parent::wpssw_option( 'wpssw_inputoption' );
			if ( ! $wpssw_inputoption ) {
				$wpssw_inputoption = 'USER_ENTERED';
			}
			$wpssw_charts_data         = array();
			$wpssw_existingsheetsnames = array();
			$wpssw_response            = self::$instance_api->get_sheet_listing( $wpssw_spreadsheetid );
			$wpssw_existingsheetsnames = self::$instance_api->get_sheet_list( $wpssw_response );
			$wpssw_chart_sheet_months  = array( 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December' );
			$hpos_order_enabled        = parent::wpssw_check_hpos_order_setting_enabled();
			if ( $hpos_order_enabled ) {
				// Base query arguments.
				$wpssw_query_args = array(
					'orderby' => 'date',
					'order'   => 'ASC',
					'limit'   => -1, // Fetch all orders.
					'return'  => 'ids', // Fetch only IDs.
					'status'  => array_keys( wc_get_order_statuses() ),
				);

				// Fetch the orders.
				$wpssw_all_orders = wc_get_orders( $wpssw_query_args );

			} else {
				$wpssw_query_args           = array(
					'post_type'      => 'shop_order',
					'posts_per_page' => -1,
					'order'          => 'ASC',
					'post_status'    => array_keys( wc_get_order_statuses() ),
				);
				$wpssw_query_args['fields'] = 'ids'; // Fetch only ids.

				$wpssw_all_orders = get_posts( $wpssw_query_args );
			}
			$months_array = array();
			$wpssw_years  = array();
			foreach ( $wpssw_all_orders as $wpssw_order_id ) {
				$wpssw_order        = wc_get_order( $wpssw_order_id );
				$wpssw_version      = '3.7.0';
				$used_coupons_count = 0;
				global $woocommerce;
				if ( version_compare( $woocommerce->version, $wpssw_version, '>=' ) ) {
					$used_coupons_count = count( $wpssw_order->get_coupon_codes() );
				} else {
					$used_coupons_count = count( $wpssw_order->get_used_coupons() );
				}
				$time  = strtotime( $wpssw_order->get_date_created() );
				$month = date_i18n( 'n', $time );
				$month = self::wpssw_get_month( $month );

				if ( 'Total Used Coupons Graph' === (string) $wpssw_graphsheetname ) {
					if ( $used_coupons_count > 0 ) {
						$wpssw_years[] = (int) date_i18n( 'Y', $time );
					}
				} elseif ( 'Total Customers Graph' === (string) $wpssw_graphsheetname ) {
					$args            = array(
						'role'    => 'customer',
						'orderby' => 'ID',
						'order'   => 'ASC',
					);
					$wpssw_customers = get_users( $args );
					if ( ! empty( $wpssw_customers ) && count( $wpssw_customers ) > 0 ) {
						$wpssw_years[] = (int) date_i18n( 'Y', $time );
					}
				} else {
					$wpssw_years[] = (int) date_i18n( 'Y', $time );
				}
			}
			if ( empty( $wpssw_years ) ) {
				$wpssw_year_range = array();
			} else {
				$year_from        = min( $wpssw_years );
				$year_to          = max( $wpssw_years );
				$wpssw_year_range = array();
				if ( $year_from === $year_to ) {
					$wpssw_year_range = array( $year_from );
				} else {
					$wpssw_year_range = array();
					$range            = $year_to - $year_from;
					for ( $i = 0;$i <= $range;$i++ ) {
						if ( in_array( $year_from, $wpssw_years, true ) ) {
							$wpssw_year_range[] = $year_from;
						}
						$year_from++;
					}
				}
				rsort( $wpssw_year_range );
			}
			$order_data = array();
			foreach ( $wpssw_all_orders as $wpssw_order_id ) {
				$wpssw_order      = wc_get_order( $wpssw_order_id );
				$wpssw_order_data = $wpssw_order->get_data();
				$wpssw_items      = $wpssw_order->get_items();
				$time             = strtotime( $wpssw_order->get_date_created() );
				$month            = date_i18n( 'n', $time );
				$month            = self::wpssw_get_month( $month );

				$year               = date_i18n( 'Y', $time );
				$wpssw_order_status = $wpssw_order->get_status();
				foreach ( $wpssw_year_range as $wpssw_yrange ) {
					if ( $wpssw_yrange === (int) $year ) {

						if ( ! in_array( $wpssw_order_status, array( 'processing', 'completed' ), true ) ) {
							continue;
						}
						if ( isset( $order_data[ $year ][ $month ] ) ) {
							$order_data[ $year ][ $month ] = $order_data[ $year ][ $month ];
						} else {
							$order_data[ $year ][ $month ] = 0;
						}
						if ( 'Sales Orders Graph' === (string) $wpssw_graphsheetname ) {
							$order_data[ $year ][ $month ] = $order_data[ $year ][ $month ] + (float) $wpssw_order_data['total'] - (float) $wpssw_order->get_total_refunded();
						}
						if ( 'Total Orders Graph' === (string) $wpssw_graphsheetname ) {
								$order_data[ $year ][ $month ] = $order_data[ $year ][ $month ] + 1;
						}
						if ( 'Products Sold Graph' === (string) $wpssw_graphsheetname ) {
							$wpssw_prod_qty = 0;
							foreach ( $wpssw_items as $wpssw_item ) {
								$wpssw_prod_qty = $wpssw_prod_qty + $wpssw_item['quantity'];
							}
							$order_data[ $year ][ $month ] = $order_data[ $year ][ $month ] + (int) $wpssw_prod_qty;
						}
						if ( 'Total Used Coupons Graph' === (string) $wpssw_graphsheetname ) {
							$wpssw_version      = '3.7.0';
							$used_coupons_count = 0;
							global $woocommerce;
							if ( version_compare( $woocommerce->version, $wpssw_version, '>=' ) ) {
								$used_coupons_count = count( $wpssw_order->get_coupon_codes() );
							} else {
								$used_coupons_count = count( $wpssw_order->get_used_coupons() );
							}
							if ( $used_coupons_count > 0 ) {
								$order_data[ $year ][ $month ] = $order_data[ $year ][ $month ] + $used_coupons_count;
							}
						}
					}
				}
			}

			if ( 'Sales Orders Graph' === (string) $wpssw_graphsheetname ) {
				$wpssw_year_range1 = array();

				foreach ( $wpssw_year_range as $wpssw_yrange ) {
					$count_sales_year = 0;
					foreach ( $order_data[ $wpssw_yrange ] as $key => &$value ) {
						$count_sales_year += $value;
					}
					if ( (int) $count_sales_year > 0 ) {
						$wpssw_year_range1[] = $wpssw_yrange;
					}
				}
				if ( $wpssw_year_range1 !== $wpssw_year_range ) {
					$wpssw_year_range = array();
					$wpssw_year_range = $wpssw_year_range1;
				}
			}
			if ( 'Total Customers Graph' === (string) $wpssw_graphsheetname ) {
					$args            = array(
						'role'    => 'customer',
						'orderby' => 'ID',
						'order'   => 'ASC',
					);
					$wpssw_customers = get_users( $args );
					$wpssw_years     = array();
					foreach ( $wpssw_customers as $customer ) {
						$time  = strtotime( $customer->user_registered );
						$month = date_i18n( 'n', $time );
						$month = self::wpssw_get_month( $month );

						$wpssw_years[] = (int) date_i18n( 'Y', $time );
					}
					if ( ! empty( $wpssw_years ) ) {
						$year_from        = min( $wpssw_years );
						$year_to          = max( $wpssw_years );
						$wpssw_year_range = array();
						if ( $year_from === $year_to ) {
							$wpssw_year_range = array( $year_from );
						} else {
							$wpssw_year_range = array();
							$range            = $year_to - $year_from;
							for ( $i = 0;$i <= $range;$i++ ) {
								if ( in_array( $year_from, $wpssw_years, true ) ) {
									$wpssw_year_range[] = $year_from;
								}
								$year_from++;
							}
						}
						rsort( $wpssw_year_range );
					} else {
						$wpssw_year_range = array();
					}
					$order_data = array();
					foreach ( $wpssw_customers as $customer ) {
						$time  = strtotime( $customer->user_registered );
						$month = date_i18n( 'n', $time );
						$month = self::wpssw_get_month( $month );
						$year  = date_i18n( 'Y', $time );
						foreach ( $wpssw_year_range as $wpssw_yrange ) {
							if ( $wpssw_yrange === (int) $year ) {
								if ( isset( $order_data[ $year ][ $month ] ) ) {
									$order_data[ $year ][ $month ] = $order_data[ $year ][ $month ];
								} else {
									$order_data[ $year ][ $month ] = 0;
								}
								$order_data[ $year ][ $month ] = $order_data[ $year ][ $month ] + 1;
							}
						}
					}
			}
			$wpssw_values       = array();
			$wpssw_values_array = array();
			foreach ( $wpssw_year_range as $wpssw_yrange ) {
				if ( 'Total Orders Graph' === (string) $wpssw_graphsheetname ) {
					$wpssw_values_array[ $wpssw_yrange ][] = array( 'Months of ' . $wpssw_yrange, 'Orders' );
					$wpssw_axisname                        = 'Orders';
					$wpssw_graphtitle                      = 'Total Orders';
					if ( empty( $graph_type ) ) {
						// phpcs:ignore.
						$graph_type = isset( $_POST['total_orders_graph_type'] ) ? strtoupper( sanitize_text_field( wp_unslash( $_POST['total_orders_graph_type'] ) ) ) : '';
					}
				}
				if ( 'Sales Orders Graph' === (string) $wpssw_graphsheetname ) {
					if ( empty( $graph_type ) ) {
						// phpcs:ignore.
						$graph_type = isset( $_POST['sales_orders_graph_type'] ) ? strtoupper( sanitize_text_field( wp_unslash( $_POST['sales_orders_graph_type'] ) ) ) : '';
					}
					$priceargs                             = wp_parse_args(
						array(),
						array(
							'ex_tax_label'       => false,
							'currency'           => '',
							'decimal_separator'  => wc_get_price_decimal_separator(),
							'thousand_separator' => wc_get_price_thousand_separator(),
							'decimals'           => wc_get_price_decimals(),
							'price_format'       => get_woocommerce_price_format(),
						)
					);
					$wpssw_axisname                        = 'Sales (' . get_woocommerce_currency_symbol( $priceargs['currency'] ) . ')';
					$wpssw_axisname                        = html_entity_decode( $wpssw_axisname );
					$wpssw_values_array[ $wpssw_yrange ][] = array( 'Months of ' . $wpssw_yrange, $wpssw_axisname );
					$wpssw_graphtitle                      = 'Total Sales';
				}
				if ( 'Products Sold Graph' === (string) $wpssw_graphsheetname ) {
					if ( empty( $graph_type ) ) {
						// phpcs:ignore.
						$graph_type = isset( $_POST['products_sold_graph_type'] ) ? strtoupper( sanitize_text_field( wp_unslash( $_POST['products_sold_graph_type'] ) ) ) : '';
					}
					$wpssw_values_array[ $wpssw_yrange ][] = array( 'Months of ' . $wpssw_yrange, 'Products Sold' );
					$wpssw_axisname                        = 'Products Sold';
					$wpssw_graphtitle                      = 'Total Sold Products';
				}
				if ( 'Total Customers Graph' === (string) $wpssw_graphsheetname ) {
					if ( empty( $graph_type ) ) {
						// phpcs:ignore.
						$graph_type = isset( $_POST['total_customers_graph_type'] ) ? strtoupper( sanitize_text_field( wp_unslash( $_POST['total_customers_graph_type'] ) ) ) : '';
					}
					$wpssw_values_array[ $wpssw_yrange ][] = array( 'Months of ' . $wpssw_yrange, 'Customers' );
					$wpssw_axisname                        = 'Customers';
					$wpssw_graphtitle                      = 'Total Customers';
				}
				if ( 'Total Used Coupons Graph' === (string) $wpssw_graphsheetname ) {
					if ( empty( $graph_type ) ) {
						// phpcs:ignore.
						$graph_type = isset( $_POST['total_used_coupons_graph_type'] ) ? strtoupper( sanitize_text_field( wp_unslash( $_POST['total_used_coupons_graph_type'] ) ) ) : '';
					}
					$wpssw_values_array[ $wpssw_yrange ][] = array( 'Months of ' . $wpssw_yrange, 'Used Coupons' );
					$wpssw_axisname                        = 'Used Coupons';
					$wpssw_graphtitle                      = 'Total Used Coupons';
				}
				foreach ( $wpssw_chart_sheet_months as $m1 ) {
					$wpssw_values   = array();
					$wpssw_values[] = $m1;
					if ( array_key_exists( $m1, $order_data[ $wpssw_yrange ] ) ) {
						$wpssw_values[] = $order_data[ $wpssw_yrange ][ $m1 ];
					} else {
						$wpssw_values[] = 0;
					}
					$wpssw_values_array[ $wpssw_yrange ][] = $wpssw_values;
				}
			}
			if ( count( $wpssw_year_range ) > 0 ) {
				$yeardata_index = 25 * count( $wpssw_year_range ) + 2;
			}
			foreach ( $wpssw_year_range as $yrange ) {
				$wpssw_range       = trim( $wpssw_graphsheetname ) . '!A' . $yeardata_index;
				$wpssw_requestbody = self::$instance_api->valuerangeobject( $wpssw_values_array[ $yrange ] );
				$wpssw_params      = array( 'valueInputOption' => $wpssw_inputoption );
				$param             = self::$instance_api->setparamater( $wpssw_spreadsheetid, $wpssw_range, $wpssw_requestbody, $wpssw_params );
				$wpssw_response1   = self::$instance_api->appendentry( $param );
				$yeardata_index    = $yeardata_index + count( $wpssw_values_array[ $yrange ] ) + 2;
			}
			$wpssw_graph_sheetid = $wpssw_existingsheetsnames[ $wpssw_graphsheetname ];
			$wpssw_sheet         = "'" . $wpssw_graphsheetname . "'!A:A";
			$wpssw_allentry      = self::$instance_api->get_row_list( $wpssw_spreadsheetid, $wpssw_sheet );
			$wpssw_data          = $wpssw_allentry->getValues();
			if ( ! empty( $wpssw_data ) ) {
				foreach ( $wpssw_data as $key => $value ) {
					if ( ! isset( $value['0'] ) ) {
						unset( $wpssw_data[ $key ] );
					}
				}
				$endcolumnindex_domain  = 2;
				$overlayposition_row    = 2;
				$wpssw_year_range_count = count( $wpssw_year_range );
				for ( $i = 0;$i < $wpssw_year_range_count;$i++ ) {
					foreach ( $wpssw_data as $key => $value ) {
						if ( in_array( 'Months of ' . $wpssw_year_range[ $i ], $value, true ) ) {
							$startrowindex_domain = (int) $key;
						}
					}
					$endrowindex_domain = $startrowindex_domain + 13;
					try {
						$param                        = array();
						$param['graph_title']         = $wpssw_graphtitle . ' of Year ' . $wpssw_year_range[ $i ];
						$param['graph_type']          = $graph_type;
						$param['bottom_axisname']     = 'Months';
						$param['left_axisname']       = $wpssw_axisname;
						$param['graph_sheetID']       = $wpssw_graph_sheetid;
						$param['startRowIndex']       = $startrowindex_domain;
						$param['endRowIndex']         = $endrowindex_domain;
						$param['endColumnIndex']      = $endcolumnindex_domain;
						$param['row_overlayPosition'] = $overlayposition_row;
						$param['spreadsheetid']       = $wpssw_spreadsheetid;
						$wpssw_response               = self::$instance_api->addchartobject( $param );
						$overlayposition_row          = $overlayposition_row + 25;
					} catch ( Exception $e ) {
						echo esc_html( 'Message: ' . $e->getMessage() );
					}
				}
			} else {
				$wpssw_inputoption = parent::wpssw_option( 'wpssw_inputoption' );
				if ( ! $wpssw_inputoption ) {
					$wpssw_inputoption = 'USER_ENTERED';
				}
				$wpssw_range       = trim( $wpssw_graphsheetname ) . '!A1';
				$wpssw_array       = array();
				$wpssw_array []    = 'There is no data to generate the graph';
				$wpssw_requestbody = self::$instance_api->valuerangeobject( array( $wpssw_array ) );
				$wpssw_params      = array( 'valueInputOption' => $wpssw_inputoption );
				$param             = self::$instance_api->setparamater( $wpssw_spreadsheetid, $wpssw_range, $wpssw_requestbody, $wpssw_params );
				$wpssw_response    = self::$instance_api->appendentry( $param );
			}
		}
		/**
		 * Get Month name from month number.
		 *
		 * @param int $month month number.
		 */
		public static function wpssw_get_month( $month ) {
			$months = array(
				1  => 'January',
				2  => 'February',
				3  => 'March',
				4  => 'April',
				5  => 'May',
				6  => 'June',
				7  => 'July',
				8  => 'August',
				9  => 'September',
				10 => 'October',
				11 => 'November',
				12 => 'December',
			);

			return isset( $months[ (int) $month ] ) ? $months[ (int) $month ] : '';
		}
		/**
		 * Regenerate Graph
		 */
		public static function wpssw_regenerate_graph() {

			$wpssw_order_spreadsheet_setting = WPSSW_Order::wpssw_check_order_spreadsheet_setting();
			if ( 'yes' !== (string) $wpssw_order_spreadsheet_setting ) {
				echo 'spreadsheetnotexist';
				die();
			}
			if ( ! isset( $_POST['sync_nonce_token'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['sync_nonce_token'] ) ), 'sync_nonce' )
			) {
				echo esc_html__( 'Sorry, your nonce did not verify.', 'wpssw' );
				die();
			}
			$wpssw_sheet          = isset( $_POST['sheet_id'] ) ? sanitize_text_field( wp_unslash( $_POST['sheet_id'] ) ) : '';
			$graph_type           = isset( $_POST['graph_type'] ) ? sanitize_text_field( wp_unslash( $_POST['graph_type'] ) ) : '';
			$wpssw_graphsheetname = '';
			if ( 'regenerate_total_graph' === (string) $wpssw_sheet ) {
				$wpssw_graphsheetname = 'Total Orders Graph';
			}
			if ( 'regenerate_sales_graph' === (string) $wpssw_sheet ) {
				$wpssw_graphsheetname = 'Sales Orders Graph';
			}
			if ( 'regenerate_product_graph' === (string) $wpssw_sheet ) {
				$wpssw_graphsheetname = 'Products Sold Graph';
			}
			if ( 'regenerate_customers_graph' === (string) $wpssw_sheet ) {
				$wpssw_graphsheetname = 'Total Customers Graph';
			}
			if ( 'regenerate_used_coupons_graph' === (string) $wpssw_sheet ) {
				$wpssw_graphsheetname = 'Total Used Coupons Graph';
			}
			try {
				$wpssw_spreadsheetid = parent::wpssw_option( 'wpssw_woocommerce_spreadsheet' );
				$spreadsheets_list   = self::$instance_api->get_spreadsheet_listing();
				if ( ! empty( $wpssw_spreadsheetid ) && ! array_key_exists( $wpssw_spreadsheetid, $spreadsheets_list ) ) {
					echo 'spreadsheetnotexist';
					die;
				} elseif ( ! parent::wpssw_check_sheet_exist( $wpssw_spreadsheetid, $wpssw_graphsheetname ) ) {
					echo 'sheetnotexist';
					die;
				}
				$wpssw_response            = self::$instance_api->get_sheet_listing( $wpssw_spreadsheetid );
				$wpssw_existingsheetsnames = array();
				$wpssw_charts_data         = array();
				foreach ( $wpssw_response->getSheets() as $wpssw_key => $wpssw_value ) {
					$wpssw_existingsheetsnames[ $wpssw_value['properties']['title'] ] = $wpssw_value['properties']['sheetId'];
					if ( $wpssw_value['charts'] ) {
						$wpssw_charts_data[ $wpssw_value['properties']['title'] ] = $wpssw_value['charts'];
					}
				}
				$wpssw_chartid       = array();
				$wpssw_graph_sheetid = $wpssw_existingsheetsnames[ $wpssw_graphsheetname ];
				if ( isset( $wpssw_charts_data[ $wpssw_graphsheetname ] ) && $wpssw_charts_data[ $wpssw_graphsheetname ] ) {
					$wpssw_chart_data = $wpssw_charts_data[ $wpssw_graphsheetname ];
					foreach ( $wpssw_chart_data as $chart_data ) {

						$chart_data_array = (array) $chart_data;
						$wpssw_chartid[]  = $chart_data_array['chartId'];
					}
				}
				$total_headers = 2;
				$last_column   = parent::wpssw_get_column_index( $total_headers );
				$requestbody   = self::$instance_api->clearobject();
				if ( isset( $wpssw_existingsheetsnames[ $wpssw_graphsheetname ] ) ) {
					try {
						$range                  = $wpssw_graphsheetname . '!A1:' . $last_column . '10000';
						$param                  = array();
						$param['spreadsheetid'] = $wpssw_spreadsheetid;
						$param['sheetname']     = $range;
						$param['requestbody']   = $requestbody;
						$response               = self::$instance_api->clear( $param );
						if ( ! empty( $wpssw_chartid ) ) {
							foreach ( $wpssw_chartid as $chart_id ) {
								$param                  = array();
								$param['spreadsheetid'] = $wpssw_spreadsheetid;
								$param['chart_ID']      = $chart_id;
								$wpssw_response         = self::$instance_api->deleteembeddedobject( $param );
							}
						}
					} catch ( Exception $e ) {
						echo esc_html( 'Message: ' . $e->getMessage() );
					}
				}
				self::wpssw_add_graph( $wpssw_graphsheetname, $graph_type );
			} catch ( Exception $e ) {
				echo esc_html( 'Message: ' . $e->getMessage() );
			}
			echo 'successful';
			die();
		}
	}
	new WPSSW_General();
endif;
