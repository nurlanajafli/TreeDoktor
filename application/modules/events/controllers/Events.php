<?php
if (!defined('BASEPATH'))
	exit('No direct script access allowed');

use application\modules\events\models\EventsReport;
use application\modules\schedule\models\ScheduleTeams;
use application\modules\schedule\models\ScheduleEvent;
use application\modules\schedule\models\Expense;
use application\modules\schedule\models\ExpenseType;
class Events extends MX_Controller
{
	function __construct()
	{
		parent::__construct();

		if (!isUserLoggedIn() && !(isset($this->token) && isset($this->user)) && $this->router->fetch_method() != 'tailgate_safety_pdf' && $this->router->fetch_method() != 'report_pdf')
			redirect('login');

		//Global settings:
		$this->_title = SITE_NAME;
		$this->load->model('mdl_schedule');
		$this->load->model('mdl_clients', 'mdl_clients');
		$this->load->model('mdl_estimates_orm', 'mdl_estimates_orm');
		$this->load->model('mdl_estimates', 'mdl_estimates');
		$this->load->model('mdl_services_orm', 'mdl_services_orm');
		$this->load->model('mdl_crews_orm', 'mdl_crews_orm');
		$this->load->model('mdl_equipment_orm', 'mdl_equipment_orm');
		$this->load->model('mdl_user', 'mdl_user');
		$this->load->model('mdl_vehicles');

		$this->load->model('mdl_workorders', 'mdl_workorders');
		$this->load->model('mdl_events_orm', 'mdl_events_orm');

		$this->load->model('mdl_events_reports', 'mdl_events_reports');
		$this->load->config('safety_meeting_form');
		$this->load->helper('file');
		$this->load->helper('events');

		$this->load->library('Common/EventActions');

        $this->load->model('mdl_safety_pdf_signs');

		/*
		//Load all common models and libraries here:
		$this->load->model('mdl_est_status');
		
		$this->load->model('mdl_services', 'mdl_services');
		$this->load->model('mdl_crews', 'mdl_crews');
		$this->load->model('mdl_leads', 'mdl_leads');

		$this->load->model('mdl_invoices', 'mdl_invoices');
		
		$this->load->model('mdl_reports', 'mdl_reports');
		$this->load->model('mdl_crew', 'crew_model');
		$this->load->model('mdl_employees', 'employees_model');
		$this->load->model('mdl_calls');
		$this->load->model('mdl_sms_messages');
		
		//Load helpers:
		$this->load->helper('estimates');
		$this->load->helper('business_days_cal');
		$this->load->library('pagination');
		$this->load->library('form_validation');
		$this->load->library('googlemaps');
		*/
	}	

	function get_team_report()
	{
	    $requert = request();
		if(!$requert->input('team_id'))
			return $this->ajax_response('error', ['msg'=>'Not valid report']);

		$expenseType = ExpenseType::whereSlug(ExpenseType::EMPLOYEE_BENEFITS)->first()->expense_type_id;

		$team = ScheduleTeams::find($requert->input('team_id'));
        $data['team'] = EventsReport::with([
		    'workorder.estimate.lead.client',
            'workorder.estimate.estimates_service',
            'team.schedule_teams_members_user' => function($query) use ($team, $expenseType){
                return $query->with([
                    'employeeWorked' => function($query) use ($team){
                        return $query->select('employee_worked.*')->with(['logins'])->dateInterval($team->team_date_start, $team->team_date_end)
                            ->bldExpense()
                            ->extraExpense();
                    },
                    'expenses_report' => function($query) use ($team){
                        return $query->originalFields()->withTeam($team->team_id);
                    },
                ]);
            }])->where('er_team_id', '=', $requert->input('team_id'))
            ->where('er_report_confirmed', '=', 0)
            ->get();

		$data['report_edit_fields'] = $this->eventactions->report_edit_fields();
		$view = $this->load->view("dashboard/event_info_reports", $data, true);
		return $this->response(['html'=>$view]);
	}

