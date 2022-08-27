<?php


namespace application\core;


use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Validation\Rules\In;

class RulesMixin
{
    public function keyIn($values = [])
    {
        if ($values instanceof Arrayable) {
            $values = $values->toArray();
        }

        return new In(is_array($values) ? $values : func_get_args());
    }
}