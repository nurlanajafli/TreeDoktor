<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

use ElephantIO\Client as WSClient;
use ElephantIO\Engine\SocketIO\Version1X;
use application\modules\employees\models\Employee;

class Tracker extends APP_Controller
{

    function __construct()
    {
        parent::__construct();
        $this->load->model('mdl_payroll');
        $this->load->model('mdl_worked');
        $this->load->model('mdl_emp_login');
    }

    function index($date = NULL)
    {
        $data["login"] = false;
        $data["logout"] = false;
        $data["login_time"] = NULL;
        $data["logout_time"] = NULL;
        $data["login_rec_id"] = 0;
        $data["date"] = date('Y-m-d');
        $date = $date ? date('Y-m-d', strtotime($date)) : date('Y-m-d');
        $payroll = $this->mdl_payroll->get_by(array('payroll_start_date <=' => $date, 'payroll_end_date >=' => $date));
        if (date('N', strtotime($payroll->payroll_start_date)) == 1) {
            $firstDay = date("Y-m-d", strtotime('monday this week', strtotime($date)));
            $lastDay = date("Y-m-d", strtotime('sunday this week', strtotime($date)));
        } elseif (date('N', strtotime($payroll->payroll_start_date)) == 7) {
            if (date('N', strtotime($date)) == 7) {
                $firstDay = $date;
                $lastDay = date("Y-m-d", strtotime('saturday next week', strtotime($date)));
            } else {
                $firstDay = date("Y-m-d", strtotime('last sunday', strtotime($date)));
                $lastDay = date("Y-m-d", strtotime('saturday this week', strtotime($date)));
            }
        }

        $data['first_day'] = $firstDay;
        $data['last_day'] = $lastDay;

        $data["employee_id"] = $this->user->id;
        $data["user_name"] = $this->user->firstname . ' ' . $this->user->lastname;

        $employee_login_new_table = $this->mdl_emp_login->get_by(array('login_user_id' => $this->user->id, 'login_date' => date('Y-m-d'), 'logout' => NULL));
        if (empty((array)$employee_login_new_table))
            $employee_login_new_table = $this->mdl_emp_login->get_by(array('login_employee_id' => $this->user->id, 'login_date' => date('Y-m-d'), 'logout' => NULL));

        $worked = $this->mdl_worked->get_by(array('worked_date' => date('Y-m-d'), 'worked_user_id' => $this->user->id));
        if (!$worked)
            $worked = $this->mdl_worked->get_by(array('worked_date' => date('Y-m-d'), 'worked_employee_id' => $this->user->id));
        $data["time_diff"] = $worked ? $worked->worked_hours : 0;
        $data["today_lunch_seconds"] = $worked ? $worked->worked_lunch * 3600 : 0;
        if ($employee_login_new_table && !empty((array)$employee_login_new_table)) {
            $data["login"] = true;
            $data["login_time"] = date("H:i:s", strtotime($employee_login_new_table->login));
            $data["login_rec_id"] = $employee_login_new_table->login_id;
            $data["current_login_row"] = $employee_login_new_table;
        } else {
            if ($worked) {
                $data["login"] = true;
                $data["login_time"] = date("H:i:s", strtotime($worked->worked_start));
                $data["logout"] = true;
                $data["logout_time"] = date("H:i:s", strtotime($worked->worked_end));

            }
        }

        $todayCompletedLogins = $this->mdl_emp_login->get_many_by('login_user_id = ' . $this->user->id . ' AND login_date = "' . date("Y-m-d") . '" AND login IS NOT NULL AND logout IS NOT NULL');
        $data['today_tracked_seconds'] = 0;
        foreach ($todayCompletedLogins as $row) {
            $data['today_tracked_seconds'] += ((new DateTime($row->login_date . ' ' . $row->logout))->getTimestamp() - (new DateTime($row->login_date . ' ' . $row->login))->getTimestamp());
        }

        $payroll = $this->mdl_payroll->get_by(array('payroll_start_date <=' => $firstDay, 'payroll_end_date >=' => $lastDay));
        $days = $this->mdl_worked->order_by('worked_date', 'ASC')->with('mdl_emp_login', 'logins')->get_many_by(array(
            'worked_payroll_id' => $payroll->payroll_id,
            'worked_user_id' => $this->user->id,
            'worked_date >=' => $firstDay,
            'worked_date <=' => $lastDay,
        ));

        $currentDate = $firstDay;
        while (strtotime($currentDate) <= strtotime($lastDay)) {
            $data['days'][$currentDate] = [];
            $cur = DateTime::createFromFormat('Y-m-d', $currentDate)->modify('+1 day');
            $currentDate = $cur->format('Y-m-d');
        }

        foreach ($days as &$day) {
            $day->worked_tracked_seconds = 0;
            $day->worked_lunch_seconds = $day->worked_lunch * 3600;
            foreach ($day->mdl_emp_login as $row) {
                if ($row->login && $row->logout)
                    $day->worked_tracked_seconds += ((new DateTime($row->login_date . ' ' . $row->logout))->getTimestamp() - (new DateTime($row->login_date . ' ' . $row->login))->getTimestamp());
            }
            $data['days'][$day->worked_date] = $day;
        }

        $response = array(
            'status' => TRUE,
            'data' => $data,
        );
        return $this->response($response);
    }

