<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once(APPPATH . '/libraries/CreatorJwt.php');

use application\modules\user\models\User;
use application\modules\user\models\UserDevices;

class APP_Controller extends MX_Controller
{
    var $tokenLifetime = 86400;
    var $user = NULL;

    function __construct()
    {
        parent::__construct();
        $CI =& get_instance();
        $this->load->model('mdl_user');
        $this->load->model('mdl_estimates_orm', 'mdl_estimates_orm');
        $this->load->model('mdl_crews_orm', 'mdl_crews_orm');
        $this->load->model('mdl_clients');
        $this->load->model('mdl_equipment_orm', 'mdl_equipment_orm');
        $this->load->model('mdl_workorders');
        $this->load->model('mdl_schedule');
        $this->load->model('mdl_vehicles');
        $this->load->model('mdl_events_orm');
        $this->load->helper('events_helper');
        $this->load->helper('tree_helper');

        $this->objOfJwt = new CreatorJwt();
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
        header('Access-Control-Max-Age: 1000');
        header('Access-Control-Allow-Headers: Content-Type, Version, Authorization, X-Requested-With, Firebase-Token, Device-Id');
        header('Content-type: application/json');

        $method = isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] ? $_SERVER['REQUEST_METHOD'] : NULL;
        if($method == "OPTIONS")
            exit;

        $this->_rawToPost();
        $segments = $this->router->fetch_class() . '/' . $this->router->fetch_method();

        $exceptedMethods = [
            'settings/index',
            'settings/online',
            'auth/index',
            'token/refresh',
            'notification/index',
            'notification/send',
            'log/get',
            'log/clear',
            'sync/get',
            'sync/clear',
            'gps/show',
            'gps/clear',
            'appestimates/deleteTestEstimate',
            'appestimates/deleteAndrewTestEstimate',
            'appcreditcards/success',
            'appcreditcards/error',
            'gps/log',
            'gps/clearLog',
        ];

        $receivedToken = $this->input->request_headers('Authorization');
        $this->version = isset($receivedToken['Version']) && $receivedToken['Version'] ? $receivedToken['Version'] : NULL;
        $this->token = isset($receivedToken['Authorization']) && $receivedToken['Authorization'] ? $receivedToken['Authorization'] : NULL;
        if($this->token === NULL && $this->input->get('authorization') !== false) {
            $this->token = $this->input->get('authorization');
        }

        $this->firebase_token = request()->header('Firebase-Token');
        $this->device_id = request()->header('Device-Id');

        $CI->token = $this->token;
        $local = false;

        if (array_search($segments, $exceptedMethods))// !== FALSE && !strpos(base_url(), 'crmold.loc') && !strpos(base_url(), 'arbostar.loc'))
        {
            return;
        }
/*        elseif (strpos(base_url(), 'crmold.loc') || strpos(base_url(), 'localhost') || strpos(base_url(),
                'arbostar.loc') || strpos(base_url(), 'arbostar.localhost') || strpos(base_url(),
                'crm.loc') || strpos(base_url(), 'arbostar.test')) {
            $local = true;
            $this->jwt = true;
            $CI->jwt = $this->jwt;
            $CI->token = $this->jwt;
            $deviceData = true;
            $this->user = User::getAuthData(['id' => 31]);
            $CI->user = $this->user;
            $CI->jwt = $this->user;
            $CI->jwt->user_id = $this->user->id;
            $this->device = new stdClass();
            $this->device->device_id = 123456;
            $this->device->device_user_id = 31;
            $this->device->device_token = 123456;
            $this->device->device_token_expiration = '2220-01-17 23:56:11';

            $CLPerm = $this->user->clperm->first();

            $CI->token = $this->jwt = $this->objOfJwt->GenerateToken([
                'user_id' => $this->user->id,
                'id' => $this->user->id,
                'user_type' => $this->user->user_type,
                'firstname' => $this->user->firstname,
                'lastname' => $this->user->lastname,
                'rate' => $this->user->rate,
                'user_pic' => $this->user->picture,
                'user_last_login' => $this->user->last_login,
                'user_logged_in' => TRUE,
                'worker_type' => $this->user->worker_type,
                'chatusername' =>$this->user->firstname . ' ' . $this->user->lastname,
                'twilio_worker_id' => $this->user->twilio_worker_id,
                'twilio_support' => $this->user->twilio_support,
                'twilio_workspace_id' => $this->user->twilio_workspace_id,
                'exp' => time() + $this->tokenLifetime,
                'lifetime' => $this->tokenLifetime,
                'roles' => [
                    'estimator' => $this->user->employee && $this->user->employee->emp_field_estimator,
                    'fieldworker' => $this->user->worker_type == 1 || ($this->user->employee && $this->user->employee->emp_feild_worker),
                ],
                'permissions' => [
                    'CL'     => ($this->user->user_type === 'admin' ? 1 : (!empty($CLPerm) ? (int) $CLPerm->module_status : 0)),
                ],
                'is_tracked' => $this->user->is_tracked
            ]);
            //return;
        }*/

