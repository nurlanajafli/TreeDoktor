<?php

class importcategories extends CI_Driver implements JobsInterface
{
    private $CI;
    private $dataService;
    private $classDB;
    private $classQB;
    private $action;
    private $route;
    private $classId;

    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->helper('qb_helper');
        $this->CI->load->model('mdl_clients');
        $this->CI->load->model('mdl_estimates');
        $this->CI->load->library('Common/QuickBooks/QBCategoryActions');
        $this->CI->load->library('Common/CategoryActions');
        $this->dataService = $this->CI->qbcategoryactions->dataService;
        $this->route = 'pull';
    }

    public function getPayload($data = NULL)
    {
        if (!$data || !$this->CI->qbcategoryactions->checkAccessToken())
            return FALSE;
        return $data;
    }

    public function execute($job = NULL)
    {
        if (!$this->CI->qbcategoryactions->settings['stateFromQB'])
            die;
        if ($job) {
            $payload = unserialize($job->job_payload);
            $categories = $this->CI->qbcategoryactions->getCategories();
            if(empty($categories)) {
                $this->checkImport($payload);
                return true;
            }
            elseif (is_array($categories)){
                foreach ($categories as $category){
                    $this->updateOrSaveCategory($category);
                }
            }
            elseif(is_object($categories)){
                $this->updateOrSaveCategory($categories);
            }
            $this->checkImport($payload);
            return true;
        }
        return false;
    }

    private function checkImport($payload){
        if (!empty($payload) && !empty($payload['module']) && $payload['module'] == 'All')
            pushJob('quickbooks/item/importservices', 'Item');
    }

    private function updateOrSaveCategory(object $category){
        $parentCategoryId = null;
        // if has parent
        if(!empty($category->ParentRef)){
            $parentCategory = new $this->CI->categoryactions();
            $parentCategoryResult = $parentCategory->setCategoryByQBId($category->ParentRef);
            if($parentCategoryResult === false){
                $parentCategoryQB = new $this->CI->qbcategoryactions($category->ParentRef);
                if(!empty($parentCategoryQB->getCategory())) {
                    $parentCategory->setCategoryByArrayQB($parentCategoryQB->getCategory());
                    $parentCategoryId = $parentCategory->save();
                }
            } else {
                $parentCategoryId = $parentCategory->getCategoryId();
            }
        }
        $DBCategory = new $this->CI->categoryactions();
        $DBCategoryResult = $DBCategory->setCategoryByQBId($category->Id);
        if($DBCategoryResult === false){
            $DBCategory->setCategoryByArrayQB((array)$category, $parentCategoryId);
        } elseif(!empty($parentCategoryId)){
            $DBCategory->setParentCategoryId($parentCategoryId);
        }
        $DBCategory->save();
    }

}