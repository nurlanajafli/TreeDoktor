<?php


namespace application\modules\schedule\models;


use application\core\Database\EloquentModel;

class ScheduleTeamsTool extends EloquentModel
{
    /**
     * @var string
     */
    protected $table = 'schedule_teams_tools';

    /**
     * @var string
     */
    protected $primaryKey = 'stt_id';



}