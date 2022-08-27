<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed');

use application\modules\clients\models\Tag;
use application\modules\employees\models\Employee;
use application\modules\crew\models\Crew;
use application\modules\clients\models\ClientLetter;
use application\modules\workorders\models\Workorder;
use application\modules\estimates\models\EstimatesServicesEquipments;
use application\modules\estimates\models\EstimatesService;
use application\modules\estimates\models\Service;
use application\modules\workorders\models\WorkorderStatus;
use application\modules\estimates\models\EstimatesServicesStatus;
use application\modules\user\models\User;
use application\modules\estimates\models\TreeInventoryEstimateServiceWorkTypes;
use application\modules\tree_inventory\models\WorkType;

use Illuminate\Http\JsonResponse;

class Workorders extends MX_Controller
{

//*******************************************************************************************************************
//*************																					Workorders Controller;
//*************
//*******************************************************************************************************************	

    function __construct()
    {

        parent::__construct();

        //Checking if user is logged in;

        if (!isUserLoggedIn() && isWorkorderAccessible()) {
            redirect('login');
        }

        if (is_cl_permission_none() && isWorkorderAccessible()) {
            redirect(base_url());
        }

        $this->_title = SITE_NAME;

        //load all common models and libraries here;
        $this->load->model('mdl_workorders', 'mdl_workorders');
        $this->load->model('mdl_clients', 'mdl_clients');
        $this->load->model('mdl_estimates', 'mdl_estimates');
        $this->load->model('mdl_invoices', 'mdl_invoices');
        $this->load->model('mdl_user', 'mdl_user');
        $this->load->model('mdl_employee');
        $this->load->model('mdl_administration');
        $this->load->model('mdl_schedule', 'mdl_schedule');

        $this->load->model('mdl_estimates_orm', 'mdl_estimates_orm');
        $this->load->model('mdl_services_orm', 'mdl_services_orm');
        $this->load->model('mdl_crews_orm', 'mdl_crews_orm');
        $this->load->model('mdl_equipment_orm', 'mdl_equipment_orm');
        $this->load->model('mdl_calls');
        $this->load->model('mdl_sms_messages');
        $this->load->model('mdl_estimates_bundles');
        //Load Library
        $this->load->library('mpdf');
        $this->load->library('pagination');

        //Load Google Map Library
        $this->load->library('googlemaps');

        //Load helpers:
        $this->load->helper('estimates');
        $this->load->helper('workorders');
        $this->load->helper('tree_helper');
    }

//********************************************************************************************************************
//*************
//*************																									Index;
//*************
//********************************************************************************************************************	

    public function index()
    {

        $data['title'] = $this->_title . ' - Workorders';
        $data['menu_workorders'] = "active";
        if (request()->ajax()) {
            $this->ajax_search_workorders();
            return;
        }

        $statuses = WorkorderStatus::withCount(["workorders" => function ($q) {
            $q->permissions();
        }])->active()->notFinished()->orderBy('wo_status_priority')->get();

        $data['select2Tags'] = Tag::select2FormatData();

        $users = User::estimator()->noSystem()->nameAsc()->get();
        $data['select2Estimators'] = collect([
            'active' => $users->where('active_status', 'yes')->values()->forSelect2('id', 'full_name', true),
            'inactive' => $users->where('active_status', '!=', 'yes')->values()->forSelect2('id', 'full_name', true)
        ])->toJson();

        $data['select2Crews'] = Crew::select2FormatData();
        $data['select2Services'] = Service::selectServices2FormatData();
        $data['select2Bundles'] = Service::selectBundles2FormatData();
        $data['select2Products'] = Service::selectProducts2FormatData();
        $data['select2Statuses'] = WorkorderStatus::selectStatuses2FormatData();
        $data['select2DefaultStatus'] = WorkorderStatus::active()->default()->first();
        $data['tagsExpandLimit'] = Tag::TAGS_EXPAND_LIMIT;

        $data['statuses'] = $statuses;
        $data['default_status'] = WorkorderStatus::active()->default()->first();
        $this->load->view("index", $data);
    }

    private function  ajax_search_workorders()
    {
        $request = request();
        $status = $filters = [];
        $orderColumnTargetIndex = $request->order[0]['column'];
        $orderColumnName = $request->columns[$orderColumnTargetIndex]['name'];
        $orderDir = $request->order[0]['dir'];
        $page = ($request->start / $request->length) + 1;
        $status_query = WorkorderStatus::withCount(["workorders" => function ($q) {
            $q->permissions();
        }])->active();

        $finished = WorkorderStatus::finished()->first();

        if(!$request->input('wo_status_id') && !$request->input('search_keyword')) {
            $filters['status_id'] = $status_query->default()->first()->wo_status_id;
            $status = $status_query->default()->first();
        }
        elseif($request->input('wo_status_id')==-1) {
            $filters['status_id !='] = -1;
            $status = $status_query->notFinished()->get()->pluck('wo_status_id')->toArray();
        }
        else {
            $filters['status_id'] = $status_query->find($request->input('wo_status_id'))->wo_status_id;
            $status = $status_query->find($request->input('wo_status_id'));
        }

        if($request->input('search_tags'))
            $filters['tags'] = $request->input('search_tags');

        $statuses = WorkorderStatus::withCount(["workorders" => function ($q) {
            $q->permissions();
        }])->active()->notFinished()->orderBy('wo_status_priority')->get();

        $all = Workorder::join('estimates', 'estimates.estimate_id', 'workorders.estimate_id')
            ->where('workorders.wo_status', '<>',  $finished->wo_status_id)->count();
        $wo = Workorder::getWorkordersList($page, $filters, $request->length, $orderColumnName, $orderDir, $request);

        $total = count($wo) ? $wo->total() : 0 ;

        $statuses->prepend(['wo_status_id' => -1, 'wo_status_name' => 'All', 'workorders_count' => $all]);
        $workorders = count( $wo) ?  $wo->items() : [] ;

        return $this->response([
            'data' => new JsonResponse($workorders),
            'active_status' => $status,
            'statuses' => $statuses,
            'recordsTotal' => $total,
            'recordsFiltered' => $total,
            'total'=>$all
        ]);
    }


//**********************************************************************************************************************************
//*************
//*************																				Workorder_Mapper: Ready to be Scheduled:
//*************
//**********************************************************************************************************************************	

