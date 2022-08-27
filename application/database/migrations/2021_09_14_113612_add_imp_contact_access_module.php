<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddImpContactAccessModule extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('modules_master')->insert([
            'module_id'  =>  'IMP_CT',
            'module_desc' =>  'Important Contacts'
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('modules_master')->where('module_id', '=', 'IMP_CT')->delete();
    }
}
