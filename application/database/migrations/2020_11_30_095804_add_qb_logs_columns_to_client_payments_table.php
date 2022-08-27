<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddQbLogsColumnsToClientPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('client_payments', function (Blueprint $table) {
            $table->timestamp('payment_last_qb_time_log')->nullable(true)->default(null);
            $table->tinyInteger('payment_last_qb_sync_result');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('client_payments', function (Blueprint $table) {
            $table->dropColumn(['payment_last_qb_time_log', 'payment_last_qb_sync_result']);
        });
    }
}
