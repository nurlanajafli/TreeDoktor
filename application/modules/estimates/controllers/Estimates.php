<?php
if (!defined('BASEPATH'))
	exit('No direct script access allowed');

use application\modules\clients\models\Client;
use application\modules\invoices\models\InvoiceStatus;
use application\modules\leads\models\LeadStatus;
use application\modules\qb\models\QbLogs as QbLogsModel;
use application\modules\categories\models\Category;
use application\modules\estimates\models\Service;
use application\modules\estimates\models\EstimateStatus;
use application\modules\classes\models\QBClass;
use application\modules\brands\models\Brand;
use application\modules\estimates\models\Estimate;
use application\modules\leads\models\Lead;
use application\modules\clients\models\ClientLetter;
use application\modules\references\models\Reference;
use Illuminate\Support\Facades\Redis;
use application\modules\estimates\models\TreeInventoryEstimateServiceWorkTypes;
use application\modules\estimates\models\TreeInventoryEstimateService;
use application\modules\tree_inventory\models\TreeInventoryWorkTypes;
use application\modules\tree_inventory\models\WorkType;
use application\modules\estimates\models\EstimatesService;
use application\modules\invoices\models\Invoice;
use application\modules\estimates\models\EstimatesBundle;
use application\modules\estimates\models\EstimatesServicesStatus;
use application\modules\workorders\models\WorkorderStatus;
class Estimates extends MX_Controller
{

//*******************************************************************************************************************
//*************
//*************																					Estimates Controller;
//*************																		Cretated by: Konstantin Mereshkin
//*************
//*******************************************************************************************************************

    public $draftDriver;
    public $draftPrefix;

	function __construct()
	{
		parent::__construct();

		//Checking if user is logged in;
        if (!isUserLoggedIn() && isEstimateAccessible()) {
            redirect('login');
        }

		//Global settings:
		$this->_title = SITE_NAME;

		//Load all common models and libraries here:
		$this->load->model('mdl_est_status');
		$this->load->model('mdl_estimates', 'mdl_estimates');

		$this->load->model('mdl_estimates_orm', 'mdl_estimates_orm');
		$this->load->model('mdl_services_orm', 'mdl_services_orm');
		$this->load->model('mdl_crews_orm', 'mdl_crews_orm');
		$this->load->model('mdl_equipment_orm', 'mdl_equipment_orm');
		$this->load->model('mdl_expenses_orm', 'mdl_expenses_orm');

		$this->load->model('mdl_services', 'mdl_services');
		$this->load->model('mdl_crews', 'mdl_crews');
		$this->load->model('mdl_leads', 'mdl_leads');
		$this->load->model('mdl_clients', 'mdl_clients');
		$this->load->model('mdl_workorders', 'mdl_workorders');
		$this->load->model('mdl_invoices', 'mdl_invoices');
		$this->load->model('mdl_user', 'mdl_user');

		$this->load->model('mdl_users_orm', 'mdl_users_orm');

		$this->load->model('mdl_reports', 'mdl_reports');
		$this->load->model('mdl_crew', 'crew_model');
		$this->load->model('mdl_employees', 'employees_model');
		$this->load->model('mdl_calls');
		$this->load->model('mdl_sms_messages');
        $this->load->model('mdl_vehicles');
        $this->load->model('mdl_estimates_bundles');
        $this->load->model('mdl_schedule');
        $this->load->model('mdl_tree_inventory_orm', 'tree_inventory');

        //Load helpers:
        $this->load->helper('estimates');
        $this->load->helper('business_days_cal');
        $this->load->helper('settings');
        $this->load->helper('cache');
        $this->load->helper('tree_helper');
		$this->load->library('pagination');
		$this->load->library('form_validation');
		$this->load->library('googlemaps');
        $this->load->library('Common/EstimateActions');
        $this->load->library('Common/LeadsActions');
        $this->load->library('Common/InvoiceActions');

        $this->draftDriver = $this->config->item('estimateDraftDriver');
        $this->draftPrefix = 'estimate:';
	}

//*******************************************************************************************************************
//*************
//*************																				Estimates Index Function;
//*************
//*******************************************************************************************************************	

	public function index()
	{
		//Set title:
		$data['title'] = $this->_title . ' - Estimates';
		//Set menu active status:
		$data['menu_estimates'] = "active";
		//Get all estimates:

		$search_keyword = "";

		if (isset($_POST['search_keyword']) && $_POST['search_keyword'] != '')
			$search_keyword = $_POST['search_keyword'];

		//Pagination for Pending Estimates

		$config = array();
		$config["base_url"] = base_url() . "estimates/paginationEstimates/";
		$config["total_rows"] = $this->mdl_estimates->estimate_record_count($search_keyword, 'Pending approval');
		$config["per_page"] = 50;
		$config['full_tag_open'] = '<ul class="pagination pagination-sm m-t-none m-b-none pull-right">';
		$config['full_tag_close'] = '</ul>';
		$config['use_page_numbers'] = TRUE;

		$this->pagination->initialize($config);

		$data['statuses'] = $this->mdl_est_status->get_many_by(array('est_status_active' => 1));
		$data['types'] = array_column($data['statuses'], 'est_status_id');
		$page = 1;
		$start = $page - 1;
		$start = $start * $config["per_page"];
		$limit = $config["per_page"];
		$data['symbols'] = array(' - ', ' ','-');
		foreach($data['statuses'] as $key=>$status)
		{

			$data['estimates'][mb_strtolower(str_replace($data['symbols'], '_', $status->est_status_name)) . '_estimate'] = $this->mdl_estimates->get_estimates($search_keyword, $status->est_status_id, $limit, $start);
			$config["total_rows"] = $this->mdl_estimates->estimate_record_count($search_keyword, $status->est_status_id);
			$this->pagination->initialize($config);
			$data['estimates'][mb_strtolower(str_replace($data['symbols'], '_', $status->est_status_name)) . '_estimate_links'] = $this->pagination->create_links();
			$data['estimates'][mb_strtolower(str_replace($data['symbols'], '_', $status->est_status_name)) . '_count'] = $config["total_rows"];
		}

		$confirmed = $this->mdl_est_status->get_by(['est_status_confirmed' => 1]);
		//echo "<pre>";var_dump($data['estimates']);die;
		$qaWdata = array();
		$qaWdata['user_id'] = request()->user()->id;
		$qaWdata['wo_status'] = $this->mdl_workorders->getFinishedStatusId();

		$qaWdata['invoices.date_created >'] = date('Y-m-d', (time() - (86400 * 10)));
		$data["qa_estimate"] = $this->mdl_estimates->get_estimates($search_keyword, intval($confirmed->est_status_id), $limit, $start, "estimates_qa.qa_id, invoices.date_created", $order_type = "DESC", $qaWdata);

        $data["qa_count"] = (!empty($data["qa_estimate"]->num_rows()) && $data["qa_estimate"]->num_rows()) ? $data["qa_estimate"]->num_rows() : 0;
		$data['search_keyword'] = $search_keyword;

		$data['estimateCount'] = $this->mdl_reports->getTotalEstimates();

		/**WORKING ESTIMATES**/

		$days_ago = [7, 14, 30, 60, 90, 120]; //how much days ago
		$follow_up_statuses = $this->mdl_est_status->get_many_by(['est_status_confirmed'=>0, 'est_status_declined'=>0, 'est_status_default' => 0, 'est_status_sent' => 0]);

		$data['working_count'] = 0;
		$data["working_estimates"] = [];
		foreach ($days_ago as $ago) {
			$wdata['estimates.date_created >'] = strtotime(date('Y-m-d')) - ($ago * 86400);
			$wdata['estimates.date_created <'] = strtotime(date('Y-m-d')) - ($ago * 86400) + 86399;
			$data['working_estimates'][$ago] = array();
			foreach($follow_up_statuses as $key=>$val)
			{
				$result = $this->mdl_estimates->get_estimates($search_keyword, intval($val->est_status_id), 100, 0, "estimates.estimate_id", "DESC", $wdata);
                if ($result && $result->num_rows())
					$data['working_estimates'][$ago] = array_merge($result->result(),$data['working_estimates'][$ago]);

			}
            $data['working_count'] += count($data["working_estimates"][$ago]); //countOk
		}
		/**WORKING ESTIMATES**/
		$data['workers'] = $data['estimators'] = [];
		$estimators = $this->mdl_estimates->get_active_estimators();
		if($estimators)
			$data['estimators'] = $estimators;

		$data['services'] = $this->mdl_services->find_all(array('service_status' => 1), 'service_priority');
		$data['statuses'] = $this->mdl_est_status->with('mdl_est_reason')->get_many_by(array('est_status_active' => 1));
		$users = $this->mdl_user->get_usermeta(array('emp_status' => 'current', 'emp_feild_worker' => 1, 'active_status' => 'yes'));
		if($users)
			$data['workers'] = $users->result_array();
		//Load view:
		$this->load->view("index", $data);
	}

	public function paginationEstimates($page = 1)
	{
        $statuses = $this->mdl_est_status->get_many_by(array('est_status_active' => 1));
		$types = array_column($statuses, 'est_status_id');
		if (!$this->input->post('type'))
			show_404();
		if (array_search($this->input->post('type'), $types) === FALSE)
			show_404();
		$type = $this->input->post('type');
		$search_keyword = "";
		$total_rows = $this->mdl_estimates->estimate_record_count($search_keyword, $type);
		$sorts = $this->input->post('sorts');
		$config = array();
		$config["base_url"] = base_url() . "estimates/paginationEstimates/";
		$config["total_rows"] = $total_rows;
		$config["per_page"] = 50;
		$config['use_page_numbers'] = TRUE;
		$config['full_tag_open'] = '<ul class="pagination pagination-sm m-t-none m-b-none pull-right">';
		$config['full_tag_close'] = '</ul>';

		$this->pagination->initialize($config);

		$start = $page - 1;
		$start = $start * $config["per_page"];
		$limit = $config["per_page"];

		$field = isset($sorts[$type]) ? $sorts[$type]['field'] : 'estimates.estimate_id';
		$sort = isset($sorts[$type]) ? $sorts[$type]['type'] : 'DESC';

		$status = $this->mdl_est_status->get($type);
		$data['current_status'] = $status;

        $data['symbols'] = array(' - ', ' ','-');
		$data['estimates'][mb_strtolower(str_replace($data['symbols'], '_', $status->est_status_name)) . '_estimate'] = $this->mdl_estimates->get_estimates($search_keyword, $status->est_status_id, $limit, $start, $field, $sort);
		$config["total_rows"] = $this->mdl_estimates->estimate_record_count($search_keyword, $status->est_status_id);
		$this->pagination->initialize($config);
		$data['estimates'][mb_strtolower(str_replace($data['symbols'], '_', $status->est_status_name)) . '_estimate_links'] = $this->pagination->create_links();;
		$data['estimates'][mb_strtolower(str_replace($data['symbols'], '_', $status->est_status_name)) . '_count'] = $config["total_rows"];
		$result['html'] = $this->load->view('index_tab_estimate', $data, TRUE);

		$result['status'] = 'ok';
		$this->response($result);
	}

//*******************************************************************************************************************
//*************
//*************																						Profile Function;
//*************
//*******************************************************************************************************************	

	public function profile($estimate_id=null, $lead_id=null)
	{

		//NB:Set to redirect to index if variable is null or not set;
		if (!isset($estimate_id) && !$lead_id)
			redirect('estimates/', 'refresh');

		$this->load->model('mdl_sms');
		$this->load->model('mdl_letter');
		$this->load->model('mdl_qa');
		$this->load->model('mdl_est_equipment');
		$data['active_users'] = $this->mdl_user->get_usermeta(['active_status' => 'yes', 'emp_field_estimator'=>'1', 'system_user' => 0])->result();
		//Set title:
		$data['title'] = $this->_title . ' - Estimate';
		$data['menu_estimates'] = "active";
		$data['brands'] = Brand::withTrashed()->get();
		//Get estimate informations - using common function from MY_Models;
		$id = $estimate_id;

        $data['clients']=Client::all();

		if($estimate_id)
			$estimate_data = $this->mdl_estimates_orm->with('mdl_services_orm')->get_full_estimate_data(['estimate_id' => $id]);
		if($lead_id)
			$estimate_data = $this->mdl_estimates_orm->with('mdl_services_orm')->get_full_estimate_data(['estimates.lead_id' => $lead_id]);

		$data['estimate_data'] = isset($estimate_data[0]) ? $estimate_data[0] : FALSE;
		if (!$data['estimate_data'])
			page_404(['message'=>'This estimate does not exist']);

		$id = $estimate_id = $data['estimate_data']->estimate_id;
		$data['lead_data'] = $this->mdl_leads->get_leads(array('lead_id' => $data['estimate_data']->lead_id), '')->row();

		if(!$data['lead_data'])
			page_404(['message'=>'This estimate does not exist']);

		$this->load->model('mdl_letter');

		$client_id = $data['estimate_data']->client_id;
		$client_contact = $this->mdl_clients->get_primary_client_contact($client_id);

		$data['client_contact'] = $client_contact;

        $clientsData = $this->mdl_clients->get_client_by_id($client_id); //Get client details
        // add qb logs
        $clientsData->qb_logs = QbLogsModel::where(['log_module_id' => QbLogsModel::MODULE_CLIENT, 'log_entity_id' => $clientsData->client_id])->orderBy('log_created_at', 'desc')->get();

        $data['client_data'] = $clientsData;

        $data['client_estimates'] = $this->mdl_estimates->get_client_estimates($client_id); //Get client estimates

		if($data['client_estimates'])
		{
			foreach($data['client_estimates']->result_array() as $key => $estimate)
			{
				$data['estimates_crews'][] = $this->mdl_estimates->find_estimate_crews($estimate_id);
				$data['estimates_discounts'][$estimate['estimate_id']] = $this->mdl_clients->get_discount(array('discounts.estimate_id' => $estimate_id));

			}
		}
        $client_tags = $this->mdl_clients->get_client_tags(array('client_id' => $data['client_data']->client_id)); //Get client contacts
        $data['client_tags'] = array_map(function ($item){
            return ['id'=>$item['tag_id'], 'text' => $item['name']];
        }, $client_tags);

		$data['client_papers'] = $this->mdl_clients->get_papers(['cp_client_id' => $client_id], 'cp_id DESC');
		$data['statuses'] = $this->mdl_est_status->with('mdl_est_reason')->get_many_by(array('est_status_active' => 1));

		//Payment data
		$data['payments_data'] = $this->mdl_clients->get_payments(array('client_payments.estimate_id' => $estimate_id));
		//Discount data
		$data['discount_data'] = $this->mdl_clients->get_discount(array('discounts.estimate_id' => $estimate_id));

		//Get user_id and retrive user's information for estimator's name;

		$invoice = $this->mdl_invoices->find_by_fields(['estimate_id' => $estimate_id]);
		if($invoice && !empty($invoice))
		{
			$data['estimate_data']->interest_status = $invoice->interest_status;
			$data['invoice_interest_data'] = $this->mdl_invoices->getInterestData($invoice->id);
		}

		$client_id = $data['estimate_data']->client_id;
		$street = $data['lead_data']->lead_address;

		$city = $data['lead_data']->lead_city;
		$address = $street . "+" . $city;
		//Set the map:
		$config['center'] = config_item('map_center');
		$config['zoom'] = '10';

		$this->googlemaps->initialize($config);

		$marker = [];
		$marker['position'] = $address;
		$marker['icon'] = mappin_svg('#FD7567', '&#9899;', FALSE, '#000');
		$this->googlemaps->add_marker($marker);
		$data['address'] = $address;
		$data['map'] = $this->googlemaps->create_map();

		$id = $data['estimate_data']->user_id;
		$data['user_data'] = $this->mdl_user->find_by_id($id);

		//Get client_id and retrive client's information:
		$id = $data['estimate_data']->client_id;
		if (!$data['estimate_data']->client_id)
			page_404(['message'=>'The client for this estimate does not exist']);

		$data['client_info'] = $this->mdl_clients->get_client_by_id($id);

//		$jkey = md5($this->config->item('encryption_key') . $data['client_data']->client_id);
//		$data['client_data']->client_cc_number = $data['client_data']->client_cc_number;//decrypt_data($jkey, $data['client_data']->client_cc_number);

		$wdata = array('estimate_id' => $estimate_id);
		$workorder = $this->mdl_workorders->find_all($wdata);
		$data['wo_statuses'] = $this->mdl_services_orm->get_service_status();

		if(isset($workorder[0]))
			$data['workorder_data'] = $workorder[0];

		//Set TRUE to include estimate options;
		$data['estimate_contacts'] = $this->mdl_estimates->find_contacts($estimate_id);

		$data['estimate_options'] = TRUE;
		$finishedWoStatusId = (int)$this->mdl_workorders->getFinishedStatusId();

		if (isset($workorder[0]->wo_status) && (int)$workorder[0]->wo_status == $finishedWoStatusId)
		{
			$data['invoice_data'] = $this->mdl_invoices->getEstimatedData($estimate_id);
			//qa
			$data['estimate_qa'] = $this->mdl_estimates->find_estimate_qa($estimate_id);
			$data['qa'] = $this->mdl_qa->find_all_with_limit([], '', '', 'qa_type_int', ['qa_status' => 1]);
            if(isset($data['invoice_data']->id)) {
                $data['invoice_interest_data'] = $this->mdl_invoices->getInterestData($data['invoice_data']->id);
            } else {
                $this->load->library('Common/WorkorderActions');
                $this->workorderactions->setStatus(\application\modules\workorders\models\Workorder::find($workorder[0]->id), $finishedWoStatusId, true);
                $data['invoice_data'] = $this->mdl_invoices->getEstimatedData($estimate_id);
                $data['invoice_interest_data'] = $this->mdl_invoices->getInterestData($data['invoice_data']->id);
            }
		}

		//setup
		$this->load->model('mdl_vehicles');
		$data["equipment_items"] =  $this->mdl_vehicles->get_many_by(array('vehicle_trailer IS NULL', 'vehicle_disabled' => NULL));
		$data["tools"] = $this->mdl_vehicles->get_many_by(array('vehicle_trailer' => 2));
		//end setup
		$data['crews_active'] = $this->crew_model->get_crewdata(array('crew_status' => 1, 'crew_id <>' => 0))->result_array();
		$data['crew_row'] = $this->crew_model->get_crewdata();

		$html = ClientLetter::find(10);
		$data['estimate_pdf_letter_template_id'] = $html->email_template_id;
		$data['client_contacts'] = $this->mdl_clients->get_client_contacts(array('cc_client_id' => $client_id)); //Get client contacts

		$data['sms'] = array();
		$data['messages'] = array();
		$sms = $this->mdl_sms->get(2);
		if($sms && (is_object($sms) || !empty($sms))) {
            $data['sms'] = $sms;
            $data['messages'] = [json_decode(json_encode($sms), FALSE)];
        }

		$data['estimate_id'] = $data['estimate_data']->estimate_id;

        $taxes = all_taxes();

        $data['taxText'] = $data['estimate_data']->estimate_tax_name . ' (' . round($data['estimate_data']->estimate_tax_value,3) . '%)';
        $data['taxRate'] = $data['estimate_data']->estimate_tax_rate;

        $checkTax = checkTaxInAllTaxes($data['taxText']);
        if(!$checkTax)
            $taxes[] = ['text' => $data['taxText'], 'id' => $data['taxText']];

        // Tax recommendation for US companies if the address has changed
        if(config_item('office_country') == 'United States of America') {
            if(!empty($data['lead_data']->lead_tax_name) && !empty($data['lead_data']->lead_tax_value) && $data['lead_data']->lead_tax_value != $data['estimate_data']->estimate_tax_value){
                $data['taxRecommendation'] = round($data['lead_data']->lead_tax_value, 3);
                $data['taxEstimate'] = round($data['estimate_data']->estimate_tax_value, 3);
                $taxText = $data['lead_data']->lead_tax_name . ' (' . round($data['lead_data']->lead_tax_value, 3) . '%)';
                $checkTax = checkTaxInAllTaxes($taxText);
                if(!$checkTax)
                    $taxes[] = ['text' => $taxText,'id' => $taxText];
            }
        }
        $data['allTaxes'] = $taxes;

        $data['search_by_clients']=Reference::select('id')->where('is_client_active',1)->get()->toArray();
        $data['estimateStatuses']=EstimateStatus::select('est_status_id','est_status_name','est_status_default')
            ->where('est_status_active','1')->where('est_status_confirmed','0')->orderBy('est_status_priority', 'asc')->get()->toArray();
        $data['workorderStatuses']=WorkorderStatus::select('wo_status_id','wo_status_name','is_default')
            ->where('wo_status_active','1')->where('is_finished','0')->orderBy('wo_status_priority', 'asc')->get()->toArray();
        $data['invoiceStatuses']=InvoiceStatus::select('invoice_status_id','invoice_status_name','default')
            ->where('invoice_status_active','1')->where('completed','0')->orderBy('priority', 'asc')->get()->toArray();
        //Load view
//        dd($data['workorder_data']);
		$this->load->view('profile', $data);

	}//End Profile();

