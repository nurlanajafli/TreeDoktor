<?php


namespace application\modules\employees\models;


use application\core\Database\EloquentModel;
use application\modules\user\models\User;

class SafetyPdfSign extends EloquentModel
{
    /**
     * @var string
     */
    protected $table = 'safety_pdf_signs';

    /**
     * @var string
     */
    protected $primaryKey = 'id';

    /**
    * The attributes that are mass assignable.
    *
    * @var array
    */
    protected $fillable = [
        'event_id', 'team_id', 'user_id', 'is_teamlead', 'safety_pdf_sign', 'signed', 'date'
    ];

    public function scopeWithDate($query, $date){
        return $query->whereDate('date', '=', $date);
    }

    public function scopeWithEvent($query, $event_id){
        return $query->where('event_id', '=', $event_id);
    }

    public function scopeSigned($query){
        return $query->where('is_teamlead', '=', 1)->where('safety_pdf_sign', '<>', '');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    function user(){
        return $this->hasOne(User::class, 'id', 'user_id');
    }
}