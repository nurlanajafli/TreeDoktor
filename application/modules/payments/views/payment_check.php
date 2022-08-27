<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="Margin:0;padding:0;min-width:100%;background-color:#ffffff;">
<center class="wrapper" style="width:100%;table-layout:fixed;-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%;">
    <div class="webkit" style="max-width:600px;">
        <table border="0" width="600" cellpadding="0" cellspacing="0">
            <tbody>
            <tr>
                <td align="left" style="border-collapse:collapse;padding:25px;background-color: #<?php if (isset($payment) && $payment) : ?>5d863b<?php else : ?>fb6b5b<?php endif; ?>;">
                    <table style="font-family:Arial,Helvetica,Verdana,sans-serif;font-size:13px;color:#333333;border-spacing:0">
                        <tbody><tr>
                            <td style="border-collapse:collapse">
                                <table style="font-family:Arial,Helvetica,Verdana,sans-serif;font-size:13px;color: #FFF;border-spacing:0;">
                                    <tbody><tr>
                                        <td style="padding-right: 25px;border-collapse:collapse;">
                                            <span style="font-size: 50px; border: 4px solid #FFF;border-radius: 45px;"><span style="color: #FFF;margin: 8px;">&#<?php if (isset($payment) && $payment) : ?>10003<?php else : ?>10008<?php endif; ?>;</span></span>
                                        </td>
                                        <td style="border-collapse:collapse">
                                            <h1 style="font-family:Arial,Verdana,sans-serif;font-size:20px;margin:0">
                                                Your payment of <strong><?php echo isset($amount) ? money(floatval($amount)) : 0; ?></strong> to <?php echo $this->config->item('company_name_short'); ?> has been <?php if (isset($payment) && $payment) : ?>sent<?php else : ?>declined<?php endif; ?>.
                                            </h1>
                                        </td>
                                    </tr></tbody>
                                </table>
                            </td>
                        </tr></tbody>
                    </table>
                </td>
            </tr>
            <tr>
                <td align="left" bgcolor="#ffffff" style="border-collapse:collapse;padding:25px;border-top:1px solid #e8e8ea">
                    <?php if (isset($payment) && $payment) : ?>
                        <table style="font-family:Arial,Helvetica,Verdana,sans-serif;font-size:13px;color:#333333;border-spacing:0">
                            <tbody><tr><td width="480" style="border-collapse:collapse">
                                <p style="font-size:13px;margin:0;padding-bottom: 15px;">Thank you for your payment for
                                    <a style="color: #474a5d;"
                                       href="<?php echo config_item('payment_link'); ?>payments/<?php echo isset($invoice) && $invoice ? 'invoice/' . md5($invoice . $client_id) : (isset($workorder) ? 'invoice/' . md5($workorder . $client_id) : 'estimate/' . md5($estimate . $client_id)); ?>"
                                       target="_blank"><strong><?php echo isset($invoice) ? 'Invoice #' . $invoice : (isset($workorder) ? 'Partial Invoice #' . str_replace('-E', '-I', $estimate) : 'Estimate #' . $estimate); ?></strong></a>.
                                </p>
                                <p style="font-size:13px;margin:0;padding-bottom: 15px;">Transaction details:</p>
                            </td></tr></tbody>
                        </table>
                    <?php endif; ?>
                    <table style="font-family:Arial,Helvetica,Verdana,sans-serif;font-size:14px;color:#333333;border-spacing:0">
                        <tbody>
                        <?php if (isset($message) && $message): ?>
                            <tr>
                                <td>
                                    <table align="left" width="130">
                                        <tbody><tr><td><strong>Message</strong></td></tr></tbody>
                                    </table>
                                    <table align="left" width="260">
                                        <tbody><tr><td><?php echo $message; ?></td></tr></tbody>
                                    </table>
                                </td>
                            </tr>
                        <?php endif; ?>
                        <tr>
                            <td>
                                <table align="left" width="130">
                                    <tbody><tr><td><strong>Paid to</strong></td></tr></tbody>
                                </table>
                                <table align="left" width="260">
                                    <tbody><tr><td><?php echo config_item('company_name_long'); ?></td></tr></tbody>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <table align="left" width="130">
                                    <tbody><tr><td><strong><?php echo isset($invoice) ? 'Invoice # ' : (isset($workorder) ? 'Partial Invoice # ' : 'Estimate # '); ?></strong></td></tr></tbody>
                                </table>
                                <table align="left" width="260">
                                    <tbody><tr><td>
                                        <a style="color: #474a5d;"
                                           href="<?php echo config_item('payment_link'); ?>payments/<?php echo isset($invoice) && $invoice ? 'invoice/' . md5($invoice . $client_id) : ((isset($workorder) && $workorder) ? 'invoice/' . md5($workorder . $client_id) : 'estimate/' . md5($estimate . $client_id)); ?>"
                                           target="_blank"><?php echo $invoice ?? $estimate; ?></a>
                                    </td></tr></tbody>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <table align="left" width="130">
                                    <tbody><tr><td><strong>Payment amount</strong></td></tr></tbody>
                                </table>
                                <table align="left" width="260">
                                    <tbody><tr><td><?php echo isset($amount) ? money(floatval($amount)) : 0; ?></td></tr></tbody>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <table align="left" width="130">
                                    <tbody><tr><td><strong>Total</strong></td></tr></tbody>
                                </table>
                                <table align="left" width="260">
                                    <tbody><tr><td><?php echo isset($total) ? money(floatval($total)) : 0; ?></td></tr></tbody>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <table align="left" width="130">
                                    <tbody><tr><td><strong>Balance</strong></td></tr></tbody>
                                </table>
                                <table align="left" width="260">
                                    <tbody><tr><td><b><?php echo isset($due) ? money(floatval($due)) : 0; ?></b></td></tr></tbody>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <table align="left" width="" style="margin-top: 10px; color: #85898c;">
                                    <tbody><tr><td>
                                        <?php if (isset($card) && isset($card_icon)): ?>
                                            <img src="<?php echo base_url(); ?>assets/img/cards/<?php echo $card_icon['img']; ?>" height="13" style="margin-right: 6px;">
                                            <span><?php echo $card_icon['title']; ?><?php echo $card; ?></span>
                                        <?php endif; ?>
                                        <span style="margin-left: 30px;"><?php echo $date; ?></span>
                                    </td></tr>
                                    <?php if (isset($auth_code) && $auth_code): ?>
                                        <tr><td align="right">Auth Code: <?php echo $auth_code; ?></td></tr>
                                    <?php endif; ?>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
            <tr>
                <td class="one-column footer" style="padding:0;background-color:#5d863b;background-image:none;background-repeat:repeat;background-position:top left;background-attachment:scroll;height:50px;">
                    <table width="100%" style="border-spacing:0;font-family:AvenirNext-Medium, Arial, 'Helvetica Neue', Helvetica, sans-serif;font-size:14px;color:#fff;">
                        <tbody>
                        <tr>
                            <td class="inner contents" style="padding:10px 30px;text-align:left;">
                            <span style="color:#fff;padding-right:20px;display:inline-block;">
                                <?php if (config_item('office_address')): ?>
                                    <a style="color:#fff;"
                                       href="https://maps.google.com/?q=<?php echo config_item('office_address') . '+' . config_item('office_city') . '+' . config_item('office_state') . '+' . config_item('office_zip'); ?>"
                                       target="_blank"><?php echo config_item('office_address'); ?>. <?php echo config_item('office_city'); ?> <?php echo config_item('office_zip'); ?></a>
                                <?php endif; ?>
                            </span>
                                <span style="padding-right:20px;display:inline-block;">
                                <?php if (config_item('office_phone')): ?>
                                    <a style="color:#fff;" href="tel:<?php echo config_item('office_phone'); ?>"><?php echo config_item('office_phone_mask'); ?></a>
                                <?php endif; ?>
                            </span>
                                <span style="padding-right:20px;display:inline-block;color:#fff;">
                                <?php if (config_item('account_email_address')): ?>
                                    <a style="color:#fff;" href="mailto:<?php echo config_item('account_email_address'); ?>"
                                       target="_blank"><?php echo $this->config->item('account_email_address'); ?></a>
                                <?php endif; ?>
                            </span>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
</center>
</body>
</html>
