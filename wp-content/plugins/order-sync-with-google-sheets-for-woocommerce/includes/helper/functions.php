<?php
/**
 * Re-usable non-OOP helping functions for the plugin
 *
 * @package OrderSyncWithGoogleSheetForWooCommerce
 */


if ( ! function_exists( 'osgsw_get_option' ) ) {
	/**
	 * Get an option from the options table
	 *
	 * @param string $option_name The option name.
	 * @param mixed  $default     The default value.
	 * @return mixed
	 */
	function osgsw_get_option( $option_name, $default = null ) {
		$value = get_option( OSGSW_PREFIX . $option_name );
		if ( ! $value ) {
			return $default;
		}
		return apply_filters( 'osgsw_get_' . $option_name, $value );
	}
}

// update option.
if ( ! function_exists( 'osgsw_update_option' ) ) {
	/**
	 * Update an option in the options table
	 *
	 * @param string $option_name The option name.
	 * @param mixed  $value       The value.
	 * @return bool
	 */
	function osgsw_update_option( $option_name, $value ) {
		$value = apply_filters( 'osgsw_update_' . $option_name, $value );
		do_action( 'osgsw_before_update_' . $option_name, $value );
		$updated = update_option( OSGSW_PREFIX . $option_name, $value );
		if ( $updated ) {
			do_action( 'osgsw_updated_' . $option_name, $value );
		}
		return $updated;
	}
}

/**
 * Get all custom meta fields data
 *
 * @return array
 */
if ( ! function_exists( 'osgsw_get_product_custom_fields' ) ) {
	/**
	 *  Get custom meta fields data.
	 *
	 * @return array
	 */
	function osgsw_get_product_custom_fields() {
		global $wpdb;
		$custom_order_table = get_option( 'woocommerce_custom_orders_table_enabled' );

		if ( isset( $custom_order_table ) && 'yes' === $custom_order_table ) {
			$ordermeta_table = $wpdb->prefix . 'wc_orders_meta';
			$order_table = $wpdb->prefix . 'wc_orders';

			$sql = "SELECT DISTINCT pm.meta_key, pm.meta_value
			FROM $ordermeta_table pm
			INNER JOIN $order_table p ON pm.order_id = p.id
			WHERE p.type = %s";
			$sql = $wpdb->prepare($sql,'shop_order');//phpcs:ignore

		} else {
			$ordermeta_table = $wpdb->prefix . 'postmeta';
			$order_table = $wpdb->prefix . 'posts';

			$sql = "SELECT DISTINCT pm.meta_key, pm.meta_value
				FROM $ordermeta_table pm
				INNER JOIN $order_table p ON pm.post_id = p.ID
				WHERE p.post_type = %s";

			$sql = $wpdb->prepare($sql,'shop_order');//phpcs:ignore
		}

		$results = $wpdb->get_results($sql);//phpcs:ignore
		$custom_fields = [];
		foreach ( $results as $result ) {
			$custom_fields[ $result->meta_key ] = $result->meta_key;
		}
		$item_meta = osgsw_get_order_item_meta();
		$all_fields = $custom_fields + $item_meta;
		return $all_fields;
	}
}
/**
 * Get all custom order item meta fields data
 *
 * @return array
 */
if ( ! function_exists( 'osgsw_get_order_item_meta' ) ) {
	/**
	 * Get custom order item meta fields data.
	 *
	 * @return array
	 */
	function osgsw_get_order_item_meta() {
		global $wpdb;
		$order_itemmeta_table = $wpdb->prefix . 'woocommerce_order_itemmeta';
		$order_items_table = $wpdb->prefix . 'woocommerce_order_items';

		$sql = "SELECT DISTINCT oim.meta_key, oim.meta_value
			FROM $order_itemmeta_table oim
			INNER JOIN $order_items_table oi ON oim.order_item_id = oi.order_item_id";

		$results = $wpdb->get_results($sql); // phpcs:ignore
		$custom_fields = [];

		foreach ( $results as $result ) {
			$meta_key = $result->meta_key . '(Itemmeta)';
			$custom_fields[ $meta_key ] = $result->meta_key;
		}

		return $custom_fields;
	}
}
function osgsw_divided_prefix( $text, $keyword ) {
	if ( strpos($text, $keyword) !== false ) {
		$parts = explode($keyword, $text);
		return [
			'before' => $parts[0],
			'keyword' => $keyword,
		];
	} else {
		return false;
	}
}
/**
 * Is Ultimate License activated?
 *
 * @return bool
 * @version 1.0.0
 */
