<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class PatchMailableTableForAddEstimateToEmail extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("
            INSERT INTO emailables (email_email_id, emailable_type, emailable_id)
            SELECT ecn.email_email_id, 'application\\\\modules\\\\estimates\\\\models\\\\Estimate', e.estimate_id FROM client_notes
            LEFT JOIN emailables as ecn ON client_note_id = ecn.emailable_id AND ecn.emailable_type = 'application\\\\modules\\\\clients\\\\models\\\\ClientNote'
            LEFT JOIN emailables as ee ON ecn.email_email_id = ee.email_email_id AND ee.emailable_type = 'application\\\\modules\\\\estimates\\\\models\\\\Estimate'
            LEFT JOIN estimates e on client_notes.lead_id = e.lead_id
            WHERE client_note LIKE '%Estimate % sent to %' AND ecn.emailable_id IS NOT NULL AND ee.emailable_id IS NULL;
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
