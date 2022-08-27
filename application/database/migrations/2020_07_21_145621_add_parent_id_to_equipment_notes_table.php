<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddParentIdToEquipmentNotesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('equipment_notes', function (Blueprint $table) {
            $table->unsignedBigInteger('note_parent_id')->nullable()->after('note_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('equipment_notes', function (Blueprint $table) {
            $table->dropColumn(['note_parent_id']);
        });
    }
}
