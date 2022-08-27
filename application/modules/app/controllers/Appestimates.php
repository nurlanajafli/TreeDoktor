<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

//require APPPATH . '/libraries/JWT.php';
use application\modules\categories\models\Category;
use application\modules\classes\models\QBClass;
use application\modules\clients\models\Client;
use application\modules\leads\models\Lead;
use application\modules\invoices\models\Invoice;
use application\modules\messaging\models\SmsTpl;
use application\modules\workorders\models\WorkorderStatus;
use application\modules\workorders\models\Workorder;
use application\modules\estimates\models\EstimatesService;
use application\modules\estimates\models\EstimateStatus;
use application\modules\estimates\models\Estimate;
use application\modules\clients\models\ClientLetter;

class Appestimates extends APP_Controller
{

    function __construct() {
        parent::__construct();
        $this->load->model('mdl_estimates');
        $this->load->model('mdl_est_status');
        $this->load->model('mdl_services');
        $this->load->model('mdl_vehicles');
        $this->load->model('mdl_crews');
        $this->load->model('mdl_crews');

        $this->load->library('Common/EstimateActions');
        $this->load->library('Common/LeadsActions');
        $this->load->library('Common/WorkorderActions');
        $this->load->library('Common/InvoiceActions');
        $this->load->library('Common/ClientsActions');
        $this->load->library('Messages/Messages');
    }

    function services() {
        $order_by['service_status'] = 'DESC';
        $order_by['service_priority'] = 'ASC';
        $data['services'] = $this->mdl_services->order_by($order_by)->get_many_by(array('service_parent_id' => NULL, 'service_status' => 1, 'is_product' => 0));
        $data['products'] = $this->mdl_services->order_by($order_by)->get_many_by(array('service_parent_id' => NULL, 'service_status' => 1, 'is_product' => 1));

        array_map(function (&$service) {
            $service->service_attachments = $service->service_attachments ? json_decode($service->service_attachments) : [];
        }, $data['services']);

        $data["tools"] = $this->mdl_vehicles->get_many_by(array('vehicle_trailer' => 2, 'vehicle_disabled' => NULL));
        $data["vehicles"] = $this->mdl_vehicles->get_many_by(array('vehicle_trailer IS NULL', 'vehicle_disabled' => NULL));
        $data["trailers"] = $this->mdl_vehicles->get_many_by(array('vehicle_trailer' => 1, 'vehicle_disabled' => NULL));

        $data['crew'] = $this->mdl_crews->get_crews(array('crew_status' => 1, 'crew_id >' => 0), 'crew_status DESC, crew_priority ASC');

        array_map(function (&$row) {
            $row->vehicle_options = $row->vehicle_options ? json_decode($row->vehicle_options) : [];
        }, $data['tools']);
        array_map(function (&$row) {
            $row->vehicle_options = $row->vehicle_options ? json_decode($row->vehicle_options) : [];
        }, $data['vehicles']);
        array_map(function (&$row) {
            $row->vehicle_options = $row->vehicle_options ? json_decode($row->vehicle_options) : [];
        }, $data['trailers']);

        return $this->response([
            'status' => TRUE,
            'data' => $data
        ], 200);
    }
    function categoriesWithItems(){
        $bundles = $this->mdl_services->find_all(array('service_status' => 1, 'is_bundle' => 1), 'service_priority');
        foreach ($bundles as $bundle){
            $result = $this->mdl_services->get_records_included_in_bundle($bundle->service_id);
            if($result){
                foreach ($result as $record) {
                    $record->service_attachments = $this->getServiceAttachment($record->service_attachments);
                    $record->non_taxable = 0;
                    unset($record->service_qb_id);
                    if(!empty($record->service_class_id)){
                        $class = QBClass::where('class_id', $record->service_class_id)->first();
                        if(!empty($class))
                            $record->service_class_name = $class->class_name;
                    }
                }
            }
            $bundle->bundle_records = json_encode($result, true);
        }
        $data['bundles'] = $bundles;

        $categoryWithProducts = Category::whereNull('category_parent_id')->with(['categoriesWithProducts', 'products'])->get()->toArray();
        $categoryWithServices = Category::whereNull('category_parent_id')->with(['categoriesWithServices', 'services'])->get()->toArray();
        $data['categoriesWithProducts'] =  $this->estimateactions->getCategoriesWithItemsForApp($categoryWithProducts);
        $data['categoriesWithServices'] =  $this->estimateactions->getCategoriesWithItemsForApp($categoryWithServices);

        $data["tools"] = $this->mdl_vehicles->get_many_by(array('vehicle_trailer' => 2, 'vehicle_disabled' => NULL));
        $data["transport"]["vehicles"] = $this->mdl_vehicles->get_many_by(array('vehicle_trailer IS NULL', 'vehicle_disabled' => NULL));
        $data["transport"]["trailers"] = $this->mdl_vehicles->get_many_by(array('vehicle_trailer' => 1, 'vehicle_disabled' => NULL));

        $data['crew'] = $this->mdl_crews->get_crews_app(array('crew_status' => 1, 'crew_id >' => 0), 'crew_status DESC, crew_priority ASC');

        array_map(function (&$row) {
            $row->vehicle_options = $row->vehicle_options ? json_decode($row->vehicle_options) : [];
        }, $data['tools']);
        array_map(function (&$row) {
            $row->vehicle_options = $row->vehicle_options ? json_decode($row->vehicle_options) : [];
        }, $data["transport"]['vehicles']);
        array_map(function (&$row) {
            $row->vehicle_options = $row->vehicle_options ? json_decode($row->vehicle_options) : [];
        }, $data["transport"]['trailers']);
        $this->response([
            'status' => TRUE,
            'data' => $data
        ], 200);
    }
    function items() {
        $order_by['service_status'] = 'DESC';
        $order_by['service_priority'] = 'ASC';
        $data['services'] = $this->mdl_services->order_by($order_by)->get_many_by(array('service_parent_id' => NULL, 'service_status' => 1, 'is_product' => 0, 'is_bundle' => 0));
        $data['products'] = $this->mdl_services->order_by($order_by)->get_many_by(array('service_parent_id' => NULL, 'service_status' => 1, 'is_product' => 1));

        $bundles = $this->mdl_services->find_all(array('service_status' => 1, 'is_bundle' => 1), 'service_priority');
        foreach ($bundles as $bundle){
            $result = $this->mdl_services->get_records_included_in_bundle($bundle->service_id);
            if($result){
                foreach ($result as $record) {
                    $record->service_attachments = $this->getServiceAttachment($record->service_attachments);
                    $record->non_taxable = 0;
                    unset($record->service_qb_id);
                }
            }
            $bundle->bundle_records = json_encode($result, true);
        }
        $data['bundles'] = $bundles;

        foreach($data['services'] as $service) {
            if(isset($service->service_attachments)){
                $new_att = $this->getServiceAttachment($service->service_attachments);
                $service->service_attachments = json_encode($new_att);
            }
        }

        foreach($data['products'] as $service) {
            if(isset($service->service_attachments)){
                $new_att = $this->getServiceAttachment($service->service_attachments);
                $service->service_attachments = json_encode($new_att);
            }
            if(!empty($service->service_class_id)){
                $class = QBClass::where('class_id', $service->service_class_id)->first();
                if(!empty($class))
                    $service->service_class_name = $class->class_name;
            }
        }

        $data["tools"] = $this->mdl_vehicles->get_many_by(array('vehicle_trailer' => 2, 'vehicle_disabled' => NULL));
        $data["transport"]["vehicles"] = $this->mdl_vehicles->get_many_by(array('vehicle_trailer IS NULL', 'vehicle_disabled' => NULL));
        $data["transport"]["trailers"] = $this->mdl_vehicles->get_many_by(array('vehicle_trailer' => 1, 'vehicle_disabled' => NULL));

        $data['crew'] = $this->mdl_crews->get_crews_app(array('crew_status' => 1, 'crew_id >' => 0), 'crew_status DESC, crew_priority ASC');

        array_map(function (&$row) {
            $row->vehicle_options = $row->vehicle_options ? json_decode($row->vehicle_options) : [];
        }, $data['tools']);
        array_map(function (&$row) {
            $row->vehicle_options = $row->vehicle_options ? json_decode($row->vehicle_options) : [];
        }, $data["transport"]['vehicles']);
        array_map(function (&$row) {
            $row->vehicle_options = $row->vehicle_options ? json_decode($row->vehicle_options) : [];
        }, $data["transport"]['trailers']);

        $this->response([
            'status' => TRUE,
            'data' => $data
        ], 200);
    }

