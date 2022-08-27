<?php
use application\modules\categories\models\Category as CategoryModel;
use application\modules\estimates\models\Service;
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Categories extends MX_Controller
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

        $this->load->config('form_validation');
        $this->load->library('form_validation');
    }

    function ajaxChangeItemCategory(){
        $itemId = $this->input->post('itemId');
        $categoryId = $this->input->post('categoryId');
        if(!empty($itemId) && !empty($categoryId)){
            $affectedRow = Service::where('service_id', $itemId)->update(['service_category_id' => $categoryId]);
            if(!empty($affectedRow)) {
                //create a new job for synchronization in QB
                $service = Service::where('service_id', $itemId)->first();
                if(!empty($service))
                    pushJob('quickbooks/item/syncserviceinqb', serialize(['id' => $itemId, 'qbId' => $service->service_qb_id]));
                return $this->response('success!', 200);
            }
        }
        return $this->response('error!', 400);
    }
    function ajaxChangeParentCategory(){
        $categoryId = $this->input->post('categoryId');
        $categoryParentId = empty($this->input->post('categoryParentId')) ? null : $this->input->post('categoryParentId');
        if(!empty($categoryId)){
            if(!empty($categoryParentId)){
                $categories = CategoryModel::where('category_id', $categoryId)->with('categories')->get()->toArray();
                $isChild = $this->checkIsChild($categories, $categoryParentId);
                if($isChild == true){
                    return $this->response(['status'=>'error', 'message' => 'The item cannot be a subitem of itself.']);
                }
            }
            $affectedRow = CategoryModel::where('category_id', $categoryId)->update(['category_parent_id' => $categoryParentId]);
            $category = CategoryModel::where('category_id', $categoryId)->first();
            if(!empty($affectedRow) && !empty($category)) {
                pushJob('quickbooks/category/synccategoryinqb', serialize(['id' => $categoryId, 'qbId' => $category->category_qb_id]));
                return $this->response('success!', 200);
            }
        }
        return $this->response('error!', 400);
    }

    function index(){
        $data['categories'] = CategoryModel::whereNull('category_parent_id')->with('categories')->get();
        return $this->load->view('index', $data);
    }

    function ajaxSaveCategory(){
        $response = ['status'=>'ok'];
        $rules = config_item('categories');
        $this->form_validation->set_rules($rules);

        if(!$this->form_validation->run()) {
            $response['errors'] = $this->form_validation->error_array();
            $response['status'] = 'error';
            return $this->response($response);
        }

        $categoryId = $this->input->post('categoryId');
        $categoryParentId = empty($this->input->post('categoryParentId')) ? null : $this->input->post('categoryParentId');
        $categoryName = $this->input->post('categoryName');
        $category = [
            'category_name' => $categoryName
        ];

        if(!empty($categoryParentId)){
            $categories = CategoryModel::where('category_id', $categoryId)->with('categories')->get()->toArray();
            $isChild = $this->checkIsChild($categories, $categoryParentId);
            if($isChild == true){
                return $this->response(['status'=>'error', 'message' => 'The item cannot be a subitem of itself.']);
            }
        }

        if(!empty($categoryParentId))
            $category['category_parent_id'] = $categoryParentId;
        if($categoryParentId == 0)
            $category['category_parent_id'] = null;
        if(!empty($categoryId)){
            CategoryModel::where(['category_id' => $categoryId])->update($category);
        } else{
            $newCategory = CategoryModel::create($category);
            if(!empty($newCategory))
                $categoryId = $newCategory->category_id;
        }
        $category = CategoryModel::where('category_id', $categoryId)->first();
        if(!empty($category))
            pushJob('quickbooks/category/synccategoryinqb', serialize(['id' => $categoryId, 'qbId' => $category->category_qb_id]));
        return $this->response($response, 200);
    }
    function ajaxToggleActiveCategory(){
        $categoryId = $this->input->post('category_id');
        if(!empty($categoryId) && $categoryId == 1) {
            return $this->response(['status'=>'error', 'message' => "Can't remove default category!"]);
        }elseif(!empty($categoryId)){
            $category = CategoryModel::where('category_id', $categoryId)->first();
            $childCategory = CategoryModel::where([['category_parent_id', $categoryId], ['category_active', 1]])->first();
            $items = Service::where('service_category_id', $categoryId)->first();
            if(!empty($items) || !empty($childCategory)){
                return $this->response(['status'=>'error', 'message' => 'Category is not empty!']);
            }
            if(!empty($category) && !empty($category->category_parent_id)) {
                $parentCategory = CategoryModel::where([['category_id', $category->category_parent_id], ['category_active', 0]])->first();
                if (!empty($parentCategory))
                    return $this->response(['status'=>'error', 'message' => 'The parent category must be active!']);
            }
            if(!empty($category)){
                $isActive = $category->category_active == 1 ? 0 : 1;
                $category->update(['category_active' => $isActive]);
                pushJob('quickbooks/class/syncclassinqb', serialize(['id' => $categoryId, 'qbId' => $category->category_qb_id]));
            }
            return $this->response(['status'=>'success']);
        }
    }
    function deleteCategory(){
        $categoryId = $this->input->post('categoryId');
        if(!empty($categoryId)){
            $category = CategoryModel::where('category_id', $categoryId)->first();
            $defaultCategory = CategoryModel::first()->category_id;
            $parentCategory = $category->category_parent_id;
            Service::where('service_category_id', $categoryId)->update(['service_category_id' => !empty($parentCategory) ? $parentCategory : $defaultCategory]);
            CategoryModel::where('category_parent_id', $categoryId)->update(['category_parent_id' => !empty($parentCategory) ? $parentCategory : null]);
            CategoryModel::where('category_id', $categoryId)->delete();
            pushJob('quickbooks/category/synccategoryinqb', serialize(['id' => $categoryId, 'qbId' => !empty($category->category_qb_id) ? $category->category_qb_id : null]));
        }
        return $this->response(['status'=>'success']);
    }
    private function checkIsChild(array $categories, int $childId):bool{
        $isChild = false;
        if(!empty($categories)){
            foreach ($categories as $category){
                if(!empty($category['category_id']) && $category['category_id'] == $childId)
                    return true;
                if(!empty($category['categories'])){
                    $isChild =  $this->checkIsChild($category['categories'], $childId);
                }
            }
        }
        return $isChild;
    }
}