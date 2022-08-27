<?php

use Illuminate\Database\Migrations\Migration;

class FromSql extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //DB::statement('CREATE TABLE `arbostar`.`test2`( `test` VARCHAR(255) );');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //DB::statement('DROP TABLE `arbostar`.`test2`;');
    }
}
