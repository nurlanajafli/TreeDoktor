<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DataForSettingLunchAndDeduction extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('settings')->insert([
            'stt_key_name'      =>  'payroll_deduction_state',
            'stt_key_value'     =>  1,
            'stt_key_validate'  =>  NULL,
            'stt_section'       =>  'Payroll Settings',
            'stt_label'         =>  'Deduction',
            'stt_is_hidden'     =>  0,
            'stt_html_attrs'    => 'type="checkbox" data-toggle="toggle" id="deduction_switcher"'
        ]);

        DB::table('settings')->insert([
            'stt_key_name'      =>  'payroll_lunch_state',
            'stt_key_value'     =>  1,
            'stt_key_validate'  =>  NULL,
            'stt_section'       =>  'Payroll Settings',
            'stt_label'         =>  'Payroll Lunch',
            'stt_is_hidden'     =>  0,
            'stt_html_attrs'    => 'type="checkbox" data-toggle="toggle" id="lunch_switcher"'
        ]);

        DB::table('settings')->insert([
            'stt_key_name'      =>  'payroll_lunch_after_workhours',
            'stt_key_value'     =>  5,
            'stt_key_validate'  =>  'numeric|floatval|less_than[24]|greater_than[0]',
            'stt_section'       =>  'Payroll Settings',
            'stt_label'         =>  'Payroll Lunch After Worked Hours',
            'stt_is_hidden'     =>  0,
            'stt_html_attrs'    => 'class="lunch form-control" type="number" style="width:80px;"'
        ]);

        DB::table('settings')->insert([
            'stt_key_name'      =>  'payroll_lunch_time',
            'stt_key_value'     =>  0.5,
            'stt_key_validate'  =>  'numeric|floatval|less_than[24]|greater_than[0]',
            'stt_section'       =>  'Payroll Settings',
            'stt_label'         =>  'Payroll Lunch Time',
            'stt_is_hidden'     =>  0,
            'stt_html_attrs'    => 'class="lunch form-control" type="number" style="width:80px;"'
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('settings')->where('stt_key_name', '=', 'payroll_deduction_state')->delete();
        DB::table('settings')->where('stt_key_name', '=', 'payroll_lunch_state')->delete();
        DB::table('settings')->where('stt_key_name', '=', 'payroll_lunch_after_workhours')->delete();
        DB::table('settings')->where('stt_key_name', '=', 'payroll_lunch_time')->delete();
    }
}
