<?php $this->load->view('includes/header'); ?>
<link rel="stylesheet" href="<?php echo base_url('assets/css/colpick.css'); ?>"/>
<script src="<?php echo base_url('assets/js/colpick.js'); ?>"></script>
<script src="<?php echo base_url('assets/js/jasny-bootstrap.js'); ?>"></script>
<style>.table.table-hover tr td {
		line-height: 30px;
	}</style>
<section class="scrollable p-sides-15">
<ul class="breadcrumb no-border no-radius b-b b-light pull-in">
	<li><a href="<?php echo base_url(); ?>"><i class="fa fa-home"></i> Home</a></li>
	<li><a href="<?php echo base_url('user'); ?>">Users</a></li>
	<li class="active"><?php if (isset($user_row->user_id)) : ?>User - <?php echo $user_row->firstname; ?><?php else : ?>New User<?php endif; ?></li>
</ul>
<section class="col-md-12 panel panel-default p-n">
	<?php
$attri = array(
	'name' => 'user_form',
	'id' => 'user_form');

if (isset($edit)) {
	$url = base_url() . 'user/save/' . $user_row->id;
	//echo form_open_multipart('user/save/' . $user_row->id, $attri);
} else {
	$url = base_url() . 'user/save/';
	//echo form_open_multipart('user/save', $attri);
}?>
<header class="panel-heading">User Profile</header>
<form action="<?php echo $url; ?>" method="POST"  id="user_form" name="user_form" enctype="multipart/form-data">

