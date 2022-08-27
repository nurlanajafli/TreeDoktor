<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

use application\modules\leads\models\Lead;

class Office extends MX_Controller
{
    function __construct()
    {

        parent::__construct();

        if (!isUserLoggedIn() && $this->router->fetch_method() != 'ajax_get_traking_position' && $this->router->fetch_method() != 'workorder_overview') {
            redirect('login');
        }

        $this->_title = SITE_NAME;
        /*
        $this->load->model('mdl_schedule', 'mdl_schedule');
        $this->load->model('mdl_object', 'mdl_object');
        $this->load->model('mdl_crews', 'mdl_crews');
        $this->load->model('mdl_estimates', 'mdl_estimates');
        $this->load->model('mdl_user');
        $this->load->model('mdl_safety_pdf_signs');

        $this->load->model('mdl_est_equipment', 'mdl_est_equipment');
        $this->load->model('mdl_estimates_orm', 'mdl_estimates_orm');
        $this->load->model('mdl_services_orm', 'mdl_services_orm');
        $this->load->model('mdl_crews_orm', 'mdl_crews_orm');
        $this->load->model('mdl_equipment_orm', 'mdl_equipment_orm');


        $this->load->model('mdl_calls');
        $this->load->model('mdl_sms_messages');
        */
        $this->load->model('mdl_vehicles');
        $this->load->model('mdl_client_tasks');
        $this->load->model('mdl_user');
        $this->load->model('mdl_clients');
        $this->load->model('mdl_categories');
    }

    function index()
    {
		$this->load->model('mdl_clients');
		$this->load->model('mdl_user');
		$this->load->model('mdl_categories');
		$data['title'] = 'Office Schedule';

        $users = $this->mdl_user->get_usermeta([
            'active_status' => 'yes',
            'emp_status'    => 'current',
            'users.id <>'   => 0,
            'worker_type'   => 2
        ], [], [], false);

        $data['users'] = $users ? $users->result() : [];

		$data['task_categories'] = $this->mdl_categories->get_all('category_active = 1 AND category_id >= 0');
		//$data['clients'] = $this->mdl_clients->find_all();
		$data['sticker']['tpl'] = $this->load->view('event_office_sticker_tpl', array(), TRUE);
		$data['clients_tpl']['tpl'] = $this->load->view('tpl_clients_field', array(), TRUE);
		$data['address_tpl']['tpl'] = $this->load->view('tpl_address_field', array(), TRUE);
		$data['leads_tpl']['tpl'] = $this->load->view('tpl_leads_field', array(), TRUE);


        $this->load->model('mdl_letter');
        $emailTpls[0] = $this->mdl_letter->find_by_fields(['system_label' => 'estimator_schedule_appointment']);
        $emailTpls[1] = $this->mdl_letter->find_by_fields(['system_label' => 'client_schedule_appointment']);

        foreach($emailTpls as $k=>$v)
        {
            if(empty($emailTpls[$k]) && !$emailTpls){
                unset($emailTpls[$k]);
            }
        }
        $this->load->model('mdl_sms');
        $smsTpls[0] = $this->mdl_sms->get_by(['system_label' => 'estimator_schedule_appointment']);
        $smsTpls[1] = $this->mdl_sms->get_by(['system_label' => 'client_schedule_appointment']);
        foreach($smsTpls as $k=>$v)
        {
            if(empty($smsTpls[$k]) && !$smsTpls){
                unset($smsTpls[$k]);
            }
        }
        $data['smsTpls'] = $smsTpls;
        $data['emailTpls'] = $emailTpls;

        $data['stump_grinder'] = $this->mdl_vehicles->get(4);

        $data['sms_tpl']['tpl'] = $this->load->view('dropdown_sms_office', $data, TRUE);
        $data['emails_tpl']['tpl'] = $this->load->view('dropdown_letters_office', $data, TRUE);


		$data['office_address'] = $this->config->item('office_address');
		$data['office_city'] = $this->config->item('office_city');
		$data['office_state'] = $this->config->item('office_state');
		$data['office_zip'] = $this->config->item('office_zip');
		$data['user_task'] = (int) $this->mdl_user->get_user('', array('id' => $this->session->userdata('user_id')))->row_array()['user_task'];

		$this->load->view('index_office', $data);
    }

