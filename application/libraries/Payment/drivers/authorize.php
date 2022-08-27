<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

use Carbon\Carbon;
use net\authorize\api\constants\ANetEnvironment;
use net\authorize\api\contract\v1 as AnetAPI;
use net\authorize\api\contract\v1\TransactionDetailsType;
use net\authorize\api\controller as AnetController;

//define("AUTHORIZENET_LOG_FILE", "phplog");

/**
 * Class authorize
 * @mixin Payment
 */
class authorize extends CI_Driver implements PaymentInterface
{
    private $MA;
    private $refId;

    private $validationMode = 'testMode'; // liveMode
    private $endPoint = ANetEnvironment::SANDBOX;

    public function init()
    {
        try {
            $this->MA = new AnetAPI\MerchantAuthenticationType();
            $this->MA->setName($this->getCredentials('loginId', 'authorize'));
            $this->MA->setTransactionKey($this->getCredentials('transactionKey', 'authorize'));
            $this->refId = 'ref' . time();
            $this->endPoint = $this->getCredentials('isSandbox',
                'authorize') ? ANetEnvironment::SANDBOX : ANetEnvironment::PRODUCTION;
            $this->validationMode = $this->getCredentials('isValidationEnabled', 'authorize') ? 'liveMode' : 'testMode';
        } catch (\Exception $e) {
            die($e->getMessage());
        }
    }

    public function tokenize($number, $expr_month, $expr_year, $cvd): bool
    {
        return false;
    }

    /**
     * @param array $billingData
     * @param $token
     * @param $cardholderName
     * @param array|false $additional
     * @return string
     * @throws PaymentException
     */
    public function createProfile(array $billingData, $token, $cardholderName, $additional = false): string
    {
        try {
            $paymentProfiles[] = $this->getPaymentProfileData($billingData, $token, $additional);

            $profile = new AnetAPI\CustomerProfileType();
            $profile->setDescription($billingData['name']);
            $profile->setMerchantCustomerId($billingData['customer_id']);
            $profile->setEmail($billingData['email']);
            $profile->setPaymentProfiles($paymentProfiles);

            $request = new AnetAPI\CreateCustomerProfileRequest();
            $request->setMerchantAuthentication($this->MA);
            $request->setRefId($this->refId);
            $request->setProfile($profile);
            $request->setValidationMode($this->validationMode);

            $controller = new AnetController\CreateCustomerProfileController($request);
            $response = $controller->executeWithApiResponse($this->endPoint);

            if (($response == null) || ($response->getMessages()->getResultCode() != "Ok")) {
                $msg = $response->getMessages()->getMessage();
                throw new Exception($msg[0]->getText());
            }
        } catch (Exception $e) {
            throw new PaymentException($e->getMessage());
        }

        return $response->getCustomerProfileId();
    }

    /**
     * @param string $profileId
     * @return array
     * @throws PaymentException
     */
    public function profileCards(string $profileId): array
    {
        try {
            $request = new AnetAPI\GetCustomerProfileRequest();
            $request->setMerchantAuthentication($this->MA);
            $request->setCustomerProfileId($profileId);
            $request->setUnmaskExpirationDate(true);

            $controller = new AnetController\GetCustomerProfileController($request);
            $response = $controller->executeWithApiResponse($this->endPoint);

            if (($response == null) || ($response->getMessages()->getResultCode() != "Ok")) {
                $msg = $response->getMessages()->getMessage();
                throw new Exception($msg[0]->getText());
            }
        } catch (Exception $e) {
            throw new PaymentException($e->getMessage());
        }
        $profile = $response->getProfile();
        $paymentProfiles = $profile->getPaymentProfiles();
        $cards = [];

        if ($paymentProfiles && $paymentProfiles != null) {
            foreach ($paymentProfiles as $paymentProfile) {
                $pm = $paymentProfile->getPayment();
                $card = $pm->getCreditCard();
                list($expY, $expM) = explode('-', $card->getExpirationDate());
                $cards[] = [
                    'card_id' => $paymentProfile->getCustomerPaymentProfileId(),
                    'card_type' => $this->cardType($card->getCardType()),
                    'number' => $card->getCardNumber(),
                    'expiry_month' => $expM,
                    'expiry_year' => $expY,
                    'name' => '---'
                ];
            }
        }

        return $cards;
    }

