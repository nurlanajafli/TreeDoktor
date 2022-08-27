<?php $this->load->view('includes/header'); ?>
<link rel="stylesheet" href="<?php echo base_url('assets/css/colpick.css'); ?>"/>
<script src="<?php echo base_url('assets/js/colpick.js'); ?>"></script>
<style>
	@media (max-width: 420px){
		.colpick.colpick_full.colpick_full_ns.colpick_dark {left:10%!important; }
	}
</style>
<section class="scrollable p-sides-15">
	<ul class="breadcrumb no-border no-radius b-b b-light pull-in">
		<li><a href="<?php echo base_url(); ?>"><i class="fa fa-home"></i> Home</a></li>
		<li class="active">Static Objects</li>
	</ul>
	<section class="panel panel-default">
		<header class="panel-heading">Static Objects
			<a class="btn btn-success btn-xs pull-right" type="button" style="margin-top: -1px;"
			   href="#object-" role="button"  data-toggle="modal" data-backdrop="static" data-keyboard="false">
				<i class="fa fa-plus"></i>
			</a>
		</header>
		
		<div id="object-" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content panel panel-default p-n">
			<header class="panel-heading">Create Object</header>
			<form>
			<div class="modal-body">
				<div class="form-horizontal">
					<div class="control-group">
						<label class="control-label">Name</label>

						<div class="controls">
							<input class="object_name form-control" type="text" value="" placeholder="Object Name">
						</div>
					</div>
					<div class="control-group">
						<label class="control-label">Description</label>

						<div class="controls">
							<textarea class="object_description form-control" type="text" value="" placeholder="Object Description"></textarea>
						</div>
					</div>
					<div class="control-group">
						<label class="control-label">Street</label>

						<div class="controls">
							<input data-autocompleate="true" data-part-address="address" class="object_street form-control" type="text" value="" placeholder="Object Street">
						</div>
					</div>
					<div class="control-group">
						<label class="control-label">City</label>

						<div class="controls">
							<input data-part-address="locality" class="object_city form-control" type="text" value="" placeholder="Object City">
						</div>
					</div>
					<div class="control-group">
						<label class="control-label">Schedule Sticker Color</label>
						<div class="controls">
							<input class="mycolorpicker form-control object_color" type="text"
							       value="#FFFFFF"
							       placeholder="Object Color" readonly style="background-color: #fff;">
							<input type="hidden" class="object_lat" data-part-address="lat">
							<input type="hidden" class="object_lon" data-part-address="lon">
						</div>
					</div>
				</div>
			</div>
			</form>
			<div class="modal-footer">
				<button class="btn btn-success" data-save-object="">
					<span class="btntext">Save</span>
					<img src="<?php echo base_url(); ?>assets/img/ajax-loader.gif" style="display: none;width: 32px;"
					     class="preloader">
				</button>
				<button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
			</div>
		</div>
	</div>
