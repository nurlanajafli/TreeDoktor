<?php 

namespace application\modules\leads\models;

# use application\core\Database\Casts\AppDate;
# use application\core\Database\Casts\AppDateTime;
use application\core\Database\EloquentModel;
use application\modules\estimates\models\Estimate;
use application\modules\tasks\models\Task;
use application\modules\user\models\User;
use application\modules\clients\models\Client;

use application\modules\dashboard\models\traits\FullTextSearch;
use application\modules\leads\models\LeadStatus;
use application\modules\estimates\models\Service;

use DB;

use Illuminate\Database\Eloquent\Builder;
class Lead extends EloquentModel
{
    use FullTextSearch;

    const ATTR_LEAD_ID = 'lead_id';
    const ATTR_LEAD_NO = 'lead_no';
    const ATTR_LEAD_AUTHOR_ID = 'lead_author_id';
    const ATTR_LEAD_ADDRESS = 'lead_address';
    const ATTR_LEAD_CITY = 'lead_city';
    const ATTR_LEAD_STATE = 'lead_state';
    const ATTR_LEAD_NEIGHBORHOOD = 'lead_neighborhood';
    const ATTR_LEAD_ZIP = 'lead_zip';
    const ATTR_LEAD_COUNTRY = 'lead_country';
    const ATTR_CLIENT_ID = 'client_id';
    const ATTR_LEAD_BODY = 'lead_body';
    const ATTR_TREE_REMOVAL = 'tree_removal';
    const ATTR_TREE_PRUNING = 'tree_pruning';
    const ATTR_STUMP_REMOVAL = 'stump_removal';
    const ATTR_HEDGE_MAINTENANCE = 'hedge_maintenance';
    const ATTR_SHRUB_MAINTENANCE = 'shrub_maintenance';
    const ATTR_WOOD_DISPOSAL = 'wood_disposal';
    const ATTR_ARBORIST_REPORT = 'arborist_report';
    const ATTR_DEVELOPMENT = 'development';
    const ATTR_ROOT_FERTILIZING = 'root_fertilizing';
    const ATTR_TREE_CABLING = 'tree_cabling';
    const ATTR_EMERGENCY = 'emergency';
    const ATTR_OTHER = 'other';
    const ATTR_SPRAYING = 'spraying';
    const ATTR_TRUNK_INJECTION = 'trunk_injection';
    const ATTR_AIR_SPADING = 'air_spading';
    const ATTR_PLATING = 'planting';
    const ATTR_LATITUDE = 'latitude';
    const ATTR_LONGITUDE = 'longitude';
    const ATTR_LEAD_REFFERED_BY = 'lead_reffered_by';
    const ATTR_LEAD_ESTIMATOR = 'lead_estimator';
    const ATTR_LEAD_ADD_INFO_ = 'lead_add_info';

    protected $primaryKey = 'lead_id';
    /**
     * Table  name
     * @var string
     */
    protected $table = 'leads';

    public static $withoutAppends = false;

    CONST DEFAULT_MARKER_COLOR = '#00E64D';
    CONST PRIORITY_MAP_MARKER_ICON = [
        'Priority' => 'warning',
        'Emergency' => 'fire'
    ];

    CONST PRIORITY = [
        'Regular' => ['class' => 'text-success', 'name' => 'Regular'],
        'Priority' => ['class' => 'text-warning', 'name' => 'Priority'],
        'Emergency' => ['class' => 'text-danger', 'name' => 'Emergency']
    ];

    protected $appends = [
        'full_address',
        'lead_days',
        'marker_color',
        'marker_priority_icon',
        'lead_date_created_view',
        'waypoint'
    ];

    public $base_fields = [
        'leads.lead_id',
        'leads.lead_no',
        'leads.lead_author_id',
        'leads.lead_body',
        'leads.lead_address',
        'leads.lead_city',
        'leads.lead_country',
        'leads.lead_state',
        'leads.lead_zip',
        'leads.latitude',
        'leads.longitude',
        'leads.client_id',
        'leads.lead_priority',
        'leads.lead_call',
        'leads.lead_status_id',
        'leads.lead_estimator',
        'lead_date_created',
        'leads.lead_reffered_by'
    ];

    public $schedule_fields = [
        'lead_id',
        'client_id',
        'latitude',
        'longitude',
        'lead_address',
        'lead_city',
        'lead_state',
        'lead_zip',
        'lead_country',
        'lead_date_created',
    ];

