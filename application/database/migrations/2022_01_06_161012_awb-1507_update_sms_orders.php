<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AWB1507UpdateSmsOrders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sms_orders', function (Blueprint $table) {
            $table->date('to')->nullable()->change();
            $table->string('sub_name');
            $table->decimal('sub_amount');
            $table->enum('renewal', ['period', 'limit'])->nullable();
            $table->integer('card_id')->nullable();
            $table->boolean('paid')->default(false);
            $table->dateTime('paid_at')->nullable();
            $table->string('error_info')->nullable();
            $table->boolean('is_active')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sms_orders', function (Blueprint $table) {
            $table->date('to')->change();
            $table->dropColumn(['sub_name', 'sub_amount', 'renewal', 'card_id', 'paid', 'paid_at', 'error_info', 'is_active']);
        });
    }
}
