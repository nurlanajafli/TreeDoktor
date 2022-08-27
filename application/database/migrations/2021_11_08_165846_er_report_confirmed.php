<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ErReportConfirmed extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('events_reports', function (Blueprint $table) {
            $table->tinyInteger('er_report_confirmed')->default(0)->index();
        });

        DB::table('events_reports as er')
            ->join('schedule as e', 'e.id', '=', 'er.er_event_id')
            ->update([ 'er.er_report_confirmed' => DB::raw("`e`.`event_report_confirmed`") ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('events_reports', function (Blueprint $table) {
            $table->dropColumn(['er_report_confirmed']);
        });
    }
}
