<div id="report-form-modal" class="modal fade" role="dialog">
	<div class="modal-dialog modal-lg">
	<!-- Modal content-->
		<div class="modal-content">
			<form id="report-form" data-type="ajax" data-url="<?php echo site_url('events/stop_work'); ?>" data-callback="Events.stop_work">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal">&times;</button>
					<h4 class="modal-title text-success pull-left">REPORT</h4>
					<span class="pull-right m-right-20 p-top-5 hidden-xs hidden-sm">
						<span class="h5">
							<span class="glyphicon glyphicon-map-marker text-warning"></span>&nbsp;
							<strong>Workorder:</strong>&nbsp;<?php echo $event['workorder_no']; ?> (<?php echo $event['lead_address']; ?>)
						</span>
					</span>
					<span class="pull-right m-right-20 p-top-5 hidden-xs hidden-sm">
						<span class="h5">
							<span class="glyphicon glyphicon-time text-warning"></span>&nbsp;
								<strong>Work(start/finish):</strong>&nbsp;
								<?php if(isset($started_events['ev_id'])): ?>
								<span class="label label-success"><?php echo date('h:m A', strtotime($started_events['ev_start_work'])); ?></span>
								<?php else: ?>--:--<?php endif; ?>
								&nbsp;/&nbsp;<span class="label label-success">Now</span>
						</span>
					</span>
						

					<div class="clearfix"></div>
				</div>
				<div class="modal-body">
					
					<div class="row">
						<?php $this->load->view('events/_partials/event_report'); ?>
						<?php //$this->load->view('events/_partials/event_report'); ?>
					</div>
					<br class="hidden-xs"><br class="hidden-xs"><br class="hidden-xs">
					<div class="row">
						<div class="col-md-6">
							<div class="form-group m-b-none">
								<textarea class="form-control eventText" rows="7" placeholder="Note" name="event_description" ></textarea>
								<div class="clear"></div>
							</div>
							<br class="visible-xs"><br class="visible-xs">

							<br class="hidden-xs"><br class="hidden-xs"><br class="hidden-xs"><br class="hidden-xs"><br class="hidden-xs"><br class="hidden-xs">
							<div class="row">
								<div class="col-md-6 col-xs-5 h5"><strong>Date:</strong></div>
								<div class="col-md-6 col-xs-7 h5"><?php echo date('d M Y h:i A'); ?></div>
							</div>
							<div class="row">
								<div class="col-md-6 col-xs-5 h5"><strong>COMPLETED BY:</strong></div>
								<div class="col-md-6 col-xs-7 h5"><?php echo $client_data->client_name; ?></div>
							</div>
							<br class="visible-xs">
						</div>
						<div class="col-md-6">
							
							<div class="form-group m-b-none">
								<input type="hidden" name="client_signature_image" id="client-signature-image" value="">
								<div id="client-signature-pad" class="signature-pad">
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
					<button type="button" id="confirm-stop-work" class="btn btn-success btn-lg">Confirm Report</button>
				</div>
			</form>
		</div>
	</div>
</div>