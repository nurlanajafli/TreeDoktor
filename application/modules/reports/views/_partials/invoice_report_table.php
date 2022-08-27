<div class="table-container">
<table class="invoice_table table table-hover text-nowrap table-striped dataTable no-footer display nowrap" width="100%" style="margin-top: 0px !important;">
    <thead class="bg-light">
    <tr>
        <th>INVOICE <br> PAID DATE</th>
        <th>INVOICE <br> NUMBER</th>
        <th>INVOICE <br> CREATED DATE</th>
        <th>INVOICE <br> SENT DATE</th>
        <th>ESTIMATOR / <br> SALESMAN</th>
        <th>REFERENCED BY / <br> LEAD SOURCE</th>
        <th>CUSTOMER NAME</th>
        <th>CUSTOMER <br> PHONE NUMBER</th>
        <th>CUSTOMER <br> EMAIL ADDRESS</th>
        <?php if(!empty($showClasses) && is_array($showClasses)):
                foreach ($showClasses as $class):?>
                    <th><?= $class['class_name'] ?></th>
        <?php endforeach; endif; ?>
        <th>ALL Classes **</th>
        <th>NO Class</th>
        <th>DISCOUNT AMOUNT</th>
    </tr>
    </thead>
    <tbody>
    <?php if(!empty($invoices)):
            foreach ($invoices as $invoice):?>
                <tr>
                    <td><?= getDateTimeWithTimestamp($invoice['invoice_paid_date'])?></td>
                    <td><?= $invoice['invoice_no'] ?></td>
                    <td><?= getDateTimeWithDate($invoice['date_created'], 'Y-m-d');  ?></td>
                    <td><?= getDateTimeWithTimestamp($invoice['invoice_sent_date']) ?></td>
                    <td><?= $invoice['user_firstname'] . ' ' . $invoice['user_lastname'] ?></td>
                    <td><?= $invoice['reference_name'] ?></td>
                    <td><?= $invoice['client_name'] ?></td>
                    <td><?= numberTo($invoice['cc_phone']) ?></td>
                    <td><?= $invoice['cc_email'] ?></td>
                    <?php if(!empty($showClasses) && is_array($showClasses)):
                        foreach ($showClasses as $class): $total = 0;?>
                                <?php if(!empty($invoice['total_class_' . $class['class_id']])){
                                    $total = $invoice['total_class_' . $class['class_id']];
                                } ?>
                            <td><?= money($total) ?></td>
                        <?php endforeach; endif; ?>
                    <td><?= money(!empty($invoice['all_classes']) ? $invoice['all_classes'] : 0) ?></td>
                    <td><?= money(!empty($invoice['no_class']) ? $invoice['no_class'] : 0) ?></td>
                    <td><?= money($invoice['discount_total']) ?></td>
                </tr>
    <?php endforeach; endif; ?>
    </tbody>
</table>
</div>