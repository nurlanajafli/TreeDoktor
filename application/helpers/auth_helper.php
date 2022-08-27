<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

function isUserLoggedInParent()
{
    $CI = &get_instance();
    return $CI->session->userdata('user_logged_in') == true ? true : false;
}

function isUserLoggedIn1()
{
    $CI = &get_instance();
    return $CI->session->userdata('user_logged_in') == true ? true : false;
}
function isWorkorderAccessible()
{
    $router =& load_class('Router', 'core');
    $classCheck = true;
    $methodCheck = $router->fetch_method() != 'pdf' && $router->fetch_method() != 'workorder_overview' && $router->fetch_method() != '_workorder_html_generate' && $router->fetch_method() != 'partial_invoice_generate';
    $requestUriCheck = true;
    return $classCheck && $methodCheck && $requestUriCheck;
}
function isInvoiceAccessible()
{
    $router =& load_class('Router', 'core');
    $classCheck = $router->fetch_class() != 'payments';
    $methodCheck = $router->fetch_method() != 'invoice' && $router->fetch_method() != 'update_status' && $router->fetch_method() != 'appinvoices';
    $requestUriCheck = isset($_SERVER['REQUEST_URI']) && $_SERVER['REQUEST_URI'] != '/app/estimates/add_payment';
    return $classCheck && $methodCheck && $requestUriCheck && !is_cli();
}
function isEstimateAccessible()
{
    $router =& load_class('Router', 'core');
    $classCheck = $router->fetch_class() != 'appestimates' && $router->fetch_class() != 'payments';
    $methodCheck = $router->fetch_method() != 'send'  && $router->fetch_method() != 'estimate';
    $requestUriCheck = isset($_SERVER['REQUEST_URI']) && $_SERVER['REQUEST_URI'] != '/app/estimates/add_payment';
    return $classCheck && $methodCheck && $requestUriCheck && !is_cli();
}
function getUserIfToken()
{
    $CI = &get_instance();
    $receivedToken = $CI->input->request_headers('Authorization');
    $token = isset($receivedToken['Authorization']) && $receivedToken['Authorization'] ? $receivedToken['Authorization'] : NULL;
    if($token === NULL && $CI->input->get('authorization') !== false) {
        $token = $CI->input->get('authorization');
    }

    if (!is_null($token)) {

        $CI->token = $token;
        $objOfJwt = new CreatorJwt();
        try {
            $jwt = (object)$objOfJwt->DecodeToken($token);
            $user = application\modules\user\models\User::find($jwt->user_id);
            if (!is_null($user)) {
                return $user;
            } else {
                return false;
            }
        } catch (Exception $e) {
            return false;
        }
    }
}

