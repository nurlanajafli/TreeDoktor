<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AWB750AddSettingsUnlimitPaid extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('settings')->where('stt_key_name', '=', 'sms_limit')->delete();

        DB::table('settings')->insert([
            'stt_key_name'      => 'sms_unlimited',
            'stt_key_value'     => 0,
            'stt_key_validate'  => NULL,
            'stt_section'       => 'Messages settings',
            'stt_label'         => 'Unlimited SMS',
            'stt_is_hidden'     => 1,
            'stt_html_attrs'    => null
        ]);

        DB::table('settings')->insert([
            'stt_key_name'      => 'sms_free_limit',
            'stt_key_value'     => 1000,
            'stt_key_validate'  => null,
            'stt_section'       => 'Messages settings',
            'stt_label'         => 'Free SMS limit',
            'stt_is_hidden'     => 1,
            'stt_html_attrs'    => null
        ]);

        DB::table('settings')->insert([
            'stt_key_name'      => 'sms_paid_limit',
            'stt_key_value'     => 0,
            'stt_key_validate'  => null,
            'stt_section'       => 'Messages settings',
            'stt_label'         => 'Paid SMS limit',
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
        DB::table('settings')->where('stt_key_name', '=', 'sms_unlimited')->delete();
        DB::table('settings')->where('stt_key_name', '=', 'sms_paid_limit')->delete();
        DB::table('settings')->where('stt_key_name', '=', 'sms_free_limit')->delete();

        DB::table('settings')->insert([
            'stt_key_name'      => 'sms_limit',
            'stt_key_value'     => 1000,
            'stt_key_validate'  => NULL,
            'stt_section'       => 'Messages settings',
            'stt_label'         => 'Send SMS limit',
            'stt_is_hidden'     => 1,
            'stt_html_attrs'    => null
        ]);
    }
}