	function no($no = NULL) {
		if(!$no) {
			show_404();
		}

		$this->profile(NULL, $no);
	}

	function pdf($no = NULL) {

		if(!$no) {
			show_404();
		}

		$this->estimate_pdf(NULL, $no);
	}

//*******************************************************************************************************************
//*************
//*************										Estimate PDF();
//*************
//*************											*** Generating pdf of the estimate using estimate_id; ***
//*************
//*******************************************************************************************************************

	function estimate_pdf($estimate_id=NULL, $lead_id=NULL)
	{
		if (!isset($estimate_id) && !$lead_id) { // NB: Set to redirect to index if variable is null or not set;
			redirect('estimates/', 'refresh');
		} else {

			$data = $this->estimate_pdf_generate($estimate_id, $lead_id);
			if(!$data) {
                redirect('estimates/', 'refresh');
			    return false;
            }
            //var_dump($data);
            //die;
			$this->load->library('mpdf');
			$this->mpdf->WriteHTML($data['html']);
			foreach ($data['files'] as $file) {
			    if(pathinfo($file, PATHINFO_EXTENSION) == 'pdf') {
                    $this->mpdf->AddPage('L');
                    $this->mpdf->Thumbnail(bucket_get_stream($file), 1, 10, 16, 1);
                }
            }

            if(isset($data['estimate']->tree_inventory_pdf) && !empty($data['estimate']->tree_inventory_pdf)) {
                $this->leadsactions->setLead($lead_id);
                $treeInventoryHtml = $this->leadsactions->tree_inventory_pdf(true);

                if ($treeInventoryHtml) {
                    $this->mpdf->WriteHTML($treeInventoryHtml);
                }
            }

			$this->mpdf->Output($data['file'], 'I');	

			if($estimate_id)
			    $estimate = $this->mdl_estimates->get_estimate($estimate_id)->row();
			else
                $estimate = $this->mdl_estimates->find_by_fields(['lead_id' => $lead_id]);
		}
		// end else;
	}
	function test_pdf()
	{
        $data['link'] = 'http://localhost/estimates/estimate_pdf/41422/48077';
        $this->successResponse($data);
        return;
		$result = $this->estimate_pdf_generate(22259);
		$this->load->library('mpdf');
		$this->mpdf->WriteHTML('<strong>TEST</strong>');
		$this->mpdf->Output($result['file'], 'I');

	}
	//End Profile();
	function estimate_pdf_generate($estimate_id, $lead_id=NULL)
	{
		//Set title:
		$this->load->model('mdl_est_equipment');
		$this->load->model('mdl_estimates_bundles');
		$data['title'] = $this->_title . ' - Estimates PDF';

		//Get estimate informations - using common function from MY_Models;
		$id = $estimate_id;

        if($estimate_id) {
            $where['estimates.estimate_id'] = $estimate_id;
        }

        if($lead_id) {
            $where['estimates.lead_id'] = $lead_id;
        }

        if (is_cl_permission_none()) {
            $where['estimates.user_id'] = -1;
        }

		$data['estimate'] = $estimate_data = $this->mdl_estimates_orm->with('mdl_services_orm')->get_full_estimate_data($where);

		if(!$estimate_data || empty($estimate_data)) {
		    return false;
        }


        $files = json_decode($estimate_data[0]->estimate_pdf_files, true);

        if(!empty($files)) {
            $estimateServices = EstimatesService::where('estimate_id', $data['estimate'][0]->estimate_id)->orderBy('service_priority')->get()->toArray();
            $files = $this->estimateactions->sortEstimateFiles($estimateServices, $files);
            $estimate_data[0]->estimate_pdf_files = json_encode($files);
        }

//        // add tree inventory map
//        $treeInventoryMapPath = inventory_screen_path($estimate_data[0]->client_id, $data['estimate'][0]->lead_id . '_tree_inventory_map.png');
//        array_unshift($files, $treeInventoryMapPath);
//        $treeInventoryMapPath = inventory_screen_path($estimate_data[0]->client_id, $data['estimate'][0]->lead_id . '.png');
//        array_unshift($files, $treeInventoryMapPath);
//		$estimate_data[0]->estimate_pdf_files = json_encode($files);

		$data['estimate_data'] = $this->mdl_estimates_orm->_explodePdfFiles($estimate_data)[0];

		$id = $estimate_id = $data['estimate_data']->estimate_id;
		//estimate services
        $estimateServicesData = $this->mdl_estimates->find_estimate_services($id, ['estimates_services.service_status <>' => 1]);
        $estimateTreeInventoryServicesData = [];
        $treeInventoryWorkTypes = [];
        $treeInventoryPriorities = [];
        foreach ($estimateServicesData as $key => $value){
            if($value['is_bundle']){
                $bundleRecords = $this->mdl_estimates_bundles->get_many_by(['eb_bundle_id' => $value['id']]);
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
            if(!empty($treeInventoryWorkTypes))
                $data['work_types'] = WorkType::whereIn('ip_name_short', $treeInventoryWorkTypes)->get()->toArray();
            if(!empty($treeInventoryPriorities))
                $data['tree_inventory_priorities'] = array_unique($treeInventoryPriorities);
        }
        $data['estimate_services_data'] = $estimateServicesData;
        $data['estimate_tree_inventory_services_data'] = $estimateTreeInventoryServicesData;
		//Discount data
		$data['discount_data'] = $this->mdl_clients->get_discount(array('discounts.estimate_id' => $id));

		//Get client_id and retrive client's information:
		$id = $data['estimate_data']->client_id;
		$data['client_data'] = $this->mdl_clients->find_by_id($id);
		$data['client_contact'] = $this->mdl_clients->get_primary_client_contact($id);

		//Get user_id and retrive user's information for estimator's name;
		$id = $data['estimate_data']->user_id;
		$data['user_data'] = $this->mdl_user->find_by_id($id);

		//$data['equipment_items'] = $this->mdl_est_equipment->order_by('eq_weight')->get_many_by(array('eq_status' => 1));
		//setup
		$this->load->model('mdl_vehicles');
		$data["equipment_items"] =  $this->mdl_vehicles->get_many_by(array('vehicle_trailer IS NULL', 'vehicle_disabled' => NULL));
		$data["tools"] = $this->mdl_vehicles->get_many_by(array('vehicle_trailer' => 2));
		//echo '<pre>'; var_dump($data['equipment_items']); die;
		//end setup
		$data['crews_active'] = $this->crew_model->get_crewdata(array('crew_status' => 1, 'crew_id <>' => 0))->result_array();

		$file =  'Estimate ' . $data['estimate_data']->estimate_no . " - " . str_replace('/', '_', $data['estimate_data']->lead_address) . '.pdf';
        list($result, $view) = Modules::find('pdf_templates/' . config_item('company_dir') . '/estimate_pdf', 'includes', 'views/');
        if($result) {
            $html = $this->load->view('includes/pdf_templates/' . config_item('company_dir') . '/' . 'estimate_pdf', $data, TRUE);
        } else {
            $html = $this->load->view('includes/pdf_templates/estimate_pdf', $data, TRUE);
        }

        $brand_id = get_brand_id($estimate_data, $data['client_data']);
        $html = ClientLetter::parseCustomTemplates($estimate_id, $html, $brand_id);

		return array('file' => $file, 'html' => $html, 'files' => $data['estimate_data']->estimate_pdf_files ? json_decode($data['estimate_data']->estimate_pdf_files) : [], 'estimate'=>$data['estimate'][0]);
	}
//*******************************************************************************************************************
//*************
//*************										New Estimates Function;
//*************
//*************											*** Allows estimator to create a new estimate based
//*************												on the original lead information. ***
//*************
//*******************************************************************************************************************

	public function new_estimate($lead_id=NULL)
	{
		if (!(int)$lead_id) // NB: Set to redirect to index if variable is null or not set;
			return redirect('estimates/', 'refresh');

		//Set title:
		$data['title'] = $this->_title . ' - Estimates';
		$this->load->model('mdl_est_equipment');
		$this->load->model('mdl_info');
		$this->load->model('mdl_vehicles');
		$this->load->model('mdl_settings_orm');
		//Get lead informations
		$data['brands'] = Brand::all();
		$data['lead'] = $this->mdl_leads->get_leads(array('lead_id' => $lead_id), '')->row();
		if(!$data['lead'])
			show_404();

		$this->load->model('mdl_tree_inventory_orm', 'tree_inventory');
		$data['tree_inventory'] = $this->tree_inventory->with('work_types')->with('tree_type')->order_by('ti_tree_number')->get_many_by(['ti_client_id'=>$data['lead']->client_id, 'ti_lead_id'=>$data['lead']->lead_id]);

		$data['trees'] = $this->mdl_info->find_all();
		$data['services'] = $this->mdl_services->order_by('service_priority')->with('mdl_services')->get_many_by(array('service_parent_id' => NULL, 'service_status' => 1, 'is_product'=>0, 'is_bundle'=>0));

		$data['products'] = $this->mdl_services->order_by('service_priority')->get_many_by(array('service_parent_id' => NULL, 'service_status' => 1, 'is_product'=>1));
        $bundles = $this->mdl_services->find_all(array('service_status' => 1, 'is_bundle' => 1), 'service_priority');

        foreach ($bundles as $bundle){
            $result = $this->mdl_services->get_records_included_in_bundle($bundle->service_id);
            if($result){
                foreach ($result as $record)
                    $record->non_taxable = 0;
            }
            $bundle->bundle_records = json_encode($result, true);
        }
        $data['bundles'] = $bundles;

        $categoryWithProducts = Category::whereNull('category_parent_id')->with(['categoriesWithProducts', 'products'])->get()->toArray();
        $categoryWithServices = Category::whereNull('category_parent_id')->with(['categoriesWithServices', 'services'])->get()->toArray();
        $categories = Category::whereNull('category_parent_id')->with('categories')->get()->toArray();
        $data['categoriesWithChildren'] = getCategories($categories);
        $data['categoriesWithProducts'] =  $this->estimateactions->getCategoryWithItemsForSelect2($categoryWithProducts);
        $data['categoriesWithServices'] =  $this->estimateactions->getCategoryWithItemsForSelect2($categoryWithServices);
        $classes = QBClass::where(['class_parent_id' => null, 'class_active' => 1])->with('classesWithoutInactive')->get();
        $data['classes'] = [];
        if(!empty($classes->toArray())) {
            $data['classes'] = getClasses($classes->toArray());
        }
        //data for favourite icons
        $servicesWithIcons = DB::table('services')->where([['service_is_favourite', '!=', 0],['service_favourite_icon', '!=', null], ['service_status', 1]])->orderBy('is_bundle')->orderBy('is_product')->get()->toArray();
        $data['favouriteIcons'] = setFavouriteShortcut($servicesWithIcons);

		//$data['equipment'] = $this->mdl_est_equipment->order_by('eq_weight')->get_many_by(array('eq_status' => 1));
		$data['crews'] = $this->mdl_crews->get_crews(array('crew_status' => 1, 'crew_id >' => 0), 'crew_status DESC, crew_priority ASC');
		//echo '<pre>'; var_dump($this->db->last_query()); die;
		if(isset($data['lead']->lead_json_backup) && $data['lead']->lead_json_backup)
			parse_str($data['lead']->lead_json_backup, $data['services_estimate_data']);

		$data["tools"] = $this->mdl_vehicles->get_many_by(array('vehicle_trailer' => 2, 'vehicle_disabled' => NULL));
		$data["vehicles"] = $this->mdl_vehicles->get_many_by(array('vehicle_trailer IS NULL', 'vehicle_disabled' => NULL));
		$data["trailers"] = $this->mdl_vehicles->get_many_by(array('vehicle_trailer' => 1, 'vehicle_disabled' => NULL));


		//Get client id and information
		$client_id = $data['lead']->client_id;
//		$data['client_data'] = $this->mdl_clients->find_by_id($client_id);
        $clientsData = $this->mdl_clients->get_client_by_id($client_id); //Get client details
        // add qb logs
        $clientsData->qb_logs = QbLogsModel::where(['log_module_id' => QbLogsModel::MODULE_CLIENT, 'log_entity_id' => $clientsData->client_id])->orderBy('log_created_at', 'desc')->get();

        $data['client_data'] = $clientsData;
		$data['service_tpl'] = json_encode(array('tpl' => $this->load->view('service_tpl', $data, TRUE)));
		$data['product_tpl'] = json_encode(array('tpl' => $this->load->view('products/product_tpl', $data, TRUE)));
		$data['bundle_tpl'] = json_encode(array('tpl' => $this->load->view('bundles/bundle_tpl', $data, TRUE)));
		//echo '<pre>'; var_dump($data['services']); die;
		$data['icons'] = bucketScanDir('uploads/scheme_items/');
		sort($data['icons']);

        $client_tags = $this->mdl_clients->get_client_tags(array('client_id' => $data['client_data']->client_id)); //Get client contacts
        $data['client_tags'] = array_map(function ($item){
            return ['id'=>$item['tag_id'], 'text' => $item['name']];
        }, $client_tags);


		$draftData = $draftInfo = $draftScheme = NULL;
		// old draft
//		$schemeFilename = $lead_id . '_source_html';
//		$filename = $lead_id . '_estimate_draft';
//		$dir = 'uploads/tmp/';
//		$subDirs = [$client_id];
//
//		foreach ($subDirs as $key => $value) {
//			$dir .= $value . '/';
//		}
//
//		$fileFullPath = $dir . $filename;
//
//        $draftData = bucket_read_file($fileFullPath);
//        $draftInfo = bucket_get_file_info($fileFullPath);
//
//		if(is_file($dir . $schemeFilename)) {
//			$draftScheme = bucket_read_file($dir . $schemeFilename);
//		}
        // end old draft
        if($data['lead']->lead_estimate_draft || $data['lead']->lead_status_default) {
            $draftData = $this->estimateactions->getEstimateDraftData($client_id, $lead_id);
            $draftInfo = $this->estimateactions->getEstimateDraftInfo($client_id, $lead_id);
            $draftScheme = $this->estimateactions->getEstimateDraftScheme($client_id, $lead_id);
        } else {
            Lead::where('lead_id', $data['lead']->lead_id)->update(['lead_estimate_draft' => 1]);
        }
		$client_id = $data['client_data']->client_id;
		$name = $data['client_data']->client_name;
		$street = $data['lead']->lead_address;

		$city = $data['lead']->lead_city;
		$country = $data['lead']->lead_city;
		$address = $street . "+" . $city;
			//Set the map:
		$config['center'] = $address;
		$config['zoom'] = '11';

		$this->googlemaps->initialize($config);

		$marker = array();
		$marker['position'] = $address;
		$marker['icon'] = mappin_svg('#FD7567', '&#9899;', FALSE, '#000');
		$this->googlemaps->add_marker($marker);
		$data['address'] = $address;
		$data['map'] = $this->googlemaps->create_map();

		$this->load->library('user_agent');
		$data['is_mobile'] = $this->agent->is_mobile();

		$this->load->model('mdl_services');
		$this->load->model('mdl_leads_services');
		$data['est_services'] = $this->mdl_leads_services->get_with_services(['lead_id' => $lead_id]);

		$data['client_contacts'] = $this->mdl_clients->get_client_contacts(array('cc_client_id' => $client_id)); //Get client contacts
		$data['draft'] = $draftData;

		$draft = json_decode($draftData);
        if(!isset($draft->new_format_draft_tree) && isset($draft->items) && is_array($draft->items)) { //Check update format for trees_id 20.06.2022
            foreach ($draft->items as $key=>$draftItem) {
                $newList = [];
                foreach(json_decode($draftItem->ties_work_types) as $keyInner =>$treeItem) {
                    $getItem = TreeInventoryWorkTypes::where('tiwt_id',$treeItem)->pluck('tiwt_work_type_id')->first();
                    if(isset($getItem)){
                        $newList[] = $getItem;
                    }
                }
                $draft->items->$key->ties_work_types = json_encode($newList);
            }
            $data['draft'] = json_encode($draft);
        }

//		$data['draft_order'] = json_encode(isset($draft->items) && !empty($draft->items) ? array_keys((array)$draft->items) : []);
		$data['draft_scheme'] = $draftScheme;
		$data['draft_info'] = $draftInfo;



        $allTaxes = all_taxes();

        $taxText = config_item('taxManagement');
        $taxName = getDefaultTax()['name'] ?? 'Tax';

        if (isset($data['estimate_data']->estimate_tax_name)) {
            $taxText = $data['estimate_data']->estimate_tax_name . ' (' . round($data['estimate_data']->estimate_tax_value, 3) . '%)';
            $taxName = $data['estimate_data']->estimate_tax_name;
        }
        elseif (!empty($clientsData->client_tax_name)) {
            $taxText = $clientsData->client_tax_name . ' (' . round($clientsData->client_tax_value, 3) . '%)';
            $taxName = $clientsData->client_tax_name;
            $data['taxRate'] = $clientsData->client_tax_rate;
            $data['taxValue'] = $clientsData->client_tax_value;

            $checkTax = checkTaxInAllTaxes($taxText);
            if (!$checkTax) {
                $allTaxes[] = [
                    'id' => $taxText,
                    'text' => $taxText,
                    'name' => $clientsData->client_tax_name,
                    'rate' => $clientsData->client_tax_rate,
                    'value' => round($clientsData->client_tax_value, 3)
                ];
            }
        }

        $data['taxText'] = $taxText;
        $data['taxName'] = $taxName;

        // auto tax for US
       if(config_item('office_country') == 'United States of America') {
           if(empty($data['lead']->lead_tax_name) || empty($data['lead']->lead_tax_rate) || empty($data['lead']->lead_tax_value)) {
               if ($data['lead']->lead_country === 'United States') {
                   $addressForAutoTax = [
                       'Address' => $data['lead']->lead_address,
                       'City' => $data['lead']->lead_city,
                       'State' => $data['lead']->lead_state,
                       'Zip' => $data['lead']->lead_zip
                   ];
                   $autoTax = $this->estimateactions->getTaxForUSCompany($addressForAutoTax);
                   if (!empty($autoTax)) {
                       $allTaxes[] = $autoTax['estimate'];
                       $this->mdl_leads->update_leads($autoTax['db'], ['lead_id' => $lead_id]);
                       $data['taxRecommendation'] = $autoTax['estimate']['value'];
                   }
               }
           } else {
               $text = $data['lead']->lead_tax_name . ' (' . round($data['lead']->lead_tax_value, 3) . '%)';

               if ($taxText !== $text) {
                   $checkTax = checkTaxInAllTaxes($text);
                   if (!$checkTax) {
                       $allTaxes[] = [
                           'id' => $text,
                           'text' => $text,
                           'name' => $data['lead']->lead_tax_name,
                           'rate' => $data['lead']->lead_tax_rate,
                           'value' => round($data['lead']->lead_tax_value, 3)
                       ];
                   }
               }
               $data['taxRecommendation'] = round($data['lead']->lead_tax_value, 3);
           }
       }
		$name = $data['client_data']->client_name;
		$street = isset($data['estimate_data']->lead_address) ?  $data['estimate_data']->lead_address : $data['client_data']->client_address;

		$city = isset($data['estimate_data']->lead_city) ?  $data['estimate_data']->lead_city : $data['client_data']->client_city;
		$country = isset($data['estimate_data']->lead_country) ?  $data['estimate_data']->lead_country : $data['client_data']->client_country;
		$address = $street . "+" . $city;
		//Set the map:
		$config['center'] = $address;
		$config['zoom'] = '11';

		$this->googlemaps->initialize($config);

		$marker = array();
		$marker['position'] = $address;
		$marker['icon'] = mappin_svg('#FD7567', '&#9899;', FALSE, '#000');
		$this->googlemaps->add_marker($marker);
		$data['address'] = $address;
		$data['map'] = $this->googlemaps->create_map();
       $data['allTaxes'] = $allTaxes;
       $data['access_token'] = $this->mdl_settings_orm->get_by('stt_key_name', 'accessTokenKey')->stt_key_value ? 1 : 0;

       $data['work_types'] = $this->work_types->get_all();
       $data['trees_types'] = $this->mdl_trees->get_trees();

       $this->load->view("form", $data);
	}

// End New Estimate Function;


	function save_estimate()
    {
//        die(json_encode(array('status' => 'error', 'estimate_id' => $this->input->post())));
        $estimate_id = $this->mdl_estimates_orm->save_estimate();
        if(!$estimate_id)
            die(json_encode(array('status' => 'error', 'msg' => "Your request couldn't be processed")));
        $estimate = $this->mdl_estimates_orm->get($estimate_id);

        //create a new job for synchronization in QB
        $invoice = Invoice::where('estimate_id', $estimate_id)->first();
        if (!empty($invoice)) {
            $this->invoiceactions->changeInvoiceStatusWhenUpdatingEstimate($invoice);
            pushJob('quickbooks/invoice/syncinvoiceinqb', serialize(['id' => $invoice->id, 'qbId' => $invoice->invoice_qb_id]));
        }

        die(json_encode(array('status' => 'ok', 'estimate_id' => $estimate_id, 'estimate_no' => $estimate->estimate_no)));
    }

// End. Add Estimate.

// End. Add Estimate.

    function preupload() {
        $photos = $this->estimateactions->preupload($this->input->post());
        return $this->response([
            'status' => TRUE,
            'data' => $photos
        ]);

        $this->load->model('mdl_leads');
        $leadId = $this->input->post('lead_id');
        $estimateId = $this->input->post('estimate_id');
        $serviceId = $this->input->post('service_id');
        $uuids = $this->input->post('files_uuids');
        $uuids = $uuids ? explode(',', $uuids) : [];

        $lead = $this->mdl_leads->find_by_id($leadId);
        if(!$lead)
            die(json_encode(['status' => FALSE]));

        $path = 'uploads/clients_files/' . $lead->client_id . '/leads/tmp/' . str_replace('-L', '-E', $lead->lead_no) . '/';
        $max = 1;
        $updateEstimatePdfFiles = FALSE;

        if($estimateId && $serviceId) {  // if upload file for exists service
            $estimate = $this->mdl_estimates_orm->get($estimateId);
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
        if (isset($_FILES['files']) && is_array($_FILES['files'])) {
            $this->load->library('upload');
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

                $this->upload->initialize($config);
                if ($this->upload->do_upload('file')) {
                    $uploadData = $this->upload->data();
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
                        'error' => strip_tags($this->upload->display_errors())
                    ];
                }
            }
        }
        if($updateEstimatePdfFiles)
            $this->mdl_estimates_orm->update($estimateId, ['estimate_pdf_files' => json_encode($estimate_pdf_files)]);

        die(json_encode([
            'status' => TRUE,
            'data' => $photos
        ]));
    }

//*******************************************************************************************************************
//*************
//*************																					Edit Estimate Function;
//*************
//*************																			*** Retrives all estimate data.
//*************																		Returns html form with values ***
//*************
//*******************************************************************************************************************

