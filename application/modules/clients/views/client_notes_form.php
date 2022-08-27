<section class="panel panel-default p-n clearfix">
	<div id="borderless_form" class="p-10">
		<!--form-->
		<?php echo form_open_multipart(base_url() . "clients/add_note") ?>
		<?php
		$options = array(
			'name' => 'new_note',
			'label' => 'New note',
			'rows' => '4',
			'class' => 'input-block-level',
			'placeholder' => 'Update client notes...');
		?>
		<?php echo form_textarea($options) ?>
		<?php
		$hidden = array(
			'author' => $this->session->userdata('user_id'),
			'robot' => "no",
			'client_id' => $client_data->client_id);
		?>
		<?php echo form_hidden($hidden); ?>
	</div>
	<span class="btn btn-primary btn-file m-l-sm m-t-md pull-left"
	      style="background: url('<?php echo base_url(); ?>assets/img/attach.png') no-repeat; height: 25px;border-radius: 5px;cursor: pointer;">
		<input type="file" name="file" class="fileToUpload btn-upload" id="fileToUpload_2" data-item_id="2">
	</span>
	<span class="m-t-md m-l-sm" style="display:inline-block;"></span>

	<div class="pull-right">
		<?php if(isset($estimate_data) && isset($estimate_data->lead_id) && $estimate_data->lead_id) : ?>
			<input type="hidden" name="lead_id" value="<?php echo $estimate_data->lead_id; ?>">
		<?php endif; ?>
		<?php echo form_submit('submit', 'Post', "class='btn btn-info pull-right m-10'"); ?>
	</div>
	<?php echo form_close() ?>
</section>
