<?php

/**
 * FreightQuote ltl Update Plan
 */

if (!defined('ABSPATH')) {
    exit;
}

add_action('wp_ajax_freightquote_en_ltl_activate_hit_to_update_plan', 'freightquote_en_ltl_activate_hit_to_update_plan');
add_action('wp_ajax_nopriv_freightquote_en_ltl_activate_hit_to_update_plan', 'freightquote_en_ltl_activate_hit_to_update_plan');

/**
 * Activate FreightQuote LTL
 */
function freightquote_en_ltl_activate_hit_to_update_plan($network_wide = null)
{
    if ( is_multisite() && $network_wide ) {

        foreach (get_sites(['fields'=>'ids']) as $blog_id) {
            switch_to_blog($blog_id);
            $domain = freightquote_quotes_get_domain();

            $index = 'ltl-freight-quotes-freightquote-edition/ltl-freight-quotes-freightquote-edition.php';
            $plugin_info = get_plugins();
            $plugin_version = $plugin_info[$index]['Version'];

            $post_data = array(
                'platform' => 'wordpress',
                'carrier' => '61',
                'store_url' => $domain,
                'webhook_url' => '',
                'plugin_version' => $plugin_version,
            );

            $license_key = get_option('wc_settings_freightquote_license_key');
            strlen($license_key) > 0 ? $post_data['license_key'] = $license_key : '';

            $url = FREIGHTQUOTE_DOMAIN_HITTING_URL . "/web-hooks/subscription-plans/create-plugin-webhook.php?";
            $response = wp_remote_get($url,
                array(
                    'method' => 'GET',
                    'timeout' => 60,
                    'redirection' => 5,
                    'blocking' => true,
                    'body' => $post_data,
                )
            );
            $output = wp_remote_retrieve_body($response);
            $response = json_decode($output, TRUE);
            $plan = isset($response['pakg_group']) ? $response['pakg_group'] : '';
            $expire_day = isset($response['pakg_duration']) ? $response['pakg_duration'] : '';
            $expiry_date = isset($response['expiry_date']) ? $response['expiry_date'] : '';
            $plan_type = isset($response['plan_type']) ? $response['plan_type'] : '';
            if (isset($response['pakg_price']) && $response['pakg_price'] == '0') {
                $plan = '0';
            }
            update_option('freightquote_ltl_packages_quotes_package', $plan);
            update_option('freightquote_ltl_package_expire_days', $expire_day);
            update_option('freightquote_ltl_package_expire_date', $expiry_date);
            update_option('freightquote_quests_store_type', $plan_type);

            if(false === get_option('en_freight_quote_truckload_weight_threshold')){
                add_option('en_freight_quote_truckload_weight_threshold', 7000);
            }
    
            if(false === get_option('en_freight_quote_truckload_weight_threshold_chr')){
                add_option('en_freight_quote_truckload_weight_threshold_chr', 7500);
            }
            en_check_freightquote_ltl_plan_on_product_detail();
            restore_current_blog();
        }

    } else {
        $domain = freightquote_quotes_get_domain();

        $index = 'ltl-freight-quotes-freightquote-edition/ltl-freight-quotes-freightquote-edition.php';
        $plugin_info = get_plugins();
        $plugin_version = $plugin_info[$index]['Version'];

        $post_data = array(
            'platform' => 'wordpress',
            'carrier' => '61',
            'store_url' => $domain,
            'webhook_url' => '',
            'plugin_version' => $plugin_version,
        );

        $license_key = get_option('wc_settings_freightquote_license_key');
        strlen($license_key) > 0 ? $post_data['license_key'] = $license_key : '';
        $url = FREIGHTQUOTE_DOMAIN_HITTING_URL . "/web-hooks/subscription-plans/create-plugin-webhook.php?";
        $response = wp_remote_get($url,
            array(
                'method' => 'GET',
                'timeout' => 60,
                'redirection' => 5,
                'blocking' => true,
                'body' => $post_data,
            )
        );
        $output = wp_remote_retrieve_body($response);
        $response = json_decode($output, TRUE);
        $plan = isset($response['pakg_group']) ? $response['pakg_group'] : '';
        $expire_day = isset($response['pakg_duration']) ? $response['pakg_duration'] : '';
        $expiry_date = isset($response['expiry_date']) ? $response['expiry_date'] : '';
        $plan_type = isset($response['plan_type']) ? $response['plan_type'] : '';
        if (isset($response['pakg_price']) && $response['pakg_price'] == '0') {
            $plan = '0';
        }
        update_option('freightquote_ltl_packages_quotes_package', $plan);
        update_option('freightquote_ltl_package_expire_days', $expire_day);
        update_option('freightquote_ltl_package_expire_date', $expiry_date);
        update_option('freightquote_quests_store_type', $plan_type);

        if(false === get_option('en_freight_quote_truckload_weight_threshold')){
            add_option('en_freight_quote_truckload_weight_threshold', 7000);
        }

        if(false === get_option('en_freight_quote_truckload_weight_threshold_chr')){
            add_option('en_freight_quote_truckload_weight_threshold_chr', 7500);
        }

        en_check_freightquote_ltl_plan_on_product_detail();
    }

}

/**
 * Product detail Features
 */
