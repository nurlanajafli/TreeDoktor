<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

use Illuminate\Support\Collection;

use Illuminate\Support\Carbon;
use ElephantIO\Client as WSClient;
use ElephantIO\Engine\SocketIO\Version1X;
use application\modules\schedule\models\ScheduleTeams;
use application\modules\schedule\models\TeamExpesesReport;
use application\modules\schedule\models\ScheduleEvent;
use application\modules\employees\models\SafetyPdfSign;

use application\resources\data\EventsSuggestedToolsResource;
use application\modules\app\resources\AppDashboardResource;
use application\modules\app\resources\AppAgendaResource;
use application\modules\app\resources\AppEventResource;

/**
 * @property  mdl_worked
 */
class Appjobs extends APP_Controller
{
    function __construct()
    {
        parent::__construct();
        $this->load->model('mdl_estimates');
        $this->load->model('mdl_team_expeses_report');
        $this->load->model('mdl_safety_pdf_signs');
        $this->load->model('mdl_worked');
        $this->load->config('safety_meeting_form');
        $this->load->library('Common/EventActions');
    }

	function dashboard($date){
        $checkDate = DateTime::createFromFormat('Y-m-d', $date);
        if (!$checkDate)
            return $this->response([
                'status' => FALSE,
                'message' => 'Incorrect date format'
            ], 400);

        $data = ScheduleTeams::with([
            'members' => function($query) use ($checkDate){
                return $query->appMember()->with(['employeeWorked' => function ($subQuery) use ($checkDate){
                    return $subQuery->whereDate("worked_date", "=", $checkDate)
                        ->with('logins:login_id,login_worked_id,login,logout', 'payroll:payroll_id,payroll_day');
                }]);
            },
            'equipment' => function($query){ return $query->nameOnly(); },
            'tools' => function($query){ return $query->nameOnly(); },
            'events.estimates_services_equipment',
        ])->appDashboard()
            ->datesInterval($date,$date)
            ->withMember($this->user->id)
            ->first();

        if(!$data)
            return $this->response(['status' => FALSE,'message' => 'No Team'], 200);

        $data->expenses = TeamExpesesReport::fields()
            ->lunchApproved()
            ->extraApproved()
            ->withDate($date)
            ->withTeam($data->team_id)
            ->get();

        return $this->response(['status'=>true, 'data'=>new AppDashboardResource($data)]);
    }

	function save_expenses($id = null) {
        $request = request();
		if(!$request->input('team_id') || !$request->input('user_id'))
            return $this->response([
                'status' => FALSE,
                'message' => 'Team ID and User ID are required'
            ], 400);

        $data['ter_team_id'] = $request->input('team_id');
        $data['ter_user_id'] = $request->input('user_id');

        if(!$request->has('lunch') && !$request->has('extra'))
            return $this->response([
                'status' => FALSE,
                'message' => 'No expenses provided'
            ], 400);

        $data['ter_bld'] = $request->input('lunch');
        $data['ter_extra'] = $request->input('extra');
        $data['ter_extra_comment'] = $request->input('note');
        $data['ter_date'] = $request->input("date")??date("Y-m-d");

        if($this->mdl_team_expeses_report->save($data, (int)$id)){
            $id = (!intval($id))?$this->db->insert_id():$id;
            return $this->response([
                'status' => TRUE,
                'data' => ['expense_id' => $id]
            ], 200);
        }

        return $this->response([
            'status' => FALSE,
            'message' => 'Expenses are not saved'
        ], 400);


	}

	function agenda($date = NULL, $toDate = NULL) {
        $date = $date ?? date("Y-m-d");
        $toDate = $toDate ?? $date;

        $response = (new AppAgendaResource(ScheduleTeams::with(['events'=>function($query){
            $query->has('workorder')->with('event_works');
        }])->datesInterval($date, $toDate)
            ->withMember($this->user->id)
            ->groupBy('team_id')
            ->get()))->toArray(request());

        return $this->response($response,200);
    }

