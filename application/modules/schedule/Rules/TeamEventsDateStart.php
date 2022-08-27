<?php


namespace application\modules\schedule\Rules;
use Illuminate\Contracts\Validation\Rule;
use application\modules\schedule\models\ScheduleEvent;

class TeamEventsDateStart implements Rule
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

        /* if team start > event_team_start */
        $event_min = ScheduleEvent::where('event_team_id', '=', $team_id)
            ->where('event_start', '<', strtotime($attributes['team_date_start'].' 00:00'))->first();

        if(!empty($event_min)){
            $this->message = "The event start is scheduled for ".date(getDateFormat(), $event_min->event_start);
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