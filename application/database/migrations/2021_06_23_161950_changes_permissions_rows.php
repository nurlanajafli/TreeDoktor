<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use \Illuminate\Support\Facades\DB;

class ChangesPermissionsRows extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('modules_master')->whereIn('module_id', ['LD', 'EST', 'WO', 'IN'])->delete();
        DB::table('modules_master')->where('module_id', 'CL')->update(['module_desc' => 'CRM Access Level']);
        DB::table('user_module')->where('module_id', 'CL')->where('module_status', '3')->update(['module_status' => '2']);
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
