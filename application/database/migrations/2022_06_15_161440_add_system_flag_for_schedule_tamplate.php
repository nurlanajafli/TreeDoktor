<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSystemFlagForScheduleTamplate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('email_templates')->where('system_label', 'stump_grinding_schedule')->update(['email_system_template' => 1]);
        DB::table('email_templates')->where('system_label', 'tree_work_reschedule')->update(['email_system_template' => 1]);
        DB::table('email_templates')->where('system_label', 'firewood_delivery_for')->update(['email_system_template' => 1]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('email_templates')->where('system_label', 'stump_grinding_schedule')->update(['email_system_template' => 0]);
        DB::table('email_templates')->where('system_label', 'tree_work_reschedule')->update(['email_system_template' => 0]);
        DB::table('email_templates')->where('system_label', 'firewood_delivery_for')->update(['email_system_template' => 0]);
    }
}
