<?php


class importdepositindb extends CI_Driver implements JobsInterface
{
    private $settings;
    private $dataService;
    private $CI;

    function __construct()
    {
        $this->CI = &get_instance();
        $this->CI->load->helper('qb_helper');
        $this->CI->load->model('mdl_clients');
        $this->CI->load->model('mdl_leads');
        $this->CI->load->model('mdl_estimates');
        $this->CI->load->model('mdl_workorders');
        $this->CI->load->model('mdl_invoices');

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
            $payload = $job->job_payload;
            $i = 1;
            $paymentMethods = getPaymentMethods($this->dataService);
            while (true) {
                $invoices = $this->dataService->FindAll($payload, $i, 1000);
                $error = checkError($this->dataService);
                if (!$error) {
                    return FALSE;
                }
                if (!$invoices)
                    break;
                foreach ($invoices as $invoice) {
                    $i++;
                    //create deposit
                    if (!empty($invoice->Deposit)) {
                        $qbId = $invoice->Id;
                        $invoices = $this->CI->mdl_invoices->find_all(['invoice_qb_id' => $qbId]);
                        if(!$invoices)
                            continue;
                        $invoiceDB = array_shift($invoices);
                        $paymentToDB = getDepositToDB($invoiceDB->estimate_id, $invoice->Deposit, $invoice->PaymentMethodRef, $paymentMethods);
                        $this->CI->mdl_clients->insert_payment($paymentToDB);
                    }

                }
            }
            deleteLogsInTmp();
            return TRUE;
        }
        return FALSE;
    }
}
