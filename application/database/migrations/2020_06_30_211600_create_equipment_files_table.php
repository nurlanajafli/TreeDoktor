<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEquipmentFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('equipment_files');
        Schema::create('equipment_files', function (Blueprint $table) {
            $table->id('file_id');
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('eq_id')->index();
            $table->unsignedBigInteger('repair_id')->nullable()->index();
            $table->unsignedBigInteger('service_report_id')->nullable()->index();
            $table->unsignedBigInteger('part_id')->nullable()->index();
            $table->string('file_name');
            $table->dateTime('file_created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('equipment_files');
        Schema::create('equipment_files', function (Blueprint $table) {
            $table->id('file_id');
            $table->integer('file_eq_item_id');
            $table->string('file_name');
            $table->string('file_path');
            $table->date('file_exp')->nullable();
            $table->tinyInteger('file_notification')->default(0);
            $table->integer('file_notification_user')->nullable();
        });
    }
}
