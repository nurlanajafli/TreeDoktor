<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateTaxInLeadsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->decimal('lead_tax_rate', 8, 5)->default(1)->change();
            $table->decimal('lead_tax_value', 6, 3)->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->decimal('lead_tax_rate', 7, 4)->default(1)->change();
            $table->decimal('lead_tax_value', 5, 2)->default(0)->change();
        });
    }
}
