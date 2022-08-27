<?php

use ElephantIO\Client as WSClient;
use ElephantIO\Engine\SocketIO\Version1X;

class appclients_create extends CI_Driver implements JobsInterface
{
    public $payload, $body, $wsClient, $CI = [];

    public function getPayload($data = NULL)
    {
        return $data;
    }

    public function execute($job = NULL)
    {
        $this->CI =& get_instance();
        $this->CI->load->model('mdl_clients');
        $this->CI->load->model('mdl_user');
        $this->CI->load->model('mdl_leads');
        $this->CI->load->library('Common/ClientsActions');

        $this->payload = json_decode($job->job_payload, TRUE);
        $this->body = $this->payload['body'] ?? [];

        $post = $_POST = $this->body;
        $this->CI->token = TRUE;
        $this->CI->user = new stdClass();
        $this->CI->user->id = $this->payload['user_id'];

        $all_contacts_data = [];

        foreach ($post['client_name'] as $key => $value) {
            //if (!empty($post['client_name'][$key]) || !empty($post['client_phone'][$key]) || !empty($post['client_email'][$key])) {

            $phone_to_save = null;
            $phone_to_save_clean = null;
            $email = null;
            $email_exists = null;

            if (isset($post['client_phone'][$key]) && $post['client_phone'][$key]) {
                $phone_to_save = numberFrom($post['client_phone'][$key]);
                $phone_to_save_clean = substr($phone_to_save, 0, config_item('phone_clean_length'));
            }

            if (isset($post['client_email'][$key]) && $post['client_email'][$key]) {
                $email = $post['client_email'][$key];
                $email_exists = check_email_exists($email);
            }

            $contact_data['cc_title'] = isset($post['client_title'][$key]) ? $post['client_title'][$key] : NULL;
            $contact_data['cc_name'] = isset($post['client_name'][$key]) ? $post['client_name'][$key] : NULL;
            $contact_data['cc_phone'] = $phone_to_save;
            $contact_data['cc_phone_clean'] = $phone_to_save_clean;
            $contact_data['cc_email'] = $email ?? null;
            $contact_data['cc_email_check'] = $email_exists ?? null;
            $contact_data['cc_print'] = isset($post['client_print'][$key]) ? $post['client_print'][$key] : 0;

            $all_contacts_data[] = $contact_data;
            //}
        }

        //Checkboxes:
        $uData = $this->CI->mdl_user->getUserById($this->CI->user->id);
        $user = $uData[0];
        $lead_data['lead_created_by'] = $user['firstname'] . " " . $user['lastname'];
        $lead_data['lead_date_created'] = $this->payload['date'];

        if (isset($post['new_address']) && $post['new_address'] != null && $post['new_address'] != '') {
            $lead_data['lead_address'] = $post['new_address'];
        } else {
            $lead_data['lead_address'] = isset($post['new_client_address']) ? $post['new_client_address'] : '';
        }

        if (isset($post['new_city']) && $post['new_city'] != null && $post['new_city'] != '') {
            $lead_data['lead_city'] = $post['new_city'];
        } else {
            $lead_data['lead_city'] = isset($post['new_client_city']) ? $post['new_client_city'] : '';
        }

        if (isset($post['new_state']) && $post['new_state'] != null && $post['new_state'] != '') {
            $lead_data['lead_state'] = $post['new_state'];
        } else {
            $lead_data['lead_state'] = isset($post['new_client_state']) ? $post['new_client_state'] : '';
        }

        if (isset($post['new_zip']) && $post['new_zip'] != null && $post['new_zip'] != '') {
            $lead_data['lead_zip'] = $post['new_zip'];
        } else {
            $lead_data['lead_zip'] = isset($post['new_client_zip']) ? $post['new_client_zip'] : '';
        }

        if (isset($post['new_country']) && $post['new_country'] != null && $post['new_country'] != '') {
            $lead_data['lead_country'] = $post['new_country'];
        } else {
            $lead_data['lead_country'] = isset($post['new_client_country']) ? $post['new_client_country'] : '';
        }

        if (isset($post['lead_scheduled'])) {
            $lead_data['lead_scheduled'] = 1;
        } else {
            $lead_data['lead_scheduled'] = 0;
        }

        if (isset($post['lead_call'])) {
            $lead_data['lead_call'] = 1;
        } else {
            $lead_data['lead_call'] = 0;
        }

        $lead_data['lead_reffered_client'] = NULL;
        $lead_data['lead_reffered_user'] = NULL;
        $lead_data['lead_reffered_by'] = NULL;
        if ($post['reffered'] != '') {
            $reffered = $post['reffered'];
            if ($reffered == 'client') {
                $lead_data['lead_reffered_by'] = $reffered;
                $lead_data['lead_reffered_client'] = $post['reff_id'];
            } elseif ($reffered == 'user') {
                $lead_data['lead_reffered_user'] = $post['reff_id'];
                $lead_data['lead_reffered_by'] = $reffered;
            } elseif ($reffered == 'other')
                $lead_data['lead_reffered_by'] = $post['other_comment'];
            else
                $lead_data['lead_reffered_by'] = $reffered;
        }

        if (isset($post['new_lat']) && $post['new_lat'] != null && $post['new_lat'] != '') {
            $lead_data['latitude'] = $post['new_lat'];
        } else {
            $lead_data['latitude'] = isset($post['new_client_lat']) ? $post['new_client_lat'] : null;
        }
        if (isset($post['new_lon']) && $post['new_lon'] != null && $post['new_lon'] != '') {
            $lead_data['longitude'] = $post['new_lon'];
        } else {
            $lead_data['longitude'] = isset($post['new_client_lon']) ? $post['new_client_lon'] : null;
        }

        if ($lead_data['latitude'] == null || $lead_data['longitude'] == null) {
            $coords = get_lat_lon($lead_data['lead_address'], $lead_data['lead_city'], $lead_data['lead_state'], $lead_data['lead_zip']);
            $lead_data['latitude'] = $coords['lat'];
            $lead_data['longitude'] = $coords['lon'];
        }
        $lead_data['lead_neighborhood'] = get_neighborhood(['latitude' => $lead_data['latitude'], 'longitude' => $lead_data['longitude']]);

        $lead_data['preliminary_estimate'] = $post['preliminary_estimate'] ?? null;
        $lead_data['timing'] = isset($post['new_lead_timing']) ? strip_tags($post['new_lead_timing']) : '';
        $lead_data['lead_body'] = isset($post['new_client_lead']) ? strip_tags($post['new_client_lead']) : '';
        $lead_data['lead_priority'] = isset($post['new_lead_priority']) ?  strip_tags($post['new_lead_priority']) : 'Regular';
        $lead_data['lead_author_id'] = $this->CI->user->id;

        $services = [];
        $idsStr = null;
        if (isset($post['est_services'])) {
            $idsStr .= $post['est_services'];
        }
        if (isset($post['est_products'])) {
            $idsStr .=  '|' . $post['est_products'];
        }
        if (isset($post['est_bundles'])) {
            $idsStr .= '|' . $post['est_bundles'];
        }
        $services = explode('|', ltrim($idsStr, '|'));
        $preuploaded_files = [];
        if (isset($post['pre_uploaded_files']) && $post['pre_uploaded_files'] != null && count($post['pre_uploaded_files']) > 0) {
            $preuploaded_files = $post['pre_uploaded_files'];
        }

        $tagNames = [];
        if (isset($post['tag_names']) && !empty($post['tag_names']) && is_array($post['tag_names'])) {
            $tagNames = $post['tag_names'];
        }

        if ($client_id = $this->CI->clientsactions->create($all_contacts_data, $lead_data, $services, $post, $preuploaded_files, false, $tagNames)) {
            $this->CI->clientsactions->update_date_offline($client_id, ['client_date_created' => explode(' ', $this->payload['date'])[0]]);
            $estimateData = $this->getDataForCreateEstimate($client_id, $post);
            if(!empty($estimateData) && $this->hasStringKeys($estimateData)){
                $estimateData['user_id'] = $this->CI->user->id;
                $estimateData['date'] = $this->payload['date'];
                $estimateData['id'] = $this->payload['id'];
                pushJob('app/offline/appestimates_save', $estimateData);
            }elseif(!empty($estimateData)){
                foreach ($estimateData as  $data){
                    $data['user_id'] = $this->CI->user->id;
                    $data['date'] = $this->payload['date'];
                    $data['id'] = $this->payload['id'];
                    pushJob('app/offline/appestimates_save', $data);
                }
            }
            $this->_socketWrite('syncJobSuccess', [
                'request_id' => $this->payload['id'],
                'client_id' => $client_id
            ]);

            return TRUE;
        } else {
            $this->_socketWrite('syncJobFailed', [
                'request_id' => $this->payload['id'],
                'message' => 'Client was not added',
            ]);
            return TRUE;
        }

    }

