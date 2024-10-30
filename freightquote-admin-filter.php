<?php
/**
 * FreightQuote LTL Admin Filters
 *
 * @package     FreightQuote LTL
 * @author      Eniture-Technology
 */
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Check WooCommerce Exist
 */
function freightquote_ltl_woocommrec_avaibility_error()
{
    echo "<div class=\"error\"> <p>WooCommerce LTL Freight is enabled, but not effective. It requires WooCommerce in order to work , Please <a target='_blank' href='https://wordpress.org/plugins/woocommerce/installation/'>Install</a> WooCommerce Plugin. Reactivate WooCommerce LTL Freight plugin to create LTL shipping class.</p></div>";
}

/**
 * Add Tab For Speedfreight In Woo Settings
 * @param $settings
 */
function freightquote_ltl_shipping_sections($settings)
{
    include('freightquote-tab-class.php');
    return $settings;
}

/**
 * Check WooCommerce Version And Throw Error If Less Than 2.6
 */
function freightquote_ltl_check_woo_version()
{

    $wcPluginVersion = new Freightquote_ltl_shipping_get_quotes();
    $woo_version = $wcPluginVersion->freightquote_ltl_get_woo_version_number();

    $version = '2.6';
    if (!version_compare($woo_version["woocommerce_plugin_version"], $version, ">=")) {

        add_action('admin_notices', 'freightquote_ltl_admin_notice_failure');
    }
}

/**
 * Admin Notices
 */
function freightquote_ltl_admin_notice_failure()
{
    ?>
    <div class="notice notice-error">
        <p><?php __('FreightQuote LTL plugin requires WooCommerce version 2.6 or higher to work. Functionality may not work properly.', 'eniture-freightquote-ltl'); ?></p>
    </div>
    <?php
}

/**
 * Hide Shipping Methods If Not From Eniture
 * @param $available_methods
 */
function freightquote_ltl_hide_shipping_based_on_class($available_methods)
{
    if (get_option('wc_settings_freightquote_allow_other_plugins') == 'no'
        && count($available_methods) > 0) {
        $plugins_array = array();
        $eniture_plugins = get_option('EN_Plugins');
        if ($eniture_plugins) {
            $plugins_array = json_decode($eniture_plugins, true);
        }

        // flag to check if rates available of current plugin
        $rates_available = false;
        foreach ($available_methods as $value) {
            if ($value->method_id == 'freightquote_ltl_shipping_method') {
                $rates_available = true;
                break;
            }
        }

        // add methods which not exist in array
        $plugins_array[] = 'ltl_shipping_method';
        $plugins_array[] = 'daylight';
        $plugins_array[] = 'tql';
        $plugins_array[] = 'unishepper_small';
        $plugins_array[] = 'usps';

        if ($rates_available) {
            foreach ($available_methods as $index => $method) {
                if (!in_array($method->method_id, $plugins_array)) {
                    unset($available_methods[$index]);
                }
            }
        }
    }
    return $available_methods;
}

/**
 * Save FreightQuote LTL Freight Carriers
 * @param  $post_id
 */
function freightquote_ltl_save_carrier_status($post_id)
{

    if (isset($_POST['action']) && sanitize_text_field( wp_unslash($_POST['action'])) == 'freight_quote_save_carrier_status') {

        global $wpdb;
        $all_freight_array = array();
        $count_carrier = 1;

        $api_selected = get_option('freightquote_api_endpoint');
        $fq_tl_carriers_scac_arr = ['ABHB', 'REEF', 'TSM'];
        $chr_tl_carriers_scac_arr = ['Van', 'Flatbed', 'Reefer'];
        $table_name = FREIGHTQUOTE_FREIGHT_CARRIERS;
        $ltl_freight_get = $wpdb->get_results("SELECT * FROM $table_name ORDER BY freightQuote_carrierName ASC");

        foreach ($ltl_freight_get as $ltl_freight_get_value):

            if (isset($_POST[$ltl_freight_get_value->freightQuote_carrierSCAC . $ltl_freight_get_value->id]) && $_POST[$ltl_freight_get_value->freightQuote_carrierSCAC . $ltl_freight_get_value->id] == 'on') {

                $wpdb->query($wpdb->prepare(
                    "UPDATE $table_name SET carrier_status = %s WHERE freightQuote_carrierSCAC = %s",
                    '1',
                    $ltl_freight_get_value->freightQuote_carrierSCAC
                ));
            } else {

                if((in_array($ltl_freight_get_value->freightQuote_carrierSCAC, $fq_tl_carriers_scac_arr) && $api_selected == 'freightquote_new_api') 
                            || (in_array($ltl_freight_get_value->freightQuote_carrierSCAC, $chr_tl_carriers_scac_arr) && $api_selected == 'freightquote_old_api')){
                                continue;
                }else{
                    $wpdb->query($wpdb->prepare(
                        "UPDATE $table_name SET carrier_status = %s WHERE freightQuote_carrierSCAC = %s",
                        '0',
                        $ltl_freight_get_value->freightQuote_carrierSCAC
                    ));
                }
            }
        endforeach;
    }
}

/**
 * Add FreightQuote LTL Shipping Method
 * @param $methods
 */
function freightquote_ltl_add_LTL_shipping_method($methods)
{
    $methods['freightquote_ltl_shipping_method'] = 'Freightquote_WC_Shipping_Method';
    return $methods;
}

/**
 * Remove Label If Free Shipping
 * @param $full_label
 * @param $method
 * @return string
 */
function freightquote_ltl_remove_free_label($full_label, $method)
{
    return str_replace("(Free)", "", $full_label);
}

/**
 * Create LTL Class
 */