function osgsw_is_ultimate_license_activated() {
	$ultimate_license = get_option( 'osgsw_license_active' );
	if ( true == $ultimate_license ) { //phpcs:ignore
		return true;
	}
	return false;
}
/**
 *  Get all the custom order status.
 *
 * @return array
 */
function ossgsw_get_order_statuses() {
	$osgsw_status = osgsw_get_option('custom_order_status_bolean');
	if ( osgsw_is_ultimate_license_activated() && $osgsw_status ) {
		$status = [
			'wc-pending',
			'wc-processing',
			'wc-on-hold',
			'wc-completed',
			'wc-cancelled',
			'wc-refunded',
			'wc-failed',
			'wc-checkout-draft',
		];
		$status = apply_filters('osgsw_update_custom_order_status', $status);
		return $status;
	} else {
		return [
			'wc-pending',
			'wc-processing',
			'wc-on-hold',
			'wc-completed',
			'wc-cancelled',
			'wc-refunded',
			'wc-failed',
			'wc-checkout-draft',
		];
	}
}
/**
 * Retrieves the total count of WooCommerce orders.
 *
 * This function checks whether the WooCommerce custom orders table is enabled and
 * calculates the total count of orders based on the data storage option:
 * - If the custom orders table is enabled, it counts orders from the `wc_orders` table.
 * - Otherwise, it counts orders from the WordPress `posts` table.
 *
 * Orders with statuses `trash` and `auto-draft` are excluded from the count.
 *
 * @global wpdb $wpdb WordPress database abstraction object.
 * @return int The total count of WooCommerce orders.
 */
function osgsw_order_count() {
	global $wpdb;

	// Check if WooCommerce custom orders table is enabled
	$order_data_storage_option = get_option( 'woocommerce_custom_orders_table_enabled' );

	if ( 'yes' === $order_data_storage_option ) {
		// Count orders from the `wc_orders` table
		$order_count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) 
				 FROM {$wpdb->prefix}wc_orders 
				 WHERE type = %s 
				 AND status NOT IN ('trash', 'auto-draft')",
				'shop_order'
			)
		);
	} else {
		// Count orders from the `posts` table
		$order_count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) 
				 FROM {$wpdb->prefix}posts
				 WHERE post_type = %s 
				 AND post_status NOT IN ('trash', 'auto-draft')",
				'shop_order'
			)
		);
	}

	return $order_count;
}

/**
 * Format and retrieve the index number for a given product ID from a multi-dimensional array.
 *
 * This function processes a multi-dimensional array to extract the first value of each sub-array
 * and creates a single-dimensional array. It then checks if the given product ID exists as a key
 * in the formatted array and returns the corresponding value.
 *
 * @param int   $product_id The product ID (index position) to search for in the flattened array.
 * @param array $sheets_info A multi-dimensional array containing product information.
 *                           Each sub-array is expected to have the relevant data in the first index (0).
 * @return mixed|null Returns the formatted index number (first value of the sub-array) if found;
 *                    otherwise, returns null. If the input array is empty, returns null.
 *
 * Example Usage:
 * $sheets_info = [
 *     [1001, 'Product A'],
 *     [1002, 'Product B'],
 *     [1003, 'Product C'],
 * ];
 * $product_id = 1; // Index position to retrieve.
 * $result = ssgsw_format_index_number($product_id, $sheets_info);
 * // Output: 1002 (value from index 1 in the flattened array).
 */
