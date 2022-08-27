<script>
    window.followup_tags = <?= json_encode((!isset($tags) || is_null($tags)) ? [] : $tags); ?>;
</script>
<div id="setting-<?php echo isset($setting->fs_id) ? $setting->fs_id : '0'; ?>" data-fs_id="<?php echo isset($setting->fs_id) ? $setting->fs_id : '0'; ?>" class="modal fade text-left followup_settings_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content panel panel-default p-n">
			<header class="panel-heading"><?php if(isset($setting->fs_id)) : ?>Edit<?php else : ?>Add<?php endif; ?></header>
			<form data-type="ajax" data-location="<?= base_url('administration/followup');?>" data-url="<?php echo base_url('administration/ajax_save_followup'); ?>" autocomplete="off">
				<div class="modal-body">
					<div class="form-horizontal">

						<div class="row">
							<div class="control-group col-md-4">
								<label class="control-label">Module</label>

								<div class="controls">
									<select name="fs_table" class="form-control moduleSelector" data-toggle="tooltip" data-placement="top" title="" data-original-title="">
										<?php foreach ($modules as $key => $value) : ?>
											<option value="<?php echo $key; ?>" <?php echo isset($setting->fs_table) && $setting->fs_table == $key ? 'selected' : ''; ?>>
											<?php echo $value['name']; ?>
										</option>
										<?php endforeach; ?> 
									</select>
								</div>
							</div>
							<div class="control-group col-md-8">
								<label class="control-label">Statuses</label>

								<div class="controls pos-rlt">
									<input type="hidden" name="fs_statuses" autocomplete="false" class="select-statuses w-100" value="<?php echo isset($setting->fs_statuses) ? implode('|', json_decode($setting->fs_statuses)) : '' ;?>" data-value="<?php echo isset($setting->fs_statuses) ? implode('|', json_decode($setting->fs_statuses)) : '' ;?>" data-toggle="tooltip" data-placement="top" title="" data-original-title=""/>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="control-group col-md-4">
								<label class="control-label">Type</label>

								<div class="controls">
									<select name="fs_type" class="form-control fs_type" data-toggle="tooltip" data-placement="top" title="" data-original-title="">
										<optgroup label="Manual">
											<option value="call" <?php echo isset($setting->fs_type) && $setting->fs_type == 'call' ? 'selected' : ''; ?>>
												Call
											</option>
											<option value="mail" <?php echo isset($setting->fs_type) && $setting->fs_type == 'mail' ? 'selected' : ''; ?>>
												Mail
											</option>
										</optgroup>
										<optgroup label="Auto">
											<option value="email" <?php echo isset($setting->fs_type) && $setting->fs_type == 'email' ? 'selected' : ''; ?>>
												Email
											</option>
                                            <?php if(config_item('messenger')) : ?>
                                            <!--<option value="sms" <?php echo isset($setting->fs_type) && $setting->fs_type == 'sms' ? 'selected' : ''; ?>>
												SMS
											</option>-->
                                            <?php endif; ?>
										</optgroup>
										<!--<optgroup label="Function">
											<option value="invoice_overdue" <?php echo isset($setting->fs_type) && $setting->fs_type == 'invoice_overdue' ? 'selected' : ''; ?>>
												Invoice Overdue
											</option>
											<option value="update_overdue" <?php echo isset($setting->fs_type) && $setting->fs_type == 'update_overdue' ? 'selected' : ''; ?>>
												Update Overdue
											</option>
											<option value="estimate_expired" <?php echo isset($setting->fs_type) && $setting->fs_type == 'estimate_expired' ? 'selected' : ''; ?>>
												Estimate Expired
											</option>
											<option value="equipment_alarm" <?php echo isset($setting->fs_type) && $setting->fs_type == 'equipment_alarm' ? 'selected' : ''; ?>>
												Equipment Alarm
											</option>
											<option value="expired_user_docs" <?php echo isset($setting->fs_type) && $setting->fs_type == 'expired_user_docs' ? 'selected' : ''; ?>>
												Expired User Documents
											</option>
										</optgroup>-->
									</select>
								</div>
							</div>
							<div class="control-group col-md-3 fs_periodicity block <?php if(isset($setting->fs_table) && ($setting->fs_table == 'schedule' && $setting->fs_table == 'client_tasks')) : ?>hide<?php endif; ?>">
                                <?php if(isset($setting->fs_type) && $setting->fs_type == 'call') :?>
                                    <label class="control-label">Call after (days)</label>
                                <?php else : ?>
                                    <label class="control-label">Send After (days)</label>
                                <?php endif;?>

								<div class="controls">
									<input type="number" name="fs_periodicity" min="0" <?php if(isset($setting->fs_table) && ($setting->fs_table == 'schedule' && $setting->fs_table == 'client_tasks')) : ?>disabled="disabled"<?php endif; ?> class="form-control block" value="<?php echo isset($setting->fs_periodicity) ? $setting->fs_periodicity : ''; ?>" data-toggle="tooltip" data-placement="top" title="" data-original-title="">
								</div>
							</div>
							
							<!--<div class="control-group col-md-3 fs_time_periodicity block <?php if(isset($setting->fs_table) && ($setting->fs_table != 'schedule' && $setting->fs_table != 'client_tasks')) : ?>hide<?php endif; ?>">
								<label class="control-label">Time Before</label>
								<div class="controls">
									<input <?php if(isset($setting->fs_table) && ($setting->fs_table == 'schedule' && $setting->fs_table == 'client_tasks')) : ?>disabled="disabled"<?php endif; ?> type="number" name="fs_time_periodicity" class="form-control" value="<?php echo isset($setting->fs_time_periodicity) && $setting->fs_time_periodicity != 0 ? $setting->fs_time_periodicity : ''; ?>" data-toggle="tooltip" data-placement="top" title="" data-original-title="">
								</div>
							</div>-->
							<div class="control-group col-md-3 fs_time_block block ">
								<label class="control-label">Time</label>
								<div class="controls">
									<input type="time" name="fs_time" <?php if(isset($setting->fs_table) && ($setting->fs_table == 'schedule' && $setting->fs_table == 'client_tasks')) : ?>disabled="disabled"<?php endif; ?>  class="form-control" value="<?php echo isset($setting->fs_time) ? date(trim(str_replace('a', '', getPHPTimeFormatWithOutSeconds())), strtotime($setting->fs_time)) : ''; ?>" data-toggle="tooltip" data-placement="top" title="" data-original-title="">
									<?php if(isset($setting->fs_id)) : ?>
										<input type="hidden" name="fs_id" value="<?php echo $setting->fs_id; ?>">
									<?php endif; ?>
								</div>
							</div>
							<div class="control-group col-md-2 block">
								<div class="checkbox">
									<label>
										<span style="display: block;height: 25px;margin-left: -30px;">Repeat</span>
										<input type="checkbox" name="fs_every" value="1" data-toggle="tooltip" data-placement="top" title="" data-original-title=""<?php echo isset($setting->fs_every) && $setting->fs_every ? ' checked' : ''; ?>>
									</label>
								</div>
							</div>
							<div class="clear"></div>
						</div>
						<?php $types = isset($setting) && $setting->fs_client_types ? json_decode($setting->fs_client_types) : []; ?>
						<?php if(isset($setting->fs_id) && !$setting->fs_client_types) $types = []; ?>
						<?php if(!isset($setting->fs_id)) $types = [1, 2, 3]; ?>
						<?php if($types === FALSE) $types = []; ?>
						<div class="row">
							<div class="control-group col-md-12 m-t" name="fs_client_types[]" data-toggle="tooltip" data-placement="top" title="" data-original-title="">
								<label class="control-label pull-left">Allowed For</label>
								<div data-toggle="buttons" class="pull-right m-l fs_client_types" name="fs_client_types[]" data-toggle="tooltip" data-placement="top" title="" data-original-title="">
									<?php 
									$active = $checked = NULL;
									if(array_search(1, $types) !== FALSE) {
										$active = 'active';
										$checked = 'checked';
									} 
									?>
			                        <label class="btn btn-sm btn-info m-l-sm <?php echo $active; ?>" style="width: 130px;">
			                        	<i class="fa fa-check text-active"></i>
			                        	Residential
			                          	<input type="checkbox" name="fs_client_types[]" <?php echo $checked; ?> value="1">
			                        </label>
			                        <?php 
									$active = $checked = NULL;
									if(array_search(2, $types) !== FALSE) {
										$active = 'active';
										$checked = 'checked';
									} 
									?>
			                        <label class="btn btn-sm btn-info m-l-sm <?php echo $active; ?>" style="width: 130px;">
			                        	<i class="fa fa-check text-active"></i>
			                          	Corporate
			                          	<input type="checkbox" name="fs_client_types[]" <?php echo $checked; ?> value="2">
			                        </label>
			                        <?php 
									$active = $checked = NULL;
									if(array_search(3, $types) !== FALSE) {
										$active = 'active';
										$checked = 'checked';
									} 
									?>
			                        <label class="btn btn-sm btn-info m-l-sm <?php echo $active; ?>" style="width: 130px;">
			                        	<i class="fa fa-check text-active"></i>
			                        	Municipal
			                          	<input type="checkbox" name="fs_client_types[]" <?php echo $checked; ?> value="3"> 
			                        </label>
			                      </div>
							</div>
						</div>

                        <div class="row">
                            <div class="control-group col-md-12 m-t">
                                <div class="client-tags-container m-bottom-5">
                                    <span class="pull-left h5" style="padding-top: 7px"><i class="fa fa-bookmark icon-muted fa-fw text-warning"></i>&nbsp;Tag:</span>
                                        <div id="folowup-settings-tags-form">
                                            <input class="followup-tags" type="text" multiple="multiple" autocomplete="nope"/>
                                            <input type="hidden" name="followup_tags" value="" autocomplete="off" autocorrect="off"
                                                autocapitalize="off" spellcheck="false">
                                            <div class="clear"></div>
                                        </div>
                                </div>
                                <div class="clear"></div>
                            </div>
                        </div>
						<div class="row hide fs_template_block">
							<div class="control-group col-md-12">
									
							</div>
							<div class="control-group col-md-12">
								<label class="control-label">Template</label>

								<div class="controls fs_template_email">
									<input type="text" name="fs_subject" class="form-control m-b-sm" value="<?php echo isset($setting->fs_subject) ? $setting->fs_subject : ''; ?>" placeholder="Subject"  data-toggle="tooltip" data-placement="top" title="" data-original-title="">
									<textarea name="fs_template" id="fs_template_editor_<?php echo isset($setting) && isset($setting->fs_id) ? $setting->fs_id : '0'; ?>" class="form-control fs_template" data-toggle="tooltip" data-placement="top" title="" data-original-title="">
										<?php echo isset($setting->fs_template) ? $setting->fs_template : ''; ?>
									</textarea>
									<div class="checkbox pull-right">
										<label>
											<input type="checkbox" name="fs_pdf" value="1"<?php echo isset($setting) && isset($setting->fs_pdf) && $setting->fs_pdf ? ' checked' : ''; ?>> 
											Attach PDF
										</label>
									</div>
								</div>

								<div class="controls fs_template_sms">
									<textarea name="fs_template" class="form-control fs_template" data-toggle="tooltip" data-placement="top" title="" data-original-title=""><?php echo isset($setting) && isset($setting->fs_template) ? trim(strip_tags($setting->fs_template)) : ''; ?></textarea>
								</div>

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
					<button class="btn" data-dismiss="modal" aria-hidden="true" type="button">Close</button>
				</div>
			</form>
		</div>
	</div>
