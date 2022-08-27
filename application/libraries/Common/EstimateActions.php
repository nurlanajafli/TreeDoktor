<?php

use application\modules\classes\models\QBClass;
use application\modules\estimates\models\Estimate;
use application\modules\estimates\models\EstimatesBundle;
use application\modules\estimates\models\EstimatesServicesCrew;
use application\modules\estimates\models\EstimatesServicesEquipments;
use application\modules\estimates\models\EstimateStatus;
use application\modules\invoices\models\Invoice;
use application\modules\leads\models\Lead;
use application\modules\leads\models\LeadService;
use application\modules\clients\models\Client;
use application\modules\clients\models\ClientsContact;
use application\modules\leads\models\LeadStatus;
use application\modules\workorders\models\Workorder;
use application\modules\workorders\models\WorkorderStatus;
use Carbon\Carbon;
use application\modules\estimates\models\Service;
use application\modules\clients\models\ClientLetter;
use application\modules\estimates\models\EstimatesService;
use application\modules\tree_inventory\models\TreeInventory;
use Illuminate\Support\Facades\Cache;
use application\modules\estimates\models\TreeInventoryEstimateService;
use application\modules\estimates\models\TreeInventoryEstimateServiceWorkTypes;
use application\modules\tree_inventory\models\TreeInventoryWorkTypes;
use application\modules\tree_inventory\models\TreeType;

use Illuminate\Support\ServiceProvider;
use Mpdf\MpdfException;
use application\modules\tree_inventory\models\WorkType;

class EstimateActions extends ServiceProvider
{
    protected $CI;
    protected $estimate;
    protected $estimateId;
    protected $lead;
    protected $_title;
    private $isConfirmedWeb;

    private $checkProductDraft = [
        'class' => 'class',
        'collapsed' => 'is_collapsed',
        'noTax' => 'non_taxable',
        'cost' => 'product_cost',
        'quantity' => 'quantity',
        'service_description' => 'service_description',
        'service_price' => 'service_price',
        'service_id' => 'service_type_id',
    ];

    private $checkServiceDraft = [
        'lead_id' => 'lead_id',
        'class' => 'class',
        'collapsed' => 'is_collapsed',
        'crews' => 'crews',
        'equipments' => 'equipments',
        'noTax' => 'non_taxable',
        'service_id' => 'service_type_id',
        'service_price' => 'service_price',
        'service_description' => 'service_description',
        'service_time' => 'service_time',
        'service_travel_time' => 'service_travel_time',
        'service_disposal_time' => 'service_disposal_time',
        'service_overhead_rate' => 'service_overhead_rate',
        'service_markup' => 'service_markup_rate',
        'is_view_in_pdf' => 'is_view_in_pdf',
        'serviceimages' => 'pre_uploaded_files',
        'trailer_option' => 'trailer_option',
        'service_trailer' => 'service_trailer',
        'vehicle_option' => 'vehicle_option',
        'service_vehicle' => 'service_vehicle',
        'service_crew' => 'service_crew',
        'tools_option' => 'tools_option',
        'expenses' => 'expenses',
        'tree_inventory_service' => 'tree_inventory_service',
        'tree_inventory_title' => 'tree_inventory_title',
        'service_priority' => 'service_priority',
        'ties_number' => 'ties_number',
        'ties_type' => 'ties_type',
        'ties_size' => 'ties_size',
        'ties_priority' => 'ties_priority',
        'ties_cost' => 'ties_cost',
        'ties_stump' => 'ties_stump',
        'ties_work_types' => 'ties_work_types'
    ];

    private $checkBundleDraft = [
        'quantity' => 'quantity',
        'service_description' => 'service_description',
        'is_view_in_pdf' => 'is_view_in_pdf',
        'is_product' => 'is_product',
        'is_bundle' => 'is_bundle',
        'services' => 'services',
        'products' => 'products',
        'bundles_services' => 'bundles_services',
        'bundle_id' => 'service_type_id',
        'toPdf' => 'toPdf',
        'bundle_qty' => 'bundle_qty',
        'items' => 'items'
    ];

    public $draftPrefix;
    public $draftLifeTime;

    /**
     * EstimateActions constructor.
     * @param null $estimateId
     * @noinspection MagicMethodsValidityInspection
     */
    public function __construct($estimateId = NULL) {
        $this->CI =& get_instance();

        $this->CI->load->model('mdl_clients');
        $this->CI->load->model('mdl_leads');
        $this->CI->load->model('mdl_est_status');
        $this->CI->load->model('mdl_est_reason');
        $this->CI->load->model('mdl_estimates');
        $this->CI->load->model('mdl_estimates_orm');
        $this->CI->load->model('mdl_vehicles');
        $this->CI->load->model('mdl_user');
        $this->CI->load->model('mdl_crews_orm');
        $this->CI->load->model('mdl_equipment_orm');
        $this->CI->load->model('mdl_crew', 'crew_model');
        $this->CI->load->model('mdl_tree_inventory_orm', 'tree_inventory');

        $this->CI->load->helper('cache');
        $this->CI->load->helper('tree_helper');
        //Global settings:
        $this->_title = SITE_NAME;

        $this->estimateId = $estimateId;
        if($estimateId) {
            $this->estimate = $this->CI->mdl_estimates_orm->get($estimateId);
            $this->lead = $this->CI->mdl_leads->find_by_id($this->estimate->lead_id);
        }

        $this->draftPrefix   = 'estimate:';
        $this->draftLifeTime = 1800; // in seconds
    }

    public function clear(): void
    {
        $this->estimate = NULL;
        $this->estimateId = NULL;
        $this->lead = NULL;
    }

    /**
     * @param $estimateId
     * @return bool
     */
    public function setEstimateId($estimateId): bool
    {
        $this->estimate = $this->CI->mdl_estimates_orm->get($estimateId);
        $this->lead = $this->CI->mdl_leads->find_by_id($this->estimate->lead_id);
        if (!$this->estimate) {
            return FALSE;
        }
        $this->estimateId = $estimateId;
        return TRUE;
    }

    public function getEstimateId(){
        if(!empty($this->estimateId))
            return $this->estimateId;
        return false;
    }

    function getEstimate() {
        if($this->estimate)
            return $this->estimate;

        return FALSE;
    }

    function create(array $estimate) {
        if(!empty($estimate)){
            $newEstimate = Estimate::create($estimate);
            if(!empty($newEstimate)) {
                $this->setEstimateId($newEstimate->estimate_id);
                return true;
            }
        }
        return false;
    }

    public function update(): void
    {

    }

    /**
     * @param null $method
     * @param null $schedulingPreference
     * @param null $extraNoteCrew
     * @param null $date
     * @return bool
     */
    public function confirm($method = NULL, $schedulingPreference = NULL, $officeNoteCrew = NULL, $date = NULL): bool
    {
        $result = $this->changeStatus($this->getConfirmedStatusId());

        if($result && ($method || $schedulingPreference || $officeNoteCrew || $date)) {
            $this->CI->workorderactions->updateInitialData($method, $schedulingPreference, $officeNoteCrew, $date);
        }

        return $result;
    }

    /**
     * @return mixed
     */
    public function getConfirmedStatusId() {
        $confirmedStatus = $this->CI->mdl_est_status->get_by(['est_status_confirmed' => 1]);
        return $confirmedStatus->est_status_id;
    }

    /**
     * @param $signatureData
     * @param $withBackground
     * @return false|string
     */
    public function sign($signatureData = NULL, $withBackground = true) {

        if (!$signatureData) {
            return FALSE;
        }

        $signature = str_replace('[removed]', '', $signatureData);
        if ($signature == $signatureData) {
            $signature = explode(',', $signatureData)[1];
        }

        $tmpPath = sys_get_temp_dir() . '/signature_' . $this->estimate->estimate_id . '.png';
        $path =  'uploads/clients_files/' . $this->estimate->client_id . '/estimates/' . $this->estimate->estimate_no . '/signature.png';
        $im = imagecreatefromstring(base64_decode($signature));

        $tmp = imagecreate(imagesx($im), imagesy($im));
        imagealphablending($tmp, false);
        imagesavealpha($tmp, true);

        imagecopyresampled($tmp, $im, 0, 0, 0, 0, imagesx($im), imagesy($im), imagesx($im), imagesy($im));
        if ($withBackground) {
            $bg = imagecolorexact ($tmp, 241, 243, 247);
            imagecolorset($tmp, $bg, 143, 254, 9);
            $bg = imagecolorexact ($tmp, 242, 243, 247);
            imagecolorset($tmp, $bg, 143, 254, 9);
            $bg = imagecolorexact ($tmp, 235, 235, 231);
            imagecolorset($tmp, $bg, 143, 254, 9);

            $bg = imagecolorclosest($tmp, 143, 254, 9);
            imagecolortransparent($tmp, $bg);
        }

        imagepng($tmp, $tmpPath);
        imagedestroy($tmp);
        imagedestroy($im);

        if (!getimagesize($tmpPath)) {
            return FALSE;
        }

        bucket_move($tmpPath, $path, ['ContentType' => 'image/png']);
        @unlink($tmpPath);

        return $path;
    }

    public function deposit(): void
    {

    }

    /**
     * @param $statusId
     * @param null $reasonId
     * @return bool
     */
    public function changeStatus($statusId, $reasonId = NULL): bool
    {
        $statusData = $this->CI->mdl_est_status->with('mdl_est_reason')->get($statusId);

		$reasonData = $this->CI->mdl_est_reason->get_by(['reason_est_status_id' => $statusId, 'reason_id' => $reasonId]);
		if(isset($reasonData) && !empty($reasonData)) {
			$reason_id = $reasonId;
		}
		else {
			$reason_id = NULL;
		}
        $oldStatusData = $this->CI->mdl_est_status->get($this->estimate->status);

        if (isset($statusData->statusData)) {
            if (!$statusData) {
                return FALSE;
            }
        }
        $this->CI->load->library('Common/WorkorderActions');
		if($oldStatusData->est_status_confirmed) {
			$this->CI->workorderactions->delete($this->estimateId);
		}
        $this->CI->mdl_estimates_orm->update($this->estimateId, ['status' => $statusId, 'estimate_reason_decline' => $reason_id]);
        $this->estimate = $this->CI->mdl_estimates_orm->get($this->estimateId);

        if($statusData->est_status_confirmed) {
            if($this->getIsConfirmedWeb())
                $this->CI->workorderactions->setIsConfirmedWeb(true);
            return $this->CI->workorderactions->create($this->estimateId);
        }

        return TRUE;
    }

    public function getPDF(): void
    {

    }

    /**
     * @throws MpdfException
     */
    public function showPDF(): void
    {
        include_once('./application/libraries/Mpdf.php');
        $this->CI->mpdf = new mPDF();
        $this->CI->mpdf->WriteHTML($this->getPDFTemplate());
        $this->CI->mpdf->Output($this->getPDFFileName(), 'I');
    }

    /**
     * @return string
     * @throws MpdfException
     */
    public function tmpPDF(): string
    {
        include_once('./application/libraries/Mpdf.php');
        $this->CI->mpdf = new mPDF();
        $this->CI->mpdf->WriteHTML($this->getPDFTemplate());

        $file = sys_get_temp_dir() . '/' . $this->getPDFFileName() . '.pdf';

        if(isset($file) && is_file($file))
            unlink($file);

        if(isset($file) && file_exists($file))
            $file = sys_get_temp_dir() . '/' . $this->getPDFFileName() . '-' . uniqid('', true) . '.pdf';

        $files = $this->estimate->estimate_pdf_files ? json_decode($this->estimate->estimate_pdf_files) : [];
        foreach ($files as $f) {
            if(pathinfo($f, PATHINFO_EXTENSION) == 'pdf' && is_bucket_file($f)) {
                $this->CI->mpdf->AddPage();
                $this->CI->mpdf->Thumbnail(bucket_get_stream($f), 1, 10, 16, 1);
            }
        }

        $this->CI->mpdf->Output($file, 'F');

        return $file;
    }

    /**
     * @return string
     */
    public function getPDFFileName(): string
    {
        return 'Estimate ' . $this->estimate->estimate_no . " - " . str_replace('/', '_', trim($this->lead->lead_address)). '.pdf';
    }

    /**
     * @return mixed
     */
    public function getPDFTemplate() {
        $this->CI->load->model('mdl_est_equipment');
        $this->CI->load->model('mdl_estimates_bundles');
        $estimate_data = $this->CI->mdl_estimates_orm->with('mdl_services_orm')->get_full_estimate_data(array('estimate_id' => $this->estimateId), true, false, false);
        $data['title'] = $this->_title . ' - Estimates PDF';

        $files = json_decode($estimate_data[0]->estimate_pdf_files, true);

        if(!empty($files)) {
            $estimateServices = EstimatesService::where('estimate_id', $estimate_data[0]->estimate_id)->orderBy('service_priority')->get()->toArray();
            $files = $this->sortEstimateFiles($estimateServices, $files);
            $estimate_data[0]->estimate_pdf_files = json_encode($files);
        }

        $data['estimate_data'] = $this->CI->mdl_estimates_orm->_explodePdfFiles($estimate_data)[0];

        $this->CI->load->model('mdl_estimates');

        $estimateServicesData = $this->CI->mdl_estimates->find_estimate_services($this->estimateId, ['estimates_services.service_status <>' => 1]);
        $estimateTreeInventoryServicesData = [];
        $treeInventoryWorkTypes = [];
        $treeInventoryPriorities = [];

        foreach ($estimateServicesData as $key => $value){
            if($value['is_bundle']){
                $bundleRecords = $this->CI->mdl_estimates_bundles->get_many_by(['eb_bundle_id' => $value['id']]);
                $bundleRecordsForPDF = [];
                if(!empty($bundleRecords)){
                    foreach ($bundleRecords as $record){
                        foreach ($estimateServicesData as $esKey => $esValue){
                            if($record->eb_service_id == $esValue['id']){
                                $bundleRecordsForPDF[] = (object)$esValue;
                                unset($estimateServicesData[$esKey]);
                            }
                        }
                    }
                }
                $estimateServicesData[$key]['bundle_records'] = $bundleRecordsForPDF;
            }
            // add tree inventory work types
            if(isset($value['ties_id']) && !empty($value['ties_id'])){
                $estimateTreeInventoryServicesData[$key] = $value;
                $treeInventoryPriorities[] = $value['ties_priority'];
                unset($estimateServicesData[$key]);
                $workTypes = TreeInventoryEstimateServiceWorkTypes::where('tieswt_ties_id', $value['ties_id'])->with('work_type')->get()->pluck('work_type')->pluck('ip_name_short')->toArray();
                $treeInventoryWorkTypes = array_merge($treeInventoryWorkTypes, $workTypes);
                if(!empty($workTypes) && is_array($workTypes)){
                    $estimateTreeInventoryServicesData[$key]['work_types'] = implode(', ', $workTypes);
                }
                $estimateTreeInventoryServicesData[$key]['ties_priority'] = ucfirst(substr($value['ties_priority'], 0,1));
            }
        }
        if(!empty($estimateTreeInventoryServicesData)){
            if(!empty($treeInventoryWorkTypes)) {
                $data['work_types'] = WorkType::whereIn('ip_name_short', $treeInventoryWorkTypes)->get()->toArray();
            }
            if(!empty($treeInventoryPriorities)) {
                $data['tree_inventory_priorities'] = array_unique($treeInventoryPriorities);
            }
        }
        $data['estimate_services_data'] = $estimateServicesData;
        $data['estimate_tree_inventory_services_data'] = $estimateTreeInventoryServicesData;
        $data['discount_data'] = $this->CI->mdl_clients->get_discount(array('discounts.estimate_id' => $this->estimateId));

        $this->CI->load->model('mdl_clients');
        $data['client_data'] = $this->CI->mdl_clients->find_by_id($this->estimate->client_id);
        $data['client_contact'] = $this->CI->mdl_clients->get_primary_client_contact($this->estimate->client_id);

        $this->CI->load->model('mdl_user');
        $data['user_data'] = $this->CI->mdl_user->find_by_id($this->estimate->user_id);

        $this->CI->load->model('mdl_vehicles');
        $data["equipment_items"] =  $this->CI->mdl_vehicles->get_many_by(array('vehicle_trailer IS NULL', 'vehicle_disabled' => NULL));
        $data["tools"] = $this->CI->mdl_vehicles->get_many_by(array('vehicle_trailer' => 2));

        $this->CI->load->model('mdl_crew');
        $data['crews_active'] = $this->CI->mdl_crew->get_crewdata(array('crew_status' => 1, 'crew_id <>' => 0))->result_array();

        list($result, $view) = Modules::find('pdf_templates/' . config_item('company_dir') . '/estimate_pdf', 'includes', 'views/');
        if($result) {
            $html = $this->CI->load->view('includes/pdf_templates/' . config_item('company_dir') . '/' . 'estimate_pdf', $data, TRUE);
        } else {
            $html = $this->CI->load->view('includes/pdf_templates/estimate_pdf', $data, TRUE);
        }

        $brand_id = get_brand_id($data['estimate_data'], $data['client_data']);
        $html = ClientLetter::parseCustomTemplates($data['estimate_data']->estimate_id, $html, $brand_id);
        return $html;
    }

