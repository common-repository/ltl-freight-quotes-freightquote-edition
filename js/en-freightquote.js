jQuery(window).on('load', function () {
    var saved_mehod_value = en_freight_quote_admin_script.wc_settings_freightquote_rate_method;
    if (saved_mehod_value == 'Cheapest') {
        jQuery(".freightquote_delivery_estimate").removeAttr('style');
        jQuery(".freightquote_Number_of_label_as").removeAttr('style');
        jQuery(".freightquote_Number_of_options_class").removeAttr('style');

        jQuery(".en_freight_quote_truckload_flatbed_label_tr_class").removeAttr('style');
        jQuery(".en_freight_quote_truckload_refrigerated_label_tr_class").removeAttr('style');
        jQuery(".en_freight_quote_truckload_van_label_tr_class").removeAttr('style');

        jQuery("#wc_settings_freightquote_Number_of_options").closest('tr').addClass("freightquote_Number_of_options_class");
        jQuery("#wc_settings_freightquote_Number_of_options").closest('tr').css("display", "none");
        jQuery("#wc_settings_freightquote_label_as").closest('tr').addClass("freightquote_Number_of_label_as");
        jQuery("#wc_settings_freightquote_delivery_estimate").closest('tr').addClass("freightquote_delivery_estimate");
        jQuery("#wc_settings_freightquote_rate_method").closest('tr').addClass("freightquote_rate_mehod");

        jQuery('.freightquote_rate_mehod td p').html('Displays only the cheapest returned Rate.');
        jQuery('.freightquote_Number_of_label_as td p').html('What the user sees during checkout, e.g. "Freight". Leave blank to display the carrier name.');
    }
    if (saved_mehod_value == 'cheapest_options') {

        jQuery(".freightquote_delivery_estimate").removeAttr('style');
        jQuery(".freightquote_Number_of_label_as").removeAttr('style');
        jQuery(".freightquote_Number_of_options_class").removeAttr('style');

        jQuery(".en_freight_quote_truckload_flatbed_label_tr_class").removeAttr('style');
        jQuery(".en_freight_quote_truckload_refrigerated_label_tr_class").removeAttr('style');
        jQuery(".en_freight_quote_truckload_van_label_tr_class").removeAttr('style');

        jQuery("#wc_settings_freightquote_delivery_estimate").closest('tr').addClass("freightquote_delivery_estimate");
        jQuery("#wc_settings_freightquote_label_as").closest('tr').addClass("freightquote_Number_of_label_as");
        jQuery("#wc_settings_freightquote_label_as").closest('tr').css("display", "none");
        jQuery("#wc_settings_freightquote_Number_of_options").closest('tr').addClass("freightquote_Number_of_options_class");
        jQuery("#wc_settings_freightquote_rate_method").closest('tr').addClass("freightquote_rate_mehod");

        jQuery('.freightquote_rate_mehod td p').html('Displays a list of a specified number of least expensive options.');
        jQuery('.freightquote_Number_of_options_class td p').html('Number of options to display in the shopping cart.');
    }
    if (saved_mehod_value == 'average_rate') {

        jQuery(".freightquote_delivery_estimate").removeAttr('style');
        jQuery(".freightquote_Number_of_label_as").removeAttr('style');
        jQuery(".freightquote_Number_of_options_class").removeAttr('style');

        jQuery("#wc_settings_freightquote_delivery_estimate").closest('tr').addClass("freightquote_delivery_estimate");
        jQuery("#wc_settings_freightquote_delivery_estimate").closest('tr').css("display", "none");
        jQuery("#wc_settings_freightquote_label_as").closest('tr').addClass("freightquote_Number_of_label_as");
        jQuery("#wc_settings_freightquote_Number_of_options").closest('tr').addClass("freightquote_Number_of_options_class");
        jQuery("#wc_settings_freightquote_rate_method").closest('tr').addClass("freightquote_rate_mehod");

        jQuery('.freightquote_rate_mehod td p').html('Displays a single rate based on an average of a specified number of least expensive options.');
        jQuery('.freightquote_Number_of_options_class td p').html('Number of options to include in the calculation of the average.');
        jQuery('.freightquote_Number_of_label_as td p').html('What the user sees during checkout, e.g. "Freight". If left blank will default to "Freight".');

        jQuery("#en_freight_quote_truckload_flatbed_label").closest('tr').addClass("en_freight_quote_truckload_flatbed_label_tr_class");
        jQuery("#en_freight_quote_truckload_flatbed_label").closest('tr').css("display", "none");
        jQuery("#en_freight_quote_truckload_refrigerated_label").closest('tr').addClass("en_freight_quote_truckload_refrigerated_label_tr_class");
        jQuery("#en_freight_quote_truckload_refrigerated_label").closest('tr').css("display", "none");
        jQuery("#en_freight_quote_truckload_van_label").closest('tr').addClass("en_freight_quote_truckload_van_label_tr_class");
        jQuery("#en_freight_quote_truckload_van_label").closest('tr').css("display", "none");

    }

    jQuery("#en_wd_origin_markup, #en_wd_dropship_markup, ._en_product_markup").keydown(function (e) {
        const val = jQuery(this).val();

        if ((e.keyCode === 109 || e.keyCode === 189) && (val.length > 0)) return false;
        if (e.keyCode === 53) if (e.shiftKey) if (val.length == 0) return false; 
        
        if ((val.indexOf('.') != -1) && (val.substring(val.indexOf('.'), val.indexOf('.').length).length > 2)) {
            if (e.keyCode !== 8 && e.keyCode !== 46) { //exception
                e.preventDefault();
            }
        }

        // Allow: backspace, delete, tab, escape, enter and .
        if (jQuery.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 190, 53, 189]) !== -1 ||
            // Allow: Ctrl+A, Command+A
            (e.keyCode === 65 && (e.ctrlKey === true || e.metaKey === true)) ||
            // Allow: home, end, left, right, down, up
            (e.keyCode >= 35 && e.keyCode <= 40)) {
            // let it happen, don't do anything
            return;
        }

        // Ensure that it is a number and stop the keypress
        if (val.length > 7 || (e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
            e.preventDefault();
        }
    }); 
    
    // Origin and product level markup fields validations
    jQuery("#en_wd_origin_markup, #en_wd_dropship_markup, ._en_product_markup").keyup(function (e) {

        var val = jQuery(this).val();

        if (val.length && val.includes('%')) {
            jQuery(this).val(val.substring(0, val.indexOf('%') + 1));
        }

        if (val.split('.').length - 1 > 1) {
            var newval = val.substring(0, val.length - 1);
            var countDots = newval.substring(newval.indexOf('.') + 1).length;
            newval = newval.substring(0, val.length - countDots - 1);
            jQuery(this).val(newval);
        }

        if (val.split('%').length - 1 > 1) {
            var newval = val.substring(0, val.length - 1);
            var countPercentages = newval.substring(newval.indexOf('%') + 1).length;
            newval = newval.substring(0, val.length - countPercentages - 1);
            jQuery(this).val(newval);
        }

        if (val.split('-').length - 1 > 1) {
            var newval = val.substring(0, val.length - 1);
            var countPercentages = newval.substring(newval.indexOf('-') + 1).length;
            newval = newval.substring(0, val.length - countPercentages - 1);
            jQuery(this).val(newval);
        }
    });

    jQuery("#en_wd_origin_markup, #en_wd_dropship_markup, ._en_product_markup").bind("cut copy paste",function(e) {
        e.preventDefault();
    });
    
    jQuery("#en_wd_origin_markup, #en_wd_dropship_markup, ._en_product_markup").keypress(function (e) {
        if (!String.fromCharCode(e.keyCode).match(/^[-0-9\d\.%\s]+$/i)) return false;
    });   
});
// Weight threshold for LTL freight
if (typeof en_weight_threshold_limit != 'function') {
    function en_weight_threshold_limit() {
        // Weight threshold for LTL freight
        jQuery("#en_weight_threshold_lfq").keypress(function (e) {
            if (String.fromCharCode(e.keyCode).match(/[^0-9]/g) || !jQuery("#en_weight_threshold_lfq").val().match(/^\d{0,3}$/)) return false;
        });

        jQuery('#en_plugins_return_LTL_quotes').on('change', function () {
            if (jQuery('#en_plugins_return_LTL_quotes').prop("checked")) {
                jQuery('tr.en_weight_threshold_lfq').css('display', 'contents');
                jQuery('tr.en_suppress_parcel_rates').css('display', '');
            } else {
                jQuery('tr.en_weight_threshold_lfq, tr.en_suppress_parcel_rates').css('display', 'none');
            }
        });

        jQuery("#en_plugins_return_LTL_quotes").closest('tr').addClass("en_plugins_return_LTL_quotes_tr");
        // Weight threshold for LTL freight
        var weight_threshold_class = jQuery("#en_weight_threshold_lfq").attr("class");
        jQuery("#en_weight_threshold_lfq").closest('tr').addClass("en_weight_threshold_lfq " + weight_threshold_class);

        // Weight threshold for LTL freight is empty
        if (jQuery('#en_weight_threshold_lfq').length && !jQuery('#en_weight_threshold_lfq').val().length > 0) {
            jQuery('#en_weight_threshold_lfq').val(150);
        }

        // Suppress parcel rates when thresold is met
        jQuery(".en_suppress_parcel_rates").closest('tr').addClass("en_suppress_parcel_rates");
        !jQuery("#en_plugins_return_LTL_quotes").is(":checked") ? jQuery('tr.en_suppress_parcel_rates').css('display', 'none') : jQuery('tr.en_suppress_parcel_rates').css('display', '');
    }
}
jQuery(document).ready(function () {

    // Weight threshold for LTL freight
    en_weight_threshold_limit();

    var en_chr_truckload_service = jQuery("input[type='radio'][name='en_chr_truckload_service']").attr("class");
    jQuery("input[type='radio'][name='en_chr_truckload_service']").closest('tr').addClass(en_chr_truckload_service);
    var en_chr_truckload_label = jQuery("#en_chr_truckload_label").attr("class");
    jQuery("#en_chr_truckload_label").closest('tr').addClass(en_chr_truckload_label);
    var en_freight_quote_truckload_flatbed_label = jQuery("#en_freight_quote_truckload_flatbed_label").attr("class");
    jQuery("#en_freight_quote_truckload_flatbed_label").closest('tr').addClass(en_freight_quote_truckload_flatbed_label);
    var en_freight_quote_truckload_refrigerated_label = jQuery("#en_freight_quote_truckload_refrigerated_label").attr("class");
    jQuery("#en_freight_quote_truckload_refrigerated_label").closest('tr').addClass(en_freight_quote_truckload_refrigerated_label);
    var en_freight_quote_truckload_van_label = jQuery("#en_freight_quote_truckload_van_label").attr("class");
    jQuery("#en_freight_quote_truckload_van_label").closest('tr').addClass(en_freight_quote_truckload_van_label);
    var en_freight_quote_truckload_weight_threshold = jQuery("#en_freight_quote_truckload_weight_threshold").attr("class");
    jQuery("#en_freight_quote_truckload_weight_threshold").closest('tr').addClass(en_freight_quote_truckload_weight_threshold);
    var en_freight_quote_truckload_weight_threshold_chr = jQuery("#en_freight_quote_truckload_weight_threshold_chr").attr("class");
    jQuery("#en_freight_quote_truckload_weight_threshold_chr").closest('tr').addClass(en_freight_quote_truckload_weight_threshold_chr);

    var en_freight_quote_truckload_weight_threshold = jQuery("#en_freight_quote_truckload_weight_threshold").attr("class");
    jQuery("#en_freight_quote_truckload_weight_threshold").closest('tr').addClass(en_freight_quote_truckload_weight_threshold);

    var en_freight_quote_ltl_above_threshold = jQuery("#en_freight_quote_ltl_above_threshold").attr("class");
    jQuery("#en_freight_quote_ltl_above_threshold").closest('tr').addClass(en_freight_quote_ltl_above_threshold);

    var en_freight_quote_label_truckload_settings = jQuery("#en_freight_quote_label_truckload_settings").attr("class");
    
    if(typeof en_freight_quote_label_truckload_settings != 'undefined'){
        en_freight_quote_label_truckload_settings = en_freight_quote_label_truckload_settings.replace('hidden', '');
        jQuery("#en_freight_quote_label_truckload_settings").closest('tr').addClass(en_freight_quote_label_truckload_settings);
    
    }
    
    if (typeof freightquote_ltl_connection_section_api_endpoint == 'function') {
        freightquote_ltl_connection_section_api_endpoint();
    }

    jQuery("#order_shipping_line_items .shipping .view .display_meta").css('display', 'none');

    // JS for edit product nested fields
    jQuery("._nestedMaterials").closest('p').addClass("_nestedMaterials_tr");
    jQuery("._nestedPercentage").closest('p').addClass("_nestedPercentage_tr");
    jQuery("._maxNestedItems").closest('p').addClass("_maxNestedItems_tr");
    jQuery("._nestedDimension").closest('p').addClass("_nestedDimension_tr");
    jQuery("._nestedStakingProperty").closest('p').addClass("_nestedStakingProperty_tr");
// Cuttoff Time
    jQuery("#freightquote_freight_shipment_offset_days").closest('tr').addClass("freightquote_freight_shipment_offset_days_tr");
    jQuery('#freightquote_freight_shipment_offset_days').attr('maxlength', '1');
    jQuery("#all_shipment_days_freightquote").closest('tr').addClass("all_shipment_days_freightquote_tr");
    jQuery(".freightquote_shipment_day").closest('tr').addClass("freightquote_shipment_day_tr");
    jQuery("#freightquote_freight_order_cut_off_time").closest('tr').addClass("freightquote_freight_cutt_off_time_ship_date_offset");
    var freightquote_current_time = en_freight_quote_admin_script.freightquote_freight_order_cutoff_time;
    if (freightquote_current_time == '') {

        jQuery('#freightquote_freight_order_cut_off_time').wickedpicker({
            now: '',
            title: 'Cut Off Time',
        });
    } else {
        jQuery('#freightquote_freight_order_cut_off_time').wickedpicker({

            now: freightquote_current_time,
            title: 'Cut Off Time'
        });
    }

    var delivery_estimate_val = jQuery('input[name=freightquote_delivery_estimates]:checked').val();
    if (delivery_estimate_val == 'dont_show_estimates') {
        jQuery("#freightquote_freight_order_cut_off_time").prop('disabled', true);
        jQuery("#freightquote_freight_shipment_offset_days").prop('disabled', true);
        jQuery("#freightquote_freight_shipment_offset_days").css("cursor", "not-allowed");
        jQuery("#freightquote_freight_order_cut_off_time").css("cursor", "not-allowed");
        jQuery('.all_shipment_days_freightquote, .freightquote_shipment_day').prop('disabled', true);
        jQuery('.all_shipment_days_freightquote, .freightquote_shipment_day').css('cursor', 'not-allowed');
    } else {
        jQuery("#freightquote_freight_order_cut_off_time").prop('disabled', false);
        jQuery("#freightquote_freight_shipment_offset_days").prop('disabled', false);
        // jQuery("#freightquote_freight_order_cut_off_time").css("cursor", "auto");
        jQuery("#freightquote_freight_order_cut_off_time").css("cursor", "");
        jQuery('.all_shipment_days_freightquote, .freightquote_shipment_day').prop('disabled', false);
        jQuery('.all_shipment_days_freightquote, .freightquote_shipment_day').css('cursor', 'auto');
    }

    jQuery("input[name=freightquote_delivery_estimates]").change(function () {
        var delivery_estimate_val = jQuery('input[name=freightquote_delivery_estimates]:checked').val();
        if (delivery_estimate_val == 'dont_show_estimates') {
            jQuery("#freightquote_freight_order_cut_off_time").prop('disabled', true);
            jQuery("#freightquote_freight_shipment_offset_days").prop('disabled', true);
            jQuery("#freightquote_freight_order_cut_off_time").css("cursor", "not-allowed");
            jQuery("#freightquote_freight_shipment_offset_days").css("cursor", "not-allowed");
            jQuery('.all_shipment_days_freightquote, .freightquote_shipment_day').prop('disabled', true);
            jQuery('.all_shipment_days_freightquote, .freightquote_shipment_day').css('cursor', 'not-allowed');
        } else {
            jQuery("#freightquote_freight_order_cut_off_time").prop('disabled', false);
            jQuery("#freightquote_freight_shipment_offset_days").prop('disabled', false);
            jQuery("#freightquote_freight_order_cut_off_time").css("cursor", "auto");
            jQuery("#freightquote_freight_shipment_offset_days").css("cursor", "auto");
            jQuery('.all_shipment_days_freightquote, .freightquote_shipment_day').prop('disabled', false);
            jQuery('.all_shipment_days_freightquote, .freightquote_shipment_day').css('cursor', 'auto');
        }
    });

    /*
     * Uncheck Week days Select All Checkbox
     */
    jQuery(".freightquote_shipment_day").on('change load', function () {

        var checkboxes = jQuery('.freightquote_shipment_day:checked').length;
        var un_checkboxes = jQuery('.freightquote_shipment_day').length;
        if (checkboxes === un_checkboxes) {
            jQuery('.all_shipment_days_freightquote').prop('checked', true);
        } else {
            jQuery('.all_shipment_days_freightquote').prop('checked', false);
        }
    });

    /*
     * Select All Shipment Week days
     */

    var all_int_checkboxes = jQuery('.all_shipment_days_freightquote');
    if (all_int_checkboxes.length === all_int_checkboxes.filter(":checked").length) {
        jQuery('.all_shipment_days_freightquote').prop('checked', true);
    }

    jQuery(".all_shipment_days_freightquote").change(function () {
        if (this.checked) {
            jQuery(".freightquote_shipment_day").each(function () {
                this.checked = true;
            });
        } else {
            jQuery(".freightquote_shipment_day").each(function () {
                this.checked = false;
            });
        }
    });


    //** End: Order Cut Off Time
    if (!jQuery('._nestedMaterials').is(":checked")) {
        jQuery('._nestedPercentage_tr').hide();
        jQuery('._nestedDimension_tr').hide();
        jQuery('._maxNestedItems_tr').hide();
        jQuery('._nestedDimension_tr').hide();
        jQuery('._nestedStakingProperty_tr').hide();
    } else {
        jQuery('._nestedPercentage_tr').show();
        jQuery('._nestedDimension_tr').show();
        jQuery('._maxNestedItems_tr').show();
        jQuery('._nestedDimension_tr').show();
        jQuery('._nestedStakingProperty_tr').show();
    }

    jQuery("._nestedPercentage").attr('min', '0');
    jQuery("._maxNestedItems").attr('min', '0');
    jQuery("._nestedPercentage").attr('max', '100');
    jQuery("._maxNestedItems").attr('max', '100');
    jQuery("._nestedPercentage").attr('maxlength', '3');
    jQuery("._maxNestedItems").attr('maxlength', '3');

    if (jQuery("._nestedPercentage").val() == '') {
        jQuery("._nestedPercentage").val(0);
    }

    jQuery("._nestedPercentage").keydown(function (eve) {
        freight_quote_lfq_stop_special_characters(eve);
        var nestedPercentage = jQuery('._nestedPercentage').val();
        if (nestedPercentage.length == 2) {
            var newValue = nestedPercentage + '' + eve.key;
            if (newValue > 100) {
                return false;
            }
        }
    });

    jQuery("._nestedDimension").keydown(function (eve) {
        freight_quote_lfq_stop_special_characters(eve);
        var nestedDimension = jQuery('._nestedDimension').val();
        if (nestedDimension.length == 2) {
            var newValue1 = nestedDimension + '' + eve.key;
            if (newValue1 > 100) {
                return false;
            }
        }
    });

    jQuery("._maxNestedItems").keydown(function (eve) {
        freight_quote_lfq_stop_special_characters(eve);
    });

    jQuery("._nestedMaterials").change(function () {
        if (!jQuery('._nestedMaterials').is(":checked")) {
            jQuery('._nestedPercentage_tr').hide();
            jQuery('._nestedDimension_tr').hide();
            jQuery('._maxNestedItems_tr').hide();
            jQuery('._nestedDimension_tr').hide();
            jQuery('._nestedStakingProperty_tr').hide();
        } else {
            jQuery('._nestedPercentage_tr').show();
            jQuery('._nestedDimension_tr').show();
            jQuery('._maxNestedItems_tr').show();
            jQuery('._nestedDimension_tr').show();
            jQuery('._nestedStakingProperty_tr').show();
        }
    });

    // Start handling unit
    jQuery("#freight_quote_settings_handling_weight").closest('tr').addClass("freight_quote_settings_handling_weight_tr");
    jQuery("#freight_quote_maximum_handling_weight").closest('tr').addClass("freight_quote_maximum_handling_weight_tr");
    jQuery('#freight_quote_settings_handling_weight, #freight_quote_maximum_handling_weight').attr('maxlength', '7');

    jQuery("#en_freight_quote_truckload_weight_threshold").closest('tr').addClass("en_freight_quote_truckload_weight_threshold_tr");
    jQuery("#en_freight_quote_truckload_weight_threshold_chr").closest('tr').addClass("en_freight_quote_truckload_weight_threshold_chr_tr");
    jQuery("#en_freight_quote_ltl_above_threshold").closest('tr').addClass("en_freight_quote_ltl_above_threshold_tr");
    jQuery("#en_freight_quote_truckload_flatbed_label").closest('tr').addClass("en_freight_quote_truckload_flatbed_label_tr");
    jQuery("#en_freight_quote_truckload_refrigerated_label").closest('tr').addClass("en_freight_quote_truckload_refrigerated_label_tr");
    jQuery("#en_freight_quote_truckload_van_label").closest('tr').addClass("en_freight_quote_truckload_van_label_tr");
    jQuery("#en_chr_truckload_label").closest('tr').addClass("en_chr_truckload_label_tr");

    jQuery("#wc_settings_freightquote_rate_method").closest('tr').addClass("rating_method_freightquote_tr");
    jQuery("#wc_settings_freightquote_Number_of_options").closest('tr').addClass("number_of_options_freightquote_tr");
    jQuery("#wc_settings_freightquote_label_as").closest('tr').addClass("label_as_freightquote_tr");

    /**
     *Weight of Handling Unit field validation
     */
    jQuery("#freight_quote_settings_handling_weight,#freight_quote_maximum_handling_weight, #en_freight_quote_truckload_weight_threshold, #en_freight_quote_truckload_weight_threshold_chr").keydown(function (e) {
        // Allow: backspace, delete, tab, escape and enter
        if (jQuery.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 190, 53, 189]) !== -1 ||
            // Allow: Ctrl+A, Command+A
            (e.keyCode === 65 && (e.ctrlKey === true || e.metaKey === true)) ||
            // Allow: home, end, left, right, down, up
            (e.keyCode >= 35 && e.keyCode <= 40) ||
            (e.target.id == 'freight_quote_settings_handling_weight' && (e.keyCode == 109)) ||
            (e.target.id == 'freight_quote_settings_handling_weight' && (e.keyCode == 189))) {
            // let it happen, don't do anything
            return;
        }
        // Ensure that it is a number and stop the keypress
        if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
            e.preventDefault();
        }

        if ((jQuery(this).val().indexOf('.') != -1) && (jQuery(this).val().substring(jQuery(this).val().indexOf('.'), jQuery(this).val().indexOf('.').length).length > 3)) {
            if (e.keyCode !== 8 && e.keyCode !== 46) { //exception
                e.preventDefault();
            }
        }

    });
    // End handling unit

    jQuery("#wc_settings_freightquote_residential_delivery").closest('tr').addClass("wc_settings_freightquote_residential_delivery");
    jQuery("#avaibility_auto_residential").closest('tr').addClass("avaibility_auto_residential");
    jQuery("#avaibility_lift_gate").closest('tr').addClass("avaibility_lift_gate");
    jQuery("#wc_settings_freightquote_lift_gate_pickup").closest('tr').addClass("wc_settings_freightquote_lift_gate_pickup");
    jQuery("#wc_settings_freightquote_lift_gate_delivery").closest('tr').addClass("wc_settings_freightquote_lift_gate_delivery");
    jQuery("#freightquote_quests_liftgate_delivery_as_option").closest('tr').addClass("freightquote_quests_liftgate_delivery_as_option");
    jQuery("#freightquote_quests_no_liftgate_delivery_as_option").closest('tr').addClass("freightquote_quests_no_liftgate_delivery_as_option");
    jQuery("#freightquote_quests_no_liftgate_delivery_as_option_item_length").closest('tr').addClass("freightquote_quests_no_liftgate_delivery_as_option_item_length");
    jQuery("#residential_delivery_options_label").closest('tr').addClass("residential_delivery_options_label");
    jQuery("#liftgate_delivery_options_label").closest('tr').addClass("liftgate_delivery_options_label");

    jQuery('#wc_settings_freightquote_residential_delivery').closest('tr').addClass('custom_sub_menu_tr');
    jQuery('#wc_settings_freightquote_residential_delivery').closest('td').addClass('custom_sub_menu_td');

    jQuery('#wc_settings_freightquote_lift_gate_delivery').closest('tr').addClass('custom_sub_menu_tr');
    jQuery('#wc_settings_freightquote_lift_gate_delivery').closest('td').addClass('custom_sub_menu_td');

    jQuery('#freightquote_quests_liftgate_delivery_as_option').closest('tr').addClass('custom_sub_menu_tr');
    jQuery('#freightquote_quests_liftgate_delivery_as_option').closest('td').addClass('custom_sub_menu_td');

    jQuery('#freightquote_quests_no_liftgate_delivery_as_option').closest('tr').addClass('custom_sub_menu_tr');
    jQuery('#freightquote_quests_no_liftgate_delivery_as_option').closest('td').addClass('custom_sub_menu_td');

    /**
     * Offer lift gate delivery as an option and Always include residential delivery fee
     * @returns {undefined}
     */

    jQuery(".checkbox_fr_add").on("click", function () {
        var id = jQuery(this).attr("id");
        if (id == "wc_settings_freightquote_lift_gate_delivery") {
            jQuery("#freightquote_quests_liftgate_delivery_as_option").prop({checked: false});
            jQuery("#en_woo_addons_liftgate_with_auto_residential").prop({checked: false});

        } else if (id == "freightquote_quests_liftgate_delivery_as_option" ||
            id == "en_woo_addons_liftgate_with_auto_residential") {
            jQuery("#wc_settings_freightquote_lift_gate_delivery").prop({checked: false});
        }
    });

    var url = getUrlVarsFreightQuoteLTL()["tab"];
    if (url === 'freightquote_quests') {
        jQuery('#footer-left').attr('id', 'wc-footer-left');
    }

    /*
     * Restrict Handling Fee with 8 digits limit
     */

    jQuery("#wc_settings_freightquote_hand_free_mark_up").attr('maxlength', '8');

    jQuery(".freightquote_ltl_connection_section_class .button-primary, .freightquote_ltl_connection_section_class .is-primary").click(function () {
        var input = freightquote_validateInput('.freightquote_ltl_connection_section_class');
        if (input === false) {
            return false;
        }
    });
    jQuery(".freightquote_ltl_connection_section_class .woocommerce-save-button").before('<a href="javascript:void(0)" class="button-primary freightquote_ltl_test_connection">Test Connection</a>');
    jQuery('.freightquote_ltl_test_connection').click(function (e) {
        var input = freightquote_validateInput('.freightquote_ltl_connection_section_class');
        if (input === false) {
            return false;
        }

        var postForm = {
            'freightquote_username': jQuery('#wc_settings_freigtquote_freight_username').val(),
            'freightquote_password': jQuery('#wc_settings_freigtquote_freight_password').val(),
            'freightquote_licence_key': jQuery('#wc_settings_freightquote_license_key').val(),
            // New API Endpoint
            'freightquote_customer_code': jQuery('#freightquote_customer_code').val(),
            'freightquote_api_endpoint': jQuery('#freightquote_api_endpoint').val(),
            'action': 'freightquote_ltl_validate_keys'
        };
        jQuery.ajax({
            type: 'POST',
            url: ajaxurl,
            data: postForm,
            dataType: 'json',
            beforeSend: function () {
                jQuery(".freightquote_ltl_test_connection").css("color", "#fff");
                jQuery(".freightquote_ltl_connection_section_class .button-primary, .freightquote_ltl_connection_section_class .is-primary").css("cursor", "pointer");
                jQuery('#wc_settings_freigtquote_freight_username').css('background', 'rgba(255, 255, 255, 1) url("' + en_freight_quote_admin_script.plugins_url + '/ltl-freight-quotes-freightquote-edition/warehouse-dropship/wild/assets/images/processing.gif") no-repeat scroll 50% 50%');
                jQuery('#wc_settings_freigtquote_freight_password').css('background', 'rgba(255, 255, 255, 1) url("' + en_freight_quote_admin_script.plugins_url + '/ltl-freight-quotes-freightquote-edition/warehouse-dropship/wild/assets/images/processing.gif") no-repeat scroll 50% 50%');
                jQuery('#wc_settings_freightquote_license_key').css('background', 'rgba(255, 255, 255, 1) url("' + en_freight_quote_admin_script.plugins_url + '/ltl-freight-quotes-freightquote-edition/warehouse-dropship/wild/assets/images/processing.gif") no-repeat scroll 50% 50%');

                // New API Endpoint
                jQuery('#freightquote_customer_code').css('background', 'rgba(255, 255, 255, 1) url("' + en_freight_quote_admin_script.plugins_url + '/ltl-freight-quotes-freightquote-edition/warehouse-dropship/wild/assets/images/processing.gif") no-repeat scroll 50% 50%');
            },
            success: function (data) {

                if (data.message == 'success') {
                    jQuery(".updated").hide();
                    jQuery('#wc_settings_freigtquote_freight_username').css('background', '#fff');
                    jQuery('#wc_settings_freigtquote_freight_password').css('background', '#fff');
                    jQuery('#wc_settings_freightquote_license_key').css('background', '#fff');
                    // New API Endpoint
                    jQuery('#freightquote_customer_code').css('background', '#fff');
                    jQuery(".class_success_message").remove();
                    jQuery(".class_error_message").remove();
                    jQuery(".freightquote_ltl_connection_section_class .button-primary, .freightquote_ltl_connection_section_class .is-primary").attr("disabled", false);
                    jQuery('.warning-msg-ltl').before('<p class="class_success_message" ><strong>Success!</strong> The test resulted in a successful connection. </p>');
                } else {
                    jQuery(".updated").hide();
                    jQuery(".class_error_message").remove();
                    jQuery('#wc_settings_freigtquote_freight_username').css('background', '#fff');
                    jQuery('#wc_settings_freigtquote_freight_password').css('background', '#fff');
                    jQuery('#wc_settings_freightquote_license_key').css('background', '#fff');
                    // New API Endpoint
                    jQuery('#freightquote_customer_code').css('background', '#fff');
                    jQuery(".class_success_message").remove();
                    jQuery(".freightquote_ltl_connection_section_class .button-primary, .freightquote_ltl_connection_section_class .is-primary").attr("disabled", false);
                    if (data.message != 'failure') {
                        jQuery('.warning-msg-ltl').before('<p class="class_error_message" > <strong>Error!</strong> ' + data.message + '</p>');
                    } else if (data.message == 'failure') {
                        jQuery('.warning-msg-ltl').before('<p class="class_error_message" > <strong>Error!</strong> Confirm your credentials and try again.</p>');
                    } else {
                        jQuery('.warning-msg-ltl').before('<p class="class_error_message" > <strong>Error!</strong> The credentials entered did not result in a successful test. Confirm your credentials and try again.</p>');
                    }
                }
            }
        });
        e.preventDefault();
    });
    // fdo va
    jQuery('#fd_online_id_freightquote').click(function (e) {
        var postForm = {
            'action': 'freightquote_fd',
            'company_id': jQuery('#freightdesk_online_id').val(),
            'disconnect': jQuery('#fd_online_id_freightquote').attr("data")
        }
        var id_lenght = jQuery('#freightdesk_online_id').val();
        var disc_data = jQuery('#fd_online_id_freightquote').attr("data");
        if(typeof (id_lenght) != "undefined" && id_lenght.length < 1) {
            jQuery(".class_error_message").remove();
            jQuery('.user_guide_fdo').before('<div class="notice notice-error class_error_message"><p><strong>Error!</strong> FreightDesk Online ID is Required.</p></div>');
            return;
        }
        jQuery.ajax({
            type: "POST",
            url: ajaxurl,
            data: postForm,
            beforeSend: function () {
                jQuery('#freightdesk_online_id').css('background', 'rgba(255, 255, 255, 1) url("' + en_freight_quote_admin_script.plugins_url + '/ltl-freight-quotes-freightquote-edition/warehouse-dropship/wild/assets/images/processing.gif") no-repeat scroll 50% 50%');
            },
            success: function (data_response) {
                if(typeof (data_response) == "undefined"){
                    return;
                }
                var fd_data = JSON.parse(data_response);
                jQuery('#freightdesk_online_id').css('background', '#fff');
                jQuery(".class_error_message").remove();
                if((typeof (fd_data.is_valid) != 'undefined' && fd_data.is_valid == false) || (typeof (fd_data.status) != 'undefined' && fd_data.is_valid == 'ERROR')) {
                    jQuery('.user_guide_fdo').before('<div class="notice notice-error class_error_message"><p><strong>Error! ' + fd_data.message + '</strong></p></div>');
                }else if(typeof (fd_data.status) != 'undefined' && fd_data.status == 'SUCCESS') {
                    jQuery('.user_guide_fdo').before('<div class="notice notice-success class_success_message"><p><strong>Success! ' + fd_data.message + '</strong></p></div>');
                    window.location.reload(true);
                }else if(typeof (fd_data.status) != 'undefined' && fd_data.status == 'ERROR') {
                    jQuery('.user_guide_fdo').before('<div class="notice notice-error class_error_message"><p><strong>Error! ' + fd_data.message + '</strong></p></div>');
                }else if (fd_data.is_valid == 'true') {
                    jQuery('.user_guide_fdo').before('<div class="notice notice-error class_error_message"><p><strong>Error!</strong> FreightDesk Online ID is not valid.</p></div>');
                } else if (fd_data.is_valid == 'true' && fd_data.is_connected) {
                    jQuery('.user_guide_fdo').before('<div class="notice notice-error class_error_message"><p><strong>Error!</strong> Your store is already connected with FreightDesk Online.</p></div>');

                } else if (fd_data.is_valid == true && fd_data.is_connected == false && fd_data.redirect_url != null) {
                    window.location = fd_data.redirect_url;
                } else if (fd_data.is_connected == true) {
                    jQuery('#con_dis').empty();
                    jQuery('#con_dis').append('<a href="#" id="fd_online_id_freightquote" data="disconnect" class="button-primary">Disconnect</a>')
                }
            }
        });
        e.preventDefault();
    });
    jQuery('#freightquote_api_endpoint').change(function () {
        freightquote_ltl_connection_section_api_endpoint();
    });

    freightquote_ltl_connection_section_api_endpoint();

    jQuery('.freightquote_ltl_connection_section_class .form-table').before('<div class="warning-msg-ltl"><p> <b>Note!</b> You must have a Freightquote LTL account to use this application. If you do not have one, click <a href="https://www.freightquote.com/create-account" target="_blank">here</a> to access the new account request form. </p>');

    jQuery('.carrier_section_class .button-primary, .carrier_section_class .is-primary').on('click', function () {
        jQuery(".updated").hide();
        var num_of_checkboxes = jQuery('.carrier_check:checked').length;
        if (num_of_checkboxes < 1) {
            jQuery(".carrier_section_class:first-child").before('<div id="message" class="error inline no_srvc_select"><p><strong>Error!</strong> Please select at least one carrier service.</p></div>');//junaid fix

            jQuery('html, body').animate({
                'scrollTop': jQuery('.no_srvc_select').position().top
            });
            return false;
        }
    });


    jQuery('.quote_section_class_ltl .button-primary, .quote_section_class_ltl .is-primary').on('click', function () {
        jQuery(".updated").hide();
        jQuery('.error').remove();
        
        const offset_days = jQuery('#freightquote_freight_shipment_offset_days').val();
        if (offset_days && (offset_days > 8 || isNaN(offset_days))) {
            jQuery("#mainform .quote_section_class_ltl").prepend('<div id="message" class="error inline handlng_fee_error"><p><strong>Error!</strong> Fullfilment Offset days must be less than or equal to 8.</p></div>');
            jQuery('html, body').animate({
                'scrollTop': jQuery('.handlng_fee_error').position().top
            });
            return false;
        }

        var handling_fee = jQuery('#wc_settings_freightquote_hand_free_mark_up').val();
        if (typeof handling_fee != 'undefined' && handling_fee.slice(handling_fee.length - 1) == '%') {
            handling_fee = handling_fee.slice(0, handling_fee.length - 1)
        }
        if (!freightQuoteWeightOfHandlingUnit()) {
            return false;
        }
        if (!freightQuoteMaxWeightOfHandlingUnit()) {
            return false;
        }
        if (typeof handling_fee != 'undefined' && handling_fee === "") {
            return true;
        } else {
            if (typeof handling_fee != 'undefined' && isValidNumber(handling_fee) === false) {

                jQuery("#mainform .quote_section_class_ltl").prepend('<div id="message" class="error inline handlng_fee_error"><p><strong>Error!</strong> Handling fee format should be 100.20 or 10%.</p></div>');//junaid fix
                jQuery('html, body').animate({
                    'scrollTop': jQuery('.handlng_fee_error').position().top
                });
                return false;
            } else if (typeof handling_fee != 'undefined' && isValidNumber(handling_fee) === 'decimal_point_err') {
                jQuery("#mainform .quote_section_class_ltl").prepend('<div id="message" class="error inline handlng_fee_error"><p><strong>Error!</strong> Handling fee format should be 100.2000 or 10% and only 4 digits are allowed after decimal.</p></div>');//junaid fix
                jQuery('html, body').animate({
                    'scrollTop': jQuery('.handlng_fee_error').position().top
                });
                return false;
            } else {
                return true;
            }
        }
    });

    var all_checkboxes = jQuery('.carrier_check');
    if (all_checkboxes.length === all_checkboxes.filter(":checked").length) {
        jQuery('.include_all').prop('checked', true);
    }

    jQuery(".include_all").change(function () {
        if (this.checked) {
            jQuery(".carrier_check").each(function () {
                this.checked = true;
            })
        } else {
            jQuery(".carrier_check").each(function () {
                this.checked = false;
            })
        }
    });

    /*
     * Uncheck Select All Checkbox
     */

    jQuery(".carrier_check").on('change load', function () {
        var int_checkboxes = jQuery('.carrier_check:checked').length;
        var int_un_checkboxes = jQuery('.carrier_check').length;
        if (int_checkboxes === int_un_checkboxes) {
            jQuery('.include_all').prop('checked', true);
        } else {
            jQuery('.include_all').prop('checked', false);
        }
    });

    //        changed
    var wc_settings_freightquote_rate_method = jQuery("#wc_settings_freightquote_rate_method").val();
    if (wc_settings_freightquote_rate_method == 'Cheapest') {
        jQuery("#wc_settings_freightquote_Number_of_options").closest('tr').addClass("freightquote_Number_of_options_class");
        jQuery("#wc_settings_freightquote_Number_of_options").closest('tr').css("display", "none");
    }

    jQuery("#wc_settings_freightquote_rate_method").change(function () {
        var rating_method = jQuery(this).val();
        if (rating_method == 'Cheapest') {

            jQuery(".freightquote_delivery_estimate").removeAttr('style');
            jQuery(".freightquote_Number_of_label_as").removeAttr('style');
            jQuery(".freightquote_Number_of_options_class").removeAttr('style');
            
            jQuery(".en_freight_quote_truckload_flatbed_label_tr_class").removeAttr('style');
            jQuery(".en_freight_quote_truckload_refrigerated_label_tr_class").removeAttr('style');
            jQuery(".en_freight_quote_truckload_van_label_tr_class").removeAttr('style');
    
            jQuery("#wc_settings_freightquote_Number_of_options").closest('tr').addClass("freightquote_Number_of_options_class");
            jQuery("#wc_settings_freightquote_Number_of_options").closest('tr').css("display", "none");
            jQuery("#wc_settings_freightquote_label_as").closest('tr').addClass("freightquote_Number_of_label_as");
            jQuery("#wc_settings_freightquote_delivery_estimate").closest('tr').addClass("freightquote_delivery_estimate");
            jQuery("#wc_settings_freightquote_rate_method").closest('tr').addClass("freightquote_rate_mehod");

            jQuery('.freightquote_rate_mehod td p').html('Displays only the cheapest returned Rate.');
            jQuery('.freightquote_Number_of_label_as td p').html('What the user sees during checkout, e.g. "Freight". Leave blank to display the carrier name.');
            jQuery('#wc_settings_freightquote_rate_method').next('.description').text('Displays a least expensive option.');//junaid rating fix

        }
        if (rating_method == 'cheapest_options') {

            jQuery(".freightquote_delivery_estimate").removeAttr('style');
            jQuery(".freightquote_Number_of_label_as").removeAttr('style');
            jQuery(".freightquote_Number_of_options_class").removeAttr('style');

            jQuery(".en_freight_quote_truckload_flatbed_label_tr_class").removeAttr('style');
            jQuery(".en_freight_quote_truckload_refrigerated_label_tr_class").removeAttr('style');
            jQuery(".en_freight_quote_truckload_van_label_tr_class").removeAttr('style');
    
            jQuery("#wc_settings_freightquote_delivery_estimate").closest('tr').addClass("freightquote_delivery_estimate");
            jQuery("#wc_settings_freightquote_label_as").closest('tr').addClass("freightquote_Number_of_label_as");
            jQuery("#wc_settings_freightquote_label_as").closest('tr').css("display", "none");
            jQuery("#wc_settings_freightquote_Number_of_options").closest('tr').addClass("freightquote_Number_of_options_class");
            jQuery("#wc_settings_freightquote_rate_method").closest('tr').addClass("freightquote_rate_mehod");

            jQuery('.freightquote_rate_mehod td p').html('Displays a list of a specified number of least expensive options.');
            jQuery('.freightquote_Number_of_options_class td p').html('Number of options to display in the shopping cart.');
            jQuery('#wc_settings_freightquote_rate_method').next('.description').text('Displays a list of a specified number of least expensive options.');//junaid rating fix
            jQuery('#wc_settings_freightquote_Number_of_options').next('.description').text('Number of options to display in the shopping cart.');//junaid new fix
        }
        if (rating_method == 'average_rate') {

            jQuery(".freightquote_delivery_estimate").removeAttr('style');
            jQuery(".freightquote_Number_of_label_as").removeAttr('style');
            jQuery(".freightquote_Number_of_options_class").removeAttr('style');

            jQuery("#wc_settings_freightquote_delivery_estimate").closest('tr').addClass("freightquote_delivery_estimate");
            jQuery("#wc_settings_freightquote_delivery_estimate").closest('tr').css("display", "none");
            jQuery("#wc_settings_freightquote_label_as").closest('tr').addClass("freightquote_Number_of_label_as");
            jQuery("#wc_settings_freightquote_Number_of_options").closest('tr').addClass("freightquote_Number_of_options_class");
            jQuery("#wc_settings_freightquote_rate_method").closest('tr').addClass("freightquote_rate_mehod");

            jQuery('.freightquote_rate_mehod td p').html('Displays a single rate based on an average of a specified number of least expensive options.');
            jQuery('.freightquote_Number_of_options_class td p').html('Number of options to include in the calculation of the average.');
            jQuery('.freightquote_Number_of_label_as td p').html('What the user sees during checkout, e.g. "Freight". If left blank will default to "Freight".');
            jQuery('#wc_settings_freightquote_rate_method').next('.description').text('Displays a single rate based on an average of a specified number of least expensive options.');//junaid rating fix
            jQuery('#wc_settings_freightquote_Number_of_options').next('.description').text('Number of options to include in the calculation of the average.');//junaid new fix

            jQuery("#en_freight_quote_truckload_flatbed_label").closest('tr').addClass("en_freight_quote_truckload_flatbed_label_tr_class");
            jQuery("#en_freight_quote_truckload_flatbed_label").closest('tr').css("display", "none");
            jQuery("#en_freight_quote_truckload_refrigerated_label").closest('tr').addClass("en_freight_quote_truckload_refrigerated_label_tr_class");
            jQuery("#en_freight_quote_truckload_refrigerated_label").closest('tr').css("display", "none");
            jQuery("#en_freight_quote_truckload_van_label").closest('tr').addClass("en_freight_quote_truckload_van_label_tr_class");
            jQuery("#en_freight_quote_truckload_van_label").closest('tr').css("display", "none");
            
        }
    });

    jQuery('.freightquote_ltl_connection_section_class input[type="text"]').each(function () {
        if (jQuery(this).parent().find('.err').length < 1) {
            jQuery(this).after('<span class="err"></span>');
        }
    });

    jQuery('#wc_settings_freigtquote_freight_username').attr('title', 'Username');
    jQuery('#wc_settings_freigtquote_freight_password').attr('title', 'Password');
    jQuery('#wc_settings_freightquote_license_key').attr('title', 'Eniture API Key');
    jQuery('#wc_settings_freightquote_text_for_own_arrangment').attr('title', 'Text For Own Arrangement');
    jQuery('#wc_settings_freightquote_hand_free_mark_up').attr('title', 'Handling Fee / Markup');
    jQuery('#wc_settings_freightquote_label_as').attr('title', 'Label As');
    jQuery('#wc_settings_freightquote_label_as').attr('maxlength', '50');

    jQuery('#freightquote_customer_code').attr('title', 'Customer Code');

    // limited access delivery fee
    jQuery("#freightquote_quests_no_liftgate_delivery_as_option_item_length").keypress(function (e) {
        if (!String.fromCharCode(e.keyCode).match(/^[0-9\d\.\s]+$/i)) return false;
    });

    jQuery('#freightquote_quests_no_liftgate_delivery_as_option_item_length').keyup(function () {
		var val = jQuery(this).val();
		if (val.length > 7) {
			val = val.substring(0, 7);
			jQuery(this).val(val);
		}
	});

    jQuery('#freightquote_quests_no_liftgate_delivery_as_option_item_length').keyup(function () {
		var val = jQuery(this).val();
		var regex = /\./g;
		var count = (val.match(regex) || []).length;
		
        if (count > 1) {
			val = val.replace(/\.+$/, '');
			jQuery(this).val(val);
		}
    });
    
    // Product variants settings
    jQuery(document).on("click", '._nestedMaterials', function(e) {
        const checkbox_class = jQuery(e.target).attr("class");
        const name = jQuery(e.target).attr("name");
        const checked = jQuery(e.target).prop('checked');

        if (checkbox_class?.includes('_nestedMaterials')) {
            const id = name?.split('_nestedMaterials')[1];
            setNestMatDisplay(id, checked);
        }
    });

    // Callback function to execute when mutations are observed
    const handleMutations = (mutationList) => {
        let childs = [];
        for (const mutation of mutationList) {
            childs = mutation?.target?.children;
            if (childs?.length) setNestedMaterialsUI();
          }
    };
    const observer = new MutationObserver(handleMutations),
        targetNode = document.querySelector('.woocommerce_variations.wc-metaboxes'),
        config = { childList: true, subtree: true };
    if (targetNode) observer.observe(targetNode, config);
});

