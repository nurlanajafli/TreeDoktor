<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddWorkorderPdfAmountSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $stt_html_attrs = 'id="stt-show-workorder-pdf-amounts" data-href="#enabled-disabled-select" class="select2 w-100"';
        DB::table('settings')->insert([
            'stt_key_name'  =>  'show_workorder_pdf_amounts',
            'stt_key_value' =>  0,
            'stt_key_validate'  =>  NULL,
            'stt_section'       =>  'Prices',
            'stt_label'         =>  'Amounts in Work Order PDF',
            'stt_is_hidden'     =>  0,
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
        DB::table('settings')->where('stt_key_name', '=', 'show_workorder_pdf_amounts')->delete();
    }
}