    function data()
    {
		$this->load->model('mdl_client_tasks');
		$this->load->model('mdl_user');
		$data = array();

		if ($this->input->post()) {
		    // string 'null' to NULL
			$post = array_map(function($val) {
			        return $val === 'null' ? null : $val;
                },
                $this->input->post()
            );

			$id = $prefix = $post['ids'] ?? null;
			$data['task_id'] = $id;

			if ($post[$id . '_section_id'] ?? null) {
                $data['task_assigned_user'] = $post[$id . '_section_id'];
            }

			if (isset($post[$id . '_category_id']) && $post[$id . '_category_id'] >= 0) {
                $data['task_category'] = $post[$id . '_category_id'];
            }

			if ($post[$id . '_client_id'] ?? null) {
                $post[$id . '_client_id']  = ($post[$id . '_client_id']==-1)?null:$post[$id . '_client_id'];
                $data['task_client_id'] = $post[$id . '_client_id'];
            }

			if (isset($post[$id . '_description']) ?? null) {
                $data['task_desc'] = $post[$id . '_description'];
            }

			if ($post[$id . '_task_address'] ?? null) {
                $data['task_address'] = $post[$id . '_task_address'];
            }

			if ($post[$id . '_task_city'] ?? null) {
                $data['task_city'] = $post[$id . '_task_city'];
            }

			if ($post[$id . '_task_state'] ?? null) {
                $data['task_state'] = $post[$id . '_task_state'];
            }

			if ($post[$id . '_task_zip'] ?? null) {
                $data['task_zip'] = $post[$id . '_task_zip'];
            }

            if (!empty($post[$id . '_lead_id'])) {
                $data['task_lead_id'] = $post[$id . '_lead_id'];
            }

			if (!empty($post[$id . '_task_lat']) && !empty($post[$id . '_task_lon'])) {
				$data['task_latitude'] = $post[$id . '_task_lat'];
				$data['task_longitude'] = $post[$id . '_task_lon'];
			}
			elseif(empty($post[$id . '_task_lat']) && empty($post[$id . '_task_lon'])) {
				$coords = get_lat_lon($data['task_address'] ?? '', $data['task_city'] ?? '', $data['task_state'] ?? '', $data['task_zip'] ?? '');
				$data['task_latitude'] = $coords['lat'];
				$data['task_longitude'] = $coords['lon'];
			}

			if ($post[$id . '_start_date'] ?? null) {
                $data['task_date'] = date('Y-m-d', strtotime($post[$id . '_start_date'] . ':00'));
            }

			if ($post[$id . '_start_date'] ?? null) {
                $data['task_start'] = date('H:i:s', strtotime($post[$id . '_start_date'] . ':00'));
            }

			if ($post[$id . '_end_date'] ?? null) {
                $data['task_end'] = date('H:i:s', strtotime($post[$id . '_end_date'] . ':00'));
            }

			if ($post[$id . '_task_status'] ?? null) {
                $data['task_status'] = $post[$id . '_task_status'];
            }

			if (isset($post[$id . '_!nativeeditor_status']) && $post[$id . '_!nativeeditor_status'] === 'deleted') {
				$this->mdl_client_tasks->delete($id);
				$this->generate_office_followUp($id, FALSE);
			} else {
				$update = $post[$id . '_!nativeeditor_status'] === 'updated';

				if (!$update) {
                    $data['task_date_created'] = date('Y-m-d');
                }
				$result = $this->mdl_client_tasks->save_event($data, $update);

				if (is_numeric($result)) {
                    $id = $result;
                }
				$this->generate_office_followUp($id, TRUE);
			}
			$this->scheduleResponse($post[$prefix . '_!nativeeditor_status'], $prefix, $id);

			return TRUE;
		}
		$data = array();

		if($this->input->get('from') && $this->input->get('to'))
		{
			$wdata['task_date >='] = date('Y-m-d', strtotime($this->input->get('from')));
			$wdata['task_date <'] = date('Y-m-d', strtotime($this->input->get('to')));
		}
		else
			$wdata = ['task_date' => date('Y-m-d')];

		$user = $this->mdl_user->get_user('', array('id' => $this->session->userdata('user_id')))->row_array();
		if($this->input->get('user_id') && $this->input->get('user_id') != 0)
			$wdata['task_assigned_user'] = $this->input->get('user_id');
		elseif($user['user_task'] && !$this->input->get('filter'))
			$wdata['task_assigned_user'] = (int) $user['id'];

		/*$wdata['ass.worker_type'] = 2;*/

		$events = $this->mdl_client_tasks->get_all($wdata, false, false, false);
        $lead = new Lead();

        $key = 0;
        foreach($events as $event)
        {
            if ((bool) $event['emp_feild_worker'] === true &&
                (bool) $event['emp_field_estimator'] === false &&
                $event['task_category'] == -1)
            {
                continue;
            }

            if(isset($event['task_lead_id']) && $event['task_lead_id']){
                $event['task_address'] = $event['lead_address'];
                $event['task_city'] = $event['lead_city'];
                $event['task_state'] = $event['lead_state'];
                $event['task_zip'] =  $event['lead_zip'];
                $event['task_country'] = $event['lead_country'];
                $event['task_latitude'] = $event['latitude'];
                $event['task_longitude'] = $event['longitude'];
            }
            $data[$key] = $event;
            $data[$key]['id'] = $event['task_id'];
            $data[$key]['assign'] = $event['task_assigned_user'];
            $data[$key]['Categories'] = $event['task_category'];
            $data[$key]['client_title'] = '#' . $event['client_id'] . ', ' . $event['client_name'] . ' - ' . $event['task_address'] . ', ' . $event['task_city'] . ', ' . $event['task_state'];

            if(!$event['client_id'])
                $data[$key]['client_title'] = '# Office - ' . $this->config->item('office_address') . ', ' . $this->config->item('office_city') . ', ' . $this->config->item('office_state');
            else{
                $leads = $lead->getLeadsByDefaultStatus([['leads.client_id', '=', $event['client_id']]]);
                $data[$key]['leads'] = $leads;
            }
            $data[$key]['task_lat'] = $event['task_latitude'];
            $data[$key]['task_lon'] = $event['task_longitude'];
            $data[$key]['description'] = $event['task_desc'];
            $data[$key]['start_date'] = $event['task_date'] . ' ' . $event['task_start'];
            $data[$key]['end_date'] = $event['task_date'] . ' ' . $event['task_end'];
            $data[$key]['date'] = $event['task_date'];
            $data[$key]['color'] = $event['ass_color'];
            $data[$key]['cc_phone_view'] = numberTo($event['cc_phone']);

            $data[$key]['text'] = $this->load->view('event_office_sticker_tpl', ['event' => $event], TRUE);

            $key++;
        }

		$this->response(['count' => count($data), 'data' => $data]);
    }


