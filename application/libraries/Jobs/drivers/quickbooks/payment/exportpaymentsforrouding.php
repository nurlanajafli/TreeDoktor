<?php

use QuickBooksOnline\API\Facades\Payment;
use QuickBooksOnline\API\QueryFilter\QueryMessage;

class exportpaymentsforrouding extends CI_Driver implements JobsInterface
{
    private $settings;
    private $dataService;
    private $CI;

    function __construct()
    {
        $this->CI = &get_instance();
        $this->CI->load->helper('qb_helper');
        $this->CI->load->model('mdl_clients');

        $this->settings = getQbSettings();
        if (!empty($this->settings) && $this->settings && $this->settings['clientID'] && $this->settings['clientSecret'] && $this->settings['accessTokenKey'] && $this->settings['refreshTokenKey'] && $this->settings['QBORealmID'] && $this->settings['baseUrl'])
            $this->dataService = dataServiceConfigureFromArguments($this->settings['clientID'], $this->settings['clientSecret'], $this->settings['accessTokenKey'],
                $this->settings['refreshTokenKey'], $this->settings['QBORealmID'], $this->settings['baseUrl']);
    }
    public function getPayload($data = NULL)
    {
        if (!$data || empty($this->settings['accessToken']))
            return FALSE;
        return $data;
    }

    public function execute($job = NULL)
    {
        if ($job) {
            while (true) {
                // Build a query
                $oneQuery = new QueryMessage();
                $oneQuery->sql = "SELECT";
                $oneQuery->entity = "Invoice";
                $oneQuery->whereClause = ["Balance = '0.01'"];
                $result = customQuery($oneQuery, $this->dataService);
                if (!$result)
                    return TRUE;
                elseif ($result == 'refresh')
                    return FALSE;
                foreach ($result as $invoice) {
                    $payment = getPaymentForQBFromInvoiceQB($invoice);
                    $paymentObject = Payment::create($payment);
                    $qbId = createRecordInQBFromObject($paymentObject, $this->dataService);
                    if ($qbId == 'AuthenticationFailed' || $qbId == 'AuthorizationFailed')
                        return FALSE;
                }
            }
            return TRUE;
        }
        return FALSE;
    }
}
