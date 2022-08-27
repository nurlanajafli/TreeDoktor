<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRepairEstHoursToEquipmentRepairsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('equipment_repairs', function (Blueprint $table) {
            $table->double('repair_est_hours')->nullable()->after('repair_priority');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('equipment_repairs', function (Blueprint $table) {
            $table->dropColumn(['repair_est_hours']);
        });
    }
}
