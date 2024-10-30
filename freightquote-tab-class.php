<?php
/**
 * Freightquote LTL Tab Class
 *
 * @package     Freightquote LTL
 * @author      Eniture-Technology
 */
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Freightquote_WC_ltl_Settings_tabs
 */
class Freightquote_WC_ltl_Settings_tabs extends WC_Settings_Page
{

    /**
     * Constructor
     */
    public function __construct()
    {

        $this->id = 'freightquote_quests';
        add_filter('woocommerce_settings_tabs_array', array($this, 'add_settings_tab'), 50);
        add_action('woocommerce_sections_' . $this->id, array($this, 'output_sections'));
        add_action('woocommerce_settings_' . $this->id, array($this, 'output'));
        add_action('woocommerce_settings_save_' . $this->id, array($this, 'save'));
    }

    /**
     * Add Setting Tab
     * @param $settings_tabs
     * @return array
     */
    public function add_settings_tab($settings_tabs)
    {

        $settings_tabs[$this->id] = __('Freightquote', 'eniture-freightquote-ltl');
        return $settings_tabs;
    }

    /**
     * Get Section
     * @return array
     */
    public function get_sections()
    {

        $sections = array(
            '' => __('Connection Settings', 'eniture-freightquote-ltl'),
            'section-1' => __('Carriers', 'eniture-freightquote-ltl'),
            'section-2' => __('Quote Settings', 'eniture-freightquote-ltl'),
            'section-3' => __('Warehouses', 'eniture-freightquote-ltl'),
            // fdo va
            'section-5' => __('FreightDesk Online', 'eniture-freightquote-ltl'),
            'section-6' => __('Validate Addresses', 'eniture-freightquote-ltl'),
            'section-4' => __('User Guide', 'eniture-freightquote-ltl'),
        );

        // Logs data
        $enable_logs = get_option('shipping_logs_freightquote');
        if ($enable_logs == 'yes') {
            $sections['en-logs'] = 'Logs';
        }

        $sections = apply_filters('en_woo_addons_sections', $sections, freightquote_en_woo_plugin_freightquote_quests);
        // Standard Packaging
        $sections = apply_filters('en_woo_pallet_addons_sections', $sections, freightquote_en_woo_plugin_freightquote_quests);
        return apply_filters('woocommerce_get_sections_' . $this->id, $sections);
    }

    /**
     * Warehouses
     */
    public function ltl_warehouse()
    {
        require_once 'warehouse-dropship/wild/warehouse/warehouse_template.php';
        require_once 'warehouse-dropship/wild/dropship/dropship_template.php';
    }

    /**
     * User Guide
     */
    public function ltl_user_guide()
    {

        include_once('template/guide.php');
    }

    /**
     * Setting Tab
     * @return array
     */
    public function ltl_section_setting_tab()
    {

        $settings = array(
            'section_title_freightquote' => array(
                'name' => '',
                'type' => 'title',
                'desc' => '<br> ',
                'id' => 'wc_settings_freightquote_title_section_connection',
            ),
            'freightquote_api_endpoint' => array(
                'name' => __('API Endpoint ', 'eniture-freightquote-ltl'),
                'type' => 'select',
                'default' => 'freightquote_old_api',
                'id' => 'freightquote_api_endpoint',
                'options' => array(
                    'freightquote_old_api' => __('FreightQuote.com', 'eniture-freightquote-ltl'),
                    'freightquote_new_api' => __('C. H. Robinson API', 'eniture-freightquote-ltl'),
                )
            ),
            // NEW API
            'freightquote_customer_code' => array(
                'name' => __('Customer Code ', 'eniture-freightquote-ltl'),
                'type' => 'text',
                'class' => 'freightquote_new_api',
                'id' => 'freightquote_customer_code'
            ),
            // OLD API
            'freightquote_username' => array(
                'name' => __('Username ', 'eniture-freightquote-ltl'),
                'type' => 'text',
                'class' => 'freightquote_old_api',
                'id' => 'wc_settings_freigtquote_freight_username'
            ),
            'freightquote_password' => array(
                'name' => __('Password ', 'eniture-freightquote-ltl'),
                'type' => 'text',
                'class' => 'freightquote_old_api',
                'id' => 'wc_settings_freigtquote_freight_password'
            ),
            'freightquote_license_key' => array(
                'name' => __('Eniture API Key ', 'eniture-freightquote-ltl'),
                'type' => 'text',
                'class' => 'freightquote_new_api freightquote_old_api',
                'desc' => __('Obtain a Eniture API Key from <a href="https://eniture.com/woocommerce-freightquote-ltl-freight/" target="_blank" >eniture.com </a>', 'eniture-freightquote-ltl'),
                'id' => 'wc_settings_freightquote_license_key'
            ),
            'save_freightquote_buuton' => array(
                'name' => __('Save Button ', 'eniture-freightquote-ltl'),
                'type' => 'button',
                'id' => 'wc_settings_freightquote_button'
            ),
            'section_end_freightquote' => array(
                'type' => 'sectionend',
                'id' => 'wc-settings-freightquote-end-section_connection'
            ),
        );
        return $settings;
    }