    public function workorders_mapper($status = null)
    {
        $status = $status == 'all' ? 'all' : intval($status);
        if (!$status)
            redirect(base_url('workorders/workorders_mapper/1'));

        //Page Presets
        $data['title'] = $this->_title . ' - Workorders Map - ' . $status;
        $data['menu_workorders'] = "active";

        //Set the map:
        $config['center'] = config_item('map_center');
        $config['zoom'] = 'auto';
        $this->googlemaps->initialize($config);

        //Get required workorder data:
        $wdata['wo_status'] = '';
        $finishedStatusId = $this->mdl_workorders->getFinishedStatusId();

        if($status != 'all') {
            $arr = $this->mdl_workorders->get_workorders('', $status, '', '');
        } else {
            $arr = $this->mdl_workorders->get_workorders('', null, '', '', 'wo_status !=' . $finishedStatusId);
        }
        $data['status_id'] = $status;

        $data['statuses'] = array_merge([['wo_status_id' => 'all', 'wo_status_name' => 'All']], $this->mdl_workorders->get_all_statuses(['wo_status_active' => 1, 'wo_status_id !=' => $finishedStatusId]));

        if ($arr->num_rows()) {
            //Creating the markers for leads:
            foreach ($arr->result() as $row) {

                $client_id = $row->client_id;
                $name = $row->client_name;
                $street = $row->client_address;
                $city = $row->client_city;
                $address = $street . "+" . $city;
                $status = $row->wo_status_name;
                $total = $row->total;

                //Marker Content:
                $marker_link = base_url($row->workorder_no);
                $marker_content = "<strong data-crew='" . $row->estimate_crew_id . "'><a href='" . $marker_link . "' target='_blank'>" . $name . " - " .  $row->workorder_no . "</a></strong>";
                $marker_content .= "<br>" . $status;
                $marker_content .= "<br>Total:&nbsp;$" . number_format((float)$total, 2, '.', '');

                //$marker_style = "https://chart.apis.google.com/chart?chst=d_map_pin_letter&chld=%E2%80%A2|" . str_replace('#', '', $row->wo_status_color) . "|D8B44F";
                $marker_style = mappin_svg($row->wo_status_color, '&#9899;', FALSE, '#D8B44F');

                $marker = array();
                $marker['position'] = $row->lat . ',' . $row->lon;//$address;
                $marker['infowindow_content'] = $marker_content;
                $marker['icon'] = $marker_style;
                $this->googlemaps->add_marker($marker);
            }
        }
        $data['map'] = $this->googlemaps->create_map();

        $this->load->view('map', $data);
    }

