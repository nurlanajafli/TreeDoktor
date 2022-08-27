<div id="addEstimateCall" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
     aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content panel panel-default p-n">
			<header class="panel-heading">Add Estimate Contact</header>

			<div class="p-10">
				Contact Info:<br>
				<?php $options = array(
					'name' => 'contact_info',
					'id' => 'contact_info',
					'rows' => '4',
					'class' => 'form-control',
					'Placeholder' => 'Contact Info');

				echo form_textarea($options);
				?>

			</div>

			<div class="modal-footer">
				<div class="pull-right ">
					<button class="btn btn-success m-right-5" id="addContact"
					        data-estimate-id="<?php echo $estimate_data->estimate_id; ?>"/>
					Add</button>
					<button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
				</div>
			</div>
		</div>
	</div>
</div>
