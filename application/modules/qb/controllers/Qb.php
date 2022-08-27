<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

use application\modules\estimates\models\EstimatesService;
use application\modules\estimates\models\EstimateStatus;
use application\modules\estimates\models\TreeInventoryEstimateService;
use application\modules\invoices\models\InvoiceStatus;
use application\modules\leads\models\LeadStatus;
use application\modules\messaging\models\SmsTpl;
use application\modules\workorders\models\Workorder;
use Illuminate\Database\Eloquent\Builder;
use QuickBooksOnline\API\Core\ServiceContext;
use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\PlatformService\PlatformService;
use QuickBooksOnline\API\Core\Http\Serialization\XmlObjectSerializer;
use QuickBooksOnline\API\Facades\Customer;
use QuickBooksOnline\API\Facades\Item;
use QuickBooksOnline\API\QueryFilter\QueryMessage;
use QuickBooksOnline\API\Facades\Invoice;
use QuickBooksOnline\API\Facades\Payment;
use QuickBooksOnline\API\Facades\Account;
use QuickBooksOnline\API\Facades\QuickBookClass;
use QuickBooksOnline\API\Data\IPPAttachable;
use QuickBooksOnline\API\Data\IPPAttachableRef;
use QuickBooksOnline\API\Data\IPPReferenceType;
use QuickBooksOnline\API\Data\IPPIntuitEntity;
use application\modules\qb\models\QbLogs as QbLogsModel;
use application\modules\categories\models\Category;
use application\modules\estimates\models\Service;
use application\modules\brands\models\BrandReview;
use application\modules\classes\models\QBClass;
use application\modules\brands\models\BrandReviewLink;
use Carbon\Carbon;
use application\modules\clients\models\ClientsContact;
use application\modules\estimates\models\Estimate;
use application\modules\leads\models\Lead;
use application\modules\workorders\models\WorkorderStatus;
use application\modules\clients\models\Client;

use QuickBooksOnline\API\Data\IPPPaymentMethod;

class Qb extends MX_Controller
{
    protected $accessToken;
    protected $dataService;
    protected $oauth2LoginHelper;
    protected $settings;

    function __construct()
    {
        parent::__construct();
        if (!isUserLoggedIn() && $this->router->fetch_method() != 'endpoint') {
            redirect('login');
            return FALSE;
        }

        $this->load->config('config_qb');
        $this->load->helper('qb_helper');
        $this->load->helper('user_tasks_helper');
        $this->load->helper('user');
        $this->load->helper('tree_helper');
        $this->load->helper('fileinput_helper');
        //$this->load->library('session');
        $this->load->library('Common/PaymentActions');
        $this->load->library('Common/EstimateActions');
        $this->load->library('Common/CategoryActions');
        $this->load->library('Common/QuickBooks/QBPaymentActions');
        $this->load->library('Common/QuickBooks/QBAttachmentActions');
        $this->load->library('Common/QuickBooks/QBBase');
        $this->load->library('Common/QuickBooks/QBClassActions');
        $this->load->library('Common/QuickBooks/QBCategoryActions');
        $this->load->library('Common/QuickBooks/QBClientActions');
        $this->load->library('Common/WorkorderActions');
        $this->load->library('Common/InvoiceActions');
        $this->load->library('Common/LeadsActions');
        $this->load->library('PHPExcel');
        $this->load->library('Googlemaps');
        $this->settings = getQbSettings();

        if (!empty($this->settings['accessToken'])) {
            $this->accessToken = unserialize($this->settings['accessToken']);
        }

        if (isset($this->settings['clientID']) && isset($this->settings['clientSecret'])) {
            $this->dataService = DataService::Configure(array(
                'auth_mode' => 'oauth2',
                'ClientID' => $this->settings['clientID'],
                'ClientSecret' => $this->settings['clientSecret'],
                'RedirectURI' => base_url('qb/processCode'),
                'scope' => $this->settings['OauthScope'],
                'baseUrl' => $this->settings['baseUrl']
            ));
            $this->oauth2LoginHelper = $this->dataService->getOAuth2LoginHelper();
        }

        if ((!$this->dataService || !$this->oauth2LoginHelper) && $this->router->fetch_method() != 'callback') {
            show_404();
        }

        //load all common models;
        $this->load->model('mdl_clients');
        $this->load->model('mdl_client_payments');
        $this->load->model('mdl_services');
        $this->load->model('mdl_services_orm');
        $this->load->model('mdl_settings_orm');
        $this->load->model('mdl_leads');
        $this->load->model('mdl_leads_status');
        $this->load->model('mdl_estimates');
        $this->load->model('mdl_estimates_orm');
        $this->load->model('mdl_workorders');
        $this->load->model('mdl_invoices');
        $this->load->model('mdl_client_tasks');
        $this->load->model('mdl_bundles_services');
        $this->load->model('mdl_estimates_bundles');
        $this->load->model('mdl_vehicles');
        $this->load->model('mdl_schedule');
        $this->load->model('mdl_payments');
        $this->load->model('mdl_crews');
        $this->load->model('mdl_letter');
        $this->load->model('mdl_employees');
        $this->load->model('mdl_letter');
        $this->load->model('mdl_trees');
        $this->load->model('mdl_work_types_orm', 'work_types');
        $this->load->model('mdl_est_status');
        $this->load->model('mdl_user', 'mdl_users');
        $this->load->model('mdl_tree_inventory_orm', 'tree_inventory');
    }

    public function callback()
    {
        $data['title'] = 'QuickBooks Settings';
        if (isset($this->settings['clientID']) && isset($this->settings['clientSecret']))
            $data['authUrl'] = $this->oauth2LoginHelper->getAuthorizationCodeURL();

        if (!empty($this->accessToken)) {
            $data['accessTokenJson'] = ['token_type' => 'bearer',
                'access_token' => $this->accessToken->getAccessToken(),
                'refresh_token' => $this->accessToken->getRefreshToken(),
                'x_refresh_token_expires_in' => $this->accessToken->getRefreshTokenExpiresAt(),
                'expires_in' => $this->accessToken->getAccessTokenExpiresAt()
            ];
            $this->dataService->updateOAuth2Token($this->accessToken);
        }
        $this->load->view("index", $data ?? []);
    }

    public function processCode()
    {
        if (!$this->input->get('code') || !$this->input->get('realmId'))
            show_404();

        $parseUrl = parseAuthRedirectUrl($_SERVER['QUERY_STRING']);
        /*
         * Update the OAuth2Token
         */
        if (!isset($parseUrl['code']) || !$parseUrl['code'] || !isset($parseUrl['realmId']) || !$parseUrl['realmId'])
            show_404();

        $this->accessToken = $this->oauth2LoginHelper->exchangeAuthorizationCodeForToken($parseUrl['code'], $parseUrl['realmId']);

        if (!$this->accessToken)
            show_404();

        $this->dataService->updateOAuth2Token($this->accessToken);
        /*
         * Setting the accessToken for session variable
         */

        createOrUpdateQbAccessToken($this->accessToken);
    }

    public function makeAPICall()
    {
        /*
         * Update the OAuth2Token of the dataService object
         */
        if (!$this->accessToken)
            show_404();

        $this->dataService->updateOAuth2Token($this->accessToken);
        $companyInfo = $this->dataService->getCompanyInfo();

        if (!$companyInfo)
            die('NO DATA');

        $address = "QuickBooks Online API call was successfully passed\n\nCompany Name: " . $companyInfo->CompanyName . "\nCompany Address: " .
            $companyInfo->CompanyAddr->Line1 . " " . $companyInfo->CompanyAddr->City . " " . $companyInfo->CompanyAddr->PostalCode;

        print_r($address);
        return $address;
    }

    private function checkIsChild(array $categories, int $childId): bool
    {
        $isChild = false;
        if (!empty($categories)) {
            foreach ($categories as $category) {
                if (!empty($category['category_id']) && $category['category_id'] == $childId)
                    return true;
                if (!empty($category['categories'])) {
                    $isChild = $this->checkIsChild($category['categories'], $childId);
                }
            }
        }
        return $isChild;
    }

    function is_valid_email_address($email){

        $qtext = '[^\\x0d\\x22\\x5c\\x80-\\xff]';

        $dtext = '[^\\x0d\\x5b-\\x5d\\x80-\\xff]';

        $atom = '[^\\x00-\\x20\\x22\\x28\\x29\\x2c\\x2e\\x3a-\\x3c'.
            '\\x3e\\x40\\x5b-\\x5d\\x7f-\\xff]+';

        $quoted_pair = '\\x5c[\\x00-\\x7f]';

        $domain_literal = "\\x5b($dtext|$quoted_pair)*\\x5d";

        $quoted_string = "\\x22($qtext|$quoted_pair)*\\x22";

        $domain_ref = $atom;

        $sub_domain = "($domain_ref|$domain_literal)";

        $word = "($atom|$quoted_string)";

        $domain = "$sub_domain(\\x2e$sub_domain)*";

        $local_part = "$word(\\x2e$word)*";

        $addr_spec = "$local_part\\x40$domain";

        return preg_match("!^$addr_spec$!", $email) ? 1 : 0;
    }

