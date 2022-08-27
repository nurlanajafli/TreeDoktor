<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

use Illuminate\Support\Carbon;

class Apppayroll extends APP_Controller
{
    public $_tz;

    function __construct() {
        parent::__construct();
        $this->load->model('mdl_schedule');
        $this->load->model('mdl_worked');
        $this->load->model('mdl_payroll');
        $this->load->model('mdl_emp_login');
    }

    public function getTeamMemberTime()
    {
        $postData = [
            'loggedUser'   => request()->user()->id,
            'teamMemberId' => $this->input->post('member'), //request()->post('member'),
            'teamWorkDate' => $this->input->post('date'), //request()->post('date'),
        ];

        if (in_array(null, $postData, true) || in_array('', $postData, true)) {
            return $this->response(['error' => 'Not enough data']);
        }

        $dateCreate = new Carbon($postData['teamWorkDate']);

        $postData['teamWorkDayStart']   = $dateCreate->copy()->startOfDay()->timestamp;
        $postData['teamWorkDayEnd']     = $dateCreate->copy()->endOfDay()->timestamp;

        if (false === $this->isMemberDayAllowed($postData)) {
            return $this->response(['error' => 'You don\'t have access to get this data']);
        }

        $workedHours = $this->getMemberWorkedHours($postData['teamMemberId'], $postData['teamWorkDate']);

        return $this->response(is_array($workedHours) ? $workedHours : []);
    }

    public function setTeamMemberTime()
    {
        $hours = is_string($this->input->post('data')) ? json_decode($this->input->post('data'), true) : $this->input->post('data');

        $postData = [
            'loggedUser'    => request()->user()->id,
            'teamMemberId'  => $this->input->post('member'), //request()->post('member'),
            'teamWorkDate'  => $this->input->post('date'),
            'memberLoginId' => $hours['login_id'] ?? 0,
            'memberLogin'   => $hours['login'] ?? null, // json_decode($this->input->post('data'), true)["login"]
            'memberLogout'  => $hours['logout'] ?? null, // json_decode($this->input->post('data'), true)["logout"]
        ];

        $errorMsg = [
            'loggedUser'    => 'Auth User ID',
            'teamMemberId'  => 'Memeber ID',
            'teamWorkDate'  => 'Worked Date',
            'memberLogin'   => 'Login Time',
            'memberLogout'  => 'Logout Time',
        ];

        $error = 'You are missed next data in request: ';
        $errorCount = 0;

        $response = [];

        foreach ($postData as $key => $item) {
            if (array_key_exists($key, $errorMsg) && (!$item || $item == '')) {
                $error .= $errorMsg[$key] . ' ';
                $errorCount++;
            }
        }

        if ($errorCount > 0) {
            return $this->response(['error' => $error]);
        }

        $dateCreate = new Carbon($postData['teamWorkDate']);

        $postData['teamWorkDayStart'] = $dateCreate->copy()->startOfDay()->timestamp;
        $postData['teamWorkDayEnd']   = $dateCreate->copy()->endOfDay()->timestamp;

        if (false === $this->isMemberDayAllowed($postData)) {
            return $this->response(['error' => 'You don\'t have access to get this data']);
        }

        if (false === $this->isAllowedPayday($postData['teamWorkDate'])) {
            return $this->response(['error' => 'Payday has already passed']);
        }

        if (Carbon::now()->format('Y-m-d') == $postData['teamWorkDate']) {
            $currentTime = Carbon::now()->timestamp;
            $loginTime   = (new Carbon($postData['memberLogin']))->timestamp;
            $logoutTime  = (new Carbon($postData['memberLogout']))->timestamp;

            if ($currentTime < $loginTime || $currentTime < $logoutTime) {
                return $this->response(['error' => 'You try set time which more than current time']);
            }
        }

        if ($postData['memberLogin'] > $postData['memberLogout']) {
            return $this->response(['error' => 'Login time more than Logout time']);
        }

        $memberWorkedHours = $this->getMemberWorkedHours($postData['teamMemberId'], $postData['teamWorkDate']);
        $isAllowTime = true;
        $login  = Carbon::create($postData['memberLogin'])->format('H:i:s');
        $logout = Carbon::create($postData['memberLogout'])->format('H:i:s');

        if ($postData['memberLoginId'] == 0) {
            foreach ($memberWorkedHours as $hours) {
                if (($hours['login'] && $hours['logout']) && ($hours['logout'] < $login || $hours['login'] > $logout)) {
                    continue;
                } else {
                    $isAllowTime = false;
                }
            }

            if (true === $isAllowTime) {
                $response = $this->setMemberWorkedHours($postData, true);
            } else {
                if (!$hours['login']) {
                    return $this->response(['error' => 'Login time in last range was not set']);
                }

                if (!$hours['logout']) {
                    return $this->response(['error' => 'Logout time in last range was not set']);
                }

                return $this->response(['error' => 'This time range is already was set']);
            }
        }

        if ($postData['memberLoginId'] > 0) {
            foreach ($memberWorkedHours as $hours) {
                if ($postData['memberLoginId'] == $hours['login_id']) {
                    continue;
                }
                else if ($hours['logout'] && (($hours['login'] > $login && $hours['login'] < $logout) || ( $hours['logout'] > $login && $hours['logout'] < $logout))) {
                    $isAllowTime = false;
                }
            }

            if (true === $isAllowTime) {
                $response = $this->setMemberWorkedHours($postData);
            } else {
                if (!$hours['logout']) {
                    return $this->response(['error' => 'Logout time in last range was not set']);
                }

                return $this->response(['error' => 'This time range is already was set']);
            }
        }

        return $this->response($response);
    }

