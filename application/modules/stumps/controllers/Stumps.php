<?php    if (!defined('BASEPATH'))
	exit('No direct script access allowed');


class Stumps extends MX_Controller
{

//*******************************************************************************************************************
//*************
//*************																						Leads Controller;
//*************
//*******************************************************************************************************************	
	var $statusesVal = ['new', 'grinded', 'cleaned_up', 'skipped', 'canceled', 'on_hold'];
	var $statusesSelect = [
			['value' => 'new', 'text' => 'New'],
			['value' => 'grinded', 'text' => 'Grinded/Injected'],
			['value' => 'cleaned_up', 'text' => 'Cleaned Up'],
			['value' => 'skipped', 'text' => 'Skipped'],
			['value' => 'canceled', 'text' => 'Canceled'],
			['value' => 'on_hold', 'text' => 'On Hold'],
		];
	var $orderColumns = [
		'id' => 'ABS(stump_unique_id)',
		'locates' => 'stump_locates',
		'address' => 'stump_street',
		'grid' => 'ABS(stump_map_grid)',
		'range' => 'stump_range',
		'grind' => 'stump_removal',//'grinded.firstname',
		'clean' => 'stump_clean',//'cleaned.firstname',
		'status' => 'stump_status_work'
	];


	function __construct()
	{
		parent::__construct();

		if (!isUserLoggedIn()) {
			redirect('login');
		}
		$this->_title = SITE_NAME;

		//load all common models and libraries here;
		$this->load->model('mdl_stumps', 'mdl_stumps');
		$this->load->model('mdl_user', 'mdl_users');

		//Load Libraries
		$this->load->library('form_validation');
		$this->load->library('googlemaps');
	}

	function index()
	{
		$this->stumps_list();
	}

	public function stumps_mapper($status = NULL, $client_id = NULL)
	{
		if($this->session->userdata('STP') != 3  && $this->session->userdata('STP') != 1  && $this->session->userdata('user_type') != "admin")
			redirect(base_url());

		$where = array();

		if (!$status)
			$status = 'new';
		if(intval($client_id)) {
			$client_id = intval($client_id);
			$where['stump_client_id'] = $client_id;
			$wdata['stump_client_id'] = $client_id;
		}

		$data['status'] = $status;

		$data['title'] = $this->_title . ' - Stumps';
		$data['menu_leads'] = "active";

		$where['stump_status'] = $status;
		//Set the map:
		$config['center'] = config_item('map_center');
		$config['zoom'] = 'auto';
		$this->googlemaps->initialize($config);
		$whusers['module_id'] = 'STP';
		$whusers['module_status'] = '1';
		$whusers['active_status'] = 'yes';
		
		$active_users = [];
		$users = $this->mdl_users->get_usermeta($whusers);
		if($users)
			$active_users = $users->result();
		$users = [];
		
		$search_keyword = "";
		$data['user_id'] = NULL;
		$data['statuses'] = $this->statusesVal;
		$data['statusesSelect'] = $this->statusesSelect;
		$data['placeholder'] = 'Name, address, unique id...';
		if ($this->input->get('q')) {
			$search_keyword = $this->input->get('q');
		}
		$data['search_keyword'] = $search_keyword;

		$arr = $this->mdl_stumps->get_all($search_keyword, $where, 0, 0);
		
		$users[] = ['value' => 0, 'text' => 'N/A'];
		foreach ($active_users as $active_user)
			$users[] = ['value' => $active_user->id, 'text' => $active_user->firstname . " " . $active_user->lastname];

		foreach ($arr as $key => $row) {

			if($row['stump_status_work'] == 0) $color = '#FFFFFF';
			if($row['stump_status_work'] == 1) $color = '#95B3D7';
			if($row['stump_status_work'] == 2) $color = '#C3D69B';
			$pinData['row'] = $row;
			$pinData['users'] = $users;
			$pinData['statuses'] = $this->statusesSelect;
			$marker_content = addslashes(str_replace(["\r\n", "\r", "\n"], "", $this->load->view('stump_map_infowindow', $pinData, TRUE)));

			$text = NULL;
			if($row['stump_status_work'] == 1)
				$text = 'L';
			if($row['stump_status_work'] == 2)
				$text = 'C';
			
			$color = $row['grinded_color'] ? $row['grinded_color'] : '#ffffff';
			
			$marker_style = mappin_svg($color, $text, FALSE);
		
			$marker = array();
			$marker['position'] = $row['stump_lat'] . ', ' . $row['stump_lon'];
			$marker['infowindow_content'] = $marker_content;
			$marker['icon'] = $marker_style;
			$marker['cursor'] = $row['stump_id'];
			$marker['onclick'] = "mapSelectable(marker_$key, event);";
			
			$this->googlemaps->add_marker($marker);
		}

		foreach($this->statusesVal as $key=>$val)
		{
			$wdata['stump_status'] = $val;
			$data['counter'][$val . '_count'] = $this->mdl_stumps->count_all($search_keyword, $wdata);
		}

		$polygon = is_array(config_item('stumps_polygons')) ? config_item('stumps_polygons') : [];

		foreach($polygon as $k=>$v)
		{
			$coords = FALSE;
			$marker = array();
			$polygon[$k]['fillOpacity'] = '0.04';
			$polygon[$k]['fillColor'] = $v['strokeColor'];
			$this->googlemaps->add_polygon($polygon[$k]);
			
			$coords = get_center_polygon($v['points']);
			if($coords)
			{
				$marker['clickable'] = FALSE;
				$text = strlen($k) == 1 ? '0' . strval($k) : $k;
				$marker_style = mappin_svg($v['strokeColor'], $text, FALSE);

				 
				$marker['icon'] = $marker_style;
				$marker['position'] = $coords[0] . ', ' . $coords[1];
				$marker['onclick'] = "mapSelectable(marker_$k, event);";
				
				$this->googlemaps->add_marker($marker);
			}
			//$data['map'] = $this->googlemaps->create_map();
		}
		//$this->googlemaps->add_polygon($polygon);
		$data['map'] = $this->googlemaps->create_map();

		//$data['map'] = $this->googlemaps->create_map();
		
		$whusers['module_id'] = 'STP';
		$whusers['module_status'] = '1';
		$whusers['active_status'] = 'yes';
		
		$data['users'] = $usersData = [];
		$usersData = $this->mdl_users->get_usermeta($whusers);
		if($usersData)
			$data['users'] = $usersData->result();
		 
		$data['client_id'] = $client_id;

		$this->load->view('map', $data);
		
	}
	
