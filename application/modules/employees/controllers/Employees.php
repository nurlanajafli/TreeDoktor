<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

use Carbon\Carbon;
use application\modules\user\models\User;

class Employees extends MX_Controller
{
    var $comissionMore = 0;
    var $comissionLess = 0;

    /**
     * Employee controller
     */
    function __construct()
    {

        parent::__construct();

        if (!isUserLoggedIn() && $this->router->fetch_class() != 'cron' && !is_cli()) {
            redirect('login');
        }

        if ($this->session->userdata('user_type') != "admin" && !strstr($this->router->fetch_method(), "_ajax") && $this->session->userdata('EMP_ED') != 1 && $this->session->userdata('RPS_PR') != 1 && $this->session->userdata('UHR') != 1 && $this->router->fetch_class() != 'cron' && !$this->input->is_cli_request()) {
            show_404();
        }

        $this->_title = SITE_NAME;

        //load all common models and libraries here;
        $this->load->model('mdl_employees', 'employee_model');
        $this->load->model('mdl_user');
        $this->load->model('mdl_employee', 'employee_login_model');
        $this->load->model('mdl_bonuses_types');
        $this->load->model('mdl_reasons');
        $this->load->helper('utilities');
    }

    /*
     * function index
     * shows login or list of employee if employee logged in;
     *
     * param null
     * returns html view
     *
     */

    public function index($status = NULL)
    {
        show_404();
        die;
        $this->employee_list($status);
    }


    /*
     * function list employee
     * lists all employees with employee type = employee
     *
     * param null
     * returns html view
     *
     */

    public function employee_list($status = NULL)
    {
        show_404();
        die;
        $where['emp_status'] = 'current';
        $where['user_active_employee'] = 1;
        if ($status)
            $where['emp_status'] = $status;
        $data['title'] = $this->_title . " - Employee Management";
        $data['page_title'] = "Employee Management";
        $data['page'] = "employee/index";
        $data['menus'] = "employee/menu";
        $data['status'] = $status;
        $users = $this->mdl_user->get_payroll_user($where, 'emp_name');

        $data['employee_row'] = [];
        if ($users)
            $data['employee_row'] = $users->result();

        $this->load->view('index', $data);
    }

    public function employee_pdf($status = NULL)
    {
        $this->load->library('mpdf');
        $where['user_active_employee'] = 1;
        $where['emp_status'] = 'current';
        if ($status)
            $where['emp_status'] = $status;
        $data['title'] = $this->_title . " - Employee Management";

        $data['status'] = $status;
        $users = $this->mdl_user->get_payroll_user($where, 'emp_name');

        $data['employee_row'] = [];
        if ($users)
            $data['employee_row'] = $users->result();
        $data['pdf'] = TRUE;
        $html = $this->load->view('index', $data, TRUE);

        $this->mpdf->WriteHTML($html);
        $file = "employees.pdf";
        $this->mpdf->Output($file, 'I');
    }

    /*
     * function employee add form
     *
     * param $id = null
     * returns html view
     *
     */

