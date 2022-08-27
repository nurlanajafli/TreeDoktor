<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQBLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('qb_logs', function (Blueprint $table) {
            $table->increments('log_id');
            $table->tinyInteger('log_module_id')->index('log_module_id');
            $table->integer('log_entity_id')->index('log_entity_id');
            $table->integer('log_status_code')->nullable();
            $table->string('log_message')->nullable();
            $table->integer('log_action');
            $table->tinyInteger('log_route');
            $table->boolean('log_result');
            $table->bigInteger('log_user_id')->index('log_user_id');
            $table->timestamp('log_created_at')->default(DB::raw('CURRENT_TIMESTAMP'))->index('log_created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('qb_logs');
    }
}
