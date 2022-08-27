<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddClientEstimateBrandId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->integer('client_brand_id')->default(0)->after('client_id');
        });

        Schema::table('estimates', function (Blueprint $table) {
            $table->integer('estimate_brand_id')->default(0)->after('client_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn('client_brand_id');
        });

        Schema::table('estimates', function (Blueprint $table) {
            $table->dropColumn('estimate_brand_id');
        });
    }
}
