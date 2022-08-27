<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');

use application\modules\schedule\models\ScheduleEventService;
use ElephantIO\Client as WSClient;
use ElephantIO\Engine\SocketIO\Version1X;

use \application\modules\dashboard\models\Search;
use application\modules\references\models\Reference;
use application\modules\equipment\models\EquipmentService;
use application\modules\equipment\models\EquipmentRepair;
use application\modules\events\models\EventsReport;

class Dashboard extends MX_Controller
{

    /*******************************************************************************************************************
     * //*************
     * //*************                                            Dashboard Controller;
     * //*************
     *******************************************************************************************************************/

    function __construct()
    {
        parent::__construct();

        if (!isUserLoggedIn()) {
            redirect('login');
        }

        $this->_title = SITE_NAME;
        $this->load->helper('events');
        //load all common models and libraries here;
        $this->load->model('mdl_dashboard');
        $this->load->model('mdl_dashboard_tasks', 'mdl_tasks');
        $this->load->model('mdl_client_tasks');
        $this->load->model('mdl_employee');
        $this->load->model('mdl_user_certificates', 'user_docs');
        $this->load->model('mdl_user', 'user_model');
        $this->load->model('mdl_clients');
        $this->load->model('mdl_leads');
        $this->load->model('mdl_estimates');
        $this->load->model('mdl_workorders');
        $this->load->model('mdl_invoices');
        $this->load->model('mdl_reports');

        $this->load->model('mdl_schedule');
        $this->load->model('mdl_worked');
        $this->load->model('mdl_emp_login');

        //helper library
        $this->load->library('form_validation');
        $this->load->library('pagination');

        /*-- actions --*/
        $this->load->library('Common/EventActions');
    }

    /*******************************************************************************************************************
     * //*************
     * //*************                                            index function ();
     * //*************
     *******************************************************************************************************************/

    public function index()
    {
        $this->load->model('mdl_expense');
        $this->load->model('mdl_employees');
        $this->load->model('mdl_est_status');
        $this->load->model('mdl_payroll');
        $this->load->model('mdl_schedule');

        $this->load->model('mdl_history_log');

        $loginData = $this->loginData();
        $data = $loginData;
        $data['title'] = $this->_title . " - Dashboard";
        $data['page_title'] = "Dashboard";
        $data['page'] = "dashboard/index";
        $data['menus'] = "";
        $data['flash_message'] = $this->session->flashdata('message');

        //Get all active tasks;
        $wdata = '(tasks.user_id = ' . $this->session->userdata('user_id') . ' OR tasks.task_created_by = ' . $this->session->userdata('user_id') . ' ) AND tasks.task_status = 1';
        $data['todo_list'] = $this->mdl_tasks->getNextTodo($wdata);

        //employees online
        $this->load->model('mdl_employee');
        $data['employees_online'] = $this->mdl_employee->get('MIN(first_login.login_time) as first_login, employee_login.*, employees.*', array('employee_login.login_time >' => date('Y-m-d 00:00:00'), 'employee_login.logout_time' => '0000-00-00 00:00:00'), 'employee_login.employee_id', TRUE);
        if ($data['employees_online'])
            $data['employees_online'] = $data['employees_online']->result_array();

        $this->load->model('mdl_worked');
        $this->load->model('mdl_emp_login');

        $data['employees_autologout'] = $this->mdl_worked->get_workeds(array('worked_auto_logout' => 1));

        if (date('N', time()) == 1) {
            $officeWhere['from'] = date('Y-m-d 00:00:00', strtotime('last Friday'));
            $officeWhere['to'] = date('Y-m-d 23:59:59', strtotime('last Friday'));
        } else {
            $officeWhere['from'] = date('Y-m-d 00:00:00', time() - 60 * 60 * 24);
            $officeWhere['to'] = date('Y-m-d 23:59:59', time() - 60 * 60 * 24);
        }
        $data['logout_from_office'] = $this->user_model->logout_not_from_office(['login_date' => $officeWhere['from'], 'log_date >=' => $officeWhere['from'], 'log_date <=' => $officeWhere['to'], 'login_office' => 1]);

        $wh['login_date'] = date('Y-m-d');
        $wh['logout'] = NULL;
        $data['employees_online'] = $this->mdl_emp_login->with('mdl_worked')->get_peoples($wh);
        //Get all completed tasks;
        $wdata = '(tasks.user_id = ' . $this->session->userdata('user_id') . ' OR tasks.task_created_by = ' . $this->session->userdata('user_id') . ' ) AND tasks.task_status = 0';
        $data['completed_list'] = $this->mdl_tasks->getCompletedTodo($wdata);
        $usermeta = $this->user_model->get_usermeta(array('active_status' => 'yes'));
        $data['userData'] = $this->user_model->get_usermeta(array('users.id' => $this->session->userdata('user_id')))->row();

        if ($data['userData']->worker_type == 1 || $data['userData']->emp_feild_worker == 1) {
            $data = $this->get_feild_worker_data($data);
        }

        $data['users'] = $this->user_model->getActiveUsersWithTaskManager();

        $post['from'] = date('Y-m-d');
        $post['to'] = date('Y-m-d');

        //Fetch numbers of files for specific date:
        $data['today_clients'] = $this->mdl_clients->record_count([], ['client_date_created >=' => $post['from'], 'client_date_created <=' => $post['to']]);
        $data['today_clients_records'] = $this->mdl_clients->get_client_with_last_lead(array('clients.client_date_created >=' => $post['from'], 'clients.client_date_created <=' => $post['to']));

        $data['today_leads'] = $this->mdl_reports->getTodayTotalLeads(false, ['leads.lead_date_created >=' => $post['from'], 'leads.lead_date_created <=' => $post['to'] . ' 23:59:59']); //Get given's date new new leads

        $data['today_count_leads'] = 0;
        foreach ($data['today_leads'] as $lead)
            $data['today_count_leads'] += $lead['count'];
//var_dump($post['from']);
//        var_dump($post['to']);
//die;
        $leadsObj = $this->mdl_leads->get_leads(array('lead_date_created >=' => $post['from'], 'lead_date_created <=' => $post['to'] . ' 23:59:59'), NULL, 'leads.client_id');
        $data['today_leads_records'] = $leadsObj ? $leadsObj->result() : array();

        $data['today_estimates'] = $this->mdl_reports->getTodayTotalEstimates(false, ['estimates.date_created >=' => strtotime($post['from']), 'estimates.date_created <=' => strtotime($post['to'] . ' 23:59:59')]); //Get today's new estimates

        $data['today_count_estimates'] = 0;
        foreach ($data['today_estimates'] as $est)
            $data['today_count_estimates'] += $est['count'];

        $this->load->model('mdl_leads_status');
        $data['lead_statuses'] = $this->mdl_leads_status->with('mdl_leads_reason')->get_many(['lead_status_active' => 1]);
        $declineStatus = $this->mdl_leads_status->with('mdl_leads_reason')->get_by(['lead_status_declined' => 1]);

        foreach ($data['lead_statuses'] as $k => $v) {
            $data['today_leads_cate'][$k]['status'] = $v->lead_status_name;
            $countLeads = $this->mdl_reports->getDateTotalLeadsByCat(false, ['leads.lead_date_created >=' => $post['from'], 'leads.lead_date_created <=' => $post['to'] . ' 23:59:59', 'leads.lead_status_id' => $v->lead_status_id]);
            $data['today_leads_cate'][$k]['count'] = isset($countLeads['count']) ? $countLeads['count'] : 0;
        }

        $data['today_estimates_records'] = $this->mdl_estimates->get_estimates_with_estimator(array('estimates.date_created >=' => strtotime($post['from'] . ' 00:00:00'), 'estimates.date_created <=' => strtotime($post['to'] . ' 23:59:59')));
        $data['today_nogo_leads_records'] = $this->mdl_leads->getLeadsByStatusDate(array('lead_status_id' => $declineStatus->lead_status_id, 'status_value' => $declineStatus->lead_status_id, 'status_date >=' => strtotime($post['from'] . ' 00:00:00'), 'status_date <' => strtotime($post['to'] . ' 23:59:59')));
        $data['today_count_estimates'] += count($data['today_nogo_leads_records']);//countOk

        $data['today_workorders'] = $this->mdl_reports->getTodayTotalWorkorders(false, ['workorders.date_created >=' => $post['from'], 'workorders.date_created <=' => $post['to']]); //Get given's date new new workorders
        $data['today_count_workorders'] = 0;
        foreach ($data['today_workorders'] as $wo)
            $data['today_count_workorders'] += $wo['count'];

        $woQuery = $this->mdl_workorders->get_workorders('', '', '', '', 'workorders.date_created >= "' . $post['from'] . '" AND workorders.date_created <= "' . $post['to'] . '"');
        $data['today_workorders_records'] = $woQuery ? $woQuery->result() : array();

        $estimator_id = "";
        $status = "";
        $status_only = "";
        $from_date = $post['from'];
        $to_date = $post['to'];//date('Y-m-d', strtotime($from_date) + 86400); ???
        $this->load->model('mdl_est_status');
        $data['statuses'] = $this->mdl_est_status->get_many_by(array(/*'est_status_active' => 1*/));

        $all_estimates_statistics = $this->mdl_reports->estimates_statistic('', '', '', $from_date . ' 00:00:00', $to_date);
        $all_estimates_revenue = $this->mdl_reports->revenue_estimates_sum_new([
            'estimates.date_created >=' => strtotime($from_date . ' 00:00:00'), 'estimates.date_created <=' => strtotime($to_date . ' 23:59:59')]);

        foreach ($data['statuses'] as $key => $val) {
            $symbols = array(' - ', ' ', '-');

            $data['estimates']['corp_' . mb_strtolower(str_replace($symbols, '_', $val->est_status_name)) . '_estimates'] = 0;
            foreach ($all_estimates_statistics as $est_total) {
                if ($val->est_status_id == $est_total->status) {
                    $data['estimates']['corp_' . mb_strtolower(str_replace($symbols, '_', $val->est_status_name)) . '_estimates'] = $est_total->estimates_amount;
                }
            }

            $data['estimates']['corp_revenue_' . mb_strtolower(str_replace($symbols, '_', $val->est_status_name)) . '_estimates'] = 0;
            foreach ($all_estimates_revenue as $est_total) {
                if ($val->est_status_id == $est_total->status) {
                    $data['estimates']['corp_revenue_' . mb_strtolower(str_replace($symbols, '_', $val->est_status_name)) . '_estimates'] = $est_total->sum_for_services;
                }
            }

        }

        //Fetch number of total files:

        $data['total_clients'] = $this->mdl_reports->getTotalClients();
        $data['total_leads'] = $this->mdl_reports->getTotalLeads();
        $data['total_estimates'] = $this->mdl_reports->getTotalEstimates();
        $data['total_workorders'] = $this->mdl_reports->getTotalWorkorders();
        $data['today_sum_workorders'] = $this->mdl_reports->getTodayWorkordersSum(false, ['workorders.date_created >=' => $post['from'], 'workorders.date_created <=' => $post['to']]);

        $defaultStatus = $this->mdl_leads_status->get_by(['lead_status_default' => 1]);
		$data['my_leads'] = $this->mdl_leads->getAppLeads(['lead_estimator' => (int)$this->session->userdata('user_id'), 'leads.lead_status_id' => $defaultStatus->lead_status_id]);
        $data['my_tasks'] = $this->mdl_client_tasks->get_all(array('task_assigned_user' => $this->session->userdata('user_id'), 'task_status' => 'new'));

        //invoices pules counts
        $data['totalInvoicesCount'] = $this->mdl_reports->getTotalInvoices();
        $data['invoicesRows'] = $this->mdl_reports->getDailyInvoices(false, ['invoices.date_created >=' => $from_date, 'invoices.date_created <=' => $to_date]);
        $data['totalInvoices'] = $this->mdl_reports->getDailyTotalInvoicesRevenue(false, ['invoices.date_created >=' => $from_date, 'invoices.date_created <=' => $to_date]);
        $data['totalInvoicesByStatus'] = $this->mdl_reports->getDailyInvoicesByStatus(false, ['invoices.date_created >=' => $from_date, 'invoices.date_created <=' => $to_date]);
        $data['totalInvoicesByEstimator'] = $this->mdl_reports->getDailyInvoicesByEstimator(false, ['invoices.date_created >=' => $from_date, 'invoices.date_created <=' => $to_date]);

        $employees = $this->user_model->get_payroll_user(array('user_active_employee' => 1, 'emp_status' => 'current'), 'emp_status ASC, emp_name ASC');
        if ($employees)
            $data['employees'] = $employees->result();

        if ($this->session->userdata('user_type') == "admin" || $this->session->userdata('SCHD_RPR') == 1) {
            $this->load->model('mdl_schedule', 'mdl_schedule');
            $data['events'] = $this->eventactions->teams_new_events_reports();

            //remove this if date > 05.06.2022
            //$events = $this->mdl_schedule->get_events_dashboard(['events_reports.er_report_confirmed' => 0]);
            /*
			foreach($events as $key => $event) {
				$data['items'][$event['id']] = $this->mdl_schedule->get_team_items(array('equipment_team_id' => $event['team_id']));
			}
            */
        }
        $data['countEventsReport'] = EventsReport::has('schedule_event')->noConfirmed()->distinct('er_team_id')->count();

        $data['references'] = Reference::getAllActive()->pluck('name', 'id')->toArray();
        $stats = $this->_get_motivation_stats();
        $data = array_merge($data, $stats);

        $this->load->view('index', $data);
    }

