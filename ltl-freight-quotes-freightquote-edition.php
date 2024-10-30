<?php
/*
  Plugin Name: LTL Freight Quotes - Freightquote Edition
  Plugin URI: https://eniture.com/products/
  Description: Obtains a dynamic estimate of LTL Freight rates via the FreightQuote API for your orders.
  Author: Eniture Technology
  Author URI: http://eniture.com/
  Version: 2.3.8
  Text Domain: eniture-freightquote-ltl
  License:           GPL-2.0-or-later
  License URI:       https://www.gnu.org/licenses/gpl-2.0.html
  Requires at least: 6.6
  Requires PHP:      7.4
 */

/**
 * FreightQuote LTL Shipping Plugin
 *
 * @package     FreightQuote LTL
 * @author      Eniture-Technology
 * LTL Freightquote for WooCommerce - Freightquote Edition
 * Copyright (C) 2016  Eniture LLC d/b/a Eniture Technology
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License version 2
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Inquiries can be emailed to info@eniture.com or sent via the postal service to Eniture Technology, 320 W. Lanier Ave, Suite 200, Fayetteville, GA 30214, USA.
 */
if (!defined('ABSPATH')) {
    exit;
}
define('FREIGHTQUOTE_DOMAIN_HITTING_URL', 'https://ws061.eniture.com');
define('FREIGHTQUOTE_FDO_HITTING_URL', 'https://freightdesk.online/api/updatedWoocomData');
define('FREIGHTQUOTE_MAIN_FILE', __FILE__);

add_action('before_woocommerce_init', function () {
    if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
    }
});

//multisite constant
global $wpdb;
$en_carrier_table =  $wpdb->prefix . "freightQuote_carriers";
define('FREIGHTQUOTE_FREIGHT_CARRIERS', $en_carrier_table);
// Define reference
function freightquote_en_plugin($plugins)
{
    $plugins['lfq'] = (isset($plugins['lfq'])) ? array_merge($plugins['lfq'], ['freightquote_ltl_shipping_method' => 'Freightquote_WC_Shipping_Method']) : ['freightquote_ltl_shipping_method' => 'Freightquote_WC_Shipping_Method'];
    return $plugins;
}

add_filter('en_plugins', 'freightquote_en_plugin');

add_action('admin_enqueue_scripts', 'freightquote_ltl_admin_script');

if (!function_exists('is_plugin_active')) {
    require_once(ABSPATH . 'wp-admin/includes/plugin.php');
}

if (!function_exists('freightquote_en_woo_plans_notification_PD')) {

    function freightquote_en_woo_plans_notification_PD($product_detail_options)
    {
        $eniture_plugins_id = 'eniture_plugin_';

        for ($en = 1; $en <= 25; $en++) {
            $settings = get_option($eniture_plugins_id . $en);

            if (isset($settings) && !empty($settings) && is_array($settings)) {
                $plugin_detail = current($settings);
                $plugin_name = (isset($plugin_detail['plugin_name'])) ? $plugin_detail['plugin_name'] : "";

                foreach ($plugin_detail as $key => $value) {
                    if ($key != 'plugin_name') {
                        $action = $value === 1 ? 'enable_plugins' : 'disable_plugins';
                        $product_detail_options[$key][$action] = (isset($product_detail_options[$key][$action]) && strlen($product_detail_options[$key][$action]) > 0) ? ", $plugin_name" : "$plugin_name";
                    }
                }
            }
        }

        return $product_detail_options;
    }

    add_filter('freightquote_en_woo_plans_notification_action', 'freightquote_en_woo_plans_notification_PD', 10, 1);
}

if (!function_exists('freightquote_en_woo_plans_notification_message')) {

    function freightquote_en_woo_plans_notification_message($enable_plugins, $disable_plugins)
    {
        $enable_plugins = (strlen($enable_plugins) > 0) ? "$enable_plugins: <b> Enabled</b>. " : "";
        $disable_plugins = (strlen($disable_plugins) > 0) ? " $disable_plugins: Upgrade to <b>Standard Plan to enable</b>." : "";
        return $enable_plugins . "<br>" . $disable_plugins;
    }

    add_filter('freightquote_en_woo_plans_notification_message_action', 'freightquote_en_woo_plans_notification_message', 10, 2);
}

/**
 * Load scripts for FreightQuote Freight json tree view
 */
