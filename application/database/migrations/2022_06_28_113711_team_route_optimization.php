<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class TeamRouteOptimization extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('schedule_teams', function (Blueprint $table) {
            $table->tinyInteger('team_route_optimized')->default(0);
            $table->string('team_route_hash')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('schedule_teams', function (Blueprint $table) {
            $table->dropColumn(['team_route_optimized', 'team_route_hash']);
        });
    }
}
