<?php

namespace application\modules\billing\models;

use application\core\Database\EloquentModel;
use application\modules\internalPayments\models\InternalPayment;
use application\modules\messaging\models\SmsCounter;
use Illuminate\Support\Carbon;

class SmsOrder extends EloquentModel
{

    protected $table = 'sms_orders';
    protected $primaryKey = 'id';

    /**
     *     id           int auto_increment      primary key
     *     sms_sub_id   int
     *     count        int
     *     remain       int
     *     from         date
     *     to           date
     *     created_at   datetime
     *     updated_at   datetime
     *     sub_name     string
     *     sub_amount   decimal(8,2)
     *     sub_period   enum(year, month)
     *     renewal      enum(period, limit)     default null
     *     card_id      int                     default null
     *     paid         tinyint                 default 0
     *     paid_at      datetime                default null
     *     error_info   varchar(255)            default null
     *     is_active    tinyint                 default 1
     */

    protected $fillable = [
        'sms_sub_id',
        'count',
        'remain',
        'from',
        'to',
        'created_at',
        'updated_at',
        'sub_name',
        'sub_amount',
        'sub_period',
        'renewal',
        'card_id',
        'paid',
        'paid_at',
        'error_info',
        'is_active'
    ];

    protected $appends = [];

    const CREATED_AT = false;
    const UPDATED_AT = false;
    protected $dateFormat = 'Y-m-d';

    public $timestamps = false;