    /**
     * @param $path
     * @return bool
     */
    public function addFileToPdf($path): bool
    {
        $estimatePdfFiles = $this->estimate->estimate_pdf_files ? json_decode($this->estimate->estimate_pdf_files, TRUE) : [];
        if(is_bucket_file($path)) {
            $estimatePdfFiles[] = $path;
            $estimatePdfFiles = array_unique(array_values($estimatePdfFiles));
            $this->CI->mdl_estimates_orm->update($this->estimate->estimate_id, ['estimate_pdf_files' => json_encode($estimatePdfFiles)]);
        }
        return FALSE;
    }

    /**
     * @param $path
     * @return bool
     */
    public function hideFileFromPdf($path): bool
    {
        $estimatePdfFiles = $this->estimate->estimate_pdf_files ? json_decode($this->estimate->estimate_pdf_files, TRUE) : [];
        $key = array_search($path, $estimatePdfFiles, true);
        if($key !== FALSE) {
            unset($estimatePdfFiles[$key]);
            $estimatePdfFiles = array_unique(array_values($estimatePdfFiles));
            $this->estimate->estimate_pdf_files = json_encode($estimatePdfFiles);
            $this->CI->mdl_estimates_orm->update($this->estimate->estimate_id, ['estimate_pdf_files' => json_encode($estimatePdfFiles)]);
        }
        return FALSE;
    }

    public function send(): void
    {

    }

    /**
     * @param $to
     * @return bool
     */
    public function sendConfirmed($to = false): bool
    {
        if(!$to)
            $to = $this->estimate->cc_email;

        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return FALSE;
        }

        $letter = ClientLetter::where(['system_label' => 'confirmed_estimate'])->first();
        $estimate_data = Estimate::with(['client', 'client.primary_contact', 'user', 'lead', 'workorder', 'invoice'])->find($this->estimateId);

        $brand_id = get_brand_id($estimate_data->toArray(), $estimate_data->client->toArray());

        $letter = ClientLetter::compileLetter($letter, $brand_id, [
            'client'    =>  $estimate_data->client,
            'estimate'  =>  $estimate_data,
        ]);

