<?php

/**
 * FreightQuote LTL Database
 *
 * @package     FreightQuote LTL
 * @author      Eniture-Technology
 */
if (!defined('ABSPATH')) {
    exit;
}

// carriers table for multisite
global $wpdb;
require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

function freightquote_carriers_db($network_wide = null)
{
    global $wpdb;
    if ($wpdb->query("SHOW TABLES LIKE 'wp_freightQuote_carriers'") != 0 && $wpdb->prefix != 'wp_') {
        $wpdb->query("RENAME TABLE wp_freightQuote_carriers TO " . $wpdb->prefix . "freightQuote_carriers");
    }
    if (is_multisite() && $network_wide) {
        foreach (get_sites(['fields' => 'ids']) as $blog_id) {
            switch_to_blog($blog_id);
            global $wpdb;
            $carriers_table = $wpdb->prefix . "freightQuote_carriers";
            if ($wpdb->query("SHOW TABLES LIKE '" . $wpdb->prefix . "freightQuote_carriers" . "'") === 0) {
                $sql = 'CREATE TABLE ' . $carriers_table . '(
                    `id` int(10) NOT NULL AUTO_INCREMENT,
                    `freightQuote_shipmentQuoteId` varchar(600) NOT NULL,
                    `freightQuote_carrierSCAC` varchar(600) NOT NULL,
                    `freightQuote_carrierName` varchar(600) NOT NULL,
                    `freightQuote_transitDays` varchar(600) NOT NULL,
                    `freightQuote_guaranteedService` varchar(600) NOT NULL,
                    `freightQuote_highCostDeliveryShipment` varchar(600) NOT NULL,
                    `freightQuote_interline` varchar(600) NOT NULL,
                    `freightQuote_nmfcRequired` varchar(600) NOT NULL,
                    `freightQuote_carrierNotifications` varchar(600) NOT NULL,
                    `carrier_logo` varchar(255) NOT NULL,
                    `carrier_status` varchar(8) NOT NULL,
                    PRIMARY KEY (`id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;" ';

                dbDelta($sql);
                freightquote_carriers($carriers_table);
            } else {
                $van_carrier = $wpdb->get_results("SELECT COUNT(*) AS carrier FROM ".$wpdb->prefix. "freightQuote_carriers where freightQuote_carrierSCAC = 'Van'");
                if ($van_carrier[0]->carrier < 1) {
                    $wpdb->insert(
                        $carriers_table, array(
                            'freightQuote_carrierSCAC' => 'Van',
                            'freightQuote_carrierName' => 'C.H. Robinson - Truckload Van',
                            'carrier_logo' => 'chr-logo.png'
                        )
                    );
                } else {
                    $data_array = [
                        'freightQuote_carrierName' => 'C.H. Robinson - Truckload Van'
                    ];
                    $wpdb->update(
                        $carriers_table, $data_array, ['freightQuote_carrierSCAC' => 'Van']
                    );
                }

                $flatbed_carrier = $wpdb->get_results("SELECT COUNT(*) AS carrier FROM ".$wpdb->prefix. "freightQuote_carriers where freightQuote_carrierSCAC = 'Flatbed'");
                if ($flatbed_carrier[0]->carrier < 1) {
                    $wpdb->insert(
                        $carriers_table, array(
                            'freightQuote_carrierSCAC' => 'Flatbed',
                            'freightQuote_carrierName' => 'C.H. Robinson - Truckload Flatbed',
                            'carrier_logo' => 'chr-logo.png'
                        )
                    );
                } else {
                    $data_array = [
                        'freightQuote_carrierName' => 'C.H. Robinson - Truckload Flatbed'
                    ];
                    $wpdb->update(
                        $carriers_table, $data_array, ['freightQuote_carrierSCAC' => 'Flatbed']
                    );
                }

                $reefer_carrier = $wpdb->get_results("SELECT COUNT(*) AS carrier FROM ".$wpdb->prefix. "freightQuote_carriers where freightQuote_carrierSCAC = 'Reefer'");
                if ($reefer_carrier[0]->carrier < 1) {
                    $wpdb->insert(
                        $carriers_table, array(
                            'freightQuote_carrierSCAC' => 'Reefer',
                            'freightQuote_carrierName' => 'C.H. Robinson - Truckload Refrigerated',
                            'carrier_logo' => 'chr-logo.png'
                        )
                    );
                } else {
                    $data_array = [
                        'freightQuote_carrierName' => 'C.H. Robinson - Truckload Refrigerated'
                    ];
                    $wpdb->update(
                        $carriers_table, $data_array, ['freightQuote_carrierSCAC' => 'Reefer']
                    );
                }

                $ups_freight_carrier = $wpdb->get_results("SELECT COUNT(*) AS carrier FROM ".$wpdb->prefix. "freightQuote_carriers where freightQuote_carrierSCAC = 'UPGF'");
                if ($ups_freight_carrier[0]->carrier > 0) {
                    $data_array = [
                        'freightQuote_carrierName' => 'Tforce Freight'
                    ];
                    $wpdb->update(
                        $carriers_table, $data_array, ['freightQuote_carrierSCAC' => 'UPGF']
                    );
                }

                $ups_freight_carrier = $wpdb->get_results("SELECT COUNT(*) AS carrier FROM ".$wpdb->prefix. "freightQuote_carriers where freightQuote_carrierSCAC = 'UPGF-CN'");
                if ($ups_freight_carrier[0]->carrier > 0) {
                    $data_array = [
                        'freightQuote_carrierName' => 'Tforce Freight-Canada'
                    ];
                    $wpdb->update(
                        $carriers_table, $data_array, ['freightQuote_carrierSCAC' => 'UPGF-CN']
                    );
                }

                $bdvl_freight_carrier = $wpdb->get_results("SELECT COUNT(*) AS carrier FROM " . $carriers_table . " where freightQuote_carrierSCAC = 'BDVL'");
                if (isset($bdvl_freight_carrier[0], $bdvl_freight_carrier[0]->carrier) && $bdvl_freight_carrier[0]->carrier < 1) {
                    $wpdb->insert(
                        $carriers_table, array(
                        'freightQuote_carrierSCAC' => 'BDVL',
                        'freightQuote_carrierName' => 'Best Delivery LLC',
                        'carrier_logo' => 'bdvl.png',
                        'carrier_status' => '1'
                    ));
                }
            }
            // Origin terminal address
            freightquote_update_warehouse();
            restore_current_blog();
        }
    } else {
        global $wpdb;
        $carriers_table = $wpdb->prefix . "freightQuote_carriers";
        
        if ($wpdb->query("SHOW TABLES LIKE '" . $wpdb->prefix . "freightQuote_carriers" . "'") === 0) {
            $sql = 'CREATE TABLE ' . $carriers_table . '(
                    `id` int(10) NOT NULL AUTO_INCREMENT,
        `freightQuote_shipmentQuoteId` varchar(600) NOT NULL,
        `freightQuote_carrierSCAC` varchar(600) NOT NULL,
        `freightQuote_carrierName` varchar(600) NOT NULL,
        `freightQuote_transitDays` varchar(600) NOT NULL,
        `freightQuote_guaranteedService` varchar(600) NOT NULL,
        `freightQuote_highCostDeliveryShipment` varchar(600) NOT NULL,
        `freightQuote_interline` varchar(600) NOT NULL,
        `freightQuote_nmfcRequired` varchar(600) NOT NULL,
        `freightQuote_carrierNotifications` varchar(600) NOT NULL,
        `carrier_logo` varchar(255) NOT NULL,
        `carrier_status` varchar(8) NOT NULL,
        PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;" ';

            dbDelta($sql);
            freightquote_carriers($carriers_table);

        } else {

            $van_carrier = $wpdb->get_results("SELECT COUNT(*) AS carrier FROM ".$wpdb->prefix. "freightQuote_carriers where freightQuote_carrierSCAC = 'Van'");
            if ($van_carrier[0]->carrier < 1) {
                $wpdb->insert(
                    $carriers_table, array(
                        'freightQuote_carrierSCAC' => 'Van',
                        'freightQuote_carrierName' => 'C.H. Robinson - Truckload Van',
                        'carrier_logo' => 'chr-logo.png'
                    )
                );
            } else {
                $data_array = [
                    'freightQuote_carrierName' => 'C.H. Robinson - Truckload Van'
                ];
                $wpdb->update(
                    $carriers_table, $data_array, ['freightQuote_carrierSCAC' => 'Van']
                );
            }

            $flatbed_carrier = $wpdb->get_results("SELECT COUNT(*) AS carrier FROM ".$wpdb->prefix. "freightQuote_carriers where freightQuote_carrierSCAC = 'Flatbed'");
            if ($flatbed_carrier[0]->carrier < 1) {
                $wpdb->insert(
                    $carriers_table, array(
                        'freightQuote_carrierSCAC' => 'Flatbed',
                        'freightQuote_carrierName' => 'C.H. Robinson - Truckload Flatbed',
                        'carrier_logo' => 'chr-logo.png'
                    )
                );
            } else {
                $data_array = [
                    'freightQuote_carrierName' => 'C.H. Robinson - Truckload Flatbed'
                ];
                $wpdb->update(
                    $carriers_table, $data_array, ['freightQuote_carrierSCAC' => 'Flatbed']
                );
            }

            $reefer_carrier = $wpdb->get_results("SELECT COUNT(*) AS carrier FROM ".$wpdb->prefix. "freightQuote_carriers where freightQuote_carrierSCAC = 'Reefer'");
            if ($reefer_carrier[0]->carrier < 1) {
                $wpdb->insert(
                    $carriers_table, array(
                        'freightQuote_carrierSCAC' => 'Reefer',
                        'freightQuote_carrierName' => 'C.H. Robinson - Truckload Refrigerated',
                        'carrier_logo' => 'chr-logo.png'
                    )
                );
            } else {
                $data_array = [
                    'freightQuote_carrierName' => 'C.H. Robinson - Truckload Refrigerated'
                ];
                $wpdb->update(
                    $carriers_table, $data_array, ['freightQuote_carrierSCAC' => 'Reefer']
                );
            }
            // update "Forward Air, Inc" scac from FWDA to FWRA
            $forward_air = $wpdb->get_results("SELECT id FROM ".$wpdb->prefix. "freightQuote_carriers where freightQuote_carrierSCAC = 'FWDA'");
            if (!empty($forward_air) && $forward_air[0]->id > 0) {
                $data_array = [
                    'freightQuote_carrierSCAC' => 'FWRA'
                ];
                $wpdb->update(
                    $carriers_table, $data_array, ['id' => $forward_air[0]->id]
                );
            }

            $ups_freight_carrier = $wpdb->get_results("SELECT COUNT(*) AS carrier FROM ".$wpdb->prefix. "freightQuote_carriers where freightQuote_carrierSCAC = 'UPGF'");
            if ($ups_freight_carrier[0]->carrier > 0) {
                $data_array = [
                    'freightQuote_carrierName' => 'Tforce Freight'
                ];
                $wpdb->update(
                    $carriers_table, $data_array, ['freightQuote_carrierSCAC' => 'UPGF']
                );
            }

            $ups_freight_carrier = $wpdb->get_results("SELECT COUNT(*) AS carrier FROM ".$wpdb->prefix. "freightQuote_carriers where freightQuote_carrierSCAC = 'UPGF-CN'");
            if ($ups_freight_carrier[0]->carrier > 0) {
                $data_array = [
                    'freightQuote_carrierName' => 'Tforce Freight-Canada'
                ];
                $wpdb->update(
                    $carriers_table, $data_array, ['freightQuote_carrierSCAC' => 'UPGF-CN']
                );
            }

            $bdvl_freight_carrier = $wpdb->get_results("SELECT COUNT(*) AS carrier FROM " . $carriers_table . " where freightQuote_carrierSCAC = 'BDVL'");
            if (isset($bdvl_freight_carrier[0], $bdvl_freight_carrier[0]->carrier) && $bdvl_freight_carrier[0]->carrier < 1) {
                $wpdb->insert(
                    $carriers_table, array(
                    'freightQuote_carrierSCAC' => 'BDVL',
                    'freightQuote_carrierName' => 'Best Delivery LLC',
                    'carrier_logo' => 'bdvl.png',
                    'carrier_status' => '1'
                ));
            }
        }
        // Origin terminal address
        freightquote_update_warehouse();
    }

}

global $wpdb;
require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

/**
 * Update warehouse
 */
function freightquote_update_warehouse()
{
    // Origin terminal address
    global $wpdb;
    $warehouse_table = $wpdb->prefix . "warehouse";
    $warehouse_address = $wpdb->get_row("SHOW COLUMNS FROM " . $wpdb->prefix . "warehouse LIKE 'phone_instore'");
    if (!(isset($warehouse_address->Field) && $warehouse_address->Field == 'phone_instore')) {
        $wpdb->query("ALTER TABLE ".$wpdb->prefix . "warehouse ADD COLUMN address VARCHAR(255) NOT NULL");
        // Terminal phone number
        $wpdb->query("ALTER TABLE ".$wpdb->prefix . "warehouse ADD COLUMN phone_instore VARCHAR(255) NOT NULL");
    }
}

/**
 * Create Warehouse Table
 * @global $wpdb
 */
function freightquote_create_ltl_wh_db($network_wide = null)
{
    if (is_multisite() && $network_wide) {

        foreach (get_sites(['fields' => 'ids']) as $blog_id) {
            switch_to_blog($blog_id);
            global $wpdb;
            $warehouse_table = $wpdb->prefix . "warehouse";
            if ($wpdb->query("SHOW TABLES LIKE '" . $wpdb->prefix . "warehouse'") === 0) {
                $origin = 'CREATE TABLE ' . $wpdb->prefix . 'warehouse(
                    id mediumint(9) NOT NULL AUTO_INCREMENT,
                    city varchar(200) NOT NULL,
                    state varchar(200) NOT NULL,
                    address varchar(255) NOT NULL,
                    phone_instore varchar(255) NOT NULL,
                    zip varchar(200) NOT NULL,
                    country varchar(200) NOT NULL,
                    location varchar(200) NOT NULL,
                    nickname varchar(200) NOT NULL,
                    enable_store_pickup VARCHAR(255) NOT NULL,
                    miles_store_pickup VARCHAR(255) NOT NULL ,
                    match_postal_store_pickup VARCHAR(255) NOT NULL ,
                    checkout_desc_store_pickup VARCHAR(255) NOT NULL ,
                    enable_local_delivery VARCHAR(255) NOT NULL ,
                    miles_local_delivery VARCHAR(255) NOT NULL ,
                    match_postal_local_delivery VARCHAR(255) NOT NULL ,
                    checkout_desc_local_delivery VARCHAR(255) NOT NULL ,
                    fee_local_delivery VARCHAR(255) NOT NULL ,
                    suppress_local_delivery VARCHAR(255) NOT NULL,
                    origin_markup VARCHAR(255),
                    PRIMARY KEY  (id) )';
                dbDelta($origin);
            }

            $myCustomer = $wpdb->get_row("SHOW COLUMNS FROM " . $wpdb->prefix . "warehouse LIKE 'enable_store_pickup'");
            if (!(isset($myCustomer->Field) && $myCustomer->Field == 'enable_store_pickup')) {
                $wpdb->query(sprintf("ALTER TABLE %s ADD COLUMN enable_store_pickup VARCHAR(255) NOT NULL , "
                    . "ADD COLUMN miles_store_pickup VARCHAR(255) NOT NULL , "
                    . "ADD COLUMN match_postal_store_pickup VARCHAR(255) NOT NULL , "
                    . "ADD COLUMN checkout_desc_store_pickup VARCHAR(255) NOT NULL , "
                    . "ADD COLUMN enable_local_delivery VARCHAR(255) NOT NULL , "
                    . "ADD COLUMN miles_local_delivery VARCHAR(255) NOT NULL , "
                    . "ADD COLUMN match_postal_local_delivery VARCHAR(255) NOT NULL , "
                    . "ADD COLUMN checkout_desc_local_delivery VARCHAR(255) NOT NULL , "
                    . "ADD COLUMN fee_local_delivery VARCHAR(255) NOT NULL , "
                    . "ADD COLUMN suppress_local_delivery VARCHAR(255) NOT NULL", $warehouse_table));
            }

            $fq_origin_markup = $wpdb->get_row("SHOW COLUMNS FROM ".$wpdb->prefix . "warehouse LIKE 'origin_markup'");
            if (!(isset($fq_origin_markup->Field) && $fq_origin_markup->Field == 'origin_markup')) {
                $wpdb->query("ALTER TABLE ".$wpdb->prefix . "warehouse ADD COLUMN origin_markup VARCHAR(255) NOT NULL");
            }  

            restore_current_blog();
        }

    } else {
        global $wpdb;
        $warehouse_table = $wpdb->prefix . "warehouse";
        if ($wpdb->query("SHOW TABLES LIKE '" . $wpdb->prefix . "warehouse'") === 0) {
            $origin = 'CREATE TABLE ' . $wpdb->prefix . 'warehouse(
                    id mediumint(9) NOT NULL AUTO_INCREMENT,
                    city varchar(200) NOT NULL,
                    state varchar(200) NOT NULL,
                    address varchar(255) NOT NULL,
                    phone_instore varchar(255) NOT NULL,
                    zip varchar(200) NOT NULL,
                    country varchar(200) NOT NULL,
                    location varchar(200) NOT NULL,
                    nickname varchar(200) NOT NULL,
                    enable_store_pickup VARCHAR(255) NOT NULL,
                    miles_store_pickup VARCHAR(255) NOT NULL ,
                    match_postal_store_pickup VARCHAR(255) NOT NULL ,
                    checkout_desc_store_pickup VARCHAR(255) NOT NULL ,
                    enable_local_delivery VARCHAR(255) NOT NULL ,
                    miles_local_delivery VARCHAR(255) NOT NULL ,
                    match_postal_local_delivery VARCHAR(255) NOT NULL ,
                    checkout_desc_local_delivery VARCHAR(255) NOT NULL ,
                    fee_local_delivery VARCHAR(255) NOT NULL ,
                    suppress_local_delivery VARCHAR(255) NOT NULL,
                    origin_markup VARCHAR(255),
                    PRIMARY KEY  (id) )';
            dbDelta($origin);
        }

        $myCustomer = $wpdb->get_row("SHOW COLUMNS FROM " . $wpdb->prefix . "warehouse LIKE 'enable_store_pickup'");
        if (!(isset($myCustomer->Field) && $myCustomer->Field == 'enable_store_pickup')) {
            $wpdb->query(sprintf("ALTER TABLE %s ADD COLUMN enable_store_pickup VARCHAR(255) NOT NULL , "
                . "ADD COLUMN miles_store_pickup VARCHAR(255) NOT NULL , "
                . "ADD COLUMN match_postal_store_pickup VARCHAR(255) NOT NULL , "
                . "ADD COLUMN checkout_desc_store_pickup VARCHAR(255) NOT NULL , "
                . "ADD COLUMN enable_local_delivery VARCHAR(255) NOT NULL , "
                . "ADD COLUMN miles_local_delivery VARCHAR(255) NOT NULL , "
                . "ADD COLUMN match_postal_local_delivery VARCHAR(255) NOT NULL , "
                . "ADD COLUMN checkout_desc_local_delivery VARCHAR(255) NOT NULL , "
                . "ADD COLUMN fee_local_delivery VARCHAR(255) NOT NULL , "
                . "ADD COLUMN suppress_local_delivery VARCHAR(255) NOT NULL", $warehouse_table));
        }

        $fq_origin_markup = $wpdb->get_row("SHOW COLUMNS FROM ".$wpdb->prefix . "warehouse LIKE 'origin_markup'");
        if (!(isset($fq_origin_markup->Field) && $fq_origin_markup->Field == 'origin_markup')) {
            $wpdb->query("ALTER TABLE ".$wpdb->prefix . "warehouse ADD COLUMN origin_markup VARCHAR(255) NOT NULL");
        }  
    }

}

function freightquote_carriers($carriers_table)
{

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    global $wpdb;
    $table_name = $carriers_table;
    $installed_carriers = $wpdb->get_results("SELECT COUNT(*) AS carriers FROM $table_name");
    if ($installed_carriers[0]->carriers < 1) {
        $wpdb->insert(
            $table_name, array(
            'freightQuote_carrierSCAC' => 'AACT',
            'freightQuote_carrierName' => 'AAA Cooper Transportation',
            'carrier_logo' => 'aact.png',
            'carrier_status' => '1'
        ));

        $wpdb->insert(
            $table_name, array(
            'freightQuote_carrierSCAC' => 'ABFS',
            'freightQuote_carrierName' => 'ABF Freight System, Inc',
            'carrier_logo' => 'abfs.png',
            'carrier_status' => '1'
        ));

        $wpdb->insert(
            $table_name, array(
            'freightQuote_carrierSCAC' => 'CHRA',
            'freightQuote_carrierName' => 'Atlanta Consolidation',
            'carrier_logo' => 'atlanta-consolidated.png',
            'carrier_status' => '1'
        ));
        $wpdb->insert(
            $table_name, array(
            'freightQuote_carrierSCAC' => 'AVRT',
            'freightQuote_carrierName' => 'Averitt Express',
            'carrier_logo' => 'averitt.png',
            'carrier_status' => '1'
        ));
        $wpdb->insert(
            $table_name, array(
            'freightQuote_carrierSCAC' => 'AMAP',
            'freightQuote_carrierName' => 'AMA Transportation Company Inc',
            'carrier_logo' => 'amap.png',
            'carrier_status' => '1'
        ));
        $wpdb->insert(
            $table_name, array(
            'freightQuote_carrierSCAC' => 'APXT',
            'freightQuote_carrierName' => 'APEX XPRESS',
            'carrier_logo' => 'apxt.png',
            'carrier_status' => '1'
        ));
        $wpdb->insert(
            $table_name, array(
            'freightQuote_carrierSCAC' => 'ATMR',
            'freightQuote_carrierName' => 'Atlas Motor Express',
            'carrier_logo' => 'atmr.png',
            'carrier_status' => '1'
        ));
        $wpdb->insert(
            $table_name, array(
            'freightQuote_carrierSCAC' => 'BDVL',
            'freightQuote_carrierName' => 'Best Delivery LLC',
            'carrier_logo' => 'bdvl.png',
            'carrier_status' => '1'
        ));
        $wpdb->insert(
            $table_name, array(
            'freightQuote_carrierSCAC' => 'BCKT',
            'freightQuote_carrierName' => 'Becker Trucking Inc',
            'carrier_logo' => 'bckt.png',
            'carrier_status' => '1'
        ));
        $wpdb->insert(
            $table_name, array(
            'freightQuote_carrierSCAC' => 'BEAV',
            'freightQuote_carrierName' => 'Beaver Express Service, LLC',
            'carrier_logo' => 'beav.png',
            'carrier_status' => '1'
        ));
        $wpdb->insert(
            $table_name, array(
            'freightQuote_carrierSCAC' => 'BTVP',
            'freightQuote_carrierName' => 'Best Overnite Express',
            'carrier_logo' => 'btvp.png',
            'carrier_status' => '1'
        ));
        $wpdb->insert(
            $table_name, array(
            'freightQuote_carrierSCAC' => 'CAZF',
            'freightQuote_carrierName' => 'Central Arizona Freight Lines',
            'carrier_logo' => 'cazf.png',
            'carrier_status' => '1'
        ));
        $wpdb->insert(
            $table_name, array(
            'freightQuote_carrierSCAC' => 'CENF',
            'freightQuote_carrierName' => 'Central Freight Lines, Inc',
            'carrier_logo' => 'cenf.png',
            'carrier_status' => '1'
        ));
        $wpdb->insert(
            $table_name, array(
            'freightQuote_carrierSCAC' => 'CLNI',
            'freightQuote_carrierName' => 'Clear Lane Freight Systems',
            'carrier_logo' => 'clni.png',
            'carrier_status' => '1'
        ));
        $wpdb->insert(
            $table_name, array(
            'freightQuote_carrierSCAC' => 'CNWY',
            'freightQuote_carrierName' => 'XPO Logistics',
            'carrier_logo' => 'cnwy.png',
            'carrier_status' => '1'
        ));
        $wpdb->insert(
            $table_name, array(
            'freightQuote_carrierSCAC' => 'CPCD',
            'freightQuote_carrierName' => 'Cape Cod Express',
            'carrier_logo' => 'cpcd.png',
            'carrier_status' => '1'
        ));
        $wpdb->insert(
            $table_name, array(
            'freightQuote_carrierSCAC' => 'CTII',
            'freightQuote_carrierName' => 'Central Transport',
            'carrier_logo' => 'ctii.png',
            'carrier_status' => '1'
        ));
        $wpdb->insert(
            $table_name, array(
            'freightQuote_carrierSCAC' => 'CXRE',
            'freightQuote_carrierName' => 'Cal State Express',
            'carrier_logo' => 'cxre.png',
            'carrier_status' => '1'
        ));
        $wpdb->insert(
            $table_name, array(
            'freightQuote_carrierSCAC' => 'CTBV',
            'freightQuote_carrierName' => 'Custom Transport',
            'carrier_logo' => 'ctii.png',
            'carrier_status' => '1'
        ));
        $wpdb->insert(
            $table_name, array(
            'freightQuote_carrierSCAC' => 'DAYR',
            'freightQuote_carrierName' => 'Day and Ross Inc.',
            'carrier_logo' => 'dar.png',
            'carrier_status' => '1'
        ));
        $wpdb->insert(
            $table_name, array(
            'freightQuote_carrierSCAC' => 'DAFG',
            'freightQuote_carrierName' => 'Dayton Freight',
            'carrier_logo' => 'dafg.png',
            'carrier_status' => '1'
        ));
        $wpdb->insert(
            $table_name, array(
            'freightQuote_carrierSCAC' => 'DUBL',
            'freightQuote_carrierName' => 'Dugan Truckline',
            'carrier_logo' => 'dubl.png',
            'carrier_status' => '1'
        ));
        $wpdb->insert(
            $table_name, array(
            'freightQuote_carrierSCAC' => 'DDPP',
            'freightQuote_carrierName' => 'Dedicated Delivery Professionals',
            'carrier_logo' => 'ddpp.png',
            'carrier_status' => '1'
        ));
        $wpdb->insert(
            $table_name, array(
            'freightQuote_carrierSCAC' => 'DHRN',
            'freightQuote_carrierName' => 'Dohrn Transfer Company',
            'carrier_logo' => 'dhrn.png',
            'carrier_status' => '1'
        ));
        $wpdb->insert(
            $table_name, array(
            'freightQuote_carrierSCAC' => 'DPHE',
            'freightQuote_carrierName' => 'Dependable Highway Express',
            'carrier_logo' => 'dphe.png',
            'carrier_status' => '1'
        ));
        $wpdb->insert(
            $table_name, array(
            'freightQuote_carrierSCAC' => 'DTST',
            'freightQuote_carrierName' => 'DATS Trucking Inc',
            'carrier_logo' => 'dtst.png',
            'carrier_status' => '1'
        ));
        $wpdb->insert(
            $table_name, array(
            'freightQuote_carrierSCAC' => 'DYLT',
            'freightQuote_carrierName' => 'Daylight Transport',
            'carrier_logo' => 'dylt.png',
            'carrier_status' => '1'
        ));
        $wpdb->insert(
            $table_name, array(
            'freightQuote_carrierSCAC' => 'DLDS',
            'freightQuote_carrierName' => 'Diamond Line Delivery System, Inc.',
            'carrier_logo' => 'dlds.png',
            'carrier_status' => '1'
        ));

        $wpdb->insert(
            $table_name, array(
            'freightQuote_carrierSCAC' => 'EXLA',
            'freightQuote_carrierName' => 'Estes Express Lines',
            'carrier_logo' => 'exla.png',
            'carrier_status' => '1'
        ));
        $wpdb->insert(
            $table_name, array(
            'freightQuote_carrierSCAC' => 'FXNL',
            'freightQuote_carrierName' => 'FedEx Freight Economy',
            'carrier_logo' => 'logo-header-fedex.png',
            'carrier_status' => '1'
        ));
        $wpdb->insert(
            $table_name, array(
            'freightQuote_carrierSCAC' => 'FXFE',
            'freightQuote_carrierName' => 'FedEx Freight Priority',
            'carrier_logo' => 'logo-header-fedex.png',
            'carrier_status' => '1'
        ));

        $wpdb->insert(
            $table_name, array(
            'freightQuote_carrierSCAC' => 'FCSY',
            'freightQuote_carrierName' => 'Frontline Freight Inc',
            'carrier_logo' => 'fcsy.png',
            'carrier_status' => '1'
        ));
        $wpdb->insert(
            $table_name, array(
            'freightQuote_carrierSCAC' => 'FLAN',
            'freightQuote_carrierName' => 'Flo Trans',
            'carrier_logo' => 'flan.png',
            'carrier_status' => '1'
        ));
        $wpdb->insert(
            $table_name, array(
            'freightQuote_carrierSCAC' => 'FTSC',
            'freightQuote_carrierName' => 'Fort Transportation',
            'carrier_logo' => 'ftsc.png',
            'carrier_status' => '1'
        ));
        $wpdb->insert(
            $table_name, array(
            'freightQuote_carrierSCAC' => 'FWRA',
            'freightQuote_carrierName' => 'Forward Air, Inc',
            'carrier_logo' => 'fwdn.png',
            'carrier_status' => '1'
        ));

        $wpdb->insert(
            $table_name, array(
            'freightQuote_carrierSCAC' => 'GLDF',
            'freightQuote_carrierName' => 'Gold Coast Freightways',
            'carrier_logo' => 'gldf.png',
            'carrier_status' => '1'
        ));
        $wpdb->insert(
            $table_name, array(
            'freightQuote_carrierSCAC' => 'HMES',
            'freightQuote_carrierName' => 'Holland',
            'carrier_logo' => 'hmes.png',
            'carrier_status' => '1'
        ));
        $wpdb->insert(
            $table_name, array(
            'freightQuote_carrierSCAC' => 'LAXV',
            'freightQuote_carrierName' => 'Land Air Express Of New England',
            'carrier_logo' => 'laxv.png',
            'carrier_status' => '1'
        ));
        $wpdb->insert(
            $table_name, array(
            'freightQuote_carrierSCAC' => 'LKVL',
            'freightQuote_carrierName' => 'Lakeville Motor Express Inc',
            'carrier_logo' => 'lkvl.png',
            'carrier_status' => '1'
        ));
        $wpdb->insert(
            $table_name, array(
            'freightQuote_carrierSCAC' => 'CHRW',
            'freightQuote_carrierName' => 'Los Angeles Consolidation',
            'carrier_logo' => 'los.png',
            'carrier_status' => '1'
        ));
        $wpdb->insert(
            $table_name, array(
            'freightQuote_carrierSCAC' => 'MDLD',
            'freightQuote_carrierName' => 'Midland Transport',
            'carrier_logo' => 'mdld.png',
            'carrier_status' => '1'
        ));
        $wpdb->insert(
            $table_name, array(
            'freightQuote_carrierSCAC' => 'MIDW',
            'freightQuote_carrierName' => 'Midwest Motor Express',
            'carrier_logo' => 'midw.png',
            'carrier_status' => '1'
        ));
        $wpdb->insert(
            $table_name, array(
            'freightQuote_carrierSCAC' => 'NEBT',
            'freightQuote_carrierName' => 'Nebraska Transport',
            'carrier_logo' => 'nebt.png',
            'carrier_status' => '1'
        ));
        $wpdb->insert(
            $table_name, array(
            'freightQuote_carrierSCAC' => 'NEMF',
            'freightQuote_carrierName' => 'New England Motor Freight',
            'carrier_logo' => 'nemf.png',
            'carrier_status' => '1'
        ));
        $wpdb->insert(
            $table_name, array(
            'freightQuote_carrierSCAC' => 'NJCH',
            'freightQuote_carrierName' => 'New Jersey Consolidation',
            'carrier_logo' => 'nj.png',
            'carrier_status' => '1'
        ));
        $wpdb->insert(
            $table_name, array(
            'freightQuote_carrierSCAC' => 'NMTF',
            'freightQuote_carrierName' => 'N and M Transfer',
            'carrier_logo' => 'nm.png',
            'carrier_status' => '1'
        ));
        $wpdb->insert(
            $table_name, array(
            'freightQuote_carrierSCAC' => 'NOPK',
            'freightQuote_carrierName' => 'North Park Transportation Co',
            'carrier_logo' => 'nopk.png',
            'carrier_status' => '1'
        ));
        $wpdb->insert(
            $table_name, array(
            'freightQuote_carrierSCAC' => 'NPME',
            'freightQuote_carrierName' => 'New Penn Motor Express',
            'carrier_logo' => 'npme.png',
            'carrier_status' => '1'
        ));
        $wpdb->insert(
            $table_name, array(
            'freightQuote_carrierSCAC' => 'OAKH',
            'freightQuote_carrierName' => 'Oak Harbor',
            'carrier_logo' => 'oakh.png',
            'carrier_status' => '1'
        ));
        $wpdb->insert(
            $table_name, array(
            'freightQuote_carrierSCAC' => 'ODFL',
            'freightQuote_carrierName' => 'Old Dominion Freight Line',
            'carrier_logo' => 'odfl.png',
            'carrier_status' => '1'
        ));
        $wpdb->insert(
            $table_name, array(
            'freightQuote_carrierSCAC' => 'PITD',
            'freightQuote_carrierName' => 'Pitt Ohio Express',
            'carrier_logo' => 'pitd.png',
            'carrier_status' => '1'
        ));

        $wpdb->insert(
            $table_name, array(
            'freightQuote_carrierSCAC' => 'PIEC',
            'freightQuote_carrierName' => 'Pilot Freight Services- Economy',
            'carrier_logo' => 'pilot.png',
            'carrier_status' => '1'
        ));
        $wpdb->insert(
            $table_name, array(
            'freightQuote_carrierSCAC' => 'POLT',
            'freightQuote_carrierName' => 'Polaris Transport',
            'carrier_logo' => 'polt.png',
            'carrier_status' => '1'
        ));
        $wpdb->insert(
            $table_name, array(
            'freightQuote_carrierSCAC' => 'RNLO',
            'freightQuote_carrierName' => 'R and L Carriers',
            'carrier_logo' => 'rlca.png',
            'carrier_status' => '1'
        ));
        $wpdb->insert(
            $table_name, array(
            'freightQuote_carrierSCAC' => 'PMLI',
            'freightQuote_carrierName' => 'Pace Motor Lines, Inc',
            'carrier_logo' => 'pmli.png',
            'carrier_status' => '1'
        ));
        $wpdb->insert(
            $table_name, array(
            'freightQuote_carrierSCAC' => 'PNII',
            'freightQuote_carrierName' => 'ProTrans International',
            'carrier_logo' => 'pnii.png',
            'carrier_status' => '1'
        ));
        $wpdb->insert(
            $table_name, array(
            'freightQuote_carrierSCAC' => 'PYLE',
            'freightQuote_carrierName' => 'A Duie PYLE',
            'carrier_logo' => 'pyle.png',
            'carrier_status' => '1'
        ));
        $wpdb->insert(
            $table_name, array(
            'freightQuote_carrierSCAC' => 'RDFS',
            'freightQuote_carrierName' => 'Roadrunner Freight',
            'carrier_logo' => 'rdfs.png',
            'carrier_status' => '1'
        ));

        $wpdb->insert(
            $table_name, array(
            'freightQuote_carrierSCAC' => 'RDWY',
            'freightQuote_carrierName' => 'YRC Freight',
            'carrier_logo' => 'rdwy.png',
            'carrier_status' => '1'
        ));
        $wpdb->insert(
            $table_name, array(
            'freightQuote_carrierSCAC' => 'RETL',
            'freightQuote_carrierName' => 'Reddaway',
            'carrier_logo' => 'retl.png',
            'carrier_status' => '1'
        ));
        $wpdb->insert(
            $table_name, array(
            'freightQuote_carrierSCAC' => 'YRCA',
            'freightQuote_carrierName' => 'YRC Accelerated',
            'carrier_logo' => 'rdwy.png',
            'carrier_status' => '1'
        ));
        $wpdb->insert(
            $table_name, array(
            'freightQuote_carrierSCAC' => 'RDTC',
            'freightQuote_carrierName' => 'YRC Freight Time Critical PM',
            'carrier_logo' => 'rdwy.png',
            'carrier_status' => '1'
        ));

        $wpdb->insert(
            $table_name, array(
            'freightQuote_carrierSCAC' => 'PAN4',
            'freightQuote_carrierName' => 'Panther Deferred (LTL)',
            'carrier_logo' => 'Panther.png',
            'carrier_status' => '1'
        ));
        $wpdb->insert(
            $table_name, array(
            'freightQuote_carrierSCAC' => 'RJWI',
            'freightQuote_carrierName' => 'RJW Transport',
            'carrier_logo' => 'rjwi.png',
            'carrier_status' => '1'
        ));
        $wpdb->insert(
            $table_name, array(
            'freightQuote_carrierSCAC' => 'ROSI',
            'freightQuote_carrierName' => 'Roseville Motor Express',
            'carrier_logo' => 'rosi.png',
            'carrier_status' => '1'
        ));
        $wpdb->insert(
            $table_name, array(
            'freightQuote_carrierSCAC' => 'RXIC',
            'freightQuote_carrierName' => 'Ross Express',
            'carrier_logo' => 'rxic.png',
            'carrier_status' => '1'
        ));
        $wpdb->insert(
            $table_name, array(
            'freightQuote_carrierSCAC' => 'SAIA',
            'freightQuote_carrierName' => 'Saia Motor Freight',
            'carrier_logo' => 'saia.png',
            'carrier_status' => '1'
        ));
        $wpdb->insert(
            $table_name, array(
            'freightQuote_carrierSCAC' => 'SEFL',
            'freightQuote_carrierName' => 'Southeastern Freight Lines',
            'carrier_logo' => 'sefl.png',
            'carrier_status' => '1'
        ));

        $wpdb->insert(
            $table_name, array(
            'freightQuote_carrierSCAC' => 'SHIF',
            'freightQuote_carrierName' => 'Shift Freight',
            'carrier_logo' => 'shif.png',
            'carrier_status' => '1'
        ));
        $wpdb->insert(
            $table_name, array(
            'freightQuote_carrierSCAC' => 'SMTL',
            'freightQuote_carrierName' => 'Southwestern Motor Transport',
            'carrier_logo' => 'smtl.png',
            'carrier_status' => '1'
        ));
        $wpdb->insert(
            $table_name, array(
            'freightQuote_carrierSCAC' => 'STDF',
            'freightQuote_carrierName' => 'Standard Forwarding Company Inc',
            'carrier_logo' => 'stdf.png',
            'carrier_status' => '1'
        ));
        $wpdb->insert(
            $table_name, array(
            'freightQuote_carrierSCAC' => 'SVSE',
            'freightQuote_carrierName' => 'SuperVan Service Co. Inc',
            'carrier_logo' => 'svse.png',
            'carrier_status' => '1'
        ));
        $wpdb->insert(
            $table_name, array(
            'freightQuote_carrierSCAC' => 'OVLD',
            'freightQuote_carrierName' => 'TST Overland Express',
            'carrier_logo' => 'ovld.png',
            'carrier_status' => '1'
        ));
        $wpdb->insert(
            $table_name, array(
            'freightQuote_carrierSCAC' => 'UPGF',
            'freightQuote_carrierName' => 'Tforce Freight',
            'carrier_logo' => 'upgf.png',
            'carrier_status' => '1'
        ));
        $wpdb->insert(
            $table_name, array(
            'freightQuote_carrierSCAC' => 'WARD',
            'freightQuote_carrierName' => 'Ward Trucking',
            'carrier_logo' => 'ward.png',
            'carrier_status' => '1'
        ));
        $wpdb->insert(
            $table_name, array(
            'freightQuote_carrierSCAC' => 'USRD',
            'freightQuote_carrierName' => 'US Road Freight Express',
            'carrier_logo' => 'us-road.png',
            'carrier_status' => '1'
        ));
        $wpdb->insert(
            $table_name, array(
            'freightQuote_carrierSCAC' => 'UPGF-CN',
            'freightQuote_carrierName' => 'Tforce Freight-Canada',
            'carrier_logo' => 'upgf.png',
            'carrier_status' => '1'
        ));
        $wpdb->insert(
            $table_name, array(
            'freightQuote_carrierSCAC' => 'UPPN',
            'freightQuote_carrierName' => 'US Special Delivery',
            'carrier_logo' => 'uppn.png',
            'carrier_status' => '1'
        ));
        $wpdb->insert(
            $table_name, array(
            'freightQuote_carrierSCAC' => 'WEBE',
            'freightQuote_carrierName' => 'West Bend Transit',
            'carrier_logo' => 'webe.png',
            'carrier_status' => '1'
        ));
        $wpdb->insert(
            $table_name, array(
            'freightQuote_carrierSCAC' => 'CGOJ',
            'freightQuote_carrierName' => 'Cargomatic ',
            'carrier_logo' => 'cargomatic.png',
            'carrier_status' => '1'
        ));

        $wpdb->insert(
            $table_name, array(
                'freightQuote_carrierSCAC' => 'WTVA',
                'freightQuote_carrierName' => 'Wilson Trucking Corporation',
                'carrier_logo' => 'wtva.png',
                'carrier_status' => '1'
            )
        );
        $wpdb->insert(
            $table_name, array(
            'freightQuote_carrierSCAC' => 'XGS1',
            'freightQuote_carrierName' => 'Xpress Global Systems',
            'carrier_logo' => 'xgs1.png',
            'carrier_status' => '1'
        ));

        $wpdb->insert(
            $table_name, array(
                'freightQuote_carrierSCAC' => 'TSM',
                'freightQuote_carrierName' => 'FreightQuote.com - Flatbed Logistics',
                'carrier_logo' => 'freightquote-logo.png',
                'carrier_status' => '1'
            )
        );

        $wpdb->insert(
            $table_name, array(
                'freightQuote_carrierSCAC' => 'REEF',
                'freightQuote_carrierName' => 'FreightQuote.com - Refrigerated Logistics',
                'carrier_logo' => 'freightquote-logo.png',
                'carrier_status' => '1'
            )
        );

        $wpdb->insert(
            $table_name, array(
                'freightQuote_carrierSCAC' => 'ABHB',
                'freightQuote_carrierName' => 'FreightQuote.com - Truckload Logistics',
                'carrier_logo' => 'freightquote-logo.png',
                'carrier_status' => '1'
            )
        );

        $wpdb->insert(
            $table_name, array(
                'freightQuote_carrierSCAC' => 'Van',
                'freightQuote_carrierName' => 'C.H. Robinson - Truckload Van',
                'carrier_logo' => 'chr-logo.png',
                'carrier_status' => '1'
            )
        );

        $wpdb->insert(
            $table_name, array(
                'freightQuote_carrierSCAC' => 'Flatbed',
                'freightQuote_carrierName' => 'C.H. Robinson - Truckload Flatbed',
                'carrier_logo' => 'chr-logo.png',
                'carrier_status' => '1'
            )
        );

        $wpdb->insert(
            $table_name, array(
                'freightQuote_carrierSCAC' => 'Reefer',
                'freightQuote_carrierName' => 'C.H. Robinson - Truckload Refrigerated',
                'carrier_logo' => 'chr-logo.png',
                'carrier_status' => '1'
            )
        );
    }
}
