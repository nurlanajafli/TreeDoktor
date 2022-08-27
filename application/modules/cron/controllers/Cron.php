<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
define("COOKIE_FILE", "uploads/cookie.txt");
//session_start();
/*require_once '/home/blacklabel/www/crm.dev/td_current_application/application/libraries/Gmail.php';
require_once '/home/blacklabel/www/crm.dev/td_current_application/application/libraries/Google/Client.php';
require_once '/home/blacklabel/www/crm.dev/td_current_application/application/libraries/Google/Client.php';
require_once  '/home/blacklabel/www/crm.dev/td_current_application/application/libraries/Google/Service/Gmail.php';
*/
//use WebSocket\Client;
use application\models\PaymentTransaction;
use application\modules\administration\models\FollowupSettings;
use Twilio\Rest\Client;
use \application\modules\clients\models\ClientLetter;

class Cron extends MX_Controller
{
	private $onlineActivitySid 		= 'WAfc57c478bd7cea19883d8908c85b0f6b';
	private $busyActivitySid		= 'WAc34bf63afbabe02fa3aba16bcec1bde4';
	private $reservedActivitySid	= 'WAcee551aab46ac59139a9c9b06059465c';
	private $wrapUpActivitySid 		= 'WA8c2811c172735411234161750938690a';
	private $offlineActivitySid 	= 'WAb59015c8ffd0c47bb1f812a8c06c38d6';

	private $accountSid 	= 'ACba9a5eeb8b45a12e3973bd16e6ae83f2';
	private $authToken  	= '59cd6e4b9e26182c4fc30956b950bc5f';
	private $workspaceSid 	= 'WSd5ddf64bb22aa165abac6c6434764dec';
	private $taskQueueSid 	= 'WQ4634bbda790aa3a5e589ac482f7229aa';
	private $appSid			= 'AP1b1806bca60981e676ba7c77a9636772';
	private $workflowSid = 'WWade680760e2f16066950ea0f956a99c9';
	private $myNumber = '14162018000';
	private $twilioNumber = '14162018000';
	
	
	var $fuErrors = [];

	function __construct()
	{

		parent::__construct();


		//load all common models and libraries here;
		$this->load->model('mdl_administration', 'mdl_administration');
		$this->load->model('mdl_invoices', 'mdl_invoices');
		$this->load->model('mdl_workorders', 'mdl_workorders');
		$this->load->model('mdl_user');
	}

	function followup_generator() {
		$this->load->model('mdl_followup_settings');
		$this->load->model('mdl_followups');
		$this->load->model('mdl_estimates');
		$this->load->model('mdl_leads');
		$this->load->model('mdl_invoices');
		$this->load->model('mdl_user', 'mdl_users');
		$this->load->model('mdl_equipment_items');
		
		$fsSettings = $this->mdl_followup_settings->get_many_by(['fs_disabled' => '0', 'fs_table !=' => 'schedule', 'fs_table !=' => 'client_tasks']);

		$fsConfig = $this->config->item('followup_modules');
		$this->mdl_followups->update_by(['fu_date <=' => date('Y-m-d'), 'fu_status' => 'postponed'], ['fu_status' => 'new']);
		
		foreach ($fsSettings as $key => $value) {
			
			$module = $value->fs_table;
			$modelName = 'mdl_' . $module; 
			if($this->$modelName && isset($this->$modelName->table) && $this->$modelName->table == $value->fs_table) {
				$statuses = json_decode($value->fs_statuses);
				
				$clientTypes = $value->fs_client_types ? json_decode($value->fs_client_types) : FALSE;
				$periodicity = $value->fs_periodicity;
				$every = $value->fs_every;
				$data = $this->$modelName->get_followup($statuses, $periodicity, $every, $clientTypes);
				
				foreach ($data as $item) {
					
					$item_id = $item[$fsConfig[$value->fs_table]['id_field_name']];
					$variables = $this->$modelName->get_followup_variables($item_id);

					$variables['COMPANY_NAME'] = config_item('company_name_short');
					$variables['COMPANY_EMAIL'] = config_item('account_email_address');
					$variables['COMPANY_PHONE'] = config_item('office_phone_mask');
					$variables['COMPANY_ADDRESS'] = config_item('office_address') . ', ' . config_item('office_city') . ', ' . config_item('office_zip');
					$variables['COMPANY_BILLING_NAME'] = config_item('company_name_long');
					$variables['COMPANY_WEBSITE'] = config_item('company_site');

					$existsNewFu = $this->mdl_followups->get_many_by(['fu_module_name' => $module, 'fu_status' => 'new', 'fu_client_id' => $item['client_id']]);
					$existsPostponedFu = $this->mdl_followups->get_many_by(['fu_module_name' => $module, 'fu_status' => 'postponed', 'fu_client_id' => $item['client_id']]);

					$fuData = [];

					$fuData = [
						'fu_fs_id' => $value->fs_id,
						'fu_date' => date('Y-m-d'),
						'fu_module_name' => $value->fs_table,
						'fu_action_name' => $fsConfig[$value->fs_table]['action_name'],
						'fu_client_id' => $item['client_id'],
						'fu_item_id' => $item[$fsConfig[$value->fs_table]['id_field_name']],
						'fu_estimator_id' => $item['estimator_id'],
						'fu_status' => 'new',
						'fu_variables' => json_encode($variables)
					];

					if(!$existsNewFu || !$existsPostponedFu) {
						$this->mdl_followups->insert($fuData);
					}


				}
			}
		}
	}

	function followup_executor() {
		include('./application/libraries/Mpdf.php');
		$this->load->model('mdl_followup_settings');
		$this->load->model('mdl_followups');
		$this->load->model('mdl_estimates');
		$this->load->model('mdl_leads');
		$this->load->model('mdl_invoices');
		$this->load->model('mdl_schedule');
		$this->load->model('mdl_client_tasks'); 
		
		$where = "fs_cron = 1 AND ((CONCAT(fu_date, ' ', fs_time) <= '" . date('Y-m-d H:i:s') . "' AND fu_time IS NULL) OR (CONCAT(fu_date, ' ', fu_time) <= '" . date('Y-m-d H:i:s') . "' AND fs_time IS NULL))";
			/*'fu_date <=' => date('Y-m-d'),
			'fs_time <=' => date('H:i:s'),*/
		
		
		/* ТО ЧТО НАДО */
		$types['email'] = $types['sms'] = 'Sender';
		$types['invoice_overdue'] = $types['update_overdue'] = $types['estimate_expired'] = $types['expired_user_docs'] = $types['equipment_alarm'] = 'Function';
		
		$followUps = $this->mdl_followups->get_list('new', $where);
		
		foreach ($followUps as $key => $job) {
			
			//$driverName = 'followUp' . ucfirst($job->fs_type) . 'Sender';
			/* ТО ЧТО НАДО   */
            $driverName = 'followUp';
            if(isset($types[$job->fs_type])) {
                $driverName .= str_replace(' ', '', ucwords(str_replace('_', ' ', $job->fs_type))) . $types[$job->fs_type];
            }
			
			if(method_exists($this, $driverName)) {
				if(intval($job->fs_disabled)) {
					$this->mdl_followups->update($job->fu_id, [
						'fu_status' => 'canceled',
						'fu_comment' => 'Canceled ' . date('Y-m-d H:i:s') . ' - disabled in the settings'
					]);
				} else {
					$result = $this->$driverName($job);
					if($result) {
						$this->mdl_followups->update($job->fu_id, [
							'fu_status' => 'completed',
							'fu_comment' => 'Completed ' . date('Y-m-d H:i:s')
						]);
					} else {
						$errorMsg = isset($this->fuErrors[$job->fu_id]) && isset($this->fuErrors[$job->fu_id]['msg']) ? $this->fuErrors[$job->fu_id]['msg'] : 'Undefined';
						$this->mdl_followups->update($job->fu_id, [
							'fu_status' => 'canceled',
							'fu_comment' => 'Canceled ' . date('Y-m-d H:i:s') . ', ERROR: ' . $errorMsg
						]);
					}
				}
			}
		} 
		$this->followupErrorNotification();
	}

	private function followUpEmailSender($job = []) {
		
		$this->load->model('mdl_leads');
		$this->load->model('mdl_estimates');
		$this->load->model('mdl_invoices');
		$this->load->model('mdl_user', 'mdl_users');
		$this->load->library('email');
		$fsConfig = $this->config->item('followup_modules');

		$this->load->library('mpdf');
		$this->mpdf = new mPDF();
		$this->email->clear(TRUE);

		$modelName = 'mdl_' . $job->fu_module_name;

        $variables = [];
		
		if($this->$modelName) {
			
			$variables = $this->$modelName->get_followup_variables($job->fu_item_id);
			
			$itemData = $this->$modelName->find_by_id($job->fu_item_id);
			$note['to'] = $to = $variables['EMAIL'];//'yl@treedoctors.ca'
            $itemStatus = $job->fs_table == 'estimates' ? $itemData->status_id : $itemData->{$fsConfig[$job->fs_table]['status_field_name']};
			
			$fsStatuses = $job->fs_statuses ? json_decode($job->fs_statuses) : [];
            if(($job->fu_module_name == 'employees' && $job->fs_table == 'client_tasks')
				|| ($job->fu_module_name == 'users' && $job->fs_table == 'schedule') || $job->fs_table == 'users') {
                $fsStatuses[0] = NULL;
            }

            if(array_search($itemStatus, $fsStatuses) === FALSE) {
                $this->fuErrors[$job->fu_id]['msg'] = 'Email was not sent "' . $to . '". Item status doesn\'t match the settings';
                $this->fuErrors[$job->fu_id]['job'] = $job;
                $this->fuErrors[$job->fu_id]['variables'] = $variables;
                return FALSE;
            }

            $client = $this->mdl_followups->getClient($job->fu_client_id);
			if(intval($client->client_unsubscribe)) {
				$this->fuErrors[$job->fu_id]['msg'] = 'Email was not sent "' . $to . '". Client unsubscribed';
				$this->fuErrors[$job->fu_id]['job'] = $job;
				$this->fuErrors[$job->fu_id]['variables'] = $variables;
				return FALSE;
			}
			
			$client_id = $job->fu_client_id;
			$check = check_receive_email($client_id, $to);
			
			if(!filter_var($to, FILTER_VALIDATE_EMAIL)) {
				$this->fuErrors[$job->fu_id]['msg'] = 'Incorrect Email "' . $to . '"';
				$this->fuErrors[$job->fu_id]['job'] = $job;
				$this->fuErrors[$job->fu_id]['variables'] = $variables;
				return FALSE;
			}

			if($check['status'] != 'ok') {
				$this->fuErrors[$job->fu_id]['msg'] = 'Incorrect Email "' . $to . '"';
				$this->fuErrors[$job->fu_id]['job'] = $job;
				$this->fuErrors[$job->fu_id]['variables'] = $variables;
				return FALSE;
			}

			$signature = NULL;

			/*if($job->fu_module_name == 'invoices') {
				//154
				$user = $this->mdl_user->find_by_id(163);
				$note['from'] = $from_email = $user->user_email;
				$signature = $user->user_signature;
			}
			else*/
			if(!isset($itemData->user_signature) || !$itemData->user_signature) {
				$note['from'] = $from_email = config_item('account_email_address');
				$signature = $this->config->item('default_signature');
			}
			else {
				$note['from'] = $from_email = $itemData->user_email;
				$signature = $itemData->user_signature;
			}

			
			$keysVars = array_map(function($value) { return '[' . $value . ']'; }, array_keys($variables));
			$valuesVars = array_values($variables);
			$subject = $note['subject'] = str_replace($keysVars, $valuesVars, $job->fs_subject);
			$text = str_replace($keysVars, $valuesVars, $job->fs_template);

            $special_replace_array = [
                isset($variables['CCLINK']) ? str_replace('link', '$1', $variables['CCLINK']) : null,
                isset($variables['SIGNATURELINK']) ? str_replace('link', '$1', $variables['SIGNATURELINK']) : null,
                null
            ];

            $text = preg_replace(
                ClientLetter::CLIENT_LETTER_SPECIAL_KEYWORDS,
                $special_replace_array,
                $text
            );

			$pattern = "/<body>(.*?)<\/body>/is"; 
			preg_match($pattern, $text, $res);
			$text = isset($res[1]) && $res[1] ? $res[1] : $text;

			$toDomain = substr(strrchr($to, "@"), 1);
			/*if(array_search($toDomain, $this->config->item('smtp_domains')) !== FALSE) {
				$config = $this->config->item('smtp_mail');
				$note['from'] = $from_email = $config['smtp_user'];
				$signature = $this->config->item('default_signature');
			}*/
			$config['mailtype'] = 'html';
			$this->email->initialize($config);
			
			$this->email->to($to);
			$this->email->from($from_email, config_item('company_name_short'));
			$this->email->subject($subject);

			$text .= $signature;
			$text .= '<br><div style="text-align:center; font-size: 10px; color: rgb(71, 74, 93);"> If you no longer wish to receive these emails you may ' . 
			'<a style="color: rgb(71, 74, 93);" href="' . config_item('unsubscribe_link') . 'unsubscribe/unsubscribeAll/' . md5($client_id) . '">unsubscribe</a> at any time.</div>';
		
			if($job->fs_pdf && ($job->fu_module_name == 'invoices' || $job->fu_module_name == 'estimates')) {

				$module = $job->fu_module_name;
				$controller = $job->fu_module_name;
				$method = rtrim($job->fu_module_name, 's') . '_pdf_generate';

				$pdf = Modules::run($module . '/' . $controller . '/' . $method, $job->fu_item_id);

				$this->load->library('mpdf');
				$this->mpdf->WriteHTML($pdf['html']);
				$file = sys_get_temp_dir() . DIRECTORY_SEPARATOR .'attach_' . $pdf['file'] . '.pdf';

				if(is_file($file))
					@unlink($file);
				
				$this->mpdf->Output($file, 'F');

                if(filesize($file) < config_item('default_pdf_size')
                    && strlen(base64_encode(file_get_contents($file))) < config_item('default_pdf_size')){
                    $this->email->attach($file);
                }else{
                    $estimate_link = '<div style="text-align: center">';
                    $href = base_url("payments/" . rtrim($module, 's') . "/" . md5($variables['NO'] . $client_id));
                    $estimate_link .= '<a href="' . $href . '" style="font-family:-apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\';box-sizing:border-box;border-radius:3px;color:#fff;display:inline-block;text-decoration:none;background-color: #81BA53;border-top: 10px solid #81BA53;border-right: 18px solid #81BA53;border-bottom: 10px solid #81BA53;border-left: 18px solid #81BA53;font-size: 20px;" target="_blank" data-saferedirecturl="' . $href . '">View ' . ucfirst(rtrim($module, 's')) . '</a>';
                    $estimate_link .= '</div>';
                    $text .= $estimate_link;
                }
			}

            $this->email->message($text);

			$send = $this->email->send();

			if (!is_array($send) || array_key_exists('error', $send)) {
				$this->fuErrors[$job->fu_id]['msg'] = 'Sending Error';
				$this->fuErrors[$job->fu_id]['job'] = $job;
				$this->fuErrors[$job->fu_id]['variables'] = $variables;
				return FALSE;
			}
			$itemStatusName = $itemData->{$fsConfig[$job->fs_table]['status_field_name']};
			
			$name = uniqid();
			$periodicity = $job->fs_every ? 'Every ' : 'After ';
			$periodicity .= $job->fs_periodicity . ' Days';
			$noteText = 'Follow Up ' . strtoupper($job->fs_type) . ': ' . $variables['NO'] . ', ' . $itemStatusName . ', ' . $periodicity . ', ' . $job->fs_time . '<br>';
			$noteText .= 'Subject: "' . $subject . '"';
			$note_id = make_notes($client_id, $noteText, 'email', $itemData->lead_id);
			$dir = 'uploads/notes_files/' . $client_id .'/' . $note_id . '/';
			
			$pattern = "/<body>(.*?)<\/body>/is"; 
			preg_match($pattern, $text, $res); 
			$note['text'] = isset($res[1]) && $res[1] ? $res[1] : $text;

            $this->mpdf->Output(sys_get_temp_dir() . DIRECTORY_SEPARATOR .'attach_' . $name . '.pdf', 'F');
            bucket_move(sys_get_temp_dir() . DIRECTORY_SEPARATOR .'attach_' . $name . '.pdf', $dir . 'attach_' . $name . '.pdf', ['ContentType' => 'application/pdf']);
            @unlink(sys_get_temp_dir() . DIRECTORY_SEPARATOR .'attach_' . $name . '.pdf');
            @unlink($file);
            bucket_write_file($dir . $name . '.html', $this->load->view('clients/note_file', $note, TRUE), ['ContentType' => 'text/html']);

			return TRUE;
		}
		$this->fuErrors[$job->fu_id]['msg'] = 'Incorrect FollowUp Type';
		$this->fuErrors[$job->fu_id]['job'] = $job;
		$this->fuErrors[$job->fu_id]['variables'] = $variables;
		return FALSE;
	}

