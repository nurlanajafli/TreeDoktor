<?php

namespace application\modules\payments\models;
use application\core\Database\EloquentModel;
use application\modules\estimates\models\Estimate;
use application\modules\invoices\models\Invoice;
use application\modules\user\models\User;
use DB;
class ClientPayment extends EloquentModel
{
    const ATTR_PAYMENT_ID = 'payment_id';
    const ATTR_ESTIMATE_ID = 'estimate_id';
    const ATTR_PAYMENT_TYPE = 'payment_type';
    const ATTR_PAYMENT_DATE = 'payment_date';
    const ATTR_PAYMENT_AMOUNT = 'payment_amount';
    const ATTR_PAYMENT_FEE = 'payment_fee';
    const ATTR_PAYMENT_FEE_PERCENT = 'payment_fee_percent';
    const ATTR_PAYMENT_TIPS = 'payment_tips';
    const ATTR_PAYMENT_FILE = 'payment_file';
    const ATTR_PAYMENT_CHECKED = 'payment_checked';
    const ATTR_PAYMENT_AUTHOR = 'payment_author';
    const ATTR_PAYMENT_ACCOUNT = 'payment_account';
    const ATTR_PAYMENT_TRANS_ID = 'payment_trans_id';
    const ATTR_PAYMENT_ALARM = 'payment_alarm';
    const ATTR_PAYMENT_QB_ID = 'payment_qb_id';
    const ATTR_PAYMENT_METHOD_INT = 'payment_method_int';
    const ATTR_PAYMENT_LAST_QB_TIME_LOG = 'payment_last_qb_time_log';
    const ATTR_PAYMENT_LAST_QB_SYNC_RESULT = 'payment_last_qb_sync_result';
    const ATTR_PAYMENT_NOTES = 'payment_notes';

    public $base_fields = [
        'client_payments.payment_id',
        'client_payments.estimate_id',
        'client_payments.payment_date',
        'client_payments.payment_type',
        'client_payments.payment_amount',
    ];

    /**
     * @var string
     */
    protected $table = 'client_payments';

    /**
     * @var string
     */
    protected $primaryKey = 'payment_id';

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function estimates()
    {
        return $this->hasOne(Estimate::class, 'estimate_id', 'estimate_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function users()
    {
        return $this->hasOne(User::class, 'payment_author', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function payment_account()
    {
        return $this->hasOne(PaymentAccount::class, 'payment_account', 'payment_account_id');
    }

    public function invoice(){
        return $this->belongsTo(Invoice::class, 'estimate_id', 'estimate_id');
    }

    /**
     * @param array $wdata
     * @param string $limit
     * @param string $start
     * @param string $order
     * @return array
     */
    public function get_payments($wdata = array(), $limit = '', $start = '', $order = '') {

        $query = ClientPayment::where('client_payments.payment_amount', '<>', 0);
        $query->join('estimates', 'estimates.estimate_id' , '=','client_payments.estimate_id');
        $query->leftJoin('clients', 'clients.client_id', '=', 'estimates.client_id');
        $query->leftJoin('users', 'users.id', '=', 'client_payments.payment_author');
        $query->leftJoin('payment_account', 'payment_account.payment_account_id', '=', 'client_payments.payment_account');
        $query->join('leads', 'estimates.lead_id', '=', 'leads.lead_id');

        if ($limit != '') {
            $query->limit($limit)->offset($start);
        }
        if (!empty($wdata)) {
            $query->where($wdata);
        }
        if ($order == '') {
            $query->orderBy('payment_date', 'DESC');
        } else {
            $query->orderBy($order);
        }

        return $query->get()->toArray();
    }

    /**
     * @param $query
     * @return mixed
     */
    function scopeBaseFields($query)
    {
        $query_string = implode(',', $this->base_fields);
        return $query->select(DB::raw($query_string));
    }
}
