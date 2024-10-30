<?php
/**
 * FreightQuote LTL Woo Changes
 * 
 * @package     FreightQuote LTL
 * @author      Eniture-Technology
 */
if (!defined('ABSPATH')) {
    exit;
}
/**
 * Class Freightquote_Woo_Update_Changes
 */
    class Freightquote_Woo_Update_Changes {
        /** $WooVersion */ public $WooVersion;
        /**
         * Constructor
         */
        function __construct() {
            if (!function_exists('get_plugins'))
                require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
                $plugin_folder = get_plugins('/' . 'woocommerce');
                $plugin_file = 'woocommerce.php';
                $this->WooVersion = $plugin_folder[$plugin_file]['Version'];
        }
        /**
         * Get Postcode
         * @return string
         */
        function freightquote_postcode(){
            $postcode = "";
            switch ($this->WooVersion) {
                case ($this->WooVersion <= '2.7'):
                    $postcode = WC()->customer->get_postcode();
                    break;
                case ($this->WooVersion >= '3.0'):
                    $postcode = WC()->customer->get_billing_postcode();
                    break;

                default:

                    break;
            }

            return $postcode;
        }
        /**
         * Get State
         * @return string
         */
        function freightquote_getState()
        { 
            $sState = "";
            switch ($this->WooVersion) 
            {  
                case ($this->WooVersion <= '2.7'):
                    $sState = WC()->customer->get_state();
                    break;
                
                case ($this->WooVersion >= '3.0'):
                    $sState = WC()->customer->get_billing_state();
                    break;

                default:
                    break;
            }
            return $sState;
        }
        /**
         * Get City
         * @return string
         */
        function freightquote_getCity()
        { 
            $sCity = "";
            switch ($this->WooVersion) 
            {  
                case ($this->WooVersion <= '2.7'):
                    $sCity = WC()->customer->get_city();
                    break;
                
                case ($this->WooVersion >= '3.0'):
                    $sCity = WC()->customer->get_billing_city();
                    break;

                default:
                    break;
            }
            return $sCity;
        }
        
        /**
         * FreightQuote Country
         */
        function freightquote_getCountry()
        { 
            $sCountry = "";
            switch ($this->WooVersion) 
            {  
                case ($this->WooVersion <= '2.7'):
                    $sCountry = WC()->customer->get_country();
                    break;
                case ($this->WooVersion >= '3.0'):
                    $sCountry = WC()->customer->get_billing_country();
                    break;

                default:
                    break;
            }
            return $sCountry;
        }
}
