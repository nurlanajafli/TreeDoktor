<?php    if (!defined('BASEPATH'))
	exit('No direct script access allowed');

use application\modules\clients\models\Client;
use application\modules\clients\models\Tag;
use application\modules\leads\models\Lead;
use application\modules\user\models\User;
use application\modules\references\models\Reference;
use Illuminate\Http\JsonResponse;

class Leads extends MX_Controller
{
//*******************************************************************************************************************
//*************
//*************																						Leads Controller;
//*************
//*******************************************************************************************************************	

	function __construct()
	{

		parent::__construct();


		if (!isUserLoggedIn()) {
			redirect('login');
		}
        if (is_cl_permission_none())
            redirect('dashboard');

		$this->_title = SITE_NAME;

		//load all common models and libraries here;
		$this->load->helper('leads');
		$this->load->model('mdl_leads', 'mdl_leads');
		$this->load->model('mdl_estimates', 'mdl_estimates');
		$this->load->model('mdl_user', 'mdl_users');
		$this->load->model('mdl_clients', 'mdl_clients');
		$this->load->model('mdl_reports', 'mdl_reports');
		$this->load->model('mdl_workorders');
		$this->load->model('mdl_invoices');
		$this->load->model('mdl_leads_status');
		$this->load->model('mdl_client_payments');

		//Load Libraries
		$this->load->library('form_validation');
		$this->load->library('googlemaps');
        $this->load->library('Common/EstimateActions');
	}

//*******************************************************************************************************************
//*************
//*************																							Index Function;
//*************
//*******************************************************************************************************************

    public function index()
    {
        if (is_cl_permission_none()) {
            redirect('dashboard');
        }

        if (request()->ajax()) {
            if (is_null(request()->query('my_leads'))) {
                $this->ajax_search_leads();
            } else {
                $this->ajax_search_my_leads();
            }
            return;
        }
        // Set title
        $data['title'] = $this->_title . ' - Leads';
        // Set menu status
        $data['menu_leads'] = "active";

        $data['access_token'] = $this->mdl_settings_orm->get_by('stt_key_name', 'accessTokenKey')->stt_key_value;
        $data['select2Tags'] = Tag::select2FormatData();
        $data['tagsExpandLimit'] = Tag::TAGS_EXPAND_LIMIT;

        $data['estimators'] = [];
        $estimators = $this->mdl_estimates->get_active_estimators();
        if ($estimators) {
            $data['estimators'] = $estimators;
        }

        $this->load->view('leads/index', $data);
    }

    private function ajax_search_leads()
    {
        $request = request();

        $orderBy = ['leads.lead_id' => 'DESC'];
        $where = $whereOr = [];
        $status = $request->input('approval');
        if($status === 'true')
            $where[]['lead_statuses.lead_status_for_approval'] = 1;
        else {
            $whereOr[] = [
                'lead_statuses.lead_status_default' => 1,
                'lead_statuses.lead_status_draft' => 1
            ];
        }
        if(is_cl_permission_owner() || is_cl_permission_none()) {
            $where[]['leads.client_id'] = -1;
        }
        $leadsQuery = (new Lead())->getLeadsQuery($where, $whereOr, '', $orderBy);
        $totalQueryLeads = Lead::countAggregate($leadsQuery);
        $leads = $leadsQuery->offset($request->start)->limit($request->length)->get();

        return $this->response([
            'data' => (new JsonResponse($leads)),
            'recordsTotal' => $totalQueryLeads,
            'recordsFiltered' => $totalQueryLeads
        ]);
    }

    private function ajax_search_my_leads()
    {
        $request = request();
        $orderColumnTargetIndex = $request->order[0]['column'];
        $orderColumnName = $request->columns[$orderColumnTargetIndex]['name'];
        $orderDir = $request->order[0]['dir'];
        $whereOr = [];
        $orderBy = ['leads.lead_id' => 'DESC'];

        $status = $request->input('approval');
        if($status === 'true')
            $where[]['lead_statuses.lead_status_for_approval'] = 1;
        else {
            $whereOr[] = [
                'lead_statuses.lead_status_default' => 1,
                'lead_statuses.lead_status_draft' => 1
            ];
        }
        $where[]['leads.lead_estimator'] = (int)request()->user()->id;
        $myLeadsQuery = Lead::getMyLeadsQuery($where, $whereOr, $orderBy);
        $totalQueryLeads = Client::countAggregate($myLeadsQuery);
        $leads = $myLeadsQuery->get();

        return $this->response([
            'data' => (new JsonResponse($leads)),
            'recordsTotal' => $totalQueryLeads,
            'recordsFiltered' => $totalQueryLeads,
        ]);
    }

    function for_approval()
	{
		
		$this->load->model('mdl_services');
		$this->load->model('mdl_leads_services');
		$this->load->model('mdl_client_tasks');
		
		$data['title'] = $this->_title . ' - Leads For Approval';
		$data['menu_leads'] = "active";
		$wdata = array();
		//$data['my_leads'] = $this->mdl_leads->get_my_leads($select = '', $wdata);
		$wdata['lead_statuses.lead_status_for_approval'] = 1;
		$data['leads'] = $this->mdl_leads->get_leads($wdata, '', 'lead_date_created DESC');
		//echo '<pre>'; var_dump($data['leads']->result_array()); die;
		
		$data['lead_statuses'] = $this->mdl_leads_status->with('mdl_leads_reason')->get_all();
		$data['estimators'] = $this->mdl_users->get_usermeta(array('active_status' => 'yes','emp_status' => 'current', 'users.id <>' => 0, 'emp_field_estimator'=>'1'))->result();
				
		$data['lead_services'] = [];
		foreach($data['leads']->result() as $lead) {
			$est_services = $this->mdl_leads_services->get_many_by(['lead_id' => $lead->lead_id]);
			$data['schedule_appointments'][$lead->lead_id] = $this->mdl_client_tasks->get_all(['task_lead_id'=>$lead->lead_id]);
			
			$data['lead_services'][$lead->lead_id] = '';
			if(!empty($est_services)){
				foreach($est_services as $k=>$v) {
					$data['lead_services'][$lead->lead_id] .= $v->services_id . '|';
				}
				
				$data['lead_services'][$lead->lead_id] = trim($data['lead_services'][$lead->lead_id], '|');
			}
		}
				
		$services = $this->mdl_services->order_by('service_priority')->get_many_by(array('service_parent_id' => NULL, 'service_status' => 1));
		if(count($services))
		{
			foreach($services as $k=>$v)
			{
				$data['services'][$k]['key'] = $v->service_id;
				$data['services'][$k]['name']  = $v->service_name;
			}
			
		}  
		$data['services'] = json_encode($data['services']);

		
		
		$this->load->view("index", $data);
	}


//*******************************************************************************************************************
//*************
//*************																					Leads_Mapper Function;
//*************
//*******************************************************************************************************************	

