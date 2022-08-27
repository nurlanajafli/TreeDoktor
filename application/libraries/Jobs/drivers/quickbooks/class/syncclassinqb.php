<?php

class syncclassinqb extends CI_Driver implements JobsInterface
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
            $class = new $this->CI->classactions($payload['id']);
            if (empty($class->getClass()))
                return false;
            if (!empty($class->getParentClassId())) {
                if (!empty($class->getParentClassQBId())) {
                    $class->setParentClassQBId($class->getParentClassQBId());
                } else {
                    $parentClass = new $this->CI->classactions($class->getParentCategoryId());
                    if (!empty($parentClass->getClassId())) {
                        $parentQBId = $this->createOrUpdateClass($parentClass);
                        $class->setParentClassQBId($parentQBId);
                    }
                }
            }
            $resultQB = $this->createOrUpdateClass($class);
            if ($resultQB == false)
                return false;
            return true;
        }
        return false;
    }

    private function createOrUpdateClass(ClassActions $class)
    {
        $resultQB = $this->CI->qbclassactions->setClassByArray($class->getClass());
        if ($resultQB == true) {
            $qbId = $this->CI->qbclassactions->save($class->getClassId());
            if ($qbId == 'AuthenticationFailed' || $qbId == 'AuthorizationFailed')
                return FALSE;
            elseif (!empty($qbId)) {
                $result = $class->setQBId($qbId);
                if ($result == true) {
                    $class->save();
                    return $qbId;
                }
            }
            return true;
        }
        return false;
    }
}