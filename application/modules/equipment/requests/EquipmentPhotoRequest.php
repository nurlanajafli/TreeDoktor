<?php


namespace application\modules\equipment\requests;


use application\modules\equipment\models\Equipment;
use Illuminate\Foundation\Http\FormRequest;

class EquipmentPhotoRequest extends FormRequest
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
            'eq_id' => 'required|exists:equipment,eq_id',
            'thumb' => 'required|file|image'
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