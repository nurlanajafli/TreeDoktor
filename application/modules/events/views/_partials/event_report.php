
<input type="hidden" name="ev_event_id" value="<?php echo $event['id']; ?>">
<input type="hidden" name="ev_estimate_id" value="<?php echo $event['estimate_id']; ?>">
<input type="hidden" name="ev_team_id" value="<?php echo $event['team_id']; ?>">
<input type="hidden" name="workorder" value="<?php echo $event['workorder_no'] . '-' . $event['lead_address']; ?>">
<?php if(isset($started_events['ev_id'])): ?>
	<input type="hidden" name="ev_id" value="<?php echo $started_events['ev_id']; ?>">
<?php endif; ?>
<?php if(isset($started_events['ev_start_work'])): ?>
	<input type="hidden" name="event_start" value="<?php echo $started_events['ev_start_work']; ?>">
	<input type="hidden" name="event_start_time" value="<?php echo date('H:i', strtotime($started_events['ev_start_work'])); ?>">
	
	<input type="hidden" name="event_finish_hours">
	<input type="hidden" name="event_finish_min">
<?php endif; ?>

<div class="col-xs-12 visible-xs visible-sm">
	<span class="m-right-20 p-top-5 m-left-10"><span class="h5 report-label"><span class="glyphicon glyphicon-map-marker text-warning"></span>&nbsp;<strong>Workorder:</strong>&nbsp;<?php echo $event['workorder_no']; ?> (<?php echo $event['lead_address']; ?>)</span></span>
</div>
<div class="col-xs-12 visible-xs visible-sm">
	<br>
	<span class="m-right-20 p-top-5 m-left-10"><span class="h5 report-label"><span class="glyphicon glyphicon-time text-warning"></span>&nbsp;<strong>Work(start/finish):</strong>&nbsp;
	<?php if(isset($started_events['ev_id'])): ?>
	<?php echo date('h:m A', strtotime($started_events['ev_start_work'])); ?>
	<?php else: ?>
		--:--
	<?php endif; ?>&nbsp;/&nbsp;Now
	</span>
	</span>
	<div class="line line-dashed line-lg"></div>

</div>