    public function delTeamMemberTime()
    {
        $postData = [
            'loggedUser'    => request()->user()->id,
            'teamMemberId'  => $this->input->post('member'), //request()->post('member'),
            'memberLoginId' => $this->input->post('login_id'), //request()->post('login_id'),
            'teamWorkDate'  => $this->input->post('date'), //request()->post('date'),
        ];

        if (in_array(null, $postData, true) || in_array('', $postData, true)) {
            return $this->response(['error' => 'Not enough data']);
        }

        $dateCreate = new Carbon($postData['teamWorkDate']);

        $postData['teamWorkDayStart'] = $dateCreate->copy()->startOfDay()->timestamp;
        $postData['teamWorkDayEnd']   = $dateCreate->copy()->endOfDay()->timestamp;

        if (false === $this->isMemberDayAllowed($postData)) {
            return $this->response(['error' => 'You don\'t have access to get this data']);
        }

        return $this->response($this->delMemberWorkedHours($postData['memberLoginId']));
    }

    /**
     * @param $data
     * @return bool
     */
    protected function isMemberDayAllowed($data): bool
    {
        $checkResult = $this->mdl_schedule->getEventMemberInTeam($data);

        if(!$checkResult && $data['teamMemberId'] != $data['loggedUser']) {
            return false;
        }

        return true;
    }

    /**
     * @param $member_id
     * @param $worked_date
     * @return array
     */
    protected function getMemberWorkedHours($member_id, $worked_date): array
    {
        $worked_date = (new Carbon($worked_date))->format('Y-m-d');

        return $this->mdl_worked->getEmployeeWorkedTimes($member_id, $worked_date);
    }

    /**
     * @param $data
     * @param false $insert
     * @return false
     */
    protected function setMemberWorkedHours($data, $insert = false)
    {
        $result = false;

        if (true === $insert) {
            if ($login_id = $this->mdl_emp_login->insert([
                'login_user_id' => $data['teamMemberId'],
                'login_date'    => $data['teamWorkDate'],
                'login'         => $data['memberLogin'],
                'logout'        => $data['memberLogout'],
            ])) {
                return $this->mdl_emp_login->getLastEmpDataByLoginID($login_id);
            }
        }

        if (false === $insert) {
            if ($this->mdl_emp_login->update($data['memberLoginId'], ['login' => $data['memberLogin'], 'logout' => $data['memberLogout']])) {
                return $this->mdl_emp_login->getLastEmpDataByLoginID($data['memberLoginId']);
            }
        }

        return $result;
    }

    protected function delMemberWorkedHours($login_id)
    {
        $worked_id = $this->mdl_emp_login->get($login_id)->login_worked_id;

        if ($this->mdl_emp_login->delete($login_id)) {
            $total_hrs = $this->mdl_worked->get($worked_id)->worked_hours ?? 0;

            return ['status' => true, 'total_hrs' => $total_hrs];
        }

        return false;
    }

    /**
     * @param $checkDate
     * @return bool
     */
    protected function isAllowedPayday($checkDate): bool
    {
        $payday = $this->mdl_payroll->getPayday((new Carbon($checkDate))->format('Y-m-d'), $checkDate);

        return $payday['payroll_day'] > Carbon::now()->format('Y-m-d');
    }
}