	public function leads_mapper()
	{

        $this->load->model('mdl_leads_reason');
		//Page Presets
		$data['title'] = $this->_title . ' - Leads';
		$data['menu_leads'] = "active";

		//Set the map:
		$config['center'] = config_item('map_center');
		$config['zoom'] = 'auto';
		$this->googlemaps->initialize($config);

		//Get required leads data:

		$arr = $this->mdl_leads->get_leads(array('lead_postpone_date <=' => date('Y-m-d')), 'New', 'leads.lead_id ASC');

        $all_lead_statuses = $this->mdl_leads_status->with('mdl_leads_reason')->get_many_by(['lead_status_active' => 1]);

		//echo '<pre>'; var_dump($arr->result_array()); die;
        $brand_id = get_brand_id([], $arr);

        $arr = ($arr && $arr->num_rows()) ? $arr->result_array() : array();
		$this->load->model('mdl_user', 'mdl_users');
		$usersData = $this->mdl_users->get_usermeta(array('active_status' => 'yes', 'emp_field_estimator'=>'1', 'system_user' => 0));
		$users = array();
		foreach ($usersData->result_array() as $user) {
			$users[$user['id']] = $user;
		}
		$active_users = $usersData->result();
		//$this->load->model('mdl_voices');
		$this->load->model('mdl_sms');
		//$voice = $this->mdl_voices->get(4);
		$data['maxLeadId'] = 0;
		$data['sms'] = array();
        if(config_item('messenger')) {
            $sms = $this->mdl_sms->get(3);
            if($sms)
                $data['sms'] = $sms;
        }

		//Creating the markers for leads:
		foreach ($arr as $k=>$row) {
			if(!isset($arr[$k+1])) 
				$data['maxLeadId'] = $row['lead_id'];
			$lastEtimate = FALSE;//$this->mdl_estimates->get_last_client_estimate($row['client_id']);
			$marker_style = NULL;
			$client_id = $row['client_id'];
			$name = $row['client_name'];
			$phone = numberTo($row['cc_phone']);
			$street = $row['lead_address'];
			$state = $row['lead_state'];
			$city = $row['lead_city'];
            $country = $row['lead_country'];

            if(!$row['latitude'] || !$row['longitude'])
			{
				$coords = get_lat_lon($row['lead_address'], $row['lead_city'], $row['lead_state'], $row['lead_zip'], $row['lead_country']);
				$row['latitude'] = $coords['lat'];
				$row['longitude'] = $coords['lon'];
			}
			$this->load->helper('user');
			$address = ($row['latitude'] && $row['longitude']) ? $row['latitude'] . ', ' . $row['longitude'] : lead_address_map($row);
			
			$lead_date = getDateTimeWithDate($row['lead_date_created'], 'Y-m-d H:i:s', true);
			$lead_body_dirty = $row['lead_body'];
			$lead_body = preg_replace("/[^\p{L}\p{N}]/u", ' ', $lead_body_dirty);
			$lead_id = $row['lead_id'];
			$lead_priority = $row['lead_priority'];
			$lead_status = $row['lead_status_id'];
			$count_workorders = $row['count_workorders'];
			$lead_call = $row['lead_call'];
			$estimator_id = (int)$row['lead_estimator'];
			$estimator_first_name = $row['firstname'];
			$estimator_last_name = $row['lastname'];
			$leadDays = (string)round((strtotime(date('Y-m-d')) - strtotime(date('Y-m-d', strtotime($row['lead_date_created'])))) / 86400);

			//Base Marker information:
			$marker_link = base_url('client/' . $client_id);

			//$pinType = 'd_map_pin_letter';
			$preChstData = $afterChstData = $star = NULL;
			
			$tree = FALSE;
			if($count_workorders) {
				$star = TRUE;
			}
			if($row['lights_installation'] == 'yes') {
				$tree = TRUE;
			}
			//Priority Markker style:	
			if ($lead_priority == "Priority") {
				//$marker_style = "https://chart.apis.google.com/chart?chst=$pinType&chld=$preChstData$leadDays|FDF569$afterChstData";
				$marker_style = mappin_svg('#FDF569', $leadDays, $star, '#000', $tree); 
				//$marker_style = "http://maps.google.com/mapfiles/ms/icons/yellow-dot.png";
				$priority_indicator = " - Priority";
			}
			if ($lead_priority == "Emergency") {
				//$marker_style = "https://chart.apis.google.com/chart?chst=$pinType&chld=$preChstData$leadDays|FD7567$afterChstData";
				$marker_style = mappin_svg('#FD7567', $leadDays, $star, '#000', $tree); 
				//$marker_style = "http://maps.google.com/mapfiles/ms/icons/red-dot.png";
				$priority_indicator = " - Emergency";
			}
			if ($lead_priority == "Regular") {
				//$marker_style = "https://chart.apis.google.com/chart?chst=$pinType&chld=$preChstData$leadDays|00E64D$afterChstData";
				$marker_style = mappin_svg('#00E64D', $leadDays, $star, '#000', $tree); 
				//$marker_style = "http://maps.google.com/mapfiles/ms/icons/green-dot.png";
				$priority_indicator = "";
			}
			$showAs = ($lead_priority == 'Priority' || $lead_priority == 'Emergency') ? $lead_priority : $estimator_id;
			$showAs = ($showAs == 'none' || !$showAs) ? 0 : $showAs;
			$marker_content = "<div id='" . $lead_id . "' class='marker taskMarker'><strong data-user='" . $showAs . "'><a href='" . $marker_link . "' target='_blank'>" . $name . "</a>" . $priority_indicator . "</strong>";

			if($lastEtimate)
				$marker_content .= ' <span style="margin-left: 10px;"><strong>Last Estimator: ' . $lastEtimate->firstname . ' ' . $lastEtimate->lastname . '</strong></span>';

			$marker_content .= "<br/>Assigned to:&nbsp;";
			if ($estimator_id) {
				$marker_content .= "<strong>" . $estimator_first_name . "&nbsp;" . $estimator_last_name . "</strong>";
			} else {
				$marker_content .= "<strong>Not Assigned</strong>";
			}

			if(config_item('messenger') && (isset($sms) && (is_object($sms) || !empty($sms))) && $this->session->userdata('twilio_worker_id'))
			{
				$marker_content .= "<div class='d-inline-block pull-right btn btn-info addLeadSms'";
				if(isset($row['cc_phone']) && $row['cc_phone'])
					$marker_content .= " data-phone ='".substr($row['cc_phone'], 0, 10)."'";
				elseif(isset($row['client_mobile']) && $row['client_mobile'])
					$marker_content .= " data-phone ='".$row['client_mobile']."'";
					
				$marker_content .= " data-email='".$row['cc_email']."'";
				$marker_content .= " data-name='".$row['cc_name']."'";
				$marker_content .= " data-company='".brand_name($brand_id)."'";
				$marker_content .= " data-company-phone='".brand_phone($brand_id)."'";
				$marker_content .= " data-href='#sms-".$sms->sms_id."' >";
				$marker_content .= " SMS to " . $row['cc_name'] . "</div>" ;
			}

			$marker_content .= "<br>Date:&nbsp;" . $lead_date . "<br><abbr title='Phone'>Phone:&nbsp;</abbr>" . $phone . "<br>Address:&nbsp;" . $street . ", " . $city . ', ' . $state . ', ' . $country . "<br>";

			$servicesList = NULL;
			foreach($this->config->item('leads_services') as $block) {
				foreach($block['list'] as $item) {
					if($row[$item['name']] == 'yes')
						$servicesList .= $item['label'] . ', ';
				}
			}

			$servicesList = rtrim($servicesList, ', ');
			if($servicesList)
				$marker_content .= "Services:&nbsp;" . $servicesList . "<br>";

			$marker_content .= ucfirst($lead_body);
			$marker_content .= "<br>";
			$marker_content .= "<div class='p-top-10'>";
            $color = '#FFFFFF';
            $marker_content .= "<div class='form-inline p-top-10'><form action='#' class='change_lead_status'><span class='lead_status'>Status:<br><select name='lead_status_change' class='lead_status_change m-t-xs m-b-xs'>";
            foreach($all_lead_statuses as $key=>$val) {
                $sel = "";
                if($lead_status == $val->lead_status_id){
                    $sel = 'selected="selected"';
                }
                $marker_content .= "<option value='$val->lead_status_id' " . $sel . ">" . $val->lead_status_name . "</option>";
            }
            $marker_content .= "</select>";
            foreach($all_lead_statuses as $key=>$val) {

                $reason_block = false;
                if (isset($val->mdl_leads_reason) && count($val->mdl_leads_reason)) {
                    $reason_block = "<span class='lead_reason_change_block' style='display:none;'>&nbsp;Reason:</span><select name='lead_reason_change-" . $val->lead_status_id ."' class='lead_reason_change lead_reason_change-" . $val->lead_status_id ." m-t-xs m-b-xs' style='display:none;'>";
                    foreach ($val->mdl_leads_reason as $zkey => $zval) {
                        $reason_block .= "<option value='$zval->reason_id'>" . $zval->reason_name . "</option>";
                    }
                    $reason_block .= "</select>";
                    $marker_content .= $reason_block;
                }
            }
            $marker_content .= "<button class='pull-right btn btn-success submitLead' style='display:none; padding: 6px 12px;'><span class='btntext'>Save</span></button><button class='pull-right btn submitLead' style='display:none; padding: 6px 12px; margin-right:10px;'>Close</button></form></div><br>";
			// Leads Assignment:
            if (request()->user()->user_type == "admin" || is_cl_permission_all()) {

				//Get list of active users:
				if (!empty($active_users)) {

					$marker_content .= "<div class='form-inline p-top-10 inline'>";

					$marker_content .= "<form class='assign_lead' action='#'>";
					$marker_content .= "<input type='hidden' name='assigned_what' value='" . $lead_id . "'>";
					$marker_content .= "<select name='assigned_to'>";
					$marker_content .= "<option value='none'>Not assigned</option>";
					foreach ($active_users as $active_user) {
						if ($active_user->id == $estimator_id) {
							$selected = "selected";
							//$marker_style = 'https://chart.apis.google.com/chart?chst=d_map_pin_letter&chld='.$leadDays.'|' . str_replace('#', '', $active_user->color);
							$marker_style = mappin_svg($active_user->color, $leadDays, $star, '#000', $tree);
							//$marker_style = "http://maps.google.com/mapfiles/ms/icons/green-dot.png";
							$color = $active_user->color;
						} else {
							$selected = " ";
						}
						$marker_content .= "<option value='" . $active_user->id . "' " . $selected . ">" . $active_user->firstname . "&nbsp;" . $active_user->lastname . " </option>";
					}
					$marker_content .= "</select> &nbsp;&nbsp;";
					$marker_content .= "<button class='btn btn-info assign_lead_btn' style='display: inline-block; padding: 6px 12px;'><span class='btntext'>Assign</span></button>";
					$marker_content .= "</form>";
					$marker_content .= "</div>";
				}
			}
            $checked = $checked_call = '';
            $class = $class_call = '';

            if($lead_call)
            {
                $checked_call = ' checked="checked"';
                $class_call = ' checked';
            }
            $marker_content .= '<div class="checkbox m-l-md" style="display:inline-block;width: 112px;"><label class="checkbox-custom"><input type="checkbox" class="callLead" data-lead_id="' . $lead_id . '" name="lead_call"' . $checked_call . '><i class="fa fa-fw fa-square-o' . $class_call . '"></i>Call The Client</label></div>';

            $marker_content .= anchor('estimates/new_estimate/' . $lead_id, '&nbsp;<i class="icon-leaf icon-white" title="Create an estimate"></i>&nbsp;Create Estimate&nbsp;', 'class="btn btn-success btn"');
			//Priority Markker style:	
			if ($estimator_id == "none" || !$marker_style) {
				$marker_style = mappin_svg('#00E64D', $leadDays, $star, '#000', $tree);
				//$marker_style = "https://chart.apis.google.com/chart?chst=$pinType&chld=$preChstData$leadDays|00E64D$afterChstData";
				//$marker_style = "http://maps.google.com/mapfiles/ms/icons/green-dot.png";
			}
			if ($lead_priority == "Priority") {
				$marker_style = mappin_svg('#FDF569', $leadDays, $star, '#000', $tree);
				//$marker_style = "https://chart.apis.google.com/chart?chst=$pinType&chld=$preChstData$leadDays|FDF569$afterChstData";
				//$marker_style = "http://maps.google.com/mapfiles/ms/icons/yellow-dot.png";
			}
			if ($lead_priority == "Emergency") {
				$marker_style = mappin_svg('#FD7567', $leadDays, $star, '#000', $tree);
				//$marker_style = "https://chart.apis.google.com/chart?chst=$pinType&chld=$preChstData$leadDays|FD7567$afterChstData";
				//$marker_style = "http://maps.google.com/mapfiles/ms/icons/red-dot.png";
			}



			$pinType = 'd_map_pin_icon';
			$preChstData = $afterChstData = $star = NULL;

			if($count_workorders) {
				$pinType = 'd_map_xpin_icon';
				$preChstData = 'pin_star|';
				$afterChstData = '|FDF569';
				$star = TRUE;
			}

			if ($lead_call) {
				$marker_style = mappin_svg($color, 'phone', $star, '#000', $tree);
				//$marker_style = "https://chart.apis.google.com/chart?chst=$pinType&chld=".$preChstData."glyphish_phone|" . $color . "$afterChstData";
				//$marker_style = "http://maps.google.com/mapfiles/ms/icons/green-dot.png";
			}

			//if($lead_priority =="Regular") {$marker_style = "http://chart.apis.google.com/chart?chst=d_map_pin_letter&chld=%E2%80%A2|6991FD";}


            $marker_content .= "</div>";
			$marker = array();
			$marker['position'] = $address;//$row['latitude'] . ',' . $row['longitude'];
			$marker['infowindow_content'] = $marker_content;
			$marker['icon'] = $marker_style;
			$marker['zIndex'] = 1;
            $marker['lead_id'] = $row['lead_id'];
			//echo '<pre>'; var_dump($marker_content); die;
				$this->googlemaps->add_marker($marker);
		}

		

		
		$this->load->model('mdl_client_tasks');
		$wdata = array();
		$wdata['task_status'] = 'new';
		$wdata['task_no_map'] = 0;
		$wdata['employees.emp_field_estimator'] = '1';
		//if($this->session->userdata('TSKS') === '2')
			//$wdata['task_author_id'] = $this->session->userdata['user_id'];
		$arr = $this->mdl_client_tasks->get_all($wdata);
		foreach ($arr as $row) {
			$marker_style = NULL;
			$marker_date['client_id'] = $row['task_client_id'];
			$marker_date['name'] = $row['client_name'];
			$marker_date['category_name'] = $row['category_name'];
			$marker_date['phone'] = numberTo($row['cc_phone']);
			$marker_date['street'] = $row['task_address'];
			$marker_date['city'] = $row['task_city'];
			$marker_date['status'] = $row['task_status'];
			$marker_date['address'] = $marker_date['street'] . ", " . $marker_date['city'];
			$marker_date['task_date'] = getDateTimeWithDate($row['task_date_created'], 'Y-m-d');
			
			$marker_date['task_schedule_date'] = $marker_date['task_schedule_start'] = $marker_date['task_schedule_end'] = NULL;
			if($row['task_date'])
			{
                $marker_date['task_schedule_date'] = date('m/d', strtotime($row['task_date']));
			    $taskDate = getDateTimeWithDate($row['task_date'], 'Y-m-d');
//				$marker_date['task_schedule_date'] = $taskDate;
//				$marker_date['task_schedule_start'] = $row['task_start'];
//				$marker_date['task_schedule_end'] = $row['task_end'];
				$marker_date['task_schedule_start'] = getTimeWithDate($row['task_start'], 'H:i:s', true);
				$marker_date['task_schedule_end'] = getTimeWithDate($row['task_end'], 'H:i:s', true);
			}
			$marker_date['task_body_dirty'] = $row['task_desc'];
			$marker_date['task_body']= preg_replace("/[^\p{L}\p{N}]/u", ' ', $marker_date['task_body_dirty']);
			$marker_date['task_id'] = $row['task_id'];
			$marker_date['task_name_creator'] = $row['firstname'] . ' ' . $row['lastname'];
			$marker_date['task_assigned'] = $row['ass_firstname'] . ' ' . $row['ass_lastname'];
			//Base Marker information:
			$marker_date['marker_link'] = base_url($marker_date['client_id']);
			$marker_content = str_replace(array("\n", "\r"), '', $this->load->view('tasks/marker', $marker_date, TRUE));
			if ($row['ass_color'])
				$marker_style = task_pin($row['ass_color'],  ($marker_date['task_schedule_date']) ? $marker_date['task_schedule_date'] : 'T', FALSE, '#000');
				//$marker_style = "http://maps.google.com/mapfiles/ms/icons/blue-dot.png";
				//$marker_style = 'https://chart.apis.google.com/chart?chst=d_map_xpin_letter_withshadow&chld=pin|T|' . str_replace('#', '', $row['ass_color']) . '|000000|';
			else
				$marker_style = task_pin('#ADDE63', ($marker_date['task_schedule_date']) ? $marker_date['task_schedule_date'] : 'T', FALSE, '#000');
				//$marker_style = "http://maps.google.com/mapfiles/ms/icons/blue-dot.png";
				//$marker_style = "https://chart.apis.google.com/chart?chst=d_map_xpin_letter_withshadow&chld=pin|T|ADDE63|000000";
			 
			$marker = array();

			if($row['task_latitude'] && $row['task_longitude'] && $row['task_longitude'] < 0)
				$marker['position'] = $row['task_latitude'] . ',' . $row['task_longitude'];
			else
				$marker['position'] = $marker_date['address'];

			$marker['infowindow_content'] = $marker_content;
			$marker['icon'] = $marker_style;
            $marker['zIndex'] = 1;
            $marker['lead_id'] = $row['lead_id'];
			$this->googlemaps->add_marker($marker);
		}

        if(config_item('leads_circles')) {
            $circles = config_item('leads_circles');
            foreach ($circles as $circle) {
                $this->googlemaps->add_circle([
                    'center' => $circle['lat'] . ', ' . $circle['lng'],
                    'radius' => $circle['radius'],
                    'strokeColor' => isset($circle['strokeColor']) ? $circle['strokeColor'] : "#FF0000",
                    'strokeOpacity' => isset($circle['strokeOpacity']) ? $circle['strokeOpacity'] : 0.3,
                    'strokeWeight' => isset($circle['strokeWeight']) ? $circle['strokeWeight'] : 1,
                    'fillColor' => isset($circle['fillColor']) ? $circle['fillColor'] : "#FF0000",
                    'fillOpacity' => isset($circle['fillOpacity']) ? $circle['fillOpacity'] : 0.1,
                ]);
            }
        }
		
		//http://chart.apis.google.com/chart?chst=d_map_xpin_letter_withshadow&chld=pin|T|ADDE63|000000


		if(is_array(config_item('leads_polylines'))) {
		    foreach (config_item('leads_polylines') as $polyline)
                $this->googlemaps->add_polyline(['points' => $polyline]);
        }

		$data['map'] = $this->googlemaps->create_map();
		$data['users'] = $this->mdl_users->get_usermeta(array('active_status' => 'yes', 'employees.emp_field_estimator' => '1'))->result();

		$this->load->model('mdl_equipments');
		$trackingItems = $this->mdl_equipments->get_items('item_id = 25 OR item_id = 15 OR item_id = 68');
		$data['vehicles'] = array();
		foreach($trackingItems as $item)
			$data['vehicles'][] = array('item_code' => $item->item_code, 'item_name' => $item->item_name, 'item_tracker_name' => $item->item_tracker_name);

		$this->load->view('map', $data);

		//var_dump($arr);

	}

	
	
//*******************************************************************************************************************
//*************
//*************																							Assign lead;
//*************
//*******************************************************************************************************************

