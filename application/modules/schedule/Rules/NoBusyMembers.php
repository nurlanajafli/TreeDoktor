<?php

namespace application\modules\schedule\Rules;

use application\modules\schedule\models\ScheduleTeamsMember;
use Illuminate\Contracts\Validation\Rule;

class NoBusyMembers implements Rule
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
        $attributes = $this->request->all();

        $members = ScheduleTeamsMember::with(['team', 'user'])->whereHas('team', function ($query) use ($attributes){
            $query_sub = $query->datesInterval($attributes['team_date_start'], $attributes['team_date_end']);
            if(isset($attributes['team_id']))
                $query_sub->where('team_id', '<>', $attributes['team_id']);

            return $query_sub;
        })->whereIn('user_id', $value)->get();

        if($members->count())
        {
            $users = $members->implode('user.fullname', ', ');
            $this->message = "These team member(s): " . $users . " already busy on this date.";
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