    public function test()
    {
        debug2($this->invoiceactions->send_pdf_to_email());die;
        $invoices = \application\modules\invoices\models\Invoice::where('invoice_qb_id', null)
            ->with(['estimate' => function($query) {
                $query->select(Estimate::LIGHT_FIELDS);
                $query->with(['estimates_service.service'])
                        ->withTotals([], 'invoices.invoice_qb_id is null');
            }])
            ->get()->toArray();
        debug2($invoices);
        die;
        $test = [
            'test' => 1
        ];
        debug2($test['te']); die;
        $wo = Workorder::whereDoesntHave('invoices')->where('wo_status', 0)->get()->pluck('estimate_id')->toArray();
//        $wo = Workorder::whereDoesntHave('invoices')->where('wo_status', 0)->update(['wo_status'=>]);
//        EstimatesService::whereIn('estimate_id', $wo)->update('service_status', 0);
        debug2($wo);
        die;
//        $test = Client::whereDoesntHave('contacts')->get()->toArray();
//        debug2($test);
//        die;
//        $test = ['test1', 'test2', 'test3'];
//        unset($test[0]);
//        sort($test);
//        debug2($test); die;
//        $test = '385-405-8329, 801-295-2101';
//        $test = '+1 (801) 815-8437';
//        $test = str_replace('+1', "", $test);
//        $phones = explode(',', $test);
//        $phone = preg_replace('/[^0-9]/', '', $phones[0]);
//        debug2($phone); die;
        $file = bucket_read_file('uploads/import_files/export_client.csv');
        file_put_contents(storage_path('file.csv'), $file);

        if (($open = fopen(storage_path('file.csv'), "r")) !== FALSE)
        {

            $key = 0;

            while (($data = fgetcsv($open, 1000, ",")) !== FALSE)
            {
//                $array[] = $data;
//                continue;
                $clientContacts = [];
                $key++;
//                debug2($key);
                if($key == 1){
                    $clientName = array_search('client_name', $data);
                    $address = array_search('client_address', $data);
                    $city = array_search('client_city', $data);
                    $state = array_search('client_state', $data);
                    $zip = array_search('client_zip', $data);
                    $country = array_search('client_country', $data);
                    $lng = array_search('client_lng', $data);
                    $lat = array_search('client_lat', $data);
                    $emails = array_search('emails', $data);
                    $phones = array_search('phone', $data);
                    $mobiles = array_search('mobile', $data);
                    $date = array_search('last_update', $data);

                } else{
                    $oldClient = Client::where('client_name', $data[$clientName])->where('client_id', '>', 100244)->get()->first();
                    if(!empty($oldClient) && empty($oldClient->client_address) && !empty($data[$address])) {
//                        debug2($oldClient->client_id . ' => ' .$data[$address]);
                        $clientArr = [
                            'client_address' => $data[$address],
                            'client_city' => $data[$city],
                            'client_state' => $data[$state],
                            'client_zip' => $data[$zip],
                            'client_country' => $data[$country],
                            'client_lng' => $data[$lng],
                            'client_lat' => $data[$lat]
                        ];
                        $oldClient->update($clientArr);
                    }
                    continue;
//                    debug2($data); die;
                    $createDate = Carbon::create($data[$date]);
                    $clientArr = [
                        'client_name' => $data[$clientName],
                        'client_address' => $data[$address],
                        'client_city' => $data[$city],
                        'client_state' => $data[$state],
                        'client_zip' => $data[$zip],
                        'client_country' => $data[$country],
                        'client_lng' => $data[$lng],
                        'client_lat' => $data[$lat],
                        'client_date_created' => $createDate->format('Y-m-d'),
                        'client_type' => 1
                    ];
                    $client = Client::create($clientArr);
                    if(empty($client))
                        continue;
//                    $test = '385-405-8329, 801-295-2101';
//                    $test = '+1 (801) 815-8437';
//                    $test = str_replace('+1', "", $test);
//                    $phone = preg_replace('/[^0-9]/', '', $phones[0]);
                    $phonesArr = !empty($data[$phones]) ? explode(',', $data[$phones]) : [];
                    $mobilesArr = !empty($data[$mobiles]) ? explode(',', $data[$mobiles]) : [];
                    $emailsArr = !empty($data[$emails]) ? explode(',', $data[$emails]) : [];

                    $print = 1;
                    $clientContacts = [];
                    if(!empty($mobilesArr)){
                        $title = 'Mobile';
                        foreach ($mobilesArr as $keyMob => $mob) {
                            $mob = str_replace('+1', "", $mob);
                            $email = '';
                            if(!empty($emailsArr)) {
                                $email = $emailsArr[0];
                                unset($emailsArr[0]);
                                sort($emailsArr);
                            }
                            $clientContacts[] = [
                                'cc_title' => $keyMob > 0 ? $title . ' #' . ($keyMob + 1) : $title,
                                'cc_name' => $data[$clientName],
                                'cc_phone' => preg_replace('/[^0-9]/', '', $mob),
                                'cc_email' => trim(mb_strtolower($email)),
                                'cc_print' => $print,
                                'cc_client_id' => $client->client_id
                            ];
//                            $array[] = $clientContacts;
                            if($print == 1)
                                $print = 0;
                        }
                    }
                    if(!empty($phonesArr)) {
                        $title = 'Phone';
                        foreach ($phonesArr as $keyPhone => $phone) {
                            if(!empty($emailsArr)) {
                                $email = $emailsArr[0];
                                unset($emailsArr[0]);
                                sort($emailsArr);
                            }
                            $phone = str_replace('+1', "", $phone);
                            $clientContacts[] = [
                                'cc_title' => $keyPhone > 0 ? $title . ' #' . ($keyPhone + 1) : $title,
                                'cc_name' => $data[$clientName],
                                'cc_phone' => preg_replace('/[^0-9]/', '', $phone),
                                'cc_email' => trim(mb_strtolower($email)),
                                'cc_print' => $print,
                                'cc_client_id' => $client->client_id
                            ];
//                            $array[] = $clientContacts;
                            if($print == 1)
                                $print = 0;
                        }
                    }
                    if(!empty($emailsArr)){
                        $title = 'Email';
                        foreach ($emailsArr as $keyEmail => $emailVal){
                            $clientContacts[] = [
                                'cc_title' => $keyEmail > 0 ? $title . ' #' . ($keyEmail + 1) : $title,
                                'cc_name' => $data[$clientName],
                                'cc_phone' => '',
                                'cc_email' => trim(mb_strtolower($emailVal)),
                                'cc_print' => $print,
                                'cc_client_id' => $client->client_id
                            ];
                            $array[] = $clientContacts;
                            if($print == 1)
                                $print = 0;
                        }
                    }

                    if(empty($clientContacts)){
                        $clientContacts[] = [
                            'cc_title' => 'Contact #1',
                            'cc_name' => $data[$clientName],
                            'cc_print' => 1,
                            'cc_client_id' => $client->client_id
                        ];
                    }

                    ClientsContact::insert($clientContacts);
//                    $array[] = $clientArr;
                }


//                $array[] = $data;
            }

            fclose($open);
        }
//        debug2($array);
        die;



//        ini_set('max_execution_time', 0);
//                $address = "Springville";
//        $data = $this->googlemaps->get_lat_long_from_address($address, 0, true);

//        debug2($data);
//        die;
        $file = bucket_read_file('uploads/import_files/importClients.csv');
//        $file = bucket_read_file('uploads/import_files/export_client.csv');
        file_put_contents(storage_path('file.csv'), $file);
        $result[] = [
            'client_name',
            'client_address',
            'client_city',
            'client_state',
            'client_zip',
            'client_country',
            'client_lng',
            'client_lat',
            'emails',
            'phone',
            'mobile',
            'last_update'
        ];
        if (($open = fopen(storage_path('file.csv'), "r")) !== FALSE)
        {

            $key = 0;

            while (($data = fgetcsv($open, 1000, ",")) !== FALSE)
            {
//                $array[] = $data;
//                continue;

                $key++;
//                debug2($key);
                if($key == 1){
                    $clientName = array_search('BillingName', $data);
                    $address = array_search('BillingAddress', $data);
                    $shipAddress = array_search('ShippingAddress', $data);
                    $email = array_search('Email', $data);
                    $phone = array_search('Phone', $data);
                    $mobile = array_search('Mobile', $data);
                    $date = array_search('LastUpdated', $data);
//                    debug2($clientName); debug2($mobile); die;
                } elseif($key == 50){
//                    debug2($result);
//                    break;
                } else {
                    if(empty($data) || empty($data[$clientName])) {
                        debug2($key);
                        debug2($data);
                        continue;
                    }

                    $geocode = null;
                    if(!empty($data[$address]))
                        $geocode = $this->googlemaps->get_lat_long_from_address($data[$address], 0, true);
                    elseif(!empty($data[$shipAddress]))
                        $geocode = $this->googlemaps->get_lat_long_from_address($data[$shipAddress],  0, true);

                    $clientAddress = '';
                    $clientCity= '';
                    $clientState = '';
                    $clientZip = '';
                    $clientCountry = '';
                    $clientLng= '';
                    $clientLat = '';

                    if(!empty($geocode) && is_object($geocode) && $geocode->status == "OK"){
//                        $formattedAddress = $geocode->results[0]->formatted_address;
                        $addressComponents = $geocode->results[0]->address_components;
//                        $addressArr = explode(',', $formattedAddress);
//                        $stateCodeArr = explode(' ', trim($addressArr[2]));
                        $street_number = '';
                        $street = '';
                        $city = '';
                        $state = '';
                        $zip = '';
                        $country = '';
                        foreach ($addressComponents as $component){
                            if($component->types[0] == 'street_number')
                                $street_number = $component->short_name;
                            elseif($component->types[0] == 'route')
                                $street = $component->short_name;
                            elseif($component->types[0] == 'locality')
                                $city = $component->short_name;
                            elseif($component->types[0] == 'administrative_area_level_1')
                                $state = $component->short_name;
                            elseif($component->types[0] == 'postal_code')
                                $zip = $component->short_name;
                            elseif($component->types[0] == 'country')
                                $country = $component->short_name;
                        }
                        $clientAddress = $street_number. ' ' . $street;
                        $clientCity = $city;
                        $clientState = $state;
                        $clientZip = $zip;
                        $clientCountry = $country;
                        $clientLat = $geocode->results[0]->geometry->location->lat;
                        $clientLng = $geocode->results[0]->geometry->location->lng;
                    }


                    $result[] = [
                        trim($data[$clientName]),
                        $clientAddress,
                        $clientCity,
                        $clientState,
                        $clientZip,
                        $clientCountry,
                        $clientLng,
                        $clientLat,
                        $data[$email],
                        $data[$phone],
                        $data[$mobile],
                        $data[$date]
                    ];
                }


//                $array[] = $data;
            }

            fclose($open);
        }
//debug2($array); die;
        if(!empty($result)){
            $path = '/tmp/';
            $fileName = 'export_client.csv';
            $file = fopen($path.$fileName, 'w+');
            foreach ($result as $fields) {
                if(is_array($fields))
                    fputcsv($file, $fields, ",");
            }
            bucket_write_file('uploads/import_files/' . $fileName , $file);
            fclose($file);

//            $this->successResponse(['link' => "uploads/qb/export/" . $fileName, 'name' => $fileName], "uploads/qb/export/" . $fileName);
//            return;
        }

//        echo "<pre>";
//        //To display array data
//        var_dump($array);
//        echo "</pre>";

        die;
//        $address = "50 W 725 N Lidon";
//        $data = $this->googlemaps->get_lat_long_from_address($address, 0, true);
//        if($data->status == "OK"){
//            $formattedAddress = $data->results[0]->formatted_address;
//            $addressArr = explode(',', $formattedAddress);
//            $stateCodeArr = explode(' ', trim($addressArr[2]));
//            $street = $addressArr[0];
//            $city = $addressArr[1];
//            $state = $stateCodeArr[0];
//            $zip = $stateCodeArr[1];
//            $country = $addressArr[3];
//        }
//        debug2($street);
//        debug2($city);
//        debug2($state);
//        debug2($zip);
//        debug2($country);
//        die;



        $file = bucket_read_file('uploads/import_files/importClients.csv');

        $lines = explode(PHP_EOL, $file);
        $header = array_shift($lines);

        $header = explode(",", $header);

        $clientName = array_search('BillingName', $header);
        $address = array_search('BillingAddress', $header);
        $shipAddress = array_search('ShippingAddress', $header);
        $email = array_search('Email', $header);
        $phone = array_search('Phone', $header);
        $mobile = array_search('Mobile', $header);
        $date = array_search('LastUpdated', $header);

//        debug2($lines);
        $result[] = [
            'client_name',
            'client_address',
            'client_city',
            'client_state',
            'client_zip',
            'client_country',
            'client_lng',
            'client_lat',
            'emails',
            'phone',
            'mobile',
            'last_update'
        ];
        $key = 0;
        foreach ($lines as $line){
            $key++;
            $row = explode(',', $line);

            if($key == 7){
//                debug2($result);
                break;
            }
            debug2($line);
            continue;

            if(empty($row) || !isset($row[$clientName]))
                continue;

            $data = null;
            if(!empty($row[$address]))
                $data = $this->googlemaps->get_lat_long_from_address($row[$address], 0, true);
            elseif(!empty($row[$shipAddress]))
                $data = $this->googlemaps->get_lat_long_from_address($row[$shipAddress],  0, true);

            $clientAddress = '';
            $clientCity= '';
            $clientState = '';
            $clientZip = '';
            $clientCountry = '';
            $clientLng= '';
            $clientLat = '';

//            if(!empty($data) && is_object($data) && $data->status == "OK"){
//                $formattedAddress = $data->results[0]->formatted_address;
//                $addressArr = explode(',', $formattedAddress);
//                $stateCodeArr = explode(' ', trim($addressArr[2]));
//                $clientAddress = $addressArr[0];
//                $clientCity = $addressArr[1];
//                $clientState = $stateCodeArr[0];
//                $clientZip = $stateCodeArr[1];
//                $clientCountry = $addressArr[3];
//                $clientLat = $data->results[0]->geometry->location->lat;
//                $clientLng = $data->results[0]->geometry->location->lng;
//            }


            $result[] = [
                trim($row[$clientName]),
                $clientAddress,
                $clientCity,
                $clientState,
                $clientZip,
                $clientCountry,
                $clientLng,
                $clientLat,
                $row[$email],
                $row[$phone],
                $row[$mobile],
                $row[$date]
            ];
            if($key == 7){
//                debug2($result);
                break;
            }
        }

        if(!empty($result)){
            $path = '/tmp/';
            $fileName = 'export_client.csv';
            $file = fopen($path.$fileName, 'w+');
            foreach ($result as $fields) {
                if(is_array($fields))
                    fputcsv($file, $fields, "\t");
            }
            bucket_write_file('uploads/import_files/' . $fileName , $file);
            fclose($file);

//            $this->successResponse(['link' => "uploads/qb/export/" . $fileName, 'name' => $fileName], "uploads/qb/export/" . $fileName);
//            return;
        }

        debug2($result);
        die;




        $address = "2985 n 700 e , lehi
(801) 372-1801";
        $data = $this->googlemaps->get_lat_long_from_address($address);
        if($data->status == "OK"){
            $formattedAddress = $data->results[0]->formatted_address;
            $addressArr = explode(',', $formattedAddress);
            $stateCodeArr = explode(' ', trim($addressArr[2]));
            $street = $addressArr[0];
            $city = $addressArr[1];
            $state = $stateCodeArr[0];
            $zip = $stateCodeArr[1];
            $country = $addressArr[3];
        }
        debug2($street);
        debug2($city);
        debug2($state);
        debug2($zip);
        debug2($country);
        die;
        $estimate = Estimate::where('estimate_id', 51763)->get()->first();
//        $estimate = Estimate::where('estimate_id', 51683)->get()->first();
//        $estimate = $this->mdl_estimates->get_total_for_estimate_by(['estimates.estimate_id' => 51683]);
//        $estimate = $this->mdl_estimates->estimate_sum_and_hst( 51683);
//        $estimate = $this->mdl_estimates->get_total_estimate_balance( 51683);

        debug2(is_object($estimate));
die;
        pushJob('quickbooks/other/importfromshadylanefile', '1');
        die;
        $companyName = '1';
        debug2($companyName ?: 'test');
        die;
        $phone = "NULL";
        $test = preg_replace('/[^0-9]/', '', $phone);
        debug2($test);
        die;
        debug2(0 === '0'); die;
        $test = '905-683-8329';
        $test = explode(' ', $test)[0];
        debug2($test);
        die;
//        $leadStatus = $dateCreate = new DateTime('30.01.2017');
//        debug2($dateCreate->format('Y-m-d H:i:s'));die;
//        $row = 1;
        $file = bucket_read_file('uploads/import_files/import.csv');
//        file_put_contents(storage_path('file.csv'), $file);

//        $file = file_get_contents(storage_path('file.csv'));


        $lines = explode(PHP_EOL, $file);
        $header = array_shift($lines);

        $header = explode("\t", $header);
//        debug2($header);


        $firstKey = array_search('First', $header);
        $lastKey = array_search('Last', $header);
        $clientAddressKey = array_search('Address', $header);
        $clientCityKey = array_search('City', $header);
        $clientStateKey = array_search('Region', $header);
        $clientZipKey = array_search('PostalCode', $header);
        $clientQbIdKey = array_search('CustNum', $header);

        $phoneKey1 = array_search('Phone1', $header);
        $phoneKey2 = array_search('Phone2', $header);
        $phoneKey3 = array_search('Phone3', $header);
        $phoneKey4 = array_search('Phone4', $header);
        $emailKey = array_search('Email', $header);
        $emailKey2 = array_search('Email2', $header);

        $serviceNameKey = array_search('TaskName', $header);

        $leadAddressKey = array_search('JobSiteAddress', $header);
        $leadCityKey = array_search('JobSiteCity', $header);
        $leadStateKey = array_search('JobSiteState', $header);
        $leadZipKey = array_search('JobSiteZipCode', $header);

        $leadStatus = LeadStatus::where(['lead_status_estimated' => 1])->get()->first();
        $dateKey = array_search('BidDate', $header);
        $statusKey = array_search('JobStatus', $header);
        $taxedKey = array_search('Taxed', $header);
        $priceKey = array_search('Price', $header);
        $discountKey = array_search('JobDiscount', $header);

        $woDateKey = array_search('DateStarted', $header);
        $invoiceNumKey = array_search('Invoice', $header);
        $invoiceDueKey = array_search('InvoiceDueDate', $header);
        $invoiceDateKey = array_search('InvoiceDate', $header);
        $invoiceStatusKey = array_search('PaidStatus', $header);
        $paymentDateKey = array_search('PaidDate', $header);
        $totalAmountKey = array_search('Total', $header);

        $estimateServiceDescriptionKey = array_search('SpecialInstruction', $header);
        $estimateQbKey = array_search('JobNum', $header);

        $key = 1;
        foreach($lines as $line) {
            debug2($key);

            $key++;
            if($key < 111263)
                continue;

            $row = explode("\t", $line);
            $client = null;
            // client
            if($clientQbIdKey !== false && isset($row[$clientQbIdKey])) {
                $clientQbId = $row[$clientQbIdKey];
                $client = Client::where('client_qb_id', $clientQbId)->get()->first();

                if(empty($client)) {
                    $clientArr = [];
                    $clientArr['client_qb_id'] = $clientQbId;
                    $clientArr['client_type'] = 1;
                    if ($firstKey !== false && isset($row[$firstKey]))
                        $clientArr['client_name'] = $row[$firstKey] . ' ' . $row[$lastKey];
                    if ($clientAddressKey !== false && isset($row[$clientAddressKey]))
                        $clientArr['client_address'] = $row[$clientAddressKey];
                    if ($clientCityKey !== false && isset($row[$clientCityKey]))
                        $clientArr['client_city'] = $row[$clientCityKey];
                    if ($clientStateKey !== false && isset($row[$clientStateKey]))
                        $clientArr['client_state'] = $row[$clientStateKey];
                    if ($clientZipKey !== false && isset($row[$clientZipKey]))
                        $clientArr['client_zip'] = $row[$clientZipKey];

                    if(empty($clientArr['client_name']))
                        $clientArr['client_name'] = $clientArr['client_address'] . ', ' . $clientArr['client_city'] . ', ' . $clientArr['client_state'] . ', ' . $clientArr['client_zip'];

                    $client = Client::create($clientArr);

                    if(!empty($client)){
                        $clientContact = [
                            'cc_title' => 'Contact #1',
                            'cc_name' => $clientArr['client_name'],
                            'cc_phone' => $row[$phoneKey1] ?: $row[$phoneKey2] ?: $row[$phoneKey3] ?: $row[$phoneKey4],
                            'cc_email' => $row[$emailKey] ?: $row[$emailKey2],
                            'cc_print' => 1,
                            'cc_client_id' => $client->client_id
                        ];
                        $clientContact['cc_phone'] = explode(' ', $clientContact['cc_phone'])[0];
                        if($clientContact['cc_phone'] == "NULL")
                            $clientContact['cc_phone'] = NULL;

                        ClientsContact::insert($clientContact);
                    }
                }

            }

            if($serviceNameKey !== false && isset($row[$serviceNameKey])){
                $serviceName = $row[$serviceNameKey];
                if(empty($serviceName) || $serviceName == 'NULL')
                    $serviceName = 'Service';
                $service = Service::where('service_name', $serviceName)->get()->first();
                if(empty($service)){
                    $service = Service::create(['service_name' => $serviceName]);
                }
            }


            if(!empty($client)) {
                $checkEstimateInDB = false;
                $estimate = Estimate::where(['estimate_qb_id' => $row[$estimateQbKey]])->get()->first();
                //lead
                $dateCreate = new DateTime($row[$dateKey]);
                if(empty($estimate)) {
                    $lead = [
                        'client_id' => $client->client_id,
                        'lead_address' => $row[$leadAddressKey],
                        'lead_city' => $row[$leadCityKey],
                        'lead_state' => $row[$leadStateKey],
                        'lead_zip' => $row[$leadZipKey],
                        'lead_status' => 'Estimated',
                        'lead_reffered_by' => 'Quickbooks desktop',
                        'lead_status_id' => $leadStatus->lead_status_id,
                        'lead_date_created' => $dateCreate->format('Y-m-d H:i:s')
                    ];
                    $lead = Lead::create($lead);
                    $leadNO = getLeadNO($lead->lead_id);
                    Lead::where('lead_id', $lead->lead_id)->update($leadNO);
                } else {
                    $lead = Lead::find($estimate->lead_id);
                    $checkEstimateInDB = true;
                }

                if(!empty($lead)) {
                    //estimate

                    $status = 1;
                    $serviceStatus = 0;
                    if ($row[$statusKey] !== false) {
                        if ($row[$statusKey] == 'Completed' || $row[$statusKey] == 'Work Order') {
                            $status = 6;
                        }
                        if ($row[$statusKey] == 'Declined' || $row[$statusKey] == 'Cancelled') {
                            $status = 4;
                            $serviceStatus = 1;
                        }
                        if ($row[$statusKey] == 'Hold')
                            $status = 8;
                    }

                    if(!$checkEstimateInDB) {
                        $estimateNo = str_pad($lead->lead_id, 5, '0', STR_PAD_LEFT);
                        $estimateNo .= "-E";

                        $estimate = [
                            'estimate_no' => $estimateNo,
                            'lead_id' => $lead->lead_id,
                            'estimate_brand_id' => default_brand(),
                            'client_id' => $client->client_id,
                            'date_created' => $dateCreate->getTimestamp(),
                            'status' => $status,
                            'user_id' => 0,
                            'estimate_qb_id' => $row[$estimateQbKey],
                            'estimate_tax_name' => 'TAX',
                            'estimate_tax_rate' => '1.13',
                            'estimate_tax_value' => '13',
                        ];
                        $estimate = Estimate::create($estimate);
                    }

                    if(!empty($estimate)){
                        //estimate service
                        if(!empty($row[$invoiceNumKey]) && $row[$invoiceNumKey] != NULL && $row[$invoiceNumKey] != 'NULL')
                            $serviceStatus = 2;

                        $record = [
                            'service_id' => $service->service_id,
                            'estimate_id' => $estimate->estimate_id,
                            'service_status' => $serviceStatus,
                            'service_description' => strip_tags($row[$estimateServiceDescriptionKey]),
                            'service_price' => $row[$priceKey],
                            'non_taxable' => $row[$taxedKey] == 0 ? 1 : 0,
                            'quantity' => 1
                        ];
                        EstimatesService::insert($record);

                        if($checkEstimateInDB)
                            continue;

                        if($row[$discountKey] > 0){
                            $discount = [
                                'discount_amount' => $row[$discountKey] * 100,
                                'estimate_id' => $estimate->estimate_id,
                                'discount_percents' => 1
                            ];
                            $this->mdl_clients->insert_discount($discount);
                        }

                        if($status == 6){
                            $workOrderNo = getNO($lead->lead_id, 'W');
                            if(!empty($row[$woDateKey]) && $row[$woDateKey] != "NULL"){
                                $dateCreate = new DateTime($row[$woDateKey]);
                                $dateCreate = $dateCreate->format('Y-m-d');
                            }
                            elseif(!empty($row[$dateKey]) && $row[$dateKey] != "NULL"){
                                $dateCreate = new DateTime($row[$dateKey]);
                                $dateCreate = $dateCreate->format('Y-m-d');
                            }
                            if(!empty($row[$invoiceNumKey]) && $row[$invoiceNumKey] != NULL && $row[$invoiceNumKey] != 'NULL')
                                $statusId = $this->mdl_workorders->getFinishedStatusId();
                            else
                                $statusId = $this->mdl_workorders->getDefaultStatusId();

                            $workOrder = [
                                'workorder_no' => $workOrderNo,
                                'estimate_id' => $estimate->estimate_id,
                                'client_id' => $client->client_id,
                                'wo_status' => $statusId,
                                'date_created' => isset($dateCreate) ? $dateCreate : new DateTime()
                            ];
                            $workOrderId = $this->mdl_workorders->insert_workorders($workOrder);

                            if(!empty($workOrderId) && !empty($row[$invoiceNumKey]) && $row[$invoiceNumKey] != 'NULL'){
                                $invoiceNO = getNO( $lead->lead_id, 'I');
                                if(!empty($row[$invoiceDueKey])){
                                    $overdue = new DateTime($row[$invoiceDueKey]);
                                    $overdue = $overdue->format('Y-m-d');
                                }
                                if(!empty($row[$invoiceDateKey])){
                                    $dateCreate = new DateTime($row[$invoiceDateKey]);
                                    $dateCreate = $dateCreate->format('Y-m-d');
                                }
                                $status = 1;
                                if(!empty($row[$invoiceStatusKey]) && $row[$invoiceStatusKey] == 'Paid'){
                                    $status = 4;
                                }
                                $invoice = [
                                    'invoice_no' => $invoiceNO,
                                    'workorder_id' => $workOrderId,
                                    'estimate_id' => $estimate->estimate_id,
                                    'client_id' => $client->client_id,
                                    'in_status' => $status,
                                    'date_created' => isset($dateCreate) ? $dateCreate : '',
                                    'overdue_date' => isset($overdue) ? $overdue : '',
                                    'invoice_notes' => '',
                                    'invoice_qb_id' => $row[$invoiceNumKey]
                                ];
                                $this->mdl_invoices->insert_invoice($invoice);

                                if($status == 4) {
                                    if(!empty($row[$paymentDateKey])){
                                        $dateCreate = new DateTime($row[$paymentDateKey]);
                                    }

                                    $paymentToDB = [
                                        'estimate_id' => $estimate->estimate_id,
                                        'payment_method_int' => 1,
                                        'payment_date' => $dateCreate->getTimestamp(),
                                        'payment_amount' => $row[$totalAmountKey],
                                        'payment_checked' => 1,
                                        'payment_type' => 'invoice',
                                        'payment_qb_id' => 0
                                    ];
                                    $this->mdl_clients->insert_payment($paymentToDB);
                                }
                            }
                        }
                    }
                }
            }

        }



        die;
//        file_put_contents(storage_path('file.csv'), $fileFromS3);
        $headers = [];
        if (($handle = fopen(storage_path('file.csv'), "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
//                if($row == 1)
//                    $headers =  explode("\t", $data[$row]);
                debug2( count($data));
//                die;
//                $num = count($data);
//                echo "<p> $num полей в строке $row: <br /></p>\n";
//                $row++;
//                for ($c=0; $c < $num; $c++) {
//                    echo $data[$c] . "<br />\n";
//                }
            }
            fclose($handle);
        }

        die;
        $sections = $this->mdl_schedule->get_teams(array('team_id' => 9688));
        debug2($sections);die;
        debug2('test'); die;
        $file = bucket_read_file('uploads/import_files/import.xlsx');
        debug2('1');
        file_put_contents(storage_path('file.xlsx'), $file);
        debug2('2');
//        $test = file_get_contents(storage_path('file.xlsx'));
//        debug2($test); die;
//        debug2(storage_path('uploads/import_files/test.xlsx')); die;
//        $test = file_get_contents('/home/ivan/test.xlsx');
//        debug2($test);
//        die;

//        $file = file_get_contents('/home/ivan/test.xlsx');
//        $file = bucket_read_file('uploads/import_files/import.xlsx');
//        $file = bucket_read_file('uploads/import_files/test.xlsx');
//        file_put_contents(storage_path('file.xlsx'), $file);
//        debug2(file_get_contents(storage_path('file.xlsx')));
//        die;
//        $file = bucket_read_file('uploads/import_files/test.xlsx');
//        Storage::disk('local')->put('file.xlsx', $file);
//        debug2(file_get_contents(storage_path('file.xlsx'))); die;
//        debug2($file); die;
//        $inputFileName = '/home/ivan/test.xlsx';
//Read your Excel workbook
        $file = storage_path('file.xlsx');
//        $file = '/home/ivan/test.xlsx';
//        debug2($file);
//        unlink($file);
        try{
//            $inputFileType  =   PHPExcel_IOFactory::identify($file);
//            $objReader      =   PHPExcel_IOFactory::createReader($file);
            $objReader      =   PHPExcel_IOFactory::createReaderForFile($file);
            $objPHPExcel    =   $objReader->load($file);
        }catch(Exception $e){
            die('Error loading file : '.$e->getMessage());
        }
        debug2('3'); die;
//  Get worksheet dimensions
        $sheet = $objPHPExcel->getActiveSheet();

//        $array = $sheet->toArray();

//        debug2($sheet);
//        debug2(array_slice($array, 0, 5));

        die;

        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();

//  Loop through each row of the worksheet in turn
        for ($row = 1; $row <= $highestRow; $row++){
            //  Read a row of data into an array
            $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row,
                NULL,
                TRUE,
                FALSE);
            //  Use foreach loop and insert data into Query
            debug2($rowData);
            if($row == 5)
                return;
        }
        die;
//        $fileCsv = bucket_read_file('uploads/import_files/import.csv');
        $fileXlsx = bucket_read_file('uploads/import_files/import.xlsx');
        $objPHPExcel =  PHPExcel_IOFactory::load($fileCsv);

        foreach ($objPHPExcel->getWorksheetIterator() as $worksheet) {
            $highestRow = $worksheet->getHighestRow();
            debug2($highestRow);
        }
        die;
        $file = bucket_read_file('uploads/import_files/import.xlsx');
//        $test = $this->phpexcel->getActiveSheet()->toArray(); ;
        $newSheet = new PHPExcel_Worksheet('uploads/import_files/import.xlsx');
//        $test = $this->phpexcel->load('uploads/import_files/import.xlsx');
        debug2($newSheet);
            die;
        $invoiceNumber = 1;
        $dateCreate = new DateTime('2017-05-14');
        debug2($dateCreate->format('Y-m-d') );die;
        $this->load->library('Common/QuickBooks/QBEstimateActions');
        $estimates = $this->qbestimateactions->getAllByIterator(1);
        debug2($estimates);
        die();
        Client::update(['client_country' => ''])->where('client_country', 'Canada');
        $dataService = dataServiceConfigure($this->accessToken);
//        $entities = $dataService->Query("Select * from Preferences");
//        debug2($entities);
        $taxesCodeInQB = query('Customer', $dataService);
        debug2($taxesCodeInQB);
        die;
        $clients = Client::doesnthave('contacts')->get()->toArray();
        $contacts = [];
        if(!empty($clients)){
            foreach ($clients as $client){
                $contacts[] = [
                    'cc_title' => 'Contact #1',
                    'cc_name' => $client['client_name'],
                    'cc_client_id' => $client['client_id'],
                    'cc_print' => 1
                ];
            }
        }
        ClientsContact::insert($contacts);
        die;
        $leads = Lead::where('client_id', 88585)
            ->where(function (Builder $query) {
                $query->where(function (Builder $query) {
                    $query->defaultStatus();
                });
                $query->orWhere(function (Builder $query) {
                    $query->draftStatus();
                });
            })->select(['lead_id', 'lead_no'])->get()->toArray();
        debug2($leads); die;
        $dataService = dataServiceConfigure($this->accessToken);
//        $entities = $dataService->Query("Select * from Preferences");
//        debug2($entities);
        $taxesCodeInQB = query('TaxCode', $dataService);
        debug2($taxesCodeInQB);
        die;
//        debug2($this->estimateactions->getEstimateDraftData(88584, 48424));
//        debug2($this->mdl_leads->get_leads(array('lead_id' => 48427), '')->row());
        $lead =  Lead::where('lead_id', 48428)->with('status')->first();
        debug2($lead->status->lead_status_default);
        die;

        $address = "130 Harvest moon dr, Unionville, ON, L3R 0L7";
        $geocode = $this->googlemaps->get_lat_long_from_address($address);
        debug2($geocode);die;
        pushJob('geocoding/check_coords', 'payload');
        die;
//        debug2( Client::find(18561));die;
//        $invoiceStatuses = InvoiceStatus::where('completed', 1)
//            ->pluck('invoice_status_id')
//            ->toArray();
//        debug2($invoiceStatuses);
//        die;
//        $result = Client::whereHas('leads', function($query){
//            $query->whereClientAddress("leads.lead_address");
//        })->where([['clients.client_lng', '=', null]])->get()->toArray();
//        ->where('client_id', 88585)
//        ['leads.latitude', 'is not', null]
//        $result = Client::with(['estimates', 'leads', 'workorders'])
////            ->select(['clients.client_id, clients.client_address, clients.client_lat, clients.client_lng, leads.lead_id, leads.lead_address, leads.latitude, leads.longitude'])
//            ->select(['clients.client_id'])
//        ->where('client_id', 88585)->get()->toArray();


//        $result = DB::table('clients')
//            ->join('leads', 'clients.client_id', '=', 'leads.client_id')
//            ->where('clients.client_lng', null)
//            ->where('clients.client_lat', null)
//            ->where('clients.client_address', DB::raw('leads.lead_address'))
//            ->where('leads.latitude', '!=', null)
//            ->where('leads.longitude', '!=', null)
//            ->groupBy('clients.client_id')
//            ->update([
//                'clients.client_lng' => DB::raw('leads.longitude'),
//                'clients.client_lat' => DB::raw('leads.latitude')
//            ]);


//        $result = DB::table('leads')
//            ->join('clients', 'clients.client_id', '=', 'leads.client_id')
//            ->where('leads.latitude',null)
//            ->where('leads.longitude',null)
//            ->where('clients.client_address', DB::raw('leads.lead_address'))
//            ->where('clients.client_lng', '!=', null)
//            ->where('clients.client_lat', '!=', null)
//            ->update([
//                'leads.longitude' => DB::raw('clients.client_lng'),
//                'leads.latitude' => DB::raw('clients.client_lat')
//            ]);


        $leadStatuses = LeadStatus::where('lead_status_declined', 1)
            ->orWhere('lead_status_estimated', 1)
            ->orWhere('lead_status_id', 5)
            ->pluck('lead_status_id')
            ->toArray();

        $estimateStatuses = EstimateStatus::where('est_status_declined', 1)
            ->orWhere('est_status_confirmed', 1)
            ->pluck('est_status_id')
            ->toArray();

        $workOrderStatuses = WorkorderStatus::where('is_finished', 1)
            ->pluck('wo_status_id')
            ->toArray();

        $invoiceStatuses = InvoiceStatus::where('completed', 1)
            ->pluck('invoice_status_id')
            ->toArray();

        $result = DB::table('clients')
            ->join('leads', 'clients.client_id', '=', 'leads.client_id')
            ->leftJoin('estimates', 'leads.lead_id', '=', 'estimates.lead_id')
            ->leftJoin('workorders', 'estimates.estimate_id', '=', 'workorders.estimate_id')
            ->leftJoin('invoices', 'estimates.estimate_id', '=', 'invoices.estimate_id')
            ->where(function ($query) use ($leadStatuses, $estimateStatuses, $workOrderStatuses, $invoiceStatuses){
                $query->where(function ($query) use ($leadStatuses){
                    $query->whereNotIn('leads.lead_status_id',  $leadStatuses)
                    ->where('estimates.estimate_id', null);
                })
                    ->orWhere(function ($query) use ($estimateStatuses){
                        $query->whereNotIn('estimates.status',  $estimateStatuses)
                            ->where('workorders.estimate_id', null);
                    })
                    ->orWhere(function ($query) use ($workOrderStatuses){
                        $query->whereNotIn('workorders.wo_status',  $workOrderStatuses)
                            ->where('invoices.estimate_id', null);
                    })
                    ->orWhereNotIn('invoices.in_status', $invoiceStatuses);
            })
            ->where(function ($query){
                $query->where('clients.client_lat',  '0')
                    ->orWhere('clients.client_lng',  '0')
                    ->orWhere('clients.client_lng',  null)
                    ->orWhere('clients.client_lng',  null)
                    ->orWhere('leads.latitude',  '0')
                    ->orWhere('leads.longitude',  '0')
                    ->orWhere('leads.latitude',  null)
                    ->orWhere('leads.longitude',  null);
            })
            ->get([
                'clients.client_id',
                'clients.client_lat',
                'clients.client_lng',
                'clients.client_address',
                'clients.client_city',
                'clients.client_state',
                'clients.client_zip',
                'clients.client_country',
                'leads.lead_id',
                'leads.longitude',
                'leads.latitude',
                'leads.lead_address',
                'leads.lead_city',
                'leads.lead_state',
                'leads.lead_zip',
                'leads.lead_country'
            ])
            ->toArray();

        if(!empty($result) && is_array($result)){
            foreach ($result as $key => $val){
                if((empty($val->client_lng) || empty($val->client_lat)) && !empty($val->client_address) && !empty($val->client_id)){
                    $address = $val->client_address;
                    if(!empty($val->client_city))
                        $address .= ', ' . $val->client_city;
                    if(!empty($val->client_state))
                        $address .= ', ' . $val->client_state;
                    if(!empty($val->client_zip))
                        $address .= ', ' . $val->client_zip;
                    if(!empty($val->client_country))
                        $address .= ', ' . $val->client_country;

                    $geocode = $this->googlemaps->get_lat_long_from_address($address);

                    if(!empty($geocode) && is_array($geocode) && !empty($geocode[0]) && !empty($geocode[1])) {
                        $client = Client::find($val->client_id);
                        if(!empty($client)) {
                            $client->client_lat = $geocode[0];
                            $client->client_lng = $geocode[1];
                            $client->save();
                        }
//                            ->update([
//                                'client_lat' => $geocode[0],
//                                'client_lng' => $geocode[1]
//                            ]);
                    }

                }
                if((empty($val->longitude) || empty($val->latitude)) && !empty($val->lead_address) && !empty($val->lead_id)){
                    if($val->lead_address != $val->client_address){
                        $address = $val->lead_address;
                        if(!empty($val->lead_city))
                            $address .= ', ' . $val->lead_city;
                        if(!empty($val->lead_state))
                            $address .= ', ' . $val->lead_state;
                        if(!empty($val->lead_zip))
                            $address .= ', ' . $val->lead_zip;
                        if(!empty($val->lead_country))
                            $address .= ', ' . $val->lead_country;

                        $geocode = $this->googlemaps->get_lat_long_from_address($address);
                    }
                    if(!empty($geocode) && is_array($geocode) && !empty($geocode[0]) && !empty($geocode[1])) {
                        $lead = Lead::find($val->lead_id);
                        if(!empty($lead)) {
                            $lead->latitude = $geocode[0];
                            $lead->longitude = $geocode[1];
                            $lead->save();
                        }
//                            ->update([
//                                'latitude' => $geocode[0],
//                                'longitude' => $geocode[1]
//                            ]);
                    }
                }
//                debug2($val->client_id);
            }
        }

//        $address = '501 Kingston Rd, Toronto, ON, M4L';
//        $geocode = $this->googlemaps->get_lat_long_from_address($address);
//        debug2($geocode);
//        die;

//        debug2(count($result));
        debug2($result);
        die;
//        $data['files'] = json_decode('["uploads\/clients_files\/1847\/estimates\/04735-E\/pdf_estimate_no_04735-E_scheme.png","uploads\/clients_files\/1847\/estimates\/04735-E\/8279\/estimate_no_04735-E_1.aac","uploads\/clients_files\/1847\/estimates\/04735-E\/8279\/estimate_no_04735-E_2.aac","uploads\/clients_files\/1847\/estimates\/04735-E\/8279\/estimate_no_04735-E_3.mp3","uploads\/clients_files\/1847\/estimates\/04735-E\/8281\/estimate_no_04735-E_1.aac","uploads\/clients_files\/1847\/estimates\/04735-E\/8281\/estimate_no_04735-E_2.mp4","uploads\/clients_files\/1847\/estimates\/04735-E\/8281\/estimate_no_04735-E_3.mp4","uploads\/clients_files\/1847\/estimates\/04735-E\/8281\/estimate_no_04735-E_4.mp4"]');
        $data['files'] = [
            10 => ["uploads\/clients_files\/1847\/estimates\/04735-E\/pdf_estimate_no_04735-E_scheme.png",
            "uploads\/clients_files\/1847\/estimates\/04735-E\/8279\/estimate_no_04735-E_1.aac",
            "uploads\/clients_files\/1847\/estimates\/04735-E\/8279\/estimate_no_04735-E_2.aac"],
            "uploads\/clients_files\/1847\/estimates\/04735-E\/8279\/estimate_no_04735-E_3.mp3",
            "uploads\/clients_files\/1847\/estimates\/04735-E\/8281\/estimate_no_04735-E_1.aac",
            "uploads\/clients_files\/1847\/estimates\/04735-E\/8281\/estimate_no_04735-E_2.mp4",
            30 => [
            "uploads\/clients_files\/1847\/estimates\/04735-E\/8281\/estimate_no_04735-E_3.mp4",
            "uploads\/clients_files\/1847\/estimates\/04735-E\/8281\/estimate_no_04735-E_4.mp4"],
        ];
        $data = $this->estimateactions->removeAudioVideoFiles($data['files']);
//        foreach ($data['files'] as $keyFiles => $file) {
//            if(is_array($file) && !empty($file)){
//                foreach ($file as $key => $val){
//                    if(pathinfo($val, PATHINFO_EXTENSION) == 'pdf') {
//                        $this->mpdf->AddPage('L');
//                        $this->mpdf->Thumbnail(bucket_get_stream($file), 1, 10, 16, 1);
//                    }else{
//                        $type = getMimeType($val);
//                        if(!is_bucket_file($val) || strripos($type, 'audio') !== false || strripos($type, 'video') !== false)
//                            unset($file[$key]);
//                    }
//                }
//                $data['files'][$keyFiles] = $file;
//            }
//            elseif(!is_array($file) && pathinfo($file, PATHINFO_EXTENSION) == 'pdf') {
//                $this->mpdf->AddPage('L');
//                $this->mpdf->Thumbnail(bucket_get_stream($file), 1, 10, 16, 1);
//            } else{
//                $type = getMimeType($file);
//                if(!is_bucket_file($file) || strripos($type, 'audio') !== false || strripos($type, 'video') !== false)
//                    unset($data['files'][$keyFiles]);
//            }
//        }
        debug2($data);
        die;
        $estimate[0] = new stdClass();
        $estimate[0]->estimate_pdf_files = '["uploads\/clients_files\/1847\/estimates\/04735-E\/pdf_estimate_no_04735-E_scheme.png","uploads\/clients_files\/1847\/estimates\/04735-E\/8279\/estimate_no_04735-E_1.aac","uploads\/clients_files\/1847\/estimates\/04735-E\/8279\/estimate_no_04735-E_2.aac","uploads\/clients_files\/1847\/estimates\/04735-E\/8279\/estimate_no_04735-E_3.mp3","uploads\/clients_files\/1847\/estimates\/04735-E\/8281\/estimate_no_04735-E_1.aac","uploads\/clients_files\/1847\/estimates\/04735-E\/8281\/estimate_no_04735-E_2.mp4","uploads\/clients_files\/1847\/estimates\/04735-E\/8281\/estimate_no_04735-E_3.mp4","uploads\/clients_files\/1847\/estimates\/04735-E\/8281\/estimate_no_04735-E_4.mp4"]';
debug2(json_decode($estimate[0]->estimate_pdf_files));
        //        $file = '/tes/ggfdg/test.mp4';
//        $test = pathinfo($file, PATHINFO_EXTENSION);
//        $test = getMimeType($file);
//        debug2(strripos($test, 'video') !== false);
        debug2($this->mdl_estimates_orm->_explodePdfFiles($estimate)[0]);
        die;
        $estimate_data = $this->mdl_estimates_orm->with('mdl_services_orm')->get_full_estimate_data(['estimates.lead_id' => 48374]);
        foreach ($estimate_data[0]->mdl_services_orm as $key => $val){
            debug2($val->tree_inventory->toArray());
        }
        debug2($estimate_data);
        die;
        $clientContact = $this->mdl_clients->get_client_contact(['cc_client_id ' => 38883, 'cc_print' => 1]);
        debug2($clientContact);
        die;
        $dataService = dataServiceConfigure($this->accessToken);
        $invoice = getQBEntityById('Invoice', 382, $dataService);
        debug2($invoice);
    }
    public function import()
    {
        if (!$this->accessToken)
            show_404();

        pushJob('quickbooks/client/importinactiveclients', 'Customer');
        createOrUpdateQbAccessToken($this->accessToken);
    }

