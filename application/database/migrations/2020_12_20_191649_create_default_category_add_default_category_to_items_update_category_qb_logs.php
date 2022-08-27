<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDefaultCategoryAddDefaultCategoryToItemsUpdateCategoryQbLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // create default category
        DB::table('categories')->insert([
            'category_name' => 'No category',
            'category_active' => 1
        ]);
        $categoryId = DB::table('categories')->where(['category_name' => 'No category'])->first()->category_id;

        // set default category for items
        DB::table('services')->where('service_category_id', '=', null)->update(['service_category_id' => $categoryId]);

        // update categories qb logs
        $fields = DB::table('categories')->get();
        foreach ($fields as $field) {
            if(!empty($field->category_qb_id) && $field->category_qb_id > 0) {
                DB::table('categories')->where(['category_id' => $field->category_id])->update([
                    'category_last_qb_sync_result' => 1
                ]);
            } elseif ($field->category_qb_id !== NULL){
                DB::table('categories')->where(['category_id' => $field->category_id])->update([
                    'category_last_qb_sync_result' => 2
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
}