	private function followUpSmsSender($job = []) {
		$this->load->model('mdl_leads');
		$this->load->model('mdl_estimates');
		$this->load->model('mdl_invoices');
		$this->load->model('mdl_user');
		$this->load->model('mdl_schedule');
		$this->load->model('mdl_client_tasks');
		$this->load->model('mdl_employees');
		 
		$fsConfig = $this->config->item('followup_modules');
		
		$accountSid 	= $this->config->item('accountSid');
		$authToken  	= $this->config->item('authToken');
		$twilioNumber = $this->config->item('twilioNumber');
		$modelName = 'mdl_' . $job->fu_module_name;
		 
		if($this->$modelName) {
			$variables = $this->$modelName->get_followup_variables($job->fu_item_id);
			$itemData = $this->$modelName->find_by_id($job->fu_item_id);
			$keysVars = array_map(function($value) { return '[' . $value . ']'; }, array_keys($variables));
			$valuesVars = array_values($variables);
			$client_id = $job->fu_client_id;

            $itemStatus = $job->fs_table == 'estimates' ? $itemData->status_id : $itemData->{$fsConfig[$job->fs_table]['status_field_name']};
			
            $fsStatuses = $job->fs_statuses ? json_decode($job->fs_statuses) : [];
            if(($job->fu_module_name == 'employees' && $job->fs_table == 'client_tasks') 
				|| ($job->fu_module_name == 'users' && $job->fs_table == 'schedule')) {
                $fsStatuses[0] = NULL;
            }

            if(array_search($itemStatus, $fsStatuses) === FALSE) {
                $this->fuErrors[$job->fu_id]['msg'] = 'SMS was not sent to "' . $variables['PHONE'] . '". Item status doesn\'t match the settings';
                $this->fuErrors[$job->fu_id]['job'] = $job;
                $this->fuErrors[$job->fu_id]['variables'] = $variables;
                return FALSE;
            }

            $text = trim(str_replace($keysVars, $valuesVars, $job->fs_template));
			if(!$text) {
				$this->fuErrors[$job->fu_id]['msg'] = 'Empty SMS Template';
				$this->fuErrors[$job->fu_id]['job'] = $job;
				$this->fuErrors[$job->fu_id]['variables'] = $variables;
				return FALSE;
			}

			if(strlen($variables['PHONE']) < 7) {
				$this->fuErrors[$job->fu_id]['msg'] = 'Incorrect Number Format "' . $variables['PHONE'] . '"';
				$this->fuErrors[$job->fu_id]['job'] = $job;
				$this->fuErrors[$job->fu_id]['variables'] = $variables;
				return FALSE;
			}

			$toNumber = $variables['PHONE'];//'4168366877'266696687

            // TODO: remove phone validation
			$phoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();
			$swissNumberProto = $phoneUtil->parse($toNumber, "UA");

			$isValid = $phoneUtil->isValidNumber($swissNumberProto);
			
			if(!$isValid) {
				$this->fuErrors[$job->fu_id]['msg'] = 'Incorrect Number Format "' . $toNumber . '"';
				$this->fuErrors[$job->fu_id]['job'] = $job;
				$this->fuErrors[$job->fu_id]['variables'] = $variables;
				return FALSE;
			}

			$to = $phoneUtil->format($swissNumberProto, \libphonenumber\PhoneNumberFormat::E164);

			$this->load->driver('messages');

			$this->messages->send($to, $text);

			$periodicity = $job->fs_every ? 'Every ' : 'After ';
			$periodicity .= $job->fs_periodicity . ' Days';
            $itemStatusName = $itemData->{$fsConfig[$job->fs_table]['status_field_name']};
			$noteText = 'Follow Up ' . strtoupper($job->fs_type) . ': ' . $variables['NO'] . ', ' . $itemStatusName . ', ' . $periodicity . ', ' . $job->fs_time . '<br>';
			$noteText .= 'Text: "' . $text . '"';
			
			if(!empty($itemData) && isset($itemData->lead_id)) {
                $note_id = make_notes($client_id, $noteText, 'system', $itemData->lead_id);
            } else {
				$note_id = make_notes($client_id, $noteText, 'system', 0);
            }
		}

		return TRUE;
	}

	private function followUpEstimateExpiredFunction($job = [])
	{
		
		$this->load->model('mdl_followups');
		$this->load->model('mdl_estimates');
		$this->load->model('mdl_clients');
		$fsConfig = $this->config->item('followup_modules');
		/*
		$wh['fs_id'] = 39;
		$job = $this->mdl_followups->get_list('new', $wh, 1);
		$job = $job[0];
		*/
		if($job && !empty($job))
		{
			$query = $this->mdl_estimates->find_all(['estimate_id' => $job->fu_item_id]);
			$data = $query[0];
			//echo '<pre>'; var_dump($data); die;
			$this->mdl_estimates->update_estimates(['status' => 9], ['estimate_id' => $job->fu_item_id]);
			$status = array('status_type' => 'estimate', 'status_item_id' => $job->fu_item_id, 'status_value' => 9, 'status_date' => time());
			$this->mdl_estimates->status_log($status);
			make_notes($data->client_id, 'Change status for '. $data->estimate_no .' from "' . $data->est_status_name . '" to "Expired"', 'system', $data->lead_id);
		}
	}
	
	private function followUpInvoiceOverdueFunction($job = [])
	{
		$this->load->model('mdl_invoices');
		$this->load->model('mdl_clients');
		$this->load->model('mdl_estimates');
		
		if($job && !empty($job))
		{
			$invoiceData = $this->mdl_invoices->find_by_id($job->fu_item_id);
			
			$cur_date = date('Y-m-d');
			$overdue_date_timestamp = strtotime($cur_date);
			$overdue_date_tmte = strtotime('+30 days', $overdue_date_timestamp);
			$overdue_date = date('Y-m-d', $overdue_date_tmte);
			$data['overdue_date'] = $overdue_date;
			$update_status = $this->mdl_invoices->update_interest($data, $job->fu_item_id);
			$inte_data['invoice_id'] = $job->fu_item_id;
			$inte_data['overdue_date'] = $overdue_date;
			$inte_data['rate'] = INVOICE_INTEREST;
			$insert_rec = $this->mdl_invoices->insert_interest($inte_data);
            $this->mdl_invoices->update_all_invoice_interes($invoiceData->estimate_id);
            $this->mdl_estimates->update_estimate_balance($invoiceData->estimate_id);
			make_notes($job->fu_client_id, 'Status for invoice ' . $invoiceData->invoice_no . ' was modified from "' . $invoiceData->in_status . '" to "Overdue"', 'system', $invoiceData->lead_id);
		}
	}

	private function followupErrorNotification() {
		if(empty($this->fuErrors))
			return FALSE;

		$to = config_item('account_email_address');//'isq200820082008@gmail.com'
		$this->load->library('email');
		$this->email->clear(TRUE);
		$subject = "Warning! FollowUp Executor Errors";
		$text = $this->load->view('followup_errors_email', ['errors' => $this->fuErrors], TRUE);

		$config['mailtype'] = 'html';
		$this->email->initialize($config);
		
		$this->email->to($to);
		$this->email->from(config_item('account_email_address'), config_item('company_name_short'));
		$this->email->subject($subject);
		
		$this->email->message($text);
		$this->email->send();
	}

	private function followUpExpiredUserDocsFunction($job = [])
	{
		
		$fsConfig = $this->config->item('followup_modules');
		
		if($job && !empty($job))
		{
			$this->load->library('email');
			$this->email->clear(TRUE);
			$config['mailtype'] = 'html';
			$this->email->initialize($config);
			$variables = json_decode($job->fu_variables);
			
			$smsDocs = '';
			$smsText = $job->fs_template;
			$text = $job->fs_template;
			$subject = 'Expired Documents';
			$number = $variables->PHONE;
			$to = $variables->EMAIL;
			$from = $this->config->item('account_email_address');
			
			$smsDocs = "\n" . $variables->DOCUMENTS . "\n";
			$docs = '<br>' . $variables->DOCUMENTS . '<br>';
			
			$text = str_replace('[NAME]', $variables->NAME, $text);
			$text = rtrim(str_replace('[DOCUMENTS]', $docs, $text));
			$smsText = str_replace('[NAME]', $variables->NAME, $smsText);
			$smsText = str_replace('[DOCUMENTS]', $smsDocs, $smsText);

            $this->email->to($to);
            $this->email->from($from, $this->config->item('company_name_short'));
            $this->email->subject($subject);

            $this->email->message($text);

            $send = $this->email->send();

            if (is_array($send) && !array_key_exists('error', $send)) {
                return true;
            } else {
                return false;
            }

            $this->load->driver('messages');
            $this->messages->send($v['phone'], $smsText);
        }
	}
	
