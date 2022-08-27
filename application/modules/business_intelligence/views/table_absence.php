
	<div class="form-horizontal table-absence-rows">
		<table class="table m-b-none">
			
			<thead>
				<th class="bg-light text-right" style="border: 1px solid #cfcfcf">Date</th>
				<th class="bg-light text-center" style="border: 1px solid #cfcfcf">Reason</th>
				<th class="bg-light text-center" style="border: 1px solid #cfcfcf">Action</th>
			</thead>
			<tbody>
				<?php if(isset($rows) && !empty($rows)) :?>
					<?php foreach($rows as $key=>$val) :?>
						<tr <?php if(is_weekend($val->absence_ymd)) : ?> class="bg-warning"<?php endif; ?>>
							<td class="bg-light font-bold b-a text-right" style="font-weight: bold; background: #f1f1f1;color: #717171;border-top: 1px solid #cfcfcf;">
								<?php echo getDateTimeWithDate($val->absence_ymd, 'Y-m-d'); ?>
							</td>
							<td class="b-a text-center" style="border-top: 1px solid #cfcfcf;">
								<?php echo $val->mdl_reasons->reason_name; ?>
							</td>
							<td class="b-a text-center" style="border-top: 1px solid #cfcfcf;">
								<a href="#" data-id="<?php echo $val->absence_user_id; ?>" data-reason="<?php echo $val->absence_reason_id; ?>" data-date="<?php echo $val->absence_ymd; ?>" class="removeAbs btn-danger btn btn-xs"><i class="fa fa-trash-o"></i></a>
							</td>
						</tr>
					<?php endforeach; ?>
				<?php else : ?>
					<tr colspan="3" class="b-a">
						<td>&nbsp;</td>
						<td class="text-center">No records</td>
						<td >&nbsp;</td>
					</tr>
				<?php endif; ?>
			</tbody>
			
		</table>
	</div>
