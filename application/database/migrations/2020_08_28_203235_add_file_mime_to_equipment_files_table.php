<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFileMimeToEquipmentFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('equipment_files', function (Blueprint $table) {
            $table->string('file_mime')->nullable()->after('file_name');
            $table->bigInteger('file_size')->nullable()->after('file_mime');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('equipment_files', function (Blueprint $table) {
            $table->dropColumn(['file_mime', 'file_size']);
        });
    }
}
