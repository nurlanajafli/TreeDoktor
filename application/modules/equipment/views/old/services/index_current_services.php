<?php $this->load->view('includes/header'); ?>
<!-- Services -->
<script src="<?php echo base_url('assets/js/jquery.tablesorter.min.js'); ?>"></script>
<section class="scrollable p-sides-15">
	<ul class="breadcrumb no-border no-radius b-b b-light pull-in">
		<li><a href="<?php echo base_url(); ?>"><i class="fa fa-home"></i> Home</a></li>
		<li class="active">Equipment Services</li>
	</ul>

	<section class="panel panel-default p-n">
		<header class="panel-heading" style="border-top: none;">
			Required Services To
			<form method="POST" action="" style="display: inline-block;">
				<input type="text" class="monthpicker date-input-client form-control text-center" name="date" value="<?php echo !empty($date) ? $date : date('Y-m'); ?>" readonly style="margin-left: 5px;width: 100px;display: inline-block;height: 20px;">
				<button type="submit" class="btn btn-xs btn-info" style="font-size: 11px;margin-top: -4px;margin-left: -2px;">GO</button>
			</form>
			<form method="POST" action="" class="pull-right" style="display: inline-block;">
				<input type="hidden" name="pdf" value="1">
				<button type="submit" class="btn btn-xs btn-info">Download PDF</button>
			</form>
		</header>
		<div class="table-responsive">
			<table class="table" id="servicesList">
				<thead>
				<tr>
					<th>#</th>
					<th>Item</th>
					<th>Service Type</th>
					<th width="130">Service Date</th>
					<th width="180">Service Postponed On</th>
					<th width="150">Next Counter Value</th>
					<th width="170px" style="text-align:center">Action</th>
				</tr>
				</thead>
				<tbody>
				<?php if(!empty($months_services)) : ?>
					
					<?php foreach($months_services as $key => $service) : ?>
					<?php $kms = isset($service['complete_reports'][0]) ? intval($service['complete_reports'][0]['report_counter_kilometers_value']) + intval($service['service_period_kilometers']) : 0; ?>
								<tr<?php if($service['curr_service_date'] < date('Y-m')) : ?> class="bg-danger"<?php endif; ?>>
									<td><?php echo ($key+1); ?></td>
									<td>
										<a href="<?php echo base_url('equipments/profile/' . $service['item_id']); ?>">
											<?php echo $service['item_name']; ?>
										</a>
									</td>
									<td><?php echo $service['equipment_service_type']; ?></td>
