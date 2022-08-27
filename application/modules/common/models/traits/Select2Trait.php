<?php


namespace application\modules\common\models\traits;
use application\Collections\Select2Collection;

trait  Select2Trait
{
    public function newCollection(array $models = [])
    {
        return new Select2Collection($models);
    }
}