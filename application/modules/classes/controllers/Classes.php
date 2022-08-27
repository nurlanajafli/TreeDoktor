<?php
use application\modules\estimates\models\Service;
use application\modules\classes\models\QBClass;
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Classes extends MX_Controller
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
    function ajaxSaveClass(){
        $response = ['status'=>'ok'];
        $classId = $this->input->post('classId');
        $parentId = !empty($this->input->post('parentId')) ? $this->input->post('parentId') : null;
        $className = $this->input->post('className');
        $isActive = $this->input->post('isActive');
        $data = [];
        if(!empty($parentId) && $isActive == 1) {
            $cntChild = 0;
            $cntParent = $this->getCountParent($parentId);
            if(!empty($classId)){
                $classes = QBClass::where('class_id', $classId)->with('classes')->get()->toArray();
                $isChild = $this->checkIsChild($classes, $parentId);
                if($isChild == true){
                    $this->response(['status'=>'error', 'message' => 'The item cannot be a subitem of itself.']);
                    return;
                }
                $cntChild = $this->getCountChildren($classes);
            }
            if($cntParent >= 4 || $cntParent + $cntChild > 4){
                $this->response(['status'=>'error', 'message' => 'Nesting is limited to 5 levels deep.']);
                return;
            }
        }
        if($isActive == 0){
            $class = QBClass::where('class_id', $classId)->first();
            $classes = QBClass::where('class_id', $classId)->with('classes')->get()->toArray();
            $this->makeInactiveClass($classes);
        }
        elseif(empty($classId)){
            $class = QBClass::create([
                'class_name' => $className,
                'class_active' => $isActive,
                'class_parent_id' => $parentId
            ]);
            if(!empty($class))
                $classId = $class->class_id;
        } else{
            $class = QBClass::where('class_id', $classId)->first();
            if(!empty($class) && $class->class_active == 0 && !empty($parentId)){
                $parentClass = QBClass::where('class_id', $parentId)->first();
                if(!empty($parentClass) && $parentClass->class_active == 0){
                    $this->response(['status'=>'error', 'message' => 'Please make the parent class active first.']);
                    return;
                }
            }
            QBClass::where('class_id', $classId)->update([
                'class_name' => $className,
                'class_active' => $isActive,
                'class_parent_id' => $parentId
            ]);
        }
        $classes = QBClass::whereNull('class_parent_id')->with('classes')->get();
        if(!empty($classes)) {
            $classesForSelect2 = getClasses($classes->toArray());
        }
        if(!empty($classesForSelect2))
            $data['classes'] = $classesForSelect2;
        $classesForDrop = QBClass::where([['class_parent_id', null], 'class_active' => 1])->with('classesWithoutInactive')->get();
        if(!empty($classesForDrop)) {
            $classesForDropSelect2 = getClasses($classesForDrop->toArray());
        }
        if(!empty($classesForDropSelect2))
            $data['classesForParent'] = $classesForDropSelect2;
        $response['data'] = $data;
        if(!empty($class))
            pushJob('quickbooks/class/syncclassinqb', serialize(['id' => $classId, 'qbId' => !empty($class->class_qb_id) ? $class->class_qb_id : null]));
        $this->response($response);
    }
    private function makeInactiveClass(array $classes){
        foreach ($classes as $class){
            if(isset($class['class_id'])) {
                QBClass::where('class_id', $class['class_id'])->update(['class_active' => 0]);
                if(!empty($class['classes']))
                    $this->makeInactiveClass($class['classes']);
            }
        }
    }
    private function getCountParent(int $parentId, bool $isParent = false, int $cnt = 0):int{
        if ($isParent == true) {
            $cnt++;
            if ($cnt == 4) {
                return $cnt;
            }
        }
        if(!empty($parentId)){
            $parent = QBClass::where('class_id', $parentId)->first();
            if(!empty($parent) && !empty($parent->class_parent_id)){
                $cnt = $this->getCountParent($parent->class_parent_id, true, $cnt);
                if($cnt >= 4)
                    return $cnt;
            }
        }
        return isset($cnt) ? $cnt : 0;
    }
    private function getCountChildren(array $classes, bool $isChildren = false, int $cnt = 1):int{
        if ($isChildren == true) {
            $cnt++;
            if ($cnt == 4) {
                return $cnt;
            }
        }
        if(!empty($classes)){
            foreach ($classes as $class){
                if(!empty($class['classes'])){
                    $cnt =  $this->getCountChildren($class['classes'], true, $cnt);
                }
            }
        }
        return isset($cnt) ? $cnt : 1;
    }
    private function checkIsChild(array $classes, int $childId):bool{
        $isChild = false;
        if(!empty($classes)){
            foreach ($classes as $class){
                if(!empty($class['class_id']) && $class['class_id'] == $childId)
                    return true;
                if(!empty($class['classes'])){
                    $isChild =  $this->checkIsChild($class['classes'], $childId);
                }
            }
        }
        return $isChild;
    }
}