<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class EventReportDate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('events_reports', function (Blueprint $table) {
            $table->date('er_report_date')->nullable();
        });

        DB::table('events_reports as er')
            ->join('events as e', 'e.ev_event_id', '=', 'er.er_event_id')
            ->update([ 'er.er_report_date' => DB::raw("`e`.`ev_start_time`") ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('events_reports', function (Blueprint $table) {
            $table->dropColumn(['er_report_date']);
        });
    }
}
