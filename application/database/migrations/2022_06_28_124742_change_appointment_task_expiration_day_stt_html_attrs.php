<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeAppointmentTaskExpirationDaySttHtmlAttrs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('settings')
            ->where('stt_key_name', '=', 'AppointmentTaskExpirationDay')
            ->update([
                'stt_html_attrs' => "class='select2 w-200' id='taskLength' data-href='#appointment-task-config' style='float:right'",
            ]);
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
