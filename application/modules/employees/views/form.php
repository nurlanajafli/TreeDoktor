<?php $this->load->view('includes/header', array('msg' => $msg)); ?>

<section class="scrollable p-sides-15">
<ul class="breadcrumb no-border no-radius b-b b-light pull-in">
	<li><a href="<?php echo base_url(); ?>"><i class="fa fa-home"></i> Home</a></li>
	<li><a href="<?php echo base_url('employees'); ?>">Employees</a></li>
	<li class="active"><?php if (isset($employee_row->emp_name)) : ?>Employee - <?php echo $employee_row->emp_name; ?><?php else : ?>New Employee<?php endif; ?></li>
</ul>
<section class="col-md-12 panel panel-default p-n">
<header
	class="panel-heading"><?php if (isset($employee_row->emp_name)) : ?>Employee - <?php echo $employee_row->emp_name; ?><?php else : ?>New Employee<?php endif; ?></header>
<?php
$attri = array(
	'name' => 'employees_form',
	'id' => 'employees_form');

if (isset($edit)) {
	echo form_open_multipart('employees/save/' . $employee_row->employee_id, $attri);
} else {
	echo form_open_multipart('employees/save', $attri);
}
?>
<section class="col-md-6">
<div class="p-sides-30 p-top-10">
<table class="table">
<tr>
	<td class="w-150"><label class="control-label">Employee Name:</label></td>
	<td class="p-left-30">

		<?php  if (form_error('txtname')) {
			$emp_name_error = "inputError";
			$emp_name_holder = "control-group error";
		} else {
			$emp_name_error = "inputSuccess";
			$emp_name_holder = "control-group success";
		}; ?>

		<?php
		$emp_name_attributes = array(
			'name' => 'txtname',
			'id' => 'txtname',
			'class' => 'form-control',
			'value' => set_value('txtname', isset($employee_row->emp_name) ? $employee_row->emp_name : ''),
			'maxlength' => '50'
		);
		?>
		<span class="<?php echo $emp_name_holder ?>">
                 <?php echo form_input($emp_name_attributes) ?>
              </span>
	</td>
</tr>


<tr>
	<td class="w-150"><label class="control-label">Employee Sex:</label></td>
	<td class="p-left-30">
		<select name="txtsex" class="form-control">
			<option
				value="male"<?php if (isset($employee_row->emp_sex) && $employee_row->emp_sex == 'male') : ?> selected<?php endif; ?>>
				Male
			</option>
			<option
				value="female"<?php if (isset($employee_row->emp_sex) && $employee_row->emp_sex == 'female') : ?> selected<?php endif; ?>>
				Female
			</option>
		</select>
	</td>
</tr>

<tr>
	<td class="w-150"><label class="control-label">Employee Date Of Birthday:</label></td>
	<td class="p-left-30">
		<?php  if (form_error('txtbirthday')) {
			$emp_email_error = "inputError";
			$emp_email_holder = "control-group error";
		} else {
			$emp_email_error = "inputSuccess";
			$emp_email_holder = "control-group success";
		}; ?>
		<?php
		$emp_email_attributes = array(
			'name' => 'txtbirthday',
			'id' => 'txtbirthday',
			'class' => 'datepicker form-control',
			'placeholder' => 'Choose Date of Birthday',
			'value' => set_value('txtbirthday', isset($employee_row->emp_birthday) && $employee_row->emp_birthday == TRUE  ? $employee_row->emp_birthday : ''),
			'maxlength' => '50'
		);
		?>
			<span class="<?php echo $emp_email_holder ?>">
                 <?php echo form_input($emp_email_attributes) ?>
              </span>
	</td>
