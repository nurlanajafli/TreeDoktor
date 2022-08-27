<?php

use application\modules\estimates\models\EstimatesService;
use application\modules\estimates\models\Service;
use application\modules\estimates\models\TreeInventoryEstimateServiceWorkTypes;
use application\modules\invoices\models\Invoice;
use application\modules\invoices\models\InvoiceStatus;
use application\modules\tree_inventory\models\WorkType;
use application\modules\workorders\models\Workorder;
use application\modules\workorders\models\WorkorderStatus;

class WorkorderActions
{
    protected $CI;
    protected $workorderId;
    protected $workorder;
    private $isConfirmedWeb;

    function __construct(array $params = []) {
        $this->CI =& get_instance();
        $this->_title = SITE_NAME;
        $this->workorderId = $params['workorder_id']??NULL;
        $this->CI->load->model('mdl_invoices');
        $this->CI->load->model('mdl_workorders');
        $this->CI->load->model('mdl_estimates_orm');
        $this->CI->load->helper('tree_helper');
    }

    function setWorkorderId($workorderId) {
        $this->workorder = $this->CI->mdl_workorders->find_by_id($workorderId);
        if(!$this->workorder)
            return FALSE;
        $this->workorderId = $workorderId;
        return TRUE;
    }

    function getWorkorderId(){
        if(!$this->workorderId)
            return FALSE;
        return $this->workorderId;
    }

    public function getWorkOrder(){
        if(!$this->workorder)
            return FALSE;
        return $this->workorder;
    }

    function create($estimateId, $confirmationType = NULL, $schedulingPreference = NULL, $extraNotCrew = NULL)
    {
        $this->CI->load->library('Common/EstimateActions', $estimateId);
        $estimate = $this->CI->estimateactions->getEstimate();
        $workorder = $this->CI->mdl_workorders->find_by_fields(['estimate_id' => $estimateId]);
        $woStatus = $this->getDefaultStatusId();

        if($this->getIsConfirmedWeb()){
            $confirmedByClientStatusId = $this->getConfirmedByClientStatusId();
            if($confirmedByClientStatusId)
                $woStatus = $confirmedByClientStatusId;
        }

        if($workorder) {
            $this->setWorkorderId($workorder->id);
            return true;
        }

        if(!$estimate)
            return FALSE;
        if($estimate->status != $this->CI->estimateactions->getConfirmedStatusId())
            return FALSE;

        $data['wo_priority'] = 'Regular';
        $data['wo_confirm_how'] = $confirmationType;
        $data['wo_scheduling_preference'] = $schedulingPreference;
        $data['wo_extra_not_crew'] = $extraNotCrew;
        $data['client_id'] = $estimate->client_id;
        $data['estimate_id'] = $estimate->estimate_id;
        $data['workorder_no'] = str_replace('E', 'W', $estimate->estimate_no);
        $data['wo_pdf_files'] = $estimate->estimate_pdf_files;
        $data['wo_status'] = $woStatus;
        $data['date_created'] = date('Y-m-d');

        $workorderId = $this->CI->mdl_workorders->insert_workorders($data);
        $this->setWorkorderId($workorderId);

        return TRUE;
    }

    function updateInitialData($confirmationType = NULL, $schedulingPreference = NULL, $officeNoteCrew = NULL, $date = NULL) {
        $data = [];

        if($confirmationType !== NULL)
            $data['wo_confirm_how'] = $confirmationType;
        if($confirmationType === 'Signature')
            $data['wo_status'] = $this->CI->mdl_workorders->getConfirmByClientId();

        if($schedulingPreference !== NULL)
            $data['wo_scheduling_preference'] = $schedulingPreference;

        if($officeNoteCrew !== NULL)
            $data['wo_office_notes'] = $officeNoteCrew;

        if($date !== NULL)
            $data['date_created'] = $date;
        if(!empty($data)) {
            $this->CI->mdl_workorders->update_workorder($data, ['id' => $this->workorderId]);
            $this->setWorkorderId($this->workorderId);
        }
        return TRUE;
    }

    function getDefaultStatusId() {
        return $this->CI->mdl_workorders->getDefaultStatusId();
    }

    function getFinishedStatusId() {
        $status = WorkorderStatus::where('is_finished', 1)->first();
        if(!empty($status))
            return $status->wo_status_id;
        return false;
    }

    function update() {

    }

