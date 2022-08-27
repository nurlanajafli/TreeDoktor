<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

use application\modules\clients\models\Client;
use application\modules\clients\models\Tag;
use application\modules\references\models\Reference;
use application\modules\tree_inventory\models\TreeInventoryScheme;

class Appclients extends APP_Controller
{

	function __construct()
	{
		parent::__construct();
        $this->load->model('mdl_clients');
		$this->load->model('mdl_leads_status');
		$this->load->model('mdl_leads');
		$this->load->model('mdl_leads_services');
		$this->load->model('mdl_client_tasks');
		$this->load->model('mdl_user');
		$this->load->model('mdl_calls');
		$this->load->model('mdl_sms_messages');
		$this->load->model('mdl_estimates');
		$this->load->model('mdl_workorders');
		$this->load->model('mdl_invoices');
		
		$this->load->library('form_validation');
		$this->load->library('Common/ClientsActions');
	}

	function test() {
	    var_dump($this->user);die;
        make_notes(22660, 'test');
    }


    public function get($page = 1, $limit = 20) {

	    $data = Client::with(['tags'])
            ->permissions()
            ->apiFilterClient(request())
            ->apiOrder()
            ->paginate($limit, [], 'page', $page);

	    return $this->response($data);
    }
    
    public function getOld($limit = 20, $offset = 0, $only_own = 0) {
        $limit = intval($limit);
        $offset = intval($offset);

		$filters = [];
		
		if (isset($_POST['filters']) && !empty($_POST['filters'])) {
			$filters = $_POST['filters'];
			if(isset($filters['search'])){
				$checkPhone = preg_match('/(\(?\d{3}\)?[\- ]?)?[\d\- ]{7,10}$/is', trim($filters['search']));
				if($checkPhone)
                $filters['search'] = numberFrom($filters['search']);
			}
                
        }
		
		if($only_own == 1){
			$filters['client_maker'] = $this->user->id;
		} 
		
		if (isset($_POST['filters']) && isset($_POST['filters']['brand_id']) && !empty($_POST['filters']['brand_id'])) { 
			$filters['client_brand_id'] = $_POST['filters']['brand_id'];
		}

		$totalRows = $this->mdl_clients->record_count_app($filters);
        if($offset > $totalRows)
            $this->response([
                'status' => FALSE,
                'message' => 'Incorrect Offset Value'
            ], 400);
		
		$order = 'client_id DESC';
		if(isset($_POST['order_field']) && $_POST['order_field'] != ''){            
            $dir = 'asc';
            if(isset($_POST['dir']) && $_POST['dir'] != ''){
                $dir = $_POST['dir'];
            }
            $order = $_POST['order_field'] . ' ' . $dir;
        }
		
		$clients = $this->mdl_clients->get_clients_app($filters, $limit, $offset, $order);

        array_map(function($client) {
            $client->client_tags = isset($client->client_tags) ? explode(',', $client->client_tags) : [];
        }, $clients);

        if($clients !== false){
			$this->response([
				'status' => TRUE,
				'total_rows' => $totalRows,
				'limit' => $limit,
				'offset' => $offset,
				'data' => $clients
			], 200);
		} else {
			$this->response([
				'status' => FALSE,
				'message' => 'Error getting a list of clients'
			], 400);
		}       
		
    }

