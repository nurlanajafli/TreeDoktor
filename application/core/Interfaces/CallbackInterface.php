<?php

namespace application\core\Interfaces;

/**
 * Callback interface
 */
interface CallbackInterface
{
    /**
     * @param array $params
     * @return mixed
     */
    public function handle(array $params);
}