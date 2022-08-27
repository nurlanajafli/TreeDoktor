<?php

use Illuminate\Database\Migrations\Migration;

class Awb1807AddAllowToTime extends Migration
{
    /**
     * Run the migrations.
     * Add AEEPT to user_module
     *
     * @return void
     */
    public function up()
    {
        DB::statement("DELETE user_module FROM user_module JOIN users ON user_module.user_id = users.id WHERE module_id = 'AEEPT'");
        DB::statement("INSERT INTO user_module (id, user_id, module_id, module_status) SELECT null, id, 'AEEPT', '1' FROM users WHERE system_user = 0");
    }

    /**
     * Reverse the migrations.
     * Delete AEEPT to user_module
     *
     * @return void
     */
    public function down()
    {
        DB::statement("DELETE user_module FROM user_module JOIN users ON user_module.user_id = users.id WHERE module_id = 'AEEPT'");
    }
}
