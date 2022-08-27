<?php $this->load->view('includes/header'); ?>

<?php
if($estimate_data->lead_state){
	$estimate_data->lead_state = $estimate_data->lead_state.'+';
}
else{
	$estimate_data->lead_state = '';
}
?>
<?php $tags = str_replace(array(' ', '#'), array('+', ''), $estimate_data->lead_address); ?>


<?php $client_address = $tags.'+'.$estimate_data->lead_city.'+'.$estimate_data->lead_state.$client_data->client_country.'+'.str_replace(array(' ', '#'), array('+', ''), $estimate_data->lead_zip); ?>


<?php 
//echo $tags;
/*if($_SERVER['REMOTE_ADDR']=='127.0.0.1'){
	$client_address = 'Чернигов, Черниговская область, 14000';
}*/ ?>


<script type="text/javascript">
	window.map_origin = JSON.parse('<?php echo (isset($origin))?json_encode(explode(",", $origin)):json_encode([]); ?>');
	window.map_destination = JSON.parse('<?php echo (isset($destination))?json_encode(explode(",", $destination)):json_encode([]); ?>');
	window.client_address = "<?php echo addslashes($client_address); ?>";
	window.event_id = <?php echo $event['id']; ?>;
</script>

<style type="text/css">
	.line-lg{margin-bottom: 0;}
</style>

