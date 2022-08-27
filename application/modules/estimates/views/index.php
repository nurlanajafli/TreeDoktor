<?php $this->load->view('includes/header'); ?>
<!-- Title -->
<section class="scrollable p-sides-15">
	<ul class="breadcrumb no-border no-radius b-b b-light pull-in">
		<li><a href="<?php echo base_url(); ?>"><i class="fa fa-home"></i> Home</a></li>
		<li class="active">Estimates</li>
	</ul>
	<!-- Estimates header -->
	<?php $symbols = array(' - ', ' ','-'); ?>
	<section class="panel panel-default" style="min-height: calc(100% - 64px);">
		<header class="panel-heading">Estimates (<?php echo $estimateCount; ?>)
			<div class="btn-group  pull-right" style="margin-top: -8px; ">
				<!-- Search Estimates -->
				<?php $this->load->view('includes/estimateSearch'); ?>
			</div>
			<div class="btn-group pull-right" style="margin-top: -8px;">
				<button class="btn btn-info dropdown-toggle"  data-toggle="dropdown">
					<?php echo isset($statuses[0]) ? $statuses[0]->est_status_name : NULL; ?> <?php echo isset($estimates[mb_strtolower(str_replace($symbols, '_', $statuses[0]->est_status_name)) . '_count']) && $estimates[mb_strtolower(str_replace($symbols, '_', $statuses[0]->est_status_name)) . '_count'] ? $estimates[mb_strtolower(str_replace($symbols, '_', $statuses[0]->est_status_name)) . '_count'] : 0; ?><span class="caret"
					                                                                      style="margin-left:5px;"></span>
				</button>
				<ul class="dropdown-menu" id="est_status" data-type="estimates">
						<li><a href="#tab0" data-toggle="tab"
							   style="padding-right: 6px;padding-left: 6px;">Follow Up  <span
							class="badge<?php if ($working_count) : ?> bg-info<?php endif; ?>"><?php echo $working_count; ?></span></a>
						</li>
						<?php foreach($statuses as $key=>$status) : ?>
						<li><a href="#tab<?php echo $key + 1; ?>" data-toggle="tab" data-statusname="<?php echo $status->est_status_name; ?>"
							   style="padding-right: 6px;padding-left: 6px;"><?php echo $status->est_status_name; ?> <span class="badge<?php if (isset($estimates[mb_strtolower(str_replace($symbols, '_', $status->est_status_name)) . '_count']) && $estimates[mb_strtolower(str_replace($symbols, '_', $status->est_status_name)) . '_count']) : ?> bg-info<?php endif; ?>">
									<?php echo isset($estimates[mb_strtolower(str_replace($symbols, '_', $status->est_status_name)) . '_count']) ? ($estimates[mb_strtolower(str_replace($symbols, '_', $status->est_status_name)) . '_count']) : 0; ?>
								</span>
							</a>
						</li>
						<?php endforeach; ?>
						<!--<li>
							<a href="#tab<?php /*echo count($statuses) + 1; //countOk  */?>" data-toggle="tab">
								QA <span class="badge<?php /*if ($qa_count) : */?> bg-info<?php /*endif; */?>"><?php /*echo $qa_count; */?></span>
							</a>
						</li>-->
						
					</ul>
			</div>
			<div class="clear"></div>
		</header>
		<div class="tabbable"> <!-- Only required for left/right tabs -->

			<div class="tab-content">
				<div class="tab-pane" id="tab0" style="overflow: hidden;">
					<!-- Display Working Estimates -->
					<?php $this->load->view('index_working_estimates.php'); ?>
					<!-- /Display Working Estimates -->
				</div>
				<?php $symbols = array(' - ', ' '); ?>
				<?php foreach($statuses as $key=>$status) : //var_dump($estimates); die;?>				
					<div class="tab-pane" id="tab<?php echo $key + 1;?>">
						<!-- Display New Estimates -->
						<?php $current_status['current_status'] = $status; ?>
						<?php $this->load->view('index_tab_estimate.php', $current_status); ?>
						<!-- /Display New Estimates -->
					</div>
				<?php endforeach; ?>
				<div class="tab-pane" id="tab<?php echo count($statuses) + 1; //countOk ?>">
					<!-- Display Declined Estimates -->
					<?php $this->load->view('index_qa_estimates'); ?>
					<!-- /Display Declined Estimates -->
				</div>
			</div>
		</div>
	</section>
</section>
<script>
	var sorts = {};
	var types = <?php echo json_encode($types); ?>;
	$(document).ready(function () {
		$('#est_status li a').click(function () {
			$('#est_status li.active').removeClass('active');
			var text = $(this).not('span').text();
			$('#est_status').prev('.dropdown-toggle').html(text + '<span class="caret" style="margin-left:5px;"></span>');
			//$('#statusMapper').attr('href', baseUrl + 'workorders/workorders_mapper/' + $(this).data('statusname'));
		});
		$(document).on('click', '.sort', function () {
			var tab = $(this).parents().find('.tab-pane:visible').attr('id');
			var page = $('#' + tab + ' .pagination ul li.active a').text();
			if (!page)
				page = 1;
			$('.sort').not(this).attr('data-type', 'ASC');
			$('.sort').not(this).children().removeClass('asc');
			$('.sort').not(this).children().addClass('desc');
			var obj = $(this);
			var type = $(this).attr('data-type');
			var field = $(this).data('field');
			var status = $(this).data('status');
			sorts[status] = {field: field, type: type};
			if (type == 'ASC') {
				$(this).children().removeClass('desc');
				$(this).children().addClass('asc');
				$(this).attr('data-type', 'DESC');
			}
			if (type == 'DESC') {
				$(this).children().removeClass('asc');
				$(this).children().addClass('desc');
				$(this).attr('data-type', 'ASC');
			}
			$.post(baseUrl + 'estimates/ajax_sort_estimates', {page: page, status: status, field: field, order: type}, function (resp) {
				if (resp.status == 'ok') {
					$(obj).parent().parent().parent().next().html(resp.html);
				}
				else {
					alert('Error');
				}
				return false;
			}, 'json');
			return false;
		});
		$(document).on('click', 'li.page a', function () {
			var ids = {};
			for(var i = 0; i < types.length; i++) {
                ids['tab' + (i + 1)] = types[i];
            }
			var id = $(this).parents().find('.tab-pane.active').attr('id');
			if (ids[id] == undefined)
				return false;
			var segments = $(this).attr('href').split('/');
			var pageNumber = segments[segments.length - 1];
			var type = ids[id];
			$.post(baseUrl + 'estimates/paginationEstimates/' + pageNumber, {sorts: sorts, type: type, page: pageNumber}, function (resp) {
				if (resp.status == 'ok') {
					$('#' + id).html(resp.html);
				}
			}, 'json');
			return false;
		});
		if (!location.hash)
			$('[data-type="estimates"] [href="#tab1"]').click();
	});
</script>
<!-- /Declined Estimates ends -->
<?php $this->load->view('includes/footer'); ?>
