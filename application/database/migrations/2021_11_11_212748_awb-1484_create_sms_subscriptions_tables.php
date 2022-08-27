<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

class AWB1484CreateSmsSubscriptionsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sms_subscriptions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->nullable();
            $table->integer('count');
            $table->decimal('amount');
            $table->enum('period', ['year', 'month']);
            $table->date('next_date')->nullable();
            $table->tinyInteger('on_out_limit')->default(0);
            $table->string('note')->nullable();
            $table->tinyInteger('active')->default(0);
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
        });

        DB::table('sms_subscriptions')->insert([
            'name' => 'Lite',
            'count' => 1000,
            'amount' => 50,
            'period' => 'month',
            'next_date' => null,
            'on_out_limit' => 0,
            'note' => 'Available automatic renewal at the end of the period and if the limit is reached',
            'active' => 1,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);

        DB::table('sms_subscriptions')->insert([
            'name' => 'Optimal',
            'count' => 5000,
            'amount' => 100,
            'period' => 'month',
            'next_date' => null,
            'on_out_limit' => 0,
            'note' => 'Available automatic renewal at the end of the period and if the limit is reached',
            'active' => 1,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);

        DB::table('sms_subscriptions')->insert([
            'name' => 'Business',
            'count' => 10000,
            'amount' => 150,
            'period' => 'month',
            'next_date' => null,
            'on_out_limit' => 0,
            'note' => 'Available automatic renewal at the end of the period and if the limit is reached',
            'active' => 1,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);

        Schema::create('sms_orders', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('sms_sub_id');
            $table->integer('count');
            $table->integer('remain');
            $table->date('from');
            $table->date('to');
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
        });

        Schema::create('sms_counters', function (Blueprint $table) {
            $table->increments('id');
            $table->date('month');
            $table->integer('count');
            $table->integer('remain');
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sms_subscriptions');
        Schema::dropIfExists('sms_orders');
        Schema::dropIfExists('sms_counters');
    }
}