    public function profile($workorder_id = null, $event_id = null, $lead_id = NULL)
    {
        //Set title:
        $data['title'] = $this->_title . ' - Workorders';
        $data['menu_workorders'] = "active";
        $this->load->model('mdl_schedule');
        $this->load->model('mdl_est_equipment');
        $this->load->model('mdl_crew', 'crew_model');
        $this->load->model('mdl_sms');
        $this->load->model('mdl_vehicles');
        $data["tools"] = $this->mdl_vehicles->get_many_by(array('vehicle_trailer' => 2));
        if (!$workorder_id && !$lead_id)
            redirect('workorders/', 'refresh');

        //Get workorder data
        if (!$lead_id)
            $data['workorder_data'] = $this->mdl_workorders->wo_find_by_id($workorder_id);
        else
            $data['workorder_data'] = $this->mdl_workorders->wo_find_by_lead_id($lead_id);

        if(!$data['workorder_data'] || !is_object($data['workorder_data']))
            return page_404(['message'=>'This workorder does not exist']);
        
        $workorder_id = $data['workorder_data']->id;
        $equipment_items = $this->mdl_est_equipment->order_by('eq_weight')->get_many_by(array('eq_status' => 1));

        foreach ($equipment_items as $jkey => $val) {
            $data['equipment_items'][$val->eq_id] = (array)$val;
        }

        $data['crew_row'] = $this->crew_model->get_crewdata();
        $data['crews_active'] = $this->crew_model->get_crewdata(array('crew_status' => 1));

        if ($event_id) {
            $schEvents = $this->mdl_schedule->get_events(array('schedule.id' => $event_id));
            $schedule_event = $schEvents[0] ?? null;

            if($schedule_event) {
                $data['event_services'] = $this->mdl_schedule->get_event_services(['event_id' => $event_id]);
                $data['schedule_event'] = $schedule_event;
                $data['team_id'] = $schedule_event['team_id'];

                $event_services_id = '';
                foreach ($data['event_services'] as $val) {
                    $event_services_id .= 'estimates_services.id = ' . $val['id'] . ' OR ';
                    $data['service_ids'][] = $val['event_service_id'];
                }
                $event_services_id = '(' . rtrim($event_services_id, ' OR ') . ')';
            }
        }

        foreach ($data['crews_active']->result_array() as $key => $val)
            $data['all_crews'][$val['crew_id']] = $val;
        if (!$data['workorder_data'])
            page_404(['message'=>'This workorder does not exist']);

        $data['estimate_id'] = $estimate_id = $data['workorder_data']->estimate_id;
        $data['statuses'] = $this->mdl_workorders->get_all_statuses(array('wo_status_active' => 1));
        $finishedWoStatusId = (int)$this->mdl_workorders->getFinishedStatusId();
        //Get invoice data and interest if workorder is finished
        if (!$data['workorder_data']->wo_status) {
            $data['invoice_data'] = $this->mdl_invoices->getEstimatedData($estimate_id);
            if(isset($data['invoice_data']->id)) {
                $data['invoice_interest_data'] = $this->mdl_invoices->getInterestData($data['invoice_data']->id);
            } else {
                $this->load->library('Common/WorkorderActions');
                $this->workorderactions->setStatus(\application\modules\workorders\models\Workorder::find($data['workorder_data']->id), $finishedWoStatusId, true);
                $data['invoice_data'] = $this->mdl_invoices->getEstimatedData((int)$estimate_id);
                $data['invoice_interest_data'] = $this->mdl_invoices->getInterestData($data['invoice_data']->id);
            }
        }
        $data['wo_statuses'] = $this->mdl_services_orm->get_service_status();
        //Get estimate informations - using common function from MY_Models;

        $data['estimate_data'] = $this->mdl_estimates_orm->with('mdl_services_orm')->get_full_estimate_data(array('estimate_id' => $estimate_id), true, false, true, true)[0];


        if (!$data['estimate_data'])
            page_404(['message'=>'This workorder does not exist']);
        //estimate services
        $data['estimate_services_data'] = $this->mdl_estimates->find_estimate_services($estimate_id);
        //estimate crews
        $data['estimate_crews_data'] = $this->mdl_estimates->find_estimate_crews($estimate_id);

        $this->load->model('mdl_tracking');
        $this->load->model('mdl_leads');
        $this->load->model('mdl_payroll');
        $data['lead_data'] = $this->mdl_leads->find_by_id($data['estimate_data']->lead_id);

        //employees
        $this->load->model('mdl_employees');

        $users = $this->mdl_user->get_usermeta(array('emp_status' => 'current', 'emp_feild_worker' => 1));
        $data['employees'] = $users ? $users->result() : [];

        //workers
        $this->load->model('mdl_crews', 'mdl_crews');
        $crews = $this->mdl_crews->get_crews();

        //events
        $data['events'] = $this->mdl_schedule->get_events(['schedule.event_wo_id' => $workorder_id]);
        $data['events_total'] = $this->mdl_schedule->get_events_totals(['workorders.id' => $workorder_id]);
        $data['events_total_summ'] = arrau_field_summ($data['events_total'], 'planned_mhrs');
        $data['planned_price'] = arrau_field_summ($data['events_total'], 'planned_price') - ($data['estimate_data']->discount_total??0);

        $range = 0.4; //Radius in kilometers; ~60-100 meters diameter. ///0.019 CHANGED BY GLEBA RUSLAN
        $lat_range = $range / 69.172;
        $lon_range = abs($range / (cos($data['lead_data']->latitude) * 69.172));
        $data['min_lat'] = number_format($data['lead_data']->latitude - $lat_range, "6", ".", "");
        $data['max_lat'] = number_format($data['lead_data']->latitude + $lat_range, "6", ".", "");
        $data['min_lon'] = number_format($data['lead_data']->longitude - $lon_range, "6", ".", "");
        $data['max_lon'] = number_format($data['lead_data']->longitude + $lon_range, "6", ".", "");

        $data['tracking'] = [];
        foreach ($data['events'] as $k => $event) {
            $data['members'][$event['id']] = $this->mdl_schedule->get_team_members(['employee_team_id' => $event['team_id']]);
            $data['items'][$event['id']] = $this->mdl_schedule->get_team_items(array('equipment_team_id' => $event['team_id']));
            if (!empty($data['members'][$event['id']])) {
                foreach ($data['members'][$event['id']] as $key => $member) {
                    $where['id'] = $member['employee_id'];
                    $where['start_date'] = date('Y-m-d 00:00:00', $member['team_date']);
                    $where['end_date'] = date('Y-m-d 23:59:59', $member['team_date']);
                    $data['members'][$event['id']][$key]['logouts'] = $this->mdl_employee->get_emp_login_data_biweekly($where);
                    $data['members'][$event['id']][$key]['payroll'] = $this->mdl_payroll->get_by(array('payroll_start_date <=' => $where['start_date'], 'payroll_end_date >=' => $where['end_date']));
                }
            }
            $data['events'][$k]['count_team_events'] = $this->mdl_schedule->getCountEvents(['event_team_id' => $event['event_team_id']]);
            foreach ($data['items'][$event['id']] as $item) {
                $wdata['tracking_device_name'] = $item['eq_gps_id'];
                $wdata['tracking_start_time >='] = date('Y-m-d 00:00:00', $event['event_start']);
                $wdata['tracking_start_time <='] = date('Y-m-d 23:59:59', $event['event_start']);
                $wdata['tracking_lat >='] = $data['min_lat'];
                $wdata['tracking_lon >='] = $data['min_lon'];
                $wdata['tracking_lat <='] = $data['max_lat'];
                $wdata['tracking_lon <='] = $data['max_lon'];
                $parkings = $this->mdl_tracking->get_tracking($wdata, $event);
                foreach ($parkings as $parks)
                    $data['tracking'][] = $parks;
            }
        }
        //Get client_id and retrive client's information:
        $client_id = $data['workorder_data']->client_id;
        $data['client_data'] = $this->mdl_clients->get_client_by_id($client_id);
        
        $data['client_contacts'] = $this->mdl_clients->get_client_contacts(array('cc_client_id' => $client_id)); //Get client contacts
        //Get client_id and retrive client's information:
        $data['payments_data'] = $this->mdl_clients->get_payments(array('client_payments.estimate_id' => $estimate_id));

        $client_tags = $this->mdl_clients->get_client_tags(array('client_id' => $client_id)); //Get client contacts
        $data['client_tags'] = array_map(function ($item){
            return ['id'=>$item['tag_id'], 'text' => $item['name']];
        }, $client_tags);


        //Discount data
        $data['discount_data'] = $this->mdl_clients->get_discount(array('discounts.estimate_id' => $estimate_id));

        //Enable options:
        $data['workorder_options'] = TRUE;

        //users
        $data['active_users'] = $this->mdl_user->get_usermeta(array('active_status' => 'yes', 'emp_field_estimator'=>'1', 'system_user' => 0))->result();

        $data['client_papers'] = $this->mdl_clients->get_papers(['cp_client_id' => $client_id], 'cp_id DESC');

        $data['client_estimates'] = $this->mdl_estimates->get_client_estimates($client_id); //Get client estimates

        if ($data['client_estimates']) {
            foreach ($data['client_estimates']->result_array() as $key => $estimate) {
                $data['estimates_crews'][] = $this->mdl_estimates->find_estimate_crews($estimate_id);
                $data['estimates_discounts'][$estimate['estimate_id']] = $this->mdl_clients->get_discount(array('discounts.estimate_id' => $estimate_id));

            }
        }

        //Check files:
        $this->load->helper('file');
        $path = 'uploads/payment_files/' . $client_id . '/' . $data['estimate_data']->estimate_no;
        $data['files'] = bucketScanDir($path);
        if (!$data['files'])
            $data['files'] = array();
        sort($data['files'], SORT_NATURAL);

        $id = $data['estimate_data']->user_id;
        $data['user_data'] = $this->mdl_user->find_by_id($id);

        $client_id = $data['estimate_data']->client_id;
        $name = $data['estimate_data']->client_name;
        $street = $data['estimate_data']->lead_address;

        $city = $data['estimate_data']->lead_city;
        $address = $street . "+" . $city;
        //Set the map:
        $config['center'] = config_item('map_center');
        $config['zoom'] = '10';

        $this->googlemaps->initialize($config);

        $marker = array();
        $marker['position'] = $address;
        $marker['icon'] = mappin_svg('#FD7567', '&#9899;', FALSE, '#000');
        $this->googlemaps->add_marker($marker);
        $data['address'] = $address;
        $data['map'] = $this->googlemaps->create_map();
        $data['unsortable'] = TRUE;//DISABLE SORTABLE
        $data['equipment_items'] = $this->mdl_est_equipment->order_by('eq_weight')->get_many_by(array('eq_status' => 1));

        $this->load->model('mdl_vehicles');
        $data["tools"] = $this->mdl_vehicles->get_many_by(array('vehicle_trailer' => 2));

        $data['crews_active'] = $this->crew_model->get_crewdata(array('crew_status' => 1, 'crew_id <>' => 0))->result_array();
        $data['client_contacts'] = $this->mdl_clients->get_client_contacts(array('cc_client_id' => $client_id)); //Get client contacts
        $data['client_contact'] = $this->mdl_clients->get_primary_client_contact($client_id);

        $data['sms'] = array();
        $data['messages'] = array();
        $sms = $this->mdl_sms->get(5);
        if ($sms && (is_object($sms) || !empty($sms))) {
            $data['sms'] = $sms;
            $data['messages'] = [json_decode(json_encode($sms), FALSE)];
        }

        $brand_id = get_brand_id($data['estimate_data'], $data['client_data']);

        $this->load->model('mdl_letter');

        $letter = ClientLetter::where('system_label', '=', 'partial_invoice')->first();
        $data['partial_invoice_letter_template_id'] = $letter->email_template_id;


        $taxes = all_taxes();
        $data['taxText'] = $data['estimate_data']->estimate_tax_name . ' (' . round($data['estimate_data']->estimate_tax_value, 2) . '%)';
        $checkTax = checkTaxInAllTaxes($data['taxText']);
        if(!$checkTax)
            $taxes[] = ['text' => $data['taxText'], 'id' => $data['taxText']];
        // Tax recommendation for US companies if the address has changed
        if(config_item('office_country') == 'United States of America') {
            if(!empty($data['estimate_data']->lead_tax_name) && !empty($data['estimate_data']->lead_tax_value) && $data['estimate_data']->lead_tax_value != $data['estimate_data']->estimate_tax_value){
                $data['taxRecommendation'] = round($data['estimate_data']->lead_tax_value, 2);
                $data['taxEstimate'] = round($data['estimate_data']->estimate_tax_value, 2);
                $taxText = $data['estimate_data']->lead_tax_name . ' (' . round($data['estimate_data']->lead_tax_value, 2) . '%)';
                $checkTax = checkTaxInAllTaxes($taxText);
                if(!$checkTax)
                    $taxes[] = ['text' => $taxText,'id' => $taxText];
            }
        }
        $data['allTaxes'] = $taxes;
        //Load view
        $data['estimate_equipments'] = EstimatesServicesEquipments::with('equipment', 'attachment')->where('equipment_estimate_id', $data['estimate_id'])->get();
        $this->load->view('profile', $data);

        // end else;
    }//End Profile();

