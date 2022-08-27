<?php

namespace application\modules\employees\models;

use application\core\Database\EloquentModel;
use application\modules\user\models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * application\modules\employees\models\Employee
 *
 * @property int $employee_id
 * @property string|null $emp_name
 * @property string $emp_email
 * @property string $emp_username
 * @property string $emp_pass
 * @property string $emp_position
 * @property string|null $emp_address1
 * @property string|null $emp_address2
 * @property string|null $emp_city
 * @property string|null $emp_state
 * @property string|null $emp_phone
 * @property string|null $emp_sin
 * @property float|null $emp_hourly_rate
 * @property float|null $emp_yearly_rate
 * @property mixed|null $emp_message_on_account
 * @property string|null $added_on
 * @property string|null $updated_on
 * @property string|null $emp_feild_worker
 * @property string|null $emp_driver
 * @property string|null $emp_climber
 * @property bool $emp_ground
 * @property bool $emp_technique
 * @property string $emp_status
 * @property string $emp_start_time
 * @property string|null $emp_date_hire
 * @property string|null $emp_sex
 * @property string|null $emp_birthday
 * @property string $emp_pay_frequency
 * @property string $emp_field_estimator
 * @property bool $deductions_state
 * @property string|null $deductions_desc
 * @property float|null $deductions_amount
 * @property int|null $emp_user_id
 * @property bool|null $emp_no_dayoff
 * @property string $emp_type
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\employees\models\Employee newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\employees\models\Employee newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\employees\models\Employee query()
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\employees\models\Employee whereAddedOn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\employees\models\Employee whereDeductionsAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\employees\models\Employee whereDeductionsDesc($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\employees\models\Employee whereDeductionsState($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\employees\models\Employee whereEmpAddress1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\employees\models\Employee whereEmpAddress2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\employees\models\Employee whereEmpBirthday($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\employees\models\Employee whereEmpCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\employees\models\Employee whereEmpClimber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\employees\models\Employee whereEmpDateHire($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\employees\models\Employee whereEmpDriver($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\employees\models\Employee whereEmpEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\employees\models\Employee whereEmpFeildWorker($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\employees\models\Employee whereEmpFieldEstimator($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\employees\models\Employee whereEmpGround($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\employees\models\Employee whereEmpHourlyRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\employees\models\Employee whereEmpMessageOnAccount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\employees\models\Employee whereEmpName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\employees\models\Employee whereEmpNoDayoff($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\employees\models\Employee whereEmpPass($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\employees\models\Employee whereEmpPayFrequency($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\employees\models\Employee whereEmpPhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\employees\models\Employee whereEmpPosition($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\employees\models\Employee whereEmpSex($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\employees\models\Employee whereEmpSin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\employees\models\Employee whereEmpStartTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\employees\models\Employee whereEmpState($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\employees\models\Employee whereEmpStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\employees\models\Employee whereEmpTechnique($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\employees\models\Employee whereEmpType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\employees\models\Employee whereEmpUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\employees\models\Employee whereEmpUsername($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\employees\models\Employee whereEmpYearlyRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\employees\models\Employee whereEmployeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\employees\models\Employee whereUpdatedOn($value)
 * @mixin \Eloquent
 * @property-read \application\modules\user\models\User|null $user
 */
class Employee extends EloquentModel
{
    protected $table = 'employees';

    protected $primaryKey = 'employee_id';

    protected $fillable = [
        'employee_id',
        'emp_name',
        'emp_email',
        'emp_username',
        'emp_pass',
        'emp_position',
        'emp_address1',
        'emp_address2',
        'emp_city',
        'emp_state',
        'emp_phone',
        'emp_sin',
        'emp_hourly_rate',
        'emp_yearly_rate',
        'emp_message_on_account',
        'added_on',
        'updated_on',
        'emp_feild_worker',
        'emp_driver',
        'emp_climber',
        'emp_ground',
        'emp_technique',
        'emp_status',
        'emp_start_time',
        'emp_date_hire',
        'emp_sex',
        'emp_birthday',
        'emp_pay_frequency',
        'emp_field_estimator',
        'deductions_state',
        'deductions_desc',
        'deductions_amount',
        'emp_user_id',
        'emp_no_dayoff',
        'emp_type'
    ];

    const CREATED_AT = null;

    /**
     * The supported cast types are: integer, real, float, double, decimal:<digits>, string, boolean,
     * object, array, collection, date, datetime, and timestamp.
     * When casting to decimal, you must define the number of digits (decimal:2).
     * @var array
     */
    protected $casts = [
        'emp_hourly_rate' => 'float',
        'emp_yearly_rate' => 'float',
    ];

    /**
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id', 'emp_user_id');
    }

    public function scopeEstimator($query){
        return $query->where('emp_field_estimator', '=', "1");
    }

    public function scopeFieldWorker($query){
        return $query->where('emp_status', '=' ,'current')->where('emp_feild_worker', '=', 1);
    }
}
