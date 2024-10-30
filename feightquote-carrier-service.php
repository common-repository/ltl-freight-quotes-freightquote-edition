<?php

/**
 * FreightQuote LTL Carrier Service
 *
 * @package     FreightQuote LTL Quotes
 * @author      Eniture-Technology
 */
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Freightquote_ltl_shipping_get_quotes
 */
class Freightquote_ltl_shipping_get_quotes extends Freightquote_Ltl_Liftgate_As_Option
{

    /**
     * $EndPointURL
     * @var string type
     */
    protected $EndPointURL = FREIGHTQUOTE_DOMAIN_HITTING_URL . '/index.php';
    public $en_wd_origin_array;
    public $InstorPickupLocalDelivery;
    public $liftgateExcluded = false;

    /**
     * details array
     * @var array type
     */
    public $quote_settings;
    private $freightquote_woo_obj;

    function __construct()
    {
        $this->quote_settings = array();
        $this->freightquote_woo_obj = new Freightquote_Woo_Update_Changes();
    }

    /**
     * Get Web Service Array
     * @param $packages
     * @return array
     */
    function ltl_shipping_get_web_service_array($packages, $package_plugin = "")
    {

        // FDO
        $EnFreightQuoteFdo = new Freightquote_EnFdo();
        $en_fdo_meta_data = array();
        $product_name = array();

        $destinationAddressFreightquote = $this->destination_address_freightquote();
        $freightquote_residential_delivery = 'N';

        if (get_option('wc_settings_freightquote_residential_delivery') == 'yes') {
            $freightquote_residential_delivery = 'Y';
        }

        $accesorails = array();
        (get_option('wc_settings_freightquote_lift_gate_delivery') == 'yes') ? $accesorails[] = 'LIFTGAT' : '';
        (get_option('wc_settings_freightquote_residential_delivery') == 'yes') ? $accesorails[] = 'RESDEL' : '';
        (get_option('wc_settings_freightquote_lift_gate_pickup') == 'yes') ? $accesorails[] = 'LIFTGATEPICKUP' : '';

        $freightquote_lift_gate_delivery = 'N';
        if (get_option('wc_settings_freightquote_lift_gate_delivery') == 'yes') {
            $freightquote_lift_gate_delivery = 'Y';
        }

        $freightClass_ltl_gross = "";

        $this->en_wd_origin_array = (isset($packages['origin'])) ? $packages['origin'] : array();
        // Cuttoff Time
        $shipment_week_days = "";
        $order_cut_off_time = "";
        $shipment_off_set_days = "";
        $modify_shipment_date_time = "";
        $store_date_time = "";
        $freightquote_delivery_estimates = get_option('freightquote_delivery_estimates');
        $shipment_week_days = $this->freightquote_shipment_week_days();
        if ($freightquote_delivery_estimates == 'delivery_days' || $freightquote_delivery_estimates == 'delivery_date') {
            $order_cut_off_time = $this->quote_settings['orderCutoffTime'];
            $shipment_off_set_days = $this->quote_settings['shipmentOffsetDays'];
            $modify_shipment_date_time = ($order_cut_off_time != '' || $shipment_off_set_days != '' || (is_array($shipment_week_days) && count($shipment_week_days) > 0)) ? 1 : 0;
            $store_date_time = $today = gmdate('Y-m-d H:i:s', current_time('timestamp'));
        }
        // check plan for nested material
        $nested_plan = apply_filters('freightquote_quests_quotes_plans_suscription_and_features', 'nested_material');

        $nestingPercentage = $nestedDimension = $nestedItems = $stakingProperty = [];
        $doNesting = false;
        $markup = [];
        $product_markup_shipment = 0;

        $lineItem = $product_name = array();
        foreach ($packages['items'] as $item) {
            // Standard Packaging
            $ship_as_own_pallet = isset($item['ship_as_own_pallet']) && $item['ship_as_own_pallet'] == 'yes' ? 1 : 0;
            $vertical_rotation_for_pallet = isset($item['vertical_rotation_for_pallet']) && $item['vertical_rotation_for_pallet'] == 'yes' ? 1 : 0;
            $freightquote_counter = (isset($item['variantId']) && $item['variantId'] > 0) ? $item['variantId'] : $item['productId'];
            $nmfc_num = (isset($item['nmfc_number'])) ? $item['nmfc_number'] : '';
            $get_markup = (isset($item['product_markup'])) ? $item['product_markup'] : [];
            $lineItem[$freightquote_counter] = array(
                'lineItemHeight' => $item['productHeight'],
                'lineItemLength' => $item['productLength'],
                'lineItemWidth' => $item['productWidth'],
                'lineItemClass' => $item['productClass'],
                'lineItemWeight' => $item['productWeight'],
                'piecesOfLineItem' => $item['productQty'],
                'lineItemNMFC' => $nmfc_num,
                'markup' => $get_markup,
                'lineItemPackageCode' => 'Bags',
                // Nested indexes
                'nestingPercentage' => $item['nestedPercentage'],
                'nestingDimension' => $item['nestedDimension'],
                'nestedLimit' => $item['nestedItems'],
                'nestedStackProperty' => $item['stakingProperty'],

                // Shippable handling units
                'lineItemPalletFlag' => $item['lineItemPalletFlag'],
                'lineItemPackageType' => $item['lineItemPackageType'],
                // Standard Packaging
                'shipPalletAlone' => $ship_as_own_pallet,
                'vertical_rotation' => $vertical_rotation_for_pallet
            );
            $lineItem[$freightquote_counter] = apply_filters('en_fdo_carrier_service', $lineItem[$freightquote_counter], $item);
            $product_name[] = $item['product_name'];
            $markup[] = $get_markup;
            //nesting start
            isset($item['nestedMaterial']) && !empty($item['nestedMaterial']) &&
            $item['nestedMaterial'] == 'yes' && !is_array($nested_plan) ? $doNesting = 1 : "";

            if (!empty($item['markup']) && is_numeric($item['markup'])) {
                $product_markup_shipment += $item['markup'];
            }
        }

        $aPluginVersions = $this->freightquote_ltl_get_woo_version_number();
        $domain = freightquote_quotes_get_domain();
        $receiverCountryCode = $this->freightquote_get_country_code($destinationAddressFreightquote['country']);
        $residential_detecion_flag = get_option("en_woo_addons_auto_residential_detecion_flag");

        $freightquote_api_endpoint = get_option('freightquote_api_endpoint');

        switch ($freightquote_api_endpoint) {
            case 'freightquote_new_api':
                $tl_equipment_type_arr = $this->get_tl_equipment_type_arr();
                $post_data_api_endpoint = [
                    'b2bApiVersion' => '2.0',
                    'customer_code' => get_option('freightquote_customer_code'),
                    'TLEquipmentTypeArray' => $tl_equipment_type_arr,
                    'TLWeightThreshold' => get_option('en_freight_quote_truckload_weight_threshold_chr'),
                ];
                break;
            default:
                $post_data_api_endpoint = [
                    'name' => get_option('wc_settings_freigtquote_freight_username'),
                    'password' => get_option('wc_settings_freigtquote_freight_password'),
                    'responseVersion' => '2',
                    'TLWeightThreshold' => get_option('en_freight_quote_truckload_weight_threshold'),
                ];
                break;
        }

        // FDO
        $en_fdo_meta_data = $EnFreightQuoteFdo->en_cart_package($packages);
        $post_data = array(
            'platform' => 'WordPress',
            'plugin_version' => $aPluginVersions["freightquote_ltl_plugin_version"],
            'woocommerce_version' => $aPluginVersions["woocommerce_plugin_version"],
            'wordpress_version' => get_bloginfo('version'),
            'server_name' => freightquote_ltl_parse_url($domain),
            'carrierName' => 'b2b',
            'carrier_mode' => 'pro',
            'licence_key' => get_option('wc_settings_freightquote_license_key'),
            'suspend_residential' => get_option('suspend_automatic_detection_of_residential_addresses'),
            'residential_detecion_flag' => $residential_detecion_flag,
            'addressLine' => get_option('woocommerce_store_address'),
            'receiverCity' => $destinationAddressFreightquote['city'],
            'receiverState' => $destinationAddressFreightquote['state'],
            'receiverZip' => $destinationAddressFreightquote['zip'],
            'receiverCountryCode' => ($receiverCountryCode == 'CAN') ? 'CA' : $receiverCountryCode,
            'senderCity' => (isset($packages['origin']['city'])) ? $packages['origin']['city'] : '',
            'senderState' => (isset($packages['origin']['state'])) ? $packages['origin']['state'] : '',
            'senderZip' => (isset($packages['origin']['zip'])) ? $packages['origin']['zip'] : '',
            'senderCountryCode' => (isset($packages['origin']['country'])) ? $packages['origin']['country'] : '',
            'accessorial' => $accesorails,
            'commdityDetails' => $lineItem,
            'handlingUnitWeight' => get_option('freight_quote_settings_handling_weight'),
            'maxWeightPerHandlingUnit' => get_option('freight_quote_maximum_handling_weight'),
            // FDO
            'en_fdo_meta_data' => $en_fdo_meta_data,
            'product_name' => $product_name,
            'sender_origin' => $packages['origin']['location'] . ": " . $packages['origin']['city'] . ", " . $packages['origin']['state'] . " " . $packages['origin']['zip'],
            //nesting
            'doNesting' => $doNesting,
            'markup' => $markup,
            // Cuttoff Time
            'modifyShipmentDateTime' => $modify_shipment_date_time,
            'OrderCutoffTime' => $order_cut_off_time,
            'shipmentOffsetDays' => $shipment_off_set_days,
            'storeDateTime' => $store_date_time,
            'shipmentWeekDays' => $shipment_week_days,
            // TL option
            'quoteLTLAboveThreshold' => (!empty(get_option('en_freight_quote_ltl_above_threshold')) && get_option('en_freight_quote_ltl_above_threshold') == 'yes') ? '1' : '0',
            // Origin and product level markup
            'origin_markup' => (isset($packages['origin']['origin_markup'])) ? $packages['origin']['origin_markup'] : 0,
            'product_level_markup' => $product_markup_shipment,
        );
        $post_data = array_merge($post_data, $post_data_api_endpoint);

        // Hazardous Material
        $hazardous_material = apply_filters('freightquote_quests_quotes_plans_suscription_and_features', 'hazardous_material');

        if (!is_array($hazardous_material)) {
            (isset($packages['hazardousMaterial']) && !empty($packages['hazardousMaterial']) && $packages['hazardousMaterial'] == '1') ? $post_data['hazmat'] = 'Y' : 'N';
            $post_data['HazardousMaterialContactName'] = 'test';
            $post_data['HazardousMaterialContactPhone'] = '4545464875';

            // FDO
            $post_data['en_fdo_meta_data'] = array_merge($post_data['en_fdo_meta_data'], $EnFreightQuoteFdo->en_package_hazardous($packages, $en_fdo_meta_data));
            (isset($packages['hazardousMaterial']) == 'yes') ? $post_data['hazardous'][] = 'H' : '';
        }

        // In-store pickup and local delivery
        $instore_pickup_local_devlivery_action = apply_filters('freightquote_quests_quotes_plans_suscription_and_features', 'instore_pickup_local_devlivery');
        if (!is_array($instore_pickup_local_devlivery_action)) {
            $post_data = apply_filters('en_wd_standard_plans', $post_data, $post_data['receiverZip'], $this->en_wd_origin_array, $package_plugin);
        }

        $post_data = $this->freightquote_ltl_update_carrier_service($post_data);
        $post_data = apply_filters("en_woo_addons_carrier_service_quotes_request", $post_data, freightquote_en_woo_plugin_freightquote_quests);

        $this->freightquote_ltl_update_carrier_service($post_data);

        // Standard Packaging
        // Configure standard plugin with pallet packaging addon
        $post_data = apply_filters('en_pallet_identify', $post_data);
        do_action("eniture_debug_mood", "FreightQuote Request", $post_data);

        return $post_data;
    }

