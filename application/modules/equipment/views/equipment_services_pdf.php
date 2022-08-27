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

        #items, #items th, #items td {
            border-width: 1px;
            border-style: solid;
            border-color: black;
            border-image: initial;
            border-collapse: collapse;
        }

        #items thead {
            border-bottom-width: 2px;
        }

        #items th {
            padding-top: 12px;
            padding-bottom: 12px;
            text-align: left;
            background-color: #f1efef;
        }

        #items tbody tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        td {
            vertical-align: top;
        }

        #items tr.bg-danger {
            background-color: #f59f9f !important;
        }

        #items tr.bg-warning {
            background-color: #ffe39f !important;
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
echo brand_office_address($brand_id); ?>,
<?php
echo brand_office_city($brand_id); ?>,
<?php
echo brand_office_zip($brand_id); ?>,
<?php
echo brand_office_country($brand_id); ?>
<br />
<span style="font-family:dejavusanscondensed;">&#9742;</span>
<?php
echo brand_phone($brand_id); ?>
</td>
<td width="50%" style="text-align: right;"></td>
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
<table id="items" width="100%" style="font-family: serif;" cellpadding="10">
    <thead>
    <tr>
        <th>Equipment</th>
        <th>Service Type</th>
        <th>Name</th>
        <th>Description</th>
        <th>Next due on</th>
        <th>Next due <?php echo DISTANCE_MEASUREMENT; ?>/hrs</th>
        <th>Last done on</th>
        <th>Last done <?php echo DISTANCE_MEASUREMENT; ?>/hrs</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($services as $service) : ?>
        <tr id="row_<?php echo $service->service_id; ?>" class="
        <?php if ((int)$service->service_next_date_due < 0) {
            echo "bg-danger";
        } elseif ((int)$service->service_next_date_due <= 5) {
            echo "bg-warning";
        }
        ?>
        ">
            <td>
                <?php echo $service->equipment->eq_name; ?>
            </td>
            <td><?php echo $service->service_type->service_type_form_str; ?></td>
            <td><?php echo $service->service_name; ?></td>
            <td><?php echo $service->service_description; ?></td>
            <td><?php if (!empty($service->service_next_date)) : ?>
                    <?php echo $service->service_next_date; ?>, <?php echo $service->service_next_date_due; ?> day
                <?php endif; ?>
            </td>
            <td><?php if (!empty($service->service_next_counter)) : ?>
                    <?php echo $service->service_next_counter; ?><?php echo $service->equipment->eq_counter_type_str; ?>,
                    <?php echo $service->service_next_counter_due; ?><?php echo $service->equipment->eq_counter_type_str; ?>
                <?php endif; ?>
            </td>
            <td><?php if (!empty($service->last_report)) : ?>
                    <?php if (!empty($service->last_report->service_report_end_date)) : ?>
                        <?php echo $service->last_report->service_report_end_date; ?>
                    <?php else : ?>
                        <?php echo $service->last_report->service_report_created_date; ?>
                    <?php endif; ?>
                <?php endif; ?>
            </td>
            <td><?php if (!empty($service->last_report) && !empty($service->last_report->counter)) : ?>
                    <?php echo $service->last_report->counter->counter_value; ?>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</body>
</html>