<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

use \application\modules\leads\models\Lead;
use application\modules\estimates\models\Service;
use application\modules\tree_inventory\models\TreeInventoryScheme;
use application\modules\tree_inventory\models\TreeInventoryWorkTypes;
use application\modules\tree_inventory\requests\TreeInventorySchemeRequest;
use application\modules\estimates\models\TreeInventoryEstimateService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;
use application\modules\clients\models\Client;
use application\modules\tree_inventory\models\WorkType;
use application\modules\workorders\models\Workorder;
use application\modules\invoices\models\Invoice;
use application\modules\tree_inventory\models\TreeInventory;

class Tree_inventory extends MX_Controller
{

//*******************************************************************************************************************
//*************
//*************																						Tasks Controller;
//*************
//*******************************************************************************************************************	

	function __construct()
	{

		parent::__construct();
		if (!isUserLoggedIn()) {
			redirect('login');
		}
		
		$this->_title = SITE_NAME;
		
		$this->load->helper('tree');
		$this->load->helper('user');
		$this->load->library('form_validation');
		$this->load->library('googlemaps');
		$this->load->model('mdl_clients', 'mdl_clients');
		$this->load->model('mdl_trees');
		
		$this->load->model('mdl_work_types_orm', 'work_types');

		$this->load->model('mdl_tree_inventory_orm', 'tree_inventory');
		$this->load->model('mdl_tree_inventory_map_orm', 'tree_inventory_map');
		$this->load->model('mdl_tree_inventory_work_types_orm', 'tree_inventory_work_types');
		$this->load->model('mdl_leads');
		$this->load->model('mdl_services_orm');

		$this->load->library('Common/LeadsActions');
		$this->load->library('Common/EstimateActions');
		$this->load->library('Common/WorkorderActions');
		$this->load->library('Common/InvoiceActions');
		$this->load->library('Common/TreeInventorySchemeActions');
	}

	public function map($client_id=false, $lead_id=false)
	{
		if(!$client_id)
			show_404();

		$data['title'] = $this->_title . ' - Tree inventory';
		$data['client_data'] = $data['client'] = $this->mdl_clients->get_client_by_id($client_id);
		
		if(empty((array)$data['client']))
			show_404();

        $data['client_address'] = $data['home_address'] = client_address((array)$data['client']);
        if($lead_id){
            $lead = Lead::find($lead_id);
            $home_address = ($lead && $lead->lead_address)?lead_address($lead->toArray()):$data['home_address'];
            $data['home_address'] = ($home_address && (!isset($data['home_address']) || $home_address != $data['home_address']))?$home_address:$data['home_address'];
        }

		$data['work_types'] = $this->work_types->get_all();
		$data['trees'] = $this->mdl_trees->get_trees();

		/*------------lead modal----------*/
		$this->load->model('mdl_services');
		$this->load->model('mdl_leads_services');
		$services = $this->mdl_services->order_by('service_priority')->get_many_by([
		        'service_parent_id' => NULL,
                'service_status' => 1,
        ]);

		/*----------map data-----------*/
		$data = $this->map_data($data, $client_id, $lead_id);
		/*----------map data-----------*/

		
		$data['bundles'] = json_encode(array_map('service_select_option', array_filter($services, function($v){ return ($v->is_bundle); })));	
		$data['products'] = json_encode(array_map('service_select_option', array_filter($services, function($v){ return ($v->is_product); })));
		$data['services'] = json_encode(array_map('service_select_option', array_filter($services, function($v){ return (!$v->is_product && !$v->is_bundle); })));

		
		$data['client_leads'] = $this->mdl_leads->get_client_leads($client_id)->result();
		$data['client_leads_not_confirmed'] = array_filter($this->mdl_leads->get_client_leads($client_id, ['lead_statuses.lead_status_declined'=>0])->result(), function($v){ return ($v->est_status_confirmed!=1); });
		
		$data['priority_color'] = $this->tree_inventory->priority_color;
		
		if($this->input->is_ajax_request())
			return $this->ajax_response("ok", ['response'=>$data]);

//        $data['services'] = Service::where(['is_product' => 0, 'is_bundle' => 0])->get()->toArray();
		$this->load->view('map', $data);
	}
	