    /**
     * @return shipment days of a week  - Cuttoff time
     */
    public function freightquote_shipment_week_days()
    {
        $shipment_days_of_week = array();

        if (get_option('all_shipment_days_yrc') == 'yes') {
            return $shipment_days_of_week;
        }

        if (get_option('monday_shipment_day_yrc') == 'yes') {
            $shipment_days_of_week[] = 1;
        }
        if (get_option('tuesday_shipment_day_yrc') == 'yes') {
            $shipment_days_of_week[] = 2;
        }
        if (get_option('wednesday_shipment_day_yrc') == 'yes') {
            $shipment_days_of_week[] = 3;
        }
        if (get_option('thursday_shipment_day_yrc') == 'yes') {
            $shipment_days_of_week[] = 4;
        }
        if (get_option('friday_shipment_day_yrc') == 'yes') {
            $shipment_days_of_week[] = 5;
        }

        return $shipment_days_of_week;
    }

    /**
     * Refine URL
     * @param $domain
     * @return Domain URL
     */
    function freightquote_parse_url($domain)
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

    /**
     * Get FreightQuote Country Code
     * @param $sCountryName
     * @return string
     */
    function freightquote_get_country_code($sCountryName)
    {
        switch (trim($sCountryName)) {
            case 'CN':
                $sCountryName = "CAN";
                break;
            case 'CA':
                $sCountryName = "CAN";
                break;
            case 'CAN':
                $sCountryName = "CAN";
                break;
            case 'USA':
                $sCountryName = "US";
                break;
        }
        return $sCountryName;
    }