<div class="">
<!--Form -->
<div class="col-md-8">
	<div class="row">
		<div class="col-md-3">
			<div class="form-group">
				<label class="control-label">User type <span style="color:red">*</span></label>
				<div>
					<?php if ($this->session->userdata('user_type') == "admin") : ?>
					<?php
					$options = array(
						'' => 'Select',
						'admin' => 'Admin',
						'user' => 'User',
					);
					$selected = "";
					if (isset($edit)) $selected = $user_row->user_type;
					elseif($this->input->post('selectusertype')) $selected = $this->input->post('selectusertype');
					echo form_dropdown('selectusertype', $options, $selected, 'class="form-control" style="display:inline-block;"');
					?>
				<?php else : ?>
					<label class="control-label">
						<?php echo isset($user_row->user_type) ? $user_row->user_type : '-'; ?>
					</label>
				<?php endif;  ?>
				</div>
			</div>
		</div>
		<div class="col-md-7">
			<div class="form-group" style="margin-top: 8px;">
				<label class="control-label">&nbsp;</label>
				<div>
					<div data-toggle="buttons" class="text-center btn-group m-t-n-xs">
						<?php $checked = $class = ''; ?>
						<?php if ($this->input->post('is_field_estimator') || set_value('is_field_estimator') == '1' || (isset($user_row->emp_field_estimator) && $user_row->emp_field_estimator == '1')) : ?>
							<?php $checked = 'checked="checked"'; ?>
							<?php $class = 'active'; ?>
						<?php endif; ?>
						<label class="btn btn-xs btn-info <?php echo $class; ?> m-r-xs p-5">
							<i class="fa fa-check text-active"></i> Is estimator
							<input class="" type="checkbox"  <?php echo $checked; ?> name="is_field_estimator">
						</label>&nbsp;
						<?php $checked = $class = ''; ?>
						<?php if ($this->input->post('is_feild_worker') || set_value('is_feild_worker') == '1' || (isset($user_row->emp_feild_worker) && $user_row->emp_feild_worker == '1')) : ?>
							<?php $checked = 'checked="checked"'; ?>
							<?php $class = 'active'; ?>
						<?php endif; ?>
						<label class="btn btn-xs btn-info  <?php echo $class; ?> m-r-xs p-5">
							<i class="fa fa-check text-active"></i> At Schedule
							<input class="" type="checkbox"  <?php echo $checked; ?> name="is_feild_worker">
						</label>&nbsp;
					</div>
					<div data-toggle="buttons" class="text-center btn-group m-t-n-xs">
						<?php $checked = $class = ''; ?>
						<?php if ((isset($user_row->worker_type) && $user_row->worker_type == 1) || $this->input->post('worker_type') == 1) : ?>
							<?php $checked = 'selected="selected"'; ?>
							<?php $class = 'active'; ?>
						<?php endif; ?>
						<label class="btn btn-xs btn-info  <?php echo $class; ?> m-r-xs p-5">
							<i class="fa fa-check text-active"></i> Field Worker
							<input class="" type="radio" value="1" <?php echo $checked; ?> name="worker_type">
						</label>&nbsp;
						<?php $checked = $class = ''; ?>
						<?php if ((isset($user_row->worker_type) && $user_row->worker_type == 2) || $this->input->post('worker_type') == 2) : ?>
							<?php $checked = 'selected="selected"'; ?>
							<?php $class = 'active'; ?>
						<?php endif; ?>
						<label class="btn btn-xs btn-info  <?php echo $class; ?> m-r-xs p-5">
							<i class="fa fa-check text-active"></i> Support
							<input class="" type="radio" value="2" <?php echo $checked; ?> name="worker_type">
						</label>&nbsp;
					</div>
				</div>
			</div>
		</div>
		<div class="col-md-2">
			<div class="form-group">
				<label class="control-label">#</label>
				<div>
					<input type="number" style=" display: inherit;  " name="extention" value="<?php if (isset($user_row->extention_key) && $user_row->extention_key) : echo $user_row->extention_key; elseif($this->input->post('extention')) : echo $this->input->post('extention'); endif; ?>" id="userExt" class="form-control" maxlength="4">
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-3">
			<div class="form-group">
				<label class="control-label">Email <span style="color:red">*</span></label>
				<div>
					<input type="text" name="userEmail" value="<?php if (isset($user_row->user_email) && $user_row->user_email) : echo $user_row->user_email;else/*if($this->input->post('extention'))*/ : echo $this->input->post('userEmail'); endif; ?>" id="userEmail" class="form-control" maxlength="100">
					<div id="dUemail"></div>
				</div>
			</div>
		</div>
		<div class="col-md-3">
			<div class="form-group">
				<label class="control-label">Phone:</label>
				<div>
					<?php
					$emp_phone_attributes = array(
						'name' => 'txtphone',
						'id' => 'txtphone',
						'class' => 'form-control',
						'value' => set_value('txtphone', isset($user_row->emp_phone) ? $user_row->emp_phone : $this->input->post('txtphone')),
						'maxlength' => '100'
					);
					?>
					<span class="control-group <?php echo (form_error('txtphone'))?'error':'success'; ?>">
					<?php echo form_input($emp_phone_attributes) ?>
					</span>
				</div>
			</div>
		</div>
		<div class="col-md-3">
			<div class="form-group">
				<label class="control-label">Color:</label>
				<div>
					<?php
					$data = array(
						'name' => 'color',
						'id' => 'color',
						'readonly' => 'readonly',
						'class' => 'form-control mycolorpicker',
						'value' => set_value('color', isset($user_row->color) ? $user_row->color : $this->input->post('color'))
					);
					echo form_input($data);
					?>
				</div>
			</div>
		</div>
		<div class="col-md-3">
			<div class="form-group">
				<label class="control-label">Picture:</label> 
					<div>
						<?php
						$data = array(
							'name' => 'picture',
							'id' => 'picture',
							'value' => set_value('picture', isset($user_row->picture) ? $user_row->picture : "")
						);
						echo form_upload($data);
						?>
						<div id="dcountry"></div>
					</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-3">
			<div class="form-group">
				<label class="control-label">Login <span style="color:red">*</span></label>
				<div>
				<?php
				$data = array(
					'name' => 'txtemail',
					'id' => 'txtemail',
					'class' => 'form-control',
					'value' => set_value('txtemail', isset($user_row->emailid) ? $user_row->emailid : $this->input->post('txtemail')),
					'maxlength' => '100'
				);
				echo form_input($data);
				?>
				<div id="demail"></div>
				</div>
			</div>
		</div>
		<div class="col-md-3">
			<div class="form-group">
				<label class="control-label">Password <span style="color:red">*</span></label>
				<div>
					<?php
					$data = array(
						'name' => 'txtpassword',
						'class' => 'form-control',
						'id' => 'txtpassword',
						'maxlength' => '50'
					);
					echo form_password($data);
					?>
					<div id="dpassword"></div>
				</div>
			</div>
		</div>
		<div class="col-md-3">
			<div class="form-group">
				<label class="control-label">Firstname <span style="color:red">*</span></label>
				<div>
					<?php
					$data = array(
						'name' => 'txtfirstname',
						'id' => 'txtfirstname',
						'class' => 'form-control',
						'value' => set_value('txtfirstname', isset($user_row->firstname) ? $user_row->firstname : $this->input->post('txtfirstname')),
						'maxlength' => '50'
					);
					echo form_input($data);
					?>
					<div id="dfirstname"></div>
				</div>
			</div>
		</div>
		<div class="col-md-3">
			<div class="form-group">
				<label class="control-label">Lastname <span style="color:red">*</span></label>
				<div>
					<?php
					$data = array(
						'name' => 'txtlastname',
						'id' => 'txtlastname',
						'class' => 'form-control',
						'value' => set_value('txtlastname', isset($user_row->lastname) ? $user_row->lastname : $this->input->post('txtlastname')),
						'maxlength' => '50'
					);
					echo form_input($data);
					?>
					<div id="dlastname"></div>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-3">
			<div class="form-group">
				<label class="control-label">Address</label>
				<div>
					<?php
					$data = array(
						'name' => 'txtaddress1',
						'id' => 'txtaddress1',
						'class' => 'form-control',
						'data-autocompleate' => 'true',
						'data-part-address' => 'address',
						'value' => set_value('txtaddress1', isset($user_row->address1) ? $user_row->address1 : $this->input->post('txtaddress1')),
						'maxlength' => '100'
					);
					echo form_input($data);
					?>
					<div id="daddress1"></div>
				</div>
			</div>
		</div>
		<div class="col-md-3">
			<div class="form-group">
				<label class="control-label">City</label>
				<div>
					<?php
					$data = array(
						'name' => 'txtcity',
						'id' => 'txtcity',
						'class' => 'form-control locality',
						'data-part-address' => 'locality',
						'value' => set_value('txtcity', isset($user_row->city) ? $user_row->city : $this->input->post('txtcity')),
						'maxlength' => '100'
					);
					echo form_input($data);
					?>
					<div id="dcity"></div>
				</div>
			</div>
		</div>
		<div class="col-md-3">
			<div class="form-group">
				<label class="control-label">State/Province</label>
				<div>
					<?php
					$data = array(
						'name' => 'txtstate',
						'id' => 'txtstate',
						'class' => 'form-control',
						'data-part-address' => "administrative_area_level_1",
						'value' => set_value('txtstate', isset($user_row->state) ? $user_row->state : $this->input->post('txtstate')),
						'maxlength' => '100'
					);
					echo form_input($data);
					?>
					<div id="dstate"></div>
				</div>
			</div>
		</div>
		
	</div>
	<div style="display:none">
		<?php
			$data = array(
				'name' => 'txtaddress2',
				'id' => 'txtaddress2',
				'class' => 'form-control',
				'value' => set_value('txtaddress2', isset($user_row->address2) ? $user_row->address2 : $this->input->post('txtaddress2')),
				'maxlength' => '100'
			);
			echo form_input($data);
			?>
			<div id="daddress2"></div>
	</div>