    protected $fillable = [
        'lead_id',
        'lead_no',
        'lead_author_id',
        'lead_body',
        'lead_address',
        'lead_city',
        'lead_country',
        'lead_state',
        'lead_zip',
        'latitude',
        'longitude',
        'client_id',
        'lead_priority',
        'lead_call',
        'lead_status_id',
        'lead_estimator',
        'lead_date_created',
        'lead_estimate_draft',
        'lead_reffered_by'
    ];

    /**
     * The columns of the full text index
     */
    protected $searchable = [
        'lead_no',
        'lead_body',
        'lead_address',
        'lead_city',
        'lead_country',
        'lead_state',
        'lead_zip',
        'lead_add_info',
    ];

    /**
     * The supported cast types are: integer, real, float, double, decimal:<digits>, string, boolean,
     * object, array, collection, date, datetime, and timestamp.
     * When casting to decimal, you must define the number of digits (decimal:2).
     * @var array
     */
    protected $casts = [
        'lead_author_id' => 'integer',
        'client_id' => 'integer',
        'lead_status_id' => 'integer',
        'lead_reason_status_id' => 'integer'
    ];

    /**
     * API application fields for get
     * @var array
     */
    const API_GET_FIELDS = [
        'leads.lead_address',
        'leads.lead_city',
        'leads.lead_zip',
        'leads.latitude',
        'leads.longitude',
        'leads.lead_estimator',
        'leads.lead_reffered_by'
    ];

    const CLIENT_FILES_STATUSES = [
        'inprogress' => [
            'name' => 'inprogress',
            'text' => 'In progress',
            'default' => true,
            'badge' => 'bg-warning'
        ],
        'completed' => [
            'name' => 'completed',
            'text' => 'Completed',
            'default' => false,
            'badge' => 'bg-success'
        ],
        'declined' => [
            'name' => 'declined',
            'text' => 'Declined',
            'default' => false,
            'badge' => 'bg-danger'
        ],
        'all' => [
            'name' => 'all',
            'text' => 'All',
            'default' => false,
            'badge' => 'bg-info'
        ]
    ];

    public function task() {
        return $this->hasOne(Task::class, 'task_lead_id', 'lead_id');
    }

    function user()
    {
        return $this->hasOne(User::class, 'id', 'lead_estimator');
    }

    function estimator()
    {
        return $this->user();
    }

    function client()
    {
        return $this->hasOne(Client::class, 'client_id', 'client_id')
            ->withCount('workorders')
            ->with('primary_contact');
    }

    function status()
    {
        return $this->hasOne(LeadStatus::class, 'lead_status_id', 'lead_status_id');
    }

    function reasonStatus()
    {
        return $this->hasOne(LeadReasonStatus::class, 'reason_id', 'lead_reason_status_id');
    }

    function lead_services()
    {
        return $this->belongsToMany(Service::class, 'lead_services', 'lead_id', 'services_id');
    }

    function servicesSelect() {
        return $this->lead_services()
            ->select([
                'service_id',
                'service_name',
                'service_description',
                'service_markup',
                'service_attachments',
                'service_default_crews',
                'is_product',
                'is_bundle',
                'cost'
            ]);
    }

    function services() {
        return $this->servicesSelect()
            ->where('is_product', 0)
            ->where('is_bundle', 0);
    }

    function products() {
        return $this->servicesSelect()
            ->where('is_product', 1);
    }

    function bundles() {
        return $this->servicesSelect()
            ->where('is_bundle', 1);
    }

    function estimate()
    {
        return $this->hasOne(Estimate::class, 'lead_id', 'lead_id');
    }

    function leadAuthor()
    {
        return $this->hasOne(User::class, 'id', 'lead_author_id');
    }


    /********************* Sorts **********************/

    public function scopeDescending($query)
    {
        return $query->orderBy($this->table . '.' . $this->primaryKey, 'DESC');
    }

    public function scopeAscending($query)
    {
        return $query->orderBy($this->table . '.' . $this->primaryKey, 'ASC');
    }

    /********************* Sorts **********************/

    /********************* Status **********************/
    public function scopeDefaultStatus($query)
    {
        return $query->whereHas('status', function (Builder $query) {
            $query->default();
        });
    }

    public function scopeDeclinedStatus($query)
    {
        return $query->whereHas('status', function (Builder $query) {
            $query->declined();
        });
    }

