<?php

namespace application\modules\settings\models\integrations\twilio;

use application\core\Database\EloquentModel;

/**
 * Class SoftTwilioActivityModel
 * @package application\modules\soft_twilio_calls\models
 */
class SoftTwilioActivityModel extends SoftTwilioModelBase
{
    /**
     * @var string
     */
    protected $table = 'soft_twilio_activities';

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
        'available',
    ];

    const ATTR_WORKSPACE_ID = 'workspace_id';

    /**
     * @param int $workspaceId
     * @return \Illuminate\Support\Collection
     */
    public static function getListByWorkspaceId(int $workspaceId)
    {
        return static::where(static::ATTR_WORKSPACE_ID, '=', $workspaceId)->get();
    }

    /**
     * @param int $workspaceId
     * @param int $available
     * @return \Illuminate\Support\Collection
     */
    public static function getActivitiesByAvailableByWorkspace(int $workspaceId, int $available)
    {
        return static::where('available', '=', $available)
            ->where('workspace_id', '=', $workspaceId)->get();
    }

    /**
     * @param $workspace_id
     * @return int
     */
    public static function deleteByWorkspaceId($workspace_id) {
        return static::where('workspace_id', '=', $workspace_id)->delete();
    }
}