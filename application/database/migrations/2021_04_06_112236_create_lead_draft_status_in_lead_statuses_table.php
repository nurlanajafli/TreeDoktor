<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLeadDraftStatusInLeadStatusesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $row = [
            'lead_status_name' => 'Draft',
            'lead_status_draft' => 1
        ];
        DB::table('lead_statuses')->insert($row);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('lead_statuses')->where('lead_status_draft', 1)->delete();
    }
}