function en_check_freightquote_ltl_plan_on_product_detail()
{

    $hazardous_feature_PD = 0;
    $dropship_feature_PD = 1;
    $nested_material_PD = 0;
    //  Nested material
    $nested_mateials = apply_filters('freightquote_quests_quotes_plans_suscription_and_features', 'nested_material');
    if (!is_array($nested_mateials)) {
        $nested_material_PD = 1;
    }


//  Hazardous Material
    $hazardous_material = apply_filters('freightquote_quests_quotes_plans_suscription_and_features', 'hazardous_material');
    if (!is_array($hazardous_material)) {
        $hazardous_feature_PD = 1;
    }

//  Dropship 
    if (get_option('freightquote_quests_store_type') == "1") {
        $action_dropship = apply_filters('freightquote_quests_quotes_plans_suscription_and_features', 'multi_dropship');
        if (!is_array($action_dropship)) {
            $dropship_feature_PD = 1;
        } else {
            $dropship_feature_PD = 0;
        }
    }
    update_option('eniture_plugin_20', array('freightquote_ltl_packages_quotes_package' => array('plugin_name' => 'LTL FreigthQuote - Edition', 'multi_dropship' => $dropship_feature_PD, 'hazardous_material' => $hazardous_feature_PD, 'nested_material' => $nested_material_PD)));
}

/**
 * Deactivate FreightQuote LTL
 */
function freightquote_en_ltl_deactivate_hit_to_update_plan($network_wide = null)
{
    if ( is_multisite() && $network_wide ) {

        foreach (get_sites(['fields'=>'ids']) as $blog_id) {
            switch_to_blog($blog_id);
            delete_option('eniture_plugin_20');
            delete_option('freightquote_ltl_packages_quotes_package');
            delete_option('freightquote_ltl_package_expire_days');
            delete_option('freightquote_ltl_package_expire_date');
            delete_option('freightquote_quests_store_type');
            restore_current_blog();
        }

    } else {
        delete_option('eniture_plugin_20');
        delete_option('freightquote_ltl_packages_quotes_package');
        delete_option('freightquote_ltl_package_expire_days');
        delete_option('freightquote_ltl_package_expire_date');
        delete_option('freightquote_quests_store_type');
    }

}

/**
 * Get FreightQuote ltl Plan
 * @return string
 */
function en_freightquote_ltl_plan_name()
{
    $plan = get_option('freightquote_ltl_packages_quotes_package');
    $expire_days = get_option('freightquote_ltl_package_expire_days');
    $expiry_date = get_option('freightquote_ltl_package_expire_date');
    $plan_name = "";

    switch ($plan) {
        case 3:
            $plan_name = "Advanced Plan";
            break;
        case 2:
            $plan_name = "Standard Plan";
            break;
        case 1:
            $plan_name = "Basic Plan";
            break;
        case 0:
            $plan_name = "Trial Plan";
            break;
    }

    $package_array = array(
        'plan_number' => $plan,
        'plan_name' => $plan_name,
        'expire_days' => $expire_days,
        'expiry_date' => $expiry_date
    );
    return $package_array;
}

/**
 * Show FreightQuote ltl Plan Notice
 * @return string
 */
function en_freightquote_ltl_plan_notice()
{
    if (isset($_GET['tab']) && $_GET['tab'] == "freightquote_quests") {
        $plan_package = en_freightquote_ltl_plan_name();
        $plan_number = get_option('freightquote_ltl_packages_quotes_package');
        $store_type = get_option('freightquote_quests_store_type');

        if ($store_type == "1" || $store_type == "0" && ($plan_number == "0" || $plan_number == "1" || $plan_number == "2" || $plan_number == "3")) {

            if ($plan_package['plan_number'] == '0') {

                echo '<div class="notice notice-success is-dismissible">
                <p> You are currently on the ' . esc_attr($plan_package['plan_name']) . '. Your plan will be expire on ' . esc_attr($plan_package['expiry_date']) . ' <a href="javascript:void(0)" data-action="freightquote_en_ltl_activate_hit_to_update_plan" onclick="en_update_plan(this);">Click here</a> to refresh the plan.</p>
                </div>';
            } else if ($plan_package['plan_number'] == '1' || $plan_package['plan_number'] == '2' || $plan_package['plan_number'] == '3') {

                echo '<div class="notice notice-success is-dismissible">
                <p> You are currently on ' . esc_attr($plan_package['plan_name']) . '. The plan renews on ' . esc_attr($plan_package['expiry_date']) . ' <a href="javascript:void(0)" data-action="freightquote_en_ltl_activate_hit_to_update_plan" onclick="en_update_plan(this);">Click here</a> to refresh the plan.</p>
                </div>';
            } else {
                echo '<div class="notice notice-warning is-dismissible">
                <p>Your currently plan subscription is inactive. <a href="javascript:void(0)" data-action="freightquote_en_ltl_activate_hit_to_update_plan" onclick="en_update_plan(this);">Click here</a> to refresh the plan to check the subscription status. If the subscription status remains inactive, log into eniture.com and update your license.</p>
                </div>';
            }
        }
    }
}

add_action('admin_notices', 'en_freightquote_ltl_plan_notice');