    public function ajax_feild_worker_data()
    {

        $data = [];
        if (!$this->input->post())
            return $this->ajax_response(['type' => 'error', 'data' => 'request is not walid!']);

        $data = $this->get_feild_worker_data([], date("Y-m-d", $this->input->post('date')));

        $response = [];
        $response['dashboard_date'] = $this->load->view('field_worker/dashboard_date', $data, true);
        $response['global_js'] = $this->load->view('field_worker/global_js', $data, true);
        $response['team_equipments_tools'] = $this->load->view('field_worker/team_equipments_tools', $data, true);
        $response['jobs'] = $this->load->view('field_worker/jobs', $data, true);
        $response['map'] = $this->load->view('field_worker/map', $data, true);
        $response['dates_pagination'] = $this->load->view('field_worker/dates_pagination', $data, true);

        return $this->ajax_response(['type' => 'success', 'data' => $response]);
    }

    private function get_feild_worker_data($data, $date = false)
    {

        $day = date('w');

        $data['week_start'] = date('Y-m-d', strtotime('-' . ($day - 1) . ' days'));
        $data['week_end'] = date('Y-m-d', strtotime('+' . (7 - $day) . ' days'));

        $data['dashboard_date'] = $date;
        if (!$date)
            $data['dashboard_date'] = date('Y-m-d');

        $data['page_start'] = date('Y-m-d', strtotime($date . '-3 days'));

        $teams = $this->mdl_schedule->find_team_by([
            'team_date >= ' => strtotime($data['dashboard_date'] . ' 00:00:00'),
            'team_date <= ' => strtotime($data['dashboard_date'] . ' 23:59:59'),
            'schedule_teams_members.user_id' => $this->session->userdata('user_id')
        ]);

        $data['dashboardTeams'] = [];
        foreach ($teams as $key => $value) {
            $data['dashboardTeams'][$data['dashboard_date']] = $value;
        }

        $this->load->model('mdl_estimates_orm', 'mdl_estimates_orm');
        $this->load->model('mdl_crews_orm', 'mdl_crews_orm');
        $this->load->model('mdl_equipment_orm', 'mdl_equipment_orm');
        $jobs = [];

        $data['origin'] = $data['destination'] = config_item('office_location');

        //'schedule.event_report' => NULL,
        $data['emp_events'] = $this->mdl_schedule->get_events_dashboard(array('schedule_teams_members.user_id' => $this->session->userdata('user_id'), 'team_date >=' => strtotime($data['dashboard_date'] . ' 00:00:00'), 'team_date <=' => strtotime($data['dashboard_date'] . ' 23:59:59')), FALSE, 'schedule.event_start ASC');
        //var_dump($data['dashboard_date']);
        //var_dump($data['emp_events']);
        //die;

        $data['events_withouth_report'] = [];
        foreach ($data['emp_events'] as $key => $value) {
            if ($value['er_id'] == NULL)
                $data['events_withouth_report'][] = $value;
        }

        foreach ($data['emp_events'] as $key => $event) {
            if (!isset($event['estimate_id']) || !$event['estimate_id'])
                continue;

            $data['emp_events'][$key]['estimate_data'] = $this->mdl_estimates_orm->with('mdl_services_orm')->get_full_estimate_data(['estimate_id' => $event['estimate_id']], true, false, false)[0];
            $data['emp_events'][$key]['event_services'] = ScheduleEventService::where('event_id', $event['id'])->with('estimate_service_new_status')->get()->pluck('estimate_service_new_status')->toArray();
            if (!empty($data['emp_events'][$key]['event_services']))
                $data['emp_events'][$key]['event_services'] = array_filter($data['emp_events'][$key]['event_services'], function ($element) {
                    return !empty($element);
                });

            $data['waypoints'][] = $event['latitude'] ? $event['latitude'] . ',' . $event['longitude'] : urlencode($event['lead_address']) . ',' . urlencode($event['lead_city']) . ',' . urlencode($event['lead_state']) . ',' . urlencode($event['lead_zip']);
        }

        foreach ($data['dashboardTeams'] as $key => &$value) {
            $items = isset($value->team_id) ? $this->mdl_schedule->getTeamsMembersWithOrder(NULL, $value->team_id) : [];

            $value->tools = $this->mdl_schedule->get_team_tools([]);

            $value->items['users'] = array_filter($items, function ($item) {
                return ($item['type'] == 'user');
            });
            $value->items['equipment'] = array_filter($items, function ($item) {
                return ($item['type'] == 'equipment');
            });

            $value->events = isset($value->team_id) ? $this->mdl_schedule->get_events_dashboard(['schedule.event_team_id' => $value->team_id]) : [];
            $value->team_members = isset($value->team_id) ? $this->mdl_schedule->get_team_members(array('employee_team_id' => $value->team_id, 'schedule_teams_members.user_id <>' => $this->session->userdata('user_id'))) : [];

            $absence = $this->mdl_schedule->get_absence(['absence_ymd' => $key, 'absence_user_id' => $this->session->userdata('user_id')]);
            $value->absence = !empty($absence) ? $absence[0] : [];
        }

        return $data;
    }

    /*******************************************************************************************************************
     * //*************
     * //*************                            Global Search;
     * //*************
     *******************************************************************************************************************/

    function globalSearch()
    {
        $data['title'] = $this->_title . " - Search Result";
        $data['page_title'] = "Search Result";
        //print_r($_POST);
        $search_keyword = '';
        $sort_order = '';
        if ($this->input->post('search_keyword') != '') {
            $search_keyword = trim($this->input->post('search_keyword'));
        } else if ($this->uri->segment(2) != '') {
            $search_keyword = trim($this->uri->segment(2));
        }
        if ($this->input->post('sort_opt') != '') {
            $sort_opt = $this->input->post('sort_opt');
        } else if ($this->uri->segment(3) != '') {
            $sort_opt = $this->uri->segment(3);
        } else {
            $sort_opt = 'client_name';
        }
        if ($this->input->post('sort_order') != '') {
            $sort_order = $this->input->post('sort_order');
        } else if ($this->uri->segment(4) != '') {
            $sort_order = $this->uri->segment(4);
        } else {
            $sort_order = 'ASC';
        }
        //echo $sort_order;
        if ($search_keyword != '') {
            $search_keyword = trim($search_keyword);

            //$search_data = $this->mdl_dashboard->globalSearch($search_keyword);
            //echo $this->db->last_query();
            //$total_rows = count($search_data);

            //$config = array();
            //$config["base_url"] = base_url()."••••••••••••globalSearch/".$search_keyword.'/'.$sort_opt.'/'.$sort_order."/page/";
            //$config["total_rows"] = $total_rows;
            //$config["per_page"] = 20;
            //$config["uri_segment"] = 6;
            //$config['use_page_numbers'] = TRUE;

            //$this->pagination->initialize($config);

            //$page = ($this->uri->segment(6)) ? $this->uri->segment(6) : 1;
            //$start = $page - 1;
            $start = 0; //$start * $config["per_page"];
            //$limit = $config["per_page"];
            $limit = '';
            $phone = preg_match('/(\(?\d{3}\)?[\- ]?)?[\d\- ]{7,10}$/is', $search_keyword);
            if ($phone)
                $search_keyword = numberFrom($search_keyword);
            //var_dump($phone, $search_keyword); die;
            $data['search_data'] = $this->mdl_dashboard->globalSearch($search_keyword, $limit, $start, $sort_opt, $sort_order);
            //echo $this->db->last_query();
            $data['search_count'] = count($data['search_data']); //$total_rows;//countOk
            $data['pagination_link'] = $this->pagination->create_links();
            if ($phone)
                $search_keyword = numberTo($search_keyword);
            $data['search_keyword'] = $search_keyword;
            $data['sort_order'] = $sort_order;
            $data['sort_opt'] = $sort_opt;
            $this->load->view('global_search', $data);
        } else {
            redirect('dashboard');
        }

    }//end of global search function

    /*******************************************************************************************************************
     * //*************
     * //*************                    AJAX Function for - dashboard productivity pulse count
     * //*************
     *******************************************************************************************************************/


    function getProductivityPulse()
    {
        //$this->output->enable_profiler(TRUE);
        //extract($this->input->post());
        //$date = DateTime::createFromFormat(getDateFormat(), $date_now);
        //$date_now = $date->format('Y-m-d');
        $post = $this->input->post();

        //Given date;
        //Fetch numbers of files for specific date:
        $this->load->model('mdl_payroll');
        //$data['today_clients'] = $this->mdl_reports->getTodayTotalClients($date_now); //Get given's date new clients
        $data['today_clients'] = $this->mdl_clients->record_count([], ['client_date_created >=' => $post['from'], 'client_date_created <=' => $post['to']]);

        $data['today_clients_records'] = $this->mdl_clients->get_client_with_last_lead(array('clients.client_date_created >=' => $post['from'], 'clients.client_date_created <=' => $post['to']));//, 'lead_date_created >=' => $date_now . ' 00:00:00', 'lead_date_created <=' => $date_now . ' 23:59:59'));


        $data['today_leads'] = $this->mdl_reports->getTodayTotalLeads(false, ['leads.lead_date_created >=' => $post['from'], 'leads.lead_date_created <=' => $post['to'] . ' 23:59:59']); //Get given's date new new leads

        $data['today_count_leads'] = 0;
        foreach ($data['today_leads'] as $lead)
            $data['today_count_leads'] += $lead['count'];

        $leadsObj = $this->mdl_leads->get_leads(array('lead_date_created >=' => $post['from'] . ' 00:00:00', 'lead_date_created <=' => $post['to'] . ' 23:59:59'), NULL, 'leads.client_id');

        $data['today_leads_records'] = $leadsObj ? $leadsObj->result() : array();

        $data['today_estimates'] = $this->mdl_reports->getTodayTotalEstimates(false, ['estimates.date_created >=' => strtotime($post['from'] . ' 00:00:00'), 'estimates.date_created <= ' => strtotime($post['to'] . ' 23:59:59')]); //Get today's new estimates

        $data['today_count_estimates'] = 0;
        foreach ($data['today_estimates'] as $est)
            $data['today_count_estimates'] += $est['count'];
        $data['today_estimates_records'] = $this->mdl_estimates->get_estimates_with_estimator(array('estimates.date_created >=' => strtotime($post['from'] . ' 00:00:00'), 'estimates.date_created <=' => strtotime($post['to'] . ' 23:59:59')));

        $this->load->model('mdl_leads_status');
        $declineStatus = $this->mdl_leads_status->with('mdl_leads_reason')->get_by(['lead_status_declined' => 1]);
        $data['lead_statuses'] = $this->mdl_leads_status->with('mdl_leads_reason')->get_many(['lead_status_active' => 1]);
        foreach ($data['lead_statuses'] as $k => $v) {
            $data['today_leads_cate'][$k]['status'] = $v->lead_status_name;
            $countLeads = $this->mdl_reports->getDateTotalLeadsByCat(false, ['leads.lead_date_created >=' => $post['from'] . ' 00:00:00', 'leads.lead_date_created <=' => $post['to'] . ' 23:59:59', 'leads.lead_status_id' => $v->lead_status_id]);
            $data['today_leads_cate'][$k]['count'] = isset($countLeads['count']) ? $countLeads['count'] : 0;
        }


        $data['today_nogo_leads_records'] = $this->mdl_leads->getLeadsByStatusDate(array('lead_status_id' => $declineStatus->lead_status_id, 'status_value' => $declineStatus->lead_status_id, 'status_date >=' => strtotime($post['from'] . ' 00:00:00'), 'status_date <' => strtotime($post['to'] . ' 23:59:59')));
        $data['today_count_estimates'] += count($data['today_nogo_leads_records']);//countOk
        $data['today_workorders'] = $this->mdl_reports->getTodayTotalWorkorders(false, ['workorders.date_created >=' => $post['from'], 'workorders.date_created <=' => $post['to']]); //Get given's date new new workorders
        $data['today_count_workorders'] = 0;
        foreach ($data['today_workorders'] as $wo)
            $data['today_count_workorders'] += $wo['count'];

        $woQuery = $this->mdl_workorders->get_workorders('', '', '', '', 'workorders.date_created >= "' . $post['from'] . '" AND workorders.date_created <= "' . $post['to'] . '"');
        $data['today_workorders_records'] = $woQuery ? $woQuery->result() : array();
        $data['today_sum_workorders'] = $this->mdl_reports->getTodayWorkordersSum(false, ['workorders.date_created >=' => $post['from'], 'workorders.date_created <=' => $post['to']]);

        //fetch numbers of files for every category of leads
        $estimator_id = "";
        $status = "";
        $status_only = "";
        $from_date = $post['from'];
        $to_date = $post['to'];//date('Y-m-d', strtotime($date_now) + 86400); ???

        //fetch numbers of files for every category of estimates

        $this->load->model('mdl_est_status');
        $data['statuses'] = $this->mdl_est_status->get_many_by(array(/*'est_status_confirmed' => 0*/));

        $all_estimates_statistics = $this->mdl_reports->estimates_statistic('', '', '', $from_date, $to_date);
        $all_estimates_revenue = $this->mdl_reports->revenue_estimates_sum_new([
            'estimates.date_created >=' => strtotime($from_date . ' 00:00:00'), 'estimates.date_created <=' => strtotime($to_date . ' 23:59:59')]);

        foreach ($data['statuses'] as $key => $val) {
            $symbols = array(' - ', ' ', '-');

            $data['estimates']['corp_' . mb_strtolower(str_replace($symbols, '_', $val->est_status_name)) . '_estimates'] = 0;
            foreach ($all_estimates_statistics as $est_total) {
                if ($val->est_status_id == $est_total->status) {
                    $data['estimates']['corp_' . mb_strtolower(str_replace($symbols, '_', $val->est_status_name)) . '_estimates'] = $est_total->estimates_amount;
                }
            }

            $data['estimates']['corp_revenue_' . mb_strtolower(str_replace($symbols, '_', $val->est_status_name)) . '_estimates'] = 0;
            foreach ($all_estimates_revenue as $est_total) {
                if ($val->est_status_id == $est_total->status) {
                    $data['estimates']['corp_revenue_' . mb_strtolower(str_replace($symbols, '_', $val->est_status_name)) . '_estimates'] = $est_total->sum_for_services;
                }
            }

        }

        //Fetch number of total files:
        $data['total_clients'] = $this->mdl_reports->getTotalClients();
        $data['total_leads'] = $this->mdl_reports->getTotalLeads();
        $data['total_estimates'] = $this->mdl_reports->getTotalEstimates();
        $data['total_workorders'] = $this->mdl_reports->getTotalWorkorders();

        //invoices pules counts
        $data['totalInvoicesCount'] = $this->mdl_reports->getTotalInvoices();
        $data['invoicesRows'] = $this->mdl_reports->getDailyInvoices(false, ['invoices.date_created >=' => $from_date, 'invoices.date_created <=' => $to_date]);
        $data['totalInvoices'] = $this->mdl_reports->getDailyTotalInvoicesRevenue(false, ['invoices.date_created >=' => $from_date, 'invoices.date_created <=' => $to_date]);
        $data['totalInvoicesByStatus'] = $this->mdl_reports->getDailyInvoicesByStatus(false, ['invoices.date_created >=' => $from_date, 'invoices.date_created <=' => $to_date]);
        $data['totalInvoicesByEstimator'] = $this->mdl_reports->getDailyInvoicesByEstimator(false, ['invoices.date_created >=' => $from_date, 'invoices.date_created <=' => $to_date]);

        if ($this->session->userdata('user_type') == "admin" || $this->session->userdata('SCHD_RPR') == 1) {
            $this->load->model('mdl_schedule', 'mdl_schedule');
            $data['events'] = $this->eventactions->teams_new_events_reports();
            //remove this if date > 05.06.2022
            /*
            $events = $this->mdl_schedule->get_events_dashboard(['events_reports.er_report_confirmed' => 0], FALSE, 'schedule.event_start ASC');
            foreach($events as $key => $event) {
				$data['items'][$event['id']] = $this->mdl_schedule->get_team_items(array('equipment_team_id' => $event['team_id']));
			}
            */
        }
        $data['countEventsReport'] = EventsReport::has('schedule_event')->noConfirmed()->distinct('er_team_id')->count();
        $data['references'] = Reference::getAllActive()->pluck('name', 'id')->toArray();
        $result = [
            'status' => 'success',
            'html' => $this->load->view('productivity_pulse', $data, TRUE)
        ];

        return $this->response($result);
        //$this->load->view('productivity_pulse', $data);
    }

