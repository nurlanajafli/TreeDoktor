<?php if ($sms_subscriptions->count()): ?>
    <div class="row m-b-xl">
        <?php foreach ($sms_subscriptions as $key => $subscription): ?>
            <div class="col-sm-4 animated <?php echo $key === 0 ? 'fadeInLeftBig' : ($key === 1 ? 'fadeInUp' : 'fadeInRightBig'); ?>">
                <section class="panel text-center <?php echo $key%2 ? 'b-primary' : 'b-light m-t'; ?>">
                    <header class="panel-heading pos-rlt <?php echo $key%2 ? 'bg-primary' : 'bg-white b-light'; ?>">
                        <h3 class="m-t-sm"><?php echo $subscription->name; ?></h3>
                        <p><?php echo $subscription->count . ' SMS per ' . $subscription->period; ?></p>

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
                                <span class="text-danger font-bold h1">
                                    <?php echo money($subscription->amount); ?>
                                </span> / <?php echo $subscription->period; ?>
                            <?php if ($key%2): ?></div><?php endif; ?>
                        </li>
                        <li class="list-group-item sub-description-text"><?php echo $subscription->description; ?></li>
                    </ul>
                    <footer class="panel-footer">
                        <?php $href = $sms_subscriptions_disabled ? '#' : '#order_subscription_modal'; ?>
                        <?php $title = $sms_subscriptions_disabled ? 'Subscription unavailable' : 'Order subscription'; ?>
                        <a href="<?php echo $href; ?>" data-toggle="modal"
                           class="btn btn-primary m-t m-b order_sub_btn"
                           title="<?php echo $title; ?>"
                           data-id="<?php echo $subscription->id; ?>"
                           data-name="<?php echo $subscription->name; ?>"
                           data-count="<?php echo $subscription->count; ?>"
                           data-amount="<?php echo money($subscription->amount); ?>"
                           data-period="<?php echo $subscription->period; ?>"
                           data-action="create"
                           data-cards='<?php echo json_encode($cards); ?>'
                           <?php echo $sms_subscriptions_disabled ? 'disabled style="pointer-events: auto;"' : ''; ?>
                        >
                            Order
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
        <?php endforeach; ?>
    </div>
<?php endif; ?>
