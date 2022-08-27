<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTreeInventorySchemeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tree_inventory_scheme', function (Blueprint $table) {
            $table->increments('tis_id');
            $table->string('tis_name');
            $table->string('tis_client_id');
            $table->string('tis_address')->nullable();
            $table->string('tis_city')->nullable();
            $table->string('tis_state')->nullable();
            $table->string('tis_zip')->nullable();
            $table->string('tis_country')->nullable();
            $table->double('tis_lat')->nullable();
            $table->double('tis_lng')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tree_inventory_scheme');
    }
}
