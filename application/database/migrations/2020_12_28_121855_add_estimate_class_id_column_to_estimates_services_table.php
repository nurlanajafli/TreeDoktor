<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEstimateClassIdColumnToEstimatesServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('estimates_services', function (Blueprint $table) {
            $table->integer('estimate_service_class_id')->nullable(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('estimates_services', function (Blueprint $table) {
            $table->dropColumn('estimate_service_class_id');
        });
    }
}
