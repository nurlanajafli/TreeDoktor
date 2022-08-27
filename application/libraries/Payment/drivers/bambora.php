<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

use Beanstream\Exceptions\ApiException;
use Beanstream\Exceptions\Exception;
use Beanstream\Gateway;

/**
 * Class bambora
 * @mixin Payment
 */
class bambora extends MY_Driver implements PaymentInterface
{
    /**
     * @var Gateway $GW ;
     */
    private $GW;

    public function init()
    {
        try {
            $this->GW = new Gateway($this->getCredentials(false, 'bambora'));
        } catch (\Exception $e) {
            die($e->getMessage());
        }
    }

    /**
     * @param $number
     * @param $expr_month
     * @param $expr_year
     * @param $cvd
     * @return false|array
     */
    public function tokenize($number, $expr_month, $expr_year, $cvd)
    {
        try {
            $result = $this->GW->tokenization->tokenizeCreditCard([
                "number" => $number,
                "expiry_month" => $expr_month,
                "expiry_year" => $expr_year,
                "cvd" => $cvd
            ]);

        } catch (Exception $e) {
            return false;
        }

        return $result['token'];
    }

    /**
     * @param $billingData
     * @param $token
     * @param string $cardholderName
     * @param array|false $additional
     * @return string
     * @throws PaymentException
     */
    public function createProfile($billingData, $token, string $cardholderName, $additional = false): string
    {
        try {
            if (is_array($additional) && sizeof($additional)) {
                $billing = [
                    'name' => $additional['bill_fname'] . ' '. $additional['bill_lname'],
                    'address_line1' => $additional['bill_address'],
                    'address_line2' => '',
                    'city' => $additional['bill_city'],
                    'province' => $additional['bill_state'],
                    'country' => $additional['bill_country'],
                    'postal_code' => $additional['bill_zip'],
                    'phone_number' => $additional['bill_phone']
                ];
            } else {
                $billing = [
                    'name' => $billingData['name'],
                    'address_line1' => $billingData['address'],
                    'address_line2' => '',
                    'city' => $billingData['city'],
                    'province' => $billingData['state'],
                    'country' => $billingData['country'],
                    'postal_code' => $billingData['zip'],
                    'phone_number' => $billingData['phone']
                ];
            }

            $data = [
                'token' => [
                    'name' => $cardholderName,
                    'code' => $token
                ],
                'billing' => $billing
            ];

            if (!empty($billingData['customer_id']) && $billingData['customer_id']) {
                $data['customer_code'] = $billingData['customer_id'];
            }

            $result = $this->GW->profiles->createProfile($data);

        } catch (ApiException $e) {
            throw new PaymentException($e->getMessage(), $e->getCode(), $e->getResponse());

        } catch (Exception $e) {
            throw new PaymentException($e->getMessage());
        }

        return $result['customer_code'];
    }

    /**
     * @param string $profileId
     * @return array
     * @throws PaymentException
     */
    public function profileCards(string $profileId): array
    {
        try {
            $result = $this->GW->profiles->getCards($profileId);
        } catch (ApiException $e) {
            throw new PaymentException($e->getMessage(), $e->getCode(), $e->getResponse());
        } catch (Exception $e) {
            throw new PaymentException($e->getMessage());
        }

        foreach ($result['card'] as &$card) {
            $card['card_type'] = $this->cardType($card['card_type']);
        }

        return $result['card'];
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
            $result = $this->GW->profiles->getCard($profileId, $cardId);
        } catch (ApiException $e) {
            throw new PaymentException($e->getMessage(), $e->getCode(), $e->getResponse());
        } catch (Exception $e) {
            throw new PaymentException($e->getMessage());
        }

        if (isset($result['card'][0])) {
            $result['card'][0]['card_type'] = $this->cardType($result['card'][0]['card_type']);
        }

