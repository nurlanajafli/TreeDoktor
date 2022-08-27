<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

//require APPPATH . '/libraries/JWT.php';

use application\modules\user\models\UserDevices;
use application\modules\user\models\User;

class Token extends APP_Controller
{
	function __construct() {
		parent::__construct();
	}

	function refresh() {
        try {
            $this->jwt = (object) $this->objOfJwt->DecodeToken(isset($this->token) && $this->token ? $this->token : NULL);
        } catch (ExpiredException $e) {
            list($header, $payload, $signature) = explode(".", $this->token);
            $payload = JWT::jsonDecode(JWT::urlsafeB64Decode($payload));

            $deviceData = UserDevices::getDeviceBy(['device_user_id' => $payload->user_id, 'device_token' => $this->token]);

            if(!$deviceData) {
                return $this->response(array(
                    'status' => FALSE,
                    'message' => 'Incorrect Token'
                ), 401);
            }

            $user = User::getAuthData(['id' => $payload->user_id]);

            if ($user && $user->active_status == 'yes') {
                $CLPerm = $user->clperm->first();
                $TLCSTEPerm = $user->tlcsteperm->first();
                $FWSSPerm = $user->fwssperm->first();
                $FWIPerm = $user->fwiperm->first();
                $GPSPerm = $user->gpsperm->first();

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

                $jwtToken = $this->objOfJwt->GenerateToken($tokenData);
                UserDevices::deviceRegistration([
                    'device_id' => $this->device_id ?: $deviceData['device_id'],
                    'device_user_id' => $user->id,
                    'device_token' => $jwtToken,
                    'device_token_expiration' => date('Y-m-d H:i:s', $tokenData['exp']),
                    'firebase_token' => $this->firebase_token
                ]);

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
                    'message' => 'Your Account Is Inactive'
                ), 401);
            }
        } catch (Exception $e) {
            return $this->response(array(
                'status' => FALSE,
                'message' => $e->getMessage()
            ), 401);
        }

        return $this->response(array(
            'status' => TRUE
        ), 200);
	}
}
