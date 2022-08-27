<?php
if (!defined('BASEPATH'))
	exit('No direct script access allowed');

use Twilio\Rest\Client;
use Twilio\Rest\Api\V2010\Account\Recording\AddOnResult;
use Twilio\Jwt\TaskRouter\WorkspaceCapability;
use Twilio\Jwt\ClientToken;
use Twilio\Rest\Api\V2010\Account\Recording\AddOnResult\PayloadContext;
use Twilio\Version;
use Twilio\Domain;
use Twilio\VersionInfo;

class Client_calls extends MX_Controller
{
	function __construct()
	{
		parent::__construct();
		$this->load->model('mdl_calls');
		$this->load->model('mdl_calls_hold');
		$this->load->model('mdl_voices');
		$this->load->model('mdl_sms');
		$this->load->model('mdl_calls_reservations');
		$this->load->model('mdl_clients');
		$this->load->model('mdl_user');
	}

	function test1($param=false)
	{
		$client = new Client($this->config->item('accountSid'), $this->config->item('authToken'));
		/*
		$call = $client->calls($param)->fetch();
		echo "<pre>";var_dump($call);die;*/
		$task = $client->taskrouter->v1->workspaces("WSd5ddf64bb22aa165abac6c6434764dec")
                               ->tasks
                                ->read();
        echo "<pre>";var_dump($task);die;
	}

	public function index()
	{
		$caller = urldecode($this->input->get('Caller'));
		if(!$this->input->get('Caller'))
			$caller = $this->input->post('Caller', FALSE, FALSE);

		if(strpos($caller, 'client:') !== FALSE)
		{
			if($this->input->post('agent')) {
				$this->load->view_xml($this->config->item('company_dir') . '/forward_to_agent', [
					'contact_uri' => $this->input->post('agent')
				]);
			} else {
				$this->dial();
			}
		} else {
			$this->load->view_xml($this->config->item('company_dir') . '/call');
		}
	}

	public function dial()
	{
		$phoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();

		if($this->input->post('callerId'))
			$caller = $this->input->post('callerId');
		else 
			$caller = urldecode($this->input->get('Caller'));
			
		if(!$caller && !$this->input->get('Caller'))
			$caller = $this->input->post('Caller', FALSE, FALSE);

		if(!$this->input->post('callerId') && strpos($caller, 'client:') !== FALSE)
		{
			$caller = $this->config->item('myNumber');
			$fromNumber = $caller;
			$toNumber = urldecode($this->input->get('PhoneNumber'));
			$callSid = urldecode($this->input->get('CallSid'));
		}

		$number = $this->input->post('PhoneNumber', FALSE, $this->input->post('To', FALSE, ''));

		if($this->input->get('PhoneNumber'))
			$number = $this->input->get('PhoneNumber');

		if($number) {
			$swissNumberProto = $phoneUtil->parse($number, 'CA');
		
			$isValidNum = $phoneUtil->isValidNumber($swissNumberProto);
			
			$swissCallerProto = $phoneUtil->parse($caller, 'CA');
			$isValidCaller = $phoneUtil->isValidNumber($swissCallerProto);

			$number = $phoneUtil->format($swissNumberProto, \libphonenumber\PhoneNumberFormat::E164);
		}
		/*if($isValidNum && $isValidCaller)
		{*/
			$data = ['caller' => $caller, 'phone_number' => $number];
			$this->load->view_xml($this->config->item('company_dir') . '/dial', $data);
		/*}*/
	}

	function assignment()
	{
		$TaskAttributes = json_decode($this->input->post('TaskAttributes'));
		if ($TaskAttributes == false) {
			var_dump($this->input->post());exit;
		}
		$WorkerAttributes = json_decode($this->input->post('WorkerAttributes'));

		$call_note = [
			'call_type' => 'taskrouter',
			'call_from' => $TaskAttributes->from,
			'call_to' => $TaskAttributes->to,
			'call_client_id' => $TaskAttributes->clientId,
			'call_user_id' => NULL,//$userId,
			'call_route' => 1,
			'call_date' => date('Y-m-d H:i:s'),
			'call_twilio_sid' => $TaskAttributes->call_sid,
			'call_complete' => '0',
			'call_disabled' => 1,
			'call_workspace_sid' => $this->input->post('WorkspaceSid'),
		];

		$this->mdl_calls->insert($call_note);

		$assignment_instruction = [
			'instruction' => 'dequeue',
			'to' => 'client:' . $WorkerAttributes->contact_uri,
			'from' => $TaskAttributes->from,
			'record' => 'record-from-answer',
			'status_callback_url' => base_url('client_calls/recording/'),
			'post_work_activity_sid' => $this->config->item('onlineActivitySid')
		];



		header('Content-Type: application/json');
		die(json_encode($assignment_instruction));
	}

	function recordingTaskrouter($parentCallSid = NULL) {

		$fromNumber = urldecode($this->input->post('From'));
		$workerUri = str_replace('client:', '', urldecode($this->input->post('To')));
		$toNumber = $this->config->item('myNumber');
		$callRoute = 1;
		$clientNumber = $fromNumber;
		$userTwilioSid = 0;
		$userId = 0;
		$clientId = 0;

		foreach($workers as $worker){
			$attrs = json_decode($worker->attributes);
			if(isset($attrs->contact_uri) && $attrs->contact_uri == $WorkerAttributes->contact_uri)
			{
				$userTwilioSid = $worker->sid;
				break;
			}
		}

		if($userTwilioSid) {
			$user = $this->mdl_user->find_by_fields(['twilio_worker_id' => $userTwilioSid]);
			$userId = $user ? $user->id : NULL;
		}

		$client_data = $this->mdl_clients->find_by_phone(trim($clientNumber));
		
		if($client_data)
			$clientId = $client_data['client_id'];

		$call_note = [
			'call_type' => 'taskrouter',
			'call_from' => $fromNumber,
			'call_to' => $toNumber,
			'call_client_id' => $clientId,
			'call_user_id' => $userId,
			'call_route' => 1,
			'call_date' => date('Y-m-d H:i:s'),
			'call_twilio_sid' => urldecode($this->input->post('CallSid')),
			'call_twilio_parent_call_sid' => $parentCallSid,
			'call_complete' => '1',
			'call_workspace_sid' => $this->config->item('workspaceSid'),
		];

		$this->mdl_calls->insert($call_note);
	}

