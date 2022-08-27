<?php

use application\models\PaymentTransaction;
use Illuminate\Database\QueryException;
use Illuminate\Support\Carbon;

if (!defined('BASEPATH')) exit('No direct script access allowed');

//require_once APPPATH . "/libraries/MY_Driver_library.php";

interface_exists('PaymentInterface', FALSE) OR require_once(APPPATH . 'libraries/Payment/PaymentInterface.php');
class_exists('PaymentException', FALSE) OR require_once(APPPATH . 'libraries/Payment/PaymentException.php');
trait_exists('MY_Driver_Credentials_trait', FALSE) OR require_once(APPPATH . 'libraries/MY_Driver_Credentials_trait.php');

/**
 * Payment Class
 * @mixin bambora
 * @mixin authorize
 */
class Payment extends MY_Driver_Library
{
    use MY_Driver_Credentials_trait;

    public $log = [];

    protected $_adapter = 'bambora';
    public $internalPayment = false;

    const TRANSACTION_STATUS_NOT_PROCESSED = -1;
    const TRANSACTION_STATUS_PENDING = 0;
    const TRANSACTION_STATUS_SUCCESS = 1;
    const TRANSACTION_STATUS_DECLINED = 2;
    const TRANSACTION_STATUS_ERROR = 3;
    const TRANSACTION_STATUS_REVIEW = 4;
    const TRANSACTION_STATUS_REFUNDED = 5;
    const TRANSACTION_STATUS_CANCELED = 6;

    /**
     * Class constructor
     */
    public function __construct($config)
    {
        parent::__construct($config);

        $this->CI->load->helper(['payment_log']);

        $this->enabled = config_item('processing');
        $this->valid_drivers = $config['payment_valid_drivers'];
        $this->_adapter = config_item('payment_default');
    }

    /**
     * Check enable or disable payments
     *
     * @throws PaymentException
     */
    private function checkAvailability() {
        if (!$this->enabled()) {
            throw new PaymentException('Payments disabled');
        }
    }

    /**
     * Get card form
     *
     * @param array $data
     * @param string|bool $adapter
     * @param string $dir
     * @return mixed
     * @throws PaymentException
     */
    public function getForm(array $data, $adapter = false, string $dir = 'form') {
        $this->checkAvailability();

        if ($adapter) {
            $this->setNewAdapter($adapter);
        }
        return $this->{$this->_adapter}->getForm($data, $dir);
    }

    /**
     * Get profile cards
     *
     * @param string $profileId
     * @param string|bool $adapter
     * @return array|false
     * @throws PaymentException
     */
    public function profileCards(string $profileId, $adapter = false) {
        $this->checkAvailability();

        if ($adapter) {
            $this->setNewAdapter($adapter);
        }

        return $this->{$this->_adapter}->profileCards($profileId);
    }

    /**
     * Get card by ID
     *
     * @param string $profileId
     * @param int $cardId
     * @param string|bool $adapter
     * @return mixed
     * @throws PaymentException
     */
    public function getCard(string $profileId, int $cardId, $adapter = false) {
        $this->checkAvailability();

        if ($adapter) {
            $this->setNewAdapter($adapter);
        }

        return $this->{$this->_adapter}->getCard($profileId, $cardId);
    }

    /**
     * Add card to profile
     * add created cardId to internal payment default card setting if not exist
     *
     * @param string $profileId
     * @param array $billingData
     * @param string|array $token
     * @param string $cardholderName
     * @param array|false $additional
     * @param string|false $adapter
     * @return bool
     * @throws PaymentException
     */
    public function profileAddCard(string $profileId, array $billingData, $token, string $cardholderName, $additional = false, $adapter = false): bool
    {
        $this->checkAvailability();

        if ($adapter) {
            $this->setNewAdapter($adapter);
        }

        $added = $this->{$this->_adapter}->profileAddCard($profileId, $billingData, $token, $cardholderName, $additional);

        if ($added && !config_item('int_pay_default_card_id_' . $this->_adapter)) {
            $cards = $this->profileCards($profileId);

            if (sizeof($cards)) {
                $this->CI->load->helper('settings');
                $defaultCardKey = 'int_pay_default_card_id_' . $this->_adapter;
                updateSettings($defaultCardKey, $cards[sizeof($cards) - 1]['card_id']);
            }
        }

        return $added;
    }

