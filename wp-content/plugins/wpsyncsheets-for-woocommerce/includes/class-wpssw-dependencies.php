<?php
/**
 * Main WPSyncSheets_For_WooCommerce namespace.
 *
 * @since 1.0.0
 * @package wpsyncsheets-for-woocommerce
 */

/**
 * WPSSW Dependency Checker
 */
class WPSSW_Dependencies {
	/**
	 * Active plugins
	 *
	 * @var $active_plugins
	 */
	private static $active_plugins;
	/**
	 * Initialization
	 */
	public static function init() {
		if ( is_multisite() ) {
			if ( ! function_exists( 'is_plugin_active_for_network' ) ) {
				require_once ABSPATH . '/wp-admin/includes/plugin.php';
			}
			// Check WooCommerce active at the network site.
			if ( is_plugin_active_for_network( 'woocommerce/woocommerce.php' ) ) {
				self::$active_plugins = (array) get_site_option( 'active_sitewide_plugins', array() );
			} else { // Check WooCommerce active at the network individual site.
				self::$active_plugins = (array) get_option( 'active_plugins', array() );
			}
		} else {
			self::$active_plugins = (array) get_option( 'active_plugins', array() );
		}
	}
	/**
	 * Check woocommerce exist
	 *
	 * @return Boolean
	 */
	public static function wpssw_woocommerce_active_check() {
		if ( ! self::$active_plugins ) {
			self::init();
		}
		if ( in_array( 'woocommerce/woocommerce.php', self::$active_plugins, true ) || array_key_exists( 'woocommerce/woocommerce.php', self::$active_plugins ) ) {
			if ( ! function_exists( 'is_plugin_active' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}
			if ( ! is_plugin_active( 'wpsyncsheets-woocommerce/wpsyncsheets-lite-woocommerce.php' ) ) {
				add_action( 'admin_notices', 'WPSSW_Dependencies::wpsyncsheets_missing_dependency_notice' );
			}
			return true;
		}
		return false;
	}
	/**
	 * Check if woocommerce active
	 *
	 * @return Boolean
	 */
	public static function wpssw_is_woocommerce_active() {
		return self::wpssw_woocommerce_active_check();
	}

	/**
	 * Display an admin notice
	 */
	public static function wpsyncsheets_missing_dependency_notice() {
		?>
		<div class="notice notice-error">
			<?php
			$installed_plugins = get_plugins();
			if ( isset( $installed_plugins['wpsyncsheets-woocommerce/wpsyncsheets-lite-woocommerce.php'] ) ) {
				// The plugin is installed but not active.
				$activate_url = wp_nonce_url( admin_url( 'plugins.php?action=activate&plugin=wpsyncsheets-woocommerce/wpsyncsheets-lite-woocommerce.php' ), 'activate-plugin_wpsyncsheets-woocommerce/wpsyncsheets-lite-woocommerce.php' );
				?>
			<h3><?php echo esc_html__( "You're not using WPSyncSheets For WooCommerce Pro yet!", 'wpssw' ); ?></h3>
			<p><?php echo esc_html__( "Activate the WPSyncSheets Lite For WooCommerce plugin to start using all of WPSyncSheets For WooCommerce Pro plugin's features.", 'wpssw' ); ?></p>
			<p>
			<a href="<?php echo esc_url( $activate_url ); ?>" class="button button-primary">
				<?php echo esc_html__( 'Activate Now', 'wpssw' ); ?>
			</a>
			</p>
				<?php
			} else {
				// The plugin is not installed.
				$install_url = wp_nonce_url( admin_url( 'update.php?action=install-plugin&plugin=wpsyncsheets-woocommerce' ), 'install-plugin_wpsyncsheets-woocommerce' );
				?>
			<h3><?php echo esc_html__( 'WPSyncSheets For WooCommerce Pro plugin requires WPSyncSheets Lite For WooCommerce to be installed and activated', 'wpssw' ); ?></h3>
			<p><?php echo esc_html__( 'Install and activate the WPSyncSheets Lite For WooCommerce plugin to access all the Pro features.', 'wpssw' ); ?></p>
			<p>
			<a href="<?php echo esc_url( $install_url ); ?>" class="button button-primary">
				<?php echo esc_html__( 'Install Now', 'wpssw' ); ?>
			</a>
			</p>
			<?php } ?>
		</div>
		<?php
	}
}
