<?php

use application\modules\billing\models\SmsSubscription;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

class AWB1507AddFreeSubscription extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $freeSubscription = SmsSubscription::where('amount', 0)->first();

        if ($freeSubscription) {
            $deletedOrders = DB::table('sms_orders')->where('sms_sub_id', $freeSubscription->id)->delete();

            if ($deletedOrders) {
                $date = Carbon::today()->startOfMonth()->toDateString();
                DB::table('sms_counters')->where('month', $date)->delete();
            }

            if ($freeSubscription->job_id) {
                DB::table('jobs')->where('job_id', $freeSubscription->job_id)->delete();
            }

            $freeSubscription->delete();
        }

        $nextDate = Carbon::today()->startOfYear()->addYear();

        $subscription = SmsSubscription::create([
            'name' => 'Gift',
            'count' => 3000,
            'amount' => 0,
            'period' => 'year',
            'next_date' => $nextDate->toDateString(),
            'on_out_limit' => 0,
            'description' => 'Free subscription',
            'active' => 1,
            'add_job' => false,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'job_id' => null
        ]);

        $payload = [
            'sub_id' => $subscription->id,
            'sub_next_date' => $subscription->next_date,
            'sub_period' => $subscription->period
        ];

        $jobId = DB::table('jobs')->insertGetId([
            'job_driver' => 'sms_subscriptions/renewal_on_period',
            'job_payload' => json_encode($payload),
            'job_attempts' => 0,
            'job_is_completed' => 0,
            'job_available_at' => $nextDate->addHour()->timestamp,
            'job_reserved_at' => 0,
            'job_created_at' => Carbon::now()
        ]);

        $subscription->job_id = $jobId;
        $subscription->add_job = true;
        $subscription->save();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $subscription = SmsSubscription::where('amount', 0)->first();

        if ($subscription) {
            if ($subscription->job_id) {
                DB::table('jobs')->where('job_id', $subscription->job_id)->delete();
            }

            $deletedOrders = DB::table('sms_orders')->where('sms_sub_id', $subscription->id)->delete();

            if ($deletedOrders) {
                $date = Carbon::today()->startOfMonth()->toDateString();
                DB::table('sms_counters')->where('month', $date)->delete();
            }

            $subscription->delete();
        }
    }
}
