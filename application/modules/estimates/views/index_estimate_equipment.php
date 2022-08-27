<?php $this->load->view('includes/header'); ?>
<script src="<?php echo base_url('assets/vendors/notebook/js/sortable/jquery.sortable.js'); ?>"></script>
<style>.sortable-placeholder {
		min-height: 54px;
	}</style>
<section class="scrollable p-sides-15">
	<ul class="breadcrumb no-border no-radius b-b b-light pull-in">
		<li><a href="<?php echo base_url(); ?>"><i class="fa fa-home"></i> Home</a></li>
		<li class="active">Estimate Equipment</li>
	</ul>
	<section class="panel panel-default">
		<header class="panel-heading">Equipment
			<a href="#item-" class="btn btn-xs btn-success pull-right" role="button" data-toggle="modal"
			   data-backdrop="static" data-keyboard="false"><i class="fa fa-plus"></i></a>
		</header>
		<ul class="list-group gutter list-group-lg list-group-sp sortable">
			<?php foreach ($equipment as $item) : ?>
				<li data-id="<?php echo $item->eq_id; ?>" class="clear list-group-item">
					<?php $style = $item->eq_status ? '' : ' style="text-decoration: line-through;"'; ?>
					<div class="col-md-11"<?php echo $style; ?>><i
							class="fa fa-sort text-muted fa m-r-sm"></i><?php echo $item->eq_name; ?></div>
					<div class="col-md-1">

						<a class="btn btn-xs btn-default" href="#item-<?php echo $item->eq_id; ?>"
						   role="button" data-toggle="modal" data-backdrop="static" data-keyboard="false"><i
								class="fa fa-pencil"></i></a>
						<form data-type="ajax" data-url="<?php echo base_url('estimates/ajax_delete_estimate_equipment'); ?>" data-location="<?php echo current_url(); ?>" style="display: inline-block;">
							<input type="hidden" name="eq_id" value="<?php echo $item->eq_id; ?>">
							<input type="hidden" name="status" value="<?php echo $item->eq_status ? 0 : 1; ?>">
							<button class="btn btn-xs btn-info deleteItem" type="submit">
								<i class="fa <?php if ($item->eq_status) : ?>fa-eye-slash<?php else : ?>fa-eye<?php endif; ?>"></i>
							</button>
						</form>
					</div>
				</li>
			<?php endforeach; ?>
			<?php if(empty($equipment)) : ?>
				<li class="clear list-group-item"><div class="col-sm-12 text-center text-danger">No Records Found</div></li>
			<?php endif; ?>
		</ul>
		<?php foreach ($equipment as $item) : ?>
			<div id="item-<?php echo $item->eq_id; ?>" class="modal fade" tabindex="-1"
			     role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
				<div class="modal-dialog">
					<form class="modal-content panel panel-default p-n" data-type="ajax" data-url="<?php echo base_url('estimates/ajax_save_estimate_equipment'); ?>" data-location="<?php echo current_url(); ?>">
						<header class="panel-heading">Edit
							Item <?php echo $item->eq_name; ?></header>
						<div class="modal-body">
							<div class="form-horizontal">
								<div class="control-group">
									<label class="control-label">Item Name</label>

									<div class="controls">
										<input class="item_name form-control" name="eq_name" type="text"
										       value="<?php echo $item->eq_name; ?>"
										       data-toggle="tooltip" data-placement="top" title="" data-original-title=""
										       placeholder="Item Name">
										<input type="hidden" name="eq_id" value="<?php echo $item->eq_id; ?>">
									</div>
								</div>
							</div>
						</div>
						<div class="modal-footer">
							<button class="btn btn-success" type="submit">
								<span class="btntext">Save</span>
								<img src="<?php echo base_url(); ?>assets/img/ajax-loader.gif"
								     style="display: none;width: 32px;" class="preloader">
							</button>
							<button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
						</div>
					</form>
				</div>
			</div>
		<?php endforeach; ?>
	</section>
</section>
<div id="item-" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<form class="modal-content panel panel-default p-n" data-type="ajax" data-url="<?php echo base_url('estimates/ajax_save_estimate_equipment'); ?>" data-location="<?php echo current_url(); ?>">
			<header class="panel-heading">New Item</header>
			<div class="modal-body">
				<div class="form-horizontal">
					<div class="control-group">
						<label class="control-label">Name</label>

						<div class="controls">
							<input class="form-control" name="eq_name" type="text" value="" placeholder="Item Name" data-toggle="tooltip" data-placement="top" title="" data-original-title="">
						</div>
					</div>
					
				</div>
			</div>
			<div class="modal-footer">
				<button class="btn btn-success" data-save-status="">
					<span class="btntext">Save</span>
					<img src="<?php echo base_url(); ?>assets/img/ajax-loader.gif" style="display: none;width: 32px;"
					     class="preloader">
				</button>
				<button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
			</div>
		</form>
	</div>
</div>
<script>
	
	$(document).ready(function () {
		$('.sortable').sortable().bind('sortupdate', function () {
			var arr = [];
			$.each($('.sortable').children(), function (key, val) {
				priority = key + 1;
				arr[$(val).data('id')] = priority;
			});
			$.post(baseUrl + 'estimates/ajax_priority_equipment', {data: arr}, function (resp) {
				if (resp.status == 'error')
					alert('Ooops! Error...');
				return false;
			}, 'json');
			return false;
		});
	});

</script>
<?php $this->load->view('includes/footer'); ?>