	function get_event_report()
	{
		if(!$this->input->post('id'))
			return $this->ajax_response('error', ['msg'=>'Not valid report']);

		$this->eventactions->setEventId($this->input->post('id'), $this->input->post('date'));
		$event = $this->eventactions->getEvent();
		//$report = array_values($this->eventactions->get_reports($this->input->post('id')));
        $date = $this->input->post('date');

		$team = ScheduleTeams::find($this->input->post('team_id'));
        $data['team'] = EventsReport::with([
            'workorder.estimate.lead.client',
            'workorder.estimate.estimates_service',
            'team.schedule_teams_members_user' => function($query) use ($team){
                return $query->with([
                    'employeeWorked' => function($query) use ($team){
                        return $query->with(['logins'])->dateInterval($team->team_date_start, $team->team_date_end);
                    },
                    'expenses_report' => function($query) use ($team){
                        return $query->originalFields()->lunchApproved()->extraApproved()->withTeam($team->team_id);
                    },
                ]);
            }])
            ->where('er_team_id', '=', $team->team_id)
            ->where('er_wo_id', $this->input->post('wo_id'))
            ->where(function ($query) use ($date){
                $query->whereDate('er_report_date', '=', $date);
                $query->orWhere(function ($query) use ($date){
                    $query->whereDate('er_event_date', '=', $date);
                });
            })
            ->get();


		if(!$event)
		{
			$this->eventactions->start_trevel(['ev_event_id'=>$this->input->post('id'), 'ev_team_id'=>$this->input->post('team_id'), 'wo_id'=>$this->input->post('wo_id'), 'ev_date'=>$this->input->post('date')]);
			$this->eventactions->setEventId($this->input->post('id'), $this->input->post('date'));
			$event = $this->eventactions->getEvent();
		}

		if(!$data['team']->count()){
			// er_event_id, er_estimate_id, er_team_id, er_wo_id
			$date = ($event->ev_date)?$event->ev_date:date("Y-m-d");
			$report_data = [
				'team_id'=>(int)$event->ev_team_id,
				'event_id'=>(int)$event->ev_event_id,
				'event_date'=>$date,
                'report_date'=>$this->input->post('date'),
				'wo_id'=>(int)$this->input->post('wo_id'),
				'estimate_id'=>(int)$event->ev_estimate_id,
				'payment_amount'=>0
			];
			$id = $this->eventactions->create_report($report_data);
            if($id){
                $data['team'] = EventsReport::with([
                    'workorder.estimate.lead.client',
                    'workorder.estimate.estimates_service',
                    'team.schedule_teams_members_user' => function($query) use ($team){
                        return $query->with([
                            'employeeWorked' => function($query) use ($team){
                                return $query->with(['logins'])->dateInterval($team->team_date_start, $team->team_date_end);
                            },
                            'expenses_report' => function($query) use ($team){
                                return $query->originalFields()->lunchApproved()->extraApproved()->withTeam($team->team_id);
                            },
                        ]);
                    }])
                    ->where('er_id', '=', $id)
                    ->get();
            }
            //$report = array_values($this->eventactions->get_reports($event->ev_event_id));
		}

		$data['report_edit_fields'] = $this->eventactions->report_edit_fields();

		//$data['command'] = isset($report[0])?$report[0]:[];
		//$data = $this->command_members($data, $event->ev_team_id);

		$data['report_event_id'] = $this->input->post('id');
		//$view = $this->load->view("dashboard_report/team_report", $data, true);
		$view = $this->load->view("dashboard/event_info_reports", $data, true);
		return $this->ajax_response('ok', ['html'=>$view]);

	}
	
	function team_event($id){

		$data['title'] = $this->_title . ' - Team Event';
		$event = $this->mdl_schedule->get_events(['schedule.id'=>$id]);
		if(empty($event))
			return redirect('dashboard');
		
		$data['event'] = $event[0];

		$data['estimate_data'] = $this->mdl_estimates_orm->with('mdl_services_orm')->get_full_estimate_data(['estimate_id' => $data['event']['estimate_id']], true, false, false)[0];
		$data['origin']= $data['destination'] = config_item('office_location');
		$data['estimate_services'] = [];

		foreach ($data['estimate_data']->mdl_services_orm as $key => $value)
			$data['estimate_services'][$value->service_id] = $value;	

		$data['client_data'] = $this->mdl_clients->find_by_id($data['estimate_data']->client_id);
		$data['client_contact'] = $this->mdl_clients->get_primary_client_contact($data['estimate_data']->client_id);

		$data['estimator_data'] = $this->mdl_user->find_by_id($data['estimate_data']->user_id);		
		$data['members'] = $this->mdl_schedule->get_team_members(array('employee_team_id' => $data['event']['team_id']));

        list($data['hospital_address'], $data['hospital_name'], $data['hospital_coords']) = getNearestHospitalInfo($data['estimate_data']->lat, $data['estimate_data']->lon, implode(',', [$data['estimate_data']->lead_address, $data['estimate_data']->lead_city, $data['estimate_data']->lead_state]));

		$workorder = $this->mdl_workorders->find_by_fields(['estimate_id' => $data['estimate_data']->estimate_id]);
		$data['files'] = (isset($workorder->wo_pdf_files) && $workorder->wo_pdf_files)?only_images(json_decode($workorder->wo_pdf_files, true)):[];
		
		$data['service_names'] = array_map(function($service_data){
			return $service_data->service->service_name;
		}, $data['estimate_data']->mdl_services_orm);

		$data['event_id'] = $id;
		$data['event_services'] = $this->mdl_schedule->get_event_services(['event_id' => $id]);
		$data["tools"] = $this->mdl_vehicles->get_many_by(array('vehicle_trailer' => 2, 'vehicle_disabled' => NULL));

		$data['started_events'] = $this->mdl_events_orm->get_started(['ev_event_id'=>$data['event']['id'], 'ev_team_id'=>$data['event']['team_id']]);
		$this->load->view('events/event', $data);
	}


