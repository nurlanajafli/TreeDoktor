<?php

namespace application\modules\references\models;

use application\core\Database\EloquentModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use DB;

class Reference extends EloquentModel
{
    use SoftDeletes;

    const ATTR_ID = 'id';
    const ATTR_SLUG = 'slug';
    const ATTR_NAME = 'name';
    const ATTR_WEIGHT = 'weight';
    const ATTR_IS_CLIENT_ACTIVE = 'is_client_active';
    const ATTR_IS_USER_ACTIVE = 'is_user_active';
    const ATTR_DELETED_AT = 'deleted_at';
    const ATTR_ALWAYS_HIDDEN = 'always_hidden';

    const AUTO_REFERENCE_QUICKBOOKS = 'Quickbooks';

    /**
     * @var array
     */
    protected $fillable = ['slug', 'name', 'is_client_active', 'is_user_active'];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];


    const HIDE_REFERENCE_ARRAY = [
        'client' => 'Client',
        'user' => 'Employee',
    ];

    /**
     * Primary key name
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Table  name
     * @var string
     */
    protected $table = 'reference';

    /**
     * API application fields for get
     * @var array
     */
    const API_GET_FIELDS = [
        'reference.name as reference_name'
    ];

    /**
     * @param array $select
     * @param string $keyBy
     * @return \Illuminate\Support\Collection
     */
    public static function getAllActive(array $select = ['*'], string $keyBy = 'id')
    {
        $result = Reference::select($select);
        foreach (static::HIDE_REFERENCE_ARRAY as $key => $value) {

            $result->orWhereNull(static::ATTR_DELETED_AT);
            $result->where('is_' . $key . '_active', '=', 1);
            $result->where(static::ATTR_SLUG, '=', $key);

        }
        $result->orWhereNull(static::ATTR_DELETED_AT);
        $result->where(static::ATTR_IS_USER_ACTIVE, '=', 0);
        $result->where(static::ATTR_IS_CLIENT_ACTIVE, '=', 0);
        $result->whereNotIn(static::ATTR_SLUG, array_keys(static::HIDE_REFERENCE_ARRAY));
        $result->where(static::ATTR_ALWAYS_HIDDEN, '=', 0);
        $result->orderBy(self::ATTR_WEIGHT);

        return $result->get()->keyBy($keyBy);
    }

    /**
     * @return Reference[]|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Query\Builder[]|\Illuminate\Support\Collection
     */
    public function scopeWithoutAlwaysHidden($query)
    {
        return $query->withTrashed()
            ->orderBy(Reference::ATTR_DELETED_AT)
            ->where(static::ATTR_ALWAYS_HIDDEN, '=', 0);
    }

    /**
     * @param array $select
     * @return \Illuminate\Support\Collection
     */
    public static function getAlwaysHidden(array $select = ['*'])
    {
        return Reference::select($select)
            ->where(static::ATTR_ALWAYS_HIDDEN, '=', 1)
            ->where(static::ATTR_SLUG, '=', static::AUTO_REFERENCE_QUICKBOOKS)
            ->first();
    }

}
