<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

use application\modules\workorders\models\Workorder;
use application\modules\workorders\models\WorkorderStatus;
use application\modules\schedule\models\ScheduleEvent;
use application\modules\estimates\models\Estimate;
use application\modules\estimates\models\EstimatesServicesStatus;
use application\modules\user\models\User;
use application\modules\crew\models\Crew;
use application\modules\estimates\models\Service;
use application\modules\invoices\models\Invoice;
use application\modules\invoices\models\InvoiceStatus;

class Appworkorders extends APP_Controller
{

    /**
     * Appworkorders constructor.
     */
    function __construct()
    {
        parent::__construct();
    }

    /**
     * @param int $page
     * @param int $limit
     */
    public function get($page = 1, $limit = 20)
    {
        $filters = [];
        $limit = intval($limit);
        $page = intval($page);

        if (isset($_POST['filters']) && !empty($_POST['filters'])) {
            $filters = $_POST['filters'];
            if (isset($filters['search'])) {
                $checkPhone = preg_match('/(\(?\d{3}\)?[\- ]?)?[\d\- ]{7,10}$/is', trim($filters['search']));
                if($checkPhone) {
                    $filters['search'] = numberFrom($filters['search']);
                }
            }
        }

        $workorders = Workorder::getWorkorders($page, $filters ?: [], $limit);

        if ($workorders !== false) {
            return $this->response($workorders);
        } else {
            return $this->response([
                'status' => false,
                'message' => 'Error getting a list of workorders'
            ], 400);
        }

    }

    /**
     * Update status
     */
    public function update_status()
    {
        $workorder_id = $this->input->post('workorder_id');
        $workorder_status_id = $this->input->post('workorder_status_id');
        $force = $this->input->post('force');
        $workorderModel = Workorder::find($workorder_id);

        if ($workorder_id == null || is_null($workorderModel)) {
            return $this->response(['status' => false, 'message' => 'Wrong ID provided'], 400);
        }

        $workorder = Workorder::getWorkorder($workorderModel);

        $this->load->library('Common/WorkorderActions');
        $this->load->library('Common/EstimateActions');

        if($force) {
            $this->estimateactions->completeAllNonDeclinedServices($workorderModel->estimate_id);
        }

        $result = $this->workorderactions->setStatus($workorder, $workorder_status_id, $force);

        if (!empty($result['invoice_id']) && ($result['status'] === TRUE || $result['status'] == 'success')) {
            return $this->response([
                'status' => $result['status'],
                'invoice_id' => $result['invoice_id'],
                'message' => $result['message']??'',
            ], $result['httpCode']??400);
        }
        return $this->response(['status' => $result['status'], 'message' => $result['message']??''], $result['httpCode']??400);
    }

    /**
     * @param null $id
     */
    public function fetch($id = null)
    {
        $workorder = Workorder::find($id);
        if ($id == null || is_null($workorder)) {
            return $this->response([
                'status' => false,
                'message' => 'Wrong ID provided'
            ], 400);
        }
        $workorder = Workorder::getWorkorder($workorder, true);

        //todo: REMOVE on finish app v1.19.* supporting
        foreach($workorder['schedules'] as &$val) {
            $val['schedule_teams'][] = $val['team'];
        }
        //todo
        
        return $this->response([
            'status' => true,
            'data' => $workorder
        ], 200);
    }

    /**
     * Upload file method
     */
    public function upload()
    {
        $workorder_id = $this->input->post('id');
        $workorder = $this->mdl_workorders->find_by_id($workorder_id);

        if (!$workorder && $workorder_id != 0) {
            return $this->response(array(
                'status' => false,
                'message' => 'Wrong workorder ID provided'
            ), 400);
        }

        $max = 1;

        $this->load->model('mdl_estimates');
        $estimate_data = $this->mdl_estimates->find_by_id($workorder->estimate_id);
        $path = 'uploads/payment_files/' . $workorder->client_id . '/' . $estimate_data->estimate_no . '/';

        $photos = [];
        $wo_pdf_files = $workorder->wo_pdf_files ? json_decode($workorder->wo_pdf_files, true) : [];
        if (isset($_FILES['files']) && is_array($_FILES['files'])) {
            $this->load->library('upload');

            foreach ($_FILES['files']['name'] as $key => $val) {

                $_FILES['file']['name'] = $_FILES['files']['name'][$key];
                $_FILES['file']['type'] = $_FILES['files']['type'][$key];
                $_FILES['file']['tmp_name'] = $_FILES['files']['tmp_name'][$key];
                $_FILES['file']['error'] = $_FILES['files']['error'][$key];
                $_FILES['file']['size'] = $_FILES['files']['size'][$key];

                $ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
                $suffix = $ext == 'pdf' ? 'pdf_' : null;
                $config['file_name'] = $suffix . 'lead_no_' . str_pad($workorder_id, 5, '0',
                        STR_PAD_LEFT) . '-W_' . $max++ . '.' . $ext;

                $config['upload_path'] = $path;
                $config['allowed_types'] = 'gif|jpg|jpeg|png|pdf|ogg|mp3|mp4|webm|aac|m4a|wav|GIF|JPG|JPEG|PNG|PDF|OGG|MP3|MP4|WEBM|AAC|M4A|WAV';
                $config['remove_spaces'] = true;
                $config['encrypt_name'] = true;

                $this->upload->initialize($config);
                if ($this->upload->do_upload('file')) {
                    $uploadData = $this->upload->data();

                    $photos[] = [
                        'filepath' => $path . $uploadData['file_name'],
                        'filename' => $uploadData['file_name']
                    ];
                    $wo_pdf_files[] = $path . $uploadData['file_name'];

                } else {
                    $photos[] = [
                        'error' => strip_tags($this->upload->display_errors())
                    ];
                }
            }
            $this->mdl_workorders->update_workorder(
                ['wo_pdf_files' => json_encode($wo_pdf_files)],
                ['workorders.id' => $workorder_id]
            );

            return $this->response(array(
                'status' => true,
                'data' => $photos
            ));
        } else {
            return $this->response(array(
                'status' => false,
                'message' => 'No files to upload'
            ), 400);
        }
    }

