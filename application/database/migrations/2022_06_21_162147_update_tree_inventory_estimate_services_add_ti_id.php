<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateTreeInventoryEstimateServicesAddTiId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tree_inventory_estimate_services', function (Blueprint $table) {
            $table->integer('ti_id')->nullable()->default(null)->index('ti_id');
        });

        DB::statement("
        UPDATE
            tree_inventory_estimate_services AS ties,
            (
            SELECT
                tree_inventory_estimate_services.ties_id AS ties_id,
                tree_inventory.ti_id
            FROM
                tree_inventory
            LEFT JOIN tree_inventory_estimate_services ON tree_inventory.ti_tree_number = tree_inventory_estimate_services.ties_number AND tree_inventory.ti_tree_type = tree_inventory_estimate_services.ties_type AND tree_inventory.ti_size = tree_inventory_estimate_services.ties_size AND tree_inventory.ti_tree_priority = tree_inventory_estimate_services.ties_priority AND tree_inventory.ti_cost = tree_inventory_estimate_services.ties_cost AND tree_inventory.ti_stump_cost = tree_inventory_estimate_services.ties_stump_cost
            LEFT JOIN estimates_services ON tree_inventory_estimate_services.ties_estimate_service_id = estimates_services.id
            LEFT JOIN estimates ON estimates_services.estimate_id = estimates.estimate_id
            WHERE
                estimates.client_id = tree_inventory.ti_client_id
        ) T
        SET
            ties.ti_id = T.ti_id
        WHERE
            ties.ties_id = T.ties_id;
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tree_inventory_estimate_services', function (Blueprint $table) {
            $table->dropColumn(['ti_id']);
            $table->dropIndex('ti_id');
        });
    }
}
