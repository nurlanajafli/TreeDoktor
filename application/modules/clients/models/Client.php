<?php

namespace application\modules\clients\models;

use application\core\Database\EloquentModel;
use application\modules\clients\models\ClientNote;
use application\modules\clients\models\ClientsContact;
use application\modules\clients\models\Tag;
use application\modules\dashboard\models\traits\FullTextSearch;
use application\modules\estimates\models\Estimate;
use application\modules\leads\models\Lead;
use application\modules\estimates\models\EstimatesService;
use application\modules\invoices\models\Invoice;
use application\modules\payments\models\ClientPayment;
use application\modules\workorders\models\Workorder;
use application\modules\emails\models\Email;
use DB;
use Illuminate\Http\Request;
use function Clue\StreamFilter\fun;

class Client extends EloquentModel
{
    use FullTextSearch;

    const PERM_NONE = '0';
    const PERM_ALL = '1';
    const PERM_OWN = '2';

    const API_FILTER_INCLUDE_ESTIMATES = [
        'total_from',
        'total_to',
        'is_confirmed',
        'estimator_id'
    ];

    const ATTR_CLIENT_ID = 'client_id';
    const ATTR_CLIENT_NAME = 'client_name';
    const ATTR_CLIENT_CONTACT= 'client_contact';
    const ATTR_CLIENT_ADDRESS = 'client_address';
    const ATTR_CLIENT_CITY = 'client_city';
    const ATTR_CLIENT_COUNTRY = 'client_country';
    const ATTR_CLIENT_ZIP = 'client_zip';

    /**
     * @var array
     */
    const PRIMARY_CONTACT_FIELDS = [
        "clients_contacts.cc_id",
        "clients_contacts.cc_client_id",
        "clients_contacts.cc_title",
        "clients_contacts.cc_name",
        "clients_contacts.cc_phone",
        "clients_contacts.cc_phone_clean",
        "clients_contacts.cc_email",
        "clients_contacts.cc_email_check",
        "clients_contacts.cc_email_manual_approve",
        "clients_contacts.cc_print"
    ];

    /**
     * Appends columns
     */
    protected $appends = [];

    /**
     * The columns of the full text index
     */
    protected $searchable = [
        'clients.client_name',
        'clients.client_address',
        'clients.client_city',
        'clients.client_country',
        'clients.client_state',
        'clients.client_zip',
    ];

    protected $joinable = [
        'clients_contacts.cc_name',
        'clients_contacts.cc_email',
        'clients_contacts.cc_phone'
    ];

    public $base_fields = [
        'clients.client_id',
        'clients.client_name',
        'clients.client_brand_id',
        'clients.client_address',
        'clients.client_city',
        'clients.client_state',
        'clients.client_zip',
        'clients.client_country',
        'clients.client_lng',
        'clients.client_lat',
        'clients.client_email_check'
    ];

    /**
     * @var string[]
     */
    public $portal_fields = [
        'clients.client_address',
        'clients.client_city',
        'clients.client_country',
        'clients.client_email',
        'clients.client_id',
        'clients.client_lat',
        'clients.client_lng',
        'clients.client_name',
        'clients.client_state',
        'clients.client_zip',
    ];

    /**
     * API application fields
     * @var array
     */
    const API_FIELDS = [
        'clients.client_id',
        'clients.client_brand_id',
        'clients.client_type',
        'clients.client_name',
        'clients.client_contact',
        'clients.client_address',
        'clients.client_city',
        'clients.client_country',
        'clients.client_state',
        'clients.client_zip',
        'clients.client_type',
        'clients.client_main_intersection',
        'clients.client_lat',
        'clients.client_lng',
        'clients.client_tax_name',
        'clients.client_tax_rate',
        'clients.client_tax_value'
    ];

    /**
     * API application fields for get
     * @var array
     */
    const API_GET_FIELDS = [
        'clients.client_id',
        'clients.client_name',
    ];


    const API_GET_BASE_FIELDS = [
        "clients.client_type",
        "clients.client_id",
        "clients.client_brand_id",
        "clients.client_name",
        "clients.client_lat",
        "clients.client_lng",
        "clients.client_address",
        "clients.client_city",
        "clients.client_zip",
        "clients.client_state",
        "clients.client_country",
        "clients_contacts.cc_name",
        "clients_contacts.cc_phone",
        "clients_contacts.cc_email"
    ];

