<?php


namespace application\modules\estimates\models;
use application\core\Database\EloquentModel;
use application\modules\crew\models\Crew;
use application\modules\estimates\models\EstimatesService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as a1;

class EstimatesServicesCrew extends EloquentModel
{
    protected $table = 'estimates_services_crews';
    protected $primaryKey = 'crew_id';

    const ATTR_ID = 'crew_id';
    const ATTR_ESTIMATE_ID = 'crew_estimate_id';

    const API_GET_FIELDS = [
        'estimates_services_crews.crew_id'
    ];

    function estimates_service() {
        return $this->hasOne(EstimatesService::class, 'id', 'crew_service_id');
    }

    function crew() {
        return $this->hasOne(Crew::class, 'crew_id', 'crew_user_id');
    }

    public function scopeCrewsNamesLine($query, $withCrewsCount = true) {
        /*return $query->selectRaw("GROUP_CONCAT(DISTINCT crew_name ORDER BY crew_name DESC SEPARATOR ', ') as crew_name, estimates_services_crews.crew_estimate_id")
        ->join('crews', ['crews.crew_id' => 'estimates_services_crews.crew_user_id'])
        ->groupBy(['crew_estimate_id', 'crew_service_id']);
        GROUP_CONCAT(crew_name, ' (', MAX(count_crew), ')')
        */
        $query->selectRaw("GROUP_CONCAT(DISTINCT crew_name ORDER BY crew_name DESC SEPARATOR ', ') as crew_name, crew_estimate_id")
            ->fromSub(function($subQuery) use ($query, $withCrewsCount) {
                $select = "crew_name, crew_estimate_id";

                if ($withCrewsCount) {
                    $select = "CONCAT(crew_name, ' (', MAX(count_crew), ')') as " . $select;
                }

                $subQuery->selectRaw($select)
                    ->fromSub(function($subQueryCrew) use ($query) {
                        $subQueryCrew->selectRaw('crew_user_id, crew_name, COUNT(crew_user_id) as count_crew, crew_service_id, crew_estimate_id');
                        $subQueryCrew->from($this->table);
                        $subQueryCrew->join('crews', 'crews.crew_id', '=', 'estimates_services_crews.crew_user_id');
                        $subQueryCrew->groupBy(['crew_user_id', 'crew_service_id']);
                        $subQueryCrew->mergeWheres($query->getQuery()->wheres, $subQueryCrew->bindings);
                    }, 't')
                    ->groupBy(['t.crew_user_id', 't.crew_estimate_id']);
        }, 't1')
            ->groupBy(['t1.crew_estimate_id']);

        $query->getQuery()->wheres = [];

        return $query;
    }

    public function scopeNewService($query){
        return $query->whereHas('estimates_service', function (Builder $query) {
            $query->where('service_status', '=', 0);
        });
    }

}