<?php

class importclasses extends CI_Driver implements JobsInterface
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
        $this->CI->load->library('Common/QuickBooks/QBClassActions');
//        $this->CI->load->library('Common/CategoryActions');
        $this->CI->load->library('Common/ClassActions');
        $this->dataService = $this->CI->qbclassactions->dataService;
        $this->route = 'pull';
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
            $classes = $this->CI->qbclassactions->getClasses();
            if (!empty($classes) && is_array($classes)){
                foreach ($classes as $class){
                    $this->updateOrSaveClass($class);
                }
            }
            elseif(!empty($classes) && is_object($classes)){
                $this->updateOrSaveClass($classes);
            }
            if (!empty($payload) && !empty($payload['module']) && $payload['module'] == 'All')
                pushJob('quickbooks/category/importcategories', serialize(['module' => 'All']));
            return true;
        }
        return false;
    }
    private function updateOrSaveClass(object $class){
        $parentClassId = null;
        // if has parent
        if(!empty($class->ParentRef)){
            $parentClass = new $this->CI->classactions();
            $parentClassResult = $parentClass->setClassByQBId($class->ParentRef);
            if($parentClassResult === false){
                $parentClassQB = new $this->CI->qbclassactions($class->ParentRef);
                if(!empty($parentClassQB->getClass())) {
                    $parentClass->setClassByArrayQB($parentClassQB->getClass());
                    $parentClassId = $parentClass->save();
                }
            } else {
                $parentClassId = $parentClass->getClassId();
            }
        }
        $DBClass = new $this->CI->classactions();
        $DBClassResult = $DBClass->setClassByQBId($class->Id);
        if($DBClassResult === false){
            $DBClass->setClassByArrayQB((array)$class, $parentClassId);
        } elseif(!empty($parentClassId)){
            $DBClass->setParentClassId($parentClassId);
        }
        $DBClass->save();
    }
}