    function ajax_user_like()
    {
        $like = $this->input->post('like');
        $user_id = $this->input->post('user_id');
        if ($this->session->userdata('user_id') == $user_id)
            die(json_encode(array('status' => 'error', 'msg' => 'Sorry! You can not vote for yourself.')));
        $vote = $this->user_model->vote($this->session->userdata('user_id'), $user_id, $like);
        if ($vote)
            die(json_encode(array('status' => 'ok')));
        die(json_encode(array('status' => 'error', 'msg' => 'Sorry! You have already voted for this user in the last 1 hr.')));
    }

    function ajax_employee_logout()
    {
        if ($this->session->userdata('user_type') != "admin" && $this->session->userdata('RPS_PR') != 1)
            show_404();
        $this->load->model('mdl_emp_login');
        $employee_id = $this->input->post('employee_id');
        $login_id = $this->input->post('login_id');
        $time = $this->input->post('time');
        if (!$time)
            $time = date('H:i');
        if (!$employee_id)
            die(json_encode(array('status' => 'error')));
        $this->mdl_emp_login->update($login_id, array('logout' => date($time . ':00')));
        die(json_encode(array('status' => 'ok')));
    }

    function ajax_add_expense()
    {
        if ($this->session->userdata('user_type') != "admin" && $this->session->userdata('EXP') != 1)
            die(json_encode(array('status' => 'error')));

        $this->load->model('mdl_expense');
        $errors = array();

        $itemsGroups = $this->mdl_expense->get_selected_groups(array('expense_type_id' => $this->input->post('expense_type')));
        if ($itemsGroups && !empty($itemsGroups))
            $this->form_validation->set_rules('expense_item', 'Expense Item', 'trim|numeric|required');
        else
            $this->form_validation->set_rules('expense_item', 'Expense Item', 'trim|numeric');

        $this->form_validation->set_error_delimiters('', '');
        $this->form_validation->set_rules('expense_date', 'Expense Date', 'trim|required');
        $this->form_validation->set_rules('expense_type', 'Expense Type', 'required|numeric');
        $this->form_validation->set_rules('expense_employee', 'Employee', 'numeric');
        $this->form_validation->set_rules('expense_amount', 'Amount', 'required|numeric|trim');
        $this->form_validation->set_rules('expense_description', 'Expense Description', 'trim');
        $this->form_validation->set_rules('expense_payment', 'Expense Payment Type', 'required|trim|callback__expense_payment');
        $this->form_validation->set_message('_expense_payment', 'Incorrect Payment Type');
        if ($this->form_validation->run($this) === FALSE) {
            $errors = $this->form_validation->error_array();
            $errors['status'] = 'error';
        } else {
            $insert['expense_type_id'] = $this->input->post('expense_type');
            $insert['expense_item_id'] = $this->input->post('expense_item');
            $date = DateTime::createFromFormat(getDateFormat(), $this->input->post('expense_date'));
            $insert['expense_date'] = $date->getTimestamp();
            $insert['expense_amount'] = $this->input->post('expense_amount');
            $tax = checkTaxInAllTaxes(trim($this->input->post('tax_text')));

            $insert['expense_hst_amount'] = '0.00';
            if (!$this->input->post('expense_hst_amount')) {
                if ($tax) {
                    $amount = $insert['expense_amount'] / $tax['rate'];
                    $taxAmount = $amount * ($tax['value'] / 100);
                } else {
                    $amount = $insert['expense_amount'] / ($this->input->post('tax_value') / 100 + 1);
                    $taxAmount = $amount * ($this->input->post('tax_value') / 100);
                }
                $insert['expense_amount'] = $amount;
                $insert['expense_hst_amount'] = $taxAmount;
            }
            if ($tax)
                $insert['expense_tax'] = json_encode(['name' => $tax['name'], 'value' => $tax['value']]);
            else
                $insert['expense_tax'] = json_encode(['name' => $this->input->post('tax_select'), 'value' => $this->input->post('tax_value')]);

            $insert['expense_description'] = $this->input->post('expense_description');
            if (!$this->input->post('expense_id')) {
                $insert['expense_created_by'] = $this->session->userdata['user_id'];
                $insert['expense_create_date'] = time();
            }
            if ($this->input->post('expense_employee')) {
                $insert['expense_user_id'] = $this->input->post('expense_employee');
            }
            if ($this->input->post('expense_event_id')) {
                $insert['expense_event_id'] = $this->input->post('expense_event_id');
            }
            $insert['expense_payment'] = $this->input->post('expense_payment');
        }
        $id = $this->input->post('expense_id');
        if ($id && empty($errors))
            $this->mdl_expense->update_expense($id, $insert);
        elseif (isset($insert['expense_amount']) && $insert['expense_amount'] && empty($errors))
            $id = $this->mdl_expense->insert_expense($insert);
        if (!$_FILES['file']['error']) {
            $dir = 'expenses_files/';
            $name = $id;
            $this->mdl_expense->uploadFile($dir, $name, $field = 'file', $types = '*');
            $this->mdl_expense->update_expense($id, array('expense_file' => $id . '.' . pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION)));
        }
        if (!empty($errors))
            die(json_encode($errors));

        if ($this->input->post('callback')) {
            $callback = $this->input->post('callback');
            return $this->$callback();
        }

