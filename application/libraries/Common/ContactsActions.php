<?php

class ContactsActions
{
    protected $CI;
        
    function __construct($clientId = NULL) {
        $this->CI =& get_instance();

        $this->CI->load->model('mdl_clients');	           
    }
    
    function create($data){
        $client_id = $data['cc_client_id'] ?? null;

        if(!$client_id) {
            return false;
        }
        
        if($this->CI->mdl_clients->add_client_contact($data)){
			$result = $this->CI->db->insert_id();
			
			unset($data['cc_client_id'], $data['cc_email_check']);
			$text = 'Hey, I just created contact:<br><ul>';
			foreach ($data as $k => $v)
			    if('cc_print' !=  $k && 'cc_phone_clean' != $k)
				    $text .= '<li>' . ucfirst(str_replace('cc_', '', $k)) . ': ' . $v . '</li>';
			$text .= '</ul>';
			
			make_notes($client_id, $text, 'system', 0);
			
			//create a new job for client synchronization in qb			
			$cl_data = $this->CI->mdl_clients->get_client_by_id($client_id);
			pushJob('quickbooks/client/syncclientinqb', serialize(['id' => $client_id, 'qbId' => $cl_data->client_qb_id]));
			
			return $result;
		} else {
            return false;
        }
    }
    
    function update($id, $data) {

        $contact = $this->CI->mdl_clients->get_client_contact(['cc_id' => $id]);

        if(empty($contact)) {
            return false;
        }

		$wdata = ['cc_id' => $id];

		if ($contact['cc_email'] != $data['cc_email'])
			$data['cc_email_check'] = NULL;
            
        if($this->CI->mdl_clients->update_client_contact($data, $wdata)) {
		
			unset($data['cc_email_check']);
			$text = 'Hey, I just updated contact:<br><ul>';
			foreach ($data as $k => $v) {
				$nVal = $v;
				$oVal = $contact[$k];
	
				if ($k == 'cc_phone') {
					$nVal = numberTo($v);
					$oVal = numberTo($oVal);
				}
	
				if ($contact[$k] != $v && 'cc_print' !=  $k && 'cc_phone_clean' != $k)
					$text .= '<li>' . ucfirst(str_replace('cc_', '', $k)) . ': ' . $oVal . ' => ' . $nVal . '</li>';
			}
			$text .= '</ul>';
			make_notes($contact['cc_client_id'], $text, 'system', 0);	
			
			//create a new job for client synchronization in qb			
			$cl_data = $this->CI->mdl_clients->get_client_by_id($contact['cc_client_id']);
			pushJob('quickbooks/client/syncclientinqb', serialize(['id' => $contact['cc_client_id'], 'qbId' => $cl_data->client_qb_id]));
			
			return $contact['cc_client_id'];
		} else {
            return false;
        }
        
    }
    
    function delete($id) {
        $contact = $this->CI->mdl_clients->get_client_contact(['cc_id' => $id]);
        if ($contact['cc_print'] || empty($contact)){
           return false;
        } else {
            if($this->CI->mdl_clients->delete_client_contact($id)){
                
                //create a new job for client synchronization in qb
                $client_id = $contact['cc_client_id'];
                $cl_data = $this->CI->mdl_clients->get_client_by_id($client_id);
                pushJob('quickbooks/client/syncclientinqb', serialize(['id' => $client_id, 'qbId' => $cl_data->client_qb_id]));
        
                if ($contact) {						
                    unset($contact['cc_id'], $contact['cc_client_id'], $contact['cc_email_check'], $contact['cc_print']);
                    $text = 'Hey, I just deleted contact:<br><ul>';
                    foreach ($contact as $k => $v) {
                        $val = $v;
        
                        if ($k == 'cc_phone') {
                            $val = numberTo($v);
                        }
        
                        $text .= '<li>' . ucfirst(str_replace('cc_', '', $k)) . ': ' . $val . '</li>';
                    }
                    $text .= '</ul>';
                    make_notes($client_id, $text, 'system', 0);
                }
                
                return $contact['cc_client_id'];
                
            } else {
                return false;
            }
        }
    }
    
}