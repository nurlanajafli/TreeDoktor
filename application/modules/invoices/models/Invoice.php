<?php
namespace application\modules\invoices\models;

use application\core\Database\Casts\AppDate;
use application\core\Database\Casts\AppDateTime;
use application\core\Database\EloquentModel;
use application\modules\clients\models\Client;
use application\modules\clients\models\ClientsContact;
use application\modules\clients\models\StatusLog;
use application\modules\clients\models\Tag;
use application\modules\emails\models\Email;
use application\modules\estimates\models\Estimate;
use application\modules\estimates\models\EstimatesService;
use application\modules\estimates\models\Service;
use application\modules\leads\models\Lead;
use application\modules\references\models\Reference;
use application\modules\user\models\User;
use application\modules\payments\models\ClientPayment;

use application\modules\dashboard\models\traits\FullTextSearch;
use application\modules\workorders\models\Workorder;
use DB;
use Illuminate\Database\Eloquent\Builder;
use phpDocumentor\Reflection\Types\Boolean;
use function GuzzleHttp\Psr7\str;

class Invoice extends EloquentModel
{
    use FullTextSearch;

    const ATTR_ID = 'id';
    const ATTR_INVOICE_NO = 'invoice_no';
    const ATTR_WORKORDER_ID = 'workorder_id';
    const ATTR_ESTIMATE_ID = 'estimate_id';
    const ATTR_CLIENT_ID = 'client_id';
    const ATTR_IN_STATUS = 'in_status';
    const ATTR_PAYMENT_MODE = 'payment_mode';
    const ATTR_PAYMENT_AMOUNT = 'payment_amount';
    const ATTR_LINK_HASH = 'link_hash';
    const ATTR_LINK_HASH_VALID_TILL = 'link_hash_valid_till';
    const ATTR_INTEREST_RATE = 'interest_rate';
    const ATTR_INTEREST_STATUS = 'interest_status';
    const ATTR_DATE_CREATED = 'date_created';
    const ATTR_OVERDUE_DATE = 'overdue_date';
    const ATTR_IN_FINISHED_HOW = 'in_finished_how';
    const ATTR_IN_EXTRA_NOTE_CREW = 'in_extra_note_crew';
    const ATTR_INVOICE_LIKE = 'invoice_like';
    const ATTR_INVOICE_FEEDBACK = 'invoice_feedback';
    const ATTR_INVOICE_PDF_FILES = 'invoice_pdf_files';
    const ATTR_PAID_BY_CC = 'paid_by_cc';
    const ATTR_INVOICE_NOTES = 'invoice_notes';
    const ATTR_OVERPAID = 'overpaid';
    const ATTR_INVOICE_QB_ID = 'invoice_qb_id';
    const ATTR_QB_INVOICE_NO = 'qb_invoice_no';
    const ATTR_INVOICE_LAST_QB_TIME_LOG = 'invoice_last_qb_time_log';
    const ATTR_INVOICE_LAST_QB_SYNC_RESULT = 'invoice_last_qb_sync_result';

    /**
     * Invoice table primary key name
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Table  name
     * @var string
     */
    protected $table = 'invoices';

    /**
     * @var
     */
    public static $invoiceTermsForClientTypes;

    /**
     * The columns of the full text index
     */
    protected $searchable = [
        'invoice_no'
    ];

    /**
     * @var array
     */
    protected $appends = ['date_created_view', 'overdue_date_view'];

    /**
     * @var string[]
     */
    public $portal_fields = [
        'invoices.id',
        'invoices.invoice_no',
        'invoices.estimate_id',
        'invoices.date_created',
        'invoices.overdue_date',
        'invoices.in_status',
    ];
    /**
     * @var string[]
     */
    public $schedule_fields = [
        'invoices.id',
        'invoices.estimate_id',
        'invoices.invoice_notes'
    ];
    /**
     * @var array
     */
    protected $fillable = [
        'id',
        'invoice_no',
        'workorder_id',
        'estimate_id',
        'client_id',
        'in_status',
        'date_created',
        'overdue_date',
        'invoice_pdf_files',
        'invoice_qb_id',
        'qb_invoice_no',
        'invoice_last_qb_time_log',
        'invoice_last_qb_sync_result'
    ];

