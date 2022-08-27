<?php

use application\modules\equipment\models\EquipmentServiceType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEquipmentServiceTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('equipment_service_types', function (Blueprint $table) {
            $table->unsignedInteger('service_type_form')->default(EquipmentServiceType::FORM_SERVICE)->after('equipment_service_id');
            $table->bigInteger('equipment_service_id')->change();
            $table->renameColumn('equipment_service_id', 'service_type_id');
            $table->renameColumn('equipment_service_type', 'service_type_name');
            $table->renameColumn('equipment_service_desc', 'service_type_description');
            $table->dateTime('service_type_created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('equipment_service_types', function (Blueprint $table) {
            $table->dropColumn('service_type_form');
            $table->integer('service_type_id')->change();
            $table->renameColumn('service_type_id', 'equipment_service_id');
            $table->renameColumn('service_type_name', 'equipment_service_type');
            $table->renameColumn('service_type_description', 'equipment_service_desc');
            $table->dropColumn('service_type_created_at');
        });
    }
}