// Update plan
if (typeof en_update_plan != 'function') {
    function en_update_plan(input) {
        let action = jQuery(input).attr('data-action');
        jQuery.ajax({
            type: "POST",
            url: ajaxurl,
            data: {action: action},
            success: function (data_response) {
                window.location.reload(true);
            }
        });
    }
}

function isValidNumber(value, noNegative) {
    if (typeof (noNegative) === 'undefined')
        noNegative = false;
    var isValidNumber = false;
    var validNumber = (noNegative == true) ? parseFloat(value) >= 0 : true;
    if ((value == parseInt(value) || value == parseFloat(value)) && (validNumber)) {
        if (value.indexOf(".") >= 0) {
            var n = value.split(".");
            if (n[n.length - 1].length <= 4) {
                isValidNumber = true;
            } else {
                isValidNumber = 'decimal_point_err';
            }
        } else {
            isValidNumber = true;
        }
    }
    return isValidNumber;
}

/**
 * Read a page's GET URL variables and return them as an associative array.
 */
function freightquote_ltl_connection_section_api_endpoint() {
    var api_endpoint = jQuery('#freightquote_api_endpoint').val();
    switch (api_endpoint) {
        case 'freightquote_new_api':
            jQuery('.freightquote_old_api').closest('tr').hide();
            jQuery('.freightquote_new_api').closest('tr').show();//junaid fix swap
            break;
        default:
            jQuery('.freightquote_new_api').closest('tr').hide();
            jQuery('.freightquote_old_api').closest('tr').show();//junaid fix swap
            break;
    }

}

