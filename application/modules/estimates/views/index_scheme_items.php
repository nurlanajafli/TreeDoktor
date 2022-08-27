<?php $this->load->view('includes/header'); ?>

<section class="scrollable p-sides-15">
	<ul class="breadcrumb no-border no-radius b-b b-light pull-in">
		<li><a href="<?php echo base_url(); ?>"><i class="fa fa-home"></i> Home</a></li>
		<li class="active">Estimate Scheme Items</li>
	</ul>
	<section class="panel panel-default">
		<header class="panel-heading">Estimate Scheme Items
		<span class="btn btn-xs btn-primary est-file pull-right">
					<input type="file" name="estFile" id="fileToUploadEstimate" class="est-upload">
				<i class="fa fa-plus"></i>
				</span>
			 
		</header>
		
		<div class="m-bottom-10 p-sides-10">
			<table class="table tsble-striped m-n">
				<thead>
				<tr>
					<th>#</th>
					<th>Item Icon</th>
					<th width="100px">Action</th>
				</tr>
				</thead>
				<tbody>
				<?php

                $icons = [];
				$icons = bucketScanDir('uploads/scheme_items/');
				sort($icons);
				
				if ($icons) {
					foreach ($icons as $key => $icon):
						?>
						<tr>
							<td><?php echo $key+1; ?></td>
							<td><img style="max-width:200px;" src="<?php echo base_url('uploads/scheme_items/' . $icon); ?>"/></td>
							<td>
								<a class="btn btn-xs btn-danger deleteIcon"
								   data-delete-item="<?php echo $icon; ?>">
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

    </section>
</section>
		<script type="text/javascript">
	
	$(document).ready(function(){
		
	$(document).on('change', '#fileToUploadEstimate', function () {
		if (!$('#fileToUploadEstimate').parent().is('.disabled'))
			ajaxFileUploadEstimate();
		return false;
	});
		$(document).on('click', '.deleteIcon', function () {
			var name = $(this).data('delete-item'); 
			if (confirm('Are you sure?')) {
				$.post(baseUrl + 'estimates/ajax_delete_scheme_icon', {name: name}, function (resp) {
					if (resp.status == 'ok') {
						location.reload();
						return false;
					}
					alert('Ooops! Error!');
				}, 'json');
			}
		});
	});
	function ajaxFileUploadEstimate() {
		$('#preloaderEstimate').show();
		$('#fileToUploadEstimate').parent().addClass('disabled');
		var path = "scheme_items/";
		
		/*if($(".pdfFile").prop("checked"))
		{
			suffix = 1;
			checked = 'checked';
		}*/
		//starting setting some animation when the ajax starts and completes
		$.ajaxFileUpload
		(
			{
				url: baseUrl + 'estimates/ajax_save_file_item/',
				secureuri: false,
				fileElementId: 'fileToUploadEstimate',
				dataType: 'json',
				data: {path: path},
				success: function (data, status) {
					$('#preloaderEstimate').hide();
					$('#fileToUploadEstimate').parent().removeClass('disabled');
					if (data.status == 'error')
						alert('Error');
					else {
						num = 1;
						if($('table tr').length)
							num = $('table tr').length;
						$('table tbody').append('<tr><td>'+ num +'</td><td><img style="max-width:200px;" src="' + baseUrl + data.filepath + '"></td><td><a class="btn btn-xs btn-danger deleteIcon" data-delete-item="'+ data.filename +'"><i class="fa fa-trash-o"></i></a></td></tr>');
					}
					$('#fileToUploadEstimate').removeAttr('disabled');
				},
				
			}
		)

		return false;

	}
</script>
		<?php $this->load->view('includes/footer'); ?>
