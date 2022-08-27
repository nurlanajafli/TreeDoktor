<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title><?php echo $title; ?></title>
    <meta name="description" content="">
    <meta name="author" content="Main Prospect">

    <!-- CSS -->

    <link rel="stylesheet" href="<?php echo base_url('assets/vendors/notebook/css/bootstrap.css'); ?>">

    <link rel="stylesheet" href="<?php echo base_url('assets/vendors/notebook/css/app.css?v='.config_item('app.css')); ?>">

</head>
<body>

<div class="center-block " style="font-weight: bold">
    <div class="text-right"><?php echo $title; ?></div>
    <div class="text-left">
        <?php if(isset($filters) && !empty($filters)) : ?>
            Filter Params <br>
            <?php foreach($filters as $key=>$val) :?>
                <?php echo $key; ?>:
                <?php if(!is_array($val)) : ?>
                    <?php echo $val; ?>
                <?php else : ?>
                    <?php foreach($val as $k=>$v) : ?>
                        <?php echo $v; ?><?php if(isset($val[$k+1])) : ?>, <?php endif; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
                <br>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div><br><br>
<section class="panel panel-default" style="margin: 0px -30px;">


    <div class="m-bottom-10 p-sides-5" style="padding: 0px;">
        <table cellpadding="0" cellspacing="0" border="0" width="100%" class="p_top_5 table">
            <thead>
            <tr>
                <th>#</th>
                <th align="right">Service Name</th>
                <th align="center">Count of Services</th>
                <th align="center">Total for Services</th>
            </tr>
            </thead>
            <tbody>
            <?php
            if ($services) :
                $total = 0;
                $amount = 0;
                $hst = 0;
                foreach ($services as $key => $val):
                    ?>
                    <tr>
                        <td >
                            <?php echo $key + 1; ?>
                        </td>
                        <td align="right">
                            <?php echo $val->service_name; ?>
                        </td>
                        <td  align="center">
                            <?php echo $val->count; ?>
                        </td>
                        <td  align="center">
                            <?php echo money($val->total); ?>
                            <?php $amount += $val->total; ?>
                        </td>
                    </tr>
                    <?php $total += $val->count; ?>
                <?php endforeach; ?>
                <tr>
                    <td></td>
                    <td align="right">
                        <strong>Services: </strong><br>
                        <strong>Estimates: </strong>
                    </td>
                    <td align="center">
                        <strong><?php echo $total; ?></strong><br>

                        <strong><?php echo $all_estimates->total;?></strong>
                    </td>
                    <td align="center">
                        <strong><?php echo money($amount); ?></strong><br>
                        <strong><?php echo money($all_estimates->sum); ?></strong>
                    </td>

                </tr>
            <?php else :
                ?>
                <tr>
                    <td colspan="8"><?php echo "No records found"; ?></td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

</section>

</body>
</html>
