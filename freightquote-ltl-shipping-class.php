<?php

/**
 * Freightquote LTL Shipping Class
 *
 * @package     FreightQuote LTL
 * @author      Eniture-Technology
 */
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Freightquote LTL Shipping Method Init
 */
function freightquote_ltl_shipping_method_init()
{

    if (!class_exists('Freightquote_WC_Shipping_Method')) {

        /**
         * Class Freightquote_WC_Shipping_Method
         */
        class Freightquote_WC_Shipping_Method extends WC_Shipping_Method
        {

            public $forceAllowShipMethodFreightQuote = array();
            public $getPkgObjFreightQuote;
            public $FreightQuote_Ltl_Liftgate_As_Option;
            public $ltl_res_inst;
            public $quote_settings;
            public $instore_pickup_and_local_delivery;
            public $InstorPickupLocalDelivery;
            public $group_small_shipments;
            public $web_service_inst;
            public $package_plugin;
            public $woocommerce_package_rates;
            public $shipment_type;
            private $changObj;
            public $minPrices;
            // FDO
            public $en_fdo_meta_data = [];
            public $en_fdo_meta_data_third_party = [];


            /**
             * smpkgFoundErr
             * @var array type
             */
            public $smpkgFoundErr = array();

            /**
             * Constructor
             * @param $instance_id
             */
            public function __construct($instance_id = 0)
            {
                $this->changObj = new Freightquote_Woo_Update_Changes();
                $this->id = 'freightquote_ltl_shipping_method';
                $this->instance_id = absint($instance_id);
                $this->method_title = __('Freightquote', 'eniture-freightquote-ltl');
                $this->method_description = __('Real-time LTL Freight quotes - Freightquote Edition', 'eniture-freightquote-ltl');
                $this->supports = array(
                    'shipping-zones',
                    'instance-settings',
                    'instance-settings-modal',
                );
                $this->enabled = "yes";
                $this->title = "LTL Freight Quotes - Freightquote Edition";
                $this->init();

                $this->FreightQuote_Ltl_Liftgate_As_Option = new Freightquote_Ltl_Liftgate_As_Option();
            }

            /**
             * Initialization
             */
            function init()
            {

                $this->init_form_fields();
                $this->init_settings();
                add_action('woocommerce_update_options_shipping_' . $this->id, array($this, 'process_admin_options'));
            }

            /**
             * Form Fields
             */
            public function init_form_fields()
            {

                $this->instance_form_fields = array(
                    'enabled' => array(
                        'title' => __('Enable / Disable', 'eniture-freightquote-ltl'),
                        'type' => 'checkbox',
                        'label' => __('Enable This Shipping Service', 'eniture-freightquote-ltl'),
                        'default' => 'yes',
                        'id' => 'speed_freight_enable_disable_shipping'
                    )
                );
            }

            /**
             * Virtual Products
             */
            public function en_virtual_products()
            {
                global $woocommerce;
                $products = $woocommerce->cart->get_cart();
                $items = $product_name = [];
                foreach ($products as $key => $product_obj) {
                    $product = $product_obj['data'];
                    $is_virtual = $product->get_virtual();

                    if ($is_virtual == 'yes') {
                        $attributes = $product->get_attributes();
                        $product_qty = $product_obj['quantity'];
                        $product_title = str_replace(array("'", '"'), '', $product->get_title());
                        $product_name[] = $product_qty . " x " . $product_title;

                        $meta_data = [];
                        if (!empty($attributes)) {
                            foreach ($attributes as $attr_key => $attr_value) {
                                $meta_data[] = [
                                    'key' => $attr_key,
                                    'value' => $attr_value,
                                ];
                            }
                        }

                        $items[] = [
                            'id' => $product_obj['product_id'],
                            'name' => $product_title,
                            'quantity' => $product_qty,
                            'price' => $product->get_price(),
                            'weight' => 0,
                            'length' => 0,
                            'width' => 0,
                            'height' => 0,
                            'type' => 'virtual',
                            'product' => 'virtual',
                            'sku' => $product->get_sku(),
                            'attributes' => $attributes,
                            'variant_id' => 0,
                            'meta_data' => $meta_data,
                        ];
                    }
                }

                $virtual_rate = [];

                if (!empty($items)) {
                    $virtual_rate = [
                        'id' => 'en_virtual_rate',
                        'label' => 'Virtual Quote',
                        'cost' => 0,
                    ];

                    $virtual_fdo = [
                        'plugin_type' => 'ltl',
                        'plugin_name' => 'wwe_quests',
                        'accessorials' => '',
                        'items' => $items,
                        'address' => '',
                        'handling_unit_details' => '',
                        'rate' => $virtual_rate,
                    ];

                    $meta_data = [
                        'sender_origin' => 'Virtual Product',
                        'product_name' => wp_json_encode($product_name),
                        'en_fdo_meta_data' => $virtual_fdo,
                    ];

                    $virtual_rate['meta_data'] = $meta_data;

                }

                return $virtual_rate;
            }

            /**
             * Third party quotes
             * @param type $forceShowMethods
             * @return type
             */
            public function force_allow_ship_method_freightquote($forceShowMethods)
            {
                if (!empty($this->getPkgObjFreightQuote->ValidShipmentsArr) && (!in_array("ltl_freight", $this->getPkgObjFreightQuote->ValidShipmentsArr))) {
                    $this->forceAllowShipMethodFreightQuote[] = "free_shipping";
                    $this->forceAllowShipMethodFreightQuote[] = "valid_third_party";
                } else {

                    $this->forceAllowShipMethodFreightQuote[] = "ltl_shipment";
                }

                $forceShowMethods = array_merge($forceShowMethods, $this->forceAllowShipMethodFreightQuote);
                return $forceShowMethods;
            }

            /**
             * Calculate Shipping
             * @param $package
             * @global $current_user
             * @global $wpdb
             */
            public function calculate_shipping($package = [], $eniture_admin_order_action = false)
            {
                if (is_admin() && !wp_doing_ajax() && !$eniture_admin_order_action) {
                    return [];
                }

                $this->package_plugin = get_option('freightquote_ltl_packages_quotes_package');

                $this->instore_pickup_and_local_delivery = FALSE;

//                  Eniture debug mood
                do_action("eniture_error_messages", "Errors");

                $freight_zipcode = (strlen(WC()->customer->get_shipping_postcode()) > 0) ? WC()->customer->get_shipping_postcode() : $this->changObj->freightquote_postcode();

                $coupn = WC()->cart->get_coupons();
                if (isset($coupn) && !empty($coupn)) {
                    $freeShipping = $this->freight_quote_ltl_free_shipping($coupn);
                    if ($freeShipping == 'y')
                        return [];
                }

                $this->create_speedfreight_ltl_option();
                global $wpdb;
                global $current_user;
                $sandbox = "";
                $quotes = array();
                $smallQuotes = array();
                $rate = array();
                $own_freight = array();

                $smallPackages = false;

                $allowArrangements = get_option('wc_settings_freightquote_allow_for_own_arrangment');
                $ltl_res_inst = new Freightquote_ltl_shipping_get_quotes();
                $this->ltl_res_inst = $ltl_res_inst;
                $this->web_service_inst = $ltl_res_inst;

                $this->ltl_shipping_quote_settings();

//                  Eniture debug mood
                do_action("eniture_debug_mood", "FreightQuote Quote Settings", $this->ltl_res_inst->quote_settings);

                if (isset($this->ltl_res_inst->quote_settings['handling_fee']) &&
                    ($this->ltl_res_inst->quote_settings['handling_fee'] == "-100%")) {
                        $rates = array(
                            'id' => $this->id . ':' . 'free',
                            'label' => 'Free Shipping',
                            'cost' => 0,
                            'plugin_name' => 'b2b',
                            'plugin_type' => 'ltl',
                            'owned_by' => 'eniture'
                        );
                        $this->add_rate($rates);

                        return [];
                }

                $freightquote_group_ltl_shipments = new Freightquote_Group_Ltl_Shipments();
                $this->getPkgObjFreightQuote = $freightquote_group_ltl_shipments;

                $ltl_package = $freightquote_group_ltl_shipments->ltl_package_shipments($package, $ltl_res_inst, $freight_zipcode);

                add_filter('force_show_methods', array($this, 'force_allow_ship_method_freightquote'));

                $no_param_multi_ship = 0;

                if (isset($ltl_package) && is_array($ltl_package) && count($ltl_package) > 1) {
                    foreach ($ltl_package as $key => $value) {
                        if (isset($value["NOPARAM"]) && $value["NOPARAM"] === 1 && empty($value["items"])) {
                            $no_param_multi_ship = 1;
                            unset($ltl_package[$key]);
                        }
                    }
                }

                $eniturePluigns = json_decode(get_option('EN_Plugins'));
                $calledMethod = array();
                $smallPluginExist = 0;

                if (!empty($ltl_package)) {
                    $ltl_products = $small_products = [];
                    foreach ($ltl_package as $key => $sPackage) {
                        if (array_key_exists('ltl', $sPackage)) {
                            $ltl_products[] = $sPackage;
                            $web_service_arr = $ltl_res_inst->ltl_shipping_get_web_service_array($sPackage, $this->package_plugin);
                            $response = $ltl_res_inst->ltl_shipping_get_web_freightquote_quotes($web_service_arr, $this->ltl_res_inst->quote_settings);
                            if (empty($response)) {
                                return [];
                            }
                            
                            (!empty($response)) ? $quotes[$key] = $response : "";
                            continue;
                        } elseif (array_key_exists('small', $sPackage)) {
                            $sPackage['is_shipment'] = 'small';
                            $small_products[] = $sPackage;
                        }
                    }
                }

                if (isset($small_products) && !empty($small_products) && !empty($ltl_products)) {
                    foreach ($eniturePluigns as $enIndex => $enPlugin) {
                        $freightSmallClassName = 'WC_' . $enPlugin;
                        if (!in_array($freightSmallClassName, $calledMethod)) {
                            if (class_exists($freightSmallClassName)) {
                                $smallPluginExist = 1;
                                $SmallClassNameObj = new $freightSmallClassName();
                                $package['itemType'] = 'ltl';
                                $package['sPackage'] = $small_products;
                                $smallQuotesResponse = $SmallClassNameObj->calculate_shipping($package, true);
                                $smallQuotes[] = $smallQuotesResponse;
                            }
                            $calledMethod[] = $freightSmallClassName;
                        }
                    }
                }


                $smallQuotes = (is_array($smallQuotes) && (!empty($smallQuotes))) ? reset($smallQuotes) : $smallQuotes;
                $smallMinRate = (is_array($smallQuotes) && (!empty($smallQuotes))) ? current($smallQuotes) : $smallQuotes;
                // Virtual products
                $virtual_rate = $this->en_virtual_products();

                // FDO
                if (isset($smallMinRate['meta_data']['en_fdo_meta_data'])) {
                    if (!empty($smallMinRate['meta_data']['en_fdo_meta_data']) && !is_array($smallMinRate['meta_data']['en_fdo_meta_data'])) {
                        $en_third_party_fdo_meta_data = json_decode($smallMinRate['meta_data']['en_fdo_meta_data'], true);
                        isset($en_third_party_fdo_meta_data['data']) ? $smallMinRate['meta_data']['en_fdo_meta_data'] = $en_third_party_fdo_meta_data['data'] : '';
                    }
                    $this->en_fdo_meta_data_third_party = (isset($smallMinRate['meta_data']['en_fdo_meta_data']['address'])) ? [$smallMinRate['meta_data']['en_fdo_meta_data']] : $smallMinRate['meta_data']['en_fdo_meta_data'];
                }

                $smpkgCost = (isset($smallMinRate['cost'])) ? $smallMinRate['cost'] : 0;


                if (isset($smallMinRate) && (!empty($smallMinRate))) {
                    switch (TRUE) {
                        case (isset($smallMinRate['minPrices'])):
                            $small_quotes = $smallMinRate['minPrices'];
                            break;
                        default :
                            $shipment_zipcode = key($smallQuotes);
                            $small_quotes = array($shipment_zipcode => $smallMinRate);
                            break;
                    }
                }

                if (isset($quotes) && (empty($quotes))) {
                    return FALSE;
                }

                $this->minPrices = array();

                $this->quote_settings = $this->ltl_res_inst->quote_settings;
                $this->quote_settings = json_decode(wp_json_encode($this->quote_settings), true);
                $quotes = json_decode(wp_json_encode($quotes), true);

                $liftgateExcluded = ($ltl_res_inst->liftgateExcluded) ?? false;

                $handling_fee = $this->quote_settings['handling_fee'];

                $Ltl_Freights_Quotes = new Freightquote_Ltl_Freights_Quotes();
                
                // Virtual products
                if (((is_array($quotes) && count($quotes) > 1) || $smpkgCost > 0) || $no_param_multi_ship == 1 || !empty($virtual_rate)) {
                    $multi_cost = 0;
                    $s_multi_cost = 0;
                    $tl_multi_cost = 0;
                    $_label = "";
                    $TL_included = false;

                    $this->quote_settings['shipment'] = "multi_shipment";

                    (isset($small_quotes) && count($small_quotes) > 0) ? $this->minPrices['LIFT'] = $small_quotes : "";
                    (isset($small_quotes) && count($small_quotes) > 0) ? $this->minPrices['NOTLIFT'] = $small_quotes : "";
                    // Virtual products
                    if (!empty($virtual_rate)) {
                        $en_virtual_fdo_meta_data[] = $virtual_rate['meta_data']['en_fdo_meta_data'];
                        $virtual_meta_rate['virtual_rate'] = $virtual_rate;
                        $this->minPrices['LIFT'] = isset($this->minPrices['LIFT']) && !empty($this->minPrices['LIFT']) ? array_merge($this->minPrices['LIFT'], $virtual_meta_rate) : $virtual_meta_rate;
                        $this->minPrices['NOTLIFT'] = isset($this->minPrices['NOTLIFT']) && !empty($this->minPrices['NOTLIFT']) ? array_merge($this->minPrices['NOTLIFT'], $virtual_meta_rate) : $virtual_meta_rate;

                        $this->en_fdo_meta_data_third_party = !empty($this->en_fdo_meta_data_third_party) ? array_merge($this->en_fdo_meta_data_third_party, $en_virtual_fdo_meta_data) : $en_virtual_fdo_meta_data;
                    }

                    $lt_involed = false;
                    foreach ($quotes as $key => $quote) {
                        $key = "LTL_" . $key;

                        $simple_quotes = (isset($quote['simple_quotes'])) ? $quote['simple_quotes'] : array();
                        $truckload_logistics_services = (isset($quote['truckload_logistics_services'])) ? $quote['truckload_logistics_services'] : array();
                        
                        $quote = $this->remove_array($quote, 'simple_quotes');
                        $quote = $this->remove_array($quote, 'truckload_logistics_services');

                        if(($liftgateExcluded || $lt_involed) && !empty($simple_quotes)){
                            if($lt_involed && $s_multi_cost > 0){
                                $multi_cost = $s_multi_cost;
                                $s_multi_cost = 0;
                            }
                            $quote = $simple_quotes;
                            $simple_quotes = [];
                        }

                        $rates = $Ltl_Freights_Quotes->freightquote_calculate_quotes($quote, $this->ltl_res_inst->quote_settings, 'simple');
                        
                        if(!empty($truckload_logistics_services) && empty($simple_quotes)){
                            if(!empty(get_option('freightquote_api_endpoint')) && get_option('freightquote_api_endpoint') == 'freightquote_new_api'){
                                $truckload_rates = $truckload_logistics_services;
                            }else{
                                $truckload_rates = $Ltl_Freights_Quotes->freightquote_calculate_quotes($truckload_logistics_services, $this->ltl_res_inst->quote_settings, 'truckload');
                            }

                            if(empty($rates)){
                                $lt_involed = true;
                            }else{
                                $tl_rates = reset($truckload_rates);
                                $normal_rate = reset($rates);
                                if(is_numeric($tl_rates['cost']) && $tl_rates['cost'] > 0 && is_numeric($normal_rate['cost']) && $normal_rate['cost'] > 0 
                                && $tl_rates['cost'] < $normal_rate['cost']){
                                    $lt_involed = true;
                                }
                            }
                            
                            (!empty($truckload_rates)) ? $rates = $this->sort_asec_order_arr(array_merge($rates, $truckload_rates), 'cost') : '';
                            
                        }

                        $rates = reset($rates);

                        $this->minPrices['LIFT'][$key] = $rates;

                        // FDO
                        $this->en_fdo_meta_data['LIFT'][$key] = (isset($rates['meta_data']['en_fdo_meta_data'])) ? $rates['meta_data']['en_fdo_meta_data'] : [];

                        $_cost = (isset($rates['cost'])) ? $rates['cost'] : 0;
                        $_label = (isset($rates['label_sufex'])) ? $rates['label_sufex'] : "";
                        $append_label = (isset($rates['append_label'])) ? $rates['append_label'] : "";
                        $handling_fee = (isset($rates['markup']) && (strlen($rates['markup']) > 0)) ? $rates['markup'] : $handling_fee;
                        $ship_cost = $this->add_handling_fee($_cost, $handling_fee);
                        $this->en_fdo_meta_data['LIFT'][$key]['rate']['cost'] = $ship_cost;
                        $multi_cost += $ship_cost;

//                          Offer lift gate delivery as an option is enabled
                        if (isset($this->quote_settings['liftgate_delivery_option']) &&
                            ($this->quote_settings['liftgate_delivery_option'] == "yes") &&
                            (!empty($simple_quotes))) {
                                $s_ship_cost = 0;
                            $s_rates = $Ltl_Freights_Quotes->freightquote_calculate_quotes($simple_quotes, $this->ltl_res_inst->quote_settings, 'liftgate');
                            // (!empty($simple_truckload_logistics_services)) ? $s_rates = $this->sort_asec_order_arr(array_merge($s_rates, $simple_truckload_logistics_services), 'cost') : '';

                            $s_rates = reset($s_rates);
                            $this->minPrices['NOTLIFT'][$key] = $s_rates;

                            // FDO
                            $this->en_fdo_meta_data['NOTLIFT'][$key] = (isset($s_rates['meta_data']['en_fdo_meta_data'])) ? $s_rates['meta_data']['en_fdo_meta_data'] : [];

                            $s_cost = (isset($s_rates['cost'])) ? $s_rates['cost'] : 0;
                            $s_label = (isset($s_rates['label_sufex'])) ? $s_rates['label_sufex'] : "";
                            $s_append_label = (isset($s_rates['append_label'])) ? $s_rates['append_label'] : "";
                            $s_ship_cost = $this->add_handling_fee($s_cost, $handling_fee);
                            if (!empty($truckload_logistics_services)) {
                                $tl_cost = 0;
                                if(!empty(get_option('freightquote_api_endpoint')) && get_option('freightquote_api_endpoint') == 'freightquote_new_api'){
                                    $truckload_rates = $truckload_logistics_services;
                                }else{
                                    $truckload_rates = $Ltl_Freights_Quotes->freightquote_calculate_quotes($truckload_logistics_services, $this->ltl_res_inst->quote_settings, 'truckload');
                                }
    
                                $truckload_rates = reset($truckload_rates);
                                $tl_cost = (isset($truckload_rates['cost'])) ? $truckload_rates['cost'] : 0;
                                $tl_cost = $this->add_handling_fee($tl_cost, $handling_fee);
                                if($tl_cost> 0 && $s_ship_cost> 0 && $tl_cost < $s_ship_cost){
                                    $this->minPrices['NOTLIFT'][$key] = $tl_cost;
                                    $this->en_fdo_meta_data['NOTLIFT'][$key] = (isset($tl_cost['meta_data']['en_fdo_meta_data'])) ? $tl_cost['meta_data']['en_fdo_meta_data'] : [];
                                    $s_label = (isset($tl_cost['label_sufex'])) ? $tl_cost['label_sufex'] : "";
                                    $s_append_label = (isset($tl_cost['append_label'])) ? $tl_cost['append_label'] : "";
                                    $s_multi_cost += $tl_cost;
                                    $TL_included = true;
                                }
                            }else{
                                $s_multi_cost += $s_ship_cost;
                            }
                            
                            $this->en_fdo_meta_data['NOTLIFT'][$key]['rate']['cost'] = $s_ship_cost;
                        }
                        
                    }

                    ($s_multi_cost > 0) ? $rate[] = $this->arrange_multiship_freight(($s_multi_cost + $smpkgCost), 'NOTLIFT', $s_label, $s_append_label) : "";
                    ($multi_cost > 0 && !$TL_included) ? $rate[] = $this->arrange_multiship_freight(($multi_cost + $smpkgCost), 'LIFT', $_label, $append_label) : "";
                    
                    $this->shipment_type = 'multiple';
                    $rates = $this->freightquote_ltl_add_rate_arr($rate);
                    
                } else {
                    
                    // Dispaly Local and In-store PickUp Delivery 
                    $this->InstorPickupLocalDelivery = $ltl_res_inst->freightquote_ltl_return_local_delivery_store_pickup();

                    $quote = reset($quotes);
                    
                    $simple_quotes = (isset($quote['simple_quotes'])) ? $quote['simple_quotes'] : array();
                    $truckload_logistics_services = (isset($quote['truckload_logistics_services'])) ? $quote['truckload_logistics_services'] : array();
                    
                    $quote = $this->remove_array($quote, 'simple_quotes');
                    $quote = $this->remove_array($quote, 'truckload_logistics_services');

                    $rates = $Ltl_Freights_Quotes->freightquote_calculate_quotes($quote, $this->ltl_res_inst->quote_settings, 'simple');
                    if(!empty(get_option('freightquote_api_endpoint')) && get_option('freightquote_api_endpoint') == 'freightquote_new_api'){
                        $truckload_rates = $truckload_logistics_services;
                    }else{
                        $truckload_rates = $Ltl_Freights_Quotes->freightquote_calculate_quotes($truckload_logistics_services, $this->ltl_res_inst->quote_settings, 'truckload');
                    }
                    
                    (!empty($truckload_rates)) ? $rates = array_merge($rates, $truckload_rates) : '';

                    // Offer lift gate delivery as an option is enabled
                    if (isset($this->quote_settings['liftgate_delivery_option']) &&
                        ($this->quote_settings['liftgate_delivery_option'] == "yes") &&
                        !empty($simple_quotes)) {
                        $simple_rates = $Ltl_Freights_Quotes->freightquote_calculate_quotes($simple_quotes, $this->ltl_res_inst->quote_settings, 'liftgate');
                        $rates = array_merge($rates, $simple_rates);
                    }

                    $cost_sorted_key = array();

                    $this->quote_settings['shipment'] = "single_shipment";

                    foreach ($rates as $key => $quote) {
                        $handling_fee = (isset($rates['markup']) && (strlen($rates['markup']) > 0)) ? $rates['markup'] : $handling_fee;
                        $_cost = (isset($quote['cost'])) ? $quote['cost'] : 0;
                        $rates[$key]['cost'] = $this->add_handling_fee($_cost, $handling_fee);
                        (isset($rates[$key]['meta_data']['en_fdo_meta_data']['rate']['cost'])) ? $rates[$key]['meta_data']['en_fdo_meta_data']['rate']['cost'] = $this->add_handling_fee($_cost, $handling_fee) : "";
                        $cost_sorted_key[$key] = (isset($quote['cost'])) ? $quote['cost'] : 0;
                        $rates[$key]['shipment'] = "single_shipment";
                    }

//                       array multisort 
                    array_multisort($cost_sorted_key, SORT_ASC, $rates);

                    /**
                     * call local-delivery and instore-pickup function to show the data on shipping page
                     */
                    (isset($this->ltl_res_inst->InstorPickupLocalDelivery->localDelivery) && ($this->ltl_res_inst->InstorPickupLocalDelivery->localDelivery->status == 1)) ? $this->local_delivery($this->ltl_res_inst->en_wd_origin_array['fee_local_delivery'], $this->ltl_res_inst->en_wd_origin_array['checkout_desc_local_delivery'], $this->ltl_res_inst->en_wd_origin_array) : "";
                    (isset($this->ltl_res_inst->InstorPickupLocalDelivery->inStorePickup) && ($this->ltl_res_inst->InstorPickupLocalDelivery->inStorePickup->status == 1)) ? $this->pickup_delivery($this->ltl_res_inst->en_wd_origin_array['checkout_desc_store_pickup'], $this->ltl_res_inst->en_wd_origin_array, $this->ltl_res_inst->InstorPickupLocalDelivery->totalDistance) : "";

                    $this->shipment_type = 'single';
                    $rates = $this->freightquote_ltl_add_rate_arr($rates);
                }
                
                return $rates;
            }

            /**
             * final rates sorting
             * @param array type $rates
             * @param array type $package
             * @return array type
             */
            function en_sort_woocommerce_available_shipping_methods($rates, $package)
            {
                if (!empty($rates)) {
                    //  if there are no rates don't do anything
                    if (!$rates) {
                        return;
                    }

                    // get an array of prices
                    $prices = array();
                    foreach ($rates as $rate) {
                        $prices[] = $rate->cost;
                    }

                    // use the prices to sort the rates
                    array_multisort($prices, $rates);
                }

                // return the rates
                return $rates;
            }

            /**
             * Pickup delivery quote
             * @return array type
             */
            function pickup_delivery($label, $en_wd_origin_array, $total_distance)
            {
                $this->woocommerce_package_rates = 1;
                $this->instore_pickup_and_local_delivery = TRUE;

                $label = (isset($label) && (strlen($label) > 0)) ? $label : 'In-store pick up';
                // Origin terminal address
                $address = (isset($en_wd_origin_array['address'])) ? $en_wd_origin_array['address'] : '';
                $city = (isset($en_wd_origin_array['city'])) ? $en_wd_origin_array['city'] : '';
                $state = (isset($en_wd_origin_array['state'])) ? $en_wd_origin_array['state'] : '';
                $zip = (isset($en_wd_origin_array['zip'])) ? $en_wd_origin_array['zip'] : '';
                $phone_instore = (isset($en_wd_origin_array['phone_instore'])) ? $en_wd_origin_array['phone_instore'] : '';
                strlen($total_distance) > 0 ? $label .= ': Free | ' . str_replace("mi", "miles", $total_distance) . ' away' : '';
                strlen($address) > 0 ? $label .= ' | ' . $address : '';
                strlen($city) > 0 ? $label .= ', ' . $city : '';
                strlen($state) > 0 ? $label .= ' ' . $state : '';
                strlen($zip) > 0 ? $label .= ' ' . $zip : '';
                strlen($phone_instore) > 0 ? $label .= ' | ' . $phone_instore : '';

                $pickup_delivery = array(
                    'id' => $this->id . ':' . 'in-store-pick-up',
                    'cost' => 0,
                    'label' => $label,
                    'plugin_name' => 'b2b',
                    'plugin_type' => 'ltl',
                    'owned_by' => 'eniture',
                );
                $pickup_delivery = array_merge($pickup_delivery, $this->addMetaData());

                add_filter('woocommerce_package_rates', array($this, 'en_sort_woocommerce_available_shipping_methods'), 10, 2);
                $this->add_rate($pickup_delivery);
            }

            /**
             * Local delivery quote
             * @param string type $cost
             * @return array type
             */
            function local_delivery($cost, $label, $en_wd_origin_array)
            {
                $this->woocommerce_package_rates = 1;
                $this->instore_pickup_and_local_delivery = TRUE;
                $label = (isset($label) && (strlen($label) > 0)) ? $label : 'Local Delivery';

                $local_delivery = array(
                    'id' => $this->id . ':' . 'local-delivery',
                    'cost' => $cost,
                    'label' => $label,
                    'plugin_name' => 'b2b',
                    'plugin_type' => 'ltl',
                    'owned_by' => 'eniture',
                );
                $local_delivery = array_merge($local_delivery, $this->addMetaData());

                add_filter('woocommerce_package_rates', array($this, 'en_sort_woocommerce_available_shipping_methods'), 10, 2);
                $this->add_rate($local_delivery);
            }

            /**
             * Remove array
             * @return array
             */
            function remove_array($quote, $remove_index)
            {
                unset($quote[$remove_index]);

                return $quote;
            }

            /**
             * Arrange Own Freight
             * @return array
             */
            function arrange_own_freight()
            {

                return array(
                    'id' => $this->id . ':' . 'own_freight',
                    'cost' => 0,
                    'label' => get_option('wc_settings_freightquote_text_for_own_arrangment'),
                    'calc_tax' => 'per_item',
                    'plugin_name' => 'b2b',
                    'plugin_type' => 'ltl',
                    'owned_by' => 'eniture',
                );
            }

            /**
             * Multishipment
             * @return array
             */
            function arrange_multiship_freight($cost, $id, $label_sufex, $append_label)
            {

                $multiship_arr = array(
                    'id' => $id,
                    'label' => "Freight",
                    'cost' => $cost,
                    'label_sufex' => $label_sufex,
                    'append_label' => $append_label,
                    'plugin_name' => 'b2b',
                    'plugin_type' => 'ltl',
                    'owned_by' => 'eniture',
                );
                $multiship_arr = array_merge($multiship_arr, $this->addMetaData());

                if($id == 'truckload'){
                    $multiship_arr['service_type'] = 'truckload';
                }

                return $multiship_arr;

            }

            /**
             *
             * @param string type $price
             * @param string type $handling_fee
             * @return float type
             */
            function add_handling_fee($price, $handling_fee)
            {
                $handelingFee = 0;
                if ($handling_fee != '' && $handling_fee != 0) {
                    if (strrchr($handling_fee, "%")) {

                        $prcnt = (float)$handling_fee;
                        $handelingFee = (float)$price / 100 * $prcnt;
                    } else {
                        $handelingFee = (float)$handling_fee;
                    }
                }

                $handelingFee = $this->smooth_round($handelingFee);
                $price = (float)$price + $handelingFee;
                return $price;
            }

            /**
             *
             * @param float type $val
             * @param int type $min
             * @param int type $max
             * @return float type
             */
            function smooth_round($val, $min = 2, $max = 4)
            {
                $result = round($val, $min);
                if ($result == 0 && $min < $max) {
                    return $this->smooth_round($val, ++$min, $max);
                } else {
                    return $result;
                }
            }

            /**
             * sort array
             * @param array type $rate
             * @return array type
             */
            public function sort_asec_order_arr($rate, $index)
            {
                $price_sorted_key = array();
                foreach ($rate as $key => $cost_carrier) {
                    $price_sorted_key[$key] = (isset($cost_carrier[$index])) ? $cost_carrier[$index] : 0;
                }
                array_multisort($price_sorted_key, SORT_ASC, $rate);

                return $rate;
            }

            /**
             * Label from quote settings tab
             * @return string type
             */
            public function freightquote_label_as()
            {
                return (strlen($this->quote_settings['freightquote_label']) > 0) ? $this->quote_settings['freightquote_label'] : "Freight";
            }

            /**
             * Append label in quote
             * @param array type $rate
             * @return string type
             */
            public function set_label_in_quote($rate)
            {
                $rate_label = "";
                $label_sufex = (isset($rate['label_sufex']) && is_array($rate['label_sufex'])) ? array_unique($rate['label_sufex']) : array();
                $rate_label = (!isset($rate['label']) ||
                    ($this->quote_settings['shipment'] == "single_shipment" &&
                        strlen($this->quote_settings['freightquote_label']) > 0)) ?
                    $this->freightquote_label_as() : $rate['label'];

                $carrier = (isset($rate['carrier'])) ? $rate['carrier'] : '';
                // $rate_label = $carrier != 'truckload_logistics_service' ? $rate_label : $rate['label'];
                $rate_label .= (isset($this->quote_settings['sandbox'])) ? ' (Sandbox) ' : '';
                $rate_label .= (isset($rate['append_label'])) ? $rate['append_label'] : "";
                $rad_status = true;
                $all_plugins = apply_filters('active_plugins', get_option('active_plugins'));
                if (stripos(implode($all_plugins), 'residential-address-detection.php') || is_plugin_active_for_network('residential-address-detection/residential-address-detection.php')) {
                    if(get_option('suspend_automatic_detection_of_residential_addresses') != 'yes') {
                        $rad_status = get_option('residential_delivery_options_disclosure_types_to') != 'not_show_r_checkout';
                    }
                }
                if (isset($label_sufex) && (!empty($label_sufex))) {
                    if (is_array($label_sufex) && count($label_sufex) == 1) {
                        (in_array('R', $label_sufex) && $rad_status == true) ? $rate_label .= " with residential delivery " : "";
                        (in_array('L', $label_sufex)) ? $rate_label .= " with liftgate delivery " : "";
                    } elseif (is_array($label_sufex) && count($label_sufex) == 2) {
                        (in_array('R', $label_sufex) && $rad_status == true) ? $rate_label .= " with residential delivery " : "";
                        (in_array('L', $label_sufex)) ? $rate_label .= (strlen($rate_label) > 0 && $rad_status == true) ? " and liftgate delivery " : " with liftgate delivery " : "";
                    }
                }

                $parse_id_arr = explode('_', $rate['id']);
                $tl_scac_arr = ['ABHB', 'REEF', 'TSM', 'Van', 'Flatbed', 'Reefer'];

                if($this->shipment_type == 'single' && in_array($parse_id_arr[0], $tl_scac_arr)){
                    $rate_label = $rate['label'];
                }

                $delivery_estimate_freightquote = isset($this->quote_settings['delivery_estimates']) ? $this->quote_settings['delivery_estimates'] : '';
                $shipment_type = isset($this->quote_settings['shipment']) && !empty($this->quote_settings['shipment']) ? $this->quote_settings['shipment'] : '';
                if (isset($this->quote_settings['delivery_estimates']) && !empty($this->quote_settings['delivery_estimates'])
                    && $this->quote_settings['delivery_estimates'] != 'dont_show_estimates' && $shipment_type != 'multi_shipment'
                    && $this->quote_settings['rating_method'] != 'average_rate') {
                    $d_time_stamp = isset($rate['meta_data']['en_fdo_meta_data']['rate']['delivery_time_stamp']) ? $rate['meta_data']['en_fdo_meta_data']['rate']['delivery_time_stamp'] : '';
                    $days_time = isset($rate['meta_data']['en_fdo_meta_data']['rate']['delivery_estimates']) ? $rate['meta_data']['en_fdo_meta_data']['rate']['delivery_estimates'] : '';
                    if ($this->quote_settings['delivery_estimates'] == 'delivery_date') {
                        isset($d_time_stamp) && is_string($d_time_stamp) && strlen($d_time_stamp) > 0 ? $rate_label .= ' (Expected delivery by ' . gmdate('m-d-Y', strtotime($d_time_stamp)) . ')' : '';
                    } else if ($delivery_estimate_freightquote == 'delivery_days') {
                        $correct_word = (isset($days_time) && $days_time == 1) ? 'is' : 'are';
                        isset($days_time) && is_string($days_time) && strlen($days_time) > 0 ? $rate_label .= ' (Intransit days: ' . $days_time . ')' : '';
                    }
                }

                if(isset($rate['id']) && $rate['id'] == 'en_avg_truckload'){
                    $rate_label = (empty(get_option('wc_settings_freightquote_label_as'))) ? 'Freight - Truckload Service' : get_option('wc_settings_freightquote_label_as') . ' - Truckload Service';
                }else if(isset($rate['service_type']) && $rate['service_type'] == 'truckload'){
                    $rate_label = 'Freight - Truckload Service';
                }

                return $rate_label;
            }

            /**
             * rates to add_rate woocommerce
             * @param array type $add_rate_arr
             */
            public function freightquote_ltl_add_rate_arr($add_rate_arr)
            {
                if (isset($add_rate_arr) && (!empty($add_rate_arr)) && (is_array($add_rate_arr))) {

                    // Images for FDO
                    $image_urls = apply_filters('en_fdo_image_urls_merge', []);

                    add_filter('woocommerce_package_rates', array($this, 'en_sort_woocommerce_available_shipping_methods'), 10, 2);
                    $instore_pickup_local_devlivery_action = apply_filters('freightquote_quests_quotes_plans_suscription_and_features', 'instore_pickup_local_devlivery');
                    foreach ($add_rate_arr as $key => $rate) {

                        if (isset($rate['cost']) && $rate['cost'] > 0) {

                            $rate['label'] = $this->set_label_in_quote($rate);

                            if (isset($rate['meta_data'])) {
                                $rate['meta_data']['label_sufex'] = (isset($rate['label_sufex'])) ? wp_json_encode($rate['label_sufex']) : array();
                            }

                            $rate['id'] = (isset($rate['id'])) ? $rate['id'] : '';
                            if (isset($this->minPrices[$rate['id']])) {
                                $rate['meta_data']['min_prices'] = wp_json_encode($this->minPrices[$rate['id']]);
                                $rate['meta_data']['en_fdo_meta_data']['data'] = array_values($this->en_fdo_meta_data[$rate['id']]);
                                (!empty($this->en_fdo_meta_data_third_party)) ? $rate['meta_data']['en_fdo_meta_data']['data'] = array_merge($rate['meta_data']['en_fdo_meta_data']['data'], $this->en_fdo_meta_data_third_party) : '';
                                $rate['meta_data']['en_fdo_meta_data']['shipment'] = 'multiple';
                                $rate['meta_data']['en_fdo_meta_data'] = wp_json_encode($rate['meta_data']['en_fdo_meta_data']);
                            } else {
                                $en_set_fdo_meta_data['data'] = isset($rate['meta_data']) ? [$rate['meta_data']['en_fdo_meta_data']] : '';
                                $en_set_fdo_meta_data['shipment'] = 'sinlge';

                                if(isset($en_set_fdo_meta_data['data'][0]['quote_settings']['rating_method']) && $en_set_fdo_meta_data['data'][0]['quote_settings']['rating_method'] == 'average_rate'){
                                    $en_set_fdo_meta_data['data'][0]['rate']['id'] = $rate['id'];
                                    $en_set_fdo_meta_data['data'][0]['rate']['cost'] = $rate['cost'];
                                    $en_set_fdo_meta_data['data'][0]['rate']['label'] = $rate['label'];
                                }

                                // if($en_set_fdo_meta_data['data'])
                                $rate['meta_data']['en_fdo_meta_data'] = wp_json_encode($en_set_fdo_meta_data);

                            }

                            // Images for FDO
                            $rate['meta_data']['en_fdo_image_urls'] = wp_json_encode($image_urls);
                            $rate['id'] = isset($rate['id']) && is_string($rate['id']) ? $this->id . ':' . $rate['id'] : '';

                            if (isset($this->web_service_inst->en_wd_origin_array['suppress_local_delivery']) && $this->web_service_inst->en_wd_origin_array['suppress_local_delivery'] == "1" && (!is_array($instore_pickup_local_devlivery_action)) && ($this->shipment_type != 'multiple')) {

                                $rate = apply_filters('suppress_local_delivery', $rate, $this->web_service_inst->en_wd_origin_array, $this->package_plugin, $this->InstorPickupLocalDelivery);

                                if (!empty($rate)) {
                                    $this->add_rate($rate);
                                    $add_rate_arr[$key] = $rate;
                                    $this->woocommerce_package_rates = 1;
                                }
                            } else {
//                               Custom client work 
                                if (has_filter('add_duplicate_array') &&
                                    (isset($rate['shipment'])) && ($rate['shipment'] == "single_shipment")) {
                                    $quote = apply_filters('add_duplicate_array', $rate);
                                    foreach ($quote as $value) {
                                        $this->add_rate($value);
                                    }
                                } else {
                                    $this->add_rate($rate);
                                    $add_rate_arr[$key] = $rate;
                                }
                            }
                        }
                    }

                    (isset($this->quote_settings['own_freight']) && ($this->quote_settings['own_freight'] == "yes")) ? $this->add_rate($this->arrange_own_freight()) : "";
                }

                return $add_rate_arr;
            }

            /**
             * quote settings array
             * @global $wpdb $wpdb
             */
            function ltl_shipping_quote_settings()
            {
                global $wpdb;
                $table_name = FREIGHTQUOTE_FREIGHT_CARRIERS;
                $enable_carriers = $wpdb->get_results("SELECT `freightQuote_carrierSCAC` FROM $table_name WHERE carrier_status ='1'");
                $enable_carriers = json_decode(wp_json_encode($enable_carriers), TRUE);
                $rating_method = get_option('wc_settings_freightquote_rate_method');
                $freightquote_label = get_option('wc_settings_freightquote_label_as');
                $VersionCompat = new Freightquote_VersionCompat();
                $enable_carriers = $VersionCompat->enArrayColumn($enable_carriers, 'freightQuote_carrierSCAC');

                $this->ltl_res_inst->quote_settings['own_freight'] = get_option('wc_settings_freightquote_allow_for_own_arrangment');
                $this->ltl_res_inst->quote_settings['total_carriers'] = get_option('wc_settings_freightquote_Number_of_options');
                $this->ltl_res_inst->quote_settings['rating_method'] = (isset($rating_method) && (strlen($rating_method)) > 0) ? $rating_method : "Cheapest";
                $this->ltl_res_inst->quote_settings['freightquote_label'] = ($rating_method == "average_rate" || $rating_method == "Cheapest") ? $freightquote_label : "";
                $this->ltl_res_inst->quote_settings['handling_fee'] = get_option('wc_settings_freightquote_hand_free_mark_up');
                $this->ltl_res_inst->quote_settings['enable_carriers'] = $enable_carriers;
                $this->ltl_res_inst->quote_settings['liftgate_pickup'] = get_option('wc_settings_freightquote_lift_gate_pickup');
                $this->ltl_res_inst->quote_settings['liftgate_delivery'] = get_option('wc_settings_freightquote_lift_gate_delivery');
                $this->ltl_res_inst->quote_settings['liftgate_delivery_option'] = get_option('freightquote_quests_liftgate_delivery_as_option');
                $this->ltl_res_inst->quote_settings['residential_delivery'] = get_option('wc_settings_freightquote_residential_delivery');
                $this->ltl_res_inst->quote_settings['liftgate_resid_delivery'] = get_option('en_woo_addons_liftgate_with_auto_residential');
                // Cuttoff Time
                $this->web_service_inst->quote_settings['delivery_estimates'] = get_option('freightquote_delivery_estimates');
                $this->web_service_inst->quote_settings['orderCutoffTime'] = get_option('freightquote_freight_order_cut_off_time');
                $this->web_service_inst->quote_settings['shipmentOffsetDays'] = get_option('freightquote_freight_shipment_offset_days');
                // Handling Unit
                $this->web_service_inst->quote_settings['handling_weight'] = get_option('freight_quote_settings_handling_weight');
                $this->web_service_inst->quote_settings['maximum_handling_weight'] = get_option('freight_quote_maximum_handling_weight');
            }

            /**
             * Create plugin option
             */
            function create_speedfreight_ltl_option()
            {
                $eniture_plugins = get_option('EN_Plugins');
                if (!$eniture_plugins) {
                    add_option('EN_Plugins', wp_json_encode(array('freightquote_ltl_shipping_method')));
                } else {
                    $plugins_array = json_decode($eniture_plugins, true);
                    if (!in_array('freightquote_ltl_shipping_method', $plugins_array)) {
                        array_push($plugins_array, 'freightquote_ltl_shipping_method');
                        update_option('EN_Plugins', wp_json_encode($plugins_array));
                    }
                }
            }

            /**
             * Check is free shipping or not
             * @param $coupon
             * @return string
             */
            function freight_quote_ltl_free_shipping($coupon)
            {
                foreach ($coupon as $key => $value) {
                    if ($value->get_free_shipping() == 1) {
                        $free = array(
                            'id' => $this->id . ':' . 'free',
                            'label' => 'Free Shipping',
                            'cost' => 0,
                            'plugin_name' => 'b2b',
                            'plugin_type' => 'ltl',
                            'owned_by' => 'eniture',
                        );
                        $free = array_merge($free, $this->addMetaData());
                        $this->add_rate($free);
                        return 'y';
                    }
                }
                return 'n';
            }

            /** 
             * Add meta data 
            */
            function addMetaData()
            {
                return [
                    'plugin_name' => 'b2b',
                    'plugin_type' => 'ltl',
                    'owned_by' => 'eniture',
                ];
            }
        }

    }
}