	public function my_mapper($status = NULL, $client_id = NULL)
	{
		if(!$this->session->userdata('STP')  && $this->session->userdata('user_type') != "admin")
			redirect(base_url());

        $polygon = is_array(config_item('stumps_polygons')) ? config_item('stumps_polygons') : [];
		foreach($polygon as $k=>$v)
		{
			$coords = FALSE;
			$marker = array();
			$polygon[$k]['fillOpacity'] = '0.04';
			$polygon[$k]['fillColor'] = $v['strokeColor'];
			$this->googlemaps->add_polygon($polygon[$k]);
			
			$coords = get_center_polygon($v['points']);
			if($coords)
			{
				$marker['clickable'] = FALSE;
				$text = strlen($k) == 1 ? '0' . strval($k) : $k;
				$marker_style = mappin_svg($v['strokeColor'], $text, FALSE);

				 
				$marker['icon'] = $marker_style;
				$marker['position'] = $coords[0] . ', ' . $coords[1];
				$marker['onclick'] = "mapSelectable(marker_$k, event);";
				
				$this->googlemaps->add_marker($marker);
			}
			//$data['map'] = $this->googlemaps->create_map();
		}




		$data['title'] = $this->_title . ' - Stumps';
		$data['menu_leads'] = "active";
		$where = array();

		if(intval($client_id)) {
			$client_id = intval($client_id);
			$where['stump_client_id'] = $client_id;
			$wdata['stump_client_id'] = $client_id;
		}

		//Set the map:
		$config['center'] = config_item('map_center');
		$config['zoom'] = 'auto';
		$this->googlemaps->initialize($config);
		$whusers['module_id'] = 'STP';
		$whusers['module_status'] = '1';
		$whusers['active_status'] = 'yes';
		
		$active_users = [];
		$users = $this->mdl_users->get_usermeta($whusers);
		if($users)
			$active_users = $users->result();
		$users = [];
		
		$search_keyword = "";
		$data['user_id'] = $this->session->userdata['user_id'];
		
		if (!$status)
			$status = 'new';
		$data['status'] = $status;
		$where['stump_status'] = $status;
		$data['statuses'] = $this->statusesVal;
		$data['statusesSelect'] = $this->statusesSelect;
		$data['placeholder'] = 'Name, address, unique id...';
		
		if ($this->input->get('q')) {
			$search_keyword = $this->input->get('q');
		}

		$data['search_keyword'] = $search_keyword;
		$arr = $this->mdl_stumps->get_my_all($search_keyword, $where, 0, 0);
		
		$users[] = ['value' => 0, 'text' => 'N/A'];
		foreach ($active_users as $active_user)
			$users[] = ['value' => $active_user->id, 'text' => $active_user->firstname . " " . $active_user->lastname];
		
		foreach ($arr as $row) {
			if($row['stump_status_work'] == 0) $color = '#FFFFFF';
			if($row['stump_status_work'] == 1) $color = '#95B3D7';
			if($row['stump_status_work'] == 2) $color = '#C3D69B';

			$pinData['row'] = $row;
			$pinData['users'] = $users;
			$pinData['statuses'] = $this->statusesSelect;
			$marker_content = str_replace(["\r\n", "\r", "\n"], "", $this->load->view('stump_map_infowindow', $pinData, TRUE));

			$text = NULL;
			if($row['stump_status_work'] == 1)
				$text = 'L';
			if($row['stump_status_work'] == 2)
				$text = 'C';

			$color = $row['grinded_color'] ? $row['grinded_color'] : '#ffffff';
			
			$marker_style = mappin_svg($color, $text, FALSE);
		
			$marker = array();
			$marker['position'] = $row['stump_lat'] . ', ' . $row['stump_lon'];
			$marker['infowindow_content'] = $marker_content;
			$marker['icon'] = $marker_style;
			
			$this->googlemaps->add_marker($marker);
		}

		foreach($this->statusesVal as $key=>$val)
		{
			$wdata['stump_status'] = $val;
			$data['counter'][$val . '_count'] = $this->mdl_stumps->count_my_all($search_keyword, $wdata);
		}

		$data['map'] = $this->googlemaps->create_map();
		$whusers['module_id'] = 'STP';
		$whusers['module_status'] = '1';
		$whusers['active_status'] = 'yes';
		
		$data['users'] = [];
		$usersData = $this->mdl_users->get_usermeta($whusers);
		if($usersData)
			$data['users'] = $usersData->result();
			
		$data['client_id'] = $client_id;

		$this->load->view('map', $data);
	}

