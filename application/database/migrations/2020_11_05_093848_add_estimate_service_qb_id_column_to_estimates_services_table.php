<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEstimateServiceQbIdColumnToEstimatesServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('estimates_services', function (Blueprint $table) {
            $table->integer('estimate_service_qb_id')->nullable();
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
            $table->dropColumn('estimate_service_qb_id');
        });
    }
}
