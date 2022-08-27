<?php

namespace application\modules\employees\models;

use application\core\Database\EloquentModel;
use application\modules\user\models\User;
use application\modules\employees\models\EmployeeLogin;
use application\modules\payroll\models\Payroll;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

use application\modules\schedule\models\Expense;
use application\modules\schedule\models\ExpenseType;

use DB;
class EmployeeWorked extends EloquentModel
{
    protected $table = 'employee_worked';

    protected $primaryKey = 'worked_id';

    protected $fillable = [
        'worked_date',
        'worked_hours',
        'worked_lunch',
        'worked_hourly_rate',
        'worked_bonuses',
        'worked_employee_id',
        'worked_user_id',
        'worked_late',
        'worked_payroll_id',
        'worked_start',
        'worked_end',
        'worked_auto_logout',
    ];

    /**
     * The supported cast types are: integer, real, float, double, decimal:<digits>, string, boolean,
     * object, array, collection, date, datetime, and timestamp.
     * When casting to decimal, you must define the number of digits (decimal:2).
     * @var array
     */
//    protected $casts = [
//        'worked_hours'       => 'float',
//        'worked_lunch'       => 'float',
//        'worked_hourly_rate' => 'float',
//    ];

    public function scopeDateInterval($query, $from, $to){
        return $query->whereDate('worked_date', '>=', $from)->whereDate('worked_date', '<=', $to);
    }
    /**
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'worked_user_id');
    }
    /**
     * @return BelongsTo
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'worked_employee_id');
    }

    public function logins(){
        return $this->hasMany(EmployeeLogin::class, 'login_worked_id', 'worked_id');
    }

    public function payroll(){
        return $this->hasOne(Payroll::class, 'payroll_id', 'worked_payroll_id');
    }

    public function scopeBldExpense($query){
        return $query->addSelect(DB::raw('bld.expense_user_id, (bld.expense_amount+bld.expense_hst_amount) as bld_expense_value, bld.expense_date'))
            ->leftJoin('expenses as bld', function ($query){
            $query->on('worked_user_id', '=', 'bld.expense_user_id')
                ->on('worked_date', '=', DB::raw("FROM_UNIXTIME(bld.expense_date, '%Y-%m-%d')"))
                ->where('bld.expense_is_extra', '=', 0);
        });
    }

    public function scopeExtraExpense($query){
        return $query->addSelect(DB::raw('extra.expense_user_id, (extra.expense_amount+extra.expense_hst_amount) as extra_expense_value, extra.expense_date'))
            ->leftJoin('expenses as extra', function ($query){
            $query->on('worked_user_id', '=', 'extra.expense_user_id')
                ->on('worked_date', '=', DB::raw("FROM_UNIXTIME(extra.expense_date, '%Y-%m-%d')"))
                ->where('extra.expense_is_extra', '=', 1);
        });
    }

}
