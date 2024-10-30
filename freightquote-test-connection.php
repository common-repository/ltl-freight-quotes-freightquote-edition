<?php

/**
 * Freightquote LTL Test connection
 *
 * @package     Freightquote LTL
 * @author      Eniture-Technology
 */
if (!defined('ABSPATH')) {
    exit;
}

add_action('wp_ajax_nopriv_freightquote_ltl_validate_keys', 'freightquote_ltl_submit');
add_action('wp_ajax_freightquote_ltl_validate_keys', 'freightquote_ltl_submit');

/**
 * Test connection Function
 */
function freightquote_ltl_submit()
{

    $sp_user = isset($_POST['freightquote_username']) ? sanitize_text_field( wp_unslash($_POST['freightquote_username'])) : '';
    $sp_pass = isset($_POST['freightquote_password']) ? sanitize_text_field( wp_unslash($_POST['freightquote_password'])) : '';
    $sp_licence_key = isset($_POST['freightquote_licence_key']) ? sanitize_text_field( wp_unslash($_POST['freightquote_licence_key'])) : '';
    // New API Endpoint
    $freightquote_customer_code = isset($_POST['freightquote_customer_code']) ? sanitize_text_field( wp_unslash($_POST['freightquote_customer_code'])) : '';
    $freightquote_api_endpoint = isset($_POST['freightquote_api_endpoint']) ? sanitize_text_field(wp_unslash($_POST['freightquote_api_endpoint'])) : '';

    switch ($freightquote_api_endpoint) {
        case 'freightquote_new_api':
            $post_data = [
                'b2bApiVersion' => '2.0',
                'customer_code' => $freightquote_customer_code,
            ];
            break;
        default:
            $post_data = [
                'name' => $sp_user,
                'password' => $sp_pass,
            ];
            break;
    }
    $domain = freightquote_quotes_get_domain();
    $post = array(
        'licence_key' => $sp_licence_key,
        'server_name' => freightquote_ltl_parse_url($domain),
        'platform' => 'WordPress',
        'carrier_mode' => 'test',
        'carrierName' => 'b2b',
    );
    $post = array_merge($post, $post_data);

    if (is_array($post) && count($post) > 0) {

        $ltl_curl_obj = new Freightquote_LTL_Curl_Request();
        $output = $ltl_curl_obj->freightquote_ltl_get_curl_response(FREIGHTQUOTE_DOMAIN_HITTING_URL . '/index.php', $post);
    }
    $sResponseData = json_decode($output);

    if (isset($sResponseData->severity) && $sResponseData->severity == 'SUCCESS') {
        $sResult = array('message' => "success");
    } elseif(isset($sResponseData->severity) && $sResponseData->severity == 'ERROR' 
    && is_string($sResponseData->Message) && strlen($sResponseData->Message) > 0){
        $sResult = array('message' => $sResponseData->Message);
    }elseif (isset($sResponseData->error) || $sResponseData->q->error == 1) {
        if (isset($sResponseData->error) && !empty($sResponseData->error)) {
            $sResult = $sResponseData->error;
        } else {
            $sResult = (count($sResponseData->q->error_desc->{0}) == 1) ? $sResponseData->q->error_desc->{0} : $sResponseData->q->error_desc;
        }

        $fullstop = (substr($sResult, -1) == '.') ? '' : '.';
        $sResult = array('message' => $sResult . $fullstop);
    } else {
        $sResult = array('message' => "failure");
    }
    print_r(wp_json_encode($sResult));
    die;
}

/**
 * URL parsing
 * @param $domain
 * @return url
 */
function freightquote_ltl_parse_url($domain)
{

    $domain = trim($domain);
    $parsed = wp_parse_url($domain);
    if (empty($parsed['scheme'])) {

        $domain = 'http://' . ltrim($domain, '/');
    }
    $parse = wp_parse_url($domain);
    $refinded_domain_name = $parse['host'];
    $domain_array = explode('.', $refinded_domain_name);
    if (in_array('www', $domain_array)) {

        $key = array_search('www', $domain_array);
        unset($domain_array[$key]);
        if(phpversion() < 8) {
            $refinded_domain_name = implode($domain_array, '.'); 
        }else {
            $refinded_domain_name = implode('.', $domain_array);
        }
    }
    
    return $refinded_domain_name;
}
