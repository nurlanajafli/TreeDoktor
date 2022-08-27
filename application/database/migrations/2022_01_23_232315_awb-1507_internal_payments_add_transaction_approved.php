<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AWB1507InternalPaymentsAddTransactionApproved extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('internal_payments', function (Blueprint $table) {
            $table->boolean('transaction_approved')->default(false)->after('transaction_id');
        });

        DB::table('internal_payments')->update(['transaction_approved' => true]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('internal_payments', function (Blueprint $table) {
            $table->dropColumn(['transaction_approved']);
        });
    }
}