    private function scheduleResponse($action, $id, $new_id = NULL, $result = [])
    {
        $new_id = $new_id ? $new_id : $id;
        $this->session->set_userdata(array('id' => $id, 'action' => $action));
        $data['result'] = $result;
        $data['result']['type'] = $action;
        $data['result']['sid'] = $id;
        $data['result']['tid'] = $new_id;
        $this->load->view_xml('crew_schedule_response', $data);
    }

    function generate_office_followUp($eventId, $action)
    {
        $this->load->model('mdl_followup_settings');
        $this->load->model('mdl_followups');
        $this->load->model('mdl_user');
        $this->load->model('mdl_client_tasks');
        $this->load->model('mdl_employees');
        $fsSettings = $this->mdl_followup_settings->get_many_by(['fs_disabled' => '0', 'fs_table' => 'client_tasks']);
        $this->mdl_followups->delete_by(['fu_item_id' => $eventId]);
        //$data = $this->mdl_client_tasks->get_followup(['task_id' => $eventId]);
        $fsConfig = $this->config->item('followup_modules')['client_tasks'];
        if ($action && $fsSettings) {
            foreach ($fsSettings as $key => $value) {
                $statuses = json_decode($value->fs_statuses);
                $data = $this->mdl_client_tasks->get_followup(['task_id' => $eventId], $statuses);

                if(!empty($data))
                {
                    $variables = $this->mdl_client_tasks->get_followup_variables($eventId);

                    //$existsNewFu = $this->mdl_followups->get_many_by(['fu_module_name' => $module, 'fu_status' => 'new', 'fu_client_id' => $item['client_id']]);
                    //$existsPostponedFu = $this->mdl_followups->get_many_by(['fu_module_name' => $module, 'fu_status' => 'postponed', 'fu_client_id' => $item['client_id']]);

                    $fuData = [];

                    $fuData = [
                        'fu_fs_id' => $value->fs_id,
                        'fu_date' => date('Y-m-d', (strtotime($data[0]['this_status_date']) - $value->fs_time_periodicity*3600)),
                        'fu_module_name' => $value->fs_table,
                        'fu_action_name' => $fsConfig['action_name'],
                        'fu_client_id' => $data[0]['client_id'],
                        'fu_item_id' => $data[0][$fsConfig['id_field_name']],
                        'fu_estimator_id' => $data[0]['estimator_id'],
                        'fu_status' => 'new',
                        'fu_time' => date('H:i:s', (strtotime($data[0]['this_status_date']) - $value->fs_time_periodicity*3600)),
                        'fu_variables' => json_encode($variables)
                    ];

                    //if(!$existsNewFu || !$existsPostponedFu) {
                    $this->mdl_followups->insert($fuData);
                    //}

                    $variables = $this->mdl_employees->get_followup_variables($eventId);

                    //$existsNewFu = $this->mdl_followups->get_many_by(['fu_module_name' => $module, 'fu_status' => 'new', 'fu_client_id' => $item['client_id']]);
                    //$existsPostponedFu = $this->mdl_followups->get_many_by(['fu_module_name' => $module, 'fu_status' => 'postponed', 'fu_client_id' => $item['client_id']]);

                    $fuData = [];

                    $fuData = [
                        'fu_fs_id' => $value->fs_id,
                        'fu_date' => date('Y-m-d', (strtotime($data[0]['this_status_date']) - $value->fs_time_periodicity*3600)),
                        'fu_module_name' => 'employees',
                        'fu_action_name' => $fsConfig['action_name'],
                        'fu_client_id' => $data[0]['client_id'],
                        'fu_item_id' => $data[0][$fsConfig['id_field_name']],
                        'fu_estimator_id' => $data[0]['estimator_id'],
                        'fu_status' => 'new',
                        'fu_time' => date('H:i:s', (strtotime($data[0]['this_status_date']) - $value->fs_time_periodicity*3600)),
                        'fu_variables' => json_encode($variables)
                    ];

                    //if(!$existsNewFu || !$existsPostponedFu) {
                    $this->mdl_followups->insert($fuData);
                    //}
                }
            }
        }
    }
}
