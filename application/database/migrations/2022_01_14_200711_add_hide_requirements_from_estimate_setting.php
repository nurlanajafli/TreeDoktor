<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddHideRequirementsFromEstimateSetting extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('settings')->insert([
            'stt_key_name'      =>  'show_estimate_pdf_requirements_block',
            'stt_key_value'     =>  '1',
            'stt_key_validate'  =>  NULL,
            'stt_section'       =>  'Estimate PDF Settings',
            'stt_label'         =>  'Hide Requirements From PDF',
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
        DB::table('settings')->where('stt_key_name', '=', 'show_estimate_pdf_requirements_block')->delete();
    }
}
