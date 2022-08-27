<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DefaultFavouriteIconsPatch extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $services = DB::table('services')->where([['is_bundle', 0],['is_product', 0]])->get()->toArray();
        foreach ($services as $service){
            $data = [
                'service_is_favourite' => 1
            ];
            $service_name = trim($service->service_name);
            if($service_name == 'Tree Removal')
                $data['service_favourite_icon'] = 'tree-removal';
            elseif ($service_name == 'Deep Root Fertilizing')
                $data['service_favourite_icon'] = 'root-fertilizing';
            elseif ($service_name == 'Planting Service')
                $data['service_favourite_icon'] = 'planting-service';
            elseif ($service_name == 'Pruning')
                $data['service_favourite_icon'] = 'pruning-service';
            elseif ($service_name == 'Stump Grinding')
                $data['service_favourite_icon'] = 'stump-grinding';
            elseif ($service_name == 'Christmas Tree/Lights installation')
                $data['service_favourite_icon'] = 'christmas-tree';
            elseif ($service_name == 'General Arborist Report - Toronto')
                $data['service_favourite_icon'] = 'arborist-report';

            if(!empty($data['service_favourite_icon']))
                DB::table('services')->where('service_id', $service->service_id)->update($data);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $data = [
            'service_is_favourite' => 0,
            'service_favourite_icon' => null
        ];
        DB::table('services')->update($data);
    }
}
