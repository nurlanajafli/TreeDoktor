<?php

namespace application\modules\messaging\models;

use application\core\Database\Casts\AppDateTime;
use application\core\Database\EloquentModel;
use application\modules\clients\models\Client;
use application\modules\user\models\User;
use Illuminate\Database\Eloquent\Collection;

class Messages extends EloquentModel
{

    protected $table = 'sms_messages';
    protected $primaryKey = 'sms_id';

    /**
     *     sms_id        int auto_increment            primary key,
     *     sms_sid       varchar(255)                         null,
     *     sms_number    varchar(255)                         null,
     *     sms_body      text                                 null,
     *     sms_date      datetime   default CURRENT_TIMESTAMP null,
     *     sms_support   tinyint(1) default 0                 null,
     *     sms_readed    tinyint(1) default 0                 null,
     *     sms_client_id int                                  null,
     *     sms_user_id   int                                  null,
     *     sms_incoming  tinyint    default 0                 null,
     *     sms_status    varchar(255)                         null,
     *     sms_error     varchar(255)                         null,
     *     sms_auto      tinyint(1) default 0                 null
     *     sms_segment   smallint(6) default 1                null
     *     sms_provider  varchar(255)                         null
     *     sms_debug     text                                 null
     */

    protected $fillable = [
        'sms_sid',
        'sms_number',
        'sms_body',
        'sms_date',
        'sms_support',
        'sms_readed',
        'sms_client_id',
        'sms_user_id',
        'sms_incoming',
        'sms_status',
        'sms_error',
        'sms_auto',
        'sms_segment',
        'sms_provider',
        'sms_debug'
    ];

    protected $appends = [
        'sms_date_view', 'sms_date_time_view', 'sms_number_view'
    ];

    const COLUMNS = [
    ];

    const ENT_NAME = 'Messages';
    const NAME_COL = '';
    const COL_RELATIONS = [
        'sms_client_id' => 'client',
        'sms_user_id' => 'user',
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
        //'sms_date' => 'datetime',
        'sms_support' => 'boolean',
        'sms_readed' => 'boolean',
        'sms_client_id' => 'integer',
        'sms_user_id' => 'integer',
        'sms_incoming' => 'boolean',
        'sms_auto' => 'boolean',
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

    public function user()
    {
        return $this->belongsTo(User::class, 'sms_user_id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'sms_client_id');
    }

    function getSmsDateViewAttribute(){
        if(!isset($this->attributes['sms_date']))
            return '';

        return getDateTimeWithDate($this->attributes['sms_date'],'Y-m-d H:i:s', false);
    }

    function getSmsDateTimeViewAttribute(){
        if(!isset($this->attributes['sms_date']))
            return '';

        return getDateTimeWithDate($this->attributes['sms_date'],'Y-m-d H:i:s', true);
    }

    function getSmsNumberViewAttribute(){
        if(!isset($this->attributes['sms_number']))
            return '';

        return numberTo($this->attributes['sms_number']);
    }

    public function scopeFromSmsId($query, $smsId = null) {
        if ($smsId) {
            return $query->where('sms_id', '>', $smsId);
        }

        return $query;
    }

    /**
     * Get sms by numbers for client notes
     *
     * @param array $numbers
     * @return Messages[]|Collection|\Illuminate\Database\Query\Builder[]|\Illuminate\Support\Collection
     */
    public static function getClientNotesSms(array $numbers) {
        return Messages::whereIn('sms_number', $numbers)
            ->limit(config_item('per_page_notes'))
            ->orderBy('sms_date', 'desc')
            ->get()
            ->groupBy('sms_number_view')
            ->map(function (Collection $number) {
                return $number->groupBy('sms_date_view');
            });
    }

    /**
     * Count delivered messages
     *
     * @return int
     */
    public static function countSuccessMessages() {
        $smsLimitPeriod = config_item('sms_limit_period') ?? 'month';

        $start = today()->startOfMonth();
        $end = today()->endOfMonth();

        if ($smsLimitPeriod === 'year') {
            $start = today()->startOfYear();
            $end = today()->endOfYear();
        }

        return self::whereBetween('sms_date', [$start, $end])
            ->where('sms_status', '!=', 'error')
            ->where('sms_status', '!=', 'failed')
            ->sum('sms_segment');
    }

    /**
     * Set unreaded last incoming SMS message
     *
     * @param string|null $number
     * @return bool|int
     */
    public static function setUnread(string $number = null) {
        if (!$number) {
            return false;
        }

        return Messages::where('sms_number', $number)
            ->where('sms_incoming', 1)
            ->orderByDesc('sms_date')
            ->first()
            ->update(['sms_readed' => 0]);
    }
}