        return $result['card'][0] ?? [];
    }

    /**
     * @param string $profileId
     * @param array|null $billingData
     * @param $token
     * @param string $cardholderName
     * @param array|false $additional
     * @return bool
     * @throws PaymentException
     */
    public function profileAddCard(string $profileId, $billingData, $token, $cardholderName, $additional = false): bool
    {
        try {
            $data = [
                'token' => [
                    'name' => $cardholderName,
                    'code' => $token
                ]
            ];

            $result = $this->GW->profiles->addCard($profileId, $data);
        } catch (ApiException $e) {
            throw new PaymentException($e->getMessage(), $e->getCode(), $e->getResponse());

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
            $result = $this->GW->profiles->deleteCard($profileId, $cardId);

        } catch (ApiException $e) {
            throw new PaymentException($e->getMessage(), $e->getCode(), $e->getResponse());

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
            case "AM":
                return "amex";
            case "DI":
                return "diners";
            case "JB":
                return "jcb";
            case "MC":
                return "mastercard";
            case "NN":
                return "discover";
            case "VI":
                return "visa";
            default:
                return "undefined";
        }
    }

    /**
     * @param array $data
     * @param float $amount
     * @param string $order_number
     * @return array|false
     * @throws PaymentException
     */
    public function pay(array $data, float $amount, string $order_number)
    {
        $payment_data = array(
            'order_number' => $order_number,
            'amount' => (float)$amount,
        );
        if (isset($data['token']) && $data['name']) {
            $payment_data['payment_method'] = 'token';
            $payment_data['token'] = array(
                'code' => $data['token'],
                'name' => $data['name'],
                'complete' => true,
            );
        } elseif (isset($data['payment_profile']) && $data['card_id']) {
            $payment_data['payment_method'] = 'payment_profile';
            $payment_data['payment_profile'] = array(
                'customer_code' => $data['payment_profile'],
                'card_id' => $data['card_id'],
                'complete' => true,
            );
        } else {
            return false;
        }

        try {
            $response = $this->GW->payments->makePayment($payment_data);
            if ($response['type'] == "PA") {
                $response = $this->GW->payments->completePreAuth($response['id'], $payment_data);
            }
        } catch (ApiException $e) {
            throw new PaymentException($e->getCode() . ' ' . $e->getMessage(), Payment::TRANSACTION_STATUS_ERROR,
                $e->getResponse());

        } catch (Exception $e) {
            throw new PaymentException($e->getCode() . ' ' . $e->getMessage(), Payment::TRANSACTION_STATUS_ERROR);
        }

        return [
            'transaction' => [
                'payment_transaction_status' => Payment::TRANSACTION_STATUS_PENDING,
                'payment_transaction_approved' => $response['approved'],
                'payment_transaction_risk' => $response['risk_score'],
                'payment_transaction_message' => $response['message'],
                'payment_transaction_date' => $response['created'],
                'payment_transaction_remote_id' => $response['id'],
                'payment_transaction_amount' => $response['amount'],
                'payment_transaction_card' => isset($response['card']) ? $this->cardType($response['card']['card_type']) : null,
                'payment_transaction_card_num' => isset($response['card']) && isset($response['card']['last_four']) ? $response['card']['last_four'] : null,
                'payment_transaction_auth_code' => $response['auth_code'],
            ],
            'response' => $response
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
        try {
            $response = $this->GW->payments->returnPayment($transaction_id, [
                'amount' => $amount,
                'order_number' => $trn['order_number']
            ]);
        } catch (ApiException $e) {
            throw new PaymentException($e->getCode() . ' ' . $e->getMessage(), Payment::TRANSACTION_STATUS_ERROR,
                $e->getResponse());

        } catch (Exception $e) {
            throw new PaymentException($e->getCode() . ' ' . $e->getMessage(), Payment::TRANSACTION_STATUS_ERROR);
        }

        return [
            'transaction' => [
                'payment_transaction_status' => Payment::TRANSACTION_STATUS_PENDING,
                'payment_transaction_approved' => $response['approved'],
                'payment_transaction_risk' => $response['risk_score'],
                'payment_transaction_message' => $response['message'],
                'payment_transaction_date' => $response['created'],
                'payment_transaction_remote_id' => $response['id'],
                'payment_transaction_amount' => $response['amount'],
                'payment_transaction_card' => isset($response['card']) ? $this->cardType($response['card']['card_type']) : null,
                'payment_transaction_card_num' => isset($response['card']) && isset($response['card']['last_four']) ? $response['card']['last_four'] : null,
                'payment_transaction_auth_code' => $response['auth_code'],
            ],
            'response' => $response
        ];
    }

    /**
     * @param int $transaction_id
     * @return array
     * @throws PaymentException
     */
    public function void(int $transaction_id): array
    {
        $trn = $this->getTransaction($transaction_id);

        return $this->GW->payments->voidTransaction($transaction_id, [
            'amount' => $trn['amount'],
            'order_number' => $trn['order_number']
        ]);
    }

    /**
     * @param int $transaction_id
     * @return array
     * @throws PaymentException
     */
    public function getTransaction(int $transaction_id): array
    {
        try {
            $result = $this->GW->payments->getPayment($transaction_id);
        } catch (ApiException $e) {
            throw new PaymentException($e->getMessage(), $e->getCode(), $e->getResponse());

        } catch (Exception $e) {
            throw new PaymentException($e->getMessage());
        }

        return $result;
    }

    /**
     * @param int $transaction_id
     * @return array
     * @throws PaymentException
     */
    public function checkTransaction(int $transaction_id): array
    {
        $transaction = $this->getTransaction($transaction_id);
        $settled_amount = $transaction['total_completions'];
        $remote_status = $transaction['type'];
        $refunded = 0;
        $status = '';

        if ($transaction['approved'] != 1) {
            $status = Payment::TRANSACTION_STATUS_DECLINED;
            $remote_status = "Declined";
        }
        switch ($transaction['type']) {
            case "P":
                if (isset($transaction['adjusted_by']) && !empty($transaction['adjusted_by'])) {
                    foreach ($transaction['adjusted_by'] as $adjusted) {
                        if ($adjusted['type'] == 'VP' && $adjusted['approval'] == 1) {
                            $status = Payment::TRANSACTION_STATUS_CANCELED;
                            $remote_status = $adjusted['type'];
                        }
                        if ($adjusted['type'] == 'R' && $adjusted['approval'] == 1) {
                            $refunded += $adjusted['amount'];
                        }
                    }
                    if ($refunded != 0 && $refunded == $transaction['amount']) {
                        $status = Payment::TRANSACTION_STATUS_REFUNDED;
                    }
                }
                if ($settled_amount + $refunded != $transaction['amount']) {
                    $status = Payment::TRANSACTION_STATUS_PENDING;
                }
                break;
            case "PA":
                $status = Payment::TRANSACTION_STATUS_REVIEW;
                if (isset($transaction['adjusted_by']) && !empty($transaction['adjusted_by'])) {
                    foreach ($transaction['adjusted_by'] as $adjusted) {
                        if ($adjusted['type'] == 'PAC' && $adjusted['approval'] == 1) {
                            $status = Payment::TRANSACTION_STATUS_SUCCESS;
                            $remote_status = $adjusted['type'];
                        }
                        if ($adjusted['type'] == 'VP' && $adjusted['approval'] == 1) {
                            $status = Payment::TRANSACTION_STATUS_CANCELED;
                            $remote_status = $adjusted['type'];
                        }
                        if ($adjusted['type'] == 'R' && $adjusted['approval'] == 1) {
                            $refunded += $adjusted['amount'];
                        }
                    }
                    if ($refunded != 0 && $refunded == $transaction['amount']) {
                        $status = Payment::TRANSACTION_STATUS_REFUNDED;
                    }
                }
                if ($settled_amount + $refunded != $transaction['amount']) {
                    $status = Payment::TRANSACTION_STATUS_PENDING;
                }
        }

        return [
            'transaction_id' => $transaction['id'],
            'status' => $status,
            'remote_status' => $remote_status,
            'response_code' => $transaction['approved'],
            'reason_code' => $transaction['message_id'],
            'reason_description' => $transaction['message'],
            'auth_code' => $transaction['auth_code'],
            'amount' => $transaction['amount'],
            'settled_amount' => $settled_amount,
            'response' => $transaction
        ];
    }

    /**
     * @param array $data
     * @param string $dir
     * @return string
     */
    public function getForm(array $data = [], string $dir = 'form'): string
    {
        $CI = get_instance();

        return $CI->load->ext_view(__DIR__ . '/../views/' . $dir . '/bambora', $data, true);
    }

    /**
     * @param string $profileId
     * @return array
     * @throws PaymentException
     */
    public function getProfile(string $profileId): array
    {
        try {
            $result = $this->GW->profiles->getProfile($profileId);
        } catch (ApiException $e) {
            throw new PaymentException($e->getMessage(), $e->getCode(), $e->getResponse());

        } catch (Exception $e) {
            throw new PaymentException($e->getMessage());
        }

        return $result;
    }

    /**
     * @param string $profileId
     * @return array
     * @throws PaymentException
     */
    public function deleteProfile(string $profileId): array
    {
        try {
            $result = $this->GW->profiles->deleteProfile($profileId);
        } catch (ApiException $e) {
            throw new PaymentException($e->getMessage(), $e->getCode(), $e->getResponse());

        } catch (Exception $e) {
            throw new PaymentException($e->getMessage());
        }

        return $result;
    }
}

