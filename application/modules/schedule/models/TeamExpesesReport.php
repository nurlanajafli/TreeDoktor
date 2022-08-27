<?php


namespace application\modules\schedule\models;


use application\core\Database\EloquentModel;
use application\modules\schedule\models\Expense;
use DB;

class TeamExpesesReport extends EloquentModel
{
    /**
     * @var string
     */
    protected $table = 'team_expeses_report';

    /**
     * @var string
     */
    protected $primaryKey = 'ter_id';

    public function scopeOriginalFields($query){
        return $query->select(['team_expeses_report.*']);
    }

    public function scopeFields($query){
        return $query->select(['ter_user_id as user_id', 'ter_bld as lunch', 'ter_extra as extra', 'ter_extra_comment as note', 'ter_id as expense_id']);
    }

    public function scopeLunchApproved($query){
        return $query->addSelect(['le.expense_id as lunch_approved', DB::raw("FROM_UNIXTIME(le.expense_date, '%Y-%m-%d') as lunch_date")])->leftJoin('expenses as le', function ($query){
            $query->on('ter_user_id', '=', 'le.expense_user_id')
                ->on('ter_team_id', '=', 'le.expense_team_id')
                ->on('ter_date', '=', DB::raw("FROM_UNIXTIME(le.expense_date, '%Y-%m-%d')"))
                ->where('le.expense_is_extra', '=', 0);
        });
    }

    public function scopeExtraApproved($query){
        return $query->addSelect(['ee.expense_id as extra_approved', DB::raw("FROM_UNIXTIME(ee.expense_date, '%Y-%m-%d') as extra_expense_date")])->leftJoin('expenses as ee', function ($query){
            $query->on('ter_user_id', '=', 'ee.expense_user_id')
                ->on('ter_team_id', '=', 'ee.expense_team_id')
                ->on('ter_date', '=', DB::raw("FROM_UNIXTIME(ee.expense_date, '%Y-%m-%d')"))
                ->where('ee.expense_is_extra', '=', 1);
        });
    }

    public function scopeWithApproved($query)
    {
        return $query->with(['lunch_approved'/*, 'extra_approved'*/]);
    }

    public function scopeWithDate($query, $date)
    {
        return $query->whereDate('ter_date', '=', $date);
    }

    public function scopeWithTeam($query, $team_id)
    {
        return $query->where('ter_team_id', '=', $team_id);
    }

}