    public function export()
    {
        if (!$this->accessToken)
            show_404();
        pushJob('quickbooks/item/syncservicesfromqb', serialize(['action' => 'Sync services from QB']));
        pushJob('quickbooks/client/exportclientsv3', serialize(['module' => 'Customer', 'count' => 0]));
        createOrUpdateQbAccessToken($this->accessToken);
    }

    public function exportV2()
    {
        if (!$this->accessToken)
            show_404();
        $module = $this->input->post('module');
        $driver = getExportDriverClass($module);
        createOrUpdateQbAccessToken($this->accessToken);
        pushJob($driver, serialize(['module' => ucfirst($module)]));
    }

    public function importV2()
    {
        if (!$this->accessToken)
            show_404();
        $module = $this->input->post('module');
        $driver = getImportDriverClass($module);
        createOrUpdateQbAccessToken($this->accessToken);
        pushJob($driver, serialize(['module' => ucfirst($module)]));
    }

    public function endpoint()
    {
        sleep(10);
        $data = file_get_contents("php://input");

        if (!$data)
            show_404();

        $data = json_decode($data);

        if (!$data || !isset($data->eventNotifications[0]->dataChangeEvent->entities))
            show_404();

        foreach ($data->eventNotifications[0]->dataChangeEvent->entities as $entity) {
            if ($entity->operation == 'Create') {
                $check = checkQbIdInDB($entity->name, $entity->id);
                if ($check)
                    continue;
            }
            if($entity->name == 'Item'){
                $dataService = dataServiceConfigure($this->accessToken);
                $item = getQBEntityById('Item', $entity->id, $dataService);
                if(!empty($item)){
                    if($item->Type == 'Category'){
                        $entity->name = 'Category';
                    }
                }
                elseif($entity->operation == 'Delete'){
                    $entity->name = 'Category';
                }
            }
            $jobsName = getDriverNameForJobDB($entity->name);
            pushJob($jobsName, serialize(['module' => $entity->name, 'qbId' => $entity->id, 'operation' => $entity->operation]));
        }
    }

