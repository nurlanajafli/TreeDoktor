<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTwilioWorkScheduleSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('settings')->insert([
            'stt_key_name'  =>  'twilioWorkingTimeFrom',
            'stt_key_value' =>  7,
            'stt_key_validate'  =>  NULL,
            'stt_section'       =>  '',
            'stt_label'         =>  '',
            'stt_is_hidden'     =>  0,
            'stt_html_attrs'    => ''
        ]);

        DB::table('settings')->insert([
            'stt_key_name'      =>  'twilioWorkingTimeTo',
            'stt_key_value'     =>  19,
            'stt_key_validate'  =>  NULL,
            'stt_section'       =>  '',
            'stt_label'         =>  '',
            'stt_is_hidden'     =>  0,
            'stt_html_attrs'    => ''
        ]);

        DB::table('settings')->insert([
            'stt_key_name'      =>  'twilioWeekendDays',
            'stt_key_value'     =>  json_encode(["0"]),
            'stt_key_validate'  =>  NULL,
            'stt_section'       =>  '',
            'stt_label'         =>  '',
            'stt_is_hidden'     =>  0,
            'stt_html_attrs'    => ''
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('settings')->where('stt_key_name', '=', 'twilioWorkingTimeFrom')->delete();
        DB::table('settings')->where('stt_key_name', '=', 'twilioWorkingTimeTo')->delete();
        DB::table('settings')->where('stt_key_name', '=', 'twilioWeekendDays')->delete();
    }
}
