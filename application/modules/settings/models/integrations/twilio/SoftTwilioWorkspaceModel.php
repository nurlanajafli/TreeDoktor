<?php

namespace application\modules\settings\models\integrations\twilio;


/**
 * Class SoftTwilioWorkspaceModel
 * @package application\modules\soft_twilio_calls\models
 */
class SoftTwilioWorkspaceModel extends SoftTwilioModelBase
{
    /**
     * @var string
     */
    protected $table = 'soft_twilio_workspaces';

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
        'defaultActivityName',
        'timeoutActivityName',
        'eventCallbackUrl',
    ];
}
