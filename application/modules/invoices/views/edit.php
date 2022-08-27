<?php $this->load->view('includes/header'); ?>

	<script type="text/javascript">
		$(document).ready(function () {


			//end of document.ready function
		});
	</script>

	<!-- Client profile -->
	<div class="row">
		<div class="grid_9 filled_white rounded shadow h-230 overflow">
			<!-- Client header -->
			<div class="module-header">
				<div class="module-title">Client Profile
					<a href="#myModal" role="button" class="btn btn-mini pull-right" data-toggle="modal"
					   style="margin-top: -1px"><i class="icon-pencil"></i></a>
				</div>
				<div class="<?php if ($client_data->client_type == 1) {
					echo "icon_residential";
				} ?> <?php if ($client_data->client_type == 2) {
					echo "icon_corp";
				} ?> <?php if ($client_data->client_type == 3) {
					echo "icon_municipal";
				} ?>"></div>
			</div>
			<!-- client body -->
			<div class="p-15">
				<h3><?php echo $client_data->client_name; ?></h3>
				<ul class="inline">
					<li>
						<address>
							<?php echo $client_data->client_address; ?><br>
							<?php echo $client_data->client_city; ?>&#44;&nbsp;<?php echo $client_data->client_state; ?>
							<br>
							<?php echo $client_data->client_country; ?>&nbsp;<?php echo $client_data->client_zip; ?>
						</address>
					</li>
					<li>
						<address>
							<abbr title="Phone">Ph:</abbr><?php echo $client_data->cc_phone; ?><br>
							<abbr title="Mobile">Mob:</abbr><?php echo $client_data->client_mobile; ?><br>
							<abbr title="Fax">Fax:</abbr><?php echo $client_data->client_fax; ?>
						</address>
					</li>
					<li>
						<address>
							<a href="mailto:<?php echo $client_contact['cc_email']; ?>"><?php echo $client_contact['cc_email']; ?></a><br>
							<?php $client_web = prep_url($client_data->client_web); ?>
							<a href="<?php echo $client_web; ?>"><?php echo $client_web; ?></a><br>
							<span class="muted">Contact:&nbsp;<?php echo $client_data->client_contact; ?></span><br>
						</address>
					</li>
				</ul>
			</div>

		</div>
		<!--/grid 9-->
		<div class="grid_3 rounded shadow overflow">
			<!--map-->
			<?php $tags = str_replace(' ', '+', $client_data->client_address); ?>
			<img
				src="https://maps.googleapis.com/maps/api/staticmap?center=<?php echo $tags; ?>+<?php echo $client_data->client_city; ?>+<?php echo $client_data->client_state; ?>+<?php echo $client_data->client_country; ?>&zoom=11&size=230x230&markers=<?php echo $tags; ?>+<?php echo $client_data->client_city; ?>+<?php echo $client_data->client_state; ?>+<?php echo $client_data->client_country; ?>"
				alt="Google Map" height="230" width="230">
		</div>
		<!--/grid 3-->
	</div>
	<!-- /Client profile -->



	<!-- Update invoice -->
	<div class="row m-top-10">
		<div class="grid_12 filled_white rounded shadow overflow">

			<!-- Client header -->
			<div class="module-header">
				<div class="module-title">Update Invoice for&nbsp;<?php echo $client_data->client_name; ?></div>
			</div>

			<!-- Invoice Body -->
			<?php echo form_open('invoices/update_invoice'); ?>
			<!-- Hidden values required for invoice and client notes -->
			<?php  $hidden = array(
				'client_id' => $client_data->client_id,
				'invoice_id' => $invoices_data->id,
				'invoice_no' => $invoices_data->invoice_no); //'estimator_name'	=> $lead->lead_estimator
			echo form_hidden($hidden); ?>

			<!-- Finished How-->
			<div class="row p-top-10">
				<div class="grid_9">
					<div class="p-sides-15">
						<label>Finished How :</label>
						<?php $options = array(
							'name' => 'in_finished_how',
							'id' => 'in_finished_how',
							'rows' => '2',
							'class' => 'input-block-level',
							'Placeholder' => 'Over the phone, in person, by email etc.',
							'value' => $invoices_data->in_finished_how);

						echo form_textarea($options); ?>
					</div>
				</div>
			</div>
			<!-- /Finished How -->

			<!-- Extra Note Crew-->
			<div class="row p-top-10">
				<div class="grid_9">
					<div class="p-sides-15">
						<label>Extra Note Crew:</label>
						<?php $options = array(
							'name' => 'in_extra_note_crew',
							'id' => 'in_extra_note_crew',
							'rows' => '2',
							'class' => 'input-block-level',
							'Placeholder' => 'Credit card and number- cash so on or deposit waved etc.',
							'value' => $invoices_data->in_extra_note_crew);

						echo form_textarea($options); ?>
					</div>
				</div>
			</div>
			<!-- /Extra Note Crew -->

			<!-- Payment mode-->
			<?php if ((int)$invoices_data->completed) { ?>
				<div class="row p-top-10">
					<div class="grid_9">
						<div class="p-sides-15">
							<label>Payment Mode:</label>
							<?php $options = array(
								'name' => 'payment_mode',
								'id' => 'payment_mode',
								'rows' => '2',
								'class' => 'input-block-level',
								'Placeholder' => 'Credit card and number- cash so on or deposit waved etc.',
								'value' => $invoices_data->payment_mode);

							echo form_input($options); ?>
						</div>
					</div>
				</div>
			<?php } ?>
			<!-- /Payment mode -->
		</div>
		<!-- /grid_12-->
	</div><!-- /row -->

	<!-- Preview and Create -->
	<div class="row p-top-10">
		<div class="grid_12 filled_grey rounded shadow overflow">
			<div class="p-sides-15">
				<?php echo anchor('invoices/', 'Cancel', 'class="btn btn-info pull-right m-top-10"');

				$data = array(
					'name' => 'submit',
					'value' => 'Update',
					'class' => 'btn btn-success pull-right',
					'style' => 'margin:10px');
				echo form_submit($data);

				echo form_close(); ?>
			</div>
		</div>
	</div><!-- /Preview and Create-->

	<!-- /Update Estimate -->

<?php $this->load->view('includes/footer'); ?>