	function my_stumps($status = 'new')
	{
		if(!$this->session->userdata('STP') && $this->session->userdata('user_type') != "admin")
			redirect(base_url());
		$data['statuses'] = $statuses = $this->statusesVal;
		$data['statusesSelect'] = $this->statusesSelect;
		$where = array();
		
		$search_keyword = "";
		$page = 1;
		
		if($this->uri->segment(3))
		{
			if(array_search($this->uri->segment(3), $statuses) !== FALSE) {
				$status = $this->uri->segment(3);
				$page = $this->uri->segment(4) ? $this->uri->segment(4) : 1;
			}
		}

		$order = [];
		$config["suffix"] = NULL;
		$type = 'asc';
		$field = NULL;
		$config["suffix"] = NULL;

		$order_segment = 5;
		if($this->uri->segment($order_segment) && isset($this->orderColumns[$this->uri->segment($order_segment)])) {
			$type = $this->uri->segment($order_segment + 1) == 'asc' || $this->uri->segment($order_segment + 1) == 'desc' ? $this->uri->segment($order_segment + 1) : 'asc';
			$field = $this->uri->segment($order_segment);
			$order[$this->orderColumns[$field]] = $type;
			$config["suffix"] = '/' . $field . '/' . $type;
		}

		
		if ($this->input->get('q')) {
			$search_keyword = $this->input->get('q');
			$config["suffix"] .= '/?q=' . $search_keyword;
		}
		$config["base_url"] = base_url('stumps/my_stumps/' . $status);
		$config['first_url'] = $config['base_url'] . '/1' . $config['suffix'];
		
		$data['field'] = $field;
		$data['type'] = $type;

		$this->load->library('pagination');
		$data['title'] = $this->_title . ' - My Stumps';

		$whusers['module_id'] = 'STP';
		$whusers['module_status'] = '1';
		$whusers['active_status'] = 'yes';
		
		$data['active_users'] = [];
		$users = $this->mdl_users->get_usermeta($whusers);
		if($users)
			$data['active_users'] = $users->result();
		$users = [];

		$data['user_id'] = $this->session->userdata['user_id'];
		$data['status'] = $status;
		$limit = 100;
		$start = $page - 1;
		$start = $start * $limit;
		$data['placeholder'] = 'Name, address, unique id...';
		
		$data['search_keyword'] = $search_keyword;
		
		$config["uri_segment"] = 3;
		if($status)
		{
			$where['stump_status'] = $status;
			$data['status'] = $status;
			$config["uri_segment"] = 4;
		}

		$data['stumps'] = $this->mdl_stumps->get_my_all($search_keyword, $where, $limit, $start, $order);

		foreach($this->statusesVal as $key=>$val)
		{
			$wdata['stump_status'] = $val;
			$data['counter'][$val . '_count'] = $this->mdl_stumps->count_my_all($search_keyword, $wdata);
		}

		$config['total_rows'] = $data['counter'][$status . '_count'];
		$config["per_page"] = $limit;
		$config['num_links'] = 5;
		$config['use_page_numbers'] = TRUE;
		$config['full_tag_open'] = '<ul class="pagination pagination-sm m-t-none m-b-none pull-right">';
		$config['full_tag_close'] = '</ul>';

		$this->pagination->initialize($config);

		$data["page"] = $page;
		$data["links"] = $this->pagination->create_links();
		$data['clients'] = $this->mdl_stumps->get_all_client();

		$this->load->view('profile_client', $data);
	}
	
	function stumps_list($status = 'new', $page = 1)
	{
		if($this->session->userdata('STP') < 1  && $this->session->userdata('user_type') != "admin")
			redirect(base_url());
		
		$data['statuses'] = $statuses = $this->statusesVal;
		$data['statusesSelect'] = $this->statusesSelect;
		
		$search_keyword = "";

		$page = 1;
		
		if($this->uri->segment(3))
		{
			$page = $this->uri->segment(3);
			if(array_search($this->uri->segment(3), $statuses) !== FALSE) {
				$status = $this->uri->segment(3);
				$page = $this->uri->segment(4) ? $this->uri->segment(4) : 1;
			}
		}

		if(!intval($page) && $page != 'all')
			$page = 1;

		$order = [];
		$config["suffix"] = NULL;
		$type = 'asc';
		$field = NULL;
		$config["suffix"] = NULL;
		if($this->uri->segment(5) && isset($this->orderColumns[$this->uri->segment(5)])) {
			$type = $this->uri->segment(6) == 'asc' || $this->uri->segment(6) == 'desc' ? $this->uri->segment(6) : 'asc';
			$field = $this->uri->segment(5);
			$order[$this->orderColumns[$field]] = $type;
			$config["suffix"] = '/' . $field . '/' . $type;
		}

		
		if ($this->input->get('q')) {
			$search_keyword = $this->input->get('q');
			$config["suffix"] .= '/?q=' . $search_keyword;
		}
		$config["base_url"] = base_url('stumps/stumps_list/' . $status);
		$config['first_url'] = $config['base_url'] . '/1' . $config['suffix'];
		
		$data['field'] = $field;
		$data['type'] = $type;

		$this->load->library('pagination');
		$data['title'] = $this->_title . ' - All Stumps';
		
		$whusers['module_id'] = 'STP';
		$whusers['module_status'] = '1';
		$whusers['active_status'] = 'yes';
		
		$data['active_users'] = [];
		$users = $this->mdl_users->get_usermeta($whusers);
        if ($users && $users->num_rows())
			$data['active_users'] = $users->result();
		
		$where = array();
		$data['user_id'] = NULL;
		$data['client_id'] = NULL;
		$data['status'] = $status;

		$limit = $start = 0;
		if($page != 'all') {
			$limit = 100;
			$start = $page - 1;
			$start = $start * $limit;
		}
		

		$data['placeholder'] = 'Name, address, unique id...';
		$data['search_keyword'] = $search_keyword;

		$config["uri_segment"] = 3;
		if($status)
		{
			$where = array('stump_status' => $status);
			$data['status'] = $status;
			$config["uri_segment"] = 4;
		}

		if($this->session->userdata('STP') == 1  || $this->session->userdata('STP') == 3  || $this->session->userdata('user_type') == "admin")
		{
			$data['stumps'] = $this->mdl_stumps->get_all($search_keyword, $where, $limit, $start, $order);
			foreach($this->statusesVal as $key=>$val)
			{
				$wdata['stump_status'] = $val;
				$data['counter'][$val . '_count'] = $this->mdl_stumps->count_all($search_keyword, $wdata);
			}
			$config["total_rows"] = $this->mdl_stumps->count_all($search_keyword, $where);
		}
		else
		{
			$data['stumps'] = $this->mdl_stumps->get_my_all($search_keyword, $where, $limit, $start, $order);
			foreach($this->statusesVal as $key=>$val)
			{
				$wdata['stump_status'] = $val;
				$data['counter'][$val . '_count'] = $this->mdl_stumps->count_my_all($search_keyword, $wdata);
			}
			$config["total_rows"] = $this->mdl_stumps->count_my_all($search_keyword, $where);
		}

		if($page != 'all') {
			$config["per_page"] = $limit;
			$config['num_links'] = 5;
			$config['use_page_numbers'] = TRUE;
			$config['full_tag_open'] = '<ul class="pagination pagination-sm m-t-none m-b-none pull-right">';
			$config['full_tag_close'] = '</ul>';

			$this->pagination->initialize($config);	
		}
		
		$data["links"] = $this->pagination->create_links();

		$data['clients'] = $this->mdl_stumps->get_all_client();
		$data['page'] = $page;
		$this->load->view('profile_client', $data);
	}

