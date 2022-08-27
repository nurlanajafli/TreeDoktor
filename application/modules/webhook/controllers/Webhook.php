<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

use application\core\Interfaces\CallbackInterface;
use application\modules\webhook\models\EmailLogs as EmailLogsModel;
use application\modules\emails\models\Email as EmailModel;

/**
 * Webhook Controller
 * Execute callback controller
 */
class Webhook extends Webhook_Controller
{
    /**
     * Initial callback method
     * @return void
     */
    function callback()
    {
        array_shift($this->uri->segments);
        $callbackClass = $this->getClassName(array_pop($this->uri->segments));
        $pathToCallback = $this->uri->segments;
        $className = 'application\modules\\' . implode('\\', $pathToCallback) . '\callback\\' . ucfirst($callbackClass);
        $params = request()->query->all();
        try {
            $classInstance = new $className();
            if (!$classInstance instanceof CallbackInterface) {
                throw new Exception('You should implement callback interface');
            }
            $result = $classInstance->handle($params);
        } catch (\Throwable $e) {
            die(json_encode(['error' => $e->getMessage()]));
        }

        return $this->response($result, 200);
    }

    /**
     * Method prepare class name to camelcase type
     * @param $callbackClass
     * @return string
     */
    private function getClassName($callbackClass)
    {
        if (strpos($callbackClass, '-')) {
            $callbackClass = implode('', array_map(function ($value) {
                return ucfirst($value);
            }, explode('-', $callbackClass)));
        } else {
            $callbackClass = ucfirst($callbackClass);
        }
        return $callbackClass;
    }
}