    function fetch($id = NULL, $date = NULL) {

        $currentDate = $date/*($date)?$date:date("Y-m-d")*/;
        $event = (new AppEventResource(ScheduleEvent::find($id), $currentDate))->toArray(request());

        if(!count($event))
            return $this->response([
                'status' => FALSE,
                'message' => 'Incorrect Event'
            ], 400);

        $event['members'] = $this->mdl_safety_pdf_signs->getMembersWithSigns($event['id'], $event['event_work']->ev_date??false);

        return $this->response([
            'status' => TRUE,
            'data' => $event
        ], 200);
    }

    function safety_form() {
        $response = array(
            'status' => TRUE,
            'data' => [
                'hazards' => config_item('hazards'),
                'controls' => config_item('controls'),
            ],
        );
        return $this->response($response);
    }

    function start($id = NULL) {
        $id = (int)$id;
        $date = (request()->input('date'))?request()->input('date'):date("Y-m-d");
        if(!$id)
            return $this->response(array(
                'status' => FALSE,
                'message' => 'Incorrect ID'
            ), 200);

        $event = $this->mdl_schedule->find_by_id($id);

        if(!$event) {
            return $this->response([
                'status' => FALSE,
                'message' => 'Incorrect Event'
            ]);
        }

        if ($this->input->post('new_version')) {
            if (SafetyPdfSign::withEvent($id)->withDate($date)->signed()->count()==0) {
                return $this->response(['error' => 'Team Lead signature is absent']);
            }
        } else {
            $this->load->library('form_validation');
            $this->form_validation->set_rules('signature_image', 'Signature', 'required');
            if ($this->form_validation->run() == FALSE)
                return $this->response(array(
                    'status' => FALSE,
                    'message' => 'Validation Error',
                    'errors' => validation_errors_array()
                ), 200);
        }

        $result = $this->eventactions->start_work($this->input->post()+[
            'ev_team_id' => $event->event_team_id,
            'wo_id' => $event->event_wo_id
        ]);

        SafetyPdfSign::withDate($date)
            ->where('event_id', '=', $id)
            ->where('team_id', '=', $event->event_team_id)
            ->update(['work_event_id'=> $result]);

        return $this->response([
            'status' => TRUE,
            'data' => [
                'pdf_url' => base_url('events/tailgate_safety_pdf/' . $event->id)
            ]
        ]);
    }

    function stop($id = NULL) {

        $id = (int) $id;

        if(!$id)
            return $this->response(array(
                'status' => FALSE,
                'message' => 'Incorrect Event'
            ), 200);

        $event = $this->mdl_schedule->find_by_id($id);

        if(!$event)
            return $this->response(array(
                'status' => FALSE,
                'message' => 'Incorrect Event'
            ), 200);


        $this->load->library('form_validation');
        $this->form_validation->set_rules('status', 'Finished', 'required');
        $this->form_validation->set_rules('expenses', 'Expenses', 'required');
        $this->form_validation->set_rules('event_damage', 'Damage', 'required');
        $this->form_validation->set_rules('malfunctions_equipment', 'Malfunctions Equipment', 'required');
        $this->form_validation->set_rules('client_signature_image', 'Signature', 'required');
        if($this->input->post('event_payment')=='yes')
            $this->form_validation->set_rules('payment_amount', 'Payment amount', 'required');

        if ($this->form_validation->run() == FALSE)
            return $this->response(array(
                'status' => FALSE,
                'message' => 'Validation Error',
                'errors' => validation_errors_array()
            ), 200);


        $this->eventactions->setEventId($id);
        $result = $this->eventactions->end_work($this->input->post()+['wo_id'=>$event->event_wo_id]);

        return $this->response(array(
            'status' => TRUE,
            'data' => [
                'pdf_url' => base_url('events/report_pdf/' . $id)
            ],
        ));
    }

