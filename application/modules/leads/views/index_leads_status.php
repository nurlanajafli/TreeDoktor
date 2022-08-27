<?php $this->load->view('includes/header'); ?>
<style>
    #sortable tr {
        cursor: move;
    }
</style>
<section class="scrollable p-sides-15">
	<ul class="breadcrumb no-border no-radius b-b b-light pull-in">
		<li><a href="<?php echo base_url(); ?>"><i class="fa fa-home"></i> Home</a></li>
		<li class="active">Statuses</li>
	</ul>
	<section class="panel panel-default">
		<header class="panel-heading">Statuses
			<a href="#status-" class="btn btn-xs btn-success pull-right" role="button" data-toggle="modal"
			   data-backdrop="static" data-keyboard="false"><i class="fa fa-plus"></i></a>
			   <div class="clear"></div>
		</header>
		<div class="table-responsive">
			<table class="table table-hover">
				<thead>
				<tr>
					<th>Status Name</th>
					<th class="text-center" width="80">Default</th>
					<th class="text-center" width="80">Estimated</th>
					<th class="text-center" width="80">For Approval</th>
					<th class="text-center" width="100">Declined</th>
					<th class="text-center" width="100">Draft</th>
					<th class="text-center">Status Reasons</th>
					<th width="80px">Action</th>
				</tr>
				</thead>
				<tbody id="sortable">
				<?php if(isset($statuses) && !empty($statuses)) : ?>
					<?php foreach ($statuses as $key=>$status) : ?>
						<tr data-status_id="<?php echo $status->lead_status_id; ?>" <?php if (!$status->lead_status_active) : ?> style="text-decoration: line-through;"<?php endif; ?> data-wo_status_id="<?php echo $status->lead_status_id; ?>">
							<td><?php echo $status->lead_status_name; ?></td>
							<td class="text-center">
									<i class="fa fa-<?php echo $status->lead_status_default ? "check text-success" : "times text-danger";?>"></i>
							</td>
							<td class="text-center">
									<i class="fa fa-<?php echo $status->lead_status_estimated ? "check text-success" : "times text-danger";?>"></i>
							</td>
							<td class="text-center">
									<i class="fa fa-<?php echo $status->lead_status_for_approval ? "check text-success" : "times text-danger";?>"></i>
							</td>
							<td class="text-center">
									<i class="fa fa-<?php echo $status->lead_status_declined ? "check text-success" : "times text-danger";?>"></i>
							</td>
                            <td class="text-center">
                                <i class="fa fa-<?php echo $status->lead_status_draft ? "check text-success" : "times text-danger";?>"></i>
                            </td>
							<td<?php if(!isset($status->mdl_leads_reason) || empty($status->mdl_leads_reason)) : ?> class="text-center"<?php endif; ?>>
								<?php if(isset($status->mdl_leads_reason) && !empty($status->mdl_leads_reason)) : ?>
									<ul>
									<?php foreach($status->mdl_leads_reason as $jkey=>$reason) : ?>
										<li>
											<?php echo $reason->reason_name; ?>
										</li>
									<?php endforeach;?>
									</ul>
								<?php else : ?>
									—
								<?php endif; ?>
							</td>
							<td>
								<div id="status-<?php echo $status->lead_status_id; ?>" class="modal fade" tabindex="-1" role="dialog"
									 aria-labelledby="myModalLabel" aria-hidden="true">
									<div class="modal-dialog">
										<div class="modal-content panel panel-default p-n">
											<header class="panel-heading">Edit Status <?php echo $status->lead_status_name; ?></header>
											<div class="modal-body">
												<div class="form-horizontal">
													<div class="control-group">
														<label class="control-label">Name</label>

														<div class="controls">
															<input class="status_name form-control" type="text"
																   value="<?php echo $status->lead_status_name; ?>"
																   style="background-color: #fff;">
														</div>
													</div>
													<div class="control-group" style="display:<?php if($status->lead_status_declined) : ?>block;<?php else : ?>none;<?php endif; ?>">
														<a href="#" class="btn btn-xs btn-success pull-right new-reason"><i class="fa fa-plus"></i></a>
														<label class="control-label">Decline Reason</label>
														<div class="controls">
															<?php if($status->lead_status_declined) : ?>
																<?php foreach($status->mdl_leads_reason as $key=>$reason) : ?>
																	<br>
																	<div class="reason_block">
																		<input class="reason form-control" data-id="<?php echo $reason->reason_id; ?>" type="text" <?php if(!$status->lead_status_declined) : ?>disabled="disabled"<?php endif; ?>
																		   value="<?php echo $reason->reason_name; ?>"
																		   style="background-color: #fff; width:75%; float:left">
																		<div class="checkbox">
																			<label>
																				<input class="active_reason" style="margin: 2px 2px 0 28px;" <?php if(!$reason->reason_active) : ?>checked="checked"<?php endif; ?> type="checkbox"> Inactive Reason
																			</label>
																		</div>
																	</div>
																<?php endforeach; ?>
															<?php endif; ?>
														</div>
													</div>
													
												</div>
											</div>
											<div class="modal-footer">
												<button class="btn btn-success" data-save-status="<?php echo $status->lead_status_id; ?>" data-status-declined="<?php echo $status->lead_status_declined; ?>">
													<span class="btntext">Save</span>
													<img src="<?php echo base_url(); ?>assets/img/ajax-loader.gif"
														 style="display: none;width: 32px;" class="preloader">
												</button>
												<button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
											</div>
										</div>
									</div>
								</div>

								<a class="btn btn-default btn-xs" href="#status-<?php echo $status->lead_status_id; ?>" role="button"
								   data-toggle="modal" data-backdrop="static" data-keyboard="false"><i class="fa fa-pencil"></i></a>
								<?php if($status->lead_status_id && (!$status->lead_status_declined && !$status->lead_status_default && !$status->lead_status_estimated && !$status->lead_status_for_approval && !$status->lead_status_draft)) : ?>
								<a class="btn btn-xs btn-info deleteStatus" data-delete_id="<?php echo $status->lead_status_id; ?>" data-active="<?php echo $status->lead_status_active;?>"><i
										class="fa <?php if ($status->lead_status_active) : ?>fa-eye-slash<?php else : ?>fa-eye<?php endif; ?>"></i></a>
								<?php endif; ?>
							</td>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
				</tbody>
			</table>
		</div>
	</section>
