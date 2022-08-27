<?php $this->load->view('includes/header'); ?>

<style type="text/css">
	.btn-start-stop{
		position: fixed;
	    right: 15px;
	    bottom: 20px;
	    z-index: 1;
	    font-size: 19px;
	    padding: 28px 10px;
	    border-radius: 64px;

	    
	}
	.btn-success.btn-start-stop{
		animation: pulse-success 2s infinite;
	}
	.btn-warning.btn-start-stop{
		animation: pulse-warning 2s infinite;
	}
    @-webkit-keyframes pulse-success {
        0% {
        -webkit-box-shadow: 0 0 0 0 rgba(109, 206, 59, 0.86);
        }
        70% {
          -webkit-box-shadow: 0 0 0 10px rgba(109, 206, 59, 0);
        }
        100% {
          -webkit-box-shadow: 0 0 0 0 rgba(109, 206, 59, 0);
        }
    }
    @keyframes  pulse-success {
        0% {
        -moz-box-shadow: 0 0 0 0 rgba(109, 206, 59, 0.86);
        box-shadow: 0 0 0 0 rgba(109, 206, 59, 0.86);
        }
        70% {
          -moz-box-shadow: 0 0 0 10px rgba(109, 206, 59, 0);
          box-shadow: 0 0 0 10px rgba(109, 206, 59, 0);
        }
        100% {
          -moz-box-shadow: 0 0 0 0 rgba(109, 206, 59, 0);
          box-shadow: 0 0 0 0 rgba(109, 206, 59, 0);
        }
    }
    @-webkit-keyframes pulse-warning {
        0% {
        -webkit-box-shadow: 0 0 0 0 rgba(251, 200, 15, 0.86);
        }
        70% {
          -webkit-box-shadow: 0 0 0 10px rgba(251, 200, 15, 0);
        }
        100% {
          -webkit-box-shadow: 0 0 0 0 rgba(251, 200, 15, 0);
        }
    }
    @keyframes  pulse-warning {
        0% {
        -moz-box-shadow: 0 0 0 0 rgba(251, 200, 15, 0.86);
        box-shadow: 0 0 0 0 rgba(251, 200, 15, 0.86);
        }
        70% {
          -moz-box-shadow: 0 0 0 10px rgba(251, 200, 15, 0);
          box-shadow: 0 0 0 10px rgba(251, 200, 15, 0);
        }
        100% {
          -moz-box-shadow: 0 0 0 0 rgba(251, 200, 15, 0);
          box-shadow: 0 0 0 0 rgba(251, 200, 15, 0);
        }
    }
</style>
<script src="https://cdn.jsdelivr.net/npm/signature_pad@2.3.2/dist/signature_pad.min.js"></script>

