<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AWB1507SmsOrdersAddSubPeriod extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sms_orders', function (Blueprint $table) {
            $table->enum('sub_period', ['year', 'month'])->default('month')->after('sub_amount');
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
            $table->dropColumn(['sub_period']);
        });
    }
}
