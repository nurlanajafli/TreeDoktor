<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRegexPhonePreview extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('settings')->insert([
            'stt_key_name'  =>  'phone_mask_php_regex_pattern',
            'stt_key_value' =>  '(\d{3})(\d{3})(\d{4})(\d{0,})',
            'stt_key_validate'  =>  null,
            'stt_section'       =>  'Phone Numbers',
            'stt_label'         =>  'Regex Pattern',
            'stt_is_hidden'     =>  1,
            'stt_html_attrs'    => null
        ]);
        DB::table('settings')->insert([
            'stt_key_name'  =>  'phone_mask_php_regex_pattern_preview',
            'stt_key_value' =>  '($1) $2-$3',
            'stt_key_validate'  =>  null,
            'stt_section'       =>  'Phone Numbers',
            'stt_label'         =>  'Pattern Preview',
            'stt_is_hidden'     =>  1,
            'stt_html_attrs'    => null
        ]);
        DB::table('settings')->insert([
            'stt_key_name'  =>  'phone_clean_length',
            'stt_key_value' =>  '10',
            'stt_key_validate'  =>  null,
            'stt_section'       =>  'Phone Numbers',
            'stt_label'         =>  'Length Without Country Code',
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
        DB::table('settings')->where('stt_key_name', '=', 'phone_mask_php_regex_pattern')->delete();
        DB::table('settings')->where('stt_key_name', '=', 'phone_mask_php_regex_pattern_preview')->delete();
        DB::table('settings')->where('stt_key_name', '=', 'phone_clean_length')->delete();
    }
}
