<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP 5.1.6 or newer
 *
 * @package		CodeIgniter
 * @author		ExpressionEngine Dev Team
 * @copyright	Copyright (c) 2008 - 2011, EllisLab, Inc.
 * @license		http://codeigniter.com/user_guide/license.html
 * @link		http://codeigniter.com
 * @since		Version 1.0
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * Exceptions Class
 *
 * @package		CodeIgniter
 * @subpackage	Libraries
 * @category	Exceptions
 * @author		ExpressionEngine Dev Team
 * @link		http://codeigniter.com/user_guide/libraries/exceptions.html
 */


use application\core\Application;
use application\core\Bootstrap\BootProviders;
use application\core\Bootstrap\LoadConfiguration;
use application\core\Bootstrap\RegisterFacades;
use application\core\Bootstrap\RegisterProviders;
use application\core\Kernel;
use Illuminate\Container\Container;
use Illuminate\Contracts\Http\Kernel as KernelContract;


class MY_Exceptions extends CI_Exceptions
{
    var $action;
    var $severity;
    var $message;
    var $filename;
    var $line;


    /**
     * General Error Page
     *
     * This function takes an error message as input
     * (either as a string or an array) and displays
     * it using the specified template.
     *
     * @access	private
     * @param	string	the heading
     * @param	string	the message
     * @param	string	the template name
     * @param 	int		the status code
     * @return	string
     */
    function show_error($heading, $message, $template = 'error_general', $status_code = 500)
    {
        if (class_exists("CI_Controller") && $CI = &get_instance()) {
            //$CI =& get_instance();
            if($CI->router->fetch_module() === 'app' || $CI->input->is_cli_request()) {
                $this->show_json_error($message, $status_code, $heading);
            }

            if($status_code!='404') {
                $CI->load->config('hooks');
                $CI->load->model('Mdl_error_logs', 'mdl_error_logs');

                $subject = 'Important! PHP ERROR ON '.config_item('company_name_short');
                $msg = $CI->load->view('email_error/fatal_error', ['company_site_name'=>config_item('company_name_short'), 'message'=>['type'=>$heading, 'message'=>'<p>'.implode('</p><p>', ( ! is_array($message)) ? array($message) : $message).'</p>'.$this->_get_debug_backtrace()]], true);
                $exist = $CI->mdl_error_logs->exist_error_log(md5($msg));
                if($exist==FALSE){
                    $CI->mdl_error_logs->push_error_log(md5($msg));

                    pushJob('common/send_error_notification', [
                        'to'=>config_item('error_notification_emails'),
                        'from'=>config_item('error_notification_from'),
                        'subject'=>$subject,
                        'message'=>$msg,
                        'message_data'=>[
                            'type'=>$heading,
                            'message'=>'<p>'.implode('</p><p>', ( ! is_array($message)) ? array($message) : $message).'</p>'.$this->_get_debug_backtrace()
                        ],
                        'post'=>json_encode($_POST, JSON_PRETTY_PRINT),
                        'server'=>$_SERVER??[]
                    ]);
                }
            }
        }
        return parent::show_error($heading, $message, $template, $status_code);
    }

    // --------------------------------------------------------------------

//    public function show_exception($exception)
//    {
//        $templates_path = config_item('error_views_path');
//        if (empty($templates_path)) {
//            $templates_path = VIEWPATH . 'errors' . DIRECTORY_SEPARATOR;
//        }
//
//        $message = $exception->getMessage();
//        if (empty($message)) {
//            $message = '(null)';
//        }
//
//        if (is_cli()) {
//            $templates_path .= 'cli' . DIRECTORY_SEPARATOR;
//        } else {
//            set_status_header(500);
//            $templates_path .= 'html' . DIRECTORY_SEPARATOR;
//        }
//
//        if (ob_get_level() > $this->ob_level + 1)
//        {
//            ob_end_flush();
//        }
//
//        ob_start();
//        include($templates_path . 'error_exception.php');
//        $buffer = ob_get_contents();
//        ob_end_clean();
//        echo $buffer;
//    }

    function _get_debug_backtrace($br = "<BR>") {
        $trace = array_slice(debug_backtrace(), 3);
        $msg = '<code>';
        foreach($trace as $index => $info) {
          if (isset($info['file']))
          {
            
            $msg .= $info['file'] . ':' . $info['line'] . " -> " . $info['function'] . $br;
          }
        }
        $msg .= '</code>';
        return $msg;
    }

    function show_json_error($message, $status_code = 500, $status_message = '', $file = NULL, $line = NULL)
    {
        if (class_exists("CI_Controller") && $CI = &get_instance()) {
            //$CI =& get_instance();
            if (!$CI->input->is_cli_request()) {
                /*header('Cache-Control: no-cache, must-revalidate');
                header('Content-type: application/json');
                set_status_header($status_code, $status_message);*/
            }
        }

        $resp = [
            'error' => 'Server Error',
            'message' => $message
        ];
        if($file)
            $resp['file'] = str_replace(FCPATH, '', $file);
        if($line)
            $resp['line'] = $line;

        echo json_encode($resp);

        exit;
    }

