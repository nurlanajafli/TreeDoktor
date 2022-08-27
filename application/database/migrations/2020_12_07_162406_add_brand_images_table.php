<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBrandImagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        
        Schema::create('brand_images', function (Blueprint $table) {
            $table->increments('bi_id');
            $table->integer('bi_brand_id')->index('bi_brand_id');
            $table->string('bi_key')->nullable();
            $table->string('bi_value')->nullable();
            $table->dateTime('bi_created_at')->nullable()->index('bi_created_at');
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
        Schema::dropIfExists('brand_images');
    }
}
