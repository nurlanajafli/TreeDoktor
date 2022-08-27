<?php
require_once('application/modules/qb/controllers/Qb.php');
use QuickBooksOnline\API\Core\ServiceContext;
use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\PlatformService\PlatformService;
use QuickBooksOnline\API\Core\Http\Serialization\XmlObjectSerializer;
use QuickBooksOnline\API\Facades\Customer;
use QuickBooksOnline\API\Facades\Invoice;
use QuickBooksOnline\API\Facades\Account;
use QuickBooksOnline\API\Facades\Item;
use QuickBooksOnline\API\QueryFilter\QueryMessage;
use QuickBooksOnline\API\Data\IPPAttachable;
use QuickBooksOnline\API\Data\IPPAttachableRef;
use QuickBooksOnline\API\Data\IPPReferenceType;
use QuickBooksOnline\API\Data\IPPIntuitEntity;
use application\modules\qb\models\QbLogs as QbLogsModel;
use application\modules\categories\models\Category;
use application\modules\classes\models\QBClass;
use application\modules\clients\models\ClientsContact;
use application\modules\references\models\Reference;
use application\modules\user\models\User;
use application\models\PaymentTransaction;


if (!defined('BASEPATH'))
    exit('No direct script access allowed');

function parseAuthRedirectUrl($url)
{
    parse_str($url, $qsArray);
    return [
        'code' => $qsArray['code'],
        'realmId' => $qsArray['realmId']
    ];
}

function dataServiceConfigure($accessToken)
{
    $dataService = DataService::Configure(array(
        'auth_mode' => 'oauth2',
        'ClientID' => $accessToken->getClientId(),
        'ClientSecret' => $accessToken->getClientSecret(),
        'accessTokenKey' => $accessToken->getaccessToken(),
        'refreshTokenKey' => $accessToken->getrefreshToken(),
        'QBORealmID' => $accessToken->getRealmID(),
        'baseUrl' => config_item('qb_base_url') && array_search(config_item('qb_base_url'), ['https://sandbox-quickbooks.api.intuit.com', 'https://quickbooks.api.intuit.com']) !== FALSE ? config_item('qb_mode') : 'https://quickbooks.api.intuit.com'
    ));
    return $dataService;
}

function dataServiceConfigureFromArguments($clientId, $clientSecret, $accessToken, $refreshToken, $realmId, $baseUrl)
{
    $dataService = DataService::Configure(array(
        'auth_mode' => 'oauth2',
        'ClientID' => $clientId,
        'ClientSecret' => $clientSecret,
        'accessTokenKey' => $accessToken,
        'refreshTokenKey' => $refreshToken,
        'QBORealmID' => $realmId,
        'baseUrl' => config_item('qb_mode') && array_search(config_item('qb_mode'), ['development', 'production']) !== FALSE ? config_item('qb_mode') : 'production'
    ));
    return $dataService;
}

function refreshToken(DataService $dataService)
{
    $OAuth2LoginHelper = $dataService->getOAuth2LoginHelper();
    $accessToken = $OAuth2LoginHelper->refreshToken();
    createOrUpdateQbAccessToken($accessToken);
    $error = $OAuth2LoginHelper->getLastError();
    if ($error) {
        $message = getErrorMessage($error, 63);
        log_message('error', $message);
        return false;
    }
    $dataService->updateOAuth2Token($accessToken);
    $error = $dataService->getLastError();
    if ($error) {
        $message = getErrorMessage($error, 70);
        log_message('error', $message);
    }
    return $dataService;
}

function getErrorMessage($error, $line)
{
    $message = "QB ERROR FILE = qb_helper:" . $line . " \n";
    $message .= "The Status code is: " . $error->getHttpStatusCode() . "\n";
    $message .= "The Helper message is: " . $error->getOAuthHelperError() . "\n";
    $message .= "The Response message is: " . $error->getResponseBody() . "\n";
    return $message;
}

function query($module, $dataService)
{
    $data = [];
    $i = 1;
    while (true) {
        $allData = $dataService->FindAll($module, $i, 500);
        $error = $dataService->getLastError();
        if ($error) {

            $statusCode = $error->getHttpStatusCode();
            if ($statusCode == 401) {
                refreshToken($dataService);
                return false;
            } else {
                $message = getErrorMessage($error, 79);
                log_message('error', $message);
            }
            return false;
        }
        if (!$allData || !is_array($allData) || empty($allData)) {
            break;
        }

        foreach ($allData as $oneRecord) {
            $i++;
            array_push($data, $oneRecord);
        }
    }
    return $data;
}

function getAllCustomerToDB($customers, $import = false)
{
    if (!$customers)
        return null;
    $CI = &get_instance();
    $CI->load->model('mdl_clients');
    $customerTypeResidential = 1;
    $customerTypeCorporate = 2;
    $data = [];
    foreach ($customers as $customer) {
//        if(!empty($customer->ParentRef))
//            continue;
        $qbId = $customer->Id;
        $client = $CI->mdl_clients->find_by_fields(['client_qb_id' => $qbId]);
        if ($import) {
            $clients = $CI->mdl_clients->get_clients('', ['client_qb_id' => $qbId])->result();
            if ($clients)
                continue;
        }
        $billAddress = $customer->BillAddr;

        $clientName = $customer->CompanyName ?: $customer->DisplayName;
        $clientType = !empty($customer->CompanyName) ? $customerTypeCorporate : $customerTypeResidential;
        $dateCreate = date('Y-m-d', strtotime($customer->MetaData->CreateTime));
        $city = isset($billAddress->City) ? $billAddress->City : '';
        $state = isset($billAddress->CountrySubDivisionCode) ? $billAddress->CountrySubDivisionCode : '';
        $zip = isset($billAddress->PostalCode) ? $billAddress->PostalCode : '';
        $country = isset($billAddress->Country) ? $billAddress->Country : '';
        $lng = isset($customer->client_lng) ? $customer->client_lng : 0;
        $lat = isset($customer->client_lat) ? $customer->client_lat : 0;

        $record = [
            'client_qb_id' => $qbId,
            'client_brand_id'=>$client->client_brand_id ?? default_brand(),
            'client_name' => $clientName,
            'client_type' => $clientType,
            'client_source' => 'QuickBooks',
            'client_date_created' => $dateCreate,
            'client_address' => !empty($billAddress) ? getAddressForDB($billAddress) : '',
            'client_city' => $city,
            'client_state' => $state,
            'client_zip' => $zip,
            'client_country' => $country,
            'client_lng' => $lng,
            'client_lat' => $lat
        ];
        array_push($data, $record);
    }
    return $data;
}

function getAddressForDB(object $qbAddress):string {
    $addressLine1 = isset($qbAddress->Line1) ? $qbAddress->Line1 : '';
    $addressLine2 = isset($qbAddress->Line2) ? $qbAddress->Line2 : '';
    $addressLine3 = isset($qbAddress->Line3) ? $qbAddress->Line3 : '';
    $addressLine4 = isset($qbAddress->Line4) ? $qbAddress->Line4 : '';
    $addressLine5 = isset($qbAddress->Line5) ? $qbAddress->Line5 : '';
    return  trim($addressLine1 . ' ' . $addressLine2 . ' ' . $addressLine3 . ' ' . $addressLine4 . ' ' . $addressLine5);
}

function getAllClientsContactsToDB($customers, $customerId = '')
{
    $data = [];
    foreach ($customers as $customer) {
        $clientName = $customer->CompanyName ?: $customer->DisplayName;
        $email = isset($customer->PrimaryEmailAddr->Address) ? $customer->PrimaryEmailAddr->Address : '';
        $title = 'Contact #1';
        $phone = '';
        $print = 0;
        if(isset($customer->PrimaryPhone->FreeFormNumber) || isset($customer->Mobile->FreeFormNumber) || isset($customer->Fax->FreeFormNumber)) {
            if (isset($customer->PrimaryPhone->FreeFormNumber)) {
                $phone = $customer->PrimaryPhone->FreeFormNumber;
                $cleanPhone = numberFrom($phone);
                $record = getClientContactToDB($customerId, $title, $clientName, $cleanPhone, $email, 1);
                array_push($data, $record);
                $print = 1;
            }
//            if (isset($customer->Mobile->FreeFormNumber)) {
//                $title = 'Mobile';
//                $phone = $customer->Mobile->FreeFormNumber;
//                $cleanPhone = numberFrom($phone);
//                $record = getClientContactToDB($customerId, $title, $clientName, $cleanPhone, $email, 0);
//                array_push($data, $record);
//                $print = 1;
//            }
//            if (isset($customer->Fax->FreeFormNumber)) {
//                $title = 'Fax';
//                $phone = $customer->Fax->FreeFormNumber;
//                $cleanPhone = numberFrom($phone);
//                $record = getClientContactToDB($customerId, $title, $clientName, $cleanPhone, $email, 0);
//                array_push($data, $record);
//            }
        } else if(!empty($email)){
            $record = getClientContactToDB($customerId, $title, $clientName, '', $email, 1);
            array_push($data, $record);
        }
    }
    return $data;
}

function getClientContactToDB($customerId, $title, $clientName, $phone, $email, $print)
{
    $record = [
        'cc_client_id' => $customerId,
        'cc_title' => $title,
        'cc_name' => $clientName,
        'cc_phone' => $phone,
        'cc_phone_clean' => substr($phone, 0, config_item('phone_clean_length')),
        'cc_email' => $email,
        'cc_print' => $print
    ];
    return $record;
}

function addClientIdToClientsContacts($clientsContacts, $clientName, $clientsId)
{
    foreach ($clientsContacts as $key => $clientContact) {
        $name = $clientContact['cc_name'];
        if ($name == $clientName) {
            $clientsContacts[$key]['cc_client_id'] = $clientsId;
        }
    }
    return $clientsContacts;
}

function getAllItemsToDB($items, $dataService = null)
{
    $CI =& get_instance();
    $CI->load->model('mdl_settings_orm');
    $data = [];
    if (!is_array($items) || empty($items))
        return null;
    foreach ($items as $item) {
        if ($item->Type != 'Categories') {
            $qbId = $item->Id;
            $setting = $CI->mdl_settings_orm->get_by('stt_key_name', 'interest');
            $checkInterest = false;
            $serviceStatus = $item->Active == 'true' ? 1 : 0;
            if ($setting)
                $checkInterest = $setting->stt_key_value == $qbId ? true : false;
            if (!$checkInterest) {
                $name = $item->Name;
                $description = $item->Description;
                $record = [
                    'service_name' => $name,
                    'service_description' => $description,
                    'service_qb_id' => $qbId,
                    'service_status' => $serviceStatus,
                    'service_category_id' => 1,
                    'is_product' => 0,
                    'is_bundle' => 0
                ];
                if($serviceStatus == 0){
                    unset($record['service_name']);
                }
                if(!empty($item->ClassRef)){
                    $classRef = getClassId($item->ClassRef);
                    if(!empty($classRef))
                        $record['service_class_id'] = $classRef;
                }
                if(!empty($item->ParentRef)){
                    $categoryRef = getCategoryId($item->ParentRef);
                    if(!empty($categoryRef))
                        $record['service_category_id'] = $categoryRef;
                }
                if ($item->Type == 'NonInventory' || $item->Type == 'Inventory') {
                    $record['is_product'] = 1;
                    $record['cost'] = $item->UnitPrice;
                } elseif ($item->Type == 'Group' && isset($item->ItemGroupDetail->ItemGroupLine)){
                    $record['is_bundle'] = 1;
                    $record['is_view_in_pdf'] = (empty($item->PrintGroupedItems) ||  $item->PrintGroupedItems == 'false')? 0 : 1;
                    if(is_object($item->ItemGroupDetail->ItemGroupLine))
                        $bundleRecords[] = $item->ItemGroupDetail->ItemGroupLine;
                    else
                        $bundleRecords = $item->ItemGroupDetail->ItemGroupLine;
                    $record['bundle_records'] = getBundlesRecordsForDB($bundleRecords, 0, $dataService);
                }
                array_push($data, $record);
            }
        }
    }
    return $data;
}
function getCategoryId($categoryQBId){
    $CI =& get_instance();
    $CI->load->library('Common/QuickBooks/QBCategoryActions');
    $CI->load->library('Common/CategoryActions');
    $categoryId = null;
    if(!empty($categoryQBId)){
        $result = $CI->categoryactions->setCategoryByQBId($categoryQBId);
        if($result === false){
            $resultQB = $CI->qbcategoryactions->setCategory($categoryQBId);
            if($resultQB === true) {
                $CI->categoryactions->setCategoryByArrayQB($CI->qbcategoryactions->getCategory());
                $categoryId = $CI->categoryactions->save();
            }
        } else {
            $categoryId = $CI->categoryactions->getCategoryId();
        }
    }
    return $categoryId == null ? 1 : $categoryId;
}
function getClassId($classQBId){
    $CI =& get_instance();
    $CI->load->library('Common/QuickBooks/QBClassActions');
    $CI->load->library('Common/ClassActions');
    $classId = null;
    if(!empty($classQBId)){
        $result = $CI->classactions->setClassByQBId($classQBId);
        if($result === false){
            $resultQB = $CI->qbclassactions->setClass($classQBId);
            if($resultQB === true) {
                $CI->classactions->setClassByArrayQB($CI->qbclassactions->getClass());
                $classId = $CI->classactions->save();
            }
        } else {
            $classId = $CI->classactions->getClassId();
        }
    }
    return $classId;
}
function createRecordInQBFromObject($object, DataService $dataService, $invoiceNO = false, $returnObj = false, $entityId = false)
{
    if (!is_object($object) || !is_object($dataService)) {
        return false;
    }
    $resultingObj = $dataService->Add($object);
    $error = $dataService->getLastError();
    createLogFromQbObject($object, 'create', 'push', $entityId, $error);
    if ($error) {
        $statusCode = $error->getHttpStatusCode();
        if ($statusCode == 403)
            return 'AuthorizationFailed';
        if ($statusCode == 401) {
            refreshToken($dataService);
            return 'AuthenticationFailed';
        } elseif ($error->getIntuitErrorMessage() == 'Duplicate Name Exists Error' || $error->getIntuitErrorMessage() == 'Duplicate Document Number Error') {
            return 'duplicate';
        } else {
            $message = getErrorMessage($error, 260);
            log_message('error', $message);
            return false;
        }

    } else {
        if($invoiceNO && !empty($resultingObj->DocNumber))
            return ['id' => $resultingObj->Id, 'invoiceNO' => $resultingObj->DocNumber];
        return $returnObj ? $resultingObj : $resultingObj->Id;
    }
}