	function assignment_callback(){
		ob_start();
		var_dump(date('H:i:s'), $this->input->post());
		$str = ob_get_contents();
		ob_end_clean();
		$fp = fopen('uploads/test3', 'a');
		fwrite($fp, $str . "\n\n");
		fclose($fp);
	}

	function timeout_update_workflow()
	{
		sleep(10);
		$this->_update_workflow($this->input->post('level'), $this->input->post('workflow_sid'));
	}

	function timeout_delete_workflow()
	{
		sleep(10);
		$client->taskrouter->workspaces($this->config->item('workspaceSid'))->tasks($this->input->post('task_sid'))->delete();
		$this->_delete_workflow($this->input->post('workflow_sid'));
	}

	function play()
	{
		$this->load->view_xml($this->config->item('company_dir') . '/play');
	}

	function recording($user_id = NULL/*, $callSid = NULL*/)
	{
		//if($callSid)
			//$this->mdl_calls->delete_by(['call_twilio_sid' => $callSid]); 
		$client = new Client($this->config->item('accountSid'), $this->config->item('authToken'));
		$callSid = urldecode($this->input->post('CallSid'));
		$dialCallSid = urldecode($this->input->post('DialCallSid'));
		$clientId = NULL;
		$userTwilioSid = NULL;
		$userId = NULL;
		$recording = $this->input->post('RecordingUrl') ? $this->input->post('RecordingUrl') : NULL;

		/********SAVE RECORDING TO SERVER******/
		/*$recording = NULL;
		$url = $this->input->post('RecordingUrl') ? $this->input->post('RecordingUrl') : NULL;
		if($url) {
			$rawData = file_get_contents($url);
			$path = 'uploads/recordings/' . date('Y-m') . '/';
			$fileName = $path . basename($url) . '.mp3';
				
			if(!is_dir($path)) {
				mkdir($path);
				chmod($path, 0777);
			}
			file_put_contents('./' . $fileName, $rawData, 0777);
			$recording = base_url($fileName)  . '?';
		}*/
		//$client->recordings(basename($url))->delete();
		/********SAVE RECORDING TO SERVER******/
		
		$callRows = $this->mdl_calls->get_calls(['call_twilio_sid' => $callSid]);

		if($callRows && !empty($callRows)) {
			$this->mdl_calls->update($callRows[0]['call_id'], [
				'call_complete' => 1,
				'call_disabled' => 0,
				'call_voice' => $recording ?: $callRows[0]['call_voice']
			]);
			$this->load->view_xml($this->config->item('company_dir') . '/hangup');
			return false;
		}

		$direction = $this->input->post('Direction');
		$call = $client->calls($callSid)->fetch();
		$callDuration = $this->input->post('CallDuration', FALSE, 0);
		$callDuration = $callDuration ? $callDuration : $this->input->post('DialCallDuration', FALSE, 0);
		$callDuration = $this->input->post('QueueResult') == 'hangup' ? 0 : $callDuration;
		$callDate = $call->startTime->setTimezone(new DateTimeZone(date_default_timezone_get()))->format("Y-m-d H:i:s");

		if($direction == 'outbound-api')
		{
			$fromNumber = urldecode($this->input->post('From'));
			$workerUri = str_replace('client:', '', urldecode($this->input->post('To')));
			$toNumber = $this->config->item('myNumber');
			$callRoute = 1;
			$clientNumber = $fromNumber;
		}
		elseif($direction == 'inbound')
		{
			$callRoute = 0;
			$fromNumber = $this->config->item('myNumber');
			$toNumber = $this->input->post('To');
			$clientNumber = $toNumber;
			$workerUri = str_replace('client:', '', urldecode($this->input->post('From')));

			if($this->input->post('To') && $this->input->post('DialCallSid') && (strpos($this->input->post('To'), 'client:') !== FALSE || strpos($this->input->post('To'), 'client:') !== FALSE))
			{
				$parentCall = $client->calls($this->input->post('DialCallSid'))->fetch();
				$toNumber = $parentCall->to;
				$fromNumber = $this->input->post('From');
				$clientNumber = $fromNumber;
				$workerUri = str_replace('client:', '', $parentCall->to);
				$callRoute = 1;
				$callDate = $parentCall->startTime->setTimezone(new DateTimeZone(date_default_timezone_get()))->format("Y-m-d H:i:s");
				$callDuration = ($parentCall->duration)?$parentCall->duration:0;
			}

			if(strpos($this->input->post('From'), 'sip:') === FALSE && strpos($this->input->post('From'), 'client:') === FALSE && $this->input->post('DialCallSid'))
			{
				$parentCall = $client->calls($this->input->post('DialCallSid'))->fetch();
				$toNumber = $parentCall->to;
				$fromNumber = $this->input->post('From');
				$clientNumber = $fromNumber;
				$workerUri = str_replace('client:', '', $parentCall->to);
				$callRoute = 1;
				$callDate = $parentCall->startTime->setTimezone(new DateTimeZone(date_default_timezone_get()))->format("Y-m-d H:i:s");
				$callDuration = ($parentCall->duration)?$parentCall->duration:0;
			}

			if(!$toNumber && $this->input->post('DialCallSid'))
			{
				if(ltrim($fromNumber, '+') == $this->config->item('myNumber'))
					$callRoute = 0;

				$parentCall = $client->calls($this->input->post('DialCallSid'))->fetch();
				$toNumber = $parentCall->to;
				$clientNumber = $toNumber;

				if(strpos($fromNumber, 'client:') !== FALSE || strpos($fromNumber, 'sip:') !== FALSE)
					$callRoute = 0;
			}

			if(ltrim($toNumber, '+') == ltrim($fromNumber, '+') || ltrim($fromNumber, '+') == $this->config->item('myNumber'))
			{
				$fromNumber = $this->input->post('From');
				$clientNumber = $fromNumber;
				$callRoute = 1;

				if(strpos($fromNumber, 'client:') !== FALSE || strpos($fromNumber, 'sip:') !== FALSE) {
					$callRoute = 0;
					$clientNumber = $toNumber;
				}

				/*if(ltrim($fromNumber, '+') == $this->myNumber)
					$callRoute = 0;
				$toNumber = str_replace(['sip:', '@treedoctorsoffice.sip.us1.twilio.com'], ['', ''], $toNumber);*/

			}
		}



		/***************SIP***********************/
		if((isset($fromNumber) && $fromNumber == 'sip:treedoctors@treedoctorsoffice.sip.us1.twilio.com') || $this->input->post('From') == '+14162018244') {
			$fromNumber = 'TD Hard Phone';
			$toNumber = str_replace(['sip:', '@treedoctorsoffice.sip.us1.twilio.com'], ['', ''], $toNumber);
			$clientNumber = $toNumber;
		}
		if((isset($toNumber) && $toNumber == 'sip:treedoctors@treedoctorsoffice.sip.us1.twilio.com') || $this->input->post('To') == '+14162018244') {
			$toNumber = 'TD Hard Phone';
			$fromNumber = str_replace(['sip:', '@treedoctorsoffice.sip.us1.twilio.com'], ['', ''], $fromNumber);
			$clientNumber = $fromNumber;
		}
		/***************SIP***********************/

		$client_data = $this->mdl_clients->find_by_phone(trim($clientNumber));
		
		if($client_data)
			$clientId = $client_data['client_id'];

		if(isset($workerUri) && !empty($workerUri)) {
			$workers = $client->taskrouter->workspaces($this->config->item('workspaceSid'))->workers->read();

			foreach($workers as $worker){
				$attrs = json_decode($worker->attributes);
				if(isset($attrs->contact_uri) && $attrs->contact_uri == $workerUri)
				{
					$userTwilioSid = $worker->sid;
					break;
				}
			}

			$user = NULL;

			if($userTwilioSid){
				$user = $this->mdl_user->find_by_fields(['twilio_worker_id' => $userTwilioSid]);
				$userId = $user ? $user->id : NULL;
			}
		}
		
		$callNewVoicemail = !$callDuration && $callRoute && $recording ? 1 : 0;
		if(!$callNewVoicemail) {
			$callNewVoicemail = $callRoute && !$recording ? 1 : 0;
			$callDuration = $callNewVoicemail ? 0 : $callDuration;
		}

		$callRowsExist = false;

		if($this->input->get('TaskCallSid')){
			$callRowsExist = $this->mdl_calls->get_calls(['call_twilio_sid' => $this->input->get('TaskCallSid')]);
		}

		if($callRowsExist && !empty($callRowsExist)) {
			$this->mdl_calls->update($callRowsExist[0]['call_id'], [
				'call_client_id' => $clientId,
				'call_route' => $callRoute,
				'call_user_id' => $userId ? $userId : $user_id,
				'call_voice' => $recording,
				'call_complete' => 1,
				'call_date'=> $callDate,
				'call_duration' => $callDuration ?: 0,
				'call_new_voicemail' => $callNewVoicemail
			]);
		} else {
			$fromNumber = is_numeric($fromNumber) ? '+' . ltrim($fromNumber, '+') : $fromNumber;
			$call_note = [
				'call_from' => $fromNumber,
				'call_to' => $toNumber,
				'call_type' => 'dialer',
				'call_client_id' => $clientId,
				'call_route' => $callRoute,
				'call_twilio_sid' => $callSid,
				'call_user_id' => $userId ? $userId : $user_id,
				'call_voice' => $recording,
				'call_complete' => 1,
				'call_date'=> $callDate,
				'call_workspace_sid'=> $this->config->item('workspaceSid'),
				'call_duration' => $callDuration ?: 0,
				'call_new_voicemail' => $callNewVoicemail
			];
			if(strpos($call_note['call_from'], $this->config->item('twilioNumber')) !== FALSE)
				$call_note['call_from'] = $this->config->item('myNumber');
			elseif(strpos($call_note['call_to'], $this->config->item('twilioNumber')) !== FALSE)
				$call_note['call_to'] = $this->config->item('myNumber');

			$this->mdl_calls->insert($call_note);
		}
		
		$this->dial_from_hold_call($callSid);
		if($dialCallSid)
			$this->dial_from_hold_call($dialCallSid);

		//$call_note_view = $this->load->view('call_note', ['user' => $user, 'call_note'=>$call_note, 'client_data'=>$client_data],  TRUE);
		
		/*if($clientId)
			make_notes($clientId, $call_note_view, 'contact', 0);*/


		pushJob('common/socket_send', [
			'room' => [$this->config->item('workspaceSid')],
			'message' => ['method' => 'updateHistory']
		]);

		$this->load->view_xml($this->config->item('company_dir') . '/hangup');
	}
	function recording_tmp()
	{
		$this->load->view_xml($this->config->item('company_dir') . '/rec_tmp');
	}