    function no($no = NULL, $event = NULL)
    {
        if (!$no) {
            show_404();
        }

        $this->profile(NULL, $event, $no);
    }

    function pdf($no = NULL, $event = NULL)
    {
        if (!$no) {
            show_404();
        }

        $this->workorder_pdf(NULL, $event, $no);
    }
//*******************************************************************************************************************
//*************										Ajax Change Workorder Status
//*******************************************************************************************************************	

	function ajax_change_workorder_status($run = FALSE)
	{
		$this->load->model('mdl_schedule', 'mdl_schedule');
		$this->load->model('mdl_services_orm', 'mdl_services_orm');
		$this->load->model('mdl_invoice_status');
        $this->load->library('Common/InvoiceActions');

		$workorder_id = 0;
		$pre_workorder_status = 0;
		$new_workorder_status = 0;
		$eventId = $this->input->post('eventId') ? $this->input->post('eventId') : NULL;
		$scheduleDate = FALSE;
        $invoice_email_template = 7;

		if($this->input->post('date'))
			$scheduleDate = strtotime($this->input->post('date'));

		if ($this->input->post('workorder_id'))
			$workorder_id = $this->input->post('workorder_id');

		if(!$workorder_id)
			return $this->output->set_output(json_encode(['status'=>'error']));

        $workorder_data = $this->mdl_workorders->wo_find_by_id($workorder_id);
		$estimate_data = $this->mdl_estimates_orm->with('mdl_services_orm')->get_full_estimate_data(['estimate_id' => $workorder_data->estimate_id])[0];

		$pre_workorder_status = $workorder_data->wo_status;
		if ($this->input->post('pre_workorder_status'))
			$pre_workorder_status = $this->input->post('pre_workorder_status');

		if ($this->input->post('workorder_status') !== FALSE)
			$new_workorder_status = $this->input->post('workorder_status');

		if ($pre_workorder_status == $new_workorder_status) {
			if($run) {
                return false;
            }

			return $this->output->set_output(json_encode(['status'=>'success', 'workorder_data'=>$workorder_data]));
		}

		$allow_status = allow_workorder_status($new_workorder_status, $estimate_data->mdl_services_orm);

		if(!$allow_status['status'] && $allow_status['message']) {
			return $this->output->set_output(json_encode(['status'=>'error', 'message'=>$allow_status['message'], 'workorder_data'=>$workorder_data]));
		}


		if ($pre_workorder_status != $new_workorder_status) {
			$status = ['status_type' => 'workorder', 'status_item_id' => $workorder_id, 'status_value' => $new_workorder_status, 'status_date' => time()];

			$this->mdl_estimates->status_log($status);
		}

		//Check if the new status == Finished
		//Code to inser invoice data into db
		if ($new_workorder_status == 0) {
		    if($estimate_data->total_due <= 0) {
                $invoice_status = $this->invoiceactions->getPaidInvoiceStatus();
                $email_template = ClientLetter::where('system_label', 'invoice_paid_thanks')->pluck('email_template_id')->first();
                if(!empty($email_template))
                    $invoice_email_template = $email_template;
            }else
                $invoice_status = $this->invoiceactions->getDefaultInvoiceStatus();

			$data = [];
            $workorder_no = $workorder_data->workorder_no;
            $invoice_no = str_replace('W', 'I', $workorder_no);
            $data['client_id'] = $workorder_data->client_id;
            $data['estimate_id'] = $workorder_data->estimate_id;
            $data['workorder_id'] = $workorder_data->id;
            $data['invoice_no'] = $invoice_no;
            $data['in_status'] = $invoice_status;
            $data['date_created'] = date('Y-m-d');
            $data['overdue_date'] = date('Y-m-d', strtotime('+' . \application\modules\invoices\models\Invoice::getInvoiceTerm($workorder_data->client_type ?? null) . ' days'));

            $invoice = $this->mdl_invoices->find_by_fields(['estimate_id' => $workorder_data->estimate_id]);
            if(empty($invoice)) {
                $invoice_id = $this->mdl_invoices->insert_invoice($data);
            } else {
                $invoice_id = $invoice->id;
            }

            //create a new job for synchronization in QB
            if ($invoice_id)
                pushJob('quickbooks/invoice/syncinvoiceinqb', serialize(['id' => $invoice_id, 'qbId' => '']));
		}

		$update_data = [];

        //Form data
        //Delete previous workorders if any:
        if ($new_workorder_status != 0) {
            $id = $workorder_data->estimate_id;
            //create a new job for synchronization in QB
            $invoice = $this->mdl_invoices->find_by_field(['invoices.estimate_id' => $id]);
            if ($invoice)
                pushJob('quickbooks/invoice/syncinvoiceinqb', serialize(['id' => $invoice->id, 'qbId' => $invoice->invoice_qb_id]));

            $delete_invoice = $this->mdl_invoices->delete_invoice($id);
        }

		$update_data['wo_status'] = $new_workorder_status;

		$new_wo_status_row = $this->mdl_workorders->get_all_statuses(['wo_status_id' => $new_workorder_status], 1);
		$pre_wo_status_row = $this->mdl_workorders->get_all_statuses(['wo_status_id' => $pre_workorder_status], 1);

		$wdata = ['id' => $workorder_id];

		$updated = $this->mdl_workorders->update_workorder($update_data, $wdata);
        $workorder_data = $this->mdl_workorders->wo_find_by_id($workorder_id);

		if ($updated) {

			$update_msg = "Status for " . $workorder_data->workorder_no . ' was modified from "' . $pre_wo_status_row['wo_status_name'] . '" to "' . $new_wo_status_row['wo_status_name'] . '"';

			$this->load->model('mdl_schedule');
			if($scheduleDate) {
				$this->mdl_schedule->insert_update(['update_time' => $scheduleDate]);
			} else {
                $events = $this->mdl_schedule->get_events(array('schedule.event_wo_id' => $workorder_data->id));
                if ($events && !empty($events)) {
                    foreach ($events as $event) {
                        $this->mdl_schedule->insert_update(array('update_time' => $event['event_start']));
                    }
                }
            }
            /* ToDo: Check It
			if($eventId)
			{
				if((intval($new_wo_status_row['wo_status_id']) == 7 || intval($new_wo_status_row['wo_status_id']) == 48) && (intval($pre_wo_status_row['wo_status_id']) != 7 || intval($pre_wo_status_row['wo_status_id']) != 48))
				{
					Modules::run('schedule/schedule/generate_followUp', $eventId, TRUE);
				} elseif((intval($pre_wo_status_row['wo_status_id']) == 7 || intval($pre_wo_status_row['wo_status_id']) == 48) && (intval($new_wo_status_row['wo_status_id']) != 7 || intval($new_wo_status_row['wo_status_id']) != 48)) {
					Modules::run('schedule/schedule/generate_followUp', $eventId, FALSE);
				}
			}
            */

			if (make_notes($workorder_data->client_id, $update_msg, 'system', $estimate_data->lead_id)) {
				if($run) return false;

				return $this->output->set_output(json_encode(['status'=>'success', 'workorder_data'=>$workorder_data, 'invoice_email_template'=>$invoice_email_template]));
			}
		}

		if($run)
			return false;
		die(json_encode(['status'=>'success', 'workorder_data'=>$workorder_data, 'invoice_email_template'=>$invoice_email_template]));
		return $this->output->set_output;

	}// End ajax_change_workorder_status


//*********************************************************************************************************************
//*************
//*************																			Ajax Update Workorder Priority:
//*************
//**********************************************************************************************************************

