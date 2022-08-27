<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCurrenySettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('settings')->insert([
            'stt_key_name'  =>  'currency_symbol',
            'stt_key_value' =>  '$',
            'stt_key_validate'  =>  NULL,
            'stt_section'       =>  'Currency',
            'stt_label'         =>  'Currency Symbol',
            'stt_is_hidden'     =>  1,
            'stt_html_attrs'    => ''
        ]);


        $stt_html_attrs = 'id="stt-currency-symbol-position" data-href="#currency-symbols-position-option" class="select2 w-100"';
        DB::table('settings')->insert([
            'stt_key_name'      =>  'currency_symbol_position',
            'stt_key_value'     =>  '{currency} {amount}',
            'stt_key_validate'  =>  NULL,
            'stt_section'       =>  'Currency',
            'stt_label'         =>  'Currency Symbol Position',
            'stt_is_hidden'     =>  1,
            'stt_html_attrs'    => $stt_html_attrs
        ]);

        

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('settings')->where('stt_key_name', '=', 'currency_symbols')->delete();
        DB::table('settings')->where('stt_key_name', '=', 'currency_symbols_position')->delete();
    }
}
