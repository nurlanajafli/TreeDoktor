<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Log extends APP_Controller
{

    function __construct()
    {
        parent::__construct();
    }

    function index()
    {

        $path = 'uploads/applogs.txt';

        if (!is_file($path)) {
            $fp = fopen($path, 'w+');
            fclose($fp);
        }


        $data = file_get_contents($path);
        $obj = [];
        if ($data) {
            $obj = json_decode($data, true);
        }

        $obj[$this->device['device_id']] = isset($obj[$this->device['device_id']]) && is_array($obj[$this->device['device_id']]) ? $obj[$this->device['device_id']] : [];
        array_unshift($obj[$this->device['device_id']], [
            'date' => date('Y-m-d H:i:s'),
            'post' => $this->input->post()
        ]);


        file_put_contents($path, json_encode($obj));

        return $this->response(array(
            'status' => true,
            'data' => [
                'date' => date('Y-m-d H:i:s'),
                'post' => $this->input->post()
            ]
        ), 200);
    }

    function get()
    {
        $path = 'uploads/applogs.txt';
        $data = file_get_contents($path);
        $obj = [];
        if ($data) {
            $obj = json_decode($data);
        }

        return $this->response(array(
            'status' => true,
            'data' => $obj
        ), 200);
    }

    function clear()
    {
        $path = 'uploads/applogs.txt';
        $data = file_put_contents($path, '');
        return $this->response(array(
            'status' => true,
            'data' => []
        ), 200);
    }

    function slack_report()
    {
        $url = 'https://hooks.slack.com/services/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';
        $body = request()->all();
        $client = new \GuzzleHttp\Client();
        try {
            $res = $client->request('POST',
                $url, [
                    'json' => $body
                ]);
        } catch (Exception $e) {
            return $this->response(array(
                'status' => false,
                'message' => $e->getMessage(),
            ), 400);
        }
        return $this->response(array(
            'status' => true,
            'data' => $res->getBody()->getContents()
        ), $res->getStatusCode());
    }
}
