<?php
/**
 * Class Column
 * includes/model/Column.php
 *
 * @package OrderSyncWithGoogleSheetForWooCommerce
 */

namespace OrderSyncWithGoogleSheetForWooCommerce;

defined( 'ABSPATH' ) || die( 'No script kiddies please!' );

if ( ! class_exists( '\OrderSyncWithGoogleSheetForWooCommerce\Column' ) ) {
	/**
	 * Column class
	 */
	class Column extends Base {
		/**
		 * Column properties
		 */
		public function get_all_columns() {
			$columns = [
				'order_id' => [
					'label' => __( 'Order ID', 'order-sync-with-google-sheets-for-woocommerce' ),
					'type'  => 'term',
				],
				'product_names' => [
					'label' => __( 'Product Names', 'order-sync-with-google-sheets-for-woocommerce' ),
					'type'  => 'meta',
				],
				'order_status' => [
					'label' => __( 'Order Status', 'order-sync-with-google-sheets-for-woocommerce' ),
					'type'  => 'meta',
				],
				'total_items' => [
					'label' => __( 'Total Items', 'order-sync-with-google-sheets-for-woocommerce' ),
					'type'  => 'meta',
				],
				'product_sku_sync' => [
					'label' => __( 'Product SKU', 'order-sync-with-google-sheets-for-woocommerce' ),
					'type'  => 'term',
				],
				'order_totals' => [
					'label'    => __( 'Total Price', 'order-sync-with-google-sheets-for-woocommerce' ),
					'type'     => 'meta',
				],
				'total_discount' => [
					'label'    => __( 'Total Discount', 'order-sync-with-google-sheets-for-woocommerce' ),
					'type'     => 'meta',
				],
				'show_billing_details' => [
					'label'    => __( 'Billing Details', 'order-sync-with-google-sheets-for-woocommerce' ),
					'type'     => 'meta',
				],
				'shipping_details' => [
					'label'    => __( 'Shipping Details', 'order-sync-with-google-sheets-for-woocommerce' ),
					'type'     => 'meta',
				],
				'order_date' => [
					'label'    => __( 'Order Date', 'order-sync-with-google-sheets-for-woocommerce' ),
					'type'     => 'meta',
				],
				'payment_method' => [
					'label'    => __( 'Payment Method', 'order-sync-with-google-sheets-for-woocommerce' ),
					'type'     => 'meta',
				],
				'customer_note' => [
					'label'    => __( 'Customer Note', 'order-sync-with-google-sheets-for-woocommerce' ),
					'type'     => 'meta',
				],
				'who_place_order' => [
					'label'    => __( 'Order Placed by', 'order-sync-with-google-sheets-for-woocommerce' ),
					'type'     => 'meta',
				],
				'order_url' => [
					'label'    => __( 'Order URL', 'order-sync-with-google-sheets-for-woocommerce' ),
					'type'     => 'meta',
				],
				'order_note' => [
					'label' => __( 'Order Note', 'order-sync-with-google-sheets-for-woocommerce' ),
					'type'  => 'term',
				],
			];
			$columns = apply_filters( 'osgsw_add_row_before_custom_fields', $columns );
			$custom_meta_value = $this->osgsw_custom_meta_fields();
			if ( ! empty( $custom_meta_value ) ) {
				$columns = $columns + $custom_meta_value;
			}
			return apply_filters( 'osgsw_columns', $columns );
		}
		/**
		 * Get Editable Columns
		 */
		public function get_columns() {
			$columns          = $this->get_all_columns();
			$total_discount   = true === wp_validate_boolean( osgsw_get_option( 'total_discount', false ) );
			$shipping_details = true === wp_validate_boolean( osgsw_get_option( 'add_shipping_details_sheet', false ) );
			$order_date       = true === wp_validate_boolean( osgsw_get_option( 'show_order_date', false ) );
			$payment_method   = true === wp_validate_boolean( osgsw_get_option( 'show_payment_method', false ) );
			$customer_note    = true === wp_validate_boolean( osgsw_get_option( 'show_customer_note', false ) );
			$order_url        = true === wp_validate_boolean( osgsw_get_option( 'show_order_url', false ) );
			$who_place_order  = true === wp_validate_boolean( osgsw_get_option( 'who_place_order', false ) );
			$total_items      = true === wp_validate_boolean( osgsw_get_option( 'sync_total_items', false ) );
			$sync_total_price = true === wp_validate_boolean( osgsw_get_option( 'sync_total_price', false ) );
			$billing_details  = true === wp_validate_boolean( osgsw_get_option( 'show_billing_details', false ) );
			$show_order_note  = true === wp_validate_boolean( osgsw_get_option( 'show_order_note', false ) );
			$custom_meta_fields  = true === wp_validate_boolean( osgsw_get_option( 'show_custom_meta_fields', false ) );
			$product_sku_sync = wp_validate_boolean( get_option( OSGSW_PREFIX . 'product_sku_sync', false ) );
			if ( ! $total_items ) {
				unset( $columns['total_items'] );
			}
			if ( ! $product_sku_sync ) {
				unset( $columns['product_sku_sync'] );
			}
			if ( ! $sync_total_price ) {
				unset( $columns['order_totals'] );
			}
			if ( ! $total_discount ) {
				unset( $columns['total_discount'] );
			}
			if ( ! $billing_details ) {
				unset( $columns['show_billing_details'] );
			}
			if ( ! $shipping_details ) {
				unset( $columns['shipping_details'] );
			}
			if ( ! $order_date ) {
				unset( $columns['order_date'] );
			}
			if ( ! $payment_method ) {
				unset( $columns['payment_method'] );
			}
			if ( ! $order_url ) {
				unset( $columns['order_url'] );
			}
			if ( ! $customer_note ) {
				unset( $columns['customer_note'] );
			}
			if ( ! $who_place_order ) {
				unset( $columns['who_place_order'] );
			}
			if ( ! $show_order_note ) {
				unset( $columns['order_note'] );
			}
			if ( ! $custom_meta_fields ) {
				unset( $columns['show_custom_meta_fields'] );
			}
			$columns = apply_filters( 'osgsw_unset_columns', $columns );

			return $columns;
		}
		/**
		 * Get Editable Column Keys
		 */
		public function get_column_keys() {
			$columns = array_filter(
				$this->get_columns(),
				function ( $column ) {
					return isset( $column['column'] ) ? $column['column'] : true;
				}
			);

			$keys = array_keys( $columns );

			return $keys;
		}

		/**
		 * Get Editable Column Values
		 */
		public function get_column_names() {
			$columns = array_filter(
				$this->get_columns(),
				function ( $column ) {
					return isset( $column['column'] ) ? $column['column'] : true;
				}
			);
			$values = array_column( array_values( $columns ), 'label' );

			return $values;
		}

		/**
		 * Get column keys for query
		 */
		public function get_queryable_columns() {
			$columns = $this->get_columns();
			$columns = array_filter(
				$columns,
				function ( $column ) {
					return isset( $column['query'] ) ? $column['query'] : true;
				}
			);

			return $columns;
		}

		/**
		 * Get column keys for query
		 */
		public function get_queryable_column_keys() {
			$columns = $this->get_queryable_columns();
			$keys = array_keys( $columns );
			return $keys;
		}

		/**
		 * Format custom meta value for OSGS
		 *
		 * @return array
		 */
		public function osgsw_custom_meta_fields() {
			$checked_value    = osgsw_get_option( 'show_custom_fields' );
			$on_custom_fields = osgsw_get_option( 'show_custom_meta_fields' );
			$on_custom_fields = true === wp_validate_boolean( osgsw_get_option( 'show_custom_meta_fields', false ) );
			$custom_array = [];
			if ( $on_custom_fields ) {
				$priority = 1000;
				if ( is_array( $checked_value ) && ! empty( $checked_value ) ) {
					foreach ( $checked_value as $key => $value ) {
						$check_type = check_osgsw_file_type( $value );
						if ( 'suported' === $check_type ) {
							$custom_array[ $value ]['label']     = $value;
							$custom_array[ $value ]['type']      = 'meta';
							$custom_array[ $value ]['priority']  = $priority++;
						}
					}
				}
			}
				return $custom_array;
		}
	}
}
