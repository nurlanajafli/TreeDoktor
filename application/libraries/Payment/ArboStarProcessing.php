<?php

use application\models\PaymentTransaction;
use application\modules\internalPayments\models\InternalPayment;
use Carbon\Carbon;
use application\modules\clients\models\ClientLetter;
use application\modules\estimates\models\Estimate;

class ArboStarProcessing
{
    /**
     * @var CI_Controller $CI
     */
    public $CI;
    public $log = [];
    public $rData = [];
    public $uploadFileError = '';

    /**
     * @param array|null $params = [
     *     'internal_payment' => bool
     * ]
     * @throws Exception
     */
    public function __construct(?array $params = null)
    {
        $this->CI =& get_instance();
        $this->CI->load->helper(['payment_log', 'email']);
        $this->CI->load->model('mdl_estimates', 'mdl_estimates');
        $this->CI->load->model('mdl_services_orm', 'mdl_services_orm');
        $this->CI->load->model('mdl_clients', 'mdl_clients');
        $this->CI->load->model('mdl_invoices', 'mdl_invoices');
        $this->CI->load->model('mdl_client_payments', 'mdl_client_payments');
        $this->CI->load->model('mdl_invoice_status', 'mdl_invoice_status');
        $this->CI->load->model('mdl_est_status', 'mdl_est_status');

        $this->CI->load->library('Common/InvoiceActions');
        $this->CI->load->library('Common/EstimateActions');

        $this->CI->load->driver('payment');

        if ($params) {
            if (isset($params['internal_payment']) && $params['internal_payment'] === true) {
                $driver = config_item('int_pay_driver') ?? 'bambora';
                $newDriver = $this->CI->payment->setNewAdapter($driver);

                if ($driver !== $newDriver) {
                    throw new Exception('Wrong payment driver');
                }

                $this->CI->payment->enabled = true;
                $this->CI->payment->internalPayment = true;
            }
        }
    }

    /**
     * Get adapter name
     *
     * @return string
     */
    public function getAdapter(): string
    {
        return $this->CI->payment->getAdapter();
    }

    /**
     * Set adapter name
     *
     * @param string|null $adapter
     * @return string
     */
    public function setAdapter(string $adapter = null): string
    {
        return $this->CI->payment->setNewAdapter($adapter);
    }

    /**
     * Get card form
     *
     * @param array $billingData = [
     *     'customer_id' => (int),
     *     'name' => (string),
     *     'address' => (string),
     *     'city' => (string),
     *     'state' => (string),
     *     'zip' => (string),
     *     'country' => (string),
     *     'phone' => (string|null),
     *     'email' => (string|null),
     *     'profile_id' => (string|null),
     *     'internal_payment' => [(bool)]   // is internal payment
     * ]
     * @param string|null $adapter
     * @return array
     */
    public function getCardForm(array $billingData, $adapter = false): array
    {
        try {
            $response = $this->CI->payment->getForm($this->getClientData($billingData), $adapter);
        } catch (PaymentException $e) {
            return ['status' => 'error', 'error' => $e->getMessage()];
        }

        $view_data = [
            'customer_id' => $billingData['customer_id'] ?? 0,
            'customer_name' => $billingData['name'] ?? 'Undefined',
            'driver_form' => $response,
            'payment_driver' => $this->CI->payment->getAdapter(),
            'internal_payment' => $billingData['internal_payment'] ?? false
        ];

        return ['status' => 'ok', 'html' => $this->CI->load->ext_view(__DIR__ . '/views/card_form',  $view_data, true)];
    }

    /**
     * Get client card form
     *
     * @param array $data
     * @param bool $adapter
     * @param string $dir
     * @return string
     */
    public function getClientCardForm(array $data, $adapter = false, string $dir = 'form'): string
    {
        $view_data = $this->getClientCardFormData($data['billingData'], $adapter, $dir);
        return $this->CI->load->ext_view(__DIR__ . '/views/client_card_form', $data + $view_data, true);
    }

    /**
     * Get client card form for portal
     *
     * @param array $data
     * @param bool $adapter
     * @param string $dir
     * @return string
     */
    public function getPortalClientCardForm(array $data, $adapter = false, string $dir = 'form'): string
    {
        $view_data = $this->getClientCardFormData($data['billingData'], $adapter, $dir);
        return $this->CI->load->ext_view(__DIR__ . '/views/portal_client_card_form', $data + $view_data, true);
    }

    /**
     * @param $billingData
     * @param $adapter
     * @param string $dir
     * @return array
     */
    public function getClientCardFormData($billingData, $adapter, string $dir = 'form')
    {
        try {
            $response = $this->CI->payment->getForm($this->getClientData($billingData), $adapter, $dir);
        } catch (PaymentException $e) {
            $response = $e->getMessage();
        }

        return [
            'driver_form' => $response,
            'payment_driver' => $this->CI->payment->getAdapter()
        ];
    }

    /**
     * Get mobile card form
     *
     * @param array $billingData = [
     *     'customer_id' => (int),
     *     'name' => (string),
     *     'address' => (string),
     *     'city' => (string),
     *     'state' => (string),
     *     'zip' => (string),
     *     'country' => (string),
     *     'phone' => (string|null),
     *     'email' => (string|null),
     *     'profile_id' => (string|null),
     *     'internal_payment' => [(bool)]   // is internal payment
     * ]
     * @param string|null $adapter
     * @return string
     */
    public function getMobileCardForm(array $billingData, $adapter = false): string
    {
        try {
            $response = $this->CI->payment->getForm($this->getClientData($billingData), $adapter, 'mobile_form');
        } catch (PaymentException $e) {
            $response = $e->getMessage();
        }

        $view_data = [
            'authorization' => $billingData['authorization'] ?? false,
            'customer_id' => $billingData['customer_id'] ?? 0,
            'customer_name' => $billingData['name'] ?? 'Undefined',
            'driver_form' => $response,
            'payment_driver' => $this->CI->payment->getAdapter(),
            'internal_payment' => $billingData['internal_payment'] ?? false
        ];

        return $this->CI->load->ext_view(__DIR__ . '/views/mobile_card_form',  $view_data, true);
    }

    /**
     * Get profile cards
     *
     * @param string $profileId
     * @param string|null $adapter
     * @return array
     * @throws PaymentException
     */
    public function profileCards(string $profileId, $adapter = false): array
    {
        if (empty($profileId)) {
            throw new PaymentException('No required data');
        }

        try {
            return $this->CI->payment->profileCards($profileId, $adapter);
        }
        catch (PaymentException $e) {
            throw new PaymentException($e->getMessage());
        }
    }

