<?php $this->load->view('includes/header'); ?>
<link rel="stylesheet" href="<?php echo base_url('assets/css/modules/settings/settings.css'); ?>">
<section class="hbox stretch">
	
	<section id="content">
		<section class="vbox">
            <section class="scrollable wrapper">
                <form action="<?php echo base_url('settings/save'); ?>" method="POST">
                    <div class="panel panel panel-default p-n">
                        <header class="header bg-gradient bg-white">
                          <p class="h4 text-success pull-left"><i class="fa fa-cogs"></i> Company management</p>
                          <button type="submit" class="btn btn-s-md btn-success btn-rounded pull-right">Save</button>
                          <div class="clearfix"></div>
                        </header>
                    </div>
				
                    <div class="row">

					<?php if(empty($settings)): ?>
                            <div class="bg-white">
                                <h4>Settings not installed</h4>
                            </div>
                        <?php else: ?>
                        <?php foreach($settings as $skey => $section): ?>
                        <div class="col-lg-6">
                            <div class="panel panel-default">
                                <div class="panel-heading"><?php echo $skey; ?> Management</div>
                                <div class="panel-body text-sm p-n">
                                    <table class="table table-striped m-n">
                                    <?php foreach($section as $key => $item): ?>
                                        <tr>
                                            <td valign="middle" style="vertical-align: middle;">
                                                <?php if((isSystemUser() && $item->stt_key_name == 'synchronization') || ($item->stt_key_name != 'synchronization' && $item->stt_key_name != 'syncInvoiceNO') || (isSystemUser() && $item->stt_key_name == 'syncInvoiceNO')):?>
                                                    <strong>
                                                        <?php echo $item->stt_label; ?>
                                                        <?php if(config_item('auto_tax') && $item->stt_key_name === 'taxManagement') : ?>
                                                            <small class="text-muted block">* if Auto Tax is not available</small>
                                                        <?php endif; ?>
                                                    </strong>
                                            </td>
                                                <?php endif;?>
                                            <td style="vertical-align: middle;">
                                                <?php if(isAdmin() && $item->stt_key_name == 'qbDesktopSync'):?>
                                                    <?php
                                                        $this->load->view('partials/qb_desktop_sync_controls');
                                                        $this->load->view('partials/qb_desktop_logs');
                                                    ?>
                                                <?php elseif(isSystemUser() && $item->stt_key_name == 'synchronization'):?>
                                                    <input type="button" class="btn btn-danger changeSync" data-id="in" value="<?php echo json_decode($item->stt_key_value, true)['in']['state'] ? json_decode($item->stt_key_value, true)['in']['textOff'] : json_decode($item->stt_key_value, true)['in']['textOn']; ?>">
                                                    <input type="button" class="btn btn-danger changeSync" data-id="from" value="<?php echo json_decode($item->stt_key_value, true)['from']['state'] ? json_decode($item->stt_key_value, true)['from']['textOff'] : json_decode($item->stt_key_value, true)['from']['textOn']; ?>">
                                                <?php elseif($item->stt_key_name == 'auto_tax') : ?>
                                                    <div class="checkbox">
                                                        <label>
                                                            <input class="checkbox" type="checkbox" name="stt_key_value[<?php echo $item->stt_key_name; ?>]" value="1"<?php echo $item->stt_key_value ? ' checked' :''; ?>>
                                                        </label>
                                                    </div>
                                                <?php elseif((isSystemUser() && $item->stt_key_name == 'syncInvoiceNO') || ($item->stt_key_name != 'synchronization' && $item->stt_key_name != 'syncInvoiceNO')):?>
                                                        <input <?php if($item->stt_html_attrs): echo $item->stt_html_attrs; ?><?php else: ?>class="form-control"<?php endif; ?> type="text" name="stt_key_value[<?php echo $item->stt_key_name; ?>]" value="<?php echo $item->stt_key_value; ?>">
                                                <?php endif;?>

                                                <?php if ($item->stt_key_name == 'payroll_lunch_state' || $item->stt_key_name == 'payroll_deduction_state'): ?>
                                                    <input type="hidden" id="<?=$item->stt_key_name?>" name="stt_key_value[<?php echo $item->stt_key_name; ?>]" value="<?php echo $item->stt_key_value; ?>">
                                                <?php endif;?>

                                                <input class="form-control" type="hidden" name="stt_key_validate[<?php echo $item->stt_key_name; ?>]" value="<?php echo $item->stt_key_validate; ?>">

                                                <input class="form-control" type="hidden" name="stt_label[<?php echo $item->stt_key_name; ?>]" value="<?php echo $item->stt_label; ?>">

                                                <span class="text-danger"><?php echo form_error('stt_key_value['.$item->stt_key_name.']'); ?></span>

                                                <?php if($item->stt_key_name == 'Location'):?>
                                                    <button type="button" onclick="changeLocation()" class="btn btn-default pull-right fa fa-pencil" id="change"></button>
                                                <?php elseif($item->stt_key_name == 'taxManagement'):?>
                                                    <a href="#" onclick="deleteTax()" class="btn btn-danger pull-right trigger deleteTaxBtn" >
                                                        <i class="fa fa-trash-o"></i>
                                                    </a>
                                                    <span class="popover-markup edit">
                                                        <a href="#" style="margin-right: 5px" class="btn btn-default pull-right trigger editTaxBtn">
                                                            <i class="fa fa-pencil"></i>
                                                        </a>
                                                         <div class="head hide">Edit Tax</div>
                                                    </span>

                                                    <span class="popover-markup">
                                                        <a href="#" style="margin-right: 5px" class="btn btn-success pull-right trigger">
                                                            <i class="fa fa-plus"></i>
                                                        </a>
                                                        <div class="head hide">Create Tax</div>
                                                    </span>

                                                    <div class="content hide">
                                                        <form action="" class="saveTax" method="post">
                                                            <div class="form-group w-200">
                                                                <label><span>Tax Name: </span><input type="text" name="taxName" class="form-control nameTax" /></label>
                                                                <label><span>Tax %: </span><input type="number" max="100" min="0" value="0" size="4" step="0.01" name="taxRate" onchange="handleChange(this);" class="form-control filterme w-200 rateTax" /></label>
                                                                <button type="button" onclick="saveTax()" class="btn btn-primary btn-sm"><i class="glyphicon glyphicon-ok"></i></button>
                                                                <button type="button" onclick="closePopover()" class="btn btn-default btn-sm closePopover"><i class="glyphicon glyphicon-remove"></i></button>
                                                                <input type="hidden" name="taxId" id="taxId">
                                                                <input type="hidden" name="taxIdx" id="taxIdx">
                                                            </div>
                                                        </form>
                                                    </div>
                                                <?php endif;?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                        <?php if($skey == 'QuickBooks') : ?>
                                            <?php $this->load->view('qb_classes');
                                                $this->load->view('classes/class_modal') ?>
                                        <?php endif; ?>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php endif; ?>

                    </div>
                    <?php $this->load->view('domain_verification_add_form'); ?>

                    <div class="row" id="references_list_wrapper">
                        <?php $this->load->view('references/list'); ?>
                    </div>
                    <?php $this->load->view('references/form'); ?>

                </form>

			<input type="hidden" value='<?= json_encode(countries_select()); ?>' id="countries-config">
                <input type="hidden" value='<?= json_encode(all_taxes()); ?>' id="allTaxes">
			<input type="hidden" value='<?= json_encode(get_date_format()); ?>' id="format-config">
			<input type="hidden" value='<?= json_encode(get_task_length()); ?>' id="task-config">
            <input type="hidden" value='<?= json_encode(get_appointment_task_length()); ?>' id="appointment-task-config">
            <input type="hidden" value='<?= json_encode(getCurrencySymbolPositions()); ?>' id="currency-symbols-position-option">
            <input type="hidden" value='<?=json_encode(enabled_disabled()); ?>' id="enabled-disabled-select">
            <input type="hidden" value='<?= json_encode(scheduler_starts_dropdown()); ?>' id="scheduler-starts-dropdown">
            <input type="hidden" value='<?= json_encode(scheduler_ends_dropdown()); ?>' id="scheduler-ends-dropdown">
                <input type="hidden" value='<?= json_encode($qbDesktopLogsForSelect2 ?? '[]'); ?>' id="desktop-logs-value">
<!--                <input type="hidden" value='--><?//= !empty($classes) ? json_encode($classes) : "" ?><!--' class="classesWithChildren">-->
<!--                <input type="hidden" value='--><?//= !empty($classesForParent) ? json_encode($classesForParent) : "" ?><!--' class="classesWithChildrenForParent">-->
            </section>
		</section>
	</section>
</section>

<script src="<?php echo base_url(); ?>assets/js/modules/settings/settings.js?v=1.31"></script>
<script src="<?php echo base_url(); ?>assets/vendors/notebook/js/select2/select2.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/caret/1.0.0/jquery.caret.min.js"></script>
<script src="https://gitcdn.github.io/bootstrap-toggle/2.2.2/js/bootstrap-toggle.min.js"></script>

<?php $this->load->view('includes/footer'); ?>
<script>
    let classes = <?= !empty($classes) ? json_encode($classes) : "[]" ?>;
    let classesForParent = <?= !empty($classesForParent) ? json_encode($classesForParent) : "[]" ?>;
</script>
