<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldworkerJobPermissions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('modules_master')->insert([
            'module_desc' => 'Edit job service status',
            'module_id' => 'FWSS',
        ]);

        DB::table('modules_master')->insert([
            'module_desc' => 'Send invoice after workorder finished',
            'module_id' => 'FWI',
        ]);

        DB::table('modules_master')->insert([
            'module_desc' => 'Require GPS to track time',
            'module_id' => 'GPS',
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('modules_master')->where('module_id', 'FWSS')->delete();
        DB::table('modules_master')->where('module_id', 'FWI')->delete();
        DB::table('modules_master')->where('module_id', 'GPS')->delete();
    }
}