<div class="event col-md-12">		
		<div class="row">

			<div class="col-md-6 col-sm-6 col-xs-12 b-r-md">
				<div class="form-group m-b-none">
					<br class="visible-xs visible-sm">
					<label class="col-md-4 col-sm-6 col-xs-5 control-label report-label">Finished:</label>
					<div class="col-md-8 col-sm-6 col-xs-7">
						<div class="row">
						  	<label class="radio-custom col-md-6 col-xs-6">
								<input type="radio" class="status" name="status" value="finished">
								<i class="fa fa-circle-o"></i>
								Yes
						  	</label>
						  	<label class="radio-custom  col-md-6 col-xs-6">
								<input type="radio" class="status" name="status" value="unfinished">
								<i class="fa fa-circle-o"></i>
								No
						  	</label>
						</div>
						<input type="hidden" name="wo_id" value="<?php echo $event['event_wo_id']; ?>">
						<br class="hidden-xs">
					</div>
					<span class="form-error text-danger"></span>
					<div class="clear"></div>
					<div class="line line-dashed line-lg"></div>
				</div>
				<div class="form-group m-b-none payment" style="display:none;">
					<br>
					<label class="col-md-4 col-sm-6 col-xs-5 control-label report-label">Payment:</label>
					<div class="col-md-8 col-sm-6 col-xs-7">
						<div class="row">
						  	<label class="radio-custom col-md-6 col-xs-6">
								<input type="radio" class="payment" name="event_payment" value="yes">
								<i class="fa fa-circle-o"></i>
								Yes
						  	</label>
						  	<label class="radio-custom col-md-6 col-xs-6">
								<input type="radio" class="payment" name="event_payment" value="no">
								<i class="fa fa-circle-o"></i>
								No
						  	</label>
						</div>
						<br class="hidden-xs">
					</div>
					<div class="clear"></div>
					<div class="line line-dashed line-lg"></div>
				</div>
				<div class="form-group m-b-none paymentSum" style="display:none;">
					<br>
					<label class="col-md-4 col-sm-6 col-xs-5 control-label report-label">Payment Type:</label>
					<div class="col-md-8 col-sm-6 col-xs-7">
						<div class="row">
						  	<label class="radio-custom col-md-6 col-xs-6">
								<input type="radio" class="payment_type" name="event_payment_type" value="Cash">
								<i class="fa fa-circle-o"></i>
								Cash
						  	</label>
							<label class="radio-custom col-md-6 col-xs-6">
								<input type="radio" class="payment_type" name="event_payment_type" value="Check">
								<i class="fa fa-circle-o"></i>
								Check
							</label>
						</div>
						<br class="hidden-xs">
					</div>
					<div class="clear"></div>
					<div class="line line-dashed line-lg"></div>
				</div>
				<div class="form-group m-b-none paymentSum" style="display:none;">
					<br>
					<div class="col-sm-12">
						<input type="text" disabled placeholder="Payment Amount" class="finished form-control currency" name="payment_amount">
					</div>
					<span class="form-error text-danger"></span>
					<div class="clear"></div>
					<div class="line line-dashed line-lg"></div>
				</div>
				
				<div class="form-group m-b-none finishDescription" style="display:none;">
					<br>
					<div class="col-sm-12">
						<textarea class="unfinished form-control" placeholder="Work Remaining" disabled name="event_work_remaining"></textarea>
					</div>
					<div class="clear"></div>
					<div class="line line-dashed line-lg"></div>
				</div>

				<div class="form-group m-b-none">
					<br>
					<label class="col-md-4 col-sm-6 col-xs-5 control-label report-label">Expenses:</label>
					<div class="col-md-8 col-sm-6 col-xs-7">
						<div class="row">
						  	<label class="radio-custom col-md-6 col-xs-6">
                            	<input type="radio" class="expenses" name="expenses" value="yes">
                            	<i class="fa fa-circle-o"></i>
                            	Yes
                          	</label>
						  	<label class="radio-custom col-md-6 col-xs-6">
								<input type="radio" class="expenses" name="expenses" value="no">
								<i class="fa fa-circle-o"></i>
								No
						  	</label>
						</div>
						
						<br class="hidden-xs">
					</div>
					<span class="form-error text-danger"></span>
					<div class="clear"></div>
					<div class="line line-dashed line-lg"></div>
				</div>
				<div class="form-group m-b-none expensesDesc" style="display:none;">
					<br>
					<div class="col-sm-12">
						<textarea class="form-control expensesText" placeholder="Expenses Description" disabled name="expenses_description" ></textarea>
					</div>
					<div class="clear"></div>
					<div class="line line-dashed line-lg"></div>
				</div>
			</div>
			<div class="col-md-6 col-sm-6 col-xs-12">
				<div class="form-group m-b-none">
					<br class="visible-xs visible-sm">
					<label class="col-md-4 col-sm-6 col-xs-5 control-label report-label">Damage:</label>
					<div class="col-md-8 col-sm-6 col-xs-7">
						<div class="row">
							<label class="radio-custom col-md-6 col-xs-6">
								<input type="radio" class="damage" name="event_damage" value="yes">
								<i class="fa fa-circle-o"></i>
								Yes
							</label>
							<label class="radio-custom col-md-6 col-xs-6">
								<input type="radio" class="damage" name="event_damage" value="no">
								<i class="fa fa-circle-o"></i>
								No
							</label>
						</div>
						<br class="hidden-xs">
					</div>
					<span class="form-error text-danger"></span>
					<div class="clear"></div>
					<div class="line line-dashed line-lg"></div>
				</div>
				<div class="form-group m-b-none dmgDescription" style="display:none;">
					<br>
					<div class="col-sm-12">
						<textarea class="form-control dmgText" placeholder="Damage Description" disabled name="event_damage_description" ></textarea>
					</div>
					<div class="clear"></div>
					<div class="line line-dashed line-lg"></div>
				</div>

				<div class="form-group m-b-none">
					<br>
					<label class="col-md-4 col-sm-6 col-xs-5 control-label report-label">Malfunctions Equipment:</label>
					<div class="col-md-8 col-sm-6 col-xs-7">
						<div class="row">
						  	<label class="radio-custom col-md-6 col-xs-6">
								<input type="radio" class="fail" name="malfunctions_equipment" value="yes">
								<i class="fa fa-circle-o"></i>
								Yes
						  	</label>
						  	<label class="radio-custom col-md-6 col-xs-6">
								<input type="radio" class="fail" name="malfunctions_equipment" value="no">
								<i class="fa fa-circle-o"></i>
								No
						  	</label>
						</div>
						<br class="hidden-xs">
					</div>

					<span class="form-error text-danger col-md-12 col-sm-12 col-xs-12"></span>
					<div class="clear"></div>
					<div class="line line-dashed line-lg"></div>
				</div>
				<div class="form-group m-b-none failDesc" style="display:none;">
					<br>
					<div class="col-sm-12">
						<textarea class="form-control failText" placeholder="Malfunctions Description" disabled name="malfunctions_description" ></textarea>
					</div>
					<div class="clear"></div>
					<div class="line line-dashed line-lg"></div>
				</div>
			</div>
		</div>
	</div>