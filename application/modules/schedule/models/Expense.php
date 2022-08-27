<?php


namespace application\modules\schedule\models;
use Illuminate\Support\Facades\Auth;


use application\core\Database\EloquentModel;
use DB;
class Expense extends EloquentModel
{
    /**
     * @var string
     */
    protected $table = 'expenses';

    protected $fillable = [
        'expense_type_id',
        'expense_item_id',
        'expense_event_id',
        'expense_employee_id',
        'expense_user_id',
        'expense_is_extra',
        'expense_team_id',
        'expense_amount',

        'expense_hst_amount',
        'expense_date',
        'expense_description',

        'expense_created_by',
        'expense_create_date',
        'expense_file',
        'expense_payment',
        'expense_tax'
    ];

    const DEFAULT_PAYMENT_TYPE = 'Cash';
    /**
     * @var string
     */
    protected $primaryKey = 'expense_id';

    protected $appends = ['expense_date_ymd', 'expense_amount_with_tax'];

    function type(){
        return $this->hasOne(ExpenseType::class, 'expense_type_id', 'expense_type_id');
    }

    function scopeExtra($query){
        return $query->where('expense_is_extra', '=', 1);
    }

    function scopeNoExtra($query){
        return $query->where('expense_is_extra', '=', 0);
    }

    function scopeSumAmount($query){
        return $query->select(DB::raw("SUM(expense_amount) as total"));
    }

    function scopeTimesRange($query, $from, $to){
        return $query->where('expense_date', '>=', $from)->where('expense_date', '<=', $to);
    }

    function scopeUser($query, $user_id){
        return $query->where('expense_user_id', '=', $user_id);
    }

    function getExpenseAmountWithTaxAttribute()
    {
        return ($this->attributes['expense_amount']+$this->attributes['expense_hst_amount']);
    }

    public function getExpenseDateYmdAttribute()
    {
        if(isset($this->attribute['expense_date']) && $this->attribute['expense_date'])
            return date("Y-m-d", $this->attribute['expense_date']);

        return '';
    }
/*
    public function setExpenseHstAmountAttribute($value){ //not working
        $hst_amount = floatval($this->attribute['expense_amount']) * (config_item('tax_perc') / 100);
        $this->attributes['expense_hst_amount'] = ($value)?$value : $hst_amount;
    }
*/
    public function setExpenseCreatedByAttribute($value){
        $this->attributes['expense_created_by'] = ($value) ? $value : request()->user()->id;
    }

    public function setExpenseCreateDateAttribute($value){
        $this->attributes['expense_create_date'] = ($value) ? $value : time();
    }

    public function setExpensePaymentAttribute($value){
        $this->attributes['expense_payment'] = ($value) ? $value : DEFAULT_PAYMENT_TYPE;
    }

    public function setExpenseTaxAttribute($value){
        $this->attributes['expense_tax'] = ($value) ? $value : json_encode(getDefaultTax());
    }
}
