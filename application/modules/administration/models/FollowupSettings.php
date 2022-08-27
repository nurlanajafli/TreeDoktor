<?php

namespace application\modules\administration\models;

use application\core\Database\EloquentModel;
use application\modules\clients\models\Tag as TagsModel;

class FollowupSettings extends EloquentModel
{
    /**
     * @var string
     */
    protected $primaryKey = 'fs_id';

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var array
     */
    protected $fillable = [
        'name',
        'fs_table',
        'fs_statuses',
        'fs_type',
        'fs_client_types',
        'fs_periodicity',
        'fs_every',
        'fs_time',
        'fs_template',
        'fs_subject',
        'fs_pdf',
        'fs_cron',
        'fs_disabled',
        'fs_table_number',
        'fs_time_periodicity',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function tags() {
        return $this->belongsToMany(TagsModel::class, 'followup_settings_tags', 'fs_id', 'tag_id');
    }

    /**
     * @return array
     */
    public function select2FormatData() {
        $result = [];
        foreach ($this->tags->toArray() as $tag) {
            $result[] = [
                'id' => $tag['tag_id'],
                'text' => $tag['name'],
            ];
        }
        return $result;
    }

}