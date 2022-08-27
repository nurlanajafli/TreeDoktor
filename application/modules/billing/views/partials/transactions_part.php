<header class="panel-heading">
    Transactions
</header>

<table class="table table-striped">
    <thead>
    <tr>
        <th>Name</th>
        <th>Date</th>
        <th>Amount</th>
        <th>Payment method</th>
        <th>Status</th>
    </tr>
    </thead>
    <tbody>
    <?php if (!empty($transactions)): ?>
        <?php foreach ($transactions as $type => $items): ?>
            <?php foreach ($items as $item): ?>
                <?php $subscriptionData = ''; ?>
                <?php if ($type === 'SMS'): ?>
                    <?php $subscriptionData = '<span class="font-semibold">' . ($item->sub_name ?? $item->subscription->name) . '</span> &mdash; ' . $item->count . ' sms'; ?>
                <?php endif; ?>
                <tr>
                    <?php $subscriptionAmount = ($item->sub_amount ?? $item->subscription->amount); ?>
                    <td><?php echo "<small>$type:</small> $subscriptionData"; ?></td>
                    <td><?php echo getDateTimeWithDate(($item->lastPayment->created_at ?? $item->created_at), 'Y-m-d H:i:s', true); ?></td>
                    <td class="currency"><?php echo $subscriptionAmount ?? null; ?></td>
                    <td>
                        <?php echo ($item->lastPayment->payment_transaction->payment_transaction_card ?? '-'); ?> /
                        <?php echo ($item->lastPayment->payment_transaction->payment_transaction_card_num ?? '-'); ?>
                    </td>
                    <td>
                        <?php $paymentTitle = $subscriptionAmount > 0
                            ? ($item->lastPayment->payment_transaction->payment_transaction_message ?? $item->error_info) ?? 'No payment information'
                            : 'Gift'; ?>
                        <?php $paymentIcon = $subscriptionAmount > 0
                            ? (($item->lastPayment->payment_transaction->payment_transaction_approved ?? false) ? 'check text-success' : 'times text-danger')
                            : 'check text-success'; ?>
                        <i title="<?php echo $paymentTitle; ?>"
                           class="fa fa-<?php echo $paymentIcon; ?>"></i>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endforeach; ?>
    <?php else: ?>
        <tr>
            <td colspan="5">No records found</td>
        </tr>
    <?php endif; ?>
    </tbody>
</table>
