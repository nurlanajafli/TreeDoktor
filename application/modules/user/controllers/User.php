<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

use application\modules\user\models\User as UserModel;
use Twilio\Rest\Client;
use application\modules\mail\helpers\MailCheck;

class User extends MX_Controller
{

    /**
     * User controller
     */
    function __construct()
    {

        parent::__construct();

        if (!isUserLoggedIn()) {
            redirect('login');
        }

        if ($this->session->userdata('user_type') != "admin"
            && $this->session->userdata('UM') == 0
            && $this->uri->segment(2) != 'users_statistics'
            && $this->uri->segment(2) != 'ajax_get_users'
        ) {
            show_404();
        }

        $this->_title = SITE_NAME;

        //load all common models and libraries here;
        $this->load->helper('user');
        $this->load->model('mdl_user', 'user_model');
        $this->load->model('mdl_user_certificates', 'user_docs');
        $this->load->model('mdl_numbers');
        $this->load->model('mdl_users_management_orm', 'users_management');
    }

    /**
     * Clear user data
     * @param $id user_id
     */
    public function clear_user_data($id)
    {
        $this->load->helper('auth');
        if (request()->ajax()) {
            try {
                clearUserSessionData(true, $id);
            } catch (Exception $e) {
                return $this->response([
                    'error' => 'User data clear error',
                ]);
            }
            return $this->response([
                'success' => 'Sessions closed successfully',
            ]);
        }

        try {
            clearUserSessionData(true);
        } catch (Exception $e) {
            $this->session->set_flashdata('message', 'User data clear error');
            redirect('dashboard');
        }
        redirect('login');
    }

    /*
     * function index
     * shows login or list of user if user logged in;
     *
     * param null
     * returns html view
     *
     */

    public function index($status = 'active', $system = null)
    {
        $this->user_list($status, $system);
    }

    /*
     * function list user
     * lists all users with user type = user
     *
     * param null
     * returns html view
     *
     */

    public function user_list($status = 'active', $system = null)
    {
        $where['active_status'] = 'yes';
        if ($status == 'inactive') {
            $where['active_status'] = 'suspended';
        }
        if ($status == 'dismissed') {
            $where['active_status'] = 'no';
        }
        $where['system_user'] = 0;
        //if ($system == 'system')
        //$where['system_user'] = 1;
        $data['title'] = $this->_title . " - User Management";
        $data['page_title'] = "User Management";
        $data['page'] = "user/index";
        $data['status'] = $status;
        $data['system'] = $system;
        $data['user_row'] = $this->user_model->get_usermeta($where);


        $this->load->view('index', $data);
    }

    public function user_pdf($status = 'active', $system = null)
    {
        //ob_end_clean();
        $this->load->library('mpdf');
        $where['active_status'] = 'yes';
        if ($status == 'inactive') {
            $where['active_status'] = 'suspended';
        }
        if ($status == 'dismissed') {
            $where['active_status'] = 'no';
        }
        $where['system_user'] = 0;
        //if ($system == 'system')
        //$where['system_user'] = 1;
        $where['user_active_employee'] = 1;
        $data['title'] = $this->_title . " - User Management";

        $data['status'] = $status;
        $data['system'] = $system;
        $data['user_row'] = $this->user_model->get_usermeta($where);

        $data['pdf'] = true;
        $html = $this->load->view('list_user_pdf', $data, true);

        $this->mpdf->WriteHTML($html);
        $file = "user.pdf";
        $this->mpdf->Output('', 'I');
    }
    /*
     * function user add form
     *
     * param $id = null
     * returns html view
     *
     */

    /*public function user_add($msg = NULL)
    {

        if($msg && $msg != '')
            $data['msg'] = message('alert', $msg);

        $data['title'] = $this->_title . " - User Management";
        $data['page_title'] = "User Management";
        $data['page'] = "user/form";
        $data['menus'] = "user/menu";
        $data['module_opt'] = $this->user_model->get_module_options();
        $data['doc_tpl'] = json_encode(['html' => $this->load->view('form/certificate_form', ['number' => 0], TRUE)]);
        $this->load->view('form', $data);
    }*/

    public function user_add($user_type = null, $msg = null)
    {
        if ($user_type != 'support' && $user_type != 'field' && !$this->input->post()) {
            show_404();
        }

        if ($msg && $msg != '') {
            $data['msg'] = message('alert', $msg);
        }


        $data['title'] = $this->_title . " - User Management";
        $data['page_title'] = "User Management";
        $data['page'] = "user/form";
        $data['menus'] = "user/menu";
        $data['user_type'] = $user_type;
        $data['module_opt'] = $this->user_model->get_module_options();
        $data['doc_tpl'] = json_encode(['html' => $this->load->view('form/certificate_form', ['number' => 0], true)]);
        $this->load->view('form', $data);
    }

    /*
     * function showform
     * is called both for add and edit function
     *
     * param $id = null
     * returns html view
     *
     */

