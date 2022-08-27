<?php

namespace application\modules\settings\models\integrations\twilio;

use application\core\Database\EloquentModel;

/**
 * Class SoftTwilioApplicationModel
 * @package application\modules\soft_twilio_calls\models
 */
class SoftTwilioApplicationModel extends SoftTwilioModelBase
{
    /**
     * @var string
     */
    protected $table = 'soft_twilio_applications';

    /**
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * @var array
     */
    protected $fillable = [
        'sid',
        'friendlyName',
        'voiceUrl',
        'statusCallback',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function flow()
    {
        return $this->hasOne(SoftTwilioCallsFlow::class, 'id', 'flow_id');
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public static function allNotAssignedToFlow()
    {
        return static::select('soft_twilio_applications.*')->join(
            SoftTwilioCallsFlow::tableName(),
            'soft_twilio_applications.flow_id',
            '<>',
            'soft_twilio_calls_flow.id'
        )->groupBy('soft_twilio_applications.id')->get();
    }
}