<?php
use application\modules\classes\models\QBClass;
use application\modules\estimates\models\EstimateStatus;
use application\modules\estimates\models\TreeInventoryEstimateServiceWorkTypes;
use application\modules\estimates\models\TreeInventoryEstimateService;
use application\modules\estimates\models\EstimatesService;
use application\modules\estimates\models\Estimate;
use application\modules\tree_inventory\models\TreeInventoryWorkTypes;
use application\modules\invoices\models\Invoice;
use application\modules\workorders\models\Workorder;
use application\modules\workorders\models\WorkorderStatus;

class Mdl_estimates_orm extends JR_Model
{
	protected $_table = 'estimates';
	protected $primary_key = 'estimate_id';
	public $has_many = array('mdl_services_orm' => array('primary_key' => 'estimates_services.estimate_id', 'model' => 'mdl_services_orm'));
	public function __construct() {
		parent::__construct();
	}

	function get($id) {
        $totalsSubQuery = $this->calcQuery([$this->_table . '.' .$this->primary_key => $id]);
        $this->_database->where($this->_table . '.' . $this->primary_key, $id);
        $this->_database->join("({$totalsSubQuery}) totals", 'estimates.estimate_id = totals.estimate_id', 'left', FALSE);
        $this->_database->join("leads", 'estimates.lead_id = leads.lead_id', 'left', FALSE);
        $this->_database->join("clients c", 'estimates.client_id = c.client_id', 'left', FALSE);
        $this->_database->join("clients_contacts cc", 'estimates.client_id = cc.cc_client_id AND cc.cc_print = 1', 'left', FALSE);
        $this->_database->select('estimates.*, totals.*, leads.*, cc.cc_name, cc.cc_phone, cc.cc_phone_clean, cc.cc_email, c.client_name, c.client_brand_id');
        return $this->_database->get($this->_table)->row();
    }

    function getCompletedOnly($id) {
        $totalsSubQuery = $this->calcQuery([$this->_table . '.' .$this->primary_key => $id, 'estimates_services.service_status' => 2]);
        $this->_database->where($this->_table . '.' . $this->primary_key, $id);
        $this->_database->join("({$totalsSubQuery}) totals", 'estimates.estimate_id = totals.estimate_id', 'left', FALSE);
        $this->_database->join("leads", 'estimates.lead_id = leads.lead_id', 'left', FALSE);
        return $this->_database->get($this->_table)->row();
    }

