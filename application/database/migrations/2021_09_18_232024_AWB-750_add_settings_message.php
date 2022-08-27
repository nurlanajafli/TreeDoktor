<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AWB750AddSettingsMessage extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('settings')->insert([
            'stt_key_name'      => 'smsProvider',
            'stt_key_value'     => 'twilio/sms',
            'stt_key_validate'  => NULL,
            'stt_section'       => 'Messages settings',
            'stt_label'         => 'Provider',
            'stt_is_hidden'     => 1,
            'stt_html_attrs'    => null
        ]);

        DB::table('settings')->insert([
            'stt_key_name'      => 'sms_limit_period',
            'stt_key_value'     => 'year',
            'stt_key_validate'  => NULL,
            'stt_section'       => 'Messages settings',
            'stt_label'         => 'Limit period',
            'stt_is_hidden'     => 1,
            'stt_html_attrs'    => null
        ]);

        DB::table('settings')->insert([
            'stt_key_name'      => 'sms_limit',
            'stt_key_value'     => 1000,
            'stt_key_validate'  => NULL,
            'stt_section'       => 'Messages settings',
            'stt_label'         => 'Send SMS limit',
            'stt_is_hidden'     => 1,
            'stt_html_attrs'    => null
        ]);

        DB::table('settings')->insert([
            'stt_key_name'      => 'sms_support_chat_box',
            'stt_key_value'     => 0,
            'stt_key_validate'  => null,
            'stt_section'       => 'Messages Settings',
            'stt_label'         => 'Support chat box enable',
            'stt_is_hidden'     => 1,
            'stt_html_attrs'    => null
        ]);

        DB::table('settings')->insert([
            'stt_key_name'      => 'sms_messages_show_limit',
            'stt_key_value'     => 100,
            'stt_key_validate'  => null,
            'stt_section'       => 'Messages Settings',
            'stt_label'         => 'Show messages limit',
            'stt_is_hidden'     => 1,
            'stt_html_attrs'    => null
        ]);

        DB::table('settings')->insert([
            'stt_key_name'      => 'sms_chats_show_limit',
            'stt_key_value'     => 100,
            'stt_key_validate'  => null,
            'stt_section'       => 'Messages Settings',
            'stt_label'         => 'Show chats limit',
            'stt_is_hidden'     => 1,
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
        DB::table('settings')->where('stt_key_name', '=', 'smsProvider')->delete();
        DB::table('settings')->where('stt_key_name', '=', 'sms_limit_period')->delete();
        DB::table('settings')->where('stt_key_name', '=', 'sms_limit')->delete();
        DB::table('settings')->where('stt_key_name', '=', 'sms_support_chat_box')->delete();
        DB::table('settings')->where('stt_key_name', '=', 'sms_messages_show_limit')->delete();
        DB::table('settings')->where('stt_key_name', '=', 'sms_chats_show_limit')->delete();
    }
}