	function gather()
	{
		$client = new Client($this->config->item('accountSid'), $this->config->item('authToken'));
		
		$this->load->model('mdl_numbers');
		$this->load->model('mdl_user');
		
		$digits = $this->input->post('Digits');
		
		$view = '';
		$data = [];
		$extention = $this->mdl_numbers->get_extention_number(array('extention_key' => $digits, 'extention_emergency' => 0));
		$key = isset($extention->extention_key) ? $extention->extention_key : -1;
		
		switch ($this->input->post('Digits')) {
			case 201:
			case 9:
				$view = 'duty';
				$userObject = $this->mdl_user->get_usermeta(['duty' => 1]);
				$data['user'] = $userObject ? $userObject->row() : [];
				$data['caller'] = $this->input->post('Caller');
			break;
		    case $key:
		    	$hr = date('G', time());

		    	if($hr < config_item('twilioWorkingTimeFrom') || $hr > config_item('twilioWorkingTimeTo')) {
		    		$view = 'extention_voicemail';
					$data['extention'] = $extention;
					break;
		    	}

				if($extention->twilio_worker_id)
				{
					$workers = $client->taskrouter->workspaces($this->config->item('workspaceSid'))->workers($extention->twilio_worker_id)->fetch();
					$selfNumber = isset($extention->emp_phone) && $extention->emp_phone ? $extention->emp_phone : $extention->extention_number;

					if($selfNumber) {
						if($workers->available)
						{
							$attr = json_decode($workers->attributes);
							$this->extensionToWorker($key);
						}
						else {
							$this->extensionToNumber($key);
						}
					}
					else {
						$view = 'extention_voicemail';
						$data['extention'] = $extention;
						break;
					}
					
					return FALSE;
				}
				$view = 'extention';
				$data['extention'] = $extention;
			break;
			default:
				$view = 'enqueue';
				$number = ltrim($this->input->post('Caller'), '+1');
				$client_data = $this->mdl_clients->find_by_phone($number);
				$data['workflowSid'] = $this->config->item('workflowSid');
		    	$data['taskAttributes']['clientId'] = $client_data ? $client_data['client_id'] : NULL;
				$data['taskAttributes']['clientName'] = $client_data ? $client_data['client_name'] : NULL;
		    	$data['taskAttributes']['skills'] = ['support'];
		    break;
		}

		$this->load->view_xml($this->config->item('company_dir') . '/' . $view, $data);
	}

