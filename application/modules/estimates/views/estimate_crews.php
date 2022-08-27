<section class="col-md-12 panel panel-default p-n">
	<!-- Team -->
	<header class="panel-heading">Crew
		<select name="estimate_crew_id" style="width:300px;display:inline-block;" id="selectCrew"
		        class="form-control m-l-md">
			<option value="">Select Crew</option>
			<?php foreach ($crews as $crew) : ?>
				<option
					value="<?php echo $crew->crew_id; ?>"><?php echo $crew->crew_name; ?><?php if ($crew->emp_name) : ?> (<?php echo $crew->emp_name; ?>)<?php endif; ?></option>
			<?php endforeach; ?>
		</select>
		<?php if (isset($estimate_data->estimate_item_team) && $estimate_data->estimate_item_team) {
			echo "  Originally proposed crew: " . $estimate_data->estimate_item_team;
		} ?>
	</header>
</section>

<div id="estimateCrews" class="row">
	<?php if (isset($estimate_crews_data)) : ?>
		<?php foreach ($estimate_crews_data as $estimate_crew) : ?>
			<section class="col-md-4" id="crew_<?php echo $estimate_crew['crew_id']; ?>">
				<section class="panel panel-default p-n">
					<header class="panel-heading">
						<div class="col-md-7" style="padding-top: 7px;">
							<?php echo $estimate_crew['crew_name']; ?>
							<?php if ($estimate_crew['emp_name']) : ?>
								(<?php echo $estimate_crew['emp_name']; ?>)
							<?php endif; ?>
						</div>
						<div class="col-md-4">
							<input type="text"
							       name="crew_team[<?php echo $estimate_crew['crew_id']; ?>][estimate_crew_team]"
							       placeholder="Team" class="form-control team_count"
							       value="<?php echo $estimate_crew['estimate_crew_team']; ?>">
						</div>
						<a class="btn btn-danger btn-xs pull-right deleteCrew m-t-xs"
						   data-crew_id="<?php echo $estimate_crew['crew_id']; ?>"><i class="fa fa-trash-o"></i></a>
						<input type="hidden"
						       name="crew_team[<?php echo $estimate_crew['crew_id']; ?>][estimate_crew_id]"
						       value="<?php echo $estimate_crew['estimate_crew_id']; ?>">

						<div class="clear"></div>
					</header>
				</section>
			</section>
		<?php endforeach; ?>
	<?php endif; ?>
</div>
<script type="text/javascript">
	$(document).ready(function () {
		$('#selectCrew').change(function () {
			var obj = $('#selectCrew [value="' + $('#selectCrew').val() + '"]');
			if ($('#selectCrew').val()) {
				var html = '<section class="col-md-4" id="crew_' + $('#selectCrew').val() + '"><section class="panel panel-default p-n"><header class="panel-heading">';
				html += '<div class="col-md-7" style="padding-top: 7px;">' + $(obj).text() + '</div>';
				html += '<div class="col-md-4"><input type="text" name="crew_team[' + $('#selectCrew').val() + '][estimate_crew_team]" placeholder="Team" class="form-control team_count"></div>';
				html += '<a class="btn btn-danger btn-xs pull-right deleteCrew m-t-xs" data-crew_id="' + $('#selectCrew').val() + '"><i class="fa fa-trash-o"></i></a>';
				html += '<div class="clear"></div></header>';
				html += '</section></section>';
				$('#estimateCrews').prepend(html);
				$('#selectCrew').val('');
				$('#selectCrew').change();
				$(obj).attr('disabled', 'disabled');
			}
			return false;
		});
		$(document).on('click', '.deleteCrew', function () {
			var crew_id = $(this).data('crew_id');
			if ($('[name="crew_team[' + crew_id + '][estimate_crew_id]"]'))
				$('#crew_' + crew_id).replaceWith('<input name="delete_crews[' + crew_id + ']" type="hidden" value="' + $('[name="crew_team[' + crew_id + '][estimate_crew_id]"]').val() + '">');
			else
				$('#crew_' + crew_id).remove();
			$('#selectCrew [value="' + crew_id + '"]').removeAttr('disabled');
			return false;
		});
		$.each($('#estimateCrews').children(), function (key, val) {
			id = $(val).find('[data-crew_id]').data('crew_id');
			$('#selectCrew').children('option[value="' + id + '"]').attr('disabled', 'disabled');
		});
	});
</script>