    public function getBadPaymentFromQB()
    {
        $dataService = dataServiceConfigure($this->accessToken);
        $i = 1;
        while (true) {
            $payments = $dataService->FindAll('Payment', $i, 1000);
            $error = checkError($dataService);
            if (!$error) {
                debug("Error or refresh token! -> i = " . $i);
                return FALSE;
            }
            if (!$payments) {
                debug("END!");
                break;
            }
            foreach ($payments as $payment) {
                $i++;
                $paymentsInDB = $this->mdl_clients->get_payments(['payment_qb_id' => $payment->Id]);
                if (!$paymentsInDB)
                    debug($payment);
            }
        }
        deleteLogsInTmp();
    }

    function importDeposit()
    {
        pushJob('quickbooks/invoice/importdepositindb', 'Invoice');
    }

    function updateAllInvoiceIfNeeded(){
        // 74284
        $invoices = $this->mdl_invoices->find_all();
        $dataService = dataServiceConfigure($this->accessToken);
        refreshToken($dataService);
        foreach ($invoices as $invoice){
            if(!empty($invoice->invoice_qb_id)) {
                $invoiceQB = getQBEntityById('Invoice', $invoice->invoice_qb_id, $dataService);
                $invoiceDB = $this->mdl_estimates_orm->get($invoice->estimate_id);
                if(!empty($invoiceQB->TotalAmt) && !empty($invoiceDB->total_with_tax)){
                    if($invoiceQB->TotalAmt != $invoiceDB->total_with_tax){
                        pushJob('quickbooks/invoice/syncinvoiceinqb', serialize(['id' => $invoice->id, 'qbId' => $invoice->invoice_qb_id]));
                        sleep( 1);
                    }
                }
            }
        }
    }

