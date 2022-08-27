<button class="btn btn-warning dropdown-toggle"  data-toggle="dropdown">
	<i class="fa fa-filter"></i>
	<span class="caret"  style="margin-left:5px;"></span>
</button>
<div class="dropdown-menu animated fadeInDown searchFrom" style="min-width: 330px;">
	<span class="arrow top" style="left: 85%;"></span>

	<form name="search" id="search" method="post" action="<?php echo base_url('clients/emailing_search'); ?>">
		<table class="table m-n">
			<tr>
				<td>
					<label class="pull-left" style="width:50%;"><small>Client Created From</small>
						<input name="search_client_from" class="datepicker form-control input-sm" type="text" placeholder="Date From" readonly
							   value="<?php if(isset($from)) : echo date('Y-m-d', strtotime($from)); endif; ?>">
					</label>
					
					<label class="pull-right" style="width:50%;"><small>Client Created To</small>
						<input name="search_client_to" class="datepicker form-control input-sm" type="text" placeholder="Date To" readonly
							   value="<?php if(isset($to)) : echo date('Y-m-d', strtotime($to)); endif; ?>">
					</label>
					<small class="pull-right resetDate">Reset</small>
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
				<td><small>Select Search Method</small><small class="pull-right reset mainReset">Reset</small>
					<select name="search_by[]" multiple="" class="input-sm form-control" >
						<option <?php if ((isset($search_by) && !empty($search_by)) && array_search('tasks', $search_by) !== FALSE) : ?>selected="selected"<?php endif; ?> value="tasks">Tasks</option>
						<option <?php if ((isset($search_by) && !empty($search_by)) && array_search('leads', $search_by) !== FALSE) : ?>selected="selected"<?php endif; ?> value="leads">Leads</option>
						<option <?php if ((isset($search_by) && !empty($search_by)) && array_search('estimates', $search_by) !== FALSE) : ?>selected="selected"<?php endif; ?> value="estimates">Estimates</option>
						<option <?php if ((isset($search_by) && !empty($search_by)) && array_search('workorders', $search_by) !== FALSE) : ?>selected="selected"<?php endif; ?>value="workorders">Workorders</option>
						<option <?php if ((isset($search_by) && !empty($search_by)) && array_search('invoices', $search_by) !== FALSE) : ?>selected="selected"<?php endif; ?> value="invoices">Invoices</option>
					</select>
				</td>
			</tr>
		</table>
		<table class="table m-n tasksTable hide">
			<tr><td><strong>Tasks</strong></td></tr>
			<tr>
				<td>
					<label class="pull-left" style="width:50%;"><small>Task Created From</small>
						<input name="search_task_from" class="datepicker form-control input-sm" type="text" placeholder="Date From" readonly
							   value="<?php if(isset($search_task_from)) : echo date('Y-m-d', strtotime($search_task_from)); endif; ?>">
					</label>
					
					<label class="pull-right" style="width:50%;"><small>Task Created To</small>
						<input name="search_task_to" class="datepicker form-control input-sm" type="text" placeholder="Date To" readonly
							   value="<?php if(isset($search_task_to)) : echo date('Y-m-d', strtotime($search_task_to)); endif; ?>">
					</label>
					<small class="pull-right resetDate">Reset</small>
				</td>
			</tr>	
			<tr>
				<td>
					<small>Select Service</small>
					<select name="search_task_categories" class="input-sm form-control" >
						<option value="">Task Service</option>
						<?php foreach($task_categories as $k=>$v) : ?>
							<option <?php if (isset($search_task_category) && $search_task_category == $v['category_id']): ?>selected="selected"<?php endif; ?> value="<?php echo $v['category_id'];?>"><?php echo $v['category_name'];?></option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<tr>
				<td>
					<small>Select Status</small>
					<select name="search_task_status" class="input-sm form-control" >
						<option value="">Task Status</option>
						<option <?php if(isset($search_task_status) && $search_task_status == 'new') : ?>selected="selected"<?php endif; ?> value="new">New</option>
						<option <?php if(isset($search_task_status) && $search_task_status == 'canceled') : ?>selected="selected"<?php endif; ?> value="canceled">Canceled</option>
						<option <?php if(isset($search_task_status) && $search_task_status == 'done') : ?>selected="selected"<?php endif; ?> value="done">Done</option>
					</select>
				</td>
			</tr>
			 
		</table>	
		<table class="table m-n leadsTable hide">
			<tr><td><strong>Leads</strong></td></tr>
			<tr>
				<td>
					<label class="pull-left" style="width:50%;"><small>Lead Created From</small>
						<input name="search_lead_from" class="datepicker form-control input-sm" type="text" placeholder="Date From" readonly
							   value="<?php if(isset($search_lead_from)) : echo date('Y-m-d', strtotime($search_lead_from)); endif; ?>">
					</label>
					
					<label class="pull-right" style="width:50%;"><small>Lead Created To</small>
						<input name="search_lead_to" class="datepicker form-control input-sm" type="text" placeholder="Date To" readonly
							   value="<?php if(isset($search_lead_to)) : echo date('Y-m-d', strtotime($search_lead_to)); endif; ?>">
					</label>
					<small class="pull-right resetDate">Reset</small>
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
			<tr>
				<td><small>Select Lead Work</small><small class="pull-right reset" >Reset</small>
					<select name="search_lead_work[]" multiple="" class="input-sm form-control" >
						<option <?php if ((isset($search_lead_work) && !empty($search_lead_work)) && array_search('tree_removal', $search_lead_work) !== FALSE) : ?>selected="selected"<?php endif; ?> value="tree_removal">Tree Removal</option>
						<option <?php if ((isset($search_lead_work) && !empty($search_lead_work)) && array_search('tree_pruning', $search_lead_work) !== FALSE) : ?>selected="selected"<?php endif; ?> value="tree_pruning">Tree Pruning, Trimming</option>
						<option <?php if ((isset($search_lead_work) && !empty($search_lead_work)) && array_search('hedge_maintenance', $search_lead_work) !== FALSE) : ?>selected="selected"<?php endif; ?> value="hedge_maintenance">Hedge/ Shrub Maintenance</option>
						<option <?php if ((isset($search_lead_work) && !empty($search_lead_work)) && array_search('wood_disposal', $search_lead_work) !== FALSE) : ?>selected="selected"<?php endif; ?>value="wood_disposal">Brush/ Wood Disposa</option>
						<option <?php if ((isset($search_lead_work) && !empty($search_lead_work)) && array_search('stump_removal', $search_lead_work) !== FALSE) : ?>selected="selected"<?php endif; ?> value="stump_removal">Stump Removal</option>
						<option <?php if ((isset($search_lead_work) && !empty($search_lead_work)) && array_search('root_fertilizing', $search_lead_work) !== FALSE) : ?>selected="selected"<?php endif; ?> value="root_fertilizing">Deep Root Fertilizing</option>
						<option <?php if ((isset($search_lead_work) && !empty($search_lead_work)) && array_search('spraying', $search_lead_work) !== FALSE) : ?>selected="selected"<?php endif; ?> value="spraying"> Spraying/ Fungicide Application</option>
						<option <?php if ((isset($search_lead_work) && !empty($search_lead_work)) && array_search('trunk_injection', $search_lead_work) !== FALSE) : ?>selected="selected"<?php endif; ?> value="trunk_injection" > Trunk Injection </option>
						<option <?php if ((isset($search_lead_work) && !empty($search_lead_work)) && array_search('air_spading', $search_lead_work) !== FALSE) : ?>selected="selected"<?php endif; ?> value="air_spading" > Air Spading </option>
						<option <?php if ((isset($search_lead_work) && !empty($search_lead_work)) && array_search('planting', $search_lead_work) !== FALSE) : ?>selected="selected"<?php endif; ?> value="planting" > Planting </option>
						<option <?php if ((isset($search_lead_work) && !empty($search_lead_work)) && array_search('arborist_consultation', $search_lead_work) !== FALSE) : ?>selected="selected"<?php endif; ?> value="arborist_consultation" > Arborist Consultation </option>
						<option <?php if ((isset($search_lead_work) && !empty($search_lead_work)) && array_search('arborist_report', $search_lead_work) !== FALSE) : ?>selected="selected"<?php endif; ?> value="arborist_report" > Regular Arborist Report </option>
						<option <?php if ((isset($search_lead_work) && !empty($search_lead_work)) && array_search('construction_arborist_report', $search_lead_work) !== FALSE) : ?>selected="selected"<?php endif; ?> value="construction_arborist_report" > Construction Arborist Report </option>
						<option <?php if ((isset($search_lead_work) && !empty($search_lead_work)) && array_search('tree_cabling', $search_lead_work) !== FALSE) : ?>selected="selected"<?php endif; ?> value="tree_cabling" > Cabling/ Bracing </option>
						<option <?php if ((isset($search_lead_work) && !empty($search_lead_work)) && array_search('tpz_installation', $search_lead_work) !== FALSE) : ?>selected="selected"<?php endif; ?> value="tpz_installation" > TPZ Installation </option>
						<option <?php if ((isset($search_lead_work) && !empty($search_lead_work)) && array_search('lights_installation', $search_lead_work) !== FALSE) : ?>selected="selected"<?php endif; ?> value="lights_installation" > Lights Installation </option>
						<option <?php if ((isset($search_lead_work) && !empty($search_lead_work)) && array_search('landscaping', $search_lead_work) !== FALSE) : ?>selected="selected"<?php endif; ?> value="landscaping" > Landscaping </option>
						<option <?php if ((isset($search_lead_work) && !empty($search_lead_work)) && array_search('emergency', $search_lead_work) !== FALSE) : ?>selected="selected"<?php endif; ?> value="emergency" > Emergency Work </option>
						<option <?php if ((isset($search_lead_work) && !empty($search_lead_work)) && array_search('snow_removal', $search_lead_work) !== FALSE) : ?>selected="selected"<?php endif; ?> value="snow_removal" > Snow Removal </option>
						<option <?php if ((isset($search_lead_work) && !empty($search_lead_work)) && array_search('other', $search_lead_work) !== FALSE) : ?>selected="selected"<?php endif; ?> value="other" > Other Services </option>

					</select>
				</td>
			</tr>
		</table>	
		<table class="table m-n estimatesTable hide">
			<tr><td><strong>Estimates</strong></td></tr>
			<tr>
				<td>
					<label class="pull-left" style="width:50%;"><small>Estimate Created From</small>
						<input name="search_estimate_from" class="datepicker form-control input-sm" type="text" placeholder="Date From" readonly
							   value="<?php if(isset($search_estimate_from)) : echo date('Y-m-d', strtotime($search_estimate_from)); endif; ?>">
					</label>
					
					<label class="pull-right" style="width:50%;"><small>Estimate Created To</small>
						<input name="search_estimate_to" class="datepicker form-control input-sm" type="text" placeholder="Date To" readonly
							   value="<?php if(isset($search_estimate_to)) : echo date('Y-m-d', strtotime($search_estimate_to)); endif; ?>">
					</label>
					<small class="pull-right resetDate">Reset</small>
				</td>
			</tr>		
			<tr>
				<td><small>Select Service</small>
					<select name="search_service_type" class="input-sm form-control" >
						<option value="">Select Service</option>
						<?php foreach($services as $k=>$v) : ?>
							<option <?php if (isset($search_service_type) && $search_service_type == $v->service_id): ?>selected="selected"<?php endif; ?> value="<?php echo $v->service_id;?>"><?php echo $v->service_name;?></option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<tr>
				<td>
					<label class="pull-left" style="width:50%;"><small>Estimate Price From</small>
						<input onchange="/*updateInput(sliderPrice, from, this.value)*/" style="width:90%" name="search_estimate_price_from" type="text" class="input-sm form-control" placeholder="Estimate Price From"
							value="<?php if(isset($search_estimate_price_from)) : echo $search_estimate_price_from; endif; ?>">
					</label>
					<label class="pull-right" style="width:50%;"><small>Estimate Price To</small>
						<input onchange="/*updateInput(sliderPrice, to, this.value)*/" style="width:90%" name="search_estimate_price_to" type="text" class="input-sm form-control" placeholder="Estimate Price To"
							value="<?php if(isset($search_estimate_price_to)) : echo $search_estimate_price_to; endif; ?>">
					</label>
					
				</td>
			</tr>
			<tr>
				<td>
					<label class="pull-left" style="width:50%;"><small>Service Price From</small>
						<input style="width:90%" name="search_service_price_from" type="text" class="input-sm form-control" placeholder="Service Price From"
							value="<?php if(isset($search_service_price_from)) : echo $search_service_price_from; endif; ?>">
					</label>
					<label class="pull-right" style="width:50%;"><small>Service Price To</small>
						<input style="width:90%" name="search_service_price_to" type="text" class="input-sm form-control" placeholder="Service Price To"
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
				<td><small>Select Estimate Status</small>
					<select name="search_status" class="input-sm form-control" >
						<option value="">Select Status</option>
						<?php foreach($statuses as $k=>$v) : ?>
							<option <?php if (isset($search_status) && $search_status == $v->est_status_id): ?>selected="selected"<?php endif; ?> value="<?php echo $v->est_status_id;?>"><?php echo $v->est_status_name;?></option>
						<?php endforeach; ?>
					</select>

				</td>
			</tr>
			<tr>
				<td><small>Estimate Description</small>
					<textarea name="search_estimate_description" type="text" class="input-sm form-control" placeholder="Description has..."
					   value="<?php if (isset($search_desc)) : echo $search_desc;  endif;?>"></textarea>
				</td>
			</tr>
		</table>
		<table class="table m-n workordersTable hide">
			<tr><td><strong>Workorders</strong></td></tr>
			<tr>
				<td>
					<label class="pull-left" style="width:50%;"><small>Workorders Created From</small>
						<input name="search_invoice_from" class="datepicker form-control input-sm" type="text" placeholder="Date From" readonly
							   value="<?php if(isset($search_workorder_from)) : echo date('Y-m-d', strtotime($search_workorder_from)); endif; ?>">
					</label>
					
					<label class="pull-right" style="width:50%;"><small>Workorders Created To</small>
						<input name="search_invoice_to" class="datepicker form-control input-sm" type="text" placeholder="Date To" readonly
							   value="<?php if(isset($search_workorder_to)) : echo date('Y-m-d', strtotime($search_workorder_to)); endif; ?>">
					</label>
					<small class="pull-right resetDate">Reset</small>
				</td>
			</tr>	
			<tr>
				<td> 
					<small>Select Status</small>
					<select name="search_workorder_status" class="input-sm form-control" >
						<option value="">Workorder Status</option>
						<?php foreach($wo_statuses as $k=>$v) : ?>
							<option <?php if (isset($search_workorder_status) && $search_workorder_status == $v['wo_status_id']): ?>selected="selected"<?php endif; ?> value="<?php echo $v['wo_status_id'];?>"><?php echo  $v['wo_status_name'];?></option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
		</table>
		<table class="table m-n invoicesTable hide">
			<tr><td><strong>Invoices</strong></td></tr>
			<tr>
				<td>
					<label class="pull-left" style="width:50%;"><small>Invoice Created From</small>
						<input name="search_invoice_from" class="datepicker form-control input-sm" type="text" placeholder="Date From" readonly
							   value="<?php if(isset($search_invoice_from)) : echo date('Y-m-d', strtotime($search_invoice_from)); endif; ?>">
					</label>
					
					<label class="pull-right" style="width:50%;"><small>Invoice Created To</small>
						<input name="search_invoice_to" class="datepicker form-control input-sm" type="text" placeholder="Date To" readonly
							   value="<?php if(isset($search_invoice_to)) : echo date('Y-m-d', strtotime($search_invoice_to)); endif; ?>">
					</label>
					<small class="pull-right resetDate">Reset</small>
				</td>
			</tr>	
			<tr>
				<td> 
					<small>Select Status</small>
					<select name="search_invoice_status" class="input-sm form-control" >
						<option value="">Invoice Status</option>
						<option <?php if(isset($search_invoice_status) && $search_invoice_status == 'Issued') : ?>selected="selected"<?php endif; ?> value="Issued">Issued</option>
						<option <?php if(isset($search_invoice_status) && $search_invoice_status == 'Sent') : ?>selected="selected"<?php endif; ?> value="Sent">Sent</option>
						<option <?php if(isset($search_invoice_status) && $search_invoice_status == 'Overdue') : ?>selected="selected"<?php endif; ?> value="Overdue">Overdue</option>
						<option <?php if(isset($search_invoice_status) && $search_invoice_status == 'Paid') : ?>selected="selected"<?php endif; ?> value="Paid">Paid</option>
					</select>
				</td>
			</tr>
			<tr>
				<td>
					<label class="pull-left" style="width:50%;"><small>Invoice Price From</small>
						<input onchange="/*updateInput(sliderPrice, from, this.value)*/" style="width:90%" name="search_invoice_price_from" type="text" class="input-sm form-control" placeholder="Invoice Price From"
							value="<?php if(isset($search_invoice_price_from)) : echo $search_invoice_price_from; endif; ?>">
					</label>
					<label class="pull-right" style="width:50%;"><small>Invoice Price To</small>
						<input onchange="/*updateInput(sliderPrice, to, this.value)*/" style="width:90%" name="search_invoice_price_to" type="text" class="input-sm form-control" placeholder="Invoice Price To"
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
<script>
	$(document).ready(function () {
		$('.datepicker').datepicker({format: 'yyyy-mm-dd'});
		$(document).on('click', '.dropdown-menu.animated.fadeInDown.searchFrom', function (e) {
			e.stopPropagation();
		});
		$('[name="search_by[]"]').on('change', function(){
			var value = $(this).val();
			$('#search').find('.table:not(:first)').addClass('hide');
			$.each(value, function(key, val){
				$('#search').find('.' + val + 'Table').removeClass('hide');
			});
		});
		$('.reset').on('click', function(){
			var obj = $(this);
			$(obj).parents('td:first').find('select').val('');
			if($(obj).hasClass('mainReset'))
				$(obj).parents('form').find(".table:not(:first)").addClass('hide');
			
		});
		$('.resetDate').on('click', function(){
			var obj = $(this);
			$(obj).parents('td:first').find('input').val('');
			
			
		});
		//onclick="$('.search_lead_work').val('')"
		 //onclick="$('.search_by').val('')"
	});
	/*setTimeout(function() {
		$('.sliderPrice').slider({
			min:0,
			max:10000,
			step:5,
			tooltip:'hide',
			value:[<?php echo isset($search_estimate_price_from) ? $search_estimate_price_from : 500; ?>, <?php echo isset($search_estimate_price_to) ? $search_estimate_price_to : 1500; ?>]
		}).on('slide', function(ev){
			$('input[name="search_estimate_price_from"]').val(ev.value[0]);
			$('input[name="search_estimate_price_to"]').val(ev.value[1]);
			console.log(ev);
			
		}).on('slideStop', function(ev){
			$('input[name="search_estimate_price_from"]').val(ev.value[0]);
			$('input[name="search_estimate_price_to"]').val(ev.value[1]);
		});
		$('.slider.slider-horizontal').css('width', '210px');
		$('.sliderServicePrice').slider({
			min:0,
			max:10000,
			step:5,
			tooltip:'hide',
			value:[<?php echo isset($search_service_price_from) ? $search_service_price_from : 500; ?>, <?php echo isset($search_estimate_price_to) ? $search_estimate_price_to : 1500; ?>]
		}).on('slide', function(ev){
			$('input[name="search_service_price_from"]').val(ev.value[0]);
			$('input[name="search_service_price_to"]').val(ev.value[1]);
			
			return false;
		});
		$('.slider.slider-horizontal').css('width', '210px');
	}, 3000);
	function updateInput(name, input, val){
		$('.' + name).value = val;
		if(input == 'from')
			value = [val, ]
		$('.' + name).slider({
			value:[]
		});
	}*/
</script>
