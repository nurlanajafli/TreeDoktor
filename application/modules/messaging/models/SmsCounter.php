<?php

namespace application\modules\messaging\models;

use application\core\Database\EloquentModel;
use application\modules\billing\models\SmsOrder;
use Illuminate\Support\Carbon;

class SmsCounter extends EloquentModel
{

    protected $table = 'sms_counters';
    protected $primaryKey = 'id';

    /**
     *     id           int auto_increment      primary key
     *     month        date
     *     count        int
     *     remain       int
     *     created_at   datetime
     *     updated_at   datetime
     */

    protected $fillable = [
        'month',
        'count',
        'remain',
        'created_at',
        'updated_at'
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
        'remain' => 'integer'
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
     * Get counts for current period
     *
     * @return mixed
     */
    public static function getCurrentCountRemain($onlyExisting = false) {
        $currentCount = SmsCounter::select(['id', 'count', 'remain'])
            ->whereMonth('month', Carbon::now()->month)
            ->whereYear('month', Carbon::now()->year)
            ->first();

        if (!$currentCount && !$onlyExisting) {
            $currentCount = SmsCounter::createCurrentPeriod();
        }

        return $currentCount;
    }

    /**
     * Create new counters for current period
     *
     * @return SmsCounter|null
     */
    public static function createCurrentPeriod(): ?SmsCounter
    {
        $currentDate = Carbon::now();
        $ordersCounts = SmsOrder::getCurrentRemain();

        $newCounter = null;
        $remain = $ordersCounts->sum('remain');

        if ($remain > 0) {
            $count = $ordersCounts->sum('count');
            $newCounter = SmsCounter::create([
                'month' => date('Y-m-1'),
                'count' => $count,
                'remain' => $remain,
                'created_at' => $currentDate,
                'updated_at' => $currentDate
            ]);
            $newCounter->makeHidden(['month', 'created_at', 'updated_at']);
        }

        return $newCounter;
    }

    /**
     * Update current remain in SmsCounters and SmsOrders
     *
     * @param $numSegments
     * @param bool $addToLimit
     * @return false|void
     */
    public static function updateCurrentRemain($numSegments, bool $addToLimit = false) {
        if (!is_numeric($numSegments)) {
            return false;
        }

        $current = SmsCounter::getCurrentCountRemain();
        $remain = $current->remain;

        // renewal on out limit
        $checkingLimit = config_item('sms_subscriptions_min_out_limit') ?? 10;

        if ($remain <= $checkingLimit && !$addToLimit) {
            $renewal = SmsOrder::renewalOnOutLimit();

            if ($renewal) {
                $current = SmsCounter::getCurrentCountRemain();
                $remain = $current->remain;
            }
        }

        if ($addToLimit) {
            $remain += $numSegments;
        } else {
            $remain -= $numSegments;
        }

        $current->remain = $remain;
        $current->updated_at = Carbon::now();
        $current->save();
        SmsOrder::updateCurrentRemain($numSegments, $addToLimit);
    }

    /**
     * Update current Counter if created new order with current period
     *
     * @param $order
     * @return bool
     */
    public static function updateForCurrentOrder($order): bool
    {
        $current = SmsCounter::getCurrentCountRemain(true);

        if ($current) {
            $current->count = $current->count + $order->count;
            $current->remain = $current->remain + $order->remain;
            $current->updated_at = Carbon::now();
            $current->save();

            return true;
        }

        return false;
    }
}