    function freightquote_get_line_items($packages)
    {
        $lineItem = array();

        foreach ($packages['items'] as $item) {
            $lineItem[] = array(
                'lineItemHeight' => $item['productHeight'],
                'lineItemLength' => $item['productLength'],
                'lineItemWidth' => $item['productWidth'],
                'lineItemClass' => $item['productClass'],
                'lineItemWeight' => $item['productWeight'],
                'piecesOfLineItem' => $item['productQty'],
                'lineItemPackageCode' => 'Bags',
            );
        }
        return $lineItem;
    }

    /**
     * destination_address_freightquote
     * @return array type
     */
    function destination_address_freightquote()
    {
        $en_order_accessories = apply_filters('en_order_accessories', []);
        if (isset($en_order_accessories) && !empty($en_order_accessories)) {
            return $en_order_accessories;
        }

        $freight_zipcode = (strlen(WC()->customer->get_shipping_postcode()) > 0) ? WC()->customer->get_shipping_postcode() : $this->freightquote_woo_obj->freightquote_postcode();
        $freight_state = (strlen(WC()->customer->get_shipping_state()) > 0) ? WC()->customer->get_shipping_state() : $this->freightquote_woo_obj->freightquote_getState();
        $freight_country = (strlen(WC()->customer->get_shipping_country()) > 0) ? WC()->customer->get_shipping_country() : $this->freightquote_woo_obj->freightquote_getCountry();
        $freight_city = (strlen(WC()->customer->get_shipping_city()) > 0) ? WC()->customer->get_shipping_city() : $this->freightquote_woo_obj->freightquote_getCity();
        return array(
            'city' => $freight_city,
            'state' => $freight_state,
            'zip' => $freight_zipcode,
            'country' => $freight_country
        );
    }

