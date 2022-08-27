<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SetWorkorderStatusFlags extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('workorder_status')
            ->where('wo_status_color', '=', '')
            ->orWhereNull('wo_status_color')
            ->update(['wo_status_use_team_color' => 1]);

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('workorder_status')
            ->where('wo_status_use_team_color', '=', 1)
            ->update(['wo_status_color' => '', 'wo_status_use_team_color'=>0]);
    }
}
