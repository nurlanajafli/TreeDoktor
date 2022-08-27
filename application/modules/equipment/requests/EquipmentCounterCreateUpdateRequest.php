<?php


namespace application\modules\equipment\requests;


use application\modules\equipment\models\EquipmentCounter;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;

class EquipmentCounterCreateUpdateRequest extends FormRequest
{
    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        $this->merge([
            'counter_value' => (int)$this->counter_value,
            'counter_date' => isset($this->counter_date) ? $this->counter_date : Carbon::now()->startOfDay()->format(getDateFormat()),
            'user_id' => isset($this->user_id) ? $this->user_id : $this->user()->id,
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
            'counter_value' => 'required|integer',
            'user_id' => 'required|exists:users,id',
            'eq_id' => 'required|exists:equipment,eq_id',
            'repair_id' => 'exists:equipment_repairs,repair_id',
            'service_report_id' => 'exists:equipment_service_reports,service_report_id',
            'counter_date' => 'date_format:"' . getDateFormat() . '"|nullable',
            'counter_note' => '',
        ];
        $ci = get_instance();
        if ($ci->router->method === 'ajax_update_counter') {
            $rules['counter_id'] = 'required|exists:equipment_counters,counter_id';
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