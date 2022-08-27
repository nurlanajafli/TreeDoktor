<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldEventId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('safety_pdf_signs', function (Blueprint $table) {
            $table->unsignedBigInteger('work_event_id')->nullable()->after('event_id')->index();
        });

        DB::table('safety_pdf_signs as sps')
            ->join('events as e', 'e.ev_event_id', '=', 'sps.event_id')
            ->update([ 'sps.work_event_id' => DB::raw("`e`.`ev_id`") ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('safety_pdf_signs', function (Blueprint $table) {
            $table->dropColumn(['work_event_id']);
        });
    }
}
