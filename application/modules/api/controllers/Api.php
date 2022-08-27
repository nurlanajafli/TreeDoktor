<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

use application\modules\references\models\Reference;
use application\modules\user\models\User;

class Api extends MX_Controller
{
	protected $CI;

	function __construct()
	{
		parent::__construct();
		$this->CI =& get_instance();
        $this->CI->load->driver('Jobs');
        $this->CI->load->library('Common/QuickBooks/QBAttachmentActions');
		$this->CI->load->model('mdl_clients');
		$this->CI->load->model('mdl_leads');
		$this->CI->load->model('mdl_leads_status');
		$this->CI->pointlocation = NULL;
	}






	function mail_feed() {
		/**
			Purpose: 			Currently to save leads only
			Authorized:		Via "From address"

			Params:				user inputed form fields mandatory
											1. from
										  2. from_address
											3. name
											4. email
											5. phone

											non mandatory
											1. street
											2. city
											3. postal_code
											4. details

			Accepted formats:
											1. POST <input type text...> OR js formed post: var data = new FormData(); data.append('name', 'Nano Shmano');
											2. URL encoded JSON: variant=a&data.json=%7B%22zip_code%22%3A....
											3. json: {'name':'foo'}
											4. data.json: {'data' : {'name':'foo'}}
		**/

		$flat = [];

		if ( isset($_POST) && sizeof($_POST) > 0 ) {
			syslog(LOG_DEBUG,json_encode($_POST));

			if(array_key_exists('data',$_POST))
				$flat = array_merge($flat, $_POST["data"]);
			elseif(array_key_exists('data_json',$_POST)) {
				foreach (json_decode($_POST["data_json"]) as $key => $value)
					$flat[$key] = $value[0];
			}
			else
				$flat = array_merge($flat, $_POST);

		} else {
			// get the data - parse it
			$raw = file_get_contents('php://input');
			syslog(LOG_DEBUG,json_encode($raw));

			// ether json data or url encoded crap
			$raw = preg_replace("/[\r\n]+/", " ", $raw);
			$raw = utf8_encode($raw);
			if (json_decode($raw) === null) {

				foreach (explode('&', $raw) as $chunk) {
			    $param = explode("=", $chunk);
					if ( $param && ( $param[0] == "data_json" || $param[0] == "data.json") )
						$parcel = json_decode(urldecode($param[1]),true);
				}
				// key-values from this data.json are single item arrays - flatten 'em (grab only first ones)
				if ( isset($parcel) && is_array($parcel) ) {
					foreach ($parcel as $key => $value)
						$flat[$key] = $value[0];
				} else {
					syslog(LOG_DEBUG,"mail_feed DID NOT LOCATE DATA! Check incoming structure!");
				}
			} else {
				$jsonArray = json_decode($raw,true);
				if($jsonArray && json_last_error() === JSON_ERROR_NONE) {
					// if "data" key exists and has items
					if(array_key_exists('data',$jsonArray) && sizeof($jsonArray["data"]) > 0 )
						$flat = array_merge($flat, $jsonArray["data"]);
					elseif(array_key_exists('data_json',$jsonArray) && sizeof($jsonArray["data_json"]) > 0 )
						foreach ($jsonArray["data_json"] as $key => $value)
							$flat[$key] = $value[0];
					else
						$flat = array_merge($flat, $jsonArray);
				}
			}
		}

		$flat["from_address"] = filter_var($flat["from_address"], FILTER_SANITIZE_EMAIL);
		// make sure we've got a valid email
		if( filter_var( $flat["from_address"], FILTER_VALIDATE_EMAIL ) ) {
		    // split on @ and return last value of array (the domain)
				$var = explode('@', $flat["from_address"]);
				$domain = array_pop($var);
		}

		$brand_id = default_brand();
		$brand_site = parse_url(brand_site($brand_id), PHP_URL_HOST);
		$site = str_replace('www.', '', rtrim($brand_site, '/'));

		if ( !isset($domain) || $site != $domain ) {
			// received from unauthorized domain; secondary check on email list
			$valid = false;
			// get list of emails
			$user = User::where(['user_email' => $flat["from_address"]])->first();
			if ( $user && $user->active_status == 'yes' ) {
				$valid = true;
			}
			if ( !$valid ) die('Unauthorized');
		}

		// passed the auth - add what is needed
		if ( !isset($flat["city"]) ) $flat["city"] = $this->config->item('office_city');
		if ( !isset($flat["country"]) ) $flat["country"] = $this->config->item('office_country');
		if ( !isset($flat["state"]) ) $flat["state"] = $this->config->item('office_state');
		if ( !isset($flat["lat"]) ) $flat["lat"] = $this->config->item('office_lat');
		if ( !isset($flat["lon"]) ) $flat["lon"] = $this->config->item('office_lon');

		$lead_origin = $flat["from_address"];

		// merge data to _POST
		$_POST = array_merge($_POST, $flat);

		// strip garbage off the posted values
		foreach ($_POST as $key => $value)
				if ( is_string($value) ) $_POST[$key] = rtrim($value);

		// validate post
		$this->load->library('form_validation');
		$this->form_validation->set_message('check_phone', 'The Phone number must contain a valid phone number.');
		$this->form_validation->set_rules('name', 'Name', 'required|trim');
		$this->form_validation->set_rules('email', 'Email', 'required|trim|valid_email');
		$this->form_validation->set_rules('phone', 'Phone', 'required|trim');
		$this->form_validation->set_rules('address', 'Address', 'required|trim');
		$this->form_validation->set_rules('city', 'City', 'trim');
		$this->form_validation->set_rules('zip_code', 'Postal Code', 'trim');

		$this->form_validation->set_rules('country', 'Сountry', 'trim');
		$this->form_validation->set_rules('state', 'State', 'trim');
		$this->form_validation->set_rules('lat', 'Lat', 'required|trim', [
		    'required' => 'You must provide the correct address'
        ]);
		$this->form_validation->set_rules('lon', 'Lon', 'required|trim', [
            'required' => 'You must provide the correct address'
        ]);

		$this->form_validation->set_rules('note', 'Request', 'trim');

		if($this->form_validation->run($this) == FALSE)
		{
			$result['errors'] = $this->form_validation->error_array();
			if(!isset($result['errors']['address']) && (isset($result['errors']['lat']) || isset($result['errors']['lon']))) {
                $result['errors']['address'] = $result['errors']['lat'] ?? $result['errors']['lon'];
            }
		}
		else
		{
			$this->CI->load->model('mdl_clients');
			$this->CI->load->model('mdl_leads');
			$this->CI->load->model('mdl_leads_status');

      $data['client_brand_id'] = $brand_id;
			$data['client_name'] = $this->input->post('name', TRUE);
			$data['client_type'] = 1;
			$data['client_address'] = $this->input->post('address', TRUE);
			$data['client_city'] = $this->input->post('city', TRUE);
			$data['client_zip'] = $this->input->post('zip_code', TRUE);

			$data['client_state'] = $this->input->post('state', TRUE);
			$data['client_country'] = $this->input->post('country', TRUE);
			$data['client_lng'] = $this->input->post('lon', TRUE);
			$data['client_lat'] = $this->input->post('lat', TRUE);

			$data['client_date_created'] = date('Y-m-d');

			if(!$data['client_lat'] || !$data['client_lng'])
			{
				$coords = get_lat_lon($data['client_address'], $data['client_city'], $data['client_state'], $data['client_zip'], $data['client_country']);
				$data['client_lat'] = $coords['lat'];
				$data['client_lng'] = $coords['lon'];
			}

			$client_id = $this->CI->mdl_clients->add_new_client_with_data($data);

			$contact_data['cc_client_id'] = $client_id;
			$contact_data['cc_name'] = $this->input->post('name', TRUE);
			$contact_data['cc_phone'] = numberFrom($this->input->post('phone'));
			$contact_data['cc_phone_clean'] = substr(numberFrom($this->input->post('phone')), 0, config_item('phone_clean_length'));
			$contact_data['cc_email'] = $this->input->post('email', TRUE);
			$contact_data['cc_print'] = 1;
			$this->CI->mdl_clients->add_client_contact($contact_data);

      pushJob('quickbooks/client/syncclientinqb', serialize(['id' => $client_id, 'qbId' => '']));

			$lead_data['lead_body'] = strip_tags($this->input->post('note'));

			$lead_reffered_by = Reference::where('name',$lead_origin)->first();
			if ( !$lead_reffered_by ) $lead_reffered_by = Reference::where('name','Other')->first();

			$lead_data['lead_reffered_by'] = ($lead_reffered_by) ? $lead_reffered_by->id : null;

			if($this->input->post('reff') && $this->input->post('reff') != 'no_info_provided')
				$lead_data['lead_reffered_by'] = $this->input->post('reff', TRUE);

			$thisStatus = $this->CI->mdl_leads_status->get_by(['lead_status_for_approval' => 1]);
			$lead_data['lead_status_id'] = $thisStatus->lead_status_id;
			$lead_data['lead_reason_status_id'] = 0;

			$lead_data['lead_created_by'] = $lead_origin;
			$lead_data['lead_date_created'] = date('Y-m-d H:i:s');
			$lead_data['client_id'] = $client_id;

			$lead_data['lead_address'] = $this->input->post('address', TRUE);
			$lead_data['lead_city'] = $this->input->post('city', TRUE);
			$lead_data['lead_zip'] = $this->input->post('zip_code', TRUE);

			$lead_data['lead_state'] = $this->input->post('state', TRUE);
			$lead_data['lead_country'] = $this->input->post('country', TRUE);
			$lead_data['latitude'] = $this->input->post('lat', TRUE);
			$lead_data['longitude'] = $this->input->post('lon', TRUE);

			$lead_data['lead_gclid'] = $this->input->post('gclid_field', TRUE);
			$lead_data['lead_msclkid'] = $this->input->post('msclkid', TRUE);

			if(!$lead_data['latitude'] || !$lead_data['longitude']){
				$coords = get_lat_lon($lead_data['lead_address'], $lead_data['lead_city'], $lead_data['lead_state'], $lead_data['lead_zip'], $lead_data['lead_country']);
				$lead_data['latitude'] = $coords['lat'];
				$lead_data['longitude'] = $coords['lon'];
			}

			$lead_data['lead_neighborhood'] = get_neighborhood(['latitude' => $lead_data['latitude'], 'longitude' => $lead_data['longitude']]);

			$lead_id = $this->CI->mdl_leads->insert_leads($lead_data);
			make_notes($client_id, 'New lead "For Approval" was created', 'system', $lead_id);

			if ($lead_id)
			{
				$lead_no = str_pad($lead_id, 5, '0', STR_PAD_LEFT);
				$lead_no = $lead_no . "-L";
				$update_data = array("lead_no" => $lead_no);
				$wdata = array("lead_id" => $lead_id);
				$this->CI->mdl_leads->update_leads($update_data, $wdata);
				$result['status'] = 'ok';

                $messageData = $lead_data;
                $messageData['lead_no'] = $lead_no;
                $messageData['name'] = $contact_data['cc_name'];
                $messageData['phone'] = $contact_data['cc_phone_clean'];
                $messageData['email'] = $contact_data['cc_email'];
                $messageData['message'] = $lead_data['lead_body'];

                \Illuminate\Support\Facades\Notification::route('mail', config_item('account_email_address'))
                    ->notify(new application\notifications\NewLeadNotification($messageData));
			}
			if ( $result['status'] == 'ok' ) die("ok");
		}
		die(json_encode($result));
	}












