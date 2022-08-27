<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class TasksWithoutAddressFix extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('client_tasks')
            ->whereNull(['task_address', 'task_city', 'task_state', 'task_zip', 'task_country'])
            ->update([
                'task_address' => config_item('office_address'),
                'task_city' => config_item('office_city'),
                'task_state' => config_item('office_state'),
                'task_zip' => config_item('task_zip'),
                'task_country' => config_item('office_country'),
                'task_latitude' => config_item('office_lat'),
                'task_longitude' => config_item('office_lon')
            ]);
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