<!--									<td>--><?php //echo date("Y-m-d", strtotime($service['curr_service_date'])); ?><!--</td>-->
									<td><?php echo getDateTimeWithDate($service['curr_service_date'], 'Y-m-d') ?></td>
									<td>
										<?php if($service['service_postpone_on']): ?>
										<?php echo $service['service_postpone_on']; ?> month
										<?php else: ?>
											-
										<?php endif; ?>
									</td>
									<?php if($service['group_id'] != 18) : ?>
										<td>
											<?php echo $service['service_period_kilometers'] ? $kms : '—'; ?>
										</td>
									<?php else : ?>
										<td>
											—
										</td>
									<?php endif; ?>

									<td class="text-center">
										<a href="#<?php echo $date . '-' . $service['id']; ?>" class="btn btn-default btn-xs" role="button" data-toggle="modal" data-backdrop="true" data-keyboard="true">Complete</a>
										
										<?php //if(date('m', strtotime($date)) <= date('m')) : ?>
											<a href="#pone-<?php echo $date . '-' . $service['id']; ?>" class="btn btn-default btn-xs" role="button" data-toggle="modal" data-backdrop="true" data-keyboard="true">Postpone</a>
										<?php //endif; ?>
									</td>
								</tr>
			
		
					<?php endforeach; ?>
				<?php else : ?>
					<tr>
						<td colspan="4" style="color:#FF0000;">No record found</td>
					</tr>
				<?php endif; ?>
				</tbody>
			</table>
		</div>
	</section>
	<?php if(!empty($months_services)) : ?>
		<?php foreach($months_services as $key => $service) : ?>
			<?php echo $this->load->view('equipments/services/service_done_modal', $service); ?>
			<?php echo $this->load->view('equipments/services/service_pawn_modal', $service); ?>
		<?php endforeach; ?>
	<?php endif; ?>

	<section class="panel panel-default p-n" style="box-shadow: none;">
		<header class="panel-heading">Reports</header> 
		<table class="table">
			<thead>
			<tr>
				<th width="150">Item</th>
				<th>Author</th>
				<th>Service Type</th>
				<th width="100">Date</th>
				<?php //<th>Hours Value</th>?>
				<th>Counter Value</th>
				<th>Description</th>
				<th>Timing</th>
				<th>Cost</th>
				<th width="100px">Action</th>
				
			</tr>
			</thead>
			<tbody>
			<?php if(!empty($months_services_reports) && !empty($months_services_reports)) : ?>
				<?php foreach($months_services_reports as $key => $report) : ?>
					<tr <?php if(!$report['report_kind']) : ?>class='bg-success'<?php endif; ?>>
						<td>
							<a href="<?php echo base_url('equipments/profile/' . $report['item_id']); ?>">
								<?php echo $report['item_name']; ?>
							</a>
						</td>
						<td><?php
							if($report['firstname']) :
								echo $report['firstname']; ?><br><?php echo $report['lastname']; 
							else : ?> 
								-
							<?php endif; ?>
						</td>
						<td><?php echo $report['equipment_service_type']; ?></td>
						<td><?php echo date(getDateFormat(), strtotime($report['report_date_created'])); ?><?php echo isset($report['report_kind']) ? ' (' . $report['report_kind'] . ')' : ''; ?></td>
						<?php //<td><?php echo isset($report['report_counter_hours_value']) ? $report['report_counter_hours_value'] : ''; </td>?>
						<td><?php echo isset($report['report_counter_kilometers_value']) ? $report['report_counter_kilometers_value'] : ''; ?></td>
						<td><?php echo $report['report_comment']; ?></td>
						<td><?php echo $report['report_hours']; ?></td>
                        <td><?php echo money($report['report_cost']); ?></td>
						<td class="text-center">
							<?php if($report['report_kind']) : ?>
								<a href="#report_<?php echo $report['report_id']; ?>" class="btn btn-default btn-xs" role="button" data-toggle="modal" data-backdrop="true" data-keyboard="true">Edit</a>
								<?php echo $this->load->view('equipments/services/service_pawn_modal', $report); ?>
							<?php else : ?>
								<a href="#report_<?php echo $report['report_id']; ?>" class="btn btn-default btn-xs" role="button" data-toggle="modal" data-backdrop="true" data-keyboard="true">Edit</a>
								<?php echo $this->load->view('equipments/services/service_done_modal', $report); ?>
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
			<?php else : ?>
				<tr>
					<td colspan="4" style="color:#FF0000;">No record found</td>
				</tr>
			<?php endif; ?>
			</tbody>
		</table>
	</section>



	<script>
		if($('#servicesList tbody tr').length > 1)
		{
			var defaultSort = [0, 0];

			if(location.hash) {
				defaultSort = location.hash.replace('#', '').split(',');
			}

			var myTextExtraction = function(node) 
			{ 
				return $(node).text(); 
			} 
			$("#servicesList").tablesorter({
				textExtraction: myTextExtraction,
				headers: { 
					0: {
						sorter: 'digit',
					},
					1: { 
						sorter: 'text',
					}, 
					2: { 
						sorter: 'text'
					},
					3: {
						sorter:'date'
					}
            	},
			});
			$.each($('#servicesList tbody tr'), function(key, val){
				$(val).find('td:first').text((key + 1));
			});
			$('#servicesList').bind('sortEnd', function(e,table) {
	    		location.hash = e.target.config.sortList[0][0] + ',' + e.target.config.sortList[0][1];
	    	});
		}
		$(document).ready(function () {
			$("#servicesList").trigger("sorton", [ [ defaultSort ] ]);
			$('.datepicker').datepicker({
				format: "yyyy-mm-dd"
			});
			$('.monthpicker').datepicker({
				format: "yyyy-mm",
				viewMode: "months",
				minViewMode: "months"
			});

		});
		
	</script>
