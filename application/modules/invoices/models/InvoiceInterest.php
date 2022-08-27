<?php

namespace application\modules\invoices\models;

use application\core\Database\EloquentModel;
use DB;

class InvoiceInterest extends EloquentModel
{
    const ATTR_ID = 'id';
    const ATTR_INVOICE_ID = 'invoice_id';
    const ATTR_OVERDUE_DATE = 'overdue_date';
    const ATTR_RATE = 'rate';
    const ATTR_NILL_RATE = 'nill_rate';
    const ATTR_DISCOUNT = 'discount';
    const ATTR_INTERES_COST = 'interes_cost';
    const ATTR_SYNC_QB  = 'sync_qb';

    /**
     * Invoice Status table primary key name
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Table  name
     * @var string
     */
    protected $table = 'invoice_interest';

    /**
     * @var array
     */
    protected $fillable = [
        'invoice_id', 'overdue_date', 'rate',
    ];

    public function invoices()
    {
        return $this->hasOne(Invoice::class, 'invoice_id', 'id');
    }

    public function getInterestData(int $invoice_id)
    {
        $query = InvoiceInterest::where(['invoice_id' => $invoice_id])->with('invoices');
        return $query->orderBy('invoice_interest.overdue_date')->get();
    }
}
