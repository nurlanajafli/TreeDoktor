<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin:0;padding:0;min-width:100%;background-color:#ffffff;">
<center class="wrapper" style="width:100%;table-layout:fixed;-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%;">
    <div class="webkit" style="max-width:600px;">
        <table border="0" width="600" cellpadding="0" cellspacing="0">
            <tbody><tr>
                <td align="left" style="border-collapse:collapse;padding:25px;background-color: #<?php echo isset($payment) && $payment ? '5d863b' : 'fb6b5b'; ?>;">
                    <table style="font-family:Arial,Helvetica,Verdana,sans-serif;font-size:13px;color:#333333;border-spacing:0"><tbody><tr>
                        <td style="border-collapse:collapse">
                            <table style="font-family:Arial,Helvetica,Verdana,sans-serif;font-size:13px;color: #FFF;border-spacing:0;"><tbody><tr>
                                <td style="padding-right: 25px;border-collapse:collapse;">
                                    <span style="font-size: 50px;border: 4px solid #fff;border-radius: 50px;"><span style="color: #FFF;margin: 8px;">&#<?php echo isset($payment) && $payment ? '10003' : '10008'; ?>;</span></span>
                                </td>
                                <td style="border-collapse:collapse">
                                    <h1 style="font-family:Arial,Verdana,sans-serif;font-size:20px;margin:0">Your payment of <strong><?php echo isset($amount) ? money(floatval($amount)) : 0; ?></strong> to <?php echo config_item('arbostar_company_name'); ?> has been <?php echo isset($payment) && $payment ? 'sent' : 'declined'; ?>.</h1>
                                </td>
                            </tr></tbody></table>
                        </td>
                    </tr></tbody></table>
                </td>
            </tr>
            <tr>
                <td align="left" bgcolor="#ffffff" style="border-collapse:collapse;padding:25px;border-top:1px solid #e8e8ea">
                    <?php if (isset($payment) && $payment): ?>
                        <table style="font-family:Arial,Helvetica,Verdana,sans-serif;font-size:13px;color:#333333;border-spacing:0">
                            <tbody><tr>
                                <td width="480" style="border-collapse:collapse">
                                    <p style="font-size:13px;margin:0;padding-bottom: 15px;">Thank you for your payment for <strong><?php echo $entity_description . ' ' . $entity_item_name; ?></strong>.</p>
                                    <p style="font-size:13px;margin:0;padding-bottom: 15px;">Transaction details:</p>
                                </td>
                            </tr></tbody>
                        </table>
                    <?php endif; ?>
                    <table style="font-family:Arial,Helvetica,Verdana,sans-serif;font-size:14px;color:#333333;border-spacing:0">
                        <tbody>
                        <?php if (isset($message) && $message): ?>
                            <tr>
                                <td>
                                    <table align="left" width="150">
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
                                <table align="left" width="150">
                                    <tbody><tr><td><strong>Paid to</strong></td></tr></tbody>
                                </table>
                                <table align="left" width="260">
                                    <tbody><tr><td><?php echo config_item('arbostar_company_name'); ?></td></tr></tbody>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <table align="left" width="150">
                                    <tbody><tr><td><strong><?php echo $entity_description; ?></strong></td></tr></tbody>
                                </table>
                                <table align="left" width="260">
                                    <tbody><tr><td><?php echo $entity_item_name; ?></td></tr></tbody>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <table align="left" width="150">
                                    <tbody><tr><td><strong>Payment amount</strong></td></tr></tbody>
                                </table>
                                <table align="left" width="260">
                                    <tbody><tr><td><?php echo isset($amount) ? money(floatval($amount)) : 0; ?></td></tr></tbody>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <table align="left" width="" style="margin-top: 10px; color: #85898c;">
                                    <tbody>
                                    <tr>
                                        <td>
                                            <?php if (isset($card) && isset($card_icon)) : ?>
                                                <img src="<?php echo base_url(); ?>assets/img/cards/<?php echo $card_icon['img']; ?>" height="13" style="margin-right: 6px;">
                                                <span><?php echo $card_icon['title']; ?><?php echo $card; ?></span>
                                            <?php endif; ?>
                                            <span style="margin-left: 30px;"><?php echo $date; ?></span>
                                        </td>
                                    </tr>
                                    <?php if (isset($auth_code) && $auth_code) : ?>
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
                <td style="padding-top:0;padding-bottom:0;padding-right:0;padding-left:0;background-color:#5d863b;background-image:none;background-repeat:repeat;background-position:top left;background-attachment:scroll;height:50px;">
                    <table width="100%" style="border-spacing:0;font-family:AvenirNextLTPro-Regular, Arial, 'Helvetica Neue', Helvetica, sans-serif;color:#474a5d;">
                        <tbody><tr>
                            <td class="inner contents" style="padding-top:10px;padding-bottom:10px;padding-right:30px;padding-left:30px;font-family:AvenirNext-Medium, Arial, 'Helvetica Neue', Helvetica, sans-serif;color:#fff;text-align:right;">
                                <span style="color:#fff;display:inline-block;">
                                    <a style="color:#fff;" href="mailto:<?php echo config_item('arbostar_email'); ?>"
                                       target="_blank"><?php echo config_item('arbostar_email'); ?></a>
                                </span>
                            </td>
                        </tr></tbody>
                    </table>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
</center>
</body>
</html>
