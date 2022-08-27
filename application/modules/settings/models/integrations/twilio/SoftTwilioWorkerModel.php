<?php

namespace application\modules\settings\models\integrations\twilio;

use application\modules\user\models\User;

/**
 * Class SoftTwilioWorkerModel
 * @package application\modules\soft_twilio_calls\models
 */
class SoftTwilioWorkerModel extends SoftTwilioModelBase
{

    /**
     * @var string
     */
    protected $table = 'soft_twilio_workers';

    /**
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * @var array
     */
    protected $fillable = [
        'workspace_id',
        'user_id',
        'sid',
        'friendlyName',
        'activityName',
        'available'
    ];

    const ATTR_USER_ID = 'user_id';
    const ATTR_WORKSPACE_ID = 'workspace_id';

    /**
     * Get the worker record associated with the user.
     */
    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    /**
     * @param $workspace_id
     * @return int
     */
    public static function deleteByWorkspaceId($workspace_id) {
        return static::where('workspace_id', '=', $workspace_id)->delete();
    }

    /**
     * @param int $workspaceId
     * @return \Illuminate\Support\Collection
     */
    public static function getListByWorkspaceId(int $workspaceId)
    {
        return static::where(static::ATTR_WORKSPACE_ID, '=', $workspaceId)->get();
    }

    /**
     * @param $id
     * @return SoftTwilioWorkerModel|\Illuminate\Database\Eloquent\Model|object|null
     */
    public static function getById($id)
    {
        return static::with([
            'user' => function($query) {
                $query->with('employee');
            }
        ])->where('soft_twilio_workers.id', '=', $id)->first();
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public static function allWithUsers()
    {
        return static::with([
            'user' => function($query) {
                $query->with('employee');
            }
        ])->get();
    }
}