<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLogoutToEmpLogin extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('emp_login', function (Blueprint $table) {
            $table->boolean('logout_office')->default(false);
            $table->boolean('logout_from_app')->default(false);
            $table->boolean('login_in_office')->default(false);
            $table->boolean('logout_in_office')->default(false);
            $table->boolean('login_app_in_office')->default(false);
            $table->boolean('logout_app_in_office')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('emp_login', function (Blueprint $table) {
            $table->dropColumn([
                'logout_office',
                'logout_from_app',
                'login_in_office',
                'logout_in_office',
                'login_app_in_office',
                'logout_app_in_office'
            ]);
        });
    }
}