    /**
     * Get Web Quotes CURL Call
     * @param $request_data
     * @return json
     */
    function ltl_shipping_get_web_freightquote_quotes($request_data, $quote_settings)
    {
        $this->quote_settings = $quote_settings;

        // Eniture debug mood
        do_action("eniture_debug_mood", "FreightQuoute Build Query", http_build_query($request_data));
        // Check response from session
        $currentData = md5(wp_json_encode($request_data));
        $requestFromSession = WC()->session->get('previousRequestData');
        $requestFromSession = ((is_array($requestFromSession)) && (!empty($requestFromSession))) ? $requestFromSession : array();

        if (is_array($request_data) && count($request_data) > 0) {

            $freightquote_ltl_curl_obj = new Freightquote_LTL_Curl_Request();
            $output = $freightquote_ltl_curl_obj->freightquote_ltl_get_curl_response($this->EndPointURL, $request_data);

            // Eniture debug mood
            do_action("eniture_debug_mood", "FreightQuote Quotes Response", json_decode($output));

            // Set response in session
            $response = json_decode($output);

            $errorDescriptions = (isset($response->q->quoteSpeedFreightShipmentReturn->errorDescriptions) ? $response->q->quoteSpeedFreightShipmentReturn->errorDescriptions : NULL);

            if ((isset($response->q) || isset($response->Truckload)) && (!empty($response->q) || !empty($response->Truckload)) && ($errorDescriptions == NULL)) {
                if (isset($response->autoResidentialSubscriptionExpired) &&
                    ($response->autoResidentialSubscriptionExpired == 1)) {
                    $flag_api_response = "no";
                    $request_data['residential_detecion_flag'] = $flag_api_response;
                    $currentData = md5(wp_json_encode($request_data));
                }

                $requestFromSession[$currentData] = $output;
                WC()->session->set('previousRequestData', $requestFromSession);
            }

            $quotes['quotes'] = $response;
            $quotes['markup'] = "";
            return $this->parse_freightquote_ltl_output($quotes, $request_data);

        }
    }

