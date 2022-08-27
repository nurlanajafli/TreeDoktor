<?php $number = isset($number) && $number ? $number : 0; ?>
<?php $data = $this->input->post(); ?>
<section class="col-lg-3 col-md-6  col-sm-6 col-xs-12 contact-section">
	<section class="panel panel-default p-n" style="overflow: hidden">
		<header class="panel-heading">
			<label class="radio-inline">
				<input type="radio" name="client_print[]" title="Primary" value="<?php echo $number; ?>"<?php if($number == 1 || (isset($client_print) && $client_print && $client_print == $number)) : ?> checked="checked"<?php endif; ?>>
			</label>
			<a href="#" data-toggle="popover" class="contact-title-link" data-html="true" data-placement="right" data-content="<input type='text' class='form-control pull-left' style='width:85%;' id='contact-title' value='Contact #<?php echo $number; ?>'><a class='btn btn-xs btn-success pull-right m-t-xs change-title'><i class='fa fa-check'></i></a><div class='clear'></div>" data-original-title="Change Title <button type='button' class='close pull-right' data-dismiss='popover'>×</button>">
				Contact #<?php echo $number; ?>
			</a>
			<input type="hidden" name="client_title[]" class="client-title-value" value="Contact #<?php echo $number; ?>">
			<div class="pull-right">
				<?php if($number != 1) : ?>
					<a class="btn btn-xs btn-danger btn-rounded delete-contact" style="line-height: 17px;">
						<i class="fa fa-minus"></i>
					</a>
				<?php endif; ?>
				<a class="btn btn-xs btn-success btn-rounded add-contact" style="line-height: 17px;">
					<i class="fa fa-plus"></i>
				</a>
			</div>
		</header>
        <table class="table m-n profile-table">
            <tr>
                <td class="w-150">
                    <label class="control-label">Name:</label>
                </td>
                <td class="p-left-30">
                    <input type="text" name="client_name[]" class="form-control contact-name" value="<?php echo isset($client_name) && $client_name ? $client_name : ''; ?>">
                </td>
            </tr>
            <tr>
                <td class="w-150"><label class="control-label">Phone:</label></td>
                <td class="p-left-30">
                    <input type="text" name="client_phone[]" class="form-control client-phone" value="<?php echo isset($client_phone) && $client_phone ? $client_phone : ''; ?>">
                </td>
            </tr>
            <tr>
                <td>
                    <label class="control-label">Email:</label>
                </td>
                <td class="p-left-30">
                    <input type="text" autocomplete="nope" style="background-color:<?php if(isset($client_email_check) && $client_email_check == 1) : ?>#dff0d8<?php elseif (isset($client_email_check) && $client_email_check === '0') : ?>#ff8181<?php else : ?>#fdff81<?php endif; ?>" name="client_email[]" class="form-control client-email" value="<?php echo isset($client_email) && $client_email ? $client_email : ''; ?>">
                    <input type="hidden" class="client-email-checker" name="client_email_check[]" value="<?php echo isset($client_email_check) ? $client_email_check : ''; ?>">
                </td>
            </tr>
        </table>
	</section>
</section>
