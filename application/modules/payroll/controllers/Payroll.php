<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

use ElephantIO\Client as WSClient;
use ElephantIO\Engine\SocketIO\Version1X;

use application\modules\employees\controllers\Employees;
use application\modules\employees\models\EmployeeWorked;
use application\modules\employees\models\EmployeeLogin;
use application\modules\employees\models\Employee as EmployeeModel;
use application\modules\user\models\User as UserModel;
use application\modules\schedule\models\Expense;
use application\modules\schedule\models\ExpenseType;
use Carbon\Carbon;
use application\modules\user\models\User;

class Payroll extends MX_Controller
{
    public $employeeWorked;
    public $_title;

    function __construct()
    {
        parent::__construct();
        $this->load->library('form_validation');
        $this->load->model('mdl_worked');
        if (!isUserLoggedIn()) {
            redirect('login');
        }

        $this->_title = SITE_NAME;

        $this->employeeWorked = new EmployeeWorked();
    }

    function ajax_reload_workspace()
    {
        $this->load->model('mdl_emp_login');
        $this->load->model('mdl_payroll');
        $this->load->model('mdl_worked');
        $this->load->model('mdl_employees', 'employee_model');

        $employee_id = $this->input->post('employee_id');
        $payroll_id = $this->input->post('payroll_id');

        $data['employee_id'] = $employee_id;
        $data['payroll_id'] = $payroll_id;

        $date = date('Y-m-d');
        if (!$payroll_id)
            $data['payroll'] = $this->mdl_payroll->get_by(array('payroll_start_date <=' => $date, 'payroll_end_date >=' => $date));
        else
            $data['payroll'] = $this->mdl_payroll->get($payroll_id);

        $payroll_id = $data['payroll']->payroll_id;

        $data['employee'] = $this->employee_model->find_by_id($employee_id);

        if (!$data['payroll'] || !$data['employee'])
            show_404();

        $data['weeks'][] = array('week_start_time' => strtotime($data['payroll']->payroll_start_date), 'week_end_time' => strtotime('Sunday this week', strtotime($data['payroll']->payroll_start_date)));
        $data['weeks'][] = array('week_start_time' => strtotime('Monday next week', strtotime($data['payroll']->payroll_start_date)), 'week_end_time' => (strtotime($data['payroll']->payroll_end_date)));

        $worked_data = $this->mdl_worked->with('mdl_emp_login')->get_many_by('worked_payroll_id', $data['payroll']->payroll_id);

        foreach ($worked_data as $row)
            $data['worked_data'][strtotime($row->worked_date)] = $row;

        foreach ($data['weeks'] as $week) {
            $where_week = array(
                'worked_employee_id' => $employee_id,
                'worked_date >=' => date('Y-m-d', $week['week_start_time']),
                'worked_date <=' => date('Y-m-d', $week['week_end_time']),
                'worked_payroll_id' => $payroll_id
            );
            $data['weeks_data'][] = $this->mdl_worked->get_worked_hours_total($where_week, 1);
        }

        $where_week = array(
            'worked_employee_id' => $employee_id,
            'worked_payroll_id' => $payroll_id
        );
        $data['payroll_total_data'] = $this->mdl_worked->get_worked_hours_total($where_week, 1);

        $this->load->view('reports/payroll_employee_workspace', $data);
    }