    /**
     * @param string $profileId
     * @param int $cardId
     * @return array
     * @throws PaymentException
     */
    public function getCard(string $profileId, int $cardId): array
    {
        try {
            $request = new AnetAPI\GetCustomerPaymentProfileRequest();
            $request->setMerchantAuthentication($this->MA);
            $request->setCustomerProfileId($profileId);
            $request->setCustomerPaymentProfileId($cardId);
            $request->setUnmaskExpirationDate(true);

            $controller = new AnetController\GetCustomerPaymentProfileController($request);
            $response = $controller->executeWithApiResponse($this->endPoint);

            if (($response == null) || ($response->getMessages()->getResultCode() != "Ok")) {
                $msg = $response->getMessages()->getMessage();
                throw new Exception($msg[0]->getText());
            }
        } catch (Exception $e) {
            throw new PaymentException($e->getMessage());
        }

        $paymentProfile = $response->getPaymentProfile();
        $card = [];

        if ($paymentProfile && $paymentProfile !== null) {
            $pm = $paymentProfile->getPayment();
            $card = $pm->getCreditCard();
            list($expY, $expM) = explode('-', $card->getExpirationDate());
            $card = [
                'card_id' => $paymentProfile->getCustomerPaymentProfileId(),
                'card_type' => $this->cardType($card->getCardType()),
                'number' => $card->getCardNumber(),
                'expiry_month' => $expM,
                'expiry_year' => $expY,
                'name' => '---'
            ];
        }

        return $card;
    }

    /**
     * @param string $profileId
     * @param $billingData
     * @param $token
     * @param $cardholderName
     * @param array|false $additional
     * @return bool
     * @throws PaymentException
     */
    public function profileAddCard(string $profileId, $billingData, $token, $cardholderName, $additional = false): bool
    {
        try {
            $paymentProfile = $this->getPaymentProfileData($billingData, $token, $additional);

            $request = new AnetAPI\CreateCustomerPaymentProfileRequest();
            $request->setMerchantAuthentication($this->MA);

            $request->setCustomerProfileId($profileId);
            $request->setPaymentProfile($paymentProfile);
            $request->setValidationMode($this->validationMode);

            $controller = new AnetController\CreateCustomerPaymentProfileController($request);
            $response = $controller->executeWithApiResponse($this->endPoint);

            if (($response == null) || ($response->getMessages()->getResultCode() != "Ok")) {
                $msg = $response->getMessages()->getMessage();
                throw new Exception($msg[0]->getText());
            }
        } catch (Exception $e) {
            throw new PaymentException($e->getMessage());
        }

        return true;
    }

    /**
     * @param string $profileId
     * @param int $cardId
     * @return bool
     * @throws PaymentException
     */
    public function profileDeleteCard(string $profileId, int $cardId): bool
    {
        try {
            $request = new AnetAPI\DeleteCustomerPaymentProfileRequest();
            $request->setMerchantAuthentication($this->MA);
            $request->setCustomerProfileId($profileId);
            $request->setCustomerPaymentProfileId($cardId);
            $controller = new AnetController\DeleteCustomerPaymentProfileController($request);
            $response = $controller->executeWithApiResponse($this->endPoint);

            if (($response == null) || ($response->getMessages()->getResultCode() != "Ok")) {
                $msg = $response->getMessages()->getMessage();
                throw new Exception($msg[0]->getText());
            }
        } catch (Exception $e) {
            throw new PaymentException($e->getMessage());
        }

        return true;
    }

