<?php $freeSubscription = null; ?>
<section class="sms-orders panel panel-default p-n">
    <header class="panel-heading">
        SMS orders
        <?php if (isSystemUser()): ?>
            <?php $freeSubHref = isset($free_subscription) ? 'update_subscription_modal_' . $free_subscription->id : 'create_subscription_modal'; ?>
            <a id="add_free_subscription_btn"
               href="#<?php echo $freeSubHref; ?>"
               class="btn btn-xs btn-success pull-right"
               role="button"
               data-toggle="modal"
               title="<?php echo isset($free_subscription) ? 'Update' : 'Add' ?> free subscription"
            >
                <i class="fa fa-plus"></i><i class="fa fa-calendar-o"></i>
            </a>
        <?php endif; ?>
    </header>

    <table class="table table-striped">
        <thead>
            <tr>
                <th>Subscription</th>
                <th>Count</th>
                <th>Remain</th>
                <th>From</th>
                <th>To</th>
                <th>Created</th>
                <th>Updated</th>
                <th>Renewal</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($sms_orders->count()): ?>
                <?php foreach ($sms_orders as $order): ?>
                    <?php $subscriptionName = $order->subscription->name; ?>
                    <tr>
                        <td><?php echo $subscriptionName; ?></td>
                        <td><?php echo $order->count; ?></td>
                        <td><?php echo $order->remain; ?></td>
                        <td><?php echo $order->from; ?></td>
                        <td><?php echo $order->to; ?></td>
                        <td><?php echo getDateTimeWithDate($order->created_at, 'Y-m-d H:i:s', true); ?></td>
                        <td><?php echo getDateTimeWithDate($order->updated_at, 'Y-m-d H:i:s', true); ?></td>
                        <td>
                            <?php if (!$order->subscription->next_date && !$order->subscription->on_out_limit): ?>
                                &mdash;
                            <?php else: ?>
                                <div class="text-success">
                                    <?php if ($order->subscription->next_date): ?>
                                        <i class="fa fa-calendar m-right-10" title="Automatic renewal at the end of the period"></i>
                                    <?php endif; ?>
                                    <?php if ($order->subscription->on_out_limit): ?>
                                        <i class="fa fa-circle-o" title="Automatic renewal when the limit is reached"></i>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($order->subscription->amount > 0 || isSystemUser()): ?>
                                <a href="#order_subscription_modal"
                                   role="button"
                                   class="btn btn-xs btn-default"
                                   data-toggle="modal"
                                   title="Edit subscription"
                                   data-id="<?php echo $order->id; ?>"
                                   data-name="<?php echo $subscriptionName; ?>"
                                   data-count="<?php echo $order->count; ?>"
                                   data-remain="<?php echo $order->remain; ?>"
                                   data-from="<?php echo $order->from; ?>"
                                   data-to="<?php echo $order->to; ?>"
                                   data-amount="<?php echo money($order->subscription->amount); ?>"
                                   data-next-date="<?php echo $order->subscription->next_date; ?>"
                                   data-on-out-limit="<?php echo $order->subscription->on_out_limit; ?>"
                                   data-cards='<?php echo json_encode($cards); ?>'
                                   data-default-card-id="<?php echo $default_card_id; ?>"
                                   data-action="update"
                                >
                                    <i class="fa fa-pencil"></i>
                                </a>
                            <?php endif; ?>
                            <?php if ($order->subscription->amount == 0 && isSystemUser()): ?>
                                <?php $freeSubscription = $order; ?>
                                <a href="#delete_order_subscription_modal"
                                   role="button"
                                   class="btn btn-xs btn-danger"
                                   data-toggle="modal"
                                   title="Delete order or subscription"
                                   data-action="delete"
                                >
                                    <i class="fa fa-trash-o"></i>
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="9">No records found</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    <?php if (isSystemUser() && $freeSubscription): ?>
        <?php $this->load->view(
            'messaging/subscriptions/delete_order_subscription_modal',
            ['freeSubscription' => $freeSubscription]
        ); ?>
    <?php endif; ?>
</section>
