    <?php $this->load->view('includes/header'); ?>
<link rel="stylesheet" href="<?php echo base_url('assets/css/colpick.css'); ?>"/>
<script src="<?php echo base_url('assets/js/colpick.js'); ?>"></script>
<script src="<?php echo base_url('assets/vendors/notebook/js/sortable/jquery.sortable.js'); ?>"></script>
<style>
	@media (max-width: 420px){
		.colpick.colpick_full.colpick_full_ns.colpick_dark {left:10%!important; }
	}
</style>

<section class="scrollable p-sides-15">
	<ul class="breadcrumb no-border no-radius b-b b-light pull-in">
		<li><a href="<?php echo base_url(); ?>"><i class="fa fa-home"></i> Home</a></li>
		<li class="active">Status</li>
	</ul>
	<section class="panel panel-default">
		<header class="panel-heading">Status
			<a href="#status-modal" class="btn btn-xs btn-success pull-right" role="button" data-toggle="modal"
			   data-backdrop="static" data-keyboard="false"><i class="fa fa-plus"></i></a>
		</header>
		<table class="table table-hover">
			<thead>
			<tr>
				<th>Status Name</th>
				<th width="170px">Status Sticker Color</th>
				<th class="text-center">Confirmed by user (default)</th>
				<th class="text-center">Finished by field</th>
				<th class="text-center">Confirmed online</th>
				<th class="text-center">Deleted Invoice</th>
				<th class="text-center">Finished</th>
				<th width="80px">Action</th>
			</tr>
			</thead>
			<tbody class="sortable">
			<?php foreach ($statuses as $key=>$status) : ?>
				<tr<?php if (!$status['wo_status_active']) : ?> style="text-decoration: line-through;"<?php endif; ?> data-wo_status_id="<?php echo $status['wo_status_id']; ?>">
					<td><?php echo $status['wo_status_name']; ?></td>
					
					<td class="text-center">
                        <?php if(!$status['wo_status_use_team_color'] && !$status['wo_status_use_estimator_color']): ?>
                            <span style="border: 1px solid #000;display: inline-block;width: 18px;background: <?php echo $status['wo_status_color']; ?>">&nbsp;</span>
                        <?php else: ?>
                            <?php if($status['wo_status_use_team_color']): ?>
                            Team Color
                            <?php elseif($status['wo_status_use_estimator_color']): ?>
                            Estimator Color
                            <?php endif; ?>
                        <?php endif; ?>
                    </td>
					<td class="text-center"><i class="fa fa-<?php if($status['is_default']): ?>check text-success<?php else: ?>times text-danger <?php endif; ?>"></i></td>
					<td class="text-center"><i class="fa fa-<?php if($status['is_finished_by_field']): ?>check text-success<?php else: ?>times text-danger <?php endif; ?>"></i></td>
					<td class="text-center"><i class="fa fa-<?php if($status['is_confirm_by_client']): ?>check text-success<?php else: ?>times text-danger <?php endif; ?>"></i></td>
					<td class="text-center"><i class="fa fa-<?php if($status['is_delete_invoice']): ?>check text-success<?php else: ?>times text-danger <?php endif; ?>"></i></td>
					<td class="text-center"><i class="fa fa-<?php if($status['is_finished']): ?>check text-success<?php else: ?>times text-danger <?php endif; ?>"></i></td>
					<td>
						<a class="btn btn-default btn-xs" href="#status-modal" data-id="<?php echo $status['wo_status_id']; ?>" role="button"
						   data-toggle="modal" data-backdrop="static" data-keyboard="false"><i class="fa fa-pencil"></i></a>
						<?php if(!$status['is_protected']) : ?>
						<a class="btn btn-xs btn-info deleteStatus" data-delete_id="<?php echo $status['wo_status_id']; ?>" data-active="<?php echo $status['wo_status_active']?>"><i
								class="fa <?php if ($status['wo_status_active']) : ?>fa-eye-slash<?php else : ?>fa-eye<?php endif; ?>"></i></a>
						<?php endif; ?>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	</section>
</section>


<?php $this->load->view('modals/workorders_status_modal'); ?>


<script>
	window.workorder_statuses =  <?php echo json_encode(count($statuses)?$statuses:[]); ?>;

	function changeStatusesPriority() {
		var arr = [];
		$.each($('.sortable tr'), function (key, val) {
			priority = key + 1;
			arr[key] = {id: $(val).data('wo_status_id'), priority: priority};
		});
		$.post(baseUrl + 'workorders/status/ajax_priority_statuses', {data: arr}, function (resp) {
			if (resp.status == 'error')
				alert('Ooops! Error...');
			return false;
		}, 'json');
	}
	var setMyColorpicker = function (elem) {
		$(elem).colpick({
			submit: 0,
			colorScheme: 'dark',
			onChange: function (hsb, hex, rgb, el, bySetColor) {
				$(el).css('background-color', '#' + hex);
				if (!bySetColor) {
					$(el).val('#' + hex);
				}
			}
		}).keyup(function () {
			$(this).colpickSetColor(this.value);
		});

		$('.mycolorpicker').each(function () {
			var current_color = $(this).val();
			if(!current_color)
				$(this).colpickSetColor('#ffffff');
			else
			{
				var current_color_short = current_color.replace(/^#/, '');
				$(this).colpickSetColor(current_color_short);
			}
		});
	};
	$(document).ready(function () {

		$('.deleteStatus').click(function () {
			var status_id = $(this).data('delete_id');
			var status = $(this).data('active');
			if (confirm('Are you sure?')) {
				if(status == 0)
					status = 1;
				else
					status = 0;
				$.post(baseUrl + 'workorders/status/ajax_delete_status', {status_id: status_id, status: status}, function (resp) {
					if (resp.status == 'ok') {
						location.reload();
						return false;
					}
					alert('Ooops! Error!');
				}, 'json');
			}
		});
		$('.mycolorpicker').each(function () {
			var current_color = $(this).val();
			var current_color_short = current_color.replace(/^#/, '');
			$(this).colpickSetColor(current_color_short);
		});
	});

</script>
<script src="<?php echo base_url(); ?>assets/js/modules/workorders/workorders_status.js?v=<?php echo config_item('workorders_status.js'); ?>"></script>
<?php $this->load->view('includes/footer'); ?>
