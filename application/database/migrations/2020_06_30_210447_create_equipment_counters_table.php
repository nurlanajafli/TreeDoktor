<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEquipmentCountersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('equipment_counters', function (Blueprint $table) {
            $table->id('counter_id');
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('eq_id')->index();
            $table->unsignedBigInteger('repair_id')->nullable()->index();
            $table->unsignedBigInteger('service_report_id')->nullable()->index();
            $table->unsignedBigInteger('counter_value');
            $table->text('counter_note')->nullable();
            $table->dateTime('counter_created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('equipment_counters');
    }
}