    function updateInvoice(){
        $dataService = dataServiceConfigure($this->accessToken);
        refreshToken($dataService);
        $invoices = $this->mdl_invoices->find_all(['invoice_qb_id' => 0]);
        foreach ($invoices as $invoice)
        {
            $oneQuery = new QueryMessage();
            $oneQuery->sql = "SELECT";
            $oneQuery->entity = "Invoice";
            $invoiceNO = trim((!empty(config_item('prefix')) ? config_item('prefix') : '') . $invoice->invoice_no);
            $oneQuery->whereClause = ["DocNumber = '" . $invoiceNO . "'" ];
            $result = customQuery($oneQuery, $dataService);
            if(!empty($result[0])) {
                $theInvoice = $result[0];
                $invoiceToQB['TxnDate'] = $invoice->date_created;
                $invoiceToQB['DueDate'] = $invoice->overdue_date;
                $updateInvoice = Invoice::update($theInvoice, $invoiceToQB);
                updateRecordInQBFromObject($updateInvoice, $dataService, false, $invoice->id);
                $this->mdl_invoices->update_invoice(['invoice_qb_id' => $theInvoice->Id], ['id' => $invoice->id]);
            }
        }
    }

    function manualSync()
    {
        $id = $this->input->post('id');
        $module = $this->input->post('module');
        $route = $this->input->post('route');
        $qbId = null;
        if ($module == 'item' && !empty($id)) {
            $service = $this->mdl_services->get($id);
            if (!empty($service) && $service->service_qb_id == 0)
                $this->mdl_services->update($id, ['service_qb_id' => null]);
            $qbId = !empty($service) && !empty($service->service_qb_id) ? $service->service_qb_id : null;
        } elseif ($module == 'client' && !empty($id)) {
            $module = 'customer';
            $client = $this->mdl_clients->find_by_id($id);
            if (!empty($client) && $client->client_qb_id == 0)
                $this->mdl_clients->update_client(['client_qb_id' => null], ['client_id' => $id]);
            $qbId = !empty($client) && !empty($client->client_qb_id) ? $client->client_qb_id : null;
        } elseif ($module == 'invoice' && !empty($id)) {
            $invoice = $this->mdl_invoices->find_by_id($id);
            if (!empty($invoice) && $invoice->invoice_qb_id == 0)
                $this->mdl_invoices->update_invoice(['invoice_qb_id' => null], ['id' => $id]);
            $qbId = !empty($invoice) && !empty($invoice->invoice_qb_id) ? $invoice->invoice_qb_id : null;
        } elseif ($module == 'payment' && !empty($id)) {
            $payment = $this->mdl_client_payments->find_by_id($id);
            if (!empty($payment) && $payment->payment_qb_id == 0)
                $this->mdl_client_payments->update_by_cond(['payment_id' => $id], ['payment_qb_id' => null]);
            $qbId = !empty($payment) && !empty($payment->payment_qb_id) ? $payment->payment_qb_id : null;
        } elseif ($module == 'class' && !empty($id)) {
            $category = Category::where('category_id', '=', $id)->first()->toArray();
            if (!empty($category) && $category['category_qb_id'] == 0)
                Category::where('category_id', '=', $id)->update(['category_qb_id' => null]);
            $qbId = !empty($category['category_qb_id']) ? $category['category_qb_id'] : null;
        }


        if ($route == 'push')
            $driver = getPushManualSyncDriver($module);
        elseif ($route == 'pull')
            $driver = getPullManualSyncDriver($module);
        if (!empty($driver) && !empty($id)) {
            if ($route == 'push')
                pushJob($driver, serialize(['id' => $id, 'qbId' => $qbId]));
            else
                pushJob($driver, serialize(['module' => $module, 'id' => $id, 'qbId' => $qbId, 'operation' => '']));
            return $this->response(['id' => $id, 'module' => $driver, 'route' => $route], 200);
        }
        return $this->response(['id' => $id, 'module' => $driver, 'route' => $route], 400);
    }