    public function scopeEstimatedStatus($query)
    {
        return $query->whereHas('status', function (Builder $query) {
            $query->estimated();
        });
    }

    public function scopeForApprovalStatus($query)
    {
        return $query->whereHas('status', function (Builder $query) {
            $query->forApproval();
        });
    }

    public function scopeDraftStatus($query)
    {
        return $query->whereHas('status', function (Builder $query) {
            $query->draft();
        });
    }

    public function scopePostponePassed($query)
    {
        return $query->where('lead_postpone_date', '<=', date('Y-m-d'));
    }

    /********************* Status **********************/

    public function scopeWithoutAppends($query)
    {
        self::$withoutAppends = true;
        return $query;
    }

    protected function getArrayableAppends()
    {
        if (self::$withoutAppends){
            return [];
        }

        return parent::getArrayableAppends();
    }

    function getFullAddressAttribute()
    {
        if (!isset($this->attributes['lead_address']) || !isset($this->attributes['lead_country'])) {
            return null;
        }

        $address_array = [
            $this->attributes['lead_address'],
            $this->attributes['lead_city'],
            $this->attributes['lead_state'],
            $this->attributes['lead_zip']
        ];

        $address = implode(', ', array_diff($address_array, array('', null, false)));

        return $address;
    }

    function getWaypointAttribute(){
        if(isset($this->attributes['latitude']) && $this->attributes['latitude'])
            return $this->attributes['latitude'] . ',' . $this->attributes['longitude'];

        return urlencode($this->attributes['lead_address']??'') . ',' . urlencode($this->attributes['lead_city']??'') . ',' . urlencode($this->attributes['lead_state']??'') . ',' . urlencode($this->attributes['lead_zip']??'');
    }

    function getLeadDaysAttribute()
    {
        if (!isset($this->attributes['lead_date_created'])) {
            return null;
        }
        $leadDays = (string)round((strtotime(date('Y-m-d')) - strtotime(date('Y-m-d',
                    strtotime($this->attributes['lead_date_created'])))) / 86400);
        return $leadDays;
    }

    function getMarkerColorAttribute()
    {
        return config_item('default_lead_marker_color') ? config_item('default_lead_marker_color') : self::DEFAULT_MARKER_COLOR;
    }

    function getMarkerPriorityIconAttribute()
    {
        if (!isset($this->attributes['lead_priority'])) {
            return false;
        }

        if ($this->attributes['lead_priority'] && isset(self::PRIORITY_MAP_MARKER_ICON[$this->attributes['lead_priority']])) {
            return self::PRIORITY_MAP_MARKER_ICON[$this->attributes['lead_priority']];
        }

        return false;
    }

    function getLeadDateCreatedViewAttribute()
    {
        if (!isset($this->attributes['lead_date_created'])) {
            return false;
        }

        return getDateTimeWithDate($this->attributes['lead_date_created'], 'Y-m-d H:i:s', true);
    }

