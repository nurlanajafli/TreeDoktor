<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddQbSyncCustomFieldsFieldToSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('settings')->insert([
            'stt_key_name'  =>  'qb_sync_custom_fields_in_qb',
            'stt_key_value' =>  '',
            'stt_key_validate'  =>  null,
            'stt_section'       =>  'QuickBooks',
            'stt_label'         =>  'QB sync custom fields in qb',
            'stt_is_hidden'     =>  1,
            'stt_html_attrs'    => null
        ]);
        DB::table('settings')->insert([
            'stt_key_name'  =>  'qb_sync_custom_fields_in_db',
            'stt_key_value' =>  '',
            'stt_key_validate'  =>  null,
            'stt_section'       =>  'QuickBooks',
            'stt_label'         =>  'QB sync custom fields in db',
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
        DB::table('settings')->where('stt_key_name', '=', 'qb_sync_custom_fields_in_qb')->delete();
        DB::table('settings')->where('stt_key_name', '=', 'qb_sync_custom_fields_in_db')->delete();
    }
}