/**
 * Check is valid number
 * @param num
 * @param selector
 * @param limit | LTL weight limit 20K
 * @returns {boolean}
 */
function isValidDecimal(num, selector, limit = 20000) {
    // validate the number:
    // positive and negative numbers allowed
    // just - sign is not allowed,
    // -0 is also not allowed.
    if (parseFloat(num) === 0) {
        // Change the value to zero
        return false;
    }

    const reg = /^(-?[0-9]{1,5}(\.\d{1,4})?|[0-9]{1,5}(\.\d{1,4})?)$/;
    let isValid = false;
    if (reg.test(num)) {
        isValid = inRange(parseFloat(num), -limit, limit);
    }
    if (isValid === true) {
        return true;
    }
    return isValid;
}

/**
 * Check is the number is in given range
 *
 * @param num
 * @param min
 * @param max
 * @returns {boolean}
 */
function inRange(num, min, max) {
    return ((num - min) * (num - max) <= 0);
}

function freightQuoteMaxWeightOfHandlingUnit() {
    var max_weight_of_handling_unit = jQuery('#freight_quote_maximum_handling_weight').val();
    if (typeof max_weight_of_handling_unit != 'undefined' && max_weight_of_handling_unit.length > 0) {
        var validResponse = isValidDecimal(max_weight_of_handling_unit, 'freight_quote_maximum_handling_weight');
    } else {
        validResponse = true;
    }
    if (validResponse) {
        return true;
    } else {
        jQuery("#mainform .quote_section_class_ltl").prepend('<div id="message" class="error inline ups_freight_max_wieght_of_handling_unit_error"><p><strong>Error! </strong>Maximum Weight per Handling Unit format should be like, e.g. 48.5 and only 3 digits are allowed after decimal point. The value can be up to 20,000.</p></div>');
        jQuery('html, body').animate({
            'scrollTop': jQuery('.ups_freight_max_wieght_of_handling_unit_error').position().top
        });
        jQuery("#freight_quote_maximum_handling_weight").css({'border-color': '#e81123'});
        return false;
    }
}