</div>

		<div class="m-bottom-10 p-sides-10 table-responsive">
			<table class="table tsble-striped m-n">
				<thead>
				<tr>
					<th>Name</th>
					<th>Decription</th>
					<th>Address</th>
					<th>City</th>
					<th>Color</th>
					<th>Action</th>
				</tr>
				</thead>
				<tbody>
				<?php
				if ($objects) {
					foreach ($objects as $key => $object):
						?>
						<tr>
							<td><?php echo $object['object_name']; ?></td>
							<td><?php echo $object['object_desc']; ?></td>
							<td><?php echo $object['object_street']; ?></td>
							<td><?php echo $object['object_city']; ?></td>
							<td><?php echo $object['object_color'] ? '<span style="border: 1px solid #000;display: inline-block;width: 18px;background: ' .  $object['object_color'] . '">&nbsp;</span>' : '-'; ?></td>
							<td>
								<div id="object-<?php echo $object['object_id']; ?>" class="modal fade" tabindex="-1" role="dialog"
						     aria-labelledby="myModalLabel" aria-hidden="true">
							<div class="modal-dialog">
								<div class="modal-content panel panel-default p-n">
									<header class="panel-heading">Edit Object <?php echo $object['object_name']; ?></header>
									<form>
									<div class="modal-body">
										<div class="form-horizontal">
											<div class="control-group">
												<label class="control-label">Name</label>

												<div class="controls">
													<input class="object_name form-control" type="text"
													       value="<?php echo $object['object_name']; ?>"
													       placeholder="Object Name" style="background-color: #fff;">
												</div>
											</div>
											<div class="control-group">
												<label class="control-label">Description</label>

												<div class="controls">
													<textarea class="object_description form-control" type="text"
													  placeholder="Object Description" style="background-color: #fff;"><?php echo $object['object_desc']; ?></textarea>
												</div>
											</div>
											<div class="control-group">
												<label class="control-label">Address</label>

												<div class="controls">
													<input  data-autocompleate="true" data-part-address="address" class="object_street form-control" type="text" 
													       value="<?php echo $object['object_street']; ?>"
													       placeholder="Object Street" style="background-color: #fff;">
												</div>
											</div>
											<div class="control-group">
												<label class="control-label">City</label>

												<div class="controls">
													<input data-part-address="locality" class="object_city form-control" type="text" 
													       value="<?php echo $object['object_city']; ?>"
													       placeholder="Object City" style="background-color: #fff;">
												</div>
											</div>
											
											<div class="control-group">
												<label class="control-label">Sticker Color</label>
												<div class="controls">
													<input class="mycolorpicker form-control object_color" type="text"
													       value="<?php echo $object['object_color']; ?>"
													       placeholder="Object Color" readonly style="background-color: #fff;">
													<input type="hidden" class="object_lat" data-part-address="lat" value="<?php echo $object['object_latitude']; ?>">
													<input type="hidden" class="object_lon" data-part-address="lon" value="<?php echo $object['object_longitude']; ?>">
												</div>
											</div>
										</div>
									</div>
									</form>
									<div class="modal-footer">
										<button class="btn btn-success" data-save-object="<?php echo $object['object_id']; ?>">
											<span class="btntext">Save</span>
											<img src="<?php echo base_url(); ?>assets/img/ajax-loader.gif"
											     style="display: none;width: 32px;" class="preloader">
										</button>
										<button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
									</div>
								</div>
							</div>
						</div>
								<a class="btn btn-xs btn-default" href="#object-<?php echo $object['object_id']; ?>" role="button" 
									data-toggle="modal" data-backdrop="static" data-keyboard="false"><i class="fa fa-pencil"></i></a>
									&nbsp;
								<a class="btn btn-xs btn-danger deleteObject"
								   data-delete-id="<?php echo $object['object_id']; ?>">
									<i class="fa fa-trash-o"></i>
								</a>
							</td>
						</tr>
					<?php
					endforeach;
				} else {
					?>
					<tr>
						<td colspan="5"><?php echo "No records found"; ?></td>
					</tr>
				<?php } ?>
				</tbody>
			</table>
		</div>

		</div>
		</div>
		<script type="text/javascript">

	
	var setMyColorpicker = function (elem) {
		$(elem).colpick({
			submit: 0,
			colorScheme: 'dark',
			onChange: function (hsb, hex, rgb, el, bySetColor) {
				$(el).css('background-color', '#' + hex);
				if (!bySetColor) {
					$(el).val('#' + hex);
					//for (var i = 0, len = scopes.data.items.length; i < len; i++) {
					//	var curItem = scopes.data.items[i];
					//}
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
	$(document).ready(function(){
		//initialize();
		setMyColorpicker($('.mycolorpicker'));
		
		$(document).on("click", '[data-save-object]', function(){
			
			var id = $(this).data('save-object');
			var name = $('#object-' + id + ' .object_name').val();
			var desc = $('#object-' + id + ' .object_description').val();
			var street = $('#object-' + id + ' .object_street').val();
			var city = $('#object-' + id + ' .object_city').val();
			var lat = $('#object-' + id + ' .object_lat').val();
			var lon = $('#object-' + id + ' .object_lon').val();
			var color = $('#object-' + id + ' .object_color').val();
			
			$(this).attr('disabled', 'disabled');
			$('#object-' + id + ' .modal-footer .btntext').hide();
			$('#object-' + id + ' .modal-footer .preloader').show();
			$('#object-' + id + ' .object_name').parents('.control-group').removeClass('error');
			
			if (!name) {
				$('#object-' + id + ' .object_name').parents('.control-group').addClass('error');
				$('#object-' + id + ' .modal-footer .btntext').show();
				$('#object-' + id + ' .modal-footer .preloader').hide();
				$(this).removeAttr('disabled');
				return false;
			}
			$.post(baseUrl + 'schedule/ajax_save_object', {id : id, name : name, desc : desc, street:street, city:city, lat:lat, lon:lon, color : color}, function (resp) {
				if (resp.status == 'ok')
					location.reload();
				return false;
			}, 'json');
			return false;
		});
		$('.deleteObject').click(function () {
			var id = $(this).data('delete-id');
			if (confirm('Are you sure?')) {
				$.post(baseUrl + 'schedule/ajax_delete_object', {id: id}, function (resp) {
					if (resp.status == 'ok') {
						location.reload();
						return false;
					}
					alert('Ooops! Error!');
				}, 'json');
			}
		});
	});
</script>
		<?php $this->load->view('includes/footer'); ?>