    /**
     * Get card by ID
     *
     * @param string $profileId
     * @param int $cardId
     * @param string|bool $adapter
     * @return array
     * @throws PaymentException
     */
    public function getCard(string $profileId, int $cardId, $adapter = false): array
    {
        if (empty($profileId) || empty($cardId)) {
            throw new PaymentException('No required data');
        }

        try {
            return $this->CI->payment->getCard($profileId, $cardId, $adapter);
        }
        catch (PaymentException $e) {
            throw new PaymentException($e->getMessage());
        }
    }

    /**
     * Add card to profile
     *
     * @param string $profileId
     * @param array $billingData = [
     *     'customer_id' => (int),
     *     'name' => (string),
     *     'address' => (string),
     *     'city' => (string),
     *     'state' => (string),
     *     'zip' => (string),
     *     'country' => (string),
     *     'phone' => (string|null),
     *     'email' => (string|null),
     *     'profile_id' => (string|null),
     *     'internal_payment' => [(bool)]   // is internal payment
     * ]
     * @param string|array $token
     * @param string $cardholderName
     * @param array|false $additional
     * @param string|null $adapter
     * @return bool
     * @throws PaymentException
     */
    public function profileAddCard(string $profileId, array $billingData, $token, string $cardholderName, $additional = false, $adapter = false): bool
    {
        if (empty($profileId) || empty($token)) {
            throw new PaymentException('No required data');
        }

        try {
            return $this->CI->payment->profileAddCard($profileId, $billingData, $token, $cardholderName, $additional, $adapter);
        }
        catch (PaymentException $e) {
            throw new PaymentException($e->getMessage());
        }
    }

    /**
     * Create profile
     *
     * @param array $billingData = [
     *     'customer_id' => (int),
     *     'name' => (string),
     *     'address' => (string),
     *     'city' => (string),
     *     'state' => (string),
     *     'zip' => (string),
     *     'country' => (string),
     *     'phone' => (string|null),
     *     'email' => (string|null),
     *     'profile_id' => (string|null),
     *     'internal_payment' => [(bool)]   // is internal payment
     * ]
     * @param string|array $token
     * @param string $cardholderName
     * @param array|false $additional
     * @param string|null $adapter
     * @return string
     * @throws PaymentException
     */
    public function createProfile(array $billingData, $token, string $cardholderName, $additional = false, $adapter = false): string
    {
        if (empty($billingData) || empty($token)) {
            throw new PaymentException('No required data');
        }

        try {
            return $this->CI->payment->createProfile($billingData, $token, $cardholderName, $additional, $adapter);
        }
        catch (PaymentException $e) {
            throw new PaymentException($e->getMessage());
        }
    }

    /**
     * Delete profile card
     *
     * @param string $profileId
     * @param $cardId
     * @param string|null $adapter
     * @return bool
     * @throws PaymentException
     */
    public function profileDeleteCard(string $profileId, $cardId, $adapter = false): bool
    {
        if (empty($profileId) || empty($cardId)) {
            throw new PaymentException('No required data');
        }

        try {
            return $this->CI->payment->profileDeleteCard($profileId, $cardId, $adapter);
        }
        catch (PaymentException $e) {
            throw new PaymentException($e->getMessage());
        }
    }

