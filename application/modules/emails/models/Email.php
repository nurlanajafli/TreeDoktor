<?php

namespace application\modules\emails\models;

use application\core\Database\EloquentModel;
use application\modules\clients\models\Client;
use application\modules\clients\models\ClientNote;
use application\modules\estimates\models\Estimate;
use application\modules\invoices\models\Invoice;
use application\modules\user\models\User;
use application\modules\clients\models\ClientLetter;
use application\modules\webhook\models\EmailLogs;
use application\modules\workorders\models\Workorder;
use Illuminate\Database\QueryException;

class Email extends EloquentModel
{
    protected $table = 'emails';
    protected $primaryKey = 'email_id';

    /**
     *     email_id             bigint auto_increment            primary key
     *     email_message_id     varchar(255)                         null
     *     email_from           varchar(255)
     *     email_to             varchar(255)
     *     email_subject        varchar(255)                         null
     *     email_status         enum(accepted, delivered, rejected, bounce, complained, unsubscribed, error, opened, clicked)
     *     email_user_id        bigint                               null
     *     email_template_id    int                                  null
     *     email_provider       varchar(255)
     *     email_error          text                                 null
     *     email_created_at     datetime
     *     email_updated_at     datetime
     */

    protected $fillable = [
        'email_message_id',
        'email_from',
        'email_to',
        'email_subject',
        'email_status',
        'email_user_id',
        'email_template_id',
        'email_provider',
        'email_error',
        'email_created_at',
        'email_updated_at'
    ];

    protected $appends = [];

    const COLUMNS = [
    ];

    const ENT_NAME = 'Emails';
    const NAME_COL = '';
    const COL_RELATIONS = [
        'email_user_id' => 'user',
        'email_template_id' => 'email_templates',
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
        'email_user_id' => 'integer',
        'email_template_id' => 'integer'
    ];

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted() {
        //
    }

    public function user() {
        return $this->belongsTo(User::class, 'email_user_id');
    }

    public function emailTemplate() {
        return $this->belongsTo(ClientLetter::class, 'email_template_id');
    }

    public function emailLogs() {
        return $this->hasMany(EmailLogs::class, 'email_log_email_id', 'email_id')
            ->select([
                'email_log_email_id',
                'email_log_id',
                'email_log_tracking_status',
                'email_log_tracking_details',
                'email_log_error',
                'email_log_provider',
                'email_log_created_at'
            ]);
    }

    public function client() {
        return $this->morphedByMany(Client::class, 'emailable');
    }

    public function clientNote() {
        return $this->morphedByMany(ClientNote::class, 'emailable');
    }

    public function estimate() {
        return $this->morphedByMany(Estimate::class, 'emailable');
    }

    public function invoice() {
        return $this->morphedByMany(Invoice::class, 'emailable');
    }

    public function workorder() {
        return $this->morphedByMany(Workorder::class, 'emailable');
    }

    public function emailLogsClicked() {
        return $this->emailLogs()->where('email_log_tracking_status', '=', 'clicked');
    }

    /**
     * Create email
     *
     * @param array $email
     * @return Email|array|bool
     */
    public static function createEmail(array $email) {
        if (!is_array($email) || !sizeof($email)) {
            return false;
        }

        try {
            return Email::create($email);
        }
        catch (QueryException $e) {
            return [
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get email ID by messageId
     *
     * @param string $messageId
     * @return int|null
     */
    public static function getIdByMessageId(string $messageId): ?int
    {
        $emailId = Email::select('email_id')->where('email_message_id', $messageId)->first();

        return $emailId ? $emailId['email_id'] : null;
    }

    /**
     * Get email by messageId
     *
     * @param string $messageId
     * @return Email|null
     */
    public static function getEmailByMessageId(string $messageId): ?Email
    {
        return Email::where('email_message_id', $messageId)->first();
    }

    /**
     * Update email status
     *
     * @param Email $email
     * @param string $newStatus
     * @return array|bool
     */
    public static function updateEmailStatus(Email $email, string $newStatus) {
        if (empty($email) || empty($newStatus)) {
            return false;
        }

        try {
            if ($email->email_status === $newStatus || ($email->email_status === 'clicked' && $newStatus === 'opened')) {
                return false;
            }

            $email->email_status = $newStatus;
            $email->email_updated_at = getNowDateTime();

            return $email->save();
        }
        catch(QueryException $e) {
            return [
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get email statistics
     *
     * @param string|null $fromDate
     * @param string|null $toDate
     * @param string|null $status
     * @param int|null $limit
     * @param int|null $offset
     * @param bool|null $countLog
     * @param bool|null $countActual
     * @return array|bool|int
     */
    public static function getEmailsStatistics(
        string $fromDate = null,
        string $toDate = null,
        string $status = null,
        int $limit = null,
        int $offset = null,
        bool $countLog = null,
        bool $countActual = null
    ) {
        if (!$fromDate && !$toDate) {
            return false;
        }

        $emails = Email::select(['email_id', 'email_to', 'email_subject', 'email_status', 'email_error', 'email_created_at', 'email_updated_at'])
            ->whereBetween('email_created_at', [$fromDate, $toDate]);

        if ($status) {
            $emails->where('email_status', '=', $status);
        }

        if ($countLog || $countActual) {
            if (!$status || $status === 'error' || $countActual) {
                return $emails->count();
            }

            return EmailLogs::countUniqueStatuses($status, $fromDate, $toDate);
        } else {
            $emails->with(['emailLogs' => function ($query) {
                $query->orderBy('email_log_created_at', 'desc');
            }]);
        }

        $emails->orderBy('email_created_at', 'desc');

        if ($offset) {
            $emails->offset($offset);
        }

        if ($limit) {
            $emails->limit($limit);
        }

        return $emails->get()->toArray();
    }
}
