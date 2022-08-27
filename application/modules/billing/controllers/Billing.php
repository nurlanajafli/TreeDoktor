<?php

use application\modules\billing\models\SmsSubscription;
use application\modules\billing\models\SmsOrder;
use application\modules\messaging\models\SmsCounter;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 * SmsSubscriptions controller
 */
class Billing extends MX_Controller
{
    private $request;

    private $requestError;

    private $intPaymentEnable = false;

    private $paymentProfile;
    
    private $intPaymentDriver;

    function __construct()
    {
        parent::__construct();

        if (!isUserLoggedIn()) {
            redirect('login');
        }

        if(!isSystemUser() || !config_item('messenger')) {
            show_404();
        }

        $this->_title = SITE_NAME;

        try {
            $this->request = app(Request::class);
        } catch (Exception $e) {
            $this->request = null;
            $this->requestError = $e->getMessage();
        }

        $driverParams = [
            'internal_payment' => true
        ];

        try {
            $this->load->library('Payment/ArboStarProcessing', $driverParams, 'arboStarProcessing');
            $this->intPaymentDriver = $this->arboStarProcessing->getAdapter();
            $this->paymentProfile = config_item('int_pay_profile_' . $this->intPaymentDriver);
            $this->intPaymentEnable = true;
        }
        catch (Exception $e) {
            // if int_pay_driver wrong
            $this->intPaymentEnable = false;
        }
    }

    public function _remap($method, $params = array()) {
        if (!$this->request) {
            $data = [
                'status' => 'error',
                'error' => 'Unexpected error',
                'debug' => $this->requestError ?? null
            ];

            return $this->response($data);
        }

        if (method_exists($this, $method)) {
            return call_user_func_array(array($this, $method), $params);
        }

        show_404();
    }

    /**
     * Billing overview
     */
    public function index() {
        $data['title'] = $this->_title . " - Overview";
        $data['view_name'] = 'overview';

        $data['active_services'] = [];
        $data['transactions'] = [];
        $data['sms_counts'] = [
            'total_count' => 0,
            'total_remain' => 0,
            'percent' => 0
        ];

        $sms_orders = SmsOrder::getActiveOrders();

        // key shows in subscription title
        if ($sms_orders->count()) {
            $paidOrders = $sms_orders->filter(function ($val, $key) {
                return $val->paid;
            });

            if ($paidOrders->count()) {
                $data['sms_counts']['total_count'] = $paidOrders->sum('count');
                $data['sms_counts']['total_remain'] = $paidOrders->sum('remain');
                $data['sms_counts']['percent'] = round($data['sms_counts']['total_remain'] * 100 / $data['sms_counts']['total_count']);
            }

            $data['active_services']['SMS'] = $sms_orders;
        }

        $sms_transactions = SmsOrder::getSmsTransactions(5);

        // key shows in subscription title
        if ($sms_transactions->count()) {
            $data['transactions']['SMS'] = $sms_transactions;
        }

        $data['cards'] = [];
        $data['sms_subscriptions_disabled'] = !$this->intPaymentEnable;
        $data['default_card_id'] = null;

        if ($this->intPaymentEnable && $this->paymentProfile) {
            try {
                $data['cards'] = $this->arboStarProcessing->profileCards($this->paymentProfile);
                $data['default_card_id'] = config_item('int_pay_default_card_id_' . $this->intPaymentDriver);

                // move default card to top
                if ($defaultIdx = array_search($data['default_card_id'], array_column($data['cards'], 'card_id'))) {
                    array_unshift($data['cards'], array_splice($data['cards'], $defaultIdx, 1)[0]);
                }
            }
            catch (PaymentException $e) {

            }
        }

        $this->load->view('billing', $data);
    }

