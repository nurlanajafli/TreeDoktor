<?php

use application\modules\clients\models\Client;
use application\modules\clients\models\ClientNote;
use application\modules\clients\models\Tag;

class ClientsActions
{
    protected $CI;
    protected $client;
    
    function __construct($clientId = NULL) {
        $this->CI =& get_instance();

        $this->CI->load->model('mdl_clients');
		$this->CI->load->model('mdl_leads_status');
		$this->CI->load->model('mdl_leads');
		$this->CI->load->model('mdl_leads_services');
		$this->CI->load->model('mdl_client_tasks');
		$this->CI->load->model('mdl_user');
		$this->CI->load->model('mdl_paint');

        if ($clientId) {
            $this->client = $this->mdl_clients->get_client_by_id($clientId);
        }
        
    }
    
    function create($all_contacts_data, $lead_data, $services, $post, $preuploaded_files, $send_lead = false, $tags = []) {
        $this->CI->load->model('mdl_clients');
        $this->CI->load->model('mdl_leads');
        $this->CI->load->model('mdl_leads_services');
        $this->CI->load->model('mdl_leads_status');
        $this->CI->load->model('mdl_client_tasks');
        if($client_id = $this->CI->mdl_clients->add_new_client()) {
            foreach($all_contacts_data as $contact){
                $contact['cc_client_id'] = $client_id;
                $this->CI->mdl_clients->add_client_contact($contact);
            }

            if(!empty($tags) && is_array($tags)) {
                Tag::syncTagsWithClient($tags, $client_id);
            }
            
            pushJob('quickbooks/client/syncclientinqb', serialize(['id' => $client_id, 'qbId' => '']));

            $defaultStatus = $this->CI->mdl_leads_status->get_by(['lead_status_default' => 1]);
            $lead_data['lead_status_id'] = $defaultStatus->lead_status_id;
            $lead_data['client_id'] = $client_id;

            $lead_id = $this->CI->mdl_leads->insert_leads($lead_data);

			if ($lead_id) {
                
                if(!empty($services)) {
                    foreach($services as $k=>$v)
                        $this->CI->mdl_leads_services->insert(['lead_id' => $lead_id, 'services_id' => intval($v)]);
                }
                        
                $appt = $post;
                $appt['client_id'] = $client_id;
				$appt['lead_id'] = $lead_id;
				$this->CI->mdl_client_tasks->office_data($appt);
                
                $lead_no = str_pad($lead_id, 5, '0', STR_PAD_LEFT);
				$lead_no = $lead_no . "-L";
				$update_data = array("lead_no" => $lead_no);
				$wdata = array("lead_id" => $lead_id);

				$lead_no_updated = $this->CI->mdl_leads->update_leads($update_data, $wdata);
                
                //move files from tmp to the actual lead_id folder
				if(!empty($preuploaded_files)) {
                    $batchUpdate = [];
					foreach($preuploaded_files as $file){
						$file_name = explode('/', $file)[4];
						$new_path = 'uploads/clients_files/' . $client_id . '/leads/' . str_pad($lead_id, 5, '0', STR_PAD_LEFT) . '-L/' . str_replace('0-L', str_pad($lead_id, 5, '0', STR_PAD_LEFT) . '-L', $file_name);
						bucket_copy($file, $new_path);
						bucket_unlink($file);
                        $batchUpdate[] = [
                            'paint_path' => $file,
                            'paint_path ' => $new_path //space in key is required !!!
                        ];
					}
                    $this->CI->mdl_paint->updateBatchByPaths($batchUpdate);
				}
                
                if ($lead_no_updated) {
					make_notes($client_id, 'Hey, I just created a new client.', 'system', 0);
                    if($send_lead){
                       return $client_id . '_' . $lead_id;
                    } else {
                        return $client_id;
                    }										
				}
                        
            } else {
                return false;
            }
            
        } else {
            return false;
        }
    }
    
    function update($id, $data) {
        
        $cl_data = $this->CI->mdl_clients->get_client_by_id($id);
        $wdata['client_id'] = $id;
        $changed_data_arr = $this->getListOfChanges($data, $id);
        
        if ($this->CI->mdl_clients->update_client($data, $wdata)) {

            if (!empty($changed_data_arr)) {
                //Posting a note to the new client profile;
                //make_notes is a helper function defined at helper/notes_helper.php and the helper is autoloaded
                foreach ($changed_data_arr as $key => $val) {
                    $update_msg = ucfirst($key) . ' was modified from ' . $val['pre_data'] . ' to ' . $val['new_data'];
                    make_notes($id, $update_msg, 'system', 0);
                }
				
				pushJob('quickbooks/client/syncclientinqb', serialize(['id' => $id, 'qbId' => $cl_data->client_qb_id]));
                
                return true;               
            } else {
                return null;
            }

        } else {
            return false;
        }
        
    }
    
