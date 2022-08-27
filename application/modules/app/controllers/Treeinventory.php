<?php if (!defined('BASEPATH')) exit('No direct script access allowed.');

use application\modules\estimates\models\TreeInventoryEstimateService;
use application\modules\leads\models\Lead;
use application\modules\tree_inventory\models\TreeInventoryScheme;
use application\modules\tree_inventory\requests\TreeInventorySchemeRequest;
use Illuminate\Validation\ValidationException;
use application\modules\estimates\models\Service;
use application\modules\workorders\models\Workorder;
use application\modules\invoices\models\Invoice;
use application\modules\tree_inventory\models\TreeInventory as TreeInventoryModal;

class Treeinventory extends APP_Controller //MX_Controller
{

    function __construct() {
        parent::__construct();
        $this->load->model('mdl_leads');
        $this->load->model('mdl_clients');
        //$this->load->model('mdl_clients');
        $this->load->helper('tree');

        $this->load->model('mdl_trees');
        $this->load->model('mdl_work_types_orm', 'work_types');

        $this->load->model('mdl_tree_inventory_orm', 'tree_inventory');
        $this->load->model('mdl_tree_inventory_map_orm', 'tree_inventory_map');
        $this->load->model('mdl_tree_inventory_work_types_orm', 'tree_inventory_work_types');

        $this->load->library('Common/ClientsActions');
        $this->load->library('Common/LeadsActions');
        $this->load->library('Common/EstimateActions');
        $this->load->library('Common/WorkorderActions');
        $this->load->library('Common/invoiceActions');
        $this->load->library('Common/TreeInventorySchemeActions');

    }

    public function fetch($id = null) {

        if(!$id)
            return $this->response(['status' => FALSE, 'message' => 'Wrong ID provided'], 400);

        $response = ['status'=>TRUE, 'data'=>[]];
        $this->clientsactions->set_client($id);
        $client = $this->clientsactions->get_client();
        if(!$client)
            return $this->response(['status' => FALSE, 'message' => 'Client is not defined'], 400);
        
        $response['data'] = $this->leadsactions->tree_inventory_static_data();
        $response['data']['client'] = $client;
        $response['data']['client']->leads = [];
        foreach ($this->clientsactions->leads() as $key => $lead) {
            $response['data']['client']->leads[] = $this->leadsactions->lead_tree_inventory($lead);
        }

        $schemes = TreeInventoryScheme::where('tis_client_id', $id)->get();
        if(!empty($schemes))
            foreach ($schemes as $key => $scheme) {
                $response['data']['client']->schemes[] = $this->leadsactions->schema_tree_inventory($scheme);
            }

        return $this->response($response, 200);
    }

    function save()
    {
        $this->load->library('form_validation');
        $result = $this->leadsactions->tree_inventory_save_point($this->input->post());
        if($result['result']==false)
            return $this->response([
              'status' => FALSE, 
              'errors' => $result['errors']
            ], 400);

        if (isset($_FILES['file']) && !$_FILES['file']['error']){
          $upload = $this->leadsactions->tree_inventory_point_file($_FILES['file'], $this->input->post('ti_tis_id'), $result['result']);

          if(!$upload['result'])
            return $this->response([
              'status' => FALSE, 
              'message' => $result['error']
            ], 400);
        } 
        
        $response = ['status'=>true, 'data'=>[]];
        $this->clientsactions->set_client($this->input->post('ti_client_id'));
        $client = $this->clientsactions->get_client();
        $response['data'] = $this->tree_inventory->get($result['result']);
        
        return $this->response($response, 200);
    }

    function update_screen(){
      if (!$this->input->post('ti_client_id'))
        return $this->response(['status' => FALSE, 'message' => 'Client is not defined'], 400);

      if (!$this->input->post('ti_lead_id'))
        return $this->response(['status' => FALSE, 'message' => 'Lead is not defined'], 400);
      
      if ($this->input->post('map_image')){
        $this->leadsactions->setLead($this->input->post('ti_lead_id'));
        $this->leadsactions->updateTreeInventoryScreenshot($this->input->post('map_image'));
      }
      
      $response = ['pdf'=>base_url('/app/treeinventory/pdf/'.$this->input->post('ti_lead_id'))];
      return $this->response($response, 200);
    }

    function delete()
    {
      if(!$this->input->post('ti_id'))
        return $this->response(['status' => FALSE, 'message' => 'Pin is not defined'], 400);

      $result = $this->leadsactions->tree_inventory_delete_point($this->input->post('ti_id'));

      if(!$result['status'])
        return $this->response(['status' => FALSE, 'message' => 'Delete errors'], 400);

      $response = ['status'=>true];
      return $this->response($response, 200);
    }

