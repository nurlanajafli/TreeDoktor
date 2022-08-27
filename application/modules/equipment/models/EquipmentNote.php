<?php

namespace application\modules\equipment\models;

use application\core\Database\Casts\AppDateTime;
use application\core\Database\EloquentModel;
use application\modules\user\models\User;

/**
 * application\modules\equipment\models\EquipmentNote
 *
 * @property int $note_id
 * @property int $user_id
 * @property int $eq_id
 * @property int|null $repair_id
 * @property int|null $service_report_id
 * @property string $note_description
 * @property int $note_type
 * @property \Illuminate\Support\Carbon $note_created_at
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentNote newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentNote newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentNote query()
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentNote whereEqId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentNote whereNoteCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentNote whereNoteDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentNote whereNoteId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentNote whereNoteType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentNote whereRepairId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentNote whereServiceReportId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentNote whereUserId($value)
 * @mixin \Eloquent
 * @property int|null $note_parent_id
 * @property-read \application\modules\equipment\models\Equipment $equipment
 * @property-read \Illuminate\Database\Eloquent\Collection|\application\modules\equipment\models\EquipmentFile[] $files
 * @property-read int|null $files_count
 * @property-read mixed $note_type_str
 * @property-read \application\modules\equipment\models\EquipmentRepair|null $repair
 * @property-read \Illuminate\Database\Eloquent\Collection|\application\modules\equipment\models\EquipmentNote[] $replies
 * @property-read int|null $replies_count
 * @property-read \application\modules\equipment\models\EquipmentServiceReport|null $service_report
 * @property-read \application\modules\user\models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentNote whereNoteParentId($value)
 */
class EquipmentNote extends EloquentModel
{
    const TYPE_SYSTEM = 1;
    const TYPE_ATTACHMENT = 2;
    const TYPE_INFO = 3;
    const TYPES = [
        self::TYPE_SYSTEM => 'System',
        self::TYPE_ATTACHMENT => 'Files',
        self::TYPE_INFO => 'Info'
    ];
    protected $table = 'equipment_notes';
    protected $primaryKey = 'note_id';
    protected $fillable = [
        'note_parent_id',
        'user_id',
        'eq_id',
        'repair_id',
        'service_report_id',
        'note_description',
        'note_type',
        'note_created_at'
    ];

    protected $appends = [
        'note_type_str',
    ];

    const CREATED_AT = 'note_created_at';

    /**
     * The supported cast types are: integer, real, float, double, decimal:<digits>, string, boolean,
     * object, array, collection, date, datetime, and timestamp.
     * When casting to decimal, you must define the number of digits (decimal:2).
     * @var array
     */
    protected $casts = [
        'note_type' => 'integer',
        'note_created_at' => AppDateTime::class
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($query) {
            if (($user = request()->user()) && !isset($query->attributes['user_id'])) {
                $query->user_id = $user->id;
            }
            if (!isset($query->attributes['note_type'])) {
                $query->note_type = self::TYPE_INFO;
            }
        });

        static::deleting(function ($query) {
            $query->files()->delete();
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function equipment()
    {
        return $this->belongsTo(Equipment::class, 'eq_id');
    }

    public function repair()
    {
        return $this->belongsTo(EquipmentRepair::class, 'repair_id');
    }

    public function service_report()
    {
        return $this->belongsTo(EquipmentServiceReport::class, 'service_report_id');
    }

    public function replies()
    {
        return $this->hasMany(self::class, 'note_parent_id');
    }

    public function files()
    {
        return $this->hasMany(EquipmentFile::class, 'note_id');
    }

    public function getNoteTypeStrAttribute()
    {
        return self::TYPES[$this->attributes['note_type']];
    }


    public static function post(int $eq_id, array $to, string $message, int $type = self::TYPE_SYSTEM): void
    {
        self::create([
                'eq_id' => $eq_id,
                'note_description' => $message,
                'note_type' => $type
            ] + $to);
    }
}
