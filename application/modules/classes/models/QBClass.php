<?php
namespace application\modules\classes\models;

use application\core\Database\EloquentModel;

class QBClass extends EloquentModel
{
    const ATTR_CLASS_ID = 'class_id';
    protected $table = 'classes';

    protected $primaryKey = 'class_id';

    protected $fillable = [
        'class_name',
        'class_active',
        'class_parent_id',
        'class_qb_id'
    ];

    /**
     * The supported cast types are: integer, real, float, double, decimal:<digits>, string, boolean,
     * object, array, collection, date, datetime, and timestamp.
     * When casting to decimal, you must define the number of digits (decimal:2).
     * @var array
     */
    protected $casts = [
        'class_name' => 'string',
        'class_active' => 'boolean',
        'class_parent_id' => 'integer',
        'class_qb_id' => 'integer',
    ];

    // Relationships
    public function classes(){
        return  $this->hasMany(QBClass::class, 'class_parent_id')->with('classes')->orderBy('class_active', 'DESC');
    }
    public function classesWithoutInactive(){
        return  $this->hasMany(QBClass::class, 'class_parent_id')->where(['class_active' => 1])->with('classesWithoutInactive')->orderBy('class_id', 'DESC');
    }

    /**
     * @param $query
     * @return mixed
     */
    function scopeApiFields($query)
    {
        return $query->select([
            'class_name',
        ]);
    }
}