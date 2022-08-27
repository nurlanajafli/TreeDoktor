<?php
/**
 * @var EquipmentServiceReport $report
 */

use application\modules\equipment\models\EquipmentServiceReport;
use Illuminate\Support\Carbon;

?>
<html>
<head>
    <style>
        body {
            font-family: sans-serif;
            font-size: 10pt;
        }

        p {
            margin: 0pt;
        }

        table.items {
            border: 0.1mm solid #000000;
        }

        td {
            vertical-align: top;
        }

        .items td {
            border-left: 0.1mm solid #000000;
            border-right: 0.1mm solid #000000;
        }

        table thead td {
            background-color: #EEEEEE;
            text-align: center;
            border: 0.1mm solid #000000;
            font-variant: small-caps;
        }

        .items td.blanktotal {
            background-color: #EEEEEE;
            border: 0.1mm solid #000000;
            background-color: #FFFFFF;
            border: 0mm none #000000;
            border-top: 0.1mm solid #000000;
            border-right: 0.1mm solid #000000;
        }

        .items td.totals {
            text-align: right;
            border: 0.1mm solid #000000;
        }

        .items td.cost {
            text-align: left;
        }
    </style>
</head>
<body>
<?php
$brand_id = default_brand(); ?>
<!--mpdf
<htmlpageheader name="myheader">
<table width="100%"><tr>
<td width="50%" style="color:#0000BB; ">
<span style="font-weight: bold; font-size: 14pt;"><?php
echo brand_name($brand_id); ?></span>
<br />
<?php
echo brand_office_address($brand_id); ?>
<br />
<?php
echo brand_office_city($brand_id); ?>
<br />
<?php
echo brand_office_zip($brand_id); ?>
<br />
<?php
echo brand_office_country($brand_id); ?>
<br />
<span style="font-family:dejavusanscondensed;">&#9742;</span>
<?php
echo brand_phone($brand_id); ?>
</td>
<td width="50%" style="text-align: right;">Service Report No. #<?php
echo $report->service_report_id; ?></td>
</tr></table>
</htmlpageheader>
<htmlpagefooter name="myfooter">
<div style="border-top: 1px solid #000000; font-size: 9pt; text-align: center; padding-top: 3mm; ">
Page {PAGENO} of {nb}
</div>
</htmlpagefooter>
<sethtmlpageheader name="myheader" value="on" show-this-page="1" />
<sethtmlpagefooter name="myfooter" value="on" />
mpdf-->
<br/>
<br/>
<br/>
<br/>
<div style="text-align: right">
    Date: <?php echo Carbon::now()->format(config_item('dateFormat') . (config_item('time') == 12 ? ' h:i:s a' : ' H:i:s')); ?>
</div>
<table width="100%" style="font-family: serif;" cellpadding="10">
    <tr>
        <td width="10%">
            <img width="20%" height="auto" src="<?php echo $report->equipment->eq_photo_url; ?>"/>
        </td>
        <td width="35%" style="border: 0.1mm solid #888888; font-size: 10pt">
            <span style="font-size: 14pt; color: #555555; font-family: sans;">Equipment: <?php echo $report->equipment->eq_name; ?></span>
            <br/><br/>
            SERIAL/VIN: <strong><?php echo $report->equipment->eq_serial; ?></strong><br/>
            LICENSE PLATE: <strong><?php echo $report->equipment->eq_license_plate; ?></strong><br/>
            YEAR: <strong><?php echo $report->equipment->eq_year; ?></strong><br/>
            MAKE: <strong><?php echo $report->equipment->eq_make; ?></strong><br/>
            MODEL: <strong><?php echo $report->equipment->eq_model; ?></strong><br/>
            COLOR: <strong><?php echo $report->equipment->eq_color; ?></strong><br/>
            DESCRIPTION:<br/>
            <i><?php echo $report->equipment->eq_description; ?></i>
        </td>
        <td width="3%">&nbsp;</td>
        <td width="42%" style="border: 0.1mm solid #888888;">
            <span style="font-size: 14pt; color: #555555; font-family: sans;">
                Service Report No: #<?php echo $report->service_report_id; ?> At <?php echo $report->service_report_created_at; ?>
            </span>
            <br/><br/>
            SERVICE: <strong><?php echo $report->service->service_name; ?></strong><br/>
            TYPE: <strong><?php echo $report->service_report_type_str; ?></strong><br/>
            <?php if ($report->service_report_type === EquipmentServiceReport::TYPE_POSTPONED) : ?>
                POSTPONED TO: <strong><?php echo $report->service_report_postponed_to ?></strong><br/>
            <?php else : ?>
                END DATE: <strong><?php echo $report->service_report_end_date ?></strong><br/>
            <?php endif; ?>
            REPORT CREATED:
            <strong><?php echo $report->user->firstname; ?>&nbsp;<?php echo $report->user->lastname; ?></strong><br/>
            NOTE:<br/>
            <i><?php echo $report->service_report_note; ?></i>
        </td>
    </tr>
