<?php
class PHPAfterError {
	public $emails;
	public $CI;
	function __construct(){
		$this->CI = & get_instance();
	}

	public function init($params)
	{
		$this->emails = $params['error_emails'];
		if($this->CI->router->fetch_module() === 'app' || is_cli()) {
			return FALSE;
		}

		set_error_handler(array($this, 'HandleError'));
		set_exception_handler(array($this, 'GlobalExceptions'));
	}

	public function GlobalExceptions($exception)
	{
		$msg = $this->CI->load->view('email_error/_partials/head', [], true);
		$msg .= $this->CI->load->view('email_error/index', ['error'=>$exception], true);
		$msg .= $this->CI->load->view('email_error/_partials/foot', [], true);

		$host = isset($_SERVER['HTTP_HOST'])?$_SERVER['HTTP_HOST']:'';
		$subject = 'Important! PHP ERROR ON '.$host;
		
		$this->CI->load->model('mdl_error_logs', 'mdl_error_logs');
		$exist = $this->CI->mdl_error_logs->exist_error_log(md5($msg));
		if($exist==FALSE){
			$this->CI->mdl_error_logs->push_error_log(md5($msg));
			pushJob('common/send_error_notification', [
				'to'=>$this->emails,
				'from'=>'system@arbostar.com',
				'subject'=>$subject,
				'message'=>$msg,
                'message_data'=>[
                    'type'=>$exception->getCode(),
                    'message'=>$exception->getMessage(),
                    'trace'=>$exception->getTrace(),
                ],
                'post'=>json_encode($_POST, JSON_PRETTY_PRINT),
                'server'=>$_SERVER??[]
			]);
			error_clear_last();
		}
	}

	public function HandleError($code, $description, $file, $line){
			
		list($error, $log) = $this->mapErrorCode($code);
		$message = $error . ' (' . $code . '): ' . $description . ' in [' . $file . ', line ' . $line . ']';
	    //throw new ErrorException ($message, $code, $log, $file, $line);
	    return FALSE;
		
	}



	function mapErrorCode($code) {
	    $error = $log = null;
	    switch ($code) {
	        case E_PARSE:
	        case E_ERROR:
	        case E_CORE_ERROR:
	        case E_COMPILE_ERROR:
	        case E_USER_ERROR:
	            $error = 'Fatal Error';
	            $log = LOG_ERR;
	            break;
	        case E_WARNING:
	        case E_USER_WARNING:
	        case E_COMPILE_WARNING:
	        case E_RECOVERABLE_ERROR:
	            $error = 'Warning';
	            $log = LOG_WARNING;
	            break;
	        case E_NOTICE:
	        case E_USER_NOTICE:
	            $error = 'Notice';
	            $log = LOG_NOTICE;
	            break;
	        case E_STRICT:
	            $error = 'Strict';
	            $log = LOG_NOTICE;
	            break;
	        case E_DEPRECATED:
	        case E_USER_DEPRECATED:
	            $error = 'Deprecated';
	            $log = LOG_NOTICE;
	            break;
	        default :
	            break;
	    }
	    return array($error, $log);
	}

    
}


class WarningException              extends ErrorException {}
class ParseException                extends ErrorException {}
class NoticeException               extends ErrorException {}
class CoreErrorException            extends ErrorException {}
class CoreWarningException          extends ErrorException {}
class CompileErrorException         extends ErrorException {}
class CompileWarningException       extends ErrorException {}
class UserErrorException            extends ErrorException {}
class UserWarningException          extends ErrorException {}
class UserNoticeException           extends ErrorException {}
class StrictException               extends ErrorException {}
class RecoverableErrorException     extends ErrorException {}
class DeprecatedException           extends ErrorException {}
class UserDeprecatedException       extends ErrorException {}
