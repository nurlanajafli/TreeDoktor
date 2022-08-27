<?php

namespace application\modules\internalPayments\models;

use application\core\Database\EloquentModel;
use application\models\PaymentTransaction;
use Eloquent;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property float $amount
 * @property int $transaction_id
 * @property bool $transaction_approved
 * @property int $payment_alarm
 * @property bool $checked
 * @property int $paymentable_id
 * @property string $paymentable_type
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @mixin Eloquent
 */
class InternalPayment extends EloquentModel
{
    protected $table = 'internal_payments';
    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id',
        'amount',
        'transaction_id',
        'transaction_approved',
        'payment_alarm',
        'checked',
        'paymentable_id',
        'paymentable_type',
        'created_at',
        'updated_at'
    ];

    /**
     * The supported cast types are: integer, real, float, double, decimal:<digits>, string, boolean,
     * object, array, collection, date, datetime, and timestamp.
     * When casting to decimal, you must define the number of digits (decimal:2).
     * @var array
     */
    protected $casts = [
        'user_id' => 'integer',
        'amount' => 'decimal:2',
        'transaction_id' => 'integer',
        'transaction_approved' => 'boolean',
        'payment_alarm' => 'integer',
        'checked' => 'boolean',
        'paymentable_id' => 'integer',
    ];

    public function paymentable() {
        return $this->morphTo();
    }

    public function payment_transaction() {
        return $this->hasOne(PaymentTransaction::class, 'payment_transaction_id', 'transaction_id');
    }

    /**
     * Get internal payments
     *
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Support\Collection
     */
    public static function getPayments() {
        return InternalPayment::with('paymentable')
            ->orderByDesc('created_at')
            ->get()
            ->map(function (InternalPayment $payment) {
                if ($payment->paymentable && isset($payment->paymentable->sub_name)) {
                    $payment->paymentable->name = 'SMS subscription "' . $payment->paymentable->sub_name . '"';
                }

                return $payment;
            });
    }
}
