<?php foreach ($sms_subscriptions as $key => $subscription): ?>
    <?php if ($subscription->amount > 0): ?>
        <?php $href = $sms_subscriptions_disabled ? '#' : '#order_subscription_modal'; ?>
        <?php $title = $sms_subscriptions_disabled ? 'Subscription unavailable' : 'Add ' . $subscription->name . ' subscription'; ?>
        <li>
            <a href="<?php echo $href; ?>" data-toggle="modal"
               class="<?php echo $sms_subscriptions_disabled ? 'text-muted' : ''; ?>"
               title="<?php echo $title; ?>"
               data-id="<?php echo $subscription->id; ?>"
               data-name="<?php echo $subscription->name; ?>"
               data-count="<?php echo $subscription->count; ?>"
               data-amount="<?php echo money($subscription->amount); ?>"
               data-period="<?php echo $subscription->period; ?>"
               data-type="<?php echo $type; ?>"
               data-description="<?php echo $sub_description ?? null; ?>"
               data-default-card-id="<?php echo $default_card_id ?? null; ?>"
               data-action="create"
                <?php echo $sms_subscriptions_disabled ? 'disabled style="pointer-events: auto;"' : ''; ?>
            >
                Add <span class="font-semibold"><?php echo $subscription->name; ?></span> subscription
            </a>
        </li>
    <?php endif; ?>
<?php endforeach; ?>