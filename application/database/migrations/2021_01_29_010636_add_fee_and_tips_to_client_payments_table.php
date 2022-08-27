<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFeeAndTipsToClientPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('client_payments', function (Blueprint $table) {
            $table->decimal('payment_fee', 10, 2)->after('payment_amount')->default(0);
            $table->decimal('payment_fee_percent', 5, 2)->after('payment_fee')->default(0);
            $table->decimal('payment_tips', 10, 2)->after('payment_fee_percent')->default(0);
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
            $table->dropColumn(['payment_fee', 'payment_fee_percent', 'payment_tips']);
        });
    }
}