    /**
     * Make client payment
     *
     * @param string $method
     * @param array $data
     * @param array $paymentData
     * @return array
     * @throws PaymentException
     */
    public function pay(string $method, array $data, array $paymentData = []): array
    {
        try {
            if (is_array($data['contact'])) {
                $data['contact'] = (object)$data['contact'];
            }

            $this->rData = [
                'client_id' => $data['client']->client_id,
                'estimate_id' => $data['estimate']->estimate_id,
                'estimate_no' => $data['estimate']->estimate_no,
                'invoice_no' => !empty($data['invoice']) ? $data['invoice']->invoice_no : null,
                'workorder_no' => !empty($data['workorder']) ? $data['workorder']->workorder_no : null,
                'lead_id' => $data['estimate']->lead_id,
                'amount' => $data['amount'],
                'brand_id' => get_brand_id(element('estimate', $data, []), element('client', $data, []))
            ];

            if (isset($data['contact']->cc_email) && (bool)filter_var($data['contact']->cc_email, FILTER_VALIDATE_EMAIL)) {
                $this->rData['client_email'] = $data['contact']->cc_email;
            }

            $this->rData['client_name'] = isset($data['contact']->cc_name) && $data['contact']->cc_name !== ""
                ? $data['contact']->cc_name
                : $data['client']->client_name;

            $transId = null;
            $amount = $data['amount'];
            $paymentDate = Carbon::now();
            switch ($method) {
                case config_item('default_cc'):
                    $ccRes = $this->ccProcessing($data, $paymentData);
                    $paymentFile = $ccRes['pdf'] ?: null;
                    $transId = $ccRes['transaction']->payment_transaction_id;
                    $paymentDate = new Carbon($ccRes['transaction']->payment_transaction_date);
                    $amount = $ccRes['transaction']->payment_transaction_amount;
                    break;
                default:
                    $paymentFile = $data['file'] ?: null;
            }
            $clientPayment['estimate_id'] = $data['estimate']->estimate_id;
            $clientPayment['payment_method_int'] = $method;
            $clientPayment['payment_trans_id'] = $transId;
            $clientPayment['payment_type'] = $data['type'] ?? 'deposit';
            $clientPayment['payment_date'] = $data['date'] ?: $paymentDate->timestamp;

            $excludedAmount = 0;

            if (isset($data['fee'])) {
                $excludedAmount = config_item('cc_extra_fee_service_id') ? 0 : $data['fee'];
            }

            $clientPayment['payment_amount'] = isset($data['fee']) ? $amount - $excludedAmount : $amount;
            $clientPayment['payment_fee'] = $data['fee'] ?? 0;
            $clientPayment['payment_fee_percent'] = $data['fee_percent'] ?? 0;
            $clientPayment['payment_tips'] = $data['tips'] ?? 0;
            $clientPayment['payment_file'] = $paymentFile;
            $clientPayment['payment_author'] = $data['user_id'] ?? 0;
            $clientPayment['payment_notes'] = $data['notes'] ?? null;
            $paymentID = $this->CI->mdl_clients->insert_payment($clientPayment);

            if ($clientPayment['payment_fee'] > 0) {
                $this->setFee($clientPayment['estimate_id']);
            }

            $total = $this->CI->mdl_estimates->update_estimate_balance($data['estimate']->estimate_id);//update estimate balance

            if (isset($data['workorder'], $data['workorder']->workorder_no) && $data['workorder']->workorder_no) {
                $total = $this->CI->mdl_estimates_orm->getCompletedOnly($data['estimate']->estimate_id);
            }

            if ($method == config_item('default_cc')) {
                $this->sendTransactionEmail($this->rData, $ccRes['transaction']);
            }

            if ($data['invoice']) {
                $updateInvoiceData['overpaid'] = $total < 0 ? 1 : null;

                if ($method == config_item('default_cc') && isset($data['extra']['is_client'])) {
                    $updateInvoiceData['paid_by_cc'] = $data['invoice']->paid_by_cc + 1;
                }

                $this->CI->mdl_invoices->update_invoice(
                    $updateInvoiceData,
                    ["id" => $data['invoice']->id]
                );

                if ($total <= 0) {
                    $newStatus = $this->CI->mdl_invoice_status->get_by(['completed' => 1]);
                    Modules::run('invoices/invoices/change_invoice_status', [
                        'invoice_id' => $data['invoice']->id,
                        'pre_invoice_status' => $data['invoice']->in_status,
                        'new_invoice_status' => $newStatus->invoice_status_id,
                        'payment_mode' => $this->methodToText($method),
                        'overpaid' => $total < 0 ? 1 : null
                    ]);
                    $this->rData['client_address'] = $data['client']->client_address;
                    $this->rData['invoice_id'] = !empty($data['invoice']) ? $data['invoice']->id : null;

                    $letter = ClientLetter::where('system_label', 'invoice_paid_thanks')->first();
                    $thanks = [
                        'client_id' => $this->rData['client_id'],
                        'estimate_id' => $this->rData['estimate_id'],
                        'workorder_id' => $data['invoice']->workorder_id,
                        'invoice_id' => $this->rData['invoice_id'],
                        'email_template_id' => !empty($letter) ? $letter->email_template_id : ''
                    ];
                }

                if($data['notes']){
                    $note['notes'] = "Payment Notes: " . $data['notes'];
                }

                if (Carbon::parse($data['date'])->format('d-m-Y') != Carbon::now()->format('d-m-Y')){
                    $note['date'] = "Payment date set: " . Carbon::parse($data['date'])->format('d-m-Y H:i') ;
                }

                make_notes(
                    $data['client']->client_id,
                    "Make invoice by " . '"' . $this->methodToText($method) . '"' . " for " . $data['estimate']->estimate_no
                    . ".<br>Payment Amount: " . money($amount)
                    . ".<br>" . implode('<br />', $note)
                    ,
                    'system',
                    $data['estimate']->lead_id
                );

            } else {
                if ($method == config_item('default_cc') && isset($data['extra']['is_client'])) {
                    $this->CI->mdl_estimates->update_estimates(
                        ['paid_by_cc' => $data['estimate']->paid_by_cc + 1],
                        ["estimate_id" => $data['estimate']->estimate_id]
                    );
                }

                $confirmed = $this->CI->mdl_est_status->get_by(['est_status_confirmed' => 1]);

                if ($data['estimate']->status_id != $confirmed->est_status_id) {
                    $this->CI->estimateactions->setEstimateId($data['estimate']->estimate_id);

                    if (isset($data['extra']) && isset($data['extra']['is_client']) && !empty($data['extra']['is_client'])) {
                        $this->CI->estimateactions->setIsConfirmedWeb(true);
                    }
                    $this->CI->estimateactions->confirm('deposit');

                    $this->sendEstimateEmail($data, $this->rData);
                }

                $note = [];

                if($data['notes']){
                    $note['notes'] = "Payment Notes: " . $data['notes'];
                }

                if (Carbon::parse($data['date'])->format('d-m-Y') != Carbon::now()->format('d-m-Y')){
                    $note['date'] = "Payment date set: " . Carbon::parse($data['date'])->format('d-m-Y H:i') ;
                }

                make_notes(
                    $data['client']->client_id,
                    "Make payment by " . '"' . $this->methodToText($method) . '"' . " for " . $data['estimate']->estimate_no
                        . ".<br>Payment Amount: " . money($amount)
                        . ".<br>" . implode('<br />', $note)
                    ,
                    'system',
                    $data['estimate']->lead_id
                );
            }
            pushJob('quickbooks/payment/syncpaymentinqb', serialize(['id' => $paymentID, 'qbId' => '']));
        } finally {
            $this->log = [];
            $this->rData = [];
        }

        return [
            'payment_amount' => $amount,
            'payment_file' => base_url($this->getFilePath($data['client']->client_id, $data['estimate']->estimate_no) . $paymentFile),
            'total' => $total,
            'thanks' => $thanks ?? []
        ];
    }

    /**
     * Delete client payment
     *
     * @param array $data
     * @return bool
     */
    public function deletePayment(array $data): bool
    {
        $this->CI->load->model('mdl_client_payments');
        $this->CI->load->model('mdl_estimates');

        if ($data['payment_data']->payment_file) {
            $filePath = $this->getFilePath(
                $data['estimate_data']->client_id,
                $data['estimate_data']->estimate_no
            ) . $data['payment_data']->payment_file;
            bucket_unlink($filePath);
        }
        $this->CI->mdl_client_payments->delete($data['payment_data']->payment_id);

        make_notes(
            $data['estimate_data']->client_id,
            "Delete payment by " . $this->methodToText($data['payment_data']->payment_method_int) . " for "
            . $data['estimate_data']->estimate_no . ".<br>Payment Amount: " . money($data['payment_data']->payment_amount),
            'system',
            $data['estimate_data']->lead_id
        );
        $this->CI->mdl_estimates->update_estimate_balance($data['payment_data']->estimate_id);

        pushJob(
            'quickbooks/payment/syncpaymentinqb',
            serialize(['id' => $data['payment_data']->payment_id, 'qbId' => $data['payment_data']->payment_qb_id])
        );

        return true;
    }