if (!function_exists('freightquote_en_jtv_script')) {
    function freightquote_en_jtv_script()
    {
        wp_register_style('freightquote_en_json_tree_view_style', plugin_dir_url(__FILE__) . 'logs/en-json-tree-view/en-jtv-style.css');
        wp_register_script('en_freightquote_json_tree_view_script', plugin_dir_url(__FILE__) . 'logs/en-json-tree-view/en-jtv-script.js', ['jquery'], '1.0.1');

        wp_enqueue_style('freightquote_en_json_tree_view_style');
        wp_enqueue_script('en_freightquote_json_tree_view_script', [
            'en_tree_view_url' => plugins_url(),
        ]);
    }

    add_action('admin_init', 'freightquote_en_jtv_script');
}

/**
 * Get Host
 * @param type $url
 * @return type
 */
if (!function_exists('freightquote_getHost')) {

    function freightquote_getHost($url)
    {
        $parseUrl = wp_parse_url(trim($url));
        if (isset($parseUrl['host'])) {
            $host = $parseUrl['host'];
        } else {
            $path = explode('/', $parseUrl['path']);
            $host = $path[0];
        }
        return trim($host);
    }

}

//Product detail set plans notification message for nested checkbox
if (!function_exists('freightquote_en_woo_plans_nested_notification_message')) {

    function freightquote_en_woo_plans_nested_notification_message($enable_plugins, $disable_plugins, $feature)
    {
        $enable_plugins = (strlen($enable_plugins) > 0) ? "$enable_plugins: <b> Enabled</b>. " : "";
        $disable_plugins = (strlen($disable_plugins) > 0 && $feature == 'nested_material') ? " $disable_plugins: Upgrade to <b>Advance Plan to enable</b>." : "";
        return $enable_plugins . "<br>" . $disable_plugins;
    }

    add_filter('freightquote_en_woo_plans_nested_notification_message_action', 'freightquote_en_woo_plans_nested_notification_message', 10, 3);
}

add_action('admin_enqueue_scripts', 'freightquote_en_script');

/**
 * Load Front-end scripts for fedex
 */
function freightquote_en_script()
{
    wp_enqueue_script('jquery');
    wp_enqueue_script('freightquote_en_script', plugin_dir_url(__FILE__) . 'js/en-freightquote.js', array(), '1.1.2');
    wp_localize_script('freightquote_en_script', 'en_freight_quote_admin_script', array(
        'plugins_url' => plugins_url(),
        'allow_proceed_checkout_eniture' => trim(get_option("allow_proceed_checkout_eniture")),
        'prevent_proceed_checkout_eniture' => trim(get_option("prevent_proceed_checkout_eniture")),
        'wc_settings_freightquote_rate_method' => get_option("wc_settings_freightquote_rate_method"),
        // Cuttoff Time
        'freightquote_freight_order_cutoff_time' => get_option("freightquote_freight_order_cut_off_time"),
    ));
}

/**
 * Get Domain Name
 */
if (!function_exists('freightquote_quotes_get_domain')) {

    function freightquote_quotes_get_domain()
    {
        global $wp;
        $url = home_url($wp->request);
        return freightquote_getHost($url);
    }

}

/**
 * Load Css And Js Scripts
 */
function freightquote_ltl_admin_script()
{
    // Cuttoff Time
    wp_register_style('freightquote_wickedpicker_style', plugin_dir_url(__FILE__) . 'css/wickedpicker.min.css', false, '1.0.1');
    wp_register_script('freightquote_wickedpicker_script', plugin_dir_url(__FILE__) . 'js/wickedpicker.js', false, '1.0.1');
    wp_enqueue_style('freightquote_wickedpicker_style');

    wp_enqueue_script('freightquote_wickedpicker_script');
    wp_register_style('freightquote_ltl_style', plugin_dir_url(__FILE__) . '/css/freightquote_ltl_style.css', array(), '1.1.8', 'screen');
    wp_enqueue_style('freightquote_ltl_style');
}

/**
 * Add Plugin Actions
 */
add_filter('plugin_action_links', 'freightquote_ltl_add_action_plugin', 10, 5);

/**
 * Plugin Action
 * @staticvar $plugin
 * @param $actions
 * @param $plugin_file
 * @return array
 */
function freightquote_ltl_add_action_plugin($actions, $plugin_file)
{

    static $plugin;
    if (!isset($plugin))
        $plugin = plugin_basename(__FILE__);
    if ($plugin == $plugin_file) {

        $settings = array('settings' => '<a href="admin.php?page=wc-settings&tab=freightquote_quests">' . __('Settings', 'eniture-freightquote-ltl') . '</a>');
        $site_link = array('support' => '<a href="https://support.eniture.com/" target="_blank">Support</a>');
        $actions = array_merge($settings, $actions);
        $actions = array_merge($site_link, $actions);
    }

    return $actions;
}