  public function pdf($lead_id){
    if(!(int)$lead_id)
      return $this->response(['status' => FALSE, 'message' => 'Lead is not exist'], 400);
    
    $this->leadsactions->setLead((int)$lead_id);
    
    if (!$this->leadsactions->getLead())
      return $this->response(['status' => FALSE, 'message' => 'Lead is not exist1'], 400);

    $this->load->library('mpdf', ['orientation'=>'L']);
    $html = $this->leadsactions->tree_inventory_pdf();
    
    $this->mpdf->WriteHTML($html);
    $this->mpdf->_setPageSize('Letter', $this->mpdf->DefOrientation);
    $this->mpdf->SetHtmlFooter('');

    $this->mpdf->Output('TreeInventory.pdf', 'I');
  }

  public function copy()
  {
    $this->load->library('form_validation');

    $ti_client_id_from  = $this->input->post('ti_client_id_from');
    $ti_lead_id_from    = $this->input->post('ti_lead_id_from');
    $ti_client_id_to    = $this->input->post('ti_client_id_to');
    $ti_lead_id_to      = $this->input->post('ti_lead_id_to');
    
    if (!$ti_client_id_from || !$ti_lead_id_from || !$ti_client_id_to || !$ti_lead_id_to)
      return $this->response(['status' => FALSE], 400);

    $data['tree_inventory'] = $this->tree_inventory
    ->with('work_types')
    ->with('tree_type')
    ->order_by('ti_tree_number')
    ->get_many_by(['ti_client_id'=>$ti_client_id_from, 'ti_lead_id'=>$ti_lead_id_from]);

    $data['tree_inventory_old'] = $this->tree_inventory
    ->with('work_types')
    ->with('tree_type')
    ->order_by('ti_tree_number')
    ->get_many_by(['ti_client_id'=>$ti_client_id_to, 'ti_lead_id'=>$ti_lead_id_to]);
    
    if (count($data['tree_inventory_old'])){
      $this->leadsactions->deleteTreeInventoryScreen($ti_client_id_to, $ti_lead_id_to);

      foreach ($data['tree_inventory_old'] as $key => $value) {
        $this->leadsactions->tree_inventory_delete_point($value->ti_id, $value);
      }
    }

    $this->leadsactions->copyMapScreen($ti_lead_id_from, $ti_client_id_to, $ti_lead_id_to);
    
    if (!count($data['tree_inventory']))
      return $this->response(['status'=>true, 'data'=>[]], 200);

    foreach ($data['tree_inventory'] as $key => $value) {

      $work_types = [];
      if(count($value->work_types))
        $value->work_types = array_map(function($v){ return $v->tiwt_work_type_id; }, $value->work_types);

      $value->ti_client_id = $ti_client_id_to;
      $value->ti_lead_id = $ti_lead_id_to;

      unset($value->ti_id);
      unset($value->tree_type);
      
      $result = $this->leadsactions->tree_inventory_save_point((array)$value);
      
      if($value->ti_file)
        $this->leadsactions->copy_point_file($ti_lead_id_from, $ti_lead_id_to, $value->ti_file, $result['result']);
    }
    
    
    $this->leadsactions->setLead($ti_lead_id_to);
    $lead = $this->leadsactions->getLead();

    $response = ['status'=>true];
    $response['data'] = $this->leadsactions->lead_tree_inventory($lead);
    
    return $this->response($response, 200);
  }

  public function upload_map_image()
  {
    if (!isset($_FILES['map']) || $_FILES['map']['error']){
      return $this->response([
        'status' => FALSE, 
        'message' => 'File is not valid'
      ], 400); 
    }
    
    if (isset($_FILES['map']) && !$_FILES['map']['error']){
      $upload = $this->leadsactions->uploadTreeInventoryScreen($_FILES, $this->input->post('ti_client_id'), $this->input->post('ti_lead_id'));

      if($upload['status']===false){
        return $this->response([
          'status' => FALSE, 
          'message' => 'File is not valid'
        ], 400);
      }
      
      $response = [
        'status' => true, 
        'data' => $upload['result']
      ];
      return $this->response($response, 200);
    }

    $this->tree_inventory_map->save(['tim_client_id'=>$this->input->post('ti_client_id'), 'tim_lead_id'=>$this->input->post('ti_lead_id'), 'tim_image'=>0]);

    return $this->ajax_response('errors', ['map'=>'File is not valid']);
  }

  public function delete_map_image()
  {
    $result = $this->leadsactions->deleteTreeInventoryScreen($this->input->post('ti_client_id'), $this->input->post('ti_lead_id'));

    if ($result['status']===false){
      return $this->response([
        'status' => FALSE, 
        'message' => 'File is not defined'
      ], 400);
    }

    $response = ['status'=>true];
    return $this->response($response, 200);
  }

