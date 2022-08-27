<?php
class Mdl_events_reports extends JR_Model
{
	protected $_table = 'events_reports';
	protected $primary_key = 'er_id';

	/*
	
	public $after_create = array('addon_login');
	*/

	
	public $before_update = array('format_dates');
	public $after_create = array('after_create_report');
	public $after_update = array('after_update_report');
	
	/*
	public $before_delete = array('recalculate_data');
	public $after_delete = array('recalculate');
	*/

	//public $has_many = array('mdl_services_orm' => array('primary_key' => 'estimate_id', 'model' => 'estimates/mdl_services_orm'));
	//public $has_many = array('mdl_services_orm' => array('primary_key' => 'estimates_services.estimate_id', 'model' => 'mdl_services_orm'));
	//public $belongs_to = array( 'author' );
    //public $has_many = array( 'comments' );
	
	//public $validate = [['field' => 'ev_event_id', 'label' => 'ev_event_id','rules' => 'required|numeric']];
	protected $prefix = 'er_';
	protected $fillable = [
		'er_event_id',
	    'er_estimate_id',
	    'er_team_id',
	    'er_wo_id',
	    'er_event_payment',
	    'er_event_payment_type',
	    'er_event_work_remaining',
	    'er_event_damage',
	    'er_event_damage_description',
	    'er_event_description',
	    'er_malfunctions_equipment',
	    'er_expenses',
	    'er_expenses_description',
	    'er_payment_amount',
	    'er_malfunctions_description',
	    
	    'er_event_date',
	    'er_event_start_work',
	    'er_event_finish_work',
	    
	 	'er_event_start_travel',   
	 	'er_travel_time',
		'er_on_site_time',
		 	
	    'er_event_status_work',
	    'er_team_fail_equipment',
	    'er_event_payment_amount',
        'er_report_date'
	];
	
	protected $edit_field_relations = [
		'er_event_status_work' => [
			'unfinished' => [
				'er_event_payment' => 'No',
				'er_event_payment_type' => '',
				'er_payment_amount' => '',
			]
		],
		'er_event_payment'=>[
			'no' => [
				'er_event_payment_type' => '',
				'er_payment_amount' => '',
			]
		],
		'er_event_damage' => [
			'no' => [
				'er_event_damage_description'=>'',
			]
		],
		'er_malfunctions_equipment' => [
			'no' => [
				'er_team_fail_equipment'=>''
			]
		],
		'er_expenses'=>[
			'no' => [
				'er_expenses_description'=>''
			]
		]
	];

