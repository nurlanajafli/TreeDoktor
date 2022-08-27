<?php

namespace application\modules\user\models;

use application\modules\settings\models\integrations\twilio\SoftTwilioWorkerModel;
use application\modules\common\models\traits\Select2Trait;
use application\modules\schedule\models\TeamExpesesReport;
use application\modules\employees\models\EmployeeWorked;
use application\modules\employees\models\Employee;
use application\modules\schedule\models\Expense;
use application\core\Auth\User as AuthUser;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Notifications\Notifiable;
use Carbon\Carbon;
use DB;

/**
 * application\modules\user\models\User
 *
 * @property int $id
 * @property string $user_type
 * @property string $emailid
 * @property string $password
 * @property string|null $firstname
 * @property string|null $lastname
 * @property \Illuminate\Support\Carbon $added_on
 * @property \Illuminate\Support\Carbon|null $updated_on
 * @property string $active_status
 * @property string $picture
 * @property \Illuminate\Support\Carbon|null $last_login
 * @property int $rate
 * @property string $color
 * @property int|null $last_online
 * @property string|null $user_email
 * @property string|null $twilio_worker_id
 * @property string|null $twilio_workspace_id
 * @property bool|null $twilio_worker_agent
 * @property bool|null $twilio_user_list
 * @property bool $twilio_support
 * @property int|null $twilio_level
 * @property bool|null $user_task
 * @property string|null $user_signature
 * @property bool|null $user_active_employee
 * @property int|null $user_emp_id
 * @property bool $system_user
 * @property bool|null $duty
 * @property bool|null $worker_type
 * @property bool $is_appointment
 * @property bool $is_require_payment_details
 * @property-read \application\modules\user\models\UserMeta|null $meta
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\user\models\User forAuth()
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\user\models\User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\user\models\User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\user\models\User query()
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\user\models\User whereActiveStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\user\models\User whereAddedOn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\user\models\User whereColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\user\models\User whereDuty($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\user\models\User whereEmailid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\user\models\User whereFirstname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\user\models\User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\user\models\User whereIsAppointment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\user\models\User whereIsRequirePaymentDetails($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\user\models\User whereLastLogin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\user\models\User whereLastOnline($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\user\models\User whereLastname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\user\models\User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\user\models\User wherePicture($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\user\models\User whereRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\user\models\User whereSystemUser($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\user\models\User whereTwilioLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\user\models\User whereTwilioSupport($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\user\models\User whereTwilioUserList($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\user\models\User whereTwilioWorkerAgent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\user\models\User whereTwilioWorkerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\user\models\User whereTwilioWorkspaceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\user\models\User whereUpdatedOn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\user\models\User whereUserActiveEmployee($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\user\models\User whereUserEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\user\models\User whereUserEmpId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\user\models\User whereUserSignature($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\user\models\User whereUserTask($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\user\models\User whereUserType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\user\models\User whereWorkerType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\user\models\User withMeta()
 * @mixin \Eloquent
 * @property-read \application\modules\employees\models\Employee|null $employee
 * @property bool $is_tracked
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\user\models\User whereIsTracked($value)
 * @property-read mixed $full_name
 */
class User extends AuthUser
{
    use Notifiable;
    use Select2Trait;
    public $twilioVoiceDevices;
    protected $table = 'users';
    protected $primaryKey = 'id';

