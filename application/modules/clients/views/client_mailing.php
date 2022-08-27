<?php $this->load->view('includes/header'); ?>
<style>
    .mce-tinymce.mce-container.mce-panel {
        border-width: 2px!important;
    }

    .reset-button {
        text-decoration: underline;
        cursor: pointer;
    }
</style>
<!-- All clients display -->
<section class="scrollable p-sides-15">
	<ul class="breadcrumb no-border no-radius b-b b-light pull-in">
		<li><a href="<?php echo base_url(); ?>"><i class="fa fa-home"></i> Home</a></li>
		<li class="active">Clients</li>
	</ul>

	<div class="row">
		<section class="col-lg-4">
			<section class="panel panel-default p-n">
			<header class="panel-heading">Filter<small class="pull-right resetAll reset-button">Reset All</small></header>
			<div class="table-responsive">
				<form name="search" id="search" method="post" action="<?php echo base_url('clients/emailing_search'); ?>">
					<table class="table m-n">
						<tr>
							<td>
								<label class="pull-left" style="width:100%;"><small>Client Created From</small>
									<input name="search_client_from" class="datepicker form-control input-sm" type="text" placeholder="Date From" autocomplete="off"
										   value="<?php if(isset($search_client_from)) : echo date(getDateFormat(), strtotime($search_client_from)); endif; ?>">
								</label>
								
								<label class="pull-right" style="width:100%;"><small>Client Created To</small>
									<input name="search_client_to" class="datepicker form-control input-sm" type="text" placeholder="Date To" autocomplete="off"
										   value="<?php if(isset($search_client_to)) : echo date(getDateFormat(), strtotime($search_client_to)); endif; ?>">
								</label>
								<small class="pull-right resetDate reset-button">Reset</small>
							</td>
						</tr>
						<tr>
							<td>
								<small>Select Type</small>
								<select name="search_client_type" class="input-sm form-control" >
									<option value="">Client Type</option>
									<option <?php if(isset($search_client_type) && $search_client_type == 1) : ?>selected="selected"<?php endif; ?> value="1">Residential</option>
									<option <?php if(isset($search_client_type) && $search_client_type == 2) : ?>selected="selected"<?php endif; ?> value="2">Corporate</option>
									<option <?php if(isset($search_client_type) && $search_client_type == 3) : ?>selected="selected"<?php endif; ?> value="3">Municipal</option>
								</select>
							</td>
						</tr>
						<tr>
							<td><small>Select Search Method</small><small class="pull-right reset mainReset reset-button">Reset</small>
								<select name="search_by[]" multiple="" class="input-sm form-control" style="overflow: auto;">
									<?php /* <option <?php if ((isset($search_by) && count($search_by)) && array_search('tasks', $search_by) !== FALSE) : ?>selected="selected"<?php endif; ?> value="tasks">Tasks</option> */?>
									<option <?php if ((isset($search_by) && !empty($search_by)) && array_search('leads', $search_by) !== FALSE) : ?>selected="selected"<?php endif; ?> value="leads">Leads</option>
									<option <?php if ((isset($search_by) && !empty($search_by)) && array_search('estimates', $search_by) !== FALSE) : ?>selected="selected"<?php endif; ?> value="estimates">Estimates</option>
									<option <?php if ((isset($search_by) && !empty($search_by)) && array_search('workorders', $search_by) !== FALSE) : ?>selected="selected"<?php endif; ?>value="workorders">Workorders</option>
									<option <?php if ((isset($search_by) && !empty($search_by)) && array_search('invoices', $search_by) !== FALSE) : ?>selected="selected"<?php endif; ?> value="invoices">Invoices</option>
								</select>
							</td>
						</tr>
					</table>
					<table class="table m-n leadsTable <?php if (!isset($search_by) || empty($search_by) || array_search('leads', $search_by) === FALSE) : ?>hide<?php endif; ?>">
						<tr><td><strong>Leads</strong></td></tr>
						<tr>
							<td>
								<label class="pull-left" style="width:100%;"><small>Lead Created From</small>
									<input name="search_lead_from" class="datepicker form-control input-sm" type="text" placeholder="Date From"
										   value="<?php if(isset($search_lead_from)) : echo date(getDateFormat(), strtotime($search_lead_from)); endif; ?>">
								</label>
								
								<label class="pull-right" style="width:100%;"><small>Lead Created To</small>
									<input name="search_lead_to" class="datepicker form-control input-sm" type="text" placeholder="Date To"
										   value="<?php if(isset($search_lead_to)) : echo date(getDateFormat(), strtotime($search_lead_to)); endif; ?>">
								</label>
								<small class="pull-right resetDate reset-button">Reset</small>
							</td>
						</tr>	
						<tr>
							<td> 
								<small>Select Status</small>
								<select name="search_lead_status" class="input-sm form-control" >
									<option value="">Lead Status</option>
									<?php foreach($lead_statuses as $k=>$v) : ?>
										<option <?php if(isset($search_lead_status) && $search_lead_status == $v->lead_status_id) : ?>selected="selected"<?php endif; ?> value="<?php echo $v->lead_status_id?>"><?php echo $v->lead_status_name; ?></option>
									<?php endforeach; ?>
									<?php /*
										<option <?php if(isset($search_lead_status) && $search_lead_status == 'Already Done') : ?>selected="selected"<?php endif; ?> value="Already Done">Already Done</option>
										<option <?php if(isset($search_lead_status) && $search_lead_status == 'No Go') : ?>selected="selected"<?php endif; ?> value="No Go">No Go</option>
										<option <?php if(isset($search_lead_status) && $search_lead_status == 'For Approval') : ?>selected="selected"<?php endif; ?> value="For Approval">For Approval</option>
									*/?>
								</select>
							</td>
						</tr>
					</table>	 
					
					<table class="table m-n estimatesTable <?php if (!isset($search_by) || empty($search_by) || array_search('estimates', $search_by) === FALSE) : ?>hide<?php endif; ?>">
						<tr><td><strong>Estimates</strong></td></tr>
						<tr>
							<td>
								<label class="pull-left" style="width:100%;"><small>Estimate Created From</small>
									<input name="search_estimate_from" class="datepicker form-control input-sm" type="text" placeholder="Date From"
										   value="<?php if(isset($search_estimate_from)) : echo date(getDateFormat(), strtotime($search_estimate_from)); endif; ?>">
								</label>
								
								<label class="pull-right" style="width:100%;"><small>Estimate Created To</small>
									<input name="search_estimate_to" class="datepicker form-control input-sm" type="text" placeholder="Date To"
										   value="<?php if(isset($search_estimate_to)) : echo date(getDateFormat(), strtotime($search_estimate_to)); endif; ?>">
								</label>
								<small class="pull-right resetDate reset-button">Reset</small>
							</td>
						</tr>		
						<tr>
							<td><small>Select Service</small><small class="pull-right reset reset-button" >Reset</small>
								<select multiple="" name="search_service_type[]" class="input-sm form-control" >
									<?php foreach($services as $k=>$v) : ?>
										<option <?php if ((isset($search_service_type) && !empty($search_service_type)) && array_search($v->service_id, $search_service_type) !== FALSE) : ?>selected="selected"<?php endif; ?> value="<?php echo $v->service_id;?>"><?php echo $v->service_name;?></option>
									<?php endforeach; ?>
								</select>
							</td>
						</tr>
						<tr>
							<td>
                                <label class="pull-left" style="width:100%;"><small>Estimate Price From</small>
                                    <input onchange="/*updateInput(sliderPrice, from, this.value)*/" style="width:100%" name="search_estimate_price_from" type="number" step="any" class="input-sm form-control" placeholder="Estimate Price From"
                                           value="<?php if(isset($search_estimate_price_from)) : echo $search_estimate_price_from; endif; ?>">
                                </label>
                                <label class="pull-right" style="width:100%;"><small>Estimate Price To</small>
                                    <input onchange="/*updateInput(sliderPrice, to, this.value)*/" style="width:100%" name="search_estimate_price_to" type="number" step="any" class="input-sm form-control" placeholder="Estimate Price To"
                                           value="<?php if(isset($search_estimate_price_to)) : echo $search_estimate_price_to; endif; ?>">
                                </label>
								
							</td>
						</tr>
						<tr>
							<td>
                                <label class="pull-left" style="width:100%;"><small>Service Price From</small>
                                    <input style="width:100%" name="search_service_price_from" type="number" step="any" class="input-sm form-control" placeholder="Service Price From"
                                           value="<?php if(isset($search_service_price_from)) : echo $search_service_price_from; endif; ?>">
                                </label>
                                <label class="pull-right" style="width:100%;"><small>Service Price To</small>
                                    <input style="width:100%" name="search_service_price_to" type="number" step="any" class="input-sm form-control" placeholder="Service Price To"
                                           value="<?php if(isset($search_service_price_to)) : echo $search_service_price_to; endif; ?>">
                                </label>
								
							</td>
						</tr>
						<tr>
							<td><small>Select Estimator</small>
								<select name="search_estimator" class="input-sm form-control" >
									<option value="">Select Estimator</option>
									<?php foreach($estimators as $k=>$v) : ?>
										<option <?php if (isset($search_estimator) && $search_estimator == $v['id']): ?>selected="selected"<?php endif; ?> value="<?php echo $v['id'];?>"><?php echo $v['firstname'];?> <?php echo $v['lastname'];?></option>
									<?php endforeach; ?>
								</select>
								
							</td>
						</tr>
						
						<tr>
							<td><small>Select Estimate Status</small><small class="pull-right reset reset-button" >Reset</small>
								<select name="search_status[]" multiple="" class="input-sm form-control" >
									<?php foreach($statuses as $k=>$v) : ?>
										<option <?php if ((isset($search_status) && !empty($search_status)) && array_search($v->est_status_id, $search_status) !== FALSE) : ?>selected="selected"<?php endif; ?> value="<?php echo $v->est_status_id;?>"><?php echo $v->est_status_name;?></option>
									<?php endforeach; ?>
								</select>

							</td>
						</tr>
						<tr>
							<td><small>Estimate Description</small>
								<textarea name="search_estimate_description" type="text" class="input-sm form-control" placeholder="Description has..."
								   value="<?php if (isset($search_desc)) : echo $search_desc;  endif;?>"><?php if (isset($search_desc)) : echo $search_desc;  endif;?></textarea>
							</td>
						</tr>
					</table>
					<table class="table m-n workordersTable <?php if (!isset($search_by) || empty($search_by) || array_search('workorders', $search_by) === FALSE) : ?>hide<?php endif; ?>">
						<tr><td><strong>Workorders</strong></td></tr>
						<tr>
							<td>
								<label class="pull-left" style="width:100%;"><small>WO Created From</small>
									<input name="search_workorder_from" class="datepicker form-control input-sm" type="text" placeholder="Date From"
										   value="<?php if(isset($search_workorder_from)) : echo date(getDateFormat(), strtotime($search_workorder_from)); endif; ?>">
								</label>
								
								<label class="pull-right" style="width:100%;"><small>WO Created To</small>
									<input name="search_workorder_to" class="datepicker form-control input-sm" type="text" placeholder="Date To"
										   value="<?php if(isset($search_workorder_to)) : echo date(getDateFormat(), strtotime($search_workorder_to)); endif; ?>">
								</label>
								<small class="pull-right resetDate reset-button">Reset</small>
							</td>
						</tr>	
						<tr>
							<td> 
								<small>Select Status</small><small class="pull-right reset reset-button" >Reset</small>
								<select name="search_workorder_status[]" multiple="" class="input-sm form-control" >
									<?php foreach($wo_statuses as $k=>$v) : ?>
										<option <?php if ((isset($search_workorder_status) && !empty($search_workorder_status)) && array_search($v['wo_status_id'], $search_workorder_status) !== FALSE) : ?>selected="selected"<?php endif; ?> value="<?php echo $v['wo_status_id'];?>"><?php echo $v['wo_status_name'];?></option>
									<?php endforeach; ?>
								</select>
							</td>
						</tr>
					</table>
					<table class="table m-n invoicesTable <?php if (!isset($search_by) || empty($search_by) || array_search('invoices', $search_by) === FALSE) : ?>hide <?php endif; ?>">
						<tr><td><strong>Invoices</strong></td></tr>
						<tr>
							<td>
								<label class="pull-left" style="width:100%;"><small>Invoice Created From</small>
									<input name="search_invoice_from" class="datepicker form-control input-sm" type="text" placeholder="Date From"
										   value="<?php if(isset($search_invoice_from)) : echo date(getDateFormat(), strtotime($search_invoice_from)); endif; ?>">
								</label>
								
								<label class="pull-right" style="width:100%;"><small>Invoice Created To</small>
									<input name="search_invoice_to" class="datepicker form-control input-sm" type="text" placeholder="Date To"
										   value="<?php if(isset($search_invoice_to)) : echo date(getDateFormat(), strtotime($search_invoice_to)); endif; ?>">
								</label>
								<small class="pull-right resetDate reset-button">Reset</small>
							</td>
						</tr>	
						<tr>
							<td> 
								<small>Select Status</small><small class="pull-right reset reset-button" >Reset</small>
								<select name="search_invoice_status[]" multiple="" class="input-sm form-control" >
									<?php if(isset($invoices_statuses) && !empty($invoices_statuses)) : ?>
										<?php foreach($invoices_statuses as $k=>$v) : ?>
											<option <?php if ((isset($search_invoice_status) && !empty($search_invoice_status)) && array_search(intval($v->invoice_status_id), $search_invoice_status) !== FALSE) : ?>selected="selected"<?php endif; ?> value="<?php echo $v->invoice_status_id; ?>"><?php echo $v->invoice_status_name; ?></option>
										<?php endforeach;?>
									<?php endif;?>
								</select>
							</td>
						</tr>
						<tr>
							<td>
                                <label class="pull-left" style="width:100%;"><small>Invoice Price From</small>
                                    <input onchange="/*updateInput(sliderPrice, from, this.value)*/" style="width:100%" name="search_invoice_price_from" type="number" step="any" class="input-sm form-control" placeholder="Invoice Price From"
                                           value="<?php if(isset($search_invoice_price_from)) : echo $search_invoice_price_from; endif; ?>">
                                </label>
                                <label class="pull-right" style="width:100%;"><small>Invoice Price To</small>
                                    <input onchange="/*updateInput(sliderPrice, to, this.value)*/" style="width:100%" name="search_invoice_price_to" type="number" step="any" class="input-sm form-control" placeholder="Invoice Price To"
                                           value="<?php if(isset($search_invoice_price_to)) : echo $search_invoice_price_to; endif; ?>">
                                </label>
								
							</td>
						</tr>
					</table>
					<div class="text-center" style="padding: 6px 15px;">
						<button class="btn btn-sm btn-default" style="width:100%; background-color: #ededed;" type="submit" id="searchEst">Go!</button>
					</div>
				</form>	
			</div>
			
			</section>
		</section>
		<section class="col-lg-8 clientSection">
			<section class="panel panel-default p-n">
			<header class="panel-heading">Clients (<?php echo $count; ?>)
				<button class="btn btn-xs btn-success pull-left m-r-sm checkAll" onclick="checkAll(true, $(this));"><i class="fa fa-check"></i></button>
				<div style="" class="pull-right">
					<button class="btn btn-success dropdown-toggle sendLetter hidden"  style="margin-top: -8px;" data-toggle="dropdown">
						Newsletter 
						<span class="caret"style="margin-left:5px;"></span>
					</button>
					<ul class="dropdown-menu btn-block" id="wo_status" data-type="workorders" style="max-height: 200px; overflow-y: auto;">
						<li><a href="#email-" onclick="Common.initTinyMCE('template_text_')" data-toggle="modal"
							   style="padding-right: 6px;padding-left: 6px;">Without Template<span
									class="badge bg-info"></span></a>
						</li>
						<?php foreach($letters as $key=>$letter) : ?>
						<li><a href="#email-<?php echo $letter['email_template_id']; ?>" onclick="Common.initTinyMCE('template_text_<?php echo $letter['email_template_id']; ?>')" data-toggle="modal"
							   style="padding-right: 6px;padding-left: 6px;"><?php echo $letter['email_template_title']; ?> <span
									class="badge bg-info"></span></a>
						</li>
						<?php endforeach; ?>
					</ul>
					<?php $this->load->view('client_news_letters_modal');?>
					<div id="email-" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
						<div class="modal-dialog" style="max-width: 900px;">
							<div class="modal-content panel panel-default p-n">
								<header class="panel-heading">Send Email</header>
								<div class="modal-body">
									<div class="form-horizontal">
										<div class="control-group">
											<label class="control-label">From Estimator</label>
											<div class="controls">
												<select name="fromEstimator" class="input-sm form-control fromEstimator" onchange="changeEmail('', $('#email-').find('.fromEstimator option:selected').attr('data-email'));">
													<option value="">From Estimator</option>
													<option value="0">From Me</option>
													<?php foreach($estimators as $k=>$v) : ?>
														<option  value="<?php echo $v['id'];?>" data-email="<?php echo $v['user_email']; ?>"><?php echo $v['firstname'];?> <?php echo $v['lastname'];?></option>
													<?php endforeach; ?>
												</select>
												
											</div>
										</div>
										<div class="control-group">
											<label class="control-label">Email From </label>
											<div class="controls">
												<input class="fromEmail form-control" type="text"
														<?php if(isset($user_email)) : ?>
															value="<?php echo $user_email; ?>"
															<?php else :?>
																value="<?php echo $this->config->item('account_email_address'); ?>"
															<?php endif; ?>
															placeholder="Email from..." style="background-color: #fff;"/>
											</div>
										</div>
										<div class="control-group">
											<label class="control-label">Email Subject</label>
											<div class="controls">
												<input class="subject form-control" type="text"
													   value=""
													   placeholder="Email Subject" style="background-color: #fff;"/>
											</div>
										</div>
										<div class="control-group">
											<label class="control-label">Email Text</label>
											<div class="controls">
												<textarea id="template_text_" class="form-control" value="">
												</textarea>
											</div>
										</div>
									</div>
								</div>
								<div class="modal-footer">
									<button class="btn btn-success" data-save-template="" >
										<span class="btntext" >Send</span>
										<img src="<?php echo base_url(); ?>assets/img/ajax-loader.gif" style="display: none;width: 32px;"
											 class="preloader">
									</button>
									<button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
								</div>
							</div>
						</div>
					</div>
				</div>
			</header>
			<div class="table-responsive">
				
				<table class="table table-striped b-t b-light" id="tbl_search_result">
					<thead>
					<tr>
						<th></th>
						<th>Name</th>
						<th>Phone Number</th>
						<th>Email</th>
						<th>Date Created</th>
						<th>Address</th>
					</tr>
					</thead>
					<tbody>
					<?php if(isset($clients) && $count) : ?>
						<?php foreach ($clients as $client) : ?>
							<tr class="">
								<td>
									<label class="checkbox" style="margin-bottom: 0; margin-top: 0;	padding: 15px 0 16px 25px;">
											<input type="checkbox" style="margin-left: -10px; margin-top: -5px;" name="clients-<?php echo $client->client_id; ?>" value="<?php echo $client->client_id; ?>" onchange="propChecked()">
									</label>
								</td>
								<td>
									<?php if($client->client_type == 1) : ?>
										<?php $icon = 'icon_residential.png'?>
									<?php elseif($client->client_type == 2) : ?>
										<?php $icon = "icon_corp.png"; ?>
									<?php else : ?>
										<?php $icon = "icon_municipal.png";?>
									<?php endif; ?>
									<img height="17px" src="<?php echo base_url('assets/vendors/notebook/images') . '/' . $icon; ?>">
									<a href="<?php echo base_url() . $client->client_id ; ?>" target="_blank"><?php echo  $client->client_name; ?></a>
								</td>
								<td>
									<?php echo numberTo($client->cc_phone); ?>
								</td>
								<td>
									<?php echo $client->cc_email; ?>
								</td>
								<td>
									<?php echo getDateTimeWithDate($client->client_date_created, 'Y-m-d'); ?>
								</td>
								<td><?php echo $client->client_address;
									echo "&#44;&nbsp;";
									echo $client->client_city; ?>&nbsp;<?php echo $client->client_country; ?></td>
							</tr>
						<?php endforeach; ?>
					<?php else : ?>
					<tr>
						<td></td>
						<td colspan="4">No record found</td>
					</tr>
					<?php endif; ?>
					</tbody>
				</table>
			</div>
			</section>
		</section>
        <input type="hidden" id="php-variable" value="<?php echo getJSDateFormat()?>" />
	</div>
