<?php
/**
 * Abstract Class Base
 * includes/class/Base.php
 * Base class of the plugin
 *
 * @package OrderSyncWithGoogleSheetForWooCommerce
 */

namespace OrderSyncWithGoogleSheetForWooCommerce;

defined( 'ABSPATH' ) || die( 'No script kiddies please!' );

if ( ! class_exists( '\OrderSyncWithGoogleSheetForWooCommerce\Base' ) ) {
	/**
	 * Class OrderSyncWithGoogleSheetForWooCommerce\Base
	 *
	 * @version  1.0
	 */
	abstract class Base {
		/**
		 * Utilities Trait to use in all classes globally
		 */
		use Utilities;

		/**
		 * Instance of the Core App
		 *
		 * @var mixed
		 */
		protected $app = null;
		/**
		 * Instance of the Core App
		 *
		 * @var null
		 */
		public static $instance = null;
		/**
		 * Ajax constructor.
		 */
		public function __construct() {
			$this->app = new App();
		}
	}
}
