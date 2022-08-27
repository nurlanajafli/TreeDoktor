<?php
require_once('QBBase.php');
use QuickBooksOnline\API\Facades\Item;
use QuickBooksOnline\API\QueryFilter\QueryMessage;

class QBCategoryActions extends QBBase
{
    protected $module = 'Item';
    private $category = [];
    private $qbId;

    public function __construct(int $categoryId = null)
    {
        parent::__construct();
        if(!empty($categoryId))
            $this->setCategory($categoryId);
    }

    public function setCategory(int $qbId): bool
    {
        if (!empty($qbId)) {
            $this->setQbId($qbId);
            $category = $this->get($qbId);
            if (!empty($category)) {
                $this->category = [
                    'Type' => $category->Type,
                    'Name' => $category->Name,
                    'ParentRef' => $category->ParentRef,
                    'Id' => $category->Id
                ];
                return true;
            }
        }
        return false;
    }

    public function getCategory(): array
    {
        if (!empty($this->category)) {
            return $this->category;
        }
        return [];
    }

    public function getQBId(){
        if (!empty($this->qbId)) {
            return $this->qbId;
        }
        return false;
    }

    public function getCategories()
    {
        $oneQuery = new QueryMessage();
        $oneQuery->sql = "SELECT";
        $oneQuery->entity = "Item";
        $oneQuery->whereClause = ["Type = 'Category'"];
        $result = $this->customQuery($oneQuery);
        return $result;
    }

    public function getParentId()
    {
        if (!empty($this->getCategory()) && !empty($this->getCategory()['ParentRef'])) {
            return $this->getCategory()['ParentRef'];
        }
        return false;
    }

    public function setQbId($qbId){
        $this->qbId = $qbId;
    }

    public function setClassByArray(array $category): bool
    {
        if (!empty($category)) {
            if(!empty($category['category_qb_id']))
                $this->setCategory($category['category_qb_id']);
            $this->category = [
                'Type' => 'Category',
                'Name' => $category['category_name']
            ];
            if(!empty($category['parent_qb_id'])) {
                $this->category['ParentRef'] = $category['parent_qb_id'];
                $this->category['SubItem'] = true;
            }
            if(!empty($category['category_qb_id']))
                $this->category['Id'] = $category['category_qb_id'];

            return true;
        }
        return false;
    }

    public function save($dbId)
    {
        if(!empty($this->getQBId())){
            $category = $this->get($this->getQBId());
            $categoryForUpdate = Item::update($category, $this->getCategory());
            if(!empty($this->getCategory()))
                updateRecordInQBFromObject($categoryForUpdate, $this->dataService, false, $dbId);
            else
                deleteRecordInQBFromObject($categoryForUpdate, $this->dataService,  $dbId);
        } else{
            $categoryForCreate = Item::create($this->getCategory());
            return createRecordInQBFromObject($categoryForCreate, $this->dataService, false, false, $dbId);
        }
        return null;
    }

    public function create($dbId){
        $categoryForCreate = Item::create($this->getCategory());
        if(!empty($categoryForCreate))
            return createRecordInQBFromObject($categoryForCreate, $this->dataService, false, false, $dbId);
        return false;
    }
    public function update($dbId){
        $category = $this->get($this->getQBId());
        if(!empty($category)) {
            $categoryForUpdate = Item::update($category, $this->getCategory());
            if (!empty($this->getCategory())) {
                updateRecordInQBFromObject($categoryForUpdate, $this->dataService, false, $dbId);
                return true;
            }
        }
        return false;
    }
    public function delete($dbId, $qbId){
        $category = $this->get($qbId);
        if(!empty($category)) {
            return deleteRecordInQBFromObject($category, $this->dataService, $dbId);
        }
        return false;
    }
}