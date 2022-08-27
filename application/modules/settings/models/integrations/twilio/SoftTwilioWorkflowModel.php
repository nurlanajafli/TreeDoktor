<?php

namespace application\modules\settings\models\integrations\twilio;


/**
 * Class SoftTwilioWorkflowModel
 * @package application\modules\soft_twilio_calls\models
 */
class SoftTwilioWorkflowModel extends SoftTwilioModelBase
{
    const ATTR_WORKSPACE_ID = 'workspace_id';

    /**
     * @var string
     */
    protected $table = 'soft_twilio_workflows';

    /**
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * @var array
     */
    protected $fillable = [
        'workspace_id',
        'sid',
        'friendlyName',
        'taskReservationTimeout',
        'assignmentCallbackUrl',
        'configuration',
        'defaultFilter'
    ];

    /**
     * @param int $workspaceId
     * @return \Illuminate\Support\Collection
     */
    public static function getListByWorkspaceId(int $workspaceId)
    {
        return static::where(static::ATTR_WORKSPACE_ID, '=', $workspaceId)->get();
    }

    /**
     * @param $workspace_id
     * @return int
     */
    public static function deleteByWorkspaceId($workspace_id) {
        return static::where('workspace_id', '=', $workspace_id)->delete();
    }
}