function createLogFromQbObject($object, $action, $route, $entityId, $error){
    $qbModule = get_class($object);
    $moduleId = null;
    $statusCode = null;
    $message = null;
    if($action == 'create')
        $action = QbLogsModel::ACTION_CREATE;
    elseif($action == 'update')
        $action = QbLogsModel::ACTION_UPDATE;
    elseif($action == 'delete')
        $action = QbLogsModel::ACTION_DELETE;
    $route = $route == 'pull' ? QbLogsModel::ROUTE_PULL : QbLogsModel::ROUTE_PUSH;
    $result = empty($error) ? QbLogsModel::RESULT_SUCCESS : QbLogsModel::RESULT_ERROR;
    if(stripos($qbModule, 'Item') !== false) {
        $moduleId = QbLogsModel::MODULE_ITEM;
        if(!empty($object->Type) && !empty($object->Type->value) && $object->Type->value == 'Category')
            $moduleId = QbLogsModel::MODULE_CLASS;
    } elseif (stripos($qbModule, 'Customer') !== false){
        $moduleId = QbLogsModel::MODULE_CLIENT;
    } elseif (stripos($qbModule, 'Invoice') !== false){
        $moduleId = QbLogsModel::MODULE_INVOICE;
    } elseif (stripos($qbModule, 'Payment') !== false){
        $moduleId = QbLogsModel::MODULE_PAYMENT;
    }elseif (stripos($qbModule, 'Class') !== false){
        $moduleId = QbLogsModel::MODULE_CLASS;
    }

    if(!empty($error)){
        $statusCode = $error->getHttpStatusCode();
        $message = $error->getIntuitErrorDetail();
    }
    if ($moduleId !== null && !empty($entityId) && !empty($action) && !empty($route)) {
        $log = [
            'log_module_id' => $moduleId,
            'log_entity_id' => $entityId,
            'log_status_code' => $statusCode,
            'log_message' => $message,
            'log_action' => $action,
            'log_route' => $route,
            'log_result' => $result,
            'log_user_id' => !empty(get_user_id()) ? get_user_id() : 0,
        ];
        QbLogsModel::create($log);
    }
}
function getQBLogAction(string $action){
    $qbAction = QbLogsModel::ACTION_CREATE;
    if($action == 'update')
        $qbAction = QbLogsModel::ACTION_UPDATE;
    elseif($action == 'delete')
        $qbAction = QbLogsModel::ACTION_DELETE;
    elseif ($action == 'get')
        $qbAction = QbLogsModel::ACTION_GET;
    return $qbAction;
}
function getQBLogRoute(string $route){
    return $route == 'pull' ? QbLogsModel::ROUTE_PULL : QbLogsModel::ROUTE_PUSH;
}
function createQBLog($module, $action, $route, $entityId, $message = ''){
    $moduleId = null;
    $statusCode = null;
    $action = getQBLogAction($action);
    $route = getQBLogRoute($route);
    $result = empty($message) ? QbLogsModel::RESULT_SUCCESS : QbLogsModel::RESULT_ERROR;
    if($module == 'item') {
        $moduleId = QbLogsModel::MODULE_ITEM;
    } elseif ($module == 'customer'){
        $moduleId = QbLogsModel::MODULE_CLIENT;
    } elseif ($module == 'invoice'){
        $moduleId = QbLogsModel::MODULE_INVOICE;
    } elseif ($module == 'payment'){
        $moduleId = QbLogsModel::MODULE_PAYMENT;
    } elseif ($module == 'class')
        $moduleId = QbLogsModel::MODULE_CLASS;
    if ($moduleId !== null && !empty($entityId) && !empty($action) && !empty($route)) {
        $log = [
            'log_module_id' => $moduleId,
            'log_entity_id' => $entityId,
            'log_status_code' => $statusCode,
            'log_message' => $message,
            'log_action' => $action,
            'log_route' => $route,
            'log_result' => $result,
            'log_user_id' => !empty(get_user_id()) ? get_user_id() : 0,
        ];
        QbLogsModel::create($log);
    }
}

function getQBEntityById($module, $id, $dataService)
{
    $record = $dataService->FindById($module, $id);
    $error = checkError($dataService);
    if (!$error)
        return false;
    return $record;
}

function updateRecordInQBFromObject($object, $dataService, $returnObj = false, $entityId = false)
{
    if (!is_object($object) || !is_object($dataService)) {
        return false;
    }
    $resultingObj = $dataService->Update($object);
    $result = checkError($dataService, $object, 'update', 'push', $entityId);
    if($result === false)
        return $result;
    return $returnObj ? $resultingObj : $result;
}

function checkError($dataService, $object=null, $action=null, $route=null, $entityId=null)
{
    $error = $dataService->getLastError();
    if(!empty($object) && !empty($action) && !empty($route) && !empty($entityId))
        createLogFromQbObject($object, $action, $route, $entityId, $error);
    if ($error) {
        $statusCode = $error->getHttpStatusCode();
        if ($statusCode == 401) {
            refreshToken($dataService);
        }
        $message = getErrorMessage($error, 298);
        log_message('error', $message);
        return false;
    }
    return true;
}

function createClientForQB($client)
{
    if (!isset($client) || empty($client)) {
        return false;
    }
    $CI =& get_instance();
    $CI->load->model('mdl_clients');

    $clientId = is_object($client) ? $client->client_id : $client['client_id'];
    $clientContacts = $CI->mdl_clients->get_client_contacts('cc_client_id = ' . $clientId);
    $phones = getPhonesToQb($clientContacts);
    $clientName = is_object($client) ? $client->client_name : $client['client_name'];
    $clientType = is_object($client) ? $client->client_type : $client['client_type'];
    if (isset($phones['email']))
        $checkEmail = filter_var(trim($phones['email']), FILTER_VALIDATE_EMAIL);
//        $checkEmail = preg_match('/^((([0-9A-Za-z]{1}[-0-9A-z\.]{1,}[0-9A-Za-z]{1})|([0-9А-Яа-я]{1}[-0-9А-я\.]{1,}[0-9А-Яа-я]{1}))@([-A-Za-z]{1,}\.){1,2}[-A-Za-z]{2,})$/u', trim($phones['email']));
    if (empty($clientName)) {
        $clientMainIntersection = is_object($client) ? $client->client_main_intersection : $client['client_main_intersection'];
        $clientAddress = is_object($client) ? $client->client_address : $client['client_address'];
        $clientName = $clientMainIntersection ?: $clientAddress;
    }
    $names = getNamesToQb($clientName, $clientType);
    $clientArray = [
        "BillAddr" => [
            "Line1" => is_object($client) ? $client->client_address : $client['client_address'],
            "City" => is_object($client) ? $client->client_city : $client['client_city'],
            "Country" => is_object($client) ? $client->client_country : $client['client_country'],
            "CountrySubDivisionCode" => is_object($client) ? $client->client_state : $client['client_state'],
            "PostalCode" => is_object($client) ? $client->client_zip : $client['client_zip']
        ],
        "Notes" => isset($phones['notes']) ? $phones['notes'] : '',
        "CompanyName" => isset($names['CompanyName']) ? $names['CompanyName'] : '',
//        "DisplayName" => isset($names['DisplayName']) ? preg_replace('/[^, a-zа-яё\d]/ui', '', $names['DisplayName']) : '',
        "DisplayName" => isset($names['DisplayName']) ? $names['DisplayName'] : '',
        "GivenName" => isset($names['GivenName']) ? $names['GivenName'] : '',
        "FamilyName" => isset($names['FamilyName']) ? $names['FamilyName'] : '',
        "PrimaryPhone" => [
            "FreeFormNumber" => isset($phones['phone']) ? $phones['phone'] : ''
        ],
        "PrimaryEmailAddr" => [
            "Address" => (isset($phones['email']) && $checkEmail) ? trim($phones['email']) : ''
        ]
    ];
    return $clientArray;
}

function getNamesToQb($clientName, $type)
{
    if ($type && $clientName) {
        $customerTypeResidential = 1;
        if ($type == $customerTypeResidential) {
            $namesArr = explode(" ", $clientName);
            $names['CompanyName'] = '';
            $names['GivenName'] = $namesArr[0];
            $names['FamilyName'] = isset($namesArr[1]) ? $namesArr[1] : '';
        } else {
            $names['CompanyName'] = $clientName;
            $names['GivenName'] = '';
            $names['FamilyName'] = '';
        }
        $names['DisplayName'] = $clientName;
        return $names;
    }
}

function getPhonesToQb($clientContacts)
{
    $notes = '';
    $phones = [];
    foreach ($clientContacts as $clientContact) {
        if ($clientContact['cc_print'] == 1) {
            $primaryPhone = $clientContact['cc_phone'];
            $primaryEmail = $clientContact['cc_email'];
            $phones = [
                'phone' => $primaryPhone,
                'email' => $primaryEmail
            ];
        } else {
            $notes .= $clientContact['cc_title'] . ': ' . $clientContact['cc_name'] . PHP_EOL . 'phone: ' . $clientContact['cc_phone'] . ', email: ' . $clientContact['cc_email'] . '.' . PHP_EOL;
        }
    }
    $phones['notes'] = $notes;
    return $phones;
}

function createItemsInDB($items)
{
    $CI =& get_instance();
    $CI->load->model('mdl_services');
    $CI->load->model('mdl_bundles_services');
    foreach ($items as $item) {
        $checkInDB = $CI->mdl_services->find_all(['service_qb_id' => $item['service_qb_id']]);
        if($checkInDB)
            continue;
        if(isset($item['is_bundle'])){
            $bundleRecords = $item['bundle_records'] ?? [];
            unset($item['bundle_records']);
        }
        $id = $CI->mdl_services->insert($item);
        if(!empty($bundleRecords)){
            $price = 0;
            foreach ($bundleRecords as $record){
                $record['bundle_id'] = $id;
                $price += $record['cost'];
                unset($record['cost']);
                $CI->mdl_bundles_services->insert($record);
            }
            $CI->mdl_services->update_by(['service_qb_id' => $item['service_qb_id']], ['cost' => $price]);
            unset($bundleRecords);
        }
        if(!empty($id))
            createQBLog('item', 'create', 'pull', $id);
    }
}

function getLeadToDB($document, $clientQbId = null)
{
    if (is_object($document)) {
        $CI =& get_instance();
        $CI->load->model('mdl_leads_status');
        $CI->load->model('mdl_clients');
        $clientQbId = !empty($clientQbId) ? $clientQbId : $document->CustomerRef;
        $client = $CI->mdl_clients->get_clients('', 'client_qb_id = ' . $clientQbId)->row();
        $leadStatus = $CI->mdl_leads_status->get_by(['lead_status_estimated' => 1]);
        $leadStatusName = !empty($leadStatus) ? $leadStatus->lead_status_name : 'Estimated';
        $referenceFromDB = Reference::where('slug', 'Quickbooks')->first();
        $leadRefferedBy = !empty($referenceFromDB) ? $referenceFromDB->id : 'Quickbooks';
        $timing = 'Right Away';
        $dateCreate = new DateTime($document->MetaData->CreateTime);

        $lead = [
            'client_id' => $client->client_id,
            'lead_address' => $client->client_address,
            'lead_city' => $client->client_city,
            'lead_state' => $client->client_state,
            'lead_zip' => $client->client_zip,
            'lead_country' => $client->client_country,
            'timing' => $timing,
            'lead_status' => $leadStatusName,
            'lead_reffered_by' => $leadRefferedBy,
            'lead_status_id' => $leadStatus->lead_status_id,
            'lead_date_created' => $dateCreate->format('Y-m-d H:i:s')
        ];
        $lead = setLeadCustomFieldsToDB($lead, $document);
        return $lead;
    }
    return false;
}

function getLeadNO($leadId)
{
    if ($leadId) {
        $leadNo = str_pad($leadId, 5, '0', STR_PAD_LEFT);
        $leadNo = $leadNo . "-L";
        $updateData = ["lead_no" => $leadNo];
        return $updateData;
    }
    return false;
}

