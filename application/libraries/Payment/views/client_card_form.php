<style type="text/css">
    #cc_form {
        margin: 10px;
    }
    #cc_form iframe {
        height: 34px;
    }

    #cc_form .form-control {
        width: 100%;
    }

    #cc_form .has-success .form-control,
    #cc_form .has-error .form-control {
        box-shadow: none;
        -webkit-box-shadow: none;
    }

    #cc_form #feedback {
        text-align: left;
    }

    #cc_form #feedback.error {
        color: red;
    }

    #cc_form #feedback.success {
        color: seagreen;
    }

    .has-feedback .form-control {
        padding-right: 0;
    }
</style>
<div class="col-md-3 col-xs-2">&nbsp;</div>
<form id="cc_form" data-client-id="<?php echo $client_data['client_id']; ?>"
      data-payment-type="<?php echo $payment_type; ?>"
    <?php if ($invoice) : ?> data-invoice-id="<?php echo $invoice->id ?>"
    <?php elseif (isset($workorder) && $workorder) : ?> data-workorder-id="<?php echo $workorder->id ?>"
    <?php elseif ($estimate) : ?> data-estimate-id="<?php echo $estimate->estimate_id ?>"
    <?php endif; ?> class="form-inline text-center col-md-6 col-xs-8 m-t-lg">
    <div id="checkout">
        <label class="row text-center h4 control-label" for="amount">Payment Amount
            <?php if (_CC_MAX_PAYMENT != 0) : ?>
                (max. <?php echo money(_CC_MAX_PAYMENT); ?>)
            <?php endif;?>
        </label>
        <div class="form-group col-xs-12 has-feedback" id="pay-amount-bootstrap">
            <div id="pay-amount" class="form-control" style="width: 100%">
                <?php
                    $val = $estimate_balance;
                    $percents = config_item('percents_estimate_deposit') ?: 10;
                    if (!$invoice && (!isset($workorder) || !$workorder) && $estimate && $estimate_balance && $estimate->paid_by_cc < _CC_MAX_CLIENT_PAY_COUNT) {
                        $val = round(($estimate_balance * $percents / 100), 2);
                    }
                    if($val > _CC_MAX_PAYMENT) {
                        $val = _CC_MAX_PAYMENT;
                    }
                ?>
                <input name="amount" id="amount" value="<?php
                echo $val; ?>" type="text"
                       class="form-control text-center"
                       style="box-sizing: border-box; width: 100%; height: 100%; border: none; overflow: visible; background-color: transparent;">
            </div>
            <?php
            if ((float) config_item('cc_extra_fee') > 0) : ?>
                <small class="block text-left">
                    <div>+ <?php echo (float) config_item('cc_extra_fee') ?>% transaction fee</div>
                    Total Payment:
                    <span id="with_fee" class="font-bold"><?php echo money(round($val + ($val * (float) (config_item('cc_extra_fee') / 100)), 2)); ?></span>
                    <span id="with_tips"></span>
                </small>
            <?php
            endif; ?>
            <label class="help-block" for="pay-amount" id="pay-amount-error"></label>
        </div>

        <?php
        echo $driver_form; ?>
        <div class="form-group col-xs-12 has-feedback hide" id="pay-amount-bootstrap">
            <label class="block text-left control-label">Crew Tips</label>
            <div id="pay-tips" class="form-control" style="width: 100%">
                <input name="tips" id="tips" value="0" type="text"
                       class="form-control text-center"
                       style="box-sizing: border-box; width: 100%; height: 100%; border: none; overflow: visible; background-color: transparent;">
            </div>
            <label class="help-block" for="pay-amount" id="pay-amount-error"></label>

        </div>
    </div>

    <div class="text-center m-t">
        <button type="submit" class="btn btn-lg btn-success" id="cc-form-submit">MAKE A PAYMENT
        </button>

    </div>
</form>
<div class="col-md-2">&nbsp;</div>
<script>
    var cc_extra_fee = <?php echo (float) config_item('cc_extra_fee') ?>;
    $(document).ready(function () {
        var paymentDriver = "<?php echo $payment_driver; ?>";
        if ($('#driver_' + paymentDriver)) {
            $('#driver_' + paymentDriver).show();
        }
        if ($('form#cc_form #with_fee').length != 0) {
            $('form#cc_form #amount').on("keyup", function (event) {
                let amount = parseFloat($(this).val()) || 0;
                let tips = parseFloat($('form#cc_form #tips').val()) || 0;
                let with_fee = amount + (amount * (cc_extra_fee / 100)) + tips;
                $('form#cc_form #with_fee').text(Common.money(with_fee.toFixed(2)));
                if (tips > 0) {
                    $('form#cc_form #with_tips').text("(included " + tips + "$ crew tips)")
                } else {
                    $('form#cc_form #with_tips').text("");
                }
            });
        }
        $('form#cc_form #tips').on("keyup", function (event) {
            let tips = parseFloat($(this).val()) || 0;
            let amount = parseFloat($('form#cc_form #amount').val()) || 0;
            let with_fee = amount + (amount * (cc_extra_fee / 100)) + tips;
            $('form#cc_form #with_fee').text(with_fee.toFixed(2));
            if (tips > 0) {
                $('form#cc_form #with_tips').text("(included " + tips + "$ crew tips)")
            } else {
                $('form#cc_form #with_tips').text("");
            }
        });
    });
</script>