</table>
<br/>
<strong>Parts:</strong>
<table class="items" width="100%" style="font-size: 9pt; border-collapse: collapse; " cellpadding="8">
    <thead>
    <tr>
        <td width="15%">Name</td>
        <td width="10%">Part No.</td>
        <td width="10%">Seller</td>
        <td width="15%">Purchased Date</td>
        <td width="20%">Description</td>
        <td width="15%">TAX</td>
        <td width="15%">Cost</td>
    </tr>
    </thead>
    <tbody>
    <?php
    $subtotal = 0;
    $tax = 0;
    $total = 0;
    $all = 0;
    ?>
    <?php foreach ($report->parts as $part) : ?>
        <?php
        $subtotal += $part->part_price;
        $total += $part->part_price_with_tax;
        ?>
        <tr>
            <td align="left"><?php echo $part->part_name; ?></td>
            <td align="center"><?php echo $part->part_number; ?></td>
            <td><?php echo $part->part_seller; ?></td>
            <td><?php echo $part->part_purchased_date; ?></td>
            <td><?php echo $part->part_description; ?></td>
            <td><?php echo $part->part_tax_name; ?></td>
            <td class="cost"><?php echo money($part->part_price); ?></td>
        </tr>
    <?php endforeach; ?>
    <!-- END ITEMS HERE -->
    <tr>
        <td class="blanktotal" colspan="5" rowspan="3"></td>
        <td class="totals">Subtotal:</td>
        <td class="totals cost"><?php echo money($subtotal); ?></td>
    </tr>
    <tr>
        <td class="totals">Tax:</td>
        <td class="totals cost"><?php echo money(($total - $subtotal)); ?></td>
    </tr>
    <tr>
        <td class="totals"><b>Total Parts Cost:</b></td>
        <td class="totals cost"><b><?php echo money($total);
                $all += $total; ?></b></td>
    </tr>
    </tbody>
</table>
<br/>
<strong>Employees:</strong>
<table class="items" width="100%" style="font-size: 9pt; border-collapse: collapse; " cellpadding="8">
    <thead>
    <tr>
        <td width="25%">Employee</td>
        <td width="20%">Date</td>
        <td width="25%">Hours</td>
        <td width="15%">Hourly Rate</td>
        <td width="15%">Cost</td>
    </tr>
    </thead>
    <tbody>
    <?php
    $total = 0;
    ?>
    <?php foreach ($report->employees as $emp) : ?>
        <?php
        $total += round($emp->emp_hours * $emp->emp_hourly_rate, 2);
        ?>
        <tr>
            <td align="left"><?php echo $emp->user->firstname; ?>&nbsp;<?php echo $report->user->lastname; ?></td>
            <td align="center"><?php echo $emp->emp_worked_at; ?></td>
            <td><?php echo $emp->emp_hours; ?></td>
            <td><?php echo money($emp->emp_hourly_rate); ?></td>
            <td><?php echo money(round($emp->emp_hours * $emp->emp_hourly_rate, 2)); ?></td>
        </tr>
    <?php endforeach; ?>
    <!-- END ITEMS HERE -->
    <tr>
        <td class="blanktotal" colspan="3" rowspan="1"></td>
        <td class="totals"><b>Total Labour Cost:</b></td>
        <td class="totals cost"><b><?php echo money($total);
                $all += $total; ?></b></td>
    </tr>
    </tbody>
</table>
<br/>
<table class="items" width="100%" style="font-size: 9pt; border-collapse: collapse; " cellpadding="8">
    <tbody>
    <tr>
        <td class="blanktotal"></td>
        <td width="15%" class="totals"><b>Total Service Cost:</b></td>
        <td width="15%" class="totals cost"><b><?php echo money($all); ?></b></td>
    </tr>
    </tbody>
</table>
<br/>
<?php if ($report->service_report_type === EquipmentServiceReport::TYPE_COMPLETED) : ?>
    End date: <strong><?php echo $report->service_report_end_date; ?> </strong> with note:
    <table class="items" width="100%" style="font-size: 9pt; border-collapse: collapse; " cellpadding="8">
        <tbody>
        <tr>
            <td class="blanktotal"><?php echo $report->service_report_note; ?></td>
        </tr>
        </tbody>
    </table>
<?php endif; ?>

<?php foreach ($report->files as $file): ?>
    <?php if (\Illuminate\Support\Str::endsWith(strtolower($file->file_name),
        ['.jpg', 'jpeg', '.tiff', '.gif', '.png'])): ?>
        <div style="text-align: center">
            <img src="<?php echo $file->file_url; ?>" width="80%" height="auto"/>
        </div>
    <?php endif; ?>
<?php endforeach; ?>
</body>
</html>