    /**
     * @param null $id
     */
    public function showPdf($id = null)
    {
        $this->load->library('Common/WorkorderActions');
        $result = $this->workorderactions->setWorkorderId($id);
        if (!$result) {
            return $this->response([
                'status' => false,
                'message' => 'Wrong ID provided'
            ], 400);
        }

        $result = $this->workorderactions->showPDF();
        if (!$result) {
            return $this->response([
                'status' => false,
                'message' => 'Workorder pdf not found'
            ], 400);
        }
    }

    /**
     * @return mixed
     */
    public function send_pdf_to_email()
    {
        $this->load->library('Common/WorkorderActions');
        return $this->workorderactions->send_pdf_to_email();
    }

    /**
     * Method update workorder notes
     */
    public function update_notes()
    {
        $request = request();
        if (!$request->input('id')) {
            return $this->response(['message' => 'Request is not valid'], 400);
        }

        /** @var Workorder $workorder */
        $workorder = Workorder::with('estimate')->find($request->input('id'));
        if (!$workorder) {
            return $this->response(['message' => 'Request is not valid'], 400);
        }

        if (is_string($request->input('wo_office_notes'))) {
            $workorder->wo_office_notes = trim($request->input('wo_office_notes'));
            $workorder->save();
        }
        if (is_string($request->input('estimate_crew_notes'))) {
            $workorder->estimate->estimate_crew_notes = trim($request->input('estimate_crew_notes'));
            $workorder->estimate->save();
        }
        return $this->response(['message' => 'Saved successfully', 'workorder' => $workorder], 200);
    }

    function workordersByStatuses(){
        $request = request();

        $status_query = WorkorderStatus::withCount(["workorders" => function ($q) {
            $q->permissions();
        }])->active();

        if(!$request->input('wo_status_id') && !$request->input('search_keyword'))
            $status_query->default();
        elseif($request->has('wo_status_id') && is_array($request->input('wo_status_id')) && !empty($request->input('wo_status_id')))
            $status_query->whereIn('wo_status_id', $request->input('wo_status_id'));
        elseif((int)$request->input('wo_status_id') > 0)
            $status_query->where('wo_status_id', '=', $request->input('wo_status_id'));
        elseif($request->input('wo_status_id')==-1 || $request->input('search_keyword'))
            $status_query->notFinished();

        $status = $status_query->get()->pluck('wo_status_id')->toArray();

        if((int)$request->input('event_id'))
            $event = ScheduleEvent::find($request->input('event_id'));

        $query = Workorder::select(Workorder::API_FIELDS)->with([
            'estimate' => function($query) use($status, $request){
                $query->select(Estimate::BASE_FIELDS);
                $query->withoutAppends();
                $query->withTotals($status, []);
                $query->withCount(['estimates_service AS total_time' => function ($query) {
                    $query->secvicesCalc()->newService();
                }]);

                $query->with([
                    'estimates_service'=>function($query){
                        $query->baseFields();
                        $query->doesntHave('bundle_service');
                        $query->with(['service', 'bundle.estimate_service.service', 'equipments', 'services_crew.crew', 'tree_inventory.tree_inventory_work_types.work_type']);
                        $query->withCount('services_crew');
                    },
                    'estimate_status',
                    'estimate_crews.crew',
                    'estimates_services_crew'=>function($query){ $query->newService()->crewsNamesLine(); },
                    'client'=>function($query){ $query->baseFields(); },
                    'lead'=>function($query){ $query->baseFields(); },
                    'user'=>function($query){ $query->baseFields(); },
                    'client_payments'=>function($query){ $query->orderBy('payment_date', 'DESC'); },
                    'invoice'
                ]);

                if($request->input('filter_crew')){
                    $query->with('estimates_new_services_crews');
                }

                return $query;
            }]);
        $query->withCount(['status_log as change_date' => function($query){ $query->lastChangeDate(); }]);
        $query->filters($request, $status);
        $query->permissions();

        return $this->response([
            'workorders' => $query->get()->sortByDesc('days_from_creation')->values(),
            'active_status' => (count($status) <= 1)?$status_query->first():[],
            'statuses' => WorkorderStatus::withCount(["workorders" => function ($q) {
                $q->permissions();
            }])->active()->notFinished()->orderBy('wo_status_priority')->get(),
            'estimates_services_status'=> EstimatesServicesStatus::get(),
            'event' => $event??false,
            'estimators'=> (!$request->input('count_estimators'))?User::active()->estimator()->orderBy('emailid', 'ASC')->get()->keyBy('id'):[],
            'crews' => (!$request->input('count_crews'))?Crew::active()->noDayOff()->get()->keyBy('crew_id'):[],
            'services' => (!$request->input('count_services'))?Service::baseFields()->active()->orderBy('service_priority')->get()->keyBy('service_id'):[],
            'filter_estimator' => $request->input('filter_estimator'),
            'filter_crew' => $request->input('filter_crew'),
            'filter_service' => $request->input('filter_service'),
            'filter_product' => $request->input('filter_product'),
            'filter_bundle' => $request->input('filter_bundle')
        ], 200);
    }
}
