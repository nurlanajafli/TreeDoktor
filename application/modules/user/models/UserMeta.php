<?php

namespace application\modules\user\models;

use application\core\Database\EloquentModel;

/**
 * application\modules\user\models\UserMeta
 *
 * @property int $id
 * @property int $user_id
 * @property string|null $address1
 * @property string|null $address2
 * @property string|null $city
 * @property string|null $state
 * @property string|null $country
 * @property-read \application\modules\user\models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\user\models\UserMeta newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\user\models\UserMeta newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\user\models\UserMeta query()
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\user\models\UserMeta whereAddress1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\user\models\UserMeta whereAddress2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\user\models\UserMeta whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\user\models\UserMeta whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\user\models\UserMeta whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\user\models\UserMeta whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\user\models\UserMeta whereUserId($value)
 * @mixin \Eloquent
 */
class UserMeta extends EloquentModel
{
    protected $table = 'user_meta';
    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id',
        'address1',
        'address2',
        'city',
        'state',
        'country',
    ];


    /**
     * The supported cast types are: integer, real, float, double, decimal:<digits>, string, boolean,
     * object, array, collection, date, datetime, and timestamp.
     * When casting to decimal, you must define the number of digits (decimal:2).
     * @var array
     */
    protected $casts = [
        'user_id' => 'integer',
    ];

    /**
     * Get the user that owns the meta.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}