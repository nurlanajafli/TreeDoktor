

<div class="form-group payroll-details">
	<div class="row">
		<div class="col-md-4">
			<label class="control-label">Employee Sex:</label>
			<div>
				<select name="txtsex" class="form-control">
					<option
						value="male"<?php if ((isset($user_row->emp_sex) && $user_row->emp_sex == 'male') || $this->input->post('txtsex') == 'male') : ?> selected<?php endif; ?>>
						Male
					</option>
					<option
						value="female"<?php if ((isset($user_row->emp_sex) && $user_row->emp_sex == 'female') || $this->input->post('txtsex') == 'female') : ?> selected<?php endif; ?>>
						Female
					</option>
				</select>
			</div>
		</div>
		<div class="col-md-4">
			<label class="control-label">Date Of Birthday:</label>
			<div>
				<?php  
					$emp_email_holder = "control-group success";
					if (form_error('txtbirthday'))
						$emp_email_holder = "control-group error"; 
					?>
					
					<?php
					$emp_email_attributes = array(
						'name' => 'txtbirthday',
						'id' => 'txtbirthday',
						'autocomplete'=>'off',
						'data-date-format' => 'yyyy-mm-dd',
						'class' => 'datepicker form-control',
						'placeholder' => 'Choose Date of Birthday',
						'value' => set_value('txtbirthday', isset($user_row->emp_birthday) && $user_row->emp_birthday == TRUE && $user_row->emp_birthday !== '0000-00-00' ? $user_row->emp_birthday : $this->input->post('txtbirthday')),
						'maxlength' => '50'
					);
				?>
				<span class="<?php echo $emp_email_holder ?>">
					<?php echo form_input($emp_email_attributes) ?>
				</span>
			</div>
		</div>

		<div class="col-md-4">
			<label class="control-label">Date Of Hire:</label>
			<div>
				<?php
				$emp_email_attributes = array(
					'name' => 'txthiredate',
					'id' => 'txthiredate',
					'autocomplete'=>'off',
					'data-date-format' => 'yyyy-mm-dd',
					'class' => 'datepicker form-control',
					'placeholder' => 'Choose Date of Hire',
					'value' => set_value('txthiredate', isset($user_row->emp_date_hire) && $user_row->emp_date_hire == TRUE && $user_row->emp_date_hire !== '0000-00-00' ? $user_row->emp_date_hire : $this->input->post('txthiredate')),
					'maxlength' => '50'
				);
				?>
				<span class="control-group <?php echo (form_error('txthiredate'))?'error':'success'; ?>">
					<?php echo form_input($emp_email_attributes) ?>
				</span>
			</div>		
		</div>

	</div>
</div>



<div class="form-group">
	<div class="row">
		
		<div class="col-md-4">
			<label class="control-label">Start Time:</label>
			<div>
				<?php  if (form_error('txtstarttime')) {
					$emp_email_holder = "control-group error";
				} else {
					$emp_email_holder = "control-group success";
				}; ?>
				<?php
				$emp_sin_attributes = array(
					'name' => 'txtstarttime',
					'type' => 'time',
					'id' => 'txtstarttime',
					'class' => 'form-control',
					'value' => set_value('txtstarttime', (isset($user_row->emp_start_time) && $user_row->emp_start_time != '00:00:00') ? $user_row->emp_start_time : $this->input->post('txtstarttime'))
				);
				?>
				<span class="<?php echo $emp_email_holder; ?>">
				<?php echo form_input($emp_sin_attributes) ?>
				</span>
			</div>
		</div>
        <div class="form-group deductionChecker" style="margin-bottom: 0px;">
            <div class="row">
                <div class="col-md-4 p-top-5">
                    <label class="control-label">Check work time:</label>
                </div>
                <div class="col-md-4">
                    <label class="switch" style="margin-bottom: 3px;">
                        <?php
                            $is_check_work_time = array(
                                'name' => 'emp_check_work_time',
                                'id' => 'emp_check_work_time',
                                'value' => 1,
                                'checked' => (isset($user_row->emp_check_work_time) && $user_row->emp_check_work_time) || $this->input->post('emp_check_work_time') ? TRUE : FALSE,
                                //'onchange' => "if($(this).is(':checked')){ $('.deductions').fadeIn(); $('.area_account_message').attr('rows', '2'); }else{ $('.deductions').fadeOut(); $('.area_account_message').attr('rows', '6');}"
                            );
                        ?>

                        <?php echo form_checkbox($is_check_work_time) ?>
                        <span></span>
                    </label>
                    <div class="clearfix"></div>
                </div>
            </div>
        </div>
		<div class="col-md-8">
			<div class="form-group">
				<label class="control-label">Employee Type:</label>
				<div>
					<?php //$options = ['employee' => 'Employee', 'sub_ta' => 'Sub TA', 'sub_ca' => 'Sub CA']; ?>
					<?php $options = ['employee' => 'Employee', 'subcontractor' => 'Subcontractor', 'temp/cash' => 'Temp/Cash']; ?>
					<?php echo form_dropdown('txttype', $options, set_value('txttype', isset($user_row->emp_type) ? $user_row->emp_type : $this->input->post('txttype')), 'class="form-control" id="txttype"'); ?>
					
				</div>
			</div>	
		</div>

	</div>
