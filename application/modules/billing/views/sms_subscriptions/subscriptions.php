<link href="<?php echo base_url('assets/css/modules/billing/sms_subscriptions.css?v=1.03'); ?>" rel="stylesheet">
<?php if (isSystemUser()): ?>
    <?php $this->load->view('billing/sms_subscriptions/create_update_subscription_modal', $free_update); ?>
<?php endif; ?>

<div class="row">
    <div class="col-xs-12 m-b">
        <div class="m-b">
            <span class="h3">
                Subscriptions
            </span>
        </div>
        <div class="renewal-subscriptions">
            <div class="col-xs-12 col-lg-6 padder-v subscription-content b-a r b-light">
                <?php if ($next_period_order): ?>
                    <div class="fa-stack fa-2x pull-left m-r-sm">
                        <i class="fa fa-circle fa-stack-2x text-success"></i>
                        <i class="fa fa-stack-1x text-white fa-calendar h4"></i>
                    </div>
                    <div class="clear subscription-info">
                        <div class="sub-main-info h3 block m-t-xs text-uc">
                            <strong>
                                $ <?php echo $next_period_order->sub_amount . ' (' . $next_period_order->count . ' SMS)'; ?>
                            </strong>
                        </div>
                        <small class="text-muted text-uc">
                            will be charged on the 1st of the next month
                        </small>
                    </div>
                    <div class="billing-action-block">
                        <a href="#order_subscription_modal"
                           class="edit-sms-subscription text-muted billing-action-btn"
                           title="Edit subscription"
                           data-toggle="modal"
                           data-id="<?php echo $next_period_order->id; ?>"
                           data-name="<?php echo $next_period_order->sub_name; ?>"
                           data-count="<?php echo $next_period_order->count; ?>"
                           data-from="<?php echo $next_period_order->from; ?>"
                           data-to="<?php echo $next_period_order->to; ?>"
                           data-amount="<?php echo money($next_period_order->sub_amount); ?>"
                           data-default-card-id="<?php echo $next_period_order->card_id; ?>"
                           data-description="Subscription will be charged on the 1st of the next month"
                           data-type="period"
                           data-action="update"
                        ><i class="fa fa-pencil"></i></a>
                        <a href="#" class="delete-sms-subscription text-danger billing-action-btn" title="Delete subscription"
                           data-order-id="<?php echo $next_period_order['id']; ?>"><i class="fa fa-trash-o"></i></a>
                    </div>
                <?php else: ?>
                    <div class="fa-stack fa-2x pull-left m-r-sm">
                        <i class="fa fa-circle fa-stack-2x text-muted"></i>
                        <i class="fa fa-stack-1x text-white fa-calendar h4"></i>
                    </div>
                    <div class="clear subscription-info">
                        <div class="sub-main-info h3 block m-t-xs text-uc">
                            <strong>&mdash;</strong>
                        </div>
                        <small class="text-muted text-uc">
                            will be charged on the 1st of the next month
                        </small>
                    </div>
                    <div class="dropdown billing-action-block">
                        <a href="#" id="add_period_subscription" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                           class="billing-action-add billing-action-btn text-success" title="Add subscription">
                            <i class="fa fa-plus"></i>
                        </a>
                        <ul class="dropdown-menu animated fadeInRight" aria-labelledby="add_period_subscription">
                            <li class="arrow top top-right"></li>
                            <?php $this->load->view('add_subscriptions', [
                                    'type' => 'period',
                                    'sub_description' => 'Subscription will be charged on the 1st of the next month'
                            ]); ?>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>
            <div class="col-xs-12 col-lg-6 padder-v subscription-content b-a r b-light<?php echo false ? '' : ' lt'; ?>">
                <?php if ($limit_order): ?>
                    <div class="fa-stack fa-2x pull-left m-r-sm">
                        <i class="fa fa-circle fa-stack-2x text-success"></i>
                        <i class="fa fa-stack-1x text-white fa-ban"></i>
                    </div>
                    <div class="clear subscription-info">
                        <div class="sub-main-info h3 block m-t-xs text-uc">
                            <strong>
                                $ <?php echo $limit_order->sub_amount . ' (' . $limit_order->count . ' SMS)'; ?>
                            </strong>
                        </div>
                        <small class="text-muted text-uc">
                            will be charged when the limit is reached
                        </small>
                    </div>
                    <div class="billing-action-block">
                        <a href="#order_subscription_modal"
                           class="edit-sms-subscription text-muted billing-action-btn"
                           title="Edit subscription"
                           data-toggle="modal"
                           data-id="<?php echo $limit_order->id; ?>"
                           data-name="<?php echo $limit_order->sub_name; ?>"
                           data-count="<?php echo $limit_order->count; ?>"
                           data-amount="<?php echo money($limit_order->sub_amount); ?>"
                           data-default-card-id="<?php echo $limit_order->card_id; ?>"
                           data-description="Subscription will be charged when the limit is reached"
                           data-type="period"
                           data-action="update"
                        ><i class="fa fa-pencil"></i></a>
                        <a href="#" class="delete-sms-subscription text-danger billing-action-btn" title="Delete subscription"
                           data-order-id="<?php echo $limit_order->id; ?>"><i class="fa fa-trash-o"></i></a>
                    </div>
                <?php else: ?>
                    <div class="fa-stack fa-2x pull-left m-r-sm">
                        <i class="fa fa-circle fa-stack-2x text-muted"></i>
                        <i class="fa fa-stack-1x text-white fa-ban"></i>
                    </div>
                    <div class="clear subscription-info">
                        <div class="sub-main-info h3 block m-t-xs text-uc">
                            <strong>&mdash;</strong>
                        </div>
                        <small class="text-muted text-uc">
                            will be charged when the limit is reached
                        </small>
                    </div>
                    <div class="dropdown billing-action-block"<?php echo !$add_limit_order_enable ? ' title="You have no active orders"' : ''; ?>>
                        <a href="#"
                           id="add_limit_subscription"
                           <?php echo $add_limit_order_enable ? 'data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"' : 'disabled'; ?>
                           class="billing-action-add billing-action-btn text-success<?php echo $add_limit_order_enable ? '' : ' disabled'; ?>"
                           title="Add subscription">
                            <i class="fa fa-plus"></i>
                        </a>
                        <?php if ($add_limit_order_enable): ?>
                            <ul class="dropdown-menu animated fadeInRight" aria-labelledby="add_limit_subscription">
                                <li class="arrow top top-right"></li>
                                <?php $this->load->view('add_subscriptions', [
                                        'type' => 'limit',
                                        'sub_description' => 'Subscription will be charged when the limit is reached'
                                ]); ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-xs-12">
        <div class="m-b">
            <span class="h3">
                Packages
            </span>

            <?php if (isSystemUser() && $free_subscription): ?>
                <?php $freeSubHref = 'update_subscription_modal_' . $free_subscription->id; ?>
                <a id="edit_free_subscription_btn"
                   href="#<?php echo $freeSubHref; ?>"
                   class="pull-right text-success"
                   data-toggle="modal"
                   title="Edit Gift subscription"
                >
                    Edit "Gift subscription" <?php echo $free_subscription->count; ?> SMS/<?php echo $free_subscription->period; ?>
                </a>
            <?php endif; ?>
        </div>

        <?php if ($sms_subscriptions->count()): ?>
            <div class="row m-b-xl">
                <?php foreach ($sms_subscriptions as $key => $subscription): ?>
                    <?php if ($subscription->active || isSystemUser()): ?>
                        <div class="col-xs-12 col-lg-4 animated <?php echo $key === 0 ? 'fadeInLeftBig' : ($key === 1 ? 'fadeInUp' : 'fadeInRightBig'); ?>">
                            <section class="panel text-center <?php echo $key%2 ? 'b-success' : 'b-light m-t'; ?>">
                                <header class="panel-heading pos-rlt <?php echo !$subscription->active && isSystemUser() ? 'bg-light lt' : ($key%2 ? 'bg-success' : 'bg-white b-light'); ?>">
                                    <h3 class="m-t-sm<?php echo $key === 1 ? ' font-semibold' : ''; ?>"><?php echo $subscription->name; ?></h3>
                                    <p<?php echo $key === 1 ? ' class="font-semibold"' : ''; ?>><?php echo $subscription->count . ' SMS per ' . $subscription->period; ?></p>

                                    <?php if (isSystemUser()): ?>
                                        <div class="subscription-system-edit">
                                            <a href="#update_subscription_modal_<?php echo $subscription->id; ?>"
                                               class="<?php echo $key === 1 ? 'sub-text-white' : 'text-success'; ?>"
                                               role="button"
                                               data-toggle="modal"
                                               title="Edit subscription"
                                            >
                                                <i class="fa fa-gear"></i>
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </header>
                                <ul class="list-group">
                                    <li class="list-group-item text-center bg-light lt">
                                        <?php if ($key%2): ?><div class="padder-v"><?php endif; ?>
                                            <span class="text-<?php echo !$subscription->active && isSystemUser() ? 'dark' : 'danger'; ?> font-bold h1">
                                        <?php echo money($subscription->amount); ?>
                                    </span> / <?php echo $subscription->period; ?>
                                            <?php if ($key%2): ?></div><?php endif; ?>
                                    </li>
                                    <li class="list-group-item sub-description-text"><?php echo $subscription->description; ?></li>
                                </ul>
                                <footer class="panel-footer">
                                    <?php $href = $sms_subscriptions_disabled ? '#' : '#order_subscription_modal'; ?>
                                    <?php $title = $sms_subscriptions_disabled ? 'Subscription unavailable' : 'Buy package'; ?>
                                    <a href="<?php echo $href; ?>" data-toggle="modal"
                                       class="btn btn-success m-t m-b order_sub_btn"
                                       title="<?php echo $title; ?>"
                                       data-id="<?php echo $subscription->id; ?>"
                                       data-name="<?php echo $subscription->name; ?>"
                                       data-count="<?php echo $subscription->count; ?>"
                                       data-amount="<?php echo money($subscription->amount); ?>"
                                       data-period="<?php echo $subscription->period; ?>"
                                       data-default-card-id="<?php echo $default_card_id ?? null; ?>"
                                       data-type="package"
                                       data-action="create"
                                        <?php echo $sms_subscriptions_disabled || (!$subscription->active && isSystemUser()) ? 'disabled style="pointer-events: auto;"' : ''; ?>
                                    >
                                        Buy Now
                                    </a>
                                </footer>
                            </section>
                        </div>
                        <?php if (isSystemUser()): ?>
                            <?php $this->load->view(
                                'billing/sms_subscriptions/create_update_subscription_modal',
                                ['updateSubscription' => $subscription]
                            ); ?>
                        <?php endif; ?>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php $this->load->view('billing/sms_subscriptions/order_subscription_modal'); ?>

<script src="<?php echo base_url('assets/js/modules/billing/sms_subscriptions.js?v=1.05'); ?>"></script>
<script src="https://gitcdn.github.io/bootstrap-toggle/2.2.2/js/bootstrap-toggle.min.js"></script>