    public function employee_add($msg = '')
    {
        $data['msg'] = $msg;
        $data['title'] = $this->_title . " - Employee Management";
        $data['page_title'] = "Employee Management";
        $data['page'] = "employees/form";
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

    public function employee_update($id = '', $msg = '')
    {

        if ($id != '') {
            $data['msg'] = $msg;
            $data['title'] = $this->_title . " - Employee Management";
            $data['page_title'] = "Employee Management";
            $data['page'] = "employees/form";
            $data['edit'] = $id;


            $users = $this->mdl_user->get_payroll_user(array('users.id' => $id), 'emp_name');

            $data['employee_row'] = [];
            if ($users)
                $data['employee_row'] = $users->result()[0];

            $this->load->view('form', $data);
        } else {
            show_404();
        }
    }

    /*
     * function showform
     * is called for change password
     * 
     * param $id = null
     * returns html view
     * 
     */

    public function employee_change_pass($id = '')
    {

        if ($id != '') {

            $data['title'] = $this->_title . " - Employee Management";
            $data['page_title'] = "Employee Change Password";
            $data['page'] = "employees/form";
            $data['edit'] = $id;
            $wdata['id'] = $id;

            $users = $this->mdl_user->get_payroll_user(array('users.id' => $id), 'emp_name');

            $data['employee_row'] = [];
            if ($users)
                $data['employee_row'] = $users->result()[0];


            $this->load->library('form_validation');

            $this->form_validation->set_rules('txtPassword', 'Password', 'trim|required|min_length[3]|max_length[20]|matches[txtConfPassword]');
            $this->form_validation->set_rules('txtConfPassword', 'Confirm Password', 'trim|required|min_length[3]|max_length[20]');
            if ($this->form_validation->run() == TRUE) {
                $data = array();
                $data["password"] = md5($this->input->post('txtPassword'));
                $data['updated_on'] = date("now");
                $where_data['id'] = $id;
                $this->mdl_user->update_user($data, $where_data);

                $mess = message('success', 'Employee Password Changed!');
                $this->session->set_flashdata('user_message', $mess);
                redirect('employees');
            }

            $this->load->view('change_pass', $data);
        } else {
            show_404();
        }
    }

    /*
     * function save
     * inserts or updated the employee details
     *
     * param $id = null
     * returns bool or error on failure
     *
     */

    public function save($id = '')
    {
        /* validation */
        $this->load->library('form_validation');
        $this->form_validation->set_rules('txtname', 'Name', 'required');
        $this->form_validation->set_rules('txtemail', 'Email', 'required|valid_email');
        $this->form_validation->set_rules('txtusername', 'User Name', 'trim|required|min_length[2]|max_length[20]|alpha_numeric');
        $this->form_validation->set_rules('txtposition', 'Position', 'required');
        $this->form_validation->set_rules('txtaddress1', 'Address line 1', 'required');
        //$this->form_validation->set_rules('txtaddress2', 'Address line 2', '');
        $this->form_validation->set_rules('txtcity', 'City', 'required');
        /*$this->form_validation->set_rules('txtstate', 'State', '');
        $this->form_validation->set_rules('txtphone', 'Phone', '');
        $this->form_validation->set_rules('txtsin', 'Sin', '');
        $this->form_validation->set_rules('txtstatus', 'Status', '');
        $this->form_validation->set_rules('txtstatus', 'Status', '');
        $this->form_validation->set_rules('txttype', 'Type', '');
        $this->form_validation->set_rules('is_field_estimator', 'Estimator', '');
        $this->form_validation->set_rules('is_feild_worker', 'Worker', '');
        $this->form_validation->set_rules('driver', 'Driver', '');
        $this->form_validation->set_rules('climber', 'Climber', '');
        $this->form_validation->set_rules('area_account_message', 'Msg', '');*/
        $this->form_validation->set_rules('deductions_amount', 'Deductions Amount', 'numeric');
        $this->form_validation->set_rules('deductions_state', 'Deductions State', 'is_natural');
        //$this->form_validation->set_rules('deductions_desc', 'Deductions Description', '');

        if ($this->session->userdata('user_type') == "admin")
            $this->form_validation->set_rules('txthourlyrate', 'Employee Hourly Rate', 'required');
        if ($this->session->userdata('user_type') == "admin" && $this->input->post('txtyearlyrate'))
            $this->form_validation->set_rules('txtyearlyrate', 'Employee Yearly Rate', 'required');

        /*$this->form_validation->set_rules('txtstarttime', 'Employee Start Time', '');

        $this->form_validation->set_rules('txthiredate', 'Employee Date Of Hire', '');
        $this->form_validation->set_rules('txtbirthday', 'Employee Date Of Birthday', '');*/

        if ($this->form_validation->run() == FALSE) {
            //echo "validation false";
            $mess = message('alert', validation_errors());
            if ($id != '')
                $this->employee_update($id, $mess);
            else
                $this->employee_add($mess);

        } else {
            //echo "validation OK";
            $now_gmt_time = now(); // GMT time
            $now_time = mdate('%Y-%m-%d %H:%i:%s', $now_gmt_time);

            $data['emp_name'] = $this->input->post('txtname');
            $data['emp_email'] = $this->input->post('txtemail');
            $data['emp_username'] = $this->input->post('txtusername');
            $data['emp_position'] = $this->input->post('txtposition');
            $data['emp_address1'] = $this->input->post('txtaddress1');
            $data['emp_address2'] = $this->input->post('txtaddress2');
            $data['emp_city'] = $this->input->post('txtcity');
            $data['emp_phone'] = $this->input->post('txtphone');
            $data['emp_state'] = $this->input->post('txtstate');
            $data['emp_sin'] = $this->input->post('txtsin');
            if ($this->session->userdata('user_type') == "admin")
                $data['emp_hourly_rate'] = $this->input->post('txthourlyrate');
            $data['emp_yearly_rate'] = NULL;
            if ($this->session->userdata('user_type') == "admin" && $this->input->post('txtyearlyrate'))
                $data['emp_yearly_rate'] = $this->input->post('txtyearlyrate');

            $data['emp_message_on_account'] = $this->input->post('area_account_message');
            $data['emp_feild_worker'] = $this->input->post('is_feild_worker');
            $data['emp_field_estimator'] = $this->input->post('is_field_estimator');
            $data['emp_driver'] = $this->input->post('driver');
            $data['emp_climber'] = $this->input->post('climber');
            $data['emp_status'] = $this->input->post('txtstatus');
            $data['emp_start_time'] = $this->input->post('txtstarttime');
            $data['emp_type'] = $this->input->post('txttype');

            $data['deductions_state'] = $this->input->post('deductions_state');
            $data['deductions_amount'] = $this->input->post('deductions_amount');
            $data['deductions_desc'] = $this->input->post('deductions_desc');

            $data['emp_sex'] = $this->input->post('txtsex');
            $data['emp_date_hire'] = $this->input->post('txthiredate');
            $data['emp_birthday'] = $this->input->post('txtbirthday');
            $data['emp_pay_frequency'] = $this->input->post('txtfrequency');
            if (!$data['emp_date_hire'])
                $data['emp_date_hire'] = NULL;
            if (!$data['emp_birthday'])
                $data['emp_birthday'] = NULL;
            //upload function

            if ($id == '') {

                $dup_data['emp_name'] = $data['emp_name'];
                $check_dup = $this->employee_model->get_employee($sel = '', $dup_data);
                if ($check_dup) {
                    $mess = message('alert', 'Employee already exists!');
                    $this->session->set_flashdata('user_message', $mess);
                    redirect('employees/employee_add');
                }
                $data['added_on'] = $now_time;

                $insert = $this->employee_model->insert_employee($data);
                if ($insert) {
                    $mess = message('success', 'Employee added!');
                    $this->session->set_flashdata('user_message', $mess);
                    redirect('employees');

                }
            } else {

                $data['updated_on'] = $now_time;
                $where_data['employee_id'] = $id;
                $this->employee_model->update_employee($data, $where_data);
                $mess = message('success', 'Employee Updated!');
                $this->session->set_flashdata('user_message', $mess);
                redirect('employees');
            }
        }
    }

    /*
     * function delete
     * deletes the employee
     *
     * param $id
     * returns null / redirects the employee
     *
     */

    public function employee_delete($id)
    {
        if ($id) {
            $this->session->set_userdata("_DELETE_EMPID", $id);
            $confirm = $this->input->post("confirm");
            if (empty($confirm)) {
                $data['title'] = $this->_title . " - Employee Delete Confirm";
                $data['page_title'] = "Employee Delete Confirm";
                $data['employee_row'] = $this->employee_model->find_by_id($id);
                $this->load->view("confirm_delete", $data);
            } else {
                $delete = $this->employee_model->delete($id);
                if ($delete) {
                    $link = ('employees');
                    $mess = message('success', '&nbsp; Employee Deleted!');
                    $this->session->set_flashdata('user_message', $mess);
                    redirect($link); // redirect to list page
                }
            } // check confirm
        }
    }


    /*
    *
    *	function to insert time detals
    *	through ajax
    *   params: created date, intime, outtime, employee id
    *	method: post
    *   referrer: payroll report
    */

    function addtime_ajax()
    {
        $idata = array();
        $idata["login_time"] = $this->input->post("intime");
        $idata["created_date"] = $this->input->post("indate");
        if ($this->input->post("outtime"))
            $idata["logout_time"] = $idata["created_date"] . " " . $this->input->post("outtime") . ":00";

        $idata["employee_id"] = $this->input->post("empid");
        $this->load->model('mdl_employees');
        $employee = $this->mdl_employees->find_by_id($idata["employee_id"]);
        $idata["employee_hourly_rate"] = $employee->emp_hourly_rate;
        $idata["login_time"] = $idata["created_date"] . " " . $idata["login_time"] . ":00";
        $idata["created_date"] = $idata["created_date"] . " 00:00:00";

        if ($this->input->post("outtime")) {
            if (strtotime($idata["login_time"]) > strtotime($idata["logout_time"])) {
                echo "error: greater";
                die;
            }
        }
        $idata["created_ip"] = $this->input->ip_address();
        $res = $this->employee_login_model->insert($idata);

        $this->load->model('mdl_emp_login');
        $newData['login_date'] = $this->input->post('indate');
        $newData['login_employee_id'] = $this->input->post("empid");
        $newData['login'] = $this->input->post("intime");
        $newData['logout'] = $this->input->post("outtime");
        $this->mdl_emp_login->insert($newData);

        if ($res > 0 && isset($idata["logout_time"])) {

            $udata["logout_time"] = $idata["logout_time"];
            $udata["id"] = $res;
            echo $res = $this->employee_login_model->update_logout($udata);
        } else
            echo $res;
        die();
    }


    function loginAs($empId = NULL)
    {
        if ($this->session->userdata('user_type') != "admin" || !$empId)
            show_404();
        $userdata = $this->employee_model->get_employee('', array("employee_id" => $empId));
        if (!empty($userdata)) {
            $this->session->unset_userdata(array('user_id' => '', 'user_type' => '', 'firstname' => '', 'lastname' => '', 'rate' => '', 'user_pic' => '', 'user_last_login' => '', 'user_logged_in' => '', 'chatusername' => ''));
            $emp_details = $userdata->result_array();
            if (!$this->session->userdata('user_id'))
                $this->session->set_userdata(array('user_id' => 0));
            $sdata = array(
                'emp_user_id' => $emp_details[0]["employee_id"],
                'emp_estimator' => $emp_details[0]["emp_field_estimator"],
                'emp_name' => $emp_details[0]["emp_name"],
                'emp_logged_in' => TRUE,
                'user_type' => 'employee',
                'emp_hourly_rate' => $emp_details[0]["emp_hourly_rate"],
                'emp_start_time' => $emp_details[0]["emp_start_time"]
            );
            $this->session->set_userdata($sdata);
            redirect(base_url('employee'));
            exit;
        }
        redirect(base_url('employees'));
    }

    function bonuses()
    {
        if ($this->session->userdata('user_type') != "admin")
            show_404();

        $data['title'] = "Bonuses Types";

        $data['bonuses'] = $this->mdl_bonuses_types->find_all(array(), 'bonus_type_amount DESC');
        $this->load->view('index_bonuses_types', $data);
    }

    function ajax_save_bonus()
    {
        if ($this->session->userdata('user_type') != "admin" && $this->session->userdata('SCR') != 1) {
            show_404();
        }

        $id = $this->input->post('id');
        $data['bonus_type_name'] = strip_tags($this->input->post('name', TRUE));
        $data['bonus_type_description'] = strip_tags($this->input->post('text', TRUE));
        $data['bonus_type_amount'] = intval($this->input->post('amount', TRUE));

        if ($id != '') {
            $this->mdl_bonuses_types->update_bonus($id, $data);
            die(json_encode(array('status' => 'ok')));
        }
        $this->mdl_bonuses_types->insert_bonus($data);
        die(json_encode(array('status' => 'ok')));
    }

    function ajax_delete_bonus()
    {
        if ($this->session->userdata('user_type') != "admin" && $this->session->userdata('SCR') != 1) {
            show_404();
        }
        $id = $this->input->post('id');
        if ($id != '') {
            $this->mdl_bonuses_types->delete_bonus($id);
        }
        die(json_encode(array('status' => 'ok')));
    }

    /***********************REASONS**************************/
    public function reasons()
    {
        if ($this->session->userdata('user_type') != "admin" && $this->session->userdata('RS_ABS') != 1) {
            show_404();
        }
        $data['title'] = 'Reasons Of Absence';
        $data['reasons'] = $this->mdl_reasons->order_by('reason_status', 'DESC')->get_all(array());

        $this->load->view('index_reasons', $data);
    }

    function ajax_save_reason()
    {
        if ($this->session->userdata('user_type') != "admin" && $this->session->userdata('RS_ABS') != 1) {
            show_404();
        }
        $id = $this->input->post('reason_id');
        $data['reason_name'] = strip_tags($this->input->post('reason_name', TRUE));
        $data['reason_limit'] = intval($this->input->post('reason_limit'));
        $data['reason_company'] = intval($this->input->post('reason_company'));
        if ($id != '') {
            $this->mdl_reasons->update($id, $data);
            die(json_encode(array('status' => 'ok')));
        }
        $this->mdl_reasons->insert($data);
        die(json_encode(array('status' => 'ok')));
    }

    function ajax_delete_reason()
    {
        if ($this->session->userdata('user_type') != "admin" && $this->session->userdata('RS_ABS') != 1) {
            show_404();
        }
        $id = $this->input->post('reason_id');
        $status = intval($this->input->post('status'));
        if ($status && $status != 1)
            $status = 1;
        $this->mdl_reasons->update($id, array('reason_status' => $status));
        die(json_encode(array('status' => 'ok')));
    }

    /***********************END REASONS**********************/

    public function crews()
    {
        if ($this->session->userdata('user_type') != "admin" && $this->session->userdata('CRW') != 1) {
            show_404();
        }
        $this->load->model('mdl_crews');
        $data['title'] = 'Employee Roles';

        //get employees

        $users = $this->mdl_user->get_payroll_user(array('emp_status' => 'current', 'emp_feild_worker' => 1));

        $employees = [];
        if ($users)
            $employees = $users->result();

        foreach ($employees as $key => $val)
            $data['employees'][$val->employee_id] = $val;

        $data['crews'] = $this->mdl_crews->find_all_with_limit(array(), '', '', 'crew_status DESC, crew_priority ASC');
        //echo '<pre>'; var_dump($this->db->last_query()); die;
        $this->load->view('index_crews', $data);
    }

    function ajax_save_crew()
    {
        if ($this->session->userdata('user_type') != "admin" && $this->session->userdata('CRW') != 1) {
            show_404();
        }
        $this->load->model('mdl_crews');
        $crew_id = $this->input->post('crew_id');
        $data['crew_name'] = strip_tags($this->input->post('crew_name', TRUE));
        $data['crew_full_name'] = strip_tags($this->input->post('crew_full_name', TRUE));
        $data['crew_leader'] = intval($this->input->post('crew_leader'));
        $data['crew_color'] = $this->input->post('crew_color', TRUE);
        $data['crew_status'] = 1;
        $data['crew_rate'] = $this->input->post('crew_rate') ? $this->input->post('crew_rate') : 0;
        if ($crew_id !== FALSE && $crew_id !== '') {
            $this->mdl_crews->update($crew_id, $data);
            die(json_encode(array('status' => 'ok')));
        }
        $this->mdl_crews->insert($data);
        die(json_encode(array('status' => 'ok')));
    }

    function ajax_delete_crew()
    {
        if ($this->session->userdata('user_type') != "admin" && $this->session->userdata('CRW') != 1) {
            show_404();
        }
        $this->load->model('mdl_crews');
        $crew_id = $this->input->post('crew_id');
        $status = $this->input->post('status') ? 1 : 0;
        if ($crew_id)
            $this->mdl_crews->update($crew_id, array('crew_status' => $status));
        die(json_encode(array('status' => 'ok')));
    }

    function bonuses_pdf()
    {
        $this->load->model('mdl_worked');
        $this->load->model('mdl_schedule');
        $this->load->model('mdl_user');

        $employees = $this->mdl_user->get_payroll_user(array('user_active_employee' => 1, 'employees.emp_status' => 'current'), 'employees.emp_type ASC, employees.emp_name ASC');

        if ($employees)
            $employees = $employees->result();

        foreach ($employees as $employee) {
            $result[$employee->employee_id]['bonus_sum'] = 0;
            $result[$employee->employee_id]['employee'] = $employee;
            $result[$employee->employee_id]['bonuses_percents'] = 0;
            $crews = $this->mdl_schedule->find_team_by(array('team_date >= ' => strtotime('2015-05-01'), 'team_date < ' => strtotime('2015-12-01'), 'user_id' => $employee->employee_id));
            foreach ($crews as $crew) {
                $bonuses = $this->mdl_schedule->get_bonuses(array('bonus_team_id' => $crew->team_id));
                $bonusPerc = 0;
                $bonusSum = 0;
                foreach ($bonuses as $bonus) {
                    if ($bonus['bonus_amount'] >= 0)
                        $bonusPerc += $bonus['bonus_amount'];
                }
                $worked = $this->mdl_worked->get_by(array('worked_user_id' => $employee->employee_id, 'worked_date' => date('Y-m-d', $crew->team_date)));
                if ($worked) {
                    $bonusSum = round((($worked->worked_hours - $worked->worked_lunch) * $worked->worked_hourly_rate) * $bonusPerc / 100, 2);
                    $result[$employee->employee_id]['bonuses_percents'] += $bonusPerc;
                    $result[$employee->employee_id]['bonus_sum'] += $bonusSum;
                    if ($bonusSum)
                        $result[$employee->employee_id]['bonuses'][date('Y-m-d', $crew->team_date)] = $bonusSum;
                }
            }
        }
        $this->load->library('mpdf');
        $this->mpdf->_setPageSize('Letter', $this->mpdf->DefOrientation);
        $html = $this->load->view('bonuses_pdf', array('data' => $result), TRUE);

        $this->mpdf->WriteHTML($html);
        $file = 'bonuses-2015-05-11-2015-12-01.pdf';
        $this->mpdf->Output($file, 'I');
    }

    /**
     * @param $csv_data
     * @throws PHPExcel_Exception
     * @throws PHPExcel_Reader_Exception
     * @throws PHPExcel_Writer_Exception
     * Make csv data and call csv generation function to download csv
     */
    private function payroll_csv($csv_data)
    {
        $employee = $csv_data['employee'];
        $weeks = $csv_data['weeks'];
        $weeks_data = $csv_data['weeks_data'];

        $csv_table_data = [];
        foreach ($weeks as $key => $week) {
            // make csv table for week(same data with payroll_new_pdf.php view)
            $num = $key + 1;
            $weekDays = ['Week #' . $num];
            $weekTmstp = ['PD'];
            $timeIn = ['Clock In'];
            $timeOut = ['Clock Out'];
            $lunch = ['Lunch'];
            $worked = ['Worked'];
            $hourlyRate = ['Hourly Rate'];
            $mhrReturn = ['MHR Return'];
            $teams = ['Teams'];
            $bld = ['B.L.D'];
            $extra_expense = ['Extra Expenses'];

            for ($i = $week['week_start_time']; $i <= $week['week_end_time']; $i += 86400) {
                array_push($weekDays, date('D', $i));
                array_push($weekTmstp, getDateTimeWithTimestamp($i));

                $timeInItem = $timeOutItem = '00:00';
                $lunchItem = $hourlyRateItem = $mhrReturnItem = $teamsItem = '-';
                $workedItem = '0 hrs.';

                if (isset($csv_data['worked_data'][$i])) {
                    $worked_data = $csv_data['worked_data'];

                    if ($worked_data[$i]->worked_start)
                        $timeInItem = date('H:i', strtotime($worked_data[$i]->worked_start));
                    if ($worked_data[$i]->worked_end)
                        $timeOutItem = date('H:i', strtotime($worked_data[$i]->worked_end));
                    if ($worked_data[$i]->worked_lunch !== NULL)
                        $lunchItem = $worked_data[$i]->worked_lunch . ' hrs.';
                    if (isset($worked_data[$i]->mhr['mhrs_return']))
                        $mhrReturnItem = money($worked_data[$i]->mhr['mhrs_return']);
                    if (isset($worked_data[$i]->team)) {
                        $teamsItem = '';
                        foreach ($worked_data[$i]->team as $memberIndex => $member) {
                            $teamsItem .= ($memberIndex != 0 ? ', ' : '') . $member['emp_name'];
                        }
                    }

                    $workedItem = ($worked_data[$i]->worked_hours - $worked_data[$i]->worked_lunch) . ' hrs.';
                    $hourlyRateItem = money($worked_data[$i]->worked_hourly_rate);
                }

                array_push($timeIn, $timeInItem);
                array_push($timeOut, $timeOutItem);
                array_push($lunch, $lunchItem);
                array_push($worked, $workedItem);
                array_push($hourlyRate, $hourlyRateItem);
                array_push($mhrReturn, $mhrReturnItem);
                array_push($teams, $teamsItem);

                $bld_val = money('0.00');
                $extra_val = money('0.00');
                if (isset($csv_data['expenses_data'][$i])) {
                    if (isset($csv_data['expenses_data'][$i]['bld']))
                        $bld_val = money($csv_data['expenses_data'][$i]['bld']['expense_amount']);

                    if (isset($csv_data['expenses_data'][$i]['extra']))
                        $extra_val = money($csv_data['expenses_data'][$i]['extra']['expense_amount']);
                }


                array_push($bld, $bld_val);
                array_push($extra_expense, $extra_val);
            }

            // make footer row
            $footer = ['Late'];
            $footerLateItem = $footerRateItem = $footerPayItem = '-';
            $footerHoursItem = 0;

            if (isset($weeks_data[$num - 1]->worked_lates) && $weeks_data[$num - 1]->worked_lates)
                $footerLateItem = $weeks_data[$num - 1]->worked_lates;
            if (isAdmin()) {
                if (isset($weeks_data[$num - 1]->worked_rate)) {
                    $footerRateItem = money($weeks_data[$num - 1]->worked_rate);
                } else {
                    $footerRateItem = money('0');
                }
                if (isset($weeks_data[$num - 1]->worked_total_pay)) {
                    $footerPayItem = money($weeks_data[$num - 1]->worked_total_pay);
                } else {
                    $footerPayItem = money('0');
                }
            }

            if (isset($weeks_data[$num - 1]->worked_payed)) {
                $footerHoursItem = $weeks_data[$num - 1]->worked_payed;
            }

            $emptyCsvRow = [null]; // add empty row for separating tables in csv

            array_push($footer, $footerLateItem, 'AVG Rate', $footerRateItem, 'To Pay', $footerPayItem, 'Total Hours', $footerHoursItem . ' hrs.');

            array_push($csv_table_data, ['header' => $weekDays], $weekTmstp, $timeIn, $timeOut, $lunch, $worked, $hourlyRate, $mhrReturn, $teams, $bld, $extra_expense, ['footer' => $footer], $emptyCsvRow);
        }

        // START: payroll biweekly table items
        $deduction_amount = isset($csv_data['deductions']->deduction_amount) ? $csv_data['deductions']->deduction_amount : NULL;
        $deduction_amount = ($deduction_amount === NULL && $employee->deductions_state && $employee->deductions_amount) ? 0 : $deduction_amount;
        $more = $this->comissionMore;
        $less = $this->comissionLess;

        array_push($csv_table_data,
            ['Payday', getDateTimeWithDate($csv_data['payroll']->payroll_day, 'Y-m-d')],
            ['Total', (isset($csv_data['payroll_total_data']->worked_payed) ? $csv_data['payroll_total_data']->worked_payed : 0) . ' hrs.'],
            ['Hourly Rate', money(isset($csv_data['payroll_total_data']->worked_rate) ? trim(getAmount($csv_data['payroll_total_data']->worked_rate)) : 0)],

            ['Subtotal', money((isset($csv_data['payroll_total_data']->worked_rate) && isset($csv_data['payroll_total_data']->worked_payed)) ? trim(getAmount($csv_data['payroll_total_data']->worked_rate * $csv_data['payroll_total_data']->worked_payed)) : 0)],

            ['Late', isset($csv_data['payroll_total_data']->worked_lates) ? $csv_data['payroll_total_data']->worked_lates : 0],
            ['Deductions', money($deduction_amount ? $deduction_amount : 0)]
        );

        if (isset($employee->estmhrs)) {
            $mhrs_return2 = money(trim(getAmount(element('mhrs_return2', $employee->estmhrs, 0))));
            array_push($csv_table_data, ['AVG Man Hours Return (EST)', $mhrs_return2]);
        }
        if (isset($employee->empmhrs)) {
            $mhrs_return2 = money(trim(getAmount(element('new_avg', $employee->empmhrs, 0))));
            array_push($csv_table_data, ['AVG Man Hours Return (EMP)', $mhrs_return2]);
        }
        if ($employee->emp_field_estimator) {
            $more = money(trim($more));
            $less = money(trim($less));
            array_push($csv_table_data, ['TOTAL ' . money(GOOD_MAN_HOURS_RETURN) . '+', $more]);
            array_push($csv_table_data, ['TOTAL ' . money(GOOD_MAN_HOURS_RETURN) . '-', $less]);
        }

        array_push($csv_table_data, ['B.L.D / Extra Expenses', money($csv_data['bld_total'], 2, '.', ',') . ' / ' . money($csv_data['extra_total'], 2, '.', ',')]);

        array_push($csv_table_data, ['Total Pay', money((isset($csv_data['payroll_total_data']->worked_total_pay) ? $csv_data['payroll_total_data']->worked_total_pay : 0) + $csv_data['bld_total'] + $csv_data['extra_total'])]);

        // END: payroll biweekly table items
        $this->load->library('excel');
        $file_name = $csv_data['employee']->emp_name . ' ' . $csv_data['payroll']->payroll_start_date . ' - ' . $csv_data['payroll']->payroll_end_date . '.csv';

        save_csv($file_name, $csv_table_data, true);


    }

    function payroll($employee_id = NULL, $payroll_id = NULL, $pdf = FALSE, $more = FALSE)
    {
        if ($this->router->fetch_class() != 'cron' && !is_cli() && $this->session->userdata('user_type') != "admin" && !$this->session->userdata("RPS_PR"))
            show_404();

        $data['title'] = $this->_title . ' - Payroll';
        $data['employee_id'] = $employee_id;
        $data['payroll_id'] = $payroll_id;
        $this->load->model('mdl_payroll');
        $this->load->model('mdl_worked');
        $this->load->model('mdl_workorders');
        $this->load->model('mdl_emp_login');
        $this->load->model('mdl_deductions');
        $this->load->model('mdl_schedule');
        $this->load->model('mdl_user');
        $this->load->model('mdl_crews_orm');
        $this->load->helper('payroll');

        /****GET EMPLOYEES****/
        $this->load->model('mdl_employees', 'employee_model');
        $data['employees'] = array();
        $employees = $this->mdl_user->get_payroll_user(array('user_active_employee' => 1), '`active_status` ASC , emp_name ASC');

        if (!empty($employees)) {
            $data['employees'] = $employees->result_array();
        }
        /****END GET EMPLOYEES****/
        $data['user_id'] = $employee_id;
        if ($employee_id) {
            $data['lunch_state'] = (bool)$this->mdl_settings_orm->get_by('stt_key_name', 'payroll_lunch_state')->stt_key_value;
            $data['deduction_state'] = (bool)$this->mdl_settings_orm->get_by('stt_key_name', 'payroll_deduction_state')->stt_key_value;

            $date = date('Y-m-d');
            if (!$payroll_id)
                $data['payroll'] = $this->mdl_payroll->get_by(array('payroll_start_date <=' => $date, 'payroll_end_date >=' => $date));
            else
                $data['payroll'] = $this->mdl_payroll->get($payroll_id);

            $payroll_id = $data['payroll']->payroll_id;
            $data['payroll_id'] = $payroll_id;
            $date1 = new DateTime($data['payroll']->payroll_start_date);
            $date2 = new DateTime($data['payroll']->payroll_end_date);
            $interval = $date1->diff($date2);

            $user = $this->mdl_user->get_payroll_user(array('users.id' => $employee_id));

            if ($user)
                $data['employee'] = $user->result()[0];

            if (!$data['payroll'] || !$data['employee'])
                show_404();

            if (date("w", strtotime($data['payroll']->payroll_start_date)) == 0) { //starts from sunday

                $six_day_int = 86400 * 6;

                $w_start_time = strtotime($data['payroll']->payroll_start_date);
                $w_end_time = DateTime::createFromFormat('Y-m-d H:i:s', $data['payroll']->payroll_start_date . ' 00:00:00')->modify('+6 days')->getTimestamp();
                $data['weeks'][] = array('week_start_time' => $w_start_time, 'week_end_time' => $w_end_time);
                if ($interval->d > 7) {
                    $w_end_time = strtotime($data['payroll']->payroll_end_date);
                    $w_start_time = DateTime::createFromFormat('Y-m-d H:i:s', $data['payroll']->payroll_end_date . ' 00:00:00')->modify('-6 days')->getTimestamp();
                    $data['weeks'][] = array('week_start_time' => $w_start_time, 'week_end_time' => $w_end_time);
                }

            } else { //starts from monday

                $data['weeks'][] = array('week_start_time' => strtotime($data['payroll']->payroll_start_date), 'week_end_time' => strtotime('Sunday this week', strtotime($data['payroll']->payroll_start_date)));

                if ($interval->d > 7) {
                    $data['weeks'][] = array('week_start_time' => strtotime('Monday next week', strtotime($data['payroll']->payroll_start_date)), 'week_end_time' => (strtotime($data['payroll']->payroll_end_date)));
                }
            }

            $worked_data = $this->mdl_worked->with('mdl_emp_login')->get_many_by(array('worked_payroll_id' => $data['payroll']->payroll_id, 'worked_user_id' => $employee_id));

            $data['hasLunch'] = $this->hasLunch($worked_data);

            $this->load->model('mdl_reports');
            $expenses = $this->mdl_reports->get_expenses(['expense_date >=' => strtotime($data['payroll']->payroll_start_date), 'expense_date <=' => strtotime($data['payroll']->payroll_end_date), 'expense_user_id' => $data['employee']->id, 'flag' => 'employee_benefits']);

            /*--------------sort expenses --------------*/
            $data['expenses_data'] = [];
            $data['bld_total'] = 0;
            $data['extra_total'] = 0;
            foreach ($expenses as $k => $value) {
                $key = date('Y-m-d', $value['expense_date'] + 3600);
                if (!isset($data['expenses_data'][$key]))
                    $data['expenses_data'][$key] = ['bld' => 0, 'extra' => 0];

                $value['expense_amount'] = $value['expense_amount'] + $value['expense_hst_amount'];
                if ($value['expense_is_extra']) {
                    $data['extra_total'] += $value['expense_amount'];
                    $data['expenses_data'][$key]['extra'] = $value;
                } else {
                    $data['bld_total'] += $value['expense_amount'];
                    $data['expenses_data'][$key]['bld'] = $value;
                }
            }
            /*--------------sort expenses --------------*/

            foreach ($worked_data as $row) {
                $mhr = array();
                $notOffice = FALSE;
                $notOffice = $this->mdl_emp_login->get_many_by(array('login_worked_id' => $row->worked_id, 'login_user_id' => $row->worked_user_id, 'logout_lon != ' => 0, 'logout_lon !=' => 'NULL'));

                $data['worked_data'][$row->worked_date] = $row;

                if ($notOffice && !empty($notOffice)) {
                    $data['worked_data'][$row->worked_date]->not_office = TRUE;
                }

                $wdata['team_date >='] = strtotime($row->worked_date);

                $wdata['team_date <'] = DateTime::createFromFormat('Y-m-d', $row->worked_date)->modify('+1 day')->getTimestamp();
                $wdata['users.id'] = $employee_id;

                if ((int)$data['employee']->emp_field_estimator) {
                    $mhr = $this->mdl_workorders->estimators_mhr_rate($wdata, array('users.id' => $employee_id), TRUE);
                } elseif ((int)$data['employee']->emp_feild_worker) {
                    $wdata_emp['emp_status'] = 'current';
                    $wdata_emp['active_status'] = 'yes';
                    $wdata_emp['users.id'] = $employee_id;
                    $mhr = $this->mdl_workorders->employees_mhr_return($wdata, $wdata_emp, TRUE);
                }

                if (!empty($mhr))
                    $data['worked_data'][$row->worked_date]->mhr = $mhr;
            }

            $data['payroll_bonuses'] = 0;

            /****************BONUSES AMOUNT*******************/
            /*$whereBonus['team_date >='] = strtotime($data['payroll']->payroll_start_date);
            $whereBonus['team_date <='] = strtotime($data['payroll']->payroll_end_date . ' 23:59:59');
            $whereBonus['employee_id'] = $employee_id;
            $bonusesRows = $this->mdl_schedule->get_employee_bonuses($whereBonus);

            /********BONUSES**********/
            /*foreach($bonusesRows as $bonus)
            {
                $key = strtotime(date('Y-m-d', $bonus['team_date']));

                if(date('m', $bonus['team_date']) >= 5 && date('m', $bonus['team_date']) <= 11)
                {
                    if(isset($data['worked_data'][$key]))
                    {
                        $bonusSum = round(($data['worked_data'][$key]->worked_hours - $data['worked_data'][$key]->worked_lunch) * $data['worked_data'][$key]->worked_hourly_rate * $bonus['bonus_amount'] / 100, 2);
                        $data['worked_data'][$key]->bonuses = isset($data['worked_data'][$key]->bonuses) ? $data['worked_data'][$key]->bonuses + $bonusSum : $bonusSum;
                        $data['payroll_bonuses'] += $bonusSum;
                    }
                }
            }
            $data['bonuses_dates'] = $this->mdl_schedule->get_collected_bonuses_dates($data['payroll']->payroll_end_date);
            $data['collected_bonuses'] = $this->mdl_schedule->get_collected_bonuses_sum1($employee_id, $data['payroll']->payroll_end_date);*/

            /****END*BONUSES**********/


            /***************END BONUSES AMOUNT*****************/
            /****************TEAMS MEMBERS*******************/
            $teams = [];
            if ($data['employee']->emp_feild_worker) {
                $whereTeams['schedule_teams.team_date >='] = strtotime($data['payroll']->payroll_start_date);
                $whereTeams['schedule_teams.team_date <='] = strtotime($data['payroll']->payroll_end_date . ' 23:59:59');
                $whereTeams['schedule_teams_members.user_id'] = $employee_id;
                $teams = $this->mdl_schedule->get_team_members($whereTeams, false, false);

                $whereTeams = NULL;
                foreach ($teams as $team)
                    $whereTeams .= ' OR employee_team_id = ' . $team['team_id'];
                $whereTeams = ltrim($whereTeams, ' OR ');
                $members = $this->mdl_schedule->get_team_members($whereTeams);

                if (!$members)
                    $members = array();

                foreach ($members as $member) {
                    $key = date('Y-m-d', $member['team_date']);
                    if (isset($data['worked_data'][$key]) && $member['employee_id'] != $employee_id)
                        $data['worked_data'][$key]->team[] = $member;
                }
            }
            /***************END TEAMS MEMBERS****************/

            foreach ($data['weeks'] as $week) {
                $where_week = array(
                    'worked_user_id' => $employee_id,
                    'worked_date >=' => date('Y-m-d', $week['week_start_time']),
                    'worked_date <=' => date('Y-m-d', $week['week_end_time']),
                    'worked_payroll_id' => $payroll_id
                );
                $data['weeks_data'][] = $this->mdl_worked->get_worked_hours_total($where_week, FALSE, 1);
            }

            $where_week = array(
                'worked_user_id' => $employee_id,
                'worked_payroll_id' => $payroll_id
            );
            $data['payroll_total_data'] = $this->mdl_worked->get_worked_hours_total($where_week, TRUE, 1);
            $data['deductions'] = $this->mdl_deductions->get_by(array('deduction_payroll_id' => $payroll_id, 'deduction_user_id' => $employee_id));

            if ($data['employee']->emp_feild_worker)
                $data['employee']->empmhrs = worker_mhrs_and_total($employee_id, $data['payroll']->payroll_start_date, $data['payroll']->payroll_end_date);
            if ($data['employee']->emp_field_estimator) {
                $data['employee']->estmhrs = estimator_mhrs_and_total($employee_id, $data['payroll']->payroll_start_date, $data['payroll']->payroll_end_date);
                $estimator_mhrs_return = $this->mdl_workorders->estimator_stats_by_finished_wo($employee_id, strtotime($data['payroll']->payroll_start_date), strtotime($data['payroll']->payroll_end_date));

                if (!empty($estimator_mhrs_return) && isset($estimator_mhrs_return[0]->estimator_mhr_return)) {
                    $data['employee']->estmhrs['mhrs_return'] = $estimator_mhrs_return[0]->estimator_mhr_return;
                    $data['employee']->estmhrs['mhrs_return2'] = $estimator_mhrs_return[0]->estimator_mhr_return2;
                }
            }

        }
        //echo '<pre>'; var_dump($data['worked_data']); die;
        if ($this->input->is_ajax_request())
            $this->load->view('employees/payroll_employee_workspace', $data);
        elseif ($pdf) {
            if ($data['employee']->emp_feild_worker) {
                $empData = $data;

            }

            $estHtml = $this->_getEstimatorComissionHtml($employee_id, $payroll_id);
            $empHtml = $this->_getFieldWorkrerHtml($employee_id, $payroll_id);

            $data['more'] = $this->comissionMore;
            $data['less'] = $this->comissionLess;

            $data['pdf'] = true;
            $this->load->library('mpdf');
            $this->mpdf->_setPageSize('Letter', $this->mpdf->DefOrientation);
            $this->mpdf->AddPage('P', 'Letter', 0, '', 0, 0, 0, 5, 0, 0);
            $this->mpdf->SetHtmlFooter('');
            $html = $this->load->view('payroll_new_pdf', $data, TRUE);
            $this->mpdf->WriteHTML($html);

            if ($data['employee']->emp_feild_worker && $empHtml) {
                $this->mpdf->AddPage('L', 'Letter', 0, '', 2, 2, 2, 2, 0, 0);
                $this->mpdf->SetHtmlFooter('<div class="text-left">' . $data['employee']->firstname . ' ' . $data['employee']->lastname . '</div>');
                $this->mpdf->WriteHTML($empHtml);
            }

            if ($data['employee']->emp_field_estimator && $estHtml) {
                $this->mpdf->AddPage('P', 'Letter', 0, '', 2, 2, 2, 2, 0, 0);
                $this->mpdf->SetHtmlFooter('<div class="text-right">' . $data['employee']->firstname . ' ' . $data['employee']->lastname . '</div>');
                $this->mpdf->WriteHTML($estHtml);
            }


            if (!$more) {
                $file = $data['employee']->emp_name . ' ' . $data['payroll']->payroll_start_date . ' - ' . $data['payroll']->payroll_end_date . '.pdf';
                $this->mpdf->Output($file, 'I');
            }
        } elseif ($this->input->get('payroll_csv')) {
            $this->payroll_csv($data);
        } else {
            $during = User::find($this->session->user_id)->during;
            $data['is_allow_edit'] = true;

            if ($during != 0 && isset($data['payroll']) && !empty($data['payroll'])) {
                $data['is_allow_edit'] = Carbon::now()->format('Y-m-d') <= Carbon::create($data['payroll']->payroll_end_date)->addDays($during)->format('Y-m-d');
            }

            $this->load->view('index_payroll', $data);
        }
    }

    function payroll_comission($employee_id = NULL, $payroll_id = NULL)
    {
        $this->load->model('mdl_payroll');

        if (!$payroll_id)
            $data['payroll'] = $this->mdl_payroll->get_by(array('payroll_start_date <=' => $date, 'payroll_end_date >=' => $date));
        else
            $data['payroll'] = $this->mdl_payroll->get($payroll_id);
        $data['employee'] = $this->mdl_user->get_payroll_user(['users.id' => $employee_id])->row();

        $this->load->library('mpdf');
        $html = $this->_getEstimatorComissionHtml($employee_id, $payroll_id);

        if (!$html)
            $html = $this->load->view('payroll_estimator_commission_empty_pdf', [], TRUE);

        $this->mpdf->AddPage('P', 'Letter', 0, '', 2, 2, 2, 2, 0, 0);
        $this->mpdf->SetHtmlFooter('<div class="text-right">' . $data['employee']->firstname . ' ' . $data['employee']->lastname . '</div>');
        $this->mpdf->WriteHTML($html);
        $file = $data['employee']->emp_name . ' ' . $data['payroll']->payroll_start_date . ' - ' . $data['payroll']->payroll_end_date . '.pdf';
        $this->mpdf->Output($file, 'I');
    }

    function all_payroll_comission($payroll_id = NULL)
    {
        $this->load->model('mdl_payroll');

        if (!$payroll_id)
            $data['payroll'] = $this->mdl_payroll->get_by(array('payroll_start_date <=' => $date, 'payroll_end_date >=' => $date));
        else
            $data['payroll'] = $this->mdl_payroll->get($payroll_id);


        $this->load->library('mpdf');

        $employees = [];
        $empData = $this->mdl_user->get_payroll_user(['user_active_employee' => 1, 'emp_field_estimator' => '1', 'active_status' => 'yes'], 'emp_status ASC,   `active_status` ASC , emp_name ASC');
        if ($empData)
            $employees = $empData->result();


        foreach ($employees as $key => $value) {
            $html = $this->_getEstimatorComissionHtml($value->id, $payroll_id);

            if (!$html)
                continue;

            $this->mpdf->AddPage('P', 'Letter', 0, '', 2, 2, 2, 2, 0, 0);
            $this->mpdf->SetHtmlFooter('<div class="text-right">' . $value->firstname . ' ' . $value->lastname . '</div>');
            $this->mpdf->WriteHTML($html);
        }

        $file = $data['payroll']->payroll_start_date . ' - ' . $data['payroll']->payroll_end_date . '.pdf';
        $this->mpdf->Output($file, 'I');
    }

    private function _getEstimatorComissionHtml($employee_id, $payroll_id = NULL, $date_range = null)
    {
        if ($employee_id == 28 and $payroll_id == 140)
            return false;
        $this->load->model('mdl_payroll');
        $this->load->model('mdl_workorders');
        $this->load->model('mdl_schedule');

        $estData['title'] = $this->_title . ' - Comission';

        $date = date('Y-m-d');

        if (null !== $date_range)
            $data['payroll'] = $this->mdl_payroll->get_by(array('payroll_start_date <=' => $date_range['end'], 'payroll_end_date >=' => $date_range['start']));
        else if (!$payroll_id)
            $data['payroll'] = $this->mdl_payroll->get_by(array('payroll_start_date <=' => $date, 'payroll_end_date >=' => $date));
        else
            $data['payroll'] = $this->mdl_payroll->get($payroll_id);


        if (date("w", strtotime($data['payroll']->payroll_start_date)) == 0) { //starts from sunday

            $six_day_int = 86400 * 6;

            $w_start_time = strtotime($data['payroll']->payroll_start_date);
            $w_end_time = $w_start_time + $six_day_int;
            $estData['weeks'][] = array('week_start_time' => $w_start_time, 'week_end_time' => $w_end_time);
            $w_end_time = strtotime($data['payroll']->payroll_end_date);
            $w_start_time = $w_end_time - $six_day_int;
            $estData['weeks'][] = array('week_start_time' => $w_start_time, 'week_end_time' => $w_end_time);

        } else { //starts from monday

            $estData['weeks'][] = array('week_start_time' => strtotime($data['payroll']->payroll_start_date), 'week_end_time' => strtotime('Sunday this week', strtotime($data['payroll']->payroll_start_date)));
            $estData['weeks'][] = array('week_start_time' => strtotime('Monday next week', strtotime($data['payroll']->payroll_start_date)), 'week_end_time' => (strtotime($data['payroll']->payroll_end_date)));
        }

        //$estData['weeks'][] = array('week_start_time' => strtotime($data['payroll']->payroll_start_date), 'week_end_time' => strtotime('Sunday this week', strtotime($data['payroll']->payroll_start_date)));
        //$estData['weeks'][] = array('week_start_time' => strtotime('Monday next week', strtotime($data['payroll']->payroll_start_date)), 'week_end_time' => (strtotime($data['payroll']->payroll_end_date)));

        $employee = $this->mdl_user->get_payroll_user(['users.id' => $employee_id]);

        if (!$employee)
            return FALSE;

        $data['employee'] = $employee->row();

        if (!$data['employee']->emp_field_estimator)
            return FALSE;

        $this->comissionMore = 0;
        $this->comissionLess = 0;

        $payrollWorkorders = [];
        $finishedStatusId = $this->mdl_workorders->getFinishedStatusId();
        $workordersData = $this->mdl_workorders->get_scheduled_workorders_by_status_date([
            'user_id' => $employee_id,
            'status_log.status_value' => $finishedStatusId,
            'status_log.status_date >=' => strtotime($data['payroll']->payroll_start_date),
            'status_log.status_date <=' => strtotime($data['payroll']->payroll_end_date) + 86400,
            'workorders.wo_status' => $finishedStatusId,
        ]);

        //echo "<pre>" . $this->db->last_query(); die;
        if (!$workordersData)
            return false;

        foreach ($workordersData as $key => &$value) {
            $value['events'] = $this->mdl_schedule->get_events_dashboard(['schedule.event_wo_id' => $value['id']], FALSE, TRUE);

            $totalForWork = $totalMhrsPrice = $totalForReturn = $totalHours = $totalForReturnWithDamages = 0;
            $workorderDamages = 0;
            foreach ($value['events'] as $k => &$v) {
                $v['team'] = $this->mdl_schedule->get_estimator_report_event_members(['schedule.id' => $v['id']], TRUE);
                $teamDamages = $this->mdl_schedule->sum_demage_complain(['team_id' => $v['team']['team_id']]);
                $mhrReturn = 0;
                $totalForWork += $v['event_price'] - $v['event_damage'];
                if ($v['team']['team_closed']) {
                    $mhrReturn = round(($v['team']['team_amount'] - $teamDamages['event_damage']) / $v['team']['team_man_hours'], 2);
                    $totalForReturn += ($v['event_price']);
                    $totalForReturnWithDamages += ($v['event_price'] - $v['event_damage']);
                    $teamAmt = (int)$v['team']['team_amount'] === 0 ? 1 : (int)$v['team']['team_amount'];
                    $totalHours += $v['event_price'] * $v['team']['team_man_hours'] / $teamAmt;
                    $totalMhrsPrice += ($v['event_price'] - $v['event_damage']) * $mhrReturn;
                }

                $v['mhrReturn'] = $mhrReturn;
                $workorderDamages += $v['event_damage'];
            }

            $discount = 0;

            if ($value['discount_amount']) {
                $discount = $value['discount_percents'] ? $totalForWork * $value['discount_amount'] / 100 : $value['discount_amount'];
            }

            $value['totalForReturn'] = $totalForReturn;
            $value['totalMhrsPrice'] = $totalMhrsPrice;
            $value['totalForWork'] = $totalForWork;
            $value['totalHours'] = $totalHours;
            $value['totalForReturnWithDamages'] = $totalForReturnWithDamages;
            //var_dump($totalMhrsPrice, $totalForReturn);die;
            $value['totalMhrsReturn'] = $value['totalForReturn'] ? round($value['totalForReturnWithDamages'] / $value['totalHours'], 2) : 0;
            if (!$value['count_other_finished']) {
                if ($value['totalMhrsReturn'] >= GOOD_MAN_HOURS_RETURN)
                    $this->comissionMore += $value['scheduled_wo_price'];
                else
                    $this->comissionLess += $value['scheduled_wo_price'];
            }

            $payrollWorkorders[date('Y-m-d', $value['status_date'])][] = $value;
        }

        if (count($payrollWorkorders) > 5) { //countOk
            $count = 0;
            $handler = 0;
            $firstHalfTo = count($payrollWorkorders) % 2 ? (int)(count($payrollWorkorders) / 2) + 1 : count($payrollWorkorders) / 2; //countOk
            foreach ($payrollWorkorders as $key => $value) {
                $estData['payrollWorkorders'][$handler][$key] = $value;
                $handler = $count + 1 == $firstHalfTo ? $handler + 1 : $handler;
                $count++;
            }
        } else {
            $estData['payrollWorkorders'][0] = $payrollWorkorders;
        }
        $html = $this->load->view('payroll_estimator_report_comission_workspace_pdf', $estData, TRUE);
        return $html;

    }

    private function _getFieldWorkrerHtml($employee_id, $payroll_id = NULL, $date_range = null)
    {
        $empData = [];

        $date = date('Y-m-d');

        if (null !== $date_range) {
            $data['payroll'] = $this->mdl_payroll->get_by(array('payroll_start_date <=' => $date_range['end'], 'payroll_end_date >=' => $date_range['start']));
        } else if (!$payroll_id) {
            $data['payroll'] = $this->mdl_payroll->get_by(array('payroll_start_date <=' => $date, 'payroll_end_date >=' => $date));
        } else {
            $data['payroll'] = $this->mdl_payroll->get($payroll_id);
        }

        $ev_add_time = 0;
        if (date("w", strtotime($data['payroll']->payroll_start_date)) == 0) { //starts from sunday

            $six_day_int = 86400 * 6;

            $w_start_time = strtotime($data['payroll']->payroll_start_date);
            $w_end_time = $w_start_time + $six_day_int;
            $empData['weeks'][] = array('week_start_time' => $w_start_time, 'week_end_time' => $w_end_time);
            $w_end_time = strtotime($data['payroll']->payroll_end_date);
            $w_start_time = $w_end_time - $six_day_int;
            $empData['weeks'][] = array('week_start_time' => $w_start_time, 'week_end_time' => $w_end_time);

            $ev_add_time = 86400; //plus one day to get schedule events

        } else { //starts from monday

            $empData['weeks'][] = array('week_start_time' => strtotime($data['payroll']->payroll_start_date), 'week_end_time' => strtotime('Sunday this week', strtotime($data['payroll']->payroll_start_date)));
            $empData['weeks'][] = array('week_start_time' => strtotime('Monday next week', strtotime($data['payroll']->payroll_start_date)), 'week_end_time' => (strtotime($data['payroll']->payroll_end_date)));
        }

        //$empData['weeks'][] = array('week_start_time' => strtotime($data['payroll']->payroll_start_date), 'week_end_time' => strtotime('Sunday this week', strtotime($data['payroll']->payroll_start_date)));
        //$empData['weeks'][] = array('week_start_time' => strtotime('Monday next week', strtotime($data['payroll']->payroll_start_date)), 'week_end_time' => (strtotime($data['payroll']->payroll_end_date)));

        $events = $this->mdl_schedule->get_events_dashboard(['schedule.event_end >=' => strtotime($data['payroll']->payroll_start_date), 'schedule.event_end <=' => strtotime($data['payroll']->payroll_end_date) + $ev_add_time, 'schedule_teams_members.user_id' => $employee_id]);

        /*echo $data['payroll']->payroll_start_date . ' - ' . $data['payroll']->payroll_end_date;
        echo "<pre>";
        echo $this->db->last_query();
        var_dump($events);
        die;*/

        if (!$events)
            return FALSE;

        $data['employee'] = $this->mdl_user->get_payroll_user(['users.id' => $employee_id])->row();

        if (!$data['employee']->emp_feild_worker)
            return FALSE;

        $teams = [];

        $whereTeams['schedule_teams.team_date >='] = strtotime($data['payroll']->payroll_start_date);
        $whereTeams['schedule_teams.team_date <='] = strtotime($data['payroll']->payroll_end_date . ' 23:59:59');
        $whereTeams['schedule_teams_members.user_id'] = $employee_id;
        $teams = $this->mdl_schedule->get_team_members($whereTeams, false, false);

        $whereTeams = NULL;
        foreach ($teams as $team)
            $whereTeams .= ' OR employee_team_id = ' . $team['team_id'];
        $whereTeams = ltrim($whereTeams, ' OR ');
        $members = $this->mdl_schedule->get_team_members($whereTeams);

        if (!$members)
            $members = array();

        foreach ($members as $member) {
            $key = date('Y-m-d', $member['team_date']);
            if ($member['employee_id'] != $employee_id) {
                if (!isset($empData['worked_data'][$key])) {
                    $empData['worked_data'][$key] = new \StdClass();
                    $empData['worked_data'][$key]->team = [];
                }
                $empData['worked_data'][$key]->team[] = $member;
            }
        }

        $empData['events_totals'] = [];
        foreach ($events as $key => $value) {
            $empData['events'][date('Y-m-d', $value['event_end'])][] = $value;
            if (!isset($empData['events_totals'][date('Y-m-d', $value['event_end'])]['travel_time_total']))
                $empData['events_totals'][date('Y-m-d', $value['event_end'])]['travel_time_total'] = 0;
            if (!isset($empData['events_totals'][date('Y-m-d', $value['event_end'])]['planned_travel_time_total']))
                $empData['events_totals'][date('Y-m-d', $value['event_end'])]['planned_travel_time_total'] = 0;
            if (!isset($empData['events_totals'][date('Y-m-d', $value['event_end'])]['on_site_time_total']))
                $empData['events_totals'][date('Y-m-d', $value['event_end'])]['on_site_time_total'] = 0;
            if (!isset($empData['events_totals'][date('Y-m-d', $value['event_end'])]['planned_service_time_total']))
                $empData['events_totals'][date('Y-m-d', $value['event_end'])]['planned_service_time_total'] = 0;

            $empData['events_totals'][date('Y-m-d', $value['event_end'])]['travel_time_total'] += (float)$value['er_travel_time'];
            $empData['events_totals'][date('Y-m-d', $value['event_end'])]['planned_travel_time_total'] += (float)$value['planned_travel_time'];

            $empData['events_totals'][date('Y-m-d', $value['event_end'])]['on_site_time_total'] += (float)$value['er_on_site_time'];
            $empData['events_totals'][date('Y-m-d', $value['event_end'])]['planned_service_time_total'] += (float)$value['planned_service_time'];
        }

        foreach ($teams as $key => $value) {
            $teamDate = date('Y-m-d', $value['team_date'] + 4000);
            $empData['teams'][$teamDate] = $value;

            $empData['teams'][$teamDate]['team_estimated_hours'] = 0;
            $empData['teams'][$teamDate]['team_estimated_amount'] = 0;

            $team_events = $this->mdl_schedule->get_events_dashboard(['schedule.event_team_id' => $value['team_id']]);
            foreach ($team_events as $ev) {
                $ev['event_services'] = array();
                $eventServices = $this->mdl_schedule->get_event_services(array('event_id' => $ev['id']));

                //echo "<pre>"; echo $this->db->last_query(); die;
                if (!empty($eventServices)) {
                    foreach ($eventServices as $jkey => $event_service) {
                        $estimated_crew = $this->mdl_crews_orm->get_many_by(['crew_service_id' => $event_service['event_service_id']]);
                        $count_members = !empty($estimated_crew) && $estimated_crew ? count($estimated_crew) : 1; //countOk

                        $empData['teams'][$teamDate]['team_estimated_hours'] += $count_members * ($event_service['service_time'] + $event_service['service_disposal_time'] + $event_service['service_travel_time']);
                        $empData['teams'][$teamDate]['team_estimated_amount'] += $event_service['service_price'];
                        $empData['teams'][$teamDate]['team_estimated_amount'] = round($empData['teams'][$teamDate]['team_estimated_amount'], 2);
                    }
                }
            }
        }
        //if($employee_id == 87)
        //{echo "<pre>";var_dump($empData['teams']);die;}
        $html = $this->load->view('payroll_report_workspace_pdf', $empData, TRUE);
//		var_dump($html); die;
        return $html;
    }

    function payroll_all_pdf($payroll_id = NULL, $output = 'I')
    {
        if ($this->router->fetch_class() != 'cron' && !is_cli() && $this->session->userdata('user_type') != "admin" && !$this->session->userdata("RPS_PR"))
            show_404();

        $this->load->model('mdl_payroll');
        $this->load->model('mdl_worked');
        $this->load->library('mpdf');

        if (!$payroll_id)
            $payroll = $this->mdl_payroll->get_by(array('payroll_start_date <=' => date('Y-m-d'), 'payroll_end_date >=' => date('Y-m-d')));
        else
            $payroll = $this->mdl_payroll->get($payroll_id);

        if (!$payroll)
            show_404();

        //$empTypes = array('employee', 'sub_ta', 'sub_ca');
        $empTypes = array('employee', 'subcontractor', 'temp/cash');
        foreach ($empTypes as $val) {

            $employees = $this->mdl_worked->get_payroll_overview_employees(array('worked_payroll_id' => $payroll->payroll_id, 'emp_type' => $val), TRUE);

            foreach ($employees as $employee) {
                $this->payroll($employee->employee_id, $payroll->payroll_id, TRUE, TRUE);
                if (count($this->mpdf->pages) % 2) { //countOk
                    $this->mpdf->AddPage('P', 'Letter', 0, '', 0, 0, 0, 0, 0, 0);
                    $this->mpdf->SetHtmlFooter('');
                }
            }
        }

        $name = '';
        if ($output == 'F') {
            $name .= 'uploads/payrolls_pdf/';
            $name .= 'backup_payroll_' . $payroll->payroll_id . '.pdf';
            $this->mpdf->Output(sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'backup_payroll_' . $payroll->payroll_id . '.pdf', 'F');
            bucket_move(sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'backup_payroll_' . $payroll->payroll_id . '.pdf', $name, ['ContentType' => 'application/pdf']);
            @unlink(sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'backup_payroll_' . $payroll->payroll_id . '.pdf');
        }

        $fileName = 'Payroll ' . $payroll->payroll_start_date . ' - ' . $payroll->payroll_end_date;
        $this->mpdf->Output($fileName . '.pdf', 'I');
    }

    function payroll_overview($payroll_id = NULL, $pdf = FALSE)
    {
        if ($this->session->userdata('user_type') != "admin" && !$this->session->userdata("RPS_PRO"))
            show_404();


        $data['title'] = $this->_title . ' - Payroll Overview';
        $data['payroll_id'] = $payroll_id;

        $this->load->model('mdl_payroll');
        $this->load->model('mdl_reports');
        $this->load->model('mdl_worked');
        $this->load->model('mdl_deductions');
        $this->load->model('mdl_schedule');
        $this->load->model('mdl_employee');
        $this->load->model('mdl_user');
        $this->load->model('mdl_employees');

        $date = date('Y-m-d');
        if (!$payroll_id)
            $data['payroll'] = $this->mdl_payroll->get_by(array('payroll_start_date <=' => $date, 'payroll_end_date >=' => $date));
        else
            $data['payroll'] = $this->mdl_payroll->get($payroll_id);


        if (!$data['payroll'])
            show_404();

        $data['pdf'] = $pdf;

        $data['payroll_id'] = $payroll_id;

        $payroll_id = $data['payroll']->payroll_id;
        $data['payroll_id'] = $payroll_id;
        $data['users'] = [];
        $users = $this->mdl_user->get_payroll_user([/*'emp_field_estimator' => 1,*/ 'users.active_status' => 'yes'], 'employees.emp_type ASC, employees.emp_name ASC');
        if ($users) {
            $users = $users->result();
            foreach ($users as $key => $val)
                $data['users'][$val->employee_id] = $val;
        }
        $empTypes = array('employee', 'subcontractor', 'temp/cash');
        $data['total'] = new stdClass();
        $data['total']->worked_payed = 0;
        $data['total']->worked_total_pay = 0;

        foreach ($empTypes as $val) {
            $data['payroll_overview_report'][$val]['employees'] = $this->mdl_worked->get_payroll_overview_employees(['worked_payroll_id' => $payroll_id, 'emp_type' => $val], TRUE);

            $data['payroll_overview_report'][$val]['total'] = new stdClass();
            $data['payroll_overview_report'][$val]['total']->worked_payed = 0;
            $data['payroll_overview_report'][$val]['total']->worked_total_pay = 0;

            foreach ($data['payroll_overview_report'][$val]['employees'] as $key => $employeePayroll) {
                $data['payroll_overview_report'][$val]['total']->worked_payed += $employeePayroll->worked_payed;
                $data['payroll_overview_report'][$val]['total']->worked_total_pay += $employeePayroll->worked_total_pay;

                $id = $data['payroll_overview_report'][$val]['employees'][$key]->id;


                if ($data['payroll_overview_report'][$val]['employees'][$key]->emp_feild_worker)
                    $data['payroll_overview_report'][$val]['employees'][$key]->empmhrs = worker_mhrs_and_total($id, $data['payroll']->payroll_start_date, $data['payroll']->payroll_end_date);

                if ($data['payroll_overview_report'][$val]['employees'][$key]->emp_field_estimator)
                    $data['payroll_overview_report'][$val]['employees'][$key]->estmhrs = estimator_mhrs_and_total($id, $data['payroll']->payroll_start_date, $data['payroll']->payroll_end_date);


            }

        }

        $data['total'] = $this->mdl_reports->get_payroll_sum(['worked_payroll_id' => $payroll_id]);


        if ($this->input->get('payroll_overview_csv')) {
            $this->payroll_overview_csv($data);
        } elseif (!$pdf) {
            $this->load->view('index_payroll_overview', $data);
        } else {
            $this->load->library('mpdf');
            $this->mpdf->_setPageSize('Letter', $this->mpdf->DefOrientation);
            $html = $this->load->view('payroll_overview_new_pdf', $data, TRUE);
            $this->mpdf->WriteHTML($html);
            $file = 'Payroll Overview ' . $data['payroll']->payroll_start_date . ' - ' . $data['payroll']->payroll_end_date . '.pdf';
            $this->mpdf->Output($file, 'I');
        }
    }

    function newUser($id = NULL)
    {
        $result['status'] = 'error';
        if (!$id)
            $id = intval($this->input->post('id'));
        $employee = $this->employee_model->find_employee($id);
        $this->load->model('mdl_user');
        $user['user_type'] = 'user';
        $user['emailid'] = $employee->emp_username;
        $user['password'] = md5('123456');
        $name = explode(" ", $employee->emp_name);

        $user['firstname'] = $name[0];
        $user['lastname'] = $name[1];
        $user['added_on'] = date('Y-m-d H:i:s');
        $user['updated_on'] = date('Y-m-d H:i:s');
        $user['active_status'] = 'yes';
        $user['rate'] = $employee->emp_hourly_rate;
        $user['user_email'] = $employee->emp_email;
        $result['id'] = $this->mdl_user->insert_user($user);
        $meta['user_id'] = $result['id'];
        $meta['address1'] = $employee->emp_address1;
        $meta['address2'] = $employee->emp_address2;
        $meta['city'] = $employee->emp_city;
        $meta['state'] = $employee->emp_state;
        $meta['country'] = config_item('office_country');
        $this->mdl_user->insert_usermeta($meta);
        //insert_user
        if ($result['id'])
            $result['status'] = 'ok';
        die(json_encode($result));
    }

    private function payroll_overview_csv($data)
    {
        $csv_data = [];
        $count = 0;
        $payroll_overview_report = $data['payroll_overview_report'];
        $users = $data['users'];

        foreach ($payroll_overview_report as $key => $payroll) {
            array_push($csv_data, ['header' => ['mergeCells' => [9, ucfirst($key)]]]); // merge cell count , cellName

            if (count($payroll['employees'])) {
                array_push($csv_data, ['#', 'Name', 'Hours', 'AVG Rate', 'To Pay', 'Deductions', 'AVG MHR', 'Total R', 'Late']);

                foreach ($payroll['employees'] as $employee) {
                    $avg = '-';
                    $total = '-';
                    if (isAdmin()) {
                        if (isset($employee->empmhrs)) {
                            $avg = money(element('mhrs_return2', $employee->empmhrs, 0));
                            $total = money((element('total2', $employee->empmhrs, 0) + element('total2', $employee->empmhrs, 0)) / 2) . ' (EMP.)';
                        }
                        if (isset($employee->estmhrs)) {
                            $avg = money(element('mhrs_return', $employee->estmhrs, 0));
                            $total = money(element('total', $employee->estmhrs, 0)) . ' (EST.)';
                        }
                    }

                    $employeeRow = [
                        ++$count,
                        $employee->emp_name,
                        $employee->worked_payed,
                        (isAdmin() ? money(round($employee->worked_rate, 4)) : '-'),
                        (isAdmin() ? money(round($employee->worked_total_pay, 2)) : '-'),
                        (isAdmin() ? money(($employee->deduction_amount ? $employee->deduction_amount : 0)) : '-'),
                        preg_replace('!\s+!', ' ', trim($avg)),
                        preg_replace('!\s+!', ' ', trim($total)),
                        $employee->worked_lates
                    ];

                    array_push($csv_data, $employeeRow);
                    unset($users[$employee->employee_id]);
                }
            } else {
                array_push($csv_data, ['header' => ['mergeCells' => [9, 'No record found']]]);
            }
            // add separator empty row
            array_push($csv_data, [
                'Total hours for \'' . ucfirst($key) . '\':', isset($payroll['total']->worked_payed) ? $payroll['total']->worked_payed : 0,
                (isAdmin() ? ('Total to pay for \'' . ucfirst($key)) : null) . '\': ', (isAdmin() ? money((isset($payroll['total']->worked_total_pay) ? $payroll['total']->worked_total_pay : 0)) : null)
            ]);
            array_push($csv_data, [null]);
        }

        // add 'Did Not Work' table data
        array_push($csv_data, ['header' => ['mergeCells' => [5, 'Did Not Work']]]);
        array_push($csv_data, ['#', 'Name', 'Rate', 'Total Bonuses', 'Type']);

        if (count($users)) {
            foreach ($users as $user) {
                array_push($csv_data, [++$count, $user->emp_name, money(($user->emp_hourly_rate ? $user->emp_hourly_rate : 0)), money(0), ucfirst($user->emp_type)]);
            }
        } else {
            array_push($csv_data, ['header' => ['mergeCells' => [5, 'No record found']]]);
        }

        array_push($csv_data, [null]);
        // add total data
        if (isAdmin()) {
            array_push($csv_data, [
                'Total Hours: ' . (isset($data['total']['sum_hours']) ? $data['total']['sum_hours'] : 0),
                'Total To Pay: ' . money(isset($data['total']['sum']) ? $data['total']['sum'] : 0),
                'AVG To Pay: ' . money($count ? round($data['total']['sum'] / $count, 2) : 0),
                'Payroll Bonuses:' . money(0),
                'Total Bonuses: ' . money(0)
            ]);
        } else {
            array_push($csv_data, ['Total Hours', isset($data['total']['sum_hours']) ? $data['total']['sum_hours'] : 0]);
        }

        $file_name = 'Payroll Overview ' . $data['payroll']->payroll_start_date . ' - ' . $data['payroll']->payroll_end_date . '.csv';
        save_csv($file_name, $csv_data, true);
    }

    /**
     * @param $data
     * @return bool
     */
    public function hasLunch($data): bool
    {
        foreach ($data as $day) {
            if ($day->worked_lunch) {
                return true;
            }
        }

        return false;
    }

}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */
