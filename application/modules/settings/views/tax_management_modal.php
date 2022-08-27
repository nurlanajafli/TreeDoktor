<?php foreach ($client_contacts as $key => $value) : ?>
    <?php $value['key'] = $key; ?>
    <table class="m-r-lg m-t-xs contact-table" data-cc-id="<?php echo $value['cc_id']; ?>">
        <tr>
            <td class="text-left">
                <a href="#" data-name="client_cont" data-value="{cc_title:'<?php echo $value['cc_title']; ?>',cc_name:'<?php echo $value['cc_name'];?>',cc_phone:'<?php echo $value['cc_phone'];?>',cc_email:'<?php echo $value['cc_email'];?>',cc_client_id:'<?php echo $value['cc_client_id'];?>'}" data-placement="right" data-type="contact" data-pk="<?php echo $value['cc_id']; ?>" class="client_contact" title="Client Contact" data-url="<?php echo base_url('clients/ajax_save_client_contact'); ?>">

                    <strong><?php echo $value['cc_title'] ? $value['cc_title'] : 'Contact #' . ($key + 1); ?></strong></a>

                <label class="radio-inline m-l-xs" title="Change Primary Contact">
                    <input type="radio" class="primary-contact" name="primary_contact" value="<?php echo $value['cc_id']; ?>" data-client-id="<?php echo $value['cc_client_id']; ?>"<?php if($value['cc_print']) : ?> checked="checked"<?php endif; ?>>
                </label>
            </td>
        </tr>
        <tr>
            <td>
                <?php if($value['cc_name']) : ?>
                    <span class="contact-name"><?php echo $value['cc_name']; ?></span>
                    <br>
                <?php endif; ?>

                <?php if($value['cc_phone']) : ?>

                    <a href="#" class="<?php if($value['cc_phone'] == numberTo($value['cc_phone'])) : ?>text-danger<?php else : ?>createCall<?php endif;?>" data-client-id="<?php echo $value['cc_client_id']; ?>" data-number="<?php echo substr($value['cc_phone'], 0, 10); ?>"><?php echo numberTo($value['cc_phone']);?></a>
                    <br>
                <?php endif; ?>

                <?php if($value['cc_email']) : ?>

                    <a href="#" data-email="<?php echo $value['cc_email']; ?>" onclick="checkEmail('<?php echo $value['cc_email']; ?>', <?php echo $value['cc_email_check']; ?>)" class="text-<?php if($value['cc_email_check'] == 1) : ?>success<?php elseif($value['cc_email_check'] === '0') : ?>danger<?php else : ?>warning<?php endif; ?>"><?php echo $value['cc_email']; ?></a>
                <?php endif; ?>
            </td>
        </tr>
    </table>

<?php endforeach; ?>
