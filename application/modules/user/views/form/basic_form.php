<input type="hidden" name="worker_type" value="<?php echo worker_type((isset($user_row)) ? $user_row : []); ?>"
       xmlns:text-align="http://www.w3.org/1999/xhtml">
<div class="form-group">
	<div class="row">
		<div class="col-lg-6 col-md-6 col-sm-6">
			<label class="control-label">User type <span style="color:red">*</span></label>
			<div>
				<?php if ($this->session->userdata('user_type') == "admin") : ?>
				<?php
					$options = ['' => 'Select','admin' => 'Admin', 'user' => 'User'];
					$selected = "";
					if (isset($edit)) $selected = $user_row->user_type;
					elseif($this->input->post('selectusertype')) $selected = $this->input->post('selectusertype');
					echo form_dropdown('selectusertype', $options, $selected, 'class="form-control" style="display:inline-block;"');
					?>
				<?php else : ?>
					<label class="control-label">
						<?php echo isset($user_row->user_type) ? $user_row->user_type : '-'; ?>
						<input type="hidden" name="selectusertype" value="<?php echo isset($user_row->user_type) ? $user_row->user_type : 'user'; ?>">
					</label>
				<?php endif;  ?>
			</div>
		</div>
		<div class="col-lg-6 col-md-6 col-sm-6">
			<label class="control-label">Active Status:</label>
			<div>
				<?php
				$options = array(
					'yes' => 'Active',
		            'suspended' => 'Inactive',
					'no' => 'Dismissed',
				);
				$selected = "yes";
				if (isset($edit)) $selected = $this->input->post('select_active_status') ? $this->input->post('select_active_status') : $user_row->active_status;
				echo form_dropdown('select_active_status', $options, $selected, 'class="form-control"');
				?>
				<input name="active_employee" class="active_employee" type="hidden" <?php if((isset($user_row->user_active_employee) && $user_row->user_active_employee) || $this->input->post('active_employee')) : ?>checked="checked"<?php endif; ?>>
							<input name="employee_id" class="employee_id" type="hidden" value="<?php if(isset($user_row->employee_id) && $user_row->employee_id) : echo $user_row->employee_id; endif;  ?>">
				<div id="dcountry"></div>
			</div>
		</div>
	</div>
</div>

<div class="form-group">
	<label class="control-label">Position:</label>
	<div>
		<?php
		$emp_position_attributes = array(
			'name' => 'txtposition',
			'id' => 'txtposition',
			'class' => 'form-control',
			'value' => set_value('txtposition', isset($user_row->emp_position) ? $user_row->emp_position : $this->input->post('txtposition'), false),
			'maxlength' => '50'
		);
		?>
		<span class="<?php echo (form_error('txtposition'))?'control-group error':'control-group success'; ?>">
			<?php echo form_input($emp_position_attributes) ?>
		</span>
	</div>
</div>

<div class="form-group">
	<div class="row">
		<div class="col-lg-5 col-md-4 col-sm-4 col-xs-4">
			<label class="control-label">Login <span style="color:red">*</span></label>
			<div>
				<?php
				$data = array(
					'name' => 'txtemail',
					'id' => 'txtemail',
					'class' => 'form-control',
					'autocomplete'=>'off',
					'value' => set_value('txtemail', isset($user_row->emailid) ? $user_row->emailid : $this->input->post('txtemail'), false),
					'maxlength' => '100'
				);
				echo form_input($data);
				?>
				<div id="demail"></div>
			</div>
		</div>
        <div class="col-lg-5 col-md-4 col-sm-4 col-xs-4">
            <label class="control-label">Password <span style="color:red">*</span></label>
            <div>
                <?php
                $data = array(
                    'name' => 'txtpassword',
                    'class' => 'form-control',
                    'id' => 'txtpassword',
                    'autocomplete'=>'off',
                    'maxlength' => '50'
                );
                echo form_input($data);
                ?>
                <div id="dpassword"></div>
            </div>
		</div>

		<div class="col-lg-2 col-md-3 col-sm-3 col-xs-3">
			<label class="control-label">Color:</label>
			<div>
				<?php
                $colorValue = isset($user_row->color) ? $user_row->color : $this->input->post('color');
                $colorValue = $colorValue ? $colorValue : '#a0a0a0';
				$data = array(
					'name' => 'color',
					'id' => 'color',
					'readonly' => 'readonly',
					'class' => 'form-control mycolorpicker',
					'value' => set_value('color', $colorValue, false)
				);
				echo form_input($data);
				?>
			</div>
		</div>
	</div>
</div>