	public function assign_lead()
	{
		// Validation:
		$this->form_validation->set_rules('assigned_what', '', 'required');
		$this->form_validation->set_rules('assigned_to', '', 'required');
		//Validation Passed:
		if ($this->form_validation->run()) {

			$update_data = array('lead_estimator' => (int)$this->input->post('assigned_to'), 'lead_assigned_date' => date('Y-m-d'));
			$wdata = array('lead_id' => $this->input->post('assigned_what'));
			$this->mdl_leads->update_leads($update_data, $wdata);
			$this->session->set_flashdata('message', 'Done. The lead was assigned');
			if ($this->input->post('client_id'))
                die(json_encode(['status' => 'ok', 'url' => base_url($this->input->post('client_id'))]));
			else{
			    $lead = Lead::with(['user', 'client', 'lead_services'])->find($this->input->post('assigned_what'));
                $lead->client->append('brand');
			    return $this->response(['status' => 'ok', 'lead' => $lead], 200);
            }

                //redirect('leads/leads_mapper');
		} else {
			$this->session->set_flashdata('message', 'Ouch. There was an error assigning the lead.');
			//redirect('leads/leads_mapper');
		}
	}// End assign_lead();

    function change_lead_priority(){
	    $request = request();
	    if(!$request->input('lead_id') || !$request->input('lead_priority'))
            return $this->response(['error' => 'Data is not walid'], 400);

	    $lead = Lead::with(['user', 'client', 'lead_services'])->find($request->input('lead_id'));
        $lead->client->append('brand');
        $lead->lead_priority = $request->input('lead_priority');
        $lead->save();

        $this->response(['status' => 'ok', 'lead' => $lead], 200);
    }

//*******************************************************************************************************************
//*************
//*************																				Create leads function;
//*************
//*******************************************************************************************************************