    /**
     * @param Workorder $workorder
     * @param $workorder_status_id
     * @param $force
     * @return array
     */
    function setStatus(Workorder $workorder, $workorder_status_id, $force = false) {

        $this->CI->load->model('mdl_schedule', 'mdl_schedule');
        $this->CI->load->model('mdl_services_orm', 'mdl_services_orm');
        $this->CI->load->model('mdl_estimates');
        $this->CI->load->model('mdl_invoices');
        $this->CI->load->model('mdl_invoice_status');


        $workorder_id = $workorder->getAttribute(Workorder::ATTR_ID);
        $pre_workorder_status = $workorder->getAttribute(Workorder::ATTR_WO_STATUS);;
        $new_workorder_status = $workorder_status_id;
        $scheduleDate = FALSE;

        $default_invoice_status = element('invoice_status_id', (array)$this->CI->mdl_invoice_status->get_by(['invoice_status_active'=>1, 'default'=>1]), 0);

        if($this->CI->input->post('date')) {
            $scheduleDate = strtotime($this->CI->input->post('date'));
        }

        $estimate_data = $this->CI->mdl_estimates_orm->with('mdl_services_orm')->get_full_estimate_data([
            'estimate_id' => $workorder->getAttribute(Workorder::ATTR_ESTIMATE_ID)
        ])[0];

        if ($pre_workorder_status == $new_workorder_status && !$force) {
            return ['status'=>'success', 'httpCode' => 200];
        }
        $this->CI->load->helper('workorders');
        $allow_status = allow_workorder_status($new_workorder_status, $estimate_data->mdl_services_orm);

        if(!$allow_status['status'] && $allow_status['message'] && !$force){
            return ['status'=>'error', 'message'=>$allow_status['message'], 'httpCode' => 400];
        }


        if ($pre_workorder_status != $new_workorder_status) {
            $status = ['status_type' => 'workorder', 'status_item_id' => $workorder_id, 'status_value' => $new_workorder_status, 'status_date' => time()];

            $this->CI->mdl_estimates->status_log($status);
        }

        //Check if the new status == Finished
        //Code to inser invoice data into db
        if ($new_workorder_status == 0) {

            $data = [];
            $workorder_no = $workorder->getAttribute(Workorder::ATTR_WORKORDER_NO);
            $invoice_no = str_replace('W', 'I', $workorder_no);
            $data['client_id'] = $workorder->getAttribute(Workorder::ATTR_CLIENT_ID);
            $data['estimate_id'] = $workorder->getAttribute(Workorder::ATTR_ESTIMATE_ID);
            $data['workorder_id'] = $workorder->getAttribute(Workorder::ATTR_ID);
            $data['invoice_no'] = $invoice_no;
            $data['in_status'] = $default_invoice_status;
            $data['date_created'] = date('Y-m-d');
            $data['overdue_date'] = date('Y-m-d', strtotime('+' . \application\modules\invoices\models\Invoice::getInvoiceTerm($workorder->estimate->client->client_type ?? null) . ' days'));

            $invoice = $this->CI->mdl_invoices->find_by_fields(['estimate_id' => $workorder->getAttribute(Workorder::ATTR_ESTIMATE_ID)]);
            if(empty($invoice)) {
                $invoice_id = $this->CI->mdl_invoices->insert_invoice($data);
            } else {
                $invoice_id = $invoice->id;
            }

            //create a new job for synchronization in QB
            if ($invoice_id) {
                pushJob('quickbooks/invoice/syncinvoiceinqb', serialize(['id' => $invoice_id, 'qbId' => '']));
            }
        }
        //Form data
        //Delete previous workorders if any:
        if ($new_workorder_status != 0) {
            $id = $workorder->getAttribute(Workorder::ATTR_ESTIMATE_ID);
            //create a new job for synchronization in QB
            $invoice = $this->CI->mdl_invoices->find_by_field(['invoices.estimate_id' => $id]);
            if ($invoice)
                pushJob('quickbooks/invoice/syncinvoiceinqb', serialize(['id' => $invoice->id, 'qbId' => $invoice->invoice_qb_id]));

            $delete_invoice = $this->CI->mdl_invoices->delete_invoice($id);
        }

        $new_wo_status_row = WorkorderStatus::where([WorkorderStatus::ATTR_ID => $new_workorder_status])->first();
        $pre_wo_status_row = WorkorderStatus::where([WorkorderStatus::ATTR_ID => $pre_workorder_status])->first();
        $updated = $workorder->update(['wo_status' => $new_workorder_status]);

        if ($updated) {

            $update_msg = "Status for " . $workorder->getAttribute(Workorder::ATTR_WORKORDER_NO) . ' was modified from "' . $pre_wo_status_row->getAttribute(WorkorderStatus::ATTR_NAME) . '" to "' . $new_wo_status_row->getAttribute(WorkorderStatus::ATTR_NAME) . '"';

            if($scheduleDate) {
                $this->CI->mdl_schedule->insert_update(['update_time' => $scheduleDate]);
            } else {
                $events = $this->CI->mdl_schedule->get_events(array('schedule.event_wo_id' => $workorder->getAttribute(Workorder::ATTR_ID)));
                if ($events && !empty($events)) {
                    foreach ($events as $event) {
                        $this->CI->mdl_schedule->insert_update(array('update_time' => $event['event_start']));
                    }
                }
            }


            if (make_notes($workorder->getAttribute(Workorder::ATTR_CLIENT_ID), $update_msg, 'system', $estimate_data->lead_id)) {
                return ['status'=>'success', 'workorder_data'=> $workorder->toArray(), 'invoice_id' => $invoice_id ?? false, 'httpCode' => 200];
            }
        }

        return ['status'=>'success', 'workorder_data' => $workorder->toArray(), 'invoice_id' => $invoice_id ?? false, 'httpCode' => 200];
    }