	public function copy_tree_inventory()
	{
		$data['tree_inventory'] = $this->tree_inventory->with('work_types')->with('tree_type')->order_by('ti_tree_number')->get_many_by(['ti_client_id'=>$this->input->post('ti_client_id_from'), 'ti_lead_id'=>$this->input->post('ti_lead_id_from')]);

		$data['tree_inventory_old'] = $this->tree_inventory->with('work_types')->with('tree_type')->order_by('ti_tree_number')->get_many_by(['ti_client_id'=>$this->input->post('ti_client_id_to'), 'ti_lead_id'=>$this->input->post('ti_lead_id_to')]);

		
		if(count($data['tree_inventory_old'])){
			$this->leadsactions->deleteTreeInventoryScreen($this->input->post('ti_client_id_to'), $this->input->post('ti_lead_id_to'));

			foreach ($data['tree_inventory_old'] as $key => $value) {
				$this->leadsactions->tree_inventory_delete_point($value->ti_id, $value);
			}
		}

		$this->leadsactions->copyMapScreen($this->input->post('ti_lead_id_from'), $this->input->post('ti_client_id_to'), $this->input->post('ti_lead_id_to'));
		
		if(!count($data['tree_inventory']))
			$this->response(['status'=>'ok']);

		foreach ($data['tree_inventory'] as $key => $value) {
			$work_types = [];
			if(count($value->work_types))
				$value->work_types = array_map(function($v){ return $v->tiwt_work_type_id; }, $value->work_types);

			$value->ti_client_id = $this->input->post('ti_client_id_to');
			$value->ti_lead_id = $this->input->post('ti_lead_id_to');
			unset($value->ti_id);
			unset($value->tree_type);
			
			$result = $this->leadsactions->tree_inventory_save_point((array)$value);
			
			if($value->ti_file)
				$this->leadsactions->copy_point_file($this->input->post('ti_lead_id_from'), $this->input->post('ti_lead_id_to'), $value->ti_file, $result['result']);
		}
		
		return $this->response([
			'status'=>'ok', 
			'client_id'=>$this->input->post('ti_client_id_to'), 
			'lead_id'=>$this->input->post('ti_lead_id_to')], 
		200);
	}

	public function save_points(){
        if(isset($_FILES['file']) && !$_FILES['file']['error'] && isset($_FILES['file']['name']) && $_FILES['file']['name']!="") {
            $allowed_mime_type_arr = array('image/jpeg','image/pjpeg','image/png','image/x-png');
            $mime = get_mime_by_extension($_FILES['file']['name']);
            if(!in_array($mime, $allowed_mime_type_arr)){
                return $this->ajax_response('errors', ['file'=>'File is not valid']);
            }
        }
		$response = [];
		$result = $this->leadsactions->tree_inventory_save_point($this->input->post());
        if($result['result']==false)
			return $this->ajax_response('errors', $result['errors']);
        $ti_id = element('ti_id', $this->input->post(), $result['result']);
        $marker = $this->tree_inventory->get($ti_id);

        if (isset($_FILES['file']) && !$_FILES['file']['error']){
			$upload = $this->leadsactions->tree_inventory_point_file($_FILES['file'],  $this->input->post('ti_tis_id'), $result['result']);

          	if(!$upload['result'])
				return $this->ajax_response('errors', ['file'=>'File is not valid']);

		}	
		
		$response['response'] = $this->map_data($response, $this->input->post('ti_client_id'), false, $this->input->post('ti_tis_id'));
		$response['marker'] = $this->tree_inventory->get($ti_id);

		return $this->ajax_response('ok', $response);
	}

