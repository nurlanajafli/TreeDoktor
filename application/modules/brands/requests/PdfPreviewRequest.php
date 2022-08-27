<?php


namespace application\modules\brands\requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use application\modules\brands\models\Brand;
class PdfPreviewRequest extends FormRequest
{
    public function rules()
    {
        return [
            'pdf_data'=>'required'
        ];
    }

}