<?php


namespace application\modules\clients\models;
use application\core\Database\EloquentModel;
use application\modules\emails\models\Email;
use application\modules\user\models\User;
use Illuminate\Database\QueryException;

class ClientNote extends EloquentModel
{
    /**
     * ClientNote table primary key name
     * @var string
     */
    protected $primaryKey = 'client_note_id';
    /**
     * Table  name
     * @var string
     */
    protected $table = 'client_notes';

    protected $fillable = [
        'client_id',
        'client_note_date',
        'client_note',
        'client_note_type',
        'author',
        'robot',
        'client_note_top',
        'lead_id'
    ];

    protected $appends = ['client_note_date_view'];
    const CLIENT_NOTES_TYPES = [
        'info', 'attachment', 'system', 'email'
    ];

    const SMS_NOTES_TYPE = 'sms';
    const CLIENT_CALLS_NOTES_TYPE = 'calls';

    function getClientNoteDateViewAttribute(){
        if(!isset($this->attributes['client_note_date']))
            return '';

        return getDateTimeWithDate($this->attributes['client_note_date'],'Y-m-d H:i:s', true);
    }

    function user(){
        return $this->hasOne(User::class, 'id', 'author');
    }

    function emails() {
        return $this->morphToMany(Email::class, 'emailable');
    }

    /**
     * Create new note
     *
     * @param array $note_data
     * @return false|int
     */
    public static function createNote(array $note_data) {
        if (!is_array($note_data) || !sizeof($note_data)) {
            return false;
        }

        try {
            $note = ClientNote::create($note_data);
        }
        catch (QueryException $e) {
            return false;
        }

        return $note->client_note_id;
    }

    /**
     * Get client notes
     *
     * @param int $client_id
     * @param int $lead_id
     * @param bool $client_only
     * @param int|string $client_note_type
     * @param int|null $page
     * @return array
     */
    public static function getClientNotes(int $client_id, int $lead_id, bool $client_only, $client_note_type, ?int $page = 1): array
    {
        $conditions = [
            'client_id' =>  $client_id,
            'lead_id'   =>  ($client_only) ? 0 : $lead_id,
            'client_note_type' => $client_note_type
        ];

        $conditions = array_filter($conditions, function ($value) {
            return $value;
        });

        return ClientNote::with([
                'user:id,picture,firstname,lastname,rate',
                'emails' => function ($query) {
                    $query->select(['email_id', 'email_from', 'email_to', 'email_subject', 'email_status', 'email_error', 'email_updated_at'])
                        ->with(['emailLogs' => function ($q) {
                            $q->orderBy('email_log_created_at', 'desc');
                        }]);
                }
            ])
            ->where($conditions)
            ->orderBy('client_note_top', 'DESC')
            ->orderBy('client_note_date', 'DESC')
            ->orderBy('client_note_id', 'DESC')
            ->paginate(config_item('per_page_notes'), '*', '', $page)
            ->toArray();
    }
}