    /**
     * The supported cast types are: integer, real, float, double, decimal:<digits>, string, boolean,
     * object, array, collection, date, datetime, and timestamp.
     * When casting to decimal, you must define the number of digits (decimal:2).
     * @var array
     */
    protected $casts = [
        'workorder_id' => 'integer',
        'estimate_id' => 'integer',
        'client_id' => 'integer',
        'in_status' => 'integer'
    ];

    /**
     * API application fields for get
     * @var array
     */
    const API_GET_FIELDS = [
        'invoices.id',
        'invoices.invoice_no',
        'invoices.date_created',
        'invoices.in_status',
    ];

    function estimate(){
        return $this->hasOne(Estimate::class, 'estimate_id', 'estimate_id')->with(['client', 'user']);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    function status()
    {
        return $this->hasOne(InvoiceStatus::class, 'invoice_status_id', 'in_status');
    }

    public function payments(){
        return $this->hasMany(ClientPayment::class, 'estimate_id', 'estimate_id');
    }

    public function client(){
        return $this->belongsTo(Client::class, 'client_id', 'client_id');
    }

    function emails() {
        return $this->morphToMany(Email::class, 'emailable');
    }

    /**
     * @param $query_string
     * @return mixed
     */
    function globalSearchQuery($query_string){
        $columns = implode(',',$this->searchable);

        $search = $this->fullTextWildcards($query_string);

        return self::search($query_string)->select([
            "lead_address as item_address", "client_name as item_name", "cc_phone as item_phone", "cc_name as item_cc_name", "cc_email as item_email", DB::raw("CONCAT(NULL) as item_status"), DB::raw("CONCAT(invoices.date_created, ' ','00:00:00') as item_date_created"), DB::raw("CONCAT('invoices') as item_module_name"), DB::raw("CONCAT('profile') as item_action_name"), "invoices.id as item_id", "invoice_no as item_no", "invoice_no as item_title", DB::raw("CONCAT('5') as item_position"),
            DB::raw("ROUND(CAST((SUM(IF(estimates_services.service_status <> 1, estimates_services.service_price, 0)) /
                   IF(COUNT(DISTINCT (client_payments.payment_id)), COUNT(DISTINCT (client_payments.payment_id)), 1) /
                   IF(COUNT(DISTINCT (invoice_interest.id)), COUNT(DISTINCT (invoice_interest.id)), 1) - IF(estimate_hst_disabled <> 2, IFNULL(
                           IF(discounts.discount_percents = 0, discounts.discount_amount,
                              (discounts.discount_amount * SUM(
                                      IF(estimates_services.service_status <> 1, estimates_services.service_price, 0)) /
                   IF(COUNT(DISTINCT (client_payments.payment_id)), COUNT(DISTINCT (client_payments.payment_id)), 1) /
                   IF(COUNT(DISTINCT (invoice_interest.id)), COUNT(DISTINCT (invoice_interest.id)), 1) /
                               100)), 0), 0)) /
                  IF(estimate_hst_disabled = 2, " . config_item('tax_rate') . ", 1) AS DECIMAL(10, 3)), 2) as total"),
            DB::raw("MATCH ({$columns}) AGAINST ('".$search."' IN BOOLEAN MODE) AS relevance_score")
        ])
        ->join('estimates', ['invoices.estimate_id' => 'estimates.estimate_id'])
        ->leftJoin('invoice_interest', ['invoices.id' => 'invoice_interest.invoice_id'])
        ->leftJoin('invoice_statuses', ['invoices.in_status' => 'invoice_statuses.invoice_status_id'])
        ->join('leads', ['estimates.lead_id' => 'leads.lead_id'])
        ->join('clients', ['estimates.client_id' => 'clients.client_id'])
        ->leftJoin('clients_contacts', ['cc_client_id' => 'clients.client_id', 'cc_print' => DB::raw('1')])
        ->leftJoin('estimates_services', ['estimates_services.estimate_id' => 'invoices.estimate_id',  'service_status' => DB::raw('2')])
        ->leftJoin('discounts', ['discounts.estimate_id'=>'invoices.estimate_id'])
        ->leftJoin('client_payments', ['client_payments.estimate_id'=>'invoices.estimate_id'])
        ->groupBy('invoices.estimate_id')
        ->permissions();
    }

    /**
     * @param array $filters
     * @param array $select
     * @return Invoice
     */
    public static function globalSearchByFilters(array $filters, array $select = [])
    {
        $select = empty($select) ? [Invoice::tableName() . '.' . Invoice::ATTR_ID] : $select;

        $query = Invoice::select('invoices.id')
            ->join(Estimate::tableName(),
                Invoice::tableName() . '.' . Workorder::ATTR_ESTIMATE_ID, '=', Estimate::tableName() . '.' . Estimate::ATTR_ESTIMATE_ID
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
                $query->orWhere(Invoice::tableName() . '.' . Invoice::ATTR_INVOICE_NO, 'LIKE', DB::raw('"%' . $search . '%"'));
            });
        }

        if (isset($filters['estimator_id'])) {
            $query->where([Estimate::tableName() . '.' . Estimate::ATTR_USER_ID => $filters['estimator_id']]);
        } elseif (isset($filters['estimators_id'])){
            $query->whereIn(Estimate::tableName() . '.' . Estimate::ATTR_USER_ID, $filters['estimators_id']);
        }

        if (isset($filters['tag_names']) && !empty($filters['tag_names']) && !is_null($filters['tag_names'])) {
            $query->leftJoin('client_tags', 'client_tags.client_id', '=', Client::tableName() . '.' . Client::ATTR_CLIENT_ID)
                ->leftJoin(Tag::tableName(), Tag::tableName() . '.' . Tag::ATTR_TAG_ID, '=', 'client_tags.tag_id');

            $query->whereIn(Tag::tableName() . '.' . Tag::ATTR_NAME, $filters['tag_names']);
        }

        return $query;
    }
    /**
     * @param int $clientType
     * @return int|mixed
     */
    public static function getInvoiceTerm($clientType = 1)
    {
        $definedTerms = [
            1 => defined('INVOICE_TERM') ? INVOICE_TERM : 30,
            2 => defined('INVOICE_CORP_TERM') ? INVOICE_CORP_TERM : 30,
            3 => defined('INVOICE_MUNI_TERM') ? INVOICE_MUNI_TERM : 30
        ];

        return $definedTerms[$clientType] ?? INVOICE_TERM;
    }


    /**
     * @param $page
     * @param array $filters
     * @param int $limit
     * @param bool $withoutPermission
     * @param bool $pagination
     * @return array|\Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public static function getInvoices($page, array $filters, int $limit = 20, bool $withoutPermission = false, $addFields = [])
    {
        $CI = &get_instance();
        $CI->load->model('mdl_estimates_orm');
        $matchedInvoiceIds = [];
        $filterWhere = [];
        $filterWhereIn = [];
        $filterWhereBetween = [];

        if ((isset($filters['search']) && !empty($filters['search']))
            ||
            (isset($filters['estimator_id']) && !empty($filters['estimator_id']))
            ||
            (isset($filters['estimators_id']) && !empty($filters['estimators_id']))) {

            $matchedInvoiceIds = static::globalSearchByFilters($filters)
                ->get()->keyBy('id')->toArray();

            if (empty($matchedInvoiceIds)) {
                return [];
            }
        }

        $query = Invoice::select(array_merge_recursive(
            static::API_GET_FIELDS, ['totals.total_due'], Estimate::API_GET_FIELDS,
            Client::API_GET_FIELDS, Lead::API_GET_FIELDS, User::API_GET_FIELDS, ClientsContact::API_GET_FIELDS, Reference::API_GET_FIELDS
        ))
            ->leftJoin(Estimate::tableName(),
                Estimate::tableName() . '.' . Estimate::ATTR_ESTIMATE_ID, '=', Invoice::tableName() . '.' . Invoice::ATTR_ESTIMATE_ID)
            ->leftJoin(Client::tableName(),
                Estimate::tableName() . '.' . Estimate::ATTR_CLIENT_ID, '=', Client::tableName() . '.' . Client::ATTR_CLIENT_ID)
            ->leftJoin(ClientsContact::tableName(),
                ClientsContact::tableName() . '.' . ClientsContact::ATTR_CC_CLIENT_ID, '=', Client::tableName() . '.' . Client::ATTR_CLIENT_ID)
            ->leftJoin(Lead::tableName(),
                Estimate::tableName() . '.' . Estimate::ATTR_LEAD_ID, '=', Lead::tableName() . '.' . Lead::ATTR_LEAD_ID)
            ->leftJoin(User::tableName(),
                User::tableName() . '.' . User::ATTR_ID, '=', Estimate::tableName() . '.' . Estimate::ATTR_USER_ID)
            ->leftJoin(Reference::tableName(),
                Reference::tableName() . '.' . Reference::ATTR_ID, '=', Lead::tableName() . '.' . Lead::ATTR_LEAD_REFFERED_BY);

        /* Start Filters */
        if(isset($filters['classes']) && !empty($filters['classes']) && is_array($filters['classes'])){
            $query->leftJoin(EstimatesService::tableName(),
                EstimatesService::tableName() . '.' . EstimatesService::ATTR_ESTIMATE_ID, '=', Invoice::tableName() . '.' . Invoice::ATTR_ESTIMATE_ID)
            ->distinct();
            array_push($filterWhereIn,[EstimatesService::tableName() . '.' . EstimatesService::ATTR_ESTIMATE_SERVICE_CLASS_ID, $filters['classes']]);
        }
        if (isset($filters['date_from']) && isset($filters['date_to'])) {
            array_push($filterWhereBetween, [Invoice::tableName() . '.' . Invoice::ATTR_DATE_CREATED, [$filters['date_from'], $filters['date_to']]]);
        } elseif (isset($filters['date_from']) && !isset($filters['date_to'])) {
            array_push($filterWhere, [Invoice::tableName() . '.' . Invoice::ATTR_DATE_CREATED, '>=', $filters['date_from']]);
        } elseif (!isset($filters['date_from']) && isset($filters['date_to'])) {
            array_push($filterWhere, [Invoice::tableName() . '.' . Invoice::ATTR_DATE_CREATED, '<=', $filters['date_to']]);
        }
        if (isset($filters['status_id']) && !empty($filters['status_id']) && !is_null($filters['status_id'])) {
            array_push($filterWhere, [Invoice::tableName() . '.' . Invoice::ATTR_IN_STATUS, $filters['status_id']]);
            if(isset($filters['overpaid'])) {
                array_push($filterWhere, [Invoice::tableName() . '.' . Invoice::ATTR_OVERPAID, 1]);
            }
        } else {
            array_push($filterWhere, [Invoice::tableName() . '.' . Invoice::ATTR_IN_STATUS , '<>', NULL]);
        }
        if(isset($filters['reference_id']) && !empty($filters['reference_id'])){
            array_push($filterWhere, [Lead::tableName() . '.' . Lead::ATTR_LEAD_REFFERED_BY, $filters['reference_id']]);
        }
        elseif(isset($filters['references_id']) && !empty($filters['references_id'])){
            array_push($filterWhereIn, [Lead::tableName() . '.' . Lead::ATTR_LEAD_REFFERED_BY, $filters['references_id']]);
        }
        if (!empty($matchedInvoiceIds)) {
            array_push($filterWhereIn,[Invoice::tableName() . '.' . Invoice::ATTR_ID, array_keys($matchedInvoiceIds)]);
        }
        if(isset($addFields['invoice_paid_date'])){
            $subquery = ClientPayment::select(DB::raw('MAX(' . ClientPayment::ATTR_PAYMENT_DATE . ') as payment_date, ' . ClientPayment::tableName() . '.' .ClientPayment::ATTR_ESTIMATE_ID))
                ->leftJoin(Invoice::tableName(), Invoice::tableName() . '.' . Invoice::ATTR_ESTIMATE_ID, '=',  ClientPayment::tableName() . '.' . ClientPayment::ATTR_ESTIMATE_ID)
                ->leftJoin(Estimate::tableName(),Estimate::tableName() . '.' . Estimate::ATTR_ESTIMATE_ID, '=', Invoice::tableName() . '.' . Invoice::ATTR_ESTIMATE_ID)
                ->leftJoin(Lead::tableName(),Estimate::tableName() . '.' . Estimate::ATTR_LEAD_ID, '=', Lead::tableName() . '.' . Lead::ATTR_LEAD_ID)
                ->leftJoin(InvoiceStatus::tableName(), InvoiceStatus::tableName() . '.' . InvoiceStatus::ATTR_ID, '=', Invoice::tableName() . '.' . Invoice::ATTR_IN_STATUS)
                ->leftJoin(EstimatesService::tableName(), EstimatesService::tableName() . '.' . EstimatesService::ATTR_ESTIMATE_ID, '=', Invoice::tableName() . '.' . Invoice::ATTR_ESTIMATE_ID)
                ->where(InvoiceStatus::tableName() . '.' . InvoiceStatus::ATTR_COMPLETED, '=', 1)
                ->groupBy(Invoice::ATTR_ESTIMATE_ID);
            $subquery = self::setInvoiceFilters($subquery, $filterWhere, $filterWhereIn, $filterWhereBetween);
            $query->leftJoinSub($subquery, 'client_payment', function($join){
                $join->on(  'client_payment.' . ClientPayment::ATTR_ESTIMATE_ID, '=', Invoice::tableName() . '.' . Invoice::ATTR_ESTIMATE_ID);
            })->addSelect('client_payment.payment_date as invoice_paid_date');
        }
        if(isset($addFields['invoice_sent_date'])){
            $subquery = StatusLog::select(DB::raw('MAX(' . StatusLog::tableName() . '.' . StatusLog::ATTR_STATUS_DATE . ') as status_date, ' . StatusLog::ATTR_STATUS_ITEM_ID . ', ' . StatusLog::ATTR_STATUS_VALUE . ', ' . StatusLog::ATTR_STATUS_TYPE))
                ->leftJoin(Invoice::tableName(), Invoice::tableName() . '.' . Invoice::ATTR_ID, '=',  StatusLog::tableName() . '.' . StatusLog::ATTR_STATUS_ITEM_ID)
                ->leftJoin(Estimate::tableName(),Estimate::tableName() . '.' . Estimate::ATTR_ESTIMATE_ID, '=', Invoice::tableName() . '.' . Invoice::ATTR_ESTIMATE_ID)
                ->leftJoin(Lead::tableName(),Estimate::tableName() . '.' . Estimate::ATTR_LEAD_ID, '=', Lead::tableName() . '.' . Lead::ATTR_LEAD_ID)
                ->leftJoin(InvoiceStatus::tableName(), InvoiceStatus::tableName() . '.' . InvoiceStatus::ATTR_ID, '=', StatusLog::tableName() . '.' . StatusLog::ATTR_STATUS_VALUE)
                ->leftJoin(EstimatesService::tableName(), EstimatesService::tableName() . '.' . EstimatesService::ATTR_ESTIMATE_ID, '=', Invoice::tableName() . '.' . Invoice::ATTR_ESTIMATE_ID)
                ->where(InvoiceStatus::tableName() . '.' .InvoiceStatus::ATTR_IS_SENT, '=', 1)
                ->where(StatusLog::ATTR_STATUS_TYPE, '=', 'invoice')
                ->groupBy( StatusLog::ATTR_STATUS_ITEM_ID);
            $subquery = self::setInvoiceFilters($subquery, $filterWhere, $filterWhereIn, $filterWhereBetween);
            $query->leftJoinSub($subquery, 'status_log', function($join){
                $join->on(  'status_log.' . StatusLog::ATTR_STATUS_ITEM_ID, '=', Invoice::tableName() . '.' . Invoice::ATTR_ID);
            })->addSelect('status_log.status_date as invoice_sent_date');
        }
        if(isset($addFields['total_by_classes'])){
            $subquery = EstimatesService::select(DB::raw('SUM(' . EstimatesService::ATTR_SERVICE_PRICE . ') as all_classes, ' . EstimatesService::tableName() . '.' . EstimatesService::ATTR_ESTIMATE_ID))
                ->leftJoin(Invoice::tableName(), Invoice::tableName() . '.' . Invoice::ATTR_ESTIMATE_ID, '=', EstimatesService::tableName() . '.' . EstimatesService::ATTR_ESTIMATE_ID)
                ->leftJoin(Estimate::tableName(),Estimate::tableName() . '.' . Estimate::ATTR_ESTIMATE_ID, '=', Invoice::tableName() . '.' . Invoice::ATTR_ESTIMATE_ID)
                ->leftJoin(Lead::tableName(),Estimate::tableName() . '.' . Estimate::ATTR_LEAD_ID, '=', Lead::tableName() . '.' . Lead::ATTR_LEAD_ID)
                ->leftJoin(Service::tableName(), Service::tableName() . '.' . Service::ATTR_SERVICE_ID, '=', EstimatesService::tableName() . '.' . EstimatesService::ATTR_SERVICE_ID)
                ->where(Service::tableName() . '.' . Service::ATTR_IS_BUNDLE, '=', 0)
                ->groupBy(EstimatesService::ATTR_ESTIMATE_ID);

//            $subquery = self::setInvoiceFilters($subquery, $filterWhere, $filterWhereIn, $filterWhereBetween);

            if(isset($addFields['total_by_classes']['all_classes'])){
                $all_classes = clone $subquery;
                $all_classes->where(EstimatesService::tableName() . '.' . EstimatesService::ATTR_ESTIMATE_SERVICE_CLASS_ID, '>', 0);
                $query->leftJoinSub($all_classes,'all_classes',function($join){
                    $join->on(  'all_classes.' . EstimatesService::ATTR_ESTIMATE_ID, '=', Invoice::tableName() . '.' . Invoice::ATTR_ESTIMATE_ID);
                })->addSelect('all_classes.all_classes');
            }

            if(isset($addFields['total_by_classes']['no_class'])){
                $no_class = clone $subquery;
                $no_class->where(function ($query) {
                    $query->where(EstimatesService::tableName() . '.' . EstimatesService::ATTR_ESTIMATE_SERVICE_CLASS_ID, '=', 0)
                        ->orWhere(EstimatesService::tableName() . '.' . EstimatesService::ATTR_ESTIMATE_SERVICE_CLASS_ID, '=', null);
                });
                $query->leftJoinSub($no_class,'no_class',function($join){
                    $join->on(  'no_class.' . EstimatesService::ATTR_ESTIMATE_ID, '=', Invoice::tableName() . '.' . Invoice::ATTR_ESTIMATE_ID);
                })->addSelect('no_class.all_classes as no_class');
            }
            if(isset($filters['classes']) && !empty($filters['classes']) && is_array($filters['classes'])){
                foreach ($filters['classes'] as $class){
                    $className = 'total_class_' . $class;
                    $test = clone $subquery;
                    $test->where(EstimatesService::tableName() . '.' . EstimatesService::ATTR_ESTIMATE_SERVICE_CLASS_ID, '=', $class);
                    $query->leftJoinSub($test, $className, function($join) use ($className){
                        $join->on(  $className . '.' . EstimatesService::ATTR_ESTIMATE_ID, '=', Invoice::tableName() . '.' . Invoice::ATTR_ESTIMATE_ID);
                    })->addSelect($className . '.all_classes as '. $className);
                }
            }
        }
        $query = self::setInvoiceFilters($query, $filterWhere, $filterWhereIn, $filterWhereBetween);

        /* End Filters */
        $query->where([ClientsContact::tableName() . '.' . ClientsContact::ATTR_CC_PRINT => 1])
            ->orderBy(Invoice::tableName() . '.' . Invoice::ATTR_DATE_CREATED, 'DESC');

        if($withoutPermission === false) {
            $user = request()->user();
            if (isset($user) && !is_null($user)) {
                $where[Estimate::tableName() . '.' . Estimate::ATTR_USER_ID] = $user->id;
                if (is_cl_permission_none()) {
                    $where[Estimate::tableName() . '.' . Estimate::ATTR_USER_ID] = -1;
                    $query->where('estimates.user_id', '=', -1);
                } elseif (is_cl_permission_owner()) {
                    $query->where('estimates.user_id', '=', $user->id);
                } else {
                    $where[Estimate::tableName() . '.' . Estimate::ATTR_USER_ID] = $filters['estimator_id'] ?? $user->id;
                    $query->where('estimates.user_id', '=', $filters['estimator_id'] ?? $user->id);
                }
            }
        }

        $where[Invoice::tableName() . '.' . Invoice::ATTR_IN_STATUS . '  IS NOT NULL'] = null;
        $totalsSubQuery = $CI->mdl_estimates_orm->calcQuery($where);
        $query->leftJoin(DB::raw('(' . $totalsSubQuery. ') AS totals'), 'totals.estimate_id', '=', 'estimates.estimate_id');
        if(isset($addFields['discount_total']))
            $query->addSelect('totals.discount_total');
        if($limit == 0)
            $limit = $query->count();
        return $query->paginate($limit, [], 'page', $page);
    }

    /**
     * @param $id
     * @return array
     */
    public static function getInvoice($id)
    {
        $status = null;
        $query = Invoice::select(static::apiFields())
            ->with([
                'estimate' => function ($query) use ($status) {
                    $query->select(Estimate::BASE_FIELDS);
                    $query->addSelect(Estimate::NOTES_FIELDS);
                    $query->withTotals($status);
                    $query->with([
                        'estimate_status',
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
            ])->where([Invoice::tableName() . '.' . Invoice::ATTR_ID => $id]);

        $result = $query->first();

        $result->estimate->setAppends(['files']);

        if (!is_null($result)) {
            return $result->toArray();
        }
        return [];
    }

    /**
     * @return array
     */
    public static function apiFields()
    {
        return [
            'invoices.id',
            'invoices.estimate_id',
            'invoices.invoice_no',
            'invoices.invoice_like',
            'invoices.invoice_notes',
            'invoices.workorder_id',
            'invoices.interest_status',
            'invoices.interest_rate',
            'invoices.date_created',
            'invoices.in_status',
            'invoices.overpaid',
            'invoices.invoice_qb_id',
            'invoices.invoice_last_qb_time_log',
            'invoices.invoice_last_qb_sync_result',
            DB::raw('DATEDIFF(NOW(),invoices.date_created) as days'),
            'invoices.overdue_date',
        ];
    }

    /**
     * @return bool|\Illuminate\Support\Collection
     */
    public function update_all_invoice_interes()
    {
        $CI = &get_instance();
        $CI->load->model('mdl_invoices');
        $CI->load->model('mdl_estimates_orm');
        $CI->load->model('mdl_clients');

        if (!$this->getAttribute(static::ATTR_ESTIMATE_ID)) {
            return FALSE;
        }
        $estimate_id = $this->getAttribute(static::ATTR_ESTIMATE_ID);

        $invoice = $CI->mdl_invoices->getEstimatedData($estimate_id);
        $estimate = (new Estimate())->totalEstimateBalance($estimate_id)->first();
        $payments = $CI->mdl_clients->get_payments(['estimates.estimate_id' => $estimate_id]);

        if (!is_null($invoice) && isset($invoice->id)) {
            $client = Client::find($invoice->client_id);
            $term = Invoice::getInvoiceTerm($client->client_type);
            $interes = $CI->mdl_invoices->getInterestData($invoice->id);
            if ($interes && !empty($interes)) {
                $newBal = ($estimate->sum_taxable + $estimate->sum_non_taxable) - $estimate->discount_total;
                $intCost = 0;
                foreach ($interes as $k => $v) {
                    if (isset($payments) && !empty($payments)) {
                        foreach ($payments as $jk => $pay) {
                            if ($pay['payment_date'] < (strtotime($v->overdue_date) - $term * 86400)) {
                                $newBal = $newBal - $pay['payment_amount'];
                                unset($payments[$jk]);
                            }
                        }
                    }
                    $interest = abs($v->rate / 100);
                    $intCost = round($newBal * $interest, 2);
                    $newBal += $intCost;
                    InvoiceInterest::where(['id' => $v->id])->update(['interes_cost' => $intCost]);
                }
                $interes = $CI->mdl_invoices->getInterestData($invoice->id);
                return $interes;
            }
        }

        return TRUE;
    }

    /**
     * @param $estimate_id
     * @return \Illuminate\Support\Collection
     */
    public function getEstimatedData($estimate_id)
    {
        //todo::by join
        $query = Invoice::select([
            DB::raw('invoices.id'),
            DB::raw('invoices.invoice_no'),
            DB::raw('invoices.interest_status'),
            DB::raw('invoices.in_finished_how'),
            DB::raw('invoices.in_extra_note_crew'),
            DB::raw('invoices.invoice_pdf_files'),
            DB::raw('invoices.interest_rate'),
            DB::raw('invoices.date_created'),
            DB::raw('invoices.in_status'),
            DB::raw('DATEDIFF(NOW(),invoices.date_created) as days'),
            DB::raw('invoices.overdue_date'),
        ])->with(['estimate' => function($query) use ($estimate_id) {

        }, 'status'])->where(Invoice::tableName() . '.' . Invoice::ATTR_ESTIMATE_ID, '=', $estimate_id);
        return $query->get();
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
            $query->join('estimates', 'estimates.estimate_id', '=', 'invoices.estimate_id');
        }

        if(is_cl_permission_none()) {
            $query->where('estimates.user_id', -1);
        } elseif (is_cl_permission_owner()) {
            $query->where('estimates.user_id', $user->id);
        }

        return $query;
    }

    /**
     * @param $query
     * @return mixed
     */
    function scopeOwn($query) {
        $user = request()->user();

        if(!isset($user) || is_null($user)) {
            return $query;
        }
        $query->where('estimates.user_id', $user->id);

        return $query;
    }

    function scopeScheduleFields($query)
    {
        $query_string = implode(',', $this->schedule_fields);
        return $query->select(DB::raw($query_string));
    }

    /**
     * @return int
     */
    public static function getCount(){
        $invoices = Invoice::where( Invoice::ATTR_IN_STATUS, '!=', 'NULL')->get();
        return $invoices->count();
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

    /**
     * @return string|null
     */
    function getOverdueDateViewAttribute()
    {
        if (!isset($this->attributes['overdue_date'])) {
            return false;
        }
        return getDateTimeWithTimestamp(strtotime($this->attributes['overdue_date']));
    }

    private static function setInvoiceFilters(Builder $query, $where = [], $whereIn = [], $whereBetween = []){
        if(!empty($where)){
            $query->where($where);
        }
        if(!empty($whereBetween)){
            foreach ($whereBetween as $filter){
                $query->whereBetween($filter[0], $filter[1]);
            }

        }
        if(!empty($whereIn)){
            foreach ($whereIn as $filter){
                $query->whereIn($filter[0], $filter[1]);
            }
        }
        return $query;
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
}
