<?php

use application\modules\equipment\models\Equipment;
use application\modules\equipment\models\EquipmentNote;
use application\modules\equipment\models\EquipmentRepair;
use application\modules\equipment\models\EquipmentService;
use application\modules\equipment\models\EquipmentServiceReport;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Carbon;

class MigrateOldServices extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('equipment_services')->truncate();
        DB::table('equipment_service_reports')->truncate();
        DB::table('equipment_repairs')->truncate();
        DB::table('equipment_counters')->truncate();
        DB::table('equipment_employees')->truncate();
        DB::table('equipment_notes')->truncate();

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
            $serviceStart = !empty($field->service_start) ? $field->service_start : $field->service_last_update;
            $serviceMonth = $field->service_postpone_on == 0 ? $field->service_period_months : $field->service_postpone_on;
            $nextDate = Carbon::createFromFormat('Y-m-d H:i:s', $serviceStart)
                ->addMonths($serviceMonth)->toDateString();
            $datePeriodType = EquipmentService::DATE_PERIOD_TYPE_MONTH;
            $counterPeriodType = EquipmentService::COUNTER_PERIOD_TYPE_DISTANCE;
            $counterPeriod = !empty($field->service_period_kilometers) ? $field->service_period_kilometers : null;
            if (!empty($field->service_period_hours)) {
                $counterPeriodType = EquipmentService::COUNTER_PERIOD_TYPE_HOURS;
                $counterPeriod = $field->service_period_hours;
            }
            DB::table('equipment_services')->insert([
                'service_id' => $field->id,
                'service_type_id' => $field->service_type_id,
                'eq_id' => $field->item_id,
                'user_id' => 0,
                'service_name' => $field->service_type_name,
                'service_description' => $field->service_type_description,
                'service_date_period_type' => $datePeriodType,
                'service_date_period' => $field->service_period_months,
                'service_counter_period_type' => $counterPeriodType,
                'service_counter_period' => $counterPeriod,
                'service_next_date' => $nextDate,
                'service_next_counter' => null,
                'service_created_at' => Carbon::createFromFormat('Y-m-d H:i:s',
                    $field->service_created)->toDateTimeString(),
            ]);
        }

        $fields = DB::table('equipment_services_reports')->get();
        foreach ($fields as $field) {
            $reportType = EquipmentServiceReport::TYPE_COMPLETED;
            $postponedTo = null;
            if (!empty($field->report_kind)) {
                $reportType = EquipmentServiceReport::TYPE_POSTPONED;
                $postponedTo = Carbon::createFromFormat('Y-m-d H:i:s',
                    $field->report_date_created)->addMonths($field->report_kind)->toDateString();
            }
            $counter = (int)$field->report_counter_kilometers_value;
            if (!empty((int)$field->report_counter_hours_value)) {
                $counter = (int)$field->report_counter_hours_value;
                DB::table('equipments')
                    ->where('eq_id', '=', $field->report_item_id)
                    ->update(['eq_counter_type' => Equipment::COUNTER_TYPE_HOURS]);
            }
            DB::table('equipment_service_reports')->insert([
                'service_report_id' => $field->report_id,
                'user_id' => $field->report_create_user ?: 0,
                'eq_id' => $field->report_item_id,
                'service_type_id' => $field->report_service_id,
                'service_id' => $field->report_service_settings_id,
                'service_report_type' => $reportType,
                'service_report_postponed_to' => $postponedTo,
                'service_report_note' => $field->report_comment,
                'service_report_created_at' => Carbon::createFromFormat('Y-m-d H:i:s',
                    $field->report_date_created)->toDateTimeString(),
            ]);
            if (!empty($counter)) {
                DB::table('equipment_counters')->insert([
                    'user_id' => $field->report_create_user ?: 0,
                    'eq_id' => $field->report_item_id,
                    'repair_id' => null,
                    'service_report_id' => $field->report_id,
                    'counter_value' => $counter,
                    'counter_note' => 'migrate from old module',
                    'counter_created_at' => $field->report_date_created,
                ]);
            }
            if ($field->report_hours != 0 || $field->report_cost != 0) {
                $hours = abs((float)$field->report_hours);
                if ($hours == 0) {
                    $hours = 1;
                }
                $rate = round($field->report_cost / $hours, 2);

                DB::table('equipment_employees')->insert([
                    'user_id' => $field->report_create_user ?: 0,
                    'repair_id' => null,
                    'service_report_id' => $field->report_id,
                    'emp_hours' => $hours,
                    'emp_hourly_rate' => $rate,
                    'emp_worked_at' => Carbon::createFromFormat('Y-m-d H:i:s',
                        $field->report_date_created)->toDateString(),
                    'emp_created_at' => $field->report_date_created,
                ]);
            }
        }

        $statuses = DB::table('equipment_repair_statuses')->get();
        $types = DB::table('equipment_repair_types')->get();
        $fields = DB::table('repair_requests')
            ->where('repair_deleted', '=', 0)
            ->get();
        foreach ($fields as $field) {
            switch ($field->repair_type) {
                case 'maintenance':
                    $repairType = $types->where('repair_type_name', '=', 'Maintenance')->first()->repair_type_id;
                    break;
                case 'damage':
                    $repairType = $types->where('repair_type_name', '=', 'Damage')->first()->repair_type_id;
                    break;
                case 'repair':
                default:
                    $repairType = $types->where('repair_type_name', '=', 'Repair')->first()->repair_type_id;
                    break;
            }
            $repairCompleted = false;
            switch ($field->repair_status) {
                case 'repaired':
                    $repairStatus = $statuses->where('repair_status_flag_completed', '=', 1)->first()->repair_status_id;
                    $repairCompleted = true;
                    break;
                case 'on_hold':
                    $repairStatus = $statuses->where('repair_status_name', '=', 'On Hold')->first()->repair_status_id;
                    break;
                case 'not_repaired':
                default:
                    $repairStatus = $statuses->where('repair_status_flag_default', '=', 1)->first()->repair_status_id;
                    break;
            }

            DB::table('equipment_repairs')->insert([
                'repair_id' => $field->repair_id,
                'user_id' => $field->repair_author_id ?: 0,
                'eq_id' => $field->repair_item_id,
                'assigned_id' => !empty($field->repair_solder_id) ? $field->repair_solder_id : null,
                'repair_status_id' => $repairStatus,
                'repair_type_id' => $repairType,
                'repair_priority' => $field->repair_priority > 1 ? EquipmentRepair::PRIORITY_EMERGENCY : EquipmentRepair::PRIORITY_GENERAL,
                'repair_description' => $field->repair_first_comment,
                'repair_end_at' => $repairCompleted ? Carbon::createFromFormat('Y-m-d H:i:s',
                    $field->repair_date)->toDateTimeString() : null,
                'repair_end_note' => $field->repair_finish_comment,
            ]);
            if (!empty($field->repair_counter)) {
                DB::table('equipment_counters')->insert([
                    'user_id' => $field->repair_author_id ?: 0,
                    'eq_id' => $field->repair_item_id,
                    'repair_id' => $field->repair_id,
                    'service_report_id' => null,
                    'counter_value' => intval($field->repair_counter),
                    'counter_note' => 'migrate from old module',
                    'counter_created_at' => $field->repair_date,
                ]);
            }
            if ($field->repair_hours != 0 || $field->repair_price != 0) {
                $hours = abs((float)$field->repair_hours);
                if ($hours == 0) {
                    $hours = 1;
                }
                $rate = round($field->repair_price / $hours, 2);
                DB::table('equipment_employees')->insert([
                    'user_id' => $field->repair_solder_id ?: 0,
                    'repair_id' => $field->repair_id,
                    'service_report_id' => null,
                    'emp_hours' => $hours,
                    'emp_hourly_rate' => $rate,
                    'emp_worked_at' => Carbon::createFromFormat('Y-m-d H:i:s', $field->repair_date)->toDateString(),
                    'emp_created_at' => $field->repair_date,
                ]);
            }
        }

        $fields = DB::table('equipment_counters')
            ->select(['eq_id', DB::raw('MAX(counter_value) as counter_value')])
            ->groupBy('eq_id')
            ->get();

        foreach ($fields as $field) {
            $services = DB::table('equipment_services')
                ->where('eq_id', '=', $field->eq_id)
                ->whereNotNull('service_counter_period')
                ->get();
            foreach ($services as $service) {
                DB::table('equipment_services')
                    ->where('service_id', '=', $service->service_id)
                    ->update(['service_next_counter' => $field->counter_value + $service->service_counter_period]);
            }
        }

        $fields = DB::table('equipment_repair_notes')->get();
        foreach ($fields as $field) {
            DB::table('equipment_notes')->insert([
                'note_id' => $field->equipment_note_id,
                'note_parent_id' => null,
                'user_id' => $field->equipment_note_author,
                'eq_id' => $field->equipment_note_item_id,
                'repair_id' => $field->equipment_repair_id,
                'service_report_id' => null,
                'note_description' => $field->equipment_note_text,
                'note_type' => EquipmentNote::TYPE_INFO,
                'note_created_at' => $field->equipment_note_date,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public
    function down()
    {
        DB::table('equipment_services')->truncate();
        DB::table('equipment_service_reports')->truncate();
        DB::table('equipment_repairs')->truncate();
        DB::table('equipment_counters')->truncate();
        DB::table('equipment_employees')->truncate();
        DB::table('equipment_notes')->truncate();
    }
}
