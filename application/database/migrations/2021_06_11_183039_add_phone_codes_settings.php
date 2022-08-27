<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPhoneCodesSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('settings')->insert([
            'stt_key_name'  =>  'allow_phone_codes',
            'stt_key_value' =>  '',
            'stt_key_validate'  =>  null,
            'stt_section'       =>  'Phone Numbers',
            'stt_label'         =>  'Allow Phone Codes',
            'stt_is_hidden'     =>  1,
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
        DB::table('settings')->where('stt_key_name', '=', 'allow_phone_codes')->delete();
    }
}