	function ajax_update_stump()
	{
		$result['status'] = 'error';
		if($this->session->userdata('STP') < 1 && $this->session->userdata('user_type') != "admin")
			die(json_encode($result));

		$stump_id = $this->input->post('pk');
		$value = $this->input->post('value') ? $this->input->post('value', true) : NULL;
		$name = $this->input->post('name', true);

		$udata = [$name => $value];
		if(is_array($value))
			$udata = $value;
		if(isset($udata['stump_address']) && $udata['stump_address'] != '') {
		    $udata['stump_house_number'] = intval($udata['stump_address']);
            $udata['stump_address'] = trim(preg_replace('/[0-9]+/', '', $udata['stump_address']));
        }
		$search_keyword = "";
		$data['placeholder'] = 'Name, address, unique id...';
		if (isset($_POST['search_keyword']) && $_POST['search_keyword'] != '') {
			$search_keyword = $_POST['search_keyword'];
		}
		$data['search_keyword'] = $search_keyword;
		if($stump_id)
		{
			if($this->mdl_stumps->update_stumps($udata, array('stump_id' => $stump_id))) {

				$wdata['stump_id'] = $stump_id;
				/*if($this->input->post('status'))
					$wdata['stump_status'] = $this->input->post('status');
				if($this->input->post('user_id'))
					$wdata['stump_assigned'] = $this->input->post('user_id');*/

				$data['stumps'] = $this->mdl_stumps->get_all($search_keyword, $wdata);
				$row = $data['stumps'][0];

				$whusers['module_id'] = 'STP';
				$whusers['module_status'] = '1';
				$whusers['active_status'] = 'yes';
				$data['active_users'] = $this->mdl_users->get_usermeta($whusers)->result();
				$data['statusesSelect'] = $this->statusesSelect;

				if(!$this->input->post('map')) {
					$result['html'] = $this->load->view('stump_row', $data, TRUE);
				}
				else {
					$active_users = $this->mdl_users->get_usermeta($whusers)->result();
					$users[] = ['value' => 0, 'text' => 'N/A'];
					foreach ($active_users as $active_user)
						$users[] = ['value' => $active_user->id, 'text' => $active_user->firstname . " " . $active_user->lastname];

					$text = NULL;
					if($row['stump_status_work'] == 1)
						$text = 'L';
					if($row['stump_status_work'] == 2)
						$text = 'C';
					
					$color = $row['grinded_color'] ? $row['grinded_color'] : '#ffffff';

					$pinData['row'] = isset($data['stumps'][0]) ? $data['stumps'][0] : [];
					$result['lat'] = isset($data['stumps'][0]) ? $data['stumps'][0]['stump_lat'] : '';
					$result['lon'] = isset($data['stumps'][0]) ? $data['stumps'][0]['stump_lon'] : '';
					$result['pin'] = mappin_svg($color, $text, FALSE);
					$pinData['users'] = $users;
					$pinData['statuses'] = $this->statusesSelect;
					$result['html'] = !empty($pinData['row']) ? $this->load->view('stump_map_infowindow', $pinData, TRUE) : '';
				}


				$result['id'] = $stump_id;
				$result['status'] = 'ok';
			}
		}
		die(json_encode($result));
	}