	function file_upload($estimate_id)
	{

		$data = $this->do_upload_payments($estimate_id);
		if(!is_array($data) && !$data)
			$this->ajax_response('error', ['File is not valid']);

		$html = $this->load->view('events/_partials/gallery', $data, TRUE);
		$this->ajax_response('ok', ['html'=>$html]);
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
		
		$dir = $path . $config['file_name'];
		$workorder = $this->mdl_workorders->find_by_fields(['estimate_id' => $estimate_id]);
		
		$files = $workorder->wo_pdf_files ? json_decode($workorder->wo_pdf_files) : [];
		$files[] = $dir;
		$files = array_values(only_images($files));

		$str = json_encode($files);
		$this->mdl_workorders->update($workorder->id, array('wo_pdf_files' => $str));
		return ['files'=>$files];
	}

	function start_work(){

		$result = $this->eventactions->start_work($this->input->post());

		if(!$result)
			return $this->ajax_response('error', validation_errors_array());
		
		return $this->ajax_response('ok', ['msg'=>'Started']);
	}

	function stop_work(){
		$this->load->library('form_validation');

		$this->form_validation->set_rules('status', 'Finished', 'required');
		$this->form_validation->set_rules('expenses', 'Expenses', 'required');
		$this->form_validation->set_rules('event_damage', 'Damage', 'required');
		$this->form_validation->set_rules('malfunctions_equipment', 'Malfunctions Equipment', 'required');
		$this->form_validation->set_rules('client_signature_image', 'Signature', 'required');
		if($this->input->post('event_payment')=='yes')
			$this->form_validation->set_rules('payment_amount', 'Payment amount', 'required');

		if ($this->form_validation->run() == FALSE)
			return $this->ajax_response('error', validation_errors_array());
		

		$data = $this->input->post();
		$team_id = $this->input->post('ev_team_id');

		$this->eventactions->setEventId($this->input->post('ev_event_id'));
		$result = $this->eventactions->end_work($this->input->post());
		$event = $this->eventactions->getEvent();

		$response = ['msg'=>'Success!'];
		$data['event'] = $this->mdl_schedule->get_events(['schedule.id'=>$event->ev_event_id])[0];
		$data['estimate_data'] = $this->mdl_estimates_orm->with('mdl_services_orm')->get_full_estimate_data(['estimate_id' => $data['event']['estimate_id']], true, false, false)[0];

		$data['started_events'] = $this->mdl_events_orm->get_started($this->input->post());

		$response['event_buttons'] = $this->load->view('events/_partials/buttons', $data, true);
		$response['event_report'] = $this->load->view('events/_partials/event_report', $data, true);

		return $this->ajax_response('ok', $response);
	}

	function ride() {
		if(!$this->input->post('ev_event_id'))
			return $this->ajax_response('error', ['text'=>'Event is not valid']);

		$this->eventactions->start_trevel($this->input->post());
        return $this->ajax_response('ok', []);
    }