</div>


	
<?php if ($this->session->userdata('user_type') == "admin") : ?>
	
	<div class="form-group">
		<div class="row">
			<div class="col-md-4">
				<label class="control-label">Pay Frequency:</label>
				<div>
					<select name="txtfrequency" class="form-control">
						<option
							value="weekly"<?php if ((isset($user_row->emp_pay_frequency) && $user_row->emp_pay_frequency == 'weekly') || $this->input->post('txtfrequency') == 'weekly') : ?> selected<?php endif; ?>>
							Weekly
						</option>
						<option
							value="be-weekly"<?php if ((isset($user_row->emp_pay_frequency) && $user_row->emp_pay_frequency == 'be-weekly') || $this->input->post('txtfrequency') == 'be-weekly') : ?> selected<?php endif; ?>>
							Bi-weekly
						</option>
						<option
							value="monthly"<?php if ((isset($user_row->emp_pay_frequency) && $user_row->emp_pay_frequency == 'monthly') || $this->input->post('txtfrequency') == 'monthly') : ?> selected<?php endif; ?>>
							Monthly
						</option>
					</select>
				</div>
			</div>
			<div class="col-md-8">
				<label class="control-label">Yearly Rate: / Hourly Rate:</label>
				
				<div class="input-group">
				  	<?php
				  	$yearly_rate = set_value('txtyearlyrate', isset($user_row->emp_yearly_rate) ? $user_row->emp_yearly_rate : $this->input->post('txtyearlyrate'));
				  	
				  	$hourly_rate = set_value('txthourlyrate', isset($user_row->emp_hourly_rate) ? $user_row->emp_hourly_rate : $this->input->post('txthourlyrate'));

					$yearly_rate_checked = ($yearly_rate!==false && $yearly_rate!=0)?'checked="checked"':''; 
					$hourly_rate_checked = ($hourly_rate!==false && $hourly_rate!=0)?'checked="checked"':''; 
					if($yearly_rate_checked != '' && $hourly_rate_checked != '')
                        $hourly_rate_checked = '';
					
					if($yearly_rate_checked == '' && $hourly_rate_checked == '')
						$hourly_rate_checked = 'checked="checked"';

					$emp_sin_attributes = array(
						'name' => 'txtyearlyrate',
						'id' => 'txtyearlyrate',
						'class' => 'form-control',
						'value' => set_value('txtyearlyrate', isset($user_row->emp_yearly_rate) ? $user_row->emp_yearly_rate : $this->input->post('txtyearlyrate')),
						'maxlength' => '100'
					);
					?>
					
					<span class="<?php echo $emp_email_holder; ?> yearly_rate" <?php if($yearly_rate_checked == ''): ?>style="display: none;"<?php endif; ?> >
						<?php echo form_input($emp_sin_attributes) ?>	
					</span>

					

					<?php  if (form_error('txthourlyrate')) {
						$emp_email_holder = "control-group error";
					} else {
						$emp_email_holder = "control-group success";
					}; ?>
					<?php
					$emp_sin_attributes = array(
						'name' => 'txthourlyrate',
						'id' => 'txthourlyrate',
						'class' => 'form-control',
						'value' => set_value('txthourlyrate', isset($user_row->emp_hourly_rate) ? $user_row->emp_hourly_rate : $this->input->post('txthourlyrate')),
						'maxlength' => '100'
					);
					?>
					
					<span class="<?php echo $emp_email_holder; ?> hourly_rate" <?php if($hourly_rate_checked == ''): ?>style="display: none;"<?php endif; ?> >
						<?php echo form_input($emp_sin_attributes) ?>
					</span>
					
					
				  	<div class="input-group-btn">
					    <button class="btn btn-default dropdown-toggle" style="padding-bottom: 7px;" data-toggle="dropdown">
					      <span class="dropdown-label">
					      	<?php if($yearly_rate_checked != ''): ?>
					      	Yearly
					      	<?php else: ?>
					      	Hourly
					      	<?php endif; ?>
					      </span>  
					      <span class="caret"></span>
					    </button>
					    <ul class="dropdown-menu dropdown-select pull-right">
					      	<li data-value="yearly_rate" class="yearly_hourly_rate <?php echo ($yearly_rate_checked != '')?'active':''; ?>">
					        	<a href="#"><input type="radio" value="yearly_rate" name="yearly_hourly_rate" <?php echo $yearly_rate_checked;?>>Yearly</a>
					      	</li>
					      	<li data-value="hourly_rate" class="yearly_hourly_rate <?php echo ($hourly_rate_checked != '')?'active':''; ?>">
					        	<a href="#"><input type="radio" value="hourly_rate" name="yearly_hourly_rate" <?php echo $hourly_rate_checked;?>>Hourly</a>
					      	</li>
					    </ul>
				  	</div>
				</div>
				<?php /*
				<div class="yearly_rate">
					<label class="control-label">Yearly Rate:</label>
					<div>
						
						<?php
						$emp_sin_attributes = array(
							'name' => 'txtyearlyrate',
							'id' => 'txtyearlyrate',
							'class' => 'form-control',
							'value' => set_value('txtyearlyrate', isset($user_row->emp_yearly_rate) ? $user_row->emp_yearly_rate : $this->input->post('txtyearlyrate')),
							'maxlength' => '100'
						);
						?>
						<span class="<?php echo $emp_email_holder; ?>">
							<?php echo form_input($emp_sin_attributes) ?>
						</span>
					</div>
				</div>

				<div class="hourly_rate" >
					<label class="control-label">Hourly Rate:</label>
					<div>
						<?php  if (form_error('txthourlyrate')) {
							$emp_email_holder = "control-group error";
						} else {
							$emp_email_holder = "control-group success";
						}; ?>
						<?php
						$emp_sin_attributes = array(
							'name' => 'txthourlyrate',
							'id' => 'txthourlyrate',
							'class' => 'form-control',
							'value' => set_value('txthourlyrate', isset($user_row->emp_hourly_rate) ? $user_row->emp_hourly_rate : $this->input->post('txthourlyrate')),
							'maxlength' => '100'
						);
						?>
						<span class="<?php echo $emp_email_holder; ?>">
							<?php echo form_input($emp_sin_attributes) ?><br>
						</span>
					</div>
				</div>
				*/ ?>
			</div>
		</div>

		<?php /*
		<div class="row">
			<div class="col-md-4">
				<label class="control-label">Pay Frequency:</label>
				<div>
					<select name="txtfrequency" class="form-control">
						<option
							value="weekly"<?php if ((isset($user_row->emp_pay_frequency) && $user_row->emp_pay_frequency == 'weekly') || $this->input->post('txtfrequency') == 'weekly') : ?> selected<?php endif; ?>>
							Weekly
						</option>
						<option
							value="be-weekly"<?php if ((isset($user_row->emp_pay_frequency) && $user_row->emp_pay_frequency == 'be-weekly') || $this->input->post('txtfrequency') == 'be-weekly') : ?> selected<?php endif; ?>>
							Bi-weekly
						</option>
						<option
							value="monthly"<?php if ((isset($user_row->emp_pay_frequency) && $user_row->emp_pay_frequency == 'monthly') || $this->input->post('txtfrequency') == 'monthly') : ?> selected<?php endif; ?>>
							Monthly
						</option>
					</select>
				</div>
			</div>
			<div class="col-md-4">
				<label class="control-label">Yearly/Hourly Rate:</label>
				
			</div>
			<div class="col-md-4">
				
				<div class="input-group input-s-sm">
				  	<input type="text" id="appendedInput" class="input-sm form-control">
				  	
				  	<div class="input-group-btn">
					    <button class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown">
					      <span class="dropdown-label">USD</span>  
					      <span class="caret"></span>
					    </button>
					    <ul class="dropdown-menu dropdown-select pull-right">
					      	<li class="active">
					        	<a href="#"><input type="radio" value="USD" name="pay_unit" checked="">USD</a>
					      	</li>
					      	<li class="">
					        	<a href="#"><input type="radio" value="GBP" name="pay_unit">GBP</a>
					      	</li>
					    </ul>
				  	</div>
				</div>
				<div class="yearly_rate">
					<label class="control-label">Yearly Rate:</label>
					<div>
						
						<?php
						$emp_sin_attributes = array(
							'name' => 'txtyearlyrate',
							'id' => 'txtyearlyrate',
							'class' => 'form-control',
							'value' => set_value('txtyearlyrate', isset($user_row->emp_yearly_rate) ? $user_row->emp_yearly_rate : $this->input->post('txtyearlyrate')),
							'maxlength' => '100'
						);
						?>
						<span class="<?php echo $emp_email_holder; ?>">
							<?php echo form_input($emp_sin_attributes) ?>
						</span>
					</div>
				</div>

				<div class="hourly_rate" style="display: none;">
					<label class="control-label">Hourly Rate:</label>
					<div>
						<?php  if (form_error('txthourlyrate')) {
							$emp_email_holder = "control-group error";
						} else {
							$emp_email_holder = "control-group success";
						}; ?>
						<?php
						$emp_sin_attributes = array(
							'name' => 'txthourlyrate',
							'id' => 'txthourlyrate',
							'class' => 'form-control',
							'value' => set_value('txthourlyrate', isset($user_row->emp_hourly_rate) ? $user_row->emp_hourly_rate : $this->input->post('txthourlyrate')),
							'maxlength' => '100'
						);
						?>
						<span class="<?php echo $emp_email_holder; ?>">
							<?php echo form_input($emp_sin_attributes) ?><br>
						</span>
					</div>
				</div>

			</div>
		</div>
		*/ ?>
	</div>
	
	<div class="form-group">
		<label class="control-label">Employee Sin:</label>
		<div>
			<?php
			$emp_sin_attributes = array(
				'name' => 'txtsin',
				'id' => 'txtsin',
				'class' => 'form-control',
				'value' => set_value('txtsin', isset($user_row->emp_sin) ? $user_row->emp_sin : $this->input->post('txtsin')),
				'maxlength' => '100'
			);
			?>
			<?php echo form_input($emp_sin_attributes) ?>
		</div>
	</div>

