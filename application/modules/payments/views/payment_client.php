<?php
/**
 * @var stdClass|bool $invoice
 * @var stdClass|bool $estimate
 * @var stdClass $client_data
 * @var stdClass $lead_data
 * @var stdClass $invoice_paid_status
 * @var array $client_contact
 * @var float $estimate_balance
 * @var string $card_form
 */
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <?php if ($invoice) : ?>
        <title>Online Invoice Payment</title>
    <?php else : ?>
        <title>Online <?php echo config_item('estimate_label') ?: 'Estimate'; ?> Deposit</title>
    <?php endif; ?>
    <meta name="description" content="">
    <meta name="author" content="Main Prospect">

    <!-- CSS -->
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/vendors/notebook/css/bootstrap.css">
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/vendors/notebook/css/app.css?v=<?php echo config_item('app.css'); ?>">
    <link rel="stylesheet"
          href="<?php echo base_url(); ?>assets/<?php echo $this->config->item('company_dir'); ?>/css/estimate_payment.css"
          type="text/css">
    <script>
        var base_url = '<?php echo base_url(); ?>';
        var currency_symbol = '<?php echo config_item('currency_symbol'); ?>';
        var currency_symbol_position = '<?php echo config_item('currency_symbol_position'); ?>';
    </script>
    <script src="<?php echo base_url(); ?>assets/js/jquery.js"></script>

</head>
<?php
$default_img = 'assets/'.$this->config->item('company_dir').'/print/header.png';
$brand_id = get_brand_id(isset($estimate)?$estimate:[], isset($client_data)?$client_data:[]);
$payment_logo = get_brand_logo($brand_id, 'payment_logo_file', $default_img);