    /**
     * Create profile
     * add created cardId to internal payment default card setting
     *
     * @param array $billingData
     * @param $token
     * @param $cardholderName
     * @param array|false $additional
     * @param string|false $adapter
     * @return string
     * @throws PaymentException
     */
    public function createProfile(array $billingData, $token, $cardholderName, $additional = false, $adapter = false): string
    {
        $this->checkAvailability();

        if ($adapter) {
            $this->setNewAdapter($adapter);
        }

        $profileId = $this->{$this->_adapter}->createProfile($billingData, $token, $cardholderName, $additional);

        $cards = $this->profileCards($profileId);

        if (sizeof($cards)) {
            $this->CI->load->helper('settings');
            $defaultCardKey = 'int_pay_default_card_id_' . $this->_adapter;
            updateSettings($defaultCardKey, $cards[0]['card_id']);
        }

        return $profileId;
    }

    /**
     * Delete profile card
     * if deleted card is internal payment default card change it to first from card list
     * if no card clear internal payment default card setting
     *
     * @param string $profileId
     * @param $cardId
     * @param string|null $adapter
     * @return bool
     * @throws PaymentException
     */
    public function profileDeleteCard(string $profileId, $cardId, $adapter = false): bool
    {
        $this->checkAvailability();

        if ($adapter) {
            $this->setNewAdapter($adapter);
        }

        $deleted = $this->{$this->_adapter}->profileDeleteCard($profileId, $cardId);

        // update default card setting
        if ($deleted && config_item('int_pay_default_card_id_' . $this->_adapter) == $cardId) {
            $defaultCardId = null;
            $cards = $this->profileCards($profileId);

            if (sizeof($cards)) {
                $defaultCardId = $cards[0]['card_id'];
            }

            $this->CI->load->helper('settings');
            $defaultCardKey = 'int_pay_default_card_id_' . $this->_adapter;
            updateSettings($defaultCardKey, $defaultCardId);
        }

        return true;
    }

    /**
     * Make payment
     *
     * @param $paymentData
     * @param $amount
     * @param $trnData
     * @param $orderNo
     * @return array
     * @throws PaymentException
     */
    public function pay($paymentData, $amount, $trnData, $orderNo = null): array
    {
        $this->checkAvailability();

        if (empty($paymentData) || empty($amount) || empty($trnData)) {
            throw new PaymentException("No required data");
        }

        $orderNo = $orderNo ?? (time() . '_' . random_int(1000,9999));

        try {
            $transaction = PaymentTransaction::create($trnData);
        }
        catch (QueryException $e) {
            throw new PaymentException("Failed save payment data to Database");
        }

        try {
            $trnResult = $this->{$this->_adapter}->pay($paymentData, $amount, $orderNo);
        } catch (PaymentException $e) {
            toLog($this->log, $e->__toArray());
            $trnResp = $e->getResponse();

            if (preg_match('/decline/iu', $e->getMessage())) {
                $status = Payment::TRANSACTION_STATUS_DECLINED;
            } else {
                $status = $e->getCode();
            }

            $updData = [
                'payment_transaction_status' => $status,
                'payment_transaction_message' => $e->getMessage(),
                'payment_transaction_date' => Carbon::now()->toDateTimeString(),
                'payment_transaction_log' => json_encode($this->log, JSON_PRETTY_PRINT),
                'payment_transaction_remote_id' => $trnResp['id'] ?? null,
                'payment_transaction_amount' => $trnResp['amount'] ?? $amount,
                'payment_transaction_card' => isset($trnResp['card']) ? $this->cardType($trnResp['card']['card_type']) : null,
                'payment_transaction_card_num' => isset($trnResp['card']) && isset($trnResp['card']['last_four']) ? $trnResp['card']['last_four'] : null,
            ];

            $updated = false;

            if ($transaction) {
                $transaction->fill($updData);
                $updated = $transaction->save();
            }

            if (!$updated) {
                throw new PaymentException("Failed save payment data to Database");
            }

            return [
                'error' => true,
                'transaction' => $transaction,
                'message' => $e->__toString()
            ];
        }

        toLog($this->log, $trnResult['response']);

        $updated = false;

        if ($transaction) {
            $trnResult['transaction']['payment_transaction_log'] = json_encode($this->log, JSON_PRETTY_PRINT);
            $transaction->fill($trnResult['transaction']);
            $updated = $transaction->save();
        }

        if (!$updated) {
            throw new PaymentException("Failed save payment data to Database");
        }

        return [
            'transaction' => $transaction
        ];
    }

    /**
     * Make refund
     *
     * @param int $transactionRemoteId
     * @param array $trnData
     * @param float $amount
     * @return array
     * @throws PaymentException
     */
    public function refund(int $transactionRemoteId, array $trnData, float $amount): array
    {
        $this->checkAvailability();

        if (empty($transactionRemoteId) || empty($amount) || empty($trnData)) {
            throw new PaymentException("No required data");
        }

        try {
            $transaction = PaymentTransaction::create($trnData);
        }
        catch (QueryException $e) {
            toLog($this->log, $e->getMessage());
            throw new PaymentException("Failed save payment data to Database");
        }

        try {
            $trnResult = $this->{$this->_adapter}->refund($transactionRemoteId, abs($amount));
        } catch (PaymentException $e) {
            toLog($this->log, $e->__toArray());
            $trnResp = $e->getResponse();

            if (preg_match('/decline/iu', $e->getMessage())) {
                $status = Payment::TRANSACTION_STATUS_DECLINED;
            } else {
                $status = $e->getCode();
            }

            $updData = [
                'payment_transaction_status' => $status,
                'payment_transaction_message' => $e->getMessage(),
                'payment_transaction_date' => Carbon::now()->toDateTimeString(),
                'payment_transaction_log' => json_encode($this->log, JSON_PRETTY_PRINT),
                'payment_transaction_remote_id' => $trnResp['id'] ?? null,
                'payment_transaction_amount' => isset($trnResp['amount']) ? -1 * abs($trnResp['amount']) : -1 * abs($amount),
            ];

            $updated = false;

            if ($transaction) {
                $transaction->fill($updData);
                $updated = $transaction->save();
            }

            if (!$updated) {
                throw new PaymentException("Failed save payment data to Database");
            }

            return [
                'error' => true,
                'message' => $e->__toString()
            ];
        }

        toLog($this->log, $trnResult);

        $updated = false;

        if ($transaction) {
            $trnResult['transaction']['payment_transaction_log'] = json_encode($this->log, JSON_PRETTY_PRINT);
            $transaction->fill($trnResult['transaction']);
            $updated = $transaction->save();
        }

        if (!$updated) {
            throw new PaymentException("Failed save payment data to Database");
        }

        return [
            'transaction' => $transaction
        ];
    }

    /**
     * Get transaction
     *
     * @param int $transactionId
     * @param string|null $adapter
     * @return mixed
     * @throws PaymentException
     */
    public function getTransaction(int $transactionId, $adapter = false)
    {
        $this->checkAvailability();

        if ($adapter) {
            $this->setNewAdapter($adapter);
        }

        return $this->{$this->_adapter}->getTransaction($transactionId);
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
        $this->checkAvailability();

        if ($adapter) {
            $this->setNewAdapter($adapter);
        }

        return $this->{$this->_adapter}->checkTransaction($transactionId);
    }

    /**
     * Set new adapter
     *
     * @param string|null $adapter
     * @return string
     */
    public function setNewAdapter(string $adapter = null): string
    {
        if ($adapter && $this->validateAdapter($adapter)) {
            $this->setAdapter($adapter);
        }

        return $this->getAdapter();
    }

    /**
     * @param string $status
     * @return string
     */
    public function statusToText(string $status): string
    {
        switch ($status) {
            case self::TRANSACTION_STATUS_NOT_PROCESSED:
                return "Not processed";
            case self::TRANSACTION_STATUS_SUCCESS:
                return "Success";
            case self::TRANSACTION_STATUS_DECLINED:
                return "Declined";
            case self::TRANSACTION_STATUS_REVIEW:
                return "Pending review";
            case self::TRANSACTION_STATUS_REFUNDED:
                return "Refunded";
            case self::TRANSACTION_STATUS_CANCELED:
                return "Canceled";
            case self::TRANSACTION_STATUS_PENDING:
                return "Pending";
            case self::TRANSACTION_STATUS_ERROR:
                return "Error";
        }
        return 'Undefined';
    }
}