    public function user_update($id = null, $msg = null)
    {

        if (!$id || ($this->session->userdata('user_type') != "admin" && $this->session->userdata('UM') == 0)) {
            show_404();
        }


        if ($msg && $msg != '') {
            $data['msg'] = message('alert', $msg);
        }

        $data['edit'] = $id;
        $data['title'] = $this->_title . " - User Management";
        $data['page_title'] = "User Management";
        $data['page'] = "user/form";
        $data['menus'] = "user/menu";
        $data['user_module'] = $this->user_model->get_userModules($id);

        $data['module_opt'] = $this->user_model->get_module_options();
        $data['user_row'] = $this->user_model->get_usermeta(['users.id' => $id])->row();

        if (!$data['user_row'] || ($data['user_row']->system_user == 1 && $this->session->userdata('system_user') != 1)) {
            show_404();
        }

        $data['docs'] = $this->user_docs->get_many_by(['us_user_id' => $id]);
        $number = count($data['docs']);
        $data['doc_tpl'] = json_encode([
            'html' => $this->load->view('form/certificate_form', ['num' => $number + 2], true)
        ]);

        $level = $this->user_model->get_user('twilio_level', 'twilio_level IS NOT NULL', 'twilio_level DESC');
        $data['max_level'] = 0;
        if ($level && $level->result()) {
            $data['max_level'] = $level->result()[0]->twilio_level;
        }

        $data['deduction_state'] = (bool) config_item('payroll_deduction_state');

        if ($this->session->userdata('user_type') != "admin") {
            if ($data['user_row']->user_type == 'admin') {
                show_404();
            }
        }

        if (config_item('default_mail_driver') === 'amazon' && isset($data['user_row']->user_email)) {

            $data['current_email_identity_status'] = 'Unverified';
            $data['identity_id'] = NULL;

            if ($emailCheck = (new MailCheck())->checkEmailIdentityStatus($data['user_row']->user_email)) {
                $verificationData = json_decode($emailCheck->verificationAttributes, true);
                if (true === array_key_exists('VerificationStatus', $verificationData)) {
                    $data['current_email_identity_status'] = $verificationData['VerificationStatus'];
                    $data['identity_id'] = $emailCheck->identity_id;
                }
            }
        }

        $this->load->view('form', $data);
    }

    /*
     * function save
     * inserts or updated the user details
     *
     * param $id = null
     * returns bool or error on failure
     *
     */