    function ajax_update_workorder_priority()
    {

        //Receive variables from ajax submit:
        $client_id = $this->input->post('for_client_id');
        $workorder_id = $this->input->post('workorder_id');
        $workorder_number = $this->input->post('workorder_number');
        $old_workorder_priority = $this->input->post('old_workorder_priority');
        $new_workorder_priority = $this->input->post('new_workorder_priority');

        //Update the db:
        $update_data['wo_priority'] = $new_workorder_priority;
        $wdata = array('id' => $workorder_id);

        if ($this->mdl_workorders->update_workorder($update_data, $wdata)) {

            //if updated - record the note:
            $message = "Priority for workorder&nbsp;" . $workorder_number . "&nbsp;was modified from&nbsp;" . $old_workorder_priority . "&nbsp;to&nbsp;" . $new_workorder_priority;
            if (make_notes($client_id, $message, 'system', intval($workorder_number))) {

                //if recorded - set the success message:
                $tmp = "<strong>Succuess !</strong>&nbsp;Workorder priority for&nbsp;" . $workorder_number . "&nbsp;was modified from " . $old_workorder_priority . " to " . $new_workorder_priority . "!";
                $mess = message('success', $tmp);
                $this->session->set_flashdata('user_message', $mess);
                echo 'success';
                exit;

            } else {
                return false;
            }

        } else {
            //if not updated - set the error message
            $tmp = "<strong>Allert!</strong>&nbsp;Workorder priority for&nbsp;" . $workorder_number . "&nbsp; was not changed!";
            $mess = message('alert', $tmp);
            $this->session->set_flashdata('user_message', $mess);

            echo 'error';
            exit;
        }
        // I don't think we ever reach this point;
        echo 'error';
        exit;

    }// End ajax_change_workorder_status

//*******************************************************************************************************************
//*************			 
//*************																							Workorder PDF();
//*************											
//*******************************************************************************************************************	

    function workorder_pdf($workorder_id, $event_id = null, $lead_id = NULL)
    {
        if (!$workorder_id && !$lead_id) { // NB: Set to redirect to index if variable is null or not set;
            redirect('workorders/', 'refresh');
        } else {
            //Set title:
            $data = $this->_workorder_html_generate($workorder_id, $event_id, $lead_id);
            $this->load->library('mpdf');
            $this->mpdf->WriteHTML($data['html']);
            foreach ($data['pdf_files'] as $file) {
                $this->mpdf->AddPage();
                $this->mpdf->Thumbnail(bucket_get_stream($file), 1, 10, 5, 5);
            }
            $this->mpdf->Output($data['file'], 'I');

            if ($workorder_id) {
                $workorder_data = $this->mdl_workorders->wo_find_by_id($workorder_id);
                $estimate = $this->mdl_estimates->get_estimate($workorder_data->estimate_id)->row();
            } else {
                $estimate = $this->mdl_estimates->find_by_fields(['lead_id' => $lead_id]);
            }
        }
        // end else;
    }


    function _workorder_html_generate($workorder_id, $event_id = null, $lead_id = null)
    {
        $data['title'] = $this->_title . ' - Workorder';

        $data['estFiles'] = $pdfs = [];
        //Get workorder informations - using common function from MY_Models;
        if (!$lead_id)
            $data['workorder_data'] = $this->mdl_workorders->wo_find_by_id($workorder_id);
        else
            $data['workorder_data'] = $this->mdl_workorders->wo_find_by_lead_id($lead_id);
        if(!$data['workorder_data']) {
            show_404();
            return false;
        }
        $workorder_id = $data['workorder_data']->id;
        $event_services_id = NULL;
        $data['team_id'] = NULL;
        $data['event_id'] = $event_id;
        if ($event_id) {
            $schedule_event = $this->mdl_schedule->get_events(array('schedule.id' => $event_id))[0] ?? false;

            if($schedule_event) {
                $data['event_services'] = $this->mdl_schedule->get_event_services(['event_id' => $event_id]);
                $data['schedule_event'] = $schedule_event;
                $data['team_id'] = $schedule_event['team_id'];

                foreach ($data['event_services'] as $val) {
                    $event_services_id .= 'estimates_services.id = ' . $val['id'] . ' OR ';
                    $data['service_ids'][] = $val['event_service_id'];
                }
                $rtrimed = rtrim($event_services_id, ' OR ');
                $event_services_id = $rtrimed ? '(' . $rtrimed . ')' : null;
            }
        }

        //Get estimate informations - using common function from MY_Models;
        $estimate_id = $data['workorder_data']->estimate_id;

        $estimate_data = $this->mdl_estimates_orm->with('mdl_services_orm')->get_full_estimate_data(array('estimate_id' => $estimate_id));

        $data['estimate_data'] = $estimate_data[0];//$this->mdl_estimates_orm->_explodePdfFiles($estimate_data)[0];
		
        $this->load->model('mdl_vehicles');
        $data["tools"] = $this->mdl_vehicles->get_many_by(array('vehicle_trailer' => 2));


        $estClPath = 'uploads/clients_files/' . $data['estimate_data']->client_id . '/estimates/' . $data['estimate_data']->estimate_no . '/tmp/';
        $pdfFiles = $data['workorder_data']->wo_pdf_files ? json_decode($data['workorder_data']->wo_pdf_files) : [];
        $pictures['files'] = $pdfFiles;

        if (!$pictures['files'])
            $pictures['files'] = array();
        $schemePath = '';
        foreach ($pictures['files'] as $key => $file) {
            $type = getMimeType($file);
            if(strpos($file, 'scheme')) {
                $schemePath = $file;
                continue;
            }
            elseif (strripos($type, 'audio') !== false || strripos($type, 'video') !== false)
                continue;

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
                elseif (strpos($file, 'tree_inventory'))
                    $data['estFiles'] = ['Tree Inventory Map' => [$file]] + $data['estFiles'];
                else
                    $data['estFiles'][] = [$file];
            } else
                $pdfs[] = $file;
        }
        if(!empty($schemePath))
            $data['estFiles'] = ['Project Scheme' => [$schemePath]] + $data['estFiles'];

//        // add tree inventory map
//        $treeInventoryMapPath = inventory_screen_path($estimate_data[0]->client_id, $estimate_data[0]->lead_id . '_tree_inventory_map.png');
//        if(is_bucket_file($treeInventoryMapPath))
//            $data['estFiles'] = ['Tree Inventory Map' => [$treeInventoryMapPath]] + $data['estFiles'];
//        $treeInventoryMapPath = inventory_screen_path($estimate_data[0]->client_id, $estimate_data[0]->lead_id . '.png');
//        if(is_bucket_file($treeInventoryMapPath))
//            $data['estFiles'] = ['Tree Inventory Map' => [$treeInventoryMapPath]] + $data['estFiles'];