    function ajax_save_time()
    {
        $this->load->model('mdl_emp_login');
        $this->load->model('mdl_worked');
        $id = $this->input->post('login_id') ? $this->input->post('login_id') : FALSE;
        $data['login'] = $this->input->post('login') ? $this->input->post('login') : NULL;
        $data['logout'] = $this->input->post('logout') ? $this->input->post('logout') : NULL;
        $employee_id = $this->input->post('employee_id') ? $this->input->post('employee_id') : FALSE;
        $date = $this->input->post('date') ? $this->input->post('date') : FALSE;
        $loginWithoutLogout = $logoutBetween = $loginBetween = $logoutInIssetRows = $isssetRowsInNewRow = [];
        $login_id = '';
        if (!$employee_id) {
            $updated = $this->mdl_emp_login->get($id);
            $employee_id = $updated->login_user_id;
            $date = $updated->login_date;
            $login_id = ' AND login_id != ' . $updated->login_id;
        }

        $where = 'login_user_id = ' . $employee_id . ' AND login_date = "' . $date . '"';

        $loginWithoutLogout = $this->mdl_emp_login->get_many_by($where . ' AND login IS NOT NULL AND logout IS NULL' . $login_id);

		if ($data['login'] == null && $data['logout'] == null) {
            return $this->response(['error' => 'Empty data']);
        }

        if ($data['logout'] && $data['logout'] != '00:00') {
            if ($data['login'] > $data['logout'])
                return $this->response(['error' => 'Time "clock in" less time "clock out"']);
            $logoutBetween = $this->mdl_emp_login->get_many_by($where . ' AND login < "' . $data['logout'] . '" AND logout >= "' . $data['logout'] . '"' . $login_id);

            $logoutInIssetRows = $this->mdl_emp_login->get_many_by($where . ' AND login > "' . $data['login'] . '" AND login < "' . $data['logout'] . '" AND logout >= "' . $data['logout'] . '"' . $login_id);

            $isssetRowsInNewRow = $this->mdl_emp_login->get_many_by($where . ' AND login >= "' . $data['login'] . '" AND logout <= "' . $data['logout'] . '"' . $login_id);

        }

        $loginBetween = $this->mdl_emp_login->get_many_by($where . ' AND login <= "' . $data['login'] . '" AND logout > "' . $data['login'] . '"' . $login_id);

        if (!empty($loginWithoutLogout) || !empty($logoutBetween) || !empty($logoutInIssetRows) || !empty($isssetRowsInNewRow) || !empty($loginBetween)) {
            return $this->response(['error' => 'Time "clock in" less previous time "clock out"']);
        } elseif ($id) {
            if ($this->input->post('is_allow_edit') === "0") {
                return false;
            }
            $this->mdl_emp_login->update($id, $data);
        } else {
            $data['login_user_id'] = $this->input->post('employee_id');
            //$data['login_employee_id'] = $this->input->post('employee_id');
            $data['login_date'] = $this->input->post('date');
            $id = $this->mdl_emp_login->insert($data);
        }

        $login = $this->mdl_emp_login->get($id);
        $worked = $this->mdl_worked->get($login->login_worked_id);

        $data['login_id'] = $id;
        $data['login_diff'] = ($data['logout'] && $data['login']) ? date('H:i', strtotime($data['logout']) - strtotime($data['login']) + strtotime(date('Y-m-d'))) : 0;
        $data['worked_total'] = $worked->worked_hours;
        $data['worked_time'] = strtotime($worked->worked_date);
        $data['worked_date'] = $worked->worked_date;

        $this->response($data);
    }

    function ajax_delete_time()
    {
        $this->load->model('mdl_emp_login');
        $this->load->model('mdl_worked');
        $id = $this->input->post('login_id');

        $login = $this->mdl_emp_login->get($id);
        if (!$login || empty($login)) {

            if (!$this->input->post('user_id') || !$this->input->post('login_date'))
                die(json_encode(['status' => 'error', 'msg' => 'Warning! This time record is not valid.']));

            $worked = $this->mdl_worked->get_by(['worked_user_id' => $this->input->post('user_id'), 'worked_date' => $this->input->post('login_date')]);


            $data['login_id'] = $id;
            $data['worked_total'] = isset($worked->worked_hours) ? $worked->worked_hours : 0;
            $data['worked_time'] = strtotime($this->input->post('login_date'));
            $data['worked_date'] = $this->input->post('login_date');

            die(json_encode($data));
        }

        $this->mdl_emp_login->delete($id);

        $data = ['login_id' => $id, 'worked_total' => 0, 'worked_time' => 0];
        if ($login) {
            $worked = $this->mdl_worked->get($login->login_worked_id);
            $data['worked_time'] = strtotime($login->login_date);
            $data['worked_date'] = $login->login_date;
        }


        if (isset($worked) && $worked) {
            $data['login_id'] = $id;
            $data['worked_total'] = isset($worked->worked_hours) ? $worked->worked_hours : 0;
            $data['worked_date'] = isset($worked->worked_date) ? $worked->worked_date : null;
        }

        if ($this->config->item('wsClient')) {
            $wsClient = new WSClient(new Version1X($this->config->item('wsClient') . '?chat=1&user_id=' . $login->login_user_id));
            if ($wsClient) {
                $wsClient->initialize();
                $wsClient->emit('room', ['chat-' . $login->login_user_id]);
                $wsClient->emit('message', ['method' => 'trackerHistoryChanged', 'params' => ['date' => $login->login_date]]);
                $wsClient->close();
            }
        }

        die(json_encode($data));
    }