    const ATTR_ID = 'id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_type',
        'emailid',
        'password',
        'firstname',
        'lastname',
        'added_on',
        'updated_on',
        'active_status',
        'picture',
        'last_login',
        'rate',
        'color',
        'last_online',
        'user_email',
        'twilio_worker_id',
        'twilio_workspace_id',
        'twilio_worker_agent',
        'twilio_user_list',
        'twilio_support',
        'twilio_level',
        'user_task',
        'user_signature',
        'user_active_employee',
        'user_emp_id',
        'system_user',
        'duty',
        'worker_type',
        'is_appointment',
        'is_require_payment_details',
    ];

    /**
     * The supported cast types are: integer, real, float, double, decimal:<digits>, string, boolean,
     * object, array, collection, date, datetime, and timestamp.
     * When casting to decimal, you must define the number of digits (decimal:2).
     * @var array
     */
    protected $casts = [
        'rate' => 'int',
        'last_online' => 'timestamp',
        'twilio_worker_agent' => 'boolean',
        'twilio_user_list' => 'boolean',
        'twilio_support' => 'boolean',
        'twilio_level' => 'integer',
        'user_task' => 'boolean',
        'user_active_employee' => 'boolean',
        'user_emp_id' => 'integer',
        'system_user' => 'boolean',
        'duty' => 'boolean',
        'worker_type' => 'boolean',
        'is_appointment' => 'boolean',
        'is_require_payment_details' => 'boolean',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password'
    ];

    protected $dates = [
        'added_on',
        'updated_on',
        'last_login'
    ];

    protected $appends = [
        'full_name',
        'initials'
    ];

    protected $api_fields = [
        'users.id',
        'users.firstname',
        'users.lastname',
    ];

    const ENT_NAME = 'User';
    const NAME_COL = 'full_name';
    const APP_MEMBER = [
        'id', 'firstname', 'lastname'
    ];

    public const AVATAR_WIDTH = 300;
    public const AVATAR_HEIGHT = 300;

    public $base_fields = [
        'users.id',
        'users.user_type',
        'users.emailid',
        'users.firstname',
        'users.lastname',
        'users.picture',
        'users.color',
        'users.user_email',
        'users.user_emp_id',
        'users.worker_type',
    ];

    public $portal_fields = [
        'users.id',
        'users.firstname',
        'users.lastname',
    ];

    /**
     * API application fields for get
     * @var array
     */
    const API_GET_FIELDS = [
        'users.id as user_id',
        'users.firstname as user_firstname',
        'users.lastname as user_lastname',
    ];

    const API_GET_BASE_FIELDS = [
        'users.id',
        'users.user_type',
        'users.emailid',
        'users.firstname',
        'users.lastname',
        'users.picture',
        'users.color',
        'users.user_email',
        'users.user_emp_id',
        'users.worker_type',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object|null
     */
    public static function getSystemUser()
    {
        return self::where('system_user', '=', 1)->first();
    }

    public function scopeNameAsc($query){
        return $query->orderBy('firstname');
    }

    /**
     * Scope a query to only include popular users.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForAuth($query)
    {
        return $query->where('votes', '>', 100);
    }


    public function scopeAppMember($query){
        return $query->select(['users.id', 'users.firstname', 'users.lastname', DB::raw("CONCAT(users.firstname, ' ', users.lastname) as name"), 'users.picture']);
    }


    /**
     * Scope a query to only include popular users.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithMeta($query)
    {
        return $query->select([
            'users.*',
            //'employees.*',
            //'users.id as employee_id',
            self::raw("CONCAT(users.firstname, ' ', users.lastname) as emp_name"),
            'user_meta.address1',
            'user_meta.address2',
            'user_meta.city',
            'user_meta.state',
            'user_meta.country',
            //'employees.emp_phone',
            //'employees.emp_feild_worker',
            //'ext_numbers.*'
        ])
            ->join('user_meta', 'users.id', '=', 'user_meta.user_id');
    }

    public function scopeEstimator($query){
        return $query->whereHas('employee', function (Builder $query) {
            $query->estimator();
        });
    }

    public function scopeTwilio(Builder $query) {
        return $query->where('twilio_worker_agent', '=', 1)
            ->orWhere('twilio_user_list', '=', 1)
            ->orWhere('twilio_support', '=', 1);
    }

    public function scopeActive($query) {
        return $query->where('active_status', '=', 'yes');
    }

    public function scopeNoSystem($query){
        return $query->where('system_user', '=', 0);
    }

    public function scopeFieldWorker($query){
        return $query->whereHas('employee', function ($q){
            $q->fieldWorker();
        });
    }
    /**
     * Get the meta record associated with the user.
     */
    public function meta()
    {
        return $this->hasOne(UserMeta::class, 'user_id');
    }

    public function modulesForPermissions()
    {
        return $this->hasMany(UserModule::class)->select('module_status', 'module_id');
    }

    public function modules()
    {
        return $this->hasMany(UserModule::class);
    }

    public function clperm()
    {
        return $this->modules()->where('module_id', 'CL');
    }

    public function tlcsteperm()
    {
        return $this->modules()->where('module_id', 'TLCSTE');
    }

    public function fwssperm()
    {
        return $this->modules()->where('module_id', 'FWSS');
    }

    public function fwiperm()
    {
        return $this->modules()->where('module_id', 'FWI');
    }

    public function gpsperm()
    {
        return $this->modules()->where('module_id', 'GPS');
    }

    public function employee()
    {
        return $this->hasOne(Employee::class, 'emp_user_id', 'id');
    }

    public function devices()
    {
        return $this->hasMany(UserDevices::class, 'device_user_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function twilioWorkers()
    {
        return $this->hasOne(SoftTwilioWorkerModel::class, 'user_id', 'id');
    }

    public function employeeWorked()
    {
        return $this->hasMany(EmployeeWorked::class, 'worked_user_id', 'id');
    }

    public function expenses_report(){
        return $this->hasMany(TeamExpesesReport::class, 'ter_user_id', 'id');
    }

    public function expense()
    {
        return $this->hasMany(Expense::class, 'expense_user_id', 'id');
    }

    public function getPictureAttribute()
    {
        return base_url((!empty($this->attributes['picture']) ? PICTURE_PATH . $this->attributes['picture'] : 'assets/pictures/avatar_default.jpg'));
    }

    public function getEmailAttribute()
    {
        return $this->attributes['user_email'] ?? false;
    }

    public function getFullNameAttribute()
    {
        return $this->attributes['firstname'] . ' '. $this->attributes['lastname'];
    }

    public function getInitialsAttribute()
    {
        $first_name = ($this->attributes['firstname'])?substr($this->attributes['firstname'], 0, 1):null;
        $last_name = ($this->attributes['lastname'])?substr($this->attributes['lastname'], 0, 1):null;

        if(!$first_name && $this->attributes['lastname']){
            return substr($this->attributes['lastname'], 0, 2);
        }
        if(!$last_name && $this->attributes['firstname']){
            return substr($this->attributes['firstname'], 0, 2);
        }

        return $first_name . $last_name;
    }

    function scopeBaseFields($query)
    {
        $query_string = implode(',', $this->base_fields);
        return $query->select(DB::raw($query_string));
    }

    function scopeBaseWithoutField($query, $except)
    {
        $fields = $this->base_fields;
        if(count($except)){
            $fields = collect($this->base_fields)->filter(function ($item) use ($except){
                return array_search($item, $except)===FALSE;
            })->toArray();
        }

        $query_string = implode(',', $fields);
        return $query->select(DB::raw($query_string));
    }

    /**
     * @param $users
     * @return array
     */
    public static function prepareDataForSelect2($users)
    {
        $result = [];
        if (!empty($users)) {
            foreach ($users as $user) {
                if ($user->system_user) {
                    continue;
                }

                $result[] = [
                    'id' => $user->id,
                    'text' => $user->firstname . ' ' . $user->lastname
                ];
            }
        }

        return $result;
    }

    public static function get_service_tags($users = []){
        if (empty($users)) {
            return [];
        }
        $result = [];


        foreach($users as $k => $user) {

            $result[ $k ][ 'id' ] = $user->id;
            $result[ $k ][ 'text' ] = $user->firstname . ' ' . $user->lastname;
        }

        return $result;
    }

    public function getActiveEstimators(){

    }

    /**
     * @return mixed
     */
    public static function getAllAvailableTwilioUsers($user_id = null)
    {
        $query = User::select(DB::raw('users.id, users.firstname, users.lastname, users.emailid'))
            ->join(SoftTwilioWorkerModel::tableName(),
                SoftTwilioWorkerModel::tableName() . '.' . SoftTwilioWorkerModel::ATTR_USER_ID,
                '=',
                User::tableName() . '.' . User::ATTR_ID,
                'left'
            ) ->whereNull(SoftTwilioWorkerModel::tableName() . '.' . SoftTwilioWorkerModel::ATTR_USER_ID);

        if (!is_null($user_id)) {
            $query->where(
                SoftTwilioWorkerModel::tableName() . '.' . SoftTwilioWorkerModel::ATTR_USER_ID,
                '=',
                $user_id
            );
        }
        return $query->noSystem()->active()->get()->toArray();
    }

    /**
     * @param $query
     * @return mixed
     */
    function scopeApiFields($query)
    {
        $query_string = implode(',', $this->api_fields);
        return $query->select(DB::raw($query_string));
    }

    /**
     * Get user data for auth
     *
     * @param array $data
     * @return User|object|null|bool
     */
    public static function getAuthData(array $data) {
        if (empty($data)) {
            return false;
        }

        return User::select([
            'id',
            'user_type',
            'firstname',
            'lastname',
            'rate',
            'picture',
            'last_login',
            'worker_type',
            'twilio_worker_id',
            'twilio_support',
            'twilio_workspace_id',
            'active_status',
            'is_tracked'
        ])
            ->where($data)
            ->with([
                'employee:emp_user_id,emp_field_estimator,emp_feild_worker,emp_check_work_time,emp_start_time',
                'clperm:user_id,module_status',
                'tlcsteperm:user_id,module_status',
                'fwssperm:user_id,module_status',
                'fwiperm:user_id,module_status',
                'gpsperm:user_id,module_status',
            ])
            ->first();
    }

    public function routeNotificationForFcm()
    {
        $dateLimit = Carbon::now();
        $dateLimit->sub(2, 'weeks');

        $devices = $this->devices
            ->where('device_token_expiration', '>=', $dateLimit->toDateTimeString())
            ->where('firebase_token', '<>', '')
            ->whereNotNull('firebase_token')
            ->pluck('firebase_token');

        if (!$devices->count()) {
            return false;
        }

        return $devices->toArray();
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