    public function save($id = null)
    {
        if ($this->session->userdata('user_type') != "admin" && $this->session->userdata('UM') != 1) {
            show_404();
        }
        /* validation */

        $this->config->load('form_validation');
        $this->load->library('form_validation');
        $this->load->model('mdl_employees');

        $module_opt = $this->user_model->get_module_options();

        $rules = config_item('create_user');
        if ($id) {
            $user = $this->user_model->getUserById($id);
            if(isset($user[0])) {
                $user_row = $user[0];
            }
            $rules = config_item('update_user');
        }

        if (is_support((isset($user_row)) ? $user_row : [])) {
            $rules[] = ['field' => 'userEmail', 'label' => 'Email', 'rules' => 'required'];
            $rules[] = ['field' => 'txtphone', 'label' => 'Phone', 'rules' => 'required'];
        }

        if (isAdmin() && isset($user_row) && $user_row['user_type'] !== 'admin') {
            foreach ($module_opt as $opt) {
                $rules[] = [
                    'field' => $opt->module_id,
                    'label' => $opt->module_desc,
                    'rules' => 'required'
                ];
            }
        }

        /*if(!isAdmin())
            $rules[] = ['field' => 'selectusertype', 'label' => 'User Type', 'rules' => ''];*/

        if ($this->input->post('select_active_status') && ($this->input->post('select_active_status') == 'yes' || $this->input->post('select_active_status') == 'suspended')) {
            /*$rules[] = ['field'=>'txtcity', 'label'=>'City', 'rules'=>''];
            $rules[] = ['field'=>'txtaddress1', 'label'=>'Address line 1', 'rules'=>''];*/
            $rules[] = ['field' => 'txtemail', 'label' => 'Login', 'rules' => 'required'];//|is_unique_update[users.emailid@id@'.$id.']
        }

        if ($this->input->post('extention') && $id) {
            $extCheck = $this->mdl_numbers->get_extention_number(array('extention_key' => $this->input->post('extention')));
            if (($extCheck && $extCheck->extention_user_id != $id) || !$extCheck) {
                $rules[] = [
                    'field' => 'extention',
                    'label' => 'Extention',
                    'rules' => 'is_unique[ext_numbers.extention_key]'
                ];
            }
        }
        /*if($this->input->post('select_active_status') && ($this->input->post('select_active_status') == 'yes' || $this->input->post('select_active_status') == 'suspended'))
            $rules[] = ['field'=>'txtposition', 'label'=>'Position', 'rules'=>''];*/

        $this->form_validation->set_rules($rules);

        $msg = '';
        $data['is_require_payment_details'] = $this->input->post('is_require_payment_details');
        //var_dump($data['is_require_payment_details']);
        //die;

        if (!$id && $this->form_validation->run() == false) {
            foreach ($this->form_validation->error_array() as $k => $v) {
                $msg .= '<br>' . $v;
            }

            return $this->user_add(null, $msg);
        }


        if ($id && $this->form_validation->run() == false) {
            foreach ($this->form_validation->error_array() as $k => $v) {
                $msg .= $v . '<br>';
            }

            return $this->user_update($id, $msg);
        }

        $now_gmt_time = now(); // GMT time
        $now_time = mdate('%Y-%m-%d %H:%i:%s', $now_gmt_time->timestamp);

        $data['emailid'] = $this->input->post('txtemail');
        $data['firstname'] = $this->input->post('txtfirstname');
        $data['is_appointment'] = $this->input->post('is_appointment');

        $pass = $this->input->post('txtpassword');
        if ($pass) {
            $data['password'] = md5($pass);
        }

        if ($this->session->userdata('user_type') == "admin") {
            $data['user_type'] = $this->input->post('selectusertype');
        } else {
            $data['user_type'] = 'user';
        }
        $data['active_status'] = $this->input->post('select_active_status');
        $data['lastname'] = $this->input->post('txtlastname');
        $data['color'] = $this->input->post('color');
        $data['user_email'] = $this->input->post('userEmail');
        $data['user_signature'] = $this->input->post('user_signature');
        if ($this->input->post('worker_type') !== false) {
            $data['worker_type'] = $this->input->post('worker_type');
        }

		$data['is_tracked'] = 0;
		if($this->input->post('is_tracked') != null && $this->input->post('is_tracked') != ''){
			$data['is_tracked'] = 1;
		}

        $data['during'] = $this->input->post('during') != null && $this->input->post('during') != ''
          ? $this->input->post('during')
          : 0;

        $metadata['address1'] = $this->input->post('txtaddress1');
        $metadata['address2'] = $this->input->post('txtaddress2');
        $metadata['city'] = $this->input->post('txtcity');
        $metadata['state'] = $this->input->post('txtstate');
        $metadata['country'] = '';//$this->input->post('txtcountry');
        //store module_opt in to array
        if (isAdmin()) {
            foreach ($module_opt as $opt) {
                $moduledata[$opt->id]['module_id'] = $opt->module_id;
                $moduledata[$opt->id]['module_status'] = $this->input->post($opt->module_id);
            }
        }

        if ($data['active_status'] == 'yes' || $data['active_status'] == 'suspended') {
            $data['user_active_employee'] = 1;
            $employee = $this->employee_to_user();

            if ($employee['errors'] && $id) {
                return $this->user_update($id, $employee['msg']);
            }
            if ($employee['errors'] && !$id) {
                return $this->user_add(null, $employee['msg']);
            }
        }

        //upload function
        if ($_FILES['picture']['size'] > 0) {
            /* upload code */
            $exts = pathinfo($_FILES['picture']['name'], PATHINFO_EXTENSION);
            $data['picture'] = make_filename($exts);
            $size = $_FILES['picture']['size'] / 1024;

            $this->load->config('upload');
            if (array_search($exts,
                    explode("|", config_item('allowed_types'))) == false || $size > (int)config_item('max_size')) {
                $mess = message('alert', 'File type or file size is not valid.');
                $this->session->set_flashdata('user_message', $mess);
                redirect(base_url('user/user_update/' . $id));
            }

            $this->load->library('image_lib');
            $config['source_image'] = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'tmp_user_file.' . $exts;
            move_uploaded_file($_FILES['picture']['tmp_name'], $config['source_image']);

            $config['image_library'] = 'gd2';
            $config['new_image'] = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'tmp_user_file_thumb.' . $exts;
            $config['create_thumb'] = false;
            $config['maintain_ratio'] = true;
            $config['width'] = 230;
            $config['height'] = 230;

            $this->image_lib->initialize($config);
            $this->image_lib->resize();

            bucket_move($config['new_image'], PICTURE_PATH . $data['picture']);
            if ($id && $this->session->userdata('user_id') == $id) {
                $this->session->set_userdata('user_pic', $data['picture']);
            }

            unlink($config['source_image']);
            unlink($config['new_image']);

            if (!empty($user) && is_bucket_file(PICTURE_PATH . $user[0]['picture'])) {
                bucket_unlink(PICTURE_PATH . $user[0]['picture']);
            }
        }

        if (!$id) {

            $data['added_on'] = $now_time;

            if (isset($employee)) {
                $data['user_emp_id'] = $employee['id'];
            }

            $iData = $data;
            unset($iData['letters_templates'], $iData['sms_templates']);
            $insert = $this->user_model->insert_user($iData);
            if (isset($employee)) {
                $this->mdl_employees->update_employee(array('emp_user_id' => $insert),
                    array('employee_id' => $employee['id']));
            }

            if ($insert) {

                if (intval($this->input->post('extention'))) {
                    $extention['extention_key'] = $this->input->post('extention');
                    $extention['extention_user_id'] = $insert;
                    $this->mdl_numbers->insert($extention);
                }

                $metadata['user_id'] = $insert;
                if (isAdmin()) {
                    foreach ($module_opt as $opt) {
                        $moduledata[$opt->id]['user_id'] = $insert;
                    }
                }

                $insert_meta = $this->user_model->insert_usermeta($metadata);
                if (isAdmin()) {
                    foreach ($moduledata as $row) {
                        $this->user_model->insert_userModules($row);
                    }
                }

                if ($insert_meta) {
                    $mess = message('success', 'User Added!');
                    $this->session->set_flashdata('user_message', $mess);
                }
                if ($_FILES['us_photo']) {
                    $this->user_sertificates($insert);
                }

                $worker_type = $data['worker_type'];
                $this->users_management->add_user_event([
                    'um_user_id' => $insert,
                    'um_action' => 'create',
                    'um_action_value' => "Worker type: $worker_type"
                ]);
            }
        } else {

            $data['updated_on'] = $now_time;
            $where_data['user_meta.user_id'] = $id;
            if (isAdmin()) {
                foreach ($module_opt as $opt) {
                    $moduledata[$opt->id]['user_id'] = $id;
                }
            }
            if (isset($_FILES['us_photo'])) {
                $this->user_sertificates($id);
            }
            $iData = $data;
            unset($iData['letters_templates'], $iData['sms_templates']);
            $this->user_model->update_user($iData, array('users.id' => $id));
            if ($iData['active_status'] == 'no' || $iData['active_status'] == 'suspended') {
                clearUserSessionData(true, $id);
            }
            if ($data['active_status'] == 'no') {
                $agentData['prop'] = 'false';
                $agentData['id'] = $id;
                $agentData['name'] = 'list';
                $this->edit_agent($agentData);
                $agentData['name'] = 'support';
                $this->edit_agent($agentData);
                $agentData['name'] = 'agent';
                $this->edit_agent($agentData);
            }
            $extCheck = $this->mdl_numbers->get_extention_number(array('extention_user_id' => $id));

            if (intval($this->input->post('extention'))) {
                $extention['extention_key'] = $this->input->post('extention');
                if ($extCheck) {
                    $this->mdl_numbers->update_by(array('extention_user_id' => $id), $extention);
                } else {
                    $extention['extention_user_id'] = $id;
                    $this->mdl_numbers->insert($extention);
                }
            } else {
                if ($extCheck) {
                    $this->mdl_numbers->delete_by(array('extention_user_id' => $id));
                }
            }

            $update = $this->user_model->update_usermeta($metadata, $where_data);
            if (isAdmin()) {
                foreach ($moduledata as $row) {
                    $check_module = $this->user_model->checkModuleExsist($id, $row['module_id']);

                    if ($check_module->num_rows()) {
                        $this->user_model->update_userModules($row, $id, $row['module_id']);
                    } else {
                        $this->user_model->insert_userModules($row);
                    }
                }
            }

            $itUser = $this->user_model->get_user('*', 'users.id = ' . $id);

            $userRow = $itUser->row();

            $name = strtolower($data['firstname']);
            $lastname = explode(' ', $data['lastname']);
            foreach ($lastname as $k => $v) {
                $name .= $v;
            }

            if ($data['active_status'] == 'yes' || $data['active_status'] == 'suspended') {
                $this->mdl_employees->update_employee(array('emp_status' => 'current'), array('emp_user_id' => $id));
            } else {
                $this->mdl_employees->update_employee(array('emp_status' => 'past'), array('emp_user_id' => $id));
            }

            $worker_type = $data['worker_type'];
            $this->users_management->add_user_event([
                'um_user_id' => $id,
                'um_action' => 'update',
                'um_action_value' => "Worker type: $worker_type"
            ]);

            $mess = message('success', 'User Updated!');
            $this->session->set_flashdata('user_message', $mess);
        }

        redirect('user/user_list');
    }