<?php if(!is_support((isset($user_row))?$user_row:[])):  ?>
<hr class="hidden-xs" style="margin: 37px 0 26px;">
<?php endif; ?> 

<div class="form-group m-b-sm">
	<label class="control-label">First name <span style="color:red">*</span></label>
	<div>
		<?php
		$data = array(
			'name' => 'txtfirstname',
			'id' => 'txtfirstname',
			'class' => 'form-control',
			'autocomplete'=>'off',
			'value' => set_value('txtfirstname', isset($user_row->firstname) ? $user_row->firstname : $this->input->post('txtfirstname'), false),
			'maxlength' => '50'
		);
		echo form_input($data);
		?>
		<div id="dfirstname"></div>
	</div>
</div>

<div class="form-group m-b-sm" >
	<label class="control-label">Last name <span style="color:red">*</span></label>
	<div>
		<?php
		$data = array(
			'name' => 'txtlastname',
			'id' => 'txtlastname',
			'class' => 'form-control',
			'autocomplete'=>'off',
			'value' => set_value('txtlastname', isset($user_row->lastname) ? $user_row->lastname : $this->input->post('txtlastname'), false),
			'maxlength' => '50'
		);
		echo form_input($data);
		?>
		<div id="dlastname"></div>
	</div>
</div>

<?php
$usertype = element('selectusertype', $this->input->post(), element('user_type', isset($user_row)?(array)$user_row:[], 'user'));
?>
<?php if(is_field( (isset($user_row))?$user_row:[] )): ?>

    <input type="hidden" name="selectusertype" value="user">
    <?php /*
	<input type="hidden" name="">
	<input type="hidden" name="">
	<input type="hidden" name="">
	*/ ?>
    <input type="hidden" name="is_feild_worker" value="1">
<?php else:  ?>
    <div class="form-group">
        <div class="row">
            <div class="col-lg-12 col-md-12 col-xs-12 text-right">
                <label class="control-label">&nbsp;</label>
                <div class="clearfix"></div>
                <table class="table m-n">
                    <tr>
                        <td style="text-align:left;padding:0px;vertical-align: middle;"> Works as an Estimator</td>
                        <td style="padding:0px;">
                            <?php
                            if ($this->input->post('is_field_estimator') || set_value('is_field_estimator') == '1' || (isset($user_row->emp_field_estimator) && $user_row->emp_field_estimator == '1')) {
                                $checked = TRUE;
                            } else {
                                $checked = FALSE;
                            }

                            $data = array(
                                'name' => 'is_field_estimator',
                                'id' => 'is_field_estimator_1',
                                'value' => '1',
                                'checked' => $checked,
                                'style' => 'margin:10px');

                            echo form_radio($data); ?>
                            Yes
                            <?php if (!$this->input->post('is_field_estimator') && (set_value('is_field_estimator') == '0' || set_value('is_field_estimator') == '')  && (!isset($user_row->emp_field_estimator) || $user_row->emp_field_estimator == '0')) {
                                $checked1 = TRUE;
                            } else {
                                $checked1 = FALSE;
                            }

                            $data = array(
                                'name' => 'is_field_estimator',
                                'id' => 'is_field_estimator_0',
                                'value' => '0',
                                'checked' => $checked1,
                                'style' => 'margin:10px');
                            echo form_radio($data); ?>
                            NO
                        </td>
                    </tr>
                    <tr>
                        <td style="text-align:left;padding:0px;vertical-align: middle;">Works as a Crew Member</td>
                        <td style="padding:0px;">
                            <?php
                            if ($this->input->post('is_feild_worker') || set_value('is_feild_worker') == '1' || (isset($user_row->emp_feild_worker) && $user_row->emp_feild_worker == '1')) {
                                $checked = TRUE;
                            } else {
                                $checked = FALSE;
                            }

                            $data = array(
                                'name' => 'is_feild_worker',
                                'id' => 'is_feild_worker_1',
                                'value' => '1',
                                'checked' => $checked,
                                'style' => 'margin:10px');

                            echo form_radio($data); ?>
                            Yes
                            <?php

                            if (!$this->input->post('is_feild_worker') && (set_value('is_feild_worker') == '0' || set_value('is_feild_worker') == '')  && (!isset($user_row->emp_feild_worker) || $user_row->emp_feild_worker == '0')) {
                                $checked1 = TRUE;
                            } else {
                                $checked1 = FALSE;
                            }

                            $data = array(
                                'name' => 'is_feild_worker',
                                'id' => 'is_feild_worker_0',
                                'value' => '0',
                                'checked' => $checked1,
                                'style' => 'margin:10px');
                            echo form_radio($data); ?>
                            NO
                        </td>
                    </tr>
                    <tr>
                        <td style="text-align: left; padding:0px;vertical-align:middle;">Attends appointments</td>
                        <td style="padding:0px;">
                            <?php

                            if ($this->input->post('is_appointment') || set_value('is_appointment') == 1 || (isset($user_row->is_appointment) && $user_row->is_appointment)) {
                                $checked = TRUE;
                            } else {
                                $checked = FALSE;
                            }

                            $data = array(
                                'name' => 'is_appointment',
                                'id' => 'is_appointment_1',
                                'value' => '1',
                                'checked' => $checked,
                                'style' => 'margin:10px');

                            echo form_radio($data); ?>
                            Yes
                            <?php

                            if (!$this->input->post('is_appointment') && (set_value('is_appointment') == '0' || set_value('is_appointment') == '')  && (!isset($user_row->is_appointment) || !$user_row->is_appointment)) {
                                $checked1 = TRUE;
                            } else {
                                $checked1 = FALSE;
                            }

                            $data = array(
                                'name' => 'is_appointment',
                                'id' => 'is_appointment_0',
                                'value' => '0',
                                'checked' => $checked1,
                                'style' => 'margin:10px');
                            echo form_radio($data); ?>
                            NO
                        </td>
                    </tr>
                    <tr>
                        <td style="text-align:left;padding:0px;vertical-align: middle;">Requires to provide payment details</td>
                        <td style="padding:0px;">
                            <?php
                            $class = $checked = '';
                            $value = element('is_require_payment_details', $this->input->post(), element('is_require_payment_details', isset($user_row)?(array)$user_row:[], 1));
                            $value = ($usertype!='admin')?$value:0;
                            ?>
                            <?php if ($value) : ?>
                                <?php $checked = TRUE; ?>
                            <?php else : ?>
                                <?php $checked1 = TRUE; ?>
                            <?php endif; ?>
                            <?php

                            $data = array(
                                'name' => 'is_require_payment_checkbox',
                                'id' => 'is_require_payment_checkbox_1',
                                'value' => '1',
                                'checked' => $checked,
                                'style' => 'margin:10px');

                            echo form_radio($data, ); ?>
                            Yes
                            <?php

                            $data = array(
                                'name' => 'is_require_payment_checkbox',
                                'id' => 'is_require_payment_checkbox_0',
                                'value' => '0',
                                'checked' => $checked1,
                                'style' => 'margin:10px');
                            echo form_radio($data); ?>
                            NO
                            <input type="hidden"  value="<?php echo $value; ?>" name="is_require_payment_details">
                        </td>
                    </tr>
                </table>


            </div>

        </div>
    </div>
