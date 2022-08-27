<?php

class Mdl_trees extends JR_Model
{
	protected $_table = 'trees';
	protected $primary_key = 'trees_id';

	public $has_many = array('pests' => array('primary_key' => 'tpr_tree_id', 'model' => 'mdl_trees_relations'));
	
	
	public $before_delete = array('delete_tree');
	
	
	public function __construct() {
		parent::__construct();
		
		$this->load->model('mdl_trees_relations');
		$this->load->model('mdl_pests_products');
	}

	function get_trees($where = array(), $products = FALSE)
	{
		
		if(!empty($where))
			$this->db->where($where);
		
		$result = $this->_database->get($this->_table)->result();
		
		foreach ($result as $key => &$row)
		{
			
			if (is_object($row))
			{
				$valueFrom = $row->{$this->primary_key};
				$row->{'pests'} = $this->{'mdl_trees_relations'}->join('trees_pests', 'tpr_pest_id = pest_id')->get_many_by('tpr_tree_id', $valueFrom);
				if($products)
				{
					foreach($row->{'pests'} as $k=>&$v)
						$v->{'products'} = $this->{'mdl_pests_products'}->get_many_by('tpp_pest_id', $v->pest_id);
				}
			}
			else
			{
				$valueFrom = $row[$this->primary_key];
				$row['pests'] = $this->{'mdl_trees_relations'}->join('trees_pests', 'tpr_pest_id = pest_id')->get_many_by('tpr_tree_id', $valueFrom);
				if($products)
				{
					foreach($row['pests'] as $k=>&$v)
						$v['products'] = $this->{'mdl_pests_products'}->get_many_by('tpp_pest_id', $v['pest_id']);
				}
			}
		}
		
		$this->_with = array();
		return $result;
	}
	
	function global_search($where = array())
	{
		//$this->db->select("trees_id as id, " . $field . " as text, trees_pests.*", FALSE);
		if(empty($where))
			return FALSE;
		$this->db->join('tree_pest_relations', 'trees.trees_id = tree_pest_relations.tpr_tree_id', 'left');
		$this->db->join('trees_pests', 'tree_pest_relations.tpr_pest_id = trees_pests.pest_id', 'left');
		$this->db->join('trees_pests_products', 'trees_pests.pest_id = trees_pests_products.tpp_pest_id', 'left');
		$array = array(
			'trees_name_eng' => $where,
			'trees_name_lat' => $where,
			'tpr_notes' => $where,
			'tpr_description' => $where,
			'pest_eng_name' => $where,
			'pest_lat_name' => $where,
			'pest_description' => $where,
			'pest_notes' => $where,
			'pest_notes' => $where,
			'pest_affecting' => $where,
			'tpp_name' => $where,
			'tpp_rate' => $where,
		);
		$this->db->or_like($array);
		$this->db->group_by('pest_id');
		$result = $this->db->get($this->_table)->result();
		return $result;
		//SELECT * FROM `trees`
		//LEFT JOIN `tree_pest_relations` ON trees.trees_id = tree_pest_relations.tpr_tree_id
		//LEFT JOIN `trees_pests` ON tree_pest_relations.tpr_pest_id = trees_pests.pest_id
		//LEFT JOIN `trees_pests_products` ON trees_pests.pest_id = trees_pests_products.tpp_pest_id
		
		
		/*
			SELECT * FROM `trees`
			LEFT JOIN `tree_pest_relations` ON trees.trees_id = tree_pest_relations.tpr_tree_id
			LEFT JOIN `trees_pests` ON tree_pest_relations.tpr_pest_id = trees_pests.pest_id
			LEFT JOIN `trees_pests_products` ON trees_pests.pest_id = trees_pests_products.tpp_pest_id
			WHERE trees_name_eng LIKE '%Balsam%'
			OR trees_name_lat LIKE '%Balsam%'
			OR tpr_notes LIKE '%Balsam%'
			OR tpr_description LIKE '%Balsam%'
			OR pest_eng_name LIKE '%Balsam%'
			OR pest_lat_name LIKE '%Balsam%'
			OR pest_description LIKE '%Balsam%'
			OR pest_notes LIKE '%Balsam%'
			OR pest_affecting LIKE '%Balsam%'
			OR tpp_name LIKE '%Balsam%'
			OR tpp_rate LIKE '%Balsam%'
			OR tpp_notes LIKE '%Balsam%'
		*/
	}
}