function osgsw_format_index_number( $sheets_info = [] ) {
	if ( is_array($sheets_info) && ! empty($sheets_info) ) {
		$flattened_array = array_merge(...array_map(function ( $subArray ) {
			return [ $subArray[0] ];
		}, $sheets_info));
		return array_flip($flattened_array);
	}
	return [];
}
/**
 * Retrieve the value of the element that immediately precedes a given key in an array.
 *
 * This function locates the specified key in the array and returns the value of the
 * element located immediately before it. If the key is not found or it is the first
 * element in the array, the function returns `false`.
 *
 * @param array $array The array to search.
 * @param mixed $key   The key for which to find the previous value.
 *
 * @return mixed|false The value of the previous element if it exists, or `false` if the
 *                     key is not found or it is the first element.
 *
 * Example:
 * $data = [
 *     608 => 2,
 *     671 => 6,
 *     673 => 9,
 *     674 => 13,
 * ];
 *
 * $result = ssgsw_get_previous_value($data, 671);
 * // Output: 2 (value of key 608)
 */
function osgsw_get_previous_value( $array, $key ) {
	$keys = array_keys($array);
	$index = array_search($key, $keys);

	if ( $index === false || $index === 0 ) {
		return false;
	}
	$previous_key = $keys[ $index - 1 ];
	return isset($array[ $previous_key ]) ? $array[ $previous_key ] : false;
}

 /**
  * Get the index number for a product from index data.
  *
  * This function retrieves the index number for a given product ID from the provided index data array.
  *
  * @param mixed $product_id The product ID to search for.
  * @param array $index_data The array containing index data for products.
  *
  * @return mixed The index number for the product or false if not found.
  */
function osgsw_get_index_number_option( $order_id, $index_data ) {
	return isset($index_data[ $order_id ]) ? $index_data[ $order_id ] + 1 : null;
}
/**
 * Save meta fields value
 *
 * @param  int   $id The option id.
 * @param  mixed $meta_key       The meta key.
 * @param  mixed $value       The meta value.
 *
 * @return void
 */
function osgsw_meta_field_value_save( $id, $meta_key, $value ) {
	if ( function_exists( 'get_field' ) ) {
		$field_object = acf_get_field( $meta_key );
		if ( is_array( $field_object ) && ! empty( $field_object ) ) {
			if ( array_key_exists( 'choices', $field_object ) ) {
				$choices = $field_object['choices'];
				if ( array_key_exists( $value, $choices ) ) {
					update_post_meta( $id, $meta_key, $value );
				}
			} else {
				update_post_meta( $id, $meta_key, $value );
			}
		} else {
			update_post_meta( $id, $meta_key, $value );
		}
	} else {
		update_post_meta( $id, $meta_key, $value );
	}
}

/**
 * Check acf fields type
 *
 * @param string $meta_key acf meta key.
 * @return string
 */
function check_osgsw_file_type( $meta_key ) {
	$all_acf_type = osgsw_all_type_field_in_acf();
	if ( function_exists( 'get_field' ) ) {
		$field_object = acf_get_field( $meta_key );
		if ( is_array( $field_object ) && ! empty( $field_object ) ) {
			if ( array_key_exists( 'type', $field_object ) ) {
				if ( 'select' === $field_object['type'] ) {
					if ( 1 === $field_object['multiple'] ) {
						return 'not_suported';
					}
				} else if ( in_array( $field_object['type'], $all_acf_type ) ) {
					return 'not_suported';
				}
			}
		}
	}
	return 'suported';
}




/**
 * Collection of all type fields in acf.
 *
 * @return array
 */
function osgsw_all_type_field_in_acf() {
	$field_types = [
		'wysiwyg',
		'image',
		'file',
		'checkbox',
		'post_object',
		'page_link',
		'relationship',
		'taxonomy',
		'user',
		'google_map',
		'date_picker',
		'date_time_picker',
		'time_picker',
		'message',
		'tab',
		'group',
		'repeater',
		'flexible_content',
		'clone',
		'accordion',
		'gallery',
		'block',
		'nav_menu',
		'post_taxonomy',
		'sidebar',
		'widget_area',
		'user_role',
		'true_false',
		'button_group',
		'link',
	];
	return $field_types;
}


/**
 * Sql reserved keyword check
 *
 * @param string $key key.
 * @return string
 */