<?php /*
	<section class="col-sm-12 panel panel-default p-n" style="background: none;border-radius: 5px;">
		<div class="alert alert-warning alert-block text-center" style="margin-bottom: 0px;">
			<h4 style="margin-bottom: 0;"><i class="fa fa-bell-alt"></i>Old Services</h4>
		</div>
	</section>
	
	<section class="col-sm-12 panel panel-default p-n">
		<header class="panel-heading">Current Month:
			<a class="pull-right btn btn-info" style="margin-top:-8px;" target="_blank"
			   href="<?php echo base_url('equipments/service_pdf'); ?>">Download List</a>
		</header>
		<table class="table table-hover" id="tbl_search_result">
			<thead>
			<tr>
				<th width="30"></th>
				<th>Group Name</th>
				<th>Item Name</th>
				<th>Service Type</th>
				<th>Service Date</th>
				<!--<th>Service Next</th>
				<th>Service Description</th>-->
				<th>Action</th>
			</tr>
			</thead>
			<tbody>
			<?php if (count($services)) : ?>
				<?php foreach ($services as $service) : ?>
					<tr style="color:#000;">
						<td>
							<?php if ($service['service_status'] != 'new') : ?>
								<i class="fa fa-check"></i>&nbsp;
							<?php endif; ?>
						</td>
						<td>
							<?php echo $service['group_name']; ?>
						</td>
						<td>
							<a href="<?php echo base_url('equipments/profile') . '/' . $service['item_id']; ?>">
								<?php echo $service['item_name']; ?></td>
							</a>
						<td><?php echo $service['equipment_service_type']; ?></td>
						<td><?php echo date('Y-m-d', $service['service_date']); ?></td>
						<td>
							<a href="<?php echo base_url('equipments/profile') . '/' . $service['item_id']; ?>" data-service_id="<?php echo $service['service_id']; ?>" data-item_id="<?php echo $service['service_item_id']; ?>"
							class="btn btn-xs btn-default <?php if($service['service_status'] == 'new') : ?>btn-danger<?php elseif($service['service_status'] == 'complete') : ?>btn-info<?php endif; ?>"
							role="button" data-toggle="modal">Item</a>
							<a href="#service_info_<?php echo $service['service_id']; ?>" data-service_id="<?php echo $service['service_id']; ?>" data-item_id="<?php echo $service['service_item_id']; ?>" style=""
							class="btn btn-xs btn-default" role="button" data-toggle="modal">Status</a>
							<div id="service_info_<?php echo $service['service_id']; ?>" class="modal fade rounded overflow"
								 tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
								<div class="modal-dialog" style="width: 900px;">
									<div class="modal-content panel panel-default p-n">
										<header class="panel-heading">Service
											for <?php echo $service['item_name']; ?></header>
										<div class="modal-body">
											<div class="p-10">
												<?php //$item['row'] = $service;
												//$this->load->view('equipment_info', $item); ?>
												<!-- <div id="servicesTabs" class="divider-bottom p-top-5 m-bottom-5"></div>-->
												<?php $data['service'] = $service;
												$this->load->view('complete_service_info', $data); ?>

											</div>
										</div>
										<div class="modal-footer">
											<button class="btn btn-primary updateStatus"
													data-service_id="<?php echo $service['service_id']; ?>">Save
											</button>
											<button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
										</div>
									</div>
								</div>
							</div>
						<a href="#service_edit_<?php echo $service['service_id']; ?>" data-service_id="<?php echo $service['service_id']; ?>" data-item_id="<?php echo $service['service_item_id']; ?>" style=""
							class="btn btn-xs btn-default service_edit" role="button" data-toggle="modal">Edit</a>
							<div id="service_edit_<?php echo $service['service_id']; ?>" class="modal fade rounded overflow"
								 tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
								<div class="modal-dialog" style="width: 900px;">
									<div class="modal-content panel panel-default p-n">
										<header class="panel-heading">Service
											for <?php echo $service['item_name']; ?></header>
										<div class="modal-body">
											<div class="p-10">
												<table class="table table-hover">
															<thead>
																<tr>
																	<th width="22%">Service Type</th>
																	<th>Periodicity</th>
																	<th width="12%">Date</th>
																	<th width="12%">Next</th>
																	<th width="5%">Current Hours</th>
																	<th width="10%">Next Hours</th>
																	<th width="35%">Description</th>
																	
																</tr>
															</thead>
															<tbody>
																<tr id="addServiceRow" data-service_id="<?php echo $service['service_id']; ?>" data-item_id="<?php echo $service['item_id']; ?>">
																	<td>
																		<select id="" class="form-control serviceType">
																			<option value="" selected="selected">—</option>
																			<?php foreach($service_types as $type) : ?>
																				<option <?php if($service['equipment_service_id'] == $type['equipment_service_id']) : ?>selected="selected"<?php endif; ?> value="<?php echo $type['equipment_service_id']?>"><?php echo $type['equipment_service_type'];?></option>
																			<?php endforeach; ?> 
																		</select>
																	</td>
																	<td>
																		<select id="" class="form-control servicePeriod">
																			<option <?php if($service['service_periodicity'] == 0) : ?>selected="selected"<?php endif; ?> value="">—</option>
																			<option <?php if($service['service_periodicity'] == 1) : ?>selected="selected"<?php endif; ?> value="1">One Month</option>
																			<option <?php if($service['service_periodicity'] == 3) : ?>selected="selected"<?php endif; ?> value="3">Three Month</option>
																			<option <?php if($service['service_periodicity'] == 6) : ?>selected="selected"<?php endif; ?> value="6">Six Month</option>
																			<option <?php if($service['service_periodicity'] == 12) : ?>selected="selected"<?php endif; ?> value="12">One Year</option>
																			<option <?php if($service['service_periodicity'] == 24) : ?>selected="selected"<?php endif; ?> value="24">Two Years</option>
																			<option <?php if($service['service_periodicity'] == 36) : ?>selected="selected"<?php endif; ?> value="36">Three Years</option>
																		</select>
																	</td>
																	<td>
																		<input type="text" id="" class="input-small form-control serviceDate  datepicker" value="<?php echo date('Y-m-d', $service['service_date']); ?>">
																	</td>
																	<td>
																		<input type="text" id="" class="input-small form-control serviceNext" value="<?php if($service['service_next']) : echo date('Y-m-d', $service['service_next']); endif;?>">
																	</td>
																	
																	<td>
																		<input type="text" id="" class="form-control serviceCurrentHrs" value="<?php echo $service['service_current_hrs']; ?>">
																	</td>
																	<td>
																		<input type="text" id="" class="form-control serviceNextHrs" value="<?php echo $service['service_next_hrs']; ?>">
																	</td>
																	<td>
																		<textarea id="" class="form-control serviceDescr" rows="2"><?php echo $service['service_description'];?></textarea>
																	</td>
																</tr>
															</tbody>
														</table>

											</div>
										</div>
										<div class="modal-footer">
											<button class="btn btn-primary updateServiceInfo"
													data-service_id="<?php echo $service['service_id']; ?>" data-item_id="<?php echo $service['service_item_id']; ?>">Save
											</button>
											<button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
										</div>
									</div>
								</div>
							</div>
							
						</td>
					</tr>
				<?php endforeach; ?>
			<?php else : ?>
				<tr>
					<td colspan="4" style="color:#FF0000;">No record found</td>
				</tr>
			<?php endif; ?>
			</tbody>
		</table>
	</section>
	
	<?php //$this->load->view('index_past_due.php'); ?>
	<?php //$this->load->view('index_past_services.php'); ?>
	<section class="col-sm-12 panel panel-default p-n text-center">
		<span style="border: 1px solid #000;display: inline-block;width: 18px;" class="bg-info">&nbsp;</span> - "Done" Service
		<span style="border: 1px solid #000;display: inline-block;width: 18px;" class="bg-danger">&nbsp;</span> - "New" Service
		<span style="border: 1px solid #000;display: inline-block;width: 18px;" class="bg-warning">&nbsp;</span> - "Future" Service
	</section>
	*/ ?>
	<footer class="panel-footer">
		<div class="row">
			<div class="col-sm-5 text-right text-center-xs pull-right">
				<?php echo $links; ?>
			</div>
		</div>
	</footer>
