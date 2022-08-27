<script src="<?php echo base_url(); ?>assets/js/jquery.js"></script>
<script src="<?php echo base_url(); ?>assets/js/libs/currency.min.js"></script>
<script src="<?php echo base_url(); ?>assets/js/common.js?v=<?php echo config_item('js_common'); ?>"></script>
<script src="<?php echo base_url(); ?>assets/js/ccvalidate.js"></script>
<script src="<?php echo base_url(); ?>assets/js/jquery.inputmask.bundle.js"></script>

<style>
    * {
        box-sizing: border-box;
        outline: 0;
        -webkit-user-select: none;
        user-select: none;
        -webkit-tap-highlight-color: rgba(255, 255, 255, 0);
    }

    input, textarea {
        -webkit-user-select: initial;
        user-select: initial;
    }

    html, body {
        background: transparent;
    }

    body {
        margin: 0;
        padding: 0;
        font-family: sans-serif;
        overflow-y: hidden;
        overflow-x: hidden;
        position: relative;
    }

    input {
        border: 1px solid #e3e3e3;
        width: 100%;
        border-radius: 4px;
        height: 38px;
        padding: 0 12px;
        transition: all 0.2s;
        outline: 0;
        -webkit-appearance: none;
        margin-bottom: 15px;
    }

    input:focus {
        border: 1px solid #169148;
    }

    input:disabled {
        background: #F9F9F9;
        opacity: 0.5;
    }

    input._error {
        border: 1px solid #E74C3C;
    }

    .btn {
        display: inline-flex;
        align-items: center;
        line-height: 1;
        justify-content: center;
        border-radius: 4px;
        background: #169148;
        color: #ffffff;
        height: 38px;
        padding: 0 15px;
        transition: all 0.2s;
        cursor: pointer;
        text-transform: uppercase;
        border: none;
    }

    .btn:hover {
        background: #168342;
    }

    .btn:disabled, .btn.disabled {
        opacity: 0.25;
        color: #A4B0BE;
        background: #F9F9F9;
        border: 1px solid #A4B0BE;
        cursor: not-allowed;
    }

    .name-wrapper, .card-number-wrapper, .expiry-wrapper, .cvc-wrapper {
        position: relative;
    }

    .icon {
        position: absolute;
        left: 6px;
        bottom: 26px;
        fill: #A4B0BE;
    }

    .jsCards input {
        padding-left: 36px;
    }

    .jsCards {
        display: grid;
        grid-template-columns: 1fr 1fr;
        column-gap: 15px;
    }
    .card-number-wrapper, .name-wrapper {
        grid-column-start: 1;
        grid-column-end: 3;
    }

    .bill-addr {
        display: grid;
        grid-template-columns: 1fr 1fr;
        column-gap: 15px;
    }
    .bill-addr div:nth-child(3), .bill-addr div:last-child {
        grid-column-start: 1;
        grid-column-end: 3;
    }
    .total-payment-row {
        font-size: 12px;
        font-weight: 400;
        margin-bottom: 15px;
        margin-top: 20px;
    }

    .name-wrapper:before, .card-number-wrapper:before, .expiry-wrapper:before, .cvc-wrapper:before, .input-label {
        display: block;
        margin-bottom: 3px;
        font-size: 80%;
    }

    .name-wrapper:before {
        content: 'Card holder';
    }

    .card-number-wrapper:before {
        content: 'Card number';
    }

    .expiry-wrapper:before {
        content: 'Expiration date';
    }

    .cvc-wrapper:before {
        content: 'CVC code';
    }

    #feedback {
        line-height: 1.4;
        color: #f77070;
    }

    .btn-wrapper {
        margin-top: 15px;
        text-align: center;
    }

    #showBillingInfo, #hideBillingInfo {
        color: #169148;
        margin-bottom: 15px;
        margin-top: 5px;
        cursor: pointer;
        transition: all 0.2s;
    }

    #showBillingInfo:hover, #hideBillingInfo:hover {
        color: #168342;
    }
    #pay-amount {
        text-align: center;
        margin-bottom: 15px;
    }
    #pay-amount input {
        border: none;
        text-align: center;
        border-bottom: 1px solid #c7c7c7;
        border-radius: 0;
        width: 180px;
    }
    #pay-amount input:focus {
        border: none;
        border-bottom: 1px solid #169148 !important;
    }
    #pay-amount .input-label {
        margin-bottom: 6px;
        font-size: 100%;
    }
    #with_tips {
        display: none;
    }
    #pay-amount input { text-align: center !important;}
    .help-block {
        text-align: center;
        display: block;
        margin-top: 5px;
        margin-bottom: 10px;
        color: #a94442;
    }
