<?php

use ElephantIO\Engine\SocketIO\Version1X;
use ElephantIO\Client as WSClient;

class socket_send extends CI_Driver implements JobsInterface
{
    public function getPayload($data = NULL)
    {
        if (!is_array($data['room']) || !sizeof($data['room']) || !is_array($data['message']) || !sizeof($data['message'])
            || empty($data['message']['method'])/* || !isset($data['message']['params'])*/) {
            return false;
        }

        return $data;
    }

    public function execute($job = NULL)
    {
        $wsClient = new WSClient(new Version1X(config_item('wsClient')));

        if (!$wsClient) {
            return false;
        }

        $payload = json_decode($job->job_payload, true);

        $wsClient->initialize();
        $wsClient->emit('room', $payload['room']);
        $wsClient->emit('message', $payload['message']);
        $wsClient->close();

        return TRUE;
    }
}
