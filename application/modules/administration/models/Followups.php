<?php

namespace application\modules\administration\models;

use application\core\Database\EloquentModel;

class Followups extends EloquentModel
{
    /**
     * @var string
     */
    protected $primaryKey = 'fu_id';

    /**
     * Table  name
     * @var string
     */
    protected $table = 'followups';

    /**
     * @var array
     */
    protected $fillable = [
        'fu_fs_id',
        'fu_date',
        'fu_time',
        'fu_module_name',
        'fu_action_name',
        'fu_client_id',
        'fu_item_id',
        'fu_estimator_id',
        'fu_status',
        'fu_comment',
        'fu_author',
        'fu_variables',
    ];
}
