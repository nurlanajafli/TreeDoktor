<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPhoneFull extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        
        Schema::table('brand_contacts', function (Blueprint $table) {
            $table->string('bc_phone_clean')->nullable(true)->after('bc_phone');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('brand_contacts', function (Blueprint $table) {
            $table->dropColumn('bc_phone_clean');
        });
    }
}
