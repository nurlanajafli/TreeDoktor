<?php

namespace application\modules\billing\models;

use application\core\Database\EloquentModel;
use application\modules\messaging\models\SmsCounter;
use Illuminate\Support\Carbon;

class SmsSubscription extends EloquentModel
{

    protected $table = 'sms_subscriptions';
    protected $primaryKey = 'id';

    /**
     *     id           int auto_increment      primary key
     *     name         varchar(255)            null
     *     count        int
     *     amount       decimal(8,2)
     *     period       enum[year, month]
     *     next_date    date                    null
     *     on_out_limit tinyint(1) default 0
     *     description  text                    null
     *     active       tinyint(1) default 0
     *     add_job      tinyint(1) default 0
     *     created_at   datetime
     *     updated_at   datetime
     *     job_id       int                     null    // stored job_id
     */

    protected $fillable = [
        'name',
        'count',
        'amount',
        'period',
        'next_date',
        'on_out_limit',
        'description',
        'active',
        'add_job',
        'created_at',
        'updated_at',
        'job_id'
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
        'count' => 'integer',
        'amount' => 'decimal:2',
        'on_out_limit' => 'boolean',
        'active' => 'boolean',
        'add_job' => 'boolean',
        'job_id' => 'integer'
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
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function orders() {
        return $this->hasMany(SmsOrder::class, 'sms_sub_id', 'id');
    }

    public function scopeActive($query) {
        return $query->where('active', 1);
    }

    public function scopePaidOnly($query) {
        return $query->where('amount', '>', 0);
    }

    /**
     * Get paid subscriptions
     *
     * @return mixed
     */
    public static function getSubscriptions() {
        return SmsSubscription::paidOnly()
            ->limit(3)
            ->get();
    }

    /**
     * Get active subscription by ID
     *
     * @param int $id
     * @return SmsSubscription|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object|null
     */
    public static function getActiveSubscription($id) {
        return SmsSubscription::where('id', $id)
            ->where('active', 1)
            ->first();
    }

    /**
     * Get free subscription
     *
     * @return mixed
     */
    public static function getFreeSubscription() {
        return SmsSubscription::where('amount', 0)
            ->with([
                'orders' => function($query) {
                    $query->forCurrentPeriod();
                }
            ])
            ->first();
    }

    /**
     * Create subscription
     *
     * @param $data
     * @return SmsSubscription|\Illuminate\Database\Eloquent\Model
     */
    public static function createSubscription($data) {
        // return subscription if already exists
        if (!empty($data['id'])) {
            $subscription = SmsSubscription::find($data['id']);

            if ($subscription) {
                // update if needed
                $data['subscription'] = $subscription;
                $updated = SmsSubscription::updateSubscription($data);

                return $updated ?? $subscription;
            }
        }

        $nextDate = null;

        if (isset($data['on_period'])) {
            $nextDate = Carbon::today();
            $nextDate = $nextDate->startOf($data['period']);
            $nextDate = $nextDate->add(1, $data['period'])->toDateString();
        }

        $now = Carbon::now();
        return SmsSubscription::create([
            'name' => $data['name'],
            'count' => $data['count'],
            'amount' => $data['amount'],
            'period' => $data['period'],
            'next_date' => $nextDate,
            'on_out_limit' => isset($data['on_out_limit']) ? 1 : 0,
            'description' => $data['description'],
            'active' => 1,
            'created_at' => $now,
            'updated_at' => $now
        ]);
    }

    /**
     * Update subscription
     *
     * @param $data
     * @return mixed
     */
    public static function updateSubscription($data) {
        $id = isset($data['order']) ? $data['order']->sms_sub_id : $data['id'];

        // $data['subscription'] exists if updated free subscription
        $subscription = $data['subscription'] ?? SmsSubscription::find($id);

        if (!$subscription) {
            return false;
        }

        $beforeUpdate = $subscription->toArray();

        $isFree = $subscription->amount == 0;

        $update = false;
        $periodUpdated = false;
        $renewalPeriod = false;

        if (isset($data['name']) && $subscription->name != $data['name']) {
            $update = true;
            $subscription->name = $data['name'];
        }

        if (isset($data['description']) && $subscription->description != $data['description']) {
            $update = true;
            $subscription->description = $data['description'];
        }

        if (isset($data['amount']) && $subscription->amount != $data['amount']) {
            $update = true;
            $subscription->amount = $data['amount'];
        }

        if (isset($data['count']) && $subscription->count != $data['count']) {
            $update = true;
            $subscription->count = $data['count'];
        }

        if (isset($data['period']) && $subscription->period != $data['period']) {
            $update = true;
            $periodUpdated = $isFree;
            $subscription->period = $data['period'];
        }

        if ($isFree) {
            if ($data['updateOnPeriod'] && ($periodUpdated || $subscription->next_date === null)) {
                $update = true;
                $renewalPeriod = true;

                if (!empty($subscription->job_id)) {
                    deleteJob($subscription->job_id);
                }

                $subscription->add_job = false;
                $subscription->job_id = null;
            } elseif (!$data['updateOnPeriod'] && $subscription->next_date !== null) {
                $update = true;
                $subscription->next_date = null;
                $subscription->add_job = false;

                if (!empty($subscription->job_id)) {
                    deleteJob($subscription->job_id);
                }

                $subscription->job_id = null;
            }

            if ($data['updateOnOutLimit'] && !$subscription->on_out_limit) {
                $update = true;
                $subscription->on_out_limit = true;
            }
            elseif (!$data['updateOnOutLimit'] && $subscription->on_out_limit) {
                $update = true;
                $subscription->on_out_limit = false;
            }
        } else {
            if (isset($data['active']) && !$subscription->active) {
                $update = true;
                $subscription->active = true;
            }
            elseif (!isset($data['active']) && $subscription->active) {
                $update = true;
                $subscription->active = false;
            }
        }

        $result = null;

        if ($update) {
            if ($isFree && $renewalPeriod) {
                // update subscription next_date
                $nextDate = Carbon::today()->startOf($subscription->period)->add(1, $subscription->period);
                $subscription->next_date = $nextDate->toDateString();

                pushJob('sms_subscriptions/renewal_on_period',
                    [
                        'sub_id' => $subscription->id,
                        'sub_next_date' => $subscription->next_date,
                        'sub_period' => $subscription->period
                    ],
                    $nextDate->timestamp,
                    0,
                    $subscription
                );

                $subscription->add_job = true;
            }

            $subscription->updated_at = Carbon::now();
            $subscription->save();

            if ($isFree && (!isset($data['add_free_order']) || !$data['add_free_order'])) {
                // send notification about changes
                $CI =& get_instance();
                $CI->load->helper('message');
                $notificationData = [
                    'beforeUpdate' => $beforeUpdate,
                    'subscription' => $subscription->toArray()
                ];

                free_subscription_notification($notificationData);
            }

            $result = true;
        }

        if ($isFree && isset($data['add_free_order']) && $data['add_free_order']) {
            $orderData = [
                'subscription' => $subscription,
                'updateOnPeriod' => $subscription->next_date !== null,
                'updateOnOutLimit' => $subscription->on_out_limit,
                'usedPeriod' => 'current',
                'subscriptionBeforeUpdate' => $update ? $beforeUpdate : null
            ];

            SmsOrder::createOrder($orderData);
        }

        if ($result) {
            // if updated free subscription
            if (isset($data['subscription'])) {
                return $subscription;
            }

            return true;
        } else {
            return null;
        }
    }

    /**
     * Delete free subscription
     *
     * @param $subscription
     * @return bool
     */
    public static function deleteFreeSubscription($subscription): bool
    {
        if (!$subscription) {
            return false;
        }

        try {
            $subscription->orders()->delete();

            if ($subscription->delete()) {
                $counter = SmsCounter::getCurrentCountRemain(true);

                if ($counter) {
                    $counter->delete();
                }

                return true;
            }
        }
        catch (\Exception $e) {
            return false;
        }

        return false;
    }
}
