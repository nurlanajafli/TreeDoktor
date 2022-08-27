<?php


namespace application\modules\tasks\models;
use application\core\Database\EloquentModel;

class TaskCategory extends EloquentModel
{
    protected $table = 'client_task_categories';
    protected $primaryKey = 'category_id';

}