function getEstimateToDB($invoice, $leadId, $clientId = null)
{
    if (is_object($invoice) && $leadId) {
        $userId = 0;
        $statusConfirmed = 6;
        $CI =& get_instance();
        $client = $CI->mdl_clients->get_clients('', 'client_qb_id = ' . $invoice->CustomerRef)->row();
        $estimateNo = str_pad($leadId, 5, '0', STR_PAD_LEFT);
        $estimateNo .= "-E";
        $dateCreate = new DateTime($invoice->MetaData->CreateTime);
        $estimate = [
            'estimate_no' => $estimateNo,
            'lead_id' => $leadId,
            'estimate_brand_id' => default_brand(),
            'client_id' => !empty($clientId) ? $clientId : $client->client_id,
            'date_created' => $dateCreate->getTimestamp(),
            'status' => $statusConfirmed,
            'user_id' => $userId
        ];
        return $estimate;
    }
    return false;
}

function getClientId($qbId)
{
    $CI =& get_instance();
    $client = $CI->mdl_clients->get_clients('', 'client_qb_id = ' . $qbId)->row();
    return $client->client_id ?? FALSE;
}

function getEstimateServicesToDB($services, $estimateId, $dataService, $us = false, $taxCalculation = false, $taxRate = 1, $serviceStatus = 2)
{
    if (is_array($services) && $estimateId && !empty($services)) {
        $CI =& get_instance();
        $CI->load->model('mdl_services');
        $CI->load->model('mdl_settings_orm');
        $estimateServices = [];
        $setting = $CI->mdl_settings_orm->get_by('stt_key_name', 'interest');
        $interestId = !empty($setting) && !empty($setting->stt_key_value) ? $setting->stt_key_value : 0;
        foreach ($services as $service) {
            if (isset($service->SalesItemLineDetail->ItemRef) || isset($service->GroupLineDetail->GroupItemRef)) {
                $qbId = isset($service->SalesItemLineDetail->ItemRef) ? $service->SalesItemLineDetail->ItemRef : $service->GroupLineDetail->GroupItemRef;
                if(!empty($interestId) && $interestId == $qbId)
                    continue;
                $nonTaxable = 0;
                if(isset($service->SalesItemLineDetail->Qty) && !empty($service->SalesItemLineDetail->Qty))
                    $qty = $service->SalesItemLineDetail->Qty;
                elseif (isset($service->GroupLineDetail->Quantity) && !empty($service->GroupLineDetail->Quantity))
                    $qty = $service->GroupLineDetail->Quantity;
                else
                    $qty = 1;
                $cost = isset($service->SalesItemLineDetail->UnitPrice) ? $service->SalesItemLineDetail->UnitPrice : null;
                $qbInvoiceServiceId = isset($service->Id) ? $service->Id : null;
                $serviceFromDb = getServiceByQbId($qbId);
                $serviceId = null;
                if (!$serviceFromDb) {
                    $items = findByIdInQB('Item', $qbId, $dataService);
                    $itemsArray[] = $items;
                    $itemsToDB = getAllItemsToDB($itemsArray);
                    $serviceId = $CI->mdl_services->insert($itemsToDB[0]);
                }
                $id = isset($serviceFromDb[0]->service_id) ? $serviceFromDb[0]->service_id : $serviceId;
                if (!$id)
                    continue;
                if ($us && isset($service->SalesItemLineDetail->TaxCodeRef))
                    $nonTaxable = $service->SalesItemLineDetail->TaxCodeRef == 'NON' ? 1 : 0;
                elseif(isset($service->SalesItemLineDetail->TaxCodeRef)){
                    $taxRate = getTaxRateValue($dataService, $service->SalesItemLineDetail->TaxCodeRef);
                    $nonTaxable = $taxRate == 0 ? 1 : 0;
                }
                $amount = isset($service->Amount) ? $service->Amount : 0;
                if(isset($service->GroupLineDetail))
                    $amount = getBundleAmount($service->GroupLineDetail->Line);
                if ($taxCalculation == 'TaxInclusive')
                    $amount = $taxRate > 0 ? $amount * ($taxRate / 100 + 1) : $amount;
                $record = [
                    'service_id' => $id,
                    'estimate_id' => $estimateId,
                    'service_status' => $serviceStatus,
                    'service_description' => $service->Description,
                    'service_price' => $amount,
                    'non_taxable' => $nonTaxable,
                    'quantity' => $qty > 0 ? $qty : 1,
                    'cost' => $cost,
                    'estimate_service_qb_id' => $qbInvoiceServiceId
                ];

                if(!empty($service->SalesItemLineDetail->ClassRef)){
                    $class = QBClass::where('class_qb_id', $service->SalesItemLineDetail->ClassRef)->first();
                    if(!empty($class))
                        $record['estimate_service_class_id'] = $class->class_id;
                }
                if(isset($service->GroupLineDetail)){
                    $bundleRecords = $service->GroupLineDetail->Line;
                    if(is_object($bundleRecords))
                        $records = [$bundleRecords];
                    elseif(is_array($bundleRecords))
                        $records = $bundleRecords;
                    else{
                        $records = [];
                    }
                    $result =  getEstimateServicesToDB($records, $estimateId, $dataService, $us, $taxCalculation , $taxRate);
                    $record['bundle_records'] = $result;
                }
                if ($record['service_id'])
                    array_push($estimateServices, $record);
            }
        }
        return $estimateServices;
    }
    return false;
}

function getBundleAmount($records){
    $amount = 0;
    if(!$records)
        return $amount;
    if(is_array($records)) {
        foreach ($records as $record)
            $amount += $record->Amount;
    }
    else{
        $amount += $records->Amount;
    }
    return $amount;
}

function getTaxRateValue($dataService, $taxCodeRef)
{
    $taxCodeFromQB = getQBEntityById('TaxCode', $taxCodeRef, $dataService);
    if (!empty($taxCodeFromQB->SalesTaxRateList->TaxRateDetail->TaxRateRef)) {
        $taxCode = $taxCodeFromQB->SalesTaxRateList->TaxRateDetail->TaxRateRef;
        $taxRate = getQBEntityById('TaxRate', $taxCode, $dataService);
        if (isset($taxRate->RateValue)) {
            return $taxRate->RateValue;
        }
    }
    return -1;
}

function getDiscountToDB($items, $estimateId)
{
    if (is_array($items) && $estimateId) {
        $discount = [];
        foreach ($items as $item) {
            if (isset($item->DiscountLineDetail)) {
                $amount = $item->Amount;
                $isPersent = isset($item->DiscountLineDetail->DiscountPercent) ? true : false;
                if ($isPersent) {
                    $amount = $item->DiscountLineDetail->DiscountPercent;
                }
                $discount = [
                    'discount_amount' => $amount,
                    'estimate_id' => $estimateId,
                    'discount_percents' => $isPersent
                ];
            }
        }
        if(!count($discount))
            $discount = [
                'discount_amount' => 0,
                'estimate_id' => $estimateId,
                'discount_percents' => 0
            ];

        return $discount;
    }
}

function getServiceByQbId($qbId)
{
    if ($qbId) {
        $CI =& get_instance();
        $CI->load->model('mdl_services');
        $service = $CI->mdl_services->find_all(['service_qb_id' => $qbId]);
        return $service;
    }
    return false;
}

function getWorkOrderToDB($clientId, $estimateId, $workOrderNo, $qbInvoiceNumber = null, $invoice = null, $dateCreate = null, $isDefaultStatus = false)
{

    $CI =& get_instance();
    $CI->load->model('mdl_workorders');
    if(!$isDefaultStatus)
        $statusId = $CI->mdl_workorders->getFinishedStatusId();
    else
        $statusId = $CI->mdl_workorders->getDefaultStatusId();

    if($qbInvoiceNumber)
        $invoiceNumber = 'Quickbooks invoice# ' . $qbInvoiceNumber;
    if ($invoice) {
        $dateCreate = new DateTime($invoice->TxnDate);
        $dateCreate = $dateCreate->format('Y-m-d');
    }
    $workOrder = [
        'workorder_no' => $workOrderNo,
        'estimate_id' => $estimateId,
        'client_id' => $clientId,
        'wo_status' => $statusId,
        'wo_confirm_how' => $invoiceNumber ?? null,
        'date_created' => isset($dateCreate) ? $dateCreate : new DateTime()
    ];
    return $workOrder;
}

function getInvoiceToDB($invoiceNO, $workOrderId, $estimateId, $clientId, $invoice, $operation = null)
{
    $dateCreate = date('Y-m-d', strtotime($invoice->TxnDate));
    $overdue = date('Y-m-d', strtotime($invoice->DueDate));
    $statusSent = 3;
    $statusPaid = 4;
    $statusIssued = 1;
    $qbNO = $invoice->DocNumber;
    $invoiceNotes = 'Quickbooks invoice# ' . $qbNO . '. ' . $invoice->CustomerMemo;
    if ($invoice->Balance == 0) {
        $status = $statusPaid;
    } else if ($invoice->EmailStatus == 'EmailSent') {
        $status = $statusSent;
    } else {
        $status = $statusIssued;
    }
    $invoice = [
        'invoice_no' => $invoiceNO,
        'workorder_id' => $workOrderId,
        'estimate_id' => $estimateId,
        'client_id' => $clientId,
        'in_status' => $status,
        'date_created' => $dateCreate,
        'overdue_date' => $overdue,
        'invoice_notes' => $invoiceNotes,
        'invoice_qb_id' => $invoice->Id,
        'invoice_qb_link' => $invoice->InvoiceLink
    ];
    if($operation == 'create')
        $invoice['qb_invoice_no'] = $qbNO;
    return $invoice;
}

function getNO($id, $symbol)
{
    $number = str_pad($id, 5, '0', STR_PAD_LEFT);
    $number .= '-' . $symbol;
    return $number;
}

function getPaymentToDB($payment, $estimateId, $totalAmt, $paymentMethods, $paymentType = 'invoice')
{
    if (is_object($payment)) {
        $dateCreate = new DateTime($payment->MetaData->CreateTime);
        $paymentChecked = 1;
        $paymentMethod = array_search($payment->PaymentMethodRef, $paymentMethods);
        if(empty($paymentMethod) || !is_int($paymentMethod)){
            if(array_search($payment->PaymentMethodRef, $paymentMethods['CREDIT_CARD']))
                $paymentMethod = 2;
            elseif(array_search($payment->PaymentMethodRef, $paymentMethods['NON_CREDIT_CARD']))
                $paymentMethod = 1;
        }
        $paymentToDB = [
            'estimate_id' => $estimateId,
            'payment_method_int' => $paymentMethod,
            'payment_date' => $dateCreate->getTimestamp(),
            'payment_amount' => $totalAmt,
            'payment_checked' => $paymentChecked,
            'payment_type' => $paymentType,
            'payment_qb_id' => $payment->Id
        ];
        return $paymentToDB;
    }
    return false;
}

function getEstimateIdByQbInvoiceId($invoiceQBid)
{
    if ($invoiceQBid) {
        $CI =& get_instance();
        $CI->load->model('mdl_invoices');
        $invoice = $CI->mdl_invoices->find_by_field(['invoice_qb_id' => $invoiceQBid]);
        return $invoice->estimate_id ?? FALSE;
    }
    return false;
}

function createOrUpdateQbAccessToken($accessToken)
{
    if (is_object($accessToken)) {
        $CI = &get_instance();
        $CI->load->model('mdl_settings_orm');

        $settings = [
            'clientID' => $accessToken->getClientId(),
            'clientSecret' => $accessToken->getClientSecret(),
            'accessTokenKey' => $accessToken->getaccessToken(),
            'refreshTokenKey' => $accessToken->getrefreshToken(),
            'QBORealmID' => $accessToken->getRealmID(),
            'baseUrl' => config_item('qb_mode') && array_search(config_item('qb_mode'), ['development', 'production']) !== FALSE ? config_item('qb_mode') : 'production',
            'accessToken' => serialize($accessToken)
        ];
        foreach ($settings as $key => $value) {
            $isHidden = ($key != 'clientID' && $key != 'clientSecret') ? true : false;
            $data = [
                'stt_key_name' => $key,
                'stt_key_value' => $value,
                'stt_is_hidden' => $isHidden
            ];
            $setting = $CI->mdl_settings_orm->get_by('stt_key_name', $key);
            if (is_object($setting)) {
                $CI->mdl_settings_orm->update_by('stt_key_name', $key, $data);
            } else {
                $CI->mdl_settings_orm->insert($data, true);
            }
        }
    }
}

function getQbSettings()
{
    $CI = &get_instance();
    $CI->load->model('mdl_settings_orm');
//    $CI->load->model('mdl_settings_orm', 'settings');
    $CI->mdl_settings_orm->install();
    $clientId = $CI->mdl_settings_orm->get_by('stt_key_name', 'ClientID');
    $clientSecret = $CI->mdl_settings_orm->get_by('stt_key_name', 'ClientSecret');
    $accessToken = $CI->mdl_settings_orm->get_by('stt_key_name', 'accessTokenKey');
    $refreshToken = $CI->mdl_settings_orm->get_by('stt_key_name', 'refreshTokenKey');
    $realmId = $CI->mdl_settings_orm->get_by('stt_key_name', 'QBORealmID');
    $baseUrl = $CI->mdl_settings_orm->get_by('stt_key_name', 'baseUrl');
    $taxRate = config_item('tax');
    $prefix = config_item('prefix');
    $location = !empty(config_item('Location')) ? explode(',', config_item('Location'))[0] : null;
    $accessTokenFull = $CI->mdl_settings_orm->get_by('stt_key_name', 'accessToken');
    $authorizationRequestUrl = $CI->mdl_settings_orm->get_by('stt_key_name', 'AuthorizationRequestUrl');
    $tokenEndPointUrl = $CI->mdl_settings_orm->get_by('stt_key_name', 'TokenEndPointUrl');
    $oauthScope = $CI->mdl_settings_orm->get_by('stt_key_name', 'OauthScope');
    $oauthRedirectUri = $CI->mdl_settings_orm->get_by('stt_key_name', 'OauthRedirectUri');
    $interest = $CI->mdl_settings_orm->get_by('stt_key_name', 'interest');
    $us = config_item('autocomplete_restriction') == 'us';
    $sync = json_decode(config_item('synchronization'), TRUE);
    $stateInQB = isset($sync['in']['state']) ? $sync['in']['state'] : 1;
    $stateFromQB = isset($sync['from']['state']) ? $sync['from']['state'] : 1;
    $stateSyncInvoiceNO = config_item('syncInvoiceNO') == 'Disable' ? 1 : 0;
    if (!$clientId || (isset($clientId->stt_key_value) && !$clientId->stt_key_value))
        return FALSE;
    $interestValue = null;
    if ($interest)
        $interestValue = $interest->stt_key_value;
    $settings = [
        $clientId->stt_key_name => $clientId->stt_key_value,
        $clientSecret->stt_key_name => $clientSecret->stt_key_value,
        $accessToken->stt_key_name => $accessToken->stt_key_value,
        $refreshToken->stt_key_name => $refreshToken->stt_key_value,
        $realmId->stt_key_name => $realmId->stt_key_value,
        $baseUrl->stt_key_name => $baseUrl->stt_key_value,
        $accessTokenFull->stt_key_name => $accessTokenFull->stt_key_value,
        $authorizationRequestUrl->stt_key_name => $authorizationRequestUrl->stt_key_value,
        $tokenEndPointUrl->stt_key_name => $tokenEndPointUrl->stt_key_value,
        $oauthScope->stt_key_name => $oauthScope->stt_key_value,
        $oauthRedirectUri->stt_key_name => $oauthRedirectUri->stt_key_value,
        'tax_rate' => $taxRate,
        'interest' => $interestValue,
        'prefix' => $prefix,
        'location' => $location,
        'us' => $us,
        'stateInQB' => $stateInQB,
        'stateFromQB' => $stateFromQB,
        'stateSyncInvoiceNO' => $stateSyncInvoiceNO
    ];

    return $settings;
}

function createServiceForQB($service, $accountRef, $parentRef = null, $classRef = null)
{
    $CI = &get_instance();
    $CI->load->model('mdl_services');
    if (!isset($service) || empty($service)) {
        return false;
    }
    $type = 'Service';
    if($service->is_product)
        $type = 'NonInventory';
    $serviceArray = [
        "Name" => $service->service_name,
        "Description" => $service->service_description,
        "IncomeAccountRef" => $accountRef,
        "Type" => $type,
        "Active" => $service->service_status
    ];
    if($classRef != null)
        $serviceArray['ClassRef'] = $classRef;
    if ($service->is_product)
        $serviceArray['UnitPrice'] = !empty($service->cost) ? $service->cost : 0;
    if($parentRef != null) {
        $serviceArray['SubItem'] = 'true';
        $serviceArray['ParentRef'] = $parentRef;
    }
    return $serviceArray;
}

function createInvoiceForQB($customerId, $itemsForQB, $invoiceNO, $description, $txndate, $dueDate, $hst, $departmentQBid = null, $usa = false, $shipFromLead= null, $syncNO = true, $estimate = null, $isStatusSent = false, $email = null)
{
    if (!empty($customerId) || is_array($itemsForQB)) {
        $shipFromAddr = "";
        if ($usa && isset($shipFromLead)) {
            $shipFromAddr = [
                "Line1" => $shipFromLead
            ];
        }
        $invoice = [
            "CustomerRef" => $customerId,
            "TxnDate" => $txndate,
            "GlobalTaxCalculation" => $hst,
            "Line" => $itemsForQB,
            "DueDate" => $dueDate,
//            "Deposit" => 0,
            "ShipFromAddr" => $shipFromAddr
        ];
        if($departmentQBid)
            $invoice["DepartmentRef"] = $departmentQBid;
        if ($description) {
            if(strlen($description) > 1000)
                $description = substr($description, 0, 999);
            $invoice["CustomerMemo"] = $description;
        }
        if($syncNO)
            $invoice["DocNumber"] = $invoiceNO;
        if($usa)
            $invoice["ApplyTaxAfterDiscount"] = true;
        if($isStatusSent)
            $invoice['EmailStatus'] = 'EmailSent';
        if($estimate && is_object($estimate)){
            $invoice["BillAddr"] = [
                "Line1" => isset($estimate->lead_address) ? $estimate->lead_address : '',
                "City" => isset($estimate->lead_city) ? $estimate->lead_city : '',
                "Country" => isset($estimate->lead_state) ? $estimate->lead_state : '',
                "PostalCode" => isset($estimate->lead_zip) ? $estimate->lead_zip : '',
                "CountrySubDivisionCode" => isset($estimate->lead_country) ? $estimate->lead_country : ''
            ];
        }
        if(!empty($email))
            $invoice["BillEmail"] = [
                'Address' => $email
            ];
        $invoice = setCustomFieldsToQB($estimate, $invoice);

        return $invoice;
    }
    return false;
}
function deleteBundleRecordsFromAllRecords($records, $dataService){
    if(empty($records))
        return [];
    $CI = &get_instance();
    $CI->load->model('mdl_services');
    $CI->load->model('mdl_estimates_bundles');
    foreach ($records as $keyRec => $record){
        $itemFromDB = $CI->mdl_services->get($record['service_id']);
        if(!empty($itemFromDB) && $itemFromDB->is_bundle) {
            if (!empty($itemFromDB->service_qb_id)) {
                refreshToken($dataService);
                $itemFromQB = getQBEntityById('Item', $itemFromDB->service_qb_id, $dataService);
                if(!empty($itemFromQB)){
                    $recordsIncludeInBundle = $CI->mdl_estimates_bundles->get_estimates_bundles_records(['eb_bundle_id' => $record['id']]);
                    foreach ($records as $key => $value){
                        foreach ($recordsIncludeInBundle as $item){
                            if(isset($value['id']) && $value['id'] == $item->id)
                                unset($records[$key]);
                        }
                    }
                }
            }
            else
                unset($records[$keyRec]);
        }
    }
    return $records;
}
function createServicesForInvoiceQB($items, $taxCodeRef, $hst, $tax, $dataService, $taxCodeRefExempt = '')
{
    $CI = &get_instance();
    $CI->load->model('mdl_services');
    if (is_array($items)) {
        $items = deleteBundleRecordsFromAllRecords($items, $dataService);
        $itemsForQB = [];
        foreach ($items as $item) {
            $itemFromDB = $CI->mdl_services->get($item['service_id']);
            $type = !empty($itemFromDB->is_bundle) ? 'GroupLineDetail' : 'SalesItemLineDetail';
            $qty = !empty($item['quantity']) ? $item['quantity'] : 1;
            $price = !empty($item['cost']) ? $item['cost'] : '';
            if(empty($item['is_product']) && empty($item['is_bundle']))
                $price = '';
            if (isset($item['qbId']))
                $qbId = $item['service_id'];
            else
                $qbId = $itemFromDB->service_qb_id;
            if (!$qbId) {
                // Build a query
                $oneQuery = new QueryMessage();
                $oneQuery->sql = "SELECT";
                $oneQuery->entity = "Item";
                $oneQuery->whereClause = ["Name = '". $item['service_name'] ."'"];

                $checNameInQB = customQuery($oneQuery, $dataService);
                $itemObj = (object)$item;
                if(!is_array($checNameInQB) && $checNameInQB == 'refresh')
                    return false;
                elseif(empty($checNameInQB))
                    $qbId = createServiceInQB($itemObj, $dataService);
                else
                    $qbId = !empty($checNameInQB[0]) && !empty($checNameInQB[0]->Id) ? $checNameInQB[0]->Id : 0;

                if ($qbId == 'AuthenticationFailed' || $qbId == 'AuthorizationFailed')
                    return FALSE;
                elseif (!$qbId) {
                    $updateData = [
                        'service_qb_id' => 0
                    ];
                    $CI->mdl_services->update($itemObj->service_id, $updateData);
                    return FALSE;
                } else {
                    $updateData = [
                        'service_qb_id' => $qbId
                    ];
                    $CI->mdl_services->update($itemObj->service_id, $updateData);
                }
            }
            $description = $item['service_description'];
            if (strlen($description) > 3999)
                $description = mb_strimwidth($description, 0, 3999, "...");
            $itemForQB = [
                "Amount" => ($hst == 2 && $item['non_taxable'] == 0) ? ($item['service_price'] / (($tax + 100) / 100)) : $item['service_price'],
                "DetailType" => $type,
                "Description" => $description,
            ];
            if(!empty($itemFromDB->is_bundle) && $itemFromDB->is_bundle){
                $bundlesService = getBundleRecordToInvoice($item['id'], $hst, $tax, $taxCodeRefExempt, $taxCodeRef, $dataService);
                if($bundlesService === false)
                    return false;
                $itemForQB["GroupLineDetail"] = [
                    "GroupItemRef" => $qbId,
                    "Quantity" => $qty,
                    "Line" => $bundlesService
                ];
            }
            else{
                $itemForQB["SalesItemLineDetail"] = [
                    "ItemRef" => $qbId,
                    "Qty" => $qty,
                    "TaxCodeRef" => $hst == 1 || $item['non_taxable'] == 1 ? $taxCodeRefExempt : $taxCodeRef,
                    "UnitPrice" => ($hst == 2 && $item['non_taxable'] == 0 && $price > 0) ? ($price / (($tax + 100) / 100)) : $price
                ];
            }
            if(!empty($item['estimate_service_class_id'])){
                $class = QBClass::where([['class_id', $item['estimate_service_class_id']], ['class_qb_id', '>', 0]])->first();
                if(!empty($class))
                    $itemForQB["SalesItemLineDetail"]['ClassRef'] = $class->class_qb_id;

            }
            $itemsForQB[] = $itemForQB;
        }
        return $itemsForQB;
    }
    return false;
}

function getBundleRecordToInvoice($bundleId, $hst, $tax, $taxCodeRefExempt, $taxCodeRef, $dataService = null){
    $CI = &get_instance();
    $CI->load->model('mdl_bundles_services');
    $CI->load->model('mdl_services');
    $CI->load->model('mdl_estimates_bundles');
    $records = $CI->mdl_estimates_bundles->get_estimates_bundles_records(['eb_bundle_id' => $bundleId]);
    $result = [];
    foreach ($records as $record){
        $amount = $record->service_price;
        $qbId = $CI->mdl_services->get($record->service_id)->service_qb_id;
        if (!$qbId && $dataService) {
            $qbId = createServiceInQB($record, $dataService);
            if ($qbId == 'AuthenticationFailed' || $qbId == 'AuthorizationFailed')
                return FALSE;
            elseif (!$qbId) {
                $updateData = [
                    'service_qb_id' => 0
                ];
                $CI->mdl_services->update($record->service_id, $updateData);
                return FALSE;
            } else {
                $updateData = [
                    'service_qb_id' => $qbId
                ];
                $CI->mdl_services->update($record->service_id, $updateData);
            }
        }
//        $cost = !empty($record->cost) ? $record->cost : $amount;
        $qty = !empty($record->quantity) ? $record->quantity : 1;
        $amount = ($hst == 2 && $record->non_taxable == 0) ? ($amount / (($tax + 100) / 100)) : $amount;
        $itemToQB = [
            "Description" => $record->service_description,
            "Amount" => $amount,
            "DetailType" => "SalesItemLineDetail",
            "SalesItemLineDetail" => [
                "ItemRef" => $qbId,
                "UnitPrice" => $amount / $qty,
                "Qty" => $qty,
                "TaxCodeRef" => $hst == 1 || $record->non_taxable == 1 ? $taxCodeRefExempt : $taxCodeRef
            ]
        ];
        if(!empty($record->estimate_service_class_id)){
            $class = QBClass::where([['class_id', $record->estimate_service_class_id], ['class_qb_id', '>', 0]])->first();
            if(!empty($class))
                $itemToQB["SalesItemLineDetail"]['ClassRef'] = $class->class_qb_id;
//            debug2($class->class_qb_id);
        }

        $result[] = $itemToQB;
    }

    return $result;
}

function getTaxRateRef($taxInSettings, $taxesInQB, $taxesCodeInQB)
{
//    $rate = fmod($taxInSettings, 1) * 100;
    foreach ($taxesInQB as $oneTax) {
        if ($oneTax->RateValue == strval($taxInSettings) && getTaxCodeRef($taxesCodeInQB, $oneTax->Id)) {
            return $oneTax->Id;
        }
    }
}

function deleteRecordInQBFromObject($object, $dataService, $entityId = false)
{
    if (!is_object($object) || !is_object($dataService)) {
        return false;
    }
    $resultingObj = $dataService->Delete($object);
    $result = checkError($dataService, $object, 'delete', 'push', $entityId);
    if($result === false)
        return $result;
    return $resultingObj->Id;
}

function createDiscountForInvoiceQB($discount)
{
    if (is_array($discount) && !empty($discount)) {
        $percentBased = boolval($discount['discount_percents']);
        $discountForQB = [
            'Amount' => !$percentBased ? $discount['discount_amount'] : '',
            'DetailType' => 'DiscountLineDetail',
            'DiscountLineDetail' => [
                'PercentBased' => $percentBased,
                'DiscountPercent' => $percentBased ? $discount['discount_amount'] : ''
            ]
        ];
        return $discountForQB;
    }
    return null;
}

function createPaymentForQB($payment, $paymentMethods, $dataService)
{
    if (is_array($payment)) {
        $CI = &get_instance();
        $CI->load->model('mdl_invoices');
        $CI->load->model('mdl_clients');
        $invoice = $CI->mdl_invoices->find_by_field(['invoices.estimate_id' => $payment['estimate_id']]);
        if (is_object($invoice)) {
            if (isset($invoice->invoice_qb_id)) {
                $invoiceQBid = $invoice->invoice_qb_id;
            } else {
                $invoiceQBid = createInvoiceInQB($invoice, $dataService);
            }
            if (!$invoiceQBid)
                return false;
        }
        $client = $CI->mdl_clients->find_by_id($payment['client_id']);
        $clientQBid = '';
        if (is_object($client)) {
            if ($client->client_qb_id > 0)
                $clientQBid = $client->client_qb_id;
        }
        if (!$clientQBid)
            $clientQBid = createClientInQB($client, $dataService);
        if (!$clientQBid)
            return false;
        $paymentMethod = $paymentMethods[$payment['payment_method_int']] ?? '';
        if(empty($paymentMethod)){
            $paymentTransaction = PaymentTransaction::find($payment['payment_trans_id']);
            if(!empty($paymentTransaction) && is_object($paymentTransaction)) {
                $paymentMethod = $paymentMethods[$paymentTransaction->payment_transaction_card] ?? '';
            }
        }
        $date = new DateTime();
        $date->setTimestamp($payment['payment_date']);
        $paymentForQB = [
            'CustomerRef' => $clientQBid,
            'PaymentMethodRef' => $paymentMethod ?: '',
            'TotalAmt' => $payment['payment_amount'],
            'TxnDate' => $date->format('Y-m-d '),
            'Line' => ''
        ];
        if (is_object($invoice)) {
            $paymentForQB['Line'] = [
                'Amount' => $payment['payment_amount'],
                'LinkedTxn' => [
                    'TxnId' => $invoiceQBid,
                    'TxnType' => 'invoice'
                ]
            ];
        }
        return $paymentForQB;
    }
    return false;
}

function createPaymentForQBv2($payments, $paymentMethods, $dataService)
{
    if (is_array($payments)) {
        $CI = &get_instance();
        $CI->load->model('mdl_invoices');
        $CI->load->model('mdl_clients');

        $client = $CI->mdl_clients->find_by_id($payments[0]['client_id']);
        $clientQBid = '';
        if (is_object($client)) {
            if ($client->client_qb_id > 0)
                $clientQBid = $client->client_qb_id;
        }
        if (!$clientQBid)
            $clientQBid = createClientInQB($client, $dataService);
        if (!$clientQBid)
            return false;
        $paymentMethod = isset($paymentMethods[$payments[0]['payment_method_int']]) ? $paymentMethods[$payments[0]['payment_method_int']] : '';
        if(empty($paymentMethod) && !empty($payments[0]['payment_trans_id'])){
            $paymentTransaction = PaymentTransaction::find($payments[0]['payment_trans_id']);
            if(!empty($paymentTransaction) && is_object($paymentTransaction))
                $paymentMethod = isset($paymentMethods[$paymentTransaction->payment_transaction_card]) ? $paymentMethods[$paymentTransaction->payment_transaction_card] : '';
        }
        $date = new DateTime();
        $date->setTimestamp($payments[0]['payment_date']);

        $invoiceLines = [];
        $totalAmount = 0;
        foreach ($payments as $payment) {
            $invoice = $CI->mdl_invoices->find_by_field(['invoices.estimate_id' => $payment['estimate_id']]);
            $totalAmount += $payment['payment_amount'];
            if (is_object($invoice)) {
                if (isset($invoice->invoice_qb_id)) {
                    $invoiceQBid = $invoice->invoice_qb_id;
                } else {
                    $invoiceQBid = createInvoiceInQB($invoice, $dataService);
                }
                if (!$invoiceQBid)
                    return false;
            }
            if (is_object($invoice)) {
                $invoiceLines[] = [
                    'Amount' => $payment['payment_amount'],
                    'LinkedTxn' => [
                        'TxnId' => $invoiceQBid,
                        'TxnType' => 'invoice'
                    ]
                ];
            }
        }
        $paymentForQB = [
            'CustomerRef' => $clientQBid,
            'PaymentMethodRef' => $paymentMethod ?: '',
            'TotalAmt' => $totalAmount,
            'TxnDate' => $date->format('Y-m-d '),
            'Line' => $invoiceLines
        ];
        return $paymentForQB;
    }
    return false;
}

function getDriverNameForJobDB($name)
{
    $driverName = false;
    switch ($name) {
        case 'Customer':
            $driverName = 'quickbooks/client/syncclientindb';
            break;
        case 'Item':
            $driverName = 'quickbooks/item/syncserviceindb';
            break;
        case 'Invoice':
            $driverName = 'quickbooks/invoice/syncinvoiceindb';
            break;
        case 'Payment':
            $driverName = 'quickbooks/payment/syncpaymentindbv2';
            break;
        case 'Class':
            $driverName = 'quickbooks/class/syncclassindb';
            break;
        case 'Category':
            $driverName = 'quickbooks/category/synccategoryindb';
            break;
    }
    return $driverName;
}

function findByIdInQB($module, $id, DataService $dataService)
{
    $record = $dataService->FindById($module, $id);
    $error = checkError($dataService);
    if ($error)
        return $record;
    return false;
}

function removeAllinDB()
{
    $CI = &get_instance();
    $CI->load->model('mdl_clients');
    $CI->load->model('mdl_services');
    $clients = $CI->mdl_clients->get_clients()->result_array();
    $CI->mdl_services->delete_by('service_id > 18');
    foreach ($clients as $client) {
        $clientId = $client['client_id'];
        $CI->mdl_clients->complete_client_removal($clientId);
//        $cc = $CI->mdl_clients->get_client_contacts(['cc_client_id' => $clientId]);
//        foreach ($cc as $record)
//            $CI->mdl_clients->delete_client_contact($record['cc_id']);
    }
}

function getEstimateServicesServiceId($estimateId)
{
    $CI = &get_instance();
    $CI->load->model('mdl_estimates');
    $estimateServices = null;
    $oldEstimateServices = $CI->mdl_estimates->find_estimate_services($estimateId);
    if (!empty($oldEstimateServices)) {
        foreach ($oldEstimateServices as $estimateService)
            $estimateServices[] = $estimateService['service_id'];
    }
    return $estimateServices;
}

function updatePaymentInDB($estimateId, $paymentQbId, $updateData)
{
    $CI = &get_instance();
    $CI->load->model('mdl_clients');
    $where = [
        'payment_qb_id' => $paymentQbId,
        'estimates.estimate_id' => $estimateId
    ];
    $thePayment = $CI->mdl_clients->get_payments($where);
    if(!empty($thePayment) && !empty($thePayment[0])) {
        $paymentId = $thePayment[0]['payment_id'];
        $CI->mdl_clients->update_payment($paymentId, $updateData);
    } else {
        $paymentId = $CI->mdl_clients->insert_payment($updateData);
        $thePayment = $CI->mdl_clients->get_payments(['payment_id' => $paymentId]);
    }
    $message['id'] = $paymentId;
    $message['message'] = getPaymentMessage($thePayment[0], $updateData);
    return $message;
}

function getPaymentMessage($oldPayment, $newPayment)
{
    $message = '';
    if ($oldPayment['payment_amount'] != $newPayment['payment_amount']) {
        $message .= '<li>';
        $message .= 'Payment amount From: "' . $oldPayment['payment_amount'] . '" To: "' . $newPayment['payment_amount'] . '"';
        $message .= '</li>';
    }
//    if ($oldPayment['payment_method_int'] != $newPayment['payment_method_int']) {
//        $message .= '<li>';
//        $message .= 'Payment method From: "' . config_item('payment_methods')[$oldPayment['payment_method_int']] . '" To: "' . config_item('payment_methods')[$newPayment['payment_method_int']] . '"';
//        $message .= '</li>';
//    }
    if ($oldPayment['payment_type'] != $newPayment['payment_type']) {
        $message .= '<li>';
        $message .= 'Payment type From: "' . $oldPayment['payment_type'] . '" To: "' . $newPayment['payment_type'] . '"';
        $message .= '</li>';
    }
    if (!empty($message)){
        $message = '<ul>' . $message . '</ul>';
    }
    return $message;
}

function deletePaymentInDB($paymentQbId)
{
    $CI = &get_instance();
    $CI->load->model('mdl_clients');
    $where = [
        'payment_qb_id' => $paymentQbId
    ];
    $thePayment = $CI->mdl_clients->get_payments($where);
    foreach ($thePayment as $payment)
        $CI->mdl_clients->delete_payment($payment['payment_id']);
}

function addAddressToDisplayNameForQB($client, $clientId = false)
{
    if (isset($client["DisplayName"]))
        $client["DisplayName"] .= ", " . $client["BillAddr"]["Line1"];
    if ($clientId)
        $client["DisplayName"] .= ", " . $clientId;
    return $client;
}

function getAccountRef($dataService)
{
    $accounts = query('Account', $dataService);
    if (!$accounts)
        return 'refresh';
    foreach ($accounts as $account) {
        if ($account->Name == 'Services')
            return $account->Id;
    }
    return null;
}

function removeInnDB()
{
    $CI = &get_instance();
    $CI->load->model('mdl_clients');
    $clients = $CI->mdl_clients->get_clients()->result_array();
    $CI->mdl_services->delete_by('service_id >= 0');
    foreach ($clients as $client) {
        $clientId = $client['client_id'];
        $CI->mdl_clients->complete_client_removal($clientId);
//            $cc = $CI->mdl_clients->get_client_contacts(['cc_client_id' => $clientId]);
//            foreach ($cc as $record)
//                $CI->mdl_clients->delete_client_contact($record['cc_id']);
    }
}

function getPaymentMethods($dataService)
{
    $paymentMethods = query('PaymentMethod', $dataService);
    if (!$paymentMethods)
        return false;
    $methods = [];
    foreach ($paymentMethods as $paymentMethod) {
        if ($paymentMethod->Name == 'Cash')
            $methods[1] = $paymentMethod->Id;
        elseif ($paymentMethod->Name == 'Credit Card')
            $methods[2] = $paymentMethod->Id;
        elseif ($paymentMethod->Name == 'Cheque')
            $methods[3] = $paymentMethod->Id;
        elseif ($paymentMethod->Name == 'Check')
            $methods[3] = $paymentMethod->Id;
        elseif ($paymentMethod->Name == 'Direct Debit')
            $methods[4] = $paymentMethod->Id;
        elseif ($paymentMethod->Name == 'Visa')
            $methods['visa'] = $paymentMethod->Id;
        elseif ($paymentMethod->Name == 'MasterCard')
            $methods['mastercard'] = $paymentMethod->Id;
        elseif ($paymentMethod->Name == 'Discover')
            $methods['discover'] = $paymentMethod->Id;
        elseif ($paymentMethod->Name == 'American Express')
            $methods['amex'] = $paymentMethod->Id;

        if($paymentMethod->Type == 'CREDIT_CARD')
            $methods['CREDIT_CARD'][] = $paymentMethod->Id;
        elseif($paymentMethod->Type == 'NON_CREDIT_CARD')
            $methods['NON_CREDIT_CARD'][] = $paymentMethod->Id;
    }
    return $methods;
}

function getTaxCodeRef($taxesInQB, $taxRateRef)
{
    $taxCodeRef = '';
    foreach ($taxesInQB as $tax) {
        if ($tax->Active && (!empty($tax->SalesTaxRateList) && is_object($tax->SalesTaxRateList->TaxRateDetail) && $tax->SalesTaxRateList->TaxRateDetail->TaxRateRef == $taxRateRef || !empty($tax->PurchaseTaxRateList) && is_object($tax->PurchaseTaxRateList->TaxRateDetail) && $tax->PurchaseTaxRateList->TaxRateDetail->TaxRateRef == $taxRateRef)) {
            $taxCodeRef = $tax->Id;
            break;
        }
    }
    return $taxCodeRef;
}

function checkQbIdInDB($module, $qbId)
{
    $CI = &get_instance();
    $CI->load->model('mdl_clients');
    $CI->load->model('mdl_invoices');
    $CI->load->model('mdl_services');
    $result = [];
    if ($module == 'Customer')
        $result = $CI->mdl_clients->get_clients('', ['client_qb_id' => $qbId])->result_array();
    elseif ($module == 'Item')
        $result = $CI->mdl_services->find_all(['service_qb_id' => $qbId]);
    elseif ($module == 'Payment')
        $result = $CI->mdl_clients->get_payments(['payment_qb_id' => $qbId]);
    elseif ($module == 'Invoice')
        $result = $CI->mdl_invoices->find_by_field(['invoice_qb_id' => $qbId]);
    if (!empty($result))
        return true;
    return false;

}

function deleteLogsInTmp()
{
    $files = scandir(sys_get_temp_dir());
    foreach ($files as $file) {
        $posRes = strpos($file, '-Request-');
        $posReq = strpos($file, '-Response-');
        if (($posRes || $posReq) && file_exists(sys_get_temp_dir() . '/' . $file))
            unlink(sys_get_temp_dir() . '/' . $file);
    }
}

function getHst($hstCode)
{
    if ($hstCode == 1)
        $hst = 'NotApplicable';
    elseif ($hstCode == 2)
        $hst = 'TaxInclusive';
    else
        $hst = 'TaxExcluded';
    return $hst;
}

function getClientMessage($clientFromDB, $clientFromQB)
{
    $message = '';
    if (trim($clientFromDB->client_name) != trim($clientFromQB['client_name']))
        $message .= 'QuickBooks: Client name was modified from ' . $clientFromDB->client_name . ' to ' . $clientFromQB['client_name'] . '<br>';
    if (!empty($clientFromQB['client_type']) && trim($clientFromDB->client_type) != trim($clientFromQB['client_type']))
        $message .= 'QuickBooks: Client type was modified from ' . $clientFromDB->client_type . ' to ' . $clientFromQB['client_type'] . '<br>';
    if (trim($clientFromDB->client_address) != trim($clientFromQB['client_address']))
        $message .= 'QuickBooks: Client address was modified from ' . $clientFromDB->client_address . ' to ' . $clientFromQB['client_address'] . '<br>';
    if (trim($clientFromDB->client_city) != trim($clientFromQB['client_city']))
        $message .= 'QuickBooks: Client city was modified from ' . $clientFromDB->client_city . ' to ' . $clientFromQB['client_city'] . '<br>';
    if (trim($clientFromDB->client_state) != trim($clientFromQB['client_state']))
        $message .= 'QuickBooks: Client state was modified from ' . $clientFromDB->client_state . ' to ' . $clientFromQB['client_state'] . '<br>';
    if (trim($clientFromDB->client_zip) != trim($clientFromQB['client_zip']))
        $message .= 'QuickBooks: Client zip was modified from ' . $clientFromDB->client_zip . ' to ' . $clientFromQB['client_zip'] . '<br>';
    if (trim($clientFromDB->client_country) != trim($clientFromQB['client_country']))
        $message .= 'QuickBooks: Client country modified from ' . $clientFromDB->client_country . ' to ' . $clientFromQB['client_country'];
    return $message;
}

function getCcMessage($operation, $clientContactQB, $clientContactDB = null)
{
    $message = '';
    if ($operation == 'Create') {
        $message = 'QuickBooks: Hey, I just created contact:<br>';
        $message .= '<ul>';
        $message .= '<li>';
        $message .= 'Title: ' . $clientContactQB['cc_title'];
        $message .= '</li>';
        $message .= '<li>';
        $message .= 'Name: ' . $clientContactQB['cc_name'];
        $message .= '</li>';
        $message .= '<li>';
        $message .= 'Phone: ' . $clientContactQB['cc_phone'];
        $message .= '</li>';
        $message .= '<li>';
        $message .= 'Email: ' . $clientContactQB['cc_email'];
        $message .= '</li>';
        $message .= '</ul>';
    } elseif ($operation == 'Update') {
        if ($clientContactDB['cc_phone'] != $clientContactQB['cc_phone'] || $clientContactDB['cc_email'] != $clientContactQB['cc_email']) {
            $message = 'QuickBooks: Hey, I just updated contact:<br>';
            $message .= '<ul>';
            if ($clientContactDB['cc_phone'] != $clientContactQB['cc_phone']) {
                $message .= '<li>';
                $message .= 'Phone: ' . $clientContactDB['cc_phone'] . ' => ' . $clientContactQB['cc_phone'];
                $message .= '</li>';
            }
            if ($clientContactDB['cc_email'] != $clientContactQB['cc_email']) {
                $message .= '<li>';
                $message .= 'Email: ' . $clientContactDB['cc_email'] . ' => ' . $clientContactQB['cc_email'];
                $message .= '</li>';
            }
            $message .= '</ul>';
        }
    }
    return $message;
}

function checkBatchError($batch, $dataService)
{
    $error = $batch->getLastError();
    if ($error) {
        $statusCode = $error->getHttpStatusCode();
        if ($statusCode == 401) {
            refreshToken($dataService);
            return false;
        }
    }
    return true;
}

function createDiscountForInvoiceQBv2($invoice)
{
    if ($invoice->discount_id) {
        $percentBased = boolval($invoice->discount_percents);
        $discountForQB = [
            'Amount' => !$percentBased ? $invoice->discount_amount : '',
            'DetailType' => 'DiscountLineDetail',
            'DiscountLineDetail' => [
                'PercentBased' => $percentBased,
                'DiscountPercent' => $percentBased ? $invoice->discount_amount : ''
            ]
        ];
        return $discountForQB;
    }
    return null;
}
function getPushManualSyncDriver($module){
    switch ($module) {
        case 'item':
            $driverName = 'quickbooks/item/syncserviceinqb';
            break;
        case 'invoice':
            $driverName = 'quickbooks/invoice/syncinvoiceinqb';
            break;
        case 'payment':
            $driverName = 'quickbooks/payment/syncpaymentinqb';
            break;
        case 'customer':
            $driverName = 'quickbooks/client/syncclientinqb';
            break;
        case 'class':
            $driverName = 'quickbooks/class/syncclassinqb';
            break;
        default:
            $driverName = '';
    }
    return $driverName;
}
function getPullManualSyncDriver($module){
    switch ($module) {
        case 'item':
            $driverName = 'quickbooks/item/syncserviceindb';
            break;
        case 'invoice':
            $driverName = 'quickbooks/invoice/syncinvoiceindb';
            break;
        case 'payment':
            $driverName = 'quickbooks/payment/syncpaymentindbv2';
            break;
        case 'customer':
            $driverName = 'quickbooks/client/syncclientindb';
            break;
        case 'class':
            $driverName = 'quickbooks/class/syncclassindb';
            break;
        default:
            $driverName = '';
    }
    return $driverName;
}
function getExportDriverClass($name)
{
    switch ($name) {
        case 'item':
            $driverName = 'quickbooks/item/syncservicesfromqb';
            break;
        case 'invoice':
            $driverName = 'quickbooks/invoice/exportinvoicesv3';
            break;
        case 'payment':
            $driverName = 'quickbooks/payment/exportpaymentsv3';
            break;
        case 'paymentRounding':
            $driverName = 'quickbooks/payment/exportpaymentsforrouding';
            break;
        case 'interest':
            $driverName = 'quickbooks/invoice/exportinterestinqb';
            break;
        default:
            $driverName = 'quickbooks/client/exportclientsv3';
    }
    return $driverName;
}

function getImportDriverClass($name)
{
    switch ($name) {
        case 'estimate':
            $driverName = 'quickbooks/estimate/importestimates';
            break;
        case 'customer':
            $driverName = 'quickbooks/client/importclients';
            break;
        case 'class':
            $driverName = 'quickbooks/class/importclasses';
            break;
        case 'category':
            $driverName = 'quickbooks/category/importcategories';
            break;
        case 'invoice':
            $driverName = 'quickbooks/invoice/importinvoices';
            break;
        default:
            $driverName = 'quickbooks/client/importinactiveclients';
    }
    return $driverName;
}

function customQuery(QueryMessage $message, $dataService)
{
    // Run a query
    $queryString = $message->getString();
    $entities = $dataService->Query($queryString);
    $error = checkError($dataService);
    if ($error)
        return $entities;
    return 'refresh';
}

function getPaymentForQBFromInvoiceQB($invoice)
{
    $paymentForQB = [
        'CustomerRef' => $invoice->CustomerRef,
        'TotalAmt' => '0.01',
        'Line' => [
            'Amount' => '0.01',
            'LinkedTxn' => [
                'TxnId' => $invoice->Id,
                'TxnType' => 'invoice'
            ]
        ]
    ];
    return $paymentForQB;
}

function getServicesAmount($items)
{
    $servicesAmount = 0;
    if (!$items || !is_array($items) || empty($items))
        return $servicesAmount;
    foreach ($items as $item) {
        if($item['is_bundle'] == 0)
            $servicesAmount += $item['service_price'];
    }
    return $servicesAmount;
}

function getInvoiceInterestToQB($items, $amount, $taxRateRef, $taxValue, $hst)
{
    $totalTax = $amount * ($taxValue / 100);
    $invoice = [
        "GlobalTaxCalculation" => $hst,
        "Line" => $items,
        "TxnTaxDetail" => [
            'TotalTax' => $totalTax,
            'TaxLine' => [
                'Amount' => $totalTax,
                'DetailType' => 'TaxLineDetail',
                'TaxLineDetail' => [
                    'TaxRateRef' => $taxRateRef,
                    'NetAmountTaxable' => $amount
                ]
            ],
        ],
    ];
    return $invoice;
}

function createServiceForInvoiceInterestQB($price, $discountPercents, $discount, $serviceId, $term, $overdueDate, $overdueRate)
{
    if ($discountPercents)
        $price /= (100 - $discount) / 100;
    $item = [
        'service_id' => $serviceId,
        'service_description' => 'Interest Of Month \'' . date_format(date_sub(date_create($overdueDate), date_interval_create_from_date_string($term . ' days')), 'Y-m-d') .
            '\' is ' . $overdueRate . '%',
        'service_price' => round($price, 3),
        'qbId' => true,
        'non_taxable' => 0
    ];
    return $item;
}

function getTaxRateRefFromTaxCode($taxesInQB, $taxRateRef)
{
    $taxRate = '';
    foreach ($taxesInQB as $tax) {
        if ($tax->SalesTaxRateList->TaxRateDetail->TaxRateRef == $taxRateRef || $tax->PurchaseTaxRateList->TaxRateDetail->TaxRateRef == $taxRateRef) {
            $taxRate = $tax->SalesTaxRateList->TaxRateDetail->TaxRateRef;
            break;
        }
    }
    return $taxRate;
}

function createOrUpdateInterestItem($dataService)
{
    $CI = &get_instance();
    $CI->load->model('mdl_settings_orm');
    $setting = $CI->mdl_settings_orm->get_by('stt_key_name', 'interest');
    if (!$setting) {
        return createInterestItem($dataService);
    }
    $theService = getQBEntityById('Item', $setting->stt_key_value, $dataService);
    if (!$theService) {
        $error = $dataService->getLastError();
        if ($error)
            if ($error->getIntuitErrorMessage() == 'Object Not Found') {
                $CI->mdl_settings_orm->delete($setting->stt_id);
                return createInterestItem($dataService);
            }
        return FALSE;
    } elseif ($theService->Active == 'false') {
        $CI->mdl_settings_orm->delete($setting->stt_id);
        return createInterestItem($dataService);
    }
    return $setting->stt_key_value;
}

function createInterestItem($dataService)
{
    $CI = &get_instance();
    $CI->load->model('mdl_settings_orm');
    $accountRef = getAccountRef($dataService);
    $serviceArray = [
        "Name" => 'Interest Of Month',
        "IncomeAccountRef" => $accountRef,
        "Type" => "NonInventory",
        "Active" => true
    ];
    $serviceObject = Item::create($serviceArray);
    $qbId = createRecordInQBFromObject($serviceObject, $dataService);
    if ($qbId == 'AuthenticationFailed' || $qbId == 'AuthorizationFailed')
        return FALSE;
    $data = [
        'stt_key_name' => 'interest',
        'stt_key_value' => $qbId,
        'stt_is_hidden' => true
    ];
    $CI->mdl_settings_orm->insert($data, true);
    return $qbId;
}

function createServiceInQB($itemObj, $dataService)
{
    $accountRef = getAccountRef($dataService);
    if ($accountRef == 'refresh')
        return FALSE;
    if (!$accountRef) {
        $account = [
            'Name' => 'Services',
            'AccountType' => 'Income'
        ];
        $obj = Account::create($account);
        $accountRef = createRecordInQBFromObject($obj, $dataService);
    }
    if (!$accountRef)
        return FALSE;
    $serviceForQB = createServiceForQB($itemObj, $accountRef);
    $serviceObject = Item::create($serviceForQB);
    $qbId = createRecordInQBFromObject($serviceObject, $dataService,false, false, $itemObj->service_id);
    return $qbId;
}

function createClientInQB($clientObj, $dataService)
{
    $CI = &get_instance();
    $CI->load->model('mdl_clients');
    $clientForQB = createClientForQB($clientObj);
    if(empty($clientForQB))
        return false;
    $clientObject = Customer::create($clientForQB);
    $clientId = $clientObj->client_id;
    $qbId = createRecordInQBFromObject($clientObject, $dataService, false, false, $clientId);
    if ($qbId == "AuthenticationFailed" || $qbId == 'AuthorizationFailed')
        return FALSE;
    elseif ($qbId == 'duplicate') {
        $newClientForQB = addAddressToDisplayNameForQB($clientForQB);
        $newClientObject = Customer::create($newClientForQB);
        $qbId = createRecordInQBFromObject($newClientObject, $dataService, false, false, $clientId);
    }
    if ($qbId == 'duplicate') {
        $newClientForQB = addAddressToDisplayNameForQB($clientForQB, $clientId);
        $newClientObject = Customer::create($newClientForQB);
        $qbId = createRecordInQBFromObject($newClientObject, $dataService, false, false, $clientId);
    }
    if ($qbId == 'AuthenticationFailed' || $qbId == 'AuthorizationFailed')
        return FALSE;
    elseif ($qbId && $qbId != 'duplicate') {
        $where = [
            'client_id' => $clientId
        ];
        $updateData = [
            'client_qb_id' => $qbId
        ];
        $CI->mdl_clients->update_client($updateData, $where);
        return $qbId;
    }
    return FALSE;
}

function createInvoiceInQB($invoice, $dataService)
{
    $CI = &get_instance();
    $CI->load->model('mdl_estimates');
    $CI->load->model('mdl_invoices');
    $settings = getQbSettings();
    $items = $CI->mdl_estimates->find_estimate_services($invoice->estimate_id, ['estimates_services.service_status' => 2]);
    $estimate = $CI->mdl_estimates->find_by_id($invoice->estimate_id);
    $taxesInQB = query('TaxRate', $dataService);
    if (!$taxesInQB)
        return FALSE;
    $taxRateRef = getTaxRateRef($settings['tax_rate'], $taxesInQB);
    $taxesInQB = query('TaxCode', $dataService);
    $taxCodeRef = getTaxCodeRef($taxesInQB, $taxRateRef);
    $hst = getHst($estimate->estimate_hst_disabled);
    $itemsToQB = createServicesForInvoiceQB($items, $taxCodeRef, $estimate->estimate_hst_disabled, $settings['tax_rate'], $dataService);
    if (!$itemsToQB)
        return FALSE;
    $discount = $CI->mdl_clients->get_discount(['discounts.estimate_id' => $invoice->estimate_id]);
    $discountToQB = createDiscountForInvoiceQB($discount);
    if ($discountToQB && $estimate->estimate_hst_disabled != 2) {
        $itemsToQB[] = $discountToQB;
    }
    $customerFromDB = $CI->mdl_clients->get_client_by_id($invoice->client_id);
    $customerId = $customerFromDB->client_qb_id;
    if (!$customerId) {
        $customerId = createClientInQB($customerFromDB, $dataService);
        if (!$customerId)
            return FALSE;
    }
    $invoiceNO = $invoice->invoice_no;
    if (!empty($settings['prefix']))
        $invoiceNO = $settings['prefix'] . $invoiceNO;
    $description = $invoice->invoice_notes;
    $date = new DateTime($invoice->date_created);
    $dueDate = new DateTime($invoice->overdue_date);
    $invoiceToQB = createInvoiceForQB($customerId, $itemsToQB, $invoiceNO, $description, $date->format('Y-m-d '), $dueDate->format('Y-m-d '), $hst);

    $invoiceObject = Invoice::create($invoiceToQB);
    $qbId = createRecordInQBFromObject($invoiceObject, $dataService, false, false, $invoice->id);
    $where = [
        'id' => $invoice->id
    ];
    if ($qbId == 'AuthenticationFailed' || $qbId == 'AuthorizationFailed')
        return FALSE;
    elseif ($qbId == 'duplicate') {
        $invoice = $CI->mdl_invoices->find_by_id($invoice->id);
        $qbId = $invoice->invoice_qb_id;
    }
    if (!$qbId) {
        $updateData = [
            'invoice_qb_id' => 0
        ];
        $CI->mdl_invoices->update_invoice($updateData, $where);
        return false;
    } else {
        $updateData = [
            'invoice_qb_id' => $qbId
        ];
    }
    $CI->mdl_invoices->update_invoice($updateData, $where);
    return $qbId;
}

function createInvoiceInDB($invoiceQBid, $dataService, $settings)
{
    $CI = &get_instance();
    $CI->load->model('mdl_estimates');
    $CI->load->model('mdl_leads');
    $CI->load->model('mdl_workorders');
    $CI->load->model('mdl_invoices');
    $CI->load->model('mdl_estimates_bundles');

    $invoice = findByIdInQB('Invoice', $invoiceQBid, $dataService);
    if (!$invoice)
        return FALSE;
    if (!empty($invoice->DepartmentRef)) {
        $checkLocation = checkLocation($invoice->DepartmentRef);
        if (!$checkLocation)
            return TRUE;
    }
    $clientId = getClientId($invoice->CustomerRef);
    if (!$clientId)
        $clientId = createClientInDB($invoice->CustomerRef, $dataService);
    if (!$clientId)
        return FALSE;
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

    //Lead
    $lead = getLeadToDB($invoice);
    //Invoice Services
    $services = $invoice->Line;
//    $theInvoice = $this->CI->mdl_invoices->find_by_field(['invoice_qb_id' => $payload['qbId']]);
    //create Lead
    $leadId = $CI->mdl_leads->insert_leads($lead);
    $leadNO = getLeadNO($leadId);
    $CI->mdl_leads->update_leads($leadNO, ['lead_id' => $leadId]);
    make_notes($clientId, 'Quickbooks: I just created a new lead "' . $leadNO['lead_no'] . '" for the client. ', $type = 'system', $lead_id = NULL);

    // create Estimate
    $estimate = getEstimateToDB($invoice, $leadId);
    if (count(array_filter($tax)) >= 2)
        $estimate = array_merge($estimate, $tax);
    $estimateId = $CI->mdl_estimates->insert_estimates($estimate);
    make_notes($clientId, 'Quickbooks: I just created a new estimate "' . $estimate['estimate_no'] . '" for the client. ', $type = 'system', $lead_id = NULL);

    //create Estimate Services
//    $estimateServices = getEstimateServicesToDB($services, $estimateId, $dataService);
//    foreach ($estimateServices as $estimateService) {
//        $CI->mdl_estimates->insert_estimate_service($estimateService);
//    }

    //create Estimate Services
    $estimateServices = getEstimateServicesToDB($services, $estimateId, $dataService, $settings['us'], $invoice->GlobalTaxCalculation, isset($tax['estimate_tax_rate']) ? $tax['estimate_tax_rate'] : 1);
    foreach ($estimateServices as $estimateService) {
        $estimateBundleServices = !empty($estimateService['bundle_records']) ? $estimateService['bundle_records'] : [];
        unset($estimateService['bundle_records']);
        $estimateServiceId = $CI->mdl_estimates->insert_estimate_service($estimateService);
        if(!empty($estimateBundleServices))
            foreach ($estimateBundleServices as $record){
                $estimateBundleServiceId = $CI->mdl_estimates->insert_estimate_service($record);
                if(!empty($estimateServiceId) && !empty($estimateBundleServiceId)) {
                    $estimateBundle = [
                        'eb_service_id' => $estimateBundleServiceId,
                        'eb_bundle_id' => $estimateServiceId
                    ];
                    $CI->mdl_estimates_bundles->insert($estimateBundle);
                }
            }

    }

    // create discount
    $discount = getDiscountToDB($services, $estimateId);
    if (is_array($discount) && !empty($discount)) {
        $CI->mdl_clients->insert_discount($discount);
    }

    // create work orders
    $workOrderNumber = getNO($leadId, 'W');
    $workOrder = getWorkOrderToDB($clientId, $estimateId, $workOrderNumber, $qbInvoiceNO, $invoice);
    $workOrderId = $CI->mdl_workorders->insert_workorders($workOrder);
    make_notes($clientId, 'Quickbooks: I just created a new work order "' . $workOrderNumber . '" for the client. ', $type = 'system', $lead_id = NULL);

    // create invoice
    $invoiceNumber = getNO($leadId, 'I');
    $invoice = getInvoiceToDB($invoiceNumber, $workOrderId, $estimateId, $clientId, $invoice, 'create');
    $invoiceDBid = $CI->mdl_invoices->insert_invoice($invoice);
    make_notes($clientId, 'Quickbooks: I just created a new invoice "' . $invoiceNumber . '" for the client. ', $type = 'system', $lead_id = NULL);

//    pushJob('quickbooks/invoice/syncinvoiceinqb', serialize(['id' => $invoiceDBid, 'qbId' => $invoiceQBid, 'no' => 'true']));
    createQBLog('invoice', 'create', 'pull', $invoiceDBid);
    return $estimateId;
}


function getPaymentIdByQBid($paymentQBid)
{
    $CI = &get_instance();
    $CI->load->model('mdl_clients');
    $payment = $CI->mdl_clients->get_payments(['payment_qb_id' => $paymentQBid]);
    if ($payment) {
        return $payment[0]['payment_id'];
    }
    return FALSE;
}

function createClientInDB($clientQbId, $dataService)
{
    $CI = &get_instance();
    $CI->load->model('mdl_clients');

    $customer = findByIdInQB('Customer', $clientQbId, $dataService);
    if (!$customer)
        return FALSE;
    $customerArr[] = $customer;
    $clientsToDB = getAllCustomerToDB($customerArr);
    $clientsContacts = getAllClientsContactsToDB($customerArr);

    $clientId = $CI->mdl_clients->add_new_client_with_data($clientsToDB[0]);
    $message = 'QuickBooks: Hey, I just created a new client.';
    $clientsContacts = addClientIdToClientsContacts($clientsContacts, $clientsToDB[0]['client_name'], $clientId);

    make_notes($clientId, $message, $type = 'system', $lead_id = NULL);
    actionsWithClientsContacts($clientsContacts);
    return $clientId;
}

function actionsWithClientsContacts($clientsContacts)
{
    $CI = &get_instance();
    $CI->load->model('mdl_clients');
    foreach ($clientsContacts as $clientContact) {
        $clientId = $clientContact['cc_client_id'];
        $message = '';
        $where = [
            'cc_client_id' => $clientId,
            'cc_title' => $clientContact['cc_title']
        ];
        $cc = $CI->mdl_clients->get_client_contact($where);
        if (!empty($cc)) {
            $CI->mdl_clients->update_client_contact($clientContact, $where);
            $message = getCcMessage('Update', $clientContact, $cc);
        } else {
            $CI->mdl_clients->add_client_contact($clientContact);
            $message = getCcMessage('Create', $clientContact);
        }
        if ($message)
            make_notes($clientId, $message, $type = 'system', $lead_id = NULL);
    }
}

function checkLocation($qbLocation)
{
    $locationFromDB = config_item('Location');
    if (!$locationFromDB)
        return true;
    $arrLocationFromDB = explode(',', $locationFromDB);
    return in_array($qbLocation, $arrLocationFromDB);
}

function createAttachment($dataService, $json, $type, $typeId)
{
    if (!$dataService || !$json || !$type || !$typeId)
        return FALSE;
    $entityRef = new IPPReferenceType(array('value' => $typeId, 'type' => $type));
    $attachableRef = new IPPAttachableRef(array('EntityRef' => $entityRef, 'FileName' => 'test.txt'));
    $objAttachable = new IPPAttachable();
    $objAttachable->FileName = "Arbostar.txt";
    $objAttachable->AttachableRef = $attachableRef;
    $resultObj = $dataService->Upload($json,
        $objAttachable->FileName,
        'text/plain',
        $objAttachable);
    return $resultObj;
}

function getAttachment($dataService, $type, $typeId)
{
    if (!$dataService || !$type || !$typeId)
        return FALSE;
    $oneQuery = new QueryMessage();
    $oneQuery->sql = "SELECT";
    $oneQuery->entity = "attachable";
    $oneQuery->whereClause = [
        "AttachableRef.EntityRef.Type = '$type'",
        "AttachableRef.EntityRef.value = '$typeId'",
        "FileName = 'Arbostar.txt'"
    ];
    $result = customQuery($oneQuery, $dataService);
    if ($result == 'refresh')
        return 'refresh';
    elseif (!$result)
        return FALSE;
    $client = new GuzzleHttp\Client();
    $res = $client->request('GET', $result[0]->TempDownloadUri);
    if (!$res)
        return FALSE;
    if ($res->getStatusCode() == 200)
        return $res->getBody();
    return FALSE;
}

function getDataForCustomerAttachmentInQB($customerId)
{
    $CI = &get_instance();
    $CI->load->model('mdl_clients');
    $client = $CI->mdl_clients->get_client_by_id($customerId);
    $cc = $CI->mdl_clients->get_client_contacts('cc_client_id = ' . $client->client_id);
    $dataForAttachmentQB['client_lng'] = $client->client_lng;
    $dataForAttachmentQB['client_lat'] = $client->client_lat;
    $dataForAttachmentQB['cc'] = $cc;
    $dataForAttachmentQB['ccMd5'] = md5(json_encode($cc));

    return $dataForAttachmentQB;
}

function checkPaymentInDB($payments, $invoiceQbId)
{
    if (empty($payments))
        return false;

    if (is_array($payments)) {
        foreach ($payments as $payment) {
            $checkPayment = checkPayment($payment, $invoiceQbId);
            if ($checkPayment)
                return true;
        }
    } else {
        return checkPayment($payments, $invoiceQbId);
    }
    return false;
}

function checkPayment($payment, $invoiceQbId)
{
    $CI = &get_instance();
    $CI->load->model('mdl_invoices');
    $result = $CI->mdl_invoices->find_all(['estimate_id' => $payment['estimate_id']]);
    $invoice = array_shift($result);
    if (!empty($invoice) && $invoice->invoice_qb_id == $invoiceQbId)
        return true;
    return false;
}

function getDepositToDB($estimateId, $totalAmt, $paymentMethod, $paymentMethods, $paymentType = 'deposit')
{
    $dateCreate = new DateTime();
    $paymentChecked = 1;
    $paymentMethodInt = array_search($paymentMethod, $paymentMethods);
    $paymentToDB = [
        'estimate_id' => $estimateId,
        'payment_method_int' => $paymentMethodInt,
        'payment_date' => $dateCreate->getTimestamp(),
        'payment_amount' => $totalAmt,
        'payment_checked' => $paymentChecked,
        'payment_type' => $paymentType,
        'payment_qb_id' => 0
    ];
    return $paymentToDB;
}

function getShipAddrFromCustomer($estimate)
{
    if (is_object($estimate)) {
        return $estimate->lead_address . ', ' . $estimate->lead_city . ', ' . $estimate->lead_state . ', ' . $estimate->lead_zip;
    }
    return null;
}

function getTaxToDbEstimate($invoiceTax, $taxFromQB)
{
    if (empty($invoiceTax) || empty($taxFromQB))
        return null;
    $taxPercent = 0;
    if(is_array($invoiceTax->TaxLine)) {
        foreach ($invoiceTax->TaxLine as $taxLine) {
            $taxPercent += $taxLine->TaxLineDetail->TaxPercent;
        }
    } elseIf(is_object($invoiceTax->TaxLine))
        $taxPercent = $invoiceTax->TaxLine->TaxLineDetail->TaxPercent;
    return [
        'estimate_tax_name' => $taxFromQB->Name,
        'estimate_tax_value' => $taxPercent,
        'estimate_tax_rate' => $taxPercent / 100 + 1
    ];
}

function getTaxToDbEstimateFromTaxLine($taxLine, $dataService)
{
    if (empty($taxLine))
        return null;
    $taxPercent = 0;
    $name = '';
    if (is_array($taxLine)) {
        foreach ($taxLine as $line) {
            $taxPercent += $line->TaxLineDetail->TaxPercent;
            if ($taxPercent > 0) {
                $taxRate = getQBEntityById('TaxRate', $line->TaxLineDetail->TaxRateRef, $dataService);
                if (!empty($taxRate->Name))
                    $name = $taxRate->Name;
                break;
            }
        }
    } else{
        $taxPercent = $taxLine->TaxLineDetail->TaxPercent;
        $taxRate = getQBEntityById('TaxRate', $taxLine->TaxLineDetail->TaxRateRef, $dataService);
        if (!empty($taxRate->Name))
            $name = $taxRate->Name;
    }
    return [
        'estimate_tax_name' => $name,
        'estimate_tax_value' => $taxPercent,
        'estimate_tax_rate' => $taxPercent / 100 + 1
    ];
}

function debug2($record)
{
    echo '<pre>';
    print_r($record);
    echo '</pre>';
}

function getBundlesRecordsForDB(array $recordsFromQB, $bundleId, $dataService)
{
    $CI = &get_instance();
    $CI->load->model('mdl_services');
    $result = [];
    foreach ($recordsFromQB as $record){
        $itemsArray = [];
        if(empty($record->ItemRef))
            continue;
        $recordFromDB = $CI->mdl_services->find_all(['service_qb_id' => $record->ItemRef]);
        $cost = !empty($recordFromDB[0]->cost) ? $recordFromDB[0]->cost : 0;
        if(empty($recordFromDB[0]->service_id)){
            $items = findByIdInQB('Item', $record->ItemRef, $dataService);
            $itemsArray[] = $items;
            $itemsToDB = getAllItemsToDB($itemsArray);
            $serviceId = $CI->mdl_services->insert($itemsToDB[0]);
            $cost = !empty($itemsToDB[0]['cost']) ? $itemsToDB[0]['cost'] : 0;
        }
        $id = isset($recordFromDB[0]->service_id) ? $recordFromDB[0]->service_id : $serviceId;
        $recordToDB = [
            'bundle_id' => $bundleId,
            'service_id' => $id,
            'qty' => $record->Qty,
            'cost' => $record->Qty * $cost
        ];
        $result[] = $recordToDB;
    }
    return $result;
}

function getBundleRecordsForEstimateDB($bundleRecords, $dataService, $us, $bundleId)
{
    $CI = &get_instance();
    $CI->load->model('mdl_services');
    $CI->load->model('mdl_bundles_services');
    $result = [];
    if(is_object($bundleRecords)) {
        $bundleRecordsArr[] = $bundleRecords;
        $bundleRecords = $bundleRecordsArr;
    }
    foreach ($bundleRecords as $record){
        $serviceFromDb = getServiceByQbId($record->SalesItemLineDetail->ItemRef);
        $serviceId = null;
        if (!$serviceFromDb) {
            $items = findByIdInQB('Item', $record->SalesItemLineDetail->ItemRef, $dataService);
            $itemsArray[] = $items;
            $itemsToDB = getAllItemsToDB($itemsArray);
            $serviceId = $CI->mdl_services->insert($itemsToDB[0]);
            $bundleServicesForDB = [
                'bundle_id' => $bundleId,
                'service_id' => $serviceId,
                'qty' => $record->SalesItemLineDetail->Qty ?: 1
            ];
            $CI->mdl_bundles_services->insert($bundleServicesForDB);
        }
        $id = isset($serviceFromDb[0]->service_id) ? $serviceFromDb[0]->service_id : $serviceId;
        if (!$id)
            continue;
        if ($us)
            $nonTaxable = $record->SalesItemLineDetail->TaxCodeRef == 'NON' ? 1 : 0;
        else{
            $taxRate = getTaxRateValue($dataService, $record->SalesItemLineDetail->TaxCodeRef);
            $nonTaxable = $taxRate == 0 ? 1 : 0;
        }
        $result[] = [
            'estimate_service_id' => null,
            'bundle_id' => $bundleId,
            'estimate_bundle_record_qty' => $record->SalesItemLineDetail->Qty ?: 1,
            'estimate_bundle_record_cost' => $record->SalesItemLineDetail->UnitPrice ?: 0,
            'estimate_bundle_record_description' => $record->Description ?: '',
            'non_taxable' => $nonTaxable,
            'record_id' => $id,
        ];
    }
    return $result;
}
function addServiceToBundle($serviceQbId){
    $resultArr = [];
    if(!empty($serviceQbId)){
        $resultArr = [
            'Description' => '',
            'Amount' => 0,
            'DetailType' => 'SalesItemLineDetail',
            'SalesItemLineDetail' => [
                'ItemRef' => $serviceQbId,
                'UnitPrice' => 0,
                'Qty' => 1,
                'TaxCodeRef' => 'Non',
            ]
        ];
    }
    return $resultArr;
}
function getQbEstimateServicesForAddQbIdToDb($estimateServices, $qbInvoiceServices){
    foreach ($estimateServices as $key => $value){
        if(isset($value['qbId']))
            continue;
        foreach ($qbInvoiceServices as $qbKey => $qbValue){
            if($qbValue->DetailType == 'GroupLineDetail'){
                if ($value['service_qb_id'] == $qbValue->GroupLineDetail->GroupItemRef && trim($value['service_description']) == trim($qbValue->Description)) {
                    $estimateServices[$key]['estimate_service_qb_id'] = $qbValue->Id;
                    if(empty($qbValue->GroupLineDetail->Line))
                        unset($qbInvoiceServices[$qbKey]);
                    break;
                } else{
                    if (is_array($qbValue->GroupLineDetail->Line)) {
                        $check = false;
                        foreach ($qbValue->GroupLineDetail->Line as $bKey => $bValue) {
                            if ($value['service_qb_id'] == $bValue->SalesItemLineDetail->ItemRef && trim($value['service_description']) == trim($bValue->Description) && $value['service_price'] == $bValue->Amount) {
                                $estimateServices[$key]['estimate_service_qb_id'] = $bValue->Id;
                                unset($qbInvoiceServices[$qbKey]->GroupLineDetail->Line[$bKey]);
                                $check = true;
                                break;
                            }
                        }
                        if($check)
                            break;
                    } else {
                        if ($value['service_qb_id'] == $qbValue->GroupLineDetail->Line->SalesItemLineDetail->ItemRef && trim($value['service_description']) == trim($qbValue->GroupLineDetail->Line->Description) && $value['service_price'] == $qbValue->GroupLineDetail->Line->Amount) {
                            $estimateServices[$key]['estimate_service_qb_id'] = $qbValue->GroupLineDetail->Line->Id;
                            unset($qbInvoiceServices[$qbKey]);
                            break;
                        }
                    }
                }
            } elseif ($qbValue->DetailType == 'SalesItemLineDetail') {
                if ($value['service_qb_id'] == $qbValue->SalesItemLineDetail->ItemRef && trim($value['service_description']) == trim($qbValue->Description) && $value['service_price'] == $qbValue->Amount) {
                    $estimateServices[$key]['estimate_service_qb_id'] = $qbValue->Id;
                    unset($qbInvoiceServices[$qbKey]);
                    break;
                }
            }
        }
    }
    return $estimateServices;
}

function getLeadToDBv2($document, $client, $clientId)
{
    if (is_object($document)) {
        $CI =& get_instance();
        $CI->load->model('mdl_leads_status');
//        $client = $CI->mdl_clients->get_clients('', 'client_qb_id = ' . $document->CustomerRef)->row();
        $leadStatusName = 'Estimated';
        $leadStatus = $CI->mdl_leads_status->get_by(['lead_status_name' => $leadStatusName]);
        $leadRefferedBy = 'Quickbooks';
        $timing = 'Right Away';
        $dateCreate = new DateTime($document->MetaData->CreateTime);
        $lead = [
            'client_id' => $clientId,
            'lead_address' => $client->BillAddr->Line1 ? $client->BillAddr->Line1 : ' ',
            'lead_city' => $client->BillAddr->City ? $client->BillAddr->City : ' ',
            'lead_state' => !empty($client->BillAddr->CountrySubDivisionCode) ? $client->BillAddr->CountrySubDivisionCode: '',
            'lead_zip' => $client->BillAddr->PostalCode ? $client->BillAddr->PostalCode : '',
            'timing' => $timing,
            'lead_status' => $leadStatusName,
            'lead_reffered_by' => $leadRefferedBy,
            'lead_status_id' => $leadStatus->lead_status_id,
            'lead_date_created' => $dateCreate->format('Y-m-d H:i:s')
        ];
        return $lead;
    }
    return false;
}

function setLeadCustomFieldsToDB(array $leadToDB, $invoiceFromQB){
    $fields = config_item('qb_sync_custom_fields_in_db');
    if(!empty($invoiceFromQB) && !empty($fields)){
        $fields = json_decode($fields, true);
        if(is_array($fields) && !empty($fields['leads']) && is_array($fields['leads'])){
            foreach ($fields['leads'] as $key => $val){
                if($key == 'lead_reffered_by' && isset($invoiceFromQB->$val)){
                    $reference = Reference::where('name', $invoiceFromQB->$val)->first();
                    if(!empty($reference))
                        $leadToDB[$key] = $reference->id;
                }
                if($key == 'lead_estimator'){
                    $definitionId = $val['CustomField'] ?? '';
                    $valueType = $val['valueType'] ?? '';
                    if(!empty($definitionId) && !empty($valueType)) {
                        if (isset($invoiceFromQB->CustomField) && is_object($invoiceFromQB->CustomField) && isset($invoiceFromQB->CustomField->DefinitionId) && $invoiceFromQB->CustomField->DefinitionId == $definitionId){
                            $estimator = $invoiceFromQB->CustomField->$valueType;
                            $user = getQbEstimatorFromCustomField($estimator);
                            if(!empty($user))
                                $leadToDB[$key] = $user;
                        } elseif (isset($invoiceFromQB->CustomField) && is_array($invoiceFromQB->CustomField)){
                            foreach ($invoiceFromQB->CustomField as $customFieldKey => $customFieldVal){
                                if($customFieldVal->DefinitionId == $definitionId){
                                    $estimator = $customFieldVal->$valueType;
                                    $user = getQbEstimatorFromCustomField($estimator);
                                    if(!empty($user))
                                        $leadToDB[$key] = $user;
                                    continue;
                                }
                            }
                        }
                    }
                }
            }
        }
    }
    return $leadToDB;
}
function getQbEstimatorFromCustomField($estimator){
    $user = '';
    if(!empty($estimator)){
        $where = getWhereEstimator($estimator);
        if(!empty($where)) {
            $users = User::where($where)->get()->toArray();
            if(empty($users)){
                $where = getWhereEstimator($estimator, false);
                $users = User::where($where)->get()->toArray();
            }
            if(!empty($users) && is_countable($users) && count($users) == 1){
                $user = $users[0]['id'];
            }
        }
    }
    return $user;
}
function getWhereEstimator($estimator, $asc = true){
    $estimatorArr = explode(" ", $estimator);
    $where = [];
    if(!empty($estimatorArr) && $asc){
        if(!empty($estimatorArr[0]))
            $where['firstname'] = $estimatorArr[0];
        if(!empty($estimatorArr[1]))
            $where['lastname'] = $estimatorArr[1];
    } elseif (!empty($estimatorArr) && $asc == false){
        if(!empty($estimatorArr[0]))
            $where['lastname'] = $estimatorArr[0];
        if(!empty($estimatorArr[1]))
            $where['firstname'] = $estimatorArr[1];
    }
    return $where;
}

function setCustomFieldsToQB($estimateFromDB, $invoiceToQB){
    $fields = config_item('qb_sync_custom_fields_in_qb');
    if(!empty($estimateFromDB) && is_object($estimateFromDB) && !empty($fields)){
        $fields = json_decode($fields, true);
        if(is_array($fields)){
            foreach ($fields as $key => $val){
                if($key != 'CustomField'){
                    $invoiceToQB[$key] = substr($estimateFromDB->$val ?? '', 0, 31);
                } else {
                    $customFields = [];
                    if(is_array($val)) {
                        foreach ($val as $customField){
                            if(is_array($customField) && !empty($customField['Type']) && !empty($customField['DefinitionId']) && !empty($customField[$customField['ValueName']])) {
                                $customValue = $customField[$customField['ValueName']];
                                $customFields[] = [
                                    'DefinitionId' => $customField['DefinitionId'],
                                    'Type' => $customField['Type'],
                                    $customField['ValueName'] => substr($estimateFromDB->$customValue ?? '', 0, 31)
                                ];
                            }
                        }
                    }
                    $invoiceToQB[$key] = $customFields;
                }
            }
        }
    }
    return $invoiceToQB;
}