    /**
     * Get Shipping Array For Single Shipment
     * @param $output
     * @return Single Quote Array
     */
    function parse_freightquote_ltl_output($result, $request_data)
    {
        $markup = (isset($request_data['markup'])) ? $request_data['markup'] : [];

        // FDO
        $en_fdo_meta_data = (isset($request_data['en_fdo_meta_data'])) ? $request_data['en_fdo_meta_data'] : '';
        if (isset($result['quotes']->debug)) {
            $en_fdo_meta_data['handling_unit_details'] = $result['quotes']->debug;
        }

        // Standard Packaging
        $standard_packaging = isset($result['quotes'], $result['quotes']->standardPackagingData) ? $result['quotes']->standardPackagingData : [];

        if(isset($result['quotes']->liftgateExcluded) && $result['quotes']->liftgateExcluded == 1){
            $this->liftgateExcluded = true;
        }

        $accessorials = [];
        ($this->quote_settings['liftgate_delivery'] == "yes" && !$this->liftgateExcluded) ? $accessorials[] = "L" : "";
        ($this->quote_settings['liftgate_pickup'] == "yes") ? $accessorials[] = "LP" : "";
        ($this->quote_settings['residential_delivery'] == "yes") ? $accessorials[] = "R" : "";
        (isset($request_data['hazardous']) && is_array($request_data['hazardous']) && in_array('H', $request_data['hazardous'])) ? $accessorials[] = "H" : "";

        // Truckload Logistics
        $truckload_logistics_services = $simple_truckload_logistics_services = [];
        $truckload_logistics_carrier_services = [
            // FQ
            'TSM' => (empty(get_option('en_freight_quote_truckload_flatbed_label'))) ? 'Flatbed Truckload Service' : get_option('en_freight_quote_truckload_flatbed_label'),
            'REEF' => (empty(get_option('en_freight_quote_truckload_refrigerated_label'))) ? 'Refrigerated Truckload Service' : get_option('en_freight_quote_truckload_refrigerated_label'),
            'ABHB' => (empty(get_option('en_freight_quote_truckload_van_label'))) ? 'Truckload Service' : get_option('en_freight_quote_truckload_van_label'),
            //CHR
            'Flatbed' => (empty(get_option('en_freight_quote_truckload_flatbed_label'))) ? 'Flatbed Truckload Service' : get_option('en_freight_quote_truckload_flatbed_label'),
            'Reefer' => (empty(get_option('en_freight_quote_truckload_refrigerated_label'))) ? 'Refrigerated Truckload Service' : get_option('en_freight_quote_truckload_refrigerated_label'),
            'Van' => (empty(get_option('en_freight_quote_truckload_van_label'))) ? 'Truckload Service' : get_option('en_freight_quote_truckload_van_label'),
        ];
        $this->InstorPickupLocalDelivery = (isset($result['quotes']->InstorPickupLocalDelivery)) ? $result['quotes']->InstorPickupLocalDelivery : array();

        $residentialDelivery = isset($result['quotes']->residentialDelivery) && $result['quotes']->residentialDelivery == 'y' ? $result['quotes']->residentialDelivery : 'n';
        $liftGateDelivery = isset($result['quotes']->liftGateDelivery) && $result['quotes']->liftGateDelivery == 'y' ? $result['quotes']->liftGateDelivery : 'n';

        $quote_results = (isset($result['quotes']->q)) ? $result['quotes']->q : array();
        $quote_error = (isset($result['quotes']->q->severity) && !empty($result['quotes']->q->severity)) && $result['quotes']->q->severity == 'ERROR' ? $result['quotes']->q->severity : NULL;

        $api_single_quote = new stdClass();

        $customer_code_list = [
            'C48618',
            'C8496770',
        ];
        $customer_code = get_option('freightquote_customer_code');

        (isset($quote_results->t)) ? $this->quote_settings['sandbox'] = "sandbox" : "";

        if (isset($quote_results) && is_array($quote_results) && count($quote_results) == 1) {
            $api_single_quote->{0} = $quote_results;
            $quote_results = (object)$api_single_quote;
        }

        $label_sufex_arr = array();
        
        // calculation for normal quotes
        if (isset($result['quotes']->q) && !isset($result['quotes']->error) && (!empty($result['quotes']->q)) 
        && !isset($result['quotes']->q->severity)) {
            $count = 0;
            $meta_data = array();
            $price_quotes = $price_truckload_logistics_services = array();
            $quotes = array();
            foreach ($result['quotes']->q as $key => $quote) {

                // Cuttoff Time
                $delivery_estimates = (isset($quote->totalTransitTimeInDays)) ? $quote->totalTransitTimeInDays : '';
                $delivery_time_stamp = (isset($quote->deliveryTimestamp)) ? $quote->deliveryTimestamp : '';
                if ((isset($quote->serviceType) && in_array($quote->serviceType, $this->quote_settings['enable_carriers'])) || in_array($customer_code, $customer_code_list)) {

                    if(isset($result['quotes']->Truckload) && !empty($result['quotes']->Truckload) && array_key_exists($quote->serviceType, $truckload_logistics_carrier_services)){
                        continue;
                    }
                    
                    $surcharges = isset($quote->surcharges) ? (array)$quote->surcharges : array();
                    $surcharges['residentialDelivery'] = $residentialDelivery;
                    $surcharges['liftGateDelivery'] = $liftGateDelivery;

                    $label_sufex_arr = $this->filter_label_sufex_array_freightquote_ltl($surcharges);

                    $meta_data['accessorials'] = wp_json_encode($accessorials);
                    $meta_data['sender_origin'] = $request_data['sender_origin'];
                    $meta_data['product_name'] = wp_json_encode($request_data['product_name']);
                    $meta_data['address'] = [];
                    $meta_data['_address'] = '';
                    // Standard Packaging
                    $meta_data['standard_packaging'] = wp_json_encode($standard_packaging);
                    $meta_data['quote_id'] = isset( $quote->quoteId ) ? $quote->quoteId : '';

                    $unique_key = $this->get_unique_service_index($quote->serviceType);
                    $cost = $this->addProductAndOriginMarkup($quote->totalNetCharge, $request_data);

                    $quotes[$count] = array(
                        'id' => $quote->serviceType . '_' . $unique_key,
                        'label' => $quote->serviceDesc,
                        'cost' => $cost,
                        // Cuttoff Time
                        'delivery_estimates' => $delivery_estimates,
                        'delivery_time_stamp' => $delivery_time_stamp,
                        'label_sfx_arr' => $label_sufex_arr,
                        'meta_data' => $meta_data,
                        'markup' => $this->quote_settings['handling_fee'],
                        'product_markup' => $markup,
                        'quote_id' => isset( $quote->quoteId ) ? $quote->quoteId : '',
                        'plugin_name' => 'b2b',
                        'plugin_type' => 'ltl',
                        'owned_by' => 'eniture',
                    );

                    $quotes[$count] = apply_filters('en_product_markup', $quotes[$count]);

                    $en_fdo_meta_data['rate'] = $quotes[$count];
                    if (isset($en_fdo_meta_data['rate']['meta_data'])) {
                        unset($en_fdo_meta_data['rate']['meta_data']);
                    }
                    $en_fdo_meta_data['quote_settings'] = $this->quote_settings;
                    $quotes[$count]['meta_data']['en_fdo_meta_data'] = $en_fdo_meta_data;

                    $price_quotes[$count] = (isset($quotes[$count]['cost'])) ? $quotes[$count]['cost'] : 0;
                    $quotes[$count] = apply_filters("en_woo_addons_web_quotes", $quotes[$count], freightquote_en_woo_plugin_freightquote_quests);

                    $label_sufex = (isset($quotes[$count]['label_sfx_arr'])) ? $quotes[$count]['label_sfx_arr'] : array();
                    $label_sufex = $this->label_residential_freightquote_ltl($label_sufex);
                    $quotes[$count]['label_sufex'] = $label_sufex;

                    in_array('R', $label_sufex_arr) ? $quotes[$count]['meta_data']['en_fdo_meta_data']['accessorials']['residential'] = true : '';
                    ($this->quote_settings['liftgate_resid_delivery'] == "yes") && (in_array("R", $label_sufex)) && in_array('L', $label_sufex_arr) ? $quotes[$count]['meta_data']['en_fdo_meta_data']['accessorials']['liftgate'] = true : '';

                    if (($this->quote_settings['liftgate_delivery_option'] == "yes") && !$this->liftgateExcluded && 
                        (($this->quote_settings['liftgate_resid_delivery'] == "yes") && (!in_array("R", $label_sufex)) ||
                            ($this->quote_settings['liftgate_resid_delivery'] != "yes"))) {

                        (isset($quotes[$count]['label_sufex']) &&
                            (!empty($quotes[$count]['label_sufex']))) ?
                            array_push($quotes[$count]['label_sufex'], "L") : // IF
                            $quotes[$count]['label_sufex'] = array("L");       // ELSE

                        $quotes[$count]['meta_data']['en_fdo_meta_data']['accessorials']['liftgate'] = true;
                    }

                    $count++;
                }
            }
        }

        // calculation for quotesWithoutLiftGate
        if (isset($result['quotes']->quotesWithoutLiftGate) && !isset($result['quotes']->error) 
        && (!empty($result['quotes']->quotesWithoutLiftGate)) && !isset($result['quotes']->quotesWithoutLiftGate->severity)) {

            $price_simple_quotes = $simple_truckload_logistics_services = $price_simple_truckload_logistics_services = array();
            $simple_quotes = array();
            $count = 0;

            foreach ($result['quotes']->quotesWithoutLiftGate as $key => $quote) {
                // Cuttoff Time
                $delivery_estimates = (isset($quote->totalTransitTimeInDays)) ? $quote->totalTransitTimeInDays : '';
                $delivery_time_stamp = (isset($quote->deliveryTimestamp)) ? $quote->deliveryTimestamp : '';
                if ((isset($quote->serviceType) && in_array($quote->serviceType, $this->quote_settings['enable_carriers'])) || in_array($customer_code, $customer_code_list)) {
                    
                    if(!empty($result['quotes']->Truckload) && array_key_exists($quote->serviceType, $truckload_logistics_carrier_services)){
                        continue;
                    }
                    
                    $quote->transitDays = (isset($quote->totalTransitTimeInDays) && is_string($quote->totalTransitTimeInDays)) ? $quote->totalTransitTimeInDays : '';
                    $meta_data['accessorials'] = wp_json_encode($accessorials);
                    $meta_data['sender_origin'] = $request_data['sender_origin'];
                    $meta_data['product_name'] = wp_json_encode($request_data['product_name']);
                    $meta_data['address'] = [];
                    $meta_data['_address'] = '';
                    // Standard Packaging
                    $meta_data['standard_packaging'] = wp_json_encode($standard_packaging);
                    $meta_data['quote_id'] = isset( $quote->quoteId ) ? $quote->quoteId : '';

                    $unique_key = $this->get_unique_service_index($quote->serviceType);
                    $cost = $this->addProductAndOriginMarkup($quote->totalNetCharge, $request_data);

                    $simple_quotes[$count] = array(
                        'id' => $quote->serviceType . '_' . $unique_key . 'WL',
                        'label' => $quote->serviceDesc,
                        'cost' => $cost,
                        // Cuttoff Time
                        'delivery_estimates' => $delivery_estimates,
                        'delivery_time_stamp' => $delivery_time_stamp,
                        'label_sfx_arr' => $label_sufex_arr,
                        'meta_data' => $meta_data,
                        'product_markup' => $markup,
                        'quote_id' => isset( $quote->quoteId ) ? $quote->quoteId : '',
                        'plugin_name' => 'b2b',
                        'plugin_type' => 'ltl',
                        'owned_by' => 'eniture',
                    );

                    $simple_quotes[$count] = apply_filters('en_product_markup', $simple_quotes[$count]);

                    // FDO
                    $en_fdo_meta_data['rate'] = $simple_quotes[$count];
                    if (isset($en_fdo_meta_data['rate']['meta_data'])) {
                        unset($en_fdo_meta_data['rate']['meta_data']);
                    }
                    $en_fdo_meta_data['quote_settings'] = $this->quote_settings;
                    $simple_quotes[$count]['meta_data']['en_fdo_meta_data'] = $en_fdo_meta_data;

                    $price_simple_quotes[$count] = (isset($simple_quotes[$count]['cost'])) ? $simple_quotes[$count]['cost'] : 0;
                    $simple_quotes[$count] = apply_filters("en_woo_addons_web_quotes", $simple_quotes[$count], freightquote_en_woo_plugin_freightquote_quests);

                    $label_sufex = (isset($simple_quotes[$count]['label_sufex'])) ? $simple_quotes[$count]['label_sufex'] : array();

                    isset($label_sufex_arr) && is_array($label_sufex_arr) && in_array('R', $label_sufex_arr) ? $simple_quotes[$count]['meta_data']['en_fdo_meta_data']['accessorials']['residential'] = true : '';

                    if (($this->quote_settings['liftgate_delivery_option'] == "yes") &&
                        (($this->quote_settings['liftgate_resid_delivery'] == "yes") && (in_array("R", $label_sufex)))) {
                        $simple_quotes = array();
                        continue;
                    }
                    $label_sufex = $this->label_residential_freightquote_ltl($label_sufex);
                    $simple_quotes[$count]['label_sufex'] = $label_sufex;
                    (!empty($simple_quotes[$count])) && (in_array("R", $simple_quotes[$count]['label_sufex'])) ? $simple_quotes[$count]['label_sufex'] = array("R") : $simple_quotes[$count]['label_sufex'] = array();

                    $count++;
                }
            }
        }

        // calculation for TL services
        if (isset($result['quotes']->Truckload) && !isset($result['quotes']->error) && (!empty($result['quotes']->Truckload)) 
        && !isset($result['quotes']->Truckload->severity)) {

            $price_tl_quotes = array();
            $tl_quotes = array();
            $count = 0;

            foreach ($result['quotes']->Truckload as $key => $quote) {
                if(isset($quote->severity)){
                    continue;
                }
                // Cuttoff Time
                $delivery_estimates = (isset($quote->totalTransitTimeInDays)) ? $quote->totalTransitTimeInDays : '';
                $delivery_time_stamp = (isset($quote->deliveryTimestamp)) ? $quote->deliveryTimestamp : '';
                if ((isset($quote->serviceType) && isset($truckload_logistics_carrier_services[$quote->serviceType]) && in_array($quote->serviceType, $this->quote_settings['enable_carriers']))) {
                    
                    $quote->transitDays = (isset($quote->totalTransitTimeInDays) && is_string($quote->totalTransitTimeInDays)) ? $quote->totalTransitTimeInDays : '';
                    // $meta_data['accessorials'] = wp_json_encode($accessorials);
                    $meta_data['sender_origin'] = $request_data['sender_origin'];
                    $meta_data['product_name'] = wp_json_encode($request_data['product_name']);
                    $meta_data['address'] = [];
                    $meta_data['_address'] = '';
                    // Standard Packaging
                    $meta_data['standard_packaging'] = wp_json_encode($standard_packaging);
                    $meta_data['quote_id'] = isset( $quote->quoteId ) ? $quote->quoteId : '';

                    $unique_key = $this->get_unique_service_index($quote->serviceType);
                    $cost = $this->addProductAndOriginMarkup($quote->totalNetCharge, $request_data);

                    $tl_quotes[$count] = array(
                        'id' => $quote->serviceType . '_' . $unique_key,
                        'label' => $truckload_logistics_carrier_services[$quote->serviceType],
                        'cost' => $cost,
                        'delivery_estimates' => $delivery_estimates,
                        'delivery_time_stamp' => $delivery_time_stamp,
                        'meta_data' => $meta_data,
                        'product_markup' => $markup,
                        'quote_id' => isset( $quote->quoteId ) ? $quote->quoteId : '',
                        'plugin_name' => 'b2b',
                        'plugin_type' => 'ltl',
                        'owned_by' => 'eniture',
                    );

                    $tl_quotes[$count] = apply_filters('en_product_markup', $tl_quotes[$count]);

                    // FDO
                    $en_fdo_meta_data['rate'] = $tl_quotes[$count];
                    if (isset($en_fdo_meta_data['rate']['meta_data'])) {
                        unset($en_fdo_meta_data['rate']['meta_data']);
                    }

                    if(isset($en_fdo_meta_data['accessorials']) && is_array($en_fdo_meta_data['accessorials'])){
                        if(!empty(get_option('freightquote_api_endpoint')) && get_option('freightquote_api_endpoint') == 'freightquote_new_api'){
                            foreach ($en_fdo_meta_data['accessorials'] as $key => $value) {
                                if($key != 'hazmat'){
                                    unset($en_fdo_meta_data['accessorials'][$key]);
                                }
                            }
                        }else{
                            $en_fdo_meta_data['accessorials'] = [];
                        }
                    }

                    $en_fdo_meta_data['quote_settings'] = $this->quote_settings;
                    $tl_quotes[$count]['meta_data']['en_fdo_meta_data'] = $en_fdo_meta_data;

                    $price_tl_quotes[$count] = (isset($tl_quotes[$count]['cost'])) ? $tl_quotes[$count]['cost'] : 0;
                    $tl_quotes[$count] = apply_filters("en_woo_addons_web_quotes", $tl_quotes[$count], freightquote_en_woo_plugin_freightquote_quests);

                    $count++;
                }
            }
        }
        
//          array multisort 
        (!empty($quotes)) ? array_multisort($price_quotes, SORT_ASC, $quotes) : $quotes = array();
        (!empty($simple_quotes)) ? array_multisort($price_simple_quotes, SORT_ASC, $simple_quotes) : $simple_quotes = array();
        
        (!empty($simple_quotes)) ? $quotes['simple_quotes'] = $simple_quotes : "";
        (!empty($tl_quotes)) ? $quotes['truckload_logistics_services'] = $tl_quotes : "";
        
        // Eniture debug mood
        do_action("eniture_debug_mood", "Sorting Quotes (FreightQuote)", $quotes);

        return $quotes;
    }

