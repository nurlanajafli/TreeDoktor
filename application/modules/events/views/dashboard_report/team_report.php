<style type="text/css">.text-bold{font-weight: 700;}</style>
<?php if(!isset($team) || $team->isEmpty()): ?>
	<h4 class="text-center">Event not started</h4>
<?php else: ?>
<?php foreach($team as $key => $report) : ?>
    <?php $date = $report->er_report_date_original ? $report->er_report_date_original : $report->er_event_date; ?>
	<div class="one-report" id="one-report-<?php echo $report->er_id; ?>">
		<div class="form-group m-b-none">
			<label class="col-sm-4 control-label"><strong>Workorder:</strong></label>
			<div class="col-sm-8">
				<p class="form-control-static">
					<strong>
						<a style="text-decoration: none;" class="woProfile" href="<?php echo base_url($report->workorder->workorder_no); ?>" target="_blank">
							<i class="fa fa-map-marker"></i>&nbsp;<?php echo $report->workorder->workorder_no; ?>
                                <?php if($report->workorder): ?>
                                    - <?php echo $report->workorder->estimate->lead->lead_address; ?>
                                <?php endif; ?>
							<br><i class="fa fa-user"></i>&nbsp;<?php echo $report->workorder->estimate->lead->client_name; ?>
						</a>
					</strong>
				</p>
			</div>
			<div class="clear"></div>
			<div class="line line-dashed line-lg"></div>
		</div>
		
		<?php if(isset($report_edit_fields) && !empty($report_edit_fields)) : ?>
			<?php foreach($report_edit_fields as $fkey => $field): ?>
			<?php 
				if(isset($field['visibility_condition']) && !$field['visibility_condition']($report))
					continue;
			?>
			<div class="form-group m-b-none">
				<label class="col-sm-4 control-label">
				<?php echo $field['label']; ?>
				</label>
				<?php 
					$value = '';
                    $value = ucwords($report->{$field['name']});
				?>
				<label class="col-sm-8 control-label" style="text-align:left!important;word-break: break-word;">
					<?php if($field['edit']): ?>
						<a href="#" 
						class="editable editable-report <?php if(isset($field['class'])): echo $field['class']; endif; ?>" 
						data-name="<?php echo $field['name']; ?>" 
						data-type="<?php echo element('type', $field, 'text'); ?>" 
						data-title="<?php echo $field['label']; ?>"
						data-pk="<?php echo $report->er_id; ?>"
						data-url="<?php echo base_url('events/events/update_event_report_field'); ?><?php echo isset($report_event_id)?'/'.$report_event_id.'/1':''; ?>" 
						data-mode="inline"
						data-value="<?php echo $value; ?>"
                        data-emptytext="Empty"
						data-inputclass="<?php echo element('inputclass', $field, ''); ?>"
							<?php if(isset($field['source'])):?>data-source="<?php echo $field['source']; ?>"<?php endif; ?>
							id="<?php echo $report->er_id; ?>-<?php echo $field['name']; ?>">
							<strong class="<?php //echo isset($field['class_callback'])?$field['class_callback'](element($field['name'], $report->toArray(), ''), $report):''; ?>">
							<?php echo $value; ?>
							</strong>
						</a>
					<?php else: ?>
						<strong class="<?php //echo isset($field['class_callback'])?$field['class_callback'](element($field['name'], $report->toArray(), ''), $report):''; ?>">
                            <?php echo $value; ?>
                        </strong>
					<?php endif; ?>
				</label>
				<div class="clear"></div>
				<div class="line line-dashed line-lg"></div>
			</div>
			
		<?php endforeach; ?>
		<?php endif; ?>

        <div id="members-report-<?php echo $report->er_id; ?>">
        <?php
        $this->load->view('events/dashboard_report/members_report', ['report'=>$report]/*['command_members'=>$command_members, 'team_details'=>$val]*/);
        ?>
        </div>
        <?php
        if ($this->session->userdata('user_type') == "admin" || $this->session->userdata('SCHD_RPR') == 1) : ?>
			<?php if(!$report->er_report_confirmed) : ?>
			<div class="form-group m-b-none m-n clear" style="position: relative;height: 50px">
				<button class="btn btn-success pull-left confirmReport" data-er_id="<?php echo $report->er_id; ?>" data-event_id="<?php echo $report->er_event_id; ?>" data-leader_id="<?php echo $report->team->team_leader_user_id; ?>" style="position: absolute; bottom: 14px; right: 0;">Confirm Report</button>
				<div class="clear"></div>
			</div>
			<?php else : ?>
			<div class="alert alert-success m-t-md text-center">
				<div>&nbsp;</div>
				<h4>CONFIRMED</h4>
			</div>
			<?php endif; ?>
		<?php endif; ?>

		<div class="form-group clearfix p-left-15">
			<a class="text-info pull-left" target="_blank" href="<?php echo base_url('/events/tailgate_safety_pdf/'.$report->er_event_id.'/'.$date); ?>"><i class="fa fa-file"></i>&nbsp;Tailgate safety meeting form <strong>(PDF)</strong></a>
			<a class="text-warning pull-right" target="_blank" href="<?php echo base_url('/events/report_pdf/'.$report->er_event_id.'/'.$date); ?>"><i class="fa fa-file"></i>&nbsp;Report FORM <strong>(PDF)</strong></a>
		</div>
		<hr class="line line-dashed line-lg">
	</div><br>
	<?php endforeach; ?>
<?php endif; ?>