	function extensionToWorker($ext = NULL, $finished = FALSE) {
		$client = new Client($this->config->item('accountSid'), $this->config->item('authToken'));
		$this->load->model('mdl_numbers');
		$this->load->model('mdl_user');
		$extension = $this->mdl_numbers->get_extention_number(array('extention_key' => $ext, 'extention_emergency' => 0));

		$workers = $client->taskrouter->workspaces($this->config->item('workspaceSid'))->workers($extension->twilio_worker_id)->fetch();
		$selfNumber = isset($extension->emp_phone) && $extension->emp_phone ? $extension->emp_phone : $extension->extention_number;

		$attr = json_decode($workers->attributes);

		if(!$this->input->post('DialCallStatus') || $this->input->post('DialCallStatus') == 'no-answer') {
			if($finished)
				$this->extensionToNumber($ext);
			else
				$this->load->view_xml($this->config->item('company_dir') . '/extensionToWorker', ['ext' => $ext, 'uri' => $attr->contact_uri]);
		}
		else {
			$this->recording();
		}
	}

	function extensionToNumber($ext = NULL, $finished = FALSE) {
		$client = new Client($this->config->item('accountSid'), $this->config->item('authToken'));
		$this->load->model('mdl_numbers');
		$this->load->model('mdl_user');
		$extension = $this->mdl_numbers->get_extention_number(array('extention_key' => $ext, 'extention_emergency' => 0));

		$selfNumber = isset($extension->emp_phone) && $extension->emp_phone ? $extension->emp_phone : $extension->extention_number;

		if(!$this->input->post('DialCallStatus') || $this->input->post('DialCallStatus') == 'no-answer') {
			if($finished || !$selfNumber)
				$this->load->view_xml($this->config->item('company_dir') . '/extention_voicemail', ['extention' => $extension]);
			else
				$this->load->view_xml($this->config->item('company_dir') . '/extensionToNumber', ['number' => $selfNumber, 'ext' => $ext]);
		}
		else {
			$this->recording();
		}
	}
	
	
	
	function emergency_gather()
	{
		$this->load->model('mdl_numbers');
		$digits = $this->input->post('Digits');
		$digits=9;
		if($digits == 201 or $digits == 9) {
			$this->load->view('duty', ['caller' => $this->input->post('Caller')]);
		}
		//	$this->emergency_gather_result(0);
		//}
	}
	
	function emergency_gather_result($key = NULL)
	{
		$this->load->model('mdl_numbers');
		$data['status'] = $this->input->post('DialCallStatus') ? $this->input->post('DialCallStatus') : NULL;
		$view = '';
		$data = [];
		
		$emergs = $this->mdl_numbers->get_emergency();
		$data['emergency'] = isset($emergs[$key]) ? $emergs[$key] : NULL;
		$view = 'emergency';
		
		$data['key'] = $key + 1;
		$this->load->view_xml($view, $data);
		
	}

	function forwardToAgent($contact_uri = NULL, $forward = FALSE, $forwarder = NULL)
	{
		$this->load->view_xml($this->config->item('company_dir') . '/forward_to_agent', ['contact_uri' => $contact_uri, 'forward' => $forward, 'forwarder' => $forwarder]);
	}

	function forwardToNumber($number = NULL, $forward = FALSE, $then = FALSE, $forwarder = NULL)
	{
		$this->load->view_xml($this->config->item('company_dir') . '/forward_to_number', ['number' => $number, 'forward' => $forward, 'then' => $then, 'forwarder' => $forwarder]);
	}
	
	function resultForwardAgent($number = NULL, $notOfficeAction = FALSE, $forwarder = NULL)
	{

		if($this->input->post('DialCallStatus')=='no-answer') {
			if($notOfficeAction) {
				if($forwarder)
					return $this->forwardToAgent($forwarder, config_item('office_phone'));
				if(strlen($number) <= 1 && !$forwarder)
					return $this->forwardToNumber(config_item('office_phone'), FALSE, FALSE, $forwarder);	
				return $this->forwardToNumber(intval($number), FALSE, FALSE, $forwarder);
			}
			if(strlen($number) > 1)
				return $this->forwardToNumber(intval($number), FALSE, config_item('office_phone'), $forwarder);
			return $this->forwardToNumber(config_item('office_phone'), FALSE, FALSE, $forwarder);
		}

		return $this->recording();
	}
	
	function enque()
	{
		var_dump($_POST, $this); die;
	}
	
