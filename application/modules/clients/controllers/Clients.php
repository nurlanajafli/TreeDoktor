<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

use application\modules\qb\models\QbLogs as QbLogsModel;
use application\modules\brands\models\Brand;
use application\modules\clients\models\ClientsContact;
use application\modules\clients\models\Tag;
use application\modules\clients\models\Client;
use application\modules\clients\models\ClientNote;
use application\modules\estimates\models\Estimate;
use application\modules\user\models\User;
use application\modules\references\models\Reference;
use application\modules\tree_inventory\models\TreeInventoryScheme;

use Illuminate\Http\JsonResponse;
use application\modules\clients\models\ClientLetter;
use \application\modules\dashboard\models\Search;
use application\modules\leads\models\Lead;

class Clients extends MX_Controller
{
//*******************************************************************************************************************
//*************
//*************
//*************											Clients Controller
//*************ajax_delete_tag
//*************
//*******************************************************************************************************************	
    function __construct()
    {
        //echo "<pre>";
        //var_dump($this->db->where('service_id', 9)->get('services')->result(true, 'services'));
        //die;
        parent::__construct();
        if (!isUserLoggedIn()) {
            redirect('login');
        }
        if (is_cl_permission_none()) {
            redirect('dashboard');
            return;
        }
        $this->_title = SITE_NAME;

        $this->load->helper('leads');
        //load all common models and libraries here;
        $this->load->model('mdl_clients', 'mdl_clients');
        $this->load->model('mdl_leads', 'mdl_leads');
        $this->load->model('mdl_client_tasks');
        $this->load->model('mdl_estimates', 'mdl_estimates');
        $this->load->model('mdl_workorders', 'mdl_workorders');
        $this->load->model('mdl_invoices', 'mdl_invoices');
        $this->load->model('mdl_invoice_status');
        $this->load->model('mdl_letter', 'mdl_letter');
        $this->load->model('mdl_script', 'mdl_script');
        $this->load->model('mdl_categories', 'mdl_categories');
        $this->load->model('mdl_calls');
        $this->load->model('mdl_settings_orm');

        $this->load->model('mdl_sms_messages');
        $this->load->model('mdl_user', 'mdl_users');


        //Load Google Map Library
        $this->load->library('googlemaps');

        $this->load->library('form_validation');
        $this->load->library('pagination');
        $this->load->library('Common/EstimateActions');
        $this->load->library('Common/LeadsActions');
        $this->load->library('Common/WorkorderActions');
        $this->load->library('Common/InvoiceActions');
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
        Client::permissions();

        if (is_cl_permission_none()) {
            redirect('dashboard');
        }

        if (request()->ajax() || request()->input('csv')) {
            $this->ajax_search_clients();
            return;
        }
        // Set title
        $data['title'] = "Clients";
        // Set menu status
        $data['menu_clients'] = "active";

        $data['access_token'] = $this->mdl_settings_orm->get_by('stt_key_name', 'accessTokenKey')->stt_key_value;
        $data['select2Tags'] = Tag::select2FormatData();
        $data['tagsExpandLimit'] = Tag::TAGS_EXPAND_LIMIT;

        $data['estimators'] = [];
        $estimators = $this->mdl_estimates->get_active_estimators();
        if ($estimators)
            $data['estimators'] = $estimators;

        $this->load->view('clients/index', $data);
    }// End Index

    private function  ajax_search_clients()
    {
        $request = request();
        $request->request->add(['client_maker' => get_user_cl_module_status()]);

        $orderColumnTargetIndex = $request->order[0]['column'];
        $orderColumnName = $request->columns[$orderColumnTargetIndex]['name'];
        $orderDir = $request->order[0]['dir'];

        $clientQuery = Client::with( 'tags')->orderBy($orderColumnName, $orderDir);
        $clientQuery->filterClient($request, 'clients.client_id');

        $clients_ids = $clientQuery->offset($request->start)->permissions()->limit($request->length)->pluck('client_id')->toArray();
//        $clients_ids = $clientQuery->permissions()->pluck('client_id')->toArray();

        if(!count($clients_ids))
            return $this->response(['data' => (new JsonResponse([])), 'recordsTotal' => 0, 'recordsFiltered' => 0]);

        $calcQuery = false;
        if(count($clients_ids))
            $calcQuery = $this->mdl_estimates_orm->calcQuery(count($clients_ids)?['estimates.client_id'=>$clients_ids]:[]);

        $clientQuery = Client::with( 'tags')->orderBy($orderColumnName, $orderDir);
        $clientQuery->permissions()->filterClient($request, false, $calcQuery);

        $totalQueryClients = Client::countAggregate($clientQuery);

        if(!$request->input('csv')) {
            $clientQuery->offset($request->start)
                ->limit($request->length);
        }

        $clients = $clientQuery->get();

        $access_token = config_item('accessTokenKey');
        $clients->map(function($client) use ($access_token) {
            $client->cc_phone_config_status = 0;
            if (!empty($client->cc_phone) && $client->cc_phone == numberTo($client->cc_phone)) {
                $client->cc_phone_config_status = 1;
            }
            $client->cc_phone_masked = numberTo($client->cc_phone);
            $client->qb_html = $this->load->view('qb/partials/qb_logs', ['lastQbTimeLog' => $client->client_last_qb_time_log, 'lastQbSyncResult' => $client->client_last_qb_sync_result, 'module' => 'client', 'entityId' => $client->client_id, 'entityQbId' => $client->client_qb_id, 'class' => 'pull-right m-right-10', 'access_token'=> $access_token], true);
        });

        if($request->input('csv') && isAdmin()) {
            return $this->_csv($clients);
        }

        return $this->response([
            'data' => (new JsonResponse($clients)),
            'recordsTotal' => $totalQueryClients,
            'recordsFiltered' => $totalQueryClients,
        ]);
    }

    private function _csv(\Illuminate\Database\Eloquent\Collection $clients) {
        $headers = [
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Content-type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename=clients.csv',
            'Expires'             => '0',
            'Pragma'              => 'public'
        ];

        $csvColumns = [
            'client_id' => 'ID',
            'client_name' => 'Name',
            'client_address' => 'Address',
            'client_city' => 'City',
            'client_state' => 'State / Province',
            'client_zip' => 'Postal Code / Zip',
            'cc_name' => 'Contact Person',
            'cc_phone' => 'Phone',
            'cc_email' => 'Email',
            'client_date_created' => 'Date Created',
            'tags' => 'Tags'
        ];

        $csvData = [];

        foreach ($clients as $k => $clientRow) {
            foreach ($csvColumns as $key => $val) {
                if($key === 'tags') {
                    $clientRow[$key] = (isset($clientRow[$key]) && $clientRow[$key]) ? implode('|', $clientRow[$key]->pluck('name')->toArray()) : null;
                }
                $csvData[$k][$val] = $clientRow[$key];
            }
        }

        foreach ($headers as $key => $header) {
            header("$key: $header");
        }

        array_unshift($csvData, array_keys($csvData[0]));

        $FH = fopen('php://output', 'w');
        foreach ($csvData as $key => $row) {
            fputcsv($FH, $row);
        }

        echo stream_get_contents($FH);
        fclose($FH);
    }

//*******************************************************************************************************************
//*************
//*************
//*************																						Details function;
//*************
//*************
//*******************************************************************************************************************
    public function details($client_id = null)
    {
        //Presets - Checking if variable $client_id exists;
        if (!isset($client_id)) {
            //variable was not set -> redirect to general clients view.
            redirect('dashboard');


        } else {

            //Setting $id for master models.
            $id = strip_tags($client_id);

            //Checking if a record for the requested variable exists.
            if ($this->mdl_clients->check_by_id($id) == FALSE) {
                //No record exists -> redirect to general clients view.
                redirect('dashboard');
            } else {

                // End check_by_id. recoders exists ->retrive information.
                $data['title'] = $this->_title . " - Client Details";
                $data['menu_clients'] = "active";
                
                $data['brands'] = Brand::withTrashed()->get();

                $this->load->model('mdl_leads_status');

                $data['lead_statuses'] = $this->mdl_leads_status->with('mdl_leads_reason')->get_all();
                //Authorised file: - Check if user is authorised to see the info:
                if (is_cl_permission_owner()) {

                    //Get current user ID:
                    $user_id = request()->user()->id;

                    //Check if the there is match:
                    $wdata = array('client_id' => $id);
                    $query = Client::where('clients.client_id', $id)->permissions()->first();

                    if (empty($query)) {
                        show_404(); // No match
                    }

                }

                $this->load->model('mdl_voices');
                $this->load->model('mdl_sms');

                $data['estimators'] = $this->mdl_users->get_usermeta(array('active_status' => 'yes','emp_status' => 'current', 'users.id <>' => 0, 'emp_field_estimator'=>'1'))->result();
                $users = User::with('employee')->active()->noSystem()->estimator()->get();
                $active_users = User::get_service_tags($users);
                $data['estimatorsList'] = json_encode($active_users  ?? []);

                //End Authorised File:
                $data['client_data'] = $this->mdl_clients->get_client_by_id($id); //Get client details

                $data['client_contacts'] = $this->mdl_clients->get_client_contacts(array('cc_client_id' => $client_id)); //Get client contacts


                $ClientModel = Client::with('tags')->find($client_id);
                $data['client_tags'] = $ClientModel->tags->map(function($tag) {
                    return [ 'id'=>$tag->tag_id, 'text'=>$tag->name];
                });

                $data['client_contact'] = $this->mdl_clients->get_primary_client_contact($client_id); //Get client contacts

                $client_id = $data['client_data']->client_id;
                $name = $data['client_data']->client_name;
                $street = $data['client_data']->client_address;
                $city = $data['client_data']->client_city;
                $state = $data['client_data']->client_state;
                $country = $data['client_data']->client_country;
                $address = $street . "+" . $city . "+" . $state . "+" . $country;
                $lat = $data['client_data']->client_lat;
                $lng = $data['client_data']->client_lng;

                // data form select in new_lead_modal
                $data['default_client_selected_address'] = [
                    'id' => $street,
                    'text' => $street . ', ' . $city,
                    'newAddress' => [
                        'new_address' => $street,
                        'new_city' => $city,
                        'new_state' => $state,
                        'new_zip' => $data['client_data']->client_zip,
                        'new_country' => $country,
                        'stump_add_info' => $data['client_data']->client_main_intersection,
                        'new_lat' => $lat,
                        'new_lon' => $lng,
                    ]
                ];

                $brand_id = get_brand_id([], $data['client_data']);

                //$letters = $this->mdl_letter->get_all(['email_news_templates' => NULL, 'system_label' => NULL]);
                $this->load->helper('user');
                $data['letters'] = ClientLetter::where(['email_news_templates' => NULL, 'system_label' => NULL])
                    ->get()
                    ->map(function($item) use ($data, &$brand_id) {
                        $brand_array = array(
                            '<span class="_var_cc_name">' . $data['client_data']->cc_name . '</span>',
                            brand_name($brand_id),
                            brand_email($brand_id),
                            brand_phone($brand_id),

                            brand_address($brand_id),
                            client_address($data['client_data']),

                            brand_name($brand_id, true),
                            brand_site($brand_id),
                            brand_team_signature($brand_id),
                            brand_email($brand_id)
                        );

                        $item->email_template_title = str_replace(
                            ClientLetter::CLIENT_LETTER_KEYWORDS,
                            $brand_array,
                            $item->email_template_title
                        );
                        return $item;
                    });

                $data['blocks'] = $this->config->item('leads_services');
                $data['fromQ'] = NULL;
                $data['toQ'] = NULL;

                foreach ($data['client_contacts'] as $key => $contact) {
                    if (strpos($contact['cc_email'], '@') !== FALSE) {
                        $data['fromQ'] .= 'from:' . strtolower(trim($contact['cc_email'])) . ' OR ';
                        $data['toQ'] .= '(from:me to:' . strtolower(trim($contact['cc_email'])) . ') OR (cc:me to:' . strtolower(trim($contact['cc_email'])) . ') OR ';
                    }
                }
                $data['fromQ'] = rtrim($data['fromQ'], ' OR ');
                $data['toQ'] = rtrim($data['toQ'], ' OR ');

                //Set the map:
                $center = ($lat && $lng) ? ($lat . ', ' . $lng) : config_item('map_center');
                $config['center'] = $center;

                $config['zoom'] = '10';

                $this->googlemaps->initialize($config);

                $marker = array();
                $marker['position'] = $address;
                $marker['icon'] = mappin_svg('#FD7567', '&#9899;', FALSE, '#000');
                $this->googlemaps->add_marker($marker);
                $data['address'] = $address;
                $data['map'] = false;// $this->googlemaps->create_map();
                $data['tasks'] = $this->mdl_client_tasks->get_all(array('task_client_id' => $id), FALSE, 'client_tasks.task_date_created DESC');

                $data['task_categories'] = $this->mdl_categories->get_all('category_active = 1');
                $data['client_leads'] = $this->mdl_leads->get_client_leads($id); //Get client lead

                $this->load->model('mdl_client_tasks');
                $data['appointments'] = [];

                $this->load->helper('user_tasks');
                $data['schedule_appointments'] = delete_special($this->mdl_client_tasks->get_all(['task_client_id' => $id]));

                $this->load->model('mdl_services');
				$this->load->model('mdl_leads_services');

                $services = $this->mdl_services->get_service_tags();

				if(!empty($services))
				{
                    if ($data['client_leads']->num_rows())
					{
						foreach($data['client_leads']->result_array() as $key=>$val)
						{
							$data['est_services'][$val['lead_id']] = '';
							$est_services  = $this->mdl_leads_services->get_many_by(['lead_id' => $val['lead_id']]);
							foreach($est_services as $k=>$v)
								$data['est_services'][$val['lead_id']] .= $v->services_id . '|';
							$data['est_services'][$val['lead_id']] = trim($data['est_services'][$val['lead_id']], '|');
						}
					}
				}

				$data['services'] = json_encode($services['serviceTags'] ?? []);
				$data['products'] = json_encode($services['productTags'] ?? []) ;
				$data['bundles'] = json_encode($services['bundleTags'] ?? []) ;

				$reffered_leads = $this->mdl_leads->get_leads(array('lead_reffered_client' => $id), '');

                $data['reffered_leads'] = $reffered_leads ? $reffered_leads->result() : NULL;

                $data['active_users'] = $this->mdl_users->get_usermeta(array('active_status' => 'yes1','emp_status' => 'current', 'users.id <>' => 0))->result();

				$data['client_estimates'] = $this->mdl_estimates->get_client_estimates($id); //Get client estimates
                $data['total_estimates_sum'] = $this->mdl_estimates->get_total_for_estimate_by(array('estimates.client_id' => $id));
                $data['total_confirmed_estimates_sum'] = $this->mdl_estimates->get_total_for_estimate_by(array('estimates.client_id' => $id, 'estimates.status' => 6));
                $data['client_payments'] = $this->mdl_clients->get_payments(array('clients.client_id' => $client_id)); //Get client payments

                $clientFilesStatuses = Lead::CLIENT_FILES_STATUSES;

                foreach ($clientFilesStatuses as $key => $statusData) {
                    $data['client_files_statuses'][$key] = $statusData;
                    $data['client_files_statuses'][$key]['count'] = Lead::countLeadClientFiles($client_id, $statusData['name']);
                }

                $data['voices'] = array();
                $voices = $this->mdl_voices->get_all();

                if ($voices && !empty($voices)) {
                    $data['voices'] = $voices;
                }

                $data['messages'] = array();
                $sms = $this->mdl_sms->get_all();

                if ($sms && (is_object($sms) || !empty($sms))) {
                    $data['messages'] = $sms;
                }

                $data['client_papers'] = $this->mdl_clients->get_papers(['cp_client_id' => $id], 'cp_id DESC');
                $data['access_token'] = $this->mdl_settings_orm->get_by('stt_key_name', 'accessTokenKey')->stt_key_value;

                $this->load->view('clients/details', $data);
            }
            //end of retrive information.
        }
        //end of checking if variable $client_id exists;
    } // end Details();