	function create_estimate(){
        $tree_inventory_ids = json_decode($this->input->post('ti_ids'));
        $tree_inventory_schema_id = json_decode($this->input->post('ti_tis_id'));
        $tree_inventory_screen = $this->input->post('screen_map');
        $lead_id = $this->input->post('lead_id');
        $new_lead_id = null;
        if(!empty($tree_inventory_schema_id) && (empty($lead_id) || $lead_id == 'new')) {
            $lead = $this->create_lead($tree_inventory_schema_id);
            if(!empty($lead) && !empty($lead->lead_id)) {
                $lead_id = $lead->lead_id;
                $new_lead_id = $lead_id;
            }
        }
        if(!empty($tree_inventory_ids) && is_array($tree_inventory_ids) && !empty($lead_id)) {
            $this->leadsactions->setTreeInventoryDraftService($tree_inventory_ids, $lead_id);
            $this->successResponse(['url' => base_url('estimates/new_estimate/' . $lead_id), 'new_lead_id' => $new_lead_id]);
        }
        if(!empty($tree_inventory_screen) && !empty($lead_id)){
            $this->leadsactions->setLead($lead_id);
            $this->leadsactions->updateTreeInventoryScreenshot($tree_inventory_screen, '_tree_inventory_map');
        }
    }

    function create_wo(){
        $tree_inventory_ids = json_decode($this->input->post('ti_ids'));
        $tree_inventory_schema_id = json_decode($this->input->post('ti_tis_id'));
        $tree_inventory_screen = $this->input->post('screen_map');
        $result = $this->create_lead_estimate_wo($tree_inventory_ids, $tree_inventory_schema_id, Service::SERVICE_STATUS_NEW, $tree_inventory_screen);
        if($result)
           return $this->successResponse(['data' => $result, 'url' => base_url($result['workorder_no'])]);
        return $this->errorResponse([]);
    }

    function create_invoice(){
        $tree_inventory_ids = json_decode($this->input->post('ti_ids'));
        $tree_inventory_schema_id = json_decode($this->input->post('ti_tis_id'));
        $tree_inventory_screen = $this->input->post('screen_map');
	    $result = $this->create_lead_estimate_wo($tree_inventory_ids, $tree_inventory_schema_id, Service::SERVICE_STATUS_COMPLETE, $tree_inventory_screen);
	    if($result) {
            $result = $this->workorderactions->setStatus(Workorder::find($result['workorder_id']), $this->workorderactions->getFinishedStatusId());
            if($result && !empty($result['workorder_data'])){
                $invoice = Invoice::where('workorder_id', $result['workorder_data']['id'])->first();
                if(!empty($invoice))
                    return $this->successResponse(['data' =>
                        [
                        'client_id' => $result['workorder_data']['client_id'],
                        'estimate_id' => $result['workorder_data']['estimate_id'],
                        'workorder_id' => $result['workorder_data']['id'],
                        'invoice_id' => $invoice->id,
                        ],
                        'url' => base_url($invoice->invoice_no)
                    ]);
            }
        }
        return $this->errorResponse([]);
    }

	function update_screen($id){
		if (!$id)
			return $this->ajax_response('errors', []);

		
		if($this->input->post('map_image')){
			$this->leadsactions->setLead($this->input->post('ti_lead_id'));
			$this->leadsactions->updateTreeInventoryScreenshot($this->input->post('map_image'));
		}
		
		
		return $this->ajax_response("ok", ['link'=>base_url('/tree_inventory/pdf/'.$id.'/'.$this->input->post('ti_lead_id'))]);
	}

	function pdf($id, $lead_id){
		if (!$id)
			return $this->ajax_response('errors', []);

		$where = ['ti_client_id'=>$id, 'ti_lead_id'=>$lead_id];
		$this->render_pdf($where);
	}