    function update_address($id, $data) {
        
        $oldData = $this->CI->mdl_clients->get_clients('client_address, client_city, client_state, client_country, client_zip, client_main_intersection', array('client_id' => $id));
        
        if($this->CI->mdl_clients->update_client($data, array('client_id' => $id))){
            //create a new job for client synchronization in qb
            $cl_data = $this->CI->mdl_clients->get_client_by_id($id);
            pushJob('quickbooks/client/syncclientinqb', serialize(['id' => $id, 'qbId' => $cl_data->client_qb_id]));
    
            $oldAddress = $oldData->row_array();
            if ($oldData->num_rows()) {
                $text = 'Hey, I just changed client address:<br><ul>';
                foreach ($oldAddress as $k => $v)
                    $text .= '<li>' . $v . ' => ' . $data[$k] . '</li>';
                $text .= '</ul>';
                make_notes($id, $text, 'system', 0);
            }
    
            $result['main_intersection'] = $data['client_main_intersection'];
            $result['address'] = $data['client_address'];
            $result['city'] = $data['client_city'];
            $result['state'] = $data['client_state'];
            $result['zip'] = $data['client_zip'];
            $result['country'] = $data['client_country'];
    
            $result['lat'] = $data['client_lat'];
            $result['lon'] = $data['client_lng'];
    
            $result['id'] = $id;
            $result['name'] = 'client';
            $result['status'] = 'ok';
            
            return $result;
            
        } else {
            return false;
        }
    }
    
    function update_date_offline($id, $data) {
        if($this->CI->mdl_clients->update_client($data, array('client_id' => $id))){
            return true;
        } else {
             return false;
        }     
    }
    
    function getListOfChanges($data, $client_id)
    {
        $changed_data_arr = array();
        //getting the previous data
        $client_pre_data = $this->CI->mdl_clients->find_by_id($client_id);

        foreach ($data as $key => $val) {
            if(isset($client_pre_data->$key)) {
                if ($client_pre_data->$key != $data[$key]) {
                    $changed_data_arr[$key] = array('pre_data' => $client_pre_data->$key, 'new_data' => $data[$key]);
                }
            }
        }
		
		return $changed_data_arr;
	}
    
    function delete($id, $get_user)
    {        
        $user = $this->CI->mdl_user->get_user('', $get_user);

        if (!$user || !$user->num_rows()) {
             return false;
        } else {
            //create a new job for client synchronization in qb
            $cl_data = $this->CI->mdl_clients->get_client_by_id($id);
            pushJob('quickbooks/client/syncclientinqb', serialize(['id' => $id, 'qbId' => $cl_data->client_qb_id]));
    
            if($this->CI->mdl_clients->complete_client_removal($id)){
                return true;
            } else {
                return false;
            }
            
        }
    }
    
    function create_note($client_id, $note_data) {
        return ClientNote::createNote($note_data);
    }
    
    function delete_note_client($id) {
        $client_id = $this->CI->mdl_clients->delete_note_client($id)->client_id;
         
        if ($this->CI->mdl_clients->delete_note($id)) {

            return $client_id;

        } else {
            return false;
        }
    }
    
    function set_client($clientId){
        $this->client = $this->CI->mdl_clients->get_client_by_id($clientId);
    }

    function get_client(){
        return $this->client;
    }

    function leads($lead_id = null)
    {
        if(!$this->client)
            return [];

        $where = [];
        if($lead_id)
            $where = ['leads.lead_id'=>$lead_id];

        return $this->CI->mdl_leads->get_client_leads($this->client->client_id, $where)->result();
    }

    public function getCCFromQbCustomerToDB(object $QBCustomer, int $clientId): array {
        $cc = [];
        if(!empty($QBCustomer)) {
            $billAddress = $QBCustomer->BillAddr;
            $addressLine1 = isset($billAddress->Line1) ? $billAddress->Line1 : '';
            $addressLine2 = isset($billAddress->Line2) ? $billAddress->Line2 : '';
            $addressLine3 = isset($billAddress->Line3) ? $billAddress->Line3 : '';
            $addressLine4 = isset($billAddress->Line4) ? $billAddress->Line4 : '';
            $addressLine5 = isset($billAddress->Line5) ? $billAddress->Line5 : '';
            $name = !empty($QBCustomer->GivenName) || !empty($QBCustomer->FamilyName) ? $QBCustomer->GivenName . ' ' . $QBCustomer->FamilyName : $QBCustomer->DisplayName;
            $cc = [
                'cc_title' => $QBCustomer->DisplayName,
                'cc_name' => $name,
                'cc_email' => !empty($QBCustomer->PrimaryEmailAddr->Address) ? $QBCustomer->PrimaryEmailAddr->Address : null,
                'cc_client_id' => $clientId,
            ];

            $phoneCleanLength = config_item('phone_clean_length');

            if (!empty($QBCustomer->PrimaryPhone->FreeFormNumber)) {
                $cc['cc_phone'] = numberFrom($QBCustomer->PrimaryPhone->FreeFormNumber);
                $cc['cc_phone_clean'] = substr(numberFrom($QBCustomer->PrimaryPhone->FreeFormNumber), 0, $phoneCleanLength);
            }
            if (!empty($QBCustomer->Mobile->FreeFormNumber)) {
                $cc['cc_phone'] = numberFrom($QBCustomer->Mobile->FreeFormNumber);
                $cc['cc_phone_clean'] = substr(numberFrom($QBCustomer->Mobile->FreeFormNumber), 0, $phoneCleanLength);
            }
            $cc['cc_email_check'] = NULL;
        }
        return $cc;
    }

    public function getClientType(){
        if(empty($this->get_client()))
            return false;
        return $this->client->client_type;
    }
}