    function getLogData()
    {
        $id = $this->input->get('id');
        $module = $this->input->get('module');
        $qb_logs = [];
        if ($module == 'item') {
            $qb_logs = QbLogsModel::where(['log_module_id' => QbLogsModel::MODULE_ITEM, 'log_entity_id' => $id])->orderBy('log_created_at', 'desc')->get();
        } elseif ($module == 'client') {
            $qb_logs = QbLogsModel::where(['log_module_id' => QbLogsModel::MODULE_CLIENT, 'log_entity_id' => $id])->orderBy('log_created_at', 'desc')->get();
        } elseif ($module == 'invoice') {
            $qb_logs = QbLogsModel::where(['log_module_id' => QbLogsModel::MODULE_INVOICE, 'log_entity_id' => $id])->orderBy('log_created_at', 'desc')->get();
        } elseif ($module == 'payment') {
            $qb_logs = QbLogsModel::where(['log_module_id' => QbLogsModel::MODULE_PAYMENT, 'log_entity_id' => $id])->orderBy('log_created_at', 'desc')->get();
        } elseif ($module == 'class') {
            $qb_logs = QbLogsModel::where(['log_module_id' => QbLogsModel::MODULE_CLASS, 'log_entity_id' => $id])->orderBy('log_created_at', 'desc')->get();
        }

        if (!empty($qb_logs)) {
            foreach ($qb_logs as $key => $log) {
                $qb_logs[$key]['log_created_at_js'] = getDateTimeWithTimestamp($log['log_created_at'], true);
            }
        }

        return $this->response(['id' => $id, 'module' => $module, 'qb_logs' => $qb_logs], 200);
    }

