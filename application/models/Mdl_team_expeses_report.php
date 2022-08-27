<?php
class Mdl_team_expeses_report extends JR_Model
{
	protected $_table = 'team_expeses_report';
	protected $primary_key = 'ter_id';
	
	/*
	public $before_delete = array('recalculate_data');
	public $after_delete = array('recalculate');
	*/

	//public $has_many = array('mdl_services_orm' => array('primary_key' => 'estimate_id', 'model' => 'estimates/mdl_services_orm'));
	//public $has_many = array('mdl_services_orm' => array('primary_key' => 'estimates_services.estimate_id', 'model' => 'mdl_services_orm'));
	//public $belongs_to = array( 'author' );
    //public $has_many = array( 'comments' );
	
	//public $validate = [['field' => 'ev_event_id', 'label' => 'ev_event_id','rules' => 'required|numeric']];
	protected $prefix = 'ter_';
	protected $fillable = [
		'ter_team_id',
	    'ter_user_id',
	    'ter_bld',
	    'ter_extra',
	    'ter_extra_comment',
        'ter_date'
	];
	

	public function __construct() {
		parent::__construct();
	}

	public function save_many($data=[]){
		if(!count($data))
			return FALSE;

		foreach ($data as $key => $value) {
			$this->save($value);	
		}
	}

	public function save($form, $id=NULL)
	{
        $fillable = elements($this->fillable, $form, NULL);
		$data = array_filter($fillable, function ($item){
		    return ($item!==NULL && $item!==FALSE);
        });

		if(!$id){
			$expense = $this->get_by(['ter_team_id' => $form['ter_team_id'], 'ter_user_id'=>$form['ter_user_id'], 'ter_date'=>$form['ter_date']]);
            if(!$expense)
			    return $this->insert($data);
            
            return $this->update($expense->ter_id, $data);
		}

		$this->skip_validation();
		$result = $this->update((int)$id, $data);
		return $result;
	}

	
	/*----------------GETTERS-----------------*/

	public function getFields(){
		return $this->fillable;
	}
	
	public function getPrefix(){
		return $this->prefix;
	}
	
	public function getExpensesForDashboard($teamId) {
		$this->db->select('ter.ter_user_id as user_id, ter.ter_bld as lunch, ter.ter_extra as extra, ter.ter_extra_comment as note, ter.ter_id as expense_id, '.
						  'exl.expense_id as lunch_approved, exe.expense_id as extra_approved');
		$this->db->from($this->_table . ' ter');
		$this->db->join('expenses exl', 'exl.expense_user_id = ter.ter_user_id and exl.expense_team_id = ter.ter_team_id and exl.expense_is_extra = 0', 'left');
		$this->db->join('expenses exe', 'exe.expense_user_id = ter.ter_user_id and exe.expense_team_id = ter.ter_team_id and exe.expense_is_extra = 1', 'left');
		$this->db->where('ter.ter_team_id', $teamId);
		$this->db->group_by('ter.ter_user_id');
		return $this->db->get()->result();
	}

}