function isUserLoggedIn()
{
    $CI = &get_instance();
    $router =& load_class('Router', 'core');
    $module = $router->fetch_module() ? $router->fetch_module() : $CI->uri->segment(1);
    $action = $router->fetch_method();
    $exceptionUrls = [
        '/schedule/ajax_get_traking_position',
        '/schedule/ajax_check_any_updates',
        '/user/ajax_check_autologout'
    ];

    //if($CI->session->userdata('user_id') != 43 && $module != 'chat' && array_search($_SERVER['REQUEST_URI'], $exceptionUrls) === FALSE/* && ($CI->session->userdata('user_id') == 6 || $CI->session->userdata('user_id') == 31)*/)
    /*{
        $time = $CI->session->_get_time();

        $timeDiff = intval($CI->input->cookie('lastActivity') / 1000) - $CI->session->userdata('last_activity');
        $newLastActivity = ($time + $timeDiff) * 1000;
        $CI->input->set_cookie([
            'name' => 'lastActivity',
            'value' => $newLastActivity,
            'path' => '/',
            'expire' => time() + 60 * 60 * 24 * 30
        ]);

        $CI->session->set_userdata('lastActivity', $newLastActivity);

        $last_active = $CI->session->userdata('last_activity');
        if(($time - $last_active) > ACTIVITY_TIMEOUT)
            $CI->session->sess_destroy();
        else
            $CI->session->set_userdata('last_activity', $time);
    }*/

    if (isUserLoggedInParent()) {
        if ($module == 'administration' || $module == 'includes' || $module == 'chat' || $module == "dashboard" || $module == "events" || $module == "payments" || $module == "user" || $module == "employee" || $module == "employees" || $module == "refferals") {
            return true;
        }
        if ($CI->session->userdata('user_type') == "admin") {
            return true;

        }
        if (($module == "clients" || $module == "tree_inventory") && ($CI->session->userdata("CL") == 1 || $CI->session->userdata("CL") == 2)) {
            return true;
        }
        if ($module == "leads" && ($CI->session->userdata("CL") == 1 || $CI->session->userdata("CL") == 2)) {
            return true;
        }
        if ($module == "estimates" && ($CI->session->userdata("CL") == 1 || $CI->session->userdata("CL") == 2)) {
            return true;
        }
        if ($module == "workorders" && ($CI->session->userdata("CL") == 1 || $CI->session->userdata("CL") == 2)) {
            return true;
        }
        if ($module == "invoices" && ($CI->session->userdata("CL") == 1 || $CI->session->userdata("CL") == 2)) {
            return true;
        }
        if ($module == "info") {
            return true;
        }
        if ($module == "screen") {
            return true;
        }
        if (($module == "equipments" && $CI->session->userdata("EQMTS") == 1) || $action == "new_repair") {
            return true;
        }
        if (($module == "equipment" && $CI->session->userdata("EQMTS") == 1) || $action == "new_repair") {
            return true;
        }
        if ($module == "schedule" && $CI->session->userdata("SCHD") == 1) {
            return true;
        }
        if ($module == "tasks" && $CI->session->userdata("TSKS") == true) {
            return true;
        }
        if ($module == "payroll" && $CI->session->userdata("RPS_PR") == true) {
            return true;
        }
        if ($module == "stumps") {
            return true;
        }
        if ($module == "notebook") {
            return true;
        }
        if (
            $module == "business_intelligence" && $CI->session->userdata("UHR") &&
            ($action == "absent_days" || $action == "ajax_save_absence" || $action == "ajax_get_absence" ||
                $action == "ajax_delete_absence" || $action == "users_statistics")
        ) {
            return true;
        }
        if (
            $module == "business_intelligence" && $CI->session->userdata('CRW') == 1 &&
            ($action == "crews_statistic")
        ) {
            return true;
        }
        if (
            $module == "business_intelligence" && $CI->session->userdata('SCHD') == 1 &&
            ($action == "schedule_report")
        ) {
            return true;
        }
        if (
            $module == "business_intelligence" && ($CI->session->userdata('RPS_IN') == 1 || $CI->session->userdata('CL') == 1 || $CI->session->userdata('CL') == 2) &&
            ($action == "invoices_report" || $action == "sales" || $action == "ajax_get_sales_data")
        ) {
            return true;
        }
        if (
            $module == "reports" && ($CI->session->userdata('RPS_IN') == 1 || $CI->session->userdata('CL') == 1 || $CI->session->userdata('CL') == 2) &&
            ($action == "sales" || $action == "ajax_get_sales_data")
        ) {
            return true;
        }
        if (
            $module == "business_intelligence" && ($CI->session->userdata('RPS_WO') == 1 || $CI->session->userdata('CL') == 1 || $CI->session->userdata('CL') == 2) &&
            ($action == "workorders_report")
        ) {
            return true;
        }
        if (
            $module == "business_intelligence" && $CI->session->userdata('RPS_EST') == 1 &&
            ($action == "estimates_report")
        ) {
            return true;
        }
        if (
            $module == "business_intelligence" && ($CI->session->userdata('CL') == 1 || $CI->session->userdata('CL') == 2) &&
            ($action == "estimates_statistic")
        ) {
            return true;
        }
        if (
            $module == "business_intelligence" && ($CI->session->userdata('CL') == 1 || $CI->session->userdata('CL') == 2) &&
            ($action == "lead_statistics")
        ) {
            return true;
        }
        if (
            $module == "business_intelligence" && ($CI->session->userdata('CL') == 1 || $CI->session->userdata('CL') == 2) &&
            ($action == "emails_stat" || $action == "statistic")
        ) {
            return true;
        }
        if ($module == "reports" && $CI->session->userdata("RPS") == 1) {
            if ($module == "reports" && $action == "index") {
                return true;
            }
            if ($module == "reports" && $action == "estimates" && $CI->session->userdata("RPS_EST") == 1) {
                return true;
            }
            if ($module == "reports" && $action == "workorders" && $CI->session->userdata("RPS_WO") == 1) {
                return true;
            }
            if ($module == "reports" && $action == "invoices" && $CI->session->userdata("RPS_IN") == 1) {
                return true;
            }
            if ($module == "reports" && ($action == "payroll" || $action == 'update_lunch' || $action == 'add_expense_amount' || $action == 'update_expense_amount') && $CI->session->userdata("RPS_PR") == 1) {
                return true;
            }
            if ($module == "reports" && $action == "payroll_overview" && $CI->session->userdata("RPS_PRO") == 1) {
                return true;
            }
            if ($module == "reports" && ($action == "payroll1") && $CI->session->userdata("RPS_PR") == 1) {
                return true;
            }
            if ($module == "reports" && $action == "payroll_overview1" && $CI->session->userdata("RPS_PRO") == 1) {
                return true;
            }
            if ($module == "reports" && ($action == "expenses" || $action == "ajax_get_expense") && $CI->session->userdata("EXP") == 1) {
                return true;
            }
        }
        return false;
    } else {
        return false;
    }
}

