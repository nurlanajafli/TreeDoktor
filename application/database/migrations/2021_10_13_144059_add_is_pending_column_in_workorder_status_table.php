<?php

use application\modules\workorders\models\WorkorderStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsPendingColumnInWorkorderStatusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('workorder_status', function (Blueprint $table) {
            $table->tinyInteger('is_scheduled_pending')->default(0);
        });
        WorkorderStatus::where('wo_status_name', 'Scheduled - Pending')->update(['is_scheduled_pending' => 1]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('workorder_status', function (Blueprint $table) {
            $table->dropColumn('is_scheduled_pending');
        });
    }
}