function osgsw_reserved_keyword( $key ) {
	$reserved_keywords = [
		'ADD',
		'ALL',
		'ALTER',
		'AND',
		'AS',
		'ASC',
		'BETWEEN',
		'BY',
		'CHAR',
		'CHECK',
		'COLUMN',
		'CONNECT',
		'CREATE',
		'CURRENT',
		'DECIMAL',
		'DEFAULT',
		'DELETE',
		'DESC',
		'DISTINCT',
		'DROP',
		'ELSE',
		'EXCLUSIVE',
		'EXISTS',
		'FILE',
		'FLOAT',
		'FOR',
		'FROM',
		'GRANT',
		'GROUP',
		'HAVING',
		'IDENTIFIED',
		'IMMEDIATE',
		'IN',
		'INCREMENT',
		'INDEX',
		'INITIAL',
		'INSERT',
		'INTEGER',
		'INTERSECT',
		'INTO',
		'IS',
		'JOIN',
		'LIKE',
		'LOCK',
		'LONG',
		'MAXEXTENTS',
		'MINUS',
		'MLSLABEL',
		'MODE',
		'MODIFY',
		'NOAUDIT',
		'NOCOMPRESS',
		'NOT',
		'NOWAIT',
		'NULL',
		'NUMBER',
		'OF',
		'OFFLINE',
		'ON',
		'ONLINE',
		'OPTION',
		'OR',
		'ORDER',
		'PCTFREE',
		'PRIOR',
		'PRIVILEGES',
		'PUBLIC',
		'RAW',
		'RENAME',
		'RESOURCE',
		'REVOKE',
		'ROW',
		'ROWID',
		'ROWNUM',
		'ROWS',
		'SELECT',
		'SESSION',
		'SET',
		'SMALLINT',
		'START',
		'SYNONYM',
		'SYSDATE',
		'TABLE',
		'THEN',
		'TO',
		'TRIGGER',
		'UID',
		'UNION',
		'UNIQUE',
		'UPDATE',
		'USER',
		'VALIDATE',
		'VALUES',
		'VARCHAR',
		'VIEW',
		'WHENEVER',
		'WHERE',
		'WITH',
		'RANGE',
		'LIMIT',
		'GROUP BY',
		'ORDER BY',
		'WHERE',
		'FROM',
	];
	$value_lower = strtolower( $key );
	$keywords_lower = array_map( 'strtolower', $reserved_keywords );
	if ( in_array( $value_lower, $keywords_lower ) ) {
		return 'yes';
	} else {
		return 'no';
	}
}


if ( ! function_exists( 'osgsw' ) ) {
	/**
	 * Get the app instance
	 *
	 * @return App
	 */
	function osgsw() {
		return new \OrderSyncWithGoogleSheetForWooCommerce\App();
	}
}


/**
 * Convert readable string for serialized data
 */
function osgsw_serialize_to_readable_string( $data ) {

	// Check if the data is serialized and unserialize it
	if ( is_serialized($data) ) {
		$data = maybe_unserialize($data);
	}

	if ( is_array($data) ) {
		$result = [];
		foreach ( $data as $key => $value ) {
			$result[] = $key . ': ' . osgsw_serialize_to_readable_string($value);
		}
		return implode(', ', $result);
	} elseif ( is_object($data) ) {
		$result = [];
		foreach ( get_object_vars($data) as $key => $value ) {
			$result[] = $key . ': ' . osgsw_serialize_to_readable_string($value);
		}
		return implode(', ', $result);
	} else {
		return isset($data) ? (string) $data : '';
	}
}

/**
 * Format item meta data
 *
 * @return string
 */
function ssgsw_format_item_meta( $data ) {
	if ( strpos($data, 'ssgsw_sep,') !== false ) {
		$items = explode('ssgsw_sep,', $data);
		if ( is_array($items) && ! empty($items) ) {
			$new_data = '';
			foreach ( $items as $key => $item ) {
				$new_data .= ssgsw_find_out_item_meta_values($item) . ', ';
			}
			return ossgw_remove_last_comma($new_data);
		}
	} else {
		return ossgw_remove_last_comma(ssgsw_find_out_item_meta_values($data));
	}
}

 /**
  * Remove the last comma from a string.
  *
  * This function trims any leading or trailing whitespace from the input string,
  * locates the last occurrence of a comma, and removes it while leaving all other commas intact.
  *
  * @param string $text The input string from which the last comma should be removed.
  * @return string The modified string with the last comma removed.
  */
