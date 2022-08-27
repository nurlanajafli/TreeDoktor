<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class EstimateCrewNotes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('estimates', function (Blueprint $table) {
            $table->text('estimate_crew_notes')->charset('utf8mb4')->after('estimate_item_note_crew')->nullable();
            $table->string('estimate_no')->charset('utf8mb4')->collation('utf8mb4_general_ci')->change();
            $table->string('estimate_item_note_payment')->charset('utf8mb4')->collation('utf8mb4_general_ci')->change();
            $table->text('estimate_item_note_crew')->charset('utf8mb4')->collation('utf8mb4_general_ci')->change();
            $table->text('estimate_item_note_estimate')->charset('utf8mb4')->collation('utf8mb4_general_ci')->change();
        });

        DB::unprepared('UPDATE estimates LEFT JOIN workorders ON workorders.estimate_id = estimates.estimate_id SET estimates.estimate_crew_notes = CONCAT(IFNULL(`estimates`.`estimate_item_note_crew`, \'\'), \'\r\n\', IFNULL(`workorders`.`wo_extra_not_crew`, \'\'));');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('estimates', function (Blueprint $table) {
            $table->dropColumn(['estimate_crew_notes']);
        });
    }
}
