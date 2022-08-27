<?php $this->load->view('includes/header'); ?>
<link rel="stylesheet" href="<?php echo base_url('assets/css/colpick.css'); ?>"/>
<script src="<?php echo base_url('assets/js/colpick.js'); ?>"></script>
<section class="scrollable p-sides-15">
	<ul class="breadcrumb no-border no-radius b-b b-light pull-in">
		<li><a href="<?php echo base_url(); ?>"><i class="fa fa-home"></i> Home</a></li>
		<li class="active">Employee Roles</li>
	</ul>
	<section class="panel panel-default">
		<header class="panel-heading">Employee Roles
			<a href="#crew-" class="btn btn-xs btn-success pull-right" role="button" data-toggle="modal"
			   data-backdrop="static" data-keyboard="false"><i class="fa fa-plus"></i></a>
			<div class="clear"></div>
		</header>
		<div class="table-responsive">
			<table class="table table-hover">
				<thead>
				<tr>
					<th>Crew Name</th>
					<th>Crew Full Name</th>
					<th>Crew Color</th>
					<th>Cost Per Hour</th>
					<th width="80px">Action</th>
				</tr>
				</thead>
				<tbody>
				<?php if($crews && !empty($crews)) : ?>
				<?php foreach ($crews as $crew) : ?>
					<tr<?php if (!$crew->crew_status) : ?> style="text-decoration: line-through;"<?php endif; ?>>
						<td><?php echo $crew->crew_name; ?></td>
						<td><?php echo $crew->crew_full_name; ?></td>
						<td><span
								style="background-color: <?php echo $crew->crew_color; ?>"><?php echo $crew->crew_color; ?></span>
						</td>
						<td>
							<?php if($crew->crew_rate) : ?>
								<?php echo $crew->crew_rate; ?>
							<?php else : ?>
								-
							<?php endif; ?>
						</td>
						<td>
							<a class="btn btn-default btn-xs" href="#crew-<?php echo $crew->crew_id; ?>" role="button"
							   data-toggle="modal" data-backdrop="static" data-keyboard="false"><i class="fa fa-pencil"></i></a>
							<?php if ($crew->crew_id) : ?>
							<a class="btn btn-xs btn-info deleteCrew" data-delete_id="<?php echo $crew->crew_id; ?>"><i
									class="fa <?php if ($crew->crew_status) : ?>fa-eye-slash<?php else : ?>fa-eye<?php endif; ?>"></i></a>
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
				<?php endif; ?>
				</tbody>
			</table>
		</div>
	</section>
</section>
<?php if($crews && !empty($crews)) : ?>
	<?php foreach ($crews as $crew) : ?>
	<div id="crew-<?php echo $crew->crew_id; ?>" class="modal fade" tabindex="-1" role="dialog"  aria-labelledby="myModalLabel" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content panel panel-default p-n">
				<header class="panel-heading">Edit Crew <?php echo $crew->crew_name; ?></header>
				<div class="modal-body">
					<div class="form-horizontal">
						<div class="control-group">
							<label class="control-label">Name</label>

							<div class="controls">
								<input class="crew_name form-control" type="text"
									   value="<?php echo $crew->crew_name; ?>"
									   placeholder="Crew Name" style="background-color: #fff;">
							</div>
						</div>
						<div class="control-group">
							<label class="control-label">Full Name</label>

							<div class="controls">
								<input class="crew_full_name form-control" type="text"
									   value="<?php echo $crew->crew_full_name; ?>"
									   placeholder="Crew Full Name" style="background-color: #fff;">
							</div>
						</div>
						<div class="control-group">
							<label class="control-label">Color</label>

							<div class="controls">
								<input type="text" class="crew_color form-control mycolorpicker"
									   readonly placeholder="Crew Color"
									   value="<?php echo $crew->crew_color; ?>" style="cursor: pointer;">
							</div>
						</div>
						<div class="control-group">
							<label class="control-label">Rate</label>

							<div class="controls">
								<input class="crew_rate form-control" type="text"
									   value="<?php if($crew->crew_rate) : echo $crew->crew_rate; endif;  ?>"
									   placeholder="Cost Per Hour" style="background-color: #fff;">
							</div>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button class="btn btn-success" data-save-crew="<?php echo $crew->crew_id; ?>">
						<span class="btntext">Save</span>
						<img src="<?php echo base_url(); ?>assets/img/ajax-loader.gif"
							 style="display: none;width: 32px;" class="preloader">
					</button>
					<button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
				</div>
			</div>
		</div>
	</div>
	<?php endforeach; ?>