    function ajax_change_lunch()
    {
        //New Table.
        $response['status'] = 'ok';
        $this->load->model('mdl_worked');
        $worked_id = $this->input->post('worked_id');
        $value = $this->input->post('lunch');
        $lunch = str_replace(',', '.', $value);
        $data['worked_lunch'] = (float)$lunch;
        $worked = $this->mdl_worked->get($worked_id);
        $this->mdl_worked->update($worked_id, $data);
        if ($this->config->item('wsClient')) {
            $wsClient = new WSClient(new Version1X($this->config->item('wsClient') . '?chat=1&user_id=' . $worked->worked_user_id));
            if ($wsClient) {
                $wsClient->initialize();
                $wsClient->emit('room', ['chat-' . $worked->worked_user_id]);
                $wsClient->emit('message', ['method' => 'trackerHistoryChanged', 'params' => ['date' => $worked->worked_date]]);
                $wsClient->close();
            }
        }
        $response['value'] = $value;
        return $this->response($response);
    }

    function ajax_change_rate()
    {
        $response['status'] = 'ok';
        $this->load->model('mdl_worked');
        $worked_id = $this->input->post('worked_id');
        $value = $this->input->post('rate');
        $rate = str_replace(',', '.', $value);
        $data['worked_hourly_rate'] = (float)$rate;
        $this->mdl_worked->update($worked_id, $data);
        $worked_data = $this->mdl_worked->get_by(array('worked_id' => $worked_id));
        $response['value'] = $value;
        return $this->response($response);
    }

    function ajax_change_deductions()
    {
        $result['status'] = 'ok';
        $data['deduction_user_id'] = $this->input->post('employee_id');
        //$data['deduction_employee_id'] = $this->input->post('employee_id');
        $data['deduction_payroll_id'] = $this->input->post('payroll_id');
        $data['deduction_amount'] = $this->input->post('deduction_amount');
        $deduction_id = $this->input->post('deduction_id');

        $this->load->model('mdl_deductions');
        if ($deduction_id)
            $this->mdl_deductions->update($deduction_id, $data);
        else
            $this->mdl_deductions->insert($data);
        $deduction = $this->mdl_deductions->get_by(array('deduction_id' => $deduction_id));
        $result['deduction'] = $deduction;
        die(json_encode($result));
    }

    function date_validation($date)
    {
        if (!strtotime($date)) {
            $this->form_validation->set_message(__FUNCTION__, "Wrong date field");

            return false;
        }
        return true;
    }

    public function range_report($employee_id = null, $range_start = null, $range_end = null)
    {
        $this->load->helper('payroll');

        $result['title'] = $this->_title . ' - Payroll Custom Date Range';
        $result['employee_id'] = $employee_id;

        $selectRaw = "
                users.*,
                e.*,
                users.id as employee_id,
                CONCAT(users.firstname, ' ', users.lastname) as emp_name,
                um.address1,
                um.address2,
                um.city,
                um.state,
                um.country";

        $result['employees'] = UserModel::selectRaw($selectRaw)
            ->leftJoin('user_meta as um', 'users.id', '=', 'um.user_id')
            ->leftJoin('employees as e', 'users.id', '=', 'e.emp_user_id')
            ->where('user_active_employee', '=', 1)
            ->orderBy('e.emp_status', 'ASC')
            ->orderBy('users.active_status', 'ASC')
            ->orderBy('e.emp_name', 'ASC')
            ->groupBy('users.id')
            ->get()
            ->toArray();

        $result['employee'] = UserModel::selectRaw($selectRaw)
            ->leftJoin('user_meta as um', 'users.id', '=', 'um.user_id')
            ->leftJoin('employees as e', 'users.id', '=', 'e.emp_user_id')
            ->where('users.id', '=', $employee_id)
            ->first();

        $result['payroll'] = $this->range_report_table($employee_id, $range_start, $range_end, true);
        $result['total'] = $this->range_report_total($employee_id, $range_start, $range_end, true);

        $during = User::find($this->session->user_id)->during;
        $result['is_allow_edit'] = true;

        if ($during != 0 && isset($result['payroll']) &&
            !empty($result['payroll']) &&
            isset($result['payroll']['payroll_end_date']) &&
            !empty($result['payroll']['payroll_end_date'])
        ) {
            $result['is_allow_edit'] = Carbon::now()->format('Y-m-d') <= Carbon::create($result['payroll']['payroll_end_date'])->addDays($during)->format('Y-m-d');
        }

        return $this->load->view('index_payroll', $result);
    }