    function statuses() {
        $statuses = $this->mdl_est_status->get_many_by(['est_status_active' => 1]);

        array_unshift($statuses, [
            'est_status_id' => 0,
            'est_status_name' => 'All Estimates',
            'est_status_active' => '0',
            'est_status_declined' => '0',
            'est_status_default' => '0',
            'est_status_confirmed' => '0',
            'est_status_sent' => '0',
            'est_status_priority' => '0'
        ]);

        return $this->response([
            'status' => TRUE,
            'data' => $statuses
        ], 200);
    }

    public function get_with_filters($page = 1, $limit = 20){
        $estimates =  Estimate::with(['client', 'user', 'lead', 'estimates_service'])
            ->withoutAppends()
            ->permissions()
            ->apiFilter(request())
            ->OrderDesc()
            ->offset($page > 1 ? ($page - 1) * $limit : 0)
            ->limit($limit)
            ->pluck('estimate_id')->toArray();

        $lastPage = count($estimates) == $limit ? $page + 2 : $page;
        if($page == 1){
            $countEstimates = Estimate::withoutAppends()
                ->permissions()
                ->apiFilter(request())
                ->get()
                ->count();
            $lastPage = ceil($countEstimates / $limit);
        }

        $data = [];
        if(!empty($estimates))
            $data = Estimate::with(['estimate_status', 'estimate_reason_status', 'client', 'user' => function($query){$query->apiFields();}, 'lead', 'estimates_service'])
                ->withoutAppends()
                ->select(Estimate::BASE_FIELDS)
                ->addSelect(Estimate::NOTES_FIELDS)
                ->whereIn('estimates.estimate_id', $estimates)
                ->withTotals(null, ['estimates.estimate_id' => $estimates])
//                ->apiFilter(request())
                ->formattedCreateDate()
                ->OrderDesc()
//                ->paginate($limit, [], 'page', $page);
                ->get();
        $this->response(['data' => $data, 'last_page' => $lastPage]);
//        $this->response($data);
        return;
    }

    public function fetch($estimate_id){
        $data = ['data' => []];

        $estimate =  Estimate::where('estimates.estimate_id', $estimate_id)
            ->with(['estimate_status', 'estimate_reason_status', 'client',
                'user' => function($query){
                    $query->apiFields();
                },
                'lead', 'estimates_service.service', 'estimates_service.classes', 'estimates_service.equipments',
                'estimates_service.expenses', 'estimates_service.services_crew.crew', 'estimates_service.tree_inventory.tree', 'estimates_service.tree_inventory.tree_inventory_work_types',
                'estimates_service.tree_inventory.tree_inventory_work_types.work_type', 'estimates_service.bundle_service', 'estimates_service.classes',
                'estimates_service' => function($query) {
                    $query->withoutBundleServices();
                    },
                'estimates_service.bundle.estimate_service', 'estimates_service.bundle.estimate_service.service', 'estimates_service.bundle.estimate_service.classes', 'estimates_service.bundle.estimate_service.equipments',
                'estimates_service.bundle.estimate_service.expenses', 'estimates_service.bundle.estimate_service.services_crew.crew'])
            ->withTotals(null, ['estimates.estimate_id' => $estimate_id])
            ->select(Estimate::BASE_FIELDS)
            ->addSelect(Estimate::NOTES_FIELDS)
            ->addSelect(Estimate::TOTAL_FIELDS)
            ->permissions()
            ->first();

        if(!empty($estimate)) {
            $estimate->setAppends(['files']);
//            if(isset($estimate->discount_comment))
//                $estimate->discount_comment = trim($estimate->discount_comment);
            if(!empty($estimate->estimates_service)){
                foreach ($estimate->estimates_service as $service){
                    if(!empty($service->tree_inventory)){
                        $service->profile_estimate_service_ti_title = $service->estimate_service_ti_title;
                        $service->profile_service_description = $service->service_description;
                        if(!empty($service->tree_inventory) && !empty($service->tree_inventory->ties_priority)){
                            $service->profile_estimate_service_ti_title .= ', Priority: ' . $service->tree_inventory->ties_priority;
                            if(!empty($service->tree_inventory->tree_inventory_work_types)){
                                $workTypes = 'Work Types: ';
                                foreach ($service->tree_inventory->tree_inventory_work_types as $key => $workType){
                                    if(!empty($workType->work_type)) {
                                        $workTypes .= $workType->work_type->ip_name;
                                    }
                                    if (count($service->tree_inventory->tree_inventory_work_types) - 1 != $key)
                                        $workTypes .= ', ';
                                }
                                $service->profile_service_description = $workTypes . '<br>' . $service->service_description;
                            }
                        }
                    }
                }
            }
            $data = ['data' => $estimate];
        }

        $this->response($data);
        return;
    }

