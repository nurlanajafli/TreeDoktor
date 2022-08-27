<?php


namespace application\modules\brands\requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use application\modules\brands\models\Brand;
class BrandRequest extends FormRequest
{
    public function rules()
    {
        $rules = [
            'b_name'  => 'required|min:3',
            'b_company_address' => 'required',
            'b_company_city'    => 'required',
            'b_company_state'   => 'required',
            'b_company_zip'     => 'required',
            'b_company_country' => 'required',
            'bc_phone'          => 'required',
            'bc_email'          => 'required',
            'bc_site'           => 'url'
        ];

        return $rules;
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        return Brand::COLUMNS;
    }
}