	function ajax_update_stumps_array()
	{
		$result['status'] = 'error';
		if($this->session->userdata('STP') < 1 && $this->session->userdata('user_type') != "admin")
			die(json_encode($result));

		$stumps_ids = $this->input->post('ids');
		$value = $this->input->post('value') ? $this->input->post('value', true) : NULL;
		$name = $this->input->post('name', true);

		if(is_array($value))
			$udata = $value;
		else
			$udata = [$name => $value];


		if($stumps_ids && !empty($stumps_ids))
		{
			if(isset($value['stump_status'])){
				if(isset($value["stump_assigned"]) && ($value['stump_status'] == 'grinded' || $value['stump_status'] == 'cleaned_up')) 
				{
					$udata['stump_assigned'] = $value["stump_assigned"];
					$udata['stump_removal'] = $value["stump_removal"] ? DateTime::createFromFormat(getDateFormat() . ' ' . getTimeFormat(true), $value["stump_removal"])->format('Y-m-d H:i') : null;
					$udata['stump_clean'] = NULL;
				}
	    		
	    		if(isset($value["stump_cleaned"]) && $value['stump_status'] == 'cleaned_up')
	    		{
	    			$udata['stump_clean'] = $value["stump_cleaned_date"] ? DateTime::createFromFormat(getDateFormat() . ' ' . getTimeFormat(true), $value["stump_cleaned_date"])->format('Y-m-d H:i') : null;
	    			$udata['stump_clean_id'] = $value["stump_cleaned"];
	    		}
	    		$udata['stump_last_status_changed'] = date('Y-m-d H:i:s');
	    		unset($udata["stump_cleaned_date"]);
	    		unset($udata["stump_cleaned"]);
			}
			
			$this->mdl_stumps->update_batch_stumps($udata, $stumps_ids);
			$result['status'] = 'ok';

			$wdata = [];
			if($this->input->post('status'))
				$wdata['stump_status'] = $this->input->post('status');
			if($this->input->post('user_id'))
				$wdata['stump_assigned'] = $this->input->post('user_id');

			$stumps = $this->mdl_stumps->get_all_in('stump_id', $wdata, $stumps_ids);

			$whusers['module_id'] = 'STP';
			$whusers['module_status'] = '1';
			$whusers['active_status'] = 'yes';
			$data['active_users'] = $this->mdl_users->get_usermeta($whusers)->result();
			$data['statusesSelect'] = $this->statusesSelect;


			if(!$this->input->post('map')) {
				foreach ($stumps as $stump) {

					$text = NULL;
					if($stump['stump_status_work'] == 1)
						$text = 'L';
					if($stump['stump_status_work'] == 2)
						$text = 'C';
					
					$color = $stump['grinded_color'] ? $stump['grinded_color'] : '#ffffff';

					unset($stumps_ids[array_search($stump['stump_id'], $stumps_ids)]);
					$data['stumps'][0] = $stump;
					$result['data'][] = [
						'stump_id' => $stump['stump_id'],
						'html' => $this->load->view('stump_row', $data, TRUE),
						'pin' => mappin_svg($color, $text, FALSE)];
				}
			}
			else {
				$active_users = $this->mdl_users->get_usermeta($whusers)->result();
				$users[] = ['value' => 0, 'text' => 'N/A'];
				foreach ($active_users as $active_user)
					$users[] = ['value' => $active_user->id, 'text' => $active_user->firstname . " " . $active_user->lastname];
				foreach ($stumps as $stump) {
					unset($stumps_ids[array_search($stump['stump_id'], $stumps_ids)]);
					$pinData['row'] = $stump;

					$text = NULL;
					if($stump['stump_status_work'] == 1)
						$text = 'L';
					if($stump['stump_status_work'] == 2)
						$text = 'C';
					
					$color = $stump['grinded_color'] ? $stump['grinded_color'] : '#ffffff';

					//$result['lat'] = $stump['stump_lat'];
					//$result['lon'] = $stump['stump_lon'];
					$pinData['users'] = $users;
					$pinData['statuses'] = $this->statusesSelect;
					$result['data'][] = [
						'stump_id' => $stump['stump_id'],
						'html' => $this->load->view('stump_map_infowindow', $pinData, TRUE),
						'pin' => mappin_svg($color, $text, FALSE)
					];

				}
			}
			foreach ($stumps_ids as $id)
				$result['data'][] = ['stump_id' => $id, 'html' => NULL];

			$result['status'] = 'ok';
		}
		die(json_encode($result));
	}

