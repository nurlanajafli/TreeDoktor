<?php

namespace application\models;

use application\core\Database\EloquentModel;
use Illuminate\Support\Carbon;

/**
 * @method static \Illuminate\Database\Eloquent\Builder|\application\models\PaymentTransaction newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\application\models\PaymentTransaction newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\application\models\PaymentTransaction query()
 * @property int $payment_transaction_id
 * @property int $payment_transaction_status
 * @property int $client_id
 * @property int $estimate_id
 * @property int|null $invoice_id
 * @property string $payment_driver
 * @property int|null $payment_transaction_remote_id
 * @property mixed $payment_transaction_amount
 * @property bool $payment_transaction_approved
 * @property float $payment_transaction_risk
 * @property string|null $payment_transaction_order_no
 * @property string|null $payment_transaction_card
 * @property string|null $payment_transaction_card_num
 * @property Carbon|null $payment_transaction_date
 * @property string|null $payment_transaction_message
 * @property array|null $payment_transaction_log
 * @property string|null $payment_transaction_auth_code
 * @property string|null $payment_transaction_remote_reason_code
 * @property string|null $payment_transaction_remote_reason_description
 * @property string|null $payment_transaction_remote_status
 * @property mixed|null $payment_transaction_settled_amount
 * @method static \Illuminate\Database\Eloquent\Builder|\application\models\PaymentTransaction whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\models\PaymentTransaction whereEstimateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\models\PaymentTransaction whereInvoiceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\models\PaymentTransaction wherePaymentDriver($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\models\PaymentTransaction wherePaymentTransactionAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\models\PaymentTransaction wherePaymentTransactionApproved($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\models\PaymentTransaction wherePaymentTransactionAuthCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\models\PaymentTransaction wherePaymentTransactionCard($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\models\PaymentTransaction wherePaymentTransactionCardNum($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\models\PaymentTransaction wherePaymentTransactionDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\models\PaymentTransaction wherePaymentTransactionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\models\PaymentTransaction wherePaymentTransactionLog($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\models\PaymentTransaction wherePaymentTransactionMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\models\PaymentTransaction wherePaymentTransactionOrderNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\models\PaymentTransaction wherePaymentTransactionRemoteId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\models\PaymentTransaction wherePaymentTransactionRemoteReasonCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\models\PaymentTransaction wherePaymentTransactionRemoteReasonDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\models\PaymentTransaction wherePaymentTransactionRemoteStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\models\PaymentTransaction wherePaymentTransactionRisk($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\models\PaymentTransaction wherePaymentTransactionSettledAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\models\PaymentTransaction wherePaymentTransactionStatus($value)
 * @mixin \Eloquent
 * @property int|null $payment_transaction_ref_id
 * @property string $payment_transaction_type
 * @method static \Illuminate\Database\Eloquent\Builder|\application\models\PaymentTransaction wherePaymentTransactionRefId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\models\PaymentTransaction wherePaymentTransactionType($value)
 */
class PaymentTransaction extends EloquentModel
{
    protected $table = 'payment_transactions';
    protected $primaryKey = 'payment_transaction_id';

    protected $fillable = [
        'payment_transaction_status',
        'client_id',
        'estimate_id',
        'invoice_id',
        'payment_driver',
        'payment_transaction_remote_id',
        'payment_transaction_amount',
        'payment_transaction_approved',
        'payment_transaction_risk',
        'payment_transaction_order_no',
        'payment_transaction_card',
        'payment_transaction_card_num',
        'payment_transaction_date',
        'payment_transaction_message',
        'payment_transaction_log',
        'payment_transaction_auth_code',
        'payment_transaction_remote_reason_code',
        'payment_transaction_remote_reason_description',
        'payment_transaction_remote_status',
        'payment_transaction_settled_amount',
        'payment_transaction_ref_id',
        'payment_transaction_type',
    ];

    /**
     * The supported cast types are: integer, real, float, double, decimal:<digits>, string, boolean,
     * object, array, collection, date, datetime, and timestamp.
     * When casting to decimal, you must define the number of digits (decimal:2).
     * @var array
     */
    protected $casts = [
        'payment_transaction_status' => 'integer',
        'client_id' => 'integer',
        'estimate_id' => 'integer',
        'invoice_id' => 'integer',
        'payment_transaction_remote_id' => 'integer',
        'payment_transaction_amount' => 'decimal:2',
        'payment_transaction_approved' => 'boolean',
        'payment_transaction_risk' => 'float',
        'payment_transaction_log' => 'array',
        'payment_transaction_settled_amount' => 'decimal:2',
    ];

    /**
     * @param int $id
     * @param array $data
     * @return bool|int
     */
    public static function updateTransaction(int $id, array $data) {
        if (empty($id) || empty($data)) {
            return false;
        }

        return PaymentTransaction::where('payment_transaction_id', $id)
            ->update($data);
    }
}