<?php endif;?>

<?php if (true === (bool) config_item('payroll_deduction_state')): ?>
<div class="form-group deductionChecker" style="margin-bottom: 0px;">
	<div class="row">
		<div class="col-md-4 p-top-5">
			<label class="control-label">Deductions:</label>
		</div>
		<div class="col-md-8">
			<label class="switch pull-right" style="margin-bottom: 3px;">
		      	<?php
				$deductions_state_attributes = array(
					'name' => 'deductions_state',
					'id' => 'deductions_state',
					'value' => 1,
					'checked' => (isset($user_row->deductions_amount) && $user_row->deductions_amount) || $this->input->post('deductions_state') ? TRUE : FALSE,
					'onchange' => "if($(this).is(':checked')){ $('.deductions').fadeIn(); $('.area_account_message').attr('rows', '2'); }else{ $('.deductions').fadeOut(); $('.area_account_message').attr('rows', '6');}"
				);

				?>

				<?php echo form_checkbox($deductions_state_attributes) ?>
		      	<span></span>
		    </label>
		    <div class="clearfix"></div>	
		</div>
	</div>
</div>

<div class="form-group deductions"<?php echo (isset($user_row->deductions_amount) && $user_row->deductions_amount) || $this->input->post('deductions_state') ? "" : ' style="display:none"' ?>>
	<div class="row">
		<div class="col-md-4">
			<label class="control-label">Amount:</label>
			<div>
				<?php
					$deductions_amount_attributes = array(
						'name' => 'deductions_amount',
						'id' => 'deductions_amount',
						'class' => 'form-control',
						'value' => set_value('deductions_amount', isset($user_row->deductions_amount) ? $user_row->deductions_amount : $this->input->post('deductions_amount'))
					);
					?>

					<?php echo form_input($deductions_amount_attributes) ?>
			</div>
		</div>
		<div class="col-md-8">
			<label class="control-label">Deductions Description:</label>
			<div>
				<?php
				$deductions_desc_attributes = array(
					'name' => 'deductions_desc',
					'id' => 'deductions_desc',
					'class' => 'form-control',
					'value' => set_value('deductions_desc', isset($user_row->deductions_desc) ? $user_row->deductions_desc : $this->input->post('deductions_desc'))
				);
				?>

				<?php echo form_input($deductions_desc_attributes) ?>
			</div>
		</div>
	</div>
</div>
<?php endif; ?>

<div class="form-group">
	<label class="control-label">Messsage Accounts:</label>
	<div>
		<?php $count_rows = (isset($user_row->deductions_amount) && $user_row->deductions_amount) || $this->input->post('deductions_state')?'2':'6'; ?>
		<?php  $options = array(
			'name' => 'area_account_message', 
			'class' => 'form-control area_account_message',
			'rows' => $count_rows,
			'placeholder' => 'employee account message...',
			'value' => set_value('area_account_message', isset($user_row->emp_message_on_account) ? $user_row->emp_message_on_account : $this->input->post('area_account_message')));?>

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
</div>
	

<!-- /end of client profile -->
	  
	<script>
		$(document).ready(function(){
			//$('.deductionChecker').height($('.deductions:first').height());
			$('#txtphone').inputmask(PHONE_NUMBER_MASK);
			$('.datepicker').datepicker({container:'.payroll-details', orientation : 'bottom'});
		});
        $(document).on('change', '#txtstarttime', function() {
            if ($(this).val() === '00:00' || $(this).val().length === 0) {
                $('#emp_check_work_time').prop('checked', false).change();
            }
        });
	</script>

	 
