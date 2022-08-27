<?php

namespace application\modules\schedule\Rules;

use application\modules\equipment\models\Equipment;
use application\modules\schedule\models\ScheduleTeamsEquipment;
use Illuminate\Contracts\Validation\Rule;

class NoBusyItems implements Rule
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

        $equipment = ScheduleTeamsEquipment::with(['team', 'equipment'])->whereHas('team', function ($query) use ($attributes){
            $query_sub = $query->datesInterval($attributes['team_date_start'], $attributes['team_date_end']);
            if(isset($attributes['team_id']))
                $query_sub->where('team_id', '<>', $attributes['team_id']);

            return $query_sub;
        })->whereIn('equipment_id', $value)->get();

        if($equipment->count())
        {
            $items = $equipment->implode('equipment.eq_name', ', ');
            $this->message = "These item(s):  " . $items . " already busy on this date.";
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