    function ride($id = NULL) {
        $request = request();
        $date = date("Y-m-d");
        if($request->input('date')){
            $date = $request->input('date');
        }

        $event = ScheduleEvent::with('workorder')->find($id);

        if(!$event)
            return $this->response(array(
                'status' => FALSE,
                'message' => 'Incorrect Event'
            ), 200);


        $this->eventactions->start_trevel([
            'ev_event_id'=>$id,
            'ev_team_id'=>$event->event_team_id,
            'ev_date'=>$date,
            'wo_id'=>$event->event_wo_id,
            'ev_estimate_id'=>$event->workorder->estimate_id
        ]);

        return $this->response(array(
            'status' => TRUE,
            'data' => [],
        ));
    }

	function show($id = NULL) {

	    $id = intval($id);

	    if(!$id)
            return $this->response(array(
                'status' => FALSE,
                'message' => 'Incorrect Event'
            ), 200);

        $event = $this->mdl_schedule->get_events(['schedule.id' => $id]);
        if(empty($event))
            return $this->response(array(
                'status' => FALSE,
                'message' => 'Incorrect Event'
            ), 200);

        $data['event'] = $event[0];

        $data['estimate_data'] = $this->mdl_estimates_orm->with('mdl_services_orm')->get_full_estimate_data(['estimate_id' => $data['event']['estimate_id']])[0];
        $data['origin']= $data['destination'] = config_item('office_location');
        $data['estimate_services'] = [];

        foreach ($data['estimate_data']->mdl_services_orm as $key => $value)
            $data['estimate_services'][$value->service_id] = $value;

        $data['client_data'] = $this->mdl_clients->find_by_id($data['estimate_data']->client_id);
        $data['client_contact'] = $this->mdl_clients->get_primary_client_contact($data['estimate_data']->client_id);

        $data['estimator_data'] = $this->mdl_user->find_by_id($data['estimate_data']->user_id);
        unset($data['estimator_data']->password);
        $data['members'] = $this->mdl_schedule->get_team_members(array('employee_team_id' => $data['event']['team_id']));

        list($data['hospital_address'], $data['hospital_name'], $data['hospital_coords']) = getNearestHospitalInfo($data['estimate_data']->lat, $data['estimate_data']->lon, implode(',', [$data['estimate_data']->lead_address, $data['estimate_data']->lead_city, $data['estimate_data']->lead_state]));

        $workorder = $this->mdl_workorders->find_by_fields(['estimate_id' => $data['estimate_data']->estimate_id]);
        $data['files'] = (isset($workorder->wo_pdf_files) && $workorder->wo_pdf_files)?json_decode($workorder->wo_pdf_files, true):[];

        $data['service_names'] = array_map(function($service_data){
            return $service_data->service->service_name;
        }, $data['estimate_data']->mdl_services_orm);

        $data['event_id'] = $id;
        $data['event_services'] = $this->mdl_schedule->get_event_services(['event_id' => $id]);
        $data["tools"] = $this->mdl_vehicles->get_many_by(array('vehicle_trailer' => 2, 'vehicle_disabled' => NULL));

        $data['started_events'] = $this->mdl_events_orm->get_started(['ev_event_id'=>$data['event']['id'], 'ev_team_id'=>$data['event']['team_id']]);
        return $this->response(array(
            'status' => TRUE,
            'data' => $data
        ));
    }

