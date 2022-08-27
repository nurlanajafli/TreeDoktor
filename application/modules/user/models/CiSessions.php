<?php

namespace application\modules\user\models;

use application\core\Database\EloquentModel;
use DB;

class CiSessions extends EloquentModel
{

    /**
     * @var string
     */
    protected $table = 'ci_sessions';

    /**
     * @var array
     */
    protected $fillable = ['id', 'ip_address', 'timestamp', 'data'];

    const ATTR_ID = 'id';
    const ATTR_IP_ADDRESS = 'ip_address';
    const ATTR_TIMESTAMP = 'timestamp';
    const ATTR_DATA = 'data';

    /**
     * @param int $user_id
     * @return int
     */
    public static function deleteAllByUserId(int $user_id)
    {
        $searchParametr = 'user_id|s:' . strlen($user_id) . ':"' . $user_id;
        return CiSessions::where(static::ATTR_DATA, 'like', "%{$searchParametr}%")->delete();
    }

    /**
     * @param string $id
     * @return CiSessions|\Illuminate\Database\Eloquent\Model|object|null
     */
    public static function getById(string $id)
    {
        return CiSessions::where(static::ATTR_ID, '=', $id)->first();
    }

    /**
     * @param CiSessions $ciSession
     */
    public static function createFromObject(CiSessions $ciSession)
    {
        CiSessions::create([
            static::ATTR_ID => $ciSession->id,
            static::ATTR_IP_ADDRESS => $ciSession->ip_address,
            static::ATTR_TIMESTAMP => $ciSession->timestamp,
            static::ATTR_DATA => $ciSession->data
        ]);
    }

}
