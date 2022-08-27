<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

class CreateEquipmentPartsTable extends Migration
{
    public $withinTransaction = false;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::rename('equipment_parts', 'equipment_parts_old');
        Schema::create('equipment_parts', function (Blueprint $table) {
            $table->id('part_id');
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('eq_id')->index();
            $table->unsignedBigInteger('repair_id')->nullable()->index();
            $table->unsignedBigInteger('service_report_id')->nullable()->index();
            $table->string('part_name');
            $table->string('part_number');
            $table->string('part_seller')->nullable();
            $table->decimal('part_price', 10, 2)->default(0);
            $table->text('part_description')->nullable();
            $table->date('part_purchased_date')->nullable();
            $table->dateTime('part_created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        });

        $fields = DB::table('equipment_parts_old')->get();
        foreach ($fields as $field) {
            DB::table('equipment_parts')->insert([
                'part_id' => $field->part_id,
                'user_id' => 0,
                'eq_id' => $field->part_item_id,
                'part_name' => $field->part_name,
                'part_number' => 'empty',
                'part_price' => $field->part_price,
                'part_description' => $field->part_seller,
                'part_purchased_date' => Carbon::createFromTimestamp($field->part_date)->toDateTimeString(),
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
        Schema::dropIfExists('equipment_parts');
        Schema::rename('equipment_parts_old', 'equipment_parts');
    }
}