	function change_status()
	{
		$result['status'] = 'error';
		if($this->session->userdata('STP') < 1  && $this->session->userdata('user_type') != "admin")
			die(json_encode($result));

		$status_array = $this->input->post('value');
		$stump_id = $this->input->post('pk');
		$status = isset($status_array['stump_status'])?$status_array['stump_status']:NULL;
		$search_keyword = "";
		$data['placeholder'] = 'Name, address, unique id...';
		if (isset($_POST['search_keyword']) && $_POST['search_keyword'] != '') {
			$search_keyword = $_POST['search_keyword'];
		}
		$data['search_keyword'] = $search_keyword;
		if($stump_id)
		{
			$stump = $this->mdl_stumps->find_by_id($stump_id);
			$newData['stump_status'] = $status;
			$newData['stump_removal'] = $status == 'new' ? NULL : $stump->stump_removal;
			$newData['stump_clean'] = $status == 'new' ? NULL : $stump->stump_clean;
			$newData['stump_removal'] = $status == 'grinded' ? date('Y-m-d') : $newData['stump_removal'];
			$newData['stump_clean'] = $status == 'cleaned_up' ? date('Y-m-d') : $newData['stump_clean'];
			$newData['stump_last_status_changed'] = date('Y-m-d H:i:s');

			if(isset($status_array["stump_assigned"]) && ($status == 'grinded' || $status == 'cleaned_up')) 
			{
			    $stump_removal = $status_array["stump_removal"] ? DateTime::createFromFormat(getDateFormat() . ' ' . getTimeFormat(true), $status_array["stump_removal"])->format('Y-m-d H:i') : null;
				$newData['stump_assigned'] = $status_array["stump_assigned"];
				$newData['stump_removal'] = $stump_removal;
				$newData['stump_clean'] = NULL;
			}
    		
    		if(isset($status_array["stump_cleaned"]) && $status == 'cleaned_up'){
    		    $stump_clearned = $status_array["stump_cleaned_date"] ? DateTime::createFromFormat(getDateFormat() . ' ' . getTimeFormat(true), $status_array["stump_cleaned_date"])->format('Y-m-d H:i') : null;
    			$newData['stump_clean'] = $stump_clearned;
    			$newData['stump_clean_id'] = $status_array["stump_cleaned"];
    		}
    		
			$this->mdl_stumps->update_stumps($newData, array('stump_id' => $stump_id));

			$wdata['stump_id'] = $stump_id;
			if($this->input->post('status'))
				$wdata['stump_status'] =  $this->input->post('status');
			
			if($this->input->post('user_id'))
				$data['stumps'] = $this->mdl_stumps->get_my_all($search_keyword, $wdata);
			else
				$data['stumps'] = $this->mdl_stumps->get_all($search_keyword, $wdata);

			$whusers['module_id'] = 'STP';
			$whusers['module_status'] = '1';
			$whusers['active_status'] = 'yes';
			$data['active_users'] = $this->mdl_users->get_usermeta($whusers)->result();
			$data['statusesSelect'] = $this->statusesSelect;
			$result['stump_removal'] = isset($data['stumps'][0]) ? $data['stumps'][0]['stump_removal'] : '';
			$result['stump_clean'] = isset($data['stumps'][0]) ? $data['stumps'][0]['stump_clean'] : '';
			if(!$this->input->post('map'))
				$result['html'] = $this->load->view('stump_row', $data, TRUE);
			else {
				$active_users = $this->mdl_users->get_usermeta($whusers)->result();
				$users[] = ['value' => 0, 'text' => 'N/A'];
				foreach ($active_users as $active_user)
					$users[] = ['value' => $active_user->id, 'text' => $active_user->firstname . " " . $active_user->lastname];
				$pinData['row'] = isset($data['stumps'][0]) ? $data['stumps'][0] : [];
				$pinData['users'] = $users;
				$pinData['statuses'] = $this->statusesSelect;
				$result['html'] = !empty($pinData['row']) ? $this->load->view('stump_map_infowindow', $pinData, TRUE) : '';
			}
			$result['id'] = $stump_id;
			$result['status'] = 'ok';
		}
		die(json_encode($result));
	}


	function client_stumps($id = NULL, $status = 'new')
	{
		if($this->session->userdata('STP') < 1  && $this->session->userdata('user_type') != "admin")
			redirect(base_url());

		if(!$id)
			redirect(base_url('stumps/stumps_list'));

		$data['statuses'] = $statuses = $this->statusesVal;
		$data['statusesSelect'] = $this->statusesSelect;

		$data['user_id'] = NULL;
		$data['client_id'] = $id;

		if(!$status)
			$status = NULL;
		$where['stump_status'] = $data['status'] = $status;
		$data['placeholder'] = 'Name, address, unique id...';
		$search_keyword = "";
		$page = 1;

		$config = array();
		$config["uri_segment"] = 5;
		if($this->uri->segment(4))
		{
			if(array_search($this->uri->segment(4), $statuses) !== FALSE) {
				$status = $this->uri->segment(4);
				$page = $this->uri->segment(5) ? $this->uri->segment(5) : 1;
			}
			else
				$page = $this->uri->segment(4);
		}

		$order = [];
		$config["suffix"] = NULL;
		$type = 'asc';
		$field = NULL;
		if($this->uri->segment(6) && isset($this->orderColumns[$this->uri->segment(6)])) {
			$type = $this->uri->segment(7) == 'asc' || $this->uri->segment(7) == 'desc' ? $this->uri->segment(7) : 'asc';
			$field = $this->uri->segment(6);
			$order[$this->orderColumns[$field]] = $type;
			$config["suffix"] = '/' . $field . '/' . $type;
		}

		
		if ($this->input->get('q')) {
			$search_keyword = $this->input->get('q');
			$config["suffix"] .= '/?q=' . $search_keyword;
		}
		$config["base_url"] = base_url('stumps/client_stumps/' . $id . '/' . $status);
		$config['first_url'] = $config['base_url'] . '/1' . $config['suffix'];
		
		
		$this->load->library('pagination');
		$data['user_id'] = NULL;
		$data['page'] = $page;
		$data['type'] = $type;
		$data['field'] = $field;

		$limit = 100;
		$start = $page - 1;
		$start = $start * $limit;

		
		$data['search_keyword'] = $search_keyword;
		$data['title'] = $this->_title . ' - Client Stumps';

		$whusers['module_id'] = 'STP';
		$whusers['module_status'] = '1';
		$whusers['active_status'] = 'yes';
		$data['active_users'] = $this->mdl_users->get_usermeta($whusers)->result();

		$data['client'] = $this->mdl_stumps->get_all_client(array('cl_id' => intval($id)));
		$where['stump_client_id'] = intval($id);

		if($this->session->userdata('STP') == 1  || $this->session->userdata('user_type') == "admin")
		{
			$data['stumps'] = $this->mdl_stumps->get_all($search_keyword, $where, $limit, $start, $order);
			foreach($this->statusesVal as $key=>$val)
			{
				$where['stump_status'] = $val;
				$data['counter'][$val . '_count'] = $this->mdl_stumps->count_all($search_keyword, $where);
			}
		}
		else
		{
			$data['stumps'] = $this->mdl_stumps->get_my_all($search_keyword, $where, $limit, $start, $order);
			foreach($this->statusesVal as $key=>$val)
			{
				$where['stump_status'] = $val;
				$data['counter'][$val . '_count'] = $this->mdl_stumps->count_my_all($search_keyword, $where);
			}
		}

		$config["total_rows"] = isset($data['counter'][$status . '_count']) ? $data['counter'][$status . '_count'] : 0;
		$config["per_page"] = $limit;
		$config['num_links'] = 5;
		$config['use_page_numbers'] = TRUE;
		$config['full_tag_open'] = '<ul class="pagination pagination-sm m-t-none m-b-none pull-right">';
		$config['full_tag_close'] = '</ul>';

		$this->pagination->initialize($config);
		$data['links'] = $this->pagination->create_links();

		$data['clients'] = $this->mdl_stumps->get_all_client();
		$this->load->view('profile_client', $data);
	}

