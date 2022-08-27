<?php


namespace application\modules\schedule\models;


use application\core\Database\EloquentModel;

class ScheduleTeamsBonuse extends EloquentModel
{
    /**
     * @var string
     */
    protected $table = 'schedule_teams_bonuses';

    /**
     * @var string
     */
    protected $primaryKey = 'bonus_id';

}