    function delete($estimateId) {

        if($estimateId) {
            $this->CI->load->library('Common/InvoiceActions');
            $deleteWorkorder = $this->CI->mdl_workorders->delete_workorder($estimateId);
            $deleteInvoices =  $this->CI->invoiceactions->delete($estimateId);
            return $deleteWorkorder;
        } else {
            return false;
        }
    }

    function getWorkordersByFilterEstimates(array $workorders, array $estimates) : array
    {
        foreach ($workorders as $key => $workorder){
            if(in_array($workorder['estimate_id'], $estimates) === false)
                unset($workorders[$key]);
        }
        return $workorders;
    }

    public function setIsConfirmedWeb(bool $isConfirmedWeb){
        $this->isConfirmedWeb = $isConfirmedWeb;
    }

    public function getIsConfirmedWeb(){
        return $this->isConfirmedWeb;
    }

    function getConfirmedByClientStatusId(){
        return $this->CI->mdl_workorders->getConfirmedByClientStatusId();
    }

    function getPendingStatusId(){
        $status = $this->CI->mdl_workorders->getPendingStatus();
        return $status['wo_status_id'] ?? false;
    }

    /**
     * @param string $method
     * @return string
     */
    public function showPDF($method = 'I') {
        include_once('./application/libraries/Mpdf.php');
        $this->CI->mpdf = new mPDF();
        $this->CI->mpdf->WriteHTML($this->getPDFTemplate($this->workorderId));
        $file = sys_get_temp_dir() . '/' . $this->getPDFFileName();

        if(is_file($file)) {
            @unlink($file);
        }

        if(file_exists($file)) {
            $file = sys_get_temp_dir() . '/' . uniqid() . '-' . $this->getPDFFileName();
        }

        $this->CI->mpdf->Output($file, $method);

        return $file;
    }

    /**
     * @return string
     */
    public function getPDFFileName() {
        return 'Workorder_' . $this->workorderId . '.pdf';
    }