    /**
     * Edit client payment
     *
     * @param array $data
     * @param array $payload
     * @return bool
     */
    public function editPayment(array $data, array $payload): bool
    {
        $this->CI->load->model('mdl_client_payments');
        $this->CI->load->model('mdl_estimates');
        $this->CI->load->model('mdl_invoices');

        $newData = [];
        $note = [];

        if (isset($payload['estimate_id']) && $payload['estimate_id'] != $data['payment_data']->estimate_id) {
            if($newEstimate = $this->CI->mdl_estimates->find_by_id($payload['estimate_id'])) {
                $newData['estimate_id'] = $newEstimate->estimate_id;
                $note[] = 'Estimate changed from <strong>' . $data['estimate_data']->estimate_no . '</strong> to <strong>'
                    . $newEstimate->estimate_no . '</strong>';
            }
        }

        if ($data['payment_data']->payment_method_int != config_item('default_cc')) {
            if (isset($payload['payment_type']) && $payload['payment_type'] != $data['payment_data']->payment_type) {
                $newData['payment_type'] = $payload['payment_type'];
                $note[] = 'Payment type changed from <strong>' . $data['payment_data']->payment_type . '</strong> to <strong>'
                    . $payload['payment_type'] . '</strong>';
            }

            if (isset($payload['payment_method']) && $payload['payment_method'] != $data['payment_data']->payment_method_int) {
                $newData['payment_method_int'] = $payload['payment_method'];
                $note[] = 'Payment method changed from <strong>' . $this->methodToText($data['payment_data']->payment_method_int)
                    . '</strong> to <strong>' . $this->methodToText($payload['payment_method']) . '</strong>';
            }

            if (isset($data['amount']) && $data['amount'] != $data['payment_data']->payment_amount) {
                $newData['payment_amount'] = $data['amount'];
                $note[] = 'Amount changed from <strong>' . money($data['payment_data']->payment_amount) . '</strong> to <strong>'
                    . money($data['amount']) . '</strong>';
            }

            if (isset($payload['payment_date'])) {
                $paymentDate = Carbon::createFromFormat(getDateFormat(), $payload['payment_date']);
                $oldDate = Carbon::createFromTimestamp($data['payment_data']->payment_date);

                if ($paymentDate->startOfDay()->notEqualTo($oldDate->startOfDay())) {
                    $newData['payment_date'] = $paymentDate->timestamp;
                    $note[] = 'Payment date changed from <strong>' . $oldDate->format('Y-m-d') . '</strong> to <strong>'
                        . $paymentDate->format('Y-m-d') . '</strong>';
                }
            }

            if ($data['file']) {
                if ($data['payment_data']->payment_file) {
                    $filePath = $this->getFilePath($data['estimate_data']->client_id,
                            $data['estimate_data']->estimate_no) . $data['payment_data']->payment_file;
                    bucket_unlink($filePath);
                }
                $newData['payment_file'] = $data['file'];
                $note[] = 'Payment file changed. Old file is deleted';
            }

            if (isset($payload['payment_notes']) && $payload['payment_notes'] != $data['payment_data']->payment_notes) {
                $newData['payment_notes'] = $payload['payment_notes'];
                $note[] = 'Notes changed from <strong>' . $data['payment_data']->payment_notes . '</strong> to <strong>'
                    . $payload['payment_notes'] . '</strong>';
            }
        }

        if (empty($newData)) {
            return true;
        }

        $this->CI->mdl_client_payments->update($data['payment_data']->payment_id, $newData);

        make_notes(
            $data['estimate_data']->client_id,
            "Edit payment <strong>" . $data['payment_data']->payment_id . "</strong> by "
            . $this->methodToText($data['payment_data']->payment_method_int) . " for " . $data['estimate_data']->estimate_no
            . ".<br>" . implode('<br />', $note),
            'system',
            $data['estimate_data']->lead_id
        );
        $this->CI->mdl_estimates->update_estimate_balance($data['payment_data']->estimate_id);
        $this->CI->mdl_invoices->update_all_invoice_interes($data['payment_data']->estimate_id);

        if (isset($newData['estimate_id'])) {
            $this->CI->mdl_estimates->update_estimate_balance($newData['estimate_id']);
            $this->CI->mdl_invoices->update_all_invoice_interes($newData['estimate_id']);
        }

        pushJob(
            'quickbooks/payment/syncpaymentinqb',
            serialize(['id' => $data['payment_data']->payment_id, 'qbId' => ''])
        );

        return true;
    }

    /**
     * Refund client payment
     *
     * @param array $data
     * @return bool
     * @throws PaymentException
     */
    public function refundClientPayment(array $data): bool
    {
        $this->CI->load->model('mdl_client_payments');
        $this->CI->load->model('mdl_estimates');

        if (!$data['payment_data']->payment_trans_id) {
            throw new PaymentException("Not found transaction ID");
        }

        if (!$transaction = PaymentTransaction::find($data['payment_data']->payment_trans_id)) {
            throw new PaymentException("Not found Transaction");
        }

        if (!$transaction->payment_driver || !$this->CI->payment->validateAdapter($transaction->payment_driver)) {
            throw new PaymentException("Not valid payment driver in this transaction");
        }

        $paymentDriver = $this->CI->payment->setNewAdapter($transaction->payment_driver);

        $logData = [
            'payment_id' => $data['payment_data']->payment_id,
            'payment_driver' => $transaction->payment_driver,
            'amount' => $data['amount'],
            'fee' => $data['fee'],
        ];
        toLog($this->log, $logData);

        $amount = $data['amount'];

        if ($data['fee']) {
            $amount += $data['fee'];
        }

        $trnData = [
            'payment_transaction_status' => Payment::TRANSACTION_STATUS_NOT_PROCESSED,
            'client_id' => $data['estimate_data']->client_id,
            'estimate_id' => $data['estimate_data']->estimate_id,
            'invoice_id' => !empty($data['invoice_data']) ? $data['invoice_data']->id : null,
            'payment_driver' => $paymentDriver,
            'payment_transaction_amount' => -1 * $amount,
            'payment_transaction_log' => json_encode($this->log, JSON_PRETTY_PRINT),
            'payment_transaction_ref_id' => $transaction->payment_transaction_id,
            'payment_transaction_type' => 'refund',
        ];

        try {
            $refundResult = $this->CI->payment->refund($transaction->payment_transaction_remote_id, $trnData, abs($amount));
        } catch (PaymentException $e) {
            throw new PaymentException("Payment processing failed: ".$e->__toString());
        }

        if (!$refundResult) {
            throw new PaymentException("Payment processing failed: Unexpected error");
        }

        if (isset($refundResult['error'])) {
            throw new PaymentException("Payment processing failed: " . $refundResult['message']);
        }

        $client_update = [
            'payment_amount' => (float)$data['payment_data']->payment_amount - abs($data['amount'])
        ];

        if ($data['fee']) {
            $client_update['payment_fee'] = (float)$data['payment_data']->payment_fee - abs($data['fee']);
        }

        $this->CI->mdl_client_payments->update($data['payment_data']->payment_id, $client_update);

        make_notes(
            $data['estimate_data']->client_id,
            "Refund " . money($amount) . " payment <strong>" . $data['payment_data']->payment_id . "</strong> by "
            . $this->methodToText($data['payment_data']->payment_method_int) . " for " . $data['estimate_data']->estimate_no
            . ".<br> New payment amount is " . money((float)$data['payment_data']->payment_amount - abs($data['amount'])),
            'system',
            $data['estimate_data']->lead_id
        );
        $this->CI->mdl_estimates->update_estimate_balance($data['payment_data']->estimate_id);

        pushJob('quickbooks/payment/syncpaymentinqb',
            serialize(['id' => $data['payment_data']->payment_id, 'qbId' => $data['payment_data']->payment_qb_id])
        );
        $this->setFee($data['estimate_data']->estimate_id);

        return true;
    }

    /**
     * Get transaction
     *
     * @param int $transactionId
     * @param string|null $adapter
     * @return mixed
     * @throws PaymentException
     */
    public function getTransaction(int $transactionId, $adapter = false) {
        if (empty($transactionId)) {
            throw new PaymentException('No required data');
        }

        try {
            return $this->CI->payment->getTransaction($transactionId, $adapter);
        }
        catch (PaymentException $e) {
            throw new PaymentException($e->getMessage());
        }
    }

