<?php $this->load->view('includes/header'); ?>
<section class="scrollable p-sides-15">
	<ul class="breadcrumb no-border no-radius b-b b-light pull-in">
		<li><a href="<?php echo base_url(); ?>"><i class="fa fa-home"></i> Home</a></li>
		<li class="active">Voice Templates</li>
	</ul>
	<section class="panel panel-default">
		<header class="panel-heading">Voice Templates
			<a href="#template-" class="btn btn-xs btn-success pull-right" role="button" data-toggle="modal"
			   data-backdrop="static"  data-keyboard="false"><i class="fa fa-plus"></i></a>
		</header>
		<table class="table table-hover">
			<thead>
			<tr>
				<th width="300px">Title</th>
				<th>Text</th>
				<th width="120px">Action</th>
			</tr>
			</thead>
			<tbody>
			<?php if(isset($voices) && !empty($voices)) : ?>
			<?php foreach ($voices as $key=>$voice) : //var_dump($letter); die;?>
				<tr>
					<td><?php echo $voice->voice_name; ?></td>
					<td><?php echo $voice->voice_resp; ?>...</td>
					<td>
						<div id="template-<?php echo $voice->voice_id; ?>" class="modal fade" tabindex="-1" role="dialog"
						     aria-labelledby="myModalLabel" aria-hidden="true">
							<div class="modal-dialog" style="width: 900px;">
								<div class="modal-content panel panel-default p-n">
									<header class="panel-heading">Edit Template <?php echo $voice->voice_name; ?></header>
									<div class="modal-body">
										<div class="form-horizontal">
											<div class="control-group">
												<label class="control-label">Template Name</label>

												<div class="controls">
													<input class="template_title form-control" type="text"
													       value="<?php echo $voice->voice_name; ?>"
													       placeholder="Template Name" style="background-color: #fff;">
												</div>
											</div>
											<div class="control-group">
												<label class="control-label">Template Text</label>
												<div class="controls">
													<textarea  placeholder="Template Text" class="form-control template_text"><?php echo $voice->voice_resp; ?></textarea>
												</div>
											</div>
										</div>
									</div>
									<div class="modal-footer">
										<button class="btn btn-success" data-save-template="<?php echo $voice->voice_id; ?>">
											<span class="btntext">Save</span>
											<img src="<?php echo base_url(); ?>assets/img/ajax-loader.gif"
											     style="display: none;width: 32px;" class="preloader">
										</button>
										<button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
									</div>
								</div>
							</div>
						</div>

						<a class="btn btn-default btn-xs" href="#template-<?php echo $voice->voice_id; ?>" role="button"
						   data-toggle="modal" data-backdrop="static" data-keyboard="false"><i class="fa fa-pencil"></i></a>
						
						<a class="btn btn-xs btn-info deleteTemplate" data-delete_id="<?php echo $voice->voice_id; ?>" >
							<i class="fa fa-trash-o"></i>
						</a>
						
					</td>
				</tr>
			<?php endforeach; ?>
			<?php else : ?>
			<tr>
				<td colspan="5" style="color:#FF0000;">No record found</td>
			</tr>
			<?php endif;  ?>
			</tbody>
		</table>
	</section>
</section>
<div id="template-" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog" style="width: 900px;">
		<div class="modal-content panel panel-default p-n">
			<header class="panel-heading">Create Template</header>
			<div class="modal-body">
				<div class="form-horizontal">
					<div class="control-group">
						<label class="control-label">Template Name</label>

						<div class="controls">
							<input class="template_title form-control" type="text"
							       value=""
							       placeholder="Template Name" style="background-color: #fff;">
						</div>
					</div>
					<div class="control-group">
						<label class="control-label">Template Text</label>
						<div class="controls">
							<textarea  class="form-control template_text" placeholder="Template Text" value=""></textarea>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button class="btn btn-success" data-save-template="">
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
	$(document).on('focusin', function(e) {
		if ($(e.target).closest(".mce-window").length) {
			e.stopImmediatePropagation();
		}
	});
	
	$(document).ready(function () {
		$('[data-save-template]').click(function () {
			var template_id = $(this).data('save-template');
			$(this).attr('disabled', 'disabled');
			$('#template-' + template_id + ' .modal-footer .btntext').hide();
			$('#template-' + template_id + ' .modal-footer .preloader').show();
			$('#template-' + template_id + ' .template_title').parents('.control-group').removeClass('error');
			var template_name = $('#template-' + template_id).find('.template_title').val();
			var template_text = $('#template-' + template_id).find('.template_text').val()
			
			if (!template_name) {
				$('#template-' + template_id + ' .template_title').parents('.control-group').addClass('error');
				$('#template-' + template_id + ' .modal-footer .btntext').show();
				$('#template-' + template_id + ' .modal-footer .preloader').hide();
				$(this).removeAttr('disabled');
				return false;
			}
			if (!template_text) {
				$('#template-' + template_id + ' .mce-tinymce.mce-container.mce-panel').addClass('error');
				$('#template-' + template_id + ' .modal-footer .btntext').show();
				$('#template-' + template_id + ' .modal-footer .preloader').hide();
				$(this).removeAttr('disabled');
				return false;
			}
			$.post(baseUrl + 'clients/ajax_save_voice', {template_id : template_id, template_name : template_name, template_text : template_text}, function (resp) {
				if (resp.status == 'ok')
					location.reload();
				return false;
			}, 'json');
			return false;
		});
		$('.deleteTemplate').click(function () {
			var template_id = $(this).data('delete_id');
			if (confirm('Are you sure?')) {
				
				$.post(baseUrl + 'clients/ajax_delete_voice', {template_id:template_id}, function (resp) {
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
