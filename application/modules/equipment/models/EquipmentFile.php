<?php

namespace application\modules\equipment\models;

use application\core\Database\Casts\AppDateTime;
use application\core\Database\EloquentModel;
use application\modules\equipment\models\traits\PostNoteTrait;
use application\modules\user\models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * application\modules\equipment\models\EquipmentFile
 *
 * @property int $file_id
 * @property int $user_id
 * @property int $eq_id
 * @property int|null $repair_id
 * @property int|null $service_report_id
 * @property int|null $part_id
 * @property string $file_name
 * @property \Illuminate\Support\Carbon $file_created_at
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentFile newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentFile newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentFile query()
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentFile whereEqId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentFile whereFileCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentFile whereFileId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentFile whereFileName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentFile wherePartId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentFile whereRepairId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentFile whereServiceReportId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentFile whereUserId($value)
 * @mixin \Eloquent
 * @property int|null $note_id
 * @property-read \application\modules\equipment\models\Equipment $equipment
 * @property-read mixed $file_url
 * @property-read \application\modules\equipment\models\EquipmentNote|null $note
 * @property-read \application\modules\equipment\models\EquipmentPart|null $part
 * @property-read \application\modules\equipment\models\EquipmentRepair|null $repair
 * @property-read \application\modules\equipment\models\EquipmentServiceReport|null $service_report
 * @property-read \application\modules\user\models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentFile whereNoteId($value)
 * @property string|null $file_mime
 * @property int|null $file_size
 * @property-read mixed $file_content
 * @property-read mixed $file_size_human
 * @property-read mixed $file_stream
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentFile whereFileMime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentFile whereFileSize($value)
 */
class EquipmentFile extends EloquentModel
{
    use PostNoteTrait;
    protected $table = 'equipment_files';
    protected $primaryKey = 'file_id';
    protected $fillable = [
        'user_id',
        'eq_id',
        'repair_id',
        'service_report_id',
        'part_id',
        'note_id',
        'file_name',
        'file_mime',
        'file_size',
        'file_created_at',
    ];

    const COLUMNS = [
        'user_id' => 'User',
        'eq_id' => 'Equipment',
        'repair_id' => 'Repair Request',
        'service_report_id' => 'Service Report',
        'part_id' => 'Part',
        'note_id' => 'Note',
        'file_name' => 'Name',
        'file_mime' => 'Mime Type',
        'file_size' => 'Size',
        'counter_note' => 'Note',
        'file_created_at' => 'Created At'
    ];
    const ENT_NAME = 'File';
    const NAME_COL = 'file_name';

    protected $appends = ['file_url'];

    const CREATED_AT = 'file_created_at';

    /**
     * The supported cast types are: integer, real, float, double, decimal:<digits>, string, boolean,
     * object, array, collection, date, datetime, and timestamp.
     * When casting to decimal, you must define the number of digits (decimal:2).
     * @var array
     */
    protected $casts = [
        'file_created_at' => AppDateTime::class
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($query) {
            if (($user = request()->user()) && !isset($query->attributes['user_id'])) {
                $query->user_id = $user->id;
            }
            if ($query->attributes['file_name'] instanceof UploadedFile) {
                $name = self::prepareName($query->attributes['file_name']->getClientOriginalName());
                $filePath = 'uploads/equipment/' . $query->attributes['eq_id'] . '/';
                $query->attributes['file_mime'] = $query->attributes['file_name']->getMimeType();
                $query->attributes['file_size'] = $query->attributes['file_name']->getSize();
                Storage::put($filePath . $name, file_get_contents($query->attributes['file_name']));
                $query->attributes['file_name'] = $name;

            }
        });
        static::deleted(function ($query) {
            $filePath = 'uploads/equipment/' . $query->eq_id . '/';
            Storage::delete($filePath . $query->file_name);
        });
    }

    public static function sync(EloquentModel $relModel, array $files, array $exists)
    {
        $oldFileKeys = $relModel->files->pluck('file_id')->all();
        if ($files !== null) {
            foreach ($files as $key => $file) {
                if ($file === null) {
                    continue;
                }
                if (Str::startsWith($key, 'new_')) {
                    $relModel->files()->create([
                        'eq_id' => $relModel->eq_id,
                        'file_name' => $file
                    ]);
                }
            }
        }
        $filesExist = array_keys($exists);
        $filesForDelete = array_diff($oldFileKeys, $filesExist);
        foreach ($relModel->files as $file) {
            if (in_array($file->file_id, $filesForDelete)) {
                $file->delete();
            }
        }
    }

    public static function prepareName($originName)
    {
        return pathinfo($originName, PATHINFO_FILENAME) . '_' . time() . '.' . pathinfo($originName,
                PATHINFO_EXTENSION);
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

    public function part()
    {
        return $this->belongsTo(EquipmentPart::class, 'part_id');
    }

    public function note()
    {
        return $this->belongsTo(EquipmentNote::class, 'note_id');
    }

    public function getFileUrlAttribute()
    {
        return base_url(Storage::url('uploads/equipment/' . $this->attributes['eq_id'] . '/' . $this->attributes['file_name']));
    }


    public function getFileSizeHumanAttribute()
    {
        if ($this->attributes['file_size'] > 0) {
            $size = (int)$this->attributes['file_size'];
            $base = log($size) / log(1024);
            $suffixes = array(' bytes', ' KB', ' MB', ' GB', ' TB');
            return round(1024 ** ($base - floor($base)), 2) . $suffixes[(int)floor($base)];
        } else {
            return $this->attributes['file_size'];
        }
    }

    public function getFileStreamAttribute()
    {
        return Storage::readStream('uploads/equipment/' . $this->attributes['eq_id'] . '/' . $this->attributes['file_name']);
    }

    public function getFileContentAttribute()
    {
        return Storage::get('uploads/equipment/' . $this->attributes['eq_id'] . '/' . $this->attributes['file_name']);
    }
}