        if (!$this->token) {
            $this->response(array(
                'status' => false,
                'message' => 'Auth Required'
            ), 401, true);
        }

        try {
            $this->jwt = (object) $this->objOfJwt->DecodeToken($this->token);
            $userLog = User::with(['modules'])->whereId($this->jwt->user_id)->first();
            $deviceData = UserDevices::getDeviceBy(['device_user_id' => $this->jwt->user_id, 'device_token' => $this->token]);

            if ($userLog->count()) {
                $this->user = $userLog;
                $modules = $userLog->modules;

                if ($userLog->user_type == 'admin') {
                    $modules->map(function ($item, $key) {
                        $item->module_status = $item->module_status != '1' ? '1' : $item->module_status;
                        return $item;
                    });
                }

                $this->user->permissions = $userLog->modules->pluck('module_status', 'module_id')->toArray();
            }
            if ($deviceData) {
                $this->device = $deviceData;
            }

            $CI->jwt = $this->jwt;
            $CI->user = $this->user;

            if (!$userLog) {
                $this->response(array(
                    'status' => false,
                    'message' => 'User Not Found'
                ), 401, true);
            }
            if (!$deviceData && $segments != 'auth/device' && !$local) {
                $this->response(array(
                    'status' => false,
                    'message' => 'Unregistered Token'
                ), 401, true);
            }
        }
        catch (Exception $e) {
            $this->response(array(
                'status' => false,
                'message' => $e->getMessage()
            ), 401, true);
        }
    }

//    public function response($data = [], $code = 200) {
//        header('Content-type: application/json');
//        $this->output
//            ->set_content_type('application/json')
//            ->set_status_header($code)
//            ->set_output(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))
//            ->_display();
//        exit;
//    }

    private function _rawToPost() {
        $raw = file_get_contents('php://input');
        if($raw) {
            $jsonArray = json_decode($raw,true);
            if($jsonArray && json_last_error() === JSON_ERROR_NONE) {
                $_POST = array_merge($_POST, $jsonArray);
            }
        }
    }
    
    protected function _fakeWebLogin() {
        $CI =& get_instance();
        switch ($this->user->user_type) {
            case "admin":
                $data = array(
                    'user_id' => $this->user->id,
                    'user_type' => 'admin',
                    'firstname' => $this->user->firstname,
                    'lastname' => $this->user->lastname,
                    'rate' => $this->user->rate,
                    'user_pic' => $this->user->picture,
                    'user_last_login' => $this->user->last_login,
                    'user_logged_in' => TRUE,
                    'worker_type' => $this->user->worker_type,
                    'chatusername' => $this->user->firstname . ' ' . $this->user->lastname,
                    'twilio_worker_id' => $this->user->twilio_worker_id,
                    'twilio_support' => $this->user->twilio_support,
                    'twilio_workspace_id' => $this->user->twilio_workspace_id,
                    'system_user' => $this->user->system_user,
                    //'username' => $this->user->id,
                );
                break;
            case "user":
                /* get user modu;es status*/

                $data = array(
                    'user_id' => $this->user->id,
                    'user_type' => 'user',
                    'firstname' => $this->user->firstname,
                    'lastname' => $this->user->lastname,
                    'rate' => $this->user->rate,
                    'user_pic' => $this->user->picture,
                    'user_last_login' => $this->user->last_login,
                    'user_logged_in' => TRUE,
                    'worker_type' => $this->user->worker_type,
                    'chatusername' => $this->user->firstname . ' ' . $this->user->lastname,
                    'twilio_worker_id' => $this->user->twilio_worker_id,
                    'twilio_support' => $this->user->twilio_support,
                    'twilio_workspace_id' => $this->user->twilio_workspace_id,
                    //'username' => $this->user->id
                );
                break;
        };
        $CI->session->set_userdata($data);
    }
}
