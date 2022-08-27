<div class="row">
    <div class="col-xs-12">
        <section class="panel panel-default p-bottom-20 active-services-block">
            <header class="panel-heading pos-rlt">
                Active Services
                <div class="dropdown billing-action-block">
                    <a href="#" id="add_subscription" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                       class="billing-action-add text-success billing-action-btn">
                        <i class="fa fa-plus"></i>
                    </a>
                    <ul class="dropdown-menu animated fadeInRight" aria-labelledby="add_subscription">
                        <li class="arrow top top-right"></li>
                        <li>
                            <a href="<?php echo base_url('billing/sms_subscriptions'); ?>" title="Add SMS subscription">
                                SMS subscription
                            </a>
                        </li>
                    </ul>
                </div>
            </header>

            <div class="subscription-panel-content">
                <?php $includeSubscriptionModal = false; ?>
                <?php if (sizeof($active_services)): ?>
                    <?php foreach ($active_services as $key => $services): ?>
                        <div class="subscriptions-block p-15">
                            <div class="sub-header row">
                                <div class="col-sm-4 col-lg-3 col-xs-12 h4">
                                    <?php echo $key; ?>
                                </div>

                                <?php if ($key === 'SMS'): ?>
                                    <div class="sub-progress col-sm-8 col-lg-9 col-xs-12">
                                        <div class="row">
                                            <div class="col-xs-6 col-sm-5 col-lg-4 text-right">
                                                Quota remaining: <?php echo ($sms_counts['total_remain'] >= 0 ? $sms_counts['total_remain'] : 0) . '/' . $sms_counts['total_count']; ?>
                                            </div>
                                            <div class="col-xs-6 col-sm-7 col-lg-8 p-left-0">
                                                <div class="progress progress-xs m-t-xs m-b-xs">
                                                    <div class="progress-bar progress-bar-info"
                                                         data-toggle="tooltip"
                                                         data-original-title="<?php echo $sms_counts['percent']; ?>%"
                                                         style="width: <?php echo $sms_counts['percent']; ?>%">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <?php foreach ($services as $service): ?>
                                <div class="subscription-content m-t-sm b-a r col-xs-12 padder-v">
                                    <?php $serviceIconTitle = !$service->renewal
                                        ? ($service->sub_amount > 0 ? 'Regular package' : 'Free subscription')
                                        : ($service->renewal === 'limit' ? 'Reached limit subscription' : 'Monthly subscription'); ?>
                                    <div class="fa-stack fa-2x pull-left m-r-sm"
                                         title="<?php echo $serviceIconTitle; ?>">
                                        <?php $icon = !$service->renewal
                                            ? ($service->sub_amount > 0 ? 'smile-o' : 'gift')
                                            : ($service->renewal === 'limit' ? 'ban' : 'calendar h4'); ?>
                                        <?php $iconColor = !$service->paid ? 'danger' : ($service->sub_amount > 0 ? 'success' : 'primary'); ?>
                                        <i class="fa fa-circle fa-stack-2x text-<?php echo $iconColor; ?>"></i>
                                        <i class="fa fa-stack-1x text-white fa-<?php echo $icon; ?>"></i>
                                    </div>
                                    <div class="clear subscription-info">
                                        <div class="sub-main-info h3 block m-t-xs text-uc">
                                            <strong><?php echo $service->sub_name; ?></strong>
                                        </div>
                                        <small class="text-muted text-uc">
                                            <?php if (!$service->paid): ?>
                                                Unpaid (<?php echo $service->error_info; ?>)
                                            <?php else: ?>
                                                <?php echo $service->sub_amount > 0
                                                    ? 'Successfully paid'
                                                    : $service->remain . ' sms left';
                                                ?>
                                            <?php endif; ?>
                                        </small>
                                    </div>
                                    <div class="pull-right">
                                        <div class="sub-additional-info text-uc">
                                            <?php echo $service->count; ?> SMS / <?php echo $service->sub_period; ?>
                                        </div>
                                        <div class="sub-additional-info text-right">
                                            <?php if (!$service->paid && $service->sub_amount > 0): ?>
                                                <?php $includeSubscriptionModal = true; ?>
                                                <a href="#order_subscription_modal"
                                                   class="btn btn-success btn-rounded btn-xs p-sides-20"
                                                   title="Pay for this subscription"
                                                   data-toggle="modal"
                                                   data-id="<?php echo $service->id; ?>"
                                                   data-name="<?php echo $service->sub_name; ?>"
                                                   data-count="<?php echo $service->count; ?>"
                                                   data-amount="<?php echo money($service->sub_amount); ?>"
                                                   data-from="<?php echo $service->from; ?>"
                                                   data-to="<?php echo $service->to; ?>"
                                                   data-default-card-id="<?php echo $service->card_id ?: $default_card_id; ?>"
                                                   data-description="Subscription will be activated after payment"
                                                   data-type="<?php echo $service->renewal ?? 'package'; ?>>"
                                                   data-action="pay"
                                                >
                                                    Pay
                                                </a>
                                            <?php elseif ($service->sub_amount == 0 && isSystemUser()): ?>
                                                <a href="#order_subscription_modal"
                                                   class="edit-sms-subscription text-muted billing-action-btn m-right-10"
                                                   title="Edit subscription"
                                                   data-toggle="modal"
                                                   data-id="<?php echo $service->id; ?>"
                                                   data-name="<?php echo $service->sub_name; ?>"
                                                   data-count="<?php echo $service->count; ?>"
                                                   data-from="<?php echo $service->from; ?>"
                                                   data-to="<?php echo $service->to; ?>"
                                                   data-amount="<?php echo money($service->sub_amount); ?>"
                                                   data-type="free"
                                                   data-action="update"
                                                ><i class="fa fa-pencil"></i></a>
                                                <a href="#" class="delete-free-order text-danger billing-action-btn m-right-10"
                                                   title="Delete gift order"
                                                   data-type="order"
                                                   data-id="<?php echo $service->id; ?>"><i class="fa fa-trash-o"></i></a>
                                            <?php endif; ?>
                                            <strong><?php echo $service->sub_amount > 0 ? '$ ' . $service->sub_amount : 'FREE'; ?></strong>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="subscriptions-block p-15">
                        <p>You have no active subscriptions.</p>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </div>

    <div class="col-lg-8 col-xs-12">
        <section class="panel panel-default p-n height-280">
            <?php $this->load->view('billing/partials/transactions_part'); ?>
            <?php if (!empty($transactions)): ?>
                <div class="b-light text-center p-top-10 p-bottom-10">
                    <a href="<?php echo base_url('billing/transactions'); ?>" class="btn btn-default btn-rounded btn-sm">
                        Show more
                    </a>
                </div>
            <?php endif; ?>
        </section>
    </div>

    <div class="col-lg-4 col-xs-12">
        <section class="panel panel-default p-n">
            <header class="panel-heading pos-rlt">
                Payment methods
                <div class="billing-action-block" id="billing_add_card_block">
                    <a href="#card-form"
                       id="payment_add_cc_card"
                       class="add_credit_card billing-action-add text-success billing-action-btn"
                       data-toggle="modal">
                        <i class="fa fa-plus"></i>
                    </a>
                </div>
            </header>

            <div class="scrollable height-240">
                <div class="p-15 p-top-5">
                    <?php if (sizeof($cards)): ?>
                        <?php foreach ($cards as $card): ?>
                            <div id="cardId-<?php echo $card['card_id']; ?>"
                                 class="payment-content m-t-sm b-a r p-15<?php echo $card['card_id'] === $default_card_id ? ' is-default-card' : ''; ?>">
                                <div class="default-card-block text-primary">
                                    <i class="fa fa-star default-card"
                                       title="Default card"
                                       data-toggle="tooltip"
                                       data-placement="right"></i>
                                    <a href="#" class="set-default-card"
                                       data-card-id="<?php echo $card['card_id']; ?>"
                                       title="Set as default card"
                                       data-toggle="tooltip"
                                       data-placement="right">
                                        <i class="fa fa-star-o text-primary"></i>
                                    </a>
                                </div>
                                <div class="row">
                                    <div class="col-xs-2 col-lg-3 payment-image vcenter p-right-5">
                                        <?php $img = $card['card_type'] === 'mastercard' ? 'mc.png' : $card['card_type'] . '.png'; ?>
                                        <img src="/assets/img/cards/<?php echo $img; ?>">