</div>
<!-- Image -->
<div class="col-md-4 p-top-10 m-bottom-10 p-right-10 p-left-30 pull-right">
<?php if (isset($user_row->picture) && !empty($user_row->picture)) { ?>
		<div class=" ">
			<img src="<?php echo base_url() . PICTURE_PATH . $user_row->picture; ?>">

		</div>
<?php } ?>
		<label class="  control-label">Signature:</label>
		<div id="epiceditor"  style="height: 490px;"></div>
		<textarea id="epiceditor-content" style="display: none;" name="user_signature"><?php echo isset($user_row->user_signature) ? $user_row->user_signature : $this->input->post('user_signature');?></textarea>
</div>


<!--
<div class="employee_settings" style="width:100%; <?php /* if((!isset($user_row->active_status) || $user_row->active_status == 'no') && !$this->input->post('active_status') == 'no') :  ?>display:none;<?php endif; */?>">
-->
	<?php $this->load->view('form/employee_form');?>
<!--
	</div>
-->
</div>

	<?php $this->load->view('form/docs_form');?>

<div  >
<table class="table table-hover">
<?php if ($this->session->userdata('user_type') == "admin") : ?>
<tr>
	<th>Rule Name</th>
	<th>Access</th>
</tr>
<?php
if (isset($module_opt) && !empty($module_opt)) {
	foreach ($module_opt as $row) {
		if (isset($user_module) && !empty($user_module)) {

			$module_status = '';
			foreach ($user_module as $module) {
				if ($module->module_id == $row->module_id) {
					$module_status = $module->module_status;
					break;
				} else {
					$module_status = $this->input->post($module->module_id);
				}
			}
		} else {
			$module_status = $this->input->post($row->module_id);
		}
		if ($row->module_id == 'CL') {  ?>
            <tr>
                <td><?php echo $row->module_desc; ?></td>
                <td collspan="4">
                    <?php

                    if (set_value($row->module_id) == '1' || @$module_status == '1') {
                        $checked = TRUE;
                    } else {
                        $checked = FALSE;
                    }

                    $data = array(
                        'name' => $row->module_id,
                        'id' => $row->module_id . '_1',
                        'class' => $row->module_id,
                        'value' => '1',
                        'checked' => $checked,
                        'style' => 'margin:10px');

                    echo form_radio($data); ?>
                    Full

                    <?php if (set_value($row->module_id) == '2' || @$module_status == '2') {
                        $checked2 = TRUE;
                    } else {
                        $checked2 = FALSE;
                    }
                    $data = array(
                        'name' => $row->module_id,
                        'id' => $row->module_id . '_2',
                        'class' => $row->module_id,
                        'value' => '2',
                        'checked' => $checked2,
                        'style' => 'margin:10px');
                    echo form_radio($data); ?>
                    Limited

                    <?php if (set_value($row->module_id) == '0' || @$module_status == '0' || @$module_status == '') {
                        $checked1 = TRUE;
                    } else {
                        $checked1 = FALSE;
                    }

                    $data = array(
                        'name' => $row->module_id,
                        'id' => $row->module_id . '_0',
                        'class' => $row->module_id,
                        'value' => '0',
                        'checked' => $checked1,
                        'style' => 'margin:10px');
                    echo form_radio($data); ?>
                    None


                    <div id="dcountry"></div>
                </td>
            </tr>
		<?php } else if ($row->module_id == 'TSKS') { ?>
			<tr>
				<td><?php echo $row->module_desc; ?>  Access</td>
				<td>
					<?php

					if (set_value($row->module_id) == '1' || @$module_status == '1') {
						$checked = TRUE;
					} else {
						$checked = FALSE;
					}

					$data = array(
						'name' => $row->module_id,
						'id' => $row->module_id . '_1',
						'class' => $row->module_id,
						'value' => '1',
						'checked' => $checked,
						'style' => 'margin:10px');

					echo form_radio($data); ?>
					Show
					<?php if (set_value($row->module_id) == '0' || @$module_status == '0' || @$module_status == '') {
						$checked1 = TRUE;
					} else {
						$checked1 = FALSE;
					}

					$data = array(
						'name' => $row->module_id,
						'id' => $row->module_id . '_0',
						'class' => $row->module_id,
						'value' => '0',
						'checked' => $checked1,
						'style' => 'margin:10px');
					echo form_radio($data); ?>
					Hide

					<?php if (set_value($row->module_id) == '2' || @$module_status == '2') {
						$checked2 = TRUE;
					} else {
						$checked2 = FALSE;
					}
					$data = array(
						'name' => $row->module_id,
						'id' => $row->module_id . '_2',
						'class' => $row->module_id,
						'value' => '2',
						'checked' => $checked2,
						'style' => 'margin:10px');
					echo form_radio($data); ?>
					Own Tasks
					
					<div id="dcountry"></div>
				</td>
			</tr>
		<?php } else if ($row->module_id == 'STP') { ?>
			<tr>
				<td><?php echo $row->module_desc; ?>  Access</td>
				<td>
					<?php

					if (set_value($row->module_id) == '2' || @$module_status == '2') {
						$checked = TRUE;
					} else {
						$checked = FALSE;
					}

					$data = array(
						'name' => $row->module_id,
						'id' => $row->module_id . '_2',
						'class' => $row->module_id,
						'value' => '2',
						'checked' => $checked,
						'style' => 'margin:10px');

					echo form_radio($data); ?>
					My Stumps
					<?php

					if (set_value($row->module_id) == '1' || @$module_status == '1') {
						$checked = TRUE;
					} else {
						$checked = FALSE;
					}

					$data = array(
						'name' => $row->module_id,
						'id' => $row->module_id . '_1',
						'class' => $row->module_id,
						'value' => '1',
						'checked' => $checked,
						'style' => 'margin:10px');

					echo form_radio($data); ?>
					All
					<?php if (set_value($row->module_id) == '0' || @$module_status == '0' || @$module_status == '') {
						$checked1 = TRUE;
					} else {
						$checked1 = FALSE;
					}

					$data = array(
						'name' => $row->module_id,
						'id' => $row->module_id . '_0',
						'class' => $row->module_id,
						'value' => '0',
						'checked' => $checked1,
						'style' => 'margin:10px');
					echo form_radio($data); ?>
					No
					<?php if (set_value($row->module_id) == '3' || @$module_status == '3') {
						$checked1 = TRUE;
					} else {
						$checked1 = FALSE;
					}

					$data = array(
						'name' => $row->module_id,
						'id' => $row->module_id . '_3',
						'class' => $row->module_id,
						'value' => '3',
						'checked' => $checked1,
						'style' => 'margin:10px');
					echo form_radio($data); ?>
					Readonly List And Map
					<div id="dcountry"></div>
				</td>
			</tr>
		<?php } else if ($row->module_id == 'STA') { ?>
			<tr>
				<td><?php echo $row->module_desc; ?>  Access</td>
				<td>
					<?php

					if (set_value($row->module_id) == '1' || @$module_status == '1') {
						$checked = TRUE;
					} else {
						$checked = FALSE;
					}

					$data = array(
						'name' => $row->module_id,
						'id' => $row->module_id . '_1',
						'class' => $row->module_id,
						'value' => '1',
						'checked' => $checked,
						'style' => 'margin:10px');

					echo form_radio($data); ?>
					Yes
					<?php if (set_value($row->module_id) == '0' || @$module_status == '0' || @$module_status == '') {
						$checked1 = TRUE;
					} else {
						$checked1 = FALSE;
					}

					$data = array(
						'name' => $row->module_id,
						'id' => $row->module_id . '_0',
						'class' => $row->module_id,
						'value' => '0',
						'checked' => $checked1,
						'style' => 'margin:10px');
					echo form_radio($data); ?>
					No
					<div id="dcountry"></div>
				</td>
			</tr>
		<?php } else { ?>
			<tr>
				<td><?php echo $row->module_desc; ?>  Access </td>
				<td>
					<?php

					if (set_value($row->module_id) == '1' || @$module_status == '1') {
						$checked = TRUE;
					} else {
						$checked = FALSE;
					}

					$data = array(
						'name' => $row->module_id,
						'id' => $row->module_id . '_1',
						'class' => $row->module_id,
						'value' => '1',
						'checked' => $checked,
						'style' => 'margin:10px');

					echo form_radio($data); ?>
					Yes
					<?php if (set_value($row->module_id) == '0' || @$module_status == '0' || @$module_status == '') {
						$checked1 = TRUE;
					} else {
						$checked1 = FALSE;
					}

					$data = array(
						'name' => $row->module_id,
						'id' => $row->module_id . '_0',
						'class' => $row->module_id,
						'value' => '0',
						'checked' => $checked1,
						'style' => 'margin:10px');
					echo form_radio($data); ?>
					NO
					<div id="dcountry"></div>
				</td>
			</tr>
		<?php
		}
	}
}
?>
<?php endif; ?>
<tr>
	<td colspan="2" align="center">
		<?php
		//$attributes = array("onclick" => "return validate()");
		$value = "Add";
		if (isset($edit)) $value = 'Update';
		echo form_submit('submit', $value, 'class="btn btn-info pull-right m-top-10" id="sub_btn"');
		echo anchor('user/', 'Cancel', 'class="btn btn-info pull-right m-top-10 m-right-10"');?>
	</td>
