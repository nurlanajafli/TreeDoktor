<div id="safety-meeting-form-modal" class="modal fade" role="dialog">
	<div class="modal-dialog modal-lg">
		<!-- Modal content-->
			<div class="modal-content">
				<form id="safety-meeting-form" data-type="ajax" data-url="<?php echo site_url('events/start_work'); ?>" data-callback="Events.start_work">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title text-success">TAILGATE SAFETY MEETING FORM | PRE JOB SAFETY ASSESSMENT</h4>
					</div>
					<div class="modal-body">
						<div class="row">
							<div class="col-md-4 col-sm-4 col-xs-6">
								<div class="row">
									<div class="col-md-5 col-sm-5 col-lg-5 col-xs-4">
										<strong>Date:</strong>
									</div>
									<div class="col-md-7 col-sm-7 col-xs-8"><?php echo date('Y-m-d'); ?></div>
								</div>
								<div class="row">
									<div class="col-md-5 col-sm-5 col-lg-5 col-xs-4">
										<strong>Client:</strong>
									</div>
									<div class="col-md-7 col-sm-7 col-xs-8"><?php echo $client_data->client_name; ?></div>
								</div>
								<div class="row">
									<div class="col-md-5 col-sm-5 col-lg-5 col-xs-4">
										<strong>Client Phone:</strong>
									</div>
									<div class="col-md-7 col-sm-7 col-xs-8"><i class="fa  fa-phone"></i>&nbsp;<a href="tel:<?php echo numberTo($client_contact['cc_phone']); ?>"><?php echo numberTo($client_contact['cc_phone']); ?></a>
									</div>
								</div>
								<div class="row">
									<?php if ($client_data->client_contact != $client_contact['cc_name']) : ?>
										<div class="col-md-5 col-sm-5 col-lg-5 col-xs-4">
											<strong>Contact Persone:</strong>
										</div>
										<div class="col-md-7 col-sm-7 col-xs-8">
											<?php echo $client_contact['cc_name']; ?>
										</div>
									<?php endif; ?>
								</div>
							</div>

							<div class="col-md-3 col-sm-4 col-xs-6">
								<div class="row">
									<div class="col-md-5 col-sm-5 col-lg-5 col-xs-4">
										<strong>Client Address:</strong>
									</div>
									<div class="col-md-7 col-sm-7 col-xs-8">
										<?php echo $estimate_data->lead_address . ", " . $estimate_data->lead_city; ?>,&nbsp;
										<?php echo $estimate_data->lead_state . ", " . $client_data->client_country; ?>,&nbsp;
										<?php echo isset($client_data->client_zip) ? $client_data->client_zip : $estimate_data->lead_zip; ?>
									</div>
								</div>
							</div>
							<div class="col-md-5 col-sm-4 col-xs-12">
								<div class="col-md-5 col-sm-5 col-lg-5 col-xs-4">
									<div class="hospital-icon"></div>
								</div>
								<div class="col-md-7 col-sm-7 col-xs-8">
									<p class="text-success"><?php echo $hospital_name; ?></p>
									<br>
									<?php echo $hospital_address; ?>
								</div>
							</div>

						</div>
						<div class="row">
							<div class="col-md-12 work-activity-container">
								<h4>PROJECT OR WORK ACTIVITY</h4>
								<?php foreach ($event_services as $key => $service_data) : ?>
									<div>
										<?php echo $service_data['service_description']; ?>
									</div>
								<?php endforeach; ?>
							</div>
						</div>

						<?php $this->load->view('events/_partials/safety_meeting_form'); ?>
						
						<hr>
						<div class="row">
							<div class="col-md-4 col-sm-4 team-list-text">
								<h5><strong>Team:</strong></h5>
								<?php if(isset($members) && !empty($members)): ?>
		                            <?php foreach($members as $member): ?>
		                            
		                                <p><?php echo $member['firstname'].' '.$member['firstname']; ?></p>
		                            
		                        	<?php endforeach; ?>
								<?php endif; ?>
								<br>
								<h5><strong>Date:&nbsp;</strong><?php echo date('d M Y h:i A'); ?></h5>
								<h5><strong>COMPLETED BY:&nbsp;</strong><?php echo $members[0]['team_leader_name']; ?></h5>
							</div>
							<div class="col-md-8 col-sm-8">
								
								<div class="form-group m-b-none">
									<input type="hidden" name="signature_image" id="signature-image" value="">
									<div id="signature-pad" class="signature-pad">
										<div class="signature-pad--body">
										<canvas></canvas>
										</div>
										<div class="signature-pad--footer">
										
										<div class="signature-pad--actions">
										<button type="button" class="button clear btn btn-default" data-action="clear">Clear&nbsp;<i class="fa fa-eraser"></i></button>
										<button type="button" class="button btn btn-info hidden" data-action="undo">Undo&nbsp;<i class="fa fa-undo"></i></button>

										
										<button type="button" class="button hidden" data-action="change-color">Change color</button>
										<div>
										<button type="button" id="save-png" class="button save btn btn-success hidden" data-action="save-png">Save as PNG</button>
										<button type="button" class="button save hidden" data-action="save-jpg">Save as JPG</button>
										<button type="button" class="button save  hidden" data-action="save-svg">Save as SVG</button>
										
										</div>

										</div>
										</div>
									</div>
									<span class="form-error text-danger"></span>
								</div>
							</div>
						</div>

					</div>
					<div class="modal-footer">
						<button class="btn btn-default btn-lg" data-dismiss="modal">Close</button>
						<button type="button" class="btn btn-success btn-lg confirm-start-work">Confirm Start</button>
					</div>
				</form>
			</div>
	</div>
</div>