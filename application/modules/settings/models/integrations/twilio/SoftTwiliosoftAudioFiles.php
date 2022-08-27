<?php

namespace application\modules\settings\models\integrations\twilio;

use application\core\Database\EloquentModel;

/**
 * Class SoftTwiliosoftAudioFiles
 * @package application\modules\soft_twilio_calls\models
 */
class SoftTwiliosoftAudioFiles extends EloquentModel
{
    /**
     * @var string
     */
    protected $table = 'soft_twilio_audio_files';

    /**
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * @var array
     */
    protected $fillable = [
        'label',
        'user_id',
        'url',
        'tag'
    ];

}