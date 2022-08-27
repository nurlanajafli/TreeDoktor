<?php $this->load->view('includes/header'); ?>
<section class="scrollable p-sides-15">
	<ul class="breadcrumb no-border no-radius b-b b-light pull-in">
		<li><a href="<?php echo base_url(); ?>"><i class="fa fa-home"></i> Home</a></li>
		<li class="active">Email Templates</li>
	</ul>
	<section class="panel panel-default">
		<header class="panel-heading">Email Templates
			<a href="#template-" class="btn btn-xs btn-success pull-right" role="button" data-toggle="modal"
			   data-backdrop="static" onclick="Common.initTinyMCE('template_text', {variables:true})"  data-keyboard="false"><i class="fa fa-plus"></i></a>
		</header>
		<div class="table-responsive">
			<table class="table table-hover">
				<thead>
				<tr>
					<th width="300px">Title</th>
					<th>Text</th>
                    <th width="80px">Action</th>
				</tr>
				</thead>
				<tbody>
				<?php if(isset($letters) && !empty($letters)) : ?>
				<?php foreach ($letters as $key=>$letter) : //var_dump($letter); die;?>
					<tr<?php if($letter['email_system_template']) : ?> class="bg-light"<?php endif; ?>>
						<td><?php echo $letter['email_template_title']; ?></td>
                        <td><?php echo mb_substr(strip_tags(preg_replace(array('!<style.*?>.*?</style>!is', '/&#?[a-z0-9]+;/i'), '', $letter['email_template_text'])), 0, 500); ?>...</td>
                        <td>
							<div id="template-<?php echo $letter['email_template_id']; ?>" class="modal fade" tabindex="-1" role="dialog"
								 aria-labelledby="myModalLabel" aria-hidden="true">
								<div class="modal-dialog" style="max-width: 900px;">
									<div class="modal-content panel panel-default p-n">
										<header class="panel-heading">Edit Template <?php echo $letter['email_template_title']; ?></header>
										<div class="modal-body">
											<div class="form-horizontal">
												<?php /*
												<div class="control-group">
													<label class="control-label">[DOCUMENTS] - for list of user documents</label>
												</div>
												*/ ?>
												<div class="control-group">
													<label class="control-label">Template Subject</label>

													<div class="controls">
														<input class="template_title form-control" type="text"
															   value="<?php echo $letter['email_template_title']; ?>"
															   placeholder="Template Subject" style="background-color: #fff;">
													</div>
												</div>
												<div class="control-group">
													<label class="checkbox pull-left">
														<input type="checkbox" name="news_templates" <?php if($letter['email_news_templates'] == 1) : ?>checked="checked"<?php endif;?> class="news_templates"> News Template
													</label>
													<div class="clear"></div>
												</div>
												<?php /*
												<div class="control-group">
													<label class="checkbox pull-left">
														<input type="checkbox" name="email_user_notification" <?php if($letter['email_news_templates'] == 2) : ?>checked="checked"<?php endif;?> class="email_user_notification"> User Notification
													</label>
													<div class="clear"></div>
												</div>
												*/ ?>
												<div class="control-group">
													<label class="control-label">Template Text</label>
													<div class="controls">
														<textarea  placeholder="Template Text" id="template_text_<?php echo $letter['email_template_id']; ?>" class="form-control"><?php echo $letter['email_template_text']; ?></textarea>
													</div>
												</div>
											</div>
										</div>
										<div class="modal-footer">
											<button class="btn btn-success" data-save-template="<?php echo $letter['email_template_id']; ?>">
												<span class="btntext">Save</span>
												<img src="<?php echo base_url(); ?>assets/img/ajax-loader.gif"
													 style="display: none;width: 32px;" class="preloader">
											</button>
											<button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
										</div>
									</div>
								</div>
							</div>

							<a class="btn btn-default btn-xs" href="#template-<?php echo $letter['email_template_id']; ?>" role="button"
							   data-toggle="modal" onclick="Common.initTinyMCE('template_text_<?php echo $letter['email_template_id']; ?>', {variables:true})" data-backdrop="static" data-keyboard="false"><i class="fa fa-pencil"></i></a>
							<?php if($letter['email_template_id'] && !$letter['email_system_template']) : ?>
							<a class="btn btn-xs btn-info deleteTemplate" data-delete_id="<?php echo $letter['email_template_id']; ?>" >
								<i class="fa fa-trash-o"></i>
							</a>
							<?php endif; ?>
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
		</div>
	</section>
</section>
<div id="template-" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog" style="max-width: 900px;">
		<div class="modal-content panel panel-default p-n">
			<header class="panel-heading">Create Template</header>
			<div class="modal-body">
				<div class="form-horizontal">
					<?php /*
					<div class="control-group">
						<label class="control-label">[DOCUMENTS] - for list of user documents</label>
					</div>
					*/ ?>
					<div class="control-group">
						<label class="control-label">Template Subject</label>

						<div class="controls">
							<input class="template_title form-control" type="text"
							       value=""
							       placeholder="Template Subject" style="background-color: #fff;">
						</div>
					</div>
					<div class="control-group">
						<label class="checkbox pull-left">
							<input type="checkbox" name="news_templates" class="news_templates"> News Template
						</label>
						<div class="clear"></div>
					</div>
					<?php /*
					<div class="control-group">
						<label class="checkbox pull-left">
							<input type="checkbox" name="email_user_notification" class="email_user_notification"> User Notification
						</label>
						<div class="clear"></div>
					</div>
					*/ ?>
                    <div class="control-group">
                        <label class="control-label">Template Text</label>
                        <div class="controls">
                            <textarea id="template_text" class="form-control" placeholder="Template Text" value=""></textarea>
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
<script src="<?php echo base_url(); ?>assets/vendors/notebook/js/tinymce/tinymce.min.js"></script>
<script>

    $(document).on('focusin', function(e) {
		if ($(e.target).closest(".mce-window").length) {
			e.stopImmediatePropagation();
		}
	});

    // Prevent Bootstrap dialog from blocking focusin
    /*
    $(document).on('focusin', function(e) {
        if ($(e.target).closest(".tox-tinymce, .tox-tinymce-aux, .moxman-window, .tam-assetmanager-root").length) {
            e.stopImmediatePropagation();
        }
    });
    */


    $(document).ready(function () {
		$('[data-save-template]').click(function () {
			var template_id = $(this).data('save-template');
			$(this).attr('disabled', 'disabled');
			$('#template-' + template_id + ' .modal-footer .btntext').hide();
			$('#template-' + template_id + ' .modal-footer .preloader').show();
			$('#template-' + template_id + ' .template_title').parents('.control-group').removeClass('error');
			var template_name = $('#template-' + template_id).find('.template_title').val();
			var news_templates = $('#template-' + template_id).find('.news_templates').prop('checked');
			var email_user_notification = $('#template-' + template_id).find('.email_user_notification').prop('checked');
			var template_text = $.trim(tinyMCE.activeEditor.getContent());
			
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
			$.post(baseUrl + 'clients/ajax_save_template', {template_id : template_id, template_name : template_name, news_templates:news_templates, email_user_notification:email_user_notification, template_text : template_text}, function (resp) {
				if (resp.status == 'ok')
					location.reload();
				return false;
			}, 'json');
			return false;
		});
		$('.deleteTemplate').click(function () {
			var template_id = $(this).data('delete_id');
			if (confirm('Are you sure?')) {
				
				$.post(baseUrl + 'clients/ajax_delete_template', {template_id:template_id}, function (resp) {
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