	public function create_lead()
	{
		//Get variables
		//Validation. Leave for later.
		//var_dump($_POST); die;
		$this->load->model('mdl_client_tasks');
		
		$data['client_id'] = strip_tags($this->input->post('client_id'));
		$data['lead_body'] = $this->input->post('new_client_lead', TRUE);
		$data['lead_created_by'] = $this->session->userdata['firstname'] . " " . $this->session->userdata['lastname'];
		$data['lead_author_id'] = $this->session->userdata['user_id'];
		$data['lead_date_created'] = date('Y-m-d H:i:s');
		
		$data['lead_reffered_client'] = NULL;
		$data['lead_reffered_user'] = NULL;
		
		if ($this->input->post('reffered') != '') {
			$reffered = $this->input->post('reffered');

			if ($reffered == 'client') {
				if ($this->input->post('lead_reff_id') != '') {
					$data['lead_reffered_client'] = $this->input->post('lead_reff_id');
					$data['lead_reffered_by'] = $reffered;
				}
				//$this->mdl_clients->update_client(array('client_is_refferal' => 1), array('client_id' => $data['lead_reffered_client']));
			}
			elseif ($reffered == 'user') {
				if ($this->input->post('lead_reff_id') != '') {
					$data['lead_reffered_user'] = $this->input->post('lead_reff_id');
					$data['lead_reffered_by'] = $reffered;
				}
			}
			elseif ($reffered == 'other') {
                $data['lead_reffered_by'] = $this->input->post('other_comment');
            }
			else {
                $data['lead_reffered_by'] = $reffered;
            }
		}
		
        $data['lead_address'] = strip_tags($this->input->post('new_address'));
        $data['lead_city'] = strip_tags($this->input->post('new_city'));
        $data['lead_state'] = strip_tags($this->input->post('new_state'));
        $data['lead_zip'] = strip_tags($this->input->post('new_zip'));
        $data['lead_country'] = strip_tags($this->input->post('new_country'));
        $data['latitude'] = strip_tags($this->input->post('new_lat'));
        $data['longitude'] = strip_tags($this->input->post('new_lon'));

        if ($this->input->post('stump_add_info')) {
            $data['lead_add_info'] = $this->input->post('stump_add_info');
        }

		if (!$data['latitude'] || !$data['longitude']) {
			$coords = get_lat_lon($data['lead_address'], $data['lead_city'], $data['lead_state'], $data['lead_zip'], $data['lead_country']);
			$data['latitude'] = $coords['lat'];
			$data['longitude'] = $coords['lon'];
		}
		
		$data['lead_neighborhood'] = get_neighborhood(['latitude' => $data['latitude'], 'longitude' => $data['longitude']]);
		
		$data['lead_scheduled'] = 0;
		$defaultStatus = $this->mdl_leads_status->get_by(['lead_status_default' => 1]);
		$data['lead_status_id'] = $defaultStatus->lead_status_id;

		$data['timing'] = $this->input->post('new_lead_timing') ? $this->input->post('new_lead_timing') : 'Right Away';
		$data['lead_priority'] = $this->input->post('new_lead_priority') ? $this->input->post('new_lead_priority') : 'Regular';
		$data['preliminary_estimate'] = $this->input->post('preliminary_estimate');
		$data['lead_call'] = $this->input->post('lead_call') ? 1 : 0;
        $data['lead_postpone_date'] = date('Y-m-d');

		if ($this->input->post('postpone_date')) {
            $postponeDate = DateTime::createFromFormat(getDateFormat(), $this->input->post('postpone_date'));
            $postpone = $postponeDate->format('Y-m-d');
            $data['lead_postpone_date'] = $postpone;
        }

        if (!$this->input->post('estimators') && $this->input->post('scheduled_user_id')) {
            $data['lead_estimator'] = $this->input->post('scheduled_user_id');
        }
        else {
            $data['lead_estimator'] = $this->input->post('estimators');
        }
        /*
        $data['tree_removal'] = 'no';
        $data['tree_pruning'] = 'no';
        $data['stump_removal'] = 'no';
        $data['hedge_maintenance'] = 'no';
        $data['shrub_maintenance'] = 'no';
        $data['wood_disposal'] = 'no';
        $data['arborist_report'] = 'no';
        $data['development'] = 'no';
        $data['root_fertilizing'] = 'no';
        $data['tree_cabling'] = 'no';
        $data['emergency'] = 'no';
        $data['other'] = 'no';
        */
		
		$lead_id = $this->mdl_leads->insert_leads($data);

		if ($lead_id) {
			$lead_no = str_pad($lead_id, 5, '0', STR_PAD_LEFT);
			$lead_no = $lead_no . "-L";
			$update_data = array("lead_no" => $lead_no);
			$wdata = array("lead_id" => $lead_id);

			$lead_no_updated = $this->mdl_leads->update_leads($update_data, $wdata);
			
			$post = $this->input->post();
			$post['lead_id'] = $lead_id;
			$post['latitude'] = $data['latitude'];
			$post['longitude'] = $data['longitude'];
			//$post['task_date'] = date(strtotime("Y-m-d", $post['task_date']));
			$office_data = $this->mdl_client_tasks->office_data($post);
			
			//services
			$this->load->model('mdl_leads_services');
			$servicesEst = $this->input->post('est_services');
            if (!empty($this->input->post('est_products'))) {
                $servicesEst .= '|' . $this->input->post('est_products');
            }
            if (!empty( $this->input->post('est_bundles'))) {
                $servicesEst .= '|' . $this->input->post('est_bundles');
            }
			if ($servicesEst != '') {
				$services = explode('|', $servicesEst);
				foreach($services as $k=>$v) {
                    $this->mdl_leads_services->insert(['lead_id' => $lead_id, 'services_id' => intval($v)]);
                }
			}	
			//services - end
			
			//move files from tmp to the actual lead_id folder
			if ($this->input->post('pre_uploaded_files') != null && array_key_exists(0, $this->input->post('pre_uploaded_files')) && count($this->input->post('pre_uploaded_files')[0]) > 0){
				foreach ($this->input->post('pre_uploaded_files')[0] as $file) {
					$path_parts = explode('/', $file);
                    $new_path = $path_parts[0] . '/' . $path_parts[1] . '/' . $path_parts[2] . '/' . $path_parts[3] . '/' . $lead_no . '/' . str_replace('0-L', $lead_no, $path_parts[6]);
					bucket_copy($file, $new_path);
				}
			}
			
			//every file that is not contained in pre uploaded files, we'll consider unneded tmp file from before, so it will be deleted
			bucket_unlink_all('uploads/clients_files/' . $data['client_id'] . '/leads/tmp/0-L/');

			if ($lead_no_updated) {

                $assigned_to = ($data['lead_estimator'])?User::find($data['lead_estimator']):null;

				if (make_notes($data['client_id'], 'I just created a new lead "' . $lead_no . '" for the client.'.($assigned_to?(' Assigned to:'.$assigned_to->full_name):''), 'system', $lead_id)) {
					//All done. All good. Redirecting with success message.
					$link = base_url($data['client_id']);
					$mess = message('success', 'New Lead added successfuly!');
					$this->session->set_flashdata('user_message', $mess);
					
					if($this->input->is_ajax_request()) {
                        return $this->response(['lead_id' => $lead_id, 'client_id' => $data['client_id']], 200);
                    }

					redirect($link);
				}
			}
		}
	}

// End. Create_lead;

//*******************************************************************************************************************
//*************
//*************																					Edit leads function;
//*************
//*******************************************************************************************************************


	public function edit($lead_id = NULL)
	{
		if (!isset($lead_id))
			return page_404(['message'=>'This lead does not exist']);
		
		$data['title'] = $this->_title . ' - Leads';
		$data['menu_leads'] = "active";

		//Get lead informations - using common function from MY_Models;
		$this->load->model('mdl_client_tasks');
		$data['row'] = $this->mdl_leads->get_leads(['lead_id' => $lead_id], '')->row_array();

		if(!$data['row'] || empty($data['row']))
			return page_404(['message'=>'This lead does not exist']);

		$data['schedule_appointments'] = $this->mdl_client_tasks->get_all(['task_lead_id'=>$lead_id]);
		$data['lead_statuses'] = $this->mdl_leads_status->with('mdl_leads_reason')->get_all();
		 
		$data['active_users'] = $this->mdl_users->get_usermeta(array('active_status' => 'yes','emp_status' => 'current', 'users.id <>' => 0))->result();

		$data['estimators'] = $this->mdl_users->get_usermeta(array('active_status' => 'yes','emp_status' => 'current', 'users.id <>' => 0, 'emp_field_estimator'=>'1'))->result();

		//get client id and information
		$data['client_data'] = $this->mdl_clients->find_by_id($data['row']['client_id']);
		
		$client_id = $data['client_data']->client_id;
		$street = $data['row']['lead_address'];
		
		$city = $data['row']['lead_city'];
		$address = $street . "+" . $city;
		//Set the map:
		$config['center'] = config_item('map_center');
		$config['zoom'] = '10';

		$this->googlemaps->initialize($config);
		$data['est_services'] ='';
		$data['services'] = [];
		$this->load->model('mdl_services');
		$this->load->model('mdl_leads_services');

        $ClientModel = Client::with('tags')->find($client_id);
        $data['client_tags'] = $ClientModel->tags->map(function($tag) {
            return [ 'id'=>$tag->tag_id, 'text'=>$tag->name];
        });
        $services = $this->mdl_services->get_service_tags();
        $leadServices = $this->mdl_leads->getLeadServices($lead_id);


        $data['est_services'] = implode('|', $leadServices['est_services']);
        $data['est_products'] = implode('|', $leadServices['est_products']);
        $data['est_bundles'] = implode('|', $leadServices['est_bundles']);

        $data['services'] = json_encode($services['serviceTags'] ?? []);
        $data['products'] = json_encode($services['productTags'] ?? []) ;
        $data['bundles'] = json_encode($services['bundleTags'] ?? []) ;


		$marker = array();
		$marker['position'] = $address;
		$marker['icon'] = mappin_svg('#FD7567', '&#9899;', FALSE, '#000'); 
		$this->googlemaps->add_marker($marker);
		$data['address'] = $address;
		$data['map'] = $this->googlemaps->create_map();
        $data['client_contacts'] = $this->mdl_clients->get_client_contacts(array('cc_client_id' => $client_id));
        $data['references'] = Reference::getAllActive()->pluck('name', 'id')->toArray();

		//$data['blocks'] = $this->config->item('leads_services');
		//load view
		$this->load->view("details", $data);
	}


	public function edit_form()
    {
		$lead_id = $this->input->post('id');
		//Get lead informations - using common function from MY_Models;
		$this->load->model('mdl_client_tasks');
		$this->load->model('mdl_services');
		$this->load->model('mdl_leads_services');
		$data = ['est_services'=>'', 'services'=>[]];
		$data['row'] = $this->mdl_leads->get_leads(['lead_id' => $lead_id], '')->row_array();
		

		if(!$data['row'] || !count($data['row']))
			die(json_encode(['status'=>'error', 'error'=>"Lead not found"]));

		$data['schedule_appointments'] = $this->mdl_client_tasks->get_all(['task_lead_id'=>$lead_id]);
		$data['lead_statuses'] = $this->mdl_leads_status->with('mdl_leads_reason')->get_active_statuses();

		//$data['estimators'] = $this->mdl_users->get_usermeta(array('active_status' => 'yes','emp_status' => 'current', 'users.id <>' => 0, 'emp_field_estimator'=>'1'))->result();
        //echo "<pre>";
		//var_dump($data['estimators']);
        //die;
        $data['estimators'] = User::active()->noSystem()->estimator()->nameAsc()->get();

        $services = $this->mdl_services->get_service_tags();
        $leadServices = $this->mdl_leads->getLeadServices($lead_id);


        $data['est_services'] = implode('|', $leadServices['est_services']);
        $data['est_products'] = implode('|', $leadServices['est_products']);
        $data['est_bundles'] = implode('|', $leadServices['est_bundles']);

        $data['services'] = json_encode($services['serviceTags'] ?? []);
        $data['products'] = json_encode($services['productTags'] ?? []);
        $data['bundles'] = json_encode($services['bundleTags'] ?? []);

        $data['references'] = Reference::getAllActive()->pluck('name', 'id')->toArray();

		$response['heading'] = "Lead for ".$data['row']['client_name'];
		$response['status'] = 'ok';
		$response['html'] = $this->load->view('clients/partials/edit_lead_modal', $data, true);
		die(json_encode($response));
	}
	// End. edit(lead_id);
	//*******************************************************************************************************************
	//*************
	//*************																					Update leads function;
	//*************
	//*******************************************************************************************************************	

