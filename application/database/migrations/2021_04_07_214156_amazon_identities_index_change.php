<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AmazonIdentitiesIndexChange extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared('ALTER TABLE amazon_identities DROP INDEX identity;');
        DB::unprepared('CREATE FULLTEXT INDEX search ON amazon_identities(identity)');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared('ALTER TABLE amazon_identities DROP INDEX search;');
        DB::unprepared('CREATE unique INDEX identity ON amazon_identities(identity)');
    }
}
