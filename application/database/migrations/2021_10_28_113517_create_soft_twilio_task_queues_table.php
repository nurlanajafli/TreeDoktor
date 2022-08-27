<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSoftTwilioTaskQueuesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('soft_twilio_task_queues', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('workspace_id');
            $table->string('sid')->unique();
            $table->string('friendlyName');
            $table->string('reservationActivitySid')->nullable();
            $table->string('assignmentActivitySid')->nullable();
            $table->string('maxReservedWorkers');
            $table->string('targetWorkers');

            $table->dateTime('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->dateTime('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('soft_twilio_task_queues');
    }
}