function freightQuoteWeightOfHandlingUnit() {
    var weight_of_handling_unit = jQuery('#freight_quote_settings_handling_weight').val();
    if (typeof weight_of_handling_unit != 'undefined' && weight_of_handling_unit.length > 0) {
        var validResponse = isValidDecimal(weight_of_handling_unit, 'freight_quote_settings_handling_weight');
    } else {
        validResponse = true;
    }
    if (validResponse) {
        return true;
    } else {
        jQuery("#mainform .quote_section_class_ltl").prepend('<div id="message" class="error inline ups_freight_wieght_of_handling_unit_error"><p><strong>Error! </strong>Weight of Handling Unit format should be like, e.g. 48.5 and only 3 digits are allowed after decimal point. The value can be up to 20,000.</p></div>');
        jQuery('html, body').animate({
            'scrollTop': jQuery('.ups_freight_wieght_of_handling_unit_error').position().top
        });
        jQuery("#freight_quote_settings_handling_weight").css({'border-color': '#e81123'});
        return false;
    }
}

/**
 * Read a page's GET URL variables and return them as an associative array.
 */
function getUrlVarsFreightQuoteLTL() {
    var vars = [], hash;
    var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
    for (var i = 0; i < hashes.length; i++) {
        hash = hashes[i].split('=');
        vars.push(hash[0]);
        vars[hash[0]] = hash[1];
    }
    return vars;
}

