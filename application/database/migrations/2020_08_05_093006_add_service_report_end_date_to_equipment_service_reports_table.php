<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddServiceReportEndDateToEquipmentServiceReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('equipment_service_reports', function (Blueprint $table) {
            $table->date('service_report_end_date')->nullable()->after('service_report_note');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('equipment_service_reports', function (Blueprint $table) {
            $table->dropColumn(['service_report_end_date']);
        });
    }
}