    /**
     * @param string $inputType
     * @return string
     */
    public function cardType(string $inputType): string
    {
        switch ($inputType) {
            case "AmericanExpress":
                return "amex";
            case "DinersClub":
                return "diners";
            case "JCB":
                return "jcb";
            case "MasterCard":
                return "mastercard";
            case "Discover":
                return "discover";
            case "Visa":
                return "visa";
            default:
                return "undefined";
        }
    }

    /**
     * @param array $data
     * @param float $amount
     * @param string $order_number
     * @return array
     * @throws PaymentException
     */
    public function pay(array $data, float $amount, string $order_number): array
    {
        try {
            $order = new AnetAPI\OrderType();
            $order->setInvoiceNumber($order_number);
            //$order->setDescription("Test payment!");

            if (isset($data['additional'])) {
                $customerAddress = new AnetAPI\CustomerAddressType();
                $customerAddress->setFirstName($data['additional']['bill_fname']);
                $customerAddress->setLastName($data['additional']['bill_lname']);
                $customerAddress->setAddress($data['additional']['bill_address']);
                $customerAddress->setCity($data['additional']['bill_city']);
                $customerAddress->setState($data['additional']['bill_state']);
                $customerAddress->setZip($data['additional']['bill_zip']);
                $customerAddress->setCountry(str_replace(['Canada'], ['CAN'], $data['additional']['bill_country']));

                $customerData = new AnetAPI\CustomerDataType();
                $customerData->setType('individual');
            }

            $transactionRequestType = new AnetAPI\TransactionRequestType();
            $transactionRequestType->setTransactionType("authCaptureTransaction");
            $transactionRequestType->setAmount(number_format($amount, 2, '.', ''));
            $transactionRequestType->setOrder($order);
            $transactionRequestType->setCustomerIP($this->CI->input->ip_address());

            if (isset($data['additional'])) {
                $transactionRequestType->setBillTo($customerAddress);
                $transactionRequestType->setCustomer($customerData);
            }

            if (isset($data['token']) && $data['name']) {
                $opaqueData = new AnetAPI\OpaqueDataType();
                $opaqueData->setDataDescriptor($data['token']['dataDescriptor']);
                $opaqueData->setDataValue($data['token']['dataValue']);

                $paymentType = new AnetAPI\PaymentType();
                $paymentType->setOpaqueData($opaqueData);

                $transactionRequestType->setPayment($paymentType);
            } elseif (isset($data['payment_profile']) && $data['card_id']) {
                $profileToCharge = new AnetAPI\CustomerProfilePaymentType();
                $profileToCharge->setCustomerProfileId($data['payment_profile']);
                $paymentProfile = new AnetAPI\PaymentProfileType();
                $paymentProfile->setPaymentProfileId($data['card_id']);
                $profileToCharge->setPaymentProfile($paymentProfile);

                $transactionRequestType->setProfile($profileToCharge);
            } else {
                throw new Exception('Wrong input data!');
            }

            $request = new AnetAPI\CreateTransactionRequest();
            $request->setMerchantAuthentication($this->MA);
            $request->setRefId($this->refId);
            $request->setTransactionRequest($transactionRequestType);
            $controller = new AnetController\CreateTransactionController($request);
            $response = $controller->executeWithApiResponse($this->endPoint);
        } catch (Exception $e) {
            throw new PaymentException($e->getMessage(), $e->getCode());
        }

        if ($response == null) {
            throw new PaymentException(
                'No response returned',
                Payment::TRANSACTION_STATUS_ERROR);
        }

        /** @var net\authorize\api\contract\v1\TransactionResponseType $tresponse */
        $tresponse = $response->getTransactionResponse();

        if ($tresponse == null) {
            throw new PaymentException(
                "Transaction Failed",
                Payment::TRANSACTION_STATUS_ERROR,
                $response->jsonSerialize()
            );
        }

        if ($response->getMessages()->getResultCode() != "Ok") {
            if ($tresponse->getErrors() != null) {
                throw new PaymentException(
                    $tresponse->getErrors()[0]->getErrorCode() . ' ' . $tresponse->getErrors()[0]->getErrorText(),
                    (int)$tresponse->getResponseCode(),
                    $response->jsonSerialize(),
                    $tresponse->jsonSerialize()
                );
            } else {
                throw new PaymentException(
                    $response->getMessages()->getMessage()[0]->getCode() . ' ' . $response->getMessages()->getMessage()[0]->getText(),
                    Payment::TRANSACTION_STATUS_ERROR,
                    $response->jsonSerialize(),
                    $tresponse->jsonSerialize()
                );
            }
        }

        if ($tresponse->getMessages() == null) {
            if ($tresponse->getErrors() != null) {
                throw new PaymentException(
                    $tresponse->getErrors()[0]->getErrorCode() . ' ' . $tresponse->getErrors()[0]->getErrorText(),
                    Payment::TRANSACTION_STATUS_ERROR,
                    $response->jsonSerialize(),
                    $tresponse->jsonSerialize()
                );
            } else {
                throw new PaymentException(
                    "Transaction Failed",
                    Payment::TRANSACTION_STATUS_ERROR,
                    $response->jsonSerialize(),
                    $tresponse->jsonSerialize()
                );
            }
        }

        if ((int)$tresponse->getTestRequest() === 1 || (int)$tresponse->getTransId() === 0) {
            throw new PaymentException(
                "Transaction Failed. Test or wrong transaction id",
                Payment::TRANSACTION_STATUS_ERROR,
                $response->jsonSerialize(),
                $tresponse->jsonSerialize()
            );
        }

        return [
            'transaction' => [
                'payment_transaction_status' => (int)$tresponse->getResponseCode(),
                'payment_transaction_approved' => (int)$tresponse->getResponseCode() == 1 ? 1 : 0,
                'payment_transaction_risk' => 0,
                'payment_transaction_message' => $tresponse->getMessages()[0]->getDescription(),
                'payment_transaction_date' => Carbon::now()->toDateTimeString(),
                'payment_transaction_remote_id' => $tresponse->getTransId(),
                'payment_transaction_amount' => $amount,
                'payment_transaction_card' => $this->cardType($tresponse->getAccountType()),
                'payment_transaction_card_num' => $tresponse->getAccountNumber(),
                'payment_transaction_auth_code' => $tresponse->getAuthCode(),
            ],
            'response' => $tresponse->jsonSerialize()
        ];
    }

