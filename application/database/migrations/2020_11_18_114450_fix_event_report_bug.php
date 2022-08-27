<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FixEventReportBug extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /*Schema::table('events', function (Blueprint $table) {
            $table->where('ev_on_site_time', '>', '100000')->update(['ev_on_site_time' => 0]);
        });*/
        $fields = DB::table('events')->where('ev_on_site_time', '>', 100000)->update(['ev_on_site_time' => 0]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
