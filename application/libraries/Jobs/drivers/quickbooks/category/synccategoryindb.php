<?php

class synccategoryindb extends CI_Driver implements JobsInterface
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
        if (!$data || !$this->CI->qbclassactions->checkAccessToken())
            return FALSE;
        return $data;
    }

    public function execute($job = NULL)
    {
        if (!$this->CI->qbcategoryactions->settings['stateFromQB'])
            die;
        if ($job) {
            $payload = unserialize($job->job_payload);
            $categoryQB = new $this->CI->qbcategoryactions($payload['qbId']);
            $categoryDB = new $this->CI->categoryactions();
            if(empty($categoryQB->getCategory())){
                $categoryDB->delete($payload['qbId']);
                return true;
            }
//            if($categoryDB->setCategoryByQBId($payload['qbId']) == false){
            $categoryDB->setCategoryByQBId($payload['qbId']);
            $categoryDB->setCategoryByArrayQB($categoryQB->getCategory());
//            }
            if(!empty($categoryQB->getParentId())){
                $parentDB = new $this->CI->categoryactions();
                if($parentDB->setCategoryByQBId($categoryQB->getParentId()) == false){
                    $parentQB = new $this->CI->qbclassactions($categoryQB->getParentId());
                    $parentDB->setCategoryByArrayQB($parentQB->getCategory());
                    $parentId = $parentDB->save();
                    if(!empty($parentId))
                        $categoryDB->setParentCategoryId($parentId);
                } else{
                    $categoryDB->setParentCategoryId($parentDB->getCategoryId());
                }
            }
            $categoryDB->save(true);
            return true;
        }
        return false;
    }
}