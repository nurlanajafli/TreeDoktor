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
<div id="card-form" data-customer-id="<?php echo $customer_id; ?>" data-payment-driver="<?php echo $payment_driver; ?>" class="modal fade"
     tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="z-index: 10001;">
    <div class="modal-dialog">
        <div class="modal-content panel panel-default p-n">
            <header class="panel-heading">Credit Card for&nbsp;<?php echo $customer_name; ?></header>
            <form id="cc_form" class="form-inline text-center" autocomplete="off">
                <div class="modal-body p-10">
                    <?php echo $driver_form; ?>
                </div>
                <div class="modal-footer">
                    <button type="reset" class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>
                    <button id="cc-form-submit" class="btn btn-info" type="submit" style="30px"><span
                                class="btntext">Save</span>
                        <img src="<?php echo base_url(); ?>assets/img/ajax-loader.gif" style="display: none;width: 84px;"
                             class="preloader">
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<input id="payment_driver" type="hidden" value="<?php echo $payment_driver; ?>">

<?php $jsFile = isset($internal_payment) && $internal_payment === true ? 'card_form_internal_payment.js?v=1.00' : 'card_form_client_payment.js?v=1.00'; ?>

<script src="<?php echo base_url('assets/js/libraries/payment/' . $jsFile); ?>"></script>
