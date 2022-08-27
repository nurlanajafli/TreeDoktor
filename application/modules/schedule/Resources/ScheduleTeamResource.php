<?php
namespace application\modules\schedule\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
class ScheduleTeamResource extends JsonResource
{
    //public $preserveKeys = true;
    public static $wrap = null;

    public $data_type = 'default';

    public function __construct($resource, $data_type = false)
    {
        parent::__construct($resource);
        $this->resource = $resource;
        $this->data_type = $data_type;
    }

    public function toArray($request)
    {
        switch ($this->data_type) {
            case 'event':
                return $this->eventData();
                break;
            case 'members':
                return $this->membersData();
                break;
            default:
                return $this->resource;
                break;
        }
    }

    private function eventData()
    {
        return [
            'team_id'=>$this->team_id,
            'team_color'=>$this->team_color,
            'team_amount_money_format'=>$this->team_amount_money_format,
            'team_route_optimized' => $this->team_route_optimized,
            'team_leader'=>($this->team_leader)?$this->team_leader->only('id', 'initials', 'full_name', 'picture'):[],
            'crew' => ($this->crew)?$this->crew->only('crew_id', 'crew_name', 'crew_color'):[]
        ];
    }

    private function membersData()
    {
        return [
            'team_id'=>$this->team_id,
            'members'=>$this->members(),
        ];
    }

}