<?php endif; ?>

<div id="crew-" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content panel panel-default p-n">
			<header class="panel-heading">Create Quality Crew</header>
			<div class="modal-body">
				<div class="form-horizontal">
					<div class="control-group">
						<label class="control-label">Name</label>

						<div class="controls">
							<input class="crew_name form-control" type="text" value="" placeholder="Crew Name">
						</div>
					</div>
					<div class="control-group">
						<label class="control-label">Full Name</label>

						<div class="controls">
							<input class="crew_full_name form-control" type="text" value="" placeholder="Crew Full Name">
						</div>
					</div>
					<div class="control-group">
						<label class="control-label">Color</label>

						<div class="controls">
							<input type="text" class="crew_color form-control mycolorpicker" readonly
							       placeholder="Crew Color" value="#FFFFFF" style="cursor: pointer;">
						</div>
					</div>
					<div class="control-group">
						<label class="control-label">Rate</label>

						<div class="controls">
							<input class="crew_rate form-control" type="text"
								   value=""
								   placeholder="Cost Per Hour" style="background-color: #fff;">
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button class="btn btn-success" data-save-crew="">
					<span class="btntext">Save</span>
					<img src="<?php echo base_url(); ?>assets/img/ajax-loader.gif" style="display: none;width: 32px;"
					     class="preloader">
				</button>
				<button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
			</div>
		</div>
	</div>
</div>
<script>
	$(document).ready(function () {
		$('[data-save-crew]').click(function () {
			var crew_id = $(this).data('save-crew');
			$(this).attr('disabled', 'disabled');
			$('#crew-' + crew_id + ' .modal-footer .btntext').hide();
			$('#crew-' + crew_id + ' .modal-footer .preloader').show();
			$('#crew-' + crew_id + ' .crew_name').parents('.control-group').removeClass('error');
			var crew_name = $('#crew-' + crew_id).find('.crew_name').val();
			var crew_full_name = $('#crew-' + crew_id).find('.crew_full_name').val();
			var crew_color = $('#crew-' + crew_id).find('.crew_color').val();
			var crew_rate = $('#crew-' + crew_id).find('.crew_rate').val();
			if (!crew_name) {
				$('#crew-' + crew_id + ' .crew_name').parents('.control-group').addClass('error');
				$('#crew-' + crew_id + ' .modal-footer .btntext').show();
				$('#crew-' + crew_id + ' .modal-footer .preloader').hide();
				$(this).removeAttr('disabled');
				return false;
			}
			$.post(baseUrl + 'employees/ajax_save_crew', {crew_id: crew_id, crew_name: crew_name, crew_full_name: crew_full_name, crew_color: crew_color, crew_rate:crew_rate}, function (resp) {
				if (resp.status == 'ok')
					location.reload();
				return false;
			}, 'json');
			return false;
		});
		$('.deleteCrew').click(function () {
			var crew_id = $(this).data('delete_id');
			if (confirm('Are you sure, you want to delete the crew?')) {
				if ($(this).children().is('.fa-eye'))
					status = 1;
				$.post(baseUrl + 'employees/ajax_delete_crew', {crew_id: crew_id, status: status}, function (resp) {
					if (resp.status == 'ok') {
						location.reload();
						return false;
					}
					alert('Ooops! Error!');
				}, 'json');
			}
		});
	});
	$(function () {

		var setMyColorpicker = function (elem) {
			$(elem).colpick({
				submit: 0,
				colorScheme: 'dark',
				onChange: function (hsb, hex, rgb, el, bySetColor) {
					$(el).css('background-color', '#' + hex);
					if (!bySetColor) {
						$(el).val('#' + hex);
						for (var i = 0, len = scopes.data.items.length; i < len; i++) {
							var curItem = scopes.data.items[i];
						}
					}
				}
			}).keyup(function () {
				$(this).colpickSetColor(this.value);
			});
			$('.mycolorpicker').each(function () {
				var current_color = $(this).val();
				var current_color_short = current_color.replace(/^#/, '');
				$(this).colpickSetColor(current_color_short);
			});
		};
		window.setMyColorpicker = setMyColorpicker;
		setTimeout(function () {
			setMyColorpicker($('.mycolorpicker'));
		}, 200);
	});
</script>
<?php $this->load->view('includes/footer'); ?>
