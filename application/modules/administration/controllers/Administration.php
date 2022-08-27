<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

use application\modules\administration\models\FollowupSettings;
use application\modules\clients\models\Tag;

class Administration extends MX_Controller
{

	//*******************************************************************************************************************
//*************
//*************
//*************											Administration Controller
//*************
//*************
//*******************************************************************************************************************	
	function __construct()
	{
		parent::__construct();

		if (!isUserLoggedIn()) {
			redirect('login');
		}

		$this->_title = SITE_NAME;

		//load all common models and libraries here;
		$this->load->model('mdl_administration', 'mdl_administration');
		$this->load->model('mdl_est_status');
	}
//*******************************************************************************************************************
//*************
//*************
//*************												Index function;
//*************
//*************
//*******************************************************************************************************************	

	public function index()
	{
		show_404();
		if ($this->session->userdata('user_type') != "admin") {
			show_404();
		}
		// Set title
		$data['title'] = "Database backup";
		// Set menu status
		$data['menu_administration'] = "active";
		$dir_info = get_dir_file_info(DB_BACKUP_PATH);
		$dir_arr = array();
		foreach ($dir_info as $row) {
			$dir_arr[] = $row;
		}
		$get_fileDate_arr = array();
		foreach ($dir_arr as $row) {
			$get_fileDate_arr[$row['name']] = date('Y-m-d H:i:s', $row['date']);
		}
		$new_array = $get_fileDate_arr;
		krsort($new_array);
		$data['latest_files'] = $new_array;
		$this->load->view("db_backup", $data);

	}// End Index
//*******************************************************************************************************************
//*************
//*************
//*************												Index function;
//*************
//*************
//*******************************************************************************************************************	

	public function db_backup()
	{
		show_404();
		if ($this->session->userdata('user_type') != "admin") {
			show_404();
		}
		ini_set('memory_limit', '-1');
		// Set title
		$data['title'] = "Database backup";
		// Set menu status
		$data['menu_administration'] = "active";
		$date = date("Y-m-d-H-i-s");
		$file_name = 'backup-on-' . $date;
		//back up
		$this->load->dbutil();
		$prefs = array(
			'format' => 'zip',
			'filename' => $file_name . '.sql'
		);

		$backup =& $this->dbutil->backup($prefs);
		$db_name = $file_name . '.zip';
		$this->load->helper('download');
		force_download($db_name, $backup);

	}

	// End Index

	public function backups()
	{
		show_404();
		if ($this->session->userdata('user_type') != "admin") {
			show_404();
		}
		$data['title'] = "Database backups";
		$data['menu_administration'] = "active";
		$path = /*FCPATH . */'docs';
		$this->load->helper('file');
		$data['files'] = bucketScanDir($path);
		$data['files'] = $data['files'] ? $data['files'] : [];
		sort($data['files'], SORT_STRING);
		$this->load->view('backups', $data);
	}
	public function download($filename = NULL)
	{
		show_404();
		if ($this->session->userdata('user_type') != "admin") {
			show_404();
		}
		$filename = urldecode($filename);
		ini_set('memory_limit', '-1');
		$path = /*FCPATH . */'docs/';
		if (!$filename)
			show_404();
		if (!is_file($path . $filename))
			show_404();
		$this->load->helper('file');
		$data = read_file($path . $filename);
		$this->load->helper('download');
		force_download($filename, $data);
	}

//*******************************************************************************************************************
//*************
//*************
//*************												downloadFile function;
//*************
//*************
//*******************************************************************************************************************	

	function downloadFile($file)
	{ // $file = include path
		if ($this->session->userdata('user_type') != "admin") {
			show_404();
		}
		if (file_exists(DB_BACKUP_PATH . $file)) {
			$this->load->library('zip');
			$this->zip->read_file(DB_BACKUP_PATH . $file);
			$this->zip->download($file);
		}

	}
//*******************************************************************************************************************
//*************
//*************
//*************										END		downloadFile function; 
//*************
//*************
//*******************************************************************************************************************

    public function ajax_search_tag() {
        $tagName = $this->input->get('q');
        $searchTags = Tag::where('name', 'like', "%{$tagName}%")->get(['tag_id as id', 'name as text'])->toArray();
        die(json_encode(['items'=>$searchTags]));
    }