    private function _socketWrite($msg, $response = [])
    {
        if (config_item('wsClient')) {
            if (!$this->wsClient) {
                $this->wsClient = new WSClient(new Version1X(config_item('wsClient') . '?chat=1&user_id=' . $this->payload['user_id']));
                $this->wsClient->initialize();
            }
            if ($this->wsClient) {
                $this->wsClient->emit('room', ['chat-' . $this->payload['user_id']]);
                $this->wsClient->emit('message', ['method' => $msg, 'params' => $response]);
            }
        }
    }

    private function getDataForCreateEstimate($clientId, $post)
    {
        $lead = $this->CI->mdl_leads->find_by_fields(['client_id' => $clientId/*, 'lead_status' => null*/]);
        if (empty($lead) || empty($post))
            return null;
        $leadId = $lead->lead_id;
        if (isset($post['estimates']) && !empty($post['estimates'])) {
            if (count($post['estimates']) > 1) {
                foreach ($post['estimates'] as $key => $item) {
                    $tmpArrayForEstimate['body'] = $item;
                    $tmpArrayForEstimate['body']['client_id'] = $clientId;
                    if($key == 0)
                        $tmpArrayForEstimate['body']['lead_id'] = $leadId;
                    else{
                        unset( $tmpArrayForEstimate['body']['lead_id']);
                        $tmpArrayForEstimate['body']['tmp_lead_id'] = !empty($item['tmp_lead_id']) ? $item['tmp_lead_id'] : $item['lead_id'];
                        $tmpArrayForEstimate['body']['street'] = isset($post['new_client_address']) ? $post['new_client_address'] : '';
                        $tmpArrayForEstimate['body']['city'] = isset($post['new_client_city']) ? $post['new_client_city'] : '';
                        $tmpArrayForEstimate['body']['state'] = isset($post['new_client_state']) ? : '';
                        $tmpArrayForEstimate['body']['zip'] = isset($post['new_client_zip']) ? $post['new_client_zip'] : '';
                        $tmpArrayForEstimate['body']['country'] = isset($post['new_client_country']) ? $post['new_client_country'] : '';
                    }
                    $arrayForEstimate[] = $tmpArrayForEstimate;
                }
            } else {
                $arrayForEstimate['body'] = $post['estimates'][0];
                $arrayForEstimate['body']['client_id'] = $clientId;
                $arrayForEstimate['body']['lead_id'] = $leadId;
            }
        } else {
            return null;
        }
        return $arrayForEstimate;
    }

    // checking if an associative array is
    function hasStringKeys(array $array) {
        return count(array_filter(array_keys($array), 'is_string')) > 0;
    }
}