<section class="vbox">

	<section class="scrollable p-sides-15">
		<ul class="breadcrumb no-border no-radius b-b b-light pull-in">
			<li><a href="<?php echo site_url(); ?>"><i class="fa fa-home"></i> Home</a></li>
			<li class="active">Event</li>
		</ul>

		<section class="media">
			<div class="row bg-white hidden-lg">
				<div class="col-md-12 col-sm-12 col-xs-12">
					<section class=" panel panel-default p-n">
						<header class="panel-heading"><h4 class="text-success">Client information</h4></header>
						<div class="panel-body">
							<div class="col-md-5 col-sm-5 col-xs-12 m-bottom-10 ">			
								<div class="row" style="position: relative">
									<div class="row">
										<div class="col-md-3 col-sm-3 col-lg-3 col-xs-3">
											<strong>Date:</strong>
										</div>
										<div class="col-md-7 col-sm-7 col-xs-8"><?php echo date('Y-m-d'); ?></div>
									</div>
									<div class="row">
										<div class="col-md-3 col-sm-3 col-lg-3 col-xs-3">
											<strong>Client:</strong>
										</div>
										<div class="col-md-7 col-sm-7 col-xs-8"><?php echo $client_data->client_name; ?></div>
									</div>
									<div class="row">
										<div class="col-md-3 col-sm-3 col-lg-3 col-xs-3">
											<strong>Client Address:</strong>
										</div>
										<div class="col-md-7 col-sm-7 col-xs-8">
											<?php echo $estimate_data->lead_address . ", " . $estimate_data->lead_city; ?>,&nbsp;
											<?php echo str_replace(array('+', '#'), array(' ', ' '), $estimate_data->lead_state) . $client_data->client_country; ?>,&nbsp;
											<?php echo isset($client_data->client_zip) ? $client_data->client_zip : $estimate_data->lead_zip; ?>
										</div>
									</div>
									<div class="row">
										<div class="col-md-3 col-sm-3 col-lg-3 col-xs-3">
											<strong>Client Phone:</strong>
										</div>
										<div class="col-md-7 col-sm-7 col-xs-8"><i class="fa  fa-phone"></i>&nbsp;<a href="tel:<?php echo numberTo($client_contact['cc_phone']); ?>"><?php echo numberTo($client_contact['cc_phone']); ?></a>
										</div>
									</div>
									<div class="row">
										<?php if ($client_data->client_contact != $client_contact['cc_name']) : ?>
											<div class="col-md-3 col-sm-3 col-lg-3 col-xs-3">
												<strong>Contact Persone:</strong>
											</div>
											<div class="col-md-7 col-sm-7 col-xs-8">
												<?php echo $client_contact['cc_name']; ?>
											</div>
										<?php endif; ?>
									</div>
									<a class="brn btn-success btn-rounded call-button hidden-lg" href="tel:<?php echo numberTo($client_contact['cc_phone']); ?>"><i class="fa fa-phone"></i></a>
								</div>

								<div class="row hidden-xs" style="position: relative">
									<br class="hidden-sm"><br><br><br>
									<div class="col-sm-12 col-md-12 col-lg-12 col-xs-12 m-top-10 p-left-0">
										<?php $this->load->view('events/_partials/dropzone'); ?>
									</div>
								</div>
							</div>
							<div class="col-sm-7 col-md-7 col-xs-12 p-n">
								<section class="panel panel-default m-n">
									<div class="panel-body p-n">
										<div class="clearfix text-center" style="height: 300px;">
											<div id="map_canvas_mobile" style="float:none;width:100%;height:100%;"></div>	
										</div>
										<footer class="panel-footer bg-info text-center">
											<?php $this->load->view('events/_partials/buttons'); ?>
										</footer>
									</div>
								</section>
							</div>
						</div>
					</section>
				</div>
			</div>
			<div class="row bg-white p-15">
					<div class="col-sm-12 col-md-12 col-lg-8">
						<section class=" panel panel-default p-n hidden-xs hidden-sm hidden-md">
							<header class="panel-heading"><h4 class="text-success">Client information</h4></header>
							<div class="panel-body">
								<div class="row" style="position: relative">
									<div class="col-md-8 col-sm-8 col-xs-12">
										<div class="row">
											<div class="col-md-3 col-sm-3 col-lg-3 col-xs-3">
												<strong>Date:</strong>
											</div>
											<div class="col-md-7 col-sm-7 col-xs-8"><?php echo date('Y-m-d'); ?></div>
										</div>
										<div class="row">
											<div class="col-md-3 col-sm-3 col-lg-3 col-xs-3">
												<strong>Client:</strong>
											</div>
											<div class="col-md-7 col-sm-7 col-xs-8"><?php echo $client_data->client_name; ?></div>
										</div>
										<div class="row">
											<div class="col-md-3 col-sm-3 col-lg-3 col-xs-3">
												<strong>Client Address:</strong>
											</div>
											<div class="col-md-7 col-sm-7 col-xs-8">
												<?php echo $estimate_data->lead_address . ", " . $estimate_data->lead_city; ?>,&nbsp;
												<?php echo str_replace(array('+', '#'), array(' ', ' '), $estimate_data->lead_state) . $client_data->client_country; ?>,&nbsp;
												<?php echo isset($client_data->client_zip) ? $client_data->client_zip : $estimate_data->lead_zip; ?>
											</div>
										</div>
										<div class="row">
											<div class="col-md-3 col-sm-3 col-lg-3 col-xs-3">
												<strong>Client Phone:</strong>
											</div>
											<div class="col-md-7 col-sm-7 col-xs-8"><i class="fa  fa-phone"></i>&nbsp;<a href="tel:<?php echo numberTo($client_contact['cc_phone']); ?>"><?php echo numberTo($client_contact['cc_phone']); ?></a>
											</div>
										</div>
										<div class="row">
											<?php if ($client_data->client_contact != $client_contact['cc_name']) : ?>
												<div class="col-md-3 col-sm-3 col-lg-3 col-xs-3">
													<strong>Contact Persone:</strong>
												</div>
												<div class="col-md-7 col-sm-7 col-xs-8">
													<?php echo $client_contact['cc_name']; ?>
												</div>
											<?php endif; ?>
										</div>
									</div>
									<div class="col-md-4 col-sm-4 col-xs-12 text-right">
										<?php $this->load->view('events/_partials/pdf_buttons'); ?>
									</div>
									<a class="brn btn-success btn-rounded call-button hidden-lg" href="tel:<?php echo numberTo($client_contact['cc_phone']); ?>"><i class="fa fa-phone"></i></a>
									
								</div>
							</div>
						</section>

						<section class=" panel panel-default p-n">
							<header class="panel-heading"><h4 class="text-success">Estimate for <strong><?php echo nl2br($estimator_data->firstname . ' ' . $estimator_data->lastname); ?></strong></h4></header>
							<div class="panel-body">
								<?php foreach ($event_services as $key => $service_data): ?>
								<div class="row">
									<div class="col-lg-4 col-sm-4"><h4><strong>Description:</strong></h4></div>
									<?php if($service_data['service_time'] || $service_data['service_travel_time'] || $service_data['service_disposal_time']) : ?>
									<div class="col-lg-8 col-sm-8 text-success hidden-xs">
										<h4 class="text-right">
											<strong>
												Estimated Time (with travel): <?php echo round(($service_data['service_time'] + $service_data['service_disposal_time'] + $service_data['service_travel_time']) * count($members), 2); ?>mhr.&nbsp;
												<i class="fa fa-clock-o"></i>&nbsp;<?php echo date("H:i", $event['event_start']); ?>-<?php echo date("H:i", $event['event_end']); ?>
											</strong>
										</h4>
									</div>
									<div class="col-lg-8 text-success visible-xs">
										<h5>
											<strong>
												Estimated Time (with travel): <?php echo round(($service_data['service_time'] + $service_data['service_disposal_time'] + $service_data['service_travel_time']) * count($members), 2); ?>mhr.&nbsp;
												<i class="fa fa-clock-o"></i>&nbsp;<?php echo date("H:i", $event['event_start']); ?>-<?php echo date("H:i", $event['event_end']); ?>
											</strong>
										</h5>
									</div>
									<?php endif; ?>
									<div class="col-lg-12"><?php echo nl2br($service_data['service_description']); ?></div>

								</div>
                                    <?php if(isset($service_data['bundle_records']) && !empty($service_data['bundle_records'])) :
                                        foreach ($service_data['bundle_records'] as $record): ?>
                                            <div class="row">
                                                <div class="col-lg-11 p-left-20 m-left-20" style="border-left: 1px solid #bebebe;">
                                                    <div style=""><strong ><?php echo $record['service_name']; ?></strong></div>
                                                    <div style=""> <?php echo $record['service_description']; ?></div>

                                                </div>
                                            </div>
                                        <?php endforeach; endif; ?>
                                <?php if(isset($service_data['is_bundle']) && !$service_data['is_bundle']): ?>
								<div class="row">
									<br><br>
									<div class="col-lg-3 col-sm-3">
										<strong>Team Requirements:</strong>
										<?php
										foreach($estimate_services[$service_data['service_id']]->crew as $jkey=>$crew) : ?>
											<div>
												<i class="fa fa-check"></i>
												<?php echo $crew->crew_name; ?><br>
											</div>
										<?php endforeach; ?>
										<br class="visible-xs">
									</div>
									<div class="col-lg-3 col-sm-3">
										<strong>Equip. Requirements:</strong>
										<?php
										foreach($estimate_services[$service_data['service_id']]->equipments as $jkey=>$equipment) : ?>
											<div>
												<?php if($equipment->vehicle_name) : ?>
												<i class="fa fa-check"></i>
													<?php echo $equipment->vehicle_name;?>
													<?php if($equipment->equipment_item_option && !empty(json_decode($equipment->equipment_item_option))) : ?>
														<?php $opts = json_decode($equipment->equipment_item_option); ?>
														<?php foreach($opts as $j=>$opt) : ?>
															<?php if(!$j) : ?>(<?php endif?><?php echo trim($opt); ?><?php if(isset($opts[$j +1 ])) : ?> or <?php else : ?>)<?php endif; ?>
														<?php endforeach; ?>
													<?php else : ?>
														(Any)
													<?php endif; ?>
												<?php endif; ?>
												<?php if($equipment->trailer_name) : ?>
												<?php if(!isset($equipment->vehicle_name) || !$equipment->vehicle_name) : ?>
													<i class="fa fa-check"></i>
														<?php echo $equipment->trailer_name; ?>
												<?php else : ?>
													<?php echo ', ' . $equipment->trailer_name ?>
												<?php endif; ?>
													<?php if($equipment->equipment_attach_option && !empty(json_decode($equipment->equipment_attach_option))) : ?>
														<?php $opts = json_decode($equipment->equipment_attach_option); ?>
														<?php foreach($opts as $j=>$opt) : ?>
															<?php if(!$j) : ?>(<?php endif?><?php echo trim($opt); ?><?php if(isset($opts[$j +1 ])) : ?> or <?php else : ?>)<?php endif; ?>
														<?php endforeach; ?>
													<?php else : ?>
													(Any)
													<?php endif;?>
												<?php endif;?>
												<?php if($equipment->equipment_attach_tool) : ?>
													<?php $eqOpt = json_decode($equipment->equipment_attach_tool); ?>
													<?php foreach($eqOpt as $k=>$v) : ?>
														<?php $opts = json_decode($equipment->equipment_tools_option);?>
														<?php foreach($tools as $tool) : ?>
														<?php if($tool->vehicle_id == $v) : ?>

															<?php if(isset($opts->$v) && !empty($opts->$v)) : ?>

																<?php foreach($opts->$v as $j=>$opt) : ?>
																	<?php echo ', ' . trim($opt); ?>

																<?php endforeach; ?>
															<?php else : ?>
															(Any)
															<?php endif;?>
														<?php endif; ?>
														<?php endforeach; ?>
													<?php endforeach; ?>
												<?php endif;?>
											</div>
										<?php endforeach; ?>
										<br class="visible-xs">
									</div>
									<div class="col-lg-3 col-sm-3">
										<label>
											<strong>Project Requirements:</strong>
										</label>
										<?php if($estimate_services[$service_data['service_id']]->service_disposal_brush) : ?>
											<div>
												<i class="fa fa-check"></i>
												Disposal Brush
											</div>
										<?php endif; ?>
										<?php if($estimate_services[$service_data['service_id']]->service_disposal_wood) : ?>
											<div>
												<i class="fa fa-check"></i>
												Disposal Wood
											</div>
										<?php endif; ?>
										<?php if($estimate_services[$service_data['service_id']]->service_cleanup) : ?>
											<div>
												<i class="fa fa-check"></i>
												Clean Up
											</div>
										<?php endif; ?>
										<?php if($estimate_services[$service_data['service_id']]->service_exemption) : ?>
											<div>
												<i class="fa fa-check"></i>
												Exemption
											</div>
										<?php endif; ?>
										<?php if($estimate_services[$service_data['service_id']]->service_permit) : ?>
											<div>
												<i class="fa fa-check"></i>
												Permit Required
											</div>
										<?php endif; ?>
										<?php if($estimate_services[$service_data['service_id']]->service_client_home) : ?>
											<div>
												<i class="fa fa-check"></i>
												Client Home
											</div>
										<?php endif; ?>
										<br class="visible-xs">
									</div>
									<div class="col-lg-3 col-sm-3">

										<strong>Times:</strong>

										<div>
											Service Time :
											<?php echo isset($service_data['service_time']) ? $service_data['service_time'] : 0; ?> hrs.
										</div>
										<div>
											Travel Time:
											<?php echo isset($service_data['service_travel_time']) ? $service_data['service_travel_time'] : 0; ?> hrs.
										</div>
										<div>
											Disposal Time:
											<?php echo isset($service_data['service_disposal_time']) ? $service_data['service_disposal_time'] : 0; ?> hrs.
										</div>

									</div>
								</div>
								<?php endif; if(isset($event_services[$key+1])): ?>
								<hr>
								<?php endif; ?>
								<?php endforeach; ?>
							</div>
						</section>

						<h4>Photos</h4>
						<div class="row">
							<div class="col-sm-12 col-md-6 col-lg-12  visible-xs">
								<?php $this->load->view('events/_partials/dropzone'); ?>
							</div>
							<br>
						</div>

						<section class="hbox stretch">
							<section class="vbox bg-white" id="gallery-container">
								<?php $this->load->view('events/_partials/gallery'); ?>
							</section>
				        </section>

					</div>
					<div class="col-sm-12 col-md-12 col-lg-4 p-bottom-20">
						<div class="row">
							<div class="col-sm-6 col-md-6 col-lg-12 hidden-xs hidden-sm hidden-md">
								<section class="panel panel-default">
									<div class="panel-body">
										<div class="clearfix text-center" style="height: 300px;">
											<div id="map_canvas" style="float:none;width:100%;height:100%;"></div>	
										</div>
										<footer class="panel-footer bg-info text-center">
											<?php $this->load->view('events/_partials/buttons'); ?>
										</footer>
									</div>
								</section>
							</div>

							<div class="col-sm-12 col-md-6 col-lg-12  hidden-sm hidden-md hidden-xs">
								<?php $this->load->view('events/_partials/dropzone'); ?>
							</div>
							
						</div>
					</div>
					<?php $this->load->view('events/_partials/report_form_modal'); ?>
					<?php $this->load->view('events/_partials/safety_meeting_form_modal'); ?>
				
			</div>
		</section>
	</section>
