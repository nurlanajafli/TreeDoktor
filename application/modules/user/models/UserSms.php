<?php

namespace application\modules\user\models;

use application\core\Auth\User as AuthUser;
use DB;

class UserSms extends AuthUser
{
    protected $table = 'users_sms';
    protected $primaryKey = 'us_id';

    /**
     * The attributes that are mass assignable.
     *
     * us_id                int auto_increment
     * primary key,
     * us_user_id           int                                null,
     * us_recipient_user_id int                                null,
     * us_recipient         varchar(255)                       null,
     * us_body              text                               null,
     * us_date              datetime default CURRENT_TIMESTAMP null
     *
     * @var array
     */
    protected $fillable = [
        'us_user_id',
        'us_recipient_user_id',
        'us_recipient',
        'us_body',
        'us_date',
    ];

    /**
     * The supported cast types are: integer, real, float, double, decimal:<digits>, string, boolean,
     * object, array, collection, date, datetime, and timestamp.
     * When casting to decimal, you must define the number of digits (decimal:2).
     * @var array
     */
    protected $casts = [
        'us_user_id' => 'int',
        'us_recipient_user_id' => 'int',
    ];

    public static function checkUserSms($number)
    {
        return self::select([
            'users_sms.*',
            'users.*',
            'employees.*',
            DB::raw('recipient.firstname as recipient_firstname'),
            DB::raw('recipient.lastname as recipient_lastname'),
        ])
            ->where('us_reciepient', '=', $number)
            ->where(DB::raw('DATE_ADD(us_date, INTERVAL 1 DAY)'), '>=', date('Y-m-d H:i:s'))
            ->leftJoin('users', 'users.id', '=', 'users_sms.us_user_id')
            ->leftJoin(DB::raw('users recipient'), 'users.id', '=', 'users_sms.us_recipient_user_id')
            ->leftJoin('employees', 'users.id', '=', 'employees.emp_user_id')
            ->first();
    }
}
