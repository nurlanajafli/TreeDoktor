<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

use application\modules\user\models\LoginLog;
use application\modules\user\models\UserDevices;
use application\modules\user\models\User;

class Auth extends APP_Controller
{
	function __construct() {
		parent::__construct();
	}

	function index() {
        $wdata = [
            'emailid' => request()->input('username'),
            'password' => request()->input('password')
        ];

        if(!$wdata['emailid'] || !$wdata['password']) {
            return $this->response(array(
                'status' => FALSE,
                'message' => 'Username and Password Required'
            ), 401);
        }

        $wdata['password'] = md5($wdata['password']);

        $user = User::getAuthData($wdata);

        if ($user) {
            $CLPerm = $user->clperm->first();
            $TLCSTEPerm = $user->tlcsteperm->first();
            $FWSSPerm = $user->fwssperm->first();
            $FWIPerm = $user->fwiperm->first();
            $GPSPerm = $user->gpsperm->first();

            $logData = array('log_user_id' => $user->id, 'log_time' => time(), 'log_user_ip' => request()->ip(), 'log_data' => json_encode(['mobile_app' => true]));
            LoginLog::create($logData);

            if ($user->active_status == 'yes') {
                $tokenData = array(
                    'user_id' => $user->id,
                    'id' => $user->id,
                    'user_type' => $user->user_type,
                    'firstname' => $user->firstname,
                    'lastname' => $user->lastname,
                    'rate' => $user->rate,
                    'user_pic' => $user->picture,
                    'user_last_login' => $user->last_login,
                    'user_logged_in' => TRUE,
                    'worker_type' => $user->worker_type,
                    'chatusername' => $user->firstname . ' ' . $user->lastname,
                    'twilio_worker_id' => $user->twilio_worker_id,
                    'twilio_support' => $user->twilio_support,
                    'twilio_workspace_id' => $user->twilio_workspace_id,
                    'exp' => time() + $this->tokenLifetime,
                    'lifetime' => $this->tokenLifetime,
                    'emp_check_work_time' => $user->employee->emp_check_work_time,
                    'emp_start_time' => $user->employee->emp_start_time,
                    'emp_date_hire' => $user->employee->emp_date_hire,
                    'roles' => [
                        'estimator' => $user->employee && $user->employee->emp_field_estimator,
                        'fieldworker' => $user->worker_type == 1 || ($user->employee && $user->employee->emp_feild_worker),
                    ],
					'permissions' => [
                        'CL'     => ($user->user_type === 'admin' ? 1 : (!empty($CLPerm) ? (int) $CLPerm->module_status : 0)),
                        'TLCSTE' => ($user->user_type === 'admin' ? 1 : (!empty($TLCSTEPerm) ? (int) $TLCSTEPerm->module_status : 0)),
                        'FWSS' => ($user->user_type === 'admin' ? 1 : (!empty($FWSSPerm) ? (int) $FWSSPerm->module_status : 0)),
                        'FWI' => ($user->user_type === 'admin' ? 1 : (!empty($FWIPerm) ? (int) $FWIPerm->module_status : 0)),
                        'GPS' => !empty($GPSPerm) ? (int) $GPSPerm->module_status : 0,
                    ],
					'is_tracked' => $user->is_tracked
                );
            } else {
                return $this->response(array(
                    'status' => FALSE,
                    'message' => 'Your Account Is Inactive'
                ), 401);
            }
            $jwtToken = $this->objOfJwt->GenerateToken($tokenData);

            $data['Token'] = $jwtToken;

            $user->last_login = date('Y-m-d H:i:s');
            $user->save();

            return $this->response(array(
                'status' => TRUE,
                'data' => $data
            ), 200);
        } else {
            return $this->response(array(
                'status' => FALSE,
                'message' => 'Incorrect Credentials'
            ), 401);
        }
	}

	function device($deviceId = NULL) {
        if(!$deviceId || (!$deviceId && !$this->device_id)) {
            return $this->response(array(
                'status' => FALSE,
                'message' => 'Device ID Is Required'
            ), 401);
        }

	    // TODO: mb some how firebase_token validation?

	    $registered = UserDevices::deviceRegistration([
	        'device_id' => $this->device_id ?: $deviceId,
            'device_user_id' => $this->user->id,
            'device_token' => $this->token,
            'device_token_expiration' => date('Y-m-d H:i:s', $this->jwt->exp),
            'firebase_token' => $this->firebase_token
        ]);

        if (!$registered) {
            return $this->response(array(
                'status' => false,
                'message' => 'Unexpected error'
            ), 500);
        }

        return $this->response(array(
            'status' => TRUE,
            'data' => []
        ), 200);
    }

	function logout() {
	    try {
            UserDevices::deviceUnregistration($this->token);

            return $this->response(array(
                'status' => TRUE
            ), 204);
        }
        catch (Exception $e) {
            return $this->response(array(
                'status' => false,
                'message' => $e->getMessage()
            ), 500);
        }
    }
}
