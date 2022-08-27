<?php

namespace application\modules\invoices\models;

use application\modules\invoices\models\Invoice;
use application\core\Database\EloquentModel;
use DB;

class InvoiceStatus extends EloquentModel
{
    const ATTR_ID = 'invoice_status_id';
    const ATTR_NAME = 'invoice_status_name';
    const ATTR_ACTIVE = 'invoice_status_active';
    const ATTR_DEFAULT = 'default';
    const ATTR_IS_HOLD_BACKS = 'is_hold_backs';
    const ATTR_IS_SENT = 'is_sent';
    const ATTR_IS_OVERDUE = 'is_overdue';
    const ATTR_COMPLETED  = 'completed';
    const ATTR_PROTECTED = 'protected';
    const ATTR_PRIORITY = 'priority';
    const ATTR_IS_OVERPAID = 'is_overpaid';

    /**
     * Invoice Status table primary key name
     * @var string
     */
    protected $primaryKey = 'invoice_status_id';

    /**
     * Table  name
     * @var string
     */
    protected $table = 'invoice_statuses';

    /**
     * @return \application\models\Relations\HasManySyncable
     */
    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'in_status', 'invoice_status_id');
    }

    /**
     * @param $id
     * @return bool
     */
    public static function isStatusPaid($id)
    {
        $status = InvoiceStatus::select(static::ATTR_COMPLETED)->find($id);
        return (bool) $status->getAttribute(static::ATTR_COMPLETED);
    }

    /**
     * @param $query
     * @return mixed
     */
    function scopeApiFields($query)
    {
        return $query->select([
            InvoiceStatus::tableName() . '.' . InvoiceStatus::ATTR_ID,
            InvoiceStatus::tableName() . '.' . InvoiceStatus::ATTR_NAME,
        ]);
    }
}