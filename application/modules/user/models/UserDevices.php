<?php

namespace application\modules\user\models;

use application\core\Database\EloquentModel;
use application\core\Models\Traits\HasCompositePrimaryKey;

class UserDevices extends EloquentModel
{
    use HasCompositePrimaryKey;

    protected $table = 'user_devices';
    protected $primaryKey = ['device_id', 'device_user_id'];
    public $incrementing = false;

    const ATTR_DEVICE_ID = 'device_id';
    const ATTR_DEVICE_USER_ID = 'device_user_id';
    const ATTR_DEVICE_TOKEN = 'device_token';
    const ATTR_DEVICE_TOKEN_EXPIRATION = 'device_token_expiration';
    const ATTR_FIREBASE_TOKEN = 'firebase_token';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'device_id',
        'device_user_id',
        'device_token',
        'device_token_expiration',
        'firebase_token'
    ];

    /**
     * Get user device by
     *
     * @param array $data
     * @return UserDevices|false|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object|null
     */
    public static function getDeviceBy(array $data) {
        if (empty($data)) {
            return false;
        }

        return UserDevices::where($data)->first();
    }

    /**
     * Create or update userDevice record
     *
     * @param array $data = [
     *    'device_id' => (string),
     *    'device_user_id' => (int),
     *    'device_token' => (string),
     *    'device_token_expiration' => (string),
     *    'firebase_token' => (string)
     *  ]
     * @return UserDevices|\Illuminate\Database\Eloquent\Model|bool
     */
    public static function deviceRegistration(array $data) {
        if (empty($data)) {
            return false;
        }

        return UserDevices::updateOrCreate(
            ['device_id' => $data['device_id']],
            $data
        );
    }

    /**
     * Delete userDevice record by device token
     *
     * @param string $deviceToken
     * @return bool|mixed|null
     * @throws \Exception
     */
    public static function deviceUnregistration(string $deviceToken) {
        return UserDevices::where('device_token', $deviceToken)->delete();
    }

    /**
     * @param int $user_id
     * @return int
     */
    public static function deleteAllByUserId(int $user_id)
    {
        return UserDevices::where([static::ATTR_DEVICE_USER_ID => $user_id])->delete();
    }
}
