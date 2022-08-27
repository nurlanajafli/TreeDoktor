<?php

use application\modules\estimates\models\Service;
use application\modules\leads\models\Lead;
use application\modules\tree_inventory\models\TreeInventoryScheme;
use application\modules\leads\models\LeadStatus;
use application\modules\tree_inventory\models\TreeInventory;
use application\modules\tree_inventory\models\TreeInventoryWorkTypes;
use application\modules\user\models\User;

class LeadsActions
{
    protected $CI;    
    
    protected $treeInventory;
    protected $lead;
    protected $client;


    function __construct($leadId=null) {
        $this->CI =& get_instance();

        $this->CI->load->helper('tree');
        $this->CI->load->helper('user');

        $this->CI->load->model('mdl_clients');
		$this->CI->load->model('mdl_leads_status');
		$this->CI->load->model('mdl_leads');
		$this->CI->load->model('mdl_leads_services');
		$this->CI->load->model('mdl_client_tasks');
		$this->CI->load->model('mdl_paint');
		$this->CI->load->model('mdl_user');
        
        /*----------------Tree Inventory-----------------*/
        $this->CI->load->model('mdl_trees');
        $this->CI->load->model('mdl_work_types_orm', 'work_types');
        $this->CI->load->model('mdl_tree_inventory_orm', 'tree_inventory');
        $this->CI->load->model('mdl_tree_inventory_map_orm', 'tree_inventory_map');
        $this->CI->load->model('mdl_tree_inventory_work_types_orm', 'tree_inventory_work_types');
        /*----------------Tree Inventory-----------------*/

        $this->CI->load->library('Common/EstimateActions');

        if($leadId) {
            $this->lead = $this->CI->mdl_leads->find_by_id($leadId);
            if($this->lead)
                $this->client = $this->CI->mdl_clients->get_client_by_id($this->lead->client_id);
        }

    }
    
    function clear() {
        $this->treeInventory = NULL;
        $this->client = NULL;
        $this->lead = NULL;
    }

    function setLead($leadId) {
        $this->lead = $this->CI->mdl_leads->find_by_id($leadId);
        if($this->lead)
            $this->client = $this->CI->mdl_clients->get_client_by_id($this->lead->client_id);

        if(!$this->lead || !$this->client)
            return false;

        return true;
    }

    function getLead() {
        if($this->lead)
            return $this->lead;

        return false;
    }

    public function getId(){
        if($this->lead)
            return $this->lead->lead_id;

        return false;
    }

    function getClient() {
        if($this->client)
            return $this->client;

        return false;
    }