	function tailgate_safety_pdf($id, $date = null){
        $date = (!$date)?date("Y-m-d"):$date;

        if (!$id)
			return redirect('dashboard');

		$data['title'] = $this->_title . ' - Team Event';
        $data['origin']= $data['destination'] = config_item('office_location');

        $data['event'] = ScheduleEvent::with([
            'team' => function($query){
                return $query->with(['members', 'team_leader']);
            },
            'workorder.estimate'=>function($query){
                return $query->with([ 'lead', 'client', 'user' ]);
            },
            'event_works'=>function($query) use ($date){
                return $query->with(['safety_pdf_signs'=>function($query) use ($date){
                   return $query->with('user')->whereDate('date', '=', $date);
                }])->whereDate('ev_date', '=', $date)
                    ->whereNotNull('ev_start_work');
            }
        ])->find($id);

        list($data['hospital_address'], $data['hospital_name'], $data['hospital_coords']) = getNearestHospitalInfo($data['event']->workorder->estimate->lead->latitude, $data['event']->workorder->estimate->lead->longitude, implode(',', [$data['event']->workorder->estimate->lead->lead_address, $data['event']->workorder->estimate->lead->lead_city, $data['event']->workorder->estimate->lead->lead_state]));

        $data['signature'] = [
            'teamlead' => ($data['event']->event_works->count())?$data['event']->event_works->first()->safety_pdf_signs->where('is_teamlead', '=', 1)->first():[],
            'team' => ($data['event']->event_works->count())?$data['event']->event_works->first()->safety_pdf_signs->where('is_teamlead', '=', 0)->values()->all():[]
        ];

        $data['event_services'] = $this->mdl_schedule->get_event_services(['event_id' => $id]);
        $data['signature_path'] = event_signature_patch($data['event']->workorder->estimate_id, $data['event']->id);

        $data['started_events'] = ($data['event']->event_works->count())?$data['event']->event_works->first()->toArray():[];

        $html = $this->load->view('events/_partials/safety_meeting_form_pdf', $data, TRUE);

		$this->eventactions->create_pdf("safety_meeting_form_pdf.pdf", $html);
	}

	function report_pdf($id, $date = null){
        $date = (!$date)?date("Y-m-d"):$date;

		$this->load->library('mpdf');
		if (!$id)
			return redirect('dashboard');

		$data['title'] = $this->_title . ' - Team Event Report';

        $data['event'] = EventsReport::with([
            'team' => function($query){
                return $query->with(['members', 'team_leader']);
            },
            'workorder.estimate'=>function($query){
                return $query->with([ 'lead', 'client', 'user' ]);
            },
            'event_work'=>function($query) use ($date){
                $query->whereDate('ev_date', '=', $date);
            }, 'schedule_event.schedule_event_service'])->whereDate('er_report_date', '=', $date)
            ->where('er_event_id', $id)->first();


		if(!$data['event']){
            $html = $this->load->view('events/_partials/report_form_pdf', $data, TRUE);
            return $this->eventactions->create_pdf("report_pdf.pdf", $html);
        }

        $data['signature'] = false;
        $data['signature_path'] = false;

        $data['signature_path'] = event_signature_patch($data['event']->er_estimate_id, $data['event']->er_event_id.'_'.$data['event']->workorder->estimate->client_id);

        $data['files'] = ($data['event']->workorder->files_array) ? only_images($data['event']->workorder->files_array) : [];

        $data['travel_time'] = $data['event']->schedule_event->schedule_event_service->sum('service_travel_time');

		$html = $this->load->view('events/_partials/report_form_pdf', $data, TRUE);

		$this->eventactions->create_pdf("report_pdf.pdf", $html);
	}

    /**
     * @param array $eventData
     * @return array
     */
    protected function getEventWorkDates(array $eventData) {

        if(!isset($eventData['ev_event_id'])) {
            return [
                'event_start_wrok_date' => ' - ',
                'event_finish_wrok_date' => ' - ',
            ];
        }

        $started_events_report = $this->mdl_events_reports->get_report_event_by_event_id($eventData['ev_event_id']);

        if (isset($started_events_report) && !empty($started_events_report['er_event_start_work']) && !empty($started_events_report['er_event_start_work'])) {

            if (isset($eventData['ev_start_work']) && $eventData['ev_start_work']) {
                $startDate = date(getDateFormat(), strtotime($eventData['ev_start_work']));
                $event_start_wrok_date = date(getDateFormat() . ' ' . getTimeFormat(true), strtotime($startDate  . ' ' .  $started_events_report['er_event_start_work']));
            } else {
                $event_start_wrok_date = ' - ';
            }
        } else {
            $event_start_wrok_date = (isset($eventData['ev_start_work']) && $eventData['ev_start_work']) ? date(getDateFormat() . ' ' . getTimeFormat(true), strtotime($eventData['ev_start_work'])) :' - ';
        }

        if (isset($started_events_report) && !empty($started_events_report['er_event_finish_work']) && !empty($started_events_report['er_event_finish_work'])) {

            if (isset($eventData['ev_end_work']) && $eventData['ev_end_work']) {
                $finishDate = date(getDateFormat(), strtotime($eventData['ev_end_work']));
                $event_finish_wrok_date = '/'.date(getTimeFormat(true), strtotime($finishDate  . ' ' .  $started_events_report['er_event_finish_work']));
            } else {
                $event_finish_wrok_date = ' - ';
            }
        } else {
            $event_finish_wrok_date = (isset($eventData['ev_end_work']) && $eventData['ev_end_work']) ? '/'.date(getTimeFormat(true), strtotime($eventData['ev_end_work'])):'';
        }

        return [
            'event_start_wrok_date' => $event_start_wrok_date,
            'event_finish_wrok_date' => $event_finish_wrok_date,
        ];
    }