        //estimate services
        if ($event_services_id)
            $data['estimate_services_data'] = $this->mdl_estimates->find_estimate_services($estimate_id, $event_services_id);
        else
            $data['estimate_services_data'] = $this->mdl_estimates->find_estimate_services($estimate_id);

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

        //Get user_id and retrive estimator information:
        $user_id = $data['estimate_data']->user_id;
        $user = $this->mdl_user->get_usermeta(array('users.id' => $user_id));
        $data['user_data'] = $data['emp_data'] = $user ? $user->result()[0] : [];

        $this->load->model('mdl_employees');

        //Get client_id and retrive client's information:
        $id = $data['estimate_data']->client_id;
        $data['client_data'] = $this->mdl_clients->find_by_id($id);
        $data['client_contact'] = $this->mdl_clients->get_primary_client_contact($id);

        $this->load->model('mdl_schedule', 'mdl_schedule');
        $data['events'] = $this->mdl_schedule->get_events(array('schedule.event_wo_id' => $workorder_id));

        foreach ($data['events'] as $event) {
            $data['members'][$event['id']] = $this->mdl_schedule->get_team_members(['employee_team_id' => $event['team_id']]);
            $data['items'][$event['id']] = $this->mdl_schedule->get_team_items(array('equipment_team_id' => $event['team_id']));
        }

        list($data['hospital_address'], $data['hospital_name'], $data['hospital_coords']) = getNearestHospitalInfo($data['estimate_data']->lat, $data['estimate_data']->lon, implode(',', [$data['estimate_data']->lead_address, $data['estimate_data']->lead_city, $data['estimate_data']->lead_state]));

        $file = "workorder_" . $estimate_id . '.pdf';

        list($result, $view) = Modules::find('pdf_templates/' . config_item('company_dir') . '/workorder_pdf', 'includes', 'views/');

        if($result) {
            $html = $this->load->view('includes/pdf_templates/' . config_item('company_dir') . '/' . 'workorder_pdf', $data, TRUE);
        } else {
            $html = $this->load->view('includes/pdf_templates/workorder_pdf', $data, TRUE);
        }

