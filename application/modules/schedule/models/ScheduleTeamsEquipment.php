<?php


namespace application\modules\schedule\models;

use application\core\Database\EloquentModel;
use application\modules\schedule\models\ScheduleTeams;
use application\modules\equipment\models\Equipment;
use application\modules\user\models\User;

class ScheduleTeamsEquipment extends EloquentModel
{
    /**
     * @var string
     */
    protected $table = 'schedule_teams_equipment';

    protected $casts = [
        'weight'=>'int'
    ];
    /**
     * @var string
     */
    protected $primaryKey = null;



    public function team() {
        return $this->hasOne(ScheduleTeams::class, 'team_id', 'equipment_team_id');
    }

    public function equipment() {
        return $this->hasOne(Equipment::class, 'eq_id', 'equipment_id');
    }

    public function driver(){
        return $this->hasOne(User::class, 'id', 'equipment_driver_id');
    }

    public function scopeDatesInterval($query, $from, $to){
        return $query->whereHas('team', function ($subQuery) use ($from, $to){
            return $subQuery->datesInterval($from, $to);
        });
    }
}