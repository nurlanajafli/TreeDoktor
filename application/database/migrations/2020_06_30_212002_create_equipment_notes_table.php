<?php

use application\modules\equipment\models\EquipmentNote;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEquipmentNotesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('equipment_notes', function (Blueprint $table) {
            $table->id('note_id');
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('eq_id')->index();
            $table->unsignedBigInteger('repair_id')->nullable()->index();
            $table->unsignedBigInteger('service_report_id')->nullable()->index();
            $table->text('note_description');
            $table->unsignedTinyInteger('note_type')->default(EquipmentNote::TYPE_SYSTEM);
            $table->dateTime('note_created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('equipment_notes');
    }
}
