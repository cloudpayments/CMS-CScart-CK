<?php
/**
 * Gets statuses list for statuses_paid setting.
 */
function fn_settings_variants_addons_cloudkassir_statuses_paid()
{
    return fn_get_simple_statuses(STATUSES_ORDER);
}

/**
 * Gets statuses list for statuses_refund setting.
 */
function fn_settings_variants_addons_cloudkassir_statuses_refund()
{
    return fn_get_simple_statuses(STATUSES_ORDER);
}

/**
 * Gets currencies list for currency setting.
 */
function fn_settings_variants_addons_cloudkassir_currency()
{
    $result = array();
    $currencies = fn_get_currencies_list();

    foreach ($currencies as $code => $item) {
        $result[$code] = $item['description'];
    }

    return $result;
}

/**
 * Gets taxation systems list for sno setting.
 */
function fn_settings_variants_addons_cloudkassir_taxation_system()
{
    $result = array();
    $schema = fn_get_schema('cloudkassir', 'taxation_system');

    foreach ($schema as $key => $item) {
        $result[$key] = $item['name'];
    }

    return $result;
}