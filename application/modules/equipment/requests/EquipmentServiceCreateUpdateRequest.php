<?php


namespace application\modules\equipment\requests;


use application\modules\equipment\models\EquipmentCounter;
use application\modules\equipment\models\EquipmentService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class EquipmentServiceCreateUpdateRequest extends FormRequest
{
    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        $this->merge([
            'service_counter_period' => empty($this->service_counter_period) ? null : (int)$this->service_counter_period,
            'service_date_period' => (int)$this->service_date_period,
            'service_date_period_type' => (int)$this->service_date_period_type,
            'service_start_date' => isset($this->service_start_date)
                ? Carbon::createFromFormat(getDateFormat(), $this->service_start_date)->startOfDay()
                : Carbon::now()->startOfDay(),
            'user_id' => isset($this->user_id) ? $this->user_id : $this->user()->id,
        ]);
        $ci = get_instance();
        if ($ci->router->method === 'ajax_create_service') {
            $this->merge([
                'eq_id' => isset($this->eq_id) ? explode('|', $this->eq_id) : null,
            ]);
        }
        /** @var Carbon $service_next_date */
        $service_next_date = $this->service_start_date->copy();
        switch ($this->service_date_period_type) {
            case EquipmentService::DATE_PERIOD_TYPE_DAY:
                $service_next_date->addDays($this->service_date_period);
                break;
            case EquipmentService::DATE_PERIOD_TYPE_WEEK:
                $service_next_date->addWeeks($this->service_date_period);
                break;
            case EquipmentService::DATE_PERIOD_TYPE_YEAR:
                $service_next_date->addYears($this->service_date_period);
                break;
            case EquipmentService::DATE_PERIOD_TYPE_MONTH:
            default:
                $service_next_date->addMonths($this->service_date_period);
                break;
        }
        $this->merge([
            'service_next_date' => $service_next_date
        ]);

        $this->merge(array_map(function ($v) {
            return $v === "" ? null : $v;
        }, $this->all()));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'service_type_id' => 'exists:equipment_service_types,service_type_id',
            'service_description' => '',
            'service_date_period_type' => ['required', Rule::in(array_keys(EquipmentService::DATE_PERIOD_TYPES))],
            'service_date_period' => 'required|integer',
            'service_counter_period' => 'integer|nullable',
            'eq_id' => 'required|array|exists:equipment,eq_id',
            'user_id' => 'required|exists:users,id',
            'service_start_date' => 'date',
            'service_next_date' => 'date'
        ];
        $ci = get_instance();
        if ($ci->router->method === 'ajax_update_service') {
            $rules['service_id'] = 'required|exists:equipment_services,service_id';
            $rules['eq_id'] = 'required|exists:equipment,eq_id';
        }
        return $rules;
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        return EquipmentCounter::COLUMNS;
    }
}