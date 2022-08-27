<?php
namespace application\Collections;
use Illuminate\Database\Eloquent\Collection;
class Select2Collection extends Collection
{
    public function  forSelect2($key = 'id', $value = 'text', $short = false)
    {
        return $this->map(function ($item) use ($key, $value, $short){
            if(!$short)
                return collect($item)->merge(['id'=>$item->$key, 'text'=>$item->$value]);

            return ['id'=>$item->$key, 'text'=>$item->$value];
        });
    }
}