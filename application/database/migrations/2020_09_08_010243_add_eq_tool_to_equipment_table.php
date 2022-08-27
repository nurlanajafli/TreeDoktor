<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEqToolToEquipmentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('equipment', function (Blueprint $table) {
            $table->boolean('eq_schedule_tool')->default(false)->after('eq_schedule');
        });
        DB::table('equipment')
            ->leftJoin(
                'equipment_groups',
                'equipment.group_id',
                '=',
                'equipment_groups.group_id')
            ->where('equipment_groups.group_name', 'LIKE', 'Tools')->update(['eq_schedule_tool' => true]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('equipment', function (Blueprint $table) {
            $table->dropColumn('eq_schedule_tool');
        });
    }
}
