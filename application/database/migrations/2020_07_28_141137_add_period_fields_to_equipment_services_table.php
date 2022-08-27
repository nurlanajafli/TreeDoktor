<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPeriodFieldsToEquipmentServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('equipment_services', function (Blueprint $table) {

            $table->unsignedInteger('service_date_period')->nullable()->after('service_period_type');
            $table->unsignedTinyInteger('service_counter_period_type')->default(1)->after('service_date_period');
            $table->unsignedInteger('service_counter_period')->nullable()->after('service_counter_period_type');
            $table->renameColumn('service_period_type', 'service_date_period_type');
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
            $table->dropColumn(['service_counter_period', 'service_counter_period_type', 'service_date_period']);
            $table->renameColumn('service_date_period_type', 'service_period_type');
        });
    }
}
