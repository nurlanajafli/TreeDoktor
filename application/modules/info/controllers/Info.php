<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Info extends MX_Controller
{

//*******************************************************************************************************************
//*************
//*************																						Info Controller;
//*************
//*******************************************************************************************************************	
	function __construct()
	{

		parent::__construct();
		show_404();
		//Checking if user is logged in;
		if (!isUserLoggedIn()) {
			redirect('login');
		}

		$this->_title = SITE_NAME;
		//load all common models and libraries here;
		$this->load->model('mdl_info', 'mdl_info');
		$this->load->model('mdl_trees');
		$this->load->model('mdl_trees_pests');
		$this->load->model('mdl_trees_relations');
	}

//*******************************************************************************************************************
//*************
//*************																									Index;
//*************
//*******************************************************************************************************************		 
	public function index()
	{

		$data['title'] = $this->_title . ' - Tree Info';

		$wdata = array();
		if ($this->input->post('search_keyword'))
			$data['search_keyword'] = $wdata['tree_data'] = $wdata['tree_common_name'] = $wdata['tree_scientific_name'] = $wdata['tree_family_name'] = $this->input->post('search_keyword');
		$data['trees'] = $this->mdl_info->find_all_with_limit($wdata);

		$this->load->view("index", $data);
	}

	public function details($id = NULL)
	{
		if (!$id)
			show_404();

		$data['tree'] = $this->mdl_info->find_by_id($id);

		$data['title'] = $this->_title . ' - ' . $data['tree']->tree_common_name;
		$this->load->view("details", $data);
	}
	
	public function trees()
	{
		$data['title'] = $this->_title . ' - Trees & Pests';
		$data['trees'] = $this->mdl_trees->get_trees([], FALSE);
		$data['pests'] = $this->mdl_trees_pests->order_by('pest_eng_name')->get_all();
		
		$this->load->view("index_trees_pests", $data);
	}
	
	public function profile($id = NULL)
	{
		if (!$id)
			show_404();
		$tree = $this->mdl_trees->get_trees(['trees_id' => $id], TRUE);
		if(!empty($tree))
			$data['tree'] = $tree[0];
		$data['title'] = $this->_title . ' - ' . $data['tree']->trees_name_eng;
		//echo '<pre>'; var_dump($data['tree']); die;
		$this->load->view("profile", $data);
	}
	
	function add_tree()
	{
		$data['trees_name_eng'] = $this->input->post('name_eng');
		$data['trees_name_lat'] = $this->input->post('name_lat');
		$pests = ($this->input->post('pests')) ? $this->input->post('pests'):FALSE;
		$insert = $this->mdl_trees->insert($data);
		if($pests)
		{
			$new_pests = [];
			foreach($pests as $k=>$v)
			{
				$new_pests[$k]['tpr_tree_id'] = $insert;
				$new_pests[$k]['tpr_pest_id'] = $v;
			}
			$this->mdl_trees_relations->insert_many($new_pests);
		}
		redirect('info/profile/' . $insert, 'refresh');
	}
	
	function ajax_by_name()
	{
		/*$array = ['length' => 5, 'items' => [
				['id'=>1, 'text' => 'test'],
				['id'=>2, 'text' => 'test'],
				['id'=>3, 'text' => 'test'],
				['id'=>4, 'text' => 'test'],
				['id'=>5, 'text' => 'test'],
			]
		];*/
		 //pest_eng_name
		
		$result['trigger'] = $trigger = $this->input->post('trigger');
		$search = $this->input->post('name');
		
		$field = 'pest_eng_name';
		
		if($trigger == 'name_lat')
			$field = 'pest_lat_name';
		
		$result['items'] = $this->mdl_trees_pests->search_by_name($search, $field)->result_array();
		
		if($result['items'] && is_countable($result['items']))
		{
			$result['length'] = count($result['items']);
			$result['status'] = 'ok';
		}
		
		die(json_encode($result));
	}
	
	function add_pest()
	{
		echo '<pre>'; var_dump($_POST); die;
	}
	
	function search()
	{
		$search = $this->input->post('search');
		$result = array();
		if($search != '')
			$result = $this->mdl_trees->global_search($search);
	//	echo '<pre>'; var_dump($result); die;
		$trees['search_trees'] = array();
		$data = array();
		if(!empty($result))
		{
			//$end_result = '';
			foreach($result as $key=>$val)
			{
				
				foreach($val as $k=>$v)
				{
					//echo '<pre>'; var_dump(strripos($val->tpr_notes, $search)); die;
					if(strripos($v, $search) !== FALSE)
						$trees['search_trees'][$key][$k] =  str_ireplace($search, '<span style="color:#FF0000;text-transform: uppercase;">' . $search . '</span>', $v) . '</li>';
					else
						$trees['search_trees'][$key][$k] = $v;
				}
			
			}
		}
		//echo '<pre>'; var_dump($data); die;
		$data['html'] = $this->load->view('table_trees', $trees, TRUE);
		die(json_encode($data));	
	}
}
//end of file reports.php