    /**
     * check "R" in array
     * @param array type $label_sufex
     * @return array type
     */
    public function label_residential_freightquote_ltl($label_sufex)
    {
        if (get_option('wc_settings_freightquote_residential_delivery') == 'yes' && (in_array("R", $label_sufex))) {

            $label_sufex = array_flip($label_sufex);
            unset($label_sufex['R']);
            $label_sufex = array_keys($label_sufex);
        }

        return $label_sufex;
    }

    /**
     * Multi Warehouse
     * @param $warehous_list
     * @param $receiverZipCode
     * @return array
     */
    function freightquote_ltl_multi_warehouse($warehous_list, $receiverZipCode)
    {

        if (count($warehous_list) == 1) {
            $warehous_list = reset($warehous_list);
            return $this->ltl_origin_array($warehous_list);
        }
        require_once 'warehouse-dropship/get-distance-request.php';

        $freightquote_ltl_distance_request = new Freightquote_Get_ltl_distance();
        $accessLevel = "MultiDistance";
        $response_json = $freightquote_ltl_distance_request->freightquote_ltl_get_distance($warehous_list, $accessLevel, $this->destination_address_freightquote());

        $response_obj = json_decode($response_json);
        return $this->ltl_origin_array((isset($response_obj->origin_with_min_dist)) ? $response_obj->origin_with_min_dist : array());
    }

