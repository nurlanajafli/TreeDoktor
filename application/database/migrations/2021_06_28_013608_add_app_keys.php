<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAppKeys extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('settings')->insert([
            'stt_key_name'  =>  'app_android_key',
            'stt_key_value' =>  'Y3YFr7zzuNof37kTsNFgcbW2loS4vfpVqJNSp',
            'stt_key_validate'  =>  NULL,
            'stt_section'       =>  'APP',
            'stt_label'         =>  'APP Android Key',
            'stt_is_hidden'     =>  1,
            'stt_html_attrs'    => ''
        ]);
        DB::table('settings')->insert([
            'stt_key_name'  =>  'app_ios_key',
            'stt_key_value' =>  'r7totaIAWsENGgWvBCX7oATZcHGVA9fknMmB5',
            'stt_key_validate'  =>  NULL,
            'stt_section'       =>  'APP',
            'stt_label'         =>  'APP iOS Key',
            'stt_is_hidden'     =>  1,
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
        //
    }
}
