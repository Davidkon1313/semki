<?php
/**
 * Plugin Name: WPSyncSheets For WooCommerce
 * Plugin URI: https://www.wpsyncsheets.com/wpsyncsheets-for-woocommerce/
 * Description: Synchronize your WooCommerce orders, products, customers, coupons, and events to a single Google Spreadsheet.
 * Author: Creative Werk Designs
 * Author URI: https://www.creativewerkdesigns.com/
 * Text Domain: wpssw
 * Domain Path: /languages
 * Version: 9.2.0
 * WC tested up to: 4.6.0
 *
 * @package     wpsyncsheets-for-woocommerce
 * @author      Creative Werk Designs
 * @category    Plugin
 * @copyright   Copyright (c) 2021 Creative Werk Designs
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
define( 'WPSSW_PLUGIN_SECURITY', 1 );
define( 'WPSSW_URL', plugin_dir_url( __FILE__ ) );
define( 'WPSSW_VERSION', '9.2.0' );
define( 'WPSSW_PLUGIN_ID', '22636997' );
define( 'WPSSW_PLUGIN_ITEM_ID', '1378' );
define( 'WPSSW_STORE_URL', 'https://www.wpsyncsheets.com/' );
define( 'WPSSW_PLUGIN_NAME', 'WPSyncSheets For WooCommerce' );
define( 'WPSSW_PLUGIN_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
define( 'WPSSW_DIRECTORY', dirname( plugin_basename( __FILE__ ) ) );
define( 'WPSSW_PLUGIN_SLUG', WPSSW_DIRECTORY . '/' . basename( __FILE__ ) );
define( 'WPSSW_BASE_FILE', basename( dirname( __FILE__ ) ) . '/wpsyncsheets-for-woocommerce.php' );
define( 'WPSSW_DOC_MENU_URL', 'https://docs.wpsyncsheets.com' );
define( 'WPSSW_SUPPORT_MENU_URL', 'https://www.wpsyncsheets.com/support-tickets/' );
if ( ! class_exists( 'WPSSW_Dependencies' ) ) {
	require_once trailingslashit( dirname( __FILE__ ) ) . 'includes/class-wpssw-dependencies.php';
}
// Check WPSSW Dependency Class and WooCommerce Activation.
if ( WPSSW_Dependencies::wpssw_is_woocommerce_active() ) {
	// Add methods if WooCommerce is active.
	add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'wpssw_add_action_links' );

	// User Role Editor integration.
	add_filter( 'ure_capabilities_groups_tree', 'wpssw_filter_ure_capabilities_groups_tree' );
	add_filter( 'ure_custom_capability_groups', 'wpssw_filter_ure_custom_capability_groups', 10, 2 );

	/**
	 * Register WPSyncSheets For WooCommerce capabilities group with User Role Editor plugin.
	 *
	 * @since  5.0
	 *
	 * @param array $groups Existing capabilities groups.
	 *
	 * @return array
	 */
	function wpssw_filter_ure_capabilities_groups_tree( $groups = array() ) {

		$groups['wpssw'] = array(
			'caption' => esc_html__( 'WPSyncSheets For WooCommerce', 'wpssw' ),
			'parent'  => 'custom',
			'level'   => 2,
		);
		return $groups;
	}

	/**
	 * Register WPSyncSheets For WooCommerce capabilities with WPSyncSheets For WooCommerce group in User Role Editor plugin.
	 *
	 * @since  5.0
	 *
	 * @param array  $groups Current capability groups.
	 * @param string $cap_id Capability identifier.
	 *
	 * @return array
	 */
	function wpssw_filter_ure_custom_capability_groups( $groups = array(), $cap_id = '' ) {
		wpssw_add_cap();
		// Get WooCommerce Forms capabilities.
		$caps = array( 'edit_wpssw_settings' );
		// If capability belongs to WPSyncSheets For WooCommerce, register it to group.
		if ( in_array( $cap_id, $caps, true ) ) {
			$groups[] = 'wpssw';
		}
		return $groups;
	}
	/**
	 * Add Capability.
	 */
	function wpssw_add_cap() {
		$roles = get_editable_roles();
		foreach ( $GLOBALS['wp_roles']->role_objects as $key => $role ) {
			if ( 'administrator' === (string) $key && isset( $roles[ $key ] ) ) {
				$role->add_cap( 'edit_wpssw_settings' );
			}
		}
	}
	/**
	 * Add Settings Link.
	 *
	 * @param array $links Links array.
	 */
	function wpssw_add_action_links( $links ) {
		$mylinks = array(
			'<a href="' . admin_url( 'admin.php?page=wpsyncsheets-for-woocommerce' ) . '">Settings</a>',
		);
		return array_merge( $mylinks, $links );
	}
	add_action(
		'before_woocommerce_init',
		function() {
			if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
				\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
			}
		}
	);
	require_once dirname( __FILE__ ) . '/src/class-wpsyncsheets-for-woocommerce.php';
	wpssw();
} else {
	add_action( 'admin_notices', 'wpssw_wc_admin_notice' );
	if ( ! function_exists( 'wpssw_wc_admin_notice' ) ) {
		/**
		 * Add plugin missing notice.
		 */
		function wpssw_wc_admin_notice() {
			global $pagenow;
			// phpcs:ignore
			if ( 'plugins.php' === (string) $pagenow || ( isset( $_GET['page'] ) && ( 'wpsyncsheets-for-woocommerce' === (string) sanitize_text_field( $_GET['page'] ) ) ) ) {
				echo '<div class="notice error wpssw-error">
				<div>
					<p>WPSyncSheets For WooCommerce plugin requires <a href="http://wordpress.org/extend/plugins/woocommerce/">WooCommerce</a> plugin to be active!</p>
				</div>
			</div>';
			}
		}
	}
}
