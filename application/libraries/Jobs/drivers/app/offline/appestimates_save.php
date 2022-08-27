<?php

use ElephantIO\Client as WSClient;
use ElephantIO\Engine\SocketIO\Version1X;

class appestimates_save extends CI_Driver implements JobsInterface
{
    var $payload, $body, $wsClient, $CI = [];

    public function getPayload($data = NULL) {
        return $data;
    }

    public function execute($job = NULL)
    {
        $this->CI =& get_instance();
        $this->CI->load->model('mdl_estimates_orm');
        $this->CI->load->model('mdl_leads');
        $this->CI->load->library('Common/EstimateActions');

        $this->payload = json_decode($job->job_payload, TRUE);
        $this->body = $this->payload['body'] ?? [];

        $_POST = $this->body;
        $this->CI->token = TRUE;
        $this->CI->user = new stdClass();
        $this->CI->user->id = $this->payload['user_id'];

        $leadId = false;
        if(isset($this->body['lead_id']) && $this->body['lead_id'] != null && $this->body['lead_id'] != ''){
            $leadId = $this->body['lead_id'];
        }
        
//        if(!$leadId) {
//            if(!$this->body['tmp_lead_id'] || $this->body['tmp_lead_id'] == null || $this->body['tmp_lead_id'] == '') {
//                $this->_socketWrite('syncJobFailed', [
//                    'request_id' => $this->payload['id'],
//                    'message' => 'lead_id is required',
//                ]);
//                return TRUE;
//            }
//        }

        if($leadId) {
            $estimate = $this->CI->mdl_estimates_orm->get_by(['lead_id' => $leadId]);
            if($estimate) {
                $this->_socketWrite('syncJobFailed', [
                    'request_id' => $this->payload['id'],
                    'message' => 'Estimate Is Already Exists',
                ]);
                return TRUE;
            }
        }

        $estimate_id = $this->CI->mdl_estimates_orm->save_estimate();

        $updateEstimateData = ['date_created' => strtotime($this->payload['date'])];
        $this->CI->mdl_estimates_orm->update($estimate_id, $updateEstimateData);
                    
        $new_lead_id = $this->CI->mdl_estimates_orm->get_by(['estimate_id' => $estimate_id])->lead_id;//get from estimate
		$this->CI->mdl_leads->update_leads(['lead_date_created' => $this->payload['date']], ["lead_id" => $new_lead_id]);
        
        $this->CI->estimateactions->setEstimateId($estimate_id);
        if($this->CI->estimateactions->sign(isset($this->body['data']) ? $this->body['data'] : NULL)) {
            $this->CI->estimateactions->confirm('Signature', NULL, NULL, $this->payload['date']);

            if(isset($this->body['is_email']) && $this->body['is_email'] && isset($this->body['email']) && $this->body['email'])
                $this->CI->estimateactions->sendConfirmed($this->body['email']);
        }

        $this->_socketWrite('syncJobSuccess', [
            'request_id' => $this->payload['id'],
            'estimate_id' => $estimate_id
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
