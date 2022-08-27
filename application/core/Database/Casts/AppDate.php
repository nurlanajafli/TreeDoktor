<?php


namespace application\core\Database\Casts;

use application\core\Database\EloquentModel;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Support\Facades\Date;

class AppDate implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param EloquentModel $model
     * @param string $key
     * @param mixed $value
     * @param array $attributes
     * @return string
     */
    public function get($model, $key, $value, $attributes)
    {
        if (!empty($value)) {
            return $model->toDate($value)
                ->format(config_item('dateFormat'));
        }
        return null;
    }

    /**
     * Prepare the given value for storage.
     *
     * @param EloquentModel $model
     * @param string $key
     * @param array $value
     * @param array $attributes
     * @return string
     */
    public function set($model, $key, $value, $attributes)
    {
        if (Date::hasFormat($value, config_item('dateFormat'))) {
            return Date::createFromFormat(config_item('dateFormat'), $value);
        }
        return $model->toDate($value);
    }
}