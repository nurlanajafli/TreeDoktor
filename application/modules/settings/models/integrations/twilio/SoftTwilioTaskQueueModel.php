<?php

namespace application\modules\settings\models\integrations\twilio;

use application\core\Database\EloquentModel;
use function GuzzleHttp\Promise\all;

/**
 * Class SoftTwilioTaskQueueModel
 * @package application\modules\soft_twilio_calls\models
 */
class SoftTwilioTaskQueueModel extends SoftTwilioModelBase
{
    const ATTR_WORKSPACE_ID = 'workspace_id';

    /**
     * @var string
     */
    protected $table = 'soft_twilio_task_queues';

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
        'reservationActivitySid',
        'assignmentActivitySid',
        'maxReservedWorkers',
        'targetWorkers',
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