    // --------------------------------------------------------------------

    /**
     * Native PHP error handler
     *
     * @access	private
     * @param	string	the error severity
     * @param	string	the error string
     * @param	string	the error filepath
     * @param	string	the error line number
     * @return	string
     */
    function show_php_error($severity, $message, $filepath, $line)
    {
        if (class_exists("CI_Controller") && $CI = &get_instance()) {
            //$CI =& get_instance();
            if ($CI->router->fetch_module() === 'app' || $CI->input->is_cli_request()) {
                $this->show_json_error($message, 500, '', $filepath, $line);
            }


            $CI->load->config('hooks');
            $CI->load->model('Mdl_error_logs', 'mdl_error_logs');
            $subject = 'Important! PHP ERROR ON '.config_item('company_name_short');
            $msg = $CI->load->view('email_error/fatal_error', ['company_site_name'=>config_item('company_name_short'), 'message'=>[
                            'type'=>( ! isset($this->levels[$severity])) ? $severity : $this->levels[$severity], 
                            'message'=>$message.'<br>'.$this->_get_debug_backtrace(), 
                            'file'=>$filepath, 
                            'line'=>$line
                        ]], true);
            $exist = $CI->mdl_error_logs->exist_error_log(md5($msg));
            
            if($exist==FALSE){
                $CI->mdl_error_logs->push_error_log(md5($msg));

                pushJob('common/send_error_notification', [
                    'to'=>config_item('error_notification_emails'),
                    'from'=>config_item('error_notification_from'),
                    'subject'=>$subject,
                    'message'=>$msg,
                    'message_data'=>[
                        'type'=>( ! isset($this->levels[$severity])) ? $severity : $this->levels[$severity],
                        'message'=>$message.'<br>'.$this->_get_debug_backtrace(),
                        'file'=>$filepath,
                        'line'=>$line
                    ],
                    'post'=>json_encode($_POST, JSON_PRETTY_PRINT),
                    'server'=>$_SERVER??[]
                ]);
            }

        }

        $severity = ( ! isset($this->levels[$severity])) ? $severity : $this->levels[$severity];

        $filepath = str_replace("\\", "/", $filepath);

        // For safety reasons we do not show the full file path
        if (FALSE !== strpos($filepath, '/'))
        {
            $x = explode('/', $filepath);
            $filepath = $x[count($x)-2].'/'.end($x); //countOk
        }

        if (ob_get_level() > $this->ob_level + 1)
        {
            ob_end_flush();
        }
        ob_start();
        include(APPPATH.'errors/error_php.php');
        $buffer = ob_get_contents();
        ob_end_clean();
        echo $buffer;
    }

    /**
     * 404 Error Handler
     *
     * @param string $page Page URI
     * @param bool $log_error Whether to log the error
     * @param bool $options  = array('heading'=>'', 'message'=>'') 
     * @return    void
     * @uses    CI_Exceptions::show_error()
     *
     */
    
    public function show_404($page = '', $log_error = true, $options = [])
    {
        if (is_cli()) {
            $heading = 'Not Found';
            $message = 'The controller/method pair you requested was not found.';
        } else {
            $heading = 'Oops!'; //404 Page Not Found
            $message = 'The page you requested was not found.';
        }

        if(isset($options['heading']))
            $message = $options['heading'];

        if(isset($options['message']))
            $message = $options['message'];

        // By default we log this, but allow a dev to skip it
        if ($log_error)
        {
            log_message('error', $heading . ': ' . $page);
        }

        spl_autoload_register(function ($class) {
            $class = str_replace('\\', DIRECTORY_SEPARATOR, $class);
            if (strpos($class, 'application' . DIRECTORY_SEPARATOR) !== false
                && file_exists(FCPATH . $class . '.php')) {
                require_once FCPATH . $class . '.php';
            }
        });

        Container::setInstance(new Application(FCPATH));

        app()->singleton(KernelContract::class, function ($app) {
            return new Kernel($app, $app['router']);
        });

        app(KernelContract::class)->bootstrap();
        app()->register(\application\core\ServiceProviders\CustomServiceProvider::class);

        $CI = &get_instance();
        $CI->output->set_status_header(404);
        $CI->load->view('includes/header', ['title'=>'Arbostar - 404 Page not found']);
        $CI->load->view('errors/error_404', ['heading'=>$heading, 'message'=>$message]);
        $CI->load->view('includes/footer', []);
        echo $CI->output->get_output();
        exit(4); // EXIT_UNKNOWN_FILE
    }
}


// END Exceptions Class

/* End of file Exceptions.php */
/* Location: ./system/core/Exceptions.php */