    function get($statusId = NULL, $limit = 20, $offset = 0) {
        $statusId = intval($statusId);
        $limit = intval($limit) ?? 2;
        $offset = intval($offset);

        if($statusId && $statusId !== 0) {
            $status = $this->mdl_est_status->get_by(['est_status_default' => 1]);
            if(!$status)
                return $this->response([
                    'status' => FALSE,
                    'message' => 'Incorrect Estimate Status'
                ], 400);

            $filter['estimates.status'] = $statusId;
        }

        $filter['estimates.user_id'] = $this->user->id;
        $totalRows = $this->mdl_estimates->record_count([], $filter);
        if($offset > $totalRows)
            return $this->response([
                'status' => FALSE,
                'message' => 'Incorrect Offset Value'
            ], 400);

        $estimates = $this->mdl_estimates->get_estimates(NULL, $statusId, $limit, $offset, "estimates.estimate_id", "DESC", [
            'user_id' => $this->user->id
        ]);

        return $this->response([
            'status' => TRUE,
            'total_rows' => $totalRows,
            'limit' => $limit,
            'offset' => $offset,
            'data' => $estimates->result()
        ], 200);
    }

    function show($id = NULL) {
        $estimate = $this->mdl_estimates_orm->with('mdl_services_orm')->get_full_estimate_data(['estimate_id' => (int) $id], TRUE, TRUE);
        $estimate = isset($estimate[0]) ? $estimate[0] : FALSE;
        if(!$estimate)
            return $this->response([
                'status' => FALSE,
                'message' => 'Incorrect Estimate ID'
            ], 400);

        unset($estimate->user_signature);
        return $this->response([
            'status' => TRUE,
            'data' => $estimate
        ], 200);
    }

    function presave_scheme() {
        $lead_id = $this->input->post('lead_id');
        $elements = $this->input->post('elements');
        $image = $this->input->post('image');
        $config = [];

        if((int)$lead_id > 0) {
            $this->load->model('mdl_leads');
            $lead = $this->mdl_leads->find_by_id($lead_id);
            if(!$lead) {
                return $this->response([
                    'status' => FALSE,
                    'message' => 'Incorrect Lead ID'
                ], 400);
            }
            $client_id = $lead->client_id;
            $estimate = $this->mdl_estimates_orm->get_by(['lead_id' => $lead_id]);
            $estimate_id = $estimate->estimate_id ?? NULL;

            $schemePath = 'uploads/tmp/' . $client_id;
            if($this->input->post('source'))
                $schemePath .= '/source';
            $schemePath .=  '/' . str_pad($lead_id, 5, '0', STR_PAD_LEFT) . '_scheme.png';
            $elementsPath = 'uploads/tmp/' . $client_id . '/' . str_pad($lead_id, 5, '0', STR_PAD_LEFT) . '_scheme_elements';
            $tmpPath = sys_get_temp_dir() . '/' . str_pad($lead_id, 5, '0', STR_PAD_LEFT) . '_scheme.png';
        } else {
            $unique = uniqid();
            $schemePath = 'uploads/tmp/leads/' . $unique . '_scheme.png';
            $elementsPath = 'uploads/tmp/leads/' . $unique . '_scheme_elements';
            $tmpPath = sys_get_temp_dir() . '/' . $unique . '_scheme.png';
        }

        $result = [];

        if($image)
        {
            $estimate_scheme_data = str_replace('[removed]', '', $image);
            if($estimate_scheme_data == $image)
                $estimate_scheme_data = explode(',', $image)[1];

            if(!empty($estimate_id) && $estimate_id && !$this->input->post('source')) {
                $estimate_data = $this->mdl_estimates->find_by_id($estimate_id);
                $estimate_pdf_files = $estimate_data->estimate_pdf_files ? json_decode($estimate_data->estimate_pdf_files, TRUE) : [];
                $estimate_no = str_pad($lead_id, 5, '0', STR_PAD_LEFT) . "-E";

                $schemePath = 'uploads/clients_files/' . $client_id . '/estimates/' . $estimate_no;
                $schemePath .= '/pdf_estimate_no_' . $estimate_no . '_scheme.png';

                $estimate_pdf_files[] = ltrim($schemePath, './');
                $estimate_pdf_files = array_unique($estimate_pdf_files);

                $this->mdl_estimates_orm->update($estimate_id, [
                    'estimate_pdf_files' => json_encode($estimate_pdf_files),
                    'estimate_scheme' => isset($elements) && $elements ? $elements : json_encode([]),
                ]);
            }

            if($elements) {
                if(isset($client_id)) {
                    $schemeHtml = 'uploads/tmp/' . $client_id . '/' . str_pad($lead_id, 5, '0', STR_PAD_LEFT) . '_source_html';
                    if (is_bucket_file($schemeHtml))
                        bucket_unlink($schemeHtml);
                }
                bucket_write_file($elementsPath, json_encode($elements));
                $result['elements'] = $elementsPath;
            }

            file_put_contents($tmpPath, base64_decode($estimate_scheme_data));

            $config['image_library'] = 'gd2';
            $config['source_image']	= $tmpPath;
            $config['quality'] = 50;
            $config['maintain_ratio'] = TRUE;
            $config['width'] = 1200;
            $config['height'] = 1200;
            $this->load->library('image_lib', $config);
            $this->image_lib->resize();

            bucket_move($tmpPath, $schemePath);//, base64_decode($estimate_scheme_data), ['ContentType' => 'image/png']);
            @unlink($tmpPath);
            $result['path'] = base_url(ltrim($schemePath, './'));
        }

        return $this->response([
            'status' => TRUE,
            'data' => $result
        ], 200);
    }

    function scheme() {
        $lead_id = $this->input->post('lead_id');
        $srcPath = $this->input->post('path');
        $elementsPath = $this->input->post('elements');
        $sourceData = $sourceDataPath = $sourceImagePath = NULL;

        if((int)$lead_id > 0) {
            $this->load->model('mdl_leads');
            $lead = $this->mdl_leads->find_by_id($lead_id);
            $estimate = $this->mdl_estimates->find_by_fields(['lead_id' => $lead_id]);
            if(!$lead) {
                return $this->response([
                    'status' => FALSE,
                    'message' => 'Incorrect Lead ID'
                ], 400);
            }

            $client_id = $lead->client_id;
            $sourceImagePath = 'uploads/tmp/' . $client_id . '/source' . '/' . str_pad($lead_id, 5, '0', STR_PAD_LEFT) . '_scheme.png';
            if($estimate) {
                $sourceData = $estimate->estimate_scheme ? json_decode($estimate->estimate_scheme) : [];
            } else {
                $sourceDataPath = 'uploads/tmp/' . $client_id . '/' . str_pad($lead_id, 5, '0', STR_PAD_LEFT) . '_scheme_elements';
            }
        } else {
            $sourceImagePath = $srcPath;
            $sourceDataPath = $elementsPath;
        }

        if(!$sourceData && $sourceDataPath && is_bucket_file($sourceDataPath)) {
            $sourceData = json_decode(bucket_read_file($sourceDataPath));
        }

        return $this->response([
            'status' => TRUE,
            'data' => [
                'original' => $sourceImagePath ? base_url($sourceImagePath) : NULL,
                'elements' => $sourceData
            ]
        ]);
    }