</tr>
<tr>
	<td class="w-150"><label class="control-label">Employee Email:</label></td>
	<td class="p-left-30">

		<?php  if (form_error('txtemail')) {
			$emp_email_error = "inputError";
			$emp_email_holder = "control-group error";
		} else {
			$emp_email_error = "inputSuccess";
			$emp_email_holder = "control-group success";
		}; ?>

		<?php
		$emp_email_attributes = array(
			'name' => 'txtemail',
			'id' => 'txtemail',
			'class' => 'form-control',
			'value' => set_value('txtemail', isset($employee_row->emp_email) ? $employee_row->emp_email : ""),
			'maxlength' => '50'
		);
		?>
		<span class="<?php echo $emp_email_holder ?>">
                 <?php echo form_input($emp_email_attributes) ?>
              </span>
	</td>
</tr>

<tr>
	<td class="w-150"><label class="control-label">Employee Username:</label></td>
	<td class="p-left-30">

		<?php  if (form_error('txtusername')) {
			$emp_username_error = "inputError";
			$emp_username_holder = "control-group error";
		} else {
			$emp_username_error = "inputSuccess";
			$emp_username_holder = "control-group success";
		}; ?>

		<?php
		$emp_username_attributes = array(
			'name' => 'txtusername',
			'id' => 'txtusername',
			'class' => 'form-control',
			'value' => set_value('txtusername', isset($employee_row->emp_username) ? $employee_row->emp_username : ""),
			'maxlength' => '50'
		);
		?>
		<span class="<?php echo $emp_username_holder ?>">
                 <?php echo form_input($emp_username_attributes) ?>
              </span>
	</td>
</tr>

<tr>
	<td class="w-150"><label class="control-label">Position:</label></td>
	<td class="p-left-30">

		<?php  if (form_error('txtposition')) {
			$emp_position_error = "inputError";
			$emp_position_holder = "control-group error";
		} else {
			$emp_position_error = "inputSuccess";
			$emp_position_holder = "control-group success";
		}; ?>

		<?php
		$emp_position_attributes = array(
			'name' => 'txtposition',
			'id' => 'txtposition',
			'class' => 'form-control',
			'value' => set_value('txtposition', isset($employee_row->emp_position) ? $employee_row->emp_position : ''),
			'maxlength' => '50'
		);
		?>
		<span class="<?php echo $emp_position_holder ?>">
                 <?php echo form_input($emp_position_attributes) ?>
              </span>
	</td>
</tr>

<tr>
	<td><label class="control-label">Address1:</label></td>
	<td class="p-left-30">

		<?php  if (form_error('txtaddress1')) {
			$emp_add1_error = "inputError";
			$emp_add1_holder = "control-group error";
		} else {
			$emp_add1_error = "inputSuccess";
			$emp_add1_holder = "control-group success";
		}; ?>

		<?php
		$emp_add1_attributes = array(
			'name' => 'txtaddress1',
			'id' => 'txtaddress1',
			'class' => 'form-control',
			'value' => set_value('txtaddress1', isset($employee_row->emp_address1) ? $employee_row->emp_address1 : ''),
			'maxlength' => '100'
		);
		?>
		<span class="<?php echo $emp_add1_holder ?>">
                 <?php echo form_input($emp_add1_attributes) ?>
              </span>
	</td>
</tr>

<tr>
	<td><label class="control-label">Address2:</label></td>
	<td class="p-left-30">

		<?php
		$emp_add2_attributes = array(
			'name' => 'txtaddress2',
			'id' => 'txtaddress2',
			'class' => 'form-control',
			'value' => set_value('txtaddress2', isset($employee_row->emp_address2) ? $employee_row->emp_address2 : ''),
			'maxlength' => '100'
		);
		?>

		<?php echo form_input($emp_add2_attributes) ?>
	</td>
</tr>