    /**
     * Check transaction
     *
     * @param int $transactionId
     * @param string|null $adapter
     * @return array
     * @throws PaymentException
     */
    public function checkTransaction(int $transactionId, $adapter = false): array
    {
        if (empty($transactionId)) {
            throw new PaymentException('No transaction ID');
        }

        try {
            return $this->CI->payment->checkTransaction($transactionId, $adapter);
        }
        catch (PaymentException $e) {
            throw new PaymentException($e->getMessage());
        }
    }

    /**
     * @param $method
     * @return mixed|string
     */
    public function methodToText($method)
    {
        $paymentMethods = config_item('payment_methods');
        return $paymentMethods[$method] ?? 'Undefined';
    }

    /**
     * @param $status
     * @return string
     */
    public function statusToText($status): string
    {
        return $this->CI->payment->statusToText($status);
    }

    /**
     * Upload file
     *
     * @param array $data
     * @param string|null $tmpname
     * @return false|string
     */
    public function uploadFile(array $data, ?string $tmpname = null)
    {
        $this->uploadFileError = '';
        $mimesToExt = [
            'application/octet-stream' => 'pdf',
            'image/jpeg' => 'jpg',
            'image/jpg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
        ];
        $path = $this->getFilePath($data['client_id'], $data['estimate_no']);
        $file_no = 1;
        $files = bucketScanDir($path);

        if (!empty($files)) {
            sort($files, SORT_NATURAL);
            preg_match('/payment_([0-9]{1,})/is', $files[count($files) - 1], $num);//countOk
            $file_no = isset($num[1]) ? ($num[1] + 1) : 1;
        }
        $uploadFilename = 'payment_' . $file_no;

        if ($tmpname) {
            $ext = pathinfo($tmpname, PATHINFO_EXTENSION);
            $uploadFilename = $uploadFilename . '.' . $ext;
            bucket_move($tmpname, $path . $uploadFilename, ['ContentType' => 'application/pdf']);
            @unlink($tmpname);
        } elseif (isset($_FILES) && isset($_FILES['payment_file']) && isset($_FILES['payment_file']['error']) && $_FILES['payment_file']['error'] === 0) {
            $ext = pathinfo($_FILES['payment_file']['name'], PATHINFO_EXTENSION);

            if (!$ext) {
                $imgSize = @getimagesize($_FILES['payment_file']['tmp_name']);
                $type = $imgSize['mime'] ?? $_FILES['payment_file']['type'];
                $ext = $mimesToExt[$type] ?? $ext;
                $_FILES['payment_file']['name'] = $ext ? $_FILES['payment_file']['name'] . '.' . $ext : $_FILES['payment_file']['name'];
            }
            $uploadFilename = $uploadFilename . '.' . $ext;
            $config['allowed_types'] = 'gif|jpg|jpeg|png|pdf';
            $config['overwrite'] = true;
            $this->CI->load->library('upload');
            $config['upload_path'] = $path;
            $config['file_name'] = $uploadFilename;
            $this->CI->upload->initialize($config);

            if (!$this->CI->upload->do_upload('payment_file')) {
                $this->uploadFileError = $this->CI->upload->display_errors();
                return false;
            }
        } else {
            return false;
        }

        $note = 'Add Payment File for ' . $data['estimate_no'] . ': <a href="' . base_url($path . $uploadFilename) . '">' . $uploadFilename . '</a>';
        make_notes($data['client_id'], $note, 'attachment', $data['lead_id']);

        return $uploadFilename;
    }

    /**
     * Internal payments functions
     */