<?php endif; ?>


<?php if(!is_support((isset($user_row))?$user_row:[])):  ?>
<hr class="hidden-xs" style="margin: 22px 0 16px; border-top: #fff;">
<?php endif; ?> 

<style type="text/css">
	.p-7{padding: 7px!important;}
	.p-l-4{padding-left: 4px!important;}
	.p-r-4{padding-right: 4px!important;}
</style>
<script type="text/javascript">
    var admin = '<?php echo ($usertype=='admin') ? 1 : 0;;?>';
	$('input[name="is_require_payment_checkbox"]').on('change', function(){
	   $('[name="is_require_payment_details"]').val($(this).val());
	});
    $(document).ready(function(){
        if(admin == 1)
        {
            $('input[name="is_require_payment_checkbox"]').attr('disabled', 'disabled');
        }

    });
	$('[name="selectusertype"]').change(function(){
		var val = $(this).val();
		if($('input[name="is_require_payment_checkbox"]').val() == 0)
		{
            $('#is_require_payment_checkbox_1').prop("checked", true);
            $('#is_require_payment_checkbox_1').removeAttr('disabled');
            $('#is_require_payment_checkbox_0').removeAttr('disabled');
		}
		
		if(val=='admin'){
            $('#is_require_payment_checkbox_0').prop("checked", true);
			$('#is_require_payment_checkbox_1').attr('disabled', 'disabled');
			$('#is_require_payment_checkbox_0').attr('disabled', 'disabled');
		}
		else {
            $('#is_require_payment_checkbox_1').prop("checked", true);
			$('#is_require_payment_checkbox_1').removeAttr('disabled');
			$('#is_require_payment_checkbox_0').removeAttr('disabled');
		}

		$('[name="is_require_payment_details"]').val($('input[name="is_require_payment_checkbox"]').val());
	});

</script>