    function save() {
        $leadId = $this->input->post('lead_id');
        $estimateId = $this->input->post('estimate_id');
        $prices = $this->input->post('service_price');
        $createWo = !empty($this->input->post('create_wo')) ? true : false;
        $createInvoice = !empty($this->input->post('create_invoice')) ? true : false;

        if(!$leadId) {
            if(!$this->input->post('tmp_lead_id')) {
                return $this->response([
                    'status' => FALSE,
                    'message' => 'lead_id required'
                ], 400);
            }
        }

        if(!$prices || empty($prices)) {
            return $this->response([
                'status' => FALSE,
                'message' => 'The estimate must have at least one line'
            ], 400);
        }

        if($estimateId) {
            $estimate = $this->mdl_estimates_orm->get($estimateId);
            if(!$estimate)
                return $this->response([
                    'status' => FALSE,
                    'message' => 'Incorrect Estimate ID'
                ], 400);
        } else {
            if($leadId) {
                $estimate = $this->mdl_estimates_orm->get_by(['lead_id' => $leadId]);
                if($estimate)
                    return $this->response([
                        'status' => FALSE,
                        'estimate_id' => $estimate->estimate_id,
                        'message' => 'Estimate Already Exists'
                    ], 400);
            }
            //
        }

        if(!empty($this->input->post('service_type_ids')) && empty($this->input->post('service_type_id')))
            $_POST["service_type_id"] = $this->input->post('service_type_ids');

        $estimate_id = $this->mdl_estimates_orm->save_estimate();
        $estimate = $this->mdl_estimates_orm->get_by(['estimate_id' => $estimate_id]);//$this->mdl_estimates_orm->get($estimate_id); - gives nulls in estimate_no, estimate_id

        $response = [
            'estimate_id' => $estimate_id,
            'estimate_no' => $estimate->estimate_no,
            'estimate_status_id' => $estimate->status
        ];

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

        //create a new job for synchronization in QB
        $invoice = Invoice::where('estimate_id', $estimate_id)->first();
        if (!empty($invoice)) {
            $this->invoiceactions->changeInvoiceStatusWhenUpdatingEstimate($invoice);
            pushJob('quickbooks/invoice/syncinvoiceinqb', serialize(['id' => $invoice->id, 'qbId' => $invoice->invoice_qb_id]));
        }


        return $this->response([
            'status' => TRUE,
            'data' => $response
        ], 200);
    }

    // old upload
    /*
    function upload() {
        $this->load->model('mdl_leads');
        $leadId = $this->input->post('id') ?: $this->input->post('lead_id');
        if((int)$leadId > 0) {
            $lead = $this->mdl_leads->find_by_id($leadId);
            if(!$lead)
                return $this->response([
                    'status' => FALSE,
                    'message' => 'Incorrect Lead'
                ], 400);
            $path = 'uploads/clients_files/' . $lead->client_id . '/leads/tmp/' . str_replace('-L', '-E', $lead->lead_no) . '/';
        } else {
            $path = 'uploads/tmp/leads/';
        }

        $photos = [];
        if (isset($_FILES['files']) && is_array($_FILES['files'])) {
            $this->load->library('upload');
            foreach ($_FILES['files']['name'] as $key => $val) {

                $_FILES['file']['name'] = $_FILES['files']['name'][$key];
                $_FILES['file']['type'] = $_FILES['files']['type'][$key];
                $_FILES['file']['tmp_name'] = $_FILES['files']['tmp_name'][$key];
                $_FILES['file']['error'] = $_FILES['files']['error'][$key];
                $_FILES['file']['size'] = $_FILES['files']['size'][$key];

                $config['upload_path'] = $path;
                $config['allowed_types'] = 'gif|jpg|jpeg|png|pdf|GIF|JPG|JPEG|PNG|PDF';
                $config['remove_spaces'] = TRUE;
                $config['encrypt_name'] = TRUE;
                $this->upload->initialize($config);
                if ($this->upload->do_upload('file')) {
                    $uploadData = $this->upload->data();
                    $photos[] = [
                        'filepath' => $path . $uploadData['file_name'],
                        'filename' => $uploadData['file_name']
                    ];
                } else {
                    $photos[] = [
                        'error' => strip_tags($this->upload->display_errors())
                    ];
                }
            }
        }
        return $this->response([
            'status' => TRUE,
            'data' => $photos
        ], 200);
    }
    */