    function timer()
    {
        $CI = &get_instance();
        $CI->timer = TRUE;

        $response = [
            'status' => FALSE,
            'message' => 'ERROR'
        ];

        if (!$this->input->post()) {
            return $this->response($response);
        }

        $loginRow = $this->mdl_emp_login->get_by('login_user_id = ' . $this->user->id . ' AND login_date = "' . date("Y-m-d") . '" AND login IS NOT NULL AND logout IS NULL');

        if (!$loginRow) {
            $wsMethod = 'trackerStarted';

            $loginTime = date("H:i:s");
            $login_time = date("Y-m-d H:i:s");
            $employees = Employee::where(['emp_user_id' => $this->user->id])->first();
            if ($employees->emp_check_work_time == '1' && $employees->emp_start_time != '00:00:00') {
                if ($loginTime < $employees->emp_start_time) {
                    $login_time = date("Y-m-d") . ' ' . $employees->emp_start_time;
                }
            }

            if ($this->input->post("lat") && $this->input->post("lng")) {
                $login['lat'] = $this->input->post("lat") ?: 'false';
                $login['lon'] = $this->input->post("lng") ?: 'false';

                $new_data["login_app_in_office"] = $this->geoDistance(
                    $login["lat"],
                    $login["lon"],
                    config_item('office_lat'),
                    config_item('office_lon')
                );
                //$new_data["login_office"] = (int) $this->in_office($login);
            }

            $new_data["login_lat"] = $this->input->post("lat") ?: 'false';
            $new_data["login_lon"] = $this->input->post("lng") ?: 'false';
            $new_data['login_date'] = date('Y-m-d', strtotime($login_time));
            $new_data['login'] = date('H:i:s', strtotime($login_time));
            $new_data['login_user_id'] = $this->user->id;
            $new_data['login_from_app'] = true;

            $new_rec_id = $this->mdl_emp_login->insert($new_data);

            $response = array(
                'status' => TRUE,
                'data' => [
                    "login_time" => date("H:i:s", strtotime($login_time)),
                    "new_rec_id" => $new_rec_id
                ],
            );
        } else {
            $wsMethod = 'trackerStopped';
            $newRow = $this->mdl_emp_login->get($loginRow->login_id);

            $logout_time = date("Y-m-d H:i:s");
            $logoutTime = date("H:i:s");
            $employees = Employee::where(['emp_user_id' => $this->user->id])->first();
            if ($employees->emp_check_work_time == '1' && $employees->emp_start_time != '00:00:00') {
                if ($logoutTime <= $employees->emp_start_time) {
                    $logout_time = date("Y-m-d") . ' ' . $employees->emp_start_time;
                }
            }

            $udata = [];

            if ($this->input->post("lat") && $this->input->post("lng")) {
                $login['lat'] = $this->input->post("lat") ?: 'false';
                $login['lon'] = $this->input->post("lng") ?: 'false';

                $udata["logout_app_in_office"] = $this->geoDistance(
                    $login['lat'],
                    $login['lon'],
                    config_item('office_lat'),
                    config_item('office_lon')
                );
            }
            $udata['logout_from_app'] = true;
            $udata["logout_lat"] = $this->input->post("lat") ?: 'false';
            $udata["logout_lon"] = $this->input->post("lng") ?: 'false';

            $udata['logout'] = date('H:i:s', strtotime($logout_time));
//            $udata['login_from_app'] = true;

            $this->mdl_emp_login->update($loginRow->login_id, $udata);
            $worked = $this->mdl_worked->get($newRow->login_worked_id);

            $todayCompletedLogins = $this->mdl_emp_login->get_many_by('login_user_id = ' . $this->user->id . ' AND login_date = "' . date("Y-m-d") . '" AND login IS NOT NULL AND logout IS NOT NULL');
            $todayTrackedSeconds = 0;
            foreach ($todayCompletedLogins as $row) {
                $todayTrackedSeconds += ((new DateTime($row->login_date . ' ' . $row->logout))->getTimestamp() - (new DateTime($row->login_date . ' ' . $row->login))->getTimestamp());
            }

            $response = array(
                'status' => TRUE,
                'data' => [
                    "login_time" => date("H:i:s", strtotime($loginRow->login)),
                    "logout_time" => date("H:i:s", strtotime($logout_time)),
                    "time_diff" => round((strtotime($logout_time) - strtotime($newRow->login)) / 3600, 2),
                    'total_time_diff' => $worked->worked_hours,
                    "today_tracked_seconds" => $todayTrackedSeconds
                ],
            );
        }

        if ($this->config->item('wsClient')) {
            $wsClient = new WSClient(new Version1X($this->config->item('wsClient') . '?chat=1&user_id=' . $this->user->id));
            if ($wsClient) {
                $wsClient->initialize();
                $wsClient->emit('room', ['chat-' . $this->user->id]);
                $wsClient->emit('message', ['method' => $wsMethod, 'params' => $response]);
                $wsClient->close();
            }
        }
        return $this->response($response);
    }

