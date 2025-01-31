<?php
/**
 * Trait Utilities
 * includes/helper/Utilities.php
 * Utilities Trait to use in all classes globally
 *
 * @package OrderSyncWithGoogleSheetForWooCommerce
 */

namespace OrderSyncWithGoogleSheetForWooCommerce;

defined( 'ABSPATH' ) || die( 'No script kiddies please!' );

if ( ! trait_exists( '\OrderSyncWithGoogleSheetForWooCommerce\Utilities' ) ) {
	/**
	 * Trait Utilities
	 */
	trait Utilities {
		/**
		 * Is Development Mode
		 */
		public function is_development_mode() { //phpcs:ignore
			return defined( 'WP_DEBUG' ) && WP_DEBUG;
		}
		/**
		 * Log message
		 *
		 * @param string $message Message.
		 * @return mixed
		 */
		public function log( $message ) { //phpcs:ignore
			if ( ! $this->is_development_mode() ) {
				return;
			}

			$args = func_get_args();

			foreach ( $args as $arg ) {
				if ( is_array( $arg ) || is_object( $arg ) ) {
					error_log( print_r( $arg, true ) );
				} else {
					error_log( $arg );
				}
			}
		}

		/**
		 * Debug message
		 */
		public function debug() { //phpcs:ignore
			if ( ! $this->is_development_mode() ) {
				return;
			}

			$args = func_get_args();

			echo '<pre>';
			foreach ( $args as $arg ) {
				if ( is_array( $arg ) || is_object( $arg ) ) {
					print_r( $arg );
				} else {
					var_dump( $arg );
				}
			}
			echo '</pre>';
		}
		/**
		 * Sends json response.
		 *
		 * @param bool   $success Whether the request was successful or not.
		 * @param string $data   The data to send.
		 * @return void
		 */
		public function send_json( $success, $data = null ) { //phpcs:ignore
			$response = [
				'success' => $success,
			];
			if ( $data ) {
				if ( is_array( $data ) || is_object( $data ) ) {
					$response['data'] = $data;
				} else {
					$response['message'] = $data;
				}
			}
			wp_send_json( $response, 200 );
			wp_die();
		}
		/**
		 * Sanitize array recursively.
		 *
		 * @param mixed $array The array to sanitize.
		 * @return mixed
		 */
		public function sanitize( $array ) { //phpcs:ignore
			foreach ( $array as $key => $value ) {
				if ( is_array( $value ) ) {
					$array[ $key ] = $this->sanitize( $value );
				} else {
					$array[ $key ] = sanitize_text_field( $value );
				}
			}
			return $array;
		}

		/**
		 * Get request body
		 */
		public function get_body() { //phpcs:ignore
			$inputs = json_decode( file_get_contents( 'php://input' ), true );
			if ( is_array( $inputs ) ) {
				return (object) $this->sanitize( $inputs );
			}

			return (object) [];
		}
		/**
		 * Loads template file.
		 *
		 * @param string $template The template file name.
		 * @param array  $data     The data to pass to the template.
		 * @return void
		 */
		public function load_template( $template, $data = [] ) { //phpcs:ignore
			if ( file_exists( OSGSW_TEMPLATES . $template . '.php' ) ) {
				include OSGSW_TEMPLATES . $template . '.php';
			}
		}
	}
}
