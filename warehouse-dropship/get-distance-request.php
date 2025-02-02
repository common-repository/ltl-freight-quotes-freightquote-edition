<?php
/**
 * FreightQuote LTL Distance Get
 *
 * @package     FreightQuote LTL
 * @author      Eniture-Technology
 */
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Freightquote_Get_ltl_distance
 */
class Freightquote_Get_ltl_distance
{
    /**
     * Get Distance Function
     * @param $map_address
     * @param $accessLevel
     * @return json | string
     */
    function freightquote_ltl_get_distance($map_address, $accessLevel, $destinationZip = array())
    {

        $domain = freightquote_quotes_get_domain();
        $post = array(
            'acessLevel' => $accessLevel,
            'address' => $map_address,
            'originAddresses' => (isset($map_address)) ? $map_address : "",
            'destinationAddress' => (isset($destinationZip)) ? $destinationZip : "",
            'eniureLicenceKey' => get_option('wc_settings_freightquote_license_key'),
            'ServerName' => $domain,
        );

        if (is_array($post) && count($post) > 0) {
            $ltl_curl_obj = new Freightquote_LTL_Curl_Request();
            $output = $ltl_curl_obj->freightquote_ltl_get_curl_response(FREIGHTQUOTE_DOMAIN_HITTING_URL . '/addon/google-location.php', $post);
            return $output;
        }
    }
}
