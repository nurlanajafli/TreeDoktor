<?php

use application\modules\equipment\models\EquipmentRepair;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEquipmentRepairsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('equipment_repairs', function (Blueprint $table) {
            $table->id('repair_id');
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('eq_id')->index();
            $table->unsignedBigInteger('assigned_id')->nullable()->index();
            $table->unsignedBigInteger('repair_status_id')->index();
            $table->unsignedBigInteger('repair_type_id')->index();
            $table->unsignedTinyInteger('repair_priority')->default(EquipmentRepair::PRIORITY_GENERAL);
            $table->text('repair_description')->nullable();
            $table->dateTime('repair_end_at')->nullable();
            $table->text('repair_end_note')->nullable();
            $table->dateTime('repair_created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('equipment_repairs');
    }
}