	public function update_lead()
	{
		
		$this->load->model('mdl_client_tasks');
		$this->load->model('mdl_leads_status');
        $this->load->model('mdl_estimates_orm');
		
		$lead_id = strip_tags($this->input->post('lead_id'));
		$lead_no = strip_tags($this->input->post('lead_no'));
		$client_id = strip_tags($this->input->post('client_id'));
		
		$oldLeadData = $this->mdl_leads->get_leads(['lead_id' => $lead_id], FALSE)->row();
		$old_status = $this->mdl_leads->find_by_id($lead_id);
        if(empty($old_status))
            redirect(base_url($client_id));

		$thisStatus = $this->mdl_leads_status->with('mdl_leads_reason')->get($this->input->post('set_lead_status'));

		/*
		$upload_files = $this->upload();
		if($upload_files['status']==FALSE){
			$mess = message('alert', 'File type or file size is not valid.');
  			$this->session->set_flashdata('user_message', $mess);
			return redirect(base_url($client_id));
		}
		*/
		if ($old_status->lead_status_id != $this->input->post('set_lead_status') && $thisStatus) {
			if ($old_status->lead_status_for_approval && $thisStatus->lead_status_default) {
				$data['lead_date_created'] = date('Y-m-d H:i:s');
			}
			$this->load->model('mdl_followups');
			$fuRowPost = $this->mdl_followups->get_by(['fu_module_name' => 'leads', 'fu_item_id' => $lead_id, 'fu_status' => 'postponed']);
			$fuRowNew = $this->mdl_followups->get_by(['fu_module_name' => 'leads', 'fu_item_id' => $lead_id, 'fu_status' => 'new']);

			if($fuRowNew && !empty($fuRowNew))
				$this->mdl_followups->update($fuRowNew->fu_id, ['fu_status' => 'canceled', 'fu_comment' => 'Canceled ' . date('Y-m-d H:i:s') . ' - status was changed']);
			elseif($fuRowPost && !empty($fuRowPost))
				$this->mdl_followups->update($fuRowPost->fu_id, ['fu_status' => 'canceled', 'fu_comment' => 'Canceled ' . date('Y-m-d H:i:s') . ' - status was changed']);
			$status = array('status_type' => 'lead', 'status_item_id' => $lead_id, 'status_value' => $this->input->post('set_lead_status'), 'status_date' => time());
			$this->mdl_leads->status_log($status);
		}
		$postponeDate = DateTime::createFromFormat(getDateFormat(), $this->input->post('postpone_date'));
		$postpone = $postponeDate ? $postponeDate->format('Y-m-d') : '';
		$data['lead_postpone_date'] = date('Y-m-d');
		if($postpone != '')
			$data['lead_postpone_date'] = $postpone;
		
		
		
		//$data['lead_reffered_client'] = NULL;
		//$data['lead_reffered_user'] = NULL;
		//$data['lead_reffered_by'] = NULL;
		

		$data['lead_scheduled'] = 0;
		$data['lead_call'] = $this->input->post('lead_call') ? 1 : 0;

		//Lead Details;
        $estimate_data = $this->mdl_estimates_orm->with('mdl_services_orm')->get_full_estimate_data(['estimates.lead_id' => $lead_id]);
        if(empty($estimate_data) || ($thisStatus && $thisStatus->lead_status_estimated))
        {
            if($this->input->post('set_lead_status'))
                $data['lead_status_id'] = strip_tags($this->input->post('set_lead_status'));
            $data['lead_reason_status_id'] = NULL;
            if($this->input->post('set_lead_status') && $thisStatus->lead_status_declined)
                $data['lead_reason_status_id'] = strip_tags($this->input->post('set_lead_reason_status'));
        }
		$data['lead_priority'] = strip_tags($this->input->post('set_lead_priority'));
		$data['timing'] = strip_tags($this->input->post('set_lead_timing'));
		//Lead Discription;
		$data['lead_body'] = strip_tags($this->input->post('set_lead_discription'));
		$data['lead_estimator'] = (int)$this->input->post('assigned_to');
		//Checkboxes:

		if ($this->input->post('new_add') && $this->input->post('new_address')) {
			$data['lead_address'] = strip_tags($this->input->post('new_address'));
			$data['lead_city'] = strip_tags($this->input->post('new_city'));
			$data['lead_state'] = strip_tags($this->input->post('new_state'));
			$data['lead_zip'] = strip_tags($this->input->post('new_zip'));
			$data['lead_country'] = strip_tags($this->input->post('lead_country'));
			$data['latitude'] = strip_tags($this->input->post('new_lat'));
			$data['longitude'] = strip_tags($this->input->post('new_lon'));
			$data['lead_add_info'] = strip_tags($this->input->post('lead_add_info'));
		} else {
			$client = $this->mdl_clients->find_by_id($client_id);
			$data['lead_address'] = $client->client_address;
			$data['lead_city'] = $client->client_city;
			$data['lead_state'] = $client->client_state;
			$data['lead_zip'] = $client->client_zip;
			$data['lead_country'] = $client->client_country;
			$data['latitude'] = $client->client_lat;
			$data['longitude'] = $client->client_lng;
            $data['lead_add_info'] = $client->client_main_intersection2 ? $client->client_main_intersection2 : $client->client_main_intersection ;
		}
		/*var_dump($this->input->post('lead_reff'));
		var_dump($this->input->post('lead_reff_id'));
		var_dump($this->input->post('other_comment'));
		die;*/
		if ($this->input->post('lead_reff') != '') {
			$reffered = $this->input->post('lead_reff');
			$reffId = $this->input->post('reff_id');
		
			if($reffered == 'client')
			{
				$data['lead_reffered_client'] = ($reffId == '' && $this->input->post('reff_id') != '') ? $this->input->post('reff_id') : $reffId;
				$data['lead_reffered_by'] = $reffered;
			}
			elseif($reffered == 'user' && $reffId != '')
			{
				$data['lead_reffered_user'] = ($reffId == '' && $this->input->post('reff_id') != '') ? $this->input->post('reff_id') : $reffId;
				$data['lead_reffered_by'] = $reffered;
			}
			elseif($reffered == 'other')
				$data['lead_reffered_by'] = $this->input->post('other_comment');
			elseif($reffered != '')
				$data['lead_reffered_by'] = $reffered;
		}
		else
		{ 
			$data['lead_reffered_client'] = NULL;
			$data['lead_reffered_user'] = NULL;
			$data['lead_reffered_by'] = NULL;
		}
        // $data['references'] = Reference::getAllActive()->pluck('name', 'id')->toArray();
		// auto Tax for US company if change address
        if(config_item('office_country') == 'United States of America') {
            if (!empty($oldLeadData->tax_name) || !empty($oldLeadData->tax_rate) || !empty($oldLeadData->tax_value) ||
                $oldLeadData->lead_address != $data['lead_address'] || $oldLeadData->lead_city != $data['lead_city'] ||
                $oldLeadData->lead_state != $data['lead_state'] || $oldLeadData->lead_zip != $data['lead_zip']) {

                $addressForAutoTax = [
                    'Address' => $data['lead_address'],
                    'City' =>  $data['lead_city'],
                    'State' => $data['lead_state'],
                    'Zip' => $data['lead_zip']
                ];
                $autoTax = $this->estimateactions->getTaxForUSCompany($addressForAutoTax);
                if (!empty($autoTax['db'])) {
                    $data = array_merge($data, $autoTax['db']);
                }
            }
        }

		if(!$data['latitude'] || !$data['longitude']){

			$coords = get_lat_lon($data['lead_address'], $data['lead_city'], $data['lead_state'], $data['lead_zip'], $data['lead_country']);

			$data['latitude'] = $coords['lat'];
			$data['longitude'] = $coords['lon'];	
		}
		

		$task['task_address'] = $data['lead_address'];
        $task['task_city']  = $data['lead_city'];
        $task['task_state'] = $data['lead_state'];
        $task['task_country'] = $data['lead_country'];
        $task['task_latitude'] = $data['latitude'];
        $task['task_longitude'] = $data['longitude'];
        $task['task_zip'] = $data['lead_zip'];
        $this->mdl_client_tasks->update_by($task, ['task_lead_id'=>$lead_id]);



		$this->load->model('mdl_leads_services');

		$this->mdl_leads_services->delete_by(['lead_id' => $lead_id]);

        $estServices = explode('|', $this->input->post('est_services'));
        $estProducts = explode('|', $this->input->post('est_products'));
        $estBundles = explode('|', $this->input->post('est_bundles'));

        foreach($estServices as $k=>$v)
            $this->mdl_leads_services->insert(['lead_id' => $lead_id, 'services_id' => intval($v)]);

        foreach($estProducts as $k=>$v)
            $this->mdl_leads_services->insert(['lead_id' => $lead_id, 'services_id' => intval($v)]);

        foreach($estBundles as $k=>$v)
            $this->mdl_leads_services->insert(['lead_id' => $lead_id, 'services_id' => intval($v)]);



		$wdata['lead_id'] = $lead_id;
		//Recodring values and analizing the responce.
		//Checking if anything was recorded.
		$note = NULL;
		/*-------------add appointments------------*/
		$post = $this->input->post();
		$office_data = $this->mdl_client_tasks->office_data(array_merge($post, (array)$old_status));
		/*-------------add appointments------------*/
		
		$this->mdl_leads->update_leads($data, $wdata);
		//Posting a note
		$new_lead = $this->mdl_leads->find_by_id($lead_id);
		$noteStart = 'Client Lead "' . $lead_no . '" updated<br><ul>';
		$note = '';
		foreach($old_status as $key=>$val)
		{
			if(
			        $old_status->$key == $new_lead->$key ||
                    in_array($key, ['lead_reason_status_id', 'lead_status_id', 'lead_status_declined', 'lead_status_for_approval', 'reason_id', 'reason_lead_status_id', 'reason_active'])
            )
				continue;
			
			$note .= '<li>';
			$name = explode('_', $key);
			foreach($name as $k=>$v)
				$note .= ucfirst($name[$k]) . ' ';
			if($key == 'lead_call')
			{
				if($old_status->$key)
					$note .= ' from: "Yes"';
				else
					$note .= ' from: "No"';
				
				if($new_lead->$key)
					$note .= ' to: "Yes"</li>';
				else
					$note .= ' to: "No"</li>';
			}
			elseif($key == 'lead_estimator')
			{
				$newLeadData = $this->mdl_leads->get_leads(['lead_id' => $lead_id], FALSE)->row();
				
				if($old_status->$key)
					$note .= ' from: ' . $oldLeadData->firstname . ' ' . $oldLeadData->lastname;
				if($new_lead->$key)
					$note .= ' to: ' . $newLeadData->firstname . ' ' . $newLeadData->lastname;
			}
			else
			{
				$note .= ' from: ';
				$note .= ($old_status->$key) ? $old_status->$key : 'no';
				$note .= ' to: ';
				$note .= ($new_lead->$key) ? $new_lead->$key : 'no' . '</li>';
			}
			
		}
		
		$noteEnd = '</ul>';
		if($note != '')
			$text = $noteStart . $note . $noteEnd;
		if ($note && make_notes($client_id, $text, 'system', $lead_id)) {
			//Record was inserted to db. Redirecting with success message.
			
			$mess = message('success', 'Lead was updated!');
			$this->session->set_flashdata('user_message', $mess);
			
		}
		
		if($this->input->post('from_leads') == null || $this->input->post('from_leads') == ''){
			redirect(base_url($client_id));
		} else {
			redirect($_SERVER['HTTP_REFERER'] ?? base_url('leads'));
		}
		
	}

//Update client profile.

//*******************************************************************************************************************
//*************
//*************																					Delete leads function;
//*************
//*******************************************************************************************************************


