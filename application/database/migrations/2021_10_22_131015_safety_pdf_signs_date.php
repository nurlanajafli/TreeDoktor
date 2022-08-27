<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use \Illuminate\Support\Facades\DB;
class SafetyPdfSignsDate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('safety_pdf_signs', function (Blueprint $table) {
            $table->date('date')->nullable();
        });

        DB::table('safety_pdf_signs as sps')
            ->join('schedule_teams as st', 'sps.team_id', '=', 'st.team_id')
            ->update([ 'sps.date' => DB::raw("`st`.`team_date_start`") ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('safety_pdf_signs', function (Blueprint $table) {
            $table->dropColumn(['date']);
        });
    }
}
