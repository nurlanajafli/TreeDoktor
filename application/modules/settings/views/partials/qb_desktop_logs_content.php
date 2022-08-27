<table class="table" style="overflow-y: scroll; max-height: 600px;  display: block; margin-bottom: 0px">
    <thead>
    <tr>
        <th scope="col">Clients</th>
        <th scope="col">Invoices</th>
        <th scope="col">Payments</th>
    </tr>
    </thead>
    <tbody>
    <?php if (!empty($clients)): ?>
        <?php foreach ($clients as $client): ?>
            <tr>
                <td rowspan="<?= count($client['invoices']) > 1 ? count($client['invoices']) : 1 ?>"><?= $client['client_name'] ?></td>
                <?php if (!empty($client['invoices'])): ?>
                    <td>
                        <?= $client['invoices'][0]['invoice_no'] ?>
                    </td>
                    <?php if (!empty($client['invoices'][0]['payments'])): ?>

                        <?php if (count($client['invoices'][0]['payments']) > 1): ?>
                            <td style="padding: 0px">
                                <table style="border: none">
                                    <tbody>
                                    <?php foreach ($client['invoices'][0]['payments'] as $payment) : ?>
                                        <tr>
                                            <td style="border-top: none;"><?= money($payment['payment_amount']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </td>
                        <?php else: ?>
                            <td>
                                <?= money($client['invoices'][0]['payments'][0]['payment_amount']) ?>
                            </td>
                        <?php endif; ?>
                    <?php else: ?>
                        <td></td>
                    <?php endif; ?>
                <?php else: ?>
                    <td></td>
                    <td></td>
                <?php endif ?>
            </tr>
            <?php if (!empty($client['invoices']) && count($client['invoices']) > 1): ?>
                <?php foreach ($client['invoices'] as $key => $invoice): if ($key == 0) continue; ?>
                    <tr>
                        <td>
                            <?= $invoice['invoice_no'] ?>
                        </td>
                        <?php if (!empty($invoice['payments'])): ?>
                            <?php if (count($invoice['payments']) > 1): ?>
                                <td style="padding: 0px">
                                    <table style="border: none">
                                        <tbody>
                                        <?php foreach ($invoice['payments'] as $payment) : ?>
                                            <tr>
                                                <td style="border-top: none;"><?= money($payment['payment_amount']) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </td>
                            <?php else: ?>
                                <td>
                                    <?= money($invoice['payments'][0]['payment_amount']) ?>
                                </td>
                            <?php endif; ?>
                        <?php else: ?>
                            <td></td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        <?php endforeach; ?>
    <?php endif; ?>

    <?php if (!empty($invoices)): ?>
        <?php foreach ($invoices as $invoice): ?>
            <tr>
                <td></td>
                <td>
                    <?= $invoice['invoice_no'] ?>
                </td>
                <?php if (!empty($invoice['payments'])): ?>
                    <?php if (count($invoice['payments']) > 1): ?>
                        <td style="padding: 0px">
                            <table style="border: none">
                                <tbody>
                                <?php foreach ($invoice['payments'] as $payment) : ?>
                                    <tr>
                                        <td style="border-top: none;"><?= money($payment['payment_amount']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </td>
                    <?php else: ?>
                        <td>
                            <?= money($invoice['payments'][0]['payment_amount']) ?>
                        </td>
                    <?php endif; ?>
                <?php else: ?>
                    <td></td>
                <?php endif; ?>
            </tr>
        <?php endforeach; ?>
    <?php endif; ?>

    <?php if (!empty($payments)): ?>
<!--        --><?php //if (count($payments) > 1): ?>
            <?php foreach ($payments as $payment) : ?>
                <tr>
                    <td></td>
                    <td></td>
                    <td><?= money($payment['payment_amount']) ?><?php if (!empty($payment['invoice'])) : echo ' ( ' . $payment['invoice']['invoice_no'] . ' )'; endif; ?> </td>
                </tr>
            <?php endforeach; ?>
<!--        --><?php //else: ?>
<!--            <td></td>-->
<!--            <td></td>-->
<!--            <td>-->
<!--                --><?php //echo money($payments[0]['payment_amount']);
//                if (!empty($payments[0]['invoice'])) : echo ' ( ' . $payments[0]['invoice']['invoice_no'] . ' )';
//                endif; ?>
<!--            </td>-->
<!--        --><?php //endif; ?>
    <?php endif; ?>
    </tbody>
</table>