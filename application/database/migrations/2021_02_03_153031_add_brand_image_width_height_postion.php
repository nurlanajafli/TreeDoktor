<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBrandImageWidthHeightPostion extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('brand_images', function (Blueprint $table) {
            $table->string('bi_width')->nullable(true)->after('bi_value');
            $table->string('bi_height')->nullable(true)->after('bi_width');
            $table->string('bi_position')->nullable(true)->after('bi_height');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('brand_images', function (Blueprint $table) {
            $table->dropColumn('bi_width');
            $table->dropColumn('bi_height');
            $table->dropColumn('bi_position');
        });
    }
}
