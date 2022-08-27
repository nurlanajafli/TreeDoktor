<?php

namespace application\modules\clients\models;

use application\core\Database\EloquentModel;
use application\modules\dashboard\models\traits\FullTextSearch;
use DB;

class ClientPaper extends EloquentModel
{
    protected $primaryKey = 'cp_id';

    protected $fillable = [
        'cp_client_id',
        'cp_user_id',
        'cp_text',
        'cp_date'
    ];
}