	function redirectToVoice()
	{
		$client = new Client($this->config->item('accountSid'), $this->config->item('authToken'));
		$client->calls($this->input->post('CallSid'))->update([
			"url" => base_url('client_calls/gather_voicemail'),
			"method" => "POST"
		]);
		$this->load->model('mdl_calls_tasks');
		$taskRow = $this->mdl_calls_tasks->get_by(['twilio_calls' => $this->input->post('CallSid')]);

		try {
			$task = $client->taskrouter
				->workspaces($this->config->item('workspaceSid'))
				->tasks($taskRow->twilio_tasks);
			$task->update([
				'assignmentStatus' => 'canceled',
				'reason' => 'will be recording'
			]);
		} catch (Exception $e) {

		}

		$this->mdl_calls_tasks->delete_by(['twilio_calls' => $this->input->post('CallSid')]);
	}
	
	function gather_voicemail()
	{
		$this->load->view_xml($this->config->item('company_dir') . '/rec_tmp');
	}

	function voicemail()
	{
		
	}

	function connection_loss()
	{
		$this->load->view_xml($this->config->item('company_dir') . '/connection_loss_message');
	}

	function call_status()
	{
		if($this->input->post('DialCallStatus')=='no-answer')
			return $this->gather_voicemail();

		return $this->recording();
	}

	/**
	 * @param Client $client
	 * @param string $workerSid
	 * @param string $taskSid
	 * @param $postTaskAttributes
	 * @throws \Twilio\Exceptions\TwilioException
	 */
	private function addReservationWorkersToTaskAttributes(Client $client, string $workerSid, string $taskSid, $postTaskAttributes)
	{
		if (!is_null($workerSid) && !is_null($taskSid) && !is_null($postTaskAttributes)) {
			$TaskAttributes = json_decode($postTaskAttributes);
			if (isset($TaskAttributes->workersReservation)) {
				$workersReservation = array_merge($TaskAttributes->workersReservation, [$workerSid]);
			} else {
				$workersReservation = [$workerSid];
			}
			$TaskAttributes->workersReservation = $workersReservation;
			$client->taskrouter->workspaces($this->config->item('workspaceSid'))
				->tasks($taskSid)
				->update([
					'attributes' => json_encode($TaskAttributes)
				]);
		}

	}

	/**
	 * @param Client $client
	 * @param $postTaskAttributes
	 * @throws \Twilio\Exceptions\TwilioException
	 */
	private function setReservationWorkersToIdle(Client $client, $postTaskAttributes)
	{
		$TaskAttributes = json_decode($postTaskAttributes);
		if (isset($TaskAttributes->workersReservation)) {
			foreach ($TaskAttributes->workersReservation as $workerSid) {
				$worker = $client->taskrouter->workspaces($this->config->item('workspaceSid'))
					->workers($workerSid)->fetch();
				if ($worker->activitySid == $this->config->item('wrapUpActivitySid')) {
					$worker->update([
						'activitySid' => $this->config->item('onlineActivitySid')
					]);
				}
			}
		}
	}

	/**
	 * @param Client $client
	 * @param $postTaskAttributes
	 * @return bool
	 */
	private function isAllWorkersTimeoutForCurrentTask(Client $client, $postTaskAttributes)
	{
		$taskAttributes = json_decode($postTaskAttributes);
		$skills = $taskAttributes->skills;
		$result = false;

		$fp = fopen('/tmp/twilio', 'a+');
		fwrite($fp, json_encode(sprintf('skills HAS "%s"', $skills[0])) . "\r\n\r\n");
		fclose($fp);


		$workers = $client->taskrouter->workspaces($this->config->item('workspaceSid'))->workers->read([
			'targetWorkersExpression' => sprintf('skills HAS "%s"', $skills[0]),
			'activitySid' => $this->config->item('onlineActivitySid')
		]);
		if (empty($workers)) {
			$result = true;
		}
		return $result;
	}

	function tasksCallback()
	{
		$client = new Client($this->config->item('accountSid'), $this->config->item('authToken'));

		if(
			$this->input->post('EventType') == 'reservation.created'
			&&
			$this->input->post('EventType') !== 'reservation.accepted'
		) {
			$taskAttributesJson = $this->input->post('TaskAttributes') ?? null;
			$taskSid = $this->input->post('TaskSid') ?? null;
			$workerSid = $this->input->post('WorkerSid') ?? null;
			$this->addReservationWorkersToTaskAttributes($client, $workerSid, $taskSid, $taskAttributesJson);
		}

		if (
			$this->input->post('EventType')=='task.canceled'
			||
			$this->input->post('EventType')=='task.system-deleted'
			||
			$this->input->post('EventType')=='task.deleted'
			||
			$this->input->post('EventType')=='reservation.accepted'
		) {
			if ($taskAttributesJson = $this->input->post('TaskAttributes')) {
				$this->setReservationWorkersToIdle($client, $taskAttributesJson);
			}
		}

		if ($this->input->post('EventType')=='reservation.timeout') {
			if ($taskAttributesJson = $this->input->post('TaskAttributes')) {
				$isSetAllTaskWorkersToIdle = $this->isAllWorkersTimeoutForCurrentTask($client, $taskAttributesJson);
				if ($isSetAllTaskWorkersToIdle) {
					$this->setReservationWorkersToIdle($client, $taskAttributesJson);
				}
			}
		}

		if($this->input->post('EventType')=='reservation.wrapup') {
			$taskSid = $this->input->post('TaskSid') ?? null;
			$client->taskrouter->workspaces($this->config->item('workspaceSid'))
				->tasks($taskSid)
				->update([
					"assignmentStatus" => "completed",
					"reason" => "the agent hang up"
				]);
		}

		if($this->input->post('EventType')=='task.created') {
			$data['twilio_calls'] = json_decode($this->input->post('TaskAttributes'))->call_sid;
			$data['twilio_tasks'] = $this->input->post('TaskSid');
			$this->load->model('mdl_calls_tasks');
			$this->mdl_calls_tasks->insert($data);
		}

		if($this->input->post('EventType')=='task.canceled' || $this->input->post('EventType')=='task.completed') {
			/*$url = base_url('client_calls/timeout_delete_workflow');
		    $curl = curl_init();                
		    $data = [];
			$data['workflow_sid'] = $this->input->post('WorkflowSid');
			$data['task_sid'] = $this->input->post('TaskSid');
		    curl_setopt($curl, CURLOPT_URL, $url);
		    curl_setopt($curl, CURLOPT_POST, TRUE);
		    curl_setopt($curl, CURLOPT_POSTFIELDS, $data); 
		    curl_setopt($curl, CURLOPT_USERAGENT, 'api');
		    curl_setopt($curl, CURLOPT_TIMEOUT, 1);
		    curl_setopt($curl, CURLOPT_HEADER, 0);
		    curl_setopt($curl, CURLOPT_RETURNTRANSFER, false);
		    curl_setopt($curl, CURLOPT_FORBID_REUSE, true);
		    curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 1);
		    curl_setopt($curl, CURLOPT_DNS_CACHE_TIMEOUT, 10); 
		    curl_setopt($curl, CURLOPT_FRESH_CONNECT, true);
		    $data = curl_exec($curl);*/
		}


		/*if($this->input->post('EventType')=='workflow.timeout') {
			$workerAttr = json_decode($this->input->post('WorkerAttributes'));
			$this->_update_workflow($workerAttr->level, $this->input->post('WorkflowSid'));
		}*/

		pushJob('common/socket_send', [
			'room' => [$this->config->item('workspaceSid')],
			'message' => ['method' => 'updateQueueCounter']
		]);
	}
	