</section>

<link rel="stylesheet" href="<?php echo base_url('assets/js/libs/signature_pad/css/ie9.css'); ?>">
<link rel="stylesheet" href="<?php echo base_url('assets/js/libs/signature_pad/css/signature-pad.css'); ?>">

<link rel="stylesheet" href="<?php echo base_url('assets/css/modules/events/events.css'); ?>">

<script src="<?php echo base_url('assets/js/libs/signature_pad/js/signature_pad.js'); ?>"></script>
<script src="<?php echo base_url('assets/js/libs/signature_pad/js/app.js'); ?>"></script>

<link rel="stylesheet" href="<?php echo base_url('assets/js/libs/dropzone/min/dropzone.min.css'); ?>">
<script src="<?php echo base_url('assets/js/libs/dropzone/min/dropzone.min.js'); ?>"></script>

<script src="<?php echo base_url('assets/js/libs/unitegallery/js/unitegallery.min.js'); ?>"></script>
<link rel="stylesheet" href="<?php echo base_url('assets/js/libs/unitegallery/css/unite-gallery.css'); ?>">
<script src="<?php echo base_url('assets/js/libs/unitegallery/themes/tiles/ug-theme-tiles.js'); ?>"></script>

<script src="<?php echo base_url('assets/js/modules/events/events.js?v=2'); ?>"></script>
<script type="text/javascript">
	jQuery(document).ready(function(){
		jQuery("#gallery").unitegallery();
	});
	
	Dropzone.autoDiscover = false;
    jQuery(document).ready(function() {

		var myDropzone = $(".dropzone").dropzone({
			url: "/events/file_upload/<?php echo $event['estimate_id']; ?>",
			acceptedFiles: 'image/*',
			addRemoveLinks: true,
            timeout: 300000,
			"success":function(file, response){
				var html = JSON.parse(response);
				$("#gallery-container").html(html['html']);
				
				jQuery("#gallery").unitegallery();
				
				this.removeAllFiles();
			}
		});
		
    });
     
	$('.status').change(function(){
				if($(this).val() == 'finished')
				{
					$(this).parents('.event:first').find('.payment').slideDown();
					$(this).parents('.event:first').find('.timeToFinish').slideUp();
					$(this).parents('.event:first').find('.finishDescription').slideUp();
					$(this).parents('.event:first').find('.timeToFinish').find('.unfinished').val('').attr('disabled', 'disabled');
					$(this).parents('.event:first').find('.finishDescription').find('.unfinished').val('').val('').attr('disabled', 'disabled');
				}
				else
				{
					$(this).parents('.event:first').find('.payment').slideUp();
					$(this).parents('.event:first').find('.paymentSum').slideUp();
					$(this).parents('.event:first').find('.paymentSum').find('.finished').val('').attr('disabled', 'disabled');
					$(this).parents('.event:first').find('.payment').find('.payment').prop('checked', false);
					$(this).parents('.event:first').find('.timeToFinish').find('.unfinished').removeAttr('disabled');
					$(this).parents('.event:first').find('.timeToFinish').slideDown();
					$(this).parents('.event:first').find('.finishDescription').find('.unfinished').removeAttr('disabled');
					$(this).parents('.event:first').find('.finishDescription').slideDown();
				}
				return false;
			});

	$('.payment').change(function(){
				if($(this).val() == 'yes')
				{
					$(this).parents('.event:first').find('.paymentSum').slideDown();
					$(this).parents('.event:first').find('.paymentSum').find('.finished').removeAttr('disabled');
				}
				else
				{
					$(this).parents('.event:first').find('.paymentSum').slideUp();
					$(this).parents('.event:first').find('.paymentSum').find('.finished').val('').attr('disabled', 'disabled');
				}
				return false;
			});
			$('.damage').change(function(){
				if($(this).val() == 'yes')
				{
					$(this).parents('.event:first').find('.dmgDescription').slideDown();
					$(this).parents('.event:first').find('.dmgDescription').find('.dmgText').removeAttr('disabled');
				}
				else
				{
					$(this).parents('.event:first').find('.dmgDescription').slideUp();
					$(this).parents('.event:first').find('.dmgDescription').find('.dmgText').val('').attr('disabled', 'disabled');
				}
			});
			$('.fail').change(function(){
				
				if($(this).val() == 'yes')
				{
					$('.failDesc').slideDown();
					$('.failDesc').find('.failText').removeAttr('disabled');
				}
				else
				{
					$('.failDesc').slideUp();
					$('.failDesc').find('.failText').val('').attr('disabled', 'disabled');
				}
			});
			$('.expenses').change(function(){
				
				if($(this).val() == 'yes')
				{
					$('.expensesDesc').slideDown();
					$('.expensesDesc').find('.expensesText').removeAttr('disabled');
				}
				else
				{
					$('.expensesDesc').slideUp();
					$('.expensesDesc').find('.expensesText').val('').attr('disabled', 'disabled');
				}
			});
</script>
<?php $this->load->view('includes/footer'); ?>