	private function followUpEquipmentAlarmFunction()
	{
		return $this->equipment_gps_tracker(NULL);
	}
	
	function equipmentMap($date = NULL, $code = NULL, $returnData = FALSE) {
        $this->load->driver('gps');
        if(!$this->gps->enabled()){
            //show_error('GPS driver is disabled!');
            return true;
        }

		$this->output->cache(1500);
		$this->load->model('mdl_equipments');
		$this->load->model('mdl_tracker');
		set_time_limit(0);
		if(!$date || !$code)
			show_404();
		$truck = $this->mdl_equipments->get_item(['item_code' => $code]);
		$trackerDB = $this->mdl_tracker->get_by(array('eq_td_code' => $code, 'eq_td_date' => $date));
		
		if(!$truck)
			show_404();

		$id = $truck->item_tracker_name;

		if(!$id)
			show_404();
		
		if(!empty($trackerDB))
		{
			$info = json_decode($trackerDB->eq_td_data); 
			
			if(isset($info->parkData) && !empty($info->parkData))
				$parkingsResponse = json_encode($info->parkData);
			else
			{
                $parkingsResponse = $this->gps->parkings($id, $date);
			}
			
			if(isset($info->routeData) && !empty($info->routeData))
				$routeResponse = json_encode($info->routeData);
			else
			{
                $routeResponse = $this->gps->route($id, $date);
			}
			if(isset($info->distanceData) && !empty($info->distanceData))
				$distanceResponse = json_encode($info->distanceData);
			else
			{
                $distanceResponse = $this->gps->distance($id, $date);
			}
		}
		else
		{	
			if(!$truck)
				show_404();

			$id = $truck->item_tracker_name;

			if(!$id)
				show_404();

            $routeResponse = $this->gps->route($id, $date);
            $parkingsResponse = $this->gps->parkings($id, $date);
            $distanceResponse = $this->gps->distance($id, $date);

			$insert['parkData'] = json_decode($parkingsResponse);
			$insert['routeData'] = json_decode($routeResponse);
			$insert['distanceData'] = json_decode($distanceResponse);
			
			if(!empty($insert['parkData']->data) || !empty($insert['routeData']) || !empty($insert['distanceData']->data))
				$this->mdl_tracker->insert(array('eq_td_code' => $code, 'eq_td_date' => $date, 'eq_td_data' => json_encode($insert)));
		}
		
		if($returnData)
		{
			$data['route'] = $routeResponse;
			$data['parkings'] = $parkingsResponse;
			$data['truck'] = $truck;
			return $data;
		}
		else
			$this->load->view('equipment_route', ['route' => $routeResponse, 'parkings' => $parkingsResponse, 'distanсe' => $distanceResponse, 'truck' => $truck]);
	}


	function equipment_gps_tracker($date = NULL) {
        $this->load->driver('gps');
        if(!$this->gps->enabled()){
            return true;
        }

		set_time_limit(0);
		$date = $date ? $date : date('Y-m-d', strtotime(date('Y-m-d')) - 86400);
		$this->load->model('mdl_equipments');
		$this->load->model('mdl_tracker');
		$this->load->library('mpdf');
		$office = [
			['lat' => '43.608330', 'lng' => '-79.524709'],
			['lat' => '43.604168', 'lng' => '-79.515833'],
		];

        $tracks = json_decode($this->gps->tracks());

		$data = [];
		$resultLeft = [];
		$counter = 0;
		
		foreach ($tracks as $key => $value) {

			$insert['parkData'] = array();
			$insert['routeData'] = array();
			$insert['distanceData'] = array();

            $parkingsResponse = json_decode($this->gps->parkings($value->SN_IMEI_ID, $date));
			$parkingsResponseData = isset($parkingsResponse->data) ? $parkingsResponse->data : [];
			foreach ($parkingsResponseData as $k => $v) {
				if($v[3] > $office[0]['lat'] || $v[3] < $office[1]['lat'] ||
					$v[4] < $office[0]['lng'] || $v[4] > $office[1]['lng']) {
					$resultLeft[$counter]['route_travel_time'] = 0;
					$reversePark = array_reverse($parkingsResponseData);
					foreach($reversePark as $key=>$val)
					{
						if(isset($reversePark[$key+1]))
							$resultLeft[$counter]['route_travel_time'] += strtotime($val[1]) - strtotime($reversePark[$key+1][2]);
					}
					$resultLeft[$counter]['parkingData'] = $parkingsResponseData;

                    $distanceResponse = json_decode($this->gps->distance($value->SN_IMEI_ID, $date));

					if(isset($distanceResponse->data) && !empty($distanceResponse->data))
					{
						$resultLeft[$counter]['route_travel_time'] = 0;
						$reversePark = array_reverse($parkingsResponseData);
						foreach($reversePark as $key=>$val)
						{
							if(isset($reversePark[$key+1]))
								$resultLeft[$counter]['route_travel_time'] += strtotime($val[1]) - strtotime($reversePark[$key+1][2]);
						}

						$resultLeft[$counter]['name'] = $value->DeviceName;
						$item = $this->mdl_equipments->get_item(array('item_tracker_name' => $value->SN_IMEI_ID));
						
						$insert['parkData'] = $parkingsResponse;

                        $routeResponse = json_decode($this->gps->route($value->SN_IMEI_ID, $date));

						$insert['routeData'] = $routeResponse;
						$insert['distanceData'] = $distanceResponse;
						if($item) {
							$resultLeft[$counter]['code'] = $item->item_code;
							$resultLeft[$counter]['item_id'] = $item->item_id;
						}

						if($distanceResponse && !empty($distanceResponse))
						{
							$resultLeft[$counter]['kms'] = 0;
							foreach($distanceResponse->data as $a=>$b)
								$resultLeft[$counter]['kms'] += $b[6];
							$this->mdl_equipments->insert_gps_distance(array('egtd_item_id' => $item->item_id, 'egtd_date' => $date, 'egtd_counter' => round($resultLeft[$counter]['kms']/* / 1000*/, 2)));
						}
						$counter++;
						if(isset($item->item_code) && $item->item_code)
							$this->mdl_tracker->delete_by(array('eq_td_code' => $item->item_code, 'eq_td_date' => $date));
						if(!empty($insert['parkData']->data) || !empty($insert['routeData']) || !empty($insert['distanceData']->data))
							$this->mdl_tracker->insert(array('eq_td_code' => $item->item_code, 'eq_td_date' => $date, 'eq_td_data' => json_encode($insert)));
					break;
					}		
					
				}
			}
			
			
		}
		$data['link'] = '<a>ascasc</a>';
		$data['link'] = $this->to_tracker_emails($date, $resultLeft);
		$data['resultLeft'] = $resultLeft;
		$data['date'] = $date;
		
		$text = $this->load->view('equipment_alarm', $data, TRUE);
		
		$this->load->library('email');
		$config['mailtype'] = 'html';
		$this->email->initialize($config);
		if(!empty($resultLeft)) {
			$to = 'ys@treedoctors.ca, yl@treedoctors.ca, gleba.ruslan@gmail.com, info@treedoctors.ca, dmitriy.vashchenko@gmail.com';
			//$to = 'isq200820082008@gmail.com';
			$this->email->to($to);
			$this->email->from('info@treedoctors.ca', 'Tree Doctors');
			$this->email->subject('EQUIPMENT ALARM');

			$this->email->message($text);

            $send = $this->email->send();

            if (is_array($send) && !array_key_exists('error', $send)) {
				return TRUE;
            } else {
                return FALSE;
            }
		}

	}

//*******************************************************************************************************************
//*************
//*************											cron_checkOverdueDate									Create leads function;
//*************
//*******************************************************************************************************************


	public function cron_checkOverdueDate() {
		include('./application/libraries/Mpdf.php');
		$this->load->model('mdl_estimates', 'mdl_estimates');

		$this->load->model('mdl_invoice_status');
		$overdue_status = element('invoice_status_id', (array)$this->mdl_invoice_status->get_by(['invoice_status_active'=>1, 'is_overdue' => 1]), 0);

		$get_records = $this->mdl_invoices->getInvoiceOverdueRec();
		
		if($get_records){
			foreach($get_records as $key => $row){
				$data = array();
				$timestamp = strtotime($row->overdue_date . '+' . \application\modules\invoices\models\Invoice::getInvoiceTerm($row->client_type) . ' days');
				
				$overdue_date = date('Y-m-d', $timestamp);                                
				$data['in_status'] = $overdue_status;
				$data['overdue_date'] = $overdue_date;
				$update_status = $this->mdl_invoices->update_interest($data,$row->id);
				if($update_status){
					$inte_data['invoice_id'] = $row->id;
					$inte_data['overdue_date'] = $overdue_date;
					$inte_data['rate'] = INVOICE_INTEREST;
					$insert_rec = $this->mdl_invoices->insert_interest($inte_data);
                    $this->mdl_invoices->update_all_invoice_interes($row->estimate_id);
					$this->mdl_estimates->update_estimate_balance($row->estimate_id); //estimate balance
                    //create a new job for synchronization interest in QB
//                    pushJob('quickbooks/invoice/exportinterestinqb', serialize(['invoiceId' => $row->id]));
                    pushJob('quickbooks/invoice/syncinvoiceinqb', serialize([
                        'id' => $row->id,
                        'qbId' => $row->invoice_qb_id
                    ]));
				}
			}
		}
	}

	private function invoice_pdf_generate($invoice_id)
	{
		//Get invoices data
		$data['invoice_data'] = $this->mdl_invoices->find_by_id($invoice_id);
		$invoice_no = $data['invoice_data']->invoice_no;

		//Get workorder informations - using common function from MY_Models;
		$workorder_id = $data['invoice_data']->workorder_id;
		$this->load->model('mdl_workorders', 'mdl_workorders');
		$data['workorder_data'] = $this->mdl_workorders->find_by_id($workorder_id);

		//Get estimate informations - using common function from MY_Models;
		$estimate_id = $data['workorder_data']->estimate_id;
		$data['estimate_data'] = $this->mdl_invoices->getEstimatedData($estimate_id);
		//estimate services
		$this->load->model('mdl_estimates', 'mdl_estimates');
		$data['estimate_services_data'] = $this->mdl_estimates->find_estimate_services($estimate_id);
		$this->load->model('mdl_clients', 'mdl_clients');
		$data['payments_data'] = $this->mdl_clients->get_payments(array('client_payments.estimate_id' => $estimate_id));
		//Discount data
		$data['discount_data'] = $this->mdl_clients->get_discount(array('discounts.estimate_id' => $estimate_id));
		$data['invoice_interest_data'] = $this->mdl_invoices->getInterestData($invoice_id);

		//Get client_id and retrive client's information:
		$id = $data['estimate_data']->client_id;
		$data['client_data'] = $this->mdl_clients->find_by_id($id);
		$data['client_contact'] = $this->mdl_clients->get_primary_client_contact($id);

		$file = "Invoice_" . $invoice_no . " - " . str_replace('/', '_', $data['client_data']->client_address);

        list($result, $view) = Modules::find('pdf_templates/' . config_item('company_dir') . '/invoice_pdf', 'includes', 'views/');
        if($result) {
            $html = $this->load->view('includes/pdf_templates/' . config_item('company_dir') . '/' . 'invoice_pdf', $data, TRUE);
        } else {
            $html = $this->load->view('includes/pdf_templates/invoice_pdf', $data, TRUE);
        }
		return array('file' => $file, 'html' => $html);
	}

	// End. cron_checkOverdueDate;


	public function autobackup()
	{
		$this->load->database();
		$hours = date('H');
		$mins = date('i');
		if ($hours != '00')
			$filename = 'autobackup_' . date('d') . date('M') . date('Y') . '-' . $hours . ':' . $mins . '.sql';
		else {
			for ($i = 0; $i <= 23; $i += 1) {
				$time = time() - 60 * 60 * 24 + ($i * 60 * 60);
				$h = date('H', $time);
				$file = FCPATH . 'docs/autobackup_' . date('d', $time) . date('M', $time) . date('Y', $time) . '-' . $h . 'h.sql';
				$file1 = FCPATH . 'docs/autobackup_' . date('d', $time) . date('M', $time) . date('Y', $time) . '-' . $h . ':00.sql';
				$file2 = FCPATH . 'docs/autobackup_' . date('d', $time) . date('M', $time) . date('Y', $time) . '-' . $h . ':30.sql';
				if (is_file($file))
					unlink($file);
				if (is_file($file1))
					unlink($file1);
				if (is_file($file2))
					unlink($file2);
			}
			$filename = 'autobackup_' . date('d', time() - 60 * 60 * 24) . date('M', time() - 60 * 60 * 24) . date('Y', time() - 60 * 60 * 24) . '.sql';
		}
		$mysqldump = "mysqldump --ignore-table=" . $this->db->database . ".user_history_log --ignore-table=" . $this->db->database . ".tracking_parking --ignore-table=" . $this->db->database . ".newsletters  -h " . $this->db->hostname . " -u " . $this->db->username . " -p" . $this->db->password . " " . $this->db->database . " > " . FCPATH . "docs.bak/" . $filename;
		exec($mysqldump);
	}

