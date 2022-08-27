<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateClientEstimateBrandId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $default_brand = DB::table('brands')->where(['b_is_default'=>1])->whereNull('deleted_at')->first();

        if($default_brand){
            DB::table('clients')->where('client_brand_id', 0)->update(['client_brand_id'=>$default_brand->b_id]);
            DB::table('estimates')->where('estimate_brand_id', 0)->update(['estimate_brand_id'=>$default_brand->b_id]);
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
