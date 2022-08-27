<?php

use application\modules\categories\models\Category;
use application\modules\estimates\models\Service;

class CategoryActions
{
    protected $CI;
    protected $categoryId;
    protected $category;

    function __construct($categoryId = NULL)
    {
        $this->CI =& get_instance();
        if (!empty($categoryId))
            $this->setCategoryById($categoryId);
    }

    public function setCategoryById($categoryId): bool
    {
        if (!empty($categoryId)) {
            $this->categoryId = $categoryId;
            $category = Category::where('category_id', $categoryId)->first();
            if (!empty($category)) {
                $this->category = $category->toArray();
                return true;
            }
        }
        $this->reset();
        return false;
    }

    public function setCategoryByQBId($categoryQBId)
    {
        if (!empty($categoryQBId)) {
            $result = Category::where('category_qb_id', $categoryQBId)->first();
            if (!empty($result)) {
                $this->category = $result->toArray();
                $this->categoryId = $this->category['category_id'];
                return true;
            }
        }
        $this->reset();
        return false;
    }

    /**
     * @return mixed
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @return mixed
     */
    public function getCategoryId()
    {
        return $this->categoryId;
    }

    /**
     * @return mixed
     */
    public function getParentCategoryId()
    {
        if (!empty($this->getCategory()))
            return $this->getCategory()['category_parent_id'];
        return null;
    }

    /**
     * @return mixed
     */
    public function getParentCategoryQBId()
    {
        if (!empty($this->getParentCategoryId())) {
            $result = Category::where('category_id', $this->getParentCategoryId())->first();
            if (!empty($result)) {
                return $result->toArray()['category_qb_id'];
            }
        }
        return null;
    }

    public function setParentCategoryQBId(int $parentQBId)
    {
        if (!empty($this->getCategory()))
            $this->category['parent_qb_id'] = $parentQBId;
    }

    public function setParentCategoryId(int $categoryId)
    {
        if (!empty($this->getCategory()))
            $this->category['category_parent_id'] = $categoryId;
    }

    public function setCategoryByArrayQB(array $category, $parentId = null): bool
    {
        if (!empty($category)) {
            $categoryForDB = [
                'category_name' => $category['Name'],
                'category_qb_id' => $category['Id'],
            ];
            if(!empty($parentId))
                $categoryForDB['category_parent_id'] = $parentId;
            $this->category = $categoryForDB;
            return true;
        }
        $this->reset();
        return false;
    }

    public function setQBId($QBId)
    {
        if (!empty($QBId) && !empty($this->getCategory())) {
            $this->category['category_qb_id'] = $QBId;
            return true;
        }
        return false;
    }

    public function save($createLog = false)
    {
        if (!empty($this->getCategory())) {
            unset($this->category['parent_qb_id']);
            if (!empty($this->getCategoryId())) {
                unset($this->category['category_last_qb_sync_result']);
                unset($this->category['category_last_qb_time_log']);
                Category::where(['category_id' => $this->getCategoryId()])->update($this->getCategory());
                $action = 'update';
                $Id = $this->getCategoryId();
            } else {
                $action = 'create';
                $Id = Category::create($this->getCategory())->category_id;
            }
            createQBLog('category', $action, 'pull', $Id);
            return $Id;
        }
        $this->reset();
        $message = 'Empty category ';
        if ($createLog == true)
            createQBLog('category', 'get', 'pull', 0, $message);
        return false;
    }

    public function delete($qbId){
        if(!empty($qbId)){
            $category = Category::where('category_qb_id', $qbId)->first();
            if(!empty($category)){
                $defaultCategory = Category::first()->category_id;
                $parentCategory = $category->category_parent_id;
                Service::where('service_category_id', $category->category_id)->update(['service_category_id' => !empty($parentCategory) ? $parentCategory : $defaultCategory]);
                Category::where('category_parent_id', $category->category_id)->update(['category_parent_id' => !empty($parentCategory) ? $parentCategory : null]);
            }
            Category::where('category_qb_id', $qbId)->delete();
        }
    }

    public function reset()
    {
        $this->category = [];
        $this->categoryId = null;
    }
}