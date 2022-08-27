<?php


namespace application\modules\schedule\requests;

use application\modules\schedule\models\ScheduleTeams;
use application\modules\schedule\models\ScheduleTeamsEquipment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use application\modules\schedule\Rules\NoBusyMembers;
use application\modules\schedule\Rules\NoBusyItems;
use application\modules\schedule\Rules\TeamEventsDateStart;
use application\modules\schedule\Rules\TeamEventsDateEnd;
use DateTime;
use application\modules\crew\models\Crew;
use application\modules\schedule\models\ScheduleTeamsMember;


class ScheduleSaveEventTeamRequest extends FormRequest
{
    protected $map = [
        'start_date'=>'team_date_start',
        'end_date'=>'team_date_end',
        'team_leader_user_id'=>'team_leader_user_id',
        'event_crew_id'=>'event_crew_id',
        'crew_id'=>'crew_id',
        'section_id'=>'section_id',
        'mode'=> 'mode',
    ];

    protected $filter = [
        'team' => 'team',
        'team_id'=>'team_id',
        'team_crew_id'=>'team_crew_id',
        'team_color'=>'team_color',
        'team_leader_user_id'=>'team_leader_user_id',
        'team_members'=>'team_members',
        'team_items'=>'team_items',
        'team_date_start'=>'team_date_start',
        'team_date_end'=>'team_date_end'
    ];

    protected function prepareForValidation(): void
    {
        $prefix = $this->ids.'_';
        foreach($this->map as $original => $new) {
            $field = $prefix.$original;
            if(!isset($this->$field))
                continue;

            $value = $this->$field;
            if($original=='start_date' || $original=='end_date'){
                $value = date("Y-m-d", strtotime($value));
            }
            if($original=='section_id'){
                $this->request->set('team_id', $value);
            }
            $this->request->set($new, $value);
            $this->request->remove($field);
        }

        if($this->mode=='timeline'){
            $team_id = ($this->event_crew_id)?$this->event_crew_id:$this->crew_id;
            $this->request->set('team_id', $team_id);

            if(!$this->team_leader_user_id){
                $this->request->set('team_leader_user_id', $this->section_id);
            }
        }

        $this->request->set('team_crew_id', Crew::active()->noDayOff()->first()->crew_id);
        $this->request->set('team_color', ScheduleTeams::DEFAULT_TEAM_COLOR);
        $this->request->set('team_members', [$this->team_leader_user_id]);
        $this->request->set('team_items', []);

        if($this->team_id) {
            $team = ScheduleTeams::with('members', 'equipment')->find($this->team_id);
            $this->request->set('team', $team);

            if($team->members->count())
                $this->request->set('team_members', $team->members->pluck('id'));

            if($team->equipment->count())
                $this->request->set('team_items', $team->equipment->pluck('eq_id'));

            $team_dates = ScheduleTeams::updateTeamDates($team, (object)[
                'team_date_start'   => strtotime($this->team_date_start),
                'team_date_end'     => strtotime($this->team_date_end)
            ]);

            $this->request->set('team_date_start', $team_dates['team_date_start']);
            $this->request->set('team_date_end', $team_dates['team_date_end']);
        }

        foreach ($this->request->all() as $key => $value){
            if(!isset($this->filter[$key])){
                $this->request->remove($key);
            }
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
            'team_members' => ['required', new NoBusyMembers($this)],
            'team_items' => ['nullable', new NoBusyItems($this)]
        ];
    }

    public function attributes()
    {
        return [
            'team_date_start' => 'From',
            'team_date_end' => 'To',
            'team_crew_id' => 'required',
            'team_leader_user_id' => 'nullable|required',
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