$payment_watermark = get_brand_logo($brand_id, 'watermark_logo_file', false);
$payment_left_side = get_brand_logo($brand_id, 'estimate_left_side_file', false);
?>
<body class="col-md-12" style="overflow-y:auto;">
<!--Credit Card Container -->
<div class="col-md-3"></div>
<div class="m-t-sm filled_white overflow col-md-6 payment-conteiner"
     style="margin-top: 4%!important; margin-bottom: 4%!important; box-shadow: 0 0 10px rgba(0,0,0,0.5);background-image: url('<?php echo $payment_left_side; ?>');">


    <div class="holder text-center"><img
                src="<?php echo base_url($payment_logo); ?>"
                width="100%" style="width: auto;float: right;margin-bottom: -70px; max-width: 180px;<?php //echo $this->config->item('payment_logo_styles'); ?>"></div>
    <div class="clear"></div>
    <div class="estimate_number">

        <div class="h3 text-center">
            <?php if ($invoice) : ?>
                <div class="estimate_number_1">Invoice #</div>
                <div class="estimate_number_2">
                    <a target="_blank" class="text-ul"
                       href="<?php echo base_url('payments/invoice/' . md5($invoice->id . $invoice->client_id)); ?>"><?php echo $invoice->invoice_no; ?></a>
                </div>
            <?php elseif (isset($workorder) && $workorder) : ?>
                <div class="estimate_number_1">Partial Invoice #</div>
                <div class="estimate_number_2">
                    <a target="_blank" class="text-ul"
                       href="<?php echo base_url('payments/invoice/' . md5($workorder->workorder_no . $workorder->client_id)); ?>"><?php echo str_replace('-E', '-I', $estimate->estimate_no); ?></a>
                </div>
            <?php elseif ($estimate) : ?>
                <div class="estimate_number_1"><?php echo config_item('estimate_label') ?: 'Estimate'; ?> #</div>
                <div class="estimate_number_2">
                    <a target="_blank" class="text-ul"
                       href="<?php echo base_url('payments/estimate/' . md5($estimate->estimate_no . $estimate->client_id)); ?>"><?php echo $estimate->estimate_no; ?></a>
                </div>
            <?php endif; ?>
            <div class="clear"></div>
        </div>
    </div>


    <div class="title">Client Information</div>
    <div class="col-md-9 p-n">
        <div class="data">
            <!-- CLIENT INFO -->
            <table cellpadding="0" cellspacing="0" border="0" class="client_table">
                <tr>
                    <td>Client:</td>
                    <td><?php echo $client_data['client_name']; ?></td>
                </tr>
                <tr>
                    <td>Client Address:</td>
                    <td><?php echo $client_data['client_address'] . ", " . $client_data['client_city'] . " " . $client_data['client_state'] . " " . $client_data['client_zip']; ?></td>
                </tr>
                <tr>
                    <td>Client Phone:</td>
                    <td><?php echo numberTo($client_data['primary_contact']['cc_phone']); ?></td>
                </tr>
                <?php if (!empty($client_data['primary_contact']) && !empty($client_data['primary_contact']['cc_email'])) : ?>
                    <tr>
                        <td>Client Email:</td>
                        <td><?php echo $client_data['primary_contact']['cc_email']; ?></td>
                    </tr>
                <?php endif; ?>
                <?php if ($client_data['client_address'] != $lead_data->lead_address) : ?>
                    <tr>
                        <td>Job Site Location:</td>
                        <td><?php echo $lead_data->lead_address . " " . $lead_data->lead_city; ?></td>
                    </tr>
                <?php endif; ?>
                <?php if (!empty($client_data['primary_contact']) && $client_data['client_name'] != $client_data['primary_contact']['cc_name']) : ?>
                    <tr>
                        <td>Job Site Contact:</td>
                        <td><?php echo $client_data['primary_contact']['cc_name']; ?></td>
                    </tr>
                <?php endif; ?>
            </table>
            <!-- CLIENT INFO -->
        </div>
    </div>
    <div class="col-md-3 text-right">
        <?php if (config_item('processing')) : ?>
            <img src="<?php echo base_url(config_item('custom_credit_cards_logos') ?: 'assets/img/creditcards.png'); ?>" style="max-width: 100%;"/>
        <?php endif; ?>
    </div>


    <div class="m-lg" style="margin-left: 40px;">

        <!-- Online Payment Title-->
        <div class="col-md-12 text-center">
            <?php if (!$invoice && !$workorder && $estimate_balance > 0) : ?>
                <h3>
                    <?php $percents = config_item('percents_estimate_deposit') ?: 10; ?>
                    In order to confirm this <?php echo config_item('estimate_label') ? strtolower(config_item('estimate_label')) : 'estimate'; ?> we would need a <?php echo $percents; ?>% deposit<br>
                    <a target="_blank"
                       href="<?php echo base_url('payments/estimate/' . md5($estimate->estimate_no . $estimate->client_id)); ?>">
                        <u>Press here for a copy of the <?php echo config_item('estimate_label') ? strtolower(config_item('estimate_label')) : 'estimate'; ?></u>
                    </a>
                </h3>
            <?php elseif ($estimate_balance > 0) : ?>
                <h3>
                    Thank you for choosing <?php echo $this->config->item('company_name_short'); ?>.<br>
                    <?php if (config_item('processing')) : ?>
                        Please enter your billing information below to pay the invoice.<br>
                    <?php endif; ?>

                    <?php if(isset($invoice) && $invoice) : ?>
                        <a target="_blank"
                           href="<?php echo base_url('payments/invoice/' . md5($invoice->invoice_no . $invoice->client_id)); ?>">
                            <u>Press here for a copy of the invoice</u>
                        </a>
                    <?php elseif(isset($workorder) && $workorder) : ?>
                        <a target="_blank"
                           href="<?php echo base_url('payments/invoice/' . md5($workorder->workorder_no . $workorder->client_id)); ?>">
                            <u>Press here for a copy of the partial invoice</u>
                        </a>
                    <?php endif; ?>
                </h3>
            <?php endif; ?>
        </div>
        <div class="clear"></div>

        <div class="row m-t m-l-none">
            <div class="h1 text-center" id="balance">
                <?php if ($invoice && $estimate_balance > 0) : ?>
                    Invoice Balance <strong><?php echo money($estimate_balance); ?></strong>
                <?php elseif (isset($workorder) && $workorder && $estimate_balance > 0) : ?>
                    Partial Invoice Balance <strong><?php echo money($estimate_balance); ?></strong>
                <?php elseif ($estimate && !$invoice && $estimate_balance > 0) : ?>
                    <?php echo config_item('estimate_label') ?: 'Estimate'; ?> Balance <strong><?php echo money($estimate_balance); ?></strong>
                <?php else : ?>
                    <strong>Paid In Full</strong>
                <?php endif; ?>
            </div>
        </div>

        <?php if (config_item('processing')) : ?>

            <?php if ($invoice && $invoice->in_status == $invoice_paid_status->invoice_status_id) : ?>
                <div class="alert alert-success m-l m-t-lg text-center" style="font-size: 14px; margin-right: -10px;">
                    Thank you for your payment. Your invoice is paid now. You can download a copy of your paid inovice
                    by clicking at the link above.<br> Thank you for
                    choosing <?php echo $this->config->item('company_name_short'); ?> and have a great day!
                </div>
            <?php elseif (($invoice && $invoice->paid_by_cc >= _CC_MAX_CLIENT_PAY_COUNT) || (!$invoice && $estimate && $estimate->paid_by_cc >= _CC_MAX_CLIENT_PAY_COUNT)) : ?>
                <div class="alert alert-danger m-t-lg text-center" style="font-size: 14px; margin-left: 40px;">
                    The payment with credit card has already been made. You can pay the remaining of the invoice with a
                    Check, Cash, E-transfer payable to (<?php echo $this->config->item('account_email_address'); ?>) or
                    via Debit card in our offices. Thank you.
                </div>
            <?php elseif ($estimate_balance > 0) : ?>
                <div class="row m-l-none">
                    <?php echo $card_form ?? null; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
        <div class="col-md-12 text-center m-b-lg">
            <?php if ($invoice && $estimate_balance > 0) : ?>
                <h3>Thank you for your business. We will contact you shortly</h3>
            <?php elseif (isset($workorder) && $workorder && $estimate_balance > 0) : ?>
                <h3>Thank you for your business. We will contact you shortly</h3>
            <?php endif; ?>
            <?php if(config_item('company_dir') == 'treedoctors') : ?>
            <div class="m-t"><img
                        src="<?php echo base_url(); ?>assets/<?php echo $this->config->item('company_dir'); ?>/print/reviews.png"
                        width="100%"></div>
            <?php endif; ?>
        </div>
    </div>
