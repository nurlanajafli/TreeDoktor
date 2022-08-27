<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use application\modules\estimates\models\Service;
use application\modules\categories\models\Category;
class AddDefaultTreeInventoryService extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $category = Category::all();
        $treeInventoryService = [
            'service_name' => 'Tree inventory',
            'service_category_id' => !empty($category) && !empty($category->first()) ? $category->first()->category_id : null
        ];
        $serviceId = Service::create($treeInventoryService);

        DB::table('settings')->insert([
            'stt_key_name'  =>  'tree_inventory_service_id',
            'stt_key_value' =>  $serviceId->service_id,
            'stt_key_validate'  =>  NULL,
            'stt_section'       =>  'Services',
            'stt_label'         =>  'Try inventory service',
            'stt_is_hidden'     =>  1,
            'stt_html_attrs'    => ''
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Service::where('service_id', config_item('tree_inventory_service_id'))->delete();
        DB::table('settings')->where('stt_key_name', '=', 'tree_inventory_service_id')->delete();
    }
}
