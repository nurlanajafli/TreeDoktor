<?php

namespace application\modules\payments\models;
use application\core\Database\EloquentModel;
use application\modules\estimates\models\Estimate;

class PaymentAccount extends EloquentModel
{
    const ATTR_PAYMENT_ACCOUNT_ID = 'payment_account_id';
    const ATTR_PAYMENT_ACCOUNT_NAME = 'payment_account_name';

    /**
     * @var string
     */
    protected $table = 'payment_account';

    /**
     * @var string
     */
    protected $primaryKey = 'payment_account_id';

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function estimates()
    {
        return $this->hasOne(Estimate::class, 'estimate_id', 'estimate_id');
    }

}