    function confirmReport(){

        $request = request();

        $Report = EventsReport::find($request->input('er_id'));
        if(!$Report->count()){
            return $this->response(['error'=>"Event is note defined"], 400);
        }
        $er_report_date = (!$Report->er_report_date)?$Report->er_event_date:$Report->er_report_date;
        $Report->er_report_confirmed = 1;
        $Report->er_report_date = $er_report_date;
        $Report->save();

        return $this->response([
            'status'=>"ok",
            'countEventsReport' => EventsReport::has('schedule_event')->noConfirmed()->distinct('er_team_id')->count()
        ], 200);
    }

	function update_event_report_field($event_id = false, $all = false){

		if(!$this->input->post('pk'))
			return $this->ajax_response('error', ['pk'=>'Not valid field']);

		$report = $this->eventactions->get_report($this->input->post('pk'));
		if(!$report)
			return $this->ajax_response('error', ['pk'=>'No report']);

		$update[$this->input->post('name')]=$this->input->post('value');
		$update_relations = $this->mdl_events_reports->get_edit_field_relations($this->input->post('name'), $this->input->post('value'));

		$this->eventactions->set_report($this->input->post('pk'), array_merge($update, $update_relations));

		$ReportQuery = EventsReport::with([
            'workorder.estimate.lead.client',
            'workorder.estimate.estimates_service',
            'team.schedule_teams_members_user'
        ]);

		if(!$all){
            //'schedule.event_report_confirmed'
            $data['team'] = $ReportQuery->where('er_team_id', '=', $report->er_team_id)->get();
        }
		else{
			$data['report_event_id'] = $event_id;
            $data['team'] = $ReportQuery->where('er_id', '=', $this->input->post('pk'))->get();
		}

		$data['report_edit_fields'] = $this->eventactions->report_edit_fields();
		$view = $this->load->view("dashboard_report/team_report", $data, true);
		return $this->ajax_response('ok', ['html'=>$view, 'team_id'=>$report->er_team_id]);
	}

	function update_members_expenses($team_id, $user_id, $date){

		$data = [$this->input->post('name')=>$this->input->post('value')];
		if(!$this->input->post('pk'))
		{
			$data['ter_team_id'] = $team_id;
			$data['ter_user_id'] = $user_id;
            $data['ter_date'] = $date;
		}

		$this->load->model('mdl_team_expeses_report');
		$this->mdl_team_expeses_report->save($data, $this->input->post('pk'));

		//$data = $this->command_members($data, $team_id, $user_id);
        $team = ScheduleTeams::find($team_id);
        $data['report'] = EventsReport::with([
            'workorder.estimate.lead.client',
            'workorder.estimate.estimates_service',
            'team.schedule_teams_members_user' => function($query) use ($team){
                return $query->with([
                    'employeeWorked' => function($query) use ($team){
                        return $query->with(['logins'])->dateInterval($team->team_date_start, $team->team_date_end);
                    },
                    'expenses_report' => function($query) use ($team){
                        return $query->originalFields()->lunchApproved()->extraApproved()->withTeam($team->team_id);
                    },
                ]);
            }])
            ->where('er_team_id', '=', $team_id)
            ->whereDate('er_report_date', '=', $date)
            ->first();


		$data['team_id'] = $team_id;
		$data['container'] = '#members-report-'.$data['report']->er_id;

		$data['html'] = $this->load->view('events/dashboard_report/members_report', $data, true);

		return $this->ajax_response('ok', $data);
	}

