<?php


namespace application\core\Database;


use application\models\Relations\HasManySyncable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Concerns\AsPivot;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Str;

/**
 *
 *
 * @mixin \Eloquent
 * @mixin \Illuminate\Database\Eloquent\Builder
 * @mixin \Illuminate\Database\Query\Builder
 */
abstract class EloquentModel extends Model
{
    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey;

    protected $dateFormat = 'Y-m-d H:i:s';

    const CREATED_AT = null;
    const UPDATED_AT = null;

    const COLUMNS = [];
    const ENT_NAME = 'Model';
    const NAME_COL = false;
    const COL_RELATIONS = [];

    /**
     * Get the primary key for the model.
     *
     * @return string
     */
    public function getKeyName()
    {
        $basename = class_basename($this);
        $name = preg_replace('/model$/i', '', $basename);
        return $this->primaryKey ?? str_replace('\\', '', Str::snake(Str::singular($name)));
    }

    public static function tableName()
    {
        return with(new static)->getTable();
    }

    public static function colName($col, $default = false)
    {
        return array_key_exists($col, static::COLUMNS) ? static::COLUMNS[$col] : ($default or $col);
    }

    public function entName($ent = false, $col = false)
    {
        return static::ENT_NAME;
    }

    public function entNameCol($col = false)
    {
        $col = $col ?: static::NAME_COL;
        if ($col && array_key_exists($col, $this->attributes)) {
            return $this->attributes[$col];
        }
        if ($col && in_array($col, $this->appends)) {
            return $this->{$col};
        }
        return $this->primaryKey ? ' #' . $this->attributes[$this->primaryKey] : '';
    }

    public function getOriginModel()
    {
        return static::make($this->getRawOriginal());
    }


    /**
     * Instantiate a new HasMany relationship.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \Illuminate\Database\Eloquent\Model $parent
     * @param string $foreignKey
     * @param string $localKey
     * @return HasManySyncable
     */
    protected function newHasMany(Builder $query, Model $parent, $foreignKey, $localKey)
    {
        return new HasManySyncable($query, $parent, $foreignKey, $localKey);
    }

    /**
     * @param $value
     * @return \Illuminate\Support\Carbon
     */
    public function toDate($value)
    {
        return $this->asDate($value);
    }

    /**
     * @param $value
     * @return \Illuminate\Support\Carbon
     */
    public function toDateTime($value)
    {
        return $this->asDateTime($value);
    }

    /**
     * Perform any actions required after the model boots.
     *
     * @return void
     */
    public function __construct(array $attributes = [])
    {
        //$this->setDateFormat(config_item('dateFormat') . (config_item('time') == 12 ? ' h:i:s a' : ' H:i:s'));
        parent::__construct($attributes);
    }


    /**
     * Reload the current model instance with fresh attributes from the database.
     *
     * @return $this
     */
    public function refreshWithScopes()
    {
        if (!$this->exists) {
            return $this;
        }

        $this->setRawAttributes(
            static::newQuery()->findOrFail($this->getKey())->attributes
        );

        $this->load(collect($this->relations)->reject(function ($relation) {
            return $relation instanceof Pivot
                || (is_object($relation) && in_array(AsPivot::class, class_uses_recursive($relation), true));
        })->keys()->all());

        $this->syncOriginal();

        return $this;
    }
}