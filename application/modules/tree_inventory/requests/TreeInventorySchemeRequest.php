<?php

namespace application\modules\tree_inventory\requests;

use application\core\Rules\ArrayKeyIn;
use application\modules\equipment\models\Equipment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TreeInventorySchemeRequest extends FormRequest
{
    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        $date = date('Y-m-d H:i:s');
        $array = ['updated_at' => $date];
        if(empty($this->tis_id))
            $array['created_at'] = $date;

        $this->merge($array);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'tis_name' => 'required|string',
            'tis_client_id' => 'required|exists:clients,client_id',
            'tis_address' => 'string',
            'tis_city' => 'string',
            'tis_state' => 'string',
            'tis_zip' => 'string',
            'tis_country' => 'string',
            'tis_lat' => 'numeric',
            'tis_lng' => 'numeric',
            'created_at' => 'date',
            'updated_at' => 'date',
        ];
    }
}