<tr>
	<td><label class="control-label">City:</label></td>
	<td class="p-left-30">

		<?php  if (form_error('txtcity')) {
			$emp_city_error = "inputError";
			$emp_city_holder = "control-group error";
		} else {
			$emp_city_error = "inputSuccess";
			$emp_city_holder = "control-group success";
		}; ?>

		<?php
		$emp_city_attributes = array(
			'name' => 'txtcity',
			'id' => 'txtcity',
			'class' => 'form-control',
			'value' => set_value('txtcity', isset($employee_row->emp_city) ? $employee_row->emp_city : ""),
			'maxlength' => '100'
		);
		?>
		<span class="<?php echo $emp_city_holder ?>">
                 <?php echo form_input($emp_city_attributes) ?>
              </span>
	</td>
</tr>

<tr>
	<td><label class="control-label">State:</label></td>
	<td class="p-left-30">

		<?php
		$emp_state_attributes = array(
			'name' => 'txtstate',
			'id' => 'txtstate',
			'class' => 'form-control',
			'value' => set_value('txtstate', isset($employee_row->emp_state) ? $employee_row->emp_state : ''),
			'maxlength' => '100'
		);
		?>

		<?php echo form_input($emp_state_attributes) ?>

	</td>
</tr>

<tr>
	<td><label class="control-label">Deductions:</label></td>
	<td class="p-left-30 text-center">

		<?php
		$deductions_state_attributes = array(
			'name' => 'deductions_state',
			'id' => 'deductions_state',
			'value' => 1,
			'checked' => isset($employee_row->deductions_state) && $employee_row->deductions_state ? TRUE : FALSE,
			'onchange' => "if($(this).is(':checked')) $('.deductions').fadeIn(); else $('.deductions').fadeOut();"
		);

		?>

		<?php echo form_checkbox($deductions_state_attributes) ?>

	</td>
</tr>
<tr class="deductions"<?php echo isset($employee_row->deductions_state) && $employee_row->deductions_state ? "" : ' style="display:none"' ?>>
	<td><label class="control-label">Deductions Amount:</label></td>
	<td class="p-left-30">

		<?php
		$deductions_amount_attributes = array(
			'name' => 'deductions_amount',
			'id' => 'deductions_amount',
			'class' => 'form-control',
			'value' => set_value('deductions_amount', isset($employee_row->deductions_amount) ? $employee_row->deductions_amount : '')
		);
		?>

		<?php echo form_input($deductions_amount_attributes) ?>

	</td>
</tr>
<tr class="deductions"<?php echo isset($employee_row->deductions_state) && $employee_row->deductions_state ? "" : ' style="display:none"' ?>>
	<td><label class="control-label">Deductions Desctiprion:</label></td>
	<td class="p-left-30">

		<?php
		$deductions_desc_attributes = array(
			'name' => 'deductions_desc',
			'id' => 'deductions_desc',
			'class' => 'form-control',
			'rows' => '2',
			'value' => set_value('deductions_desc', isset($employee_row->deductions_desc) ? $employee_row->deductions_desc : '')
		);
		?>

		<?php echo form_textarea($deductions_desc_attributes) ?>

	</td>
</tr>
</table>

</section>
<!-- /end left block -->


<!-- right block -->
<section class="col-md-6">
	<div class="p-sides-30 p-top-10">

		<table class="table">
			<tr>
				<td><label class="control-label">Phone:</label></td>
				<td class="p-left-30">

					<?php  if (form_error('txtphone')) {
						$emp_phone_error = "inputError";
						$emp_phone_holder = "control-group error";
					} else {
						$emp_phone_error = "inputSuccess";
						$emp_phone_holder = "control-group success";
					}; ?>

					<?php
					$emp_phone_attributes = array(
						'name' => 'txtphone',
						'id' => 'txtphone',
						'class' => 'form-control',
						'value' => set_value('txtphone', isset($employee_row->emp_phone) ? $employee_row->emp_phone : ''),
						'maxlength' => '100'
					);
					?>
					<?php echo form_input($emp_phone_attributes) ?>
				</td>
			</tr>
			<tr>
	<td class="w-150"><label class="control-label">Pay Frequency:</label></td>
	<td class="p-left-30">
		<select name="txtfrequency" class="form-control">
			<option
				value="weekly"<?php if (isset($employee_row->emp_pay_frequency) && $employee_row->emp_pay_frequency == 'weekly') : ?> selected<?php endif; ?>>
				Weekly
			</option>
			<option
				value="be-weekly"<?php if (isset($employee_row->emp_pay_frequency) && $employee_row->emp_pay_frequency == 'be-weekly') : ?> selected<?php endif; ?>>
				Be-weekly
			</option>
			<option
				value="monthly"<?php if (isset($employee_row->emp_pay_frequency) && $employee_row->emp_pay_frequency == 'monthly') : ?> selected<?php endif; ?>>
				Monthly
			</option>
		</select>
	</td>