	public function delete($lead_id)
	{
		if ($this->session->userdata('user_type') != "admin") {
			show_404();
		} else {

			//Get Client_id:
			$id = $lead_id;
			$client_id = $this->mdl_leads->find_by_id($id)->client_id;
			$estimate = $this->mdl_estimates->get_estimates('', '', '', '', 'estimates.estimate_id', '', array('estimates.lead_id' =>  $id));
			
			if($estimate)
				$estimate = $estimate->row_array();
			
			$this->load->helper('estimates_helper');
            $invoice = $wo = NULL;
			if($estimate && isset($estimate['estimate_id'])) {
                $invoice_obj = $this->mdl_invoices->invoices(['invoices.estimate_id' => $estimate['estimate_id']]);
                $invoice = (!empty($invoice_obj))?(array)$invoice_obj[0]:[];
                $wo = $this->mdl_workorders->get_workorders('', '', '', '', array('workorders.estimate_id' => $estimate['estimate_id']));
            }
			if ($this->mdl_leads->delete_leads($lead_id)) {
				$this->load->model('mdl_followups');
				$fuRowPost = $this->mdl_followups->get_by(['fu_module_name' => 'leads', 'fu_item_id' => $lead_id, 'fu_status' => 'postponed']);
				$fuRowNew = $this->mdl_followups->get_by(['fu_module_name' => 'leads', 'fu_item_id' => $lead_id, 'fu_status' => 'new']);
				
				if($fuRowNew && !empty($fuRowNew))
					$this->mdl_followups->update($fuRowNew->fu_id, ['fu_status' => 'canceled', 'fu_comment' => 'Canceled ' . date('Y-m-d H:i:s') . ' - lead was deleted']);
				elseif($fuRowPost && !empty($fuRowPost))
					$this->mdl_followups->update($fuRowPost->fu_id, ['fu_status' => 'canceled', 'fu_comment' => 'Canceled ' . date('Y-m-d H:i:s') . ' - lead was deleted']);
				if(!empty($invoice))
				{
					$this->mdl_invoices->delete_invoice_new($invoice['id']);
					
					$fuRowPost = $this->mdl_followups->get_by(['fu_module_name' => 'invoices', 'fu_item_id' => $invoice['id'], 'fu_status' => 'postponed']);
					$fuRowNew = $this->mdl_followups->get_by(['fu_module_name' => 'invoices', 'fu_item_id' => $invoice['id'], 'fu_status' => 'new']);
					
					if($fuRowNew && !empty($fuRowNew))
						$this->mdl_followups->update($fuRowNew->fu_id, ['fu_status' => 'canceled', 'fu_comment' => 'Canceled ' . date('Y-m-d H:i:s') . ' - lead was deleted']);
					elseif($fuRowPost && !empty($fuRowPost))
						$this->mdl_followups->update($fuRowPost->fu_id, ['fu_status' => 'canceled', 'fu_comment' => 'Canceled ' . date('Y-m-d H:i:s') . ' - lead was deleted']);

					if(!empty($invoice['invoice_qb_id']))
                        pushJob('quickbooks/invoice/syncinvoiceinqb', serialize(['id' => $invoice['id'], 'qbId' => $invoice['invoice_qb_id']]));
				}
				if($wo && $wo->num_rows())
				{
					$wo = $wo->row_array();
					$this->mdl_workorders->delete_workorder_new($wo['id']);
				}
				if($estimate && isset($estimate['estimate_id']))
				{
                    $payments = $this->mdl_client_payments->get(array('estimate_id' => $estimate['estimate_id']));
					$clientsPath = 'uploads/clients_files/' . $client_id . '/estimates/' . $estimate['estimate_no'];
					if(isset($client_id) && is_dir($clientsPath))
						recursive_rm_files($clientsPath);
					/*********DELETE PAYMENTS FILES*****************/
					$paymentsPath = 'uploads/payment_files/' . $client_id . '/' . $estimate['estimate_no'];
					if(isset($client_id) && is_dir($paymentsPath))
						recursive_rm_files($paymentsPath);
					/*********DELETE PAYMENTS FILES*****************/
					$this->mdl_estimates->delete_estimate($estimate['estimate_id']);
					$fuRowPost = $this->mdl_followups->get_by(['fu_module_name' => 'estimates', 'fu_item_id' => $estimate['estimate_id'], 'fu_status' => 'postponed']);
					$fuRowNew = $this->mdl_followups->get_by(['fu_module_name' => 'estimates', 'fu_item_id' => $estimate['estimate_id'], 'fu_status' => 'new']);
					
					if($fuRowNew && !empty($fuRowNew))
						$this->mdl_followups->update($fuRowNew->fu_id, ['fu_status' => 'canceled', 'fu_comment' => 'Canceled ' . date('Y-m-d H:i:s') . ' - lead was deleted']);
					elseif($fuRowPost && !empty($fuRowPost))
						$this->mdl_followups->update($fuRowPost->fu_id, ['fu_status' => 'canceled', 'fu_comment' => 'Canceled ' . date('Y-m-d H:i:s') . ' - lead was deleted']);


					// It is dangerous to delete from QB as in one payment there can be payments for different invoices
//                    if(!empty($payments)){
//                        foreach ($payments as $payment){
//                            pushJob('quickbooks/payment/syncpaymentinqb', serialize(['id' => $payment['payment_id'], 'qbId' => $payment['payment_qb_id']]));
//                        }
//                    }
				}
				
				
				$link = base_url($client_id);
				$mess = message('success', 'Lead, Estimate, Workorder, Invoice were successfully deleted!');
				$this->session->set_flashdata('user_message', $mess);
				redirect($link);
			}
		}
	}

// End. delete(lead_id);


	function ajax_get_leads()
	{
		$return = $this->mdl_leads->search_leads();
		//print_r($return);
		if ($return->num_rows() > 0) {
			foreach ($return->result() as $rows):
				?>
				<tr>
					<td><?php echo anchor('client/' . $rows->client_id, $rows->client_name); ?></td>
					<th><?php echo $rows->lead_body; ?></th>
					<td><?php echo $rows->lead_date_created; ?></td>
					<td><?php echo $rows->lead_created_by; ?></td>
					<td><?php echo $rows->lead_status; ?></td>
					<td><?php echo anchor($rows->lead_no, 'Edit') ?></td>
				</tr>
			<?php
			endforeach;
		} else {
			print_r('<tr><td colspan="6">No records found</td></tr>');
		}
	}

	function ajax_scheduled_lead(){
		$result['status'] = 'ok';
		$result['status'] = 'error';
		die(json_encode($result));
	}

	function ajax_call_lead(){
		$result['status'] = 'ok';
		$lead_id = intval($this->input->post('lead_id'));
		$update['lead_call'] = intval($this->input->post('call'));
		if($update['lead_call'] !== 1 && $update['lead_call'] !== 0)
			$result['status'] = 'error';
		if(!$lead_id)
			$result['status'] = 'error';
		$this->mdl_leads->update_leads($update, array('lead_id' => $lead_id));
		die(json_encode($result));
	}
	
	function saveCopyEstimate($lead_id)
	{
		$result['status'] = 'ok';
		$update['lead_json_backup'] = json_encode($_POST);
		$lead = $this->mdl_leads->find_by_id($lead_id);
		
		if(!$lead_id)
			$result['status'] = 'error';
		if($lead->lead_status_default)
			$this->mdl_leads->update_leads($update, array('lead_id' => $lead_id));
		die(json_encode($result));
	}
	
	function ajax_postpone_lead()
	{
		$result['status'] = 'error';
		$lead_id = intval($this->input->post('pk'));
		$update['lead_postpone_date'] = $this->input->post('value') ? $this->input->post('value') : date('Y-m-d');
		
		if($lead_id)
		{
			$this->mdl_leads->update_leads($update, array('lead_id' => $lead_id));
			$result['status'] = 'ok';
			$result['date'] = getDateTimeWithDate($update['lead_postpone_date'], 'Y-m-d');
			$result['lead_id'] = $lead_id;
		}
		die(json_encode($result));
	}
	
	function reffered_lead()
	{
		$lead_id = $this->input->post('lead_id');
		$reff_by = $this->input->post('lead_reff');
		$reff_id = $this->input->post('lead_reff_id') ? $this->input->post('lead_reff_id') : NULL;
		
		$lead = $this->mdl_estimates->get_estimates('', '', '', '', 'estimates.estimate_id', '', array('leads.lead_id' => $lead_id));
		if(!$lead)
			$lead = $this->mdl_leads->get_leads(array('lead_id' => $lead_id), FALSE);
		
		if(!$lead && ($reff_by == 'user' || $reff_by == 'client'))
			return FALSE;
		elseif($lead && ($reff_by == 'user' || $reff_by == 'client'))
			$lead = $lead->row();
		else
			$lead = $this->mdl_leads->get_leads(array('lead_id' => $lead_id), FALSE)->row();
		if($reff_by == '')
			$this->mdl_leads->update_leads(array('lead_reffered_user' => NULL, 'lead_reffered_by' => NULL, 'lead_reffered_client' => NULL), array('lead_id' => $lead_id));
		elseif($reff_by == 'user')
		{
			$this->mdl_leads->update_leads(array('lead_reffered_user' => $reff_id, 'lead_reffered_by' => 'user', 'lead_reffered_client' => NULL), array('lead_id' => $lead_id));
			if($lead->lead_reffered_client)
				$this->mdl_clients->update_client(array('client_is_refferal' => 0), array('client_id' => intval($lead->lead_reffered_client)));
		}
		elseif($reff_by == 'client')
		{
			if(isset($lead->est_status_confirmed) && $lead->est_status_confirmed)
			{
				
				if($lead->lead_reffered_client)
					$this->mdl_clients->update_client(array('client_is_refferal' => 0), array('client_id' => intval($lead->lead_reffered_client)));
				
			
				$this->mdl_leads->update_leads(array('lead_reffered_user' => NULL, 'lead_reffered_by' => 'client', 'lead_reffered_client' => $reff_id), array('lead_id' => $lead_id));
				$this->mdl_clients->update_client(array('client_is_refferal' => 1), array('client_id' => $reff_id));
				
			}
			else
				$this->mdl_leads->update_leads(array('lead_reffered_user' => NULL, 'lead_reffered_by' => 'client', 'lead_reffered_client' => $reff_id), array('lead_id' => $lead_id));
			if($lead->lead_reffered_client)
				$this->mdl_clients->update_client(array('client_is_refferal' => 0), array('client_id' => $lead_id->lead_reffered_client));
		}
		elseif($reff_by == 'other')
		{
			$this->mdl_leads->update_leads(array('lead_reffered_user' => NULL, 'lead_reffered_client' => NULL, 'lead_reffered_by' => $this->input->post('other_comment')), array('lead_id' => $lead_id));
		}
		else
			$this->mdl_leads->update_leads(array('lead_reffered_user' => NULL, 'lead_reffered_client' => NULL, 'lead_reffered_by' => $reff_by), array('lead_id' => $lead_id));

		redirect(base_url($lead->client_id));
	}
	
