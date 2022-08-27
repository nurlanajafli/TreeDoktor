<?php


namespace application\modules\clients\requests;
use Illuminate\Foundation\Http\FormRequest;

class ClientLetterRequest extends FormRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'email_template_id' => 'numeric',
            'client_id' => 'numeric',
            'estimate_id' => 'numeric',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        return []; //Equipment::COLUMNS;
    }
}