    /**
     * @return string|null
     */
    private function getModulesStatusesAndReasons() {
        $this->load->model('mdl_est_reason');
        $this->load->model('mdl_est_status');
        $this->load->model('mdl_leads_reason');
        $this->load->model('mdl_leads_status');
        $this->load->model('mdl_invoice_status');
        $data['modules'] = $this->config->item('followup_modules');

        unset($data['modules']['leads']['statuses'], $data['modules']['estimates']['statuses'], $data['modules']['invoices']['statuses']);
        unset($data['modules']['schedule'], $data['modules']['employees'], $data['modules']['users']);
        if(config_item('company_dir') !== 'treedoctors') {
            unset($data['modules']['client_tasks']);
        }

        $data['modules']['leads']['statuses'] = $this->mdl_leads_status->get_all_active_statuses_name();
        $data['modules']['leads']['reasons'] = $this->mdl_leads_reason->get_all_join_lead_statuses('lead_reason_status.reason_name, lead_reason_status.reason_id');
        $data['modules']['estimates']['statuses'] = $this->mdl_est_status->get_all_active_statuses_name();
        $data['modules']['estimates']['reasons'] = $this->mdl_est_reason->get_all_active_join_estimate_statuses('estimate_reason_status.reason_name, estimate_statuses.est_status_name as reason_status');
        $data['modules']['invoices']['statuses'] = $this->mdl_invoice_status->get_all_active_statuses_name();

        return $data['modules'];
    }

    /**
     * render FollowupSettings modal form
     */
    public function ajax_get_followup_modal_form() {
	    $fs_id = $this->input->get('fs_id')??NULL;
	    if (!is_null($fs_id) && $fs_id != 0) {
            $data['setting'] = FollowupSettings::find($fs_id);
            $data['tags'] = $data['setting']->select2FormatData();
        } else {
            $data['setting'] = new FollowupSettings();
            $data['tags'] = [];
        }
        $data['modules'] = $this->getModulesStatusesAndReasons();
        $this->load->view('followup_modal', $data);
    }

    /**
     * Followup settings list
     */
    function followup() {
		$data['title'] = "Follow Up Settings";
        $data['settings'] = FollowupSettings::with('tags')->orderBy('fs_disabled', 'ASC')
            ->orderBy('fs_table_number', 'ASC')
            ->orderBy('fs_table', 'ASC')
            ->orderBy('fs_statuses', 'DESC')
            ->orderBy('fs_periodicity', 'ASC')
            ->orderBy('fs_type', 'ASC')
            ->get();

        $data['modules'] = $this->getModulesStatusesAndReasons();

		$this->load->view('followup', $data);
	}

	function ajax_delete_followup() {
		$fs_id = $this->input->post('fs_id');
        $followSettingsModel = FollowupSettings::find($fs_id);
		$fs_disabled = intval($this->input->post('fs_disabled'));
        $followSettingsModel->setAttribute('fs_disabled', $fs_disabled);
        if ($followSettingsModel->save()) {
            $followSettingsModel->tags()->detach();
        }

		die(json_encode(['status'=>'ok']));
	}

