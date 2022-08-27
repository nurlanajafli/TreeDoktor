<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SoftTwilioAudioFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('soft_twilio_audio_files', function (Blueprint $table) {
            $table->id('id');
            $table->string('label');
            $table->integer('user_id');
            $table->string('url');
            $table->string('recording_call_sid');
            $table->string('tag');
            $table->integer('cancelled');
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
        Schema::drop('soft_twilio_audio_files');
    }
}