</style>
<form id="cc_form" data-client-id="<?php echo $client_data['client_id']; ?>"
      data-payment-type="<?php echo $payment_type; ?>"
    <?php if ($invoice) : ?> data-invoice-id="<?php echo $invoice->id ?>"
    <?php elseif (isset($workorder) && $workorder) : ?> data-workorder-id="<?php echo $workorder->id ?>"
    <?php elseif ($estimate) : ?> data-estimate-id="<?php echo $estimate->estimate_id ?>"
    <?php endif; ?> class="">
    <div id="checkout">
        <div class="pay-amount-wrapper">
            <div class="has-feedback" id="pay-amount-bootstrap">
                <div id="pay-amount" data-cmp="<?=_CC_MAX_PAYMENT?>" data-estb="<?=$estimate_balance?>">
                    <?php
                    $maxPayment = (_CC_MAX_PAYMENT <= $estimate_balance) ? _CC_MAX_PAYMENT : $estimate_balance;
                    $val = $estimate_balance;
                    $percents = config_item('percents_estimate_deposit') ?: 10;
                    $minPayment = round(($estimate_balance * $percents / 100), 2);

                    if (!$invoice && (!isset($workorder) || !$workorder) && $estimate && $estimate_balance && $estimate->paid_by_cc < _CC_MAX_CLIENT_PAY_COUNT) {
                        $val = $minPayment;
                    }
                    if ($val > _CC_MAX_PAYMENT) {
                        $val = _CC_MAX_PAYMENT;
                    }
                    ?>
                    <label class="input-label">Payment Amount</label>
                    <input name="amount" id="amount" value="<?= $val; ?>" type="text" style="text-align: right;" placeholder="max. <?php echo money(_CC_MAX_PAYMENT); ?>">
                </div>
                <label class="help-block" for="pay-amount" id="pay-amount-error"></label>
            </div>
        </div>
        <?php echo $driver_form; ?>
    </div>

    <?php if ((float)config_item('cc_extra_fee') > 0) : ?>
        <div class="total-payment-row">
            <?php echo (float)config_item('cc_extra_fee') ?>% transaction fee will be added. Total payment: <span id="with_fee"><?php echo money(round($val + ($val * (float)(config_item('cc_extra_fee') / 100)), 2)); ?></span>
            <span id="with_tips"></span>
        </div>
    <?php endif; ?>

    <input name="tips" id="tips" value="0" type="hidden" />
    <div class="btn-wrapper">
        <button class="btn" type="submit" id="cc-form-submit">
            <?= $invoice ? 'MAKE A PAYMENT' : 'MAKE A DEPOSIT'?>
        </button>
    </div>
