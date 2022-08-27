<?php $groups = array(18); ?>
<?php $col_md = 4; ?>
<div id="<?php echo empty($report_id) ? $date . '-' . $id : 'report_' . $report_id; ?>" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content panel panel-default p-n text-dark">
			<header class="panel-heading text-left">Service Report</header>
			<form class="reportForm" data-type="ajax" method="POST" data-url="<?php echo base_url('equipments/ajax_service_report'); ?>" data-location="<?php echo current_url(); ?>">
				<div class="modal-body form-horizontal">
					<div class="form-group">
						<label class="col-sm-3 control-label">Date</label>
						<div class="col-sm-9">
<!--							<input class="form-control datepicker" name="report_date" value="--><?php //echo !empty($report_id) ? date('Y-m-d', strtotime($report_date_created)) : date('Y-m-d'); ?><!--" readonly>-->
							<input class="form-control datepicker" name="report_date" value="<?php echo !empty($report_id) ? getDateTimeWithDate($report_date_created, 'Y-m-d H:i:s') : date(getDateFormat()); ?>" readonly>
							<span class="help-inline text-danger"></span>
						</div>
					</div>
					<div class="line line-dashed line-lg"></div>
					<div class="form-group">
						<label class="col-sm-3 control-label">Comment</label>
						<div class="col-sm-9">
							<textarea class="form-control" name="report_comment"><?php echo !empty($report_comment) ? $report_comment : ''; ?></textarea>
							<span class="help-inline text-danger"></span>
						</div>
					</div>

					<div class="line line-dashed line-lg"></div>
					<div class="form-group">
						<label class="col-sm-3 control-label">Labor</label>
						<div class="col-sm-9">
							<input class="form-control" name="report_time" value="<?php echo !empty($report_hours) ? $report_hours : ''; ?>">
							<span class="help-inline text-danger"></span>
						</div>
					</div>

					<div class="line line-dashed line-lg"></div>
					<div class="form-group">
						<label class="col-sm-3 control-label">Service Cost</label>
						<div class="col-sm-9">
							<?php 
								$cost = isset($report_cost) ? $report_cost : 0;
								if(isset($complete_reports[0]) && !$report_id)
									$cost = $complete_reports[0]['report_cost'];
							?>
							<input class="form-control" name="report_cost" value="<?php echo $cost; ?>">
							<span class="help-inline text-danger"></span>
						</div>
					</div>
					
					
					<?php if(!in_array($group_id, $groups)) : ?>
					<?php $col_md = 3; ?>
						<div class="line line-dashed line-lg"></div>
						<div class="form-group">
							<label class="col-sm-3 control-label">Current Hours/Kms</label>
							<div class="col-sm-9">
								<input class="form-control" name="report_kilometers" value="<?php echo !empty($report_counter_kilometers_value) && !empty($report_id) ? $report_counter_kilometers_value : ''; ?>"
								data-toggle="tooltip" data-placement="top" title="" data-original-title="">
								<span class="help-inline text-danger"></span>
							</div>
						</div>
					<?php endif; ?>

					<div class="line line-dashed line-lg"></div>
					<div class="form-group">
						<label class="col-sm-3 control-label">Completed By</label>
						<div class="col-sm-9">
							<select name="report_user_id" class="form-control">
								<?php foreach($users as $user) : ?>
								<option value="<?php echo $user['id']; ?>"<?php if((isset($report_create_user) && $report_create_user == $user['id']) || (!isset($report_create_user) && $user['id'] == $this->session->userdata('user_id'))) : ?> selected="selected"<?php endif; ?>><?php echo $user['firstname'] . ' ' . $user['lastname']; ?></option>
							<?php endforeach; ?>
							</select>
							<span class="help-inline text-danger"></span>
						</div>
					</div>
					<?php /*
					<div class="form-group">
						<label class="col-sm-3 control-label">Hours Counter</label>
						<div class="col-sm-9">
							<input class="form-control" name="report_hours" value="<?php echo !empty($report_counter_hours_value) && !empty($report_id) ? $report_counter_hours_value : ''; ?>"
								data-toggle="tooltip" data-placement="top" title="" data-original-title="">				
							<span class="help-inline text-danger"></span>
						</div>
					</div> */ ?>
					<div class="modal-footer">
						<?php if(isset($history_reports) && $history_reports && !empty($history_reports)) : ?>
						<div class="row">
							<div class="col-md-<?php echo $col_md; ?> b-r b-t b-b b-l text-center bg-light"  style="width: 100px;"><strong>Date</strong></div>
							<div class="col-md-<?php echo $col_md; ?> b-r b-t b-b text-center bg-light" style="width: 170px;"><strong>Name</strong></div>
							<?php if(!in_array($group_id, $groups)) : ?>
								<div class="col-md-<?php echo $col_md; ?> b-r b-t b-b text-center bg-light" style="width: 100px;"><strong>Hours/Kms</strong></div>
							<?php endif; ?>
							<div class="col-md-<?php echo $col_md; ?> b-r b-t b-b text-center bg-light" style="width: 170px;"><strong>Completed/Postponed</strong></div>
							<?php /*<div class="col-md-<?php echo $col_md; ?> b-r b-t b-b text-center bg-light"><strong>HR</strong></div>*/ ?>
						</div>
							<?php foreach($history_reports as $jkey=>$jval) : ?>
									<div class="row reportData">
										<div class="col-md-<?php echo $col_md; ?> b-r b-b b-l text-center" style="width: 100px;">
