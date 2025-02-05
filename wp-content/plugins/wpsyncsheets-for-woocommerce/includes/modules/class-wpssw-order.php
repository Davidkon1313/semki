<?php
/**
 * Main WPSyncSheets_For_WooCommerce namespace.
 *
 * @since 1.0.0
 * @package wpsyncsheets-for-woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }
if ( ! class_exists( 'WPSSW_Order' ) ) :
	/**
	 * Class WPSSW_Order.
	 */
	class WPSSW_Order extends WPSSW_Setting {
		/**
		 * Instance of WPSSW_Google_API_Functions
		 *
		 * @var $instance_api
		 */
		protected static $instance_api = null;
		/**
		 * Initialization
		 */
		public static function init() {
			$wpssw_include = new WPSSW_Include_Action();
			$wpssw_include->wpssw_include_orderfield_hook();
			$wpssw_include->wpssw_include_order_hook();
			$wpssw_include->wpssw_include_order_ajax_hook();
			self::wpssw_google_api();
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
		 * Insert WPSyncSheets column at shop order page
		 *
		 * @param array $columns shop order columns array.
		 * @return array $columns
		 */
		public static function wpssw_shop_order_page_syncbtn_column( $columns ) {

			try {
				$wpssw_order_spreadsheet_setting = self::wpssw_check_order_spreadsheet_setting();
				if ( 'yes' === (string) $wpssw_order_spreadsheet_setting ) {
					$reordered_columns   = array();
					$wpssw_spreadsheetid = parent::wpssw_option( 'wpssw_woocommerce_spreadsheet' );
					$spreadsheets_list   = self::$instance_api->get_spreadsheet_listing();
					if ( ! self::$instance_api->checkcredenatials() || empty( $wpssw_spreadsheetid ) || ( ! empty( $wpssw_spreadsheetid ) && ! array_key_exists( $wpssw_spreadsheetid, $spreadsheets_list ) ) ) {
						return $columns;
					}
					// phpcs:ignore.
					if ( ( isset( $_GET['page'] ) && 'wc-orders' === sanitize_text_field( wp_unslash( $_GET['page'] ) ) && isset( $_GET['status'] ) && 'trash' === sanitize_text_field( wp_unslash( $_GET['status'] ) ) ) || ( isset( $_GET['post_status'] ) && 'trash' === sanitize_text_field( wp_unslash( $_GET['post_status'] ) ) ) ) {
						return $columns;
					}
					// Inserting columns to a specific location.
					foreach ( $columns as $key => $column ) {
						$reordered_columns[ $key ] = $column;
						if ( 'order_total' === (string) $key ) {
							// Inserting after "Total" column.
							$reordered_columns['wpssw_syncbtn_column'] = __( 'WPSyncSheets', 'wpssw' );
						}
					}
					return $reordered_columns;
				}
			} catch ( Exception $e ) {
				return $columns;
			}
			return $columns;
		}
		/**
		 * Insert Sync button in WPSyncSheets column at shop order page
		 *
		 * @param string $column shop order WPSyncSheets column.
		 * @param int    $post_id .
		 */
		public static function wpssw_shop_order_page_syncbtn( $column, $post_id ) {
			if ( 'wpssw_syncbtn_column' === (string) $column ) {
				echo '<a href="#" class="button wpssw_single_order_sync_btn">' . esc_html__( 'Click to Sync', 'wpssw' ) . '</a>
					<img src="' . esc_url( admin_url( 'images/spinner.gif' ) ) . '" class="syncbtnloader">';
			}
		}
		/**
		 * Insert Meta box in entry page
		 */
		public static function wpssw_add_syncbtn_meta_box() {
			$wpssw_order_spreadsheet_setting = self::wpssw_check_order_spreadsheet_setting();
			if ( 'yes' !== (string) $wpssw_order_spreadsheet_setting ) {
				return;
			}
			$wpssw_spreadsheetid = parent::wpssw_option( 'wpssw_woocommerce_spreadsheet' );
			$spreadsheets_list   = self::$instance_api->get_spreadsheet_listing();
			if ( ! self::$instance_api->checkcredenatials() || empty( $wpssw_spreadsheetid ) || ( ( ! empty( $wpssw_spreadsheetid ) && ! array_key_exists( $wpssw_spreadsheetid, $spreadsheets_list ) ) ) ) {
				return;
			}
			$hpos_order_enabled = parent::wpssw_check_hpos_order_setting_enabled();
			$screen             = $hpos_order_enabled ? wc_get_page_screen_id( 'shop-order' ) : 'shop_order';
			add_meta_box( 'wpssw_syncbtn_meta_box', 'WPSyncSheets', __CLASS__ . '::wpssw_syncbtn_meta_box_content', $screen, 'side', 'default', null );
		}

		/**
		 * Save IP Address, User Name, User Agent into order meta.
		 *
		 * @param int $order_id Order id.
		 */
		public static function wpssw_woocommerce_checkout_update_order_meta( $order_id ) {
			if ( ! $order_id ) {
				return;
			}

			$wpssw_user     = wp_get_current_user();
			$wpssw_username = '';
			if ( $wpssw_user && ! is_null( $wpssw_user ) && ! is_wp_error( $wpssw_user ) ) {
				$wpssw_user_name = $wpssw_user->data;
				$wpssw_username  = $wpssw_user_name->display_name;
			}

			if ( isset( $_SERVER['HTTP_CLIENT_IP'] ) && ! empty( sanitize_text_field( wp_unslash( $_SERVER['HTTP_CLIENT_IP'] ) ) ) ) {
				$wpssw_ip_address = sanitize_text_field( wp_unslash( $_SERVER['HTTP_CLIENT_IP'] ) );
			} elseif ( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) && ! empty( sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) ) ) {
				$wpssw_ip_address = sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) );
			} else {
				$wpssw_ip_address = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
			}

			$wpssw_user_agent   = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '';
			$hpos_order_enabled = parent::wpssw_check_hpos_order_setting_enabled();
			$wpssw_order        = wc_get_order( $order_id );
			if ( $hpos_order_enabled ) {
				$wpssw_saved_ip_address = $wpssw_order->get_meta( 'wpssw_ip_address', true );
				$wpssw_saved_user_agent = $wpssw_order->get_meta( 'wpssw_user_agent', true );
				$wpssw_saved_username   = $wpssw_order->get_meta( 'wpssw_username', true );
			} else {
				$wpssw_saved_ip_address = get_post_meta( $order_id, 'wpssw_ip_address', true );
				$wpssw_saved_user_agent = get_post_meta( $order_id, 'wpssw_user_agent', true );
				$wpssw_saved_username   = get_post_meta( $order_id, 'wpssw_username', true );
			}

			if ( ! empty( $wpssw_saved_ip_address ) ) {
				$wpssw_ip_address = $wpssw_saved_ip_address;
			}

			if ( ! empty( $wpssw_saved_user_agent ) ) {
				$wpssw_user_agent = $wpssw_saved_user_agent;
			}

			if ( ! empty( $wpssw_saved_username ) ) {
				$wpssw_username = $wpssw_saved_username;
			}

			if ( $hpos_order_enabled ) {
				$wpssw_order->update_meta_data( 'wpssw_ip_address', $wpssw_ip_address );
				$wpssw_order->update_meta_data( 'wpssw_user_agent', $wpssw_user_agent );
				$wpssw_order->update_meta_data( 'wpssw_username', $wpssw_username );
				$wpssw_order->save();
			} else {
				update_post_meta( $order_id, 'wpssw_ip_address', $wpssw_ip_address );
				update_post_meta( $order_id, 'wpssw_user_agent', $wpssw_user_agent );
				update_post_meta( $order_id, 'wpssw_username', $wpssw_username );
			}
		}
		/**
		 * Change order status
		 *
		 * @param int    $wpssw_order_id .
		 * @param string $wpssw_old_status .
		 * @param string $wpssw_new_status .
		 */
		public static function wpssw_woo_order_status_change_custom( $wpssw_order_id, $wpssw_old_status, $wpssw_new_status ) {

			$wpssw_order_spreadsheet_setting = self::wpssw_check_order_spreadsheet_setting();
			if ( 'yes' !== (string) $wpssw_order_spreadsheet_setting ) {
				return;
			}
			$wpssw_rsync = apply_filters( 'wpssw_realtime_sync', false );
			if ( $wpssw_rsync ) {
				return;
			}
			if ( ! self::$instance_api->checkcredenatials() ) {
				return;
			}
			$subscription = apply_filters( 'wpssw_check_renewal_subscription_order', false, $wpssw_order_id );
			if ( $subscription ) {
				return;
			}
			// @codingStandardsIgnoreStart.
			if ( ( isset( $_REQUEST['post'] ) && is_array( $_REQUEST['post'] ) && count( $_REQUEST['post'] ) > 0 && isset( $_REQUEST['paged'] ) && ! empty( sanitize_text_field( wp_unslash( $_REQUEST['paged'] ) ) ) ) || ( isset( $_REQUEST['doaction'] ) && 'undo' === sanitize_text_field( wp_unslash( $_REQUEST['doaction'] ) ) && isset( $_REQUEST['ids'] ) && ! empty( $_REQUEST['ids'] ) ) || ( isset( $_REQUEST['id'] ) && true === is_array( $_REQUEST['id'] ) && count( $_REQUEST['id'] ) > 1 ) ) {
				
				global $wpssw_multiple_order_data;
				$is_id_values = false;
				if ( isset( $_REQUEST['doaction'] ) && 'undo' === sanitize_text_field( wp_unslash( $_REQUEST['doaction'] ) ) && isset( $_REQUEST['ids'] ) && ! empty( $_REQUEST['ids'] ) ) {
					$changed_posts = explode( ',', sanitize_text_field( wp_unslash( $_REQUEST['ids'] ) ) );
				}else if( isset( $_REQUEST['id'] ) && true === is_array( $_REQUEST['id'] ) && !empty( $_REQUEST['id'] ) ){
					$changed_posts = array_map( 'sanitize_text_field', wp_unslash( $_REQUEST['id'] ) );
					$is_id_values = true;
				} else {
					$changed_posts = array_map( 'sanitize_text_field', wp_unslash( $_REQUEST['post'] ) );
				}
				
				if (false === $is_id_values && isset( $_REQUEST['action'] ) && ( 'trash' !== sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) && 'untrash' !== sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) ) ) {
					sort( $changed_posts );
				}
				
				// @codingStandardsIgnoreEnd.
				$changed_post_last_index = count( $changed_posts ) - 1;
				if ( true === $is_id_values ) {
					if ( (int) $wpssw_order_id === (int) $changed_posts[ $changed_post_last_index ] ) {
						$wpssw_multiple_order_data = array();
					}
				} else {
					if ( (int) $wpssw_order_id === (int) $changed_posts[0] ) {
						$wpssw_multiple_order_data = array();
					}
				}
				if ( self::wpssw_check_product_category( $wpssw_order_id ) ) {
					return;
				}
				$wpssw_multiple_order_data[ $wpssw_order_id ] = array(
					'old_staus'  => $wpssw_old_status,
					'new_status' => $wpssw_new_status,
				);
				if ( true === $is_id_values ) {
					if ( (int) $wpssw_order_id === (int) $changed_posts[0] ) {
						self::wpssw_multiple_order_update( $wpssw_multiple_order_data );
					}
				} elseif ( (int) $wpssw_order_id === (int) $changed_posts[ $changed_post_last_index ] ) {
					self::wpssw_multiple_order_update( $wpssw_multiple_order_data );
				}
				return;
			} else {
				if ( isset( $GLOBALS['wpssw_multiple_order_data'] ) ) {
					unset( $GLOBALS['wpssw_multiple_order_data'] );
				}
			}

			$wpssw_status_array  = wc_get_order_statuses();
			$wpssw_spreadsheetid = parent::wpssw_option( 'wpssw_woocommerce_spreadsheet' );

			$wpssw_spreadsheets_list = self::$instance_api->get_spreadsheet_listing();
			if ( ! array_key_exists( $wpssw_spreadsheetid, $wpssw_spreadsheets_list ) ) {
				return;
			}
			$response                  = self::$instance_api->get_sheet_listing( $wpssw_spreadsheetid );
			$wpssw_existingsheetsnames = self::$instance_api->get_sheet_list( $response );

			$wpssw_sheets = array_filter( (array) parent::wpssw_option( 'wpssw_sheets' ) );
			if ( ! $wpssw_sheets ) {
				$wpssw_sheets = self::wpssw_prepare_sheets();
			}

			$wpssw_old_staus_name           = '';
			$wpssw_sheetid                  = '';
			$wpssw_sheetname                = '';
			$wpssw_status_array['wc-trash'] = 'Trash';
			foreach ( $wpssw_status_array as $wpssw_key => $wpssw_val ) {
				$wpssw_status      = substr( $wpssw_key, strpos( $wpssw_key, '-' ) + 1 );
				$wpssw_orderstatus = str_replace( '-', ' ', $wpssw_status );
				$wpssw_orderstatus = ucwords( $wpssw_orderstatus ) . ' Orders';
				if ( $wpssw_status === $wpssw_old_status ) {
					if ( array_key_exists( $wpssw_orderstatus, $wpssw_sheets ) && isset( $wpssw_existingsheetsnames[ $wpssw_sheets[ $wpssw_orderstatus ] ] ) ) {
						$wpssw_old_staus_name = $wpssw_sheets[ $wpssw_orderstatus ];
						$wpssw_sheetid        = $wpssw_existingsheetsnames[ $wpssw_old_staus_name ];
					}
				}
				if ( $wpssw_status === $wpssw_new_status ) {
					if ( array_key_exists( $wpssw_orderstatus, $wpssw_sheets ) && isset( $wpssw_existingsheetsnames[ $wpssw_sheets[ $wpssw_orderstatus ] ] ) ) {
						$wpssw_sheetname = $wpssw_sheets[ $wpssw_orderstatus ];
					}
				}
			}

			/* Event settings and producs name as sheets function start */
			$wpssw_eventsheetnames           = array();
			$wpssw_event_spreadsheet_setting = parent::wpssw_option( 'wpssw_event_spreadsheet_setting' );
			$wpssw_product_names_as_sheet    = parent::wpssw_option( 'product_names_as_sheet' );
			if ( ( parent::wpssw_is_event_calender_ticket_active() && 'yes' === (string) $wpssw_event_spreadsheet_setting ) || 'yes' === (string) $wpssw_product_names_as_sheet ) {

				$wpssw_order                = wc_get_order( $wpssw_order_id );
				$wpssw_items                = $wpssw_order->get_items();
				$wpssw_additionalsheetnames = array();
				if ( ( parent::wpssw_is_event_calender_ticket_active() && 'yes' === (string) $wpssw_event_spreadsheet_setting ) ) {
					foreach ( $wpssw_items as $wpssw_item ) {

						$wpssw_ticket_meta = get_post_meta( $wpssw_item['product_id'] );
						if ( isset( $wpssw_ticket_meta['_tribe_wooticket_for_event'] ) ) {
							$wpssw_eventid = $wpssw_ticket_meta['_tribe_wooticket_for_event'][0];
							$term_obj_list = get_the_terms( $wpssw_eventid, 'tribe_events_cat' );
							if ( ! $term_obj_list ) {
								$term_obj_list = array();
							}
							foreach ( $term_obj_list as $term ) {
								if ( isset( $wpssw_existingsheetsnames[ $term->name ] ) ) {
									$wpssw_additionalsheetnames[] = $term->name;
								}
							}
						}
					}
				}
				if ( 'yes' === (string) $wpssw_product_names_as_sheet ) {
					$wpssw_productsheets_list = parent::wpssw_option( 'wpssw_productname_sheets' );
					if ( ! $wpssw_productsheets_list ) {
						$wpssw_productsheets_list = array();
					}
					foreach ( $wpssw_items as $wpssw_item ) {
						if ( array_key_exists( $wpssw_item['product_id'], $wpssw_productsheets_list ) ) {
							$wpssw_additionalsheetnames[] = $wpssw_productsheets_list[ $wpssw_item['product_id'] ];
						}
					}
				}
				$wpssw_additionalsheetnames = array_values( array_unique( $wpssw_additionalsheetnames ) );

				foreach ( $wpssw_additionalsheetnames as $wpssw_sheet_name ) {
					if ( 'trash' === (string) $wpssw_new_status ) {
						$wpssw_sheet_id = $wpssw_existingsheetsnames[ $wpssw_sheet_name ];
						$wpssw_sheet    = "'" . $wpssw_sheet_name . "'!A:A";
						$wpssw_allentry = self::$instance_api->get_row_list( $wpssw_spreadsheetid, $wpssw_sheet );
						$wpssw_data     = $wpssw_allentry->getValues();
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
						$order_keys     = array_keys( parent::wpssw_convert_int( $wpssw_data ), (int) $wpssw_order_id, true );

						if ( $wpssw_sheet_id && ! empty( $order_keys ) ) {
							$param              = self::$instance_api->prepare_param( $wpssw_sheet_id, $order_keys[0], $order_keys[0] + count( $order_keys ) );
							$deleterequestarray = self::$instance_api->deleteDimensionrequests( $param, 'ROWS' );
						}
						try {
							if ( ! empty( $deleterequestarray ) ) {
								$param                  = array();
								$param['spreadsheetid'] = $wpssw_spreadsheetid;
								$param['requestarray']  = $deleterequestarray;
								self::$instance_api->updatebachrequests( $param );
							}
						} catch ( Exception $e ) {
							echo esc_html( 'Message: ' . $e->getMessage() );
						}
					} else {
						self::wpssw_insert_data_into_sheet( $wpssw_order_id, $wpssw_sheet_name, 0, $wpssw_old_staus_name );
					}
				}
			}
			/* Event settings and products name as sheets function end */
			if ( ! empty( $wpssw_sheetname ) ) {
				if ( 'trash' === (string) $wpssw_new_status && isset( $wpssw_sheets['Trash Orders'] ) && $wpssw_sheetname === $wpssw_sheets['Trash Orders'] ) {
					self::wpssw_insert_data_into_sheet( $wpssw_order_id, $wpssw_sheetname, 0, $wpssw_old_staus_name, $wpssw_new_status );
				} else {
					self::wpssw_insert_data_into_sheet( $wpssw_order_id, $wpssw_sheetname, 0, $wpssw_old_staus_name );
				}
			}

			if ( ! empty( $wpssw_old_staus_name ) && ! empty( $wpssw_sheetid ) ) {
				self::wpssw_move_order( $wpssw_order_id, $wpssw_sheetid, $wpssw_old_staus_name );
			}

			if ( array_key_exists( 'All Orders', $wpssw_sheets ) && 'trash' !== (string) $wpssw_new_status ) {
				$wpssw_is_allowed = apply_filters( 'wpssw_order_status_allow', true, $wpssw_sheetname );

				if ( $wpssw_is_allowed ) {
					$wpssw_sheetname = $wpssw_sheets['All Orders'];
					self::wpssw_all_orders( $wpssw_order_id, $wpssw_sheetname );
				}
			}
		}
		/**
		 * Process Update of shop orders
		 *
		 * @param int    $wpssw_post_id .
		 * @param object $wpssw_post .
		 */
		public static function wpssw_wc_woocommerce_process_post_meta( $wpssw_post_id, $wpssw_post ) {
			$wpssw_order_spreadsheet_setting = self::wpssw_check_order_spreadsheet_setting();
			if ( 'yes' !== (string) $wpssw_order_spreadsheet_setting ) {
				return;
			}
			// @codingStandardsIgnoreStart.
			$wpssw_order_status = isset( $_REQUEST['order_status'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['order_status'] ) ) : '';
			$wpssw_post_status  = isset( $_REQUEST['post_status'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['post_status'] ) ) : '';
			// @codingStandardsIgnoreEnd.
			if ( false === strpos( $wpssw_post_status, 'wc-' ) && 0 === strpos( $wpssw_order_status, 'wc-' ) ) {
				$wpssw_post_status = 'wc-' . $wpssw_post_status;
			}
			if ( $wpssw_order_status !== $wpssw_post_status ) {
				return;
			}
			if ( ! self::$instance_api->checkcredenatials() ) {
				return;
			}

			if ( self::wpssw_check_product_category( $wpssw_post_id ) ) {
				return;
			}
			$subscription = apply_filters( 'wpssw_check_renewal_subscription_order', false, $wpssw_post_id );
			if ( $subscription ) {
				return;
			}
			self::wpssw_wc_woocommerce_update_post_meta( $wpssw_post_id, $wpssw_post );
		}
		/**
		 * Added v4.6
		 * Check if orders are of in selected product categories from orders settings or not.
		 *
		 * @param int $wpssw_order_id .
		 */
		public static function wpssw_check_product_category( $wpssw_order_id ) {
			$wpssw_category_filter     = parent::wpssw_option( 'wpssw_category_filter' );
			$wpssw_category_filter_ids = parent::wpssw_option( 'wpssw_category_filter_ids' );
			if ( $wpssw_category_filter && $wpssw_order_id && $wpssw_category_filter_ids && is_array( $wpssw_category_filter_ids ) ) {
				$wpssw_order = wc_get_order( $wpssw_order_id );
				$wpssw_items = $wpssw_order->get_items();
				foreach ( $wpssw_items as $wpssw_id => $wpssw_item ) {
					$wpssw_product_id = $wpssw_item['product_id'] ? $wpssw_item['product_id'] : '';
					if ( $wpssw_product_id > 0 ) {
						$product_cats_ids  = wc_get_product_term_ids( $wpssw_product_id, 'product_cat' );
						$is_allowed        = array_intersect( $product_cats_ids, $wpssw_category_filter_ids );
						$wpssw_ticket_meta = get_post_meta( $wpssw_item['product_id'] );
						if ( isset( $wpssw_ticket_meta['_tribe_wooticket_for_event'] ) ) {
							$wpssw_eventid = $wpssw_ticket_meta['_tribe_wooticket_for_event'][0];
							if ( parent::wpssw_is_event_calender_ticket_active() ) {
								$term_obj_list = get_the_terms( $wpssw_eventid, 'tribe_events_cat' );
								if ( ! empty( $term_obj_list ) && is_array( $term_obj_list ) ) {
									foreach ( $term_obj_list as $term ) {
										$is_allowed[] = $term->term_id;
									}
								} elseif ( $wpssw_eventid ) {
									$is_allowed[] = $wpssw_eventid;
								}
							}
							if ( $wpssw_eventid ) {
								$is_allowed[] = $wpssw_eventid;
							}
						}
						if ( ! empty( $is_allowed ) ) {
							return false;
						}
					}
				}
				return true;
			} else {
				return false;
			}
		}
		/**
		 * Update shop orders
		 *
		 * @param int    $wpssw_post_id .
		 * @param object $wpssw_post .
		 */
		public static function wpssw_wc_woocommerce_update_post_meta( $wpssw_post_id, $wpssw_post ) {
			$wpssw_order_spreadsheet_setting = self::wpssw_check_order_spreadsheet_setting();
			$parameter_type                  = '';
			if ( is_a( $wpssw_post, 'WP_Post' ) ) {
				$parameter_type = 'post';
			} elseif ( is_a( $wpssw_post, 'WC_Order' ) ) {
				$parameter_type = 'wc-order';
			} else {
				return;
			}
			if ( 'yes' !== (string) $wpssw_order_spreadsheet_setting || ( 'post' === $parameter_type && 'shop_order' !== (string) $wpssw_post->post_type ) ) {
				return;
			}
			// @codingStandardsIgnoreStart.
			$wpssw_order_status = isset( $_REQUEST['order_status'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['order_status'] ) ) : '';
			$wpssw_post_status  = isset( $_REQUEST['post_status'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['post_status'] ) ) : '';
			// @codingStandardsIgnoreEnd.

			if ( false === strpos( $wpssw_post_status, 'wc-' ) && 0 === strpos( $wpssw_order_status, 'wc-' ) ) {
				$wpssw_post_status = 'wc-' . $wpssw_post_status;
			}
			if ( $wpssw_order_status !== $wpssw_post_status ) {
				return;
			}

			$wpssw_spreadsheetid     = parent::wpssw_option( 'wpssw_woocommerce_spreadsheet' );
			$wpssw_spreadsheets_list = self::$instance_api->get_spreadsheet_listing();
			if ( ! array_key_exists( $wpssw_spreadsheetid, $wpssw_spreadsheets_list ) ) {
				return;
			}
			if ( ! empty( $wpssw_spreadsheetid ) ) {
				$wpssw_order = wc_get_order( $wpssw_post_id );

				$wpssw_items        = $wpssw_order->get_items();
				$wpssw_headers_name = parent::wpssw_option( 'wpssw_sheet_headers_list' );
				$wpssw_sheets       = array_filter( (array) parent::wpssw_option( 'wpssw_sheets' ) );
				if ( ! $wpssw_sheets ) {
					$wpssw_sheets = self::wpssw_prepare_sheets();
				}
				if ( parent::wpssw_is_event_calender_ticket_active() ) {
					$wpssw_woo_event_headers = parent::wpssw_option( 'wpssw_woo_event_headers' );
					if ( ! is_array( $wpssw_woo_event_headers ) ) {
						$wpssw_woo_event_headers = array();
					}
					$wpssw_headers_name = array_merge( $wpssw_headers_name, $wpssw_woo_event_headers );
				}
				$wpssw_header_type = self::wpssw_is_productwise();
				$wpssw_inputoption = parent::wpssw_option( 'wpssw_inputoption' );
				if ( ! $wpssw_inputoption ) {
					$wpssw_inputoption = 'USER_ENTERED';
				}

				$wpssw_values = self::wpssw_make_value_array( 'update', $wpssw_order->get_id() );
				do_action( 'wpssw_update_order', $wpssw_order->get_id(), $wpssw_values, $wpssw_order_status );
				$wpssw_response            = self::$instance_api->get_sheet_listing( $wpssw_spreadsheetid );
				$wpssw_existingsheetsnames = self::$instance_api->get_sheet_list( $wpssw_response );
				$wpssw_sheetname           = '';
				$wpssw_status              = '';

				$wpssw_status_array = wc_get_order_statuses();
				foreach ( $wpssw_status_array as $wpssw_key => $wpssw_val ) {
					if ( (string) $wpssw_key === $wpssw_order_status ) {
						$wpssw_status = substr( $wpssw_key, strpos( $wpssw_key, '-' ) + 1 );
						$wpssw_status = str_replace( '-', ' ', $wpssw_status );
						$wpssw_status = ucwords( $wpssw_status ) . ' Orders';
						if ( array_key_exists( $wpssw_status, $wpssw_sheets ) && isset( $wpssw_existingsheetsnames[ $wpssw_sheets[ $wpssw_status ] ] ) ) {
							$wpssw_sheetname = $wpssw_sheets[ $wpssw_status ];
							$wpssw_sheetid   = $wpssw_existingsheetsnames[ $wpssw_sheetname ];
						}
					}
				}

				/* Event settings and producs name as sheets function start */
				$wpssw_eventsheetnames           = array();
				$wpssw_event_spreadsheet_setting = parent::wpssw_option( 'wpssw_event_spreadsheet_setting' );
				$wpssw_product_names_as_sheet    = parent::wpssw_option( 'product_names_as_sheet' );
				if ( ( parent::wpssw_is_event_calender_ticket_active() && 'yes' === (string) $wpssw_event_spreadsheet_setting ) || 'yes' === (string) $wpssw_product_names_as_sheet ) {
					$wpssw_response             = self::$instance_api->get_sheet_listing( $wpssw_spreadsheetid );
					$wpssw_existingsheetsnames  = self::$instance_api->get_sheet_list( $wpssw_response );
					$wpssw_items                = $wpssw_order->get_items();
					$wpssw_additionalsheetnames = array();
					if ( ( parent::wpssw_is_event_calender_ticket_active() && 'yes' === (string) $wpssw_event_spreadsheet_setting ) ) {
						foreach ( $wpssw_items as $wpssw_item ) {

							$wpssw_ticket_meta = get_post_meta( $wpssw_item['product_id'] );
							if ( isset( $wpssw_ticket_meta['_tribe_wooticket_for_event'] ) ) {
								$wpssw_eventid = $wpssw_ticket_meta['_tribe_wooticket_for_event'][0];
								$term_obj_list = get_the_terms( $wpssw_eventid, 'tribe_events_cat' );
								if ( ! $term_obj_list ) {
									$term_obj_list = array();
								}
								foreach ( $term_obj_list as $term ) {
									if ( isset( $wpssw_existingsheetsnames[ $term->name ] ) ) {
										$wpssw_additionalsheetnames[] = $term->name;
									}
								}
							}
						}
					}
					if ( 'yes' === (string) $wpssw_product_names_as_sheet ) {
						$wpssw_productsheets_list = parent::wpssw_option( 'wpssw_productname_sheets' );
						if ( ! $wpssw_productsheets_list ) {
							$wpssw_productsheets_list = array();
						}
						foreach ( $wpssw_items as $wpssw_item ) {
							if ( array_key_exists( $wpssw_item['product_id'], $wpssw_productsheets_list ) ) {
								$wpssw_additionalsheetnames[] = $wpssw_productsheets_list[ $wpssw_item['product_id'] ];
							}
						}
					}
					$wpssw_additionalsheetnames = array_unique( $wpssw_additionalsheetnames );
					foreach ( $wpssw_additionalsheetnames as  $wpsswsheetname ) {
						self::wpssw_insert_data_into_sheet( $wpssw_order->get_id(), $wpsswsheetname, 0 );
					}
				}
				/* Event settings and producs name as sheets function end */
				if ( ! empty( $wpssw_sheetname ) ) {
					$wpssw_values      = self::wpssw_set_static_values( $wpssw_order->get_id(), $wpssw_sheetname, $wpssw_values );
					$wpssw_rangetofind = $wpssw_sheetname . '!A:A';
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
					$wpssw_num         = array_search( (int) $wpssw_order->get_id(), parent::wpssw_convert_int( $wpssw_data ), true );
					if ( $wpssw_num > 0 ) {
						$wpssw_rownum = $wpssw_num + 1;
						// Add or Remove Row at spreadsheet.
						$wpssw_ordrow   = 0;
						$wpssw_notempty = 0;
						end( $wpssw_data );
						$wpssw_lastelement = key( $wpssw_data );
						reset( $wpssw_data );
						$wpssw_data_count = count( $wpssw_data );
						for ( $i = $wpssw_rownum; $i < $wpssw_data_count; $i++ ) {
							if ( (int) $wpssw_data[ $i ] === (int) $wpssw_order->get_id() ) {
								$wpssw_ordrow++;
								if ( (int) $wpssw_lastelement === (int) $i ) {
									$wpssw_ordrow++;
								}
							} else {
								if ( (int) $wpssw_lastelement === (int) $i ) {
									$wpssw_notempty = 1;
									if ( $wpssw_ordrow > 0 ) {
										$wpssw_ordrow++;
									}
								} else {
									$wpssw_ordrow++;
								}
								break;
							}
						}
						$wpssw_samerow = 0;
						if ( 0 === (int) $wpssw_ordrow ) {
							$wpssw_samerow = 1;
						}
						if ( 1 === (int) $wpssw_samerow && $wpssw_header_type && 0 === (int) $wpssw_notempty ) {
							$wpssw_alphabet   = range( 'A', 'Z' );
							$wpssw_alphaindex = '';
							$wpssw_is_id      = array_search( 'Product ID', $wpssw_headers_name, true );
							if ( $wpssw_is_id ) {
								$wpssw_alphaindex = $wpssw_alphabet[ $wpssw_is_id + 1 ];
							} else {
								$wpssw_is_name = array_search( 'Product Name', $wpssw_headers_name, true );
								if ( $wpssw_is_name ) {
									$wpssw_alphaindex = $wpssw_alphabet[ $wpssw_is_name + 1 ];
								}
							}
							if ( '' !== (string) $wpssw_alphaindex ) {
								$wpssw_rangetofind = $wpssw_sheetname . '!' . $wpssw_alphaindex . $wpssw_rownum . ':' . $wpssw_alphaindex;
								$wpssw_allentry    = self::$instance_api->get_row_list( $wpssw_spreadsheetid, $wpssw_rangetofind );
								$wpssw_data        = $wpssw_allentry->getValues();

								$wpssw_data = array_map(
									function( $wpssw_element ) {
										if ( isset( $wpssw_element['0'] ) ) {
											return $wpssw_element['0'];
										} else {
											return '';
										}
									},
									$wpssw_data
								);
								if ( ( count( $wpssw_values ) < count( $wpssw_data ) ) ) {
									$wpssw_ordrow  = count( $wpssw_data );
									$wpssw_samerow = 0;
								}
							}
						}
						if ( 1 === (int) $wpssw_notempty && 0 === (int) $wpssw_ordrow ) {
							$wpssw_samerow = 0;
							$wpssw_ordrow  = 1;
						}
						if ( ( count( $wpssw_values ) > (int) $wpssw_ordrow ) && 0 === (int) $wpssw_samerow ) {// Insert blank row into spreadsheet.
							$wpssw_endindex      = count( $wpssw_values ) - (int) $wpssw_ordrow;
							$wpssw_endindex      = (int) $wpssw_endindex + (int) $wpssw_rownum;
							$param               = array();
							$param               = self::$instance_api->prepare_param( $wpssw_sheetid, $wpssw_rownum, $wpssw_endindex );
							$wpssw_inherit_style = parent::wpssw_option( 'wpssw_order_inherit_style' );
							if ( 'no' === (string) $wpssw_inherit_style ) {
								$wpssw_batchupdaterequest = self::$instance_api->insertdimensionobject( $param, false );
							} else {
								$wpssw_batchupdaterequest = self::$instance_api->insertdimensionobject( $param, true );
							}
							$requestobject                  = array();
							$requestobject['spreadsheetid'] = $wpssw_spreadsheetid;
							$requestobject['requestbody']   = $wpssw_batchupdaterequest;
							$wpssw_response                 = self::$instance_api->formatsheet( $requestobject );
						} elseif ( count( $wpssw_values ) < (int) $wpssw_ordrow && 0 === (int) $wpssw_samerow ) {// Remove extra row from spreadhseet.
							$wpssw_endindex         = (int) $wpssw_ordrow - count( $wpssw_values );
							$wpssw_endindex         = (int) $wpssw_endindex + (int) $wpssw_rownum;
							$param                  = array();
							$param                  = self::$instance_api->prepare_param( $wpssw_sheetid, $wpssw_rownum, $wpssw_endindex );
							$deleterequest          = self::$instance_api->deleteDimensionrequests( $param, 'ROWS' );
							$param                  = array();
							$param['spreadsheetid'] = $wpssw_spreadsheetid;
							$param['requestarray']  = $deleterequest;
							$wpssw_response         = self::$instance_api->updatebachrequests( $param );
						}
						// End of add- remove row at spreadsheet.
						$wpssw_rangetoupdate = $wpssw_sheetname . '!A' . $wpssw_rownum;
						$wpssw_requestbody   = self::$instance_api->valuerangeobject( $wpssw_values );
						$wpssw_params        = array( 'valueInputOption' => $wpssw_inputoption ); // USER_ENTERED.
						$param               = array();
						$param               = self::$instance_api->setparamater( $wpssw_spreadsheetid, $wpssw_rangetoupdate, $wpssw_requestbody, $wpssw_params );
						$wpssw_response      = self::$instance_api->updateentry( $param );
					} else {
						self::wpssw_insert_data_into_sheet( (int) $wpssw_order->get_id(), $wpssw_sheetname, 0 );
					}
				}

				// All Order.
				if ( array_key_exists( 'All Orders', $wpssw_sheets ) ) {
					$wpssw_is_allowed = apply_filters( 'wpssw_order_status_allow', true, $wpssw_status );
					if ( $wpssw_is_allowed ) {
						$wpssw_sheetname = $wpssw_sheets['All Orders'];
						self::wpssw_all_orders( $wpssw_order->get_id(), $wpssw_sheetname );
					}
				}
			}
		}
		/**
		 * Get all orders
		 *
		 * @param int    $wpssw_order_id .
		 * @param string $wpssw_sheetname .
		 * @param string $new_order_status .
		 */
		public static function wpssw_all_orders( $wpssw_order_id, $wpssw_sheetname, $new_order_status = '' ) {
			if ( self::wpssw_check_product_category( $wpssw_order_id ) ) {
				return;
			}
			$wpssw_inputoption = parent::wpssw_option( 'wpssw_inputoption' );
			if ( ! $wpssw_inputoption ) {
				$wpssw_inputoption = 'USER_ENTERED';
			}
			$wpssw_order         = wc_get_order( $wpssw_order_id );
			$wpssw_spreadsheetid = parent::wpssw_option( 'wpssw_woocommerce_spreadsheet' );
			if ( ! parent::wpssw_check_sheet_exist( $wpssw_spreadsheetid, $wpssw_sheetname ) ) {
				return;
			}
			$wpssw_headers_name = stripslashes_deep( parent::wpssw_option( 'wpssw_sheet_headers_list' ) );
			$wpssw_header_type  = self::wpssw_is_productwise();
			$wpssw_sheet        = "'" . $wpssw_sheetname . "'!A:A";
			$wpssw_allentry     = self::$instance_api->get_row_list( $wpssw_spreadsheetid, $wpssw_sheet );
			$wpssw_data         = $wpssw_allentry->getValues();
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
			$wpssw_num          = array_search( (int) $wpssw_order_id, parent::wpssw_convert_int( $wpssw_data ), true );
			$hpos_order_enabled = parent::wpssw_check_hpos_order_setting_enabled();
			if ( $wpssw_num > 0 ) {
				$wpssw_values = self::wpssw_make_value_array( 'insert', $wpssw_order_id );
				if ( ( $hpos_order_enabled || ( ! $hpos_order_enabled && 'trash' !== (string) $wpssw_order->get_status() ) ) && 'trash' === (string) $new_order_status ) {
					$wpssw_woo_selections = $wpssw_headers_name;
					array_unshift( $wpssw_woo_selections, 'Order Id' );
					$wpssw_order_status_key = array_search( 'Order Status', $wpssw_woo_selections, true );
					if ( false !== $wpssw_order_status_key ) {
						if ( $wpssw_header_type && 'yes' === (string) parent::wpssw_option( 'wpssw_repeat_checkbox' ) ) {
							foreach ( $wpssw_values as &$prdarray ) {
								$prdarray[ $wpssw_order_status_key ] = 'Trash';
							}
						} else {
							$wpssw_values[0][ $wpssw_order_status_key ] = 'Trash';
						}
					}
				}
				$wpssw_values              = self::wpssw_set_static_values( $wpssw_order_id, $wpssw_sheetname, $wpssw_values );
				$wpssw_response            = self::$instance_api->get_sheet_listing( $wpssw_spreadsheetid );
				$wpssw_existingsheetsnames = self::$instance_api->get_sheet_list( $wpssw_response );
				$wpssw_sheetid             = $wpssw_existingsheetsnames[ $wpssw_sheetname ];
				$wpssw_rownum              = $wpssw_num + 1;
				// Add or Remove Row at spreadsheet.
				$wpssw_ordrow   = 0;
				$wpssw_notempty = 0;
				end( $wpssw_data );
				$wpssw_lastelement = key( $wpssw_data );
				reset( $wpssw_data );
				$wpssw_data_count = count( $wpssw_data );
				for ( $i = $wpssw_rownum; $i < $wpssw_data_count; $i++ ) {
					if ( (int) $wpssw_data[ $i ] === (int) $wpssw_order->get_id() ) {
						$wpssw_ordrow++;
						if ( (int) $wpssw_lastelement === (int) $i ) {
							$wpssw_ordrow++;
						}
					} else {
						if ( (int) $wpssw_lastelement === (int) $i ) {
							$wpssw_notempty = 1;
							if ( $wpssw_ordrow > 0 ) {
								$wpssw_ordrow++;
							}
						} else {
							$wpssw_ordrow++;
						}
						break;
					}
				}
				$wpssw_samerow = 0;
				if ( 0 === (int) $wpssw_ordrow ) {
					$wpssw_samerow = 1;
				}
				if ( 1 === (int) $wpssw_samerow && $wpssw_header_type && 0 === (int) $wpssw_notempty ) {
					$wpssw_alphabet   = range( 'A', 'Z' );
					$wpssw_alphaindex = '';
					$wpssw_is_id      = array_search( 'Product ID', $wpssw_headers_name, true );
					if ( $wpssw_is_id ) {
						$wpssw_alphaindex = $wpssw_alphabet[ $wpssw_is_id + 1 ];
					} else {
						$wpssw_is_name = array_search( 'Product Name', $wpssw_headers_name, true );
						if ( $wpssw_is_name ) {
							$wpssw_alphaindex = $wpssw_alphabet[ $wpssw_is_name + 1 ];
						}
					}
					if ( '' !== (string) $wpssw_alphaindex ) {
						$wpssw_rangetofind = $wpssw_sheetname . '!' . $wpssw_alphaindex . $wpssw_rownum . ':' . $wpssw_alphaindex;
						$wpssw_allentry    = self::$instance_api->get_row_list( $wpssw_spreadsheetid, $wpssw_rangetofind );
						$wpssw_data        = $wpssw_allentry->getValues();
						$wpssw_data        = array_map(
							function( $wpssw_element ) {
								if ( isset( $wpssw_element['0'] ) ) {
									return $wpssw_element['0'];
								} else {
									return '';
								}
							},
							$wpssw_data
						);
						if ( ( count( $wpssw_values ) < count( $wpssw_data ) ) ) {
							$wpssw_ordrow  = count( $wpssw_data );
							$wpssw_samerow = 0;
						}
					}
				}
				if ( 1 === (int) $wpssw_notempty && 0 === (int) $wpssw_ordrow ) {
					$wpssw_samerow = 0;
					$wpssw_ordrow  = 1;
				}
				if ( ( count( $wpssw_values ) > (int) $wpssw_ordrow ) && 0 === (int) $wpssw_samerow ) {// Insert blank row into spreadsheet.
					$wpssw_endindex      = count( $wpssw_values ) - (int) $wpssw_ordrow;
					$wpssw_endindex      = (int) $wpssw_endindex + (int) $wpssw_rownum;
					$param               = self::$instance_api->prepare_param( $wpssw_sheetid, $wpssw_rownum, $wpssw_endindex );
					$wpssw_inherit_style = parent::wpssw_option( 'wpssw_order_inherit_style' );
					if ( 'no' === (string) $wpssw_inherit_style ) {
						$wpssw_batchupdaterequest = self::$instance_api->insertdimensionobject( $param, false );
					} else {
						$wpssw_batchupdaterequest = self::$instance_api->insertdimensionobject( $param, true );
					}
					$requestobject                  = array();
					$requestobject['spreadsheetid'] = $wpssw_spreadsheetid;
					$requestobject['requestbody']   = $wpssw_batchupdaterequest;
					$wpssw_response                 = self::$instance_api->formatsheet( $requestobject );
				} elseif ( count( $wpssw_values ) < (int) $wpssw_ordrow && 0 === (int) $wpssw_samerow ) {// Remove extra row from spreadhseet.
					$wpssw_endindex         = (int) $wpssw_ordrow - count( $wpssw_values );
					$wpssw_endindex         = (int) $wpssw_endindex + (int) $wpssw_rownum;
					$param                  = array();
					$param                  = self::$instance_api->prepare_param( $wpssw_sheetid, $wpssw_rownum, $wpssw_endindex );
					$deleterequest          = self::$instance_api->deleteDimensionrequests( $param, 'ROWS' );
					$param                  = array();
					$param['spreadsheetid'] = $wpssw_spreadsheetid;
					$param['requestarray']  = $deleterequest;
					$wpssw_response         = self::$instance_api->updatebachrequests( $param );
				}
				// End of add- remove row at spreadsheet.
				$wpssw_rangetoupdate = $wpssw_sheetname . '!A' . $wpssw_rownum;
				$wpssw_requestbody   = self::$instance_api->valuerangeobject( $wpssw_values );
				$wpssw_params        = array( 'valueInputOption' => $wpssw_inputoption ); // USER_ENTERED.
				$param               = array();
				$param               = self::$instance_api->setparamater( $wpssw_spreadsheetid, $wpssw_rangetoupdate, $wpssw_requestbody, $wpssw_params );
				$wpssw_response      = self::$instance_api->updateentry( $param );
			} else {
				self::wpssw_insert_data_into_sheet( $wpssw_order_id, $wpssw_sheetname, 0 );
			}
		}
		/**
		 * Insert Order data into sheet provided by $wpssw_sheetname.
		 *
		 * @param int    $wpssw_order_id .
		 * @param string $wpssw_sheetname .
		 * @param int    $wpssw_flag .
		 * @param string $wpssw_old_staus_name .
		 * @param string $wpssw_new_staus_name .
		 */
		public static function wpssw_insert_data_into_sheet( $wpssw_order_id, $wpssw_sheetname, $wpssw_flag = 0, $wpssw_old_staus_name = '', $wpssw_new_staus_name = '' ) {

			if ( ! self::$instance_api->checkcredenatials() ) {
				return;
			}
			if ( self::wpssw_check_product_category( $wpssw_order_id ) ) {
				return;
			}
			$wpssw_spreadsheetid = parent::wpssw_option( 'wpssw_woocommerce_spreadsheet' );
			if ( ! parent::wpssw_check_sheet_exist( $wpssw_spreadsheetid, $wpssw_sheetname ) ) {
				return;
			}
			$wpssw_inputoption = parent::wpssw_option( 'wpssw_inputoption' );
			if ( ! $wpssw_inputoption ) {
				$wpssw_inputoption = 'USER_ENTERED';
			}
			$wpssw_order = wc_get_order( $wpssw_order_id );

			$wpssw_headers_name = parent::wpssw_option( 'wpssw_sheet_headers_list' );
			if ( parent::wpssw_is_event_calender_ticket_active() ) {
				$wpssw_woo_event_headers = parent::wpssw_option( 'wpssw_woo_event_headers' );
				if ( ! is_array( $wpssw_woo_event_headers ) ) {
					$wpssw_woo_event_headers = array();
				}
				$wpssw_headers_name = array_merge( $wpssw_headers_name, $wpssw_woo_event_headers );
			}
			$wpssw_header_type = self::wpssw_is_productwise();
			$order_ascdesc     = parent::wpssw_option( 'wpssw_order_ascdesc' );
			if ( ! empty( $wpssw_spreadsheetid ) ) {
				$wpssw_prdarray = self::wpssw_make_value_array( 'insert', $wpssw_order_id, $wpssw_sheetname );
				$wpssw_sheets   = array_filter( (array) parent::wpssw_option( 'wpssw_sheets' ) );
				if ( 'trash' === (string) $wpssw_new_staus_name && isset( $wpssw_sheets['Trash Orders'] ) && $wpssw_sheetname === $wpssw_sheets['Trash Orders'] ) {
					$wpssw_woo_selections = $wpssw_headers_name;
					array_unshift( $wpssw_woo_selections, 'Order Id' );
					$wpssw_order_status_key = array_search( 'Order Status', $wpssw_woo_selections, true );
					if ( false !== $wpssw_order_status_key ) {
						if ( $wpssw_header_type && 'yes' === (string) parent::wpssw_option( 'wpssw_repeat_checkbox' ) ) {
							foreach ( $wpssw_prdarray as &$prdarray ) {
								$prdarray[ $wpssw_order_status_key ] = 'Trash';
							}
						} else {
							$wpssw_prdarray[0][ $wpssw_order_status_key ] = 'Trash';
						}
					}
				}

				$wpssw_prdarray = self::wpssw_set_static_values( $wpssw_order_id, $wpssw_old_staus_name, $wpssw_prdarray );
				do_action( 'wpssw_insert_new_order', $wpssw_order_id, $wpssw_prdarray, $wpssw_sheetname );
				if ( 1 === (int) $wpssw_flag ) {
					return $wpssw_prdarray;
				}
				if ( 0 === (int) $wpssw_flag ) {
					$wpssw_values              = $wpssw_prdarray;
					$wpssw_sheet               = "'" . $wpssw_sheetname . "'!A:A";
					$wpssw_allentry            = self::$instance_api->get_row_list( $wpssw_spreadsheetid, $wpssw_sheet );
					$wpssw_data                = $wpssw_allentry->getValues();
					$wpssw_data                = array_map(
						function( $wpssw_element ) {
							if ( isset( $wpssw_element['0'] ) && is_numeric( $wpssw_element['0'] ) ) {
								return $wpssw_element['0'];
							} else {
								return '';
							}
						},
						$wpssw_data
					);
					$response                  = self::$instance_api->get_sheet_listing( $wpssw_spreadsheetid );
					$wpssw_existingsheetsnames = self::$instance_api->get_sheet_list( $response );
					$wpssw_sheetid             = $wpssw_existingsheetsnames[ $wpssw_sheetname ];
					$wpssw_append              = 0;
					$wpssw_desc_append         = 0;
					$wpssw_event_data_append   = 0;
					$wpssw_num                 = array_search( (int) $wpssw_order_id, parent::wpssw_convert_int( $wpssw_data ), true );

					if ( 0 === (int) $wpssw_event_data_append ) {
						$wpssw_requestbody = self::$instance_api->valuerangeobject( $wpssw_values );
						$wpssw_params      = array( 'valueInputOption' => $wpssw_inputoption );
						if ( $wpssw_num > 0 ) {
							$wpssw_event_data_append = 1;
							self::wpssw_insert_event_data_into_sheet( $wpssw_order_id, $wpssw_sheetname );
						}
					}

					if ( $wpssw_num < 1 ) {
						$highest_order_id = max( $wpssw_data );

						if ( $wpssw_header_type ) {
							$items_to_keep  = self::wpssw_get_specific_items( $wpssw_order, $wpssw_sheetname );
							$wpssw_prdcount = ( is_array( $items_to_keep ) && ! empty( $items_to_keep ) ) ? count( $items_to_keep ) : count( $wpssw_order->get_items() );
						}
						if ( ( $highest_order_id ) && $wpssw_order_id < $highest_order_id && 0 === (int) $wpssw_event_data_append ) {
							foreach ( $wpssw_data as $wpssw_key => $wpssw_value ) {
								if ( ! empty( $wpssw_value ) ) {

									if ( ( (int) $wpssw_order_id < (int) $wpssw_value ) && 'descorder' !== (string) $order_ascdesc && 0 === (int) $wpssw_event_data_append && $wpssw_num < 1 ) {
										$wpssw_append            = 1;
										$wpssw_event_data_append = 1;
										if ( $wpssw_header_type ) {
											$wpssw_startindex = $wpssw_key;
											$wpssw_endindex   = $wpssw_key + $wpssw_prdcount;
										} else {
											$wpssw_startindex = $wpssw_key;
											$wpssw_endindex   = $wpssw_key + 1;
										}
										$param               = array();
										$param               = self::$instance_api->prepare_param( $wpssw_sheetid, $wpssw_startindex, $wpssw_endindex );
										$wpssw_inherit_style = parent::wpssw_option( 'wpssw_order_inherit_style' );
										if ( 'no' === (string) $wpssw_inherit_style ) {
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
										$wpssw_requestbody              = self::$instance_api->valuerangeobject( $wpssw_values );
										$wpssw_params                   = array( 'valueInputOption' => $wpssw_inputoption );
										$param                          = array();
										$param                          = self::$instance_api->setparamater( $wpssw_spreadsheetid, $wpssw_rangetoupdate, $wpssw_requestbody, $wpssw_params );

										$wpssw_response = self::$instance_api->updateentry( $param );
										break;
									}
									if ( 'descorder' === (string) $order_ascdesc ) {
										if ( ( (int) $wpssw_order_id > (int) $wpssw_value ) && (int) $wpssw_value > 0 && 0 === (int) $wpssw_event_data_append && $wpssw_num < 1 ) {
											$wpssw_append            = 1;
											$wpssw_desc_append       = 1;
											$wpssw_event_data_append = 1;
											if ( $wpssw_header_type ) {
												$wpssw_startindex = $wpssw_key;
												$wpssw_endindex   = $wpssw_key + $wpssw_prdcount;
											} else {
												$wpssw_startindex = $wpssw_key;
												$wpssw_endindex   = $wpssw_key + 1;
											}
											$param               = array();
											$param               = self::$instance_api->prepare_param( $wpssw_sheetid, $wpssw_startindex, $wpssw_endindex );
											$wpssw_inherit_style = parent::wpssw_option( 'wpssw_order_inherit_style' );
											if ( 'no' === (string) $wpssw_inherit_style ) {
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
											$wpssw_requestbody              = self::$instance_api->valuerangeobject( $wpssw_values );
											$wpssw_params                   = array( 'valueInputOption' => $wpssw_inputoption );
											$param                          = array();
											$param                          = self::$instance_api->setparamater( $wpssw_spreadsheetid, $wpssw_rangetoupdate, $wpssw_requestbody, $wpssw_params );

											$wpssw_response = self::$instance_api->updateentry( $param );
											break;
										}
									}
								}
							}
						}
					}
					if ( 0 === (int) $wpssw_desc_append && 'descorder' === (string) $order_ascdesc && 0 === (int) $wpssw_event_data_append && $wpssw_num < 1 ) {
						// If there is no sheet_id available, leave the sheet_id field blank. Otherwise, if there is a sheet_id, leave the sheet_name field blank.
						$wpssw_startindex    = 1;
						$wpssw_endindex      = 1 + count( $wpssw_values );
						$param               = array();
						$param               = self::$instance_api->prepare_param( $wpssw_sheetid, $wpssw_startindex, $wpssw_endindex );
						$wpssw_inherit_style = parent::wpssw_option( 'wpssw_order_inherit_style' );
						if ( 'no' === (string) $wpssw_inherit_style ) {
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
						$wpssw_requestbody              = self::$instance_api->valuerangeobject( $wpssw_values );
						$wpssw_params                   = array( 'valueInputOption' => $wpssw_inputoption );
						$param                          = array();
						$param                          = self::$instance_api->setparamater( $wpssw_spreadsheetid, $wpssw_rangetoupdate, $wpssw_requestbody, $wpssw_params );

						$wpssw_response = self::$instance_api->updateentry( $param );

						$wpssw_append            = 1;
						$wpssw_event_data_append = 1;

					}

					if ( 0 === (int) $wpssw_append && 0 === (int) $wpssw_event_data_append ) {
						$wpssw_isupdated         = 0;
						$wpssw_event_data_append = 1;
						$wpssw_requestbody       = self::$instance_api->valuerangeobject( $wpssw_values );
						$wpssw_params            = array( 'valueInputOption' => $wpssw_inputoption );
						if ( count( $wpssw_data ) > 1 ) {
							$wpssw_num = array_search( (int) $wpssw_order_id, parent::wpssw_convert_int( $wpssw_data ), true );
							if ( $wpssw_num > 0 ) {
								$wpssw_rangetoupdate = $wpssw_sheetname . '!A' . ( $wpssw_num + 1 );
								$param               = array();
								$param               = self::$instance_api->setparamater( $wpssw_spreadsheetid, $wpssw_rangetoupdate, $wpssw_requestbody, $wpssw_params );
								$wpssw_response      = self::$instance_api->updateentry( $param );
							} else {
								// If there is no sheet_id available, leave the sheet_id field blank. Otherwise, if there is a sheet_id, leave the sheet_name field blank.
								$wpssw_inherit_params = array(
									'sheet_id'       => $wpssw_sheetid,
									'sheet_name'     => '',
									'start_index'    => count( $wpssw_data ),
									'end_index'      => count( $wpssw_data ) + count( $wpssw_values ),
									'spreadsheet_id' => $wpssw_spreadsheetid,
								);

								parent::wpssw_inherit_row_format_style( 'order', $wpssw_inherit_params );

								$wpssw_rangetoupdate = $wpssw_sheetname . '!A' . ( count( $wpssw_data ) + 1 );
								$param               = array();
								$param               = self::$instance_api->setparamater( $wpssw_spreadsheetid, $wpssw_rangetoupdate, $wpssw_requestbody, $wpssw_params );
								$wpssw_response      = self::$instance_api->appendentry( $param );

							}
							$wpssw_isupdated = 1;
						}
						if ( 0 === (int) $wpssw_isupdated ) {

							// If there is no sheet_id available, leave the sheet_id field blank. Otherwise, if there is a sheet_id, leave the sheet_name field blank.
							$wpssw_inherit_params = array(
								'sheet_id'       => $wpssw_sheetid,
								'sheet_name'     => '',
								'start_index'    => count( $wpssw_data ),
								'end_index'      => count( $wpssw_data ) + count( $wpssw_values ),
								'spreadsheet_id' => $wpssw_spreadsheetid,
							);

							parent::wpssw_inherit_row_format_style( 'order', $wpssw_inherit_params );
							$rangetofind    = $wpssw_sheetname . '!A' . ( count( $wpssw_data ) + 1 );
							$param          = self::$instance_api->setparamater( $wpssw_spreadsheetid, $rangetofind, $wpssw_requestbody, $wpssw_params );
							$wpssw_response = self::$instance_api->appendentry( $param );
						}
					}
				}
			}
		}
		/**
		 * Equal value array.
		 *
		 * @param array $temp Value array.
		 * @param array $wpssw_items Item array.
		 *
		 * @return array
		 */
		public static function wpssw_make_equal( $temp, $wpssw_items ) {
			$wpssw_is_repeat = parent::wpssw_option( 'wpssw_repeat_checkbox' );
			$wpssw_new_array = array();
			$value           = self::wpssw_array_flatten( $temp );
			if ( 'yes' === (string) $wpssw_is_repeat ) {
				for ( $i = 0; $i < $wpssw_items; $i++ ) {
					$wpssw_new_array[] = isset( $value[0] ) ? array( $value[0] ) : array();
				}
			} else {
				for ( $i = 0; $i < $wpssw_items; $i++ ) {
					$wpssw_new_array[] = isset( $value[ $i ] ) ? array( $value[ $i ] ) : array( 0 => '' );
				}
			}
			return $wpssw_new_array;
		}
		/**
		 * Mearge arrays
		 *
		 * @param array $array1 .
		 * @param array $array2 .
		 *
		 * @return array
		 */
		public static function wpssw_array_merge_recursive_distinct( &$array1, &$array2 ) {
			$merged = $array1;
			foreach ( $array2 as $key => &$value ) {
				if ( is_array( $value ) && isset( $merged[ $key ] ) && is_array( $merged[ $key ] ) ) {
					$merged[ $key ] = array_merge( $merged[ $key ], $value );
				} else {
					$merged[ $key ] = $value;
				}
			}
			return $merged;
		}
		/**
		 * Prepare array value of order data to insert into sheet.
		 *
		 * @param string $wpssw_operation operation to perfom on sheet.
		 * @param int    $wpssw_order_id .
		 * @param string $wpssw_sheet_name .
		 */
		public static function wpssw_make_value_array( $wpssw_operation = 'insert', $wpssw_order_id = 0, $wpssw_sheet_name = '' ) {
			$wpssw_order                = wc_get_order( $wpssw_order_id );
			$wpssw_inputoption          = parent::wpssw_option( 'wpssw_inputoption' );
			$wpssw_static_header_values = stripslashes_deep( parent::wpssw_option( 'wpssw_static_header_values' ) );
			$wpssw_custom_value         = array();
			$wpssw_static_header_name   = array();
			if ( ! empty( $wpssw_static_header_values ) ) {
				foreach ( $wpssw_static_header_values as $wpssw_static_header_value ) {
					if ( strpos( $wpssw_static_header_value, ',(static_header),' ) ) {
						$wpssw_static_header_value = str_replace( ',(static_header),', ',', $wpssw_static_header_value );
						$wpssw_custom_value[]      = explode( ',', $wpssw_static_header_value );
					}
				}
				if ( ! empty( $wpssw_custom_value ) ) {
					$wpssw_static_header_name = array_column( $wpssw_custom_value, 0 );
				}
			}
			if ( ! $wpssw_inputoption ) {
				$wpssw_inputoption = 'USER_ENTERED';
			}
			$wpssw_filterd = array();
			$wpssw_include = new WPSSW_Include_Action();
			$wpssw_include->wpssw_include_order_compatibility_files();
			$wpssw_header_type = self::wpssw_is_productwise();
			$wpssw_headers     = apply_filters( 'wpsyncsheets_order_headers', array() );

			$wpssw_headers_name = stripslashes_deep( parent::wpssw_option( 'wpssw_sheet_headers_list' ) );
			$wpssw_items        = $wpssw_order->get_items();
			/**
			 * Get custom headers
			 */
			$wpssw_extra_headers     = array();
			$wpssw_extra_headers     = apply_filters( 'woosheets_custom_headers', array() );
			$wpssw_temp_headers      = array();
			$wpssw_value             = array();
			$wpssw_prdarray          = array();
			$wpssw_value[0]          = $wpssw_order_id;
			$wpssw_headers_name      = stripslashes_deep( parent::wpssw_option( 'wpssw_sheet_headers_list' ) );
			$wpssw_woo_event_headers = array();
			$wpssw_eventheaders      = array();
			if ( parent::wpssw_is_event_calender_ticket_active() ) {
				$wpssw_woo_event_headers = parent::wpssw_option( 'wpssw_woo_event_headers' );
				if ( ! is_array( $wpssw_woo_event_headers ) ) {
					$wpssw_woo_event_headers = array();
				}
				$wpssw_headers_name = array_merge( $wpssw_headers_name, $wpssw_woo_event_headers );
				$wpssw_include->wpssw_include_event_compatibility_files();
				$wpssw_eventheaders = apply_filters( 'wpsyncsheets_event_headers', array() );
				if ( ! is_array( $wpssw_eventheaders ) ) {
					$wpssw_eventheaders = array();
				}
				$wpssw_headers = array_merge( $wpssw_headers, $wpssw_eventheaders );
			}

			$wpssw_product_headers = stripslashes_deep( parent::wpssw_option( 'wpssw_product_sheet_headers_list' ) );

			$wpssw_header_type              = self::wpssw_is_productwise();
			$wpssw_headers['WPSSW_Default'] = parent::wpssw_array_flatten( $wpssw_headers['WPSSW_Default'] );
			$wpssw_classarray               = array();
			$wpssw_headers_name_count       = count( $wpssw_headers_name );
			for ( $i = 0; $i < $wpssw_headers_name_count; $i++ ) {
				if ( in_array( $wpssw_headers_name[ $i ], $wpssw_static_header_name, true ) ) {
					$wpssw_classarray[ $wpssw_headers_name[ $i ] ] = 'WPSSW_Default';
					continue;
				}
				if ( in_array( $wpssw_headers_name[ $i ], $wpssw_product_headers, true ) ) {
					$wpssw_classarray[ $wpssw_headers_name[ $i ] ] = 'WPSSW_Default';
					continue;
				}
				$wpssw_classarray[ $wpssw_headers_name[ $i ] ] = parent::wpssw_find_class( $wpssw_headers, $wpssw_headers_name[ $i ] );
			}
			$wpssw_order_row = array();
			$wpssw_temp      = array();
			$wpssw_items     = $wpssw_order->get_items();
			$items_to_keep   = array();
			if ( $wpssw_sheet_name ) {
				$items_to_keep = self::wpssw_get_specific_items( $wpssw_order, $wpssw_sheet_name );
			}

			if ( $wpssw_header_type ) {
				$wpssw_rcount = 0;
				foreach ( $wpssw_items as $wpssw_item ) {
					$wpssw_order_row[ $wpssw_rcount ][] = $wpssw_order_id;
					$wpssw_temp[ $wpssw_rcount ][]      = '';
					$wpssw_rcount++;
				}
			}
			if ( ! $wpssw_header_type ) {
				$wpssw_order_row   = array();
				$wpssw_order_row[] = $wpssw_order_id;
			}

			foreach ( $wpssw_classarray as $headername => $classname ) {
				$temp = array();
				if ( ! empty( $classname ) ) {
					$header_value = $classname::get_value( $headername, $wpssw_order, 'insert', $wpssw_custom_value, $wpssw_product_headers );

					if ( is_array( $header_value ) ) {
						$temp = array_chunk( $header_value, 1 );

						if ( ! empty( $items_to_keep ) ) {
							$flipped_key_arr = array_flip( $items_to_keep );
							if ( $wpssw_header_type ) {
								if ( ! isset( $flipped_key_arr[0] ) && isset( $temp[0] ) && 1 === count( $temp ) ) {
									$flipped_arr_keys = array_keys( $flipped_key_arr );
									for ( $j = 1;$j <= $flipped_arr_keys[0];$j++ ) {
										$temp[ $j ] = $temp[0];
									}
								}
							}
						}
					}
					if ( empty( $temp ) ) {
						$temp = $wpssw_temp;
					}
				} else {
					$temp = $wpssw_temp;
				}
				if ( $wpssw_header_type ) {

					if ( count( $temp ) !== count( $wpssw_items ) ) {
						$temp = self::wpssw_make_equal( $temp, count( $wpssw_items ) );
					}

					$wpssw_order_row = self::wpssw_array_merge_recursive_distinct( $wpssw_order_row, $temp );
				} else {
					$wpssw_val = parent::wpssw_array_flatten( $temp );
					if ( ! empty( array_filter( $wpssw_val ) ) ) {
						$wpssw_order_row[] = implode( ',', array_filter( $wpssw_val ) );
					} else {
						$wpssw_order_row[] = '';
					}
				}
			}
			if ( $wpssw_header_type ) {

				if ( ! empty( $items_to_keep ) && ! empty( $wpssw_order_row ) ) {
					$wpssw_order_row = array_values( array_intersect_key( $wpssw_order_row, array_flip( $items_to_keep ) ) );
				}
				foreach ( $wpssw_order_row as $wpssw_arrykey => $wpssw_valarray ) {
					$wpssw_order_row[ $wpssw_arrykey ] = self::wpssw_order_clean_array( $wpssw_valarray );
				}
			} else {

				$wpssw_order_row = self::wpssw_order_clean_array( $wpssw_order_row );
				$wpssw_order_row = array( $wpssw_order_row );
			}
			$wpssw_order_row = apply_filters( 'woosheets_values', $wpssw_order_row );

			return $wpssw_order_row;
		}
		/**
		 * Uses the WooCommerce options API to save settings via the @see woocommerce_update_options() function.
		 *
		 * @uses woocommerce_update_options()
		 * @uses self::wpssw_get_settings()
		 */
		public static function wpssw_update_settings() {

			if ( ! isset( $_POST['wpssw_general_settings'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wpssw_general_settings'] ) ), 'save_general_settings' ) ) {
				echo esc_html__( 'Sorry, your nonce did not verify.', 'wpssw' );
				die();
			}

			if ( isset( $_POST['order_settings_checkbox'] ) ) {
				parent::wpssw_update_option( 'wpssw_order_spreadsheet_setting', 'yes' );

				if ( isset( $_POST['order_inherit_style'] ) ) {
					$wpssw_order_inherit_style = sanitize_text_field( wp_unslash( $_POST['order_inherit_style'] ) );
					parent::wpssw_update_option( 'wpssw_order_inherit_style', $wpssw_order_inherit_style );
				} else {
					parent::wpssw_update_option( 'wpssw_order_inherit_style', 'none' );
				}
				$wpssw_spreadsheetid = self::wpssw_create_sheet( $_POST );
				if ( isset( $_POST['header_fields'] ) ) {
					$wpssw_header        = array();
					$wpssw_header_custom = array();
					if ( isset( $_POST['prdassheetheaders'] ) && isset( $_POST['wpssw_append_after'] ) && in_array(
						$_POST['wpssw_append_after'],
						array_map(
							function( $key ) {
								return str_replace( ' ', '-', strtolower( $key ) ); },
							array_map( 'sanitize_text_field', wp_unslash( $_POST['header_fields'] ) )
						),
						true
					) ) {
						$flag = 0;
						foreach ( array_map( 'sanitize_text_field', wp_unslash( $_POST['header_fields'] ) ) as $headers ) {
							$wpssw_header[]        = $headers;
							$wpssw_header_custom[] = isset( $_POST['header_fields_custom'][ $flag ] ) ? sanitize_text_field( wp_unslash( $_POST['header_fields_custom'][ $flag ] ) ) : '';
							if ( str_replace( ' ', '-', strtolower( $headers ) ) === str_replace( ' ', '-', strtolower( sanitize_text_field( wp_unslash( $_POST['wpssw_append_after'] ) ) ) ) && isset( $_POST['product_header_fields'] ) ) {
								foreach ( array_map( 'sanitize_text_field', wp_unslash( $_POST['product_header_fields'] ) ) as $prd_header ) {
									$wpssw_header[] = $prd_header;
								}
								if ( isset( $_POST['product_header_fields_custom'] ) ) {
									foreach ( array_map( 'sanitize_text_field', wp_unslash( $_POST['product_header_fields_custom'] ) ) as $prd_header ) {
										$wpssw_header_custom[] = $prd_header;
									}
								}
							}
							$flag++;
						}
					} else {
						if ( isset( $_POST['prdassheetheaders'] ) && isset( $_POST['product_header_fields'] ) && is_array( sanitize_text_field( wp_unslash( $_POST['product_header_fields'] ) ) ) && isset( $_POST['prdassheetheaders'] ) ) {
							$wpssw_header        = array_merge( sanitize_text_field( wp_unslash( $_POST['header_fields'] ) ), sanitize_text_field( wp_unslash( $_POST['product_header_fields'] ) ) );
							$wpssw_header_custom = array_merge( sanitize_text_field( wp_unslash( $_POST['header_fields_custom'] ) ), sanitize_text_field( wp_unslash( $_POST['product_header_fields_custom'] ) ) );
						} else {
							$wpssw_header        = array_map( 'sanitize_text_field', wp_unslash( $_POST['header_fields'] ) );
							$wpssw_header_custom = array_map( 'sanitize_text_field', wp_unslash( $_POST['header_fields_custom'] ) );
						}
					}
					if ( isset( $_POST['prdassheetheaders'] ) ) {
						parent::wpssw_update_option( 'wpssw_prdassheetheaders', sanitize_text_field( wp_unslash( $_POST['prdassheetheaders'] ) ) );
						parent::wpssw_update_option( 'wpssw_append_after', sanitize_text_field( wp_unslash( $_POST['wpssw_append_after'] ) ) );
						if ( isset( $_POST['product_header_fields'] ) ) {
							$wpssw_product_headers = array_map( 'sanitize_text_field', stripslashes_deep( $_POST['product_header_fields'] ) );
							parent::wpssw_update_option( 'wpssw_product_sheet_headers_list', $wpssw_product_headers );
						}
						if ( isset( $_POST['product_header_fields_custom'] ) ) {
							$product_header_fields_custom = array_map( 'sanitize_text_field', stripslashes_deep( $_POST['product_header_fields_custom'] ) );
							parent::wpssw_update_option( 'wpssw_product_sheet_headers_list_custom', $product_header_fields_custom );
						}
					} else {
						parent::wpssw_update_option( 'wpssw_prdassheetheaders', '' );
						parent::wpssw_update_option( 'wpssw_append_after', '' );
						$wpssw_product_headers = array();
						parent::wpssw_update_option( 'wpssw_product_sheet_headers_list', $wpssw_product_headers );
						parent::wpssw_update_option( 'wpssw_product_sheet_headers_list_custom', $wpssw_product_headers );
					}
					if ( isset( $_POST['category_filter'] ) ) {
						$wpssw_category_filter = sanitize_text_field( wp_unslash( $_POST['category_filter'] ) );
						$productcat_filter     = isset( $_POST['productcat_filter'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['productcat_filter'] ) ) : '';
						parent::wpssw_update_option( 'wpssw_category_filter', $wpssw_category_filter );
						parent::wpssw_update_option( 'wpssw_category_filter_ids', $productcat_filter );
					} else {
						parent::wpssw_update_option( 'wpssw_category_filter', '' );
						parent::wpssw_update_option( 'wpssw_category_filter_ids', '' );
					}
					$wpssw_my_headers = stripslashes_deep( $wpssw_header );
					parent::wpssw_update_option( 'wpssw_sheet_headers_list', $wpssw_my_headers );
					$wpssw_mycustom_headers = stripslashes_deep( $wpssw_header_custom );
					parent::wpssw_update_option( 'wpssw_sheet_headers_list_custom', $wpssw_mycustom_headers );
					// Append after dropdown value array.
					if ( isset( $_POST['header_fields'] ) ) {
						$keys                     = array_map(
							function( $key ) {
								return str_replace( ' ', '-', strtolower( $key ) );
							},
							array_map( 'sanitize_text_field', wp_unslash( $_POST['header_fields'] ) )
						);
						$wpssw_append_after_array = array_combine( $keys, array_map( 'sanitize_text_field', wp_unslash( $_POST['header_fields_custom'] ) ) );
						parent::wpssw_update_option( 'wpssw_append_after_array', $wpssw_append_after_array );
					}
					woocommerce_update_options( self::wpssw_get_settings() );
					parent::wpssw_update_option( 'wpssw_woocommerce_spreadsheet', $wpssw_spreadsheetid );

					$wpssw_event_spreadsheet_setting = parent::wpssw_option( 'wpssw_event_spreadsheet_setting' );
					$wpssw_event_spreadsheet_id      = '';
					if ( 'yes' === (string) $wpssw_event_spreadsheet_setting ) {
						$wpssw_event_spreadsheet_id = $wpssw_spreadsheetid;
					}
					parent::wpssw_update_option( 'wpssw_event_spreadsheet_id', $wpssw_event_spreadsheet_id );

					if ( isset( $_POST['header_format'] ) ) {
						$wpssw_header_format = sanitize_text_field( wp_unslash( $_POST['header_format'] ) );
						parent::wpssw_update_option( 'wpssw_header_format', $wpssw_header_format );
					}
					if ( isset( $_POST['repeat_checkbox'] ) ) {
						parent::wpssw_update_option( 'wpssw_repeat_checkbox', 'yes' );
					} else {
						parent::wpssw_update_option( 'wpssw_repeat_checkbox', 'no' );
					}
					if ( isset( $_POST['product_names_as_sheet'] ) ) {
						parent::wpssw_update_option( 'product_names_as_sheet', 'yes' );
					} else {
						parent::wpssw_update_option( 'product_names_as_sheet', '' );
					}

					if ( isset( $_POST['import_order_checkbox'] ) ) {
						parent::wpssw_update_option( 'wpssw_order_import', 1 );
					} else {
						parent::wpssw_update_option( 'wpssw_order_import', '' );
					}
					if ( isset( $_POST['insert_order_checkbox'] ) ) {
						parent::wpssw_update_option( 'wpssw_order_insert', 1 );
					} else {
						parent::wpssw_update_option( 'wpssw_order_insert', '' );
					}
					if ( isset( $_POST['update_order_checkbox'] ) ) {
						parent::wpssw_update_option( 'wpssw_order_update', 1 );
					} else {
						parent::wpssw_update_option( 'wpssw_order_update', '' );
					}
					if ( isset( $_POST['delete_order_checkbox'] ) ) {
						parent::wpssw_update_option( 'wpssw_order_delete', 1 );
					} else {
						parent::wpssw_update_option( 'wpssw_order_delete', '' );
					}
					if ( isset( $_POST['exclude_order_status_from_notes'] ) ) {
						parent::wpssw_update_option( 'wpssw_exclude_order_status_from_notes', 'yes' );
					} else {
						parent::wpssw_update_option( 'wpssw_exclude_order_status_from_notes', 'no' );
					}

					/*
					* Update Sheetname Option Value.
					*/
					$wpssw_sheet_names = isset( $_POST['wpssw_sheets'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['wpssw_sheets'] ) ) : array();
					if ( isset( $_POST['wpssw_customsheets'] ) && ! empty( $_POST['wpssw_customsheets'] ) ) {
						$wpssw_defaultnames            = array_merge( $wpssw_sheet_names, array_map( 'sanitize_text_field', wp_unslash( $_POST['wpssw_customsheets'] ) ) );
						$wpssw_sheet_customnames       = isset( $_POST['wpssw_sheets_custom'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['wpssw_sheets_custom'] ) ) : array();
						$wpssw_customsheet_customnames = isset( $_POST['wpssw_customsheets_custom'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['wpssw_customsheets_custom'] ) ) : array();
						$wpssw_editednames             = array_merge( $wpssw_sheet_customnames, $wpssw_customsheet_customnames );
						$wpssw_orderstatus             = array_combine( $wpssw_defaultnames, $wpssw_editednames );
						parent::wpssw_update_option( 'wpssw_sheets', $wpssw_orderstatus );
					} else {
						$wpssw_defaultnames = $wpssw_sheet_names;
						$wpssw_editednames  = isset( $_POST['wpssw_sheets_custom'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['wpssw_sheets_custom'] ) ) : $wpssw_sheet_names;
						$wpssw_orderstatus  = array_combine( $wpssw_defaultnames, $wpssw_editednames );
						parent::wpssw_update_option( 'wpssw_sheets', $wpssw_orderstatus );
					}

					/*
					* Update Static Headers
					*/
					if ( isset( $_POST['header_fields_static'] ) && is_array( $_POST['header_fields_static'] ) ) {
						$wpssw_static_header = array_map( 'sanitize_text_field', wp_unslash( $_POST['header_fields_static'] ) );
						parent::wpssw_update_option( 'wpssw_static_header', $wpssw_static_header );
					}
					if ( isset( $_POST['wpssw_static_header_values'] ) && is_array( $_POST['wpssw_static_header_values'] ) ) {
						$wpssw_static_header_values = array_map( 'sanitize_text_field', wp_unslash( $_POST['wpssw_static_header_values'] ) );
						parent::wpssw_update_option( 'wpssw_static_header_values', $wpssw_static_header_values );
					}
					if ( isset( $_POST['order_ascdesc'] ) ) {
						$order_ascdesc = sanitize_text_field( wp_unslash( $_POST['order_ascdesc'] ) );
						parent::wpssw_update_option( 'wpssw_order_ascdesc', $order_ascdesc );
					}
				}
			} else {
				parent::wpssw_update_option( 'wpssw_order_spreadsheet_setting', 'no' );
				$_POST['scheduling_enable'] = 0;
			}

			/* Schedule Auto Sync */
			if ( isset( $_POST['scheduling_enable'] ) ) {
				self::wpssw_update_schedule_data( $_POST );
			}
			// phpcs:ignore.
			return;

		}
		/**
		 * Update sync schedule data.
		 *
		 * @param array $data .
		 */
		public static function wpssw_update_schedule_data( $data = array() ) {
			if ( ! isset( $_POST['wpssw_general_settings'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wpssw_general_settings'] ) ), 'save_general_settings' ) ) {
				echo esc_html__( 'Sorry, your nonce did not verify.', 'wpssw' );
				die();
			}
			$wpssw_schedule_enable = isset( $_POST['scheduling_enable'] ) ? sanitize_text_field( wp_unslash( $_POST['scheduling_enable'] ) ) : '';
			parent::wpssw_update_option( 'wpssw_scheduling_enable', $wpssw_schedule_enable );
			if ( 1 === (int) $wpssw_schedule_enable ) {

				$wpssw_scheduling_run_on = parent::wpssw_option( 'wpssw_scheduling_run_on' );
				$scheduling_run_on       = isset( $_POST['scheduling_run_on'] ) ? sanitize_text_field( wp_unslash( $_POST['scheduling_run_on'] ) ) : '';
				if ( 'recurrence' === (string) $scheduling_run_on ) {
					parent::wpssw_update_option( 'wpssw_scheduling_weekly_days', '' );
					parent::wpssw_update_option( 'wpssw_scheduling_date', '' );
					parent::wpssw_update_option( 'wpssw_scheduling_time', '' );
				} elseif ( 'weekly' === (string) $scheduling_run_on ) {
					parent::wpssw_update_option( 'wpssw_schedule_recurrence', '' );
					parent::wpssw_update_option( 'wpssw_scheduling_date', '' );
					parent::wpssw_update_option( 'wpssw_scheduling_time', '' );
				} elseif ( 'onetime' === (string) $scheduling_run_on ) {
					parent::wpssw_update_option( 'wpssw_scheduling_weekly_days', '' );
					parent::wpssw_update_option( 'wpssw_schedule_recurrence', '' );
				}

				if ( (string) $wpssw_scheduling_run_on !== (string) $scheduling_run_on ) {
					self::wpssw_remove_cron_job();
				}

				parent::wpssw_update_option( 'wpssw_scheduling_run_on', $scheduling_run_on );
				if ( 'recurrence' === (string) $scheduling_run_on ) {
					$wpssw_interval            = array(
						'ten_minutes'    => 'ten_minutes',
						'thirty_minutes' => 'thirty_minutes',
						'once_daily'     => 'daily',
						'twice_daily'    => 'twicedaily',
						'fifteen_days'   => 'fifteen_days',
						'monthly'        => 'monthly',
					);
					$schedule_recurrence       = isset( $_POST['schedule_recurrence'] ) ? sanitize_text_field( wp_unslash( $_POST['schedule_recurrence'] ) ) : '';
					$wpssw_schedule_recurrence = parent::wpssw_option( 'wpssw_schedule_recurrence' );
					parent::wpssw_update_option( 'wpssw_schedule_recurrence', $schedule_recurrence );
					if ( isset( $wpssw_interval[ $schedule_recurrence ] ) ) {
						if ( (string) $schedule_recurrence !== (string) $wpssw_schedule_recurrence || false === wp_next_scheduled( 'wpssw_cron_run' ) ) {
							self::wpssw_remove_cron_job();
							$startcron = parent::wpssw_cron_timeintervals( $wpssw_interval[ $schedule_recurrence ] );
							self::wpssw_set_cron_job( $startcron, $wpssw_interval[ $schedule_recurrence ] );
						}
					}
				}
				if ( 'weekly' === (string) $scheduling_run_on ) {
					$scheduling_weekly_days       = isset( $_POST['scheduling_weekly_days'] ) ? sanitize_text_field( wp_unslash( $_POST['scheduling_weekly_days'] ) ) : '';
					$wpssw_scheduling_weekly_days = parent::wpssw_option( 'wpssw_scheduling_weekly_days' );
					if ( ! empty( $scheduling_weekly_days ) ) {
						self::wpssw_remove_cron_job();
						$wpssw_weekdays = explode( ',', $scheduling_weekly_days );
						if ( is_array( $wpssw_weekdays ) && ! empty( $wpssw_weekdays ) ) {
							parent::wpssw_update_option( 'wpssw_scheduling_weekly_days', $scheduling_weekly_days );
							foreach ( $wpssw_weekdays as $weekday ) {
								$startcron = strtotime( 'next ' . $weekday );
								self::wpssw_set_cron_job( $startcron, 'weekly' );
							}
						}
					}
				}
				if ( 'onetime' === (string) $scheduling_run_on ) {
					$scheduling_date       = isset( $_POST['scheduling_date'] ) ? sanitize_text_field( wp_unslash( $_POST['scheduling_date'] ) ) : '';
					$scheduling_time       = isset( $_POST['scheduling_time'] ) ? sanitize_text_field( wp_unslash( $_POST['scheduling_time'] ) ) : '';
					$wpssw_scheduling_date = parent::wpssw_option( 'wpssw_scheduling_date' );
					$wpssw_scheduling_time = parent::wpssw_option( 'wpssw_scheduling_time' );
					parent::wpssw_update_option( 'wpssw_scheduling_date', $scheduling_date );
					parent::wpssw_update_option( 'wpssw_scheduling_time', $scheduling_time );
					if ( ! empty( $scheduling_date ) && ! empty( $scheduling_time ) ) {
						$starttime = strtotime( $scheduling_date . ' ' . $scheduling_time );
						if ( ! empty( $wpssw_scheduling_date ) && ! empty( $wpssw_scheduling_time ) ) {
							$previousstarttime = strtotime( $wpssw_scheduling_date . ' ' . $wpssw_scheduling_time );
							if ( (int) $starttime !== (int) $previousstarttime ) {
								self::wpssw_remove_cron_job();
							}
						}
						if ( date_i18n( 'Y-m-d H:i' ) < date_i18n( 'Y-m-d H:i', $starttime ) ) {
							self::wpssw_set_cron_job( $starttime, '', true );
						} else {
							parent::wpssw_update_option( 'wpssw_scheduling_date', '' );
							parent::wpssw_update_option( 'wpssw_scheduling_time', '' );
							parent::wpssw_update_option( 'wpssw_scheduling_run_on', '' );
							parent::wpssw_update_option( 'wpssw_scheduling_enable', '' );
						}
					}
				}
			} else {
				parent::wpssw_update_option( 'wpssw_scheduling_run_on', '' );
				parent::wpssw_update_option( 'wpssw_scheduling_weekly_days', '' );
				parent::wpssw_update_option( 'wpssw_scheduling_date', '' );
				parent::wpssw_update_option( 'wpssw_scheduling_time', '' );
				parent::wpssw_update_option( 'wpssw_schedule_recurrence', '' );
				self::wpssw_remove_cron_job();
			}
		}
		/**
		 * Set cron job.
		 *
		 * @param int  $startcron .
		 * @param int  $interval .
		 * @param bool $once .
		 */
		public static function wpssw_set_cron_job( $startcron, $interval, $once = false ) {
			if ( $once ) {
				wp_schedule_single_event( $startcron, 'wpssw_cron_run' );
			} else {
				wp_schedule_event( $startcron, $interval, 'wpssw_cron_run' );
			}
		}
		/**
		 * Remove cron job.
		 */
		public static function wpssw_remove_cron_job() {
			wp_clear_scheduled_hook( 'wpssw_cron_run' );
		}
		/**
		 * Add cron interval.
		 *
		 * @param array $schedules .
		 */
		public static function wpssw_add_cron_interval( $schedules ) {
			$wpssw_interval              = 86400 * 14;
			$schedules['ten_minutes']    = array(
				'interval' => 600,
				'display'  => esc_html__( 'Every Ten Minutes', 'wpssw' ),
			);
			$schedules['thirty_minutes'] = array(
				'interval' => 1800,
				'display'  => esc_html__( 'Every Thirty Minutes', 'wpssw' ),
			);
			$schedules['fifteen_days']   = array(
				'interval' => $wpssw_interval,
				'display'  => esc_html__( 'Every Fifteen Days', 'wpssw' ),
			);

			return $schedules;
		}
		/**
		 * Run cron job.
		 */
		public static function wpssw_cron_run() {
			$wpssw_order_spreadsheet_setting = self::wpssw_check_order_spreadsheet_setting();
			if ( 'yes' !== (string) $wpssw_order_spreadsheet_setting || ! self::$instance_api->checkcredenatials() ) {
				return;
			}

			$wpssw_firstcolumns = self::wpssw_get_orders_count( true );
			$wpssw_all_status   = wc_get_order_statuses();
			$wpssw_all_status   = array_map( 'strtolower', $wpssw_all_status );
			$wpssw_allorders    = parent::wpssw_array_flatten( $wpssw_firstcolumns );

			$wpssw_smallestid = min( $wpssw_allorders );
			if ( ! is_numeric( $wpssw_smallestid ) ) {
				$wpssw_smallestid = 0;
			}
			$wpssw_spreadsheetid     = parent::wpssw_option( 'wpssw_woocommerce_spreadsheet' );
			$wpssw_spreadsheets_list = self::$instance_api->get_spreadsheet_listing();
			if ( ! array_key_exists( $wpssw_spreadsheetid, $wpssw_spreadsheets_list ) ) {
				return;
			}
			$wpssw_sheets = array_filter( (array) parent::wpssw_option( 'wpssw_sheets' ) );
			if ( ! $wpssw_sheets ) {
				$wpssw_sheets = self::wpssw_prepare_sheets();
			}
			$wpssw_productsheets_list     = array();
			$wpssw_product_names_as_sheet = parent::wpssw_option( 'product_names_as_sheet' );
			if ( 'yes' === (string) $wpssw_product_names_as_sheet ) {
				$wpssw_productsheets_list = parent::wpssw_option( 'wpssw_productname_sheets' );
				if ( ! $wpssw_productsheets_list ) {
					$wpssw_productsheets_list = array();
				}
			}
			$wpssw_inputoption = parent::wpssw_option( 'wpssw_inputoption' );

			if ( ! $wpssw_inputoption ) {
				$wpssw_inputoption = 'USER_ENTERED';
			}
			$order_ascdesc = parent::wpssw_option( 'wpssw_order_ascdesc' );

			$wpssw_response            = self::$instance_api->get_sheet_listing( $wpssw_spreadsheetid );
			$wpssw_existingsheetsnames = self::$instance_api->get_sheet_list( $wpssw_response );
			$hpos_order_enabled        = parent::wpssw_check_hpos_order_setting_enabled();
			foreach ( $wpssw_firstcolumns as $wpssw_sheetname => $wpssw_data ) {
				$wpssw_sheetname_key = in_array( $wpssw_sheetname, $wpssw_sheets, true ) ? array_search( $wpssw_sheetname, $wpssw_sheets, true ) : '';
				$wpssw_sheet_slug    = trim( strtolower( str_replace( 'Orders', '', $wpssw_sheetname_key ) ) );
				$wpssw_sheet_slug    = str_replace( ' ', '-', $wpssw_sheet_slug );
				$is_product_sheet    = 0;
				if ( in_array( $wpssw_sheetname, $wpssw_productsheets_list, true ) ) {
					$is_product_sheet = 1;
				}
				if ( array_key_exists( 'wc-' . $wpssw_sheet_slug, $wpssw_all_status ) ) {
					$wpssw_sheet_slug = 'wc-' . $wpssw_sheet_slug;
				} elseif ( ! in_array( $wpssw_sheetname_key, array( 'All Orders', 'Trash Orders' ), true ) && ! $is_product_sheet ) {
					continue;
				}

				$wpssw_check_category      = false;
				$wpssw_category_filter     = parent::wpssw_option( 'wpssw_category_filter' );
				$wpssw_category_filter_ids = parent::wpssw_option( 'wpssw_category_filter_ids' );
				if ( $wpssw_category_filter && ! empty( $wpssw_category_filter_ids ) ) {
					$wpssw_check_category = true;
				}
				$wpssw_data = parent::wpssw_convert_int( $wpssw_data );
				if ( $hpos_order_enabled ) {
					// Determine the base query arguments based on sheet type.
					if ( 'All Orders' === (string) $wpssw_sheetname_key || $is_product_sheet ) {
						$wpssw_query_args = array(
							'status'  => array_keys( wc_get_order_statuses() ),
							'orderby' => 'date',
							'order'   => 'ASC',
							'limit'   => apply_filters( 'wpssw_order_sync_limit', 500 ),
							'return'  => 'ids',
						);
					} else {
						$wpssw_query_args = array(
							'status'  => $wpssw_sheet_slug,
							'orderby' => 'date',
							'order'   => 'ASC',
							'limit'   => apply_filters( 'wpssw_order_sync_limit', 500 ),
							'return'  => 'ids',
						);
					}

					// Check the order direction.
					if ( 'descorder' === (string) $order_ascdesc ) {
						$wpssw_query_args['order'] = 'DESC';
					}

					if ( is_array( $wpssw_data ) && ! empty( $wpssw_data ) ) {
						$wpssw_query_args['exclude'] = $wpssw_data;
					}

					// Fetch the orders.
					$wpssw_all_orders = wc_get_orders( $wpssw_query_args );

				} else {
					if ( 'All Orders' === (string) $wpssw_sheetname_key || $is_product_sheet ) {
						$wpssw_query_args = array(
							'post_type'      => 'shop_order',
							'posts_per_page' => apply_filters( 'wpssw_order_sync_limit', 500 ),
							'order'          => 'ASC',
							'post_status'    => array_keys( wc_get_order_statuses() ),
						);
					} else {
						$wpssw_query_args = array(
							'post_type'      => 'shop_order',
							'post_status'    => $wpssw_sheet_slug,
							'posts_per_page' => apply_filters( 'wpssw_order_sync_limit', 500 ),
							'order'          => 'ASC',
						);
					}
					if ( 'descorder' === (string) $order_ascdesc ) {
						$wpssw_query_args['order'] = 'DESC';
					}
					$wpssw_query_args['fields'] = 'ids'; // Fetch only ids.
					if ( is_array( $wpssw_data ) && ! empty( $wpssw_data ) ) {
						$wpssw_query_args['post__not_in'] = $wpssw_data;
					}

					$wpsswcustom_query = new WP_Query( $wpssw_query_args );
					$wpssw_all_orders  = $wpsswcustom_query->posts;
				}
				if ( empty( $wpssw_all_orders ) ) {
					continue;
				}

				$wpssw_values_array = array();
				$neworder           = 0;
				foreach ( $wpssw_all_orders as $wpssw_order_id ) {
					if ( $wpssw_check_category ) {
						if ( self::wpssw_check_product_category( $wpssw_order_id ) || $wpssw_smallestid > $wpssw_order_id ) {
							continue;
						}
					}
					$wpssw_order = wc_get_order( $wpssw_order_id );
					if ( $is_product_sheet ) {
						$items_to_keep = self::wpssw_get_specific_items( $wpssw_order, $wpssw_sheetname );
						if ( empty( $items_to_keep ) ) {
							continue;
						}
					}
					set_time_limit( 999 );
					$wpssw_order_data = $wpssw_order->get_data();
					$wpssw_status     = $wpssw_order_data['status'];
					$wpssw_is_allowed = true;
					if ( 'All Orders' === (string) $wpssw_sheetname_key ) {
						$wpssw_is_allowed = apply_filters( 'wpssw_order_status_allow', true, ucwords( $wpssw_status ) . ' Orders' );
					}
					if ( $wpssw_is_allowed ) {
						if ( $is_product_sheet ) {
							$wpssw_value = self::wpssw_make_value_array( 'insert', $wpssw_order_id, $wpssw_sheetname );
						} else {
							$wpssw_value = self::wpssw_make_value_array( 'insert', $wpssw_order_id );
						}
						$wpssw_values_array = array_merge( $wpssw_values_array, $wpssw_value );
					}
				}

				$wpssw_sheetid = $wpssw_existingsheetsnames[ $wpssw_sheetname ];
				$rangetofind   = $wpssw_sheetname . '!A' . ( count( $wpssw_data ) + 1 );
				if ( ! empty( $wpssw_values_array ) ) {
					try {
						$startindex = count( $wpssw_data );
						if ( 'descorder' === (string) $order_ascdesc ) {
							if ( count( $wpssw_data ) > 0 ) {

								$param               = array();
								$startindex          = 1;
								$endindex            = count( $wpssw_values_array ) + 1;
								$param               = self::$instance_api->prepare_param( $wpssw_sheetid, $startindex, $endindex );
								$wpssw_inherit_style = parent::wpssw_option( 'wpssw_order_inherit_style' );
								if ( 'no' === (string) $wpssw_inherit_style ) {
									$wpssw_batchupdaterequest = self::$instance_api->insertdimensionobject( $param, false );
								} else {
									$wpssw_batchupdaterequest = self::$instance_api->insertdimensionobject( $param, true );
								}
								$requestobject                  = array();
								$requestobject['spreadsheetid'] = $wpssw_spreadsheetid;
								$requestobject['requestbody']   = $wpssw_batchupdaterequest;
								$wpssw_response                 = self::$instance_api->formatsheet( $requestobject );
								$rangetofind                    = $wpssw_sheetname . '!A2';
							}
						}

						// If there is no sheet_id available, leave the sheet_id field blank. Otherwise, if there is a sheet_id, leave the sheet_name field blank.
						$wpssw_inherit_params = array(
							'sheet_id'       => $wpssw_sheetid,
							'sheet_name'     => '',
							'start_index'    => $startindex,
							'end_index'      => $startindex + count( $wpssw_values_array ),
							'spreadsheet_id' => $wpssw_spreadsheetid,
						);

						parent::wpssw_inherit_row_format_style( 'order', $wpssw_inherit_params );

						$wpssw_params      = array( 'valueInputOption' => $wpssw_inputoption );
						$wpssw_requestbody = self::$instance_api->valuerangeobject( $wpssw_values_array );
						$param             = self::$instance_api->setparamater( $wpssw_spreadsheetid, $rangetofind, $wpssw_requestbody, $wpssw_params );
						$wpssw_response    = self::$instance_api->appendentry( $param );

					} catch ( Exception $e ) {
						echo esc_html( 'Message: ' . $e->getMessage() );
					}
				}
			}
		}

		/**
		 * Get cart item from session.
		 *
		 * @param array $wpssw_cart_item Cart item data.
		 * @param array $wpssw_values    Cart item values.
		 * @return array
		 */
		public static function wpssw_get_cart_item_from_session( $wpssw_cart_item, $wpssw_values ) {
			if ( ! empty( $wpssw_values['wpssw_headers'] ) ) {
				$wpssw_cart_item['wpssw_headers'] = $wpssw_values['wpssw_headers'];
			}
			return $wpssw_cart_item;
		}
		/**
		 * Add item data to the cart.
		 *
		 * @param array $wpssw_cart_item_data Cart item data.
		 * @param int   $wpssw_product_id .
		 */
		public static function wpssw_add_cart_item_data( $wpssw_cart_item_data, $wpssw_product_id ) {
			if ( ! class_exists( 'WC_Product_Addons' ) ) {
				return $wpssw_cart_item_data;
			}
			// @codingStandardsIgnoreStart.
			if ( isset( $_POST ) && ! empty( $wpssw_product_id ) ) {
				$wpssw_post_data = $_POST;
				// @codingStandardsIgnoreEnd.
			} else {
				return;
			}
			$wpssw_product_addons = WC_Product_Addons_Helper::get_product_addons( $wpssw_product_id );
			if ( empty( $wpssw_cart_item_data['wpssw_headers'] ) ) {
				$wpssw_cart_item_data['wpssw_headers'] = array();
			}
			if ( is_array( $wpssw_product_addons ) && ! empty( $wpssw_product_addons ) ) {
				foreach ( $wpssw_product_addons as $wpssw_addon ) {
					// If type is heading, skip.
					if ( 'heading' === (string) $wpssw_addon['type'] ) {
						continue;
					}
					$wpssw_value = isset( $wpssw_post_data[ 'addon-' . $wpssw_addon['field_name'] ] ) ? $wpssw_post_data[ 'addon-' . $wpssw_addon['field_name'] ] : '';
					$wpssw_key   = strtolower( str_replace( ' ', '_', $wpssw_addon['name'] ) );
					if ( is_array( $wpssw_value ) ) {
						$wpssw_value = array_map( 'stripslashes', $wpssw_value );
					} else {
						$wpssw_value = stripslashes( $wpssw_value );
					}
					$wpssw_data[ $wpssw_addon['field_name'] ] = $wpssw_addon['name'];
					$wpssw_cart_item_data['wpssw_headers']    = array_merge( $wpssw_cart_item_data['wpssw_headers'], apply_filters( 'woocommerce_product_addon_cart_item_data', $wpssw_data, $wpssw_addon, $wpssw_product_id, $wpssw_post_data ) );
				}
			}
			return $wpssw_cart_item_data;
		}
		/**
		 * Include add-ons line item meta.
		 *
		 * @param  WC_Order_Item_Product $wpssw_item          Order item data.
		 * @param  string                $wpssw_cart_item_key Cart item key.
		 * @param  array                 $wpssw_values        Order item values.
		 */
		public static function wpssw_order_line_item( $wpssw_item, $wpssw_cart_item_key, $wpssw_values ) {
			if ( ! class_exists( 'WC_Product_Addons' ) ) {
				return;
			}
			if ( ! empty( $wpssw_values['addons'] ) ) {
				foreach ( $wpssw_values['addons'] as $wpssw_addon ) {
					$wpssw_key           = $wpssw_addon['name'];
					$wpssw_price_type    = $wpssw_addon['price_type'];
					$wpssw_product       = $wpssw_item->get_product();
					$wpssw_product_price = $wpssw_product->get_price();
					$wpssw_price         = html_entity_decode( wp_strip_all_tags( wc_price( WC_Product_Addons_Helper::get_product_addon_price_for_display( $wpssw_addon['price'], $wpssw_values['data'], true ) ) ) );

					/*
					* For percentage based price type we want
					* to show the calculated price instead of
					* the price of the add-on itself and in this
					* case its not a price but a percentage.
					* Also if the product price is zero, then there
					* is nothing to calculate for percentage so
					* don't show any price.
					*/
					if ( $wpssw_addon['price'] && 'percentage_based' === (string) $wpssw_price_type && 0 !== (int) $wpssw_product_price ) {
						$wpssw_price = html_entity_decode( wp_strip_all_tags( wc_price( WC_Product_Addons_Helper::get_product_addon_price_for_display( ( $wpssw_product_price * ( $wpssw_addon['price'] / 100 ) ) ) ) ) );
					}
					if ( 'custom_price' === (string) $wpssw_addon['field_type'] ) {
						$wpssw_addon['value'] = $wpssw_addon['price'];
					}
					$wpssw_key = strtolower( str_replace( ' ', '_', $wpssw_addon['name'] ) );
					if ( is_array( $wpssw_value ) ) {
						$wpssw_value = array_map( 'stripslashes', $wpssw_value );
					} else {
						$wpssw_value = stripslashes( $wpssw_value );
					}
					$wpssw_item->add_meta_data( $wpssw_addon['field_name'], $wpssw_addon['value'] );
				}
			}
			if ( ! empty( $wpssw_values['wpssw_headers'] ) ) {
				$wpssw_item->add_meta_data( 'wpssw_headers', $wpssw_values['wpssw_headers'] );
			}
		}
		/**
		 * Output sheets.
		 */
		public static function wpssw_woocommerce_admin_field_set_sheets() {
			$wpssw_sheets          = array();
			$wpssw_sheets          = self::$wpssw_default_sheets;
			$wpssw_sheets_selected = array();
			$wpssw_sheets_selected = array_filter( (array) parent::wpssw_option( 'wpssw_sheets' ) );

			if ( ! $wpssw_sheets_selected ) {
				$wpssw_sheets_selected = self::wpssw_prepare_sheets();
			}
			$wpssw_spreadsheetid       = parent::wpssw_option( 'wpssw_woocommerce_spreadsheet' );
			$spreadsheets_list         = self::$instance_api->get_spreadsheet_listing();
			$wpssw_existingsheetsnames = array();
			if ( ! empty( $wpssw_spreadsheetid ) && array_key_exists( $wpssw_spreadsheetid, $spreadsheets_list ) ) {
				$wpssw_response            = self::$instance_api->get_sheet_listing( $wpssw_spreadsheetid );
				$wpssw_existingsheetsnames = self::$instance_api->get_sheet_list( $wpssw_response );
				$wpssw_existingsheetsnames = array_flip( $wpssw_existingsheetsnames );
			}
			?>
			<select id="existing_sheets" hidden="true">
			<?php
			foreach ( $wpssw_existingsheetsnames as $wpssw_existingsheet ) {
				?>
					<option value="<?php echo esc_attr( trim( $wpssw_existingsheet ) ); ?>"><?php echo esc_attr( $wpssw_existingsheet ); ?></option>
				<?php } ?>
			</select>
			<?php
			if ( ! empty( $wpssw_sheets ) ) {
				?>
				<div class="generalSetting-section wpssw-section-2 default-order-sheet-section ord_spreadsheet_row">
				<div class="generalSetting-left">
					<h4><?php echo esc_html__( 'Default Order Status as Sheets', 'wpssw' ); ?></h4>
					<p><?php echo esc_html__( "Here's a list of sheets based on the default order status provided by WooCommerce, including 'Trash' and 'All Orders'. These selected sheets will be created in your assigned Google Spreadsheets and will neatly organize orders according to their status. These sheets will be automatically updated to keep everything well-managed whenever there are updates or syncs for orders.", 'wpssw' ); ?></p>
				<div class="default-order-sheet-box">
				<?php
				foreach ( $wpssw_sheets as $wpssw_key => $wpssw_val ) {

					$wpssw_is_check = '';
					$wpssw_labelid  = strtolower( str_replace( ' ', '_', $wpssw_val ) );
					if ( array_key_exists( $wpssw_val, $wpssw_sheets_selected ) ) {
						$wpssw_is_check  = 'checked';
						$wpssw_customval = $wpssw_sheets_selected[ $wpssw_val ];
					} else {
						$wpssw_customval = $wpssw_val;
					}

					?>
					<div class="default-order-sheet">
						<div class="orderSheet-left">
							<label for="<?php echo esc_attr( $wpssw_labelid ); ?>">
								<span class="wootextfield"><?php echo esc_html( $wpssw_customval ); ?></span>
								<span class="ui-icon ui-icon-pencil edit-sheetname wpssw-tooltio-link">
									<span class="pencil-icon">
									<svg width="12" height="12" viewBox="0 0 13 13" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path d="M1.33333 11.6668H2.26667L8.01667 5.91677L7.08333 4.98343L1.33333 10.7334V11.6668ZM10.8667 4.9501L8.03333 2.1501L8.96667 1.21677C9.22222 0.961213 9.53611 0.833435 9.90833 0.833435C10.2806 0.833435 10.5944 0.961213 10.85 1.21677L11.7833 2.1501C12.0389 2.40566 12.1722 2.71399 12.1833 3.0751C12.1944 3.43621 12.0722 3.74455 11.8167 4.0001L10.8667 4.9501ZM9.9 5.93343L2.83333 13.0001H0V10.1668L7.06667 3.1001L9.9 5.93343Z" fill="#64748B"/>
									</svg>
									</span>
									<span class="check-icon">
										<svg xmlns="http://www.w3.org/2000/svg"  viewBox="0 0 26 26" width="12" height="12">
											<path d="M 22.566406 4.730469 L 20.773438 3.511719 C 20.277344 3.175781 19.597656 3.304688 19.265625 3.796875 L 10.476563 16.757813 L 6.4375 12.71875 C 6.015625 12.296875 5.328125 12.296875 4.90625 12.71875 L 3.371094 14.253906 C 2.949219 14.675781 2.949219 15.363281 3.371094 15.789063 L 9.582031 22 C 9.929688 22.347656 10.476563 22.613281 10.96875 22.613281 C 11.460938 22.613281 11.957031 22.304688 12.277344 21.839844 L 22.855469 6.234375 C 23.191406 5.742188 23.0625 5.066406 22.566406 4.730469 Z" fill="#64748B"/></svg>
									</span>
									<span class="tooltip-text edit-tooltip">Edit</span>
									<span class="tooltip-text update-tooltip">Update</span>
								</span>
							</label>
						</div>
						<div class="orderSheet-right forminp forminp-checkbox inside-checkbox">
							<fieldset>
								<label for="<?php echo esc_attr( $wpssw_labelid ); ?>">
								<input type="checkbox" name="wpssw_sheets_custom[]" value="<?php echo esc_attr( $wpssw_customval ); ?>" class="sheets_chk1" <?php echo esc_html( $wpssw_is_check ); ?> hidden="true">
								<input type="checkbox" name="wpssw_sheets[]" value="<?php echo esc_attr( $wpssw_val ); ?>" id="<?php echo esc_attr( $wpssw_labelid ); ?>" class="sheets_chk" <?php echo esc_html( $wpssw_is_check ); ?>>
								<span class="checkbox-switch-new"></span>
								</label>
							</fieldset>
						</div>
					</div>

						<?php
				}
				?>
				</div>
				</div>
				</div>
				<?php
			}
		}
		/**
		 * Output Custom sheets.
		 */
		public static function wpssw_woocommerce_admin_field_set_custom_sheets() {
			$wpssw_status_array    = wc_get_order_statuses();
			$wpssw_sheets_selected = array_filter( (array) parent::wpssw_option( 'wpssw_sheets' ) );
			if ( ! $wpssw_sheets_selected ) {
				$wpssw_sheets_selected = self::wpssw_prepare_sheets();
			}
			?>
			<div class="generalSetting-section custom-order-sheet-section ord_spreadsheet_row">
			<div class="generalSetting-left">
				<h4><?php echo esc_html__( 'Custom Order Status as Sheets', 'wpssw' ); ?></h4>
				<p><?php echo esc_html__( 'This option displays the list of custom order statuses created by the WooCommerce third-party plugin. Selected sheets will be created in your Google Spreadsheets, organizing orders based on their status.', 'wpssw' ); ?></p>
			<div class="default-order-sheet-box">
			<?php
			foreach ( $wpssw_status_array as $wpssw_key => $wpssw_val ) {
				$wpssw_status = substr( $wpssw_key, strpos( $wpssw_key, '-' ) + 1 );
				if ( ! in_array( $wpssw_status, self::$wpssw_default_status, true ) ) {
					$wpssw_status    = str_replace( '-', ' ', $wpssw_status );
					$wpssw_status    = ucwords( $wpssw_status ) . ' Orders';
					$wpssw_is_check  = '';
					$wpssw_labelid   = strtolower( str_replace( ' ', '_', $wpssw_val ) );
					$wpssw_val       = ucwords( trim( $wpssw_val ) ) . ' Orders';
					$wpssw_customval = $wpssw_val;
					if ( array_key_exists( $wpssw_status, $wpssw_sheets_selected ) ) {
						$wpssw_is_check  = 'checked';
						$wpssw_customval = trim( $wpssw_sheets_selected[ $wpssw_status ] );
					}
					?>
					<div class="custom-order-sheet default-order-sheet">
						<div class="orderSheet-left">
							<label for="<?php echo esc_attr( $wpssw_labelid ); ?>">
								<span class="wootextfield"><?php echo esc_attr( $wpssw_customval ); ?></span>
								<span class="ui-icon ui-icon-pencil edit-customsheetname wpssw-tooltio-link">
									<span class="pencil-icon">
									<svg width="12" height="12" viewBox="0 0 13 13" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path d="M1.33333 11.6668H2.26667L8.01667 5.91677L7.08333 4.98343L1.33333 10.7334V11.6668ZM10.8667 4.9501L8.03333 2.1501L8.96667 1.21677C9.22222 0.961213 9.53611 0.833435 9.90833 0.833435C10.2806 0.833435 10.5944 0.961213 10.85 1.21677L11.7833 2.1501C12.0389 2.40566 12.1722 2.71399 12.1833 3.0751C12.1944 3.43621 12.0722 3.74455 11.8167 4.0001L10.8667 4.9501ZM9.9 5.93343L2.83333 13.0001H0V10.1668L7.06667 3.1001L9.9 5.93343Z" fill="#64748B"/>
									</svg>
									</span>
									<span class="check-icon">
										<svg xmlns="http://www.w3.org/2000/svg"  viewBox="0 0 26 26" width="12" height="12">
											<path d="M 22.566406 4.730469 L 20.773438 3.511719 C 20.277344 3.175781 19.597656 3.304688 19.265625 3.796875 L 10.476563 16.757813 L 6.4375 12.71875 C 6.015625 12.296875 5.328125 12.296875 4.90625 12.71875 L 3.371094 14.253906 C 2.949219 14.675781 2.949219 15.363281 3.371094 15.789063 L 9.582031 22 C 9.929688 22.347656 10.476563 22.613281 10.96875 22.613281 C 11.460938 22.613281 11.957031 22.304688 12.277344 21.839844 L 22.855469 6.234375 C 23.191406 5.742188 23.0625 5.066406 22.566406 4.730469 Z" fill="#64748B"/></svg>
									</span>
									<span class="tooltip-text edit-tooltip">Edit</span>
									<span class="tooltip-text update-tooltip">Update</span>
								</span>
							</label>
						</div>
						<div class="forminp forminp-checkbox orderSheet-right inside-checkbox">
							<fieldset>
							<label for="<?php echo esc_attr( $wpssw_labelid ); ?>">
							<input type="checkbox" name="wpssw_customsheets_custom[]" value="<?php echo esc_attr( $wpssw_customval ); ?>" class="customsheets_chk1" <?php echo esc_html( $wpssw_is_check ); ?> hidden="true">
							<input type="checkbox" name="wpssw_customsheets[]" value="<?php echo esc_attr( $wpssw_status ); ?>" id="<?php echo esc_attr( $wpssw_labelid ); ?>" class="customsheets_chk" <?php echo esc_html( $wpssw_is_check ); ?>>
							<span class="checkbox-switch-new"></span>
							</label>
						</fieldset>
						</div>
					</div>
					<?php
				}
			}
			?>
			</div>
			</div>
			</div>
			<?php
		}
		/**
		 * Output sheet header settings.
		 */
		public static function wpssw_woocommerce_admin_field_set_headers() {
			$wpssw_include = new WPSSW_Include_Action();
			$wpssw_include->wpssw_include_order_compatibility_files();
			$wpssw_header_type         = self::wpssw_is_productwise();
			$wpssw_headers             = array();
			$wpssw_shipping_fields     = array();
			$wpssw_billing_fields      = array();
			$wpssw_additional_fields   = array();
			$wpssw_headers             = apply_filters( 'wpsyncsheets_order_headers', array() );
			$wpssw_productwise_headers = self::get_headers_by_key( $wpssw_headers, 'ProductWise' );
			$wpssw_orderwise_headers   = self::get_headers_by_key( $wpssw_headers, 'OrderWise' );
			$wpssw_headers             = array_unique( array_values( parent::wpssw_array_flatten( $wpssw_headers ) ) );
			$wpssw_selections          = stripslashes_deep( parent::wpssw_option( 'wpssw_sheet_headers_list' ) );
			if ( ! $wpssw_selections ) {
				$wpssw_selections = array();
			}
			$wpssw_selections_custom = stripslashes_deep( parent::wpssw_option( 'wpssw_sheet_headers_list_custom' ) );
			if ( ! $wpssw_selections_custom ) {
				$wpssw_selections_custom = array();
			}
			$wpssw_prdwise = array_merge( $wpssw_productwise_headers, $wpssw_headers );
			$wpssw_ordwise = array_merge( $wpssw_orderwise_headers, $wpssw_headers );
			if ( $wpssw_header_type ) {
				$wpssw_headers = $wpssw_prdwise;
			} else {
				$wpssw_headers = $wpssw_ordwise;
			}
			$wpssw_headers = stripslashes_deep( $wpssw_headers );
			global $wpssw_global_headers;
			$wpssw_global_headers       = $wpssw_headers;
			$wpssw_product_selections   = stripslashes_deep( parent::wpssw_option( 'wpssw_product_sheet_headers_list' ) );
			$wpssw_static_header        = stripslashes_deep( parent::wpssw_option( 'wpssw_static_header' ) );
			$wpssw_static_header_values = stripslashes_deep( parent::wpssw_option( 'wpssw_static_header_values' ) );
			$wpssw_custom_value         = array();
			if ( ! $wpssw_product_selections ) {
				$wpssw_product_selections = array();
			}
			if ( ! $wpssw_static_header ) {
				$wpssw_static_header = array();
			}
			if ( ! $wpssw_static_header_values ) {
				$wpssw_static_header_values = array();
			}
			if ( ! empty( $wpssw_static_header_values ) ) {
				foreach ( $wpssw_static_header_values as $wpssw_static_header_value ) {
					if ( strpos( $wpssw_static_header_value, ',(static_header),' ) ) {
						$wpssw_static_header_value = str_replace( ',(static_header),', ',', $wpssw_static_header_value );
						$wpssw_custom_value[]      = explode( ',', $wpssw_static_header_value );
					}
				}
			}
			?>
			<div class="generalSetting-section sheetHeaders-section ord_spreadsheet_row">
				<div class="td-wpssw-headers">
					<div class='wpssw_headers'>
						<div class="generalSetting-sheet-headers-row">
							<div class="generalSetting-left">
								<h4><?php echo esc_html__( 'Sheet Headers', 'wpssw' ); ?></h4>
								<p><?php echo esc_html__( 'If you disable any sheet headers, they will be automatically removed from the current spreadsheet. You can always re-enable the headers you need, save the settings, and update the spreadsheet with the latest data. Clear the existing spreadsheet and click the "Click to Sync" button to initiate the sync process.', 'wpssw' ); ?></p>
							</div>
						</div>
						<div class="selectall-btnset">
								<button type="button" class="wpssw-button wpssw-button-primary" id="selectall"><?php esc_html_e( 'Select All', 'wpssw' ); ?></button>                
								<button type="button" class="wpssw-button wpssw-button-secondary" id="selectnone"><?php esc_html_e( 'Select None', 'wpssw' ); ?></button>
							</div>
						<div class="sheetHeaders-main">
						<ul id="sortable" class="sheetHeaders-box">
							<?php
							$wpssw_operation = array( 'Insert', 'Update', 'Delete' );
							if ( ! empty( $wpssw_selections ) ) {
								foreach ( $wpssw_selections as $wpssw_key => $wpssw_val ) {
									if ( in_array( $wpssw_val, $wpssw_product_selections, true ) ) {
										continue;
									}
									$wpssw_static_field        = '';
									$wpssw_static_field_values = '';
									$wpssw_is_check            = 'checked';
									$wpssw_labelid             = strtolower( str_replace( ' ', '_', $wpssw_val ) );

									$wpssw_display   = true;
									$wpssw_classname = '';
									if ( in_array( $wpssw_val, $wpssw_operation, true ) ) {
										$wpssw_display   = false;
										$wpssw_labelid   = '';
										$wpssw_classname = strtolower( $wpssw_val ) . 'order';
									}
									?>
									<li class="default-order-sheet ui-state-default <?php echo esc_html( $wpssw_classname ); ?>">
										<label for="<?php echo esc_attr( $wpssw_labelid ); ?>">
											<span class="orderSheet-left">
											<span class="wootextfield"><?php echo isset( $wpssw_selections_custom[ $wpssw_key ] ) ? esc_attr( $wpssw_selections_custom[ $wpssw_key ] ) : esc_attr( $wpssw_val ); ?></span>
										<?php if ( $wpssw_display ) { ?>
										<span class="ui-icon ui-icon-pencil wpssw-tooltio-link">
											<span class="pencil-icon">
												<svg width="12" height="12" viewBox="0 0 13 13" fill="none" xmlns="http://www.w3.org/2000/svg">
												<path d="M1.33333 11.6668H2.26667L8.01667 5.91677L7.08333 4.98343L1.33333 10.7334V11.6668ZM10.8667 4.9501L8.03333 2.1501L8.96667 1.21677C9.22222 0.961213 9.53611 0.833435 9.90833 0.833435C10.2806 0.833435 10.5944 0.961213 10.85 1.21677L11.7833 2.1501C12.0389 2.40566 12.1722 2.71399 12.1833 3.0751C12.1944 3.43621 12.0722 3.74455 11.8167 4.0001L10.8667 4.9501ZM9.9 5.93343L2.83333 13.0001H0V10.1668L7.06667 3.1001L9.9 5.93343Z" fill="#64748B"/>
												</svg>
												</span>
												<span class="check-icon">
													<svg xmlns="http://www.w3.org/2000/svg"  viewBox="0 0 26 26" width="12" height="12">
														<path d="M 22.566406 4.730469 L 20.773438 3.511719 C 20.277344 3.175781 19.597656 3.304688 19.265625 3.796875 L 10.476563 16.757813 L 6.4375 12.71875 C 6.015625 12.296875 5.328125 12.296875 4.90625 12.71875 L 3.371094 14.253906 C 2.949219 14.675781 2.949219 15.363281 3.371094 15.789063 L 9.582031 22 C 9.929688 22.347656 10.476563 22.613281 10.96875 22.613281 C 11.460938 22.613281 11.957031 22.304688 12.277344 21.839844 L 22.855469 6.234375 C 23.191406 5.742188 23.0625 5.066406 22.566406 4.730469 Z" fill="#64748B"/></svg>
												</span>
												<span class="tooltip-text edit-tooltip">Edit</span>
												<span class="tooltip-text update-tooltip">Update</span>
										</span>
										<?php } ?>
									</span>
									<span class="orderSheet-right">
									<input type="checkbox" name="header_fields_custom[]" value="<?php echo isset( $wpssw_selections_custom[ $wpssw_key ] ) ? esc_attr( $wpssw_selections_custom[ $wpssw_key ] ) : esc_attr( $wpssw_val ); ?>" class="headers_chk1" <?php echo esc_html( $wpssw_is_check ); ?> hidden="true">
										<?php
										if ( in_array( $wpssw_val, $wpssw_static_header, true ) ) {
											?>
										<input type="checkbox" name="header_fields_static[]" value="<?php echo esc_attr( $wpssw_val ); ?>" hidden="true" checked>
											<?php
										}
										if ( in_array( $wpssw_val, array_column( $wpssw_custom_value, 0 ), true ) ) {
											$search_key          = array_search( $wpssw_val, array_column( $wpssw_custom_value, 0 ), true );
											$wpssw_search_array  = $wpssw_custom_value[ $search_key ];
											$wpssw_search_keyval = implode( ',(static_header),', $wpssw_search_array );
											?>
										<input type="checkbox" name="wpssw_static_header_values[]" value="<?php echo esc_attr( $wpssw_search_keyval ); ?>" hidden="true" checked>
											<?php
										}
										?>
										<input type="checkbox" name="header_fields[]" value="<?php echo esc_attr( $wpssw_val ); ?>" id="<?php echo esc_attr( $wpssw_labelid ); ?>" class="headers_chk" <?php echo esc_html( $wpssw_is_check ); ?>>
										<?php if ( $wpssw_display ) { ?>
										<span class="checkbox-switch-new"></span>
										<?php } ?>
										<span class="ui-icon ui-icon-caret-2-n-s wpssw-tooltio-link">
											<svg width="17" height="16" viewBox="0 0 17 16" fill="none" xmlns="http://www.w3.org/2000/svg">
											<mask id="mask0_384_3228" style="mask-type:alpha" maskUnits="userSpaceOnUse" x="0" y="0" width="17" height="16">
											<rect x="0.5" width="16" height="16" fill="#D9D9D9"/>
											</mask>
											<g mask="url(#mask0_384_3228)">
											<path d="M5.95875 13.67C5.55759 13.67 5.21418 13.5272 4.92851 13.2415C4.64284 12.9558 4.5 12.6124 4.5 12.2113C4.5 11.8101 4.64284 11.4667 4.92851 11.181C5.21418 10.8953 5.55759 10.7525 5.95875 10.7525C6.35991 10.7525 6.70332 10.8953 6.98899 11.181C7.27466 11.4667 7.4175 11.8101 7.4175 12.2113C7.4175 12.6124 7.27466 12.9558 6.98899 13.2415C6.70332 13.5272 6.35991 13.67 5.95875 13.67ZM10.335 13.67C9.93384 13.67 9.59043 13.5272 9.30476 13.2415C9.01909 12.9558 8.87625 12.6124 8.87625 12.2113C8.87625 11.8101 9.01909 11.4667 9.30476 11.181C9.59043 10.8953 9.93384 10.7525 10.335 10.7525C10.7362 10.7525 11.0796 10.8953 11.3652 11.181C11.6509 11.4667 11.7937 11.8101 11.7937 12.2113C11.7937 12.6124 11.6509 12.9558 11.3652 13.2415C11.0796 13.5272 10.7362 13.67 10.335 13.67ZM5.95875 9.29375C5.55759 9.29375 5.21418 9.15091 4.92851 8.86524C4.64284 8.57957 4.5 8.23616 4.5 7.835C4.5 7.43384 4.64284 7.09043 4.92851 6.80476C5.21418 6.51909 5.55759 6.37625 5.95875 6.37625C6.35991 6.37625 6.70332 6.51909 6.98899 6.80476C7.27466 7.09043 7.4175 7.43384 7.4175 7.835C7.4175 8.23616 7.27466 8.57957 6.98899 8.86524C6.70332 9.15091 6.35991 9.29375 5.95875 9.29375ZM10.335 9.29375C9.93384 9.29375 9.59043 9.15091 9.30476 8.86524C9.01909 8.57957 8.87625 8.23616 8.87625 7.835C8.87625 7.43384 9.01909 7.09043 9.30476 6.80476C9.59043 6.51909 9.93384 6.37625 10.335 6.37625C10.7362 6.37625 11.0796 6.51909 11.3652 6.80476C11.6509 7.09043 11.7937 7.43384 11.7937 7.835C11.7937 8.23616 11.6509 8.57957 11.3652 8.86524C11.0796 9.15091 10.7362 9.29375 10.335 9.29375ZM5.95875 4.9175C5.55759 4.9175 5.21418 4.77466 4.92851 4.48899C4.64284 4.20332 4.5 3.85991 4.5 3.45875C4.5 3.05759 4.64284 2.71418 4.92851 2.42851C5.21418 2.14284 5.55759 2 5.95875 2C6.35991 2 6.70332 2.14284 6.98899 2.42851C7.27466 2.71418 7.4175 3.05759 7.4175 3.45875C7.4175 3.85991 7.27466 4.20332 6.98899 4.48899C6.70332 4.77466 6.35991 4.9175 5.95875 4.9175ZM10.335 4.9175C9.93384 4.9175 9.59043 4.77466 9.30476 4.48899C9.01909 4.20332 8.87625 3.85991 8.87625 3.45875C8.87625 3.05759 9.01909 2.71418 9.30476 2.42851C9.59043 2.14284 9.93384 2 10.335 2C10.7362 2 11.0796 2.14284 11.3652 2.42851C11.6509 2.71418 11.7937 3.05759 11.7937 3.45875C11.7937 3.85991 11.6509 4.20332 11.3652 4.48899C11.0796 4.77466 10.7362 4.9175 10.335 4.9175Z" fill="#64748B"/>
											</g>
											</svg>
											<span class="tooltip-text">Drag & Drop</span>
										</span>
									</span>
										</label>
									</li>
									<?php
								}
							}
							if ( ! empty( $wpssw_headers ) ) {
								foreach ( $wpssw_headers as $wpssw_key => $wpssw_val ) {
									$wpssw_is_check = '';
									if ( in_array( $wpssw_val, $wpssw_selections, true ) ) {
										continue;
									}
									$wpssw_labelid = strtolower( str_replace( ' ', '_', $wpssw_val ) );
									?>
									<li class="default-order-sheet ui-state-default">
										<label for="<?php echo esc_attr( $wpssw_labelid ); ?>">
											<span class="orderSheet-left">
												<span class="wootextfield"><?php echo esc_html( $wpssw_val ); ?></span>
												<span class="ui-icon ui-icon-pencil wpssw-tooltio-link">
													<span class="pencil-icon">
												<svg width="12" height="12" viewBox="0 0 13 13" fill="none" xmlns="http://www.w3.org/2000/svg">
												<path d="M1.33333 11.6668H2.26667L8.01667 5.91677L7.08333 4.98343L1.33333 10.7334V11.6668ZM10.8667 4.9501L8.03333 2.1501L8.96667 1.21677C9.22222 0.961213 9.53611 0.833435 9.90833 0.833435C10.2806 0.833435 10.5944 0.961213 10.85 1.21677L11.7833 2.1501C12.0389 2.40566 12.1722 2.71399 12.1833 3.0751C12.1944 3.43621 12.0722 3.74455 11.8167 4.0001L10.8667 4.9501ZM9.9 5.93343L2.83333 13.0001H0V10.1668L7.06667 3.1001L9.9 5.93343Z" fill="#64748B"/>
												</svg>
												</span>
												<span class="check-icon">
													<svg xmlns="http://www.w3.org/2000/svg"  viewBox="0 0 26 26" width="12" height="12">
														<path d="M 22.566406 4.730469 L 20.773438 3.511719 C 20.277344 3.175781 19.597656 3.304688 19.265625 3.796875 L 10.476563 16.757813 L 6.4375 12.71875 C 6.015625 12.296875 5.328125 12.296875 4.90625 12.71875 L 3.371094 14.253906 C 2.949219 14.675781 2.949219 15.363281 3.371094 15.789063 L 9.582031 22 C 9.929688 22.347656 10.476563 22.613281 10.96875 22.613281 C 11.460938 22.613281 11.957031 22.304688 12.277344 21.839844 L 22.855469 6.234375 C 23.191406 5.742188 23.0625 5.066406 22.566406 4.730469 Z" fill="#64748B"/></svg>
												</span>
												<span class="tooltip-text edit-tooltip">Edit</span>
												<span class="tooltip-text update-tooltip">Update</span>
												</span>
											</span>
											<span class="orderSheet-right">
											<input type="checkbox" name="header_fields_custom[]" value="<?php echo esc_attr( $wpssw_val ); ?>" class="headers_chk1" <?php echo esc_html( $wpssw_is_check ); ?> hidden="true"><input type="checkbox" name="header_fields[]" value="<?php echo esc_attr( $wpssw_val ); ?>" id="<?php echo esc_attr( $wpssw_labelid ); ?>" class="headers_chk" <?php echo esc_html( $wpssw_is_check ); ?>>
												<span class="checkbox-switch-new"></span>
												<span class="ui-icon ui-icon-caret-2-n-s wpssw-tooltio-link">
													<svg width="17" height="16" viewBox="0 0 17 16" fill="none" xmlns="http://www.w3.org/2000/svg">
			<mask id="mask0_384_3228" style="mask-type:alpha" maskUnits="userSpaceOnUse" x="0" y="0" width="17" height="16">
			<rect x="0.5" width="16" height="16" fill="#D9D9D9"/>
			</mask>
			<g mask="url(#mask0_384_3228)">
			<path d="M5.95875 13.67C5.55759 13.67 5.21418 13.5272 4.92851 13.2415C4.64284 12.9558 4.5 12.6124 4.5 12.2113C4.5 11.8101 4.64284 11.4667 4.92851 11.181C5.21418 10.8953 5.55759 10.7525 5.95875 10.7525C6.35991 10.7525 6.70332 10.8953 6.98899 11.181C7.27466 11.4667 7.4175 11.8101 7.4175 12.2113C7.4175 12.6124 7.27466 12.9558 6.98899 13.2415C6.70332 13.5272 6.35991 13.67 5.95875 13.67ZM10.335 13.67C9.93384 13.67 9.59043 13.5272 9.30476 13.2415C9.01909 12.9558 8.87625 12.6124 8.87625 12.2113C8.87625 11.8101 9.01909 11.4667 9.30476 11.181C9.59043 10.8953 9.93384 10.7525 10.335 10.7525C10.7362 10.7525 11.0796 10.8953 11.3652 11.181C11.6509 11.4667 11.7937 11.8101 11.7937 12.2113C11.7937 12.6124 11.6509 12.9558 11.3652 13.2415C11.0796 13.5272 10.7362 13.67 10.335 13.67ZM5.95875 9.29375C5.55759 9.29375 5.21418 9.15091 4.92851 8.86524C4.64284 8.57957 4.5 8.23616 4.5 7.835C4.5 7.43384 4.64284 7.09043 4.92851 6.80476C5.21418 6.51909 5.55759 6.37625 5.95875 6.37625C6.35991 6.37625 6.70332 6.51909 6.98899 6.80476C7.27466 7.09043 7.4175 7.43384 7.4175 7.835C7.4175 8.23616 7.27466 8.57957 6.98899 8.86524C6.70332 9.15091 6.35991 9.29375 5.95875 9.29375ZM10.335 9.29375C9.93384 9.29375 9.59043 9.15091 9.30476 8.86524C9.01909 8.57957 8.87625 8.23616 8.87625 7.835C8.87625 7.43384 9.01909 7.09043 9.30476 6.80476C9.59043 6.51909 9.93384 6.37625 10.335 6.37625C10.7362 6.37625 11.0796 6.51909 11.3652 6.80476C11.6509 7.09043 11.7937 7.43384 11.7937 7.835C11.7937 8.23616 11.6509 8.57957 11.3652 8.86524C11.0796 9.15091 10.7362 9.29375 10.335 9.29375ZM5.95875 4.9175C5.55759 4.9175 5.21418 4.77466 4.92851 4.48899C4.64284 4.20332 4.5 3.85991 4.5 3.45875C4.5 3.05759 4.64284 2.71418 4.92851 2.42851C5.21418 2.14284 5.55759 2 5.95875 2C6.35991 2 6.70332 2.14284 6.98899 2.42851C7.27466 2.71418 7.4175 3.05759 7.4175 3.45875C7.4175 3.85991 7.27466 4.20332 6.98899 4.48899C6.70332 4.77466 6.35991 4.9175 5.95875 4.9175ZM10.335 4.9175C9.93384 4.9175 9.59043 4.77466 9.30476 4.48899C9.01909 4.20332 8.87625 3.85991 8.87625 3.45875C8.87625 3.05759 9.01909 2.71418 9.30476 2.42851C9.59043 2.14284 9.93384 2 10.335 2C10.7362 2 11.0796 2.14284 11.3652 2.42851C11.6509 2.71418 11.7937 3.05759 11.7937 3.45875C11.7937 3.85991 11.6509 4.20332 11.3652 4.48899C11.0796 4.77466 10.7362 4.9175 10.335 4.9175Z" fill="#64748B"/>
			</g>
			</svg>
<span class="tooltip-text">Drag & Drop</span>
												</span>
											</span>
										</label>
									</li>
									<?php
								}
							}
							?>
						</ul>
					</div>
						<input type="hidden" id="prdwise" value="<?php echo esc_attr( implode( ',', $wpssw_prdwise ) ); ?>">
						<input type="hidden" id="ordwise" value="<?php echo esc_attr( implode( ',', $wpssw_ordwise ) ); ?>">
					</div>
				</div>
			</div>
			<?php
		}
		/**
		 * Output product names as headers.
		 *
		 * @param int $flag .
		 */
		public static function wpssw_woocommerce_admin_field_product_headers( $flag = 0 ) {
			$wpssw_selections        = stripslashes_deep( parent::wpssw_option( 'wpssw_product_sheet_headers_list' ) );
			$wpssw_selections_custom = stripslashes_deep( parent::wpssw_option( 'wpssw_product_sheet_headers_list_custom' ) );
			if ( ! $wpssw_selections ) {
				$wpssw_selections = array();
			}
			if ( ! $wpssw_selections_custom ) {
				$wpssw_selections_custom = array();
			}
			$prdassheetheaders = parent::wpssw_option( 'wpssw_prdassheetheaders' );
			if ( ! empty( $prdassheetheaders ) || $flag ) {
				$args               = array(
					'post_type'      => 'product',
					'posts_per_page' => -1,
					'fields'         => 'ids',
				);
				$wpssw_product_list = array();
				$loop               = new WP_Query( $args );
				$product_ids        = $loop->posts;
				$product_ids        = array_filter( $product_ids );
				foreach ( $product_ids as $pid ) {
					$wpssw_product_list[] = html_entity_decode( get_the_title( $pid ), ENT_QUOTES );
				}
				wp_reset_postdata();
				$wpssw_product_list = array_unique( array_filter( $wpssw_product_list ) );
				?>
				<div class="td-prd-wpssw-headers sheetHeaders-section ord_spreadsheet_row">
					<div class="td-wpssw-headers">
						<div class='wpssw_headers'>
							<label for="sheet_headers"></label>
							<div class="selectall-btnset">
							<button type="button" class="wpssw-button wpssw-button-primary" id="prdselectall" ><?php esc_html_e( 'Select All', 'wpssw' ); ?></button>                
							<button type="button" class="wpssw-button wpssw-button-secondary" id="prdselectnone" ><?php esc_html_e( 'Select None', 'wpssw' ); ?></button>
						</div>
							<ul id="product-sortable" class="sheetHeaders-box">
								<?php
								if ( ! empty( $wpssw_selections ) ) {
									foreach ( $wpssw_selections as $wpssw_key => $wpssw_val ) {
										$wpssw_is_check = 'checked';
										$wpssw_labelid  = strtolower( str_replace( ' ', '_', $wpssw_val ) );
										?>
										<li class="default-order-sheet ui-state-default">
											<label for="<?php echo esc_attr( $wpssw_labelid ); ?>">
												<span class="orderSheet-left">
												<span class="wootextfield"><?php echo isset( $wpssw_selections_custom[ $wpssw_key ] ) ? esc_attr( $wpssw_selections_custom[ $wpssw_key ] ) : esc_attr( $wpssw_val ); ?>
												</span>
												<span class="ui-icon ui-icon-pencil wpssw-tooltio-link">
													<span class="pencil-icon">
													<svg width="12" height="12" viewBox="0 0 13 13" fill="none" xmlns="http://www.w3.org/2000/svg">
													<path d="M1.33333 11.6668H2.26667L8.01667 5.91677L7.08333 4.98343L1.33333 10.7334V11.6668ZM10.8667 4.9501L8.03333 2.1501L8.96667 1.21677C9.22222 0.961213 9.53611 0.833435 9.90833 0.833435C10.2806 0.833435 10.5944 0.961213 10.85 1.21677L11.7833 2.1501C12.0389 2.40566 12.1722 2.71399 12.1833 3.0751C12.1944 3.43621 12.0722 3.74455 11.8167 4.0001L10.8667 4.9501ZM9.9 5.93343L2.83333 13.0001H0V10.1668L7.06667 3.1001L9.9 5.93343Z" fill="#64748B"/>
													</svg>
													</span>
													<span class="check-icon">
														<svg xmlns="http://www.w3.org/2000/svg"  viewBox="0 0 26 26" width="12" height="12">
															<path d="M 22.566406 4.730469 L 20.773438 3.511719 C 20.277344 3.175781 19.597656 3.304688 19.265625 3.796875 L 10.476563 16.757813 L 6.4375 12.71875 C 6.015625 12.296875 5.328125 12.296875 4.90625 12.71875 L 3.371094 14.253906 C 2.949219 14.675781 2.949219 15.363281 3.371094 15.789063 L 9.582031 22 C 9.929688 22.347656 10.476563 22.613281 10.96875 22.613281 C 11.460938 22.613281 11.957031 22.304688 12.277344 21.839844 L 22.855469 6.234375 C 23.191406 5.742188 23.0625 5.066406 22.566406 4.730469 Z" fill="#64748B"/></svg>
													</span>
													<span class="tooltip-text edit-tooltip">Edit</span>
													<span class="tooltip-text update-tooltip">Update</span>
												</span>
											</span>
											<span class="orderSheet-right">
												<input type="checkbox" name="product_header_fields_custom[]" value="<?php echo isset( $wpssw_selections_custom[ $wpssw_key ] ) ? esc_attr( $wpssw_selections_custom[ $wpssw_key ] ) : esc_attr( $wpssw_val ); ?>" class="prdheaders_chk1" <?php echo esc_html( $wpssw_is_check ); ?> hidden="true">
												<input type="checkbox" name="product_header_fields[]" value="<?php echo esc_attr( $wpssw_val ); ?>" id="<?php echo esc_attr( $wpssw_labelid ); ?>" class="prdheaders_chk" <?php echo esc_html( $wpssw_is_check ); ?> >
												<span class="checkbox-switch-new"></span>
												<span class="ui-icon ui-icon-caret-2-n-s wpssw-tooltio-link">
													<svg width="8" height="12" viewBox="0 0 8 12" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M1.95875 11.67C1.55759 11.67 1.21418 11.5272 0.928508 11.2415C0.642836 10.9558 0.5 10.6124 0.5 10.2113C0.5 9.81009 0.642836 9.46668 0.928508 9.18101C1.21418 8.89534 1.55759 8.7525 1.95875 8.7525C2.35991 8.7525 2.70332 8.89534 2.98899 9.18101C3.27466 9.46668 3.4175 9.81009 3.4175 10.2113C3.4175 10.6124 3.27466 10.9558 2.98899 11.2415C2.70332 11.5272 2.35991 11.67 1.95875 11.67ZM6.335 11.67C5.93384 11.67 5.59043 11.5272 5.30476 11.2415C5.01909 10.9558 4.87625 10.6124 4.87625 10.2113C4.87625 9.81009 5.01909 9.46668 5.30476 9.18101C5.59043 8.89534 5.93384 8.7525 6.335 8.7525C6.73616 8.7525 7.07957 8.89534 7.36524 9.18101C7.65091 9.46668 7.79375 9.81009 7.79375 10.2113C7.79375 10.6124 7.65091 10.9558 7.36524 11.2415C7.07957 11.5272 6.73616 11.67 6.335 11.67ZM1.95875 7.29375C1.55759 7.29375 1.21418 7.15091 0.928508 6.86524C0.642836 6.57957 0.5 6.23616 0.5 5.835C0.5 5.43384 0.642836 5.09043 0.928508 4.80476C1.21418 4.51909 1.55759 4.37625 1.95875 4.37625C2.35991 4.37625 2.70332 4.51909 2.98899 4.80476C3.27466 5.09043 3.4175 5.43384 3.4175 5.835C3.4175 6.23616 3.27466 6.57957 2.98899 6.86524C2.70332 7.15091 2.35991 7.29375 1.95875 7.29375ZM6.335 7.29375C5.93384 7.29375 5.59043 7.15091 5.30476 6.86524C5.01909 6.57957 4.87625 6.23616 4.87625 5.835C4.87625 5.43384 5.01909 5.09043 5.30476 4.80476C5.59043 4.51909 5.93384 4.37625 6.335 4.37625C6.73616 4.37625 7.07957 4.51909 7.36524 4.80476C7.65091 5.09043 7.79375 5.43384 7.79375 5.835C7.79375 6.23616 7.65091 6.57957 7.36524 6.86524C7.07957 7.15091 6.73616 7.29375 6.335 7.29375ZM1.95875 2.9175C1.55759 2.9175 1.21418 2.77466 0.928508 2.48899C0.642836 2.20332 0.5 1.85991 0.5 1.45875C0.5 1.05759 0.642836 0.71418 0.928508 0.428508C1.21418 0.142836 1.55759 0 1.95875 0C2.35991 0 2.70332 0.142836 2.98899 0.428508C3.27466 0.71418 3.4175 1.05759 3.4175 1.45875C3.4175 1.85991 3.27466 2.20332 2.98899 2.48899C2.70332 2.77466 2.35991 2.9175 1.95875 2.9175ZM6.335 2.9175C5.93384 2.9175 5.59043 2.77466 5.30476 2.48899C5.01909 2.20332 4.87625 1.85991 4.87625 1.45875C4.87625 1.05759 5.01909 0.71418 5.30476 0.428508C5.59043 0.142836 5.93384 0 6.335 0C6.73616 0 7.07957 0.142836 7.36524 0.428508C7.65091 0.71418 7.79375 1.05759 7.79375 1.45875C7.79375 1.85991 7.65091 2.20332 7.36524 2.48899C7.07957 2.77466 6.73616 2.9175 6.335 2.9175Z" fill="#64748B"/>
</svg>
<span class="tooltip-text">Drag & Drop</span>
												</span>
											</span>
											</label>
										</li>
										<?php
									}
								}
								if ( ! empty( $wpssw_product_list ) ) {
									foreach ( $wpssw_product_list as $wpssw_key => $wpssw_val ) {
										$wpssw_is_check = '';
										if ( in_array( $wpssw_val, $wpssw_selections, true ) ) {
											continue;
										}
										$wpssw_labelid = strtolower( str_replace( ' ', '_', $wpssw_val ) );
										?>
										<li class="default-order-sheet ui-state-default">
											<label for="<?php echo esc_html( $wpssw_labelid ); ?>">
												<span class="orderSheet-left">
												<span class="wootextfield"><?php echo esc_attr( $wpssw_val ); ?></span>
												<span class="ui-icon ui-icon-pencil">
													<span class="pencil-icon">
													<svg width="12" height="12" viewBox="0 0 13 13" fill="none" xmlns="http://www.w3.org/2000/svg">
													<path d="M1.33333 11.6668H2.26667L8.01667 5.91677L7.08333 4.98343L1.33333 10.7334V11.6668ZM10.8667 4.9501L8.03333 2.1501L8.96667 1.21677C9.22222 0.961213 9.53611 0.833435 9.90833 0.833435C10.2806 0.833435 10.5944 0.961213 10.85 1.21677L11.7833 2.1501C12.0389 2.40566 12.1722 2.71399 12.1833 3.0751C12.1944 3.43621 12.0722 3.74455 11.8167 4.0001L10.8667 4.9501ZM9.9 5.93343L2.83333 13.0001H0V10.1668L7.06667 3.1001L9.9 5.93343Z" fill="#64748B"/>
													</svg>
													</span>
													<span class="check-icon">
														<svg xmlns="http://www.w3.org/2000/svg"  viewBox="0 0 26 26" width="12" height="12">
															<path d="M 22.566406 4.730469 L 20.773438 3.511719 C 20.277344 3.175781 19.597656 3.304688 19.265625 3.796875 L 10.476563 16.757813 L 6.4375 12.71875 C 6.015625 12.296875 5.328125 12.296875 4.90625 12.71875 L 3.371094 14.253906 C 2.949219 14.675781 2.949219 15.363281 3.371094 15.789063 L 9.582031 22 C 9.929688 22.347656 10.476563 22.613281 10.96875 22.613281 C 11.460938 22.613281 11.957031 22.304688 12.277344 21.839844 L 22.855469 6.234375 C 23.191406 5.742188 23.0625 5.066406 22.566406 4.730469 Z" fill="#64748B"/></svg>
													</span>
												</span>
											</span>
											<span class="orderSheet-right">
												<input type="checkbox" name="product_header_fields_custom[]" value="<?php echo esc_attr( $wpssw_val ); ?>" class="prdheaders_chk1" <?php echo esc_html( $wpssw_is_check ); ?> hidden="true"><input type="checkbox" name="product_header_fields[]" value="<?php echo esc_attr( $wpssw_val ); ?>" id="<?php echo esc_attr( $wpssw_labelid ); ?>" class="prdheaders_chk" <?php echo esc_html( $wpssw_is_check ); ?>>

												<span class="checkbox-switch-new"></span>
												<span class="ui-icon ui-icon-caret-2-n-s">
													<svg width="8" height="12" viewBox="0 0 8 12" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M1.95875 11.67C1.55759 11.67 1.21418 11.5272 0.928508 11.2415C0.642836 10.9558 0.5 10.6124 0.5 10.2113C0.5 9.81009 0.642836 9.46668 0.928508 9.18101C1.21418 8.89534 1.55759 8.7525 1.95875 8.7525C2.35991 8.7525 2.70332 8.89534 2.98899 9.18101C3.27466 9.46668 3.4175 9.81009 3.4175 10.2113C3.4175 10.6124 3.27466 10.9558 2.98899 11.2415C2.70332 11.5272 2.35991 11.67 1.95875 11.67ZM6.335 11.67C5.93384 11.67 5.59043 11.5272 5.30476 11.2415C5.01909 10.9558 4.87625 10.6124 4.87625 10.2113C4.87625 9.81009 5.01909 9.46668 5.30476 9.18101C5.59043 8.89534 5.93384 8.7525 6.335 8.7525C6.73616 8.7525 7.07957 8.89534 7.36524 9.18101C7.65091 9.46668 7.79375 9.81009 7.79375 10.2113C7.79375 10.6124 7.65091 10.9558 7.36524 11.2415C7.07957 11.5272 6.73616 11.67 6.335 11.67ZM1.95875 7.29375C1.55759 7.29375 1.21418 7.15091 0.928508 6.86524C0.642836 6.57957 0.5 6.23616 0.5 5.835C0.5 5.43384 0.642836 5.09043 0.928508 4.80476C1.21418 4.51909 1.55759 4.37625 1.95875 4.37625C2.35991 4.37625 2.70332 4.51909 2.98899 4.80476C3.27466 5.09043 3.4175 5.43384 3.4175 5.835C3.4175 6.23616 3.27466 6.57957 2.98899 6.86524C2.70332 7.15091 2.35991 7.29375 1.95875 7.29375ZM6.335 7.29375C5.93384 7.29375 5.59043 7.15091 5.30476 6.86524C5.01909 6.57957 4.87625 6.23616 4.87625 5.835C4.87625 5.43384 5.01909 5.09043 5.30476 4.80476C5.59043 4.51909 5.93384 4.37625 6.335 4.37625C6.73616 4.37625 7.07957 4.51909 7.36524 4.80476C7.65091 5.09043 7.79375 5.43384 7.79375 5.835C7.79375 6.23616 7.65091 6.57957 7.36524 6.86524C7.07957 7.15091 6.73616 7.29375 6.335 7.29375ZM1.95875 2.9175C1.55759 2.9175 1.21418 2.77466 0.928508 2.48899C0.642836 2.20332 0.5 1.85991 0.5 1.45875C0.5 1.05759 0.642836 0.71418 0.928508 0.428508C1.21418 0.142836 1.55759 0 1.95875 0C2.35991 0 2.70332 0.142836 2.98899 0.428508C3.27466 0.71418 3.4175 1.05759 3.4175 1.45875C3.4175 1.85991 3.27466 2.20332 2.98899 2.48899C2.70332 2.77466 2.35991 2.9175 1.95875 2.9175ZM6.335 2.9175C5.93384 2.9175 5.59043 2.77466 5.30476 2.48899C5.01909 2.20332 4.87625 1.85991 4.87625 1.45875C4.87625 1.05759 5.01909 0.71418 5.30476 0.428508C5.59043 0.142836 5.93384 0 6.335 0C6.73616 0 7.07957 0.142836 7.36524 0.428508C7.65091 0.71418 7.79375 1.05759 7.79375 1.45875C7.79375 1.85991 7.65091 2.20332 7.36524 2.48899C7.07957 2.77466 6.73616 2.9175 6.335 2.9175Z" fill="#64748B"/>
</svg>
												</span>
											</span>
											</label>
										</li>
										<?php
									}
								}
								?>
							</ul>
						</div>
					</div>
				</div>
				<?php } else { ?>
			<div class="generalSetting-section td-prd-wpssw-headers ord_spreadsheet_row">
			</div>
					<?php
				}
		}
		/**
		 * Output Product name as sheets headers.
		 */
		public static function wpssw_woocommerce_admin_field_product_as_sheet_header() {
			$prdassheetheaders = parent::wpssw_option( 'wpssw_prdassheetheaders' );
			if ( ! $prdassheetheaders ) {
				$prdassheetheaders = '';
			}

			?>
			<div class="generalSetting-section order-setting-prd-name-sheet-row sheetHeaders-section ord_spreadsheet_row" id="custom-disable-id">
				<div>
					<div class="order-setting-prd-name-sheet-section">
						<div class="generalSetting-left">
							<h4><?php echo esc_html__( 'Product Name as Sheets Headers', 'wpssw' ); ?></h4>
							<p><?php echo esc_html__( "By enabling this option, our plugin will automatically create columns in the spreadsheet for all the selected product names. These columns will display their respective quantities and be added after the header option you've chosen in the 'Append after' dropdown.", 'wpssw' ); ?></p>
						</div>
						<div class="generalSetting-right order-setting-prd-name-sheet-right-section ">
							<label for="prdassheetheaders">
								<img id="loaderprdheader" src="<?php dirname( __FILE__ ); ?>images/spinner.gif">
								<input name="prdassheetheaders" id="prdassheetheaders" type="checkbox" class="" value="yes" 
								<?php
								if ( 'yes' === (string) $prdassheetheaders ) {
									echo 'checked';}
								?>
								><span class="checkbox-switch"></span>					
							</label>
						</div>
					</div>
				<?php
				$wpssw_selections        = stripslashes_deep( parent::wpssw_option( 'wpssw_product_sheet_headers_list' ) );
				$wpssw_selections_custom = stripslashes_deep( parent::wpssw_option( 'wpssw_product_sheet_headers_list_custom' ) );

				if ( ! $wpssw_selections ) {
					$wpssw_selections = array();
				}
				if ( ! $wpssw_selections_custom ) {
					$wpssw_selections_custom = array();
				}
				$prdassheetheaders = parent::wpssw_option( 'wpssw_prdassheetheaders' );
				if ( ! empty( $prdassheetheaders ) ) {
					$args               = array(
						'post_type'      => 'product',
						'posts_per_page' => -1,
						'fields'         => 'ids',
					);
					$wpssw_product_list = array();
					$loop               = new WP_Query( $args );
					$product_ids        = $loop->posts;
					$product_ids        = array_filter( $product_ids );
					foreach ( $product_ids as $pid ) {
						$wpssw_product_list[] = html_entity_decode( get_the_title( $pid ), ENT_QUOTES );
					}
					wp_reset_postdata();
					$wpssw_product_list = array_unique( array_filter( $wpssw_product_list ) );
					?>
					<div class="td-prd-wpssw-headers">
						<div class="td-wpssw-headers">
							<div class='wpssw_headers'>
								<label for="sheet_headers"></label>
								<div class="selectall-btnset">
								<button type="button" class="wpssw-button wpssw-button-primary" id="prdselectall" ><?php esc_html_e( 'Select All', 'wpssw' ); ?></button>                
								<button type="button" class="wpssw-button wpssw-button-secondary" id="prdselectnone" ><?php esc_html_e( 'Select None', 'wpssw' ); ?></button>
							</div>								
								<ul id="product-sortable" class="sheetHeaders-box">
								<?php
								if ( ! empty( $wpssw_selections ) ) {
									foreach ( $wpssw_selections as $wpssw_key => $wpssw_val ) {
										$wpssw_is_check = 'checked';
										$wpssw_labelid  = strtolower( str_replace( ' ', '_', $wpssw_val ) );
										?>
											<li class="default-order-sheet ui-state-default">
												<label for="<?php echo esc_attr( $wpssw_labelid ); ?>">
													<span class="orderSheet-left">
														<span class="wootextfield"><?php echo isset( $wpssw_selections_custom[ $wpssw_key ] ) ? esc_attr( $wpssw_selections_custom[ $wpssw_key ] ) : esc_attr( $wpssw_val ); ?></span>
														<span class="ui-icon ui-icon-pencil wpssw-tooltio-link">
															<span class="pencil-icon">
																<svg width="12" height="12" viewBox="0 0 13 13" fill="none" xmlns="http://www.w3.org/2000/svg">
																	<path d="M1.33333 11.6668H2.26667L8.01667 5.91677L7.08333 4.98343L1.33333 10.7334V11.6668ZM10.8667 4.9501L8.03333 2.1501L8.96667 1.21677C9.22222 0.961213 9.53611 0.833435 9.90833 0.833435C10.2806 0.833435 10.5944 0.961213 10.85 1.21677L11.7833 2.1501C12.0389 2.40566 12.1722 2.71399 12.1833 3.0751C12.1944 3.43621 12.0722 3.74455 11.8167 4.0001L10.8667 4.9501ZM9.9 5.93343L2.83333 13.0001H0V10.1668L7.06667 3.1001L9.9 5.93343Z" fill="#64748B"/>
																</svg>
															</span>
															<span class="check-icon">
																<svg xmlns="http://www.w3.org/2000/svg"  viewBox="0 0 26 26" width="12" height="12">
																	<path d="M 22.566406 4.730469 L 20.773438 3.511719 C 20.277344 3.175781 19.597656 3.304688 19.265625 3.796875 L 10.476563 16.757813 L 6.4375 12.71875 C 6.015625 12.296875 5.328125 12.296875 4.90625 12.71875 L 3.371094 14.253906 C 2.949219 14.675781 2.949219 15.363281 3.371094 15.789063 L 9.582031 22 C 9.929688 22.347656 10.476563 22.613281 10.96875 22.613281 C 11.460938 22.613281 11.957031 22.304688 12.277344 21.839844 L 22.855469 6.234375 C 23.191406 5.742188 23.0625 5.066406 22.566406 4.730469 Z" fill="#64748B"/>
																</svg>
															</span>
															<span class="tooltip-text edit-tooltip">Edit</span>
															<span class="tooltip-text update-tooltip">Update</span>
														</span>
													</span>
													<span class="orderSheet-right">
														<input type="checkbox" name="product_header_fields_custom[]" value="<?php echo isset( $wpssw_selections_custom[ $wpssw_key ] ) ? esc_attr( $wpssw_selections_custom[ $wpssw_key ] ) : esc_attr( $wpssw_val ); ?>" class="prdheaders_chk1" <?php echo esc_html( $wpssw_is_check ); ?> hidden="true">
														<input type="checkbox" name="product_header_fields[]" value="<?php echo esc_attr( $wpssw_val ); ?>" id="<?php echo esc_attr( $wpssw_labelid ); ?>" class="prdheaders_chk" <?php echo esc_html( $wpssw_is_check ); ?> >
														<span class="checkbox-switch-new"></span>			
													</span>
												</label>
											</li>
											<?php
									}
								}
								if ( ! empty( $wpssw_product_list ) ) {
									foreach ( $wpssw_product_list as $wpssw_key => $wpssw_val ) {
										$wpssw_is_check = '';
										if ( in_array( $wpssw_val, $wpssw_selections, true ) ) {
											continue;
										}
										$wpssw_labelid = strtolower( str_replace( ' ', '_', $wpssw_val ) );
										?>
											<li class="default-order-sheet ui-state-default">
												<label for="<?php echo esc_attr( $wpssw_labelid ); ?>">
													<span class="orderSheet-left">
														<span class="wootextfield"><?php echo esc_attr( $wpssw_val ); ?></span>
														<span class="ui-icon ui-icon-pencil wpssw-tooltio-link">
															<span class="pencil-icon">
																<svg width="12" height="12" viewBox="0 0 13 13" fill="none" xmlns="http://www.w3.org/2000/svg">
																	<path d="M1.33333 11.6668H2.26667L8.01667 5.91677L7.08333 4.98343L1.33333 10.7334V11.6668ZM10.8667 4.9501L8.03333 2.1501L8.96667 1.21677C9.22222 0.961213 9.53611 0.833435 9.90833 0.833435C10.2806 0.833435 10.5944 0.961213 10.85 1.21677L11.7833 2.1501C12.0389 2.40566 12.1722 2.71399 12.1833 3.0751C12.1944 3.43621 12.0722 3.74455 11.8167 4.0001L10.8667 4.9501ZM9.9 5.93343L2.83333 13.0001H0V10.1668L7.06667 3.1001L9.9 5.93343Z" fill="#64748B"/>
																</svg>
															</span>
															<span class="check-icon">
																<svg xmlns="http://www.w3.org/2000/svg"  viewBox="0 0 26 26" width="12" height="12">
																	<path d="M 22.566406 4.730469 L 20.773438 3.511719 C 20.277344 3.175781 19.597656 3.304688 19.265625 3.796875 L 10.476563 16.757813 L 6.4375 12.71875 C 6.015625 12.296875 5.328125 12.296875 4.90625 12.71875 L 3.371094 14.253906 C 2.949219 14.675781 2.949219 15.363281 3.371094 15.789063 L 9.582031 22 C 9.929688 22.347656 10.476563 22.613281 10.96875 22.613281 C 11.460938 22.613281 11.957031 22.304688 12.277344 21.839844 L 22.855469 6.234375 C 23.191406 5.742188 23.0625 5.066406 22.566406 4.730469 Z" fill="#64748B"/>
																</svg>
															</span>
															<span class="tooltip-text edit-tooltip">Edit</span>
															<span class="tooltip-text update-tooltip">Update</span>
														</span>
													</span>
													<span class="orderSheet-right">
														<input type="checkbox" name="product_header_fields_custom[]" value="<?php echo esc_attr( $wpssw_val ); ?>" class="prdheaders_chk1" <?php echo esc_html( $wpssw_is_check ); ?> hidden="true">
														<input type="checkbox" name="product_header_fields[]" value="<?php echo esc_attr( $wpssw_val ); ?>" id="<?php echo esc_attr( $wpssw_labelid ); ?>" class="prdheaders_chk" <?php echo esc_html( $wpssw_is_check ); ?> >
														<span class="checkbox-switch-new"></span>
													</span>
												</label>
											</li>
											<?php
									}
								}
								?>
								</ul>
							</div>
						</div>
					</div>
				<?php } else { ?>
					<div class="td-prd-wpssw-headers">
					</div>
					<?php
				}
				?>
				</div>
			</div>
			<?php
		}
		/**
		 *
		 * Output append after dropdown setting for product names as sheet headers.
		 */
		public static function wpssw_woocommerce_admin_field_product_headers_append_after() {
			global $wpssw_global_headers;
			$wpssw_append_after_array = parent::wpssw_option( 'wpssw_append_after_array' );
			$wpssw_append_after       = parent::wpssw_option( 'wpssw_append_after' );
			$wpssw_global_headers     = $wpssw_append_after_array ? $wpssw_append_after_array : $wpssw_global_headers;
			$selected                 = '';
			?>
			<div class="generalSetting-section td-prd-append-after ord_spreadsheet_row">
				<div class="generalSetting-left">
					<h4><?php echo esc_html__( 'Append After', 'wpssw' ); ?> </h4>
					<p><?php echo esc_html__( 'The header for the above-selected product names will be inserted into the sheet immediately after the chosen option you have specified.', 'wpssw' ); ?></p>
				</div>
				<div class="forminp forminp-select generalSetting-right">
					<select name="wpssw_append_after" id="wpssw_append_after" class="">
				<?php
				foreach ( $wpssw_global_headers as $key => $headers ) {
					if ( str_replace( ' ', '-', strtolower( $wpssw_append_after ) ) === (string) $key || $headers === $wpssw_append_after ) {
						$selected = 'selected="selected"';
					}
					$headerkey = '';
					?>
							<option value="<?php echo ! is_numeric( $key ) ? esc_attr( $key ) : esc_attr( $headers ); ?>" <?php echo esc_html( $selected ); ?>><?php echo esc_html( $headers ); ?></option>    
							<?php
							$selected = '';
				}
				?>
					</select> 							
				</div>
			</div>
			<?php
		}
		/**
		 * Output radio button field.
		 */
		public static function wpssw_woocommerce_admin_field_manage_row_field() {

			$wpssw_header_format = parent::wpssw_option( 'wpssw_header_format' );
			if ( ! empty( $wpssw_header_format ) ) {
				if ( 'orderwise' === (string) $wpssw_header_format ) {
					$wpssw_orderwise   = "checked='checked' disabled='disabled'";
					$wpssw_productwise = "disabled='disabled'";
				} else {
					$wpssw_productwise = "checked='checked' disabled='disabled'";
					$wpssw_orderwise   = "disabled='disabled'";
				}
				$wpssw_disableclass = 'disabled';
			} else {
				$wpssw_orderwise    = "checked='checked'";
				$wpssw_productwise  = '';
				$wpssw_disableclass = '';
			}
			?>
			<div class="generalSetting-section ord_spreadsheet_row">
					<div class="generalSetting-left">
					<h4><?php echo esc_html__( 'Manage Row Data', 'wpssw' ); ?></h4>
					<p><?php echo esc_html__( 'This setting lets you choose how your data will be organized in the sheet. If you pick "Order-wise," all the order data will be written in a single line. On the other hand, if you go for "Product-wise," each product will have its own separate line. ', 'wpssw' ); ?> <br><strong><?php echo esc_html__( 'Remember, this option is only available when creating a new spreadsheet.', 'wpssw' ); ?></strong></p>
				<div class="forminp radio-box-td generalSetting-right createanew-radio mb-0">
					<input name="header_format" id="orderwise" class="manage-row <?php echo esc_attr( $wpssw_disableclass ); ?>" value="orderwise" type="radio" <?php echo esc_html( $wpssw_orderwise ); ?>><label for="orderwise"><?php echo esc_html__( 'Order Wise', 'wpssw' ); ?></label> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
					<input name="header_format" id="productwise" class="manage-row <?php echo esc_attr( $wpssw_disableclass ); ?>" value="productwise" type="radio" <?php echo esc_html( $wpssw_productwise ); ?>><label for="productwise"><?php echo esc_html__( 'Product Wise', 'wpssw' ); ?></label>
				</div>
				</div>
			</div>
					<?php
		}
		/**
		 * Output Syncronization Button
		 */
		public static function wpssw_woocommerce_admin_field_sync_button() {
			$wpssw_selections             = parent::wpssw_option( 'wpssw_sheet_headers_list' );
			$wpssw_scheduling_enable      = parent::wpssw_option( 'wpssw_scheduling_enable' );
			$wpssw_scheduling_run_on      = parent::wpssw_option( 'wpssw_scheduling_run_on' );
			$wpssw_schedule_recurrence    = parent::wpssw_option( 'wpssw_schedule_recurrence' );
			$wpssw_scheduling_weekly_days = parent::wpssw_option( 'wpssw_scheduling_weekly_days' );
			$wpssw_scheduling_date        = parent::wpssw_option( 'wpssw_scheduling_date' );
			$wpssw_scheduling_time        = parent::wpssw_option( 'wpssw_scheduling_time' );

			$wpssw_scheduling_weekly_days_array = array();
			if ( ! empty( $wpssw_scheduling_weekly_days ) ) {
				$wpssw_scheduling_weekly_days_array = explode( ',', $wpssw_scheduling_weekly_days );
			}
			$wpssw_active     = '';
			$wpssw_deactive   = '';
			$wpssw_recurrence = '';
			$wpssw_weekly     = '';
			$wpssw_onetime    = '';
			if ( 1 === (int) $wpssw_scheduling_enable ) {
				$wpssw_active = 'checked=checked';
			} else {
				$wpssw_deactive = 'checked=checked';
			}

			if ( 'onetime' === (string) $wpssw_scheduling_run_on && $wpssw_scheduling_date && $wpssw_scheduling_time ) {
				$starttime = strtotime( $wpssw_scheduling_date . ' ' . $wpssw_scheduling_time );
				if ( date_i18n( 'Y-m-d H:i' ) > date_i18n( 'Y-m-d H:i', $starttime ) ) {
					$wpssw_scheduling_date   = '';
					$wpssw_scheduling_time   = '';
					$wpssw_scheduling_run_on = '';
					$wpssw_scheduling_enable = '';

					parent::wpssw_update_option( 'wpssw_scheduling_enable' );
					parent::wpssw_update_option( 'wpssw_scheduling_run_on' );
					parent::wpssw_update_option( 'wpssw_scheduling_date' );
					parent::wpssw_update_option( 'wpssw_scheduling_time' );

				}
			}

			if ( 'recurrence' === (string) $wpssw_scheduling_run_on ) {
				$wpssw_recurrence = 'checked=checked';
			}
			if ( 'onetime' === (string) $wpssw_scheduling_run_on ) {
				$wpssw_onetime = 'checked=checked';
			}
			if ( 'weekly' === (string) $wpssw_scheduling_run_on ) {
				$wpssw_weekly = 'checked=checked';
			}
			if ( empty( $wpssw_recurrence ) && empty( $wpssw_onetime ) && empty( $wpssw_weekly ) ) {
				$wpssw_recurrence = 'checked=checked';
			}
			$wpssw_interval = array(
				'ten_minutes'    => 'Every Ten Minutes',
				'thirty_minutes' => 'Every Thirty Minutes',
				'once_daily'     => 'Once Daily',
				'twice_daily'    => 'Twice Daily',
				'fifteen_days'   => 'Every Fifteen Days',
				'monthly'        => 'Monthly',
			);
			$wpssw_weekday  = array(
				'monday'    => 'Mon',
				'tuesday'   => 'Tue',
				'wednesday' => 'Wed',
				'thursday'  => 'Thu',
				'friday'    => 'Fri',
				'saturday'  => 'Sat',
				'sunday'    => 'Sun',
			);
			if ( ! empty( $wpssw_selections ) ) {
				$wpssw_order_spreadsheet_setting = self::wpssw_check_order_spreadsheet_setting();
				if ( 'yes' === (string) $wpssw_order_spreadsheet_setting ) {
					?>
				<div class="generalSetting-section synctr ord_spreadsheet_row">
					<div class="generalSetting-left">
						<h4>
							<span class="wpssw-tooltio-link tooltip-right">
								<?php echo esc_html__( 'Sync Orders', 'wpssw' ); ?>
								<span class="tooltip-text">Export</span>
							</span>
						</h4>
						<p><?php echo esc_html__( "By clicking the 'Click to Sync' button, all orders or orders within the custom date range that are not already present in the sheet will be appended. The existing orders in the sheet will not be updated, providing a seamless synchronization process.", 'wpssw' ); ?></p>
						<div class="sync_all_fromtodate-main">
						<div class="syncall-radio">
							<div class="syncall-radio-box">
								<input type="radio" name="sync_range" value="1" id="sync_all" checked="checked">
								<label for="sync_all"><?php echo esc_html__( 'All Orders', 'wpssw' ); ?></label>
							</div>
							<div class="syncall-radio-box">
								<input type="radio" name="sync_range" value="0" id="sync_daterange">
								<label for="sync_daterange"><?php echo esc_html__( 'Date Range', 'wpssw' ); ?></label>
							</div>
						</div>
						<div class="forminp forminp-select sync_all_fromtodate"> 
							<label for="sync_all_fromdate" >
								<?php echo esc_html__( 'From :', 'wpssw' ); ?>   <input name="sync_all_fromdate" id="sync_all_fromdate" class="sync_all_fromdate" type="date" >
							</label>
							<label for="sync_all_todate" class="sync_todate_label">
								<?php echo esc_html__( 'To :', 'wpssw' ); ?> <input name="sync_all_todate" id="sync_all_todate" class="sync_all_todate" type="date" >
							</label>
						</div>
						</div>
						<div class="sync-button-box">              
							<img src="<?php dirname( __FILE__ ); ?>images/spinner.gif" id="syncloader"><span id="synctext"><?php echo esc_html__( 'Synchronizing...', 'wpssw' ); ?></span>
							<a class="wpssw-button wpssw-button-secondary" href="javascript:void(0)" id="sync">
							<?php
							esc_html_e( 'Click to Sync', 'wpssw' );
							?>
							</a>
						</div>
					</div>
				</div>
				<?php } ?>
				<div valign="top" class="autosynctr generalSetting-section ord_spreadsheet_row">
					<div scope="row" class="titledesc generalSetting-left">
						<h4>
							<span class="wpssw-tooltio-link tooltip-right">
								<?php echo esc_html__( 'Schedule Auto Sync', 'wpssw' ); ?>
								<span class="tooltip-text">Export</span>
							</span>
						</h4>
						<p><?php echo esc_html__( 'Enabling this setting allows you to schedule automatic synchronization at specified intervals.', 'wpssw' ); ?></p>
						<div class="generalSetting-autosynctr-row td-radio-btn-row mb-0"> 
							<div class="schedulecontainer mb-0">
								<input type="radio" name="scheduling_enable" value="1" id="autoschedule" <?php echo esc_attr( $wpssw_active ); ?>>
								<label for="autoschedule"><?php echo esc_html__( 'Automatic Scheduling', 'wpssw' ); ?></label>
							</div>
							<div class="schedulecontainer mb-0">
								<input type="radio" name="scheduling_enable" value="0" <?php echo esc_attr( $wpssw_deactive ); ?> id="donotschedule">
								<label for="donotschedule"><?php echo esc_html__( 'Do Not Schedule', 'wpssw' ); ?></label>
							</div>         
						</div>
						<div class="generalSetting-autosynctr-row">
							<div id="automatic-scheduling">
								<div class="schedulecontainer">
									<div class="input">
										<input type="radio" name="scheduling_run_on" value="recurrence" id="recurrenceradio" <?php echo esc_attr( $wpssw_recurrence ); ?> >
										<label for="recurrenceradio"><?php echo esc_html__( 'Schedule Recurrence', 'wpssw' ); ?></label>
									</div>
									<select name="schedule_recurrence" id="schedule_recurrence">
										<?php
										foreach ( $wpssw_interval as $intkey => $intvalue ) {
											$selected = '';
											if ( (string) $intkey === (string) $wpssw_schedule_recurrence ) {
												$selected = 'selected="selected"';
											}
											?>
											<option value="<?php echo esc_attr( $intkey ); ?>" <?php echo esc_attr( $selected ); ?>><?php echo esc_html( $intvalue ); ?></option>
										<?php } ?>
									</select>
								</div>
								<div class="schedulecontainer">
									<div class="input">
										<input type="radio" name="scheduling_run_on" value="weekly" id="weeklyradio" <?php echo esc_attr( $wpssw_weekly ); ?>>
										<label for="weeklyradio"><?php echo esc_html__( 'Every week on...', 'wpssw' ); ?></label>
									</div>
									<input type="hidden" name="scheduling_weekly_days" value="<?php echo esc_attr( $wpssw_scheduling_weekly_days ); ?>" id="weekly_days">
									<ul class="days-of-week" id="weekly">
										<?php
										foreach ( $wpssw_weekday as $weekname => $weekshortname ) {
											$selectedday = '';
											if ( in_array( $weekname, $wpssw_scheduling_weekly_days_array, true ) ) {
												$selectedday = 'selected';
											}
											?>
										<li data-day="<?php echo esc_attr( $weekname ); ?>" class="<?php echo esc_attr( $selectedday ); ?>"><?php echo esc_html( $weekshortname ); ?></li>
									<?php } ?>
									</ul>
								</div>
								<div class="schedulecontainer mb-0">
									<div class="input">
										<input type="radio" name="scheduling_run_on" value="onetime" id="onetime" <?php echo esc_attr( $wpssw_onetime ); ?>>
										<label for="onetime"><?php echo esc_html__( 'One time run at', 'wpssw' ); ?></label>
									</div>
									<div id="scheduling_date">
										<?php
										$timezone = wp_timezone_string();
										if ( ! $wpssw_scheduling_date ) {
											$wpssw_scheduling_date = date_format( date_create( 'now', timezone_open( $timezone ) ), 'Y-m-d' );
										}
										if ( ! $wpssw_scheduling_time ) {
											$wpssw_scheduling_time = date_format( date_create( 'now', timezone_open( $timezone ) ), 'H:i' );
										}
										?>
										<input type="date" name="scheduling_date" autocomplete="off" value="<?php echo esc_attr( $wpssw_scheduling_date ); ?>">
										<input type="time" name="scheduling_time" autocomplete="off" value="<?php echo esc_attr( $wpssw_scheduling_time ); ?>">
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
						<?php
			}
		}
		/**
		 * Output Custom Static headers with dropdown field.
		 */
		public static function wpssw_woocommerce_admin_field_custom_headers_action() {
			?>
			<div class="generalSetting-section ord_spreadsheet_row">
				<div class="generalSetting-left">
					<h4><?php echo esc_html__( 'Custom Static Headers', 'wpssw' ); ?></h4>
					<p><?php echo esc_html__( 'Enable this option to add a custom header. The selected option will determine the header value. To include a static header-value pair in your list of headers, click on the "Add" button. This allows for personalized and easy customization of your headers.', 'wpssw' ); ?></p>
					<div class="custom-input-div">
						<input class="input-text" type="text" name="custom_headers_val" id="custom_headers_val" placeholder="Enter Header Name"><br><br>
						<select name="custom_headers_val_dropdown" id="custom_headers_val_dropdown">
							<option value="IP_address">IP Address</option>
							<option value="site_name">Site Name</option>
							<option value="site_URL">Site URL</option>
							<option value="user_agent">User Agent</option>
							<option value="user_name">User Name</option>
							<option value="blank">Blank</option>
						</select>
						<button class="wpssw-button wpssw-button-secondary" type="button" id='add_ctm_val'><?php echo esc_html__( 'Add', 'wpssw' ); ?></button>
					</div>
				</div>
				<div class="generalSetting-right">              
					<label for="custom_header_action">
						<input name="custom_header_action" id="custom_header_action" type="checkbox" class="" value="1"><span class="checkbox-switch"></span> 							
					</label> <br><br>
					   
				</div>
			</div>
					<?php
		}
		/**
		 * Output Allow to copy same columns field for orders of sheet
		 */
		public static function wpssw_woocommerce_admin_field_repeat_checkbox() {
			$wpssw_is_checked = '';
			$wpssw_is_repeat  = parent::wpssw_option( 'wpssw_repeat_checkbox' );
			if ( 'yes' === (string) $wpssw_is_repeat ) {
				$wpssw_is_checked = 'checked';
			}
			?>
			<div valign="top" class="repeat_checkbox generalSetting-section ord_spreadsheet_row">
				<div class="generalSetting-left">
					<h4><?php echo esc_html__( 'Allow to Copy Same Columns', 'wpssw' ); ?></h4>
					<p><?php echo esc_html__( 'Enable this setting option to copy identical columns into rows. You can copy details like Billing First Name, Billing Last Name, and more, making data representation and analysis even more convenient. To learn more,', 'wpssw' ); ?> <a href="<?php echo esc_url( parent::$doc_sheet_setting_allowtocopy ); ?>" target="_blank"> <?php echo esc_html__( 'click here', 'wpssw' ); ?></a>.</p>
				</div>
				<div class="generalSetting-right">              
					<label for="id_repeat_checkbox">
						<input name="repeat_checkbox" id="id_repeat_checkbox" type="checkbox" class="" value="1" <?php echo esc_html( $wpssw_is_checked ); ?>><span class="checkbox-switch"></span> 							
					</label>
				</div>
			</div>
					<?php
		}
		/**
		 * Added v4.6
		 * Output Category filter field for Product
		 *
		 * @param int $flag .
		 */
		public static function wpssw_woocommerce_admin_field_product_category_as_order_filter( $flag = 0 ) {
			$wpssw_category_filter       = parent::wpssw_option( 'wpssw_category_filter' );
			$wpssw_category_filter_ids   = parent::wpssw_option( 'wpssw_category_filter_ids' );
			$wpssw_product_category_list = self::wpssw_get_product_categories();
			if ( 0 === (int) $flag ) {
				?>
				<div class="generalSetting-section order-setting-producat-filter-row sheetHeaders-section ord_spreadsheet_row">
					<div>
						<div class="order-setting-producat-filter-section">
							<div class="generalSetting-left">
								<?php $freeze_header = self::wpssw_option( 'freeze_header' ); ?>
								<h4><?php echo esc_html__( 'Select Category', 'wpssw' ); ?></h4>
								<p><?php echo esc_html__( 'Enable this setting to include orders based on selected product categories in the sheet. Only orders that belong to the chosen product categories will be included in the spreadsheet. ', 'wpssw' ); ?></p>
							</div>
							<div class="generalSetting-right">
								<label for="product_category_select">
									<input name="category_filter" id="product_category_select" type="checkbox" class="" value="1" 
								<?php
								if ( $wpssw_category_filter ) {
									echo 'checked'; }
								?>
									><span class="checkbox-switch"></span> 	<img id="loaderprdcatheader" src="<?php dirname( __FILE__ ); ?>images/spinner.gif">						
								</label>
							</div>
						</div>
						<?php
						if ( is_array( $wpssw_category_filter_ids ) && ! empty( $wpssw_category_filter_ids ) ) {
							$wpssw_category_filter_ids = parent::wpssw_convert_int( $wpssw_category_filter_ids );
						}
						?>
						<div class="td-producat-wpssw">
							<div class="td-wpssw-headers">
								<div class='wpssw_headers'>
									<label for="sheet_headers"></label>
									<div class="selectall-btnset">
									<button type="button" class="wpssw-button wpssw-button-primary" id="producatselectall" 
									<?php
									if ( ! empty( $wpssw_selections ) ) {
										?>
										class="wpssw-button wpssw-button-secondary wpssw-prdcatselect"
										<?php
									} else {
										?>
										class="wpssw-button wpssw-button-secondary" <?php } ?>> <?php esc_html_e( 'Select All', 'wpssw' ); ?></button>                
									<button class="wpssw-button wpssw-button-secondary" type="button" id="producatselectnone" 
											<?php
											if ( ! empty( $wpssw_selections ) ) {
												?>
										class="wpssw-button wpssw-button-secondary wpssw-prdcatselect"
												<?php
											} else {
												?>
										class="wpssw-button wpssw-button-secondary"<?php } ?> > <?php esc_html_e( 'Select None', 'wpssw' ); ?></button>
									</div>
										<ul id="productcategory-sortable" class="sheetHeaders-box">
										<?php
										foreach ( $wpssw_product_category_list as $wpssw_key => $wpssw_val ) {
											$wpssw_checked = '';
											if ( is_array( $wpssw_category_filter_ids ) && in_array( (int) $wpssw_key, $wpssw_category_filter_ids, true ) ) {
												$wpssw_checked = 'checked';
											}
											?>
												<li class="default-order-sheet ui-state-default">
													<label for="<?php echo esc_html( 'cat_' . $wpssw_val ); ?>">
														<span class="orderSheet-left">
															<span class="wootextfield"><?php echo esc_html( $wpssw_val ); ?></span>
														</span>
														<span class="orderSheet-right">
														<input type="checkbox" name="productcat_filter[]" value="<?php echo esc_attr( $wpssw_key ); ?>" id="<?php echo esc_attr( 'cat_' . $wpssw_val ); ?>" class="producatheaders_chk" <?php echo esc_html( $wpssw_checked ); ?>>
														<span class="checkbox-switch-new"></span>
													</span>
												</span>
												</label>
												</li>
										<?php } ?>
										</ul>
									</div>
								</div>
							</div>
					</div>
				</div>
				<?php
			}
		}
		/**
		 * Output Spreadsheet dropdown field.
		 */
		public static function wpssw_woocommerce_admin_field_select_spreadsheet() {
			$spreadsheets_list               = self::$instance_api->get_spreadsheet_listing();
			$wpssw_spreadsheetid             = parent::wpssw_option( 'wpssw_woocommerce_spreadsheet' );
			$wpssw_order_spreadsheet_setting = self::wpssw_check_order_spreadsheet_setting();
			$wpssw_order_settings_checked    = '';
			if ( 'yes' === (string) $wpssw_order_spreadsheet_setting ) {
				$wpssw_order_settings_checked = 'checked';
			}

			?>
			<div class="generalSetting-section">
				<div class="generalSetting-left">
					<h4><?php echo esc_html__( 'Order Settings', 'wpssw' ); ?></h4>
					<p><?php echo esc_html__( 'Enable this option to automatically create customized spreadsheets and sheets based on order status for efficient order management & seamless functionality.', 'wpssw' ); ?></p>
				</div>
				<div class="generalSetting-right">
					<label for="order_settings_checkbox">
						<input name="order_settings_checkbox" id="order_settings_checkbox" type="checkbox" class="" value="1" <?php echo esc_attr( $wpssw_order_settings_checked ); ?>>
						<span class="checkbox-switch"></span>
					</label>
				</div>
			</div>
			<div class="generalSetting-section googleSpreadsheet-section ord_spreadsheet_row">
				<div class="generalSetting-left">
				<h4><?php echo esc_html__( 'Google Spreadsheet Settings', 'wpssw' ); ?></h4>
				<p><?php echo esc_html__( 'Your chosen Google Spreadsheet automatically generates a sheet with customized headers based on the below-mentioned settings. Whenever a new order is placed, WPSyncSheets creates a new row to accommodate it with the spreadsheet.', 'wpssw' ); ?></p>
				<div class="createanew-radio">
					<div class="createanew-radio-box">
						<input type="radio" name="spreadsheetselection" value="new" id="createanew">
						<label for="createanew"><?php echo esc_html__( 'Create New Spreadsheet', 'wpssw' ); ?></label>
					</div>
					<div class="createanew-radio-box">
						<input type="radio" name="spreadsheetselection" value="existing" id="existing" checked="checked">
						<label for="existing"><?php echo esc_html__( 'Select Existing Spreadsheet', 'wpssw' ); ?></label>
					</div>
				</div>
				<div id="woocommerce_spreadsheet_container" class="spreadsheet-form">
					<select name="woocommerce_spreadsheet" id="woocommerce_spreadsheet"  class="">
						<?php

						$selected = '';

						foreach ( $spreadsheets_list as $spreadsheetid => $spreadsheetname ) {
							if ( (string) $wpssw_spreadsheetid === $spreadsheetid ) {
								$selected = 'selected="selected"';
							}
							?>
									<option value="<?php echo esc_attr( $spreadsheetid ); ?>" <?php echo esc_attr( $selected ); ?>><?php echo esc_html( $spreadsheetname ); ?></option>
								<?php $selected = ''; } ?>
							</select>
				</div>
					<div valign="top" id="newsheet" class="newsheetinput spreadsheet-form ord_spreadsheet_inputrow">
						<input class="input-text" name="spreadsheetname" id="spreadsheetname" type="text" placeholder="<?php echo esc_html__( 'Enter Spreadsheet Name', 'wpssw' ); ?>">
					</div>
					</div>
				</div>
			</div>
			<?php
		}
		/**
		 * Added v4.6
		 * Output Row Background Color field for orders of sheet
		 */
		public static function wpssw_woocommerce_admin_field_order_row_color() {
			$wpssw_color_code_enable = parent::wpssw_option( 'wpssw_color_code' );
			$wpssw_oddcolor          = parent::wpssw_option( 'wpssw_oddcolor' );
			$wpssw_evencolor         = parent::wpssw_option( 'wpssw_evencolor' );
			$wpssw_inputoption       = parent::wpssw_option( 'wpssw_inputoption' );
			if ( ! $wpssw_inputoption ) {
				$wpssw_inputoption = 'USER_ENTERED';
			}
			$wpssw_user = '';
			$wpssw_raw  = '';
			if ( 'USER_ENTERED' === (string) $wpssw_inputoption ) {
				$wpssw_user = 'selected';
			} else {
				$wpssw_raw = 'selected';
			}
			?>
			<tr valign="top ord_spreadsheet_row">
				<th scope="row" class="titledesc">
					<label for="color_code"><?php echo esc_html__( 'Row Background Color', 'wpssw' ); ?></label>
				</th>
				<td>              
					<label for="color_code">
						<input name="color_code" id="color_code" type="checkbox" class="" value="1" 
				<?php
				if ( $wpssw_color_code_enable ) {
					echo 'checked'; }
				?>
						><span class="checkbox-switch"></span>					
					</label>
				</td>
			</tr>
			<tr valign="top" id="color_selection ord_spreadsheet_row">
				<th scope="row">
				</th>
				<td class="forminp radio-box-td ">
					<input type="color" id="color_code_odd" name="oddcolor" value="<?php echo esc_attr( $wpssw_oddcolor ); ?>"><label for="color_code_odd"><?php echo esc_html__( ' Odd Rows', 'wpssw' ); ?></label> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
					<input type="color" id="color_code_even" name="evencolor" value="<?php echo esc_attr( $wpssw_evencolor ); ?>"><label for="color_code_even"><?php echo esc_html__( ' Even Rows', 'wpssw' ); ?></label>
					<br><br><i><strong><?php echo esc_html__( 'Note', 'wpssw' ); ?>:</strong> <?php echo esc_html__( 'Odd & Even Rows are calculated as per the order id.', 'wpssw' ); ?></i>
				</td>
			</tr>
			<tr valign="top ord_spreadsheet_row">
				<th scope="row" class="titledesc">
					<label for="inputoption"><?php echo esc_html__( 'Row Input Format Option', 'wpssw' ); ?></label>
				</th>
				<td>              
					<label for="inputoption">
						<select name="inputoption">
							<option value="USER_ENTERED" <?php echo esc_attr( $wpssw_user ); ?> >USER ENTERED</option>
							<option value="RAW" <?php echo esc_attr( $wpssw_raw ); ?>>RAW</option>
						</select>					
					</label>
					<br><br><i><strong><?php echo esc_html__( 'Note', 'wpssw' ); ?>: </strong><?php echo esc_html__( 'Determines how input data should be interpreted. For More Details', 'wpssw' ); ?><a href="<?php echo esc_url( 'https://developers.google.com/sheets/api/reference/rest/v4/ValueInputOption', 'wpssw' ); ?>" target="_blank"> <?php echo esc_html__( 'click here.', 'wpssw' ); ?></a></i>
				</td>
			</tr>
					<?php
		}
		/**
		 * Added v8.1
		 * Output Inhert Style field for orders settings.
		 */
		public static function wpssw_woocommerce_admin_field_inherit_style() {
			$wpssw_inherit_style = parent::wpssw_option( 'wpssw_order_inherit_style' );
			?>
			<div class="inheritStyles generalSetting-section ord_spreadsheet_row">
				<div class="titledesc generalSetting-left">
					<h4><?php echo esc_html__( 'Inherit Styles', 'wpssw' ); ?></h4>
					<p><?php echo esc_html__( 'This dropdown allows you to choose whether the new row should inherit the style from the row above it. The available options are "Yes" to inherit the style, "No" to not inherit the style, or "None" to leave the style inheritance unaffected.', 'wpssw' ); ?>  <a href="<?php echo esc_url( 'https://docs.wpsyncsheets.com/how-to-use-inherit-style-option/' ); ?>" target="_blank"><?php echo esc_html__( 'How to use inherit style option?', 'wpssw' ); ?></a></p>
				</div>
				<div class="forminp generalSetting-right">
					<label for="order_inherit_style">
						<?php $inherit_style_options = array( 'yes', 'no', 'none' ); ?>
						<select name="order_inherit_style">
							<?php foreach ( $inherit_style_options as $inherit_option ) { ?>
								<option value="<?php echo esc_attr( $inherit_option ); ?>" 
									<?php
									if ( $inherit_option === $wpssw_inherit_style || ( 'none' === (string) $inherit_option && '' === (string) $wpssw_inherit_style ) ) {
										echo ' selected';
									}
									?>
								><?php echo esc_html( ucfirst( $inherit_option ) ); ?></option>
							<?php } ?>
						</select>
					</label>
				</div>
			</div>
			<?php
		}

		/**
		 * Output exclude orders status from notes
		 */
		public static function wpssw_woocommerce_admin_field_exclude_order_status() {
			$wpssw_is_checked  = '';
			$wpssw_is_excluded = parent::wpssw_option( 'wpssw_exclude_order_status_from_notes' );
			if ( 'yes' === (string) $wpssw_is_excluded ) {
				$wpssw_is_checked = 'checked';
			}
			?>
			<div valign="top" class="exclude_order_status_from_notes generalSetting-section ord_spreadsheet_row">
				<div class="generalSetting-left">
					<h4><?php echo esc_html__( 'Exclude Order Status from Order Notes', 'wpssw' ); ?></h4>
					<p><?php echo esc_html__( 'Enable this setting option to exculde order status change note from order notes.', 'wpssw' ); ?></p>
				</div>
				<div class="generalSetting-right">              
					<label for="id_exclude_order_status_from_notes">
						<input name="exclude_order_status_from_notes" id="id_exclude_order_status_from_notes" type="checkbox" class="" value="1" <?php echo esc_html( $wpssw_is_checked ); ?>><span class="checkbox-switch"></span> 							
					</label>
				</div>
			</div>
					<?php
		}
		/**
		 * Added v5.1
		 * Output Price Format field for orders of sheet
		 */
		public static function wpssw_woocommerce_admin_field_price_format() {
			$wpssw_price_format = parent::wpssw_option( 'wpssw_price_format' );
			if ( ! $wpssw_price_format ) {
				$wpssw_price_format = 'plain';
			}
			$wpssw_plain     = '';
			$wpssw_formatted = '';
			if ( 'plain' === (string) $wpssw_price_format ) {
				$wpssw_plain = 'selected';
			} else {
				$wpssw_formatted = 'selected';
			}
			?>
			<tr valign="top ord_spreadsheet_row">
				<th scope="row" class="titledesc">
					<label for="wpssw_price_format"><?php echo esc_html__( 'Price Format', 'wpssw' ); ?></label>
				</th>
				<td>              
					<label for="wpssw_price_format">
						<select name="wpssw_price_format">
							<option value="plain" <?php echo esc_html( $wpssw_plain ); ?> ><?php echo esc_html__( 'Only Values', 'wpssw' ); ?></option>
							<option value="formatted" <?php echo esc_html( $wpssw_formatted ); ?>><?php echo esc_html__( 'Formatted Values with Symbol', 'wpssw' ); ?></option>
						</select>					
					</label>
				</td>
			</tr>
					<?php
		}
		/**
		 * Added v5.1
		 * Output Price Format field for orders of sheet
		 */
		public static function wpssw_woocommerce_admin_field_import_orders() {
			$wpssw_order_import = parent::wpssw_option( 'wpssw_order_import' );
			$wpssw_order_update = parent::wpssw_option( 'wpssw_order_update' );
			$wpssw_order_delete = parent::wpssw_option( 'wpssw_order_delete' );
			$wpssw_order_insert = parent::wpssw_option( 'wpssw_order_insert' );

			$wpssw_order_spreadsheet_setting = self::wpssw_check_order_spreadsheet_setting();
			?>
			<div class="generalSetting-section ord-import-section-row import-section-row ord_spreadsheet_row" id="import_order_checkbox_row">
				<div>
					<div class="ord-import-section import-section">
						<div class="generalSetting-left">
							<h4>
								<span class="wpssw-tooltio-link tooltip-right">
									<?php esc_html_e( 'Import Orders', 'wpssw' ); ?>
									<span class="tooltip-text">Import</span>
								</span>
							</h4>
							<p><?php echo esc_html__( "Enable this option to import orders from Google Sheets to your site. Before clicking the import button it's essential to take a database backup.", 'wpssw' ); ?> 
							<a href="<?php echo esc_url( 'https://docs.wpsyncsheets.com/how-to-import-orders/' ); ?>" target="_blank"><?php echo esc_html__( 'How to Import Orders?', 'wpssw' ); ?></a></p>
						</div>
						<div class="generalSetting-right">     
							<label for="import_order_checkbox">
								<input name="import_order_checkbox" id="import_order_checkbox" type="checkbox" class="" value="1" 
								<?php
								if ( $wpssw_order_import ) {
									echo 'checked="checked"';}
								?>
								><span class="checkbox-switch"></span><span class="checkbox-switch"></span> 		
							</label> 
						</div>
					</div>
						<div class="generalSetting-section ord_import_options_row import_options_row">
						<div valign="top" id="insert_order_checkbox_row" class="wpssw_crud_ord_row wpssw_all_crud_row" >
							<div class="generalSetting-left">
								<label><?php esc_html_e( 'Insert Order', 'wpssw' ); ?></label>
							</div>
							<div class="generalSetting-right inside-checkbox">    
								<label for="insert_order_checkbox">
									<input name="insert_order_checkbox" id="insert_order_checkbox" type="checkbox" class="" value="1" 
									<?php
									if ( $wpssw_order_insert ) {
										echo 'checked="checked"';}
									?>
									><span class="checkbox-switch-new"></span>
								</label> 
							</div>
						</div>
						<div valign="top" id="update_order_checkbox_row" class="wpssw_crud_ord_row wpssw_all_crud_row" >
							<div  class="generalSetting-left">
								<label><?php esc_html_e( 'Update Order', 'wpssw' ); ?></label>
							</div>
							<div class="generalSetting-right inside-checkbox">    
								<label for="update_order_checkbox">
									<input name="update_order_checkbox" id="update_order_checkbox" type="checkbox" class="" value="1" 
									<?php
									if ( $wpssw_order_update ) {
										echo 'checked="checked"';}
									?>
									><span class="checkbox-switch-new"></span>
								</label> 
							</div>
						</div>
						<div valign="top" id="delete_order_checkbox_row" class="wpssw_crud_ord_row wpssw_all_crud_row" >
							<div class="generalSetting-left">
								<label><?php esc_html_e( 'Delete Order', 'wpssw' ); ?></label>
							</div>
							<div class="generalSetting-right inside-checkbox">     
								<label for="delete_order_checkbox">
									<input name="delete_order_checkbox" id="delete_order_checkbox" type="checkbox" class="" value="1" 
									<?php
									if ( $wpssw_order_delete ) {
										echo 'checked="checked"';}
									?>
									><span class="checkbox-switch-new"></span>
								</label> 
							</div>
						</div>
					</div>
			<?php if ( 'yes' === (string) $wpssw_order_spreadsheet_setting && ( 1 === (int) $wpssw_order_insert || 1 === (int) $wpssw_order_update || 1 === (int) $wpssw_order_delete ) ) { ?>
				<div valign="top" class="ord_import_row generalSetting-section import_row">
					<div class="generalSetting-left">
						<img src="<?php dirname( __FILE__ ); ?>images/spinner.gif" id="ordimportsyncloader">
						<span id="ordimportsynctext"><?php esc_html_e( 'Checking...', 'wpssw' ); ?></span>
						<a class="wpssw-button wpssw-button-secondary" href="javascript:void(0)" id="ordimportsync"><?php echo esc_html__( 'Import Order', 'wpssw' ); ?></a>
						<br />
						<a class="wpssw-button wpssw-button-secondary" href="javascript:void(0)" id="ordimportsyncbtm"><?php esc_html_e( 'Proceed', 'wpssw' ); ?></a>
						<a class="wpssw-button wpssw-button-secondary" href="javascript:void(0)" id="ordcancelsyncbtm"><?php esc_html_e( 'Cancel', 'wpssw' ); ?></a>
					</div>
					<div class="generalSetting-right">   
					</div>
				</div>
				<?php } ?>
			</div>
				</div>
			</div>			
				<?php
		}
		/**
		 * Order ID in Ascending Order and Descending Order
		 */
		public static function wpssw_woocommerce_admin_field_order_asc_desc() {
			$order_ascdesc = parent::wpssw_option( 'wpssw_order_ascdesc' );
			if ( ! empty( $order_ascdesc ) ) {
				if ( 'ascorder' === (string) $order_ascdesc ) {
					$wpssw_ascorder  = "checked='checked' disabled='disabled'";
					$wpssw_descorder = "disabled='disabled'";
				} else {
					$wpssw_descorder = "checked='checked' disabled='disabled'";
					$wpssw_ascorder  = "disabled='disabled'";
				}
				$wpssw_disableclass = 'disabled';
			} else {
				$wpssw_ascorder     = "checked='checked' disabled='disabled'";
				$wpssw_descorder    = "disabled='disabled'";
				$wpssw_disableclass = 'disabled';
			}
			?>
			<div class="generalSetting-section ord_spreadsheet_row">
				<div class="generalSetting-left">
					<h4><?php echo esc_html__( 'Spreadsheet Row Order', 'wpssw' ); ?></h4>
					<p><?php echo esc_html__( 'This option allows you to set the sequence in which the rows are organized, based on the order ID. This feature is only available when you create a new spreadsheet.', 'wpssw' ); ?></strong></p>
					<div class="forminp radio-box-td spreadsheetOrder-radio mb-0">
						<input type="radio" id="asc_order" name="order_ascdesc" class="manage_order <?php echo esc_attr( $wpssw_disableclass ); ?>" value="ascorder"<?php echo esc_html( $wpssw_ascorder ); ?>><label for="asc_order"><?php echo esc_html__( ' Ascending Order', 'wpssw' ); ?></label>
						<input type="radio" id="desc_order" name="order_ascdesc" class="manage_order <?php echo esc_attr( $wpssw_disableclass ); ?>" value="descorder" <?php echo esc_html( $wpssw_descorder ); ?>><label for="desc_order"><?php echo esc_html__( ' Descending Order', 'wpssw' ); ?></label>
					</div>
				</div>
			</div>
					<?php
		}
		/**
		 * Chart Display Function Start
		 * Add Graph section
		 */
		public static function wpssw_woocommerce_admin_field_add_graph_section() {
			$wpssw_graphsheets_list = array();
			if ( ! empty( parent::wpssw_option( 'wpssw_graphsheets_list' ) ) ) {
				$wpssw_graphsheets_list = parent::wpssw_option( 'wpssw_graphsheets_list' );
			}
			$wpssw_products_sold_graph_type      = parent::wpssw_option( 'wpssw_products_sold_graph_type' );
			$wpssw_sales_orders_graph_type       = parent::wpssw_option( 'wpssw_sales_orders_graph_type' );
			$wpssw_total_orders_graph_type       = parent::wpssw_option( 'wpssw_total_orders_graph_type' );
			$wpssw_total_customers_graph_type    = parent::wpssw_option( 'wpssw_total_customers_graph_type' );
			$wpssw_total_used_coupons_graph_type = parent::wpssw_option( 'wpssw_total_used_coupons_graph_type' );
			$wpssw_is_checked_total              = '';
			$wpssw_is_checked_sales              = '';
			$wpssw_is_disabled_total             = '';
			$wpssw_is_disabled_sales             = '';
			$wpssw_is_checked_product            = '';
			$wpssw_is_disabled_product           = '';
			$wpssw_is_checked_customers          = '';
			$wpssw_is_disabled_customers         = '';
			$wpssw_is_checked_used_coupons       = '';
			$wpssw_is_disabled_used_coupons      = '';
			if ( in_array( 'sales_orders_graph', $wpssw_graphsheets_list, true ) ) {
				$wpssw_is_checked_sales = 'checked';
			} else {
				$wpssw_is_disabled_sales = 'disabled-regenerate-graph';
			}
			if ( in_array( 'total_orders_graph', $wpssw_graphsheets_list, true ) ) {
				$wpssw_is_checked_total = 'checked';
			} else {
				$wpssw_is_disabled_total = 'disabled-regenerate-graph';
			}
			if ( in_array( 'products_sold_graph', $wpssw_graphsheets_list, true ) ) {
				$wpssw_is_checked_product = 'checked';
			} else {
				$wpssw_is_disabled_product = 'disabled-regenerate-graph';
			}
			if ( in_array( 'total_customers_graph', $wpssw_graphsheets_list, true ) ) {
				$wpssw_is_checked_customers = 'checked';
			} else {
				$wpssw_is_disabled_customers = 'disabled-regenerate-graph';
			}
			if ( in_array( 'total_used_coupons_graph', $wpssw_graphsheets_list, true ) ) {
				$wpssw_is_checked_used_coupons = 'checked';
			} else {
				$wpssw_is_disabled_used_coupons = 'disabled-regenerate-graph';
			}
			$wpssw_is_sales_line        = '';
			$wpssw_is_total_line        = '';
			$wpssw_is_product_line      = '';
			$wpssw_is_customers_line    = '';
			$wpssw_is_used_coupons_line = '';
			$wpssw_is_sales_bar         = '';
			$wpssw_is_total_bar         = '';
			$wpssw_is_product_bar       = '';
			$wpssw_is_customers_bar     = '';
			$wpssw_is_used_coupons_bar  = '';
			$wpssw_is_sales_pie         = '';
			$wpssw_is_total_pie         = '';
			$wpssw_is_product_pie       = '';
			$wpssw_is_customers_pie     = '';
			$wpssw_is_used_coupons_pie  = '';
			if ( 'line' === (string) $wpssw_sales_orders_graph_type ) {
				$wpssw_is_sales_line = 'checked';
			}
			if ( 'column' === (string) $wpssw_sales_orders_graph_type ) {
				$wpssw_is_sales_bar = 'checked';
			}
			if ( 'pie' === (string) $wpssw_sales_orders_graph_type ) {
				$wpssw_is_sales_pie = 'checked';
			}
			if ( 'line' === (string) $wpssw_total_orders_graph_type ) {
				$wpssw_is_total_line = 'checked';
			}
			if ( 'column' === (string) $wpssw_total_orders_graph_type ) {
				$wpssw_is_total_bar = 'checked';
			}
			if ( 'pie' === (string) $wpssw_total_orders_graph_type ) {
				$wpssw_is_total_pie = 'checked';
			}
			if ( 'line' === (string) $wpssw_products_sold_graph_type ) {
				$wpssw_is_product_line = 'checked';
			}
			if ( 'column' === (string) $wpssw_products_sold_graph_type ) {
				$wpssw_is_product_bar = 'checked';
			}
			if ( 'pie' === (string) $wpssw_products_sold_graph_type ) {
				$wpssw_is_product_pie = 'checked';
			}
			if ( 'line' === (string) $wpssw_total_customers_graph_type ) {
				$wpssw_is_customers_line = 'checked';
			}
			if ( 'column' === (string) $wpssw_total_customers_graph_type ) {
				$wpssw_is_customers_bar = 'checked';
			}
			if ( 'pie' === (string) $wpssw_total_customers_graph_type ) {
				$wpssw_is_customers_pie = 'checked';
			}
			if ( 'line' === (string) $wpssw_total_used_coupons_graph_type ) {
				$wpssw_is_used_coupons_line = 'checked';
			}
			if ( 'column' === (string) $wpssw_total_used_coupons_graph_type ) {
				$wpssw_is_used_coupons_bar = 'checked';
			}
			if ( 'pie' === (string) $wpssw_total_used_coupons_graph_type ) {
				$wpssw_is_used_coupons_pie = 'checked';
			}
			?>
			<div class="wpssw-section-6 form-table graph_spreadsheet_row">
				<div class="graphSheets-box">
					<div class="titledesc">
						<div class="graph-header">
							<label><?php echo esc_html__( 'Sales Orders', 'wpssw' ); ?></label>
						<div class="forminp forminp-checkbox inside-checkbox">  
							<label for="sales_orders_graph">
								<input name="graphsheets_list[]" id="sales_orders_graph" type="checkbox" class="sales" value="sales_orders_graph" <?php echo esc_html( $wpssw_is_checked_sales ); ?>><span class="checkbox-switch-new"></span> 							
							</label>
						</div>
						</div>
					</div>
					<div class="graph-img sales-tdclass">
						<label>
							<input type="radio" name="sales_orders_graph_type" value="column" <?php echo esc_html( $wpssw_is_sales_bar ); ?> class="graph-radio-button" >
							<span class="graph_type_radio">
							<img src="<?php echo esc_url( WPSSW_URL . 'assets/images/bar_chart.png' ); ?>" >
						</span>
						</label>
						<label>
							<input type="radio" name="sales_orders_graph_type" value="line" <?php echo esc_html( $wpssw_is_sales_line ); ?> class="graph-radio-button">
							<span class="graph_type_radio">
								<img src="<?php echo esc_url( WPSSW_URL . 'assets/images/line_chart.png' ); ?>">
							</span>
						</label>
						<label>
							<input type="radio" name="sales_orders_graph_type" value="pie" <?php echo esc_html( $wpssw_is_sales_pie ); ?> class="graph-radio-button">
							<span class="graph_type_radio">
								<img src="<?php echo esc_url( WPSSW_URL . 'assets/images/pie_chart.png' ); ?>" >
							</span>
						</label>
					</div>
					<div class="graph-regenerateBtn sales-tdclass">  
						<a href="javascript:void(0)" id="regenerate_sales_graph" class="graph-regenerate wpssw-button <?php echo esc_html( $wpssw_is_disabled_sales ); ?>" ><?php echo esc_html__( 'Regenerate', 'wpssw' ); ?> </a>
						<img src="<?php dirname( __FILE__ ); ?>images/spinner.gif" id="sales_chartloader" class="chartloader">
					</div>
				</div>
				<div class="graphSheets-box">
					<div class="titledesc">
						<div class="graph-header">
							<label><?php echo esc_html__( 'Total Orders', 'wpssw' ); ?></label>
							<div class="forminp forminp-checkbox inside-checkbox">  
								<label for="total_orders_graph">
									<input name="graphsheets_list[]" id="total_orders_graph" type="checkbox" class="total" value="total_orders_graph" <?php echo esc_html( $wpssw_is_checked_total ); ?>><span class="checkbox-switch-new"></span>				
								</label>
							</div>
						</div>
					</div>
					<div class="graph-img total-tdclass">
						<label>
							<input type="radio" name="total_orders_graph_type" value="column" <?php echo esc_html( $wpssw_is_total_bar ); ?> class="graph-radio-button">
							<span class="graph_type_radio">
								<img src="<?php echo esc_url( WPSSW_URL . 'assets/images/bar_chart.png' ); ?>">
							</span>
						</label>
						<label>
							<input type="radio" name="total_orders_graph_type" value="line" <?php echo esc_html( $wpssw_is_total_line ); ?> class="graph-radio-button">
							<span class="graph_type_radio">
								<img src="<?php echo esc_url( WPSSW_URL . 'assets/images/line_chart.png' ); ?>">
							</span>
						</label>
						<label>
							<input type="radio" name="total_orders_graph_type" value="pie" <?php echo esc_html( $wpssw_is_total_pie ); ?> class="graph-radio-button">
							<span class="graph_type_radio">
								<img src="<?php echo esc_url( WPSSW_URL . 'assets/images/pie_chart.png' ); ?>" >
							</span>
						</label>
					</div>
					<div class="graph-regenerateBtn total-tdclass">  	
						<a href="javascript:void(0)" id="regenerate_total_graph" class="graph-regenerate wpssw-button <?php echo esc_html( $wpssw_is_disabled_total ); ?>" ><?php echo esc_html__( 'Regenerate', 'wpssw' ); ?> </a>
						<img src="<?php dirname( __FILE__ ); ?>images/spinner.gif" id="total_chartloader" class="chartloader">			
					</div>
				</div>
				<div class="graphSheets-box">
					<div class="titledesc">
						<div class="graph-header">
							<label><?php echo esc_html__( 'Products Sold', 'wpssw' ); ?></label>
							<div class="forminp forminp-checkbox inside-checkbox">  
								<label for="products_sold_graph">
									<input name="graphsheets_list[]" id="products_sold_graph" type="checkbox" class="product" value="products_sold_graph"  <?php echo esc_html( $wpssw_is_checked_product ); ?>><span class="checkbox-switch-new"></span> 							
								</label>
							</div>

						</div>
					</div>
					<div class="graph-img product-tdclass">
						<label>
							<input type="radio" name="products_sold_graph_type" value="column" <?php echo esc_html( $wpssw_is_product_bar ); ?> class="graph-radio-button">
							<span class="graph_type_radio">
								<img src="<?php echo esc_url( WPSSW_URL . 'assets/images/bar_chart.png' ); ?>">
							</span>
						</label>
						<label>
							<input type="radio" name="products_sold_graph_type" value="line" <?php echo esc_html( $wpssw_is_product_line ); ?> class="graph-radio-button">
							<span class="graph_type_radio">
								<img src="<?php echo esc_url( WPSSW_URL . 'assets/images/line_chart.png' ); ?>">
							</span>
						</label>
						<label>
							<input type="radio" name="products_sold_graph_type" value="pie" <?php echo esc_html( $wpssw_is_product_pie ); ?> class="graph-radio-button">
							<span class="graph_type_radio">
								<img src="<?php echo esc_url( WPSSW_URL . 'assets/images/pie_chart.png' ); ?>" >
							</span>
						</label>
					</div>
					<div class="graph-regenerateBtn product-tdclass">  	
						<a href="javascript:void(0)" id="regenerate_product_graph" class="graph-regenerate wpssw-button <?php echo esc_html( $wpssw_is_disabled_product ); ?>" ><?php echo esc_html__( 'Regenerate', 'wpssw' ); ?> </a>
						<img src="<?php dirname( __FILE__ ); ?>images/spinner.gif" id="product_chartloader" class="chartloader">			
					</div>
				</div>
				<div class="graphSheets-box">
					<div class="titledesc">
						<div class="graph-header">
							<label><?php echo esc_html__( 'Total Customers', 'wpssw' ); ?></label>
							<div class="forminp forminp-checkbox inside-checkbox">  
								<label for="total_customers_graph">
									<input name="graphsheets_list[]" id="total_customers_graph" type="checkbox" class="customers" value="total_customers_graph" <?php echo esc_html( $wpssw_is_checked_customers ); ?>><span class="checkbox-switch-new"></span> 							
								</label>
							</div>

						</div>
					</div>
					<div class="graph-img customers-tdclass">
						<label>
							<input type="radio" name="total_customers_graph_type" value="column" <?php echo esc_html( $wpssw_is_customers_bar ); ?> class="graph-radio-button">
							<span class="graph_type_radio">
								<img src="<?php echo esc_url( WPSSW_URL . 'assets/images/bar_chart.png' ); ?>">
							</span>
						</label>
						<label>
							<input type="radio" name="total_customers_graph_type" value="line" <?php echo esc_html( $wpssw_is_customers_line ); ?> class="graph-radio-button">
							<span class="graph_type_radio">
								<img src="<?php echo esc_url( WPSSW_URL . 'assets/images/line_chart.png' ); ?>">
							</span>
						</label>
						<label>
							<input type="radio" name="total_customers_graph_type" value="pie" <?php echo esc_html( $wpssw_is_customers_pie ); ?> class="graph-radio-button">
							<span class="graph_type_radio">
								<img src="<?php echo esc_url( WPSSW_URL . 'assets/images/pie_chart.png' ); ?>" >
							</span>
						</label>
					</div>
					<div class="graph-regenerateBtn customers-tdclass">  	
						<a href="javascript:void(0)" id="regenerate_customers_graph" class="graph-regenerate wpssw-button <?php echo esc_html( $wpssw_is_disabled_customers ); ?>" ><?php echo esc_html__( 'Regenerate', 'wpssw' ); ?> </a>
						<img src="<?php dirname( __FILE__ ); ?>images/spinner.gif" id="customers_chartloader" class="chartloader">			
					</div>
				</div>
				<div class="graphSheets-box" class="last-section-tr">
					<div class="titledesc">
						<div class="graph-header">
							<label><?php echo esc_html__( 'Total Used Coupons', 'wpssw' ); ?></label>
							<div class="forminp forminp-checkbox inside-checkbox">  
								<label for="total_used_coupons_graph">
									<input name="graphsheets_list[]" id="total_used_coupons_graph" type="checkbox" class="used-coupons" value="total_used_coupons_graph" <?php echo esc_html( $wpssw_is_checked_used_coupons ); ?>><span class="checkbox-switch-new"></span>			
								</label>
							</div>
						</div>
					</div>
					<div class="graph-img used-coupons-tdclass">
						<label>
							<input type="radio" name="total_used_coupons_graph_type" value="column" <?php echo esc_html( $wpssw_is_used_coupons_bar ); ?> class="graph-radio-button">
							<span class="graph_type_radio">
								<img src="<?php echo esc_url( WPSSW_URL . 'assets/images/bar_chart.png' ); ?>">
							</span>
						</label>
						<label>
							<input type="radio" name="total_used_coupons_graph_type" value="line" <?php echo esc_html( $wpssw_is_used_coupons_line ); ?> class="graph-radio-button">
							<span class="graph_type_radio">
								<img src="<?php echo esc_url( WPSSW_URL . 'assets/images/line_chart.png' ); ?>">
							</span>
						</label>
						<label>
							<input type="radio" name="total_used_coupons_graph_type" value="pie" <?php echo esc_html( $wpssw_is_used_coupons_pie ); ?> class="graph-radio-button">
							<span class="graph_type_radio">
								<img src="<?php echo esc_url( WPSSW_URL . 'assets/images/pie_chart.png' ); ?>" >
							</span>
						</label>
					</div>
					<div class="graph-regenerateBtn used-coupons-tdclass">  	
						<a href="javascript:void(0)" id="regenerate_used_coupons_graph" class="graph-regenerate wpssw-button <?php echo esc_html( $wpssw_is_disabled_used_coupons ); ?>" ><?php echo esc_html__( 'Regenerate', 'wpssw' ); ?> </a>
						<img src="<?php dirname( __FILE__ ); ?>images/spinner.gif" id="used_coupons_chartloader" class="chartloader">			
					</div>
				</div>
			</div>
					<?php

		}
		/**
		 * Output Product name as sheets field for orders settings.
		 */
		public static function wpssw_woocommerce_admin_field_product_names_as_sheet() {
			$wpssw_is_checked             = '';
			$wpssw_product_names_as_sheet = parent::wpssw_option( 'product_names_as_sheet' );

			if ( 'yes' === (string) $wpssw_product_names_as_sheet ) {
				$wpssw_is_checked = 'checked';
			}

			?>
			<div valign="top" class="product_names_as_sheet generalSetting-section ord_spreadsheet_row">
				<div class="generalSetting-left">
					<h4><?php echo esc_html__( 'Product Names as Sheets', 'wpssw' ); ?></h4>
					<p><?php echo esc_html__( 'This setting will create all product names sheet and will hold orders that include that particular product.', 'wpssw' ); ?></a></p>
				</div>
				<div class="generalSetting-right">              
					<label for="product_names_as_sheet">
						<input name="product_names_as_sheet" id="product_names_as_sheet" type="checkbox" class="" value="1" <?php echo esc_html( $wpssw_is_checked ); ?>><span class="checkbox-switch"></span>				
					</label>
				</div>
			</div>
					<?php
		}
		/**
		 * Sync single order
		 */
		public static function wpssw_sync_single_order_data() {

			if ( ! isset( $_POST['sync_nonce_token'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['sync_nonce_token'] ) ), 'sync_nonce' )
			) {
				echo 'nonceerror';
				die();
			}
			$wpssw_order_spreadsheet_setting = self::wpssw_check_order_spreadsheet_setting();
			if ( 'yes' !== (string) $wpssw_order_spreadsheet_setting ) {
				echo 'savesettings';
				die;
			}
			if ( ! self::$instance_api->checkcredenatials() ) {
				return;
			}
			$wpssw_spreadsheetid     = parent::wpssw_option( 'wpssw_woocommerce_spreadsheet' );
			$wpssw_spreadsheets_list = self::$instance_api->get_spreadsheet_listing();
			if ( ! array_key_exists( $wpssw_spreadsheetid, $wpssw_spreadsheets_list ) ) {
				echo 'savesettings';
				die;
			}
			$wpssw_sheets = array_filter( (array) parent::wpssw_option( 'wpssw_sheets' ) );
			if ( ! $wpssw_sheets ) {
				$wpssw_sheets = self::wpssw_prepare_sheets();
			}
			$wpssw_inputoption = parent::wpssw_option( 'wpssw_inputoption' );
			if ( ! $wpssw_inputoption ) {
				$wpssw_inputoption = 'USER_ENTERED';
			}
			$order_id                  = isset( $_POST['order_id'] ) ? sanitize_text_field( wp_unslash( $_POST['order_id'] ) ) : '';
			$wpssw_order               = wc_get_order( $order_id );
			$wpssw_old_status          = $wpssw_order->get_status();
			$wpssw_existingsheetsnames = array();
			$wpssw_response            = self::$instance_api->get_sheet_listing( $wpssw_spreadsheetid );
			$wpssw_existingsheetsnames = self::$instance_api->get_sheet_list( $wpssw_response );

			$wpssw_old_status = str_replace( '-', ' ', $wpssw_old_status );
			$wpssw_sheet_name = ucwords( $wpssw_old_status ) . ' Orders';

			if ( array_key_exists( $wpssw_sheet_name, $wpssw_sheets ) ) {

				if ( ! isset( $wpssw_existingsheetsnames[ $wpssw_sheets[ $wpssw_sheet_name ] ] ) ) {
					echo 'statussheetnotexist';
					die;
				}
				$wpssw_getactivesheets[]              = $wpssw_sheets[ $wpssw_sheet_name ] . '!A:A';
				$wpssw_sheetname[ $wpssw_old_status ] = $wpssw_sheets[ $wpssw_sheet_name ];
			}
			if ( array_key_exists( 'All Orders', $wpssw_sheets ) ) {
				if ( ! isset( $wpssw_existingsheetsnames[ $wpssw_sheets['All Orders'] ] ) ) {
					echo 'allordersheetnotexist';
					die;
				}
				$wpssw_is_allowed = apply_filters( 'wpssw_order_status_allow', true, ucwords( $wpssw_old_status ) . ' Orders' );
				if ( $wpssw_is_allowed ) {
					$wpssw_sheetname['all_order'] = $wpssw_sheets['All Orders'];
					$wpssw_getactivesheets[]      = $wpssw_sheets['All Orders'] . '!A:A';
				}
			}

			$wpssw_eventsheetnames = array();
			if ( parent::wpssw_is_event_calender_ticket_active() ) {

				$wpssw_eventsheetnames = parent::wpssw_option( 'wpssw_eventsheets_list' );
				if ( ! is_array( $wpssw_eventsheetnames ) ) {
					$wpssw_eventsheetnames = array();
				}
				foreach ( $wpssw_eventsheetnames as $eventsheet ) {
					if ( ! isset( $wpssw_existingsheetsnames[ $eventsheet ] ) ) {
						continue;
					}
					$items_to_keep = self::wpssw_get_specific_items( $wpssw_order, $eventsheet );
					if ( empty( $items_to_keep ) ) {
						continue;
					}
					$wpssw_sheetname[ $eventsheet ] = $eventsheet;
					$wpssw_getactivesheets[]        = $eventsheet . '!A:A';
				}
			}
			$wpssw_productsheets_list     = array();
			$wpssw_product_names_as_sheet = parent::wpssw_option( 'product_names_as_sheet' );
			if ( 'yes' === (string) $wpssw_product_names_as_sheet ) {
				$wpssw_productsheets_list = parent::wpssw_option( 'wpssw_productname_sheets' );
				if ( ! $wpssw_productsheets_list ) {
					$wpssw_productsheets_list = array();
				}
				foreach ( $wpssw_productsheets_list as $product_id => $product_name ) {
					if ( empty( $product_name ) || ! isset( $wpssw_existingsheetsnames[ $product_name ] ) ) {
						continue;
					}
					$items_to_keep = self::wpssw_get_specific_items( $wpssw_order, $product_name );
					if ( empty( $items_to_keep ) ) {
						continue;
					}

					$wpssw_sheetname[ $product_name ] = $product_name;
					$wpssw_getactivesheets[]          = $product_name . '!A:A';
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

			$wpssw_existingorders = array();
			foreach ( $wpssw_response->getValueRanges() as $wpssw_order ) {
				if ( false !== strpos( $wpssw_order->range, "'!A" ) ) {
					$wpssw_rangetitle = explode( "'!A", $wpssw_order->range );
				} else {
					$wpssw_rangetitle = explode( '!A', $wpssw_order->range );
				}
				$wpssw_sheettitle                          = str_replace( "'", '', $wpssw_rangetitle[0] );
				$wpssw_data                                = array_map(
					function( $wpssw_element ) {
						if ( isset( $wpssw_element['0'] ) ) {
							return $wpssw_element['0'];
						} else {
							return '';
						}
					},
					$wpssw_order->values
				);
				$wpssw_existingorders[ $wpssw_sheettitle ] = $wpssw_data;
			}
			$wpssw_dataarray = array();
			$wpssw_isexecute = 0;
			foreach ( $wpssw_sheetname as $wpssw_sheet_slug => $sheetname ) {
				$wpssw_values_array = array();
				if ( self::wpssw_check_product_category( $order_id ) ) {
					continue;
				}
				if ( in_array( $order_id, $wpssw_existingorders[ $sheetname ], true ) ) {
					self::wpssw_insert_event_data_into_sheet( $order_id, $sheetname );
					continue;
				}

				self::wpssw_insert_data_into_sheet( $order_id, $sheetname );
			}
			echo 'successful';
			die;
		}
		/**
		 * Clear General settings sheets
		 */
		public static function wpssw_clear_all_sheet() {
			$wpssw_order_spreadsheet_setting = self::wpssw_check_order_spreadsheet_setting();
			if ( 'yes' !== (string) $wpssw_order_spreadsheet_setting ) {
				echo 'spreadsheetnotexist';
				die();
			}
			if ( ! isset( $_POST['wpssw_general_settings'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wpssw_general_settings'] ) ), 'save_general_settings' ) ) {
				echo esc_html__( 'Sorry, your nonce did not verify.', 'wpssw' );
				die();
			}
			$wpssw_spreadsheetid     = parent::wpssw_option( 'wpssw_woocommerce_spreadsheet' );
			$wpssw_spreadsheets_list = self::$instance_api->get_spreadsheet_listing();
			if ( ! array_key_exists( $wpssw_spreadsheetid, $wpssw_spreadsheets_list ) ) {
				die;
			}
			$requestbody   = self::$instance_api->clearobject();
			$total_headers = count( parent::wpssw_option( 'wpssw_sheet_headers_list' ) ) + 1;
			if ( parent::wpssw_is_event_calender_ticket_active() ) {
				$wpssw_woo_event_headers = parent::wpssw_option( 'wpssw_woo_event_headers' );
				if ( ! is_array( $wpssw_woo_event_headers ) ) {
					$wpssw_woo_event_headers = array();
				}
				$total_headers = count( $wpssw_woo_event_headers ) + $total_headers;
			}
			$last_column              = parent::wpssw_get_column_index( $total_headers );
			$wpssw_order_status_array = self::$wpssw_default_status_slug;
			/* Custom Order Status*/
			$wpssw_status_array             = wc_get_order_statuses();
			$wpssw_status_array['wc-trash'] = 'Trash';
			$wpssw_existingsheetsnames      = array();
			$wpssw_response                 = self::$instance_api->get_sheet_listing( $wpssw_spreadsheetid );
			$wpssw_existingsheetsnames      = self::$instance_api->get_sheet_list( $wpssw_response );
			$wpssw_order_status_array_count = count( $wpssw_order_status_array );
			$wpssw_sheets                   = array_filter( (array) parent::wpssw_option( 'wpssw_sheets' ) );
			if ( ! $wpssw_sheets ) {
				$wpssw_sheets = self::wpssw_prepare_sheets();
			}
			$wpssw_sheetnames             = array();
			$wpssw_status_array['wc-all'] = 'All';
			foreach ( $wpssw_status_array as $wpssw_key => $wpssw_val ) {
				$wpssw_status      = substr( $wpssw_key, strpos( $wpssw_key, '-' ) + 1 );
				$wpssw_orderstatus = str_replace( '-', ' ', $wpssw_status );
				$wpssw_orderstatus = ucwords( $wpssw_orderstatus ) . ' Orders';
				if ( array_key_exists( $wpssw_orderstatus, $wpssw_sheets ) && isset( $wpssw_existingsheetsnames[ $wpssw_sheets[ $wpssw_orderstatus ] ] ) ) {
					$wpssw_sheetnames[] = $wpssw_sheets[ $wpssw_orderstatus ];
				}
			}

			$wpssw_productsheets_list     = array();
			$wpssw_product_names_as_sheet = parent::wpssw_option( 'product_names_as_sheet' );
			if ( 'yes' === (string) $wpssw_product_names_as_sheet ) {
				$wpssw_productsheets_list = array_filter( array_unique( array_values( parent::wpssw_option( 'wpssw_productname_sheets' ) ) ) );
				if ( ! $wpssw_productsheets_list ) {
					$wpssw_productsheets_list = array();
				}
				foreach ( $wpssw_productsheets_list as $product_name ) {
					if ( ! empty( $product_name ) && isset( $wpssw_existingsheetsnames[ $product_name ] ) ) {
						$wpssw_sheetnames[] = $product_name;
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
		 * Check for existing spreadsheet
		 */
		public static function wpssw_check_existing_sheet() {

			if ( ! isset( $_POST['sync_nonce_token'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['sync_nonce_token'] ) ), 'sync_nonce' )
			) {
				echo esc_html__( 'Sorry, your nonce did not verify.', 'wpssw' );
				die();
			}
			$wpssw_sheetnames = array_filter( (array) parent::wpssw_option( 'wpssw_sheets' ) );
			if ( ! $wpssw_sheetnames ) {
				$wpssw_sheetnames = self::wpssw_prepare_sheets();
			}
			if ( ! isset( $_POST['id'] ) ) {
				echo esc_html__( 'Spreadsheet id not found.', 'wpssw' );
				die();
			}
			$wpssw_spreadsheetid = sanitize_text_field( wp_unslash( $_POST['id'] ) );
			if ( 'new' !== (string) $wpssw_spreadsheetid && 0 !== (int) $wpssw_spreadsheetid ) {
				$wpssw_exist               = 0;
				$wpssw_response            = self::$instance_api->get_sheet_listing( $wpssw_spreadsheetid );
				$wpssw_existingsheetsnames = self::$instance_api->get_sheet_list( $wpssw_response );
				$wpssw_existingsheetsnames = array_flip( $wpssw_existingsheetsnames );
				foreach ( $wpssw_existingsheetsnames as $sheetname ) {
					if ( in_array( $sheetname, $wpssw_sheetnames, true ) ) {
						$wpssw_exist = 1;
						break;
					}
				}
				if ( $wpssw_exist ) {
					echo 'successful';
					die();
				}
			}
			die();
		}
		/**
		 * Save Export orders settings
		 */
		public static function wpssw_export_order() {

			if ( ! isset( $_POST['wpssw_export_settings'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wpssw_export_settings'] ) ), 'save_export_settings' ) ) {
				echo 'error';
				die();
			}
			$wpssw_fromdate        = isset( $_POST['from_date'] ) ? sanitize_text_field( wp_unslash( $_POST['from_date'] ) ) : '';
			$wpssw_todate          = isset( $_POST['to_date'] ) ? sanitize_text_field( wp_unslash( $_POST['to_date'] ) ) : '';
			$wpssw_exportall       = isset( $_POST['exportall'] ) ? sanitize_text_field( wp_unslash( $_POST['exportall'] ) ) : '';
			$wpssw_spreadsheetname = isset( $_POST['spreadsheetname'] ) ? sanitize_text_field( wp_unslash( $_POST['spreadsheetname'] ) ) : '';
			$iscategory_enable     = isset( $_POST['category_select'] ) ? sanitize_text_field( wp_unslash( $_POST['category_select'] ) ) : '';
			$wpssw_category_ids    = array();
			if ( isset( $_POST['category_ids'] ) ) {
				$wpssw_category_ids = array_map( 'sanitize_text_field', wp_unslash( $_POST['category_ids'] ) );
			}
			$wpssw_inputoption = parent::wpssw_option( 'wpssw_inputoption' );
			if ( ! $wpssw_inputoption ) {
				$wpssw_inputoption = 'USER_ENTERED';
			}
			$wpssw_spreadsheetid = self::wpssw_create_spreadsheet( $wpssw_spreadsheetname );

			if ( empty( $wpssw_spreadsheetid ) ) {
				return false;
			}
			$wpssw_sheets = array_filter( (array) parent::wpssw_option( 'wpssw_sheets' ) );
			if ( ! $wpssw_sheets ) {
				$wpssw_sheets = self::wpssw_prepare_sheets();
			}
			$wpssw_order_status_array = self::$wpssw_default_status_slug;

			$wpssw_existingsheetsnames = array();
			$wpssw_response            = self::$instance_api->get_sheet_listing( $wpssw_spreadsheetid );
			$wpssw_existingsheetsnames = self::$instance_api->get_sheet_list( $wpssw_response );
			$wpssw_headers             = stripslashes_deep( parent::wpssw_option( 'wpssw_sheet_headers_list' ) );
			$wpssw_headers_custom      = stripslashes_deep( parent::wpssw_option( 'wpssw_sheet_headers_list_custom', array() ) );
			if ( ! empty( $wpssw_headers_custom ) ) {
				$wpssw_headers = $wpssw_headers_custom;
			}
			if ( ! $wpssw_headers ) {
				$wpssw_headers = array();
			}
			if ( parent::wpssw_is_event_calender_ticket_active() ) {
				$wpssw_woo_event_headers = parent::wpssw_option( 'wpssw_woo_event_headers' );
				if ( ! is_array( $wpssw_woo_event_headers ) ) {
					$wpssw_woo_event_headers = array();
				}
				$wpssw_headers = array_merge( $wpssw_headers, $wpssw_woo_event_headers );
			}
			array_unshift( $wpssw_headers, 'Order Id' );
			$wpssw_value = array( $wpssw_headers );
			/* Custom Order Status*/
			$wpssw_status_array             = wc_get_order_statuses();
			$wpssw_status_array['wc-trash'] = 'Trash';
			$wpssw_status_array['wc-all']   = 'All';
			$wpssw_activesheets             = array();

			foreach ( $wpssw_status_array as $wpssw_key => $wpssw_val ) {
				$wpssw_status      = substr( $wpssw_key, strpos( $wpssw_key, '-' ) + 1 );
				$wpssw_orderstatus = str_replace( '-', ' ', $wpssw_status );
				$wpssw_orderstatus = ucwords( $wpssw_orderstatus ) . ' Orders';
				if ( array_key_exists( $wpssw_orderstatus, $wpssw_sheets ) ) {
					if ( 'wc-trash' === (string) $wpssw_key ) {
						$wpssw_activesheets['trash'] = $wpssw_orderstatus;
					} elseif ( 'wc-all' === (string) $wpssw_key ) {
						$wpssw_activesheets['all_order'] = $wpssw_orderstatus;
					} else {
						$wpssw_activesheets[ $wpssw_key ] = $wpssw_orderstatus;
					}
					$wpssw_sheetname = $wpssw_sheets[ $wpssw_orderstatus ];
					/*Create new sheet into spreadsheet*/
					$param                  = array();
					$param['spreadsheetid'] = $wpssw_spreadsheetid;
					$param['sheetname']     = $wpssw_sheetname;
					$wpssw_response         = self::$instance_api->newsheetobject( $param );
					$wpssw_range            = trim( $wpssw_sheetname ) . '!A1';
					$wpssw_requestbody      = self::$instance_api->valuerangeobject( $wpssw_value );
					$wpssw_params           = array( 'valueInputOption' => $wpssw_inputoption );
					$param                  = self::$instance_api->setparamater( $wpssw_spreadsheetid, $wpssw_range, $wpssw_requestbody, $wpssw_params );
					$wpssw_response         = self::$instance_api->appendentry( $param );
				}
			}
			/*Delete Default sheet*/
			$param                  = array();
			$param['spreadsheetid'] = $wpssw_spreadsheetid;
			$wpssw_response         = self::$instance_api->deletesheetobject( $param );
			$wpssw_dataarray        = array();
			$wpssw_isexecute        = 0;
			$hpos_order_enabled     = parent::wpssw_check_hpos_order_setting_enabled();
			foreach ( $wpssw_activesheets as $wpssw_sheet_slug => $wpssw_sheetname ) {
				if ( $hpos_order_enabled ) {
					// Base query arguments.
					$wpssw_query_args = array(
						'orderby' => 'date',
						'order'   => 'ASC',
						'limit'   => -1, // Fetch all orders.
						'return'  => 'ids', // Fetch only IDs.
					);

					// Determine order status based on sheet name.
					if ( 'All Orders' === (string) $wpssw_sheetname ) {
						$wpssw_query_args['status'] = array_keys( wc_get_order_statuses() );
					} else {
						$wpssw_query_args['status'] = $wpssw_sheet_slug;
					}

					// Fetch the orders.
					$wpssw_all_orders = wc_get_orders( $wpssw_query_args );

				} else {
					if ( 'All Orders' === (string) $wpssw_sheetname ) {
						$wpssw_query_args = array(
							'post_type'      => 'shop_order',
							'posts_per_page' => -1,
							'order'          => 'ASC',
							'post_status'    => array_keys( wc_get_order_statuses() ),
						);
					} else {
						$wpssw_query_args = array(
							'post_type'      => 'shop_order',
							'post_status'    => $wpssw_sheet_slug,
							'posts_per_page' => -1,
							'order'          => 'ASC',
						);
					}
					$wpssw_query_args['fields'] = 'ids'; // Fetch only ids.

					$wpssw_all_orders = get_posts( $wpssw_query_args );
				}
				if ( empty( $wpssw_all_orders ) ) {
					continue;
				}
				$wpssw_values_array = array();
				foreach ( $wpssw_all_orders as $wpssw_order_id ) {
					set_time_limit( 999 );
					$wpssw_order = wc_get_order( $wpssw_order_id );
					if ( 'yes' === (string) $iscategory_enable ) {
						$wpssw_flag  = 'exclude';
						$wpssw_items = $wpssw_order->get_items();
						foreach ( $wpssw_items as $item ) {
							$terms = get_the_terms( $item->get_product_id(), 'product_cat' );
							foreach ( $terms as $term ) {
								if ( in_array( (int) $term->term_id, parent::wpssw_convert_int( $wpssw_category_ids ), true ) ) {
									$wpssw_flag = 'include';
									break;
								}
							}
						}
						if ( 'exclude' === (string) $wpssw_flag ) {
							continue;
						}
					}
					if ( 'no' === (string) $wpssw_exportall ) {
						$wpssw_orderdate = new DateTime( $wpssw_order->get_date_created()->format( 'Y-m-d' ) );
						$wpssw_datefrom  = new DateTime( $wpssw_fromdate );
						$wpssw_dateto    = new DateTime( $wpssw_todate );
						if ( $wpssw_orderdate < $wpssw_datefrom || $wpssw_orderdate > $wpssw_dateto ) {
							continue;
						}
					}
					$wpssw_order_data   = $wpssw_order->get_data();
					$wpssw_status       = $wpssw_order_data['status'];
					$wpssw_value        = self::wpssw_make_value_array( 'insert', $wpssw_order_id );
					$wpssw_values_array = array_merge( $wpssw_values_array, $wpssw_value );
				}
				if ( ! empty( $wpssw_values_array ) ) {
					try {
						$wpssw_newarray = array();
						foreach ( $wpssw_values_array as $key => $rowvalue ) {
							$wpssw_newarray[] = array_map( 'trim', $rowvalue );
						}
						$wpssw_requestbody = self::$instance_api->valuerangeobject( $wpssw_newarray );
						$wpssw_params      = array( 'valueInputOption' => $wpssw_inputoption );
						$param             = array();
						$param             = self::$instance_api->setparamater( $wpssw_spreadsheetid, $wpssw_sheets[ $wpssw_sheetname ], $wpssw_requestbody, $wpssw_params );
						$wpssw_response    = self::$instance_api->appendentry( $param );
					} catch ( Exception $e ) {
						echo esc_html( 'Message: ' . $e->getMessage() );
					}
				}
			}
			$wpssw_resultdata                  = array();
			$wpssw_resultdata['result']        = 'successful';
			$wpssw_resultdata['spreadsheetid'] = $wpssw_spreadsheetid;
			echo wp_json_encode( $wpssw_resultdata );
			die;
		}
		/**
		 * Get list of all products
		 */
		public static function wpssw_get_product_list() {
			$flag = 1;
			// phpcs:ignore.
			echo self::wpssw_woocommerce_admin_field_product_headers( $flag );
			wp_die();
		}
		/**
		 * Get list of all products category
		 */
		public static function wpssw_get_category_list() {
			$flag = 1;
			return self::wpssw_woocommerce_admin_field_product_category_as_order_filter( $flag );
		}
		/**
		 * Get orders count for syncronization
		 *
		 * @param bool $wpssw_getfirst .
		 */
		public static function wpssw_get_orders_count( $wpssw_getfirst = false ) {

			$wpssw_order_spreadsheet_setting = self::wpssw_check_order_spreadsheet_setting();
			if ( 'yes' !== (string) $wpssw_order_spreadsheet_setting ) {
				echo 'spreadsheetnotexist';
				die();
			}
			if ( ! $wpssw_getfirst ) {
				if ( ! isset( $_POST['wpssw_general_settings'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wpssw_general_settings'] ) ), 'save_general_settings' ) ) {
					echo 'error';
					die();
				}
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
			$wpssw_sheets = array_filter( (array) parent::wpssw_option( 'wpssw_sheets' ) );
			if ( ! $wpssw_sheets ) {
				$wpssw_sheets = self::wpssw_prepare_sheets();
			}

			$wpssw_fromdate            = isset( $_POST['sync_all_fromdate'] ) ? sanitize_text_field( wp_unslash( $_POST['sync_all_fromdate'] ) ) : '';
			$wpssw_todate              = isset( $_POST['sync_all_todate'] ) ? sanitize_text_field( wp_unslash( $_POST['sync_all_todate'] ) ) : '';
			$wpssw_syncall             = isset( $_POST['sync_all'] ) ? sanitize_text_field( wp_unslash( $_POST['sync_all'] ) ) : '';
			$wpssw_order_status_array  = self::$wpssw_default_status_slug;
			$wpssw_check_category      = false;
			$wpssw_category_filter     = parent::wpssw_option( 'wpssw_category_filter' );
			$wpssw_category_filter_ids = parent::wpssw_option( 'wpssw_category_filter_ids' );
			if ( $wpssw_category_filter && ! empty( $wpssw_category_filter_ids ) ) {
				$wpssw_check_category = true;
			}

			$wpssw_existingsheetsnames = array();
			$wpssw_response            = self::$instance_api->get_sheet_listing( $wpssw_spreadsheetid );
			$wpssw_existingsheetsnames = self::$instance_api->get_sheet_list( $wpssw_response );
			$wpssw_existingsheetsnames = array_flip( $wpssw_existingsheetsnames );

			$wpssw_status_array             = wc_get_order_statuses();
			$wpssw_status_array['wc-trash'] = 'Trash';

			$wpssw_status_array['wc-all'] = 'All';
			$wpssw_getactivesheets        = array();
			$wpssw_activesheets           = array();
			foreach ( $wpssw_status_array as $wpssw_key => $wpssw_val ) {
				$wpssw_status      = substr( $wpssw_key, strpos( $wpssw_key, '-' ) + 1 );
				$wpssw_orderstatus = str_replace( '-', ' ', $wpssw_status );
				$wpssw_orderstatus = ucwords( $wpssw_orderstatus ) . ' Orders';
				if ( array_key_exists( $wpssw_orderstatus, $wpssw_sheets ) && in_array( $wpssw_sheets[ $wpssw_orderstatus ], $wpssw_existingsheetsnames, true ) ) {
					if ( 'wc-trash' === (string) $wpssw_key ) {
						$wpssw_activesheets['trash'] = $wpssw_orderstatus;
					} elseif ( 'wc-all' === (string) $wpssw_key ) {
						$wpssw_activesheets['all_order'] = $wpssw_orderstatus;
					} else {
						$wpssw_activesheets[ $wpssw_key ] = $wpssw_orderstatus;
					}
					$wpssw_getactivesheets[] = "'" . $wpssw_sheets[ $wpssw_orderstatus ] . "'!A:A";
				}
			}
			$wpssw_productsheets_list     = array();
			$wpssw_product_names_as_sheet = parent::wpssw_option( 'product_names_as_sheet' );
			if ( 'yes' === (string) $wpssw_product_names_as_sheet ) {
				$wpssw_productsheets_list = array_filter( array_unique( array_values( parent::wpssw_option( 'wpssw_productname_sheets' ) ) ) );
				if ( ! $wpssw_productsheets_list ) {
					$wpssw_productsheets_list = array();
				}
				foreach ( $wpssw_productsheets_list as $product_name ) {
					if ( ! empty( $product_name ) && in_array( $product_name, $wpssw_existingsheetsnames, true ) ) {
						$wpssw_activesheets[ $product_name ] = $product_name;
						$wpssw_getactivesheets[]             = "'" . $product_name . "'!A:A";
						$wpssw_sheets[ $product_name ]       = $product_name;
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
			$wpssw_existingorders = array();

			foreach ( $wpssw_response->getValueRanges() as $wpssw_order ) {
				if ( strpos( $wpssw_order->range, "'!A" ) ) {
					$wpssw_rangetitle = explode( "'!A", $wpssw_order->range );
				} else {
					$wpssw_rangetitle = explode( '!A', $wpssw_order->range );
				}
				$wpssw_sheettitle = str_replace( "'", '', $wpssw_rangetitle[0] );
				if ( ! is_array( $wpssw_order->values ) ) {
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
						$wpssw_order->values
					);
				}

				$wpssw_existingorders[ $wpssw_sheettitle ] = $wpssw_data;
			}

			if ( $wpssw_getfirst ) {
				return $wpssw_existingorders;
			}
			$wpssw_dataarray    = array();
			$wpssw_isexecute    = 0;
			$response           = array();
			$hpos_order_enabled = parent::wpssw_check_hpos_order_setting_enabled();
			foreach ( $wpssw_activesheets as $wpssw_sheet_slug => $wpssw_sheetname ) {
				$is_product_sheet = 0;
				if ( in_array( $wpssw_sheetname, $wpssw_productsheets_list, true ) ) {
					$is_product_sheet = 1;
				}
				// Exclude existing order IDs if applicable.
				$wpssw_oldids     = parent::wpssw_convert_int( $wpssw_existingorders[ $wpssw_sheets[ $wpssw_sheetname ] ] );
				$wpssw_all_orders = array();
				if ( $hpos_order_enabled ) {
					if ( 'All Orders' === (string) $wpssw_sheetname || $is_product_sheet ) {
						if ( isset( $wpssw_fromdate ) && isset( $wpssw_todate ) && $wpssw_fromdate && $wpssw_todate ) {

							$wpssw_query_args = array(
								'limit'        => -1, // Fetch all orders.
								'orderby'      => 'date',
								'order'        => 'ASC',
								'status'       => array_keys( wc_get_order_statuses() ),
								'date_created' => $wpssw_fromdate . '...' . $wpssw_todate, // Date range.
								'return'       => 'ids', // Fetch only IDs.
							);
						} else {
							$wpssw_query_args = array(
								'limit'   => -1, // Fetch all orders.
								'orderby' => 'date',
								'order'   => 'ASC',
								'status'  => array_keys( wc_get_order_statuses() ),
								'return'  => 'ids', // Fetch only IDs.
							);
						}
					} else {
						if ( isset( $wpssw_fromdate ) && isset( $wpssw_todate ) && $wpssw_fromdate && $wpssw_todate ) {

							$wpssw_query_args = array(
								'limit'        => -1, // Fetch all orders.
								'orderby'      => 'date',
								'order'        => 'ASC',
								'status'       => $wpssw_sheet_slug,
								'date_created' => $wpssw_fromdate . '...' . $wpssw_todate, // Date range.
								'return'       => 'ids', // Fetch only IDs.
							);
						} else {
							$wpssw_query_args = array(
								'limit'   => -1, // Fetch all orders.
								'orderby' => 'date',
								'order'   => 'ASC',
								'status'  => $wpssw_sheet_slug,
								'return'  => 'ids', // Fetch only IDs.
							);
						}
					}

					if ( is_array( $wpssw_oldids ) && ! empty( $wpssw_oldids ) ) {
						$wpssw_query_args['exclude'] = $wpssw_oldids;
					}
					$wpssw_all_orders = wc_get_orders( $wpssw_query_args );
				} else {
					if ( 'All Orders' === (string) $wpssw_sheetname || $is_product_sheet ) {
						if ( isset( $wpssw_fromdate ) && isset( $wpssw_todate ) && $wpssw_fromdate && $wpssw_todate ) {
							$wpssw_query_args = array(
								'post_type'      => 'shop_order',
								'posts_per_page' => -1,
								'order'          => 'ASC',
								'post_status'    => array_keys( wc_get_order_statuses() ),
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
								'post_type'      => 'shop_order',
								'posts_per_page' => -1,
								'order'          => 'ASC',
								'post_status'    => array_keys( wc_get_order_statuses() ),
							);
						}
					} else {
						if ( isset( $wpssw_fromdate ) && isset( $wpssw_todate ) && $wpssw_fromdate && $wpssw_todate ) {
							$wpssw_query_args = array(
								'post_type'      => 'shop_order',
								'posts_per_page' => -1,
								'order'          => 'ASC',
								'post_status'    => $wpssw_sheet_slug,
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
								'post_type'      => 'shop_order',
								'post_status'    => $wpssw_sheet_slug,
								'posts_per_page' => -1,
								'order'          => 'ASC',
							);
						}
					}
					$wpssw_query_args['fields'] = 'ids'; // Fetch only ids.

					if ( is_array( $wpssw_oldids ) && ! empty( $wpssw_oldids ) ) {
						$wpssw_query_args['post__not_in'] = $wpssw_oldids;
					}

					$wpsswcustom_query = new WP_Query( $wpssw_query_args );
					$wpssw_all_orders  = $wpsswcustom_query->posts;
				}
				if ( empty( $wpssw_all_orders ) ) {
					continue;
				}
				$wpssw_values_array = array();
				$ordercount         = 0;
				if ( 'false' === (string) $wpssw_syncall ) {
					foreach ( $wpssw_all_orders as $wpssw_order_id ) {
						if ( $wpssw_check_category ) {
							if ( self::wpssw_check_product_category( $wpssw_order_id ) ) {
								continue;
							}
						}
						$wpssw_order = wc_get_order( $wpssw_order_id );
						if ( $is_product_sheet ) {
							$items_to_keep = self::wpssw_get_specific_items( $wpssw_order, $wpssw_sheets[ $wpssw_sheetname ] );
							if ( empty( $items_to_keep ) ) {
								continue;
							}
						}

						if ( 'false' === (string) $wpssw_syncall ) {
							$wpssw_orderdate = new DateTime( $wpssw_order->get_date_created()->format( 'Y-m-d' ) );
							$wpssw_datefrom  = new DateTime( $wpssw_fromdate );
							$wpssw_dateto    = new DateTime( $wpssw_todate );
							if ( $wpssw_orderdate < $wpssw_datefrom || $wpssw_orderdate > $wpssw_dateto ) {
								continue;
							}
						}
						$ordercount++;
					}
				} else {
					if ( $hpos_order_enabled ) {
						$ordercount = count( $wpssw_all_orders );
					} else {
						$ordercount = $wpsswcustom_query->found_posts;
					}
				}
				if ( $ordercount > 0 ) {
					$orderlimit = apply_filters( 'wpssw_order_sync_limit', 500 );
					$response[] = array(
						'sheet_name'  => $wpssw_sheets[ $wpssw_sheetname ],
						'sheet_slug'  => $wpssw_sheet_slug,
						'totalorders' => $ordercount,
						'orderlimit'  => $orderlimit,
					);
				}
			}
			echo wp_json_encode( $response );
			die;
		}
		/**
		 * Syncronize order data sheetwise
		 */
		public static function wpssw_sync_sheetswise() {
			if ( ! isset( $_POST['sync_nonce_token'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['sync_nonce_token'] ) ), 'sync_nonce' )
			) {
				echo esc_html__( 'Sorry, your nonce did not verify.', 'wpssw' );
				die();
			}
			if ( ! self::$instance_api->checkcredenatials() ) {
				return;
			}
			$wpssw_spreadsheetid = parent::wpssw_option( 'wpssw_woocommerce_spreadsheet' );
			$wpssw_inputoption   = parent::wpssw_option( 'wpssw_inputoption' );
			$wpssw_fromdate      = isset( $_POST['sync_all_fromdate'] ) ? sanitize_text_field( wp_unslash( $_POST['sync_all_fromdate'] ) ) : '';
			$wpssw_todate        = isset( $_POST['sync_all_todate'] ) ? sanitize_text_field( wp_unslash( $_POST['sync_all_todate'] ) ) : '';
			$wpssw_syncall       = isset( $_POST['sync_all'] ) ? sanitize_text_field( wp_unslash( $_POST['sync_all'] ) ) : '';
			if ( ! $wpssw_inputoption ) {
				$wpssw_inputoption = 'USER_ENTERED';
			}
			$wpssw_sheet_slug             = isset( $_POST['sheetslug'] ) ? sanitize_text_field( wp_unslash( $_POST['sheetslug'] ) ) : '';
			$wpssw_sheetname              = isset( $_POST['sheetname'] ) ? sanitize_text_field( wp_unslash( $_POST['sheetname'] ) ) : '';
			$wpssw_ordercount             = isset( $_POST['ordercount'] ) ? sanitize_text_field( wp_unslash( $_POST['ordercount'] ) ) : '';
			$wpssw_orderlimit             = isset( $_POST['orderlimit'] ) ? sanitize_text_field( wp_unslash( $_POST['orderlimit'] ) ) : '';
			$order_ascdesc                = parent::wpssw_option( 'wpssw_order_ascdesc' );
			$wpssw_productsheets_list     = array();
			$wpssw_product_names_as_sheet = parent::wpssw_option( 'product_names_as_sheet' );
			$is_product_sheet             = 0;
			if ( 'yes' === (string) $wpssw_product_names_as_sheet ) {
				$wpssw_productsheets_list = parent::wpssw_option( 'wpssw_productname_sheets' );
				if ( ! $wpssw_productsheets_list ) {
					$wpssw_productsheets_list = array();
				}
				if ( ! empty( $wpssw_sheetname ) && in_array( $wpssw_sheetname, $wpssw_productsheets_list, true ) ) {
					$is_product_sheet = 1;
				}
			}

			$wpssw_response            = self::$instance_api->get_sheet_listing( $wpssw_spreadsheetid );
			$wpssw_existingsheetsnames = self::$instance_api->get_sheet_list( $wpssw_response );
			$wpssw_sheetid             = $wpssw_existingsheetsnames[ $wpssw_sheetname ];
			$wpssw_sheet               = "'" . $wpssw_sheetname . "'!A:A";
			$wpssw_allentry            = self::$instance_api->get_row_list( $wpssw_spreadsheetid, $wpssw_sheet );
			$wpssw_data                = $wpssw_allentry->getValues();
			if ( ! is_array( $wpssw_data ) ) {
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
					$wpssw_data
				);
			}
			$hpos_order_enabled = parent::wpssw_check_hpos_order_setting_enabled();
			if ( $hpos_order_enabled ) {
				// Base query arguments.
				$base_query_args = array(
					'orderby' => 'ID',
					'order'   => 'ASC',
					'limit'   => isset( $wpssw_orderlimit ) ? $wpssw_orderlimit : -1, // Fetch all orders unless a limit is set.
					'return'  => 'ids', // Fetch only IDs.
				);

				// Determine order status based on sheet type.
				if ( 'all_order' === (string) $wpssw_sheet_slug || $is_product_sheet ) {
					$base_query_args['status'] = array_keys( wc_get_order_statuses() );
				} else {
					$base_query_args['status'] = $wpssw_sheet_slug;
				}

				// Date filtering.
				if ( isset( $wpssw_fromdate ) && isset( $wpssw_todate ) && ! empty( $wpssw_fromdate ) && ! empty( $wpssw_todate ) ) {
					$base_query_args['date_created'] = $wpssw_fromdate . '...' . $wpssw_todate;
				}

				// Exclude specific order IDs if applicable.
				if ( is_array( $wpssw_data ) && ! empty( $wpssw_data ) ) {
					$base_query_args['exclude'] = $wpssw_data;
				}

				// Order direction.
				if ( 'descorder' === (string) $order_ascdesc ) {
					$base_query_args['order'] = 'DESC';
				}

				// Fetch the orders.
				$wpssw_all_orders = wc_get_orders( $base_query_args );

			} else {
				if ( 'all_order' === (string) $wpssw_sheet_slug || $is_product_sheet ) {
					if ( isset( $wpssw_fromdate ) && isset( $wpssw_todate ) && ! empty( $wpssw_fromdate ) && ! empty( $wpssw_todate ) ) {
						$wpssw_query_args = array(
							'post_type'      => 'shop_order',
							'posts_per_page' => -1,
							'order'          => 'ASC',
							'orderby'        => 'ID',
							'post_status'    => array_keys( wc_get_order_statuses() ),
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
							'post_type'      => 'shop_order',
							'posts_per_page' => -1,
							'order'          => 'ASC',
							'orderby'        => 'ID',
							'post_status'    => array_keys( wc_get_order_statuses() ),
						);
					}
				} else {
					if ( isset( $wpssw_fromdate ) && isset( $wpssw_todate ) && ! empty( $wpssw_fromdate ) && ! empty( $wpssw_todate ) ) {
						$wpssw_query_args = array(
							'post_type'      => 'shop_order',
							'posts_per_page' => -1,
							'order'          => 'ASC',
							'orderby'        => 'ID',
							'post_status'    => $wpssw_sheet_slug,
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
							'post_type'      => 'shop_order',
							'post_status'    => $wpssw_sheet_slug,
							'posts_per_page' => -1,
							'order'          => 'ASC',
							'orderby'        => 'ID',
						);
					}
				}
				if ( 'descorder' === (string) $order_ascdesc ) {
					$wpssw_query_args['order'] = 'DESC';
				}
				$wpssw_query_args['fields'] = 'ids'; // Fetch only ids.

				if ( is_array( $wpssw_data ) && ! empty( $wpssw_data ) ) {
					$wpssw_query_args['post__not_in'] = $wpssw_data;
				}

				$wpssw_query_args['posts_per_page'] = $wpssw_orderlimit;
				$wpssw_query_args['fields']         = 'ids'; // Fetch only ids.

				$wpsswcustom_query = new WP_Query( $wpssw_query_args );
				$wpssw_all_orders  = $wpsswcustom_query->posts;
			}
			if ( empty( $wpssw_all_orders ) ) {
				die();
			}
			$wpssw_check_category      = false;
			$wpssw_category_filter     = parent::wpssw_option( 'wpssw_category_filter' );
			$wpssw_category_filter_ids = parent::wpssw_option( 'wpssw_category_filter_ids' );
			if ( $wpssw_category_filter && ! empty( $wpssw_category_filter_ids ) ) {
				$wpssw_check_category = true;
			}

			$wpssw_values_array = array();
			$neworder           = 0;
			foreach ( $wpssw_all_orders as $wpssw_order_id ) {

				if ( $wpssw_check_category ) {
					if ( self::wpssw_check_product_category( $wpssw_order_id ) ) {
						continue;
					}
				}
				$wpssw_order = wc_get_order( $wpssw_order_id );
				if ( 'false' === (string) $wpssw_syncall ) {
					$wpssw_orderdate = new DateTime( $wpssw_order->get_date_created()->format( 'Y-m-d' ) );
					$wpssw_datefrom  = new DateTime( $wpssw_fromdate );
					$wpssw_dateto    = new DateTime( $wpssw_todate );
					if ( $wpssw_orderdate < $wpssw_datefrom || $wpssw_orderdate > $wpssw_dateto ) {
						continue;
					}
				}
				if ( $is_product_sheet ) {
					$items_to_keep = self::wpssw_get_specific_items( $wpssw_order, $wpssw_sheetname );
					if ( empty( $items_to_keep ) ) {
						continue;
					}
				}
				if ( $neworder < $wpssw_orderlimit ) {
					set_time_limit( 999 );
					$wpssw_order_data = $wpssw_order->get_data();
					$wpssw_status     = $wpssw_order_data['status'];
					$wpssw_status     = str_replace( '-', ' ', $wpssw_status );
					$wpssw_is_allowed = true;
					if ( 'all_order' === (string) $wpssw_sheet_slug ) {
						$wpssw_is_allowed = apply_filters( 'wpssw_order_status_allow', true, ucwords( $wpssw_status ) . ' Orders' );
					}
					if ( $wpssw_is_allowed ) {
						if ( $is_product_sheet ) {
							$wpssw_value = self::wpssw_make_value_array( 'insert', $wpssw_order_id, $wpssw_sheetname );
						} else {
							$wpssw_value = self::wpssw_make_value_array( 'insert', $wpssw_order_id );
						}
						$wpssw_values_array = array_merge( $wpssw_values_array, $wpssw_value );
					}
					$neworder++;
				}
			}
			$rangetofind = $wpssw_sheetname . '!A' . ( count( $wpssw_data ) + 1 );
			if ( ! empty( $wpssw_values_array ) ) {
				try {

					// If there is no sheet_id available, leave the sheet_id field blank. Otherwise, if there is a sheet_id, leave the sheet_name field blank.
					$wpssw_inherit_params = array(
						'sheet_id'       => $wpssw_sheetid,
						'sheet_name'     => '',
						'start_index'    => count( $wpssw_data ),
						'end_index'      => count( $wpssw_data ) + count( $wpssw_values_array ),
						'spreadsheet_id' => $wpssw_spreadsheetid,
					);

					parent::wpssw_inherit_row_format_style( 'order', $wpssw_inherit_params );

					$wpssw_params      = array( 'valueInputOption' => $wpssw_inputoption );
					$wpssw_requestbody = self::$instance_api->valuerangeobject( $wpssw_values_array );
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
		 * Insert event data into sheet provided by $wpssw_sheetname
		 *
		 * @param int    $wpssw_orderid .
		 * @param string $wpssw_sheetname .
		 */
		public static function wpssw_insert_event_data_into_sheet( $wpssw_orderid, $wpssw_sheetname ) {
			try {
				if ( ! self::$instance_api->checkcredenatials() ) {
					return;
				}
				if ( ! $wpssw_orderid ) {
					return;
				}
				if ( ! empty( $wpssw_sheetname ) ) {
					$wpssw_order  = wc_get_order( $wpssw_orderid );
					$wpssw_values = self::wpssw_make_value_array( 'insert', $wpssw_order->get_id(), $wpssw_sheetname );

					$wpssw_spreadsheetid = parent::wpssw_option( 'wpssw_woocommerce_spreadsheet' );

					if ( ! parent::wpssw_check_sheet_exist( $wpssw_spreadsheetid, $wpssw_sheetname ) ) {
						return;
					}
					$wpssw_header_type  = self::wpssw_is_productwise();
					$wpssw_headers_name = parent::wpssw_option( 'wpssw_sheet_headers_list' );
					if ( parent::wpssw_is_event_calender_ticket_active() ) {
						$wpssw_woo_event_headers = parent::wpssw_option( 'wpssw_woo_event_headers' );
						if ( ! is_array( $wpssw_woo_event_headers ) ) {
							$wpssw_woo_event_headers = array();
						}
						$wpssw_headers_name = array_merge( $wpssw_headers_name, $wpssw_woo_event_headers );
					}
					if ( empty( $wpssw_spreadsheetid ) ) {
						return;
					}
					$wpssw_response = self::$instance_api->get_sheet_listing( $wpssw_spreadsheetid );

					$wpssw_existingsheetsnames = self::$instance_api->get_sheet_list( $wpssw_response );
					$wpssw_existingsheets      = array_flip( $wpssw_existingsheetsnames );
					$wpssw_sheetid             = $wpssw_existingsheetsnames[ $wpssw_sheetname ];
					$wpssw_rangetofind         = $wpssw_sheetname . '!A:A';

					$wpssw_allentry = self::$instance_api->get_row_list( $wpssw_spreadsheetid, $wpssw_rangetofind );
					$wpssw_data     = $wpssw_allentry->getValues();
					$wpssw_data     = array_map(
						function( $element ) {
							if ( isset( $element['0'] ) ) {
								return $element['0'];
							} else {
								return '';
							}
						},
						$wpssw_data
					);

					$wpssw_num = array_search( (int) $wpssw_order->get_id(), parent::wpssw_convert_int( $wpssw_data ), true );

					if ( $wpssw_num > 0 ) {
						$wpssw_rownum = $wpssw_num + 1;
						// Add or Remove Row at spreadsheet.
						$wpssw_ordrow   = 0;
						$wpssw_notempty = 0;
						end( $wpssw_data );
						$wpssw_lastelement = key( $wpssw_data );
						reset( $wpssw_data );

						$wpssw_data_count = count( $wpssw_data );
						for ( $i = $wpssw_rownum; $i < $wpssw_data_count; $i++ ) {
							if ( (int) $wpssw_data[ $i ] === (int) $wpssw_order->get_id() ) {
								$wpssw_ordrow++;
								if ( (int) $wpssw_lastelement === (int) $i ) {
									$wpssw_ordrow++;
								}
							} else {
								if ( (int) $wpssw_lastelement === (int) $i ) {
									$wpssw_notempty = 1;
									if ( $wpssw_ordrow > 0 ) {
										$wpssw_ordrow++;
									}
								} else {
									$wpssw_ordrow++;
								}
								break;
							}
						}
						$wpssw_samerow = 0;
						if ( 0 === (int) $wpssw_ordrow ) {
							$wpssw_samerow = 1;
						}
						if ( 1 === (int) $wpssw_samerow && $wpssw_header_type && 0 === (int) $wpssw_notempty ) {

							$wpssw_alphabet   = range( 'A', 'Z' );
							$wpssw_alphaindex = '';
							$wpssw_is_id      = array_search( 'Product ID', $wpssw_headers_name, true );
							if ( $wpssw_is_id ) {
								if ( isset( $wpssw_alphabet[ $wpssw_is_id + 1 ] ) ) {
									$wpssw_alphaindex = $wpssw_alphabet[ $wpssw_is_id + 1 ];
								}
							} else {
								$wpssw_is_name = array_search( 'Product Name', $wpssw_headers_name, true );
								if ( $wpssw_is_name && isset( $wpssw_alphabet[ $wpssw_is_name + 1 ] ) ) {
									$wpssw_alphaindex = $wpssw_alphabet[ $wpssw_is_name + 1 ];
								}
							}
							if ( '' !== (string) $wpssw_alphaindex ) {
								$wpssw_rangetofind = $wpssw_sheetname . '!' . $wpssw_alphaindex . $wpssw_rownum . ':' . $wpssw_alphaindex;
								$wpssw_allentry    = self::$instance_api->get_row_list( $wpssw_spreadsheetid, $wpssw_rangetofind );
								$wpssw_data        = $wpssw_allentry->getValues();
								$wpssw_data        = array_map(
									function( $wpssw_element ) {
										if ( isset( $wpssw_element['0'] ) ) {
											return $wpssw_element['0'];
										} else {
											return '';
										}
									},
									$wpssw_data
								);
								if ( ( count( $wpssw_values ) < count( $wpssw_data ) ) ) {
									$wpssw_ordrow  = count( $wpssw_data );
									$wpssw_samerow = 0;
								}
							}
						}
						if ( 1 === (int) $wpssw_notempty && 0 === (int) $wpssw_ordrow ) {
							$wpssw_samerow = 0;
							$wpssw_ordrow  = 1;
						}
						if ( ( count( $wpssw_values ) > (int) $wpssw_ordrow ) && 0 === (int) $wpssw_samerow ) {// Insert blank row into spreadsheet.

							$wpssw_endindex      = count( $wpssw_values ) - (int) $wpssw_ordrow;
							$wpssw_endindex      = (int) $wpssw_endindex + (int) $wpssw_rownum;
							$wpssw_param         = self::$instance_api->prepare_param( $wpssw_sheetid, $wpssw_rownum, $wpssw_endindex );
							$wpssw_inherit_style = parent::wpssw_option( 'wpssw_order_inherit_style' );
							if ( 'no' === (string) $wpssw_inherit_style ) {
								$wpssw_batchupdaterequest = self::$instance_api->insertdimensionobject( $wpssw_param, false );
							} else {
								$wpssw_batchupdaterequest = self::$instance_api->insertdimensionobject( $wpssw_param, true );
							}
							$requestobject                  = array();
							$requestobject['spreadsheetid'] = $wpssw_spreadsheetid;
							$requestobject['requestbody']   = $wpssw_batchupdaterequest;
							$wpssw_response                 = self::$instance_api->formatsheet( $requestobject );
						} elseif ( count( $wpssw_values ) < (int) $wpssw_ordrow && 0 === (int) $wpssw_samerow ) {// Remove extra row from spreadhseet.

							$wpssw_endindex         = (int) $wpssw_ordrow - count( $wpssw_values );
							$wpssw_endindex         = (int) $wpssw_endindex + (int) $wpssw_rownum;
							$param                  = self::$instance_api->prepare_param( $wpssw_sheetid, $wpssw_rownum, $wpssw_endindex );
							$deleterequest          = self::$instance_api->deleteDimensionrequests( $param, 'ROWS' );
							$param                  = array();
							$param['spreadsheetid'] = $wpssw_spreadsheetid;
							$param['requestarray']  = $deleterequest;
							self::$instance_api->updatebachrequests( $param );
						}
						// End of add- remove row at spreadsheet.

						$wpssw_rangetoupdate1 = $wpssw_sheetname . '!A' . $wpssw_rownum;
						$wpssw_requestbody1   = self::$instance_api->valuerangeobject( $wpssw_values );
						$wpssw_inputoption    = parent::wpssw_option( 'wpssw_inputoption' );
						if ( ! $wpssw_inputoption ) {
							$wpssw_inputoption = 'USER_ENTERED';
						}
						$wpssw_params1 = array( 'valueInputOption' => $wpssw_inputoption ); // USER_ENTERED.
						$param         = self::$instance_api->setparamater( $wpssw_spreadsheetid, $wpssw_rangetoupdate1, $wpssw_requestbody1, $wpssw_params1 );

						$wpssw_response = self::$instance_api->updateentry( $param );
					}
				}
			} catch ( Exception $e ) {
				echo esc_html( 'Message: ' . $e->getMessage() );
			}
		}
		/**
		 * Add Sync button in meta box.
		 */
		public static function wpssw_syncbtn_meta_box_content() {
			$hpos_order_enabled = parent::wpssw_check_hpos_order_setting_enabled();
			echo '<a href="#" id="wpssw_metabox_sync_btn" class="button wpssw_single_order_sync_btn" data-hpos=' . esc_attr( $hpos_order_enabled ) . '>' . esc_html__( 'Click to Sync', 'wpssw' ) . '</a>
					<img src="' . esc_url( admin_url( 'images/spinner.gif' ) ) . '" id="wpssw_metabox_syncbtnloader" class="syncbtnloader">';
		}
		/**
		 * Check which header type is selected productwise or orderwise.
		 */
		public static function wpssw_is_productwise() {
			$wpssw_header_type = parent::wpssw_option( 'wpssw_header_format' );
			if ( 'productwise' === (string) $wpssw_header_type ) {
				return true;
			}
			return false;
		}
		/**
		 * Get order items meta
		 *
		 * @param object $wpssw_item .
		 */
		public static function wpssw_getItemmeta( $wpssw_item = '' ) {
			$wpssw_meta_html = '';
			if ( ! empty( $wpssw_item ) ) {
				$wpssw_variationame = '';
				if ( $wpssw_item->get_variation_id() ) {
					$wpssw_variation    = wc_get_product( $wpssw_item->get_variation_id() );
					$wpssw_variationame = wp_strip_all_tags( $wpssw_variation->get_formatted_name() );
				}
				$wpssw_meta_html .= 'Variation Name: ' . $wpssw_variationame . '(' . $wpssw_item->get_variation_id() . ') ,'; // the Variation id.
				if ( $wpssw_item->get_tax_class() ) {
					$wpssw_meta_html .= 'Tax Class:' . $wpssw_item->get_tax_class() . ',';
				}
				$wpssw_meta_html .= 'Line subtotal:' . $wpssw_item->get_subtotal() . ','; // Line subtotal (non discounted).
				if ( $wpssw_item->get_subtotal_tax() ) {
					$wpssw_meta_html .= 'Line subtotal tax:' . $wpssw_item->get_subtotal_tax() . ','; // Line subtotal tax (non discounted).
				}
				$wpssw_meta_html .= 'Line total:' . $wpssw_item->get_total() . ','; // Line total (discounted).
				if ( $wpssw_item->get_total_tax() ) {
					$wpssw_meta_html .= 'Line total tax:' . $wpssw_item->get_total_tax(); // Line total tax (discounted).
				}
			}
			$wpssw_meta_html = rtrim( $wpssw_meta_html, ',' );
			return $wpssw_meta_html;
		}
		/**
		 * Clean Order data array.
		 *
		 * @param array $wpssw_array Order data array.
		 * @return array $wpssw_array
		 */
		public static function wpssw_order_clean_array( $wpssw_array ) {
			$wpssw_max = count( parent::wpssw_option( 'wpssw_sheet_headers_list' ) ) + 1;
			if ( parent::wpssw_is_event_calender_ticket_active() ) {
				$wpssw_woo_event_headers = parent::wpssw_option( 'wpssw_woo_event_headers' );
				if ( ! is_array( $wpssw_woo_event_headers ) ) {
					$wpssw_woo_event_headers = array();
				}
				$wpssw_max = count( $wpssw_woo_event_headers ) + $wpssw_max;
			}
			$wpssw_array = parent::wpssw_cleanarray( $wpssw_array, $wpssw_max );
			return $wpssw_array;
		}
		/**
		 * Get all order notes of given order id.
		 *
		 * @param int $order_id .
		 * @return array
		 */
		public static function wpssw_get_all_order_notes( $order_id ) {
			$order_notes = array();
			$args        = array(
				'post_id' => $order_id,
				'orderby' => 'comment_ID',
				'order'   => 'DESC',
				'approve' => 'approve',
				'type'    => 'order_note',
			);
			remove_filter(
				'comments_clauses',
				array(
					'WC_Comments',
					'exclude_order_comments',
				),
				10,
				1
			);
			$notes                           = get_comments( $args );
			$exculde_order_status_from_notes = parent::wpssw_option( 'wpssw_exclude_order_status_from_notes' );
			if ( $notes ) {
				foreach ( $notes as $note ) {
					if ( 'yes' === (string) $exculde_order_status_from_notes ) {
						$transition_strings = array( 'order status changed', 'Payment to be made upon delivery. Order status changed from', 'starea comenzii a fost modificată', 'Plata care se va face la livrare. Starea comenzii a fost modificată din' );
						$skip_comment       = false;
						foreach ( $transition_strings as $str ) {
							if ( 0 === strpos( strtolower( $note->comment_content ), strtolower( $str ) ) ) {
								$skip_comment = true;
								break;
							}
						}
						if ( true === $skip_comment ) {
							continue;
						}
						$order_notes[] = wp_kses_post( $note->comment_content );
					} else {
						$order_notes[] = wp_kses_post( $note->comment_content );
					}
				}
			}
			return $order_notes;
		}
		/**
		 * Format the price values on selecting Price Format option
		 *
		 * @param int|float $price .
		 */
		public static function wpssw_get_formatted_values( $price ) {
			$wpssw_price_format = parent::wpssw_option( 'wpssw_price_format' );
			if ( ! $wpssw_price_format ) {
				$wpssw_price_format = 'plain';
			}
			$wpssw_plain     = '';
			$wpssw_formatted = '';
			if ( 'plain' === (string) $wpssw_price_format || (int) $price < 1 ) {
				return $price;
			}
			$priceargs         = wp_parse_args(
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
			$unformatted_price = $price;
			$negative          = $price < 0;
			$price             = floatval( $negative ? (int) $price * -1 : $price );
			$price             = number_format( $price, $priceargs['decimals'], $priceargs['decimal_separator'], $priceargs['thousand_separator'] );
			$formatted_price   = ( $negative ? '-' : '' ) . sprintf( $priceargs['price_format'], get_woocommerce_currency_symbol( $priceargs['currency'] ), $price );
			return html_entity_decode( $formatted_price );
		}
		/**
		 * Prepare static array value of order data.
		 *
		 * @param int    $wpssw_order_id .
		 * @param string $wpssw_old_staus_name .
		 * @param array  $wpssw_prdarray .
		 * @return array $wpssw_prdarray
		 */
		public static function wpssw_set_static_values( $wpssw_order_id = 0, $wpssw_old_staus_name = '', $wpssw_prdarray = array() ) {
			// Add Static Values to new values array.
			$wpssw_spreadsheetid = parent::wpssw_option( 'wpssw_woocommerce_spreadsheet' );
			$wpssw_headers_name  = parent::wpssw_option( 'wpssw_sheet_headers_list' );
			if ( parent::wpssw_is_event_calender_ticket_active() ) {
				$wpssw_woo_event_headers = parent::wpssw_option( 'wpssw_woo_event_headers' );
				if ( ! is_array( $wpssw_woo_event_headers ) ) {
					$wpssw_woo_event_headers = array();
				}
				$wpssw_headers_name = array_merge( $wpssw_headers_name, $wpssw_woo_event_headers );
			}
			$wpssw_static_header = stripslashes_deep( parent::wpssw_option( 'wpssw_static_header' ) );
			if ( ! $wpssw_static_header ) {
				$wpssw_static_header = array();
			}
			if ( ! empty( $wpssw_old_staus_name ) && ! empty( $wpssw_static_header ) ) {
				$wpssw_rangetofind = $wpssw_old_staus_name . '!A:A';
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
				$wpssw_num         = array_search( (int) $wpssw_order_id, parent::wpssw_convert_int( $wpssw_data ), true );
				if ( $wpssw_num > 0 ) {
					$wpssw_rownum = $wpssw_num + 1;
					// Add or Remove Row at spreadsheet.
					$wpssw_ordrow   = 0;
					$wpssw_notempty = 0;
					end( $wpssw_data );
					$wpssw_lastelement = key( $wpssw_data );
					reset( $wpssw_data );
					$wpssw_data_count = count( $wpssw_data );
					for ( $i = $wpssw_rownum; $i < $wpssw_data_count; $i++ ) {
						if ( (int) $wpssw_data[ $i ] === (int) $wpssw_order_id ) {
							$wpssw_ordrow++;
							if ( (int) $wpssw_lastelement === (int) $i ) {
								break;
							}
						} else {
							break;
						}
					}
					$wpssw_start_row       = $wpssw_rownum;
					$wpssw_end_row         = (int) $wpssw_rownum + (int) $wpssw_ordrow;
					$wpssw_old_sheet_range = $wpssw_old_staus_name . '!A' . $wpssw_start_row . ':ZZZ' . $wpssw_end_row;
					try {
						$wpssw_response    = self::$instance_api->get_row_list( $wpssw_spreadsheetid, $wpssw_old_sheet_range );
						$wpssw_olddata     = $wpssw_response->getValues();
						$wpss_static_index = array();
						foreach ( $wpssw_static_header as $h ) {
							if ( in_array( $h, $wpssw_headers_name, true ) ) {
								$wpss_static_index[] = array_search( $h, $wpssw_headers_name, true ) + 1;
							}
						}
						$wpssw_cnt = 0;
						foreach ( $wpssw_prdarray as &$newvalue ) {
							foreach ( $wpss_static_index as $inx ) {
								if ( isset( $wpssw_olddata[ $wpssw_cnt ][ $inx ] ) ) {
									$newvalue[ $inx ] = $wpssw_olddata[ $wpssw_cnt ][ $inx ];
								}
							}
							$wpssw_cnt++;
						}
						return $wpssw_prdarray;
					} catch ( Exception $e ) {
						echo esc_html( 'Message: ' . $e->getMessage() );
					}
				}
			}
			return $wpssw_prdarray;
		}
		/**
		 * Restore a post from the Trash
		 *
		 * @param string $wpssw_new_status New status of post.
		 * @param string $wpssw_old_status Old status of post.
		 * @param object $wpssw_post Post to restore.
		 */
		public static function wpssw_wcgs_restore( $wpssw_new_status, $wpssw_old_status, $wpssw_post ) {
			$wpssw_order_spreadsheet_setting = self::wpssw_check_order_spreadsheet_setting();
			if ( 'yes' !== (string) $wpssw_order_spreadsheet_setting ) {
				return;
			}
			global $post_type;
			$wpssw_post_type = is_object( $post_type ) ? $post_type->name : $post_type;
			// @codingStandardsIgnoreStart.
			if ( ( 'shop_order' !== (string) $wpssw_post_type ) || ( isset( $_REQUEST['action'] ) && 'untrash' !== sanitize_text_field( wp_unslash($_REQUEST['action'] ) ) ) ) {
				return;
			}

			$wpssw_rsync = apply_filters( 'wpssw_realtime_sync', false );
			if ( $wpssw_rsync && isset( $_GET['action'] ) && 'untrash' === sanitize_text_field( wp_unslash( $_GET['action'] ) ) ) {
				// @codingStandardsIgnoreEnd.
				return;
			}

			$wpssw_order = wc_get_order( $wpssw_post->ID );
			if ( isset( $wpssw_order ) && ! empty( $wpssw_order ) ) {
				$wpssw_sheetname = substr( $wpssw_new_status, strpos( $wpssw_new_status, '-' ) + 1 );
				if ( 'trash' === (string) $wpssw_old_status ) {
					self::wpssw_woo_order_status_change_custom( $wpssw_post->ID, 'trash', $wpssw_sheetname );
				}
			}
		}
		/**
		 *
		 * Move a order to the Trash
		 *
		 * @param int $wpssw_order_id .
		 */
		public static function wpssw_wcgs_trash_order( $wpssw_order_id ) {
			$wpssw_order = wc_get_order( $wpssw_order_id );
			$wpssw_rsync = apply_filters( 'wpssw_realtime_sync', false );

			if ( ! is_wp_error( $wpssw_order ) && ! empty( $wpssw_order ) ) {

				$wpssw_order_spreadsheet_setting = self::wpssw_check_order_spreadsheet_setting();
				if ( 'yes' !== (string) $wpssw_order_spreadsheet_setting ) {
					return;
				}
				// @codingStandardsIgnoreStart.
				if ( $wpssw_rsync ) {
					// @codingStandardsIgnoreEnd.
					return;
				}

				$wpssw_sheets = array_filter( (array) self::wpssw_option( 'wpssw_sheets' ) );
				if ( ! $wpssw_sheets ) {
					$wpssw_sheets = self::wpssw_prepare_sheets();
				}
				if ( $wpssw_order ) {
					$wpssw_old_status = trim( $wpssw_order->get_meta( '_wp_trash_meta_status', true ) );
					if ( 0 === strpos( $wpssw_old_status, 'wc-' ) ) {
						$wpssw_old_status = str_replace( 'wc-', '', $wpssw_old_status );
					}
					/*Remove order detail from old status and move to Trash sheet*/
					self::wpssw_woo_order_status_change_custom( $wpssw_order_id, $wpssw_old_status, 'trash' );
				}
				// phpcs:ignore.
				if ( array_key_exists( 'All Orders', $wpssw_sheets ) ) {
					$wpssw_sheetname = $wpssw_sheets['All Orders'];
					self::wpssw_all_orders( $wpssw_order_id, $wpssw_sheetname, 'trash' );
				}
			}
		}
		/**
		 * Move (Delete) order data from sheet provided by $wpssw_sheetname.
		 *
		 * @param int    $wpssw_order_id .
		 * @param string $wpssw_sheetid .
		 * @param string $wpssw_sheetname .
		 */
		public static function wpssw_move_order( $wpssw_order_id, $wpssw_sheetid, $wpssw_sheetname ) {

			if ( self::wpssw_check_product_category( $wpssw_order_id ) ) {
				return;
			}
			$wpssw_spreadsheetid = parent::wpssw_option( 'wpssw_woocommerce_spreadsheet' );
			if ( ! parent::wpssw_check_sheet_exist( $wpssw_spreadsheetid, $wpssw_sheetname ) ) {
				return;
			}
			$wpssw_rangetofind = $wpssw_sheetname . '!A:A';
			$wpssw_allentry    = self::$instance_api->get_row_list( $wpssw_spreadsheetid, $wpssw_rangetofind );
			$wpssw_data        = $wpssw_allentry->getValues();
			do_action( 'wpssw_move_order', $wpssw_order_id, $wpssw_sheetname );
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
			$wpssw_order      = wc_get_order( $wpssw_order_id );
			$wpssw_item_count = $wpssw_order->get_items();
			$wpssw_num        = array_search( (int) $wpssw_order_id, parent::wpssw_convert_int( $wpssw_data ), true );
			if ( $wpssw_num > 0 ) {
				$wpssw_startindex  = $wpssw_num;
				$wpssw_header_type = self::wpssw_is_productwise();
				if ( $wpssw_header_type ) {
					$wpssw_endindex = count( $wpssw_item_count );
					$wpssw_endindex = $wpssw_num + $wpssw_endindex;
				} else {
					$wpssw_endindex = $wpssw_num + 1;
				}
				$param                  = array();
				$param                  = self::$instance_api->prepare_param( $wpssw_sheetid, $wpssw_startindex, $wpssw_endindex );
				$wpssw_requestbody      = self::$instance_api->deleteDimensionrequests( $param, 'ROWS' );
				$param                  = array();
				$param['spreadsheetid'] = $wpssw_spreadsheetid;
				$param['requestarray']  = $wpssw_requestbody;
				$wpssw_response         = self::$instance_api->updatebachrequests( $param );
			}
		}
		/**
		 * Added v4.6
		 * Get product categories
		 *
		 * @return array $wpssw_product_category_list product category listing array
		 */
		public static function wpssw_get_product_categories() {
			$wpssw_product_category_list = array();
			$args                        = array(
				'taxonomy' => 'product_cat',
				'orderby'  => 'name',
			);
			$product_categories          = get_terms( $args );
			foreach ( $product_categories as $prd_cat ) {
				$wpssw_product_category_list[ $prd_cat->term_id ] = $prd_cat->name;
			}
			return $wpssw_product_category_list;
		}
		/**
		 * Create new sheets in selected spreadsheet for General settings Default Order Status options
		 *
		 * @param array $wpssw_data .
		 * @return string $wpssw_spreadsheetid
		 */
		public static function wpssw_create_sheet( $wpssw_data ) {
			if ( ! isset( $wpssw_data['wpssw_general_settings'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $wpssw_data['wpssw_general_settings'] ) ), 'save_general_settings' ) ) {
				return;
			}
			$wpssw_inputoption = parent::wpssw_option( 'wpssw_inputoption' );
			if ( ! $wpssw_inputoption ) {
				$wpssw_inputoption = 'USER_ENTERED';
			}
			if ( 'new' === (string) $wpssw_data['spreadsheetselection'] ) {
				$wpssw_newsheetname = trim( $wpssw_data['spreadsheetname'] );

				/*
				*Create new spreadsheet
				*/
				$requestbody         = self::$instance_api->createspreadsheetobject( $wpssw_newsheetname );
				$wpssw_response      = self::$instance_api->createspreadsheet( $requestbody );
				$wpssw_spreadsheetid = $wpssw_response['spreadsheetId'];
			} else {
				$wpssw_spreadsheetid = $wpssw_data['woocommerce_spreadsheet'];
			}

			if ( ! empty( $wpssw_data['header_fields'] ) ) {
				if ( isset( $wpssw_data['prdassheetheaders'] ) && isset( $wpssw_data['wpssw_append_after'] ) && in_array(
					$wpssw_data['wpssw_append_after'],
					array_map(
						function( $key ) {
							return str_replace( ' ', '-', strtolower( $key ) ); },
						$wpssw_data['header_fields']
					),
					true
				) ) {
					$flag                = 0;
					$wpssw_header        = array();
					$wpssw_header_custom = array();
					foreach ( $wpssw_data['header_fields'] as $headers ) {
						$wpssw_header[]        = $headers;
						$wpssw_header_custom[] = $wpssw_data['header_fields_custom'][ $flag ];
						if ( str_replace( ' ', '-', strtolower( $headers ) ) === $wpssw_data['wpssw_append_after'] && ! empty( $wpssw_data['product_header_fields'] ) ) {
							foreach ( $wpssw_data['product_header_fields'] as $prd_header ) {
								$wpssw_header[] = $prd_header;
							}
							foreach ( $wpssw_data['product_header_fields_custom'] as $prd_header ) {
								$wpssw_header_custom[] = $prd_header;
							}
						}
						$flag++;
					}
				} else {
					if ( isset( $wpssw_data['prdassheetheaders'] ) && ! empty( $wpssw_data['product_header_fields'] ) && isset( $wpssw_data['wpssw_append_after'] ) && ! empty( $wpssw_data['wpssw_append_after'] ) ) {
						$wpssw_header        = array_merge( $wpssw_data['header_fields'], $wpssw_data['product_header_fields'] );
						$wpssw_header_custom = array_merge( $wpssw_data['header_fields_custom'], $wpssw_data['product_header_fields_custom'] );
					} else {
						$wpssw_header        = $wpssw_data['header_fields'];
						$wpssw_header_custom = $wpssw_data['header_fields_custom'];
					}
				}
				$wpssw_headers       = stripslashes_deep( $wpssw_header );
				$wpssw_header_custom = stripslashes_deep( $wpssw_header_custom );
			} else {
				$wpssw_headers       = stripslashes_deep( parent::wpssw_option( 'wpssw_sheet_headers_list' ) );
				$wpssw_header_custom = stripslashes_deep( parent::wpssw_option( 'wpssw_sheet_headers_list_custom' ) );
			}
			if ( ! $wpssw_headers ) {
				$wpssw_headers = array();
			}
			if ( ! $wpssw_header_custom ) {
				$wpssw_header_custom = array();
			}
			$wpssw_eventsheet_list          = array();
			$wpssw_woo_event_headers        = array();
			$wpssw_woo_event_headers_custom = array();
			if ( parent::wpssw_is_event_calender_ticket_active() ) {
				$wpssw_woo_event_headers        = parent::wpssw_option( 'wpssw_woo_event_headers' );
				$wpssw_woo_event_headers_custom = parent::wpssw_option( 'wpssw_woo_event_headers_custom' );
				if ( ! is_array( $wpssw_woo_event_headers ) ) {
					$wpssw_woo_event_headers = array();
				}
				if ( ! is_array( $wpssw_woo_event_headers_custom ) ) {
					$wpssw_woo_event_headers_custom = array();
				}
				$wpssw_headers         = array_merge( $wpssw_headers, $wpssw_woo_event_headers );
				$wpssw_header_custom   = array_merge( $wpssw_header_custom, $wpssw_woo_event_headers_custom );
				$wpssw_eventsheet_list = parent::wpssw_option( 'wpssw_eventsheets_list' );
			}
			if ( ! is_array( $wpssw_eventsheet_list ) ) {
				$wpssw_eventsheet_list = array();
			}
			$wpssw_value        = array();
			$wpssw_value_custom = array();
			if ( count( $wpssw_headers ) > 0 ) {
				array_unshift( $wpssw_headers, 'Order Id' );
				$wpssw_value = array( $wpssw_headers );
			}
			if ( count( $wpssw_header_custom ) > 0 ) {
				array_unshift( $wpssw_header_custom, 'Order Id' );
				$wpssw_value_custom = array( $wpssw_header_custom );
			}
			$wpssw_sheets_default = self::$wpssw_default_sheets;
			$response             = self::$instance_api->get_sheet_listing( $wpssw_spreadsheetid );
			$wpssw_existingsheets = self::$instance_api->get_sheet_list( $response );

			if ( isset( $wpssw_data['wpssw_customsheets'] ) && ! empty( $wpssw_data['wpssw_customsheets'] ) ) {

				$wpssw_defaultnames        = isset( $wpssw_data['wpssw_sheets'] ) ? array_merge( $wpssw_data['wpssw_sheets'], $wpssw_data['wpssw_customsheets'] ) : stripslashes_deep( $wpssw_data['wpssw_customsheets'] );
				$wpssw_customsheets_custom = isset( $wpssw_data['wpssw_customsheets_custom'] ) ? $wpssw_data['wpssw_customsheets_custom'] : array();
				$wpssw_editednames         = isset( $wpssw_data['wpssw_sheets_custom'] ) ? array_merge( $wpssw_data['wpssw_sheets_custom'], $wpssw_customsheets_custom ) : stripslashes_deep( $wpssw_customsheets_custom );
				$wpssw_sheets              = array_combine( $wpssw_defaultnames, $wpssw_editednames );
			} else {
				$wpssw_defaultnames = isset( $wpssw_data['wpssw_sheets'] ) ? stripslashes_deep( $wpssw_data['wpssw_sheets'] ) : array();
				$wpssw_editednames  = isset( $wpssw_data['wpssw_sheets_custom'] ) ? stripslashes_deep( $wpssw_data['wpssw_sheets_custom'] ) : $wpssw_defaultnames;
				$wpssw_sheets       = array_combine( $wpssw_defaultnames, $wpssw_editednames );
			}

			$wpssw_old_sheets          = (array) parent::wpssw_option( 'wpssw_sheets' );
			$wpssw_old_products_sheets = array();
			$wpssw_productsheets_list  = array();
			$wpssw_remove_sheet        = array();
			if ( isset( $wpssw_data['product_names_as_sheet'] ) ) {
				$wpssw_old_products_sheets = parent::wpssw_option( 'wpssw_productname_sheets' );
				if ( ! $wpssw_old_products_sheets ) {
					$wpssw_old_products_sheets = array();
				}
				$args        = array(
					'post_type'      => 'product',
					'posts_per_page' => -1,
					'orderby'        => 'ID',
					'order'          => 'ASC',
					'fields'         => 'ids', // Fetch only ids.
				);
				$loop        = new WP_Query( $args );
				$product_ids = $loop->posts;
				foreach ( $product_ids as $product_id ) {
					$product_title                           = get_the_title( $product_id );
					$wpssw_productsheets_list[ $product_id ] = $product_title;
					$product_sheets_strlower[ $product_id ]  = strtolower( $product_title );

					$wpssw_existingsheets_strlower = array_map( 'strtolower', array_flip( $wpssw_existingsheets ) );
					if ( in_array( strtolower( $product_title ), $wpssw_existingsheets_strlower, true ) ) {
						$key                                     = array_search( strtolower( $product_title ), $wpssw_existingsheets_strlower, true );
						$wpssw_productsheets_list[ $product_id ] = array_search( $key, $wpssw_existingsheets, true );
					} elseif ( in_array( strtolower( $product_title ), $product_sheets_strlower, true ) ) {
						$key                                     = array_search( strtolower( $product_title ), $product_sheets_strlower, true );
						$wpssw_productsheets_list[ $product_id ] = $wpssw_productsheets_list[ $key ];
					}
				}
			}

			if ( 'new' !== (string) $wpssw_data['woocommerce_spreadsheet'] ) {
				foreach ( $wpssw_sheets as $sheetkey => $sheetname ) {
					if ( array_key_exists( $sheetkey, $wpssw_old_sheets ) ) {
						if ( $sheetname !== $wpssw_old_sheets[ $sheetkey ] ) {
							$wpssw_sheetid = isset( $wpssw_existingsheets[ $wpssw_old_sheets[ $sheetkey ] ] ) ? $wpssw_existingsheets[ $wpssw_old_sheets[ $sheetkey ] ] : '';
							if ( $wpssw_sheetid && $sheetname ) {
								$param                  = array();
								$param['spreadsheetid'] = $wpssw_spreadsheetid;
								$param['sheetid']       = $wpssw_sheetid;
								$param['newsheetname']  = trim( $sheetname );
								$wpssw_response         = self::$instance_api->updatesheetnameobject( $param );
							}
						}
					}
				}
				if ( ! empty( $wpssw_productsheets_list ) ) {
					foreach ( $wpssw_productsheets_list as $product_id => $product_name ) {
						if ( array_key_exists( $product_id, $wpssw_old_products_sheets ) ) {
							if ( strtolower( $product_name ) !== strtolower( $wpssw_old_products_sheets[ $product_id ] ) ) {
								$wpssw_existingsheets_strlower = array_map( 'strtolower', array_flip( $wpssw_existingsheets ) );
								if ( ! in_array( strtolower( $product_name ), $wpssw_existingsheets_strlower, true ) ) {
									$wpssw_sheetid = isset( $wpssw_existingsheets[ $wpssw_old_products_sheets[ $product_id ] ] ) ? $wpssw_existingsheets[ $wpssw_old_products_sheets[ $product_id ] ] : '';
									if ( $wpssw_sheetid && $product_name ) {
										$param                                    = array();
										$param['spreadsheetid']                   = $wpssw_spreadsheetid;
										$param['sheetid']                         = $wpssw_sheetid;
										$param['newsheetname']                    = trim( $product_name );
										$wpssw_response                           = self::$instance_api->updatesheetnameobject( $param );
										$wpssw_old_products_sheets[ $product_id ] = $product_name;
									}
								} else {
									$wpssw_remove_sheet[] = $wpssw_old_products_sheets[ $product_id ];
								}
							}
						}
					}
				}
			}

			$response             = self::$instance_api->get_sheet_listing( $wpssw_spreadsheetid );
			$wpssw_existingsheets = self::$instance_api->get_sheet_list( $response );

			$wpssw_status_array             = wc_get_order_statuses();
			$wpssw_status_array['wc-all']   = 'All';
			$wpssw_status_array['wc-trash'] = 'Trash';

			$wpssw_sheetnames  = array();
			$wpssw_order_array = array();

			foreach ( $wpssw_status_array as $wpssw_key => $wpssw_val ) {
				$wpssw_status  = substr( $wpssw_key, strpos( $wpssw_key, '-' ) + 1 );
				$wpssw_status  = str_replace( '-', ' ', $wpssw_status );
				$wpssw_status  = ucwords( $wpssw_status ) . ' Orders';
				$old_key_exist = array_key_exists( $wpssw_status, $wpssw_old_sheets );
				$new_key_exist = array_key_exists( $wpssw_status, $wpssw_sheets );
				if ( true === $new_key_exist ) {
					$wpssw_order_array[] = 1;
					$wpssw_sheetnames[]  = $wpssw_sheets[ $wpssw_status ];
				}
				if ( false === $new_key_exist && true === $old_key_exist ) {
					$wpssw_remove_sheet[] = $wpssw_old_sheets[ $wpssw_status ];
				}
			}

			$response             = self::$instance_api->get_sheet_listing( $wpssw_spreadsheetid );
			$wpssw_existingsheets = self::$instance_api->get_sheet_list( $response );
			$wpssw_existingsheets = array_flip( $wpssw_existingsheets );
			$wpssw_sheetnames     = array_merge( $wpssw_sheetnames, $wpssw_eventsheet_list );

			$wpssw_sheetnames_count = count( $wpssw_eventsheet_list );
			for ( $i = 0; $i < $wpssw_sheetnames_count; $i++ ) {
				if ( in_array( $wpssw_eventsheet_list[ $i ], $wpssw_existingsheets, true ) ) {
					$wpssw_order_array[] = 0;
				} else {
					$wpssw_order_array[] = 1;
				}
			}
			if ( isset( $wpssw_data['product_names_as_sheet'] ) ) {
				$remove_product_sheet = array_filter( array_unique( array_values( array_diff_key( $wpssw_old_products_sheets, $wpssw_productsheets_list ) ) ) );
				$wpssw_remove_sheet   = ! empty( $remove_product_sheet ) ? array_merge( $wpssw_remove_sheet, $remove_product_sheet ) : $wpssw_remove_sheet;
				$product_sheets       = array_filter( array_unique( array_values( $wpssw_productsheets_list ) ) );

				$wpssw_existingsheets_strlower = array_map( 'strtolower', $wpssw_existingsheets );

				$product_sheets_strlower_val = array_map( 'strtolower', $product_sheets );

				$wpssw_sheetnames = array_merge( $wpssw_sheetnames, $product_sheets );
				foreach ( $product_sheets as $product_name ) {
					if ( in_array( $product_name, $wpssw_existingsheets, true ) ) {
						$wpssw_order_array[] = 0;
					} else {
						$wpssw_order_array[] = 1;
					}
				}
				parent::wpssw_update_option( 'wpssw_productname_sheets', $wpssw_productsheets_list );
			} elseif ( ! isset( $wpssw_data['product_names_as_sheet'] ) && 'yes' === parent::wpssw_option( 'product_names_as_sheet' ) ) {
				$wpssw_productsheets_list = parent::wpssw_option( 'wpssw_productname_sheets' );
				$wpssw_productsheets_list = array_filter( array_unique( array_values( $wpssw_productsheets_list ) ) );
				if ( ! empty( $wpssw_productsheets_list ) ) {
					$wpssw_remove_sheet = array_merge( $wpssw_remove_sheet, $wpssw_productsheets_list );
				}
				parent::wpssw_update_option( 'wpssw_productname_sheets', array() );
				parent::wpssw_update_option( 'product_names_as_sheet', '' );
			}

			if ( 'new' !== (string) $wpssw_data['woocommerce_spreadsheet'] ) {
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
					$wpssw_requestbody      = self::$instance_api->valuerangeobject( $wpssw_value_custom );
					$param                  = array();
					$param                  = self::$instance_api->setparamater( $wpssw_spreadsheetid, $wpssw_range, $wpssw_requestbody, $wpssw_params );
					$wpssw_response         = self::$instance_api->appendentry( $param );
					$wpssw_newsheet         = 1;
				}
			}
			if ( 'new' === (string) $wpssw_data['spreadsheetselection'] ) {
				$param                  = array();
				$param['spreadsheetid'] = $wpssw_spreadsheetid;
				$wpssw_response         = self::$instance_api->deletesheetobject( $param );
			}
			if ( 'new' !== (string) $wpssw_data['woocommerce_spreadsheet'] ) {
				$requestarray           = array();
				$deleterequestarray     = array();
				$wpssw_old_header_order = parent::wpssw_option( 'wpssw_sheet_headers_list' );
				if ( ! is_array( $wpssw_old_header_order ) ) {
					$wpssw_old_header_order = array();
				}
				if ( parent::wpssw_is_event_calender_ticket_active() ) {
					$wpssw_old_header_order = array_merge( $wpssw_old_header_order, $wpssw_woo_event_headers );
				}

				array_unshift( $wpssw_old_header_order, 'Order Id' );

				if ( $wpssw_old_header_order !== $wpssw_headers ) {
					// Delete deactivate column from sheet.
					$wpssw_column = array_diff( $wpssw_old_header_order, $wpssw_headers );
					if ( ! empty( $wpssw_column ) ) {
						$wpssw_column = array_reverse( $wpssw_column, true );
						foreach ( $wpssw_column as $columnindex => $columnval ) {
							unset( $wpssw_old_header_order[ $columnindex ] );
							$wpssw_old_header_order = array_values( $wpssw_old_header_order );
							$wpssw_sheetnames_count = count( $wpssw_sheetnames );
							for ( $i = 0; $i < $wpssw_sheetnames_count; $i++ ) {
								if ( in_array( $wpssw_sheetnames[ $i ], $wpssw_existingsheets, true ) ) {
									$wpssw_sheetid = array_search( $wpssw_sheetnames[ $i ], $wpssw_existingsheets, true );
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
				if ( $wpssw_old_header_order !== $wpssw_headers ) {
					foreach ( $wpssw_headers as $key => $hname ) {
						if ( 'Order Id' === (string) $hname ) {
							continue;
						}
						$wpssw_startindex = array_search( $hname, $wpssw_old_header_order, true );

						if ( false !== $wpssw_startindex && ( isset( $wpssw_old_header_order[ $key ] ) && $wpssw_old_header_order[ $key ] !== $hname ) ) {
							unset( $wpssw_old_header_order[ $wpssw_startindex ] );
							$wpssw_old_header_order = array_merge( array_slice( $wpssw_old_header_order, 0, $key ), array( 0 => $hname ), array_slice( $wpssw_old_header_order, $key, count( $wpssw_old_header_order ) - $key ) );

							$wpssw_endindex         = $wpssw_startindex + 1;
							$wpssw_destindex        = $key;
							$wpssw_sheetnames_count = count( $wpssw_sheetnames );
							for ( $i = 0; $i < $wpssw_sheetnames_count; $i++ ) {
								if ( in_array( $wpssw_sheetnames[ $i ], $wpssw_existingsheets, true ) ) {
									$wpssw_sheetid = array_search( $wpssw_sheetnames[ $i ], $wpssw_existingsheets, true );
									if ( $wpssw_sheetid ) {
										$param              = array();
										$param              = self::$instance_api->prepare_param( $wpssw_sheetid, $wpssw_startindex, $wpssw_endindex );
										$param['destindex'] = $wpssw_destindex;
										$requestarray[]     = self::$instance_api->moveDimensionrequests( $param );
									}
								}
							}
						} elseif ( false === (bool) $wpssw_startindex ) {
							$wpssw_old_header_order = array_merge( array_slice( $wpssw_old_header_order, 0, $key ), array( 0 => $hname ), array_slice( $wpssw_old_header_order, $key, count( $wpssw_old_header_order ) - $key ) );

							$wpssw_sheetnames_count = count( $wpssw_sheetnames );
							$wpssw_inherit_style    = parent::wpssw_option( 'wpssw_order_inherit_style' );
							for ( $i = 0; $i < $wpssw_sheetnames_count; $i++ ) {
								if ( in_array( $wpssw_sheetnames[ $i ], $wpssw_existingsheets, true ) ) {
									$wpssw_sheetid = array_search( $wpssw_sheetnames[ $i ], $wpssw_existingsheets, true );
									if ( $wpssw_sheetid ) {
										$param            = array();
										$wpssw_startindex = $key;
										$wpssw_endindex   = $key + 1;
										$param            = self::$instance_api->prepare_param( $wpssw_sheetid, $wpssw_startindex, $wpssw_endindex );
										if ( 'no' === (string) $wpssw_inherit_style ) {
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
			}

			$wpssw_sheetnames_count = count( $wpssw_sheetnames );
			for ( $i = 0; $i < $wpssw_sheetnames_count; $i++ ) {
				if ( in_array( $wpssw_sheetnames[ $i ], $wpssw_existingsheets, true ) && 0 === (int) $wpssw_order_array[ $i ] ) {
					$wpssw_range       = trim( $wpssw_sheetnames[ $i ] ) . '!A1';
					$wpssw_requestbody = self::$instance_api->valuerangeobject( $wpssw_value_custom );
					$wpssw_params      = array( 'valueInputOption' => $wpssw_inputoption );
					$param             = self::$instance_api->setparamater( $wpssw_spreadsheetid, $wpssw_range, $wpssw_requestbody, $wpssw_params );
					$wpssw_response    = self::$instance_api->updateentry( $param );
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
			if ( ! empty( $wpssw_remove_sheet ) && 'new' !== (string) $wpssw_data['woocommerce_spreadsheet'] ) {
				parent::wpssw_delete_sheet( $wpssw_spreadsheetid, $wpssw_remove_sheet, $wpssw_existingsheets );
			}
			if ( isset( $wpssw_data['freeze_header'] ) ) {
				$wpssw_freeze = 1;
			} else {
				$wpssw_freeze = 0;
			}
			if ( isset( $wpssw_data['color_code'] ) ) {
				$oddcolor          = $wpssw_data['oddcolor'];
				$evencolor         = $wpssw_data['evencolor'];
				$rowcolor_disabled = false;
			} else {
				$oddcolor          = '#ffffff';
				$evencolor         = '#ffffff';
				$rowcolor_disabled = true;
			}
			$color_code_saved = parent::wpssw_option( 'wpssw_color_code' );
			$oddcolor_saved   = parent::wpssw_option( 'wpssw_oddcolor' );
			$evencolor_saved  = parent::wpssw_option( 'wpssw_evencolor' );
			$wpssw_color      = 1;
			if ( isset( $wpssw_data['color_code'] ) && (string) $wpssw_data['color_code'] === $color_code_saved && isset( $wpssw_data['oddcolor'] ) && (string) $wpssw_data['oddcolor'] === $oddcolor_saved && isset( $wpssw_data['evencolor'] ) && (string) $wpssw_data['evencolor'] === $evencolor_saved ) {
				$wpssw_color = 0;
			}
			$wpssw_freeze_header = 1;
			$freeze_header       = parent::wpssw_option( 'freeze_header' );
			if ( ( $wpssw_freeze && 'yes' === (string) $freeze_header ) || ( ! $wpssw_freeze && 'no' === (string) $freeze_header ) ) {
				$wpssw_freeze_header = 0;
			}
			if ( 'new' === (string) $wpssw_data['spreadsheetselection'] || $wpssw_newsheet ) {
				$wpssw_freeze_header = 1;
				$wpssw_color         = 1;
			}
			if ( $wpssw_freeze_header || $wpssw_color ) {
				parent::wpssw_freeze_header( $wpssw_spreadsheetid, $wpssw_freeze, $oddcolor, $evencolor, $wpssw_color, $wpssw_freeze_header, $rowcolor_disabled );
			}
			return $wpssw_spreadsheetid;
		}
		/**
		 * Prepare sheetnames array
		 *
		 * @return array $wpssw_sheets_selected
		 */
		public static function wpssw_prepare_sheets() {
			$wpssw_sheets_selected = array();
			$wpssw_sheets_selected = array_filter( (array) parent::wpssw_option( 'wpssw_sheets' ) );
			$wpssw_status_array    = wc_get_order_statuses();

			if ( ! $wpssw_sheets_selected ) {
				if ( 'yes' === (string) parent::wpssw_option( 'pending_orders' ) ) {
					$wpssw_sheets_selected['Pending Orders'] = 'Pending Orders';
				}
				if ( 'yes' === (string) parent::wpssw_option( 'processing_orders' ) ) {
					$wpssw_sheets_selected['Processing Orders'] = 'Processing Orders';
				}
				if ( 'yes' === (string) parent::wpssw_option( 'on_hold_orders' ) ) {
					$wpssw_sheets_selected['On Hold Orders'] = 'On Hold Orders';
				}
				if ( 'yes' === (string) parent::wpssw_option( 'completed_orders' ) ) {
					$wpssw_sheets_selected['Completed Orders'] = 'Completed Orders';
				}
				if ( 'yes' === (string) parent::wpssw_option( 'cancelled_orders' ) ) {
					$wpssw_sheets_selected['Cancelled Orders'] = 'Cancelled Orders';
				}
				if ( 'yes' === (string) parent::wpssw_option( 'refunded_orders' ) ) {
					$wpssw_sheets_selected['Refunded Orders'] = 'Refunded Orders';
				}
				if ( 'yes' === (string) parent::wpssw_option( 'failed_orders' ) ) {
					$wpssw_sheets_selected['Failed Orders'] = 'Failed Orders';
				}
				if ( 'yes' === (string) parent::wpssw_option( 'trash' ) ) {
					$wpssw_sheets_selected['Trash Orders'] = 'Trash Orders';
				}
				if ( 'yes' === (string) parent::wpssw_option( 'all_orders' ) ) {
					$wpssw_sheets_selected['All Orders'] = 'All Orders';
				}
				foreach ( $wpssw_status_array as $wpssw_key => $wpssw_val ) {
					$wpssw_status = substr( $wpssw_key, strpos( $wpssw_key, '-' ) + 1 );
					if ( ! in_array( $wpssw_status, self::$wpssw_default_status, true ) ) {
						if ( 'yes' === (string) parent::wpssw_option( $wpssw_status ) ) {
							$wpssw_status                              = str_replace( '-', ' ', $wpssw_status );
							$wpssw_sheetname                           = ucwords( $wpssw_status ) . ' Orders';
							$wpssw_sheets_selected[ $wpssw_sheetname ] = $wpssw_sheetname;
						}
					}
				}
				parent::wpssw_update_option( 'wpssw_sheets', $wpssw_sheets_selected );
			}
			return $wpssw_sheets_selected;
		}
		/**
		 * Prepare array of items that belongs to given event category($wpssw_sheet_name)
		 *
		 * @param object $wpssw_order .
		 * @param string $wpssw_sheet_name Sheetname.
		 * @return array $items_to_keep array of items to keep from all items of order.
		 */
		public static function wpssw_get_specific_items( $wpssw_order, $wpssw_sheet_name ) {
			$items_to_keep = array();

			$wpssw_event_spreadsheet_setting = parent::wpssw_option( 'wpssw_event_spreadsheet_setting' );
			$event_sheet                     = 0;
			if ( 'yes' === (string) $wpssw_event_spreadsheet_setting ) {
				$wpssw_eventsheets_list = array();
				if ( is_array( parent::wpssw_option( 'wpssw_eventsheets_list' ) ) ) {
					$wpssw_eventsheets_list = parent::wpssw_option( 'wpssw_eventsheets_list' );
					if ( ! empty( $wpssw_sheet_name ) && in_array( $wpssw_sheet_name, $wpssw_eventsheets_list, true ) ) {
						$event_sheet = 1;
						$i           = 0;
						$wpssw_items = $wpssw_order->get_items();
						foreach ( $wpssw_items as $wpssw_itemid => $wpssw_item ) {
							$wpssw_eventsheetnames = array();
							$wpssw_ticket_meta     = get_post_meta( $wpssw_item['product_id'] );
							if ( isset( $wpssw_ticket_meta['_tribe_wooticket_for_event'] ) ) {
								$wpssw_eventid = $wpssw_ticket_meta['_tribe_wooticket_for_event'][0];
								$term_obj_list = get_the_terms( $wpssw_eventid, 'tribe_events_cat' );
								if ( ! $term_obj_list ) {
									$term_obj_list = array();
								}
								foreach ( $term_obj_list as $term ) {
									$wpssw_eventsheetnames[] = $term->name;
								}
								if ( in_array( $wpssw_sheet_name, $wpssw_eventsheetnames, true ) ) {
									$items_to_keep[ $wpssw_itemid ] = $i;
								}
							}
							$i++;
						}
					}
				}
			}
			$wpssw_product_names_as_sheet = parent::wpssw_option( 'product_names_as_sheet' );
			if ( 'yes' === (string) $wpssw_product_names_as_sheet && 0 === (int) $event_sheet ) {
				$wpssw_productsheets_list = array();

				if ( is_array( parent::wpssw_option( 'wpssw_productname_sheets' ) ) ) {
					$wpssw_productsheets_list = parent::wpssw_option( 'wpssw_productname_sheets' );
					if ( ! empty( $wpssw_sheet_name ) && in_array( $wpssw_sheet_name, $wpssw_productsheets_list, true ) ) {
						$i           = 0;
						$wpssw_items = $wpssw_order->get_items();
						$product_ids = array_keys( $wpssw_productsheets_list, $wpssw_sheet_name, true );
						foreach ( $wpssw_items as $wpssw_itemid => $wpssw_item ) {
							if ( ! empty( $product_ids ) && in_array( (int) $wpssw_item['product_id'], parent::wpssw_convert_int( $product_ids ), true ) ) {
								$items_to_keep[ $wpssw_itemid ] = $i;
							}
							$i++;
						}
					}
				}
			}

			return $items_to_keep;
		}
		/**
		 * Update shet on multiple orders update.
		 *
		 * @param array $wpssw_multiple_order_data updated orders data.
		 */
		public static function wpssw_multiple_order_update( $wpssw_multiple_order_data ) {

			$wpssw_order_spreadsheet_setting = self::wpssw_check_order_spreadsheet_setting();
			if ( 'yes' !== (string) $wpssw_order_spreadsheet_setting || empty( $wpssw_multiple_order_data ) ) {
				return;
			}

			$wpssw_status_array  = wc_get_order_statuses();
			$wpssw_spreadsheetid = parent::wpssw_option( 'wpssw_woocommerce_spreadsheet' );

			$wpssw_spreadsheets_list = self::$instance_api->get_spreadsheet_listing();
			if ( ! array_key_exists( $wpssw_spreadsheetid, $wpssw_spreadsheets_list ) ) {
				return;
			}
			$response                  = self::$instance_api->get_sheet_listing( $wpssw_spreadsheetid );
			$wpssw_existingsheetsnames = self::$instance_api->get_sheet_list( $response );
			$wpssw_existingsheetsnames = array_flip( $wpssw_existingsheetsnames );

			$wpssw_sheets = array_filter( (array) parent::wpssw_option( 'wpssw_sheets' ) );
			if ( ! $wpssw_sheets ) {
				$wpssw_sheets = self::wpssw_prepare_sheets();
			}

			$wpssw_status_array             = wc_get_order_statuses();
			$wpssw_status_array['wc-trash'] = 'Trash';

			$wpssw_status_array['wc-all'] = 'All';
			$wpssw_getactivesheets        = array();
			$wpssw_activesheets           = array();
			$trashed_orders_sheet         = array();
			foreach ( $wpssw_status_array as $wpssw_key => $wpssw_val ) {
				$wpssw_status      = substr( $wpssw_key, strpos( $wpssw_key, '-' ) + 1 );
				$wpssw_orderstatus = str_replace( '-', ' ', $wpssw_status );
				$wpssw_orderstatus = ucwords( $wpssw_orderstatus ) . ' Orders';
				if ( array_key_exists( $wpssw_orderstatus, $wpssw_sheets ) && in_array( $wpssw_sheets[ $wpssw_orderstatus ], $wpssw_existingsheetsnames, true ) ) {
					if ( 'wc-trash' === (string) $wpssw_key ) {
						$wpssw_activesheets['trash']                = $wpssw_orderstatus;
						$trashed_orders_sheet[ $wpssw_orderstatus ] = $wpssw_sheets[ $wpssw_orderstatus ];
					} elseif ( 'wc-all' === (string) $wpssw_key ) {
						$wpssw_activesheets['all_order']            = $wpssw_orderstatus;
						$trashed_orders_sheet[ $wpssw_orderstatus ] = $wpssw_sheets[ $wpssw_orderstatus ];
					} else {
						$wpssw_activesheets[ $wpssw_key ] = $wpssw_orderstatus;
					}
					$wpssw_getactivesheets[] = "'" . $wpssw_sheets[ $wpssw_orderstatus ] . "'!A:A";
				}
			}

			if ( ! $trashed_orders_sheet ) {
				$trashed_orders_sheet = array();
			}

			$wpssw_additionalsheetnames = array();
			$wpssw_eventsheetnames      = parent::wpssw_option( 'wpssw_eventsheets_list' );
			if ( ! is_array( $wpssw_eventsheetnames ) ) {
				$wpssw_eventsheetnames = array();
			}
			$wpssw_productsheets_list = parent::wpssw_option( 'wpssw_productname_sheets' );
			if ( ! $wpssw_productsheets_list ) {
				$wpssw_productsheets_list = array();
			}

			if ( ( parent::wpssw_is_event_calender_ticket_active() && 'yes' === (string) parent::wpssw_option( 'wpssw_event_spreadsheet_setting' ) ) || 'yes' === (string) parent::wpssw_option( 'product_names_as_sheet' ) ) {

				foreach ( $wpssw_multiple_order_data as $order_id => $order_data ) {
					$wpssw_order = wc_get_order( $order_id );
					$wpssw_items = $wpssw_order->get_items();

					if ( ( parent::wpssw_is_event_calender_ticket_active() && 'yes' === (string) parent::wpssw_option( 'wpssw_event_spreadsheet_setting' ) ) ) {

						foreach ( $wpssw_items as $wpssw_item ) {
							$wpssw_ticket_meta = get_post_meta( $wpssw_item['product_id'] );
							if ( isset( $wpssw_ticket_meta['_tribe_wooticket_for_event'] ) ) {
								$wpssw_eventid = $wpssw_ticket_meta['_tribe_wooticket_for_event'][0];
								$term_obj_list = get_the_terms( $wpssw_eventid, 'tribe_events_cat' );
								if ( ! $term_obj_list ) {
									$term_obj_list = array();
								}
								foreach ( $term_obj_list as $term ) {
									if ( in_array( $term->name, $wpssw_existingsheetsnames, true ) && in_array( $term->name, $wpssw_eventsheetnames, true ) ) {
										$wpssw_additionalsheetnames[] = $term->name;
									}
								}
							}
						}
					}
					if ( 'yes' === (string) parent::wpssw_option( 'product_names_as_sheet' ) ) {

						foreach ( $wpssw_items as $wpssw_item ) {
							if ( array_key_exists( $wpssw_item['product_id'], $wpssw_productsheets_list ) && in_array( $wpssw_productsheets_list[ $wpssw_item['product_id'] ], $wpssw_existingsheetsnames, true ) ) {
								$wpssw_additionalsheetnames[] = $wpssw_productsheets_list[ $wpssw_item['product_id'] ];
							}
						}
					}
					$wpssw_order                = null;
					$wpssw_items                = null;
					$wpssw_additionalsheetnames = array_values( array_unique( $wpssw_additionalsheetnames ) );
					foreach ( $wpssw_additionalsheetnames as $additionalsheet ) {
						$wpssw_activesheets[ $additionalsheet ] = $additionalsheet;
						$wpssw_getactivesheets[]                = "'" . $additionalsheet . "'!A:A";
						$wpssw_sheets[ $additionalsheet ]       = $additionalsheet;
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
			$wpssw_existingorders = array();
			foreach ( $wpssw_response->getValueRanges() as $wpssw_response_data ) {
				if ( strpos( $wpssw_response_data->range, "'!A" ) ) {
					$wpssw_rangetitle = explode( "'!A", $wpssw_response_data->range );
				} else {
					$wpssw_rangetitle = explode( '!A', $wpssw_response_data->range );
				}
				$wpssw_sheettitle = str_replace( "'", '', $wpssw_rangetitle[0] );
				$wpssw_data       = array_map(
					function( $wpssw_element ) {
						if ( isset( $wpssw_element['0'] ) ) {
							return (int) $wpssw_element['0'];
						} else {
							return '';
						}
					},
					$wpssw_response_data->values
				);

				$wpssw_existingorders[ $wpssw_sheettitle ] = $wpssw_data;
				$wpssw_data                                = null;
			}
			$wpssw_response = null;

			$wpssw_header_type  = self::wpssw_is_productwise();
			$order_ascdesc      = parent::wpssw_option( 'wpssw_order_ascdesc' );
			$repeat_checkbox    = parent::wpssw_option( 'wpssw_repeat_checkbox' );
			$delete_row_indexes = array();
			$deleterequestarray = array();
			foreach ( $wpssw_multiple_order_data as $order_id => $order_data ) {
				if ( 'new' === $order_data['old_staus'] ) {
					continue;
				}
				foreach ( $wpssw_existingorders as $sheet_name => $existingorders_sheet ) {
					$sheet_id = array_search( $sheet_name, $wpssw_existingsheetsnames, true );
					if ( in_array( $order_id, $existingorders_sheet, true ) ) {
						$wpssw_num   = array_search( (int) $order_id, parent::wpssw_convert_int( $existingorders_sheet ), true );
						$start_index = $wpssw_num;
						if ( $wpssw_header_type ) {
							$item_count = count( array_keys( $existingorders_sheet, (int) $order_id, true ) );
							$end_index  = $wpssw_num + $item_count;
						} else {
							$end_index = $wpssw_num + 1;
						}
						$delete_row_indexes[ $sheet_name ][] = array(
							'start_index' => $start_index,
							'end_index'   => $end_index,
						);
					}
				}
			}

			foreach ( $delete_row_indexes as $sheet_name => $delete_row ) {
				$requests                 = array();
				$delete_row_indexes_count = count( $delete_row );
				array_multisort( array_column( $delete_row, 'start_index' ), SORT_ASC, $delete_row );
				for ( $i = 0;$i < $delete_row_indexes_count;$i++ ) {
					if ( 0 === (int) $i ) {
						$startindex = $delete_row[0]['start_index'];
					} elseif ( $delete_row[ $i ]['start_index'] - $delete_row[ $i - 1 ]['end_index'] > 0 ) {
						$requests[] = array(
							'startIndex' => $startindex,
							'endIndex'   => $delete_row[ $i - 1 ]['end_index'],
						);
						$startindex = $delete_row[ $i ]['start_index'];
					}
					if ( $delete_row_indexes_count - 1 === (int) $i ) {
						$requests[] = array(
							'startIndex' => $startindex,
							'endIndex'   => $delete_row[ $i ]['end_index'],
						);
					}
				}
				array_multisort( array_column( $requests, 'startIndex' ), SORT_DESC, $requests );
				$sheet_id = array_search( $sheet_name, $wpssw_existingsheetsnames, true );
				foreach ( $requests as $request ) {
					$param                = array();
					$param                = self::$instance_api->prepare_param( $sheet_id, $request['startIndex'], $request['endIndex'] );
					$deleterequestarray[] = self::$instance_api->deleteDimensionrequests( $param, 'ROWS' );
				}
				$requests = null;
			}

			if ( ! empty( $deleterequestarray ) ) {

				$delete_row_indexes = null;

				$wpssw_response     = self::$instance_api->updatebachrequests(
					array(
						'spreadsheetid' => $wpssw_spreadsheetid,
						'requestarray'  => $deleterequestarray,
					)
				);
				$deleterequestarray = null;
				$wpssw_response     = null;
				/*Get First Column Value from all sheets*/
				$param                  = array();
				$wpssw_response         = array();
				$param['spreadsheetid'] = $wpssw_spreadsheetid;
				$param['ranges']        = array( 'ranges' => $wpssw_getactivesheets );
				$wpssw_response         = self::$instance_api->getbatchvalues( $param );

				$wpssw_existingorders = array();
				foreach ( $wpssw_response->getValueRanges() as $wpssw_response_data ) {
					if ( strpos( $wpssw_response_data->range, "'!A" ) ) {
						$wpssw_rangetitle = explode( "'!A", $wpssw_response_data->range );
					} else {
						$wpssw_rangetitle = explode( '!A', $wpssw_response_data->range );
					}
					$wpssw_sheettitle = str_replace( "'", '', $wpssw_rangetitle[0] );
					$wpssw_data       = array_map(
						function( $wpssw_element ) {
							if ( isset( $wpssw_element['0'] ) && is_numeric( $wpssw_element['0'] ) ) {
								return (int) $wpssw_element['0'];
							} else {
								return '';
							}
						},
						$wpssw_response_data->values
					);

					$wpssw_existingorders[ $wpssw_sheettitle ] = $wpssw_data;
					$wpssw_data                                = null;
				}
				$wpssw_response = null;
			}

			$wpssw_order_status_key = false;
			if ( in_array( 'trash', array_column( $wpssw_multiple_order_data, 'new_status' ), true ) ) {
				$wpssw_woo_selections = stripslashes_deep( parent::wpssw_option( 'wpssw_sheet_headers_list' ) );
				array_unshift( $wpssw_woo_selections, 'Order Id' );
				$wpssw_order_status_key = array_search( 'Order Status', $wpssw_woo_selections, true );

			}

			foreach ( $wpssw_multiple_order_data as $order_id => $order_data ) {
				foreach ( $wpssw_existingorders as $sheet_name => $existingorders_sheet ) {
					$sheet_id          = array_search( $sheet_name, $wpssw_existingsheetsnames, true );
					$wpssw_append      = 0;
					$wpssw_desc_append = 0;
					$wpssw_order       = wc_get_order( $order_id );

					if ( 'trash' === (string) $order_data['new_status'] && ( empty( $trashed_orders_sheet ) || ! in_array( $sheet_name, $trashed_orders_sheet, true ) ) ) {
						continue;
					} else {
						if ( ! in_array( (string) array_search( $sheet_name, $wpssw_sheets, true ), array( ucwords( str_replace( '-', ' ', $order_data['new_status'] ) ) . ' Orders', 'All Orders' ), true ) && ! in_array( $sheet_name, $wpssw_eventsheetnames, true ) && ! in_array( $sheet_name, $wpssw_productsheets_list, true ) ) {
							continue;
						}
						$wpssw_prdcount = 1;
						if ( $wpssw_header_type ) {
							if ( in_array( $sheet_name, $wpssw_eventsheetnames, true ) || in_array( $sheet_name, $wpssw_productsheets_list, true ) ) {
								$items_to_keep = self::wpssw_get_specific_items( $wpssw_order, $sheet_name );
								if ( empty( $items_to_keep ) ) {
									continue;
								}
								$wpssw_prdcount = count( $items_to_keep );
							} else {
								$wpssw_prdcount = count( $wpssw_order->get_items() );
							}
						}
					}
					$highest_order_id = max( $existingorders_sheet );

					if ( ( $highest_order_id ) && $order_id < $highest_order_id ) {
						foreach ( $existingorders_sheet as $wpssw_key => $wpssw_value ) {
							if ( ! empty( $wpssw_value ) ) {

								$wpssw_startindex       = $wpssw_key + 1;
								$insert_row_start_index = $wpssw_key;
								if ( $wpssw_header_type ) {
									$wpssw_endindex       = $wpssw_startindex + $wpssw_prdcount;
									$insert_row_end_index = $wpssw_key + $wpssw_prdcount;
								} else {
									$wpssw_endindex       = $wpssw_startindex + 1;
									$insert_row_end_index = $wpssw_key + 1;
								}

								if ( ( (int) $order_id < (int) $wpssw_value ) && 'descorder' !== (string) $order_ascdesc ) {
									$wpssw_append                        = 1;
									$insert_row_indexes[ $sheet_name ][] = array(
										'start_index' => $insert_row_start_index,
										'end_index'   => $insert_row_end_index,
									);

									$update_row_indexes[ $sheet_name ][ $order_id ] = array(
										'start_index'  => $wpssw_startindex,
										'end_index'    => $wpssw_endindex,
										'order_id'     => $order_id,
										'values_count' => $wpssw_prdcount,

									);
									break;
								}
								if ( 'descorder' === (string) $order_ascdesc ) {
									if ( ( (int) $order_id > (int) $wpssw_value ) && (int) $wpssw_value > 0 ) {
										$wpssw_append                                   = 1;
										$wpssw_desc_append                              = 1;
										$insert_row_indexes[ $sheet_name ][]            = array(
											'start_index' => $insert_row_start_index,
											'end_index'   => $insert_row_end_index,
										);
										$update_row_indexes[ $sheet_name ][ $order_id ] = array(
											'start_index'  => $wpssw_startindex,
											'end_index'    => $wpssw_endindex,
											'order_id'     => $order_id,
											'values_count' => $wpssw_prdcount,
										);
										break;
									}
								}
							}
						}
					}
					if ( 0 === (int) $wpssw_desc_append && 'descorder' === (string) $order_ascdesc ) {
						if ( ! $highest_order_id || $order_id > $highest_order_id ) {
							$wpssw_append = 1;
							if ( $wpssw_header_type ) {
								$end_index            = 2 + $wpssw_prdcount;
								$insert_row_end_index = 1 + $wpssw_prdcount;
							} else {
								$end_index            = 3;
								$insert_row_end_index = 2;
							}
							$insert_row_indexes[ $sheet_name ][]            = array(
								'start_index' => 1,
								'end_index'   => $insert_row_end_index,
							);
							$update_row_indexes[ $sheet_name ][ $order_id ] = array(
								'start_index'  => 2,
								'end_index'    => $end_index,
								'order_id'     => $order_id,
								'values_count' => $wpssw_prdcount,
							);
						}
					}
					if ( 0 === (int) $wpssw_append ) {
						if ( $wpssw_header_type ) {
							$end_index            = count( $existingorders_sheet ) + 1 + $wpssw_prdcount;
							$insert_row_end_index = count( $existingorders_sheet ) + $wpssw_prdcount;
						} else {
							$end_index            = count( $existingorders_sheet ) + 2;
							$insert_row_end_index = count( $existingorders_sheet ) + 1;
						}
						$insert_row_indexes[ $sheet_name ][]            = array(
							'start_index' => count( $existingorders_sheet ),
							'end_index'   => $insert_row_end_index,
						);
						$update_row_indexes[ $sheet_name ][ $order_id ] = array(
							'start_index'  => count( $existingorders_sheet ) + 1,
							'end_index'    => $end_index,
							'order_id'     => $order_id,
							'values_count' => $wpssw_prdcount,
						);
					}
				}
			}

			$insertrequestarray  = array();
			$wpssw_inherit_style = parent::wpssw_option( 'wpssw_order_inherit_style' );
			foreach ( $insert_row_indexes as  $sheet_name => $insert_row ) {
				$requests                 = array();
				$insert_row_indexes_count = count( $insert_row );
				for ( $i = 0;$i < $insert_row_indexes_count;$i++ ) {
					$requests[] = array(
						'startIndex' => $insert_row[ $i ]['start_index'],
						'endIndex'   => $insert_row[ $i ]['end_index'],
					);
				}
				array_multisort( array_column( $requests, 'startIndex' ), SORT_DESC, $requests );
				$sheet_id = array_search( $sheet_name, $wpssw_existingsheetsnames, true );
				foreach ( $requests as $request ) {
					$param = array();
					$param = self::$instance_api->prepare_param( $sheet_id, $request['startIndex'], $request['endIndex'] );
					if ( 'no' === (string) $wpssw_inherit_style ) {
						$insertrequestarray[] = self::$instance_api->insertdimensionrequests( $param, 'ROWS', false );
					} else {
						$insertrequestarray[] = self::$instance_api->insertdimensionrequests( $param, 'ROWS', true );
					}
				}
				$requests = null;
			}

			if ( ! empty( $insertrequestarray ) ) {
				$insert_row_indexes = null;
				$wpssw_response     = self::$instance_api->updatebachrequests(
					array(
						'spreadsheetid' => $wpssw_spreadsheetid,
						'requestarray'  => $insertrequestarray,
					)
				);
				$wpssw_response     = null;
			}

			$update_row_data = array();
			$dimension       = 'ROWS';

			$update_row_indexes_new = array();

			foreach ( $update_row_indexes as  $sheet_name => $update_row ) {
				array_multisort( array_column( $update_row, 'start_index' ), SORT_ASC, $update_row );

				$requests                 = array();
				$update_row_indexes_count = count( $update_row );
				for ( $i = 0;$i < $update_row_indexes_count;$i++ ) {
					$new_start               = $update_row[ $i ]['start_index'];
					$same_startindex_rowkeys = array_keys( parent::wpssw_convert_int( array_column( $update_row, 'start_index' ) ), (int) $update_row[ $i ]['start_index'], true );

					if ( count( $same_startindex_rowkeys ) > 1 && $same_startindex_rowkeys[0] < $i ) {
						continue;
					}

					for ( $j = 0;$j < $update_row_indexes_count;$j++ ) {
						if ( $j < $i ) {
							$new_start = $new_start + $update_row[ $j ]['values_count'];
						}
						if ( ( 0 === (int) $i || ( $i === $update_row_indexes_count - 1 && $j === $i ) ) && count( $same_startindex_rowkeys ) < 2 ) {
							$param = array();
							if ( 0 === (int) $i ) {
								$param['range'] = $sheet_name . '!A' . $update_row[ $i ]['start_index'];
							} else {
								$param['range'] = $sheet_name . '!A' . $new_start;
							}

							$orderid = $update_row[ $i ]['order_id'];
							if ( in_array( $sheet_name, $wpssw_eventsheetnames, true ) || in_array( $sheet_name, $wpssw_productsheets_list, true ) ) {
								$wpssw_value_data = self::wpssw_make_value_array( 'insert', $orderid, $sheet_name );
							} else {
								$wpssw_value_data = self::wpssw_make_value_array( 'insert', $orderid );
							}
							if ( 'trash' === (string) $wpssw_multiple_order_data[ $orderid ]['new_status'] && false !== $wpssw_order_status_key ) {
								if ( $wpssw_header_type && 'yes' === (string) $repeat_checkbox ) {
									foreach ( $wpssw_value_data as &$prdarray ) {
										$prdarray[ $wpssw_order_status_key ] = 'Trash';
									}
								} else {
									$wpssw_value_data[0][ $wpssw_order_status_key ] = 'Trash';
								}
							}
							$update_row_data[] = new \Google_Service_Sheets_ValueRange(
								array(
									'range'          => $param['range'],
									'majorDimension' => $dimension,
									'values'         => $wpssw_value_data,
								)
							);
							$wpssw_value_data  = null;
							break;
						}
						if ( (int) $update_row[ $i ]['start_index'] === (int) $update_row[ $j ]['start_index'] && $update_row[ $i ]['order_id'] !== $update_row[ $j ]['order_id'] ) {

							break;
						}

						if ( $update_row[ $i ]['start_index'] < $update_row[ $j ]['start_index'] ) {
							$param          = array();
							$param['range'] = $sheet_name . '!A' . $new_start;

							$orderid = $update_row[ $i ]['order_id'];
							if ( in_array( $sheet_name, $wpssw_eventsheetnames, true ) || in_array( $sheet_name, $wpssw_productsheets_list, true ) ) {
								$wpssw_value_data = self::wpssw_make_value_array( 'insert', $orderid, $sheet_name );
							} else {
								$wpssw_value_data = self::wpssw_make_value_array( 'insert', $orderid );
							}
							if ( 'trash' === (string) $wpssw_multiple_order_data[ $orderid ]['new_status'] && false !== $wpssw_order_status_key ) {
								if ( $wpssw_header_type && 'yes' === (string) $repeat_checkbox ) {
									foreach ( $wpssw_value_data as &$prdarray ) {
										$prdarray[ $wpssw_order_status_key ] = 'Trash';
									}
								} else {
									$wpssw_value_data[0][ $wpssw_order_status_key ] = 'Trash';
								}
							}
							$update_row_data[] = new \Google_Service_Sheets_ValueRange(
								array(
									'range'          => $param['range'],
									'majorDimension' => $dimension,
									'values'         => $wpssw_value_data,
								)
							);
							$wpssw_value_data  = null;
							break;
						}
						if ( count( $same_startindex_rowkeys ) > 1 ) {
							$same_row_start = $update_row[ $i ]['start_index'];
							for ( $k = 0;$k < $i;$k++ ) {
								$same_row_start = $same_row_start + $update_row[ $k ]['values_count'];
							}
							$new_start = $same_row_start;

							$same_rows = array();
							foreach ( $same_startindex_rowkeys as $same_rowkey ) {
								$same_rows[ $update_row[ $same_rowkey ]['order_id'] ] = $update_row[ $same_rowkey ];
							}
							$same_startindex_rowkeys = null;
							if ( 'descorder' === (string) $order_ascdesc ) {
								krsort( $same_rows );
							} else {
								ksort( $same_rows );
							}
							$same_rows_value = array();
							foreach ( $same_rows as $same_row ) {
								$orderid = $same_row['order_id'];
								if ( in_array( $sheet_name, $wpssw_eventsheetnames, true ) || in_array( $sheet_name, $wpssw_productsheets_list, true ) ) {
									$wpssw_value_data = self::wpssw_make_value_array( 'insert', $orderid, $sheet_name );
								} else {
									$wpssw_value_data = self::wpssw_make_value_array( 'insert', $orderid );
								}
								if ( 'trash' === (string) $wpssw_multiple_order_data[ $orderid ]['new_status'] && false !== $wpssw_order_status_key ) {
									if ( $wpssw_header_type && 'yes' === (string) $repeat_checkbox ) {
										foreach ( $wpssw_value_data as &$prdarray ) {
											$prdarray[ $wpssw_order_status_key ] = 'Trash';
										}
									} else {
										$wpssw_value_data[0][ $wpssw_order_status_key ] = 'Trash';
									}
								}

								$same_rows_value = array_merge( $same_rows_value, $wpssw_value_data );

								$wpssw_value_data = null;
							}

							$update_row_data[] = new \Google_Service_Sheets_ValueRange(
								array(
									'range'          => $sheet_name . '!A' . $new_start,
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
				$update_row_indexes = null;
				$wpssw_inputoption  = parent::wpssw_option( 'wpssw_inputoption' );
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
				$update_row_data              = null;
				$wpssw_response               = self::$instance_api->multirangevalueupdate( $requestobject );
				$wpssw_response               = null;
			}
		}
		/**
		 * Get all the settings for this plugin for @see woocommerce_admin_fields() function.
		 *
		 * @return array Array of settings for @see woocommerce_admin_fields() function.
		 */
		public static function wpssw_get_settings() {
			$wpssw_status_array              = wc_get_order_statuses();
			$wpssw_order_spreadsheet_setting = self::wpssw_check_order_spreadsheet_setting();
			$wpssw_settings                  = array(
				'section_first_title'  => array(
					'name' => '',
					'type' => 'title',
					'desc' => '',
					'id'   => 'wc_google_sheet_settings_first_section_start',
				),
				array( 'type' => 'select_spreadsheet' ),
				'section_first_end'    => array(
					'type' => 'sectionend',
					'id'   => 'wc_google_sheet_settings_first_section_end',
				),
				'section_second_title' => array(
					'name' => '',
					'type' => 'title',
					'desc' => '',
					'id'   => 'wc_google_sheet_settings_second_section_start',
				),
				array( 'type' => 'set_sheets' ),
				'section_second_end'   => array(
					'type' => 'sectionend',
					'id'   => 'wc_google_sheet_settings_second_section_end',
				),
				'section_third_title'  => array(
					'name' => '',
					'type' => 'title',
					'desc' => '',
					'id'   => 'wc_google_sheet_settings_third_section_start',
				),
				array( 'type' => 'manage_row_field' ),
				array( 'type' => 'set_headers' ),
				array( 'type' => 'custom_headers_action' ),
				array( 'type' => 'product_category_as_order_filter' ),
				array( 'type' => 'product_as_sheet_header' ),
				array( 'type' => 'product_headers_append_after' ),
				'section_third_end'    => array(
					'type' => 'sectionend',
					'id'   => 'wc_google_sheet_settings_third_section_end',
				),
				'section_fourth_title' => array(
					'name'  => '',
					'type'  => 'title',
					'desc'  => '',
					'class' => 'section_fourth_end',
					'id'    => 'wc_google_sheet_settings_fourth_section_start',
				),
				array( 'type' => 'repeat_checkbox' ),
				array( 'type' => 'order_asc_desc' ),
				array( 'type' => 'inherit_style' ),
				array( 'type' => 'exclude_order_status' ),
				array( 'type' => 'import_orders' ),
				array( 'type' => 'sync_button' ),
				'section_fourth_end'   => array(
					'type' => 'sectionend',
					'id'   => 'wc_google_sheet_settings_fourth_section_end',
				),
				'section_sixth_end'    => array(
					'type' => 'sectionend',
					'id'   => 'wc_google_sheet_settings_sixth_section_end',
				),
			);

			$wpssw_custom_status_array = array();
			$wpssw_settingflag         = 0;
			foreach ( $wpssw_status_array as $wpssw_key => $wpssw_val ) {
				$wpssw_status = substr( $wpssw_key, strpos( $wpssw_key, '-' ) + 1 );
				if ( ! in_array( $wpssw_status, self::$wpssw_default_status, true ) ) {
					$wpssw_settingflag++;
					if ( 1 === (int) $wpssw_settingflag ) {
						$wpssw_custom_status_array['section_fifth_title'] = array(
							'name' => '',
							'type' => 'title',
							'desc' => '',
							'id'   => 'wc_google_sheet_settings_second_section_start',
						);
					}
					$wpssw_custom_status_array['set_custom_sheets'] = array( 'type' => 'set_custom_sheets' );
				}
			}
			if ( $wpssw_settingflag > 0 ) {
				$wpssw_custom_status_array['section_fifth_end'] = array(
					'type' => 'sectionend',
					'id'   => 'wc_google_sheet_settings_second_section_end',
				);
			}
			/*Product names as sheets code start*/
			$is_product_names_as_sheet          = apply_filters( 'wpssw_is_productname_as_sheets', '' );
			$wpssw_product_names_as_sheet_array = array();
			if ( 'yes' === (string) $is_product_names_as_sheet ) {
				$wpssw_product_names_as_sheet_array['product_names_as_sheet'] = array(
					'type' => 'product_names_as_sheet',
				);
			}
			if ( ! empty( $wpssw_product_names_as_sheet_array ) ) {
				$wpssw_settings = array_slice( $wpssw_settings, 0, 7, true ) + $wpssw_product_names_as_sheet_array + array_slice( $wpssw_settings, 7, count( $wpssw_settings ) - 1, true );
			}

			/*Productnames as sheets code end*/

			if ( ! empty( $wpssw_custom_status_array ) ) {
				$wpssw_settings = array_slice( $wpssw_settings, 0, 7, true ) + $wpssw_custom_status_array + array_slice( $wpssw_settings, 7, count( $wpssw_settings ) - 1, true );
			}
			return $wpssw_settings;
		}
		/**
		 * Get headers using key.
		 *
		 * @param array  $wpssw_headers .
		 * @param string $array_key .
		 * @return array
		 */
		public static function get_headers_by_key( &$wpssw_headers = array(), $array_key = '' ) {
			if ( ! is_array( $wpssw_headers ) ) {
				return false;
			}
			if ( isset( $wpssw_headers['WPSSW_Default'][ $array_key ] ) ) {
				$wpssw_result = $wpssw_headers['WPSSW_Default'][ $array_key ];
				unset( $wpssw_headers['WPSSW_Default'][ $array_key ] );
			}
			return $wpssw_result;
		}
		/**
		 * Get products meta values for WooCommerce Product Add on.
		 */
		public static function wpssw_get_all_meta_values() {
			global $wpdb;
			$wpssw_wc_cf_headers = array();
			$table_name          = $wpdb->prefix;
			$meta_key            = '_product_addons';
			// @codingStandardsIgnoreStart.
			$wpssw_querystr  = "SELECT {$wpdb->prefix}posts.* FROM {$wpdb->prefix}posts INNER JOIN {$wpdb->prefix}postmeta ON ( {$wpdb->prefix}posts.ID = {$wpdb->prefix}postmeta.post_id ) WHERE 1=1 AND ( {$wpdb->prefix}postmeta.meta_key = '_product_addons' )"; //db call ok.
			$wpssw_postsmeta = $wpdb->get_results( $wpssw_querystr, ARRAY_A );
			// @codingStandardsIgnoreEnd.
			foreach ( $wpssw_postsmeta as $wpssw_cfield ) {
				$wpssw_value = get_post_meta( $wpssw_cfield['ID'], '_product_addons', true );
				if ( ! empty( $wpssw_value ) ) {
					foreach ( $wpssw_value as $wpssw_val ) {
						if ( 'heading' === (string) $wpssw_val['type'] ) {
							continue;
						}
						$wpssw_wc_cf_headers[] = $wpssw_val['name'];
					}
				}
			}
			return $wpssw_wc_cf_headers;
		}
		/**
		 * Check whether order setting is enabled or not.
		 */
		public static function wpssw_check_order_spreadsheet_setting() {
			$wpssw_spreadsheetid             = parent::wpssw_option( 'wpssw_woocommerce_spreadsheet' );
			$wpssw_selections                = stripslashes_deep( parent::wpssw_option( 'wpssw_sheet_headers_list' ) );
			$wpssw_order_spreadsheet_setting = self::wpssw_option( 'wpssw_order_spreadsheet_setting' );
			if ( 'yes' === (string) $wpssw_order_spreadsheet_setting || ( empty( $wpssw_order_spreadsheet_setting ) && ( ! empty( $wpssw_selections ) || '' !== $wpssw_spreadsheetid ) ) ) {
				return 'yes';
			} else {
				return 'no';
			}
		}
	}
	WPSSW_Order::init();
	endif;