	function my_client_stumps($id = NULL, $status = 'new')
	{
		if($this->session->userdata('STP') < 1  && $this->session->userdata('user_type') != "admin")
			redirect(base_url());

		if(!$id)
			redirect(base_url('stumps/stumps_list'));

		$data['statuses'] = $statuses = $this->statusesVal;
		$data['statusesSelect'] = $this->statusesSelect;

		$data['user_id'] = NULL;
		$data['client_id'] = $id;

		if(!$status)
			$status = NULL;
		$where['stump_status'] = $data['status'] = $status;
		$data['placeholder'] = 'Name, address, unique id...';
		$search_keyword = "";
		$page = 1;

		$config = array();
		$config["uri_segment"] = 5;
		if($this->uri->segment(4))
		{
			$page = $this->uri->segment(4);
			if(array_search($this->uri->segment(4), $statuses) !== FALSE) {
				$status = $this->uri->segment(4);
				$page = $this->uri->segment(5) ? $this->uri->segment(5) : 1;
			}	
		}

		$order = [];
		$config["suffix"] = NULL;
		$type = 'asc';
		$field = NULL;
		if($this->uri->segment(6) && isset($this->orderColumns[$this->uri->segment(6)])) {
			$type = $this->uri->segment(7) == 'asc' || $this->uri->segment(7) == 'desc' ? $this->uri->segment(7) : 'asc';
			$field = $this->uri->segment(6);
			$order[$this->orderColumns[$field]] = $type;
			$config["suffix"] = '/' . $field . '/' . $type;
		}
		
		if ($this->input->get('q')) {
			$search_keyword = $this->input->get('q');
			$config["suffix"] .= '/?q=' . $search_keyword;
		}
		$config["base_url"] = base_url('stumps/profile_client/' . $id . '/' . $status);
		$config['first_url'] = $config['base_url'] . '/1' . $config['suffix'];

		$this->load->library('pagination');
		$data['user_id'] = NULL;
		$data['page'] = $page;
		$data['type'] = $type;
		$data['field'] = $field;

		$limit = 100;
		$start = $page - 1;
		$start = $start * $limit;

		$data['search_keyword'] = $search_keyword;
		$data['title'] = $this->_title . ' - Client Stumps';

		$whusers['module_id'] = 'STP';
		$whusers['module_status'] = '1';
		$whusers['active_status'] = 'yes';
		$data['active_users'] = $this->mdl_users->get_usermeta($whusers)->result();

		$data['client'] = $this->mdl_stumps->get_all_client(array('cl_id' => intval($id)));
		$where['stump_client_id'] = intval($id);
		//$where['stump_client_id'] = intval($id);

		if($this->session->userdata('STP') == 1  || $this->session->userdata('user_type') == "admin")
		{
			$data['stumps'] = $this->mdl_stumps->get_my_all($search_keyword, $where, $limit, $start, $order);
			foreach($this->statusesVal as $key=>$val)
			{
				$where['stump_status'] = $val;
				$data['counter'][$val . '_count'] = $this->mdl_stumps->count_my_all($search_keyword, $where);
			}
		}
		else
		{
			$data['stumps'] = $this->mdl_stumps->get_my_all($search_keyword, $where, $limit, $start, $order);
			foreach($this->statusesVal as $key=>$val)
			{
				$where['stump_status'] = $val;
				$data['counter'][$val . '_count'] = $this->mdl_stumps->count_my_all($search_keyword, $where);
			}
		}

		$config["total_rows"] = isset($data['counter'][$status . '_count']) ? $data['counter'][$status . '_count'] : 0;
		$config["per_page"] = $limit;
		$config['num_links'] = 5;
		$config['use_page_numbers'] = TRUE;
		$config['full_tag_open'] = '<ul class="pagination pagination-sm m-t-none m-b-none pull-right">';
		$config['full_tag_close'] = '</ul>';

		$this->pagination->initialize($config);
		$data['links'] = $this->pagination->create_links();

		$data['clients'] = $this->mdl_stumps->get_all_client();
		$this->load->view('profile_client', $data);
	}

	function report()
	{
		if($this->session->userdata('STP') < 1  && $this->session->userdata('user_type') != "admin")
			redirect(base_url());
		$data['title'] = $this->_title . ' - Stumps Report';
		$from = $this->input->post('from') ? $this->input->post('from') : date(getDateFormat(), strtotime("last Monday"));
		$to = $this->input->post('to') ? $this->input->post('to') : date(getDateFormat(), strtotime("Sunday"));
        $data['from'] = $from;
        $data['to'] = $to;
        $newTo = DateTime::createFromFormat(getDateFormat(), $to);
        $to = $newTo->format('Y-m-d');
        $newFrom = DateTime::createFromFormat(getDateFormat(), $from);
        $from = $newFrom->format('Y-m-d');

        //$wdata = ['stump_removal >=' => $from . ' 00:00:00', 'stump_removal <=' => $to . ' 23:59:59'];
		$wdata = ['stump_removal >=' => $from . ' 00:00:00', 'stump_removal <=' => $to . ' 23:59:59'];
		$data['grinded'] = $this->mdl_stumps->get_grinded_stat($wdata);

		$wdata = ['stump_clean >=' => $from, 'stump_clean <=' => $to];
		$data['cleaned'] = $this->mdl_stumps->get_cleaned_stat($wdata);

		$this->load->view('index_report', $data);
	}

