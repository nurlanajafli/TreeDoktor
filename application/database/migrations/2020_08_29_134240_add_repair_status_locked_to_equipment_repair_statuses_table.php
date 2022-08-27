<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRepairStatusLockedToEquipmentRepairStatusesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('equipment_repair_statuses', function (Blueprint $table) {
            $table->boolean('repair_status_locked')->default(0)->after('repair_status_color');
        });
        DB::table('equipment_repair_statuses')->update(['repair_status_locked' => 1]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('equipment_repair_statuses', function (Blueprint $table) {
            $table->dropColumn(['repair_status_locked']);
        });
    }
}