	protected $edit_fields = [
		
		[
			'name'=>'er_report_date_view',
			'label'=>'Date:', 
			'edit' => false,
			//'callback'=>'report_date'
		],
		[	
			'name'=>'er_event_status_work', 
			'label'=>'Event Status Work:',
			'type'=>'select', 
			'source'=>"[{value: 'Finished', text: 'Finished'},{value: 'Unfinished', text: 'Unfinished'}]",
			'edit' => true
		],
		[	
			'name'=>'er_event_payment', 
			'label'=>'Payment:', 
			'type'=>'select', 
			'source'=>"[{value: 'Yes', text: 'Yes'},{value: 'No', text: 'No'}]",
			//'callback'=>'er_event_payment',
			'edit' => true,
			'visibility_condition' => 'is_finished_event'
		],
		[	
			'name'=>'er_event_payment_type', 
			'label'=>'Event Payment Type:', 
			'type'=>'select', 
			'source'=>"[{value: 'Cash', text: 'Cash'},{value: 'Check', text: 'Check'}]",
			//'callback'=>'er_event_payment_type',
			'edit' => true,
			'visibility_condition' => 'is_paid_event'
		],
		[
			'name'=>'er_payment_amount', 
			'label'=>'Event Payment:',
			//'callback'=>'er_payment_amount',
			'edit' => true,
			'visibility_condition' => 'is_paid_event',
			'class'=>'currency text-bold'
		],
		[
			'name'=>'er_event_work_remaining', 
			'label'=>'Event Work Remaining:',
			'type'=>'textarea',
			'edit' => true
		],
		[
			'name'=>'er_event_damage', 
			'label'=>'Event Damage:', 
			'type'=>'select', 
			'source'=>"[{value: 'Yes', text: 'Yes'},{value: 'No', text: 'No'}]",
			'edit' => true
		],
		[
			'name'=>'er_event_damage_description', 
			'label'=>'Event Damage Description:', 
			'type'=>'textarea',
			'edit' => true,
			'visibility_condition' => 'is_event_damage'
		],
		[
			'name'=>'er_event_description', 
			'label'=>'Event Description:', 
			'type'=>'textarea',
			'edit' => true
		],
		[
			'name'=>'er_malfunctions_equipment', 
			'label'=>'Malfunctions Equipment:',
			'type'=>'select', 
			'source'=>"[{value: 'Yes', text: 'Yes'},{value: 'No', text: 'No'}]",
			'edit' => true
		],
		[
			'name'=>'er_team_fail_equipment', 
			'label'=>'Malfunctions Description:', 
			'type'=>'textarea',
			'edit' => true,
			'visibility_condition' => 'is_malfunctions_equipment'
		],
		[
			'name'=>'er_expenses', 
			'label'=>'Expenses:',
			'type'=>'select', 
			'source'=>"[{value: 'Yes', text: 'Yes'},{value: 'No', text: 'No'}]",
			'edit' => true
		],
		[
			'name'=>'er_expenses_description', 
			'label'=>'Expenses Description:', 
			'type'=>'textarea',
			'edit' => true,
			'visibility_condition' => 'is_expenses'
		],
		[
			'name'=>'er_event_start_travel', 
			'label'=>'Start Travel:',
			'type'=>'time',
			'inputclass'=>'form-control',
			//'callback'=>'time_to_format',
			'edit' => true
		],
		[
			'name'=>'er_event_start_work', 
			'label'=>'End Travel/Start Work:',
			'type'=>'time',
			'inputclass'=>'form-control',
			//'callback'=>'datetime_to_time',
			'edit' => true
		],
		[
			'name'=>'er_event_finish_work', 
			'label'=>'Event Finish Work:',
			'type'=>'time',
			'inputclass'=>'form-control',
			//'callback'=>'datetime_to_time',
			'edit' => true
		],
		[
			'name'=>'er_travel_time', 
			'label'=>'Travel Time:', 
			//'callback'=>'time_to_hours',
			'edit' => false
		],
		[
			'name'=>'er_on_site_time', 
			'label'=>'Time for Work:', 
			//'callback'=>'time_to_hours',
			'class_callback'=>'estimator_time_class',
			'edit' => false
		],
		[
			'name'=>'er_estimator_time',
			'label'=>'Estimator Time:', 
			//'callback'=>'estimator_time',
			'edit' => false
		],
		
		
		/*
		['name'=>'er_event_id', 'label'=>''],
	    ['name'=>'er_estimate_id', 'label'=>''],
	    ['name'=>'er_team_id', 'label'=>''],
	    ['name'=>'er_wo_id', 'label'=>''],
	    ['name'=>'er_event_payment', 'label'=>''],
	    ['name'=>'er_event_payment_type', 'label'=>''],
	    ['name'=>'er_event_work_remaining', 'label'=>''],
	    ['name'=>'er_event_damage_description', 'label'=>''],
	    ['name'=>'er_malfunctions_equipment', 'label'=>''],
	    ['name'=>'er_expenses', 'label'=>''],
	    ['name'=>'er_expenses_description', 'label'=>''],
	    ['name'=>'er_malfunctions_description', 'label'=>''],
	    
	    ['name'=>'er_team_fail_equipment', 'label'=>''],
	    ['name'=>'er_event_payment_amount' 'label'=>'']
	    */
	];

	public function __construct() {
		parent::__construct();
	}

	public function save($form, $id=NULL)
	{
		$data = array_filter(elements($this->fillable, $form, NULL));
		if(!$id){
			$event = $this->get_by(['er_event_id' => $form['er_event_id'], 'er_report_date'=>$form['er_report_date']]);
            if(!$event)
			    return $this->insert($data);
            
            return $this->update($event->er_id, $data);
		}

		$this->skip_validation();
		$result = $this->update((int)$id, $data);
		return $result;
	}

