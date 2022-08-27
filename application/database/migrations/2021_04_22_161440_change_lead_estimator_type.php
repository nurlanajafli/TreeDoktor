<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeLeadEstimatorType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        DB::unprepared("UPDATE leads SET lead_estimator = NULL WHERE lead_estimator = 'none';");
        DB::unprepared('ALTER TABLE leads CHANGE lead_estimator lead_estimator BIGINT(20) NULL DEFAULT NULL;');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

    }
}
