<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

use application\models\PaymentTransaction;
use application\modules\internalPayments\models\InternalPayment;

class InternalPayments extends MX_Controller
{
    private $driver;
    private $intPayProfile;
    private $billingData;

    function __construct()
    {
        parent::__construct();

        //Checking if user is logged in;
        if (!isUserLoggedIn()) {
            redirect('login');
        }

        $this->_title = SITE_NAME;

        $driverParams = [
            'internal_payment' => true
        ];

        try {
            $this->load->library('Payment/ArboStarProcessing', $driverParams, 'arboStarProcessing');

            $this->driver = $this->arboStarProcessing->getAdapter();
            $this->intPayProfile = !empty(config_item('int_pay_profile_' . $this->driver))
                ? config_item('int_pay_profile_' . $this->driver)
                : null;

            $this->billingData = [
                'customer_id' => 0,
                'name' => request()->user()->full_name ?? '',
                'address' => config_item('office_address'),
                'city' => config_item('office_city'),
                'state' => config_item('office_state'),
                'zip' => config_item('office_zip'),
                'country' => config_item('office_country'),
                'phone' => config_item('office_phone_mask') ?? null,
                'profile_id' => $this->intPayProfile,
                'internal_payment' => true
            ];
        }
        catch (Exception $e) {
            // if int_pay_driver wrong
            throw new Exception('Internal Payment driver error');
        }
    }

    /**
     * Payment info
     */
    function payments_info() {
        if (!isSystemUser()) {
            show_404();
        }

        $data['title'] = 'Internal payments';
        $data['payments'] = InternalPayment::getPayments()->toArray();
        $data['internal_payment'] = true;

        $this->load->view('internalPayments/internal_payments', $data);
    }

    /**
     * Get card form
     *
     * @uses $_POST['type'] = 'int_payment'
     */
    function ajax_get_card_form()
    {
        if (request('type') !== 'int_payment') {
            return $this->response([
                'status' => 'error',
                'error' => 'Not internal payment'
            ]);
        }

        $card_form = $this->arboStarProcessing->getCardForm($this->billingData);

        return $this->response($card_form, 200);
    }

    /**
     * Add card to profile or create profile
     *
     * @uses $_POST['token']        (string|array)
     * @uses $_POST['crd_name']     (string)
     * @uses $_POST['additional']   (array|null)
     */
    function ajax_save_billing()
    {
        $token = request('token');
        $cardholderName = request('crd_name');
        $additional = request('additional');

        if (empty($token)) {
            return $this->response([
                'status' => 'error',
                'error' => 'No required data'
            ], 400);
        }

        if ($this->intPayProfile) {
            try {
                $this->arboStarProcessing->profileAddCard(
                    $this->intPayProfile,
                    $this->billingData,
                    $token,
                    $cardholderName,
                    $additional
                );
            } catch (PaymentException $e) {
                return $this->response([
                    'status' => 'error',
                    'error' => $e->getMessage()
                ]);
            }
        } else {
            try {
                $this->intPayProfile = $this->arboStarProcessing->createProfile(
                    $this->billingData,
                    $token,
                    $cardholderName,
                    $additional
                );
            } catch (PaymentException $e) {
                return $this->response([
                    'status' => 'error',
                    'error' => $e->getMessage()
                ]);
            }

            $this->load->helper('settings');
            $profileKey = 'int_pay_profile_' . $this->driver;
            $result = updateSettings($profileKey, $this->intPayProfile);

            if (!$result) {
                return $this->response([
                    'status' => 'error',
                    'error' => 'Unexpected error. Try later.'
                ]);
            }
        }

        try {
            $cards = $this->arboStarProcessing->profileCards($this->intPayProfile);
        }
        catch (PaymentException $e) {
            $cards = [];
        }

        if (!empty($cards)) {
            foreach ($cards as &$card){
                $card['number'] = '['.$card['card_type'].'] '.$card['number'].' ('.$card['expiry_month'].'/'.$card['expiry_year'].')';
            }
        }

        return $this->response([
            'status' => 'ok',
            'cards' => $cards
        ]);
    }

    /**
     * Refund payment
     *
     * @uses $_POST['params'] = [
     *      'refund_payment_id' => (int),
     *      'refund_payment_amount' => (float),
     *      'refund_password' => (string)
     * ]
     */
    function ajax_refund_payment()
    {
        if (!isSystemUser()) {
            return $this->response(['status' => 'error', 'error' => 'Not allowed!']);
        }

        $params = request('params');

        if (empty($params['refund_payment_id'])) {
            return $this->response(['status' => 'error', 'error' => 'not valid PaymentId'], 200);
        }

        if (empty($params['refund_payment_amount'])
            || !$amount = getAmount($params['refund_payment_amount']))
        {
            return $this->response([
                'status' => 'error',
                'errors' => ['refund_payment_amount' => 'Incorrect Refund Amount']
            ], 200);
        }

        if (empty($params['refund_password'])) {
            return $this->response(['status' => 'error', 'errors' => ['refund_password' => 'Empty password']], 200);
        }

        $pwd = $params['refund_password'];
        $user = request()->user();

        if ($user->password !== md5($pwd)) {
            return $this->response(['status' => 'error', 'errors' => ['refund_password' => 'Invalid password']], 200);
        }

        if (!$payment = InternalPayment::find($params['refund_payment_id'])) {
            return $this->response(['status' => 'error', 'error' => 'Payment not found'], 200);
        }

        try {
            $refunded = $this->arboStarProcessing->internalRefund([
                'payment_data' => $payment,
                'amount' => abs($amount)
            ]);

            if ($refunded) {
                return $this->response(['status' => 'ok'], 200);
            }
        } catch (PaymentException $e) {
            return $this->response(['status' => 'error', 'error' => $e->getMessage()], 200);
        }

        $this->response(['status' => 'error', 'error' => 'Fail'], 200);
    }

    /**
     * Get transaction details
     *
     * @uses $_POST['transaction_id']   (int)
     */
    function ajax_get_transaction_details()
    {
        $transactionId = request('transaction_id');

        if (!$transaction = PaymentTransaction::find($transactionId)) {
            return $this->response(['status' => 'error', 'error' => 'Transaction not found'], 200);
        }

        $transaction->payment_transaction_status = $this->arboStarProcessing->statusToText($transaction->payment_transaction_status);

        return $this->response([
            'status' => 'ok',
            'html' => $this->load->view('internalPayments/transaction_details', ['transaction' => $transaction], true)
        ], 200);
    }

    public function ajax_payment()
    {
//        if (request('type') !== 'int_payment') {
//            return $this->response([
//                'status' => 'error',
//                'error' => 'Not internal payment.'
//            ]);
//        }
//
//        if (!$ccId = request('cc_id')) {
//            return $this->response([
//                'status' => 'error',
//                'error' => 'Card processing error',
//                'errors' => ['cc_select' => 'Payment card is not selected']
//            ]);
//        }


        return $this->response([
            'status' => 'not implemented'
        ], 501);
    }

    function ajax_edit_payment()
    {
        return $this->response([
            'status' => 'not implemented'
        ], 501);
    }

    function ajax_get_payment()
    {
        return $this->response([
            'status' => 'not implemented'
        ], 501);
    }

    function ajax_delete_payment()
    {
        return $this->response([
            'status' => 'not implemented'
        ], 501);
    }
}
