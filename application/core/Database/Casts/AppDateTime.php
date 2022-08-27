<?php


namespace application\core\Database\Casts;

use application\core\Database\EloquentModel;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Support\Facades\Date;

class AppDateTime implements CastsAttributes
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
            return $model->toDateTime($value)
                ->format(config_item('dateFormat') . (config_item('time') == 12 ? ' h:i:s a' : ' H:i:s'));
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
        $format = config_item('dateFormat') . (config_item('time') == 12 ? ' h:i:s a' : ' H:i:s');
        if (Date::hasFormat($value, $format)) {
            return Date::createFromFormat($format, $value);
        }
        return $model->toDateTime($value);
    }
}