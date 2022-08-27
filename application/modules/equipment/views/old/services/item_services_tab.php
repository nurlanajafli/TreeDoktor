<script src="<?php echo base_url('assets/js/jquery.tablesorter.min.js'); ?>"></script>
<section class="panel panel-default p-n" style="box-shadow: none;border-top: none;">
	<header class="panel-heading" style="border-top: none;">
		Required Services To
		<form method="POST" action="" style="display: inline-block;">
			<input type="text" class="monthpicker date-input-client form-control text-center" name="date" value="<?php echo !empty($date) ? $date : date('Y-m'); ?>" readonly style="margin-left: 5px;width: 100px;display: inline-block;height: 20px;">
			<button type="submit" class="btn btn-xs btn-info" style="font-size: 11px;margin-top: -4px;margin-left: -2px;">GO</button>
		</form>
	</header>

	<table class="table" id="servicesList">
		<thead>
		<tr>
			<th>#</th>
			<th>Service Type</th>
			<th width="150">Service Date</th>
			<th width="180">Service Postponed On</th>
			<th width="">Next Counter Value</th>
			<th width="200px" style="text-align:center;">Action</th>
		</tr>
		</thead>
		<tbody>
		<?php if(!empty($months_services)) : ?>
			
			<?php foreach($months_services as $key => $service) : ?>
				<?php $kms = isset($service['complete_reports'][0]) ? intval($service['complete_reports'][0]['report_counter_kilometers_value'] + $service['service_period_kilometers']) : 0; ?>

				<tr <?php if($service['curr_service_date'] < date('Y-m')) : ?> class="bg-danger"<?php endif; ?>>
					<td><?php echo ($key+1); ?></td>
					<td><?php echo $service['equipment_service_type']; ?></td>
<!--					<td>--><?php //echo date("Y-m-d", strtotime($service['curr_service_date'])); ?><!--</td>-->
					<td><?php echo getDateTimeWithDate($service['curr_service_date'], 'Y-m-d'); ?></td>
					<td>
						<?php if($service['service_postpone_on']): ?>
						<?php echo $service['service_postpone_on']; ?> month
						<?php else: ?>
							-
						<?php endif; ?>
					</td>
					<td><?php echo ($kms)?$kms:'-'; ?></td>
					<td class="text-center">
						<a href="#<?php echo $date . '-' . $service['id']; ?>" class="btn btn-default btn-xs" role="button" data-toggle="modal" data-backdrop="true" data-keyboard="true">Complete</a>
						<?php echo $this->load->view('equipments/services/service_done_modal', $service); ?>
			
							<a href="#pone-<?php echo $date . '-' . $service['id']; ?>" class="btn btn-default btn-xs" role="button" data-toggle="modal" data-backdrop="true" data-keyboard="true">Postpone</a>
							<?php echo $this->load->view('equipments/services/service_pawn_modal', $service); ?>
					</td>
				</tr>

			<?php endforeach; ?>
		<?php else : ?>
			<tr>
				<td colspan="4" style="color:#FF0000;">No record found</td>
			</tr>
		<?php endif; ?>
		</tbody>
	</table>
</section>
<section class="panel panel-default p-n" style="box-shadow: none;">
	<header class="panel-heading">Reports1</header>
	<table class="table">
		<thead>
		<tr>
			<th>Service Type</th>
			<th width="150">Author</th>
			<th width="150">Date</th>
			<?php //<th>Hours Value</th> ?>
			<th>Counter Value</th>
			<th>Description</th>
			<th>Timing</th>
			<th>Cost</th>
			<th width="100px">Action</th>
		</tr>
		</thead>
		<tbody>
		<?php if(!empty($months_services_reports) && !empty($months_services_reports)) : ?>
			<?php foreach($months_services_reports as $key => $report) : ?>
				<tr <?php if(!$report['report_kind']) : ?>class='bg-success'<?php endif; ?>>
					<td><?php echo $report['equipment_service_type']; ?></td>
					<td><?php echo ($report['firstname'] && $report['lastname']) ? $report['firstname'] . ' ' . $report['lastname'] : '-'; ?></td>
<!--					<td>--><?php //echo date('Y-m-d', strtotime($report['report_date_created'])); ?><!----><?php //echo isset($report['report_kind']) ? ' (' . $report['report_kind'] . ')' : ''; ?><!--</td>-->
					<td><?php echo getDateTimeWithDate($report['report_date_created'], 'Y-m-d H:i:s'); ?><?php echo isset($report['report_kind']) ? ' (' . $report['report_kind'] . ')' : ''; ?></td>
					<?php /*<td><?php echo isset($report['report_counter_hours_value']) ? $report['report_counter_hours_value'] : ''; ?></td>*/ ?>
					<td><?php echo isset($report['report_counter_kilometers_value']) ? $report['report_counter_kilometers_value'] : ''; ?></td>
					<td><?php echo $report['report_comment']; ?></td>
					<td><?php echo $report['report_hours']; ?></td>
                    <td><?php echo money($report['report_cost']); ?></td>
					<td class="text-center">
						<?php if($report['report_kind']) : ?>
							<a href="#report_<?php echo $report['report_id']; ?>" class="btn btn-default btn-xs" role="button" data-toggle="modal" data-backdrop="true" data-keyboard="true">Edit</a>
							<?php echo $this->load->view('equipments/services/service_pawn_modal', $report); ?>
						<?php else : ?>
							<a href="#report_<?php echo $report['report_id']; ?>" class="btn btn-default btn-xs" role="button" data-toggle="modal" data-backdrop="true" data-keyboard="true">Edit</a>
							<?php echo $this->load->view('equipments/services/service_done_modal', $report); ?>
						<?php endif; ?>
					</td>
				</tr>
			<?php endforeach; ?>
		<?php else : ?>
			<tr>
				<td colspan="4" style="color:#FF0000;">No record found</td>
			</tr>
		<?php endif; ?>
		</tbody>
	</table>
</section>

<script>
	$(document).ready(function () {
		$('.datepicker').datepicker({
			format: "yyyy-mm-dd"
		});
		$('.monthpicker').datepicker({
			format: "yyyy-mm",
			viewMode: "months",
			minViewMode: "months"
		});
		if($("#servicesList").find('tr').length > 2)
		{
			$("#servicesList").tablesorter({sortList: [
				[2, 0]
			]});
			$.each($('#servicesList tbody tr'), function(key, val){
				$(val).find('td:first').text((key + 1));
			});
		}
	});
</script>
