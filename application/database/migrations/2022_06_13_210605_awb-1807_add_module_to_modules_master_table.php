<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Awb1807AddModuleToModulesMasterTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('modules_master')
            ->insert([
                'module_id' => 'AEEPT',
                'module_desc' => 'Allow editing employee payroll times',
            ]);

        Schema::table('users', function (Blueprint $table) {
            $table->integer('during')->nullable()->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('modules_master')
            ->where('module_id', '=', 'AEEPT')
            ->delete();

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('during');
        });
    }
}
