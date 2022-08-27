<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="Margin:0;padding:0;min-width:100%;background-color:#ffffff;">
<center class="wrapper" style="width:100%;table-layout:fixed;-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%;font-family:Arial,Helvetica,Verdana,sans-serif;">
    <div class="webkit" style="max-width:600px;padding-bottom: 60px;">
        <table border="0" width="600" cellpadding="0" cellspacing="0">
            <tbody>
            <tr>
                <td colspan="2" align="left" style="border-collapse:collapse;padding:25px 15px 5px 15px;font-size: 18px;color:#333333; font-weight: bold;">
                    Free subscription changes notification
                </td>
            </tr>
            <tr>
                <td align="left" width="100" style="border-collapse:collapse;padding:5px 5px 5px 15px;font-size: 16px;color:#333333;">
                    Company name:
                </td>
                <td align="left" style="border-collapse:collapse;padding:5px 15px 5px 5px;font-size: 16px;color:#333333;">
                    <?php echo $company; ?>
                </td>
            </tr>
            <tr>
                <td align="left" width="100" style="border-collapse:collapse;padding:5px 5px 5px 15px;font-size: 16px;color:#333333;">
                    Domain:
                </td>
                <td align="left" style="border-collapse:collapse;padding:5px 15px 5px 5px;font-size: 16px;color:#333333;">
                    <?php echo base_url(); ?>
                </td>
            </tr>
            <tr>
                <td colspan="2" align="left" style="border-collapse:collapse;padding:15px;font-size: 16px;color:#333333;">
                    <?php if (!empty($createdSubscription) || !empty($beforeUpdate)): ?>
                        <?php echo !empty($createdSubscription) && $createdSubscription ? 'Created ' : 'Updated '; ?>free subscription
                    <?php endif; ?>
                    <?php if (!empty($order)): ?>
                        <br>Created free order
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <td colspan="2" style="border-collapse:collapse;padding:0 15px;font-size: 16px;color:#333333; font-weight: bold">
                    <hr>
                </td>
            </tr>
            <tr>
                <td colspan="2" style="border-collapse:collapse;padding:5px 15px;font-size: 16px;color:#333333; font-weight: bold">
                    Subscription
                </td>
            </tr>
            <tr>
                <td colspan="2" style="border-collapse:collapse;padding:5px 15px;font-size: 14px;color:#333333;">
                    <table>
                        <thead>
                            <tr>
                                <th style="background: #bbbbbd">Field</th>
                                <?php if (!empty($beforeUpdate)): ?>
                                    <th style="background: #bbbbbd">Previous value</th>
                                <?php endif; ?>
                                <th style="background: #bbbbbd">Current value</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (!empty($subscription)): ?>
                            <?php $i = 0; ?>
                            <?php foreach ($subscription as $key => $val): ?>
                                <?php if ($key !== 'id'): ?>
                                    <tr>
                                        <td width="120" style="padding: 5px;background: <?php echo $i%2 ? '#efefef' : '#fff'; ?>;">
                                            <?php echo $key; ?>
                                        </td>
                                        <?php if (!empty($beforeUpdate)): ?>
                                            <td width="190" style="padding: 5px;background: <?php echo $i%2 ? '#efefef' : '#fff'; ?>;">
                                                <?php echo $beforeUpdate[$key] ?: '&mdash;'; ?>
                                            </td>
                                        <?php endif; ?>
                                        <td width="190" style="padding: 5px;background: <?php echo $i%2 ? '#efefef' : '#fff'; ?>;color: #<?php echo !empty($beforeUpdate) && $val !== $beforeUpdate[$key] ? 'c82333' : '333333'; ?>;">
                                            <?php echo $val ?: '&mdash;'; ?>
                                        </td>
                                    </tr>
                                    <?php $i++; ?>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </td>
            </tr>
            <?php if (!empty($order)): ?>
                <tr>
                    <td colspan="2" style="border-collapse:collapse;padding:0 15px;font-size: 16px;color:#333333; font-weight: bold">
                        <hr>
                    </td>
                </tr>
                <tr>
                    <td colspan="2" style="border-collapse:collapse;padding:5px 15px;font-size: 16px;color:#333333; font-weight: bold">
                        Created order
                    </td>
                </tr>
                <tr>
                    <td colspan="2" style="border-collapse:collapse;padding:5px 15px;font-size: 14px;color:#333333;">
                        <table>
                            <thead>
                                <tr>
                                    <th style="background: #bbbbbd">Field</th>
                                    <th style="background: #bbbbbd">Value</th>
                                </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td width="120" style="padding: 5px;background: #fff;">
                                    Name
                                </td>
                                <td width="190" style="padding: 5px;background: #fff;">
                                    <?php echo $order['sub_name']; ?>
                                </td>
                            </tr>
                            <tr>
                                <td width="120" style="padding: 5px;background: #efefef;">
                                    Number of SMS
                                </td>
                                <td width="190" style="padding: 5px;background: #efefef;">
                                    <?php echo $order['count']; ?>
                                </td>
                            </tr>
                            <tr>
                                <td width="120" style="padding: 5px;background: #fff;">
                                    Active from
                                </td>
                                <td width="190" style="padding: 5px;background: #fff;">
                                    <?php echo $order['from']; ?>
                                </td>
                            </tr>
                            <tr>
                                <td width="120" style="padding: 5px;background: #efefef;">
                                    Active to
                                </td>
                                <td width="190" style="padding: 5px;background: #efefef;">
                                    <?php echo $order['to']; ?>
                                </td>
                            </tr>
                            <tr>
                                <td width="120" style="padding: 5px;background: #fff;">
                                    Price
                                </td>
                                <td width="190" style="padding: 5px;background: #fff;">
                                    <?php echo $order['sub_amount']; ?>
                                </td>
                            </tr>
                            <tr>
                                <td width="120" style="padding: 5px;background: #efefef;">
                                    Created
                                </td>
                                <td width="190" style="padding: 5px;background: #efefef;">
                                    <?php echo $order['created_at']; ?>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</center>
</body>
</html>