    /**
     * @param int $transaction_id
     * @param float $amount
     * @return array
     * @throws PaymentException
     */
    public function refund(int $transaction_id, float $amount): array
    {
        $trn = $this->getTransaction($transaction_id);
        if ($trn->getTransactionStatus() !== "settledSuccessfully") {
            throw new PaymentException('The unsettled transaction can\'t be refunded');
        }

        try {
            $payment = new AnetAPI\PaymentType();
            if (($creditCardMasked = $trn->getPayment()->getCreditCard()) !== null) {
                $creditCard = new AnetAPI\CreditCardType();
            }
            $creditCard->setCardNumber($creditCardMasked->getCardNumber());
            $creditCard->setExpirationDate('XXXX');
            $payment->setCreditCard($creditCard);

            $transactionRequestType = new AnetAPI\TransactionRequestType();
            $transactionRequestType->setTransactionType("refundTransaction");
            $transactionRequestType->setAmount($amount);
            $transactionRequestType->setPayment($payment);
            $transactionRequestType->setRefTransId($transaction_id);

            if (($billTo = $trn->getBillTo()) !== null) {
                $transactionRequestType->setBillTo($billTo);
            }
            if (($cust = $trn->getCustomer()) !== null) {
                $transactionRequestType->setCustomer($cust);
            }

            $request = new AnetAPI\CreateTransactionRequest();
            $request->setMerchantAuthentication($this->MA);
            $request->setRefId($this->refId);
            $request->setTransactionRequest($transactionRequestType);
            $controller = new AnetController\CreateTransactionController($request);
            $response = $controller->executeWithApiResponse($this->endPoint);
        } catch (\Exception $e) {
            throw new PaymentException($e->getMessage(), $e->getCode());
        }

        if ($response == null) {
            throw new PaymentException('No response returned', Payment::TRANSACTION_STATUS_ERROR);
        }

        /** @var net\authorize\api\contract\v1\TransactionResponseType $tresponse */
        $tresponse = $response->getTransactionResponse();

        if ($tresponse == null) {
            throw new PaymentException(
                "Transaction Failed",
                Payment::TRANSACTION_STATUS_ERROR,
                $response->jsonSerialize()
            );
        }

        if ($response->getMessages()->getResultCode() != "Ok") {
            if ($tresponse->getErrors() != null) {
                throw new PaymentException(
                    $tresponse->getErrors()[0]->getErrorCode() . ' ' . $tresponse->getErrors()[0]->getErrorText(),
                    (int)$tresponse->getResponseCode(),
                    $response->jsonSerialize(),
                    $tresponse->jsonSerialize()
                );
            } else {
                throw new PaymentException(
                    $response->getMessages()->getMessage()[0]->getCode() . ' ' . $response->getMessages()->getMessage()[0]->getText(),
                    Payment::TRANSACTION_STATUS_ERROR,
                    $response->jsonSerialize(),
                    $tresponse->jsonSerialize()
                );
            }
        }

        if ($tresponse->getMessages() == null) {
            if ($tresponse->getErrors() != null) {
                throw new PaymentException(
                    $tresponse->getErrors()[0]->getErrorCode() . ' ' . $tresponse->getErrors()[0]->getErrorText(),
                    Payment::TRANSACTION_STATUS_ERROR,
                    $response->jsonSerialize(),
                    $tresponse->jsonSerialize()
                );
            } else {
                throw new PaymentException(
                    "Transaction Failed",
                    Payment::TRANSACTION_STATUS_ERROR,
                    $response->jsonSerialize(),
                    $tresponse->jsonSerialize()
                );
            }
        }

        return [
            'transaction' => [
                'payment_transaction_status' => (int)$tresponse->getResponseCode(),
                'payment_transaction_approved' => $tresponse->getResponseCode() == 1 ? 1 : 0,
                'payment_transaction_risk' => 0,
                'payment_transaction_message' => $tresponse->getMessages()[0]->getDescription(),
                'payment_transaction_date' => Carbon::now()->toDateTimeString(),
                'payment_transaction_remote_id' => $tresponse->getTransId(),
                'payment_transaction_amount' => -1 * abs($amount),
                'payment_transaction_card' => $this->cardType($tresponse->getAccountType()),
                'payment_transaction_card_num' => $tresponse->getAccountNumber(),
                'payment_transaction_auth_code' => $tresponse->getAuthCode(),
            ],
            'response' => $tresponse->jsonSerialize()
        ];
    }

