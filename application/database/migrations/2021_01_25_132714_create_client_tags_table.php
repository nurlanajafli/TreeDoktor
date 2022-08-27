<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClientTagsTable extends Migration
{
    /** 
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('client_tags', function (Blueprint $table) {
            $table->integer('client_id');
            $table->unsignedInteger('tag_id');

            $table->foreign('client_id')
                    ->references('client_id')
                    ->on('clients')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');

            $table->foreign('tag_id')
                ->references('tag_id')
                ->on('tags')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('client_tags', function (Blueprint $table) {
            $table->dropForeign(['client_id']);
            $table->dropForeign(['tag_id']);
        });

        Schema::dropIfExists('client_tags');
    }
}
