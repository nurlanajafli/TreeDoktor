<?php
namespace application\modules\client_calls\models;
use application\core\Database\EloquentModel;
use application\modules\clients\models\Client;
use application\modules\user\models\User;

class ClientsCall extends EloquentModel
{
    /**
     * Client table primary key name
     * @var string
     */
    protected $primaryKey = 'call_id';
    /**
     * Table  name
     * @var string
     */
    protected $table = 'clients_calls';

    protected $appends = ['call_date_view'];
    function client(){
        return $this->hasOne(Client::class, 'client_id', 'call_client_id');
    }

    function user(){
        return $this->hasOne(User::class, 'id', 'call_user_id');
    }

    function scopeFilter($query, $phones){
        foreach ($phones as $key => $phone){
            if(!$key)
                $query->where('call_to', 'like', '%' . $phone);
            else
                $query->orWhere('call_to', 'like', '%' . $phone);

            $query->orWhere('call_from', 'like', '%' . $phone);
        }

        return $query;
    }

    function getCallDateViewAttribute(){
        if(!isset($this->attributes['call_date']))
            return '';

        return getDateTimeWithDate($this->attributes['call_date'],'Y-m-d H:i:s', true);
    }

    /**
     * Get client calls for notes
     *
     * @param array $numbers
     * @return mixed
     */
    public static function getClientNotesCalls(array $numbers) {
        return ClientsCall::filter($numbers)
            ->with(['client', 'user'])
            ->limit(config_item('per_page_notes'))
            ->orderBy('call_date', 'desc')
            ->get();
    }
}