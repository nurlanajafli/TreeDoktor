<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFollowupSettingsTagsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('followup_settings_tags', function (Blueprint $table) {
            $table->integer('fs_id');
            $table->unsignedInteger('tag_id');

            $table->foreign('fs_id')
                ->references('fs_id')
                ->on('followup_settings')
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
        Schema::table('followup_settings_tags', function (Blueprint $table) {
            $table->dropForeign(['fs_id']);
            $table->dropForeign(['tag_id']);
        });

        Schema::dropIfExists('followup_settings_tags');
    }
}
