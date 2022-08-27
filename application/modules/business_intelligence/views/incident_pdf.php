<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Incident #<?php echo $inc_id; ?></title>
    <meta name="description" content="">
    <meta name="author" content="Main Prospect">
    <link rel="stylesheet" href="<?php echo base_url('assets/vendors/notebook/css/bootstrap.css'); ?>">
    <link rel="stylesheet" href="<?php echo base_url('assets/vendors/notebook/css/app.css?v='.config_item('app.css')); ?>">
    <style>
        @page {
            margin-top: 20px;
            margin-bottom: 20px;
            margin-left: 50px;
            margin-right: 10px;
            background-image: url('<?php echo base_url('assets/' . $this->config->item('company_dir') . '/print/container_table_left_margin.png'); ?>'); background-position: 0 -48px; background-repeat: no-repeat;
        }
    </style>

</head>
<body style="border: 0!important; font-family: Sans-Serif; font-size: 12pt; padding: 0;">

<div style="margin: 0 0px 0 20px; padding: 0 0 0 0;">
    <div class="">
        <div class="wrapper panel-light">
            <div class="panel-heading text-right pull-right h3">Near Miss / Incident Report #<?php echo $inc_id; ?></div>
        </div>
        <div class="m-top-20 p-sides-10">
            <div style="background-image: url('<?php echo base_url('assets/print/bg.png'); ?>'); border-radius: 30px; padding: 30px 30px; font-size: 16px;">
                <div style="height: 350px; background-image: url('<?php echo base_url('assets/' . $this->config->item('company_dir') . '/print/watermark.png'); ?>'); background-position: center center; background-repeat: no-repeat; background-size: 1500px;">
                    <div class="row">
                        <div class="col-xs-4 text-center">
                            <a href="#">
                                <i class="m-b-xs h5 block">User</i><br>
                                <small class="text-muted h4"><?php echo $firstname . ' ' . $lastname ?></small>
                            </a>
                        </div>
                        <div class="col-xs-3 text-center">
                            <a href="#">
                                <i class="m-b-xs h5 block">Type</i><br>
                                <small class="text-muted h4">
                                    <?php echo isset($inc_payload->type) && $inc_payload->type ? ucfirst($inc_payload->type) : 'N/A'; ?>
                                </small>
                            </a>
                        </div>
                        <div class="col-xs-3 text-center">
                            <a href="#">
                                <i class="m-b-xs h5 block">Workorder</i><br>
                                <small class="text-muted h4"><?php echo $workorder_no ? $workorder_no : '—'; ?></small>
                            </a>
                        </div>
                    </div>

                    <div class="m-top-30" style="background-color: transparent; border-left: 1px solid #cfcfcf; border-right: 1px solid #cfcfcf; border-bottom: 1px solid #cfcfcf; display: table;">
                        <?php foreach ($inc_payload as $key => $value) : ?>
                            <div style="border-top: 1px solid #cfcfcf;">
                                <div class="pull-left" style="width: 180px; padding: 5px; border-right: 1px solid #cfcfcf; ">
                                    <b><?php echo ucfirst(str_replace('_', ' ', $key)); ?></b>
                                </div>
                                <?php if($key == 'date'): ?>
                                    <div class="pull-left" style="padding: 5px 5px 5px 6px; border-left: 1px solid #cfcfcf; margin-left: -1px;"><?php echo nl2br(getDateTimeWithDate($value, 'Y-m-d')); ?></div>
                                <?php elseif($key == 'time'): ?>
                                    <div class="pull-left" style="padding: 5px 5px 5px 6px; border-left: 1px solid #cfcfcf; margin-left: -1px;"><?php echo nl2br(getTimeWithDate($value, 'H:i')); ?></div>
                                <?php else: ?>
                                    <div class="pull-left" style="padding: 5px 5px 5px 6px; border-left: 1px solid #cfcfcf; margin-left: -1px;"><?php echo nl2br($value); ?></div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                        <div style="border-top: 1px solid #cfcfcf; ">
                            <div class="pull-left" style="width: 180px; padding: 5px; border-right: 1px solid #cfcfcf;"><b>Created at</b></div>
<!--                            <div class="pull-left" style="padding: 5px;">--><?php //echo $inc_created_at; ?><!--</div>-->
                            <div class="pull-left" style="padding: 5px;"><?php echo getDateTimeWithDate($inc_created_at, 'Y-m-d H:i:s', true); ?></div>
                        </div>
                    </div>

                </div>
            </div>


            <table class="panel-heading text-left h3" style="margin-top: 90px; border-bottom: 1px solid #000;" width="100%">
                <tr>
                <td valign="bottom" width="130px" style="padding-bottom: 10px;">Signature:</td>
                <td align="left" style="padding-bottom: 5px;">
                    <?php if(is_bucket_file('uploads/incidents/' . $inc_id . '/sign/signature.png')) : ?><img src="<?php echo base_url('uploads/incidents/' . $inc_id . '/sign/signature.png'); ?>"><?php else : ?>—<?php endif; ?>
                </td>
                </tr>
            </table>
        </div>
        <?php $photos = bucketScanDir('uploads/incidents/' . $inc_id . '/photos/', TRUE); ?>
        <?php if($photos && !empty($photos)) : ?>
        <pagebreak>
            <?php foreach ($photos as $photo) : ?>
                <img src="<?php echo base_url($photo); ?>">
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
