<?php
namespace application\modules\schedule\models;
use application\core\Database\EloquentModel;

class ScheduleUpdate extends EloquentModel
{
    /**
     * @var string
     */
    protected $table = 'schedule_updates';

    /**
     * @var string
     */
    protected $primaryKey = 'update_id';

    protected $fillable = [
        'update_time'
    ];

}