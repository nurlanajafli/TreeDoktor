<?php


namespace application\modules\schedule\requests;
use Illuminate\Foundation\Http\FormRequest;
use application\modules\schedule\Rules\FreeTimeInterval;
use Illuminate\Support\Str;
use DateTime;
use application\modules\schedule\models\ScheduleEvent;

class ScheduleSaveEventRequest extends FormRequest
{
    protected $defaults = [

    ];

    protected $map = [
        'id'=>'id',
        'start_date'=>'event_start',
        'end_date'=>'event_end',
        'text'=>'text',
        'team_leader_user_id'=>'team_leader_user_id',

        'event_crew_id'=>'event_crew_id',
        'crew_id'=>'crew_id',
        'team_id'=>'event_team_id',
        'section_id'=>'section_id',

        'event_damage'=> 'event_damage',
        'details'=> 'details',
        'mode'=> 'mode',
        'wo_id' => 'event_wo_id',
        'services' => 'event_services',
        'note'=>'note',
        'color'=>'color',
        'my_template'=>'my_template',
        '!nativeeditor_status'=>'!nativeeditor_status',
        /*'ids'=> 'ids',*/
    ];

    protected function prepareForValidation(): void
    {
        $this->mapper();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'event_team_id'=>[new FreeTimeInterval($this)]
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
            'id' => 'Event Id',
        ];
    }

    public function messages()
    {
        $messages = parent::messages();
        return $messages;
    }

    private function mapper($key = null){
        $prefix = (isset($this->ids))?$this->ids.'_':'';

        foreach($this->map as $original => $new) {
            $field = $prefix.$original;
            if(!isset($this->$field))
                continue;

            $value = $this->$field;
            if($original=='start_date' || $original=='end_date'){
                $value = strtotime($value);
            }

            if($original=='services' && !is_array($value)){
                $value = json_decode($value, true);
                $value = is_array($value)?array_values($value):[];
            }

            if($original=='section_id'){
                $this->merge(['event_team_id'=>$value]);
            }

            $this->merge([$new=>$value]);
            if($field!=$new){
                $this->request->remove($field);
                unset($this[$field]);
            }
        }

        if(!isset($this->id) || !$this->id){
            $id = 0;
            $scheduleEvent = ScheduleEvent::latest('id')->first();
            if($scheduleEvent)
                $id = $scheduleEvent->id;

            $id ++;
            $this->id = $id;
            $this->request->set('id', $id);
            $this->merge(['id'=>$id]);
        }

        if($this->mode && $this->mode=='timeline'){
            $team_id = ($this->event_crew_id)?$this->event_crew_id:$this->crew_id;
            $this->merge(['event_team_id'=>$team_id]);

            if(!$this->team_leader_user_id){
                $this->merge(['team_leader_user_id'=>$this->section_id]);
            }
        }

        foreach ($this->all() as $key => $value){
            if(is_numeric($key)){
                $this->request->remove($key);
                unset($this[$key]);
            }
        }
    }
}