	function get_full_estimate_data($where, $service = TRUE, $files = FALSE, $permissions = true)
	{
        if(array_key_exists('estimate_id', $where))
		{
			$where['estimates.estimate_id'] = $where['estimate_id'];
			unset($where['estimate_id']);
		}
         
		$this->load->model('mdl_clients');
		$this->load->model('mdl_expenses_orm');
		$this->load->model('mdl_services');
		$this->load->model('mdl_crews_orm');
		$this->load->model('mdl_equipment_orm');
		$this->load->model('mdl_expenses_orm');
		$this->load->model('mdl_bundles_services');
        $this->load->model('mdl_estimates_bundles');

        /*$this->_database->select('SUM(payment_amount) AS total, client_payments.estimate_id');
        $this->_database->from('client_payments');
        $this->_database->join('estimates','estimates.estimate_id = client_payments.estimate_id');
        $this->_database->group_by('client_payments.estimate_id');
        $subquery = $this->_database->_compile_select();
        $this->_database->_reset_select();*/

        $clientPermissionsSubQuery = $this->mdl_clients->getPermissionsSubQuery();
        $totalsSubQuery = $this->calcQuery($where, [
            [
                'table' => 'leads',
                'condition' => 'leads.lead_id = estimates.lead_id',
            ], [
                'table' => 'clients',
                'condition' => 'clients.client_id = estimates.client_id',
            ]
        ]);

        $this->_database->select('estimates.*, discount_id, discount_amount, discount_date, discount_percents, FROM_UNIXTIME(estimates.date_created) as formatted_date_created, invoices.interest_status, invoices.in_status, clients.client_brand_id, clients.client_name, clients.client_type, clients.client_zip, 
		    estimate_statuses.est_status_confirmed, estimate_statuses.est_status_name as status, estimate_statuses.est_status_id as status_id, est_status_default, workorders.date_created as workorder_date_created,
		    clients_contacts.cc_name, clients_contacts.cc_phone, clients_contacts.cc_phone_clean, clients_contacts.cc_email,
		    leads.lead_id, leads.lead_body, leads.lead_address, leads.lead_city, leads.lead_state, leads.lead_zip, leads.lead_country, leads.latitude as lat, leads.longitude as lon, leads.lead_tax_name, leads.lead_tax_value, leads.lead_tax_value, leads.lead_add_info,
            users.id, users.user_type, users.emailid, users.firstname, users.lastname, users.active_status, users.picture, users.color, users.color, users.user_email, users.user_signature, invoice_statuses.default, invoice_statuses.is_hold_backs, invoice_statuses.is_sent, invoice_statuses.is_overdue, invoice_statuses.completed,
            totals.*');
        $this->_database->join('leads', 'estimates.lead_id=leads.lead_id', 'left');
        $this->_database->join('discounts', 'estimates.estimate_id = discounts.estimate_id', 'left');
        $this->_database->join('clients', 'estimates.client_id=clients.client_id');
        $this->_database->join('clients_contacts', 'clients_contacts.cc_client_id=clients.client_id AND cc_print=1', 'left');
        $this->_database->join('estimate_statuses', 'estimate_statuses.est_status_id=estimates.status', 'left');
        $this->_database->join('workorders', 'estimates.estimate_id=workorders.estimate_id', 'left');
        $this->_database->join('invoices', 'estimates.estimate_id=invoices.estimate_id', 'left');
        $this->_database->join('invoice_statuses', 'invoices.in_status = invoice_statuses.invoice_status_id', 'left');
        $this->_database->join('users', 'estimates.user_id=users.id', 'left');
        $this->_database->join('estimates_bundles', 'estimates.user_id=estimates_bundles.eb_service_id', 'left');
        $this->_database->join("({$totalsSubQuery}) totals", 'estimates.estimate_id = totals.estimate_id', 'left', FALSE);
        $this->_database->where($where);

        if(is_cl_permission_owner() && $clientPermissionsSubQuery && $permissions) {
            $this->_database->join('(' . $clientPermissionsSubQuery .') perm', 'perm.client_id = estimates.client_id', 'left');
            $this->_database->where('perm.client_id IS NOT NULL');
        }

        $result = $this->_database->get($this->_table)->result();

		if($service)
		{
		    $bundlesRecords = [];
			foreach ($result as $key => &$row)
			{
				$order = array();
				$orderPriority = array();
				$row = $this->trigger('after_get', $row, ($key == count($result) - 1));//countOk
                if($files)
                    $row->files = bucketScanDir('uploads/clients_files/' . $row->client_id . '/estimates/' . $row->estimate_no . '/', TRUE);

                // checking if scheme create on app or web
                $row->estimate_scheme_web = null;
                if(isset($row->estimate_scheme) && !empty($row->estimate_scheme)) {
                    $scheme = json_decode($row->estimate_scheme);
                    $row->estimate_scheme_web = 0;
                    if(!empty($scheme) && is_object($scheme) && isset($scheme->html))
                        $row->estimate_scheme_web = 1;
                }
				foreach($row->mdl_services_orm as &$service)
				{
				    if($files)
                        $service->files = bucketScanDir('uploads/clients_files/' . $row->client_id . '/estimates/' . $row->estimate_no . '/' . $service->id . '/', TRUE);
				    $service->service = $this->mdl_services->get(array('service_id' => $service->service_id));
					$this->_database->select('estimates_services_crews.*, crews.*, estimates_services_crews.crew_id as estimate_service_crew_id');
					$this->_database->join('crews', 'crews.crew_id=estimates_services_crews.crew_user_id', 'left');
					$service->crew = $this->mdl_crews_orm->get_many_by(array('crew_service_id' => $service->id));
					
					//$this->_database->join('estimate_equipment', 'estimate_equipment.eq_id=estimates_services_equipments.equipment_item_id', 'left');
					//SETUP
					$this->_database->select('estimates_services_equipments.*, vehicles.*, trailer.vehicle_name as trailer_name, trailer.vehicle_id as trailer_id, trailer.vehicle_per_hour_price as trailer_per_hour_price');
					$this->_database->join('vehicles', 'vehicles.vehicle_id=estimates_services_equipments.equipment_item_id', 'left');
					$this->_database->join('vehicles as trailer', 'trailer.vehicle_id=estimates_services_equipments.equipment_attach_id', 'left');
					//$this->_database->join('vehicles as tools', 'tools.vehicle_id=estimates_services_equipments.equipment_attach_tool', 'left');
					//SETUP END
					$service->equipments = $this->mdl_equipment_orm->get_many_by(array('equipment_service_id' => $service->id));
                    $service->expenses = $this->mdl_expenses_orm->get_many_by(['ese_estimate_service_id' => $service->id]);
                    $checkBundleRecords = $this->mdl_estimates_bundles->get_by(['eb_service_id' => $service->id]);
                    if($checkBundleRecords && !empty($checkBundleRecords)) {
                        $bundlesRecords[$service->id] = $checkBundleRecords->eb_bundle_id;
                    }
					$orderId[]  = $service->id;
					$orderPriority[]  = $service->service_priority;
                    $service->tree_inventory = TreeInventoryEstimateService::where('ties_estimate_service_id', $service->id)->with(['tree'])->first();
                    if(!empty($service->tree_inventory)){
                        $tree_inventory = $service->tree_inventory;
                        $service->profile_estimate_service_ti_title = $service->estimate_service_ti_title;
                        $service->profile_service_description = $service->service_description;
                        if(!empty($tree_inventory->ties_priority) && !empty($service->estimate_service_ti_title))
                            $service->profile_estimate_service_ti_title .=  ', Priority: ' . $tree_inventory->ties_priority;
                        $workTypes = TreeInventoryEstimateServiceWorkTypes::where('tieswt_ties_id', $tree_inventory->ties_id)->with('work_type')->get()->pluck('work_type')->pluck('ip_name')->toArray();
                        if(!empty($workTypes)  && is_array($workTypes)) {
                            $workTypesDescription = 'Work Types: ' . implode(', ', $workTypes) . '<br>';
                            $service->profile_service_description = $workTypesDescription . $service->service_description;
                        }
                    }
				}
				//array_multisort($orderId, SORT_DESC, $row->mdl_services_orm);
				array_multisort($orderPriority, SORT_ASC, $row->mdl_services_orm);
			}
            
            if(isset($result[0]->mdl_services_orm)){
                $services = $result[0]->mdl_services_orm;
                foreach ($result[0]->mdl_services_orm as $record){
                    if(!empty($record->estimate_service_class_id)){
                        $class = QBClass::where('class_id', $record->estimate_service_class_id)->first();
                        if(!empty($class))
                            $record->estimate_service_class_name = $class->class_name;
                    }
                    if(key_exists($record->id, $bundlesRecords)){
                        foreach ($services as $record2){
                            if($record2->id == $bundlesRecords[$record->id])
                                $record2->bundle_records[] = $record;
                        }
                        unset($result[0]->mdl_services_orm[array_search($record, $result[0]->mdl_services_orm)]);
                    }
                }
            }
		}

		return $result;
	}
	
	function get_full_service_data($where)
	{
		$this->load->model('mdl_services');
        $this->load->model('mdl_expenses_orm');
		// event_services
		$service = $this->mdl_services_orm->get($where);
		if(!$service)
			return array();
		//echo '<pre>'; var_dump($service->service_id); die;
		$service->service = $this->mdl_services->get(array('service_id' => $service->service_id));
		//var_dump($this->db->last_query()); die;
		$this->_database->join('crews', 'crews.crew_id=estimates_services_crews.crew_user_id', 'left');
		$service->crew = $this->mdl_crews_orm->get_many_by(array('crew_service_id' => $service->id));
		$this->_database->join('estimate_equipment', 'estimate_equipment.eq_id=estimates_services_equipments.equipment_item_id', 'left');
		$service->equipments = $this->mdl_equipment_orm->get_many_by(array('equipment_service_id' => $service->id));
        $service->expenses = $this->mdl_expenses_orm->get_many_by(['ese_estimate_service_id' => $service->id]);
		return $service;
	}

	function _explodePdfFiles($result) {

		foreach ($result as $key => &$value) {
			//echo '<pre>'; var_dump($value); die;
			if($value->estimate_pdf_files) {
//				$estClPath = 'uploads/clients_files/' . $value->client_id . '/estimates/' . $value->estimate_no . '/tmp/';
				$files = isset($value->estimate_pdf_files) ? json_decode($value->estimate_pdf_files) : [];
				if(!$files)
					$files = [];
				$newFiles = $pdfs = [];

				foreach ($files as $k => $file) {
				    $type = getMimeType($file);
					if(!is_bucket_file($file) || strripos($type, 'audio') !== false || strripos($type, 'video') !== false)
						continue;
					$newFiles[] = $file;
				}
				$value->estimate_pdf_files = json_encode($newFiles);
			}
		}
		return $result;
	}

	function save_estimate() {
	    $CI =& get_instance();
	    $this->load->model('mdl_services');
	    $this->load->model('mdl_services_orm');
	    $this->load->model('mdl_crews_orm');
	    $this->load->model('mdl_equipment_orm');
	    $this->load->model('mdl_expenses_orm');
	    $this->load->model('mdl_clients');
        $this->load->model('mdl_crews');
        $this->load->model('mdl_est_equipment');
        $this->load->model('mdl_vehicles');
        $this->load->model('mdl_invoices');
        $this->load->model('mdl_leads');
        $this->load->model('mdl_followups');
        $this->load->model('mdl_leads_status');
        $this->load->model('mdl_paint');
		$this->load->model('mdl_user');
		$this->load->model('mdl_bundles_services');
        $this->load->model('mdl_estimates_bundles');
        $this->load->model('mdl_tree_inventory_orm', 'tree_inventory');

        $userId = $this->session->userdata('user_id') ? $this->session->userdata('user_id') : 0;
        if(isset($CI->token))
            $userId = $CI->user->id;

        $lead_id = $this->input->post('lead_id');
        $client_id = $this->input->post('client_id');
        $estimate_id = $this->input->post('estimate_id');

        $createWo = $this->input->post('create_wo')??false;
        $createInvoice = $this->input->post('create_invoice')??false;

        $copyEst = $this->input->post('copy_estimate')??false;
        $copyWo = $this->input->post('copy_wo')??false;
        $copyInvoice = $this->input->post('copy_invoice')??false;
        $old_estimate_id = $this->input->post('old_estimate_id')??false;
        $new_client_id = $this->input->post('new_client_id')??$client_id;
        $estimate_pdf_files = [];
		
		if(!$lead_id && $this->input->post('tmp_lead_id')){			
			if(!$estimate_id){ //if not an existing estimate
				$this->load->model('mdl_leads');
				//create lead
				$lead_data = [];
				$lead_data['lead_author_id'] = $lead_data['lead_estimator'] = (int)$userId;
				$placeholder = 'Auto created';
				$lead_data['lead_address'] = $this->input->post('street');//$placeholder;
				$lead_data['lead_city'] = $this->input->post('city');//$placeholder;
				$lead_data['lead_state'] = $this->input->post('state');//$placeholder;
				$lead_data['lead_zip'] = $this->input->post('zip');//$placeholder;
				$lead_data['lead_country'] = $this->input->post('country') ? $this->input->post('country') : null;
				$lead_data['client_id'] = $this->input->post('client_id');
				$lead_data['lead_body'] = $placeholder;
				$lead_data['latitude'] = $this->input->post('lat');
				$lead_data['longitude'] = $this->input->post('lon');
				$lead_data['timing'] = 'Right Away';
				$lead_user = $this->mdl_user->getUserById($userId)[0];
				$lead_data['lead_created_by'] = $lead_user['firstname'] . " " . $lead_user['lastname'];
				$lead_data['lead_date_created'] = date('Y-m-d H:i:s');                    
				$lead_data['lead_priority'] = 'Regular';
				$lead_data['lead_assigned_date'] = $lead_data['lead_postpone_date'] = date('Y-m-d');
				$lead_data['preliminary_estimate'] = 'medium';
				$lead_data['lead_status'] = 'Estimated';
				$lead_status = $this->mdl_leads_status->get_by(['lead_status_estimated' => 1]);
                $lead_data['lead_status_id'] = $lead_status->lead_status_id;				
				$lead_data['lead_reason_status_id'] = 0;
				$lead_data['lead_reffered_by'] = 'no_info_provided';
				$lead_id = $this->mdl_leads->insert_leads($lead_data);
				if ($lead_id) {
					$_POST['lead_id'] = $lead_id;
					$lead_no = str_pad($lead_id, 5, '0', STR_PAD_LEFT);
					$lead_no = $lead_no . "-L";
					$update_data = array("lead_no" => $lead_no);
					$wdata = array("lead_id" => $lead_id);            
					$this->mdl_leads->update_leads($update_data, $wdata);
				}
			}
		} 

        if($estimate_id) {
            $estimate_data = $this->get($estimate_id);
            $estimate_pdf_files = $estimate_data->estimate_pdf_files ? json_decode($estimate_data->estimate_pdf_files, TRUE) : [];
        } else {
			if($lead_id) {
				$estimate_data = $this->get_by(['lead_id' => $lead_id]);
				if($estimate_data)
					return FALSE;
			}
        } 
        
        
        $data['estimate_review_date'] = date('Y-m-d');
        $data['estimate_review_number'] = $this->input->post('estimate_review_number');
        $data['estimate_no'] = str_pad($lead_id, 5, '0', STR_PAD_LEFT);
        $data['estimate_no'] = $data['estimate_no'] . "-E";
        $data['estimate_item_estimated_time'] = strip_tags($this->input->post('estimate_item_estimated_time'));
        $data['estimate_item_equipment_setup'] = strip_tags($this->input->post('estimate_item_equipment_setup'));
        $data['estimate_item_note_crew'] = ''; //need delete but no default val in db
        $data['estimate_crew_notes'] = trim(strip_tags($this->input->post('estimate_crew_notes')));

        $data['estimate_item_note_payment'] = strip_tags($this->input->post('estimate_item_note_payment'));
        $data['full_cleanup'] = $this->input->post('clean_up') ? 'yes' : 'no';
        $data['brush_disposal'] = $this->input->post('disposal_brush') ? 'yes' : 'no';
        $data['leave_wood'] = $this->input->post('disposal_wood') ? 'yes' : 'no';

        $data['estimate_planned_company_cost'] = getAmount($this->input->post('estimate_planned_company_cost'));
        $data['estimate_planned_crews_cost'] = getAmount($this->input->post('estimate_planned_crews_cost'));
        $data['estimate_planned_equipments_cost'] = getAmount($this->input->post('estimate_planned_equipments_cost'));
        $data['estimate_planned_extra_expenses'] = getAmount($this->input->post('estimate_planned_extra_expenses'));
        $data['estimate_planned_overheads_cost'] = getAmount($this->input->post('estimate_planned_overheads_cost'));
        $data['estimate_planned_profit'] = getAmount($this->input->post('estimate_planned_profit'));
        $data['estimate_planned_profit_percents'] = getAmount($this->input->post('estimate_planned_profit_percents'));
        $data['estimate_planned_tax'] = getAmount($this->input->post('estimate_planned_tax'));
        $data['estimate_planned_total'] = getAmount($this->input->post('estimate_planned_total'));
        $data['estimate_planned_total_for_services'] = getAmount($this->input->post('estimate_planned_total_for_services'));

        $data['estimate_tax_name'] = $this->input->post('tax_name');
        $data['estimate_tax_value'] = floatval($this->input->post('tax_value'));
        $data['estimate_tax_rate'] = floatval($this->input->post('tax_rate'));
        $data['estimate_hst_disabled'] = isset($estimate_data) && isset($estimate_data->estimate_hst_disabled) && $this->input->post('estimate_hst_disabled') === FALSE ? (int)$estimate_data->estimate_hst_disabled : (int)$this->input->post('estimate_hst_disabled');
        if($data['estimate_hst_disabled'] === NULL || $data['estimate_hst_disabled'] < 0 || $data['estimate_hst_disabled'] > 2)
            $data['estimate_hst_disabled'] = 0;

        $data['tree_inventory_pdf'] = $this->input->post('tree_inventory_pdf')?1:0;
        $deletedServices = NULL;
        if($this->input->post('deleted_services') && $estimate_id)
        {
            $deleted_services = $this->input->post('deleted_services');
            
            foreach($deleted_services as $val)
            {
                $service = $this->get_full_service_data($val);
                if($estimate_id != $service->estimate_id) {
                    slack_attention_notification("Illegal attempt to delete the service !!! \n"
                        . "Current Estimate:" . $estimate_id . " Trying to delete service:" . $service->id . "\n"
                    );
                    continue;
                }

                if(!empty($service)){
                    /**FOR CLIENT NOTES INFO**/
                    $deletedServices .= '<li>' . (isset($service->service->service_name)?$service->service->service_name:'no name');
                    $deletedServices .= '<ul>';
                    $deletedServices .= '<li>ID: "' . $service->id;
                    $deletedServices .= '<li>Time: "' . ($service->service_time + $service->service_travel_time + $service->service_disposal_time) . 'hrs."';
                    $deletedServices .= '<li>Price: "' . money($service->service_price) . '"';
                    $deletedServices .= '<li>Description: "<small>' . $service->service_description . '</small>"';
                    $deletedServices .= '</ul></li>';
                    /**FOR CLIENT NOTES INFO**/
                }

                $this->mdl_services_orm->delete($val);
                $this->mdl_crews_orm->delete_by(array('crew_service_id' => $val));
                $this->mdl_equipment_orm->delete_by(array('equipment_service_id' => $val));
                $this->mdl_expenses_orm->delete_by(array('ese_estimate_service_id' => $val));

                $path = 'uploads/clients_files/' . $client_id . '/estimates/' . $data['estimate_no'] . '/' . $val . '/';
                bucket_unlink_all($path);
                if(count($estimate_pdf_files)){
                    foreach ($estimate_pdf_files as $key => $value){
                        if(stripos($value, $path) !== false)
                            unset($estimate_pdf_files[$key]);
                    }
                }
            }
        } elseif($this->input->post('deleted_services')) {
            slack_attention_notification("Illegal attempt to delete the services !!! \n"
                . "Current Lead:" . $lead_id . " Trying to delete services:" . implode(',', $this->input->post('deleted_services')) . "\n"
            );
        }


        /********SAVE NEW SCHEME********/
        $sourcePath = 'uploads/tmp/' . $client_id . '/';
        $schemePath = 'uploads/clients_files/' . $client_id . '/estimates/' . str_pad($lead_id, 5, '0', STR_PAD_LEFT) . '-E/pdf_estimate_no_' . str_pad($lead_id, 5, '0', STR_PAD_LEFT) . '-E_scheme.png';
        if(!$estimate_id) {
            if($this->input->post('presave_scheme') !== false && is_array($this->input->post('presave_scheme'))) {
                $presave_scheme = $this->input->post('presave_scheme');
                $presave_scheme['result'] = isset($presave_scheme['result']) ? str_replace(base_url(), '', $presave_scheme['result']) : null;
                $presave_scheme['original'] = isset($presave_scheme['original']) ? str_replace(base_url(), '', $presave_scheme['original']) : null;
                if(isset($presave_scheme['elements']) && $presave_scheme['elements']) {
                    $data['estimate_scheme'] = $presave_scheme['elements'];
                }
                if(isset($presave_scheme['result']) && is_bucket_file($presave_scheme['result'])) {
                    bucket_copy($presave_scheme['result'], $schemePath);
                    bucket_unlink($presave_scheme['result']);
                    $estimate_pdf_files[] = $schemePath;
                }
                if(isset($presave_scheme['original']) && is_bucket_file($presave_scheme['original'])) {
                    $srcPath = 'uploads/tmp/' . $client_id . '/source/' . str_pad($lead_id, 5, '0', STR_PAD_LEFT) . '_scheme.png';
                    if($presave_scheme['original'] != $srcPath) {
                        bucket_copy($presave_scheme['original'], $srcPath);
                        bucket_unlink($presave_scheme['original']);
                    }
                }
            } elseif (is_bucket_file($sourcePath . str_pad($lead_id, 5, '0', STR_PAD_LEFT) . '_scheme.png')) {
                if(is_bucket_file($sourcePath .  str_pad($lead_id, 5, '0', STR_PAD_LEFT) . '_scheme_elements'))
                    $data['estimate_scheme'] = (string) bucket_read_file($sourcePath .  str_pad($lead_id, 5, '0', STR_PAD_LEFT) . '_scheme_elements');
                elseif (is_bucket_file($sourcePath . str_pad($lead_id, 5, '0', STR_PAD_LEFT) . '_source_html'))
                    $data['estimate_scheme'] = (string) bucket_read_file($sourcePath . str_pad($lead_id, 5, '0', STR_PAD_LEFT) . '_source_html');
                $estimate_pdf_files[] = ltrim($schemePath, './');
                bucket_copy($sourcePath . str_pad($lead_id, 5, '0', STR_PAD_LEFT) . '_scheme.png', $schemePath, ['ContentType' => 'image/png']);
                bucket_unlink($sourcePath . str_pad($lead_id, 5, '0', STR_PAD_LEFT) . '_scheme.png');
            }
            if(!empty($data['estimate_scheme']) && !is_string($data['estimate_scheme']))
                $data['estimate_scheme'] = json_encode($data['estimate_scheme']);

            // adding tree inventory file path
            $treeInventoryMapPath = inventory_screen_path($client_id, $lead_id . '_tree_inventory_map.png');
            $treeInventoryMapNewPath = 'uploads/clients_files/' . $client_id . '/estimates/' . str_pad($lead_id, 5, '0', STR_PAD_LEFT) . '-E/pdf_estimate_no_' . str_pad($lead_id, 5, '0', STR_PAD_LEFT) . '-E_tree_inventory_map.png';
            if(is_bucket_file($treeInventoryMapPath)) {
                bucket_copy($treeInventoryMapPath, $treeInventoryMapNewPath, ['ContentType' => 'image/png']);
                array_unshift($estimate_pdf_files, $treeInventoryMapNewPath);
            }
            $treeInventoryMapPath = inventory_screen_path($client_id, $lead_id . '.png');
            if(is_bucket_file($treeInventoryMapPath)) {
                bucket_copy($treeInventoryMapPath, $treeInventoryMapNewPath, ['ContentType' => 'image/png']);
                array_unshift($estimate_pdf_files, $treeInventoryMapNewPath);
            }
        }
        /********SAVE NEW SCHEME********/

        $data['lead_id'] = $lead_id;
        $data['client_id'] = $client_id;
        $data['estimate_brand_id'] = (int)$this->input->post('estimate_brand_id');

        $estimate_balance = 0;
        $data['estimate_provided_by'] = $this->input->post('provided') ? $this->input->post('provided') : 'meeting';

        $this->load->model('mdl_est_status');
        $defaultStatusRow = $this->mdl_est_status->get_by(['est_status_default' => 1]);
        $thisStatus = $this->mdl_leads_status->with('mdl_leads_reason')->get_by(['lead_status_estimated' => 1]);
        $theLead = $this->mdl_leads->find_by_id($lead_id);

        $activeUser = $this->mdl_user->get_usermeta(['active_status' => 'yes', 'system_user' => 0, 'users.id' => $userId])->result();
        if(
            !empty($theLead) &&
            (!$theLead->lead_estimator || $theLead->lead_estimator == 'none') &&
            (bool)$activeUser
        ) {
            $data['user_id'] = $lead_data['lead_estimator'] = (int)$userId;
        }
        elseif(!empty($theLead) &&
            $theLead->lead_estimator){
            $data['user_id'] = $lead_data['lead_estimator'] = $theLead->lead_estimator;
        }
        if(!$estimate_id)
        {
            $data['status'] = $defaultStatusRow->est_status_id;
            $data['date_created'] = time();

            // If we have created or copied an entity above the estimate, then we set the completed status.
            if($createWo || $createInvoice || $copyWo || $copyInvoice){
                $finishedStatus=EstimateStatus::select('est_status_id')->where('est_status_confirmed','1')->first();
                $data['status'] = $finishedStatus->est_status_id;
            }else if($copyEst){
                $data['status'] =intval($copyEst);
            }


            $estimate_id = $this->insert($data);
            $this->load->model('mdl_client_tasks');
            $this->mdl_client_tasks->closeAllLeadTasks($lead_id, $userId);


            if($old_estimate_id && ($copyWo || $copyInvoice)){
                $est_old=Estimate::select('lead_id')->where('estimate_id',$estimate_id)->first()->toArray();
                $workorder = Workorder::where('estimate_id', $old_estimate_id)->first()->toArray();
                $workorder['estimate_id']=$estimate_id;
                $workorder['client_id']=$new_client_id;
                $workorder['workorder_no']=str_pad($est_old['lead_id'], 5, '0', STR_PAD_LEFT).'-W';
                if($copyWo){
                    $workorder['wo_status']=intval($copyWo);
                }else{
                    $finishedStatus=WorkorderStatus::select('wo_status_id')->where('is_finished','1')->first();
                    $data['status'] = $finishedStatus->wo_status_id;
                }
                unset($workorder['id']);
                unset($workorder['last_change']);
                unset($workorder['date_created_view']);
                unset($workorder['days_from_creation']);
                unset($workorder['files_array']);
                $workOrderId=Workorder::insertGetId($workorder);
                $response['workorder_id'] = $workOrderId;
            }

            if($old_estimate_id && $copyInvoice){
                $workorder = Workorder::where('id', $workOrderId)->first();
                if(isset($workorder) && !empty($workorder)) {
                    $invoices = Invoice::where(['estimate_id' => $old_estimate_id])->get()->toArray();
                    if(!empty($invoices)){
                        foreach ($invoices as $invoice){
                            unset($invoice['id']);
                            unset($invoice['last_change']);
                            unset($invoice['date_created_view']);
                            unset($invoice['days_from_creation']);
                            unset($invoice['overdue_date_view']);
                            $invoice['estimate_id']=$estimate_id;
                            $invoice['client_id']=$new_client_id;
                            $invoice['workorder_id']=$workOrderId;
                            $invoice['in_status']=intval($copyInvoice);
                            $invoice['invoice_no']=str_pad($est_old['lead_id'], 5, '0', STR_PAD_LEFT).'-I';
                            Invoice::insert($invoice);
                        }
                    }

                }
            }

            if($createWo || $createInvoice){
                $this->estimateactions->setEstimateId($estimate_id);
                $statusConfirmed = $this->mdl_est_status->get_by(['est_status_confirmed' => 1]);
                if(!empty($statusConfirmed) && is_object($statusConfirmed))
                    $this->estimateactions->changeStatus($statusConfirmed->est_status_id);
                $this->workorderactions->create($estimate_id);
                $workOrderId = $this->workorderactions->getWorkorderId();
                $response['workorder_id'] = $workOrderId;
            }

            if($createInvoice){
                $status = WorkorderStatus::where(['wo_status_active' => 1, 'is_finished' => 1])->first();
                if(isset($workOrderId) && !empty($workOrderId))
                    $workorder = Workorder::where('id', $workOrderId)->first();
                if(!empty($status) && isset($workorder) && !empty($workorder)) {
                    $estimateServices = EstimatesService::where(['estimate_id' => $estimate_id])->get();
                    if(!empty($estimateServices))
                        foreach ($estimateServices as $service)
                            $this->estimateactions->changeEstimateServiceStatus($service->id, 2);
                    $result = $this->workorderactions->setStatus($workorder, $status->wo_status_id);
                    if (!empty($result) && isset($result['invoice_id']) && !empty($result['invoice_id'])) {
                        $response['invoice_id'] = $result['invoice_id'];
                    }
                }
            }
        }
        else {
            $estimate = $this->get($estimate_id);
            if(!empty($estimate) && $estimate->user_id)
                $data['user_id'] = $estimate->user_id;

            $this->update($estimate_id, $data);
        }


        $lead_data['lead_status_id'] = $thisStatus->lead_status_id;
        $lead_wdata['lead_id'] = $lead_id;
        if ($this->mdl_leads->update_leads($lead_data, $lead_wdata)) {

            $this->load->model('mdl_followups');
            $fuRowPost = $this->mdl_followups->get_by(['fu_module_name' => 'leads', 'fu_item_id' => $lead_id, 'fu_status' => 'postponed']);
            $fuRowNew = $this->mdl_followups->get_by(['fu_module_name' => 'leads', 'fu_item_id' => $lead_id, 'fu_status' => 'new']);
            //echo '<pre>'; var_dump($fuRowNew); die;
            if($fuRowNew && !empty($fuRowNew)) {
                $this->mdl_followups->update($fuRowNew->fu_id, ['fu_status' => 'canceled', 'fu_comment' => 'Canceled ' . date('Y-m-d H:i:s') . ' - estimate status was changed']);
            } elseif($fuRowPost && !empty($fuRowPost)) {
                $this->mdl_followups->update($fuRowPost->fu_id, ['fu_status' => 'canceled', 'fu_comment' => 'Canceled ' . date('Y-m-d H:i:s') . ' - estimate status was changed']);
            }
            $status = array('status_type' => 'lead', 'status_item_id' => $lead_id, 'status_value' => $lead_data['lead_status_id'], 'status_date' => time());
            $this->mdl_leads->status_log($status);
        }
        
        if ($this->input->post('discount') !== FALSE && $this->input->post('discount') !== '') {
            $discount['discount_amount'] = round($this->input->post('discount'),2);
            $discount['discount_comment'] = null;
			if($this->input->post('discount_comment') !== false && $this->input->post('discount_comment') != null){
				$discount['discount_comment'] = trim($this->input->post('discount_comment'));
			}
            $discount['discount_percents'] = $this->input->post('discount_percents');
            $discount['estimate_id'] = $estimate_id;
            $discount['discount_date'] = time();
			$note_comment = '';
			if(isset($discount['discount_comment']) && trim($discount['discount_comment']) != ''){
				$note_comment = '<br>Comment:<br>' . nl2br($discount['discount_comment']);
			}
            $discountText = money($discount['discount_amount']) . $note_comment;
            if($discount['discount_percents'])
                $discountText = $discount['discount_amount'] . '%' . $note_comment;
        } else {
            $discount['estimate_id'] = $estimate_id;
            $discount['discount_amount'] = 0;
            $discount['discount_date'] = time();
            $discount['discount_percents'] = 0;
            $discount['discount_comment'] = null;
            $discountText = '';
        }

        $service = $preCopied = array();
        $services = $this->input->post('service_price') ? $this->input->post('service_price') : [];
        $bundlesRecords = [];
        $bundles = [];
        $insertServices = NULL;
        $updateServices = NULL;

        $service_type_id = $this->input->post('service_type_id');
        if(empty($service_type_id) && !empty($this->input->post('service_type_ids')))
            $service_type_id = $this->input->post('service_type_ids');
        $sortId = 0;
        foreach($services as $key => $val)
        {
            $id = isset($this->input->post('id')[$key]) ? $key : NULL;
            $existedCrew = null;
            $service = [];
            $ties = [];
            $service['service_id'] = $service_type_id[$key];
            $service['service_size'] = isset($this->input->post('service_size')[$key]) ? $this->input->post('service_size')[$key] : '';
            $service['service_reason'] = isset($this->input->post('service_reason')[$key]) ? $this->input->post('service_reason')[$key] : '';
            $service['service_species'] = isset($this->input->post('service_species')[$key]) ? $this->input->post('service_species')[$key] : '';
            $service['service_permit'] = isset($this->input->post('service_permit')[$key]) ? $this->input->post('service_permit')[$key] : 0;
            $service['service_exemption'] = isset($this->input->post('service_exemption')[$key]) ? $this->input->post('service_exemption')[$key] : 0;
            $service['service_description'] = isset($this->input->post('service_description')[$key]) ? $this->input->post('service_description')[$key] : '';
            $service['service_time'] = $this->input->post('service_time') && isset($this->input->post('service_time')[$key]) ? $this->input->post('service_time')[$key] : 0;
            $service['service_travel_time'] = $this->input->post('service_travel_time') && isset($this->input->post('service_travel_time')[$key]) ? $this->input->post('service_travel_time')[$key] : 0;
            $service['service_disposal_time'] = $this->input->post('service_disposal_time') && isset($this->input->post('service_disposal_time')[$key]) ? $this->input->post('service_disposal_time')[$key] : 0;
            $service['service_price'] = $this->input->post('service_price') && isset($this->input->post('service_price')[$key]) ? getAmount($this->input->post('service_price')[$key]) : 0;
            $service['service_wood_chips'] = isset($this->input->post('service_wood_chips')[$key])?$this->input->post('service_wood_chips')[$key]:0;

            $service['service_wood_trailers'] = isset($this->input->post('service_wood_trailers')[$key])?$this->input->post('service_wood_trailers')[$key]:0;

            $service['service_front_space'] = isset($this->input->post('service_front_space')[$key]) ? $this->input->post('service_front_space')[$key] : 0;
            $service['service_disposal_brush'] = isset($this->input->post('service_disposal_brush')[$key]) ? $this->input->post('service_disposal_brush')[$key] : 0;
            $service['service_disposal_wood'] = isset($this->input->post('service_disposal_wood')[$key]) ? $this->input->post('service_disposal_wood')[$key] : 0;
            $service['service_cleanup'] = isset($this->input->post('service_cleanup')[$key]) ? $this->input->post('service_cleanup')[$key] : 0;
            $service['service_access'] = isset($this->input->post('service_access')[$key]) ? $this->input->post('service_access')[$key] : 0;
            $service['service_client_home'] = isset($this->input->post('service_client_home')[$key]) ? $this->input->post('service_client_home')[$key] : 0;

            $service['service_overhead_rate'] = isset($this->input->post('service_overhead_rate')[$key]) ? getAmount($this->input->post('service_overhead_rate')[$key]) : 0;
            $service['service_markup_rate'] = isset($this->input->post('service_markup_rate')[$key]) ? floatval(str_replace(['% ', ','], '', $this->input->post('service_markup_rate')[$key])) : 0;

//            $service['estimate_service_category_id'] = isset($this->input->post('category')[$key]) ? $this->input->post('category')[$key] : null;
            $service['estimate_service_class_id'] = $this->input->post('class')[$key] ?? null;
            $service['estimate_service_ti_title'] = $this->input->post('tree_inventory_title')[$key] ?? null;
            $service['estimate_id'] = $estimate_id;
            $service['service_priority'] = $sortId += 1;

            if(isset($this->input->post('quantity')[$key]))
                $service['quantity'] = floatval($this->input->post('quantity')[$key]);

            if(isset($this->input->post('product_cost')[$key])){
                $service['cost'] = getAmount($this->input->post('product_cost')[$key]);
            }

            if(isset($this->input->post('ties_stump')[$key]) && isset($this->input->post('ties_cost')[$key])) {
                $ties = [
                    'ties_stump_cost' => $this->input->post('ties_stump')[$key] ?? 0,
                    'ties_cost' => $this->input->post('ties_cost')[$key] ?? 0,
                    'ties_estimate_service_id' => $id
                ];
            }


            $serviceInfo = $this->mdl_services->get($service['service_id']);
            if(!$serviceInfo->is_bundle)
                $estimate_balance += $service['service_price'];

            $service['non_taxable'] = isset($this->input->post('non_taxable')[$key]) ? $this->input->post('non_taxable')[$key] : 0;
            $service['is_view_in_pdf'] = isset($this->input->post('is_view_in_pdf')[$key]) ? 1 : 0;
            if(!$id)
            {
                $id = $this->mdl_services_orm->insert($service);
                if(isset($this->input->post('bundles_services')[$key]))
                    $bundlesRecords[$id] = $this->input->post('bundles_services')[$key];
                if($id && $serviceInfo->is_bundle)
                    $bundles[$key] = $id;

                // add tree inventory
                if(!empty($ties)){
                    $ties['ties_number'] = $this->input->post('ties_number')[$key];
                    $ties['ties_type'] = $this->input->post('ties_type')[$key] ?? '';
                    $ties['ties_size'] = $this->input->post('ties_size')[$key] ?? '';
                    $ties['ties_priority'] = $this->input->post('ties_priority')[$key] ?? '';
                    $ties['ties_estimate_service_id'] = $id;
                    $ties['ti_id'] = $key;
                    $tiesObject = TreeInventoryEstimateService::create($ties);
                    if(!empty($tiesObject) && isset($this->input->post('ties_work_types')[$key]) && !empty($this->input->post('ties_work_types')[$key])){
                        if(is_string($this->input->post('ties_work_types')[$key]))
                            $workTypes = json_decode($this->input->post('ties_work_types')[$key]);
                        else
                            $workTypes = $this->input->post('ties_work_types')[$key];
                        if(!empty($workTypes) && is_array($workTypes)){
                            foreach ($workTypes as $workType){
                                TreeInventoryEstimateServiceWorkTypes::create([
                                    'tieswt_ties_id' => $tiesObject->ties_id,
                                    'tieswt_wt_id' => $workType
                                ]);
                            }
                        }
                    }
                }

                /**FOR CLIENT NOTES INFO**/
                $insertServices .= '<li>' . $serviceInfo->service_name;
                $insertServices .= '<ul>';

                unset($service['estimate_id']);
                unset($service['service_id']);
                foreach($service as $name=>$val)
                {
                    if($name == 'service_description')
                        $insertServices .= '<li>'  . ucwords(implode(' ', explode('_', $name))) . ' "<small>' . $service[$name] . '</small>"';
                    elseif($service[$name] !== '' && $service[$name] != 0)
                        $insertServices .= '<li>'  . ucwords(implode(' ', explode('_', $name))) . ' "' . $service[$name] . '"';
                }

                repack_service_uploads($key, $id);
            }
            else
            {
                $serviceName = $serviceInfo && !empty($serviceInfo) ? $serviceInfo->service_name : 'UNDEFINED';
                $serviceInfo = $this->mdl_services_orm->get($id);
                $serviceFromDB = $this->mdl_services->get($serviceInfo->service_id);
                unset($serviceInfo->id);
                unset($serviceInfo->service_priority);
                unset($serviceInfo->service_scheme);
                unset($serviceInfo->service_status);
                $trigger = NULL;
                foreach($serviceInfo as $name=>$val)
                {
                    if(isset($service[$name]) && $service[$name] != $val && ($service[$name] || $val))
                    {
                        if(!$trigger)
                        {
                            $updateServices .= '<li>' . $serviceName;
                            $updateServices .= '<ul>';
                        }
                        if($name == 'service_description')
                            $updateServices .= '<li>'  . ucwords(implode(' ', explode('_', $name))) . ' was modified from ' . ' "<small>' . $val . '</small>"<br> to "<small>' . $service[$name] . '</small>"';
                        else
                            $updateServices .= '<li>' . ucwords(implode(' ', explode('_', $name))) . ' was modified from "' . $val . '" to "' . $service[$name] . '"';
                        $trigger = TRUE;
                    }
                }

                $existedCrew = $this->input->post('estimate_service_crew_id')[$id] ?? false;
                if(!empty($existedCrew)) {
                    $this->mdl_crews_orm->delete_by([
                        'crew_service_id' => $id,
                        'crew_id NOT IN(' . ($existedCrew ? implode(',', $existedCrew) : 'null') . ')' => null
                    ]);
                }
                else {
                    $this->mdl_crews_orm->delete_by([
                        'crew_service_id' => $id
                    ]);
                }
                $this->mdl_equipment_orm->delete_by(array('equipment_service_id' => $id));
                $this->mdl_expenses_orm->delete_by(array('ese_estimate_service_id' => $id));
                $this->mdl_services_orm->update($id, $service);

                if(isset($this->input->post('bundles_services')[$key]))
                    $bundlesRecords[$id] = $this->input->post('bundles_services')[$key];
                if($id && $serviceFromDB->is_bundle) {
                    $this->mdl_estimates_bundles->delete_by(['eb_bundle_id' => $id]);
                    $bundles[$key] = $id;
                }

                // update tree inventory
                if(!empty($ties)) {
                    $ties['ties_number'] = $this->input->post('ties_number')[$key];
                    $ties['ties_type'] = $this->input->post('ties_type')[$key] ?? '';
                    $ties['ties_priority'] = $this->input->post('ties_priority')[$key] ?? '';
                    $ties['ties_size'] = $this->input->post('ties_size')[$key] ?? '';

                    $newWorkTypes = json_decode($this->input->post('ties_work_types')[$key]);
                    $allTypes=TreeInventoryEstimateService::where('ties_estimate_service_id', $key)->with('tree_inventory_work_types')->get()->toArray();
                    $oldWorkTypes=[];
                     foreach($allTypes[0]['tree_inventory_work_types'] as $oneType){
                        $oldWorkTypes[]=$oneType['tieswt_wt_id'];
                    }

                    $needRemoveWorkTypes=$oldWorkTypes;
                    $needAddWorkTypes=[];
                    foreach($newWorkTypes as $newItem){
                        if(in_array($newItem,$needRemoveWorkTypes)){
                            $needRemoveWorkTypes=array_diff($needRemoveWorkTypes,[$newItem]);
                        }else{
                            $needAddWorkTypes[]=$newItem;
                        }
                    }


                    if(!empty($needAddWorkTypes) && is_array($needAddWorkTypes)){
                        foreach ($needAddWorkTypes as $workType){
                            TreeInventoryEstimateServiceWorkTypes::create([
                                'tieswt_ties_id' => $allTypes[0]['ties_id'],
                                'tieswt_wt_id' => $workType
                            ]);
                        }
                    }


                    if(!empty($needRemoveWorkTypes) && is_array($needRemoveWorkTypes)){
                        foreach ($needRemoveWorkTypes as $workType){
                            TreeInventoryEstimateServiceWorkTypes::where([['tieswt_wt_id', $workType],['tieswt_ties_id', $allTypes[0]['ties_id']]])->delete();
                        }
                    }
                    TreeInventoryEstimateService::where('ties_estimate_service_id', $id)->update($ties);

                }
            }

            $crews = isset($this->input->post('service_crew')[$key]) ? $this->input->post('service_crew')[$key] : array();
            $expenses = isset($this->input->post('expenses')[$key]) ? $this->input->post('expenses')[$key] : array();

            //SETUP
            $equipments = isset($this->input->post('service_vehicle')[$key]) ? $this->input->post('service_vehicle')[$key] : array(" ");
            $tools = isset($this->input->post('service_tools')[$key]) ? $this->input->post('service_tools')[$key] : array();
            $trailers = isset($this->input->post('service_trailer')[$key]) ? $this->input->post('service_trailer')[$key] : array();
            $item_options = isset($this->input->post('vehicle_option')[$key]) ? $this->input->post('vehicle_option')[$key] : array();
            $attach_options = isset($this->input->post('trailer_option')[$key]) ? $this->input->post('trailer_option')[$key] : array();
            $tools_option = isset($this->input->post('tools_option')[$key]) ? $this->input->post('tools_option')[$key] : array();
            $preUploadedFiles = isset($this->input->post('pre_uploaded_files')[$key]) ? $this->input->post('pre_uploaded_files')[$key] : array();

            if($preUploadedFiles && !empty($preUploadedFiles) ) {
                $path = 'uploads/clients_files/' . $data['client_id'] . '/estimates/' . $data['estimate_no'] . '/' . $id . '/';
                $files = bucketScanDir($path);
                $fileNum = 1;
                if ($files && !empty($files)) {
                    sort($files, SORT_NATURAL);
                    preg_match('/estimate_.*?_([0-9]{1,})\.[a-zA-Z{3,4}]/is', $files[count($files) - 1], $matches);//countOk
                    $fileNum = isset($matches[1]) ? ($matches[1] + 1) : 1;
                }

                $batchUpdate = [];
                $hiddenPdfFiles = is_array($this->input->post('hidden_photos')) ? $this->input->post('hidden_photos') : [];
                foreach ($preUploadedFiles as $keyFile => $file) {
                    $ext = pathinfo($file, PATHINFO_EXTENSION);
                    $file_name = 'estimate_no_' . $data['estimate_no'] . '_' . ($fileNum) . '.' . $ext;
                    bucket_copy($file, $path . $file_name, ['ContentType' => 'image/' . strtolower($ext)]);
                    if(array_search($file, $hiddenPdfFiles) === FALSE)
                        $preCopied[] = $path . $file_name;
                    $batchUpdate[] = [
                        'paint_path' => $file,
                        'paint_path ' => $path . $file_name //space in key is required !!!
                    ];
                    $fileNum++;

//                    if($keyFile == 0 && $this->input->post('service_type_id')[$key] == config_item('tree_inventory_service_id')){
//                        $treeInventory = $this->tree_inventory->get($key);
//                        if(!empty($treeInventory)){
//                            $this->tree_inventory->save(['ti_file' => $path . $file_name], $key, true);
//                        }
//                    }

                }
                $this->mdl_paint->updateBatchByPaths($batchUpdate);
            }

            foreach($expenses as $jkey => $jval)
            {
                if(isset($jval['title']) && isset($jval['amount'])) {
                    $expense['ese_title'] = $jval['title'];
                    $expense['ese_price'] = getAmount($jval['amount']);
                    $expense['ese_estimate_service_id'] = $id;
                    $expense['ese_estimate_id'] = $estimate_id;
                    $this->mdl_expenses_orm->insert($expense);
                }
            }
            $lastServiceId = null;
            foreach($crews as $jkey => $jval)
            {
                if(isset($existedCrew[$jkey]) && $existedCrew[$jkey]) {
                    continue;
                }
                $crew['crew_user_id'] = $jval;
                $crew['crew_estimate_id'] = $estimate_id;
                $crew['crew_service_id'] = $id;
                $this->mdl_crews_orm->insert($crew);
                $crewInfo = $this->mdl_crews->find_by_id($jval);

                if($insertServices)
                {
                    if(!$jkey)
                        $insertServices .= '<li> Crew: <ul><li>' . $crewInfo->crew_name;
                    else
                        $insertServices .= '<li>' . $crewInfo->crew_name;
                }
                else
                {
                    if(!$jkey || $lastServiceId != $id)
                        $updateServices .= '<li>'. $serviceName .' Crew:<ul><li>'  . $crewInfo->crew_name;
                    else
                        $updateServices .= '<li>' . $crewInfo->crew_name;
                }
                $lastServiceId = $id;
            }
            if(!empty($crews))
            {
                if($insertServices)
                    $insertServices .= '</ul></li>';
                else
                    $updateServices .= '</ul></li>';
            }
            $toolIds = [];
            if(isset($tools_option))
            {
                foreach($tools_option as $k=>$v)
                {
                    $toolIds[] = $k;
                }
            }
            foreach($equipments as $jkey => $jval)
            {

                $tools = $toolOpt = [];
                $eqInfo = $trailerInfo = $toolInfo = NULL;
                $equipment = [];
                if($jval != '')
                {
                    $equipment['equipment_item_id'] = $jval;
                }
                //SETUP
                $equipment['equipment_item_option'] = isset($item_options[$jkey]) ? json_encode($item_options[$jkey]) : NULL;
                $equipment['equipment_attach_id'] = isset($trailers[$jkey]) ? $trailers[$jkey] : NULL;
                $equipment['equipment_attach_option'] = isset($attach_options[$jkey]) ? json_encode($attach_options[$jkey]) : NULL;
                if(!empty($toolIds))
                {
                    foreach($toolIds as $k=>$v)
                    {
                        if(isset($tools_option[$v][$jkey]))
                        {
                            $tools[] = $v;
                            foreach($tools_option[$v][$jkey] as $jk=>$jv)
                                $toolOpt[$v][] = $jv;
                        }
                    }
                }
                $equipment['equipment_attach_tool'] = !empty($tools) ? json_encode($tools) : NULL;
                $equipment['equipment_tools_option'] = !empty($toolOpt) ? json_encode($toolOpt) : NULL;
                //SETUP END
                $equipment['equipment_estimate_id'] = $estimate_id;
                $equipment['equipment_service_id'] = $id;
                $this->mdl_equipment_orm->insert($equipment);

                //SETUP
                $eqInfo = $this->mdl_vehicles->get(array('vehicle_id' => $jval));
                $trailerInfo = isset($trailers[$jkey]) && $trailers[$jkey] != '' ? $this->mdl_vehicles->get(array('vehicle_id' => $trailers[$jkey])) : NULL;
                //SETUP END

                if($insertServices)
                {
                    //SETUPS
                    if(!$jkey && $eqInfo)
                        $insertServices .= '<li> Equipment:<ul><li>' . $eqInfo->vehicle_name;
                    elseif($eqInfo)
                        $insertServices .= '<li>' . $eqInfo->vehicle_name;

                    if($eqInfo && isset($item_options[$jkey]))
                    {
                        $insertServices .= '(';
                        foreach($item_options[$jkey] as $k=>$v)
                        {
                            $insertServices .= $v;
                            if(isset($item_options[$jkey][$k+1]))
                                $insertServices .= ' or ';
                        }
                        $insertServices .= ')';
                    }
                    if($trailerInfo)
                        $insertServices .= '</li><li>' . $trailerInfo->vehicle_name;
                    if($trailerInfo && isset($attach_options[$jkey]))
                    {
                        $insertServices .= '(';
                        foreach((array)$attach_options[$jkey] as $k=>$v)
                        {
                            $insertServices .= $v;
                            if(isset($attach_options[$jkey][$k+1]))
                                $insertServices .= ' or ';
                        }
                        $insertServices .= ')';
                    }
                    //SETUP END
                }
                else
                {
                    //SETUP
                    //todo: check for update equipment
                    /*if(!$jkey && $eqInfo)
                        $updateServices .= '<li>'. $serviceName .' Equipment:<ul><li>'  . $eqInfo->vehicle_name;
                    elseif($eqInfo && $insertServices)
                        $insertServices .= '<li>' . $eqInfo->vehicle_name;
                    if($eqInfo && isset($item_options[$jkey]) && is_array($item_options[$jkey]) && $insertServices)
                    {
                        $insertServices .= '(';
                        foreach($item_options[$jkey] as $k=>$v)
                        {
                            $insertServices .= $v;
                            if(isset($item_options[$jkey][$k+1]))
                                $insertServices .= ' or ';
                        }
                        $insertServices .= ')';
                    }
                    if($trailerInfo && $insertServices)
                        $insertServices .= '</li><li>' . $trailerInfo->vehicle_name;
                    if($trailerInfo && isset($attach_options[$jkey]) && is_array($attach_options[$jkey]) && $insertServices)
                    {
                        $insertServices .= '(';
                        foreach($attach_options[$jkey] as $k=>$v)
                        {
                            $insertServices .= $v;
                            if(isset($attach_options[$jkey][$k+1]))
                                $insertServices .= ' or ';
                        }
                        $insertServices .= ')';
                    }*/
                    //SETUP END
                }
            }
            if(!empty($equipments))
            {
                if($insertServices) {
                    $insertServices .= '</ul></li>';
                }/* else {
                    $updateServices .= '</ul></li>';
                }*/
            }
            if($insertServices) {
                $insertServices .= '</ul></li><br>';
            }
            if($updateServices) {
                $updateServices .= '</ul></li><br>';
            }
        }

        if(!empty($bundlesRecords))
            foreach ($bundlesRecords as $key => $val){
                if(key_exists($val, $bundles)) {
                    $dataToDb = [
                        'eb_service_id' => $key,
                        'eb_bundle_id' => $bundles[$val]
                    ];
                    $this->mdl_estimates_bundles->insert($dataToDb);
                }
            }

        $uploaded = $this->_do_upload($data['estimate_no'], $client_id, 'service_files');

        foreach ($uploaded as $key => $value) {
            $path = 'uploads/clients_files/' . $client_id . '/estimates/' . $data['estimate_no'] . '/' . $key . '/';

            foreach ($value as $num => $image) {
                $estimate_pdf_files[] = $path . $image;
            }
        }
        $estimate_pdf_files = array_merge($estimate_pdf_files, $preCopied);

        bucket_unlink_all('uploads/clients_files/' . $client_id . '/leads/tmp/' . str_pad($lead_id, 5, '0', STR_PAD_LEFT) . '-E/');
        $this->update($estimate_id, array('estimate_balance' => $estimate_balance * $data['estimate_tax_rate'], 'estimate_pdf_files' => json_encode($estimate_pdf_files)));
        $this->mdl_invoices->update_all_invoice_interes($estimate_id);

        $discountNote = null;

        $discount_data = $this->mdl_clients->get_discount(array('discounts.estimate_id' => $estimate_id));
        if ($discount_data && !empty($discount_data)) {
            $this->mdl_clients->update_discount($discount_data['discount_id'], $discount);
            if($discount_data['discount_amount'] != $discount['discount_amount'] || $discount_data['discount_percents'] != $discount['discount_percents'] || $discount_data['discount_comment'] != $discount['discount_comment']) {
                $discountNote = '<br>Discount - ' . $discountText;
            }
        } else {
            $this->mdl_clients->insert_discount($discount);
            if($discount['discount_amount'] > 0 || $discount['discount_percents'] > 0) {
                $discountNote = '<br>Discount - ' . $discountText;
            }
        }

        if(!$this->input->post('estimate_id')) {
            $estimateNote = 'Estimate "' . $data['estimate_no'] . '" created';
        } else {
            $estimateNote = 'Estimate "' . $data['estimate_no'] . '" updated';
        }
        if($discountNote) {
            $estimateNote .= '<br><br>' . $discountNote;
        }
        if($deletedServices) {
            $estimateNote .= '<br><br>Removed services:<br>' . $deletedServices;
        }
        if($insertServices) {
            $estimateNote .= '<br><br>Inserted services:<br>' . $insertServices;
        }
        if($updateServices) {
            $estimateNote .= '<br><br>Updated services:<br>' . $updateServices;
        }
        make_notes($client_id, $estimateNote, 'system', $lead_id);
        bucket_unlink_all('uploads/clients_files/' . $client_id . '/leads/tmp/' . $data['estimate_no']);

        return $estimate_id;
    }

    public function calcQuery($where = [], $extraJoin = [], $group_by = 'estimates.estimate_id') {
//        $where['is_bundle'] = 0;
        $whereIn = [];
        if($where && is_array($where) && is_countable($where) && count($where)) {
            foreach ($where as $key => $val) {
                if(is_array($val)) {
                    $whereIn[$key] = $val;
                    unset($where[$key]);
                }
            }
        }

	    /****SUB-QUERY for get total payments****/
	    $select = 'client_payments.estimate_id as payment_estimate_id, ROUND(CAST(SUM(client_payments.payment_amount) / COUNT(DISTINCT estimates_services.id) AS DECIMAL(10, 4)), 2) as payments_total';
	    $this->_database->select($select, FALSE);
	    $this->_database->from('client_payments');

	    $this->_database->where($where);
        foreach ($whereIn as $key => $val) {
            $this->db->where_in($key, $val);
        }
	    $this->_database->join('estimates', 'estimates.estimate_id = client_payments.estimate_id');
		$this->_database->join('estimates_services', 'estimates_services.estimate_id = estimates.estimate_id', 'left');
	    $this->_database->join('workorders', 'estimates.estimate_id = workorders.estimate_id', 'left');
	    $this->_database->join('invoices', 'estimates.estimate_id = invoices.estimate_id', 'left');
//        $this->_database->join('services', 'estimates_services.service_id = services.service_id');
	    if(is_array($extraJoin)) {
	        foreach ($extraJoin as $join) {
	            $this->_database->join($join['table'], $join['condition'], isset($join['type']) && $join['type'] ? $join['type'] : 'inner');
            }
        }
	    $this->_database->group_by('client_payments.estimate_id');
        $paymentsSubQuery = $this->_database->_compile_select();
        $this->_database->_reset_select();


        /****SUB-QUERY for get total interests****/
        $select = "invoices.estimate_id as invoice_estimate_id, ROUND(CAST(IF(invoices.interest_status = 'No', SUM(invoice_interest.interes_cost) / COUNT(DISTINCT estimates_services.id), 0) AS DECIMAL(10, 4)), 2) as interests_total";
        $this->_database->select($select, FALSE);
        $this->_database->from('invoices');
        $this->_database->where($where);
        foreach ($whereIn as $key => $val) {
            $this->db->where_in($key, $val);
        }
        $this->_database->join('estimates', 'estimates.estimate_id = invoices.estimate_id');
		$this->_database->join('estimates_services', 'estimates_services.estimate_id = estimates.estimate_id', 'left');
        $this->_database->join('workorders', 'estimates.estimate_id = workorders.estimate_id', 'left');
        $this->_database->join('invoice_interest', 'invoices.id = invoice_interest.invoice_id', 'left');
//        $this->_database->join('services', 'estimates_services.service_id = services.service_id');
        if(is_array($extraJoin)) {
            foreach ($extraJoin as $join) {
                $this->_database->join($join['table'], $join['condition'], isset($join['type']) && $join['type'] ? $join['type'] : 'inner');
            }
        }
        $this->_database->group_by('invoice_interest.invoice_id');
        $interestsSubQuery = $this->_database->_compile_select();
        $this->_database->_reset_select();
        
        
                //expenses
		/*$select = "ROUND(SUM(IFNULL(expenses.expense_amount, 0)), 2) as expenses_total, estimates.estimate_id";
        $this->_database->select($select, FALSE);
        $this->_database->from('estimates');
        $this->_database->where($where);
        $this->_database->join('workorders', 'estimates.estimate_id = workorders.estimate_id', 'left');
        $this->_database->join('invoices', 'estimates.estimate_id = invoices.estimate_id', 'left');
        $this->_database->join('schedule', 'schedule.event_wo_id = workorders.id', 'left');
        $this->_database->join('expenses', 'expenses.expense_event_id = schedule.id', 'left');
        if(is_array($extraJoin)) {
            foreach ($extraJoin as $join) {
                $this->_database->join($join['table'], $join['condition'], isset($join['type']) && $join['type'] ? $join['type'] : 'inner');
            }
        }
        $this->_database->group_by('estimates.estimate_id');
        $expensesSubQuery = $this->_database->_compile_select();
        $this->_database->_reset_select();*/

        /****SUB-QUERY for get total services, discounts, etc.****/
        $select = "estimates.estimate_no, invoices.invoice_no, workorders.workorder_no,
            estimates.estimate_hst_disabled, estimates.client_id, 
            ROUND(CAST(IF(estimates.estimate_tax_rate IS NOT NULL AND estimates.estimate_tax_rate <> '0' AND estimates.estimate_hst_disabled <> 1 , estimates.estimate_tax_rate, 1) AS DECIMAL(11, 5)), 5) as tax_rate,
            estimates.estimate_tax_value as tax_value,
            discounts.discount_amount as discount_column,
            IFNULL(payments.payments_total, 0) as payments_total,
            IFNULL(interests.interests_total, 0) as interests_total,
            ROUND(CAST(SUM(IF(services.is_bundle <> 1, estimates_services.service_price, 0)) /
                       IF(services.is_bundle <> 1 AND estimate_hst_disabled = 2, ROUND(CAST(IF(estimates.estimate_tax_rate IS NOT NULL AND estimates.estimate_tax_rate <> '0', estimates.estimate_tax_rate, 1) AS DECIMAL(10, 4)), 2), 1) AS DECIMAL(10, 4)), 2) as total_all_services,
            ROUND(CAST(SUM(IF(services.is_bundle <> 1 AND estimates_services.service_status = 1, estimates_services.service_price, 0)) /
                       IF(services.is_bundle <> 1 AND estimate_hst_disabled = 2, ROUND(CAST(IF(estimates.estimate_tax_rate IS NOT NULL AND estimates.estimate_tax_rate <> '0', estimates.estimate_tax_rate, 1) AS DECIMAL(10, 4)), 2), 1) AS DECIMAL(10, 4)), 2) as total_declined,
            ROUND(CAST(SUM(IF(services.is_bundle <> 1 AND estimates_services.service_status <> 1, estimates_services.service_price, 0)) /
                       IF(services.is_bundle <> 1 AND estimate_hst_disabled = 2, ROUND(CAST(IF(estimates.estimate_tax_rate IS NOT NULL AND estimates.estimate_tax_rate <> '0', estimates.estimate_tax_rate, 1) AS DECIMAL(10, 4)), 2), 1) AS DECIMAL(10, 4)), 2) as total_confirmed,
            ROUND(CAST(
                IF(estimate_hst_disabled <> 2, IFNULL(
                    IF(discounts.discount_percents = 0, discounts.discount_amount, 
                        (discounts.discount_amount * SUM(
                            IF(services.is_bundle <> 1 AND estimates_services.service_status <> 1, estimates_services.service_price, 0)) / 100)), 0),
                         0) AS DECIMAL(10, 4)), 2) as discount_total,
            discounts.discount_percents as discount_in_percents, discounts.discount_comment as discount_comment, 
            IF(discounts.discount_percents <> 0, discounts.discount_amount, 0) as discount_percents_amount,
            SUM(IF(services.is_bundle <> 1 AND estimates_services.service_status <> 1 AND estimates_services.non_taxable = 1, estimates_services.service_price, 0)) as sum_non_taxable_dirty,
            SUM(IF(services.is_bundle <> 1 AND (estimates_services.service_status <> 1 AND estimates_services.service_status <> 2) AND estimates_services.non_taxable = 1, estimates_services.service_price, 0)) as sum_actual_non_taxable_dirty,
            SUM(IF(services.is_bundle <> 1 AND estimates_services.service_status <> 1 AND estimates_services.non_taxable <> 1, estimates_services.service_price, 0))
                 / IF(estimate_hst_disabled = 2, IF(estimates.estimate_tax_rate IS NOT NULL AND estimates.estimate_tax_rate <> '0', estimates.estimate_tax_rate, 1), 1) as sum_taxable_dirty,  
            SUM(IF(services.is_bundle <> 1 AND (estimates_services.service_status <> 1 AND estimates_services.service_status <> 2) AND estimates_services.non_taxable <> 1, estimates_services.service_price, 0))
                 / IF(estimate_hst_disabled = 2, IF(estimates.estimate_tax_rate IS NOT NULL AND estimates.estimate_tax_rate <> '0', estimates.estimate_tax_rate, 1), 1) as sum_actual_taxable_dirty,
            estimates.estimate_id as estimate_id, invoices.id as invoice_id, workorders.id as workorder_id, estimates.user_id as estimate_user_id, 
            estimates.status as estimate_status_id, workorders.wo_status as workorder_status_id, invoices.in_status as invoice_status_id";
        $this->_database->select($select, FALSE);
        $this->_database->from('estimates');
        $this->_database->where($where);
        foreach ($whereIn as $key => $val) {
            $this->db->where_in($key, $val);
        }
        $this->_database->join('estimates_services', 'estimates_services.estimate_id = estimates.estimate_id', 'left');
        $this->_database->join('discounts', 'discounts.estimate_id = estimates.estimate_id', 'left');
        $this->_database->join('workorders', 'estimates.estimate_id = workorders.estimate_id', 'left');
        $this->_database->join('invoices', 'estimates.estimate_id = invoices.estimate_id', 'left');
        $this->_database->join('services', 'estimates_services.service_id = services.service_id', 'left');
        $this->_database->join("({$paymentsSubQuery}) payments", 'payments.payment_estimate_id = estimates.estimate_id', 'left', FALSE);
        $this->_database->join("({$interestsSubQuery}) interests", 'interests.invoice_estimate_id = estimates.estimate_id', 'left', FALSE);
        //$this->_database->join("({$expensesSubQuery}) expenses", 'expenses.estimate_id = estimates.estimate_id', 'left', FALSE);
        if(is_array($extraJoin)) {
            foreach ($extraJoin as $join) {
                $this->_database->join($join['table'], $join['condition'], isset($join['type']) && $join['type'] ? $join['type'] : 'inner');
            }
        }
        //edit by DV
        $this->_database->group_by($group_by);
        $estimatesSubQuery = $this->_database->_compile_select();
        $this->_database->_reset_select();

        /****PRIMARY QUERY with finished calculations****/
        $resultQuery = "SELECT totals.*, ROUND(totals.sum_taxable_dirty, 2) as sum_taxable, ROUND(totals.sum_non_taxable_dirty, 2) as sum_non_taxable,
            ROUND(totals.sum_taxable_dirty + totals.sum_non_taxable_dirty - discount_total, 2) as sum_for_services, ROUND(totals.sum_taxable_dirty + totals.sum_non_taxable_dirty, 2) as sum_services_without_discount, ROUND(totals.sum_taxable_dirty + totals.sum_non_taxable_dirty + interests_total - totals.discount_total, 2) as sum_without_tax, 
            ROUND(totals.sum_actual_taxable_dirty + totals.sum_actual_non_taxable_dirty - discount_total, 2) as sum_actual_for_services, ROUND(totals.sum_actual_taxable_dirty + totals.sum_actual_non_taxable_dirty, 2) as sum_actual_services_without_discount, ROUND(totals.sum_actual_taxable_dirty + totals.sum_actual_non_taxable_dirty + interests_total/* - totals.discount_total*/, 2) as sum_actual_without_tax,            
            ROUND(CAST(((sum_taxable_dirty - IFNULL((sum_taxable_dirty / (sum_taxable_dirty + sum_non_taxable_dirty) * discount_total), 0) + interests_total) * tax_rate - (sum_taxable_dirty - IFNULL((sum_taxable_dirty / (sum_taxable_dirty + sum_non_taxable_dirty) * discount_total), 0) + interests_total)) AS DECIMAL(10,4)), 2) as total_tax,
            ROUND(CAST((sum_taxable_dirty - IFNULL((sum_taxable_dirty / (sum_taxable_dirty + sum_non_taxable_dirty) * discount_total), 0) + interests_total) * tax_rate + (sum_non_taxable_dirty - IFNULL((sum_non_taxable_dirty / (sum_taxable_dirty + sum_non_taxable_dirty) * discount_total), 0)) AS DECIMAL(10,4)), 2) as total_with_tax,
            ROUND(CAST(ROUND(CAST((sum_taxable_dirty - IFNULL((sum_taxable_dirty / (sum_taxable_dirty + sum_non_taxable_dirty) * discount_total), 0) + interests_total) * tax_rate AS DECIMAL(10, 4)), 2) + (sum_non_taxable_dirty - IFNULL((sum_non_taxable_dirty / (sum_taxable_dirty + sum_non_taxable_dirty) * discount_total), 0)) - payments_total AS DECIMAL(10,4)), 2) as total_due
            FROM ($estimatesSubQuery) as totals";
        
        return $resultQuery;
    }

    private function _do_upload($estimate_no, $client_id, $field = 'files', $types = 'gif|jpg|jpeg|png|pdf|GIF|JPG|JPEG|PNG|PDF')
    {
        multifile_array($field);

        $files = $_FILES;
        $result = array();
        $config['allowed_types'] = $types;
        //$config['max_size'] = '100500';//test
        $config['overwrite'] = TRUE;

        $this->load->library('upload');
        $service = 0;
        $mkNote = FALSE;
        $note = 'Add Estimate File(s) for ' . $estimate_no . ': ';
        foreach ($files as $num => $typeFiles) {

            if ($service != $typeFiles['field']) {
                $service = $typeFiles['field'];
                $key = 1;
            }

            $path = 'uploads/clients_files/' . $client_id . '/estimates/' . $estimate_no . '/' . $typeFiles['field'] . '/';
            if ($key == 1) {
                $files = bucketScanDir($path);
                if ($files && !empty($files)) {
                    sort($files, SORT_NATURAL);
                    preg_match('/estimate_.*?_([0-9]{1,})\.[a-zA-Z{3,4}]/is', $files[count($files) - 1], $matches);//countOk
                    $key = isset($matches[1]) ? ($matches[1] + 1) : 1;
                }
            }
            $config['upload_path'] = $path;
            $ext = pathinfo($typeFiles['name'], PATHINFO_EXTENSION);
            //echo '<pre>'; var_dump($config['upload_path'], ); die;
            $config['file_name'] = 'estimate_' . $estimate_no . '_' . ($key) /*. '.jpg'; */ . '.' . $ext;

            $this->upload->initialize($config);
            if (!$this->upload->do_upload($num))
                $result[$typeFiles['field']] = array();
            else {
                $result[$typeFiles['field']][] = $config['file_name'];
                $key++;
                $note .= '<br><a href="' . base_url() . $path . $config['file_name'] . '">' . $config['file_name'] . '</a>';
                $mkNote = TRUE;
            }
        }
        if($mkNote)
            make_notes($client_id, $note, 'attachment', intval($estimate_no));
        return $result;
    }
	
}
