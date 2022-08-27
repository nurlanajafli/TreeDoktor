<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSessionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ci_sessions', function (Blueprint $table) {
            $table->string('id', 128)->nullable(false);
            $table->string('ip_address', 45)->nullable(false);
            $table->unsignedInteger('timestamp')->default(0);
            $table->binary('data')->nullable(false);
            $table->index('timestamp', 'ci_sessions_timestamp');
            $table->unique('id', 'ci_sessions_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ci_sessions');
    }
}
