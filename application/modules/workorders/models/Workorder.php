<?php

namespace application\modules\workorders\models;

use application\core\Database\Casts\AppDate;
use application\core\Database\Casts\AppDateTime;
use application\core\Database\EloquentModel;
use application\modules\clients\models\Client;
use application\modules\clients\models\ClientsContact;
use application\modules\clients\models\Tag;
use application\modules\emails\models\Email;
use application\modules\clients\models\ClientLetter;
use application\modules\employees\models\Employee;
use application\modules\crew\models\Crew;
use application\modules\estimates\models\Service;
use application\modules\estimates\models\Estimate;
use application\modules\estimates\models\EstimatesService;
use application\modules\estimates\models\EstimatesServicesCrew;
use application\modules\estimates\models\EstimatesServicesStatus;
use application\modules\estimates\models\EstimatesServicesEquipments;
use application\modules\invoices\models\Invoice;
use application\modules\leads\models\Lead;
use application\modules\schedule\models\ScheduleEvent;
use application\modules\user\models\User;
use application\modules\workorders\models\WorkorderStatus;
use application\modules\clients\models\StatusLog;
use application\modules\dashboard\models\traits\FullTextSearch;
use application\modules\estimates\models\TreeInventoryEstimateServiceWorkTypes;
use application\modules\tree_inventory\models\WorkType;
use DB;
use function Clue\StreamFilter\fun;
use function foo\func;

class Workorder extends EloquentModel
{
    use FullTextSearch;

    const ATTR_ID = 'id';
    const ATTR_WORKORDER_NO = 'workorder_no';
    const ATTR_ESTIMATE_ID = 'estimate_id';
    const ATTR_CLIENT_ID = 'client_id';

    const ATTR_WO_CONFIRM_HOW = 'wo_confirm_how';
    const ATTR_WO_DEPOSIT_TAKEN_BY = 'wo_deposit_taken_by'; // not used
    const ATTR_WO_DEPOSIT_PAID = 'wo_deposit_paid';
    const ATTR_WO_SCHEDULING_PREFERENCE = 'wo_scheduling_preference'; // not used
    const ATTR_WO_EXTRA_NOT_CREW = 'wo_extra_not_crew';
    const ATTR_IN_TIME_LEFT_OFFICE = 'in_time_left_office';
    const ATTR_IN_TIME_ARRIVED_SITE = 'in_time_arrived_site';
    const ATTR_IN_TIME_LEFT_SITE = 'in_time_left_site';
    const ATTR_IN_TIME_ARRIVED_OFFICE = 'in_time_arrived_office';
    const ATTR_IN_JOB_COMPLETED = 'in_job_completed';
    const ATTR_IN_PAYMENT_RECEIVED = 'in_payment_received';
    const ATTR_IN_LEFT_TODO = 'in_left_todo';
    const ATTR_IN_ANY_DAMAGE = 'in_any_damage';
    const ATTR_IN_EQ_MALFUNTION = 'in_eq_malfuntion';
    const ATTR_IN_NOTE_COMPLETION = 'in_note_completion';
    const ATTR_WO_STATUS = 'wo_status';
    const ATTR_WO_ESTIMATOR = 'wo_estimator';
    const ATTR_WO_PRIORITY = 'wo_priority';
    const ATTR_DATE_CREATED = 'date_created';
    const ATTR_WO_PDF_FILES = 'wo_pdf_files';

    /**
     * API application fields
     * @var array
     */
    const API_FIELDS = [
        'workorders.id',
        'workorders.workorder_no',
        'workorders.estimate_id',
        'workorders.client_id',
        'workorders.date_created',
        'workorders.wo_status',
        'workorders.wo_priority',
        'workorders.wo_estimator',
        'workorders.wo_office_notes',
        'workorders.wo_pdf_files'
    ];

    /**
     * API application fields for get
     * @var array
     */
    const API_GET_FIELDS = [
        'workorders.id',
        'workorders.workorder_no',
        'workorders.date_created',
        'workorders.wo_status',
    ];

