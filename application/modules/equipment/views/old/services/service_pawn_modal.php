<?php $groups = array(18); ?>
<?php $col_md = 3; ?>
<div id="<?php echo empty($report_id) ? 'pone-' . $date . '-' . $id : 'report_' . $report_id;?>" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content panel panel-default p-n text-dark">
			<header class="panel-heading text-left">Postpone Report</header>
			<form class="reportForm"  data-type="ajax" method="POST" data-url="<?php echo base_url('equipments/ajax_service_report'); ?>" data-location="<?php echo current_url(); ?>">
				<div class="modal-body form-horizontal">
					<div class="form-group">
						<label class="col-sm-3 control-label">Comment</label>
						<div class="col-sm-9">
							<textarea class="form-control" name="report_comment" data-toggle="tooltip" data-placement="top" title="" data-original-title=""><?php echo !empty($report_comment) ? $report_comment : ''; ?></textarea>
							<span class="help-inline text-danger"></span>
						</div>
					</div>
						
						<input type="hidden" name="current_service_date" value="<?php echo $date; ?>">
						<input type="hidden" name="service_postpone" value="<?php echo $service_postpone; ?>">
					
					<div class="line line-dashed line-lg"></div>
					<div class="form-group">
						<label class="col-sm-3 control-label">Postpone by</label>
						<div class="col-sm-9">							
							<select class="form-control" name="count_month" style="" data-toggle="tooltip" data-placement="top" title="" data-original-title="">
								<option value=""> - Select Count Of Months - </option>
								<?php for($i = 1; $i <= 36; $i++) : ?>
									<option value="<?php echo $i; ?>" <?php if(isset($report_kind) && $report_kind == $i) : ?> selected<?php endif; ?>><?php echo $i; ?></option>
								<?php endfor; ?>
							</select>
							<span class="help-inline text-danger"></span>
						</div>
					</div>
					<?php if(!in_array($group_id, $groups)) : ?>
					<?php $col_md = 3; ?>
						<div class="line line-dashed line-lg"></div>
						<div class="form-group">
							<label class="col-sm-3 control-label">Current Hours/Kms</label>
							<div class="col-sm-9">
								<input class="form-control" name="report_kilometers" value="<?php echo !empty($report_counter_kilometers_value) ? $report_counter_kilometers_value : ''; ?>"
								data-toggle="tooltip" data-placement="top" title="" data-original-title="">
								<span class="help-inline text-danger"></span>
							</div>
						</div>
					<?php endif; ?>
					<div class="line line-dashed line-lg"></div>


					<div class="form-group">
						<label class="col-sm-3 control-label">Postponed By</label>
						<div class="col-sm-9">
							<select name="report_user_id" class="form-control">
								<?php foreach($users as $user) : ?>
								<option value="<?php echo $user['id']; ?>"<?php if((isset($report_create_user) && $report_create_user == $user['id']) || (!isset($report_create_user) && $user['id'] == $this->session->userdata('user_id'))) : ?> selected="selected"<?php endif; ?>>
									<?php echo $user['firstname'] . ' ' . $user['lastname']; ?>
								</option>
							<?php endforeach; ?>
							</select>
							<span class="help-inline text-danger"></span>
						</div>
					</div>
					<?php /*
					<div class="form-group">
						<label class="col-sm-3 control-label">Hours Counter</label>
						<div class="col-sm-9">
							<input class="form-control" name="report_hours" value="<?php echo !empty($report_counter_hours_value) ? $report_counter_hours_value : ''; ?>"
								data-toggle="tooltip" data-placement="top" title="" data-original-title="">				
							<span class="help-inline text-danger"></span>
						</div>
					</div> */ ?>
					<div class="modal-footer">
						<?php if(isset($history_reports) && $history_reports && !empty($history_reports)) : ?>
							<div class="row">
								<div class="col-md-<?php echo $col_md; ?> b-r b-t b-b b-l text-center bg-light" style="width: 100px;"><strong>Date</strong></div>
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
<!--										--><?php //echo date('Y-m-d', strtotime($jval['report_date_created'])); ?>
                                        <?php echo getDateTimeWithDate($jval['report_date_created'], 'Y-m-d h:i:s'); ?>
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
						<input type="hidden" name="report_service_type_id" value="<?php echo $equipment_service_id; ?>">
						<input type="hidden" name="report_service_setting_id" value="<?php echo !empty($report_service_settings_id) ? $report_service_settings_id : $id; ?>">
						<input type="hidden" name="report_item_id" value="<?php echo $item_id; ?>">
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
	$('#<?php echo empty($report_id) ? 'pone-' . $date . '-' . $id : 'report_' . $report_id;?>').submit(function() {
		var lastVal = 0;
		$.each($(this).find('.counter'), function( key, val ) {
			if(parseFloat($(val).text()))
			{
				lastVal = parseFloat($(val).text());
				return false;
			}
		});
		if($(this).find('[name="report_kilometers"]').val() == '')
		{
			if(confirm('Do you want "Counter Value" to leave blank?'))
				return true;
			else
				return false;
		}
		else if(lastVal != 0 && lastVal > $(this).find('[name="report_kilometers"]').val())
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