    /**
     * Get SMS subscriptions
     */
    public function sms_subscriptions() {
        $data['title'] = $this->_title . " - SMS subscriptions";
        $data['view_name'] = 'sms_subscriptions/subscriptions';

        $data['sms_subscriptions'] = SmsSubscription::getSubscriptions();

        $data['next_period_order'] = SmsOrder::getNextPeriodOrder();
        $data['limit_order'] = SmsOrder::getLimitOrder();
        $data['active_orders'] = SmsOrder::getActiveOrders(true);
        $data['add_limit_order_enable'] = !!sizeof($data['active_orders']);

        if (isSystemUser()) {
            $data['free_subscription'] = SmsSubscription::getFreeSubscription();
            $data['free_update'] = [];

            if ($data['free_subscription']) {
                $data['free_update'] = [
                    'updateSubscription' => $data['free_subscription']
                ];
            }
        }

        $data['cards'] = [];
        $data['sms_subscriptions_disabled'] = !$this->intPaymentEnable;
        $data['default_card_id'] = null;

        if ($this->intPaymentEnable && $this->paymentProfile) {
            try {
                $data['cards'] = $this->arboStarProcessing->profileCards($this->paymentProfile);
                $data['default_card_id'] = config_item('int_pay_default_card_id_' . $this->intPaymentDriver);
            }
            catch (PaymentException $e) {

            }
        }

        $this->load->view('billing', $data);
    }

    public function transactions() {
        $data['title'] = $this->_title . " - Transactions";
        $data['view_name'] = 'transactions';

        $data['transactions'] = [];

        $sms_transactions = SmsOrder::getSmsTransactions();

        // key shows in subscription title
        if ($sms_transactions->count()) {
            $data['transactions']['SMS'] = $sms_transactions;
        }

        $this->load->view('billing', $data);
    }

    /**
     * Create new SMS subscription order
     */
    public function ajax_sms_order_subscription() {
        $return = [
            'status' => 'error',
            'error' => 'Unexpected error'
        ];

        if (!$subId = (int)$this->request->get('id')) {
            return $this->response($return);
        }

        if (!$ccId = $this->request->get('cc_id')) {
            return $this->response([
                'status' => 'error',
                'error' => 'Card processing error',
                'errors' => ['cc_select' => 'Payment card is not selected']
            ]);
        }

        $availablePeriods = [
            'current',
            'next'
        ];
        $usePeriod = $this->request->get('use_period');

        if (in_array($usePeriod, $availablePeriods)) {
            $subscription = SmsSubscription::find($subId);

            if ($subscription) {
                $data = [
                    'subscription' => $subscription,
                    'updateOnPeriod' => !empty($this->request->get('on_period')),
                    'updateOnOutLimit' => !empty($this->request->get('on_out_limit')),
                    'usedPeriod' => $usePeriod,
                    'card_id' => $ccId
                ];
                $created = SmsOrder::createOrder($data, true);

                if (isset($created['error'])) {
                    $return['error'] = $created['error'];
                }
                elseif ($created === true) {
                    $return = [
                        'status' => 'ok',
                        'action' => 'create'
                    ];
                }
            } else {
                $return['error'] = 'No subscription found';
            }
        } else {
            $return['error'] = 'Wrong `Use period`';
        }

        return $this->response($return);
    }

    /**
     * Update SMS order subscription
     */
    public function ajax_update_sms_order_subscription() {
        $return = [
            'status' => 'error',
            'error' => 'No order found'
        ];

        $orderId = (int)$this->request->get('id');
        $cardId = (int)$this->request->get('cc_id');

        if ($orderId) {
            $order = SmsOrder::find($orderId);

            if ($order) {
                if ($order->sub_amount > 0) {
                    if (!$cardId) {
                        $return['error'] = 'No card';

                        return $this->response($return);
                    }

                    if ($order->card_id !== $cardId) {
                        $order->updated_at = Carbon::now();
                        $order->card_id = $cardId;
                        $order->save();
                    }
                } else {
                    if (isSystemUser()) {
                        $count = $this->request->get('count');

                        if (is_null($count)) {
                            $return['error'] = 'Number of SMS is required';

                            return $this->response($return);
                        }

                        try {
                            SmsOrder::updateFreeOrder($order, ['count' => $count]);
                        }
                        catch (Exception $e) {
                            $return['error'] = $e->getMessage();

                            return $this->response($return);
                        }
                    } else {
                        $return['error'] = 'You cannot do this action';

                        return $this->response($return);
                    }
                }

                $return = [
                    'status' => 'ok',
                    'action' => 'update'
                ];
            }
        }

        return $this->response($return);
    }

