<?php
/**
 * Main WPSyncSheets_For_WooCommerce namespace.
 *
 * @since 1.0.0
 * @package wpsyncsheets-for-woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }

if ( ! class_exists( 'WPSSW_ACF' ) ) :

	/**
	 * Class WPSSW_ACF.
	 */
	class WPSSW_ACF extends WPSSW_Product_Utils {

		/**
		 * Store header list.
		 *
		 * @var array $wpssw_headers.
		 */
		public static $wpssw_headers = array();
		/**
		 * Allowed field types.
		 *
		 * @var array $wpssw_headers.
		 */
		public static $wpssw_allowed_types = array( 'text', 'url', 'image', 'gallery', 'select', 'checkbox', 'radio', 'button_group', 'file', 'link', 'relationship', 'post_object', 'taxonomy', 'user', 'true_false', 'textarea', 'range', 'number', 'email', 'password', 'wysiwyg', 'oembed', 'page_link', 'date_picker', 'time_picker', 'date_time_picker', 'color_picker' );

		/**
		 * Class Contructor.
		 */
		public function __construct() {
			if ( $this->wpssw_is_pugin_active() ) {
				$this->prepare_headers();
				add_filter( 'wpsyncsheets_product_headers', __CLASS__ . '::get_header_list', 10, 1 );
			}
		}

		/**
		 * Check if ACF (Advanced Custom Fields) or ACF Pro plugin is active or not.
		 */
		public static function wpssw_is_pugin_active() {
			if ( class_exists( 'ACF' ) || class_exists( 'acf_pro' ) ) {
				return true;
			}
			return false;
		}

		/**
		 * Get Header List.
		 *
		 * @param array $headers .
		 * @return array $headers
		 */
		public static function get_header_list( $headers = array() ) {
			if ( ! empty( self::$wpssw_headers ) ) {
				$headers['WPSSW_ACF'] = self::$wpssw_headers;
			}
			return $headers;
		}

		/**
		 * Prepare Header List.
		 */
		protected function prepare_headers() {
			$headers             = array();
			$headers             = self::prepare_custom_header( 'name' );
			self::$wpssw_headers = $headers;
		}

		/**
		 * Prepare header with key or name.
		 *
		 * @param string $wpssw_key Return with key or name.
		 */
		public static function prepare_custom_header( $wpssw_key = 'name' ) {
			$headers = array();

			$field_groups       = array();
			$wppsw_field_groups = array();

			// Check database.
			$raw_field_groups = acf_get_raw_field_groups();

			if ( $raw_field_groups ) {
				foreach ( $raw_field_groups as $raw_field_group ) {
					$field_groups[] = acf_get_field_group( $raw_field_group['ID'] );
				}
			}
			// phpcs:ignore
			$field_groups = apply_filters( 'acf/load_field_groups', $field_groups );

			$args = array(
				'post_type' => 'product',
			);

			foreach ( $field_groups as $field_group ) {
				// Check if active.
				if ( ! $field_group['active'] ) {
					continue;
				}

				// Check if location rules exist.
				if ( $field_group['location'] ) {

					// Get the current screen.
					$screen = acf_get_location_screen( $args );

					// Loop through location groups.
					foreach ( $field_group['location'] as $group ) {

						// ignore group if no rules.
						if ( empty( $group ) ) {
							continue;
						}

						// Loop over rules and determine if all rules match.
						$match_group = false;
						foreach ( $group as $rule ) {
							if ( acf_match_location_rule( $rule, $screen, $field_group ) ) {
								$match_group = true;
								break;
							}
						}

						// If this group matches, save field groups.
						if ( $match_group ) {
							if(isset($field_group['title'])){
								$wppsw_field_groups[ $field_group['title'] ] = $field_group;
							}else{
								$wppsw_field_groups[] = $field_group;
							}
						}
					}
				}
			}

			foreach ( $wppsw_field_groups as $wppsw_fieldkey => $wppsw_field ) {
				if ( ! isset( $wppsw_field['key'] ) || empty( $wppsw_field['key'] ) ) {
					continue;
				}
				$wpssw_products_fields = acf_get_fields( $wppsw_field['key'] );
				foreach ( $wpssw_products_fields as $wpssw_products_field ) {
					if ( isset( $wpssw_products_field['type'] ) && in_array( $wpssw_products_field['type'], self::$wpssw_allowed_types, true ) ) {

						$header_key = '';
						$header_val = '';

						if ( ! empty( $wpssw_products_field['label'] ) ) {
							$header_key =$wpssw_products_field[ $wpssw_key ];
							$header_val = $wpssw_products_field['label'];
						} else {
							$header_key = $wpssw_products_field[ $wpssw_key ];
							$header_val = $wpssw_products_field[ $wpssw_key ];
						}

						if( in_array($header_val, $headers, true) && $wppsw_fieldkey ){
							$header_val = $header_val.' : '.$wppsw_fieldkey;
						}
						if( array_key_exists( $header_key, $headers ) ){
							$header_key = $wpssw_products_field['key'];
						}
						$headers[ $header_key ] = $header_val;
					}
				}
			}
			return $headers;
		}

		/**
		 * Get Value for given header name.
		 *
		 * @param string $wpssw_headers_name Header name.
		 * @param object $wpssw_product product object.
		 */
		public static function get_value( $wpssw_headers_name, $wpssw_product ) {
			return self::prepare_value( $wpssw_headers_name, $wpssw_product );
		}

		/**
		 * Prepare Value for given header name.
		 *
		 * @param string $wpssw_headers_name Header name.
		 * @param object $wpssw_product product object.
		 */
		public static function prepare_value( $wpssw_headers_name, $wpssw_product ) {
			if ( 'variation' === (string) $wpssw_product->get_type() ) {
				return '';
			}
			$updatepost = false;
			// phpcs:ignore.
			if ( isset( $_POST['action'] ) && 'editpost' === sanitize_text_field( wp_unslash( $_POST['action'] ) ) ) {
				$updatepost = true;
			}
			$wpssw_value = '';

			$wpssw_headers_key = self::prepare_custom_header( 'key' );
			$wpssw_headerlist  = self::get_header_list();
			if ( in_array( $wpssw_headers_name, $wpssw_headerlist['WPSSW_ACF'], true ) ) {
				$product_id           = $wpssw_product->get_id();
				$wpssw_field_name     = array_search( $wpssw_headers_name, $wpssw_headerlist['WPSSW_ACF'], true );
				$wpssw_products_field = acf_get_field( $wpssw_field_name );

				if ( is_array( $wpssw_products_field ) && ! empty( $wpssw_products_field ) ) {

					$wpssw_field_key = $wpssw_products_field['key'];

					if ( isset( $wpssw_products_field['type'] ) ) {
						$field_type = $wpssw_products_field['type'];

						if ( in_array( $field_type, array( 'image', 'gallery' ), true ) ) {
							$image_id = self::wpssw_get_field_value( $product_id, $wpssw_field_name, $wpssw_field_key, $updatepost );

							if ( ! empty( $image_id ) ) {
								$image_ids = array();
								if ( ! is_array( $image_id ) ) {
									$image_ids[] = $image_id;
								} else {
									$image_ids = $image_id;
								}
								$image_attachment = array();
								foreach ( $image_ids as $image ) {
									$attachment = wp_get_attachment_image_src( $image, 'full' );

									if ( isset( $attachment[0] ) ) {
										$image_attachment[] = $attachment[0];
									}
								}
								$wpssw_value = implode( '|', $image_attachment );
							}

							return $wpssw_value;
						} elseif ( 'select' === (string) $field_type || 'checkbox' === (string) $field_type ) {

							$vals        = self::wpssw_get_field_value( $product_id, $wpssw_field_name, $wpssw_field_key, $updatepost );
							$wpssw_value = is_array( $vals ) ? implode( ',', $vals ) : $vals;
							return $wpssw_value;

						} elseif ( 'file' === (string) $field_type ) {
							$file_id     = self::wpssw_get_field_value( $product_id, $wpssw_field_name, $wpssw_field_key, $updatepost );
							$wpssw_value = $file_id ? wp_get_attachment_url( $file_id ) : '';
							return $wpssw_value;
						} elseif ( 'link' === (string) $field_type ) {
							$vals = self::wpssw_get_field_value( $product_id, $wpssw_field_name, $wpssw_field_key, $updatepost );
							if ( ! empty( $vals ) && is_array( $vals ) ) {
								if ( isset( $vals['title'] ) ) {
									$wpssw_value = $vals['title'];
								}
								if ( isset( $vals['url'] ) ) {
									$wpssw_value .= ' : ' . $vals['url'];
								}
							}
							return $wpssw_value;
						} elseif ( 'relationship' === (string) $field_type || 'post_object' === (string) $field_type ) {
							$post_ids = self::wpssw_get_field_value( $product_id, $wpssw_field_name, $wpssw_field_key, $updatepost );
							if ( ! empty( $post_ids ) ) {
								if ( is_array( $post_ids ) ) {
									$posts = array();
									foreach ( $post_ids as $post_id ) {
										$posts[] = get_the_title( $post_id ) . ' : ' . $post_id;
									}
									$wpssw_value = implode( ',', $posts );
								} else {
									$wpssw_value = get_the_title( $post_ids ) . ' : ' . $post_ids;
								}
							}
							return $wpssw_value;
						} elseif ( 'taxonomy' === (string) $field_type ) {
							$term_ids = self::wpssw_get_field_value( $product_id, $wpssw_field_name, $wpssw_field_key, $updatepost );
							if ( ! empty( $term_ids ) ) {
								if ( is_array( $term_ids ) ) {
									$terms = array();
									foreach ( $term_ids as $term_id ) {
										$term    = get_term( $term_id );
										$terms[] = $term->name . ' : ' . $term_id;
									}
									$wpssw_value = implode( ',', $terms );
								} else {
									$term        = get_term( $term_ids );
									$wpssw_value = $term->name . ' : ' . $term_ids;
								}
							}
							return $wpssw_value;
						} elseif ( 'user' === (string) $field_type ) {
							$user_ids = self::wpssw_get_field_value( $product_id, $wpssw_field_name, $wpssw_field_key, $updatepost );
							if ( ! empty( $user_ids ) ) {
								$users = array();
								if ( is_array( $user_ids ) ) {
									$users = $user_ids;
								} else {
									$users[] = $user_ids;
								}
								$users = array_filter( $users );
								$vals  = array();
								foreach ( $users as $uid ) {
									$user      = new WP_User( $uid );
									$user_data = $user->data;
									$vals[]    = $user_data->user_login . ' : ' . $uid;
								}
								$wpssw_value = implode( ',', $vals );
							}
							return $wpssw_value;
						} elseif ( 'true_false' === (string) $field_type ) {
							$val         = self::wpssw_get_field_value( $product_id, $wpssw_field_name, $wpssw_field_key, $updatepost );
							$wpssw_value = 1 === (int) $val ? 'Yes' : 'No';
							return $wpssw_value;
						} elseif ( 'date_picker' === (string) $field_type ) {
							$val         = self::wpssw_get_field_value( $product_id, $wpssw_field_name, $wpssw_field_key, $updatepost );
							$wpssw_value = ! empty( $val ) ? date_i18n( 'Y-m-d', strtotime( $val ) ) : '';
							return $wpssw_value;
						}
					}
				}
				$wpssw_insert_val = self::wpssw_get_field_value( $product_id, $wpssw_field_name, $wpssw_field_key, $updatepost );
				$wpssw_value      = is_array( $wpssw_insert_val ) ? implode( ',', $wpssw_insert_val ) : $wpssw_insert_val;
			}
			return $wpssw_value;
		}
		/**
		 * Import ACF Product Fields.
		 *
		 * @param int    $wpssw_productid product id currenlty being processed.
		 * @param string $wpssw_headers_name Header name.
		 * @param string $wpssw_header_val product value for header.
		 * @param array  $wpssw_data product data.
		 */
		public static function wpssw_import_acf_fields( $wpssw_productid, $wpssw_headers_name, $wpssw_header_val, $wpssw_data ) {

			$wpssw_headerlist = self::get_header_list();
			if ( in_array( $wpssw_headers_name, $wpssw_headerlist['WPSSW_ACF'], true ) ) {
				$wpssw_field_name = array_search( $wpssw_headers_name, $wpssw_headerlist['WPSSW_ACF'], true );
				$wpssw_header_val = trim( $wpssw_header_val );

				if ( ! empty( $wpssw_field_name ) && '' !== $wpssw_header_val ) {
					$wpssw_products_field = acf_get_field( $wpssw_field_name );
					if ( is_array( $wpssw_products_field ) && ! empty( $wpssw_products_field ) ) {
						if ( isset( $wpssw_products_field['type'] ) ) {
							$field_type = $wpssw_products_field['type'];
							if ( in_array( $field_type, array( 'image', 'gallery' ), true ) ) {
								if ( 'image' === $field_type ) {
									$attchment_id = WPSSW_Product_Import::wpssw_get_attachment_id_from_url( $wpssw_header_val, $wpssw_productid );
									update_post_meta( $wpssw_productid, $wpssw_field_name, $attchment_id );
									return;
								} else {
									$urls          = explode( '|', $wpssw_header_val );
									$attchment_ids = array();
									foreach ( $urls as $url ) {
										$attchment_ids[] = WPSSW_Product_Import::wpssw_get_attachment_id_from_url( $url, $wpssw_productid );
									}
									update_post_meta( $wpssw_productid, $wpssw_field_name, $attchment_ids );
									return;
								}
							} elseif ( in_array( $field_type, array( 'radio', 'checkbox', 'button_group', 'select' ), true ) ) {

								$wpssw_insert_val = '';

								if ( 'checkbox' === (string) $field_type ) {
									$chechbox_choices = array();
									$choices          = explode( ',', $wpssw_header_val );
									foreach ( $choices as &$choice ) {
										$choice = trim( $choice );
										if ( isset( $wpssw_products_field['choices'] ) && array_key_exists( $choice, $wpssw_products_field['choices'] ) ) {
											$wpssw_insert_val = $choice;
										} elseif ( isset( $wpssw_products_field['choices'] ) && ! array_key_exists( $choice, $wpssw_products_field['choices'] ) && isset( $wpssw_products_field['allow_custom'] ) && 1 === (int) $wpssw_products_field['allow_custom'] ) {
											if ( isset( $wpssw_products_field['save_custom'] ) && 1 === (int) $wpssw_products_field['save_custom'] ) {
												if ( false !== strpos( $choice, ':' ) ) {
													$vals   = explode( ':', $choice );
													$vals   = array_map( 'trim', $vals );
													$choice = $vals[0];
													$wpssw_products_field['choices'][ trim( $vals[0] ) ] = $vals[1] ? trim( $vals[1] ) : trim( $vals[0] );
												} else {
													$wpssw_products_field['choices'][ $choice ] = $choice;
												}
												// Save field.
												$field = acf_update_field( $wpssw_products_field );

											}
											$wpssw_insert_val = $choice;
										}
										$chechbox_choices[] = $wpssw_insert_val;
									}
									$chechbox_choices = array_filter( array_unique( $chechbox_choices ) );
									update_post_meta( $wpssw_productid, $wpssw_field_name, $chechbox_choices );
									return;
								} elseif ( 'select' === (string) $field_type ) {

									if ( isset( $wpssw_products_field['multiple'] ) && 1 === (int) $wpssw_products_field['multiple'] ) {
										$choices          = explode( ',', $wpssw_header_val );
										$chechbox_choices = array();
										foreach ( $choices as &$choice ) {
											$choice = trim( $choice );
											if ( isset( $wpssw_products_field['choices'] ) && array_key_exists( $choice, $wpssw_products_field['choices'] ) ) {
												$chechbox_choices[] = $choice;
											}
										}
										$chechbox_choices = array_filter( array_unique( $chechbox_choices ) );
										update_post_meta( $wpssw_productid, $wpssw_field_name, $chechbox_choices );
										return;
									}
								} elseif ( 'radio' === (string) $field_type ) {
									if ( isset( $wpssw_products_field['choices'] ) && array_key_exists( $wpssw_header_val, $wpssw_products_field['choices'] ) ) {
										$wpssw_insert_val = $wpssw_header_val;
									} elseif ( isset( $wpssw_products_field['choices'] ) && ! array_key_exists( $wpssw_header_val, $wpssw_products_field['choices'] ) && isset( $wpssw_products_field['other_choice'] ) && 1 === (int) $wpssw_products_field['other_choice'] ) {
										if ( isset( $wpssw_products_field['save_other_choice'] ) && 1 === (int) $wpssw_products_field['save_other_choice'] ) {
											if ( false !== strpos( $wpssw_header_val, ':' ) ) {
												$vals             = explode( ':', $wpssw_header_val );
												$vals             = array_map( 'trim', $vals );
												$wpssw_header_val = $vals[0];
												$wpssw_products_field['choices'][ trim( $vals[0] ) ] = $vals[1] ? trim( $vals[1] ) : trim( $vals[0] );
											} else {
												$wpssw_products_field['choices'][ $wpssw_header_val ] = $wpssw_header_val;
											}
											// Save field.
											$field = acf_update_field( $wpssw_products_field );
										}
										$wpssw_insert_val = $wpssw_header_val;
									}
									update_post_meta( $wpssw_productid, $wpssw_field_name, $wpssw_insert_val );
									return;
								}

								if ( isset( $wpssw_products_field['choices'] ) && array_key_exists( $wpssw_header_val, $wpssw_products_field['choices'] ) ) {
									$wpssw_insert_val = $wpssw_header_val;
								}
								update_post_meta( $wpssw_productid, $wpssw_field_name, $wpssw_insert_val );
								return;

							} elseif ( 'true_false' === (string) $field_type ) {
								$wpssw_insert_val = 0;
								if ( 'yes' === (string) trim( strtolower( $wpssw_header_val ) ) ) {
									$wpssw_insert_val = 1;
								}
								update_post_meta( $wpssw_productid, $wpssw_field_name, $wpssw_insert_val );
								return;
							} elseif ( 'file' === (string) $field_type ) {
								$wpssw_insert_val = '';
								if ( ! empty( $wpssw_header_val ) ) {
									$wpssw_insert_val = self::wpssw_get_file_attachment_id_from_url( $wpssw_header_val, $wpssw_productid );
								}
								update_post_meta( $wpssw_productid, $wpssw_field_name, $wpssw_insert_val );
								return;
							} elseif ( 'user' === (string) $field_type ) {
								$user_datas = explode( ',', $wpssw_header_val );
								$user_ids   = array();
								foreach ( $user_datas as $user_data ) {
									$user_obj = '';
									if ( false !== strpos( $user_data, ':' ) ) {
										$vals     = explode( ':', $user_data );
										$vals     = array_map( 'trim', $vals );
										$username = $vals[0];
										if ( isset( $vals[1] ) && ! empty( $vals[1] ) ) {
											$user_id  = $vals[1];
											$user_obj = get_user_by( 'id', $user_id );
										} else {
											$user_obj = get_user_by( 'login', $username );
										}
									} else {
										if ( is_numeric( $user_data ) ) {
											$user_obj = get_user_by( 'id', $user_data );
										} else {
											$user_obj = get_user_by( 'login', $user_data );
										}
									}
									if ( ! is_null( $user_obj ) && ! is_wp_error( $user_obj ) && ! empty( $user_obj ) ) {
										$user_ids[] = $user_obj->ID;
									}
								}
								$user_ids = array_filter( array_unique( $user_ids ) );
								if ( isset( $wpssw_products_field['multiple'] ) && 1 === (int) $wpssw_products_field['multiple'] ) {
									update_post_meta( $wpssw_productid, $wpssw_field_name, $user_ids );
									return;
								}
								$user_id = isset( $user_ids[0] ) ? $user_ids[0] : '';
								update_post_meta( $wpssw_productid, $wpssw_field_name, $user_id );
								return;
							} elseif ( 'relationship' === (string) $field_type || 'post_object' === (string) $field_type ) {
								$datas    = explode( ',', $wpssw_header_val );
								$post_ids = array();
								foreach ( $datas as $data ) {
									$post_obj = '';
									$post_id  = 0;
									if ( false !== strpos( $data, ':' ) ) {
										$vals     = explode( ':', $data );
										$vals     = array_map( 'trim', $vals );
										$postname = $vals[0];
										if ( isset( $vals[1] ) && ! empty( $vals[1] ) ) {
											$post_id = $vals[1];
										} else {
											$post_id = post_exists( $postname );
										}
									} else {
										if ( is_numeric( $data ) ) {
											$post_id = $data;
										} else {
											$post_id = post_exists( $data );
										}
									}
									if ( $post_id ) {
										$post_obj = get_post( $post_id );
									}
									if ( ! is_null( $post_obj ) && ! is_wp_error( $post_obj ) && ! empty( $post_obj ) ) {
										$post_ids[] = $post_obj->ID;
									}
								}
								$post_ids = array_filter( array_unique( $post_ids ) );
								if ( ( isset( $wpssw_products_field['multiple'] ) && 1 === (int) $wpssw_products_field['multiple'] ) || 'relationship' === (string) $field_type ) {
									update_post_meta( $wpssw_productid, $wpssw_field_name, $post_ids );
									return;
								}
								$post_id = isset( $post_ids[0] ) ? $post_ids[0] : '';
								update_post_meta( $wpssw_productid, $wpssw_field_name, $post_id );
								return;
							} elseif ( 'taxonomy' === (string) $field_type ) {
								$datas    = explode( ',', $wpssw_header_val );
								$term_ids = array();
								$taxonomy = $wpssw_products_field['taxonomy'];

								foreach ( $datas as $data ) {
									$data        = trim( $data );
									$term_exists = false;
									$term_id     = 0;
									$term_name   = '';
									if ( false !== strpos( $data, ':' ) ) {
										$vals = explode( ':', $data );
										foreach ( $vals as &$val ) {
											$val = trim( $val );
											if ( is_numeric( $val ) ) {
												$val = (int) $val;
											}
										}
										$term_name = $vals[0];
										if ( isset( $vals[1] ) && ! empty( $vals[1] ) ) {
											$term_id = $vals[1];
											$term    = term_exists( $term_id, $taxonomy );

											if ( is_array( $term ) ) {
												$term_exists = true;
												$term_id     = $term['term_id'];
											}
										} else {
											$term = term_exists( $vals[0], $taxonomy );

											if ( is_array( $term ) ) {
												$term_exists = true;
												$term_id     = $term['term_id'];
											}
										}
									} else {
										if ( is_numeric( $data ) ) {
											$data = (int) $data;
										}

										$term_name = $data;
										$term      = term_exists( $data, $taxonomy );

										if ( is_array( $term ) ) {
											$term_exists = true;
											$term_id     = $term['term_id'];
										}
									}
									if ( $term_id && true === $term_exists ) {
										$term_ids[] = $term_id;
									}
									if ( false === $term_exists ) {
										if ( isset( $wpssw_products_field['add_term'] ) && 1 === (int) $wpssw_products_field['add_term'] ) {
											$term = wp_insert_term( $term_name, $taxonomy );
											if ( is_wp_error( $term ) ) {
												continue;
											}
											$termid = $term['term_id'];

											$term_ids[] = $termid;
										}
									}
								}

								$term_ids = array_filter( array_unique( $term_ids ) );

								if ( isset( $wpssw_products_field['field_type'] ) && ( 'checkbox' === (string) $wpssw_products_field['field_type'] || 'multi_select' === (string) $wpssw_products_field['field_type'] ) ) {
									update_post_meta( $wpssw_productid, $wpssw_field_name, $term_ids );
									return;
								}
								$termid = isset( $term_ids[0] ) ? $term_ids[0] : '';

								update_post_meta( $wpssw_productid, $wpssw_field_name, $termid );
								return;
							} elseif ( 'date_picker' === (string) $field_type ) {
								$date_val = ! empty( $wpssw_header_val ) ? date_i18n( 'Ymd', strtotime( $wpssw_header_val ) ) : '';
								update_post_meta( $wpssw_productid, $wpssw_field_name, $date_val );
								return;
							} elseif ( 'link' === (string) $field_type ) {
								if ( false !== strpos( $wpssw_header_val, ':' ) && 0 !== strpos( $wpssw_header_val, 'http' ) ) {
									$vals  = explode( ':', $wpssw_header_val );
									$title = trim( $vals[0] );
									$url   = '#';
									if ( count( $vals ) > 2 ) {
										unset( $vals[0] );
										$vals[1] = implode( ':', $vals );
									}
									$vals = array_map( 'trim', $vals );

									if ( isset( $vals[1] ) && ! empty( $vals[1] ) ) {
										$url = $vals[1];
									}
								} else {
									$title = $wpssw_header_val;
									$url   = $wpssw_header_val;
								}

								$previous_link = get_post_meta( $wpssw_productid, $wpssw_field_name, true );
								if ( ! empty( $previous_link ) && is_array( $previous_link ) ) {
									if ( isset( $previous_link['title'] ) && $previous_link['title'] === $title && isset( $previous_link['url'] ) && $previous_link['url'] === $url ) {
										return;
									}
								}

								$new_link = array(
									'title'  => $title,
									'url'    => $url,
									'target' => 0,
								);
								update_post_meta( $wpssw_productid, $wpssw_field_name, $new_link );
								return;
							}
						}
						if ( isset( $wpssw_products_field['multiple'] ) && 1 === (int) $wpssw_products_field['multiple'] ) {
							$choices = explode( ',', $wpssw_header_val );
							$choices = array_filter( array_unique( $choices ) );
							update_post_meta( $wpssw_productid, $wpssw_field_name, $choices );
							return;
						}
					}
				}

				update_post_meta( $wpssw_productid, $wpssw_field_name, $wpssw_header_val );
			}
		}

		/**
		 * Get image attachment id from image url
		 *
		 * @param string $url image url.
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
		 * Get ACF field value.
		 *
		 * @param int    $product_id .
		 * @param string $wpssw_field_name name of the acf field.
		 * @param string $wpssw_field_key key of the acf field.
		 * @param bool   $updatepost update action or not.
		 */
		public static function wpssw_get_field_value( $product_id, $wpssw_field_name, $wpssw_field_key, $updatepost ) {
			$wpssw_value = '';
			if ( $updatepost ) {
				// @codingStandardsIgnoreStart.
				if ( ! empty( $wpssw_field_key ) && isset( $_POST['acf'] ) && isset( $_POST['acf'][ $wpssw_field_key ] ) ) {
					$wpssw_value = is_array($_POST['acf'][ $wpssw_field_key ]) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['acf'][ $wpssw_field_key ] ) ) : sanitize_text_field( wp_unslash( $_POST['acf'][ $wpssw_field_key ] ) );
					// @codingStandardsIgnoreEnd.
				}
			} elseif ( ! empty( $product_id ) && ! empty( $wpssw_field_name ) ) {
				$wpssw_value = get_post_meta( $product_id, $wpssw_field_name, true );
			}
			return $wpssw_value;
		}
	}
	new WPSSW_ACF();
endif;
