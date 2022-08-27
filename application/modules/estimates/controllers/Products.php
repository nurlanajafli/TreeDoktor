<?php

use application\modules\estimates\models\Service;
use Aws\S3\S3Client;
use application\modules\qb\models\QbLogs as QbLogsModel;
use application\modules\categories\models\Category;
use application\modules\classes\models\QBClass;

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

class Products extends MX_Controller
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
		$this->load->model('mdl_settings_orm');
		$this->load->config('form_validation');
		$this->load->library('form_validation');

	}

//*******************************************************************************************************************
//*************
//*************																				Estimates Index Function;
//*************
//*******************************************************************************************************************	

	public function index()
	{
		if ($this->session->userdata('user_type') != "admin" && $this->session->userdata('CL') != 1) {
			show_404();
		}
		
		$this->load->model('mdl_vehicles');
		$data['title'] = 'Estimate Products';

		$order_by = ['service_status' => 'DESC', 'service_priority' => 'ASC'];
		$products = $this->mdl_services->order_by($order_by)->get_many_by(['is_product' => 1]);
        $data['products'] = setFavouriteShortcut($products);
        $data['access_token'] = $this->mdl_settings_orm->get_by('stt_key_name', 'accessTokenKey')->stt_key_value;
        $data['categories'] = Category::whereNull('category_parent_id')->with(['categoriesWithProducts', 'products'])->orderBy('category_active', 'DESC')->orderBy('category_priority', 'ASC')->get()->toArray();
        $categories = Category::whereNull('category_parent_id')->where('category_active', 1)->with('categories')->orderBy('category_active', 'DESC')->orderBy('category_priority', 'ASC')->get()->toArray();
        $data['categoriesWithChildren'] = getCategories($categories);
        $classes = QBClass::where(['class_parent_id' => null, 'class_active' => 1])->with('classesWithoutInactive')->get();
        $data['classes'] = [];
        if(!empty($classes->toArray())) {
            $data['classes'] = getClasses($classes->toArray());
        }
		$this->load->view('products/index', $data);
	}

	public function edit()
	{
		$response = ['status'=>'ok'];
		$product = $this->mdl_services->get($this->input->post('service_id'));
        $productNew = setFavouriteShortcut([$product]);
		if(!$product)
			die(json_encode(['status'=>'error', 'errors'=>'Product']));
        $categories = getCategories(Category::whereNull('category_parent_id')->with('categories')->get()->toArray());
        $classes = QBClass::where(['class_parent_id' => null, 'class_active' => 1])->with('classesWithoutInactive')->get();
        $classWithChildren = [];
        if(!empty($classes->toArray())) {
            $classWithChildren = getClasses($classes->toArray());
        }

		$response['html'] = $this->load->view('products/product_form', ['product'=>(array)$productNew[0], 'categories' => $categories, 'classes' => $classWithChildren], true);

		die(json_encode($response));
	}

	public function save()
	{
		$response = ['status'=>'ok'];

        $productId = $this->input->post('service_id');
        $rules = config_item('products');
        if($productId)
            $rules = config_item('update_product');
        $this->form_validation->set_rules($rules);
        $validation = $this->form_validation->run();
		if(!$this->form_validation->run()) {
			$response['errors'] = $this->form_validation->error_array();
			$response['status'] = 'error';
			die(json_encode($response));
		}

		$data = [
			'service_name'=>strip_tags($this->input->post('service_name', TRUE)),
			'service_description'=>strip_tags($this->input->post('service_description', TRUE)),
			'cost' => strip_tags($this->input->post('cost', TRUE)),
			'is_product' => 1,
            'service_is_favourite' => 0,
            'service_is_collapsed' => 1
		];


        $is_favourite = $this->input->post('is_favourite');
        $favouriteIcon = $this->input->post('favourite_icon_' . $productId);
        if(!empty($is_favourite)) {
            $data['service_favourite_icon'] = $favouriteIcon;
            $data['service_is_favourite'] = 1;
        }
        $service_is_collapsed = $this->input->post('service_collapsed_view');
        if(empty($service_is_collapsed))
            $data['service_is_collapsed'] = 0;

        $categoryId = $this->input->post('categoryId');
        $data['service_category_id'] = !empty($categoryId) ? $categoryId : 1;
        $classId = $this->input->post('classId');
        if(!empty($classId)){
            $data['service_class_id'] = $classId;
        }else {
            $data['service_class_id'] = null;
        }
		
		if(!$productId){
			$service_id = $this->mdl_services->insert($data);
			pushJob('quickbooks/item/syncserviceinqb', serialize(['id' => $service_id, 'qbId' => '']));
			die(json_encode($response));	
		}
		
		$product = $this->mdl_services->get($productId);
		if(!$product)
			die(json_encode(['status'=>'error', 'errors'=>['service_name'=>'Product is not exist']]));
		
		$this->mdl_services->update($product->service_id, $data);
		pushJob('quickbooks/item/syncserviceinqb', serialize(['id' => $product->service_id, 'qbId' => $product->service_qb_id]));
		die(json_encode($response));
	}

	public function delete()
	{
		$response = ['status'=>'ok'];
		$product = $this->mdl_services->get($this->input->post('service_id'));
		if(!$product)
			die(json_encode(['status'=>'error', 'errors'=>'Product']));

		$status = $this->input->post('status') ? 0 : 1;
		$this->mdl_services->update($this->input->post('service_id'), ['service_status' => $status]);
        //create a new job for synchronization in QB
        pushJob('quickbooks/item/syncserviceinqb', serialize(['id' => $this->input->post('service_id'), 'qbId' => '']));
		die(json_encode($response));
	}

    public function is_unique_name(){
        $productId = $this->input->post('service_id');
        $services = Service::where('service_name', $this->input->post('service_name'))->pluck('service_id')->toArray();
        $this->form_validation->set_message('is_unique_name', 'This name already exists.');
        if($services && $productId && !in_array($productId, $services))
            return false;

        return true;
    }
}