<?php


namespace application\modules\schedule\Rules;
use application\modules\schedule\models\ScheduleTeams;
use Illuminate\Contracts\Validation\Rule;
use application\modules\schedule\models\ScheduleEvent;

class FreeTimeInterval implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    private $request, $message;

    public function __construct($request)
    {
        $this->request = $request;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $this->message = '';
        $attributes = $this->request->all();
        if((!isset($attributes['team_id']) || !(int)$attributes['team_id'])  && (!isset($attributes['event_team_id']) || !(int)$attributes['event_team_id']) )
            return true;

        $team_id = $attributes['team_id']??$attributes['event_team_id'];

        $team = ScheduleTeams::find($team_id);

        $events = ScheduleEvent::whereHas('team', function ($query) use ($team){
            if($team->team_leader_user_id)
                return $query->where('team_leader_user_id', '=', $team->team_leader_user_id);
            else
                return $query->where('team_id', '=', $team->team_id);
        })->datesInterval($attributes['event_start']+1, $attributes['event_end']-1, true)->where('id', '<>', $attributes['id']??0)->get();

        if($events->count()){
            $this->message = "The time interval is scheduled for other event.";
            return false;
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return $this->message;
    }
}