<?php $this->load->view('includes/header');?>
<script>
    window.followup_tags = <?= json_encode([]); ?>;
</script>
<style type="text/css">
.select2-container-multi .select2-choices .select2-search-field {
  width: auto;
}
.select2-container-multi .select2-choices .select2-search-field input {
  width: 100%!important;
}
.select2-container-multi .select2-choices {
  min-height: 26px;
}
.select2-container {
  height: auto!important;
  z-index: 0!important;
}
.select2-container a {
  height: 70%!important;
}
.has-error .select2-choices, .has-error .select2-container-multi.select2-container-active .select2-choices {
    border-color: #a94442;
    border: 1px solid #a94442;
    -webkit-box-shadow: inset 0 1px 1px rgba(0, 0, 0, .075);
    box-shadow: inset 0 1px 1px rgba(0, 0, 0, .075);
}
.has-error .select2-container {
	border-color: #a94442;
}
</style>
<section class="scrollable p-sides-15">
	<ul class="breadcrumb no-border no-radius b-b b-light pull-in">
		<li><a href="<?php echo base_url(); ?>"><i class="fa fa-home"></i> Home</a></li>
		<li class="active">Follow Up Settings</li>
	</ul>
	<section class="panel panel-default">
		<header class="panel-heading">Settings List
			<a class="add_fs btn btn-success btn-xs pull-right" data-fs_id="0" type="button" style="margin-top: -1px;"
			   href="#setting-0" role="button"  data-toggle="modal" data-backdrop="static" data-keyboard="false">
				<i class="fa fa-plus"></i>
			</a>
		</header>

		<div class="m-bottom-10 p-sides-10">
			<table class="table tsble-striped m-n">
				<thead>
				<tr>
					<th class="text-center">#</th>
					<th class="text-center">Module</th>
					<th class="text-center">Statuses</th>
					<th class="text-center">Periodicity</th>
					<th class="text-center">Type</th>
					<th class="text-center">Time</th>
					<th class="text-center">Client Types</th>
                    <th class="text-center">Tags</th>
					<th class="text-center">Action</th>
				</tr>
				</thead>
				<tbody>
				<?php if ($settings) : ?>
					<?php foreach ($settings as $key => $setting) : ?>
						<tr>
							<td class="text-center">
								<?php echo ($key + 1); ?>
							</td>
							<td class="text-center">
								<?php echo ucwords(str_replace('_', ' ', $setting->fs_table)); ?>
							</td>
							<td class="text-center">
								<?php $statusList = NULL; ?>
								<?php $statuses = json_decode($setting->fs_statuses,true); ?>
								<?php if ($statuses && !empty($statuses)) : ?>
									<?php foreach ($statuses as $k => $value) : ?>
										<?php $statusList .= $modules[$setting->fs_table]['statuses'][$value] . ', '; ?>
									<?php endforeach; ?>
								<?php endif; ?>

								<?php echo $statusList ? rtrim($statusList, ', ') : '—'; ?>
							</td>
							<td class="text-center">
								<?php echo $setting->fs_every ? 'Every' : 'After'; ?>
								<strong><?php echo $setting->fs_periodicity; ?></strong>
								<?php echo $setting->fs_periodicity > 1 ? 'Days' : 'Day'; ?>
							</td>
							<td class="text-center">
								<?php echo ucwords(str_replace('_', ' ', $setting->fs_type)); ?>
							</td>
							<td class="text-center">
								<?php if($setting->fs_time != '00:00:00') : ?>
									<?php echo getTimeWithDate($setting->fs_time, 'H:i:s'); ?>
								<?php else : ?>
									—
								<?php endif; ?>
							</td>
							<td class="text-center">
								<?php if($setting->fs_client_types) : ?>
									<?php $types = json_decode($setting->fs_client_types); ?>
									<?php foreach ($types as $key => $value) : ?>
										<?php if($value == 1) : ?>
											<?php echo 'Residential'; ?>
												<?php if(isset($types[$key+1])) : ?>
													<br>
											<?php endif;?>
										<?php endif;?>
										<?php if($value == 2) : ?>
											<?php echo 'Corporate'; ?>
												<?php if(isset($types[$key+1])) : ?>
													<br>
											<?php endif;?>
										<?php endif;?>
										<?php if($value == 3) : ?>
											<?php echo 'Municipal'; ?>
												<?php if(isset($types[$key+1])) : ?>
													<br>
											<?php endif;?>
										<?php endif;?>
									<?php endforeach; ?>
								<?php else : ?>
									—
								<?php endif; ?>
							</td>
                            <td class="text-center">
                                <?php foreach ($setting->tags as $tag): ?>
                                    <?= $tag->name?> <br />
                                <?php endforeach;?>
                            </td>
							<td class="text-center">
								<a class="edit_fs btn btn-xs btn-default" data-fs_id="<?php echo $setting->fs_id; ?>" href="#setting-<?php echo $setting->fs_id; ?>" role="button"
									data-toggle="modal" data-backdrop="static" data-keyboard="false"><i class="fa fa-pencil"></i></a>
									&nbsp;
								<form data-type="ajax" data-before="confirmDelete" data-location="<?php echo current_url(); ?>" data-url="<?php echo base_url('administration/ajax_delete_followup'); ?>" class="inline">
									<input type="hidden" name="fs_id" value="<?php echo $setting->fs_id; ?>">
									<input type="hidden" name="fs_disabled" value="<?php echo !$setting->fs_disabled; ?>">
									<button type="submit" class="btn btn-xs btn-<?php echo $setting->fs_disabled ? 'success' : 'danger'; ?> deletesetting" title="<?php echo $setting->fs_disabled ? 'Enable' : 'Disable' ;?>">
										<i class="fa <?php echo $setting->fs_disabled ? 'fa-play' : 'fa-stop';?>"></i>
									</button>
								</form>

							</td>
						</tr>
					<?php endforeach; ?>
				<?php else : ?>
					<tr>
						<td colspan="7">
							<?php echo "No records found"; ?>
						</td>
					</tr>
				<?php endif; ?>
				</tbody>
			</table>
		</div>

    </section>