    function updateTreeInventoryScreenshot($image = null, $postfix_file_name = '' )
    {
        if(!$this->lead)
            return false;
        if(!$image || !$this->lead->client_id || !$this->lead->lead_id)
            return false;

        $file_name = $this->lead->lead_id;
        if(!empty($postfix_file_name))
            $file_name .= $postfix_file_name;
        $path = inventory_screen_path($this->lead->client_id, $file_name.'.png');

        $img = str_replace('data:image/png;base64,', '', $image);
        $img = str_replace('[removed]', '', $img);
        $img = str_replace(' ', '+', $img);
        $data_img = base64_decode($img);

        try {
            $im = imagecreatefromstring($data_img);
            $width = imagesx($im);
            $height = imagesy($im);

            if($width > 2000 || $height > 2000) {
                $percent = 0.5;
                if ($width > 3000 || $height > 3000)
                    $percent = 0.3;
                $new_width = $width * $percent;
                $new_height = $height * $percent;

                // Resample
                $image_p = imagecreatetruecolor($new_width, $new_height);
                imagecopyresampled($image_p, $im, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
            }

            $white = imagecolorallocate($image_p ?? $im, 241,243,247);
            imagecolortransparent($image_p ?? $im, $white);

            $local_file = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $this->lead->client_id . '_' . $this->lead->lead_id . '.png';

            imagepng($image_p ?? $im, $local_file, 0);
            imagedestroy($im);
            bucket_unlink($path);
            bucket_move($local_file, $path);
            @unlink($local_file);

        } catch (Exception $e) {

        }

        return true;
    }
    function tree_inventory_pdf($pagebreak=false)
    {
        $body['title'] = 'Tree Inventory';
        $body['html'] = $this->tree_inventory_pdf_body();
        if(!$body['html'])
            return '';

        if($pagebreak)
            $body['pagebreak'] = $pagebreak;

        $html = $this->CI->load->view('tree_inventory/tree_inventory_pdf', $body, TRUE);
        return $html;
    }

    function tree_inventory_pdf_body(){

        $data['tree_inventory'] = $this->CI->tree_inventory->with('work_types')->with('tree_type')->order_by('ti_tree_number')->get_many_by(['ti_client_id'=>$this->lead->client_id, 'ti_lead_id'=>$this->lead->lead_id]);
        
        if(!$data['tree_inventory'] || !count($data['tree_inventory'])){
            return '';
        }

        $data['work_types'] = $this->CI->work_types->get_all();
        $data['tree_inventory'] = inventory_pic($data['tree_inventory'], base_url('assets/img/nopic.jpg'));
        

        $data['client'] = $this->client; //$this->mdl_clients->get_client_by_id($where['ti_client_id']);
        $data['client_address'] = client_address((array)$this->client);
        $data['taxRate'] = getDefaultTax()['rate'];
        $data['screen']= inventory_screen_path($this->lead->client_id, $this->lead->lead_id.'.png');
        $data['screen_src'] = false;
        if(is_bucket_file($data['screen']))
            $data['screen_src'] = base_url($data['screen']);

        return $this->CI->load->view('tree_inventory/tree_inventory_pdf_body', $data, TRUE);     
    }

    function create($data, $post, $servicesEst, $preuploaded_files, bool $isDraft = false) {
        $defaultStatus = $this->CI->mdl_leads_status->get_by(['lead_status_default' => 1]);
        $leadStatus = $defaultStatus->lead_status_id;

        if($isDraft === true) {
            $draftStatus = $this->CI->mdl_leads_status->get_by(['lead_status_draft' => 1]);
            $leadStatus = isset($draftStatus->lead_status_id) ? $draftStatus->lead_status_id : $leadStatus;
        }

        $data['lead_status_id'] = $leadStatus;
		
		$lead_id = $this->CI->mdl_leads->insert_leads($data);

		if ($lead_id) {
			$lead_no = str_pad($lead_id, 5, '0', STR_PAD_LEFT);
			$lead_no = $lead_no . "-L";
			$update_data = array("lead_no" => $lead_no);
			$wdata = array("lead_id" => $lead_id);

			$lead_no_updated = $this->CI->mdl_leads->update_leads($update_data, $wdata);			
			
			$post['lead_id'] = $lead_id;
			$post['latitude'] = $data['latitude'];
			$post['longitude'] = $data['longitude'];
			
			$office_data = $this->CI->mdl_client_tasks->office_data($post);						
			
			if($servicesEst != '')
			{
				$services = explode('|', $servicesEst);
				foreach($services as $k=>$v)
					$this->CI->mdl_leads_services->insert(['lead_id' => $lead_id, 'services_id' => intval($v)]);
			}	
			//services - end
            
            //move files from tmp to the actual lead_id folder
            $note = '';
            if(!empty($preuploaded_files)){
                $batchUpdate = [];
                foreach($preuploaded_files as $key => $file) {
                    $name_parts = explode('/', $file);
                    $file_name = $name_parts[count($name_parts)-1];
                    $new_path = 'uploads/clients_files/' . $data['client_id'] . '/leads/' . str_pad($lead_id, 5, '0', STR_PAD_LEFT) . '-L/' . str_replace('0-L', str_pad($lead_id, 5, '0', STR_PAD_LEFT) . '-L', $file_name);
                    bucket_copy($file, $new_path);

                    $note.= '<a href="' . base_url($new_path) . '">Lead File ' . ($key + 1) . '</a><br>';
                        /*$this->CI->load->view('leads/partials/lead_file_note_tmp', [
                        'new_path'=>$new_path,
                        'filename'=>str_replace('0-L', str_pad($lead_id, 5, '0', STR_PAD_LEFT) . '-L', $file_name),
                        'lead_no'=>$lead_no
                    ], TRUE);*/

                    bucket_unlink($file);
                    $batchUpdate[] = [
                        'paint_path' => $file,
                        'paint_path ' => $new_path //space in key is required !!!
                    ];
                }
                $this->CI->mdl_paint->updateBatchByPaths($batchUpdate);
            }
            //$note.= '<a href="' . base_url($new_path) . '">' . str_replace('0-L', str_pad($lead_id, 5, '0', STR_PAD_LEFT) . '-L', $file_name) . '</a>';
			/*if(!empty($preuploaded_files)){
				foreach($preuploaded_files as $file){
					$path_parts = explode('/', $file);
					$new_path = $path_parts[0] . '/' . $path_parts[1] . '/' . $path_parts[2] . '/' . $path_parts[3] . '/' . $lead_id . '-L/' . str_replace('0-L', $lead_id . '-L', $path_parts[6]);
					bucket_copy($file, $new_path);
				}
			}*/

			//every file that is not contained in pre uploaded files, we'll consider unneded tmp file from before, so it will be deleted
			bucket_unlink_all('uploads/clients_files/' . $data['client_id'] . '/leads/tmp/0-L/');

			if ($lead_no_updated) {

			    $assigned_to = ($data['lead_estimator'])?User::find($data['lead_estimator']):null;

				make_notes($data['client_id'], 'I just created a new lead "' . $lead_no . '" for the client.'.($assigned_to?(' Assigned to:'.$assigned_to->full_name):''), 'system', $lead_id);

                if($note) {
                    make_notes($data['client_id'], $note, 'system', $lead_id);
                }
			}

            return $lead_id;
		} else {
            return false;
        }
    }
    
    function tree_inventory_static_data()
    {
        return [
            'work_types' => $this->CI->work_types->get_all(),
            'trees' => $this->CI->mdl_trees->get_trees(),
            'priority_color' => $this->CI->tree_inventory->priority_color
        ];
    }

    function lead_tree_inventory($lead, $ti_id=false){
        $lead_data = $lead;

        $lead_data->tree_inventory_image = $this->CI->tree_inventory_map->get_by(['tim_client_id'=>$lead->client_id, 'tim_lead_id'=>$lead->lead_id]);
        
        if(!empty($lead_data->tree_inventory_image)){
            $lead_data->tree_inventory_image->tim_full_patch  = inventory_map_image($lead_data->tree_inventory_image->tim_client_id, $lead_data->tree_inventory_image->tim_lead_id, $lead_data->tree_inventory_image->tim_file_name);
        }

        $where = ['ti_client_id'=>$lead->client_id, 'ti_lead_id'=>$lead->lead_id];
        if($ti_id)
            $where['ti_id'] = $ti_id;
        
        $lead_data->points = $this->CI->tree_inventory->with('tree_type')->with('work_types')->order_by('ti_tree_number')->get_many_by($where);
        $lead_data->points = inventory_pic($lead_data->points);
        
        return $lead_data;
    }

    function schema_tree_inventory($schema, $ti_id=false){
        $schema_data = $schema;
        $schema_data->tree_inventory_image = $this->CI->tree_inventory_map->get_by(['tim_client_id'=>$schema->tis_client_id]);

        $where = ['ti_client_id'=>$schema->tis_client_id, 'ti_tis_id'=>$schema->tis_id];
        if($ti_id)
            $where['ti_id'] = $ti_id;

        $schema_data->points = $this->CI->tree_inventory->with('tree_type')->with('work_types')->order_by('ti_tree_number')->get_many_by($where);
        $schema_data->points = inventory_pic($schema_data->points);

        return $schema_data;
    }

    function tree_inventory_save_point($data){
        $pk = false;
        if(isset($data[$this->CI->tree_inventory->primary_key()])){
            $pk = $data[$this->CI->tree_inventory->primary_key()];
            unset($data[$this->CI->tree_inventory->primary_key()]);
        }

        if(!isset($data['ti_tree_priority']) || !$data['ti_tree_priority'])
          $data['ti_tree_priority'] = 'medium';

        $work_types = element('work_types', $data, []);
        if(isset($data['work_types']))
            unset($data['work_types']);
        
        $result = $this->CI->tree_inventory->save($data, (int)$pk);
              
        if($result==false)
            return ['result'=>$result, 'errors'=>$this->CI->tree_inventory->validation_errors];

        $ti_id = ($pk)?$pk:$result;
        $this->CI->tree_inventory_work_types->sync($ti_id, $work_types);

        return ['result'=>$ti_id];
    }

    function tree_inventory_point_file($file, $lead_id, $ti_id)
    {
        $this->CI->load->library('upload');
        $path = inventory_path($lead_id);
        $ext = '.'.pathinfo($file['name'], PATHINFO_EXTENSION);
        $config['allowed_types'] = 'gif|jpg|jpeg|png|GIF|JPG|JPEG|PNG';
        $config['overwrite'] = TRUE;
        $config['upload_path'] = $path;
        $config['file_name'] = $ti_id.$ext;
        
        $this->CI->upload->initialize($config);
        if (!$this->CI->upload->do_upload('file'))
            return ['result'=>false, 'error'=>'File is not valid'];
        
        $this->CI->tree_inventory->save(['ti_file'=>$config['file_name']], $ti_id);
        return ['result'=>true];
    }

    function tree_inventory_delete_point($ti_id, $tree = [])
    {   
        if(!$tree)
            $tree = $this->CI->tree_inventory->with('work_types')->get($ti_id);
        
        if(!$tree)
            return ['status'=>false];

        $this->CI->tree_inventory->delete($ti_id);
        $path = inventory_path($tree->ti_lead_id, $tree->ti_file);
        $result = bucket_unlink($path);
        
        if(count($tree->work_types)){
            foreach ($tree->work_types as $work_type_key => $work_type) {
                $this->CI->tree_inventory_work_types->delete($work_type->tiwt_id);
            }
        }

        return ['status'=>true, 'tree'=>$tree];
    }

    function copy_point_file($from_lead_id, $to_lead_id, $file, $new_point_id){
        
        $old_file = inventory_path($from_lead_id, $file);
        
        $ext = '.'.pathinfo($file, PATHINFO_EXTENSION);

        $name = $new_point_id.$ext;
        $new_file = inventory_path($to_lead_id, $name);

        $this->CI->tree_inventory->save(['ti_file'=>$name], $new_point_id);

        bucket_copy($old_file, $new_file);
        return true;
    }

    public function copyMapScreen($from_lead_id, $to_client_id, $to_lead_id){

        $tree_inventory_map = $this->CI->tree_inventory_map->get_by(['tim_lead_id'=>$from_lead_id]);
        
        if ($tree_inventory_map) {
            $path_from  = inventory_map_image_path($tree_inventory_map->tim_client_id, $tree_inventory_map->tim_lead_id, $tree_inventory_map->tim_file_name);

            $tree_inventory_map->tim_client_id = $to_client_id;
            $tree_inventory_map->tim_lead_id = $to_lead_id;

            unset($tree_inventory_map->tim_id);
            $this->CI->tree_inventory_map->save((array)$tree_inventory_map);
            
            $path_to = inventory_map_image_path($to_client_id, $to_lead_id, $tree_inventory_map->tim_file_name);

            bucket_copy($path_from, $path_to);
            return true;
        }

        return false;
    }

    public function uploadTreeInventoryScreen($file, $client_id, $lead_id)
    {
        $this->CI->load->library('upload');
        $ext = '.'.pathinfo($file['map']['name'], PATHINFO_EXTENSION);
        $config['allowed_types'] = 'gif|jpg|jpeg|png|GIF|JPG|JPEG|PNG';
        $config['overwrite'] = TRUE;
        $config['file_name'] = 'map'.$ext;
        $path = inventory_map_image_path($client_id, $lead_id);
        $config['upload_path'] = $path;
        $this->CI->upload->initialize($config);
        
        if (!$this->CI->upload->do_upload('map'))
            return ['status'=>false, 'errors'=>['file'=>'File is not valid']];

        list($width, $height) = getimagesize($_FILES['map']['tmp_name']);
        
        $this->CI->tree_inventory_map->save(['tim_client_id'=>$client_id, 'tim_lead_id'=>$lead_id, 'tim_image'=>1, 'tim_width'=>$width, 'tim_height'=>$height, 'tim_file_name'=>$config['file_name']]);

        $result = $this->CI->tree_inventory_map->get_by(['tim_client_id'=>$client_id, 'tim_lead_id'=>$lead_id]);
        if(!empty($result)){
            $result->tim_full_patch  = inventory_map_image($result->tim_client_id, $result->tim_lead_id, $result->tim_file_name);
        }

        return ['status'=>true, 'result'=>$result];
    }

    public function deleteTreeInventoryScreen($client_id, $lead_id)
    {
        $map = $this->CI->tree_inventory_map->get_by(['tim_client_id'=>$client_id, 'tim_lead_id'=>$lead_id]);
        
        if(empty($map))
            return ['status'=>false];

        $this->CI->tree_inventory_map->delete($map->tim_id);
        $path = inventory_map_image_path($client_id, $lead_id, $map->tim_file_name);
        bucket_unlink($path);

        $points = $this->CI->tree_inventory->with('work_types')->get_many_by([
            'ti_client_id' => $client_id, 
            'ti_lead_id' => $lead_id 
        ]);

        foreach ($points as $key => $point) {
            $this->tree_inventory_delete_point($point->ti_id, $point);
        }
        
        return ['status'=>true];
    }
    public function getLeadsId(array $leads) : array
    {
        $result = [];
        foreach ($leads as $lead)
            $result[] = $lead['lead_id'];
        return $result;
    }

    public function setTreeInventoryDraftService(array $tree_inventory_ids, $lead_id){
        if(!empty($tree_inventory_ids)) {
            foreach ($tree_inventory_ids as $tree_inventory_id) {
                $tree_inventory = null;
                $tree_inventory_work_types = [];
                if (!empty($tree_inventory_id)) {
                    TreeInventory::find($tree_inventory_id)->update(['ti_lead_id' => $lead_id]);
                    $tree_inventory = TreeInventory::find($tree_inventory_id);
                    $tree_inventory_work_types = TreeInventoryWorkTypes::where('tiwt_tree_id', $tree_inventory_id)->pluck('tiwt_work_type_id')->toArray();
                }else
                    continue;
                // set an estimate draft service
                $default_service = Service::where(['service_id' => config_item('tree_inventory_service_id')])->first();
                if (!empty($default_service)) {
                    $default_service_equipments = $this->CI->estimateactions->getEquipmentFromService($default_service);
                    $vehicles = isset($default_service_equipments['vehicles']) ? $default_service_equipments['vehicles'] : [];
                    $vehicle_option = isset($default_service_equipments['vehicle_option']) ? $default_service_equipments['vehicle_option'] : [];
                    $trailers = isset($default_service_equipments['trailers']) ? $default_service_equipments['trailers'] : [];
                    $trailer_option = isset($default_service_equipments['trailer_option']) ? $default_service_equipments['trailer_option'] : [];
                    $tools_option = isset($default_service_equipments['tools_option']) ? $default_service_equipments['tools_option'] : [];
                    $default_crews = isset($default_service_equipments['default_crews']) ? $default_service_equipments['default_crews'] : [];

//                    $description = $this->getTreeInventoryDescription($tree_inventory);
                    $title = $this->getTreeInventoryTitle($tree_inventory);
                    $cost = !empty($tree_inventory->ti_cost) ? $tree_inventory->ti_cost : 0;
                    $stump_price = !empty($tree_inventory->ti_stump_cost) ? $tree_inventory->ti_stump_cost : 0;

                    $ti_id = $tree_inventory->ti_id;
                    $estimate_draft_service = [
                        'client_id' => $tree_inventory->ti_client_id,
                        'lead_id' => $tree_inventory->ti_lead_id,
                        'service_id' => $ti_id,
                        'is_product' => [$ti_id => $default_service->is_product],
                        'is_bundle' => [$ti_id => $default_service->is_bundle],
                        'is_collapsed' => [$ti_id => $default_service->service_is_collapsed],
                        'class' => [$ti_id => $default_service->service_class_id],
                        'service_type_id' => [$ti_id => $default_service->service_id],
                        'service_price' => [$ti_id => $cost + $stump_price],
                        'service_description' => [$ti_id => $tree_inventory->ti_remark],
                        'tree_inventory_service' => [$ti_id => true],
                        'service_markup_rate' => [$ti_id => $default_service->service_markup],
                        'service_crew' => [$ti_id => $default_crews],
                        'service_vehicle' => [$ti_id => $vehicles],
                        'vehicle_option' => [$ti_id => $vehicle_option],
                        'service_trailer' => [$ti_id => $trailers],
                        'trailer_option' => [$ti_id => $trailer_option],
                        'tools_option' => [$ti_id => $tools_option],
                        'tree_inventory_title' => [$ti_id => $title],
                        'service_priority' => [$ti_id => $ti_id],
                        'ties_number' => [$ti_id => $tree_inventory->ti_tree_number],
                        'ties_type' => [$ti_id => $tree_inventory->ti_tree_type],
                        'ties_size' => [$ti_id => $tree_inventory->ti_size],
                        'ties_priority' => [$ti_id => $tree_inventory->ti_tree_priority],
                        'ties_cost' => [$ti_id => $cost],
                        'ties_stump' => [$ti_id => $stump_price],
                        'ties_work_types' => [$ti_id => json_encode($tree_inventory_work_types)]
                    ];
                    $this->CI->estimateactions->setEstimateDraftService($estimate_draft_service);


                        if (!empty($tree_inventory->ti_file)) {
                            $ti_file_path =  inventory_path($tree_inventory->ti_tis_id, $tree_inventory->ti_file);
                            $arrayFileName = explode('.', $tree_inventory->ti_file);
                            $this->CI->estimateactions->setEstimateDraftFiles([
                                'lead_id' => $tree_inventory->ti_lead_id,
                                'client_id' => $tree_inventory->ti_client_id,
                                'service_id' => $ti_id,
                                'files' => [[
                                    'filepath' => $ti_file_path,
                                    'name' => $tree_inventory->ti_file,
                                    'show_client' => true,
                                    'size' => '',
                                    'type' => 'image/' . array_pop($arrayFileName),
                                    'url' => base_url($ti_file_path),
                                    'uuid' => ''
                                ]
                                ]
                            ]);
                        }

//                        if (!empty($photos[0]) && !empty($photos[0]['filepath']))
//                            $this->CI->tree_inventory->save(['ti_file' => $photos[0]['filepath']], $ti_id);
//
//                        // delete old file from s3
//                        if (!empty($tree_inventory) && !empty($tree_inventory->ti_file) && is_bucket_file($tree_inventory->ti_file))
//                            bucket_unlink($tree_inventory->ti_file);
//                    }
                }
            }
        }
    }

    public function getLeadByTreeInventoryScheme($tis_id, $lead_status_key = 'lead_status_default'): array{
        $result = [];

        $tis = TreeInventoryScheme::find($tis_id);
        if(!empty($tis)){
            $userId = $this->CI->session->userdata('user_id');
            if(empty($userId))
                $userId = $this->CI->user->id;
            $status = LeadStatus::where($lead_status_key, 1)->first();
            $result = [
                'client_id' => $tis->tis_client_id,
                'lead_address' => $tis->tis_address,
                'lead_city' => $tis->tis_city,
                'lead_state' => $tis->tis_state,
                'lead_zip' => $tis->tis_zip,
                'lead_country' => $tis->tis_country,
                'latitude' => $tis->tis_lat,
                'longitude' => $tis->tis_lng,
                'timing' => 'Right Away',
                'lead_status' => !empty($status) ? $status->lead_status_name : '',
                'lead_status_id' => !empty($status) ? $status->lead_status_id : '',
                'lead_date_created' => date('Y-m-d H:i:s'),
                'lead_estimate_draft' => 1,
                'lead_author_id' => $userId
            ];
        }

        return  $result;
    }

    public function getTreeInventoryDescription(object $tree_inventory): string {
        $descriptions = "";
        $work_types = $this->CI->tree_inventory_work_types->get_many_by(['tiwt_tree_id' => $tree_inventory->ti_id]);

        if (!empty($work_types)) {
            foreach ($work_types as $key => $ti_work_type) {
                $work_type = $this->CI->work_types->get($ti_work_type->tiwt_work_type_id);
                if (!empty($work_type)) {
                    if ($key > 0)
                        $descriptions .= ", ";
                    $descriptions .= $work_type->ip_name;
                }
            }
            $descriptions .= "\n";
        }
        if(!empty($tree_inventory->ti_remark))
            $descriptions .= $tree_inventory->ti_remark;
        return trim($descriptions, " ");
    }

    public function getTreeInventoryTitle(object $tree_inventory): string{
        $title = "";
        if (!empty($tree_inventory->ti_tree_number)) {
            $title .= "TREE #" . $tree_inventory->ti_tree_number . " ";
        }
        if (!empty($tree_inventory->ti_tree_type)) {
            $tree = $this->CI->mdl_trees->get($tree_inventory->ti_tree_type);
            if (!empty($tree))
                $title .= $tree->trees_name_eng . " (" . $tree->trees_name_lat . ") ";
        }
        if (!empty($tree_inventory->ti_size)) {
            $title .= $tree_inventory->ti_size;
            $title .= " DBH";
        }

        return $title;
    }

    public function getStatus(){
        if($this->lead){
            return $this->lead;
        }
        return false;
    }

    public function setStatus($statusId){
        if($this->lead){
            $leadId = $this->getId();
            if(!empty($leadId)) {
                $lead = Lead::find($leadId);
                $lead->lead_status_id = $statusId;
                $lead->save();
                return true;
            }
        }
        return false;
    }
}
