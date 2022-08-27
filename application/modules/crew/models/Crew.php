<?php
namespace application\modules\crew\models;
use application\core\Database\EloquentModel;

use DB;
class Crew extends EloquentModel
{
    protected $table = 'crews';
    protected $primaryKey = 'crew_id';

    const ATTR_CREW_ID = 'crew_id';
    const ATTR_CREW_NAME = 'crew_name';
    const ATTR_CREW_COLOR = 'crew_color';
    const ATTR_CREW_STATUS = 'crew_status';
    const ATTR_CREW_LEADER = 'crew_leader';
    const ATTR_CREW_WEIGHT = 'crew_weight';
    const ATTR_CREW_FULL_NAME = 'crew_full_name';
    const ATTR_CREW_RATE = 'crew_rate';
    const ATTR_CREW_PRIORITY = 'crew_priority';
    const ATTR_CREW_RETURN_PRIORITY = 'crew_return_priority';

    const API_FIELDS = [
        'crews.crew_id',
        'crews.crew_name',
        'crews.crew_color'
    ];

    public $base_fields = [
        'crews.crew_id',
        'crews.crew_name',
    ];

    /**
     * @param $query
     * @return mixed
     */
    function scopeBaseFields($query)
    {
        $query_string = implode(',', $this->base_fields);
        return $query->select(DB::raw($query_string));
    }

    public function scopeActive($query){
        return $query->where('crew_status', '=', 1);
    }

    public function scopeNoDayOff($query){
        return $query->where('crew_id', '>', 0);
    }

    public function scopeApiFields($query)
    {
        return $query->select(static::API_FIELDS);
    }

    public static function select2FormatData()
    {
        $crews = self::active()->noDayOff()->get();
        return $crews->mapWithKeys(function ($item, $index) {
            return [
                $index => [
                    'id' => $item['crew_id'],
                    'text' => $item['crew_name'],
                ]
            ];
        })->toJson();
    }
}