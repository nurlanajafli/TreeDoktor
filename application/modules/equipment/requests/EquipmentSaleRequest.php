<?php


namespace application\modules\equipment\requests;


use application\modules\equipment\models\Equipment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EquipmentSaleRequest extends FormRequest
{
    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        $this->merge([
            'eq_sold_cost' => ($this->eq_sold_cost === "") ? null : (float)$this->eq_sold_cost,
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
        return [
            'eq_id' => 'required|exists:equipment,eq_id',
            'eq_sold_cost' => 'numeric',
        ];
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