        $expenses = $this->mdl_reports->get_expenses(['expense_id' => $id]);
        $result['html'] = $this->load->view('reports/expenses_table_tbody', ['expenses' => $expenses], TRUE);
        $result['id'] = $id;
        $result['status'] = 'ok';
        die(json_encode($result));
    }

    function get_event_expense_modal()
    {
        $data = [];
        $this->load->model('mdl_expense');
        $this->load->model('mdl_reports');
        $this->load->model('mdl_schedule');

        if ($this->input->post('event_id'))
            $event_id = $this->input->post('event_id');

        if ($this->input->post('expense_event_id'))
            $event_id = $this->input->post('expense_event_id');

        if (!$event_id)
            die(json_encode(['status' => 'error']));

        $data['event_id'] = $event_id;
        $event = $this->mdl_schedule->get_events(['schedule.id' => $event_id], TRUE);

        $data['event_expenses'] = $this->mdl_reports->get_expenses(['expenses.expense_event_id' => $event_id]);
        $data['employees_data'] = $this->mdl_schedule->get_team_members(['schedule_teams_members.employee_team_id' => $event['team_id']]);

        $data['event_date'] = date(getDateFormat(), $event['event_start']);
        $data['expense_types'] = $this->mdl_expense->find_all(['expense_status' => 1], 'expense_status DESC, expense_name ASC');

        $response = ['status' => 'ok'];
        $response['expense_amount_sum'] = $this->mdl_reports->get_expenses_sum(['expenses.expense_event_id' => $event_id]);
        $response['event_id'] = $event_id;


        $response['html'] = $this->load->view('expenses/add_expense_modal_body', $data, true);
        die(json_encode($response));
    }

    function _expense_payment($str)
    {
        $payment_types = array('Cash', 'CC', 'Bank');
        return array_search($str, $payment_types) !== FALSE;
    }

    function ajax_delete_expense()
    {
        $this->load->model('mdl_expense');
        $id = $this->input->post('expense_id');

        $response = ['status' => 'ok'];
        $response['expense_id'] = $id;
        $response['expense'] = $this->mdl_reports->get_expense(['expense_id' => (int)$id]);

        $this->mdl_expense->delete_expense($id);
        $response['expense_amount_sum'] = $this->mdl_reports->get_expenses_sum(['expenses.expense_event_id' => $response['expense']['expense_event_id']]);
        die(json_encode($response));
    }

    function ajax_get_expense_type_items()
    {
        $expense_id = intval($this->input->post('expense_id'));
        if (!$expense_id)
            die(json_encode(array('status' => 'error')));
        $this->load->model('mdl_expense');
        $this->load->model('mdl_equipments');
        $itemsGroups = $this->mdl_expense->get_selected_groups(array('expense_type_id' => $expense_id));
        $data['items'] = array();
        if (isset($itemsGroups[$expense_id])) {
            foreach ($itemsGroups[$expense_id] as $group) {
                $items = $this->mdl_equipments->get_group_items($group['expense_type_group_id']);
                if ($items && !empty($items)) {
                    foreach ($items as $item)
                        $data['items'][] = $item;
                }
            }
        }
        $result['count_items'] = count($data['items']);//countOk
        $result['status'] = 'ok';
        $result['html'] = $this->load->view('expense_items_select', $data, TRUE);
        die(json_encode($result));
    }

    function ajax_counters()
    {
        $result['status'] = 'ok';
        $this->load->model('mdl_leads');
        $this->load->model('mdl_leads_status');

        $status_for_approval = $this->mdl_leads_status->get_by(['lead_status_for_approval' => 1]);
        $result['counters']['not_approved_leads'] = $this->mdl_leads->getCountLeads($status_for_approval->lead_status_id);
        $result['counters']['root_not_approved_leads'] = $result['counters']['not_approved_leads'];
        $result['counters']['equipment_due_services'] = EquipmentService::whereHas('equipment')
            ->where('service_next_date', '<', \Carbon\Carbon::now())
            ->count('service_id');
        $result['counters']['equipment_issued_repair_requests'] = EquipmentRepair::whereHas('equipment')
            ->whereRepairStatusId(1)
            ->count('repair_id');

        $result['counters']['equipment_root_counter'] = $result['counters']['equipment_due_services'] + $result['counters']['equipment_issued_repair_requests'];

        return $this->response($result);
    }

    private function _coords($logins, $date, $user_id)
    {
        $result = array();

        $range = 0.4; //Radius in kilometers; ~60-100 meters diameter. ///0.019 CHANGED BY GLEBA RUSLAN
        $lat_range = $range / 69.172;
        $start = date('Y-m-d', strtotime($date));
        $office_lat = config_item('office_lat');
        $office_lon = config_item('office_lon');

        foreach ($logins as $jkey => $login) {
            $task = NULL;
            $estimate = NULL;

            $result[$jkey] = $login;
            if (!$login['login_lat'])
                $login['login_lat'] = 0;
            if (!$login['login_lon'])
                $login['login_lon'] = 0;
            if (!$login['logout_lat'])
                $login['logout_lat'] = 0;
            if (!$login['logout_lon'])
                $login['logout_lon'] = 0;

            $lon_range = abs($range / (cos($login['login_lat']) * 69.172));

            $coord['min_lat'] = number_format($login['login_lat'] - $lat_range, "6", ".", "");
            $coord['max_lat'] = number_format($login['login_lat'] + $lat_range, "6", ".", "");
            $coord['min_lon'] = number_format($login['login_lon'] - $lon_range, "6", ".", "");
            $coord['max_lon'] = number_format($login['login_lon'] + $lon_range, "6", ".", "");

            $result[$jkey]['location_login'] = array();
            if (($coord['min_lat'] <= $office_lat && $office_lat <= $coord['max_lat']) && ($coord['min_lon'] <= $office_lon && $office_lon <= $coord['max_lon'])) {
                $result[$jkey]['location_login'][0]['office'] = TRUE;
            } else {
                $where = array('user_id' => $user_id, 'date_created >=' => strtotime($start . '00:00:00'), 'date_created <=' => strtotime($start . '23:59:59'), 'latitude >' => $coord['min_lat'], 'latitude <' => $coord['max_lat'], 'longitude >' => $coord['min_lon'], 'longitude <' => $coord['max_lon']);
                $estimate = $this->mdl_estimates->get_full_estimate_data($where);
                $task = $this->mdl_client_tasks->get_all(array('task_user_id_updated' => $user_id, 'task_date_updated >=' => $start, 'task_date_updated <=' => $start, 'task_latitude >' => $coord['min_lat'], 'task_latitude <' => $coord['max_lat'], 'task_longitude >' => $coord['min_lon'], 'task_longitude <' => $coord['max_lon']), FALSE, FALSE);

                if ($estimate)
                    $result[$jkey]['location_login'] = $estimate;
                elseif ($task)
                    $result[$jkey]['location_login'] = $task;

            }

            $coord['min_lat'] = number_format($login['logout_lat'] - $lat_range, "6", ".", "");
            $coord['max_lat'] = number_format($login['logout_lat'] + $lat_range, "6", ".", "");
            $coord['min_lon'] = number_format($login['logout_lon'] - $lon_range, "6", ".", "");
            $coord['max_lon'] = number_format($login['logout_lon'] + $lon_range, "6", ".", "");

            $result[$jkey]['location_logout'] = array();
            if (($coord['min_lat'] <= $office_lat && $office_lat >= $coord['max_lat']) && ($coord['min_lon'] <= $office_lon && $office_lon >= $coord['max_lon'])) {
                $result[$jkey]['location_login'][0]['office'] = TRUE;
            } else {
                $where = array('user_id' => $user_id, 'date_created >=' => strtotime($start . '00:00:00'), 'date_created <=' => strtotime($start . '23:59:59'), 'latitude >' => $coord['min_lat'], 'latitude <' => $coord['max_lat'], 'longitude >' => $coord['min_lon'], 'longitude <' => $coord['max_lon']);
                $estimate = $this->mdl_estimates->get_full_estimate_data($where);
                $task = $this->mdl_client_tasks->get_all(array('task_user_id_updated' => $user_id, 'task_date_updated >=' => $start, 'task_date_updated <=' => $start, 'task_latitude >' => $coord['min_lat'], 'task_latitude <' => $coord['max_lat'], 'task_longitude >' => $coord['min_lon'], 'task_longitude <' => $coord['max_lon']), FALSE, FALSE);

                if ($estimate)
                    $result[$jkey]['location_logout'] = $estimate;
                elseif ($task)
                    $result[$jkey]['location_logout'] = $task;

            }
        }
        return $result;
    }


    private function _new_coords($logins, $date, $user_id)
    {
        $result = array();

        $range = 0.4; //Radius in kilometers; ~60-100 meters diameter. ///0.019 CHANGED BY GLEBA RUSLAN
        $lat_range = $range / 69.172;
        $start = date('Y-m-d', strtotime($date));
        $office_lat = config_item('office_lat');
        $office_lon = config_item('office_lon');
        //$emp = $this->mdl_employees->get_employee('emp_username', 'employee_id = ' . $user_id, '')->row_array();
        //$user_id = $this->user_model->get_user('id', 'emailid = "' . $emp['emp_username'] . '"')->row_array()['id'];
        //$user = $this->user_model->get_payroll_user(array('id' => $user_id));


        foreach ($logins as $jkey => $login) {
            $task = NULL;
            $estimate = NULL;


            //$estimates = $this->mdl_estimates->record_count(array(), array('user_id' => $user['id'], 'date_created >=' => strtotime(date('Y-m-d 00:00:00'))));

            $result[$jkey] = $login;
            if (!$login->login_lat)
                $login->login_lat = 0;
            if (!$login->login_lon)
                $login->login_lon = 0;
            if (!$login->logout_lat)
                $login->logout_lat = 0;
            if (!$login->logout_lon)
                $login->logout_lon = 0;

            $lon_range = abs($range / (cos($login->login_lat) * 69.172));

            $coord['min_lat'] = number_format($login->login_lat - $lat_range, "6", ".", "");
            $coord['max_lat'] = number_format($login->login_lat + $lat_range, "6", ".", "");
            $coord['min_lon'] = number_format($login->login_lon - $lon_range, "6", ".", "");
            $coord['max_lon'] = number_format($login->login_lon + $lon_range, "6", ".", "");
            //var_dump($result); die;
            $result[$jkey]->location_login = array();
            if (($coord['min_lat'] <= $office_lat && $office_lat <= $coord['max_lat']) && ($coord['min_lon'] <= $office_lon && $office_lon <= $coord['max_lon'])) {
                $result[$jkey]->location_login['office'] = TRUE;
            } else {
                $where = array('user_id' => $user_id, 'date_created >=' => strtotime($start . '00:00:00'), 'date_created <=' => strtotime($start . '23:59:59'), 'latitude >' => $coord['min_lat'], 'latitude <' => $coord['max_lat'], 'longitude >' => $coord['min_lon'], 'longitude <' => $coord['max_lon']);
                $estimate = $this->mdl_estimates->get_full_estimate_data($where);
                $task = $this->mdl_client_tasks->get_all(array('task_user_id_updated' => $user_id, 'task_date_updated >=' => $start, 'task_date_updated <=' => $start, 'task_latitude >' => $coord['min_lat'], 'task_latitude <' => $coord['max_lat'], 'task_longitude >' => $coord['min_lon'], 'task_longitude <' => $coord['max_lon']), FALSE, FALSE);
                $statuses = array('No Go', 'Estimated');
                $lead = $this->mdl_leads->get_status_log(array('status_user_id' => $user_id, 'status_date <=' => strtotime($start . $login->logout), 'status_date >=' => strtotime($start . $login->login), 'status_type' => 'lead'), $statuses);
                if ($lead)
                    $result[$jkey]->location_login = $this->mdl_leads->find_by_id($lead[0]['status_item_id']);
                elseif ($estimate)
                    $result[$jkey]->location_login = $estimate[0];
                elseif ($task)
                    $result[$jkey]->location_login = $task[0];

            }

            $coord['min_lat'] = number_format($login->logout_lat - $lat_range, "6", ".", "");
            $coord['max_lat'] = number_format($login->logout_lat + $lat_range, "6", ".", "");
            $coord['min_lon'] = number_format($login->logout_lon - $lon_range, "6", ".", "");
            $coord['max_lon'] = number_format($login->logout_lon + $lon_range, "6", ".", "");

            $result[$jkey]->location_logout = array();
            if (($coord['min_lat'] <= $office_lat && $office_lat <= $coord['max_lat']) && ($coord['min_lon'] <= $office_lon && $office_lon <= $coord['max_lon'])) {
                $result[$jkey]->location_logout['office'] = TRUE;
            } else {
                $where = array('user_id' => $user_id, 'date_created >=' => strtotime($start . '00:00:00'), 'date_created <=' => strtotime($start . '23:59:59'), 'latitude >' => $coord['min_lat'], 'latitude <' => $coord['max_lat'], 'longitude >' => $coord['min_lon'], 'longitude <' => $coord['max_lon']);
                $estimate = $this->mdl_estimates->get_full_estimate_data($where);

                $task = $this->mdl_client_tasks->get_all(array('task_user_id_updated' => $user_id, 'task_date_updated >=' => $start, 'task_date_updated <=' => $start, 'task_latitude >' => $coord['min_lat'], 'task_latitude <' => $coord['max_lat'], 'task_longitude >' => $coord['min_lon'], 'task_longitude <' => $coord['max_lon']), FALSE, FALSE);
                $statuses = array('No Go', 'Estimated');
                $lead = $this->mdl_leads->get_status_log(array('status_user_id' => $user_id, 'status_date <=' => strtotime($start . $login->logout), 'status_date >=' => strtotime($start . $login->login), 'status_type' => 'lead'), $statuses);

                if ($lead)
                    $result[$jkey]->location_login = $this->mdl_leads->find_by_id($lead[0]['status_item_id']);
                elseif ($estimate)
                    $result[$jkey]->location_logout = $estimate[0];
                elseif ($task)
                    $result[$jkey]->location_logout = $task[0];
            }
        }

        return $result;
    }

    function loginData()
    {
        $data["login"] = false;
        $data["logout"] = false;
        $data["login_time"] = "00:00";
        $data["logout_time"] = "00:00";
        $data["login_rec_id"] = 0;
        $data["new_record"] = 0;
        $data["login_data"] = array();
        $data["show_web_cam"] = 1;

        $emp_id = $this->session->userdata('user_id');
        $this->load->model('mdl_schedule');
        $this->load->model('mdl_worked');
        $this->load->model('mdl_equipments');
        $this->load->model('mdl_employees', 'employee_model');

        //date_default_timezone_set("GMT");
        $data['teams'] = $this->mdl_schedule->get_teams(array('team_leader_user_id' => $emp_id, 'team_date' => strtotime(date('Y-m-d'))));
        $userdata = $this->user_model->get_payroll_user(array('users.id' => $emp_id));

        if ($userdata) {
            $obj['emp_field_estimator'] = $userdata->result()[0]->emp_field_estimator;
            $obj['user_active_employee'] = $userdata->result()[0]->user_active_employee;
        } else
            $obj = $this->employee_model->get_employee('', array('employee_id' => $emp_id));

        if ($userdata)
            $data['estimator'] = $obj ? $obj : NULL;
        else
            $data['estimator']['emp_field_estimator'] = $obj ? $obj->row_array()['emp_field_estimator'] : NULL;

        $this->load->model('mdl_report');
        $est_report = $this->mdl_report->find_by_fields(array('report_user_id = ' => $emp_id, 'report_date >=' => date('Y-m-d 00:00:00'), 'report_date <=' => date('Y-m-d 23:59:59')));

        $data['est_report'] = '';
        if (!empty($est_report))
            $data['est_report'] = $est_report->report_comment;
        if ($data['teams'] && !empty($data['teams'])) {
            foreach ($data['teams'] as $team) {
                $data['team_members'] = $this->mdl_schedule->get_team_members(array('employee_team_id' => $team->team_id /*, 'employee_logout' => NULL*/));
                $data['team_id'] = $team->team_id;
            }
            $data['emp_events'] = $this->mdl_schedule->get_events_dashboard(array('schedule.event_report' => NULL, 'team_leader_user_id' => $emp_id, 'team_date ' => strtotime(date('Y-m-d'))), FALSE, 'schedule.event_start ASC');

            if ((!isset($data['team_members']) || empty($data['team_members'])) && empty($data['emp_events']))
                unset($data['teams']);
        }
        $data["emp_id"] = $this->session->userdata("emp_user_id");
        $data["user_name"] = $this->session->userdata("emp_name");
        if (!$data['emp_id']) {
            $data['user_name'] = $this->session->userdata('firstname') . ' ' . $this->session->userdata('lastname');
            $data['emp_id'] = $this->session->userdata('user_id');
        }

        $employee_login_new_table = $this->mdl_emp_login->get_by(array('login_user_id' => $emp_id, 'login_date' => date('Y-m-d'), 'logout' => NULL));
        if (empty((array)$employee_login_new_table))
            $employee_login_new_table = $this->mdl_emp_login->get_by(array('login_employee_id' => $emp_id, 'login_date' => date('Y-m-d'), 'logout' => NULL));

        $groups = $this->mdl_equipments->get_groups();
        $data['groups'] = array();
        if ($groups) {
            $data['groups'] = $groups->result();
            $data['items'] = array();
            $allItems = $this->mdl_equipments->get_group_items(NULL);
            if ($allItems) {
                foreach ($allItems as $key => $val)
                    $data['items'][$val['group_id']][] = $val;
            }
        } else {
            $data['items'] = array();
            $data['groups'] = (object)$data['groups'];
        }
        if ($employee_login_new_table && !empty((array)$employee_login_new_table)) {
            $data['new_record'] = $employee_login_new_table->login_id;
            $data["login"] = true;
            $data["login_time"] = date("H:i", strtotime($employee_login_new_table->login));
            $data["login_rec_id"] = $employee_login_new_table->login_id;
            $data["show_web_cam"] = 0;
        } else {
            $worked = $this->mdl_worked->get_by(array('worked_date' => date('Y-m-d'), 'worked_user_id' => $emp_id));
            if (!$worked)
                $worked = $this->mdl_worked->get_by(array('worked_date' => date('Y-m-d'), 'worked_employee_id' => $emp_id));
            if ($worked) {
                $data["login"] = true;
                $data["login_time"] = date("H:i", strtotime($worked->worked_start));
                $data["logout"] = true;
                $data["logout_time"] = date("H:i", strtotime($worked->worked_end));
                $data["time_diff"] = $worked->worked_hours;
            }
        }

        if (isset($_POST["monthyear"])) {
            list($data["month"], $data["year"]) = explode("/", $this->input->post("monthyear"));
        }

        if (empty($data["month"])) {
            $data["month"] = date("m");
        }

        if (empty($data["year"])) {
            $data["year"] = date("Y");
        }

        $data["cdate"] = $data["month"] . "/" . $data["year"];

        if ($this->input->post('json'))
            $this->load->view('login_employee', $data);
        else
            return $data;
    }

    function getdatabymonth()
    {

        $emp_id = $this->session->userdata('user_id');
        if (isset($_POST["monthyear"])) {
            list($data["month"], $data["year"]) = explode("/", $_POST["monthyear"]);
        }

        if (empty($data["month"])) {
            $data["month"] = date("m");
        }

        if (empty($data["year"])) {
            $data["year"] = date("Y");
        }

        $userdata = $this->user_model->get_payroll_user(array('users.id' => $emp_id));

        $data['userdata'] = $userdata ? $userdata->row_array() : [];

        $firstDay = $data["year"] . '-' . $data["month"] . '-' . '01';
        $lastDay = $data["year"] . '-' . $data["month"] . '-' . date('t', strtotime($firstDay));

        $emp_login_details_by_month = $this->mdl_emp_login->get_many_by(array('login_date >= ' => $firstDay, 'login_date <= ' => $lastDay, 'login_user_id' => $emp_id));

        if (!$emp_login_details_by_month)
            $emp_login_details_by_month = $this->mdl_emp_login->get_many_by(array('login_date >= ' => $firstDay, 'login_date <= ' => $lastDay, 'login_employee_id' => $emp_id));

        $this->load->model('mdl_schedule');
        $this->load->model('mdl_payroll');
        $whereBonus['team_date >='] = strtotime($firstDay);
        $whereBonus['team_date <='] = strtotime($lastDay . ' 23:59:59');
        $whereBonus['user_id'] = $emp_id;
        $bonusesRows = $this->mdl_schedule->get_employee_bonuses($whereBonus);

        if (!$bonusesRows) {
            $whereBonus['employee_id'] = $emp_id;
            unset($whereBonus['user_id']);
            $bonusesRows = $this->mdl_schedule->get_employee_bonuses($whereBonus);
        }

        $bonuses = array();

        foreach ($bonusesRows as $bonus)
            $bonuses[date('Y-m-d', $bonus['team_date'])][] = array('bonus_amount' => $bonus['bonus_amount'], 'bonus_title' => $bonus['bonus_title'] ? $bonus['bonus_title'] : $bonus['bonus_type_name'], 'bonus_description' => $bonus['bonus_type_description']);

        $data['bonuses'] = $bonuses;

        $result['collectedBonuses'] = $data['collectedBonuses'] = $this->mdl_schedule->get_collected_bonuses_sum1($emp_id, $lastDay . ' 23:59:59');

        $payrollData = [];

        //$payroll = $this->mdl_payroll->get_by(['payroll_start_date <=' =>  $firstDay, 'payroll_end_date >=' => $firstDay]);
        $payrolls = $this->mdl_payroll->get_many_by(['payroll_day >=' => $firstDay, 'payroll_day <=' => $lastDay]);
        //echo '<pre>'; var_dump($payrolls); die;

        $prevWorkeds = $this->mdl_worked->get_many_by(array('worked_date >= ' => date('Y-m-d', strtotime('monday this week', strtotime($firstDay))), 'worked_date < ' => $firstDay, 'worked_user_id' => $emp_id));
        $data['prevHours'] = 0;
        //

        foreach ($prevWorkeds as $k => $v) {
            $data['prevHours'] += isset($v->worked_hours) ? $v->worked_hours - $v->worked_lunch : 0;
        }

        foreach ($payrolls as $k => $payroll) {
            $where_week = array('worked_user_id' => $emp_id, 'worked_payroll_id' => $payroll->payroll_id);
            $payrollData = $this->mdl_worked->get_worked_hours_total($where_week, TRUE, 1);
            $data['payroll'][$payroll->payroll_end_date] = !empty($payrollData) ? $payrollData : [];
        }
        $data["emp_login_details"] = array();
        if (!empty($emp_login_details_by_month)) {
            foreach ($emp_login_details_by_month as $kk => $details) {

                $data["emp_login_details"][$details->login_date][$kk]["login_time"] = date("H:i", strtotime($details->login));
                $data["worked"][$details->login_date] = isset($data["worked"][$details->login_date]) ? $data["worked"][$details->login_date] : $this->mdl_worked->get($details->login_worked_id);


                //$payrolls =  $this->mdl_payroll->with('mdl_worked')->get_many_by($payroll_id);
                //$worked_data = $this->mdl_worked->with('mdl_emp_login')->get_many_by(array('worked_payroll_id' => $data['payroll']->payroll_id, 'worked_user_id' => $employee_id));
                if ($details->logout) {
                    $data["emp_login_details"][$details->login_date][$kk]["logout_time"] = date("H:i", strtotime($details->logout));
                    $data["emp_login_details"][$details->login_date][$kk]["hourly_rate"] = $data["worked"][$details->login_date]->worked_hourly_rate;
                } else {
                    $data["emp_login_details"][$details->login_date][$kk]["logout_time"] = "-";
                }
                $data["emp_login_details"][$details->login_date][$kk]["time_diff"] = isset($data["worked"][$details->login_date]->worked_hours) ? $data["worked"][$details->login_date]->worked_hours - $data["worked"][$details->login_date]->worked_lunch : "-";


            }

        }
        $docs = [];
        $whereDocs['us_notification'] = 1;
        $whereDocs['us_user_id'] = $emp_id;
        $whereDocs['us_exp'] = date('Y-m-d', strtotime("+1 month", strtotime(date('Y-m-d'))));

        $docsMonth = $this->user_docs->get_many_by($whereDocs);

        $whereDocs['us_exp'] = date('Y-m-d', strtotime("+1 week", strtotime(date('Y-m-d'))));

        $docsWeek = $this->user_docs->get_many_by($whereDocs);
        $data['docs'] = array_merge($docsWeek, $docsMonth);

        $result['html'] = $this->load->view('monthly_report', $data, TRUE);
        $result['status'] = 'ok';

        die(json_encode($result));
    }

    function timer()
    {
        $CI = &get_instance();
        $CI->timer = TRUE;

        $timer = $this->input->post("timer");
        $new_rec_id = $this->input->post("new_rec_id");
        $login_rec_id = $this->input->post("login_rec_id");
        $emp_id = $this->session->userdata("user_id");
        $emp = $this->user_model->get_payroll_user(array('users.id' => $emp_id));
        $employee_data = $emp ? $emp->row_array() : [];

        if ($timer == "start") {
            $loginRow = $this->mdl_emp_login->get_by('login_user_id = ' . $emp_id . ' AND login_date = "' . date("Y-m-d") . '" AND login IS NOT NULL AND logout IS NULL');
            if ($loginRow && !empty($loginRow)){
                die(json_encode(array("status" => "ok", "rec_id" => $loginRow->login_id, "login_time" => $loginRow->login, "new_rec_id" => $loginRow->login_id)));
            }

            if ($employee_data['emp_check_work_time'] == '1' && $employee_data['emp_start_time'] != '00:00:00') {
                $work_time = date("Y-m-d ") . $employee_data['emp_start_time'];
                $login_time_current = date("Y-m-d H:i:s");
                if ($login_time_current < $work_time) {
                    $login_time = $work_time;
                } else {
                    $login_time = $login_time_current;
                }
            } else {
                $login_time = date("Y-m-d H:i:s");
            }

            /*if($this->input->post('lat') != 'undefined')
            {
                $new_data["login_lat"] = $data["login_lat"] = $this->input->post('lat');
                $new_data["login_lon"] = $data["login_lon"] = $this->input->post('lon');
            }*/
            if ($this->input->post('office') == 'true') {
                $new_data["login_lat"] = $this->input->post("ltt") ?: 'false';
                $new_data["login_lon"] = $this->input->post("lng") ?: 'false';
                $new_data["login_office"] = true;
                $new_data["login_in_office"] = $this->geoDistance(
                    $new_data["login_lat"],
                    $new_data["login_lon"],
                    config_item('office_lat'),
                    config_item('office_lon')
                );
            }

            $new_data['login_date'] = date('Y-m-d');
            $new_data['login'] = date('H:i:s', strtotime($login_time));
            $new_data['login_user_id'] = $emp_id;

            $new_rec_id = $this->mdl_emp_login->insert($new_data);

            $wsResponse = [
                "login_time" => date("H:i:s", strtotime($login_time)),
                "new_rec_id" => $new_rec_id
            ];

            if ($this->config->item('wsClient')) {
                $wsClient = new WSClient(new Version1X($this->config->item('wsClient') . '?chat=1&user_id=' . $emp_id));
                $wsClient->initialize();
                $wsClient->emit('room', ['chat-' . $emp_id]);
                $wsClient->emit('message', ['method' => 'trackerStarted', 'params' => $wsResponse]);
                $wsClient->close();
            }

            if ($new_rec_id) {
                die(json_encode(array("status" => "ok", "rec_id" => $new_rec_id, "login_time" => date("H:i", strtotime($login_time)), "new_rec_id" => $new_rec_id)));
            }
        } elseif ($timer == "stop") {
            $newRow = $this->mdl_emp_login->get($new_rec_id);
            $logout_time = date("Y-m-d H:i:s");

            if ($employee_data['emp_check_work_time'] == '1' && $employee_data['emp_start_time'] != '00:00:00') {
                $work_time = date("Y-m-d ") . $employee_data['emp_start_time'];
                if ($logout_time <= $work_time) {
                    $logout_time = $work_time;
                }
            }
            /*if($this->input->post('lat') != 'undefined')
            {
                $udata["logout_lat"] = $data["logout_lat"] = $this->input->post("lat");
                $udata["logout_lon"] = $data["logout_lon"] = $this->input->post("lon");
            }*/
            if ($this->input->post('office') == 'true') {
                $udata["logout_lat"] = $this->input->post("ltt") ?: 'false';
                $udata["logout_lon"] = $this->input->post("lng") ?: 'false';
                $udata["logout_office"] = true;
                $udata["logout_in_office"] = $this->geoDistance(
                    $udata["logout_lat"],
                    $udata["logout_lon"],
                    config_item('office_lat'),
                    config_item('office_lon')
                );
            }

            $udata['logout'] = date('H:i', strtotime($logout_time));
            $res = $this->mdl_emp_login->update($new_rec_id, $udata);
            $worked = $this->mdl_worked->get($newRow->login_worked_id);
            $time_str = 'Today you worked for ' . $worked->worked_hours . ' hours. Please sign out !!';
            $wsResponse = [
                "login_time" => date("H:i:s", strtotime($newRow->login)),
                "logout_time" => date("H:i:s", strtotime($logout_time)),
                "time_diff" => round((strtotime($logout_time) - strtotime($newRow->login)) / 3600, 2),
                'total_time_diff' => $worked->worked_hours
            ];

            if ($this->config->item('wsClient')) {
                $wsClient = new WSClient(new Version1X($this->config->item('wsClient') . '?chat=1&user_id=' . $emp_id));
                $wsClient->initialize();
                $wsClient->emit('room', ['chat-' . $emp_id]);
                $wsClient->emit('message', ['method' => 'trackerStopped', 'params' => $wsResponse]);
                $wsClient->close();
            }

            die(json_encode(array("status" => "ok", "logout_time" => date("H:i", strtotime($logout_time)), "time_diff" => round((strtotime($logout_time) - strtotime($newRow->login)) / 3600, 2), 'total_time_diff' => $worked->worked_hours, 'total_pay' => (($worked->worked_hours - $worked->worked_lunch) * $worked->worked_hourly_rate), 'time_str' => $time_str)));
        }

        die(json_encode(array("status" => "error")));
    }

    function ajax_save_report()
    {
        $data = $this->input->post();
        $team_id = $this->input->post('team_id');

        if (isset($data['event_start'])) {
            foreach ($data['event_start'] as $key => $val) {
                $leader_name = $this->mdl_schedule->get_teams(array('team_id' => $team_id));
                $workorder = $this->mdl_workorders->wo_find_by_id($data['wo_id'][$key], false);

                $insert = array();
                $insert['event_start_work'] = $data['event_start'][$key];
                $insert['event_finish_work'] = $data['event_finish'][$key];
                $insert['event_start_travel'] = $data['travel_start'][$key];
                $insert['status'] = $data['status'][$key];


                if (isset($data['payment'][$key]))
                    $insert['event_payment'] = $data['payment'][$key];
                if (isset($data['payment'][$key]))
                    $insert['payment'] = $data['payment'][$key];
                if (isset($data['payment_type'][$key]))
                    $insert['event_payment_type'] = $data['payment_type'][$key];
                if (isset($data['payment_amount'][$key]))
                    $insert['payment_amount'] = getAmount($data['payment_amount'][$key]);
                if (isset($data['time'][$key]))
                    $insert['event_time_to_finish'] = $data['time'][$key];
                if (isset($data['work_description'][$key]))
                    $insert['event_work_remaining'] = $data['work_description'][$key];
                $insert['event_damage'] = $data['damage'][$key];
                if (isset($data['demage_description'][$key]))
                    $insert['event_damage_description'] = $data['demage_description'][$key];
                if (isset($data['event_description'][$key]))
                    $insert['event_description'] = $data['event_description'][$key];

                $insert['wo_id'] = $data['wo_id'][$key];

                $this->eventactions->setEventId($key);
                $event = $this->eventactions->getEvent();
                if (!$event) {
                    $this->eventactions->start_trevel(['ev_event_id' => $key, 'ev_team_id' => $team_id, 'ev_estimate_id' => $workorder->estimate_id, 'ev_start_work' => date("Y-m-d H:i:s")]);
                    $this->eventactions->setEventId($key);
                }

                $this->eventactions->end_work($insert);
                //$this->mdl_schedule->update($key, array('event_report' => json_encode($insert)));
                // make notes


                $update_msg = $leader_name[0]->emp_name . ' filled report for <a href="' . base_url($workorder->workorder_no) . '#eventInfo-' . $key . '" data-toggle="modal">' . $workorder->workorder_no . '</a>';
                make_notes($workorder->client_id, $update_msg, 'system', intval($workorder->workorder_no));
                // end make notes
            }
        }
        /*--------------------team_expeses_report-------------------*/
        $insert_team_expeses_report = [];
        if (count(element('bld', $data, []))) {
            foreach ($data['bld'] as $key => $value) {
                $insert_team_expeses_report[$key]['ter_team_id'] = $team_id;
                $insert_team_expeses_report[$key]['ter_bld'] = $value;
                $insert_team_expeses_report[$key]['ter_user_id'] = $key;
            }
        }
        if (count(element('extra', $data, false))) {
            foreach ($data['extra'] as $key => $value) {
                $insert_team_expeses_report[$key]['ter_team_id'] = $team_id;
                $insert_team_expeses_report[$key]['ter_extra'] = $value;
                $insert_team_expeses_report[$key]['ter_user_id'] = $key;
            }
        }
        if (count(element('extra_comment', $data, false))) {
            foreach ($data['extra_comment'] as $key => $value) {
                $insert_team_expeses_report[$key]['ter_team_id'] = $team_id;
                $insert_team_expeses_report[$key]['ter_extra_comment'] = $value;
                $insert_team_expeses_report[$key]['ter_user_id'] = $key;
            }
        }
        $team_expeses_report = array_values($insert_team_expeses_report);
        $this->load->model('mdl_team_expeses_report');
        $this->mdl_team_expeses_report->save_many($team_expeses_report);
        /*--------------------team_expeses_report-------------------*/

        if (isset($data['malfunctions_description']))
            $this->mdl_schedule->update_team($team_id, array('team_fail_equipment' => $data['malfunctions_description']));
        if (isset($data['expenses_description']))
            $this->mdl_schedule->update_team($team_id, array('team_expenses' => $data['expenses_description']));

        if (isset($data['logout_time'])) {
            foreach ($data['logout_time'] as $key => $val) {

                $update = array();
                $update['employee_logout'] = $val;
                $this->mdl_schedule->update_team_member(array('user_id' => $key, 'employee_team_id' => $team_id), $update);
            }
        }

        $this->mdl_schedule->update_team_member(array('user_id' => $this->session->userdata("emp_user_id"), 'employee_team_id' => $team_id), array('employee_logout' => date('H:i:s')));
        $result['status'] = 'ok';
        die(json_encode($result));
    }


    /*
    use application\modules\clients\models\Client;
    use application\modules\estimates\models\Estimate;
    use application\modules\workorders\models\Workorder;
    use application\modules\invoices\models\Invoice;
    */
    function ajax_gsearch()
    {


        $SearchModel = new Search();
        $data['results'] = $SearchModel->global_serach(trim($this->input->post('ls_query')))->paginate(100)->items();


        /*
        $keyword = trim($this->input->post('ls_query'));
        $phone = preg_match('/(\(?\d{3}\)?[\- ]?)?[\d\- ]{7,10}$/is', $keyword);
        $sumData = check_price($keyword);

        if($phone)
            $data['results'] = $this->mdl_dashboard->search(numberFrom($keyword));
        elseif($sumData)
        {

            $pattern = '/^[$]{0,1}[ ]{0,}([0-9,.]{1,})$/is';
            $res = preg_match($pattern, $keyword, $string);
            $sum = money($string[1]);

            if($sumData == 1)
                $data['results'] = $this->mdl_dashboard->search(NULL, $sum);
            else
                $data['results'] = $this->mdl_dashboard->search($sum);
        }
        else
            $data['results'] = $this->mdl_dashboard->search($keyword);

        */

        $result = [
            'status' => 'success',
            'message' => '<tr><td class=\'success\'>Successful request</td></tr>',
            'result' => json_encode([
                'html' => $this->load->view('ajax_global_search', $data, TRUE),
                'number_of_results' => 4,
                'total_pages' => 2
            ])
        ];

        return $this->response($result);
        //die(json_encode($result));
        //echo '{"status":"success","message":"<tr><td class=\'success\'>Successful request<\/td><\/tr>","result":"{\"html\":\"<tr><td>5<\\\/td><td>Teresa<\\\/td><td>Fields<\\\/td><td>Korea, North<\\\/td><td>57<\\\/td><td>58692<\\\/td><td>34026.17<\\\/td><\\\/tr><tr><td>246<\\\/td><td>Teresa<\\\/td><td>Fields<\\\/td><td>Italy<\\\/td><td>20<\\\/td><td>91121<\\\/td><td>2047.18<\\\/td><\\\/tr><tr><td>422<\\\/td><td>Teresa<\\\/td><td>Webb<\\\/td><td>Malta<\\\/td><td>26<\\\/td><td>76049<\\\/td><td>23498.79<\\\/td><\\\/tr><tr><td>367<\\\/td><td>Terry<\\\/td><td>Alvarez<\\\/td><td>India<\\\/td><td>39<\\\/td><td>65300<\\\/td><td>51153.53<\\\/td><\\\/tr><tr><td>374<\\\/td><td>Terry<\\\/td><td>Peterson<\\\/td><td>Senegal<\\\/td><td>82<\\\/td><td>51973<\\\/td><td>47313.80<\\\/td><\\\/tr>\",\"number_of_results\":6,\"total_pages\":2}"}';
    }

    function ajax_logout_not_office()
    {
        $result['status'] = 'error';
        $where['login_user_id'] = $this->input->post('id');
        $where['login_date'] = $this->input->post('date');
        $where['login_office'] = 1;
        unset($this->mdl_emp_login->before_update[1]);

        $res = $this->mdl_emp_login->update_by($where, ['login_office' => 0]);

        if ($res)
            $result['status'] = 'ok';
        die(json_encode($result));
    }

    function _get_motivation_stats()
    {
        $emp_id = $this->session->userdata('user_id');
        $userdata = $this->user_model->get_usermeta(array('users.id' => $emp_id))->row();
        $data['estimatorTrigger'] = $data['workerTrigger'] = FALSE;
        //($this->session->userdata("PP") == 0 && $this->session->userdata('user_type') != "admin") || ($data['userData']->emp_feild_worker == 1 && !$data['userData']->emp_field_estimator)

        if ($userdata->emp_field_estimator) {
            $data['estimatorTrigger'] = TRUE;
            $data['previous_current'] = $data['previous_last_year'] = $data['last_year'] = $data['current'] = $data['last_quart'] = $data['current_quart'] = $data['before_last_yearly'] = $data['last_yearly'] = 0;
            $data['company_previous_current'] = $data['company_previous_last_year'] = $data['company_last_year'] = $data['company_current'] = $data['company_last_quart'] = $data['company_current_quart'] = $data['company_before_last_yearly'] = $data['company_last_yearly'] = 0;
            $data['estimators'] = $this->mdl_estimates->get_all_estimators();

            //$whereStat['estimate_statuses.est_status_confirmed'] = 1;

//            $statArray = [
//                'previous_last_year' => [
//                    'month' => date('F Y', strtotime(date('Y-m-01 00:00:00' ,strtotime("-13 month")))),
//                    'from' => strtotime(date('Y-m-01 00:00:00' ,strtotime("-13 month"))),
//                    'to' => strtotime(date('Y-m-t 23:59:59' ,strtotime("-13 month"))),
//                ],
//                'previous_current' => [
//                    'month' => date('F Y', strtotime(date('Y-m-01 00:00:00' ,strtotime("-1 month")))),
//                    'from' => strtotime(date('Y-m-01 00:00:00' ,strtotime("-1 month"))),
//                    'to' => strtotime(date('Y-m-t 23:59:59' ,strtotime("-1 month"))),
//                ],
//                'last_year' => [
//                    'month' => date('F Y', strtotime(date('Y-m-01 00:00:00' ,strtotime("-1 year")))),
//                    'from' => strtotime(date('Y-m-01 00:00:00' ,strtotime("-1 year"))),
//                    'to' => strtotime(date('Y-m-t 23:59:59' ,strtotime("-1 year"))),
//                ],
//                'current' => [
//                    'month' => date('F Y', strtotime(date('Y-m-01  00:00:00'))),
//                    'from' => strtotime(date('Y-m-01  00:00:00')),
//                    'to' => strtotime(date('Y-m-t 23:59:59')),
//                ],
//                'current_quart' => [
//                    'month' => get_quart(date('Y-m-d', strtotime("-3 month"))),
//                    'from' => strtotime($data['current_quart_month']['start'] . ' 00:00:00'),
//                    'to' => strtotime(date('Y-m-t 23:59:59' ,strtotime("-13 month"))),
//                ],
//                'previous_last_year' => [
//                    'month' => date('F Y', strtotime(date('Y-m-01 00:00:00' ,strtotime("-13 month")))),
//                    'from' => strtotime(date('Y-m-01 00:00:00' ,strtotime("-13 month"))),
//                    'to' => strtotime(date('Y-m-t 23:59:59' ,strtotime("-13 month"))),
//                ],
//                'previous_last_year' => [
//                    'month' => date('F Y', strtotime(date('Y-m-01 00:00:00' ,strtotime("-13 month")))),
//                    'from' => strtotime(date('Y-m-01 00:00:00' ,strtotime("-13 month"))),
//                    'to' => strtotime(date('Y-m-t 23:59:59' ,strtotime("-13 month"))),
//                ],
//                'previous_last_year' => [
//                    'month' => date('F Y', strtotime(date('Y-m-01 00:00:00' ,strtotime("-13 month")))),
//                    'from' => strtotime(date('Y-m-01 00:00:00' ,strtotime("-13 month"))),
//                    'to' => strtotime(date('Y-m-t 23:59:59' ,strtotime("-13 month"))),
//                ],
//            ];
            //Previous Month Last Year
            $from = strtotime(date('Y-m-01 00:00:00', strtotime("-13 month")));
            $to = strtotime(date('Y-m-t 23:59:59', strtotime("-13 month")));
            $data['previous_last_year_month'] = date('F Y', $from);
            $data['company_previous_last_year'] = $this->mdl_estimates->get_dashboard_estimate_statistic([
                'employees.emp_field_estimator' => '1',
                'estimate_statuses.est_status_confirmed' => 1,
                'estimates.date_created >=' => $from,
                'estimates.date_created <=' => $to,
            ]);
            $data['previous_last_year'] = $this->mdl_estimates->get_dashboard_estimate_statistic([
                'estimate_statuses.est_status_confirmed' => 1,
                'estimates.user_id' => $emp_id,
                'estimates.date_created >=' => $from,
                'estimates.date_created <=' => $to,
            ]);

            //Previous Month Start
            $from = strtotime(date('Y-m-01 00:00:00', strtotime("-1 month")));
            $to = strtotime(date('Y-m-t 23:59:59', strtotime("-1 month")));
            $data['previous_current_month'] = date('F Y', $from);
            $data['company_previous_current_month'] = $this->mdl_estimates->get_dashboard_estimate_statistic([
                'employees.emp_field_estimator' => '1',
                'estimate_statuses.est_status_confirmed' => 1,
                'estimates.date_created >=' => $from,
                'estimates.date_created <=' => $to,
            ]);
            $data['previous_previous_current_month'] = $this->mdl_estimates->get_dashboard_estimate_statistic([
                'estimate_statuses.est_status_confirmed' => 1,
                'estimates.user_id' => $emp_id,
                'estimates.date_created >=' => $from,
                'estimates.date_created <=' => $to,
            ]);

            //Current Month Last Year
            $from = strtotime(date('Y-m-01 00:00:00', strtotime("-1 year")));
            $to = strtotime(date('Y-m-t 23:59:59', strtotime("-1 year")));
            $data['last_year_month'] = date('F Y', $from);
            $data['company_last_year'] = $this->mdl_estimates->get_dashboard_estimate_statistic([
                'employees.emp_field_estimator' => '1',
                'estimate_statuses.est_status_confirmed' => 1,
                'estimates.date_created >=' => $from,
                'estimates.date_created <=' => $to,
            ]);
            $data['last_year'] = $this->mdl_estimates->get_dashboard_estimate_statistic([
                'estimate_statuses.est_status_confirmed' => 1,
                'estimates.user_id' => $emp_id,
                'estimates.date_created >=' => $from,
                'estimates.date_created <=' => $to,
            ]);

            //Current Month
            $from = strtotime(date('Y-m-01  00:00:00'));
            $to = strtotime(date('Y-m-t 23:59:59'));
            $data['current_month'] = date('F Y', $from);
            $data['company_current'] = $this->mdl_estimates->get_dashboard_estimate_statistic([
                'employees.emp_field_estimator' => '1',
                'estimate_statuses.est_status_confirmed' => 1,
                'estimates.date_created >=' => $from,
                'estimates.date_created <=' => $to,
            ]);
            $data['current'] = $this->mdl_estimates->get_dashboard_estimate_statistic([
                'estimate_statuses.est_status_confirmed' => 1,
                'estimates.user_id' => $emp_id,
                'estimates.date_created >=' => $from,
                'estimates.date_created <=' => $to,
            ]);

            //Current Quart
            $data['current_quart_month'] = get_quart(date('Y-m-d', strtotime("-3 month")));
            $from = strtotime($data['current_quart_month']['start'] . ' 00:00:00');
            $to = strtotime($data['current_quart_month']['end'] . ' 23:59:59');
            $data['company_current_quart'] = $this->mdl_estimates->get_dashboard_estimate_statistic([
                'employees.emp_field_estimator' => '1',
                'estimate_statuses.est_status_confirmed' => 1,
                'estimates.date_created >=' => $from,
                'estimates.date_created <=' => $to,
            ]);
            $data['current_quart'] = $this->mdl_estimates->get_dashboard_estimate_statistic([
                'estimate_statuses.est_status_confirmed' => 1,
                'estimates.user_id' => $emp_id,
                'estimates.date_created >=' => $from,
                'estimates.date_created <=' => $to,
            ]);

            //Last Year Quart
            $data['last_quart_month'] = get_quart(date('Y-m-d', strtotime("-15 month")));
            $from = strtotime($data['current_quart_month']['start'] . ' 00:00:00');
            $to = strtotime($data['current_quart_month']['end'] . ' 23:59:59');
            $data['company_last_quart'] = $this->mdl_estimates->get_dashboard_estimate_statistic([
                'employees.emp_field_estimator' => '1',
                'estimate_statuses.est_status_confirmed' => 1,
                'estimates.date_created >=' => $from,
                'estimates.date_created <=' => $to,
            ]);
            $data['last_quart'] = $this->mdl_estimates->get_dashboard_estimate_statistic([
                'estimate_statuses.est_status_confirmed' => 1,
                'estimates.user_id' => $emp_id,
                'estimates.date_created >=' => $from,
                'estimates.date_created <=' => $to,
            ]);

            //last_yearly
            $data['last_yearly_day'] = date('Y', strtotime("-12 month"));
            $from = strtotime(date('Y-01-01', strtotime("-12 month")) . ' 00:00:00');
            $to = strtotime(date('Y-12-31', strtotime("-12 month")) . ' 23:59:59');
            $data['company_last_yearly'] = $this->mdl_estimates->get_dashboard_estimate_statistic([
                'employees.emp_field_estimator' => '1',
                'estimates.date_created >=' => $from,
                'estimates.date_created <=' => $to,
            ]);
            $data['last_yearly'] = $this->mdl_estimates->get_dashboard_estimate_statistic([
                'estimates.user_id' => $emp_id,
                'estimates.date_created >=' => $from,
                'estimates.date_created <=' => $to,
            ]);

            //before last_yearly
            $data['before_last_yearly_day'] = date('Y', strtotime("-24 month"));
            $from = strtotime(date('Y-01-01', strtotime("-24 month")) . ' 00:00:00');
            $to = strtotime(date('Y-12-31', strtotime("-24 month")) . ' 23:59:59');
            $data['before_company_last_yearly'] = $this->mdl_estimates->get_dashboard_estimate_statistic([
                'employees.emp_field_estimator' => '1',
                'estimates.date_created >=' => $from,
                'estimates.date_created <=' => $to,
            ]);
            $data['before_last_yearly'] = $this->mdl_estimates->get_dashboard_estimate_statistic([
                'estimates.user_id' => $emp_id,
                'estimates.date_created >=' => $from,
                'estimates.date_created <=' => $to,
            ]);

//            foreach($data['estimators'] as $key=>$val)
//			{
//				$whereStat['estimates.user_id'] = $val['id'];
//				//Previous Month Last Year
//				$whereStat['estimates.date_created >='] = strtotime(date('Y-m-01 00:00:00' ,strtotime("-13 month")));
//				$whereStat['estimates.date_created <='] = strtotime(date('Y-m-t 23:59:59' ,strtotime("-13 month")));
//				$estimates_confirmed = $this->mdl_estimates->get_estimate_statistic($whereStat);
//				$data['previous_last_year_month'] = date('F Y', $whereStat['estimates.date_created >=']);
//
//				if($estimates_confirmed)
//				{
//					foreach($estimates_confirmed as $jkey=>$jval)
//						$data['company_previous_last_year'] += round($jval['sum'], 2);
//				}
//				if($val['id'] == $emp_id)
//				{
//
//					$estimates_confirmed = $this->mdl_estimates->get_estimate_statistic($whereStat);
//					if($estimates_confirmed)
//					{
//
//						foreach($estimates_confirmed as $jkey=>$jval)
//							$data['previous_last_year'] += round($jval['sum'], 2);
//					}
//				}
//				//END Previous Month Last Year
//				//Previous Month Start
//				$whereStat['estimates.date_created >='] = strtotime(date('Y-m-01 00:00:00', strtotime("-1 month")));
//				$whereStat['estimates.date_created <='] = strtotime(date('Y-m-t 23:59:59', strtotime("-1 month")));
//				$estimates_confirmed = $this->mdl_estimates->get_estimate_statistic($whereStat);
//				$data['previous_current_month'] = date('F Y', $whereStat['estimates.date_created >=']);
//				if($estimates_confirmed)
//				{
//					foreach($estimates_confirmed  as $jkey=>$jval)
//						 $data['company_previous_current'] += round($jval['sum'], 2);
//				}
//				if($val['id'] == $emp_id)
//				{
//
//					$estimates_confirmed = $this->mdl_estimates->get_estimate_statistic($whereStat);
//					if($estimates_confirmed)
//					{
//						foreach($estimates_confirmed  as $jkey=>$jval)
//							 $data['previous_current'] += round($jval['sum'], 2);
//					}
//				}
//				//END Previous Month Start
//				//Current Month Last Year
//				$whereStat['estimates.date_created >='] = strtotime(date('Y-m-01 00:00:00' ,strtotime("-1 year")));
//				$whereStat['estimates.date_created <='] = strtotime(date('Y-m-t 23:59:59' ,strtotime("-1 year")));
//				$estimates_confirmed = $this->mdl_estimates->get_estimate_statistic($whereStat);
//				$data['last_year_month'] = date('F Y', $whereStat['estimates.date_created >=']);
//				if($estimates_confirmed)
//				{
//					foreach($estimates_confirmed as $jkey=>$jval)
//						 $data['company_last_year'] += round($jval['sum'], 2);
//
//				}
//				if($val['id'] == $emp_id)
//				{
//
//					$estimates_confirmed = $this->mdl_estimates->get_estimate_statistic($whereStat);
//					if($estimates_confirmed)
//					{
//						foreach($estimates_confirmed as $jkey=>$jval)
//							 $data['last_year'] += round($jval['sum'], 2);
//					}
//				}
//				//END Current Month Last Year
//
//				//Current Month
//				$whereStat['estimates.date_created >='] = strtotime(date('Y-m-01  00:00:00'));
//				$whereStat['estimates.date_created <='] = strtotime(date('Y-m-t 23:59:59'));
//				$estimates_confirmed = $this->mdl_estimates->get_estimate_statistic($whereStat);
//				$data['current_month'] = date('F Y', $whereStat['estimates.date_created >=']);
//				if($estimates_confirmed)
//				{
//					foreach($estimates_confirmed  as $jkey=>$jval)
//						 $data['company_current'] += round($jval['sum'], 2);
//				}
//				if($val['id'] == $emp_id)
//				{
//
//					$estimates_confirmed = $this->mdl_estimates->get_estimate_statistic($whereStat);
//					if($estimates_confirmed)
//					{
//						foreach($estimates_confirmed  as $jkey=>$jval)
//							 $data['current'] += round($jval['sum'], 2);
//					}
//				}
//				//END Current Month
//				//Current Quart
//				$data['current_quart_month'] = get_quart(date('Y-m-d', strtotime("-3 month")));
//				$whereStat['estimates.date_created >='] = strtotime($data['current_quart_month']['start'] . ' 00:00:00');
//				$whereStat['estimates.date_created <='] = strtotime($data['current_quart_month']['end'] . ' 23:59:59');
//				$estimates_confirmed = $this->mdl_estimates->get_estimate_statistic($whereStat);
//				if($estimates_confirmed)
//				{
//					foreach($estimates_confirmed as $jkey=>$jval)
//						 $data['company_current_quart'] += round($jval['sum'], 2);
//				}
//				if($val['id'] == $emp_id)
//				{
//					$estimates_confirmed = $this->mdl_estimates->get_estimate_statistic($whereStat);
//					if($estimates_confirmed)
//					{
//						foreach($estimates_confirmed as $jkey=>$jval)
//							 $data['current_quart'] += round($jval['sum'], 2);
//					}
//				}
//				//End Current Quart
//				//Last Year Quart
//				$data['last_quart_month'] =  get_quart(date('Y-m-d', strtotime("-15 month")));
//				$whereStat['estimates.date_created >='] = strtotime($data['last_quart_month']['start'] . ' 00:00:00');
//				$whereStat['estimates.date_created <='] = strtotime($data['last_quart_month']['end'] . ' 23:59:59');
//				$estimates_confirmed = $this->mdl_estimates->get_estimate_statistic($whereStat);
//				if($estimates_confirmed)
//				{
//					foreach($estimates_confirmed as $jkey=>$jval)
//						 $data['company_last_quart'] += round($jval['sum'], 2);
//				}
//				if($val['id'] == $emp_id)
//				{
//					$estimates_confirmed = $this->mdl_estimates->get_estimate_statistic($whereStat);
//					if($estimates_confirmed)
//					{
//						foreach($estimates_confirmed as $jkey=>$jval)
//							 $data['last_quart'] += round($jval['sum'], 2);
//					}
//				}
//				//last_yearly
//				$data['last_yearly_day'] = date('Y', strtotime("-12 month"));
//				$whereStat['estimates.date_created >='] = strtotime(date('Y-01-01', strtotime("-12 month")) . ' 00:00:00');
//				$whereStat['estimates.date_created <='] = strtotime(date('Y-12-31', strtotime("-12 month")) . ' 23:59:59');
//
//				$estimates_confirmed = $this->mdl_estimates->get_estimate_statistic($whereStat);
//
//				if($estimates_confirmed)
//				{
//					foreach($estimates_confirmed as $jkey=>$jval)
//						 $data['company_last_yearly'] += round($jval['sum'], 2);
//				}
//				if($val['id'] == $emp_id)
//				{
//					$estimates_confirmed = $this->mdl_estimates->get_estimate_statistic($whereStat);
//					if($estimates_confirmed)
//					{
//						foreach($estimates_confirmed as $jkey=>$jval)
//							 $data['last_yearly'] += round($jval['sum'], 2);
//					}
//				}
//				//END last_yearly
//				//before last_yearly
//				$data['before_last_yearly_day'] = date('Y', strtotime("-24 month"));
//				$whereStat['estimates.date_created >='] = strtotime(date('Y-01-01', strtotime("-24 month")) . ' 00:00:00');
//				$whereStat['estimates.date_created <='] = strtotime(date('Y-12-31', strtotime("-24 month")) . ' 23:59:59');
//
//				$estimates_confirmed = $this->mdl_estimates->get_estimate_statistic($whereStat);
//
//				if($estimates_confirmed)
//				{
//					foreach($estimates_confirmed as $jkey=>$jval)
//						 $data['company_before_last_yearly'] += round($jval['sum'], 2);
//				}
//				if($val['id'] == $emp_id)
//				{
//					$estimates_confirmed = $this->mdl_estimates->get_estimate_statistic($whereStat);
//					if($estimates_confirmed)
//					{
//						foreach($estimates_confirmed as $jkey=>$jval)
//							 $data['before_last_yearly'] += round($jval['sum'], 2);
//					}
//				}
//
//				//END before last_yearly
//			} //die;
        } //echo '<pre>'; var_dump($data); die;
        if ($userdata->emp_feild_worker) {
            $data['workerTrigger'] = TRUE;
            $data['worker_previous_current'] = $data['worker_previous_last_year'] = $data['worker_last_year'] = $data['worker_current'] = $data['worker_last_quart'] = $data['worker_current_quart'] = $data['worker_before_last_yearly'] = $data['worker_last_yearly'] = 0;
            $data['worker_company_previous_current'] = $data['worker_company_previous_last_year'] = $data['worker_company_last_year'] = $data['worker_company_current'] = $data['worker_company_last_quart'] = $data['worker_company_current_quart'] = $data['worker_company_before_last_yearly'] = $data['worker_company_last_yearly'] = 0;
            $data['worker_avg_previous_current'] = $data['worker_avg_previous_last_year'] = $data['worker_avg_last_year'] = $data['worker_avg_current'] = $data['worker_avg_last_quart'] = $data['worker_avg_current_quart'] = $data['worker_avg_before_last_yearly'] = $data['worker_avg_last_yearly'] = 0;
            $data['worker_avg_company_previous_current'] = $data['worker_avg_company_previous_last_year'] = $data['worker_avg_company_last_year'] = $data['worker_avg_company_current'] = $data['worker_avg_company_last_quart'] = $data['worker_avg_company_current_quart'] = $data['worker_avg_company_before_last_yearly'] = $data['worker_avg_company_last_yearly'] = 0;

            //Previous Month Last Year
            $wdata_emp['team_date >='] = $wdata['team_date >='] = strtotime(date('Y-m-01 00:00:00', strtotime("-13 month")));
            $wdata_emp['team_date <='] = $wdata['team_date <='] = strtotime(date('Y-m-t 23:59:59', strtotime("-13 month")));
            $mhrs = $this->mdl_workorders->employees_mhr_return($wdata, $wdata_emp);
            $data['worker_previous_last_year_month'] = date('F Y', $wdata_emp['team_date >=']);
            $total = 0;
            if ($mhrs && !empty($mhrs)) {
                foreach ($mhrs as $key => $val) {
                    $data['worker_company_previous_last_year'] += round($val['total_mhrs'], 2);
                    $total += round($val['total'], 2);
                    if ($val['id'] == $emp_id) {
                        $data['worker_previous_last_year'] = round($val['total_mhrs'], 2);
                        $data['worker_avg_previous_last_year'] = round($val['new_avg'], 2);
                    }
                }
                if ($total && $data['worker_company_previous_last_year'])
                    $data['worker_avg_company_previous_last_year'] = round($total / $data['worker_company_previous_last_year'], 2);
            }
            //End Previous Month Last Year
            //Previous Month Start

            $wdata_emp['team_date >='] = $wdata['team_date >='] = strtotime(date('Y-m-01 00:00:00', strtotime("-1 month")));
            $wdata_emp['team_date <='] = $wdata['team_date <='] = strtotime(date('Y-m-t 23:59:59', strtotime("-1 month")));
            $mhrs = $this->mdl_workorders->employees_mhr_return($wdata, $wdata_emp);
            $data['worker_previous_current_month'] = date('F Y', $wdata_emp['team_date >=']);
            $total = 0;
            if ($mhrs && !empty($mhrs)) {
                foreach ($mhrs as $key => $val) {
                    $data['worker_company_previous_current'] += round($val['total_mhrs'], 2);
                    $total += round($val['total'], 2);
                    if ($val['id'] == $emp_id) {
                        $data['worker_previous_current'] = round($val['total_mhrs'], 2);
                        $data['worker_avg_previous_current'] = round($val['new_avg'], 2);
                    }
                }
                if ($total && $data['worker_company_previous_current'])
                    $data['worker_avg_company_previous_current'] = round($total / $data['worker_company_previous_current'], 2);
            }
            //End Previous Month Start
            //Current Month Last Year
            $wdata_emp['team_date >='] = $wdata['team_date >='] = strtotime(date('Y-m-01 00:00:00', strtotime("-1 year")));
            $wdata_emp['team_date <='] = $wdata['team_date <='] = strtotime(date('Y-m-t 23:59:59', strtotime("-1 year")));
            $mhrs = $this->mdl_workorders->employees_mhr_return($wdata, $wdata_emp);
            $data['worker_last_year_month'] = date('F Y', $wdata_emp['team_date >=']);
            $total = 0;
            if ($mhrs && !empty($mhrs)) {
                foreach ($mhrs as $key => $val) {
                    $data['worker_company_last_year'] += round($val['total_mhrs'], 2);
                    $total += round($val['total'], 2);
                    if ($val['id'] == $emp_id) {
                        $data['worker_last_year'] = round($val['total_mhrs'], 2);
                        $data['worker_avg_last_year'] = round($val['new_avg'], 2);
                    }
                }
                if ($total && $data['worker_company_last_year'])
                    $data['worker_avg_company_last_year'] = round($total / $data['worker_company_last_year'], 2);
            }
            //End Current Month Last Year
            //Current Month
            $wdata_emp['team_date >='] = $wdata['team_date >='] = strtotime(date('Y-m-01  00:00:00'));
            $wdata_emp['team_date <='] = $wdata['team_date <='] = strtotime(date('Y-m-t 23:59:59'));
            $mhrs = $this->mdl_workorders->employees_mhr_return($wdata, $wdata_emp);
            $data['worker_current_month'] = date('F Y', $wdata_emp['team_date >=']);
            $total = 0;
            if ($mhrs && !empty($mhrs)) {
                foreach ($mhrs as $key => $val) {

                    $data['worker_company_current'] += round($val['total_mhrs'], 2);
                    $total += round($val['total'], 2);

                    if ($val['id'] == $emp_id) {
                        $data['worker_current'] = round($val['total_mhrs'], 2);
                        $data['worker_avg_current'] = round($val['new_avg'], 2);
                    }
                }
                if ($total && $data['worker_company_current'])
                    $data['worker_avg_company_current'] = round($total / $data['worker_company_current'], 2);
            }
            //End Previous Month Start
            //Current Quart
            $data['worker_current_quart_month'] = get_quart(date('Y-m-d', strtotime("-3 month")));

            $wdata_emp['team_date >='] = $wdata['team_date >='] = strtotime($data['worker_current_quart_month']['start'] . ' 00:00:00');
            $wdata_emp['team_date <='] = $wdata['team_date <='] = strtotime($data['worker_current_quart_month']['end'] . ' 23:59:59');
            $mhrs = $this->mdl_workorders->employees_mhr_return($wdata, $wdata_emp);
            $total = 0;
            if ($mhrs && !empty($mhrs)) {
                foreach ($mhrs as $key => $val) {
                    $data['worker_company_current_quart'] += round($val['total_mhrs'], 2);
                    $total += round($val['total'], 2);

                    if ($val['id'] == $emp_id) {
                        $data['worker_current_quart'] = round($val['total_mhrs'], 2);
                        $data['worker_avg_current_quart'] = round($val['new_avg'], 2);
                    }
                }
                if ($total && $data['worker_company_current_quart'])
                    $data['worker_avg_company_current_quart'] = round($total / $data['worker_company_current_quart'], 2);
            }
            //End Current Quart
            //Last Year Quart
            $data['worker_last_quart_month'] = get_quart(date('Y-m-d', strtotime("-15 month")));

            $wdata_emp['team_date >='] = $wdata['team_date >='] = strtotime($data['worker_last_quart_month']['start'] . ' 00:00:00');
            $wdata_emp['team_date <='] = $wdata['team_date <='] = strtotime($data['worker_last_quart_month']['end'] . ' 23:59:59');
            $mhrs = $this->mdl_workorders->employees_mhr_return($wdata, $wdata_emp);
            $total = 0;
            if ($mhrs && !empty($mhrs)) {

                foreach ($mhrs as $key => $val) {
                    $data['worker_company_last_quart'] += round($val['total_mhrs'], 2);
                    $total += round($val['total'], 2);
                    if ($val['id'] == $emp_id) {
                        $data['worker_last_quart'] = round($val['total_mhrs'], 2);
                        $data['worker_avg_last_quart'] = round($val['new_avg'], 2);
                    }
                }
                if ($total && $data['worker_company_last_quart'])
                    $data['worker_avg_company_last_quart'] = round($total / $data['worker_company_last_quart'], 2);
            }
            //End Current Quart
            //Last Yearly
            $data['worker_before_last_yearly_day'] = date('Y', strtotime("-24 month"));

            $wdata_emp['team_date >='] = $wdata['team_date >='] = strtotime(date('Y-01-01', strtotime("-24 month")) . ' 00:00:00');
            $wdata_emp['team_date <='] = $wdata['team_date <='] = strtotime(date('Y-12-31', strtotime("-24 month")) . ' 23:59:59');
            $mhrs = $this->mdl_workorders->employees_mhr_return($wdata, $wdata_emp);
            $total = 0;
            if ($mhrs && !empty($mhrs)) {
                foreach ($mhrs as $key => $val) {

                    $data['worker_company_before_last_yearly'] += round($val['total_mhrs'], 2);
                    $total += round($val['total'], 2);
                    if ($val['id'] == $emp_id) {
                        $data['worker_before_last_yearly'] = round($val['total_mhrs'], 2);
                        $data['worker_avg_before_last_yearly'] = round($val['new_avg'], 2);
                    }

                }
                if ($total && $data['worker_company_before_last_yearly'])
                    $data['worker_avg_company_before_last_yearly'] = round($total / $data['worker_company_before_last_yearly'], 2);
            }
            //End Last Yearly
            //Last Yearly
            $data['worker_last_yearly_day'] = date('Y', strtotime("-12 month"));

            $wdata_emp['team_date >='] = $wdata['team_date >='] = strtotime(date('Y-01-01', strtotime("-12 month")) . ' 00:00:00');
            $wdata_emp['team_date <='] = $wdata['team_date <='] = strtotime(date('Y-12-31', strtotime("-12 month")) . ' 23:59:59');
            $mhrs = $this->mdl_workorders->employees_mhr_return($wdata, $wdata_emp);
            $total = 0;
            if ($mhrs && !empty($mhrs)) {
                foreach ($mhrs as $key => $val) {
                    $data['worker_company_last_yearly'] += round($val['total_mhrs'], 2);
                    $total += round($val['total'], 2);
                    if ($val['id'] == $emp_id) {
                        $data['worker_last_yearly'] = round($val['total_mhrs'], 2);
                        $data['worker_avg_last_yearly'] = round($val['new_avg'], 2);
                    }

                }
                if ($total && $data['worker_company_last_yearly'])
                    $data['worker_avg_company_last_yearly'] = round($total / $data['worker_company_last_yearly'], 2);
            }
            //End Last Yearly

            //echo '<pre>'; var_dump($data['mhrs']); die;
        }

        return $data;
    }

    private function ajax_response($data)
    {
        echo json_encode($data);
        return false;
    }

    public function geoDistance($lat1, $lon1, $lat2, $lon2)
    {
        if (
            $lat1 == false ||
            $lon1 == false ||
            $lat2 == false ||
            $lon2 == false
        ) {
            return 0;
        }

        // Earth's radius in metres (mean radius = 6,371km)
        $radius = 6371e3;
        $pi = M_PI;

        // Angles need to be radians to pass trig functions!
        $lat1Radian = ($lat1 * $pi) / 180;
        $lat2Radian = ($lat2 * $pi) / 180;
        $latDelta = (($lat2 - $lat1) * $pi) / 180;
        $longDelta = (($lon2 - $lon1) * $pi) / 180;

        // The square of half the chord length between the points
        $square = sin($latDelta / 2) * sin($latDelta / 2) +
            cos($lat1Radian) *
            cos($lat2Radian) *
            sin($longDelta / 2) *
            sin($longDelta / 2);

        // Angular distance in radians
        $radians = 2 * atan2(sqrt($square), sqrt(1 - $square));

        // Distance is the radius * angular distance
        $distance = $radius * $radians;

        return $distance <= 50 ? 1 : 0;
    }
}

//end of file dashboard.php