</tr>
<tr>
	<td class="w-150"><label class="control-label">Employee Date Of Hire:</label></td>
	<td class="p-left-30">
		<?php  if (form_error('txthiredate')) {
			$emp_email_error = "inputError";
			$emp_email_holder = "control-group error";
		} else {
			$emp_email_error = "inputSuccess";
			$emp_email_holder = "control-group success";
		}; ?>
		<?php
		$emp_email_attributes = array(
			'name' => 'txthiredate',
			'id' => 'txthiredate',
			'class' => 'datepicker form-control',
			'placeholder' => 'Choose Date of Hire',
			'value' => set_value('txthiredate', isset($employee_row->emp_date_hire) && $employee_row->emp_date_hire == TRUE  ? $employee_row->emp_date_hire : ''),
			'maxlength' => '50'
		);
		?>
			<span class="<?php echo $emp_email_holder ?>">
                 <?php echo form_input($emp_email_attributes) ?>
              </span>
	</td>
</tr>
<?php if (isAdmin()) : ?>
			<tr>
				<td><label class="control-label">Employee Sin:</label></td>
				<td class="p-left-30">
					<?php
					$emp_sin_attributes = array(
						'name' => 'txtsin',
						'id' => 'txtsin',
						'class' => 'form-control',
						'value' => set_value('txtsin', isset($employee_row->emp_sin) ? $employee_row->emp_sin : ''),
						'maxlength' => '100'
					);
					?>
					<?php echo form_input($emp_sin_attributes) ?>
				</td>
			</tr>

			<tr>
				<td><label class="control-label">Employee Hourly Rate:</label><label class="control-label">Employee Yearly Rate:</label></td>
				<td class="p-left-30">
					<?php  if (form_error('txthourlyrate')) {
						$emp_email_error = "inputError";
						$emp_email_holder = "control-group error";
					} else {
						$emp_email_error = "inputSuccess";
						$emp_email_holder = "control-group success";
					}; ?>
					<?php
					$emp_sin_attributes = array(
						'name' => 'txthourlyrate',
						'id' => 'txthourlyrate',
						'class' => 'form-control',
						'value' => set_value('txthourlyrate', isset($employee_row->emp_hourly_rate) ? $employee_row->emp_hourly_rate : ''),
						'maxlength' => '100'
					);
					?>
					<span class="<?php echo $emp_email_holder; ?>">
                <?php echo form_input($emp_sin_attributes) ?><br>
					<?php
					$emp_sin_attributes = array(
						'name' => 'txtyearlyrate',
						'id' => 'txtyearlyrate',
						'class' => 'form-control',
						'value' => set_value('txtyearlyrate', isset($employee_row->emp_yearly_rate) ? $employee_row->emp_yearly_rate : ''),
						'maxlength' => '100'
					);
					?>
					
                <?php echo form_input($emp_sin_attributes) ?>
                </span>
				</td>
			</tr>