	public function edit($estimate_id=null)
	{
		if (!$estimate_id) // NB: Set to redirect to index if variable is null or not set;
			redirect('estimates/', 'refresh');
        $estimate_id = (int) $estimate_id;
		//Set title:
		$data['title'] = $this->_title . ' - Edit Estimate';
		$data['menu_estimates'] = "active";
		$this->load->model('mdl_est_equipment');
		$this->load->model('mdl_info');
		$this->load->model('mdl_services');
		$this->load->model('mdl_leads_services');
        $this->load->model('mdl_estimates_bundles');
        $this->load->model('mdl_settings_orm');
		//Get estimate informations - using common function from MY_Models;
		$data['brands'] = Brand::withTrashed()->get();
		
		$data['estimate_id'] = $estimate_id;

		$wdata = ['estimate_id' => $estimate_id];

		if (is_cl_permission_none()) {
            $wdata['estimates.user_id'] = -1;
        }

		$data['estimate_data'] = $this->mdl_estimates_orm->with('mdl_services_orm')->get_full_estimate_data($wdata);

		if(!is_array($data['estimate_data']) || (is_array($data['estimate_data']) && !count($data['estimate_data'])))
			return page_404(['message'=>'This estimate does not exist']);

		$data['estimate_data'] = $data['estimate_data'][0];

		
		foreach ($data['estimate_data']->mdl_services_orm as &$service) {
		    $service->expenses = $this->mdl_expenses_orm->get_many_by(array('ese_estimate_service_id' => $service->id));
        }

		$data['services_estimate_data'] = $this->mdl_estimates->find_estimate_services($estimate_id);
		$data['services'] = $this->mdl_services->find_all(array('service_status' => 1, 'is_product' => 0, 'is_bundle' => 0), 'service_priority');
		$data['products'] = $this->mdl_services->find_all(array('service_status' => 1, 'is_product'=>1), 'service_priority');
        $bundles = $this->mdl_services->find_all(array('service_status' => 1, 'is_bundle' => 1), 'service_priority');

        foreach ($bundles as $bundle){
            $result = $this->mdl_services->get_records_included_in_bundle($bundle->service_id);
            if($result){
                foreach ($result as $record)
                    $record->non_taxable = 0;
            }
            $bundle->bundle_records = json_encode($result, true);
        }
        $data['bundles'] = $bundles;
        //data for favourite icons
        $servicesWithIcons = DB::table('services')->where([['service_is_favourite', '!=', 0],['service_favourite_icon', '!=', null], ['service_status', 1]])->orderBy('is_bundle')->orderBy('is_product')->get()->toArray();
        $data['favouriteIcons'] = setFavouriteShortcut($servicesWithIcons);

        $categoryWithProducts = Category::whereNull('category_parent_id')->with(['categoriesWithProducts', 'products'])->get()->toArray();
        $categoryWithServices = Category::whereNull('category_parent_id')->with(['categoriesWithServices', 'services'])->get()->toArray();
        $categories = Category::whereNull('category_parent_id')->with('categories')->get()->toArray();
        $classes = QBClass::where(['class_parent_id' => null, 'class_active' => 1])->with('classesWithoutInactive')->get();
        $data['classes'] = [];
        if(!empty($classes->toArray())) {
            $data['classes'] = getClasses($classes->toArray());
        }
        $data['categoriesWithChildren'] = getCategories($categories);
        $data['categoriesWithProducts'] =  $this->estimateactions->getCategoryWithItemsForSelect2($categoryWithProducts);
        $data['categoriesWithServices'] =  $this->estimateactions->getCategoryWithItemsForSelect2($categoryWithServices);

		$data['estimate_crews_data'] = $this->mdl_estimates->find_estimate_crews($estimate_id);
		$data['trees'] = $this->mdl_info->find_all();
		$data['icons'] = bucketScanDir('uploads/scheme_items/');
		sort($data['icons']);

		//SETUP
		$this->load->model('mdl_vehicles');
		$data["tools"] = $this->mdl_vehicles->get_many_by(array('vehicle_trailer' => 2, 'vehicle_disabled' => NULL));
		$data["vehicles"] = $this->mdl_vehicles->get_many_by(array('vehicle_trailer IS NULL', 'vehicle_disabled' => NULL));
		$data["trailers"] = $this->mdl_vehicles->get_many_by(array('vehicle_trailer' => 1, 'vehicle_disabled' => NULL));
		//SETUP END
		//discount
		$data['discount_data'] = $this->mdl_clients->get_discount(array('discounts.estimate_id' => $data['estimate_data']->estimate_id));

		//Get client_id and retrive client's information:
		$client_id = $data['estimate_data']->client_id;
		$data['client_data'] = $this->mdl_clients->find_by_id($client_id);
		$data['client_contacts'] = $this->mdl_clients->get_client_contacts(array('cc_client_id' => $client_id)); //Get client contacts

        $data['lead'] = $this->mdl_leads->find_by_id($data['estimate_data']->lead_id);
        $data['lead']->lead_country = $data['client_data']->client_country;

        $this->load->model('mdl_tree_inventory_orm', 'tree_inventory');
		$data['tree_inventory'] = $this->tree_inventory->with('work_types')->with('tree_type')->order_by('ti_tree_number')->get_many_by(['ti_client_id'=>$data['lead']->client_id, 'ti_lead_id'=>$data['lead']->lead_id]);

        $data['est_services'] = $this->mdl_leads_services->get_with_services(['lead_id' => $data['estimate_data']->lead_id]);

        $data['crews'] = $tpl_data['crews'] = $this->mdl_crews->get_crews(array('crew_status' => 1, 'crew_id >' => 0), 'crew_status DESC, crew_priority ASC');

        $client_tags = $this->mdl_clients->get_client_tags(array('client_id' => $data['client_data']->client_id)); //Get client contacts
        $data['client_tags'] = array_map(function ($item){
            return ['id'=>$item['tag_id'], 'text' => $item['name']];
        }, $client_tags);

		$name = $data['client_data']->client_name;
		$street = $data['estimate_data']->lead_address;

		$city = $data['estimate_data']->lead_city;
		$country = $data['estimate_data']->lead_country;
		$address = $street . "+" . $city;
			//Set the map:
		$config['center'] = $address;
		$config['zoom'] = '11';

		$this->googlemaps->initialize($config);

		$marker = array();
		$marker['position'] = $address;
		$marker['icon'] = mappin_svg('#FD7567', '&#9899;', FALSE, '#000');
		$this->googlemaps->add_marker($marker);
		$data['address'] = $address;
		$data['map'] = $this->googlemaps->create_map();


		$tpl_data['services'] = $this->mdl_services->with('mdl_services')->get_many_by(array('service_parent_id' => NULL, 'service_status' => 1, 'is_product' => 0, 'is_bundle' => 0));
		$tpl_data['products'] = $data['products'];
		$tpl_data['bundles'] = $data['bundles'];
		$tpl_data['records_bundle'] = [];
//		$tpl_data['records_bundle'] = $this->mdl_services->get_records_included_in_bundle($id);

		$tpl_data['estimate_id'] = $estimate_id;
		$tpl_data["tools"] = $data['tools'];
		$tpl_data["vehicles"] = $data['vehicles'];
		$tpl_data["trailers"] = $data['trailers'];

		//SETUP
		//$tpl_data['equipment'] = $this->mdl_est_equipment->order_by('eq_weight')->get_many_by(array('eq_status' => 1));
		//END SETUP
		//$tpl_data['crews'] = $this->mdl_crews->get_crews(array('crew_status' => 1, 'crew_id >' => 0), 'crew_name');
		$data['blocks'] = $this->config->item('leads_services');
		$this->load->library('user_agent'); 
		$data['product_tpl'] = json_encode(array('tpl' => $this->load->view('products/product_tpl', $tpl_data, TRUE)));
		$data['service_tpl'] = json_encode(array('tpl' => $this->load->view('service_tpl', $tpl_data, TRUE)));
		$data['bundle_tpl'] = json_encode(array('tpl' => $this->load->view('bundles/bundle_tpl', $tpl_data, TRUE)));
        $taxes = all_taxes();
        $data['taxText'] = $data['estimate_data']->estimate_tax_name . ' (' . floatval($data['estimate_data']->estimate_tax_value) . '%)';
        $data['taxName'] = $data['estimate_data']->estimate_tax_name;
        $data['taxRate'] = $data['estimate_data']->estimate_tax_rate;
        $data['taxValue'] = round($data['estimate_data']->estimate_tax_value, 3);
        $checkTax = checkTaxInAllTaxes($data['taxText']);
        if(!$checkTax)
            $taxes[] = ['text' => $data['taxText'], 'id' => $data['taxText']];

        // Tax recommendation for US companies if the address has changed
        if(config_item('office_country') == 'United States of America') {
            if(!empty($data['lead']->lead_tax_name)){
                $data['taxRecommendation'] = round($data['lead']->lead_tax_value, 3);
                $taxText = $data['lead']->lead_tax_name . ' (' . round($data['lead']->lead_tax_value, 3) . '%)';
                $checkTax = checkTaxInAllTaxes($taxText);
                if(!$checkTax && $data['taxText'] != $taxText)
                    $taxes[] = ['text' => $taxText,'id' => $taxText];
            }
        }
        $data['allTaxes'] = $taxes;
        $data['access_token'] = $this->mdl_settings_orm->get_by('stt_key_name', 'accessTokenKey')->stt_key_value ? 1 : 0;

        if(!empty($data['estimate_data']) && !empty($data['estimate_data']->estimate_scheme)) {
            $scheme = json_decode($data['estimate_data']->estimate_scheme);
            if(isset($scheme->objects))
                $data['estimate_data']->estimate_scheme = null;
        }

        $this->load->model('mdl_tree_inventory_work_types_orm', 'tree_inventory_work_types');


        $data['work_types'] = $this->work_types->get_all();

        $data['trees_types'] = $this->mdl_trees->get_trees();
        $data['tree_inventory_service_id'] = config_item('tree_inventory_service_id');


        foreach($data['estimate_data']->mdl_services_orm as $key=> $tree){
            $value=$tree->tree_inventory;
            if(isset($value['ties_estimate_service_id']) && !empty($value['ties_estimate_service_id'])){
                $workTypes=TreeInventoryEstimateService::where('ties_estimate_service_id', $value['ties_estimate_service_id'])->with('tree_inventory_work_types')->first()->tree_inventory_work_types->pluck('tieswt_wt_id')->toArray();
                $data['estimate_data']->mdl_services_orm[$key]->tree_inventory['work_types'] ='';
                if(!empty($workTypes) && is_array($workTypes)) {
                    $data['estimate_data']->mdl_services_orm[$key]->tree_inventory['work_types']= implode(', ', $workTypes);
                }
            }
        }

		$this->load->view("form", $data);

	}// End Edit Estimate.

    /**********DEPRECATE*****************/
	/*public function estimates_mapper($status = null)
	{
		ini_set('memory_limit', '-1');
		$status = intval($status);
		if (!$status)
			redirect(base_url('estimates/estimates_mapper/1'));

		//Page Presets
		$status_name = $this->mdl_est_status->get(array('est_status_id' => $status));
		$data['title'] = $this->_title . ' - Estimates Map - ' . $status_name->est_status_name;
		$data['menu_estimates'] = "active";

		//Set the map:
		$config['center'] = config_item('map_center');
		$config['zoom'] = 'auto';
		$this->googlemaps->initialize($config);

		//Get required workorder data:

		$arr = $this->mdl_estimates_orm->with('mdl_services_orm')->get_full_estimate_data(array('status =' => $status), FALSE);
		$data['status_id'] = $status;
		$data['statuses'] = $this->mdl_est_status->get_many_by(array('est_status_active' => 1));

		if (!empty($arr)) {
			//Creating the markers for leads:
			foreach ($arr as $row) {
				$client_id = $row->client_id;
				$name = $row->client_name;
				$street = $row->lead_address;
				$city = $row->lead_city;
				$address = $street . " " . $city;
				$status = $row->status;

				//Marker Content:
				$marker_link = base_url($row->estimate_no);
				$marker_content = "<strong><a href='" . $marker_link . "' target='_blank'>" . $row->estimate_no . "<br>" . $address . "</a></strong>";
				$marker_content .= "<br>Status: " . $status;

				$marker_style = mappin_svg('#D8B44F', '&#9899;', FALSE, '#000');

				$marker = array();
				$marker['position'] = $row->lat . ',' . $row->lon;//$address;
				$marker['infowindow_content'] = $marker_content;
				$marker['icon'] = $marker_style;
				$this->googlemaps->add_marker($marker);
			}
		}
		$data['map'] = $this->googlemaps->create_map();

		$this->load->view('map', $data);
	}*/


//*******************************************************************************************************************
//*************										Update Estimate Function;
//*************
//*************									*** Updates estimated data.
//*************										Returns to Profile ***
//*************
//*******************************************************************************************************************

	function deactivate_client($id)
	{
		$update_data['client_status'] = 0;
		$wdata['client_id'] = $id;
		if ($this->mdl_clients->update_client($update_data, $wdata)) {

			$link = ('estimates');
			$mess = message('success', 'Client Updated!');
			$this->session->set_flashdata('user_message', $mess);
			redirect($link);
		}
	}

//*******************************************************************************************************************
//*************
//*************										Ajax Estimates Search
//*************
//*************
//*******************************************************************************************************************
	function ajax_get_estimates()
	{
		$return = $this->mdl_estimates->search_estimates();

		if ($return->num_rows() > 0) {
			foreach ($return->result() as $rows):
				?>
				<tr>
					<td><?php echo anchor('clients/new/' . $rows->client_id, $rows->client_name); ?></td>
					<th><?php echo $rows->lead_body; ?></th>
					<td><?php echo $rows->lead_date_created; ?></td>
					<td><?php echo $rows->lead_created_by; ?></td>
					<td><?php echo $rows->lead_status; ?></td>
					<td><?php
						if ($rows->lead_status == 'assigned')
							echo anchor($rows->lead_no, 'Edit');
						else if ($rows->lead_status == 'estimated')
							echo anchor('estimates/new_estimate/' . $rows->lead_id, 'Create Estimate');
						?></td>
				</tr>
			<?php
			endforeach;
		} else {
			print_r('<tr><td colspan="6">No records found</td></tr>');
		}
	}// End ajax_estimate_search

//*******************************************************************************************************************
//*************
//*************
//*************																			Ajax Change Estimate Status
//*************
//*************
//*******************************************************************************************************************
	function ajax_change_estimates_status()
	{

        $user = $this->mdl_users_orm->get(request()->user()->id);

        /******************VALIDATION******************/

        if (!$estimate_id = $this->input->post('estimate_id'))
            return $this->response(['status' => 'error', 'error' => 'Incorrect Request']);

        if (!$new_estimate_status = $this->input->post('new_estimate_status'))
            return $this->response(['status' => 'error', 'error' => 'Incorrect Request']);

        if (!$pre_estimate_status = $this->input->post('pre_estimate_status'))
            return $this->response(['status' => 'error', 'error' => 'Incorrect Request']);

        if ($pre_estimate_status == $new_estimate_status) {
            return $this->response(['status' => 'ok']);
        }

        if (isset($_FILES) && isset($_FILES['payment_file']) && isset($_FILES['payment_file']['error']) && $_FILES['payment_file']['error'] === 0) {
            if ($_FILES['payment_file']['tmp_name'] && !is_image($_FILES['payment_file']['tmp_name']) && !is_pdf($_FILES['payment_file']['tmp_name']))
                return $this->response(['status' => 'error', 'error' => 'File must be image or PDF']);
        }
        /******************VALIDATION******************/

        $new_status_data = $this->mdl_est_status->get_by(array('est_status_id' => $new_estimate_status));

        // Ебаная проверка :))
        $passPay = false;
        if($new_status_data->est_status_confirmed){
            if($this->input->post('payment_id'))
                $passPay = true;
            if(request()->user()->user_type == "admin"){
                if(!$this->input->post('wo_deposit') || $this->input->post('wo_deposit') == "")
                    $passPay = true;
            } elseif(!(bool)$user->is_require_payment_details){
                if(!$this->input->post('wo_deposit') || $this->input->post('wo_deposit') == "")
                    $passPay = true;
            }
        } else {
            $passPay = true;
        }

        if(!$passPay) {
            /******************VALIDATION******************/

            $amount = (float) $this->input->post('wo_deposit');
            if (!$amount || $amount == "") {
                return $this->response([
                    'status' => 'error', 'errors' => ['appendedPrependedInput_wo_deposit' => 'Amount Is Required']
                ]);
            }

            $amount = getAmount($amount);
            $amount = floatval($amount);

            $method = $this->input->post('method');
            if (!$method || $method == "") {
                return $this->response([
                    'status' => 'error', 'errors' => ['payment_method_status' => 'Incorrect payment method']
                ]);
            }

            $fee_percent = 0;
            $fee = 0;

            if ($method == config_item('default_cc')) {
                if (_CC_MAX_PAYMENT != 0 && $amount > _CC_MAX_PAYMENT) {
                    return $this->response([
                        'status' => 'error',
                        'errors' => ['appendedPrependedInput_wo_deposit' => 'Maximum Payment Amount '.money(_CC_MAX_PAYMENT)]
                    ]);
                }

                if (!$cc_id = $this->input->post('cc_id')) {
                    return $this->response([
                        'status' => 'error', 'error' => 'Card processing error',
                        'errors' => ['cc_select' => 'Payment card is not selected']
                    ]);
                }

                $fee_percent = round((float) config_item('cc_extra_fee'), 2);
                if ($fee_percent > 0) {
                    $fee = round($amount * ($fee_percent / 100), 2);
                    $amount += $fee;
                }
            }

            /******************VALIDATION******************/
            $estimate_data = $this->mdl_estimates->find_by_id($estimate_id);
            $invoice_data = $estimate_id ? $this->mdl_invoices->find_by_field(['invoices.estimate_id' => $estimate_id]) : FALSE;

            $client_data = Client::find($estimate_data->client_id);
            $client_contact = $client_data->primary_contact()->first();

            $this->load->library('Payment/ArboStarProcessing', null, 'arboStarProcessing');
            $file = false;

            if (isset($_FILES) && isset($_FILES['payment_file']) && isset($_FILES['payment_file']['error']) && $_FILES['payment_file']['error'] === 0) {
                $file = $this->arboStarProcessing->uploadFile([
                    'client_id' => $client_data->client_id,
                    'estimate_id' => $estimate_data->estimate_id,
                    'estimate_no' => $estimate_data->estimate_no,
                    'invoice_no' => !empty($invoice_data) ? $invoice_data->invoice_no : null,
                    'lead_id' => $estimate_data->lead_id
                ]);
            }

            $paymentData = [];

            if ($method == config_item('default_cc')) {
                $paymentData = [
                    'payment_profile' => $client_data->client_payment_profile_id,
                    'card_id' => $cc_id,
                ];
            }

            $iData = [
                'client' => $client_data,
                'contact' => $client_contact,
                'estimate' => $estimate_data,
                'invoice' => $invoice_data,
                'type' => $this->input->post('type') ?: 'deposit',
                'payment_driver' => $client_data->client_payment_driver,
                'amount' => $amount,
                'fee' => $fee,
                'fee_percent' => $fee_percent,
                'file' => $file,
                'user_id' => request()->user()->id,
                'wo_office_notes' => trim($this->input->post('wo_office_notes')),
                'estimate_crew_notes' => trim($this->input->post('estimate_crew_notes')),
                'date' => $this->input->post('payment_date')
                    ? \Carbon\Carbon::createFromTimeString($this->input->post('payment_date'))->timestamp
                    : \Carbon\Carbon::now()->timestamp,
                'notes' => $this->input->post('payment_note'),
                'extra' => []
            ];

            try {
                $this->arboStarProcessing->pay($method, $iData, $paymentData);
            } catch (PaymentException $e) {
                return $this->response([
                    'status' => 'error',
                    'error' => $e->getMessage()
                ]);
            }
        }

		if ($this->change_estimate_status([
            'estimate_id' => $estimate_id,
            'pre_estimate_status' => $pre_estimate_status,
            'new_estimate_status' => $new_estimate_status,
            'estimate_reason_decline' => $this->input->post('reason'),
            /*'payment_id' => $estimate_id,*/
            /*'payment_method' => 'cc',
            'wo_deposit' => $amount,*/
            'wo_priority' => 'Regular',
            'wo_confirm_how' => $this->input->post('wo_confirm_how'),
            'estimate_crew_notes' => trim($this->input->post('estimate_crew_notes')),
            'wo_office_notes' => trim($this->input->post('wo_office_notes')),
        ])) {
            return $this->response(['status' => 'ok']);
        }

        return $this->response(['status' => 'error', 'error' => 'Error']);
	}

