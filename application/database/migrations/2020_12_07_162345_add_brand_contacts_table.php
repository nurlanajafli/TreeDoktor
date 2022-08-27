<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBrandContactsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        
        Schema::create('brand_contacts', function (Blueprint $table) {
            $table->increments('bc_id');
            $table->integer('bc_brand_id')->index('bc_brand_id');
            $table->string('bc_phone')->nullable();
            $table->string('bc_email')->nullable();
            $table->dateTime('bc_created_at')->nullable()->index('bc_created_at');
            $table->softDeletes();
        });
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('brand_contacts');
    }
}
