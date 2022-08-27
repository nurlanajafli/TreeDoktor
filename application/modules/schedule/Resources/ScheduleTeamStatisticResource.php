<?php
namespace application\modules\schedule\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
class ScheduleTeamStatisticResource extends JsonResource
{
    public static $wrap = null;
    public function toArray($request)
    {

        $this->team_estimated_hours = 0;
        $this->team_estimated_amount = 0;

        $estimators = $this->events->implode('workorder.estimate.user.emailid', ', ');
        $this->setAppends(['actual_team_amount', 'actual_per_hour', 'team_statistic_color']);

        if ($this->events->count()) {
            $this->team_damage = $this->events->sum('event_damage');

            $this->events = $this->events->map(function ($event) {
                $event->schedule_event_service = $event->schedule_event_service->map(function ($service) {
                    $service->count_members = $service->services_crew->count();
                    $service->team_service_estimated_hours = $service->count_members * ($service->service_time + $service->service_disposal_time + $service->service_travel_time);
                    return $service;
                });

                $event->team_estimated_hours = $event->schedule_event_service->sum('team_service_estimated_hours');
                $event->team_estimated_amount = round($event->schedule_event_service->sum('service_price'), 2);
                return $event;
            });

            $this->team_estimated_hours = round($this->events->sum('team_estimated_hours'), 1);
            $this->team_estimated_amount = round($this->events->sum('team_estimated_amount'), 2);

        }

        $estimated_per_hour = ($this->team_estimated_amount && $this->team_estimated_hours) ? round(($this->team_estimated_amount / $this->team_estimated_hours), 2) : 0;

        return [
            'team_id' => $this->team_id,
            'team_amount' => $this->team_amount ?? 0,
            'actual_team_amount' => $this->actual_team_amount,
            'team_estimated_hours' => round($this->team_estimated_hours, 1),
            'team_estimated_amount' => $this->team_estimated_amount,
            'team_man_hours' => $this->team_man_hours,
            'estimators' => ($estimators) ? $estimators : 'N/A',
            'actual_per_hour' => $this->actual_per_hour,
            'bg_color' => $this->team_statistic_color,
            'amountProd' => ($this->team_estimated_amount) ? round($this->actual_team_amount / $this->team_estimated_amount * 100, 2) : false,
            'perHourProd' => ($estimated_per_hour) ? round($this->actual_per_hour / $estimated_per_hour * 100, 2) : false,
            'team_leader' => $this->team_leader,
            'estimated_per_hour' => $estimated_per_hour,
            'sectionWidth'=>0,
            'team_closed'=>$this->team_closed
        ];
    }
}