	public function format_dates($row, $id){
		
		/*$insert['er_event_start_travel']    = date("H:i a", strtotime($this->event->ev_start_travel));
        $insert['er_travel_time'] = strtotime($this->event->ev_start_work)-strtotime($this->event->ev_start_travel);
        $insert['er_on_site_time'] = time()-strtotime($this->event->ev_start_work); 
		*/
		//var_dump($id, $row);
		//die;
		return $row;
	}

	public function after_create_report($id){
		$this->calc_times($id);
		return TRUE;
	}
	public function after_update_report($row, $result){
		$this->calc_times($this->primary_value);
		return TRUE;
	}
	public function calc_times($id){
		$row = $this->get($id);

		$start_work = strtotime($row->er_event_date.' '.$row->er_event_start_work);
		$start_travel = strtotime($row->er_event_date.' '.$row->er_event_start_travel);
		$finish_work = strtotime($row->er_event_date.' '.$row->er_event_finish_work);

        $update['er_travel_time'] = $start_work - $start_travel;
        $update['er_on_site_time'] = $finish_work-$start_work; 

        $this->db->update($this->_table, $update, [$this->primary_key => $id]);
        return TRUE;
	}

	public function report_events($where = []){
		
		$this->db->select('events.ev_event_id, events_reports.er_wo_id, SUM(events.ev_travel_time+events.ev_on_site_time) as full_time', FALSE);
		$this->db->from('events');
		$this->db->join('events_reports', 'events_reports.er_event_id = events.ev_event_id');
		$this->db->group_by('events_reports.er_wo_id');
		$subquery = $this->db->_compile_select();
		$this->db->_reset_select();
		/* -------------end subquery ----------------*/

		$this->db->select("schedule.*, events.*, events_reports.*, workorder_time.*, schedule_teams.*, crews.crew_name, CONCAT(users.firstname, ' ', users.lastname) as leader_name, workorders.wo_pdf_files, workorders.workorder_no, workorders.estimate_id, leads.lead_address,  leads.lead_state, leads.lead_city, leads.lead_zip, leads.lead_country, leads.latitude, leads.longitude, clients.client_name", FALSE);
		
		$this->db->join('events', 'events.ev_event_id = schedule.id', "left");
		$this->db->join('events_reports', 'events_reports.er_event_id = schedule.id');
		$this->db->join('schedule_teams', 'schedule_teams.team_id = schedule.event_team_id', 'left');
		$this->db->join('schedule_teams_members', 'schedule_teams.team_id = schedule_teams_members.employee_team_id', 'left');
		$this->db->join('crews', 'crews.crew_id = schedule_teams.team_crew_id', 'left');
		$this->db->join('users', 'users.id = schedule_teams.team_leader_user_id', 'left');
		$this->db->join('workorders', 'workorders.id = schedule.event_wo_id', 'left');
		$this->db->join('estimates', 'estimates.estimate_id = workorders.estimate_id', 'left');
		$this->db->join('clients', 'clients.client_id = estimates.client_id', 'left');
		$this->db->join('leads', 'estimates.lead_id = leads.lead_id', 'left');
		//$this->db->join('workorder_status', 'workorders.wo_status = workorder_status.wo_status_id', 'left');

		$this->db->join("($subquery) workorder_time", "events_reports.er_wo_id=workorder_time.er_wo_id", 'left');

		if($where)
			$this->db->where($where);
		
		$this->db->order_by('schedule.event_start', 'ASC');
		$this->db->group_by('events_reports.er_id');
		$result = $this->db->get('schedule');

		return $result->result_array();
	}

    /**
     * @param int $event_id
     * @return array
     */
    public function get_report_event_by_event_id($event_id) {
        $this->db->select("events_reports.*");
        $this->db->where(['er_event_id' => $event_id]);
        $result = $this->db->get('events_reports');
        return $result->row_array();
    }

	
	/*----------------GETTERS-----------------*/

	public function get_edit_fields(){
		return $this->edit_fields;
	}

	public function getFields(){
		return $this->fillable;
	}
	
	public function getPrefix(){
		return $this->prefix;
	}

	public function get_edit_field_relations($field, $value)
	{
		if(!isset($this->edit_field_relations[$field]))
			return [];

		if(!isset($this->edit_field_relations[$field][strtolower($value)]))
			return [];

		return $this->edit_field_relations[$field][strtolower($value)];
	}

}