<!--                                        <div class="--><?php //echo $card['card_type']; ?><!--"></div>-->
                                    </div>
                                    <div class="col-xs-8 vcenter p-left-5">
                                        <div class="m-right-10">
                                            <?php echo ucfirst($card['card_type']); ?>:&nbsp;<strong style="color: #2e3e4e"><?php echo substr($card['number'], -4); ?></strong>
                                        </div>
                                        <div>
                                            Exp.&nbsp;date:&nbsp;<strong style="color: #2e3e4e"><?php echo $card['expiry_month'] . '/' . $card['expiry_year']; ?></strong>
                                        </div>
                                    </div>
                                    <div class="col-xs-2 col-lg-1 payment-action vcenter">
                                        <a href="#" class="delete-card text-danger" title="Delete card"
                                           data-card-id="<?php echo $card['card_id']; ?>"
                                        >
                                            <i class="fa fa-trash-o"></i>
                                        </a>
                                    </div>

                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>You have no payment methods.</p>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </div>
</div>

<?php if ($includeSubscriptionModal): ?>
    <link href="<?php echo base_url('assets/css/modules/billing/sms_subscriptions.css?v=1.03'); ?>" rel="stylesheet">

    <?php $this->load->view('billing/sms_subscriptions/order_subscription_modal'); ?>

    <script src="<?php echo base_url('assets/js/modules/billing/sms_subscriptions.js?v=1.05'); ?>"></script>
    <script src="https://gitcdn.github.io/bootstrap-toggle/2.2.2/js/bootstrap-toggle.min.js"></script>
<?php endif; ?>

<script>
    var billingAddCard = false;
</script>