</div>

<style>
    .js-expand-more{ cursor: pointer; }
    .client-tags-dropdown-container .select2-choices{
        border: none!important;
        border-bottom: 1px solid #d9d9d9!important;
        background: #ccc0!important;
    }
    .client-tags-dropdown-container .select2-choices:after{
        display: none!important
    }

    .client-tags-dropdown-container .select2-search-choice{
        color: #fff !important;
        background: #81ba53!important;
        border-color: #79b549!important;
    }

    .client-tags-dropdown-container .select2-search-choice .select2-search-choice-close:after{
        color: #fff !important;
    }
    .client-tags-dropdown-container.select2-container{
        height: auto!important;
    }
    .client-tags-dropdown-container.select2-container-active .select2-choices{
        box-shadow: none!important;
        background: #f9f9f9!important;
    }

    .client-tags-dropdown-container .select2-input{ min-width: 100px!important; }
    .client-tags-dropdown-container .select2-search-field{ max-width: 10px!important; position: relative}

    .client-tags-dropdown-container .select2-search-field:after{
        color: #989898;
        content: "Write Tag ...";
        text-decoration: underline;
        left: 10px;
        top: 5px;
        position: absolute;
    }
    .client-tags-dropdown-container.select2-dropdown-open .select2-search-field:after,
    .client-tags-dropdown-container.select2-container-active .select2-search-field:after,
    .client-tags-dropdown-container .select2-search-field:active:after,
    .client-tags-dropdown-container .select2-choices:focus .select2-search-field:after,
    .client-tags-dropdown-container .select2-choices:active .select2-search-field:after{
        content: "" !important;
    }
    .client-tags-dropdown{
        min-width: 150px;
        border: none!important;
    }
    .client-tags-dropdown .select2-results .select2-highlighted{
        background-color: #f1f1f1!important;
        color: #6d6d6d!important;
    }
    .client-tags-dropdown .select2-results{
        margin: 0!important;
        padding: 10px;
    }
</style>
<script src="<?php echo base_url(); ?>assets/js/modules/followup/followup_tags.js?v=1.1"></script>
