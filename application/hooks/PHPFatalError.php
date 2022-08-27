<?php
class PHPFatalError {
	public $emails;
	public function init($params)
	{
		$this->emails = $params['error_emails'];
		register_shutdown_function([$this, 'handleShutdown']);
	}


    function handleShutdown()
	{	
		require(BASEPATH.'../application/config/database.php');
		$CFG = & load_class('Config', 'code');
		if($message=error_get_last()){
			$type_text_arr = $this->mapErrorCode($message['type']);
			$type_text = isset($type_text_arr[0])?$type_text_arr[0]:'';
			
			ob_start();
				$base_url = isset($CFG->config['base_url'])?$CFG->config['base_url']:'';
				$company_site_name = isset($CFG->config['company_site_name'])?$CFG->config['base_url']:'';
				require(BASEPATH.'../application/views/email_error/fatal_error.php');
				$msg = ob_get_contents();
			ob_end_clean();
			
			
			$dbhost = $db['default']['hostname']; 
			$dbname = $db['default']['database'];
			$link = new PDO("mysql:host=$dbhost;dbname=$dbname", $db['default']['username'], $db['default']['password']);

			$time = time();
			$error_hash = md5($msg);
			

			/*-----------------------------------exist log---------------------------------------------*/
			$exist = $link->query("SELECT * FROM (error_logs) WHERE el_error_hash = '".$error_hash."' AND el_cteated_time > ".($time-7200)." LIMIT 1", PDO::FETCH_ASSOC)->fetchAll();
			/*-----------------------------------exist log---------------------------------------------*/
			if(empty($exist)){
				/*-----------------------------------add log--------------------------------------------*/
				$error_log_data = ['el_error_hash' => $error_hash, 'el_cteated_time' => $time];
				$error_query = $link->prepare('INSERT INTO error_logs (el_error_hash, el_cteated_time) VALUES (:el_error_hash, :el_cteated_time)');
				$error_query->execute($error_log_data);
				/*-----------------------------------add log---------------------------------------------*/

				/*-----------------------------------add job---------------------------------------------*/
				$host = isset($_SERVER['HTTP_HOST'])?$_SERVER['HTTP_HOST']:'';
				$subject = 'Important! PHP ERROR ON '.$host;
				$payload = json_encode([
					'to'=>$this->emails,
					'from'=>'system@arbostar.com',
					'subject'=>$subject,
					'message'=>$msg,
                    'message_data'=>[
                        'type'=>$type_text,
                        'message'=>$message['message']??'',
                        'file'=>$message['file']??'',
                        'line'=>$message['line']??'',
                    ],
                    'post'=>json_encode($_POST, JSON_PRETTY_PRINT),
                    'server'=>$_SERVER??[]
				]);

				$job = [
		            'job_driver' => 'common/send_error_notification',
		            'job_payload' => $payload,
		            'job_attempts' => 0,
		            'job_is_completed' => 0,
		            'job_available_at' => time(),
		            'job_reserved_at' => 0,
		            'job_created_at' => date('Y-m-d H:i:s'),
		        ];
				$job_query = $link->prepare('INSERT INTO jobs (job_driver, job_payload, job_attempts, job_is_completed, job_available_at, job_reserved_at, job_created_at) VALUES (:job_driver, :job_payload, :job_attempts, :job_is_completed, :job_available_at, :job_reserved_at, :job_created_at)');

				$job_query->execute($job);

				/*-----------------------------------add job---------------------------------------------*/
			}
			
		}

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