	function moveBackupsToCloud()
	{
		$pathFrom = FCPATH . 'docs.bak/*';
		$pathTo = FCPATH . 'docs/';
		$command = 'mv -if ' . $pathFrom . ' ' . $pathTo;
		exec($command);
	}

	public function autologout()
	{
		$this->load->model('mdl_employees');
		$this->load->model('mdl_emp_login');
		$this->load->model('mdl_worked');

		$date = time();
		/*****************EMPLOYEES WITH YEARLY RATE*********************/
		
		if(date('N', $date) <= 5)
		{
			$employeesQuery = $this->mdl_user->get_payroll_user('emp_yearly_rate != 0 AND emp_yearly_rate IS NOT NULL AND active_status <> "no"');
			$employees = array();
			if($employeesQuery)
			{
				$employees = $employeesQuery->result_array();
				foreach($employees as $employee)
				{
					$wh = array();
					$wh['login_date'] = date('Y-m-d', $date);
					$wh['login_user_id'] = $employee['employee_id'];
					$wh['active_status !='] =  'no';
					$data = $this->mdl_emp_login->get_peoples($wh);
					if(empty($data))
					{
						$insert = array();
						$insert['login'] = '08:00:00';
						$insert['logout'] = '16:30:00';
						$insert['login_user_id'] = $employee['employee_id'];
						$insert['login_date'] = date('Y-m-d', $date);
						
						$this->mdl_emp_login->insert($insert);
					}
				}
			}
		}

		$wh = array();
		$wh['login_date'] =  date('Y-m-d');
		$wh['logout'] =  NULL;
		$data = $this->mdl_emp_login->get_peoples($wh);
		
		$email_data['ids'] = array();
		foreach ($data as $key => $val) {
			$this->mdl_emp_login->update($val->login_id, array('logout' => date('H:i:s')));
			$this->mdl_worked->update($val->login_worked_id, array('worked_auto_logout' => 1));
		}

		/*****************EMPLOYEES WITH YEARLY RATE*********************/
		if(date('N') <= 5)
		{
			$employeesQuery = $this->mdl_user->get_payroll_user('emp_yearly_rate != 0 AND emp_yearly_rate IS NOT NULL AND active_status <> "no"');
			
			$employees = array();
			if($employeesQuery)
			{
				$employees = $employeesQuery->result_array();
				
				foreach($employees as $employee)
				{
					for($i = strtotime('monday this week'); $i <= strtotime(date('Y-m-d')); $i+= 86400)
					{
						$wh = array();
						$wh['login_date'] = date('Y-m-d', $i);
						$wh['login_user_id'] = $employee['employee_id'];
						$wh['active_status !='] =  'no';
						$data = $this->mdl_emp_login->get_peoples($wh);
					
						if(empty($data))
						{
							$insert = array();
							$insert['login'] = '08:00:00';
							$insert['logout'] = '16:30:00';
							$insert['login_user_id'] = $employee['employee_id'];
							$insert['login_date'] = date('Y-m-d', $i);
							$this->mdl_emp_login->insert($insert);
						}
					}
				}
			}
		}

		/******************EMAIL REPORT**********************************/
	}
    /* Delete this method if date > 16.09.2021
	function estimatesReminder()
	{
		include('./application/libraries/Mpdf.php');
		$this->load->model('mdl_estimates_orm');
		$this->load->model('mdl_letter');
		$estimatesGrops[0]['data'] = $this->mdl_estimates_orm
			->join('clients', 'clients.client_id = estimates.client_id')
			->join('clients_contacts', 'clients.client_id = clients_contacts.cc_client_id AND clients_contacts.cc_print = 1', 'left')
			->join('leads', 'leads.lead_id = estimates.lead_id')
			->join('users', 'users.id = estimates.user_id')
			->get_many_by([
				'status >' => 1,
				'status <' => 4,
				'unsubscribe' => 0,
				'client_unsubscribe' => NULL,
				'notification' => 1,
				'date_created >=' => strtotime(date('Y-m-d')) - 7 * 86400,
				'date_created <' => strtotime(date('Y-m-d')) - 6 * 86400,
			]);

		$estimatesGrops[0]['tpl'] = $this->mdl_letter->find_by_id(13);

		$estimatesGrops[1]['data'] = $this->mdl_estimates_orm
			->join('clients', 'clients.client_id = estimates.client_id')
			->join('clients_contacts', 'clients.client_id = clients_contacts.cc_client_id AND clients_contacts.cc_print = 1', 'left')
			->join('leads', 'leads.lead_id = estimates.lead_id')
			->join('users', 'users.id = estimates.user_id')
			->get_many_by([
				'status >' => 1,
				'status <' => 4,
				'unsubscribe' => 0,
				'client_unsubscribe' => NULL,
				'notification' => 2,
				'date_created >=' => strtotime(date('Y-m-d')) - 14 * 86400,
				'date_created <' => strtotime(date('Y-m-d')) - 13 * 86400,
			]);
		$estimatesGrops[1]['tpl'] = $this->mdl_letter->find_by_id(14);

		$estimatesGrops[2]['data'] = $this->mdl_estimates_orm
			->join('clients', 'clients.client_id = estimates.client_id')
			->join('clients_contacts', 'clients.client_id = clients_contacts.cc_client_id AND clients_contacts.cc_print = 1', 'left')
			->join('leads', 'leads.lead_id = estimates.lead_id')
			->join('users', 'users.id = estimates.user_id')
			->get_many_by([
				'status >' => 1,
				'status <' => 4,
				'unsubscribe' => 0,
				'client_unsubscribe' => NULL,
				'notification' => 3,
				'date_created >=' => strtotime(date('Y-m-d')) - 28 * 86400,
				'date_created <' => strtotime(date('Y-m-d')) - 27 * 86400,
			]);
		$estimatesGrops[2]['tpl'] = $this->mdl_letter->find_by_id(15);

		foreach ($estimatesGrops as $num => $group) {
			foreach ($group['data'] as $key => $estimate) {
				$this->mpdf = new mPDF();
				$this->load->library('email');
				$this->email->clear(TRUE);

				$tpl = $group['tpl']->email_template_text;
				$tpl = str_replace('[NAME]', $estimate->cc_name, $tpl);
				$tpl = str_replace('[ADDRESS]', $estimate->lead_address, $tpl);
				$tpl = str_replace('[ESTIMATOR_NAME]', $estimate->firstname . ' ' . $estimate->lastname, $tpl);
                $tpl = preg_replace('/\[CCLINK:"(.*?)"\]/is', '<a href="http://payments.treedoctors.ca/payments/' . md5($estimate->estimate_no . $estimate->client_id) . '">$1</a>', $tpl);
				$tpl = str_replace('[CCLINK]', '<a href="http://payments.treedoctors.ca/payments/' . md5($estimate->estimate_no . $estimate->client_id) . '">link</a>', $tpl);

                $brand_id = get_brand_id($estimate, $estimate);

				$tpl = str_replace(
					['[COMPANY_NAME]', '[COMPANY_EMAIL]', '[COMPANY_PHONE]', '[COMPANY_ADDRESS]', '[COMPANY_BILLING_NAME]', '[COMPANY_WEBSITE]'], 
					[
                        brand_name($brand_id),
                        brand_email($brand_id),
                        brand_phone($brand_id),
                        brand_address($brand_id, $this->config->item('office_address') . ', ' . $this->config->item('office_city') . ', ' . $this->config->item('office_zip')),
                        brand_name($brand_id, true),
                        brand_site($brand_id)
					],
					$tpl
				);

				$tpl .= $estimate->user_signature;

				$tpl .= '<div style="text-align:center; font-size: 10px;">' . 
						'This email was sent to ' . $estimate->cc_email . 
						'. If you no longer wish to receive these emails you may ' . 
						'<a href="http://unsubscribe.treedoctors.ca/unsubscribe/estimate/' . md5($estimate->estimate_id . $estimate->client_id) . '">unsubscribe</a>' . 
						' at any time.' . 
						'</div>';

				$pdf = Modules::run('estimates/estimates/estimate_pdf_generate', $estimate->estimate_id);

				$this->mpdf->WriteHTML($pdf['html']);
				$file = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $pdf['file'] . '.pdf';
				$this->mpdf->Output($file, 'F');

				$this->load->library('email');
				$config['mailtype'] = 'html';
				$this->email->initialize($config);
				$this->email->attach($file);

				$name = ($estimate->firstname && $estimate->lastname) ? ' - ' . $estimate->firstname . ' ' . $estimate->lastname : '';

				$from = ($estimate->user_email) ? $estimate->user_email : $this->config->item('account_email_address');
				$to = trim($estimate->cc_email);

				$this->email->to($to);
				$this->email->from($from, 'Tree Doctors' . $name);
				$this->email->subject('Estimate for tree services');
				$this->email->message($tpl);

				if($this->email->send())
					$this->mdl_estimates_orm->update($estimate->estimate_id, ['notification' => ($estimate->notification + 1)]);
				@unlink($file);
			}
		}
	}*/
	
	function backup_payroll_pdf()
	{
		set_time_limit(0);
		$this->load->model('mdl_payroll');
		
		$payroll = $this->mdl_payroll->get_by([
			'payroll_day <' => date('Y-m-d'),
			'payroll_start_date <' => date('Y-m-d'),
			'payroll_end_date <' => date('Y-m-d'),
			'payroll_end_date' => date('Y-m-d', strtotime("-7 day"))
		]);

		if(empty($payroll))
			return FALSE;

		Modules::run('employees/employees/payroll_all_pdf', $payroll->payroll_id, 'F');
	}
	
	function to_tracker_emails($date = NULL, &$resultLeft = NULL)
	{
		$this->load->helper('estimates_helper');
		$this->load->library('mpdf');
		$this->load->model('mdl_schedule');
		$this->load->model('mdl_equipments');
		/*
		sudo apt-get install phantomjs
		*/

		$imgs = array();
		$routeImageW = 640;
		$routeImageH = 336;
		foreach($resultLeft as $key=>$val)
		{
			$data['map_url'] = NULL;
			$data['events'] = NULL;
			$data['tools'] = NULL;
			$data['items'] = NULL;
			$data['route_kms'] = $resultLeft[$key]['route_kms'] = 0;
			$resultLeft[$key]['planned_travel_time'] = 0;
			$resultLeft[$key]['actual_worked_time'] = array();
			$resultLeft[$key]['events'] = array();
			$resultLeft[$key]['items'] = array();
			$waypoints = array();
			$truck = $this->mdl_equipments->get_item(['item_code' => $val['code']]);
			$data['code'] = $val['code'];
			$data['track_name'] = $truck->item_name;
			
			$itemTeamInfo = $this->mdl_schedule->get_team_items(array('team_date >' => strtotime($date) - 4000,  'team_date <' => strtotime($date) + 86400, 'item_id' => $val['item_id']));
			
			if(!empty($itemTeamInfo))
			{
				
				$team_id = $itemTeamInfo[0]['team_id'];
				$team = $this->mdl_schedule->get_teams(array('schedule_teams.team_id' => $team_id));
				$data['items'] = $this->mdl_schedule->getTeamsMembersWithOrder(NULL, $team_id);
				if(!empty($team))
				{
					$data['date'] = $team[0]->team_date;
					$wdata['schedule.event_start >='] = $team[0]->team_date; 
					$wdata['schedule.event_end <'] = $team[0]->team_date + 86400;
				}
				$wdata['schedule_teams.team_id'] = $team_id;
				$data['events'] = $this->mdl_schedule->get_events_dashboard($wdata);
				
				$data['tools'] = $this->mdl_schedule->get_team_tools(['stt_team_id' => $team_id]);
				$origin = $destination = config_item('office_location');
				$jobs = [];
				foreach($data['events'] as $k=>$v)
				{
					if(!$v['latitude'])
					{
						$coords = get_lat_lon($v['lead_address'], $v['lead_city'], $v['lead_state'], $v['lead_zip']);
						$v['latitude'] = $coords['lat'];
						$v['longitude'] = $coords['lon'];
					}
					$time = 0;
					$waypoints[] = $v['latitude'].','. $v['longitude'];
					$resultLeft[$key]['planned_travel_time'] += $v['planned_travel_time'];
					
					if($v['latitude'] && $v['longitude'])
					{
						$ev[0]['lat'] = $v['latitude'] + 0.001754;
						$ev[0]['lng'] = $v['longitude'] - 0.002758;
						$ev[1]['lat'] = $v['latitude'] - 0.001908;
						$ev[1]['lng'] = $v['longitude'] + 0.002639;
						
						foreach($resultLeft[$key]['parkingData'] as $j=>$park)
						{
							
							if($park[3] < $ev[0]['lat'] && $park[3] > $ev[1]['lat'] && $park[4] > $ev[0]['lng'] && $park[4] < $ev[1]['lng'])
								$time += strtotime($park[2]) - strtotime($park[1]);
							
						}
						$resultLeft[$key]['actual_worked_time'][$k] = $time;
						$data['events'][$k]['actual_worked_time'] = $time;
					}
					$jobs[] = [
						'planned_service_time' => $v['planned_service_time'],
						'estimator' => $v['emailid']
					];
				}
				
				
				$googleData = getStaticGmapURLForDirection($origin, $destination, $waypoints, '640x336', $jobs);
				$data['map_url'] = $googleData['link'];

				if(isset($googleData['data']) && isset($googleData['data']['routes']) && !empty($googleData['data']['routes'])) {
					foreach($googleData['data']['routes'][0]['legs'] as $k=>$v) {
						$data['route_kms'] += $v['distance']['value'];
					}
				}
				$resultLeft[$key]['events'] = $data['events'];
				$resultLeft[$key]['items'] = $data['items'];
			}
			if(!isset($data['date']))
				$data['date'] = strtotime($date);
			//echo "RUN phantom " . $val['code'] . "\n";
			$command = "QT_QPA_PLATFORM=offscreen /usr/lib/phantomjs/phantomjs page.js " . $this->config->item('base_url') . "cron/equipmentMap/$date/{$val['code']} /tmp/{$date}_{$val['code']}.png $routeImageW $routeImageH '#map_canvas' 2>&1";

			
			$imgs[] = $data['map_url2'] = exec($command);
			
 			$data['actual_distance'] = $val['kms'];
 			$resultLeft[$key]['route_kms'] = $data['route_kms'];
			$html = $this->load->view('workorder_overview', $data, TRUE);
			$this->mpdf->_setPageSize('Letter', $this->mpdf->DefOrientation);
			$this->mpdf->AddPage('P', 'Letter', 0, '', 0, 0, 0, 0, 0, 0);
			$this->mpdf->SetHtmlFooter('');
			$this->mpdf->WriteHTML($html);
			
		}
		
		$dir = 'uploads/equipments_files/reports/';
		$file = $date . '.pdf';
		
		if(is_bucket_file($dir . $file))
			bucket_unlink($dir . $file);
		$uniq = uniqid();
		$this->mpdf->Output(sys_get_temp_dir() . DIRECTORY_SEPARATOR . $uniq . '-' . $file, 'F');

		bucket_move(sys_get_temp_dir() . DIRECTORY_SEPARATOR . $uniq . '-' . $file, $dir . $file);

		foreach($imgs as $k=>$v)
			unlink($v);
		return ltrim($dir . $file, './');
	}