    /**
     * Client table primary key name
     * @var string
     */
    protected $primaryKey = 'client_id';
    /**
     * Table  name
     * @var string
     */
    protected $table = 'clients';
    /**
     * Client lists page, search input filter columns
     */
    const SEARCH_INPUT_FILTER_COLUMNS = [
        'client_name',
        'clients_contacts.cc_name',
        'client_address',
        'client_address2',
        'clients_contacts.cc_phone',
        'clients_contacts.cc_email'
    ];
    /**
     * Client lists page, search filter estimator fields
     */
    const SEARCH_FILTER_ESTIMATOR_FIELDS = [
        'search_estimate_from',
        'search_estimate_to',
        'search_estimate_price_from',
        'search_estimate_price_to',
        'search_estimator',
        'estimate_confirm_status'
    ];

    protected $fillable = [
        'client_brand_id',
        'client_date_created',
        'client_maker',
        'client_date_modified',
        'client_name',
        'client_type',
        'client_address',
        'client_city',
        'client_state',
        'client_zip',
        'client_country',
        'client_lng',
        'client_lat',
        'client_source',
        'client_referred_by',
        'client_qb_id',
        'client_brand_id',
        'client_tax_name',
        'client_tax_rate',
        'client_tax_value'
    ];

    /**
     * @return Client
     */
    public static function instance()
    {
        return new Client();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'client_tags', 'client_id', 'tag_id');
    }

    /**
     * @return \application\models\Relations\HasManySyncable
     */
    public function workorders()
    {
        return $this->hasMany(Workorder::class, 'client_id', 'client_id');
    }
    /**
     * @return \application\models\Relations\HasManySyncable
     */
    public function estimates()
    {
        return $this->hasMany(Estimate::class, 'client_id', 'client_id');
    }
    /**
     * @return \application\models\Relations\HasManySyncable
     */
    public function contacts()
    {
        return $this->hasMany(ClientsContact::class, 'cc_client_id', 'client_id');
    }

    /**
     * @return \application\models\Relations\HasManySyncable
     */
    public function papers()
    {
        return $this->hasMany(ClientPaper::class, 'cp_client_id', 'client_id');
    }

    /**
     * @return \application\models\Relations\HasManySyncable
     */
    public function leads()
    {
        return $this->hasMany(Lead::class, 'client_id', 'client_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    function primary_contact() {
        return $this->hasOne(ClientsContact::class, 'cc_client_id')
            ->select(static::PRIMARY_CONTACT_FIELDS)
            ->where('cc_print', '=', 1);
    }

    function notes(){
        return $this->hasMany(ClientNote::class, 'client_id', 'client_id');
    }

    function emails() {
        return $this->morphToMany(Email::class, 'emailable');
    }

    public function invoices(){
        return $this->hasMany(Invoice::class, 'client_id', 'client_id');
    }

    /**
     * @param $query_string
     * @return mixed
     */
    function globalSearchQuery($query_string){
        $columns = implode(',',$this->searchable);
        $search = $this->fullTextWildcards($query_string);
        return self::search($query_string)
        ->select([
            "client_address as item_address",
            "client_name as item_name",
            "cc_phone as item_phone",
            "cc_name as item_cc_name",
            "cc_email as item_email",
            DB::raw("CONCAT(NULL) as item_status"),
            DB::raw("CONCAT(client_date_created, ' ', '00:00:00') as item_date_created"),
            DB::raw("CONCAT('clients') as item_module_name"),
            DB::raw("CONCAT('details') as item_action_name"),
            "clients.client_id as item_id",
            "clients.client_id as item_no",
            "client_name as item_title",
            DB::raw("CONCAT('1') as item_position"),
            DB::raw("CONCAT(NULL) as total"),
            DB::raw("MATCH ({$columns}) AGAINST ('".$search."' IN BOOLEAN MODE) * 2 AS relevance_score") // Multiply score at 2 - Clients are more important than Client_Contacts
        ])
        ->leftJoin('clients_contacts', ['cc_client_id' => 'clients.client_id', 'cc_print' => DB::raw('1')])
        ->leftJoin('leads', ['clients.client_id' => 'leads.client_id', 'cc_print' => DB::raw('1')])
        ->leftJoin('estimates', ['clients.client_id' => 'estimates.client_id'])
        ->permissions()
        ->orderBy('clients.client_id')
        ->groupBy('clients.client_id');

    }

    /**
     * @param string $query_string
     * @return mixed
     */
    public function searchReffClient(string $query_string)
    {
        $columns            = implode(',', $this->searchable);
        $additional_columns = implode(',', $this->joinable);

        $search = $this->fullTextWildcards($query_string);

        return self::search($query_string)
            ->select([
            "clients.client_id",
            "clients.client_brand_id",
            "clients.client_address",
            "clients.client_city",
            "clients.client_country",
            "clients.client_name",
                DB::raw("(
                        (MATCH ({$columns}) AGAINST ('".$search."' IN BOOLEAN MODE) * 2) +
                        (MATCH ({$additional_columns}) AGAINST ('".$search."' IN BOOLEAN MODE))
                    ) AS relevance_score"
                ) // Multiply score at 2 - Clients are more important than Client_Contacts
            ])
            ->leftJoin('clients_contacts', ['cc_client_id' => 'clients.client_id', 'cc_print' => DB::raw('1')])
            ->leftJoin('leads', ['clients.client_id' => 'leads.client_id', 'cc_print' => DB::raw('1')])
            ->orWhereRaw('MATCH ('.$additional_columns.') AGAINST (\''.$search.'\' IN BOOLEAN MODE)')
            ->orderBy('clients.client_id')
            ->groupBy('clients.client_id');

    }

    /**
     * Count when group by exist, done to prevent wrong  results and errors
     */
    public static  function countAggregate($query)
    {
        return DB::table(DB::raw("({$query->toSql()}) as sub"))->mergeBindings($query->toBase())->count();
    }

    /**
     * @param $clientQuery
     * @param $request
     * @param bool $extra_select
     * @param bool $totalsSubQuery
     */
    public function scopeFilterClient($clientQuery, $request, $extra_select=FALSE, $totalsSubQuery=false)
    {
        /* Client Search By Name */
        if ($request->search_keyword)  {
            $ClientModel = $this;
            $ClientsContact = new ClientsContact();
            $where_in_id = DB::query()->fromSub(function($query) use ($request, $ClientModel, $ClientsContact) {
                $query->from($ClientModel->globalSearchQuery($request->search_keyword), 'c1')
                    ->union($ClientsContact->globalSearchQuery($request->search_keyword));
            }, 't1')->groupBy(['item_module_name', 'item_id'])->pluck('item_id')->toArray();

            $clientQuery->whereIn('clients.client_id', $where_in_id);
        }

        /* Client Contacts */
        $clientQuery->leftJoin('clients_contacts', function ($join) {
            $join->on('clients_contacts.cc_client_id', '=', 'clients.client_id')->where('clients_contacts.cc_print',  '1');
        });

        /* Tags */
        $searchTagsArray = [];
        if($request->has('search_tags') && is_array($request->search_tags)) {
            $searchTagsArray = $request->search_tags;
        } elseif ($request->has('search_tags') && is_string($request->search_tags)) {
            $searchTagsArray = array_filter(explode('|', $request->search_tags)) ?? [];
        }
        if (! empty($searchTagsArray) ) {
            $clientQuery->whereHas('tags', function ($query) use ($searchTagsArray) {
                $query->whereIn('client_tags.tag_id', $searchTagsArray);
            });
        }

        $clientQuery->leftJoin('estimates', 'clients.client_id', '=', 'estimates.client_id');
        $clientQuery->leftJoin('leads', 'estimates.lead_id', '=', 'leads.lead_id');
        $clientQuery->leftJoin('estimate_statuses', 'estimates.status', '=', 'estimate_statuses.est_status_id');
        $clientQuery->leftJoin('users', 'estimates.user_id', '=', 'users.id');

        if ($request->search_brand_id) {
            $clientQuery->where('clients.client_brand_id', $request->search_brand_id);
        }

        if ($request->date_from) {
            $clientQuery->where('clients.client_date_created', '>=', $request->date_from);
        }

        if ($request->date_to) {
            $clientQuery->where('clients.client_date_created', '<=', $request->date_to);
        }

        if ($request->search_estimate_from) {
            $clientQuery->where('estimates.date_created', '>=', strtotime($request->search_estimate_from));
        }

        if ($request->search_estimate_to) {
            $clientQuery->where('estimates.date_created', '<=', strtotime($request->search_estimate_to));
        }

        if ($request->search_estimator) {
            $clientQuery->where('leads.lead_estimator',  $request->search_estimator);
        }

        if ($request->estimate_confirm_status) {
            $clientQuery->where('estimate_statuses.est_status_confirmed', 1);
        }

        if ($request->client_maker == 2) {
            $clientQuery->where('client_maker', $this->session->userdata['user_id']);
        } elseif ($request->has('client_maker') && $request->client_maker == 0) {
            $clientQuery->where('client_maker', -1);
        }

        $totalsExtraSelect = '';
        if($totalsSubQuery){
            $totalsExtraSelect = 'sum(totals.sum_without_tax) as total_estimate_price,';
            $totalsExtraSelect .= 'sum(if(estimate_statuses.est_status_confirmed,totals.sum_without_tax, 0)) as total_confirmed_estimates_amount,';
            $totalsExtraSelect .= 'sum(if(estimate_statuses.est_status_declined,totals.sum_without_tax, 0)) as total_declined_estimates_amount,';
            $totalsExtraSelect .= 'sum(if((estimate_statuses.est_status_declined <> 1 AND estimate_statuses.est_status_confirmed <> 1),totals.sum_without_tax, 0)) as total_pending_estimates_amount,';

            $clientQuery->leftJoin(DB::raw('(' . $totalsSubQuery. ') AS totals'), ['totals.estimate_id' => 'estimates.estimate_id']);
        }

        if (($request->search_estimate_price_from || $request->search_estimate_price_to) && $totalsExtraSelect) {
            $estimateServices = EstimatesService::whereColumn('estimate_id', 'estimates.estimate_id')
                ->whereRaw(DB::raw('service_status != 1'))
                ->withCount(['service' => function ($query) {
                    $query->where('is_bundle', 0);
                }])
                ->select(DB::raw('SUM(service_price)'));
            if ($request->search_estimate_price_to) {
                $clientQuery->having('service_total', '<=', $request->search_estimate_price_to);
            }
            if ($request->search_estimate_price_from)
                $clientQuery->having('service_total', '>=', $request->search_estimate_price_from);
            $clientQuery->selectSub($estimateServices->getQuery(), 'service_total');
        }
        $clientQuery->groupBy('clients.client_id');
        if($extra_select){
            $clientQuery->selectRaw($extra_select);
        }
        else{
            $clientQuery->selectRaw(
                $totalsExtraSelect.'
                estimates.status,
                estimates.lead_id,
                estimates.estimate_id,
                estimates.user_id,
                estimate_statuses.est_status_name,
                CONCAT( users.firstname, \' \', users.lastname ) AS estimator,
                leads.lead_address,
                leads.lead_city,
                leads.lead_state,
                leads.lead_zip,
                leads.lead_country,
                client_name,
                clients_contacts.cc_name,
                client_address,
                client_address2,
                clients_contacts.cc_phone,
                clients_contacts.cc_email,
                client_main_intersection,
                clients.client_id,
                clients.client_date_created,
                client_last_qb_time_log,
                client_last_qb_sync_result,
                client_qb_id,
                client_type,
                client_city,
                client_state,
                client_zip,
                client_brand_id
            ');
        }
    }

    /**
     * @param $clientQuery
     * @param Request $request
     * @param bool|string $select
     */
    public function scopeApiFilterClient($clientQuery, Request $request, $select = false)
    {
        /* Client Search By Name */
        $totalsExtraSelect = null;
        if ($request->has('filters.search') && $request->input('filters.search'))  {
            $ClientModel = $this;
            $ClientsContact = new ClientsContact();
            $where_in_id = DB::query()->fromSub(function($query) use ($request, $ClientModel, $ClientsContact) {
                $query->from($ClientModel->globalSearchQuery($request->input('filters.search')), 'c1')
                    ->union($ClientsContact->globalSearchQuery($request->input('filters.search')));
            }, 't1')
                ->groupBy(['item_module_name', 'item_id'])
                ->pluck('item_id')->toArray();

            $clientQuery->whereIn('clients.client_id', $where_in_id);
        }

        /* Client Contacts */
        $clientQuery->leftJoin('clients_contacts', function ($join) {
            $join->on('clients_contacts.cc_client_id', '=', 'clients.client_id')->where('clients_contacts.cc_print',  '1');
        });

        $clientQuery->leftJoin('estimates', 'clients.client_id', '=', 'estimates.client_id');
        $clientQuery->leftJoin('leads', 'estimates.lead_id', '=', 'leads.lead_id');

        /* Tags */
        if ($request->has('filters.tags')) {
            if(is_string($request->input('filters.tags'))) {
                $clientQuery->whereHas('tags', function ($query) use ($request) {
                    $query->whereIn('client_tags.tag_id', array_filter(explode('|', $request->input('filters.tags'))) ?? []);
                });
            } elseif(is_array($request->input('filters.tags'))) {
                $clientQuery->whereHas('tags', function ($query) use ($request) {
                    $query->whereIn('client_tags.tag_id', $request->input('filters.tags'));
                });
            }
        } elseif($request->has('filters.tag_names') && is_array($request->input('filters.tag_names')) && !empty($request->input('filters.tag_names'))) {
            $clientQuery->whereHas('tags', function ($query) use ($request) {
                $query->whereIn('tags.name', $request->input('filters.tag_names'));
            });
        }

        if($request->has('filters')) {

            $totalsSubQuery = false;

            $clientQuery->leftJoin('estimate_statuses', 'estimates.status', '=', 'estimate_statuses.est_status_id');

            if ($request->has('filters.search_estimate_from')) {
                $clientQuery->where('estimates.date_created', '>=', strtotime($request->input('filters.search_estimate_from')));
            }

            if ($request->has('filters.search_estimate_to')) {
                $clientQuery->where('estimates.date_created', '<=', strtotime($request->input('filters.search_estimate_to')));
            }

            if ($request->has('filters.estimator_id')) {
                $clientQuery->where('estimates.user_id', $request->input('filters.estimator_id'));
            }

            if ($request->has('filters.is_confirmed')) {
                $clientQuery->where('estimate_statuses.est_status_confirmed', 1);
            }

            if ($request->has('filters.total_from')) {
                $totalsSubQuery = true;
                $clientQuery->having('total_estimate_price', '>=', $request->input('filters.total_from'));
            }

            if ($request->has('filters.total_to')) {
                $totalsSubQuery = true;
                $clientQuery->having('total_estimate_price', '<=', $request->input('filters.total_to'));
            }

            if($totalsSubQuery) {
                $CI =& get_instance();
                $totalsSubQuery = $CI->mdl_estimates_orm->calcQuery(isset($where_in_id) && !empty($where_in_id)?['estimates.client_id'=>$where_in_id]:[]);
            }

            if($totalsSubQuery) {
                $totalsExtraSelect = 'sum(totals.sum_without_tax) as total_estimate_price,';
                $totalsExtraSelect .= 'sum(if(estimate_statuses.est_status_confirmed,totals.sum_without_tax, 0)) as total_confirmed_estimates_amount,';
                $totalsExtraSelect .= 'sum(if(estimate_statuses.est_status_declined,totals.sum_without_tax, 0)) as total_declined_estimates_amount,';
                $totalsExtraSelect .= 'sum(if((estimate_statuses.est_status_declined <> 1 AND estimate_statuses.est_status_confirmed <> 1),totals.sum_without_tax, 0)) as total_pending_estimates_amount,';

                $clientQuery->leftJoin(DB::raw('(' . $totalsSubQuery. ') AS totals'), ['totals.estimate_id' => 'estimates.estimate_id']);
            }
        }

        if ($request->has('filters.brand_id')) {
            $clientQuery->where('clients.client_brand_id', $request->input('filters.brand_id'));
        }

        if ($request->has('filters.date_from')) {
            $clientQuery->where('clients.client_date_created', '>=', $request->input('filters.date_from'));
        }

        if ($request->has('filters.date_to')) {
            $clientQuery->where('clients.client_date_created', '<=', $request->input('filters.date_to'));
        }

        $clientQuery->groupBy('clients.client_id');
        if($select){
            $clientQuery->selectRaw($select);
        } else {
            $clientQuery->selectRaw(
                $totalsExtraSelect .= implode(',', self::API_GET_BASE_FIELDS)
            );
        }
    }

    /**
     * @return array
     */
    function getBrandAttribute(){
        if(!isset($this->attributes['client_brand_id']))
            return [];

        $brand = [
            'brand_office_lat'=>brand_office_lat($this->attributes['client_brand_id']),
            'brand_office_lon'=>brand_office_lat($this->attributes['client_brand_id']),
            'brand_office_address'=>brand_office_address($this->attributes['client_brand_id']),
            'brand_office_region'=>brand_office_region($this->attributes['client_brand_id']),
            'brand_office_city' => brand_office_city($this->attributes['client_brand_id']),
            'brand_office_state'=> brand_office_state($this->attributes['client_brand_id']),
            'brand_office_zip' => brand_office_zip($this->attributes['client_brand_id']),
            'brand_office_country' => brand_office_country($this->attributes['client_brand_id']),
            'brand_phone' => brand_phone($this->attributes['client_brand_id']),
            'brand_name' => brand_name($this->attributes['client_brand_id']),
            'brand_email' => brand_email($this->attributes['client_brand_id']),
            'brand_site' => brand_site($this->attributes['client_brand_id']),
            'brand_site_http' => brand_site_http($this->attributes['client_brand_id']),
            'brand_address' => brand_address($this->attributes['client_brand_id'])
        ];

        //$address = implode(', ', array_diff($address_array, array('', NULL, false)));

        return $brand;
    }

    /**
     * @param $query
     * @return mixed
     */
    public function scopeBaseFields($query)
    {
        $query_string = implode(',', $this->base_fields);
        return $query->select(DB::raw($query_string));
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

    function getSearchable()
    {
        return $this->searchable;
    }

    function scopeApiOrder($query) {
        return $query->orderBy('client_id', 'DESC');
    }

    /**
     * @param $query
     * @return mixed
     */
    public function scopeApiFields($query)
    {
        return $query->select(static::API_FIELDS);
    }

    /**
     * @param $query
     * @return mixed
     */
    public function scopePrimaryContactFields($query)
    {
        return $query->select([static::PRIMARY_CONTACT_FIELDS]);
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

        if(is_cl_permission_none()) {
            $query->where('estimates.user_id', -1);
        } elseif (is_cl_permission_owner()) {

            $query->leftJoin('leads as ldperm', 'ldperm.client_id', '=', 'clients.client_id');
            $query->leftJoin('estimates as estperm', 'estperm.client_id', '=', 'clients.client_id');

            $query->where(function($q) use ($user) {
                $q->whereClientMaker($user->id)
                    ->orWhere('ldperm.lead_author_id', $user->id)
                    ->orWhere('ldperm.lead_estimator', $user->id)
                    ->orWhere('estperm.user_id', $user->id);
            });
        }

        return $query;
    }

    /**
     * Get client base fields with primary contact
     *
     * @param int $id
     * @return mixed
     */
    public static function getWithContact(int $id) {
        return Client::baseFields()
            ->addSelect(['client_payment_driver', 'client_payment_profile_id'])
            ->with('primary_contact')
            ->where('client_id', $id)
            ->first();
    }

    /**
     * @param $clientId
     * @return object
     */
    public static function getApiDetails($clientId) {
        $details = Client::select(static::API_FIELDS)
            ->addSelect(DB::raw('0 as primary_contact_id'))
            ->where('client_id', '=', $clientId)
            ->with([
                'contacts:cc_client_id,cc_title,cc_id,cc_name,cc_phone,cc_email,cc_print',
                'leads' => function ($query) use ($clientId) {
                    $query->select([
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
                        'lead_reason_status_id',
                        'lead_add_info'
                    ])
                        ->with([
                            'estimator:id,firstname,lastname',
                            'status:lead_status_id,lead_status_name',
                            'reasonStatus:reason_id,reason_name',
                            'estimate' => function ($query) use ($clientId) {
                                $query->select(['lead_id', 'estimate_id', 'estimate_no', 'date_created', 'status', 'user_id', 'estimate_reason_decline'])
                                    ->with([
                                        'estimate_status:est_status_id,est_status_name,est_status_declined,est_status_default,est_status_confirmed,est_status_sent',
                                        'estimate_reason_status',
                                        'estimate_crews' => function ($query) {
                                            $query->crewsNamesLine(false);
                                        },
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
                                            ])->with(['status:invoice_status_id,invoice_status_name,completed,priority']);
                                        },
                                        'client_payments'
                                    ])
                                    ->detailsApiTotals(['estimates.client_id' => $clientId])
                                    ->withoutAppends();
                            }
                        ])
                        ->orderBy('lead_id', 'DESC')
                        ->withoutAppends();
                }
            ])
            ->first();

        if ($details) {
            if ($details->contacts) {
                $primary = $details->contacts->filter(function ($val, $key) {
                    return $val->cc_print == 1;
                });

                if (sizeof($primary)) {
                    $primary = $primary->first();
                    $details->primary_contact_id = $primary->cc_id;
                }
            }

            $estimatesTotal = [
                'estimates_total' => 0,
                'confirmed_total' => 0,
                'inprogress_total' => 0,
                'declined_total' => 0
            ];
            $salesTotals = [
                'total' => 0,
                'paid' => 0,
                'inprogress' => 0,
            ];

            // TODO: remove if all OK
//            $invoicesStatuses = null;
//            $invoicesStatusesTmp = [];

            foreach ($details->leads as $key => $lead) {
                $estimate = $lead->estimate;
                if ($estimate) {
                    $estimate->date_created = date('Y-m-d H:i:s', $estimate->date_created);
                    $estimatesTotal['estimates_total'] += $estimate->total_without_tax;

                    if ($estimate->estimate_status->est_status_confirmed) {
                        $estimatesTotal['confirmed_total'] += $estimate->total_without_tax;
                        $salesTotals['total'] += $estimate->total_with_tax;
                        $salesTotals['paid'] += $estimate->client_payments->sum('payment_amount');

                        // TODO: remove if all OK
//                        $invoice = $estimate->invoice;
//                        if ($invoice) {
//                            $name = $invoice->status->invoice_status_name;
//                            $isPaid = !!$invoice->status->completed;
//
//                            if (isset($invoicesStatusesTmp[$name])) {
//                                $invoicesStatusesTmp[$name]['totals']['with_tax'] += $estimate->total_with_tax;
//                                $invoicesStatusesTmp[$name]['totals']['without_tax'] += $estimate->total_without_tax;
//                            } else {
//                                $invoicesStatusesTmp[$name] = [
//                                    'priority' => $invoice->status->priority,
//                                    'name' => $name,
//                                    'isPaid' => $isPaid,
//                                    'totals' => [
//                                        'with_tax' => $estimate->total_with_tax,
//                                        'without_tax' => $estimate->total_without_tax
//                                    ]
//                                ];
//                            }
//                        }
                    }
                    elseif ($estimate->estimate_status->est_status_declined) {
                        $estimatesTotal['declined_total'] += $estimate->total_without_tax;
                    }
                    else {
                        $estimatesTotal['inprogress_total'] += $estimate->total_without_tax;
                    }
                }
            }

            // TODO: remove if all OK
//            if (sizeof($invoicesStatusesTmp)) {
//                $invoicesStatuses = array_column($invoicesStatusesTmp, null, 'int');
//
//                usort($invoicesStatuses, function($a, $b) {
//                    return $a['priority'] <=> $b['priority'];
//                });
//            }
//            $details->invoices_statuses = $invoicesStatuses;

            $salesTotals['inprogress'] = $salesTotals['total'] - $salesTotals['paid'];

            $details->estimates_totals = $estimatesTotal;
            $details->sales_totals = $salesTotals;
        }

        return $details;
    }
}
