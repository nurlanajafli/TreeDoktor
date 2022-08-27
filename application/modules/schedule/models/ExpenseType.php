<?php


namespace application\modules\schedule\models;
use application\core\Database\EloquentModel;
use DB;

class ExpenseType extends EloquentModel
{
    /**
     * @var string
     */
    protected $table = 'expense_types';

    /**
     * @var string
     */
    protected $primaryKey = 'expense_type_id';

    const EMPLOYEE_BENEFITS = 'employee_benefits';


    public function scopeWhereSlug($query, $slug){
        $query->where('expense_status', '=', 1);
        if($slug)
            $query->where('flag', '=', $slug);

        return $query;
    }
}