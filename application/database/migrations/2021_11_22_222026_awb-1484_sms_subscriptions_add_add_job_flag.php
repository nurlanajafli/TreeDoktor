<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AWB1484SmsSubscriptionsAddAddJobFlag extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sms_subscriptions', function (Blueprint $table) {
            $table->renameColumn('note', 'description');
            $table->tinyInteger('add_job')->default(0)->after('active');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sms_subscriptions', function (Blueprint $table) {
            $table->renameColumn('description', 'note');
            $table->dropColumn('add_job');
        });
    }
}