        pushJob('estimates/sendestimate', [
            'estimate_id' => $this->estimateId,
            'from' => $letter->email_static_sender,
            'cc' => $letter->email_static_cc,
            'bcc' => $letter->email_static_bcc,
            'to' => $to,
            'body' => $letter->email_template_text,
            'subject' => $letter->email_template_title,
            'user_id' => request()->user()->id ?? null
        ]);
        return TRUE;
    }

    /**
     * @return array|false
     */
    public function sendConfirmedCompanyNotification(): bool
    {
        $this->CI->load->library('email');

        $text = $this->CI->load->view('estimates/emails/confirmed_company_notification', ['estimate'=>$this->estimate], true);
        $brand_id = get_brand_id((array)$this->estimate, (array)$this->estimate);

        $config['mailtype'] = 'text';
        $this->CI->email->initialize($config);
        $this->CI->email->to(brand_email($brand_id));
        $this->CI->email->from('info@arbostar.com');
        $this->CI->email->subject("Confirmed Estimates");
        $this->CI->email->message($text);
        $this->CI->email->send();

        return true;
    }

    /**
     * @return array|false
     * @throws MpdfException
     */
    public function sendConfirmedCompanyNotificationWithPdf(): bool
    {
        $this->CI->load->library('email');
        $file = $this->tmpPDF();

        $text = $this->CI->load->view('estimates/emails/confirmed_company_notification', ['estimate'=>$this->estimate], true);
        $brand_id = get_brand_id((array)$this->estimate, (array)$this->estimate);

        $config['mailtype'] = 'text';
        $this->CI->email->attach($file);
        $this->CI->email->initialize($config);
        $this->CI->email->to(brand_email($brand_id));
        $this->CI->email->from('info@arbostar.com');
        $this->CI->email->subject("Confirmed Estimates");
        $this->CI->email->message($text);
        $this->CI->email->send();

        return true;
    }

    public function compileSmsTemplate($additionalVars = []) {

        if(!($this->estimate)) {
            return false;
        }

        $smsTpl = \application\modules\messaging\models\SmsTpl::find(2);

        if(!$smsTpl) {
            return false;
        }

        $brand_id = get_brand_id((array)$this->estimate, (array)$this->estimate);

        $originalVars = [
            '[CCLINK]' => isset($this->estimate->estimate_id) ? config_item('payment_link') . 'payments/' . md5($this->estimate->estimate_no . $this->estimate->client_id) : '',
            '[SIGNATURELINK]' => isset($this->estimate->estimate_id) ? config_item('payment_link') . 'payments/estimate_signature/' . md5($this->estimate->estimate_id) : '',
            '[ESTIMATE_LINK]' => isset($this->estimate->estimate_id) ? config_item('payment_link') . 'payments/estimate/' . md5($this->estimate->estimate_no . $this->estimate->client_id) : '',
            '[ESTIMATE_ID]' => isset($this->estimate->estimate_id) ? $this->estimate->estimate_id : '',
            '[ESTIMATE_NO]' => isset($this->estimate->estimate_no) ? $this->estimate->estimate_no : '',
            '[DATE]' => isset($this->estimate->date_created) ? getDateTimeWithTimestamp($this->estimate->date_created, false) : '',
            '[NO]' => isset($this->estimate->estimate_no) ? $this->estimate->estimate_no : '',
            '[NAME]' => isset($this->estimate->cc_name) ? $this->estimate->cc_name : $this->estimate->client_name,
            '[CLIENT_NAME]' => isset($this->estimate->cc_name) ? $this->estimate->cc_name : $this->estimate->client_name,
            '[PHONE]' => isset($this->estimate->cc_phone) ? $this->estimate->cc_phone : '-',
            '[CLIENT_PHONE]' => isset($this->estimate->cc_phone) ? $this->estimate->cc_phone : '-',
            '[EMAIL]' => isset($this->estimate->cc_email) ? $this->estimate->cc_email : '-',
            '[CLIENT_EMAIL]' => isset($this->estimate->cc_email) ? $this->estimate->cc_email : '-',
            '[ADDRESS]' => (isset($this->estimate->lead_address)) ? $this->estimate->lead_address : '-',
            '[JOB_ADDRESS]' => (isset($this->estimate->lead_address)) ? $this->estimate->lead_address : '-',
            '[AMOUNT]' => isset($this->estimate->total_due) ? money($this->estimate->total_due) : '[AMOUNT]',
            '[COMPANY_NAME]' => (brand_name($brand_id))?brand_name($brand_id):$this->CI->config->item('company_name_short'),
            '[COMPANY_EMAIL]' => (brand_email($brand_id))?brand_email($brand_id):$this->CI->config->item('account_email_address'),
            '[COMPANY_PHONE]' => (brand_phone($brand_id))?brand_phone($brand_id):$this->CI->config->item('office_phone_mask'),
            '[COMPANY_ADDRESS]' => brand_address($brand_id,$this->CI->config->item('office_address') . ', ' . $this->CI->config->item('office_city')),
            '[COMPANY_BILLING_NAME]' => (brand_name($brand_id))?brand_name($brand_id):$this->CI->config->item('company_name_long'),
            '[COMPANY_WEBSITE]' => $this->CI->config->item('company_site')
        ];

        foreach ($additionalVars as $key => $val) {
            $originalVars[$key] = $val;
        }

        $compiledText = trim(str_replace(array_keys($originalVars), array_values($originalVars), $smsTpl->sms_text));

        return $compiledText ?: false;
    }

    /**
     * @param array $address
     * @return array|false
     */
    public function getTaxForUSCompany(array $address)
    {
        $taxForUSUrl = 'http://wetax.arbostar.com';
        $taxForUSState = '';
        $taxForUSZip = '';
        $taxForUSCity = '';

        if ($address) {
            foreach ($address as $key => $value) {
                switch (strtoupper($key)) {
                    case 'STATE':
                        $taxForUSState = $value;
                        break;
                    case 'CITY':
                        $taxForUSCity = $value;
                        break;
                    case 'ZIP':
                        $taxForUSZip = $value;
                        break;
                }
            }
            $taxForUSUrl .= '/' . $taxForUSState . '/' . $taxForUSZip . '/' . $taxForUSCity;
            $response = file_get_contents($taxForUSUrl);
            $tax = json_decode($response, true);

            if (isset($tax[0])) {
                $tax = $tax[0];
                if (!empty($tax['EstimatedCombinedRate'])) {
                    $taxValue = round($tax['EstimatedCombinedRate'] * 100, 3);
                    $taxRate = $tax['EstimatedCombinedRate'] + 1;
                    $taxName = 'Tax';
                    $text = $taxName . ' (' . $taxValue. '%)';
                    $result['db'] = [
                        'lead_tax_name' => $taxName,
                        'lead_tax_rate' => $taxRate,
                        'lead_tax_value' => $taxValue
                    ];
                    $result['estimate'] = [
                        'id' => $text,
                        'text' => $text,
                        'name' => $taxName,
                        'rate' => $taxRate,
                        'value' => $taxValue
                    ];
                    return $result;
                }
            }
        }
        return FALSE;
    }

    /**
     * @param $categoriesWithItems
     * @return array
     */
    public function getCategoryWithItemsForSelect2($categoriesWithItems): array
    {
        if(empty($categoriesWithItems))
            return [];
        foreach ($categoriesWithItems as $item){
            $check[] = $item;
            if(!$this->checkItems($check)){
                continue;
            }
            $services = [];
            $items = [];
            if(isset($item['products']))
                $items = $item['products'];
            elseif(isset($item['services']))
                $items = $item['services'];
            elseif(isset($item['items']))
                $items = $item['items'];
            $categoryWithItems = isset($item['categories_with_products']) ? $item['categories_with_products'] : $item['categories_with_services'];
            foreach ($items as $service){
                if($service['service_status'] == 1)
                    $services[] = [
                        'id' => $service['service_id'],
                        'text' => $service['service_name'],
                        'is_product' => $service['is_product'],
                        'cost' => $service['cost'],
                        'service_type_id' => $service['service_id'],
                        'service_markup' => $service['service_markup'],
                        'setups' => !empty($service['service_attachments']) ? $service['service_attachments'] : '[]',
                        'name' => $service['service_name'],
                        'description' => $service['service_description'],
                        'service_class_id' => $service['service_class_id'],
                        'service_is_collapsed' => $service['service_is_collapsed'],
                        'service_default_crews' => !empty($service['service_default_crews']) ? $service['service_default_crews'] : ''
                    ];
            }
            $childCategory = $this->getCategoryWithItemsForSelect2($categoryWithItems);
            $child = array_merge($services, $childCategory);
            $result[] = [
                'text' => $item['category_name'],
                'children' => $child,
            ];
            unset($check);
        }
        return !empty($result) ? $result : [];
    }

    /**
     * @param $categoriesWithItems
     * @return array
     */
    public function getCategoriesWithItemsForApp($categoriesWithItems): array
    {
        if(empty($categoriesWithItems))
            return [];
        foreach ($categoriesWithItems as $item){
            $check[] = $item;
            if(!$this->checkItems($check)){
                continue;
            }
            $services = [];
            $items = isset($item['products']) ? $item['products'] : $item['services'];
            $categoryWithItems = isset($item['categories_with_products']) ? $item['categories_with_products'] : $item['categories_with_services'];
            foreach ($items as $key => $service){
                if($service['service_status'] == 1) {
                    if (isset($service['service_attachments'])) {
                        $new_att = $this->getServiceAttachment($service['service_attachments']);
                        $service['service_attachments'] = json_encode($new_att);
                    }
                    if(!empty($service['service_class_id'])){
                        $class = QBClass::where('class_id', $service['service_class_id'])->first();
                        if(!empty($class))
                            $service['service_class_name'] = $class->class_name;
                    }
                    $services[] = $service;
                }
            }
            $childCategory = $this->getCategoriesWithItemsForApp($categoryWithItems);
            $child = array_merge($services, $childCategory);
            $result[] = [
                'text' => $item['category_name'],
                'children' => $child,
            ];
            unset($check);
        }
        return !empty($result) ? $result : [];
    }

    /**
     * @param array $estimates
     * @param array $leads
     * @return array
     */
    public function getEstimatesByFilterLeads(array $estimates, array $leads) : array
    {
        foreach ($estimates as $key => $estimate){
            if(in_array($estimate['lead_id'], $leads) === false)
                unset($estimates[$key]);
        }
        return $estimates;
    }

    /**
     * @param array $estimates
     * @return array
     */
    public function getEstimatesId(array $estimates) : array
    {
        $result = [];
        foreach ($estimates as $estimate)
            $result[] = $estimate['estimate_id'];
        return $result;
    }

    /**
     * @throws Exception
     */
    public function setEstimateDraftService(array $post): void
    {
        $file = sys_get_temp_dir()."/queue".$post['lead_id'].".txt";
        $this->checkLeadS3Queue($file);
        $serviceId = $post['service_id'];
        $serviceData = $post;
        unset($serviceData['client_id'], $serviceData['lead_id'], $serviceData['service_id']);
        $fileFullPath = $this->getDraftFullPath($post['client_id'], $post['lead_id']);
        $draftData = json_decode($this->draftDataRead($post['lead_id'],$fileFullPath), TRUE);
        // remove old draft item
        if(!empty($draftData['service_type_id']) && !empty($draftData['service_type_id'][$serviceId])) {
            foreach ($draftData as $key => $value) {
                if(isset($draftData[$key][$serviceId]))
                    unset($draftData[$key][$serviceId]);
            }
        }
        if(!empty($draftData['services']) && is_array($draftData['services']) && array_key_exists($serviceId, $draftData['services']))
            unset($draftData['services'][$serviceId]);
        elseif(!empty($draftData['products']) && is_array($draftData['products']) && array_key_exists($serviceId, $draftData['products']))
            unset($draftData['products'][$serviceId]);
        elseif(!empty($draftData['bundles']) && is_array($draftData['bundles']) && array_key_exists($serviceId, $draftData['bundles']))
            unset($draftData['bundles'][$serviceId]);

        $itemForDraft = [];
        foreach ($serviceData as $key => $value) {
            if(is_string($value[$serviceId]) && strpos($value[$serviceId], get_currency()) !== false)
                $value[$serviceId] = trim(str_replace([get_currency(), ','], '', $value[$serviceId]));
            if($key == 'expenses' && !empty($value[$serviceId])) {
                foreach ($value[$serviceId] as $id => $expense)
                    $value[$serviceId][$id]['amount'] = trim(str_replace([get_currency(), ','], '', $expense['amount']));
            }

            if($key == 'trailer_option')
                $itemForDraft['equipments']['transport']['trailers']['option'] = $value[$serviceId];
            elseif ($key == 'service_trailer')
                $itemForDraft['equipments']['transport']['trailers']['vehicle'] = $value[$serviceId];
            elseif ($key == 'vehicle_option')
                $itemForDraft['equipments']['transport']['vehicles']['option'] = $value[$serviceId];
            elseif ($key == 'service_vehicle')
                $itemForDraft['equipments']['transport']['vehicles']['vehicle'] = $value[$serviceId];
            elseif ($key == 'service_crew')
                $itemForDraft['crews'] = $value[$serviceId];
            elseif ($key == 'tools_option')
                $itemForDraft['equipments']['tools'] = $value[$serviceId];
            elseif ($key == 'is_view_in_pdf')
                $itemForDraft['is_view_in_pdf'] = $value[$serviceId] == 'on' ? 1 : 0;
            else
                $itemForDraft[$key] = $value[$serviceId];
        }

        if(isset($itemForDraft['ties_cost']) && isset($itemForDraft['ties_stump'])){
            $itemForDraft['service_price'] = $itemForDraft['ties_cost'] + $itemForDraft['ties_stump'];
        }

        if(!empty($itemForDraft['is_product'])){
            $bundleId = !empty($itemForDraft['bundles_services']) ? $itemForDraft['bundles_services'] : false;
            if(!empty($bundleId)){
                unset($itemForDraft['bundles_services']);
                unset($itemForDraft['bundles_services_name']);
                $draftData['items'][$bundleId]['items'][$serviceId] = $itemForDraft;
                $draftData['items'][$bundleId] = $this->sortDraftItems($draftData['items'][$bundleId]);
            }else
                $draftData['items'][$serviceId] = $itemForDraft;
        } elseif (!empty($itemForDraft['is_bundle'])){
            $itemForDraft['items'] = isset($draftData['items']) && isset($draftData['items'][$serviceId]) && !empty($draftData['items'][$serviceId]['items']) ? $draftData['items'][$serviceId]['items'] : [];
            $itemForDraft = $this->sortDraftItems($itemForDraft);
            $draftData['items'][$serviceId] = $itemForDraft;
            $draftData = $this->sortDraftItems($draftData);
        } else {
            $bundleId = !empty($itemForDraft['bundles_services']) ? $itemForDraft['bundles_services'] : null;
            if(!empty($bundleId)){
                unset($itemForDraft['bundles_services']);
                unset($itemForDraft['bundles_services_name']);
                $draftData['items'][$bundleId]['items'][$serviceId] = $itemForDraft;
                $draftData['items'][$bundleId] = $this->sortDraftItems($draftData['items'][$bundleId]);
            }else
                $draftData['items'][$serviceId] = $itemForDraft;
        }
        $draftData['last_update_date'] = getNowDateTime();
        $draftData['fileFullPath']     = $fileFullPath;

        $draftData = $this->sortDraftItems($draftData);

        $draftData['new_format_draft_tree']=true; // Transition to the new tree_id format 20.06.2022
        $this->draftDataWrite($post['lead_id'], $fileFullPath, json_encode($draftData));

        if (isset($file) && file_exists($file))
            unlink($file);
    }

    /**
     * @throws Exception
     */
    public function setAppEstimateDraftService(array $post): void
    {
        $serviceId = $post ['old_position'] ?? $post['position'];
        $serviceData = $post;
        unset($serviceData['client_id']/*, $serviceData['lead_id']*/); // added lead_id to the draft service data for debug RG 07.07.21
        $fileFullPath = $this->getDraftFullPath($post['client_id'], $post['lead_id']);
        $draftData = json_decode($this->draftDataRead($post['lead_id'],$fileFullPath), TRUE);

        $itemForDraft = [];
        $searchArray = array_merge($this->checkBundleDraft, $this->checkServiceDraft, $this->checkProductDraft);
        foreach ($serviceData as $key => $value) {
            if(array_key_exists($key, $searchArray)) {
                if($key == 'class')
                    $itemForDraft[$searchArray[$key]] = $value;
                elseif($key == 'serviceimages'){
                    $files = [];
                    foreach ($value as $fileKey => $fileVal){
                        $filePath = strtok(str_replace(base_url(), '', $fileVal['file']), '?');
                        $files[] = [
                            'filepath' => $filePath,
                            'url' => $fileVal['file'],
                            'type' => $fileVal['type'],
                            'name' => $fileVal['name'],
                            'uuid' => $fileVal['id'],
                            'show_client' =>  $fileVal['show_client'] ?? true
                        ];
                        $itemForDraft[$searchArray[$key]][] = $filePath;
                    }
                    if(!empty($files))
                        $draftData['pre_uploaded_files'][$serviceId] = $files;
                }
                elseif($key == 'trailer_option')
                    $itemForDraft['equipments']['transport']['trailers']['option'] = $value;
                elseif ($key == 'service_trailer')
                    $itemForDraft['equipments']['transport']['trailers']['vehicle'] = $value;
                elseif ($key == 'vehicle_option')
                    $itemForDraft['equipments']['transport']['vehicles']['option'] = $value;
                elseif ($key == 'service_vehicle')
                    $itemForDraft['equipments']['transport']['vehicles']['vehicle'] = $value;
                elseif ($key == 'service_crew')
                    $itemForDraft['crews'] = $value;
                elseif ($key == 'tools_option')
                    $itemForDraft['equipments']['tools'] = $value;
                elseif ($key == 'noTax')
                    $itemForDraft['non_taxable'] = empty($value) ? 0 : 1;
                else
                    $itemForDraft[$searchArray[$key]] = $value;
            }
        }
        if(!isset($serviceData['service_trailer']))
            $itemForDraft['equipments']['transport']['trailers']['vehicle'] = [""];
        if(!isset($serviceData['service_vehicle']))
            $itemForDraft['equipments']['transport']['vehicles']['vehicle'] = [""];

        if(!empty($draftData['services']) && is_array($draftData['services']) && array_key_exists($serviceId, $draftData['services']))
            unset($draftData['services'][$serviceId]);
        elseif(!empty($draftData['products']) && is_array($draftData['products']) && array_key_exists($serviceId, $draftData['products']))
            unset($draftData['products'][$serviceId]);
        elseif(!empty($draftData['bundles']) && is_array($draftData['bundles']) && array_key_exists($serviceId, $draftData['bundles']))
            unset($draftData['bundles'][$serviceId]);


        if(!empty($itemForDraft['is_product'])){
            $bundleId = !empty($itemForDraft['bundles_services']) ? $itemForDraft['bundles_services'] : null;
            if(!empty($bundleId)){
                unset($itemForDraft['bundles_services']);
                $draftData['items'][$bundleId]['items'][$serviceId] = $itemForDraft;
                $draftData['items'][$bundleId] = $this->sortDraftItems($draftData['items'][$bundleId]);
            }else
                $draftData['items'][$serviceId] = $itemForDraft;
        }
        elseif (!empty($itemForDraft['is_bundle'])){
            $items = [];
            if(!empty($itemForDraft['services'])) {
                $items = array_merge($items, $itemForDraft['services']);
                $itemForDraft['services'] = [];
            }
            if(!empty($itemForDraft['products'])) {
                $items = array_merge($items, $itemForDraft['products']);
                $itemForDraft['products'] = [];
            }
            if(!empty($itemForDraft['items'])) {
                $items = array_merge($items, $itemForDraft['items']);
                $itemForDraft['items'] = [];
            }
            $draftData['items'][$serviceId] = $itemForDraft;
        }
        else {
            $bundleId = !empty($itemForDraft['bundles_services']) ? $itemForDraft['bundles_services'] : null;
            if(!empty($bundleId)){
                unset($itemForDraft['bundles_services']);
                $draftData['items'][$bundleId]['items'][$serviceId] = $itemForDraft;
                $draftData['items'][$bundleId] = $this->sortDraftItems($draftData['items'][$bundleId]);
            }else
                $draftData['items'][$serviceId] = $itemForDraft;
        }
        $draftData['last_update_date'] = getNowDateTime();
        $draftData['fileFullPath']     = $fileFullPath;

        $draftData = $this->sortDraftItems($draftData);

        $this->draftDataWrite($post['lead_id'], $fileFullPath, json_encode($draftData));

        if (!empty($items)) {
            foreach ($items as $item) {
                $item['bundles_services'] = $serviceId;
                $item['client_id'] = $post['client_id'];
                $item['lead_id'] = $post['lead_id'];
                $this->setAppEstimateDraftService($item);
            }
        }

        if (isset($file) && file_exists($file))
            unlink($file);
    }

    /**
     * @throws Exception
     */
    public function setEstimateDraftField(array $post)
    {
        $fileFullPath = $this->getDraftFullPath($post['client_id'], $post['lead_id']);
        $draftData = json_decode($this->draftDataRead($post['lead_id'],$fileFullPath), TRUE);

        $field = $post['field'];
        $fieldValue = $post['value'];
        $fromApp = !empty($post['app']) ? true : false;

        if($field != 'array'){
            $draftData[$field] = $fieldValue;
        } elseif($fromApp){
            foreach($fieldValue as $key => $val){
                $draftData[array_key_first($val)] = $val[array_key_first($val)];
            }
        } else {
            foreach($fieldValue as $fieldVal){
                if(isset($fieldVal['field'])) {
                    $draftData[$fieldVal['field']] = $fieldVal['value'];
                }
            }
        }

        if(!isset($draftData['discount_percents']))
            $draftData['discount_percents'] = 0;
        $draftData['last_update_date'] = getNowDateTime();
        $draftData['fileFullPath']     = $fileFullPath;

//        return $draftData;
        $this->draftDataWrite($post['lead_id'], $fileFullPath, json_encode($draftData));

        if (isset($file) && file_exists($file))
            unlink($file);
    }

    /**
     * @throws Exception
     */
    public function deleteEstimateDraftItem($post): void
    {
        $file = sys_get_temp_dir()."/queue".$post['lead_id'].".txt";
        $this->checkLeadS3Queue($file);
        $serviceId = $post['service_id'];
        $fileFullPath = $this->getDraftFullPath($post['client_id'], $post['lead_id']);
        $draftData = json_decode($this->draftDataRead($post['lead_id'],$fileFullPath), TRUE);

        if(!empty($draftData['service_type_id']) && !empty($draftData['service_type_id'][$serviceId])){
            foreach ($draftData as $key => $value) {
                if(isset($draftData[$key][$serviceId]))
                    unset($draftData[$key][$serviceId]);
            }
        }else {
            $bundleId = !empty($post['bundle_id']) ? $post['bundle_id'] : null;

            if (!empty($draftData['products']) && !empty($draftData['products'][$serviceId]))
                unset($draftData['products'][$serviceId]);
            elseif (!empty($draftData['services']) && !empty($draftData['services'][$serviceId]))
                unset($draftData['services'][$serviceId]);
            elseif (!empty($draftData['bundles']) && !empty($draftData['bundles'][$serviceId]))
                unset($draftData['bundles'][$serviceId]);
            elseif (!empty($draftData['bundles']) && !empty($draftData['bundles'][$bundleId])) {
                unset($draftData['bundles'][$bundleId]['services'][$serviceId]);
                unset($draftData['bundles'][$bundleId]['products'][$serviceId]);
            }
            elseif (!empty($draftData['items']) && !empty($draftData['items'][$bundleId])) {
                unset($draftData['items'][$bundleId]['items'][$serviceId]);
                if (($key = array_search($serviceId, $draftData['items'][$bundleId]['order_items'])) !== false)
                    unset($draftData['items'][$bundleId]['order_items'][$key]);
            }
            elseif (!empty($draftData['items']) && !empty($draftData['items'][$serviceId])) {
                unset($draftData['items'][$serviceId]);
                if(($key = array_search($serviceId, $draftData['order_items'])) !== false)
                    unset($draftData['order_items'][$key]);
            }

            if(!empty($draftData['pre_uploaded_files']))
                unset($draftData['pre_uploaded_files'][$serviceId]);
        }
        $draftData['last_update_date'] = getNowDateTime();
        $draftData['fileFullPath']     = $fileFullPath;

        $this->draftDataWrite($post['lead_id'], $fileFullPath, json_encode($draftData));

        if (isset($file) && file_exists($file))
            unlink($file);
    }

    /**
     * @throws Exception
     */
    public function setEstimateDraftFiles($post): void
    {
        $file = sys_get_temp_dir()."/queue".$post['lead_id'].".txt";
        $this->checkLeadS3Queue($file);
        $fileFullPath = $this->getDraftFullPath($post['client_id'], $post['lead_id']);
        $draftData = json_decode($this->draftDataRead($post['lead_id'],$fileFullPath), TRUE);
        $files = isset($post['files']) ? $post['files'] : [];

        $draftData['pre_uploaded_files'] = $draftData['pre_uploaded_files'] ?? [];
        $draftData['pre_uploaded_files'][$post['service_id']] = $files;
        $draftData['last_update_date'] = getNowDateTime();
        $draftData['fileFullPath']     = $fileFullPath;

        $this->draftDataWrite($post['lead_id'], $fileFullPath, json_encode($draftData));

        if (isset($file) && file_exists($file))
            unlink($file);
    }

    public function preupload($post){
        $file = sys_get_temp_dir()."/queue".$post['lead_id'].".txt";

        $leadId = $post['lead_id'];
        $estimateId = $post['estimate_id'];
        $serviceId = $post['service_id'];
        $uuids = $post['files_uuids'];
        $uuids = $uuids ? explode(',', $uuids) : [];

        $lead = $this->CI->mdl_leads->find_by_id($leadId);
        if(!$lead)
            return false;

        $path = 'uploads/clients_files/' . $lead->client_id . '/leads/tmp/' . str_replace('-L', '-E', $lead->lead_no) . '/';
        $max = 1;
        $updateEstimatePdfFiles = FALSE;

        if($estimateId && $serviceId) {  // if upload file for exists service
            $estimate = $this->CI->mdl_estimates_orm->get($estimateId);
            $estimate_pdf_files = $estimate->estimate_pdf_files ? json_decode($estimate->estimate_pdf_files, TRUE) : [];
            $path = 'uploads/clients_files/' . $lead->client_id . '/estimates/' . str_replace('-L', '-E', $lead->lead_no) . '/' . $serviceId . '/';
            $files = bucketScanDir($path);

            if (!empty($files) && $files) {
                foreach($files as $file)
                {
                    preg_match('/estimate_no_' . str_pad($lead->lead_id, 5, '0', STR_PAD_LEFT) . '-E.*?_([0-9]{1,})/is', $file, $num);
                    if(isset($num[1]) && ($num[1] + 1) > $max)
                        $max = $num[1] + 1;
                    preg_match('/pdf_estimate_no_' . str_pad($lead->lead_id, 5, '0', STR_PAD_LEFT) . '-E.*?_([0-9]{1,})/is', $file, $num1);
                    if(isset($num1[1]) && ($num1[1] + 1) > $max)
                        $max = $num[1] + 1;
                }
            }
        }

        $photos = [];
        if (isset($_FILES['file']) && !$_FILES['file']['error'] && !isset($_FILES['files'])) {
            $_FILES['files']['name'][] = $_FILES['file']['name'];
            $_FILES['files']['type'][] = $_FILES['file']['type'];
            $_FILES['files']['tmp_name'][] = $_FILES['file']['tmp_name'];
            $_FILES['files']['error'][] = $_FILES['file']['error'];
            $_FILES['files']['size'][] = $_FILES['file']['size'];
        }
        if (isset($_FILES['files']) && is_array($_FILES['files'])) {
            $this->CI->load->library('upload');
            foreach ($_FILES['files']['name'] as $key => $val) {

                $_FILES['file']['name'] = $_FILES['files']['name'][$key];
                $_FILES['file']['type'] = $_FILES['files']['type'][$key];
                $_FILES['file']['tmp_name'] = $_FILES['files']['tmp_name'][$key];
                $_FILES['file']['error'] = $_FILES['files']['error'][$key];
                $_FILES['file']['size'] = $_FILES['files']['size'][$key];

                if($estimateId && $serviceId) { // if upload file for exists service
                    $ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
                    $suffix = $ext == 'pdf' ? 'pdf_' : NULL;
                    $config['file_name'] = $suffix . 'estimate_no_' . str_replace('-L', '-E', $lead->lead_no) . '_' . $max++ . '.' . $ext;
                } else {
                    $config['remove_spaces'] = TRUE;
                    $config['encrypt_name'] = TRUE;
                }
                $config['upload_path'] = $path;
                $config['allowed_types'] = 'gif|jpg|jpeg|png|pdf|GIF|JPG|JPEG|PNG|PDF';

                $this->CI->upload->initialize($config);
                if ($this->CI->upload->do_upload('file')) {
                    $uploadData = $this->CI->upload->data();
                    $photos[] = [
                        'uuid' => $uuids[$key] ?? NULL,
                        'filepath' => $path . $uploadData['file_name'],
                        'name' => $uploadData['file_name'],
                        'size' => $_FILES['file']['size'],
                        'type' => $_FILES['file']['type'],
                        'url' => base_url($path . $uploadData['file_name'])
                    ];
                    if($estimateId && $serviceId) { // if upload file for exists service
                        $estimate_pdf_files[] = $path . $uploadData['file_name'];
                        $updateEstimatePdfFiles = TRUE;
                    }
                } else {
                    $photos[] = [
                        'error' => strip_tags($this->CI->upload->display_errors())
                    ];
                }
            }
        }
        if($updateEstimatePdfFiles)
            $this->CI->mdl_estimates_orm->update($estimateId, ['estimate_pdf_files' => json_encode($estimate_pdf_files)]);

        if(file_exists($file))
            unlink($file);

        return $photos;
    }

    /**
     * @param $clientId
     * @param $leadId
     * @return false|mixed
     * @throws Exception
     */
    public function getEstimateDraftData($clientId, $leadId){
        $fileFullPath = $this->getDraftFullPath($clientId, $leadId);
        return $this->draftDataRead($leadId,$fileFullPath);
    }

    /**
     * @param $clientId
     * @param $leadId
     * @return array|false
     */
    public function getEstimateDraftInfo($clientId, $leadId){
        $fileFullPath = $this->getDraftFullPath($clientId, $leadId);
        return bucket_get_file_info($fileFullPath);
    }

    /**
     * @param $clientId
     * @param $leadId
     * @return false|mixed
     * @throws Exception
     */
    public function getEstimateDraftScheme($clientId, $leadId){
        $fileFullPath = $this->getDraftFullPathScheme($clientId, $leadId);
        return bucket_read_file($fileFullPath);
    }

    /**
     * @param $clientId
     * @param $leadId
     * @return string
     */
    private function getAppDraftFullPathScheme($clientId, $leadId): string
    {
        $schemeFilename = str_pad($leadId, 5, '0', STR_PAD_LEFT) . '_scheme_elements';
        $dir = $this->getDraftDir($clientId);
        return $dir . $schemeFilename;
    }

    /**
     * @param $clientId
     * @param $leadId
     * @return false|mixed
     * @throws Exception
     */
    public function getEstimateDraftSchemeForApp($clientId, $leadId){
        $fileFullPath = $this->getAppDraftFullPathScheme($clientId, $leadId);
        $data = bucket_read_file($fileFullPath);

        if(!empty($data)){
            $sourceData = null;
            $data = (object)json_decode($data);
            $sourceDataPath = 'uploads/tmp/' . $clientId . '/' . str_pad($leadId, 5, '0', STR_PAD_LEFT) . '_scheme_elements';
            if(is_bucket_file($sourceDataPath)) {
                $sourceData = json_decode(bucket_read_file($sourceDataPath));
            }
            $data->elements = $sourceData;
            $data->result =  base_url() . 'uploads/tmp/' . $clientId . '/' . str_pad($leadId, 5, '0', STR_PAD_LEFT) . '_scheme.png';
            $schemePath = 'uploads/tmp/' . $clientId . '/source/' .  str_pad($leadId, 5, '0', STR_PAD_LEFT) . '_scheme.png';
            if(is_bucket_file($schemePath)){
                $data->original = base_url() . $schemePath;
            }
        }

//        if(isset($data->link)) {
//            $original = $data->link;
//            unset($data->link);
//            unset($data->html);
//            $data->original = base_url() . $original;
//        }
        return $data;
    }

    /**
     * @throws Exception
     */
    public function getEstimateDraftDataForApp($clientId, $leadId){
        $fileFullPath = $this->getDraftFullPath($clientId, $leadId);
        $data = $this->draftDataRead($leadId,$fileFullPath);
        $scheme = $this->getEstimateDraftSchemeForApp($clientId, $leadId);

        if(!empty($data)){
            $data = json_decode($data);
            if(!empty($data->services)){
                foreach ($data->services as $service){
                    $service->equipments = isset($service->equipments) ? $this->getDraftEstimateServiceEquipmentForApp($service->equipments) : '';
                    $serviceFromDB = isset($service->service_type_id) ? Service::find($service->service_type_id) : '';
                    $service->service_name = !empty($serviceFromDB) ? $serviceFromDB->service_name : '';
                    $service->default_markup = !empty($serviceFromDB) ? $serviceFromDB->service_markup : 0;
                    $service->service_price = !empty($service->service_price) ? $service->service_price : 0;
                }
            }
            if(!empty($data->bundles)){
                foreach ($data->bundles as $bundle){
                    if(!empty($bundle->services)){
                        foreach ($bundle->services as $service){
                            $service->equipments = isset($service->equipments) ? $this->getDraftEstimateServiceEquipmentForApp($service->equipments) : '';
                            $serviceFromDB = isset($service->service_type_id) ? Service::find($service->service_type_id) : '';
                            $service->service_name = !empty($serviceFromDB) ? $serviceFromDB->service_name : '';
                            $service->default_markup = !empty($serviceFromDB) ? $serviceFromDB->service_markup : 0;
                            $service->service_price = !empty($service->service_price) ? $service->service_price : 0;
                        }
                    }
                    if(!empty($bundle->products)){
                        foreach ($bundle->products as $product){
                            $productFromDB = isset($product->service_type_id) ? Service::find($product->service_type_id) : '';
                            $product->service_name = !empty($productFromDB) ? $productFromDB->service_name : '';
                            $product->service_price = !empty($product->service_price) ? $product->service_price : 0;
                        }
                    }
                    $bundleFromDB = Service::find($bundle->service_type_id);
                    $bundle->service_name = !empty($bundleFromDB) ? $bundleFromDB->service_name : '';
                    $bundle->is_view_in_pdf = isset($bundle->is_view_in_pdf) ? $bundle->is_view_in_pdf : false;
                    $bundle->service_price = !empty($bundle->service_price) ? $bundle->service_price : 0;
                }
            }
            if(!empty($data->products)){
                foreach ($data->products as $product){
                    $productFromDB = isset($product->service_type_id) ? Service::find($product->service_type_id) : '';
                    $product->service_name = !empty($productFromDB) ? $productFromDB->service_name : '';
                }
            }
            if(!empty($scheme)){
                $data->presave_scheme = $scheme;
            }
            if(!isset($data->show_client))
                $data->show_client = true;
            if(!empty($data->items)){
                foreach($data->items as $item){
                    $itemFromDB = isset($item->service_type_id) ? Service::find($item->service_type_id) : '';
                    $item->service_name = !empty($itemFromDB) ? $itemFromDB->service_name : '';
                    $item->service_price = !empty($item->service_price) ? $item->service_price : 0;
                    $item->default_markup = !empty($itemFromDB) ? $itemFromDB->service_markup : 0;
                    $item->equipments = isset($item->equipments) ? $this->getDraftEstimateServiceEquipmentForApp($item->equipments) : '';
                    if(!empty($item->is_bundle) && !empty($item->items)){
                        $item->is_view_in_pdf = isset($item->is_view_in_pdf) ? $item->is_view_in_pdf : false;
                        foreach($item->items as $bundleItem){
                            $bundleItemFromDB = isset($bundleItem->service_type_id) ? Service::find($bundleItem->service_type_id) : '';
                            $bundleItem->service_name = !empty($bundleItemFromDB) ? $bundleItemFromDB->service_name : '';
                            $bundleItem->service_price = !empty($bundleItem->service_price) ? $bundleItem->service_price : 0;
                            $bundleItem->default_markup = !empty($bundleItemFromDB) ? $bundleItemFromDB->service_markup : 0;
                            $bundleItem->equipments = isset($bundleItem->equipments) ? $this->getDraftEstimateServiceEquipmentForApp($bundleItem->equipments) : '';
                        }
                    }
                }
            }
            $data->disposal_brush = isset($data->disposal_brush) ? $data->disposal_brush : 1;
            $data->disposal_wood = isset($data->disposal_wood) ? $data->disposal_wood : 1;
            $data->clean_up = isset($data->clean_up) ? $data->clean_up : 1;
            $data = json_encode($data);
        }
        return $data;
    }

    /**
     * @return array
     * @throws Exception
     */
    public function getAllEstimateDraftForApp(): array
    {
        $dir = 'uploads/tmp/';
        $files = bucketScanDir($dir,false,true);
        $result = [];
        $findMe = '_estimate_draft';
        if(!empty($files)){
            foreach ($files as $file){
                if(strpos($file, $findMe)){
                    $id = str_replace($findMe, '', $file);
                    $estimate = Estimate::where(['lead_id' => $id])->first();
                    if(!$estimate) {
                        $lead = Lead::where(['lead_id' => $id])->first();
                        if($lead) {
                            $draft = $this->getEstimateDraftData($lead['client_id'], $id);
                            if(empty($draft))
                                continue;
                            $lead = $lead->toArray();
                            $client = Client::where(['client_id' => $lead['client_id']])->first()->toArray();
                            $clientContact = ClientsContact::where(['cc_id' => $lead['lead_cc_id']])->first();
                            if(empty($clientContact))
                                $clientContact = ClientsContact::where(['cc_client_id' => $lead['client_id'], 'cc_print' => 1])->first();

                            $result[] = [
                                'lead_id' => $id,
                                'address' => $lead['lead_address'],
                                'date_created' => $lead['lead_date_created'],
                                'client_name' => $client['client_name'],
                                'contact_phone' => $clientContact->toArray()['cc_phone'],
                                'total_estimate_price' => $this->getEstimateDraftTotal(json_decode($draft, true))
                            ];
                        }
                    }
                }
            }
        }
        return $result;

    }

    /**
     * @param $clientId
     * @param $leadId
     * @return string
     */
    public function getDraftFullPath($clientId, $leadId): string
    {
        $filename = $leadId . '_estimate_draft';
        $dir = $this->getDraftDir($clientId);
        return $dir . $filename;
    }

    /**
     * @param $clientId
     * @param $leadId
     * @return string
     */
    private function getDraftFullPathScheme($clientId, $leadId): string
    {
        $schemeFilename = str_pad($leadId, 5, '0', STR_PAD_LEFT) . '_source_html';
        $dir = $this->getDraftDir($clientId);
        return $dir . $schemeFilename;
    }

    /**
     * @param $clientId
     * @return string
     */
    private function getDraftDir($clientId): string
    {
        $dir = 'uploads/tmp/';
        $subDirs = [$clientId];
        foreach ($subDirs as $key => $value) {
            $dir .= $value . '/';
        }
        return $dir;
    }

    /**
     * @param $attachments
     * @return array
     */
    private function getServiceAttachment($attachments): array
    {
        $new_att = [];
        if(isset($attachments)) {
            $i = 0;
            foreach (json_decode($attachments) as $attachment) {

                $transport = new stdClass();
                $tool = [];

                if (isset($attachment->vehicle_id)) {
                    $transport->vehicles['tool_id'] = $attachment->vehicle_id;
                }
                if (isset($attachment->vehicle_option)) {
                    $transport->vehicles['option'] = $attachment->vehicle_option;
                }

                if (isset($attachment->trailer_id)) {
                    $transport->trailers['tool_id'] = $attachment->trailer_id;
                }
                if (isset($attachment->trailer_option)) {
                    $transport->trailers['option'] = $attachment->trailer_option;
                }
                if (isset($attachment->tool_id) && isset($attachment->tools_option)) {
                    foreach ($attachment->tool_id as $key => $tool_id) {
                        $tool[$tool_id] = $attachment->tools_option[$key];
                    }
                }
                $new_att[$i] = ['transport' => $transport, 'tool' => $tool];
                $i++;
            }
        }
        return $new_att;
    }

    /**
     * @param $categoriesWithItems
     * @return bool
     */
    private function checkItems($categoriesWithItems): bool
    {
        foreach ($categoriesWithItems as $item){
            $items = [];
            if(isset($item['products']))
                $items = $item['products'];
            elseif(isset($item['services']))
                $items = $item['services'];
            elseif(isset($item['items']))
                $items = $item['items'];
            $categoryWithItems = isset($item['categories_with_products']) ? $item['categories_with_products'] : $item['categories_with_services'];
            if(!empty($items))
                return true;
            if(!empty($categoryWithItems)) {
                if($this->checkItems($categoryWithItems))
                    return true;
            }
        }
        return false;
    }

    /**
     * @param array $serviceData
     * @param array $itemForDraft
     * @param $serviceId
     * @return array
     */
    private function setDefaultSetups(array $serviceData, array $itemForDraft, $serviceId): array
    {
        if(!isset($serviceData['service_access'][$serviceId]))
            $itemForDraft['service_access'] = 0;
        if(!isset($serviceData['pre_uploaded_files'][$serviceId]))
            $itemForDraft['pre_uploaded_files'] = [];
        if(!isset($serviceData['pre_uploaded_files'][$serviceId]))
            $itemForDraft['pre_uploaded_files'] = [];
        return $itemForDraft;
    }

    /**
     * @param $draft
     * @return int|mixed
     */
    private function getEstimateDraftTotal($draft){
        $total = 0;
        if(!empty($draft['service_price'])){
            foreach ($draft['service_price'] as $key => $price) {
                if(!empty($draft['is_bundle']) && !empty($draft['is_bundle'][$key]))
                    continue;
                $price = str_replace(',', '', $price);
                preg_match('!\d+(?:\.\d+)?!', $price, $matches);
                $total += !empty($matches[0]) ? $matches[0] : 0;
            }
        } elseif (!empty($draft['services']) || !empty($draft['bundles']) || !empty($draft['products'])){
            $items = [];
            if(!empty($draft['services']))
                $items = array_merge($items, $draft['services']);
            if(!empty($draft->bundles))
                $items = array_merge($items, $draft['services']);
            if(!empty($draft->products))
                $items = array_merge($items, $draft['services']);

            foreach ($items as $key => $item) {
                $price = str_replace(',', '', $item['service_price']);
                preg_match('!\d+(?:\.\d+)?!', $price, $matches);
                $total += !empty($matches[0]) ? $matches[0] : 0;
            }
        }
        return $total;
    }


    /**
     * @param bool $isConfirmedWeb
     */
    public function setIsConfirmedWeb(bool $isConfirmedWeb): void
    {
        $this->isConfirmedWeb = $isConfirmedWeb;
    }

    /**
     * @return mixed
     */
    public function getIsConfirmedWeb()
    {
        return $this->isConfirmedWeb;
    }

    /**
     * @param $equipment
     * @param string $type
     * @return array
     */
    public function getDraftEstimateServiceEquipmentForApp($equipment, $type = 'app'): array
    {
        $result = [];
        if(!empty($equipment)){
            if(!empty($equipment->transport->vehicles) || !empty($equipment->transport->trailers) ) {
                $trailers = !empty($equipment->transport->trailers) ? $equipment->transport->trailers : [];
                $vehicles = !empty($equipment->transport->vehicles) ? $equipment->transport->vehicles : [];
                $tools = [];
                $toolsIds = [];
                $equipments = !empty($vehicles) ? $vehicles->vehicle : $trailers->vehicle;
                foreach ($equipments as $key => $val) {
                    if(isset($equipment->tools))
                        foreach ($equipment->tools as $toolKey => $toolVal){
                            if(is_object($toolVal))
                                $toolVal = (array)$toolVal;

                            $tools[$toolKey] = !empty($toolVal[$key]) ? $toolVal[$key] : '';
                            $toolsIds[] = $toolKey;
                        }
                    if(!empty($trailers) && !empty($trailers->option) && is_object($trailers->option))
                        $trailers->option = (array)$trailers->option;
                    if(!empty($vehicles) && !empty($vehicles->option) && is_object($vehicles->option))
                        $vehicles->option = (array)$vehicles->option;
                    if(!empty($trailers) && !empty($trailers->vehicle) && is_object($trailers->vehicle))
                        $trailers->vehicle = (array)$trailers->vehicle;
                    if(!empty($vehicles) && !empty($vehicles->vehicle) && is_object($vehicles->vehicle))
                        $vehicles->vehicle = (array)$vehicles->vehicle;

                    $item = [
                        'equipment_attach_id' => !empty($trailers->vehicle) && !empty($trailers->vehicle[$key]) ? $trailers->vehicle[$key] : '',
                        'equipment_attach_option' => !empty($trailers->option) && !empty($trailers->option[$key]) ? $trailers->option[$key] : '',
                        'equipment_item_id' => !empty($vehicles->vehicle) && !empty($vehicles->vehicle[$key]) ? $vehicles->vehicle[$key] : '',
                        'equipment_item_option' => !empty($vehicles->option) && !empty($vehicles->option[$key]) ? $vehicles->option[$key] : '',
                        'equipment_tools_option' => $tools,
                        'equipment_attach_tool' => $toolsIds
                    ];
                    if($type == 'previewPDF'){
                        $trailer = !empty($item['equipment_attach_id']) ? $this->CI->mdl_vehicles->get($item['equipment_attach_id']) : null;
                        $vehicle = !empty($item['equipment_attach_id']) ? $this->CI->mdl_vehicles->get($item['equipment_item_id']) : null;
                        $item['vehicle_name'] = !empty($vehicle) ? $vehicle->vehicle_name : '';
                        $item['trailer_name'] = !empty($trailer) ? $trailer->vehicle_name : '';
                    }

                    $result[] = $type == 'previewPDF' ? (object)$item : $item;
                }
            }
        }
        return $result;
    }

    /**
     * @param $clientId
     * @param $leadId
     * @param $brandId
     * @return array
     * @throws Exception
     */
    public function getPreviewDraftEstimate($clientId, $leadId, $brandId): array
    {
        if(empty($clientId) || empty($leadId))
            return [];

        $fileFullPath = $this->getDraftFullPath($clientId, $leadId);
        $draftData = $this->draftDataRead($leadId,$fileFullPath);
        if(empty($draftData))
            return [];
        $draftData = json_decode($draftData, true);
        $lead = Lead::where('lead_id', $leadId)->first();

        $data['title'] = 'Estimates PDF';
        $data['client_data'] = $this->CI->mdl_clients->find_by_id($clientId);
        $data['client_contact'] = $this->CI->mdl_clients->get_primary_client_contact($clientId);
        $data["equipment_items"] =  $this->CI->mdl_vehicles->get_many_by(array('vehicle_trailer IS NULL', 'vehicle_disabled' => NULL));
        $data["tools"] = $this->CI->mdl_vehicles->get_many_by(array('vehicle_trailer' => 2));
        $data['crews_active'] = $this->CI->crew_model->get_crewdata(array('crew_status' => 1, 'crew_id <>' => 0))->result_array();
        if(!empty($lead) && !empty($lead->lead_author_id))
            $data['user_data'] = $this->CI->mdl_user->find_by_id($lead->lead_author_id);

        $defaultTax = getDefaultTax();
        $taxName = !empty($draftData['tax_name']) ? $draftData['tax_name'] : $defaultTax['name'];
        $taxValue = !empty($draftData['tax_value']) ? $draftData['tax_value'] : $defaultTax['value'];

        $data['estimate_data'] = new stdClass();
        $data['estimate_data']->estimate_brand_id = $brandId;
        $data['estimate_data']->estimate_no = str_pad($leadId, 5, '0', STR_PAD_LEFT) . '-E';
        $data['estimate_data']->discount_total = !empty($draftData['discount']) ? $draftData['discount'] : 0;
        $data['estimate_data']->discount_in_percents = !empty($draftData['discount_percents']) ? $draftData['discount_percents'] : 0;
        $data['estimate_data']->discount_percents_amount = !empty($draftData['discount_percents']) && !empty($draftData['discount']) ? $draftData['discount'] : 0;
        $data['estimate_data']->tree_inventory_pdf = !empty($draftData['tree_inventory_pdf']) ? $draftData['tree_inventory_pdf'] : 0;
        $data['estimate_data']->estimate_hst_disabled = !empty($draftData['tax_include']) && $draftData['tax_include'] == 1 ? 2 : 0;;
        $data['estimate_data']->estimate_tax_name = $taxName;
        $data['estimate_data']->estimate_tax_value = $taxValue;
        $data['estimate_data']->estimate_review_number = 1;
        $data['estimate_data']->estimate_review_date = date(getDateFormat());
        $data['estimate_data']->date_created = date(getDateFormat());
        $data['estimate_data']->client_id = $clientId;
        $data['estimate_data']->brush_disposal = isset($draftData['disposal_brush']) ? ($draftData['disposal_brush'] == 1 ? 'yes' : false) : 'yes';
        $data['estimate_data']->full_cleanup = isset($draftData['clean_up']) ? ($draftData['clean_up'] == 1 ? 'yes' : false) : 'yes';
        $data['estimate_data']->leave_wood = isset($draftData['disposal_wood']) ? ($draftData['disposal_wood'] == 1 ? 'yes' : false) : 'yes';
        $data['estimate_data']->estimate_pdf_files = [];
        $data['draftData'] = $draftData;

        if(!empty($lead)) {
            $data['estimate_data']->lead_address = $lead->lead_address;
        }
        $items = [];
        if(!empty($draftData['items']))
            $items = array_merge($items, $this->getItemForPreviewPDF($draftData['items'], 'items'));
        if(!empty($draftData['services']))
            $items = array_merge($items, $this->getItemForPreviewPDF($draftData['services'], 'services'));
        if(!empty($draftData['products']))
            $items = array_merge($items, $this->getItemForPreviewPDF($draftData['products'], 'products'));
        if(!empty($draftData['bundles']))
            foreach ($draftData['bundles'] as $key => $bundle){
                $items[$key] = $this->getBundleForPreviewPDF($bundle, $key);
            }

        $total = $this->getTotalForPreviewPDF($items);
        $taxableTotal = $this->getTotalForPreviewPDF($items, true);
        $data['estimate_data']->mdl_services_orm = $this->getEquipmentsForPreviewPDF($items);

        if($data['estimate_data']->discount_in_percents){
            $data['estimate_data']->discount_total = $total * $draftData['discount'] / 100;
        }

        $taxablePercentFromTotal = $taxableTotal / ($total ?: 1);
        $discount = $data['estimate_data']->discount_total * (($taxablePercentFromTotal == 0) ? 1 : $taxablePercentFromTotal);
        $data['estimate_data']->total_tax = ($taxableTotal -  $data['estimate_data']->discount_total) * $taxValue / 100;
        if($discount)
            $data['estimate_data']->total_tax = ($taxableTotal - $discount) * $taxValue / 100;
        $data['estimate_data']->total_with_tax = $total - $data['estimate_data']->discount_total + $data['estimate_data']->total_tax;

        if($data['estimate_data']->estimate_hst_disabled == 2){
            $data['estimate_data']->total_with_tax = $total;
            $data['estimate_data']->total_tax = ($taxableTotal / 100)*$taxValue;
            $data['estimate_data']->discount_total = 0;
        }

        $pathScheme = 'uploads/tmp/' . $clientId . '/' . str_pad($leadId, 5, '0', STR_PAD_LEFT) . '_scheme.png';
        if(is_bucket_file($pathScheme)) {
            $data['estimate_data']->estimate_pdf_files = ['scheme' => $pathScheme] + $data['estimate_data']->estimate_pdf_files;
        }

        // add tree inventory map
        $treeInventoryMapPath = inventory_screen_path($clientId, $leadId . '.png');
        if(is_bucket_file($treeInventoryMapPath))
            array_unshift($data['estimate_data']->estimate_pdf_files, $treeInventoryMapPath);

        $treeInventoryMapPath = inventory_screen_path($clientId, $leadId . '_tree_inventory_map.png');
        if(is_bucket_file($treeInventoryMapPath))
            array_unshift($data['estimate_data']->estimate_pdf_files, $treeInventoryMapPath);

        if(!empty($draftData['pre_uploaded_files']))
            $data['estimate_data']->estimate_pdf_files = array_replace_recursive($data['estimate_data']->estimate_pdf_files, $this->getFilesForPreviewPDF($draftData['pre_uploaded_files']));

        $files = $this->sortDraftEstimateFiles($items, $data['estimate_data']->estimate_pdf_files);
        $files = $this->removeAudioVideoFiles($files);
        $data['estimate_data']->estimate_pdf_files = json_encode($files);
        $file =  'Estimate.pdf';

        $estimateTreeInventoryServicesData = [];
        $treeInventoryWorkTypes = [];
        $treeInventoryPriorities = [];
        foreach ($items as $key => $value){
            // add tree inventory work types
            if(isset($value['ties_number']) && !empty($value['ties_number'])){
                $estimateTreeInventoryServicesData[$key] = $value;
                $treeInventoryPriorities[] = $value['ties_priority'];
                unset($items[$key]);
                $workTypes=[];
                foreach(json_decode($value['ties_work_types']) as $oneWorkType){
                    $getInfo=WorkType::where('ip_id',$oneWorkType)->first();
                    $workTypes[]=$getInfo->ip_name_short;
                }
//                $workTypes = TreeInventoryWorkTypes::where('tiwt_tree_id', $value['id'])->with('work_type')->get()->pluck('work_type')->pluck('ip_name_short')->toArray();
                $treeInventoryWorkTypes = array_merge($treeInventoryWorkTypes, $workTypes);
                if(!empty($workTypes) && is_array($workTypes)){
                    $estimateTreeInventoryServicesData[$key]['work_types'] = implode(', ', $workTypes);
                }
                $estimateTreeInventoryServicesData[$key]['ties_priority'] = ucfirst(substr($value['ties_priority'], 0,1));
                // add tree name
                $tiesType = TreeType::find($value['ties_type']);
                if(!empty($tiesType) && is_object($tiesType))
                    $estimateTreeInventoryServicesData[$key]['ties_type'] = $tiesType->trees_name_eng . ' (' . $tiesType->trees_name_lat . ')';
            }
        }
        if(!empty($estimateTreeInventoryServicesData)){
            if(!empty($treeInventoryWorkTypes))
                $data['work_types'] = WorkType::whereIn('ip_name_short', $treeInventoryWorkTypes)->get()->toArray();
            if(!empty($treeInventoryPriorities))
                $data['tree_inventory_priorities'] = array_unique($treeInventoryPriorities);
        }
        $data['estimate_tree_inventory_services_data'] = $estimateTreeInventoryServicesData;
        $data['estimate_services_data'] = $items;

        list($result, $view) = Modules::find('pdf_templates/' . config_item('company_dir') . '/estimate_pdf', 'includes', 'views/');
        if($result) {
            $html = $this->CI->load->view('includes/pdf_templates/' . config_item('company_dir') . '/' . 'estimate_pdf', $data, TRUE);
        } else {
            $html = $this->CI->load->view('includes/pdf_templates/estimate_pdf', $data, TRUE);
        }

//        $html = $this->CI->load->view('includes/pdf_templates/estimate_pdf', $data, TRUE);
        return array('file' => $file, 'html' => $html, 'files' =>  $data['estimate_data']->estimate_pdf_files ? json_decode($data['estimate_data']->estimate_pdf_files, true) : [], 'estimate'=>$data['estimate_data']);
    }


    public function getBundleForPreviewPDF($bundle, $id){
        $itemFromDB = Service::where('service_id', $bundle['service_type_id'])->first();
        $bundle['service_name'] = $itemFromDB->service_name;
        $bundle['is_product'] = 0;
        $bundle['is_bundle'] = 1;
        $bundle['id'] = $id;
        $bundleRecords = [];
        $bundleTotal = 0;
        if(!empty($bundle['services'])) {
            $bundleRecords = array_merge($bundleRecords, $this->getItemForPreviewPDF($bundle['services'], 'services', true));
            $bundleTotal += $this->getTotalForPreviewPDF($bundle['services']);
        }
        if(!empty($bundle['products'])) {
            $bundleRecords = array_merge($bundleRecords, $this->getItemForPreviewPDF($bundle['products'], 'products', true));
            $bundleTotal += $this->getTotalForPreviewPDF($bundle['products']);
        }
        if(!empty($bundle['items'])) {
            $bundleRecords = array_merge($bundleRecords, $this->getItemForPreviewPDF($bundle['items'], 'items', true));
            $bundleTotal += $this->getTotalForPreviewPDF($bundle['items']);
        }

        $bundle['service_price'] = $bundleTotal;
        $bundle['bundle_records'] = $bundleRecords;
        $bundle['is_view_in_pdf'] = $bundle['is_view_in_pdf'] ?? false;
        $item[$id] = $bundle;
        return $bundle;
    }

    /**
     * @param array $items
     * @param string $type
     * @param bool $isBundle
     * @return array
     */
    public function getItemForPreviewPDF(array $items, string $type, bool $isBundle = false): array
    {
        $result = [];
        foreach ($items as $key => $item){
            if($type == 'services' || (empty($item['is_product']) && empty($item['is_bundle']))) {
                $item['is_product'] = 0;
                if(isset($item['tree_inventory_title']))
                    $item['estimate_service_ti_title'] = $item['tree_inventory_title'];
            }elseif($type == 'products' || !empty($item['is_product'])){
                $item['is_product'] = 1;
                $item['cost'] = $item['product_cost'];
            }
            if(!empty($item['is_bundle'])){
                $item = $this->getBundleForPreviewPDF($item, $key);
            } else{
                $item['is_bundle'] = 0;
                $itemFromDB = Service::where('service_id', $item['service_type_id'])->first();
                $item['service_name'] = $itemFromDB->service_name;
                $item['id'] = $key;
                $item['non_taxable'] = isset($item['non_taxable']) ? $item['non_taxable'] : 0;
            }
            $result[$key] = $isBundle ? (object)$item : $item;

        }
        return $result;
    }

    /**
     * @param array $items
     * @param false $taxable
     * @return int|string
     */
    private function getTotalForPreviewPDF(array $items, $taxable = false)
    {
        $total = 0;
        if(!empty($items)){
            foreach ($items as $key => $val){
                if($taxable && isset($val['non_taxable']) && $val['non_taxable'] == 1)
                    continue;
                if(!empty($val['is_bundle'])){
                    if(!empty($val['products']))
                        $total += $this->getTotalForPreviewPDF($val['products'], $taxable);
                    if(!empty($val['services']))
                        $total += $this->getTotalForPreviewPDF($val['services'], $taxable);
                    if(!empty($val['items']))
                        $total += $this->getTotalForPreviewPDF($val['items'], $taxable);
                } else
                    $total += is_numeric($val['service_price']) ? $val['service_price'] : 0;
            }
        }
        return $total;
    }

    /**
     * @param array $items
     * @return array
     */
    private function getEquipmentsForPreviewPDF(array $items): array
    {
        $equipments = [];
        foreach ($items as $item){
            $service = new stdClass();
            $service->service = (object)['is_bundle' => 0];
            $service->equipments = [];
            $service->service_disposal_brush = 0;
            $service->service_disposal_wood = 0;
            $service->service_cleanup = 0;
            $service->service_permit = 0;
            $service->service_exemption = 0;
            $service->service_client_home = 0;
            $service->crew = [];
            $service->bundle_records = [];

            if(!empty($item['crews'])){
                foreach ($item['crews'] as $crewKey => $crewValue){
                    $crew = $this->CI->crew_model->get_crewdata(['crew_id' => $crewValue])->result_array();
                    if(!empty($crew)) {
                        $service->crew[$crewKey] = (object)[
                            'crew_full_name' => $crew[0]['crew_full_name']
                        ];
                    }
                }
            }
            if(!empty($item['equipments'])){
                $item = json_encode($item);
                $item = json_decode($item);
                $service->equipments = $this->getDraftEstimateServiceEquipmentForApp($item->equipments, 'previewPDF');

            }
            elseif(!empty($item['is_bundle']) && !empty($item['bundle_records'])){
                foreach ($item['bundle_records'] as $recordKey => $recordVal) {
                    if(!empty($recordVal->crews)){
                        foreach ($recordVal->crews as $crewKey => $crewValue){
                            $crew = $this->CI->crew_model->get_crewdata(['crew_id' => $crewValue])->result_array();
                            if(!empty($crew)) {
                                $service->crew[$crewKey] = (object)[
                                    'crew_full_name' => $crew[0]['crew_full_name']
                                ];
                            }
                        }
                    }
                    if (!empty($recordVal->equipments)) {
                        $recordVal = json_encode($recordVal);
                        $recordVal = json_decode($recordVal);
                        $service->equipments = $this->getDraftEstimateServiceEquipmentForApp($recordVal->equipments, 'previewPDF');
                    }
                }
            }
            $equipments[] = $service;
        }

        return $equipments;
    }

    /**
     * @param array $files
     * @return array
     */
    public function getFilesForPreviewPDF(array $files): array
    {
        $result = [];
        foreach ($files as $key => $val){
            $files = [];
            if(!empty($val)){
                foreach ($val as $fileKey => $fileVal){
                    $files[] = str_replace(base_url(), '', $fileVal['filepath']);
                }
            }
            $result[$key] = $files;
        }
        return $result;
    }

    /**
     * @throws Exception
     */
    private function draftDataRead($lead_id, $file)
    {
        if (Cache::getStore() instanceof \Illuminate\Cache\FileStore) {
            $this->isWritableTmp();
        }

        $data = [];

        if (Cache::getStore()) {
            if (!Cache::has($this->draftPrefix . (int) $lead_id)) {
                $this->draftDataWrite($lead_id, $file, bucket_read_file($file));
            }

            $data = Cache::get($this->draftPrefix . (int) $lead_id);
        } else {
            $data = bucket_read_file($file);
        }

        return $data;
    }

    /**
     * @throws Exception
     */
    private function draftDataWrite($lead_id, $file, $draftData)
    {
        if (Cache::getStore() instanceof \Illuminate\Cache\FileStore) {
            $this->isWritableTmp();
        }

        if (Cache::getStore()) {
            try {
                Cache::put($this->draftPrefix . (int) $lead_id, $draftData);
            } catch (Exception $e) {
                throw new \RuntimeException($e, 500);
            }
        } else {
            try {
                bucket_write_file($file, $draftData);
            } catch (Exception $e) {
                throw new \RuntimeException($e, 500);
            }
        }
    }

    private function isWritableTmp()
    {
        $path  = \Illuminate\Support\Facades\Config::get('cache.stores.file.path');

        if (!is_dir($path)) {
            $dirs = '';
            foreach (explode('/', $path) as $dir) {
                $dirs .= $dir.'/';
                if (!is_dir($dirs)) {
                    try {
                        return mkdir($dirs);
                    } catch (Exception $e) {
                        throw new \http\Exception\RuntimeException("Error create tmp directory with: " . $e->getMessage(), 500);
                    }
                }
            }
        }

    }

    public function cronRedisToBucket(): void
    {
        $drafts = $this->CI->mdl_leads->getLeadsDraft($this->draftPrefix) ?? [];

        if (count($drafts)) {
            foreach (Cache::many($drafts) as $key => $draft) {
                $current_time   = Carbon::now()->timestamp;

                if (null !== $draft && ($current_time - last_updated($key)) > $this->draftLifeTime) {
                    $draftArray = json_decode($draft, true);

                    $file = $draftArray['fileFullPath'];
                    unset($draftArray['fileFullPath']);

                    bucket_write_file($file, json_encode($draftArray));

                    Cache::forget($key);

                    print_r('Draft ' . $key . ' moved to bucket' . PHP_EOL);
                } else {
                    print_r('No draft or draft life time not expired in ' . $key . PHP_EOL);
                }
            }
        }
    }

    /**
     * @param $file
     */
    private function checkLeadS3Queue($file): void
    {
        $i=0;
        do {
            $result = file_exists($file);
            if($result)
                usleep(300000);
            else {
                $fp = fopen($file, "w");
                fclose($fp);
            }
            $i++;
            if($i == 15)
                $result = false;
        } while($result);
    }

    public function getEquipmentFromService(object $service): array{
        $vehicles = [""];
        $vehicle_option = [];
        $trailers = [""];
        $trailer_option = [];
        $tools_option = [];
        $default_crews = isset($service->service_default_crews) ? json_decode($service->service_default_crews ) : [];

        // add default attachments
        if(!empty($service->service_attachments)){
            $attachments = json_decode($service->service_attachments);
            foreach ($attachments as $key => $attachment){
                $vehicles[] = $attachment->vehicle_id;
                $vehicle_option[$key] = $attachment->vehicle_option;
                $trailers[] = $attachment->trailer_id;
                $trailer_option[$key] = $attachment->trailer_option;
                if(!empty($attachment->tool_id)){
                    foreach ($attachment->tool_id as $tool_key => $tool_id){
                        $tools_option[$tool_id][$key] = $attachment->tools_option[$tool_key];
                    }
                }
            }
        }

        return [
            'vehicles' => $vehicles,
            'vehicle_option' => $vehicle_option,
            'trailers' => $trailers,
            'trailer_option' => $trailer_option,
            'tools_option' => $tools_option,
            'default_crews' => $default_crews,
        ];
    }

    public function getEstimateFromLeadToDB(object $lead): array
    {
        $estimate = [];
        $tax = [];
        if (!empty($lead)) {
            $userId = $this->CI->session->userdata('user_id');
            if(empty($userId))
                $userId = $this->CI->user->id;
            $statusConfirmed = $this->getConfirmedStatusId();
            $estimate = [
                'estimate_no' => str_pad($lead->lead_id, 5, '0', STR_PAD_LEFT) . '-E',
                'lead_id' => $lead->lead_id,
                'estimate_brand_id' => default_brand(),
                'client_id' => $lead->client_id,
                'date_created' => time(),
                'status' => $statusConfirmed,
                'user_id' => $userId
            ];
        }

        $defaultTax = getDefaultTax();
        if(!empty($defaultTax))
            $tax = [
                'estimate_tax_name' => $defaultTax['name'],
                'estimate_tax_value' => $defaultTax['value'],
                'estimate_tax_rate' => $defaultTax['rate']
            ];
        return array_merge($estimate, $tax);
    }

    public function setEstimateServicesFromTreeInventoryIds(array $treeInventoryIds, $serviceStatusId){
        $estimateId = $this->getEstimateId();
        $estimate = $this->getEstimate();
        $this->CI->load->library('Common/LeadsActions');
        if(!empty($treeInventoryIds) && !empty($estimateId)){
            $default_service = Service::where(['service_id' => config_item('tree_inventory_service_id')])->first();
            if(!empty($default_service)) {
                foreach ($treeInventoryIds as $tiId) {
                    $tree_inventory = TreeInventory::find($tiId);
                    $description = $this->CI->leadsactions->getTreeInventoryDescription($tree_inventory);
                    $cost = !empty($tree_inventory->ti_cost) ? $tree_inventory->ti_cost : 0;
                    $stump_price = !empty($tree_inventory->ti_stump_cost) ? $tree_inventory->ti_stump_cost : 0;
                    $title = $this->CI->leadsactions->getTreeInventoryTitle($tree_inventory);
                    $record = [
                        'service_id' => $default_service->service_id,
                        'estimate_id' => $estimateId,
                        'service_status' => $serviceStatusId,
                        'service_description' => $tree_inventory->ti_remark,
                        'service_price' => $cost + $stump_price,
                        'estimate_service_ti_title' => $title,
                    ];
                    $estimateServiceId = $this->CI->mdl_estimates->insert_estimate_service($record);
                    if(!empty($default_service->service_default_crews)){
                        foreach (json_decode($default_service->service_default_crews) as $key => $val){
                            $crew['crew_user_id'] = $val;
                            $crew['crew_estimate_id'] = $estimateId;
                            $crew['crew_service_id'] = $estimateServiceId;
                            $this->CI->mdl_crews_orm->insert($crew);
                        }
                    }
                    if(!empty($tree_inventory->ti_file) && !empty($estimate)){
                        // copy
                        $source = 'uploads/tree_inventory/' . $tree_inventory->ti_tis_id . '/' . $tree_inventory->ti_file;
                        $target = 'uploads/clients_files/' . $tree_inventory->ti_client_id . '/estimates/' . $estimate->estimate_no . '/' . $estimateServiceId . '/estimate_no_' . $estimate->estimate_no . '_' . $tree_inventory->ti_file;
                        bucket_copy($source, $target, $options = []);
                    }
                    if(!empty($default_service->service_attachments)){
                        $attachments = json_decode($default_service->service_attachments);
                        foreach ($attachments as $key => $val){
                            $equipment['equipment_item_id'] = $val->vehicle_id;
                            $equipment['equipment_item_option'] = !empty($val->vehicle_option) ? json_encode([$val->vehicle_option]) : NULL;
                            $equipment['equipment_attach_id'] = !empty($val->trailer_id) ? $val->trailer_id : NULL;
                            $equipment['equipment_attach_option'] = !empty($val->trailer_option) ? json_encode([$val->trailer_option]) : NULL;
                            $equipment['equipment_attach_tool'] = !empty($val->tool_id) ? json_encode($val->tool_id) : NULL;

                            $toolsOption = [];
                            if(!empty($val->tool_id)){
                                $defaultToolsOption = $val->tools_option;
                                foreach ($val->tool_id as $keyTool => $valTool){
                                    $toolsOption[$valTool] = $defaultToolsOption[$keyTool];
                                }
                            }
                            $equipment['equipment_tools_option'] = !empty($toolsOption) ? json_encode((object)$toolsOption) : NULL;
                            $equipment['equipment_estimate_id'] = $estimateId;
                            $equipment['equipment_service_id'] = $estimateServiceId;
                            $this->CI->mdl_equipment_orm->insert($equipment);
                        }
                    }
                    // add tree inventory estimate service
                    $ties = [
                        'ties_number' =>  $tree_inventory->ti_tree_number,
                        'ties_type' => $tree_inventory->ti_tree_type,
                        'ties_size' => $tree_inventory->ti_size,
                        'ties_priority' => $tree_inventory->ti_tree_priority,
                        'ties_estimate_service_id' => $estimateServiceId,
                    ];
                    $tiesObject = TreeInventoryEstimateService::create($ties);
                    $tree_inventory_work_types = TreeInventoryWorkTypes::where('tiwt_tree_id', $tiId)->pluck('tiwt_work_type_id')->toArray();
                    if(!empty($tree_inventory_work_types) && is_array($tree_inventory_work_types)){
                        foreach ($tree_inventory_work_types as $workType){
                            TreeInventoryEstimateServiceWorkTypes::create([
                                'tieswt_ties_id' => $tiesObject->ties_id,
                                'tieswt_wt_id' => $workType
                            ]);
                        }
                    }
                }
            }
        }
    }

    public function changeEstimateServiceStatus($serviceId, $statusId){
        $estimate = $this->getEstimate();
        if(empty($estimate))
            return false;
        $service = EstimatesService::where('id', $serviceId)->first();

        if(!empty($service)){
            return EstimatesService::where('id', $serviceId)->update(['service_status' => $statusId]);
        }
        return false;
    }

    public function completeAllNonDeclinedServices($estimateId) {
        return EstimatesService::where('estimate_id', $estimateId)
            ->where('service_status', '<>', '1')
            ->update(['service_status' => '2']);
    }

    public function getDiscountAmount(){
        if($this->getEstimateId()) {
            $estimate = (new Estimate)->totalEstimateBalance($this->getEstimateId())->first();
            if(!empty($estimate) && is_object($estimate))
                return $estimate->discount_total;
        }
        return 0;
    }

    public function sortEstimateFiles(array $estimate_services, array $files): array{
        $new_files_tree_inventory = [];
        $new_files = [];
        if (!empty($estimate_services)) {
            foreach ($estimate_services as $estimate_service) {
                foreach ($files as $key_file => $file) {
                    $pos = strpos($file, '/' . $estimate_service['id'] . '/');
                    if ($pos) {
                        if(!empty($estimate_service['estimate_service_ti_title']))
                            $new_files_tree_inventory[] = $file;
                        else
                            $new_files[] = $file;
                        unset($files[$key_file]);
                    }
                }
            }
        }
        if(!empty($new_files) || !empty($new_files_tree_inventory))
            $files = array_merge($files, $new_files_tree_inventory, $new_files);
        return $files;
    }

    public function sortDraftEstimateFiles(array $items, array $files):array{
        if(!empty($files)){
            $newFiles = [];
            $newFilesTreeInventory = [];
            foreach ($items as $keyItem => $valItem) {
                foreach ($files as $key => $val) {
                    if($valItem['id'] == $key){
                        if(!empty($valItem['estimate_service_ti_title']))
                            $newFilesTreeInventory[$key] = $val;
                        else
                            $newFiles[$key] = $val;
                        unset($files[$key]);
                    }
                }
            }
            if(!empty($newFiles) || !empty($newFilesTreeInventory))
                $files = array_replace_recursive($files, $newFilesTreeInventory, $newFiles);
        }
        return $files;
    }

    public function removeAudioVideoFiles(array $files):array{
        foreach ($files as $keyFiles => $file) {
            if(is_array($file) && !empty($file)){
                foreach ($file as $key => $val){
                    $type = getMimeType($val);
                    if(!is_bucket_file($val) || strripos($type, 'audio') !== false || strripos($type, 'video') !== false)
                        unset($file[$key]);
                }
                $files[$keyFiles] = $file;
            }
            elseif(!is_array($file)) {
                $type = getMimeType($file);
                if(!is_bucket_file($file) || strripos($type, 'audio') !== false || strripos($type, 'video') !== false)
                    unset($files[$keyFiles]);
            }
        }
        return $files;
    }

    private function sortDraftItems($draftData){
        if(!empty($draftData['items'])) {
            $keys = array_keys($draftData['items']);
            $service_priority_array = array_column($draftData['items'], 'service_priority');
            if(count($service_priority_array) == count($draftData['items']))
                array_multisort(array_column($draftData['items'], 'service_priority'), SORT_ASC,  $draftData['items'], $keys);
            $draftData['items'] = array_combine($keys, $draftData['items']);
            $draftData['order_items'] = array_keys($draftData['items']);
        }
        return $draftData;
    }

    public function copyEstimate(int $estimate_id, $new_client_id = null, $estimate_status = '1', $workorders_status = 'false', $invoices_status = 'false')
    {
        $this->CI->load->library('Common/LeadsActions');

        $discount = $this->CI->mdl_clients->get_discount(array('discounts.estimate_id' => $estimate_id));
        $leadStatus = LeadStatus::select('lead_status_id')->where('lead_status_draft', 1)->first();
//      return $discount;
        $estimate = Estimate::where('estimate_id', $estimate_id)->with('lead')->first()->toArray();
//        return $estimate;
        $services = EstimatesService::where('estimate_id', $estimate_id)
            ->with('service')
            ->with('tree_inventory')
            ->with('tree_inventory_estimate_service_work_types')
            ->with('bundle')
            ->get()->toArray();

//        return $services;
        $client_id = $estimate['client_id'];
        $new_client_id = $new_client_id ?? $estimate['client_id'];
        $new_lead = $estimate['lead'];
        $new_lead['client_id'] = $new_client_id;
        $leadServices = LeadService::where('lead_id', $new_lead['lead_id'])->get()->pluck('services_id')->toArray();
//      return $client_id;
        $new_lead['lead_status_id'] = $leadStatus->lead_status_id;
        $new_lead['lead_estimate_draft'] = 1;
        $old_lead_id = $new_lead['lead_id'];
        $draftScheme = json_decode($this->getEstimateDraftScheme($client_id, $old_lead_id));
//        return [$client_id, $old_lead_id,$draftScheme];
        $old_lead_no = str_pad($old_lead_id, 5, '0', STR_PAD_LEFT);

        unset($new_lead['lead_id']);
        unset($new_lead['lead_no']);
        unset($new_lead['lead_date_created']);

        $lead = Lead::create($new_lead);
        $lead_id = $lead['lead_id'];
        foreach ($leadServices as $oneService) {
            $tmpService = new LeadService;
            $tmpService->lead_id = $lead_id;
            $tmpService->services_id = $oneService;
            $tmpService->save();
        }

        $lead_no = str_pad($lead_id, 5, '0', STR_PAD_LEFT);
        $file = "uploads/tmp/{$new_client_id}/{$lead_no}_source_html";
        if (isset($draftScheme->link)) {
            $draftScheme->link = "uploads/tmp/{$new_client_id}/source/{$lead_no}_scheme.png";;
            $res = bucket_write_file($file, json_encode($draftScheme));
        } else if (!empty($draftScheme)) {
            $res = bucket_write_file($file, json_encode($draftScheme));
        }

        $source = "uploads/tmp/{$client_id}/source/{$old_lead_no}_scheme.png";
        $target = "uploads/tmp/{$new_client_id}/source/{$lead_no}_scheme.png";
        bucket_copy($source, $target, $options = []);


        $source = "uploads/tmp/{$client_id}/{$old_lead_no}_scheme_elements";
        $target = "uploads/tmp/{$new_client_id}/{$lead_no}_scheme_elements";
        bucket_copy($source, $target, $options = []);

        $source = "uploads/clients_files/{$client_id}/estimates/{$old_lead_no}-E/pdf_estimate_no_{$old_lead_no}-E_tree_inventory_map.png";
        $target = "uploads/clients_files/{$new_client_id}/estimates/{$lead_no}-E/pdf_estimate_no_{$lead_no}-E_tree_inventory_map.png";
        bucket_copy($source, $target, $options = []);

        $source = "uploads/clients_files/{$client_id}/estimates/{$old_lead_no}-E/pdf_estimate_no_{$old_lead_no}-E_scheme.png";
        $target = "uploads/clients_files/{$new_client_id}/estimates/{$lead_no}-E/pdf_estimate_no_{$lead_no}-E_scheme.png";
        bucket_copy($source, $target, $options = []);


        $target = "uploads/tmp/{$new_client_id}/{$lead_no}_scheme.png";
        bucket_copy($source, $target, $options = []);


        $lead_no = $lead_no . "-L";
        $update_data = array("lead_no" => $lead_no);
        $wdata = array("lead_id" => $lead_id);
        $this->CI->mdl_leads->update_leads($update_data, $wdata);

        $fileFullPath = $this->getDraftFullPath($new_client_id, $lead_id);
        $draftData = [];
        $draftData['pre_uploaded_files'] = [];
        $draftData['copyEst'] = $estimate_status;
        $draftData['copyWo'] = $workorders_status;
        $draftData['copyInvoice'] = $invoices_status;
        $draftData['old_estimate_id'] = $estimate_id;
        $draftData['new_client_id'] = $new_client_id;

        $listFiles = [];

        foreach ($services as $k => $service) {
            $itemForDraft = [];
            $itemForDraft['is_bundle'] = $service['service']['is_bundle'];
            $itemForDraft['is_product'] = $service['service']['is_product'];
            $itemForDraft['service_priority'] = $service['service_priority'];
            $itemForDraft['service_type_id'] = $service['service_id'];
            $itemForDraft['non_taxable'] = $service['non_taxable'];
            $itemForDraft['is_collapsed'] = $service['service']['is_collapsed'] ?? 0;
            $itemForDraft['quantity'] = $service['quantity'];
            $itemForDraft['product_cost'] = $service['cost'];
            $itemForDraft['service_price'] = $service['service_price'];
            $itemForDraft['service_markup_rate'] = $service['service_markup_rate'];
            $itemForDraft['service_description'] = $service['service_description'];
            $itemForDraft['service_disposal_time'] = $service['service_disposal_time'];
            $itemForDraft['service_overhead_rate'] = $service['service_overhead_rate'];
            $itemForDraft['service_time'] = $service['service_time'];
            if ($service['estimate_service_class_id'] > 0) {
                $itemForDraft['class'] = $service['estimate_service_class_id'];
            }
            $itemForDraft['service_travel_time'] = $service['service_travel_time'];
            if (isset($service['tree_inventory'])) {
                unset($itemForDraft['product_cost']);
                unset($itemForDraft['quantity']);
                $itemForDraft['service_price'] = $service['tree_inventory']['ties_stump_cost'] + $service['tree_inventory']['ties_cost'];
                $itemForDraft['ties_cost'] = $service['tree_inventory']['ties_cost'];
                $itemForDraft['ties_number'] = $service['tree_inventory']['ties_number'];
                $itemForDraft['ties_priority'] = $service['tree_inventory']['ties_priority'];
                $itemForDraft['ties_size'] = $service['tree_inventory']['ties_size'];
                $itemForDraft['ties_stump'] = $service['tree_inventory']['ties_stump_cost'];
                $itemForDraft['ties_type'] = $service['tree_inventory']['ties_type'];
                $itemForDraft['ties_work_types'] = [];
                foreach ($service['tree_inventory_estimate_service_work_types'] as $oneType) {
                    $itemForDraft['ties_work_types'][] = $oneType['tieswt_wt_id'];
                }
                $itemForDraft['ties_work_types'] = '[' . implode(',', $itemForDraft['ties_work_types']) . ']';
                $itemForDraft['tree_inventory_service'] = true;
                $itemForDraft['tree_inventory_title'] = $service['estimate_service_ti_title'];
            }

            if ($service['service']['is_bundle']) {
                $itemForDraft['is_view_in_pdf'] = $service['is_view_in_pdf'];

                foreach ($service['bundle'] as $oneBundle) {
                    $itemForDraft['items'][$oneBundle['eb_service_id']] = $draftData['items'][$oneBundle['eb_service_id']];
                    $itemForDraft['order_items'][] = $oneBundle['eb_service_id'];
                    unset($draftData['items'][$oneBundle['eb_service_id']]);
                }
            }
            $draftData['items'][$service['id']] = $itemForDraft;


            $dirPath = 'uploads/clients_files/' . $client_id . '/estimates/' . str_pad($old_lead_id, 5, '0', STR_PAD_LEFT) . '-E/' . $service['id'] . '/';
            if (!is_empty_bucket_dir($dirPath)) {
                $files = bucket_get_filenames($dirPath);
                $listFiles[$dirPath] = $files;
                foreach ($files as $k => $file) {
                    $fileDetail = explode('/', $file);
                    $fileName = $fileDetail[count($fileDetail) - 1];
                    $nameFile = md5($file) . '.' . explode('.', $fileName)[1];
                    $path = 'uploads/clients_files/' . $new_client_id . '/leads/tmp/' . str_pad($lead_id, 5, '0', STR_PAD_LEFT) . '-E/';
                    $fileArray = [
                        'uuid' => NULL,
                        'filepath' => $path . $nameFile,
                        'name' => $nameFile,
                        'url' => base_url($path . $nameFile)
                    ];
                    bucket_copy($dirPath . $fileName, $path . $nameFile, $options = []);
                    $draftData['pre_uploaded_files'][$service['id']][] = $fileArray;
                }

            }
        }

//        return $listFiles;
//        exit();


        $draftData['discount'] = $discount['discount_amount'];
        $draftData['discount_percents'] = $discount['discount_percents'];
        $draftData['discount_comment'] = $discount['discount_comment'] ?? '';
        $draftData['estimate_crew_notes'] = $estimate['estimate_crew_notes'];
        $draftData['tax_label'] = $estimate['estimate_tax_name'] . ' (' . round($estimate['estimate_tax_value']) . '%)';
        $draftData['tax_name'] = $estimate['estimate_tax_name'];
        $draftData['tax_rate'] = $estimate['estimate_tax_rate'];
        $draftData['tax_value'] = $estimate['estimate_tax_value'];
        $draftData['last_update_date'] = getNowDateTime();
        $draftData['fileFullPath'] = $fileFullPath;
        $draftData['save_item'] = true;
        $draftData = $this->sortDraftItems($draftData);

        $this->draftDataWrite($lead_id, '', json_encode($draftData));

        if (!empty($lead_id)) {
            return (['open_url' => base_url(strval($lead_id) . '-E'), 'url' => base_url('estimates/new_estimate/' . $lead_id), 'new_lead_id' => str_pad($lead_id, 5, '0', STR_PAD_LEFT) . '-E']);
        }
        return false;

    }

    public function copyEstimateFast(int $estimate_id, $new_client_id = null, $estimate_status = '1', $workorders_status = 'false', $invoices_status = 'false')
    {
        $this->CI->load->library('Common/LeadsActions');

        $discount = $this->CI->mdl_clients->get_discount(array('discounts.estimate_id' => $estimate_id));
        $new_discount = [];
        $new_discount['discount_amount'] = $discount['discount_amount'];
        $new_discount['discount_percents'] = $discount['discount_percents'];
        $new_discount['discount_comment'] = $discount['discount_comment'];
        $new_discount['discount_date'] = time();
        $leadStatus = LeadStatus::select('lead_status_id')->where('lead_status_estimated', 1)->first();
//      return $discount;
        $estimate = Estimate::where('estimate_id', $estimate_id)->with('lead')->first()->toArray();

        $services = EstimatesService::where('estimate_id', $estimate_id)
            ->with('service')
            ->with('tree_inventory')
            ->with('tree_inventory_estimate_service_work_types')
            ->with('bundle')
            ->get()->toArray();

        $client_id = $estimate['client_id'];
        $new_client_id = $new_client_id ?? $estimate['client_id'];
        $new_lead = $estimate['lead'];
        $new_lead['client_id'] = $new_client_id;
        $new_lead['lead_author_id'] = $new_client_id;
        $leadServices = LeadService::where('lead_id', $new_lead['lead_id'])->get()->pluck('services_id')->toArray();
        $new_lead['lead_status_id'] = $leadStatus->lead_status_id;
        $new_lead['lead_estimate_draft'] = 1;
        $old_lead_id = $new_lead['lead_id'];
        $draftScheme = json_decode($this->getEstimateDraftScheme($client_id, $old_lead_id));
        $old_lead_no = str_pad($old_lead_id, 5, '0', STR_PAD_LEFT);

        unset($new_lead['lead_id']);
        unset($new_lead['lead_no']);
        unset($new_lead['lead_date_created']);

        $lead = Lead::create($new_lead);
        $lead_id = $lead['lead_id'];
        $lead_no = str_pad($lead_id, 5, '0', STR_PAD_LEFT);
        Lead::where('lead_id',$lead_id)->update(['lead_no' => $lead_no.'-L']);
        $new_estimate = $estimate;
        unset($new_estimate['estimate_id']);
        unset($new_estimate['lead']);
        unset($new_estimate['date_created_view']);
        $new_estimate['date_created'] = time();
        $new_estimate['estimate_no'] = $lead_no . '-E';
        $new_estimate['lead_id'] = $lead_id;
        $new_estimate['client_id'] = $new_client_id;

        foreach ($leadServices as $oneService) {
            $tmpService = new LeadService;
            $tmpService->lead_id = $lead_id;
            $tmpService->services_id = $oneService;
            $tmpService->save();
        }
        $lead_no = str_pad($lead_id, 5, '0', STR_PAD_LEFT);
        $file = "uploads/tmp/{$new_client_id}/{$lead_no}_source_html";
        if (isset($draftScheme->link)) {
            $draftScheme->link = "uploads/tmp/{$new_client_id}/source/{$lead_no}_scheme.png";;
            $res = bucket_write_file($file, json_encode($draftScheme));
        } else if (!empty($draftScheme)) {
            $res = bucket_write_file($file, json_encode($draftScheme));
        }

        $source = "uploads/tmp/{$client_id}/source/{$old_lead_no}_scheme.png";
        $target = "uploads/tmp/{$new_client_id}/source/{$lead_no}_scheme.png";
        bucket_copy($source, $target, $options = []);

        $source = "uploads/tmp/{$client_id}/{$old_lead_no}_scheme_elements";
        $target = "uploads/tmp/{$new_client_id}/{$lead_no}_scheme_elements";
        bucket_copy($source, $target, $options = []);

        $source = "uploads/clients_files/{$client_id}/estimates/{$old_lead_no}-E/pdf_estimate_no_{$old_lead_no}-E_tree_inventory_map.png";
        $target = "uploads/clients_files/{$new_client_id}/estimates/{$lead_no}-E/pdf_estimate_no_{$lead_no}-E_tree_inventory_map.png";
        bucket_copy($source, $target, $options = []);

        $source = "uploads/clients_files/{$client_id}/estimates/{$old_lead_no}-E/pdf_estimate_no_{$old_lead_no}-E_scheme.png";
        $target = "uploads/clients_files/{$new_client_id}/estimates/{$lead_no}-E/pdf_estimate_no_{$lead_no}-E_scheme.png";
        bucket_copy($source, $target, $options = []);

        $target = "uploads/tmp/{$new_client_id}/{$lead_no}_scheme.png";
        bucket_copy($source, $target, $options = []);

        $new_estimate['estimate_pdf_files'] = str_replace("clients_files\/{$client_id}\/estimates", "clients_files\/{$new_client_id}\/estimates", $new_estimate['estimate_pdf_files']);
        $new_estimate['estimate_scheme'] = str_replace("tmp\/{$client_id}\/source", "tmp\/{$new_client_id}\/source", $new_estimate['estimate_scheme']);
        $new_estimate['estimate_pdf_files'] = str_replace($old_lead_no, $lead_no, $new_estimate['estimate_pdf_files']);
        $new_estimate['estimate_scheme'] = str_replace($old_lead_no, $lead_no, $new_estimate['estimate_scheme']);

        if ($workorders_status || $invoices_status) {
            $finishedStatus = EstimateStatus::select('est_status_id')->where('est_status_confirmed', '1')->first();
            $new_estimate['status'] = $finishedStatus->est_status_id;
        } else {
            $new_estimate['status'] = $estimate_status;
        }

        $new_estimate_id = Estimate::insertGetId($new_estimate);
        $new_discount['estimate_id'] = $new_estimate_id;
        $this->CI->mdl_clients->insert_discount($new_discount);

        $arrayRelations = [];
        foreach ($services as $service) {
            $equipment = EstimatesServicesEquipments::where('equipment_service_id', $service['id'])->get()->toArray();
            $crew = EstimatesServicesCrew::where('crew_service_id', $service['id'])->get()->toArray();

            $old_service_id = $service['id'];
            $tree_inventory = $service['tree_inventory'] ?? null;
            $tree_inventory_estimate_service_work_types = $service['tree_inventory_estimate_service_work_types'] ?? null;

            $bundles = $service['bundle'];
            unset($service['id']);
            unset($service['service']);
            unset($service['tree_inventory']);
            unset($service['tree_inventory_estimate_service_work_types']);
            unset($service['bundle']);
            $service['estimate_id'] = $new_estimate_id;

            $new_service_id = EstimatesService::insertGetId($service);
            $estimateInfo = Estimate::select('estimate_pdf_files')->where('estimate_id', $new_estimate_id)->first()->toArray();
            unset($estimateInfo['date_created_view']);
            $estimateInfo['estimate_pdf_files'] = str_replace("\/{$old_service_id}\/", "\/{$new_service_id}\/", $estimateInfo['estimate_pdf_files']);
            $this->CI->mdl_estimates_orm->update($new_estimate_id, $estimateInfo);
            foreach ($equipment as $oneEquipment) {
                unset($oneEquipment['equipment_id']);
                unset($oneEquipment['equipment_tools_option_array']);
                unset($oneEquipment['equipment_item_option_string']);
                unset($oneEquipment['equipment_attach_option_string']);
                $oneEquipment['equipment_service_id'] = $new_service_id;
                $oneEquipment['equipment_estimate_id'] = $new_estimate_id;
                EstimatesServicesEquipments::insert($oneEquipment);
            }
            foreach ($crew as $oneCrew) {
                unset($oneCrew['crew_id']);
                $oneCrew['crew_service_id'] = $new_service_id;
                $oneCrew['crew_estimate_id'] = $new_estimate_id;
                EstimatesServicesCrew::insert($oneCrew);
            }

            $arrayRelations[$old_service_id] = $new_service_id;
            if (isset($tree_inventory)) {
                $tree_inventory['ties_estimate_service_id'] = $new_service_id;
                unset($tree_inventory['ties_id']);
                $new_ties_id = TreeInventoryEstimateService::insertGetId($tree_inventory);

                if (isset($tree_inventory_estimate_service_work_types)) {
                    foreach ($tree_inventory_estimate_service_work_types as $WOtype) {
                        $WOtype['tieswt_ties_id'] = $new_ties_id;
                        unset($WOtype['tieswt_id']);
                        unset($WOtype['laravel_through_key']);
                        TreeInventoryEstimateServiceWorkTypes::insertGetId($WOtype);
                    }

                }
            }
            $dirPath = 'uploads/clients_files/' . $client_id . '/estimates/' . str_pad($old_lead_id, 5, '0', STR_PAD_LEFT) . '-E/' . $old_service_id . '/';
            if (!is_empty_bucket_dir($dirPath)) {
                $files = bucket_get_filenames($dirPath);
                $listFiles[$dirPath] = $files;
                foreach ($files as $k => $file) {
                    $fileDetail = explode('/', $file);
                    $fileName = $fileDetail[count($fileDetail) - 1];
                    $nameFile = str_replace($old_lead_no, $lead_no, $fileName);
                    $path = 'uploads/clients_files/' . $new_client_id . '/estimates/' . str_pad($lead_id, 5, '0', STR_PAD_LEFT) . '-E/' . $new_service_id . '/';
                    $fileArray = [
                        'uuid' => NULL,
                        'filepath' => $path . $nameFile,
                        'name' => $nameFile,
                        'url' => base_url($path . $nameFile)
                    ];
                    bucket_copy($dirPath . $fileName, $path . $nameFile, $options = []);
                }

            }

            if (isset($bundles) && is_array($bundles)) {
                foreach ($bundles as $kBundle => $oneBundle) {
                    $tmpItem = [];
                    $tmpItem['eb_service_id'] = $arrayRelations[$oneBundle['eb_service_id']];
                    $tmpItem['eb_bundle_id'] = $new_service_id;
                    EstimatesBundle::insert($tmpItem);
                }
            }
        }

        $returnLink='-E';
        if ($workorders_status || $invoices_status) {
            $workorder = Workorder::where('estimate_id', $estimate_id)->first()->toArray();
            $workorder['estimate_id'] = $new_estimate_id;
            $workorder['client_id'] = $new_client_id;
            $workorder['workorder_no'] = $lead_no . '-W';
            $workorder['date_created'] = date('Y-m-d');
            if ($workorders_status) {
                $workorder['wo_status'] = intval($workorders_status);
            } else {
                $finishedStatus = WorkorderStatus::select('wo_status_id')->where('is_finished', '1')->first();
                $workorder['wo_status'] = $finishedStatus->wo_status_id;
            }
            unset($workorder['id']);
            unset($workorder['last_change']);
            unset($workorder['date_created_view']);
            unset($workorder['days_from_creation']);
            unset($workorder['files_array']);
            foreach ($services as $service) {
                $old_service_id=$service['id'];
                $new_service_id=$arrayRelations[$old_service_id];
                $workorder['wo_pdf_files']=str_replace("\/{$old_service_id}\/", "\/{$new_service_id}\/",$workorder['wo_pdf_files']);
                $workorder['wo_pdf_files'] = str_replace("clients_files\/{$client_id}\/estimates", "clients_files\/{$new_client_id}\/estimates", $workorder['wo_pdf_files']);
                $workorder['wo_pdf_files'] = str_replace($old_lead_no, $lead_no, $workorder['wo_pdf_files']);
            }
            $workOrderId = Workorder::insertGetId($workorder);
            $returnLink='-W';
        }

        if ($invoices_status) {
            $workorder = Workorder::where('id', $workOrderId)->first();
            if (isset($workorder) && !empty($workorder)) {
                $invoices = Invoice::where(['estimate_id' => $estimate_id])->get()->toArray();
                if (!empty($invoices)) {
                    foreach ($invoices as $invoice) {
                        unset($invoice['id']);
                        unset($invoice['last_change']);
                        unset($invoice['date_created_view']);
                        unset($invoice['days_from_creation']);
                        unset($invoice['overdue_date_view']);
                        $invoice['estimate_id'] = $new_estimate_id;
                        $invoice['client_id'] = $new_client_id;
                        $invoice['workorder_id'] = $workOrderId;
                        $invoice['date_created'] = date('Y-m-d');
                        $invoice['in_status'] = intval($invoices_status);
                        $invoice['invoice_no'] = $lead_no . '-I';
                        foreach ($services as $service) {
                            $old_service_id = $service['id'];
                            $new_service_id = $arrayRelations[$old_service_id];
                            $invoice['invoice_pdf_files'] = str_replace("\/{$old_service_id}\/", "\/{$new_service_id}\/", $invoice['invoice_pdf_files']);
                            $invoice['invoice_pdf_files'] = str_replace("clients_files\/{$client_id}\/estimates", "clients_files\/{$new_client_id}\/estimates", $invoice['invoice_pdf_files']);
                            $invoice['invoice_pdf_files'] = str_replace($old_lead_no, $lead_no, $invoice['invoice_pdf_files']);
                        }

                        Invoice::insert($invoice);
                    }
                }

            }
            $returnLink='-I';
        }

        if (!empty($lead_id)) {
            return (['open_url' => base_url(str_pad($lead_id, 5, '0', STR_PAD_LEFT) . $returnLink), 'new_lead_id' => str_pad($lead_id, 5, '0', STR_PAD_LEFT) . $returnLink]);
        }
        return false;

    }
}
