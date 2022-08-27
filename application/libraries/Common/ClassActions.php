<?php
use application\modules\classes\models\QBClass;

class ClassActions
{
    protected $CI;
    protected $classId;
    protected $class;

    function __construct($classId = NULL)
    {
        $this->CI =& get_instance();
        if (!empty($classId))
            $this->setClassById($classId);
    }

    public function setClassById($classId): bool
    {
        if (!empty($classId)) {
            $this->classId = $classId;
            $class = QBClass::where('class_id', $classId)->first();
            if (!empty($class)) {
                $this->class = $class->toArray();
                return true;
            }
        }
        $this->reset();
        return false;
    }

    public function setClassByQBId($classQBId)
    {
        if (!empty($classQBId)) {
            $result = QBClass::where('class_qb_id', $classQBId)->first();
            if (!empty($result)) {
                $this->class = $result->toArray();
                $this->classId = $this->class['class_id'];
                return true;
            }
        }
        $this->reset();
        return false;
    }

    /**
     * @return mixed
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @return mixed
     */
    public function getClassId()
    {
        return $this->classId;
    }

    /**
     * @return mixed
     */
    public function getParentClassId()
    {
        if (!empty($this->getClass()))
            return $this->getClass()['class_parent_id'];
        return null;
    }

    /**
     * @return mixed
     */
    public function getParentClassQBId()
    {
        if (!empty($this->getParentClassId())) {
            $result = QBClass::where('class_id', $this->getParentClassId())->first();
            if (!empty($result)) {
                return $result->toArray()['class_qb_id'];
            }
        }
        return null;
    }

    public function setParentClassQBId(int $parentQBId)
    {
        if (!empty($this->getClass()))
            $this->class['parent_qb_id'] = $parentQBId;
    }

    public function setParentClassId(int $classId)
    {
        if (!empty($this->getClass()))
            $this->class['class_parent_id'] = $classId;
    }

    public function setClassByArrayQB(array $class, $parentId = null): bool
    {
        if (!empty($class)) {
            $classForDB = [
                'class_name' => $class['Name'],
                'class_active' => $class['Active'] == true ? 1 : 0,
                'class_qb_id' => $class['Id'],
            ];
            if(!empty($parentId))
                $classForDB['class_parent_id'] = $parentId;
            $this->class = $classForDB;
            return true;
        }
        $this->reset();
        return false;
    }

    public function setQBId($QBId)
    {
        if (!empty($QBId) && !empty($this->getClass())) {
            $this->class['class_qb_id'] = $QBId;
            return true;
        }
        return false;
    }

    public function save($createLog = false)
    {
        if (!empty($this->getClass())) {
            unset($this->class['parent_qb_id']);
            if (!empty($this->getClassId())) {
                QBClass::where(['class_id' => $this->getClassId()])->update($this->getClass());
                $action = 'update';
                $Id = $this->getClassId();
            } else {
                $action = 'create';
                $Id = QBClass::create($this->getClass())->class_id;
            }
            createQBLog('class', $action, 'pull', $Id);
            $this->reset();
            return $Id;
        }
        $this->reset();
        $message = 'Empty class ';
        if ($createLog == true)
            createQBLog('class', 'get', 'pull', 0, $message);
        return false;
    }

    public function reset()
    {
        $this->class = [];
        $this->classId = null;
    }
}