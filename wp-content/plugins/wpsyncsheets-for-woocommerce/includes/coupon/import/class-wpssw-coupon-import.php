<?php
/**
 * Main WPSyncSheets_For_WooCommerce namespace.
 *
 * @since 1.0.0
 * @package wpsyncsheets-for-woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }
if ( ! class_exists( 'WPSSW_Coupon_Import' ) ) :
	/**
	 * Class WPSSW_Coupon_Import.
	 */
	class WPSSW_Coupon_Import extends WPSSW_Setting {
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
			$wpssw_include->wpssw_include_coupon_import_ajax_hook();
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
		 * Get coupons count for syncronization
		 */
		public static function wpssw_get_coupon_import_count() {
			if ( ! isset( $_POST['wpssw_coupon_settings'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wpssw_coupon_settings'] ) ), 'save_coupon_settings' ) ) {
				echo esc_html__( 'Sorry, your nonce did not verify.', 'wpssw' );
				die();
			}
			if ( ! self::$instance_api->checkcredenatials() ) {
				return;
			}
			$wpssw_coupon_spreadsheet_setting = parent::wpssw_option( 'wpssw_coupon_spreadsheet_setting' );
			$wpssw_spreadsheetid              = parent::wpssw_option( 'wpssw_coupon_spreadsheet_id' );
			$wpssw_sheetname                  = 'All Coupons';

			if ( 'yes' !== (string) $wpssw_coupon_spreadsheet_setting ) {
				return;
			}
			$wpssw_sheet    = "'" . $wpssw_sheetname . "'!A:A";
			$wpssw_allentry = self::$instance_api->get_row_list( $wpssw_spreadsheetid, $wpssw_sheetname );
			$wpssw_data     = $wpssw_allentry->getValues();
			$wpssw_headers  = array_shift( $wpssw_data );

			$wpssw_insert_coupons = array();
			$wpssw_update_coupons = array();
			$wpssw_delete_coupons = array();
			if ( in_array( 'Insert', $wpssw_headers, true ) ) {
				$wpssw_insert_key     = array_search( 'Insert', $wpssw_headers, true );
				$wpssw_insert_coupons = array_values( array_filter( array_column( $wpssw_data, $wpssw_insert_key ) ) );
			}
			if ( in_array( 'Update', $wpssw_headers, true ) ) {
				$wpssw_update_key     = array_search( 'Update', $wpssw_headers, true );
				$wpssw_update_coupons = array_values( array_filter( array_column( $wpssw_data, $wpssw_update_key ) ) );
			}
			if ( in_array( 'Delete', $wpssw_headers, true ) ) {
				$wpssw_delete_key     = array_search( 'Delete', $wpssw_headers, true );
				$wpssw_delete_coupons = array_values( array_filter( array_column( $wpssw_data, $wpssw_delete_key ) ) );
			}
			$wpssw_result_array = array();
			if ( count( $wpssw_insert_coupons ) > 0 ) {
				$wpssw_result_array['insertcoupons'] = count( $wpssw_insert_coupons );
			}
			if ( count( $wpssw_update_coupons ) > 0 ) {
				$wpssw_result_array['updatecoupons'] = count( $wpssw_update_coupons );
			}
			if ( count( $wpssw_delete_coupons ) > 0 ) {
				$wpssw_result_array['deletecoupons'] = count( $wpssw_delete_coupons );
			}
			$totalimportcoupons                       = array_sum( array_values( $wpssw_result_array ) );
			$wpssw_result_array['totalimportcoupons'] = $totalimportcoupons;
			echo wp_json_encode( $wpssw_result_array );
			die;
		}
		/**
		 * Import coupon
		 */
		public static function wpssw_coupon_import() {

			$wpssw_cron_function_call = false;
			if ( ! isset( $_POST['wpssw_coupon_settings'] ) ) {
				$wpssw_cron_function_call = true;
			}
			if ( ! $wpssw_cron_function_call ) {
				if ( ! isset( $_POST['wpssw_coupon_settings'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wpssw_coupon_settings'] ) ), 'save_coupon_settings' ) ) {
					echo esc_html__( 'Sorry, your nonce did not verify.', 'wpssw' );
					die();
				}
			}
			$wpssw_coupon_spreadsheet_setting = parent::wpssw_option( 'wpssw_coupon_spreadsheet_setting' );
			if ( 'yes' !== (string) $wpssw_coupon_spreadsheet_setting ) {
				return;
			}
			$wpssw_spreadsheetid = parent::wpssw_option( 'wpssw_coupon_spreadsheet_id' );

			$wpssw_sheetid   = '';
			$wpssw_sheetname = 'All Coupons';
			if ( $wpssw_cron_function_call ) {
				$wpssw_spreadsheets_list = self::$instance_api->get_spreadsheet_listing();
				if ( empty( $wpssw_spreadsheetid ) || empty( $wpssw_sheetname ) ) {
					return;
				}
				if ( ! array_key_exists( $wpssw_spreadsheetid, $wpssw_spreadsheets_list ) ) {
					return;
				} else {
					$wpssw_response            = self::$instance_api->get_sheet_listing( $wpssw_spreadsheetid );
					$wpssw_existingsheetsnames = self::$instance_api->get_sheet_list( $wpssw_response );
					if ( ! isset( $wpssw_existingsheetsnames[ $wpssw_sheetname ] ) ) {
						return;
					}
					$wpssw_sheetid = $wpssw_existingsheetsnames[ $wpssw_sheetname ];
				}
			}

			$wpssw_woo_selections = stripslashes_deep( parent::wpssw_option( 'wpssw_woo_coupon_headers' ) );
			if ( ! $wpssw_woo_selections ) {
				return;
			}
			array_unshift( $wpssw_woo_selections, 'Coupon Id' );
			$wpssw_sheet      = "'" . $wpssw_sheetname . "'!A:A";
			$wpssw_allentry   = self::$instance_api->get_row_list( $wpssw_spreadsheetid, $wpssw_sheetname );
			$wpssw_data       = $wpssw_allentry->getValues();
			$wpssw_allentry   = null;
			$wpssw_coupon_ids = array_map(
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

			$wpssw_insert_coupons = array();
			$wpssw_update_coupons = array();
			$wpssw_delete_coupons = array();
			if ( in_array( 'Insert', $wpssw_headers, true ) ) {
				$wpssw_insert_key     = array_search( 'Insert', $wpssw_headers, true );
				$wpssw_insert_coupons = array_map(
					function( $wpssw_element ) use ( $wpssw_insert_key ) {
						if ( isset( $wpssw_element[ $wpssw_insert_key ] ) ) {
							return $wpssw_element[ $wpssw_insert_key ];
						} else {
							return '';
						}
					},
					$wpssw_data
				);
				$wpssw_insert_coupons = array_filter( $wpssw_insert_coupons );
			}
			if ( in_array( 'Update', $wpssw_headers, true ) ) {
				$wpssw_update_key     = array_search( 'Update', $wpssw_headers, true );
				$wpssw_update_coupons = array_map(
					function( $wpssw_element ) use ( $wpssw_update_key ) {
						if ( isset( $wpssw_element[ $wpssw_update_key ] ) ) {
							return $wpssw_element[ $wpssw_update_key ];
						} else {
							return '';
						}
					},
					$wpssw_data
				);

				$wpssw_update_coupons = array_filter( $wpssw_update_coupons );
			}
			if ( in_array( 'Delete', $wpssw_headers, true ) ) {
				$wpssw_delete_key     = array_search( 'Delete', $wpssw_headers, true );
				$wpssw_delete_coupons = array_map(
					function( $wpssw_element ) use ( $wpssw_delete_key ) {
						if ( isset( $wpssw_element[ $wpssw_delete_key ] ) ) {
							return $wpssw_element[ $wpssw_delete_key ];
						} else {
							return '';
						}
					},
					$wpssw_data
				);
				$wpssw_delete_coupons = array_filter( $wpssw_delete_coupons );
			}
			if ( $wpssw_cron_function_call ) {
				$wpssw_importcouponlimit = apply_filters( 'wpssw_coupon_autoimport_limit', 500 );
			} else {
				$wpssw_importcouponlimit = isset( $_POST['importcouponlimit'] ) ? sanitize_text_field( wp_unslash( $_POST['importcouponlimit'] ) ) : '';
			}

			$deleterequestarray = array();
			$newimportcoupon    = 0;

			$inserted_ids = array();
			$updated_ids  = array();
			$deleted_ids  = array();
			$message      = '';

			if ( ! empty( $wpssw_update_coupons ) ) {
				foreach ( $wpssw_update_coupons as $wpssw_couponid => $wpssw_val ) {
					if ( 1 !== (int) $wpssw_val || ! isset( $wpssw_data[ $wpssw_couponid ] ) ) {
						continue;
					}
					if ( $newimportcoupon > $wpssw_importcouponlimit ) {
						break;
					}
					set_time_limit( 999 );
					$wpssw_coupon_index = $wpssw_couponid + 1;

					if ( isset( $wpssw_coupon_ids[ $wpssw_coupon_index ] ) && ! empty( $wpssw_coupon_ids[ $wpssw_coupon_index ] ) ) {
						$coupon_id = $wpssw_coupon_ids[ $wpssw_coupon_index ];

						$wpssw_coupon_values = $wpssw_data[ $wpssw_couponid ];

						$wpssw_coupon_code_key = array_search( 'Coupon Code', $wpssw_woo_selections, true );

						$wpssw_coupon = new WC_Coupon( $coupon_id );

						if ( false !== $wpssw_coupon_code_key ) {
							$wpssw_coupon_code = isset( $wpssw_coupon_values[ $wpssw_coupon_code_key ] ) ? $wpssw_coupon_values[ $wpssw_coupon_code_key ] : '';
						} else {
							$wpssw_coupon_code = $wpssw_coupon->get_code();
						}
						if ( empty( $wpssw_coupon_code ) ) {
							if ( empty( $message ) ) {
								$message = 'addcouponcode';
							}
								continue;
						} else {
							$wpssw_post = get_post( $coupon_id );
							if ( 'shop_coupon' === (string) $wpssw_post->post_type && 'trash' !== (string) $wpssw_post->post_status ) {
								self::wpssw_update_coupon( $coupon_id, $wpssw_data[ $wpssw_couponid ] );
								$updated_ids[] = $coupon_id;
								$newimportcoupon++;
							} else {
								if ( empty( $message ) ) {
									$message = 'couponnotexists';
								}
								continue;
							}
						}
					} else {
						if ( empty( $message ) ) {
							$message = 'addcouponId';
						}
								continue;
					}
				}
			}

			if ( ! empty( $wpssw_insert_coupons ) ) {
				if ( empty( $wpssw_sheetid ) && 0 !== $wpssw_sheetid ) {
					$wpssw_response            = self::$instance_api->get_sheet_listing( $wpssw_spreadsheetid );
					$wpssw_existingsheetsnames = self::$instance_api->get_sheet_list( $wpssw_response );
					$wpssw_sheetid             = $wpssw_existingsheetsnames[ $wpssw_sheetname ];
				}
				foreach ( $wpssw_insert_coupons as $wpssw_coupon_index => $wpssw_val ) {
					if ( 1 !== (int) $wpssw_val || ! isset( $wpssw_data[ $wpssw_coupon_index ] ) ) {
						continue;
					}
					if ( $newimportcoupon > $wpssw_importcouponlimit ) {
						break;
					}
					set_time_limit( 999 );

					$wpssw_coupon_values   = $wpssw_data[ $wpssw_coupon_index ];
					$wpssw_coupon_code_key = array_search( 'Coupon Code', $wpssw_woo_selections, true );
					if ( false !== $wpssw_coupon_code_key ) {
						$wpssw_coupon_code = isset( $wpssw_coupon_values[ $wpssw_coupon_code_key ] ) ? $wpssw_coupon_values[ $wpssw_coupon_code_key ] : '';
					}
					$wpssw_coupon_id = isset( $wpssw_coupon_values[0] ) ? $wpssw_coupon_values[0] : '';

					if ( ! empty( $wpssw_coupon_code ) && empty( $wpssw_coupon_id ) ) {

						$new_post = array(
							'post_title'  => $wpssw_coupon_code,
							'post_type'   => 'shop_coupon',
							'post_status' => 'publish',
						);

						if ( $wpssw_sheetid ) {
							$param                = array();
							$startindex           = $wpssw_coupon_index + 1;
							$endindex             = $wpssw_coupon_index + 2;
							$param                = self::$instance_api->prepare_param( $wpssw_sheetid, $startindex, $endindex );
							$deleterequestarray[] = self::$instance_api->deleteDimensionrequests( $param, 'ROWS' );
						}
						// Creating the Coupon .
						$coupon_id = wp_insert_post( $new_post );
						self::wpssw_update_coupon( $coupon_id, $wpssw_coupon_values );
						$inserted_ids[] = $coupon_id;
						$newimportcoupon++;
					} elseif ( ! empty( $wpssw_coupon_id ) ) {
						if ( empty( $message ) ) {
							$message = 'couponIdexist';
						}
								continue;
					} elseif ( false === $wpssw_coupon_code_key ) {
						if ( empty( $message ) ) {
							$message = 'addcouponcodecolumn';
						}
								continue;
					} else {
						if ( empty( $message ) ) {
							$message = 'addcouponcode';
						}
								continue;
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
			}
			if ( ! empty( $wpssw_delete_coupons ) ) {
				foreach ( $wpssw_delete_coupons as $wpssw_couponid => $wpssw_val ) {
					if ( 1 !== (int) $wpssw_val || ! isset( $wpssw_data[ $wpssw_couponid ] ) ) {
						continue;
					}
					if ( $newimportcoupon > $wpssw_importcouponlimit ) {
						break;
					}
					set_time_limit( 999 );

					$wpssw_coupon_index = $wpssw_couponid + 1;

					if ( isset( $wpssw_coupon_ids[ $wpssw_coupon_index ] ) && ! empty( $wpssw_coupon_ids[ $wpssw_coupon_index ] ) ) {
						$coupon_id = $wpssw_coupon_ids[ $wpssw_coupon_index ];

						$wpssw_coupon = new WC_Coupon( $coupon_id );
						$wpssw_post   = get_post( $coupon_id );
						if ( 'shop_coupon' === (string) $wpssw_post->post_type && 'trash' !== (string) $wpssw_post->post_status ) {
							remove_action( 'wp_trash_post', 'WPSSW_Setting::wpssw_wcgs_trash' );
							wp_trash_post( $coupon_id );
							$deleted_ids[] = $coupon_id;
						} else {

							if ( empty( $message ) ) {
								$message = 'couponnotexists';
							}
								continue;
						}
						$newimportcoupon++;
					} else {

						if ( empty( $message ) ) {
							$message = 'addcouponId';
						}
								continue;
					}
				}
			}
			$settings = array(
				'setting'        => 'coupon',
				'setting_enable' => $wpssw_coupon_spreadsheet_setting,
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
		 * Update imported coupon
		 *
		 * @param int    $wpssw_couponid coupon id.
		 * @param array  $wpssw_data coupon data array.
		 * @param string $wpssw_opration opration to perform on coupon.
		 */
		public static function wpssw_update_coupon( $wpssw_couponid, $wpssw_data, $wpssw_opration = 'update' ) {

			if ( (int) $wpssw_couponid < 1 ) {
				return;
			}
			$wpssw_woo_selections = stripslashes_deep( parent::wpssw_option( 'wpssw_woo_coupon_headers' ) );
			if ( ! $wpssw_woo_selections ) {
				return;
			}
			array_unshift( $wpssw_woo_selections, 'Coupon Id' );
			$wpssw_include = new WPSSW_Include_Action();
			$wpssw_include->wpssw_include_coupon_compatibility_files();
			$wpssw_woocoupon_headers   = apply_filters( 'wpsyncsheets_coupon_headers', array() );
			$wpssw_default_header      = $wpssw_woocoupon_headers['WPSSW_Coupon_Headers'];
			$wpssw_coupon_data_headers = array( 'Coupon Code', 'Description' );
			$wpssw_updated_array       = array();
			$wpssw_crud_operation      = array( 'Insert', 'Update', 'Delete' );

			$wpssw_coupon         = new WC_Coupon( $wpssw_couponid );
			$wpssw_discount_types = array( 'percent', 'fixed_cart', 'fixed_product' );

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

					if ( in_array( $wpssw_header, $wpssw_coupon_data_headers, true ) ) {
						if ( ( 'post_title' === (string) $wpssw_meta_key ) ) {
							$wpssw_updated_array['post_name'] = $wpssw_data[ $wpssw_key ];
						}
						$wpssw_updated_array[ $wpssw_meta_key ] = $wpssw_data[ $wpssw_key ];
						continue;
					}
					if ( 'customer_email' === (string) $wpssw_meta_key ) {
						$emails = explode( ',', $wpssw_data[ $wpssw_key ] );
						self::wpssw_update_coupon_meta( $wpssw_couponid, $wpssw_meta_key, $emails );
						continue;
					}
					if ( 'exclude_product_categories' === (string) $wpssw_meta_key || 'product_categories' === (string) $wpssw_meta_key ) {
						$product_categories = array_filter( explode( ',', $wpssw_data[ $wpssw_key ] ) );

						$category_names = WPSSW_Coupon::wpssw_get_all_product_categories();

						$wpssw_value  = array();
						$category_ids = array();

						if ( ! empty( $product_categories ) ) {
							foreach ( $product_categories as &$category ) {
								$category = ucfirst( strtolower( trim( $category ) ) );

								if ( in_array( $category, $category_names, true ) ) {
									$category_ids[] = array_search( $category, $category_names, true );
								}
							}
							$wpssw_value = array_filter( $category_ids );
						} else {
							$wpssw_value = array();
						}
						self::wpssw_update_coupon_meta( $wpssw_couponid, $wpssw_meta_key, $wpssw_value );
						continue;
					}
					if ( 'discount_type' === (string) $wpssw_meta_key ) {
						$discount_type = trim( strtolower( $wpssw_data[ $wpssw_key ] ) );
						if ( in_array( $discount_type, $wpssw_discount_types, true ) ) {
							self::wpssw_update_coupon_meta( $wpssw_couponid, $wpssw_meta_key, $discount_type );
						}
						continue;
					}
					if ( 'date_expires' === (string) $wpssw_meta_key ) {
						self::wpssw_update_coupon_meta( $wpssw_couponid, $wpssw_meta_key, strtotime( $wpssw_data[ $wpssw_key ] ) );
						continue;
					}
					if ( 'product_ids' === (string) $wpssw_meta_key || 'exclude_product_ids' === (string) $wpssw_meta_key ) {
						$wpssw_products_name_array = array();
						$wpssw_products            = array();
						$wpssw_product_names       = '';
						$wpssw_product_ids         = '';
						$wpssw_products_name_array = array_filter( explode( ',', $wpssw_data[ $wpssw_key ] ) );
						$wpssw_product_names       = implode( "','", $wpssw_products_name_array );
						if ( ! empty( $wpssw_product_names ) ) {
							$wpssw_product_names = "'" . $wpssw_product_names . "'";
							$wpssw_products      = self::wpssw_get_products_array( $wpssw_product_names );
							$wpssw_product_ids   = implode( ',', $wpssw_products );
						}
						self::wpssw_update_coupon_meta( $wpssw_couponid, $wpssw_meta_key, $wpssw_product_ids );
						continue;
					}
					self::wpssw_update_coupon_meta( $wpssw_couponid, $wpssw_meta_key, $wpssw_data[ $wpssw_key ] );
				}
			}
			$wpssw_updated_array['ID'] = $wpssw_couponid;
			wp_update_post( $wpssw_updated_array );
			$wpssw_coupon = new WC_Coupon( $wpssw_couponid );
			remove_action( 'woocommerce_coupon_object_updated_props', 'WPSSW_Coupon::wpssw_coupon_object_updated_props' );
			$wpssw_coupon->save();
		}
		/**
		 * Update post meta
		 *
		 * @param int          $wpssw_couponid post id to update post meta.
		 * @param string       $wpssw_meta_key meta key to update.
		 * @param string|array $wpssw_data value for meta key.
		 */
		public static function wpssw_update_coupon_meta( $wpssw_couponid, $wpssw_meta_key, $wpssw_data ) {
			if ( ! $wpssw_couponid ) {
				return;
			}
			update_post_meta( $wpssw_couponid, $wpssw_meta_key, $wpssw_data );
		}
		/**
		 * Get Product Ids from Product names
		 *
		 * @param string $wpssw_product_names product names.
		 * @return array  $wpssw_product_ids product ids of given product names.
		 */
		public static function wpssw_get_products_array( $wpssw_product_names ) {
			global $wpdb;

			if ( empty( $wpssw_product_names ) || "''" === (string) $wpssw_product_names ) {
				return;
			}
			// @codingStandardsIgnoreStart.
			$wpssw_querystr = "SELECT ID FROM {$wpdb->prefix}posts WHERE post_title IN ($wpssw_product_names) AND post_status = 'publish'"; // db call ok.

			$wpssw_products = $wpdb->get_results( $wpssw_querystr );
			// @codingStandardsIgnoreEnd.
			$wpssw_product_ids = array_column( $wpssw_products, 'ID' );
			return $wpssw_product_ids;
		}
	}
	new WPSSW_Coupon_Import();
endif;
