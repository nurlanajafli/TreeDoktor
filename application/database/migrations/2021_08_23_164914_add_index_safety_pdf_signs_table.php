<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexSafetyPdfSignsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::raw("ALTER TABLE safety_pdf_signs ADD UNIQUE event_id_team_id_user_id(event_id, team_id, user_id);");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::raw('ALTER TABLE safety_pdf_signs DROP INDEX event_id_team_id_user_id;');
    }
}
