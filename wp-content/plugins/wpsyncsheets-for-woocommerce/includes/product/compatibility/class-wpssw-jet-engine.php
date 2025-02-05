<?php
/**
 * Main WPSyncSheets_For_WooCommerce namespace.
 *
 * @since 1.0.0
 * @package wpsyncsheets-for-woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }

if ( ! class_exists( 'WPSSW_Jet_Engine' ) ) :

	/**
	 * Class WPSSW_Jet_Engine.
	 */
	class WPSSW_Jet_Engine extends WPSSW_Product_Utils {

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
		public static $wpssw_allowed_types = array( 'text', 'select', 'radio', 'checkbox', 'media', 'date', 'time', 'textarea', 'wysiwyg', 'datetime-local', 'switcher', 'iconpicker', 'colorpicker', 'posts', 'gallery', 'number', 'repeater' );

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
		 * Check if JetEngine plugin is active or not.
		 */
		public static function wpssw_is_pugin_active() {
			if ( class_exists( 'Jet_Engine' ) ) {
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
				$headers['WPSSW_Jet_Engine'] = self::$wpssw_headers;
			}
			return $headers;
		}

		/**
		 * Prepare Header List.
		 */
		protected function prepare_headers() {
			$headers = array();
			if ( jet_engine()->meta_boxes ) {
				// Fetch all JetEngine registered fields for product.
				$all_fields = jet_engine()->meta_boxes->get_fields_for_context( 'post_type', 'product' );
				if ( $all_fields ) {
					foreach ( $all_fields as $field ) {
						if ( isset( $field['object_type'] ) && 'field' === (string) $field['object_type'] && isset( $field['type'] ) && in_array( $field['type'], self::$wpssw_allowed_types, true ) ) {

							if ( 'repeater' === (string) $field['type'] ) {
								if ( isset( $field['repeater-fields'] ) ) {
									foreach ( $field['repeater-fields'] as $repeater_field ) {
										if ( isset( $repeater_field['type'] ) && in_array( $repeater_field['type'], self::$wpssw_allowed_types, true ) ) {
											$headers[ $field['name'] . '-wpssw-' . $repeater_field['name'] ] = $field['title'] . ' ' . $repeater_field['title'];
										}
									}
								}
								continue;
							}
							$headers[ $field['name'] ] = $field['title'];
						}
					}
				}
			}
			self::$wpssw_headers = $headers;
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

			$wpssw_headerlist = self::get_header_list();
			if ( isset( $wpssw_headerlist['WPSSW_Jet_Engine'] ) && in_array( $wpssw_headers_name, $wpssw_headerlist['WPSSW_Jet_Engine'], true ) ) {
				$product_id                = $wpssw_product->get_id();
				$wpssw_field_name          = array_search( $wpssw_headers_name, $wpssw_headerlist['WPSSW_Jet_Engine'], true );
				$wpssw_products_field      = array();
				$is_repeater_field         = false;
				$wpssw_repeater_field_name = '';
				if ( false !== strpos( $wpssw_field_name, '-wpssw-' ) ) {
					$wpssw_repeater_field_name = substr( $wpssw_field_name, strpos( $wpssw_field_name, '-wpssw-' ) + 7 );
					$wpssw_field_name          = substr( $wpssw_field_name, 0, strpos( $wpssw_field_name, '-wpssw-' ) );
					$is_repeater_field         = true;
				}
				$fields = jet_engine()->meta_boxes->get_meta_fields_for_object( 'product' );
				foreach ( $fields as $field ) {
					if ( ! isset( $field['name'] ) || $wpssw_field_name !== (string) $field['name'] ) {
						continue;
					}
					if ( isset( $field['object_type'] ) && 'field' === (string) $field['object_type'] && isset( $field['type'] ) && in_array( $field['type'], self::$wpssw_allowed_types, true ) ) {
						$wpssw_products_field = $field;
						break;
					}
				}
				$value = self::wpssw_get_field_value( $product_id, $wpssw_field_name, $updatepost );

				if ( empty( $value ) ) {
					return $wpssw_value;
				}

				if ( $is_repeater_field ) {

					if ( empty( $wpssw_repeater_field_name ) ) {
						return $wpssw_value;
					}
					$repeater_fields = isset( $wpssw_products_field['repeater-fields'] ) ? $wpssw_products_field['repeater-fields'] : array();
					foreach ( $repeater_fields as $repeater_field ) {
						if ( ! isset( $repeater_field['name'] ) || $wpssw_repeater_field_name !== (string) $repeater_field['name'] ) {
							continue;
						}
						if ( isset( $repeater_field['type'] ) && in_array( $repeater_field['type'], self::$wpssw_allowed_types, true ) ) {
							$wpssw_products_field = $repeater_field;
							if ( is_array( $wpssw_products_field ) && ! empty( $wpssw_products_field ) ) {

								$field_type      = $wpssw_products_field['type'];
								$repeater_values = array_column( $value, $wpssw_repeater_field_name );

								$wpssw_insert_value = array();
								foreach ( $repeater_values as $repeater_value ) {

									if ( in_array( $field_type, array( 'media', 'gallery' ), true ) ) {
										$image_val = array();
										if ( isset( $wpssw_products_field['value_format'] ) && 'both' === (string) $wpssw_products_field['value_format'] ) {
											if ( is_string( $repeater_value ) && ! empty( $repeater_value ) ) {
												$decoded_value = json_decode( $repeater_value );
												if ( ! is_null( $decoded_value ) ) { // check if it was invalid json string.
													$repeater_value = is_object( $decoded_value ) ? (array) $decoded_value : $decoded_value;
												}
											}
											if ( is_array( $repeater_value ) && ! empty( $repeater_value ) ) {
												if ( 'gallery' === (string) $field_type ) {

													foreach ( $repeater_value as $val ) {
														if ( is_object( $val ) ) {
															$val = (array) $val;
														}
														$image_val[] = isset( $val['url'] ) ? $val['url'] : '';
													}
												} else {
													$image_val[] = isset( $repeater_value['url'] ) ? $repeater_value['url'] : '';
												}
											}
										} else {
											$images = explode( ',', $repeater_value );
											$images = $images ? array_map( 'trim', $images ) : array();

											foreach ( $images as $image ) {
												if ( is_numeric( $image ) ) {
													$image_val[] = wp_get_attachment_url( $image );
												} else {
													$image_val[] = $image;
												}
											}
											$image_val = array_filter( $image_val );
										}
										$wpssw_insert_value[] = implode( ' , ', $image_val );
										continue;
									} elseif ( 'checkbox' === (string) $field_type ) {
										if ( isset( $wpssw_products_field['is_array'] ) && 1 === (int) $wpssw_products_field['is_array'] && ! $updatepost ) {
											$wpssw_insert_value[] = is_array( $repeater_value ) ? implode( ',', $repeater_value ) : $repeater_value;
											continue;

										}
										$vals                 = array_keys( $repeater_value, 'true', true );
										$wpssw_insert_value[] = is_array( $vals ) ? implode( ',', $vals ) : $vals;
										continue;

									} elseif ( 'datetime-local' === (string) $field_type ) {

										if ( isset( $wpssw_products_field['is_timestamp'] ) && 1 === (int) $wpssw_products_field['is_timestamp'] ) {

											$wpssw_insert_value[] = ! empty( $repeater_value ) ? date_i18n( 'Y-m-d H:i', $repeater_value ) : '';

										} else {
											$wpssw_insert_value[] = str_replace( 'T', ' ', $repeater_value );
										}
										continue;
									} elseif ( 'date' === (string) $field_type ) {

										if ( isset( $wpssw_products_field['is_timestamp'] ) && 1 === (int) $wpssw_products_field['is_timestamp'] ) {
											$wpssw_insert_value[] = ! empty( $repeater_value ) ? date_i18n( 'Y-m-d', $repeater_value ) : '';
										} else {
											$wpssw_insert_value[] = $repeater_value;
										}
										continue;
									}
									$wpssw_insert_value[] = is_array( $repeater_value ) ? implode( ',', $repeater_value ) : $repeater_value;
								}
								$wpssw_value = implode( ' | ', $wpssw_insert_value );
								return $wpssw_value;
							} else {
								return $wpssw_value;
							}
							break;
						}
					}
					return $wpssw_value;
				}

				if ( is_array( $wpssw_products_field ) && ! empty( $wpssw_products_field ) ) {

					$field_type = $wpssw_products_field['type'];
					if ( in_array( $field_type, array( 'media', 'gallery' ), true ) ) {
						$image_val = array();
						if ( isset( $wpssw_products_field['value_format'] ) && 'both' === (string) $wpssw_products_field['value_format'] ) {
							if ( is_string( $value ) ) {
								$decoded_value = json_decode( $value );
								if ( ! is_null( $decoded_value ) ) { // check if it was invalid json string.
									$value = is_object( $decoded_value ) ? (array) $decoded_value : $decoded_value;
								}
							}
							if ( 'gallery' === (string) $field_type ) {

								foreach ( $value as $val ) {
									if ( is_object( $val ) ) {
										$val = (array) $val;
									}
									$image_val[] = $val['url'];
								}
							} else {
								$image_val[] = $value['url'];
							}
						} else {
							$images = explode( ',', $value );
							$images = $images ? array_map( 'trim', $images ) : array();

							foreach ( $images as $image ) {
								if ( is_numeric( $image ) ) {
									$image_val[] = wp_get_attachment_url( $image );
								} else {
									$image_val[] = $image;
								}
							}
							$image_val = array_filter( $image_val );
						}
						$wpssw_value = implode( ' , ', $image_val );
						return $wpssw_value;
					} elseif ( 'checkbox' === (string) $field_type ) {

						if ( isset( $wpssw_products_field['is_array'] ) && 1 === (int) $wpssw_products_field['is_array'] && ! $updatepost ) {
							$wpssw_value = is_array( $value ) ? implode( ',', $value ) : $value;
							return $wpssw_value;
						}
						$vals        = array_keys( $value, 'true', true );
						$wpssw_value = is_array( $vals ) ? implode( ',', $vals ) : $vals;
						return $wpssw_value;

					} elseif ( 'datetime-local' === (string) $field_type ) {
						if ( isset( $wpssw_products_field['is_timestamp'] ) && 1 === (int) $wpssw_products_field['is_timestamp'] ) {
							$wpssw_value = ! empty( $value ) ? date_i18n( 'Y-m-d H:i', $value ) : '';
						} else {
							$wpssw_value = str_replace( 'T', ' ', $value );
						}
						return $wpssw_value;
					} elseif ( 'date' === (string) $field_type && isset( $wpssw_products_field['is_timestamp'] ) && 1 === (int) $wpssw_products_field['is_timestamp'] ) {
						$wpssw_value = ! empty( $value ) ? date_i18n( 'Y-m-d', $value ) : '';
						return $wpssw_value;
					}
				}
				$wpssw_value = is_array( $value ) ? implode( ',', $value ) : $value;
			}
			return $wpssw_value;
		}
		/**
		 * Import JetEngine Fields.
		 *
		 * @param int    $wpssw_productid product id currenlty being processed.
		 * @param string $wpssw_headers_name Header name.
		 * @param string $wpssw_header_val product value for header.
		 * @param array  $wpssw_data product data.
		 */
		public static function wpssw_import_jetengine_fields( $wpssw_productid, $wpssw_headers_name, $wpssw_header_val, $wpssw_data ) {

			$wpssw_headerlist = self::get_header_list();
			if ( in_array( $wpssw_headers_name, $wpssw_headerlist['WPSSW_Jet_Engine'], true ) ) {
				$wpssw_field_name = array_search( $wpssw_headers_name, $wpssw_headerlist['WPSSW_Jet_Engine'], true );
				$wpssw_header_val = trim( $wpssw_header_val );
				if ( false !== strpos( $wpssw_field_name, '-wpssw-' ) ) {
					return;
				}
				if ( ! empty( $wpssw_field_name ) && '' !== $wpssw_header_val ) {
					$wpssw_products_field = array();

					$fields = jet_engine()->meta_boxes->get_meta_fields_for_object( 'product' );
					foreach ( $fields as $field ) {
						if ( ! isset( $field['name'] ) || $wpssw_field_name !== (string) $field['name'] ) {
							continue;
						}
						if ( isset( $field['object_type'] ) && 'field' === (string) $field['object_type'] && isset( $field['type'] ) && in_array( $field['type'], self::$wpssw_allowed_types, true ) ) {
							$wpssw_products_field = $field;
							break;
						}
					}

					if ( is_array( $wpssw_products_field ) && ! empty( $wpssw_products_field ) ) {

						$field_type = $wpssw_products_field['type'];
						if ( ! in_array( $field_type, array( 'media', 'gallery', 'date', 'datetime-local', 'switcher', 'select', 'radio', 'checkbox', 'posts' ), true ) ) {
							update_post_meta( $wpssw_productid, $wpssw_field_name, $wpssw_header_val );
							return;
						}
						if ( in_array( $field_type, array( 'media', 'gallery' ), true ) ) {
							if ( 'both' !== (string) $wpssw_products_field['value_format'] ) {
								$urls          = explode( ',', $wpssw_header_val );
								$attchment_ids = array();
								foreach ( $urls as $url ) {
									$url = trim( $url );
									if ( is_numeric( $url ) && false === wp_get_attachment_metadata( $url ) ) {
										continue;
									}
									$attchment_id = $url;
									if ( ! is_numeric( $url ) ) {
										$attchment_id = WPSSW_Product::wpssw_get_file_attachment_id_from_url( $url, $wpssw_productid );
									}
									if ( 'id' === (string) $wpssw_products_field['value_format'] ) {
										$attchment_ids[] = $attchment_id;
										continue;
									}
									$attchment_ids[] = wp_get_attachment_url( $attchment_id );
								}
								$wpssw_value = implode( ',', $attchment_ids );
								update_post_meta( $wpssw_productid, $wpssw_field_name, $wpssw_value );
								return;
							} else {
								$urls_array = explode( ',', $wpssw_header_val );
								$attchments = array();
								if ( ! $urls_array ) {
									$urls_array = array();
								}
								foreach ( $urls_array as $url_arr ) {
									$url_arr = trim( $url_arr );
									$urls    = explode( ',', $url_arr );
									if ( ! empty( $urls ) ) {
										foreach ( $urls as $url ) {
											$url = trim( $url );
											if ( is_numeric( $url ) && false === wp_get_attachment_metadata( $url ) ) {
												continue;
											}
											$attchment_id = $url;
											if ( ! is_numeric( $url ) ) {
												$attchment_id = WPSSW_Product::wpssw_get_file_attachment_id_from_url( $url, $wpssw_productid );
											}
											$attchments[] = array(
												'id'  => $attchment_id,
												'url' => wp_get_attachment_url( $attchment_id ),
											);
											break;

										}
									}
								}
								if ( 'media' === (string) $field_type ) {
									$attchments = isset( $attchments[0] ) ? $attchments[0] : array();
								}
								update_post_meta( $wpssw_productid, $wpssw_field_name, $attchments );
								return;
							}
						} elseif ( 'switcher' === (string) $field_type ) {
							$wpssw_header_val = strtolower( $wpssw_header_val );
							update_post_meta( $wpssw_productid, $wpssw_field_name, $wpssw_header_val );
							return;
						} elseif ( 'posts' === (string) $field_type ) {
							$posts_value = explode( ',', $wpssw_header_val );
							$wpssw_value = array();
							foreach ( $posts_value as $post_id ) {
								$post_id = trim( $post_id );
								if ( null !== get_post( $post_id ) ) {
									$wpssw_value[] = $post_id;
								}
							}
							if ( ! isset( $wpssw_products_field['is_multiple'] ) || 1 !== (int) $wpssw_products_field['is_multiple'] ) {
								$wpssw_update_value = isset( $wpssw_value[0] ) ? $wpssw_value[0] : '';
								update_post_meta( $wpssw_productid, $wpssw_field_name, $wpssw_update_value );
								return;
							}
							update_post_meta( $wpssw_productid, $wpssw_field_name, $wpssw_value );
							return;
						} elseif ( 'date' === (string) $field_type || 'datetime-local' === (string) $field_type ) {
							$datetime_val = $wpssw_header_val ? strtotime( $wpssw_header_val ) : '';
							$wpssw_value  = '';
							if ( ! isset( $wpssw_products_field['is_timestamp'] ) || 1 !== (int) $wpssw_products_field['is_timestamp'] ) {
								if ( 'date' === (string) $field_type ) {
									$wpssw_value = ! empty( $datetime_val ) ? date_i18n( 'Y-m-d', $datetime_val ) : '';
								} else {
									$wpssw_value = ! empty( $wpssw_header_val ) ? date_i18n( 'Y-m-d\TH:i', $datetime_val ) : '';
								}
							} else {
								$wpssw_value = $datetime_val;
							}
							update_post_meta( $wpssw_productid, $wpssw_field_name, $wpssw_value );
							return;
						} elseif ( 'radio' === (string) $field_type ) {
							$header_val       = strtolower( $wpssw_header_val );
							$options          = array();
							$fields           = array();
							$glossary         = array();
							$glossary_options = false;
							if ( isset( $wpssw_products_field['options_from_glossary'] ) && 1 === (int) $wpssw_products_field['options_from_glossary'] && isset( $wpssw_products_field['glossary_id'] ) && ! empty( $wpssw_products_field['glossary_id'] ) ) {
								$glossary         = jet_engine()->glossaries->data->get_item_for_edit( absint( $wpssw_products_field['glossary_id'] ) );
								$glossary_options = true;
								if ( $glossary ) {
									$fields  = isset( $glossary['fields'] ) ? $glossary['fields'] : array();
									$options = array_map( 'strtolower', array_column( $fields, 'value' ) );
								}
							} else {
								$options = isset( $wpssw_products_field['options'] ) ? $wpssw_products_field['options'] : array();
								$options = array_map( 'strtolower', array_column( $options, 'key' ) );
							}
							$wpssw_value = '';
							$wpssw_value = $wpssw_header_val;
							if ( ! in_array( $header_val, $options, true ) ) {
								$allow_custom = isset( $wpssw_products_field['allow_custom'] ) ? $wpssw_products_field['allow_custom'] : '';
								$save_custom  = isset( $wpssw_products_field['save_custom'] ) ? $wpssw_products_field['save_custom'] : '';
								if ( $allow_custom ) {
									$wpssw_value = $wpssw_header_val;
									if ( $save_custom ) {
										$wpssw_jet_engine_meta_boxes = WPSSW_Setting::wpssw_option( 'jet_engine_meta_boxes' );
										$update_meta                 = false;
										if ( ! empty( $wpssw_jet_engine_meta_boxes ) ) {
											// Update field.
											foreach ( $wpssw_jet_engine_meta_boxes as &$meta_box ) {
												if ( isset( $meta_box['args'] ) && isset( $meta_box['args']['object_type'] ) && 'post' === $meta_box['args']['object_type'] && isset( $meta_box['args']['allowed_post_type'] ) && 'product' === $meta_box['args']['allowed_post_type'] ) {

													if ( isset( $meta_box['meta_fields'] ) && ! empty( $meta_box['meta_fields'] ) ) {
														foreach ( $meta_box['meta_fields'] as &$meta_field ) {
															if ( isset( $meta_field['name'] ) && $wpssw_field_name === (string) $meta_field['name'] ) {
																$update_meta             = true;
																$meta_field['options'][] = array(
																	'key'        => esc_attr( $wpssw_value ),
																	'value'      => esc_attr( $wpssw_value ),
																	'is_checked' => '',
																);
																break;
															}
														}
														if ( $update_meta ) {
															WPSSW_Setting::wpssw_update_option( 'jet_engine_meta_boxes', $wpssw_jet_engine_meta_boxes );
															break;
														}
													}
												}
											}
										}
										if ( $glossary && $glossary_options ) {
											$fields[] = array(
												'value' => $wpssw_value,
												'label' => $wpssw_value,
											);
											$new_item = array(
												'id'     => absint( $wpssw_products_field['glossary_id'] ),
												'name'   => $glossary['name'],
												'fields' => $fields,
											);

											jet_engine()->glossaries->data->set_request( $new_item );
											jet_engine()->glossaries->data->edit_item( false );
										}
									}
								} else {
									return;
								}
							}
							update_post_meta( $wpssw_productid, $wpssw_field_name, $wpssw_value );
							return;
						} elseif ( 'checkbox' === (string) $field_type ) {
							$options_value    = explode( ',', $wpssw_header_val );
							$options_value    = array_map( 'strtolower', $options_value );
							$options_value    = array_map( 'trim', $options_value );
							$options          = array();
							$fields           = array();
							$glossary         = array();
							$glossary_options = false;
							if ( isset( $wpssw_products_field['options_from_glossary'] ) && 1 === (int) $wpssw_products_field['options_from_glossary'] && isset( $wpssw_products_field['glossary_id'] ) && ! empty( $wpssw_products_field['glossary_id'] ) ) {
								$glossary         = jet_engine()->glossaries->data->get_item_for_edit( absint( $wpssw_products_field['glossary_id'] ) );
								$glossary_options = true;
								if ( $glossary ) {
									$fields  = isset( $glossary['fields'] ) ? $glossary['fields'] : array();
									$options = array_column( $fields, 'value' );
								}
							} else {
								$options = isset( $wpssw_products_field['options'] ) ? $wpssw_products_field['options'] : array();
								$options = array_column( $options, 'key' );
							}
							$wpssw_value = array();

							foreach ( $options as $option_value ) {
								$header_val = strtolower( $option_value );
								if ( in_array( $header_val, $options_value, true ) ) {
									$wpssw_value[ $option_value ] = 'true';
								} else {
									$wpssw_value[ $option_value ] = 'false';
								}
							}
							$allow_custom = isset( $wpssw_products_field['allow_custom'] ) ? $wpssw_products_field['allow_custom'] : '';
							$save_custom  = isset( $wpssw_products_field['save_custom'] ) ? $wpssw_products_field['save_custom'] : '';
							if ( $allow_custom ) {
								$options_value  = explode( ',', $wpssw_header_val );
								$options        = array_map( 'strtolower', $options );
								$custom_options = array();
								$options_value  = array_map( 'trim', $options_value );
								foreach ( $options_value as $option_value ) {
									$header_val = strtolower( $option_value );
									if ( ! in_array( $header_val, $options, true ) ) {
										$wpssw_value[ $option_value ] = 'true';
										$custom_options[]             = $option_value;
									}
								}
								if ( $save_custom ) {
									$wpssw_jet_engine_meta_boxes = WPSSW_Setting::wpssw_option( 'jet_engine_meta_boxes' );
									$update_meta                 = false;
									if ( ! empty( $wpssw_jet_engine_meta_boxes ) ) {
										// Update field.
										foreach ( $wpssw_jet_engine_meta_boxes as &$meta_box ) {
											if ( isset( $meta_box['args'] ) && isset( $meta_box['args']['object_type'] ) && 'post' === $meta_box['args']['object_type'] && isset( $meta_box['args']['allowed_post_type'] ) && 'product' === $meta_box['args']['allowed_post_type'] ) {

												if ( isset( $meta_box['meta_fields'] ) && ! empty( $meta_box['meta_fields'] ) ) {
													foreach ( $meta_box['meta_fields'] as &$meta_field ) {
														if ( isset( $meta_field['name'] ) && $wpssw_field_name === (string) $meta_field['name'] ) {
															$update_meta = true;
															foreach ( $custom_options as $custom_option ) {
																$meta_field['options'][] = array(
																	'key'        => esc_attr( $custom_option ),
																	'value'      => esc_attr( $custom_option ),
																	'is_checked' => '',
																);
															}
															break;
														}
													}
													if ( $update_meta ) {
														WPSSW_Setting::wpssw_update_option( 'jet_engine_meta_boxes', $wpssw_jet_engine_meta_boxes );
														break;
													}
												}
											}
										}
									}
									if ( $glossary && $glossary_options ) {
										foreach ( $custom_options as $custom_option ) {
											$fields[] = array(
												'value' => $custom_option,
												'label' => $custom_option,
											);
										}
										$new_item = array(
											'id'     => absint( $wpssw_products_field['glossary_id'] ),
											'name'   => $glossary['name'],
											'fields' => $fields,
										);
										jet_engine()->glossaries->data->set_request( $new_item );
										jet_engine()->glossaries->data->edit_item( false );
									}
								}
							}
							if ( isset( $wpssw_products_field['is_array'] ) && 1 === (int) $wpssw_products_field['is_array'] ) {
								$wpssw_value = array_keys( $wpssw_value, 'true', true );
							}
							update_post_meta( $wpssw_productid, $wpssw_field_name, $wpssw_value );
							return;
						} elseif ( 'select' === (string) $field_type ) {
							$options_value = explode( ',', $wpssw_header_val );
							$options       = array();

							if ( isset( $wpssw_products_field['options_from_glossary'] ) && 1 === (int) $wpssw_products_field['options_from_glossary'] && isset( $wpssw_products_field['glossary_id'] ) && ! empty( $wpssw_products_field['glossary_id'] ) ) {
								$glossary = jet_engine()->glossaries->data->get_item_for_edit( absint( $wpssw_products_field['glossary_id'] ) );
								if ( $glossary ) {
									$fields  = isset( $glossary['fields'] ) ? $glossary['fields'] : array();
									$options = array_map( 'strtolower', array_column( $fields, 'value' ) );
								}
							} else {
								$options = isset( $wpssw_products_field['options'] ) ? $wpssw_products_field['options'] : array();
								$options = array_map( 'strtolower', array_column( $options, 'key' ) );
							}
							$wpssw_value   = array();
							$options_value = array_map( 'trim', $options_value );
							foreach ( $options_value as $option_value ) {
								$header_val = strtolower( $option_value );
								if ( in_array( $header_val, $options, true ) ) {
									$wpssw_value[] = $option_value;
								}
							}

							if ( ! isset( $wpssw_products_field['is_multiple'] ) || 1 !== (int) $wpssw_products_field['is_multiple'] ) {
								$wpssw_update_value = isset( $wpssw_value[0] ) ? $wpssw_value[0] : '';
								update_post_meta( $wpssw_productid, $wpssw_field_name, $wpssw_update_value );
								return;
							}

							update_post_meta( $wpssw_productid, $wpssw_field_name, $wpssw_value );

							return;
						}
					}
				}

				update_post_meta( $wpssw_productid, $wpssw_field_name, $wpssw_header_val );
			}
		}

		/**
		 * Get ACF field value.
		 *
		 * @param int    $product_id .
		 * @param string $wpssw_field_name name of the acf field.
		 * @param bool   $updatepost update action or not.
		 */
		public static function wpssw_get_field_value( $product_id, $wpssw_field_name, $updatepost ) {
			$wpssw_value = '';
			if ( $updatepost ) {
				// @codingStandardsIgnoreStart.
				if ( ! empty( $wpssw_field_name ) && isset( $_POST[$wpssw_field_name] ) ) {
					$wpssw_value = is_array($_POST[$wpssw_field_name]) ? WPSSW_Setting::wpssw_sanitize_values( $_POST[$wpssw_field_name] ) : sanitize_text_field( wp_unslash( $_POST[$wpssw_field_name] ) );
					// @codingStandardsIgnoreEnd.
				}
			} elseif ( ! empty( $product_id ) && ! empty( $wpssw_field_name ) ) {
				$wpssw_value = get_post_meta( $product_id, $wpssw_field_name, true );
			}
			return $wpssw_value;
		}
	}
	new WPSSW_Jet_Engine();
endif;