    function upload() {
        $this->load->model('mdl_leads');
        $leadId = $this->input->post('id') ?: $this->input->post('lead_id');
        $serviceId = $this->input->post('service') && $this->input->post('service') != 'false' ? $this->input->post('service') : false;
        $estimate = null;
        $lead = null;
        $max = 1;
        $updateEstimatePdfFiles = FALSE;
        $path = 'uploads/tmp/leads/';
        $estimate_pdf_files = [];
        if((int)$leadId > 0) {
            $lead = $this->mdl_leads->find_by_id($leadId);
            if(!$lead)
                return $this->response([
                    'status' => FALSE,
                    'message' => 'Incorrect Lead'
                ], 400);
            $path = 'uploads/clients_files/' . $lead->client_id . '/leads/tmp/' . str_replace('-L', '-E', $lead->lead_no) . '/';
            $estimate = $this->mdl_estimates_orm->get_by(['lead_id' => $leadId]);

            $files = [];

            if($estimate && $serviceId) {
                $estimate_pdf_files = $estimate->estimate_pdf_files ? json_decode($estimate->estimate_pdf_files, TRUE) : [];
                $path = 'uploads/clients_files/' . $lead->client_id . '/estimates/' . str_replace('-L', '-E', $lead->lead_no) . '/' . $serviceId . '/';
                $files = bucketScanDir($path);
            } elseif($estimate && !$serviceId) {
                $estimate_pdf_files = $estimate->estimate_pdf_files ? json_decode($estimate->estimate_pdf_files, TRUE) : [];
                $path = 'uploads/clients_files/' . $lead->client_id . '/estimates/' . str_replace('-L', '-E', $lead->lead_no) . '/';
                $files = bucketScanDir($path);
            }

            if (!empty($files) && $files) {
                foreach($files as $file)
                {
                    preg_match('/estimate_no_' . str_pad($lead->lead_id, 5, '0', STR_PAD_LEFT) . '-E.*?_([0-9]{1,})/is', $file, $num);
                    if(isset($num[1]) && ($num[1] + 1) > $max)
                        $max = $num[1] + 1;
                    preg_match('/pdf_estimate_no_' . str_pad($lead->lead_id, 5, '0', STR_PAD_LEFT) . '-E.*?_([0-9]{1,})/is', $file, $num1);
                    if(isset($num1[1]) && ($num1[1] + 1) > $max)
                        $max = $num[1] + 1;
                }
            }
        }

        $photos = [];
        if (isset($_FILES['files']) && is_array($_FILES['files'])) {
            $this->load->library('upload');
            foreach ($_FILES['files']['name'] as $key => $val) {

                $_FILES['file']['name'] = $_FILES['files']['name'][$key];
                $_FILES['file']['type'] = $_FILES['files']['type'][$key];
                $_FILES['file']['tmp_name'] = $_FILES['files']['tmp_name'][$key];
                $_FILES['file']['error'] = $_FILES['files']['error'][$key];
                $_FILES['file']['size'] = $_FILES['files']['size'][$key];

                if($estimate || $serviceId) { // if upload file for exists service
                    $ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
                    $suffix = $ext == 'pdf' ? 'pdf_' : NULL;
                    $config['file_name'] = $suffix . 'estimate_no_' . str_replace('-L', '-E', $lead->lead_no) . '_' . $max++ . '.' . $ext;
                } else {
                    $config['remove_spaces'] = TRUE;
                    $config['encrypt_name'] = TRUE;
                }

                $config['upload_path'] = $path;
                $config['allowed_types'] = 'gif|jpg|jpeg|png|pdf|ogg|mp3|mp4|webm|aac|m4a|wav|mov|GIF|JPG|JPEG|PNG|PDF|OGG|MP3|MP4|WEBM|AAC|M4A|WAV|MOV';
                $this->upload->initialize($config);
                if ($this->upload->do_upload('file')) {
                    $uploadData = $this->upload->data();
                    $photos[] = [
                        'filepath' => $path . $uploadData['file_name'],
                        'filename' => $uploadData['file_name'],
                        'size' => $_FILES['file']['size'],
                        'type' => $_FILES['file']['type'],
                        'url' => base_url($path . $uploadData['file_name'])
                    ];
                    if($estimate && $serviceId) { // if upload file for exists service
                        $estimate_pdf_files[] = $path . $uploadData['file_name'];
                        $updateEstimatePdfFiles = TRUE;
                    }
                } else {
                    $photos[] = [
                        'error' => strip_tags($this->upload->display_errors())
                    ];
                }
            }
        }
        if($updateEstimatePdfFiles)
            $this->mdl_estimates_orm->update($estimate->estimate_id, ['estimate_pdf_files' => json_encode($estimate_pdf_files)]);
        return $this->response([
            'status' => TRUE,
            'data' => $photos
        ], 200);
    }

    function send()
    {
        $request = request();
        $wrong_emails = [];
        $from_email = $cc = $bcc = null;
        if($request->input('email') && !filter_var($request->input('email'), FILTER_VALIDATE_EMAIL)) {
            return $this->response(['status' => FALSE, 'message' => 'Invalid Email Address'], 400);
        }elseif (is_array($request->input('emails')) && !empty($request->input('emails'))){
            foreach ($request->input('emails') as $email){
                if(!filter_var($email, FILTER_VALIDATE_EMAIL))
                    $wrong_emails[] = $email;
            }
            if(count($wrong_emails) === count($request->input('emails')))
                return $this->response(['status' => FALSE, 'message' => 'Invalid Email Addresses'], 400);
        }

        if (!$request->input('id'))
            return $this->response(['status' => FALSE, 'message' => 'Estimate ID is not valid'], 400);

        $estimate_data = Estimate::with(['client.primary_contact', 'user', 'lead', 'workorder', 'invoice'])->find($request->input('id'));

        if (!$estimate_data)
            return $this->response(['status' => FALSE,
                'message' => 'Estimate ID is not valid'
            ], 400);

        $brand_id = get_brand_id($estimate_data->toArray(), $estimate_data->client->toArray());

        if (!$letter = ClientLetter::where(['system_label' => 'new_estimate_for'])->first())
            return $this->response(['status' => FALSE,
                'message' => 'Email template not found'
            ], 400);

        $letter = ClientLetter::compileLetter($letter, $brand_id, [
            'client'    =>  $estimate_data->client,
            'estimate'  =>  $estimate_data,
        ]);

        $note['to'] = $to = $request->input('email') ? $request->input('email') : ($request->input('emails') ? $request->input('emails') : $estimate_data->client->primary_contact->cc_email);
        if(is_array($to)){
            $to = array_diff($to, $wrong_emails);
            $note['to'] = implode(',', $to);
        }
        $note['subject'] = $subject = $letter->email_template_title;
        $text = $request->input('custom_email') ?: $letter->email_template_text;

        if($estimate_data->user->user_signature)
            $text .= $estimate_data->user->user_signature;

        $unsubscribe_text = '<br><div style="text-align:center; font-size: 10px;"> If you no longer wish to receive these emails you may ' .
            '<a href="' . $this->config->item('unsubscribe_link') . md5($estimate_data->client_id) . '">unsubscribe</a> at any time.</div>';

        if(!$from_email && $letter->email_static_sender && $letter->email_static_sender !== '') {
            $from_email = $letter->email_static_sender;
        }
        if($this->input->post('cc') === false && $letter->email_static_cc && $letter->email_static_cc !== '') {
            $cc = $letter->email_static_cc;
        }
        if($this->input->post('bcc') === false && $letter->email_static_bcc && $letter->email_static_bcc !== '') {
            $bcc = $letter->email_static_bcc;
        }

//        $toDomain = substr(strrchr($to, "@"), 1);
//        if(array_search($toDomain, $this->config->item('smtp_domains')) !== FALSE) {
//            $config = $this->config->item('smtp_mail');
//            $note['from'] = $email = $config['smtp_user'];
//        }

        //$name = ($estimate_data->user->full_name) ? ' - ' . $estimate_data->user->full_name : '';

        if($request->input('send_method') == 'email' || $request->input('send_method') == 'all'){
            if(is_array($to)){
                foreach ($to as $emailTo) {
                    pushJob('estimates/sendestimate', [
                        'estimate_id' => $estimate_data->estimate_id,
                        'from' => $from_email,
                        'to' => $emailTo,
                        'cc' => $cc,
                        'bcc' => $bcc,
                        'body' => $text,
                        'subject' => $subject,
                        'unsubscribe_text' => $unsubscribe_text,
                        'user_id' => request()->user()->id
                    ]);
                }
                $to = implode(', ', $to);
            } else {
                pushJob('estimates/sendestimate', [
                    'estimate_id' => $estimate_data->estimate_id,
                    'from' => $from_email,
                    'to' => $to,
                    'cc' => $cc,
                    'bcc' => $bcc,
                    'body' => $text,
                    'subject' => $subject,
                    'unsubscribe_text' => $unsubscribe_text,
                    'user_id' => request()->user()->id
                ]);
            }
        }

        $statuses = EstimateStatus::active()->get();
        $defaultStatus = $statuses->firstWhere(EstimateStatus::DEFAULT_FLAG, 1);
        $sentStatus = $statuses->firstWhere(EstimateStatus::SENT_FLAG, 1);

        if ($estimate_data->status == $defaultStatus->est_status_id) {
            $estimate_data->status = $sentStatus->est_status_id;
            $estimate_data->save();
            if($estimate_data->workorder)
                $estimate_data->workorder->delete();
            if($estimate_data->invoice)
                $estimate_data->invoice->delete();
        }

        if(
            (config_item('messenger') && $request->input('sent_sms')) ||
            (config_item('messenger') && $request->input('sent_sms') && ($request->input('send_method') == 'sms' || $request->input('send_method') == 'all'))
        ){
            $this->load->library('EstimateActions');
            $this->estimateactions->setEstimateId($request->input('id'));
            $msg = $this->estimateactions->compileSmsTemplate([
                '[EMAIL]' => $to
            ]);
            if($msg) {
                $this->messages->send($request->input('sent_sms'), $msg);
            }
        }

        return $this->response([
            'status' => TRUE,
            'data' => []
        ], 200);
    }

