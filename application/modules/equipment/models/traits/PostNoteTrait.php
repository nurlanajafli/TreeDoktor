<?php


namespace application\modules\equipment\models\traits;


use application\core\Database\EloquentModel;
use application\modules\equipment\models\EquipmentNote;

trait PostNoteTrait
{

    public static function bootPostNoteTrait()
    {

        static::created(function ($model) {
            /** @var EloquentModel $model */
            if (!array_key_exists('eq_id', $model->attributes)) {
                return;
            }
            $text = $model->entName() . " \"" . $model->entNameCol() . "\" was created\n";
            if (method_exists(__CLASS__, 'notes')) {
                $model->notes()->create([
                    'eq_id' => $model->eq_id,
                    'note_description' => $text,
                    'note_type' => EquipmentNote::TYPE_SYSTEM
                ]);
            } else {
                EquipmentNote::post($model->eq_id, [], $text);
            }
        });

        static::updated(function ($model) {
            /** @var EloquentModel $model */
            if (!array_key_exists('eq_id', $model->attributes)) {
                return;
            }
            if ($model->isDirty()) {
                $text = $model->entName() . " was changed\n";
                $dirty = $model->getDirty();
                $text .= '<blockquote class="text-sm p-top-5 p-bottom-5 m-b-none">';
                foreach ($dirty as $col => $val) {
                    $text .= $model->colName($col) . " changed";
                    $text .= " from <strong>" . $model->postNoteOriginalCol($col) . "</strong>";
                    $text .= " to <strong>" . $model->postNoteCol($col) . "</strong>\n";
                }
                $text .= '</blockquote>';
            }
            if (method_exists(__CLASS__, 'notes')) {
                $model->notes()->create([
                    'eq_id' => $model->eq_id,
                    'note_description' => $text,
                    'note_type' => EquipmentNote::TYPE_SYSTEM
                ]);
            } else {
                EquipmentNote::post($model->eq_id, [], $text);
            }
        });

        static::deleted(function ($model) {
            /** @var EloquentModel $model */
            if (!array_key_exists('eq_id', $model->attributes)) {
                return;
            }
            $text = $model->entName() . " \"" . $model->entNameCol() . "\" was deleted\n";
            if (method_exists(__CLASS__, 'notes')) {
                $model->notes()->create([
                    'eq_id' => $model->eq_id,
                    'note_description' => $text,
                    'note_type' => EquipmentNote::TYPE_SYSTEM
                ]);
            } else {
                EquipmentNote::post($model->eq_id, [], $text);
            }
        });
    }

    public function postNoteOriginalCol($col)
    {
       if (array_key_exists($col, static::COL_RELATIONS)) {
            /** @var EloquentModel $origin */
            $origin = $this->getOriginModel();
            return $origin->{$col} !== null
                ? $origin->{static::COL_RELATIONS[$col]}->entNameCol()
                : 'empty';
        }
        if (array_key_exists($col, $this->getCasts()) && in_array($this->getCasts()[$col], ['boolean', 'bool'])) {
            return $this->getOriginal($col) ? 'true' : 'false';
        }
        return empty($this->getOriginal($col)) ? 'empty' : $this->getOriginal($col);
    }

    public function postNoteCol($col)
    {
        if (array_key_exists($col, static::COL_RELATIONS)) {
            return $this->{$col} !== null
                ? $this->{static::COL_RELATIONS[$col]}->entNameCol()
                : 'empty';
        }
        if (array_key_exists($col, $this->getCasts()) && in_array($this->getCasts()[$col], ['boolean', 'bool'])) {
            return $this->{$col} ? 'true' : 'false';
        }
        return  empty($this->{$col}) ? 'empty' : $this->{$col};
    }
}