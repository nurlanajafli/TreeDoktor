<?php

require(APPPATH . 'libraries/Google/autoload.php');

// 'q' => 'from:me to:test@gmail.com' example $where

class Gmail{
	function __construct()
	{
		
		
	}
	public function get_email($username, $password)
	{
		$CI = &get_instance();
		$CI->load->library('MimeMailParser');
		
		
		//Connect Gmail feed atom
		$url = "https://mail.google.com/mail/feed/atom"; 

		// Send Request to read email 
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_USERPWD, $username . ":" . $password);
		curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($curl, CURLOPT_ENCODING, "");
		$curlData = curl_exec($curl);
		curl_close($curl);
		
		//returning retrieved feed
		return $curlData;
		
		
		
		
		/*
		$client = new Google_Client();
		$client->setDeveloperKey('AIzaSyBDUPTmUYuYnv9r8d6-TXanSefGZclfKTw');
		$client->setApplicationName("Google Analytics - CMS title");
		$client->setClientId('888794505328-4k4vg8aq6e3ohigu7ubkmv5a0ljl7soo.apps.googleusercontent.com');
		$client->setClientSecret('uWyrbHH00xsUtDqRnbIPP1_D');
		$client->setRedirectUri('http://mainprospect.com/cron/test');
		$client->setApprovalPrompt('auto');
		$client->setAccessType('offline');
		$client->setScopes(array(
			'https://www.googleapis.com/auth/gmail.readonly',
			'https://www.googleapis.com/auth/userinfo.email',
			'https://www.googleapis.com/auth/userinfo.profile',
		));

		$oAuth = new Google_Auth_OAuth2($client);
		echo "<pre>";
		//var_dump($oAuth->getRefreshToken($CI->session->userdata('access_token')));die;
		if ( isset($_GET['code']) )
		{
			echo 1;
			
			$client->authenticate($_GET['code']);
			$CI->session->set_userdata('access_token', $client->getAccessToken());
			
		}
		/*if ($CI->session->userdata('access_token'))
			$client->setAccessToken($CI->session->userdata('access_token'));
		else
		{
			$authUrl = $client->createAuthUrl();
			
		
			redirect($authUrl);
			
		//}
/*
		if ( $client->getAccessToken() )
		{
			//var_dump($client->getAccessToken()); die;
			$CI->session->set_userdata('access_token', $client->getAccessToken());
			$token_data = $client->verifyIdToken()->getAttributes();
		}
		var_dump($client->verifyIdToken()->getAttributes()); die;
		//var_dump($CI->session->userdata('access_token')); die;
		$service = new Google_Service($client);
		
		$serviceGmail = new Google_Service_Gmail($client);
		
		$messages = array();
		
		$messagesResponse = $serviceGmail->users_messages->listUsersMessages($email, $where);
		
		$messages = $messagesResponse->getMessages();

		foreach ($messages as $key => $message) {
			
			$msg = $serviceGmail->users_messages->get('dmitriy.vashchenko@gmail.com', $message->getId(), array('format' => 'raw'));
			
			$CI->mimemailparser->setText(base64_decode(str_replace(array('-', '_', '*'), array('+', '/', '='), $msg->getRaw())));
			/*if($CI->mimemailparser->getAttachments())
			{
				$attachment = $CI->mimemailparser->getAttachments();
				$attachments[$key] = $attachment[0]->content;
			}
			else
			
				$result[$key] = $CI->mimemailparser->getMessageBody('html');
			
			
		}
		*/
		//return $result;
		
	}
	
	function get_oauth2_token($grantCode,$grantType = 'online') {
		global $client_id;
		global $client_secret;
		global $redirect_uri;
		 
		$oauth2token_url = "https://accounts.google.com/o/oauth2/token";
		$clienttoken_post = array(
		"client_id" => $client_id,
		"client_secret" => $client_secret);
	 
		if ($grantType === "online"){
			$clienttoken_post["code"] = $grantCode;
			$clienttoken_post["redirect_uri"] = $redirect_uri;
			$clienttoken_post["grant_type"] = "authorization_code";
		}
		 
		if ($grantType === "offline"){
			$clienttoken_post["refresh_token"] = $grantCode;
			$clienttoken_post["grant_type"] = "refresh_token";
		}
		 
		$curl = curl_init($oauth2token_url);
	 
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $clienttoken_post);
		curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	 
		$json_response = curl_exec($curl);
		curl_close($curl);
	 
		$authObj = json_decode($json_response);
		 
		//if offline access requested and granted, get refresh token
		if (isset($authObj->refresh_token)){
			global $refreshToken;
			$refreshToken = $authObj->refresh_token;
		}
	 
		$accessToken = $authObj->access_token;
		return $accessToken;
	}

}