  public function create_estimate(){
      $tree_inventory_ids = json_decode($this->input->post('ti_ids'));
      $tree_inventory_schema_id = json_decode($this->input->post('ti_tis_id'));
      $tree_inventory_screen = $this->input->post('screen_map');
      $lead_id = $this->input->post('lead_id');
      if(!empty($tree_inventory_schema_id) && (empty($lead_id) || $lead_id == 'new')) {
          $lead = $this->create_lead($tree_inventory_schema_id);
          if(!empty($lead) && !empty($lead->lead_id))
              $lead_id = $lead->lead_id;
      }else{
          $this->response(['status' => false, 'message' => 'incorrect ti_tis_id'], 200);
      }
      if(!empty($tree_inventory_ids) && is_array($tree_inventory_ids) && !empty($lead_id)) {
          $this->leadsactions->setTreeInventoryDraftService($tree_inventory_ids, $lead_id);
          if(empty($lead))
            $lead = Lead::find($lead_id);
          $this->response(['status' => true, 'data' => ['lead_id' => $lead_id, 'client_id' => $lead->client_id]], 200);
      }else{
          $this->response(['status' => false, 'message' => 'incorrect tree_inventory_ids'], 200);
      }
      if(!empty($tree_inventory_screen) && !empty($lead_id)){
          $this->leadsactions->setLead($lead_id);
          $this->leadsactions->updateTreeInventoryScreenshot($tree_inventory_screen, '_tree_inventory_map');
      }
  }

  public function create_wo(){
      $tree_inventory_ids = json_decode($this->input->post('ti_ids'));
      $tree_inventory_schema_id = json_decode($this->input->post('ti_tis_id'));
      $tree_inventory_screen = $this->input->post('screen_map');
      $result = $this->create_lead_estimate_wo($tree_inventory_ids, $tree_inventory_schema_id, Service::SERVICE_STATUS_NEW, $tree_inventory_screen);
      if($result)
          return $this->successResponse(['data' => $result]);
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
                        ]
                    ]);
            }
        }
        return $this->errorResponse([]);
    }

  public function save_project(){
      try {
          /** @var TreeInventorySchemeRequest $request */
          $request = app(TreeInventorySchemeRequest::class);
      } catch (ValidationException $e) {
          return $this->errorResponse(null, $e->validator->errors());
      }
      $client_id = $this->input->post('tis_client_id');
      $update = false;
      if(!empty($this->input->post('tis_name'))){
          if(empty($this->input->post('tis_id'))){
              $tis = TreeInventoryScheme::create($request->validated());
              if(!empty($tis))
                $tis_id = $tis->tis_id;
          } else{
              $tis_id = $this->input->post('tis_id');
              TreeInventoryScheme::find($tis_id)->update($request->validated());
              $update = true;
          }
      }

      if(!empty($this->input->post('tis_copy') && !empty($this->input->post('tis_copy_id')))){
          $copy_id = $this->input->post('tis_copy_id');
          $tree_inventories = TreeInventoryModal::where('ti_tis_id', $copy_id)->get()->toArray();
          if(!empty($tree_inventories)){
              foreach ($tree_inventories as $tree_inventory){
                  $tree_inventory['ti_tis_id'] = $tis_id;
                  unset($tree_inventory['ti_id']);
                  TreeInventoryModal::create($tree_inventory);
                  if(!empty($tree_inventory['ti_file'])) {
                      $source = 'uploads/tree_inventory/' . $copy_id . '/' . $tree_inventory['ti_file'];
                      $target = 'uploads/tree_inventory/' . $tis_id . '/' . $tree_inventory['ti_file'];
                      bucket_copy($source, $target, $options = []);
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

      if(!empty($tis_id) && !empty($_FILES['tis_overlay']) && !empty($client_id)) {
          $result = $this->treeinventoryschemeactions->uploadOverlay($client_id, $tis_id, $_FILES);
          if(!empty($result) && is_array($result) && $result['status'] === true && !empty($result['path'])){
              TreeInventoryScheme::where('tis_id', $tis_id)->update(['tis_overlay_path' => $result['path']]);
              if($update)
                  TreeInventoryModal::where('ti_tis_id', $tis_id)->delete();
          }
      }

      return $this->successResponse(['tis_id' => isset($tis_id) ? $tis_id : '']);
  }

  public function delete_project(){
        if(!empty($this->input->post('tis_id'))) {
            TreeInventoryScheme::find($this->input->post('tis_id'))->delete();
            return $this->successResponse([]);
        }
        return $this->errorResponse('Required scheme id!');
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
                    ];
                }
            }
        }
        return false;
    }

    public function get_tree_history_data(){
        $tis_id = $this->input->post('tis_id');
        if(!empty($tis_id)) {
            $info = TreeInventoryEstimateService::where('ti_id',$tis_id)->with('estimate')->with('work_types:ip_name_short')->with('estimates_services')->orderBy('ties_id', 'DESC')->get()->toArray();
            return $this->successResponse(['list'=>$info]);
        }
        return $this->errorResponse('Required tis_id!');
    }

}