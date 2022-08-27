<?php
require_once('QBBase.php');
use QuickBooksOnline\API\QueryFilter\QueryMessage;

class QBClientActions extends QBBase
{
    protected $module = 'Customer';

    public function getCustomerParentQbId(int $customerQbId){
        $customer = null;
        $parentId = null;
        $oneQuery = new QueryMessage();
        $oneQuery->sql = "SELECT";
        $oneQuery->entity = "Customer";
        $oneQuery->whereClause = ["Id = '" . $customerQbId . "'"];
        $result = $this->customQuery($oneQuery);
        if($result == 'refresh')
            return $result;
        elseif(is_array($result) && !empty($result)){
            $customer = $result[0];
            $parentId = $customer->Id;
            if(!empty($customer->ParentRef)) {
                $parentId = $this->getCustomerParentQbId($customer->ParentRef);
            }
        }
        return $parentId;
    }


}