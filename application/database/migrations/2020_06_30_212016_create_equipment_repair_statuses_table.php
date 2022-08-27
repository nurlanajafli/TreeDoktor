<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEquipmentRepairStatusesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('equipment_repair_statuses', function (Blueprint $table) {
            $table->id('repair_status_id');
            $table->string('repair_status_name');
            $table->boolean('repair_status_flag_default')->default(false);
            $table->boolean('repair_status_flag_in_progress')->default(false);
            $table->boolean('repair_status_flag_completed')->default(false);
            $table->string('repair_status_color', 7)->nullable();
        });
        DB::table('equipment_repair_statuses')->insert([
            'repair_status_name' => 'Queued',
            'repair_status_flag_default' => true
        ]);
        DB::table('equipment_repair_statuses')->insert([
            'repair_status_name' => 'In Progress',
            'repair_status_flag_in_progress' => true
        ]);
        DB::table('equipment_repair_statuses')->insert(['repair_status_name' => 'On Hold']);
        DB::table('equipment_repair_statuses')->insert([
            'repair_status_name' => 'Finished',
            'repair_status_flag_completed' => true
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('equipment_repair_statuses');
    }
}
