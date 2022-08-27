<div class="form-group">
	<div class="row">
	
		<div class="col-lg-7 col-md-7 col-sm-7">
			<label class="control-label">
				Phone:
				<?php if(is_support((isset($user_row))?$user_row:[])):  ?>
				<span style="color:red">*</span>
				<?php endif; ?>
			</label>
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
		<div class="col-lg-5 col-md-5 col-sm-5">
			<label class="control-label">Internal IP Extension #</label>
			<div>
				<input type="number" style=" display: inherit;  " name="extention" value="<?php if (isset($user_row->extention_key) && $user_row->extention_key) : echo $user_row->extention_key; elseif($this->input->post('extention')) : echo $this->input->post('extention'); endif; ?>" id="userExt" class="form-control" maxlength="4">
			</div>
		</div>
	
	</div>

	
</div>

<div class="form-group row" id="verify_field">
    <div class="col-lg-9 col-md-9 col-sm-9 col-xs-9">
        <label class="control-label">Email:
            <?php if(is_support((isset($user_row))?$user_row:[])):  ?>
            <span style="color:red">*</span>
            <?php endif; ?>
        </label>
        <div style="display: flex !important">
            <input id="email_for_identity" type="text" name="userEmail" value="<?php if (isset($user_row->user_email) && $user_row->user_email) : echo $user_row->user_email;else: echo $this->input->post('userEmail'); endif; ?>" id="userEmail" class="form-control" maxlength="100" autocomplete="off">
            <input id="identity_id" type="hidden" data-identity-id="<?php echo $identity_id ?? '' ?>">
            <button id="check_status" class="btn btn-success" style="margin-left: 3px; display:none !important;">Check Status</button>
            <button id="verify_identity" class="btn btn-success" style="margin-left: 3px; display:none !important;">Verify Email</button>
            <div id="dUemail"></div>
        </div>
        <div style="display: inline-block;padding-top: 5px;">
            <span id="email_identity_status_field" style="display: none !important;">Current Status: <i><?php echo $current_email_identity_status ?? '';?></i></span>
            <span class="form-error text-danger"></span>
        </div>
    </div>
    <div class="col-lg-3 col-md-3 col-sm-3 col-xs-3" style="padding-top: 5px;">
        <label class="control-label block">&nbsp;</label>
        <button type="button" href="#" class="btn btn-block btn-success" data-toggle="modal" data-target="#signature-modal">Signature</button>
        <?php $this->load->view('form/signature_modal'); ?>
    </div>
</div>

<div class="form-group">
	
</div>

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
<div class="form-group">
	<div class="row">
		<div class="col-md-6 col-sm-6">
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
		<div class="col-md-6 col-sm-6">
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


<div class="form-group" style="display:none">
	
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

<?php if(is_support((isset($user_row))?$user_row:[])):  ?>
<!--<hr class="hidden-lg hidden-xs" style="margin-bottom: 26px;">-->
<?php endif; ?>

<hr class="hidden-xs" style="margin: 38px 0 54px;">

<div class="form-group">
	<label class="hidden-lg hidden-xs">Picture:</label>
	<div class="dropzone dz-clickable picture-dropzone">
		
	<div id="dcountry"></div>
	<div class="dz-default dz-message m-n">
		<?php if (isset($user_row->picture) && !empty($user_row->picture)): ?>
			<div class="thumb m-r" style="width: 85px;">
				<img src="<?php echo base_url() . PICTURE_PATH . $user_row->picture; ?>" style="border-radius: 48px;">
			</div>
		<?php else: ?>
		<span class="block m-top-20"><i class="fa fa-user"></i>&nbsp;User Avatar: Drop file here to upload</span>
		<?php endif; ?>
		
	</div>
	</div>

	<?php
	$data = array(
		'name' => 'picture',
		'id' => 'picture',
		'class'=>'hidden',
		'value' => set_value('picture', isset($user_row->picture) ? $user_row->picture : "")
	);
	echo form_upload($data);
	?>
</div>
<?php /*
<div class="form-group">
	<label class="control-label">Picture:</label> 
		<div class="dropzone">
			<div class="fallback">
			<?php
			$data = array(
				'name' => 'picture',
				'id' => 'picture',
				'value' => set_value('picture', isset($user_row->picture) ? $user_row->picture : "")
			);
			echo form_upload($data);
			?>
			</div>
			<div id="dcountry"></div>
		</div>
</div>*/ ?>