<?php
class Mdl_events_orm extends JR_Model
{
	protected $_table = 'events';
	protected $primary_key = 'ev_id';
	//public $has_many = array('mdl_services_orm' => array('primary_key' => 'estimate_id', 'model' => 'estimates/mdl_services_orm'));
	//public $has_many = array('mdl_services_orm' => array('primary_key' => 'estimates_services.estimate_id', 'model' => 'mdl_services_orm'));
	//public $belongs_to = array( 'author' );
    //public $has_many = array( 'comments' );
	public $validate = array(
        array( 'field' => 'ev_event_id', 
               'label' => 'ev_event_id',
               'rules' => 'required|numeric'),

        /*array('field' => 'signature_image',
               'label' => 'Signature',
               'rules' => 'required')   
               */
    );

	protected $fillable = [
		'ev_event_id',
		'ev_team_id',
		'ev_estimate_id',
		'ev_tailgate_safety_form',
		'ev_start_time',
		'ev_end_time',
		'ev_start_work',
		'ev_end_work',
		'ev_start_travel',
		'ev_travel_time',
		'ev_on_site_time',
        'ev_date'
	];



	public function __construct() {
		parent::__construct();
	}
	
	function default($array, $form=[])
	{
		if($array['ev_start_time']===NULL)
			$array['ev_start_time'] = date("Y-m-d H:i:s");

		return $array;
	}

	function save($form, $id = NULL)
	{
		$data = array_filter(elements($this->fillable, $form, NULL));
		
		if(!$id){
			$ev = $this->get_by(['ev_event_id' => $form['ev_event_id'], 'ev_date' => $form['ev_date']]);
            if(!$ev)
			    return $this->insert($data);
            
            return $this->update($ev->ev_id, $data);
		}

		$this->skip_validation();
		$result = $this->update((int)$id, $data);
		return $result;
	}

	function get_started($event)
	{
		$query = $this->_database->where(['ev_event_id'=>(int)$event['ev_event_id'], 'ev_team_id'=>(int)$event['ev_team_id']])->where('ev_start_work IS NOT NULL')->order_by('ev_id', 'desc')->limit(1)->get($this->_table);

		if(!$query)
			return [];
		$result = $query->result_array();

		return isset($result[0])?$result[0]:[];
	}


}
