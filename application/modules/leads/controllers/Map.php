<?php
use application\modules\leads\models\Lead;
use application\modules\leads\models\LeadStatus;
use application\modules\user\models\User;
use application\modules\tasks\models\Task;
use application\modules\tasks\models\TaskCategory;
use application\modules\estimates\models\Service;

class Map extends MX_Controller
{
    function __construct()
    {
        parent::__construct();
        if (!isUserLoggedIn()) {
            redirect('login');
        }
        if (is_cl_permission_none()) {
            redirect('dashboard');
            return;
        }

        $this->_title = SITE_NAME;
    }

    function index()
    {
        $data['title'] = $this->_title . ' - Leads';
        $center = explode(', ', (config_item('map_center'))?config_item('map_center'):'');
        $data['center'] = ['lat' => $center[0]??'0', 'lon' => $center[1]??'0'];

        $data['priority'] = Lead::getPriorities();
        $data['statuses'] = LeadStatus::with('reasons')->active()->get();
        $data['leads'] = Lead::with([
            'user',
            'client'=>function($query){ /*$query->append('brand');*/ },
            'lead_services'
        ])->whereHas('client', function($query) {
            $query->whereNotNull('client_id');
        })->where(function ($query){
            $query->defaultStatus();
        })->orWhere(function ($query){
            $query->draftStatus();
        })->permissions()->postponePassed()->ascending()->get();

        $data['leads']->map->client->append('brand');
        $data['users'] = User::with('employee')->active()->noSystem()->estimator()->get();
        $data['select2Users'] = User::prepareDataForSelect2($data['users']);
        
        /*------------delete after refactoring------------*/
        $this->load->model('mdl_sms');
        $data['sms'] = [];
        if(config_item('messenger') && $this->session->userdata('twilio_worker_id')) {
            $data['sms'] = $this->mdl_sms->get(3);
        }
        /*------------delete after refactoring------------*/
        $data['circles'] = config_item('leads_circles');
        $data['polylines'] = config_item('leads_polylines');

        /* -----------get services ----------- */
        $services = Service::getServiceTags();

        $data['services'] = json_encode($services['serviceTags'] ?? []);
        $data['products'] = json_encode($services['productTags'] ?? []) ;
        $data['bundles'] = json_encode($services['bundleTags'] ?? []) ;

        $this->load->view('leads_mapper/index', $data);
    }

    function tasks(){
        $data['tasks'] = Task::with('user.employee', 'client', 'owner', 'category')
            ->withoutExpiredTaskByDay()
            ->map()
            ->new()
            ->estimator()
            ->get();
        $data['categories'] = TaskCategory::whereCategoryActive('1')->get();

        return $this->response([
            'status'=>true, 'data'=>$data
        ]);
    }
}