	function send_sms_to_client()
	{
		$number = $this->input->post('PhoneNumber');
		$text = $this->input->post('sms', TRUE);

		$errors = [];

		if(empty($text)) {
			$errors[] = 'Undefined sms text';
		}

		if(strlen($number) < (int) config_item('phone_clean_length')) {
			$msg = 'Invalid Phone Number';

			if (empty($number)) {
				$msg = 'Undefined Phone Number';
			}

			$errors[] = $msg;

			die(json_encode(['status' => 'error', 'messages' => $errors]));
		}

		$this->load->driver('messages');

		$sendResult = $this->messages->send($number, $text);

		if (!is_array($sendResult)) {
			die(json_encode(['status' => 'error', 'message' => 'Unexpected error. Please try later.']));
		}

		$errors = [];

		foreach ($sendResult as $result) {
			if (isset($result['error'])) {
				$errors[] = $result['error'];
			}
		}

		if (sizeof($errors)) {
			$result = [
				'status' => 'error'
			];

			if (sizeof($errors) === 1) {
				$result['message'] = $errors[0];
			} else {
				$result['messages'] = $errors;
			}

			die(json_encode($result));
		}

		die(json_encode(['status' => 'ok', 'result' => $sendResult[0]]));
	}
	
	function send_voice_msg()
	{
		$phoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();
		$client = new Client($this->config->item('accountSid'), $this->config->item('authToken'));
		$userId =  $this->session->userdata['user_id'];
		$caller = $this->config->item('myNumber');
		$number = '+1' . $this->input->post('PhoneNumber'); 
		$voice = $this->input->post('voice');
		
		$swissNumberProto = $phoneUtil->parse($number, 'CA');
		
		$isValid = $phoneUtil->isValidNumber($swissNumberProto);

		$number = $phoneUtil->format($swissNumberProto, \libphonenumber\PhoneNumberFormat::E164);
		/*if($isValid)
		{*/
			$data = ['caller' => $caller, 'phone_number' => $number, 'sound' => $voice];
			
			$client->account->calls->create($number, $caller, array("url" => base_url('/client_calls/voiceName/'. $voice . '/' . $userId)));
		/*}*/
		
	}
	
	
	function voiceName($voice, $userId = NULL)
	{
		$voice = $this->mdl_voices->get($voice);
		$data['userId'] = $userId;

		$voice->voice_resp = str_replace('[USERID]', $userId, $voice->voice_resp);
		if($voice && !empty($voice))
		{
			$data['tpl'] = $voice;
			$this->load->view_xml($this->config->item('company_dir') . '/tpl' , $data);
		}
	}

	function voiceGather($userId = NULL)
	{
		$phoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();
		$client = new Client($this->config->item('accountSid'), $this->config->item('authToken'));
		$this->load->model('mdl_user');
		$user = FALSE;
		if($userId) {
			$userRes = $this->mdl_user->get_usermeta(['users.id' => $userId]);
			if($userRes)
				$user = $userRes->row();
		}

		if(!$user) {
			$this->load->view_xml($this->config->item('company_dir') . '/hangup');
			return FALSE;
		}

		switch ($this->input->post('Digits')) {
		    default:
		    	$phone = str_replace(['.', ' ', '(', ')', '-'], '', $user->emp_phone);
		    	$swissNumberProto = $phoneUtil->parse($phone, 'CA');
		
				$isValid = $phoneUtil->isValidNumber($swissNumberProto);

				$phone = $phoneUtil->format($swissNumberProto, \libphonenumber\PhoneNumberFormat::E164);
				/*if($isValid)
				{*/
					if($user && $user->twilio_worker_id) {
						$workers = $client->taskrouter->workspaces($user->twilio_workspace_id)->workers($user->twilio_worker_id)->fetch();
						if($workers->available)
						{
							$attr = json_decode($workers->attributes);
							$this->forwardToAgent($attr->contact_uri, $phone);
						}
						else {
							$this->forwardToNumber($phone, FALSE, config_item('office_phone'));
						}
					}
					else
						$this->forwardToNumber($phone, FALSE, config_item('office_phone'));
				/*}*/
			break;
			/*default:
				$this->load->view_xml($this->config->item('company_dir') . '/hangup');
			break;*/
		}
	}

