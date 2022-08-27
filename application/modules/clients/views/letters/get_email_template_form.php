<form id="email-template-form" data-type="ajax" data-global="false" data-url="<?php echo base_url('clients/clientLetters/getTemplate'); ?>" data-callback="<?php echo isset($callback)?$callback:''; ?>">
    <?php if(isset($client_data)): ?>
        <input type="hidden" name="client_id" value="<?php echo $client_data->client_id; ?>">
    <?php endif; ?>
    <?php if(isset($estimate_data)): ?>
        <input type="hidden" name="estimate_id" value="<?php echo $estimate_data->estimate_id; ?>">
    <?php elseif(isset($invoice_data)): ?>
        <input type="hidden" name="estimate_id" value="<?php echo $invoice_data->estimate_id; ?>">
    <?php else: ?>
        <input type="hidden" name="estimate_id" value="">
    <?php endif; ?>

    <?php if(isset($workorder_data) && isset($partial_invoice_letter_template_id)): ?>
    <input type="hidden" name="workorder_id" value="<?php echo $workorder_data->id; ?>">
    <?php endif; ?>
    <input type="hidden" name="email_template_id" value="">
    <input type="hidden" name="system_label" value="">
    <input type="hidden" name="task_id" value="">
    <input type="hidden" name="event_id" value="">
    <input type="hidden" name="related_sms_id" value="">

</form>