function isAdmin()
{
    $CI = &get_instance();
    return $CI->session->userdata('user_type') == "admin" ? true : false;
}

function isUser()
{
    $CI = &get_instance();
    return $CI->session->userdata('user_type') == "user" ? true : false;
}

function isEmployee()
{
    $CI = &get_instance();
    return $CI->session->userdata('user_type') == "employee" ? true : false;
}

function isMobile()
{
    return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i",
        $_SERVER["HTTP_USER_AGENT"]);
}

if (!function_exists('clearUserSessionData')) {

    /**
     * @param $all bool
     * @param $user_id
     */
    function clearUserSessionData($all, $user_id)
    {
        $CI = &get_instance();
        $CI->load->model('mdl_user');
        $CI->load->library('session');
        $ci_session_id = get_cookie($CI->config->item('sess_cookie_name'));

        if ($all === true) {
            if (isset($CI->session) && $sessionUserId = $CI->session->userdata('user_id')) {
                //Get current session model from current ci_session_id
                $ciSessionModel = application\modules\user\models\CiSessions::getById($ci_session_id);
                //Remove all if session came
                application\modules\user\models\CiSessions::deleteAllByUserId($user_id);
                application\modules\user\models\UserDevices::deleteAllByUserId((int) $user_id);
                if ((int)$sessionUserId === (int)$user_id) {
                    //Create current session model for available current session
                    application\modules\user\models\CiSessions::createFromObject($ciSessionModel);
                }
            } else if (isset($CI->token) && !empty($CI->token) && isset($CI->user)) {
                //Remove all if token came
                $sessionUserId = $CI->user->id;
                application\modules\user\models\CiSessions::deleteAllByUserId($sessionUserId);
                application\modules\user\models\UserDevices::deleteAllByUserId((int) $sessionUserId);
                $CI->session->sess_destroy();
            }
        } else {
            if (isset($CI->session) && $sessionUserId = $CI->session->userdata('user_id')) {
                $CI->session->sess_destroy();
                application\modules\user\models\CiSessions::deleteAllByUserId($sessionUserId);
            } else if (isset($CI->token) && !empty($CI->token)) {
                application\modules\user\models\UserDevices::deleteAllByUserId((int) $sessionUserId);
            }
        }
    }
}

function save_log()
{
    if (!strpos(current_url(), 'iframe') && !strpos(current_url(), 'ajax_counters') && !strpos(current_url(),
            'ajax_check_any_updates') && !strpos(current_url(), 'ajax_get_traking_position') && !strpos(current_url(),
            'ajax_crews_members') && !strpos(current_url(), 'screen/data')) {
        $CI = &get_instance();
        if (!$CI->session->userdata('system_user')) {
            $data['log_user_id'] = $CI->session->userdata('user_id');
            $data['log_url'] = str_replace(base_url(), '', current_url());
            //$data['log_postdata'] = $CI->input->post() ? json_encode($CI->input->post()) : NULL;
            //$data['log_getdata'] = $CI->input->get() ? json_encode($CI->input->get()) : NULL;
            $data['log_date'] = date('Y-m-d H:i:s');
            $data['log_user_ip'] = $CI->input->ip_address();
            $CI->mdl_history_log->insert($data);
        }
    }
}

function isSystemUser()
{
    $CI = & get_instance();
    return $CI->session->userdata('system_user') == 1 ? TRUE : FALSE;
}
//end of file auth_helper.php