	function change_estimate_status($update_data)
    {

        if ($update_data['pre_estimate_status'] == $update_data['new_estimate_status'])
            return true;

        $estimate_data = $this->mdl_estimates->find_by_id($update_data['estimate_id']);
        if(!$estimate_data)
            return false;

        if($estimate_data->status_id == $update_data['new_estimate_status'])
            return true;

        $status_data = $this->mdl_est_status->get_by(array('est_status_id' => $update_data['new_estimate_status']));
        $status_old_data = $this->mdl_est_status->get_by(array('est_status_id' => $update_data['pre_estimate_status']));
        if ($status_data->est_status_confirmed) {
            $estimateData = $this->mdl_estimates->get_full_estimate_data(array('estimate_id' => $update_data['estimate_id']));
            if($estimateData[0]['lead_reffered_client'])
                $this->mdl_clients->update_client(array('client_is_refferal' => 1), array('client_id' => $estimateData[0]['lead_reffered_client']));
        }

        $status = array('status_type' => 'estimate', 'status_item_id' => $update_data['estimate_id'], 'status_value' => $update_data['new_estimate_status'], 'status_date' => time());
        $this->mdl_estimates->status_log($status);


        //Check if the new status == Confirmed
        //Code to inser workorder data into db

        if ($status_data->est_status_confirmed) {
            $data = array();

            //Form data
            $data['wo_priority'] = isset($update_data['wo_priority']) ? strip_tags($update_data['wo_priority']) : 'Regular';
            $data['wo_confirm_how'] = isset($update_data['wo_confirm_how']) ? strip_tags($update_data['wo_confirm_how']) : NULL;
            $data['wo_office_notes'] = trim(element('wo_office_notes', $update_data, NULL));
            //Work order number
            $work_order = '';
            $estimate_no = $estimate_data->estimate_no;
            $work_order = str_replace('E', 'W', $estimate_no);

            $data['client_id'] = $estimate_data->client_id;
            $data['estimate_id'] = $estimate_data->estimate_id;
            $data['workorder_no'] = $work_order;
            $data['wo_pdf_files'] = $estimate_data->estimate_pdf_files;

            $data['wo_status'] = FALSE;
            /*-------------workorder status for client link payment ----------------*/
            if(isset($update_data['is_client']) && $update_data['is_client']==TRUE)
            	$data['wo_status'] = $this->mdl_workorders->getConfirmByClientId();
            /*-------------workorder status for client link payment ----------------*/

            if($data['wo_status']===FALSE)
            	$data['wo_status'] = $this->mdl_workorders->getDefaultStatusId();

            $data['date_created'] = date('Y-m-d');

            $workorder_id = $this->mdl_workorders->insert_workorders($data);
            $estimate_crews_data = $this->mdl_estimates->find_estimate_crews($estimate_data->estimate_id);
            foreach ($estimate_crews_data as $crew) {
                if ($crew['crew_leader'])
                    $this->mdl_workorders->insert_workorder_workers(array('workorder_id' => $workorder_id, 'employee_id' => $crew['crew_leader'], 'crew_id' => $crew['crew_id']));
            }
        } else {
            $invoice = $this->mdl_invoices->find_by_field(['invoices.estimate_id' => $update_data['estimate_id']]);
            if (is_object($invoice))
                pushJob('quickbooks/invoice/syncinvoiceinqb', serialize(['id' => $invoice->id, 'qbId' => $invoice->invoice_qb_id]));
            $wo = $this->mdl_workorders->find_by_fields(['estimate_id' => $update_data['estimate_id']]);
            if(!empty($wo)){
                $schedule = $this->mdl_schedule->find_by_fields(['event_wo_id' => $wo->id]);
                if(!empty($schedule) && !empty($schedule->event_team_id)){
                    $note = 'The Work order ' . $wo->workorder_no . ' has been removed so the event has been canceled.';
                    $this->mdl_schedule->update_team($schedule->event_team_id, ['team_note' => $note]);
                }
            }
            $this->mdl_workorders->delete_workorder($update_data['estimate_id']);
            $this->mdl_invoices->delete_invoice($update_data['estimate_id']);
        }

        //Update status code:
        $upd_est_data['status'] = $update_data['new_estimate_status'];
        $upd_est_data['estimate_crew_notes'] = trim(element('estimate_crew_notes', $update_data, NULL));

        if(isset($update_data['estimate_reason_decline']))
            $upd_est_data['estimate_reason_decline'] = $update_data['estimate_reason_decline'];
        $wdata = array('estimate_id' => $update_data['estimate_id']);
        $updated = $this->mdl_estimates->update_estimates($upd_est_data, $wdata);
        $this->load->model('mdl_followups');
        $fuRowPost = $this->mdl_followups->get_by(['fu_module_name' => 'estimates', 'fu_item_id' => $update_data['estimate_id'], 'fu_status' => 'postponed']);
        $fuRowNew = $this->mdl_followups->get_by(['fu_module_name' => 'estimates', 'fu_item_id' => $update_data['estimate_id'], 'fu_status' => 'new']);

        if($fuRowNew && !empty($fuRowNew))
            $this->mdl_followups->update($fuRowNew->fu_id, ['fu_status' => 'canceled', 'fu_comment' => 'Canceled ' . date('Y-m-d H:i:s') . ' - estimate status was changed']);
        elseif($fuRowPost && !empty($fuRowPost))
            $this->mdl_followups->update($fuRowPost->fu_id, ['fu_status' => 'canceled', 'fu_comment' => 'Canceled ' . date('Y-m-d H:i:s') . ' - estimate status was changed']);

        if ($updated) {
            $update_msg = "Status for " . $estimate_data->estimate_no . ' was modified from ' . $status_old_data->est_status_name . ' to ' . $status_data->est_status_name;
            make_notes($estimate_data->client_id, $update_msg, 'system', $estimate_data->lead_id);
        }
        return true;
    }

	// End ajax_change_estimates_status

	function send_pdf_to_email()
	{
		$estimate_id = $this->input->post('id');
		if (!intval($estimate_id))
			return $this->ajax_response(array('type' => 'error', 'message' => 'Estimate id is not valid'));
		//$note['to'] = $to = $this->input->post('emails');


        $note['from'] = $from_email = $this->input->post('email_from');
		$cc = $bcc = '';
		if($this->input->post('cc') != null && $this->input->post('cc') != ''){
			$note['cc'] = $cc = $this->input->post('cc');
		}

		if($this->input->post('bcc') != null && $this->input->post('bcc') != ''){
			$note['bcc'] = $bcc = $this->input->post('bcc');
		} elseif($this->config->item('default_bcc')) {
			$note['bcc'] = $bcc = $this->config->item('default_bcc');
		}
		$note['subject'] = $subject = $this->input->post('subject');
		$text = $this->input->post('text');
		$data['estimate_data'] = $this->mdl_estimates->find_by_id($estimate_id);

		if (empty($data['estimate_data']))
			return $this->ajax_response(array('type' => 'error', 'message' => 'Estimate id is not defined'));

        $note['to'] = $to = $this->input->post('email_tags');
		$check = check_receive_email($data['estimate_data']->client_id, $to);

		if($check['status'] != 'ok')
			return $this->ajax_response(array('type' => $check['status'], 'message' => $check['message']));

        $this->load->library('email');
        $config['mailtype'] = 'html';
        $toDomain = substr(strrchr($to, "@"), 1);
        if(array_search($toDomain, $this->config->item('smtp_domains')) !== FALSE) {
            $config = $this->config->item('smtp_mail');
            $note['from'] = $email = $config['smtp_user'];
        }

        $this->email->initialize($config);
        /*$cc = '';
        if(count($toEmails) > 1)
        {
            $note['cc'] = $cc = str_replace($toEmails[0].',', '', $this->input->post('email_tags'));
            unset($toEmails[0]);
        }*/

		if($data['estimate_data']->user_signature)
			$text .= $data['estimate_data']->user_signature;

		$pdf = $this->estimate_pdf_generate($estimate_id);
		if(!$pdf) {
            $this->ajax_response(array('type' => 'error', 'message' => 'Incorrect Estimate'));
            return false;
        }

		$this->load->library('mpdf');
		$this->mpdf->WriteHTML($pdf['html']);

        if(isset($data['estimate_data']->tree_inventory_pdf) && !empty($data['estimate_data']->tree_inventory_pdf)) {
            $this->leadsactions->setLead($data['estimate_data']->lead_id);
            $treeInventoryHtml = $this->leadsactions->tree_inventory_pdf(true);

            if ($treeInventoryHtml) {
                $this->mpdf->WriteHTML($treeInventoryHtml);
            }
        }

        foreach ($pdf['files'] as $file) {
            if(pathinfo($file, PATHINFO_EXTENSION) == 'pdf') {
                $this->mpdf->AddPage();
                $this->mpdf->Thumbnail(bucket_get_stream($file), 1, 10, 16, 1);
            }
        }
		$file = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $pdf['file'] . '.pdf';

		if(is_file($file))
			@unlink($file);

		if(file_exists($file)) {
			$attach = $file = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $pdf['file'] . '-' . uniqid() . '.pdf';
		}
        $this->mpdf->Output($file, 'F');



		$note['from'] = $email = $this->input->post('email_from') ? $this->input->post('email_from') : $this->config->item('account_email_address');



		//checking if a file in not larger than default_pdf_size from the settings
        if(filesize($file) < config_item('default_pdf_size')
            && strlen(base64_encode(file_get_contents($file))) < config_item('default_pdf_size')){
            $this->email->attach($file);
        }else{
            $estimate_link = '<div style="text-align: center">';
            $href = base_url("payments/estimate/" . md5($data["estimate_data"]->estimate_no . $data["estimate_data"]->client_id));
            $estimate_link .= '<a href="' . $href . '" style="font-family:-apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\';box-sizing:border-box;border-radius:3px;color:#fff;display:inline-block;text-decoration:none;background-color: #81BA53;border-top: 10px solid #81BA53;border-right: 18px solid #81BA53;border-bottom: 10px solid #81BA53;border-left: 18px solid #81BA53;font-size: 20px;" target="_blank" data-saferedirecturl="' . $href . '">View Estimate</a>';
            $estimate_link .= '</div>';
            $text .= $estimate_link;
        }

        $text .= '<br><div style="text-align:center; font-size: 10px;"> If you no longer wish to receive these emails you may ' .
            '<a href="' . $this->config->item('unsubscribe_link') . md5($data['estimate_data']->client_id) . '">unsubscribe</a> at any time.</div>';

		$name = ($data['estimate_data']->firstname && $data['estimate_data']->lastname) ? ' - ' . $data['estimate_data']->firstname . ' ' . $data['estimate_data']->lastname : '';


        if($cc && $cc != '')
            $this->email->cc($cc);
		if($bcc && $bcc != '')
			$this->email->bcc($bcc);
		$this->email->from($email, $this->config->item('company_name_short') . $name);
        $this->email->to($to);

		$this->email->subject($subject);
		$this->email->message($text);
		$this->email->set_newline("\r\n");
        $send = $this->email->send();

        if (!is_array($send) || isset($send['error'])) {
            $error = 'Oops! Email send error. Please try again';

            if (isset($send['error'])) {
                $error = $send['error'];
            }

            return $this->ajax_response(array('type' => 'error', 'message' => $error));
        }

        $entities = [
            ['entity' => 'estimate', 'id' => $data['estimate_data']->estimate_id],
            ['entity' => 'client', 'id' => $data['estimate_data']->client_id]
        ];
        $this->email->setEmailEntities($entities);

		$this->load->model('mdl_est_status');
		$statusRow = $this->mdl_est_status->get_by(array('est_status_sent' => 1));

		$update_data = array('status' => $statusRow->est_status_id);
		$wdata = array('estimate_id' => $estimate_id);
		$updated = FALSE;
		if ($data['estimate_data']->est_status_default == 1) {
			$updated = $this->mdl_estimates->update_estimates($update_data, $wdata);
			$delete_workorder = $this->mdl_workorders->delete_workorder($estimate_id);
			$delete_invoice = $this->mdl_invoices->delete_invoice($estimate_id);
		}

		$note_id = make_notes(
            $data['estimate_data']->client_id,
            'Estimate ' . $data['estimate_data']->estimate_no . ' sent to "' . $to . '".',
            'email',
            $data['estimate_data']->lead_id,
            $this->email
        );

		$dir = 'uploads/notes_files/' . $data['estimate_data']->client_id .'/' . $note_id . '/';

		$pattern = "/<body>(.*?)<\/body>/is";
		preg_match($pattern, $text, $res);
		$note['text'] = isset($res[1]) ? $res[1] : $text;
		$this->mpdf->Output(sys_get_temp_dir() . DIRECTORY_SEPARATOR . $data['estimate_data']->estimate_no . '.pdf', 'F');
        bucket_move(sys_get_temp_dir() . DIRECTORY_SEPARATOR . $data['estimate_data']->estimate_no . '.pdf', $dir . $data['estimate_data']->estimate_no . '.pdf', ['ContentType' => 'application/pdf']);
        @unlink(sys_get_temp_dir() . DIRECTORY_SEPARATOR . $data['estimate_data']->estimate_no . '.pdf');
		bucket_write_file($dir . 'Content.html', $this->load->view('clients/note_file', $note, TRUE), ['ContentType' => 'text/html']);
        bucket_unlink_all('uploads/clients_files/' . $data['estimate_data']->client_id . '/estimates/' . $data['estimate_data']->estimate_no . '/tmp/');

		return $this->ajax_response(array('type' => 'success', 'message' => 'Email sent. Thanks'));
	}

	function deletePhoto()
	{
		$estimate_no = $this->input->post('estimate_no');
		$name = $this->input->post('name');
		$service = $this->input->post('service');
		$client = $this->input->post('client');
		$lead_id = $this->input->post('lead_id');
		$path = get_image_dir($client, $estimate_no, $service);
		if (is_bucket_file($path . $name)) {
			if (bucket_unlink($path . $name)) {
                $estimate_data = $this->mdl_estimates_orm->get_by(['lead_id' => $lead_id]);
                $estimate_pdf_files = $estimate_data->estimate_pdf_files ? json_decode($estimate_data->estimate_pdf_files) : [];
                if(array_search($path . $name, $estimate_pdf_files) !== FALSE) {
                    $key = array_search($path . $name, $estimate_pdf_files);
                    unset($estimate_pdf_files[$key]);
                    $estimate_pdf_files = array_values($estimate_pdf_files);
                    $this->mdl_estimates_orm->update($estimate_data->estimate_id, ['estimate_pdf_files' => json_encode($estimate_pdf_files)]);
                }

                return $this->ajax_response(array('type' => 'ok'));
            }
			return $this->ajax_response(array('type' => 'error', 'message' => 'Permission denied'));
		}
		$tmpPath = 'uploads/clients_files/' . $client . '/leads/tmp/' . str_pad($lead_id, 5, '0', STR_PAD_LEFT) . '-E/';
        if (is_bucket_file($tmpPath . $name)) {
            if (bucket_unlink($tmpPath . $name))
                return $this->ajax_response(array('type' => 'ok'));
            return $this->ajax_response(array('type' => 'error', 'message' => 'Permission denied'));
        }
		return $this->ajax_response(array('type' => 'ok', 'message' => 'Incorrect file'));
	}

	function deleteFile()
	{
		$estimate_id = $this->input->post('estimate_id');
		$path = $this->input->post('path');
		if($estimate_id) {
			/**************/
			$estimate_data = $this->mdl_estimates->find_by_id($estimate_id);
			$files = isset($estimate_data->estimate_pdf_files) ? json_decode($estimate_data->estimate_pdf_files) : [];
			if(!$files)
				$files = [];
			$key = array_search(ltrim(ltrim($path, '.'), '/'), $files);
			if($key !== FALSE)
				unset($files[$key]);
			$files = array_values($files);
			$str = json_encode($files);
			$this->mdl_estimates->update($estimate_id, ['estimate_pdf_files' => $str]);
			/*******************/
			$files = [];
			$workorder_data = $this->mdl_workorders->find_by_fields(['estimate_id' => $estimate_id]);
			$files = isset($workorder_data->wo_pdf_files) ? json_decode($workorder_data->wo_pdf_files) : [];
			if(!$files)
				$files = [];
			$key = array_search(ltrim(ltrim($path, '.'), '/'), $files);
			if($key !== FALSE)
				unset($files[$key]);
			$files = array_values($files);
			$str = json_encode($files);
			$this->mdl_workorders->update_workorder(['wo_pdf_files' => $str], ['estimate_id' => $estimate_id]);
			/*******************/
			$files = [];
			$invoice_data = $this->mdl_invoices->find_by_fields(['estimate_id' => $estimate_id]);
			$files = isset($workorder_data->invoice_pdf_files) ? json_decode($workorder_data->invoice_pdf_files) : [];
			if(!$files)
				$files = [];
			$key = array_search(ltrim(ltrim($path, '.'), '/'), $files);
			if($key !== FALSE)
				unset($files[$key]);
			$files = array_values($files);
			$str = json_encode($files);
			$this->mdl_invoices->update_invoice(['invoice_pdf_files' => $str], ['estimate_id' => $estimate_id]);
		}
		if (is_bucket_file(trim($path, '/ '))) {
			if (bucket_unlink(trim($path, '/ ')))
				return $this->ajax_response(array('type' => 'ok'));
			return $this->ajax_response(array('type' => 'error', 'message' => 'Permission denied'));
		}
		return $this->ajax_response(array('type' => 'error', 'message' => 'Incorrect file'));
	}

	function ajax_add_contact()
	{
		$id = $this->input->post('estimate_id');
		$data = $this->mdl_estimates->find_by_id($id);
		$update = $this->mdl_estimates->update_estimates(array('estimate_last_contact' => time(), 'estimate_count_contact' => ($data->estimate_count_contact + 1)), array('estimate_id' => $id));
		make_notes($data->client_id, $this->input->post('message', TRUE), 'contact', $data->lead_id);
		$this->ajax_response(array('status' => 'ok'));
	}