	function lead_feed() {
		/**
			Purpose: 			to save leads
			Authorized:		API ACCESS KEY ( set it with k8s configmap to be written to config.php )

			Params:				user inputed form fields mandatory
											1. name
											2. email
											3. phone
											4. address
											5. zip_code

										hidden optional
											1. origin			"SAFETY FIRST"

										hidden fields mandatory
											1. api_key		1fb2e8fb-c306-490d-986a-c80c8d9467be

										hidden fields mandatory (auto-added by unbounce)
											1. page_uuid	ex.: 640eb6ad-f311-4167-8d71-ce7124b72faa
											2. page_url		ex.: "http://unbouncepages.com/arbostar/"

										optional and autocompleted to default company if missing from input
											1.  country (defaulted to client country)
											2.  city (defaulted to client city)
											3. 	state (defaulted to client state)
											4.  lat (defaulted to client's)
											5. lon (defaulted to client's)
			Accepted formats:
											1. POST <input type text...> OR js formed post: var data = new FormData(); data.append('name', 'Nano Shmano');
											2. URL encoded JSON: variant=a&data.json=%7B%22zip_code%22%3A....
											3. json: {'name':'foo'}
											4. data.json: {'data' : {'name':'foo'}}
		**/

		$ze_key = $this->config->item('api_key')?$this->config->item('api_key'):"1fb2e8fb-c306-490d-986a-c80c8d9467be";
		$result['status'] = 'error';
		$flat = [];

		if ( isset($_POST) && sizeof($_POST) > 0 ) {
			syslog(LOG_DEBUG,json_encode($_POST));

			if(array_key_exists('data',$_POST))
				$flat = array_merge($flat, $_POST["data"]);
			elseif(array_key_exists('data_json',$_POST)) {
				foreach (json_decode($_POST["data_json"]) as $key => $value)
					$flat[$key] = $value[0];
			}
			else
				$flat = array_merge($flat, $_POST);

		} else {
			// get the data - parse it
			$raw = file_get_contents('php://input');
			syslog(LOG_DEBUG,json_encode($raw));

			// ether json data or url encoded crap
			if (json_decode($raw) === null) {

				foreach (explode('&', $raw) as $chunk) {
			    $param = explode("=", $chunk);
					if ( $param && ( $param[0] == "data_json" || $param[0] == "data.json") )
						$parcel = json_decode(urldecode($param[1]),true);
				}
				// key-values from this data.json are single item arrays - flatten 'em (grab only first ones)
				if ( isset($parcel) && is_array($parcel) ) {
					foreach ($parcel as $key => $value)
						$flat[$key] = $value[0];
				} else {
					syslog(LOG_DEBUG,"lead_feed DID NOT LOCATE DATA! Check incoming structure!");
				}
			} else {
				$jsonArray = json_decode($raw,true);
				if($jsonArray && json_last_error() === JSON_ERROR_NONE) {
					// if "data" key exists and has items
					if(array_key_exists('data',$jsonArray) && sizeof($jsonArray["data"]) > 0 )
						$flat = array_merge($flat, $jsonArray["data"]);
					elseif(array_key_exists('data_json',$jsonArray) && sizeof($jsonArray["data_json"]) > 0 )
						foreach ($jsonArray["data_json"] as $key => $value)
							$flat[$key] = $value[0];
					else
						$flat = array_merge($flat, $jsonArray);
				}
			}
		}

		// check for page_uuid set
		if ( !isset($flat["page_uuid"]) ) die(json_encode($result));

		// check that api_key set and matches our env key
		if ( !isset($flat["api_key"]) || $flat["api_key"] == "" || $flat["api_key"] != $ze_key ) die(json_encode($result));

		// check that we can set the CORS with something
		if ( !isset($_SERVER['HTTP_ORIGIN']) && !isset($flat["page_url"]) ) die(json_encode($result));

		$origin = (isset($_SERVER['HTTP_ORIGIN']))? $_SERVER['HTTP_ORIGIN'] : parse_url($flat["page_url"],PHP_URL_HOST) ;

		// set the CORS with something
		$brand_id = default_brand();
		$brand_site = brand_site($brand_id);
		$brand_site_http = brand_site_http($brand_id);

		$siteHttp = str_replace('www.', '', rtrim($brand_site, '/'));
		$site = str_replace('www.', '', rtrim($brand_site_http, '/'));

		switch (str_replace('www.', '', rtrim($origin, '/'))) {
				case $siteHttp:
				case $site:
						header('Access-Control-Allow-Origin: ' . $origin);
						header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
						header('Access-Control-Max-Age: 1000');
						header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');
						break;
		}

		// passed the auth - add what is needed
		if ( !isset($flat["city"]) ) $flat["city"] = $this->config->item('office_city');
		if ( !isset($flat["country"]) ) $flat["country"] = $this->config->item('office_country');
		if ( !isset($flat["state"]) ) $flat["state"] = $this->config->item('office_state');
		if ( !isset($flat["lat"]) ) $flat["lat"] = $this->config->item('office_lat');
		if ( !isset($flat["lon"]) ) $flat["lon"] = $this->config->item('office_lon');

		$lead_origin = ( isset($flat["origin"]) ) ? $flat["origin"] : $site;

		if (  !isset($flat["request"])  )  {
			if (  isset($flat["comment"])  ) $flat["request"] = $flat["comment"];
			elseif (  isset($flat["comments"])  ) $flat["request"] = $flat["comments"];
			elseif (  isset($flat["message"])  ) $flat["request"] = $flat["message"];
		}

		// merge data to _POST
		$_POST = array_merge($_POST, $flat);

		// strip garbage off the posted values
		foreach ($_POST as $key => $value)
				if ( is_string($value) ) $_POST[$key] = rtrim($value);

		// validate post
		$this->load->library('form_validation');
		$this->form_validation->set_message('check_phone', 'The Phone number must contain a valid phone number.');
		$this->form_validation->set_rules('name', 'Name', 'required|trim');
		$this->form_validation->set_rules('email', 'Email', 'required|trim|valid_email');
		$this->form_validation->set_rules('phone', 'Phone', 'required|trim');
		$this->form_validation->set_rules('address', 'Address', 'required|trim');
		$this->form_validation->set_rules('city', 'City', 'trim');
		$this->form_validation->set_rules('zip_code', 'Postal Code', 'trim');

		$this->form_validation->set_rules('country', 'Сountry', 'trim');
		$this->form_validation->set_rules('state', 'State', 'trim');
		$this->form_validation->set_rules('lat', 'Lat', 'required|trim', [
		    'required' => 'You must provide the correct address'
        ]);
		$this->form_validation->set_rules('lon', 'Lon', 'required|trim', [
            'required' => 'You must provide the correct address'
        ]);

		$this->form_validation->set_rules('request', 'Request', 'trim');

		if($this->form_validation->run($this) == FALSE)
		{
			$result['errors'] = $this->form_validation->error_array();
			if(!isset($result['errors']['address']) && (isset($result['errors']['lat']) || isset($result['errors']['lon']))) {
                $result['errors']['address'] = $result['errors']['lat'] ?? $result['errors']['lon'];
            }
		}
		else
		{
			$this->CI->load->model('mdl_clients');
			$this->CI->load->model('mdl_leads');
			$this->CI->load->model('mdl_leads_status');

      $data['client_brand_id'] = $brand_id;
			$data['client_name'] = $this->input->post('name', TRUE);
			$data['client_type'] = 1;
			$data['client_address'] = $this->input->post('address', TRUE);
			$data['client_city'] = $this->input->post('city', TRUE);
			$data['client_zip'] = $this->input->post('zip_code', TRUE);

			$data['client_state'] = $this->input->post('state', TRUE);
			$data['client_country'] = $this->input->post('country', TRUE);
			$data['client_lng'] = $this->input->post('lon', TRUE);
			$data['client_lat'] = $this->input->post('lat', TRUE);

			$data['client_date_created'] = date('Y-m-d');

			if(!$data['client_lat'] || !$data['client_lng'])
			{
				$coords = get_lat_lon($data['client_address'], $data['client_city'], $data['client_state'], $data['client_zip'], $data['client_country']);
				$data['client_lat'] = $coords['lat'];
				$data['client_lng'] = $coords['lon'];
			}

			$client_id = $this->CI->mdl_clients->add_new_client_with_data($data);

			$contact_data['cc_client_id'] = $client_id;
			$contact_data['cc_name'] = $this->input->post('name', TRUE);
			$contact_data['cc_phone'] = numberFrom($this->input->post('phone'));
			$contact_data['cc_phone_clean'] = substr(numberFrom($this->input->post('phone')), 0, config_item('phone_clean_length'));
			$contact_data['cc_email'] = $this->input->post('email', TRUE);
			$contact_data['cc_print'] = 1;
			$this->CI->mdl_clients->add_client_contact($contact_data);

      pushJob('quickbooks/client/syncclientinqb', serialize(['id' => $client_id, 'qbId' => '']));

			$lead_data['lead_body'] = strip_tags($this->input->post('request'));

			$lead_reffered_by = Reference::where('name',$lead_origin)->first();
			if ( !$lead_reffered_by ) $lead_reffered_by = Reference::where('name','Other')->first();

			$lead_data['lead_reffered_by'] = ($lead_reffered_by) ? $lead_reffered_by->id : null;

			if($this->input->post('reff') && $this->input->post('reff') != 'no_info_provided')
				$lead_data['lead_reffered_by'] = $this->input->post('reff', TRUE);

			$thisStatus = $this->CI->mdl_leads_status->get_by(['lead_status_for_approval' => 1]);
			$lead_data['lead_status_id'] = $thisStatus->lead_status_id;
			$lead_data['lead_reason_status_id'] = 0;

			$lead_data['lead_created_by'] = $lead_origin;
			$lead_data['lead_date_created'] = date('Y-m-d H:i:s');
			$lead_data['client_id'] = $client_id;

			$lead_data['lead_address'] = $this->input->post('address', TRUE);
			$lead_data['lead_city'] = $this->input->post('city', TRUE);
			$lead_data['lead_zip'] = $this->input->post('zip_code', TRUE);

			$lead_data['lead_state'] = $this->input->post('state', TRUE);
			$lead_data['lead_country'] = $this->input->post('country', TRUE);
			$lead_data['latitude'] = $this->input->post('lat', TRUE);
			$lead_data['longitude'] = $this->input->post('lon', TRUE);

			$lead_data['lead_gclid'] = $this->input->post('gclid_field', TRUE);
			$lead_data['lead_msclkid'] = $this->input->post('msclkid', TRUE);

			if(!$lead_data['latitude'] || !$lead_data['longitude']){
				$coords = get_lat_lon($lead_data['lead_address'], $lead_data['lead_city'], $lead_data['lead_state'], $lead_data['lead_zip'], $lead_data['lead_country']);
				$lead_data['latitude'] = $coords['lat'];
				$lead_data['longitude'] = $coords['lon'];
			}

			$lead_data['lead_neighborhood'] = get_neighborhood(['latitude' => $lead_data['latitude'], 'longitude' => $lead_data['longitude']]);

			$lead_id = $this->CI->mdl_leads->insert_leads($lead_data);
			make_notes($client_id, 'New lead "For Approval" was created', 'system', $lead_id);

			if ($lead_id)
			{
				$lead_no = str_pad($lead_id, 5, '0', STR_PAD_LEFT);
				$lead_no = $lead_no . "-L";
				$update_data = array("lead_no" => $lead_no);
				$wdata = array("lead_id" => $lead_id);
				$this->CI->mdl_leads->update_leads($update_data, $wdata);
				$result['status'] = 'ok';

                $messageData = $lead_data;
                $messageData['lead_no'] = $lead_no;
                $messageData['name'] = $contact_data['cc_name'];
                $messageData['phone'] = $contact_data['cc_phone_clean'];
                $messageData['email'] = $contact_data['cc_email'];
                $messageData['message'] = $lead_data['lead_body'];

                \Illuminate\Support\Facades\Notification::route('mail', config_item('account_email_address'))
                    ->notify(new application\notifications\NewLeadNotification($messageData));
			}
		}
		die(json_encode($result));
	}

