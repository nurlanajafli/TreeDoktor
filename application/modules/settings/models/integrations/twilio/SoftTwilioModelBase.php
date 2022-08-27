<?php

namespace application\modules\settings\models\integrations\twilio;

use application\core\Database\EloquentModel;

/**
 * Class SoftTwilioModelBase
 * @package application\modules\soft_twilio_calls\models
 */
class SoftTwilioModelBase extends EloquentModel
{
    const ATTR_ID = 'id';
    const ATTR_SID = 'sid';

    /**
     * @param $sid
     * @return SoftTwilioWorkspaceModel|\Illuminate\Database\Eloquent\Model|object|null
     */
    public static function findBySid($sid)
    {
        return static::where(static::ATTR_SID, '=', $sid)->first();
    }

    /**
     * @return array
     */
    public static function getList()
    {
        return static::all()->toArray();
    }

    /**
     * @return int
     */
    public static function getTotalCount()
    {
        return static::all()->count();
    }

    /**
     * @param string $sid
     * @param array $data
     * @return bool
     */
    public static function updateBySid(string $sid, array $data)
    {
        $result = false;
        $model = static::findBySid($sid);
        if (!is_null($model)) {
            $result = $model->setRawAttributes($data)->save();
        }
        return $result;
    }

    /**
     * @param string $sid
     * @return int
     * @throws \Exception
     */
    public static function deleteBySid(string $sid)
    {
        $result = false;
        $model = static::findBySid($sid);
        if (!is_null($model)) {
            $result = $model->delete();
        }
        return $result;
    }
}