<?php

namespace application\modules\clients\models;

use application\core\Database\EloquentModel;
use application\modules\dashboard\models\traits\FullTextSearch;
use DB;

class ClientsContact extends EloquentModel
{
    use FullTextSearch;

    const ATTR_CC_ID = 'cc_id';
    const ATTR_CC_CLIENT_ID = 'cc_client_id';
    const ATTR_CC_TITLE = 'cc_title';
    const ATTR_CC_NAME = 'cc_name';
    const ATTR_CC_PHONE = 'cc_phone';
    const ATTR_CC_PHONE_CLEAN = 'cc_phone_clean';
    const ATTR_CC_EMAIL = 'cc_email';
    const ATTR_CC_EMAIL_CHECK = 'cc_email_check';
    const ATTR_CC_EMAIL_MANUAL_APPROVE = 'cc_email_manual_approve';
    const ATTR_CC_PRINT = 'cc_print';

    /**
     * API application fields
     * @var array
     */
    const API_FIELDS = [
        'cc_phone',
    ];

    /**
     * API application fields for get
     * @var array
     */
    const API_GET_FIELDS = [
        'clients_contacts.cc_phone',
        'clients_contacts.cc_email'
    ];

    protected $primaryKey = 'cc_id';

    protected $fillable = [
        'cc_title',
        'cc_name',
        'cc_email',
        'cc_client_id',
        'cc_phone',
        'cc_phone_clean',
        'cc_email_check',
        'cc_print'
    ];
    /**
     * The columns of the full text index
     */
    protected $searchable = [
        'cc_name', 'cc_email', 'cc_phone'
    ];

    protected $appends = ['cc_phone_view'];

    function globalSearchQuery($query_string){
        $columns = implode(',',$this->searchable);
        $search = $this->fullTextWildcards($query_string);
        
        return self::search($query_string)
        ->select([
            "client_address as item_address", "client_name as item_name", "cc_phone as item_phone", "cc_name as item_cc_name", "cc_email as item_email", DB::raw("CONCAT(NULL) as item_status"), DB::raw("CONCAT(client_date_created, ' ', '00:00:00') as item_date_created"), DB::raw("CONCAT('clients') as item_module_name"), DB::raw("CONCAT('details') as item_action_name"), "clients.client_id as item_id", "clients.client_id as item_no", "client_name as item_title", DB::raw("CONCAT('1') as item_position"), DB::raw("CONCAT(NULL) as total"),
            DB::raw("MATCH ({$columns}) AGAINST ('".$search."' IN BOOLEAN MODE) AS relevance_score")
        ])
        ->join('clients', ['cc_client_id' => 'client_id'])
        ->leftJoin('leads', ['clients.client_id' => 'leads.client_id'])
        ->leftJoin('estimates', ['clients.client_id' => 'estimates.client_id'])
        ->permissions()
        ->groupBy('clients.client_id');
    }

    public function getCcPhoneViewAttribute(){
        return isset($this->attributes['cc_phone']) ? numberTo($this->attributes['cc_phone']) : '';
    }

    function getSearchable()
    {
        return $this->searchable;
    }

    function scopePermissions($query) {
        $user = request()->user();

        if(!isset($user) || is_null($user)) {
            return $query;
        }

        if(is_cl_permission_none()) {
            $query->where('estimates.user_id', -1);
        } elseif (is_cl_permission_owner()) {
            $query->where(function($q) use ($user) {
                $q->whereClientMaker($user->id)
                    ->orWhere('leads.lead_author_id', $user->id)
                    ->orWhere('leads.lead_estimator', $user->id)
                    ->orWhere('estimates.user_id', $user->id);
            });
        }

        return $query;
    }

    /**
     * Get client contact clean phones
     *
     * @param int $clientId
     * @return array
     */
    public static function getClientContactsCleanPhones(int $clientId): array
    {
        return ClientsContact::select('cc_phone_clean')
            ->where('cc_client_id', '=', $clientId)
            ->whereNotNull('cc_phone_clean')
            ->get()
            ->pluck('cc_phone_clean')
            ->toArray();
    }
}