	function icons() {
		define("FONT_SIZE", 7);
		define("FONT_PATH", "./assets/Ubuntu-Medium.ttf");
		define("FONT_COLOR", 0x00000000);

		$numargs = func_num_args();

		$arg_list = func_get_args();

		$gdimage = imagecreatefromstring(file_get_contents("assets/marker.png"));
		imagesavealpha($gdimage, true);
		
		for ($i = 0; $i < $numargs; $i++) {
			$text = $arg_list[$i];
			
			list($x0, $y0, , , $x1, $y1) = imagettfbbox(FONT_SIZE, 0, FONT_PATH, $text);
			$imwide = imagesx($gdimage);
			$imtall = imagesy($gdimage) - 14;                  // adjusted to exclude the "tail" of the marker
			$bbwide = abs($x1 - $x0);
			$bbtall = abs($y1 - $y0);
			$tlx = ($imwide - $bbwide) >> 1; $tlx += 1;        // top-left x of the box
			$tly = ($imtall - $bbtall) >> 1; $tly -= 1;        // top-left y of the box
			$bbx = $tlx - $x0;                                 // top-left x to bottom left x + adjust base point
			$bby = $tly + $bbtall - $y0 + ($i * 10);           // top-left y to bottom left y + adjust base point
			imagettftext($gdimage, FONT_SIZE, 0, $bbx, $bby, FONT_COLOR, FONT_PATH, $text);
		}
		
		header("Content-Type: image/png");
		header("Expires: " . gmdate("D, d M Y H:i:s", time() + 60 * 60 * 24 * 180) . " GMT");
		imagepng($gdimage);
	}

	function test() {
		//die;
        $test = '{"fields":{"contactForm_name":{"title":"Enter your full name*","value":"test by dv","type":"text"},"contactForm_phoneNumber":{"title":"Enter your phone number*","value":"777877777777","type":"phone"},"contactForm_email":{"title":"Enter your e-mail*","value":"dmitriy.vashchenko@mail.ru","type":"email"},"4087ac06-b82b-4c2e-b967-80f361470a0e":{"title":"Type your message","value":"test","type":"textArea"}}}';
        $data = json_decode($test);
        foreach($data->fields as $key=>$val)
        {
            echo '<pre>'; var_dump($data->fields, $val, $key); die;
        }
        echo '<pre>'; var_dump(); die;
		$this->load->model('mdl_leads');
		$devices = $this->mdl_leads->get_client_leads(22660)->result();

		echo '<pre>'; var_dump($devices); die;
		$sendTo = [];
		foreach($devices as $k=>$v)
			$sendTo[] = $v['device_id'];

		$this->load->model('mdl_invoices');

		$data['invoices_statuses'] = $get_records = $this->mdl_invoices->get_followup([], '05:00', 1,  FALSE);
		 
		echo '<pre>'; var_dump($this->db->last_query());  die;
		foreach($data['invoices_statuses'] as $k=>$v)
		{
			echo '<pre>'; var_dump($v); 
		} die;
		$this->load->model('mdl_schedule');
		$data['from'] = strtotime('2019-08-01' . " 00:00:00");
		$data['to'] = strtotime('2019-08-01' . " 23:59:59");
		
		$events = $this->mdl_schedule->get_events(['schedule.event_start >=' => $data['from'], 'schedule.event_end <=' => $data['to']], FALSE, 'schedule.event_team_id ASC, schedule.event_start ASC');
		echo '<pre>'; var_dump($this->db->last_query()); die;
		include('./application/libraries/SMTPClient.php');
		$smtpClient = new SMTPClient();
		$smtpClient->setServer("smtp.gmail.com", "465", 'ssl');
		$smtpClient->setSender("info@treedoctors.ca", "info@treedoctors.ca", "TreePa$$2019");
		$smtpClient->setMail("dmitriy.vashchenko@gmail.com",
								"My first email with SMTPClient", 
								"<strong>TEXT</strong>"
								. " is working."
								, "html");
		$smtpClient->attachFile("ArborCare-Logo-1.png", 
									file_get_contents("./assets/arborcare/print/ArborCare-Logo-1.png"));
		$smtpClient->sendMail();
	
	}

	/*
	function followUpEstimateExpiredFunction($job = [])
	{
		
		$this->load->model('mdl_followups');
		$this->load->model('mdl_estimates');
		$this->load->model('mdl_clients');
		$fsConfig = $this->config->item('followup_modules');
		/*
		$wh['fs_id'] = 39;
		$job = $this->mdl_followups->get_list('new', $wh, 1);
		$job = $job[0];
		*/
		/*
		if($job && count($job))
		{
			$query = $this->mdl_estimates->find_all(['estimate_id' => $job->fu_item_id]);
			$data = $query[0];
			//echo '<pre>'; var_dump($data); die;
			$this->mdl_estimates->update_estimates(['status' => 9], ['estimate_id' => $job->fu_item_id]);
			$status = array('status_type' => 'estimate', 'status_item_id' => $job->fu_item_id, 'status_value' => 9, 'status_date' => time());
			$this->mdl_estimates->status_log($status);
			make_notes($data->client_id, 'Change status for '. $data->estimate_no .' from "' . $data->est_status_name . '" to "Expired"', 'system', $data->lead_id);
		}
	}
		
	function followUpInvoiceOverdueFunction($job = [])
	{
		$this->load->model('mdl_invoices');
		$this->load->model('mdl_clients');
		/*
		$this->load->model('mdl_followups');
		$wh['fs_id'] = 40;
		$job = $this->mdl_followups->get_list('new', $wh, 1);
		$job = $job[0];
		*/
		/*
		if($job && count($job))
		{
			$invoiceData = $this->mdl_invoices->find_by_id( $job->fu_item_id);
			
			$cur_date = date('Y-m-d');
			$overdue_date_timestamp = strtotime($cur_date);
			$overdue_date_tmte = strtotime('+30 days', $overdue_date_timestamp);
			$overdue_date = date('Y-m-d', $overdue_date_tmte);
			$data['overdue_date'] = $overdue_date;
			$update_status = $this->mdl_invoices->update_interest($data, $job->fu_item_id);
			$inte_data['invoice_id'] = $job->fu_item_id;
			$inte_data['overdue_date'] = $overdue_date;
			$inte_data['rate'] = INVOICE_INTEREST;
			$insert_rec = $this->mdl_invoices->insert_interest($inte_data);
			make_notes($job->fu_client_id, 'Status for invoice ' . $invoiceData->invoice_no . ' was modified from "' . $invoiceData->in_status . '" to "Overdue"', 'system', $invoiceData->lead_id);
		}
		//echo '<pre>'; var_dump($invoiceData); die;
	}
	*/
	function followUpUpdateOverdueFunction($job = [])
	{
		$this->load->model('mdl_invoices');
		$this->load->model('mdl_clients');
		
		$this->load->model('mdl_followups');
		$this->load->model('mdl_invoice_status');
		$overdue_status = element('invoice_status_id', (array)$this->mdl_invoice_status->get_by(['invoice_status_active'=>1, 'is_overdue' => 1]), 0);

		if($job && !empty($job))
		{
			$invoiceData = $this->mdl_invoices->find_by_id($job->fu_item_id);
			$timestamp = strtotime($invoiceData->overdue_date . '+' . \application\modules\invoices\models\Invoice::getInvoiceTerm($invoiceData->client_type) . ' days');
			
			$overdue_date = date('Y-m-d', $timestamp);
			
			$data['in_status'] = $overdue_status;
			$data['overdue_date'] = $overdue_date;
			
			$update_status = $this->mdl_invoices->update_interest($data,$job->fu_item_id);
			if($update_status){
				$inte_data['invoice_id'] = $job->fu_item_id;
				$inte_data['overdue_date'] = $overdue_date;
				$inte_data['rate'] = INVOICE_INTEREST;
				$insert_rec = $this->mdl_invoices->insert_interest($inte_data);
			}
		}
	}
	
	function test_cc()
	{
		$idsStr = '10009826';
		$ids = explode(', ', $idsStr);
		foreach($ids as $id) {
			var_dump(check_transaction($id));
		}
	}
	
	function check_cc_transactions()
	{
        $this->load->library('Payment/ArboStarProcessing', null, 'arboStarProcessing');
		$days = 90;
		$declined = [];
		$this->load->model('mdl_clients');
		$date = date("Y-m-d", strtotime("-$days day", strtotime(date("Y-m-d"))));
		$this->db->select("clients.client_name, transactions.payment_driver, transactions.payment_transaction_id, estimates.client_id, estimates.estimate_no, client_payments.*, FROM_UNIXTIME(payment_date, '%Y-%m-%d %H:%i:%s') as formated_date", FALSE);
		$this->db->join('estimates', 'client_payments.estimate_id = estimates.estimate_id');
		$this->db->join('clients', 'estimates.client_id = clients.client_id');
		$this->db->join('transactions','client_payments.payment_trans_id = transactions.payment_transaction_id');
		$this->db->where('payment_method_int', config_item('default_cc'));
		$this->db->where('payment_amount >', 0);
		$this->db->where('(payment_alarm < 2 OR payment_alarm IS NULL)');
		$this->db->where("FROM_UNIXTIME(payment_date, '%Y-%m-%d') >=", $date);
		$payments = $this->db->get('client_payments')->result();
		if($payments && !empty($payments))
		{
			foreach($payments as $k=>$v)
			{
                if(!$v->payment_trans_id) {
                    $this->mdl_clients->update_payment($v->payment_id, ['payment_alarm' => ($v->payment_alarm + 1)]);
                    $declined[] = $v;
                    continue;
                }

                // TODO: clarify parameter to checkTransaction - $v->payment_id or $v->payment_trans_id
                try {
                    $result = $this->arboStarProcessing->checkTransaction($v->payment_id, $v->payment_driver);
                }
                catch (PaymentException $e) {
                    $result = []; // ???
                }

                $updData = [
                    'payment_alarm' => null
                ];

                if($result['status'] == Payment::TRANSACTION_STATUS_SUCCESS){
                    if($v->payment_amount != $result['settled_amount']){
                        $updData['payment_amount'] = $result['settled_amount'];
                        $updData['payment_alarm'] = ($v->payment_alarm + 1);
                    }
                    $this->mdl_clients->update_payment($v->payment_id, $updData);
                } elseif($result['status'] == Payment::TRANSACTION_STATUS_PENDING || $result['status'] == Payment::TRANSACTION_STATUS_REVIEW) {
                    // void;
                } else {
                    $updData['payment_amount'] = 0;
                    $updData['payment_alarm'] = ($v->payment_alarm + 1);
                    $this->mdl_clients->update_payment($v->payment_id, ['payment_alarm' => ($v->payment_alarm + 1)]);
                    $declined[] = $v;
                }
                $log = json_decode($v->payment_transaction_log);
                toLog($log,$result['response']);

                PaymentTransaction::updateTransaction($v->payment_transaction_id, [
                    'payment_transaction_remote_reason_code' => $result['reason_code'],
                    'payment_transaction_remote_reason_description' => $result['reason_description'],
                    'payment_transaction_status' => $result['status'],
                    'payment_transaction_remote_status' => $result['remote_status'],
                    'payment_transaction_settled_amount' => $result['settled_amount'],
                    'payment_transaction_log' => json_encode($log, JSON_PRETTY_PRINT)
                ]);
			}

			if(empty($declined)) {
				echo "ALL GOOD";
				return TRUE;
			}

			$to = 'gleba.ruslan@gmail.com';
			$this->load->library('email');
			$this->email->clear(TRUE);
			$subject = "Warning! Payments Checker Alarm!";
			$text = $this->load->view('payments_alarm', ['payments' => $declined], TRUE);

			$config['mailtype'] = 'html';
			$this->email->initialize($config);
			
			$this->email->to($to);
			$this->email->from('info@treedoctors.ca', 'Tree Doctors');
			$this->email->subject($subject);
			
			$this->email->message($text);

			$this->email->send();

			return FALSE;
		}
		echo "ALL GOOD 1";
		return TRUE;
	}
	