<!--											--><?php //echo date('Y-m-d', strtotime($jval['report_date_created'])); ?>
											<?php echo getDateTimeWithDate($jval['report_date_created'], 'Y-m-d H:i:s'); ?>
										</div>
										<div class="col-md-<?php echo $col_md; ?> b-r b-b text-center" style="width: 170px;">
											<?php if($jval['firstname'] && $jval['lastname']) : ?>
												<?php echo $jval['firstname'];?> <?php echo $jval['lastname'];?>
											<?php else : ?>
												-
											<?php endif;  ?>
										</div>
										<?php if(!in_array($group_id, $groups)) : ?>
											<div class="col-md-<?php echo $col_md; ?> b-r b-b text-center counter" style="width: 100px;">
												<?php if($jval['report_counter_kilometers_value']) : ?>
													<?php echo $jval['report_counter_kilometers_value']; ?>
												<?php else : ?>
													-
												<?php endif;  ?>
											</div>
										<?php endif; ?>
										<div class="col-md-<?php echo $col_md; ?> b-r b-b text-center" style="width: 170px;">
											<?php if($jval['report_kind']) : ?>Postponed<?php else : ?>Completed<?php endif; ?>
										</div>
										<?php /*<div class="col-md-<?php echo $col_md; ?> b-r b-b text-center">
											<?php if($jval['report_counter_hours_value']) : ?>
												<?php echo $jval['report_counter_hours_value'];?>
											<?php else : ?>
												-
											<?php endif;  ?>
										</div>*/ ?>
									</div>
							<?php endforeach; ?>
							<br>
						<?php endif; ?>
						<?php if(empty($report_id)) : ?>
						<input type="hidden" name="report_service_type_id" value="<?php echo $equipment_service_id; ?>">
						<input type="hidden" name="report_service_setting_id" value="<?php echo !empty($id) ? $id : $report_service_settings_id; ?>">
						<input type="hidden" name="report_item_id" value="<?php echo $item_id; ?>">
						<?php endif; ?>
						<?php if(!empty($report_id)) : ?>
						<input type="hidden" name="report_id" value="<?php echo $report_id; ?>">
						<?php endif; ?>
						<button class="btn btn-info" type="submit">
							<span class="btntext">
								Save Report
							</span>
						</button>
						<button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>
<script>
	$('#<?php echo empty($report_id) ? $date . '-' . $id : 'report_' . $report_id; ?>').submit(function() {
		var lastVal = 0;
		$.each($(this).find('.counter'), function( key, val ) {
			if(parseFloat($(val).text()))
			{
				lastVal = parseFloat($(val).text());
				return false;
			}
		});
		if(lastVal != 0 && lastVal > $(this).find('[name="report_kilometers"]').val())
		{
			if(confirm('New Hours/Kms are less then previous entry, please check'))
				return true;
			else
				return false;
		}
		else
			return true; // return false to cancel form action
	});
</script>
