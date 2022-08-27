<?php
namespace application\modules\tasks\models;

use application\modules\tasks\models\TaskCategory;
use application\core\Database\EloquentModel;
use application\modules\clients\models\Client;
use application\modules\user\models\User;
use Illuminate\Database\Eloquent\Builder;
use application\modules\leads\models\Lead;
use Illuminate\Support\Facades\DB;
use function Clue\StreamFilter\fun;

class Task extends EloquentModel
{
    protected $table = 'client_tasks';
    protected $primaryKey = 'task_id';
    protected $appends = [
        'full_address',
        'marker_color',
        'task_schedule_date',
        'task_schedule_start',
        'task_schedule_end',
        'statuses',
        'task_date'
    ];

    CONST DEFAULT_MARKER_COLOR = '#ADDE63';
    CONST NEW_TASK_STATUS = 'new';
    CONST CANCELED_TASK_STATUS = 'canceled';
    CONST DONE_TASK_STATUS = 'done';
    CONST MAP_TASK = 0;
    CONST NO_MAP_TASK = 1;

    function user()
    {
        return $this->hasOne(User::class, 'id', 'task_assigned_user');
    }

    function owner()
    {
        return $this->hasOne(User::class, 'id', 'task_author_id');
    }

    function category()
    {
        return $this->hasOne(TaskCategory::class, 'category_id', 'task_category');
    }

    function client()
    {
        return $this->hasOne(Client::class, 'client_id', 'task_client_id')->with('primary_contact');
    }

    public function lead() {
        return $this->hasOne(Lead::class, 'lead_id', 'task_lead_id');
    }

    function status()
    {
        return [
            self::NEW_TASK_STATUS,
            self::CANCELED_TASK_STATUS,
            self::DONE_TASK_STATUS
        ];
    }

    public function scopeNew($query)
    {
        return $query->where('task_status', '=', self::NEW_TASK_STATUS);
    }

    public function scopeMap($query)
    {
        return $query->where('task_no_map', '=', self::MAP_TASK);
    }

    public function scopeWithoutExpiredTaskByDay($query)
    {
        $appointmentTaskExpirationDay = config_item('AppointmentTaskExpirationDay');
        return $query->whereBetween(DB::raw('NOW()'), [
            DB::raw("task_date"),
            DB::raw("DATE_ADD(`task_date`, INTERVAL $appointmentTaskExpirationDay DAY)")
        ]);
    }

    public function scopeEstimator($query)
    {
        return $query->whereHas('user', function (Builder $query) {
            $query->estimator();
        });
    }

    public function getFullAddressAttribute()
    {
        $address_array = [
            $this->attributes['task_address'],
            $this->attributes['task_city'],
            $this->attributes['task_state'],
            $this->attributes['task_zip']
        ];

        $address = implode(', ', array_diff($address_array, ['', null, false]));

        return $address;
    }

    function getMarkerColorAttribute()
    {
        return self::DEFAULT_MARKER_COLOR;
    }

    function getTaskScheduleDateAttribute()
    {
        return date('m/d', strtotime($this->attributes['task_date']));
    }

    function getTaskScheduleStartAttribute()
    {
        return getTimeWithDate($this->attributes['task_start'], 'H:i:s', true);
    }

    function getTaskScheduleEndAttribute()
    {
        return getTimeWithDate($this->attributes['task_end'], 'H:i:s', true);
    }

    function getStatusesAttribute()
    {
        return [
            self::NEW_TASK_STATUS => ucfirst(self::NEW_TASK_STATUS),
            self::CANCELED_TASK_STATUS => ucfirst(self::CANCELED_TASK_STATUS),
            self::DONE_TASK_STATUS => ucfirst(self::DONE_TASK_STATUS)
        ];
    }

    function getTaskDateAttribute()
    {
        return getDateTimeWithDate($this->attributes['task_date'], 'Y-m-d');
    }

    public function getTasksQuery($where = [], $orders = FALSE)
    {

        $TaskQuery = Task::select(DB::raw(
            static::tableName() . ".*, CONCAT(task_date, ' ', task_start) as task_start_date, CONCAT(task_date, ' ', task_end) as task_end_date")
        )->with([
            'category',
            'owner',
            'user' => function($query) {
                $query->with('employee');
            },
            'client' =>  function($query) {
                $query->select(\DB::raw('clients.client_phone, clients.client_email, clients.client_name, clients.client_unsubscribe, clients.client_id'));
                $query->with('primary_contact');
            },
            'lead' => function($query) {
                $query->with('status');
            },
        ]);

        if (isset($where) && !empty($where)) {
            foreach ($where as $value) {
                $TaskQuery->where($value);
            }
        }

        if($orders && !empty($orders)) {
            foreach ($orders as $key => $value) {
                $TaskQuery->orderBy($key, $value);
            }
        }

       return $TaskQuery;
    }

    /**
     * Count when group by exist, done to prevent wrong  results and errors
     */
    public static function countAggregate($query)
    {
        return DB::table(DB::raw("({$query->toSql()}) as sub"))->mergeBindings($query->toBase())->count();
    }
}