</form>
<script>
    var base_url = '<?php echo base_url(); ?>';
    var currency_symbol = '<?php echo config_item('currency_symbol'); ?>';
    var currency_symbol_position = '<?php echo config_item('currency_symbol_position'); ?>';
    var cc_extra_fee = <?php echo (float) config_item('cc_extra_fee') ?>;
    var min_payment_amount = <?= ($estimateConfirmed === false) ? $minPayment : 0; ?>;

    function processCardFormPortal(token, additional) {
        let amount = parseFloat($('#amount').val().replace( /^\D+/g, ''));
        var data = {
            "token": token,
            "crd_name": document.getElementById('crd_name').value,
            "amount": amount,
            "tips": document.getElementById('tips').value,
            "client_id": document.getElementById('cc_form').getAttribute('data-client-id'),
            "estimate_id": document.getElementById('cc_form').getAttribute('data-estimate-id'),
            "invoice_id": document.getElementById('cc_form').getAttribute('data-invoice-id'),
            "workorder_id": document.getElementById('cc_form').getAttribute('data-workorder-id'),
            "type": document.getElementById('cc_form').getAttribute('data-payment-type'),
            "additional": additional
        };
        $.post(base_url + 'payments/client_payment', data, function (resp) {
            if (resp.status == 'ok') {
                parent.postMessage({payed: true, portal_pay: true,}, '*');
            } else {
                parent.postMessage({payed: false, error: true}, '*');
                var xMark = '\u2718';
                var feedback = document.getElementById('feedback');
                var payButton = document.getElementById('cc-form-submit');
                payButton.disabled = false;
                payButton.className = 'btn btn-primary';
                if (typeof feedback !== "undefined" && resp.error) {
                    feedback.innerHTML = xMark + ' ' + resp.error;
                    feedback.classList.add('error');
                }
                if (resp.errors) {
                    $.each(resp.errors, function (index, value) {
                        el = document.getElementById(index);
                        if (typeof el !== "undefined") {
                            showErrorForId(el.parentNode.id, value);
                        }
                    });
                }
            }
            return true;
        }, 'json');
    }

    /**
     * This function change amount field and fee field
     * @param manipulationOnlyWithFee
     */
    const setWithFeeByAmountChange = (manipulationOnlyWithFee) => {

        let amount = parseFloat($('form#cc_form #amount').val().replace( /^\D+/g, ''));

        if (!manipulationOnlyWithFee && amount < min_payment_amount) {
            $('form#cc_form #amount').val(min_payment_amount.toFixed(2)).change();
            amount = min_payment_amount;
        }

        let tips = parseFloat($('form#cc_form #tips').val()) || 0;
        let with_fee = amount + (amount * (cc_extra_fee / 100)) + tips;
        $('form#cc_form #with_fee').text(Common.money(with_fee));
        if (tips > 0) {
            $('form#cc_form #with_tips').text("(included " + tips + "$ crew tips)")
        } else {
            $('form#cc_form #with_tips').text("");
        }
    };

    $(document).ready(function () {
        $('#amount').on("keyup", function (event) {
            setWithFeeByAmountChange(true);
        });

        $('#amount').on("change", function (event) {
            var elId = event.target.parentNode.id;
            setWithFeeByAmountChange();
            if (event.target.value != "") {
                hideErrorForId(elId)
            } else {
                showErrorForId(elId, 'Incorrect Amount');
            }
        });
        $('form#cc_form').on("submit", function (event) {
            let amount = parseFloat($('form#cc_form #amount').val().replace( /^\D+/g, ''));
            if (amount == "") {
                event.preventDefault();
                showErrorForId('pay-amount', 'Incorrect Amount');
            }
        });
    });

    <?php $min = 0; ?>

    Inputmask.extendDefinitions({
        'j': {
            validator: function (chrs) {
                return chrs >= new Date().getYear() - 100;
            },
            cardinality: 2,
            definitionSymbol: "i"
        }
    });

    $('#amount').inputmask("currency", {min: 0, max:<?=$maxPayment?>});

    function hideErrorForId(id) {
        console.log('hideErrorForId: ' + id);

        var element = document.getElementById(id);

        if (element !== null) {
            var errorElement = document.getElementById(id + '-error');
            if (errorElement !== null) {
                errorElement.innerHTML = '';
            }

            var bootStrapParent = document.getElementById(id + '-bootstrap');
            if (bootStrapParent !== null) {
                bootStrapParent.classList.remove('has-error');
                bootStrapParent.classList.add('has-success');
            }
        } else {
            console.log('showErrorForId: Could not find ' + id);
        }
    }

    function showErrorForId(id, message) {
        console.log('showErrorForId: ' + id + ' ' + message);

        var element = document.getElementById(id);

        if (element !== null) {
            var errorElement = document.getElementById(id + '-error');
            if (errorElement !== null) {
                errorElement.innerHTML = message;
            }

            var bootStrapParent = document.getElementById(id + '-bootstrap');
            if (bootStrapParent !== null) {
                bootStrapParent.classList.add('has-error');
                bootStrapParent.classList.remove('has-success');
            }
        } else {
            console.log('showErrorForId: Could not find ' + id);
        }
    }

    function errorMessage(msg) {
        $('body').append('<div class="alert alert-danger alert-message" id="errorMessage" style="display:none; left: auto; right: 20px;"><button type="button" class="close m-l-sm" data-dismiss="alert">×</button><strong>' + msg + '</strong></div>');
        $('#errorMessage').fadeIn();
        setTimeout(function () {
            $('#errorMessage').fadeOut(function () {
                $('#errorMessage').remove();
            });
        }, 10000);
    }

    function successMessage(msg) {
        if ($('#successMessage').length)
            $('#successMessage').remove();
        $('body').append('<div class="alert alert-success alert-message" id="successMessage" style="display:none; left: auto; right: 20px;"><button type="button" class="close m-l-sm" data-dismiss="alert">×</button><strong>' + msg + '</strong></div>');
        $('#successMessage').fadeIn();
        setTimeout(function () {
            $('#successMessage').fadeOut(function () {
                $('#successMessage').remove();
            });
        }, 10000);
    }

    $(document).ready(function () {

        var resizeObserver = new ResizeObserver(entries => {
            parent.postMessage({
                resize: true,
                portal_pay: true,
                height: $('body').height(),
                width: $('body').width()
            }, '*')
        });
        resizeObserver.observe(document.body);

        // Andrew
        $('#bill-addr').hide();
        $('#hideBillingInfo').hide();
        //end
        var paymentDriver = "authorize";
        if ($('#driver_' + paymentDriver)) {
            $('#driver_' + paymentDriver).show();
        }

        $('form#cc_form #tips').on("keyup", function (event) {
            let tips = parseFloat($(this).val()) || 0;
            let amount = parseFloat($('form#cc_form #amount').val().replace( /^\D+/g, ''));
            let with_fee = amount + (amount * (cc_extra_fee / 100)) + tips;
            $('form#cc_form #with_fee').text(with_fee.toFixed(2));
            if (tips > 0) {
                $('form#cc_form #with_tips').text("(included " + tips + "$ crew tips)")
            } else {
                $('form#cc_form #with_tips').text("");
            }
        });
    });
    //andrew
    $('#showBillingInfo').click(function () {
        $('#bill-addr').show();
        $('#hideBillingInfo').show();
        $('#showBillingInfo').hide();
    })
    $('#hideBillingInfo').click(function () {
        $('#bill-addr').hide();
        $('#showBillingInfo').show();
        $('#hideBillingInfo').hide();
    })
    //end
</script>