    /**
     * Arrange Own Freight
     * @return array
     */
    function arrange_own_freight()
    {

        return array(
            'id' => 'own_freight',
            'cost' => 0,
            'label' => get_option('wc_settings_freightquote_text_for_own_arrangment'),
            'calc_tax' => 'per_item',
            'plugin_name' => 'b2b',
            'plugin_type' => 'ltl',
            'owned_by' => 'eniture',
        );
    }

    /**
     * Origin
     * @param $origin
     * @return array
     */
    function ltl_origin_array($origin)
    {

//          In-store pickup and local delivery
        if (has_filter("en_wd_origin_array_set")) {
            return apply_filters("en_wd_origin_array_set", $origin);
        }

        $zip = (isset($origin->zip)) ? $origin->zip : "";
        $city = (isset($origin->city)) ? $origin->city : "";
        $state = (isset($origin->state)) ? $origin->state : "";
        $country = (isset($origin->country)) ? $origin->country : "";
        $location = (isset($origin->location)) ? $origin->location : "";
        $locationId = (isset($origin->id)) ? $origin->id : "";
        return array('locationId' => $locationId, 'zip' => $zip, 'city' => $city, 'state' => $state, 'location' => $location, 'country' => $country);
    }

    /**
     * Return woocomerce and freightquote ltl plugin versions
     */
    function freightquote_ltl_get_woo_version_number()
    {

        if (!function_exists('get_plugins'))
            require_once(ABSPATH . 'wp-admin/includes/plugin.php');

        $plugin_folder = get_plugins('/' . 'woocommerce');
        $plugin_file = 'woocommerce.php';

        $plugin_folders = get_plugins('/' . 'ltl-freight-quotes-freightquote-edition');
        $plugin_files = 'ltl-freight-quotes-freightquote-edition.php';


        $wc_plugin = (isset($plugin_folder[$plugin_file]['Version'])) ? $plugin_folder[$plugin_file]['Version'] : "";
        $ltl_plugin = (isset($plugin_folders[$plugin_files]['Version'])) ? $plugin_folders[$plugin_files]['Version'] : "";

        $pluginVersions = array(
            "woocommerce_plugin_version" => $wc_plugin,
            "freightquote_ltl_plugin_version" => $ltl_plugin
        );

        return $pluginVersions;
    }

