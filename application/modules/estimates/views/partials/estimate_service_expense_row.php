<div class="row m-b-sm">
    <div class="col-md-8 col-xs-5 form-group m-m pull-left m-b-none">
        <input type="text" class="form-control expenseTitle" placeholder="Expense Details" data-service_id="<?php echo isset($expense) && $expense ? $expense->ese_estimate_service_id : ''; ?>" value="<?php echo isset($expense) && $expense ? $expense->ese_title : 1; ?>"
            <?php if(isset($expense) && $expense) : ?>name="expenses[<?php echo $expense->ese_estimate_service_id; ?>][<?php echo $key; ?>][title]"
            <?php endif; ?>>
    </div>
    <div class="col-md-1 col-xs-5 form-group m-m pull-left m-b-none text-center">
        <input type="text" class="form-control expenseAmount currency text-center" placeholder="Amount" data-service_id="<?php echo isset($expense) && $expense ? $expense->ese_estimate_service_id : ''; ?>" value="<?php echo isset($expense) && $expense ? $expense->ese_price : 1; ?>"
            <?php if(isset($expense) && $expense) : ?>name="expenses[<?php echo $expense->ese_estimate_service_id; ?>][<?php echo $key; ?>][amount]"
            <?php endif; ?>>
    </div>
    <div class="col-md-1 col-xs-1 form-group m-m pull-left m-b-none text-center">
        <a href="#" class="btn btn-xs btn-danger removeExpense" style="margin-top: 6px;">
            <i class="fa fa-trash-o"></i>
        </a>
    </div>
</div>
