<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ActivateUserGpsPermissions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("INSERT INTO user_module (id, user_id, module_id, module_status) SELECT null, id, 'GPS', '1' FROM users WHERE is_tracked = 1");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("DELETE user_module FROM user_module JOIN users ON user_module.user_id = users.id WHERE module_id = 'GPS'");
    }
}
