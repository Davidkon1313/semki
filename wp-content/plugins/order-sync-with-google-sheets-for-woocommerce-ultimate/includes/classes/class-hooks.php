<?php

/**
 * Class Hooks
 * includes/class/Hooks.php
 * Handle the actions and filters 
 */

namespace OrderSyncWithGoogleSheetForWooCommerceUltimate;

use OrderSyncWithGoogleSheetForWooCommerce\Order;
use OrderSyncWithGoogleSheetForWooCommerce\Sheet;

defined('ABSPATH') or die('No script kiddies please!');

if (!class_exists('\OrderSyncWithGoogleSheetForWooCommerceUltimate\Hooks')) {
    final class Hooks
    {
        /**
         * Manipulating the plugin activation will NOT unlock the premium features
         */
        protected $license_active = false;
        /**
         * 
         * App
         */
        public $app = null;
        /**
         * Constructor 
         */
        public function __construct()
        {
            $this->app = new App();
            if ($this->is_plugin_activated()) {
                $this->init_appsero_client();
            }
            $this->add_filters();

            $this->add_actions();
        }
        /**
         * Check if the main plugin is activated
         */
        public function is_plugin_activated()
        {
            $active_plugins = get_option('osgsw_free_active', 0);
            if (true == $active_plugins) {
                return true;
            }
            return false;
        }
        /**
         * Add actions
         */
        public function add_actions()
        {
            /**
             * Init Appsero
             */
            // add_action('plugins_loaded', array($this, 'init_appsero_client')); 
            /**
             * Plugins loaded
             */
            add_action('pre_current_active_plugins', [$this, 'admin_notices']);
            add_action('admin_init', [$this, 'redirect_to_admin_page'], 9999);

            add_action('osgsw_license_active_trigger', [$this, 'osgsw_license_active_trigger']);
            add_action('osgsw_license_deactive_trigger', [$this, 'osgsw_license_deactive_trigger']);
            add_filter('ossgs_order_hpos_fields', [$this, 'ossgs_order_hpos_fields'], 10, 1);
            add_filter('ossgs_order_post_fields', [$this, 'ossgs_order_post_fields'], 10, 1);
            add_filter('osgsw_update_custom_order_status', [$this, 'update_custom_order_status'], 10, 1);
        }


        /**
         * Custom order status update
         */
        public function update_custom_order_status($defult = [])
        {
            if (is_plugin_active('woocommerce/woocommerce.php') || is_plugin_active_for_network('woocommerce/woocommerce.php')) {
                $statuses = wc_get_order_statuses();
                $keys = array_keys($statuses);
                return $keys;
            } else {
                return $defult;
            }
        }
        /**
         * Add Hpos fields
         */
        public function ossgs_order_hpos_fields($order)
        {
            global $wpdb;
            $total_discount   = true === wp_validate_boolean(osgsw_get_option('total_discount', false));
            $shipping_details = true === wp_validate_boolean(osgsw_get_option('add_shipping_details_sheet', false));
            $order_date       = true === wp_validate_boolean(osgsw_get_option('show_order_date', false));
            $payment_method   = true === wp_validate_boolean(osgsw_get_option('show_payment_method', false));
            $customer_note    = true === wp_validate_boolean(osgsw_get_option('show_customer_note', false));
            $order_url        = true === wp_validate_boolean(osgsw_get_option('show_order_url', false));
            $who_place_order  = true === wp_validate_boolean(osgsw_get_option('who_place_order', false));

            $show_custom_meta_fields = true === wp_validate_boolean(osgsw_get_option('show_custom_meta_fields', false));
            $show_custom_fields = true === wp_validate_boolean(osgsw_get_option('show_custom_fields', false));

            $billing_deatils  = true === wp_validate_boolean(osgsw_get_option('show_billing_details', false));
            $order_urls       = esc_url(admin_url('post.php?'));
            $order_note  = true === wp_validate_boolean(osgsw_get_option('show_order_note', false));

           
            $table_name_exits = "{$wpdb->base_prefix}users";
            $table_exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table_name_exits));


            if ($total_discount) {
                $order .= ', MAX( cf.discount_total_amount ) as discount';
            }
            if ($billing_deatils) {
                $order .= ", IFNULL(MAX( CASE WHEN pm.meta_key = '_billing_address_index' AND p.id = pm.order_id THEN pm.meta_value END ), 'Billing Details Not Set' ) as billing_details";
            }
            if ($shipping_details) {
                $order .= ", IFNULL(MAX( CASE WHEN pm.meta_key = '_shipping_address_index' AND p.id = pm.order_id THEN pm.meta_value END ), 'Shipping Details Not Set' ) as shipping_details";
            }
            if ($order_date) {
                $order .= ', p.date_created_gmt';
            }
            if ($payment_method) {
                $order .= ", IFNULL(p.payment_method_title, 'No Method Selected' ) as method_title";
            }
            if ($customer_note) {
                $order .= ", IFNULL(p.customer_note, 'No notes from customer' ) as customer_note";
            }
            if ($table_exists) {
                if ($who_place_order) {
                    $order .= ", IFNULL(
                        CASE 
                            WHEN MAX(p.customer_id) = 0 THEN 'Anonymous user'
                            ELSE (
                                SELECT u.user_login 
                                FROM {$wpdb->base_prefix}users u 
                                WHERE u.ID = p.customer_id
                            )
                        END, 
                        'Anonymous user'
                    ) as who_place_order";
                }
            } else {

                if ($who_place_order) {
                    $order .= ", 'Anonymous user' as who_place_order";
                }
            }
            if ($order_url) {
                $order .= ', ';
                $order .= "CONCAT('" . $order_urls . "','post=',pm.order_id,'&action=edit')";
                $order .= ' as order_urls';
            }
            if ($order_note) {
                $order .= ", (
                    SELECT IFNULL(
                        GROUP_CONCAT(comment_content SEPARATOR ', '), 
                        '** No Comment Found **'
                    ) AS order_note
                    FROM {$wpdb->prefix}comments
                    WHERE comment_post_ID = p.id
                    AND comment_type = 'order_note'
                ) as order_note";
            }
            if ($shipping_details) {
                $order .= ", IFNULL(MAX( CASE WHEN pm.meta_key = '_shipping_address_index' AND p.id = pm.order_id THEN pm.meta_value END ), 'Shipping Details Not Set' ) as shipping_details";
            }
            if ($show_custom_meta_fields && $show_custom_fields) {
                $custom_fields = osgsw_get_option('show_custom_fields');
                foreach ($custom_fields as $value) {
                    $item_extis = osgsw_divided_prefix($value, '(Itemmeta)');
                    if ($item_extis) {
                        $value2 = $item_extis['before'];
                        $custom_field = $wpdb->prepare(
                            "(
                                SELECT IFNULL(
                                    GROUP_CONCAT(CONCAT(order_item_name, '(ssgsw_itemmeta_value:', c.meta_value, 'ssgsw_itemmeta_end)') SEPARATOR 'ssgsw_sep, '), 
                                    '** No Item Found **'
                                ) 
                                FROM {$wpdb->prefix}woocommerce_order_items AS oi 
                                LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS c 
                                ON oi.order_item_id = c.order_item_id 
                                AND c.meta_key = %s 
                                WHERE oi.order_id = p.id
                            ) AS %s",
                            $value2,
                            $value
                        );
                        $order .= ', ' . $custom_field;
                    } else {
                        $custom_field = $wpdb->prepare("IFNULL(MAX( CASE WHEN pm.meta_key = %s AND p.id = pm.order_id THEN pm.meta_value END ), '' ) as %s", $value, $value);
                        $order .= ', ' . $custom_field;
                    }
                }
            }
            return apply_filters('ossgs_extra_columns_hpos', $order);
        }
        /**
         * Add post fields
         */
        public function ossgs_order_post_fields($order)
        {
            global $wpdb;
            $total_discount   = true === wp_validate_boolean(osgsw_get_option('total_discount', false));
            $shipping_details = true === wp_validate_boolean(osgsw_get_option('add_shipping_details_sheet', false));
            $order_date       = true === wp_validate_boolean(osgsw_get_option('show_order_date', false));
            $payment_method   = true === wp_validate_boolean(osgsw_get_option('show_payment_method', false));
            $customer_note    = true === wp_validate_boolean(osgsw_get_option('show_customer_note', false));
            $order_url        = true === wp_validate_boolean(osgsw_get_option('show_order_url', false));
            $who_place_order  = true === wp_validate_boolean(osgsw_get_option('who_place_order', false));

            $show_custom_meta_fields = true === wp_validate_boolean(osgsw_get_option('show_custom_meta_fields', false));
            $show_custom_fields = true === wp_validate_boolean(osgsw_get_option('show_custom_fields', false));
            $billing_deatils  = true === wp_validate_boolean(osgsw_get_option('show_billing_details', false));

            $order_note  = true === wp_validate_boolean(osgsw_get_option('show_order_note', false));
          
            $order_urls       = esc_url(admin_url('post.php?'));

            $table_name_exits = "{$wpdb->base_prefix}users";
            $table_exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table_name_exits));
            
            if ($total_discount) {
                $order .= ", MAX( CASE WHEN pm.meta_key = '_cart_discount' AND p.ID = pm.post_id THEN pm.meta_value END ) as discount";
            }
            if ($billing_deatils) {
                $order .= ", IFNULL(MAX( CASE WHEN pm.meta_key = '_billing_address_index' AND p.ID = pm.post_id THEN pm.meta_value END ), 'Billing Details Not Set' ) as billing_details";
            }
            if ($shipping_details) {
                $order .= ", IFNULL(MAX( CASE WHEN pm.meta_key = '_shipping_address_index' AND p.ID = pm.post_id THEN pm.meta_value END ), 'Shipping Details Not Set' ) as shipping_details";
            }
            if ($order_date) {
                $order .= ', p.post_date';
            }
            if ($payment_method) {
                $order .= ", IFNULL(MAX( CASE WHEN pm.meta_key = '_payment_method' AND p.ID = pm.post_id THEN pm.meta_value END ), 'No Method Selected' ) as method_title";
            }
            if ($customer_note) {
                $order .= ", IFNULL(p.post_excerpt, 'No notes from customer' ) as customer_note";
            }
            if ($table_exists) {
                if ($who_place_order) {
                    $order .= ", IFNULL(
                        CASE 
                            WHEN MAX(CASE WHEN pm.meta_key = '_customer_user' AND p.ID = pm.post_id THEN pm.meta_value END) = 0 THEN 'Anonymous user'
                            ELSE (SELECT u.user_login FROM {$wpdb->base_prefix}users u WHERE u.ID = MAX(CASE WHEN pm.meta_key = '_customer_user' AND p.ID = pm.post_id THEN pm.meta_value END))
                        END, 
                        'Anonymous user'
                    ) as who_place_order";
                }
            } else {
                if ($who_place_order) {
                    $order .= ", 'Anonymous user' as who_place_order";
                }
            }

            if ($order_url) {
                $order .= ', ';
                $order .= "CONCAT('" . $order_urls . "','post=',pm.post_id,'&action=edit')";
                $order .= ' as order_urls';
            }
            if ($order_note) {
                $order .= ", (
                    SELECT IFNULL(
                        GROUP_CONCAT(comment_content SEPARATOR ', '), 
                        '** No Comment Found **'
                    ) AS order_note
                    FROM {$wpdb->prefix}comments
                    WHERE comment_post_ID = p.ID
                    AND comment_type = 'order_note'
                ) as order_note";
            }

            
            if ($show_custom_meta_fields && $show_custom_fields) {
                $custom_fields = osgsw_get_option('show_custom_fields');
                foreach ($custom_fields as $value) {
                    $item_extis = osgsw_divided_prefix($value, '(Itemmeta)');
                    if ($item_extis) {
                        $value2 = $item_extis['before'];
                        $custom_field = $wpdb->prepare(
                            "(
                                SELECT IFNULL(
                                    GROUP_CONCAT(CONCAT(order_item_name, '(ssgsw_itemmeta_value:', c.meta_value, 'ssgsw_itemmeta_end)') SEPARATOR 'ssgsw_sep, '), 
                                    '** No Item Found **'
                                ) 
                                FROM {$wpdb->prefix}woocommerce_order_items AS oi 
                                LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS c 
                                ON oi.order_item_id = c.order_item_id 
                                AND c.meta_key = %s 
                                WHERE oi.order_id = p.ID
                            ) AS %s",
                            $value2,
                            $value
                        );
                        $order .= ', ' . $custom_field;
                    } else {
                        $custom_field = $wpdb->prepare("IFNULL(MAX( CASE WHEN pm.meta_key = %s AND p.ID = pm.post_id THEN pm.meta_value END ), '' ) as %s", $value, $value);
                        $order .= ', ' . $custom_field;
                    }
                }
            }
            return apply_filters('ossgs_extra_columns', $order);
        }
        /**
         * Acitve license
         */
        public function osgsw_license_active_trigger()
        {
            $items = ['total_discount', 'add_shipping_details_sheet', 'show_order_date', 'show_payment_method', 'show_customer_note', 'show_order_url', 'who_place_order', 'show_product_qt', 'show_billing_details', 'show_custom_meta_fields', 'custom_order_status_bolean','show_order_note', 'product_sku_sync' ];
            $value = true;
            foreach ($items as $item) {
                update_option('osgsw_' . $item, $value);
            }

            $order = new Order();
            $order->sync_all();
            delete_option('osgsw_synced');
        }
        /**
         * Acitve license
         */
        public function osgsw_license_deactive_trigger()
        {
            $items = ['total_discount', 'add_shipping_details_sheet', 'show_order_date', 'show_payment_method', 'show_customer_note', 'show_order_url', 'who_place_order', 'show_product_qt', 'show_billing_details', 'show_custom_meta_fields', 'custom_order_status_bolean', 'multiple_itmes', 'show_order_note', 'product_sku_sync'];
            $value = false;
            foreach ($items as $item) {
                update_option('osgsw_' . $item, $value);
            }
            $product = new Order();
            $product->sync_all();
            
            delete_option('osgsw_synced');
        }
        /**
         * Redirect to admin page
         */
        public function redirect_to_admin_page()
        {
            $redirect_to_admin_page = get_option('osgsw_redirect_to_license_page', 0);
            if (0 == $redirect_to_admin_page) {
                if ($this->is_plugin_activated()) {
                    update_option('osgsw_redirect_to_license_page', 1);
                    wp_redirect(admin_url('admin.php?page=osgsw-license'));
                    exit;
                }
            }
        }
        /**
         * Add filters
         */
        public function add_filters()
        {
            # Add promotional link to plugin meta links
            add_filter('plugin_row_meta', [$this, 'add_plugin_meta_links'], 10, 2);
        }
        /**
         * Add settings link
         *
         * @param $links
         * @param $file
         * @return array
         */
        public function add_plugin_meta_links($links, $file)
        {
            if ($file == plugin_basename(OSGSW_ULTIMATE)) {
                $links[] = '<a target="_blank" href="https://wppool.dev/contact/"> <span class="dashicons dashicons-editor-help" aria-hidden="true" style="font-size:16px;line-height:1.2"></span>Premium Support</a>';
            }
            return $links;
        }
        /**
         * Init appsero client
         */
        public function init_appsero_client()
        {
            $client = $this->app->get_appsero_client();
            if (!$client) {
                return;
            }
            // Active insights.
            $client->insights()->hide_notice()->init();
            // Active automatic updater.
            \OrderSyncWithGoogleSheetForWooCommerceUltimate\Appsero\Updater::init($client);

            // Active license page and checker.
            $args = [
                'type'        => 'submenu',
                'menu_title'  => __('License', OSGSW_ULTIMATE_TEXT_DOMAIN),
                'page_title'  => __('Order Sync with Google Sheets for WooCommerce Ultimate License', OSGSW_ULTIMATE_TEXT_DOMAIN),
                'menu_slug'   => 'osgsw-license',
                'parent_slug' => 'osgsw-admin'
            ];
            // IF license is valid, then add the license checker.

            global $osgsw_license;
            $this->license_active = $client->license();
            $client->license()->add_settings_page($args);
            $osgsw_license = $this->license_active;
            $this->is_license_active();
        }
        /**
         * Check if license is active.
         *
         * @return mixed
         */
        public function is_license_valid()
        {
            if (!$this->license_active) {
                return false;
            }

            return $this->license_active->is_valid();
        }
        /**
         * Admin notices
         */
        public function admin_notices()
        {
            if ($this->is_plugin_activated()) {
                return;
            }
            if (!current_user_can('activate_plugins')) {
                return;
            }
            $plugin_url = $this->app->free_version;
            $pluginName  = __('Order Sync with Google Sheets for WooCommerce Ultimate', OSGSW_ULTIMATE_TEXT_DOMAIN);
            $dependency  = __('Order Sync with Google Sheets for WooCommerce', OSGSW_ULTIMATE_TEXT_DOMAIN);
            if ($this->app->is_plugin_installed()) {
                $activation_url = wp_nonce_url('plugins.php?action=activate&amp;plugin=' . $plugin_url . '&amp;plugin_status=all&amp;paged=1&amp;s', 'activate-plugin_' . $plugin_url);
                $message     = sprintf(__('<strong>%s</strong> requires <strong>%s</strong> plugin to be Activate.', OSGSW_ULTIMATE_TEXT_DOMAIN), $pluginName, $dependency);
                $button_text = __('Activate', OSGSW_ULTIMATE_TEXT_DOMAIN);
            } else {
                $activation_url = wp_nonce_url(self_admin_url('update.php?action=install-plugin&plugin=order-sync-with-google-sheets-for-woocommerce'), 'install-plugin_order-sync-with-google-sheets-for-woocommerce');
                $message        = sprintf(__('<strong>%s</strong> requires <strong><a style="text-decoration:none" href="%s" target="__blank">%s</a></strong> plugin to be installed and activated.', OSGSW_ULTIMATE_TEXT_DOMAIN), $pluginName, 'https://wordpress.org/plugins/order-sync-with-google-sheets-for-woocommerce/', $dependency);
                $button_text    = __('Install', OSGSW_ULTIMATE_TEXT_DOMAIN);
            }
            $button = '<p><a href="' . $activation_url . '" class="button-primary">' . $button_text . '</a></p>';

            printf('<div class="error"><p style="margin-top:2px;margin-bottom: -8px;">%s</p><p class="osgs_info">%s</p></div>', $message, $button);
        }
        /**
         * Activate license 
         */
        public function is_license_active()
        {
            if ($this->is_license_valid()) {
                update_option('osgsw_license_active', true);
            } else {
                update_option('osgsw_license_active', false);
            }
        }
        /**
         * Check license status active
         */
        public function is_license_actived()
        {
            if ($this->license_active) {
                return true;
            }
            return false;
        }
    }
    /**
     * Initialize Hook
     */

    $hook = new Hooks();
}
