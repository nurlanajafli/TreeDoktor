<?php
namespace application\modules\schedule\requests;

use Illuminate\Foundation\Http\FormRequest;
use DateTime;
use Carbon\CarbonPeriod;
use application\modules\schedule\models\ScheduleTeamsMember;

class ScheduleWeekCrewsRequest extends FormRequest
{
    protected $map = [
        'from'  =>  'from',
        'to'    =>  'to',
        'mode'  =>  'mode',
        'user_id' =>'user_id',
        'team_crew_id' => 'team_crew_id'
    ];

    protected function prepareForValidation(): void
    {
        $from = $this->input('from');
        $to = DateTime::createFromFormat('Y-m-d', $this->input('to'))->modify('-1 day')->format('Y-m-d');
        $period = iterator_to_array(CarbonPeriod::create($this->input('from'), $to)->map(function ($date) {
            return $date->format('Y-m-d');
        }));

        /* need for users filter dropdown */
        $members = ScheduleTeamsMember::with('user:id,firstname,lastname', 'team:team_id,team_leader_user_id')->datesInterval($from, $to)->get();
        $team_leaders = $members->pluck('team.team_leader_user_id')->unique();
        $users = $members->sortBy('user.full_name', SORT_STRING)->pluck('user')->unique('id')->values()->toArray();

        $users = collect($users)->map(function ($user) use ($team_leaders){
            $user['is_team_leader'] = ($team_leaders->search($user['id'])!==false);
            return $user;
        });

        $user_id = $this->input('user_id');
        $team_crew_id = $this->input('team_crew_id');

        /* need for users filter dropdown */
        $result = [
            'from'  =>  $from,
            'to'    =>  $to,
            'interval' => $period,
            'user_id' => $user_id,
            'team_crew_id' => $team_crew_id,
            'members'=>$users??[],
        ];

        foreach($this->map as $original => $new) {
            $this->request->remove($original);
            $this->offsetUnset($original);
        }

        $this->merge($result);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        return [];
    }

    public function messages()
    {
        $messages = parent::messages();
        return $messages;
    }
}