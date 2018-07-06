<?php

use Tygh\CloudKassir;
use Tygh\Registry;
use Tygh\Settings;

function fn_cloudkassir_get_settings() {
    if (!Registry::isExist('cloudkassir.extra_settings')) {
        $settings = json_decode(Registry::get('addons.cloudkassir.extra'), true);

        if (!is_array($settings)) {
            $settings = array();
        }

        Registry::set('cloudkassir.extra_settings', $settings, false);
    }

    return (array)Registry::get('cloudkassir.extra_settings');
}

/**
 * Updates extra setting.
 *
 * @param string $setting_name Extra setting name
 * @param mixed  $value        Value
 */
function fn_cloudkassir_update_setting($setting_name, $value) {
    $settings                = fn_cloudkassir_get_settings();
    $settings[$setting_name] = $value;

    Registry::set(sprintf('cloudkassir.extra_settings.%s', $setting_name), $value);
    Settings::instance()->updateValue('extra', json_encode($settings), 'cloudkassir', false, false);
}

/**
 * Gets payments external identifiers.
 *
 * @return array
 */
function fn_cloudkassir_get_payments_map() {
    $settings = fn_cloudkassir_get_settings();

    return isset($settings['payments_map']) ? $settings['payments_map'] : array();
}

/**
 * Sets payment external identifier.
 *
 * @param int         $payment_id Local identifier
 * @param bool|string $enabled
 */
function fn_cloudkassir_enable_payment($payment_id, $enabled) {
    $map = fn_cloudkassir_get_payments_map();

    if ($enabled || $enabled === 'Y') {
        $map[$payment_id] = true;
    } else {
        unset($map[$payment_id]);
    }

    fn_cloudkassir_update_setting('payments_map', $map);
}

/**
 * Gets payment external identifier.
 *
 * @param int $payment_id Payment identifier
 *
 * @return int|null
 */
function fn_cloudkassir_is_payment_enabled($payment_id) {
    $map = fn_cloudkassir_get_payments_map();

    return isset($map[$payment_id]) ? $map[$payment_id] === true : false;
}

/**
 * Hook handler: after order status changed.
 *
 * @param string $status_to   Order status to
 * @param string $status_from Order status from
 * @param array  $order_info  Order data
 */
function fn_cloudkassir_change_order_status($status_to, $status_from, $order_info) {
    if ($order_info['is_parent_order'] === 'Y') {
        return;
    }

    $statuses_paid            = Registry::get('addons.cloudkassir.statuses_paid');
    $statuses_refund          = Registry::get('addons.cloudkassir.statuses_refund');
    $payment_id               = isset($order_info['payment_id']) ? $order_info['payment_id'] : 0;

    if (!fn_cloudkassir_is_payment_enabled($payment_id)) {
        return;
    }

    /** @var CloudKassir $service */
    $service = new CloudKassir(
        Registry::get('addons.cloudkassir.public_id'),
        Registry::get('addons.cloudkassir.secret_key'),
        Registry::get('addons.cloudkassir.inn'),
        Registry::get('addons.cloudkassir.taxation_system')
    );

    if (
        isset($statuses_paid[$status_to])
        && !isset($statuses_paid[$status_from])
    ) {
        $service->sendReceipt($order_info, CloudKassir::RECEIPT_TYPE_INCOME);
    } elseif (
        isset($statuses_refund[$status_to])
        && !isset($statuses_refund[$status_from])
    ) {
        $service->sendReceipt($order_info, CloudKassir::RECEIPT_TYPE_INCOME_RETURN);
    }
}

/**
 * Hook handler: after payment updated.
 *
 * @param array $payment_data Payment data
 * @param int   $payment_id   Payment identifier
 */
function fn_cloudkassir_update_payment_post($payment_data, $payment_id) {
    fn_cloudkassir_enable_payment($payment_id, isset($payment_data['cloudkassir_payment_id']) && $payment_data['cloudkassir_payment_id'] === 'Y');
}