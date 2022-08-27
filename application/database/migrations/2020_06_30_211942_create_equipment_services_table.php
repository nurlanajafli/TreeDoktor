<?php

use application\modules\equipment\models\EquipmentService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEquipmentServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::rename('equipment_services', 'equipment_services_old');
        Schema::create('equipment_services', function (Blueprint $table) {
            $table->id('service_id');
            $table->unsignedBigInteger('service_type_id')->index();
            $table->unsignedBigInteger('eq_id')->index();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('service_name');
            $table->text('service_description')->nullable();
            $table->unsignedTinyInteger('service_period_type')->default(EquipmentService::DATE_PERIOD_TYPE_MONTH);
            $table->date('service_next_date')->nullable();
            $table->dateTime('service_created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('equipment_services');
        Schema::rename('equipment_services_old', 'equipment_services');
    }
}
