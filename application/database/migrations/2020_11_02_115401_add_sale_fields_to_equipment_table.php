<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSaleFieldsToEquipmentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('equipment', function (Blueprint $table) {
            $table->dateTime('eq_sold_at')->nullable()->after('eq_gps_start_date');
            $table->decimal('eq_sold_cost', 10, 2)->nullable()->after('eq_sold_at');
            $table->unsignedInteger('seller_id')->nullable()->after('eq_sold_cost');
            $table->string('eq_sold_code')->nullable()->after('seller_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('equipment', function (Blueprint $table) {
            $table->dropColumn(['eq_sold_at', 'eq_sold_cost', 'seller_id', 'eq_sold_code']);
        });
    }
}
