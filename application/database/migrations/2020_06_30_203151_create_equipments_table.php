<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

class CreateEquipmentsTable extends Migration
{
    /**
     * Run the migrations.
     * * * * *
     * @return void
     */
    public function up()
    {
        Schema::create('equipments', function (Blueprint $table) {
            $table->id('eq_id');
            $table->unsignedBigInteger('group_id')->index();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('eq_name');
            $table->string('eq_code');
            $table->string('eq_serial')->nullable();
            $table->text('eq_description')->nullable();
            $table->string('eq_photo')->nullable();
            $table->boolean('eq_schedule')->default(false);
            $table->boolean('eq_repair')->default(false);
            $table->decimal('eq_cost', 10, 2)->nullable();
            $table->date('eq_purchased_date')->nullable();
            $table->integer('eq_counter_type')->nullable();
            $table->string('eq_license_plate')->nullable();
            $table->year('eq_year')->nullable();
            $table->string('eq_make')->nullable();
            $table->string('eq_model')->nullable();
            $table->string('eq_color', 7)->nullable();
            $table->string('eq_drive_license_req')->nullable();
            $table->string('eq_gps_id')->nullable();
            $table->unsignedBigInteger('eq_gps_start_counter')->nullable();
            $table->dateTime('eq_gps_start_date')->nullable();
            $table->dateTime('eq_created_at')->nullable();
        });

        $fields = DB::table('equipment_items')->get();
        foreach ($fields as $field) {
            DB::table('equipments')->insert([
                'eq_id' => $field->item_id,
                'group_id' => $field->group_id,
                'eq_name' => $field->item_name,
                'eq_code' => $field->item_code,
                'eq_serial' => $field->item_serial,
                'eq_description' => $field->item_description,
                'eq_schedule' => $field->item_schedule,
                'eq_repair' => $field->item_repair,
                'eq_counter_type' => \application\modules\equipment\models\Equipment::COUNTER_TYPE_DISTANCE,
                'eq_gps_id' => $field->item_tracker_name,
                'eq_gps_start_counter' => $field->item_gps_start_counter,
                'eq_gps_start_date' => $field->item_gps_start_date,
                'eq_created_at' => Carbon::createFromTimestamp($field->item_date)->toDateTimeString()
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('equipments');
    }
}
