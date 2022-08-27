<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBrandReviewTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('brand_review', function (Blueprint $table) {
            $table->increments('br_id');
            $table->integer('brand_id');
            $table->longText('br_header');
            $table->longText('br_dislike_message');
            $table->longText('br_like_message');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('brand_review');
    }
}
