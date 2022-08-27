<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexToLeadServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('lead_services', function(Blueprint $table)
        {
            $table->index('lead_id');
            $table->index('services_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('lead_services', function (Blueprint $table)
        {
            $table->dropIndex(['lead_id', 'services_id']);
        });
    }
}
