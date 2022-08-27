<?php

use application\modules\equipment\models\EquipmentService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Carbon;


class MigrateServicesToNew extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('equipment_services')->truncate();
        DB::table('equipment_counters')->truncate();

        $fields = DB::table('equipment_services_setting')
            ->select([
                'equipment_services_setting.*',
                'equipment_service_types.service_type_name',
                'equipment_service_types.service_type_description'
            ])
            ->leftJoin(
                'equipment_service_types',
                'equipment_services_setting.service_type_id',
                '=',
                'equipment_service_types.service_type_id')
            ->get();
        foreach ($fields as $field) {
            $serviceStart = $field->service_start !== "0000-00-00 00:00:00" && $field->service_start !== null
                ? $field->service_start
                : $field->service_last_update;
            $serviceMonth = $field->service_postpone_on === "0" ? $field->service_period_months : $field->service_postpone_on;
            $nextDate = Carbon::createFromFormat('Y-m-d H:i:s', $serviceStart)
                ->addMonths($serviceMonth)->toDateString();
            $datePeriodType = EquipmentService::DATE_PERIOD_TYPE_MONTH;
            $counterPeriod = $field->service_period_kilometers !== "0" && $field->service_period_kilometers !== null
                ? $field->service_period_kilometers
                : null;
            DB::table('equipment_services')->insert([
                'service_id' => $field->id,
                'service_type_id' => $field->service_type_id,
                'eq_id' => $field->item_id,
                'user_id' => 0,
                'service_name' => $field->service_type_name,
                'service_description' => $field->service_type_description,
                'service_date_period_type' => $datePeriodType,
                'service_date_period' => $field->service_period_months,
                'service_counter_period' => $counterPeriod,
                'service_start_date' => $serviceStart,
                'service_next_date' => $nextDate,
                'service_next_counter' => null,
                'service_created_at' => Carbon::createFromFormat('Y-m-d H:i:s',
                    $field->service_created)->toDateTimeString(),
            ]);
        }

        $cs = [];
        $fields = DB::table('equipment_services_reports')->get();
        foreach ($fields as $field) {
            $date = Carbon::createFromFormat('Y-m-d H:i:s',
                $field->report_date_created);
            if (!empty($field->report_counter_kilometers_value)) {
                $counter = (int)$field->report_counter_kilometers_value;
                $cs[$field->report_item_id][] = [
                    'user_id' => $field->report_create_user ?: 0,
                    'eq_id' => $field->report_item_id,
                    'service_report_id' => $field->report_id,
                    'repair_id' => null,
                    'counter_date' => $date,
                    'counter_value' => $counter,
                    'counter_note' => 'migrate from old module',
                    'counter_created_at' => $date,
                ];
            }
        }

        $fields = DB::table('repair_requests')->get();
        foreach ($fields as $field) {
            $date = Carbon::createFromFormat('Y-m-d H:i:s',
                $field->repair_date);
            if (!empty($field->repair_counter) && $field->repair_counter !== "0") {
                $counter = (int)$field->repair_counter;
                $cs[$field->repair_item_id][] = [
                    'user_id' => $field->repair_author_id ?: 0,
                    'eq_id' => $field->repair_item_id,
                    'service_report_id' => null,
                    'repair_id' => $field->repair_id,
                    'counter_date' => $date,
                    'counter_value' => $counter,
                    'counter_note' => 'migrate from old module',
                    'counter_created_at' => $date,
                ];
            }
        }

        foreach ($cs as $item) {
            $coll = collect($item)->sortBy('counter_date')->values()->all();
            foreach ($coll as $ins) {
                $ins['counter_date'] = $ins['counter_date']->toDateString();
                $ins['counter_created_at'] = $ins['counter_created_at']->toDateTimeString();
                DB::table('equipment_counters')->insert($ins);
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //DB::table('equipment_services')->truncate();
    }
}
