<?php
/**
 * Main WPSyncSheets_For_WooCommerce namespace.
 *
 * @since 1.0.0
 * @package wpsyncsheets-for-woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }
if ( ! function_exists( 'post_exists' ) ) {
	require_once ABSPATH . 'wp-admin/includes/post.php';
}
if ( ! class_exists( 'WPSSW_Product_Import' ) ) :
	/**
	 * Class WPSSW_Product_Import.
	 */
	class WPSSW_Product_Import extends WPSSW_Setting {
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
			$wpssw_include->wpssw_include_product_import_ajax_hook();
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
		public static function wpssw_get_product_import_count() {

			if ( ! isset( $_POST['wpssw_product_settings'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wpssw_product_settings'] ) ), 'save_product_settings' ) ) {
				echo esc_html__( 'Sorry, your nonce did not verify.', 'wpssw' );
				die();
			}
			if ( ! self::$instance_api->checkcredenatials() ) {
				return;
			}
			$wpssw_product_spreadsheet_setting = parent::wpssw_option( 'wpssw_product_spreadsheet_setting' );
			$wpssw_spreadsheetid               = parent::wpssw_option( 'wpssw_product_spreadsheet_id' );
			$wpssw_sheetname                   = 'All Products';

			if ( 'yes' !== (string) $wpssw_product_spreadsheet_setting ) {
				return;
			}
			$wpssw_sheet    = "'" . $wpssw_sheetname . "'!A:A";
			$wpssw_allentry = self::$instance_api->get_row_list( $wpssw_spreadsheetid, $wpssw_sheetname );
			$wpssw_data     = $wpssw_allentry->getValues();
			$wpssw_headers  = array_shift( $wpssw_data );
			if ( ! in_array( 'Product Id', $wpssw_headers, true ) || ! in_array( 'Product Variation Id', $wpssw_headers, true ) ) {
				echo esc_html( 'notexist' );
				die;
			}
			$wpssw_insert_products = array();
			$wpssw_update_products = array();
			$wpssw_delete_products = array();
			if ( in_array( 'Insert', $wpssw_headers, true ) ) {
				$wpssw_insert_key      = array_search( 'Insert', $wpssw_headers, true );
				$wpssw_insert_products = array_values( array_filter( array_column( $wpssw_data, $wpssw_insert_key ) ) );
			}
			if ( in_array( 'Update', $wpssw_headers, true ) ) {
				$wpssw_update_key      = array_search( 'Update', $wpssw_headers, true );
				$wpssw_update_products = array_values( array_filter( array_column( $wpssw_data, $wpssw_update_key ) ) );
			}
			if ( in_array( 'Delete', $wpssw_headers, true ) ) {
				$wpssw_delete_key      = array_search( 'Delete', $wpssw_headers, true );
				$wpssw_delete_products = array_values( array_filter( array_column( $wpssw_data, $wpssw_delete_key ) ) );
			}
			$wpssw_result_array                        = array();
			$wpssw_result_array['totalimportproducts'] = 0;
			$totalimportproducts                       = 0;
			if ( count( $wpssw_insert_products ) > 0 ) {
				$wpssw_result_array['insertproducts'] = count( $wpssw_insert_products );
				$totalimportproducts                  = $totalimportproducts + count( $wpssw_insert_products );
			}
			if ( count( $wpssw_update_products ) > 0 ) {
				$wpssw_result_array['updateproducts'] = count( $wpssw_update_products );
				$totalimportproducts                  = $totalimportproducts + count( $wpssw_update_products );
			}
			if ( count( $wpssw_delete_products ) > 0 ) {
				$wpssw_result_array['deleteproducts'] = count( $wpssw_delete_products );
				$totalimportproducts                  = $totalimportproducts + count( $wpssw_delete_products );
			}
			$wpssw_result_array['totalimportproducts'] = $totalimportproducts;

			echo wp_json_encode( $wpssw_result_array );
			die;
		}
		/**
		 * Import product
		 */
		public static function wpssw_product_import() {

			$wpssw_cron_function_call = false;
			if ( ! isset( $_POST['wpssw_product_settings'] ) ) {
				$wpssw_cron_function_call = true;
			}

			if ( ! $wpssw_cron_function_call ) {
				if ( ! isset( $_POST['wpssw_product_settings'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wpssw_product_settings'] ) ), 'save_product_settings' ) ) {
					echo esc_html__( 'Sorry, your nonce did not verify.', 'wpssw' );
					die();
				}
			}
			if ( ! self::$instance_api->checkcredenatials() ) {
				return;
			}
			$wpssw_product_spreadsheet_setting = parent::wpssw_option( 'wpssw_product_spreadsheet_setting' );
			$wpssw_spreadsheetid               = parent::wpssw_option( 'wpssw_product_spreadsheet_id' );
			$wpssw_sheetname                   = 'All Products';

			if ( 'yes' !== (string) $wpssw_product_spreadsheet_setting ) {
				return;
			}
			$wpssw_response            = self::$instance_api->get_sheet_listing( $wpssw_spreadsheetid );
			$wpssw_existingsheetsnames = self::$instance_api->get_sheet_list( $wpssw_response );
			$wpssw_response            = null;
			if ( false === array_key_exists( $wpssw_sheetname, $wpssw_existingsheetsnames ) ) {
				return;
			}

			$wpssw_woo_selections = stripslashes_deep( parent::wpssw_option( 'wpssw_woo_product_headers' ) );
			if ( ! $wpssw_woo_selections ) {
				return;
			}
			array_unshift( $wpssw_woo_selections, 'Product Id', 'Product Variation Id' );
			$wpssw_sheetid = $wpssw_existingsheetsnames[ $wpssw_sheetname ];

			$wpssw_sheet    = "'" . $wpssw_sheetname . "'!A:A";
			$wpssw_allentry = self::$instance_api->get_row_list( $wpssw_spreadsheetid, $wpssw_sheetname );

			$wpssw_data               = $wpssw_allentry->getValues();
			$wpssw_allentry           = null;
			$wpssw_importproductcount = isset( $_POST['importproductcount'] ) ? sanitize_text_field( wp_unslash( $_POST['importproductcount'] ) ) : '';
			$wpssw_importproductlimit = isset( $_POST['importproductlimit'] ) ? sanitize_text_field( wp_unslash( $_POST['importproductlimit'] ) ) : '';

			$wpssw_product_ids           = array_map(
				function( $wpssw_element ) {
					if ( isset( $wpssw_element[0] ) ) {
						return $wpssw_element[0];
					} else {
						return '';
					}
				},
				$wpssw_data
			);
			$wpssw_product_variation_ids = array_map(
				function( $wpssw_element ) {
					if ( isset( $wpssw_element[1] ) ) {
						return $wpssw_element[1];
					} else {
						return '';
					}
				},
				$wpssw_data
			);
			$wpssw_headers               = array_shift( $wpssw_data );

			$wpssw_insert_products = array();
			$wpssw_update_products = array();
			$wpssw_delete_products = array();
			if ( in_array( 'Insert', $wpssw_headers, true ) ) {
				$wpssw_insert_key      = array_search( 'Insert', $wpssw_headers, true );
				$wpssw_insert_products = array_map(
					function( $wpssw_element ) use ( $wpssw_insert_key ) {
						if ( isset( $wpssw_element[ $wpssw_insert_key ] ) ) {
							return $wpssw_element[ $wpssw_insert_key ];
						} else {
							return '';
						}
					},
					$wpssw_data
				);
				$wpssw_insert_products = array_filter( $wpssw_insert_products );
			}

			if ( in_array( 'Update', $wpssw_headers, true ) ) {
				$wpssw_update_key      = array_search( 'Update', $wpssw_headers, true );
				$wpssw_update_products = array_map(
					function( $wpssw_element ) use ( $wpssw_update_key ) {
						if ( isset( $wpssw_element[ $wpssw_update_key ] ) ) {
							return $wpssw_element[ $wpssw_update_key ];
						} else {
							return '';
						}
					},
					$wpssw_data
				);

				$wpssw_update_products = array_filter( $wpssw_update_products );
			}
			if ( in_array( 'Delete', $wpssw_headers, true ) ) {
				$wpssw_delete_key      = array_search( 'Delete', $wpssw_headers, true );
				$wpssw_delete_products = array_map(
					function( $wpssw_element ) use ( $wpssw_delete_key ) {
						if ( isset( $wpssw_element[ $wpssw_delete_key ] ) ) {
							return $wpssw_element[ $wpssw_delete_key ];
						} else {
							return '';
						}
					},
					$wpssw_data
				);
				$wpssw_delete_products = array_filter( $wpssw_delete_products );
			}

			$deleterequestarray = array();

			$newimportproduct    = 0;
			$message             = '';
			$updated_product_ids = array();

			if ( $wpssw_cron_function_call ) {
				$wpssw_importproductlimit = count( $wpssw_insert_products );
			}
			if ( ! empty( $wpssw_update_products ) ) {
				foreach ( $wpssw_update_products as $wpssw_productid => $wpssw_val ) {
					if ( 1 !== (int) $wpssw_val ) {
						continue;
					}
					if ( ! isset( $wpssw_data[ $wpssw_productid ] ) ) {
						continue;
					}
					$wpssw_product_index = $wpssw_productid + 1;

					if ( ! $wpssw_cron_function_call && $newimportproduct > $wpssw_importproductlimit ) {
						break;
					}
					set_time_limit( 999 );

					if ( isset( $wpssw_product_variation_ids[ $wpssw_product_index ] ) && ! empty( $wpssw_product_variation_ids[ $wpssw_product_index ] ) ) {

						$prd_id        = $wpssw_product_variation_ids[ $wpssw_product_index ];
						$wpssw_product = wc_get_product( $prd_id );
						if ( false === $wpssw_product || null === $wpssw_product && is_wp_error( $wpssw_product ) ) {
							if ( empty( $message ) ) {
									$message = 'productnotexists';
							}
							continue;
						}
						$wpssw_product = null;
						self::wpssw_update_variation_product( $prd_id, $wpssw_data[ $wpssw_productid ] );
						if ( ! in_array( (int) $wpssw_product_ids[ $wpssw_product_index ], $updated_product_ids, true ) ) {
							$updated_product_ids[] = (int) $wpssw_product_ids[ $wpssw_product_index ];
						}
					} elseif ( isset( $wpssw_product_ids[ $wpssw_product_index ] ) && ! empty( $wpssw_product_ids[ $wpssw_product_index ] ) ) {
						$prd_id        = $wpssw_product_ids[ $wpssw_product_index ];
						$wpssw_product = wc_get_product( $prd_id );
						if ( false === $wpssw_product || null === $wpssw_product && is_wp_error( $wpssw_product ) ) {
							if ( empty( $message ) ) {
								$message = 'productnotexists';
							}
							continue;
						}
						$wpssw_product = null;
						self::wpssw_update_product( $prd_id, $wpssw_data[ $wpssw_productid ] );
						if ( ! in_array( (int) $prd_id, $updated_product_ids, true ) ) {
							$updated_product_ids[] = (int) $prd_id;
						}
					} else {
						if ( empty( $message ) ) {
							$message = 'addproductId';
						}
						continue;
					}
					$newimportproduct++;
				}
				$wpssw_update_products = null;
			}
			$delete_row_indexes           = array();
			$bulk_inserted_products       = 0;
			$bulk_insert_variable_product = '';
			$wpssw_original_termids       = array();
			$wpssw_original_tagids        = array();

			$new_inserted_products = array();

			if ( ! empty( $wpssw_insert_products ) ) {
				$wpssw_language_count = 0;
				$wppsw_ln             = array();
				$languages            = apply_filters( 'wpml_active_languages', null, 'orderby=id&order=desc' );
				$language_codes       = array();
				if ( ! is_null( $languages ) && is_array( $languages ) ) {
					$language_codes = array_column( $languages, 'language_code' );
				}
				$is_wpml_sitepress_active = WPSSW_Product::wpssw_is_wpml_sitepress_active();
				foreach ( $wpssw_insert_products as $wpssw_product_index => $wpssw_val ) {
					if ( 1 !== (int) $wpssw_val ) {
						continue;
					}
					if ( ! isset( $wpssw_data[ $wpssw_product_index ] ) ) {
						continue;
					}
					if ( ! $wpssw_cron_function_call && $newimportproduct > $wpssw_importproductlimit ) {
						break;
					}
					set_time_limit( 999 );
					$wpssw_product_values = $wpssw_data[ $wpssw_product_index ];
					if ( count( $wpssw_product_values ) < count( $wpssw_woo_selections ) ) {
						$wpssw_woo_selections_count = count( $wpssw_woo_selections );
						for ( $i = count( $wpssw_product_values ); $i < $wpssw_woo_selections_count;$i++ ) {
							$wpssw_product_values[] = '';
						}
					}
					$wpssw_product_type_key = array_search( 'Product Type', $wpssw_woo_selections, true );
					$wpssw_product_type     = '';
					if ( false !== $wpssw_product_type_key && isset( $wpssw_product_values[ $wpssw_product_type_key ] ) ) {
						$wpssw_product_type = trim( strtolower( $wpssw_product_values[ $wpssw_product_type_key ] ) );
					}

					$wpssw_product_name_key = array_search( 'Product Name', $wpssw_woo_selections, true );
					$wpssw_product_name     = isset( $wpssw_product_values[ $wpssw_product_name_key ] ) ? $wpssw_product_values[ $wpssw_product_name_key ] : '';

					if ( isset( $wpssw_product_ids[ $wpssw_product_index ] ) && isset( $wpssw_product_ids[ $wpssw_product_index + 1 ] ) && ! empty( $wpssw_product_ids[ $wpssw_product_index ] ) && ! empty( $wpssw_product_ids[ $wpssw_product_index + 1 ] ) ) {
						if ( $wpssw_product_ids[ $wpssw_product_index ] === $wpssw_product_ids[ $wpssw_product_index + 1 ] ) {
							if ( ! preg_match( '/variation/', $wpssw_product_type ) ) {
								$wpssw_product_type = 'variation';
							}
						}
					}

					if ( ( false === (bool) $wpssw_product_type_key || ( false !== (bool) $wpssw_product_type_key && ! preg_match( '/variation/', $wpssw_product_type ) ) ) && (int) $bulk_inserted_products === (int) $wpssw_importproductlimit ) {
						break;
					}

					$wpssw_product_id = isset( $wpssw_product_values[0] ) ? $wpssw_product_values[0] : '';

					if ( ! empty( $wpssw_product_id ) ) {
						$product = wc_get_product( $wpssw_product_id );
						if ( false === $product || null === $product && is_wp_error( $product ) ) {
							if ( empty( $message ) ) {
								$message = 'productnotexists';
							}
							continue;
						}
					}

					if ( ! empty( $wpssw_product_name ) && empty( $wpssw_product_id ) && ! preg_match( '/variation/', $wpssw_product_type ) ) {

						$new_post = array(
							'post_title'  => $wpssw_product_name,
							'post_type'   => 'product',
							'post_status' => 'publish',
						);

						if ( $wpssw_sheetid ) {
							$delete_row_indexes[] = $wpssw_product_index + 1;
						}
						// Creating the Product .
						$post_id = wp_insert_post( $new_post );

						$new_inserted_products[] = $post_id;

						if ( preg_match( '/variable/', $wpssw_product_type ) ) {
							$bulk_insert_variable_product = $post_id;
						} else {
							$bulk_insert_variable_product = '';
						}

						$bulk_inserted_products++;
						/*Category*/
						if ( in_array( 'Product Categories', $wpssw_woo_selections, true ) ) {
							$wpssw_product_category_key = array_search( 'Product Categories', $wpssw_woo_selections, true );
							if ( ! empty( $wpssw_product_values[ $wpssw_product_category_key ] ) ) {
								$wpssw_categories = explode( ',', $wpssw_product_values[ $wpssw_product_category_key ] );
							} else {
								$wpssw_categories = array();
							}
							if ( $is_wpml_sitepress_active ) {
								$wpssw_product_wpml_key = array_search( 'WPML Language Code', $wpssw_woo_selections, true );

								$wpssw_termids = self::wpssw_parse_categories_field( $wpssw_categories, $post_id );
								if ( $wpssw_language_count > 0 && ! empty( $wpssw_original_termids ) ) {
									foreach ( $wpssw_original_termids as $termkey => $termval ) {
										$translated_post_id          = $post_id;
										$inserted_post_ids           = array(
											'original'    => $termval,
											'translation' => $wpssw_termids[ $termkey ],
										);
										$get_language_args           = array(
											'element_id'   => $inserted_post_ids['original'],
											'element_type' => 'tax_product_cat',
										);
										$original_post_language_info = apply_filters( 'wpml_element_language_details', null, $get_language_args );
										$language_code               = trim( $wpssw_product_values[ $wpssw_product_wpml_key ] );
										// Set the desired language.
										$language_args = array(
											'element_id'   => $inserted_post_ids['translation'],
											'element_type' => 'tax_product_cat',
											'trid'         => null !== $original_post_language_info ? $original_post_language_info->trid : '',
											'language_code' => $language_code,
											'source_language_code' => null !== $original_post_language_info ? $original_post_language_info->language_code : '',
										);
										do_action( 'wpml_set_element_language_details', $language_args );

									}
								}
								if ( 0 === (int) $wpssw_language_count ) {
									$wpssw_original_termids = $wpssw_termids;
								}
							} else {
								self::wpssw_parse_categories_field( $wpssw_categories, $post_id );
							}
						}

						/** Tags */
						if ( in_array( 'Product Tags', $wpssw_woo_selections, true ) ) {
							$wpssw_product_tag_key = array_search( 'Product Tags', $wpssw_woo_selections, true );
							if ( ! empty( $wpssw_product_values[ $wpssw_product_tag_key ] ) ) {
								$wpssw_tags = explode( ',', $wpssw_product_values[ $wpssw_product_tag_key ] );
							} else {
								$wpssw_tags = array();
							}
							if ( $is_wpml_sitepress_active ) {
								$wpssw_product_wpml_key = array_search( 'WPML Language Code', $wpssw_woo_selections, true );

								$wpssw_tagids = self::wpssw_parse_tags_field( $wpssw_tags, $post_id );
								if ( $wpssw_language_count > 0 && ! empty( $wpssw_original_tagids ) ) {
									foreach ( $wpssw_original_tagids as $tagkey => $tagval ) {
										$translated_post_id          = $post_id;
										$inserted_post_ids           = array(
											'original'    => $tagval,
											'translation' => $wpssw_tagids[ $tagkey ],
										);
										$get_language_args           = array(
											'element_id'   => $inserted_post_ids['original'],
											'element_type' => 'tax_product_tag',
										);
										$original_post_language_info = apply_filters( 'wpml_element_language_details', null, $get_language_args );
										$language_code               = trim( $wpssw_product_values[ $wpssw_product_wpml_key ] );
										// Set the desired language.
										$language_args = array(
											'element_id'   => $inserted_post_ids['translation'],
											'element_type' => 'tax_product_tag',
											'trid'         => null !== $original_post_language_info ? $original_post_language_info->trid : '',
											'language_code' => $language_code,
											'source_language_code' => null !== $original_post_language_info ? $original_post_language_info->language_code : '',
										);
										do_action( 'wpml_set_element_language_details', $language_args );

									}
								}
								if ( 0 === (int) $wpssw_language_count ) {
									$wpssw_original_tagids = $wpssw_tagids;
								}
							} else {
								self::wpssw_parse_tags_field( $wpssw_tags, $post_id );
							}
						}
						self::wpssw_update_product( $post_id, $wpssw_product_values );
						$wpssw_product_wpml_key = array_search( 'WPML Language Code', $wpssw_woo_selections, true );
						if ( $wpssw_language_count > 0 ) {

							$translated_post_id = $post_id;
							$inserted_post_ids  = array(
								'original'    => $wpssw_original_id,
								'translation' => $translated_post_id,
							);

							$wpml_element_type           = apply_filters( 'wpml_element_type', 'product' );
							$get_language_args           = array(
								'element_id'   => $inserted_post_ids['original'],
								'element_type' => 'product',
							);
							$original_post_language_info = apply_filters( 'wpml_element_language_details', null, $get_language_args );
							$language_code               = trim( $wpssw_product_values[ $wpssw_product_wpml_key ] );
							$set_language_args           = array(
								'element_id'           => $inserted_post_ids['translation'],
								'element_type'         => $wpml_element_type,
								'trid'                 => null !== $original_post_language_info ? $original_post_language_info->trid : '',
								'language_code'        => $language_code,
								'source_language_code' => null !== $original_post_language_info ? $original_post_language_info->language_code : '',
							);
							do_action( 'wpml_set_element_language_details', $set_language_args );
						}
						if ( 0 === (int) $wpssw_language_count ) {
							$wpssw_original_id    = $post_id;
							$wpssw_language_count = 1;
						}
						if ( ! in_array( trim( $wpssw_product_values[ $wpssw_product_wpml_key ] ), $wppsw_ln, true ) ) {
							$wppsw_ln[] = $wpssw_product_values[ $wpssw_product_wpml_key ];
						}
						if ( count( $language_codes ) === count( $wppsw_ln ) ) {
							$wppsw_ln             = array();
							$wpssw_language_count = 0;
						}
					} elseif ( empty( $wpssw_product_id ) && preg_match( '/variation/', $wpssw_product_type ) ) {

						if ( $bulk_insert_variable_product ) {
							$product_id = $bulk_insert_variable_product;
							$product    = wc_get_product( $product_id );
							if ( empty( $product ) || is_wp_error( $product ) ) {
								$bulk_insert_variable_product = '';
								continue;
							} else {
								if ( $wpssw_sheetid ) {
									$delete_row_indexes[] = $wpssw_product_index + 1;
								}
							}
						} else {
							continue;
						}

						$variation_post = array(
							'post_title'  => $product->get_name(),
							'post_status' => 'publish',
							'post_parent' => $product_id,
							'post_type'   => 'product_variation',
							'guid'        => $product->get_permalink(),
						);
						// Creating the Product Variation.
						$variation_id = wp_insert_post( $variation_post );
						if ( ! in_array( (int) $product_id, $new_inserted_products, true ) ) {
							$new_inserted_products[] = (int) $product_id;
						}
						self::wpssw_update_variation_product( $variation_id, $wpssw_product_values );
					} elseif ( ! empty( $wpssw_product_id ) && preg_match( '/variation/', $wpssw_product_type ) && preg_match( '/variable/', (string) $product->get_type() ) ) {
						$variation_post = array(
							'post_title'  => $product->get_name(),
							'post_status' => 'publish',
							'post_parent' => $wpssw_product_id,
							'post_type'   => 'product_variation',
							'guid'        => $product->get_permalink(),
						);

						// Creating the Product Variation.
						$variation_id = wp_insert_post( $variation_post );
						if ( ! in_array( (int) $wpssw_product_id, $updated_product_ids, true ) ) {
							$updated_product_ids[] = (int) $wpssw_product_id;
						}
						self::wpssw_update_variation_product( $variation_id, $wpssw_product_values );
						$bulk_insert_variable_product = '';
					} elseif ( ! empty( $wpssw_product_id ) ) {
						$bulk_insert_variable_product = '';
						if ( empty( $message ) ) {
								$message = 'productIdexist';
						}
							continue;
					} elseif ( false === $wpssw_product_name_key ) {
						$bulk_insert_variable_product = '';
						if ( empty( $message ) ) {
								$message = 'addproductnamecolumn';
						}
							continue;
					} else {
						$bulk_insert_variable_product = '';
						if ( empty( $message ) ) {
								$message = 'addproductname';
						}
							continue;
					}
					$newimportproduct++;
				}

				if ( ! empty( $delete_row_indexes ) ) {
					if ( count( $delete_row_indexes ) > 1 ) {
						$requests                 = array();
						$delete_row_indexes_count = count( $delete_row_indexes );
						for ( $i = 0;$i < $delete_row_indexes_count;$i++ ) {
							if ( 0 === (int) $i ) {
								$startindex = $delete_row_indexes[0];
							} elseif ( $delete_row_indexes[ $i ] - $delete_row_indexes[ $i - 1 ] > 1 ) {
								$requests[] = array(
									'startIndex' => $startindex,
									'endIndex'   => $delete_row_indexes[ $i - 1 ] + 1,
								);
								$startindex = $delete_row_indexes[ $i ];
							}
							if ( count( $delete_row_indexes ) - 1 === (int) $i ) {
								$requests[] = array(
									'startIndex' => $startindex,
									'endIndex'   => $delete_row_indexes[ $i ] + 1,
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
					} else {
						$param              = array();
						$startindex         = $delete_row_indexes[0];
						$endindex           = $delete_row_indexes[0] + 1;
						$param              = self::$instance_api->prepare_param( $wpssw_sheetid, $startindex, $endindex );
						$deleterequestarray = self::$instance_api->deleteDimensionrequests( $param, 'ROWS' );
					}

					if ( ! empty( $deleterequestarray ) ) {
						try {
							self::$instance_api->updatebachrequests(
								array(
									'spreadsheetid' => $wpssw_spreadsheetid,
									'requestarray'  => $deleterequestarray,
								)
							);
							$deleterequestarray = null;
						} catch ( Exception $e ) {
							return;
						}
					}
				}

				$wpssw_insert_products = null;
			}

			$deleted_product_ids = array();

			if ( ! empty( $wpssw_delete_products ) ) {
				foreach ( $wpssw_delete_products as $wpssw_productid => $wpssw_val ) {

					if ( 1 !== (int) $wpssw_val ) {
						continue;
					}

					if ( ! isset( $wpssw_data[ $wpssw_productid ] ) ) {
						continue;
					}

					if ( ! $wpssw_cron_function_call && $newimportproduct > $wpssw_importproductlimit ) {
						break;
					}
					set_time_limit( 999 );

					$wpssw_product_index = $wpssw_productid + 1;

					if ( isset( $wpssw_product_variation_ids[ $wpssw_product_index ] ) && ! empty( $wpssw_product_variation_ids[ $wpssw_product_index ] ) ) {

						$prd_id = $wpssw_product_variation_ids[ $wpssw_product_index ];

						wp_delete_post( $prd_id );

						if ( ! in_array( (int) $wpssw_product_ids[ $wpssw_product_index ], $updated_product_ids, true ) ) {
							$updated_product_ids[] = (int) $wpssw_product_ids[ $wpssw_product_index ];
						}
					} elseif ( isset( $wpssw_product_ids[ $wpssw_product_index ] ) && ! empty( $wpssw_product_ids[ $wpssw_product_index ] ) ) {
						$prd_id = $wpssw_product_ids[ $wpssw_product_index ];

						if ( ! in_array( (int) $prd_id, $deleted_product_ids, true ) ) {
							$deleted_product_ids[] = (int) $prd_id;
						}

						remove_action( 'wp_trash_post', 'WPSSW_Setting::wpssw_wcgs_trash' );
						wp_trash_post( $prd_id );

					} else {

						if ( empty( $message ) ) {
								$message = 'addproductId';
						}
							continue;
					}
					$newimportproduct++;
				}
				$wpssw_delete_products = null;
			}

			$wpssw_data                  = null;
			$wpssw_product_variation_ids = null;
			$wpssw_product_ids           = null;

			$settings = array(
				'setting'        => 'product',
				'setting_enable' => $wpssw_product_spreadsheet_setting,
				'spreadsheet_id' => $wpssw_spreadsheetid,
				'sheetname'      => $wpssw_sheetname,
			);

			if ( ! empty( $new_inserted_products ) ) {
				WPSSW_Setting::wpssw_multiple_update_data( $new_inserted_products, $settings, true, 'insert' );
				$new_inserted_products = null;
			}

			if ( ! empty( $deleted_product_ids ) ) {
				WPSSW_Setting::wpssw_multiple_update_data( $deleted_product_ids, $settings, true, 'delete' );
				$deleted_product_ids = null;
			}

			if ( ! empty( $updated_product_ids ) ) {
				WPSSW_Setting::wpssw_multiple_update_data( $updated_product_ids, $settings, true, 'update' );
				$updated_product_ids = null;
			}

			if ( ! empty( $message ) ) {
				echo esc_html( $message );
				die;
			}
			echo esc_html__( 'successful', 'wpssw' );
			die;
		}
		/**
		 * Update imported product
		 *
		 * @param int    $wpssw_productid product id.
		 * @param array  $wpssw_data product data array.
		 * @param string $wpssw_opration opration to perform on product.
		 */
		public static function wpssw_update_product( $wpssw_productid, $wpssw_data, $wpssw_opration = 'update' ) {

			if ( ! self::$instance_api->checkcredenatials() ) {
				return;
			}
			if ( (int) $wpssw_productid < 1 ) {
				return;
			}
			$wpssw_woo_selections = stripslashes_deep( parent::wpssw_option( 'wpssw_woo_product_headers' ) );
			if ( ! $wpssw_woo_selections ) {
				return;
			}

			$wpssw_product = wc_get_product( $wpssw_productid );

			if ( false === $wpssw_product || null === $wpssw_product && is_wp_error( $wpssw_product ) ) {
				return;
			}

			array_unshift( $wpssw_woo_selections, 'Product Id', 'Product Variation Id' );
			$wpssw_include = new WPSSW_Include_Action();
			$wpssw_include->wpssw_include_product_compatibility_files();
			$wpssw_wooproduct_headers = apply_filters( 'wpsyncsheets_product_headers', array() );
			$wpssw_default_header     = array_merge( $wpssw_wooproduct_headers['WPSSW_Default_Headers']['common'], $wpssw_wooproduct_headers['WPSSW_Default_Headers']['attribute_taxonomies'], $wpssw_wooproduct_headers['WPSSW_Default_Headers']['external'], $wpssw_wooproduct_headers['WPSSW_Default_Headers']['grouped'] );

			$wpssw_acf_headers = isset( $wpssw_wooproduct_headers['WPSSW_ACF'] ) ? WPSSW_Setting::wpssw_productarray_flatten( $wpssw_wooproduct_headers['WPSSW_ACF'] ) : array();
			foreach ( $wpssw_acf_headers as $acf_key => $acf_value ) {
				if ( is_numeric( $acf_key ) ) {
					unset( $wpssw_acf_headers[ $acf_key ] );
				}
			}
			$wpssw_jetengine_headers = isset( $wpssw_wooproduct_headers['WPSSW_Jet_Engine'] ) ? WPSSW_Setting::wpssw_productarray_flatten( $wpssw_wooproduct_headers['WPSSW_Jet_Engine'] ) : array();
			foreach ( $wpssw_jetengine_headers as $jetengine_field_key => $jetengine_field_value ) {
				if ( is_numeric( $jetengine_field_key ) ) {
					unset( $wpssw_jetengine_headers[ $jetengine_field_key ] );
				}
			}
			$wpssw_updated_array        = array();
			$wpssw_crud_operation       = array( 'Insert', 'Update', 'Delete' );
			$wpssw_exclude_product_type = array( 'variation', 'grouped' );
			$wpssw_addon_array          = WPSSW_Setting::wpssw_productarray_flatten( $wpssw_wooproduct_headers );
			foreach ( $wpssw_addon_array as $addon_key => $addon_value ) {
				if ( is_numeric( $addon_key ) ) {
					unset( $wpssw_addon_array[ $addon_key ] );
				}
			}
			$wpssw_variation_product_import_headers = apply_filters( 'wpssw_custom_headers_for_variation_product_import', array() );
			$wpssw_custom_import_product_headers    = apply_filters( 'wpssw_custom_headers_for_product_import', array() );
			foreach ( $wpssw_variation_product_import_headers as $variation_addon_key => $variation_addon_value ) {
				if ( is_numeric( $variation_addon_key ) ) {
					unset( $wpssw_variation_product_import_headers[ $variation_addon_key ] );
				}
			}

			$wpssw_attribute_taxonomies = WPSSW_Product::wpssw_get_all_attributes();
			$attribute_taxonomies       = wc_get_attribute_taxonomies();
			$predefined_attributes      = array();
			$taxonomy_terms             = array();
			$wpssw_attributes           = array();
			$wpssw_attributes           = $wpssw_product->get_attributes();

			if ( $attribute_taxonomies ) {
				foreach ( $attribute_taxonomies as $tax ) {
					if ( taxonomy_exists( wc_attribute_taxonomy_name( $tax->attribute_name ) ) ) {
						$attribute_name                                  = strtolower( str_replace( '-', ' ', $tax->attribute_name ) );
						$predefined_attributes[ $tax->attribute_id ]     = $attribute_name;
						$taxonomy_terms[ strtolower( $attribute_name ) ] = array_map( 'strtolower', array_column( get_terms( wc_attribute_taxonomy_name( $tax->attribute_name ), 'hide_empty=0' ), 'name', 'term_id' ) );
					}
				}
			}

			$is_wpml_sitepress_active = WPSSW_Product::wpssw_is_wpml_sitepress_active();

			$wpssw_product_wpml_key = array_search( 'WPML Language Code', $wpssw_woo_selections, true );

			if ( false !== $wpssw_product_wpml_key ) {

				if ( ! empty( $wpssw_data[ $wpssw_product_wpml_key ] ) && $is_wpml_sitepress_active ) {
					global $sitepress;

					$el_language_details = $sitepress->get_element_language_details( $wpssw_productid, 'post_product' );
					$language_code       = trim( $wpssw_data[ $wpssw_product_wpml_key ] );

					if ( empty( $el_language_details ) || ( ! empty( $el_language_details ) && strtolower( $language_code ) !== strtolower( $el_language_details->language_code ) ) ) {
						if ( empty( $el_language_details ) ) {
							$trid_id              = false;
							$source_language_code = null;
						} else {
							$trid_id              = $el_language_details->trid;
							$source_language_code = $el_language_details->source_language_code ? $el_language_details->source_language_code : null;
						}
						// Update the post language info.
						$language_args = array(
							'element_id'           => $wpssw_productid,
							'element_type'         => 'post_product',
							'trid'                 => $trid_id,
							'language_code'        => $language_code,
							'source_language_code' => $source_language_code,
						);
						do_action( 'wpml_set_element_language_details', $language_args );
					}
				}
			}

			$wpssw_product_type_key = array_search( 'Product Type', $wpssw_woo_selections, true );
			if ( false !== $wpssw_product_type_key ) {
				if ( ! empty( $wpssw_data[ $wpssw_product_type_key ] ) && 'variation' !== (string) trim( $wpssw_data[ $wpssw_product_type_key ] ) ) {
					$wpssw_product_type = trim( strtolower( $wpssw_data[ $wpssw_product_type_key ] ) );
				} elseif ( ! empty( $wpssw_data[ $wpssw_product_type_key ] ) && 'variation' === (string) trim( $wpssw_data[ $wpssw_product_type_key ] ) ) {
					$wpssw_product_type = 'variable';
				} else {
					$wpssw_product_type = '';
				}

				wp_set_object_terms( $wpssw_productid, $wpssw_product_type, 'product_type' );
				$classname = WC_Product_Factory::get_product_classname( $wpssw_productid, $wpssw_product_type );

				$wpssw_product = new $classname( $wpssw_productid );

			}

			$wpssw_used_for_variation_key = array_search( 'Use Attributes for Variations', $wpssw_woo_selections, true );
			$used_for_variation           = 'No';
			if ( preg_match( '/variable/', (string) $wpssw_product->get_type() ) ) {

				if ( false !== $wpssw_used_for_variation_key ) {
					$used_for_variation = trim( ucfirst( strtolower( $wpssw_data[ $wpssw_used_for_variation_key ] ) ) );
					if ( ! empty( $used_for_variation ) && ! in_array( $used_for_variation, array( 'Yes', 'No' ), true ) ) {
						$used_for_variation = get_post_meta( $wpssw_productid, 'attributes_used_for_variation', true );
					} elseif ( empty( $used_for_variation ) ) {
						$used_for_variation = 'Yes';
					}
				} else {
					$used_for_variation = get_post_meta( $wpssw_productid, 'attributes_used_for_variation', true );
					if ( ! $used_for_variation ) {
						$used_for_variation = 'Yes';
					}
				}
			}

			$wpssw_attributes_to_use_for_variation_key  = array_search( 'Attributes to use for Variations', $wpssw_woo_selections, true );
			$attributes_list_used_for_variation         = array();
			$attributes_list_used_for_variation_compare = array();
			if ( 'Yes' === (string) $used_for_variation && preg_match( '/variable/', $wpssw_product->get_type() ) ) {

				if ( false !== $wpssw_attributes_to_use_for_variation_key ) {
					$attributes_list = $wpssw_data[ $wpssw_attributes_to_use_for_variation_key ];
					if ( empty( $attributes_list ) ) {
						$attributes       = $wpssw_product->get_attributes();
						$attributes_value = array();
						foreach ( $attributes as $attrkey => $attrvalue ) {
							if ( isset( $attrvalue['variation'] ) && 1 === (int) $attrvalue['variation'] ) {
								if ( $attrvalue->get_id() < 1 && false === strpos( $attrkey, 'pa_' ) ) {
									$attributes_list_used_for_variation[] = $attrvalue->get_name();
								} else {
									$attrkey                              = rawurldecode( $attrkey );
									$attributes_list_used_for_variation[] = wc_attribute_label( $attrkey );
								}
							}
						}
					} else {
						$attributes_list_used_for_variation = array_map( 'trim', explode( '|', $attributes_list ) );
					}
				} else {
					$attributes       = $wpssw_product->get_attributes();
					$attributes_value = array();
					foreach ( $attributes as $attrkey => $attrvalue ) {
						if ( isset( $attrvalue['variation'] ) && 1 === (int) $attrvalue['variation'] ) {
							if ( $attrvalue->get_id() < 1 && false === strpos( $attrkey, 'pa_' ) ) {
								$attributes_list_used_for_variation[] = $attrvalue->get_name();
							} else {
								$attrkey                              = rawurldecode( $attrkey );
								$attributes_list_used_for_variation[] = wc_attribute_label( $attrkey );
							}
						}
					}
				}

				foreach ( $attributes_list_used_for_variation as $attributes_list_used ) {
					$attributes_list_used                         = trim( strtolower( $attributes_list_used ) );
					$attributes_list_used_for_variation_compare[] = str_replace( ' ', '-', $attributes_list_used );
				}
			}

			$wpssw_show_attributes_key = array_search( 'Show Attributes at product page', $wpssw_woo_selections, true );
			$show_attributes           = 'No';

			if ( false !== $wpssw_show_attributes_key ) {
				$show_attributes = trim( ucfirst( strtolower( $wpssw_data[ $wpssw_show_attributes_key ] ) ) );
				if ( ! empty( $show_attributes ) && ! in_array( $show_attributes, array( 'Yes', 'No' ), true ) ) {
					$show_attributes = get_post_meta( $wpssw_productid, 'show_attribultes_at_product_page', true );
				} elseif ( empty( $show_attributes ) ) {
					$show_attributes = 'Yes';
				}
			} else {
				$show_attributes = get_post_meta( $wpssw_productid, 'show_attribultes_at_product_page', true );
				if ( ! $show_attributes ) {
					$show_attributes = 'Yes';
				}
			}

			$attributes_list_visible_at_product_page           = array();
			$attributes_list_visible_at_product_page_compare   = array();
			$wpssw_attributes_list_visible_at_product_page_key = array_search( 'Attributes visible at product page', $wpssw_woo_selections, true );
			if ( 'Yes' === (string) $show_attributes ) {
				if ( false !== $wpssw_attributes_list_visible_at_product_page_key ) {
					$attributes_list = $wpssw_data[ $wpssw_attributes_list_visible_at_product_page_key ];

					if ( empty( $attributes_list ) ) {
						$attributes       = $wpssw_product->get_attributes();
						$attributes_value = array();
						foreach ( $attributes as $attrkey => $attrvalue ) {
							if ( isset( $attrvalue['visible'] ) && 1 === (int) $attrvalue['visible'] ) {
								if ( $attrvalue->get_id() < 1 && false === strpos( $attrkey, 'pa_' ) ) {
									$attributes_list_visible_at_product_page[] = $attrvalue->get_name();
								} else {
									$attrkey                                   = rawurldecode( $attrkey );
									$attributes_list_visible_at_product_page[] = wc_attribute_label( $attrkey );
								}
							}
						}
					} else {
						$attributes_list_visible_at_product_page = array_map( 'trim', explode( '|', $attributes_list ) );
					}
				} else {
					$attributes       = $wpssw_product->get_attributes();
					$attributes_value = array();
					foreach ( $attributes as $attrkey => $attrvalue ) {
						if ( isset( $attrvalue['visible'] ) && 1 === (int) $attrvalue['visible'] ) {
							if ( $attrvalue->get_id() < 1 && false === strpos( $attrkey, 'pa_' ) ) {
								$attributes_list_visible_at_product_page[] = $attrvalue->get_name();
							} else {
								$attrkey                                   = rawurldecode( $attrkey );
								$attributes_list_visible_at_product_page[] = wc_attribute_label( $attrkey );
							}
						}
					}
				}

				foreach ( $attributes_list_visible_at_product_page as $attributes_list_used ) {
					$attributes_list_used                              = trim( strtolower( $attributes_list_used ) );
					$attributes_list_visible_at_product_page_compare[] = str_replace( ' ', '-', $attributes_list_used );
				}
			}

			if ( false !== $wpssw_used_for_variation_key || false !== $wpssw_attributes_to_use_for_variation_key || false !== $wpssw_show_attributes_key || false !== $wpssw_attributes_list_visible_at_product_page_key ) {
				foreach ( $wpssw_attribute_taxonomies as $attribute_tax ) {
					if ( ! in_array( $attribute_tax, $wpssw_woo_selections, true ) ) {
						$attr_name_key = '';
						if ( in_array( strtolower( $attribute_tax ), $predefined_attributes, true ) ) {
							$attr_key = 'pa_' . str_replace( ' ', '-', strtolower( $attribute_tax ) );

							$attr_name_key = str_replace( ' ', '-', strtolower( wc_attribute_label( rawurldecode( $attr_key ) ) ) );

						} else {
							$attr_key = str_replace( ' ', '-', strtolower( $attribute_tax ) );
						}

						$attr_key = strtolower( rawurlencode( $attr_key ) );

						if ( array_key_exists( $attr_key, $wpssw_attributes ) ) {
							$attribute_object = $wpssw_attributes[ $attr_key ];
							if ( ! in_array( strtolower( $attribute_tax ), $predefined_attributes, true ) ) {
								$attr_name_key = str_replace( ' ', '-', strtolower( $attribute_object->get_name() ) );
							}
							if ( 'Yes' === (string) $used_for_variation && in_array( $attr_name_key, $attributes_list_used_for_variation_compare, true ) ) {
								$attribute_object->set_variation( true );
							} else {
								$attribute_object->set_variation( false );
							}
							if ( 'Yes' === (string) $show_attributes && in_array( $attr_name_key, $attributes_list_visible_at_product_page_compare, true ) ) {
								$attribute_object->set_visible( true );
							} else {
								$attribute_object->set_visible( false );
							}
							$wpssw_attributes[ $attr_key ] = $attribute_object;
						}
					}
				}
			}

			$exclude_metakeys = array( 'post_name', 'post_link', '_dimensions', '_downloadable_file_names', 'type', 'product_image_preview', 'attributes_list_used_for_variation', 'attributes_list_visible_at_product_page', 'product_category_ids' );

			$not_numeric_metakey_val_arr = array( '_sale_price', '_regular_price', '_weight', '_height', '_width', '_length', '_shipping_class_id', '_sku' );

			foreach ( $wpssw_woo_selections as $wpssw_key => $wpssw_header ) {
				if ( in_array( $wpssw_header, $wpssw_crud_operation, true ) ) {
					continue;
				}
				if ( in_array( $wpssw_header, $wpssw_default_header, true ) ) {
					$wpssw_meta_key           = array_search( $wpssw_header, $wpssw_default_header, true );
					$wpssw_data[ $wpssw_key ] = isset( $wpssw_data[ $wpssw_key ] ) ? $wpssw_data[ $wpssw_key ] : '';

					if ( in_array( $wpssw_meta_key, $exclude_metakeys, true ) || ( 'post_title' === (string) $wpssw_meta_key && empty( $wpssw_data[ $wpssw_key ] ) ) ) {
						continue;
					}
					if ( 'simple' !== (string) $wpssw_product->get_type() && ( '_downloadable_files' === (string) $wpssw_meta_key || '_download_limit' === (string) $wpssw_meta_key || '_download_expiry' === (string) $wpssw_meta_key ) ) {
						continue;
					}
					if ( 'post_status' === (string) $wpssw_meta_key ) {
						if ( ! empty( $wpssw_data[ $wpssw_key ] ) ) {
							$wpssw_updated_array['post_status'] = $wpssw_data[ $wpssw_key ];
						} else {
							$wpssw_updated_array['post_status'] = 'publish';
						}
						continue;
					}

					if ( '_sale_price_dates_from' === (string) $wpssw_meta_key || '_sale_price_dates_to' === (string) $wpssw_meta_key ) {
						if ( ! in_array( $wpssw_product->get_type(), $wpssw_exclude_product_type, true ) ) {
							$val = isset( $wpssw_data[ $wpssw_key ] ) ? strtotime( $wpssw_data[ $wpssw_key ] ) : '';
							self::wpssw_update_post_meta( $wpssw_productid, $wpssw_meta_key, $val );
						}
						continue;
					}
					if ( '_sale_price' === (string) $wpssw_meta_key || '_regular_price' === (string) $wpssw_meta_key ) {
						if ( ! in_array( $wpssw_product->get_type(), $wpssw_exclude_product_type, true ) ) {
							$wpssw_regular_price_key = array_search( 'Product Regular Price', $wpssw_woo_selections, true );
							$wpssw_sale_price_key    = array_search( 'Product Sale Price', $wpssw_woo_selections, true );
							$wpssw_regular_price     = '';
								$wpssw_sale_price    = '';
							if ( false !== $wpssw_regular_price_key || false !== $wpssw_sale_price_key ) {
								if ( false !== $wpssw_sale_price_key ) {
									$wpssw_sale_price = wc_format_decimal( $wpssw_data[ $wpssw_sale_price_key ] );

									if ( $wpssw_sale_price < 0 ) {
										$wpssw_sale_price = '';
										continue;
									} elseif ( 0 === (int) $wpssw_sale_price ) {
										$wpssw_sale_price = '';
										if ( false !== $wpssw_regular_price_key ) {
											$wpssw_regular_price = wc_format_decimal( $wpssw_data[ $wpssw_regular_price_key ] );
											self::wpssw_update_post_meta( $wpssw_productid, '_regular_price', $wpssw_regular_price );
											self::wpssw_update_post_meta( $wpssw_productid, '_price', $wpssw_regular_price );
										} else {
											$wpssw_value = wc_format_decimal( $wpssw_product->get_regular_price() );
											self::wpssw_update_post_meta( $wpssw_productid, '_regular_price', $wpssw_value );
											self::wpssw_update_post_meta( $wpssw_productid, '_price', $wpssw_value );
										}
										self::wpssw_update_post_meta( $wpssw_productid, '_sale_price', '' );
										continue;
									}
								}
								if ( false !== $wpssw_regular_price_key ) {
									$wpssw_regular_price = wc_format_decimal( $wpssw_data[ $wpssw_regular_price_key ] );
									if ( (float) $wpssw_regular_price < 0 ) {

										$wpssw_regular_price = '';
										continue;
									} elseif ( 0 === (int) $wpssw_regular_price ) {

										$wpssw_regular_price = '';
										self::wpssw_update_post_meta( $wpssw_productid, '_sale_price', '' );
										self::wpssw_update_post_meta( $wpssw_productid, '_regular_price', '' );
										self::wpssw_update_post_meta( $wpssw_productid, '_price', '' );
										continue;
									}
								}
								if ( ! empty( $wpssw_sale_price ) && ! empty( $wpssw_regular_price ) ) {

									if ( $wpssw_sale_price < $wpssw_regular_price ) {

										self::wpssw_update_post_meta( $wpssw_productid, '_sale_price', $wpssw_sale_price );
										self::wpssw_update_post_meta( $wpssw_productid, '_regular_price', $wpssw_regular_price );
										self::wpssw_update_post_meta( $wpssw_productid, '_price', $wpssw_sale_price );
										continue;
									}
									$wpssw_value = $wpssw_product->get_sale_price();
									if ( ! empty( $wpssw_value ) && $wpssw_value < $wpssw_regular_price ) {

										self::wpssw_update_post_meta( $wpssw_productid, '_regular_price', $wpssw_regular_price );
									} elseif ( empty( $wpssw_value ) ) {

										self::wpssw_update_post_meta( $wpssw_productid, '_regular_price', $wpssw_regular_price );
										self::wpssw_update_post_meta( $wpssw_productid, '_price', $wpssw_regular_price );
									}
									continue;
								} else {
									if ( ! empty( $wpssw_sale_price ) ) {
										$wpssw_value = wc_format_decimal( $wpssw_product->get_regular_price() );
										if ( ! empty( $wpssw_value ) && $wpssw_sale_price < $wpssw_value ) {

											self::wpssw_update_post_meta( $wpssw_productid, '_sale_price', $wpssw_sale_price );
											self::wpssw_update_post_meta( $wpssw_productid, '_price', $wpssw_sale_price );
											continue;
										}
									} elseif ( ! empty( $wpssw_regular_price ) ) {

										$wpssw_value = wc_format_decimal( $wpssw_product->get_sale_price() );
										if ( ! empty( $wpssw_value ) && $wpssw_value < $wpssw_regular_price ) {

											self::wpssw_update_post_meta( $wpssw_productid, '_regular_price', $wpssw_regular_price );
											continue;
										} elseif ( empty( $wpssw_value ) ) {
											self::wpssw_update_post_meta( $wpssw_productid, '_regular_price', $wpssw_regular_price );
											self::wpssw_update_post_meta( $wpssw_productid, '_price', $wpssw_regular_price );
										}
										continue;
									}
								}
								continue;
							}
						}
						continue;
					}
					if ( isset( $wpssw_data[ $wpssw_key ] ) && $wpssw_meta_key ) {
						if ( is_numeric( $wpssw_data[ $wpssw_key ] ) && ! in_array( $wpssw_meta_key, $not_numeric_metakey_val_arr, true ) ) {
							$wpssw_data[ $wpssw_key ] = (int) $wpssw_data[ $wpssw_key ];
						}
					}
					if ( 'raw_image' === (string) $wpssw_meta_key ) {
						if ( ! empty( $wpssw_data[ $wpssw_key ] ) ) {
							$wpssw_images = explode( '|', $wpssw_data[ $wpssw_key ] );
						} else {
							$wpssw_images = array();
						}
						$wpssw_imagedata['raw_image'] = array_shift( $wpssw_images );
						/*if ( ! empty( $wpssw_images ) ) {
							$wpssw_imagedata['raw_gallery_image'] = $wpssw_images;
						}*/
						self::wpssw_set_image_data( $wpssw_productid, $wpssw_imagedata );
						continue;
					}
					if ( 'raw_gallery_image' === (string) $wpssw_meta_key ) {
						if ( ! empty( $wpssw_data[ $wpssw_key ] ) ) {
							$wpssw_images = explode( '|', $wpssw_data[ $wpssw_key ] );
						} else {
							$wpssw_images = array();
						}
						//$wpssw_imagedata['raw_image'] = array_shift( $wpssw_images );
						if ( ! empty( $wpssw_images ) ) {
							$wpssw_imagedata['raw_gallery_image'] = $wpssw_images;
							self::wpssw_set_image_data( $wpssw_productid, $wpssw_imagedata );
						}else{
							self::wpssw_set_image_data( $wpssw_productid, array() );
						}
						continue;
					}
					if ( 'category_ids' === (string) $wpssw_meta_key ) {
						if ( ! empty( $wpssw_data[ $wpssw_key ] ) ) {
							$wpssw_categories = explode( ',', $wpssw_data[ $wpssw_key ] );
						} else {
							$wpssw_categories = array();
						}
						wp_set_object_terms( $wpssw_productid, $wpssw_categories, 'product_cat' );
						continue;
					}
					if ( 'tag_ids' === (string) $wpssw_meta_key ) {
						if ( ! empty( $wpssw_data[ $wpssw_key ] ) ) {
							$wpssw_tag = explode( ',', $wpssw_data[ $wpssw_key ] );
						} else {
							$wpssw_tag = array();
						}
						wp_set_object_terms( $wpssw_productid, $wpssw_tag, 'product_tag', false );
						continue;
					}
					if ( 'post_date' === (string) $wpssw_meta_key ) {

						if ( ! empty( $wpssw_data[ $wpssw_key ] ) ) {

							$wpssw_created_date                   = date_create( $wpssw_data[ $wpssw_key ] );
							$wpssw_data[ $wpssw_key ]             = date_format( $wpssw_created_date, 'Y-m-d H:i:s' );
							$wpssw_updated_array['post_date_gmt'] = $wpssw_data[ $wpssw_key ];

						} else {
							$wpssw_data[ $wpssw_key ] = '';
						}
						continue;
					}

					if ( '_children' === (string) $wpssw_meta_key ) {
						if ( ! empty( $wpssw_data[ $wpssw_key ] ) ) {
							$wpssw_childrens_array = explode( ',', $wpssw_data[ $wpssw_key ] );
						} else {
							$wpssw_childrens_array = array();
						}
						self::wpssw_update_post_meta( $wpssw_productid, $wpssw_meta_key, $wpssw_childrens_array );
						continue;
					}
					if ( '_downloadable_files' === (string) $wpssw_meta_key ) {

						if ( ! empty( $wpssw_data[ $wpssw_key ] ) ) {

							$wpssw_downloadable_filenames = array();
							$wpssw_downloadable_files     = array();
							if ( false !== strpos( $wpssw_data[ $wpssw_key ], '|' ) ) {
								$wpssw_downloadable_files = explode( '|', $wpssw_data[ $wpssw_key ] );
							} else {
								$wpssw_downloadable_files = explode( ',', $wpssw_data[ $wpssw_key ] );
							}

							$wpssw_downloadable_files        = array_map( 'trim', array_filter( $wpssw_downloadable_files ) );
							$wpssw_downloadable_filename_key = array_search( 'Product Downloadable File Names', $wpssw_woo_selections, true );
							if ( false !== $wpssw_downloadable_filename_key ) {
								$wpssw_downloadable_filenames = isset( $wpssw_data[ $wpssw_downloadable_filename_key ] ) ? explode( ',', $wpssw_data[ $wpssw_downloadable_filename_key ] ) : array();
							}
							$files_data = array();
							foreach ( $wpssw_product->get_downloads() as $downloads ) {
								$file_data['url']  = $downloads->get_file();
								$file_data['name'] = $downloads->get_name();
								$file_data['id']   = $downloads->get_id();
								$files_data[]      = $file_data;
							}
							$wpssw_downloadable_files_count = count( $wpssw_downloadable_files );

							for ( $i = 0;$i < $wpssw_downloadable_files_count;$i++ ) {

								if ( in_array( $wpssw_downloadable_files[ $i ], array_column( $files_data, 'url' ), true ) ) {
									$key  = array_search( $wpssw_downloadable_files[ $i ], array_column( $files_data, 'url' ), true );
									$name = isset( $files_data[ $key ]['name'] ) ? $files_data[ $key ]['name'] : '';
									$id   = isset( $files_data[ $key ]['id'] ) ? $files_data[ $key ]['id'] : '';

									$download = array();
									if ( isset( $wpssw_downloadable_filenames[ $i ] ) && $name !== $wpssw_downloadable_filenames[ $i ] ) {
										$download['name'] = $wpssw_downloadable_filenames[ $i ];
									} else {
										$download['name'] = $name;
									}
									$download['download_id'] = $id;
									$download['file']        = $wpssw_downloadable_files[ $i ];
									$downloads_array[]       = $download;
								} else {
									$download                = array();
									$download['download_id'] = '';
									$name                    = '';
									if ( isset( $wpssw_downloadable_filenames[ $i ] ) && ! empty( $wpssw_downloadable_filenames[ $i ] ) ) {
										$name = $wpssw_downloadable_filenames[ $i ];
									} else {
										$pathinfo = pathinfo( $wpssw_downloadable_files[ $i ] );
										$name     = isset( $pathinfo['filename'] ) ? $pathinfo['filename'] : '';
									}
									$download['name']  = $name;
									$download['file']  = $wpssw_downloadable_files[ $i ];
									$downloads_array[] = $download;
								}
							}
							if ( ! empty( $downloads_array ) ) {
								$wpssw_product->set_downloads( $downloads_array );
							}
						} else {
							$downloads_array = array();
							$wpssw_product->set_downloads( $downloads_array );
						}

						continue;
					}
					if ( '_download_limit' === (string) $wpssw_meta_key ) {
						if ( ! empty( $wpssw_data[ $wpssw_key ] ) && 'unlimited' === trim( strtolower( $wpssw_data[ $wpssw_key ] ) ) ) {
							$wpssw_data[ $wpssw_key ] = -1;
						}
					}
					if ( '_download_expiry' === (string) $wpssw_meta_key ) {
						if ( ! empty( $wpssw_data[ $wpssw_key ] ) && 'never' === trim( strtolower( $wpssw_data[ $wpssw_key ] ) ) ) {
							$wpssw_data[ $wpssw_key ] = -1;
						}
					}
					if ( 'attributes_used_for_variation' === (string) $wpssw_meta_key ) {
						$wpssw_data[ $wpssw_key ] = $used_for_variation;
					}
					if ( 'show_attribultes_at_product_page' === (string) $wpssw_meta_key ) {
						$wpssw_data[ $wpssw_key ] = $show_attributes;
					}

					if ( 'catalog_visibility' === (string) $wpssw_meta_key ) {
						$set_catalog_visibility = '';
						if ( ! empty( $wpssw_data[ $wpssw_key ] ) ) {
							$all_visibilities = wc_get_product_visibility_options();
							$catalog_val      = trim( strtolower( $wpssw_data[ $wpssw_key ] ) );
							if ( in_array( $catalog_val, array_map( 'strtolower', $all_visibilities ), true ) ) {
								if ( 'hidden' === (string) $catalog_val ) {
									// Set product hidden.
									$set_catalog_visibility = 'hidden';
								} elseif ( 'search results only' === (string) $catalog_val ) {
									// Set product visible in search.
									$set_catalog_visibility = 'search';
								} elseif ( 'shop only' === (string) $catalog_val ) {
									// Set product visible in catalog.
									$set_catalog_visibility = 'catalog';
								} else {
									$set_catalog_visibility = 'visible';
								}
							} elseif ( array_key_exists( $catalog_val, $all_visibilities ) ) {
								if ( 'search' === (string) $catalog_val ) {
									// Set product visible in search.
									$set_catalog_visibility = 'search';
								} elseif ( 'catalog' === (string) $catalog_val ) {
									// Set product visible in catalog.
									$set_catalog_visibility = 'catalog';
								} else {
									$set_catalog_visibility = 'visible';
								}
							} else {
								$set_catalog_visibility = '';
							}
						} else {
							$set_catalog_visibility = 'visible';
						}
						if ( $set_catalog_visibility ) {
							$wpssw_product->set_catalog_visibility( $set_catalog_visibility );
						}
						continue;
					}
					if ( 'featured' === (string) $wpssw_meta_key ) {
						$is_featured = '';
						if ( 'yes' === (string) trim( strtolower( $wpssw_data[ $wpssw_key ] ) ) ) {
							$is_featured = 1;
						}
						$wpssw_product->set_featured( $is_featured );
						continue;
					}
					if ( '_shipping_class_id' === (string) $wpssw_meta_key ) {
						$wpssw_product->set_shipping_class_id( trim( $wpssw_data[ $wpssw_key ] ) );
						continue;
					}
					if ( '_menu_order' === (string) $wpssw_meta_key ) {
						$menu_order = 0;
						if ( ! empty( $wpssw_data[ $wpssw_key ] ) && (int) $wpssw_data[ $wpssw_key ] > 0 ) {
							$menu_order = (int) $wpssw_data[ $wpssw_key ];
						}
						$wpssw_product->set_menu_order( $menu_order );
						continue;
					}
					if ( ! is_numeric( $wpssw_meta_key ) ) {
						$wpssw_updated_array[ $wpssw_meta_key ] = $wpssw_data[ $wpssw_key ];
						self::wpssw_update_post_meta( $wpssw_productid, $wpssw_meta_key, $wpssw_data[ $wpssw_key ] );
					}
				}
				if ( in_array( $wpssw_header, $wpssw_attribute_taxonomies, true ) ) {
					$attribute_name     = strtolower( $wpssw_header );
					$attribute_name_key = str_replace( ' ', '-', $attribute_name );

					if ( in_array( $attribute_name, $predefined_attributes, true ) ) {
						$attribute_key = 'pa_' . $attribute_name_key;
					} else {
						$attribute_key = $attribute_name_key;
					}

					$attribute_encodedkey = strtolower( rawurlencode( $attribute_key ) );

					if ( isset( $wpssw_data[ $wpssw_key ] ) && ! empty( $wpssw_data[ $wpssw_key ] ) ) {
						$attribute_object = new WC_Product_Attribute();

						if ( in_array( $attribute_name, $predefined_attributes, true ) ) {

							$options = array();
							$options = explode( '|', strtolower( $wpssw_data[ $wpssw_key ] ) );
							$options = array_filter( array_map( 'trim', $options ) );

							$option_ids = array();
							foreach ( $options as $option ) {
								if ( in_array( trim( $option ), $taxonomy_terms[ $attribute_name ], true ) ) {
									$option_ids[]   = array_search( trim( $option ), $taxonomy_terms[ $attribute_name ], true );
									$option_names[] = trim( $option );
								}
							}
							$attribute_object->set_id( array_search( $attribute_name, $predefined_attributes, true ) );
							$attribute_object->set_name( $attribute_key );

							$attribute_object->set_options( $option_ids );

							$existing_options = array();
							if ( array_key_exists( $attribute_encodedkey, $wpssw_attributes ) ) {
								$existing_options = $wpssw_attributes[ $attribute_encodedkey ]['options'];
							}

							sort( $option_ids );
							sort( $existing_options );

							$diff_option_value = array();
							if ( $existing_options !== $option_ids ) {
								$different_options = array_diff( $existing_options, $option_ids );
								foreach ( $different_options as $diff_optionid ) {
									$term                                = '';
									$term                                = get_term( $diff_optionid, $attribute_key );
									$diff_option_value[ $attribute_key ] = $term->slug;
								}
							}
							$diff_option_value = array_filter( $diff_option_value );

							if ( ! empty( $wpssw_product->get_children() ) && 'grouped' !== (string) $wpssw_product->get_type() && ! empty( $diff_option_value ) ) {
								$wpssw_childrens = array();
								$wpssw_childrens = $wpssw_product->get_children();
								foreach ( $wpssw_childrens as $wpssw_childid ) {
									$wpssw_child              = wc_get_product( $wpssw_childid );
									$wpssw_selected_variation = array_map( 'trim', $wpssw_child->get_variation_attributes() );
									foreach ( $diff_option_value as $diff_optionkey => $diff_optionslug ) {
										$diff_optionkey  = strtolower( rawurlencode( $diff_optionkey ) );
										$diff_optionslug = strtolower( rawurlencode( $diff_optionslug ) );

										if ( isset( $wpssw_selected_variation[ 'attribute_' . $diff_optionkey ] ) && $diff_optionslug === $wpssw_selected_variation[ 'attribute_' . $diff_optionkey ] ) {
											self::wpssw_update_post_meta( $wpssw_childid, 'attribute_' . $diff_optionkey, '' );
										}
									}
								}
							}
						} else {
							$attribute_object->set_name( $wpssw_header );
							$attribute_object->set_options( array( $wpssw_data[ $wpssw_key ] ) );

							if ( ! empty( $wpssw_product->get_children() ) && 'grouped' !== (string) $wpssw_product->get_type() ) {
								$wpssw_childrens = array();
								$wpssw_childrens = $wpssw_product->get_children();
								foreach ( $wpssw_childrens as $wpssw_childid ) {
									$wpssw_child              = wc_get_product( $wpssw_childid );
									$wpssw_selected_variation = array_map( 'trim', $wpssw_child->get_variation_attributes() );
									$options                  = array();
									$options                  = explode( '|', strtolower( $wpssw_data[ $wpssw_key ] ) );
									$options                  = array_filter( array_map( 'trim', $options ) );

									if ( isset( $wpssw_selected_variation[ 'attribute_' . $attribute_encodedkey ] ) && ! in_array( strtolower( $wpssw_selected_variation[ 'attribute_' . $attribute_encodedkey ] ), $options, true ) ) {
										self::wpssw_update_post_meta( $wpssw_childid, 'attribute_' . $attribute_encodedkey, '' );
									}
								}
							}
						}
						if ( $attribute_object->get_id() < 1 && false === strpos( $attribute_key, 'pa_' ) ) {
							$attr_name_key = str_replace( ' ', '-', strtolower( $attribute_object->get_name() ) );
						} else {
							$attr_name_key = str_replace( ' ', '-', strtolower( wc_attribute_label( rawurldecode( $attribute_key ) ) ) );

						}
						if ( 'Yes' === (string) $used_for_variation && in_array( $attr_name_key, $attributes_list_used_for_variation_compare, true ) ) {
							$attribute_object->set_variation( true );
						} else {
							$attribute_object->set_variation( false );
						}

						if ( 'Yes' === (string) $show_attributes && in_array( $attr_name_key, $attributes_list_visible_at_product_page_compare, true ) ) {
							$attribute_object->set_visible( true );
						} else {
							$attribute_object->set_visible( false );
						}

						$wpssw_attributes[ $attribute_encodedkey ] = $attribute_object;
					} else {
						$wpssw_attributes[ $attribute_encodedkey ] = array();
						if ( ! empty( $wpssw_product->get_children() ) && 'grouped' !== (string) $wpssw_product->get_type() ) {
							$wpssw_childrens = array();
							$wpssw_childrens = $wpssw_product->get_children();
							foreach ( $wpssw_childrens as $wpssw_childid ) {
								self::wpssw_update_post_meta( $wpssw_childid, 'attribute_' . $attribute_encodedkey, '' );
							}
						}
					}
				}
				if ( isset( $wpssw_wooproduct_headers['WPSSW_WooCommerce_Product_Brand'] ) && in_array( $wpssw_header, $wpssw_wooproduct_headers['WPSSW_WooCommerce_Product_Brand'], true ) ) {
					if ( ! empty( $wpssw_data[ $wpssw_key ] ) ) {
						$wpssw_brands = explode( ',', $wpssw_data[ $wpssw_key ] );
					} else {
						$wpssw_brands = array();
					}
					wp_set_object_terms( $wpssw_productid, $wpssw_brands, 'product_brand' );
					continue;
				}
				if ( isset( $wpssw_wooproduct_headers['WPSSW_Alg_WC_Product_Notes'] ) && in_array( $wpssw_header, $wpssw_wooproduct_headers['WPSSW_Alg_WC_Product_Notes'], true ) ) {
					if ( ! class_exists( 'Alg_WC_Product_Notes' ) ) {
						continue;
					}
					$wpssw_meta_key    = array_search( $wpssw_header, $wpssw_wooproduct_headers['WPSSW_Alg_WC_Product_Notes'], true );
					$private_or_public = '';
					if ( '_alg_wc_internal_product_note' === (string) $wpssw_meta_key ) {
						$private_or_public = 'private';
					} elseif ( '_alg_wc_public_product_note' === (string) $wpssw_meta_key ) {
						$private_or_public = 'public';
					} else {
						continue;
					}
					$notes = array();
					if ( ! empty( $wpssw_data[ $wpssw_key ] ) ) {
						$notes = explode( ' , ', $wpssw_data[ $wpssw_key ] );
					}
					$notes_class = new Alg_WC_Product_Notes_Core();
					$notes_class->set_product_notes( $notes, $private_or_public, $wpssw_productid );

					continue;
				}
				if ( in_array( $wpssw_header, $wpssw_custom_import_product_headers, true ) ) {
					do_action( 'wpssw_custom_headers_for_product_doimport', $wpssw_productid, $wpssw_header, $wpssw_data );
					continue;
				}

				if ( in_array( $wpssw_header, $wpssw_acf_headers, true ) ) {
					$wpssw_meta_val = isset( $wpssw_data[ $wpssw_key ] ) ? $wpssw_data[ $wpssw_key ] : '';
					WPSSW_ACF::wpssw_import_acf_fields( $wpssw_productid, $wpssw_header, $wpssw_meta_val, $wpssw_data );
					continue;
				}
				if ( in_array( $wpssw_header, $wpssw_jetengine_headers, true ) ) {
					$wpssw_meta_val = isset( $wpssw_data[ $wpssw_key ] ) ? $wpssw_data[ $wpssw_key ] : '';
					WPSSW_Jet_Engine::wpssw_import_jetengine_fields( $wpssw_productid, $wpssw_header, $wpssw_meta_val, $wpssw_data );
					continue;
				}
				if ( in_array( $wpssw_header, $wpssw_addon_array, true ) ) {
					$wpssw_meta_key = array_search( $wpssw_header, $wpssw_addon_array, true );
					if ( preg_match( '/variable/', (string) $wpssw_product->get_type() ) && ! empty( $wpssw_meta_key ) && array_key_exists( $wpssw_meta_key, $wpssw_variation_product_import_headers ) ) {
						continue;
					}

					if ( ! empty( $wpssw_meta_key ) ) {
						$wpssw_meta_val = isset( $wpssw_data[ $wpssw_key ] ) ? $wpssw_data[ $wpssw_key ] : '';
						if ( '_yoast_wpseo_title' === (string) $wpssw_meta_key && class_exists( 'WPSEO_Admin' ) ) {
							$post        = get_post( $wpssw_productid );
							$wpssw_value = WPSEO_Meta::get_value( 'title', $wpssw_productid );
							if ( empty( $wpssw_value ) ) {
								$wpseo_titles        = get_option( 'wpseo_titles', array() );
								$wpssw_default_value = isset( $wpseo_titles[ 'title-' . $post->post_type ] ) ? $wpseo_titles[ 'title-' . $post->post_type ] : $post->post_title;
							}
							$replacer = new WPSEO_Replace_Vars();
							$val      = $replacer->replace( $wpssw_value, $post );
							if ( trim( $wpssw_meta_val ) === $val ) {
								$wpssw_meta_val = $wpssw_value;
							}
						}
						self::wpssw_update_post_meta( $wpssw_productid, $wpssw_meta_key, $wpssw_meta_val );
					}
					continue;
				}
			}
			$wpssw_updated_array['ID'] = $wpssw_productid;
			wp_update_post( $wpssw_updated_array );
			$wpssw_product->set_attributes( $wpssw_attributes );
			remove_action( 'woocommerce_update_product', 'WPSSW_Product::wpssw_woocommerce_update_product', 99, 2 );
			$wpssw_product->save();

		}
		/**
		 * Update imported product
		 *
		 * @param int   $wpssw_productid product id.
		 * @param array $wpssw_data product data array.
		 */
		public static function wpssw_update_variation_product( $wpssw_productid, $wpssw_data ) {
			if ( ! self::$instance_api->checkcredenatials() ) {
				return;
			}
			if ( (int) $wpssw_productid < 1 ) {
				return;
			}
			$wpssw_woo_selections = stripslashes_deep( parent::wpssw_option( 'wpssw_woo_product_headers' ) );

			if ( ! $wpssw_woo_selections ) {
				return;
			}
			array_unshift( $wpssw_woo_selections, 'Product Id', 'Product Variation Id' );
			$wpssw_include = new WPSSW_Include_Action();
			$wpssw_include->wpssw_include_product_compatibility_files();
			$wpssw_wooproduct_headers = apply_filters( 'wpsyncsheets_product_headers', array() );
			$wpssw_default_header     = $wpssw_wooproduct_headers['WPSSW_Default_Headers']['variable'];

			$wpssw_only_variation_product_import_headers = apply_filters( 'wpssw_custom_headers_for_variation_product_import', array() );
			$wpssw_variation_product_import_headers      = apply_filters( 'wpssw_headers_for_variation_product_import', array() );
			$wpssw_variation_product_import_headers      = $wpssw_variation_product_import_headers + $wpssw_only_variation_product_import_headers;
			$wpssw_variation_product_import_headers      = array_filter( array_unique( $wpssw_variation_product_import_headers ) );

			foreach ( $wpssw_variation_product_import_headers as $addon_key => $addon_value ) {
				if ( is_numeric( $addon_key ) ) {
					unset( $wpssw_variation_product_import_headers[ $addon_key ] );
				}
			}

			$wpssw_variation_attribute_header = $wpssw_wooproduct_headers['WPSSW_Default_Headers']['variation'];
			$wpssw_attribute_taxonomies       = WPSSW_Product::wpssw_get_all_attributes();
			$wpssw_updated_array              = array();
			$wpssw_attributes                 = array();
			$wpssw_crud_operation             = array( 'Insert', 'Update', 'Delete' );
			$wpssw_product                    = wc_get_product( $wpssw_productid );
			$wpssw_parentproduct              = wc_get_product( $wpssw_product->parent_id );

			$wpssw_parentattributes = $wpssw_parentproduct->get_attributes();

			$attribute_taxonomies           = wc_get_attribute_taxonomies();
			$wpssw_parentattributes_options = array();
			$taxonomy_terms                 = array();
			if ( $attribute_taxonomies ) {
				foreach ( $attribute_taxonomies as $tax ) {
					if ( taxonomy_exists( wc_attribute_taxonomy_name( $tax->attribute_name ) ) ) {
						$taxonomy_terms[ strtolower( $tax->attribute_name ) ] = array_column( get_terms( wc_attribute_taxonomy_name( $tax->attribute_name ), 'hide_empty=0' ), 'name', 'term_id' );
					}
				}
			}

			foreach ( $wpssw_parentattributes as $att_key => $attr ) {
				$att_key = rawurldecode( $att_key );
				if ( $attr['id'] > 0 ) {
					$position = strpos( $att_key, 'pa_' );
					$pos      = strpos( $att_key, 'a_' );
					if ( false !== $position && 1 === $pos ) {
						$name    = substr( $att_key, strlen( 'pa_' ) );
						$options = array();
						if ( array_key_exists( $name, $taxonomy_terms ) ) {
							foreach ( $attr['options'] as $option ) {
								$options[ $option ] = $taxonomy_terms[ $name ][ $option ];
							}
							$attr['options'] = $options;
						}
					}
				}
				$wpssw_parentattributes_options[ $att_key ] = $attr['options'];
			}

			$not_numeric_metakey_val_arr = array( '_sale_price', '_regular_price', '_weight', 'dimensions', '_shipping_class_id' );

			$is_wpml_sitepress_active = WPSSW_Product::wpssw_is_wpml_sitepress_active();

			$wpssw_product_wpml_key = array_search( 'WPML Language Code', $wpssw_woo_selections, true );

			if ( false !== $wpssw_product_wpml_key ) {

				if ( ! empty( $wpssw_data[ $wpssw_product_wpml_key ] ) && $is_wpml_sitepress_active ) {
					global $sitepress;

					$el_language_details = $sitepress->get_element_language_details( $wpssw_productid, 'post_product' );
					$language_code       = trim( $wpssw_data[ $wpssw_product_wpml_key ] );

					if ( empty( $el_language_details ) || ( ! empty( $el_language_details ) && strtolower( $language_code ) !== strtolower( $el_language_details->language_code ) ) ) {
						if ( empty( $el_language_details ) ) {
							$trid_id              = false;
							$source_language_code = null;
						} else {
							$trid_id              = $el_language_details->trid;
							$source_language_code = $el_language_details->source_language_code ? $el_language_details->source_language_code : null;
						}
						// Update the post language info.
						$language_args = array(
							'element_id'           => $wpssw_productid,
							'element_type'         => 'post_product',
							'trid'                 => $trid_id,
							'language_code'        => $language_code,
							'source_language_code' => $source_language_code,
						);
						do_action( 'wpml_set_element_language_details', $language_args );
					}
				}
			}

			foreach ( $wpssw_woo_selections as $wpssw_key => $wpssw_header ) {
				if ( in_array( $wpssw_header, $wpssw_crud_operation, true ) ) {
					continue;
				}
				if ( in_array( $wpssw_header, $wpssw_default_header, true ) ) {
					$wpssw_meta_key = array_search( $wpssw_header, $wpssw_default_header, true );
					if ( 'variation_image_preview' === (string) $wpssw_meta_key ) {
						continue;
					}
					if ( '_sale_price_dates_from' === (string) $wpssw_meta_key || '_sale_price_dates_to' === (string) $wpssw_meta_key ) {
						$val = isset( $wpssw_data[ $wpssw_key ] ) ? strtotime( $wpssw_data[ $wpssw_key ] ) : '';
						self::wpssw_update_post_meta( $wpssw_productid, $wpssw_meta_key, $val );
						continue;
					}
					if ( '_sale_price' === (string) $wpssw_meta_key || '_regular_price' === (string) $wpssw_meta_key ) {

						$wpssw_regular_price_key = array_search( 'Product Variation Regular Price', $wpssw_woo_selections, true );
						$wpssw_sale_price_key    = array_search( 'Product Variation Sale Price', $wpssw_woo_selections, true );
						$wpssw_regular_price     = '';
							$wpssw_sale_price    = '';
						if ( false !== $wpssw_regular_price_key || false !== $wpssw_sale_price_key ) {
							if ( false !== $wpssw_sale_price_key ) {
								$wpssw_sale_price = $wpssw_data[ $wpssw_sale_price_key ];
								if ( (float) $wpssw_sale_price < 0 ) {
									$wpssw_sale_price = '';
									continue;
								} elseif ( 0 === (int) $wpssw_sale_price ) {
									$wpssw_sale_price = '';
									if ( false !== $wpssw_regular_price_key ) {
										$wpssw_regular_price = wc_format_decimal( $wpssw_data[ $wpssw_regular_price_key ] );
										self::wpssw_update_post_meta( $wpssw_productid, '_regular_price', $wpssw_regular_price );
										self::wpssw_update_post_meta( $wpssw_productid, '_price', $wpssw_regular_price );
									} else {
										$wpssw_value = wc_format_decimal( $wpssw_product->get_regular_price() );
										self::wpssw_update_post_meta( $wpssw_productid, '_regular_price', $wpssw_value );
										self::wpssw_update_post_meta( $wpssw_productid, '_price', $wpssw_value );
									}
									self::wpssw_update_post_meta( $wpssw_productid, '_sale_price', '' );
									continue;
								}
							}
							if ( false !== $wpssw_regular_price_key ) {
								$wpssw_regular_price = wc_format_decimal( $wpssw_data[ $wpssw_regular_price_key ] );
								if ( (float) $wpssw_regular_price < 0 ) {
									$wpssw_regular_price = '';
									continue;
								} elseif ( 0 === (int) $wpssw_regular_price ) {
									$wpssw_regular_price = '';
									self::wpssw_update_post_meta( $wpssw_productid, '_sale_price', '' );
									self::wpssw_update_post_meta( $wpssw_productid, '_regular_price', '' );
									self::wpssw_update_post_meta( $wpssw_productid, '_price', '' );
									continue;
								}
							}

							if ( ! empty( $wpssw_sale_price ) && ! empty( $wpssw_regular_price ) ) {

								if ( $wpssw_sale_price < $wpssw_regular_price ) {
									self::wpssw_update_post_meta( $wpssw_productid, '_sale_price', $wpssw_sale_price );
									self::wpssw_update_post_meta( $wpssw_productid, '_regular_price', $wpssw_regular_price );
									self::wpssw_update_post_meta( $wpssw_productid, '_price', $wpssw_sale_price );
									continue;
								}
								$wpssw_value = wc_format_decimal( $wpssw_product->get_sale_price() );
								if ( ! empty( $wpssw_value ) && $wpssw_value < $wpssw_regular_price ) {
									self::wpssw_update_post_meta( $wpssw_productid, '_regular_price', $wpssw_regular_price );
								} elseif ( empty( $wpssw_value ) ) {
									self::wpssw_update_post_meta( $wpssw_productid, '_regular_price', $wpssw_regular_price );
									self::wpssw_update_post_meta( $wpssw_productid, '_price', $wpssw_regular_price );
								}
								continue;
							} else {
								if ( ! empty( $wpssw_sale_price ) ) {
									$wpssw_value = wc_format_decimal( $wpssw_product->get_regular_price() );
									if ( ! empty( $wpssw_value ) && $wpssw_sale_price < $wpssw_value ) {
										self::wpssw_update_post_meta( $wpssw_productid, '_sale_price', $wpssw_sale_price );
										self::wpssw_update_post_meta( $wpssw_productid, '_price', $wpssw_sale_price );
										continue;
									}
								} elseif ( ! empty( $wpssw_regular_price ) ) {
									$wpssw_value = wc_format_decimal( $wpssw_product->get_sale_price() );
									if ( ! empty( $wpssw_value ) && $wpssw_value < $wpssw_regular_price ) {
										self::wpssw_update_post_meta( $wpssw_productid, '_regular_price', $wpssw_regular_price );
										continue;
									} elseif ( empty( $wpssw_value ) ) {
										self::wpssw_update_post_meta( $wpssw_productid, '_regular_price', $wpssw_regular_price );
										self::wpssw_update_post_meta( $wpssw_productid, '_price', $wpssw_regular_price );
									}
									continue;
								}
							}
							continue;
						}
					}
					if ( isset( $wpssw_data[ $wpssw_key ] ) && $wpssw_meta_key ) {
						if ( is_numeric( $wpssw_data[ $wpssw_key ] ) && ! in_array( $wpssw_meta_key, $not_numeric_metakey_val_arr, true ) ) {
							$wpssw_data[ $wpssw_key ] = (int) $wpssw_data[ $wpssw_key ];
						}
					}
					if ( '_downloadable_file_names' === (string) $wpssw_meta_key ) {
						continue;
					}
					if ( '_raw_image' === (string) $wpssw_meta_key ) {
						if ( ! empty( $wpssw_data[ $wpssw_key ] ) ) {
							$wpssw_images = explode( '|', $wpssw_data[ $wpssw_key ] );
						} else {
							$wpssw_images = array();
						}
						$wpssw_imagedata['raw_image'] = array_shift( $wpssw_images );
						if ( ! empty( $wpssw_images ) ) {
							$wpssw_imagedata['raw_gallery_image'] = $wpssw_images;
						}
						self::wpssw_set_image_data( $wpssw_productid, $wpssw_imagedata );
						continue;
					}
					if ( 'dimensions' === (string) $wpssw_meta_key ) {
						$length = '';
						$width  = '';
						$height = '';
						if ( ! empty( $wpssw_data[ $wpssw_key ] ) ) {
							$wpssw_data[ $wpssw_key ] = str_replace( array( ' ', 'cm' ), '', $wpssw_data[ $wpssw_key ] );
							$wpssw_data[ $wpssw_key ] = str_replace( array( 'x', 'X' ), '×', $wpssw_data[ $wpssw_key ] );
							$wpssw_data[ $wpssw_key ] = ( false !== strpos( $wpssw_data[ $wpssw_key ], '(' ) ) ? substr( $wpssw_data[ $wpssw_key ], 0, strpos( $wpssw_data[ $wpssw_key ], '(' ) ) : $wpssw_data[ $wpssw_key ];

							$dimensions = explode( '×', $wpssw_data[ $wpssw_key ] );
							$length     = isset( $dimensions[0] ) ? $dimensions[0] : '';
							$width      = isset( $dimensions[1] ) ? $dimensions[1] : '';
							$height     = isset( $dimensions[2] ) ? $dimensions[2] : '';
						}
						self::wpssw_update_post_meta( $wpssw_productid, '_length', $length );
						self::wpssw_update_post_meta( $wpssw_productid, '_width', $width );
						self::wpssw_update_post_meta( $wpssw_productid, '_height', $height );
						continue;
					}
					if ( '_downloadable_files' === (string) $wpssw_meta_key ) {
						if ( ! empty( $wpssw_data[ $wpssw_key ] ) ) {
							$wpssw_downloadable_filename_key = '';
							$wpssw_downloadable_files        = array();
							if ( false !== strpos( $wpssw_data[ $wpssw_key ], '|' ) ) {
								$wpssw_downloadable_files = explode( '|', $wpssw_data[ $wpssw_key ] );
							} else {
								$wpssw_downloadable_files = explode( ',', $wpssw_data[ $wpssw_key ] );
							}

							$wpssw_downloadable_files = array_map( 'trim', array_filter( $wpssw_downloadable_files ) );

							$wpssw_downloadable_filename_key = array_search( 'Product Variation Downloadable File Names', $wpssw_woo_selections, true );
							$wpssw_downloadable_filenames    = array();
							if ( false !== $wpssw_downloadable_filename_key ) {
								$wpssw_downloadable_filenames = isset( $wpssw_data[ $wpssw_downloadable_filename_key ] ) ? explode( ',', $wpssw_data[ $wpssw_downloadable_filename_key ] ) : array();
							}
							$files_data = array();
							foreach ( $wpssw_product->get_downloads() as $downloads ) {
								$file_data['url']  = $downloads->get_file();
								$file_data['name'] = $downloads->get_name();
								$file_data['id']   = $downloads->get_id();
								$files_data[]      = $file_data;
							}
							$wpssw_downloadable_files_count = count( $wpssw_downloadable_files );
							for ( $i = 0;$i < $wpssw_downloadable_files_count;$i++ ) {

								if ( in_array( $wpssw_downloadable_files[ $i ], array_column( $files_data, 'url' ), true ) ) {
									$key  = array_search( $wpssw_downloadable_files[ $i ], array_column( $files_data, 'url' ), true );
									$name = isset( $files_data[ $key ]['name'] ) ? $files_data[ $key ]['name'] : '';
									$id   = isset( $files_data[ $key ]['id'] ) ? $files_data[ $key ]['id'] : '';

									$download = array();
									if ( isset( $wpssw_downloadable_filenames[ $i ] ) && $name !== $wpssw_downloadable_filenames[ $i ] ) {
										$download['name'] = $wpssw_downloadable_filenames[ $i ];
									} else {
										$download['name'] = $name;
									}
									$download['download_id'] = $id;
									$download['file']        = $wpssw_downloadable_files[ $i ];
									$downloads_array[]       = $download;
								} else {
									$download                = array();
									$download['download_id'] = '';
									$name                    = '';
									if ( isset( $wpssw_downloadable_filenames[ $i ] ) && ! empty( $wpssw_downloadable_filenames[ $i ] ) ) {
										$name = $wpssw_downloadable_filenames[ $i ];
									} else {
										$pathinfo = pathinfo( $wpssw_downloadable_files[ $i ] );
										$name     = isset( $pathinfo['filename'] ) ? $pathinfo['filename'] : '';
									}
									$download['name']  = $name;
									$download['file']  = $wpssw_downloadable_files[ $i ];
									$downloads_array[] = $download;
								}
							}
							if ( ! empty( $downloads_array ) ) {
								$wpssw_product->set_downloads( $downloads_array );
							}
						} else {
							$downloads_array = array();
							$wpssw_product->set_downloads( $downloads_array );
						}
						continue;
					}
					if ( '_download_limit' === (string) $wpssw_meta_key ) {
						if ( ! empty( $wpssw_data[ $wpssw_key ] ) && 'unlimited' === trim( strtolower( $wpssw_data[ $wpssw_key ] ) ) ) {
							$wpssw_data[ $wpssw_key ] = -1;
						}
					}
					if ( '_download_expiry' === (string) $wpssw_meta_key ) {
						if ( ! empty( $wpssw_data[ $wpssw_key ] ) && 'never' === trim( strtolower( $wpssw_data[ $wpssw_key ] ) ) ) {
							$wpssw_data[ $wpssw_key ] = -1;
						}
					}
					if ( '_shipping_class_id' === (string) $wpssw_meta_key ) {
						$wpssw_product->set_shipping_class_id( trim( $wpssw_data[ $wpssw_key ] ) );
						continue;
					}
					if ( '_menu_order' === (string) $wpssw_meta_key ) {
						$menu_order = 0;
						if ( ! empty( $wpssw_data[ $wpssw_key ] ) && (int) $wpssw_data[ $wpssw_key ] > 0 ) {
							$menu_order = (int) $wpssw_data[ $wpssw_key ];
						}
						$wpssw_product->set_menu_order( $menu_order );
						continue;
					}
					$wpssw_data_val                         = isset( $wpssw_data[ $wpssw_key ] ) ? $wpssw_data[ $wpssw_key ] : '';
					$wpssw_updated_array[ $wpssw_meta_key ] = $wpssw_data_val;
					self::wpssw_update_post_meta( $wpssw_productid, $wpssw_meta_key, $wpssw_data_val );
				}
				if ( in_array( $wpssw_header, $wpssw_variation_attribute_header, true ) ) {
					$wpssw_attr_name  = 'attribute_';
					$wpssw_pattr_name = 'attribute_pa_';
					$wpssw_attrname   = strtolower( trim( str_replace( 'Variation: ', '', $wpssw_header ) ) );
					$wpssw_attrname   = str_replace( ' ', '-', $wpssw_attrname );

					$wpssw_attr_name  .= strtolower( rawurlencode( $wpssw_attrname ) );
					$wpssw_pattr_name .= strtolower( rawurlencode( $wpssw_attrname ) );

					$wpssw_selected_variation = $wpssw_product->get_variation_attributes();
					if ( isset( $wpssw_data[ $wpssw_key ] ) ) {
						$attr_val = trim( strtolower( $wpssw_data[ $wpssw_key ] ) );
						if ( isset( $wpssw_selected_variation[ $wpssw_attr_name ] ) ) {
							if ( '' === (string) $attr_val ) {
								$attr_val = '';
							} elseif ( array_key_exists( $wpssw_attrname, $wpssw_parentattributes_options ) ) {
								if ( in_array( $attr_val, array_map( 'strtolower', $wpssw_parentattributes_options[ $wpssw_attrname ] ), true ) ) {
									$key      = '';
									$key      = array_search( $attr_val, array_map( 'strtolower', $wpssw_parentattributes_options[ $wpssw_attrname ] ), true );
									$attr_val = $wpssw_parentattributes_options[ $wpssw_attrname ][ $key ];
								} else {
									$attr_val = '';
								}
							}
							self::wpssw_update_post_meta( $wpssw_productid, $wpssw_attr_name, $attr_val );
						} elseif ( isset( $wpssw_selected_variation[ $wpssw_pattr_name ] ) ) {
							if ( '' === $attr_val ) {
								$attr_val = '';
							} elseif ( array_key_exists( 'pa_' . $wpssw_attrname, $wpssw_parentattributes_options ) ) {
								if ( in_array( $attr_val, array_map( 'strtolower', $wpssw_parentattributes_options[ 'pa_' . $wpssw_attrname ] ), true ) ) {
									$key      = '';
									$key      = array_search( $attr_val, array_map( 'strtolower', $wpssw_parentattributes_options[ 'pa_' . $wpssw_attrname ] ), true );
									$attr_val = $wpssw_parentattributes_options[ 'pa_' . $wpssw_attrname ][ $key ];
									$term     = get_term( $key, 'pa_' . $wpssw_attrname );
									$attr_val = $term->slug;
								} else {
									$attr_val = '';
								}
							}
							self::wpssw_update_post_meta( $wpssw_productid, $wpssw_pattr_name, $attr_val );
						}
					}
				}
				if ( isset( $wpssw_wooproduct_headers['WPSSW_Alg_WC_Product_Notes'] ) && in_array( $wpssw_header, $wpssw_wooproduct_headers['WPSSW_Alg_WC_Product_Notes'], true ) ) {
					if ( ! class_exists( 'Alg_WC_Product_Notes' ) ) {
						continue;
					}
					$wpssw_meta_key    = array_search( $wpssw_header, $wpssw_wooproduct_headers['WPSSW_Alg_WC_Product_Notes'], true );
					$private_or_public = '';

					if ( '_alg_wc_internal_product_note' === (string) $wpssw_meta_key ) {
						$private_or_public = 'private';
					} elseif ( '_alg_wc_public_product_note' === (string) $wpssw_meta_key ) {
						$private_or_public = 'public';
					} else {
						continue;
					}
					$notes = array();
					if ( ! empty( $wpssw_data[ $wpssw_key ] ) ) {
						$notes[] = $wpssw_data[ $wpssw_key ];
					}
					$notes_class = new Alg_WC_Product_Notes_Core();
					$notes_class->set_product_notes( $notes, $private_or_public, $wpssw_productid );

					continue;
				}
				if ( in_array( $wpssw_header, $wpssw_variation_product_import_headers, true ) ) {
					$wpssw_meta_key = array_search( $wpssw_header, $wpssw_variation_product_import_headers, true );

					if ( ! empty( $wpssw_meta_key ) ) {
						$wpssw_meta_val = isset( $wpssw_data[ $wpssw_key ] ) ? $wpssw_data[ $wpssw_key ] : '';
						self::wpssw_update_post_meta( $wpssw_productid, $wpssw_meta_key, $wpssw_meta_val );
					}
					continue;
				}
			}
			$wpssw_updated_array['ID'] = $wpssw_productid;
			wp_update_post( $wpssw_updated_array );
			remove_action( 'woocommerce_update_product', 'WPSSW_Product::wpssw_woocommerce_update_product', 99, 2 );
			$wpssw_product->save();

		}
		/**
		 * Set image data for product
		 *
		 * @param int   $wpssw_productid product id for which image data need to set.
		 * @param array $wpssw_data image data array.
		 */
		public static function wpssw_set_image_data( $wpssw_productid, $wpssw_data ) {
			$wpssw_product = wc_get_product( $wpssw_productid );

			$detach_attachment_ids = '';
			$all_images            = array();
			global $wpdb;
			// @codingStandardsIgnoreStart.
			$wpssw_querystr = $wpdb->prepare( "SELECT ID FROM {$wpdb->prefix}posts WHERE post_type = 'attachment' AND post_parent = $wpssw_productid") ; //db call ok.
			$wpssw_postsmeta = $wpdb->get_results( $wpssw_querystr, ARRAY_A );
			$do_detach = false;
			if( !empty($wpssw_postsmeta) ){
				$do_detach = true;	
				if ( has_post_thumbnail( $wpssw_productid ) ) {
					$all_images[] = get_post_thumbnail_id( $wpssw_productid );
				}
				$gallery_image_ids = $wpssw_product->get_gallery_image_ids();
				$gallery_image_ids = array_filter( $gallery_image_ids );
				if ( ! empty( $gallery_image_ids ) ) {
					$all_images = array_unique(array_merge($all_images,$gallery_image_ids));
				}
			}
			
						
			$current_attachment_ids = array();
			// Image URLs need converting to IDs before inserting.
			if ( isset( $wpssw_data['raw_image'] ) ) {
				$wpssw_image_id = self::wpssw_get_attachment_id_from_url( $wpssw_data['raw_image'], $wpssw_product->get_id() );
				
				if ( $wpssw_image_id ) {
					$current_attachment_ids[] = $wpssw_image_id;
					$wpssw_product->set_image_id( $wpssw_image_id );
				} else {
					$wpssw_product->set_image_id( '' );
					$wpssw_image_id = '';
				}
				self::wpssw_update_post_meta( $wpssw_productid, '_thumbnail_id', $wpssw_image_id );
			}else{
				$wpssw_product->set_image_id( '' );
				self::wpssw_update_post_meta( $wpssw_productid, '_thumbnail_id', '' );
			}
			// Gallery image URLs need converting to IDs before inserting.
			if ( isset( $wpssw_data['raw_gallery_image'] ) ) {
				$gallery_image_ids = array();
				foreach ( $wpssw_data['raw_gallery_image'] as $image_id ) {
					$gallery_image_id = self::wpssw_get_attachment_id_from_url( $image_id, $wpssw_product->get_id() );
					if ( $gallery_image_id ) {
						$gallery_image_ids[] = $gallery_image_id;
					}
				}
				$current_attachment_ids = array_unique(array_merge($current_attachment_ids,array_filter( $gallery_image_ids )));
				self::wpssw_update_post_meta( $wpssw_productid, '_product_image_gallery', implode( ',', array_filter( $gallery_image_ids ) ) );
			}else{
				self::wpssw_update_post_meta( $wpssw_productid, '_product_image_gallery', '' );
			}
			
			if($do_detach){
				$detach_attachment_ids = implode(',',array_filter(array_diff($all_images,$current_attachment_ids)));
				if('' !== (string) $detach_attachment_ids){
					// @codingStandardsIgnoreStart.
					$wpssw_querystr = $wpdb->prepare( "UPDATE {$wpdb->prefix}posts SET post_parent = 0 WHERE post_type = 'attachment' AND post_parent = $wpssw_productid AND ID IN ($detach_attachment_ids) ") ; //db call ok.
					$execut_sql= $wpdb->query($wpssw_querystr);//db call ok.
					// @codingStandardsIgnoreEnd.

				}
			}

		}
		/**
		 * Get image attachment id from image url
		 *
		 * @param string $url image url.
		 * @param int    $product_id .
		 */
		public static function wpssw_get_attachment_id_from_url( $url, $product_id ) {
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
				$upload = wc_rest_upload_image_from_url( $url );

				if ( is_wp_error( $upload ) ) {
					return;
				}
				$id = wc_rest_set_uploaded_image_as_attachment( $upload, $product_id );

				if ( ! wp_attachment_is_image( $id ) ) {
					return;
				}
				// Save attachment source for future reference.
				self::wpssw_update_post_meta( $id, '_wpssw_attachment_source', $url );
			}
			if ( ! $id ) {
				return;
			}
			return $id;
		}
		/**
		 * Parse categories field
		 *
		 * @param array $value category value array.
		 * @param int   $product_id id of product to being processed.
		 */
		public static function wpssw_parse_categories_field( $value, $product_id = 0 ) {
			if ( empty( $value ) ) {
					return array();
			}
			$row_terms           = $value;
			$categories          = array();
			$wpssw_newcategories = array();
			foreach ( $row_terms as $row_term ) {
					$parent = null;
					$_terms = array_map( 'trim', explode( '>', $row_term ) );
					$total  = count( $_terms );
				foreach ( $_terms as $index => $_term ) {
						// Check if category exists. Parent must be empty string or null if doesn't exists.
						$term = term_exists( $_term, 'product_cat', $parent );
					if ( is_array( $term ) ) {
							$term_id = $term['term_id'];
					} else {
						$term = wp_insert_term( $_term, 'product_cat', array( 'parent' => intval( $parent ) ) );
						if ( is_wp_error( $term ) ) {
								break; // We cannot continue if the term cannot be inserted.
						}
						$term_id = $term['term_id'];

						$is_wpml_sitepress_active = WPSSW_Product::wpssw_is_wpml_sitepress_active();
						if ( $is_wpml_sitepress_active ) {
							global $sitepress;
							$el_language_details   = $sitepress->get_element_language_details( $product_id, 'post_product' );
							$term_language_details = $sitepress->get_element_language_details( $term_id, 'tax_product_cat' );
							// Set the desired language.
							$language_args = array(
								'element_id'           => $term_id,
								'element_type'         => 'tax_product_cat',
								'trid'                 => $term_language_details->trid,
								'language_code'        => $el_language_details->language_code ? $el_language_details->language_code : $term_language_details->language_code,
								'source_language_code' => $term_language_details->source_language_code ? $term_language_details->source_language_code : null,
							);
							do_action( 'wpml_set_element_language_details', $language_args );
							$wpssw_newcategories[] = $term_id;
						}
					}
						// Only requires assign the last category.
					if ( ( 1 + $index ) === $total ) {
							$categories[] = $term_id;
					} else {
							// Store parent to be able to insert or query categories based in parent ID.
							$parent = $term_id;
					}
				}
			}
			return $wpssw_newcategories;
		}
		/**
		 * Parse tags field
		 *
		 * @param array $value tags value array.
		 * @param int   $product_id id of product to being processed.
		 */
		public static function wpssw_parse_tags_field( $value, $product_id = 0 ) {
			if ( empty( $value ) ) {
					return array();
			}
			$tags = array();
			foreach ( $value as $index => $_term ) {
				// Check if tag exists.
				$term = term_exists( $_term, 'product_tag' );
				if ( is_array( $term ) ) {
					$term_id = $term['term_id'];
				} else {
					$term = wp_insert_term( $_term, 'product_tag' );
					if ( is_wp_error( $term ) ) {
							break; // We cannot continue if the term cannot be inserted.
					}
					$term_id = $term['term_id'];

					$is_wpml_sitepress_active = WPSSW_Product::wpssw_is_wpml_sitepress_active();
					if ( $is_wpml_sitepress_active ) {
						global $sitepress;
						$el_language_details   = $sitepress->get_element_language_details( $product_id, 'post_product' );
						$term_language_details = $sitepress->get_element_language_details( $term_id, 'tax_product_tag' );
						if ( empty( $term_language_details ) ) {
							$trid_id              = false;
							$source_language_code = null;
						} else {
							$trid_id              = $term_language_details->trid;
							$source_language_code = $term_language_details->source_language_code ? $term_language_details->source_language_code : null;
						}

						// Set the desired language.
						$language_args = array(
							'element_id'           => $term_id,
							'element_type'         => 'tax_product_tag',
							'trid'                 => $trid_id,
							'language_code'        => $el_language_details->language_code ? $el_language_details->language_code : $term_language_details->language_code,
							'source_language_code' => $source_language_code,
						);
						do_action( 'wpml_set_element_language_details', $language_args );
					}
					$tags[] = $term_id;
				}
			}
			return $tags;
		}
		/**
		 * Update post meta
		 *
		 * @param int          $wpssw_productid post id to update post meta.
		 * @param string       $wpssw_meta_key meta key to update.
		 * @param string|array $wpssw_data value for meta key.
		 */
		public static function wpssw_update_post_meta( $wpssw_productid, $wpssw_meta_key, $wpssw_data ) {
			if ( ! $wpssw_productid ) {
				return;
			}
			update_post_meta( $wpssw_productid, $wpssw_meta_key, $wpssw_data );
		}
		/**
		 * Bulk insert products
		 */
		public static function wpssw_bulk_insert_products() {
			// phpcs:ignore
			return;
		}
		/**
		 * Append inserted products to All Products sheet
		 */
		public static function wpssw_bulk_append_products() {
			$wpssw_bulk_inserted_products = parent::wpssw_option( 'wpssw_bulk_inserted_products' );
			if ( ! is_array( $wpssw_bulk_inserted_products ) || empty( $wpssw_bulk_inserted_products ) ) {
				return;
			}
			parent::wpssw_update_option( 'wpssw_bulk_inserted_products', '' );
			if ( ! self::$instance_api->checkcredenatials() ) {
				return;
			}
			$wpssw_product_spreadsheet_setting = parent::wpssw_option( 'wpssw_product_spreadsheet_setting' );
			$wpssw_spreadsheetid               = parent::wpssw_option( 'wpssw_product_spreadsheet_id' );

			if ( 'yes' !== (string) $wpssw_product_spreadsheet_setting ) {
				return;
			}

			$wpssw_inputoption  = WPSSW_Product::wpssw_get_product_inputoption();
			$wpssw_sheetname    = 'All Products';
			$wpssw_sheet        = "'" . $wpssw_sheetname . "'!A:Z";
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
			$wpssw_values_array = array();
			foreach ( $wpssw_bulk_inserted_products as $product_id ) {
				$product = wc_get_product( $product_id );
				if ( in_array( (int) $product_id, parent::wpssw_convert_int( $wpssw_data ), true ) ) {
					continue;
				}
				set_time_limit( 999 );
				if ( ! empty( $product->get_children() ) && 'grouped' !== (string) $product->get_type() ) {
					$wpssw_values_array[] = WPSSW_Product::wpssw_make_product_value_array( 'insert', $product_id );
					foreach ( $product->get_children() as $child ) {
						$wpssw_values_array[] = WPSSW_Product::wpssw_make_product_value_array( 'insert', $child, true );
					}
				} else {
					$wpssw_values_array[] = WPSSW_Product::wpssw_make_product_value_array( 'insert', $product_id );
				}
			}
			$wpssw_values_array = array_values( array_filter( $wpssw_values_array ) );
			$rangetofind        = $wpssw_sheetname . '!A' . ( count( $wpssw_data ) + 1 );

			if ( ! empty( $wpssw_values_array ) ) {
				try {

					$product_inherit_style = parent::wpssw_option( 'wpssw_product_inherit_style' );
					if ( 'yes' === (string) $product_inherit_style ) {
						// If there is no sheet_id available, leave the sheet_id field blank. Otherwise, if there is a sheet_id, leave the sheet_name field blank.
						$wpssw_inherit_params = array(
							'sheet_id'       => '',
							'sheet_name'     => $wpssw_sheetname,
							'start_index'    => count( $wpssw_data ),
							'end_index'      => count( $wpssw_data ) + count( $wpssw_values_array ),
							'spreadsheet_id' => $wpssw_spreadsheetid,
						);
						parent::wpssw_inherit_row_format_style( 'product', $wpssw_inherit_params );
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
	new WPSSW_Product_Import();
endif;
