<div class="clear text-nowrap b-t">
        <div class="col-lg-4 h-60 b-r text-center">Total
            <div class="h2"><?php echo $total_estimates_date; ?></div>
        </div>
        <div class="col-lg-4 h-60 text-center">Opportunity
            <div class="h2"><?php echo money($revenue_total_estimates_date); ?></div>
        </div>
        <div class="col-lg-4 h-60 b-l text-center">Working Days
            <div class="h2"><?php echo round($period_days_val, 0); ?></div>
        </div>
    </div>
    <!--/Totals-->

    <!--Confirmed-->
    <div class="bg-success clear text-nowrap">
        <div class="col-lg-4 h-60 b-r text-center">Confirmed
            <div class="h2"><?php echo $confirmed_estimates_date; ?></div>
        </div>
        <div class="col-lg-4 h-60 text-center">Received
            <div class="h2"><?php echo money($revenue_confirmed_estimates_date); ?></div>
        </div>
        <div class="col-lg-4 h-60 b-l text-center">Approval Rate
            <div class="h2"><?php if ($revenue_total_estimates_date != 0) {
                    $res = 100 / $revenue_total_estimates_date * $revenue_confirmed_estimates_date;
                    echo round($res, 2) . "%";
                } else {
                    echo "N/A";
                } ?></div>
        </div>
    </div>
    <!--/Confirmed-->

    <!--Declined-->
    <div class="bg-danger clear text-nowrap">
        <div class="col-lg-4 h-60 b-r text-center">Declined
            <div class="h2"><?php echo $declined_estimates_date; ?></div>
        </div>
        <div class="col-lg-4 h-60  text-center">Lost
            <div class="h2"><?php echo money($revenue_declined_estimates_date); ?></div>
        </div>
        <div class="col-lg-4 h-60 b-l text-center">Rejection Rate
            <div class="h2"><?php if ($revenue_total_estimates_date != 0) {
                    $res = 100 / $revenue_total_estimates_date * $revenue_declined_estimates_date;
                    echo round($res, 2) . "%";
                } else {
                    echo "N/A";
                } ?></div>
        </div>
    </div>

    <div class="bg-warning clear text-nowrap">
        <div class="col-lg-4 h-60 b-r text-center">Other
            <div class="h2"><?php echo $total_estimates_date - $declined_estimates_date - $confirmed_estimates_date; ?></div>
        </div>
        <div class="col-lg-4 h-60  text-center">Pending
            <div class="h2"><?php echo money($revenue_total_estimates_date - $revenue_confirmed_estimates_date - $revenue_declined_estimates_date); ?></div>
        </div>
        <div class="col-lg-4 h-60 b-l text-center">Pending Rate
            <div class="h2"><?php if ($revenue_total_estimates_date != 0) {
                    $res = 100 - ((100 / $revenue_total_estimates_date * $revenue_declined_estimates_date) + (100 / $revenue_total_estimates_date * $revenue_confirmed_estimates_date));
                    echo round(abs($res), 2) . "%";
                } else {
                    echo "N/A";
                } ?></div>
        </div>
    </div>

    <div class="bg-light clear text-nowrap">
        <div class="col-lg-4 h-60 b-r text-center">Daily average
            <div class="h2"><?php if ($total_estimates_date > 0) {
                    $res = $total_estimates_date / $period_days_val;
                    echo round($res, 0);
                } else {
                    echo "N/A";
                } ?></div>
        </div>
        <div class="col-lg-4 h-60 text-center">Commission(<?php echo config_item('estimator_commission') ? config_item('estimator_commission') : ''; ?>%)
            <div class="h2">
				<?php if(config_item('estimator_commission')) :  ?>
				<?php $res = (($net_sales ?? $revenue_confirmed_estimates_date) / 100) * config_item('estimator_commission'); ?>
                    <?php echo money($res); ?>
                <?php else : ?> &mdash; <?php endif; ?>
                </div>
        </div>
        <div class="col-lg-4 h-60 b-l text-center">Reach-out Rate
            <div class="h2"><?php if ($revenue_total_estimates_date != 0) {
                    $res = 100 / $revenue_total_estimates_date * ($revenue_confirmed_estimates_date + $revenue_declined_estimates_date);
                    echo round($res, 2) . "%";
                } else {
                    echo "N/A";
                } ?></div>
        </div>
    </div>
    <?php /*
    <div class="bg-warning clear text-nowrap">
            <div class="col-lg-4 h-60 b-r text-center">SE
                <div class="h2"><?php echo $se = $total_estimates_date * 5; ?></div>
            </div>
            <div class="col-lg-4 h-60 text-center">CSE
                <div class="h2">
                <?php if(isset($estimate_counts_contact) && $se) : ?>
                    <?php echo round($estimate_counts_contact / $se * 100, 2); ?>
                <?php else :?>
                        0
                <?php endif;?>	
                %</div>
            </div>
            <div class="col-lg-4 h-60 b-l text-center">EE
                <div class="h2">
                    <?php if(isset($estimator_counts_contact) && $se) : ?>
                        <?php echo round($estimator_counts_contact / $se * 100, 2); ?>
                    <?php else :?>
                        0
                    <?php endif;?>	
                %</div>
            </div>
        </div>
        */ ?>
        <!--Sales Engagement-->