	function ajax_sort_estimates()
	{
		$field = $this->input->post('field');
		$order = $this->input->post('order');
		$status = $this->input->post('status');

		$page = $this->input->post('page');
		$per_page = 50;
		$start = $page - 1;
		$start = $start * $per_page;
		$limit = $per_page;

		$config = array();
		$config["base_url"] = base_url() . "estimates/paginationEstimates/";
		$config["total_rows"] = $this->mdl_estimates->estimate_record_count('', $status);
		$config["per_page"] = 50;
		$config['full_tag_open'] = '<ul class="pagination pagination-sm m-t-none m-b-none pull-right">';
		$config['full_tag_close'] = '</ul>';
		$config['use_page_numbers'] = TRUE;

		$current_status = $this->mdl_est_status->get($status);
		$data['current_status'] = $current_status;

		$data['symbols'] = array(' - ', ' ','-');
		$data['estimates'][mb_strtolower(str_replace($data['symbols'], '_', $current_status->est_status_name)) . '_estimate'] = $this->mdl_estimates->get_estimates('', $current_status->est_status_id, $limit, $start, $field, $order);
		$config["total_rows"] = $this->mdl_estimates->estimate_record_count('', $current_status->est_status_id);
		$this->pagination->initialize($config);
		$data['estimates'][mb_strtolower(str_replace($data['symbols'], '_', $current_status->est_status_name)) . '_estimate_links'] = $this->pagination->create_links();
		$data['estimates'][mb_strtolower(str_replace($data['symbols'], '_', $current_status->est_status_name)) . '_count'] = $config["total_rows"];
		$data['sorted'] = true;
		$result['html'] = $this->load->view('index_tab_estimate', $data, TRUE);
		$result['status'] = 'ok';

		$this->ajax_response($result);
	}

	private function ajax_response($data)
	{
		echo json_encode($data);
		return false;
	}

	function ajax_add_qa()
	{
        show_404();
		if (!$this->input->post('qa_id'))
			show_404();
		$insert['qa_id'] = $this->input->post('qa_id');
		$insert['qa_message'] = $this->input->post('qa_message');
		$insert['estimate_id'] = $this->input->post('estimate_id');
		$insert['qa_date'] = time();
		$insert['qa_user_id'] = request()->user()->id;
		$this->mdl_estimates->insert_estimate_qa($insert);
		die(json_encode(array('status' => 'ok')));
	}

	function ajax_hst_disable()
	{
        $tax = checkTaxInAllTaxes($this->input->post('disabled'));
        if($tax){
            $data['estimate_tax_name'] = $tax['name'];
            $data['estimate_tax_rate'] = $tax['value'] / 100 + 1;
            $data['estimate_tax_value'] = $tax['value'];
        }elseif($this->input->post('recommendationValue') >= 0){
            $data['estimate_tax_name'] = 'Tax';
            $data['estimate_tax_rate'] = $this->input->post('recommendationValue') / 100 + 1;
            $data['estimate_tax_value'] = $this->input->post('recommendationValue');
        }
        $data['estimate_hst_disabled'] = (is_numeric($this->input->post('disabled')) && $this->input->post('disabled') > 0) ? $this->input->post('disabled') : $this->input->post('inclTax');
		$id = $this->input->post('estimate_id');
		if (!$id)
			die(json_encode(array('status' => 'error')));
		$estimate = $this->mdl_estimates->find_by_id($id);
		$this->mdl_estimates->update($id, $data);
		$this->mdl_estimates->update_estimate_balance($id); //estimate balance
		$this->mdl_invoices->update_all_invoice_interes($id);
        if (!empty($data['estimate_hst_disabled'])) {
            $taxName = $estimate->estimate_tax_name ? $estimate->estimate_tax_name : 'Tax';
            make_notes($estimate->client_id, 'Disabled ' . $taxName . ' for "' . $estimate->estimate_no . '"', 'system', $estimate->lead_id);
        }
        else {
            $taxName = $estimate->estimate_tax_name ? $estimate->estimate_tax_name : 'Tax';
            make_notes($estimate->client_id, 'Enabled ' . $taxName . ' for "' . $estimate->estimate_no . '"', 'system', $estimate->lead_id);
        }
        $invoice = $this->mdl_invoices->find_by_field(['invoices.estimate_id' => $id]);
        if (is_object($invoice))
            pushJob('quickbooks/invoice/syncinvoiceinqb', serialize(['id' => $invoice->id, 'qbId' => $invoice->invoice_qb_id]));
		die(json_encode(array('status' => 'ok')));
	}

	function ajax_priority_service()
	{
		$data = $this->input->post('data');
		$estimate_id = $this->input->post('estimate_id');
		$files = [];
		$new_files = [];
		if (empty($data))
			die(json_encode(array('status' => 'error')));
		if(!empty($estimate_id)){
		    $estimate = Estimate::find($estimate_id);
		    if(!empty($estimate) && !empty($estimate->estimate_pdf_files) && !empty(json_decode($estimate->estimate_pdf_files)))
                $files = json_decode($estimate->estimate_pdf_files);
        }
		foreach ($data as $key => $val) {
			if ($val) {
                $result = EstimatesService::where('id',$val['id'])->with('bundle')->get()->first();
                if(!empty($result) && !empty($result->bundle)){
                    foreach ($result->bundle as $bundle_service){
                        $updateBatch[] = ['id' => $bundle_service->eb_service_id, 'service_priority' => $val['priority']];
                    }
                }
                $updateBatch[] = ['id' => $val['id'], 'service_priority' => $val['priority']];
            }
        }

        if (empty($updateBatch) || empty($updateBatch))
			die(json_encode(array('status' => 'error')));

		$this->load->model('mdl_services');
		if ($this->mdl_estimates->update_priority($updateBatch)) {
		    if(!empty($estimate) && !empty($files)) {
                $estimate_services = EstimatesService::where('estimate_id', $estimate_id)->orderBy('service_priority')->get();
                if (!empty($estimate_services)) {
                    foreach ($estimate_services as $estimate_service) {
                        foreach ($files as $key_file => $file) {
                            $pos = strpos($file, '/' . $estimate_service->id . '/');
                            if ($pos) {
                                $new_files[] = $file;
                                unset($files[$key_file]);
                            }
                        }
                    }
                }
                if(!empty($new_files)) {
                    $files = array_merge($files, $new_files);
                    Estimate::where('estimate_id', $estimate_id)->update(['estimate_pdf_files' => json_encode($files)]);
                }
            }
            die(json_encode(array('status' => 'ok')));
        }
		die(json_encode(array('status' => 'error')));
	}

	private function do_upload_payments($estimate_id = NULL)
	{
		$path = 'uploads/payment_files/';

		$estimate_id = $estimate_id ? $estimate_id : $this->input->post('estimate_id');
		$estimate = $this->mdl_estimates->find_by_id($estimate_id);
		if (empty($estimate))
			return FALSE;
		$path .= $estimate->client_id . '/';
		$path .= $estimate->estimate_no . '/';

		$ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
		$config['allowed_types'] = 'gif|jpg|jpeg|png|pdf|GIF|JPG|JPEG|PNG|PDF';
		$config['overwrite'] = TRUE;
		$this->load->library('upload');
		$config['upload_path'] = $path;
		$files = bucketScanDir($path);
		$key = 1;
		if (!empty($files)) {
			sort($files, SORT_NATURAL);
			preg_match('/payment_([0-9]{1,})\..*?/is', $files[count($files) - 1], $num); //countOk
			$key = isset($num[1]) ? ($num[1] + 1) : 1;
		}
		$config['file_name'] = 'payment_' . $key . '.' . $ext;
		$this->upload->initialize($config);

		if (!$this->upload->do_upload('file'))
			return FALSE;
		$note = 'Add Payment File for ' . $estimate->estimate_no . ': <br> <a href="' . base_url() . $path . $config['file_name'] . '">' . $config['file_name'] . '</a>';
		make_notes($estimate->client_id, $note, 'attachment', $estimate->lead_id);
		return $config['file_name'];
	}

	function ajax_save_file()
	{
		$ext = pathinfo($_FILES['estFile']['name'], PATHINFO_EXTENSION);
		$path = $this->input->post('path');
		$estimate_no = $this->input->post('estimate_no');
		$key = 1;
		$suffix = '';
		$estimate = $this->mdl_estimates_orm->get_by(array('estimate_no' => $estimate_no));

		if($this->input->post('suffix'))
			$suffix = 'pdf_';

        $name = $suffix . 'estimate_no_' . $estimate_no . '_' . $key;

        //$file = $this->mdl_estimates->uploadFile($path, $name, 'estFile');
        $files = bucketScanDir('uploads/' . $path);

		if (!empty($files) && $files) {
			$max = 1;
			foreach($files as $file)
			{
				preg_match('/estimate_no_' . $estimate_no . '.*?_([0-9]{1,})/is', $file, $num);
				if(isset($num[1]) && ($num[1] + 1) > $max)
					$max = $num[1] + 1;
				preg_match('/pdf_estimate_no_' . $estimate_no . '.*?_([0-9]{1,})/is', $file, $num1);
				if(isset($num1[1]) && ($num1[1] + 1) > $max)
					$max = $num[1] + 1;
			}
			$key = $max;
		}
		$name = $suffix . 'estimate_no_' . $estimate_no . '_' . $key;

		$file = $this->mdl_estimates->uploadFile($path, $name, 'estFile');
		if (!$file)
			die(json_encode(array('status' => 'error')));
		else {
		    if($this->input->post('entity') == 'workorders') {
		        $workorder = $this->mdl_workorders->find_by_fields(['estimate_id' => $estimate->estimate_id]);
		        if($workorder) {
                    $wo_pdf_files = $workorder->wo_pdf_files ? json_decode($workorder->wo_pdf_files) : [];
                    $wo_pdf_files[] = 'uploads/' . $path . $name . '.' . $ext;
                    make_notes($estimate->client_id, 'Added File for '. $workorder->workorder_no .' <a href="' . base_url() . 'uploads/' . $path . $name . '.' . $ext . '">' . $name . '.' . $ext . '</a>', 'attachment', $estimate->lead_id);
                    $this->mdl_workorders->update($workorder->id, ['wo_pdf_files' => json_encode($wo_pdf_files)]);
                }
            } elseif ($this->input->post('entity') == 'invoices') {
                $invoice = $this->mdl_invoices->find_by_fields(['estimate_id' => $estimate->estimate_id]);
                if($invoice) {
                    $invoice_pdf_files = $invoice->invoice_pdf_files ? json_decode($invoice->invoice_pdf_files) : [];
                    $invoice_pdf_files[] = 'uploads/' . $path . $name . '.' . $ext;
                    make_notes($estimate->client_id, 'Added File for ' . $invoice->invoice_no . ' <a href="' . base_url() . 'uploads/' . $path . $name . '.' . $ext . '">' . $name . '.' . $ext . '</a>', 'attachment', $estimate->lead_id);
                    $this->mdl_invoices->update($invoice->id, ['invoice_pdf_files' => json_encode($invoice_pdf_files)]);
                }
            } else {
                $estimate_pdf_files = $estimate->estimate_pdf_files ? json_decode($estimate->estimate_pdf_files) : [];
                $estimate_pdf_files[] = 'uploads/' . $path . $name . '.' . $ext;
                make_notes($estimate->client_id, 'Add Estimate File for '. $estimate_no .' <a href="' . base_url() . 'uploads/' . $path . $name . '.' . $ext . '">' . $name . '.' . $ext . '</a>', 'attachment', $estimate->lead_id);
                $this->mdl_estimates_orm->update($estimate->estimate_id, ['estimate_pdf_files' => json_encode($estimate_pdf_files)]);
            }

			$filepath = $file['filepath'];
			die(json_encode(array('status' => 'ok', 'filepath' => $filepath, 'filename' => $file['filename'])));
		}
	}
	function ajax_change_estimator()
	{
		$result['status'] = 'error';
		$estimate_id = $this->input->post('estimate_id');
		$estimator_id = $this->input->post('estimator_id');
		if($estimate_id && $estimator_id)
		{
			$estimate = $this->mdl_estimates->find_by_id($estimate_id);
			$oldUser = $this->mdl_user->find_by_id($estimate->user_id);
			$newUser = $this->mdl_user->find_by_id($estimator_id);
			make_notes($estimate->client_id, 'Estimator for ' . $estimate->estimate_no . ' was changed from "' . ($oldUser->firstname??'') . ' ' . ($oldUser->lastname??'') . '" to "' . $newUser->firstname . ' ' . $newUser->lastname . '"', 'system', $estimate->lead_id);
			$this->mdl_estimates->update($estimate_id, array('user_id' => $estimator_id));
			$result['status'] = 'ok';
		}
		die(json_encode($result));
	}

	function ajax_pdf_file()
	{
		$dir = $this->input->post('name');
		$estimate_id = $this->input->post('estimate_id');
		$estimate = $this->mdl_estimates->find_by_fields(['estimate_id' => $estimate_id]);
		$files = $estimate->estimate_pdf_files ? json_decode($estimate->estimate_pdf_files) : [];
		$key = array_search($dir, $files);
		if($key !== FALSE)
			unset($files[$key]);
		elseif (strpos($dir, 'tree_inventory'))
            array_unshift($files, $dir);
		else
			$files[] = $dir;
		$files = array_values($files);
		$str = json_encode($files);
		$this->mdl_estimates->update($estimate->estimate_id, array('estimate_pdf_files' => $str));
	}

	/*function ajax_rename_file()
	{
		$dir = $this->input->post('name');
		$request = $this->input->post('check');
		$folders = explode('/', $dir);
		$fileinfo = pathinfo($dir);

		if($request === 'true')
		{
			$newName = 'pdf_' . $fileinfo['basename'];
			rename($dir, $fileinfo['dirname'] . '/' . $newName);
		}
		else
		{
			$newName = str_replace('pdf_', '', $fileinfo['basename']);
			rename($dir, $fileinfo['dirname'] . '/' . $newName);
		}
		$link = $fileinfo['dirname'] . '/' . $newName;

		if($newName)
			die(json_encode(array('status' => 'ok', 'filepath' => $link, 'filename' => $newName)));
		else
			die(json_encode(array('status' => 'error')));
	}*/

	function own($date = NULL)
	{
		if(!$date)
			$date = date('Y-m');
		$this->load->model('mdl_sale');

		$estimator_id = request()->user()->id;
		$wdata['estimates.date_created >'] = strtotime($date . '-01');
		$wdata['estimates.date_created <'] = strtotime($date . '-01 last day of this month') + 86400;
		$wdata['estimate_statuses.est_status_confirmed'] = 1;

		$data['title'] = $this->_title . ' - Own Estimates';
		$data['estimators_files'] = $this->mdl_reports->get_estimators_files($estimator_id, 1000, 0, 'estimates.date_created >=' .  strtotime(date('Y-m-01', strtotime($date))) . ' AND estimates.date_created <' . (strtotime(date('Y-m-t', strtotime($date))) + 86400));
		$sale = $this->mdl_sale->get_all(array('sale_date' => date($date . '-01')));
		if(!empty($sale))
			$sale = $sale[0]['sale_amount'];
		else
		{
			$count = $this->mdl_sale->get_all();
			$sale = 0;
			if($count)
				$sale = $count[count($count) - 1]['sale_amount']; //countOk
		}
		$data['goal'] = $sale;
		$data['date'] = $date;
		$company_estimates = array();
		$comObj = $this->mdl_estimates->get_estimates('', '', '', '', 'date_created', '', $wdata);

		if($comObj)
			$company_estimates = $comObj->result_array();
		$total_company = 0;
		if($company_estimates && !empty($company_estimates))
		{
			foreach($company_estimates as $key=>$estimate)
				$total_company += $this->mdl_estimates->get_total_for_estimate($estimate['estimate_id'])['sum'];
		}
		$wdata['user_id'] = $estimator_id;
		$user_estimates = array();
		$estObj = $this->mdl_estimates->get_estimates('', '', '', '', 'date_created', '', $wdata);

		if($estObj)
			$user_estimates = $estObj->result_array();

		$total_estimator = 0;
		$total_estimator_by_statuses = array();
		if($user_estimates && !empty($user_estimates))
		{
			foreach($user_estimates as $key=>$estimate)
			{
				$total_estimator_by_statuses[$estimate['est_status_id']]['sum'] = isset($total_estimator_by_statuses[$estimate['est_status_id']]['sum']) ? $total_estimator_by_statuses[$estimate['est_status_id']]['sum'] : 0;
				$total_estimator_by_statuses[$estimate['est_status_id']]['est_status_name'] = $estimate['est_status_name'];
				$total_estimator_by_statuses[$estimate['est_status_id']]['est_status_declined'] = $estimate['est_status_declined'];
				$total_estimator_by_statuses[$estimate['est_status_id']]['est_status_confirmed'] = $estimate['est_status_confirmed'];
				$totalForEst = $this->mdl_estimates->get_total_for_estimate($estimate['estimate_id']);
				$total_estimator += $totalForEst['sum'];
				$total_estimator_by_statuses[$estimate['est_status_id']]['sum'] += $totalForEst['sum'];
			}
		}
		$data['total_estimator'] = $total_estimator;
		if($sale)
		{
			$data['plan'] = round(100 - (($total_company / $sale) * 100), 2);
			$data['complete_company'] = round(($total_company / $sale) * 100, 2);
			$data['complete_estimator'] = round((($total_estimator / $sale) * 100), 2);
		}
		else
		{
			$data['plan'] = 0;
			$data['complete_company'] = 0;
			$data['complete_estimator'] = 0;
		}
		$data['total_estimator_by_statuses'] = $total_estimator_by_statuses;
		$data['statuses'] = $this->mdl_est_status->with('mdl_est_reason')->get_all();
		unset($wdata['estimate_statuses.est_status_confirmed']);

		$data['count_my_estimates'] = 0;
		$totalSum = 0;
		foreach($data['statuses'] as $key=>$val)
		{

			$symbols = array(' - ', ' ','-');

			$wdata['estimates.status'] = $val->est_status_id;
			$Obj = $this->mdl_estimates->get_estimates('', '', '', '', 'date_created', '', $wdata);
			if($Obj && $Obj->num_rows())
			{
				$data['estimates'][mb_strtolower(str_replace($symbols, '_', $val->est_status_name))] = $Obj->result_array();
				$data['estimates'][mb_strtolower(str_replace($symbols, '_', $val->est_status_name))]['sum'] = 0;

				foreach($Obj->result_array() as $jkey=>$est)
				{
					$data['estimates'][mb_strtolower(str_replace($symbols, '_', $val->est_status_name))]['sum'] += $this->mdl_estimates->get_total_for_estimate($est['estimate_id'])['sum'];
				}
				$totalSum += $data['estimates'][mb_strtolower(str_replace($symbols, '_', $val->est_status_name))]['sum'];
				$data['count_my_estimates'] += count($Obj->result_array()); //countOk
			}
			unset($wdata['estimates.status']);

			if($val->mdl_est_reason)
			{
				foreach($val->mdl_est_reason as $jkey=>$reason)
				{
					$wdata['estimates.estimate_reason_decline'] = $reason->reason_id;
					$Obj = $this->mdl_estimates->get_estimates('', '', '', '', 'date_created', '', $wdata);
					if($Obj)
						$data['declined'][mb_strtolower(str_replace($symbols, '_', $reason->reason_name))] = $Obj->result_array();
				}
				unset($wdata['estimates.estimate_reason_decline']);
			}

		}
		$data['total_sum'] = $totalSum;
		$data['other_estimators'] = round($data['complete_company'] - $data['complete_estimator'], 2);
		$this->load->view('index_own_estimates', $data);
	}

	function ajax_load_estimates()
	{
		$limit = 1000;
		$offset = $this->input->post('offset');
		$estimator_id = $this->input->post('estimator_id');
		if(!$offset)
			die(json_encode(array('status' => 'error')));
		$data['status'] = 'ok';
		$estimators_files = $this->mdl_reports->get_estimators_files($estimator_id, $limit, $offset);
		$data['estimators_files'] = $estimators_files ? $estimators_files->result() : array();
		die(json_encode($data));
	}

	private function do_upload($estimate_no, $client_id, $field = 'files', $types = 'gif|jpg|jpeg|png|pdf|GIF|JPG|JPEG|PNG|PDF')
	{
		multifile_array($field);

		$files = $_FILES;
		$result = array();
		$config['allowed_types'] = $types;
		$config['max_size'] = '5500';
		$config['overwrite'] = TRUE;

		$this->load->library('upload');
		$service = 0;
		$mkNote = FALSE;
		$note = 'Add Estimate File(s) for ' . $estimate_no . ': ';
		foreach ($files as $num => $typeFiles) {

			if ($service != $typeFiles['field']) {
				$service = $typeFiles['field'];
				$key = 1;
			}

			$path = 'uploads/clients_files/' . $client_id . '/estimates/' . $estimate_no . '/' . $typeFiles['field'] . '/';
			if ($key == 1) {
				$files = bucketScanDir($path);
				if ($files && !empty($files)) {
					sort($files, SORT_NATURAL);
					preg_match('/estimate_.*?_([0-9]{1,})\.jpg/is', $files[count($files) - 1], $matches); //countOk
					$key = isset($matches[1]) ? ($matches[1] + 1) : 1;
				}
			}
			$config['upload_path'] = $path;
			$ext = pathinfo($typeFiles['name'], PATHINFO_EXTENSION);
			//echo '<pre>'; var_dump($config['upload_path'], ); die;
			$config['file_name'] = 'estimate_' . $estimate_no . '_' . ($key) /*. '.jpg'; */ . '.' . $ext;

			$this->upload->initialize($config);
			if (!$this->upload->do_upload($num))
				$result[$typeFiles['field']] = array();
			else {
				$result[$typeFiles['field']][] = $config['file_name'];
				$key++;
				$note .= '<br><a href="' . base_url() . $path . $config['file_name'] . '">' . $config['file_name'] . '</a>';
				$mkNote = TRUE;
			}
		}
		if($mkNote)
			make_notes($client_id, $note, 'attachment', intval($estimate_no));
		return $result;
	}

