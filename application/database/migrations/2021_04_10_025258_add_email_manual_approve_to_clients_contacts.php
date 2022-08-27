<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEmailManualApproveToClientsContacts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('clients_contacts', function (Blueprint $table) {
            $table->boolean('cc_email_manual_approve')->after('cc_email_check')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('clients_contacts', function (Blueprint $table) {
            $table->dropColumn('cc_email_manual_approve');
        });
    }
}