function freightquote_create_ltl_freight_class($network_wide = null)
{
    if ( is_multisite() && $network_wide ) {

        foreach (get_sites(['fields'=>'ids']) as $blog_id) {
            switch_to_blog($blog_id);
            if (!function_exists('create_ltl_class')) {
                wp_insert_term(
                    'LTL Freight', 'product_shipping_class', array(
                        'description' => 'The plugin is triggered to provide an LTL freight quote when the shopping cart contains an item that has a designated shipping class. ShippingCtrl class? is a standard WooCommerce parameter not to be confused with freight class? or the NMFC classification system.',
                        'slug' => 'ltl_freight'
                    )
                );
            }
            restore_current_blog();
        }

    } else {
        if (!function_exists('create_ltl_class')) {
            wp_insert_term(
                'LTL Freight', 'product_shipping_class', array(
                    'description' => 'The plugin is triggered to provide an LTL freight quote when the shopping cart contains an item that has a designated shipping class. ShippingCtrl class? is a standard WooCommerce parameter not to be confused with freight class? or the NMFC classification system.',
                    'slug' => 'ltl_freight'
                )
            );
        }
    }
}

/**
 * Filter For CSV Import
 */
if (!function_exists('freightquote_ltl_en_import_dropship_location_csv')) {

    /**
     * Import drop ship location CSV
     * @param $data
     * @param $this
     * @return array
     */
    function freightquote_ltl_en_import_dropship_location_csv($data, $parseData)
    {
        $_product_freight_class = $_product_freight_class_variation = '';
        $_dropship_location = $locations = [];
        foreach ($data['meta_data'] as $key => $metaData) {
            $location = explode(',', trim($metaData['value']));
            switch ($metaData['key']) {
                // Update new columns
                case '_product_freight_class':
                    $_product_freight_class = trim($metaData['value']);
                    unset($data['meta_data'][$key]);
                    break;
                case '_product_freight_class_variation':
                    $_product_freight_class_variation = trim($metaData['value']);
                    unset($data['meta_data'][$key]);
                    break;
                case '_dropship_location_nickname':
                    $locations[0] = $location;
                    unset($data['meta_data'][$key]);
                    break;
                case '_dropship_location_zip_code':
                    $locations[1] = $location;
                    unset($data['meta_data'][$key]);
                    break;
                case '_dropship_location_city':
                    $locations[2] = $location;
                    unset($data['meta_data'][$key]);
                    break;
                case '_dropship_location_state':
                    $locations[3] = $location;
                    unset($data['meta_data'][$key]);
                    break;
                case '_dropship_location_country':
                    $locations[4] = $location;
                    unset($data['meta_data'][$key]);
                    break;
                case '_dropship_location':
                    $_dropship_location = $location;
            }
        }

        // Update new columns
        if (strlen($_product_freight_class) > 0) {
            $data['meta_data'][] = [
                'key' => '_ltl_freight',
                'value' => $_product_freight_class,
            ];
        }

        // Update new columns
        if (strlen($_product_freight_class_variation) > 0) {
            $data['meta_data'][] = [
                'key' => '_ltl_freight_variation',
                'value' => $_product_freight_class_variation,
            ];
        }

        if (!empty($locations) || !empty($_dropship_location)) {
            if (isset($locations[0]) && is_array($locations[0])) {
                foreach ($locations[0] as $key => $location_arr) {
                    $metaValue = [];
                    if (isset($locations[0][$key], $locations[1][$key], $locations[2][$key], $locations[3][$key])) {
                        $metaValue[0] = $locations[0][$key];
                        $metaValue[1] = $locations[1][$key];
                        $metaValue[2] = $locations[2][$key];
                        $metaValue[3] = $locations[3][$key];
                        $metaValue[4] = $locations[4][$key];
                        $dsId[] = freightquote_ltl_en_serialize_dropship($metaValue);
                    }
                }
            } else {
                $dsId[] = freightquote_ltl_en_serialize_dropship($_dropship_location);
            }

            $sereializedLocations = maybe_serialize($dsId);
            $data['meta_data'][] = [
                'key' => '_dropship_location',
                'value' => $sereializedLocations,
            ];
        }
        return $data;
    }

    add_filter('woocommerce_product_importer_parsed_data', 'freightquote_ltl_en_import_dropship_location_csv', '99', '2');
}

/**
 * Serialize drop ship
 * @param $metaValue
 * @return string
 * @global $wpdb
 */

if (!function_exists('freightquote_ltl_en_serialize_dropship')) {
    function freightquote_ltl_en_serialize_dropship($metaValue)
    {
        global $wpdb;
        $dropship = (array)reset($wpdb->get_results(
            $wpdb->prepare(
                "SELECT id
                    FROM " . $wpdb->prefix . "warehouse
                    WHERE nickname = %s AND zip = %s AND city = %s AND state = %s AND country = %s",
                $metaValue[0], $metaValue[1], $metaValue[2], $metaValue[3], $metaValue[4]
            )
        ));

        $dropship = array_map('intval', $dropship);

        if (empty($dropship['id'])) {
            $data = freightquote_ltl_en_csv_import_dropship_data($metaValue);
            $wpdb->insert(
                $wpdb->prefix . 'warehouse', $data
            );

            $dsId = $wpdb->insert_id;
        } else {
            $dsId = $dropship['id'];
        }

        return $dsId;
    }
}

/**
 * Filtered Data Array
 * @param $metaValue
 * @return array
 */
if (!function_exists('freightquote_ltl_en_csv_import_dropship_data')) {
    function freightquote_ltl_en_csv_import_dropship_data($metaValue)
    {
        return array(
            'city' => $metaValue[2],
            'state' => $metaValue[3],
            'zip' => $metaValue[1],
            'country' => $metaValue[4],
            'location' => 'dropship',
            'nickname' => (isset($metaValue[0])) ? $metaValue[0] : "",
        );
    }
}