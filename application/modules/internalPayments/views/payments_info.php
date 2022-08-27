<section class="panel panel-default p-n payments-info">
	<header class="panel-heading">Internal payments</header>
    <div class="table-responsive">
        <table class="table table-striped b-t b-light">
            <thead>
            <tr>
                <th>ID</th>
                <th>Pay for</th>
                <th width="200px">Date</th>
                <th>Amount</th>
                <th>File</th>
                <th>Action</th>
            </tr>
            </thead>
            <tbody>
            <!-- Payments -->
            <?php if ($payments): ?>
                <?php foreach ($payments as $row) : ?>
                    <tr class="payments<?php if($row['payment_alarm']): ?> bg-danger<?php endif; ?>"
                        data-id="<?php echo $row['id']; ?>"
                        <?php if($row['payment_alarm']): ?> title="Payment Was Declined"<?php endif; ?>
                    >
                        <td<?php if($row['payment_alarm']): ?> style="background-color: #fb6b5b;"<?php endif; ?>>
                            <?php echo $row['id']; ?>
                        </td>
                        <td<?php if($row['payment_alarm']): ?> style="background-color: #fb6b5b;"<?php endif; ?>>
                            <?php echo $row['paymentable']['name'] ?? 'Unknown payment'; ?>
                        </td>
                        <td<?php if($row['payment_alarm']): ?> style="background-color: #fb6b5b;"<?php endif; ?>>
                            <?php echo getDateTimeWithDate($row['created_at'], 'Y-m-d H:i:s', true); ?>
                        </td>
                        <td<?php if($row['payment_alarm']): ?> style="background-color: #fb6b5b;"<?php endif; ?>>
                            <?php echo money($row['amount']); ?>
                        </td>
                        <td<?php if($row['payment_alarm']): ?> style="background-color: #fb6b5b;"<?php endif; ?>>
                            <?php if ($row['transaction_approved']): ?>
                                <a class="btn btn-success btn-xs pull-left" type="button" target="_blank"
                                   href="<?php echo base_url('uploads/payment_files/internal/' . substr(md5($row['transaction_id']), 0, 3) . '/' . $row['transaction_id'] . '.pdf'); ?>">
                                    <i class="fa fa-picture-o"></i>
                                </a>
                            <?php else: ?>
                                &mdash;
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="#payment_details"
                               data-transaction-id="<?php echo $row['transaction_id']; ?>"
                               role="button" class="btn btn-xs btn-default"
                               data-toggle="modal" title="Transaction details">
                                <i class="fa fa-eye"></i>
                            </a>
                            <?php if (isSystemUser() && floatval($row['amount']) > 0 && $row['transaction_approved']): ?>
                                <a href="#payment_refund"
                                   data-payment-id="<?php echo $row['id']; ?>"
                                   data-amount="<?php echo $row['amount']; ?>"
                                   role="button" class="btn btn-xs btn-danger"
                                   title="Transaction refund. This action will only return the money without affecting the entity for which they paid!"
                                   data-toggle="modal">
                                    <i class="fa fa-level-up"></i>
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>

            <tbody>
        </table>
    </div>
</section>
