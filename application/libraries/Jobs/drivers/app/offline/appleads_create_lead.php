<?php

use ElephantIO\Client as WSClient;
use ElephantIO\Engine\SocketIO\Version1X;

class appleads_create_lead extends CI_Driver implements JobsInterface
{
    var $payload, $body, $wsClient, $CI = [];

    public function getPayload($data = NULL) {
        return $data;
    }

    public function execute($job = NULL)
    {
        $this->CI =& get_instance();
        $this->CI->load->model('mdl_user');
        $this->CI->load->model('mdl_clients');
        $this->CI->load->model('mdl_leads_status');
        $this->CI->load->model('mdl_leads');
        $this->CI->load->model('mdl_client_tasks');
        $this->CI->load->model('mdl_leads_services');
        $this->CI->load->library('Common/LeadsActions');

        $this->payload = json_decode($job->job_payload, TRUE);
        $this->body = $this->payload['body'] ?? [];

        $post = $_POST = $this->body;
        $this->CI->token = TRUE;
        $this->CI->user = new stdClass();
        $this->CI->user->id = $this->payload['user_id'];
        

        $data['client_id'] = strip_tags($post['client_id']);
		$data['lead_body'] = isset($post['new_client_lead']) ? nl2br($post['new_client_lead']) : '';
        $user = $this->CI->mdl_user->getUserById($this->CI->user->id)[0];
        $data['lead_created_by'] = $user['firstname'] . " " . $user['lastname'];		
		$data['lead_author_id'] = $this->CI->user->id;
		$data['lead_date_created'] = $this->payload['date'];
		
		$data['lead_reffered_client'] = NULL;
		$data['lead_reffered_user'] = NULL;
		
		if (isset($post['reffered']) && $post['reffered'] != '') {
			$reffered = $post['reffered'];
			if($reffered == 'client')
			{
				if(isset($post['lead_reff_id']) && $post['lead_reff_id'] != '')
				{
					$data['lead_reffered_client'] = $post['lead_reff_id'];
					$data['lead_reffered_by'] = $reffered;
				}				
			}
			elseif($reffered == 'user')
			{
				
				if(isset($post['lead_reff_id']) && $post['lead_reff_id'] != '')
				{
					$data['lead_reffered_user'] = $post['lead_reff_id'];
					$data['lead_reffered_by'] = $reffered;
				}
			}
			elseif($reffered == 'other')
				$data['lead_reffered_by'] = $post['other_comment'];
			else
				$data['lead_reffered_by'] = $reffered;
		}
		
		if (isset($post['new_add']) && $post['new_add']) {
			$data['lead_address'] = strip_tags($post['new_address']);
			$data['lead_city'] = strip_tags($post['new_city']);
			$data['lead_state'] = strip_tags($post['new_state']);
			$data['lead_zip'] = strip_tags($post['new_zip']);
			$data['lead_country'] = strip_tags($post['lead_country']);
            if(isset($post['new_lat']))
			$data['latitude'] = strip_tags($post['new_lat']);
            if(isset($post['new_lon']))
			$data['longitude'] = strip_tags($post['new_lon']);
		} else {
			$client = $this->CI->mdl_clients->find_by_id($data['client_id']);
			$data['lead_address'] = $client->client_address;
			$data['lead_city'] = $client->client_city;
			$data['lead_state'] = $client->client_state;
			$data['lead_zip'] = $client->client_zip;
			$data['lead_country'] = $client->client_country;
			$data['latitude'] = $client->client_lat;
			$data['longitude'] = $client->client_lng;
		}
		
		if(!isset($data['latitude']) || !isset($data['longitude']) || !$data['latitude'] || !$data['longitude'])
		{
			$coords = get_lat_lon($data['lead_address'], $data['lead_city'], $data['lead_state'], $data['lead_zip'], $data['lead_country']);
			$data['latitude'] = $coords['lat'];
			$data['longitude'] = $coords['lon'];
		}
		$data['lead_neighborhood'] = get_neighborhood(['latitude' => $data['latitude'], 'longitude' => $data['longitude']]);
		
		$data['lead_scheduled'] = 0;
		
        if(isset($post['new_lead_timing']) && $post['new_lead_timing']){
            $data['timing'] = $post['new_lead_timing'];
        } else {
            $data['timing'] = 'Right Away';
        }
        
        if(isset($post['new_lead_priority']) && $post['new_lead_priority']){
            $data['lead_priority'] = $post['new_lead_priority'];
        } else {
            $data['lead_priority'] = 'Regular';
        }
        
        if(isset($post['preliminary_estimate'])){
            $data['preliminary_estimate'] = $post['preliminary_estimate'];
        }
        
        if(isset($post['lead_call']) && $post['lead_call']){
            $data['lead_call'] = 1;
        } else {
            $data['lead_call'] = 0;
        }
        
		$postpone = '';
        if(isset($post['postpone_date']))
		$postpone = $post['postpone_date'];
        
		$data['lead_postpone_date'] = date('Y-m-d');
		if($postpone != '')
			$data['lead_postpone_date'] = $postpone;
		
        if(isset($post['est_services'])){
            $servicesEst = $post['est_services'];
        } else {
            $servicesEst = '';
        }
		
		$preuploaded_files = [];
		if(isset($post['pre_uploaded_files']) && $post['pre_uploaded_files'] != null && count($post['pre_uploaded_files']) > 0){
			$preuploaded_files = $post['pre_uploaded_files'];
		}
		
		if($this->CI->leadsactions->create($data, $post, $servicesEst, $preuploaded_files)){
			$this->_socketWrite('syncJobSuccess', [
                'request_id' => $this->payload['id'],
                'client_id' => $data['client_id']
            ]);
            return TRUE;
		} else {
			$this->_socketWrite('syncJobFailed', [
                'request_id' => $this->payload['id'],
                'message' => 'Lead was not added',
            ]);
            return TRUE;
		}
                
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