    /**
     * Get Settings
     * @param $section
     * @return array
     * @global $wpdb
     */
    public function get_settings($section = null)
    {
        ob_start();
        $settings = array();
        switch ($section) {

            case 'section-0' :

                echo '<div class="freightquote_ltl_connection_section_class">';
                $settings = $this->ltl_section_setting_tab();
                break;

            case 'section-1':

                echo '<div class="carrier_section_class">';
                ?>
                <div class="carrier_section_class wrap woocommerce">
                    <p>
                        Identifies which carriers are included in the quote response, not what is displayed in the
                        shopping cart. Identify what displays in the shopping cart in the Quote Settings. For example,
                        you may include quote responses from all carriers, but elect to only show the cheapest three in
                        the shopping cart. <br> <br>
                        Not all carriers service all origin and destination points. If a carrier doesn`t service the
                        ship to address, it is automatically omitted from the quote response. Consider conferring with
                        your Freightquote representative if you`d like to narrow the number of carrier responses.
                        <br> <br> <br>
                    </p>
                    <table style="width:100% !important;">
                        <tbody>
                        <thead>
                        <tr class="FreightQuote_even_odd_class">
                            <th class="FreightQuote_carrier_carrier">Carrier Name</th>
                            <th class="FreightQuote__carrier_logo">Logo</th>
                            <th class="FreightQuote_carrier_include"><input type="checkbox" name="include_all"
                                                                            class="include_all"/></th>
                        </tr>
                        </thead>
                        <?php
                        $api_selected = get_option('freightquote_api_endpoint');
                        $fq_tl_carriers_scac_arr = ['ABHB', 'REEF', 'TSM'];
                        $chr_tl_carriers_scac_arr = ['Van', 'Flatbed', 'Reefer'];
                        global $wpdb;
                        $all_freight_array = array();
                        $count_carrier = 1;
                        $table_name = FREIGHTQUOTE_FREIGHT_CARRIERS;
                        $ltl_freight_all = $wpdb->get_results("SELECT * FROM $table_name GROUP BY freightQuote_carrierSCAC ORDER BY freightQuote_carrierName ASC");
                        foreach ($ltl_freight_all as $ltl_freight_value):
                            if((in_array($ltl_freight_value->freightQuote_carrierSCAC, $fq_tl_carriers_scac_arr) && $api_selected == 'freightquote_new_api') 
                            || (in_array($ltl_freight_value->freightQuote_carrierSCAC, $chr_tl_carriers_scac_arr) && $api_selected == 'freightquote_old_api')){
                                continue;
                            }
                            ?>
                            <tr <?php
                            if ($count_carrier % 2 == 0) {

                                echo 'class="FreightQuote_even_odd_class"';
                            }
                            ?> >

                                <td class="FreightQuote_carrier_Name_td">
                                    <?php echo esc_attr( $ltl_freight_value->freightQuote_carrierName ); ?>
                                </td>
                                <td>
                                    <img src="<?php echo esc_url_raw( plugins_url('Carrier_Logos/' . $ltl_freight_value->carrier_logo, __FILE__) ) ?> ">
                                </td>
                                <td>
                                    <input <?php
                                    if ($ltl_freight_value->carrier_status == '1') {
                                        echo 'checked="checked"';
                                    }
                                    ?>
                                            name="<?php echo esc_attr( $ltl_freight_value->freightQuote_carrierSCAC ) . esc_attr( $ltl_freight_value->id ); ?>"
                                            class="carrier_check"
                                            id="<?php echo esc_attr( $ltl_freight_value->freightQuote_carrierSCAC ) . esc_attr( $ltl_freight_value->id ); ?>"
                                            type="checkbox">
                                </td>
                            </tr>
                            <?php
                            $count_carrier++;
                        endforeach;
                        ?>
                        <input name="action" value="freight_quote_save_carrier_status" type="hidden"/>
                        </tbody>
                    </table>
                </div>
                <?php
                break;

            case 'section-2':
                // Cuttoff Time
                $freightquote_disable_cutt_off_time_ship_date_offset = "";
                $freightquote_cutt_off_time_package_required = "";

                //  Check the cutt of time & offset days plans for disable input fields
                $freightquote_action_cutOffTime_shipDateOffset = apply_filters('freightquote_quests_quotes_plans_suscription_and_features', 'freightquote_cutt_off_time');
                if (is_array($freightquote_action_cutOffTime_shipDateOffset)) {
                    $freightquote_disable_cutt_off_time_ship_date_offset = "disabled_me";
                    $freightquote_cutt_off_time_package_required = apply_filters('freightquote_quests_plans_notification_link', $freightquote_action_cutOffTime_shipDateOffset);
                }

                $ltl_enable = get_option('en_plugins_return_LTL_quotes');
                $weight_threshold_class = $ltl_enable == 'yes' ? 'show_en_weight_threshold_lfq' : 'hide_en_weight_threshold_lfq';
                $weight_threshold = get_option('en_weight_threshold_lfq');
                $weight_threshold = isset($weight_threshold) && $weight_threshold > 0 ? $weight_threshold : 150;

                $new_api_fields_class = $old_api_fields_class = '';
                $api_selected = get_option('freightquote_api_endpoint');
                ($api_selected == 'freightquote_new_api') ? $old_api_fields_class = 'en_hide_fields' : $new_api_fields_class = 'en_hide_fields';

                global $wpdb;
                $table_name = FREIGHTQUOTE_FREIGHT_CARRIERS;
                $carriers_table = $table_name;
                $van_class = $flatbed_class = $reefer_class = $tl_others_class = '';
                if($api_selected == 'freightquote_new_api'){
                    
                    $van_carrier = $wpdb->get_results("SELECT COUNT(*) AS carrier FROM $carriers_table where freightQuote_carrierSCAC = 'Van' and carrier_status = 1");
                    $van_class = ($van_carrier[0]->carrier == 0) ? 'en_hide_fields' : '';

                    $flatbed_carrier = $wpdb->get_results("SELECT COUNT(*) AS carrier FROM $carriers_table where freightQuote_carrierSCAC = 'Flatbed' and carrier_status = 1");
                    $flatbed_class = ($flatbed_carrier[0]->carrier == 0) ? 'en_hide_fields' : '';

                    $reefer_carrier = $wpdb->get_results("SELECT COUNT(*) AS carrier FROM $carriers_table where freightQuote_carrierSCAC = 'Reefer' and carrier_status = 1");
                    $reefer_class = ($reefer_carrier[0]->carrier == 0) ? 'en_hide_fields' : '';

                }else{

                    $van_carrier = $wpdb->get_results("SELECT COUNT(*) AS carrier FROM $carriers_table where freightQuote_carrierSCAC = 'ABHB' and carrier_status = 1");
                    ($van_carrier[0]->carrier == 0) ? $van_class = 'en_hide_fields' : '';

                    $flatbed_carrier = $wpdb->get_results("SELECT COUNT(*) AS carrier FROM $carriers_table where freightQuote_carrierSCAC = 'TSM' and carrier_status = 1");
                    ($flatbed_carrier[0]->carrier == 0) ? $flatbed_class = 'en_hide_fields' : '';

                    $reefer_carrier = $wpdb->get_results("SELECT COUNT(*) AS carrier FROM $carriers_table where freightQuote_carrierSCAC = 'REEF' and carrier_status = 1");
                    ($reefer_carrier[0]->carrier == 0) ? $reefer_class = 'en_hide_fields' : '';

                }

                if($van_carrier[0]->carrier == 0 && $flatbed_carrier[0]->carrier == 0 && $reefer_carrier[0]->carrier == 0){
                    $tl_others_class = 'en_hide_fields';        
                }
                
                echo '<div class="quote_section_class_ltl">';
                $settings = array(
                    'section_title_quote' => array(
                        'title' => '',
                        'type' => 'title',
                        'desc' => '',
                        'id' => 'wc_settings_freightquote_section_title_quote'
                    ),
                    'en_freight_quote_label_ltl_settings' => array(
                        'name' => __('LTL Freight Settings ', 'eniture-freightquote-ltl'),
                        'type' => 'text',
                        'class' => 'hidden',
                        'id' => 'en_freight_quote_label_ltl_settings'
                    ),
                    'rating_method_freightquote' => array(
                        'name' => __('Rating Method ', 'eniture-freightquote-ltl'),
                        'type' => 'select',
                        'desc' => __('Displays only the cheapest returned Rate.', 'eniture-freightquote-ltl'),
                        'id' => 'wc_settings_freightquote_rate_method',
                        'options' => array(
                            'Cheapest' => __('Cheapest', 'eniture-freightquote-ltl'),
                            'cheapest_options' => __('Cheapest Options', 'eniture-freightquote-ltl'),
                            'average_rate' => __('Average Rate', 'eniture-freightquote-ltl')
                        )
                    ),
                    'number_of_options_freightquote' => array(
                        'name' => __('Number Of Options ', 'eniture-freightquote-ltl'),
                        'type' => 'select',
                        'default' => '3',
                        'desc' => __('Number of options to display in the shopping cart.', 'eniture-freightquote-ltl'),
                        'id' => 'wc_settings_freightquote_Number_of_options',
                        'options' => array(
                            '1' => '1',
                            '2' => '2',
                            '3' => '3',
                            '4' => '4',
                            '5' => '5',
                            '6' => '6',
                            '7' => '7',
                            '8' => '8',
                            '9' => '9',
                            '10' => '10',
                            '11' => '11',
                            '12' => '12',
                            '13' => '13',
                            '14' => '14',
                            '15' => '15',
                            '16' => '16',
                            '17' => '17',
                            '18' => '18',
                            '19' => '19',
                            '19' => '19',
                            '20' => '20',
                            '21' => '21',
                            '22' => '22',
                            '23' => '23',
                            '24' => '24',
                            '25' => '25',
                            '26' => '26',
                            '27' => '27',
                            '28' => '28',
                            '29' => '29',
                            '30' => '30',
                            '31' => '31',
                            '32' => '32',
                            '33' => '33',
                            '34' => '34',
                            '35' => '35',
                            '36' => '36',
                            '37' => '37',
                            '38' => '38',
                            '39' => '39',
                            '40' => '40'
                        )
                    ),
                    'label_as_freightquote' => array(
                        'name' => __('Label As ', 'eniture-freightquote-ltl'),
                        'type' => 'text',
                        'desc' => __('What the user sees during checkout, e.g "Freight" leave blank to display the carrier name.', 'eniture-freightquote-ltl'),
                        'id' => 'wc_settings_freightquote_label_as'
                    ),

                    // Truckload settings
                    'en_freight_quote_label_truckload_settings' => array(
                        'name' => __('Truckload Settings ', 'eniture-freightquote-ltl'),
                        'type' => 'text',
                        'class' => $tl_others_class.' hidden',
                        'id' => 'en_freight_quote_label_truckload_settings'
                    ),

                    'en_freight_quote_truckload_flatbed_label' => array(
                        'name' => __('Flatbed  ', 'eniture-freightquote-ltl'),
                        'type' => 'text',
                        'desc' => 'What the user sees during checkout, e.g. "Freight". Leave blank to display "Flatbed Truckload Service".',
                        'class' => $flatbed_class.' en_freight_quote_truckload_flatbed_label',
                        'id' => 'en_freight_quote_truckload_flatbed_label'
                    ),
                    'en_freight_quote_truckload_refrigerated_label' => array(
                        'name' => __('Refrigerated  ', 'eniture-freightquote-ltl'),
                        'type' => 'text',
                        'desc' => 'What the user sees during checkout, e.g. "Freight". Leave blank to display "Refrigerated Truckload Service".',
                        'class' => $reefer_class.' en_freight_quote_truckload_refrigerated_label',
                        'id' => 'en_freight_quote_truckload_refrigerated_label'
                    ),
                    'en_freight_quote_truckload_van_label' => array(
                        'name' => __('Van  ', 'eniture-freightquote-ltl'),
                        'type' => 'text',
                        'desc' => 'What the user sees during checkout, e.g. "Freight". Leave blank to display "Truckload Service".',
                        'class' => $van_class.' en_freight_quote_truckload_van_label',
                        'id' => 'en_freight_quote_truckload_van_label'
                    ),
                    'en_freight_quote_truckload_weight_threshold' => array(
                        'name' => __('Truckload weight threshold  ', 'eniture-freightquote-ltl'),
                        'type' => 'text',
                        'default'   => '7000',
                        'desc' => 'When the weight of the cart is greater than this value then Truckload rate should be returned.',
                        'class' => $tl_others_class.' '.$old_api_fields_class.' en_freight_quote_truckload_weight_threshold',
                        'id' => 'en_freight_quote_truckload_weight_threshold'
                    ),
                    'en_freight_quote_truckload_weight_threshold_chr' => array(
                        'name' => __('Truckload weight threshold  ', 'eniture-freightquote-ltl'),
                        'type' => 'text',
                        'default'   => '7500',
                        'desc' => 'When the weight of the cart is greater than this value then Truckload rate should be returned.',
                        'class' => $tl_others_class.' '.$new_api_fields_class.' en_freight_quote_truckload_weight_threshold_chr',
                        'id' => 'en_freight_quote_truckload_weight_threshold_chr'
                    ),
                    'en_freight_quote_ltl_above_threshold' => array(
                        'name' => '',
                        'type' => 'checkbox',
                        'desc' => 'Quote LTL freight above the truckload weight threshold', 
                        'id' => 'en_freight_quote_ltl_above_threshold',
                        'class' => $tl_others_class.' en_freight_quote_ltl_above_threshold'
                    ),

                    //** Start Delivery Estimate Options - Cuttoff Time
                    'service_freightquote_estimates_title' => array(
                        'name' => __('Delivery Estimate Options ', 'eniture-freightquote-ltl'),
                        'type' => 'text',
                        'desc' => '',
                        'id' => 'service_freightquote_estimates_title'
                    ),
                    'freightquote_show_delivery_estimates_options_radio' => array(
                        'name' => '',
                        'type' => 'radio',
                        'default' => 'dont_show_estimates',
                        'options' => array(
                            'dont_show_estimates' => __("Don't display delivery estimates.", 'eniture-freightquote-ltl'),
                            'delivery_days' => __("Display estimated number of days until delivery.", 'eniture-freightquote-ltl'),
                            'delivery_date' => __("Display estimated delivery date.", 'eniture-freightquote-ltl'),
                        ),
                        'id' => 'freightquote_delivery_estimates',
                        'class' => 'freightquote_dont_show_estimate_option',
                    ),
                    //** End Delivery Estimate Options
                    //**Start: Cut Off Time & Ship Date Offset
                    'cutOffTime_shipDateOffset_freightquote_freight' => array(
                        'name' => __('Cut Off Time & Ship Date Offset ', 'eniture-freightquote-ltl'),
                        'type' => 'text',
                        'class' => 'hidden',
                        'desc' => $freightquote_cutt_off_time_package_required,
                        'id' => 'freightquote_freight_cutt_off_time_ship_date_offset'
                    ),
                    'orderCutoffTime_freightquote_freight' => array(
                        'name' => __('Order Cut Off Time ', 'eniture-freightquote-ltl'),
                        'type' => 'text',
                        'placeholder' => '-- : -- --',
                        'desc' => 'Enter the cut off time (e.g. 2.00) for the orders. Orders placed after this time will be quoted as shipping the next business day.',
                        'id' => 'freightquote_freight_order_cut_off_time',
                        'class' => $freightquote_disable_cutt_off_time_ship_date_offset,
                    ),
                    'shipmentOffsetDays_freightquote_freight' => array(
                        'name' => __('Fullfillment Offset Days ', 'eniture-freightquote-ltl'),
                        'type' => 'text',
                        'desc' => 'The number of days the ship date needs to be moved to allow the processing of the order.',
                        'placeholder' => 'Fullfillment Offset Days, e.g. 2',
                        'id' => 'freightquote_freight_shipment_offset_days',
                        'class' => $freightquote_disable_cutt_off_time_ship_date_offset,
                    ),
                    'all_shipment_days_freightquote' => array(
                        'name' => __("What days do you ship orders?", 'eniture-freightquote-ltl'),
                        'type' => 'checkbox',
                        'desc' => 'Select All',
                        'class' => "all_shipment_days_freightquote $freightquote_disable_cutt_off_time_ship_date_offset",
                        'id' => 'all_shipment_days_freightquote'
                    ),
                    'monday_shipment_day_freightquote' => array(
                        'name' => '',
                        'type' => 'checkbox',
                        'desc' => 'Monday',
                        'class' => "freightquote_shipment_day $freightquote_disable_cutt_off_time_ship_date_offset",
                        'id' => 'monday_shipment_day_freightquote'
                    ),
                    'tuesday_shipment_day_freightquote' => array(
                        'name' => '',
                        'type' => 'checkbox',
                        'desc' => 'Tuesday',
                        'class' => "freightquote_shipment_day $freightquote_disable_cutt_off_time_ship_date_offset",
                        'id' => 'tuesday_shipment_day_freightquote'
                    ),
                    'wednesday_shipment_day_freightquote' => array(
                        'name' => '',
                        'type' => 'checkbox',
                        'desc' => 'Wednesday',
                        'class' => "freightquote_shipment_day $freightquote_disable_cutt_off_time_ship_date_offset",
                        'id' => 'wednesday_shipment_day_freightquote'
                    ),
                    'thursday_shipment_day_freightquote' => array(
                        'name' => '',
                        'type' => 'checkbox',
                        'desc' => 'Thursday',
                        'class' => "freightquote_shipment_day $freightquote_disable_cutt_off_time_ship_date_offset",
                        'id' => 'thursday_shipment_day_freightquote'
                    ),
                    'friday_shipment_day_freightquote' => array(
                        'name' => '',
                        'type' => 'checkbox',
                        'desc' => 'Friday',
                        'class' => "freightquote_shipment_day $freightquote_disable_cutt_off_time_ship_date_offset",
                        'id' => 'friday_shipment_day_freightquote'
                    ),
                    'show_delivery_estimate_freightquote' => array(
                        'title' => '',
                        'name' => '',
                        'desc' => '',
                        'id' => 'freightquote_show_delivery_estimates',
                        'css' => '',
                        'default' => '',
                        'type' => 'title',
                    ),
                    //**End: Cut Off Time & Ship Date Offset
                    'Services_to_include_in_quoted_price_freightquote' => array(
                        'title' => '',
                        'name' => '',
                        'desc' => '',
                        'id' => 'woocommerce_freightquote_specific_Qurt_Price',
                        'css' => '',
                        'default' => '',
                        'type' => 'title'
                    ),
                    'residential_delivery_options_label' => array(
                        'name' => __('Residential Delivery', 'eniture-freightquote-ltl'),
                        'type' => 'text',
                        'class' => 'hidden',
                        'id' => 'residential_delivery_options_label'
                    ),
                    'residential_delivery_freightquote' => array(
                        'name' => '',
                        'type' => 'checkbox',
                        'desc' => 'Always quote as residential delivery', 
                        'id' => 'wc_settings_freightquote_residential_delivery',
                    ),
//                      Auto-detect residential addresses notification
                    'avaibility_auto_residential' => array(
                        'name' => '',
                        'type' => 'text',
                        'class' => 'hidden',
                        'desc' => "Click <a target='_blank' href='https://eniture.com/woocommerce-residential-address-detection/'>here</a> to add the Auto-detect residential addresses module. (<a target='_blank' href='https://eniture.com/woocommerce-residential-address-detection/#documentation'>Learn more</a>)", //junaid fix
                        'id' => 'avaibility_auto_residential'
                    ),
                    'liftgate_delivery_options_label' => array(
                        'name' => __('Lift Gate Delivery ', 'eniture-freightquote-ltl'),
                        'type' => 'text',
                        'class' => 'hidden',
                        'id' => 'liftgate_delivery_options_label'
                    ),
                    'freightquote_quests_liftgate_pickup' => array(
                        'name' => '',
                        'type' => 'checkbox',
                        'desc' => 'Always include lift gate pick up',
                        'id' => 'wc_settings_freightquote_lift_gate_pickup',
                        'class' => 'accessorial_service checkbox_fr_add ',
                    ),
                    'lift_gate_delivery_freightquote' => array(
                        'name' => '',
                        'type' => 'checkbox',
                        'desc' => 'Always quote lift gate delivery',
                        'id' => 'wc_settings_freightquote_lift_gate_delivery',
                        'class' => 'accessorial_service checkbox_fr_add ',
                    ),
                    'freightquote_quests_liftgate_delivery_as_option' => array(
                        'name' => '',
                        'type' => 'checkbox',
                        'desc' => __('Offer lift gate delivery as an option', 'eniture-freightquote-ltl'),
                        'id' => 'freightquote_quests_liftgate_delivery_as_option',
                        'class' => 'accessorial_service checkbox_fr_add ',
                    ),
                    'freightquote_quests_no_liftgate_delivery_as_option' => array(
                        'name' => '',
                        'type' => 'checkbox',
                        'desc' => __("Don't offer lift gate delivery as an option if an item is longer than (inches):", 'eniture-freightquote-ltl'),
                        'id' => 'freightquote_quests_no_liftgate_delivery_as_option',
                        'class' => 'accessorial_service checkbox_fr_add ',
                    ),
                    'freightquote_quests_no_liftgate_delivery_as_option_item_length' => array(
                        'name' => '',
                        'type' => 'text',
                        'id' => 'freightquote_quests_no_liftgate_delivery_as_option_item_length',
                        'class' => 'accessorial_service checkbox_fr_add ',
                    ),
//                      Use my liftgate notification
                    'avaibility_lift_gate' => array(
                        'name' => __('Always include lift gate delivery when a residential address is detected', 'eniture-freightquote-ltl'),
                        'type' => 'text',
                        'class' => 'hidden',
                        'desc' => "Click <a target='_blank' href='https://eniture.com/woocommerce-residential-address-detection/'>here</a> to add the Residential Address Detection module. (<a target='_blank' href='https://eniture.com/woocommerce-residential-address-detection/#documentation'>Learn more</a>)",
                        'id' => 'avaibility_lift_gate'
                    ),
                    // Handling Unit
                    'freight_quote_label_handling_unit' => array(
                        'name' => __('Handling Unit ', 'eniture-freightquote-ltl'),
                        'type' => 'text',
                        'class' => 'hidden',
                        'id' => 'freight_quote_label_handling_unit'
                    ),
                    'freight_quote_handling_weight' => array(
                        'name' => __('Weight of Handling Unit  ', 'eniture-freightquote-ltl'),
                        'type' => 'text',
                        'desc' => 'Enter in pounds the weight of your pallet, skid, crate or other type of handling unit you use. The amount entered will be added to shipment weight prior to requesting a quote.',
                        'id' => 'freight_quote_settings_handling_weight'
                    ),
                    'freight_quote_maximum_handling_weight' => array(
                        'name' => __('Maximum Weight per Handling Unit  ', 'eniture-freightquote-ltl'),
                        'type' => 'text',
                        'desc' => 'Enter in pounds the maximum weight that can be placed on the handling unit.',
                        'id' => 'freight_quote_maximum_handling_weight'
                    ),
                    'hand_free_mark_up_freightquote' => array(
                        'name' => __('Handling Fee / Markup ', 'eniture-freightquote-ltl'),
                        'type' => 'text',
                        'desc' => 'Amount excluding tax. Enter an amount, e.g 3.75, or a percentage, e.g, 5%. Leave blank to disable.',
                        'id' => 'wc_settings_freightquote_hand_free_mark_up'
                    ),
                    'shipping_logs_freightquote' => array(
                        'name' => __("Enable Logs  ", 'eniture-freightquote-ltl'),
                        'type' => 'checkbox',
                        'desc' => 'When checked, the Logs page will contain up to 25 of the most recent transactions.',
                        'id' => 'shipping_logs_freightquote'
                    ),
                    'allow_other_plugins' => array(
                        'name' => __('Show WooCommerce Shipping Options ', 'eniture-freightquote-ltl'),
                        'type' => 'select',
                        'default' => '3',
                        'desc' => __('Enabled options on WooCommerce Shipping page are included in quote results.', 'eniture-freightquote-ltl'),
                        'id' => 'wc_settings_freightquote_allow_other_plugins',
                        'options' => array(
                            'yes' => __('YES', 'eniture-freightquote-ltl'),
                            'no' => __('NO', 'eniture-freightquote-ltl'),
                        )
                    ),
                    'return_LTL_quotes_freightquote' => array(
                        'name' => __("Return LTL quotes when an order’s parcel shipment weight exceeds the weight threshold.", 'eniture-freightquote-ltl'),
                        'type' => 'checkbox',
                        'desc' => '<span class="description" >When checked, the LTL Freight Quote plugin will return quotes when the cart’s total weight exceeds the weight threshold, even if none of the products have settings to indicate that it will ship LTL. To increase the accuracy of the returned quote(s), all products should have accurate weights and dimensions.</span>',
                        'id' => 'en_plugins_return_LTL_quotes'
                    ),
                    // Weight threshold for LTL freight
                    'en_weight_threshold_lfq' => [
                        'name' => __('Weight threshold for LTL Freight Quotes  ', 'eniture-freightquote-ltl'),
                        'type' => 'text',
                        'default' => $weight_threshold,
                        'class' => $weight_threshold_class,
                        'id' => 'en_weight_threshold_lfq'
                    ],
                    'en_suppress_parcel_rates' => array(
                        'name' => '',
                        'type' => 'radio',
                        'default' => 'display_parcel_rates',
                        'options' => array(
                            'display_parcel_rates' => __("Continue to display parcel rates when the weight threshold is met.", 'eniture-freightquote-ltl'),
                            'suppress_parcel_rates' => __("Suppress parcel rates when the weight threshold is met.", 'eniture-freightquote-ltl'),
                        ),
                        'class' => 'en_suppress_parcel_rates',
                        'id' => 'en_suppress_parcel_rates',
                    ),
                    'section_end_quote' => array(
                        'type' => 'sectionend',
                        'id' => 'wc_settings_quote_section_end'
                    )
                );
                break;

            case 'section-3' :

                $this->ltl_warehouse();
                $settings = array();
                break;

            case 'section-4' :

                $this->ltl_user_guide();
                $settings = array();
                break;
            // fdo va
            case 'section-5' :
                $this->freightdesk_online_section();
                $settings = [];
                break;

            case 'section-6' :
                $this->validate_addresses_section();
                $settings = [];
                break;

            case 'en-logs' :
                $this->shipping_logs_section();
                $settings = [];
                break;

            default:

                echo '<div class="freightquote_ltl_connection_section_class">';
                $settings = $this->ltl_section_setting_tab();
                break;
        }

        $settings = apply_filters('en_woo_addons_settings', $settings, $section, freightquote_en_woo_plugin_freightquote_quests);
        // Standard Packaging
        $settings = apply_filters('en_woo_pallet_addons_settings', $settings, $section, freightquote_en_woo_plugin_freightquote_quests);
        $settings = $this->avaibility_addon($settings);
        $updated_settings = $settings;
        
        // Fix the position of always quote liftagte pickup settings
        if (is_plugin_active('residential-address-detection/residential-address-detection.php')) {
            $updated_settings = [];
            foreach ($settings as $key => $value) {
                if ($key == 'lift_gate_delivery_freightquote') {
                    $updated_settings['freightquote_quests_liftgate_pickup'] = $settings['freightquote_quests_liftgate_pickup'];
                    unset($settings['freightquote_quests_liftgate_pickup']);
                }
    
                $updated_settings[$key] = $value;
            }
        }

        return apply_filters('woocommerce-settings-freightquote-quotes', $updated_settings, $section);
    }

