<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSoftTwilioWorkersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('soft_twilio_workers', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('workspace_id');
            $table->unsignedInteger('user_id');
            $table->string('sid')->unique();
            $table->string('friendlyName');
            $table->string('activityName');
            $table->string('available');
            $table->dateTime('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->dateTime('updated_at')->nullable();

            /*$table->foreign('workspace_id')
                ->references('id')
                ->on('soft_twilio_workspaces')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
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
            $table->dropForeign(['user_id']);
        });*/
        Schema::dropIfExists('soft_twilio_workers');
    }
}
