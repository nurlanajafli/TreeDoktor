<?php

namespace application\modules\estimates\models;

use application\core\Database\EloquentModel;
use DB;
class Service extends EloquentModel
{
    // estimate service statuses
    const SERVICE_STATUS_NEW = 0;
    const SERVICE_STATUS_DECLINED = 1;
    const SERVICE_STATUS_COMPLETE = 2;

    const ATTR_SERVICE_ID = 'service_id';
    const ATTR_IS_BUNDLE = 'is_bundle';

    protected $table = 'services';

    protected $primaryKey = 'service_id';

    protected $fillable = [
        'service_name',
        'service_description',
        'service_markup',
        'service_priority',
        'service_status',
        'service_parent_id',
        'service_attachments',
        'service_qb_id',
        'is_product',
        'cost',
        'is_bundle',
        'is_view_in_pdf',
        'service_last_qb_time_log',
        'service_last_qb_sync_result',
        'service_category_id',
    ];

    public $base_fields = [
        'services.service_id',
        'services.service_name',
        'services.is_product',
        'services.is_bundle',
        'services.service_priority'
    ];


    function scopeBaseFields($query)
    {
        $query_string = implode(',', $this->base_fields);
        return $query->select(DB::raw($query_string));
    }

    function scopeActive($query){
        return $query->where('service_status', '=', 1);
    }

    /**
     * @param int $service_status
     * @return array
     */

    public function scopeServices($query){
        $query->where(['service_parent_id' => NULL, 'is_product'=>0, 'is_bundle'=>0]);
    }

    public function scopeBundles($query){
        $query->where('is_bundle', '=', 1);
    }

    public function scopeProducts($query){
        $query->where(['service_parent_id' => NULL, 'is_product' => 1]);
    }

    public static function selectServices2FormatData()
    {
        $services = self::active()->services()->get();
        return $services->mapWithKeys(function ($item, $index) {
            return [
                $index => [
                    'id' => $item['service_id'],
                    'text' => $item['service_name'],
                ]
            ];
        })->toJson();
    }

    public static function selectBundles2FormatData()
    {
        $services = self::active()->bundles()->get();
        return $services->mapWithKeys(function ($item, $index) {
            return [
                $index => [
                    'id' => $item['service_id'],
                    'text' => $item['service_name'],
                ]
            ];
        })->toJson();
    }
    public static function selectProducts2FormatData()
    {
        $services = self::active()->products()->get();
        return $services->mapWithKeys(function ($item, $index) {
            return [
                $index => [
                    'id' => $item['service_id'],
                    'text' => $item['service_name'],
                ]
            ];
        })->toJson();
    }

    public static function getServiceTags($service_status = 1)
    {
        $services = Service::where([
            'service_parent_id' => null,
            'service_status' => $service_status,
        ])->orderBy('service_priority')->get();

        if (empty($services)) {
            return [];
        }

        $serviceTags = [];
        $productTags = [];
        $bundleTags = [];

        foreach($services as $k => $v) {
            if ($v->is_product) {
                $productTags[ $k ][ 'key' ] = $v->service_id;
                $productTags[ $k ][ 'name' ] = $v->service_name;
            } elseif ($v->is_bundle) {
                $bundleTags[ $k ][ 'key' ] = $v->service_id;
                $bundleTags[ $k ][ 'name' ] = $v->service_name;
            } else {
                $serviceTags[ $k ][ 'key' ] = $v->service_id;
                $serviceTags[ $k ][ 'name' ] = $v->service_name;
            }
        }

        return compact('serviceTags', 'productTags', 'bundleTags');
    }
}