<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class TeamDateTo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('schedule_teams', function (Blueprint $table) {
            $table->date('team_date_end')->after('team_date');
            $table->date('team_date_start')->after('team_date');
        });

        DB::unprepared("UPDATE schedule_teams SET `team_date_start` = FROM_UNIXTIME(`team_date`+3600, '%Y-%m-%d'), `team_date_end` = FROM_UNIXTIME(`team_date`+3600, '%Y-%m-%d')");

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('schedule_teams', function (Blueprint $table) {
            $table->dropColumn('team_date_start');
            $table->dropColumn('team_date_end');
        });
    }
}