    /*
     * function delete
     * deletes the user
     *
     * param $id
     * returns null / redirects the user
     *
     */

    public function user_delete($id)
    {
        show_404();
        if ($id) {
            $data['user_row'] = $this->user_model->get_usermeta(['users.id' => $id])->row();
            if ($this->session->userdata('user_type') != "admin") {
                if ($data['user_row']->user_type == 'admin') {
                    show_404();
                }
            }

            $delete = $this->user_model->delete_user($id);
            if ($delete) {
                $this->users_management->add_user_event([
                    'um_user_id' => $id,
                    'um_action' => 'delete',
                    'um_action_value' => "User: $id"
                ]);

                $delete = $this->user_model->delete_userModules($id);
                $mess = message('success', 'User Deleted!');
                $this->session->set_flashdata('user_message', $mess);

                redirect('user/user_list');
            }
        }
    }


    function loginAs($userId = null)
    {
        if ($this->session->userdata('user_type') != "admin") {
            show_404();
        }
        $data['id'] = $userId;
        $select = '';
        $user_log = $this->user_model->get_user($select, $data);
        if (!$user_log) {
            show_404();
        }
        $user = $user_log->row();
        //var_dump($user); die;
        if ($user->active_status == 'yes') {
            switch ($user->user_type) {
                case "admin":
                    $data = array(
                        'user_id' => $user->id,
                        'user_type' => 'admin',
                        'firstname' => $user->firstname,
                        'lastname' => $user->lastname,
                        'rate' => $user->rate,
                        'user_pic' => $user->picture,
                        'user_last_login' => $user->last_login,
                        'user_logged_in' => true,
                        'worker_type' => $user->worker_type,
                        'chatusername' => $user->firstname . ' ' . $user->lastname,
                        'twilio_worker_id' => $user->twilio_worker_id,
                        'twilio_support' => $user->twilio_support,
                        'twilio_workspace_id' => $user->twilio_workspace_id,
                        'system_user' => $user->system_user,
                        //'username' => $user->id,
                    );

                    if ($this->session->userdata('redirect_page') && !strpos($this->session->userdata('redirect_page'),
                            'login') && !strpos($this->session->userdata('redirect_page'), 'employee')) {
                        $redirect_page = $this->session->userdata('redirect_page');
                    } else {
                        $redirect_page = 'dashboard';
                    }
                    break;
                case "user":
                    /* get user modu;es status*/

                    $data = array(
                        'user_id' => $user->id,
                        'user_type' => 'user',
                        'firstname' => $user->firstname,
                        'lastname' => $user->lastname,
                        'rate' => $user->rate,
                        'user_pic' => $user->picture,
                        'user_last_login' => $user->last_login,
                        'user_logged_in' => true,
                        'worker_type' => $user->worker_type,
                        'chatusername' => $user->firstname . ' ' . $user->lastname,
                        'twilio_worker_id' => $user->twilio_worker_id,
                        'twilio_workspace_id' => $user->twilio_workspace_id,
                        //'username' => $user->id
                    );
                    $user_module = $this->user_model->get_userModules($user->id);
                    if (isset($user_module) && !empty($user_module)) {
                        foreach ($user_module as $row) {
                            $data[$row->module_id] = $row->module_status;
                        }
                    }

                    break;
            };
            $this->session->set_userdata($data);
        }
        redirect(base_url('dashboard'));
    }

