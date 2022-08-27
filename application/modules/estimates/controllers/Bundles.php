<?php

use application\modules\estimates\models\Service;
use application\modules\qb\models\QbLogs as QbLogsModel;
use application\modules\categories\models\Category;

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Bundles extends MX_Controller
{
    function __construct()
    {
        parent::__construct();

        //Checking if user is logged in;
        if (!isUserLoggedIn() && $this->router->fetch_method() != 'send' && $this->router->fetch_class() != 'appestimates' && $this->router->fetch_method() != 'estimate' && $this->router->fetch_class() != 'payments' && !$this->input->is_cli_request()) {
            redirect('login');
        }

        //Global settings:
        $this->_title = SITE_NAME;
        $this->load->model('mdl_services');
        $this->load->model('mdl_bundles_services');
        $this->load->model('mdl_settings_orm');
        $this->load->config('form_validation');
        $this->load->library('form_validation');
        $this->load->library('Common/EstimateActions');
    }

    public function index()
    {
        if ($this->session->userdata('user_type') != "admin" && $this->session->userdata('CL') != 1) {
            show_404();
        }
        $data['title'] = 'Estimate Bundles';
        $order_by = ['service_status' => 'DESC', 'service_priority' => 'ASC'];
        $bundles = $this->mdl_services->order_by($order_by)->get_many_by(['is_bundle' => 0, 'service_status' => 1]);

        $allBundles = [];
        foreach ($bundles as $bundle)
        {
            $allBundles[] = [
                'id' => $bundle->service_id,
                'text' => $bundle->service_name
            ];
        }
        $categoryWithItems = Category::whereNull('category_parent_id')->with(['categoriesWithProducts', 'items'])->get()->toArray();
        $data['allBundles'] =  $this->estimateactions->getCategoryWithItemsForSelect2($categoryWithItems);

//        $data['allBundles'] = $allBundles;
        $data['bundles'] = $this->mdl_services->order_by($order_by)->get_many_by(['is_bundle' => 1]);
        $data['access_token'] = $this->mdl_settings_orm->get_by('stt_key_name', 'accessTokenKey')->stt_key_value;
        $this->load->view('bundles/index', $data);
    }

    public function edit()
    {
        $response = ['status'=>'ok'];
        $bundle = $this->mdl_services->get($this->input->post('service_id'));
        $bundleNew = setFavouriteShortcut([$bundle]);
        $bundle_services = $this->mdl_bundles_services->get_all_bundle_services(['bundle_id' => $this->input->post('service_id')]);
        if(!$bundle)
            die(json_encode(['status'=>'error', 'errors'=>'Bundle']));
        $response['html'] = $this->load->view('bundles/bundle_form', ['bundle'=>(array)$bundleNew[0], 'bundle_services'=>(array)$bundle_services], true);
        return $this->response($response);
    }

    public function save()
    {
        $response = ['status'=>'ok'];
        $bundleId = $this->input->post('service_id');

        $rules = config_item('bundles');
        if($bundleId)
            $rules = config_item('update_bundle');

        $this->form_validation->set_rules($rules);
        if(!$this->form_validation->run()) {
            $response['errors'] = $this->form_validation->error_array();
            $response['status'] = 'error';
            return $this->response($response);
        }
        $bundleServices = json_decode($this->input->post('bundle_services'), TRUE);
        $viewInPdf = !empty($this->input->post('is_view_in_pdf')) ? 1 : 0;
        $data = [
            'service_name'=>strip_tags($this->input->post('bundle_name', TRUE)),
            'service_description'=>strip_tags($this->input->post('bundle_description', TRUE)),
            'cost' => $this->getCostBundle($bundleServices),
            'is_bundle' => 1,
            'is_view_in_pdf' => $viewInPdf,
            'service_is_favourite' => 0
        ];

        $is_favourite = $this->input->post('is_favourite');
        $favouriteIcon = $this->input->post('favourite_icon_' . $bundleId);
        if(!empty($is_favourite)) {
            $data['service_favourite_icon'] = $favouriteIcon;
            $data['service_is_favourite'] = 1;
        }

        if(!$bundleId){
            $service_id = $this->mdl_services->insert($data);
            foreach ($bundleServices as $bundleService) {
                $bundleServicesForDB = [
                    'bundle_id' => $service_id,
                    'service_id' => $bundleService['id'],
                    'qty' => $bundleService['qty']
                ];
                $this->mdl_bundles_services->insert($bundleServicesForDB);
            }

            pushJob('quickbooks/item/syncserviceinqb', serialize(['id' => $service_id, 'qbId' => '']));
            return $this->response($response);
        }

        $bundle = $this->mdl_services->get($bundleId);
        if(!$bundle)
            return $this->response(['status'=>'error', 'errors'=>['service_name'=>'Bundle is not exist']]);

        $this->mdl_services->update($bundle->service_id, $data);
        $this->mdl_bundles_services->delete_by(['bundle_id' => $bundle->service_id]);
        foreach ($bundleServices as $bundleService) {
            $bundleServicesForDB = [
                'bundle_id' => $bundle->service_id,
                'service_id' => $bundleService['id'],
                'qty' => $bundleService['qty']
            ];
            $this->mdl_bundles_services->insert($bundleServicesForDB);
        }
        pushJob('quickbooks/item/syncserviceinqb', serialize(['id' => $bundle->service_id, 'qbId' => $bundle->service_qb_id]));
        return $this->response($response);
    }

    private function getCostBundle(array $services)
    {
        $cost = 0;
        foreach ($services as $service){
            $result = $this->mdl_services->get($service['id']);
            if($result && !empty($result->cost))
                $cost += $service['qty'] * $result->cost;
        }
        return $cost;
    }

    public function delete()
    {
        $response = ['status'=>'ok'];
        $bundle = $this->mdl_services->get($this->input->post('service_id'));
        if(!$bundle)
            die(json_encode(['status'=>'error', 'errors'=>'Bundle']));

        $status = $this->input->post('status') ? 0 : 1;
        $this->mdl_services->update($this->input->post('service_id'), ['service_status' => $status]);
        //create a new job for synchronization in QB
        pushJob('quickbooks/item/syncserviceinqb', serialize(['id' => $this->input->post('service_id'), 'qbId' => '']));
        die(json_encode($response));
    }

    public function has_bundle_services(){
        $array = json_decode($this->input->post('bundle_services'));
        $this->form_validation->set_message('has_bundle_services', 'Add at least 1 item');

        if(empty($array))
            return false;

        return true;
    }

    public function is_unique_name(){
        $bundleId = $this->input->post('service_id');
        $services = Service::where('service_name', $this->input->post('bundle_name'))->pluck('service_id')->toArray();
        $this->form_validation->set_message('is_unique_name', 'This name already exists.');
        if($services && $bundleId && !in_array($bundleId, $services))
            return false;

        return true;
    }
}