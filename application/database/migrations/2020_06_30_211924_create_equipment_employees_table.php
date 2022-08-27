<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEquipmentEmployeesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('equipment_employees', function (Blueprint $table) {
            $table->id('emp_id');
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('repair_id')->nullable()->index();
            $table->unsignedBigInteger('service_report_id')->nullable()->index();
            $table->unsignedFloat('emp_hours')->default(0);
            $table->unsignedDecimal('emp_hourly_rate', 10, 2)->default(0);
            $table->date('emp_worked_at')->nullable();
            $table->dateTime('emp_created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('equipment_employees');
    }
}