    function employee_to_user()
    {
        $this->load->model('mdl_employees');
        $this->load->library('form_validation');
        $this->config->load('form_validation');
        $rules = config_item('employee_to_user');
        $this->form_validation->set_rules($rules);
        $res['errors'] = false;

        //echo '<pre>'; var_dump($rules, $_POST); die;

        $id = false;
        if ($this->input->post('employee_id')) {
            $id = $this->input->post('employee_id');
        }


        if ($this->form_validation->run() == false) {
            $msg = '';
            foreach ($this->form_validation->error_array() as $k => $v) {
                $msg .= $v . '<br>';
            }
            $res['errors'] = true;
            $res['msg'] = $msg;
            return $res;
        }

        $now_gmt_time = now(); // GMT time
        $now_time = mdate('%Y-%m-%d %H:%i:%s', $now_gmt_time->timestamp);

        $emp['emp_name'] = $this->input->post('txtfirstname', true) . ' ' . $this->input->post('txtlastname', true);
        $emp['emp_email'] = $this->input->post('userEmail');
        $emp['emp_username'] = $this->input->post('txtemail');
        $emp['emp_position'] = $this->input->post('txtposition', true);
        $emp['emp_address1'] = $this->input->post('txtaddress1', true) ?? null;
        $emp['emp_address2'] = $this->input->post('empaddress2', true) ?? null;
        $emp['emp_city'] = $this->input->post('txtcity', true);
        $emp['emp_phone'] = numberFrom($this->input->post('txtphone', true));
        $emp['emp_state'] = $this->input->post('empstate', true);
        $emp['emp_sin'] = $this->input->post('txtsin', true);

        if ($this->session->userdata('user_type') == "admin") {
            $emp['emp_hourly_rate'] = (float)$this->input->post('txthourlyrate');
        }

        if ($this->session->userdata('user_type') == "admin") {
            $emp['emp_yearly_rate'] = (float)$this->input->post('txtyearlyrate');
        }
        $emp['emp_message_on_account'] = $this->input->post('area_account_message');
        $emp['emp_feild_worker'] = isset($_POST['is_feild_worker'])?$_POST['is_feild_worker']:0;
        $emp['emp_field_estimator'] = isset($_POST['is_field_estimator'])?$_POST['is_field_estimator']:0;
        //$emp['emp_driver'] = $this->input->post('driver');
        //$emp['emp_climber'] = $this->input->post('climber');
        //$emp['emp_ground'] = $this->input->post('emp_ground');
        //$emp['emp_technique'] = $this->input->post('emp_technique');
        $emp['emp_start_time'] = $this->input->post('txtstarttime');
        $emp['emp_type'] = $this->input->post('txttype');
        $emp['emp_check_work_time'] = $this->input->post('emp_check_work_time');

        $emp['deductions_state'] = $this->input->post('deductions_state') ?: '';
        $emp['deductions_amount'] = $this->input->post('deductions_amount');
        $emp['deductions_desc'] = $this->input->post('deductions_desc');

        $emp['emp_sex'] = $this->input->post('txtsex');
        $emp['emp_birthday'] = $this->input->post('txtbirthday', false, null);
        $emp['emp_date_hire'] = $this->input->post('txthiredate', false, null);
        if ($this->input->post('txthiredate', false, null) == '0000-00-00' || $this->input->post('txthiredate', false,
                null) == '') {
            $emp['emp_date_hire'] = null;
        }
        if ($this->input->post('txtbirthday', false, null) == '0000-00-00' || $this->input->post('txtbirthday', false,
                null) == '') {
            $emp['emp_birthday'] = null;
        }
        $emp['emp_pay_frequency'] = $this->input->post('txtfrequency');
        $emp['emp_user_id'] = $id;
        if ($id) {
            $emp_id = $this->mdl_employees->get_employee('employee_id', ['emp_user_id' => $id]);
        }

        if (!isset($emp_id) || !$emp_id) {
            $emp['added_on'] = $now_time;
            $res['id'] = $this->mdl_employees->insert_employee($emp);
        } else {
            $emp['updated_on'] = $now_time;
            $where_data['emp_user_id'] = $id;
            $res['update'] = $this->mdl_employees->update_employee($emp, $where_data);
        }
        return $res;
    }

