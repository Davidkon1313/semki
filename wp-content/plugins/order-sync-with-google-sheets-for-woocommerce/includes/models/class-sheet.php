<?php
/**
 * Class Sheet
 * includes/model/Sheet.php
 *
 * @package OrderSyncWithGoogleSheetForWooCommerce
 */

namespace OrderSyncWithGoogleSheetForWooCommerce;

defined( 'ABSPATH' ) || die( 'No script kiddies please!' );

if ( ! class_exists( '\OrderSyncWithGoogleSheetForWooCommerce\Sheet' ) ) {
	/**
	 * Class Sheet
	 */
	class Sheet extends Base {
		/**
		 * Sheet credentials
		 *
		 * @var null
		 */
		protected $credentials = null;
		/**
		 * Sheet ID
		 *
		 * @var null
		 */
		protected $sheet_id = null;
		/**
		 * Spreadsheet ID
		 *
		 * @var string
		 */
		protected $spreadsheet_id;
		/**
		 * Sheet Sheet Tab
		 *
		 * @var string
		 */
		protected $sheet_tab = null;
		/**
		 * Constructor.
		 *
		 * @param string $sheet_id Spreadsheet ID.
		 * @param string $sheet_tab Spreadsheet Tab.
		 * @throws \Exception If plugin is not ready to use.
		 */
		public function __construct( $sheet_id = null, $sheet_tab = null ) {
			/**
			 * Check if plugin is ready to use
			 */

			if ( osgsw()->is_plugin_ready() === false ) {
				throw new \Exception( 'Plugin is not ready to use.' );
				return false;
			}
			/**
			 * Default credentials
			 */
			$this->credentials = osgsw_get_option( 'credentials' );
			/**
			 * The Spreadsheet
			 */
			$this->spreadsheet_id = $sheet_id ?? osgsw_get_option( 'spreadsheet_id' );
			/**
			 * Single Sheet
			 */
			$this->sheet_tab = $sheet_tab ?? osgsw_get_option( 'sheet_tab' );
			$this->sheet_id  = osgsw_get_option( 'sheet_id', '0' );
		}

		/**
		 * Set Sheet ID.
		 *
		 * @param string $spreadsheet_id Spreadsheet ID.
		 * @return $this
		 */
		public function setID( $spreadsheet_id = null ) {
			if ( $spreadsheet_id ) {
				$this->spreadsheet_id = $spreadsheet_id;
			}
			return $this;
		}
		/**
		 * Set Sheet Tab Name.
		 *
		 * @param string $sheet_tab Spreadsheet Tab.
		 * @return $this
		 */
		public function setTab( $sheet_tab = null ) {
			if ( $sheet_tab ) {
				$this->sheet_tab = $sheet_tab;
			}
			return $this;
		}
		/**
		 * Generate access token for google sheet access.
		 *
		 * @return mixed
		 */
		protected function generate_access_token() {
			try {
				$credentials = $this->credentials;
				if ( ! is_array( $credentials ) ) {
					return false;
				}
				if ( ! array_key_exists( 'private_key', $credentials ) ) {
					return false;
				}
				$client_email = $credentials['client_email'];
				$private_key = $credentials['private_key'];
				$now = time();
				$exp = $now + 3600;
				$payload = wp_json_encode(
					[
						'iss' => $client_email,
						'aud' => 'https://oauth2.googleapis.com/token',
						'iat' => $now,
						'exp' => $exp,
						'scope' => 'https://www.googleapis.com/auth/spreadsheets',
					]
				);

				$header = wp_json_encode(
					[
						'alg' => 'RS256',
						'typ' => 'JWT',
					]
				);

				$base64_url_header = str_replace( [ '+', '/', '=' ], [ '-', '_', '' ], base64_encode( $header ) );
				$base64_url_payload = str_replace( [ '+', '/', '=' ], [ '-', '_', '' ], base64_encode( $payload ) );

				$signature = '';
				openssl_sign( $base64_url_header . '.' . $base64_url_payload, $signature, $private_key, 'SHA256' );
				$base64_url_signature = str_replace( [ '+', '/', '=' ], [ '-', '_', '' ], base64_encode( $signature ) );

				$jwt = $base64_url_header . '.' . $base64_url_payload . '.' . $base64_url_signature;

				$token_url = 'https://oauth2.googleapis.com/token';
				$body = [
					'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
					'assertion' => $jwt,
				];

				$response = wp_remote_post(
					$token_url,
					[
						'body' => $body,
					]
				);

				$response_body = wp_remote_retrieve_body( $response );
				$token_data = json_decode( $response_body, true );
				if ( is_array($token_data) ) {
					if ( array_key_exists( 'access_token', $token_data ) ) {
						$access_token = $token_data['access_token'];
						return $access_token;
					} else {
						return false;
					}
				} else {
					return false;
				}
			} catch ( \Exception $e ) {
				return false;
			}
		}
		/**
		 * Generate token every 55minute
		 *
		 * @return string
		 */
		public function get_token() {
			$new_token = $this->generate_access_token();
			if ( $new_token ) {
				return $new_token;
			} else {
				return $this->generate_access_token();
			}
		}
		/**
		 * Get first column's value from Google Sheet using wp_remote_request.
		 *
		 * @return array|false An array of values or false if there's an error.
		 */
		public function get_first_columns() {
			$url = 'https://sheets.googleapis.com/v4/spreadsheets/' . $this->spreadsheet_id . '/values/' . urlencode($this->sheet_tab . '!A:A');
			$args = [
				'method' => 'GET',
				'headers' => [
					'Authorization' => 'Bearer ' . $this->get_token(),
				],
				'timeout' => 300,
			];
			$response = wp_remote_request($url, $args);
			if ( is_wp_error( $response ) ) {
				return [];
			}
			$response_body = wp_remote_retrieve_body($response);
			$response_data = json_decode($response_body, true);
			if ( isset( $response_data['values'] ) ) {
				return $response_data['values'];
			}
			return [];
		}
		/**
		 * Get values from google sheet by range.
		 *
		 * @param string $range Range.
		 * @param string $dimension Dimension.
		 * @param string $sheet_tab Sheet Tab.
		 * @return array|bool
		 */
		public function get_values( $range = null, $dimension = 'ROWS', $sheet_tab = null ) {
			if ( ! $range ) {
				return false;
			}

			if ( ! $sheet_tab ) {
				$sheet_tab = $this->sheet_tab;
			} else {
				$this->sheet_tab = $sheet_tab;
			}

			$url = 'https://sheets.googleapis.com/v4/spreadsheets/' . $this->spreadsheet_id . '/values/' . urlencode( $sheet_tab . '!' . $range );
			$args = [
				'method' => 'GET',
				'headers' => [
					'Authorization' => 'Bearer ' . $this->get_token(),
				],
				'timeout' => 300,
			];

			$response = wp_remote_request( $url, $args );

			if ( is_wp_error( $response ) ) {
				return [];
			}
			$response_body = wp_remote_retrieve_body( $response );
			$response_data = json_decode( $response_body, true );
			if ( isset( $response_data['values'] ) ) {
				return $response_data['values'];
			}
			return [];
		}
		/**
		 * Get rows from google sheet by range.
		 *
		 * @param string $range Range.
		 * @param string $sheet_tab Sheet Tab.
		 */
		public function get_rows( $range = null, $sheet_tab = null ) {
			if ( ! $range ) {
				return false;
			}
			return $this->get_values( $range, 'ROWS', $sheet_tab );
		}
		/**
		 * Get columns from google sheet by range.
		 *
		 * @param string $range Range.
		 * @param string $sheet_tab Sheet Tab.
		 * @return array|bool
		 */
		public function get_columns( $range = null, $sheet_tab = null ) {
			if ( ! $range ) {
				return false;
			}
			return $this->get_values( $range, 'COLUMNS', $sheet_tab );
		}
		/**
		 * Updates values in google sheet by range.
		 *
		 * @param string $range Range.
		 * @param array  $values Values.
		 * @param string $dimension Dimension.
		 * @return mixed
		 */
		public function update_values( $range = null, $values = null, $dimension = null, $title = false ) {
			if ( ! $range || ! $values ) {
				return false;
			}
			try {
				$access_token = $this->get_token();
				$this->reset_sheet2( $access_token, $title );
				$spreadsheet_id     = $this->spreadsheet_id;
				$sheet_name = $this->sheet_tab;
				$api_url = "https://sheets.googleapis.com/v4/spreadsheets/{$spreadsheet_id}/values/{$sheet_name}!A1:append?valueInputOption=USER_ENTERED";

				$data = [
					'values' => $values,
				];
				$request_data = [
					'majorDimension' => 'ROWS',
					'values' => $data['values'],
				];
				$headers = [
					'Authorization' => "Bearer {$access_token}",
					'Content-Type' => 'application/json',
				];
				$response = wp_remote_post(
					$api_url,
					[
						'headers' => $headers,
						'body' => wp_json_encode( $request_data ),
						'timeout' => 300,
					]
				);
				$response_body = wp_remote_retrieve_body( $response );
				$response_data = json_decode( $response_body, true );

				if ( isset( $response_data['updates']['updatedRows'] ) ) {
					   return true;
				} else {
					return false;
				}
			} catch ( \Throwable $error ) {
				return false;
			}
		}
		/**
		 * Updates multiple rows of data in a Google Sheet starting from a specified row.
		 * The end row and column range are automatically calculated based on the provided data.
		 *
		 * This function sends a PUT request to the Google Sheets API to update the values
		 * in a specified range, starting at the provided `start_row`. The number of rows and columns
		 * is derived from the provided `$values` array.
		 *
		 * @param array $values  A 2D array containing the data to be updated in the Google Sheet.
		 *                       Each inner array represents a row, and each element within that row represents a cell value.
		 * @param int   $start_row The row number to start the update from.
		 *
		 * @return bool Returns `true` if the update is successful, `false` otherwise.
		 *
		 * @throws Exception If the values array is empty or if there's an issue with the API request.
		 */
		public function update_multiple_row_values( $values, $start_row ) {
			// Ensure valid parameters
			if ( empty($values) ) {
				return false;
			}
			$end_row = $start_row + count($values) - 1;
			// Create the range string
			$range = "{$this->sheet_tab}!A{$start_row}:Z{$end_row}"; // Adjust column range if needed
			$url = 'https://sheets.googleapis.com/v4/spreadsheets/' . $this->spreadsheet_id . '/values/' . urlencode($range) . '?valueInputOption=USER_ENTERED';
			$args = array(
				'method' => 'PUT',
				'headers' => array(
					'Authorization' => 'Bearer ' . $this->get_token(),
					'Content-Type' => 'application/json',
				),
				'body' => wp_json_encode(array(
					'range' => $range,
					'values' => $values,
				)),
				'timeout' => 300,
			);

			// Make the request
			$response = wp_remote_request($url, $args);

			if ( is_wp_error($response) ) {
				return false;
			}

			$response_body = wp_remote_retrieve_body($response);
			$response_data = json_decode($response_body, true);

			if ( isset($response_data['updatedRows']) && $response_data['updatedRows'] > 0 ) {
				return true;
			} else {
				return false;
			}
		}
				/**
				 * Deletes a batch of rows from a Google Sheet using the Sheets API.
				 *
				 * This function takes an array of row numbers and sends a batch request to the
				 * Google Sheets API to delete the specified rows from the sheet. The rows are
				 * deleted in descending order to avoid index shifting issues.
				 *
				 * @param array $row_numbers An array of row numbers to delete from the sheet.
				 *                           Row numbers should be positive integers, and they
				 *                           will be deleted in the order provided (from highest to lowest).
				 *                           Example: [6, 4, 12, 9]
				 *
				 * @return bool Returns `true` if the rows were successfully deleted,
				 *              `false` if there was an error or no rows were passed.
				 */
		public function delete_batch_rows( $row_numbers ) {
			if ( empty($row_numbers) ) {
				return false;  // If no rows are passed, return false.
			}

			// Sort the row numbers in descending order to avoid index shifting issues when deleting
			rsort($row_numbers);

			// Create the requests for each row to delete
			$requests = array();

			foreach ( $row_numbers as $row_number ) {
				if ( ! $row_number ) {
					continue;  // Skip invalid row numbers
				}

				// Create the delete request for the row
				$requests[] = array(
					'deleteDimension' => array(
						'range' => array(
							'sheetId' => $this->sheet_id,
							'dimension' => 'ROWS',
							'startIndex' => $row_number - 1, // Google Sheets API expects zero-based index
							'endIndex' => $row_number, // For deleting a single row
						),
					),
				);
			}

			// If there are no valid requests, return false
			if ( empty($requests) ) {
				return false;
			}

			// Prepare the API request URL
			$url = 'https://sheets.googleapis.com/v4/spreadsheets/' . $this->spreadsheet_id . ':batchUpdate';

			// Prepare the API arguments
			$args = array(
				'method' => 'POST',
				'headers' => array(
					'Authorization' => 'Bearer ' . $this->get_token(),
					'Content-Type' => 'application/json',
				),
				'body' => wp_json_encode(array(
					'requests' => $requests,
				)),
				'timeout' => 300,
			);

			// Send the request to the Google Sheets API
			$response = wp_remote_request($url, $args);

			// Check for errors in the request
			if ( is_wp_error($response) ) {
				return false;
			}

			// Get the response code
			$response_code = wp_remote_retrieve_response_code($response);

			// If the response code is 200 (OK), return true, else return false
			if ( 200 === $response_code ) {
				return true;
			} else {
				return false;
			}
		}
		/**
		 * Updates multiple rows in a Google Sheet using a single batch request.
		 *
		 * This function sends a batch update request to the Google Sheets API to update values
		 * in multiple rows. It accepts an associative array where the keys are the row numbers
		 * (1-based index) and the values are arrays of the values to be updated in those rows.
		 * It formats and sends the data to the Google Sheets API for batch updating.
		 *
		 * @param array $row_values An associative array of rows and values to update.
		 *                         Each key is the row number (1-based index) and the value is
		 *                         an array of cell values to update in that row.
		 *                         Example:
		 *                         [
		 *                             2 => ['Order ID', 'Customer Name', 'Status'],
		 *                             5 => ['1234', 'John Doe', 'Completed'],
		 *                             6 => ['5678', 'Jane Smith', 'Pending'],
		 *                         ]
		 *
		 * @return bool Returns `true` if the rows were updated successfully, `false` otherwise.
		 *
		 * @throws Exception If the request to the Google Sheets API fails or the response is not successful.
		 */
		public function update_batch_rows( $row_values, $sheet_index = [] ) {
			if ( empty($row_values) ) {
				return false;
			}
			$requests = array();
			$index_id = [];
			$start_index = false;
			foreach ( $row_values as $row_number => $values ) {
				$new_id = isset($values[0]) ? $values[0] : false;
				if ( ! $new_id ) {
					return;
				}
				$find_index_from_sheet = osgsw_get_index_number_option($new_id, $sheet_index);
				if ( $find_index_from_sheet ) {
					$get_prev_values = osgsw_get_previous_value($sheet_index, $new_id);
					if ( $get_prev_values ) {
						$start_index = $get_prev_values + 2;
						$key = array_search($new_id, $index_id);
						if ( $key !== false ) {
							unset($index_id[ $key ]);
							$start_index = $key + 1;
							$index_id[ $start_index ] = $new_id;
						} else {
							$index_id[ $start_index ] = $new_id;
						}
					}
				}
				if ( ! $start_index ) {
					return false;
				}
				if ( ! is_array($values) || empty($values) ) {
					continue;
				}
				$requests[] = array(
					'updateCells' => array(
						'range' => array(
							'sheetId' => $this->sheet_id,
							'startRowIndex' => $start_index - 1,
							'endRowIndex' => $start_index,
						),
						'rows' => array(
							array(
								'values' => array_map(function ( $value ) {
									return array( 'userEnteredValue' => array( 'stringValue' => $value ) ); // Adjust to match the data type
								}, $values),
							),
						),
						'fields' => 'userEnteredValue', // Only update cell values
					),
				);
			}

			// If there are no valid requests, return false
			if ( empty($requests) ) {
				return false;
			}
			// Prepare the API request URL
			$url = 'https://sheets.googleapis.com/v4/spreadsheets/' . $this->spreadsheet_id . ':batchUpdate';

			// Prepare the API arguments
			$args = array(
				'method' => 'POST',
				'headers' => array(
					'Authorization' => 'Bearer ' . $this->get_token(),
					'Content-Type' => 'application/json',
				),
				'body' => wp_json_encode(array(
					'requests' => $requests,
				)),
				'timeout' => 300,
			);

			// Send the request to the Google Sheets API
			$response = wp_remote_request($url, $args);

			// Check for errors in the request
			if ( is_wp_error($response) ) {
				return false;
			}

			// Get the response code
			$response_code = wp_remote_retrieve_response_code($response);

			// If the response code is 200 (OK), return true, else return false
			if ( 200 === $response_code ) {
				return true;
			} else {
				return false;
			}
		}

		/**
		 * Updates values in Google Sheet by range using wp_remote_post.
		 *
		 * @param string $row_number Row number.
		 * @param array  $values Values.
		 * @param string $dimension Dimension.
		 * @return bool True if the update was successful, false otherwise.
		 */
		public function update_single_row_values( $row_number = null, $values = null, $dimension = null, $end_number = null ) {
			if ( ! $row_number || ! $values || ! $end_number ) {
				return false;
			}
			$url = 'https://sheets.googleapis.com/v4/spreadsheets/' . $this->spreadsheet_id . '/values/' . urlencode($this->sheet_tab . '!' . $row_number . ':' . $end_number) . '?valueInputOption=USER_ENTERED';

			$args = array(
				'method' => 'PUT',
				'headers' => array(
					'Authorization' => 'Bearer ' . $this->get_token(),
					'Content-Type' => 'application/json',
				),
				'body' => wp_json_encode(array(
					'values' => $values,
				)),
				'timeout' => 300,
			);

			$response = wp_remote_request($url, $args);

			if ( is_wp_error($response) ) {
				return false;
			}

			$response_body = wp_remote_retrieve_body($response);

			$response_data = json_decode($response_body, true);
			if ( isset($response_data['updates']['updatedRows']) && $response_data['updates']['updatedRows'] > 0 ) {
				return true;
			} else {
				return false;
			}
		}
		/**
		 * Insert values in google sheet by range.
		 *
		 * @param string $range Range.
		 * @param array  $data Values.
		 * @param string $dimension Dimension.
		 * @return mixed
		 */
		public function insert_new_value( $range = null, $data = null, $dimension = null ) {
			if ( ! $data ) {
				return false;
			}
			try {
				$access_token = $this->get_token();
				$spreadsheet_id     = $this->spreadsheet_id;
				$sheet_name = $this->sheet_tab;
				$api_url = "https://sheets.googleapis.com/v4/spreadsheets/{$spreadsheet_id}/values/{$sheet_name}:append?valueInputOption=USER_ENTERED";
				$data = [
					'values' => [ $data ],
				];
				$request_data = [
					'majorDimension' => 'ROWS',
					'values' => $data['values'],
				];
				$headers = [
					'Authorization' => "Bearer {$access_token}",
					'Content-Type' => 'application/json',
				];
				$response = wp_remote_post(
					$api_url,
					[
						'headers' => $headers,
						'body' => wp_json_encode( $request_data ),
						'timeout' => 300,
					]
				);
				$response_body = wp_remote_retrieve_body( $response );
				$response_data = json_decode( $response_body, true );
				if ( isset( $response_data['updates']['updatedRows'] ) ) {
					return true;
				} else {
					return false;
				}
			} catch ( \Throwable $error ) {
				return false;
			}
		}
		/**
		 * Appends data to a specified Google Sheet.
		 *
		 * @param array $values Data to append, formatted as a 2D array.
		 * @return bool True on success, false on failure.
		 */
		public function append_batch_product_to_sheet( $values, $offset = 0 ) {
			if ( empty($values) ) {
				return false;
			}
			try {
				$access_token = $this->get_token();
				if (!$offset) { //phpcs:ignore
					$this->reset_sheet2( $access_token);
				}
				$spreadsheet_id = $this->spreadsheet_id;
				$sheet_name = $this->sheet_tab;

				// Google Sheets API URL for appending values
				$api_url = "https://sheets.googleapis.com/v4/spreadsheets/{$spreadsheet_id}/values/{$sheet_name}!A1:append?valueInputOption=USER_ENTERED";

				$data = array(
					'values' => $values,
				);

				$request_data = array(
					'majorDimension' => 'ROWS',
					'values' => $data['values'],
				);

				$headers = array(
					'Authorization' => "Bearer {$access_token}",
					'Content-Type' => 'application/json',
				);

				// Make the API request to append data
				$response = wp_remote_post(
					$api_url, array(
						'headers' => $headers,
						'body' => wp_json_encode($request_data),
						'timeout' => 300,
					)
				);

				$response_body = wp_remote_retrieve_body($response);
				$response_data = json_decode($response_body, true);

				if ( isset($response_data['updates']['updatedRows']) ) {
					return true;
				} else {
					return false;
				}
			} catch ( \Throwable $error ) {
				return false;
			}
		}

		/**
		 * Append new row to Google Sheets using wp_remote_post.
		 *
		 * @param array  $data Data to append as a new row.
		 * @param string $type Type of append (e.g., 'test' or 'deleted_product').
		 * @return bool True if successful, false on failure.
		 */
		public function append_new_row( $data, $type = 'simple' ) {
			if ( ! $data ) {
				return false;
			}
			try {
				$access_token = $this->get_token();
				$spreadsheet_id     = $this->spreadsheet_id;
				$sheet_name = $this->sheet_tab;
				$api_url = "https://sheets.googleapis.com/v4/spreadsheets/{$spreadsheet_id}/values/{$sheet_name}:append?valueInputOption=USER_ENTERED";
				$data = array(
					'values' => $data,
				);
				$request_data = array(
					'majorDimension' => 'ROWS',
					'values' => $data['values'],
				);
				$headers = array(
					'Authorization' => "Bearer {$access_token}",
					'Content-Type' => 'application/json',
				);
				$response = wp_remote_post(
					$api_url, array(
						'headers' => $headers,
						'body' => wp_json_encode($request_data),
						'timeout' => 300,
					)
				);
				$response_body = wp_remote_retrieve_body($response);
				$response_data = json_decode($response_body, true);
				if ( isset($response_data['updates']['updatedRows']) ) {
					return true;
				} else {
					return false;
				}
			} catch ( \Throwable $error ) {
				return false;
			}
		}
		/**
		 * Sort Google Sheet data based on the first column using wp_remote_post.
		 *
		 * @param string $spreadsheet_id The ID of the Google Spreadsheet.
		 * @param string $access_token The access token for authorization.
		 *
		 * @return bool True if successful, false on failure.
		 */
		public function sort_google_sheet_data_wp_remote( $spreadsheet_id, $access_token ) {
			try {
				$sort_range = array(
					'sheetId' => $this->sheet_id,
					'startRowIndex' => 1,
					'endRowIndex' => 0,
					'startColumnIndex' => 0,
					'endColumnIndex' => null,
				);

				$sort_spec = array(
					'dimensionIndex' => 0,
					'sortOrder' => 'ASCENDING',
				);

				$sort_range_request = array(
					'sortRange' => array(
						'range' => $sort_range,
						'sortSpecs' => array( $sort_spec ),
					),
				);

				$batch_update_request = array(
					'requests' => array( $sort_range_request ),
				);
				$sort_range['endRowIndex'] = null;
				$sort_range_request['sortRange']['range'] = $sort_range;
				$batch_update_request['requests'] = array( $sort_range_request );
				// Build the URL for the batch update.
				$url = 'https://sheets.googleapis.com/v4/spreadsheets/' . $spreadsheet_id . ':batchUpdate';

				// Prepare the request arguments.
				$headers = array(
					'Authorization' => 'Bearer ' . $access_token,
					'Content-Type' => 'application/json',
				);

				$body = wp_json_encode($batch_update_request);

				$args = array(
					'body' => $body,
					'headers' => $headers,
					'method' => 'POST',
					'timeout' => 300,
				);
				$response = wp_remote_request($url, $args);
				if ( is_wp_error($response) ) {
					return false;
				}
				$response_code = wp_remote_retrieve_response_code($response);
				if ( 200 === $response_code ) {
					return true;
				} else {
					return false;
				}
			} catch ( \Exception $e ) {
				return false;
			}
		}
		/**
		 * Updates rows in google sheet by range.
		 *
		 * @param string $range Range.
		 * @param array  $values Values.
		 * @return mixed
		 */
		public function update_row_values( $range = null, $values = null, $title = false ) {
			if ( ! $range || ! $values ) {
				return false;
			}
			return $this->update_values( $range, $values, 'ROWS', $title );
		}
		/**
		 * Updates columns in google sheet by range.
		 *
		 * @param string $range Range.
		 * @param array  $values Values.
		 * @return mixed
		 */
		public function update_row_columns( $range = null, $values = null ) {
			if ( ! $range || ! $values ) {
				return false;
			}
			return $this->update_values( $range, $values, 'COLUMNS' );
		}
		/**
		 * Initializes the Google Sheets API service.
		 *
		 * @throws \Exception If the API client library is not found.
		 * @return mixed
		 */
		public function initialize() {
			try {
				$sheets = $this->get_sheet_tab();
				if ( empty($sheets) ) {
					$sheets = $this->get_sheet_tab();
				}
				$sheet = array_filter(
					$sheets,
					function ( $sheet ) {
						return $sheet['properties']['title'] === $this->sheet_tab;
					}
				);
				/**
				 * Getting Sheet ID of working sheet
				 */
				if ( ! $sheet ) {
					   // if no sheet title matched, create new one with the title of the sheet.
					   $response = $this->create_sheet_tab( $this->sheet_tab );

					   $sheet = isset($response['replies'][0]['addSheet']) ? $response['replies'][0]['addSheet'] : [];
				} else {
					$sheet = array_values( $sheet )[0];
				}
				/**
				 * Save working Sheet ID to database for later use.
				 */
				$sheet_id = isset( $sheet['properties']['sheetId']) ? $sheet['properties']['sheetId'] : 0;
				$sheet_title = isset( $sheet['properties']['title']) ? $sheet['properties']['title'] : 'Sheet1';
				osgsw_update_option( 'sheet_id', $sheet_id );
				$updated = $this->sync_sheet_headers($sheet_title);
				$dropdown_values = $this->update_google_sheet_dropdowns($sheet_id);
				if ( ! $dropdown_values ) {
					$this->update_google_sheet_dropdowns($sheet_id);
				}
				return $updated;
			} catch ( \Exception $e ) {
				throw new \Exception( esc_html__( 'Unable to access Google Sheet. Please check required permissions.', 'order-sync-with-google-sheets-for-woocommerce' ) );
			}
		}
		/**
		 * Creates a new sheet tab.
		 *
		 * @param mixed $sheet_name Sheet Name.
		 */
		public function create_sheet_tab( $sheet_name = null ) {
			if ( ! $sheet_name ) {
				$sheet_name = $this->sheet_tab;
			}
			try {
				$access_token = $this->get_token();
				$spreadsheet_id     = $this->spreadsheet_id;
				$api_url = "https://sheets.googleapis.com/v4/spreadsheets/{$spreadsheet_id}:batchUpdate";
				$headers = [
					'Authorization' => "Bearer {$access_token}",
					'Content-Type' => 'application/json',
				];
				$request_body = wp_json_encode(
					[
						'requests' => [
							[
								'addSheet' => [
									'properties' => [
										'title' => $sheet_name,
									],
								],
							],
						],
					]
				);
				$response = wp_remote_post(
					$api_url,
					[
						'headers' => $headers,
						'body' => $request_body,
						'timeout' => 300,

					]
				);
				$response_body = wp_remote_retrieve_body( $response );
				$response_data = json_decode( $response_body, true );
				return $response_data;
			} catch ( \Exception $e ) {
				return false;
			}
		}
		/**
		 * Delete single row value using wp_remote_post.
		 *
		 * @param int $row_number Row number to delete (starting from 1).
		 * @return bool
		 */
		public function delete_single_row( $row_number = 2, $max = 2 ) {
			if ( ! $row_number || ! $max ) {
				return false;
			}

			$url = 'https://sheets.googleapis.com/v4/spreadsheets/' . $this->spreadsheet_id . ':batchUpdate';

			$request = array(
				'deleteDimension' => array(
					'range' => array(
						'sheetId' => $this->sheet_id,
						'dimension' => 'ROWS',
						'startIndex' => $row_number - 1,
						'endIndex' => $max,
					),
				),
			);

			$args = array(
				'method' => 'POST',
				'headers' => array(
					'Authorization' => 'Bearer ' . $this->get_token(),
					'Content-Type' => 'application/json',
				),
				'body' => wp_json_encode(array(
					'requests' => array( $request ),
				)),
				'timeout' => 300,
			);

			$response = wp_remote_request($url, $args);

			if ( is_wp_error($response) ) {
				return false;
			}

			$response_code = wp_remote_retrieve_response_code($response);

			if ( 200 === $response_code ) {
				return true;
			} else {
				return false;
			}
		}
		/**
		 * Syncs sheet headers
		 *
		 * @return mixed
		 */
		public function sync_sheet_headers( $title = false ) {
			try {
				$column   = new Column();
				$keys     = $column->get_column_names();
				$response = $this->update_row_values( 'A1', [ $keys ], $title );
				return $response;
			} catch ( \Exception $e ) {
				return false;
			}
		}
		/**
		 * Reset sheet
		 *
		 * @param mixed $access_token Access Token.
		 */
		public function reset_sheet( $access_token ) {
			try {
				$spreadsheet_id = $this->spreadsheet_id;
				$sheet_name     = $this->sheet_tab;
				$api_url = "https://sheets.googleapis.com/v4/spreadsheets/{$spreadsheet_id}/values/{$sheet_name}:clear";
				if ( empty( $access_token ) ) {
					$access_token = $this->get_token();
				}
				$headers = [
					'Authorization' => "Bearer {$access_token}",
				];
				$response = wp_remote_post(
					$api_url,
					[
						'headers' => $headers,
					]
				);
				$response_code = wp_remote_retrieve_body( $response );
				if ( 204 === $response_code ) {
					   return true;
				} else {
					return false;
				}
			} catch ( \Exception $e ) {
				return $e;
			}
		}
		/**
		 * Reset sheet
		 *
		 * @param mixed $access_token Access Token.
		 */
		public function reset_sheet2( $access_token, $title = false ) {
			try {
				$spreadsheet_id = $this->spreadsheet_id;
				if ( $title ) {
					$sheet_name     = $title;
				} else {
					$sheet_name = $this->sheet_tab;
				}

				$api_url = "https://sheets.googleapis.com/v4/spreadsheets/{$spreadsheet_id}/values/{$sheet_name}:clear";
				if ( empty( $access_token ) ) {
					$access_token = $this->get_token();
				}
				$headers = [
					'Authorization' => "Bearer {$access_token}",
				];
				$response = wp_remote_post(
					$api_url,
					[
						'headers' => $headers,
					]
				);
				$response_code = wp_remote_retrieve_body( $response );
				if ( 204 === $response_code ) {
					   return true;
				} else {
					return false;
				}
			} catch ( \Exception $e ) {
				return $e;
			}
		}
		/**
		 * Freezes headers.
		 *
		 * @param boolean $freeze Freeze.
		 * @return mixed
		 */
		public function freeze_headers( $freeze = true ) {
			try {
				$frozen_row_count = $freeze ? 1 : 0;
				$frozen_column_count = $freeze ? 1 : 0;
				// Build the batch update request to freeze/unfreeze headers.
				$batch_update_request = [
					'requests' => [
						[
							'updateSheetProperties' => [
								'properties' => [
									'sheetId' => $this->sheet_id,
									'gridProperties' => [
										'frozenRowCount' => $frozen_row_count,
										'frozenColumnCount' => $frozen_column_count,
									],
								],
								'fields' => 'gridProperties.frozenRowCount,gridProperties.frozenColumnCount',
							],
						],
					],
				];

				// Build the URL.
				$url = 'https://sheets.googleapis.com/v4/spreadsheets/' . $this->spreadsheet_id . ':batchUpdate';

				// Prepare the request arguments.
				$args = [
					'method' => 'POST',
					'headers' => [
						'Authorization' => 'Bearer ' . $this->get_token(),
						'Content-Type' => 'application/json',
					],
					'body' => wp_json_encode( $batch_update_request ),
					'timeout' => 300,
				];

				// Send the POST request.
				$response = wp_remote_post( $url, $args );

				if ( is_wp_error( $response ) ) {
					return false;
				}

				$response_code = wp_remote_retrieve_response_code( $response );

				if ( 200 === $response_code ) {
					return true;
				} else {
					return false;
				}
			} catch ( \Exception $e ) {
				return false;
			}
		}
		/**
		 * Get sheet all tab
		 *
		 * @return array
		 */
		public function get_sheet_tab() {
			$access_token = $this->get_token();
			$spreadsheet_id = $this->spreadsheet_id;
			$api_url = "https://sheets.googleapis.com/v4/spreadsheets/{$spreadsheet_id}?access_token={$access_token}";
			$headers = [
				'Authorization' => "Bearer {$access_token}",
			];
			$response = wp_remote_get( $api_url, [ 'headers' => $headers ] );

			if ( is_wp_error( $response ) ) {
				return [];
			} else {
				$response_body = wp_remote_retrieve_body( $response );
				$data = json_decode( $response_body, true );
				if ( isset( $data['sheets'] ) ) {
					return $data['sheets'];
				}
				return [];
			}
		}
		/**
		 * Update values in Google Sheet column C2:C with dropdown format.
		 *
		 * @param array  $dropdown_values Array of values to populate as dropdown options.
		 * @param string $last_row     The last number of the row.
		 * @return bool True if the update was successful, false otherwise.
		 */
		public function update_google_sheet_dropdowns( $sheet_id = false ) {
			$accessToken = $this->get_token();
			$spreadsheetId = $this->spreadsheet_id;
			$sheetId = $sheet_id;
			if ( ! $sheetId ) {
				$sheetId = $this->sheet_id;
			}
			$dropdownOptions = ossgsw_get_order_statuses();

			$dataValidationRule = [
				'setDataValidation' => [
					'range' => [
						'sheetId' => $sheetId,
						'startRowIndex' => 1,
						'startColumnIndex' => 2,
						'endColumnIndex' => 3,
					],
					'rule' => [
						'condition' => [
							'type' => 'ONE_OF_LIST',
							'values' => array_map(function ( $option ) {
								return [ 'userEnteredValue' => $option ];
							}, $dropdownOptions),
						],
						'showCustomUi' => true,
						'strict' => true,
					],
				],
			];

			// Prepare the batchUpdate request body
			$body = wp_json_encode([ 'requests' => [ $dataValidationRule ] ]);

			// Make the API call
			$response = wp_remote_post(
				"https://sheets.googleapis.com/v4/spreadsheets/$spreadsheetId:batchUpdate",
				[
					'headers' => [
						'Authorization' => 'Bearer ' . $accessToken,
						'Content-Type' => 'application/json',
					],
					'body' => $body,
				]
			);

			if ( is_wp_error($response) ) {
				return false;
			} else {
				return true;
			}
		}
	}
}