    /**
     * Pay for active unpaid subscription
     */
    public function ajax_pay_sms_active_order_subscription() {
        $return = [
            'status' => 'error',
            'error' => 'Unexpected error'
        ];

        if (!$orderId = (int)$this->request->get('id')) {
            $return['error'] = 'No order ID';

            return $this->response($return);
        }

        if (!$cardId = $this->request->get('cc_id')) {
            return $this->response([
                'status' => 'error',
                'error' => 'Card processing error',
                'errors' => ['cc_select' => 'Payment card is not selected']
            ]);
        }

        $order = SmsOrder::find($orderId);

        if ($order) {
            $today = Carbon::today();
            $to = new Carbon($order->to);

            if ($today->gt($to)) {
                $return['error'] = 'Expired order';

                return $this->response($return);
            }

            $this->load->helper('internal_payments');

            $paymentDetails = [
                'card_id' => $cardId,
                'entity_description' => 'SMS subscription',
                'entity_item_name' => $order->sub_name,
                'amount' => $order->sub_amount,
                'entity' => $order
            ];

            try {
                $paymentResult = internalPay($paymentDetails);
            }
            catch (\Exception $e) {
                $return['error'] = $e->getMessage();

                return $this->response($return);
            }

            if ($paymentResult) {
                $now = Carbon::now();
                $order->card_id = $cardId;
                $order->paid = true;
                $order->paid_at = $paymentResult['intPayment']->created_at;
                $order->error_info = null;
                $order->updated_at = $now;
                $order->save();

                SmsCounter::updateForCurrentOrder($order);

                $return = [
                    'status' => 'ok',
                    'action' => 'pay'
                ];
            } else {
                $return['error'] = 'Not paid';
            }
        } else {
            $return['error'] = 'No order';
        }

        return $this->response($return);
    }

    /**
     * Create new sms subscription with order
     * for system user only
     */
    public function ajax_create_sms_subscription() {
        if (!isSystemUser()) {
            return $this->errorResponse("You can't create subscriptions");
        }

        $return = [
            'status' => 'error',
            'error' => 'Subscription create error'
        ];

        $params = $this->request->get('params');

        if ($params) {
            $subscription = SmsSubscription::createSubscription($params);

            if ($subscription) {
                $data = [
                    'subscription' => $subscription,
                    'updateOnPeriod' => isset($params['on_period']),
                    'updateOnOutLimit' => isset($params['on_out_limit']),
                    'usedPeriod' => $params['use_period'],
                    'createdSubscription' => true
                ];
                $created = SmsOrder::createOrder($data);

                if ($created !== false) {
                    $return = [
                        'status' => 'ok',
                        'action' => 'create'
                    ];
                } else {
                    $return['error'] = 'Order create error';
                }
            }
        }

        return $this->response($return);
    }

    /**
     * Update subscription
     * for system user only
     */
    public function ajax_update_sms_subscription() {
        if (!isSystemUser()) {
            return $this->errorResponse("You can't update subscriptions");
        }

        $return = [
            'status' => 'error',
            'error' => 'No subscription found'
        ];

        $params = $this->request->get('params');

        if ($params['id']) {
            $params['updateOnPeriod'] = isset($params['on_period']);
            $params['updateOnOutLimit'] = isset($params['on_out_limit']);

            try {
                $updated = SmsSubscription::updateSubscription($params);

                if ($updated !== false) {
                    $return = [
                        'status' => 'ok',
                        'action' => 'update'
                    ];
                } else {
                    $return['error'] = 'No subscription found';
                }
            }
            catch (Exception $e) {
                $return['error'] = $e->getMessage();
            }
        }

        return $this->response($return);
    }

