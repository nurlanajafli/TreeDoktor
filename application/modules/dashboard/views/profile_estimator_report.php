<!------Report-------------->
	<div id="reportInfo-<?php echo $report_id; ?>" class="modal fade reportInfoModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
	     aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-body">
					<div class="form-group m-b-none">
						<label class="col-sm-6 control-label">Report Date:</label>
						<div class="col-sm-6">
							<p class="form-control-static">
								<strong>
									<?php echo $report_date; //var_dump($report); die;?>
								</strong>
							</p>
						</div>
						<div class="clear"></div>
						<div class="line line-dashed line-lg"></div>
					</div>
					<div class="form-group m-b-none">
						<label class="col-sm-6 control-label">Estimator:</label>
						<div class="col-sm-6">
							<p class="form-control-static">
								<strong>
									<?php echo $emp_name; ?>
								</strong>
							</p>
						</div>
						<div class="clear"></div>
						<div class="line line-dashed line-lg"></div>
					</div>
					<div class="form-group m-b-none">
						<label class="col-sm-6 control-label">Total estimates for the day:</label>
						<label class="col-sm-6 control-label" style="text-align:left!important;">
							<strong><?php echo $total_estimates;?></strong>
						</label>
						<div class="clear"></div>
						<div class="line line-dashed line-lg"></div>
					</div>
				
					<div class="form-group m-b-none">
						<label class="col-sm-6 control-label">Total tasks for the day:</label>
						<label class="col-sm-6 control-label" style="text-align:left!important;">
							<strong><?php echo $total_tasks;?></strong>
						</label>
						<div class="clear"></div>
						<div class="line line-dashed line-lg"></div>
					</div>
					<div class="form-group m-b-none">
						<label class="col-sm-6 control-label">Comment:</label>
						<label class="col-sm-6 control-label" style="text-align:left!important;">
							<strong><?php echo $report_comment;?></strong>
						</label>
						<div class="clear"></div>
						<div class="line line-dashed line-lg"></div>
					</div>

					<?php 
					if(isset($report['worked_data']->mdl_emp_login) && !empty($report['worked_data']->mdl_emp_login)) : ?>
					<?php foreach($report['worked_data']->mdl_emp_login as $key=>$login) : //var_dump($report['worked_data']->mdl_emp_login); die; ?>
					<?php //$time_diff += strtotime($login['logout_time']) - strtotime($login['login_time']); ?>
						<div class="form-group m-b-none">
							<label class="col-sm-6 control-label">Start Time:</label>
							<label class="col-sm-6 control-label" style="text-align:left!important;">
								<strong><?php echo @$login->login;?></strong>
							</label>
							<label class="col-sm-6 control-label"></label>
								<label class="col-sm-6 control-label" style="text-align:left!important;">
									<?php if(!empty($login->location_login)) : //var_dump($login->location_login->lead_status); die;?>
										<?php if(is_object($login->location_login) && isset($login->location_login->lead_status_id) && ($login->location_login->lead_status_declined || $login->location_login->lead_status_estimated)) : ?>
												Lead to <a class="loc_map" href="<?php echo base_url($login->location_login->lead_no); ?>" target="_blank"><?php echo $login->location_login->lead_city . ' ' . $login->location_login->lead_address; ?></a>
										<?php elseif(is_array($login->location_login) && isset($login->location_login['client_id'])) : ?>
												Estimate to <a class="loc_map" href="<?php echo base_url($login->location_login['client_id']); ?>" target="_blank"><?php echo $login->location_login['client_name']; ?></a>
										<?php elseif(is_array($login->location_login) && isset($login->location_login['task_client_id'])) : ?>
												Task to <a class="loc_map" href="<?php echo base_url($login->location_login['task_client_id']); ?>" target="_blank"><?php echo $login->location_login['client_name'];?></a>
										<?php elseif(is_array($login->location_login) && isset($login->location_login['office'])) : ?>
												<strong>
													<a class="loc_map" href="https://maps.google.com/maps?q=<?php echo sprintf("%.4f", $login->login_lat); ?>,<?php echo sprintf("%.4f", $login->login_lon); ?>" target="_blank">Office</a>
												</strong>
										<?php endif; ?>
									<?php elseif($login->login_lat || $login->login_lon) : ?>
										<strong>
											<a class="loc_map" href="https://maps.google.com/maps?q=<?php echo sprintf("%.4f", $login->login_lat); ?>,<?php echo sprintf("%.4f", $login->login_lon); ?>">
												Login Location (<?php echo  sprintf("%.4f", $login->login_lat) . ',' . sprintf("%.4f", $login->login_lon);?>)
											</a>
										</strong>
									<?php else : ?>
										<strong style="color:red">
											No coords
										</strong>
									<?php endif; ?>
								</label>
							<div class="clear"></div>
							
						</div><br>
					
						<div class="form-group m-b-none">
							<label class="col-sm-6 control-label">End Time:</label>
							<label class="col-sm-6 control-label" style="text-align:left!important;">
								<strong><?php echo @$login->logout;?></strong>
							</label>
							<label class="col-sm-6 control-label"></label>
								<label class="col-sm-6 control-label" style="text-align:left!important;">
									<?php if(!empty($login->location_logout)) : ?>
									
										<?php if(isset($login->location_logout['client_id'])) :?>
												Estimate to <a class="loc_map" href="<?php echo base_url($login->location_logout['client_id']); ?>" target="_blank"><?php echo $login->location_logout['client_name']; ?></a>
										<?php elseif(isset($login->location_logout['task_client_id'])) : ?>
												Task to <a class="loc_map" href="<?php echo base_url($login->location_logout['task_client_id']); ?>" target="_blank"><?php echo $login->location_logout['client_name'];?></a>
										<?php elseif(isset($login->location_logout['office'])) :?>
												<strong>
													<a class="loc_map" href="https://maps.google.com/maps?q=<?php echo sprintf("%.4f", $login->logout_lat); ?>,<?php echo sprintf("%.4f", $login->logout_lon); ?>" target="_blank">Office</a>
												</strong>
										<?php endif; ?>
									<?php elseif($login->login_lat || $login->login_lon) : ?>
										<strong>
											<a class="loc_map" href="https://maps.google.com/maps?q=<?php echo sprintf("%.4f", $login->logout_lat); ?>,<?php echo sprintf("%.4f", $login->logout_lon); ?>">
												Logout Location (<?php echo sprintf("%.4f", $login->logout_lat) . ',' . sprintf("%.4f", $login->logout_lon);?>)
											</a>
										</strong>
									<?php else : ?>
										<strong style="color:red">
											No coords
										</strong>
									<?php endif; ?>
								</label>
							<div class="clear"></div>
							<div class="line line-dashed line-lg"></div>
						</div>
					
				<?php endforeach; ?>
					<div class="form-group m-b-none">
							<label class="col-sm-6 control-label">Hours per day:</label>
							<label class="col-sm-6 control-label" style="text-align:left!important;">
								<?php echo $report['worked_data']->worked_hours; ?> hrs.
							</label>
							<div class="clear"></div>
							<div class="line line-dashed line-lg"></div>
						</div>
				<?php endif; ?>
						
				<?php if ($this->session->userdata('user_type') == "admin" || $this->session->userdata('SCHD_RPR') == 1) : ?>
					<?php if(!$report_confirm) : ?>
					<div class="form-group m-b-none m-t-md">
						<button class="btn btn-success pull-left confirmEstReport" data-report_id="<?php echo $report_id; ?>">Confirm Report</button>
						<div class="clear"></div>
					</div>
					<?php else : ?>
					<div class="alert alert-success m-t-md text-center">
						<div>&nbsp;</div>
						<h4>CONFIRMED</h4>
					</div>
					<?php endif; ?>
				<?php endif; ?>

				</div>
				<div class="modal-footer">
					<button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
				</div>
			</div>
		</div>
	</div>
	
	<!------End Report-------------->