</section>
<div id="followup_setting_modal">
    
</div>

<script src="<?php echo base_url(); ?>assets/vendors/notebook/js/tinymce/tinymce.min.js"></script>
<script type="text/javascript">

    var modules = <?php echo json_encode($modules); ?>;

    $(document).on('click', '.edit_fs, .add_fs', function (e) {
        var fs_id = $(this).data('fs_id');
        $.ajax({
            global: false,
            method: "GET",
            data: {fs_id:fs_id},
            url: base_url + "/administration/ajax_get_followup_modal_form",
            dataType:'html',
            success: function(response) {
                $('#followup_setting_modal').html(response);
                $('#setting-' + fs_id).modal();
            }
        });
    });
	$(document).ready(function() {
		$(document).on('change', '.fs_type', function() {
			var modal = $(this).parents('.followup_settings_modal:first');
			var fs_id = $(modal).data('fs_id');
			var module = $(modal).find('.moduleSelector').val();

			var selector = 'fs_template_editor_' + fs_id;
			var fs_type = $(this).val();
			$(modal).find('.moduleSelector').removeAttr('readonly');
			$(modal).find('.moduleSelector option[selected="selected"]').removeAttr('selected');
			$(modal).find('.moduleSelector').change();

			if(fs_type != 'email')
                destroyTiny(selector);



			if(fs_type == 'email' || fs_type == 'sms') {
				$(modal).find('[name="fs_client_types"]').addClass('show').removeClass('hide');;
				$(modal).find('.fs_template_block').removeClass('hide');
				if(module != 'schedule' && module != 'client_tasks' )
					$(modal).find('.fs_time_block').removeClass('hide');
				if(tinymce.editors[selector] == undefined && fs_type == 'email') {
					if($('.modal.followup_settings_modal').is(':visible'))
                        Common.initTinyMCE(selector, {variables:true});
					$(modal).find('.fs_template_sms').removeClass('show').addClass('hide');
					$(modal).find('.fs_template_sms .fs_template').attr('disabled', 'disabled');

					$(modal).find('.fs_template_email').removeClass('hide').addClass('show');
					$(modal).find('.fs_template_email .fs_template').removeAttr('disabled');
				}
				if(fs_type != 'email') {
					destroyTiny(selector);
					if(fs_type == 'sms') {
						$(modal).find('.fs_template_sms').removeClass('hide').addClass('show');
						$(modal).find('.fs_template_sms .fs_template').removeAttr('disabled');

						$(modal).find('.fs_template_email').removeClass('show').addClass('hide');
						$(modal).find('.fs_template_email .fs_template').attr('disabled', 'disabled');
					}
				}
			}
			else if(fs_type == 'invoice_overdue' || fs_type == 'update_overdue')
			{
				if(tinymce.editors[selector] != undefined)
					destroyTiny(selector);
				$(modal).find('.fs_template_block').addClass('hide');
				$(modal).find('[name="fs_client_types"]').addClass('hide').removeClass('show');
				$(modal).find('.fs_time_block').addClass('show');
				$(modal).find('.moduleSelector [value="invoices"]').attr('selected', 'selected');
				$(modal).find('.moduleSelector').val("invoices");
				$(modal).find('.moduleSelector').change();
				$(modal).find('.moduleSelector').attr('readonly', 'readonly');

			}
			else if(fs_type == 'estimate_expired')
			{

				if(tinymce.editors[selector] != undefined)
					destroyTiny(selector);
				$(modal).find('.fs_template_block').addClass('hide');
				$(modal).find('.fs_time_block').addClass('show');
				$(modal).find('.moduleSelector [value="estimates"]').attr('selected', 'selected');
				$(modal).find('.moduleSelector').val("estimates");
				$(modal).find('.moduleSelector').change();
				$(modal).find('.moduleSelector').attr('readonly', 'readonly');

			}
			else if(fs_type == 'equipment_alarm')
			{

				if(tinymce.editors[selector] != undefined)
					destroyTiny(selector);
				$(modal).find('.fs_template_block').addClass('hide');
				$(modal).find('[name="fs_client_types"]').addClass('hide').removeClass('show');
				$(modal).find('.fs_time_block').addClass('show');
				$(modal).find('.moduleSelector [value="equipment_items"]').attr('selected', 'selected');
				$(modal).find('.moduleSelector').val("equipment_items");
				$(modal).find('.moduleSelector').change();
				$(modal).find('.moduleSelector').attr('readonly', 'readonly');

			}
			else if(fs_type == 'expired_user_docs')
			{

				if(tinymce.editors[selector] != undefined)
					destroyTiny(selector);
				$(modal).find('[name="fs_client_types"]').removeClass('show').addClass('hide');
				$(modal).find('.fs_template_block').addClass('hide');
				$(modal).find('.fs_time_block').addClass('show');
				$(modal).find('.moduleSelector [value="users"]').attr('selected', 'selected');
				$(modal).find('.moduleSelector').val("users");
				$(modal).find('.moduleSelector').change();
				$(modal).find('.moduleSelector').attr('readonly', 'readonly');

			}
			else {
				$(modal).find('.fs_template_block').addClass('show');
				$(modal).find('.fs_time_block').addClass('hide');
				$(modal).find('[name="fs_client_types"]').addClass('show').removeClass('hide');
				$(modal).find('.fs_template_sms').removeClass('hide').addClass('show');
				$(modal).find('.fs_template_sms .fs_template').removeAttr('disabled');

				$(modal).find('.fs_template_email').removeClass('show').addClass('hide');
				$(modal).find('.fs_template_email .fs_template').attr('disabled', 'disabled');
				//destroyTiny(selector);
			}
			if(fs_type == 'call'){
                $(modal).find('.fs_periodicity').find('label').text('Call after (days)');
            }
            else {
                $(modal).find('.fs_periodicity').find('label').text('Send After (days)');
            }
		});

		$(document).on('change', '.moduleSelector', function() {
			var modal = $(this).parents('.followup_settings_modal:first');
			var value = $(this).val();
			$(modal).find(".select-statuses").val(null);


			$.each($(modal).find('.fs_type optgroup'), function(key, val) {
				if((value == 'schedule' || value == 'client_tasks') && $(val).attr('label') == 'Function')
				{

					if($(modal).find('.fs_type').val() != 'sms' && $(modal).find('.fs_type').val() != 'email')
					{
						$(modal).find('.fs_type option[value=call]').attr('selected', 'selected');
						$(modal).find('.fs_type ').val('call');
					}
					$(val).attr('disabled', 'disabled');
					$(val).addClass('block hide');
					$(modal).find('[name="fs_every"]').prop('checked', 'checked');
					$(modal).find('[name="fs_every"]').parents('.control-group:first').addClass('hide');
					$(modal).find('[name="fs_periodicity"]').val(1);
					$(modal).find('.fs_time_block').addClass('hide');
					$(modal).find('[name="fs_time"]').attr('disabled', 'disabled');
					$(modal).find('.fs_periodicity').addClass('hide');
					$(modal).find('.fs_time_periodicity').removeClass('hide');
					$(modal).find('[name="fs_time_periodicity"]').removeAttr('disabled');
				}
				else
				{
					if($(modal).is(':visible')) {
						$(val).removeAttr('disabled');
						$(val).removeClass('block hide');
						$(modal).find('.fs_time_periodicity').addClass('hide');
						$(modal).find('[name="fs_time_periodicity"]').attr('disabled', 'disabled');
						$(modal).find('[name="fs_every"]').prop('checked', false);
						$(modal).find('[name="fs_periodicity"]').val('');
						$(modal).find('[name="fs_time"]').removeAttr('disabled');
						$(modal).find('.fs_periodicity').removeClass('hide');
						$(modal).find('.fs_time_periodicity').parents('.controls:first').addClass('hide');
						$(modal).find('[name="fs_every"]').parents('.control-group:first').removeClass('hide');
					}
				}
			});

			initSelect2(modal);
		});

		$(document).on('hide.bs.modal', '.followup_settings_modal', function () {
            $(this).remove();
        });

		$(document).on('show.bs.modal', '.followup_settings_modal', function () {

			initSelect2(this);
			var modal = this;
			var fs_id = $(this).data('fs_id');
			var selector = 'fs_template_editor_' + fs_id;
			var fs_type = $(this).find('.fs_type').val();
			var module = $(modal).find('.moduleSelector').val();
			$(modal).find('.fs_template_block').addClass('show').removeClass('hide');
            console.log(fs_type);
            if(fs_type != 'email')
                destroyTiny(selector);

			if(fs_type == 'email' || fs_type == 'sms') {
				$(modal).find('.fs_template_block').removeClass('hide');

				if(module != 'schedule' && module != 'client_tasks' )
					$(modal).find('.fs_time_block').removeClass('hide');

				if(/*tinymce.editors[selector] == undefined && */fs_type == 'email') {
                    Common.initTinyMCE(selector, {variables:true});
					$(modal).find('.fs_template_sms').removeClass('show').addClass('hide');
					$(modal).find('.fs_template_sms .fs_template').attr('disabled', 'disabled');

					$(modal).find('.fs_template_email').removeClass('hide').addClass('show');
					$(modal).find('.fs_template_email .fs_template').removeAttr('disabled');
				}
				if(fs_type != 'email') {

					if(fs_type == 'sms') {
						$(modal).find('.fs_template_sms').removeClass('hide').addClass('show');
						$(modal).find('.fs_template_sms .fs_template').removeAttr('disabled');

						$(modal).find('.fs_template_email').removeClass('show').addClass('hide');
						$(modal).find('.fs_template_email .fs_template').attr('disabled', 'disabled');
					}
				}
			}
			else if(fs_type == 'equipment_alarm')
			{
				$(modal).find('.fs_template_block').addClass('hide').removeClass('show');
			}
			else {
				$(modal).find('.fs_template_block').addClass('show');
				$(modal).find('.fs_time_block').addClass('hide');

				$(modal).find('.fs_template_sms').removeClass('hide').addClass('show');
				$(modal).find('.fs_template_sms .fs_template').removeAttr('disabled');

				$(modal).find('.fs_template_email').removeClass('show').addClass('hide');
				$(modal).find('.fs_template_email .fs_template').attr('disabled', 'disabled');
				//destroyTiny(selector);
			}
		});

		$(document).click(function() {
			if($(".select-statuses").length)
				$(".select-statuses").select2('close');
		});

		$('.fs_type').change();
	});

	function initSelect2(modal)
	{
		var select2Input = $(modal).find("input.select-statuses");
		var selectedModule = $(modal).find('.moduleSelector').val();
		var selectTags = modules[selectedModule].statuses;//$(modal).find('.moduleSelector').find('option[value="' + $(modal).find('.moduleSelector').val() + '"]').data('statuses');
		var data = [];

		$(select2Input).select2('destroy');

		$.each(selectTags, function(key, val) {
			data.push({id:key, text:val});
		});

		$(select2Input).select2({
			placeholder: "Select Statuses",
			data: data,
			allowClear: true,
			multiple: true,
			dropdownCssClass: "select-statuses-dropdown",
			separator: "|"
		});
		$(select2Input).val($(select2Input).data('value'));
		$(select2Input).trigger('change');
		$('input.select2-input').attr('name', 'select2_name_' + parseInt(Math.random() * 100000));
		$(modal).find("input.select-statuses").attr('type', 'text').addClass('pos-abt').css({'display':'block', 'top':'-1px', 'z-index':'-1'});
	}

	function confirmDelete(){
		if(confirm('Are you sure?'))
			return true;
		return false;
	}

	function destroyTiny(selector)
	{
		if(tinymce.get(selector))
			tinymce.get(selector).destroy();
	}

</script>
<?php $this->load->view('includes/footer'); ?>