    /**
     * deprecated
     * @param null $id
     */
    // TODO: remove later as deprecated
    public function fetch($id = null) {
		if($id == null || $this->mdl_clients->check_by_id($id) == FALSE){
			$this->response([
					'status' => FALSE,
					'message' => 'Wrong ID provided'
				], 400);			
		} else {		
			$id = intval($id);
	
			$data['client_data'] = $this->mdl_clients->get_client_app($id); //Get client details
            $data['client_tags'] = Client::find($id)->tags()->pluck('name')->toArray();

			$data['client_contacts'] = $this->mdl_clients->get_client_contacts_app(array('cc_client_id' => $id)); //Get client contacts
						
			$data['client_leads'] = $this->mdl_leads->get_client_leads_app($id, [], 'leads.lead_id DESC')->result(); //Get client lead
			
			$data['client_estimates'] = $this->mdl_estimates->get_client_estimates_app($id); //Get client estimates
			
			if($data['client_estimates'] && $data['client_estimates']->num_rows())
			{
				$cl_est = $data['client_estimates']->result();
				foreach($cl_est as $estimate)
				{
					$data['estimates_crews'][] = $this->mdl_estimates->find_estimate_crews_app($estimate->estimate_id);
					$estimate->date_created = date('Y-m-d H:i:s', $estimate->date_created);
					//$data['estimates_discounts'][$estimate['estimate_id']] = $this->mdl_clients->get_discount(array('discounts.estimate_id' => $estimate['estimate_id']));

				}
				
				$data['client_estimates'] = $cl_est;
			}
			else{
				$data['client_estimates'] = [];
			}
			
			
		
			$data['client_workorders'] = $this->mdl_workorders->get_client_workorders_app($id)->result(); //Get client invoices
			$data['total_estimates_sum'] = $this->mdl_estimates->get_total_for_estimate_by(array('estimates.client_id' => $id));
			$data['total_confirmed_estimates_sum'] = $this->mdl_estimates->get_total_for_estimate_by(array('estimates.client_id' => $id, 'estimates.status' => 6));
			$data['client_invoices'] = $this->mdl_invoices->get_client_invoices_app($id); //Get client invoices

			if($data['client_invoices'] && $data['client_invoices']->num_rows())
			{
				/*foreach($data['client_invoices']->result() as $invoice) {
					$data['invoice_interest_data'][$invoice->estimate_id] = $this->mdl_invoices->getInterestData($invoice->id);
					$data['payments_data'][$invoice->estimate_id] = $this->mdl_clients->get_payments(array('client_payments.estimate_id' => $invoice->estimate_id));
				}*/
				
				$data['client_invoices'] = $data['client_invoices']->result();
			} else {
				$data['client_invoices'] = [];
			}
			
			
			
			/*$data['client_payments']*/$client_payments = $this->mdl_clients->get_payments(array('clients.client_id' => $id)); //Get client payments

			foreach (/*$data['client_payments']*/$client_payments as $payment)
				$data['paid_for_estimate'][$payment['estimate_id']] = isset($data['paid_for_estimate'][$payment['estimate_id']]) ? $data['paid_for_estimate'][$payment['estimate_id']] + $payment['payment_amount'] : $payment['payment_amount'];

            $data['client_projects'] = TreeInventoryScheme::where('tis_client_id', $id)->get()->toArray();
			$this->response([
				'status' => TRUE,
				'data' => $data
			], 200);
		} 
    }

    /**
     * Get client details
     *
     * @param int|null $id
     */
    public function details(int $id = null) {
        $client = null;

        if ($id !== null) {
            $client = Client::find($id);
        }

        if ($client === null) {
            $this->response([
                'status' => FALSE,
                'message' => 'Wrong ID provided'
            ], 400);
        } else {
            $details = Client::getApiDetails($id);
            $details['client_tags'] = $client->tags()->pluck('name')->toArray();

            $this->response([
                'status' => TRUE,
                'data' => $details
            ], 200);
        }
    }
	
	public function search() {
		$search = $_POST['search_keyword'];
		$phone = preg_match('/(\(?\d{3}\)?[\- ]?)?[\d\- ]{7,10}$/is', $search);
		
		if($phone) {
			$search = numberFrom($search);
		}
			
		$result = $this->mdl_clients->client_search_app($search);
		$this->response([
			'status' => TRUE,
			'data' => $result
		], 200);
	}
	