function freightquote_validateInput(form_id) {
    var has_err = true;
    var api_endpoint = jQuery('#freightquote_api_endpoint').val();
    jQuery(form_id + " input[type='text']").each(function () {
        if (jQuery(this).hasClass(api_endpoint)) {
            var input = jQuery(this).val();
            var response = validateString(input);
            var errorElement = jQuery(this).parent().find('.err');
            jQuery(errorElement).html('');
            var errorText = jQuery(this).attr('title');
            var optional = jQuery(this).data('optional');
            optional = (optional === undefined) ? 0 : 1;
            errorText = (errorText != undefined) ? errorText : '';
            if ((optional == 0) && (response == false || response == 'empty')) {
                errorText = (response == 'empty') ? errorText + ' is required.' : 'Invalid input.';
                jQuery(errorElement).html(errorText);
            }
            has_err = (response != true && optional == 0) ? false : has_err;
        }
    });
    return has_err;
}

function validateString(string) {
    if (string == '') {
        return 'empty';
    } else {
        return true;
    }
}

function freight_quote_lfq_stop_special_characters(e) {
    // Allow: backspace, delete, tab, escape, enter and .
    if (jQuery.inArray(e.keyCode, [46, 9, 27, 13, 110, 190, 189]) !== -1 ||
        // Allow: Ctrl+A, Command+A
        (e.keyCode === 65 && (e.ctrlKey === true || e.metaKey === true)) ||
        // Allow: home, end, left, right, down, up
        (e.keyCode >= 35 && e.keyCode <= 40)) {
        // let it happen, don't do anything
        e.preventDefault();
        return;
    }
    // Ensure that it is a number and stop the keypress
    if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 90)) && (e.keyCode < 96 || e.keyCode > 105) && e.keyCode != 186 && e.keyCode != 8) {
        e.preventDefault();
    }
    if (e.keyCode == 186 || e.keyCode == 190 || e.keyCode == 189 || (e.keyCode > 64 && e.keyCode < 91)) {
        e.preventDefault();
        return;
    }
}