    function updateItemsClassFromQB()
    {
        $dataService = dataServiceConfigure($this->accessToken);
        $items = query('Item', $dataService);
        if (!empty($items)) {
            foreach ($items as $item) {
                $dataForUpdate = [];
                if (!empty($item->ParentRef)) {
                    $category = Category::where('category_qb_id', $item->ParentRef)->first();
                    if (!empty($category))
                        $dataForUpdate['service_category_id'] = $category->category_id;
                }
                if (!empty($item->ClassRef)) {
                    $class = QBClass::where('class_qb_id', $item->ClassRef)->first();
                    if (!empty($class))
                        $dataForUpdate['service_class_id'] = $class->class_id;
                }
                if (!empty($dataForUpdate))
                    Service::where('service_qb_id', $item->Id)->update($dataForUpdate);
            }
            debug2('success!');
        } else {
            debug2('records empty!');
        }
    }

    function mergeSubClientsToParentClient()
    {
        $dataService = dataServiceConfigure($this->accessToken);
        $customers = query('Customer', $dataService);
        $settings = getQbSettings();
        foreach ($customers as $customer) {
            $clientId = getClientId($customer->ParentRef);
//            $clientId = 444;
//            if (!empty($customer->ParentRef) && $customer->ParentRef == 730) {
            if (!empty($customer->ParentRef) ) {
                $name = !empty($customer->GivenName) || !empty($customer->FamilyName) ? $customer->GivenName . ' ' . $customer->FamilyName : $customer->DisplayName;
                $data = [
                    'cc_title' => $customer->DisplayName,
                    'cc_name' => $name,
                    'cc_email' => !empty($customer->PrimaryEmailAddr->Address) ? $customer->PrimaryEmailAddr->Address : null,
                    'cc_client_id' => $clientId
                ];
                if (!empty($customer->PrimaryPhone->FreeFormNumber)) {
                    $data['cc_phone'] = numberFrom($customer->PrimaryPhone->FreeFormNumber);
                    $data['cc_phone_clean'] = substr(numberFrom($customer->PrimaryPhone->FreeFormNumber), 0, config_item('phone_clean_length'));
                }
                if (!empty($customer->Mobile->FreeFormNumber)) {
                    $data['cc_phone'] = numberFrom($customer->Mobile->FreeFormNumber);
                    $data['cc_phone_clean'] = substr(numberFrom($customer->Mobile->FreeFormNumber), 0, config_item('phone_clean_length'));
                }
                $data['cc_email_check'] = NULL;
                $this->mdl_clients->add_client_contact($data);
//                die;
                $oneQuery = new QueryMessage();
                $oneQuery->sql = "SELECT";
                $oneQuery->entity = "Invoice";
                $oneQuery->whereClause = ["CustomerRef = '" . $customer->Id . "'"];
                $result = customQuery($oneQuery, $dataService);
                if (!empty($result) && is_array($result)) {
//                    debug2($result);
//                    die;

//                    debug2($customer->ParentRef);
//                    debug2($clientId);
                    if (empty($clientId))
                        continue;
//                    debug2($clientId);
//die;
                    foreach ($result as $invoice) {
                        $qbInvoiceNO = $invoice->DocNumber;
                        //tax
                        $tax = [];
                        $invoiceQbTax = $invoice->TxnTaxDetail;
                        if ($settings['us']) {
                            if (!empty($invoiceQbTax->TxnTaxCodeRef)) {
                                $taxFromQB = getQBEntityById('TaxCode', $invoiceQbTax->TxnTaxCodeRef, $dataService);
                                if ($taxFromQB)
                                    $tax = getTaxToDbEstimate($invoiceQbTax, $taxFromQB);
                            }
                        } else {
                            if (!empty($invoiceQbTax->TaxLine)) {
                                $tax = getTaxToDbEstimateFromTaxLine($invoiceQbTax->TaxLine, $dataService);
                            }
                        }
                        $lead = getLeadToDBv2($invoice, $customer, $clientId);


                        //Invoice Services
                        $services = $invoice->Line;

                        //create Lead
                        $leadId = $this->mdl_leads->insert_leads($lead);
                        $leadNO = getLeadNO($leadId);
                        $this->mdl_leads->update_leads($leadNO, ['lead_id' => $leadId]);
                        make_notes($clientId, 'Quickbooks: I just created a new lead "' . $leadNO['lead_no'] . '" for the client. ', $type = 'system', $lead_id = NULL);

                        // create Estimate
                        $estimate = getEstimateToDB($invoice, $leadId, $clientId);
                        if (count(array_filter($tax)) >= 2)
                            $estimate = array_merge($estimate, $tax);
                        $estimateId = $this->mdl_estimates->insert_estimates($estimate);
                        make_notes($clientId, 'Quickbooks: I just created a new estimate "' . $estimate['estimate_no'] . '" for the client. ', $type = 'system', $lead_id = NULL);

                        //create Estimate Services
                        $estimateServices = getEstimateServicesToDB($services, $estimateId, $dataService, $this->settings['us'], $invoice->GlobalTaxCalculation, isset($tax['estimate_tax_rate']) ? $tax['estimate_tax_rate'] : 1);
                        foreach ($estimateServices as $estimateService) {
                            $estimateBundleServices = !empty($estimateService['bundle_records']) ? $estimateService['bundle_records'] : [];
                            unset($estimateService['bundle_records']);
                            $estimateServiceId = $this->mdl_estimates->insert_estimate_service($estimateService);
                            if (!empty($estimateBundleServices))
                                foreach ($estimateBundleServices as $record) {
                                    $estimateBundleServiceId = $this->mdl_estimates->insert_estimate_service($record);
                                    if (!empty($estimateServiceId) && !empty($estimateBundleServiceId)) {
                                        $estimateBundle = [
                                            'eb_service_id' => $estimateBundleServiceId,
                                            'eb_bundle_id' => $estimateServiceId
                                        ];
                                        $this->mdl_estimates_bundles->insert($estimateBundle);
                                    }
                                }

                        }

                        // create discount
                        $discount = getDiscountToDB($services, $estimateId);
                        if (is_array($discount) && !empty($discount)) {
                            $this->mdl_clients->insert_discount($discount);
                        }

                        // create work orders
                        $workOrderNumber = getNO($leadId, 'W');
                        $workOrder = getWorkOrderToDB($clientId, $estimateId, $workOrderNumber, $qbInvoiceNO, $invoice);
                        $workOrderId = $this->mdl_workorders->insert_workorders($workOrder);
                        make_notes($clientId, 'Quickbooks: I just created a new work order "' . $workOrderNumber . '" for the client. ', $type = 'system', $lead_id = NULL);

                        // create invoice
                        $invoiceNumber = getNO($leadId, 'I');
                        $invoiceToDB = getInvoiceToDB($invoiceNumber, $workOrderId, $estimateId, $clientId, $invoice, 'create');
                        $invoiceToDB['invoice_notes'] = preg_replace("/.*\./Us", "", $invoiceToDB['invoice_notes'], 1);
                        $itemId = $this->mdl_invoices->insert_invoice($invoiceToDB);
                        make_notes($clientId, 'Quickbooks: I just created a new invoice "' . $invoiceNumber . '" for the client. ', $type = 'system', $lead_id = NULL);

                        if (!empty($invoice->Deposit)) {
                            $paymentMethods = getPaymentMethods($dataService);
                            $invoice->Id = 0;
                            $paymentToDB = getPaymentToDB($invoice, $estimateId, $invoice->Deposit, $paymentMethods);
                            $paymentId = $this->mdl_clients->insert_payment($paymentToDB);
                            createQBLog('payment', 'create', 'pull', $paymentId);
//                            pushJob('quickbooks/payment/syncpaymentinqb', serialize(['id' => $paymentId, 'qbId' => '']));
                        }
                        if (!empty($invoice->LinkedTxn)) {
                            $operation = 'Create';
                            if (is_array($invoice->LinkedTxn)) {
                                foreach ($invoice->LinkedTxn as $payment) {
                                    $payment = findByIdInQB('Payment', $payment->TxnId, $dataService);
                                    if (is_array($payment->Line)) {
                                        foreach ($payment->Line as $lineItem) {
                                            $this->paymentObject($payment, $lineItem, $operation, $dataService);
                                        }
                                    } elseif (is_object($payment->Line)) {
                                        $this->paymentObject($payment, $payment->Line, $operation, $dataService);
                                    }
//                                    $this->paymentObject($payment, $operation);
                                }
                            } elseif (is_object($invoice->LinkedTxn)) {
                                $payment = findByIdInQB('Payment', $invoice->LinkedTxn->TxnId, $dataService);
                                if (!empty($payment) && !empty($payment->Line)) {
                                    if (is_array($payment->Line)) {
                                        foreach ($payment->Line as $lineItem) {
                                            $this->paymentObject($payment, $lineItem, $operation, $dataService);
                                        }
                                    } elseif (is_object($payment->Line)) {
                                        $this->paymentObject($payment, $payment->Line, $operation, $dataService);
                                    }
                                }
//                                $this->paymentObject($payment, $operation);
                            }

                        }
                        createQBLog('invoice', 'create', 'pull', $itemId);
                    }
                }
//                debug2($customer->Id);
                $clientId = getClientId($customer->Id);
                debug2($clientId);
//                if(!empty($clientId))
//                    $this->mdl_clients->complete_client_removal($clientId);
            }

        }
    }