    function confirm() {
        $leadId = $this->input->post('lead_id');
        $estimate = $this->mdl_estimates_orm->get_by([
            'lead_id' => $leadId,
            /*'user_id' => $this->user->id*/ //disabled only own check by RH (vitree complain) 2021/03/14
        ]);

        if(!$estimate) {
            return $this->response([
                'status' => FALSE,
                'message' => 'Incorrect Estimate'
            ], 400);
        }

        $this->estimateactions->setEstimateId($estimate->estimate_id);
        if($this->estimateactions->sign($this->input->post('data'))) {
            $this->estimateactions->confirm('Signature');

            if($this->input->post('is_email') && $this->input->post('emails')) {
                $email = $this->input->post('emails');
                if(is_array($email)) {
                    foreach ($email as $value)
                        $this->estimateactions->sendConfirmed($value);
                } else {
                    $this->estimateactions->sendConfirmed($email);
                }
            }

            return $this->response([
                'status' => TRUE,
                'data' => []
            ], 200);
        }

        return $this->response([
            'status' => FALSE,
            'message' => 'Confirmation Error'
        ], 400);
    }

    function hide_photo() {
        $id = intval($this->input->post('estimate_id'));
        $path = $this->input->post('path');
        $show = $this->input->post('show');

        $estimate = $this->mdl_estimates_orm->get($id);
        if(!$estimate) {
            return $this->response(array(
                'status' => FALSE,
                'message' => 'Incorrect Estimate'
            ), 400);
        }

        if(!$path) {
            return $this->response(array(
                'status' => FALSE,
                'message' => 'Incorrect File Path'
            ), 400);
        }

        $this->estimateactions->setEstimateId($id);

        if($show)
            $this->estimateactions->addFileToPdf($path);
        else
            $this->estimateactions->hideFileFromPdf($path);

        return $this->response([
            'status' => TRUE
        ]);
    }

    function delete_file() {
        $estimateId = $this->input->post('id');
        $path = $this->input->post('file');
        $estimate = $this->mdl_estimates_orm->get($estimateId);

        if(!$path)
            return $this->response(array(
                'status' => FALSE,
                'message' => 'Incorrect File'
            ), 400);

        if(!$estimate && !is_object($estimateId))
            return $this->response(array(
                'status' => FALSE,
                'message' => 'Incorrect Estimate'
            ), 400);

        $pdf_files = $estimate->estimate_pdf_files ? json_decode($estimate->estimate_pdf_files, TRUE) : [];

        if(is_bucket_file($path)) {
            bucket_unlink($path);
            $key = array_search($path, $pdf_files);
            unset($pdf_files[$key]);
            $wo_pdf_files = array_values($pdf_files);
            $this->mdl_estimates_orm->update($estimateId, ['estimate_pdf_files' => json_encode($pdf_files)]);
            return $this->response(array(
                'status' => TRUE
            ), 200);
        }
        return $this->response(array(
            'status' => FALSE,
            'message' => 'File Not Found'
        ), 400);
    }

