<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEquipmentGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('equipment_groups', function (Blueprint $table) {
            $table->dateTime('group_created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->string('group_prefix')->after('group_name')->nullable();
        });
        $fields = DB::table('equipment_groups')->get();
        foreach ($fields as $field) {
            DB::table('equipment_groups')->update([
                'group_created_at' => $field->group_date_created,
            ]);
        }
        Schema::table('equipment_groups', function (Blueprint $table) {
            $table->dropColumn('group_date_created');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('equipment_groups', function (Blueprint $table) {
            $table->date('group_date_created');
            $table->dropColumn('group_prefix');
        });
        $fields = DB::table('equipment_groups')->get();
        foreach ($fields as $field) {
            DB::table('equipment_groups')->update([
                'group_date_created' => $field->group_created_at,
            ]);
        }
        Schema::table('equipment_groups', function (Blueprint $table) {
            $table->dropColumn('group_created_at');
        });
    }
}
