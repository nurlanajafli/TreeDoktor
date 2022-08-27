<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddExpirationDaysTaskSetting extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('settings')->insert([
            'stt_key_name'  => 'AppointmentTaskExpirationDay',
            'stt_key_value' => '90',
            'stt_key_validate'  => '',
            'stt_section'       => 'Appointment',
            'stt_label'         => 'Appointment task expiration day',
            'stt_is_hidden'     => 0,
            'stt_html_attrs'    => "class='select2 w-200' id='taskLength' data-href='#task-config' style='float:right'"
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('settings')->where('stt_key_name', '=', 'AppointmentTaskExpirationDay')->delete();
    }
}
