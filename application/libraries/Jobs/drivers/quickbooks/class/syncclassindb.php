<?php

class syncclassindb extends CI_Driver implements JobsInterface
{
    private $CI;
    private $dataService;

    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->helper('qb_helper');
        $this->CI->load->library('Common/QuickBooks/QBClassActions');
        $this->CI->load->library('Common/ClassActions');
        $this->dataService = $this->CI->qbclassactions->dataService;
    }
    public function getPayload($data = NULL)
    {
        if (!$data || !$this->CI->qbclassactions->checkAccessToken())
            return FALSE;
        return $data;
    }

    public function execute($job = NULL)
    {
        if (!$this->CI->qbclassactions->settings['stateFromQB'])
            die;
        if ($job) {
            $payload = unserialize($job->job_payload);
            $classQB = new $this->CI->qbclassactions($payload['qbId']);
            $classDB = new $this->CI->classactions();
//            if($classDB->setClassByQBId($payload['qbId']) == false){
                $classDB->setClassByQBId($payload['qbId']);
                $classDB->setClassByArrayQB($classQB->getClass());
//            }
            if(!empty($classQB->getParentId())){
                $parentDB = new $this->CI->classactions();
                if($parentDB->setClassByQBId($classQB->getParentId()) == false){
                    $parentQB = new $this->CI->qbclassactions($classQB->getParentId());
                    $parentDB->setClassByArrayQB($parentQB->getClass());
                    $parentId = $parentDB->save();
                    if(!empty($parentId))
                        $classDB->setParentClassId($parentId);
                } else{
                    $classDB->setParentClassId($parentDB->getClassId());
                }
            }
            $classDB->save(true);
            return true;
        }
        return false;
    }
}