/**
 * Inlude Plugin Files
 */
require_once('warehouse-dropship/wild-delivery.php');
require_once('standard-package-addon/standard-package-addon.php');
require_once('warehouse-dropship/get-distance-request.php');
// Origin terminal address
add_action('admin_init', 'freightquote_update_warehouse');
add_action('admin_init', 'freightquote_carriers_db');


require_once('product/en-product-detail.php');

require_once 'fdo/en-fdo.php';
require_once 'order/en-order-export.php';
require_once 'order/en-order-widget.php';
require_once 'template/products-nested-options.php';
require_once 'template/csv-export.php';

require_once('update-plan.php');
require_once 'freigtquote-liftgate-as-option.php';

register_activation_hook(__FILE__, 'freightquote_en_ltl_activate_hit_to_update_plan');
// multisite
register_activation_hook(__FILE__, 'freightquote_carriers_db');
register_deactivation_hook(__FILE__, 'freightquote_en_ltl_deactivate_hit_to_update_plan');
register_activation_hook(__FILE__, 'freightquote_create_ltl_freight_class');
register_deactivation_hook(__FILE__, 'freightquote_en_deactivate_plugin');

require_once(__DIR__ . '/freightquote-ltl-filter-quotes.php');
require_once 'feightquote-carrier-service.php';
require_once 'freightquote-version-compact.php';
require_once('freightquote-test-connection.php');
require_once 'freightquote-ltl-shipping-class.php';
require_once 'db/freightquote_db.php';
require_once 'freightquote-group-package.php';
require_once 'freightquote-admin-filter.php';
include_once(ABSPATH . 'wp-admin/includes/plugin.php');
require_once 'wc_update_change.php';
require_once 'freightquote-curl-class.php';


/**
 * FreightQuote LTL Action And Filters
 */
if (!is_plugin_active('woocommerce/woocommerce.php')) {
    add_action('admin_notices', 'freightquote_ltl_woocommrec_avaibility_error');
} else {
    add_filter('woocommerce_get_settings_pages', 'freightquote_ltl_shipping_sections');
}

add_action('admin_init', 'freightquote_ltl_check_woo_version');
add_action('woocommerce_shipping_init', 'freightquote_ltl_shipping_method_init');
add_filter('woocommerce_shipping_methods', 'freightquote_ltl_add_LTL_shipping_method');
add_filter('woocommerce_package_rates', 'freightquote_ltl_hide_shipping_based_on_class');
add_action('init', 'freightquote_ltl_save_carrier_status');
add_filter('woocommerce_cart_shipping_method_full_label', 'freightquote_ltl_remove_free_label', 10, 2);

/**
 * FreightQuote LTL Activation Hook
 */
register_activation_hook(__FILE__, 'freightquote_create_ltl_wh_db');


$arr = array();
apply_filters('product_detail_freight_class', $arr);

define("freightquote_en_woo_plugin_freightquote_quests", "freightquote_quests");

add_action('wp_enqueue_scripts', 'freightquote_en_ltl_frontend_checkout_script');

/**
 * Load Frontend scripts for FreightQuote
 */
function freightquote_en_ltl_frontend_checkout_script()
{
    wp_enqueue_script('jquery');
    wp_enqueue_script('freightquote_en_ltl_frontend_checkout_script', plugin_dir_url(__FILE__) . 'front/js/en-freightquote-checkout.js', array(), '1.0.1');
    wp_localize_script('freightquote_en_ltl_frontend_checkout_script', 'frontend_script', array(
        'pluginsUrl' => plugins_url(),
    ));
}

add_filter('freightquote_quests_quotes_plans_suscription_and_features', 'freightquote_quests_quotes_plans_suscription_and_features', 1);

function freightquote_quests_quotes_plans_suscription_and_features($feature)
{
    $package = get_option('freightquote_ltl_packages_quotes_package');

    $features = array
    (
        'instore_pickup_local_devlivery' => array('3'),
        'hazardous_material' => array('2', '3'),
        'multi_warehouse' => array('2', '3'),
        'multi_dropship' => array('', '0', '1', '2', '3'),
        'nested_material' => array('3'),
        // Cuttoff Time
        'freightquote_cutt_off_time' => array('2', '3'),
    );

    return (isset($features[$feature]) && (in_array($package, $features[$feature]))) ? TRUE : ((isset($features[$feature])) ? $features[$feature] : '');
}

add_filter('freightquote_quests_plans_notification_link', 'freightquote_quests_plans_notification_link', 1);

