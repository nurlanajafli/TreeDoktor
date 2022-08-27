<?php


namespace application\modules\schedule\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
use application\modules\schedule\Resources\ScheduleEventResource;
class ScheduleEventCollection extends ResourceCollection
{
    public $preserveKeys = true;
    public function toArray($request)
    {
        return $this->collection->map(function ($item) use ($request) {
            return (new ScheduleEventResource($item))->toArray($request);
        });
    }
}