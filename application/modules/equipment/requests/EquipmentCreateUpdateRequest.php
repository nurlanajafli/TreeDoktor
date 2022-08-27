<?php


namespace application\modules\equipment\requests;


use application\modules\equipment\models\Equipment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EquipmentCreateUpdateRequest extends FormRequest
{
    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        $this->merge([
            'eq_name' => trim(strip_tags($this->eq_name)),
            'eq_schedule' => isset($this->eq_schedule) && $this->eq_schedule === "on" ? 1 : 0,
            'eq_repair' => isset($this->eq_repair) && $this->eq_repair === "on" ? 1 : 0,
            'eq_schedule_tool' => isset($this->eq_schedule_tool) && $this->eq_schedule_tool === "on" ? 1 : 0,
            'eq_cost' => ($this->eq_cost === "") ? null : (float)$this->eq_cost,
            'eq_code' => $this->eq_prefix . '-' . $this->eq_code_num,
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
            'eq_name' => 'required|max:128',
            'group_id' => 'required',
            'eq_serial' => 'required|max:128',
            'eq_prefix' => 'required',
            'eq_code_num' => 'required',
            'eq_code' => 'required|unique:equipment,eq_code',
            'eq_description' => '',
            'eq_photo' => '',
            'eq_schedule' => 'boolean',
            'eq_schedule_tool' => 'boolean',
            'eq_repair' => 'boolean',
            'eq_cost' => '',
            'eq_purchased_date' => 'date_format:"' . getDateFormat() . '"|nullable',
            'eq_counter_type' => '',
            'eq_license_plate' => '',
            'eq_year' => '',
            'eq_make' => '',
            'eq_model' => '',
            'eq_color' => '',
            'file' => 'file|image'
        ];
        $ci = get_instance();
        if ($ci->router->method === 'ajax_update_item') {
            $rules['eq_id'] = 'required|exists:equipment,eq_id';
            $rules['eq_code'] = [
                'required',
                Rule::unique('equipment')->ignore($this->eq_id, 'eq_id')
            ];
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
        return Equipment::COLUMNS;
    }
}
