<?php
namespace application\modules\events\models;

use application\core\Database\EloquentModel;
use application\modules\events\models\EventsReport;
use application\modules\employees\models\SafetyPdfSign;

class Event extends EloquentModel
{
    protected $primaryKey = 'ev_id';
    /**
     * Table  name
     * @var string
     */
    protected $appends = ['ev_tailgate_safety_form_array'];
    protected $table = 'events';

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */

    function safety_pdf_signs(){
        return $this->hasMany(SafetyPdfSign::class, 'work_event_id', 'ev_id');
    }

    function getEvTailgateSafetyFormArrayAttribute(){
        if(!$this->attributes['ev_tailgate_safety_form'])
            return [];

        return json_decode($this->attributes['ev_tailgate_safety_form'], true);
    }

}