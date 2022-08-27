<?php

namespace application\modules\dashboard\models;

use application\core\Database\Casts\AppDate;
use application\core\Database\Casts\AppDateTime;
use application\core\Database\EloquentModel;
use application\modules\user\models\User;
use application\modules\clients\models\Client;
use application\modules\clients\models\ClientsContact;
use application\modules\leads\models\Lead;
use application\modules\estimates\models\Estimate;
use application\modules\workorders\models\Workorder;
use application\modules\invoices\models\Invoice;

use DB;
class Search extends EloquentModel
{

    

    /**
     * The columns of the full text index
     */
    /*
    protected $searchable = [
        'first_name',
        'last_name',
        'email'
    ];*/
    

    function global_serach($condition)
    {
        $ClientModel = new Client();
        $ClientsContact = new ClientsContact();
        $LeadModel = new Lead();
        $EstimateModel = new Estimate();
        $WorkorderModel = new Workorder();
        $InvoiceModel = new Invoice();

        $result = \Illuminate\Support\Facades\DB::query()->fromSub(function($query) use ($condition, $ClientModel, $ClientsContact, $LeadModel, $EstimateModel, $WorkorderModel, $InvoiceModel) {
            $query->from($ClientModel->globalSearchQuery($condition), 'c1')
                ->union($ClientsContact->globalSearchQuery($condition))
                ->union($LeadModel->globalSearchQuery($condition))
                ->union($EstimateModel->globalSearchQuery($condition))
                ->union($WorkorderModel->globalSearchQuery($condition))
                ->union($InvoiceModel->globalSearchQuery($condition));
        }, 't1')->groupBy(['item_module_name', 'item_id'])
            ->orderBy('item_position')
            ->orderBy('relevance_score', 'desc')
            ->orderBy('item_no', 'desc')
            ->orderBy('item_name')
            ->orderBy('item_cc_name');

        return $result;
    }

    function global_search_client($condition)
    {
        $ClientModel = new Client();
        $ClientsContact = new ClientsContact();

        $result = \Illuminate\Support\Facades\DB::query()->fromSub(function($query) use ($condition, $ClientModel, $ClientsContact) {
            $query->from($ClientModel->globalSearchQuery($condition), 'c1')
                ->union($ClientsContact->globalSearchQuery($condition));
        }, 't1')->groupBy(['item_module_name', 'item_id'])
            ->orderBy('item_position')
            ->orderBy('relevance_score', 'desc')
            ->orderBy('item_no', 'desc')
            ->orderBy('item_name')
            ->orderBy('item_cc_name');

        return $result;
    }
        /*
            SELECT
    `client_address` AS `item_address`,
    `client_name` AS `item_name`,
    `cc_phone` AS `item_phone`,
    `cc_name` AS `item_cc_name`,
    CONCAT('clients') AS item_module_name,
    CONCAT('details') AS item_action_name,
    `clients`.`client_id` AS `item_id`,
    `clients`.`client_id` AS `item_no`,
    `client_name` AS `item_title`,
    CONCAT('1') AS item_position,
    CONCAT(NULL) AS total,
    MATCH(
        client_name,
        client_address,
        client_city,
        client_country,
        client_state,
        client_zip
    ) AGAINST("+12*York*" IN BOOLEAN MODE) AS relevance_score
FROM
    `clients`
LEFT JOIN `clients_contacts` ON(
        `cc_client_id` = `clients`.`client_id` AND `cc_print` = 1
    )
LEFT JOIN `leads` ON(
        `clients`.`client_id` = `leads`.`client_id` AND `cc_print` = 1
    )
WHERE
    MATCH(
        client_name,
        client_address,
        client_city,
        client_country,
        client_state,
        client_zip
    ) AGAINST("+12*York*" IN BOOLEAN MODE)
    
        */    


            /*

    

        $sql = "SELECT * FROM (";
        
        if($subqueryClients)
            $sql .= "($subqueryClients) UNION ";
        if($subqueryClientsContacts)
            $sql .= "($subqueryClientsContacts) UNION ";
        if($subqueryEstimates)
            $sql .= "($subqueryEstimates) UNION ";
        
        if($subqueryWorkorders)
            $sql .= "($subqueryWorkorders) UNION ";
        
        $sql .= "($subqueryInvoices)";
        //$sql .= "UNION ($subqueryInvoicesTotal)";
        
        //, `score` DESC
        $sql .= ") as items GROUP BY `item_module_name`, `item_id` ORDER BY `item_position` ASC, `item_id` DESC LIMIT 100";

        $result = $this->db->query($sql)->result();
        return $result;*/

}