</tr>
</table>
</div>
</form>
</section>
</section>

</section>
</section>


<!-- /End Form -->
<!-- End Data -->
<?php $this->load->view('includes/footer'); ?>

<script type="text/javascript">
	var doc_tpl = <?php echo $doc_tpl; ?>;
	$(document).ready(function () {
		editor.preview();
		$('.support').click(function(){
			var val = $(this).prop('checked');
			if(val)
				$('#twilio_level').removeAttr('disabled');
			else
				$('#twilio_level').attr('disabled', 'disabled');
		});
		$('#txtyearlyrate').keyup(function(){
			var obj = $(this); 
			var last = <?php echo isset($employee_row->emp_hourly_rate) ? json_encode($employee_row->emp_hourly_rate) : 0 ; ?>;
			var val = $('#txtyearlyrate').val();
			if(val != '' && val != '0' && val)
			{
				rate = val/26/80;
				$('#content').find('#txthourlyrate').val(parseFloat(rate.toFixed(4)));
				return false;
			}
			else
				$('#content').find('#txthourlyrate').val(last);
			console.log(last); 
			return false;
		});

		/*$('.select_active_status').on('change', function(){
			console.log($(this).val()); 
			$('.employee_settings').slideToggle("slow");
			
			
		});*/
		$('.RPS').click(function () {
			if ($('input[name="RPS_GEN"]:checked').val() == 1) {
				$('#RPS_1').prop('checked', true);
			}
			if ($('input[name="RPS_EST"]:checked').val() == 1) {
				$('#RPS_1').prop('checked', true);
			}
			if ($('input[name="RPS_WO"]:checked').val() == 1) {
				$('#RPS_1').prop('checked', true);
				$('#RPS_0').prop('checked', false);

			}
			if ($('input[name="RPS_IN"]:checked').val() == 1) {
				$('#RPS_1').prop('checked', true);
			}
			if ($('input[name="RPS_PR"]:checked').val() == 1) {
				$('#RPS_1').prop('checked', true);
			}
			if ($('input[name="RPS_PRO"]:checked').val() == 1) {
				$('#RPS_1').prop('checked', true);
			}
		});
		$('.RPS_GEN').click(function () {
			if ($('input[name="RPS_GEN"]:checked').length > 0) {
				if ($('input[name="RPS_GEN"]:checked').val() == 1) {
					$('#RPS_1').prop('checked', true);
				}
			}
		});
		$('.RPS_EST').click(function () {
			if ($('input[name="RPS_EST"]:checked').length > 0) {
				if ($('input[name="RPS_EST"]:checked').val() == 1) {
					$('#RPS_1').prop('checked', true);
				}
			}
		});
		$('.RPS_WO').click(function () {
			if ($('input[name="RPS_WO"]:checked').length > 0) {
				if ($('input[name="RPS_WO"]:checked').val() == 1) {
					$('#RPS_1').prop('checked', true);
				}
			}
		});
		$('.RPS_IN').click(function () {
			if ($('input[name="RPS_IN"]:checked').length > 0) {
				if ($('input[name="RPS_IN"]:checked').val() == 1) {
					$('#RPS_1').prop('checked', true);
				}
			}
		});
		$('.RPS_PR').click(function () {
			if ($('input[name="RPS_PR"]:checked').length > 0) {
				if ($('input[name="RPS_PR"]:checked').val() == 1) {
					$('#RPS_1').prop('checked', true);
				}
			}
		});
		$('.RPS_PRO').click(function () {
			if ($('input[name="RPS_PRO"]:checked').length > 0) {
				if ($('input[name="RPS_PRO"]:checked').val() == 1) {
					$('#RPS_1').prop('checked', true);
				}
			}
		});

		 

		$(document).on('click', '.delete-doc', function() {
			if($(this).is('disabled'))
				return false;

			$(this).parents('.certificate-section:first').remove();

			$.each($('.certificate-section .doc_title'), function(key, val) {
				if(($(val).text().indexOf('Document #') + 1))
					$(val).text('Document #' + (key + 1));

			});

			return false;
		});

		$(document).on('click', '.add-doc', function() {
			if($(this).is('disabled'))
				return false;
			
			$('.certificate-section:last').after(doc_tpl.html);
			$('.certificate-section:last input').val('');
            $('.certificate-section:last .delete-doc').show();
			$('.datepicker').datepicker();
			$.each($('.certificate-section .doc_title'), function(key, val) {

				if(($(val).text().indexOf('Document #') + 1))
					$(val).text('Document #' + (key + 1));
				if(key + 1 == $('.certificate-section .doc_title').length) {
					$(val).parents('.certificate-section:first').find('.doc_title').val('Document #' + (key + 1));
				}
			});

			return false;
		});

	});
	$(function () {

		var setMyColorpicker = function (elem) {
			$(elem).colpick({
				submit: 0,
				colorScheme: 'dark',
				onChange: function (hsb, hex, rgb, el, bySetColor) {
					$(el).css('background-color', '#' + hex);
					if (!bySetColor) {
						$(el).val('#' + hex);
						for (var i = 0, len = scopes.data.items.length; i < len; i++) {
							var curItem = scopes.data.items[i];
						}
					}
				}
			}).keyup(function () {
				$(this).colpickSetColor(this.value);
			});
			$('.mycolorpicker').each(function () {
				var current_color = $(this).val();
				var current_color_short = current_color.replace(/^#/, '');
				$(this).colpickSetColor(current_color_short);
			});
		};
		window.setMyColorpicker = setMyColorpicker;
		setTimeout(function () {
			setMyColorpicker($('.mycolorpicker'));
		}, 200);
	});
	
	/*function checkActive(obj)
	{
		console.log($(obj).parent().find('.active_employee').prop('checked', true), $(obj).val());
		if($(obj).val() == 'yes')
			$(obj).parent().find('.active_employee').prop('checked', true);
		else if($(obj).val() == 'no')
			$(obj).parent().find('.active_employee').prop('checked', false);
	}*/
</script>
<script src="<?php echo base_url('assets/vendors/notebook/js/markdown/epiceditor.min.js'); ?>"></script>
<script src="<?php echo base_url('assets/vendors/notebook/js/markdown/demo.js'); ?>"></script>
