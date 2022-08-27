<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ShowScheduleTimelineWeekend extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('settings')->insert([
            'stt_key_name'  =>  'schedule_show_weekend',
            'stt_key_value' =>  0,
            'stt_key_validate'  =>  NULL,
            'stt_section'       =>  'Schedule Preferences',
            'stt_label'         =>  'Timeline Show Weekend',
            'stt_is_hidden'     =>  0,
            'stt_html_attrs'    => 'type="checkbox" data-toggle="toggle" id="show_weekend_switcher"'
        ]);

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('settings')->where('stt_key_name', '=', 'schedule_show_weekend')->delete();
    }
}
