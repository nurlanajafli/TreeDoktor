<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSchedulesSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('settings')->insert([
            'stt_key_name'  =>  'crew_schedule_start',
            'stt_key_value' =>  7,
            'stt_key_validate'  =>  NULL,
            'stt_section'       =>  'Schedule Preferences',
            'stt_label'         =>  'Crew Schedule Starts From',
            'stt_is_hidden'     =>  0,
            'stt_html_attrs'    => 'id="stt-field-schedule-start" data-href="#scheduler-starts-dropdown" class="select2 w-100"'
        ]);
        DB::table('settings')->insert([
                'stt_key_name'  =>  'crew_schedule_end',
                'stt_key_value' =>  19,
                'stt_key_validate'  =>  NULL,
                'stt_section'       =>  'Schedule Preferences',
                'stt_label'         =>  'Crew Schedule Ends At',
                'stt_is_hidden'     =>  0,
                'stt_html_attrs'    => 'id="stt-field-schedule-end" data-href="#scheduler-ends-dropdown" class="select2 w-100"'
        ]);
        DB::table('settings')->insert([
            'stt_key_name'  =>  'office_schedule_start',
            'stt_key_value' =>  7,
            'stt_key_validate'  =>  NULL,
            'stt_section'       =>  'Schedule Preferences',
            'stt_label'         =>  'Office Schedule Starts From',
            'stt_is_hidden'     =>  0,
            'stt_html_attrs'    => 'id="stt-office-schedule-start" data-href="#scheduler-starts-dropdown" class="select2 w-100"'
        ]);
        DB::table('settings')->insert([
            'stt_key_name'  =>  'office_schedule_end',
            'stt_key_value' =>  20,
            'stt_key_validate'  =>  NULL,
            'stt_section'       =>  'Schedule Preferences',
            'stt_label'         =>  'Office Schedule Ends At',
            'stt_is_hidden'     =>  0,
            'stt_html_attrs'    => 'id="stt-office-schedule-end" data-href="#scheduler-ends-dropdown" class="select2 w-100"'
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('settings')->where('stt_key_name', '=', 'crew_schedule_start')->delete();
        DB::table('settings')->where('stt_key_name', '=', 'crew_schedule_end')->delete();
        DB::table('settings')->where('stt_key_name', '=', 'office_schedule_start')->delete();
        DB::table('settings')->where('stt_key_name', '=', 'office_schedule_end')->delete();
    }
}