    const FOR_SCHEDULE_FIELDS = [
        'workorders.id',
        'workorders.workorder_no',
        'workorders.estimate_id',
        'workorders.date_created',
        'workorders.wo_status',
        'workorders.wo_office_notes',
    ];
    /**
     * Workorder table primary key name
     * @var string
     */
    protected $primaryKey = 'id';
    /**
     * Table  name
     * @var string
     */
    protected $table = 'workorders';

    /**
     * @var array
     */
    protected $fillable = [
        'wo_status'
    ];

    /**
     * The columns of the full text index
     * @var array
     */
    protected $appends = ['last_change', 'date_created_view', 'days_from_creation', 'files_array'];

    /**
     * The supported cast types are: integer, real, float, double, decimal:<digits>, string, boolean,
     * object, array, collection, date, datetime, and timestamp.
     * When casting to decimal, you must define the number of digits (decimal:2).
     * @var array
     */
    protected $casts = [
        'estimate_id' => 'integer',
        'client_id' => 'integer',
        'wo_status' => 'integer'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    function client()
    {
        return $this->hasOne(Client::class, 'client_id', 'client_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    function estimate()
    {
        return $this->hasOne(Estimate::class, 'estimate_id', 'estimate_id')->with(['user']);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasManySyncable
     */
    function invoices()
    {
        return $this->hasMany(Invoice::class, 'estimate_id', 'estimate_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    function status()
    {
        return $this->hasOne(WorkorderStatus::class, 'wo_status_id', 'wo_status');
    }

    /**
     * @return \application\models\Relations\HasManySyncable
     */
    public function schedules()
    {
        return $this->hasMany(ScheduleEvent::class, 'event_wo_id', 'id');
    }

    /**
     * @return \application\models\Relations\HasManySyncable
     */
    function status_log()
    {
        return $this->hasMany(StatusLog::class, 'status_item_id')->where('status_type', '=',
            'workorder')->orderBy('status_date', 'DESC');;
    }

    function emails() {
        return $this->morphToMany(Email::class, 'emailable');
    }

    /**
     * @var array
     */
    protected $searchable = [
        'workorder_no',
        'wo_office_notes'
    ];

    public function globalSearch($query_string){

        $columns_workorder = implode(',', add_preffix($this->searchable, 'workorders.'));
        $columns_lead = implode(',', add_preffix((new Lead())->getSearchable(), 'leads.'));
        $columns_estimate = implode(',', add_preffix((new Estimate())->getSearchable(), 'estimates.'));
        $columns_client = implode(',', add_preffix((new Client())->getSearchable(), 'clients.'));
        $columns_client_contact = implode(',', add_preffix((new ClientsContact())->getSearchable(), 'clients_contacts.'));

        $search = $this->fullTextWildcards($query_string);
        $result = $this->select([
            DB::raw("workorders.id, MATCH ({$columns_workorder}) AGAINST ('".$search."' IN BOOLEAN MODE) AS relevance_score, ".$columns_workorder.', '.$columns_lead.', '.$columns_estimate.', '.$columns_client.', '.$columns_client_contact)
        ])
            ->join('estimates', ['workorders.estimate_id' => 'estimates.estimate_id'])
            ->join('clients', ['estimates.client_id' => 'clients.client_id'])
            ->join('leads', ['estimates.lead_id' => 'leads.lead_id'])
            ->leftJoin('clients_contacts', ['cc_client_id' => 'clients.client_id', 'cc_print' => DB::raw('1')])
            ->whereRaw("MATCH ({$columns_workorder}) AGAINST (? IN BOOLEAN MODE)", $search)
            ->orWhereRaw("MATCH ({$columns_lead}) AGAINST (? IN BOOLEAN MODE)", $search)
            ->orWhereRaw("MATCH ({$columns_estimate}) AGAINST (? IN BOOLEAN MODE)", $search)
            ->orWhereRaw("MATCH ({$columns_client}) AGAINST (? IN BOOLEAN MODE)", $search)
            ->orWhereRaw("MATCH ({$columns_client_contact}) AGAINST (? IN BOOLEAN MODE)", $search)->get();
        return $result;
    }

    public function scopeFilters($query, $request, $status)
    {
        $search = $request->input('search_keyword');
        if($search){
            $search_ids = $this->globalSearch($search)->pluck('id')->toArray();
            $query->whereIn('id', $search_ids);
        }

        if($request->input('filter_estimator') && count($request->input('filter_estimator')))
            $query->whereHas('estimate', function ($query) use ($request){ $query->whereIn('user_id', $request->input('filter_estimator')); });
        if($request->input('filter_equipment') && !empty($request->input('filter_equipment')))
            $query->whereHas('estimate.estimates_service.equipments', function ($query) use ($request){ $query->whereIn('equipment_item_id', $request->input('filter_equipment')); });
        if($request->input('estimator_id'))
            $query->whereHas('estimate', function ($query) use ($request){ $query->whereIn('user_id', $request->input('estimator_id')); });
        if($request->input('filter_crew') && count($request->input('filter_crew')))
            $query->whereHas('estimate.estimate_crews', function ($query) use ($request){ $query->whereIn('crew_user_id', $request->input('filter_crew')); });
        if($request->input('filter_service') && count($request->input('filter_service')))
            $query->whereHas('estimate.estimates_service', function ($query) use ($request){ $query->whereIn('service_id', $request->input('filter_service')); });
        if($request->input('filter_product') &&  count($request->input('filter_product')))
            $query->whereHas('estimate.estimates_service', function ($query) use ($request){ $query->whereIn('service_id', $request->input('filter_product')); });
        if($request->input('filter_bundle'))
            $query->whereHas('estimate.estimates_service', function ($query) use ($request){ $query->whereIn('service_id', $request->input('filter_bundle')); });
        if($request->input('filter_estimates_services_status'))
            $query->whereHas('estimate.estimates_service.status', function ($query) use ($request){ $query->whereIn('services_status_id', $request->input('filter_estimates_services_status')); });

        if($status && is_array($status))
            $query->whereIn('wo_status', $status);
        if ($request->input('date_from'))
            $query->where('date_created', '>=', $request->input('date_from'));
        if ($request->input('date_to'))
            $query->where('date_created', '<=', $request->input('date_to'));


        return $query;
    }

    /**
     * @param $query_string
     * @return mixed
     */
    function globalSearchQuery($query_string)
    {
        $columns = implode(',', $this->searchable);
        $search = $this->fullTextWildcards($query_string);
        return self::search($query_string)
            ->select([
                "lead_address as item_address",
                "client_name as item_name",
                "cc_phone as item_phone",
                "cc_name as item_cc_name",
                "cc_email as item_email",
                DB::raw("CONCAT(NULL) as item_status"),
                DB::raw("CONCAT(workorders.date_created, ' ','00:00:00') as item_date_created"),
                DB::raw("CONCAT('workorders') as item_module_name"),
                DB::raw("CONCAT('profile') as item_action_name"),
                "workorders.id as item_id",
                "workorder_no as item_no",
                "workorder_no as item_title",
                DB::raw("CONCAT('4') as item_position"),
                DB::raw("CONCAT(NULL) as total"),
                DB::raw("MATCH ({$columns}) AGAINST ('" . $search . "' IN BOOLEAN MODE) AS relevance_score")
            ])
            ->join('estimates', ['workorders.estimate_id' => 'estimates.estimate_id'])
            ->join('clients', ['estimates.client_id' => 'clients.client_id'])
            ->join('leads', ['estimates.lead_id' => 'leads.lead_id'])
            ->leftJoin('clients_contacts', ['cc_client_id' => 'clients.client_id', 'cc_print' => DB::raw('1')])
            ->permissions();
    }


    /**
     * @param array $filters
     * @param array $select
     * @return Workorder
     */
    public static function globalSearchByFilters(array $filters, array $select = [])
    {
        $select = empty($select) ? [Workorder::tableName() . '.' . Workorder::ATTR_ID] : $select;

        $query = Workorder::select($select)
            ->join(Estimate::tableName(),
                Workorder::tableName() . '.' . Workorder::ATTR_ESTIMATE_ID, '=', Estimate::tableName() . '.' . Estimate::ATTR_ESTIMATE_ID
            )
            ->join(Client::tableName(),
                Estimate::tableName() . '.' . Estimate::ATTR_CLIENT_ID, '=', Client::tableName() . '.' . Client::ATTR_CLIENT_ID
            )
            ->join(Lead::tableName(),
                Estimate::tableName() . '.' . Estimate::ATTR_LEAD_ID, '=', Lead::tableName() . '.' . Lead::ATTR_LEAD_ID
            )
            ->leftJoin(ClientsContact::tableName(), function($query) {
                $query->on(ClientsContact::tableName() . '.' . ClientsContact::ATTR_CC_CLIENT_ID, '=', Client::tableName() . '.' . Client::ATTR_CLIENT_ID)
                    ->on(ClientsContact::tableName() . '.' . ClientsContact::ATTR_CC_PRINT, '=', DB::raw('1'));
            });

        if (isset($filters['search']) && !empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($query) use ($search) {
                $query->orWhere(Client::tableName() . '.' . Client::ATTR_CLIENT_NAME, 'LIKE', DB::raw('"%' . $search . '%"'));
                $query->orWhere(Client::tableName() . '.' . Client::ATTR_CLIENT_ADDRESS, 'LIKE', DB::raw('"%' . $search . '%"'));
                $query->orWhere(ClientsContact::tableName() . '.' . ClientsContact::ATTR_CC_NAME, 'LIKE', DB::raw('"%' . $search . '%"'));
                $query->orWhere(ClientsContact::tableName() . '.' . ClientsContact::ATTR_CC_PHONE, 'LIKE', DB::raw('"%' . $search . '%"'));
                $query->orWhere(ClientsContact::tableName() . '.' . ClientsContact::ATTR_CC_EMAIL, 'LIKE', DB::raw('"%' . $search . '%"'));
                $query->orWhere(Lead::tableName() . '.' . Lead::ATTR_LEAD_ADDRESS, 'LIKE', DB::raw('"%' . $search . '%"'));
                $query->orWhere(Workorder::tableName() . '.' . Workorder::ATTR_WORKORDER_NO, 'LIKE', DB::raw('"%' . $search . '%"'));
            });
        }

        if (isset($filters['tag_names']) && !empty($filters['tag_names']) && !is_null($filters['tag_names'])) {
            $query->leftJoin('client_tags', 'client_tags.client_id', '=', Client::tableName() . '.' . Client::ATTR_CLIENT_ID)
                ->leftJoin(Tag::tableName(), Tag::tableName() . '.' . Tag::ATTR_TAG_ID, '=', 'client_tags.tag_id');
            $query->whereIn(Tag::tableName() . '.' . Tag::ATTR_NAME, $filters['tag_names']);
        }
        if (isset($filters['tags']) && !empty($filters['tags']) && !is_null($filters['tags'])) {
            $query->leftJoin('client_tags', 'client_tags.client_id', '=', Client::tableName() . '.' . Client::ATTR_CLIENT_ID)
                ->leftJoin(Tag::tableName(), Tag::tableName() . '.' . Tag::ATTR_TAG_ID, '=', 'client_tags.tag_id');
            $query->whereIn(Tag::tableName() . '.' . Tag::ATTR_TAG_ID, $filters['tags']);
        }

        return $query;
    }

    /**
     * @return string
     */
    function getLastChangeAttribute()
    {
        if (!isset($this->attributes['change_date']) && !isset($this->attributes['date_created'])) {
            return '';
        }

        $change_date = isset($this->attributes['change_date']) ? $this->attributes['change_date'] : false;
        if (!$change_date) {
            $change_date = strtotime($this->attributes['date_created']);
        }

        $now = date_create(date('Y-m-d'));
        $wo = date_create(date('Y-m-d', $change_date));
        $interval = date_diff($now, $wo);
        if (!$interval) {
            return '';
        }

        return $interval->format('%a');
    }

    /**
     * @return string
     */
    function getDaysFromCreationAttribute(){
        if (!isset($this->attributes['date_created']) && !isset($this->attributes['date_created'])) {
            return '';
        }

        $change_date = strtotime($this->attributes['date_created']);

        $now = date_create(date('Y-m-d'));
        $wo = date_create(date('Y-m-d', $change_date));
        $interval = date_diff($now, $wo);
        if (!$interval) {
            return '';
        }

        return $interval->format('%a');
    }

    /**
     * @return string|null
     */
    function getDateCreatedViewAttribute()
    {
        if (!isset($this->attributes['date_created'])) {
            return false;
        }
        return getDateTimeWithTimestamp(strtotime($this->attributes['date_created']));
    }

    function getWoOfficeNotesAttribute() {
        return trim($this->attributes['wo_office_notes']);
    }

    function getEstimateCrewNotesAttribute() {
        return isset($this->attributes['estimate_crew_notes']) && $this->attributes['estimate_crew_notes'] ? trim($this->attributes['estimate_crew_notes']) : null;
    }

    function getFilesArrayAttribute(){
        return (isset($this->attributes['wo_pdf_files']) && $this->attributes['wo_pdf_files']) ? json_decode($this->attributes['wo_pdf_files'], true) : [];
    }
    /**
     * @param $page
     * @param array $filters
     * @param $limit
     * @return array|\Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public static function getWorkorders($page, array $filters, $limit)
    {
        $CI = &get_instance();
        $CI->load->model('mdl_estimates_orm');
        $matchedWorkordersIds = [];
        $where = [];
        if ((isset($filters['search']) && !empty($filters['search']))
            ||
            (isset($filters['tag_names']) && !empty($filters['tag_names'])) || (isset($filters['tags']) && !empty($filters['tags']))) {
            $matchedWorkordersIds = static::globalSearchByFilters($filters)
                ->get()
                ->keyBy('id')
                ->each->setAppends([])
                ->toArray();

            if (empty($matchedWorkordersIds)) {
                return [];
            }
        }

        if (isset($filters['status_id']) && !empty($filters['status_id']) && !is_null($filters['status_id'])) {
            $where['wo_status'] = $filters['status_id'];
        }
        $query = Workorder::select(array_merge_recursive(
            static::API_GET_FIELDS, ['totals.total_due', 'totals.total_with_tax', 'totals.total_all_services', 'totals.sum_without_tax', 'totals.sum_actual_without_tax'], Estimate::API_GET_FIELDS,
            Client::API_GET_FIELDS,  Lead::API_GET_FIELDS, User::API_GET_FIELDS, ClientsContact::API_GET_FIELDS,
            [DB::raw("esc.crew_name")],
            [DB::raw("es.totaltime")]
        ))
            ->leftJoin(
                Estimate::tableName(), function($leftJoin) use ($where) {
                $leftJoin->on(Estimate::tableName() . '.' . Estimate::ATTR_ESTIMATE_ID, '=', Workorder::tableName() . '.' . Workorder::ATTR_ESTIMATE_ID)
                    ->where($where);
            })
            ->leftJoin(Client::tableName(), function($leftJoin) use ($where) {
                $leftJoin->on(Estimate::tableName() . '.' . Estimate::ATTR_CLIENT_ID, '=', Client::tableName() . '.' . Client::ATTR_CLIENT_ID)
                    ->where($where);
            })
            ->leftJoin(ClientsContact::tableName(),
                ClientsContact::tableName() . '.' . ClientsContact::ATTR_CC_CLIENT_ID, '=', Client::tableName() . '.' . Client::ATTR_CLIENT_ID)
            ->leftJoin(Lead::tableName(),
                Estimate::tableName() . '.' . Estimate::ATTR_LEAD_ID, '=', Lead::tableName() . '.' . Lead::ATTR_LEAD_ID)
            ->leftJoin(User::tableName(),
                User::tableName() . '.' . User::ATTR_ID, '=', Estimate::tableName() . '.' . Estimate::ATTR_USER_ID)
            ->leftJoin(DB::raw("(SELECT GROUP_CONCAT(DISTINCT crew_name ORDER BY crew_name DESC SEPARATOR ', ') as crew_name, crew_estimate_id
                FROM (
                    SELECT CONCAT(crew_name, ' (', MAX(count_crew), ')') as crew_name, crew_estimate_id
                    FROM (
                        SELECT crew_user_id, crew_name, COUNT(crew_user_id) as count_crew, crew_service_id, crew_estimate_id
                        FROM estimates_services_crews
                        INNER JOIN crews ON crews.crew_id = estimates_services_crews.crew_user_id
                        GROUP BY crew_user_id, crew_service_id
                    ) as t
                    GROUP BY t.crew_user_id, t.crew_estimate_id
                ) as t1
                GROUP BY t1.crew_estimate_id) esc"), function($join) {
                $join->on(Estimate::tableName() . '.' . Estimate::ATTR_ESTIMATE_ID, '=', 'esc.crew_estimate_id');
            })
            ->leftJoin(DB::raw("(SELECT ROUND(service_time + service_travel_time + service_disposal_time, 2) as totaltime, estimate_id
                FROM estimates_services WHERE service_status = 0 GROUP BY estimates_services.estimate_id) es"), function($join) {
                $join->on(Estimate::tableName() . '.' . Estimate::ATTR_ESTIMATE_ID, '=','es.' . EstimatesService::ATTR_ESTIMATE_ID);
            });

        $wororderStatus = WorkorderStatus::where(WorkorderStatus::ATTR_IS_FINISHED, 1)->first();

        /* Start Filters */

        if (isset($filters['date_from']) && !empty($filters['date_from'])) {
            $query->where(Workorder::tableName() . '.' . Workorder::ATTR_DATE_CREATED, '>=', $filters['date_from']);
        }
        if (isset($filters['date_to']) && !empty($filters['date_to'])) {
            $query->where(Workorder::tableName() . '.' . Workorder::ATTR_DATE_CREATED, '<=', $filters['date_to']);
        }
        if (isset($filters['estimator_id'])) {
            $where[Estimate::tableName() . '.' . Estimate::ATTR_USER_ID] = $filters['estimator_id'];
            $query->where('estimates.user_id', $filters['estimator_id']);
        }

        if (isset($filters['status_id']) && !empty($filters['status_id']) && !is_null($filters['status_id'])) {
            $query->where([Workorder::tableName() . '.' . Workorder::ATTR_WO_STATUS => $filters['status_id']]);
        }

        if (!empty($matchedWorkordersIds)) {
            $query->whereIn(Workorder::tableName() . '.' . Workorder::ATTR_ID, array_keys($matchedWorkordersIds));
        }
        /* End Filters */
        $query->with('client.tags');
        $query->where(
            Workorder::tableName() . '.' . Workorder::ATTR_WO_STATUS,
            '<>',
            $wororderStatus->getAttribute(WorkorderStatus::ATTR_ID)
        )->where([ClientsContact::tableName() . '.' . ClientsContact::ATTR_CC_PRINT => 1])->orderBy(Workorder::tableName() . '.' . Workorder::ATTR_DATE_CREATED, 'DESC');


        $where[Workorder::tableName() . '.' . Workorder::ATTR_WO_STATUS . ' <> '] = $wororderStatus->getAttribute(WorkorderStatus::ATTR_ID);

        $totalsSubQuery = $CI->mdl_estimates_orm->calcQuery($where);
        $query->leftJoin(DB::raw('(' . $totalsSubQuery. ') AS totals'), 'totals.estimate_id', '=', 'estimates.estimate_id');

        $query->permissions();

        return $query->paginate($limit ?: $query->count(), [], 'page', $page);
    }

    /**
     * @param Workorder $workorder
     * @param bool $array
     * @return Workorder|\Illuminate\Database\Eloquent\Model|object|null
     */

    public static function getWorkordersList ($page, array $filters, $limit, $order = '', $orderDir = 'asc', $request)
    {

        $CI = &get_instance();
        $CI->load->model('mdl_estimates_orm');
        $matchedWorkordersIds = [];
        $where = [];
        if ((isset($filters['search']) && !empty($filters['search']))
            ||
            (isset($filters['tag_names']) && !empty($filters['tag_names'])) || (isset($filters['tags']) && !empty($filters['tags']))) {
            $matchedWorkordersIds = static::globalSearchByFilters($filters)
                ->get()
                ->keyBy('id')
                ->each->setAppends([])
                ->toArray();

            if (empty($matchedWorkordersIds)) {
                return [];
            }
        }

        $status = '';
        if (isset($filters['status_id']) && !empty($filters['status_id']) && !is_null($filters['status_id'])) {
            if($filters['status_id'] == -1) {
                $where['wo_status_id !='] = WorkorderStatus::finished()->first();
                $status = -1;
            }
            elseif($filters['status_id'])
                $where['wo_status'] = $filters['status_id'];
        }


        $query = Workorder::select([
            "workorders.id",
            "workorders.workorder_no",
            "workorders.date_created",
            "workorders.wo_status",
            "workorders.wo_priority",
            "workorders.wo_estimator",
            "workorders.wo_office_notes",
            "workorders.wo_pdf_files",
            "totals.total_due",
            "totals.total_with_tax",
            "estimates.estimate_id",
            "estimates.user_id",
            "estimates.estimate_crew_notes",
            "clients.client_id",
            "clients.client_name",
            "clients.client_type",
            "clients.client_brand_id",
            "clients.client_lat",
            "clients.client_lng",
            "clients.client_address",
            "clients.client_city",
            "clients.client_zip",
            "clients.client_state",
            "clients.client_country",
            "clients_contacts.cc_name",
            "clients_contacts.cc_phone",
            "clients_contacts.cc_email",
            "leads.lead_address",
            "leads.lead_city",
            "leads.lead_zip",
            "leads.latitude",
            "leads.longitude",
            "leads.lead_estimator",
            "leads.lead_reffered_by",
            "users.firstname as user_firstname",
            "users.lastname as user_lastname"
        ], ['totals.total_due', 'totals.total_with_tax'])
            ->leftJoin(
                Estimate::tableName(), function($leftJoin) use ($where) {
                $leftJoin->on(Estimate::tableName() . '.' . Estimate::ATTR_ESTIMATE_ID, '=', Workorder::tableName() . '.' . Workorder::ATTR_ESTIMATE_ID)
                    ->where($where);
            })
            ->leftJoin(Client::tableName(), function($leftJoin) use ($where) {
                $leftJoin->on(Estimate::tableName() . '.' . Estimate::ATTR_CLIENT_ID, '=', Client::tableName() . '.' . Client::ATTR_CLIENT_ID)
                    ->where($where);
            })
            ->leftJoin(ClientsContact::tableName(), function ($join){
                $join->on(ClientsContact::tableName() . '.' . ClientsContact::ATTR_CC_CLIENT_ID, '=', Client::tableName() . '.' . Client::ATTR_CLIENT_ID);
                $join->on('clients_contacts.cc_print', '=', DB::raw("1"));
            })
            ->leftJoin(Lead::tableName(),
                Estimate::tableName() . '.' . Estimate::ATTR_LEAD_ID, '=', Lead::tableName() . '.' . Lead::ATTR_LEAD_ID)
            ->leftJoin(User::tableName(),
                User::tableName() . '.' . User::ATTR_ID, '=', Estimate::tableName() . '.' . Estimate::ATTR_USER_ID);

        $wororderStatus = WorkorderStatus::where(WorkorderStatus::ATTR_IS_FINISHED, 1)->first();

        /* Start Filters */

        $query->filters($request, $status);

        if (!empty($matchedWorkordersIds)) {
            $query->whereIn(Workorder::tableName() . '.' . Workorder::ATTR_ID, array_keys($matchedWorkordersIds));
        }
        /* End Filters */
        $query->with('client.tags');
        $query->where(
            Workorder::tableName() . '.' . Workorder::ATTR_WO_STATUS,
            '<>',
            $wororderStatus->getAttribute(WorkorderStatus::ATTR_ID)
        );
        if(!empty($where))
        {
            $query->where($where);
        }
        $query->groupBy('workorders.id');

        if($order != '')
            $query->orderBy($order, $orderDir);
        else
            $query->orderBy(Workorder::tableName() . '.' . Workorder::ATTR_DATE_CREATED, 'DESC');
        $query->permissions($request);



        $where[Workorder::tableName() . '.' . Workorder::ATTR_WO_STATUS . ' <> '] = $wororderStatus->getAttribute(WorkorderStatus::ATTR_ID);

        $totalsSubQuery = $CI->mdl_estimates_orm->calcQuery($where);
        $query->leftJoin(DB::raw('(' . $totalsSubQuery. ') AS totals'), 'totals.estimate_id', '=', 'estimates.estimate_id');

        $workorders = $query->paginate($limit ?: $query->count(), [], 'page', $page);

        $workorders->each(function ($workorder) {
            $workorder->setAppends(['cc_phone_view']);
        });

        return $workorders;
    }
    /**
     * @param Workorder $workorder
     * @param bool $array
     * @return Workorder|\Illuminate\Database\Eloquent\Model|object|null
     */
    public static function getWorkorder(Workorder $workorder, $array = false)
    {

        $query = Workorder::select(static::API_FIELDS)
            ->with('schedules')
            ->with([
                'schedules.schedule_event_service' => function ($query) {
                    $query->select([
                        'schedule_event_services.id',
                        'schedule_event_services.service_id'
                    ]);
                }
            ])
            ->with('schedules.team')
            ->with([
                'schedules.team.schedule_teams_members_user' => function ($query) {
                    $query->select([
                        'users.id',
                        'users.firstname',
                        'users.lastname',
                    ]);
                }
            ])
            ->with([
                'schedules.team.schedule_teams_equipments' => function ($query) {
                    $query->select([
                        'equipment.eq_id',
                        'equipment.eq_name',
                    ]);
                }
            ])
            ->with([
                'estimate' => function ($query) use ($workorder) {
                    $query->oneWithTotals($workorder->getAttribute(Workorder::ATTR_ID),
                        $workorder->getAttribute(Workorder::ATTR_WO_STATUS));
                    $query->with([
                        'client' => function ($query) {
                            $query->apiFields(Client::API_FIELDS);
                        },
                        'lead' => function ($query) {
                            $query->apiFields();
                        },
                        'estimate_status' => function ($query) {
                            $query->apiFields();
                        },
                        'estimate_status.reason' => function ($query) {
                            $query->apiFields();
                        },
                        'estimates_service' => function ($query) {
                            $query->doesntHave('bundle_service');
                            $query->baseFields()->with([
                                'classes',
                                'service',
                                'bundle.estimate_service.service',
                                'bundle.estimate_service.classes',
                                'bundle.estimate_service.equipments',
                                'equipments',
                                'expenses',
                                'services_crew.crew',
                                'tree_inventory'
                            ]);
                            $query->withCount('services_crew');
                        }
                    ]);
                },
                'status' => function ($query) {
                    $query->apiFields();
                }
            ]);

        $result = $query->where([
            Workorder::tableName() . '.' . Workorder::ATTR_ID => $workorder->getAttribute(Workorder::ATTR_ID),
            Workorder::tableName() . '.' . Workorder::ATTR_WO_STATUS => $workorder->getAttribute(Workorder::ATTR_WO_STATUS)
        ])->first();

        $result->estimate->setAppends(['files']);

        return $array === true ? $result->toArray() : $result;
    }

    /**
     * @param $query
     * @return mixed
     */
    function scopePermissions($query) {
        $user = request()->user();

        if(!isset($user) || is_null($user)) {
            return $query;
        }

        $joins = collect($query->getQuery()->joins);
        if(!$joins->pluck('table')->contains('estimates')) {
            $query->join('estimates', 'estimates.estimate_id', '=', 'workorders.estimate_id');
        }

        if(is_cl_permission_none()) {
            $query->where('estimates.user_id', -1);
        } elseif (is_cl_permission_owner()) {
            $query->where('estimates.user_id', $user->id);
        }

        return $query;
    }

    function scopeFinished($query){
        return $query->where('wo_status', '=', WorkorderStatus::finished()->first()->wo_status_id);
    }

    public function getCcPhoneViewAttribute(){
        $this->setAttribute('cc_phone_config_status', (
            isset($this->attributes['cc_phone']) &&
            $this->attributes['cc_phone'] == numberTo($this->attributes['cc_phone'])
        )
        );
        return isset($this->attributes['cc_phone']) ? numberTo($this->attributes['cc_phone']) : null;
    }

    public static  function countAggregate($query)
    {
        return DB::table(DB::raw("({$query->toSql()}) as sub"))->mergeBindings($query->toBase())->count();
    }
}
