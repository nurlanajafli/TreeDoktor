<?php
namespace application\modules\settings\models;
use application\core\Database\EloquentModel;
use application\modules\settings\integrations\twilio\classes\BaseTwilio as BT;
use Illuminate\Database\Query\Builder;

class Settings extends EloquentModel
{
    /**
     * @var string
     */
    protected $table = 'settings';

    /**
     * @var string
     */
    protected $primaryKey = 'stt_id';

    /**
     * @var array
     */
    protected $fillable = [
        'stt_key_name', 'stt_key_value', 'stt_key_validate',
        'stt_section', 'stt_label', 'stt_is_hidden', 'stt_html_attrs'
    ];

    const ATTR_ID = 'stt_id';
    const ATTR_KEY_NAME = 'stt_key_name';
    const ATTR_KEY_VALUE = 'stt_key_value';
    const ATTR_KEY_VALIDATE = 'stt_key_validate';
    const ATTR_SECTION = 'section';
    const ATTR_LABEL = 'stt_label';
    const ATTR_IS_HIDDEN = 'stt_is_hidden';
    const ATTR_HTML_ATTRS = 'stt_html_attrs';

    /**
     * @return array
     */
    public static function getTwilioSettings($arrayResponse = true)
    {
        $result = [];

        $model = Settings::where(function($query) {
            /** @var $query Builder */
            $query->where(static::ATTR_KEY_NAME, BT::VOICE_ACCOUNT_SID)
                ->orWhere(static::ATTR_KEY_NAME, BT::VOICE_AUTH_TOKEN)
                ->orWhere(static::ATTR_KEY_NAME, BT::VOICE_PHONE_NUMBERS)
                ->orWhere(static::ATTR_KEY_NAME, BT::SMS_ACCOUNT_SID)
                ->orWhere(static::ATTR_KEY_NAME, BT::SMS_AUTH_TOKEN)
                ->orWhere(static::ATTR_KEY_NAME, BT::SMS_MESSAGING_SERVICE_SID)
                ->orWhere(static::ATTR_KEY_NAME, BT::SMS_TWILIO_NUMBER)
                ->orWhere(static::ATTR_KEY_NAME, 'messenger');
        })->whereNotNull(static::ATTR_KEY_VALUE);

        if ($model->count() > 0) {
            $result = $model->get()->keyBy(static::ATTR_KEY_NAME);
            if ($arrayResponse) {
                $result = $result->toArray();
            }
        }

        return $result;
    }
}