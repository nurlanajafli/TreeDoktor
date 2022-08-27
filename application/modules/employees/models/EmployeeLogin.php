<?php

namespace application\modules\employees\models;

use application\core\Database\EloquentModel;
use application\modules\user\models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeLogin extends EloquentModel
{
    protected $table = 'emp_login';

    protected $primaryKey = 'login_id';

    protected $fillable = [
        'login',
        'logout',
        'login_worked_id',
        'login_employee_id',
        'login_user_id',
        'login_lat',
        'login_lon',
        'logout_lat',
        'logout_lon',
        'login_date',
        'login_image',
        'logout_image',
        'login_office',
    ];

    /**
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'login_user_id');
    }

    /**
     * @return BelongsTo
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'login_employee_id');
    }

    /**
     * @return BelongsTo
     */
    public function worked(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'login_worked_id');
    }


}