</section>
<script src="<?php echo base_url(); ?>assets/vendors/notebook/js/tinymce/tinymce.min.js"></script>
<script>
	var userEmail = '<?php echo $user_email; ?>';
	$(document).ready(function () {
		$('.clientRow').on('click', function(){
			//console.log($(this));
			$(this).parents('tr:first').find('[type="checkbox"]').click();
			if($('#tbl_search_result input:checked').length)
				$('.sendLetter').removeAttr('disabled');
			else
				$('.sendLetter').attr('disabled', 'disabled');
			return true;
		});
		$('[data-save-template]').click(function () {
			var template_id = $(this).data('save-template');
			$(this).attr('disabled', 'disabled');
			$('#email-' + template_id + ' .modal-footer .btntext').hide();
			$('#email-' + template_id + ' .modal-footer .preloader').show();
			$('#email-' + template_id + ' .subject').parents('.control-group').removeClass('error');
			$('#email-' + template_id + ' .fromEmail').parents('.control-group').removeClass('error');
			var from = $('#email-' + template_id).find('.fromEmail').val();
			var subject = $('#email-' + template_id).find('.subject').val();
			var estimator = $('#email-' + template_id).find('.fromEstimator').val();
			var template_text = $.trim(tinyMCE.activeEditor.getContent());
			var clients = new Array();
			
			if (!from) {
				$('#email-' + template_id + ' .fromEmail').parents('.control-group').addClass('error');
				$('#email-' + template_id + ' .modal-footer .btntext').show();
				$('#email-' + template_id + ' .modal-footer .preloader').hide();
				$(this).removeAttr('disabled');
				return false;
			}
			if (!template_text) {
				$('#email-' + template_id + ' .mce-tinymce.mce-container.mce-panel').addClass('error');
				$('#email-' + template_id + ' .modal-footer .btntext').show();
				$('#email-' + template_id + ' .modal-footer .preloader').hide();
				$(this).removeAttr('disabled');
				return false;
			}
			if(!$('#tbl_search_result input:checked').length)
				return false;
			else
			{
				$.each($('#tbl_search_result input:checked'), function(key, val){
					clients.push($(val).val());
					//if($('#tbl_search_result input:checked').length == key+1)
						//console.log(clients);
				}); 
				/*for(i=0; i <= $('#tbl_search_result input:checked').length; i++)
				{
					clients.push($($('#tbl_search_result input:checked')[i]).val());
				}*/
				clients = JSON.stringify(clients);
			}  
			$.post(baseUrl + 'clients/ajax_send_newsletters', {from:from, subject:subject, estimator:estimator, clients:clients, template_text:template_text}, function (resp) {
				
				location.reload();
				return false;
			}, 'json');
			return false;
		});
        $('.datepicker').datepicker({format: $('#php-variable').val()});
		$(document).on('click', '.dropdown-menu.animated.fadeInDown.searchFrom', function (e) {
			e.stopPropagation();
		});
		$('[name="search_by[]"]').on('change', function(){
			var value = $(this).val();
			$('#search').find('.table:not(:first)').addClass('hide');
			$.each(value, function(key, val){
				$('#search').find('.' + val + 'Table').removeClass('hide');
			});
			$.each($('#search').find('.table:not(:first)').find('input, select, textarea'), function(key, val){
				if($(val).parents('.table:first').hasClass('hide'))
				{
					$(val).val('');
				}
			});
		});
		$('.reset').on('click', function(){
			var obj = $(this);
			$(obj).parents('td:first').find('select').val('');
			if($(obj).hasClass('mainReset'))
				$(obj).parents('form').find(".table:not(:first)").addClass('hide');
			
		});
		$('.resetAll').on('click', function(){
			$.each($('#search input, select, textarea'), function(key, val){
				$(val).val('');
			});
			$.each($('#search table:not(:first)'), function(key, val){
				$(val).addClass('hide');
			});
		});
		$('.resetDate').on('click', function(){
			var obj = $(this);
			$(obj).parents('td:first').find('input').val('');
			
			
		});
		//onclick="$('.search_lead_work').val('')"
		 //onclick="$('.search_by').val('')"
		$(".panel-footer").on("click", ".getMore", function() {
			var num = $(this).attr('data-num');
			
			$.ajax({
				global: false,
				method: "POST",
				data: {num:num},
				url: base_url + "clients/ajax_more_clients",
				dataType:'json',
				success: function(response){
					
					if (response.status != 'ok')
					{
						errorMessage('Sorry. Clients not found');
						$('#tbl_search_result').find('.getMore' + type).remove(); 
					}
					else
					{
						
						$('#tbl_search_result .table_body tr:last').after(response.table);
						if(response.more)
							$('#tbl_search_result').find('.getMore').attr('data-num', response.offset);
						else
							$('#tbl_search_result').find('.getMore').remove();
					}
				}
			});
			return false;
		});
	});
	function propChecked()
	{
		if($('#tbl_search_result input:checked').length) {
            $('.sendLetter').removeAttr('disabled');
            $('.dropdown-toggle.sendLetter').removeClass('hidden');
        }
		else {
            $('.dropdown-toggle.sendLetter').addClass('hidden');
        }
	}
	function changeEmail(id, mail)
	{ 
		if(mail != '' && mail != undefined)
			$('#email-' + id).find('.fromEmail').val(mail);
		else
			$('#email-' + id).find('.fromEmail').val(userEmail);
	}
	function checkAll(val, button)
	{
	    if(val == true)
		{
            $('.dropdown-toggle.sendLetter').removeClass('hidden');
			$('#tbl_search_result input').prop('checked', true);
			$(button).removeClass('checkAll btn-success').addClass('removeAll btn-danger');
			$(button).find('i').removeClass('fa-check').addClass('fa-times');
			$(button).attr('onclick', 'checkAll(false, $(this))');
		}
		else
		{
            $('.dropdown-toggle.sendLetter').addClass('hidden');
			$('#tbl_search_result input').prop('checked', false);
			$(button).removeClass('removeAll btn-danger').addClass('checkAll btn-success');
			$(button).find('i').removeClass('fa-times').addClass('fa-check');
			$(button).attr('onclick', 'checkAll(true, $(this))');
		}
		if($('#tbl_search_result input:checked').length)
			$('.sendLetter').removeAttr('disabled');
		else
			$('.sendLetter').attr('disabled', 'disabled');
	}
	
	function checkEmail(email, id)
	{
		//console.log($('#tbl_search_result').find('.email-row-' + id)); return false;
		$.post(baseUrl + 'clients/check_client_email', {email: email, id: id}, function (resp) {
			if (resp.status == 'error') {
				$('a[data-email="' + email + '"]').addClass('text-danger').removeClass('text-success').removeClass('text-warning');
				errorMessage('Sorry. Client email is ' + resp.error);
			}
			else
			{
				if(resp.status == 'ok') {
					$('a[data-email="' + email + '"]').addClass('text-success').removeClass('text-danger').removeClass('text-warning');
				}
				if(resp.status == 'invalid') {
					$('a[data-email="' + email + '"]').addClass('text-danger').removeClass('text-success').removeClass('text-warning');
				}
				if(resp.status == 'unverifiable') {
					$('a[data-email="' + email + '"]').addClass('text-warning').removeClass('text-success').removeClass('text-danger');
					warningMessage('The Email is Unverifiable');
				}
			}
			return false;
		}, 'json');
		

		return false;
	}

</script>

<?php $this->load->view('includes/footer'); ?>