    public function create() {
		//validation
		if($this->input->post('client_name') != null && !empty($this->input->post('client_name'))){
			foreach($this->input->post('client_name') as $key=>$value){
				$this->form_validation->set_rules('client_name['.$key.']', 'Contact name', 'trim|max_length[60]|required');
				
				$phone_check = trim($this->input->post('client_phone')[$key]);
				$email_check = trim($this->input->post('client_email')[$key]);

				if($phone_check == null || $phone_check == ''){					
					if($email_check == null || $email_check == ''){
						return $this->response([
							'status' => FALSE,
							'message' => 'Validation errors',
							'data' => ['client_name['.$key.']' => 'At least one of the fields: (phone or email) is required for the contact']
						], 400);						
					} else {
						if(!filter_var($email_check, FILTER_VALIDATE_EMAIL))
						{
							return $this->response([
								'status' => FALSE,
								'message' => 'Validation errors',
								'data' => ['client_email['.$key.']' => 'Contact email is not valid']
							], 400);
						}
					}
				}			
					
			}
		} else {
			return $this->response([
				'status' => FALSE,
				'message' => 'Validation errors',
				'data' => ['No contact provided']
			], 400);
		}

		$this->form_validation->set_rules('new_client_name', 'Name', 'trim|required');
        $this->form_validation->set_rules('new_client_source', 'Client source', 'trim');
        $this->form_validation->set_rules('client_referred_by', 'Client Referred By', 'trim');
        $this->form_validation->set_rules('new_client_type', 'Client type', 'trim|required');
        $this->form_validation->set_rules('reffered', '"How did you hear about us?"', 'trim|required');

        $this->form_validation->set_rules('new_client_main_intersection', 'new_main_intersection', 'trim');
        $this->form_validation->set_rules('new_client_address', 'Client address', 'trim|required');
        $this->form_validation->set_rules('new_client_city', 'Client city', 'trim|required');
        $this->form_validation->set_rules('new_client_state', 'Client state', 'trim');
        $this->form_validation->set_rules('new_client_zip', 'Client zip', 'trim|required');
        $this->form_validation->set_rules('new_client_country', 'Client Country', 'trim|required');

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
		//validation
		
		if ($this->form_validation->run() == true) {
									
			$post = $this->input->post(NULL, TRUE);
			
			$all_contacts_data = [];
			
			foreach ($this->input->post('client_name') as $key => $value) {
				if (!empty($this->input->post('client_name')[$key]) || !empty($this->input->post('client_phone')[$key]) || !empty($this->input->post('client_email')[$key])) {
	

					$phone_to_save = null;
					$phone_to_save_clean = null;

                    if(isset($this->input->post('client_phone')[$key]) && $this->input->post('client_phone')[$key]){
						$phone_to_save = numberFrom($this->input->post('client_phone')[$key]);
						$phone_to_save_clean = substr($phone_to_save, 0, config_item('phone_clean_length'));
					}

                    $email = null;
                    $email_exists = false;

                    if (isset($this->input->post('client_email')[$key]) && $this->input->post('client_email')[$key]) {
                        $email = $this->input->post('client_email')[$key];
                        $email_exists = check_email_exists($email);
                    }
					
					$contact_data['cc_title'] = isset($this->input->post('client_title')[$key]) ? $this->input->post('client_title')[$key] : NULL;
					$contact_data['cc_name'] = isset($this->input->post('client_name')[$key]) ? $this->input->post('client_name')[$key] : NULL;
					$contact_data['cc_phone'] = $phone_to_save;
					$contact_data['cc_phone_clean'] = $phone_to_save_clean;
					$contact_data['cc_email'] = $email ?? null;
					$contact_data['cc_email_check'] = $email_exists ?? null;
					$contact_data['cc_print'] = isset($this->input->post('client_print')[$key]) ? $this->input->post('client_print')[$key] : 0;

					$all_contacts_data[] = $contact_data;
				}
			}		
			
			//Checkboxes:
			$lead_data['lead_created_by'] = $this->user->firstname . " " . $this->user->lastname;
			$lead_data['lead_date_created'] = date('Y-m-d H:i:s');
	
			$lead_data['lead_address'] = $this->input->post('new_address') ? $this->input->post('new_address', TRUE) : $this->input->post('new_client_address', TRUE);
			$lead_data['lead_city'] = $this->input->post('new_city') ? $this->input->post('new_city', TRUE) : $this->input->post('new_client_city', TRUE);
			$lead_data['lead_state'] = $this->input->post('new_state') ? $this->input->post('new_state', TRUE) : $this->input->post('new_client_state', TRUE);
			$lead_data['lead_zip'] = $this->input->post('new_zip') ? $this->input->post('new_zip', TRUE) : $this->input->post('new_client_zip', TRUE);
			$lead_data['lead_country'] = $this->input->post('new_country') ? $this->input->post('new_country', TRUE) : $this->input->post('new_client_country', TRUE);

            if($this->input->post('new_address'))
                $lead_data['lead_add_info'] = $this->input->post('lead_add_info');
            elseif($this->input->post('new_client_main_intersection'))
                $lead_data['lead_add_info'] = $this->input->post('new_client_main_intersection');
            else
                $lead_data['lead_add_info'] = '';


			$lead_data['lead_scheduled'] = $this->input->post('lead_scheduled') ? 1 : 0;
	
			$lead_data['lead_call'] = $this->input->post('lead_call') ? 1 : 0;
			$lead_data['lead_reffered_client'] = NULL;
			$lead_data['lead_reffered_user'] = NULL;
			$lead_data['lead_reffered_by'] = NULL;

            $userClientRefferences = Reference::where('is_client_active', '1')->orWhere('is_user_active', '1')->get();

			if ($this->input->post('reffered') != '') {
			    $reffered = $this->input->post('reffered');
                $lead_data['lead_reffered_by'] = $this->input->post('reffered');
				if ($userClientRefferences && $userClientRefferences->where('is_client_active', '1')->first() && $reffered == $userClientRefferences->where('is_client_active', '1')->first()->getAttribute('id')) {
					$lead_data['lead_reffered_client'] = $this->input->post('reff_id');
				} elseif ($userClientRefferences && $userClientRefferences->where('is_user_active', '1')->first() && $reffered == $userClientRefferences->where('is_user_active', '1')->first()->getAttribute('id')) {
					$lead_data['lead_reffered_user'] = $this->input->post('reff_id');
				} elseif ($reffered == 'other') {
                    $lead_data['lead_reffered_by'] = $this->input->post('other_comment');
                } else {
                    $lead_data['lead_reffered_by'] = $reffered;
                }
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
	
			$lead_data['preliminary_estimate'] = $this->input->post('preliminary_estimate');
			$lead_data['timing'] = strip_tags($this->input->post('new_lead_timing'));
			$lead_data['lead_body'] = !empty($this->input->post('new_client_lead')) ? strip_tags($this->input->post('new_client_lead')) : '';
			$lead_data['lead_priority'] = strip_tags($this->input->post('new_lead_priority'));
			$lead_data['lead_author_id'] = $this->user->id;

            $idsStr = null;
            if ($this->input->post('est_services')) {
                $idsStr .= $this->input->post('est_services');
            }
            if ($this->input->post('est_products')) {
                $idsStr .=  '|' . $this->input->post('est_products');
            }
            if ($this->input->post('est_bundles')) {
                $idsStr .= '|' . $this->input->post('est_bundles');
            }
            $services = explode('|', ltrim($idsStr, '|'));
			
			$preuploaded_files = [];
			if($this->input->post('pre_uploaded_files') != null && count($this->input->post('pre_uploaded_files')) > 0){
				$preuploaded_files = $this->input->post('pre_uploaded_files');
			}
			if($client_lead_id = $this->clientsactions->create($all_contacts_data, $lead_data, $services, $post, $preuploaded_files, true)){
						
				$client_lead_parts = explode('_', $client_lead_id);
				$client_id = $client_lead_parts[0];
				$lead_id = $client_lead_parts[1];

                if (!empty($this->input->post('tag_names'))) {
                    Tag::syncTagsWithClient($this->input->post('tag_names'), $client_id);
                }

				$this->response([
					'status' => TRUE,
					'data' => ['client_id' => $client_id, 'lead_id' => $lead_id]
				], 200);
			} else {
				$this->response([
					'status' => FALSE,
					'message' => 'Error creating a client'
				], 400);
			}
		} else {
			$this->response([
				'status' => FALSE,
				'message' => 'Validation errors',
				'data' => $this->form_validation->error_array()
			], 400);			
		}
    }
	
	public function update($id) {
		
		$this->form_validation->set_rules('client_name', 'Name', 'trim');
		$this->form_validation->set_rules('client_type', 'Type', 'trim');
		$this->form_validation->set_rules('client_unsubscribe', 'Subscription', 'trim');
		$this->form_validation->set_rules('client_address', 'Address', 'trim');
		$this->form_validation->set_rules('client_city', 'City', 'trim');
		$this->form_validation->set_rules('client_state', 'State', 'trim');
		$this->form_validation->set_rules('client_country', 'Country', 'trim');
		$this->form_validation->set_rules('client_zip', 'Postal Code', 'trim');
		$this->form_validation->set_rules('client_lat', 'Latitude', 'trim');
		$this->form_validation->set_rules('client_lng', 'Longitude', 'trim');

		if($this->form_validation->run() == true){
			
			$client_id = (int)($id);
			$data['client_id'] = $client_id;

            if($this->input->post('client_name') != null) {
                $data['client_name'] = $this->input->post('client_name', true);
            }
            if($this->input->post('client_type') != null) {
			    $data['client_type'] = $this->input->post('client_type', true);
            }
            if($this->input->post('client_unsubscribe') != null) {
                $data['client_unsubscribe'] = $this->input->post('client_unsubscribe', true);
            }

            if($this->input->post('client_address') != null) {
                $data['client_address'] = $this->input->post('client_address', true);
            }
            if($this->input->post('client_city') != null) {
                $data['client_city'] = $this->input->post('client_city', true);
            }
            if($this->input->post('client_state') != null) {
                $data['client_state'] = $this->input->post('client_state', true);
            }
            if($this->input->post('client_country') != null) {
                $data['client_country'] = $this->input->post('client_country', true);
            }

            if($this->input->post('client_zip') != null) {
                $data['client_zip'] = $this->input->post('client_zip', true);
            }
            if($this->input->post('client_lat') != null) {
                $data['client_lat'] = $this->input->post('client_lat', true);
            }
            if($this->input->post('client_lng') != null) {
                $data['client_lng'] = $this->input->post('client_lng', true);
            }
            if($this->input->post('client_main_intersection') !== null) {
                $data['client_main_intersection'] = $this->input->post('client_main_intersection', true);
            }

            // default tax
            $data['client_tax_name'] = null;
            $data['client_tax_rate'] = 1;
            $data['client_tax_value'] = 0;
            // client tax
            if($this->input->post('client_tax_name') != null) {
                $data['client_tax_name'] = $this->input->post('client_tax_name', true);
                $data['client_tax_rate'] = floatval($this->input->post('client_tax_rate', true));
                $data['client_tax_value'] = floatval($this->input->post('client_tax_value', true));
            }

//            Tag::syncTagsWithClient([], $client_id);
            Tag::syncTagsWithClient(is_array(request()->client_tags)?request()->client_tags:[], $client_id);

            $data['client_brand_id'] = (int)$this->input->post('client_brand_id');

            $update = $this->clientsactions->update($client_id, $data);

			if($update || strtolower($this->input->post('client_tags_update_status')) === 'updated' ){
				$this->response([
					'status' => TRUE,
					'data' => ['client_id' => $client_id],
                    'message' => 'Data updated'
				], 200);
			}
			elseif ($update === null) {
                $this->response([
                    'status' => TRUE,
                    'data' => ['client_id' => $client_id],
                    'message' => 'Nothing changed'
                ], 200);
            } else {
				$this->response([
					'status' => FALSE,
					'message' => 'Error updating a client'
				], 400);
			}
		} else {
			$this->response([
				'status' => FALSE,
				'message' => 'Validation errors',
				'data' => $this->form_validation->error_array()
			], 400);
		}
		
	}
	
	public function update_address($id) {		
        
		$data['client_address'] = $this->input->post('client_address');
		$data['client_city'] = $this->input->post('client_city');
		$data['client_state'] = $this->input->post('client_state');
		$data['client_country'] = $this->input->post('client_country');

		$data['client_zip'] = $this->input->post('client_zip');
		$data['client_lat'] = $this->input->post('client_lat');
		$data['client_lng'] = $this->input->post('client_lng');
		if($this->input->post('client_main_intersection') != null){
			$data['client_main_intersection'] = $this->input->post('client_main_intersection');
		} else {
			$data['client_main_intersection'] = '';
		}
		
		if($result = $this->clientsactions->update_address($id, $data)){
			$this->response([
				'status' => TRUE,
				'data' => $result
			], 200);
		} else {
			$this->response([
				'status' => FALSE,
				'message' => 'Error updating a client'
			], 400);
		}		
		
	}
	
	public function delete($id) {
		if ($this->user->user_type != "admin"){
            $this->response([
				'status' => FALSE,
				'message' => 'Only admin can delete a client'
			], 400);
		} else {			
			$password = md5($this->input->post('password'));
			$get_user['id'] = $this->user->id;
			$get_user['password'] = $password;
			
			if($this->clientsactions->delete($id, $get_user)){
				$this->response([
					'status' => TRUE,
					'data' => []
				], 200);
			} else {
				$this->response([
					'status' => FALSE,
					'message' => 'Error deleting a client'
				], 400);
			}
			
		}
	}
	
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

            $this->response([
				'status' => FALSE,
				'message' => 'Validation errors',
				'data' => $this->form_validation->error_array()
			], 400);
        } else {
			
			if($note_id = $this->clientsactions->create_note($client_id, $note_data)){
				
				if (isset($_FILES['file']) && !$_FILES['file']['error']) {
					$dir = 'notes_files/' . $client_id . '/' . $note_id . '/';
					$explode = explode(".", $_FILES['file']['name']);
					$name = str_replace('.' . $explode[count($explode) - 1], '', $_FILES['file']['name']);
					$this->mdl_clients->uploadFile($dir, $name, 'file', '*');
				}
				
				$this->response([
					'status' => TRUE,
					'data' => ['client_id' => $client_id, 'note_id' => $note_id]
				], 200);
			} else {
				$this->response([
					'status' => FALSE,
					'message' => 'Error creating a note'
				], 400);
			}
			            
        }
    }
	
	public function delete_note($client_note_id)
    {
        if ($this->user->user_type != "admin"){
            $this->response([
				'status' => FALSE,
				'message' => 'Only admin can delete a note'
			], 400);
		} else {

            if($client_id = $this->clientsactions->delete_note_client($client_note_id)){
				$this->response([
					'status' => TRUE,
					'data' => ['client_id' => $client_id]
				], 200);
			} else {
				$this->response([
					'status' => FALSE,
					'message' => 'Error deleting a note'
				], 400);
			}
        }
    }
	
	public function get_client_leads() {
		$id = $this->input->post('client_id');
        $data = $this->mdl_leads->get_client_leads_app($id, ['lead_statuses.lead_status_estimated'=>0, 'lead_statuses.lead_status_declined'=>0,
															 'lead_statuses.lead_status_active'=>1], 'leads.lead_id ASC')->result();
        $this->response([
            'status' => TRUE,
            'data' => $data
        ], 200);
    }

    public function add_tag()
    {
        $tagName = $this->input->post('tag_name', true);
        $clientId = $this->input->post('client_id', true);

        $this->form_validation->set_rules('tag_name', 'Tag Name', 'trim|required');
        $this->form_validation->set_rules('client_id', 'Client id', 'trim|required');

        if ($this->form_validation->run() === false) {
             $this->response([
                'status' => FALSE,
                'message' => 'Validation errors',
                'data' => $this->form_validation->error_array()
            ], 400);

            return;
        }

        Tag::syncTagWithClient($tagName, $clientId);

        $this->response([
            'status' => true,
            'data' => [
                'client_tags' => Client::find($clientId)->tags->toArray()
            ]
        ], 200);
    }

    public function delete_tag()
    {
        $tagName = $this->input->post('tag_name', true);
        $clientId = $this->input->post('client_id', true);

        $this->form_validation->set_rules('tag_name', 'Tag Name', 'trim|required');
        $this->form_validation->set_rules('client_id', 'Client id', 'trim|required');
        if ($this->form_validation->run() === false) {
            $this->response([
                'status' => FALSE,
                'message' => 'Validation errors',
                'data' => $this->form_validation->error_array()
            ], 400);

            return;
        }

        $client = Client::find($clientId);
        $tag = Tag::where('name', $tagName)->first();

        if (isset($tag)) {
            $client->tags()->detach($tag->tag_id);
        }
        Tag::deleteFreeTags();
        $this->response([
            'status' => true,
            'data' => [
                'client_tags' => Client::find($clientId)->tags->toArray()
            ]
        ], 200);
    }

    public function tags_search()
    {
        $tagName = $this->input->get('tag_name');

        $this->response([
            'status' => true,
            'data' => [
                'tags' => Tag::where('name', 'like', "%{$tagName}%")->get(['tag_id', 'name'])->toArray()
            ]
        ], 200);
    }

    public function all_tags()
    {
        $this->response([
            'status' => true,
            'data' => [
                'tags' => Tag::all()->toArray()
            ]
        ], 200);
    }

    public function get_us_autotax() {
        $address = $this->input->post('address');
        $city = $this->input->post('city');
        $zip = $this->input->post('zip');
        $country = $this->input->post('country');
        $state = $this->input->post('state');

        if ((!$address || !$city || !$zip || !$country || !$state) || $country !== 'United States') {
            $msg = 'No data!';
            if ($country !== 'United States') {
                $msg = 'Not USA!';
            }

            $this->response([
                'status' => FALSE,
                'message' => 'Get autotax error: ' . $msg,
            ], 400);

            return;
        }

        $estimatedTax = null;
        $addressForAutoTax = [
            'Address' => $address,
            'City' => $city,
            'State' => $state,
            'Zip' => $zip
        ];
        $this->load->library('Common/EstimateActions');
        $autoTax = $this->estimateactions->getTaxForUSCompany($addressForAutoTax);
        if (!empty($autoTax)) {
            $estimatedTax =  $autoTax['estimate'];
        }

        // test result
//        $estimatedTax = [
//            'id' => "Tax (8.875%)",
//            'text' => "Tax (8.875%)",
//            'name' => "Tax",
//            'rate' => 1.08875,
//            'value' => 8.875
//        ];

        $this->response([
            'status' => true,
            'data' => [
                'estimatedTax' => $estimatedTax
            ]
        ], 200);
    }
}
