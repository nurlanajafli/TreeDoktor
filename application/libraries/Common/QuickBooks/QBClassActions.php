<?php
require_once('QBBase.php');
use QuickBooksOnline\API\Facades\QuickBookClass;
use application\modules\classes\models\QBClass;

class QBClassActions extends QBBase
{
    protected $module = 'Class';
    private $class = [];

    public function __construct(int $classId = null)
    {
        parent::__construct();
        if(!empty($classId))
            $this->setClass($classId);
    }

    public function setClass(int $qbId): bool
    {
        if (!empty($qbId)) {
            $class = $this->get($qbId);
            if (!empty($class)) {
                $this->class = [
                    'Name' => $class->Name,
                    'ParentRef' => $class->ParentRef,
                    'Active' => $class->Active == 'false' ? false : true,
                    'Id' => $class->Id
                ];
                return true;
            }
        }
        return false;
    }

    public function getClass(): array
    {
        if (!empty($this->class)) {
            return $this->class;
        }
        return [];
    }

    public function getQBId(){
        if (!empty($this->getClass()) && !empty($this->getClass()['Id'])) {
            return $this->getClass()['Id'];
        }
        return false;
    }

    public function isActive(){
        if (!empty($this->getClass()) && !empty($this->getClass()['Active'])) {
            return $this->getClass()['Active'];
        }
        return false;
    }

    public function getClasses()
    {
        return $this->getAll('Class');
    }

    public function getParentId()
    {
        if (!empty($this->getClass()) && !empty($this->getClass()['ParentRef'])) {
            return $this->getClass()['ParentRef'];
        }
        return false;
    }

    public function setClassByArray(array $class): bool
    {
        if (!empty($class)) {
            if(!empty($class['class_qb_id']))
                $this->setClass($class['class_qb_id']);
            $this->class = [
                'Name' => $class['class_name'],
                'Active' => empty($class['class_active']) ? false : true
            ];
            if(!empty($class['parent_qb_id']))
                $this->class['ParentRef'] = $class['parent_qb_id'];
            if(!empty($class['class_qb_id']))
                $this->class['Id'] = $class['class_qb_id'];
            return true;
        }
        return false;
    }

    public function save($dbId)
    {
        if(!empty($this->getClass()) && !empty($this->getQBId())){
            $class = $this->get($this->getQBId());
            if($this->isActive()) {
                $classForUpdate = QuickBookClass::update($class, $this->getClass());
                updateRecordInQBFromObject($classForUpdate, $this->dataService, false, $dbId);
            }
            else {
                $classWithChild = $this->getArrayQBIdForMakeInactive($dbId);
                krsort($classWithChild);
                foreach ($classWithChild as $item){
                    if(!empty($item)){
                        $class = $this->get($item);
                        $classForUpdate = QuickBookClass::update( $class, ['Active' => false]);
                        updateRecordInQBFromObject($classForUpdate, $this->dataService, false, $dbId);
                    }
                }
            }
        } else{
            $classForCreate = QuickBookClass::create($this->getClass());
            return createRecordInQBFromObject($classForCreate, $this->dataService, false, false, $dbId);
        }
        return null;
    }

    public function getArrayQBIdForMakeInactive($dbId): array{
        $class = QBClass::where('class_id', $dbId)->first();
        $result = [];
        if(!empty($class)){
            $result[] = $class->class_qb_id;
            $parentClasses = QBClass::where('class_parent_id', $dbId)->get();
            if(!empty($parentClasses)){
                foreach ($parentClasses as $parentClass) {
                    $resp = $this->getArrayQBIdForMakeInactive($parentClass->class_id);
                    $result = array_merge($result, $resp);
                }
            }
        }
        return $result;
    }
}