<?php endif; ?>
			<tr>
				<td><label class="control-label">Employee Start Time:</label></td>
				<td class="p-left-30">
					<?php  if (form_error('txtstarttime')) {
						$emp_email_error = "inputError";
						$emp_email_holder = "control-group error";
					} else {
						$emp_email_error = "inputSuccess";
						$emp_email_holder = "control-group success";
					}; ?>
					<?php
					$emp_sin_attributes = array(
						'name' => 'txtstarttime',
						'type' => 'time',
						'id' => 'txtstarttime',
						'class' => 'form-control',
						'value' => set_value('txtstarttime', isset($employee_row->emp_start_time) ? $employee_row->emp_start_time : '')
					);
					?>
					<span class="<?php echo $emp_email_holder; ?>">
                <?php echo form_input($emp_sin_attributes) ?>
                </span>
				</td>
			</tr>

			<tr>
				<td><label class="control-label">Employee Status:</label></td>
				<td class="p-left-30">
					<?php 
					$options = array(
						'current' => 'Current',
						'temporary' => 'Temporary',
						'past' => 'Past',
						'on_leave' => 'On leave',
					);
					?>
					<?php echo form_dropdown('txtstatus', $options, set_value('txtstatus', isset($employee_row->emp_status) ? $employee_row->emp_status : 'Current'), 'class="form-control" id="txtstatus"'); ?>
				</td>
			</tr>
			<tr>
				<td><label class="control-label">Employee Type:</label></td>
				<td class="p-left-30">
					<?php
					/*$options = array(
						'employee' => 'Employee',
						'sub_ta' => 'Sub TA',
						'sub_ca' => 'Sub CA',

					);*/
					$options = array(
						'employee' => 'Employee',
						'subcontractor' => 'Subcontractor',
						'temp/cash' => 'Temp/Cash',

					);
					?>
					<?php echo form_dropdown('txttype', $options, set_value('txttype', isset($employee_row->emp_type) ? $employee_row->emp_type : 'Current'), 'class="form-control" id="txttype"'); ?>
					
				</td>
			</tr>
			<tr>
				<td><label class="control-label">Is estimator:</label></td>
				<td class="p-left-30">
					<?php if (set_value('is_field_estimator') == '1' || (isset($employee_row->emp_field_estimator) && $employee_row->emp_field_estimator == '1')) {
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
					<?php if (set_value('is_field_estimator') == '0' || (isset($employee_row->emp_field_estimator) && $employee_row->emp_field_estimator == '0')) {
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
				<td><label class="control-label">Is field worker:</label></td>
				<td class="p-left-30">
					<?php if (set_value('is_feild_worker') == '1' || (isset($employee_row->emp_feild_worker) && $employee_row->emp_feild_worker == '1')) {
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
					<?php if (set_value('is_feild_worker') == '0' || (isset($employee_row->emp_feild_worker) && $employee_row->emp_feild_worker == '0')) {
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
				<td><label class="control-label">Driver:</label></td>
				<td class="p-left-30">
					<?php if (set_value('driver') == '1' || (isset($employee_row->emp_driver) && $employee_row->emp_driver == '1')) {
						$checked = TRUE;
					} else {
						$checked = FALSE;
					}

					$data_driver = array(
						'name' => 'driver',
						'id' => 'driver_1',
						'value' => '1',
						'checked' => $checked,
						'style' => 'margin:10px');

					echo form_radio($data_driver); ?>
					Yes
					<?php if (set_value('driver') == '0' || (isset($employee_row->emp_driver) && $employee_row->emp_driver == '0')) {
						$checked1 = TRUE;
					} else {
						$checked1 = FALSE;
					}

					$data = array(
						'name' => 'driver',
						'id' => 'driver_0',
						'value' => '0',
						'checked' => $checked1,
						'style' => 'margin:10px');
					echo form_radio($data); ?>
					NO
				</td>
			</tr>
			<tr>
				<td><label class="control-label">Climber:</label></td>
				<td class="p-left-30">
					<?php if (set_value('climber') == '1' || (isset($employee_row->emp_climber) && $employee_row->emp_climber == '1')) {
						$checked = TRUE;
					} else {
						$checked = FALSE;
					}

					$data = array(
						'name' => 'climber',
						'id' => 'climber_1',
						'value' => '1',
						'checked' => $checked,
						'style' => 'margin:10px');

					echo form_radio($data); ?>
					Yes
					<?php if (set_value('climber') == '0' || (isset($employee_row->emp_climber) && $employee_row->emp_climber == '0')) {
						$checked1 = TRUE;
					} else {
						$checked1 = FALSE;
					}

					$data = array(
						'name' => 'climber',
						'id' => 'climber_0',
						'value' => '0',
						'checked' => $checked1,
						'style' => 'margin:10px');
					echo form_radio($data); ?>
					NO
				</td>
			</tr>
		</table>

	</div>
</section>
<!-- /end right block -->


</section>
<!-- /end of client profile -->

<section class="col-md-12 panel panel-default p-n">
	<header class="panel-heading">Messsage Accounts</header>
	<!-- Client header -->

	<div class="p-top-10 p-sides-30">

		<?php  $options = array(
			'name' => 'area_account_message',
			'rows' => '3',
			'class' => 'form-control',
			'placeholder' => 'employee account message...',
			'value' => set_value('area_account_message', isset($employee_row->emp_message_on_account) ? $employee_row->emp_message_on_account : ''));?>

		<?php  if (form_error('area_account_message')) {
			$emp_message_error = "inputError";
			$emp_message_holder = "control-group error";
		} else {
			$emp_message_error = "inputSuccess";
			$emp_message_holder = "control-group success";
		}; ?>
		<span class="<?php echo $emp_message_holder ?>">
        <?php echo form_textarea($options) ?>
        </span>
	</div>

	<div class="pull-right p-sides-30 p-bottom-10">
		<?php
		$value = "Add";
		if (isset($edit)) $value = 'Update';
		$attr = array(
			'class' => 'btn btn-info m-t-sm',
			'id' => 'submit',
			'name' => 'submit',
			'value' => $value
		);
		echo form_submit($attr); ?>
	</div>
	<?php echo form_close() ?>
		<?php if(isset($employee_row) && !isset($employee_row->emailid)) : ?>
			<input type="button" id="addUser" class="btn btn-success m-t-sm pull-right" onclick="addUser(<?php echo $employee_row->employee_id; ?>);" value="Add As User">
		<?php endif; ?>
</section>

<?php echo form_close() ?>

</section><!-- /span12 -->
<script>
	$(document).ready(function () {
		$('.datepicker').datepicker({format: 'yyyy-mm-dd'});
	});
	
	
	$('#txtyearlyrate').keyup(function(){
		var obj = $(this);
		var last = <?php echo isset($employee_row->emp_hourly_rate) ? json_encode($employee_row->emp_hourly_rate) : 0 ; ?>;
		var val = $('#txtyearlyrate').val();
		if(val != '' && val != '0' && val)
		{
			rate = val/26/80;
			$(obj).parent().find('#txthourlyrate').val(parseFloat(rate.toFixed(4)));
			return false;
		}
		else
			$(obj).parent().find('#txthourlyrate').val(last);
		console.log(last); 
		return false;
	});
	$('#txthourlyrate').keyup(function(){
		var obj = $(this);
		var last = <?php echo isset($employee_row->emp_yearly_rate) ? json_encode($employee_row->emp_yearly_rate) : 0 ; ?>;
		var val = $('#txthourlyrate').val();
		//console.log(val); return false;
		if(val != '' && val != '0' && val)
		{
			
			//console.log(rate); return false;
			$(obj).parent().find('#txtyearlyrate').val();
			return false;
		}
		else
			$(obj).parent().find('#txtyearlyrate').val('');
		console.log(last); 
		return false;
	});
	
	function addUser(id)
	{
		var id = id;
		$.post(baseUrl + 'employees/newUser', {id:id}, function (resp) {
			if(resp.status == 'ok')
				$('#addUser').css('display', 'none');
			else
				alert('Oops!');
			
		}, 'json');

		return false;
	}
</script>
<?php $this->load->view('includes/footer'); ?>