	function remove_backup_db()
	{
		$date =  strtotime("-1 month", strtotime(date('Y-m-d')));
		$dir = './docs/';
		$dh = opendir($dir);
		$i = 0; 
		$fileNames = [];
		
		while(($file = readdir($dh)) !== false) {
			if (is_file($dir . $file)){
				$fileTime=filemtime($dir . $file);
				if ($fileTime < $date){
					$fileNames[] = $dir . $file;
					@chmod($dir . $file, '0777');
				}
			}
		} 
	}
	
	function send_newsletters()
	{

		$this->load->model('mdl_clients');
		
		$this->load->library('email');
		$config['mailtype'] = 'html';
		$this->email->initialize($config);
		
		$start = date('Y-m-d H:i:s');
		$sending = $notSending = 0;
		$clients = $this->mdl_clients->get_nl("(nl_status IS NULL OR nl_mailgun_status = 'queued')", 1000);
		$from = config_item('account_email_address');

		if(!empty($clients))
		{
			foreach($clients as $k=>$v)
			{
				$result = [];
				if(filter_var($v->nl_to, FILTER_VALIDATE_EMAIL) && filter_var($v->nl_from, FILTER_VALIDATE_EMAIL))
				{

					if($v->nl_mailgun_id == NULL)
					{

						$this->email->to($v->nl_to);
						$this->email->from($v->nl_from, $this->config->item('company_name_short'));
						$this->email->subject($v->nl_subject);

						$this->email->message($v->nl_text);
                        $result = $this->email->send();
					}

					if(is_array($result) && !array_key_exists('error', $result))
					{
						$sending++;
						$emailId = $this->email->getResultId($result);
						$this->mdl_clients->update_nl($v->nl_id, ['nl_status' => 1, 'nl_mailgun_status' => 'sent', 'nl_mailgun_id' => $emailId, 'nl_date' => date('Y-m-d H:i:s')]);
					}
					else
					{
						$notSending++;
						$this->mdl_clients->update_nl($v->nl_id, ['nl_status' => 0, 'nl_date' => date('Y-m-d H:i:s')]);
					}
				}
			}
		}
		
		$end = date('Y-m-d H:i:s');
	}


