<?php


namespace application\modules\estimates\models;
use application\core\Database\EloquentModel;
use application\modules\classes\models\QBClass;
use application\modules\crew\models\Crew;
use application\modules\estimates\models\Estimate;
use application\modules\estimates\models\Service;
use application\modules\estimates\models\EstimatesBundle;
use application\modules\estimates\models\TreeInventoryEstimateService;
use application\modules\tree_inventory\models\WorkType;
use DB;

class EstimatesService extends EloquentModel
{
    const ATTR_ESTIMATE_ID = 'estimate_id';
    const ATTR_ESTIMATE_SERVICE_CLASS_ID = 'estimate_service_class_id';
    const ATTR_SERVICE_PRICE = 'service_price';
    const ATTR_SERVICE_ID = 'service_id';
    const FINISHED_SERVICE_STATUSES = [1, 2];

    protected $table = 'estimates_services';
    protected $primaryKey = 'id';

    public $base_fields = [
        'estimates_services.id',
        'estimates_services.service_id',
        'estimates_services.estimate_id',
        'estimates_services.service_description',
        'estimates_services.service_time',
        'estimates_services.service_travel_time',
        'estimates_services.service_price',
        'estimates_services.service_priority',
        'estimates_services.service_size',
        'estimates_services.service_status',
        'estimates_services.service_disposal_time',
        'estimates_services.quantity',
        'estimates_services.cost',
        'estimates_services.non_taxable',
        'estimates_services.estimate_service_class_id',
        'estimates_services.service_markup_rate',
        'estimates_services.service_overhead_rate',
        'estimates_services.estimate_service_ti_title',
        'estimates_services.is_view_in_pdf'
    ];

    public $schedule_fields = [
        'id',
        'service_id',
        'estimate_id',
        'service_description',
        'service_time',
        'service_travel_time',
        'service_price',
        'service_priority',
        'service_size',
        'service_status',
        'service_disposal_time',
        'quantity',
        'cost',
        'non_taxable',
        'estimate_service_ti_title'
    ];


    protected $appends = [];

    /**
     * @var array 
     */
    public $fillable = ['service_status'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    function service(){
        return $this->hasOne(Service::class, 'service_id', 'service_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function classes() {
        return $this->hasOne(QBClass::class, 'class_id', 'estimate_service_class_id');
    }

    /**
     * @return \application\models\Relations\HasManySyncable
     */
    public function equipments() {
        return $this->hasMany(EstimatesServicesEquipments::class, 'equipment_service_id', 'id')
            ->with(['equipment', 'attachment']);
    }

    /**
     * relation estimates_services_expenses table
     * @return \application\models\Relations\HasManySyncable
     */
    public function tree_inventory() {
        return $this->hasOne(TreeInventoryEstimateService::class, 'ties_estimate_service_id', 'id');
    }

    public function expenses() {
        return $this->hasMany(EstimatesServicesExpenses::class, 'ese_estimate_service_id', 'id');
    }

    function bundle(){
        return $this->hasMany(EstimatesBundle::class, 'eb_bundle_id', 'id');
    }

    function bundle_service(){
        return $this->hasOne(EstimatesBundle::class, 'eb_service_id', 'id')->with(['service']);
    }

    function services_crew(){
        return $this->hasMany(EstimatesServicesCrew::class, 'crew_service_id');
    }

    function estimate(){
        return $this->hasOne(Estimate::class, 'estimate_id', 'estimate_id');
    }

    function status(){
        return $this->hasOne(EstimatesServicesStatus::class, 'services_status_id', 'service_status');
    }

    function scopeSecvicesCalc($query){
        return $query->select(DB::raw("ROUND(SUM(service_time + service_travel_time + service_disposal_time), 2) as totaltime"));
    }

    function scopeNewService($query){
        return $query->whereNotIn('service_status', self::FINISHED_SERVICE_STATUSES);
    }

    function scopeBaseFields($query)
    {
        $query_string = implode(',', $this->base_fields);
        return $query->select(DB::raw($query_string));
    }

    function scopeScheduleFields($query)
    {
        $query_string = implode(',', $this->schedule_fields);
        return $query->select(DB::raw($query_string));
    }

    function crew() {
        return $this->belongsToMany(Crew::class, 'estimates_services_crews', 'crew_service_id', 'crew_user_id');
    }

    public function scopeWithoutBundleServices($query){
        return $query->doesntHave('bundle_service');
    }

    public function tree_inventory_estimate_service_work_types() {
        return $this->hasManyThrough(TreeInventoryEstimateServiceWorkTypes::class,TreeInventoryEstimateService::class, 'ties_estimate_service_id', 'tieswt_ties_id','id','ties_id');
    }
}