    /**
     * @param int $transaction_id
     * @return array
     */
    public function void(int $transaction_id): array
    {
        return [];
    }

    /**
     * @param int $transaction_id
     * @param bool $masked
     * @return TransactionDetailsType
     * @throws PaymentException
     */
    public function getTransaction(int $transaction_id, bool $masked = true): TransactionDetailsType
    {
        try {
            $request = new AnetAPI\GetTransactionDetailsRequest();
            $request->setMerchantAuthentication($this->MA);
            $request->setTransId($transaction_id);

            $controller = new AnetController\GetTransactionDetailsController($request);

            $response = $controller->executeWithApiResponse($this->endPoint);
            if (($response == null) || ($response->getMessages()->getResultCode() != "Ok")) {
                $msg = $response->getMessages()->getMessage();
                throw new Exception($msg[0]->getText());
            }
        } catch (Exception $e) {
            throw new PaymentException($e->getMessage());
        }

        return $response->getTransaction();
    }

    /**
     * @param int $transaction_id
     * @return array
     * @throws PaymentException
     */
    public function checkTransaction(int $transaction_id): array
    {
        /** @var TransactionDetailsType $transaction */
        $transaction = $this->getTransaction($transaction_id);

        return [
            'transaction_id' => $transaction->getTransId(),
            'status' => $this->transactionStatus($transaction->getTransactionStatus()),
            'remote_status' => $transaction->getTransactionStatus(),
            'response_code' => $transaction->getResponseCode(),
            'reason_code' => $transaction->getResponseReasonCode(),
            'reason_description' => $transaction->getResponseReasonDescription(),
            'auth_code' => $transaction->getAuthCode(),
            'amount' => $transaction->getAuthAmount(),
            'settled_amount' => $transaction->getSettleAmount(),
            'response' => $transaction->jsonSerialize()
        ];
    }