	function hold_call($call_sid = NULL, $worker_id = NULL)
	{
		$data['ch_call_twilio_sid'] = $call_sid;
		$data['ch_date'] = date('Y-m-d H:i:s');
		$data['ch_call_number'] = $this->input->post('From');//$number;
		$userData = $this->mdl_user->get_user('id', array('twilio_worker_id' => $worker_id));
		$data['ch_user_id'] = NULL;
		if ($userData->num_rows())
			$data['ch_user_id'] = $userData->row()->id;
		$data['ch_client_id'] = NULL; 
		$clData = $this->mdl_clients->find_by_phone($data['ch_call_number']);
		if(isset($clData['client_id']) && $clData['client_id'])
			$data['ch_client_id'] = $clData['client_id'];

		$this->mdl_calls_hold->insert($data);

		pushJob('common/socket_send', [
			'room' => [$this->config->item('workspaceSid')],
			'message' => ['method' => 'updateInHoldList']
		]);

		$files = bucketScanDir('uploads/sounds');
		$this->load->view_xml($this->config->item('company_dir') . '/on_hold', ['file' => $files]);
	}
	
	function dial_from_hold_call($call_sid, $contact_uri = NULL)
	{
		$this->mdl_calls_hold->delete_by(['ch_call_twilio_sid' => $call_sid]);

		pushJob('common/socket_send', [
			'room' => [$this->config->item('workspaceSid')],
			'message' => ['method' => 'updateInHoldList']
		]);
		
		if($contact_uri)
			$this->load->view_xml($this->config->item('company_dir') . '/from_hold', ['call_sid' => $call_sid, 'contact_uri' => $contact_uri]);
	}
	
	function call_status_callback()
	{
		$callSid = $this->input->post('CallSid');
		$client = new Client($this->config->item('accountSid'), $this->config->item('authToken'));//parentCallSid
		$calls = $client->calls->read(['parentCallSid' => $callSid]);
		$holdCall = $this->mdl_calls_hold->get_by(['ch_call_twilio_sid' => $callSid]);
		
		if($holdCall)
			$this->dial_from_hold_call($callSid);
			
		foreach($calls as $call) {
			$holdCall = $this->mdl_calls_hold->get_by(['ch_call_twilio_sid' => $call->sid]);
			if($holdCall)
				$this->dial_from_hold_call($call->sid);
		}

		$this->recording();
	}

	function send_sms($id = NULL) {
		$client = new Client($this->config->item('accountSid'), $this->config->item('authToken'));

		$this->load->model('mdl_sms_messages');
		$sms_sid = $this->input->post('SmsSid');
		
		if(is_file(sys_get_temp_dir() . '/' . $sms_sid) && file_exists(sys_get_temp_dir() . '/' . $sms_sid)) {
			sleep(2);
			if(is_file(sys_get_temp_dir() . '/' . $sms_sid) && file_exists(sys_get_temp_dir() . '/' . $sms_sid))
				unlink(sys_get_temp_dir() . '/' . $sms_sid);
		}
		else {
			$fp = fopen(sys_get_temp_dir() . '/' . $sms_sid, 'w+');
			fclose($fp);
		}

		$findMessage = NULL;

		if($id)
			$findMessage = $this->mdl_sms_messages->get($id);
		else
			$findMessage = $this->mdl_sms_messages->get_by(['sms_sid' => $sms_sid]);

		$message = $client->messages($sms_sid)->fetch();

		if($findMessage) {
			$this->mdl_sms_messages->update($findMessage->sms_id, [
				'sms_status' => $message->status,
				'sms_error' => $message->errorMessage,
				'sms_sid' => $sms_sid,
			], TRUE);

			$socketData = [
				'method' => 'updateSmsStatus',
				'params' => [
					'sms_id' => $findMessage->sms_id,
					'sms_status' => $message->status,
					'sms_error' => $message->errorMessage,
				]
			];
		}
		else {
			$date = new DateTime();
			$tz = $date->getTimezone();
			$row = [
				'sms_sid' => $sms_sid,
				'sms_number' => ltrim($message->to, '+1'),
				'sms_body' => $message->body,
				'sms_date' => $message->dateSent->setTimezone(new DateTimeZone($tz->getName()))->format('Y-m-d H:i:s'),
				'sms_support' => 0,
				'sms_readed' => 1,
				'sms_user_id' => 0,
				'sms_incoming' => 0,
				'sms_auto' => 1,
				'sms_status' => $message->status,
				'sms_error' => $message->errorMessage,
			];
			$sms_id = $this->mdl_sms_messages->insert($row);

			$row['sms_sid'] = $sms_id;

			$socketData = [
				'method' => 'newSmsMessage',
				'params' => $row
			];
		}

		pushJob('common/socket_send', [
			'room' => ['sms'],
			'message' => $socketData
		]);
	}

