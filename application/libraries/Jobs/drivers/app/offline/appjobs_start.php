<?php

use ElephantIO\Client as WSClient;
use ElephantIO\Engine\SocketIO\Version1X;

class appjobs_start extends CI_Driver implements JobsInterface
{
    var $payload, $body, $wsClient, $CI = [];

    public function getPayload($data = NULL) {
        return $data;
    }

    public function execute($job = NULL)
    {
        $this->CI =& get_instance();
        $this->CI->load->model('mdl_schedule');
        $this->CI->load->model('mdl_workorders');
        $this->CI->load->model('mdl_events_orm');
        $this->CI->load->helper('events_helper');
        $this->CI->load->library('Common/EventActions');

        $this->payload = json_decode($job->job_payload, TRUE);
        $this->body = $this->payload['body'] ?? [];
        $date = $this->payload['date'] ?? date('Y-m-d H:i:s');

        $uriArgs = $this->payload['route']['uri_args'] ?? [];
        $eventId = array_shift($uriArgs);

        $id = intval($eventId);

        $event = $this->CI->mdl_schedule->getAppEvent($id);
        $this->body['ev_estimate_id'] = $event->estimate_id;

        if(!$event) {
            $this->_socketWrite('syncJobFailed', [
                'request_id' => $this->payload['id'],
                'message' => 'Incorrect Event',
            ]);
            return TRUE;
        }

        $this->CI->load->library('form_validation');
        $this->CI->form_validation->set_data($this->body);
        $this->CI->form_validation->set_rules('signature_image', 'Signature', 'required');
        if ($this->CI->form_validation->run() == FALSE) {
            $this->_socketWrite('syncJobFailed', [
                'request_id' => $this->payload['id'],
                'message' => 'Validation Error',
                'errors' => validation_errors_array()
            ]);
            return TRUE;
        }

        $result = $this->CI->eventactions->start_work($this->body + [
            'ev_team_id' => $event->event_team_id,
            'wo_id' => $event->event_wo_id,
            'ev_start_time' => $date,
            'ev_start_work' => $date,
        ]);

        $this->_socketWrite('syncJobSuccess', [
            'request_id' => $this->payload['id'],
            'pdf_url' => base_url('events/tailgate_safety_pdf/' . $event->id)
        ]);

        return TRUE;
    }

    private function _socketWrite($msg, $response = []) {
        if(config_item('wsClient')) {
            if(!$this->wsClient) {
                $this->wsClient = new WSClient(new Version1X(config_item('wsClient') . '?chat=1&user_id=' . $this->payload['user_id']));
                $this->wsClient->initialize();
            }
            if($this->wsClient) {
                $this->wsClient->emit('room', ['chat-' . $this->payload['user_id']]);
                $this->wsClient->emit('message', ['method' => $msg, 'params' => $response]);
            }
        }
    }
}
