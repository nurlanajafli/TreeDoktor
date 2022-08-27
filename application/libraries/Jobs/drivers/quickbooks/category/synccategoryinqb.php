<?php

class synccategoryinqb extends CI_Driver implements JobsInterface
{
    private $CI;
    private $dataService;

    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->helper('qb_helper');
        $this->CI->load->library('Common/QuickBooks/QBCategoryActions');
        $this->CI->load->library('Common/CategoryActions');
        $this->dataService = $this->CI->qbcategoryactions->dataService;
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
            $category = new $this->CI->categoryactions($payload['id']);
            if (empty($category->getCategory()) && !empty($payload['qbId'])) {
                $result = $this->CI->qbcategoryactions->delete($payload['id'], $payload['qbId']);
                return empty($result) ? false : true;
            }
            if (!empty($category->getParentCategoryId())) {
                if (!empty($category->getParentCategoryQBId())) {
                    $category->setParentCategoryQBId($category->getParentCategoryQBId());
                } else {
                    $parentCategory = new $this->CI->categoryactions($category->getParentCategoryId());
                    if (!empty($parentCategory->getCategoryId())) {
                        $parentQBId = $this->createOrUpdateClass($parentCategory);
                        $category->setParentCategoryQBId($parentQBId);
                    }
                }
            }
            $resultQB = $this->createOrUpdateClass($category);
            if ($resultQB == false)
                return false;
            return true;
        }
        return false;
    }

    private function createOrUpdateClass(CategoryActions $category)
    {
        $categoryQB = new $this->CI->qbcategoryactions();
        if(!empty($category->getCategory())){
            $resultQB = $categoryQB->setClassByArray($category->getCategory());
            if ($resultQB == true) {
                if ($categoryQB->getQBId()) {
                    $categoryQB->update($category->getCategoryId());
                    return true;
                } else {
                    $qbId = $categoryQB->create($category->getCategoryId());
                    if ($qbId == 'AuthenticationFailed' || $qbId == 'AuthorizationFailed')
                        return FALSE;
                    elseif (!empty($qbId)) {
                        $result = $category->setQBId($qbId);
                        if ($result == true) {
                            $category->save();
                            return $qbId;
                        }
                    }
                }
            }
        }
        return false;
    }
}