	function stumps_clients()
	{
		if($this->session->userdata('STP') < 1  && $this->session->userdata('user_type') != "admin")
			redirect(base_url());
		$data['title'] = $this->_title . ' - Clients';
		$data['clients'] = $this->mdl_stumps->get_all_client(['cl_hidden' => 0]);
		$this->load->view('client_list', $data);
	}

	function save_stump_client()
	{
		if($this->session->userdata('STP') < 1  && $this->session->userdata('user_type') != "admin")
			redirect(base_url());
		$result['type'] = 'success';
		$result['field'] = array();
		$data['cl_name'] = $this->input->post('name');
		$data['cl_lastname'] = $this->input->post('lastname');
		if($data['cl_name'] == '' || $data['cl_name'] == ' ' || $data['cl_name'] == FALSE)
		{
			$result['type'] = 'error';
			$result['field'][] = 'name';
		}
		elseif($data['cl_lastname'] == '' || $data['cl_lastname'] == ' ' || $data['cl_lastname'] == FALSE)
		{
			$result['type'] = 'error';
			$result['field'][] = 'lastname';
		}
		else
			$result['id'] = $this->mdl_stumps->insert_stumps_client($data);

		die(json_encode($result));
	}

	function update_stump_client()
	{
		if($this->session->userdata('STP') < 1  && $this->session->userdata('user_type') != "admin")
			redirect(base_url());

		$result['type'] = 'error';
		$result['field'] = array();
		$cl_id = $this->input->post('client_id');
		$data['cl_name'] = $this->input->post('name');
		$data['cl_lastname'] = $this->input->post('lastname');
		if($data['cl_name'] == '' || $data['cl_name'] == ' ' || $data['cl_name'] == FALSE)
		{
			$result['type'] = 'error';
			$result['field'][] = 'name';
		}
		elseif($data['cl_lastname'] == '' || $data['cl_lastname'] == ' ' || $data['cl_lastname'] == FALSE)
		{
			$result['type'] = 'error';
			$result['field'][] = 'lastname';
		}
		elseif(empty($result['field']) && $this->mdl_stumps->update_stumps_client($data, array('cl_id' => $cl_id)))
			$result['type'] = 'success';

		die(json_encode($result));
	}

	function ajax_delete_client()
	{
		if($this->session->userdata('STP') < 1  && $this->session->userdata('user_type') != "admin")
			die(json_encode(array('status' => 'error')));

		$id = $this->input->post('id');
		if (intval($id))
		{
			$this->mdl_stumps->delete_stumps_client($id);
			die(json_encode(array('status' => 'ok')));
		}
		die(json_encode(array('status' => 'error')));
	}

	function save_stump()
	{
		$result['status'] = 'error';
		if($this->session->userdata('STP') < 1  && $this->session->userdata('user_type') != "admin")
			die(json_encode($result));

		$result['errors'] = array();
		$data['stump_address'] = $this->input->post('stump_address');
		if(!$data['stump_address'])
			$result['errors']['stump_address'] = 'Address is required';

		$data['stump_city'] = $this->input->post('stump_city');
		if(!$data['stump_city'])
			$result['errors']['stump_city'] = 'City is required';

		$data['stump_state'] = $this->input->post('stump_state');
		if(!$data['stump_state'])
			$result['errors']['stump_state'] = 'State is required';

		$data['stump_desc'] = $this->input->post('stump_info');
		$data['stump_map_grid'] = $this->input->post('stump_map_grid');
		$data['stump_side'] = $this->input->post('stump_side');
		$data['stump_range'] = $this->input->post('stump_range');
		$data['stump_contractor_notes'] = $this->input->post('stump_desc');
		$data['stump_locates'] = $this->input->post('stump_locates');
		$data['stump_client_id'] = $this->input->post('stump_client_id');
		$data['stump_lat'] = $this->input->post('stump_lat');
		$data['stump_lon'] = $this->input->post('stump_lon');
		$data['stump_status'] = 'new';
		$data['stump_status_work'] = 0;
		$data['stump_last_status_changed'] = date('Y-m-d H:i:s');

		$maxUniq = $this->mdl_stumps->get_max_unique_id();

		$data['stump_unique_id'] = isset($maxUniq['stump_unique_id']) ? $maxUniq['stump_unique_id'] + 1 : 1;


		if(empty($result['errors'])) {
			$this->mdl_stumps->insert_stumps($data);
			$result['status'] = 'ok';
		}

		die(json_encode($result));
	}



	
	function ajax_delete_stump()
	{	
		if($this->session->userdata('STP') < 1  && $this->session->userdata('user_type') != "admin")
			die(json_encode(array('status' => 'error')));
		
		$id = $this->input->post('stump_id');
		if ($id)
		{
			$this->mdl_stumps->delete_stumps($id);

			die(json_encode(array('status' => 'ok')));
		}
		die(json_encode(array('status' => 'error')));
	}

	function xlsx($client_id = NULL)
	{
		vaughan_stumps_report($client_id);


		/*$wdata = [];
		if($client_id)
			$wdata['stump_client_id'] = $client_id;

		$stumps = $this->mdl_stumps->get_all_with_team($wdata, 'stump_id ASC');
		
		//echo "<pre>";
		//var_dump($stumps);
		//die;
		markham_stumps_report($stumps);*/
	}
}
//end of file leads.php
