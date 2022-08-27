<?php


namespace application\modules\app\resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
use application\modules\app\resources\AppScheduleEventResource;
class AppScheduleEventsCollection extends ResourceCollection
{
    public static $wrap = null;

    public function toArray($request)
    {
        return $this->collection->map(function ($event) use ($request) {
            return (new AppScheduleEventResource($event))->toArray($request);
        });
    }
}