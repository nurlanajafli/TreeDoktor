<?php

namespace application\modules\webhook\models;

use application\core\Database\EloquentModel;
use application\modules\emails\models\Email;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class EmailLogs extends EloquentModel
{
    protected $table = 'email_logs';
    protected $primaryKey = 'email_log_id';

    /**
     *     email_log_id                 bigint auto_increment            primary key
     *     email_log_email_id           bigint
     *     email_log_message_id         varchar(255)                         null
     *     email_log_tracking_id        varchar(255)                         null
     *     email_log_tracking_status    enum(accepted, delivered, rejected, bounce, complained, unsubscribed, error, opened, clicked)
     *     email_log_tracking_details   text                                 null
     *     email_log_error              text                                 null
     *     email_log_provider           varchar(255)
     *     email_log_created_at         datetime
     */

    protected $fillable = [
        'email_log_id',
        'email_log_email_id',
        'email_log_message_id',
        'email_log_tracking_id',
        'email_log_tracking_status',
        'email_log_tracking_details',
        'email_log_error',
        'email_log_provider',
        'email_log_created_at'
    ];

    protected $appends = [];

    const COLUMNS = [
    ];

    const ENT_NAME = 'EmailLogs';
    const NAME_COL = '';
    const COL_RELATIONS = [
        'email_log_email_id' => 'emails'
    ];

    const CREATED_AT = false;
    const UPDATED_AT = false;
    protected $dateFormat = 'Y-m-d H:i:s';

    public $timestamps = false;

    /**
     * The supported cast types are: integer, real, float, double, decimal:<digits>, string, boolean,
     * object, array, collection, date, datetime, and timestamp.
     * When casting to decimal, you must define the number of digits (decimal:2).
     * @var array
     */
    protected $casts = [
        'email_log_email_id' => 'integer'
    ];

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted() {
        //
    }

    public function email() {
        return $this->belongsTo(Email::class, 'email_id');
    }

    /**
     * Count unique statuses
     *
     * @param string|null $status
     * @param string|null $fromDate
     * @param string|null $toDate
     * @return int
     */
    public static function countUniqueStatuses(string $status = null, string $fromDate = null, string $toDate = null): int
    {
        if (!$status || !$fromDate || !$toDate) {
            return 0;
        }

        $count = DB::select("select count(*) as count from (select count(email_log_tracking_status)
            from email_logs
            join emails e on e.email_id = email_log_email_id AND e.email_created_at BETWEEN ? AND ?
            where email_log_tracking_status = ?
            group by email_log_email_id) as count_rows", [$fromDate, $toDate, $status]);

        return $count[0]->count ?? 0;
    }

    /**
     * Create email log
     *
     * @param array $emailLog
     * @return array|bool|int
     */
    public static function createEmailLog(array $emailLog) {
        if (!is_array($emailLog) || !sizeof($emailLog)) {
            return false;
        }

        try {
            $created = EmailLogs::create($emailLog);

            return $created->email_log_id;
        }
        catch (QueryException $e) {
            return [
                'error' => $e->getMessage()
            ];
        }
    }
}
