<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTisOverlayPathColumnToTreeInventorySchemeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tree_inventory_scheme', function (Blueprint $table) {
            $table->text('tis_overlay_path')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tree_inventory_scheme', function (Blueprint $table) {
            $table->dropColumn(['tis_overlay_path']);
        });
    }
}
