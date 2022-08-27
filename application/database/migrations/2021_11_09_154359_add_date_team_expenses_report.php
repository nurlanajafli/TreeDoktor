<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDateTeamExpensesReport extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('team_expeses_report', function (Blueprint $table) {
            $table->date('ter_date')->nullable();
        });

        DB::table('team_expeses_report as ter')
            ->join('schedule_teams as t', 't.team_id', '=', 'ter.ter_team_id')
            ->update([ 'ter.ter_date' => DB::raw("`t`.`team_date_start`") ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('team_expeses_report', function (Blueprint $table) {
            $table->dropColumn(['ter_date']);
        });
    }
}
