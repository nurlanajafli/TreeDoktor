<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

/**
 * Make internal payment
 *
 * @param array $paymentDetails = [
 *    'card_id' => (string),
 *    'entity_description' => (string), // 'SMS subscription'
 *    'entity_item_name' => (string),   // $subscription->name  'Lite'
 *    'amount' => (float)               // $subscription->amount
 * ]
 * @return array
 * @throws Exception
 */
function internalPay(array $paymentDetails): array
{
    if (empty($paymentDetails['card_id'])
        || empty($paymentDetails['amount'])
        || empty($paymentDetails['entity_description'])
        || empty($paymentDetails['entity_item_name']))
    {
        throw new Exception('No required data');
    }

	$CI = & get_instance();

    $driverParams = [
        'internal_payment' => true
    ];

    $CI->load->library('Payment/ArboStarProcessing', $driverParams, 'arboStarProcessing');

    $driver = config_item('int_pay_driver');
    $intPayProfile = config_item('int_pay_profile_' . $driver);

    $paymentDetails['payment_profile'] = $intPayProfile;

    try {
        return $CI->arboStarProcessing->internalPay($paymentDetails);
    }
    catch (PaymentException $e) {
        throw new Exception($e->getMessage());
    }
}

//end of file internal_payment_helper.php
