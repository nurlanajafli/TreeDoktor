<?php


namespace application\modules\schedule\requests;
use Illuminate\Foundation\Http\FormRequest;

class TeamChangeOrder extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if(count($this->input('members', []))){
            $members = collect($this->members)->keyBy('user_id')->transform(function($item) {
                return collect($item)->except('user_id')->toArray();
            });
            $this->merge(['members' => $members]);
        }

        if(count($this->input('equipments', []))){
            $equipment = collect($this->equipments)->keyBy('equipment_id')->transform(function($item) {
                return collect($item)->except('equipment_id')->toArray();
            });
            $this->merge(['equipments'=>$equipment]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'team_id' => 'required'
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
            'team_id' => "Team"
        ];
    }

    public function messages()
    {
        $messages = parent::messages();
        return $messages;
    }
}