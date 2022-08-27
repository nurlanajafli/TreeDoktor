<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AWB1507SmsSubscriptionsAddJobId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sms_subscriptions', function (Blueprint $table) {
            $table->integer('job_id')->nullable();
        });

        DB::table('jobs')->where('job_driver', 'sms_subscriptions/renewal_on_period')->delete();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sms_subscriptions', function (Blueprint $table) {
            $table->dropColumn(['job_id']);
        });
    }
}
