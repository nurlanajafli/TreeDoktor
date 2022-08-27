<?php

namespace application\modules\categories\models;

use application\core\Database\EloquentModel;
use application\modules\estimates\models\Service;

class Category extends EloquentModel
{
    protected $table = 'categories';

    protected $primaryKey = 'category_id';

    protected $fillable = [
        'category_name',
        'category_active',
        'category_is_product',
        'category_parent_id',
        'category_qb_id',
        'category_priority'
    ];

    const CREATED_AT = null;

    /**
     * The supported cast types are: integer, real, float, double, decimal:<digits>, string, boolean,
     * object, array, collection, date, datetime, and timestamp.
     * When casting to decimal, you must define the number of digits (decimal:2).
     * @var array
     */
    protected $casts = [
        'category_name' => 'string',
        'category_active' => 'boolean',
        'category_is_product' => 'boolean',
        'category_parent_id' => 'integer',
        'category_qb_id' => 'integer',
    ];

    // Relationships
    public function categoriesWithServices(){
        return $this->hasMany(Category::class, 'category_parent_id')->with(['categoriesWithServices', 'services'])->orderBy('category_active', 'DESC')->orderBy('category_priority', 'ASC');
    }
    public function categoriesWithProducts(){
        return $this->hasMany(Category::class, 'category_parent_id')->with(['categoriesWithProducts', 'products'])->orderBy('category_active', 'DESC')->orderBy('category_priority', 'ASC');
    }
    public function products(){
        return $this->hasMany(Service::class, 'service_category_id')->where('services.is_product', '=', '1')->orderBy('service_status', 'DESC')->orderBy( 'service_priority' , 'ASC');
    }
    public function services(){
        return $this->hasMany(Service::class, 'service_category_id')->where([['services.is_product', '=', '0'], ['services.is_bundle', '=', '0']])->orderBy('service_status', 'DESC')->orderBy( 'service_priority' , 'ASC');
    }
    public function categories(){
        return  $this->hasMany(Category::class, 'category_parent_id')->with('categories')->orderBy('category_active', 'DESC');
    }
    public function items(){
        return $this->hasMany(Service::class, 'service_category_id')->where('services.is_bundle', '=', '0')->orderBy('service_status', 'DESC')->orderBy( 'service_priority' , 'ASC');
    }
}
