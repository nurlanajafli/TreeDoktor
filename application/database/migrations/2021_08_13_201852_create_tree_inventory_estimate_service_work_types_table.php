<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTreeInventoryEstimateServiceWorkTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tree_inventory_estimate_service_work_types', function (Blueprint $table) {
            $table->increments('tieswt_id');
            $table->integer('tieswt_ties_id')->index();
            $table->integer('tieswt_wt_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tree_inventory_estimate_service_work_types');
    }
}
