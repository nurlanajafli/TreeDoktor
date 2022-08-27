<?php


namespace application\models\Relations;

use application\core\Database\EloquentModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HasManySyncable extends HasMany
{
    public function sync($data, $deleting = true, $additional = [])
    {
        $changes = [
            'created' => [],
            'deleted' => [],
            'updated' => [],
        ];
        $updateIds = [];

        $relatedKeyName = $this->related->getKeyName();

        $all = $this->newQuery()->get();

        foreach ($data as $key => $row) {
            if (isset($row[$relatedKeyName])
                && !empty($row[$relatedKeyName])
                && ($current = $all->firstWhere($relatedKeyName, $row[$relatedKeyName])) !== null) {
                /** @var EloquentModel $current */
                $current->fill($row)->save();
                $changes['updated'][] = $this->castKey($current->$relatedKeyName);
                $updateIds[] = $current->$relatedKeyName;
                unset($current);
            } else {
                /** @var EloquentModel $newModel */
                $newModel = $this->create(array_merge($row, $additional));
                $changes['created'][$key] = $this->castKey($newModel->$relatedKeyName);
                unset($newModel);
            }
        }

        if ($deleting) {
            foreach ($all as $current) {
                /** @var EloquentModel $current */
                if (!in_array($current->$relatedKeyName, $updateIds)) {
                    $deletedId = $current->$relatedKeyName;
                    $current->delete();
                    $changes['deleted'][] = $deletedId;
                    unset($deletedId);
                }
            }
        }
        return $changes;
    }


    /**
     * Cast the given keys to integers if they are numeric and string otherwise.
     *
     * @param array $keys
     * @return array
     */
    protected function castKeys(array $keys)
    {
        return (array)array_map(function ($v) {
            return $this->castKey($v);
        }, $keys);
    }

    /**
     * Cast the given key to an integer if it is numeric.
     *
     * @param mixed $key
     * @return mixed
     */
    protected function castKey($key)
    {
        return is_numeric($key) ? (int)$key : (string)$key;
    }
}