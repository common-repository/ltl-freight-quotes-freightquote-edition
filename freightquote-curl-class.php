<?php
/**
 * Freightquote LTL Curl Class
 * 
 * @package     Freightquote LTL
 * @author      Eniture-Technology
 */
if (!defined('ABSPATH')) {
    exit;
}
        
/**
 * Curl Response Class
 */ 
    class Freightquote_LTL_Curl_Request 
    {
        /**
         * Get Curl Response 
         * @param $url
         * @param $postData
         * @return json
         */
            function freightquote_ltl_get_curl_response($url, $postData) 
            {
                if ( !empty( $url ) && !empty( $postData ) )
                {
                    $field_string = http_build_query($postData);
                    $response = wp_remote_post($url,
                        array(
                            'method' => 'POST',
                            'timeout' => 60,
                            'redirection' => 5,
                            'blocking' => true,
                            'body' => $field_string,
                        )
                    );
                   
                    return $output = wp_remote_retrieve_body($response);
                    
                }    
            }
    }
