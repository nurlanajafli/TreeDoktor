<!------Report-------------->
	<div id="eventInfo-report-modal" class="modal fade reportInfoModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
	     aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-body">

					<div class="reports-container">

					</div>

					<?php /*if(isset($members[$id]) && count($members[$id])) : ?>
						<?php foreach($members[$id] as $key=>$member) : ?>
							<div class="form-group m-b-none">
								<?php if($key == 0) : ?>
								<label class="col-sm-12 control-label">
									<?php echo 'Team Members:';?>
								</label>
								<?php endif; ?>
								<label class="col-sm-6 control-label" style="text-align:left!important;">
									<a href="<?php echo base_url() . 'employees/payroll/' . $member['employee_id'] . '/' . $member['payroll']->payroll_id; ?>" target="_blank">
										<?php if($member['employee_id'] == $member['team_leader_id']) : ?><strong><?php endif; ?>
											<?php echo $member['emp_name']; ?><br>
											Logout Time: <?php echo $member['employee_logout'] ? $member['employee_logout'] : '—'; ?>
										<?php if($member['employee_id'] == $member['team_leader_id']) : ?></strong><?php endif; ?>
									</a>
								</label>
								<label class="col-sm-6 control-label" style="text-align:left!important;">
									<?php if(!empty($member['logouts']) && $member['logouts']) :  ?>
										<?php foreach($member['logouts'] as $jkey=>$logout) :  ?>
											<?php if(count($member['logouts']) != 1 && $jkey != 0) : ?>
												<br><br>
											<?php endif; ?>
											Login Time: <?php echo $logout['login_time'];?><br>
											Logout Time: <?php echo $logout['logout_time'];?>
											
										<?php endforeach; ?>
									<?php endif; ?>
								</label>
								
								<div class="clear"></div>
								<?php if($key + 1 != count($members[$id])) : ?>
									<div class="line line-dashed line-lg"></div>
								<?php endif; ?>
							</div>
						<?php endforeach; ?>							
					<?php endif;*/ ?>

				<?php /*if ($this->session->userdata('user_type') == "admin" || $this->session->userdata('SCHD_RPR') == 1) : ?>
					<?php if(!$event_report_confirmed) : ?>
					<div class="form-group m-b-none m-t-md">
						<button class="btn btn-success pull-left confirmReport" data-event_id="<?php echo $id; ?>">Confirm Report</button>
						<div class="clear"></div>
					</div>
					<?php else : ?>
					<div class="alert alert-success m-t-md text-center">
						<div>&nbsp;</div>
						<h4>CONFIRMED</h4>
					</div>
					<?php endif; ?>
				<?php endif;*/ ?>

				</div>
				<div class="modal-footer">
					<button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
				</div>
			</div>
		</div>
	</div>
	
	<!------End Report-------------->
