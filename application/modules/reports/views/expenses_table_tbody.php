<?php if(isset($expenses) && !empty($expenses)) : ?>
    <?php foreach($expenses as $expense) : ?>
        <?php if($expense['expense_name'] == 'Payroll') : ?>
            <?php if(!$expense_id) : ?>
                <tr>
                    <td class="text-nowrap"><?php echo getDateTimeWithTimestamp($expense['expense_date']) ?></td>
                    <td><?php echo $expense['expense_name']?></td>
                    <td> — </td>
                    <td> — </td>
                    <td class="text-nowrap"><?php echo getDateTimeWithTimestamp($expense['expense_create_date']) ?></td>
                    <td><?php echo money($expense['expense_amount']); ?></td>
                    <td><?php echo money($expense['expense_hst_amount']); ?></td>
                    <td><?php echo money($expense['sum']); ?></td>
                    <td> — </td>
                    <td> — </td>
                </tr>
            <?php endif; ?>
        <?php else : ?>
            <?php if (isset($expense['expense_id']) ||!isset($expense_id) || (isset($expense_id) && $expense_id != '0')) : ?>
                <tr id="expenseRow-<?php echo $expense['expense_id']; ?>">
                    <td><?php echo getDateTimeWithTimestamp($expense['expense_date']) ?></td>
                    <td><?php echo $expense['expense_name']; ?></td>
                    <td>
                        <strong>Code:</strong> <?php echo $expense['item_code'] ? $expense['item_code'] : '—'; ?><br>
                        <strong>Name:</strong> <?php echo $expense['item_name'] ? $expense['item_name'] : '—'; ?>
                    </td>
                    <td><?php echo $expense['emp_name'] ? $expense['emp_name'] : '—'; ?></td>
                    <td><?php echo $expense['firstname'] . ' ' . $expense['lastname'] . '<br>' . getDateTimeWithTimestamp($expense['expense_create_date']); ?></td>
                    <td><?php echo money(round($expense['expense_amount'], 2)) . ' ';?><br>
                        <strong><?php echo $expense['expense_payment']; ?></strong>
                    </td>
                    <td><?php echo money(round($expense['expense_hst_amount'], 2)); ?></td>
                    <td><?php echo money(round($expense['expense_hst_amount'] + $expense['expense_amount'], 2)); ?></td>
                    <td><?php echo $expense['expense_description'] ? $expense['expense_description'] : '—'; ?></td>
                    <td>
                        <?php if(isset($expense['expense_id'])) : ?>
                            <a class="btn btn-xs btn-default editExpenseForm" data-expense_id="<?php echo $expense['expense_id']; ?>">
                                <i class="fa fa-pencil"></i>
                            </a>
                        <?php endif; ?>
                        <?php if(isset($expense['expense_file']) && is_file('uploads/expenses_files/' . $expense['expense_file'])) : ?>
                            <a href="<?php echo base_url('uploads/expenses_files/' . $expense['expense_file']); ?>" target="_blank"
                               class="btn btn-xs btn-success">
                                <i class="fa fa-file"></i>
                            </a>
                        <?php endif; ?>
                        <?php if(isset($expense['expense_id'])) : ?>
                            <a href="#" data-expense_id="<?php echo $expense['expense_id']; ?>" class="btn btn-xs btn-danger deleteExp">
                                <i class="fa fa-trash-o"></i>
                            </a>
                        <?php endif;?>
                    </td>
                </tr>
            <?php endif; ?>
        <?php endif; ?>
    <?php endforeach; ?>
<?php else : ?>
    <tr>
        <td colspan="8" style="color:#FF0000;">No record found</td>
    </tr>
<?php endif; ?>