    /**
     * Make internal payment
     *
     * @param array $paymentDetails = [
     *    'card_id' => (string),
     *    'payment_profile' => (string),
     *    'entity_description' => (string), // 'SMS subscription'
     *    'entity_item_name' => (string),   // $subscription->name  'Lite'
     *    'amount' => (float)               // $subscription->amount
     *    'entity' => (object)              // entity object
     * ]
     * @param string|null $orderNo
     * @return array
     * @throws PaymentException
     */
    public function internalPay(array $paymentDetails, string $orderNo = null): array
    {
        toLog($this->log, $paymentDetails);

        $orderNo = $orderNo ?? time() . '_' . random_int(1000,9999);
        $paymentDriver = $this->CI->payment->getAdapter();

        toLog($this->log, $paymentDetails + ['driver' => $paymentDriver, 'order_no' => $orderNo]);

        $trnData = [
            'payment_transaction_status' => Payment::TRANSACTION_STATUS_NOT_PROCESSED,
            'client_id' => 0,
            'estimate_id' => 0,
            'invoice_id' => null,
            'payment_driver' => $paymentDriver,
            'payment_transaction_amount' => $paymentDetails['amount'],
            'payment_transaction_order_no' => $orderNo,
            'payment_transaction_log' => json_encode($this->log, JSON_PRETTY_PRINT)
        ];

        $paymentData = [
            'payment_profile' => $paymentDetails['payment_profile'],
            'card_id' => $paymentDetails['card_id']
        ];

        try {
            $paymentResult = $this->CI->payment->pay($paymentData, $paymentDetails['amount'], $trnData, $orderNo);
        } catch (PaymentException $e) {
            throw new PaymentException("Payment processing failed: " . $e->getMessage());
        }

        if (!$paymentResult || empty($paymentResult['transaction'])) {
            throw new PaymentException("Payment processing failed: Unexpected error");
        }

        $transaction = $paymentResult['transaction'];

        if ((!empty($paymentDetails['entity']) && is_object($paymentDetails['entity'])) || $transaction->payment_transaction_approved == 1) {
            $intPayment = InternalPayment::create([
                'user_id' => request()->user()->id ?? 0, // 0 if auto renewal
                'amount' => $paymentDetails['amount'],
                'transaction_id' => $transaction->payment_transaction_id,
                'transaction_approved' => $transaction->payment_transaction_approved == 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
        }

        if (!empty($paymentDetails['entity']) && is_object($paymentDetails['entity'])) {
            // associate paymentable entity to internal payment
            $intPayment->paymentable()->associate($paymentDetails['entity'])->save();
        }

        $this->sendIntPayTransactionEmail($paymentDetails, $transaction);

        if (isset($paymentResult['error'])) {
            throw new PaymentException("Payment processing failed: " . $paymentResult['message']);
        }

        if ($transaction->payment_transaction_approved == 1) {
            $this->genIntPayTransactionPdf($paymentDetails, $transaction);
        }

        if (in_array($transaction->payment_transaction_status, [
            Payment::TRANSACTION_STATUS_ERROR,
            Payment::TRANSACTION_STATUS_DECLINED,
            Payment::TRANSACTION_STATUS_CANCELED,
            Payment::TRANSACTION_STATUS_NOT_PROCESSED,
            Payment::TRANSACTION_STATUS_REVIEW
        ])) {
            throw new PaymentException("Payment processing failed: " . $transaction->payment_transaction_message);
        }

        return ['transaction' => $transaction, 'intPayment' => $intPayment ?? null];
    }

    /**
     * Refund internal payment
     *
     * @param array $data = [
     *    'payment_data' => (InternalPayment),
     *    'amount' => (float)
     * ]
     * @return bool
     * @throws PaymentException
     */
    public function internalRefund(array $data): bool
    {
        if (!$data['payment_data']->transaction_id) {
            throw new PaymentException("Not found transaction ID");
        }

        if (!$transaction = PaymentTransaction::find($data['payment_data']->transaction_id)) {
            throw new PaymentException("Not found Transaction");
        }

        if (!$transaction->payment_driver || !$this->CI->payment->validateAdapter($transaction->payment_driver)) {
            throw new PaymentException("Not valid payment driver in this transaction");
        }

        $paymentDriver = $this->CI->payment->setNewAdapter($transaction->payment_driver);

        $logData = [
            'payment_id' => $data['payment_data']->id,
            'payment_driver' => $paymentDriver,
            'amount' => $data['amount'],
        ];
        toLog($this->log, $logData);

        $amount = $data['amount'];

        $trnData = [
            'payment_transaction_status' => Payment::TRANSACTION_STATUS_NOT_PROCESSED,
            'client_id' => 0,
            'estimate_id' => 0,
            'invoice_id' => null,
            'payment_driver' => $paymentDriver,
            'payment_transaction_amount' => -1 * $amount,
            'payment_transaction_log' => json_encode($this->log, JSON_PRETTY_PRINT),
            'payment_transaction_ref_id' => $transaction->payment_transaction_id,
            'payment_transaction_type' => 'refund',
        ];

        try {
            $refundResult = $this->CI->payment->refund($transaction->payment_transaction_remote_id, $trnData, abs($amount));
        } catch (PaymentException $e) {
            throw new PaymentException("Payment processing failed: " . $e->__toString());
        }

        if (!$refundResult) {
            throw new PaymentException("Payment processing failed: Unexpected error");
        }

        if (isset($refundResult['error'])) {
            throw new PaymentException("Payment processing failed: " . $refundResult['message']);
        }

        $data['payment_data']->amount -= abs($data['amount']);
        $data['payment_data']->updated_at = Carbon::now();
        $data['payment_data']->save();

        return true;
    }


    /**
     * Private functions
     */

    /**
     * Client credit card processing
     *
     * @param array $data
     * @param array $paymentData
     * @return array
     * @throws PaymentException
     */
    private function ccProcessing(array $data, array $paymentData): array
    {
        $logData = [
            'client_id' => $data['client']->client_id,
            'contact' => $data['contact']->cc_id ?? null,
            'estimate_id' => $data['estimate']->estimate_id,
            'invoice_id' => !empty($data['invoice']) ? $data['invoice']->id : null,
            'type' => $data['type'],
            'payment_driver' => $data['payment_driver'],
            'amount' => $data['amount']
        ];
        toLog($this->log, $logData);

        $orderNo = (!empty($data['invoice']) ? $data['invoice']->invoice_no : $data['estimate']->estimate_no) . "_" . random_int(100,9999);
        $paymentDriver = $this->CI->payment->setNewAdapter($data['payment_driver']);

        toLog($this->log, $paymentData + ['driver' => $paymentDriver, 'order' => $orderNo]);

        $trnData = [
            'payment_transaction_status' => Payment::TRANSACTION_STATUS_NOT_PROCESSED,
            'client_id' => $data['client']->client_id,
            'estimate_id' => $data['estimate']->estimate_id,
            'invoice_id' => !empty($data['invoice']) ? $data['invoice']->id : null,
            'payment_driver' => $paymentDriver,
            'payment_transaction_amount' => $data['amount'],
            'payment_transaction_order_no' => $orderNo,
            'payment_transaction_log' => json_encode($this->log, JSON_PRETTY_PRINT)
        ];

        try {
            $paymentResult = $this->CI->payment->pay($paymentData, $data['amount'], $trnData, $orderNo);
        } catch (PaymentException $e) {
            throw new PaymentException("Payment processing failed: " . $e->getMessage());
        }

        if (!$paymentResult) {
            throw new PaymentException("Payment processing failed: Unexpected error");
        }

        if (isset($paymentResult['error'])) {
            $this->sendTransactionEmail($this->rData, $paymentResult['transaction']);

            throw new PaymentException("Payment processing failed: " . $paymentResult['message']);
        }

        $transaction = $paymentResult['transaction'];

        if ($transaction->payment_transaction_approved == 1) {
            $pdfRes = $this->genTransactionPdf($this->rData, $transaction);
        }

        if (in_array($transaction->payment_transaction_status, [
            Payment::TRANSACTION_STATUS_ERROR,
            Payment::TRANSACTION_STATUS_DECLINED,
            Payment::TRANSACTION_STATUS_CANCELED,
            Payment::TRANSACTION_STATUS_NOT_PROCESSED,
            Payment::TRANSACTION_STATUS_REVIEW
        ])) {
            $this->sendTransactionEmail($this->rData, $transaction);

            throw new PaymentException("Payment processing failed: " . $transaction->payment_transaction_message);
        }

        return ['transaction' => $transaction, 'pdf' => $pdfRes ?? false];
    }

    /**
     * Generate transaction PDF
     *
     * @param $data
     * @param $transaction
     * @return false|string
     */
    private function genTransactionPdf($data, $transaction)
    {
        $this->CI->load->library('mpdf');

        $entity = 'estimate ';
        $entityNo = $data['estimate_no'];

        if ($data['invoice_no']) {
            $entity = 'invoice ';
            $entityNo = $data['invoice_no'];
        }

        $trans_details = "We have received your payment for " . $entity . $entityNo
            . "! Please keep transaction details for your records:<br><br>";

        $trans_details .= "Transaction ID: " . $transaction->payment_transaction_id . "<br>";
        $trans_details .= "Transaction Message: " . $transaction->payment_transaction_message . "<br>";
        $trans_details .= "Transaction Order No.: " . $transaction->payment_transaction_order_no . "<br>";
        $trans_details .= "Amount Paid: $" . $transaction->payment_transaction_amount . "<br>";
        $trans_details .= "Transaction Date: " . $transaction->payment_transaction_date . "<br><br>";

        $this->CI->mpdf->WriteHTML($trans_details);
        $tmpName = sys_get_temp_dir() . '/' . uniqid('', true) . '.pdf';
        $this->CI->mpdf->Output($tmpName, 'F');
        $this->CI->load->unload('mpdf');

        return $this->uploadFile($data, $tmpName);
    }

    /**
     * Generate internal payment transaction PDF
     *
     * @param array $details
     * @param PaymentTransaction $transaction
     * @return bool
     */
    private function genIntPayTransactionPdf(array $details, PaymentTransaction $transaction): bool
    {
        $this->CI->load->library('mpdf');

        $trans_id = $transaction->payment_transaction_id;

        $trans_details = 'We have received your payment for ' . $details['entity_description'] . ' "'
            . $details['entity_item_name'] . '"! Please keep transaction details for your records:<br><br>';

        $trans_details .= "Transaction ID: " . $trans_id . "<br>";
        $trans_details .= "Transaction Message: " . $transaction->payment_transaction_message . "<br>";
        $trans_details .= "Transaction Order No.: " . $transaction->payment_transaction_order_no . "<br>";
        $trans_details .= "Amount Paid: $" . $transaction->payment_transaction_amount . "<br>";
        $trans_details .= "Transaction Date: " . $transaction->payment_transaction_date . "<br><br>";

        $this->CI->mpdf->WriteHTML($trans_details);
        $tmpName = sys_get_temp_dir() . '/' . uniqid('', true) . '.pdf';
        $this->CI->mpdf->Output($tmpName, 'F');
        $this->CI->load->unload('mpdf');

        $path = PAYMENT_FILES_PATH . 'internal/' . substr(md5($trans_id), 0, 3) . '/';

        $ext = pathinfo($tmpName, PATHINFO_EXTENSION);
        $uploadFilename = $trans_id . '.' . $ext;
        bucket_move($tmpName, $path . $uploadFilename, ['ContentType' => 'application/pdf']);
        @unlink($tmpName);

        return true;
    }

    /**
     * Add internal payment transaction email to job
     *
     * @param array $details
     * @param PaymentTransaction|null $transaction
     * @return void
     */
    private function sendIntPayTransactionEmail(array $details, ?PaymentTransaction $transaction): void
    {
        $toEmail = config_item('account_email_address');

        if (empty($toEmail)) {
            return;
        }

        $icons = [
            'visa' => ['img' => 'visa.png', 'title' => 'VISA'],
            'mastercard' => ['img' => 'mc.png', 'title' => 'MasterCard'],
            'amex' => ['img' => 'amex.png', 'title' => 'AMEX'],
            'discover' => ['img' => 'discover.png', 'title' => 'Discover'],
        ];

        $letterData = [
            'entity_description' => $details['entity_description'],
            'entity_item_name' => $details['entity_item_name']
        ];

        if (isset($transaction)) {
            $letterData['message'] = $transaction->payment_transaction_message;
            $letterData['id'] = $transaction->payment_transaction_id;
            $letterData['amount'] = $transaction->payment_transaction_amount;
            $letterData['date'] = $transaction->payment_transaction_date;
            $letterData['payment'] = true;
            $letterData['card'] = $transaction->payment_transaction_card_num;
            $letterData['auth_code'] = $transaction->payment_transaction_auth_code;
            $letterData['card_icon'] =
                $icons[$transaction->payment_transaction_card] ?? ['img' => 'default.png', 'title' => 'CreditCard'];
        } else {
            $letterData['message'] = 'No payment data';
            $letterData['id'] = '';
            $letterData['amount'] = $details["amount"];
            $letterData['date'] = date('Y-m-d H:i:s');
            $letterData['payment'] = false;
        }

        $from_email = config_item('arbostar_email') ?? 'info@arbostar.com';

        $subject = 'Credit Card payment for ' . $details['entity_description'] . ' ' . $details['entity_item_name'];

        if (isset($transaction)) {
            switch ($transaction->payment_transaction_status) {
                case Payment::TRANSACTION_STATUS_SUCCESS:
                case Payment::TRANSACTION_STATUS_PENDING:
                    $subject .= ' is Approved';
                    $letterData['payment'] = true;
                    break;
                case Payment::TRANSACTION_STATUS_DECLINED:
                    $subject .= ' is Declined';
                    $letterData['payment'] = false;
                    break;
                case Payment::TRANSACTION_STATUS_ERROR:
                default:
                    $subject .= ' is Error';
                    $letterData['payment'] = false;
                    break;
            }
        }

        $text = $this->CI->load->view('internalPayments/payment_check', $letterData, true);

        pushJob('common/sendemail', [
            'subject' => $subject,
            'message' => $text,
            'from' => $from_email,
            'from_name' => config_item('arbostar_email_name') ?? 'Arbostar',
            'to' => $toEmail
        ]);
    }


    /**
     * Add transaction email to job
     *
     * @param array $data
     * @param PaymentTransaction|null $transaction
     * @return void
     */
    private function sendTransactionEmail(array $data, ?PaymentTransaction $transaction): void
    {
        if (!isset($data['client_email'])) {
            return;
        }

        $brand_id = $data['brand_id'] ?? false;

        if (!$brand_id) {
            $brand_id = get_brand_id(element('estimate', $data, []), element('client', $data, []));
        }

        $icons = [
            'visa' => ['img' => 'visa.png', 'title' => 'VISA'],
            'mastercard' => ['img' => 'mc.png', 'title' => 'MasterCard'],
            'amex' => ['img' => 'amex.png', 'title' => 'AMEX'],
            'discover' => ['img' => 'discover.png', 'title' => 'Discover'],
        ];
        $letterData['client_id'] = $data["client_id"];

        if (isset($transaction)) {
            $letterData['message'] = $transaction->payment_transaction_message;
            $letterData['id'] = $transaction->payment_transaction_id;
            $letterData['amount'] = $transaction->payment_transaction_amount;
            $letterData['date'] = $transaction->payment_transaction_date;
            $letterData['payment'] = true;
            $letterData['card'] = $transaction->payment_transaction_card_num;
            $letterData['auth_code'] = $transaction->payment_transaction_auth_code;
            $letterData['card_icon'] =
                $icons[$transaction->payment_transaction_card] ?? ['img' => 'default.png', 'title' => 'CreditCard'];
        } else {
            $letterData['message'] = 'No payment data';
            $letterData['id'] = '';
            $letterData['amount'] = $data["amount"];
            $letterData['date'] = date('Y-m-d H:i:s');
            $letterData['payment'] = false;
        }

        $from_email = brand_email($brand_id);

        $subject = 'Credit Card payment for ';
        $estData = null;

        if ($data['invoice_no']) {
            $invoiceObj = $this->CI->mdl_invoices->get_invoice(['estimates.estimate_id' => $data['estimate_id']]);
            $letterData['invoice'] = $data['invoice_no'];
            $data['total'] = $invoiceObj->total_with_hst;
            $data['due'] = round($invoiceObj->due/* - $wasPay*/, 2);
            $letterData['total'] = $data['total'];
            $letterData['due'] = $data['due'];
            $subject .= 'Invoice ' . $data['invoice_no'];
        } elseif (isset($data['workorder_no']) && $data['workorder_no']) {
            $estData = $this->CI->mdl_estimates->estimate_completed_sum_and_hst($data['estimate_id']);
            $letterData['estimate'] = $data['estimate_no'];
            $letterData['workorder'] = $data['workorder_no'];
            $subject .= 'Partial Invoice ' . str_replace('-E', '-I', $data['estimate_no']);
        } else {
            $estData = $this->CI->mdl_estimates->estimate_sum_and_hst($data['estimate_id']);
            $letterData['estimate'] = $data['estimate_no'];
            $subject .= 'Estimate ' . $data['estimate_no'];
        }

        if ($estData) {
            $letterData['total'] = $estData['total'] + $estData['hst'];
            $paymentsSum = $estData['payments'] ?? 0;
            $letterData['due'] = $letterData['total'] - $paymentsSum;// - $wasPay;
        }

        if (isset($transaction)) {
            switch ($transaction->payment_transaction_status) {
                case Payment::TRANSACTION_STATUS_SUCCESS:
                case Payment::TRANSACTION_STATUS_PENDING:
                    $subject .= ' is Approved';
                    $letterData['payment'] = true;
                    break;
                case Payment::TRANSACTION_STATUS_DECLINED:
                    $subject .= ' is Declined';
                    $letterData['payment'] = false;
                    break;
                case Payment::TRANSACTION_STATUS_ERROR:
                default:
                    $subject .= ' is Error';
                    $letterData['payment'] = false;
                    break;
            }
        }

        $text = $this->CI->load->view('payments/payment_check', $letterData, true);

        pushJob('common/sendemail', [
            'subject' => $subject,
            'message' => $text,
            'from' => $from_email,
            'from_name' => brand_name($brand_id),
            'to' => $data["client_email"],
            'client_id' => $data['client_id'],
            'lead_id' => $data['lead_id']
        ]);
    }

    /**
     * Add estimate email to job
     *
     * @param array $data
     * @param array $rData
     * @return void
     */
    private function sendEstimateEmail(array $data, array $rData): void
    {
        if (!isset($rData['client_email'])) {
            return;
        }

        $brand_id = get_brand_id(element('estimate', $data, []), element('client', $data, []));

        $letter = ClientLetter::where(['system_label' => 'confirmed_estimate'])->first();
        $estimate = Estimate::with(['client.primary_contact', 'user', 'lead', 'invoice'])->find($data['estimate']->estimate_id);
        $client = $estimate->client;
        $letter = ClientLetter::compileLetter($letter, $brand_id, [
            'client'    =>  $client,
            'estimate'  =>  $estimate
        ]);

        pushJob('estimates/sendestimate', [
            'estimate_id' => $data['estimate']->estimate_id,
            'from' => ($letter->email_static_sender && $letter->email_static_sender !== '') ? $letter->email_static_sender : brand_email($brand_id),
            'from_name' => brand_name($brand_id),
            'to' => $rData['client_email'],
            'cc' => $letter->email_static_cc,
            'bcc' => $letter->email_static_bcc,
            'body' => $letter->email_template_text,
            'subject' => "Dear " . $rData['client_name']
        ]);
    }

    /**
     * @param $client_id
     * @param $estimate_no
     * @return string
     */
    private function getFilePath($client_id, $estimate_no): string
    {
        return PAYMENT_FILES_PATH . $client_id . '/' . $estimate_no . '/';
    }

    /**
     * Set Fee
     *
     * @param int $estimateId
     * @return void
     */
    private function setFee(int $estimateId): void
    {
        // temporarily maximum crutch option
        if (!empty($estimateId)) {
            $serviceId = config_item('cc_extra_fee_service_id');

            if (!empty($serviceId) && $serviceId > 0) {
                $fee = $this->CI->mdl_client_payments->get_extra_fee_sum($estimateId)->fee;
                $service = $this->CI->mdl_estimates->find_estimate_services($estimateId, ['estimates_services.service_id' => $serviceId]);

                if ((float)$fee > 0) {
                    $estimateWithDiscount = $this->CI->mdl_clients->get_discount(['discounts.estimate_id' => $estimateId]);

                    if (!empty($estimateWithDiscount) && is_array($estimateWithDiscount) && $estimateWithDiscount['discount_percents']) {
                        $fee *= 100 / (100 - $estimateWithDiscount['discount_amount']);
                    }

                    if (!empty($service) && !empty($service[0]) && !empty($service[0]['id'])) {
                        $estimateServiceId = $service[0]['id'];
                        $this->CI->mdl_estimates->update_estimate_service(['id' => $estimateServiceId], ['service_price' => $fee]);
                    } else {
                        $service = [
                            'service_id' => $serviceId,
                            'estimate_id' => $estimateId,
                            'service_price' => $fee,
                            'non_taxable' => 1,
                            'service_status' => 2
                        ];
                        $this->CI->mdl_services_orm->insert($service);
                    }
                    $invoice = $this->CI->mdl_invoices->find_all(['estimate_id' => $estimateId]);

                    if (!empty($invoice)) {
                        $invoiceQbId = $invoice[0]->invoice_qb_id;

                        pushJob(
                            'quickbooks/invoice/syncinvoiceinqb',
                            serialize(['id' => $invoice[0]->id, 'qbId' => !empty($invoiceQbId) ? $invoiceQbId : ''])
                        );
                    }
                } else {
                    if (!empty($service) && !empty($service[0]) && !empty($service[0]['id'])) {
                        $estimateServiceId = $service[0]['id'];
                        $this->CI->mdl_estimates->delete_estimate_service(['id' => $estimateServiceId]);
                        $invoice = $this->CI->mdl_invoices->find_all(['estimate_id' => $estimateId]);

                        if (!empty($invoice)) {
                            $invoiceQbId = $invoice[0]->invoice_qb_id;

                            pushJob(
                                'quickbooks/invoice/syncinvoiceinqb',
                                serialize(['id' => $invoice[0]->id, 'qbId' => !empty($invoiceQbId) ? $invoiceQbId : ''])
                            );
                        }
                    }
                }
            }
        }
    }

    /**
     * Get client data
     *
     * @param array $billingData
     * @return array
     */
    private function getClientData(array $billingData): array
    {
        $ext_data = [
            'billCountry' => $billingData['country'] === 'United States of America' ? 'United States' : $billingData['country'],
            'billState' => $billingData['state'],
            'billCity' => $billingData['city'],
            'billAddress' => $billingData['address'],
            'billZip' => $billingData['zip'],
            'billPhone' => $billingData['phone'],
            'profileExist' => !empty($billingData['profile_id']),
            'internalPayment' => $billingData['internal_payment'] ?? false
        ];
        $ext_data['billFname'] = $billingData['name'];
        $ext_data['billLname'] = '';
        $nameArr = explode(' ', $ext_data['billFname'], 2);

        if (count($nameArr) == 2) {
            $ext_data['billFname'] = $nameArr[0];
            $ext_data['billLname'] = $nameArr[1];
        }

        return $ext_data;
    }
}
