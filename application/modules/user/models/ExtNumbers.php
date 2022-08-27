<?php

namespace application\modules\user\models;

use application\core\Database\EloquentModel;
use DB;

class ExtNumbers extends EloquentModel
{

    const ATTR_ID = 'extention_id';
    const ATTR_KEY = 'extention_key';
    const ATTR_NUMBER = 'extention_number';
    const ATTR_ORDER = 'extention_order';
    const ATTR_EMERGENCY = 'extention_emergency';
    const ATTR_USER_ID = 'extention_user_id';

    /**
     * @var string
     */
    protected $table = 'ext_numbers';

    /**
     * @var string
     */
    protected $primaryKey = 'extention_id';

}