    public function range_report_table($employee_id = null, $range_start = null, $range_end = null, $getData = false)
    {
        $data = [];

        $input = $this->input->post();
        $emp_id = $data['employee_id'] = $employee_id ?? $input['employee_id'];
        $start = $data['payroll_start_date'] = $range_start ?? $input['start'];
        $end = $data['payroll_end_date'] = $range_end ?? $input['end'];

        $getWorkedModel = EmployeeWorked::where([
            ['worked_date', '>=', $start],
            ['worked_date', '<=', $end],
            ['worked_user_id', '=', $emp_id],
        ])
            ->get()
            ->toArray();

        $expenses = Expense::timesRange(strtotime($start), strtotime($end . ' 23:59:59'))
            ->user($emp_id)->whereHas('type', function ($query) {
                return $query->whereSlug(ExpenseType::EMPLOYEE_BENEFITS);
            })->get();

        $getLoginModel = EmployeeLogin::where('login_user_id', '=', $emp_id)
            ->whereBetween('login_date', [$start, $end])
            ->get()
            ->toArray();

        foreach ($getWorkedModel as $workday) {
            $worked[strtotime($workday['worked_date'])] = $workday;
        }

        $datesDiff = strtotime('Sunday this week ' . $end) - strtotime('Monday this week ' . $start);
        $weeksNum = (int)ceil($datesDiff / (3600 * 24 * 7));
        $rangeStart = (new DateTime())->modify('Monday this week ' . $start)->format('d F Y');
        $rangeEnd = (new DateTime())->modify('Sunday this week ' . $end)->format('d F Y');

        $currentDay = 0;
        for ($i = 1; $i <= $weeksNum; $i++) {
            $avgRate = [];
            for ($y = 1; $y <= 7; $y++) {
                $cursor = date('Y-m-d', strtotime('+' . $currentDay . ' days' . $rangeStart));

                $bld = $expenses->whereBetween('expense_date', [strtotime($cursor), strtotime($cursor . '23:59:59')])->where('expense_is_extra', '=', 0)->first();
                $extra = $expenses->whereBetween('expense_date', [strtotime($cursor), strtotime($cursor . '23:59:59')])->where('expense_is_extra', '=', 1)->first();

                $data['weeks'][$i]['date'][] = $cursor;
                $data['weeks'][$i]['time_in'][strtotime($cursor)] = ($cursor >= $start && $cursor <= $end) ?
                    (isset($worked[strtotime($cursor)]) ?
                        substr($worked[strtotime($cursor)]['worked_start'], 0, -3) : '00:00') : null;
                $data['weeks'][$i]['time_out'][strtotime($cursor)] = ($cursor >= $start && $cursor <= $end) ?
                    (isset($worked[strtotime($cursor)]) ?
                        substr($worked[strtotime($cursor)]['worked_end'], 0, -3) : '00:00') : null;
                $data['weeks'][$i]['lunch'][strtotime($cursor)] = ($cursor >= $start && $cursor <= $end) ?
                    (isset($worked[strtotime($cursor)]) && $worked[strtotime($cursor)]['worked_lunch'] > 0 ?
                        $worked[strtotime($cursor)]['worked_lunch'] : 0) : null;
                $data['weeks'][$i]['worked'][strtotime($cursor)] = ($cursor >= $start && $cursor <= $end) ?
                    (isset($worked[strtotime($cursor)]) ?
                        ($worked[strtotime($cursor)]['worked_hours'] - $worked[strtotime($cursor)]['worked_lunch']) . ' hrs.' : '-') : null;
                $data['weeks'][$i]['rate'][strtotime($cursor)] = ($cursor >= $start && $cursor <= $end) ?
                    (isset($worked[strtotime($cursor)]) ?
                        money($worked[strtotime($cursor)]['worked_hourly_rate']) : '-') : null;
                $data['weeks'][$i]['mhr'][strtotime($cursor)] = ($cursor >= $start && $cursor <= $end) ? '-' : null;
                $data['weeks'][$i]['teams'][strtotime($cursor)] = ($cursor >= $start && $cursor <= $end) ? '-' : null;
                $data['weeks'][$i]['bld'][strtotime($cursor)] = (($cursor >= $start && $cursor <= $end) && $bld) ? $bld : null;
                $data['weeks'][$i]['expenses'][strtotime($cursor)] = (($cursor >= $start && $cursor <= $end) && $extra) ? $extra : null;

                $data['weeks'][$i]['lates'][strtotime($cursor)] = ($cursor >= $start && $cursor <= $end && isset($worked[strtotime($cursor)]['worked_late'])) ?
                    (int)$worked[strtotime($cursor)]['worked_late'] : 0;

                if (isset($worked[strtotime($cursor)]))
                    $data['weeks'][$i]['worked_data'][strtotime($cursor)] = $worked[strtotime($cursor)];

                if (isset($worked[strtotime($cursor)]['worked_hourly_rate'])) {
                    $avgRate[] = (float)$worked[strtotime($cursor)]['worked_hourly_rate'];
                }

                $currentDay++;
            }
            //weekly total
            $data['weeks'][$i]['late'] = array_sum($data['weeks'][$i]['lates']);
            $data['weeks'][$i]['avg_rate'] = count($avgRate) > 0 ? array_sum($avgRate) / count($avgRate) : 0;
            $data['weeks'][$i]['total_hours'] = array_sum($data['weeks'][$i]['worked']);
        }

        $data['user_id'] = $emp_id;
        $data['logins'] = $getLoginModel;

        if (false === $getData) {
            return $this->load->view('payroll_date_range_table', $data);
        }

        if (true === $getData) {
            return $data;
        }
    }