    /**
     * Delete free order
     * for system user only
     */
    public function ajax_delete_free_order() {
        if (!isSystemUser()) {
            return $this->errorResponse("You can't delete order");
        }

        $return = [
            'status' => 'error',
            'error' => 'No order found'
        ];

        $id = (int)$this->request->get('id');

        if ($id) {
            $order = SmsOrder::find($id);

            if ($order && $order->sub_amount == 0) {
                $deleted = SmsOrder::deleteFreeOrder($order);

                if ($deleted) {
                    $return = [
                        'status' => 'ok',
                        'action' => 'delete'
                    ];
                }
            }
        }

        return $this->response($return);
    }

    /**
     * Delete free subscription with orders
     * for system user only
     */
    public function ajax_delete_free_subscription() {
        if (!isSystemUser()) {
            return $this->errorResponse("You can't delete subscription");
        }

        $return = [
            'status' => 'error',
            'error' => 'No subscription found'
        ];

        $id = (int)$this->request->get('id');

        if ($id) {
            $subscription = SmsSubscription::with('orders')->find($id);

            if ($subscription) {
                $deleted = SmsSubscription::deleteFreeSubscription($subscription);

                if ($deleted) {
                    $return = [
                        'status' => 'ok',
                        'action' => 'delete'
                    ];
                }
            }
        }

        return $this->response($return);
    }

    /**
     * Delete SMS order subscription
     */
    public function ajax_delete_sms_subscription() {
        $return = [
            'status' => 'error',
            'error' => 'No subscription found'
        ];

        $id = (int)$this->request->get('order_id');

        if ($id) {
            $order = SmsOrder::find($id);

            if ($order) {
                $deleted = SmsOrder::deleteOrder($order);

                if ($deleted) {
                    $return = [
                        'status' => 'ok',
                        'action' => 'delete'
                    ];
                }
            }
        }

        return $this->response($return);
    }

    /**
     * Delete card
     */
    function ajax_delete_card() {
        if (!$this->intPaymentEnable) {
            return $this->response([
                'status' => 'error',
                'error' => 'Payment driver not found'
            ]);
        }
        
        $card_id = $this->request->get('card_id');

        if (empty($card_id)) {
            return $this->response([
                'status' => 'error',
                'error' => 'No card'
            ]);
        }
        
        try {
            $this->arboStarProcessing->profileDeleteCard($this->paymentProfile, $card_id, $this->intPaymentDriver);

            // update orders
            $default_card_id = config_item('int_pay_default_card_id_' . $this->intPaymentDriver);
            SmsOrder::updateOrdersCard($card_id, $default_card_id);
        } catch (PaymentException $e) {
            return $this->response([
                'status' => 'error',
                'error' => $e->getMessage()
            ]);
        }

        return $this->response(['status' => 'ok']);
    }

    /**
     * Set card as default
     */
    function ajax_set_default_card() {
        $card_id = $this->request->get('card_id');

        if (empty($card_id)) {
            return $this->response([
                'status' => 'error',
                'error' => 'No card'
            ]);
        }

        // save to settings default card ID
        $this->load->helper('settings');
        $defaultCardKey = 'int_pay_default_card_id_' . $this->intPaymentDriver;
        $result = updateSettings($defaultCardKey, $card_id);

        if ($result) {
            return $this->response(['status' => 'ok']);
        } else {
            return $this->response([
                'status' => 'error',
                'error' => 'Unexpected error. Please try later.'
            ]);
        }
    }
}