	function ajax_save_followup() {

		$this->load->config('form_validation');
		$this->load->library('form_validation');
		$modules = $this->config->item('followup_modules');
		$rules = config_item('followup_settings');
		
		$fs_id = $this->input->post('fs_id');
		
		if($this->input->post('fs_type') == 'email' || $this->input->post('fs_type') == 'sms'){
			if($this->input->post('fs_table') == 'schedule' || $this->input->post('fs_table') == 'client_tasks')
				$rules[] = ['field'=>'fs_time_periodicity', 'label'=>'Periodicity Time', 'rules'=>'required'];
			else
				$rules[] = ['field'=>'fs_time', 'label'=>'Time', 'rules'=>'required'];
			if($this->input->post('fs_type') == 'email')
				$rules[] = ['field'=>'fs_subject', 'label'=>'Subject', 'rules'=>'required'];
		}
		if($this->input->post('fs_table') != 'users' && $this->input->post('fs_table') != 'employees' && $this->input->post('fs_table') != 'equipment_items')
		{
			$rules[] = ['field'=>'fs_statuses', 'label'=>'Statuses', 'rules'=>'required'];
			$rules[] = ['field'=>'fs_client_types[]', 'label'=>'Client Types', 'rules'=>'required'];
		}
		 
		$fs_table = $this->input->post('fs_table');

		$this->form_validation->set_rules($rules);
		
		$validation = $this->form_validation->run();
		 
		if(!$validation) {
			$result['errors'] = $this->form_validation->error_array();
			$result['status'] = 'error';
		}
		else {
			$data = [
				'fs_table' => $this->input->post('fs_table'),
				'fs_table_number' => $modules[$fs_table]['number'],
				'fs_statuses' => $this->input->post('fs_statuses') ? json_encode(explode('|', $this->input->post('fs_statuses'))) : NULL,
				'fs_type' => $this->input->post('fs_type'),
				'fs_client_types' => ($this->input->post('fs_table') == 'equipment_items' || $this->input->post('fs_table') == 'users') ? NULL : json_encode($this->input->post('fs_client_types')),
				'fs_periodicity' => $this->input->post('fs_periodicity'),
				'fs_every' => intval(boolval($this->input->post('fs_every'))),
				'fs_time' => $this->input->post('fs_time') ? $this->input->post('fs_time') : NULL,
				'fs_template' => $this->input->post('fs_template'),
				'fs_subject' => $this->input->post('fs_subject'),
				'fs_time_periodicity' => $this->input->post('fs_time_periodicity') ? $this->input->post('fs_time_periodicity') : 0,
				'fs_pdf' => $this->input->post('fs_type') == 'email' && $this->input->post('fs_pdf') ? 1 : 0,
				'fs_cron' => $this->input->post('fs_type') == 'email' || $this->input->post('fs_type') == 'sms' 
							|| $this->input->post('fs_type') == 'equipment_alarm' || $this->input->post('fs_type') == 'expired_user_docs'  ? 1 : 0,
			];

            $postFollowupTags = $this->input->post('followup_tags');
			if($fs_id) {
                $followSettingsModel = FollowupSettings::find($fs_id);
                $followSettingsModel->update($data);
            } else {
                $followSettingsModel = FollowupSettings::create($data);
            }

            if (!empty($postFollowupTags)) {
                $followSettingsModel->tags()->detach();
                $followupTags = $this->preparePostFollowupTagsToArray(json_decode($postFollowupTags, true));
                $followSettingsModel->tags()->attach($followupTags);
            }

			$result['status'] = 'ok';
		}

		die(json_encode($result));
	}

    /**
     * @param $post_tags_array
     * @return array
     */
    private function preparePostFollowupTagsToArray($post_tags_array) {
        $result = [];
        if (!empty($post_tags_array)) {
            foreach ($post_tags_array as $value) {
                if (isset($value['id']) && !empty($value['id'])) {
                    array_push($result, $value['id']);
                }
            }
        }
        return $result;
    }
	function start_the_weeks_from($what){
		$this->load->helper('payroll_dates');
		week_sunday_monday($what);
	}

	function tiny_upload() {

        $this->load->library('upload');

        $config['upload_path'] = 'uploads/gallery/' . rand(1, 1000) . '/';
        $config['allowed_types'] = 'gif|jpg|jpeg|png|pdf|GIF|JPG|JPEG|PNG|PDF';
        $config['remove_spaces'] = TRUE;
        $config['encrypt_name'] = TRUE;

        $this->upload->initialize($config);
        if ($this->upload->do_upload('userfile', false)) {
            $uploadData = $this->upload->data();

            $result = [
                'name' => $uploadData['file_name'],
                'file_name' => base_url($config['upload_path'] . $uploadData['file_name']),
                'result' => 'file_uploaded',
                'resultcode' => 'ok',
            ];

        } else {
            $result = [
                'result' => strip_tags($this->upload->display_errors()),
                'resultcode' => 'failed',
                'name' => null,
                'file_name' => NULL,
            ];
        }

        $this->load->view('ajax_tiny_upload_result', $result);
    }

    public function blank($lang='english')
    {
        $this->load->view('tiny_blank');
    }
} 
