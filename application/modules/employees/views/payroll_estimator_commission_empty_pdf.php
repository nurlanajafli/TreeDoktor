<!DOCTYPE html>
<html lang="en" style=" margin-bottom: 0px!important;">
<head>
    <meta charset="utf-8">
    <title><?= isset($title) ? $title : 'No Records Found' ?></title>
    <meta name="description" content="">
    <meta name="author" content="Main Prospect">
    <link rel="stylesheet" href="<?php echo base_url('assets/vendors/notebook/css/bootstrap.css'); ?>">
    <link rel="stylesheet" href="<?php echo base_url('assets/vendors/notebook/css/font.css'); ?>">
    <link rel="stylesheet" href="<?php echo base_url('assets/vendors/notebook/css/app.css?v='.config_item('app.css')); ?>">
    <link rel="stylesheet" href="<?php echo base_url('assets/css/payroll_pdf.css'); ?>">
</head>
<body style="border: 0!important; margin-bottom: 0px!important;">
    <div style="position: absolute; left: 3mm; width: 100%;">
        <table width="100%" class="report-page-table" autosize="1" style="overflow: wrap">
            <thead>
                <tr>
                    <th colspan="7" class="text-danger text-center h4 font-bold">No Records Found</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</body>
</html>
