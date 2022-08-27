<?php

use application\modules\brands\models\Brand;
use application\modules\clients\models\Client;
use application\modules\estimates\models\Estimate;
use application\modules\workorders\models\Workorder;
use application\modules\invoices\models\Invoice;
use application\modules\schedule\models\ScheduleEvent;
use application\modules\tasks\models\Task;

use application\modules\clients\models\ClientLetter;
use application\modules\clients\requests\ClientLetterRequest;
use Illuminate\Http\JsonResponse;

class ClientLetters  extends MX_Controller{
    function __construct()
    {
        parent::__construct();

        if (!isUserLoggedIn()) {
            redirect('login');
        }
        $this->_title = SITE_NAME;

        $this->load->helper("user");
        $this->load->helper("user_tasks");
    }

    public function index(){

        if ($this->session->userdata('user_type') != "admin" && $this->session->userdata('EM_TMP') != 1) {
            show_404();
        }

        $data['title'] = 'Letter Templates';
        $data['letters'] = ClientLetter::orderBy('email_system_template', 'ASC')->get();
        $this->load->view('letters/index', $data);
    }

    public function getTemplate(){

        try {
            $request = app(ClientLetterRequest::class);
        } catch (ValidationException $e) {
            return $this->errorResponse(null, $e->validator->errors());
        }

        $client_data = [];
        if($request->input('client_id'))
            $client_data = Client::with('primary_contact')->find($request->input('client_id'));

        $estimate_data = [];
        $workorder_data = [];
        $reload = false;

        if ($request->input('estimate_id') && (int)$request->input('estimate_id')) {
            $estimate_data = Estimate::with(['client.primary_contact', 'user', 'lead', 'invoice'])->find($request->input('estimate_id'));
            $client_data = (!$client_data && $estimate_data->client)?$estimate_data->client:$client_data;
        }
        if ($request->input('workorder_id')) {
            $workorder_data = Workorder::with(['estimate.user', 'estimate.client.primary_contact'])->find($request->input('workorder_id'));
        }
        if ($request->input('task_id')) {
            $task_data = Task::with(['user', 'client'])->find($request->input('task_id'));
            $client_data = $task_data->client;
        }
        if ($request->input('event_id')) {
            $event_data = ScheduleEvent::with(['workorder', 'schedule_event_service'])->find($request->input('event_id'));
            $client_data = $event_data->workorder->estimate->client??[];
        }


        $brand_id = get_brand_id(($estimate_data)?$estimate_data->toArray():[], ($client_data)?$client_data->toArray():[]);

        $letter_condition = [];
        if ($request->input('system_label'))
            $letter_condition = ['system_label'=>$request->input('system_label')];

        if ($request->input('email_template_id'))
            $letter_condition = ['email_template_id'=>$request->input('email_template_id')];

        if (!$letter = ClientLetter::where($letter_condition)->first()) {
            return $this->errorResponse('Email template not found');
        }

        if ( $letter->system_label == "invoice_paid_thanks")
           $reload = true;

        $letter = ClientLetter::compileLetter($letter, $brand_id, [
            'client'    =>  $client_data,
            'estimate'  =>  $estimate_data,
            'workorder' =>  $workorder_data,
            'task'      =>  $task_data??[],
            'schedule_event' =>  $event_data??[]
        ]);

        return $this->successResponse([
            'session'   =>  $this->session->userdata(),
            'client'    =>  $client_data,
            'estimate'  =>  $estimate_data??[],
            'workorder' =>  $workorder_data??[],
            'invoice'   =>  $estimate_data->invoice??[],
            'task'      =>  $task_data??[],
            'schedule_event' => $event_data??[],
            'letter'    =>  $letter,
            'brand'     =>  [
                'brand_email' => brand_email($brand_id),
                'brand_name' => brand_name($brand_id),
                'brand_phone' => brand_phone($brand_id),
                'brand_address' => brand_address($brand_id, null, true),
                'brand_site' => brand_site($brand_id)
            ],
            'related_sms_id' => $request->input('related_sms_id'),
            'reload' => $reload
        ]);
    }
}