    public function range_report_total($employee_id = null, $range_start = null, $range_end = null, $getData = false)
    {
        $input = $this->input->post();
        $emp_id = $data['employee_id'] = $employee_id ?? $input['employee_id'];
        $start = $data['range_start'] = $range_start ?? $input['start'];
        $end = $data['range_end'] = $range_end ?? $input['end'];

        $data['employee'] = EmployeeModel::where('emp_user_id', '=', $employee_id)->first();

        $getWorkedModel = EmployeeWorked::selectRaw('
                (SUM(worked_hours) - SUM(worked_lunch)) as total_hours,
                ROUND(AVG(worked_hourly_rate), 2) as rate,
                ROUND(AVG(worked_hourly_rate) * (SUM(worked_hours) - SUM(worked_lunch)), 2) as total_pay,
                SUM(worked_late) as late
            ')
            ->where([
                ['worked_date', '>=', $start],
                ['worked_date', '<=', $end],
                ['worked_user_id', '=', $emp_id],
            ])->get()->toArray();

        $data['payroll_total_data'] = $getWorkedModel[0];

        if (false === $getData) {
            return $this->load->view('payroll_date_range_biweekly_table', $data);
        }

        if (true === $getData) {
            return $data;
        }
    }

    public function range_report_pdf($employee_id = null, $start = null, $end = null)
    {
        $employee = EmployeeModel::where('emp_user_id', '=', $employee_id)->first();
        $table = $this->range_report($employee_id, $start, $end, true);
        $total = $this->range_report_total($employee_id, $start, $end, true);

        if (null === $employee) {
            die('Empty data');
        }

        $data = array_merge($table, $total);
        $data['employee'] = $employee;
        $data['work_table'] = $table;
        $data['work_total'] = $total;
        $data['pdf'] = true;
        $data['title'] = str_replace(' ', '_', $data['employee']['emp_name']) . '_[' . $start . '_' . $end . ']';

        $this->load->library('mpdf');

        $this->mpdf->defaultPagebreakType = 'clonebycss';
        $this->mpdf->autoPageBreak = true;
        $this->mpdf->debug = false;

        $estHtml = $this->estimatorHtml($employee_id, null, ['start' => $start, 'end' => $end]);
        $empHtml = $this->fieldworkrerHtml($employee_id, null, ['start' => $start, 'end' => $end]);

        $this->mpdf->_setPageSize('Letter', $this->mpdf->DefOrientation);
        $this->mpdf->AddPage('P', 'Letter', 0, '', 0, 0, 0, 5, 0, 0);
        $this->mpdf->SetHtmlFooter('');
        $html = $this->load->view('payroll_date_range_pdf', $data, TRUE);
        @$this->mpdf->WriteHTML((string)$html);

        if ($estHtml) {
            $this->mpdf->AddPage('P', 'Letter', 0, '', 2, 2, 2, 2, 0, 0);
            $this->mpdf->SetHtmlFooter('<div class="text-right">' . $data['employee']['emp_name'] . '</div>');
            $this->mpdf->WriteHTML($estHtml);
        }

        if ($empHtml) {
            $this->mpdf->AddPage('L', 'Letter', 0, '', 2, 2, 2, 2, 0, 0);
            $this->mpdf->SetHtmlFooter('<div class="text-left">' . $data['employee']['emp_name'] . '</div>');
            $this->mpdf->WriteHTML($empHtml);
        }

        $file = $data['employee']->emp_name . ' ' . $start . ' - ' . $end . '.pdf';
        $this->mpdf->Output($file, 'I');
    }

    private function estimatorHtml($employee_id, $payroll_id = NULL, $date_range = null)
    {
        if ($employee_id == 28 and $payroll_id == 140)
            return false;
        $this->load->model('mdl_payroll');
        $this->load->model('mdl_workorders');
        $this->load->model('mdl_schedule');

        $estData['title'] = $this->_title . ' - Comission';

        $data['payroll'] = [
            'payroll_start_date' => date('Y-m-d', strtotime('Monday this week', strtotime($date_range['start']))),
            'payroll_end_date' => date('Y-m-d', strtotime('Sunday this week', strtotime($date_range['end']))),
        ];

        $totalWeeks = ceil((strtotime($date_range['end']) - strtotime($date_range['start'])) / (3600 * 24 * 7));

        if (date("w", strtotime($data['payroll']['payroll_start_date'])) == 0) { //starts from sunday

            $six_day_int = 86400 * 6;

            $w_start_time = strtotime($data['payroll']['payroll_start_date']);
            $w_end_time = $w_start_time + $six_day_int;
            $estData['weeks'][] = [
                'week_start_time' => $w_start_time,
                'week_end_time' => $w_end_time
            ];
            $w_end_time = strtotime($data['payroll']['payroll_end_date']);
            $w_start_time = $w_end_time - $six_day_int;
            $estData['weeks'][] = [
                'week_start_time' => $w_start_time,
                'week_end_time' => $w_end_time];

        } else { //starts from monday
            $firstDay = strtotime('Monday this week', strtotime($date_range['start']));

            for ($w = 1; $w <= $totalWeeks; $w++) {
                if ($w == 1) {
                    $estData['weeks'][$w] = [
                        'week_start_time' => $firstDay,
                        'week_end_time' => strtotime('Sunday this week', $firstDay),
                    ];
                } else {
                    $estData['weeks'][$w] = [
                        'week_start_time' => strtotime('+ ' . ($w - 1) . ' weeks', $firstDay),
                        'week_end_time' => strtotime('+ ' . ($w - 1) . ' weeks', strtotime('Sunday this week', $firstDay)),
                    ];
                }
            }
        }

        //$estData['weeks'][] = array('week_start_time' => strtotime($data['payroll']->payroll_start_date), 'week_end_time' => strtotime('Sunday this week', strtotime($data['payroll']->payroll_start_date)));
        //$estData['weeks'][] = array('week_start_time' => strtotime('Monday next week', strtotime($data['payroll']->payroll_start_date)), 'week_end_time' => (strtotime($data['payroll']->payroll_end_date)));

        $employee = \application\modules\employees\models\Employee::where('emp_user_id', '=', $employee_id)->first();

        if (!$employee)
            return FALSE;

        $data['employee'] = $employee->toArray();

        if (!$data['employee']['emp_field_estimator'])
            return FALSE;

        $this->comissionMore = 0;
        $this->comissionLess = 0;

        $finishedStatusId = $this->mdl_workorders->getFinishedStatusId();

        $workordersData = $this->mdl_workorders->get_scheduled_workorders_by_status_date([
            'user_id' => $employee_id,
            'status_log.status_value' => $finishedStatusId,
            'status_log.status_date >=' => strtotime($date_range['start']),
            'status_log.status_date <=' => strtotime($date_range['end']),
            'workorders.wo_status' => $finishedStatusId,
        ]);

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
            $value['totalMhrsReturn'] = $value['totalForReturn'] ? round($value['totalForReturnWithDamages'] / $value['totalHours'], 2) : 0;
            if (!$value['count_other_finished']) {
                if ($value['totalMhrsReturn'] >= GOOD_MAN_HOURS_RETURN)
                    $this->comissionMore += $value['scheduled_wo_price'];
                else
                    $this->comissionLess += $value['scheduled_wo_price'];
            }

            foreach ($estData['weeks'] as $weekNum => $weekData) {
                $valueDate = (int)$value['status_date'];
                if ($valueDate >= $weekData['week_start_time'] && $valueDate <= $weekData['week_end_time']) {
                    $estData['payrollWorkorders'][$weekNum][date('Y-m-d', $valueDate)][] = $value;
                    break;
                }
            }
        }

        $html = $this->load->view('payroll_estimator_report_comission_workspace_pdf', $estData, TRUE);
//        var_dump($html); die;
        return $html;
    }

