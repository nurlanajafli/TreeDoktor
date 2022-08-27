<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSoftTwilioWorkflowsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('soft_twilio_workflows', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('workspace_id');
            $table->string('sid')->unique();
            $table->string('friendlyName');
            $table->string('taskReservationTimeout');
            $table->string('assignmentCallbackUrl')->nullable();
            $table->string('fallbackAssignmentCallbackUrl')->nullable();
            $table->text('configuration');
            $table->dateTime('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->dateTime('updated_at')->nullable();

            /*$table->foreign('workspace_id')
                ->references('id')
                ->on('soft_twilio_workspaces')
                ->onDelete('cascade')
                ->onUpdate('cascade');*/
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        /*Schema::table('client_tags', function (Blueprint $table) {
            $table->dropForeign(['workspace_id']);
        });*/
        Schema::dropIfExists('soft_twilio_workflows');
    }
}