	function ajax_change_status()
	{
		$result['status'] = 'error';
		$id = $this->input->post('id');
		$status = $this->input->post('status');
		$result['cost'] = 0;
		$currentStatus = $this->input->post('name');
		$service = $this->mdl_services_orm->with('mdl_estimates_orm')->get($id);

		$pastStatus = $this->mdl_services_orm->get_service_status(array('services_status_id' => $service->service_status))[0]['services_status_name'];

		// update services status included in a bundle
        $bundleRecords = $this->mdl_estimates_bundles->get_many_by(['eb_bundle_id' => $id]);
        if(!empty($bundleRecords)){
            foreach ($bundleRecords as $bundleRecord) {
                $this->mdl_services_orm->update($bundleRecord->eb_service_id, array('service_status' => $status));
            }
        }

		if($this->mdl_services_orm->update($id, array('service_status' => $status)))
		{

			$result['status'] = 'ok';

			$service = $this->mdl_services_orm->with('mdl_estimates_orm')->get($id);
			$this->mdl_estimates->update_estimate_balance($service->estimate_id);
			make_notes($service->mdl_estimates_orm->client_id, 'Change status for '. $service->mdl_estimates_orm->estimate_no .' from "' . $pastStatus . '" to "' . $currentStatus . '"', 'system', $service->mdl_estimates_orm->lead_id);
			if($status == 1)
			{
				$result['cost'] = - $service->service_price;
			}
			else
				$result['cost'] = $service->service_price;

			$intData = $this->mdl_invoices->update_all_invoice_interes($service->estimate_id);

			if(is_array($intData))
				$result['interests'] = $intData;
			$estimate_services = $this->mdl_services_orm->get_many_by(array('estimate_id' => $service->estimate_id, 'service_status <>' => 1, 'service_status  <>' => 2));
			$result['finish'] = count($estimate_services); //countOk
            //create a new job for synchronization in QB
            $invoice = $this->mdl_invoices->find_by_field(['invoices.estimate_id' => $service->estimate_id]);
            if (is_object($invoice))
                pushJob('quickbooks/invoice/syncinvoiceinqb', serialize(['id' => $invoice->id, 'qbId' => $invoice->invoice_qb_id]));
		}
		die(json_encode($result));
	}

    function completeServices(){
	    $request = request();
        $id = collect($request->input('services'))->map(fn($item) => (int)$item)->toArray();

        $toStatus = EstimatesServicesStatus::find(2);
        $estimate = Estimate::with('workorder')->find($request->input('estimate_id'));
        $services = EstimatesService::with(['bundle.estimate_service', 'status'])->whereIn('id', $id)->get();

        $services->map(function ($service) use ($toStatus, $estimate){

            $service->bundle->map(function ($bundle) use ($toStatus){
                $bundle->estimate_service->service_status = $toStatus->services_status_id;
                $bundle->estimate_service->save();
            });
            $service->service_status = $toStatus->services_status_id;
            $service->save();

            make_notes($estimate->client_id, 'Change status for '. $estimate->estimate_no .' from "' . $service->status->services_status_name . '" to "' . $toStatus->services_status_name . '"', 'system', $estimate->lead_id);
        });

        $interests = $this->mdl_invoices->update_all_invoice_interes($estimate->estimate_id);
        $finish = EstimatesService::newService()->where('estimate_id', '=', $estimate->estimate_id)->count();
        $invoice = Invoice::where('estimate_id', '=', $estimate->estimate_id)->get();

        if (!empty($response['invoice']))
            pushJob('quickbooks/invoice/syncinvoiceinqb', serialize(['id' => $invoice->id, 'qbId' => $invoice->invoice_qb_id]));

        return $this->response([
            'estimate'  =>  $estimate,
            'invoice'   =>  $invoice,
            'finish'    =>  $finish,
            'interests' =>  $interests
        ]);
    }
	/***********************ESTIMATE SCHEME ITEMS**************************/

	function scheme_items()
	{
		if (!isSystemUser()) {
			show_404();
		}
		$data['title'] = "Estimate Scheme Items";

		$this->load->view('index_scheme_items', $data);
	}

	function ajax_save_file_item()
	{
		if (request()->user()->user_type != "admin") {
			show_404();
		}
		$ext = pathinfo($_FILES['estFile']['name'], PATHINFO_EXTENSION);

		$name = explode('.', $_FILES['estFile']['name'])[0];
		$path = $this->input->post('path');
		$types = 'gif|jpg|jpeg|png|pdf|GIF|JPG|JPEG|PNG|PDF';
		$file = $this->mdl_estimates ->uploadFile($path, $name, 'estFile', $types, FALSE);
		if (!$file)
			die(json_encode(array('status' => 'error')));
		else {
			$filepath = $file['filepath'];
			die(json_encode(array('status' => 'ok', 'filepath' => $filepath, 'filename' => $file['filename'])));
		}
		die(json_encode(array('status' => 'ok')));
	}
	function ajax_delete_scheme_icon()
	{
		if (request()->user()->user_type != "admin") {
			show_404();
		}
		$name = $this->input->post('name');
		$dir = 'uploads/scheme_items/';
		if (bucket_unlink($dir . $name))
			die(json_encode(array('status' => 'ok')));

		die(json_encode(array('status' => 'error')));
	}

	/***********************ESTIMATE SCHEME ITEMS**********************/

	/***********************SERVICE STATUS******************************/
	public function service_status()
	{
		if (!isSystemUser()) {
			show_404();
		}

		$data['title'] = "Service Status";
		//get employees
		$data['statuses'] = $this->mdl_services_orm->get_service_status();

		$this->load->view('index_service_status', $data);
	}

	function ajax_save_service_status()
	{

		if ($this->session->userdata('user_type') != "admin" && $this->session->userdata('WO_STS') != 1) {
			show_404();
		}

		//save_status
		$id = NULL;
		if($this->input->post('status_id'))
			$id = intval($this->input->post('status_id'));
		$data['services_status_name'] = $this->input->post('status_name');

		$this->mdl_services_orm->save_status($data, $id);
		die(json_encode(array('status' => 'ok', 'msg' => 'Done!')));
	}

	function ajax_service_delete_status()
	{
		if ($this->session->userdata('user_type') != "admin" && $this->session->userdata('WO_STS') != 1) {
			show_404();
		}
		$id = $this->input->post('status_id');
		if ($id != '')
			$this->mdl_services_orm->delete_status($id);
		die(json_encode(array('status' => 'ok')));
	}
	/***********************SERVICE STATUS******************************/
	/***********************SERVICES******************************/
	public function services()
	{
		if (request()->user()->user_type != "admin" && !is_cl_permission_all()) {
			show_404();
		}
		$this->load->model('mdl_services');
		$this->load->model('mdl_vehicles');

		$data['title'] = 'Estimate Services';
		$order_by['service_status'] = 'DESC';
		$order_by['service_priority'] = 'ASC';
		$services = $this->mdl_services->order_by($order_by)->get_many_by(array('service_parent_id' => NULL, 'is_product' => 0, 'is_bundle' => 0));
        $data['services'] = setFavouriteShortcut($services);

		$data["tools"] = $this->mdl_vehicles->get_many_by(array('vehicle_trailer' => 2, 'vehicle_disabled' => NULL));
		$data["vehicles"] = $this->mdl_vehicles->get_many_by(array('vehicle_trailer IS NULL', 'vehicle_disabled' => NULL));
		$data["trailers"] = $this->mdl_vehicles->get_many_by(array('vehicle_trailer' => 1, 'vehicle_disabled' => NULL));
        $data['access_token'] = $this->mdl_settings_orm->get_by('stt_key_name', 'accessTokenKey')->stt_key_value;
        $data['categories'] = Category::whereNull('category_parent_id')->with(['categoriesWithServices', 'services'])->orderBy('category_active', 'DESC')->orderBy('category_priority', 'ASC')->get()->toArray();
        $categories = Category::whereNull('category_parent_id')->where('category_active', 1)->with('categories')->orderBy('category_active', 'DESC')->orderBy('category_priority', 'ASC')->get()->toArray();
        $data['categoriesWithChildren'] = getCategories($categories);
        $classes = QBClass::where(['class_parent_id' => null, 'class_active' => 1])->with('classesWithoutInactive')->get();
        $data['classes'] = [];
        if(!empty($classes->toArray())) {
            $data['classes'] = getClasses($classes->toArray());
        }
        $crews = $this->mdl_crews->get_crews(array('crew_status' => 1, 'crew_id >' => 0), 'crew_name');
        $data['crews'] = [];
        if(!empty($crews))
            $data['crews'] = $crews;

		$this->load->view('index_services', $data);
	}
	function ajax_estimate_priority_service()
	{
		if (request()->user()->user_type != "admin" && !is_cl_permission_all()) {
			show_404();
		}
		$data = $this->input->post('data');
		if (empty($data))
			die(json_encode(array('status' => 'error')));
		foreach ($data as $key => $val) {
			if ($val)
				$updateBatch[] = array('service_id' => $key, 'service_priority' => $val);
		}
		if (empty($updateBatch) || empty($updateBatch))
			die(json_encode(array('status' => 'error')));
		$this->load->model('mdl_services');
		if ($this->mdl_services->update_priority($updateBatch))
			die(json_encode(array('status' => 'ok')));
		die(json_encode(array('status' => 'error')));
	}

	function ajax_save_service()
	{
		if (request()->user()->user_type != "admin" && !is_cl_permission_all()) {
			show_404();
		}
		$this->load->model('mdl_services');
		$service_id = $this->input->post('service_id');
		$category_id = $this->input->post('category_id');
		$is_favourite = $this->input->post('is_favourite');
        $favouriteIcon = $this->input->post('favourite_icon');
        $service_is_collapsed = $this->input->post('service_is_collapsed');
        $crews = $this->input->post('crews');

        $services = Service::where('service_name', $this->input->post('service_name'))->pluck('service_id')->toArray();
        if($services && ($service_id && !in_array($service_id, $services) || !$service_id && !empty($services))){
            $this->errorResponse('This name already exists.');
            return;
        }

        if(!empty($crews))
            $data['service_default_crews'] = json_encode($crews);
        else
            $data['service_default_crews'] = null;
        $data['service_is_favourite'] = 0;
        $data['service_is_collapsed'] = 1;
        if(empty($service_is_collapsed))
            $data['service_is_collapsed'] = 0;
        if(!empty($is_favourite)) {
            $data['service_favourite_icon'] = $favouriteIcon;
            $data['service_is_favourite'] = 1;
        }
        $data['service_category_id'] = !empty($category_id) ? $category_id : 1;
        $classId = $this->input->post('class_id');

        if(!empty($classId)){
            $data['service_class_id'] = $classId;
        } else {
            $data['service_class_id'] = null;
        }
		$data['service_parent_id'] = $this->input->post('service_parent');
		if(!$data['service_parent_id'])
			$data['service_parent_id'] = NULL;
		$data['service_name'] = strip_tags($this->input->post('service_name', TRUE));
		$data['service_description'] = strip_tags($this->input->post('service_description', TRUE));
		$data['service_markup'] = floatval(str_replace(',', '.', $this->input->post('service_markup', TRUE)));
		$data['service_status'] = 1;

		$vehicle_ids = $this->input->post('service_vehicles');
		$vehicle_opt = $this->input->post('vehicles_options');
		$trailer_ids = $this->input->post('service_trailers');
		$trailer_opt = $this->input->post('trailers_options');
		$service_tools = $this->input->post('service_tools');
		$tools_option = $this->input->post('tools_option');
		if($vehicle_ids && !empty($vehicle_ids))
		{
			$i = 0;
			$checkAttachments = false;
			foreach($vehicle_ids as $k=>$v)
			{
				if($v != '')
				{
                    $checkAttachments = true;
					$data['service_attachments'][$i]['vehicle_id'] = $v;
					$data['service_attachments'][$i]['vehicle_option'] = trim($vehicle_opt[$k]);
					$data['service_attachments'][$i]['trailer_id'] = $trailer_ids[$k];
					$data['service_attachments'][$i]['trailer_option'] = trim($trailer_opt[$k]);
					if(isset($tools_option[$k]))
					{
						foreach($tools_option[$k] as $jkey=>$options)
						{
							$data['service_attachments'][$i]['tool_id'][] = $jkey;
							$data['service_attachments'][$i]['tools_option'][] = $options;
						}
					}
					$i++;
				} else {
                    $data['service_attachments'][$i] = null;
                }
			}
			if($checkAttachments === false)
                $data['service_attachments'] = null;
			if(isset($data['service_attachments']) && !empty($data['service_attachments']))
				$data['service_attachments'] = json_encode($data['service_attachments']);
		}
		if ($service_id) {
			$this->mdl_services->update($service_id, $data);
			if($data['service_parent_id'])
			{
				$child = $this->mdl_services->get_many_by(array('service_parent_id' =>$service_id));
				if(!empty($child))
				{
					foreach($child as $key=>$val)
					{
						$priority = $this->mdl_services->record_count() + 1;
						$this->mdl_services->update($val->service_id, array('service_parent_id' => NULL, 'service_priority' => $priority));
					}
				}
			}
			//create a new job for synchronization in QB
            $service = $this->mdl_services->get($service_id);
            pushJob('quickbooks/item/syncserviceinqb', serialize(['id' => $service_id, 'qbId' => $service->service_qb_id]));

			die(json_encode(array('status' => 'ok')));
		}
		if($data['service_parent_id'])
			$data['service_priority'] = 0;
		else
			$data['service_priority'] = $this->mdl_services->record_count() + 1;
        $service_id = $this->mdl_services->insert($data);

		//create a new job for synchronization in QB
        pushJob('quickbooks/item/syncserviceinqb', serialize(['id' => $service_id, 'qbId' => '']));
		die(json_encode(array('status' => 'ok')));
	}

	function ajax_delete_service()
	{
		if (request()->user()->user_type != "admin" && !is_cl_permission_all()) {
			show_404();
		}
		$this->load->model('mdl_services');
		$service_id = $this->input->post('service_id');
		$status = $this->input->post('status') ? 1 : 0;
		if ($service_id)
			$this->mdl_services->update($service_id, array('service_status' => $status));
		$child = $this->mdl_services->get_many_by(array('service_parent_id' =>$service_id));
		if(!empty($child))
		{
			foreach($child as $key=>$val)
				$this->mdl_services->update($val->service_id, array('service_status' => $status));
		}

		//create a new job for synchronization in QB
        pushJob('quickbooks/item/syncserviceinqb', serialize(['id' => $service_id, 'qbId' => '']));
		die(json_encode(array('status' => 'ok')));
	}
	/***********************SERVICES******************************/

	/***********************Estimate Equipment*******************/
	/*
	public function estimate_equipment()
	{
		$this->load->model('mdl_est_equipment');
		$data['title'] = "Estimate Equipment";
		$data['equipment'] = $this->mdl_est_equipment->order_by('eq_status', 'DESC')->order_by('eq_weight')->get_all();
		$this->load->view('index_estimate_equipment', $data);
	} */

	function ajax_save_estimate_equipment()
	{
		if(!$this->input->post('eq_name', TRUE))
			die(json_encode(array('status' => 'error', 'errors' => array('eq_name' => 'Item Name Is Required'))));
		$this->load->model('mdl_est_equipment');
		$id = $this->input->post('eq_id');
		$data['eq_name'] = strip_tags($this->input->post('eq_name', TRUE));
		if ($id) {
			$this->mdl_est_equipment->update($id, $data);
			die(json_encode(array('status' => 'ok')));
		}
		$data['eq_weight'] = 1;
		$this->mdl_est_equipment->insert($data);
		die(json_encode(array('status' => 'ok')));
	}

	function ajax_delete_estimate_equipment()
	{
		$this->load->model('mdl_est_equipment');
		$id = $this->input->post('eq_id');
		$status = intval($this->input->post('status'));
		if($id)
			$this->mdl_est_equipment->update($id, array('eq_status' => $status));
		die(json_encode(array('status' => 'ok')));
	}

	function ajax_priority_equipment()
	{
		$this->load->model('mdl_est_equipment');
		$data = $this->input->post('data');
		if (empty($data))
			die(json_encode(array('status' => 'error')));
		foreach ($data as $key => $val) {
			if ($val)
				$this->mdl_est_equipment->update($key, array('eq_weight' => $val));
		}
		die(json_encode(array('status' => 'ok')));
	}
	/***********************Estimate Equipment*******************/

	/***********************Estimate Statuses******************/
	public function estimate_status()
	{
		if ($this->session->userdata('user_type') != "admin") {
			show_404();
		}
		$this->load->model('mdl_est_status');
		$data['title'] = "Estimate Status";

		//get employees
		$data['statuses'] = $this->mdl_est_status->with('mdl_est_reason')->order_by('est_status_priority')->get_all();
		$data['isset_default'] = FALSE;
		$data['isset_sent'] = FALSE;
		foreach($data['statuses'] as $status)
		{
			if($status->est_status_default)
				$data['isset_default'] = TRUE;
			if($status->est_status_sent)
				$data['isset_sent'] = TRUE;
		}
		$this->load->view('index_estimate_status', $data);
	}

	function ajax_save_estimate_status()
	{
		if ($this->session->userdata('user_type') != "admin") {
			show_404();
		}
		$this->load->model('mdl_est_status');
		$this->load->model('mdl_est_reason');
		$id = (int)$this->input->post('status_id');
		$data['est_status_name'] = strip_tags($this->input->post('status_name', TRUE));
		$data['est_status_priority'] = (int)$this->input->post('status_priority');
        /*$data['est_status_declined'] = (int)$this->input->post('status_declined');
        $data['est_status_default'] = (int)$this->input->post('status_default');
        $data['est_status_confirmed'] = (int)$this->input->post('status_confirmed');
        $data['est_status_sent'] = (int)$this->input->post('status_sent');*/

		if ($id) {
			$status = $this->mdl_est_status->get($id);
			$this->mdl_est_status->update($id, $data);

			if((int)$status->est_status_declined)
			{
				$reasons = $this->input->post('status_reason');
				if($reasons && is_array($reasons)) {
                    foreach($reasons as $key=>$reason)
                    {
                        $active = 1;
                        if($reason['reason_active'] == 'true')
                            $active = 0;
                        if(isset($reason['reason_id']))
                            $this->mdl_est_reason->update($reason['reason_id'], array('reason_name' => $reason['reason_name'], 'reason_est_status_id' => $id, 'reason_active' => $active));
                        else
                            $this->mdl_est_reason->insert(array('reason_name' => $reason['reason_name'], 'reason_est_status_id' => $id, 'reason_active' => $active));
                    }
                }
			}
			die(json_encode(array('status' => 'ok')));
		}
		$id = $this->mdl_est_status->insert($data);
		die(json_encode(array('status' => 'ok')));
	}

	function ajax_delete_estimate_status()
	{
		if ($this->session->userdata('user_type') != "admin")
			show_404();

		$this->load->model('mdl_est_status');
		$id = $this->input->post('status_id');
		$status = $this->input->post('status');
		if ($id != '')
			$this->mdl_est_status->update($id, array('est_status_active' => $status));
		die(json_encode(array('status' => 'ok')));
	}
	/***********************END Estimate Statuses******************/