</section>
<div id="status-" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content panel panel-default p-n">
			<header class="panel-heading">Create Status</header>
			<div class="modal-body">
				<div class="form-horizontal">
					<div class="control-group">
						<label class="control-label">Name</label>
						<div class="controls">
							<input class="status_name form-control" type="text" value="" placeholder="Status Name">
						</div>
					</div>
					<?php /*
					<div class="checkbox">
						<label>
							<input type="checkbox" class="default" value="1" <?php if($isset_default) : ?>disabled="disabled"<?php endif; ?> name="default"> Default (Default status can be only one)
						</label>
					</div>
					<div class="checkbox">
						<label>
							<input type="checkbox" class="estimated" value="1" <?php if($isset_estimated) : ?>disabled="disabled"<?php endif; ?> name="estimated">  Estimated (Estimated status can be only one)
						</label>
					</div>
					<div class="checkbox">
						<label>
							<input type="checkbox" class="for_approval" value="1" <?php if($isset_for_approval) : ?>disabled="disabled"<?php endif; ?> name="for_approval"> For Approval Status
						</label>
					</div>
					<div class="checkbox">
						<label>
							<input type="checkbox" class="declined" value="1" <?php if($isset_for_approval) : ?>disabled="disabled"<?php endif; ?> name="declined"> Declined Status
						</label>
					</div>
					*/ ?>
					<div class="control-group" style="display:none">
						<a href="#" class="btn btn-xs btn-success pull-right new-reason"><i class="fa fa-plus"></i></a>
						<label class="control-label">Decline Reasons</label>
						<div class="controls">
							<br>
							<div class="reason_block">
								<input class="reason form-control" type="text" 
								   value=""
								   style="background-color: #fff; width:75%; float:left">
								<div class="checkbox">
									<label>
										<input class="active_reason" style="margin: 3px 2px 0 28px;" type="checkbox"> Inactive Reason
									</label>
								</div>
							</div>
						</div>
					</div>
					
				</div>
			</div>
			<div class="modal-footer">
				<button class="btn btn-success" data-save-status="" data-status-declined="0">
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
	
	$(document).ready(function () {
		$('.new-reason').click(function(){
			trigger = false;
			$.each($(this).parent().find('.controls .reason_block .reason'), function(key,val){
				if($(val).val() == '')
					trigger = true;
			})
			if(trigger)
				return false;
			else
				$(this).parent().find('.controls').append('<br><br><div class="reason_block"><input class="reason form-control" type="text" style="background-color: #fff; width:75%; float:left"><div class="checkbox"><label><input class="active_reason" style="margin: 2px 2px 0 28px;" type="checkbox"> Inactive Reason</label></div>');
		});
		$('.declined').change(function(){
			var status_id = $(this).attr('data-id');
			var declined = +$(this).is(':checked');
			if(declined)
			{
				$(this).parent().parent().next().css('display', 'block');
				$(this).parent().parent().next().find('.reason').removeAttr('disabled');
			}
			else
			{
				$(this).parent().parent().next().css('display', 'none');
				$(this).parent().parent().next().find('.reason').attr('disabled');
			}
			return false;
		});
		$('[data-save-status]').click(function () {
			var status_id = $(this).data('save-status');
			$(this).attr('disabled', 'disabled');
			$('#status-' + status_id + ' .modal-footer .btntext').hide();
			$('#status-' + status_id + ' .modal-footer .preloader').show();
			$('#status-' + status_id + ' .status_name').parents('.control-group').removeClass('incorrect');
			$('#status-' + status_id + ' .reason').parents('.control-group').removeClass('incorrect');
			var status_name = $('#status-' + status_id).find('.status_name').val();
			//var status_priority = $('#status-' + status_id).find('.status_priority').val();
			var status_default = +$('#status-' + status_id).find('.default').is(':checked');
			var status_declined = $(this).data('status-declined');
			var status_estimated = +$('#status-' + status_id).find('.estimated').is(':checked');
			var status_for_approval = +$('#status-' + status_id).find('.for_approval').is(':checked');
			var status_draft = +$('#status-' + status_id).find('.draft').is(':checked');
			//var status_confirmed = +$('#status-' + status_id).find('.confirmed').is(':checked');
			//var status_sent = +$('#status-' + status_id).find('.sent').is(':checked');
			var active_reason = null;
			var status_reason = null;
			if(status_declined == '1')
			{
				status_reason = [];
				active_reason = [];
				$.each($('#status-' + status_id).find('.reason'), function(key,val){
					if($(val).val() == '')
						return false;
					else
					{
						status_reason.push({
							reason_name:$(val).val(),
							reason_id:$(val).data('id'),
							reason_active:$(val).parent().find('.active_reason').is(':checked')});
					}
				})
				
			}
			
			if (!status_name) {
				$('#status-' + status_id + ' .status_name').addClass('incorrect');
				$('#status-' + status_id + ' .modal-footer .btntext').show();
				$('#status-' + status_id + ' .modal-footer .preloader').hide();
				$(this).removeAttr('disabled');
				return false;
			} 
			if (status_declined == '1' && !status_reason.length) {
				$('#status-' + status_id + ' .reason').addClass('incorrect');
				$('#status-' + status_id + ' .modal-footer .btntext').show();
				$('#status-' + status_id + ' .modal-footer .preloader').hide();
				$(this).removeAttr('disabled');
				return false;
			}
			
			$.post(baseUrl + 'leads/ajax_save_leads_status', {status_id : status_id, status_name : status_name, status_estimated : status_estimated, status_for_approval : status_for_approval, status_default : status_default, status_declined : status_declined, status_reason : status_reason, active_reason : active_reason, status_draft: status_draft}, function (resp) {
				if (resp.status == 'ok')
					location.reload();
				return false;
			}, 'json');
			return false;
		});
		$('.deleteStatus').click(function () {
			var status_id = $(this).data('delete_id');
			var status = $(this).data('active');
			var active = 'inactive';
			if(status == 0)
			{
				active = 'active';
				status = 1;
			}
			else
				status = 0;
			if (confirm('Status will be ' + active + ', continue?')) {
				$.post(baseUrl + 'leads/ajax_delete_leads_status', {status_id: status_id, status: status}, function (resp) {
					if (resp.status == 'ok') {
						location.reload();
						return false;
					}
					alert('Ooops! Error!');
				}, 'json');
			}
		});

        $('#sortable').sortable({
            update: function(e, ui) {
                let statuses_priority = [];
                $('#sortable tr').each(function () {
                    statuses_priority.push($(this).data('status_id'));
                });

                $.post(base_url + 'leads/ajax_statuses_update', {statuses: statuses_priority});
            }
        }).disableSelection();
	});

</script>
<?php $this->load->view('includes/footer'); ?>