	function confirm_members_expenses($team_id, $user_id, $date){
		$this->load->model('mdl_team_expeses_report');
		$this->load->model('mdl_expense');

		$expense = $this->mdl_team_expeses_report->get($this->input->post('ter_id'));
		$expenses_type = $this->mdl_expense->get_expenses(['flag'=>'employee_benefits'], TRUE);

        $data = [
			'expense_type_id'=>element('expense_type_id', $expenses_type, 0),
			'expense_user_id'=>$expense->ter_user_id,
			'expense_team_id'=>$team_id,
			'expense_date' => strtotime($date),
			'expense_created_by'=>$this->session->userdata['user_id'],
			'expense_create_date'=>time(),
			'expense_payment'=>'Cash',
		];

        if($this->input->post('is_complete_bld')){
			$data['expense_amount'] = floatval($expense->ter_bld) / config_item('tax_rate');
			$data['expense_hst_amount'] = $data['expense_amount'] * (config_item('tax_perc') / 100);
			$data['expense_description'] = "B.L.D";
			$this->mdl_expense->insert_expense($data);
		}

		if($this->input->post('is_complete_extra')){
			$data['expense_amount'] = floatval($expense->ter_extra) / config_item('tax_rate');
			$data['expense_hst_amount'] = $data['expense_amount'] * (config_item('tax_perc') / 100);
			$data['expense_description'] = $expense->ter_extra_comment;
			$data['expense_is_extra'] = 1;
			$this->mdl_expense->insert_expense($data);
		}

        $team = ScheduleTeams::find($team_id);

        $data['report'] = EventsReport::with([
            'workorder.estimate.lead.client',
            'workorder.estimate.estimates_service',
            'team.members' => function($query) use ($team, $date){
                return $query->with([
                    'employeeWorked' => function($query) use ($team){
                        return $query->with(['logins'])->dateInterval($team->team_date_start, $team->team_date_end);
                    },
                    'expenses_report' => function($query) use ($team){
                        return $query->originalFields()->lunchApproved()->extraApproved()->withTeam($team->team_id);
                    },
                    'expense' => function($query) use ($team, $date){
                        return $query->where(['expense_team_id'=>$team->team_id])->where('expense_date', $date);
                    }
                ]);
            }])
            ->where('er_team_id', '=', $team_id)
            ->whereDate('er_report_date', '=', $date)
            ->first();

        $data['team_id'] = $team_id;
        $data['container'] = '#members-report-'.$data['report']->er_id;
		$data['html'] = $this->load->view('events/dashboard_report/members_report', $data, true);

		return $this->ajax_response('ok', $data);
	}

	private function command_members($data, $team_id, $user_id=NULL)
	{
		$data['command_members'] = $this->mdl_schedule->get_team_members(['employee_team_id' => $team_id]);

		$this->load->model('mdl_payroll');
		if(count($data['command_members']))
		{
			$this->load->model('mdl_worked');
			foreach($data['command_members'] as $nkey=>$member)
			{
			    $where['worked_user_id'] = $member['employee_id'];
				$where['worked_date >='] =  $member['team_date_start'];
                $where['worked_date <='] =  $member['team_date_end'];
				$data['command_members'][$nkey]['payroll'] = $this->mdl_payroll->get_by(array('payroll_start_date <=' => date('Y-m-d', $member['team_date']), 'payroll_end_date >=' => date('Y-m-d', $member['team_date'])));

				$data['command_members'][$nkey]['logouts'] = $this->mdl_worked->with('mdl_emp_login')->get_by($where);

				$date_from = strtotime(date("Y-m-d", $member['team_date']).' 00:00:00');
                $date_to = strtotime(date("Y-m-d", $member['team_date']).' 23:59:59');

                $expenses = Expense::where('expense_user_id', '=', $member['employee_id'])
                    ->where('expense_date', '>=', $date_from)
                    ->where('expense_date', '<=', $date_to)
                    ->get();
                if($expenses->count()){
                    $bld = $expenses->where('expense_is_extra', '=', 0)->first();
                    $extra = $expenses->where('expense_is_extra', '=', 1)->first();
                    $data['command_members'][$nkey]['bld_expense_value'] = ($bld)?($bld->expense_amount+$bld->expense_hst_amount):0;
                    $data['command_members'][$nkey]['extra_expense_value'] = ($extra)?($extra->expense_amount+$extra->expense_hst_amount):0;
                }


			}
		}
		return $data;
	}

	function ajax_response($status, $data){
		if($status=='ok'){
			$data['status'] = $status;
			echo json_encode($data);
		}
		else
			echo json_encode(['status'=>$status, 'errors'=>$data]);
			

		return;
	}

}