	public function render_pdf($where){

		$this->load->library('mpdf', ['orientation'=>'L']);
		
		$data['title'] = $this->_title . ' - Tree Inventory';
		$this->leadsactions->setLead($where['ti_lead_id']);
		$html = $this->leadsactions->tree_inventory_pdf();
		

		$this->mpdf->WriteHTML($html);
		$this->mpdf->_setPageSize('Letter', $this->mpdf->DefOrientation);
		$this->mpdf->SetHtmlFooter('');

		$this->mpdf->Output('TreeInventory.pdf', 'I');
	}
	
	function delete()
	{
        if(!$this->input->post('ti_id'))
            return $this->ajax_response('errors', []);

        $result = $this->leadsactions->tree_inventory_delete_point($this->input->post('ti_id'));

        if(!$result['status'])
            return $this->ajax_response('ok', []);

        $response = [];
        $response['response'] = $this->map_data(['deleted' => $result['tree']], $result['tree']->ti_client_id, $result['tree']->ti_lead_id, $this->input->post('ti_tis_id'));

        $this->ajax_response('ok', $response);
	}

	public function upload_map_image()
	{
		if (!isset($_FILES['map']) || $_FILES['map']['error'])
			return $this->ajax_response('errors', ['map'=>'File is not valid']);

		if (isset($_FILES['map']) && !$_FILES['map']['error']){
			$upload = $this->leadsactions->uploadTreeInventoryScreen($_FILES, $this->input->post('ti_client_id'), $this->input->post('ti_lead_id'));

			if($upload['status']===false)
				return $this->ajax_response('errors', $upload['errors']);

			$data['response'] = $this->map_data([], $this->input->post('ti_client_id'), $this->input->post('ti_lead_id'));

			return $this->ajax_response('ok', $data);
		}

		$this->tree_inventory_map->save(['tim_client_id'=>$this->input->post('ti_client_id'), 'tim_lead_id'=>$this->input->post('ti_lead_id'), 'tim_image'=>0]);

		return $this->ajax_response('errors', ['map'=>'File is not valid']);
	}
	
	public function delete_map_image()
	{
		$result = $this->leadsactions->deleteTreeInventoryScreen($this->input->post('ti_client_id'), $this->input->post('ti_lead_id'));

		if($result['status']===false)
			return $this->ajax_response('errors', ['error'=>'File is not exist']);

		$this->ajax_response('ok', []);
	}