    private function fieldworkrerHtml($employee_id, $payroll_id = NULL, $date_range = null)
    {
        $this->load->model('mdl_schedule');
        $this->load->model('mdl_crews_orm');

        $empData = [];

        $empData['title'] = $this->_title . ' - Comission';
        $empData['date_range'] = $date_range;

        $data['payroll'] = [
            'payroll_start_date' => date('Y-m-d', strtotime('Monday this week', strtotime($date_range['start']))),
            'payroll_end_date' => date('Y-m-d', strtotime('Sunday this week', strtotime($date_range['end']))),
        ];

        $totalWeeks = ceil((strtotime($date_range['end']) - strtotime($date_range['start'])) / (3600 * 24 * 7));

        $ev_add_time = 0;

        if (date("w", strtotime($data['payroll']['payroll_start_date'])) == 0) { //starts from sunday

            $six_day_int = 86400 * 6;

            $w_start_time = strtotime($data['payroll']->payroll_start_date);
            $w_end_time = $w_start_time + $six_day_int;
            $empData['weeks'][] = array('week_start_time' => $w_start_time, 'week_end_time' => $w_end_time);
            $w_end_time = strtotime($data['payroll']->payroll_end_date);
            $w_start_time = $w_end_time - $six_day_int;
            $empData['weeks'][] = array('week_start_time' => $w_start_time, 'week_end_time' => $w_end_time);

            $ev_add_time = 86400; //plus one day to get schedule events

        } else { //starts from monday
            $firstDay = strtotime('Monday this week', strtotime($date_range['start']));

            for ($w = 1; $w <= $totalWeeks; $w++) {
                if ($w == 1) {
                    $empData['weeks'][$w] = [
                        'week_start_time' => $firstDay,
                        'week_end_time' => strtotime('Sunday this week', $firstDay),
                    ];
                } else {
                    $empData['weeks'][$w] = [
                        'week_start_time' => strtotime('+ ' . ($w - 1) . ' weeks', $firstDay),
                        'week_end_time' => strtotime('+ ' . ($w - 1) . ' weeks', strtotime('Sunday this week', $firstDay)),
                    ];
                }
            }
        }

        $events = $this->mdl_schedule->get_events_dashboard([
            'schedule.event_end >=' => strtotime($date_range['start']),
            'schedule.event_end <=' => strtotime($date_range['end']) + $ev_add_time,
            'schedule_teams_members.user_id' => $employee_id
        ]);

        /*echo $data['payroll']->payroll_start_date . ' - ' . $data['payroll']->payroll_end_date;
        echo "<pre>";
        echo $this->db->last_query();
        var_dump($events);
        die;*/

        if (!$events)
            return FALSE;

        $data['employee'] = \application\modules\employees\models\Employee::where('emp_user_id', '=', $employee_id)->first();

        if (!$data['employee']->emp_feild_worker)
            return FALSE;

        $teams = [];

        $whereTeams['schedule_teams.team_date >='] = strtotime($date_range['start']);
        $whereTeams['schedule_teams.team_date <='] = strtotime($date_range['start'] . ' 23:59:59');
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

        $html = $this->load->view('payroll_report_workspace_pdf', $empData, TRUE);
//        var_dump($html); die;
        return $html;
    }
}