    /**
     * Get form
     *
     * @param array $data
     * @param string $dir
     * @return string
     * @throws PaymentException
     */
    public function getForm(array $data = [], string $dir = 'form'): string
    {
        $CI = &get_instance();

        try {
            $request = new AnetAPI\GetMerchantDetailsRequest();
            $request->setMerchantAuthentication($this->MA);
            $controller = new AnetController\GetMerchantDetailsController($request);
            $response = $controller->executeWithApiResponse($this->endPoint);
            if ($response == null) {
                throw new Exception('Unknown error!');
            }
            if ($response->getMessages() && $response->getMessages()->getResultCode() != "Ok") {
                $msg = $response->getMessages()->getMessage();
                throw new Exception($msg[0]->getText());
            }
        } catch (Exception $e) {
            throw new PaymentException($e->getMessage(), $e->getCode());
        }

        return $CI->load->ext_view(__DIR__ . '/../views/' . $dir . '/authorize', [
                'isSandbox' => $this->getCredentials('isSandbox', 'authorize'),
                'loginId' => $this->getCredentials('loginId', 'authorize'),
                'publicKey' => $response->getPublicClientKey(),
            ] + $data, true);
    }

    /**
     * @param string $profileId
     * @return array
     * @throws PaymentException
     */
    public function getProfile(string $profileId): array
    {
        try {
            $request = new AnetAPI\GetCustomerProfileRequest();
            $request->setMerchantAuthentication($this->MA);
            $request->setCustomerProfileId($profileId);
            $controller = new AnetController\GetCustomerProfileController($request);
            $response = $controller->executeWithApiResponse($this->endPoint);

            if (($response == null) || ($response->getMessages()->getResultCode() != "Ok")) {
                $msg = $response->getMessages()->getMessage();
                throw new Exception($msg[0]->getText());
            }
        } catch (Exception $e) {
            throw new PaymentException($e->getMessage(), $e->getCode());
        }

        return $response->getProfile()->jsonSerialize();
    }

    /**
     * @param string $profileId
     * @return bool
     * @throws PaymentException
     */
    public function deleteProfile(string $profileId): bool
    {
        try {
            $request = new AnetAPI\DeleteCustomerProfileRequest();
            $request->setMerchantAuthentication($this->MA);
            $request->setCustomerProfileId($profileId);
            $controller = new AnetController\DeleteCustomerProfileController($request);
            $response = $controller->executeWithApiResponse($this->endPoint);

            if (($response == null) || ($response->getMessages()->getResultCode() != "Ok")) {
                $msg = $response->getMessages()->getMessage();
                throw new Exception($msg[0]->getText());
            }
        } catch (Exception $e) {
            throw new PaymentException($e->getMessage(), $e->getCode());
        }

        return true;
    }

    /**
     * @return array
     * @throws PaymentException
     */
    public function getProfiles(): array
    {
        try {
            $request = new AnetAPI\GetCustomerProfileIdsRequest();
            $request->setMerchantAuthentication($this->MA);
            $controller = new AnetController\GetCustomerProfileIdsController($request);
            $response = $controller->executeWithApiResponse($this->endPoint);

            if (($response == null) || ($response->getMessages()->getResultCode() != "Ok")) {
                $msg = $response->getMessages()->getMessage();
                throw new Exception($msg[0]->getText());
            }
        } catch (Exception $e) {
            throw new PaymentException($e->getMessage(), $e->getCode());
        }

        return $response->getIds();
    }

    /**
     * @param string $originStatus
     * @return int|void
     */
    private function transactionStatus(string $originStatus)
    {
        switch ($originStatus) {
            case 'settledSuccessfully':
                return Payment::TRANSACTION_STATUS_SUCCESS;
            case 'authorizedPendingCapture':
            case 'capturedPendingSettlement':
            case 'refundPendingSettlement':
            case 'approvedReview':
                return Payment::TRANSACTION_STATUS_PENDING;
            case 'declined':
                return Payment::TRANSACTION_STATUS_DECLINED;
            case 'communicationError':
            case 'expired':
            case 'generalError':
            case 'failedReview':
            case 'settlementError':
            case 'returnedItem':
            case 'couldNotVoid':
                return Payment::TRANSACTION_STATUS_ERROR;
            case 'refundSettledSuccessfully':
                return Payment::TRANSACTION_STATUS_REFUNDED;
            case 'underReview':
            case 'FDSPendingReview':
            case 'FDSAuthorizedPendingReview':
                return Payment::TRANSACTION_STATUS_REVIEW;
            case 'voided':
                return Payment::TRANSACTION_STATUS_CANCELED;
        }
    }

    /**
     * Create driver payment profile data
     *
     * @param array $billingData
     * @param array $token
     * @param array|false $additional
     * @return AnetAPI\CustomerPaymentProfileType
     */
    private function getPaymentProfileData(array $billingData, array $token, $additional): AnetAPI\CustomerPaymentProfileType
    {
        $billTo = new AnetAPI\CustomerAddressType();
        if ($additional) {
            $billTo->setFirstName($additional['bill_fname']);
            $billTo->setLastName($additional['bill_lname']);
            $billTo->setAddress($additional['bill_address']);
            $billTo->setCity($additional['bill_city']);
            $billTo->setState($additional['bill_state']);
            $billTo->setZip($additional['bill_zip']);
            $billTo->setCountry(str_replace(['Canada'], ['CAN'], $additional['bill_country']));
            $billTo->setPhoneNumber($additional['bill_phone']);
        } else {
            $nameArr = explode(' ', $billingData['name'], 2);
            if (count($nameArr) == 2) {//countOk
                $billTo->setFirstName($nameArr[0]);
                $billTo->setLastName($nameArr[1]);
            }
            //$billTo->setCompany("Souveniropolis");
            $billTo->setAddress($billingData['address']);
            $billTo->setCity($billingData['city']);
            $billTo->setState($billingData['state']);
            $billTo->setZip($billingData['zip']);
            $billTo->setCountry(str_replace(['Canada'], ['CAN'], $billingData['country']));
            $billTo->setPhoneNumber($billingData['phone']);
            //$billTo->setfaxNumber("999-999-9999");
        }
        $opaqueData = new AnetAPI\OpaqueDataType();
        $opaqueData->setDataDescriptor($token['dataDescriptor']);
        $opaqueData->setDataValue($token['dataValue']);

        $paymentType = new AnetAPI\PaymentType();
        $paymentType->setOpaqueData($opaqueData);

        $paymentProfile = new AnetAPI\CustomerPaymentProfileType();
        $paymentProfile->setCustomerType('individual');
        $paymentProfile->setBillTo($billTo);
        $paymentProfile->setPayment($paymentType);

        return $paymentProfile;
    }
}