        return array('file' => $file, 'html' => $html, 'pdf_files' => $pdfs);
    }

    //End invoice_pdf();

    public function ajax_save_file()
    {
        $path = 'uploads/payment_files/';

        $workorder_id = $this->input->post('id');
        $workorder = $this->mdl_workorders->find_by_id($workorder_id);
        $estimate_data = $this->mdl_estimates->find_by_id($workorder->estimate_id);
        if (empty($workorder))
            die(json_encode(array('status' => 'error')));

        $path .= $workorder->client_id . '/';
        $path .= $estimate_data->estimate_no . '/';

        $config['allowed_types'] = 'gif|jpg|jpeg|png|pdf|GIF|JPG|JPEG|PNG|PDF';
        $config['overwrite'] = FALSE;
        $this->load->library('upload');
        $config['upload_path'] = $path;
        $config['file_name'] = $_FILES['file']['name'];
        $this->upload->initialize($config);
        if (!$file = $this->upload->do_upload('file')){
            die(json_encode(array('status' => 'error')));
        }
        else {
            $filepath = 'uploads/payment_files/' . $workorder->client_id . '/' . $estimate_data->estimate_no . '/' . $this->upload->data()['file_name'];
            make_notes($workorder->client_id, 'Add Payment File for ' . $workorder->workorder_no . ' <a href="' . base_url() . $filepath . '">' . $config['file_name'] . '</a>', 'attachment', $estimate_data->lead_id);
            die(json_encode(array('status' => 'ok', 'filepath' => $filepath, 'filename' => $config['file_name'])));
        }
    }

    function ajax_add_worker()
    {
        $crew_id = intval($this->input->post('crew_id'));
        $employee_id = intval($this->input->post('employee_id'));
        $workorder_id = intval($this->input->post('workorder_id'));
        $this->mdl_workorders->insert_workorder_workers(array('workorder_id' => $workorder_id, 'employee_id' => $employee_id, 'crew_id' => $crew_id));
        die(json_encode(array('status' => 'ok')));
    }

    function ajax_delete_worker()
    {
        $crew_id = intval($this->input->post('crew_id'));
        $employee_id = intval($this->input->post('employee_id'));
        $workorder_id = intval($this->input->post('workorder_id'));
        $this->mdl_workorders->delete_workorder_workers(array('workorder_id' => $workorder_id, 'employee_id' => $employee_id, 'crew_id' => $crew_id));
        die(json_encode(array('status' => 'ok')));
    }

    function ajax_crew_date()
    {
        $estimate_crew_id = intval($this->input->post('estimate_crew_id'));
        $estimate_crew_date = strtotime($this->input->post('estimate_crew_date'));
        $this->mdl_estimates->update_estimate_crew($estimate_crew_id, array('estimate_crew_date' => $estimate_crew_date));
        die(json_encode(array('status' => 'ok')));
    }

    function partial_invoice_generate($workorder_id = NULL) {

        if(!$workorder_id)
            return FALSE;

        $data['title'] = $this->_title . ' - Partial Invoice';
        $data['workorder_data'] = $this->mdl_workorders->find_by_id($workorder_id);

        if(!$data['workorder_data'] || empty($data['workorder_data']))
            return FALSE;
        //Get estimate informations - using common function from MY_Models;
        $estimate_id = $data['workorder_data']->estimate_id;
        $data['estimate_data'] = $this->mdl_estimates_orm->getCompletedOnly($estimate_id);
        //estimate services

//        $data['estimate_services_data'] = $this->mdl_estimates->find_estimate_services($estimate_id, ['estimates_services.service_status' => 2]);

        //estimate services
        $estimateServicesData = $this->mdl_estimates->find_estimate_services($estimate_id, ['estimates_services.service_status ' => 2]);
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
        }
        $data['estimate_services_data'] = $estimateServicesData;

        $data['payments_data'] = $this->mdl_clients->get_payments(array('client_payments.estimate_id' => $estimate_id));
        //Discount data
        $data['discount_data'] = $this->mdl_clients->get_discount(array('discounts.estimate_id' => $estimate_id));

        //Get client_id and retrive client's information:
        $id = $data['workorder_data']->client_id;
        $data['client_data'] = $this->mdl_clients->find_by_id($id);
        $data['client_contact'] = $this->mdl_clients->get_primary_client_contact($id);

        $data['file'] = "Partial_Invoice_" . $data['estimate_data']->estimate_no . "-" . str_replace('/', '_', $data['client_data']->client_address) . '.pdf';

        list($result, $view) = Modules::find('pdf_templates/' . config_item('company_dir') . '/invoice_partial_pdf', 'includes', 'views/');
        if($result) {
            $data['html'] = $this->load->view('includes/pdf_templates/' . config_item('company_dir') . '/' . 'invoice_partial_pdf', $data, TRUE);
        } else {
            $data['html'] = $this->load->view('includes/pdf_templates/invoice_partial_pdf', $data, TRUE);
        }

        $brand_id = get_brand_id($data['estimate_data'], $data['client_data']);
        $data['html'] = ClientLetter::parseCustomTemplates(false, $data['html'], $brand_id, $workorder_id);

        return $data;
    }

    function partial_invoice_pdf($workorder_id)
    {
        //Get workorder informations - using common function from MY_Models;
        $pdf = $this->partial_invoice_generate($workorder_id);

        if(!$pdf)
            show_404();

        $this->load->library('mpdf');
        $css = file_get_contents('assets/css/global_invoice_pdf.css');
        $this->mpdf->WriteHTML($css,\Mpdf\HTMLParserMode::HEADER_CSS);
        $this->mpdf->WriteHTML($pdf['html']);

        $this->mpdf->Output($pdf['file'], 'I');
    }

    function send_pdf_to_email()
    {
        $workorder_id = $this->input->post('id');
        if (!(int)$workorder_id)
            die(json_encode(array('type' => 'error', 'message' => 'Workorder is not valid')));
		$cc = $bcc = '';
        $note['to'] = $to = $this->input->post('email_tags');
        if($this->input->post('cc') != null && $this->input->post('cc') != ''){
            $note['cc'] = $cc = $this->input->post('cc');
        }
        if($this->input->post('bcc') != null && $this->input->post('bcc') != ''){
            $note['bcc'] = $bcc = $this->input->post('bcc');
        }
        $note['subject'] = $subject = $this->input->post('subject');
        $text = $this->input->post('text');
        $note['from'] = $from_email = $this->input->post('email_from');

        $data['workorder_data'] = $this->mdl_workorders->find_by_id($workorder_id);

        if (!$data['workorder_data'] || empty($data['workorder_data']))
            die(json_encode(array('type' => 'error', 'message' => 'Estimate id is not defined')));

        $data['estimate_data'] = $this->mdl_estimates->find_by_id($data['workorder_data']->estimate_id);

        $check = check_receive_email($data['estimate_data']->client_id, $to);

        if($check['status'] != 'ok')
            die(json_encode(array('type' => $check['status'], 'message' => $check['message'])));

        if($data['estimate_data']->user_signature)
            $text .= $data['estimate_data']->user_signature;

        $data['client_data'] = $this->mdl_clients->find_by_id($data['estimate_data']->client_id);

        $pdf = $this->partial_invoice_generate($workorder_id);

        if(!$data)
            show_404();

        $this->load->library('mpdf');
        $this->mpdf->WriteHTML($pdf['html']);
        $attach = $file = '/tmp/' . $pdf['file'] . '.pdf';

        if(file_exists($file)) {
            $attach = $file = '/tmp/' . $pdf['file'] . '-' . uniqid() . '.pdf';
        }

        $this->mpdf->Output($file, 'F');

        $this->load->library('email');


        $toDomain = substr(strrchr($to, "@"), 1);

        if(array_search($toDomain, $this->config->item('smtp_domains')) !== FALSE) {
            $config = $this->config->item('smtp_mail');
            $note['from'] = $from_email = $config['smtp_user'];
        }
        $config['mailtype'] = 'html';
        $this->email->initialize($config);
        
        //checking if a file in not larger than default_pdf_size from the settings
        if(filesize($file) < config_item('default_pdf_size')
            && strlen(base64_encode(file_get_contents($file))) < config_item('default_pdf_size')){
            $this->email->attach($file);
        }else{
            $invoice_link = config_item('default_pdf_link_text');
            $invoice_link = str_replace('[DOCUMENT_NAME]', 'Invoice', $invoice_link);
            $link = '<a href="' . base_url("payments/invoice/" . md5($data["invoice_data"]->invoice_no . $data["invoice_data"]->client_id)) . '">here</a>';
            $invoice_link = str_replace('[DOCUMENT_LINK]', $link, $invoice_link);
            $text .= $invoice_link;
        }

        $text .= '<br><div style="text-align:center; font-size: 10px;"> If you no longer wish to receive these emails you may ' .
            '<a href="' . $this->config->item('unsubscribe_link') . md5($data['estimate_data']->client_id) . '">unsubscribe</a> at any time.</div>';

        $this->email->to($to);
        if($cc && $cc != '')
            $this->email->cc($cc);
        if($bcc && $bcc != '')
            $this->email->bcc($bcc);
        $this->email->from($from_email, $this->config->item('company_name_short'));
        $this->email->subject($subject);

        $this->email->message($text);
        $status['type'] = 'success';
        $status['message'] = 'Email sent. Thanks';

        $send = $this->email->send();

        if (!is_array($send) || isset($send['error'])) {
            $status['type'] = 'error';
            $status['message'] = 'Oops! Email send error. Please try again';

            if (isset($send['error'])) {
                $status['message'] = $send['error'];
            }

            die(json_encode($status));
        }

        $entities = [
            ['entity' => 'workorder', 'id' => $data['workorder_data']->id],
            ['entity' => 'client', 'id' => $data['estimate_data']->client_id]
        ];
        $this->email->setEmailEntities($entities);

        $note_id = make_notes(
            $data['estimate_data']->client_id,
            'Partial invoice ' . $data['estimate_data']->estimate_no . ' sent to "' . $to . '".',
            'email',
            (int)$data['estimate_data']->estimate_no,
            $this->email
        );

        $name = uniqid();
        $dir = 'uploads/notes_files/' . $data['estimate_data']->client_id .'/' . $note_id . '/';

        $pattern = "/<body>(.*?)<\/body>/is";
        preg_match($pattern, $text, $res);
        $note['text'] = isset($res[1]) ? $res[1] : $text;

        $this->mpdf->Output('/tmp/attach_' . $name . '.pdf', 'F');
        @unlink($file);
        bucket_move('/tmp/attach_' . $name . '.pdf', $dir . 'attach_' . $name . '.pdf', ['ContentType' => 'application/pdf']);
        @unlink('/tmp/attach_' . $name . '.pdf');
        bucket_write_file($dir . $name . '.html', $this->load->view('clients/note_file', $note, TRUE), ['ContentType' => 'text/html']);

        die(json_encode($status));
    }

    /******************WO STATUSES*******************/
    /*
    public function status()
    {
        if ($this->session->userdata('user_type') != "admin" && $this->session->userdata('WO_STS') != 1) {
            show_404();
        }
        $this->load->model('mdl_crews');
        $data['title'] = "Status";

        //get employees
        $data['statuses'] = $this->mdl_workorders->get_all_statuses();
        $this->load->view('index_status', $data);
    }

    function ajax_save_status()
    {
        if ($this->session->userdata('user_type') != "admin" && $this->session->userdata('WO_STS') != 1) {
            show_404();
        }
        $id = $this->input->post('status_id');

        $data['wo_status_name'] = strip_tags($this->input->post('status_name', TRUE));
        $data['wo_status_color'] = strip_tags($this->input->post('status_color', TRUE));
        $data['wo_status_active'] = 1;

        $data['is_confirm_by_client'] = (int)$this->input->post('is_confirm_by_client');
        $data['is_finished_by_field'] = (int)$this->input->post('is_finished_by_field');
        $data['is_delete_invoice'] = (int)$this->input->post('is_delete_invoice');

        $data['is_protected'] = 0;
        if($data['is_finished_by_field'] || $data['is_confirm_by_client'] || $data['is_delete_invoice'])
            $data['is_protected'] = 1;

        if ($id != '') {
            $status = $this->mdl_workorders->get_all_statuses(['wo_status_id'=>$id], true);
            if($status['is_default'] || $status['is_finished'])
                $data['is_protected'] = 1;

            $this->mdl_administration->update_status($id, $data);
            die(json_encode(array('status' => 'ok')));
        }
        $this->mdl_administration->insert_status($data);
        die(json_encode(array('status' => 'ok')));
    }

    function ajax_delete_status()
    {
        if ($this->session->userdata('user_type') != "admin" && $this->session->userdata('WO_STS') != 1) {
            show_404();
        }
        $id = $this->input->post('status_id');
        $status = $this->input->post('status');
        if ($id != '')
            $this->mdl_administration->update_status($id, array('wo_status_active' => $status));
        die(json_encode(array('status' => 'ok')));
    }

    function ajax_priority_statuses()
    {
        if ($this->session->userdata('user_type') != "admin") {
            show_404();
        }
        $data = $this->input->post('data');
        if (empty($data))
            die(json_encode(array('status' => 'error')));
        foreach ($data as $key => $val) {
            if ($val)
                $updateBatch[] = array('wo_status_id' => $val['id'], 'wo_status_priority' => $val['priority']);
        }
        if (empty($updateBatch))
            die(json_encode(array('status' => 'error')));
        if ($this->mdl_workorders->update_priority($updateBatch))
            die(json_encode(array('status' => 'ok')));
        die(json_encode(array('status' => 'error')));
    }
    */
    /******************WO STATUSES*******************/

    function ajax_pdf_file()
    {
        $dir = $this->input->post('name');
        $estimate_id = $this->input->post('estimate_id');
        $workorder = $this->mdl_workorders->find_by_fields(['estimate_id' => $estimate_id]);
        $files = $workorder->wo_pdf_files ? json_decode($workorder->wo_pdf_files) : [];
        $key = array_search($dir, $files);
        if ($key !== FALSE)
            unset($files[$key]);
        else
            $files[] = $dir;
        $files = array_values($files);
        $str = json_encode($files);
        $this->mdl_workorders->update($workorder->id, array('wo_pdf_files' => $str));
    }

    function get_workorder_teams()
    {
        if (!$this->input->post('id'))
            return $this->output->set_output(json_encode(['status' => 'error']));

        $this->load->model('mdl_schedule', 'mdl_schedule');

        $wdata = ['schedule.event_wo_id' => $this->input->post('id')];
        $data['events'] = $this->mdl_schedule->get_event_members($wdata);

        $response = ['status' => 'success', 'data' => $data['events']];
        return $this->output->set_output(json_encode($response));
    }

    //ALTER TABLE `schedule` ADD `event_damage` DOUBLE NOT NULL DEFAULT '0.00' AFTER `event_services`;
    function set_workorder_damages()
    {

        if (!$this->input->post('event_id'))
            return $this->output->set_output(json_encode(['status' => 'error']));

        $this->load->model('mdl_schedule', 'mdl_schedule');
		$events = [];
        foreach ($this->input->post('event_id') as $key => $event) {
            $data = [
                'id' => $event,
                'event_damage' => floatval(element('damage-' . $event, $this->input->post(), 0.00)),
                'event_complain' => floatval(element('complain-' . $event, $this->input->post(), 0.00))
            ];
			$events[] = $data;
            $this->mdl_schedule->save_event($data, true);
        }

        $response = ['status' => 'success', 'data' => $events];
        return $this->output->set_output(json_encode($response));

    }

    function delete_workorder($id)
    {
        if ($this->session->userdata('user_type') != "admin") {
            show_404();
        } else {
            $invoice = $this->mdl_invoices->find_by_field(['workorder_id' => $id]);
            $wo = $this->mdl_workorders->get_workorders('', '', '', '', array('workorders.id' => $id))->row_array();
            $client_id = $wo['client_id'];

            if (!empty($invoice)) {
                $this->mdl_invoices->delete_invoice_new((int)$invoice->id);
                pushJob('quickbooks/invoice/syncinvoiceinqb', serialize(['id' => $invoice->id, 'qbId' => $invoice->invoice_qb_id]));
            }

            if ($this->mdl_workorders->delete_workorder_new($id)) {

                $this->mdl_estimates->update_estimates(array('status' => 4), array('estimate_id' => $wo['estimate_id']));
                $link = base_url($client_id);
                $mess = message('success', 'Workorder & Invoice were successfully deleted!');
                $this->session->set_flashdata('user_message', $mess);
                redirect($link);
            }
        }
    }

    function changeDC()
    {
        $this->load->model('mdl_schedule');
        $result['status'] = 'ok';

        $event_id = $this->input->post('pk');
        $value = $this->input->post('value') ? $this->input->post('value', true) : NULL;
        $name = $this->input->post('name', true);

        $udata = [$name => $value];
        if (is_array($value))
            $udata = $value;

        $this->mdl_schedule->save_event(['id' => $event_id, $name => $value], TRUE);

        die(json_encode($result));
    }

    function update_notes(){
        $request = request();
        if(!$request->input('id'))
            return $this->response(['message'=>'Request is not valid'], 400);

        $workorder = Workorder::with('estimate')->find($request->input('id'));
        if(!$workorder)
            return $this->response(['message'=>'Request is not valid'], 400);

        if(is_string($request->input('wo_office_notes'))){
            $workorder->wo_office_notes = trim($request->input('wo_office_notes'));
            $workorder->save();
        }
        if(is_string($request->input('estimate_crew_notes'))){
            $workorder->estimate->estimate_crew_notes = trim($request->input('estimate_crew_notes'));
            $workorder->estimate->save();
        }
        return $this->response(['message'=>'Note saved', 'workorder'=>$workorder], 200);
    }
}
//end of file workorders.php
