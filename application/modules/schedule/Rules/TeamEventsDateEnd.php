<?php


namespace application\modules\schedule\Rules;
use Illuminate\Contracts\Validation\Rule;
use application\modules\schedule\models\ScheduleEvent;

class TeamEventsDateEnd implements Rule
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

        $event_max = ScheduleEvent::where('event_team_id', '=', $team_id)
            ->where('event_end', '>', strtotime($attributes['team_date_end'].' 23:59'))->first();

        if(!empty($event_max)){
            $this->message = "The event end is scheduled for ".date(getDateFormat(), $event_max->event_end);
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