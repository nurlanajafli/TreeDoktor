<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNextCounterFieldToEquipmentServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('equipment_services', function (Blueprint $table) {
            $table->unsignedInteger('service_next_counter')->nullable()->after('service_next_date');
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
            $table->dropColumn(['service_next_counter']);
        });
    }
}
