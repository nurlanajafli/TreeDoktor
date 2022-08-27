<?php $this->load->view('includes/header'); ?>
<link rel="stylesheet" href="<?php echo base_url('assets/css/colpick.css'); ?>"/>
<script src="<?php echo base_url('assets/js/colpick.js'); ?>"></script>
<script src="<?php echo base_url('assets/vendors/notebook/js/sortable/jquery.sortable.js'); ?>"></script>
<section class="scrollable p-sides-15">
	<ul class="breadcrumb no-border no-radius b-b b-light pull-in">
		<li><a href="<?php echo base_url(); ?>"><i class="fa fa-home"></i> Home</a></li>
		<li class="active">Task Categories</li>
	</ul>
	<section class="panel panel-default">
		<header class="panel-heading">Task Categories
			<a href="#category-" class="btn btn-xs btn-success pull-right" role="button" data-toggle="modal"
			   data-backdrop="static" data-keyboard="false"><i class="fa fa-plus"></i></a>
		</header>
		<table class="table">
			<thead>
			<tr>
				<th >Category Name</th>
				<th style="text-align:center">Category Sticker Color</th>
				<th width="100px">Action</th>
			</tr>
			</thead>
			<tbody class="sortable">
			<?php foreach ($categories as $key=>$category) : ?>
				<tr <?php if (!$category['category_active']) : ?> style="text-decoration: line-through;"<?php endif; ?> data-id="<?= $category['category_id'] ?>">
					<td><?php echo $category['category_name']; ?></td>
					<td class="text-center"><?php echo $category['category_color'] ? '<span style="border: 1px solid #000;display: inline-block;width: 18px;background: ' . $category['category_color'] . '">&nbsp;</span>' : 'Category Color'; ?></td>
					<td>
						<div id="category-<?php echo $category['category_id']; ?>" class="modal fade" tabindex="-1" role="dialog"
						     aria-labelledby="myModalLabel" aria-hidden="true">
							<div class="modal-dialog">
								<div class="modal-content panel panel-default p-n">
									<header class="panel-heading">Edit Category <?php echo $category['category_name']; ?></header>
									<div class="modal-body">
										<div class="form-horizontal">
											<div class="control-group">
												<label class="control-label">Name</label>

												<div class="controls">
													<input class="category_name form-control" type="text"
													       value="<?php echo $category['category_name']; ?>"
													       placeholder="Category Name" style="background-color: #fff;">
												</div>
											</div>
											<div class="control-group">
												<label class="control-label">Map Sticker Color</label>
												<div class="controls">
													<input class="mycolorpicker form-control category_color" type="text"
													       value="<?php echo $category['category_color']; ?>"
													       placeholder="Category Color" style="background-color: #fff;">
												</div>
											</div>
										</div>
									</div>
									<div class="modal-footer">
										<button class="btn btn-success" data-save-category="<?php echo $category['category_id']; ?>">
											<span class="btntext">Save</span>
											<img src="<?php echo base_url(); ?>assets/img/ajax-loader.gif"
											     style="display: none;width: 32px;" class="preloader">
										</button>
										<button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
									</div>
								</div>
							</div>
						</div>

						<a class="btn btn-default btn-xs" href="#category-<?php echo $category['category_id']; ?>" role="button"
						   data-toggle="modal" data-backdrop="static" data-keyboard="false"><i class="fa fa-pencil"></i></a>
						
						<a class="btn btn-xs btn-info deleteCategory" data-delete_id="<?php echo $category['category_id']; ?>" data-active="<?php echo $category['category_active']?>"><i
								class="fa <?php if ($category['category_active']) : ?>fa-eye-slash<?php else : ?>fa-eye<?php endif; ?>"></i></a>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	</section>
</section>
<div id="category-" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content panel panel-default p-n">
			<header class="panel-heading">Create Category</header>
			<div class="modal-body">
				<div class="form-horizontal">
					<div class="control-group">
						<label class="control-label">Name</label>

						<div class="controls">
							<input class="category_name form-control" type="text" value="" placeholder="Category Name">
						</div>
					</div>
					<div class="control-group">
						<label class="control-label">Map Sticker Color</label>
						<div class="controls">
							<input class="mycolorpicker form-control category_color" type="text"
							       value=""
							       placeholder="Category Color" style="background-color: #fff;">
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button class="btn btn-success" data-save-category="">
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
	$(document).ready(function () {
		$('[data-save-category]').click(function () {
			var category_id = $(this).data('save-category');
			$(this).attr('disabled', 'disabled');
			$('#category-' + category_id + ' .modal-footer .btntext').hide();
			$('#category-' + category_id + ' .modal-footer .preloader').show();
			$('#category-' + category_id + ' .category_name').parents('.control-group').removeClass('error');
			var category_name = $('#category-' + category_id).find('.category_name').val();
			var category_color = $('#category-' + category_id).find('.category_color').val();
			if (!category_name) {
				$('#category-' + category_id + ' .category_name').parents('.control-group').addClass('error');
				$('#category-' + category_id + ' .modal-footer .btntext').show();
				$('#category-' + category_id + ' .modal-footer .preloader').hide();
				$(this).removeAttr('disabled');
				return false;
			}
			$.post(baseUrl + 'tasks/ajax_save_category', {category_id : category_id, category_name : category_name, category_color : category_color}, function (resp) {
				if (resp.status == 'ok')
					location.reload();
				return false;
			}, 'json');
			return false;
		});
		$('.deleteCategory').click(function () {
			var category_id = $(this).data('delete_id');
			var status = $(this).data('active');
			if (confirm('Are you sure?')) {
				if(status == 0)
					status = 1;
				else
					status = 0;
				$.post(baseUrl + 'tasks/ajax_delete_category', {category_id : category_id, status: status}, function (resp) {
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
		setMyColorpicker($('.mycolorpicker'));
	});


	$('.sortable').sortable().bind('sortupdate', function () {
			var arr = [];
			$.each($('.sortable').children(), function (key, val) {
				priority = key + 1;
				arr[$(val).data('id')] = priority;
			});

        $.ajax({
            global: false,
            method: "POST",
            data: {data: arr},
            url: baseUrl + "tasks/ajax_sort_client_task_categories",
            dataType:'json',
            success: function(response){
                if (response.status == 'error') {
                    alert('Ooops! Error...');
                }
                return false;
            }
        });
    });


</script>
<?php $this->load->view('includes/footer'); ?>