	public function save_project(){

        try {
            /** @var TreeInventorySchemeRequest $request */
            $request = app(TreeInventorySchemeRequest::class);
        } catch (ValidationException $e) {
            return $this->errorResponse(null, $e->validator->errors());
        }
        $update = false;
        $client_id = $this->input->post('tis_client_id');
        if(!empty($this->input->post('tis_name'))){
            if(empty($this->input->post('tis_id'))){
                $tis = TreeInventoryScheme::create($request->validated());
                if(!empty($tis))
                    $tis_id = $tis->tis_id;
            } else{
                $tis_id = $this->input->post('tis_id');
                TreeInventoryScheme::where('tis_id', $tis_id)->update($request->validated());
                $update = true;
            }

            if(!empty($this->input->post('tis_copy') && !empty($this->input->post('tis_copy_id')))){
                $copy_id = $this->input->post('tis_copy_id');
                $tree_inventories = TreeInventory::where('ti_tis_id', $copy_id)->get()->toArray();
                if(!empty($tree_inventories)){
                    foreach ($tree_inventories as $tree_inventory){
                        $tree_inventory_id = $tree_inventory['ti_id'];
                        $tree_inventory['ti_tis_id'] = $tis_id;
                        unset($tree_inventory['ti_id']);
                        $new_tree_inventory = TreeInventory::create($tree_inventory);
                        if(!empty($tree_inventory['ti_file'])) {
                            $source = 'uploads/tree_inventory/' . $copy_id . '/' . $tree_inventory['ti_file'];
                            $target = 'uploads/tree_inventory/' . $tis_id . '/' . $tree_inventory['ti_file'];
                            bucket_copy($source, $target, $options = []);
                        }

                        $work_types = TreeInventoryWorkTypes::where('tiwt_tree_id', $tree_inventory_id)->get()->toArray();
                        if(!empty($work_types) && !empty($new_tree_inventory) && !empty($new_tree_inventory->ti_id)){
                            foreach ($work_types as $key => $val){
                                TreeInventoryWorkTypes::create([
                                    'tiwt_tree_id' => $new_tree_inventory->ti_id,
                                    'tiwt_work_type_id' => $val['tiwt_work_type_id']
                                ]);
                            }
                        }
                    }
                }
                $tree_inventory_scheme =  TreeInventoryScheme::find($copy_id);
                if(!empty($tree_inventory_scheme) && !empty($tree_inventory_scheme->tis_overlay_path) && empty($_FILES['tis_overlay'])){
                    $ext = substr($tree_inventory_scheme->tis_overlay_path, strpos($tree_inventory_scheme->tis_overlay_path, "."));
                    $target = tree_inventory_project_overlay_path($client_id, $tis_id, 'overlay' . rand(1,1000) . $ext);
                    bucket_copy(ltrim($tree_inventory_scheme->tis_overlay_path,'/'), $target, $options = []);
                    TreeInventoryScheme::where('tis_id', $tis_id)->update(['tis_overlay_path' => '/' . $target]);
                }
            }

            if(!empty($tis_id) && !empty($_FILES['tis_overlay'])) {
               $result = $this->treeinventoryschemeactions->uploadOverlay($client_id, $tis_id, $_FILES);
               if(!empty($result) && is_array($result) && $result['status'] === true && !empty($result['path'])){
                   TreeInventoryScheme::where('tis_id', $tis_id)->update(['tis_overlay_path' => $result['path']]);
                   if($update)
                       TreeInventory::where('ti_tis_id', $tis_id)->delete();
               }
            }
        }
        $data['tis_id'] = isset($tis_id) ? $tis_id : '';
        $data['update'] = $update;
        $schemes =  TreeInventoryScheme::where('tis_client_id', $client_id)->get();
        $client = Client::find($client_id);
        $data['section_tree_projects'] = $this->load->view('_partials/section_tree_projects', ['schemes' => $schemes, 'client_data' => $client], true);
        return $this->successResponse($data);
    }

    public function edit_project(){
	    if(!empty($this->input->post('tis_id'))) {
            $scheme = TreeInventoryScheme::where('tis_id', $this->input->post('tis_id'))->get()->first()->toArray();
            if(!empty($scheme)) {
                $client_id = $this->input->post('tis_client_id');
                $schemes =  TreeInventoryScheme::where('tis_client_id', $client_id)->get();
                $client = Client::find($client_id);
                $data['section_tree_projects'] = $this->load->view('_partials/section_tree_projects', ['schemes' => $schemes, 'client_data' => $client], true);
                $data['project'] = $scheme;
                return $this->successResponse($data);
            }
            return $this->errorResponse('Required scheme not found!');
        }
        return $this->errorResponse('Required scheme id!');
    }

    public function delete_project(){
        if(!empty($this->input->post('tis_id'))) {
            $scheme = TreeInventoryScheme::where('tis_id', $this->input->post('tis_id'))->delete();
            $client_id = $this->input->post('tis_client_id');
            $schemes =  TreeInventoryScheme::where('tis_client_id', $client_id)->get();
            $client = Client::find($client_id);
            $data['section_tree_projects'] = $this->load->view('_partials/section_tree_projects', ['schemes' => $schemes, 'client_data' => $client], true);
            $data['project'] = $scheme;
            return $this->successResponse($data);
        }
        return $this->errorResponse('Required scheme id!');
    }