if (typeof setNestedMaterialsUI != 'function') {
    function setNestedMaterialsUI() {
        const nestedMaterials = jQuery('._nestedMaterials');
        const productMarkups = jQuery('._en_product_markup');
        
        if (productMarkups?.length) {
            for (const markup of productMarkups) {
                jQuery(markup).attr('maxlength', '7');

                jQuery(markup).keypress(function (e) {
                    if (!String.fromCharCode(e.keyCode).match(/^[0-9.%-]+$/))
                        return false;
                });
            }
        }

        if (nestedMaterials?.length) {
            for (let elem of nestedMaterials) {
                const className = elem.className;

                if (className?.includes('_nestedMaterials')) {
                    const checked = jQuery(elem).prop('checked'),
                        name = jQuery(elem).attr('name'),
                        id = name?.split('_nestedMaterials')[1];
                    setNestMatDisplay(id, checked);
                }
            }
        }
    }
}

if (typeof setNestMatDisplay != 'function') {
    function setNestMatDisplay (id, checked) {
        
        jQuery(`input[name="_nestedPercentage${id}"]`).attr('min', '0');
        jQuery(`input[name="_nestedPercentage${id}"]`).attr('max', '100');
        jQuery(`input[name="_nestedPercentage${id}"]`).attr('maxlength', '3');
        jQuery(`input[name="_maxNestedItems${id}"]`).attr('min', '0');
        jQuery(`input[name="_maxNestedItems${id}"]`).attr('max', '100');
        jQuery(`input[name="_maxNestedItems${id}"]`).attr('maxlength', '3');

        jQuery(`input[name="_nestedPercentage${id}"], input[name="_maxNestedItems${id}"]`).keypress(function (e) {
            if (!String.fromCharCode(e.keyCode).match(/^[0-9]+$/))
                return false;
        });

        jQuery(`input[name="_nestedPercentage${id}"]`).closest('p').css('display', checked ? '' : 'none');
        jQuery(`select[name="_nestedDimension${id}"]`).closest('p').css('display', checked ? '' : 'none');
        jQuery(`input[name="_maxNestedItems${id}"]`).closest('p').css('display', checked ? '' : 'none');
        jQuery(`select[name="_nestedStakingProperty${id}"]`).closest('p').css('display', checked ? '' : 'none');
    }
}