    public static function getPriorities()
    {
        return self::PRIORITY;
    }

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
                DB::raw("
            IF(invoices.id IS NOT NULL, CONCAT(invoices.date_created, ' ','00:00:00'), 
                IF(workorders.id IS NOT NULL, CONCAT(workorders.date_created, ' ','00:00:00'),
                    IF(estimates.estimate_id IS NOT NULL, from_unixtime(estimates.date_created, '%Y-%m-%d %H:%i:%s'), lead_date_created)
                )
            ) as item_date_created"),
                DB::raw("
            IF(invoices.id IS NOT NULL, CONCAT('invoices'), 
                IF(workorders.id IS NOT NULL, CONCAT('workorders'),
                    IF(estimates.estimate_id IS NOT NULL, CONCAT('estimates'), CONCAT('leads'))
                )
            ) as item_module_name"),
                DB::raw("CONCAT('details') as item_action_name"),
                DB::raw("
            IF(invoices.id IS NOT NULL, invoices.id, 
                IF(workorders.id IS NOT NULL, workorders.id,
                    IF(estimates.estimate_id IS NOT NULL, estimates.estimate_id, leads.lead_id)
                )
            ) as item_id"),
                DB::raw("
            IF(invoices.id IS NOT NULL, invoices.invoice_no, 
                IF(workorders.id IS NOT NULL, workorders.workorder_no,
                    IF(estimates.estimate_id IS NOT NULL, estimates.estimate_no, clients.client_id)
                )
            ) as item_no"),
                DB::raw("
            IF(invoices.id IS NOT NULL, invoices.invoice_no, 
                IF(workorders.id IS NOT NULL, workorders.workorder_no,
                    IF(estimates.estimate_id IS NOT NULL, estimates.estimate_no, lead_no)
                )
            ) as item_title"),
                DB::raw("
            IF(invoices.id IS NOT NULL, CONCAT('5'), 
                IF(workorders.id IS NOT NULL, CONCAT('4'),
                    IF(estimates.estimate_id IS NOT NULL, CONCAT('3'), CONCAT('2'))
                )
            ) as item_position"),
                DB::raw("CONCAT(NULL) as total"),
                DB::raw("MATCH ({$columns}) AGAINST ('" . $search . "' IN BOOLEAN MODE) AS relevance_score")
            ])
            ->join('clients', ['leads.client_id' => 'clients.client_id'])
            ->leftJoin('estimates', ['leads.lead_id' => 'estimates.lead_id'])
            ->leftJoin('workorders', ['estimates.estimate_id' => 'workorders.estimate_id'])
            ->leftJoin('invoices', ['estimates.estimate_id' => 'invoices.estimate_id'])
            ->leftJoin('clients_contacts', ['cc_client_id' => 'clients.client_id', 'cc_print' => DB::raw('1')])
            ->groupBy('leads.lead_id')->permissions();
    }

    function getLeadsByDefaultStatus($where)
    {
        return $this->join('lead_statuses', 'leads.lead_status_id', '=',
            'lead_statuses.lead_status_id')->where($where)->where('lead_statuses.lead_status_default',
            DB::raw('1'))->get()->toArray();
    }

    /**
     * @param $query
     * @return mixed
     */
    function scopeBaseFields($query)
    {
        $query_string = implode(',', $this->base_fields);
        return $query->select(DB::raw($query_string));
    }

    function scopeScheduleFields($query){
        $query_string = implode(',', $this->schedule_fields);
        return $query->select(DB::raw($query_string));
    }

    function getSearchable()
    {
        return $this->searchable;
    }

    /**
     * @param $query
     * @return mixed
     */
    function scopeApiFields($query)
    {
        return $query->select([
            Lead::tableName() . '.' . Lead::ATTR_LEAD_ID,
            Lead::tableName() . '.' . Lead::ATTR_LEAD_COUNTRY,
            Lead::tableName() . '.' . Lead::ATTR_LEAD_STATE,
            Lead::tableName() . '.' . Lead::ATTR_LEAD_CITY,
            Lead::tableName() . '.' . Lead::ATTR_LEAD_ADDRESS,
            Lead::tableName() . '.' . Lead::ATTR_LEAD_ZIP,
            Lead::tableName() . '.' . Lead::ATTR_LATITUDE,
            Lead::tableName() . '.' . Lead::ATTR_LONGITUDE,
            Lead::tableName() . '.' . Lead::ATTR_LEAD_ADD_INFO_,
        ]);
    }

    /**
     * @param $query
     * @return mixed
     */
    function scopePermissions($query)
    {
        $user = request()->user();

        if (!isset($user) || is_null($user)) {
            return $query;
        }

        if (is_cl_permission_none()) {
            $query->where('leads.lead_id', -1);
        } elseif (is_cl_permission_owner()) {
            $query->where(function ($query) use ($user) {
                $query->where('leads.lead_author_id', $user->id)->orWhere('leads.lead_estimator', $user->id);
            });
        }

        return $query;
    }

    /**
     * @param array $where
     * @param array $whereOr
     * @param string $query_status
     * @param array $order
     * @return mixed
     */
    public function getLeadsQuery($where = [], $whereOr = [], $query_status = "New", $order = [])
    {
        $CI = &get_instance();
        $CI->load->model('mdl_clients');

        $clientPermissionsSubQuery = $CI->mdl_clients->getPermissionsSubQuery();

        $leadQuery = Lead::select(DB::raw("leads.*, lead_statuses.lead_status_name, lead_statuses.lead_status_default, lead_statuses.lead_status_draft, lead_reason_status.*, 
        clients_contacts.*, users.id, users.firstname, users.lastname, users.color, clients.client_brand_id, clients.client_name, 
    clients.client_address, clients.client_city, clients.client_date_created, c.client_name as reffered_client, c.client_id as reffered_client_id, u.id as reffered_user_id, 
	CONCAT(u.firstname, ' ', u.lastname) as reffered_user_text, 
	COUNT(workorders.id) as count_workorders, IF(leads.lead_author_id IS NULL, leads.lead_created_by, CONCAT(author.firstname, ' ', author.lastname)) as lead_createdBy, 
	CONCAT(crc.cc_name, ', ', IFNULL(estimates.estimate_no, 'No Estimates'), ' - ', c.client_address, ', ', c.client_city) as reffered_client_text"))
            ->join('clients', 'leads.client_id', '=', 'clients.client_id')
            ->leftJoin('clients_contacts', function ($query) {
                $query->on('clients.client_id', '=', 'clients_contacts.cc_client_id')
                    ->on('clients_contacts.cc_print', '=', DB::raw('1'));
            })
            ->leftJoin('users', 'leads.lead_estimator', '=', 'users.id')
            ->leftJoin('users as author', 'leads.lead_author_id', '=', 'author.id')
            ->leftJoin('clients as c', 'leads.lead_reffered_client', '=', 'c.client_id')
            ->leftJoin('clients_contacts as crc', function ($query) {
                $query->on('c.client_id', '=', 'crc.cc_client_id')
                    ->on('crc.cc_print', '=', DB::raw('1'));
            })
            ->leftJoin('estimates', 'estimates.client_id', '=', 'c.client_id')
            ->leftJoin('workorders', 'clients.client_id', '=', 'workorders.client_id')
            ->leftJoin('lead_statuses', 'leads.lead_status_id', '=', 'lead_statuses.lead_status_id')
            ->leftJoin('lead_reason_status', 'leads.lead_reason_status_id', '=', 'lead_reason_status.reason_id')
            ->leftJoin('users as u', 'leads.lead_reffered_user', '=', 'u.id')
            ->leftJoin('reference', 'reference.id', '=', 'leads.lead_reffered_by');

        if ($query_status == 'New') {
            $leadQuery->where(['lead_statuses.lead_status_default' => '1']);
        } elseif ($query_status == 'not_estimated') {
            $leadQuery->where('lead_statuses.lead_status_estimated', '<>', '1');
        } elseif ($query_status != '') {
            $leadQuery->where(['lead_statuses.lead_status_id' => $query_status]);
        }

        if (isset($whereOr) && !empty($whereOr)) {
            foreach ($whereOr as $key => $value) {
                $leadQuery->orWhere($value);
            }
        }
        if (isset($where) && !empty($where)) {
            foreach ($where as $value) {
                $leadQuery->where($value);
            }
        }

        if (is_cl_permission_owner() && $clientPermissionsSubQuery) {
            $leadQuery->leftJoin(DB::raw('(' . $clientPermissionsSubQuery . ') as perm'), 'perm.client_id', '=', 'leads.client_id');
            $leadQuery->whereNotNull('perm.client_id');
        } elseif (is_cl_permission_none()) {
            $leadQuery->where(['leads.lead_estimator' => -1]);
        }
        if (isset($order) && !empty($order)) {
            foreach ($order as $key => $value) {
                $leadQuery->orderBy($key, $value);
            }
        } else {
            $leadQuery->orderBy('leads.lead_priority', 'DESC');
        }

        $leadQuery->groupBy(Lead::tableName() . '.' . Lead::ATTR_LEAD_ID);

        return $leadQuery;
    }

    /**
     * @param array $where
     * @param array $whereOr
     * @param array $order
     * @return mixed
     */
    public static function getMyLeadsQuery($where = [], $whereOr = [], $order = [])
    {
        $leadQuery = Lead::select(DB::raw('leads.*, 
        lead_statuses.lead_status_name, lead_statuses.lead_status_default, lead_statuses.lead_status_draft, 
        lead_reason_status.*, users.id, users.firstname, users.lastname, clients.client_name,
		clients.client_address, clients.client_city'))
            ->join('lead_statuses', 'leads.lead_status_id', '=', 'lead_statuses.lead_status_id')
            ->leftJoin('lead_reason_status', 'leads.lead_reason_status_id', '=', 'lead_reason_status.reason_id')
            ->join('clients', 'leads.client_id', '=', 'clients.client_id')
            ->join('users', 'leads.lead_estimator', '=', 'users.id');

        if (isset($whereOr) && !empty($whereOr)) {
            foreach ($whereOr as $key => $value) {
                $leadQuery->orWhere($value);
            }
        }
        if (isset($where) && !empty($where)) {
            foreach ($where as $value) {
                $leadQuery->where($value);
            }
        }
        if (isset($order) && !empty($order)) {
            foreach ($order as $key => $value) {
                $leadQuery->orderBy($key, $value);
            }
        } else {
            $leadQuery->orderBy('leads.lead_priority', 'DESC');
        }

        return $leadQuery;
    }

    /**
     * Count when group by exist, done to prevent wrong  results and errors
     */
    public static  function countAggregate($query)
    {
        return DB::table(DB::raw("({$query->toSql()}) as sub"))->mergeBindings($query->toBase())->count();
    }

    /**
     * Client files status scope
     *
     * @param $query
     * @param string $status
     * @return mixed
     */
    public function scopeClientFilesStatus($query, string $status = self::CLIENT_FILES_STATUSES['inprogress']['name']) {
        if ($status === self::CLIENT_FILES_STATUSES['inprogress']['name']) {
            $query->whereHas('status', function (Builder $query) {
                    $query->where('lead_status_declined', '<>', 1);
                })
                ->where(function (Builder $query) {
                    $query->doesntHave('estimate')
                    ->orWhereHas('estimate.estimate_status', function (Builder $query) {
                        $query->where('est_status_declined', '<>', 1);
                    });
                })
                ->where(function (Builder $query) {
                    $query->doesntHave('estimate.invoice')
                    ->orWhereHas('estimate.invoice.status', function (Builder $query) {
                        $query->where('completed', '<>', 1);
                    });
                });
        }
        elseif ($status === self::CLIENT_FILES_STATUSES['completed']['name']) {
            $query->whereHas('estimate.invoice.status', function (Builder $query) {
                $query->where('completed', '=', 1);
            });
        }
        elseif ($status === self::CLIENT_FILES_STATUSES['declined']['name']) {
            $query->whereHas('status', function (Builder $query) {
                    $query->where('lead_status_declined', '=', 1);
                })
                ->orWhereHas('estimate.estimate_status', function (Builder $query) {
                    $query->where('est_status_declined', '=', 1);
                });
        }

        return $query;
    }

    /**
     * Client files address scope
     *
     * @param $query
     * @param string|null $address
     * @return mixed
     */
    public function scopeClientFilesAddress($query, string $address = null) {
        if($address) {
            $query->whereLeadAddress($address);
        }
        return $query;
    }

    /**
     * Get lead with relations for client files
     *
     * @param int $clientId
     * @param string $status
     * @param string|null $address
     * @return mixed
     */
    public static function getLeadClientFiles(int $clientId, string $status = self::CLIENT_FILES_STATUSES['inprogress']['name'], string $address = null) {
        if (!array_key_exists($status, self::CLIENT_FILES_STATUSES)) {
            return [];
        }

        $CI = &get_instance();

        return Lead::select([
                'lead_id',
                'lead_no',
                'lead_author_id',
                'lead_date_created',
                'lead_address',
                'lead_city',
                'lead_state',
                'lead_zip',
                'lead_country',
                'lead_comment_note',
                'lead_status_id',
                'lead_reason_status_id',
                'lead_add_info'
            ])
            ->where('client_id', '=', $clientId)
            ->with([
                'leadAuthor:id,firstname,lastname',
                'status:lead_status_id,lead_status_name,lead_status_estimated,lead_status_declined',
                'reasonStatus:reason_id,reason_name',
                'estimate' => function ($query) use ($clientId) {
                    $query->select(['lead_id', 'estimate_id', 'estimate_no', 'date_created', 'status', 'user_id'])
                        ->with([
                            'estimate_status:est_status_id,est_status_name,est_status_declined',
                            'estimate_crews' => function ($query) {
                                $query->crewsNamesLine(false);
                            },
                            'user' => function ($query) {
                                $query->select(['id', 'firstname', 'lastname'])
                                    ->noSystem();
                            },
                            'workorder' => function ($query) {
                                $query->select(['estimate_id', 'id', 'workorder_no', 'date_created', 'wo_status'])
                                    ->with(['status:wo_status_id,wo_status_name']);
                            },
                            'invoice' => function ($query) {
                                $query->select([
                                        'estimate_id',
                                        'id',
                                        'invoice_no',
                                        'in_status',
                                        'date_created',
                                        'invoice_last_qb_time_log',
                                        'invoice_last_qb_sync_result',
                                        'invoice_qb_id',
                                        'invoice_like',
                                        'invoice_feedback'
                                    ])->with(['status:invoice_status_id,invoice_status_name,completed']);
                            }
                        ])
                        // TODO: add statuses query
                        ->clientFilesTotals(['estimates.client_id' => $clientId]/*, [ statuses query! ]*/);
                }
            ])
            ->clientFilesStatus($status)
            ->clientFilesAddress($address)
            ->orderBy('lead_date_created', 'DESC')
            ->get()
            ->map(function (Lead $lead) use ($CI) {
                $lead->makeHidden(['waypoint']);
                if ($lead->estimate && $lead->estimate->invoice) {
                    $lead->estimate->invoice->qb_html = null;
                    if ($access_token = config_item('accessTokenKey')) {
                        $lead->estimate->invoice->qb_html = $CI->load->view(
                            'qb/partials/qb_logs',
                            [
                                'lastQbTimeLog' => $lead->estimate->invoice->invoice_last_qb_time_log,
                                'lastQbSyncResult' => $lead->estimate->invoice->invoice_last_qb_sync_result,
                                'module' => 'invoice',
                                'entityId' => $lead->estimate->invoice->id,
                                'entityQbId' => $lead->estimate->invoice->invoice_qb_id,
                                'class' => '',
                                'access_token'=> $access_token
                            ],
                            true
                        );
                    }

                }

                return $lead;
            })
            ->toArray();
    }

    /**
     * Count leads for client files
     *
     * @param int $clientId
     * @param string $status
     * @param string|null $address
     * @return mixed
     */
    public static function countLeadClientFiles(int $clientId, string $status = self::CLIENT_FILES_STATUSES['inprogress']['name'], string $address = null) {
        if (!array_key_exists($status, self::CLIENT_FILES_STATUSES)) {
            return 0;
        }

        return Lead::select([
                'lead_id',
                'lead_no',
                'lead_status_id',
            ])
            ->where('client_id', '=', $clientId)
            ->with([
                'status:lead_status_id,lead_status_estimated,lead_status_declined',
                'estimate' => function ($query) use ($clientId) {
                    $query->select(['lead_id', 'estimate_id', 'status'])
                        ->with([
                            'estimate_status:est_status_id,est_status_declined',
                            'workorder' => function ($query) {
                                $query->select(['estimate_id', 'id', 'wo_status'])
                                    ->with(['status:wo_status_id']);
                            },
                            'invoice' => function ($query) {
                                $query->select([
                                        'estimate_id',
                                        'id',
                                        'in_status',
                                    ])->with(['status:invoice_status_id,completed']);
                            }
                        ])
                        ->withoutAppends();
                }
            ])
            ->clientFilesStatus($status)
            ->clientFilesAddress($address)
            ->count();
    }

    /**
     * Get APP project details
     *
     * @param $leadId
     * @return mixed
     */
    public static function getProjectDetails($leadId) {
        $lead = Lead::select([
            'client_id',
            'lead_id',
            'lead_no',
            'lead_date_created',
            'lead_body',
            'lead_address',
            'lead_city',
            'lead_state',
            'lead_country',
            'lead_zip',
            'latitude',
            'longitude',
            'lead_status_id',
            'lead_estimator',
            'lead_reason_status_id'
        ])
            ->where('lead_id', $leadId)
            ->with([
                'estimator:id,firstname,lastname',
                'status',
                'reasonStatus:reason_id,reason_name',
                'services',
                'products',
                'bundles'
            ])
            ->withoutAppends()
            ->first();

        if (!$lead) {
            return false;
        }

        $CI =& get_instance();
        $CI->load->library('Common/ServicesActions');

        $lead->services->makeHidden(['pivot']);
        $lead->services->map(function ($service) use ($CI) {
            $service->service_attachments = $CI->servicesactions->getServiceAttachment($service->service_attachments);
            return $service;
        });

        $lead->products->makeHidden(['pivot']);

        $lead->bundles->makeHidden(['pivot']);
        $lead->bundles->map(function ($bundle) use ($CI) {
            return $CI->servicesactions->addRecordsInBundle($bundle);
        });

        // add lead files
        $leadFilesPath = 'uploads/clients_files/' . $lead->client_id . '/leads/' . str_pad($lead->lead_id, 5, '0', STR_PAD_LEFT) . '-L/';
        $files = bucketScanDir($leadFilesPath);

        foreach($files as $key => $file) {
            $files[$key] = $leadFilesPath . $file;
        }

        $lead->attributes['files'] = $files;

        // get lead relations
        $result = Lead::select([
            'lead_id',
            'client_id',
        ])
            ->where('lead_id', $leadId)
            ->with([
                'client' => function ($query) {
                    $query->select(Client::API_FIELDS)
                        ->with(['primary_contact']);
                },
                'estimate' => function ($query) use ($lead) {
                    $query->select(['lead_id', 'estimate_id', 'estimate_no', 'date_created', 'status', 'user_id', 'estimate_reason_decline'])
                        ->with([
                            'estimate_status:est_status_id,est_status_name,est_status_declined,est_status_default,est_status_confirmed,est_status_sent',
                            'estimate_reason_status',
                            'estimate_crews' => function ($query) {
                                $query->crewsNamesLine(false);
                            },
                            'estimates_service.service',
                            'estimates_service.classes',
                            'estimates_service.equipments',
                            'estimates_service.expenses',
                            'estimates_service.services_crew.crew',
                            'estimates_service.tree_inventory.tree',
                            'estimates_service.tree_inventory.tree_inventory_work_types',
                            'estimates_service.tree_inventory.tree_inventory_work_types.work_type',
                            'estimates_service.bundle_service',
                            'estimates_service.classes',
                            'estimates_service' => function($query) {
                                $query->withoutBundleServices();
                            },
                            'estimates_service.bundle.estimate_service',
                            'estimates_service.bundle.estimate_service.service',
                            'estimates_service.bundle.estimate_service.classes',
                            'estimates_service.bundle.estimate_service.equipments',
                            'estimates_service.bundle.estimate_service.expenses',
                            'estimates_service.bundle.estimate_service.services_crew.crew',
                            'user:id,firstname,lastname',
                            'workorder' => function ($query) {
                                $query->select(['estimate_id', 'id', 'workorder_no', 'date_created', 'wo_status'])
                                    ->with(['status:wo_status_id,wo_status_name']);
                            },
                            'invoice' => function ($query) {
                                $query->select([
                                    'estimate_id',
                                    'id',
                                    'invoice_no',
                                    'in_status',
                                    'date_created',
                                    'overdue_date'
                                ])->with(['status:invoice_status_id,invoice_status_name,completed,priority']);
                            },
                            'client_payments'
                        ])
                        ->detailsApiTotals(['estimates.client_id' => $lead->client_id]);
                }
            ])
            ->withoutAppends()
            ->first();

        if (!empty($result->estimate)) {
            $result->estimate->setAppends(['files']);

            if (!empty($result->estimate->estimates_service)) {
                foreach ($result->estimate->estimates_service as $service) {
                    if (!empty($service->tree_inventory)) {
                        $service->profile_estimate_service_ti_title = $service->estimate_service_ti_title;
                        $service->profile_service_description = $service->service_description;
                        if (!empty($service->tree_inventory) && !empty($service->tree_inventory->ties_priority)) {
                            $service->profile_estimate_service_ti_title .= ', Priority: ' . $service->tree_inventory->ties_priority;
                            if (!empty($service->tree_inventory->tree_inventory_work_types)) {
                                $workTypes = 'Work Types: ';
                                foreach ($service->tree_inventory->tree_inventory_work_types as $key => $workType) {
                                    if (!empty($workType->work_type)) {
                                        $workTypes .= $workType->work_type->ip_name;
                                    }
                                    if (count($service->tree_inventory->tree_inventory_work_types) - 1 != $key) {
                                        $workTypes .= ', ';
                                    }
                                }
                                $service->profile_service_description = $workTypes . '<br>' . $service->service_description;
                            }
                        }
                    }
                }
            }
        }

        $result = $result->toArray();

        unset($result['lead_id']);
        unset($result['client_id']);

        $result = ['lead' => $lead->toArray()] + $result;

        $result['client_payments'] = [];

        if ($result['estimate'] && isset($result['estimate']['client_payments'])) {
            $result['client_payments'] = $result['estimate']['client_payments'];
            unset($result['estimate']['client_payments']);

            foreach ($result['client_payments'] as &$payment) {
                $payment['payment_date'] = date('Y-m-d', $payment['payment_date']);

                if ($payment['payment_file']) {
                    $payment['payment_file'] = 'uploads/payment_files/' . $result['estimate']['client_id'] . '/' . $result['estimate']['estimate_no'] . '/' . $payment['payment_file'];
                }
            }
        }

        return $result;
    }
}
