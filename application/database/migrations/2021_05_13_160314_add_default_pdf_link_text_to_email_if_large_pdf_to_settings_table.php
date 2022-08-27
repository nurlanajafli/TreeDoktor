<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDefaultPdfLinkTextToEmailIfLargePdfToSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('settings')->insert([
            'stt_key_name'      =>  'default_pdf_link_text',
            'stt_key_value'     =>  'Your [DOCUMENT_NAME] is available: [DOCUMENT_LINK]',
            'stt_is_hidden'     =>  1,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('settings')->where('stt_key_name', '=', 'default_pdf_link_text')->delete();
    }
}
