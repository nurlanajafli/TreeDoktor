<?php

namespace application\modules\clients\models;

use application\core\Database\EloquentModel;
use application\modules\administration\models\FollowupSettings;

class Tag extends EloquentModel
{

    const ATTR_TAG_ID = 'tag_id';
    const ATTR_NAME = 'name';
    const ATTR_CREATED_AT = 'created_at';
    const ATTR_UPDATED_AT = 'updated_at';

    /**
     * @var string
     */
    protected $primaryKey = 'tag_id';

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * Limit before tags expand on client list page
     */
    public const TAGS_EXPAND_LIMIT = 30;
    protected $hidden = ['created_at', 'updated_at'];
    protected $fillable = ['name'];
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function clients()
    {
        return $this->belongsToMany(Client::class, 'client_tags', 'tag_id', 'client_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function followupSettings()
    {
        return $this->belongsToMany(FollowupSettings::class, 'followup_settings_tags', 'tag_id', 'fs_id');
    }

    public static function select2FormatData()
    {
        $tags = self::all();

        return $tags->mapWithKeys(function ($item, $index) {
            return [
               $index => [
                'id' => $item['tag_id'],
                'text' => $item['name'],
               ]
            ];
        })->toJson();
    }

    public static function syncTagsWithClient(/*Array */$tagNames, $clientId)
    {
        $tagIds = [];
        foreach($tagNames as $tagName) {
            if (trim($tagName) === '') {
                continue;
            }

            $tag = self::updateOrCreate(
                ['name' => $tagName]
            );

            array_push($tagIds, $tag->tag_id);
        }

        $client = Client::find($clientId);
        $client->tags()->sync($tagIds);

        return true;
    }

    public static function syncTagWithClient($tagName, $clientId)
    {
        $tag = self::updateOrCreate(
            ['name' => $tagName]
        );

        $tag->clients()->syncWithoutDetaching($clientId);

        return true;
    }

    public static function deleteFreeTags()
    {
        self::whereNull('client_tags.client_id')
            ->leftJoin('client_tags', 'tags.tag_id', '=', 'client_tags.tag_id')
            ->delete();
    }
}