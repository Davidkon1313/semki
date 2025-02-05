<?php
/**
 * Main WPSyncSheets_For_WooCommerce namespace.
 *
 * @since 1.0.0
 * @package wpsyncsheets-for-woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }
if ( ! class_exists( 'WPSSW_Default_Headers' ) ) :
	/**
	 * Class WPSSW_Default_Headers.
	 */
	class WPSSW_Default_Headers extends WPSSW_Product_Utils {
		/**
		 * Store header list.
		 *
		 * @var array $wpssw_headers.
		 */
		public static $wpssw_headers = array();
		/**
		 * Class Contructor.
		 */
		public function __construct() {
			$this->prepare_headers();

			add_filter( 'wpsyncsheets_product_headers', __CLASS__ . '::get_header_list', 10, 1 );
		}
		/**
		 * Prepare Header List.
		 */
		protected function prepare_headers() {

			$wpssw_wooproduct_headers['common'] = array(
				'post_title'               => 'Product Name',

				'post_status'              => 'Product Status',

				'post_name'                => 'Product Slug',

				'post_link'                => 'Product Link',

				'category_ids'             => 'Product Categories',

				'product_category_ids'     => 'Product Category Ids',

				'post_excerpt'             => 'Product Short Description',

				'post_content'             => 'Product Description',

				'_sku'                     => 'Product SKU',

				'type'                     => 'Product Type',

				'raw_image'                => 'Product Image',

				'product_image_preview'    => 'Product Image Preview',

				'raw_gallery_image'        => 'Product Gallery Images',

				'tag_ids'                  => 'Product Tags',

				'attributes'               => 'Product Attribute',

				'_regular_price'           => 'Product Regular Price',

				'_sale_price'              => 'Product Sale Price',

				'_sale_price_dates_from'   => 'Product Sale Price Dates From',

				'_sale_price_dates_to'     => 'Product Sale Price Dates To',

				'_stock'                   => 'Product Stock',

				'_stock_status'            => 'Product Stock Status',

				'_weight'                  => 'Product Weight',

				'_height'                  => 'Product Height',

				'_width'                   => 'Product Width',

				'_length'                  => 'Product Length',

				'_dimensions'              => 'Product Dimensions',

				'_sold_individually'       => 'Product Sold Individually',

				'_manage_stock'            => 'Manage Product Stock',

				'_shipping_class_id'       => 'Product Shipping Class',

				'_tax_status'              => 'Product Tax Status',

				'_tax_class'               => 'Product Tax Class',

				'_virtual'                 => 'Virtual Product',

				'post_date'                => 'Product Created Date',

				'date_modified'            => 'Product Modified Date',

				'total_sales'              => 'Product Total Sale',

				'_purchase_note'           => 'Product Purchase Note',

				'_downloadable'            => 'Downloadable Product',

				'_downloadable_files'      => 'Product Downloadable Files',

				'_downloadable_file_names' => 'Product Downloadable File Names',

				'_download_limit'          => 'Product Download Limit',

				'_download_expiry'         => 'Product Download Expiry',

				'_backorders'              => 'Product Allow Backorders?',

				'catalog_visibility'       => 'Product Catalog Visibility',

				'featured'                 => 'Is Featured Product',

				'_menu_order'              => 'Product Menu Order',

			);
			$wpssw_wooproduct_headers['variable'] = array(
				'_sku'                     => 'Product Variation SKU',

				'_raw_image'               => 'Product Variation Image',

				'variation_image_preview'  => 'Product Variation Image Preview',

				'_regular_price'           => 'Product Variation Regular Price',

				'_sale_price'              => 'Product Variation Sale Price',

				'_sale_price_dates_from'   => 'Product Variation Sale Price Dates From',

				'_sale_price_dates_to'     => 'Product Variation Sale Price Dates To',

				'_stock'                   => 'Product Variation Stock',

				'_stock_status'            => 'Product Variation Stock Status',

				'_weight'                  => 'Product Variation Weight',

				'dimensions'               => 'Product Variation Dimensions',

				'_manage_stock'            => 'Product Variation Manage Product Stock',

				'_shipping_class_id'       => 'Product Variation Shipping Class',

				'_virtual'                 => 'Virtual Product Variation',

				'_variation_description'   => 'Product Variation Description',

				'_downloadable'            => 'Downloadable Product Variation',

				'_downloadable_files'      => 'Product Variation Downloadable Files',

				'_downloadable_file_names' => 'Product Variation Downloadable File Names',

				'_download_limit'          => 'Product Variation Download Limit',

				'_download_expiry'         => 'Product Variation Download Expiry',

				'_backorders'              => 'Product Variation Allow Backorders?',

				'_total_sales'             => 'Product Variation Total Sale',

				'_menu_order'              => 'Product Variation Menu Order',
			);
			$wpssw_wooproduct_headers['external'] = array(
				'_product_url' => 'External Product Link',

				'_button_text' => 'External Product Button Text',
			);
			$wpssw_wooproduct_headers['grouped']  = array(
				'_children' => 'Grouped Products Ids',
			);
			// Add attributes to headers list.

			$wpssw_attribute_taxonomies                       = WPSSW_Product::wpssw_get_all_attributes();
			$wpssw_wooproduct_headers['attribute_taxonomies'] = array();
			if ( ! empty( $wpssw_attribute_taxonomies ) ) {

					$wpssw_wooproduct_headers['attribute_taxonomies'] = $wpssw_attribute_taxonomies;

					$wpssw_variation_array = preg_filter( '/^/', 'Variation: ', $wpssw_attribute_taxonomies );

					$wpssw_wooproduct_headers['variation'] = $wpssw_variation_array;

					$wpssw_wooproduct_headers['common']['attributes_used_for_variation']           = 'Use Attributes for Variations';
					$wpssw_wooproduct_headers['common']['attributes_list_used_for_variation']      = 'Attributes to use for Variations';
					$wpssw_wooproduct_headers['common']['show_attribultes_at_product_page']        = 'Show Attributes at product page';
					$wpssw_wooproduct_headers['common']['attributes_list_visible_at_product_page'] = 'Attributes visible at product page';
			}
			self::$wpssw_headers = $wpssw_wooproduct_headers;
		}
		/**
		 * Get Header List.
		 *
		 * @param array $headers .
		 * @return array $headers
		 */
		public static function get_header_list( $headers = array() ) {
			if ( ! empty( self::$wpssw_headers ) ) {
				$headers['WPSSW_Default_Headers'] = self::$wpssw_headers;
			}
			return $headers;
		}
		/**
		 * Get Product price.
		 *
		 * @param mixed int or float $price price of product to be formatted.
		 * @param array              $args argument to format price.
		 * @return mixed int or float $price
		 */
		public static function wpssw_price( $price, $args = array() ) {
			$args = apply_filters(
				'wc_price_args',
				wp_parse_args(
					$args,
					array(
						'ex_tax_label'       => false,
						'currency'           => '',
						'decimal_separator'  => wc_get_price_decimal_separator(),
						'thousand_separator' => wc_get_price_thousand_separator(),
						'decimals'           => wc_get_price_decimals(),
						'price_format'       => get_woocommerce_price_format(),
					)
				)
			);

			$original_price = $price;

			// Convert to float to avoid issues on PHP 8.
			$price = (float) $price;

			$unformatted_price = $price;
			$negative          = $price < 0;

			/**
			 * Filter raw price.
			 *
			 * @param float        $raw_price      Raw price.
			 * @param float|string $original_price Original price as float, or empty string. Since 5.0.0.
			 */
			$price = $negative ? $price * -1 : $price;
			if ( $args['decimals'] < 1 ) {
				$args['decimals'] = apply_filters( 'wpssw_price_number_decimal', 2 );
			}

			/**
			 * Filter formatted price.
			 *
			 * @param float        $formatted_price    Formatted price.
			 * @param float        $price              Unformatted price.
			 * @param int          $decimals           Number of decimals.
			 * @param string       $decimal_separator  Decimal separator.
			 * @param string       $thousand_separator Thousand separator.
			 * @param float|string $original_price     Original price as float, or empty string. Since 5.0.0.
			 */
			$price = apply_filters( 'formatted_woocommerce_price', number_format( $price, $args['decimals'], $args['decimal_separator'], $args['thousand_separator'] ), $price, $args['decimals'], $args['decimal_separator'], $args['thousand_separator'], $original_price );

			if ( apply_filters( 'woocommerce_price_trim_zeros', false ) && $args['decimals'] > 0 ) {
				$price = wc_trim_zeros( $price );
			}
			return $price;
		}
		/**
		 * Get Value for given header name.
		 *
		 * @param string $wpssw_headers_name Header name.
		 * @param object $wpssw_product product object.
		 * @param bool   $wpssw_child true if child product.
		 * @param array  $wpssw_custom_value custom value.
		 */
		public static function get_value( $wpssw_headers_name, $wpssw_product, $wpssw_child, $wpssw_custom_value = array() ) {
			return self::prepare_value( $wpssw_headers_name, $wpssw_product, $wpssw_child, $wpssw_custom_value );
		}
		/**
		 * Prepare Value for given header name.
		 *
		 * @param string $wpssw_headers_name Header name.
		 * @param object $wpssw_product product object.
		 * @param bool   $wpssw_child true if child product.
		 * @param array  $wpssw_custom_value custom value.
		 */
		public static function prepare_value( $wpssw_headers_name, $wpssw_product, $wpssw_child, $wpssw_custom_value ) {

			$wpssw_value                = '';
			$wpssw_headerlist           = self::get_header_list();
			$wpssw_attribute_taxonomies = isset( $wpssw_headerlist['WPSSW_Default_Headers']['attribute_taxonomies'] ) ? $wpssw_headerlist['WPSSW_Default_Headers']['attribute_taxonomies'] : array();
			if ( ! is_array( $wpssw_attribute_taxonomies ) ) {
				$wpssw_attribute_taxonomies = array();
			}
			$wpssw_variation_array = array();

			if ( is_array( $wpssw_attribute_taxonomies ) && ! empty( $wpssw_attribute_taxonomies ) ) {
				$wpssw_variation_array = preg_filter( '/^/', 'Variation: ', $wpssw_attribute_taxonomies );
			}

			/** Static header dropdown wpssw_static_header_name */
			if ( ! empty( $wpssw_custom_value ) ) {
				$wpssw_static_header_name = array_column( $wpssw_custom_value, 0 );

				if ( in_array( $wpssw_headers_name, $wpssw_static_header_name, true ) ) {
					$search_key         = array_search( $wpssw_headers_name, $wpssw_static_header_name, true );
					$wpssw_search_array = $wpssw_custom_value[ $search_key ];

					if ( 'blank' === (string) $wpssw_search_array[1] ) {
						$wpssw_insert_val = Google_Model::NULL_VALUE;
						$wpssw_value      = $wpssw_insert_val;
						return $wpssw_value;
					}
				}
			}

			if ( 'Product Name' === (string) $wpssw_headers_name ) {
				if ( preg_match( '/variation/', (string) $wpssw_product->get_type() ) && ! empty( $wpssw_product->get_children() ) && true === (bool) $wpssw_child ) {
					$wpssw_value = $wpssw_product->get_name();
				} else {
					$wpssw_value = $wpssw_product->get_title();
				}
				return $wpssw_value;
			}
			if ( 'Product Short Description' === (string) $wpssw_headers_name && true !== (bool) $wpssw_child ) {
				$wpssw_value = $wpssw_product->get_short_description();
				return $wpssw_value;
			}
			if ( 'Product Description' === (string) $wpssw_headers_name && true !== (bool) $wpssw_child ) {

				if ( ! preg_match( '/variation/', (string) $wpssw_product->get_type() ) ) {
					$wpssw_value = $wpssw_product->get_description();
				}
				return $wpssw_value;
			}
			if ( 'Product Status' === (string) $wpssw_headers_name && true !== (bool) $wpssw_child ) {

				if ( ! preg_match( '/variation/', (string) $wpssw_product->get_type() ) ) {
					$wpssw_value = $wpssw_product->get_status();
				}
				return $wpssw_value;
			}
			if ( 'Product Slug' === (string) $wpssw_headers_name && true !== (bool) $wpssw_child ) {
				$wpssw_value = $wpssw_product->get_slug();
				return $wpssw_value;
			}
			if ( 'Product Link' === (string) $wpssw_headers_name && true !== (bool) $wpssw_child ) {

				if ( preg_match( '/variation/', (string) $wpssw_product->get_type() ) && ! empty( $wpssw_product->get_children() ) ) {
					$wpssw_value = get_permalink( $wpssw_product->get_id() );
				} else {
					$wpssw_value = get_permalink( $wpssw_product->get_id() );
				}
				return $wpssw_value;
			}
			if ( ( 'Product Categories' === (string) $wpssw_headers_name || 'Product Category Ids' === (string) $wpssw_headers_name ) && true !== (bool) $wpssw_child ) {
				if ( ! empty( $wpssw_product->get_parent_id() ) && 'grouped' !== (string) $wpssw_product->get_type() ) {
					$pid = $wpssw_product->get_parent_id();
				} else {
					$pid = $wpssw_product->get_id();
				}
				if ( 'Product Category Ids' === (string) $wpssw_headers_name ) {
					$product_cats = wp_get_post_terms( $pid, 'product_cat', array( 'fields' => 'ids' ) );
					if ( is_array( $product_cats ) && ! empty( $product_cats ) ) {
						sort( $product_cats );
					}
				} else {
					$product_cats = wp_get_post_terms( $pid, 'product_cat', array( 'fields' => 'names' ) );
				}
				$product_category = array();
				if ( is_array( $product_cats ) && ! empty( $product_cats ) ) {
					$product_category = $product_cats;
				}

				if ( ! preg_match( '/variation/', (string) $wpssw_product->get_type() ) ) {
					$wpssw_value = implode( ', ', $product_category );
				}
				return $wpssw_value;
			}
			if ( 'Product SKU' === (string) $wpssw_headers_name && true !== (bool) $wpssw_child ) {

				if ( ! preg_match( '/variation/', (string) $wpssw_product->get_type() ) ) {
					$wpssw_value = $wpssw_product->get_sku();
				}
				return $wpssw_value;
			}
			if ( 'Product Type' === (string) $wpssw_headers_name ) {
				$wpssw_value = $wpssw_product->get_type();
				return $wpssw_value;
			}
			if ( ( 'Product Image' === (string) $wpssw_headers_name || 'Product Image Preview' === (string) $wpssw_headers_name || 'Product Gallery Images' === (string) $wpssw_headers_name ) && true !== (bool) $wpssw_child ) {
				$attachment[0]    = '';
				$image_attachment = array();
				if ( has_post_thumbnail( $wpssw_product->get_id() ) ) {
					$attachment_ids = get_post_thumbnail_id( $wpssw_product->get_id() );
					$attachment     = wp_get_attachment_image_src( $attachment_ids, 'full' );
					if ( ! isset( $attachment[0] ) ) {
						$attachment[0] = '';
					}
					if ( 'Product Image Preview' === (string) $wpssw_headers_name ) {
						$image_attachment[] = '=IMAGE("' . $attachment[0] . '")';
					} else {
						$image_attachment[] = $attachment[0];
					}
				}
				if ( 'Product Gallery Images' === (string) $wpssw_headers_name ) {
					$gallery_image_ids = $wpssw_product->get_gallery_image_ids();
					$gallery_image_ids = array_filter( $gallery_image_ids );
					if ( ! empty( $gallery_image_ids ) ) {
						foreach ( $gallery_image_ids as $gallery_image ) {
							$gallery_attachment = wp_get_attachment_image_src( $gallery_image, 'full' );

							if ( isset( $gallery_attachment[0] ) ) {
								$image_attachment[] = $gallery_attachment[0];
							}
						}
					}
				}
				if ( is_array( $image_attachment ) && ! empty( $image_attachment ) ) {
					$images = implode( '|', $image_attachment );
				} else {
					$images = '';
				}
				$wpssw_value = $images;
				return $wpssw_value;
			}
			if ( 'Product Tags' === (string) $wpssw_headers_name && true !== (bool) $wpssw_child ) {
				$taglist = array();
				// get an array of the WP_Term objects for a defined product ID.
				$terms = wp_get_post_terms( $wpssw_product->get_id(), 'product_tag' );
				// Loop through each product tag for the current product.
				if ( count( $terms ) > 0 ) {
					foreach ( $terms as $term ) {
						$term_name = $term->name; // Product tag Name.
						// Set the product tag names in an array.
						$taglist[] = $term_name;
					}
				}
				$wpssw_value = implode( ', ', $taglist );
				return $wpssw_value;
			}
			if ( 'Product Attribute' === (string) $wpssw_headers_name && true !== (bool) $wpssw_child ) {
				$attributes_value = array();
				$attributes       = $wpssw_product->get_attributes();
				foreach ( $attributes as $attrkey => $attrvalue ) {
					if ( $attrvalue->get_id() < 1 && false === strpos( $attrkey, 'pa_' ) ) {
						$attributes_value[] = $attrvalue->get_name();
					} else {
						$attrkey            = rawurldecode( $attrkey );
						$attributes_value[] = wc_attribute_label( $attrkey );
					}
				}

				if ( ! preg_match( '/variation/', (string) $wpssw_product->get_type() ) ) {
					$wpssw_value = implode( '|', $attributes_value );
				}
				return $wpssw_value;
			}
			if ( 'Product Regular Price' === (string) $wpssw_headers_name && true !== (bool) $wpssw_child ) {
				$wpssw_value = $wpssw_product->get_regular_price();
				$wpssw_value = self::wpssw_price( $wpssw_value );
				return $wpssw_value;
			}
			if ( 'Product Sale Price' === (string) $wpssw_headers_name && true !== (bool) $wpssw_child ) {
				$wpssw_value = $wpssw_product->get_sale_price();
				$wpssw_value = self::wpssw_price( $wpssw_value );
				return $wpssw_value;
			}
			if ( 'Product Sale Price Dates From' === (string) $wpssw_headers_name && true !== (bool) $wpssw_child ) {
				$date_from = '';
				if ( $wpssw_product->get_date_on_sale_from() ) {
					$date_from = $wpssw_product->get_date_on_sale_from()->format( WPSSW_Setting::wpssw_option( 'date_format' ) );
				}
				$wpssw_value = $date_from;
				return $wpssw_value;
			}
			if ( 'Product Sale Price Dates To' === (string) $wpssw_headers_name && true !== (bool) $wpssw_child ) {
				$date_to = '';
				if ( $wpssw_product->get_date_on_sale_to() ) {
					$date_to = $wpssw_product->get_date_on_sale_to()->format( WPSSW_Setting::wpssw_option( 'date_format' ) );
				}
				$wpssw_value = $date_to;
				return $wpssw_value;
			}
			if ( 'Product Stock' === (string) $wpssw_headers_name && true !== (bool) $wpssw_child ) {
				$wpssw_value = $wpssw_product->get_stock_quantity();
				return $wpssw_value;
			}
			if ( 'Product Stock Status' === (string) $wpssw_headers_name && true !== (bool) $wpssw_child ) {
				$wpssw_value = $wpssw_product->get_stock_status();
				return $wpssw_value;
			}
			if ( 'Product Weight' === (string) $wpssw_headers_name && true !== (bool) $wpssw_child ) {
				$wpssw_value = $wpssw_product->get_weight();
				return $wpssw_value;
			}
			if ( 'Product Height' === (string) $wpssw_headers_name && true !== (bool) $wpssw_child ) {
				$wpssw_value = $wpssw_product->get_height();
				return $wpssw_value;
			}
			if ( 'Product Width' === (string) $wpssw_headers_name && true !== (bool) $wpssw_child ) {
				$wpssw_value = $wpssw_product->get_width();
				return $wpssw_value;
			}
			if ( 'Product Length' === (string) $wpssw_headers_name && true !== (bool) $wpssw_child ) {
				$wpssw_value = $wpssw_product->get_length();
				return $wpssw_value;
			}
			if ( 'Product Total Sale' === (string) $wpssw_headers_name && true !== (bool) $wpssw_child ) {
				$wpssw_value = $wpssw_product->get_total_sales();
				return $wpssw_value;
			}
			if ( 'Product Purchase Note' === (string) $wpssw_headers_name && true !== (bool) $wpssw_child ) {
				$wpssw_value = $wpssw_product->get_purchase_note();
				return $wpssw_value;
			}
			if ( 'Product Dimensions' === (string) $wpssw_headers_name && true !== (bool) $wpssw_child ) {
				$wpssw_value = html_entity_decode( wc_format_dimensions( $wpssw_product->get_dimensions( false ) ), ENT_QUOTES );
				return $wpssw_value;
			}
			if ( 'Product Sold Individually' === (string) $wpssw_headers_name && true !== (bool) $wpssw_child ) {
				$is_sold_ind = 'No';
				if ( $wpssw_product->get_sold_individually() ) {
					$is_sold_ind = 'Yes';
				}
				$wpssw_value = $is_sold_ind;
				return $wpssw_value;
			}
			if ( 'Manage Product Stock' === (string) $wpssw_headers_name && true !== (bool) $wpssw_child ) {
				$is_manage = 'No';
				if ( $wpssw_product->get_manage_stock() ) {
					$is_manage = 'Yes';
				}
				$wpssw_value = $is_manage;
				return $wpssw_value;
			}
			if ( 'Product Shipping Class' === (string) $wpssw_headers_name && true !== (bool) $wpssw_child ) {
				$wpssw_value = $wpssw_product->get_shipping_class_id();
				return $wpssw_value;
			}
			if ( 'Product Tax Status' === (string) $wpssw_headers_name && true !== (bool) $wpssw_child ) {
				$wpssw_value = $wpssw_product->get_tax_status();
				return $wpssw_value;
			}
			if ( 'Product Tax Class' === (string) $wpssw_headers_name && true !== (bool) $wpssw_child ) {
				$wpssw_value = $wpssw_product->get_tax_class();
				return $wpssw_value;
			}
			if ( 'Virtual Product' === (string) $wpssw_headers_name && true !== (bool) $wpssw_child ) {
				$is_virtual = 'No';
				if ( $wpssw_product->get_virtual() ) {
					$is_virtual = 'Yes';
				}
				$wpssw_value = $is_virtual;
				return $wpssw_value;
			}
			if ( 'Product Created Date' === (string) $wpssw_headers_name && true !== (bool) $wpssw_child ) {
				if ( null !== $wpssw_product->get_date_created() ) {

					if ( ! preg_match( '/variation/', (string) $wpssw_product->get_type() ) ) {
						$wpssw_value = $wpssw_product->get_date_created()->format( WPSSW_Setting::wpssw_option( 'date_format' ) );
					}
				} else {
					$wpssw_value = '';
				}
				return $wpssw_value;
			}
			if ( 'Product Modified Date' === (string) $wpssw_headers_name && true !== (bool) $wpssw_child ) {

				if ( ! preg_match( '/variation/', (string) $wpssw_product->get_type() ) ) {
					if ( $wpssw_product->get_date_modified() ) {
						$wpssw_value = $wpssw_product->get_date_modified()->format( WPSSW_Setting::wpssw_option( 'date_format' ) );
					} else {
						$wpssw_value = '';
					}
				}
				return $wpssw_value;
			}
			if ( 'Downloadable Product' === (string) $wpssw_headers_name ) {
				$is_downloadable = 'No';
				if ( 'simple' === (string) $wpssw_product->get_type() ) {
					if ( $wpssw_product->get_downloadable() ) {
						$is_downloadable = 'Yes';
					}
				}
				$wpssw_value = $is_downloadable;
				return $wpssw_value;
			}
			if ( 'Product Downloadable Files' === (string) $wpssw_headers_name || 'Product Downloadable File Names' === (string) $wpssw_headers_name ) {
				if ( 'simple' === (string) $wpssw_product->get_type() ) {
					$file  = array();
					$files = '';
					if ( ! empty( $wpssw_product->get_downloads() ) ) {
						foreach ( $wpssw_product->get_downloads() as $downloads ) {
							$file[]      = $downloads->get_file();
							$file_name[] = $downloads->get_name();
						}
						if ( 'Product Downloadable File Names' === (string) $wpssw_headers_name ) {
							$file = $file_name;
						}
						$files = implode( ', ', $file );
					}
					$wpssw_value = $files;
				} else {
					$wpssw_value = '';
				}
				return $wpssw_value;
			}
			if ( 'Product Download Limit' === (string) $wpssw_headers_name ) {
				if ( 'simple' === (string) $wpssw_product->get_type() ) {
					if ( -1 === (int) $wpssw_product->get_download_limit() ) {
						$wpssw_value = 'Unlimited';
					} else {
						$wpssw_value = $wpssw_product->get_download_limit();
					}
				} else {
					$wpssw_value = '';
				}
				return $wpssw_value;
			}
			if ( 'Product Download Expiry' === (string) $wpssw_headers_name ) {
				if ( 'simple' === (string) $wpssw_product->get_type() ) {
					if ( -1 === (int) $wpssw_product->get_download_expiry() ) {
						$wpssw_value = 'Never';
					} else {
						$wpssw_value = $wpssw_product->get_download_expiry();
					}
				} else {
					$wpssw_value = '';
				}
				return $wpssw_value;
			}
			if ( 'Product Allow Backorders?' === (string) $wpssw_headers_name ) {
				if ( $wpssw_product->get_backorders() ) {
					$wpssw_value = $wpssw_product->get_backorders();
				} else {
					$wpssw_value = '';
				}
				return $wpssw_value;
			}
			if ( 'Product Variation SKU' === (string) $wpssw_headers_name ) {

				if ( preg_match( '/variation/', (string) $wpssw_product->get_type() ) ) {
					$wpssw_value = $wpssw_product->get_sku();
				} else {
					$wpssw_value = '';
				}
				return $wpssw_value;
			}
			if ( 'Product Variation Image' === (string) $wpssw_headers_name || 'Product Variation Image Preview' === (string) $wpssw_headers_name ) {
				$vattachment[0] = '';

				if ( preg_match( '/variation/', (string) $wpssw_product->get_type() ) ) {
					if ( has_post_thumbnail( $wpssw_product->get_id() ) ) {
						$vattachment_ids = get_post_thumbnail_id( $wpssw_product->get_id() );
						$vattachment     = wp_get_attachment_image_src( $vattachment_ids, 'full' );
						if ( ! isset( $vattachment[0] ) ) {
							$vattachment[0] = '';
						}
					}
				}
				if ( 'Product Variation Image Preview' === (string) $wpssw_headers_name ) {
					$wpssw_value = '=IMAGE("' . $vattachment[0] . '")';
				} else {
					$wpssw_value = $vattachment[0];
				}
				return $wpssw_value;
			}
			if ( 'Product Variation Regular Price' === (string) $wpssw_headers_name ) {

				if ( preg_match( '/variation/', (string) $wpssw_product->get_type() ) ) {
					$wpssw_value = $wpssw_product->get_regular_price();
					$wpssw_value = self::wpssw_price( $wpssw_value );
				} else {
					$wpssw_value = '';
				}
				return $wpssw_value;
			}
			if ( 'Product Variation Sale Price' === (string) $wpssw_headers_name ) {

				if ( preg_match( '/variation/', (string) $wpssw_product->get_type() ) ) {
					$wpssw_value = $wpssw_product->get_sale_price();
					$wpssw_value = self::wpssw_price( $wpssw_value );
				} else {
					$wpssw_value = '';
				}
				return $wpssw_value;
			}
			if ( 'Product Variation Sale Price Dates From' === (string) $wpssw_headers_name ) {
				$date_from = '';

				if ( preg_match( '/variation/', (string) $wpssw_product->get_type() ) ) {
					if ( $wpssw_product->get_date_on_sale_from() ) {
						$date_from = $wpssw_product->get_date_on_sale_from()->format( WPSSW_Setting::wpssw_option( 'date_format' ) );
					}
				}
				$wpssw_value = $date_from;
				return $wpssw_value;
			}
			if ( 'Product Variation Sale Price Dates To' === (string) $wpssw_headers_name ) {
				$date_to = '';

				if ( preg_match( '/variation/', (string) $wpssw_product->get_type() ) ) {
					if ( $wpssw_product->get_date_on_sale_to() ) {
						$date_to = $wpssw_product->get_date_on_sale_to()->format( WPSSW_Setting::wpssw_option( 'date_format' ) );
					}
				}
				$wpssw_value = $date_to;
				return $wpssw_value;
			}
			if ( 'Product Variation Stock' === (string) $wpssw_headers_name ) {

				if ( preg_match( '/variation/', (string) $wpssw_product->get_type() ) ) {
					$wpssw_value = $wpssw_product->get_stock_quantity();
				} else {
					$wpssw_value = '';
				}
				return $wpssw_value;
			}
			if ( 'Product Variation Stock Status' === (string) $wpssw_headers_name ) {

				if ( preg_match( '/variation/', (string) $wpssw_product->get_type() ) ) {
					$wpssw_value = $wpssw_product->get_stock_status();
				} else {
					$wpssw_value = '';
				}
				return $wpssw_value;
			}
			if ( 'Product Variation Weight' === (string) $wpssw_headers_name ) {

				if ( preg_match( '/variation/', (string) $wpssw_product->get_type() ) ) {
					$wpssw_value = $wpssw_product->get_weight();
				} else {
					$wpssw_value = '';
				}
				return $wpssw_value;
			}
			if ( 'Product Variation Dimensions' === (string) $wpssw_headers_name ) {

				if ( preg_match( '/variation/', (string) $wpssw_product->get_type() ) ) {
					$wpssw_value = html_entity_decode( wc_format_dimensions( $wpssw_product->get_dimensions( false ) ), ENT_QUOTES );
				} else {
					$wpssw_value = '';
				}
				return $wpssw_value;
			}
			if ( 'Product Variation Manage Product Stock' === (string) $wpssw_headers_name ) {
				$is_manage = 'No';

				if ( preg_match( '/variation/', (string) $wpssw_product->get_type() ) ) {
					if ( $wpssw_product->get_manage_stock() ) {
						$is_manage = 'Yes';
					}
				}
				$wpssw_value = $is_manage;
				return $wpssw_value;
			}
			if ( 'Product Variation Shipping Class' === (string) $wpssw_headers_name ) {

				if ( preg_match( '/variation/', (string) $wpssw_product->get_type() ) ) {
					$wpssw_value = $wpssw_product->get_shipping_class_id();
				} else {
					$wpssw_value = '';
				}
				return $wpssw_value;
			}
			if ( 'Virtual Product Variation' === (string) $wpssw_headers_name ) {
				$is_virtual = 'No';

				if ( preg_match( '/variation/', (string) $wpssw_product->get_type() ) ) {
					if ( $wpssw_product->get_virtual() ) {
						$is_virtual = 'Yes';
					}
				}
				$wpssw_value = $is_virtual;
				return $wpssw_value;
			}
			if ( 'Product Variation Description' === (string) $wpssw_headers_name ) {

				if ( preg_match( '/variation/', (string) $wpssw_product->get_type() ) ) {
					$wpssw_value = $wpssw_product->get_description();
				} else {
					$wpssw_value = '';
				}
				return $wpssw_value;
			}
			if ( 'Downloadable Product Variation' === (string) $wpssw_headers_name ) {
				$is_downloadable = 'No';

				if ( preg_match( '/variation/', (string) $wpssw_product->get_type() ) ) {
					if ( $wpssw_product->get_downloadable() ) {
						$is_downloadable = 'Yes';
					}
				}
				$wpssw_value = $is_downloadable;
				return $wpssw_value;
			}
			if ( 'Product Variation Downloadable Files' === (string) $wpssw_headers_name || 'Product Variation Downloadable File Names' === (string) $wpssw_headers_name ) {

				if ( preg_match( '/variation/', (string) $wpssw_product->get_type() ) ) {
					$file  = array();
					$files = '';
					if ( ! empty( $wpssw_product->get_downloads() ) ) {
						foreach ( $wpssw_product->get_downloads() as $downloads ) {
							$file[]      = $downloads->get_file();
							$file_name[] = $downloads->get_name();
						}
						if ( 'Product Variation Downloadable File Names' === (string) $wpssw_headers_name ) {
							$file = $file_name;
						}
						$files = implode( ', ', $file );
					}
					$wpssw_value = $files;
				} else {
					$wpssw_value = '';
				}
				return $wpssw_value;
			}
			if ( 'Product Variation Download Limit' === (string) $wpssw_headers_name ) {

				if ( preg_match( '/variation/', (string) $wpssw_product->get_type() ) ) {
					if ( -1 === (int) $wpssw_product->get_download_limit() ) {
						$wpssw_value = 'Unlimited';
					} else {
						$wpssw_value = $wpssw_product->get_download_limit();
					}
				} else {
					$wpssw_value = '';
				}
				return $wpssw_value;
			}
			if ( 'Product Variation Download Expiry' === (string) $wpssw_headers_name ) {

				if ( preg_match( '/variation/', (string) $wpssw_product->get_type() ) ) {
					if ( -1 === (int) $wpssw_product->get_download_expiry() ) {
						$wpssw_value = 'Never';
					} else {
						$wpssw_value = $wpssw_product->get_download_expiry();
					}
				} else {
					$wpssw_value = '';
				}
				return $wpssw_value;
			}
			if ( 'Grouped Products Ids' === (string) $wpssw_headers_name ) {
				if ( 'grouped' === (string) $wpssw_product->get_type() ) {
					$wpssw_value = implode( ', ', $wpssw_product->get_children() );
				} else {
					$wpssw_value = '';
				}
				return $wpssw_value;
			}
			if ( 'Product Variation Allow Backorders?' === (string) $wpssw_headers_name ) {

				if ( preg_match( '/variation/', (string) $wpssw_product->get_type() ) ) {
					$wpssw_value = $wpssw_product->get_backorders();
				} else {
					$wpssw_value = '';
				}
				return $wpssw_value;
			}
			if ( 'Product Variation Total Sale' === (string) $wpssw_headers_name ) {

				if ( preg_match( '/variation/', (string) $wpssw_product->get_type() ) ) {
					$wpssw_value = $wpssw_product->get_total_sales();
				} else {
					$wpssw_value = '';
				}
				return $wpssw_value;
			}
			if ( 'External Product Button Text' === (string) $wpssw_headers_name ) {
				if ( 'external' === (string) $wpssw_product->get_type() ) {
					$wpssw_value = $wpssw_product->get_button_text();
				} else {
					$wpssw_value = '';
				}
				return $wpssw_value;
			}
			if ( 'External Product Link' === (string) $wpssw_headers_name ) {
				if ( 'external' === (string) $wpssw_product->get_type() ) {
					$wpssw_value = $wpssw_product->get_product_url();
				} else {
					$wpssw_value = '';
				}
				return $wpssw_value;
			}
			if ( 'Use Attributes for Variations' === (string) $wpssw_headers_name ) {

				if ( preg_match( '/variable/', (string) $wpssw_product->get_type() ) ) {
					$val = get_post_meta( $wpssw_product->get_id(), 'attributes_used_for_variation', true );
					if ( ! $val ) {
						$wpssw_value = 'Yes';
					} else {
						$wpssw_value = ucfirst( $val );
					}
				} elseif ( true === (bool) $wpssw_child ) {
					$wpssw_value = '';
				} else {
					$wpssw_value = 'No';
				}
				return $wpssw_value;
			}

			if ( 'Attributes to use for Variations' === (string) $wpssw_headers_name ) {
				$wpssw_value = '';

				if ( preg_match( '/variable/', (string) $wpssw_product->get_type() ) ) {
					$attributes_used_for_variation = get_post_meta( $wpssw_product->get_id(), 'attributes_used_for_variation', true );

					if ( '' === (string) $attributes_used_for_variation || 'yes' === strtolower( $attributes_used_for_variation ) ) {
						$attributes       = $wpssw_product->get_attributes();
						$attributes_value = array();
						foreach ( $attributes as $attrkey => $attrvalue ) {
							if ( isset( $attrvalue['variation'] ) && 1 === (int) $attrvalue['variation'] ) {
								if ( $attrvalue->get_id() < 1 && false === strpos( $attrkey, 'pa_' ) ) {
									$attributes_value[] = $attrvalue->get_name();
								} else {
									$attrkey            = rawurldecode( $attrkey );
									$attributes_value[] = wc_attribute_label( $attrkey );
								}
							}
						}
						$wpssw_value = implode( ' | ', $attributes_value );
					}
				} else {
					$wpssw_value = '';
				}
				return $wpssw_value;
			}
			if ( 'Show Attributes at product page' === (string) $wpssw_headers_name ) {

				if ( true === (bool) $wpssw_child ) {
					$wpssw_value = '';
				} else {
					$val = get_post_meta( $wpssw_product->get_id(), 'show_attribultes_at_product_page', true );
					if ( ! $val ) {
						$wpssw_value = 'Yes';
					} else {
						$wpssw_value = ucfirst( $val );
					}
				}
				return $wpssw_value;
			}
			if ( 'Attributes visible at product page' === (string) $wpssw_headers_name ) {
				$wpssw_value = '';
				if ( true === (bool) $wpssw_child ) {
					$wpssw_value = '';
				} else {
					$show_attributes = get_post_meta( $wpssw_product->get_id(), 'show_attribultes_at_product_page', true );

					if ( '' === (string) $show_attributes || 'yes' === strtolower( $show_attributes ) ) {
						$attributes       = $wpssw_product->get_attributes();
						$attributes_value = array();
						foreach ( $attributes as $attrkey => $attrvalue ) {
							if ( isset( $attrvalue['visible'] ) && 1 === (int) $attrvalue['visible'] ) {
								if ( $attrvalue->get_id() < 1 && false === strpos( $attrkey, 'pa_' ) ) {
									$attributes_value[] = $attrvalue->get_name();
								} else {
									$attrkey            = rawurldecode( $attrkey );
									$attributes_value[] = wc_attribute_label( $attrkey );
								}
							}
						}
						$wpssw_value = implode( ' | ', $attributes_value );
					} else {
						$wpssw_value = '';
					}
				}
				return $wpssw_value;
			}
			if ( 'Product Catalog Visibility' === (string) $wpssw_headers_name ) {
				$wpssw_value = '';

				if ( ! preg_match( '/variation/', (string) $wpssw_product->get_type() ) ) {
					$all_visibilities   = wc_get_product_visibility_options();
					$catalog_visibility = $wpssw_product->get_catalog_visibility();
					if ( ! empty( $catalog_visibility ) && array_key_exists( $catalog_visibility, $all_visibilities ) ) {
						$wpssw_value = $all_visibilities[ $catalog_visibility ];
					}
				}
				return $wpssw_value;
			}

			if ( 'Is Featured Product' === (string) $wpssw_headers_name ) {
				$wpssw_value = '';

				if ( ! preg_match( '/variation/', (string) $wpssw_product->get_type() ) ) {
					if ( $wpssw_product->is_featured() ) {
						$wpssw_value = 'Yes';
					} else {
						$wpssw_value = 'No';
					}
				}
				return $wpssw_value;
			}
			if ( 'Product Menu Order' === (string) $wpssw_headers_name ) {
				$wpssw_value = '';

				if ( ! preg_match( '/variation/', (string) $wpssw_product->get_type() ) ) {
					$wpssw_value = $wpssw_product->get_menu_order();
				}
				return $wpssw_value;
			}
			if ( 'Product Variation Menu Order' === (string) $wpssw_headers_name ) {
				$wpssw_value = '';

				if ( preg_match( '/variation/', (string) $wpssw_product->get_type() ) ) {
					$wpssw_value = $wpssw_product->get_menu_order();
				}
				return $wpssw_value;
			}

			if ( in_array( $wpssw_headers_name, $wpssw_attribute_taxonomies, true ) && true !== (bool) $wpssw_child ) {
				$wpssw_attributes         = $wpssw_product->get_attributes();
				$taxonomy                 = strtolower( str_replace( ' ', '-', $wpssw_headers_name ) );
				$taxonomy_terms           = array();
				$wpssw_attributes_options = array();

				if ( taxonomy_exists( wc_attribute_taxonomy_name( $taxonomy ) ) ) {

					$taxonomy_terms[ wc_attribute_taxonomy_name( $taxonomy ) ] = array_column( get_terms( wc_attribute_taxonomy_name( $taxonomy ), 'hide_empty=0' ), 'name', 'term_id' );

					foreach ( $wpssw_attributes as $att_key => $attr ) {
						$att_key = rawurldecode( $att_key );
						if ( wc_attribute_taxonomy_name( $taxonomy ) === $att_key ) {
							$options = array();
							foreach ( $attr['options'] as $option ) {
								$options[ $option ] = $taxonomy_terms[ $att_key ][ $option ];
							}
							$wpssw_attributes_options[ $att_key ] = $options;
						}
					}
					$attributes_value = isset( $wpssw_attributes_options[ 'pa_' . $taxonomy ] ) ? $wpssw_attributes_options[ 'pa_' . $taxonomy ] : array();
					$wpssw_value      = implode( ' | ', $attributes_value );
				} else {
					foreach ( $wpssw_attributes as $att_key => $attr ) {
						$att_key = rawurldecode( $att_key );
						if ( $att_key === $taxonomy ) {
							$wpssw_attributes_options[ $att_key ] = $attr['options'];
						}
					}
					$attributes_value = isset( $wpssw_attributes_options[ $taxonomy ] ) ? $wpssw_attributes_options[ $taxonomy ] : array();
					$wpssw_value      = implode( ' | ', $attributes_value );
				}
				return $wpssw_value;
			}

			if ( in_array( $wpssw_headers_name, $wpssw_variation_array, true ) && true === (bool) $wpssw_child ) {

				if ( preg_match( '/variation/', (string) $wpssw_product->get_type() ) ) {
					$wpssw_attr_name  = 'attribute_';
					$wpssw_pattr_name = 'attribute_pa_';
					$wpssw_attrname   = strtolower( trim( str_replace( 'Variation: ', '', $wpssw_headers_name ) ) );
					$wpssw_attrname   = str_replace( ' ', '-', $wpssw_attrname );
					$taxonomy         = $wpssw_attrname;

					$wpssw_attr_name         .= strtolower( rawurlencode( $wpssw_attrname ) );
					$wpssw_pattr_name        .= strtolower( rawurlencode( $wpssw_attrname ) );
					$wpssw_selected_variation = $wpssw_product->get_variation_attributes();

					if ( isset( $wpssw_selected_variation[ $wpssw_attr_name ] ) ) {
						$wpssw_value = $wpssw_selected_variation[ $wpssw_attr_name ];
					} elseif ( isset( $wpssw_selected_variation[ $wpssw_pattr_name ] ) ) {
						$wpssw_value = $wpssw_selected_variation[ $wpssw_pattr_name ];
						if ( taxonomy_exists( wc_attribute_taxonomy_name( $taxonomy ) ) && ! empty( $wpssw_value ) ) {
							$term = get_term_by( 'slug', $wpssw_value, 'pa_' . $taxonomy );

							if ( $term ) {
								$wpssw_parentproduct    = wc_get_product( $wpssw_product->parent_id );
								$wpssw_parentattributes = $wpssw_parentproduct->get_attributes();
								$options                = array();
								$attr_taxonomy          = 'pa_' . strtolower( rawurlencode( $taxonomy ) );

								if ( array_key_exists( $attr_taxonomy, $wpssw_parentattributes ) ) {
									$options = $wpssw_parentattributes[ $attr_taxonomy ]['options'];
								}
								if ( in_array( $term->term_id, $options, true ) ) {
									$wpssw_value = $term->name;
								} else {
									$wpssw_value = '';
								}
							} else {
								$wpssw_value = '';
							}
						}
					} else {
						$wpssw_value = '';
					}
					return $wpssw_value;
				}
			}
		}
	}
	new WPSSW_Default_Headers();
endif;