    public function scheme($client_id){
	    if(!$client_id )
            show_404();
        $data['title'] = $this->_title . ' - Tree inventory';
        $data['client_data'] = $data['client'] = $client = Client::find($client_id);
        $data['schemes'] =  TreeInventoryScheme::where('tis_client_id', $client_id)->get();
        $data['tree_project_tmp'] = json_encode($this->load->view('_partials/tree_project', [], true));

        if(empty((array)$data['client']))
            show_404();

        $data['client_address'] = $data['home_address'] = client_address($data['client']->toArray());
        $leads = Lead::where('client_id', $client_id)
            ->where(function (Builder $query) {
                $query->where(function (Builder $query) {
                    $query->defaultStatus();
                });
                $query->orWhere(function (Builder $query) {
                    $query->draftStatus();
                });
            })->select(['lead_id', 'lead_no'])->get()->toArray();
        $data['leads'] = json_encode($leads ?? []);

        if(!count($data['schemes'])){
            $tis_name = $this->get_tree_inventory_name_from_client($client);
            $new_scheme = [
                'tis_name' => $tis_name,
                'tis_client_id' => $client->client_id,
                'tis_address' => $client->client_address,
                'tis_city' => $client->client_city,
                'tis_state' => $client->client_state,
                'tis_zip' => $client->client_zip,
                'tis_country' => $client->client_country,
                'tis_lat' => $client->client_lat ?? '',
                'tis_lng' => $client->client_lng ?? '',
            ];
            $tis = TreeInventoryScheme::create($new_scheme);
            if(!empty($tis))
                $data['schemes'] = [$tis];
        }
        $data['work_types'] = WorkType::all();
        $data['trees'] = $this->mdl_trees->get_trees();

        /*----------map data-----------*/
        $data = $this->map_data($data, $client_id, false, false);
        /*----------map data-----------*/

        $data['priority_color'] = $this->tree_inventory->priority_color;

        if($this->input->is_ajax_request())
            return $this->ajax_response("ok", ['response'=>$data]);

        $this->load->view('map', $data);
    }

    public function get_tree_history_data(){
        $tis_id = $this->input->post('tis_id');
        if(!empty($tis_id)) {
            $info = TreeInventoryEstimateService::where('ti_id',$tis_id)->with('estimate')->with('work_types:ip_name_short')->with('estimates_services')->orderBy('ties_id', 'DESC')->get()->toArray();
             return $this->successResponse(['list'=>$info]);
        }
        return $this->errorResponse('Required tis_id!');
    }

    public function get_scheme_data(){
	    $tis_id = $this->input->post('tis_id');
	    if(!empty($tis_id)) {
            $where['ti_tis_id'] = $tis_id;
            $data['tree_inventory'] = [];
            $data['tree_inventory'] = $this->tree_inventory->with('tree_type')->with('work_types')->order_by('ti_tree_number')->get_many_by($where);
            if ($data['tree_inventory']) {
                array_walk_recursive($data['tree_inventory'], function (&$item) {
                    $item->ti_remark = str_replace("'", '`', $item->ti_remark);
                    $item->ti_remark = str_replace("\"", '`', $item->ti_remark);
                });
            }
            $data['tree_inventory'] = inventory_pic($data['tree_inventory'], base_url('assets/img/nopic.jpg'));
            $scheme = TreeInventoryScheme::find($tis_id);
            $data['tree_project'] = $this->load->view('_partials/tree_project', ['project' => $scheme, 'edit' => true], true);
            $data['tis_id'] = $tis_id;
            $data['tis_lat'] = $scheme->tis_lat;
            $data['tis_lon'] = $scheme->tis_lng;
            $data['tis_overlay_path'] = $scheme->tis_overlay_path;
            return $this->successResponse($data);
        }
        return $this->errorResponse('Required tis_id!');
    }