    /**
     * Return YRC LTL In-store Pickup Array
     */
    function freightquote_ltl_return_local_delivery_store_pickup()
    {
        return $this->InstorPickupLocalDelivery;
    }

    /**
     * This function returns key of carrier index in carrier array
     */
    public function get_unique_service_index($service_id){
        $unique_key = array_search($service_id, $this->quote_settings['enable_carriers']);
        return (!empty($unique_key)) ? $unique_key : '0';
    }
    /**
     * This function returns active TL carriers
     */
    public function get_tl_equipment_type_arr(){
        global $wpdb;
        $table_name = FREIGHTQUOTE_FREIGHT_CARRIERS;
        $enable_carrier = $wpdb->get_results("SELECT `freightQuote_carrierSCAC` FROM $table_name WHERE freightQuote_carrierSCAC in ('Van', 'Flatbed', 'Reefer') AND carrier_status ='1'");
        $response = [];
        if(is_array($enable_carrier) && count($enable_carrier) > 0){
            foreach($enable_carrier as $carrier){
                $response[] = $carrier->freightQuote_carrierSCAC;
            }
        }
        
        return $response;
    }

    public function addProductAndOriginMarkup($price, $request_data)
    {
        // Add product level markup
        if (!empty($request_data['product_level_markup'])) {
            $price = $this->add_markup($price, $request_data['product_level_markup']);
        }

        // Add origin level markup
        if (!empty($request_data['origin_markup'])) {
            $price = $this->add_markup($price, $request_data['origin_markup']);
        }

        return $price;
    }

    /**
     * @param $price
     * @param $markup_fee
     * @return float
     */
    function add_markup($price, $markup_fee)
    {
        $markup_fee = $price > 0 ? $markup_fee : 0;
        $markupFee = 0;
     
        if ($markup_fee != '' && $markup_fee != 0) {
            if (strrchr($markup_fee, "%")) {

                $prcnt = (float)$markup_fee;
                $markupFee = (float)$price / 100 * $prcnt;
            } else {
                $markupFee = (float)$markup_fee;
            }
        }


        $price = (float)$price + $markupFee;
     
        return $price;
    }

}