    /**
     * avaibility_addon
     * @param array type $settings
     * @return array type
     */
    function avaibility_addon($settings)
    {
        if (is_plugin_active('residential-address-detection/residential-address-detection.php')) {
            unset($settings['avaibility_lift_gate']);
            unset($settings['avaibility_auto_residential']);
        }

        return $settings;
    }

    /**
     * Output
     * @global $current_section
     */
    public function output()
    {

        global $current_section;
        $settings = $this->get_settings($current_section);
        if (!empty($settings)) {
            WC_Admin_Settings::output_fields($settings);
        }
    }

    /**
     * Save
     * @global $current_section
     */
    public function save()
    {

        global $current_section;
        if ($current_section != 'section-1') {
            $settings = $this->get_settings($current_section);
            // Cuttoff Time
            if (isset($_POST['freightquote_freight_order_cut_off_time']) && $_POST['freightquote_freight_order_cut_off_time'] != '') {
                $time_24_format = $this->freightquote_get_time_in_24_hours(sanitize_text_field( wp_unslash($_POST['freightquote_freight_order_cut_off_time'])));
                $_POST['freightquote_freight_order_cut_off_time'] = $time_24_format;
            }

            $error_exist = false;
            if ($current_section == 'section-2') {
                if(get_option('freightquote_api_endpoint') == 'freightquote_new_api'){
                    if(empty($_POST['en_freight_quote_truckload_weight_threshold_chr'])){
                        $error_exist = true;
                        WC_Admin_Settings::add_error( esc_html__( 'Truckload weight threshold is required', 'eniture-freightquote-ltl' ) );
                    }else if($_POST['en_freight_quote_truckload_weight_threshold_chr'] < 7500){
                        $error_exist = true;
                        WC_Admin_Settings::add_error( esc_html__( 'Truckload weight threshold should be greater than or equal to 7500', 'eniture-freightquote-ltl' ) );
                    }
                }else{
                    if(empty($_POST['en_freight_quote_truckload_weight_threshold'])){
                        $error_exist = true;
                        WC_Admin_Settings::add_error( esc_html__( 'Truckload weight threshold is required', 'eniture-freightquote-ltl' ) );
                    }else if($_POST['en_freight_quote_truckload_weight_threshold'] < 7000){
                        $error_exist = true;
                        WC_Admin_Settings::add_error( esc_html__( 'Truckload weight threshold should be greater than or equal to 7000', 'eniture-freightquote-ltl' ) );
                    }
                }
            }

            if(!$error_exist){
                WC_Admin_Settings::save_fields($settings);
            }
            
        }
    }

    /**
     * Cuttoff Time
     * @param $timeStr
     * @return false|string
     */
    public function freightquote_get_time_in_24_hours($timeStr)
    {
        $cutOffTime = explode(' ', $timeStr);
        $hours = $cutOffTime[0];
        $separator = $cutOffTime[1];
        $minutes = $cutOffTime[2];
        $meridiem = $cutOffTime[3];
        $cutOffTime = "{$hours}{$separator}{$minutes} $meridiem";
        return gmdate("H:i", strtotime($cutOffTime));
    }
    // fdo va
    /**
     * FreightDesk Online section
     */
    public function freightdesk_online_section()
    {
        include_once plugin_dir_path(__FILE__) . 'fdo/freightdesk-online-section.php';
    }

    /**
     * Validate Addresses Section
     */
    public function validate_addresses_section()
    {
        include_once plugin_dir_path(__FILE__) . 'fdo/validate-addresses-section.php';
    }

    /**
     * Shipping Logs Section
    */
    public function shipping_logs_section()
    {
        include_once plugin_dir_path(__FILE__) . 'logs/en-logs.php';
    }

}

return new Freightquote_WC_ltl_Settings_tabs();