function freightquote_quests_plans_notification_link($plans)
{
    $plan = current($plans);
    $plan_to_upgrade = "";
    switch ($plan) {
        case 2:
            $plan_to_upgrade = "<a target='_blank' class='plan_color' href='https://eniture.com/woocommerce-freightquote-ltl-freight/'>Standard Plan required</a>";
            break;
        case 3:
            $plan_to_upgrade = "<a target='_blank' href='https://eniture.com/woocommerce-freightquote-ltl-freight/'>Advanced Plan required</a>";
            break;
    }

    return $plan_to_upgrade;
}
// fdo va
add_action('wp_ajax_nopriv_freightquote_fd', 'freightquote_fd_api');
add_action('wp_ajax_freightquote_fd', 'freightquote_fd_api');
/**
 * UPS AJAX Request
 */
function freightquote_fd_api()
{
    $store_name = freightquote_quotes_get_domain();
    $company_id = isset($_POST['company_id']) ? sanitize_text_field( wp_unslash($_POST['company_id'])): '';
    $data = [
        'plateform'  => 'wp',
        'store_name' => $store_name,
        'company_id' => $company_id,
        'fd_section' => 'tab=freightquote_quests&section=section-5',
    ];
    if (is_array($data) && count($data) > 0) {
        if(isset($_POST['disconnect']) && $_POST['disconnect'] != 'disconnect') {
            $url =  'https://freightdesk.online/validate-company';
        }else {
            $url = 'https://freightdesk.online/disconnect-woo-connection';
        }
        $response = wp_remote_post($url, [
                'method' => 'POST',
                'timeout' => 60,
                'redirection' => 5,
                'blocking' => true,
                'body' => $data,
            ]
        );
        $response = wp_remote_retrieve_body($response);
    }
    if(isset($_POST['disconnect']) && $_POST['disconnect'] == 'disconnect') {
        $result = json_decode($response);
        if ($result->status == 'SUCCESS') {
            update_option('en_fdo_company_id_status', 0);
        }
    }
    echo wp_json_encode(json_decode($response));
    exit();
}
add_action('rest_api_init', 'freightquote_en_rest_api_init_status');
function freightquote_en_rest_api_init_status()
{
    register_rest_route('fdo-company-id', '/update-status', array(
        'methods' => 'POST',
        'callback' => 'freightquote_en_fdo_data_status',
        'permission_callback' => '__return_true'
    ));
}

/**
 * Update FDO coupon data
 * @param array|object $request
 * @return array|void
 */
function freightquote_en_fdo_data_status(WP_REST_Request $request)
{
    $status_data = $request->get_body();
    $status_data_decoded = json_decode($status_data);
    if (isset($status_data_decoded->connection_status)) {
        update_option('en_fdo_company_id_status', $status_data_decoded->connection_status);
        update_option('en_fdo_company_id', $status_data_decoded->fdo_company_id);
    }
    return true;
}

add_filter('en_suppress_parcel_rates_hook', 'freightquote_supress_parcel_rates');
if (!function_exists('freightquote_supress_parcel_rates')) {
    function freightquote_supress_parcel_rates() {
        $exceedWeight = get_option('en_plugins_return_LTL_quotes') == 'yes';
        $supress_parcel_rates = get_option('en_suppress_parcel_rates') == 'suppress_parcel_rates';
        return ($exceedWeight && $supress_parcel_rates);
    }
}

/**
 * Remove Option For freightquote
 */
function freightquote_en_deactivate_plugin($network_wide = null)
{
    if ( is_multisite() && $network_wide ) {
        foreach (get_sites(['fields'=>'ids']) as $blog_id) {
            switch_to_blog($blog_id);
            $eniture_plugins = get_option('EN_Plugins');
            $plugins_array = json_decode($eniture_plugins, true);
            $plugins_array = !empty($plugins_array) && is_array($plugins_array) ? $plugins_array : array();
            $key = array_search('freightquote_ltl_shipping_method', $plugins_array);
            if ($key !== false) {
                unset($plugins_array[$key]);
            }
            update_option('EN_Plugins', wp_json_encode($plugins_array));
            restore_current_blog();
        }
    } else {
        $eniture_plugins = get_option('EN_Plugins');
        $plugins_array = json_decode($eniture_plugins, true);
        $plugins_array = !empty($plugins_array) && is_array($plugins_array) ? $plugins_array : array();
        $key = array_search('freightquote_ltl_shipping_method', $plugins_array);
        if ($key !== false) {
            unset($plugins_array[$key]);
        }
        update_option('EN_Plugins', wp_json_encode($plugins_array));
    }
}