<link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/beautify-json.css" type="text/css">
<dl>
    <dt>ID:</dt>
    <dd><?php echo $transaction->payment_transaction_id; ?></dd>
    <dt>STATUS:</dt>
    <dd><?php echo $transaction->payment_transaction_status; ?></dd>
    <dt>Remote ID:</dt>
    <dd><?php echo $transaction->payment_transaction_remote_id; ?></dd>
    <dt>AMOUNT:</dt>
    <dd><?php echo $transaction->payment_transaction_amount; ?></dd>
    <dt>ORDER NO:</dt>
    <dd><?php echo $transaction->payment_transaction_order_no; ?></dd>
    <dt>CARD TYPE:</dt>
    <dd><?php echo $transaction->payment_transaction_card; ?></dd>
    <dt>CARD NUM:</dt>
    <dd><?php echo $transaction->payment_transaction_card_num; ?></dd>
    <dt>DATE:</dt>
    <dd><?php echo $transaction->payment_transaction_date; ?></dd>
    <dt>MESSAGE:</dt>
    <dd><?php echo $transaction->payment_transaction_message; ?></dd>
    <dt>AUTH CODE:</dt>
    <dd><?php echo $transaction->payment_transaction_auth_code; ?></dd>
    <dt>SETTLED AMOUNT:</dt>
    <dd><?php echo $transaction->payment_transaction_settled_amount; ?></dd>
    <?php if(isAdmin()) : ?>
    <dt>LOG:</dt>
    <dd class="json-beautify" style="overflow-y: scroll;"><?php echo $transaction->payment_transaction_log; ?></dd>
    <?php endif; ?>
    </dl>
<script>
    $(document).ready(function () {
        let script = document.createElement('script');
        script.src = "<?php echo base_url(); ?>assets/js/jquery.beautify-json.js";
        document.getElementById('payment_details').append(script);
        script.onload = function () {
            $('.json-beautify').each(function(idx,el){
                $(el).beautifyJSON({
                    // enable colors
                    color: true

                });
            })
        };
    });
</script>