    public function admin_required($str)
    {
        if ($this->session->userdata('user_type') != "admin") {
            return true;
        }

        if (!(float)$str) {
            $this->form_validation->set_message('admin_required', 'The %s field is required');
            return false;
        }
        return true;
    }

    function edit_agent($data = [])
    {

        $id = isset($data['id']) ? $data['id'] : $this->input->post('id');
        $prop = isset($data['prop']) ? $data['prop'] : $this->input->post('prop');
        $fieldName = isset($data['name']) ? $data['name'] : $this->input->post('name');

        $this->config->load('twilio');
        $client = new Client(config_item('accountSid'), config_item('authToken'));
        $user = $this->user_model->get_usermeta(['users.id' => intval($id)])->row();

        $sid = null;
        if ($fieldName == 'list' && $prop == 'true') {
            $this->user_model->update_user(['twilio_user_list' => 1], ['id' => intval($id)]);
        } elseif ($fieldName == 'list' && $prop == 'false') {
            $this->user_model->update_user(['twilio_user_list' => null], ['id' => intval($id)]);
        } elseif ($fieldName == 'support' && $prop == 'true') {
            $max_level = $this->user_model->get_max('twilio_level',
                array('twilio_workspace_id' => config_item('workspaceSid')))->row();

            if (isset($max_level->twilio_level)) {
                $new_level = $max_level->twilio_level + 1;
            } else {
                $new_level = 0;
            }
            $name = 'agent_' . $user->id;

            if (!$user->twilio_worker_id) {

                $worker = $client->taskrouter
                    ->workspaces(config_item('workspaceSid'))
                    ->workers
                    ->create(
                        $user->emp_name,
                        array('attributes' => '{"skills":["support"],"languages":["en"],"contact_uri":"' . $name . '", "level":' . $new_level . '}')
                    );
                $sid = $worker->sid;
                $this->user_model->update_user([
                    'twilio_support' => 1,
                    'twilio_worker_id' => $sid,
                    'twilio_workspace_id' => config_item('workspaceSid'),
                    'twilio_level' => $new_level
                ], ['id' => intval($id)]);
            } else {
                $worker = $client->taskrouter
                    ->workspaces(config_item('workspaceSid'))
                    ->workers($user->twilio_worker_id)
                    ->update(
                        array('attributes' => '{"skills":["support"],"languages":["en"],"contact_uri":"' . $name . '", "level":' . $new_level . '}')
                    );

                $this->user_model->update_user(['twilio_support' => 1, 'twilio_level' => $new_level],
                    ['id' => intval($id)]);
            }

        } elseif ($fieldName == 'support' && $prop == 'false') {
            if ($user->twilio_worker_id) {
                $this->user_model->update_user(['twilio_support' => 0, 'twilio_level' => null], ['id' => intval($id)]);
                $name = 'agent_' . $user->id;

                $worker = $client->taskrouter
                    ->workspaces(config_item('workspaceSid'))
                    ->workers($user->twilio_worker_id)
                    ->update(
                        array('attributes' => '{"languages":["en"],"contact_uri":"' . $name . '"}')
                    );
            }
        } elseif ($prop == 'true') {
            if (!$user->twilio_worker_id) {
                $name = 'agent_' . $user->id;

                $max_level = $this->user_model->get_max('twilio_level',
                    ['twilio_workspace_id' => config_item('workspaceSid')]
                )->row();

                if (isset($max_level->twilio_level)) {
                    $new_level = $max_level->twilio_level + 1;
                } else {
                    $new_level = 0;
                }

                if ($user->twilio_support == 1) {
                    $attributes = '{"languages":["en"],"contact_uri":"' . $name . '","skills":["support"],"level":' . $new_level . '}';
                } else {
                    $attributes = '{"languages":["en"],"contact_uri":"' . $name . '"}';
                }
                $worker = $client->taskrouter
                    ->workspaces(config_item('workspaceSid'))
                    ->workers
                    ->create($user->emp_name, ['attributes' => $attributes]);
                $sid = $worker->sid;
                $this->user_model->update_user([
                    'twilio_worker_id' => $sid,
                    'twilio_worker_agent' => 1,
                    'twilio_workspace_id' => config_item('workspaceSid'),
                    'twilio_level' => $new_level
                ], ['id' => intval($id)]);
            } else {
                $this->user_model->update_user(['twilio_worker_agent' => null], ['id' => intval($id)]);
            }
        } else {
            $this->user_model->update_user([
                'twilio_worker_agent' => null,
                'twilio_worker_id' => null,
                'twilio_workspace_id' => null
            ], ['id' => intval($id)]);
            if ($user->twilio_worker_id) {
                $this->config->load('twilio');
                $workers = $client->taskrouter
                    ->workspaces(config_item('workspaceSid'))
                    ->workers($user->twilio_worker_id)
                    ->update(array('activitySid' => config_item('offlineActivitySid')));
                $client->taskrouter->workspaces(config_item('workspaceSid'))->workers($user->twilio_worker_id)->delete();
            }
        }


    }

