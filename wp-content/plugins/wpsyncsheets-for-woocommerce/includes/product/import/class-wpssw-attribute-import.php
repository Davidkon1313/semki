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
if ( ! class_exists( 'WPSSW_Attribute_Import' ) ) :
	/**
	 * Class WPSSW_Attribute_Import.
	 */
	class WPSSW_Attribute_Import extends WPSSW_Setting {
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
			$wpssw_include->wpssw_include_attribute_import_ajax_hook();
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
		public static function wpssw_get_attribute_import_count() {
			if ( ! isset( $_POST['wpssw_product_settings'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wpssw_product_settings'] ) ), 'save_product_settings' ) ) {
				echo esc_html__( 'Sorry, your nonce did not verify.', 'wpssw' );
				die();
			}
			if ( ! self::$instance_api->checkcredenatials() ) {
				return;
			}

			$wpssw_product_spreadsheet_setting = parent::wpssw_option( 'wpssw_product_spreadsheet_setting' );
			$wpssw_spreadsheetid               = parent::wpssw_option( 'wpssw_product_spreadsheet_id' );
			$wpssw_sheetname                   = 'Product Attributes';

			if ( 'yes' !== (string) $wpssw_product_spreadsheet_setting ) {
				return;
			}
			$wpssw_sheet    = "'" . $wpssw_sheetname . "'!A:A";
			$wpssw_allentry = self::$instance_api->get_row_list( $wpssw_spreadsheetid, $wpssw_sheetname );
			$wpssw_data     = $wpssw_allentry->getValues();
			$wpssw_headers  = array_shift( $wpssw_data );
			if ( ! in_array( 'Attribute ID', $wpssw_headers, true ) ) {
				echo esc_html( 'notexist' );
				die;
			}
			$wpssw_insert_attributes = array();
			$wpssw_update_attributes = array();
			$wpssw_delete_attributes = array();
			if ( in_array( 'Insert', $wpssw_headers, true ) ) {
				$wpssw_insert_key        = array_search( 'Insert', $wpssw_headers, true );
				$wpssw_insert_attributes = array_values( array_filter( array_column( $wpssw_data, $wpssw_insert_key ) ) );
			}
			if ( in_array( 'Update', $wpssw_headers, true ) ) {
				$wpssw_update_key        = array_search( 'Update', $wpssw_headers, true );
				$wpssw_update_attributes = array_values( array_filter( array_column( $wpssw_data, $wpssw_update_key ) ) );
			}
			if ( in_array( 'Delete', $wpssw_headers, true ) ) {
				$wpssw_delete_key        = array_search( 'Delete', $wpssw_headers, true );
				$wpssw_delete_attributes = array_values( array_filter( array_column( $wpssw_data, $wpssw_delete_key ) ) );
			}
			$wpssw_result_array                          = array();
			$wpssw_result_array['totalimportattributes'] = 0;
			$totalimportattributes                       = 0;
			if ( count( $wpssw_insert_attributes ) > 0 ) {
				$wpssw_result_array['insertattributes'] = count( $wpssw_insert_attributes );
				$totalimportattributes                  = $totalimportattributes + count( $wpssw_insert_attributes );
			}
			if ( count( $wpssw_update_attributes ) > 0 ) {
				$wpssw_result_array['updateattributes'] = count( $wpssw_update_attributes );
				$totalimportattributes                  = $totalimportattributes + count( $wpssw_update_attributes );
			}
			if ( count( $wpssw_delete_attributes ) > 0 ) {
				$wpssw_result_array['deleteattributes'] = count( $wpssw_delete_attributes );
				$totalimportattributes                  = $totalimportattributes + count( $wpssw_delete_attributes );
			}
			$wpssw_result_array['totalimportattributes'] = $totalimportattributes;

			echo wp_json_encode( $wpssw_result_array );
			die;
		}
		/**
		 * Import attribute
		 */
		public static function wpssw_attribute_import() {

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
			$wpssw_sheetname                   = 'Product Attributes';

			if ( 'yes' !== (string) $wpssw_product_spreadsheet_setting ) {
				return;
			}
			$wpssw_sheet    = "'" . $wpssw_sheetname . "'!A:A";
			$wpssw_allentry = self::$instance_api->get_row_list( $wpssw_spreadsheetid, $wpssw_sheetname );

			$wpssw_data = $wpssw_allentry->getValues();

			$wpssw_importattributecount    = isset( $_POST['importattributecount'] ) ? sanitize_text_field( wp_unslash( $_POST['importattributecount'] ) ) : '';
			$wpssw_importattributelimit    = isset( $_POST['importattributelimit'] ) ? sanitize_text_field( wp_unslash( $_POST['importattributelimit'] ) ) : '';
			$wpssw_attribute_ids           = array_map(
				function( $wpssw_element ) {
					if ( isset( $wpssw_element[0] ) ) {
						return $wpssw_element[0];
					} else {
						return '';
					}
				},
				$wpssw_data
			);
			$wpssw_attribute_variation_ids = array_map(
				function( $wpssw_element ) {
					if ( isset( $wpssw_element[1] ) ) {
						return $wpssw_element[1];
					} else {
						return '';
					}
				},
				$wpssw_data
			);
			$wpssw_headers                 = array_shift( $wpssw_data );

			$wpssw_insert_attributes = array();
			$wpssw_update_attributes = array();
			$wpssw_delete_attributes = array();
			if ( in_array( 'Insert', $wpssw_headers, true ) ) {
				$wpssw_insert_key        = array_search( 'Insert', $wpssw_headers, true );
				$wpssw_insert_attributes = array_map(
					function( $wpssw_element ) use ( $wpssw_insert_key ) {
						if ( isset( $wpssw_element[ $wpssw_insert_key ] ) ) {
							return $wpssw_element[ $wpssw_insert_key ];
						} else {
							return '';
						}
					},
					$wpssw_data
				);
				$wpssw_insert_attributes = array_filter( $wpssw_insert_attributes );
			}
			if ( in_array( 'Update', $wpssw_headers, true ) ) {
				$wpssw_update_key        = array_search( 'Update', $wpssw_headers, true );
				$wpssw_update_attributes = array_map(
					function( $wpssw_element ) use ( $wpssw_update_key ) {
						if ( isset( $wpssw_element[ $wpssw_update_key ] ) ) {
							return $wpssw_element[ $wpssw_update_key ];
						} else {
							return '';
						}
					},
					$wpssw_data
				);

				$wpssw_update_attributes = array_filter( $wpssw_update_attributes );
			}
			if ( in_array( 'Delete', $wpssw_headers, true ) ) {
				$wpssw_delete_key        = array_search( 'Delete', $wpssw_headers, true );
				$wpssw_delete_attributes = array_map(
					function( $wpssw_element ) use ( $wpssw_delete_key ) {
						if ( isset( $wpssw_element[ $wpssw_delete_key ] ) ) {
							return $wpssw_element[ $wpssw_delete_key ];
						} else {
							return '';
						}
					},
					$wpssw_data
				);
				$wpssw_delete_attributes = array_filter( $wpssw_delete_attributes );
			}

			$wpssw_spreadsheetid       = parent::wpssw_option( 'wpssw_product_spreadsheet_id' );
			$wpssw_sheetname           = 'Product Attributes';
			$wpssw_response            = self::$instance_api->get_sheet_listing( $wpssw_spreadsheetid );
			$wpssw_existingsheetsnames = self::$instance_api->get_sheet_list( $wpssw_response );
			$wpssw_sheetid             = $wpssw_existingsheetsnames[ $wpssw_sheetname ];

			$deleterequestarray = array();

			$newimportattribute = 0;
			if ( ! empty( $wpssw_update_attributes ) ) {
				foreach ( $wpssw_update_attributes as $wpssw_attributeid => $wpssw_val ) {
					if ( 1 !== (int) $wpssw_val ) {
						continue;
					}
					if ( ! isset( $wpssw_data[ $wpssw_attributeid ] ) ) {
						continue;
					}
					$wpssw_attribute_index = $wpssw_attributeid + 1;

					if ( $newimportattribute > $wpssw_importattributelimit ) {
						break;
					}
					$attrdata  = $wpssw_data[ $wpssw_attributeid ];
					$attr_name = str_replace( array( ' ' ), array( '_' ), $attrdata[1] );
					if ( empty( $attr_name ) ) {
						echo esc_html__( 'addattributename', 'wpssw' );
						die;
					} elseif ( isset( $attrdata[0] ) && empty( $attrdata[0] ) ) {
						echo esc_html__( 'addattributeId', 'wpssw' );
						die;
					}
					if ( taxonomy_exists( 'pa_' . strtolower( $attr_name ) ) ) {
						set_time_limit( 999 );

						$attribute_id   = $attrdata[0];
						$terms          = explode( ',', $attrdata[5] );
						$taxonomy_terms = array();
						$delete_terms   = array();
						$taxonomy_terms = array_column( get_terms( 'pa_' . strtolower( $attr_name ), 'hide_empty=0' ), 'name', 'term_id' );
						$taxonomy_terms = array_filter( $taxonomy_terms );
						$delete_terms   = ( ! empty( $taxonomy_terms ) && ! empty( $terms ) ) ? array_diff( $taxonomy_terms, $terms ) : array();

						if ( ! empty( $delete_terms ) ) {
							foreach ( $delete_terms as $delete_term_id => $delete_term ) {
								wp_delete_term( $delete_term_id, 'pa_' . strtolower( $attr_name ) );
							}
						}
						wp_set_object_terms( $attribute_id, $terms, 'pa_' . strtolower( $attr_name ), false );
						wc_update_attribute(
							$attrdata[0],
							array(
								'slug'         => strtolower( $attr_name ),
								'name'         => $attrdata[2],
								'type'         => $attrdata[3],
								'orderby'      => $attrdata[4],
								'has_archives' => false,
							)
						);
						$newimportattribute++;
					} else {
						echo esc_html__( 'attributenamenotexist', 'wpssw' );
						die;
					}
				}
			}
			if ( ! empty( $wpssw_insert_attributes ) ) {
				$attributes           = wc_get_attribute_taxonomies();
				$slugs                = wp_list_pluck( $attributes, 'attribute_name' );
				$wpssw_woo_selections = stripslashes_deep( WPSSW_Product::wpssw_attribute_headers() );

				foreach ( $wpssw_insert_attributes as $wpssw_attribute_index => $wpssw_val ) {
					if ( 1 !== (int) $wpssw_val ) {
						continue;
					}
					if ( ! isset( $wpssw_data[ $wpssw_attribute_index ] ) ) {
						continue;
					}
					if ( $newimportattribute > $wpssw_importattributelimit ) {
						break;
					}
					if ( ! $wpssw_woo_selections ) {
						return;
					}
					$attrdata  = $wpssw_data[ $wpssw_attribute_index ];
					$attr_name = str_replace( array( ' ' ), array( '_' ), $attrdata[1] );

					if ( empty( $attr_name ) ) {
						echo esc_html__( 'addattributename', 'wpssw' );
						die;
					} elseif ( in_array( $attr_name, $slugs, true ) ) {
						echo esc_html__( 'attributenameexist', 'wpssw' );
						die;
					} elseif ( isset( $attrdata[0] ) && ! empty( $attrdata[0] ) ) {
						echo esc_html__( 'attributeIdexist', 'wpssw' );
						die;
					}

					if ( ! in_array( strtolower( $attr_name ), $slugs, true ) ) {
						$args         = array(
							'slug'         => strtolower( $attr_name ),
							'name'         => $attrdata[2],
							'type'         => $attrdata[3],
							'orderby'      => $attrdata[4],
							'has_archives' => false,
						);
						$attribute_id = wc_create_attribute( $args );
					}
					if ( $wpssw_sheetid ) {
						$param                = array();
						$startindex           = $wpssw_attribute_index + 1;
						$endindex             = $wpssw_attribute_index + 2;
						$param                = self::$instance_api->prepare_param( $wpssw_sheetid, $startindex, $endindex );
						$deleterequestarray[] = self::$instance_api->deleteDimensionrequests( $param, 'ROWS' );
					}
					$terms = explode( ',', $attrdata[5] );
					self::wpssw_set_terms( $attribute_id, $terms, 'pa_' . strtolower( $attr_name ), false );
					$newimportattribute++;
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
						$wpssw_response         = self::$instance_api->updatebachrequests( $param );
					} catch ( Exception $e ) {
						if ( ! $wpssw_cron_function_call ) {
							echo esc_html( 'Message: ' . $e->getMessage() );
						}
					}
				}
			}
			if ( ! empty( $wpssw_delete_attributes ) ) {
				foreach ( $wpssw_delete_attributes as $wpssw_attributeid => $wpssw_val ) {
					if ( 1 !== (int) $wpssw_val ) {
						continue;
					}
					if ( ! isset( $wpssw_data[ $wpssw_attributeid ] ) ) {
						continue;
					}
					if ( $newimportattribute > $wpssw_importattributelimit ) {
						break;
					}
					set_time_limit( 999 );

					$wpssw_attribute_index = $wpssw_attributeid + 1;
					$attrdata              = $wpssw_data[ $wpssw_attributeid ];
					if ( isset( $attrdata[0] ) && empty( $attrdata[0] ) ) {
						echo esc_html__( 'addattributeId', 'wpssw' );
						die;
					}
					$deleted = wc_delete_attribute( $attrdata[0] );
					if ( false === $deleted ) {
						echo esc_html__( 'attributenamenotexist', 'wpssw' );
						die;
					}
					$newimportattribute++;
				}
			}
			echo esc_html__( 'successful', 'wpssw' );
			die;
		}
		/**
		 * Set attribute terms
		 *
		 * @param int    $attribute_id .
		 * @param array  $terms .
		 * @param string $attribute .
		 * @param bool   $append .
		 */
		public static function wpssw_set_terms( $attribute_id, $terms, $attribute, $append = false ) {
			wp_set_object_terms( $attribute_id, $terms, $attribute, $append );
		}
		/**
		 * Attribute added (hook).
		 *
		 * @param int   $attrid   Added attribute ID.
		 * @param array $attr_data Attribute data.
		 */
		public static function wpssw_attribute_added( $attrid, $attr_data ) {
			if ( false === self::wpssw_check_attribute_sheet() ) {
				return;
			}
			self::wpssw_update_attributes( $attrid, $attr_data, 'insert' );
		}
		/**
		 * Attribute updated (hook).
		 *
		 * @param int    $attrid        Added attribute ID.
		 * @param array  $attr_data     Attribute data.
		 * @param string $attr_old_slug Attribute old name.
		 */
		public static function wpssw_attribute_updated( $attrid, $attr_data, $attr_old_slug ) {
			if ( false === self::wpssw_check_attribute_sheet() ) {
				return;
			}
			self::wpssw_update_attributes( $attrid, $attr_data, 'update' );
		}
		/**
		 * After deleting an attribute (hook).
		 *
		 * @param int    $attrid    Attribute ID.
		 * @param string $attr_name Attribute name.
		 * @param string $taxonomy  Attribute taxonomy name.
		 */
		public static function wpssw_attribute_deleted( $attrid, $attr_name, $taxonomy ) {
			if ( false === self::wpssw_check_attribute_sheet() ) {
				return;
			}
			self::wpssw_update_attributes( $attrid, array(), 'delete' );
		}
		/**
		 * Sync Attributes
		 *
		 * @param int    $attrid    Attribute ID.
		 * @param string $attr_data Attribute data.
		 * @param string $action    Action to perform.
		 */
		public static function wpssw_update_attributes( $attrid, $attr_data, $action = 'insert' ) {

			if ( ! self::$instance_api->checkcredenatials() ) {
				return;
			}
			$wpssw_product_spreadsheet_setting = parent::wpssw_option( 'wpssw_product_spreadsheet_setting' );
			$wpssw_spreadsheetid               = parent::wpssw_option( 'wpssw_product_spreadsheet_id' );

			if ( 'yes' !== (string) $wpssw_product_spreadsheet_setting || '' === (string) $wpssw_spreadsheetid ) {
				return;
			}

			$wpssw_response            = self::$instance_api->get_sheet_listing( $wpssw_spreadsheetid );
			$wpssw_existingsheetsnames = self::$instance_api->get_sheet_list( $wpssw_response );

			$wpssw_sheetname = 'Product Attributes';
			if ( false === array_key_exists( $wpssw_sheetname, $wpssw_existingsheetsnames ) ) {
				return;
			}
			$wpssw_sheetid = $wpssw_existingsheetsnames[ $wpssw_sheetname ];

			$wpssw_sheet    = "'" . $wpssw_sheetname . "'!A:A";
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
			$wpssw_num      = array_search( (int) $attrid, parent::wpssw_convert_int( $wpssw_data ), true );
			if ( 'delete' === (string) $action ) {

				if ( $wpssw_num > 0 ) {
					$wpssw_startindex       = $wpssw_num;
					$wpssw_endindex         = $wpssw_num + 1;
					$param                  = array();
					$param                  = self::$instance_api->prepare_param( $wpssw_sheetid, $wpssw_startindex, $wpssw_endindex );
					$wpssw_requestbody      = self::$instance_api->deleteDimensionrequests( $param, 'ROWS' );
					$param                  = array();
					$param['spreadsheetid'] = $wpssw_spreadsheetid;
					$param['requestarray']  = $wpssw_requestbody;
					$wpssw_response         = self::$instance_api->updatebachrequests( $param );
				}
				return;
			}

			$wpssw_values_array = array();
			$newattribute       = 0;
			$wpssw_attr_headers = WPSSW_Product::wpssw_attribute_headers();

			$wpssw_attrval = array();
			if ( 'insert' === (string) $action || 'update' === (string) $action ) {
				foreach ( $wpssw_attr_headers as $key => $header ) {
					if ( 'Attribute ID' === (string) $header ) {
						$wpssw_attrval[] = $attrid;
						continue;
					}
					if ( 'Attribute Name' === (string) $header ) {
						$wpssw_attrval[] = $attr_data['attribute_name'];
						continue;
					}
					if ( 'Attribute Label' === (string) $header ) {
						$wpssw_attrval[] = $attr_data['attribute_label'];
						continue;
					}
					if ( 'Attribute Type' === (string) $header ) {
						$wpssw_attrval[] = $attr_data['attribute_type'];
						continue;
					}
					if ( 'Attribute Orderby' === (string) $header ) {
						$wpssw_attrval[] = $attr_data['attribute_orderby'];
						continue;
					}
					if ( 'Attribute Terms' === (string) $header ) {
						$slug        = 'pa_' . $attr_data['attribute_name'];
						$terms       = get_terms(
							array(
								'taxonomy'   => $slug,
								'hide_empty' => 0,
							)
						);
						$termsstring = array();
						foreach ( $terms as $term ) {
							if ( isset( $term->name ) ) {
								$termsstring[] = $term->name;
							}
						}
						if ( ! empty( $termsstring ) ) {
							$wpssw_attrval[] = implode( ',', $termsstring );
						} else {
							$wpssw_attrval[] = '';
						}
						continue;
					}
					$wpssw_attrval[] = '';
				}
			}
			$wpssw_values_array[] = $wpssw_attrval;

			if ( ! empty( $wpssw_values_array ) ) {
				try {

					$wpssw_inputoption = WPSSW_Product::wpssw_get_product_inputoption();
					$wpssw_requestbody = self::$instance_api->valuerangeobject( $wpssw_values_array );
					$wpssw_params      = array( 'valueInputOption' => $wpssw_inputoption );
					if ( count( $wpssw_data ) > 1 ) {
						if ( 'update' === (string) $action ) {
							if ( $wpssw_num > 0 ) {
								$wpssw_rangenum      = $wpssw_num + 1;
								$wpssw_rangetoupdate = $wpssw_sheetname . '!A' . $wpssw_rangenum;
							} else {
								$highest_attr_id = max( $wpssw_data );
								if ( ( $highest_attr_id ) && $attrid < $highest_attr_id ) {
									$product_inherit_style = self::wpssw_option( 'wpssw_product_inherit_style' );
									foreach ( $wpssw_data as $wpssw_key => $wpssw_value ) {
										if ( ! empty( $wpssw_value ) ) {
											if ( (int) $attrid < (int) $wpssw_value ) {
												$wpssw_startindex = $wpssw_key;
												$wpssw_endindex   = $wpssw_key + 1;
												$param            = array();
												$param            = self::$instance_api->prepare_param( $wpssw_sheetid, $wpssw_startindex, $wpssw_endindex );
												if ( 'no' === (string) $product_inherit_style ) {
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
												break;
											}
										}
									}
								}
							}

							$param = self::$instance_api->setparamater( $wpssw_spreadsheetid, $wpssw_rangetoupdate, $wpssw_requestbody, $wpssw_params );

							$wpssw_response = self::$instance_api->updateentry( $param );

							return;
						}

						$product_inherit_style = parent::wpssw_option( 'wpssw_product_inherit_style' );
						if ( 'yes' === (string) $product_inherit_style ) {
							// If there is no sheet_id available, leave the sheet_id field blank. Otherwise, if there is a sheet_id, leave the sheet_name field blank.
							$wpssw_inherit_params = array(
								'sheet_id'       => $wpssw_sheetid,
								'sheet_name'     => '',
								'start_index'    => count( $wpssw_data ),
								'end_index'      => count( $wpssw_data ) + count( $wpssw_values_array ),
								'spreadsheet_id' => $wpssw_spreadsheetid,
							);
							parent::wpssw_inherit_row_format_style( 'product', $wpssw_inherit_params );
						}

						$rangetofind    = $wpssw_sheetname . '!A' . ( count( $wpssw_data ) + 1 );
						$param          = self::$instance_api->setparamater( $wpssw_spreadsheetid, $rangetofind, $wpssw_requestbody, $wpssw_params );
						$wpssw_response = self::$instance_api->appendentry( $param );

					} else {
						$rangetofind    = $wpssw_sheetname . '!A2';
						$param          = self::$instance_api->setparamater( $wpssw_spreadsheetid, $rangetofind, $wpssw_requestbody, $wpssw_params );
						$wpssw_response = self::$instance_api->appendentry( $param );
					}
				} catch ( Exception $e ) {
					echo esc_html( 'Message: ' . $e->getMessage() );
				}
			}
		}
		/**
		 * Check whether the attributes sheet exists or not.
		 */
		public static function wpssw_check_attribute_sheet() {
			if ( ! self::$instance_api->checkcredenatials() ) {
				return false;
			}
			$wpssw_product_spreadsheet_setting = parent::wpssw_option( 'wpssw_product_spreadsheet_setting' );
			$wpssw_spreadsheetid               = parent::wpssw_option( 'wpssw_product_spreadsheet_id' );
			if ( 'yes' !== (string) $wpssw_product_spreadsheet_setting || ! parent::wpssw_check_sheet_exist( $wpssw_spreadsheetid, 'Product Attributes' ) ) {
				return false;
			}
			return true;
		}
		/**
		 * Create/Update Term
		 *
		 * @param int    $term_id .
		 * @param int    $tt_id .
		 * @param string $taxonomy .
		 * @param bool   $update .
		 */
		public static function wpssw_term_update( $term_id, $tt_id, $taxonomy, $update ) {
			$delete = true;
			// @codingStandardsIgnoreStart.
			if ( isset( $_REQUEST['action'] ) && ( 'delete-tag' === (string) $_REQUEST['action'] || 'delete' === (string) $_REQUEST['action'] ) ) {
				if ( isset( $_REQUEST['taxonomy'] ) ) {
					if ( 'pa_' === (string) substr( $_REQUEST['taxonomy'], 0, 3 ) ) {
						$delete = false;
					}
				}
			}
			
			$edit = true;
			if ( isset( $_POST['action'] ) && ( 'editedtag' === sanitize_text_field( wp_unslash( $_POST['action'] ) ) ) ) {
				if ( isset( $_POST['taxonomy'] ) ) {
					if ( 'pa_' === substr( sanitize_text_field( wp_unslash( $_POST['taxonomy'] ) ), 0, 3 ) ) {
						$edit = false;
					}
				}
			}

			if ( ( ! isset( $_POST['post_type'] ) || ( 'product' !== sanitize_text_field( wp_unslash( $_POST['post_type'] ) ) ) ) && $delete && $edit ) {
				return;
			}
			// @codingStandardsIgnoreEnd.
			$attr_name = str_replace( 'pa_', '', $taxonomy );

			$attribute_data = self::get_attribute_id_from_name( $attr_name );
			$attribute_id   = true;
			if ( ! isset( $attribute_data->attribute_id ) || empty( $attribute_data->attribute_id ) ) {
				$attribute_id = false;
			}
			if ( $attribute_id ) {
				$attr_data = array(
					'attribute_name'    => strtolower( $attribute_data->attribute_name ),
					'attribute_label'   => $attribute_data->attribute_label,
					'attribute_type'    => $attribute_data->attribute_type,
					'attribute_orderby' => $attribute_data->attribute_orderby,
				);
					self::wpssw_update_attributes( $attribute_data->attribute_id, $attr_data, 'update' );
			}
		}
		/**
		 * Get the product attribute ID from the name.
		 *
		 * @param string $name | The name (slug).
		 */
		public static function get_attribute_id_from_name( $name ) {
			global $wpdb;
			// @codingStandardsIgnoreStart.
			$attribute_id = $wpdb->get_results(
				"SELECT *
		    FROM {$wpdb->prefix}woocommerce_attribute_taxonomies
		    WHERE attribute_name = '$name'"
			); //db call ok.
			// @codingStandardsIgnoreEnd.
			return reset( $attribute_id );
		}
	}
	new WPSSW_Attribute_Import();
endif;