    private function paymentObject($payment, $lineItem, $operation, $dataService)
    {
//        $lineItem = $payment->Line;
        $documentType = $lineItem->LinkedTxn->TxnType;
        $paymentMethods = getPaymentMethods($dataService);
        if ($documentType == 'Invoice') {
            $qbInvoiceId = $lineItem->LinkedTxn->TxnId;
            $invoice = $this->mdl_invoices->find_all(['invoice_qb_id' => $qbInvoiceId], 'id desc');
            $estimateId = false;
            if (!empty($invoice[0]))
                $estimateId = $invoice[0]->estimate_id;
            debug2('es - ' . $estimateId);
//            $estimateId = getEstimateIdByQbInvoiceId($qbInvoiceId);
            $totalAmt = $lineItem->Amount;
            $paymentToDB = getPaymentToDB($payment, $estimateId, $totalAmt, $paymentMethods);
//            $estimate = $this->mdl_estimates->find_by_id($estimateId);
            if ($operation == 'Create') {
                $paymentId = $this->mdl_clients->insert_payment($paymentToDB);
//                make_notes($estimate->client_id, 'Quickbooks: Payment for Estimate "' . $estimate->estimate_no . '" created. Transaction ID "' . $paymentId . '". ', $type = 'system', $lead_id = NULL);
                createQBLog('payment', 'create', 'pull', $paymentId);
            } elseif ($operation == 'Update') {
                $changeMessage = updatePaymentInDB($estimateId, $payment->Id, $paymentToDB);
//                $message = 'Quickbooks: Payment Transaction ID for "' . $estimate->estimate_no . '": "' . $changeMessage['id'] . '" changed:<br>';
//                $message .= $changeMessage['message'];
//                make_notes($estimate->client_id, $message, $type = 'system', $lead_id = NULL);
                createQBLog('payment', 'update', 'pull', $payment->Id);
            }
        }
    }

    public function getBadInvoices(){
        $dataService = dataServiceConfigure($this->accessToken);
        $i = 1;
        while (true) {
            $invoices = $dataService->FindAll('Invoice', $i, 500);
            $error = checkError($dataService);
            if (!$error) {
                return FALSE;
            }
            if (!$invoices)
                break;
            foreach ($invoices as $invoice) {
                $i++;
                $qbId = $invoice->Id;
                $checkInvoice = $this->mdl_invoices->find_all(['invoice_qb_id' => $qbId]);
                if(empty($checkInvoice)){
                    debug2('Invoice NO:' . $invoice->DocNumber . ' ID: ' . $qbId);
                    if(!empty($invoice->LinkedTxn) && is_array($invoice->LinkedTxn)){
                        $jobsName = getDriverNameForJobDB('Payment');
                        foreach ($invoice->LinkedTxn as $payment) {
                            if($payment->TxnType == "Payment")
                                pushJob($jobsName, serialize(['module' => 'Payment', 'qbId' => $payment->TxnId, 'operation' => 'Create']));
                        }
                    }
                }
            }
        }
    }

    public function matchClientsFromQB(){
//        $test = 'Zaccaria Louis';
//        $names = explode(',', $test);
//        $client = Client::where('client_name', 'LIKE', "%".$names[0]."%")->where('client_name', 'LIKE', "%".$names[1]."%")->get()->toArray();
//        debug2($client);
//        debug2($names);
//        die;
        $dataService = dataServiceConfigure($this->accessToken);
        $i = 1;
        $checkErr = false;
        while (true) {
            $clients = $dataService->FindAll('Customer', $i, 500);
            $error = checkError($dataService);
            if (!$error) {
                $checkErr = true;
                break;
            }
            if(is_array($clients) && !empty($clients))
                $i += count($clients);//countOk
            elseif (is_object($clients) && !empty($clients))
                $i += 1;



            if (!$clients) {
                break;
            }
            foreach ($clients as $customer){
                $clientName = $customer->CompanyName ?: $customer->DisplayName;
                if($clientName) {
                    $names = explode(',', $clientName);
                    $names2 = explode(' ', $clientName);
                    $client = Client::where('client_name', $clientName);
                    if(empty($client) && !empty($names)) {
                        $client = Client::where('client_name', 'LIKE', "%" . $names[0] . "%");
                        if(!empty($names[1]))
                            $client->where('client_name', 'LIKE', "%" . $names[1] . "%");
                        if(!empty($names[1]))
                            $client->where('client_name', 'LIKE', "%" . $names[1] . "%");
                        if(!empty($names[1]))
                            $client->where('client_name', 'LIKE', "%" . $names[1] . "%");
                        if(!empty($names[1]))
                            $client->where('client_name', 'LIKE', "%" . $names[1] . "%");
                        if(!empty($names[1]))
                            $client->where('client_name', 'LIKE', "%" . $names[1] . "%");

                        if(!empty($names2[1]))
                            $client->where('client_name', 'LIKE', "%" . $names2[1] . "%");
                        if(!empty($names2[1]))
                            $client->where('client_name', 'LIKE', "%" . $names2[1] . "%");
                        if(!empty($names2[1]))
                            $client->where('client_name', 'LIKE', "%" . $names2[1] . "%");
                        if(!empty($names2[1]))
                            $client->where('client_name', 'LIKE', "%" . $names2[1] . "%");
                        if(!empty($names2[1]))
                            $client->where('client_name', 'LIKE', "%" . $names2[1] . "%");
                    }
                    $client->update(['client_qb_id'=> $customer->Id, 'client_last_qb_sync_result' => 1]);
                        //Client::where('client_name', $clientName)->update(['client_qb_id'=> $customer->Id]);
//                    debug2(Client::where('client_name', $clientName)->get()->toArray());
//                    debug2($clientName);
                }
            }
        }
    }
}
