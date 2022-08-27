<?php


namespace application\modules\schedule\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
class ScheduleAbsenceResource extends ResourceCollection
{
    public $preserveKeys = true;
    public function toArray($request)
    {
        $users = $this->map(function ($item){
            return ['id'=>$item->absence_user_id, 'text'=>$item->user->full_name];
        });

        return [
            'all'=>$this,
            'users_id'=>$this->pluck('absence_user_id'),
            'users' => $users
        ];
    }
}