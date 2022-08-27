<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBrandsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('brands', function (Blueprint $table) {
            $table->increments('b_id');
            $table->string('b_name')->nullable();
            $table->string('b_company_address')->nullable();
            $table->string('b_company_region')->nullable();
            $table->string('b_company_city')->nullable();
            $table->string('b_company_state')->nullable();
            $table->string('b_company_zip')->nullable();
            $table->string('b_company_country')->nullable();
            $table->double('b_company_lat')->nullable();
            $table->double('b_company_lng')->nullable();
            $table->tinyInteger('b_is_default')->default(0);
            $table->dateTime('b_created_at')->nullable()->index('b_created_at');
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
        Schema::dropIfExists('brands');
    }
}