    public function geoDistance($lat1, $lon1, $lat2, $lon2)
    {
        if (
            $lat1 == false ||
            $lon1 == false ||
            $lat2 == false ||
            $lon2 == false
        ) {
            return 0;
        }

        // Earth's radius in metres (mean radius = 6,371km)
        $radius = 6371e3;
        $pi = M_PI;

        // Angles need to be radians to pass trig functions!
        $lat1Radian = ($lat1 * $pi) / 180;
        $lat2Radian = ($lat2 * $pi) / 180;
        $latDelta = (($lat2 - $lat1) * $pi) / 180;
        $longDelta = (($lon2 - $lon1) * $pi) / 180;

        // The square of half the chord length between the points
        $square = sin($latDelta / 2) * sin($latDelta / 2) +
            cos($lat1Radian) *
            cos($lat2Radian) *
            sin($longDelta / 2) *
            sin($longDelta / 2);

        // Angular distance in radians
        $radians = 2 * atan2(sqrt($square), sqrt(1 - $square));

        // Distance is the radius * angular distance
        $distance = $radius * $radians;

        return $distance <= 50 ? 1 : 0;
    }

    private function in_office($login)
    {
        $office_distance = 0.0045; // 500 meters
        $office['lat'] = brand_office_lat(default_brand());
        $office['lon'] = brand_office_lon(default_brand());

        if ($office['lat'] && $office['lon']) {

            $in_office_lat = ($office['lat'] + $office_distance > $login['lat'] && $office['lat'] - $office_distance < $login['lat']);
            $in_office_lon = ($office['lon'] + $office_distance > $login['lon'] && $office['lon'] - $office_distance < $login['lon']);

            return true === $in_office_lat && true === $in_office_lon;
        }

        return false;
    }
}
