<?php


namespace application\modules\equipment\requests;


use application\core\Rules\ArrayKeyIn;
use application\modules\equipment\models\Equipment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EquipmentRequest extends FormRequest
{
    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        //
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'page' => 'numeric',
            'sort' => 'array',
            'sort.0' => [Rule::in(array_keys(Equipment::COLUMNS))],
            'sort.1' => [Rule::in(['asc', 'desc'])],
            'query' => 'string',
            'filter' => 'array',
            'where' => 'array',
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