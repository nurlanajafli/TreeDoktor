<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePhoneMaskRecords extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('settings')->insert([
            'stt_key_name'  =>  'phone_inputmask',
            'stt_key_value' =>  '(999) 999-9999 Ext.999999999',
            'stt_key_validate'  =>  null,
            'stt_section'       =>  'Phone Numbers',
            'stt_label'         =>  'Format',
            'stt_is_hidden'     =>  1,
            'stt_html_attrs'    => null
        ]);
        DB::table('settings')->insert([
            'stt_key_name'  =>  'phone_country_code',
            'stt_key_value' =>  '+1',
            'stt_key_validate'  =>  null,
            'stt_section'       =>  'Phone Numbers',
            'stt_label'         =>  'Country Code',
            'stt_is_hidden'     =>  1,
            'stt_html_attrs'    => null
        ]);
        DB::table('settings')->insert([
            'stt_key_name'  =>  'phone_preview_prefix',
            'stt_key_value' =>  0,
            'stt_key_validate'  =>  null,
            'stt_section'       =>  'Phone Numbers',
            'stt_label'         =>  'Preview Country Code',
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
        DB::table('settings')->where('stt_key_name', '=', 'phone_inputmask')->delete();
        DB::table('settings')->where('stt_key_name', '=', 'phone_country_code')->delete();
        DB::table('settings')->where('stt_key_name', '=', 'phone_preview_prefix')->delete();
    }
}
