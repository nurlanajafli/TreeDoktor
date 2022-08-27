<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class WoOfficeNotes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('workorders', function (Blueprint $table) {
            $table->text('wo_office_notes')->charset('utf8mb4')->collation('utf8mb4_general_ci')->after('wo_scheduling_preference')->nullable();
            $table->string('workorder_no')->charset('utf8mb4')->collation('utf8mb4_general_ci')->change();
            $table->string('wo_extra_not_crew')->charset('utf8mb4')->collation('utf8mb4_general_ci')->change();
        });

        DB::unprepared('UPDATE workorders SET wo_office_notes = CONCAT(IFNULL(`wo_deposit_taken_by`, \'\'), \'\r\n\', IFNULL(`wo_scheduling_preference`, \'\'));');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('workorders', function (Blueprint $table) {
            $table->dropColumn(['wo_office_notes']);
        });
    }
}