    public function add_payment()
    {
        $this->load->model('mdl_invoices');
        $this->load->model('mdl_clients');
        $this->load->model('mdl_estimates');
        $this->load->library('Payment/ArboStarProcessing', null, 'arboStarProcessing');
        /******************VALIDATION******************/

        if (!$method = $this->input->post('method')) {
            return $this->response([
                'status' => false,
                'errors' => ['payment_method' => 'Incorrect payment method']
            ]);
        }

        if (!$this->input->post('amount')) {
            return $this->response([
                'status' => false,
                'errors' => ['payment_amount' => 'Amount Is Required']
            ]);
        }

        $amount = getAmount($this->input->post('amount'));
        if (!$amount) {
            return $this->response([
                'status' => false,
                'errors' => ['payment_amount' => 'Incorrect Payment Amount']
            ]);
        }

        if ($method == config_item('default_cc')) {
            if (_CC_MAX_PAYMENT != 0 && $amount > _CC_MAX_PAYMENT) {
                return $this->response([
                    'status' => false,
                    'errors' => ['payment_amount' => 'Maximum Payment Amount ' . money(_CC_MAX_PAYMENT)]
                ]);
            }

            if (!$this->input->post('token') && !$this->input->post('card_id')) {
                return $this->response([
                    'status' => false,
                    'message' => 'Card processing error',
                    'errors' => ['cc_select' => 'Payment card is not selected']
                ]);
            }


            if ($this->input->post('token') && !$this->input->post('crd_name')) {
                return $this->response([
                    'status' => false,
                    'message' => 'Card processing error',
                    'errors' => ['crd_name' => 'Card Holder Name Is Required']
                ]);
            }
        }

        if (!$this->input->post('estimate_id') && !$this->input->post('invoice_id')) {
            return $this->response([
                'status' => false,
                'message' => 'Incorrect Request'
            ]);
        }
        /** Обратная совместимость для старых версий приложения <= 1.12.1 */
        if (isset($_FILES) && isset($_FILES['file'])) {
            $_FILES['payment_file'] = $_FILES['file'];
        }

        if (isset($_FILES) && isset($_FILES['payment_file']) && isset($_FILES['payment_file']['error']) && $_FILES['payment_file']['error'] === 0) {
            if ($_FILES['payment_file']['tmp_name'] && !is_image($_FILES['payment_file']['tmp_name']) && !is_pdf($_FILES['payment_file']['tmp_name'])) {
                return $this->response([
                    'status' => false,
                    'message' => 'File must be image or PDF'
                ]);
            }
        }

        /******************VALIDATION******************/

        $estimate_id = $this->input->post('estimate_id');
        $invoice_data = $estimate_id ? $this->mdl_invoices->find_by_field(['invoices.estimate_id' => $estimate_id]) : false;


        if (!$estimate_id && !$invoice_data) {
            $invoice_id = $this->input->post('invoice_id');
            if (!$invoice_id) {
                return $this->response([
                    'status' => false,
                    'message' => 'Incorrect Request'
                ]);
            }
            $invoice_data = $this->mdl_invoices->find_by_id($invoice_id);
            if (!$invoice_data) {
                return $this->response([
                    'status' => false,
                    'message' => 'Incorrect Invoice'
                ]);
            }
            $estimate_id = $invoice_data->estimate_id;
        } elseif ($invoice_data) {
            $estimate_id = $invoice_data->estimate_id;
        }

        $estimate_data = $this->mdl_estimates->find_by_id($estimate_id);

        if (!$estimate_data) {
            return $this->response([
                'status' => false,
                'message' => 'Incorrect Estimate'
            ]);
        }

        $client_data = Client::find($estimate_data->client_id);
        $client_contact = $client_data->primary_contact()->first();

        $fee_percent = 0;
        $fee = 0;

        $paymentData = [];

        if ($method == config_item('default_cc')) {
            if ($this->input->post('card_id')) {
                $paymentData = array(
                    'payment_profile' => $client_data->client_payment_profile_id,
                    'card_id' => $this->input->post('card_id'),
                );
            } elseif ($this->input->post('token') && $this->input->post('crd_name')) {
                $paymentData = array(
                    'token' => $this->input->post('token'),
                    'name' => $this->input->post('crd_name'),
                    'additional' => $this->input->post('additional')
                );
            } else {
                return $this->response([
                    'status' => false,
                    'message' => 'Incorrect Credit Card data'
                ]);
            }

            $fee_percent = round((float) config_item('cc_extra_fee'), 2);
            if ($fee_percent > 0) {
                $fee = round($amount * ($fee_percent / 100), 2);
                $amount += $fee;
            }
        }

        $file = false;
        if (isset($_FILES) && isset($_FILES['payment_file']) && isset($_FILES['payment_file']['error']) && $_FILES['payment_file']['error'] === 0) {
            $file = $this->arboStarProcessing->uploadFile([
                'client_id' => $client_data->client_id,
                'estimate_id' => $estimate_data->estimate_id,
                'estimate_no' => $estimate_data->estimate_no,
                'invoice_no' => !empty($invoice_data) ? $invoice_data->invoice_no : null,
                'lead_id' => $estimate_data->lead_id
            ]);
        }

        $iData = [
            'client' => $client_data,
            'contact' => $client_contact,
            'estimate' => $estimate_data,
            'invoice' => $invoice_data,
            'type' => $this->input->post('type') ?: 'deposit',
            'payment_driver' => $client_data->client_payment_driver,
            'amount' => $amount,
            'fee' => $fee,
            'fee_percent' => $fee_percent,
            'file' => $file,
            'user_id' => $this->user->id,
        ];

        if ($this->input->post('extra')) {
            $iData['extra'] = $this->input->post('extra');
        }

        try {
            $result = $this->arboStarProcessing->pay($method, $iData, $paymentData);
        } catch (PaymentException $e) {
            return $this->response([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }

        $estimate_data = $this->mdl_estimates->find_by_id($estimate_id);
        return $this->response([
            'status' => true,
            'amnt' => $result['payment_amount'],
            'file' => $result['payment_file'],
            'total' => $result['total'],
            'status_name' => $estimate_data->status,
            'status_id' => $estimate_data->status_id
        ]);
    }

//    function add_payment() {
//        $CI =& get_instance();
//        $this->_fakeWebLogin();
//
//        /** Обратная совместимость для старых версий приложения <= 1.12.1 */
//        if(isset($_FILES) && isset($_FILES['file'])){
//            $_FILES['payment_file'] = $_FILES['file'];
//        }
//
//        $result = Modules::run('payments/payments/ajax_payment');
//        if(!$result){
//            $result = $CI->output->get_parsed_output();
//        }
//        if($result && is_array($result)){
//            if(isset($result['status']) && $result['status'] == 'error') {
//                $result['status'] = false;
//                if(isset($result['error'])){
//                    $result['message'] = $result['error'];
//                    unset($result['error']);
//                }
//                return $this->response($result, 400);
//            }
//        }
//        if ($result) {
//            $result['status'] = true;
//            return $this->response($result, 200);
//        }
//
//        return $this->response(array(
//            'status' => FALSE,
//            'message' => 'Unknown error!'
//        ), 400);
//    }

    function payments($estimate_id = false){
        if(!$estimate_id){
            return $this->response(array(
                'status' => FALSE,
                'message' => 'Incorrect Estimate ID'
            ), 400);
        }
        $this->load->model('mdl_clients');
        $payments_data = $this->mdl_clients->get_payments(array('client_payments.estimate_id' => $estimate_id));
        return $this->response([
            'status' => TRUE,
            'data' => $payments_data
        ], 200);
    }
    
    function update_status() {
        $statusId = $this->input->post('status_id');
        $estimateId = $this->input->post('estimate_id');
        $reasonId = $this->input->post('reason_id') ? $this->input->post('reason_id') : NULL;
		$estimate = false;
		
		
		if($statusId && $statusId !== 0) {
			
            $status = $this->mdl_est_status->get($statusId);
            if(!$status) {
                return $this->response([
                    'status' => FALSE,
                    'message' => 'Incorrect Estimate Status'
                ], 400);
			}
        }
		
        if($estimateId) {
            $estimate = $this->mdl_estimates_orm->get($estimateId);
            if(!$estimate) {
            
                return $this->response([
                    'status' => FALSE,
                    'message' => 'Incorrect Estimate ID'
                ], 400);
            }
        } else {
			return $this->response([
				'status' => FALSE,
				'message' => 'Estimate Id Is Invalid'
			], 400);
		}
		$this->estimateactions->setEstimateId($estimateId);
        $updateStatus = $this->estimateactions->changeStatus($statusId, $reasonId);
		if($updateStatus) {
		    $response = [
                'status' => TRUE,
                'message' => 'Status Was Updated'
            ];

            if($statusId == $this->estimateactions->getConfirmedStatusId()){
                $workorder = Workorder::where('estimate_id', $estimateId)->get()->first();
                if(!empty($workorder))
                    $response['wo_id'] = $workorder->id;
            }
			return $this->response($response, 200);
		} else {
			return $this->response([
					'status' => FALSE,
					'message' => 'Status Was Not Updated'
				], 400);
		}
    }

    function deleteTestEstimate() {
        $this->load->model('mdl_leads');
        $this->load->model('mdl_workorders');
        $this->mdl_estimates_orm->delete_by(['estimate_no' => '46845-E']);
        $this->mdl_estimates_orm->delete_by(['estimate_no' => '00050-E']);
        echo 'Deleted ' . $this->db->affected_rows() . ' rows' . "\r\n";
        $this->mdl_leads->update_leads(['lead_status' => 'New'], ['lead_id' => 46845]);
        $this->mdl_leads->update_leads(['lead_status' => 'New'], ['lead_id' => 50]);
        $this->db->delete('workorders', ['workorder_no' => '46845-W']);
        $this->db->delete('workorders', ['workorder_no' => '00050-W']);
        echo 'OK';
    }

    function deleteAndrewTestEstimate() {
        $this->load->model('mdl_leads');
        $this->mdl_estimates_orm->delete_by(['estimate_no' => '47056-E']);
        echo 'Deleted ' . $this->db->affected_rows() . ' rows' . "\r\n";
        $this->mdl_leads->update_leads(['lead_status' => 'New'], ['lead_id' => 47056]);
        //$this->mdl_workorders->update_leads(['lead_status' => 'New'], ['lead_id' => 47056]);
        $this->db->delete('workorders', ['workorder_no' => '46845-W']);
        echo 'OK';
    }

    public function deleteEstimateDraftItem(){
        if(!$this->checkEstimateDraftRequiredFields($this->input->post()))
            return false;
        $this->estimateactions->deleteEstimateDraftItem($this->input->post());
    }

    public function getDraftEstimate(){
        if(!$this->checkEstimateDraftRequiredFields($this->input->post()))
            return false;

        $clientId = $this->input->post('client_id');
        $leadId = $this->input->post('lead_id');
        $lead = Lead::where('lead_id', $leadId)->with('status')->first();
        if(!empty($lead) && (!empty($lead->lead_estimate_draft || (!empty($lead->status) && $lead->status->lead_status_default))))
            $draftData = $this->estimateactions->getEstimateDraftDataForApp($clientId, $leadId);
        else
            Lead::where('lead_id', $leadId)->update(['lead_estimate_draft' => 1]);

        $this->response([
            'status' => TRUE,
            'data' => json_decode($draftData ?? null)
        ], 200);
        return true;
    }

    public function setEstimateDraftService(){
        if(!$this->checkEstimateDraftRequiredFields($this->input->post()))
            return false;
        $this->updateLeadEstimateDraftField($this->input->post('lead_id'));
        $this->estimateactions->setAppEstimateDraftService($this->input->post());
        $this->response([
            'status' => TRUE
        ], 200);
        return true;
    }

    public function setEstimateDraftField(){
        if(!$this->checkEstimateDraftRequiredFields($this->input->post()))
            return false;
        $this->updateLeadEstimateDraftField($this->input->post('lead_id'));
        $this->estimateactions->setEstimateDraftField($this->input->post());
        $this->response([
            'status' => TRUE
        ], 200);
        return true;
    }

    public function setEstimateDraftFiles(){
        if(!$this->checkEstimateDraftRequiredFields($this->input->post()))
            return false;
        $this->updateLeadEstimateDraftField($this->input->post('lead_id'));
        $this->estimateactions->setEstimateDraftFiles($this->input->post());
        $this->response([
            'status' => TRUE
        ], 200);
        return true;
    }

    public function deleteDraftFile() {
        $path = $this->input->post('file');
        if(!$path)
            return $this->response(array(
                'status' => FALSE,
                'message' => 'Incorrect File'
            ), 400);
        if(is_bucket_file($path)) {
            bucket_unlink($path);
            return $this->response(array(
                'status' => TRUE
            ), 200);
        }
        return $this->response(array(
            'status' => FALSE,
            'message' => 'File Not Found'
        ), 400);
    }

    public function get_preview_draft_estimate(){
        $clientId = $this->input->get('client_id');
        $leadId = $this->input->get('lead_id');
        $brandId = $this->input->get('brand_id');

        if(empty($clientId) || empty($leadId) || empty($brandId)){
            $this->response([
                'status' => FALSE,
                'message' => 'Incorrect params'
            ], 400);
            return false;
        }

        $data = $this->estimateactions->getPreviewDraftEstimate($clientId, $leadId, $brandId);

        $this->load->library('mpdf');
        $this->mpdf->WriteHTML($data['html']);

        foreach ($data['files'] as $file) {
            if(is_array($file) && !empty($file)){
                foreach ($file as $key => $val){
                    if(pathinfo($val, PATHINFO_EXTENSION) == 'pdf') {
                        $this->mpdf->AddPage('L');
                        $this->mpdf->Thumbnail(bucket_get_stream($file), 1, 10, 16, 1);
                    }
                }
            }
            elseif(!is_array($file) && pathinfo($file, PATHINFO_EXTENSION) == 'pdf') {
                $this->mpdf->AddPage('L');
                $this->mpdf->Thumbnail(bucket_get_stream($file), 1, 10, 16, 1);
            }
        }

        $this->mpdf->Output($data['file'], 'I');
    }



    private function updateLeadEstimateDraftField($leadId){
        $lead = Lead::where('lead_id', $leadId)->first();
        if(!empty($lead) && empty($lead->toArray()['lead_estimate_draft']))
            Lead::where('lead_id', $leadId)->update(['lead_estimate_draft' => 1]);
    }

    private function checkEstimateDraftRequiredFields($post){
        $clientId = $post['client_id'];
        $leadId = $post['lead_id'];
        if(empty($clientId)) {
            $this->response([
                'status' => FALSE,
                'message' => 'Incorrect Client ID'
            ], 400);
            return false;
        }
        if(empty($leadId)) {
            $this->response([
                'status' => FALSE,
                'message' => 'Incorrect Lead ID'
            ], 400);
            return false;
        }
        return true;
    }
    private function getServiceAttachment($attachments){
        $new_att = [];
        if(isset($attachments)) {
            $i = 0;
            foreach (json_decode($attachments) as $attachment) {

                $transport = new stdClass();
                $tool = [];

                if (isset($attachment->vehicle_id)) {
                    $transport->vehicles['tool_id'] = $attachment->vehicle_id;
                }
                if (isset($attachment->vehicle_option)) {
                    $transport->vehicles['option'] = $attachment->vehicle_option;
                }

                if (isset($attachment->trailer_id)) {
                    $transport->trailers['tool_id'] = $attachment->trailer_id;
                }
                if (isset($attachment->trailer_option)) {
                    $transport->trailers['option'] = $attachment->trailer_option;
                }
                if (isset($attachment->tool_id) && isset($attachment->tools_option)) {
                    foreach ($attachment->tool_id as $key => $tool_id) {
                        $tool[$tool_id] = $attachment->tools_option[$key];
                    }
                }
                $new_att[$i] = ['transport' => $transport, 'tool' => $tool];
                $i++;
            }
        }
        return $new_att;
    }
}