function ossgw_remove_last_comma( $text ) {
	// Ensure the input is a string; if not, cast it to a string
	$text = is_string($text) ? $text : (string) $text;

	// Trim whitespace to ensure a clean string
	$text = trim($text);

	// Check if the string is empty after trimming
	if ( empty($text) ) {
		return ''; // Return an empty string if input is invalid
	}

	// Find the position of the last comma
	$lastCommaPos = strrpos($text, ',');

	// If a comma exists, remove it
	if ( $lastCommaPos !== false ) {
		$text = substr_replace($text, '', $lastCommaPos, 1);
	}

	return $text;
}

 /**
  * Find out item_meta values
  */
function ssgsw_find_out_item_meta_values( $items ) {
	if ( strpos($items, '(ssgsw_itemmeta_value:') !== false ) {
		// Explode the item string to separate the plain text and the serialized data
		$exploded_data = explode('(ssgsw_itemmeta_value:', $items);
		$plain_text = isset($exploded_data[0]) ? trim($exploded_data[0]) : '';
		$remaining_data = isset($exploded_data[1]) ? $exploded_data[1] : '';
		$serialized_data = str_replace('ssgsw_itemmeta_end)', '', $remaining_data);
		$new_sr  = osgsw_serialize_to_readable_string($serialized_data);
		if ( ! empty($plain_text) ) {
			return $new_sr;
		} else {
			return $new_sr;
		}
	} else {
		return osgsw_serialize_to_readable_string($items);
	}
}


/**
 * Check if the Barcode Scanner Lite POS plugin is active.
 *
 * This function checks whether the "Barcode Scanner Lite POS" plugin
 * is active by verifying its file path in the WordPress plugins directory.
 *
 * @return bool True if the plugin is active, false otherwise.
 */
function ssgsw_is_barcode_scanner_plugin_active() {
	if ( ! function_exists('is_plugin_active') ) {
		include_once ABSPATH . 'wp-admin/includes/plugin.php';
	}
	// Define the relative path to the main plugin file of Barcode Scanner Lite POS.
	$plugin_path = 'barcode-scanner-lite-pos-to-manage-products-inventory-and-orders/barcode-scanner.php';
	if ( is_plugin_active($plugin_path) ) {
		return true;
	} else {
		return false;
	}
}
add_action( 'bulk_edit_posts', 'retrieve_bulk_edit_ids', 10, 2 );

function retrieve_bulk_edit_ids( $post_ids, $post_type ) {
}

add_action( 'load-edit.php', 'track_bulk_edit_ids' );

function track_bulk_edit_ids() {
	// Check if the current screen is handling a bulk edit
	if ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] === 'edit' ) {
		if ( isset( $_REQUEST['post'] ) && is_array( $_REQUEST['post'] ) ) {
			$post_ids = array_map( 'intval', $_REQUEST['post'] ); // Sanitize input

			// Log the IDs (for debugging or other purposes)
			foreach ( $post_ids as $post_id ) {
				error_log( "Bulk Edit Post ID: $post_id" );
			}

			// Example: Perform custom logic with the retrieved IDs
			custom_bulk_edit_action( $post_ids );
		}
	}
}

function custom_bulk_edit_action( $post_ids ) {
	foreach ( $post_ids as $post_id ) {
		$post_type = get_post_type( $post_id );

		if ( 'shop_order' === $post_type ) { // Ensure it's for WooCommerce orders
			$order = wc_get_order( $post_id );
			if ( $order ) {
				$status = $order->get_status();
				error_log( "Order ID: $post_id, Status: $status" );

				// Add your custom logic for each order here
				// Example: Update meta, trigger events, etc.
			}
		}
	}
}