    function agents($support = null)
    {

        $data['support'] = false;

        $data['title'] = $this->_title . " - User Management";
        $data['page_title'] = "User Management";
        $data['page'] = "user/index";

        $wdata = 'twilio_workspace_id = "' . config_item('workspaceSid') . '" AND twilio_worker_id IS NOT NULL AND twilio_worker_id <> " " AND twilio_level IS NOT NULL';
        if ($support) {
            $data['support'] = true;
            $wdata .= ' AND twilio_support = 1';
        }
        $order[] = 'twilio_level';
        $order[] = 'ASC';
        $agents = $this->user_model->get_usermeta($wdata, array(), $order);
        $wdata = 'twilio_workspace_id = "' . config_item('workspaceSid') . '" AND twilio_worker_id IS NOT NULL AND twilio_worker_id <> " " AND twilio_level IS NULL';
        if ($support) {
            $wdata .= ' AND twilio_support = 1';
        }
        $agents_without_level = $this->user_model->get_usermeta($wdata, array(), $order);

        $data['user_row'] = [];
        if ($agents && $agents->num_rows()) {
            $data['user_row'] = $agents->result_array();
        }
        if ($agents_without_level && $agents_without_level->num_rows()) {
            $data['user_row'] = array_merge($data['user_row'], $agents_without_level->result_array());
        }


        $this->load->view('agents', $data);
    }

    function change_level()
    {
        $data['status'] = 'error';
        $list = $this->input->post('data');
        $client = new Client(config_item('accountSid'), config_item('authToken'));

        if (!empty($list)) {
            foreach ($list as $k => $v) {

                $user = $this->user_model->get_user('*', 'id = ' . $v['id']);
                if (!empty($user)) {
                    $user = $user->result()[0];
                }
                $date['twilio_support'] = 0;
                if ($user->twilio_support) {
                    $date['twilio_support'] = 1;
                }
                $name = 'agent_' . $user->id;

                $date['twilio_level'] = $k;
                if ($user->twilio_worker_id) {
                    $this->user_model->update_user($date, array('id' => $v['id']));

                    if ($date['twilio_support']) {
                        $attr['attributes'] = '{"skills":["support"],"languages":["en"],"contact_uri":"' . $name . '", "level":' . $date['twilio_level'] . '}';
                    } else {
                        $attr['attributes'] = '{"languages":["en"],"contact_uri":"' . $name . '", "level":' . $date['twilio_level'] . '}';
                    }
                    $worker = $client->taskrouter
                        ->workspaces(config_item('workspaceSid'))
                        ->workers($user->twilio_worker_id)
                        ->update(
                            $attr
                        );
                } else {
                    if ($date['twilio_support']) {
                        $attr['attributes'] = '{"skills":["support"],"languages":["en"],"contact_uri":"' . $name . '", "level":' . $date['twilio_level'] . '}';
                    } else {
                        $attr['attributes'] = '{"languages":["en"],"contact_uri":"' . $name . '", "level":' . $date['twilio_level'] . '}';
                    }
                    $worker = $client->taskrouter
                        ->workspaces(config_item('workspaceSid'))
                        ->workers
                        ->create(
                            $user->firstname . ' ' . $user->lastname,
                            $attr
                        );

                    $date['twilio_worker_id'] = $worker->sid;
                    $date['twilio_worker_agent'] = null;
                    $date['twilio_workspace_id'] = config_item('workspaceSid');
                    $this->user_model->update_user($date, array('id' => $v['id']));
                    unset($date['twilio_worker_id'], $date['twilio_worker_agent'], $date['twilio_workspace_id']);
                }
            }

        }
    }