    /**
     * The supported cast types are: integer, real, float, double, decimal:<digits>, string, boolean,
     * object, array, collection, date, datetime, and timestamp.
     * When casting to decimal, you must define the number of digits (decimal:2).
     * @var array
     */
    protected $casts = [
        'sms_sub_id' => 'integer',
        'count' => 'integer',
        'remain' => 'integer',
        'card_id' => 'integer',
        'paid' => 'boolean',
        'is_active' => 'boolean'
    ];

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        //
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function subscription() {
        return $this->belongsTo(SmsSubscription::class, 'sms_sub_id', 'id')
            ->select(['id', 'name', 'count', 'amount', 'period', 'next_date', 'on_out_limit', 'active']);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function lastPayment() {
        return $this->morphOne(InternalPayment::class, 'paymentable')
            ->orderByDesc('created_at');
    }

    public function payments() {
        return $this->morphMany(InternalPayment::class, 'paymentable');
    }

    /**
     * @param $query
     * @return mixed
     */
    public function scopeForCurrentPeriod($query) {
        $currentDate = Carbon::today()->toDateString();

        return $query->where('from', '<=', $currentDate)
            ->where('to', '>=', $currentDate);
    }

    /**
     * @param $query
     * @return mixed
     */
    public function scopeWithAvailableSubscription($query) {
        return $query->whereHas('subscription', function ($query) {
            $query->whereNotNull('id');
        });
    }

    /**
     * @param $query
     * @param null $paid
     * @return mixed
     */
    public function scopePaid($query, $paid = null) {
        if ($paid !== null) {
            return $query->where('paid', $paid);
        }

        return $query;
    }

    /**
     * Get all orders
     *
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public static function getOrders() {
        return SmsOrder::with(['subscription'])
            ->withAvailableSubscription()
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Get active orders
     *
     * @param bool|null $paid
     * @return mixed
     */
    public static function getActiveOrders(bool $paid = null) {
        return SmsOrder::with(['subscription'])
            ->withAvailableSubscription()
//            ->whereHas('subscription', function ($query) {
//                $query->where('active', true);
//            })
            ->forCurrentPeriod()
            ->paid($paid)
            ->orderByDesc('paid')
            ->orderByDesc('created_at')
            ->get();
    }

    public static function getActiveFreeOrder() {
        return SmsOrder::with(['subscription'])
            ->where('sub_amount', 0)
            ->withAvailableSubscription()
            ->forCurrentPeriod()
            ->orderByDesc('created_at')
            ->first();
    }

    /**
     * Get orders transactions
     *
     * @return mixed
     */
    public static function getSmsTransactions($limit = null) {
        $currentDate = Carbon::today()->toDateString();

        return SmsOrder::with([
            'subscription',
            'lastPayment' => function ($query) {
                $query->with([
                    'payment_transaction' => function ($query) {
                        $query->select([
                            'payment_transaction_id',
                            'payment_driver',
                            'payment_transaction_amount',
                            'payment_transaction_approved',
                            'payment_transaction_card',
                            'payment_transaction_card_num',
                            'payment_transaction_message'
                        ]);
                    }
                ]);
            }
        ])
            ->where('from', '<=', $currentDate)
            ->where('is_active', true)
            ->withAvailableSubscription()
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Get subscription for renewal on period
     *
     * @return mixed
     */
    public static function getNextPeriodOrder() {
        $today = Carbon::today()->toDateString();

        return SmsOrder::with(['subscription'])
            ->withAvailableSubscription()
            ->whereHas('subscription', function ($query) {
                $query->where('active', true);
            })
            ->where('from', '>', $today)
            ->where('renewal', 'period')
            ->paid(false)
            ->first();
    }

    /**
     * Get subscription for renewal on limit
     *
     * @return mixed
     */
    public static function getLimitOrder() {
        return SmsOrder::with(['subscription'])
            ->withAvailableSubscription()
            ->whereHas('subscription', function ($query) {
                $query->where('active', true);
            })
            ->whereNull('to')
            ->where('renewal', 'limit')
            ->paid(false)
            ->whereNull('paid_at')
            ->first();
    }

    /**
     * Get unpaid limit order
     *
     * @return mixed
     */
    public static function getUnpaidLimitOrder() {
        return SmsOrder::with(['subscription'])
            ->withAvailableSubscription()
            ->whereHas('subscription', function ($query) {
                $query->where('active', true);
            })
            ->forCurrentPeriod()
            ->where('renewal', 'limit')
            ->where('is_active', true)
            ->paid(false)
            ->first();
    }

    /**
     * Get remain for current period
     *
     * @return mixed
     */
    public static function getCurrentRemain() {
        return SmsOrder::select(['count', 'remain'])
            ->withAvailableSubscription()
            ->whereHas('subscription', function ($query) {
                $query->where('active', true);
            })
            ->forCurrentPeriod()
            ->where('paid', true)
            ->get();
    }

    /**
     * Update sms order remain
     *
     * @param int $numSegment
     * @param bool $addToLimit
     * @return false|void
     */
    public static function updateCurrentRemain(int $numSegment, bool $addToLimit = false) {
        if (!is_numeric($numSegment)) {
            return false;
        }

        $orders = SmsOrder::select(['id', 'count', 'remain'])
            ->forCurrentPeriod()
            ->paid(true)
            ->orderBy('sub_amount', 'asc')
            ->get();

        $ordersCount = $orders->count();

        if ($ordersCount) {
            $prevOrder = null;

            foreach ($orders as $key => $order) {
                $order->updated_at = Carbon::now();

                // skip the record if remain is 0 and there are several of them and the record is not the last
                if ($order->remain === 0 && $ordersCount > 1 && $ordersCount !== $key + 1) {
                    $prevOrder = $order;

                    continue;
                }

                // if remain is 0 and this record is last
                if ($order->remain === 0 && $ordersCount === $key + 1) {
                    $order->remain = $addToLimit ? $numSegment : -$numSegment;
                    $order->save();

                    break;
                }

                // if previous remain is 0 but next period not used yet update previous used
                if ($order->count === $order->remain && $addToLimit && $prevOrder) {
                    $prevOrder->remain = $numSegment;
                    $prevOrder->updated_at = Carbon::now();
                    $prevOrder->save();

                    break;
                }

                if ($order->remain >= $numSegment) {
                    if ($addToLimit) {
                        $order->remain += $numSegment;
                    } else {
                        $order->remain -= $numSegment;
                    }
                    $order->save();

                    break;
                } else {
                    if ($addToLimit) {
                        $order->remain += $numSegment;
                        $order->save();

                        break;
                    } else {
                        // if this is the last element, then keep the negative remain
                        if ($ordersCount === $key + 1) {
                            $order->remain -= $numSegment;
                        } else {
                            // calculate the number of segments remaining for the next element
                            $numSegment = $numSegment - $order->remain;
                            $order->remain = 0;
                        }

                        $order->save();
                    }
                }
            }
        }
    }

    /**
     * Create new order with subscription and counter update
     *
     * @param array $data = [
     *    'subscription' => (SmsSubscription),
     *    'updateOnPeriod' => (bool),
     *    'updateOnOutLimit' => (bool),
     *    'usedPeriod' => (string),                 // current/next
     *    'card_id' => ([string]),
     *    'subscriptionBeforeUpdate' => ([array]),  // for free order notification
     *    'createdSubscription' => ([bool])         // for free order notification
     * ]
     * @param bool $returnError
     * @return bool|array
     */
    public static function createOrder(array $data, bool $returnError = false)
    {
        $subscription = $data['subscription'];
        $usedPeriod = $data['usedPeriod'];

        $beforeUpdate = $subscription->toArray();
        $paymentResult = false;
        $cardId = $subscription->amount > 0 && $data['card_id'] ? $data['card_id'] : null;
        $paid = false;

        if ($subscription->amount > 0 && !$data['updateOnPeriod'] && !$data['updateOnOutLimit']) {
            $driver = config_item('int_pay_driver');
            $cardId = $data['card_id'] ?: config_item('int_pay_default_card_id_' . $driver);

            if (empty($cardId)) {
                if ($returnError) {
                    return [
                        'error' => 'No cardId'
                    ];
                }

                return false;
            }

            $CI =& get_instance();
            $CI->load->helper('internal_payments');

            $paymentDetails = [
                'card_id' => $cardId,
                'entity_description' => 'SMS subscription',
                'entity_item_name' => $subscription->name,
                'amount' => $subscription->amount
            ];

            try {
                $paymentResult = internalPay($paymentDetails);
                $paid = true;
            }
            catch (\Exception $e) {
                if ($returnError) {
                    return [
                        'error' => $e->getMessage()
                    ];
                }

                return false;
            }

            // save in settings default card ID if it doesn't exist
            if (!config_item('int_pay_default_card_id_' . $driver)) {
                $CI->load->helper('settings');
                $defaultCardKey = 'int_pay_default_card_id_' . $driver;
                updateSettings($defaultCardKey, $cardId);
            }
        }

        $next_date = null;
        $today = Carbon::today();

        // $usedPeriod === 'current'
        $from = $today->copy()->startOf($subscription->period);
        $to = $today->copy()->endOf($subscription->period);

        if ($usedPeriod === 'next') {
            $from = $from->copy()->add(1, $subscription->period);
            $to = $from->copy()->endOf($subscription->period);
        }

        $renewal = null;
        $subscriptionUpdated = false;

        if ($data['updateOnPeriod']) {
            if ($subscription->amount > 0) {
                $next_date = $from->copy();
            } else {
                $next_date = $from->copy()->add(1, $subscription->period);
            }

            $subNextDate = $next_date->toDateString();

            if ($subscription->next_date !== $subNextDate) {
                $subscriptionUpdated = true;
                $subscription->next_date = $subNextDate;
                $subscription->add_job = false;

                if (!empty($subscription->job_id)) {
                    deleteJob($subscription->job_id);
                }

                $subscription->job_id = null;
            }

            $renewal = 'period';
        }

        if ($data['updateOnOutLimit']) {
            if (!$subscription->on_out_limit) {
                $subscriptionUpdated = true;
            }

            $subscription->on_out_limit = true;
            $renewal = 'limit';
        }

        $now = Carbon::now();
        $newOrder = SmsOrder::create([
            'sms_sub_id' => $subscription->id,
            'count' => $subscription->count,
            'remain' => $subscription->count,
            'from' => $from->toDateString(),
            'to' => $subscription->amount > 0 && $data['updateOnOutLimit'] ? null : $to->toDateString(),
            'created_at' => $now,
            'updated_at' => $now,
            'sub_name' => $subscription->name,
            'sub_amount' => $subscription->amount,
            'sub_period' => $subscription->period,
            'renewal' => $subscription->amount > 0 ? $renewal : null,
            'card_id' => $cardId,
            'paid' => $subscription->amount == 0 ? true : $paid,
            'paid_at' => $subscription->amount > 0 && $paid ? $now : null,
            'is_active' => !($subscription->amount > 0) || $usedPeriod === 'current' && !$data['updateOnOutLimit']
        ]);

        if ($newOrder) {
            if ($paymentResult && empty($paymentResult['intPayment']->paymentable_id) && empty($paymentResult['intPayment']->paymentable_type)) {
                // associate paymentable entity to internal payment
                $paymentResult['intPayment']->paymentable()->associate($newOrder)->save();
            }

            if ($usedPeriod === 'current' && ($subscription->amount == 0 || !$data['updateOnOutLimit'])) {
                SmsCounter::updateForCurrentOrder($newOrder);
            }

            if ($subscription->next_date && $next_date && $data['updateOnPeriod'] && !$subscription->add_job) {
                $job = [
                    'sub_id' => $subscription->id,
                    'sub_next_date' => $subscription->next_date,
                    'sub_period' => $subscription->period
                ];

                if ($subscription->amount > 0) {
                    $job['order_id'] = $newOrder->id;
                }

                pushJob(
                    'sms_subscriptions/renewal_on_period',
                    $job,
                    $next_date->timestamp,
                    0,
                    $subscription
                );

                $subscription->add_job = true;
            }

            $subscription->updated_at = $now;
            $subscription->save();

            if ($subscription->amount == 0) {
                // send notification about changes
                $CI =& get_instance();
                $CI->load->helper('message');

                $notificationData = [
                    'subscription' => $subscription->toArray(),
                    'order' => $newOrder->toArray()
                ];

                if ($subscriptionUpdated || !empty($data['subscriptionBeforeUpdate'])) {
                    $notificationData['beforeUpdate'] = $data['subscriptionBeforeUpdate'] ?? $beforeUpdate;
                }
                elseif (!empty($data['createdSubscription'])) {
                    $notificationData['createdSubscription'] = true;
                }

                free_subscription_notification($notificationData);
            }

            return true;
        }

        if ($returnError) {
            return [
                'error' => 'Order not created. Unexpected error'
            ];
        }

        return false;
    }

    /**
     * Update order of free subscription if subscription count or period changed
     *
     * @param $data
     * $return bool|null
     * @throws \Exception
     */
    public static function updateFreeOrder($order, $data): ?bool
    {
        if ($order) {
            $update = false;

            if (isset($data['count'])) {
                $update = true;
                $remain = $order->remain;

                // if limit was decreased
                if ($order->count >= $data['count']) {
                    $remain = $data['count'] < $remain ? $data['count'] : $remain;
                } else {
                    // if limit was increased
                    $remain = $data['count'] - ($order->count - $remain);
                }

                $order->count = $data['count'];
                $order->remain = $remain;
            }

            if (isset($data['period'])) {
                $update = true;
                $order->sub_period = $data['period'];
                $order->to = Carbon::today()->endOf($data['period'])->toDateString();
            }

            if ($update) {
                $order->updated_at = Carbon::now();
                $order->save();
            }

            if (isset($data['count']) || isset($data['activity'])) {
                $counter = SmsCounter::getCurrentCountRemain(true);

                if ($counter) {
                    $counter->delete();
                }
            }

            return true;
        }

        return null;
    }

    /**
     * Renewal on out limit
     *
     * @return bool
     */
    public static function renewalOnOutLimit(): bool
    {
        $result = false;

        $freeOrder = SmsOrder::getActiveFreeOrder();

        if ($freeOrder) {
            // renewal on out limit
            $checkingLimit = config_item('sms_subscriptions_min_out_limit') ?? 10;

            if ($freeOrder->subscription->active && $freeOrder->subscription->on_out_limit && $freeOrder->remain <= $checkingLimit) {
                $data = [
                    'subscription' => $freeOrder->subscription,
                    'updateOnPeriod' => false,
                    'updateOnOutLimit' => $freeOrder->subscription->on_out_limit,
                    'usedPeriod' => 'current'
                ];
                $created = SmsOrder::createOrder($data);

                if ($created) {
                    return true;
                }
            }
        }

        $currentUnpaid = SmsOrder::getUnpaidLimitOrder();
        $nextLimitOrder = SmsOrder::getLimitOrder();

        if ($nextLimitOrder && !$currentUnpaid) {
            $driver = config_item('int_pay_driver');
            $cardId = $nextLimitOrder->card_id ?: config_item('int_pay_default_card_id_' . $driver);

            $nextLimitOrder->to = Carbon::today()->endOfMonth()->toDateString();

            if (empty($cardId)) {
                self::saveRenewalOrder($nextLimitOrder, $nextLimitOrder->subscription, 'No card');

                return false;
            }

            $CI =& get_instance();
            $CI->load->helper('internal_payments');

            $paymentDetails = [
                'card_id' => $cardId,
                'entity_description' => 'SMS subscription',
                'entity_item_name' => $nextLimitOrder->sub_name,
                'amount' => $nextLimitOrder->sub_amount,
                'entity' => $nextLimitOrder
            ];

            try {
                $paymentResult = internalPay($paymentDetails);
            }
            catch (\Exception $e) {
                self::saveRenewalOrder($nextLimitOrder, $nextLimitOrder->subscription, $e->getMessage());

                return false;
            }

            if ($paymentResult) {
                self::saveRenewalOrder($nextLimitOrder, $nextLimitOrder->subscription, null, $paymentResult);

                $result = true;
            } else {
                self::saveRenewalOrder($nextLimitOrder, $nextLimitOrder->subscription, 'No payment result');

                $result = false;
            }
        }

        return $result;
    }

    /**
     * Delete free order
     *
     * @param $order
     * @return bool
     */
    public static function deleteFreeOrder($order): bool
    {
        if (!$order) {
            return false;
        }

        $today = Carbon::today();
        $from = new Carbon($order->from);

        try {
            if ($order->delete()) {
                if ($today > $from) {
                    $counter = SmsCounter::getCurrentCountRemain(true);

                    if ($counter) {
                        $counter->delete();
                    }
                }

                return true;
            }
        }
        catch (\Exception $e) {
            return false;
        }

        return false;
    }

    /**
     * Delete renewal order
     *
     * @param $order
     * @return bool
     */
    public static function deleteOrder($order): bool
    {
        if (!$order) {
            return false;
        }

        $subscription = SmsSubscription::find($order->sms_sub_id);

        if (!$subscription) {
            return false;
        }

        $renewal = $order->renewal;

        try {
            if ($order->delete()) {
                $updated = false;

                if ($subscription->next_date && $renewal === 'period') {
                    // delete job
                    if (!empty($subscription->job_id)) {
                        deleteJob($subscription->job_id);
                    }

                    $subscription->next_date = null;
                    $subscription->add_job = false;
                    $subscription->job_id = null;
                    $updated = true;
                } elseif ($subscription->on_out_limit && $renewal === 'limit') {
                    $subscription->on_out_limit = false;
                    $updated = true;
                }

                if ($updated) {
                    $subscription->updated_at = Carbon::now();
                    $subscription->save();
                }

                return true;
            }
        }
        catch (\Exception $e) {
            return false;
        }

        return false;
    }

    /**
     * Renewal on period
     *
     * @param $subscription
     * @param $payload
     * @return bool
     */
    public static function renewalOnPeriod($subscription, $payload): bool
    {
        $today = Carbon::today();

        if ($subscription->amount > 0) {
            if (empty($payload->order_id)) {
                return false;
            }

            $order = SmsOrder::find($payload->order_id);

            if (!$order) {
                return false;
            }

            if (!$today->eq(new Carbon($order->from))) {
                return false;
            }

            $driver = config_item('int_pay_driver');
            $cardId = $order->card_id ?: config_item('int_pay_default_card_id_' . $driver);

            if (empty($cardId)) {
                self::saveRenewalOrder($order, $subscription,'No card');

                return true;
            }

            $CI =& get_instance();
            $CI->load->helper('internal_payments');

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
                self::saveRenewalOrder($order, $subscription, $e->getMessage());

                return true;
            }

            if ($paymentResult) {
                self::saveRenewalOrder($order, $subscription, null, $paymentResult);
            } else {
                self::saveRenewalOrder($order, $subscription, 'No payment result');
            }

            return true;
        } else {
            // handle free subscription
            $subscription->next_date = null;
            $subscription->add_job = false;
            $subscription->job_id = null;
            $subscription->updated_at = Carbon::now();
            $subscription->save();

            $data = [
                'subscription' => $subscription,
                'updateOnPeriod' => true,
                'updateOnOutLimit' => $subscription->on_out_limit,
                'usedPeriod' => 'current'
            ];

            return SmsOrder::createOrder($data);
        }
    }

    /**
     * Save current order and create new next period order
     *
     * @param $order
     * @param $subscription
     * @param string|null $errorInfo
     * @param array|null $paymentResult
     * @return bool
     */
    private static function saveRenewalOrder($order, $subscription, string $errorInfo = null, array $paymentResult = null): bool
    {
        $driver = config_item('int_pay_driver');
        $cardId = $order->card_id ?: config_item('int_pay_default_card_id_' . $driver);

        if ($paymentResult) {
            $order->card_id = $cardId;
            $order->paid = true;
            $order->paid_at = $paymentResult['intPayment']->created_at;

            SmsCounter::updateForCurrentOrder($order);
        }

        $order->error_info = $errorInfo;
        $order->is_active = true;
        $order->updated_at = Carbon::now();
        $order->save();

        $subscription->next_date = null;
        $subscription->add_job = false;
        $subscription->job_id = null;
        $subscription->updated_at = Carbon::now();
        $subscription->save();

        if (!empty($cardId)) {
            $onPeriod = $order->renewal === 'period';
            $data = [
                'subscription' => $subscription,
                'updateOnPeriod' => $onPeriod,
                'updateOnOutLimit' => !$onPeriod,
                'usedPeriod' => $onPeriod ? 'next' : 'current',
                'card_id' => $cardId
            ];

            return SmsOrder::createOrder($data);
        }

        return false;
    }

    /**
     * Update orders card_id
     *
     * @param $cardId
     * @param $toCardId
     * @return bool|int
     */
    public static function updateOrdersCard($cardId, $toCardId) {
        if ($cardId) {
            return SmsOrder::where('card_id', $cardId)
                ->where('paid', false)
                ->update(['card_id' => $toCardId]);
        }

        return false;
    }
}