    /**
     * @param $workorder_id
     * @param null $event_id
     * @param null $lead_id
     * @return bool|object|string
     */
    public function getPDFTemplate($workorder_id, $event_id = null, $lead_id = null)
    {
        $this->CI->load->model('mdl_workorders', 'mdl_workorders');
        $this->CI->load->model('mdl_schedule', 'mdl_schedule');
        $this->CI->load->model('mdl_estimates_orm', 'mdl_estimates_orm');
        $this->CI->load->model('mdl_estimates');
        $this->CI->load->model('mdl_vehicles');
        $this->CI->load->model('mdl_employees');
        $this->CI->load->model('mdl_user');
        $this->CI->load->model('mdl_clients', 'mdl_clients');

        $data['title'] = SITE_NAME . ' - Workorder';

        $data['estFiles'] = $pdfs = [];
        //Get workorder informations - using common function from MY_Models;
        if (!$lead_id) {
            $data['workorder_data'] = $this->CI->mdl_workorders->wo_find_by_id($workorder_id);
        } else {
            $data['workorder_data'] = $this->CI->mdl_workorders->wo_find_by_lead_id($lead_id);
        }

        if(!$data['workorder_data']) {
            return false;
        }
        $workorder_id = $data['workorder_data']->id;

        $event_services_id = NULL;
        $data['team_id'] = NULL;
        $data['event_id'] = $event_id;
        if ($event_id) {
            $schedule_event = $this->CI->mdl_schedule->get_events(array('schedule.id' => $event_id))[0] ?? false;

            if($schedule_event) {
                $data['event_services'] = $this->CI->mdl_schedule->get_event_services(['event_id' => $event_id]);
                $data['schedule_event'] = $schedule_event;
                $data['team_id'] = $schedule_event['team_id'];

                foreach ($data['event_services'] as $val) {
                    $event_services_id .= 'estimates_services.id = ' . $val['id'] . ' OR ';
                    $data['service_ids'][] = $val['event_service_id'];
                }
                $event_services_id = '(' . rtrim($event_services_id, ' OR ') . ')';
            }
        }

        //Get estimate informations - using common function from MY_Models;
        $estimate_id = $data['workorder_data']->estimate_id;
        $estimate_data = $this->CI->mdl_estimates_orm->with('mdl_services_orm')->get_full_estimate_data(array('estimate_id' => $estimate_id));
        $data['estimate_data'] = $this->CI->mdl_estimates_orm->_explodePdfFiles($estimate_data)[0];
        $data["tools"] = $this->CI->mdl_vehicles->get_many_by(array('vehicle_trailer' => 2));


        $estClPath = 'uploads/clients_files/' . $data['estimate_data']->client_id . '/estimates/' . $data['estimate_data']->estimate_no . '/tmp/';
        $pdfFiles = $data['workorder_data']->wo_pdf_files ? json_decode($data['workorder_data']->wo_pdf_files) : [];
        $pictures['files'] = $pdfFiles;

        if (!$pictures['files'])
            $pictures['files'] = array();
        $schemePath = '';
        foreach ($pictures['files'] as $key => $file) {
            if(strpos($file, 'scheme')) {
                $schemePath = $file;
                continue;
            }
            if (pathinfo($file)['extension'] != 'pdf') {
                $serviceName = '';
                $array = explode('/', $file);
                if(!empty($array) && is_array($array) && !empty($array[5])) {
                    $serviceId = trim($array[5], " \\");
                    if (!empty($serviceId)) {
                        $estimateService = EstimatesService::find($serviceId);
                        if(!empty($estimateService)){
                            if(!empty($estimateService->estimate_service_ti_title))
                                $serviceName = $estimateService->estimate_service_ti_title;
                            else{
                                $service = Service::find($estimateService->service_id);
                                if(!empty($service) && !empty($service->service_name))
                                    $serviceName = $service->service_name;
                            }
                        }
                    }
                }
                if(!empty($serviceName))
                    $data['estFiles'][$serviceName][] = $file;
                else
                    $data['estFiles'][] = [$file];
            } else
                $pdfs[] = $file;
        }
        if(!empty($schemePath))
            $data['estFiles'] = ['Project Scheme' => [$schemePath]] + $data['estFiles'];

        // add tree inventory map
        $treeInventoryMapPath = inventory_screen_path($estimate_data[0]->client_id, $estimate_data[0]->lead_id . '_tree_inventory_map.png');
        if(is_bucket_file($treeInventoryMapPath))
            $data['estFiles'] = ['Tree Inventory Map' => [$treeInventoryMapPath]] + $data['estFiles'];
        $treeInventoryMapPath = inventory_screen_path($estimate_data[0]->client_id, $estimate_data[0]->lead_id . '.png');
        if(is_bucket_file($treeInventoryMapPath))
            $data['estFiles'] = ['Tree Inventory Map' => [$treeInventoryMapPath]] + $data['estFiles'];

        //estimate services

        if ($event_services_id)
            $data['estimate_services_data'] = $this->CI->mdl_estimates->find_estimate_services($estimate_id, $event_services_id);
        else
            $data['estimate_services_data'] = $this->CI->mdl_estimates->find_estimate_services($estimate_id);

        $estimateTreeInventoryServicesData = [];
        $treeInventoryWorkTypes = [];
        $treeInventoryPriorities = [];
        foreach ($estimate_data[0]->mdl_services_orm  as $key => $value){
            // add tree inventory work types
            if(isset($value->tree_inventory) && !empty($value->tree_inventory)){
                $estimateTreeInventoryServicesData[$key] = $value->tree_inventory;
                $treeInventoryPriorities[] = $value->tree_inventory->ties_priority;
                unset($estimate_data[0]->mdl_services_orm[$key]);
                $workTypes = TreeInventoryEstimateServiceWorkTypes::where('tieswt_ties_id', $value->tree_inventory->ties_id)->with('work_type')->get()->pluck('work_type')->pluck('ip_name_short')->toArray();
                $treeInventoryWorkTypes = array_merge($treeInventoryWorkTypes, $workTypes);
                if(!empty($workTypes) && is_array($workTypes)){
                    $estimateTreeInventoryServicesData[$key]['work_types'] = implode(', ', $workTypes);
                }
                $estimateTreeInventoryServicesData[$key]['ties_priority'] = ucfirst(substr($value->tree_inventory->ties_priority, 0,1));
                if(!empty($value->tree_inventory->tree)){
                    $tree = $value->tree_inventory->tree;
                    $estimateTreeInventoryServicesData[$key]['ties_type'] = $tree->trees_name_eng . " (" . $tree->trees_name_lat . ")";
                }
                $estimateTreeInventoryServicesData[$key]['service_price'] = $value->service_price;
                $estimateTreeInventoryServicesData[$key]['service_description'] = $value->service_description;
            }
        }

        if(!empty($estimateTreeInventoryServicesData)){
            if(!empty($treeInventoryWorkTypes))
                $data['work_types'] = WorkType::whereIn('ip_name_short', $treeInventoryWorkTypes)->get()->toArray();
            if(!empty($treeInventoryPriorities))
                $data['tree_inventory_priorities'] = array_unique($treeInventoryPriorities);
            $data['is_wo'] = true;
        }
        $data['estimate_tree_inventory_services_data'] = $estimateTreeInventoryServicesData;
        $data['estimate_data']->mdl_services_orm = $estimate_data[0]->mdl_services_orm;

        //Get user_i
        //d and retrive estimator information:

        $user_id = $data['estimate_data']->user_id;
        $user = $this->CI->mdl_user->get_usermeta(array('users.id' => $user_id));
        $data['user_data'] = $data['emp_data'] = $user ? $user->result()[0] : [];

        //Get client_id and retrive client's information:
        $id = $data['estimate_data']->client_id;
        $data['client_data'] = $this->CI->mdl_clients->find_by_id($id);
        $data['client_contact'] = $this->CI->mdl_clients->get_primary_client_contact($id);
        $data['events'] = $this->CI->mdl_schedule->get_events(array('schedule.event_wo_id' => $workorder_id));

        foreach ($data['events'] as $event) {
            $data['members'][$event['id']] = $this->CI->mdl_schedule->get_team_members(['employee_team_id' => $event['team_id']]);
            $data['items'][$event['id']] = $this->CI->mdl_schedule->get_team_items(array('equipment_team_id' => $event['team_id']));
        }

        list($data['hospital_address'], $data['hospital_name'], $data['hospital_coords']) = getNearestHospitalInfo($data['estimate_data']->lat, $data['estimate_data']->lon, implode(',', [$data['estimate_data']->lead_address, $data['estimate_data']->lead_city, $data['estimate_data']->lead_state]));

        $this->estimateId = $estimate_id;
        list($result, $view) = Modules::find('pdf_templates/' . config_item('company_dir') . '/workorder_pdf', 'includes', 'views/');

        if($result) {
            return $this->CI->load->view('includes/pdf_templates/' . config_item('company_dir') . '/' . 'workorder_pdf', $data, TRUE);
        } else {
            return $this->CI->load->view('includes/pdf_templates/workorder_pdf', $data, TRUE);
        }
    }

    function send_pdf_to_email()
    {
        $workorder_id = $this->CI->input->post('workorder_id')??NULL;
        $result = $this->setWorkorderId($workorder_id);
        if (!$result) {
            return $this->CI->response(['type' => 'error', 'message' => 'Workorder is not valid'], 400);
        }

        $cc = $bcc = '';
        $text ='';
        $to = $this->CI->input->post('emails');
        $subject = $text = 'Workorder ' . $this->workorder->workorder_no;
        $from_email = $this->CI->config->item('account_email_address');

        if(is_array($to)){
            foreach ($to as $value){
                pushJob('workorders/sendworkorders', [
                    'cc' => $cc,
                    'bcc' => $bcc,
                    'to' => $value,
                    'from' => $from_email,
                    'body' => $text,
                    'subject' => $subject,
                    'workorder' => $this->workorder,
                ]);
            }
        }else {
            pushJob('workorders/sendworkorders', [
                'cc' => $cc,
                'bcc' => $bcc,
                'to' => $to,
                'from' => $from_email,
                'body' => $text,
                'subject' => $subject,
                'workorder' => $this->workorder,
            ]);
        }

        return $this->CI->response(['status' => true, 'message' => 'Success'], 200);
    }
}
