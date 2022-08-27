<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEstimatesFullTextIndex extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        DB::unprepared('CREATE FULLTEXT INDEX search ON estimates(estimate_no, estimate_item_note_crew, estimate_item_note_estimate, estimate_item_note_payment)');
        DB::unprepared('CREATE FULLTEXT INDEX search ON workorders(workorder_no, wo_extra_not_crew)');
        DB::unprepared('CREATE FULLTEXT INDEX search ON invoices(invoice_no)');
    }

    public function down() {
        DB::unprepared('ALTER TABLE estimates DROP INDEX search;');
        DB::unprepared('ALTER TABLE workorders DROP INDEX search;');
        DB::unprepared('ALTER TABLE invoices DROP INDEX search;');
    }
}
