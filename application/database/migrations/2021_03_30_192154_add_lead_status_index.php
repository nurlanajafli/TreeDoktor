<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLeadStatusIndex extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->index(['lead_status_id']);
            $table->index(['lead_postpone_date']);
        });


        Schema::table('lead_statuses', function (Blueprint $table) {
            $sm = Schema::getConnection()->getDoctrineSchemaManager();
            $doctrineTable = $sm->listTableDetails('lead_statuses');

            if (! $doctrineTable->hasIndex('lead_status_declined')) {
                $table->index('lead_status_declined', 'lead_status_declined');
            }

            if (! $doctrineTable->hasIndex('lead_status_default')) {
                $table->index('lead_status_default', 'lead_status_default');
            }

            if (! $doctrineTable->hasIndex('lead_status_estimated')) {
                $table->index('lead_status_estimated', 'lead_status_estimated');
            }

            if (! $doctrineTable->hasIndex('lead_status_for_approval')) {
                $table->index('lead_status_for_approval', 'lead_status_for_approval');
            }

        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropIndex('lead_status_id');
            $table->dropIndex('lead_postpone_date');
        });

        Schema::table('lead_statuses', function (Blueprint $table) {
            $sm = Schema::getConnection()->getDoctrineSchemaManager();
            $doctrineTable = $sm->listTableDetails('lead_statuses');

            if ($doctrineTable->hasIndex('lead_status_declined')) {
                $table->dropIndex('lead_status_declined');
            }
            if ($doctrineTable->hasIndex('lead_status_default')) {
                $table->dropIndex('lead_status_default');
            }

            if ($doctrineTable->hasIndex('lead_status_estimated')) {
                $table->dropIndex('lead_status_estimated');
            }

            if ($doctrineTable->hasIndex('lead_status_for_approval')) {
                $table->dropIndex('lead_status_for_approval');
            }

        });

    }
}