	function ajax_check_any_updates()
	{
		$id = $this->input->post('maxLeadId');
		$arr = $this->mdl_leads->get_leads(array('lead_postpone_date <=' => date('Y-m-d'), 'lead_id > ' => $id), 'New', 'leads.lead_id ASC');
        $arr = ($arr && $arr->num_rows()) ? $arr->result_array() : array();
		
		$result['status'] = 'ok';
		$result['maxLeadId'] = $id;
		if(!empty($arr))
		{
			$result['maxLeadId'] = 0;
			foreach ($arr as $k=>$row) {
				if(!isset($arr[$k+1])) 
					$result['maxLeadId'] = $row['lead_id'];
				$marker_style = NULL;
				$client_id = $row['client_id'];
				$name = $row['client_name'];
				$phone = numberTo($row['cc_phone']);
				$street = $row['lead_address'];
				$state = $row['lead_state'];
				$city = $row['lead_city'];
				$country = $row['lead_country'];
				$address = ($row['latitude'] && $row['longitude']) ? $row['latitude'] . ', ' . $row['longitude'] : $street . "," . $city . ',' . $state . ',' . $country;
				$lead_date = $row['lead_date_created'];
				$lead_body_dirty = $row['lead_body'];
				$lead_body = preg_replace("/[^\p{L}\p{N}]/u", ' ', $lead_body_dirty);
				$lead_id = $row['lead_id'];
				$lead_priority = $row['lead_priority'];
				$lead_call = $row['lead_call'];
				$estimator_id = $row['lead_estimator'];
				$estimator_first_name = $row['firstname'];
				$estimator_last_name = $row['lastname'];
                $leadDays = (string)round((strtotime(date('Y-m-d')) - strtotime(date('Y-m-d', strtotime($row['lead_date_created'])))) / 86400);

				//Base Marker information:
				$marker_link = base_url('client/' . $client_id);

				$tree = FALSE;
				if($row['lights_installation'] == 'yes') {
					$tree = TRUE;
				}
				//Priority Markker style:	
				
				if ($lead_priority == "Priority") {
					//$marker_style = "https://chart.apis.google.com/chart?chst=d_map_pin_letter&chld=$leadDays|FDF569";
					$marker_style = mappin_svg('#FDF569', $leadDays, FALSE, '#000', $tree);  
					$priority_indicator = " - Priority";
				}
				if ($lead_priority == "Emergency") {
					//$marker_style = "https://chart.apis.google.com/chart?chst=d_map_pin_letter&chld=$leadDays|FD7567";

					$marker_style = mappin_svg('#FD7567', $leadDays, FALSE, '#000', $tree); 
					$priority_indicator = " - Emergency";
				}
				if ($lead_priority == "Regular") {
					//$marker_style = "https://chart.apis.google.com/chart?chst=d_map_pin_letter&chld=$leadDays|00E64D";
					$marker_style = mappin_svg('#00E64D', $leadDays, FALSE, '#000', $tree);
					$priority_indicator = "";
				}

				$showAs = ($lead_priority == 'Priority' || $lead_priority == 'Emergency') ? $lead_priority : $estimator_id;
				$showAs = ($showAs == 'none' || !$showAs) ? 0 : $showAs;
				$marker_content = "<strong data-user='" . $showAs . "'><a href='" . $marker_link . "' target='_blank'>" . $name . "</a>" . $priority_indicator . "</strong><br/>";
				$marker_content .= "Assigned to:&nbsp;";
				if ($estimator_id) {
					$marker_content .= "<strong>" . $estimator_first_name . "&nbsp;" . $estimator_last_name . "</strong>";
				} else {
					$marker_content .= "<strong>Not Assigned</strong>";
				}
				if((isset($sms) && (is_object($sms) || !empty($sms))) && $this->session->userdata('twilio_worker_id'))
				{
					$marker_content .= "<div class='d-inline-block pull-right btn btn-info addLeadSms'";
					if(isset($row['cc_phone']) && $row['cc_phone'])
						$marker_content .= " data-phone ='".substr($row['cc_phone'], 0, 10)."'";
					/*elseif(isset($row['client_mobile']) && $row['client_mobile'])
						$marker_content .= " data-phone ='".$row['client_mobile']."'";*/
						
					$marker_content .= " data-email='".$row['cc_email']."'";
					$marker_content .= " data-name='".$row['cc_name']."'";
					$marker_content .= " data-href='#sms-".$sms->sms_id."' >";
					$marker_content .= " SMS to " . $row['cc_name'] . "</div>" ;
				}
				
				$marker_content .= "<br>Date:&nbsp;" . $lead_date . "<br><abbr title='Phone'>Phone:&nbsp;</abbr>" . $phone . "<br>Address:&nbsp;" . $street . ", " . $city . ', ' . $state . ', ' . $country . "<br>";

				$servicesList = NULL;
				foreach($this->config->item('leads_services') as $block) {
					foreach($block['list'] as $item) {
						if($row[$item['name']] == 'yes')
							$servicesList .= $item['label'] . ', ';
					}
				}

				$servicesList = rtrim($servicesList, ', ');
				if($servicesList)
					$marker_content .= "Services:&nbsp;" . $servicesList . "<br>";

				$marker_content .= ucfirst($lead_body);
				$marker_content .= "<br>";
				$marker_content .= "<div class='p-top-10 p-bottom-10'>";
				$marker_content .= anchor('estimates/new_estimate/' . $lead_id, '&nbsp;<i class="icon-leaf icon-white" title="Create an estimate"></i>&nbsp;Create Estimate&nbsp;', 'class="btn btn-success btn"');
				$checked = $checked_call = '';
				$class = $class_call = '';

				if($lead_call)
				{
					$checked_call = ' checked="checked"';
					$class_call = ' checked';
				}
				$marker_content .= '<div class="checkbox m-l-md" style="display:inline-block;width: 300px;"><label class="checkbox-custom"><input type="checkbox" class="callLead" data-lead_id="' . $lead_id . '" name="lead_call"' . $checked_call . '><i class="fa fa-fw fa-square-o' . $class_call . '"></i>Call The Client</label></div>';
				$color = 'FFFFFF';
				// Leads Assignment:
                if (request()->user()->user_type == "admin" || is_cl_permission_all()) {

					//Get list of active users:
					if (!empty($active_users)) {

						$marker_content .= "<div class='form-inline p-top-10'>";

						$marker_content .= "<form id='assign_lead' method='post' action='" . base_url() . "leads/assign_lead'>";
						$marker_content .= "<input type='hidden' name='assigned_what' value='" . $lead_id . "'>";
						$marker_content .= "<select name='assigned_to'>";
						$marker_content .= "<option value='none'>Not assigned</option>";
						foreach ($active_users as $active_user) {
							if ($active_user->id == $estimator_id) {
								$selected = "selected";
								$marker_style = mappin_svg($active_user->color, $leadDays, FALSE, '#000', $tree);
								 
							} else {
								$selected = " ";
							}
							$marker_content .= "<option value='" . $active_user->id . "' " . $selected . ">" . $active_user->firstname . "&nbsp;" . $active_user->lastname . " </option>";
						}
						$marker_content .= "</select> &nbsp;&nbsp;";
						$marker_content .= "<input type='submit' name='view' value='Assign' id='estimator_id' class='btn btn-info' />";
						$marker_content .= "</div>";
						$marker_content .= "</div>";
					}
				}

				//Priority Markker style:	
				if ($estimator_id == "none" || !$marker_style) {
					//$marker_style = "https://chart.apis.google.com/chart?chst=d_map_pin_letter&chld=$leadDays|00E64D";
					$marker_style = mappin_svg('#00E64D', $leadDays, FALSE, '#000', $tree);
				}
				if ($lead_priority == "Priority") {
					//$marker_style = "https://chart.apis.google.com/chart?chst=d_map_pin_letter&chld=$leadDays|FDF569";
					$marker_style = mappin_svg('#FDF569', $leadDays, FALSE, '#000', $tree);
				}
				if ($lead_priority == "Emergency") {
					//$marker_style = "https://chart.apis.google.com/chart?chst=d_map_pin_letter&chld=$leadDays|FD7567";
					$marker_style = mappin_svg('#FD7567', $leadDays, FALSE, '#000', $tree);
				}

				if ($lead_call) {
					$marker_style = mappin_svg($color, 'phone', TRUE, '#000', $tree); 
				}

				//if($lead_priority =="Regular") {$marker_style = "http://chart.apis.google.com/chart?chst=d_map_pin_letter&chld=%E2%80%A2|6991FD";}


				$result['marker'][$k] = array();
				$result['marker'][$k]['lat'] = $row['latitude'];
				$result['marker'][$k]['lng'] = $row['longitude'];
				$result['marker'][$k]['infowindow_content'] = $marker_content;
				$result['marker'][$k]['icon'] = $marker_style;
				//echo '<pre>'; var_dump($marker_content); die;
					$this->googlemaps->add_marker($result['marker']);
			}
		}
		die(json_encode($result));
	}

	function ajax_change_lead_status(){
        if($this->update_lead_status([
            'lead_id' => $this->input->post('lead_id'),
            'lead_status' => $this->input->post('lead_status'),
        ], false))
            return $this->response(['status' => 'ok']);
        return $this->response(['status' => 'error', 'error' => 'Error']);
    }

	function update_lead_status($update_data = [], $isAjax = false) {

		if(empty($update_data))
			$update_data = $this->input->post();
		$lead_id = FALSE;
		$lead_reason = NULL;
		if (isset($update_data['lead_id']) && $update_data['lead_id']) {
			$lead_id = $update_data['lead_id'];
		}

		if (isset($update_data['lead_status']) && $update_data['lead_status']) {
			$this->load->model('mdl_leads_status');
			$thisStatus = $this->mdl_leads_status->with('mdl_leads_reason')->get($update_data['lead_status']);
			$lead_status = $update_data['lead_status'];
			if($thisStatus->lead_status_declined)
				$lead_reason = $update_data['lead_reason_status'];
		}

		if(!$lead_id) {
			return $this->response(['status'=>'error'], 400);
		}

		$lead_data = $this->mdl_leads->find_by_id($lead_id);

		if(!$lead_data) {
			return $this->response(['status'=>'error'], 400);
		}

		$update_data = array('lead_status_id' => $lead_status, 'lead_reason_status_id' => $lead_reason);

		$updated = $this->mdl_leads->update($lead_id, $update_data);

		if ($updated) {
			$this->load->model('mdl_followups');
			$fuRowPost = $this->mdl_followups->get_by(['fu_module_name' => 'leads', 'fu_item_id' => $lead_id, 'fu_status' => 'postponed']);
			$fuRowNew = $this->mdl_followups->get_by(['fu_module_name' => 'leads', 'fu_item_id' => $lead_id, 'fu_status' => 'new']);

			if($fuRowNew && !empty($fuRowNew))
				$this->mdl_followups->update($fuRowNew->fu_id, ['fu_status' => 'canceled', 'fu_comment' => 'Canceled ' . date('Y-m-d H:i:s') . ' - lead status was changed']);
			elseif($fuRowPost && !empty($fuRowPost))
				$this->mdl_followups->update($fuRowPost->fu_id, ['fu_status' => 'canceled', 'fu_comment' => 'Canceled ' . date('Y-m-d H:i:s') . ' - lead status was changed']);
			$update_msg = "Status for " . $lead_data->lead_no . ' was modified from ' . $lead_data->lead_status_name . ' to ' . $thisStatus->lead_status_name;

			if (make_notes($lead_data->client_id, $update_msg, 'system', $lead_data->lead_id)) {
			    $lead = Lead::with(['user', 'client', 'lead_services', 'status'])->find($lead_id);
                $lead->client->append('brand');
			    return $this->response(['status' => 'success', 'lead'=>$lead, 'message'=>'Lead status changed !'], 200);
			}
		}

		if($isAjax)
            return $this->response(['status'=>'error'], 400);

		return FALSE;
	}
	
