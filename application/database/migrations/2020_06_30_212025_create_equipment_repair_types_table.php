<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEquipmentRepairTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('equipment_repair_types', function (Blueprint $table) {
            $table->id('repair_type_id');
            $table->string('repair_type_name');
        });

        DB::table('equipment_repair_types')->insert(['repair_type_name' => 'Damage']);
        DB::table('equipment_repair_types')->insert(['repair_type_name' => 'Repair']);
        DB::table('equipment_repair_types')->insert(['repair_type_name' => 'Maintenance']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('equipment_repair_types');
    }
}