	private function map_data($data, $client_id=false, $lead_id=false, $tis_id = false){
		
		$where = ['ti_client_id'=>$client_id];
		$where_map = ['tim_client_id'=>$client_id];
		if($lead_id){
            $where_map['tim_lead_id'] = $data['ti_lead_id'] = $where['ti_lead_id'] = $lead_id;
        }
		if($tis_id){
            $where['ti_tis_id'] = $tis_id;
        }

		$data['tree_inventory'] = [];
		$data['tree_inventory_map'] = $this->tree_inventory_map->get_by($where_map);

		$data['ti_map_type'] = $where['ti_map_type'] = 'map';
		if(!empty($data['tree_inventory_map'])){
		    if($tis_id)
			    $data['map_image'] = inventory_map_image($data['tree_inventory_map']->tim_client_id, $tis_id, $data['tree_inventory_map']->tim_file_name);
		    else
                $data['map_image'] = inventory_map_image($data['tree_inventory_map']->tim_client_id, $data['tree_inventory_map']->tim_lead_id, $data['tree_inventory_map']->tim_file_name);
			$data['ti_map_type'] = $where['ti_map_type'] = 'image';
		}

		$data['tree_inventory'] = $this->tree_inventory->with('tree_type')->with('work_types')->order_by('ti_tree_number')->get_many_by($where);
        if ($data['tree_inventory']) {
            array_walk_recursive($data['tree_inventory'], function(&$item){
                $item->ti_remark = str_replace("'", '`', $item->ti_remark);
                $item->ti_remark = str_replace("\"", '`', $item->ti_remark);
            });
        }
		$data['tree_inventory'] = inventory_pic($data['tree_inventory'], base_url('assets/img/nopic.jpg'));

		return $data;
	}

	private function ajax_response($status, $data){
		if ($status=='ok'){
			$data['status'] = $status;
			echo json_encode($data);
		}
		else{
			echo json_encode(['status'=>$status, 'errors'=>$data]);
		}
			
		return;
	}

	private function create_lead($tree_inventory_schema_id, $lead_status_key = 'lead_status_default'){
        if(!empty($tree_inventory_schema_id)){
            $new_lead =  $this->leadsactions->getLeadByTreeInventoryScheme($tree_inventory_schema_id, $lead_status_key);
            if(!empty($new_lead)) {
                $lead = Lead::create($new_lead);
                Lead::find($lead->lead_id)->update(['lead_no' => str_pad($lead->lead_id, 5, '0', STR_PAD_LEFT) . '-L']);
            }
        }
        return isset($lead) && !empty($lead) ? $lead : "";
    }

    private function create_lead_estimate_wo($tree_inventory_ids, $tree_inventory_schema_id, $service_status, $tree_inventory_screen = ''){
        if(!empty($tree_inventory_schema_id)) {
            $lead = $this->create_lead($tree_inventory_schema_id, 'lead_status_estimated');
            if(!empty($lead) && !empty($tree_inventory_ids)){
                if(!empty($tree_inventory_screen)){
                    $this->leadsactions->setLead($lead->lead_id);
                    $this->leadsactions->updateTreeInventoryScreenshot($tree_inventory_screen, '_tree_inventory_map');
                }
                $estimateToDB = $this->estimateactions->getEstimateFromLeadToDB($lead);
                $this->estimateactions->create($estimateToDB);
                $this->estimateactions->setEstimateServicesFromTreeInventoryIds($tree_inventory_ids, $service_status);
                $result =  $this->workorderactions->create($this->estimateactions->getEstimateId());
                if($result) {
                    $wo = $this->workorderactions->getWorkOrder();
                    return [
                        'client_id' => $wo->client_id,
                        'estimate_id' => $wo->estimate_id,
                        'workorder_id' => $wo->id,
                        'workorder_no' => $wo->workorder_no
                    ];
                }
            }
        }
        return false;
    }

    private function get_tree_inventory_name_from_client(object $client): string {
	    $name = "";
	    if(!empty($client->client_address))
	        $name .= $client->client_address . ', ';
        if(!empty($client->client_city))
            $name .= $client->client_city . ', ';
        if(!empty($client->client_state))
            $name .= $client->client_state . ', ';
        if(!empty($client->client_zip))
            $name .= $client->client_zip . ', ';
        if(!empty($client->client_country))
            $name .= $client->client_country;
	    return $name ?: 'Tree inventory ' . getNowDateTime(getDateFormat());
    }
}
