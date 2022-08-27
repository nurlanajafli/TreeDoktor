<?php

namespace application\modules\estimates\models;

use application\core\Database\Casts\AppDate;
use application\core\Database\Casts\AppDateTime;
use application\core\Database\EloquentModel;
use application\modules\crew\models\Crew;
use application\modules\emails\models\Email;
use application\modules\payments\models\ClientPayment;
use application\modules\user\models\User;
use application\modules\leads\models\Lead;

use application\modules\workorders\models\Workorder;
use application\modules\invoices\models\Invoice;

use application\modules\clients\models\Client;
use application\modules\clients\models\ClientsContact;

use application\modules\estimates\models\EstimatesService;

use application\modules\dashboard\models\traits\FullTextSearch;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class Estimate extends EloquentModel
{
    use FullTextSearch;

    const FILES_DIR = 'uploads/clients_files/%d/estimates/%s';
    const FILES_WO_DIR = 'uploads/payment_files/%d/%s';
    const ATTR_ESTIMATE_ID = 'estimate_id';
    const ATTR_ESTIMATE_NO = 'estimate_no';
    const ATTR_ESTIMATE_BALANCE = 'estimate_balance';
    const ATTR_ESTIMATE_LAST_CONTACT = 'estimate_last_contact';
    const ATTR_ESTIMATE_COUNT_CONTACT = 'estimate_count_contact';
    const ATTR_CLIENT_ID = 'client_id';
    const ATTR_LEAD_ID = 'lead_id';
    const ATTR_ESTIMATE_BRAND_ID = 'estimate_brand_id';
    const ATTR_DATE_CREATED = 'date_created';
    const ATTR_STATUS = 'status';
    const ATTR_ESTIMATE_HST_DISABLED = 'estimate_hst_disabled';
    const ATTR_ESTIMATE_ITEM_TEAM = 'estimate_item_team';
    const ATTR_ESTIMATE_ITEM_ESTIMATED_TIME = 'estimate_item_estimated_time';
    const ATTR_ESTIMATE_ITEM_EQUIPMENT_SETUP = 'estimate_item_equipment_setup';
    const ATTR_ESTIMATE_ITEM_NOTE_CREW = 'estimate_item_note_crew';
    const ATTR_ESTIMATE_CREW_NOTES = 'estimate_crew_notes';
    const ATTR_ESTIMATE_ITEM_NOTE_ESTIMATE = 'estimate_item_note_estimate';
    const ATTR_ESTIMATE_ITEM_NOTE_PAYMENT = 'estimate_item_note_payment';
    const ATTR_ARBORIST = 'arborist';
    const ATTR_BUCKET_TRUCK_OPERATOR = 'bucket_truck_operator';
    const ATTR_CLIMBER = 'climber';
    const ATTR_CHIPPER_OPERATOR = 'chipper_operator';
    const ATTR_GROUNDSMEN = 'groundsmen';
    const ATTR_BUCKET_TRUCK = 'bucket_truck';
    const ATTR_WOOD_CHIPPER = 'wood_chipper';
    const ATTR_DUMP_TRUCK = 'dump_truck';
    const ATTR_CRANE = 'crane';
    const ATTR_STUMP_GRINDER = 'stump_grinder';
    const ATTR_BRUSH_DISPOSAL = 'brush_disposal';
    const ATTR_LEAVE_WOOD = 'leave_wood';
    const ATTR_FULL_CLEANUP = 'full_cleanup';
    const ATTR_STUMP_CHIPS = 'stump_chips';
    const ATTR_PERMIT_REQUIRED = 'permit_required';
    const ATTR_USER_ID = 'user_id';
    const ATTR_ESTIMATE_SCHEME = 'estimate_scheme';
    const ATTR_ESTIMATE_REASON_DECLINE = 'estimate_reason_decline';
    const ATTR_ESTIMATE_PROVIDED_BY = 'estimate_provided_by';
    const ATTR_ESTIMATE_PDF_FILES = 'estimate_pdf_files';
    const ATTR_UNSUBSCRIBE = 'unsubscribe';
    const ATTR_NOTIFICATION = 'notification';
    const ATTR_PAID_BY_CC = 'paid_by_cc';
    const ATTR_ESTIMATE_REVIEW_DATE = 'estimate_review_date';
    const ATTR_ESTIMATE_REVIEW_NUMBER = 'estimate_review_number';
    const ATTR_OVERHEAD_PER_HOUR_COSTS = 'overhead_per_hour_costs';
    const ATTR_ESTIMATE_PLANED_COMPANY_COST = 'estimate_planned_company_cost';
    const ATTR_ESTIMATE_PLANNED_CREWS_COST = 'estimate_planned_crews_cost';
    const ATTR_ESTIMATE_PLANNED_EQUIPMENTS_COST = 'estimate_planned_equipments_cost';
    const ATTR_ESTIMATE_PLANNED_EXTRA_EXPENSES = 'estimate_planned_extra_expenses';
    const ATTR_ESTIMATE_PLANNED_OVERHEADS_COST = 'estimate_planned_overheads_cost';
    const ATTR_ESTIMATE_PLANNED_PROFIT = 'estimate_planned_profit';
    const ATTR_ESTIMATE_PLANNED_PROFIT_PERCENTS = 'estimate_planned_profit_percents';
    const ATTR_ESTIMATE_PLANNED_TAX = 'estimate_planned_tax';
    const ATTR_ESTIMATE_PLANNED_TOTAL = 'estimate_planned_total';
    const ATTR_ESTIMATE_PLANNED_TOTAL_FOR_SERVICES = 'estimate_planned_total_for_services';
    const ATTR_ESTIMATE_QB_ID = 'estimate_qb_id';
    const ATTR_ESTIMATE_TAX_NAME = 'estimate_tax_name';
    const ATTR_ESTIMATE_TAX_RATE = 'estimate_tax_rate';
    const ATTR_ESTIAMTE_TAX_VALUE = 'estimate_tax_value';
    const ATTR_TREE_INVENTORY_PDF = 'tree_inventory_pdf';

    public static $withoutAppends = false;

    /**
     * @var array
     */
    protected $appends = ['date_created_view'];

    /**
     * Estimate table primary key name
     * @var string
     */
    protected $primaryKey = 'estimate_id';
    /**
     * Table  name
     * @var string
     */
    protected $table = 'estimates';

    /**
     * The columns of the full text index
     */
    protected $searchable = [
        'estimate_no',
        'estimate_crew_notes',
        'estimate_item_note_payment'
    ];

    protected $fillable = [
        'estimate_id',
        'estimate_no',
        'client_id',
        'lead_id',
        'date_created',
        'status',
        'user_id',
        'estimate_qb_id',
        'estimate_tax_name',
        'estimate_tax_rate',
        'estimate_tax_value',
        'estimate_brand_id'
    ];

    const LIGHT_FIELDS = [
        'estimates.estimate_id',
        'estimates.client_id',
        'estimates.lead_id',
        'estimates.status',
        'estimates.user_id',
        'estimate_crew_notes'
    ];

    const BASE_FIELDS = [
        'estimates.estimate_id',
        'estimates.estimate_no',
        'estimates.client_id',
        'estimates.lead_id',
        'estimates.date_created',
        'estimates.status',
        'estimates.user_id',
        'estimates.estimate_qb_id',
        'estimates.estimate_tax_name',
        'estimates.estimate_tax_rate',
        'estimates.estimate_tax_value',
        'estimates.estimate_brand_id',
        'estimates.estimate_planned_total_for_services',
        'estimates.estimate_reason_decline',
        'estimates.leave_wood',
        'estimates.full_cleanup',
        'estimates.brush_disposal',
        'estimates.estimate_pdf_files'
    ];

    const NOTES_FIELDS = [
        'estimate_item_team',
        'estimate_item_estimated_time',
        'estimate_item_equipment_setup',
        'estimate_item_note_estimate',
        'estimate_item_note_payment',

        'estimate_crew_notes',
        'tree_inventory_pdf'
    ];

    const TOTAL_FIELDS = [
        'sum_without_tax',
        'sum_actual_without_tax',
        'payments_total',
        'total_due',
        'total_with_tax',
        'total_tax',
        'tax_value',
        'discount_total',
        'interests_total',
        'discount_comment',
        'sum_for_services',
    ];

    public $base_fields = [
        'estimates.estimate_id',
        'estimates.estimate_no',
        'estimates.client_id',
        'estimates.lead_id',
        'estimates.date_created',
        'estimates.status',
        'estimates.user_id',
        'estimates.estimate_qb_id',
        'estimates.estimate_tax_name',
        'estimates.estimate_tax_rate',
        'estimates.estimate_tax_value',
        'estimates.estimate_brand_id',
        'estimates.estimate_planned_total_for_services',
        'estimates.estimate_reason_decline',
        'estimates.estimate_pdf_files',
        'estimates.estimate_review_number',
    ];

    /**
     * @var string[]
     */
    public $portal_fields = [
        'estimates.brush_disposal',
        'estimates.client_id',
        'estimates.date_created',
        'estimates.estimate_id',
        'estimates.estimate_no',
        'estimates.estimate_review_number',
        'estimates.full_cleanup',
        'estimates.lead_id',
        'estimates.leave_wood',
        'estimates.user_id',
        'estimates.estimate_pdf_files',
        'estimates.status',
        'estimates.estimate_brand_id',
    ];

    public $notes_fields = [
        'estimate_item_team',
        'estimate_item_estimated_time',
        'estimate_item_equipment_setup',
        'estimate_item_note_estimate',
        'estimate_item_note_payment',

        'estimate_crew_notes',
        'tree_inventory_pdf'
    ];

    /**
     * The supported cast types are: integer, real, float, double, decimal:<digits>, string, boolean,
     * object, array, collection, date, datetime, and timestamp.
     * When casting to decimal, you must define the number of digits (decimal:2).
     * @var array
     */
    protected $casts = [
        'client_id' => 'integer',
        'lead_id' => 'integer',
        'status' => 'integer',
        'user_id' => 'integer'
    ];

    /**
     * API application fields for get
     * @var array
     */
    const API_GET_FIELDS = [
        'estimates.estimate_id',
        'estimates.user_id',
        'estimates.estimate_crew_notes'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    function lead(){
        return $this->hasOne(Lead::class, 'lead_id', 'lead_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    function workorder(){
        return $this->hasOne(Workorder::class, 'estimate_id', 'estimate_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    function invoice(){
        return $this->hasOne(Invoice::class, 'estimate_id', 'estimate_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    function client(){
        return $this->hasOne(Client::class, 'client_id', 'client_id')->with('primary_contact');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    function user(){
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    /**
     * @return \application\models\Relations\HasManySyncable
     */
    function estimates_service(){
        return $this->hasMany(EstimatesService::class, 'estimate_id');
    }

    function estimates_service_totals(){
        return $this->estimates_service();
    }
    /*
    Double relations
    function estimate_services(){
        return $this->hasMany(EstimatesService::class, 'estimate_id', 'estimate_id');
    }
    */

    /**
     * @return \application\models\Relations\HasManySyncable
     */
    function estimates_services_crew(){
        return $this->hasMany(EstimatesServicesCrew::class, 'crew_estimate_id');
    }

    function estimate_crews(){
        return $this->estimates_services_crew();
    }

    function estimates_new_services_crews(){
        return $this->estimates_services_crew()->newService();
    }

    function crews() {
        return $this->belongsToMany(Crew::class, 'estimates_services_crews', 'crew_estimate_id', 'crew_user_id');
    }
    /**
     * @return \application\models\Relations\HasManySyncable
     */
    function client_payments() {
        return $this->hasMany(ClientPayment::class, 'estimate_id', 'estimate_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    function estimate_status() {
        return $this->hasOne(EstimateStatus::class, 'est_status_id', 'status');
    }

    function emails() {
        return $this->morphToMany(Email::class, 'emailable');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    function estimate_reason_status() {
        return $this->hasOne(EstimateReasonStatus::class, 'reason_id', 'estimate_reason_decline');
    }

    /**
     * @param $query
     * @param $wo_status
     * @param array $where
     * @param bool $addSelect
     * @return mixed
     */
    public function scopeWithTotals($query, $wo_status, $where = [], $addSelect = true)
    {
        $CI = &get_instance();
        $CI->load->model('mdl_estimates_orm');
        $totalsSubQuery = $CI->mdl_estimates_orm->calcQuery(empty($where) ? is_null($wo_status) ? [] :['workorders.wo_status'=>$wo_status] : $where);

        if ($addSelect) {
            $query->addSelect(DB::raw('estimates.estimate_id as estId, totals.sum_without_tax, totals.sum_actual_without_tax, totals.payments_total, totals.total_due, totals.total_with_tax, totals.total_tax, totals.tax_value, totals.discount_total, totals.interests_total, totals.discount_comment, totals.sum_for_services, totals.sum_taxable, totals.sum_non_taxable'));
        }
        return $query->leftJoin(DB::raw('(' . $totalsSubQuery. ') AS totals'), ['totals.estimate_id' => 'estimates.estimate_id']);
    }

    /**
     * Get totals for client files
     *
     * @param $query
     * @param array $where
     * @param array $extraJoin
     * @return mixed
     */
    public function scopeClientFilesTotals($query, array $where = [], $extraJoin = false)
    {
        $CI = &get_instance();
        $CI->load->model('mdl_estimates_orm');
        $totalsSubQuery = $CI->mdl_estimates_orm->calcQuery($where, $extraJoin);
        return $query->select(DB::raw(
            'estimates.estimate_id,
            estimates.estimate_no,
            estimates.client_id,
            estimates.lead_id,
            estimates.date_created,
            estimates.status,
            estimates.user_id,
            estimates.estimate_tax_name,
            estimates.estimate_tax_value,
            (IFNULL(SUM(es.service_time), 0) + IFNULL(SUM(es.service_travel_time), 0)) as total_time,
            totals.sum_without_tax,
            totals.sum_actual_without_tax,
            totals.payments_total,
            totals.total_due,
            totals.total_with_tax,
            estimates.leave_wood,
            estimates.full_cleanup,
            estimates.brush_disposal,
            totals.total_tax,
            totals.tax_value,
            totals.discount_total,
            totals.interests_total,
            totals.discount_comment,
            totals.sum_for_services'
        ))
        ->leftJoin(DB::raw('(' . $totalsSubQuery. ') AS totals'), ['totals.estimate_id' => 'estimates.estimate_id'])
        ->leftJoin('estimates_services as es', ['es.estimate_id' => 'estimates.estimate_id'])
        ->groupBy('estimates.estimate_id');
    }

    /**
     * @param $query
     * @param $id
     * @return Estimate|\Illuminate\Database\Eloquent\Model|object|null
     */
    public function scopeTotalEstimateBalance($query, $id)
    {
        return Estimate::where($this->table . '.' . $this->primaryKey, $id)->withTotals(null, [$this->table . '.' .$this->primaryKey => $id], false)
            ->leftJoin("leads", 'estimates.lead_id', '=', 'leads.lead_id');
    }

    /**
     * Get totals for app details
     *
     * @param $query
     * @param array $where
     * @param array $extraJoin
     * @return mixed
     */
    public function scopeDetailsApiTotals($query, array $where = [], $extraJoin = false)
    {
        $CI = &get_instance();
        $CI->load->model('mdl_estimates_orm');
        $totalsSubQuery = $CI->mdl_estimates_orm->calcQuery($where, $extraJoin);
        return $query->select(DB::raw(
            'estimates.estimate_id,
            estimates.estimate_no,
            estimates.client_id,
            estimates.lead_id,
            estimates.date_created,
            estimates.status,
            estimates.user_id,
            estimates.estimate_tax_name,
            estimates.estimate_tax_value,
            estimates.estimate_tax_rate,
            estimates.estimate_balance,
            estimates.estimate_reason_decline,
            estimates.estimate_brand_id,
            estimates.full_cleanup,
            estimates.leave_wood,
            estimates.brush_disposal,
            estimates.estimate_crew_notes,
            (IFNULL(SUM(es.service_time), 0) + IFNULL(SUM(es.service_travel_time), 0)) as total_time,
            totals.sum_without_tax as total_without_tax,
            totals.total_with_tax as total_with_tax,
            totals.payments_total,
            totals.total_due,
            totals.discount_total,
            totals.interests_total,
            totals.discount_comment,
            totals.sum_for_services'
        ))
            ->leftJoin(DB::raw('(' . $totalsSubQuery. ') AS totals'), ['totals.estimate_id' => 'estimates.estimate_id'])
            ->join('estimates_services as es', ['es.estimate_id' => 'estimates.estimate_id'])
            ->groupBy('estimates.estimate_id');
    }

    /**
     * @param $query
     * @param $w_id
     * @param $wo_status
     * @return mixed
     */
    public function scopeOneWithTotals($query, $w_id,  $wo_status)
    {
        $select_string = implode(', ', $this->base_fields).', '.implode(', ', $this->notes_fields);
        $CI = &get_instance();
        $CI->load->model('mdl_estimates_orm');
        $totalsSubQuery = $CI->mdl_estimates_orm->calcQuery(['workorders.id' => $w_id, 'workorders.wo_status'=>$wo_status]);
        return $query->select(DB::raw($select_string.', totals.sum_without_tax, totals.sum_actual_without_tax, totals.payments_total, totals.total_due, totals.total_with_tax, estimates.leave_wood, estimates.full_cleanup, estimates.brush_disposal, totals.total_tax, totals.tax_value'))
            ->leftJoin(DB::raw('(' . $totalsSubQuery. ') AS totals'), ['totals.estimate_id' => 'estimates.estimate_id']);
    }

    /**
     * @param $query_string
     * @return mixed
     */
    function globalSearchQuery($query_string)
    {
        $columns = implode(',',$this->searchable);
        $search = $this->fullTextWildcards($query_string);
        return self::search($query_string)
        ->select([
            "lead_address as item_address", "client_name as item_name", "cc_phone as item_phone", "cc_name as item_cc_name", "cc_email as item_email", DB::raw("CONCAT(NULL) as item_status"), DB::raw("from_unixtime(estimates.date_created, '%Y-%m-%d %H:%i:%s') as item_date_created"), DB::raw("CONCAT('estimates') as item_module_name"), DB::raw("CONCAT('profile') as item_action_name"), "estimates.estimate_id as item_id,", "estimate_no as item_no", "estimate_no as item_title", DB::raw("CONCAT('3') as item_position"), DB::raw("CONCAT(NULL) as total"),
            DB::raw("MATCH ({$columns}) AGAINST ('".$search."' IN BOOLEAN MODE) AS relevance_score")
        ])
        ->join('clients', ['estimates.client_id' => 'clients.client_id'])
        ->join('leads', ['estimates.lead_id' => 'leads.lead_id'])
        ->leftJoin('clients_contacts', ['cc_client_id' => 'clients.client_id', 'cc_print' => DB::raw('1')])
        ->leftJoin('estimates_services', ['estimates_services.estimate_id' => 'estimates.estimate_id'])
        ->leftJoin('discounts', ['discounts.estimate_id' => 'estimates.estimate_id'])
        ->leftJoin('client_payments', ['client_payments.estimate_id' => 'estimates.estimate_id'])
        ->groupBy('estimates.estimate_id')->permissions();
    }

    public function getFilesAttribute()
    {
        $files = Storage::allFiles(
            sprintf(self::FILES_DIR, $this->getAttribute(self::ATTR_CLIENT_ID), $this->getAttribute(self::ATTR_ESTIMATE_NO))
        );

        $estimateNo = $this->getAttribute(self::ATTR_ESTIMATE_NO);

        if ($this->relationLoaded('estimates_service')) {
            foreach ($this->estimates_service as $key => &$value) {
                $value->files = array_values(array_filter($files, function ($v, $k) use ($value, $estimateNo, &$files) {
                    $match = strpos($v, '/' . $estimateNo . '/' . $value->id . '/');
                    if($match !== false) {
                        unset($files[$k]);
                    }
                    return $match !== false;
                }, ARRAY_FILTER_USE_BOTH));

                if ($value->bundle()->exists()) {
                    foreach ($value->bundle as &$b) {
                        $b->estimate_service->files = array_values(array_filter($files, function ($v, $k) use ($b, $estimateNo, &$files) {
                            $match = strpos($v, '/' . $estimateNo . '/' . $b->eb_service_id . '/');
                            if($match !== false) {
                                unset($files[$k]);
                            }
                            return $match !== false;
                        }, ARRAY_FILTER_USE_BOTH));
                    }
                }
            }
        }

        $WOFiles = Storage::allFiles(
            sprintf(self::FILES_WO_DIR, $this->getAttribute(self::ATTR_CLIENT_ID), $this->getAttribute(self::ATTR_ESTIMATE_NO))
        );

        if(!empty($WOFiles))
            $files = array_merge($WOFiles, $files);


        return array_values($files);
    }

    /**
     * @return string|null
     */
    function getDateCreatedViewAttribute()
    {
        if (!isset($this->attributes['date_created'])) {
            return false;
        }
        return getDateTimeWithTimestamp($this->attributes['date_created']);
    }

    function getSearchable()
    {
        return $this->searchable;
    }

    function getEstimateCrewNotesAttribute() {
        return isset($this->attributes['estimate_crew_notes']) && $this->attributes['estimate_crew_notes'] ? trim($this->attributes['estimate_crew_notes']) : null;
    }

    function scopePermissions($query) {
        $user = request()->user();

        if(!isset($user) || is_null($user)) {
            return $query;
        }

        if(is_cl_permission_none()) {
            $query->where('estimates.user_id', -1);
        } elseif (is_cl_permission_owner()) {
            $query->where('estimates.user_id', $user->id);
        }

        return $query;
    }

    function scopeApiFilter($estimateQuery, Request $request){
        if($request->has('filters')) {
            if($request->has('filters.date_from'))
                $estimateQuery->where('date_created', '>=', strtotime($request->input('filters.date_from')));
            if($request->has('filters.date_to'))
                $estimateQuery->where('date_created', '<=', strtotime($request->input('filters.date_to')));
            if ($request->has('filters.status_id'))
                $estimateQuery->where('status', $request->input('filters.status_id'));
            if ($request->has('filters.estimator_id'))
                $estimateQuery->whereHas('lead', function ($query) use ($request) {
                    $query->where('lead_estimator', $request->input('filters.estimator_id'));
                });
            if ($request->has('filters.estimate_description'))
                $estimateQuery->where('estimate_crew_notes', $request->input('filters.estimate_description'));
            if ($request->has('filters.service_id') && is_array($request->input('filters.service_id')))
                $estimateQuery->whereHas('estimates_service', function ($query) use ($request) {
                    $query->whereIn('service_id', $request->input('filters.service_id'));
                });

            if ($request->has('filters.total_from') || $request->has('filters.total_to')){
                $estimateQuery->withCount([
                    'estimates_service as service_total' => function ($query){
                        $query->select(DB::raw("SUM(service_price)"))->where('service_status', '!=', '1');
                    }
                ]);
                if($request->has('filters.total_from'))
                    $estimateQuery->having('service_total', '>=', $request->input('filters.total_from'));
                if($request->has('filters.total_to'))
                    $estimateQuery->having('service_total', '<=', $request->input('filters.total_to'));
            }
        }
    }

    public function scopeFormattedCreateDate($query){
        $query->addSelect(DB::raw('FROM_UNIXTIME(estimates.date_created) as formatted_date_created'));
    }

    public function scopeOrderDesc($query) {
        return $query->orderBy('estimate_id', 'DESC');
    }

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

    /**
     * @param $query
     * @return mixed
     */
    function scopePortalFields($query)
    {
        $query_string = implode(',', $this->portal_fields);
        return $query->select(DB::raw($query_string));
    }

//    function getSumWithoutTaxAttribute(int $clientContactId = 0){
//        return self::with($this->lead())->sum('sum_without_tax');
//    }
}