	function ajax_save_lead()
	{
		$result['status'] = 'error';
        $brand_id = default_brand();
		$brand_site = brand_site($brand_id);
        $brand_site_http = brand_site_http($brand_id);

		$siteHttp = str_replace('www.', '', rtrim($brand_site, '/'));
		$site = str_replace('www.', '', rtrim($brand_site_http, '/'));

		if(!isset($_SERVER['HTTP_ORIGIN']))
			show_404();

		switch (str_replace('www.', '', rtrim($_SERVER['HTTP_ORIGIN'], '/'))) {
            case $siteHttp:
            case $site:
                header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
                header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
                header('Access-Control-Max-Age: 1000');
                header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');
                break;
        }

		//$result['errors'] = [$siteHttp, $site, $_SERVER['HTTP_ORIGIN']];
		//die(json_encode($result));

		$this->load->library('form_validation');
		$this->form_validation->set_message('check_phone', 'The Phone number must contain a valid phone number.');
		$this->form_validation->set_rules('name', 'Name', 'required|trim');
		//$this->form_validation->set_rules('last_name', 'Name', 'exact_length[0]');
		$this->form_validation->set_rules('email', 'Email', 'required|trim|valid_email');
		$this->form_validation->set_rules('phone', 'Phone', 'trim|callback_check_phone');
		$this->form_validation->set_rules('address', 'Address', 'required|trim');
		$this->form_validation->set_rules('city', 'City', 'trim');
		$this->form_validation->set_rules('zip_code', 'Postal Code', 'trim');

		$this->form_validation->set_rules('country', 'Сountry', 'trim');
		$this->form_validation->set_rules('state', 'State', 'trim');
		$this->form_validation->set_rules('lat', 'Lat', 'required|trim', [
		    'required' => 'You must provide the correct address'
        ]);
		$this->form_validation->set_rules('lon', 'Lon', 'required|trim', [
            'required' => 'You must provide the correct address'
        ]);

		$this->form_validation->set_rules('request', 'Request', 'required|trim');
		if($this->form_validation->run($this) == FALSE)
		{
			$result['errors'] = $this->form_validation->error_array();
			if(!isset($result['errors']['address']) && (isset($result['errors']['lat']) || isset($result['errors']['lon']))) {
                $result['errors']['address'] = $result['errors']['lat'] ?? $result['errors']['lon'];
            }
		}
		else
		{
			$this->CI->load->model('mdl_clients');
			$this->CI->load->model('mdl_leads');
			$this->CI->load->model('mdl_leads_status');

			/*$phone = numberFrom($this->input->post('phone'));
			$email = $this->input->post('email');
			$clients = $this->CI->mdl_clients->check_contact($phone, $email);

			if($clients && count($clients))
				$client_id = $clients[0]['client_id'];
			else
			{*/
            $data['client_brand_id'] = $brand_id;
			$data['client_name'] = $this->input->post('name', TRUE);
			$data['client_type'] = 1;
			$data['client_address'] = $this->input->post('address', TRUE);
			$data['client_city'] = $this->input->post('city', TRUE);
			$data['client_zip'] = $this->input->post('zip_code', TRUE);

			$data['client_state'] = $this->input->post('state', TRUE);
			$data['client_country'] = $this->input->post('country', TRUE);
			$data['client_lng'] = $this->input->post('lon', TRUE);
			$data['client_lat'] = $this->input->post('lat', TRUE);

			$data['client_date_created'] = date('Y-m-d');

			if(!$data['client_lat'] || !$data['client_lng'])
			{
				$coords = get_lat_lon($data['client_address'], $data['client_city'], $data['client_state'], $data['client_zip'], $data['client_country']);
				$data['client_lat'] = $coords['lat'];
				$data['client_lng'] = $coords['lon'];
			}

			$client_id = $this->CI->mdl_clients->add_new_client_with_data($data);

			$contact_data['cc_client_id'] = $client_id;
			$contact_data['cc_name'] = $this->input->post('name', TRUE);
			$contact_data['cc_phone'] = numberFrom($this->input->post('phone'));
			$contact_data['cc_phone_clean'] = substr(numberFrom($this->input->post('phone')), 0, config_item('phone_clean_length'));
			$contact_data['cc_email'] = $this->input->post('email', TRUE);
			$contact_data['cc_print'] = 1;
			$this->CI->mdl_clients->add_client_contact($contact_data);

            pushJob('quickbooks/client/syncclientinqb', serialize(['id' => $client_id, 'qbId' => '']));
			/*}*/


			$lead_data['lead_body'] = strip_tags($this->input->post('request'));
			$lead_data['lead_reffered_by'] = 'google_search';

			if($this->input->post('reff') && $this->input->post('reff') != 'no_info_provided')
				$lead_data['lead_reffered_by'] = $this->input->post('reff', TRUE);

			$thisStatus = $this->CI->mdl_leads_status->get_by(['lead_status_for_approval' => 1]);
			$lead_data['lead_status_id'] = $thisStatus->lead_status_id;
			$lead_data['lead_reason_status_id'] = 0;

			$lead_data['lead_created_by'] = $site;
			$lead_data['lead_date_created'] = date('Y-m-d H:i:s');
			$lead_data['client_id'] = $client_id;

			$lead_data['lead_address'] = $this->input->post('address', TRUE);
			$lead_data['lead_city'] = $this->input->post('city', TRUE);
			$lead_data['lead_zip'] = $this->input->post('zip_code', TRUE);

			$lead_data['lead_state'] = $this->input->post('state', TRUE);
			$lead_data['lead_country'] = $this->input->post('country', TRUE);
			$lead_data['latitude'] = $this->input->post('lat', TRUE);
			$lead_data['longitude'] = $this->input->post('lon', TRUE);

			$lead_data['lead_gclid'] = $this->input->post('gclid_field', TRUE);
			$lead_data['lead_msclkid'] = $this->input->post('msclkid', TRUE);

			if(!$lead_data['latitude'] || !$lead_data['longitude']){
				$coords = get_lat_lon($lead_data['lead_address'], $lead_data['lead_city'], $lead_data['lead_state'], $lead_data['lead_zip'], $lead_data['lead_country']);
				$lead_data['latitude'] = $coords['lat'];
				$lead_data['longitude'] = $coords['lon'];
			}

			$lead_data['lead_neighborhood'] = get_neighborhood(['latitude' => $lead_data['latitude'], 'longitude' => $lead_data['longitude']]);

			$lead_id = $this->CI->mdl_leads->insert_leads($lead_data);
			make_notes($client_id, 'New lead "For Approval" was created', 'system', $lead_id);

			if ($lead_id)
			{
				$lead_no = str_pad($lead_id, 5, '0', STR_PAD_LEFT);
				$lead_no = $lead_no . "-L";
				$update_data = array("lead_no" => $lead_no);
				$wdata = array("lead_id" => $lead_id);
				$this->CI->mdl_leads->update_leads($update_data, $wdata);
				$result['status'] = 'ok';

                $messageData = $lead_data;
                $messageData['lead_no'] = $lead_no;
                $messageData['name'] = $contact_data['cc_name'];
                $messageData['phone'] = $contact_data['cc_phone_clean'];
                $messageData['email'] = $contact_data['cc_email'];
                $messageData['message'] = $lead_data['lead_body'];

                \Illuminate\Support\Facades\Notification::route('mail', config_item('account_email_address'))
                    ->notify(new application\notifications\NewLeadNotification($messageData));
			}
		}
		die(json_encode($result));
	}


	function ajax_save_request()
	{
		$result['status'] = 'error';die(json_encode($result));
	}

	function settings() {
        $brand_id = default_brand();

        return $this->response([
            'base_url' => base_url(),//$socketConfigData['app']['backendDomain'],
            'port' => config_item('externalWsPort'),
            'company_name' => brand_name($brand_id, true),
            'logo' => base_url(get_brand_logo($brand_id, 'main_logo_file', '/assets/' . $this->config->item('company_dir') . '/img/logo.png'))
        ]);
	}

	public function check_phone($str)
	{
		$codes = config_item('allow_phone_codes');
		if(!$codes)
		    return true;

		$codes_array = explode(",", $codes);
        if(!$codes_array || !count($codes_array))
            return true;

        $codes_result = array_map(function ($v){ return intval($v); }, $codes_array);

        if(preg_match('/^\((' . implode('|', $codes_result) . ')\) [0-9]{3}-[0-9]{4}$/', $str))
			return TRUE;
		return FALSE;
	}
}
