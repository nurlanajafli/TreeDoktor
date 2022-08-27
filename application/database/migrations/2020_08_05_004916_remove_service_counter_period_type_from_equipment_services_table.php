<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveServiceCounterPeriodTypeFromEquipmentServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('equipment_services', function (Blueprint $table) {
            $table->dropColumn(['service_counter_period_type']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('equipment_services', function (Blueprint $table) {
            $table->unsignedTinyInteger('service_counter_period_type')->default(1)->after('service_date_period');
        });
    }
}
