<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSoftTwilioWorkspacesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('soft_twilio_workspaces', function (Blueprint $table) {
            $table->id();
            $table->string('sid')->unique();
            $table->string('friendlyName')->unique();
            $table->string('defaultActivityName');
            $table->string('timeoutActivityName');
            $table->string('eventCallbackUrl');
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
        Schema::dropIfExists('soft_twilio_workspaces');
    }
}
