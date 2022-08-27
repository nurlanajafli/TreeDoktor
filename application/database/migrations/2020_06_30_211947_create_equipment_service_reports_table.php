<?php

use application\modules\equipment\models\EquipmentServiceReport;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEquipmentServiceReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('equipment_service_reports', function (Blueprint $table) {
            $table->id('service_report_id');
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('eq_id')->index();
            $table->unsignedBigInteger('service_type_id')->index();
            $table->unsignedBigInteger('service_id')->index();
            $table->unsignedTinyInteger('service_report_type')->default(EquipmentServiceReport::TYPE_COMPLETED);
            $table->date('service_report_postponed_to')->nullable();
            $table->text('service_report_note');
            $table->dateTime('service_report_created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('equipment_service_reports');
    }
}