	public function delete($estimate_id)
	{

		if ($this->session->userdata('user_type') != "admin") {
			show_404();
		} else {

			//Get Client_id:
			$id = $estimate_id;
			$estimate = $this->mdl_estimates->find_by_id($id);

			if(!$estimate)
                show_404();

			$client_id = $estimate->client_id;
			$this->load->helper('estimates_helper');
			$wo = $this->mdl_workorders->get_workorders('', '', '', '', array('workorders.estimate_id' => $id));
			if($wo)
				$wo = $wo->row_array();

			if($this->mdl_estimates->delete_estimate($id)) {
				$this->load->model('mdl_followups');
				$fuRowPost = $this->mdl_followups->get_by(['fu_module_name' => 'estimates', 'fu_item_id' => $id, 'fu_status' => 'postponed']);
				$fuRowNew = $this->mdl_followups->get_by(['fu_module_name' => 'estimates', 'fu_item_id' => $id, 'fu_status' => 'new']);
				//echo '<pre>'; var_dump($fuRowNew); die;
				if($fuRowNew && !empty($fuRowNew))
					$this->mdl_followups->update($fuRowNew->fu_id, ['fu_status' => 'canceled', 'fu_comment' => 'Canceled ' . date('Y-m-d H:i:s') . ' - estimate was deleted']);
				elseif($fuRowPost && !empty($fuRowPost))
					$this->mdl_followups->update($fuRowPost->fu_id, ['fu_status' => 'canceled', 'fu_comment' => 'Canceled ' . date('Y-m-d H:i:s') . ' - estimate was deleted']);


				$clientsPath = 'uploads/clients_files/' . $client_id . '/estimates/' . $estimate->estimate_no;
				if(isset($client_id) && is_dir($clientsPath))
					recursive_rm_files($clientsPath);
				/*********DELETE PAYMENTS FILES*****************/
				$paymentsPath = 'uploads/payment_files/' . $client_id . '/' . $estimate->estimate_no;
				if(isset($client_id) && is_dir($paymentsPath))
					recursive_rm_files($paymentsPath);


				//delete wo
				//delete invoice
				$this->mdl_invoices->delete_invoice($id);
				if(isset($wo['id']) && $wo['id'])
					$this->mdl_workorders->delete_workorder_new($wo['id']);

				$this->load->model('mdl_leads_status');
				$declineStatus = $this->mdl_leads_status->get_by(['lead_status_declined' => 1]);

				$this->mdl_leads->update_leads(['lead_status_id' => $declineStatus->lead_status_id], ['lead_id' => $estimate->lead_id]);
				$this->mdl_leads->status_log(['status_type' => 'lead', 'status_item_id' => $estimate->lead_id, 'status_value' => $declineStatus->lead_status_id, 'status_date' => time()]);
				$fuRowPost = $this->mdl_followups->get_by(['fu_module_name' => 'leads', 'fu_item_id' => $estimate->lead_id, 'fu_status' => 'postponed']);
				$fuRowNew = $this->mdl_followups->get_by(['fu_module_name' => 'leads', 'fu_item_id' => $estimate->lead_id, 'fu_status' => 'new']);
				//echo '<pre>'; var_dump($fuRowNew); die;
				if($fuRowNew && !empty($fuRowNew))
					$this->mdl_followups->update($fuRowNew->fu_id, ['fu_status' => 'canceled', 'fu_comment' => 'Canceled ' . date('Y-m-d H:i:s') . ' - estimate was deleted']);
				elseif($fuRowPost && !empty($fuRowPost))
					$this->mdl_followups->update($fuRowPost->fu_id, ['fu_status' => 'canceled', 'fu_comment' => 'Canceled ' . date('Y-m-d H:i:s') . ' - estimate was deleted']);

				$link = base_url($client_id);
				$mess = message('success', 'Estimate, Workorder, Invoice were successfully deleted!');
				$this->session->set_flashdata('user_message', $mess);
				redirect($link);
			}
		}
	}

	function ajax_presave_scheme()
	{
		$lead_id = $this->input->post('lead_id');
		$client_id = $this->input->post('client_id');
		$estimate_id = $this->input->post('estimate_id');
		$html = $this->input->post('html');
		$image = $this->input->post('image');

		if($image)
		{
			$estimate_scheme_data = str_replace('[removed]', '', $image);
			if($estimate_scheme_data == $image)
				$estimate_scheme_data = explode(',', $image)[1];

			if($estimate_id) {
				$estimate_data = $this->mdl_estimates->find_by_id($estimate_id);
				$estimate_pdf_files = $estimate_data->estimate_pdf_files ? json_decode($estimate_data->estimate_pdf_files) : [];
				$estimate_no = str_pad($lead_id, 5, '0', STR_PAD_LEFT) . "-E";

				$schemePath = 'uploads/clients_files/' . $client_id . '/estimates/' . $estimate_no;
				$schemePath .= '/pdf_estimate_no_' . $estimate_no . '_scheme.png';

				array_unshift($estimate_pdf_files, ltrim($schemePath, './'));
				$estimate_pdf_files = array_unique($estimate_pdf_files);
				$source['html'] = $html;
				$source['link'] = 'uploads/tmp/' . $client_id . '/source/' .  str_pad($lead_id, 5, '0', STR_PAD_LEFT) . '_scheme.png';

				$this->mdl_estimates_orm->update($estimate_id, array('estimate_scheme' => json_encode($source), 'estimate_pdf_files' => json_encode($estimate_pdf_files)));
			}
			else {
				$schemePath = 'uploads/tmp/' . $client_id;
				if($this->input->post('source')) {
					$schemePath .= '/source';
				}
				else {
					$source['html'] = $html;
					$source['link'] = ltrim($schemePath . '/source/' .  str_pad($lead_id, 5, '0', STR_PAD_LEFT) . '_scheme.png', '.');
					bucket_write_file($schemePath . '/' .  str_pad($lead_id, 5, '0', STR_PAD_LEFT) . '_source_html', json_encode($source));
					$result['source_html'] = $schemePath . '/' .  str_pad($lead_id, 5, '0', STR_PAD_LEFT) . '_source_html';
                    //delete app elements
                    $elementsPath = 'uploads/tmp/' . $client_id . '/' . str_pad($lead_id, 5, '0', STR_PAD_LEFT) . '_scheme_elements';
                    if(is_bucket_file($elementsPath))
                        bucket_unlink($elementsPath);
				}
				$schemePath .=  '/' .  str_pad($lead_id, 5, '0', STR_PAD_LEFT) . '_scheme.png';
			}

			file_put_contents(sys_get_temp_dir() . DIRECTORY_SEPARATOR  .  str_pad($lead_id, 5, '0', STR_PAD_LEFT) . '_scheme.png', base64_decode($estimate_scheme_data));

            $config['image_library'] = 'gd2';
            $config['source_image']	= sys_get_temp_dir() . DIRECTORY_SEPARATOR .  str_pad($lead_id, 5, '0', STR_PAD_LEFT) . '_scheme.png';
            $config['quality'] = 50;
            $config['maintain_ratio'] = TRUE;
            $config['width'] = 800;
            $config['height'] = 500;
            $this->load->library('image_lib', $config);
            $this->image_lib->resize();

            bucket_move(sys_get_temp_dir() . DIRECTORY_SEPARATOR .  str_pad($lead_id, 5, '0', STR_PAD_LEFT) . '_scheme.png', $schemePath);//, base64_decode($estimate_scheme_data), ['ContentType' => 'image/png']);
            @unlink(sys_get_temp_dir() . DIRECTORY_SEPARATOR . str_pad($lead_id, 5, '0', STR_PAD_LEFT) . '_scheme.png');
			$result['path'] = base_url(ltrim($schemePath, './')) . '?' . time();
			die(json_encode($result));
		}
	}

	function ajax_estimate_draft_field() {
        $this->estimateactions->setEstimateDraftField($this->input->post());

        $lead_id = $this->input->post('lead_id');
        if(!empty($lead_id)) {
            $lead = new $this->leadsactions($lead_id);
            $the_lead = $lead->getLead();
            if($the_lead && !empty($the_lead->lead_status_default)){
                $draft_status = LeadStatus::draft()->first();
                if(!empty($draft_status)) {
                    $status_id = $draft_status->lead_status_id;
                    $lead->setStatus($status_id);
                }
            }
        }

        return;
		$client_id = $this->input->post('client_id');
		$lead_id = $this->input->post('lead_id');
		$field = $this->input->post('field');
		$fieldValue = $this->input->post('value');

		$filename = $lead_id . '_estimate_draft';
		$dir = 'uploads/tmp/';
		$subDirs = [$client_id];

		foreach ($subDirs as $key => $value) {
			$dir .= $value . '/';
		}

		$fileFullPath = $dir . $filename;

		$draftData = json_decode(bucket_read_file($fileFullPath), TRUE);
		if($field != 'array'){
			$draftData[$field] = $fieldValue;
		} else {
			foreach($fieldValue as $fval){
			    if(isset($fval['field'])) {
                    $draftData[$fval['field']] = $fval['value'];
                }
			}
		}

		if(!isset($draftData['discount_percents']))
			$draftData['discount_percents'] = 0;

		bucket_write_file($fileFullPath, json_encode($draftData));

	}

    function ajax_estimate_draft_files() {
        $this->estimateactions->setEstimateDraftFiles($this->input->post());
        return;
        $client_id = $this->input->post('client_id');
        $lead_id = $this->input->post('lead_id');
        $service_id = $this->input->post('service_id');
        $files = $this->input->post('files');

        $filename = $lead_id . '_estimate_draft';
        $dir = 'uploads/tmp/';
        $subDirs = [$client_id];

        foreach ($subDirs as $key => $value) {
            $dir .= $value . '/';
        }

        $fileFullPath = $dir . $filename;

        $draftData = json_decode(bucket_read_file($fileFullPath), TRUE);
        $draftData['pre_uploaded_files'] = $draftData['pre_uploaded_files'] ?? [];
        $draftData['pre_uploaded_files'][$service_id] = $files;

        bucket_write_file($fileFullPath, json_encode($draftData));

    }

	function ajax_estimate_delete_draft_service() {
        $this->estimateactions->deleteEstimateDraftItem($this->input->post());
        return;
		$client_id = $this->input->post('client_id');
		$lead_id = $this->input->post('lead_id');
		$service_id = $this->input->post('service_id');

		$filename = $lead_id . '_estimate_draft';
		$dir = 'uploads/tmp/';
		$subDirs = [$client_id];

		foreach ($subDirs as $key => $value) {
			$dir .= $value . '/';
		}

		$fileFullPath = $dir . $filename;

		$draftData = json_decode(bucket_read_file($fileFullPath), TRUE);

		foreach ($draftData as $key => $value) {
			if(isset($draftData[$key][$service_id]))
				unset($draftData[$key][$service_id]);
//				unset($draftData[$key]);
		}

		bucket_write_file($fileFullPath, json_encode($draftData));
	}

	function ajax_estimate_draft_service() {
        $this->estimateactions->setEstimateDraftService($this->input->post());
        return;
		$client_id = $this->input->post('client_id');
		$lead_id = $this->input->post('lead_id');
		$service_tmp_id = $this->input->post('service_id');
		$serviceData = $this->input->post()??[];
		unset($serviceData['client_id'], $serviceData['lead_id'], $serviceData['service_id']);

		$filename = $lead_id . '_estimate_draft';
		$dir = 'uploads/tmp/';
		$subDirs = [$client_id];

		foreach ($subDirs as $key => $value) {
			$dir .= $value . '/';
		}

		$fileFullPath = $dir . $filename;
		$draftData = json_decode(bucket_read_file($fileFullPath), TRUE);
		foreach ($serviceData as $key => $value) {

			if(!isset($draftData[$key]))
				$draftData[$key] = [];

			$draftData[$key][$service_tmp_id] = $value[$service_tmp_id];
		}

		if(!isset($serviceData['service_permit'][$service_tmp_id]))
			$draftData['service_permit'][$service_tmp_id] = 0;
		if(!isset($serviceData['service_exemption'][$service_tmp_id]))
			$draftData['service_exemption'][$service_tmp_id] = 0;
		if(!isset($serviceData['service_crew'][$service_tmp_id]))
			$draftData['service_crew'][$service_tmp_id] = [];
		if(!isset($serviceData['service_equipment'][$service_tmp_id]))
			$draftData['service_equipment'][$service_tmp_id] = [];
		if(!isset($serviceData['service_front_space'][$service_tmp_id]))
			$draftData['service_front_space'][$service_tmp_id] = 0;
		if(!isset($serviceData['service_cleanup'][$service_tmp_id]))
			$draftData['service_cleanup'][$service_tmp_id] = 0;
		if(!isset($serviceData['service_disposal_brush'][$service_tmp_id]))
			$draftData['service_disposal_brush'][$service_tmp_id] = 0;
		if(!isset($serviceData['service_disposal_wood'][$service_tmp_id]))
			$draftData['service_disposal_wood'][$service_tmp_id] = 0;
		if(!isset($serviceData['service_access'][$service_tmp_id]))
			$draftData['service_access'][$service_tmp_id] = 0;
		if(!isset($serviceData['service_client_home'][$service_tmp_id]))
			$draftData['service_client_home'][$service_tmp_id] = 0;
		//SETUPS
		if(!isset($serviceData['service_vehicle'][$service_tmp_id]))
			$draftData['service_vehicle'][$service_tmp_id] = [];
		if(!isset($serviceData['service_trailer'][$service_tmp_id]))
			$draftData['service_trailer'][$service_tmp_id] = [];
		if(!isset($serviceData['vehicle_option'][$service_tmp_id]))
			$draftData['vehicle_option'][$service_tmp_id] = [];
		if(!isset($serviceData['trailer_option'][$service_tmp_id]))
			$draftData['trailer_option'][$service_tmp_id] = [];
		if(!isset($serviceData['service_tools'][$service_tmp_id]))
			$draftData['service_tools'][$service_tmp_id] = [];
		if(!isset($serviceData['pre_uploaded_files'][$service_tmp_id]))
			$draftData['pre_uploaded_files'][$service_tmp_id] = [];
		if(!isset($serviceData['pre_uploaded_files'][$service_tmp_id]))
			$draftData['pre_uploaded_files'][$service_tmp_id] = [];
		//SETUPS END
		bucket_write_file($fileFullPath, json_encode($draftData));
	}

	function search_estimates()
	{
		$data['title'] = 'Search Result';
		$date = $services = $estimate_price = $service_price = $workers = $andWorkers = $orWorkers = [];
		$user_id = $status = $note = '';
		//$this->mdl_estimates->get_estimates();
		if($this->input->post('search_estimate_from') !== FALSE && $this->input->post('search_estimate_from') !== '')
		{
		    $from = DateTime::createFromFormat(getDateFormat(), $this->input->post('search_estimate_from'));
			$date['date_created >='] = strtotime($from->format('Y-m-d'). ' 00:00:00');
			$data['from'] = $from->format('Y-m-d');
		}
		if($this->input->post('search_estimate_to') !== FALSE && $this->input->post('search_estimate_to') !== '')
		{
		    $to = DateTime::createFromFormat(getDateFormat(), $this->input->post('search_estimate_to'));
			$date['date_created <='] = strtotime($to->format('Y-m-d') . ' 23:59:59');
			$data['to'] = $to->format('Y-m-d');
		}

		if($this->input->post('search_estimate_price_from') && $this->input->post('search_estimate_price_from') != '')
			$data['search_estimate_price_from'] = $estimate_price['>='] = $this->input->post('search_estimate_price_from');

		if($this->input->post('search_estimate_price_to') !== FALSE && $this->input->post('search_estimate_price_to') !== '')
			$data['search_estimate_price_to'] = $estimate_price['<='] = $this->input->post('search_estimate_price_to');

		if($this->input->post('search_service_price_from') && $this->input->post('search_service_price_from') != '')
			$data['search_service_price_from'] = $service_price['service_price >='] = $this->input->post('search_service_price_from');

		if($this->input->post('search_service_price_to') !== FALSE && $this->input->post('search_service_price_to') !== '')
			$data['search_service_price_to'] = $service_price['service_price <='] = $this->input->post('search_service_price_to');

		if($this->input->post('search_estimator') !== FALSE && $this->input->post('search_estimator') !== '')
			$data['search_estimator'] = $user_id = $this->input->post('search_estimator');

		if($this->input->post('search_status') !== FALSE && $this->input->post('search_status') !== '')
			$data['search_status'] = $status = $this->input->post('search_status');

		if($this->input->post('search_estimate_description') !== FALSE && $this->input->post('search_estimate_description') !== '')
			$data['search_desc'] = $note = $this->input->post('search_estimate_description');

		if($this->input->post('search_workers') !== FALSE && !empty($this->input->post('search_workers')) && $this->input->post('orand') == 'and') {
			$data['search_workers'] = $andWorkers = $this->input->post('search_workers'); //
			$data['orand'] = $this->input->post('orand');
		}

		if($this->input->post('search_workers') !== FALSE && !empty($this->input->post('search_workers')) && $this->input->post('orand') == 'or') {
			$data['search_workers'] = $orWorkers = $this->input->post('search_workers'); //
			$data['orand'] = $this->input->post('orand');
		}

		if($this->input->post('search_service_type') && !empty($this->input->post('search_service_type')))
			$data['search_service_type'] = $services = $this->input->post('search_service_type'); // service_id

		$config["per_page"] = 1000;

		$page = 1;
		if($this->uri->segment(3))
			$page = intval($this->uri->segment(3));

		$page = (!$page) ? 1 : $page;

		$start = $page - 1;
		$start = $start * $config["per_page"];
		$limit = $config["per_page"];
		$data['estimates'] = $this->mdl_estimates->global_search_estimates($date, $services, $estimate_price, $service_price, $user_id, $andWorkers, $orWorkers, $status, $note, $limit, $start)->result();


		$data['links'] = NULL;//$this->pagination->create_links();



		$data['estimators'] = $this->mdl_estimates->get_active_estimators();
		$data['services'] = $this->mdl_services->find_all(array('service_status' => 1), 'service_priority');
		$data['statuses'] = $this->mdl_est_status->with('mdl_est_reason')->get_many_by(array('est_status_active' => 1));
		$users = $this->mdl_user->get_usermeta(array('emp_status' => 'current', 'emp_feild_worker' => 1, 'active_status' => 'yes'));
        if ($users->num_rows())
			$data['workers'] = $users->result_array();
		//Load view:

		//
		$this->load->view("global_search_estimates", $data);
	}

	function estimate_equipment()
	{
		//$this->output->enable_profiler(TRUE);
		$this->load->model('mdl_vehicles');
		$this->load->model('mdl_vehicles_relations');
		// Set title
		$data['title'] = "Equipments";
		// Set menu status
		$data['menu_equipments'] = "active";

		//$data["equipments"] = $this->mdl_vehicles->get_all();
		$data["tools"] = $this->mdl_vehicles->get_many_by(array('vehicle_trailer' => 2, 'vehicle_disabled' => NULL));
		$data["vehicles"] = $this->mdl_vehicles->get_many_by(array('vehicle_trailer IS NULL', 'vehicle_disabled' => NULL));
		$data["trailers"] = $this->mdl_vehicles->get_many_by(array('vehicle_trailer' => 1, 'vehicle_disabled' => NULL));
		$data["disabled"] = $this->mdl_vehicles->order_by('vehicle_trailer')->get_many_by('vehicle_disabled IS NOT NULL');

		//echo '<pre>'; var_dump($data["disabled"]); die;
		$this->load->view('estimates/index_equipment', $data);
	}

	function save_vehicle()
	{

		$this->load->model('mdl_vehicles');
		$data['vehicle_name'] = $this->input->post('vehicle_name');
		$data['vehicle_per_hour_price'] = $this->input->post('vehicle_per_hour_price');
		$data['vehicle_trailer'] = $this->input->post('vehicle_trailer') ? intval($this->input->post('vehicle_trailer')) : NULL;
		//$data['vehicle_tool'] = $this->input->post('vehicle_tool') ? 1 : NULL;
		$data['vehicle_options'] = NULL;

		$options = explode("|", $this->input->post('vehicle_options'));
		//echo '<pre>'; var_dump(json_encode($options, JSON_HEX_QUOT)); die;
		$data['vehicle_options'] = $this->input->post('vehicle_options') != '' ? json_encode($options) : NULL;

		if($this->input->post('id'))
			$this->mdl_vehicles->update($this->input->post('id'), $data);
		else
			$this->mdl_vehicles->insert($data);
		die(json_encode(['status' => 'ok']));
	}

	function delete_vehicle()
	{
		$this->load->model('mdl_vehicles');

		$data['vehicle_disabled'] = intval($this->input->post('disabled')) ? 1 : NULL;
		//var_dump($_POST, $data); die;
		if($this->mdl_vehicles->update($this->input->post('id'), $data))
			die(json_encode(['status' => 'ok']));
		die(json_encode(['status' => 'error']));
	}