	function receive_sms() {
		if($this->input->post('From') && $this->input->post('Body')) {
			$from = $this->input->post('From');
			$body = $this->input->post('Body', TRUE);

			$client = new Client($this->config->item('accountSid'), $this->config->item('authToken'));
			$this->load->model('mdl_user');
			$this->load->model('mdl_sms_messages');
			$smsForUser = $this->mdl_user->check_sms_for_user($from);

			$signature = NULL;


			if($smsForUser) {
				$signature = $smsForUser->recipient_firstname ? "\n" . $smsForUser->recipient_firstname . ' ' . $smsForUser->recipient_lastname : NULL;

				$phoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();
				$swissNumberProto = $phoneUtil->parse($smsForUser->emp_phone, 'CA');
				
				$isValid = $phoneUtil->isValidNumber($swissNumberProto);
				$to = $phoneUtil->format($swissNumberProto, \libphonenumber\PhoneNumberFormat::E164);

				$sms = $this->input->post('sms_body', TRUE);
				
				$this->load->view_xml($this->config->item('company_dir') . '/forward_sms_to_user', ['to' => $to, 'From' => $from, 'Body' => $body . $signature]);
			}



			/*******************INSERT TO DB*************/

			$sms_sid = $this->input->post('SmsSid');

			if(is_file(sys_get_temp_dir() . '/' . $sms_sid) && file_exists(sys_get_temp_dir() . '/' . $sms_sid)) {
				sleep(2);
				if(is_file(sys_get_temp_dir() . '/' . $sms_sid) && file_exists(sys_get_temp_dir() . '/' . $sms_sid))
					unlink(sys_get_temp_dir() . '/' . $sms_sid);
			}
			else {
				$fp = fopen(sys_get_temp_dir() . '/' . $sms_sid, 'w+');
				fclose($fp);
			}
		
			$findMessage = NULL;

			$findMessage = $this->mdl_sms_messages->get_by(['sms_sid' => $sms_sid]);
			$message = $client->messages($sms_sid)->fetch();

			if($findMessage) {
				$this->mdl_sms_messages->update($findMessage->sms_id, [
					'sms_status' => $message->status,
					'sms_error' => $message->errorMessage,
					'sms_sid' => $sms_sid,
				], TRUE);

				$socketData = [
					'method' => 'updateSmsStatus',
					'params' => [
						'sms_id' => $findMessage->sms_id,
						'sms_status' => $findMessage->sms_id,
						'sms_error' => $message->errorMessage
					]
				];
			}
			else {
				$date = new DateTime();
				$tz = $date->getTimezone();
				$row = [
					'sms_sid' => $sms_sid,
					'sms_number' => ltrim($message->from, '+1'),
					'sms_body' => $message->body,
					'sms_date' => isset($message->dateSent) && $message->dateSent ? $message->dateSent->setTimezone(new DateTimeZone($tz->getName()))->format('Y-m-d H:i:s') : date('Y-m-d H:i:s'),
					'sms_support' => 0,
					'sms_readed' => 0,
					'sms_user_id' => 0,
					'sms_incoming' => 1,
					'sms_status' => $message->status,
					'sms_error' => $message->errorMessage,
				];
				$sms_id = $this->mdl_sms_messages->insert($row);

				$row['sms_sid'] = $sms_id;

				$socketData = [
					'method' => 'newSmsMessage',
					'params' => $row
				];
			}

			pushJob('common/socket_send', [
				'room' => ['sms'],
				'message' => $socketData
			]);
			
			
			/*******************INSERT TO DB*************/

			/*$this->load->library('email');
			$config['mailtype'] = 'html';
			$this->email->initialize($config);
			
			$this->email->to('info@treedoctors.ca');
			$this->email->from('info@treedoctors.ca', 'Tree Doctors');
			$this->email->subject('Received SMS from ' . $from);
			
			$text = $body . $signature;
			
			$this->email->message($text);

			$this->email->send();*/
		}
	}

	function send_test_sms()
	{
		$this->load->driver('messages');
		$this->load->model('mdl_sms_messages');
		/*
		$findMessage = $this->mdl_sms_messages->get_by(['sms_sid' => 'SM5bcab9f0a12549eb9bbedde3da50d229']);
		echo "<pre>";var_dump($findMessage);
		die;*/
			$name = 'Test';//$this->input->post('client');
			$sms = 'Body 1';//$this->input->post('sms', TRUE);
			
			$result = $this->messages->send('+380930716075', 'Hello, My Test Message');
		/*}*/

		die(json_encode(['status' => 'ok', 'result' => $result]));
	}

	function voicemail2text() {
		$this->load->library('email');
		$addOnData = json_decode($this->input->post('AddOns'));
		
		$client = new Client($this->config->item('accountSid'), $this->config->item('authToken'));

		$headers['User-Agent'] = 'twilio-php/' . VersionInfo::string() . ' (PHP ' . phpversion() . ')';
        $headers['Accept-Charset'] = 'utf-8';
        $headers['Accept'] = 'application/json';

        //TODO: костыль для ДЕМО, перенести в конфиг !!!
		$extname = 'nexiwave_voicemail2text';
		if(isset($addOnData->results->nexiwave_voicemail2text_2))
			$extname = 'nexiwave_voicemail2text_2';
		
		if((isset($addOnData) && $addOnData != '') && (isset($addOnData->results) && $addOnData->results != '') && (isset($addOnData->results->$extname) && $addOnData->results->$extname != '') && (isset($addOnData->results->$extname->links) && $addOnData->results->$extname->links != '') && (isset($addOnData->results->$extname->links->recording) && $addOnData->results->$extname->links->recording != ''))
		{
			$recordingLink = $addOnData->results->$extname->links->recording;
			$recordingFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR .'Record-' . uniqid() . '.wav';
			$recordingBlob = file_get_contents($recordingLink);
			file_put_contents($recordingFile, $recordingBlob);

			$recordingURI = explode('/', $recordingLink);
			$recordingSid = $recordingURI[count($recordingURI) - 1];//countOk
			$callData = $client->recordings($recordingSid)->fetch();

			$call = $this->mdl_calls->get_by(['call_twilio_sid' => $callData->callSid]);
			if($call) {
				$response = $client->getHttpClient()->request(
					'GET',
					$addOnData->results->$extname->payload[0]->url . '.json',
					[],
					[],
					$headers,
					$this->config->item('accountSid'),
					$this->config->item('authToken')
				)->getContent();
				$data = isset($response['redirect_to']) ? json_decode(file_get_contents($response['redirect_to']), TRUE) : FALSE;

				if(isset($data['text']) && $data['text']) {
					$this->mdl_calls->update($call->call_id, ['call_text' => $data['text']]);

					$emailTo = $this->config->item('account_email_address');
					if($call->call_user_id) {
						$userData = $this->mdl_user->find_by_id($call->call_user_id);
						if($userData->user_email)
							$emailTo = $userData->user_email;
					}

					$subject = 'New Voicemail';

					$config['mailtype'] = 'html';
					$this->email->initialize($config);

					$this->email->to($emailTo);
					$this->email->from($this->config->item('account_email_address'), $this->config->item('company_name_short'));
					$this->email->subject($subject);
					$this->email->attach($recordingFile);
					$this->email->message('You Have a New Voicemail<br>Number: ' . $call->call_from . '<br>Message: "' . $data['text'] . '"');
					$this->email->send();
				}
				@unlink($recordingFile);
			}
		}
		return FALSE;
	}
	
}
?>