</section>
<script>
	$('.updateStatus').click(function () {
		var service_id = $(this).data('service_id');
		var obj = $(this).parent().parent();
		var status = $(obj).find('.serviceStatus').val();
		var hrs = $(obj).find('.serviceHrs').val();
		var comment = $(obj).find('.serviceComment').val();
		$.post(baseUrl + 'equipments/ajax_save_service', {service_id: service_id, hrs: hrs, comment: comment, status: status}, function (resp) {
			location.reload();
		});
	});

	$(document).on('click', '.delete_image', function () {
		var obj = $(this);
		var item_id = $(this).data('item_id');
		var filename = $(this).data('filename');
		$.post(baseUrl + 'equipments/ajax_remove_file', {item_id: item_id, filename: filename}, function (resp) {
			if (resp.status == 'ok') {
				$(obj).parent().remove();
			}
		}, 'json');
		return false;
	});
	$(document).on('change', '.fileToUpload', function () {
		var item_id = $(this).data('item_id');
		if (!$(this).parent().is('.disabled'))
			ajaxFileUpload($(this), item_id);
		return false;
	});
	function ajaxFileUpload(obj, equipment_id) {
		$(obj).parent().next('.preloader').show();
		$(obj).parent().addClass('disabled');
		var segments = location.href.split('/');
		$.ajaxFileUpload
		(
			{
				url: baseUrl + 'equipments/ajax_save_file/',
				secureuri: false,
				fileElementId: 'fileToUpload_' + equipment_id,
				dataType: 'json',
				data: {item_id: equipment_id},
				success: function (data, status) {
					$('#fileToUpload_' + equipment_id).parent().next('.preloader').hide();
					$('#fileToUpload_' + equipment_id).parent().removeClass('disabled');
					if (data.status == 'error')
						alert('Error');
					else {
						var lightbox = '';
						if (!(data.filename.indexOf('.pdf') + 1))
							lightbox = ' data-lightbox="works" ';
						$('#fileToUpload_' + equipment_id).parent().prev().before('<div><div class="line line-dashed line-lg"></div>File: <a target="_blank"' + lightbox + 'href="' + baseUrl + data.filepath + '">' + data.filename + '</a><a class="btn btn-danger btn-xs pull-right delete_image" data-item_id="' + equipment_id + '" data-filename="' + data.filename + '" aria-hidden="true"><i class="fa fa-trash-o"></i></a></div>');
					}
					$('#fileToUpload_' + equipment_id).removeAttr('disabled');
				},
				error: function (data, status, e) {
					alert(e);
					$('#fileToUpload_' + equipment_id).parent().next('.preloader').hide();
					$('#fileToUpload_' + equipment_id).parent().removeClass('disabled');
				}
			}
		)

		return false;

	}
</script>

<?php $this->load->view('includes/footer'); ?>
