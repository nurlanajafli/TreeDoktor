<?php
namespace application\modules\workorders\requests\status;
use Illuminate\Foundation\Http\FormRequest;

class SaveStatusRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if(!isset($this->wo_status_active)){
            $this->merge(['wo_status_active'=>1]);
        }

        $is_protected = 0;
        if($this->is_finished_by_field || $this->is_confirm_by_client || $this->is_delete_invoice){
            $is_protected = 1;
        }
        $this->merge(['is_protected'=>$is_protected]);

        $wo_status_use_team_color = 0;
        if($this->status_color_type=='wo_status_use_team_color')
            $wo_status_use_team_color = 1;

        $wo_status_use_estimator_color = 0;
        if($this->status_color_type=='wo_status_use_estimator_color')
            $wo_status_use_estimator_color = 1;

        if(!isset($this->wo_status_color) || $wo_status_use_team_color || $wo_status_use_estimator_color)
            $this->merge(['wo_status_color'=>'']);


        $this->merge([
            'wo_status_use_team_color'=>$wo_status_use_team_color,
            'wo_status_use_estimator_color'=>$wo_status_use_estimator_color
        ]);

    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'wo_status_name'=>'required'
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'wo_status_name' => 'Name',
        ];
    }

    public function messages()
    {
        $messages = parent::messages();
        return $messages;
    }
}