</div>
<div class="col-md-3"></div>

<?php $this->load->view('includes/partials/preloader_modal'); ?>

<script src="<?php echo base_url(); ?>assets/js/libs/currency.min.js"></script>
<script src="<?php echo base_url(); ?>assets/vendors/notebook/js/bootstrap.js"></script>
<script src="<?php echo base_url(); ?>assets/js/jquery.inputmask.bundle.js"></script>
<script src="<?php echo base_url(); ?>assets/js/common.js?v=<?php echo config_item('js_common'); ?>"></script>
<!--Credit Card Container -->
<script>
    var baseUrl = "<?php echo base_url(); ?>";

    function processCardForm(token, additional) {
        var data = {
            "token": token,
            "crd_name": document.getElementById('crd_name').value,
            "amount": document.getElementById('amount').value,
            "tips": document.getElementById('tips').value,
            "client_id": document.getElementById('cc_form').getAttribute('data-client-id'),
            "estimate_id": document.getElementById('cc_form').getAttribute('data-estimate-id'),
            "invoice_id": document.getElementById('cc_form').getAttribute('data-invoice-id'),
            "workorder_id": document.getElementById('cc_form').getAttribute('data-workorder-id'),
            "type": document.getElementById('cc_form').getAttribute('data-payment-type'),
            "additional": additional
        };
        $('#processing-modal').modal();
        $.post(baseUrl + 'payments/client_payment', data, function (resp) {

            if (resp.status == 'ok') {
                successPayment(resp);
            } else {
                var xMark = '\u2718';
                var feedback = document.getElementById('feedback');
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
            $('#processing-modal').modal('hide');
            return true;
        }, 'json');
    }

    $(document).ready(function () {
        $('#amount').on("change", function (event) {
            var elId = event.target.parentNode.id;
            if (event.target.value != "") {
                hideErrorForId(elId)
            } else {
                showErrorForId(elId, 'Incorrect Amount');
            }
        });
        $('form#cc_form').on("submit", function (event) {
            if ($('#amount').val() == "") {
                event.preventDefault();
                showErrorForId('pay-amount', 'Incorrect Amount');
            }
        });
    });
    <?php
    $min = 0.01;
    //if(!$invoice && $estimate && $estimate_balance > 0 && !$estimate->paid_by_cc)
    //$min = round(($estimate_balance * 10 / 100), 2);
    ?>
    Inputmask.extendDefinitions({
        'j': {
            validator: function (chrs) {
                return chrs >= new Date().getYear() - 100;
            },
            cardinality: 2,
            definitionSymbol: "i"
        }
    });
    $('#amount').inputmask("decimal", {min:<?php echo $min; ?><?php if(_CC_MAX_PAYMENT != 0) echo ", max:"._CC_MAX_PAYMENT; ?>});

    function successPayment(resp) {
        if (resp.status == 'ok') {
            successMessage('Your payment is successfully processed');
            $('#cc_form').find('button').replaceWith('<a target="_blank" href="' + resp.file + '" class="btn btn-lg btn-info">Payment Receipt</a>');
            $('#cc_form').parent().append('<div class="col-md-12 col-xs-12 text-center"><h3>Thank you for your business. We will contact you shortly</h3></div>');
            $('#cc_form').trigger('reset');
            $('#checkout').hide();
            if (resp.total)
                $('#balance strong').html(Common.money(resp.total));
        }
        return true;
    }

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
    };

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
    };

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
</script>
<style>
    body {
        height: 100vh;
    }

    #amount {
        text-align: center !important;
    }

    #cc_form #feedback {
        width: 100%;
    }
</style>
</html>
