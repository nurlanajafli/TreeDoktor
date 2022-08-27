<?php

use ElephantIO\Client as WSClient;
use ElephantIO\Engine\SocketIO\Version1X;
use application\modules\employees\models\Employee;

class tracker_timer extends CI_Driver implements JobsInterface
{
    var $payload, $body, $wsClient, $CI = [];

    public function getPayload($data = NULL) {
        return $data;
    }

    public function execute($job = NULL)
    {
        $this->CI =& get_instance();
        $this->CI->load->model('mdl_emp_login');
        $this->CI->load->model('mdl_worked');

        $this->payload = json_decode($job->job_payload, TRUE);
        $this->body = $this->payload['body'] ?? [];

        $action = $this->body['action'] ?? FALSE;

        if ($action == 'start') {
            $this->_startTracker();
        } elseif ($action == 'stop') {
            $this->_stopTracker();
        } else {
            $this->_socketWrite('syncJobFailed', [
                'request_id' => $this->payload['id'],
                'message' => 'Incorrect Action'
            ]);
        }
        return TRUE;
    }

    private function _startTracker() {
        $loginDate = isset($this->body['login']) ? date('Y-m-d', strtotime($this->body['login'])) : NULL;
        $loginTime = isset($this->body['login']) ? date('H:i:s', strtotime($this->body['login'])) : NULL;
        $logoutTime = isset($this->body['logout']) ? date('H:i:s', strtotime($this->body['logout'])) : NULL;
        $lat = $this->body['lat'] ?? null;
        $lng = $this->body['lng'] ?? null;

        $employees = Employee::where(['emp_user_id' => $this->payload['user_id']])->first();
        if ($employees->emp_check_work_time == '1' && $employees->emp_start_time != '00:00:00') {
            if ($loginTime < $employees->emp_start_time) {
                $loginTime = $employees->emp_start_time;
            }
        }

        if(!$loginDate || !$loginTime) {
            $this->_socketWrite('syncJobFailed', [
                'request_id' => $this->payload['id'],
                'message' => 'Incorrect Login Time'
            ]);

            return FALSE;
        }

        if($logoutTime && $logoutTime < $loginTime) {
            if ($employees->emp_check_work_time == '1' && $employees->emp_start_time != '00:00:00') {
                $logoutTime = $loginTime;
            } else {
                $this->_socketWrite('syncJobFailed', [
                    'request_id' => $this->payload['id'],
                    'message' => "Logout Time Can't Be Less Than Login Time"
                ]);
                return FALSE;
            }
        }

        $whereCondition = "(login = '{$loginTime}' AND logout IS NULL) OR " . //exists any other without logout and the same login time
            "(login <= '{$loginTime}' AND logout >= '{$loginTime}')";       //in the other exists interval (collision)

        if($logoutTime) {
            $whereCondition .= " OR ";
            $whereCondition .=
                "(login >= '{$loginTime}' AND login <= '{$logoutTime}')"    //partial or fully cross with exists interval (collision)
            ;
        }

        $existsCollision = $this->CI->mdl_emp_login->get_by(
            "login_user_id = {$this->payload['user_id']} AND login_date = '{$loginDate}' AND (" .
                $whereCondition
            . ")"
        );

        if(!$existsCollision) {
            $insertData['login_date'] = $loginDate;
            $insertData['login'] = $loginTime;
            $insertData["login_lat"] = $lat;
            $insertData["login_lon"] = $lng;
            $insertData['login_user_id'] = $this->payload['user_id'];
            if($logoutTime) {
                $insertData['logout'] = $logoutTime;
            }
            $this->CI->mdl_emp_login->insert($insertData);
        } else {
            $this->_socketWrite('syncJobFailed', [
                'request_id' => $this->payload['id'],
                'message' => "Collision Detected"
            ]);

            return false;
        }

        $this->_socketWrite('syncJobSuccess', [
            'request_id' => $this->payload['id']
        ]);

        return TRUE;
    }

    private function _stopTracker() {

        $loginId = isset($this->body['id']) ? $this->body['id'] : NULL;

        $loginRow = $this->CI->mdl_emp_login->get_by([
            'login_user_id' => $this->payload['user_id'],
            'login_id' => $loginId,
        ]);

        if(!$loginRow) {
            $this->_socketWrite('syncJobFailed', [
                'request_id' => $this->payload['id'],
                'message' => "Login Record Not Found"
            ]);
            return false;
        }

        $loginTime = $loginRow->login ?? NULL;
        $loginDate = $loginRow->login_date ?? NULL;
        $logoutTime = isset($this->body['logout']) ? date('H:i:s', strtotime($this->body['logout'])) : NULL;
        $lat = $this->body['lat'] ?? null;
        $lng = $this->body['lng'] ?? null;

        $employees = Employee::where(['emp_user_id' => $this->payload['user_id']])->first();
        if ($employees->emp_check_work_time == '1' && $employees->emp_start_time != '00:00:00') {
            if ($logoutTime <= $employees->emp_start_time) {
                $logoutTime = $employees->emp_start_time;
            }
        }

        if(!$logoutTime || !$loginTime) {
            $this->_socketWrite('syncJobFailed', [
                'request_id' => $this->payload['id'],
                'message' => 'Incorrect Login/Logout Time'
            ]);

            return FALSE;
        }

        if($logoutTime < $loginTime) {
            if ($employees->emp_check_work_time == '1' && $employees->emp_start_time != '00:00:00') {
                $logoutTime = $loginTime;
            } else {
                $this->_socketWrite('syncJobFailed', [
                    'request_id' => $this->payload['id'],
                    'message' => "Logout Time Can't Be Less Than Login Time"
                ]);
                return FALSE;
            }
        }

        $whereCondition = "(login = '{$loginTime}' AND logout IS NULL) OR " .   //exists any other without logout and the same login time
            "(login <= '{$loginTime}' AND logout >= '{$loginTime}')";           //in the other exists interval (collision)

        if($logoutTime) {
            $whereCondition .= " OR ";
            $whereCondition .=
                "(login >= '{$loginTime}' AND login <= '{$logoutTime} AND logout')"        //partial or fully cross with exists interval (collision)
            ;
        }

        $existsCollision = $this->CI->mdl_emp_login->get_by(
            "login_user_id = {$this->payload['user_id']} AND login_id <> {$loginRow->login_id} AND login_date = '{$loginDate}' AND (" .
            $whereCondition
            . ")"
        );

        if(!$existsCollision) {
            $updateData['logout'] = $logoutTime;
            $updateData["login_lat"] = $lat;
            $updateData["login_lon"] = $lng;
            $this->CI->mdl_emp_login->update($loginRow->login_id, $updateData);
        } else {
            $this->_socketWrite('syncJobFailed', [
                'request_id' => $this->payload['id'],
                'message' => "Collision Detected"
            ]);

            return FALSE;
        }

        $this->_socketWrite('syncJobSuccess', [
            'request_id' => $this->payload['id']
        ]);

        return TRUE;
    }

    private function _socketWrite($msg, $response = []) {
        if(config_item('wsClient')) {
            if(!$this->wsClient) {
                $this->wsClient = new WSClient(new Version1X(config_item('wsClient') . '?chat=1&user_id=' . $this->payload['user_id']));
                $this->wsClient->initialize();
            }
            if($this->wsClient) {
                $this->wsClient->emit('room', ['chat-' . $this->payload['user_id']]);
                $this->wsClient->emit('message', ['method' => $msg, 'params' => $response]);
            }
        }
    }
}