    function upload()
    {
        $id         = (int) $this->input->post('id');
        $isOffline  = (int) $this->input->post('is_offline');
        $serviceId  = (int) $this->input->post('service');

        /*Временный костыль для загрузки файлов из оффлайн мода*/
        if($isOffline)
            return $this->_offline_upload();

        if(!$id)
            return $this->response(array(
                'status' => FALSE,
                'message' => 'Incorrect Event'
            ), 400);

        $event = $this->mdl_schedule->get_events_dashboard(['schedule.id'=>$id]);
        if(empty($event))
            return $this->response(array(
                'status' => FALSE,
                'message' => 'Incorrect Event'
            ), 200);
        $event = $event[0];

        $numFile = 1;

        $path = 'uploads/payment_files/';
        $path .= $event['client_id'] . '/';
        $path .= $event['estimate_no'] . '/';

        $files = bucketScanDir($path);

        if ($files && !empty($files)) {
            sort($files, SORT_NATURAL);
            preg_match('/workorder_no_' . $event['workorder_no'] . '.*?_([0-9]{1,})/is', $files[count($files) - 1], $matches);//countOk
            preg_match('/pdf_workorder_no_' . $event['workorder_no'] . '.*?_([0-9]{1,})/is', $files[count($files) - 1], $matches1);//countOk
            $numFile = isset($matches[1]) ? ($matches[1] + 1) : 1;
            $numFile1 = isset($matches1[1]) ? ($matches1[1] + 1) : 1;
            $numFile = $numFile1 > $numFile ? $numFile1 : $numFile;
       }
        $photos = [];
        $wo_pdf_files = $event['wo_pdf_files'] ? json_decode($event['wo_pdf_files'], TRUE) : [];

        if (isset($_FILES['files']) && is_array($_FILES['files'])) {
            $this->load->library('upload');
            foreach ($_FILES['files']['name'] as $key => $val) {

                $_FILES['file']['name'] = $_FILES['files']['name'][$key];
                $_FILES['file']['type'] = $_FILES['files']['type'][$key];
                $_FILES['file']['tmp_name'] = $_FILES['files']['tmp_name'][$key];
                $_FILES['file']['error'] = $_FILES['files']['error'][$key];
                $_FILES['file']['size'] = $_FILES['files']['size'][$key];
                $ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);

                $config['upload_path'] = $path;
                $config['allowed_types'] = 'gif|jpg|jpeg|png|pdf|ogg|mp3|mp4|webm|aac|m4a|wav|GIF|JPG|JPEG|PNG|PDF|OGG|MP3|MP4|WEBM|AAC|M4A|WAV';
                $config['file_name'] = 'workorder_no_' . $event['workorder_no'] . '_job_' . $numFile++ . '.' . $ext;

                $this->upload->initialize($config);
                if ($this->upload->do_upload('file')) {
                    $uploadData = $this->upload->data();

                    //copy a file in a estimate service
                    if($serviceId) {
                        $servicePath = 'uploads/clients_files/' . $event['client_id'] . '/estimates/' . $event['estimate_no'] . '/' . $serviceId . '/' . $uploadData['file_name'];
                        bucket_copy($path . $uploadData['file_name'], $servicePath);
                        $wo_pdf_files[] = $servicePath;
                        $photos[] = [
                            'filepath' => $servicePath,
                            'filename' => $uploadData['file_name']
                        ];
                    } else {
                        $photos[] = [
                            'filepath' => $path . $uploadData['file_name'],
                            'filename' => $uploadData['file_name']
                        ];
                        $wo_pdf_files[] = $path . $uploadData['file_name'];
                    }
                } else {
                    $photos[] = [
                        'error' => strip_tags($this->upload->display_errors())
                    ];
                }
            }
            $this->mdl_workorders->update_workorder(['wo_pdf_files' => json_encode($wo_pdf_files)], ['workorders.id' => $event['wo_id']]);
            return $this->response(array(
                'status' => TRUE,
                'data' => $photos
            ));
        }
        else {
            return $this->response(array(
                'status' => FALSE,
                'message' => 'Uploading Error'
            ), 400);
        }
    }

    private function _offline_upload() {

        $id = intval($this->input->post('id'));
        if(!$id)
            return $this->response(array(
                'status' => FALSE,
                'message' => 'Incorrect Event'
            ), 400);

        $sql = 'START TRANSACTION';
        $this->db->query($sql);

        $sql = 'SELECT * FROM schedule JOIN workorders ON event_wo_id = workorders.id WHERE schedule.id = ' . $id . ' ' .
            'LIMIT 1 FOR UPDATE';
        $event = $this->db->query($sql)->row_array();

        if(!$event)
            return $this->response(array(
                'status' => FALSE,
                'message' => 'Incorrect Event'
            ), 200);
        $event['estimate_no'] = str_replace('W', 'E', $event['workorder_no']);
        $path = 'uploads/payment_files/';
        $path .= $event['client_id'] . '/';
        $path .= $event['estimate_no'] . '/';
        $wo_pdf_files = $event['wo_pdf_files'] ? json_decode($event['wo_pdf_files'], TRUE) : [];

        if (isset($_FILES['files']) && isset($_FILES['files']['name'][0])) {
            $_FILES['file']['name'] = $_FILES['files']['name'][0];
            $_FILES['file']['type'] = $_FILES['files']['type'][0];
            $_FILES['file']['tmp_name'] = $_FILES['files']['tmp_name'][0];
            $_FILES['file']['error'] = $_FILES['files']['error'][0];
            $_FILES['file']['size'] = $_FILES['files']['size'][0];

            $ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
            $config['upload_path'] = $path;
            $config['allowed_types'] = 'gif|jpg|jpeg|png|pdf|ogg|mp3|mp4|webm|aac|m4a|wav|GIF|JPG|JPEG|PNG|PDF|OGG|MP3|MP4|WEBM|AAC|M4A|WAV';
            $config['file_name'] = 'workorder_no_' . $event['workorder_no'] . '_job_' . uniqid() . '.' . $ext;

            $wo_pdf_files[] = $path . $config['file_name'];
            $this->mdl_workorders->update_workorder(['wo_pdf_files' => json_encode($wo_pdf_files)], ['workorders.id' => $event['event_wo_id']]);

            $sql = 'COMMIT';
            $this->db->query($sql);

            $this->load->library('upload');
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
                unset($wo_pdf_files[count($wo_pdf_files) - 1]);//countOk
                $this->mdl_workorders->update_workorder(['wo_pdf_files' => json_encode($wo_pdf_files)], ['workorders.id' => $event['event_wo_id']]);
            }
            return $this->response(array(
                'status' => TRUE,
                'data' => $photos
            ));
        }
        else {

            $sql = 'COMMIT';
            $this->db->query($sql);

            return $this->response(array(
                'status' => FALSE,
                'message' => 'Uploading Error'
            ), 400);
        }

        return TRUE;
    }

    function delete_file() {
	    $id = $this->input->post('id');
        $path = $this->input->post('file');
        $event = $this->mdl_schedule->get_events_dashboard(['schedule.id'=>$id]);

        if(!$path)
            return $this->response(array(
                'status' => FALSE,
                'message' => 'Incorrect File'
            ), 400);

        if(!$event || empty($event))
            return $this->response(array(
                'status' => FALSE,
                'message' => 'Incorrect Event'
            ), 400);

        $event = $event[0];

        $wo_pdf_files = $event['wo_pdf_files'] ? json_decode($event['wo_pdf_files']) : [];

        if(is_bucket_file($path) && array_search($path, $wo_pdf_files) !== FALSE) {
            bucket_unlink($path);
            $key = array_search($path, $wo_pdf_files);
            unset($wo_pdf_files[$key]);
            $wo_pdf_files = array_values($wo_pdf_files);
            $this->mdl_workorders->update_workorder(['wo_pdf_files' => json_encode($wo_pdf_files)], ['workorders.id' => $event['wo_id']]);
            return $this->response(array(
                'status' => TRUE
            ), 200);
        }
        return $this->response(array(
            'status' => FALSE,
            'message' => 'File Not Found'
        ), 400);
    }


    /**
     * @param $team_id
     * @return mixed
     */
    public function getSafetyPdfSign($event_id)
    {
        return $this->mdl_safety_pdf_signs->getMembersWithSigns($event_id);
    }

    public function setSafetyPdfSign()
    {
        $response   = false;
        $methodName = 'jobSafetyPdfSign';

        $postData = [
            'event_id'          => (int) $this->input->post('id'),
            'team_id'           => (int) $this->input->post('event_team_id'),
            'user_id'           => (int) $this->input->post('user_id'),
            'is_teamlead'       => false,
            'safety_pdf_sign'   => $this->input->post('sign'),
            'signed'            => Carbon::now()->format('Y-m-d H:i:s'),
        ];

        if (in_array(null, $postData, true) || in_array('', $postData, true)) {
            return $this->response(['error' => 'Not enough data']);
        }

        $user_id = $this->user->id??request()->user()->id;
        $isTeamleadScreen = collect(ScheduleTeams::teamLead($user_id)->find($postData['team_id']))->isNotEmpty();

        if(!$isTeamleadScreen && ($user_id && $postData['user_id'] != $user_id))
            return $this->response(['error' => 'You don\'t have permission to signing instead this user']);

        $postData['date'] = date("Y-m-d");

        $signKey = SafetyPdfSign::whereDate('date', '=', $postData['date'])
            ->where('event_id', '=', $postData['event_id'])
            ->where('user_id', '=', $postData['user_id'])->first();

        if(!$signKey)
            $signKey = new SafetyPdfSign();

        if($isTeamleadScreen && $postData['user_id'] == $user_id)
            $postData['is_teamlead'] = 1;

        $signKey->fill($postData);
        $signKey->save();

        $response = [
            'job_id'    => $postData['event_id'],
            'user_id'   => $postData['user_id'],
            'signed'    => $postData['signed'],
        ];
        $user_id = $this->user->id??request()->user()->id;
        if($this->config->item('wsClient')) {
            $wsClient = new WSClient(new Version1X($this->config->item('wsClient') . '?chat=1&user_id=' . $user_id));
            if($wsClient) {
                $wsClient->initialize();
                $wsClient->emit('room', ['chat']);
                $wsClient->emit('message', ['method' => $methodName, 'params' => $response]);
                $wsClient->close();
            }
        }

        return $this->response($response);
    }

    /**
     * @param $team_id
     * @param $user_id
     * @return bool
     */
    protected function isAllowSafetyPdfSigning($team_id, $user_id): bool
    {
        $isAllowed = true;
        $login_user_id = $this->user->id??request()->user()->id;
        $isTeamLead = $this->mdl_schedule->isTeamLead($team_id, $login_user_id);

        if(!$isTeamLead && $user_id != $login_user_id) {
            $isAllowed = false;
        }

        return $isAllowed;
    }

    /**
     * @param null $id
     */
    public function showInvoicePdf($id = null)
    {
        if (is_null($id) || empty($id)) {
            return $this->response([
                'status' => false,
                'message' => 'Wrong ID provided'
            ], 400);
        }

        /***ALLOW FIELDWORKER WITH FWI PERM TO SHOW INVOICE***/
        $fwiPerm = request()->user()->modulesForPermissions->where('module_id', 'FWI')->first();
        if(($fwiPerm && $fwiPerm->module_status == 1) || is_admin()) {
            request()->user()->modulesForPermissions->where('module_id', 'CL')->first()->module_status = '1';
        } else {
            return $this->response([
                'status' => false,
                'message' => 'You don\'t have permission to view this file'
            ], 400);
        }
        /***ALLOW FIELDWORKER WITH FWI PERM TO SHOW INVOICE***/

        $this->load->library('Common/InvoiceActions', ['invoice_id' => $id]);
        $result = $this->invoiceactions->invoice_pdf();
        if (!$result) {
            return $this->response([
                'status' => false,
                'message' => 'Invoice pdf not found'
            ], 400);
        }
    }
}
