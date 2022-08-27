<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class OnImportantContacts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //IMP_CT
        $users = DB::table('users')->select('*')
            ->where('active_status', '=', 'yes')
            ->where('system_user', '=', 0)
            ->get();
        foreach($users as $k=>$v){
            DB::table('user_module')->insert([
                    'user_id' => $v->id,
                    'module_id' => 'IMP_CT',
                    'module_status' => '1'
                ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('user_module')->where(['module_id' => 'IMP_CT'])->delete();
    }
}
