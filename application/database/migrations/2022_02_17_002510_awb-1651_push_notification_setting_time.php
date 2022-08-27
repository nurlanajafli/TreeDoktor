<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AWB1651PushNotificationSettingTime extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('settings')->insert([
            'stt_key_name'      => 'client_task_push_reminder_min',
            'stt_key_value'     => 60,
            'stt_key_validate'  => null,
            'stt_section'       => 'Client task settings',
            'stt_label'         => 'How many minutes notice',
            'stt_is_hidden'     => 0,
            'stt_html_attrs'    => null
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('settings')->where('stt_key_name', '=', 'client_task_push_reminder_min')->delete();
    }
}