	function estimates_by_areas()
	{
		$this->load->model('mdl_reports');
		$getPolygons = $this->db->query('SELECT * FROM neighborhoods')->result_array();
		$data['title'] =  $this->_title . ' - Estimates By Areas';
		$data['menu_leads'] = "active";
		$data['statusesSelect'] = "New";
		$this->load->library('googlemaps');
		$this->load->library('Pointlocation');
		$this->load->library('areas');
		//Set the map:
		$config['center'] = config_item('map_center');
		$config['zoom'] = 'auto';
		$this->googlemaps->initialize($config);

		$rating[5] = 'fa141e'; $rating[10] = 'fd323b'; $rating[15] = 'f94c54'; $rating[20] = 'f9656c'; $rating[25] = 'f8787e'; $rating[30] = '3a513c';
		$rating[35] = '3c5a3f'; $rating[40] = '406643'; $rating[45] = '406f45';	$rating[50] = '417a46';	$rating[55] = '418347';	$rating[60] = '3e8c45';
		$rating[65] = '3e9647';	$rating[70] = '3b9d44';	$rating[75] = '38a843';	$rating[80] = '31b83e';	$rating[85] = '2dc53b';	$rating[90] = '26d236';
		$rating[95] = '1de330';
		$data['rating'] = $rating;

		$estimator_id = "";
		if(intval($this->input->post('user_id')))
			$data['user_id'] = $estimator_id = $this->input->post('user_id');

		$from_date = "";
		$to_date = "";

		$to_date = $data['to'] = date('Y-m-d 23:59:59');
		$from_date = $data['from'] = date('Y-m-d 00:00:00', mktime(0, 0, 0, date("m")-3, date("d"),   date("Y")));
		$data['visibleSet'] = $data['users'] = [];

		$data['users'] = [];
		$users = $this->mdl_estimates->get_active_estimators();
		if($users)
			$data['users'] = $users;

		//echo '<pre>'; var_dump($data); die;
		if($this->input->post('from'))
		{
//            $from_date = $data['from'] = $this->input->post('from') . " 00:00:00" ;
            $from = DateTime::createFromFormat(getDateFormat(), $this->input->post('from'));
            $from_date = $data['from'] = $from->format('Y-m-d') . " 00:00:00" ;
        }
		if($this->input->post('to'))
		{
//            $to_date = $data['to'] = $this->input->post('to')  . " 23:59:59";
            $to = DateTime::createFromFormat(getDateFormat(), $this->input->post('to'));
            $to_date = $data['to'] = $to->format('Y-m-d')  . " 23:59:59";
        }
		//
		foreach($getPolygons as $k=>$v)
		{
			$status = "";
			$status_only = "";
			$offset = json_decode($v['offset_center']);
			$points = json_decode($v['coords']);
			foreach($points as $key=>$val)
			{
				$polygon[$k]['points'][] = $val->lat .', '. $val->lng;
				$polygon[$k]['strokeColor'] = '#e80b0b';
			}
			$color = '';
			$coords = FALSE;
			$marker = array();
			$polygon[$k]['fillOpacity'] = '0.9';

			//ESTIMATE DATA

			$area = $v['id'];

			$rev_data = [];
			if($estimator_id != '')
			$rev_data['estimates.user_id'] = $estimator_id;
			$rev_data['estimates.date_created >='] = strtotime($from_date);
			$rev_data['estimates.date_created <='] = strtotime($to_date);
			$rev_data['area'] = $area;

			$total_estimates = $confirmed_estimates = $revenue_total_estimates = $confirmed_revenue_estimates = 0;

			$all_stats = $this->mdl_reports->estimates_statistic($status, $estimator_id, $status_only, $from_date, $to_date, $area);
			$all_rev = $this->mdl_reports->revenue_estimates_sum_new($rev_data);

			foreach($all_stats as $stat){
				$total_estimates += $stat->estimates_amount;
				if($stat->status == 6){
					$confirmed_estimates += $stat->estimates_amount;
				}
			}

			foreach($all_rev as $stat){
				$revenue_total_estimates += $stat->sum_for_services;
				if($stat->status == 6){
					$confirmed_revenue_estimates += $stat->sum_for_services;
				}
			}

			$avg = ($revenue_total_estimates) ? round(($confirmed_revenue_estimates * 100 / $revenue_total_estimates), 2) : 0;

			foreach($rating as $jkey=>$jval)
			{

				if($avg <= 5)
				{
					$data['visibleSet'][$jkey][] = $v['id'];
					$color = 'fa141e';
					break;
				}
				if(isset($rating[$jkey+5]) && ($avg >= $jkey && $avg < $jkey +5))
				{
					$data['visibleSet'][$jkey][] = $v['id'];
					$color = $jval;
					break;
				}
				elseif(!isset($rating[$jkey+5]))
				{
					$data['visibleSet'][$jkey][] = $v['id'];
					$color = $jval;
				}

			}


			$polygon[$k]['fillColor'] = '#'. $color;
			$this->googlemaps->add_polygon($polygon[$k]);
			$data['polygons'][$k] = $polygon[$k];


			$coords = $this->areas->getCentroidOfPolygon($points);

			//END ESTIMATE DATA
			//INFO WINDOW
			if($coords)
			{
				if($offset)
				{
					$coords[0] += $offset[0];
					$coords[1] += $offset[1];
				}
				$marker_content = "<div class='text-center' style='padding-right: 12px;padding-bottom: 12px;'><strong>TE : " . $total_estimates . " / " . money($revenue_total_estimates) . "</strong>";
				$marker_content .= "<br><strong>CE : " . $confirmed_estimates . " / " . money($confirmed_revenue_estimates) . "</strong></div>";

				$marker_style = task_pin('#81ba53', $avg . '%', FALSE, '#000', 120, 60, 60);

				$marker = array();
				$marker['position'] = $coords[0] . ', ' . $coords[1];
				$marker['infowindow_arr_content'] = $marker_content;
				$marker['icon'] = $marker_style;
				//$marker['icon'] = json_encode($marker['icon']);
				$this->googlemaps->add_marker($marker);
			}
			//END INFO WINDOW


		}


		$data['map'] = $this->googlemaps->create_map();


		$this->load->view('areas_map', $data);
	}

	function declines($date = NULL)
	{
		$data['title'] = 'Quality Assurance | Declines';
		$this->load->model('mdl_est_status');
		$this->load->model('mdl_estimates');
		$data['symbols'] = array(' - ', ' ','-');

		$data['from'] = date('Y-m-01');
		$data['to'] = date('Y-m-t');

		if ($this->input->post('from'))
        {
            $from = DateTime::createFromFormat(getDateFormat(), $this->input->post('from'));
            $data['from'] = $from->format('Y-m-d');
        }
		if ($this->input->post('to'))
        {
            $to =DateTime::createFromFormat(getDateFormat(), $this->input->post('to'));
            $data['to'] = $to->format('Y-m-d');
        }

		$wdata['estimates.date_created >'] = strtotime($data['from'] . '  00:00:00');
		$wdata['estimates.date_created <'] = strtotime($data['to'] . '  23:59:59');
		$data['statuses'] = $this->mdl_est_status->with('mdl_est_reason')->get_many_by(array('est_status_declined' => 1));
		$data['count_estimates'] = 0;
		foreach($data['statuses'] as $key=>$status)
		{
			foreach($status->mdl_est_reason as $jkey=>$reason)
			{
				$wdata['estimates.estimate_reason_decline'] = $reason->reason_id;
				$Obj = $this->mdl_estimates->get_estimates('', '', '', '', 'date_created', '', $wdata);
				if($Obj)
				{
					$data['declined'][mb_strtolower(str_replace($data['symbols'], '_', $reason->reason_name))] = $Obj->result_array();
					$data['count_estimates'] += count($Obj->result_array()); //countOk
				}
			}
		}
		$this->load->view('qa_declines', $data);
	}

    function ajax_get_estimate_statuses()
    {
        $this->load->model('mdl_est_status');
        return $this->response($this->mdl_est_status->order_by('est_status_priority')->get_many_by(['est_status_active' => 1]),200);
    }

    function ajax_get_estimate_reasons()
    {
        $this->load->model('mdl_est_reason');
        return $this->response($this->mdl_est_reason->get_many_by(['reason_active' => 1]),200);
    }

    function ajax_get_estimate_payments()
    {
        if(!$estimate_id = $this->input->post('estimate_id'))
            return $this->response(['error' => 'estimate id'],400);
        $this->load->model('mdl_clients');
        return $this->response($this->mdl_clients->get_payments(array('client_payments.estimate_id' => $estimate_id)),200);
    }

    function getTaxValue()
    {
        $editTax = $this->input->get('text');
        $taxes = all_taxes();
        $response = [];
        foreach ($taxes as $tax) {
            if ($editTax == $tax['text']) {
                $response = ['value' => $tax['value'], 'name' => $tax['name']];
                break;
            }
        }
        return $this->response($response);
    }
    function getBundleRecords(){
        $records =  $this->input->get('records');
        $bundleId = $this->input->get('id');
        $html = '';
        $ids = [];
        $itemsIsNotCollapsed = [];
        $servicesDefaultCrews = [];
        if(!empty($records)) {
            foreach ($records as $record) {
                $data = [];
                $service_data = new StdClass();
                $service_data->service = (object)$record;
                $service_data->id = rand(10, 10000);
                $service_data->non_taxable = 0;
                $service_data->service_id = $record['service_id'];
                $service_data->service_price = 0;
                $service_data->service_description = $record['service_description'];
                $service_data->service_markup_rate = $record['service_markup'];
                $service_data->bundle_id = $bundleId;
                $service_data->new = true;
                $service_data->estimate_service_category_id = $record['service_category_id'];
                $service_data->service_is_collapsed = $record['service_is_collapsed'];
                $data['access_token'] = $this->mdl_settings_orm->get_by('stt_key_name', 'accessTokenKey')->stt_key_value ? 1 : 0;
                $classes = QBClass::where(['class_parent_id' => null, 'class_active' => 1])->with('classesWithoutInactive')->get();
                $data['classes'] = [];
                if(!empty($classes->toArray())) {
                    $data['classes'] = getClasses($classes->toArray());
                }
                if(!empty($record['service_default_crews']))
                    $servicesDefaultCrews[$service_data->id] = $record['service_default_crews'];
                if (!$record['is_product']) {
                    $service_data->service_overhead_rate = config_item('service_overhead_rate');
                    $service_data->equipments = [];
                    $data['trailers'] = $this->mdl_vehicles->get_many_by(array('vehicle_trailer' => 1, 'vehicle_disabled' => NULL));
                    $data['tools'] = $this->mdl_vehicles->get_many_by(array('vehicle_trailer' => 2, 'vehicle_disabled' => NULL));
                    $data['crews'] = $this->mdl_crews->get_crews(array('crew_status' => 1, 'crew_id >' => 0), 'crew_status DESC, crew_priority ASC');
                    $data['vehicles'] = $this->mdl_vehicles->get_many_by(array('vehicle_trailer IS NULL', 'vehicle_disabled' => NULL));
                    if ($record['qty'] > 1) {
                        for ($i = 0; $i < $record['qty']; $i++) {
                            $service_data->id = rand(10, 10000);
                            $data['service_data'] = $service_data;
                            $html .= $this->load->view('service_tpl', $data, TRUE);
                            $ids[] = $service_data->id;
                            if($record['service_is_collapsed'] == 0)
                                $itemsIsNotCollapsed[] = $service_data->id;
                        }
                    } else {
                        $data['service_data'] = $service_data;
                        $html .= $this->load->view('service_tpl', $data, TRUE);
                        $ids[] = $service_data->id;
                        if($record['service_is_collapsed'] == 0)
                            $itemsIsNotCollapsed[] = $service_data->id;
                    }
                } else {
                    $service_data->cost = $record['cost'];
                    $service_data->quantity = $record['qty'];
                    $service_data->service_price = $record['cost'] * $record['qty'];
                    $data['service_data'] = $service_data;
                    $html .= $this->load->view('products/product_tpl', $data, TRUE);
                    $ids[] = $service_data->id;
                    if($record['service_is_collapsed'] == 0)
                        $itemsIsNotCollapsed[] = $service_data->id;
                }
            }
        }
        return $this->response(['type' => 'ok', 'html' => $html, 'ids' => $ids, 'itemsIsNotCollapsed' => $itemsIsNotCollapsed, 'servicesDefaultCrews' => $servicesDefaultCrews], 200);
    }

    function getBundleRecordsv2(){
        $bundleId = $this->input->get('id');
        $bundleDBId = $this->input->get('bundleId');
        $html = '';
        $ids = [];
        $itemsIsNotCollapsed = [];
        $servicesDefaultCrews = [];
        $records = $this->mdl_services->get_records_included_in_bundle($bundleDBId);
        if(!empty($records)) {
            foreach ($records as $record) {
                $data = [];
                $service_data = new StdClass();
                $service_data->service = (object)$record;
                $service_data->id = rand(10, 10000);
                $service_data->non_taxable = 0;
                $service_data->service_id = $record->service_id;
                $service_data->service_price = 0;
                $service_data->service_description = $record->service_description;
                $service_data->service_markup_rate = $record->service_markup;
                $service_data->bundle_id = $bundleId;
                $service_data->new = true;
                $service_data->estimate_service_category_id = $record->service_category_id;
                $service_data->service_is_collapsed = $record->service_is_collapsed;
                $data['access_token'] = $this->mdl_settings_orm->get_by('stt_key_name', 'accessTokenKey')->stt_key_value ? 1 : 0;
                $classes = QBClass::where(['class_parent_id' => null, 'class_active' => 1])->with('classesWithoutInactive')->get();
                $data['classes'] = [];
                if(!empty($classes->toArray())) {
                    $data['classes'] = getClasses($classes->toArray());
                }
                if(!empty($record->service_default_crews))
                    $servicesDefaultCrews[$service_data->id] = $record->service_default_crews;
                if (!$record->is_product) {
                    $service_data->service_overhead_rate = config_item('service_overhead_rate');
                    $service_data->equipments = [];
                    $data['trailers'] = $this->mdl_vehicles->get_many_by(array('vehicle_trailer' => 1, 'vehicle_disabled' => NULL));
                    $data['tools'] = $this->mdl_vehicles->get_many_by(array('vehicle_trailer' => 2, 'vehicle_disabled' => NULL));
                    $data['crews'] = $this->mdl_crews->get_crews(array('crew_status' => 1, 'crew_id >' => 0), 'crew_status DESC, crew_priority ASC');
                    $data['vehicles'] = $this->mdl_vehicles->get_many_by(array('vehicle_trailer IS NULL', 'vehicle_disabled' => NULL));
                    if ($record->qty > 1) {
                        for ($i = 0; $i < $record->qty; $i++) {
                            $service_data->id = rand(10, 10000);
                            $data['service_data'] = $service_data;
                            $html .= $this->load->view('service_tpl', $data, TRUE);
                            $ids[] = $service_data->id;
                            if($record->service_is_collapsed == 0)
                                $itemsIsNotCollapsed[] = $service_data->id;
                        }
                    } else {
                        $data['service_data'] = $service_data;
                        $html .= $this->load->view('service_tpl', $data, TRUE);
                        $ids[] = $service_data->id;
                        if($record->service_is_collapsed == 0)
                            $itemsIsNotCollapsed[] = $service_data->id;
                    }
                } else {
                    $service_data->cost = $record->cost;
                    $service_data->quantity = $record->qty;
                    $service_data->service_price = $record->cost * $record->qty;
                    $data['service_data'] = $service_data;
                    $html .= $this->load->view('products/product_tpl', $data, TRUE);
                    $ids[] = $service_data->id;
                    if($record->service_is_collapsed == 0)
                        $itemsIsNotCollapsed[] = $service_data->id;
                }
            }
        }
        return $this->response(['type' => 'ok', 'html' => $html, 'ids' => $ids, 'itemsIsNotCollapsed' => $itemsIsNotCollapsed, 'servicesDefaultCrews' => $servicesDefaultCrews], 200);
    }

    public function update_brand(){
        if(!$this->input->post('estimate_id') || !$this->input->post('estimate_brand_id'))
            return $this->response('Request is not walid', 400);

        $data = ['estimate_brand_id'=>$this->input->post('estimate_brand_id')];
        Estimate::where('estimate_id', $this->input->post('estimate_id'))->update($data);
        return $this->response('ok', 200);
    }

    function ajax_estimate_priority_category()
    {
        if (request()->user()->user_type != "admin" && !is_cl_permission_all()) {
            show_404();
        }
        $data = $this->input->post('data');
        if (empty($data))
            die(json_encode(array('status' => 'error')));
        foreach ($data as $key => $val) {
            if ($val)
                Category::where('category_id', $key)->update(['category_priority' => $val]);
        }
    }

    public function get_preview_pdf($template, $name)
    {
        if(!$name)
            return $this->errorResponse(400, ['error' => 'Preview not created']);

        $data['template'] = $template;
        $path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $name;
        $data['estimate_terms'] = file_exists($path) ? file_get_contents($path) : null;
        file_exists($path) ? unlink($path) : null;
        $view = $this->load->view('includes/pdf_templates/terms_conditions_preview', $data, true);
        $this->load->library('mpdf');
        $this->mpdf->WriteHTML($view);
        $this->mpdf->Output('brand_preview.pdf', 'I');
    }

    function preview_estimate_pdf($client_id, $lead_id, $brand_id)
    {

        if (!isset($client_id) && !$lead_id) { // NB: Set to redirect to index if variable is null or not set;
            redirect('estimates/', 'refresh');
        } else {
            $data = $this->estimateactions->getPreviewDraftEstimate($client_id, $lead_id, $brand_id);
            if(!$data) {
                redirect('estimates/', 'refresh');
                return false;
            }

            $this->load->library('mpdf');
            $this->mpdf->WriteHTML($data['html']);
            foreach ($data['files'] as $keyFiles => $file) {
                if(is_array($file) && !empty($file)){
                    foreach ($file as $key => $val){
                        if(pathinfo($val, PATHINFO_EXTENSION) == 'pdf') {
                            $this->mpdf->AddPage('L');
                            $this->mpdf->Thumbnail(bucket_get_stream($val), 1, 10, 16, 1);
                        }else{
                            $type = getMimeType($val);
                            if(!is_bucket_file($val) || strripos($type, 'audio') !== false || strripos($type, 'video') !== false)
                                unset($file[$key]);
                        }
                    }
                    $data['files'][$keyFiles] = $file;
                }
                elseif(!is_array($file) && pathinfo($file, PATHINFO_EXTENSION) == 'pdf') {
                    $this->mpdf->AddPage('L');
                    $this->mpdf->Thumbnail(bucket_get_stream($file), 1, 10, 16, 1);
                } elseif(!is_array($file)) {
                    $type = getMimeType($file);
                    if(!is_bucket_file($file) || strripos($type, 'audio') !== false || strripos($type, 'video') !== false)
                        unset($data['files'][$keyFiles]);
                }
            }

            if(isset($data['estimate']->tree_inventory_pdf) && !empty($data['estimate']->tree_inventory_pdf)) {
                $this->leadsactions->setLead($lead_id);
                $treeInventoryHtml = $this->leadsactions->tree_inventory_pdf(true);

                if ($treeInventoryHtml) {
                    $this->mpdf->WriteHTML($treeInventoryHtml);
                }
            }
            $this->mpdf->Output($data['file'], 'I');
        }
    }

    public function ajax_statuses_update()
    {
        $data   = $this->input->post('statuses') ?? [];
        $column = 'est_status_priority';

        foreach ($data as $key => $id) {
            $priority[$column]  = $key;

            $this->mdl_est_status->update($id, $priority, false);
        }

        $this->response(['result' => true]);
    }

    public function copy(){
        $id=$this->input->get('estimate_id');
        $clientId=null;
        if($this->input->get('new_client_id')!==null && !empty($this->input->get('new_client_id'))){
            $clientId=$this->input->get('new_client_id');
        }

        $to_status=$this->input->get('to_status');

        $estimate_status=false;
        $workorders_status=false;
        $invoices_status=false;
        if($to_status=='estimate'){
            $estimate_status=$this->input->get('est_status');
        }else if($to_status=='workorders'){
            $workorders_status=$this->input->get('wo_status');
        }else if($to_status=='invoices'){
            $invoices_status=$this->input->get('invoices_status');
        }else{
            return $this->response(['result' => 'to_status is required']);
        }
        

        $data = $this->estimateactions->copyEstimate($id,$clientId,$estimate_status,$workorders_status,$invoices_status);
        $this->response(['result' => $data]);
    }

    public function copyFast(){
        $id=$this->input->get('estimate_id');
        $clientId=null;
        if($this->input->get('new_client_id')!==null && !empty($this->input->get('new_client_id'))){
            $clientId=$this->input->get('new_client_id');
        }

        $to_status=$this->input->get('to_status');

        $estimate_status=false;
        $workorders_status=false;
        $invoices_status=false;
        if($to_status=='estimate'){
            $estimate_status=$this->input->get('est_status');
        }else if($to_status=='workorders'){
            $workorders_status=$this->input->get('wo_status');
        }else if($to_status=='invoices'){
            $invoices_status=$this->input->get('invoices_status');
        }else{
            return $this->response(['result' => 'to_status is required']);
        }

        $data = $this->estimateactions->copyEstimateFast($id,$clientId,$estimate_status,$workorders_status,$invoices_status);
        $this->response(['result' => $data]);
    }

}//end of file estimates.php
