<?php

namespace application\modules\clients\models;

use application\core\Database\EloquentModel;
class Tag extends EloquentModel
{

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'client_tags', 'tag_id', 'client_id');
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