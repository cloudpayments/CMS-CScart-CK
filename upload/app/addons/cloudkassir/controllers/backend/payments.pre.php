<?php
if (!defined('BOOTSTRAP')) { die('Access denied'); }

/** @var string $mode */

if ($mode == 'update') {
    $payment_id = isset($_REQUEST['payment_id']) ? $_REQUEST['payment_id'] : 0;

    Tygh::$app['view']->assign('cloudkassir_payment_id', fn_cloudkassir_is_payment_enabled($payment_id) ? 'Y' : 'N');
}/* elseif ($mode == 'manage') {
    //Tygh::$app['view']->assign('cloudkassir_payment_id', fn_cloudkassir_get_external_payments());
}*/
