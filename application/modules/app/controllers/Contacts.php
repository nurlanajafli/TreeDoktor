<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Contacts extends APP_Controller
{

	function __construct()
	{
		parent::__construct();
        $this->load->model('mdl_clients');
		
		$this->load->library('form_validation');
		$this->load->library('Common/ContactsActions');
	}
	
	public function create() {		
		
		$this->form_validation->set_rules('cc_name', 'Contact name', 'trim|max_length[60]|required');
		
		$phone_check = trim($this->input->post('cc_phone'));
		$email_check = trim($this->input->post('cc_email'));
        $cc_print = (bool)($this->input->post('cc_primary'));
        $client_id = (int)$this->input->post('cc_client_id');

        if(!$client_id) {
            return $this->response([
                'status' => FALSE,
                'message' => 'Validation errors',
                'data' => ['client_id' => 'Client ID is required']
            ], 400);
        }

		if($phone_check == null || $phone_check == ''){
			if($email_check == null || $email_check == ''){
				return $this->response([
					'status' => FALSE,
					'message' => 'Validation errors',
					'data' => ['cc_name' => 'At least one of the fields: (phone or email) is required for the contact']
				], 400);
			}
		} else {
			if($email_check && !filter_var($email_check, FILTER_VALIDATE_EMAIL))
			{
				return $this->response([
					'status' => FALSE,
					'message' => 'Validation errors',
					'data' => ['cc_email' => 'Contact email is not valid']
				], 400);
			}
		}

		if ($this->form_validation->run() == true) {
			if($this->input->post('cc_phone') == null || $this->input->post('cc_phone') == ''){
				$phone_to_save = null;
				$phone_to_save_clean = null;
			} else {
				$phone_to_save = numberFrom($this->input->post('cc_phone'));
				$phone_to_save_clean = substr($phone_to_save, 0, config_item('phone_clean_length'));
			}

            if($cc_print) {
                $this->mdl_clients->update_client_contact(['cc_print' => 0], [
                    'cc_client_id' => $client_id
                ]);
            }

            if ($this->input->post('cc_email') !== null && !empty($this->input->post('cc_email'))) {
                $email = $this->input->post('cc_email');
                $email_exists = check_email_exists($email);
            }

			$data = [
				'cc_title' => !empty($this->input->post('cc_title')) ? $this->input->post('cc_title') : null,
				'cc_name' => $this->input->post('cc_name'),
				'cc_phone' => $phone_to_save,
				'cc_phone_clean' => $phone_to_save_clean,
				'cc_email' => $email ?? null,
                'cc_email_check' => $email_exists ?? null,
                'cc_print' => $cc_print
			];

			/*if (!$data['cc_title'])
				$data['cc_title'] = 'Title';*/

			$data['cc_client_id'] = $this->input->post('cc_client_id');
			if($result = $this->contactsactions->create($data)){
                $clientContacts = $this->mdl_clients->get_client_contacts(['cc_client_id' => $data['cc_client_id']]);
				$this->response([
					'status' => TRUE,
					'data' => ['client_id' => $data['cc_client_id'], 'contact_id' => $result, 'contacts' => $clientContacts]
				], 200);
			} else {
				$this->response([
					'status' => FALSE,
					'message' => 'Error creating a client\'s contact'
				], 400);
			}
		} else {
			$this->response([
				'status' => FALSE,
				'message' => 'Validation errors',
				'data' => $this->form_validation->error_array()
			], 400);
		}

	}

	public function update($id) {

		$this->form_validation->set_rules('cc_name', 'Contact name', 'trim|max_length[60]|required');

		$phone_check = trim($this->input->post('cc_phone'));
		$email_check = trim($this->input->post('cc_email'));
        $cc_print = (bool)($this->input->post('cc_primary'));

		if($phone_check == null || $phone_check == ''){
			if($email_check == null || $email_check == ''){
				return $this->response([
					'status' => FALSE,
					'message' => 'Validation errors',
					'data' => ['cc_name' => 'At least one of the fields: (phone or email) is required for the contact']
				], 400);
			} else {
				if($email_check && !filter_var($email_check, FILTER_VALIDATE_EMAIL))
				{
					return $this->response([
						'status' => FALSE,
						'message' => 'Validation errors',
						'data' => ['cc_email' => 'Contact email is not valid']
					], 400);
				}
			}
		}

		if ($this->form_validation->run() == true) {

			if($this->input->post('cc_phone') == null || $this->input->post('cc_phone') == ''){
				$phone_to_save = null;
				$phone_to_save_clean = null;
			} else {
				$phone_to_save = numberFrom($this->input->post('cc_phone'));
				$phone_to_save_clean = substr($phone_to_save, 0, config_item('phone_clean_length'));
			}

			if ($this->input->post('cc_email') !== null && !empty($this->input->post('cc_email'))) {
			    $email = $this->input->post('cc_email');
			    $email_exists = check_email_exists($email);
            }

			$data = [
				'cc_title' => !empty($this->input->post('cc_title')) ? $this->input->post('cc_title') : null,
				'cc_name' => $this->input->post('cc_name'),
				'cc_phone' => $phone_to_save,
				'cc_phone_clean' => $phone_to_save_clean,
				'cc_email' => $email ?? null,
				'cc_email_manual_approve' => $email_exists ?? null,
			];

            if($cc_print) {
                $data['cc_print'] = $cc_print;
            }

			/*if (!$data['cc_title'])
				$data['cc_title'] = 'Title';*/

			if($client_id = $this->contactsactions->update($id, $data)) {
                if($cc_print) {
                    $this->mdl_clients->update_client_contact(['cc_print' => 0], [
                        'cc_client_id' => $client_id,
                        'cc_id <>' => $id,
                    ]);
                }

				$clientContacts = $this->mdl_clients->get_client_contacts(['cc_client_id' => $client_id]);
				$this->response([
					'status' => TRUE,
					'data' => ['client_id' => $client_id, 'contact_id' => $id, 'contacts' => $clientContacts]
				], 200);
			} else {
				$this->response([
					'status' => FALSE,
					'message' => 'Error updating a client\'s contact'
				], 400);
			}
		} else {
			$this->response([
				'status' => FALSE,
				'message' => 'Validation errors',
				'data' => $this->form_validation->error_array()
			], 400);
		}

	}

    function change_primary_contact()
    {
        $cc_id = $this->input->post('cc_id');
        $cc_client_id = $this->input->post('cc_client_id');

        if (!$cc_id || !$cc_client_id){
            $this->response([
				'status' => FALSE,
				'message' => 'Error changing primary contact'
			], 400);
        } else {
			$this->mdl_clients->update_client_contact(['cc_print' => 0], ['cc_client_id' => $cc_client_id]);
			$this->mdl_clients->update_client_contact(['cc_print' => 1], ['cc_id' => $cc_id]);
			$this->response([
				'status' => TRUE,
				'data' => []
			], 200);
		}

    }

    function delete($id)
    {
        if ($id == null || $id == ''){
            $this->response([
				'status' => FALSE,
				'message' => 'No ID provided'
			], 400);
        } else {

			if($client_id = $this->contactsactions->delete($id)){
                $clientContacts = $this->mdl_clients->get_client_contacts(['cc_client_id' => $client_id]);
				$this->response([
					'status' => TRUE,
					'data' => ['contacts' => $clientContacts]
				], 200);
			} else {
				$this->response([
					'status' => FALSE,
					'message' => 'Error Deleting A Contact'
				], 400);
			}		
			
		}        
    }
    
}