    function ajax_check_autologout()
    {
        $time = $this->session->_get_time();
        $last_active = $this->session->userdata('last_activity');
        die(json_encode(['no_activity' => ($time - $last_active)]));
    }

    function chage_duty()
    {
        $id = $this->input->post('id');
        $this->user_model->update_user(array('duty' => 0), array('duty' => 1));
        $this->user_model->update_user(array('duty' => 1), array('id' => $id));
    }


    function chage_worker_type()
    {
        $this->load->model('mdl_employees');
        $id = $this->input->post('id');
        $val = intval($this->input->post('val'));

        $this->users_management->add_user_event([
            'um_user_id' => $id,
            'um_action' => 'change_type',
            'um_action_value' => "Worker type: $val"
        ]);

        $this->user_model->update_user(array('worker_type' => $val), array('id' => $id));
        $emp['emp_feild_worker'] = $val == '1' ? '1' : '0';
        if ($val) {
            $emp['emp_field_estimator'] = '0';
        }
        $this->mdl_employees->update_employee($emp, ['emp_user_id' => $id]);
    }

    function user_sertificates($id)
    {
        $files = reArrayFiles($_FILES['us_photo'], 'us_photo');

        $post = $this->input->post();

        $this->load->library('upload');
        $config['allowed_types'] = '*';
        $config['max_size'] = config_item('max_size');
        $config['overwrite'] = true;
        $config['remove_spaces'] = false;
        $config['upload_path'] = 'uploads/user_docs/' . $id . '/';

        $path = $config['upload_path'];
        //@chmod($path, 0777);

        $this->upload->initialize($config);
        $old_docs = [];
        if (isset($post['us_id'])) {
            $old_docs = $this->user_docs->get_many_by('us_user_id = ' . $id . ' AND us_id NOT IN (' . implode(", ",
                    $post['us_id']) . ')');
        }

        if ($old_docs && !empty($old_docs)) {
            foreach ($old_docs as $k => $v) {
                bucket_unlink($config['upload_path'] . $v->us_photo);
                $this->user_docs->delete($v->us_id);
            }
        }

        foreach ($_FILES as $k => $v) {
            $data = [];
            $data['us_user_id'] = $id;
            $data['us_name'] = $post['us_name'][$k];
            $data['us_exp'] = $post['us_exp'][$k];

            $data['us_notification'] = $post['us_notification'][$k];
            //$this->user_docs->delete_by(['us_user_id' => $id]);
            if (isset($v['size']) && $v['size'] > 0) {
                if (isset($post['us_id'][$k])) {
                    $last_doc = $this->user_docs->get($post['us_id'][$k]);
                    bucket_unlink($config['upload_path'] . $last_doc->us_photo);
                    $a = $this->upload->do_upload($k, true, true);
                    $data['us_photo'] = $this->upload->file_name;
                    $this->user_docs->update($post['us_id'][$k], $data);
                } else {
                    $a = $this->upload->do_upload($k, true, true);
                    $data['us_photo'] = $this->upload->file_name;
                    $this->user_docs->insert($data);
                }
            } elseif (isset($post['us_id'][$k])) {
                $this->user_docs->update($post['us_id'][$k], $data);
            }

        }
        return true;
    }

    function users_statistics()
    {
        redirect('business_intelligence/users_statistics');
    }

    public function ajax_get_users()
    {
        $page = request('page', 1);
        $sort = request('sort', ['firstname', 'asc']);
        /** @var \Illuminate\Database\Query\Builder $usersQuery */
        $usersQuery = UserModel::with(['employee'])->where('system_user', '=', 0)
            ->where('active_status', '=', 'yes');
        if (!empty(request('query', ""))) {
            $usersQuery->where('firstname', 'like', '%' . request('query') . '%');
            $usersQuery->orWhere('lastname', 'like', '%' . request('query') . '%');
        }
        /** @var \Illuminate\Pagination\LengthAwarePaginator $users */
        $users = $usersQuery->orderBy(...$sort)
            ->orderBy('id', 'desc')
            ->paginate(20, ['*'], 'page', $page);
        return $this->successResponse($users->toArray());
    }
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */
