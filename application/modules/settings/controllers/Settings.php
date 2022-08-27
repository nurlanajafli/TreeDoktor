<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

use application\modules\classes\models\QBClass;
use application\modules\clients\models\Client;
use application\modules\estimates\models\Service;
use application\modules\invoices\models\Invoice;
use application\modules\payments\models\ClientPayment;
use application\modules\references\models\Reference;
use application\modules\settings\models\Settings as ST;
use application\modules\estimates\models\Estimate;

class Settings extends MX_Controller
{

//*******************************************************************************************************************
//*************
//*************																						Tasks Controller;
//*************
//*******************************************************************************************************************	

    function __construct()
    {

        parent::__construct();

        if (!isUserLoggedIn()) {
            redirect('login');
        }

        $this->_title = SITE_NAME;

        $this->load->library('form_validation');
        $this->load->library('googlemaps');
        $this->load->library('Common/QuickBooks/QBS3FileActions');
        $this->load->library('Common/QuickBooks/desktop/QBDesktopItem');
        $this->load->library('Common/QuickBooks/desktop/QBDesktopClient');
        $this->load->library('Common/QuickBooks/desktop/QBDesktopInvoice');
        $this->load->library('Common/QuickBooks/desktop/QBDesktopPayment');
        $this->load->helper('qb_helper');
        $this->load->model('mdl_amazon_identities_orm', 'amazon_identities');
        $this->load->model('mdl_users_orm');
    }

    function index()
    {
        $data = [
            'title' => 'Company Management',
            'settings' => $this->settings->settings_by_sections(),
            'defaultEmailDriver' => config_item('default_mail_driver')
        ];

        if ($data['defaultEmailDriver'] === 'amazon') {
            $userId = $this->session->userdata('user_id');
            $data['amazonIdentities'] = $this->amazon_identities->get_all();
        }
        $classes = QBClass::whereNull('class_parent_id')->with('classes')->get();
        $classesForDrop = QBClass::where([['class_parent_id', null], 'class_active' => 1])->with('classesWithoutInactive')->get();
        if(!empty($classes)) {
            $classesForSelect2 = getClasses($classes->toArray());
        }
        if (!empty($classesForDrop)) {
            $classesForDropSelect2 = getClasses($classesForDrop->toArray());
        }
        if (!empty($classesForSelect2)) {
            $data['classes'] = $classesForSelect2;
        }
        if (!empty($classesForDropSelect2)) {
            $data['classesForParent'] = $classesForDropSelect2;
        }

        $qbDesktopClientLogsForSelect2 = $this->qbdesktopclient->getLogsDataForSelect2();
        $qbDesktopInvoiceLogsForSelect2 = $this->qbdesktopinvoice->getLogsDataForSelect2();
        $qbDesktopPaymentLogsForSelect2 = $this->qbdesktoppayment->getLogsDataForSelect2();
        $qbDesktopLogsForSelect2 = [];
        if(!empty($qbDesktopClientLogsForSelect2))
            $qbDesktopLogsForSelect2 = array_merge($qbDesktopLogsForSelect2, $qbDesktopClientLogsForSelect2);
        if(!empty($qbDesktopInvoiceLogsForSelect2))
            $qbDesktopLogsForSelect2 = array_merge($qbDesktopLogsForSelect2, $qbDesktopInvoiceLogsForSelect2);
        if(!empty($qbDesktopPaymentLogsForSelect2))
            $qbDesktopLogsForSelect2 = array_merge($qbDesktopLogsForSelect2, $qbDesktopPaymentLogsForSelect2);

        $tmp = [];
        // check unique values
        foreach ($qbDesktopLogsForSelect2 as $key => $row) {
            if (!in_array($row['id'], $tmp))
                array_push($tmp, $row['id']);
            else
                unset($qbDesktopLogsForSelect2[$key]);
        }
        sort($qbDesktopLogsForSelect2);
        $data['qbDesktopLogsForSelect2'] = $qbDesktopLogsForSelect2;

        $data['references'] = Reference::withoutAlwaysHidden()->orderBy(Reference::ATTR_DELETED_AT)->orderBy(Reference::ATTR_WEIGHT)->get();
        $this->load->view('index', $data);
    }

    function save()
    {
        $data = [
            'title' => 'Company Management',
            'defaultEmailDriver' => config_item('default_mail_driver')
        ];

        if ($data['defaultEmailDriver'] === 'amazon') {
            $userId = $this->session->userdata('user_id');
            $data['amazonIdentities'] = $this->amazon_identities->get_many_by(['user_id' => $userId]);
        }

        if ($this->input->post('stt_key_validate') && !empty($this->input->post('stt_key_validate'))) {
            foreach ($this->input->post('stt_key_validate') as $key => $value) {
                if ($value === NULL || $value === FALSE)
                    continue;

                $this->form_validation->set_rules('stt_key_value[' . $key . ']', element($key, $this->input->post('stt_label'), ''), $value);
            }
        }

        $data['settings'] = $this->settings->settings_by_sections();
        if ($this->form_validation->run() == FALSE) {
            return $this->load->view('index', $data);
        }

        if ($this->input->post('stt_key_value') && !empty($this->input->post('stt_key_value'))) {
            foreach ($this->input->post('stt_key_value') as $key => $value) {
                if (!$value === NULL || $value === FALSE)
                    continue;

                $this->settings->update_by(['stt_key_name' => $key], ['stt_key_value' => $value]);
            }
            if(!isset($this->input->post('stt_key_value')['auto_tax'])) {
                $this->settings->update_by(['stt_key_name' => 'auto_tax'], ['stt_key_value' => 0]);
            }
            if(!isset($this->input->post('stt_key_value')['schedule_show_weekend'])) {
                $this->settings->update_by(['stt_key_name' => 'schedule_show_weekend'], ['stt_key_value' => 0]);
            }
        }
        $data['references'] = Reference::withoutAlwaysHidden()->orderBy(Reference::ATTR_DELETED_AT)->get();
        $data['settings'] = $this->settings->settings_by_sections();

        redirect('settings');
    }

    function getQBLocations()
    {
        if (empty(config_item('accessToken')))
            return null;
        $dataService = dataServiceConfigure(unserialize(config_item('accessToken')));
        $qbLocations = query('Department', $dataService);
        if (!$qbLocations)
            $qbLocations = query('Department', $dataService);
        $locations = [];
        foreach ($qbLocations as $department) {
            $locations[] = ['id' => $department->Id, 'text' => $department->FullyQualifiedName];
        }
        $this->settings->update_by(['stt_key_name' => 'QBLocations'], ['stt_key_value' => serialize($locations)]);
        print_r(json_encode($locations));
        return json_encode($locations);
    }

    function getLocations()
    {
        $locations = get_locations();
        print_r(json_encode($locations));
        return json_encode($locations);
    }

    function getDateFormats()
    {
        $formats = get_date_format();
        print_r(json_encode($formats));
        return json_encode($formats);
    }

    function getTimeFormats()
    {
        $formats = get_time_format();
        print_r(json_encode($formats));
        return json_encode($formats);
    }

    function saveTax()
    {
        if (!empty($this->input->post('taxName'))) {
            $taxName = $this->input->post('taxName');
            $taxValue = round($this->input->post('taxRate'), 3);
            $taxes = json_decode(config_item('allTaxes'));
            if (!empty($this->input->post('taxId'))) {
                for ($i = 0; $i < count($taxes); $i++) {
                    $text =  $taxes[$i]->name . ' (' .  $taxes[$i]->value . '%)';
                    if ($this->input->post('taxId') == $text) {
                        $taxes[$i] = ['name' => $taxName, 'value' => $taxValue];
                        break;
                    }
                }
            } else {
                $taxes[] = ['name' => $taxName, 'value' => $taxValue];
            }
            $this->settings->update_by(['stt_key_name' => 'allTaxes'], ['stt_key_value' => json_encode($taxes)]);
        }
        $allTaxes = $this->settings->get_by(['stt_key_name' => 'allTaxes']);
        $allTaxes = all_taxes(json_decode($allTaxes->stt_key_value));
        return $this->response($allTaxes);
    }

    function getTaxForEdit()
    {
        $editTax = $this->input->get('text');
        $taxes = json_decode(config_item('allTaxes'));
        $taxForEdit = [];
        foreach ($taxes as $idx => $tax) {
            $text = $tax->name . ' (' . $tax->value . '%)';
            if ($editTax == $text) {
                $tax->taxIdx = $idx;
                $taxForEdit = $tax;

                break;
            }
        }
        return $this->response($taxForEdit);
    }

    function deleteTax()
    {
        if (!empty($this->input->post('taxId'))) {
            $taxes = json_decode(config_item('allTaxes'));
            $newTaxes = [];
            for ($i = 0; $i < count($taxes); $i++) {
                $text =  $taxes[$i]->name . ' (' .  $taxes[$i]->value . '%)';
                if ($this->input->post('taxId') != $text) {
                    $newTaxes[] = $taxes[$i];
                }
            }
            $this->settings->update_by(['stt_key_name' => 'allTaxes'], ['stt_key_value' => json_encode($newTaxes)]);
        }
        $allTaxes = $this->settings->get_by(['stt_key_name' => 'allTaxes']);
        $allTaxes = all_taxes(json_decode($allTaxes->stt_key_value));
        return $this->response($allTaxes);
    }
    function getDataFromSync()
    {
        $syncType = $this->input->get('syncType');
        $syncData = json_decode(config_item('synchronization'), TRUE);
        $state = $syncData[$syncType]['state'] ? 0 : 1;
        $syncData[$syncType]['state'] = $state;
        $this->settings->update_by(['stt_key_name' => 'synchronization'], ['stt_key_value' => json_encode($syncData)]);
        $textButton = $state ? $syncData[$syncType]['textOff'] : $syncData[$syncType]['textOn'];
        return $this->response($textButton);
    }

    function changeSyncInvoiceNO()
    {
        $value = $this->input->get('value');
        $state = $value == 'Enable' ? 'Disable' : 'Enable';
        $this->settings->update_by(['stt_key_name' => 'syncInvoiceNO'], ['stt_key_value' => $state]);
        return $this->response($state);
    }

    public function verify_identity()
    {
        // domain or email
        $identity = $this->input->post('domain');
        // $dkim = $this->input->post('dkim')  === 'true'; // in order to generate DKIMS separately
        $isDomain = $this->input->post('is_domain') === 'true' ? '1' : '0';
        $insertedId = false;

        if (empty($identity)) {
            $this->response([
                'status' => false,
                'msg' => "You must enter domain name."
            ]);
        }

        $this->load->library('email');
        // load library instead of driver
        $this->load->library('MailDriver/amazon');
        $userId = $this->session->userdata('user_id');

        // check aws and db synchronization
        $this->checkAmazonSynchronizeWithDb($userId, $identity, $isDomain);

        // define which identity should be verified
        $verificationFunctionName = 'verify' . ($isDomain === '0' ? 'Email' : 'Domain') . 'Identity';

        // if there is no any record in db or aws , add new domain to aws and keep in db
        $txtToken = $this->amazon->{$verificationFunctionName}($identity);
        $cnames = $isDomain === '1' ? $this->amazon->verifyDomainDkim($identity) : [];

        if ((isset($txtToken['status']) && !$txtToken['status']) || (!empty($cnames) && $cnames['status'] === false)) {
            return $this->response([
                'status' => false,
                'msg' => ($txtToken['msg'] ?? '') . ($cnames['msg'] ?? '')
            ]);
        }

        $verificationAttributes = $this->amazon->getIdentityVerificationAttributes([ $identity])['VerificationAttributes'];
        $dkimAttributes = $this->amazon->getIdentityDkimAttributes([ $identity])['DkimAttributes'];

        // after verification of identity remove all regarding data from db and add fresh info
        $response = [
            'user_id' => $userId,
            'identity' => $identity,
            'is_domain' => $isDomain,
        ];
        $deleted = $this->amazon_identities->get_by($response);
        $deletedId = $deleted ? $deleted->identity_id : 0;
        $this->amazon_identities->delete_by($response);

        $response['dkimAttributes'] = json_encode($dkimAttributes[$identity]);
        $response['verificationAttributes'] = json_encode($verificationAttributes[$identity]);
        $insertedId = $this->amazon_identities->insert($response);

        if (!$insertedId) {
            $this->response([
                'status' => false,
                'msg' => "Cannot save identity in db!"
            ]);
        }

        $amazonIdentity = $this->amazon_identities->get($insertedId);
        $response['status'] = true;
        $response['identity_id'] = $insertedId;
        $response['msg'] = "To complete verification of $identity, you must add the following TXT / CNAME record(s) to the domain's DNS settings:";
        $response['amazonIdentity'] = $amazonIdentity;
        $response['deletedId'] = $deletedId;

        pushJob('amazon_ses/check_identity_verification', ['user_id' => $userId], strtotime("+12 hours"));

        $this->response($response);
    }

    private function checkAmazonSynchronizeWithDb($userId, $identity, $isDomain)
    {
        // return identity info if already added in amazon
        $verificationAttributes = $this->amazon->getIdentityVerificationAttributes([$identity])['VerificationAttributes'];
        $dkimAttributes = $this->amazon->getIdentityDkimAttributes([ $identity])['DkimAttributes'];
        $identityDbRow = $this->amazon_identities->order_by('identity_id','DESC' )->get_by(['identity' => $identity]);

        if (empty($verificationAttributes)) return true;
        if (empty($identityDbRow)) {
            $insertedId = $this->amazon_identities->insert([
                'user_id' => $userId,
                'identity' => $identity,
                'is_domain' => $isDomain,
                'dkimAttributes' => !empty($dkimAttributes) ? json_encode($dkimAttributes[$identity]) : json_encode([]),
                'verificationAttributes' => (isset($verificationAttributes) && !empty($verificationAttributes)) ? json_encode($verificationAttributes[$identity]) : json_encode([])
            ]);

            if (!$insertedId) {
                $this->response([
                    'status' => false,
                    'msg' => "Cannot save identity in db!"
                ]);
            }
            $identityDbRow = $this->amazon_identities->get($insertedId);
        } else {
            // keep up to date aws and db data
            $this->amazon_identities->update($identityDbRow->identity_id, [
                'dkimAttributes' => !empty($dkimAttributes) ? json_encode($dkimAttributes[$identity]) : json_encode([]),
                'verificationAttributes' => (isset($verificationAttributes) && !empty($verificationAttributes)) ? json_encode($verificationAttributes[$identity]) : json_encode([]),
                'last_checked' => date("Y-m-d H:i:s")
            ]);
            $identityDbRow = $this->amazon_identities->get($identityDbRow->identity_id);
        }


        if ($verificationAttributes[$identity]['VerificationStatus'] == 'Pending') {
            pushJob('amazon_ses/check_identity_verification', ['user_id' => $userId], strtotime("+12 hours"));
        }
        $this->response([
            'status' => true,
            'user_id' => $userId,
            'amazonIdentity' => $identityDbRow
        ]);
    }

    public function check_identity()
    {
        $identity_id = $this->input->post('identityId');
        if (!$identity_id) die(json_encode(['status' => false]));

        $this->load->library('email');
        $this->load->library('MailDriver/amazon');
        // get db identity row
        $identity = $this->amazon_identities->get($identity_id);

        // get identity status
        $verificationAttributes = $this->amazon->getIdentityVerificationAttributes([ $identity->identity])['VerificationAttributes'];
        $dkimAttributes = $this->amazon->getIdentityDkimAttributes([ $identity->identity])['DkimAttributes'];

        if (!isset($verificationAttributes) && !isset($dkimAttributes)) {
            $this->amazon_identities->delete($identity->identity_id);

            die (json_encode([
                'status' => true,
                'identity' => [],
                'identity_id' => $identity_id
            ]));
        }

        $this->amazon_identities->update($identity->identity_id, [
            'dkimAttributes' => !empty($dkimAttributes) ? json_encode($dkimAttributes[$identity->identity]) : json_encode([]),
            'verificationAttributes' => (isset($verificationAttributes) && !empty($verificationAttributes)) ? json_encode($verificationAttributes[$identity->identity]) : json_encode([]),
            'last_checked' => date("Y-m-d H:i:s")
        ]);

        die (json_encode([
            'status' => true,
            'identity' => $this->amazon_identities->get($identity_id),
            'identity_id' => $identity_id
        ]));

    }

    public function delete_identity()
    {
        $identity_id = $this->input->post('identityId');
        if (!$identity_id) die(json_encode(['status' => false]));

        $this->load->library('email');
        $this->load->library('MailDriver/amazon');

        // remove from db
        $identity = $this->amazon_identities->get($identity_id);
        // remove from aws
        $this->amazon->deleteIdentity($identity->identity);
        $deleted = $this->amazon_identities->delete($identity_id);

        if (isset($deleted['status'])) die(json_encode(['status' => $deleted['status']]));
        die (json_encode(['status' => true]));
    }

    public function importQbDesktop(){
        if(empty($_FILES['file'])){
            $this->errorResponse('Select a file!');
            return;
        }
        $QBS3FileActions = new $this->qbs3fileactions($_FILES['file']);
        if(!$QBS3FileActions->upload()){
            $this->errorResponse($QBS3FileActions->getUploadError());
            return;
        }

//        ini_set('max_execution_time', 900);
//        $file = $QBS3FileActions->setFileName('export_08-30-21.IIF');
        $file = $QBS3FileActions->getFile();
        $lines = explode(PHP_EOL, $file);
        $header = [];
        foreach($lines as $line) {
            $row = explode("\t", $line);
            if($row[0] == "!INVITEM")
                $header = $row;
            elseif ($row[0] == "INVITEM"){
                $item = new $this->qbdesktopitem();
                $item->setItemFromIIFFileRow($header, $row);
                $item->save();
            }
            elseif ($row[0] == "!CUST")
                $header = $row;
            elseif ($row[0] == "CUST"){
                $client = new $this->qbdesktopclient();
                $client->setClientFromIIFFileRow($header, $row);
                $client->save();
            }
        }

        $this->successResponse();
        return;
    }

    public function exportQbDesktop(){
        $date = new DateTime();
        $timestamp = $date->getTimestamp();
        $items = Service::where('service_qb_id', null)->get();
        Service::where('service_qb_id', null)->update(['service_qb_id' => $timestamp]);
        $clients = Client::where('client_qb_id', null)->with('primary_contact')->get();
        Client::where('client_qb_id', null)->update(['client_qb_id' => $timestamp]);
//        $invoices = Invoice::where('invoice_qb_id', null)->with('estimate.estimates_service.service')
//            ->withTotals(['invoices.invoice_qb_id' => null])->get()->toArray();
        $invoices = Invoice::where('invoice_qb_id', null)
            ->with(['estimate' => function($query) {
                $query->select(Estimate::LIGHT_FIELDS);
                $query->with(['estimates_service.service'])
                    ->withTotals([], 'invoices.invoice_qb_id is null');
            }])->get()->toArray();
        Invoice::where('invoice_qb_id', null)->update(['invoice_qb_id' => $timestamp]);
        $payments = ClientPayment::where('payment_qb_id', null)->with('estimates.client')->get()->toArray();
        ClientPayment::where('payment_qb_id', null)->update(['payment_qb_id' => $timestamp]);

        $result = [];
        if(!empty($items)){
            $itemsToQb = [];
            foreach ($items as $item){
                $itemToQB = $this->qbdesktopitem->getItemToQbDesktop($item);
                if(!empty($itemToQB))
                    $itemsToQb[] = $itemToQB;
            }
            if(!empty($itemsToQb)){
                array_unshift($itemsToQb, $this->qbdesktopitem->getQbDesktopHeader());
                $result = array_merge($result, $itemsToQb);
            }
        }

        if(!empty($clients)){
            $clientsToQb = [];
            foreach ($clients as $client){
                $clientToQb = $this->qbdesktopclient->getClientToQbDesktop($client);
                if(!empty($clientToQb))
                    $clientsToQb[] = $clientToQb;
            }
            if(!empty($clientsToQb)){
                array_unshift($clientsToQb, $this->qbdesktopclient->getQbDesktopHeader());
                $result = array_merge($result, $clientsToQb);
            }
        }

        if(!empty($invoices)){
            $invoicesToQb = [];
            foreach ($invoices as $invoice){
                $invoiceToQb = $this->qbdesktopinvoice->getInvoiceToQbDesktop($invoice);
                if(!empty($invoiceToQb) && !empty($invoice['estimate']['estimates_service'])) {
                    $invoicesToQb[] = $invoiceToQb;
                    foreach ($invoice['estimate']['estimates_service'] as $item) {
                        $invoicesToQb[] = $this->qbdesktopitem->getItemForQbDesktopInvoice($item, $invoice['date_created']);
                    }
                    $invoicesToQb[] = ['ENDTRNS'];
                }
            }
            if(!empty($invoicesToQb)){
                array_unshift($invoicesToQb, $this->qbdesktopinvoice->qbHeader, $this->qbdesktopitem->qbEstimateHeader, ['!ENDTRNS']);
                $result = array_merge($result, $invoicesToQb);
            }

        }

        if(!empty($payments)){
            $paymentsToQb = [];
            foreach ($payments as $payment){
                $paymentToQb = $this->qbdesktoppayment->getTRNSToQbDesktop($payment);
                if(!empty($paymentToQb)){
                    $paymentsToQb[] = $paymentToQb;
                    $paymentsToQb[] = $this->qbdesktoppayment->getPaymentToQbDesktop($payment);
                    $paymentsToQb[] = ['ENDTRNS'];
                }
            }
            if(!empty($paymentsToQb) && empty($invoicesToQb)){
                array_unshift($paymentsToQb, $this->qbdesktopinvoice->qbHeader, $this->qbdesktopitem->qbEstimateHeader, ['!ENDTRNS']);
            }
            $result = array_merge($result, $paymentsToQb);
        }

        if(!empty($result)){
            $path = '/tmp/';
            $fileName = 'export_' . str_replace(' ', '_', getNowDateTime(true)) . '.iif';
            $file = fopen($path.$fileName, 'w+');
            foreach ($result as $fields) {
                if(is_array($fields))
                    fputcsv($file, $fields, "\t");
            }
            bucket_write_file('uploads/qb/export/' . $fileName , $file);
            fclose($file);

            $this->successResponse(['link' => "uploads/qb/export/" . $fileName, 'name' => $fileName], "uploads/qb/export/" . $fileName);
            return;
        }
        $this->errorResponse('No data for export.');
    }

    public function getDateQbDesktopLogs(){
        $timestamp = $this->input->post('timestamp');
        $data = [];
        $clients = $this->qbdesktopclient->getLogsContent($timestamp);
        if(!empty($clients)){
            $data['clients'] = $clients;
        }
        $invoices = $this->qbdesktopinvoice->getLogsContent($timestamp);
        if(!empty($invoices)){
            $data['invoices'] = $invoices;
        }

        $payments = $this->qbdesktoppayment->getLogsContent($timestamp);
        if(!empty($payments)){
            $data['payments'] = $payments;
        }
        $content = $this->load->view('partials/qb_desktop_logs_content', $data, true);
        $this->successResponse(['content' => $content], 'success');
        return;
    }

    public function saveByKeyName()
    {
        $data = request()->input('data');

        foreach ($data as $key => $item) {
            $setting = ST::where('stt_key_name' , '=', $key)->first();
            if (is_null($setting)) {
                ST::create([
                    ST::ATTR_KEY_NAME => $key,
                    ST::ATTR_KEY_VALUE => $item
                ]);
            } else {
                $setting->stt_key_value = $item;
                $setting->save();
            }
        }
        $this->successResponse(['content' => 'Success'], 'success');
    }
}
