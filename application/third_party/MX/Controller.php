<?php (defined('BASEPATH')) OR exit('No direct script access allowed');

/** load the CI class for Modular Extensions **/
//require dirname(__FILE__) . '/Base.php';
require_once(APPPATH . '/libraries/CreatorJwt.php');

/**
 * Modular Extensions - HMVC
 *
 * Adapted from the CodeIgniter Core Classes
 * @link    http://codeigniter.com
 *
 * Description:
 * This library replaces the CodeIgniter Controller class
 * and adds features allowing use of modules and the HMVC design pattern.
 *
 * Install this file as application/third_party/MX/Controller.php
 *
 * @copyright    Copyright (c) 2015 Wiredesignz
 * @version    5.5
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 **/
class MX_Controller
{

    public $autoload = array();

    public function __construct()
    {
        if (class_exists('Modules') && !empty(Modules::$instances)) {
            $initInstance = reset(Modules::$instances);
            $initRouter = $initInstance->router;
        }
        CI_Controller::setInstance($this);

        $class = str_replace($this->config->item('controller_suffix'), '', get_class($this));

        /** @author 18e14c93 Ruslan Hleba <gleba.ruslan@gmail.com> on 13.01.2020 at 20:14 */
        $headers = $this->input->request_headers('Authorization');
        if (isset($headers['Authorization']) && $headers['Authorization']) {
            $token = isset($headers['Authorization']) && $headers['Authorization'] ? $headers['Authorization'] : null;
            $tokenParts = explode('.', $token);
            if (isset($tokenParts[1])) {
                $userData = json_decode(base64_decode($tokenParts[1]));

                if (isset($userData->user_id)) {
                    $this->load->model('mdl_user');
                    $deviceData = $this->mdl_user->getDeviceByUserAndToken($userData->user_id, $token);

                    if ($deviceData) {
                        $userLog = $this->mdl_user->get_user('*', ['users.id' => $userData->user_id]);
                        if ($userLog) {
                            $this->user = $userLog->row();
                        }
                        $this->token = $token;
                    }
                }
            }
        }

        if ($this->session->userdata('user_id') && strtolower($class) != 'chat') {
            save_log();
        }
        /** end */

        log_message('debug', $class . " MX_Controller Initialized");
        Modules::$registry[strtolower($class)] = $this;

        /* copy a loader instance and initialize */
        $this->load = clone load_class('Loader');
        $this->load->initialize($this);

        /* autoload module items */
        $this->load->_autoloader($this->autoload);

        if (isset($initRouter)) {
            CI::$APP->router = $initRouter;
        }
    }

    public function __get($class)
    {
        return CI::$APP->$class;
    }

    public function __isset($class)
    {
        return isset(CI::$APP->$class);
    }

    public function response($data = [], $httpCode = 200, $display = false)
    {
        /** @var MY_Output $output */
        $output = CI::$APP->output
            //->set_header('Access-Control-Allow-Origin: *')
            ->set_content_type('application/json')
            ->set_status_header($httpCode)
            ->set_output(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        if (config_item('debugbar') === true) {
            $debugbar = app('debugbar');
            $response = new \Illuminate\Http\Response();
            $debugbar->enable();
            $response = $debugbar->modifyResponse(app('request'), $response);
            $response->headers->get('phpdebugbar-id');
            foreach ($response->headers->all() as $key => $header) {
                $output->set_header($key . ": " . $header[0]);
            }
        }
        if ($display) {
            $output->_display();
            exit;
        }
    }

    public function perm($key = null) {
        return $this->user->permissions[$key] ?? false;
    }

    public function errorResponse($error = null, $errors = [], $httpCode = 200, $display = false)
    {
        $data['status'] = 'error';
        if ($error !== null) {
            $data['error'] = $error;
        }
        if (!empty($errors)) {
            $data['errors'] = $errors;
        }
        return $this->response($data, $httpCode, $display);
    }

    public function successResponse($data = [], $message = null, $httpCode = 200, $display = false)
    {
        $payload['status'] = 'ok';
        if ($message !== null) {
            $payload['message'] = $message;
        }
        return $this->response(array_merge($payload, $data), $httpCode, $display);
    }

}