<section class="vbox">
	<section class="scrollable p-sides-15">
		<ul class="breadcrumb no-border no-radius b-b b-light pull-in">
			<li><a href="http://td.onlineoffice.loc/"><i class="fa fa-home"></i> Home</a></li>
			<li><a href="http://td.onlineoffice.loc/estimates">Work</a></li>
			<li class="active">Event</li>
		</ul>
		
		<div class="m-b-md">
            <h3 class="m-b-none">TAILGATE SAFETY METTING FORM | PRE JOB SAFETY ASSESSMENT</h3>
        </div>
        
        <button class="btn btn-success btn-start-stop" href="#" type="button" data-toggle="modal" data-target="#start-work-modal">Start&nbsp;<i class="fa fa-play"></i></button>
		<?php /*
		<button class="btn btn-warning btn-start-stop">Stop&nbsp;<i class="fa fa-stop"></i></button>
		*/ ?>
		<section class="media">
			<div class="row" style="display: flex;flex-wrap: wrap;">
				<form class="form-horizontal" method="get">
					<div class="col-sm-12 col-md-12 col-lg-8">
						<section class=" panel panel-default p-n  ">
							<header class="panel-heading"><h4 class="text-success">Client information</h4></header>
							<div class="panel-body">
								<div class="row">
									<div class="col-md-6 col-sm-7 col-xs-6">
										<div class="row">
											<div class="col-md-5 col-sm-5 col-lg-4 col-xs-4">
												<strong>Date:</strong>
											</div>
											<div class="col-md-7 col-sm-7 col-xs-8"><?php echo date('Y-m-d'); ?></div>
										</div>
										<div class="row">
											<div class="col-md-5 col-sm-5 col-lg-4 col-xs-4">
												<strong>Client:</strong>
											</div>
											<div class="col-md-7 col-sm-7 col-xs-8"><?php echo $client_data->client_name; ?></div>
										</div>
										<div class="row">
											<div class="col-md-5 col-sm-5 col-lg-4 col-xs-4">
												<strong>Client Address:</strong>
											</div>
											<div class="col-md-7 col-sm-7 col-xs-8">
												<?php echo $estimate_data->lead_address . ", " . $estimate_data->lead_city; ?><br>
												<?php echo $estimate_data->lead_state . ", " . $client_data->client_country; ?>
												<br>
												<?php echo isset($client_data->client_zip) ? $client_data->client_zip : $estimate_data->lead_zip; ?>
											</div>
										</div>
										<div class="row">
											<div class="col-md-5 col-sm-5 col-lg-4 col-xs-4">
												<strong>Client Phone:</strong>
											</div>
											<div class="col-md-7 col-sm-7 col-xs-8"><?php echo numberTo($client_contact['cc_phone']); ?></div>
										</div>
										<div class="row">
											<?php if ($client_data->client_contact != $client_contact['cc_name']) : ?>
												<div class="col-md-5 col-sm-5 col-lg-4 col-xs-4">
													<strong>Contact Persone:</strong>
												</div>
												<div class="col-md-7 col-sm-7 col-xs-8">
													<?php echo $client_contact['cc_name']; ?>
												</div>
											<?php endif; ?>
										</div>
									</div>
									<div class="col-md-6 col-sm-5 col-xs-6 text-right">
										<?php $tags = str_replace(array(' ', '#'), array('+', ''), $estimate_data->lead_address);?>
										<img src="http://maps.googleapis.com/maps/api/staticmap?key=<?php echo $this->config->item('gmaps_key'); ?>&center=<?php echo $tags; ?>+<?php echo $estimate_data->lead_city; ?>+<?php echo $estimate_data->lead_state; ?>+<?php echo $client_data->client_country; ?>&zoom=11&size=380x200&markers=<?php echo $tags; ?>+<?php echo $estimate_data->lead_city; ?>+<?php echo $estimate_data->lead_state; ?>+<?php echo $client_data->client_country; ?>" alt="Google Map" width="100%"> 
									</div>
								</div>
							</div>
						</section>

						<section class=" panel panel-default p-n">
							<header class="panel-heading">
									<h4 class="text-success pull-left">HAZARDS</h4>
									<h4 class="pull-right">Workorder No. <span class="label label-warning"><?php echo $event['workorder_no']; ?></span></h4>
									<div class="clear"></div>
							</header>
							<div class="panel-body">
								
								<div class="row">
									<div class="col-md-6 col-sm-6 col-lg-4">
										<p><strong>Access/Work Location Hazards</strong></p>
										<div class="form-group m-b-none row">
					                      	<label class="col-sm-8 col-xs-9 control-label">Partially obstructed</label>
					                      	<div class="col-sm-4">
						                        <label class="switch">
						                          <input type="checkbox">
						                          <span></span>
						                        </label>
					                      	</div>
					                    </div>
					                    <div class="form-group m-b-none row">
					                      	<label class="col-sm-8 col-xs-9 control-label">Slip / Trip potential</label>
					                      	<div class="col-sm-4">
						                        <label class="switch">
						                          <input type="checkbox">
						                          <span></span>
						                        </label>
					                      	</div>
					                    </div>
					                    <div class="form-group m-b-none row">
					                      	<label class="col-sm-8 col-xs-9 control-label">Below Grade / Above Grade (circle one)</label>
					                      	<div class="col-sm-4">
						                        <label class="switch">
						                          <input type="checkbox">
						                          <span></span>
						                        </label>
					                      	</div>
					                    </div>
					                    <div class="form-group m-b-none row">
					                      	<label class="col-sm-8 col-xs-9 control-label">Near Vehicle / Equipment</label>
					                      	<div class="col-sm-4">
						                        <label class="switch">
						                          <input type="checkbox">
						                          <span></span>
						                        </label>
					                      	</div>
					                    </div>
					                    <div class="form-group m-b-none row">
					                      	<label class="col-sm-8 col-xs-9 control-label">Other persons above or below work area</label>
					                      	<div class="col-sm-4">
						                        <label class="switch">
						                          <input type="checkbox">
						                          <span></span>
						                        </label>
					                      	</div>
					                    </div>
					                </div>
					               	<div class="col-md-6 col-sm-6 col-lg-4">
					               		<p><strong>Activity / Equipment / Tool Hazards</strong></p>
					               		<div class="form-group m-b-none row">
					                      	<label class="col-sm-8 col-xs-9 control-label">Burn / Heat sources</label>
					                      	<div class="col-sm-4">
						                        <label class="switch">
						                          <input type="checkbox">
						                          <span></span>
						                        </label>
					                      	</div>
					                    </div>
					                    <div class="form-group m-b-none row">
					                      	<label class="col-sm-8 col-xs-9 control-label">Equipment / tools</label>
					                      	<div class="col-sm-4">
						                        <label class="switch">
						                          <input type="checkbox">
						                          <span></span>
						                        </label>
					                      	</div>
					                    </div>
					                    <div class="form-group m-b-none row">
					                      	<label class="col-sm-8 col-xs-9 control-label">Airborne particles / chipping / grinding</label>
					                      	<div class="col-sm-4">
						                        <label class="switch">
						                          <input type="checkbox">
						                          <span></span>
						                        </label>
					                      	</div>
					                    </div>
					                    <div class="form-group m-b-none row">
					                      	<label class="col-sm-8 col-xs-9 control-label">Activity creates vibration / noise / light hazard</label>
					                      	<div class="col-sm-4">
						                        <label class="switch">
						                          <input type="checkbox">
						                          <span></span>
						                        </label>
					                      	</div>
					                    </div>
					                    <div class="form-group m-b-none row">
					                      	<label class="col-sm-8 col-xs-9 control-label">Tools / Equipment not serviceable</label>
					                      	<div class="col-sm-4">
						                        <label class="switch">
						                          <input type="checkbox">
						                          <span></span>
						                        </label>
					                      	</div>
					                    </div>
					                    <div class="form-group m-b-none row">
					                      	<label class="col-sm-8 col-xs-9 control-label">Tools / Equipment not appropriate</label>
					                      	<div class="col-sm-4">
						                        <label class="switch">
						                          <input type="checkbox">
						                          <span></span>
						                        </label>
					                      	</div>
					                    </div>
					               	</div>
					               	<div class="col-md-6 col-sm-6 col-lg-4">
					               		<p><strong>Work Environment Hazards</strong></p>

					               		<div class="form-group m-b-none row">
					                      	<label class="col-sm-8 col-xs-9 control-label">Spill potential</label>
					                      	<div class="col-sm-4">
						                        <label class="switch">
						                          <input type="checkbox">
						                          <span></span>
						                        </label>
					                      	</div>
					                    </div>
					                    <div class="form-group m-b-none row">
					                      	<label class="col-sm-8 col-xs-9 control-label">Weather Conditions</label>
					                      	<div class="col-sm-4">
						                        <label class="switch">
						                          <input type="checkbox">
						                          <span></span>
						                        </label>
					                      	</div>
					                    </div>
					                    <div class="form-group m-b-none row">
					                      	<label class="col-sm-8 col-xs-9 control-label">Heat Stress / Cold exposure</label>
					                      	<div class="col-sm-4">
						                        <label class="switch">
						                          <input type="checkbox">
						                          <span></span>
						                        </label>
					                      	</div>
					                    </div>
					                    <div class="form-group m-b-none row">
					                      	<label class="col-sm-8 col-xs-9 control-label">Other workers in area</label>
					                      	<div class="col-sm-4">
						                        <label class="switch">
						                          <input type="checkbox">
						                          <span></span>
						                        </label>
					                      	</div>
					                    </div>
					                    <div class="form-group m-b-none row">
					                      	<label class="col-sm-8 col-xs-9 control-label">Housekeeping</label>
					                      	<div class="col-sm-4">
						                        <label class="switch">
						                          <input type="checkbox">
						                          <span></span>
						                        </label>
					                      	</div>
					                    </div>
					                    <div class="form-group m-b-none row">
					                      	<label class="col-sm-8 col-xs-9 control-label">Loose materials</label>
					                      	<div class="col-sm-4">
						                        <label class="switch">
						                          <input type="checkbox">
						                          <span></span>
						                        </label>
					                      	</div>
					                    </div>
				               		</div>
				               	</div>
				               	<hr>
				               	<div class="row" style="display: flex; flex-wrap: wrap;">
				               		<div class="col-md-6 col-sm-6 col-xs-6 col-lg-4 p-bottom-40">
				               			<p><strong>Health Hazards</strong></p>
					                    <div class="form-group m-b-none row">
					                      	<label class="col-sm-8 col-xs-8 control-label">Health Hazards</label>
					                      	<div class="col-sm-4 col-xs-4">
						                        <label class="switch">
						                          <input type="checkbox">
						                          <span></span>
						                        </label>
					                      	</div>
					                    </div>
					                    <div class="form-group m-b-none row">
					                      	<label class="col-sm-8 col-xs-8 control-label">Dust</label>
					                      	<div class="col-sm-4 col-xs-4">
						                        <label class="switch">
						                          <input type="checkbox">
						                          <span></span>
						                        </label>
					                      	</div>
					                    </div>
					                    <div class="form-group m-b-none row">
					                      	<label class="col-sm-8 col-xs-8 control-label">Chemical</label>
					                      	<div class="col-sm-4 col-xs-4">
						                        <label class="switch">
						                          <input type="checkbox">
						                          <span></span>
						                        </label>
					                      	</div>
					                    </div>
					                    <div class="form-group m-b-none row" style="position: absolute;bottom: 0; left: 15px; right: 15px;">
					                      	<div class="col-sm-12">
						                        <input type="text" placeholder="Other" class="form-control" name="">
					                      	</div>
					                    </div>
				               		</div>
				               		<div class="col-md-6 col-sm-6 col-xs-6 col-lg-4 p-bottom-40">
				               			<p><strong>Personal Limitations / Hazards</strong></p>
				               			<div class="form-group m-b-none row">
					                      	<label class="col-sm-8 col-xs-8 control-label">Distractions in work area</label>
					                      	<div class="col-sm-4 col-xs-4">
						                        <label class="switch">
						                          <input type="checkbox">
						                          <span></span>
						                        </label>
					                      	</div>
					                    </div>
					                    <div class="form-group m-b-none row">
					                      	<label class="col-sm-8 col-xs-8 control-label">Working alone (communication)</label>
					                      	<div class="col-sm-4 col-xs-4">
						                        <label class="switch">
						                          <input type="checkbox">
						                          <span></span>
						                        </label>
					                      	</div>
					                    </div>
					                    <div class="form-group m-b-none row">
					                      	<label class="col-sm-8 col-xs-8 control-label">Lift too heavy / awkward position</label>
					                      	<div class="col-sm-4 col-xs-4">
						                        <label class="switch">
						                          <input type="checkbox">
						                          <span></span>
						                        </label>
					                      	</div>
					                    </div>
					                    <div class="form-group m-b-none row">
					                      	<label class="col-sm-8 col-xs-8 control-label">External noise levels</label>
					                      	<div class="col-sm-4 col-xs-4">
						                        <label class="switch">
						                          <input type="checkbox">
						                          <span></span>
						                        </label>
					                      	</div>
					                    </div>
					                    <div class="form-group m-b-none row" style="position: absolute;bottom: 0; left: 15px; right: 15px;">
					                      	<div class="col-sm-12">
						                        <input type="text" placeholder="Other" class="form-control" name="">
					                      	</div>
					                    </div>
				               		</div>
				               		<div class="col-md-6 col-sm-6 col-xs-6 col-lg-4 p-bottom-40">
				               			<p><strong>Ergonomic Hazards</strong></p>
				               			<div class="form-group m-b-none row">
					                      	<label class="col-sm-8 col-xs-8 control-label">Working in tight area</label>
					                      	<div class="col-sm-4 col-xs-4">
						                        <label class="switch">
						                          <input type="checkbox">
						                          <span></span>
						                        </label>
					                      	</div>
					                    </div>
					                    <div class="form-group m-b-none row">
					                      	<label class="col-sm-8 col-xs-8 control-label">Working over your head</label>
					                      	<div class="col-sm-4 col-xs-4">
						                        <label class="switch">
						                          <input type="checkbox">
						                          <span></span>
						                        </label>
					                      	</div>
					                    </div>
					                    <div class="form-group m-b-none row">
					                      	<label class="col-sm-8 col-xs-8 control-label">Repetitive motion</label>
					                      	<div class="col-sm-4 col-xs-4">
						                        <label class="switch">
						                          <input type="checkbox">
						                          <span></span>
						                        </label>
					                      	</div>
					                    </div>
					                    <div class="form-group m-b-none row">
					                      	<label class="col-sm-8 col-xs-8 control-label">Repetitive work in awkward position</label>
					                      	<div class="col-sm-4 col-xs-4">
						                        <label class="switch">
						                          <input type="checkbox">
						                          <span></span>
						                        </label>
					                      	</div>
					                    </div>
					                    <div class="form-group m-b-none row" style="position: absolute;bottom: 0; left: 15px; right: 15px;">
					                      	<div class="col-sm-12">
						                        <input type="text" placeholder="Other" class="form-control" name="">
					                      	</div>
					                    </div>
				               		</div>
				               	</div>
								
							</div>
						</section>
					</div>
					<div class="col-sm-12 col-md-12 col-lg-4 p-bottom-20">
						<div class="row">
							<div class="col-sm-6 col-md-6 col-lg-12">
								<section class="panel panel-default p-n">
									<header class="panel-heading">
											<h4 class="text-success pull-left">Estimate Details</h4>
											<div class="clear"></div>
									</header>
									<div class="panel-body">
										<strong>Estimator:</strong> <?php echo nl2br($estimator_data->firstname . ' ' . $estimator_data->lastname); ?>
										<hr>
										<div class="panel-group m-b" id="accordion2">
										<?php foreach ($event_services as $key => $service_data) : ?>
											<div class="panel panel-default">
												<div class="panel-heading">
													<a class="accordion-toggle btn-block" style="text-decoration: none;" data-toggle="collapse" data-parent="#accordion2" href="#collapse<?php echo $key; ?>">
                                                        <div class="row"><strong
                                                                    class="col-lg-3">Price:</strong><?php echo money($service_data['service_price']); ?>
                                                        </div>
														<div class="row">
															<strong class="col-lg-12">Description:</strong><div class="col-lg-12"><?php echo mb_substr(nl2br($service_data['service_description']), 0, 90); ?>...&nbsp;<button class="btn btn-info btn-xs">More&nbsp;<i class="fa fa-hand-o-down"></i></button></div>
														</div>
													</a>
												</div>
												<div id="collapse<?php echo $key; ?>" class="panel-collapse collapse">
													<div class="panel-body text-sm">
														<?php echo nl2br($service_data['service_description']); ?>
														<hr>
														<div class="row">
															<div class="col-sm-6">
																<strong>Team Requirements:</strong>
																<?php
																foreach($estimate_services[$service_data['service_id']]->crew as $jkey=>$crew) : ?>
																	<div>
																		<i class="fa fa-check"></i>
																		<?php echo $crew->crew_name; ?><br>
																	</div>
																<?php endforeach; ?>
															</div>
															<div class="col-sm-6">
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

															</div>
														</div>
														<br>
														<div class="row">
															<div class="col-sm-6">
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
															</div>
															<div class="col-sm-6">
																
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
													</div>
												</div>
											</div>
										<?php endforeach; ?>
										</div>
									</div>
								</section>
							</div>
							<div class="col-sm-6 col-md-6 col-lg-12">
								<section class=" panel panel-default p-n">
									<header class="panel-heading">
											<h4 class="text-success pull-left">CONTROLS</h4>
											<div class="clear"></div>
									</header>
									<div class="panel-body">
										
										<p><strong>Shutdowns / Permits / Tags</strong></p>
										<div class="form-group m-b-none row">
					                      	<label class="col-sm-8 col-xs-9 control-label">Safe Work Procedure Reviewed</label>
					                      	<div class="col-sm-4">
						                        <label class="switch">
						                          <input type="checkbox">
						                          <span></span>
						                        </label>
					                      	</div>
					                    </div>
					                    <div class="form-group m-b-none row">
					                      	<label class="col-sm-8 col-xs-9 control-label">Working at Heights Planning Card</label>
					                      	<div class="col-sm-4">
						                        <label class="switch">
						                          <input type="checkbox">
						                          <span></span>
						                        </label>
					                      	</div>
					                    </div>
					                    <div class="form-group m-b-none row">
					                      	<label class="col-sm-8 col-xs-9 control-label">Equipment Log / Checklist</label>
					                      	<div class="col-sm-4">
						                        <label class="switch">
						                          <input type="checkbox">
						                          <span></span>
						                        </label>
					                      	</div>
					                    </div>
					                    <div class="form-group m-b-none row">
					                      	<label class="col-sm-8 col-xs-9 control-label">Locates valid</label>
					                      	<div class="col-sm-4">
						                        <label class="switch">
						                          <input type="checkbox">
						                          <span></span>
						                        </label>
					                      	</div>
					                    </div>

					                    <p><strong>Shutdowns / Permits / Tags</strong></p>
					                    <div class="form-group m-b-none row">
					                      	<label class="col-sm-8 col-xs-9 control-label">CSA Approved hard hat</label>
					                      	<div class="col-sm-4">
						                        <label class="switch">
						                          <input type="checkbox">
						                          <span></span>
						                        </label>
					                      	</div>
					                    </div>
					                    <div class="form-group m-b-none row">
					                      	<label class="col-sm-8 col-xs-9 control-label">Safety boots</label>
					                      	<div class="col-sm-4">
						                        <label class="switch">
						                          <input type="checkbox">
						                          <span></span>
						                        </label>
					                      	</div>
					                    </div>
					                    <div class="form-group m-b-none row">
					                      	<label class="col-sm-8 col-xs-9 control-label">Safety glasses</label>
					                      	<div class="col-sm-4">
						                        <label class="switch">
						                          <input type="checkbox">
						                          <span></span>
						                        </label>
					                      	</div>
					                    </div>
					                    <div class="form-group m-b-none row">
					                      	<label class="col-sm-8 col-xs-9 control-label">High-Visibility Garment</label>
					                      	<div class="col-sm-4">
						                        <label class="switch">
						                          <input type="checkbox">
						                          <span></span>
						                        </label>
					                      	</div>
					                    </div>
					                	
									</div>
								</section>
							</div>
						</div>
					</div>

					<?php $this->load->view('events/_partials/teamlead_signature'); ?>
					<?php $this->load->view('events/_partials/client_signature'); ?>

				</form>	
			</div>
		</section>
	</section>
</section>

<link rel="stylesheet" href="<?php echo base_url('assets/js/libs/signature_pad/css/ie9.css'); ?>">
<link rel="stylesheet" href="<?php echo base_url('assets/js/libs/signature_pad/css/signature-pad.css'); ?>">

<script src="<?php echo base_url('assets/js/libs/signature_pad/js/signature_pad.js'); ?>"></script>
<script src="<?php echo base_url('assets/js/libs/signature_pad/js/app.js'); ?>"></script>

<?php $this->load->view('includes/footer'); ?>