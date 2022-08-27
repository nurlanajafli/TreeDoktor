<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ReindexSearchLeadsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared('ALTER TABLE leads DROP INDEX search;');
        DB::unprepared('CREATE FULLTEXT INDEX search ON leads(lead_no, lead_body, lead_address, lead_city, lead_country, lead_state, lead_zip, lead_add_info)');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared('ALTER TABLE leads DROP INDEX search;');
        DB::unprepared('CREATE FULLTEXT INDEX search ON leads(lead_no, lead_body, lead_address, lead_city, lead_country, lead_state, lead_zip)');
    }
}
