<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');


class Notebook extends MX_Controller
{

	function __construct()
	{
		parent::__construct();
		$this->_title = SITE_NAME;
		$this->load->model('mdl_user');
		$this->load->model('mdl_notebook');
	}

	function index()
	{
        if($this->session->userdata('IMP_CT') == 0 && $this->session->userdata('user_type') != "admin")
            show_404();
		$data['title'] = $this->_title . ' Notebook';
		$data['numbers'] = $this->mdl_notebook->get_all(); 
		$data['users'] = $this->mdl_user->get_usermeta(array('active_status' => 'yes', 'emp_phone <> ' => ''));
		$this->load->view('index', $data);
	}
	
	function save_number()
	{
        if($this->session->userdata('IMP_CT') == 0 && $this->session->userdata('user_type') != "admin")
		    show_404();
		$data['nb_name'] = $this->input->post('name');
		$data['nb_number'] = numberFrom($this->input->post('number'));
		
		$id = $this->mdl_notebook->insert($data);
		die(json_encode(array('status' => 'ok', 'id' => $id, 'name' => $data['nb_name'], 'number' => $data['nb_number'], 'tel' => numberTo($data['nb_number']))));
	}

	function ajax_send_sms()
	{
        $result = [
            'status' => 'ok',
            'msg' => 'Messages Was Sent Successfully'
        ];

		$userlist = $this->input->post('userlist');

		if (!$userlist) {
            $result = [
                'status' => 'error',
                'msg' => 'Users not provided'
            ];

            die(json_encode($result));
        }

        $this->load->driver('messages');
		$this->load->model('mdl_user');

		$signature = NULL;
		$userId = get_user_id();
		$userSenderObj = $this->mdl_user->get_usermeta(['users.id' => $userId]);

		if($userSenderObj) {
			$userSender = $userSenderObj->row();
			$signature = $userSender->firstname . ' ' . $userSender->lastname;
		}

        $sms = $this->input->post('sms_body', TRUE);

		if (!empty($sms)) {
            foreach ($userlist as $key => $value) {
                $userObj = $this->mdl_user->get_usermeta(['users.id' => $value]);
                if($userObj) {
                    $user = $userObj->row();
                    if (!empty($user->emp_phone)) {
                        $number = substr($user->emp_phone, 0, config_item('phone_clean_length'));

                        $sendResult = $this->messages->send($number, $sms, $signature);

                        if (isset($sendResult[0]) && is_array($sendResult[0]) && !array_key_exists('error', $sendResult[0])) {
                            $number = array_key_exists('number', $sendResult[0]) ? $sendResult[0]['number'] : $number;
                            $this->mdl_user->insert_users_sms([
                                'us_user_id' => $userId,
                                'us_recipient_user_id' => $value,
                                'us_recipient' => $number,
                                'us_body' => $sms,
                                'us_date' => date('Y-m-d H:i:s'),
                            ]);
                        } else {
                            $result['unsent'][] = $user->emp_phone;
                        }
                    }
                }
            }

            if (array_key_exists('unsent', $result)) {
                $result['msg'] = 'Some messages were not sent';

                if (count($userlist) === count($result['unsent'])) {
                    $result = [
                        'status' => 'error',
                        'msg' => 'Messages not sent'
                    ];
                }
            }
        } else {
            $result = [
                'status' => 'error',
                'msg' => 'No message.'
            ];
        }

		die(json_encode($result));
	}

	function ajax_delete_number()
	{
		//if($this->session->userdata('user_type') != "admin")
			//wshow_404();
		$id = $this->input->post('id');
		if($this->mdl_notebook->delete($id))
			die(json_encode(array('status' => 'ok')));
		else
			die(json_encode(array('status' => 'error')));
		
	}
}