	function test_newsletters()
	{
	    die;
		$this->load->model('mdl_clients');
		$this->load->model('mdl_estimates');
		$this->load->model('mdl_user');
		$this->load->model('mdl_letter');
		
		$letter = $this->mdl_letter->get_all(['email_template_id' => 22]);
		$estimators = $this->mdl_estimates->get_active_estimators();
		$activeIds = [];
		foreach($estimators as $k=>$v)
			$activeIds[] = $v['id'];
		
		$clients = $this->db->query("SELECT clients.*, estimates.estimate_id, estimate_statuses.est_status_name, estimator.id as est_id, CONCAT(estimator.firstname, ' ', estimator.lastname) AS estimator, clients.client_name, clients_contacts.cc_phone, clients_contacts.cc_name, clients_contacts.cc_email, (SUM(estimates_services.service_price) /*/ IF(COUNT(DISTINCT(client_tasks.task_id)), COUNT(DISTINCT(client_tasks.task_id)), 1)*/) as estimate_price
			FROM (clients)
			LEFT JOIN leads ON clients.client_id = leads.client_id
			LEFT JOIN estimates ON estimates.lead_id = leads.lead_id 
			LEFT JOIN estimates_services ON estimates.estimate_id = estimates_services.estimate_id
			LEFT JOIN services ON estimates_services.service_id = services.service_id
			LEFT JOIN estimate_statuses ON estimates.status = estimate_statuses.est_status_id
			LEFT JOIN users estimator ON estimates.user_id = estimator.id
			JOIN clients_contacts ON clients.client_id = clients_contacts.cc_client_id AND cc_print = 1 AND cc_email_check = 1
			LEFT JOIN workorders ON estimates.estimate_id = workorders.estimate_id
			LEFT JOIN invoices ON estimates.estimate_id = invoices.estimate_id
			WHERE (clients.client_unsubscribe IS NULL OR clients.client_unsubscribe = 0)
			AND clients.client_id NOT IN (13152, 13282, 13370, 13441, 13472, 13516, 13615, 13914, 13947, 13954, 14051, 14105, 14289, 15638, 15747, 17791, 17845, 18483, 18542, 18561, 18622, 19215, 20432, 21721, 22816, 22993, 23069, 23128, 23425, 23748, 25009, 25708, 25820, 26098, 26378, 26677, 26794, 26845, 26850, 27708, 27798, 28609, 28656, 29082, 29410, 29592, 29733, 30009, 30011, 30410, 30413, 30556, 30562, 31380, 31931, 32255, 32311, 32314, 32316, 32334, 32343, 32351, 32371, 32375, 32382, 32821, 33785, 33991, 34386, 34658, 34665, 34929, 24953)
			AND cc_email NOT IN ('stanton@stantonlex.ca', 'heshi.kuhnreich@gmail.com', 'shussain@cordoba.ca', 'shighstead@glendaleproperties.ca', 'wtw@wilrep.com', 'jpn-65@rogers.com', 'chrisyip@sympatico.ca', 'nasser@saniei.com', 'arthurpidgeon@rogers.com', 'p.del@rogers.com', 'stephlyle@hotmail.com', 'ATTILA.ATANER@UTORONTO.CA', 'vitaly.kovaliv777@gmail.com', 'lstach@toronto.ca', 'janaka.perera@metcap.com', 'RPoulat@markham.ca', 'danny.orlando@ttc.ca', 'ekai@rogers.com', 'david.silva@akelius.ca', 'pcc516temp@rogers.com', 'info@horizon-db.com', 'maxm@cfdi.ca', 'natasha@pvcorp.ca', 'mbozzo@queenscorp.com', 'farmtotable@rogers.com', 'AP.Invoices@crosslinxtransit.ca', 'cooper.donmoyer@akelius.ca', 'Igor.Guzar@crosslinxtransit.ca', 'rezaekmali@gmail.com', 'garo@torcomconstruction.com', 'Niall.Prendergast@crosslinxtransit.ca', 'newworldarborist@gmail.com', 'Jonathan.Byrne@crosslinxtransit.ca', 'Mike2057@gmail.com', 'cbranco@iccpropertymanagement.com', 'karen.williams.skelton@outlook.com', 'Ammar.Jawabri@akelius.ca', 'stg1@sympatico.ca', 'Jean.Hanna@crosslinxtransit.ca', 'dtspowerinc@gmail.com', 'wiley@pvcorp.ca', 'sirglenn@gmail.com', 'dave@lifelandscaping.ca', 'lucy@newgen.cc', 'gsmith@beaconutility.com', 'pnaccarato@hady.ca', 'abobo.meliton@rogers.com', 'mr.m.mazurkiewicz@gmail.com', 'Georgespanoudis@yahoo.ca', 'sandy@perlui.ca', 'wevanloon@sympatico.ca', 'bbains_2000@yahoo.com', 'mike@somervillecc.ca', 'silvanasabti@gmail.com', 'cam.johnston@peelregion.ca', 'krawczyk123@hotmail.com', 'adouglas78@gmail.com', 'chris.thompson@rogers.com', 'jeanine1224@gmail.com', 'john@woodbridgepallet.com', 'sylvia.steward43@gmail.com', 'lesleykvaughan@gmail.com', 'tony.kramreither@ca.ey.com', 'zbajbutovic@hotmail.com', 'mark.dsylva@gmail.com', 'hlaird@isca.ca', 'Patrick.jain@metcap.com', 'alisonfound@hotmail.com', 'keaston@bronteconstruction.ca', 'dg@thefifth.com', 'carl.tribe@me.com', 'YL@treedoctors.ca')
			GROUP BY clients_contacts.cc_email
			ORDER BY clients.client_id ASC")->result();
		
		$count = 0; 
		
		if(!empty($clients))
		{
			
			$subject = 'Holliday Greetings from Tree Doctors';
			$text = $letter[0]['email_template_text'];
			$estimator = NULL;
			foreach($clients as $k=>$v)
			{
				$text = $letter[0]['email_template_text'];
				$res = array_search($v->est_id, $activeIds);
				$from = config_item('account_email_address');
				$signature = $this->config->item('default_signature');
				$name = 'Team Tree Doctors';
				if($res !== FALSE)
				{
					if(strpos($estimators[$res]['user_email'], 'treedoctors.ca') !== FALSE)
						$from = $estimators[$res]['user_email'];
					
					$estimator = $estimators[$res]['id'];
					$name = $estimators[$res]['firstname'] . ' ' . $estimators[$res]['lastname'];
					$user = $this->mdl_user->find_by_id($estimator);
					$signature = $user->user_signature; 
					
				}
				$text = str_replace('[ESTIMATOR]', $name, $text);
				
				if(strpos($text, '[SIGNATURE]') !== FALSE)
					$text = str_replace('[SIGNATURE]', $signature, $text);
				else
					$text .= $signature;
				
				$to = $v->cc_email;
				
				if($v->cc_name != NULL && $v->cc_name != '')
					$text = str_replace('[NAME]', $v->cc_name, $text);
				else
					$text = str_replace('[NAME]', $v->client_name, $text);

                $brand_id = get_brand_id($v, $v);

				$text = str_replace(
					['[COMPANY_NAME]', '[COMPANY_EMAIL]', '[COMPANY_PHONE]', '[COMPANY_ADDRESS]', '[COMPANY_BILLING_NAME]', '[COMPANY_WEBSITE]'], 
					[
                        brand_name($brand_id),
                        brand_email($brand_id),
                        brand_phone($brand_id),
                        brand_address($brand_id, $this->config->item('office_address') . ', ' . $this->config->item('office_city') . ', ' . $this->config->item('office_zip')),
                        brand_name($brand_id, true),
                        brand_site($brand_id)
					],
					$text
				);
				
				$text = str_replace('[UNSUBSCRIBE]', '<p style="text-align:left; font-size: 10px; color: rgb(71, 74, 93);"> If you no longer wish to receive these emails you may ' . 
				'<a style="color: rgb(71, 74, 93);" href="http://unsubscribe.treedoctors.ca/unsubscribe/unsubscribeAll/' . md5($v->client_id) . '">unsubscribe</a> at any time.</p>', $text);
				$text = htmlspecialchars_decode($text);
				$data[$k]['nl_estimator'] = $estimator;
				$data[$k]['nl_client'] = intval($v->client_id);
				$data[$k]['nl_subject'] = $subject;
				$data[$k]['nl_from'] = $from;
				$data[$k]['nl_to'] = $to;
				$data[$k]['nl_mailgun_status'] = 'in_progress';
				$data[$k]['nl_text'] = $text;
				
				$count++;
				
			}
			
			if(!empty($data))
				$this->mdl_clients->insert_batch_nl($data);
			echo $count;
		}
		 
	}
	
	function get_test_newsletter()
	{
		ini_set('memory_limit', '-1');
		$this->load->model('mdl_clients');
		$this->load->library('email');
		$letters = $this->mdl_clients->get_nl("(nl_mailgun_status IS NULL OR nl_mailgun_status = 'not_opened' OR nl_mailgun_status = 'accepted' OR nl_mailgun_status = 'in_progress') AND nl_date >= '". date('Y-m-01') ."' AND nl_date <= '". date('Y-m-t') ."'");
		 
		if(!empty($letters))
		{
			
			foreach($letters as $key=>$row)
			{
				$data = [];
				$id = str_replace(['<', '>'], '', $row->nl_mailgun_id);
				
				$msg = $this->email->get_mailgun($id); 
				if($msg && isset($msg->http_response_body) && isset($msg->http_response_body->items) && !empty($msg->http_response_body->items) && isset($msg->http_response_body->items[0]) && isset($msg->http_response_body->items[0]->event))
				{
					 
					if($msg->http_response_body->items[0]->event == 'clicked')
					{
						if(strpos($msg->http_response_body->items[0]->url, 'unsubscribeAll') !== FALSE)
							$data['nl_mailgun_status'] = 'unsubscribed';
						else
							$data['nl_mailgun_status'] = $msg->http_response_body->items[0]->event;
					}
					elseif($msg->http_response_body->items[0]->event == 'delivered')
						$data['nl_mailgun_status'] = 'not_opened';
					elseif($msg->http_response_body->items[0]->event == 'complained')
					{
						$data['nl_mailgun_status'] = $msg->http_response_body->items[0]->event;
						$this->mdl_clients->update_client(['client_unsubscribe' => 1], ['client_id' => $row->nl_client]);
					}
					else
						$data['nl_mailgun_status'] = $msg->http_response_body->items[0]->event;
					
					
					$data['nl_date'] = date('Y-m-d H:i:s', $msg->http_response_body->items[0]->timestamp);
					
					$this->mdl_clients->update_nl($row->nl_id, $data);
				}
			}
		}
	}
	 
	function delete_twilio_recordings() {
		$this->load->model('mdl_calls');


        $this->db->like('call_voice', 'https://api.twilio.com/', 'after');
        $obj = $this->db->get('clients_calls');

        $twilio = new Client($this->accountSid, $this->authToken);

        $recs = $rws = 0;
        if($obj) {
            $calls = $obj->result();

            foreach ($calls as $call) {

                $headers = get_headers($call->call_voice . '.mp3');
                $code = substr($headers[0], 9, 3);

                if($code != "200") {
                    $this->mdl_calls->update($call->call_id, ['call_voice' => NULL]);
                    continue;
                } else {
                    $rawData = file_get_contents($call->call_voice . '.mp3');
                }

                $segments = explode('/', $call->call_voice);
                $path = 'uploads/recordings/' . date('Y-m', strtotime($call->call_date)) . '/';
                $fileName = $path . $segments[count($segments) - 1] . '.mp3';//countOk
                if(is_bucket_file($fileName))
                    continue;

                bucket_write_file($fileName, $rawData, ['ContentType' => 'audio/mpeg']);
                $recs++;
                $this->mdl_calls->update($call->call_id, ['call_voice' => base_url($fileName) . '?']);
                echo $segments[count($segments) - 1] . "\n";//countOk
                $twilio->recordings($segments[count($segments) - 1])->delete();//countOk
            }
        }

        return TRUE;



        $twilio = new Client($this->accountSid, $this->authToken);
		$date = date('Y-m-d', time() - 60 * 60 * 24);
		$recordings = $twilio->recordings->read(["dateCreatedAfter" => new \DateTime($date)]);
		
		$recs = $rws = 0;
		 
		foreach ($recordings as $record) {
			
			if(!$this->mdl_calls->update_by(['call_twilio_sid' => $record->callSid]))
				continue;
				
			$url = 'https://api.twilio.com' . str_replace('.json', '.mp3', $record->uri);
			$rawData = file_get_contents($url);
			$path = 'uploads/recordings/' . $record->dateCreated->format('Y-m') . '/';
			$fileName = $path . $record->sid . '.mp3';
			if(is_bucket_file($fileName))
				continue;

			bucket_write_file($fileName, $rawData);
			$recs++;
			$this->mdl_calls->update_by(['call_twilio_sid' => $record->callSid], ['call_voice' => base_url($fileName) . '?']);
			
		}
	} 
	 
	function send_logout_stats()
	{
		$this->load->model('mdl_employees');
		$this->load->model('mdl_emp_login');
		$this->load->model('mdl_worked');
		
		$date = date('Y-m-d', time() - 60 * 60 * 24);
		
		$email_data['users'] = $this->mdl_worked->with('mdl_emp_login')->get_workeds(array('worked_date' =>  $date));
		 
		$this->load->library('email');
		$config['mailtype'] = 'html';
		$this->email->initialize($config);
		$this->email->to('dmitriy.vashchenko@gmail.com');
		$this->email->from(config_item('account_email_address'), 'Tree Doctors');
		$this->email->subject("Daily Payroll");
		$this->email->message($this->load->view('cron/letter', $email_data, TRUE));
		$this->email->send();
	}
	
	function get_equipment_worked_days()
	{
		$data['equipments'] = $this->db->query("SELECT  item_code, COUNT(DISTINCT(equipment_team_id)) as days FROM `schedule_teams_equipment` JOIN equipment_items ON equipment_id = item_id JOIN schedule_teams ON equipment_team_id = team_id WHERE team_date >= UNIX_TIMESTAMP('2018-01-01') AND team_date <= UNIX_TIMESTAMP('2018-12-31') GROUP BY equipment_id ORDER BY days DESC")->result();
		$data['crews'] = $this->db->query("SELECT DATE_FORMAT(FROM_UNIXTIME(team_date), '%Y-%m-%d') as dates, team_id, COUNT(DISTINCT(user_id)) as users, ROUND(team_man_hours / COUNT(DISTINCT(user_id)), 2) as avg_hours_day, team_man_hours FROM `schedule_teams` JOIN schedule_teams_members ON team_id = employee_team_id WHERE WEEKDAY(DATE_FORMAT(FROM_UNIXTIME(team_date), '%Y-%m-%d')) != 6 AND team_date >= UNIX_TIMESTAMP('2018-01-01') AND team_date <= UNIX_TIMESTAMP('2018-12-31') GROUP BY team_id")->result();
		
		$this->load->model('mdl_tracker');
		$this->load->model('mdl_equipments');
		$res = $this->mdl_tracker->get_many_by(['eq_td_date >=' => '2018-01-01', 'eq_td_date <=' => '2018-12-31']);
		$equipments = [];
		 
		foreach($res as $k=>$v)
		{ 
			$obj = json_decode($v->eq_td_data);
			
			if(!isset($equipments[$v->eq_td_code]))
				$equipments[$v->eq_td_code]['time'] = 0;
			 
			if(isset($obj->parkData))
			{
				$reversePark = array_reverse($obj->parkData->data);
				
				$result['route_travel_time'] = 0;
				foreach($reversePark as $key=>$val)
				{
					if(isset($reversePark[$key+1]))
						$result['route_travel_time'] += strtotime($val[1]) - strtotime($reversePark[$key+1][2]);
				}
				$equipments[$v->eq_td_code]['time'] += $result['route_travel_time'];
			}
		}
		foreach($equipments as $k=>$v)
		{
			$info[$k]['time'] = $v['time']; //sprintf('%02d:%02d', (int) round($v['time'] / 3600, 2), fmod(round($v['time'] / 3600, 2), 1) * 60);
			$info[$k]['distance'] = round($this->mdl_equipments->get_summ_gps_distance(['egtd_date >=' => '2018-01-01', 'egtd_date <=' => '2018-12-31', 'item_code' => $k], 'sum')->count, 2);
		}
		ksort($info); 
		$data['data'] = $info;
		
		$this->load->view('table', $data); 
	}
	function get_equipment_hrs()
	{
		show_404();
		$this->load->model('mdl_tracker');
		$this->load->model('mdl_equipments');
		$res = $this->mdl_tracker->get_many_by(['eq_td_date >=' => '2018-01-01', 'eq_td_date <=' => '2018-12-31']);
		$equipments = [];
		 
		foreach($res as $k=>$v)
		{ 
			$obj = json_decode($v->eq_td_data);
			
			if(!isset($equipments[$v->eq_td_code]))
				$equipments[$v->eq_td_code]['time'] = 0;
			 
			if(isset($obj->parkData))
			{
				$reversePark = array_reverse($obj->parkData->data);
				$equipments[$v->eq_td_code]['name'] = '';
				$result['route_travel_time'] = 0;
				foreach($reversePark as $key=>$val)
				{ 
					if(isset($reversePark[$key+1]))
						$result['route_travel_time'] += strtotime($val[1]) - strtotime($reversePark[$key+1][2]);
				}
				if(isset($reversePark[count($reversePark) - 1][0]))//countOk
					$equipments[$v->eq_td_code]['name'] = $reversePark[count($reversePark) - 1][0];//countOk
				$equipments[$v->eq_td_code]['time'] += $result['route_travel_time'];
			}
		}
		foreach($equipments as $k=>$v)
		{
			$info[$k]['time'] = $v['time']; //sprintf('%02d:%02d', (int) round($v['time'] / 3600, 2), fmod(round($v['time'] / 3600, 2), 1) * 60);
			$info[$k]['name'] = $v['name']; //sprintf('%02d:%02d', (int) round($v['time'] / 3600, 2), fmod(round($v['time'] / 3600, 2), 1) * 60);
			$info[$k]['distance'] = round($this->mdl_equipments->get_summ_gps_distance(['egtd_date >=' => '2018-01-01', 'egtd_date <=' => '2018-12-31', 'item_code' => $k], 'sum')->count, 2);
			$info[$k]['days'] = $this->db->query("SELECT COUNT(DISTINCT(egtd_date)) FROM (`equipment_items`) LEFT JOIN `equipment_gps_tracker_distance` ON `equipment_gps_tracker_distance`.`egtd_item_id` = `equipment_items`.`item_id` AND equipment_gps_tracker_distance.egtd_date >= equipment_items.item_gps_start_date WHERE `egtd_date` >= '2018-01-01' AND `egtd_date` <= '2018-12-31'  AND item_code = '". $k . "' GROUP BY egtd_date ORDER BY egtd_date")->num_rows();
		} //echo '<pre>'; var_dump($count->num_rows()); die;
		ksort($info); 
		$data['data'] = $info;
		$this->load->view('table_eq_distance', $data); 
	}
	
	function parse_gps_report()
	{
		
		$eqs = $this->db->query("SELECT * FROM `equipment_items` WHERE item_code LIKE '%VHC%'")->result_array();
		
		foreach($eqs as $key=>$val)
		{
			//echo '<pre>'; var_dump($val); die;
			$file = 'uploads/gps_reports/' . $val['item_code'] . '.xls';
			if(file_exists($file))
				{
				$result = get_vaughan_stumps_data($file);
				unset($result[0]);
				$date = date('Y-m-d');
				$data = [];
				$data['distance'] = $data['time'] = 0;
				$dates = [];
				
				foreach($result as $k=>$v)
				{
					
					if(date('w', strtotime($v[0][1])) != '0')
					{
						if($date == date('Y-m-d', strtotime($v[0][1])))
						{
							 
							$dates[date('Y-m-d', strtotime($v[0][1]))]['count']++; 
							$dates[date('Y-m-d', strtotime($v[0][1]))]['distance'] += $v[0][6]; 
							if($v[0][3] != '')
								$dates[date('Y-m-d', strtotime($v[0][1]))]['time'] = $dates[date('Y-m-d', strtotime($v[0][1]))]['time'] + (strtotime(str_replace('00 day ', '', $v[0][3])) - strtotime('TODAY'));
						}
						else
						{
							 
							$dates[date('Y-m-d', strtotime($v[0][1]))]['distance'] 	= $v[0][6];
							$dates[date('Y-m-d', strtotime($v[0][1]))]['count'] = 1; 
							if($v[0][3] != '')
								$dates[date('Y-m-d', strtotime($v[0][1]))]['time'] = strtotime(str_replace('00 day ', '', $v[0][3])) - strtotime('TODAY');
							$date = date('Y-m-d', strtotime($v[0][1]));
						}
						
					 
					}
				} 
				$data['days'] = count($dates);//countOk
				$data['name'] = $val['item_name'];
				foreach($dates as $ke=>$va)
				{
					
					if($va['distance'] >= 5 && isset($va['time']))
					{
						$data['distance'] += round($va['distance'], 2);
						$data['time'] = $data['time'] + $va['time'];
					}
					
				}
				 
				$info['data'][$val['item_code']] = $data;
				$info['data'][$val['item_code']]['schedule_days'] = 0; 
				
				$schDays = $this->db->query("SELECT COUNT(DISTINCT(equipment_team_id)) as schedule_days FROM `schedule_teams_equipment` JOIN equipment_items ON equipment_id = item_id JOIN schedule_teams ON equipment_team_id = team_id WHERE team_date >= UNIX_TIMESTAMP('2018-01-01') AND team_date <= UNIX_TIMESTAMP('2018-12-31') AND item_code = '". $val['item_code'] ."' GROUP BY equipment_id ORDER BY schedule_days DESC")->row_array();
				if($schDays && !empty($schDays))
					$info['data'][$val['item_code']]['schedule_days'] = $schDays['schedule_days'];
				
			}
		}
		ksort($info['data']);
		$this->load->view('table_eq_distance', $info); 
	}
	
	function patch_eq_tr_data()
	{
		 die;
		$data = $this->db->query('SELECT * FROM equipment_tracker_data_test')->result();
 
		$this->load->model('mdl_tracker');
		foreach($data as $k=>$v)
		{
			$this->mdl_tracker->insert(array('eq_td_code' => $v->eq_td_code, 'eq_td_date' => $v->eq_td_date, 'eq_td_data' => $v->eq_td_data));
		}
	}
	function patch_eq_tr_dis()
	{
		 die;
		$data = $this->db->query('SELECT * FROM equipment_gps_tracker_distance_test')->result();
		 
		$this->load->model('mdl_equipments');
		foreach($data as $k=>$v)
		{
			$this->mdl_equipments->insert_gps_distance(array('egtd_item_id' => $v->egtd_item_id, 'egtd_date' => $v->egtd_date, 'egtd_counter' => $v->egtd_counter));
		}
	}
	
	function get_neighborhoods()
	{
		$csv = array_map('str_getcsv', file('uploads/neighborhood.csv'));
		array_walk($csv, function(&$a) use ($csv) {
			$a = array_combine($csv[0], $a);
		});
		array_shift($csv);
		foreach($csv as $k=>$v)
		{
			$data = explode('},  ', str_replace(['-',',', ', 0.0'], ['-',', ', '}, '], $v['geometry'])); 
			$result = [];
			foreach($data as $k=>$v)
			{
				$explode = explode(', ', $v);
				
				$result[$k]['lng'] = str_replace(['}, ', '<Polygon><outerBoundaryIs><LinearRing><coordinates>'], [''], $explode[0]);
				$result[$k]['lat'] = str_replace(['},', '}'], '', $explode[1]);
				
			}
			$coords[]['coords'] = json_encode($result);
		}
		 
		$this->db->insert_batch('neighborhoods', $coords);
		echo '<pre>'; var_dump($coords); die;
		$save = $data;
		$entry = str_replace(['{lng: ', ', lat: '], ['', ' '], $data);
		
		$done = explode('}, ', $entry);
		
	}
	
	function patch_estimates_neighborhoods()
	{
		 
		ini_set('memory_limit', '-1');
		set_time_limit ( '0');

		$this->load->model('mdl_leads');
		$this->load->model('mdl_estimates');
		$this->load->library('pointLocation');
		
		$getPolygons = $this->db->query('SELECT * FROM neighborhoods')->result_array();
		
		foreach($getPolygons as $key=>$val)
		{
			foreach(json_decode($val['coords']) as $k=>$v)
				$polygons[$val['id']][] = $v->lng . ' ' . $v->lat;
		}
		 
		$leads = $this->mdl_leads->get_leads(['lead_neighborhood ' => NULL, 'latitude !=' => 0, 'longitude !=' => 0, 'lead_address !=' => ''], FALSE)->result();
		$i = 0;
		foreach($leads as $key=>$val)
		{
			if(!$val->longitude || !$val->latitude)
				continue;
			$point = $val->longitude . ' ' . $val->latitude;
			foreach($polygons as $key => $polygon) {
				if($this->pointlocation->pointInPolygon($point, $polygon, TRUE) == 'inside')
				{
					$i++;
					$this->mdl_leads->update_leads(['lead_neighborhood' => $key], ['lead_id' => $val->lead_id]);
					break;
				}
			}
		}
		echo '<pre>' . $i; die();
	}

	function emails()
	{
		$this->load->view('email_form');
	}
	
	function sendEmail()
	{
		include('./application/libraries/SMTPClient.php');
		$files = [];
		if(isset($_FILES) && isset($_FILES['image']) && !empty($_FILES['image']))
			$files = $_FILES['image'];
		
		$smtpClient = new SMTPClient();
		$smtpClient->setServer("smtp.mailgun.org", "465", 'ssl');
		$smtpClient->setSender("info@arbostar.com", "info@arbostar.com", "a8234fd8d84e2cff6f8e37a1f4fe05a3-2ae2c6f3-27879197");
		$smtpClient->setMail($this->input->post('email'), $this->input->post('name'), htmlspecialchars_decode($this->input->post('message')), "text/html");
		
		if(!empty($files) && isset($files['name']))
		{  
			for($i=0; $i<count($files['name']); $i++)//countOk
			{
				if(isset($files['tmp_name'][$i]) && $files['tmp_name'][$i])
					$smtpClient->attachFile($files['name'][$i], file_get_contents($files['tmp_name'][$i]));
 
			}
		}
		$smtpClient->sendMail();
	}

	function client_cc_patch()
	{
		$this->load->model('mdl_clients');
		$clients = $this->db->having('length(client_cc_number)<160')->where('client_cc_number IS NOT NULL AND client_cc_exp_month IS NOT NULL')->get('clients')->result();
		foreach($clients as $k=>$v)
		{
            $data['client_cc_number'] = [];
			$client_id = $v->client_id;
			$cc_number = decrypt_data(md5($this->config->item('encryption_key') . $v->client_id), $v->client_cc_number);
			$update['client_cc_type'] = GetCardType($cc_number);
			$update['client_cc_exp_month'] = $v->client_cc_exp_month;
			$update['client_cc_exp_year'] = $v->client_cc_exp_year;
			$update['client_cc_number'] = $v->client_cc_number;
			$update['client_cc_cvv'] = $v->client_cc_cvv;
			$update['client_cc_name'] = $v->client_cc_name;
			$data['client_cc_number'][] = $update;
			$this->mdl_clients->update_client(['client_cc_number' => json_encode($data['client_cc_number'])], array('client_id' => $client_id));
		}
	}
	function check_user_docs()
	{
		$this->load->model('mdl_user');
		$this->load->model('mdl_letter');
		$this->load->model('mdl_sms');
		
		$this->load->library('email');
		$this->email->clear(TRUE);
		$config['mailtype'] = 'html';
		$this->email->initialize($config);
		
		$where['active_status'] = 'yes';
		$where['system_user'] = 0;
		$where['us_notification'] = 1;
		$where['us_exp'] = date('Y-m-d', strtotime("+1 month", strtotime(date('Y-m-d'))));
		
		$emailTpl = $this->mdl_letter->find_by_fields(['email_news_templates' => 2]);
		$smsTpl = $this->mdl_sms->get_by(['user' => 1]); 
		$monthes_users = $this->mdl_user->get_payroll_user($where);
		$users = [];
		if($monthes_users)
		{
			$userData = $monthes_users->result_array();
			
			foreach($userData as $k=>$v)
			{
				$users[$v['id']]['name'] = $v['firstname'] . ' ' . $v['lastname'];
				$users[$v['id']]['email'] = $v['user_email'];
				$users[$v['id']]['phone'] = $v['emp_phone'];
				$users[$v['id']]['docs'][] = $v['us_name'];
				$users[$v['id']]['date'][] = $v['us_exp'];
			}
		}
		$where['us_exp'] = date('Y-m-d', strtotime("+1 week", strtotime(date('Y-m-d'))));
		
		$monthes_users = $this->mdl_user->get_payroll_user($where); 
		if($monthes_users)
		{
			$userData = $monthes_users->result_array();
			foreach($userData as $k=>$v)
			{
				$users[$v['id']]['name'] = $v['firstname'] . ' ' . $v['lastname'];
				$users[$v['id']]['email'] = $v['user_email'];
				$users[$v['id']]['phone'] = $v['emp_phone'];
				$users[$v['id']]['docs'][] = $v['us_name'];
				$users[$v['id']]['date'][] = $v['us_exp'];
			}
		}
		
		$officeEmail = 'Auto email. This users have expired documents: <br> [USERS]';
		$allUsers = ''; 
		foreach($users as $k=>$v)
		{
			
			if(!empty($v['docs']))
				$docs = '<ul>';
			$smsDocs = '';
			$smsText = $smsTpl->sms_text;
			$text = $emailTpl->email_template_text;
			$subject = $emailTpl->email_template_title;
			$number = $v['phone'];
			$to = $v['email'];
			$from = $this->config->item('account_email_address');
			foreach($v['docs'] as $jk=>$jv)
			{
				$smsDocs .= $jk + 1 . ') ' . $jv . ' ('. $v['date'][$jk] .')' . "\n";
				$docs .= '<li>' . $jv . ' ('. $v['date'][$jk] .')</li>';
			}
			if(!empty($v['docs']))
			{
				$docs .= '</ul>';
			}
			$text = str_replace('[NAME]', $v['name'], $text);
			$text = rtrim(str_replace('[DOCUMENTS]', $docs, $text));
			$smsText = str_replace('[NAME]', $v['name'], $smsText);
			$smsText = str_replace('[DOCUMENTS]', $smsDocs, $smsText);
            $allUsers .= $v['name'] . ': ' . $docs;
            if (isset($users[$k + 1])) {
                $allUsers .= '<br>';
            }

            $this->email->to($to);
            $this->email->from($from, $this->config->item('company_name_short'));
            $this->email->subject($subject);

            $this->email->message($text);
            $this->email->send();

            $this->load->driver('messages');
            $this->messages->send($v['phone'], $smsText);
		}
		if(!empty($users))
		{
			$officeEmail = str_replace('[USERS]', $allUsers, $officeEmail);
			
			$this->email->to($this->config->item('account_email_address'));
			$this->email->from($from, $this->config->item('company_name_short'));
			$this->email->subject('Users with expired documents');
			
			$this->email->message($officeEmail);
			$this->email->send();
		}
		
	}

	function clearToday() {
	    $this->db->query('UPDATE schedule SET event_state = 0, event_report = NULL WHERE event_start > ' . strtotime(date('Y-m-d')) . ' AND event_start < ' . (strtotime(date('Y-m-d')) + 86400));
	    $this->db->query('DELETE e FROM events e JOIN schedule s ON s.id=e.ev_event_id WHERE event_start > ' . strtotime(date('Y-m-d')) . ' AND event_start < ' . (strtotime(date('Y-m-d')) + 86400));
	    echo "OK";
    }

    function test3()
    {
        $this->load->model('mdl_followups');
        $this->followUpEmailSender($this->db->select('*')->from('followups')->join('followup_settings', 'followups.fu_fs_id = followup_settings.fs_id')->where('fu_id', '25207')->get()->row());
        die;
        $this->load->model('mdl_leads');
        $this->load->model('mdl_clients');
        $this->load->model('mdl_leads_status');
        $raw = '{"fields":{"short_text":{"title":"Enter your full name*","value":"test by dv form2","type":"text"},"contactForm_phoneNumber":{"title":"Enter your phone number*","value":"984984984948","type":"phone"},"contactForm_email":{"title":"Enter your e-mail*","value":"dmitriy.vashchenko@gmail.com","type":"email"},"3f809f56-5ab0-4880-a4aa-f555eb891de3":{"title":"Type your message","value":"ascascas","type":"textArea"}}}';
        echo '<pre>'; var_dump(json_decode($raw)); die;
        if($raw) {
            $jsonArray = json_decode($raw,true);
            if($jsonArray && json_last_error() === JSON_ERROR_NONE) {
                $_POST = array_merge($_POST, $jsonArray);
            }
        }
        $post = $_POST;
        $clientContact = $clientData = $leadData = [];
        $clientContact['cc_print'] = 1;
        $clientData['client_type'] = 1;
        $clientData['client_date_created'] = date('Y-m-d');
        $defaultStatus = $this->mdl_leads_status->get_by(['lead_status_default' => 1]);
        $leadData['lead_status_id'] = $defaultStatus->lead_status_id;
        $leadData['lead_date_created'] = date('Y-m-d H:i:s');
        $client_id = null;
        foreach($post['fields'] as $key=>$val){
            if($key == 'contactForm_name'){
                $clientData['client_name'] =  $clientContact['cc_name'] = $val['value'];
                $leadData['client_id'] = $clientContact['cc_client_id'] = $this->mdl_clients->add_new_client_with_data($clientData);
            }
            elseif($key == 'contactForm_phoneNumber') {
                $clientContact['cc_phone'] = $val['value'];
            }
            elseif($key == 'contactForm_email') {
                $clientContact['cc_email'] = $val['value'];
            }
            else {
                $leadData['lead_body'] = $val['value'];
            }
        }
        $this->mdl_clients->add_client_contact($clientContact);
        $lead_id = $this->mdl_leads->insert_leads($leadData);
        if ($lead_id) {
            $lead_no = str_pad($lead_id, 5, '0', STR_PAD_LEFT);
            $lead_no = $lead_no . "-L";
            $update_data = array("lead_no" => $lead_no);
            $wdata = array("lead_id" => $lead_id);
            $lead_no_updated = $this->mdl_leads->update_leads($update_data, $wdata);
        }



    }

}
