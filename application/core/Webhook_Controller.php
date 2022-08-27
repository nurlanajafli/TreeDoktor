<?php if (!defined('BASEPATH')) exit('No direct script access allowed');


class Webhook_Controller extends MX_Controller
{
    public function __construct($json = true)
    {
        parent::__construct();

        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
        header('Access-Control-Max-Age: 1000');
        header('Access-Control-Allow-Headers: Content-Type, Version, Authorization, X-Requested-With, responseType');
        if ($json === true) {
            header('Content-type: application/json');
        }
        $method = isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] ? $_SERVER['REQUEST_METHOD'] : NULL;
        if($method == "OPTIONS")
            exit;

        $this->_rawToPost();
    }

    private function _rawToPost() {
        $raw = file_get_contents('php://input');
        if($raw) {
            $jsonArray = json_decode($raw,true);
            if($jsonArray && json_last_error() === JSON_ERROR_NONE) {
                $_POST = array_merge($_POST, $jsonArray);
            }
        }
    }
}