	/***********************Leads Statuses******************/
	public function lead_statuses()
	{
		if ($this->session->userdata('user_type') != "admin") {
			show_404();
		}
		$this->load->model('mdl_leads_status');
		$data['title'] = "Lead Statuses";

		//get employees
		$data['statuses'] = $this->mdl_leads_status->with('mdl_leads_reason')->order_by('lead_status_priority')->get_all();
		$data['isset_default'] = FALSE;
		$data['isset_declined'] = FALSE;
		$data['isset_estimated'] = FALSE;
		$data['isset_for_approval'] = FALSE;
        $data['isset_draft'] = FALSE;
		foreach($data['statuses'] as $status)
		{
			if($status->lead_status_default)
				$data['isset_default'] = TRUE;
			if($status->lead_status_estimated)
				$data['isset_estimated'] = TRUE;
			if($status->lead_status_for_approval)
				$data['isset_for_approval'] = TRUE;
			if($status->lead_status_declined)
				$data['isset_declined'] = TRUE;
			if($status->lead_status_draft)
				$data['isset_draft'] = TRUE;
		}
		$this->load->view('index_leads_status', $data);
	}

	function ajax_save_leads_status()
	{
		if ($this->session->userdata('user_type') != "admin") {
			show_404();
		}
		$this->load->model('mdl_leads_status');
		$this->load->model('mdl_leads_reason');
		$id = $this->input->post('status_id');
		$data['lead_status_name'] = strip_tags($this->input->post('status_name', TRUE));
		//$data['est_status_priority']      = (int) $this->input->post('status_priority');

		if ($id != '') {
			$this->mdl_leads_status->update($id, $data);
			if($this->input->post('status_reason'))
			{
				$reasons = $this->input->post('status_reason');
				foreach($reasons as $key=>$reason)
				{
					$active = 1;
					if($reason['reason_active'] == 'true')
						$active = 0;
					if(isset($reason['reason_id']))
						$this->mdl_leads_reason->update($reason['reason_id'], array('reason_name' => $reason['reason_name'], 'reason_lead_status_id' => $id, 'reason_active' => $active));
					else
						$this->mdl_leads_reason->insert(array('reason_name' => $reason['reason_name'], 'reason_lead_status_id' => $id, 'reason_active' => $active));
				}
			}
			die(json_encode(array('status' => 'ok')));
		}
		$id = $this->mdl_leads_status->insert($data);
		if($this->input->post('status_reason'))
		{
			$reasons = $this->input->post('status_reason');

			foreach($reasons as $key=>$reason)
			{
				$active = 1;
				if($reason['reason_active'] == 'true')
					$active = 0;
				$this->mdl_leads_reason->insert(array('reason_name' => $reason['reason_name'], 'reason_lead_status_id' => $id, 'reason_active' => $active));
			}
		}
		die(json_encode(array('status' => 'ok')));
	}

	function ajax_delete_leads_status()
	{
		if ($this->session->userdata('user_type') != "admin")
			show_404();
		
		$this->load->model('mdl_leads_status');
		$id = $this->input->post('status_id');
		$status = $this->input->post('status');
		if ($id != '')
			$this->mdl_leads_status->update($id, array('lead_status_active' => $status));
		die(json_encode(array('status' => 'ok')));
	}
	/***********************END Estimate Statuses******************/

    function ajax_get_lead_statuses()
    {
        $this->load->model('mdl_est_status');
        return $this->response($this->mdl_leads_status->get_many_by(['lead_status_active' => 1])->order_by('lead_status_priority'),200);
    }

    function ajax_get_lead_reasons()
    {
        $this->load->model('mdl_leads_reason');
        return $this->response($this->mdl_leads_reason->get_many_by(['reason_active' => 1]),200);
    }
	
	function preupload() {
        $this->load->model('mdl_leads');
        $leadId = $this->input->post('id');
		$client_id = $this->input->post('client_id');
        $uuids = $this->input->post('files_uuids');
        $uuids = $uuids ? explode(',', $uuids) : [];
		
        $lead = $this->mdl_leads->find_by_id($leadId);
        if(!$lead && $leadId != 0)
            die(json_encode(['status' => FALSE]));
        
        $max = 1;
                      
		$to_tmp = '';
		if($leadId == 0){
			$to_tmp = 'tmp/';
		}
		
		if($client_id != 'null') {
			$path = 'uploads/clients_files/' . $client_id . '/leads/' . $to_tmp . str_pad($leadId, 5, '0', STR_PAD_LEFT) . '-L/';
		} else {
			$path = 'uploads/clients_files/tmp/lead/';
		}
        
		
		$files = bucketScanDir($path);
		if (count($files) && $files) {
			foreach($files as $file)
			{
				preg_match('/lead_no_' . $leadId . '-L.*?_([0-9]{1,})/is', $file, $num);
				if(isset($num[1]) && ($num[1] + 1) > $max)
					$max = $num[1] + 1;
				preg_match('/pdf_lead_no_' . $leadId . '-L.*?_([0-9]{1,})/is', $file, $num1);
				if(isset($num1[1]) && ($num1[1] + 1) > $max)
					$max = $num[1] + 1;
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
                
				$ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
				$suffix = $ext == 'pdf' ? 'pdf_' : NULL;
				$config['file_name'] = $suffix . 'lead_no_' . $leadId . '-L_' . $max++ . '.' . $ext;
                
                $config['upload_path'] = $path;
                $config['allowed_types'] = 'gif|jpg|jpeg|png|pdf|GIF|JPG|JPEG|PNG|PDF';

                $this->upload->initialize($config);
                if ($this->upload->do_upload('file')) {
                    $uploadData = $this->upload->data();
                    $photos[] = [
                        'id' => $lead->lead_id??null,
                        'uuid' => $uuids[$key] ?? NULL,
                        'filepath' => $path . $uploadData['file_name'],
                        'name' => $uploadData['file_name'],
                        'size' => $_FILES['file']['size'],
                        'type' => $_FILES['file']['type'],
                        'url' => base_url($path . $uploadData['file_name'])
                    ];
                    
                } else {
                    $photos[] = [
                        'error' => strip_tags($this->upload->display_errors())
                    ];
                }
            }
        }
        
        die(json_encode([
            'status' => TRUE,
            'data' => $photos
        ]));
    }
	
	function deleteFile()
	{		
		$name = $this->input->post('name');		
		$client_id = $this->input->post('client_id');
		$lead_id = $this->input->post('lead_id');
		
		if($client_id == null || $client_id == '') {
			$new_path = 'uploads/clients_files/tmp/lead/';
			if (is_bucket_file($new_path . $name)) {
				if (bucket_unlink($new_path . $name)) {                
					echo json_encode(array('type' => 'ok'));
					return;
				}
				echo json_encode(array('type' => 'error', 'message' => 'Permission denied'));
				return;
				
			}
		}
		
		$path = 'uploads/clients_files/' . $client_id . '/leads/' . str_pad($lead_id, 5, '0', STR_PAD_LEFT) . '-L/';
		if (is_bucket_file($path . $name)) {
			if (bucket_unlink($path . $name)) {                
                echo json_encode(array('type' => 'ok'));
                return;
            }
			echo json_encode(array('type' => 'error', 'message' => 'Permission denied'));
            return;
			
		}
		$tmpPath = 'uploads/clients_files/' . $client_id . '/leads/tmp/' . str_pad($lead_id, 5, '0', STR_PAD_LEFT) . '-L/';
        if (is_bucket_file($tmpPath . $name)) {
            if (bucket_unlink($tmpPath . $name)){
				echo json_encode(array('type' => 'ok'));
                return;
            }
			echo json_encode(array('type' => 'error', 'message' => 'Permission denied'));
            return;            
        }
		echo json_encode(array('type' => 'error', 'message' => 'Incorrect file'));
        return; 		
	}

    public function ajax_statuses_update()
    {
        $data   = $this->input->post('statuses') ?? [];
        $column = 'lead_status_priority';

        foreach ($data as $key => $id) {
            $priority[$column]  = $key;
            $response[] = [(int)$id, $column, $key, $this->mdl_leads_status->update((int)$id, $priority, false)];
        }

        $this->response(['result' => true, 'data' => $response]);
    }

	/*
	function upload() {
		$result = ['status' => FALSE];

		echo "<pre>";
		var_dump($this->input->post());
		var_dump($_FILES['files']);
		die;

		

        $this->load->model('mdl_leads');
        $leadId = $this->input->post('lead_id');
        $lead = $this->mdl_leads->find_by_id($leadId);
        if(!$lead)
            $this->response([
                'status' => FALSE,
                'message' => 'Incorrect Lead'
            ], 400);
        $path = 'uploads/clients_files/' . $lead->client_id . '/leads/tmp/' . str_pad($lead->lead_id, 5, '0', STR_PAD_LEFT) . '-E/';
        $photos = [];
        if (isset($_FILES['files']) && is_array($_FILES['files'])) {
            $this->load->library('upload');
            foreach ($_FILES['files']['name'] as $key => $val) {

                $_FILES['file']['name'] = $_FILES['files']['name'][$key];
                $_FILES['file']['type'] = $_FILES['files']['type'][$key];
                $_FILES['file']['tmp_name'] = $_FILES['files']['tmp_name'][$key];
                $_FILES['file']['error'] = $_FILES['files']['error'][$key];
                $_FILES['file']['size'] = $_FILES['files']['size'][$key];

                $config['upload_path'] = $path;
                $config['allowed_types'] = 'gif|jpg|jpeg|png|pdf|GIF|JPG|JPEG|PNG|PDF';
                $config['remove_spaces'] = TRUE;
                $config['encrypt_name'] = TRUE;
                $this->upload->initialize($config);
                if ($this->upload->do_upload('file')) {
                    $uploadData = $this->upload->data();
                    $photos[] = [
                        'filepath' => $path . $uploadData['file_name'],
                        'filename' => $uploadData['file_name']
                    ];
                } else {
                    $photos[] = [
                        'error' => strip_tags($this->upload->display_errors())
                    ];
                }
            }
        }
        $this->response([
            'status' => TRUE,
            'data' => $photos
        ], 200);
        
        return $result;
    }
	*/
 
}
//end of file leads.php
