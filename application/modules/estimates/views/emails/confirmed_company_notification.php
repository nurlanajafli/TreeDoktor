This is the notifying email that your customer has been confirmed the estimate <a target="_blank" href="<?php echo base_url() . $estimate->estimate_no; ?>"><?php echo $estimate->estimate_no; ?></a>
online by electronic signature.<br>
Customer: <a target="_blank" href="<?php echo base_url() . $estimate->client_id; ?>"><?php echo ($estimate->cc_name)?$estimate->cc_name:$estimate->client_name; ?></a><br>
Address: <?php echo $estimate->lead_address; ?><br>Email: <?php echo $estimate->cc_email; ?><br>Please follow up and contact him shortly!