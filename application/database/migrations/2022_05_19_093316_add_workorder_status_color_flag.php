<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddWorkorderStatusColorFlag extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('workorder_status', function (Blueprint $table) {
            $table->tinyInteger('wo_status_use_team_color')->default(0);
            $table->tinyInteger('wo_status_use_estimator_color')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('workorder_status', function (Blueprint $table) {
            $table->dropColumn(['wo_status_use_team_color', 'wo_status_use_estimator_color']);
        });
    }
}
