<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTreeInventoryEstimateServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tree_inventory_estimate_services', function (Blueprint $table) {
            $table->increments('ties_id');
            $table->string('ties_number');
            $table->string('ties_type')->nullable();
            $table->string('ties_size')->nullable();
            $table->string('ties_priority')->nullable();
            $table->decimal('ties_stump_cost', 10, 2)->default(0);
            $table->decimal('ties_cost', 10, 2)->default(0);
            $table->integer('ties_estimate_service_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tree_inventory_estimate_services');
    }
}
