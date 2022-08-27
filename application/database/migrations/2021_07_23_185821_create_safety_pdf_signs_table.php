<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSafetyPdfSignsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('safety_pdf_signs', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('event_id');
            $table->integer('team_id');
            $table->integer('user_id');
            $table->boolean('is_teamlead');
            $table->text('safety_pdf_sign')->nullable();
            $table->dateTime('signed')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('safety_pdf_signs');
    }
}
