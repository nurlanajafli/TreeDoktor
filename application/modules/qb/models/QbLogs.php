<?php

namespace application\modules\qb\models;

use application\core\Database\EloquentModel;
use application\modules\user\models\User;
use Carbon\Carbon;

class QbLogs extends EloquentModel
{
    // module constants
    const MODULE_CLIENT = 1;
    const MODULE_INVOICE = 2;
    const MODULE_PAYMENT = 3;
    const MODULE_ITEM = 4;
    const MODULE_CLASS = 5;

    // action constants
    const ACTION_CREATE = 1;
    const ACTION_UPDATE = 2;
    const ACTION_DELETE = 3;
    const ACTION_GET = 4;

    // route constants
    const ROUTE_PUSH = 1;
    const ROUTE_PULL = 2;

    // result
    const RESULT_SUCCESS = 1;
    const RESULT_ERROR = 0;

    protected $table = 'qb_logs';

    protected $primaryKey = 'log_id';

    protected $fillable = [
        'log_module_id',
        'log_entity_id',
        'log_status_code',
        'log_message',
        'log_action',
        'log_route',
        'log_result',
        'log_user_id',
        'log_created_at'
    ];

    const CREATED_AT = null;

    /**
     * The supported cast types are: integer, real, float, double, decimal:<digits>, string, boolean,
     * object, array, collection, date, datetime, and timestamp.
     * When casting to decimal, you must define the number of digits (decimal:2).
     * @var array
     */
    protected $casts = [
        'log_module_id' => 'integer',
        'log_entity_id' => 'integer',
        'log_status_code' => 'integer',
        'log_message' => 'string',
        'log_action' => 'integer',
        'log_route' => 'integer',
        'log_result' => 'integer',
        'log_user_id' => 'integer',
        'log_created_at' => 'timestamp'
    ];

    // Relationships

    public function user()
    {
        return $this->hasOne(User::class, 'user_id');
    }

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        self::creating(function ($row){
            $result = $row->log_result == 0 ? 2 : 1;

            if($row->log_module_id == self::MODULE_CLIENT)
                \DB::table('clients')->where(['client_id' => $row->log_entity_id])->update(['client_last_qb_sync_result' => $result, 'client_last_qb_time_log' => Carbon::now()]);
            elseif($row->log_module_id == self::MODULE_INVOICE)
                \DB::table('invoices')->where(['id' => $row->log_entity_id])->update(['invoice_last_qb_sync_result' => $result, 'invoice_last_qb_time_log' => Carbon::now()]);
            elseif($row->log_module_id == self::MODULE_PAYMENT)
                \DB::table('client_payments')->where(['payment_id' => $row->log_entity_id])->update(['payment_last_qb_sync_result' => $result, 'payment_last_qb_time_log' => Carbon::now()]);
            elseif($row->log_module_id == self::MODULE_ITEM)
                \DB::table('services')->where(['service_id' => $row->log_entity_id])->update(['service_last_qb_sync_result' => $result, 'service_last_qb_time_log' => Carbon::now()]);
            elseif($row->log_module_id == self::MODULE_CLASS)
                \DB::table('categories')->where(['category_id' => $row->log_entity_id])->update(['category_last_qb_sync_result' => $result, 'category_last_qb_time_log' => Carbon::now()]);
        });
    }
}
