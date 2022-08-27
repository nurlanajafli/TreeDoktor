<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DeleteCounterFromEquipmentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        DB::unprepared("DROP FUNCTION IF EXISTS getNextCustomSeq");
        DB::unprepared("DROP PROCEDURE IF EXISTS sp_setCustomVal");
        DB::unprepared("DROP TRIGGER IF EXISTS eq_code_ai");
        Schema::dropIfExists('_sequences');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('equipment', function (Blueprint $table) {
            //
        });
    }
}