    function clients_mapper()
    {
        $data['title'] = $this->_title . ' - Clients';

        //Set the map:
        $config['center'] = config_item('map_center');
        $config['zoom'] = 'auto';
        $config['cluster'] = TRUE;
        $this->googlemaps->initialize($config);

        $clients = $this->mdl_clients->get_clients_with_coords();

        foreach ($clients as $key => $client) {
            $json[$key]['client_id'] = $client['client_id'];
            $json[$key]['client_date'] = $client['client_date_created'];
            $json[$key]['name'] = $client['client_name'];
            $json[$key]['phone'] = $client['cc_phone'];
            $json[$key]['address'] = $client['client_address'] . ', ' . $client['client_city'];
            $json[$key]['lat'] = $client['latitude'];
            $json[$key]['lon'] = $client['longitude'];
        }

        $data['json'] = json_encode($json);

        //Creating the markers for leads:

        $this->load->view('index_map', $data);
    }


//*******************************************************************************************************************
//*************
//*************
//*************																								Add note;
//*************
//*************
//*******************************************************************************************************************
    public function add_note()
    {

        //Get submitted values.
        $client_id = $this->input->post("client_id");
        $note_data['client_id'] = $client_id;
        $note_data['author'] = $this->input->post("author");
        $note_data['robot'] = $this->input->post("robot");
        $note_data['client_note'] = strip_tags($this->input->post('new_note'));
        $note_data['client_note_date'] = date('Y-m-d H:i:s');
        $note_data['client_note_type'] = 'info';
        $note_data['lead_id'] = intval($this->input->post("lead_id")) ? intval($this->input->post("lead_id")) : NULL;
        if (isset($_FILES['file']) && !$_FILES['file']['error']) {
            $note_data['client_note_type'] = 'attachment';
            $this->form_validation->set_rules('new_note', 'New note', 'trim|strip_tags');
        } else
            $this->form_validation->set_rules('new_note', 'New note', 'trim|required|strip_tags');

        if ($this->form_validation->run() == FALSE) {

            //Validation failed. No note has been added.
            $link = $_SERVER['HTTP_REFERER'] ?? '/' . $client_id;
            $mess = message('alert', '&nbsp; Notes cannot be empty!');
            $this->session->set_flashdata('user_message', $mess);
            redirect($link);
        } else {
            //Validation Passed. Posting note to database.
            //Checking if anything was recorded.
            $id = ClientNote::createNote($note_data);

            if ($id) {
                if (isset($_FILES['file']) && !$_FILES['file']['error']) {
                    $dir = 'notes_files/' . $client_id . '/' . $id . '/';
                    $explode = explode(".", $_FILES['file']['name']);
                    $name = str_replace('.' . $explode[count($explode) - 1], '', $_FILES['file']['name']);//countOk
                    $this->mdl_clients->uploadFile($dir, $name, 'file', '*');
                }
                //Record was inserted to db. Redirecting with success message.
                $link = ($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '/' . $client_id;
                $mess = message('success', '<strong>Success !</strong>&nbsp;Note added!');
                $this->session->set_flashdata('user_message', $mess);
                redirect($link);
            } else {

                //Validation failed. No note has been added.
                $link = ($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '/' . $client_id;;
                $mess = message('alert', '<strong>Failed !</strong>&nbsp;Note adding failed!');
                $this->session->set_flashdata('user_message', $mess);
                redirect($link);
            }
        }
    }//end add_notes();

//*******************************************************************************************************************
//*************
//*************
//*************																								Delete note;
//*************
//*************
//*******************************************************************************************************************

    public function delete_note($client_note_id)
    {

        if (request()->user()->user_type != "admin") {
            show_404();
        } else {

            //Get Client_id:
            $client_id = $this->mdl_clients->delete_note_client($client_note_id)->client_id ?? null;

            If ($this->mdl_clients->delete_note($client_note_id)) {


                $link = base_url($client_id);
                if ($_SERVER['HTTP_REFERER'])
                    $link = $_SERVER['HTTP_REFERER'];
                $mess = message('success', 'Client note was succefully deleted!');
                $this->session->set_flashdata('user_message', $mess);
                redirect($link);

            }
        }
    }

// End. delete_note();

//*******************************************************************************************************************
//*************
//*************
//*************																							Update Client;
//*************
//*************
//*******************************************************************************************************************

    public function update_client()
    {

        //Get variables
        $client_id = strip_tags($this->input->post('client_id'));
        $cl_data = $this->mdl_clients->get_client_by_id($client_id);
        $data['client_id'] = strip_tags($this->input->post('client_id'));
        $data['client_name'] = strip_tags($this->input->post('client_name'));
        $data['client_type'] = strip_tags($this->input->post('client_type'));

        // default tax
        $data['client_tax_name'] = null;
        $data['client_tax_rate'] = 1;
        $data['client_tax_value'] = 0;
        // client tax
        if (!empty($this->input->post('client_tax_name'))) {
            $data['client_tax_name'] = strip_tags($this->input->post('client_tax_name'));
            $data['client_tax_rate'] = floatval($this->input->post('client_tax_rate'));
            $data['client_tax_value'] = floatval($this->input->post('client_tax_value'));
        }

        //$data['client_status'] = strip_tags($this->input->post('client_status'));
        $data['client_unsubscribe'] = $this->input->post('client_unsubscribe');

        $wdata['client_id'] = $client_id;

        $changed_data_arr = $this->getListOfChanges($data, $client_id);
        if ($this->mdl_clients->update_client($data, $wdata)) {

            if (!empty($changed_data_arr)) {
                //Posting a note to the new client profile;
                //make_notes is a helper function defined at helper/notes_helper.php and the helper is autoloaded
                foreach ($changed_data_arr as $key => $val) {
                    $update_msg = ucfirst($key) . ' was modified from ' . $val['pre_data'] . ' to ' . $val['new_data'];
                    make_notes($client_id, $update_msg, 'system', 0);
                }

                //Record was inserted to db. Redirecting with success message.
                $mess = message('success', '<strong>Success!</strong>&nbsp;Client updated!');
                $this->session->set_flashdata('user_message', $mess);
                echo 'success';
                //create a new job for client synchronization in qb
                pushJob('quickbooks/client/syncclientinqb', serialize(['id' => $client_id, 'qbId' => $cl_data->client_qb_id]));
                exit;
            }

        } else {

            //Validation failed. No note has been added.
            $mess = message('alert', '<strong>Failed !</strong>&nbsp;Client update failed!');
            $this->session->set_flashdata('user_message', $mess);
            echo 'error';
            exit;
        }
    }

//Update client profile.

//*******************************************************************************************************************
//*************
//*************
//*************																								New Client;
//*************
//*************
//*******************************************************************************************************************

    public function new_client()
	{
		if (is_cl_permission_none()) {
			redirect('dashboard');
			return;
		}

        $this->load->model('mdl_services');

		$data['title'] = $this->_title;
		$data['menu_clients'] = "active";
		$data['brands'] = Brand::all();
        //$data['blocks'] = $this->config->item('leads_services');

		$data['contact_tpl'] = json_encode(['html' => $this->load->view('new_client_contact', ['number' => 0], TRUE)]);
        $services = $this->mdl_services->get_service_tags();

        $data['services'] = json_encode($services['serviceTags'] ?? []);
        $data['products'] = json_encode($services['productTags'] ?? []);
        $data['bundles'] = json_encode($services['bundleTags'] ?? []);

        $data['reference'] = Reference::getAllActive()->toArray();

        $data['allTaxes'] = get_all_taxes_with_client_tax();
        $data['taxText'] = $data['dataTaxText'] = 'System Default';

        $users = User::with('employee')->active()->noSystem()->estimator()->get();
        $active_users = User::get_service_tags($users);
        $data['estimatorsList'] = json_encode($active_users  ?? []);

		$this->load->view('clients/new', $data);
	}// End new-client


//*******************************************************************************************************************
//*************
//*************
//*************																					Add Client function;
//*************
//*************
//*******************************************************************************************************************
    public function add_client()
    {
        if (is_cl_permission_none()) {
            redirect('dashboard');
            return;
        }
        $this->load->model('mdl_client_tasks');
        $this->load->model('mdl_services');
        $this->load->model('mdl_user');

        //Validating form
        $primary_cc = 0;
        if($this->input->post('client_print'))
		  $primary_cc = $this->input->post('client_print')[0] - 1;

        $this->form_validation->set_rules('client_name['. $primary_cc .']', 'Client name #' . ($primary_cc + 1), 'trim|max_length[60]');

        if($this->input->post('client_phone') && $this->input->post('client_phone')[$primary_cc] == '' && $this->input->post('client_email')[$primary_cc] == '')
        {
			$this->form_validation->set_rules('client_phone['. $primary_cc .']', 'Client phone #' . ($primary_cc + 1), 'trim|required');
			$this->form_validation->set_rules('client_email['. $primary_cc .']', 'Client email #' . ($primary_cc + 1), 'required|trim');
		}

        $this->form_validation->set_rules('new_client_name', 'Client name', 'trim|required');
        $this->form_validation->set_rules('new_client_source', 'Client source', 'trim');
        $this->form_validation->set_rules('client_referred_by', 'Client Referred By', 'trim');
        $this->form_validation->set_rules('new_client_type', 'Client type', 'trim|required');
        $this->form_validation->set_rules('reffered', '"How did you hear about us?"', 'trim|required');

        $this->form_validation->set_rules('new_client_main_intersection', 'new_main_intersection', 'trim');
        $this->form_validation->set_rules('new_client_address', 'Client address', 'trim|required');
        $this->form_validation->set_rules('new_client_city', 'Client city', 'trim|required');
        $this->form_validation->set_rules('new_client_state', 'Client state', 'trim');
        $this->form_validation->set_rules('new_client_zip', 'Client zip', 'trim|required');
        $this->form_validation->set_rules('new_client_country', 'Client Country', 'trim');

        $this->form_validation->set_rules('client_address_check', 'client address check', 'trim');
        $this->form_validation->set_rules('new_client_main_intersection2', 'New Main intersection2', 'trim');

        /* if client_address is checked then make new_client_address2,new_client_city2,new_client_zip2 fields are required otherwise not required */
        if ($this->input->post('reffered') == 'user' || $this->input->post('reffered') == 'client') {
            $this->form_validation->set_rules('reff_id', 'Refferal', 'numeric|required');
        }

        if ($this->input->post('client_address_check') == 1) {
            $this->form_validation->set_rules('new_client_address2', 'Client address2', 'trim|required');
            $this->form_validation->set_rules('new_client_city2', 'Client city2', 'trim|required');
            $this->form_validation->set_rules('new_client_zip2', 'Client zip2', 'trim|required');
        } else {
            $this->form_validation->set_rules('new_client_address2', 'Client address2', 'trim');
            $this->form_validation->set_rules('new_client_city2', 'Client city2', 'trim');
            $this->form_validation->set_rules('new_client_zip2', 'Client zip2', 'trim');
        }

        $this->form_validation->set_rules('new_client_state2', 'Client state2', 'trim');

        $this->form_validation->set_rules('author', 'Author', 'trim');
        $this->form_validation->set_rules('new_client_lead', 'Client lead', 'trim');
        //Checkboxes:
        $this->form_validation->set_rules('check_tree_removal', 'check tree removal', '');
        $this->form_validation->set_rules('check_tree_pruning', 'check tree pruning', '');
        $this->form_validation->set_rules('check_stump_removal', 'check stump removal', '');
        $this->form_validation->set_rules('check_hedge_maintenance', 'check hedge maintenance', '');
        $this->form_validation->set_rules('check_shrub_maintenance', 'check shrub maintenance', '');
        $this->form_validation->set_rules('check_wood_disposal', 'check wood disposal', '');
        $this->form_validation->set_rules('check_arborist_report', 'check arborist report', '');
        $this->form_validation->set_rules('check_development', 'check development', '');
        $this->form_validation->set_rules('check_root_fertilizing', 'check root fertilizing', '');
        $this->form_validation->set_rules('check_tree_cabling', 'check tree cabling', '');
        $this->form_validation->set_rules('check_emergency', 'check emergency', '');
        $this->form_validation->set_rules('check_other', 'check other', '');

        $this->form_validation->set_rules('new_lead_timing', 'New Lead Timing', '');
        $this->form_validation->set_rules('new_lead_priority', 'New Lead Priority', 'trim|required');

        $this->form_validation->set_rules('scheduled_user_name', '', 'trim');

        // end Validation 
        $post = $data = $this->input->post(NULL, TRUE);

        $users = User::with('employee')->active()->noSystem()->estimator()->get();
        $active_users = User::get_service_tags($users);
        $data['estimatorsList'] = json_encode($active_users  ?? []);

        //$data['estimators'] = $estimatorsObj ? $estimatorsObj->result() : [];
        $data['client_tags'] = ($this->input->post('client_tags'))?json_decode($this->input->post('client_tags')):[];
        $client_tag_names = array_map(function ($item){
            return $item->text;
        }, $data['client_tags']);
        if(!$this->input->post('estimators') && $this->input->post('scheduled_user_id'))
            $data['estimators'] = $this->input->post('scheduled_user_id');
        else
            $data['estimators'] = $this->input->post('estimators');

        if ($this->form_validation->run() == FALSE) {
            // Problem with validation - return back;
            $data['title'] = $this->_title;
            $errors = validation_errors();
            $data['msg'] = FALSE;

            $data['blocks'] = $this->config->item('leads_services');
            $data['contact_tpl'] = json_encode(['html' => $this->load->view('new_client_contact', ['number' => 0], TRUE)]);
            if ($errors)
                $data['msg'] = message('alert', validation_errors());

            $data['intervals'] = [];
            $data['client_print'] = $primary_cc + 1;
            $data['intervals'] = $this->get_schedule_intervals(['task_author_id' => element('scheduled_user_id', $data, FALSE), 'task_date' => element('scheduled_date', $data, date("Y-m-d"))])['intervals'];


            $services = $this->mdl_services->get_service_tags();

            $data['services'] = json_encode($services['serviceTags'] ?? []);
            $data['products'] = json_encode($services['productTags'] ?? []) ;
            $data['bundles'] = json_encode($services['bundleTags'] ?? []) ;
            $data['brands'] = Brand::all();
            if($this->input->post('pre_uploaded_files') != null && array_key_exists(0, $this->input->post('pre_uploaded_files')) && count($this->input->post('pre_uploaded_files')[0]) > 0){
                foreach($this->input->post('pre_uploaded_files')[0] as $key=>$file){
                    if(!is_bucket_file($file)) //do not return it if this file does not exist anymore
                    unset($data['pre_uploaded_files'][0][$key]);
                }
            }

            switch ($this->input->post('reffered')) {
                case 'client':
                    $reffClient = $this->mdl_clients->get_reff_by_id($this->input->post('reff_id'));
                    $data['referer_full_name'] = $reffClient ? $reffClient->text : null;
                    break;
                case 'user':
                    $user = $this->mdl_user->getUserById($this->input->post('reff_id'));
                    $data['referer_full_name'] = trim($user[0]['firstname'] . ' ' . $user[0]['lastname']);
                default:
                    break;
            }
            $data['reference'] = Reference::getAllActive()->toArray();

            $allTaxes = all_taxes();
            $taxText = $dataTaxText = config_item('taxManagement');

            // check client tax is used
            if (
                $this->input->post('new_client_tax_name') &&
                $this->input->post('new_client_tax_rate') &&
                $this->input->post('new_client_tax_value')
            ) {
                $taxName = $this->input->post('new_client_tax_name') ;
                $taxRate = $this->input->post('new_client_tax_rate') ;
                $taxValue = $this->input->post('new_client_tax_value');
                $taxText = $dataTaxText = $taxName . ' (' . round($taxValue, 3) . '%)';
                $checkTax = checkTaxInAllTaxes($taxText);

                if (!$checkTax) {
                    $allTaxes[] = [
                        'id' => $taxText,
                        'text' => $taxText,
                        'name' => $taxName,
                        'rate' => $taxRate,
                        'value' => round($taxValue, 3)
                    ];
                }

                if ($taxName === 'Tax') {
                    $taxText = '(' . round($taxValue, 3) . '%)';
                }

                $data['addTaxName'] = $taxName;
                $data['addTaxRate'] = $taxRate;
                $data['addTaxValue'] = $taxValue;

                if ($taxValue === $this->input->post('us_tax_recommendation')) {
                    $data['usTaxRecommendation'] = $this->input->post('us_tax_recommendation');
                }
            }

            $data['allTaxes'] = $allTaxes;
            $data['taxText'] = $taxText;
            $data['dataTaxText'] = $dataTaxText;

            $this->load->view('clients/new', $data);
        } else {
            //Validation Passed;
            if ($client_id = $this->mdl_clients->add_new_client()) {

                Tag::syncTagsWithClient($client_tag_names, $client_id);

                foreach ($this->input->post('client_name') as $key => $value) {
                    if (!empty($this->input->post('client_name')[$key]) || !empty($this->input->post('client_phone')[$key]) || !empty($this->input->post('client_email')[$key])) {
						//echo '<pre>'; var_dump($this->input->post('client_print')[$key], $key); die;

                        if ($this->input->post('client_email')[$key] !== null && !empty($this->input->post('client_email')[$key])) {
                            $email = $this->input->post('client_email')[$key];
                            $email_exists = check_email_exists($email);
                        }

                        $contact_data['cc_client_id'] = $client_id;
                        $contact_data['cc_title'] = (isset($this->input->post('client_title')[$key]) && $this->input->post('client_title')[$key] != '') ? $this->input->post('client_title')[$key] : NULL;
                        $contact_data['cc_name'] = (isset($this->input->post('client_name')[$key]) && $this->input->post('client_name')[$key] != '') ? $this->input->post('client_name')[$key] : NULL;
                        $contact_data['cc_phone'] = (isset($this->input->post('client_phone')[$key]) && $this->input->post('client_phone')[$key] != '') ? numberFrom($this->input->post('client_phone')[$key]) : NULL;
                        $contact_data['cc_phone_clean'] = (isset($this->input->post('client_phone')[$key]) && $this->input->post('client_phone')[$key] != '') ? substr(numberFrom($this->input->post('client_phone')[$key]), 0, config_item('phone_clean_length')) : NULL;
                        $contact_data['cc_email'] = $email ?? null;
                        $contact_data['cc_email_check'] = $email_exists ?? null;
                        $contact_data['cc_print'] = (isset($this->input->post('client_print')[0]) && $this->input->post('client_print')[0] == $key+1) ? 1 : 0;
                        $this->mdl_clients->add_client_contact($contact_data);
                    }
                }
                //create a new job for client synchronization in qb
                pushJob('quickbooks/client/syncclientinqb', serialize(['id' => $client_id, 'qbId' => '']));
                //Posting a note to the new client profile;
                //make_note is an helper function
                //Generating lead;

                $this->load->model('mdl_leads_status');

                $defaultStatus = $this->mdl_leads_status->get_by(['lead_status_default' => 1]);
                $lead_data['lead_status_id'] = $defaultStatus->lead_status_id;
                //Checkboxes:
                $lead_data['lead_created_by'] = $this->session->userdata['firstname'] . " " . $this->session->userdata['lastname'];
                $lead_data['lead_date_created'] = date('Y-m-d H:i:s');

                $lead_data['lead_address'] = $this->input->post('new_address') ? $this->input->post('new_address', TRUE) : $this->input->post('new_client_address', TRUE);
                $lead_data['lead_city'] = $this->input->post('new_city') ? $this->input->post('new_city', TRUE) : $this->input->post('new_client_city', TRUE);
                $lead_data['lead_state'] = $this->input->post('new_state') ? $this->input->post('new_state', TRUE) : $this->input->post('new_client_state', TRUE);
                $lead_data['lead_zip'] = $this->input->post('new_zip') ? $this->input->post('new_zip', TRUE) : $this->input->post('new_client_zip', TRUE);
                $lead_data['lead_country'] = $this->input->post('new_country') ? $this->input->post('new_country', TRUE) : $this->input->post('new_client_country', TRUE);
                $lead_data['lead_add_info'] = $this->input->post('lead_add_info') ? $this->input->post('lead_add_info', TRUE) : $this->input->post('new_client_main_intersection', TRUE);

                $lead_data['lead_scheduled'] = $this->input->post('lead_scheduled') ? 1 : 0;
                $lead_data['lead_estimator'] = $this->input->post('estimators') ? $this->input->post('estimators') : NULL;
                $lead_data['lead_call'] = $this->input->post('lead_call') ? 1 : 0;
                $lead_data['lead_reffered_client'] = NULL;
                $lead_data['lead_reffered_user'] = NULL;
                $lead_data['lead_reffered_by'] = NULL;
                if ($this->input->post('reffered') != '') {
                    $reffered = $this->input->post('reffered');
                    if ($reffered == 'client') {
                        $lead_data['lead_reffered_by'] = $reffered;
                        $lead_data['lead_reffered_client'] = $this->input->post('reff_id');
                    } elseif ($reffered == 'user') {
                        $lead_data['lead_reffered_user'] = $this->input->post('reff_id');
                        $lead_data['lead_reffered_by'] = $reffered;
                    } elseif ($reffered == 'other')
                        $lead_data['lead_reffered_by'] = $this->input->post('other_comment');
                    else
                        $lead_data['lead_reffered_by'] = $reffered;
                }


                $lead_data['latitude'] = $this->input->post('new_lat') ? $this->input->post('new_lat') : $this->input->post('new_client_lat');
                $lead_data['longitude'] = $this->input->post('new_lon') ? $this->input->post('new_lon') : $this->input->post('new_client_lon');

                if(!$lead_data['latitude'] || !$lead_data['longitude'])
                {
                    $coords = get_lat_lon($lead_data['lead_address'], $lead_data['lead_city'], $lead_data['lead_state'], $lead_data['lead_zip']);
                    $lead_data['latitude'] = $coords['lat'];
                    $lead_data['longitude'] = $coords['lon'];
                }
                $lead_data['lead_neighborhood'] = get_neighborhood(['latitude' => $lead_data['latitude'], 'longitude' => $lead_data['longitude']]);

                $lead_data['preliminary_estimate'] = $this->input->post('preliminary_estimate') !== false ? $this->input->post('preliminary_estimate') : null;
                $lead_data['timing'] = strip_tags($this->input->post('new_lead_timing'));
                $lead_data['lead_body'] = strip_tags($this->input->post('new_client_lead'));
                $lead_data['lead_priority'] = strip_tags($this->input->post('new_lead_priority'));
                $lead_data['lead_author_id'] = request()->user()->id;
                $lead_data['lead_estimator'] = $data['estimators'];
                $lead_data['client_id'] = $client_id;

                if (config_item('office_country') === 'United States of America' && $lead_data['lead_country'] === 'United States') {
                    if (!empty($this->input->post('us_tax_recommendation'))) {
                        $taxRecommendation = floatval($this->input->post('us_tax_recommendation'));
                        $lead_data['lead_tax_name'] = 'Tax';
                        $lead_data['lead_tax_rate'] = $taxRecommendation / 100 + 1;
                        $lead_data['lead_tax_value'] = $taxRecommendation;
                    }
                }

                $lead_id = $this->mdl_leads->insert_leads($lead_data);

                if ($lead_id) {

                    $this->load->model('mdl_leads_services');
                    $servicesEst = $this->input->post('est_services');
                    if(!empty($this->input->post('est_products')))
                        $servicesEst .= '|' . $this->input->post('est_products');
                    if(!empty( $this->input->post('est_bundles')))
                        $servicesEst .= '|' . $this->input->post('est_bundles');
                    if($servicesEst != '')
                    {
                        $services = explode('|', $servicesEst);
                        foreach($services as $k=>$v)
                            $this->mdl_leads_services->insert(['lead_id' => $lead_id, 'services_id' => intval($v)]);
                    }
                    /*-------------add appointments------------*/
                    $post['client_id'] = $client_id;
                    $post['lead_id'] = $lead_id;
                    $this->mdl_client_tasks->office_data($post);
                    /*-------------add appointments------------*/

                    $lead_no = str_pad($lead_id, 5, '0', STR_PAD_LEFT);
                    $lead_no = $lead_no . "-L";
                    $update_data = array("lead_no" => $lead_no);
                    $wdata = array("lead_id" => $lead_id);

                    $lead_no_updated = $this->mdl_leads->update_leads($update_data, $wdata);

                    //move files from tmp to the actual lead_id folder
                    if($this->input->post('pre_uploaded_files') != null && array_key_exists(0, $this->input->post('pre_uploaded_files')) && count($this->input->post('pre_uploaded_files')[0]) > 0){
                        foreach($this->input->post('pre_uploaded_files')[0] as $file){
                            $file_name = basename($file);
                            $new_path = 'uploads/clients_files/' . $client_id . '/leads/' . str_pad($lead_id, 5, '0', STR_PAD_LEFT) . '-L/' . str_replace('0-L', str_pad($lead_id, 5, '0', STR_PAD_LEFT) . '-L', $file_name);
                            bucket_copy($file, $new_path);
                            bucket_unlink($file);
                        }
                    }

                    if ($lead_no_updated) {

                        if (make_notes($client_id, 'Hey, I just created a new client.', 'system', 0)) {

                            //All done. All good. Redirecting with success message.
                            $link = base_url($client_id);
                            $is_create_estimate = $this->input->post('type');
                            if(!empty($is_create_estimate) && $is_create_estimate == 'estimate'){
                                $link = base_url('estimates/new_estimate/' . $lead_id);
                            }
                            $mess = message('success', 'Client Added!');
                            $this->session->set_flashdata('user_message', $mess);
                            redirect($link);

                        } else {

                            echo "Something went wrong. Terrible stuff. I'm so sorry";
                        }
                    } else {
                        echo "Something went wrong. Terrible stuff. I'm so sorry";
                    }

                } else {

                    echo "Something wen wrong. terrible stuff. Sorry";
                }
            } else {

                echo "Something wen wrong. terrible stuff. Sorry";
            }

        }
    }// End new-client

    function get_appointment_modal()
    {
        $result = [];
        $this->load->model('mdl_categories');

        $result['appointments'] = $this->get_appointment_data($this->input->post());
        $result['appointment_types'] = $this->mdl_categories->get_all('category_active = 1');
        if($this->input->post('id_client')){
            $leads = $this->mdl_leads->get_client_leads($this->input->post('id_client'), ['lead_statuses.lead_status_default' => 1]);
            if($leads->num_rows()){
                $result['leads'] = $leads->result_array();
            }
        }
        $result['lead_id'] = !empty($this->input->post('lead_id')) ? $this->input->post('lead_id') : '';
        // request from YS
        //array_unshift($result['appointment_types'], ['category_name' => 'Select task type', 'category_id' => '-1']);


        $result['view'] = $this->load->view('appointment/schedule_appointment_modal_content', $result['appointments'], true);

        return $this->response(['status' => 'ok', 'data' => $result]);
    }

    function get_schedule_intervals($data = [])
    {
        $this->load->helper('user_tasks');
        $intervals = $result = [];

        if (empty($data) && $this->input->post())
            $wdata = ['task_assigned_user' => $this->input->post('task_author_id'), 'task_date' => element('task_date', $this->input->post(), date("Y-m-d"))];
        else
            $wdata = ['task_assigned_user' => $data['task_author_id'], 'task_date' => element('task_date', $data, date("Y-m-d"))];

        if ($this->input->post('task_author_id')) {
            $result['task_author_id'] = $this->input->post('task_author_id');
            $intervals = $this->mdl_client_tasks->get_all($wdata);
        }

        if ($this->input->post())
            $result['appointments'] = $this->get_appointment_data($this->input->post());

        if (empty($intervals)) {
            $result['intervals'] = [['start' => strtotime($wdata['task_date'] . ' 07:00:00'), 'end' => strtotime($wdata['task_date'] . ' 19:00:00')]];

            if (!empty($data) && is_array($data))
                return $result;

            $result['view'] = $this->load->view('appointment/schedule_intervals', $result, true);
            echo json_encode(['status' => 'ok', 'data' => $result]);
            exit;
        }

        $result['intervals'] = get_free_intervals($wdata['task_date'], $intervals);
        if (!empty($data) && is_array($data))
            return $result;

        $result['view'] = $this->load->view('appointment/schedule_intervals', $result, true);


        echo json_encode(['status' => 'ok', 'data' => $result]);
        exit;
    }



    private function get_appointment_data($request)
    {
        $this->load->model('mdl_estimates_orm');
        $this->load->helper('user_tasks');
        $estimatorsObj = $this->mdl_users->get_usermeta(array('active_status' => 'yes','emp_status' => 'current', 'users.id <>' => 0, 'emp_field_estimator'=>'1'));
        $data['estimators'] = $estimatorsObj ? $estimatorsObj->result() : [];

        $data['origin_lat'] = config_item('office_lat');
        $data['origin_lon'] = config_item('office_lon');

        $data['appointment_address'] = element('appointment_address', $request, '');
        $data['appointment_lat'] = element('appointment_lat', $request, config_item('office_lat'));
        $data['appointment_lon'] = element('appointment_lon', $request, config_item('office_lon'));

        $this->load->model('mdl_client_tasks');
//        $app_date = date("Y-m-d", strtotime(element('task_date', $request, date("Y-m-d"))));
        $app_date = date("Y-m-d");
        if(isset($request['task_date'])){
            $taskDate = DateTime::createFromFormat(getDateFormat(), $request['task_date']);
            $app_date = $taskDate->format("Y-m-d");
        }


        $wdata = ['task_date'=>$app_date, 'employees.emp_field_estimator'=>'1', 'task_category <> ' => -1];
        if(isset($request['task_author_id']) && (int)$request['task_author_id'])
            $wdata['task_assigned_user'] = $request['task_author_id'];

        $data['schedule_appointments'] = delete_special($this->mdl_client_tasks->get_all($wdata, FALSE, 'task_end ASC'));

        $estimatorsObj = $this->mdl_users->get_usermeta(array('active_status' => 'yes','emp_status' => 'current', 'users.id <>' => 0, 'emp_field_estimator'=>'1', 'users.is_appointment'=>1));
        $data['estimators'] = $estimatorsObj ? $estimatorsObj->result() : [];

        foreach ($data['estimators'] as $key => $estimator) {
            $data['estimators'][$key]->photo = (element('picture', (array)$estimator, false) && is_bucket_file(str_replace('//','/', PICTURE_PATH.$estimator->picture)))?base_url(str_replace('//','/', PICTURE_PATH.$estimator->picture)):base_url(str_replace('//','/','assets/' . $this->config->item('company_dir') . '/pictures/avatar_default.jpg'));
        }

        $days_step = 4;
        if(date('w', strtotime($app_date)) > 2)
            $days_step = 5;

        $app_date_end = date('Y-m-d', strtotime($app_date.'+'.$days_step.' days'));
        $wdata = ['task_date >='=>$app_date, 'task_date <='=>$app_date_end];
        $appointments = $this->mdl_client_tasks->get_all($wdata, FALSE, 'task_end ASC');

        $data['appointment_recomendations'] = get_recomendations($data['estimators'], $appointments, $app_date);
        $data['appointment_recomendations'] = recomendations_set_priority($data['appointment_recomendations'], $this->input->post('schedule_lead_priority'));

        $client_point = ['appointment_lat'=>element('appointment_lat', $this->input->post(), config_item('office_lat')), 'appointment_lon'=>element('appointment_lon', $this->input->post(), config_item('office_lon'))];
        //get_absence
        $data['appointment_recomendations'] = recomendations_distance($data['appointment_recomendations'], $client_point);

        $previus_estimators = [];
        if($this->input->post('clients_ids') && strlen($this->input->post('clients_ids'))){
            $clients_ids = explode(',', $this->input->post('clients_ids'));
            foreach ($clients_ids as $key => $value) {
                $estimate = $this->mdl_estimates_orm->order_by('estimate_id')->get_by('client_id', $value);
                if(!empty($estimate))
                    $previus_estimators[] = $estimate->user_id;
            }
        }

        $data['appointment_recomendations'] = recomendations_previus($data['appointment_recomendations'], $previus_estimators);
        if($this->input->post('lead_preliminary_estimate')){
            $data['appointment_recomendations'] = recomendations_preliminary_estimate($data['appointment_recomendations'], $this->input->post('lead_preliminary_estimate'));
        }

        $this->load->model('mdl_schedule', 'mdl_schedule');

        $absence = $this->mdl_schedule->get_absence('absence_date >='.strtotime($app_date).' AND absence_date <='.strtotime($app_date_end).' AND (absence_employee_id<>0 OR absence_user_id<>0)');


        $data['appointment_recomendations'] = array_values(exclude_dayoff_estimators($data['appointment_recomendations'], $absence));
        $data['appointment_recomendations'] = array_values(group_by_estimator($data['appointment_recomendations']));

        return $data;
    }
//*******************************************************************************************************************
//*************
//*************
//*************									Function to get list of updated properties
//*************
//*************
//*******************************************************************************************************************

    function getListOfChanges($data, $client_id)
    {
        $changed_data_arr = array();
        //getting the previous data
        $client_pre_data = $this->mdl_clients->find_by_id($client_id);

        if ($client_pre_data->client_id != $data['client_id']) {
            $changed_data_arr['client id'] = array('pre_data' => $client_pre_data->client_id, 'new_data' => $data['client_id']);
        }

        if ($client_pre_data->client_name != $data['client_name']) {
            $changed_data_arr['client name'] = array('pre_data' => $client_pre_data->client_name, 'new_data' => $data['client_name']);
        }

        if ($client_pre_data->client_type != $data['client_type']) {
            $changed_data_arr['client type'] = array('pre_data' => $client_pre_data->client_type, 'new_data' => $data['client_type']);
        }

        if ($client_pre_data->client_tax_name != $data['client_tax_name']) {
            $changed_data_arr['client tax name'] = array('pre_data' => $client_pre_data->client_tax_name, 'new_data' => $data['client_tax_name']);
        }

        if ($client_pre_data->client_tax_rate != $data['client_tax_rate']) {
            $changed_data_arr['client tax rate'] = array('pre_data' => $client_pre_data->client_tax_rate, 'new_data' => $data['client_tax_rate']);
        }

        if ($client_pre_data->client_tax_value != $data['client_tax_value']) {
            $changed_data_arr['client tax value'] = array('pre_data' => $client_pre_data->client_tax_value, 'new_data' => $data['client_tax_value']);
        }

        /*if ($client_pre_data->client_status != $data['client_status']) {
            $changed_data_arr['client status'] = array('pre_data' => $client_pre_data->client_status, 'new_data' => $data['client_status']);
        }

        if ($client_pre_data->client_main_intersection != $data['client_main_intersection']) {
            $changed_data_arr['client main intersection'] = array('pre_data' => $client_pre_data->client_main_intersection, 'new_data' => $data['client_main_intersection']);
        }

        if ($client_pre_data->client_address != $data['client_address']) {
            $changed_data_arr['client address'] = array('pre_data' => $client_pre_data->client_address, 'new_data' => $data['client_address']);
        }

        if ($client_pre_data->client_city != $data['client_city']) {
            $changed_data_arr['client city'] = array('pre_data' => $client_pre_data->client_city, 'new_data' => $data['client_city']);
        }

        if ($client_pre_data->client_state != $data['client_state']) {
            $changed_data_arr['client state'] = array('pre_data' => $client_pre_data->client_state, 'new_data' => $data['client_state']);
        }

        if ($client_pre_data->client_zip != $data['client_zip']) {
            $changed_data_arr['client zip'] = array('pre_data' => $client_pre_data->client_zip, 'new_data' => $data['client_zip']);
        }

        if ($client_pre_data->client_country != $data['client_country']) {
            $changed_data_arr['client country'] = array('pre_data' => $client_pre_data->client_country, 'new_data' => $data['client_country']);
        }*/

        return $changed_data_arr;

    }//End of the function getListOfChanges


//*******************************************************************************************************************
//*************
//*************
//*************												Ajax Search for clients;
//*************
//*************
//*******************************************************************************************************************

    function ajax_get_clients()
    {
        $return = $this->mdl_clients->search_clients();
        foreach ($return->result() as $rows):
            ?>
            <tr>
                <td><?php echo anchor('client/' . $rows->client_id, $rows->client_name); ?></td>
                <td><?php echo $rows->cc_phone; ?></td>
                <td><?php echo $rows->client_city; ?></td>
                <td>
                    <button class="btn btn-mini" type="button">Option</button>
                </td>
            </tr>
        <?php
        endforeach;
    } //End Ajax Search;

//*******************************************************************************************************************
//*************
//*************
//*************												Common functions bellow;
//*************
//*************
//*******************************************************************************************************************


    function _formatPhone($num)
    {

        $num = preg_replace('/[^0-9+()EXTENSIONextension]/', '', $num);

        $num = str_replace('+1', '', $num);
        $num = preg_replace('/[()EXTENSIONextension]/', '', $num);

        $len = strlen($num);
        if ($len == 7)
            $num = preg_replace('/([0-9]{3})([0-9]{4})/', '$1-$2', $num);
        elseif ($len == 10)
            $num = preg_replace('/([0-9]{3})([0-9]{3})([0-9]{4})/', '$1.$2.$3', $num);
        elseif ($len > 10)
            $num = preg_replace('/([0-9]{3})([0-9]{3})([0-9]{4})([0-9])/', '$1.$2.$3 Ext. $4', $num);

        return $num;
    } // End _formatPhone;

    function ajax_get_billing_details()
    {
        $client_id = $this->input->post('client_id');
        $cards = [];

        if (!empty($client = Client::find($client_id))) {
            if ($client->client_payment_profile_id) {
                $this->load->library('Payment/ArboStarProcessing', null, 'arboStarProcessing');

                try {
                    $cards = $this->arboStarProcessing->profileCards($client->client_payment_profile_id, $client->client_payment_driver);
                }
                catch (PaymentException $e) {
                    return $this->response([
                        'status' => 'error',
                        'error' => $e->getMessage()
                    ]);
                }
            }
        }

        return $this->response([
            'status' => 'ok',
            'cards' => $cards,
            'html' => $this->load->view("partials/billing_details", ['cards' => $cards], TRUE)
        ]);
    }

    function ajax_save_billing()
    {
        $client_id = $this->input->post('client_id');

        if (!empty($client = Client::getWithContact($client_id))) {
            $this->load->library('Payment/ArboStarProcessing', null, 'arboStarProcessing');

            $billingData = [
                'customer_id' => $client->client_id,
                'name' => $client->client_name,
                'address' => $client->client_address,
                'city' => $client->client_city,
                'state' => $client->client_state,
                'zip' => $client->client_zip,
                'country' => $client->client_country,
                'phone' => $client->primary_contact->cc_phone_clean ?? null,
                'email' => $client->primary_contact->cc_email ?? null,
                'profile_id' => $client->client_payment_profile_id
            ];

            if ($client->client_payment_profile_id) {
                try {
                    $this->arboStarProcessing->profileAddCard(
                        $client->client_payment_profile_id,
                        $billingData,
                        $this->input->post('token'),
                        $this->input->post('crd_name'),
                        $this->input->post('additional'),
                        $client->client_payment_driver
                    );
                } catch (PaymentException $e) {
                    return $this->response([
                        'status' => 'error',
                        'error' => $e->getMessage()
                    ]);
                }
            } else {
                try {
                    $profile_id = $this->arboStarProcessing->createProfile(
                        $billingData,
                        $this->input->post('token'),
                        $this->input->post('crd_name'),
                        $this->input->post('additional'),
                        $client->client_payment_driver
                    );
                } catch (PaymentException $e) {
                    return $this->response([
                        'status' => 'error',
                        'error' => $e->getMessage()
                    ]);
                }

                $client->client_payment_profile_id = $profile_id;
                $client->client_payment_driver = $this->arboStarProcessing->getAdapter();
                $client->save();
            }

            try {
                $cards = $cards_upd = $this->arboStarProcessing->profileCards($client->client_payment_profile_id, $client->client_payment_driver);
            }
            catch (PaymentException $e) {
                $cards = $cards_upd = [];
            }

            if (!empty($cards_upd)) {
                foreach ($cards_upd as &$card){
                    $card['number'] = '['.$card['card_type'].'] '.$card['number'].' ('.$card['expiry_month'].'/'.$card['expiry_year'].')';
                }
            }

            return $this->response([
                'status' => 'ok',
                'cards' => $cards_upd,
                'html' => $this->load->view("partials/billing_details", ['cards' => $cards], TRUE)
            ]);
        }
    }

    function ajax_delete_billing()
    {
        $this->load->library('Payment/ArboStarProcessing', null, 'arboStarProcessing');

        $client_id = $this->input->post('id');
        $card_id = $this->input->post('card');

        if (empty($card_id)) {
            return $this->response([
                'status' => 'error',
                'error' => 'No card'
            ]);
        }

        if (empty($client = Client::find($client_id))) {
            return $this->response([
                'status' => 'error',
                'error' => 'Client not found'
            ]);
        }

        if (!$client->client_payment_profile_id) {
            return $this->response([
                'status' => 'error',
                'error' => 'Not found client payment profile'
            ]);
        }

        try {
            $this->arboStarProcessing->profileDeleteCard($client->client_payment_profile_id, $card_id, $client->client_payment_driver);
        } catch (PaymentException $e) {
            return $this->response([
                'status' => 'error',
                'error' => $e->getMessage()
            ]);
        }

        return $this->response(['status' => 'ok']);
    }

    function ajax_add_payment($isAjax = TRUE)
    {
        $payment_total = FALSE;
        $errors = array();
        $result = array();
        //File is Required
        if (request()->user()->user_type != "admin") {
            if ($_FILES['file']['error'] && !$this->input->post('payment_id') && !$this->input->post('payment_id') && $this->input->post('payment_amount')) {
                $errors['file'] = 'File is Required';
                $errors['status'] = 'error';
            } elseif ($_FILES['file']['tmp_name'] && !is_image($_FILES['file']['tmp_name']) && !is_pdf($_FILES['file']['tmp_name'])) {
                $errors['file'] = 'File must be image or PDF';
                $errors['status'] = 'error';
            }
        }

        $this->form_validation->set_error_delimiters('', '');
        $this->form_validation->set_rules('payment_method_int', 'Payment Method', 'trim|required');
        $this->form_validation->set_rules('estimate_id', 'Estimate', 'required|numeric');
        $this->form_validation->set_rules('payment_amount', 'Amount', 'required|trim');
        $this->form_validation->set_rules('payment_type', 'Payment Type', 'required|trim');
        $estimate = $this->mdl_estimates->find_by_id($this->input->post('estimate_id'));

        //var_dump($_POST, $this->mdl_estimates->get_total_estimate_balance($estimate->estimate_id)); die;
        if ($this->form_validation->run() === FALSE) {
            $errors['payment_method'] = form_error('payment_method_int');
            $errors['estimate_id'] = form_error('estimate_id');
            $errors['payment_amount'] = form_error('payment_amount');
            $errors['status'] = 'error';
        } else {
            $client = $this->mdl_clients->find_by_id($estimate->client_id);
            $brand_id = get_brand_id($estimate, $client);

            if (!isset($estimate->estimate_id) && !$estimate->estimate_id)
                $errors['estimate_id'] = 'Incorrect Estimate';
            $insert['payment_method_int'] = $this->input->post('payment_method_int');
            $insert['payment_type'] = $this->input->post('payment_type');
            $insert['payment_amount'] = getAmount($this->input->post('payment_amount'));
            $insert['estimate_id'] = $estimate->estimate_id;
            $insert['payment_date'] = $this->input->post('payment_date') ? strtotime($this->input->post('payment_date')) + 4000 : time();
            //author ID
            $insert['payment_author'] = request()->user()->id;

            if ($this->input->post('payment_id')) {
                if (!$this->input->post('payment_date'))
                    unset($insert['payment_date']);
                $changes = array();
                $payment = $this->mdl_clients->get_payments(array('payment_id' => $this->input->post('payment_id')));
                $payment = isset($payment[0]) ? $payment[0] : array();
                foreach ($insert as $key => $col) {
                    if ($payment[$key] != $col)
                        $changes[] = ucfirst(str_replace('_', ' ', $key)) . ' From: "' . $payment[$key] . '" To: "' . $col . '"';
                }
            }
        }
        if (!$this->input->post('payment_id') || ($this->input->post('payment_id') && !$_FILES['file']['error'])) {
            $insert['payment_file'] = $this->do_upload();
            //if(!$_FILES['file']['error'])
            //resizeImage('uploads/payment_files/' . $estimate->client_id . '/' . $estimate->estimate_no . '/' . $insert['payment_file']);
        }
        if ((!isset($insert['payment_file']) || !$insert['payment_file']) && !$this->input->post('payment_id'))
            $insert['payment_file'] = NULL;

        if (!empty($errors)) {
            if ($isAjax)
                die(json_encode($errors));
            else
                return $errors;
        }

        if ($this->input->post('payment_id')) {
            if (!isAdmin() && $this->session->userdata('PME') != 1)
                die(json_encode(array('status' => 'error')));
            if (isset($insert['payment_file']) && $insert['payment_file'])
                $changes[] = 'Payment File for ' . $estimate->estimate_no . ' From: "<a target="_blank" href="' . base_url('uploads/payment_files/' . $estimate->client_id . '/' . $estimate->estimate_no . '/' . $payment['payment_file']) . '">' . $payment['payment_file'] . '</a>" To: "<a target="_blank" href="' . base_url('uploads/payment_files/' . $estimate->client_id . '/' . $estimate->estimate_no . '/' . $insert['payment_file']) . '">' . $insert['payment_file'] . '</a>"';
            $this->mdl_clients->update_payment($this->input->post('payment_id'), $insert);
            $this->mdl_estimates->update_estimate_balance($estimate->estimate_id);//estimate balance

            if (!empty($changes)) {
                $list = NULL;
                foreach ($changes as $str)
                    $list .= '<li>' . $str . '</li>';
                make_notes($estimate->client_id, 'Payment Transaction ID for ' . $estimate->estimate_no . ': "' . $this->input->post('payment_id') . '" changed: <ul>' . $list . '</ul>', 'system', $estimate->lead_id);
                $letter = "Payment Transaction ID (" . $this->input->post('payment_id') . ") for " . base_url('client/' . $estimate->client_id) . "' changed: <ul>" . $list . "</ul><br> Payment was changed by: " . $this->session->userdata('firstname') . " " . $this->session->userdata('lastname');
                $this->load->library('email');

                $config = $this->config->item('smtp_mail');
                $config['mailtype'] = 'html';
                $this->email->initialize($config);
                $from = $config['smtp_user'];
                $to = $this->config->item('my_email');

                $this->email->to($to);
                $this->email->from($from, brand_name($brand_id));
                $this->email->subject('Change Payment');
                $this->email->message($letter);
                $this->email->send();

            }

        } else {
            if ($insert['payment_amount'])
                $id = $this->mdl_clients->insert_payment($insert);
            $this->mdl_estimates->update_estimate_balance($estimate->estimate_id);//estimate balance
            $invoice_data = $this->mdl_invoices->getEstimatedData($estimate->estimate_id);
            if ($invoice_data) {
                $oldStatus = $this->mdl_invoice_status->get_by(['invoice_status_id' => $invoice_data->in_status]);
                $newStatus = $this->mdl_invoice_status->get_by(['invoice_status_id' => 4]);
                if ($this->input->post('invoice_id')) {
                    $payment_total = TRUE;
                    $this->mdl_invoices->update_invoice(array('in_status' => 4), array('id' => $this->input->post('invoice_id')));
                    //thanks_letter_to_client($estimate->estimate_id);
                    $result['paid'] = 1;


                    make_notes($invoice_data->client_id, 'Status for invoice "' . $invoice_data->invoice_no . '" was modified from ' . $oldStatus->invoice_status_name . ' to ' . $newStatus->invoice_status_name, 'system', $estimate->lead_id);
                    $status = array('status_type' => 'invoice', 'status_item_id' => $this->input->post('invoice_id'), 'status_value' => 4, 'status_date' => time());
                    $this->mdl_invoices->status_log($status);
                } else {
                    if ($this->mdl_invoices->record_count(array(), array('estimate_id' => $estimate->estimate_id))) //if isset invoice for estimate
                    {
                        $total = $this->mdl_estimates->get_total_estimate_balance($estimate->estimate_id);
                        if ($total <= 0) {
                            $this->mdl_invoices->update_invoice(array('in_status' => 4), array('id' => $invoice_data->id));
                            $result['paid'] = 1;
                            //thanks_letter_to_client($estimate->estimate_id);
                            make_notes($invoice_data->client_id, 'Status for invoice "' . $invoice_data->invoice_no . '" was modified from ' . $oldStatus->invoice_status_name . ' to ' . $newStatus->invoice_status_name, 'system', $estimate->lead_id);
                            $insert = array('status_type' => 'invoice', 'status_item_id' => $invoice_data->id, 'status_value' => 4, 'status_date' => time());
                            $this->mdl_invoices->status_log($insert);
                        }
                    }
                }
            }

            if (!empty($id))
                make_notes($estimate->client_id, 'Payment for Estimate "' . $estimate->estimate_no . '" created. Transaction ID "' . $id . '".', 'system', $estimate->lead_id);
        }
        $result['client_unsubscribe'] = $this->input->post('client_unsubscribe') ? $this->input->post('client_unsubscribe') : NULL;
        $result['status'] = 'ok';
        if ($isAjax)
            die(json_encode($result));
        return $result;
    }


//    function ajax_edit_payment()
//    {
//        if (!isAdmin() && $this->session->userdata('PME') != 1)
//            die(json_encode(array('status' => 'error')));
//        $payment_id = $this->input->post('payment_id');
//        $payment = $this->mdl_clients->get_payments(array('payment_id' => $payment_id));
//        $payment[0]['payment_date'] = date('Y-m-d', $payment[0]['payment_date']);
//        die(json_encode($payment[0]));
//    }
//
//    function ajax_delete_payment()
//    {
//        if (!isAdmin())
//            die(json_encode(array('status' => 'error')));
//        $this->load->model('mdl_estimates');
//
//        $payment_id = $this->input->post('payment_id');
//        $payment_data = $this->mdl_clients->get_payments(array('payment_id' => $payment_id));
//        $sourcePath = 'uploads/payment_files/' . $payment_data[0]['client_id'] . '/' . $payment_data[0]['estimate_no'] . '/' . $payment_data[0]['payment_file'];
//
//        bucket_unlink($sourcePath);
//
//        $payment = $this->mdl_clients->delete_payment($payment_id);
//        if (isset($payment_data[0]))
//            $this->mdl_estimates->update_estimate_balance($payment_data[0]['estimate_id']);//update estimate balance
//        die(json_encode(array('status' => 'ok')));
//    }

    private function do_upload()
    {
        $path = 'uploads/payment_files/';
        $estimate_id = $this->input->post('estimate_id');
        $estimate = $this->mdl_estimates->find_by_id($estimate_id);
        if (empty($estimate))
            return FALSE;
        $path .= $estimate->client_id . '/';
        $path .= $estimate->estimate_no . '/';

        $ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
        $config['allowed_types'] = 'gif|jpg|jpeg|png|pdf';
        $config['overwrite'] = TRUE;
        $this->load->library('upload');
        $config['upload_path'] = $path;
        $files = bucketScanDir($path);
        $key = 1;
        if (!empty($files)) {
            sort($files, SORT_NATURAL);
            preg_match('/payment_([0-9]{1,})\..*?/is', $files[count($files) - 1], $num);//countOk
            $key = isset($num[1]) ? ($num[1] + 1) : 1;
        }
        $config['file_name'] = 'payment_' . $key . '.' . $ext;
        $this->upload->initialize($config);
        if (!$this->upload->do_upload('file'))
            return FALSE;
        $note = 'Add Payment File for ' . $estimate->estimate_no . ': <a href="' . base_url() . $path . $config['file_name'] . '">' . $config['file_name'] . '</a>';
        make_notes($estimate->client_id, $note, 'attachment', $estimate->lead_id);

        return $config['file_name'];
    }

    function ajax_check_contact()
    {
        $phone = $this->input->post('phone');
        $email = $this->input->post('email');

        if (!$phone && !$email) {
            $result['status'] = 'error';
            $result['msg'] = 'No data!';
            die(json_encode($result));
        }
        $result['status'] = 'ok';
        $users = $this->mdl_clients->check_contact($phone, $email);
        if (empty($users) || !$users)
            $result['users'] = 'Not Found!';
        else
            $result['users'] = $users;
        die(json_encode($result));
    }

    function ajax_check_address()
    {
        $street = $this->input->post('street');
        $city = $this->input->post('city');

        if (!$street && !$city) {
            $result['status'] = 'error';
            $result['msg'] = 'No data!';
            die(json_encode($result));
        }
        $result['status'] = 'ok';
        $users = $this->mdl_clients->check_address($street, $city);
        if (empty($users) || !$users)
            $result['users'] = 'Not Found!';
        else
            $result['users'] = $users;
        die(json_encode($result));
    }

    function ajax_client_removal()
    {
        if (request()->user()->user_type != "admin")
            die(json_encode(array('status' => 'error')));
        $client_id = $this->input->post('client_id');
        $password = md5($this->input->post('password'));
        $this->load->model('mdl_user');
        $user = $this->mdl_user->get_user('', array('id' => request()->user()->id, 'password' => $password));

        if (!$user || !$user->num_rows())
            die(json_encode(array('status' => 'error')));

        //create a new job for client synchronization in qb
        $cl_data = $this->mdl_clients->get_client_by_id($client_id);
        if(!empty($cl_data->client_qb_id))
            pushJob('quickbooks/client/syncclientinqb', serialize(['id' => $client_id, 'qbId' => $cl_data->client_qb_id]));

        $this->mdl_clients->complete_client_removal($client_id);
        //var_dump($this->db->last_query()); die;
        die(json_encode(array('status' => 'ok')));
    }

    function ajax_send_email()
    {
        $this->load->model('mdl_user');
        $this->load->library('email');
        $config['mailtype'] = 'html';
        $this->email->initialize($config);
        $client_id = $this->input->post('client_id');
        $estimate_id = intval($this->input->post('estimate'));
        $data = [];
        if($estimate_id)
            $data['estimate_data'] = Estimate::with(['user'])->find($estimate_id);

        if (!$client_id && isset($data['estimate_data']))
            $client_id = $data['estimate_data']->client_id;

        $data['client_data'] = Client::find($client_id);

        $brand_id = get_brand_id(isset($data['estimate_data'])?$data['estimate_data']->toArray():[], isset($data['client_data'])?$data['client_data']->toArray():[]);
        $note['to'] = $to = $this->input->post('email');
        $toEmails = explode(',', $this->input->post('email'));
        /* $note['to'] = $to = $toEmails[0];
        if(count($toEmails) > 1)
        {
            $note['cc'] = $cc = str_replace($toEmails[0].',', '', $this->input->post('email'));
            unset($toEmails[0]);
            $this->email->cc($cc);
        }*/
        $note['from'] = $from_email = $this->input->post('from_email');
        $estimate_id = $this->input->post('estimate');
        $callback = $this->input->post('callback');
        $callback_args = $this->input->post('callback_args');
        $note['subject'] = $subject = $this->input->post('subject');
        $sms = ($this->input->post('sms')) ? $this->input->post('sms') : 0;
        $check = check_receive_email($client_id, $to);


        if ($check['status'] != 'ok')
            die(json_encode(array('status' => $check['status'], 'message' => $check['message'])));

        if(count($toEmails) > 1) {
            foreach($toEmails as $key=>$val){
                if (!filter_var($val, FILTER_VALIDATE_EMAIL))
                    die(json_encode(array('status' => 'email', 'message' => 'Incorrect Email!')));
            }
        }
        else{
            if (!filter_var($to, FILTER_VALIDATE_EMAIL))
                die(json_encode(array('status' => 'email', 'message' => 'Incorrect Email!')));
        }


        $toDomain = substr(strrchr($to, "@"), 1);
        if (array_search($toDomain, $this->config->item('smtp_domains')) !== FALSE) {
            $config = $this->config->item('smtp_mail');
            $note['from'] = $from_email = $config['smtp_user'];
        }

        $from_email = ($from_email)?$from_email:brand_email($brand_id);

        $this->email->to($to);
        $this->email->from($from_email, brand_name($brand_id));
        $this->email->subject($subject);


        $text = $this->input->post('text');

        $userRow = $this->mdl_user->get_usermeta(array('users.id' => request()->user()->id));
        $user = $userRow ? $userRow->row_array() : [];

        $text .= '<br><div style="text-align:center; font-size: 10px;"> If you no longer wish to receive these emails you may ' .
            '<a href="' . $this->config->item('unsubscribe_link') . md5($client_id) . '">unsubscribe</a> at any time.</div>';

        if (isset($user['user_email'])) {
            if ($from_email == $user['user_email'] && isset($user['user_signature']))
                $text .= '<br>' . $user['user_signature'];

        }

        if ((!isset($user['user_email']) || ($from_email != $user['user_email'])) && $estimate_id) {
            if ($data['estimate_data'] && isset($data['estimate_data']->user->user_signature) && $data['estimate_data']->user->user_signature)
                $text .= '<br>' . $data['estimate_data']->user->user_signature;
        }
        $this->email->message($text);

        $send = $this->email->send();

        if (!is_array($send) || isset($send['error'])) {
            $error = 'Oops! Email send error. Please try again';

            if (isset($send['error'])) {
                $error = $send['error'];
            }

            die(json_encode(array('status' => 'error', 'message' => $error)));
        }

        $entities = [
            ['entity' => 'client', 'id' => $client_id]
        ];
        $this->email->setEmailEntities($entities);

        $name = uniqid();
        $note_id = make_notes(
            $client_id,
            'Sent email "' . $subject . '"',
            'email',
            0,
            $this->email
        );
        $dir = 'uploads/notes_files/' . $client_id . '/' . $note_id . '/';

        $pattern = "/<body>(.*?)<\/body>/is";
        preg_match($pattern, $text, $res);
        $note['text'] = isset($res[1]) && $res[1] ? $res[1] : $text;

        bucket_write_file($dir . $name . '.html', $this->load->view('note_file', $note, TRUE), ['ContentType' => 'text/html']);
        if ($callback && function_exists($callback)) {
            $callback($callback_args);
        }
        if ($sms) {
            $this->load->model('mdl_sms');
            $smsText = $this->mdl_sms->get($sms);
            if ($smsText && (is_object($smsText) || !empty($smsText)))
                die(json_encode(array('status' => 'ok', 'message' => 'Email sent. Thanks', 'text' => $smsText)));
        }
        die(json_encode(array('status' => 'ok', 'message' => 'Email sent. Thanks')));
    }

    function ajax_access_token()
    {
        require(APPPATH . 'libraries/Google/autoload.php');
        $client_email = $this->config->item('service_email_address');
        $private_key = file_get_contents($this->config->item('service_cert_path'));

        $client = new Google_Client();

        $scopes = array('https://www.googleapis.com/auth/gmail.readonly');
        $credentials = new Google_Auth_AssertionCredentials(
            $client_email,
            $scopes,
            $private_key
        );

        $credentials->sub = $this->config->item('account_email_address');

        $client->setAssertionCredentials($credentials);
        if ($client->getAuth()->isAccessTokenExpired()) {
            $client->getAuth()->refreshTokenWithAssertion();
        }
        die($client->getAccessToken());
    }

    function ajax_update_rating()
    {
        $client_id = $this->input->post('client_id');
        if (!$client_id)
            die(json_encode(array('status' => 'error')));
        $update['client_rating'] = $this->input->post('rating');
        $this->mdl_clients->update_client($update, array('client_id' => $client_id));
        die(json_encode(array('status' => 'ok')));
    }

    function ajax_top_note()
    {
        $note_id = $this->input->post('note_id');
        $note_top = intval($this->input->post('note_top')) ? intval($this->input->post('note_top')) : NULL;
        if (!$note_id)
            die(json_encode(array('status' => 'error')));
        $this->mdl_clients->update_note($note_id, array('client_note_top' => $note_top));
        die(json_encode(array('status' => 'ok')));
    }

    function attach_download($attachId = NULL, $messageId = NULL, $fileName = NULL)
    {

        if (!$attachId || !$messageId || !$fileName)
            show_404();
        require(APPPATH . 'libraries/Google/autoload.php');
        $client_email = $this->config->item('service_email_address');
        $private_key = file_get_contents($this->config->item('service_cert_path'));

        $mimes = array('jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'pdf' => 'application/pdf');

        $client = new Google_Client();
        $scopes = array('https://www.googleapis.com/auth/gmail.readonly');
        $credentials = new Google_Auth_AssertionCredentials(
            $client_email,
            $scopes,
            $private_key
        );
        $credentials->sub = $this->config->item('account_email_address');
        $client->setAssertionCredentials($credentials);
        if ($client->getAuth()->isAccessTokenExpired()) {
            $client->getAuth()->refreshTokenWithAssertion();
        }
        $serviceGmail = new Google_Service_Gmail($client);
        $messages = array();


        try {
            $messagesResponse = $serviceGmail->users_messages_attachments->get('me', $messageId, $attachId);
        } catch (apiServiceException $e) {
            show_404();
        } catch (Exception $е) {
            show_404();
        }

        $file_name = urldecode(base64_decode(str_replace(array('-', '_'), array('+', '/'), $fileName)));
        $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        if (isset($mimes[$ext])) {
            header('Content-disposition:inline; filename="' . $file_name . '"');
            header('Content-Type:' . $mimes[$ext]);
            die(base64_decode(str_replace(array('-', '_', '*'), array('+', '/', '='), $messagesResponse->data)));
        } else {
            $this->load->helper('download');
            force_download(urldecode(base64_decode(str_replace(array('-', '_'), array('+', '/'), $fileName))), base64_decode(str_replace(array('-', '_', '*'), array('+', '/', '='), $messagesResponse->data)));
        }
    }

    /***********************EMAIL TEMPLATES**************************/
    /*
    public function letters()
    {
        if ($this->session->userdata('user_type') != "admin" && $this->session->userdata('EM_TMP') != 1) {
            show_404();
        }
        $this->load->model('mdl_letter');
        $data['title'] = 'Letter Templates';
        $data['letters'] = $this->mdl_letter->get_all();
        $this->load->view('index_letters', $data);
    }*/

    function ajax_save_template()
    {
        if ($this->session->userdata('user_type') != "admin" && $this->session->userdata('EM_TMP') != 1) {
            show_404();
        }

        $this->load->model('mdl_letter');
        $id = $this->input->post('template_id');
        $data['email_template_title'] = strip_tags($this->input->post('template_name', TRUE));
        $data['email_template_text'] = $this->input->post('template_text');
        if ($this->input->post("email_user_notification") == 'true')
            $data['email_news_templates'] = 2;
        elseif ($this->input->post("news_templates") == 'true')
            $data['email_news_templates'] = 1;
        else
            $data['email_news_templates'] = NULL;

        if ($id != '') {
            $this->mdl_letter->update($id, $data);
            die(json_encode(array('status' => 'ok')));
        }
        $this->mdl_letter->insert_letter($data);
        die(json_encode(array('status' => 'ok')));
    }

    function ajax_delete_template()
    {
        if ($this->session->userdata('user_type') != "admin" && $this->session->userdata('EM_TMP') != 1) {
            show_404();
        }
        $this->load->model('mdl_letter');
        $id = $this->input->post('template_id');
        $this->mdl_letter->delete_letter($id);
        die(json_encode(array('status' => 'ok')));
    }
    /***********************END EMAIL TEMPLATES**********************/

    /***********************SCRIPTS**************************/

    function scripts()
    {
        if ($this->session->userdata('user_type') != "admin" && $this->session->userdata('SCR') != 1) {
            show_404();
        }
        $data['title'] = "Scripts";

        $data['scripts'] = $this->mdl_script->get_all();
        $this->load->view('index_scripts', $data);
    }

    function ajax_save_script()
    {
        if ($this->session->userdata('user_type') != "admin" && $this->session->userdata('SCR') != 1) {
            show_404();
        }

        $id = $this->input->post('id');
        $data['script_name'] = strip_tags($this->input->post('name', TRUE));
        $data['script_text'] = strip_tags($this->input->post('text', TRUE));

        if ($id != '') {
            $this->mdl_script->update_script($id, $data);
            die(json_encode(array('status' => 'ok')));
        }
        $this->mdl_script->insert_script($data);
        die(json_encode(array('status' => 'ok')));
    }

    function ajax_delete_script()
    {
        if ($this->session->userdata('user_type') != "admin" && $this->session->userdata('SCR') != 1) {
            show_404();
        }
        $id = $this->input->post('id');
        if ($id != '') {
            $this->mdl_script->delete_script($id);
            die(json_encode(array('status' => 'ok')));
        }
        die(json_encode(array('status' => 'error')));
    }


    function ajax_change_like()
    {
        $wdata['id'] = $this->input->post('id');
        $data['invoice_like'] = 0;
        if (!$this->input->post('val'))
            $data['invoice_like'] = 1;
        //var_dump($_POST); die;
        $this->mdl_invoices->update_invoice($data, $wdata);
        die(json_encode(array('status' => $data['invoice_like'])));
    }

    public function check_client_email()
    {
        $result['status'] = 'error';
        $email = $this->input->post('email');
        $id = (int) $this->input->post('id');

        $status = [
            'invalid',
            'ok',
            'unverifiable'
        ];

        $res = (int) check_email_exists($email);

        //$field = intval($this->input->post('field'));

        if (isset($id) && !empty($id)) {
            $this->mdl_clients->update_client_contact(array('cc_email_check' => $res), array('cc_client_id' => $id, 'cc_email' => $email));
        }

        $result['status'] = $status[$res];

        $this->response($result);
    }

    public function ajax_email_manual_approve()
    {
        $email          = $this->input->post('email') ?? null;
        $approve_status = $this->input->post('cc_approve_status') ?? 0;

        if (null === $email) {
            $this->response(['status' => false, 'reason' => 'Incorrect email address']);
        }

        $status = [true, false];

        if ($this->mdl_clients->update_client_contact(['cc_email_manual_approve' => (bool) $status[(int) $approve_status]], ['cc_email' => $email])) {
            $this->response([
                'status' => true,
                'data'   => [
                    'email'          => $email,
                    'approve_status' => (int) $status[$approve_status],
                ]
            ]);
        } else {
            $this->response(['status' => false, 'reason' => 'Failed to save']);
        }
    }

    /**
     * Deprecated!
     */
    function ajax_more_notes()
    {
        $result['status'] = 'error';
        $id = $this->input->post('id');
        $limit = $this->config->item('per_page_notes');
        $offset = intval($this->input->post('num'));
        $lead_id = $this->input->post('lead_id') ? $this->input->post('lead_id') : NULL;
        $data['client_data'] = $this->mdl_clients->get_client_by_id($id); //Get client details
        $data['client_info'] = $data['client_data'];
        $data['type'] = $type = $this->input->post('type');

        if ($data['type'] == 'calls') {
            $data['client_contacts'] = $this->mdl_clients->get_client_contacts(array('cc_client_id' => $id));
            $calls = [];
            $wh = '';

            foreach ($data['client_contacts'] as $k => $v) {
                if (isset($v['cc_phone']) && $v['cc_phone'])
                    $wh .= '(call_to LIKE "%' . $v['cc_phone'] . '%" OR call_from LIKE "%' . $v['cc_phone'] . '%") OR ';

            }
            $wh = rtrim($wh, ' OR ');
            if ($wh != '')
                $calls = $this->mdl_calls->get_calls($wh, $limit);

            //$data['client_notes'][$type . '_count'] = $offset;
            $data['client_notes'] = $calls;
            //var_dump($data['client_notes']); die;
            $result['offset'] = $offset + 200;
            if ($data['client_notes']) {
                $result['status'] = 'ok';
                $result['more'] = count($data['client_notes']) < $limit ? 0 : 1; //countOk
                $result['table'] = $this->load->view('call_sms_notes', $data, TRUE);
            }
            die(json_encode($result));
        } elseif ($data['type'] == 'sms') {
            $data['client_contacts'] = $this->mdl_clients->get_client_contacts(array('cc_client_id' => $id));
            //$data['client_notes'][$type . '_count'] = 0;
            $data['client_notes'] = [];
            //$data['client_notes'][$type . '_more'] = 0;
            foreach ($data['client_contacts'] as $k => $v) {
                $sms = $this->mdl_sms_messages->get_messages(array('sms_number' => $v['cc_phone']), $limit, $offset);
                //echo '<pre>'; var_dump($this->db->last_query()); die;
                //$data['client_notes'] = $offset;
                $data['client_notes'] += $sms;
                //$data['client_notes'][$type . '_more'] = count($data['client_notes'][$type]) < $offset ? 0 : 1;
            }
            $result['offset'] = $offset + 200;
            if ($data['client_notes']) {
                $result['status'] = 'ok';
                $result['more'] = count($data['client_notes']) < $limit ? 0 : 1; //countOk
                $result['table'] = $this->load->view('sms_notes', $data, TRUE);
            }
            //echo '<pre>'; var_dump($this->db->last_query()); die;
            die(json_encode($result));
        } elseif ($data['type'] == 'all_client_notes' || $data['type'] == 'all')
            $data['client_notes'][$data['type']] = $this->mdl_clients->get_notes($id, 'all', array(), $limit, $offset);
        elseif ($lead_id)
            $data['client_notes'][$data['type']] = $this->mdl_clients->get_notes($id, $data['type'], array('(lead_id = ' . $lead_id . ' OR lead_id IS NULL)' => NULL), $limit, $offset);
        else
            $data['client_notes'][$data['type']] = $this->mdl_clients->get_notes($id, $data['type'], array('lead_id IS NULL' => NULL), $limit, $offset);

        $result['offset'] = $offset + 200;
        if ($data['client_notes'][$data['type']]) {
            $result['status'] = 'ok';
            $result['more'] = count($data['client_notes'][$data['type']]) < $limit ? 0 : 1;//countOk
            $result['table'] = $this->load->view('notes_table', $data, TRUE);
        }

        die(json_encode($result));
    }

    /***********************END SCRIPTS**********************/

    public function ajax_get_reff()
    {
        $this->load->model('mdl_user');
        $reference = Reference::find($this->input->post('trigger'));
        $search = $this->input->post('name');
        $result['status'] = 'error';
        $result['items']  = [];

        if ($reference->getAttribute(Reference::ATTR_IS_USER_ACTIVE) == 1) {
            $result['items'] = $this->mdl_user->search_reff($search)->result_array();
            $result['length'] = count($result['items']);//countOk
            $result['status'] = 'ok';
        } elseif ($reference->getAttribute(Reference::ATTR_IS_CLIENT_ACTIVE) == 1) {

            $client = new Client();

            foreach ($client->searchReffClient($search)->get() as $item) {
                $result['items'][] = [
                    'client_brand_id' => $item->client_brand_id,
                    'id'              => $item->client_id,
                    'text'            => $item->client_name . ", " . $item->client_address . ", " . $item->client_city,
                ];
            }

            $result['length'] = count($result['items']);//countOk
            $result['status'] = 'ok';
        }

        $this->response($result);
    }

    public function voices()
    {
        if (request()->user()->user_type != "admin") {
            show_404();
        }
        $this->load->model('mdl_voices');
        $data['title'] = 'Voices Templates';
        $data['voices'] = $this->mdl_voices->get_all();
        $this->load->view('index_voices', $data);
    }

    function ajax_save_voice()
    {
        if (request()->user()->user_type != "admin") {
            show_404();
        }
        $this->load->model('mdl_voices');
        $id = $this->input->post('template_id');
        $data['voice_name'] = strip_tags($this->input->post('template_name', TRUE));
        $data['voice_resp'] = $this->input->post('template_text');
        if ($id != '') {
            $this->mdl_voices->update($id, $data);
            die(json_encode(array('status' => 'ok')));
        }
        $this->mdl_voices->insert($data);
        die(json_encode(array('status' => 'ok')));
    }

    function ajax_delete_voice()
    {
        if (request()->user()->user_type != "admin") {
            show_404();
        }
        $this->load->model('mdl_voices');
        $id = $this->input->post('template_id');
        $this->mdl_voices->delete($id);
        die(json_encode(array('status' => 'ok')));
    }

    public function sms()
    {
        if (!config_item('messenger'))
            show_404();
        $this->load->model('mdl_sms');
        $data['title'] = 'Sms Templates';
        $data['messages'] = $this->mdl_sms->get_all();
        $this->load->view('index_sms', $data);
    }

    function ajax_save_sms()
    {
        $this->load->model('mdl_sms');
        $id = $this->input->post('template_id');
        $data['sms_name'] = strip_tags($this->input->post('template_name', TRUE));
        $data['sms_text'] = $this->input->post('template_text');

        $data['user'] = $this->input->post("user") == 'true' ? 1 : NULL;
        if ($id != '') {
            $this->mdl_sms->update($id, $data);
            die(json_encode(array('status' => 'ok')));
        }
        $this->mdl_sms->insert($data);
        die(json_encode(array('status' => 'ok')));
    }

    function ajax_delete_sms()
    {
        $this->load->model('mdl_sms');
        $id = $this->input->post('template_id');
        $this->mdl_sms->delete($id);
        die(json_encode(array('status' => 'ok')));
    }

    function ajax_save_client_contact()
    {
        $cc_id = $this->input->post('pk');
        $info = $this->input->post('value');
        $phone_check = trim($info['cc_phone']);
		$email_check = trim($info['cc_email']);
        $name_check = trim($info['cc_name']);
        $approve_status = trim($info['cc_approve_status']);

        if($name_check == null || $name_check == ''){
            return $this->response('Name is required', 400);
        }

		if($phone_check == null || $phone_check == ''){
            $phone_to_save = null;
			$phone_to_save_clean = null;
            if($email_check == null || $email_check == ''){
                return $this->response('At least one of the fields: (phone or email) is required', 400);
            }
        } else {
            $phone_to_save = numberFrom($phone_check);
			$phone_to_save_clean = substr(numberFrom($phone_check), 0, config_item('phone_clean_length'));
        }

        if($email_check != null && $email_check != '' && !filter_var($email_check, FILTER_VALIDATE_EMAIL))
        {
            return $this->response('Invalid email', 400);
        }

        $data = [
            'cc_title' => $info['cc_title'],
            'cc_name' => $info['cc_name'],
            'cc_phone' => $phone_to_save,
            'cc_phone_clean' => $phone_to_save_clean,
            'cc_email' => $info['cc_email'],
            'cc_client_id' => $info['cc_client_id'],
            'cc_email_manual_approve' => $info['cc_approve_status'] ?? 0,
        ];
        if (!$data['cc_title'])
            $data['cc_title'] = 'Title';
        if ($cc_id) {
            $contact = $this->mdl_clients->get_client_contact(['cc_id' => $cc_id]);
            $wdata = ['cc_id' => $cc_id];

            if ($contact['cc_email'] != $email_check || $contact['cc_email_check'] === null)
                $data['cc_email_check'] = check_email_exists($email_check);

            $this->mdl_clients->update_client_contact($data, $wdata);
            unset($data['cc_email_check']);
            $text = 'Hey, I just updated contact:<br><ul>';
            foreach ($data as $k => $v) {
                $nVal = $v;
                $oVal = $contact[$k];

                if ($k == 'cc_phone') {
                    $nVal = numberTo($v);
                    $oVal = numberTo($oVal);
                }

                if ($contact[$k] != $v)
                    $text .= '<li>' . ucfirst(str_replace('cc_', '', $k)) . ': ' . $oVal . ' => ' . $nVal . '</li>';
            }
            $text .= '</ul>';
            make_notes($contact['cc_client_id'], $text, 'system', 0);
            $newData['client_contacts'] = $this->mdl_clients->get_client_contacts(['cc_id' => $cc_id]);
            $result['id'] = $cc_id;
            $result['save'] = 'update';
            $result['view'] = $this->load->view('client_information_display_contact', $newData, TRUE);
        } else {
            $data['cc_email_check'] = check_email_exists($data['cc_email']);
            $data['cc_client_id'] = $info['cc_client_id'];
            $this->mdl_clients->add_client_contact($data);
            $result['id'] = $newId = $this->db->insert_id();
            $newData['client_contacts'] = $this->mdl_clients->get_client_contacts(array('cc_id' => $newId));
            unset($data['cc_client_id'], $data['cc_email_check']);
            $text = 'Hey, I just created contact:<br><ul>';
            foreach ($data as $k => $v)
                $text .= '<li>' . ucfirst(str_replace('cc_', '', $k)) . ': ' . $v . '</li>';
            $text .= '</ul>';
            make_notes($info['cc_client_id'], $text, 'system', 0);

            $result['save'] = 'create';
            $result['view'] = $this->load->view('client_information_display_contact', $newData, TRUE);
        }
        $result['status'] = 'ok';

        //create a new job for client synchronization in qb
        $client_id = $info['cc_client_id'];
        $cl_data = $this->mdl_clients->get_client_by_id($client_id);

        pushJob('quickbooks/client/syncclientinqb', serialize(['id' => $client_id, 'qbId' => $cl_data->client_qb_id]));

        die(json_encode($result));
    }

    function ajax_change_primary_contact()
    {
        $cc_id = $this->input->post('cc_id');
        $cc_client_id = $this->input->post('cc_client_id');

        if (!$cc_id || !$cc_client_id)
            die(json_encode(['status' => 'error']));

        $this->mdl_clients->update_client_contact(['cc_print' => 0], ['cc_client_id' => $cc_client_id]);
        $this->mdl_clients->update_client_contact(['cc_print' => 1], ['cc_id' => $cc_id]);

        //create a new job for client synchronization in qb
        $cl_data = $this->mdl_clients->get_client_by_id($cc_client_id);
        pushJob('quickbooks/client/syncclientinqb', serialize(['id' => $cc_client_id, 'qbId' => $cl_data->client_qb_id]));

        die(json_encode(['status' => 'ok']));
    }

    function ajax_delete_contact()
    {
        $cc_id = $this->input->post('cc_id');

        if (!$cc_id)
            die(json_encode(['status' => 'error', 'msg' => 'Incorrect Contact']));

        $contact = $this->mdl_clients->get_client_contact(['cc_id' => $cc_id]);

        if ($contact['cc_print'])
            die(json_encode(['status' => 'error', 'msg' => "The Primary Contact Cannot Be Removed"]));

        $this->mdl_clients->delete_client_contact($cc_id);

        //create a new job for client synchronization in qb
        $client_id = $contact['cc_client_id'];
        $cl_data = $this->mdl_clients->get_client_by_id($client_id);
        pushJob('quickbooks/client/syncclientinqb', serialize(['id' => $client_id, 'qbId' => $cl_data->client_qb_id]));

        if ($contact) {
            $cl_id = $contact['cc_client_id'];
            unset($contact['cc_id'], $contact['cc_client_id'], $contact['cc_email_check'], $contact['cc_print']);
            $text = 'Hey, I just deleted contact:<br><ul>';
            foreach ($contact as $k => $v) {
                $val = $v;

                if ($k == 'cc_phone') {
                    $val = numberTo($v);
                }

                $text .= '<li>' . ucfirst(str_replace('cc_', '', $k)) . ': ' . $val . '</li>';
            }
            $text .= '</ul>';
            make_notes($cl_id, $text, 'system', 0);
        }
        die(json_encode(['status' => 'ok']));
    }


    function ajax_change_address()
    {
        $this->load->model('mdl_leads');
        $name = $this->input->post('name');
        $id = $this->input->post('pk');

        if ($name == 'client_address') {
            $oldData = $this->mdl_clients->get_clients('client_address, client_city, client_state, client_country, client_zip, client_main_intersection', array('client_id' => $id));


            $data['client_address'] = $this->input->post('value')['stump_address'];
            $data['client_city'] = $this->input->post('value')['stump_city'];
            $data['client_state'] = $this->input->post('value')['stump_state'];
            $data['client_country'] = $this->input->post('value')['stump_country'];

            $data['client_zip'] = element('stump_zip', $this->input->post('value'), null);
            $data['client_lat'] = element('stump_lat', $this->input->post('value'), null);
            $data['client_lng'] = element('stump_lon', $this->input->post('value'), null);
            $data['client_main_intersection'] = element('stump_main_intersection', $this->input->post('value'), '');
            $this->mdl_clients->update_client($data, array('client_id' => $id));

            //create a new job for client synchronization in qb
            $cl_data = $this->mdl_clients->get_client_by_id($id);
            pushJob('quickbooks/client/syncclientinqb', serialize(['id' => $id, 'qbId' => $cl_data->client_qb_id]));

            $oldAddress = $oldData->row_array();
            if ($oldData->num_rows()) {
                $text = 'Hey, I just changed client address:<br><ul>';
                foreach ($oldAddress as $k => $v)
                    $text .= '<li>' . $v . ' => ' . $data[$k] . '</li>';
                $text .= '</ul>';
                make_notes($id, $text, 'system', 0);
            }

            $result['main_intersection'] = $data['client_main_intersection'];
            $result['address'] = $data['client_address'];
            $result['city'] = $data['client_city'];
            $result['state'] = $data['client_state'];
            $result['zip'] = $data['client_zip'];
            $result['country'] = $data['client_country'];

            $result['lat'] = $data['client_lat'];
            $result['lon'] = $data['client_lng'];

            $result['id'] = $id;
            $result['name'] = 'client';
            $result['status'] = 'ok';
        } else {
            $oldData = $this->mdl_leads->get_leads(array('lead_id' => $id), false);

            $data['lead_address'] = $this->input->post('value')['stump_address'];
            $data['lead_city'] = $this->input->post('value')['stump_city'];
            $data['lead_state'] = $this->input->post('value')['stump_state'];
            $data['lead_zip'] = $this->input->post('value')['stump_zip'];
            $data['latitude'] = $this->input->post('value')['stump_lat'];
            $data['longitude'] = $this->input->post('value')['stump_lon'];
            if($this->input->post('value')['stump_add_info'])
                $data['lead_add_info'] = $this->input->post('value')['stump_add_info'];
            $autoTax = [];
             if(config_item('office_country') == 'United States of America') {
                 $addressForAutoTax = [
                     'Address' => $data['lead_address'],
                     'City' =>  $data['lead_city'],
                     'State' => $data['lead_state'],
                     'Zip' => $data['lead_zip']
                 ];
                 $autoTax = $this->estimateactions->getTaxForUSCompany($addressForAutoTax);
                 if (!empty($autoTax['db'])) {
                     $data = array_merge($data, $autoTax['db']);
                 }
             }

            $this->mdl_leads->update_leads($data, array('lead_id' => $id));
            $task['task_address'] = $result['address'] = $data['lead_address'];
            $task['task_city']  = $result['city'] = $data['lead_city'];
            $task['task_state'] = $result['state'] = $data['lead_state'];
            $task['task_country'] = $result['country'] = $this->input->post('value')['stump_country'];

            $task['task_latitude'] = $result['lat'] = $data['latitude'];
            $task['task_longitude'] = $result['lon'] = $data['longitude'];
            $task['task_zip'] = $result['zip'] = $data['lead_zip'];
            $result['stump_add_info'] = isset($data['lead_add_info']) ? $data['lead_add_info'] : '';
            $this->mdl_client_tasks->update_by($task, ['task_lead_id'=>$id]);

            $result['id'] = $id;

            $result['name'] = 'lead';
            $result['status'] = 'ok';
            $result['name'] = 'lead';
            $result['status'] = 'ok';



            if(!empty($autoTax['estimate'])) {
                $result['taxText'] = $autoTax['estimate']['text'];
                $result['taxName'] = $autoTax['estimate']['name'];
                $result['taxRate'] = $autoTax['estimate']['rate'];
                $result['taxValue'] = $autoTax['estimate']['value'];
                $allTaxes = all_taxes();
                $checkTax = checkTaxInAllTaxes($autoTax['estimate']['text']);
                if(!$checkTax)
                    $allTaxes[] = [
                        'id' => $autoTax['estimate']['text'],
                        'text' => $autoTax['estimate']['text'],
                        'name' => $autoTax['estimate']['name'],
                        'rate' => $autoTax['estimate']['rate'],
                        'value' => $autoTax['estimate']['value']
                    ];
                $result['allTaxes'] = $allTaxes;
            }

            if ($oldData->num_rows()) {
                unset($data['latitude'], $data['longitude']);
                $text = 'Hey, I just changed lead address:<br><ul>';
                foreach ($data as $k => $v)
                    $text .= '<li>' . $oldData->row_array()[$k] . ' => ' . $v . '</li>';
                $text .= '</ul>';
                make_notes($id, $text, 'system', $id);
            }


        }
        die(json_encode($result));
    }

    function followup($status = 'new', $module = 'all', $type = 'all', $page = 1)
    {
        $this->load->model('mdl_est_reason');
        $this->load->model('mdl_leads_reason');

        $types = [
            'all' => 'Select Type',
            'call' => 'Call',
            'mail' => 'Mail',
            'email' => 'Email',
            'sms' => 'SMS',
            'invoice_overdue' => 'Invoice Overdue',
            'estimate_expired' => 'Estimate Expired',
            'equipment_items' => 'Equipment Alarm',
            'users' => 'Expired User Docs'
        ];
        $statuses = ['new', 'completed', 'postponed', 'canceled'];
        $modules = ['all', 'leads', 'estimates', 'invoices', 'schedule', 'client_tasks', 'equipment_items', 'users'];

        if (array_search($status, $statuses) === FALSE)
            show_404();
        if (array_search($module, $modules) === FALSE)
            show_404();
        if (!isset($types[$type]))
            show_404();

        $estimateReasons = $this->mdl_est_reason->get_many_by(['reason_active' => 1]);

        $leadReasons[0]['reason_name'] = "Don't provide this service";
        $leadReasons[0]['reason_status'] = "No Go";
        $leadReasons[1]['reason_name'] = "Out of service area";
        $leadReasons[1]['reason_status'] = "No Go";
        $leadReasons[2]['reason_name'] = "Don't want work done anymore";
        $leadReasons[2]['reason_status'] = "No Go";
        $leadReasons[3]['reason_name'] = "Already Done";
        $leadReasons[3]['reason_status'] = "No Go";
        $leadReasons[4]['reason_name'] = "Dublicate lead";
        $leadReasons[4]['reason_status'] = "No Go";
        $leadReasons[5]['reason_name'] = "Hydro";
        $leadReasons[5]['reason_status'] = "No Go";
        $leadReasons[6]['reason_name'] = "Dangerous tree no access";
        $leadReasons[6]['reason_status'] = "No Go";
        $leadReasons[7]['reason_name'] = "Spam";
        $leadReasons[7]['reason_status'] = "No Go";
        $leadReasons[8]['reason_name'] = "Already hired someone else";
        $leadReasons[8]['reason_status'] = "No Go";
        $leadReasons[9]['reason_name'] = "The lead is not responding";
        $leadReasons[9]['reason_status'] = "No Go";
        $objectLeadReasons[] = (object)$leadReasons[0];
        $objectLeadReasons[] = (object)$leadReasons[1];
        $objectLeadReasons[] = (object)$leadReasons[2];
        $objectLeadReasons[] = (object)$leadReasons[3];
        $objectLeadReasons[] = (object)$leadReasons[4];
        $objectLeadReasons[] = (object)$leadReasons[5];
        $objectLeadReasons[] = (object)$leadReasons[6];
        $objectLeadReasons[] = (object)$leadReasons[7];
        $objectLeadReasons[] = (object)$leadReasons[8];
        $objectLeadReasons[] = (object)$leadReasons[9];

        $data['title'] = "Follow Up";

        //$objectLeadReasons = $this->mdl_leads_reason->get_many_by(['reason_active' => 1]);
        //echo '<pre>'; var_dump($data['lead_statuses']); die;
        $data['statuses'] = $statuses;
        $data['config_modules'] = $this->config->item('followup_modules');
        $data['config_modules']['estimates']['reasons'] = $estimateReasons;
        $data['config_modules']['leads']['reasons'] = $objectLeadReasons;


        $data['status'] = $status;
        $data['modules'] = $modules;
        $data['module'] = $module;
        $data['types'] = $types;
        $data['type'] = $type;

        $this->load->model('mdl_followups');

        $listWhere = [];
        if ($status == 'new' || $status == 'postponed')
            $listWhere = ['fs_cron' => 0];
        if ($module != 'all')
            $listWhere['fu_module_name'] = $module;
        if ($type != 'all')
            $listWhere['fs_type'] = $type;

        $config = array();
        $config["base_url"] = base_url() . "clients/followup/" . $status . '/' . $module . '/' . $type;

        $config["total_rows"] = $this->mdl_followups->get_list_count($status, $listWhere);
        $config["per_page"] = 100;
        $config["uri_segment"] = 6;
        $config['use_page_numbers'] = TRUE;
        $config['full_tag_open'] = '<ul class="pagination pagination-sm m-t-none m-b-none pull-right">';
        $config['full_tag_close'] = '</ul>';

        $this->pagination->initialize($config);

        $page = (!$page) ? 1 : intval($page);

        $start = $page - 1;
        $start = $start * $config["per_page"];
        $limit = $config["per_page"];

        $data['start'] = $start;
        $data['per_page'] = $limit;

        $data["links"] = $this->pagination->create_links();
        $data["page"] = $page;
        $data["count"] = $config["total_rows"];

        $data['fu_list'] = $this->mdl_followups->get_list($status, $listWhere, $limit, $start);

        if (!$data['fu_list'] && $start)
            show_404();

        $this->load->view('followup/index', $data);
    }

    function ajax_update_followup_status()
    {
        $this->load->model('mdl_followups');
        $this->load->model('mdl_leads');
        $this->load->model('mdl_estimates');
        $this->load->model('mdl_invoices');
        $this->load->model('mdl_est_reason');

        $fsConfig = $this->config->item('followup_modules');
        $value = $this->input->post('value');
        $pk = intval($this->input->post('pk'));

        $result['status'] = 'error';
        $result['id'] = $pk;
        $fuRow = $this->mdl_followups->get($pk);
        if (!$fuRow)
            die(json_encode($result));

        $estimateReasons = $this->mdl_est_reason->get_many_by(['reason_active' => 1]);
        $fsConfig['estimates']['reasons'] = $estimateReasons;

        $updateData = [];
        if (isset($value['fu_status']) && $fuRow->fu_status != $value['fu_status'])
            $updateData['fu_status'] = $value['fu_status'];

        if (isset($value['fu_comment']) && $fuRow->fu_comment != $value['fu_comment'])
            $updateData['fu_comment'] = $value['fu_comment'];

        if (isset($value['fu_date']) && $fuRow->fu_date != $value['fu_date'] && $value['fu_status'] == 'postponed')
            $updateData['fu_date'] = $value['fu_date'];

        if (!empty($updateData)) {
            $updateData['fu_author'] = request()->user()->id;
            $this->mdl_followups->update($pk, $updateData);

            $mdlName = 'mdl_' . $fuRow->fu_module_name;
            $itemRow = $this->$mdlName->find_by_id($fuRow->fu_item_id);

            $data['fields'] = [
                'fu_status' => 'Status',
                'fu_comment' => 'Notes',
                'fu_date' => 'Date',
            ];
            unset($updateData['fu_author']);
            $data['fsConfig'] = $this->config->item('followup_modules');
            $data['fuData'] = $fuRow;
            $data['item'] = $itemRow;
            $data['from'] = $fuRow;
            $data['to'] = $updateData;
            make_notes($fuRow->fu_client_id, $this->load->view('followup/note_tpl', $data, TRUE), 'system', $itemRow->lead_id);

            $result['status'] = 'ok';
            $result['html'] = NULL;
            if (!isset($updateData['fu_status'])) {
                $fUps = $this->mdl_followups->get_list(NULL, ['fu_id' => $pk]);
                $result['html'] = $this->load->view('followup/rows', ['fu_list' => $fUps, 'config_modules' => $fsConfig], TRUE);
            }
        }

        die(json_encode($result));
    }

    function ajax_update_followup_field()
    {
        $this->load->model('mdl_followups');
        $this->load->model('mdl_est_reason');
        $this->load->model('mdl_leads');
        $this->load->model('mdl_estimates');
        $this->load->model('mdl_invoices');

        $fields = ['fu_comment', 'fu_status'];

        $fieldName = $this->input->post('name');
        $pk = intval($this->input->post('pk'));
        $value = $this->input->post('value', TRUE);

        $fU = $this->mdl_followups->get($pk);
        $mdlName = 'mdl_' . $fU->fu_module_name;
        $itemRow = $this->$mdlName->find_by_id($fU->fu_item_id);
        $data['fields'] = [
            'fu_comment' => 'Notes',
            'fu_date' => 'Date',
        ];
        $data['fsConfig'] = $this->config->item('followup_modules');
        $data['fuData'] = $fU;
        $data['item'] = $itemRow;
        $author = NULL;
        if ($fieldName == 'fu_status') {
            if (!$fU->fu_comment)
                die(json_encode(['status' => 'error']));

            $data['fields']['fu_status'] = 'Status';

            $data['to'] = ['fu_status' => $value, 'fu_comment' => $fU->fu_comment];
            $fUData = $fU;
            $fUData->fu_comment = NULL;
            $data['from'] = $fUData;
        } else {
            $data['to'] = ['fu_comment' => $value];
            $fUData = $fU;
            $fUData->fu_status = NULL;
            $data['from'] = $fUData;
        }
        make_notes($fU->fu_client_id, $this->load->view('followup/note_tpl', $data, TRUE), 'system', $itemRow->lead_id);
        $fsConfig = $this->config->item('followup_modules');

        $estimateReasons = $this->mdl_est_reason->get_many_by(['reason_active' => 1]);
        $fsConfig['estimates']['reasons'] = $estimateReasons;

        if (array_search($fieldName, $fields) === FALSE)
            die(json_encode(['status' => 'error']));

        $updateData[$fieldName] = $value;
        $updateData['fu_author'] = request()->user()->id;
        $this->mdl_followups->update($pk, $updateData);
        $result['id'] = $pk;
        $result['status'] = 'ok';
        $result['html'] = NULL;
        if (!isset($updateData['fu_status'])) {
            $fUps = $this->mdl_followups->get_list(NULL, ['fu_id' => $pk]);
            $result['html'] = $this->load->view('followup/rows', ['fu_list' => $fUps, 'config_modules' => $fsConfig], TRUE);
        }
        die(json_encode($result));
    }

    /**
     * @todo EMERGENCY CHANGE INVOICE AND ESTIMATES UPDATE STATUS!!!!!
     */
    function ajax_update_followup_item_status()
    {

        $this->load->model('mdl_followups');
        $this->load->model('mdl_est_reason');
        $fsConfig = $this->config->item('followup_modules');

        $estimateReasons = $this->mdl_est_reason->get_many_by(['reason_active' => 1]);
        $fsConfig['estimates']['reasons'] = $estimateReasons;

        $module = $this->input->post('name');
        $pk = $this->input->post('pk');
        $values = $this->input->post();
        unset($values['pk'], $values['name']);

        $functions = [
            'leads' => 'leads/leads/update_lead_status',
            'estimates' => 'estimates/estimates/change_estimates_status',
            'invoices' => 'invoices/invoices/change_invoice_status',
        ];

        $response = ['status' => 'error', 'html' => NULL, 'id' => $pk];

        if (!isset($functions[$module]))
            die(json_encode($response));

        $fU = $this->mdl_followups->get($pk);

        $values[$fsConfig[$module]['id_field_name']] = $fU->fu_item_id;

        switch ($module) {
            case 'invoices':
                $newInvoiceStatus = $this->mdl_invoice_status->get_by(['invoice_status_name' => $values['new_invoice_status']]); // Времменно до устранения лажи
                $result = Modules::run($functions[$module], [
                    'invoice_id' => $fU->fu_item_id,
                    'pre_invoice_status' => $values['pre_invoice_status'],
                    'new_invoice_status' => $newInvoiceStatus->invoice_status_id, //$values['new_invoice_status'], // Должен быть ID а не текст!
                    'payment_mode' => $values['payment_method_int'], //$this->methodToText($method), //Не правильно бьется метод! должен быть ID
                ]);
                break;
            case 'estimates':
                $result = Modules::run($functions[$module], [
                    'estimate_id' =>$values['estimate_id'],
                    'pre_estimate_status' => $values['pre_estimate_status'],
                    'new_estimate_status' => $values['new_estimate_status'],
                    'payment_method' => $values['payment_method_int'], // не понятно почему инт возвращает текст
                    'wo_deposit' => $values['wo_deposit'],
                    'wo_confirm_how' => $values['wo_confirm_how'],
                    'estimate_reason_decline' => $values['reason']
                ]);
                break;
            case 'leads':
            default:
                $result = Modules::run($functions[$module], false, $values);
        }


        if ($result) {
            $fUps = $this->mdl_followups->get_list(NULL, ['fu_id' => $pk]);
            $response['html'] = $this->load->view('followup/rows', ['fu_list' => $fUps, 'config_modules' => $fsConfig], TRUE);
            $response['status'] = 'ok';
        }

        die(json_encode($response));
    }

    /**
     * @todo EMERGENCY CHANGE INVOICE AND ESTIMATES UPDATE STATUS!!!!!
     */
    function ajax_update_followup_item_status_paid_invoice()
    {
        $result['status'] = 'error';
        $result['msg'] = "TEMPORARY DISABLE!";
        return $this->output->set_content_type('application/json')->set_status_header(400)->set_output(json_encode($result));
        /*
        sel_invoice_status: Paid
        payment_method: cash
        payment_amount: 18
        estimate_id: 111
        payment_type: invoice
        file: 111
        */
        $this->load->model('mdl_followups');
        $this->load->model('mdl_estimates');
        $this->load->model('mdl_invoices');
        $module = $this->input->post('name');
        $ccReceipt = $this->input->post('ccReceipt');
        $pk = $this->input->post('pk');

        $fU = $this->mdl_followups->get($pk);
        $invoice = $this->mdl_invoices->find_by_id($fU->fu_item_id);

        $_POST['sel_invoice_status'] = $this->input->post('new_invoice_status');
        $_POST['estimate_id'] = $invoice->estimate_id;
        $_POST['payment_type'] = 'invoice';
        $amount = getAmount($this->input->post('payment_amount'));
        $result['status'] = 'error';
        $total = $this->mdl_estimates->get_total_estimate_balance($invoice->estimate_id);
        if ($total - $amount > 0 || $total - $amount < -1) {
            $result['msg'] = "Incorrect Amount\r\nTotal Due:" . money($total);
            return $this->output->set_content_type('application/json')->set_status_header(400)->set_output(json_encode($result));
        }

        $result = $this->ajax_add_payment(FALSE);

        if ($result['status'] == 'ok') {
            $fsConfig = $this->config->item('followup_modules');
            $fUps = $this->mdl_followups->get_list(NULL, ['fu_id' => $pk]);
            $result['html'] = $this->load->view('followup/rows', ['fu_list' => $fUps, 'config_modules' => $fsConfig], TRUE);
            $result['id'] = $pk;
            return $this->output->set_content_type('application/json')->set_status_header(200)->set_output(json_encode($result));
        }
        return $this->output->set_content_type('application/json')->set_status_header(400)->set_output(json_encode($result));
    }

    function ajax_followup_html()
    {
        $this->load->model('mdl_followups');
        $pk = $this->input->post('pk');

        $fsConfig = $this->config->item('followup_modules');
        $fUps = $this->mdl_followups->get_list(NULL, ['fu_id' => $pk]);
        $result['html'] = $this->load->view('followup/rows', ['fu_list' => $fUps, 'config_modules' => $fsConfig], TRUE);
        $result['id'] = $pk;
        $result['status'] = 'ok';
        return $this->output->set_content_type('application/json')->set_status_header(200)->set_output(json_encode($result));
    }

    function ajax_amount_validator()
    {
        $this->load->model('mdl_estimates');
        $estimate_id = $this->input->post('estimate_id');
        $amount = getAmount($this->input->post('amount'));
        $total = $this->mdl_estimates->get_total_estimate_balance($estimate_id);
        $result['status'] = 'ok';
        if ($total - $amount > 0 || $total - $amount < -1) {
            $result['status'] = 'error';
            $result['msg'] = "Incorrect Amount\r\nTotal Due:" . money($total);
        }
        return $this->output->set_content_type('application/json')->set_status_header(200)->set_output(json_encode($result));
    }

    /**
     * Save lead note
     */
    function ajax_save_lead_note()
    {
        $this->load->model('mdl_leads');
        $id = $this->input->post('id');
        $data['lead_comment_note'] = $this->input->post('text', true);

        if ($data['lead_comment_note'] === '') {
            $data['lead_comment_note'] = null;
        }

        try {
            $this->mdl_leads->update_leads($data, ['lead_id' => $id]);
            $result['status'] = 'ok';
        }
        catch (Exception $e) {
            $result['status'] = 'error';
        }

        $this->response($result);
    }

    function ajax_add_paper()
    {
        $data['cp_text'] = $this->input->post('text');
        $data['cp_client_id'] = $this->input->post('id');
        $data['cp_user_id'] = request()->user()->id;
        $data['cp_date'] = date('Y-m-d');
        //echo '<pre>'; var_dump($data); die;
        $id = $this->mdl_clients->add_client_papers($data);
        if ($id) {
            $result = $this->mdl_clients->get_papers(['cp_id' => $id]);
            if(!empty($result[0]['cp_date']))
                $result[0]['cp_date'] = getDateTimeWithDate($result[0]['cp_date'], 'Y-m-d');
        } else {
            $result = $id;
        }

        $this->response($result);
    }

    function ajax_delete_paper()
    {
        $id = $this->input->post('id');
        $result = $this->mdl_clients->delete_client_papers($id);

        $this->response($result);
    }

    public function client_mailing()
    {
        //Searching Data
        $this->load->model('mdl_est_status');
        $this->load->model('mdl_estimates');
        $this->load->model('mdl_services');
        $this->load->model('mdl_user');
        $this->load->model('mdl_categories');
        $this->load->model('mdl_workorders');
        $this->load->model('mdl_letter');
        $this->load->model('mdl_user');
        $this->load->model('mdl_invoice_status');
        $this->load->model('mdl_leads_status');



        $data['invoices_statuses'] = $this->mdl_invoice_status->get_many_by(['invoice_status_active' => 1]);

        $data['count'] = 0;
        $data['lead_statuses'] = $this->mdl_leads_status->with('mdl_leads_reason')->get_all();
        $data['clients'] = [];
        $data['user_email'] = $this->config->item('account_email_address');
        $user = $this->mdl_user->get_user('user_email', ['id' => request()->user()->id]);
        $user_data = $user->row();
        if ($user && isset($user_data->user_email) && $user_data->user_email != '')
            $data['user_email'] = $user_data->user_email;

        //Searching Data

        $data['title'] = "Newsletters";
        // Set menu status
        $data['menu_clients'] = "active";
        $search_array = array();
        $search_keyword = '';
        $offset = 500;
        $wdata = array();

        if (is_cl_permission_owner()) {
            $wdata['client_maker'] = request()->user()->id;
        }

        $total_rows = $this->mdl_clients->record_count($search_array, $wdata);
        $data['letters'] = $this->mdl_letter->get_all(['email_news_templates' => 1]);

        //Searching Data
        //$data['task_categories'] = $this->mdl_categories->get_all('category_active = 1');
        $data['estimators'] = $data['workers'] = $data['active_users'] = [];
        $data['active_users'] = [];
        $active_users = $this->mdl_user->get_usermeta(array('active_status' => 'yes', 'emp_status' => 'current', 'users.id <>' => 0));
        if ($active_users)
            $data['active_users'] = $active_users;
        $data['wo_statuses'] = $this->mdl_workorders->get_all_statuses(array('wo_status_active' => 1));


        $estimators = $this->mdl_estimates->get_active_estimators();
        if ($estimators)
            $data['estimators'] = $estimators;
        $data['services'] = $this->mdl_services->find_all([
            'service_parent_id' => NULL,
            'service_status' => 1,
            'is_product' => 0,
            'is_bundle' => 0
        ], 'service_priority');
        $data['statuses'] = $this->mdl_est_status->with('mdl_est_reason')->get_many_by(array('est_status_active' => 1));
        $data['workers'] = [];
        $users = $this->mdl_user->get_usermeta(array('emp_status' => 'current', 'emp_feild_worker' => 1, 'active_status' => 'yes'));
        if ($users)
            $data['workers'] = $users->result_array();
        //Searching Data
        //load view.
        $this->load->view('clients/client_mailing', $data);
    }

    function ajax_more_clients()
    {
        $result['status'] = 'error';
        $id = $this->input->post('id');
        $wdata = $search_array = [];
        $limit = 100;
        $offset = intval($this->input->post('num'));

        $data["clients"] = $this->mdl_clients->find_all_with_limit($search_array, $limit, $offset, 'client_date_created DESC', $wdata);

        $result['offset'] = $offset + 100;
        if ($data["clients"]) {
            $result['status'] = 'ok';
            $result['more'] = count($data["clients"]) < $limit ? 0 : 1;//countOk
            $result['table'] = $this->load->view('clients_row', $data, TRUE);
        }

        die(json_encode($result));
    }

    function emailing_search()
    {
        //echo '<pre>'; var_dump($_POST); die;
        $this->load->model('mdl_est_status');
        $this->load->model('mdl_estimates');
        $this->load->model('mdl_services');
        $this->load->model('mdl_user');
        $this->load->model('mdl_categories');
        $this->load->model('mdl_workorders');
        $this->load->model('mdl_letter');
        $this->load->model('mdl_invoice_status');
        $this->load->model('mdl_leads_status');

        $data['title'] = 'Newsletters';
        $data['user_email'] = $this->config->item('account_email_address');
        $data['lead_statuses'] = $this->mdl_leads_status->with('mdl_leads_reason')->get_all();
        $data['invoices_statuses'] = $this->mdl_invoice_status->get_many_by(['invoice_status_active' => 1]);
        $user = $this->mdl_user->get_user('user_email', ['id' => request()->user()->id]);
        $user_data = $user->row();
        if ($user && isset($user_data->user_email) && $user_data->user_email != '')
            $data['user_email'] = $user_data->user_email;


        $where['clients'] = $where['tasks'] = $where['leads'] = $where['estimates'] = $where['workorders'] = $where['invoices'] = [];
        $where['search_by']['leads'] = $where['search_by']['estimates'] = $where['search_by']['workorders'] = $where['search_by']['invoices'] = $trigger = 'left';

        $data['count'] = 0;
        $data['clients'] = [];

        $search_by = $date = $services = $estimate_price = $service_price = $status = $invoice_price = [];
        $user_id = $note = '';
        //$this->mdl_estimates->get_estimates();
        if ($this->input->post('search_client_from') !== FALSE && $this->input->post('search_client_from') !== ''){
            $clientFrom = DateTime::createFromFormat(getDateFormat(), $this->input->post('search_client_from'));
            $data['search_client_from'] = $where['clients']['clients.client_date_created >'] = $clientFrom->format('Y-m-d');
        }

        if ($this->input->post('search_client_to') !== FALSE && $this->input->post('search_client_to') !== ''){
            $clientTo =  DateTime::createFromFormat(getDateFormat(), $this->input->post('search_client_to'));
            $data['search_client_to'] = $where['clients']['clients.client_date_created <'] = $clientTo->format('Y-m-d');
        }

        if ($this->input->post('search_client_type') !== FALSE && $this->input->post('search_client_type') !== '')
            $data['search_client_type'] = $where['clients']['clients.client_type'] = $this->input->post('search_client_type');
        if ($this->input->post('search_by') !== FALSE && !empty($this->input->post('search_by')))
            $data['search_by'] = $this->input->post('search_by');
        $data['letters'] = $this->mdl_letter->get_all(['email_news_templates' => 1]);


        if (isset($data['search_by']) && !empty($data['search_by'])) {
            foreach ($where['search_by'] as $k => $v) {
                if (array_search($k, $data['search_by']) !== FALSE)
                    $trigger = 'inner';
                $where['search_by'][$k] = $trigger;
            }
        }
        //TASK
        /*
        if($this->input->post('search_task_from') !== FALSE && $this->input->post('search_task_from') !== '')
            $data['search_task_from'] = $where['tasks']['client_tasks.task_date_created >'] = $this->input->post('search_task_from');
        if($this->input->post('search_task_to') !== FALSE && $this->input->post('search_task_to') !== '')
            $data['search_task_to'] = $where['tasks']['client_tasks.task_date_created <'] = $this->input->post('search_task_to');
        if($this->input->post('search_task_categories') !== FALSE && $this->input->post('search_task_categories') !== '')
            $data['search_task_categories'] = $where['tasks']['client_tasks.task_category'] = $this->input->post('search_task_categories');
        if($this->input->post('search_task_status') !== FALSE && $this->input->post('search_task_status') !== '')
            $data['search_task_status'] = $where['tasks']['client_tasks.task_status'] = $this->input->post('search_task_status');
        */
        //END TASK
        //LEADS
        if ($this->input->post('search_lead_from') !== FALSE && $this->input->post('search_lead_from') !== '') {
            $leadFrom =  DateTime::createFromFormat(getDateFormat(), $this->input->post('search_lead_from'));
            $data['search_lead_from'] = $leadFrom->format('Y-m-d');
            $where['leads']['leads.lead_date_created >'] = $data['search_lead_from'] . ' 00:00:00';
        }
        if ($this->input->post('search_lead_to') !== FALSE && $this->input->post('search_lead_to') !== '') {
            $leadTo =  DateTime::createFromFormat(getDateFormat(), $this->input->post('search_lead_to'));
            $data['search_lead_to'] = $leadTo->format('Y-m-d');
            $where['leads']['leads.lead_date_created <'] = $data['search_lead_to'] . ' 23:59:59';
        }
        if ($this->input->post('search_lead_status') !== FALSE && $this->input->post('search_lead_status') !== '')
            $data['search_lead_status'] = $where['leads']['leads.lead_status_id'] = $this->input->post('search_lead_status');



        //END LEADS
        //ESTIMATES
        if ($this->input->post('search_estimate_from') !== FALSE && $this->input->post('search_estimate_from') !== '') {
            $estimateFrom =  DateTime::createFromFormat(getDateFormat(), $this->input->post('search_estimate_from'));
            $data['search_estimate_from'] = $estimateFrom->format('Y-m-d');
            $date['date_created >='] = strtotime($data['search_estimate_from'] . ' 00:00:00');
            $data['from'] = $this->input->post('search_estimate_from');
        }
        if ($this->input->post('search_estimate_to') !== FALSE && $this->input->post('search_estimate_to') !== '') {
            $estimateTo =  DateTime::createFromFormat(getDateFormat(), $this->input->post('search_estimate_to'));
            $data['search_estimate_to'] = $estimateTo->format('Y-m-d');
            $date['date_created <='] = strtotime($data['search_estimate_to'] . ' 23:59:59');
            $data['to'] = $this->input->post('search_estimate_to');
        }

        if ($this->input->post('search_estimate_price_from') && $this->input->post('search_estimate_price_from') != '')
            $data['search_estimate_price_from'] = $estimate_price['>='] = $this->input->post('search_estimate_price_from');

        if ($this->input->post('search_estimate_price_to') !== FALSE && $this->input->post('search_estimate_price_to') !== '')
            $data['search_estimate_price_to'] = $estimate_price['<='] = $this->input->post('search_estimate_price_to');

        if ($this->input->post('search_service_price_from') && $this->input->post('search_service_price_from') != '')
            $data['search_service_price_from'] = $service_price['service_price >='] = $this->input->post('search_service_price_from');

        if ($this->input->post('search_service_price_to') !== FALSE && $this->input->post('search_service_price_to') !== '')
            $data['search_service_price_to'] = $service_price['service_price <='] = $this->input->post('search_service_price_to');

        if ($this->input->post('search_estimator') !== FALSE && $this->input->post('search_estimator') !== '')
            $data['search_estimator'] = $user_id = $this->input->post('search_estimator');

        if ($this->input->post('search_status') !== FALSE && $this->input->post('search_status') !== '')
            $data['search_status'] = $status = $this->input->post('search_status');

        if ($this->input->post('search_estimate_description') !== FALSE && $this->input->post('search_estimate_description') !== '')
            $data['search_desc'] = $note = $this->input->post('search_estimate_description');

        if ($this->input->post('search_service_type') && !empty($this->input->post('search_service_type')))
            $data['search_service_type'] = $services = $this->input->post('search_service_type'); // service_id

        //END ESTIMATES
        //WO

        if ($this->input->post('search_workorder_from') !== FALSE && $this->input->post('search_workorder_from') !== '') {
            $woFrom =  DateTime::createFromFormat(getDateFormat(), $this->input->post('search_workorder_from'));
            $data['search_workorder_from'] = $woFrom->format('Y-m-d');
            $where['workorders']['workorders.date_created >'] = $data['search_workorder_from'];
        }
        if ($this->input->post('search_workorder_to') !== FALSE && $this->input->post('search_workorder_to') !== '') {
            $woTo =  DateTime::createFromFormat(getDateFormat(), $this->input->post('search_workorder_to'));
            $data['search_workorder_to'] = $woTo->format('Y-m-d');
            $where['workorders']['workorders.date_created <'] = $data['search_workorder_to'];
        }
        if ($this->input->post('search_workorder_status') !== FALSE && $this->input->post('search_workorder_status') !== '')
            $data['search_workorder_status'] = $where['workorders']['workorders.wo_status'] = $this->input->post('search_workorder_status');

        //END WO
        //INVOICE
        if ($this->input->post('search_invoice_from') !== FALSE && $this->input->post('search_invoice_from') !== '') {
            $invoiceFrom =  DateTime::createFromFormat(getDateFormat(), $this->input->post('search_invoice_from'));
            $data['search_invoice_from'] = $invoiceFrom->format('Y-m-d');
            $where['invoices']['invoices.date_created >'] = $data['search_invoice_from'];
        }
        if ($this->input->post('search_invoice_to') !== FALSE && $this->input->post('search_invoice_to') !== '') {
            $invoiceTo=  DateTime::createFromFormat(getDateFormat(), $this->input->post('search_invoice_to'));
            $data['search_invoice_to'] = $invoiceTo->format('Y-m-d');
            $where['invoices']['invoices.date_created <'] = $data['search_invoice_to'];
        }
        if ($this->input->post('search_invoice_status') !== FALSE && $this->input->post('search_invoice_status') !== '')
            $data['search_invoice_status'] = $where['invoices']['invoices.in_status'] = $this->input->post('search_invoice_status');
        if ($this->input->post('search_invoice_price_from') !== FALSE && $this->input->post('search_invoice_price_from') !== '')
            $data['search_invoice_price_from'] = $invoice_price['>='] = $this->input->post('search_invoice_price_from');
        if ($this->input->post('search_invoice_price_to') !== FALSE && $this->input->post('search_invoice_price_to') !== '')
            $data['search_invoice_price_to'] = $invoice_price['<='] = $this->input->post('search_invoice_price_to');


        //END INVOICE

        //$config["per_page"] = 1000;

        //$page = 1;
        //if($this->uri->segment(3))
        //$page = intval($this->uri->segment(3));

        //$page = (!$page) ? 1 : $page;

        $start = $limit = '';
        //$start = $start * $config["per_page"];
        //$limit = $config["per_page"];
        $clients = $this->mdl_clients->global_search_clients($where, $date, $services, $estimate_price, $service_price, $user_id, $status, $note, $invoice_price, $limit, $start);

        if ($clients->num_rows()) {
            $data['count'] = $clients->num_rows();
            $data['clients'] = $clients->result();
        }
        //echo '<pre>'; var_dump($data['count'], $this->db->last_query()); die;

        $data['links'] = NULL;//$this->pagination->create_links();


        $data['workers'] = $data['estimators'] = [];
        $data['wo_statuses'] = $this->mdl_workorders->get_all_statuses(array('wo_status_active' => 1));
        $users = $this->mdl_user->get_usermeta(array('emp_status' => 'current', 'emp_feild_worker' => 1, 'active_status' => 'yes'));
        if ($users && $users->num_rows())
            $data['workers'] = $users->result_array();
        $estimators = $this->mdl_estimates->get_active_estimators();
        if ($estimators)
            $data['estimators'] = $this->mdl_estimates->get_active_estimators();
        $data['services'] = $this->mdl_services->find_all([
            'service_parent_id' => NULL,
            'service_status' => 1,
            'is_product' => 0,
            'is_bundle' => 0
        ], 'service_priority');
        $data['statuses'] = $this->mdl_est_status->with('mdl_est_reason')->get_many_by(array('est_status_active' => 1));


        //Load view:

        //
        $this->load->view("client_mailing", $data);
    }

    function ajax_send_newsletters()
    {
        ini_set('memory_limit', '-1');
        $this->load->model('mdl_user');
        $clients = json_decode($this->input->post('clients'));
        $from = $this->input->post('from');
        $subject = $this->input->post('subject');
        $text = $this->input->post('template_text');
        $estimator = $this->input->post('estimator');
        $estimator_id = $by_estimator = FALSE;

        $default_signature = $this->config->item('default_signature');
        $default_name = $this->config->item('default_email_from');

        if ($estimator == '0')
            $estimator_id = request()->user()->id;
        elseif (intval($estimator))
            $estimator_id = $estimator;
        elseif ($estimator == 'team_td')
            $by_estimator = FALSE;
        else {
            $by_estimator = TRUE;
        }
        $count = 0;

        if (!empty($clients)) {

            $estimators = $this->mdl_estimates->get_active_estimators();
            $activeIds = [];
            foreach ($estimators as $k => $v)
                $activeIds[] = $v['id'];
            foreach ($clients as $k => $v) {

                $letter = (object) ['email_template_text' => $text, 'email_template_title'=>$subject];
                $clientRow = Client::with(['primary_contact', 'estimates.user'=>function($query){
                    $query->limit(1);
                }])->find(intval($v));

                $brand_id = get_brand_id([], $clientRow->toArray());
                $letter = ClientLetter::compileLetter($letter, $brand_id, [
                    'client' => $clientRow
                ]);

                $emailText = $letter->email_template_text;
                $subject = $letter->email_template_title;

                if ($by_estimator) {
                    $signature = $this->config->item('default_signature');
                    $name = $this->config->item('default_email_from');

                    if($clientRow->estimates && isset($clientRow->estimates[0]) && $clientRow->estimates[0]->user){

                        $from = $clientRow->estimates[0]->user->user_email;
                        $estimator_id = $clientRow->estimates[0]->user->id;
                        $name = $clientRow->estimates[0]->user->full_name;
                        $signature = $clientRow->estimates[0]->user->user_signature;
                    }
                } else {
                    $user = $this->mdl_user->find_by_id($estimator_id);
                    $signature = $user->user_signature;
                    $name = $user->firstname . ' ' . $user->lastname;
                    $from = $user->user_email;
                }

                $to = $clientRow->primary_contact->cc_email;

                if ($signature) {
                    if (strpos($emailText, '[SIGNATURE]') !== FALSE)
                        $emailText = str_replace('[SIGNATURE]', $signature, $emailText);
                    else
                        $emailText .= $signature;

                    $emailText = str_replace('[ESTIMATOR]', $name, $emailText);
                } else {
                    if (strpos($emailText, '[SIGNATURE]') !== FALSE)
                        $emailText = str_replace('[SIGNATURE]', $default_signature, $emailText);
                    else
                        $emailText .= $default_signature;
                    $emailText = str_replace('[ESTIMATOR]', $default_name, $emailText);
                }

                if (!$from || ($from && strpos($from, brand_name($brand_id)) === FALSE))
                    $from = $this->config->item('account_email_address');

                $emailText = str_replace('[SIGNATURE]', '', $emailText);
                $emailText = str_replace('[UNSUBSCRIBE]', '<p style="text-align:left; font-size: 10px; color: rgb(71, 74, 93);"> If you no longer wish to receive these emails you may ' .
                    '<a style="color: rgb(71, 74, 93);" href="' . $this->config->item('unsubscribe_link') . md5($v) . '">unsubscribe</a> at any time.</p>', $emailText);

                $emailText = htmlspecialchars_decode($emailText);
                $data[$k]['nl_estimator'] = $estimator_id;
                $data[$k]['nl_client'] = intval($v);
                $data[$k]['nl_subject'] = $subject;
                $data[$k]['nl_from'] = trim($from);
                $data[$k]['nl_to'] = trim($to);
                $data[$k]['nl_mailgun_status'] = 'queued';
                $data[$k]['nl_text'] = $emailText;
                $data[$k]['nl_date'] = date('Y-m-d H:i:s');

                $count++;
            }
            if (!empty($data))
                $this->mdl_clients->insert_batch_nl($data);


            $mess = message('success', '<strong>Success!</strong>&nbsp;' . $count . ' letters was added to queue!');
            $status = 'ok';
        } else {
            $mess = message('alert', '<strong>Failed !</strong>&nbsp;Something wrong. Please try again!');
            $status = 'error';
        }

        pushJob('common/newsletters_sender');
        $this->session->set_flashdata('user_message', $mess);
        die(json_encode(['status' => $status]));
    }


    public function ajax_search_tag()
    {
        $tagName = $this->input->get('q');
        $searchTags = Tag::where('name', 'like', "%{$tagName}%")->get(['tag_id as id', 'name as text'])->toArray();

        die(json_encode(['items'=>$searchTags]));
    }

    public function ajax_add_tag()
    {
        $tagName = $this->input->post('tag_name', true);
        $clientId = $this->input->post('client_id', true);

        Tag::syncTagWithClient($tagName, $clientId);

        die(Client::find($clientId)->tags->toJson());
    }


    public function ajax_delete_tag()
    {
        $tagName = $this->input->post('tag_name', true);
        $clientId = $this->input->post('client_id', true);

        $client = Client::find($clientId);
        $tag = Tag::where('name', $tagName)->first();

        if (isset($tag)) {
            $client->tags()->detach($tag->tag_id);
        }

        Tag::deleteFreeTags();
        $this->response($client->tags);
        //die($client->tags->toJson());
    }

    function tasksSearch()
    {
        $wdata = [
            'clients.client_id' => $this->input->get('term', TRUE),
            'clients.client_address' => $this->input->get('term', TRUE),
            'clients.client_city' => $this->input->get('term', TRUE),
            'clients.client_state' => $this->input->get('term', TRUE),
            'clients_contacts.cc_name' => $this->input->get('term', TRUE),
            'clients.client_name' => $this->input->get('term', TRUE),
        ];
        if(numberFrom($this->input->get('term', TRUE)) && strlen(numberFrom($this->input->get('term', TRUE))) >= 8) {
            $wdata['clients_contacts.cc_phone'] = numberFrom($this->input->get('term', TRUE));
        }
        $clients = $this->mdl_clients->find_all_with_limit($wdata, 100, 0, '', [], 'clients.client_id');

        $response = [];
        $lead = new Lead();
        foreach ($clients as $client) {
            $leads = $lead->getLeadsByDefaultStatus([['leads.client_id', '=', $client->client_id]]);
            $label = '#' . $client->client_id . ', ' . $client->client_name . ' - ' . $client->client_address . ', ' . $client->client_city . ', ' . $client->client_state;
            $response[] = [
                'label' => $label,
                'value' => $label,
                'id' => $client->client_id,
                'address' => $client->client_address,
                'city' => $client->client_city,
                'state' => $client->client_state,
                'zip' => $client->client_zip,
                'leads' => $leads,
                'cc_name'=>$client->cc_name,
                'cc_phone_view'=>numberTo($client->cc_phone)
            ];
        }
        return $this->response(['status' => 'ok', 'data' => $response]);
    }

    public function update_brand(){
        if(!$this->input->post('brand_client_id') || !$this->input->post('client_brand_id'))
            return $this->response('Request is not walid', 400);

        $data = ['client_brand_id'=>$this->input->post('client_brand_id')];
        $this->mdl_clients->update_client($data, ['client_id' => $this->input->post('brand_client_id')]);
        return $this->response('ok', 200);
    }

    public function search_clients(){
        $SearchModel = new Search();
        $data['results'] = $SearchModel->global_search_client(trim($this->input->post('search_query')))->paginate(100)->items();

        $result = [
            'status' => 'success',
            'message' => '<tr><td class=\'success\'>Successful request</td></tr>',
            'count' => count($data['results']),
            'result' => json_encode([
                'html' => $this->load->view('dashboard/ajax_global_search', $data, TRUE)
            ])
        ];

        return $this->response($result);
    }

    /**
     * Get autotax for US
     */
    function ajax_get_us_autotax() {
        $address = $this->input->post('address');
        $city = $this->input->post('city');
        $zip = $this->input->post('zip');
        $country = $this->input->post('country');
        $state = $this->input->post('state');

        if ((!$address || !$city || !$zip || !$country || !$state) || $country !== 'United States') {
            $result['status'] = 'error';
            $result['msg'] = 'No data!';
            if ($country !== 'United States') {
                $result['msg'] = 'Not USA!';
            }
            die(json_encode($result));
        }

        $result['status'] = 'ok';

        $addressForAutoTax = [
            'Address' => $address,
            'City' => $city,
            'State' => $state,
            'Zip' => $zip
        ];
        $autoTax = $this->estimateactions->getTaxForUSCompany($addressForAutoTax);
        if (!empty($autoTax)) {
            $result['estimatedTax'] =  $autoTax['estimate'];
        }

//        // test result
//        $result['estimatedTax'] = [
//            'id' => "Tax (8.875%)",
//            'text' => "Tax (8.875%)",
//            'name' => "Tax",
//            'rate' => 1.08875,
//            'value' => 8.875
//        ];

        die(json_encode($result));
    }
}

// End Class

//end of controller.php
?>
