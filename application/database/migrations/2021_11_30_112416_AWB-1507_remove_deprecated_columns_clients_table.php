<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AWB1507RemoveDeprecatedColumnsClientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn('client_cc_name');
            $table->dropColumn('client_cc_type');
            $table->dropColumn('client_cc_exp_month');
            $table->dropColumn('client_cc_exp_year');
            $table->dropColumn('client_cc_cvv');
            $table->dropColumn('client_cc_number');
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
            $table->string('client_cc_name')->nullable();
            $table->enum('client_cc_type', ['visa','mc'])->nullable();
            $table->tinyInteger('client_cc_exp_month')->nullable();
            $table->integer('client_cc_exp_year')->nullable();
            $table->string('client_cc_cvv', 10)->nullable();
            $table->longText('client_cc_number');
        });
    }
}
