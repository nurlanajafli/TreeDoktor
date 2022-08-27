<?php

namespace application\modules\schedule\requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use application\modules\schedule\Rules\NoBusyMembers;
use application\modules\schedule\Rules\NoBusyItems;
use application\modules\schedule\Rules\TeamEventsDateStart;
use application\modules\schedule\Rules\TeamEventsDateEnd;
use DateTime;
class ScheduleTeamRequest extends FormRequest
{
    protected $map = [
        'team_crew_id' => 'team_crew_id',
        'team_type' => 'team_crew_id',
        'team_date_start' => 'team_date_start',
        'team_date_end' => 'team_date_end',
        'team_leader' => 'team_leader_user_id',
        'team_color' => 'team_color',
        'team_members' => 'team_members',
        'team_items' => 'team_items',
        'team_note' => 'team_note',
        'team_tools' => 'team_tools'
    ];

    protected function prepareForValidation(): void
    {
        $remove = [];
        foreach($this->map as $original => $new) {
            if(!isset($this->$original))
                continue;

            $value = $this->$original;

            if($original=='team_members' || $original=='team_items'){

                if(is_string($value) && !empty($value))
                    $value = explode(',', $value);

                if(!$this->has('team_members')){
                    $equipment = $members = [];
                    foreach ($this->team_items as $key => $item){
                        if(!isset($item['type']))
                            continue;
                        if($item['type']=='user')
                            $members[$item['id']] = ['weight'=>$key];
                        else
                            $equipment[$item['id']] = ['weight'=>$key];
                    }

                    $value = collect($equipment)->keys()->toArray();
                    $this->merge(['team_items'=>$value]);
                    $this->merge(['team_members'=>collect($members)->keys()->toArray()]);
                    $this->merge(['team_items_assoc'=>$equipment]);
                    $this->merge(['team_members_assoc'=>$members]);
                }

                if(!$this->has('team_items')){
                    $this->merge(['team_items'=>[]]);
                }
            }

            if($original=='team_date_start' || $original=='team_date_end') {
                if(is_numeric($value))
                    $value = date('Y-m-d', $value);
                elseif(DateTime::createFromFormat(getDateFormat(), $value))
                    $value = DateTime::createFromFormat(getDateFormat(), $value)->format('Y-m-d');
            }

            $this->request->set($new, $value);
            $this->merge([$new=>$value]);
            if($original != $new){
                $this->request->remove($original);
                $this->offsetUnset($original);
            }
        }
        if(!$this->has('team_members')){
            $this->merge(['team_members'=>[]]);
        }
        if(!$this->has('team_items')){
            $this->merge(['team_items'=>[]]);
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
            'team_date_start' => ['required', new TeamEventsDateStart($this)],
            'team_date_end' => ['required', new TeamEventsDateEnd($this)],
            'team_crew_id' => 'required',
            'team_leader_user_id' => 'nullable|numeric',
            'team_color' => 'nullable',
            'team_members' => ['required_unless:mode,unit', new NoBusyMembers($this)],
            'team_items' => ['nullable', new NoBusyItems($this)]
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
            'team_date_start' => 'From',
            'team_date_end' => 'To',
            'team_crew_id' => 'required',
            'team_leader_user_id' => 'Team Leader',
            'team_color' => 'required',
            'team_members' => 'Members',
            'team_items' => 'nullable'
        ];
    }

    public function messages()
    {
        $messages = parent::messages();
        return $messages;
    }
}