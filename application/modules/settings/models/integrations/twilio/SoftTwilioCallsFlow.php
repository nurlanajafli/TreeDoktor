<?php

namespace application\modules\settings\models\integrations\twilio;

use application\core\Database\EloquentModel;

/**
 * Class SoftTwilioCallsFlow
 * @package application\modules\soft_twilio_calls\models
 */
class SoftTwilioCallsFlow extends EloquentModel
{
    /**
     * @var string
     */
    protected $table = 'soft_twilio_calls_flow';

    /**
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * @var array
     */
    protected $fillable = [
        'name',
        'data',
        'sms-data'
    ];


    public function app()
    {
        return $this->hasOne(SoftTwilioApplicationModel::class,  'flow_id', 'id');
    }

}