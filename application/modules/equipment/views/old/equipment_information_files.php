<!--Modal-->
<div id="new_item" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content panel panel-default p-n">
			<header class="panel-heading">Create new Item for&nbsp;<?php echo $group_data->group_name; ?></header>
			<?php echo form_open(base_url() . "equipments/create_item", array('id' => 'form_item')) ?>
			<!-- Client Files Header-->
			<div class="modal-body">
				<div class="p-5">
					<?php  $new_item_name_options = array(
						'name' => 'new_item_name',
						'value' => set_value('new_item_name'),
						'placeholder' => 'New Item Name',
						'class' => 'form-control',
						'id' => 'new_item_name');
					?>
					<?php echo form_input($new_item_name_options) ?>
				</div>
				<div class="p-5">
					<?php $options = array();
					foreach ($groups as $group) {
						$options[$group->group_id] = $group->group_name;
					}
					?>
					<?php echo form_dropdown('group_id', $options, $group_data->group_id, 'class="form-control"'); ?>
				</div>
				<div class="p-5">
					<?php  $new_item_serial = array(
						'name' => 'new_item_serial',
						'value' => set_value('new_item_serial'),
						'placeholder' => 'New Item Serial',
						'class' => 'form-control',
						'id' => 'new_item_serial');  ?>
					<?php echo form_input($new_item_serial) ?>
				</div>
				<div class="p-5">
					<?php  $new_item_code_options = array(
						'name' => 'new_item_code',
						'value' => set_value('new_item_code'),
						'placeholder' => 'New Item Code',
						'class' => 'form-control',
						'id' => 'new_item_code');  ?>
					<?php echo form_input($new_item_code_options) ?>
				</div>
				<div class="p-5">
					<?php  $options = array(
						'name' => 'new_item_description',
						'rows' => '5',
						'class' => 'form-control',
						'placeholder' => 'New Item Description. Get as much info as possible...',
						'value' => set_value('new_item_description'),
						'id' => 'new_item_desc');
					?>

					<?php echo form_textarea($options) ?>
				</div>
				<div class="p-5">
					<?php  $new_item_code_options = array(

						'name' => 'new_item_date',
						"data-date-format" => "yyyy-mm-dd",
						'value' => set_value('new_item_date'),
						'placeholder' => 'New Item Date',
						'class' => 'form-control',
						'id' => 'new_item_date');
					?>
					<?php echo form_input($new_item_code_options) ?>
				</div>
				<div class="p-5">
					<?php  $new_item_tracker_name_options = array(

						'name' => 'new_item_tracker_name',
						'value' => set_value('new_item_tracker_name'),
						'placeholder' => 'New Item Tracker Name',
						'class' => 'form-control',
						'id' => 'new_item_tracker_name');
					?>
					<?php echo form_input($new_item_tracker_name_options) ?>
				</div>
				<div class="p-5">
					<label>Schedule Item: </label>
					<?php  $new_item_schedule = array(
						'name'        => 'new_item_schedule',
						'value'       => '1',
						'style'       => 'margin-left:10px',
						'checked'     => FALSE,
					);
					echo form_checkbox($new_item_schedule) ?>
				</div>
			</div>
			<div class="modal-footer">
				<button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
				<button class='btn btn-info' name="submit" id="submitBtn">Add new item</button>
			</div>
		</div>
	</div>
</div>
<?php echo form_close() ?>


<!--Content-->
<section class="col-sm-12 panel panel-default p-n">
	<header class="panel-heading"><?php echo $group_data->group_name; ?> Items
		<a href="#new_item" role="button" class="btn btn-info btn-xs pull-right" data-toggle="modal"><i
				class="fa fa-plus"></i></a>
	</header>

	<!-- Client Files Data -->
	<table class="table table-hover">
		<thead>
		<tr>
			<th width="350px">Item Name</th>
			<th width="100px">Item Code</th>
			<th width="180px">Item Serial</th>
			<th width="200px">Item Description</th>
			<th width="100px">Item Date</th>
			<th width="110px">Item Repair</th>
			<th class="text-center" width="170px">Action</th>
		</tr>
		</thead>
		<tbody>

		<!-- Items -->
		<?php if ($group_items) { ?>
			<?php foreach ($group_items as $row) { ?>
				<tr>
					<td>
						<a href="<?php echo base_url('equipments/profile') . '/' . $row['item_id']; ?>">
							<?php echo $row['item_name'] ? $row['item_name'] : 'N/A'; ?>
						</a>
					</td>
					<td><?php echo $row['item_code'] ? $row['item_code'] : 'N/A'; ?></td>
					<td><?php echo $row['item_serial'] ? $row['item_serial'] : 'N/A'; ?></td>
					<td>
						<div style="white-space: pre-line; max-width: 200px; margin-top: -20px;">
							<?php echo $row['item_description'] ? $row['item_description'] : 'N/A'; ?>
						</div>
					</td>
					<td><?php echo $row['item_date'] ? date(getDateFormat(), $row['item_date']) : 'N/A'; ?></td>
					<td class="text-center">
						<a href="#" class="btn btn-xs checkRepair btn-<?php echo $row['item_repair'] ? 'danger' : 'success'; ?>" data-item_id="<?php echo $row['item_id']; ?>">
							<i style="color: #fff;" class="fa fa-<?php echo $row['item_repair'] ? 'times' : 'check'; ?>"></i>
						</a>
					</td>

					<td class="">

						<!-- Modal -->
						<div id="<?php echo $row['item_id']; ?>" class="modal fade" tabindex="-1" role="dialog"
						     aria-labelledby="myModalLabel" aria-hidden="true">
							<div class="modal-dialog" style="width:1080px;">
								<div class="modal-content panel panel-default p-n">
									<header class="panel-heading">Item
										for&nbsp;<?php echo $group_data->group_name; ?></header>
									<div class="modal-body">
										<?php $item['row'] = $row;
										$this->load->view('equipments/items/equipment_info', $item); ?>
										<a href="#" class="btn btn-warning activate" data-type="services"
										   data-item_id="<?php echo $row['item_id']; ?>">Services</a>
										<a href="#" class="btn btn-primary activate" data-type="parts"
										   data-item_id="<?php echo $row['item_id']; ?>">Parts</a>

										<div id="services_<?php echo $row['item_id']; ?>" class="slide"
										     style="display:none"></div>
										<div id="parts_<?php echo $row['item_id']; ?>" class="slide"
										     style="display:none"></div>
									</div>
									<div class="modal-footer">
										<button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
									</div>
								</div>
							</div>
						</div>

						<div id="item_edit_<?php echo $row['item_id']; ?>" class="modal fade" tabindex="-1"
						     role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
							<div class="modal-dialog">
								<div class="modal-content">
									<header class="panel-heading">Edit Item
										for&nbsp;<?php echo $group_data->group_name; ?></header>
									<?php echo form_open(base_url() . "equipments/item_edit/" . $row['item_id'], array('id' => 'itemForm_' . $row['item_id'])) ?>
									<!-- Client Files Header-->
									<div class="modal-body">
										<div class="p-5">
											<?php  $edit_item_name_options = array(
												'name' => 'edit_item_name_' . $row['item_id'],
												'value' => $row['item_name'],
												'placeholder' => 'Edit Item Name',
												'class' => 'form-control',
												'id' => 'edit_item_name_' . $row['item_id']);
											?>
											<?php echo form_input($edit_item_name_options) ?>
										</div>
										<div class="p-5">
											<?php $options = array();
											foreach ($groups as $group) {
												$options[$group->group_id] = $group->group_name;
											}
											?>
											<?php echo form_dropdown('edit_item_group_' . $row['item_id'], $options, $row['group_id'], 'class="form-control"'); ?>
										</div>
										<div class="p-5">
											<?php  $edit_item_serial_options = array(
												'name' => 'edit_item_serial_' . $row['item_id'],
												'value' => $row['item_serial'],
												'placeholder' => 'Edit Item Serial',
												'class' => 'form-control',
												'id' => 'edit_item_name_' . $row['item_id']);
											?>
											<?php echo form_input($edit_item_serial_options) ?>
										</div>
										<div class="p-5">
											<?php  $edit_item_code_options = array(
												'name' => 'edit_item_code_' . $row['item_id'],
												'value' => $row['item_code'],
												'placeholder' => 'Edit Item Code',
												'class' => 'form-control',
												'id' => 'edit_item_code_' . $row['item_id']);  ?>
											<?php echo form_input($edit_item_code_options) ?>
										</div>
										<div class="p-5">
											<?php  $edit_options = array(
												'name' => 'edit_item_description_' . $row['item_id'],
												'rows' => '5',
												'class' => 'form-control',
												'placeholder' => 'Edit Item Description. Get as much info as possible...',
												'value' => $row['item_description'],
												'id' => 'edit_item_description_' . $row['item_id']);
											?>
											<?php echo form_textarea($edit_options) ?>
										</div>
										<div class="p-5">
											<?php  $edit_item_code_options = array(

												'name' => 'edit_item_date_' . $row['item_id'],
												"data-date-format" => "yyyy-mm-dd",
												'value' => $row['item_date'] ? date(getDateFormat(), $row['item_date']) : "",
												'placeholder' => 'Edit Item Date',
												'class' => 'form-control',
												'id' => 'edit_item_date_' . $row['item_id'],

											);
											/*if($row['item_maintenance']=='No'){
												  $edit_item_code_options['disabled']='disable';
											  }*/
											?>
											<?php echo form_input($edit_item_code_options) ?>
										</div>
										<div class="p-5">
											<?php  $edit_item_tracker_name = array(

												'name' => 'edit_item_tracker_name_' . $row['item_id'],
												'value' => $row['item_tracker_name'],
												'placeholder' => 'Edit Item Tracker Name',
												'class' => 'form-control',
												'id' => 'edit_item_tracker_name_' . $row['item_id'],

											);
											echo form_input($edit_item_tracker_name) ?>
										</div>
										<div class="p-5 text-left">
											<label>Schedule Item: </label>
											<?php  $edit_item_schedule = array(
												'name'        => 'edit_item_schedule_' . $row['item_id'],
												'value'       => '1',
												'style'       => 'margin-left:10px',
												'checked'     => $row['item_schedule'] ? TRUE : FALSE,
											);
											echo form_checkbox($edit_item_schedule) ?>
										</div>
										<div class="p-5 text-left">
											<label>Repair Item: </label>
											<?php  $edit_item_repair = array(
												'name'        => 'edit_item_repair_' . $row['item_id'],
												'value'       => '1',
												'style'       => 'margin-left:10px',
												'checked'     => $row['item_repair'] ? TRUE : FALSE,
											);
											echo form_checkbox($edit_item_repair) ?>
										</div>
										<div class="p-5">

											<?php /*$hidden = array(         'group_id'         =>    $group_data->group_id);
          if( $row['item_maintenance']=='No'){
                  $options['disabled']='disable';
              }*/
											?>

											<?php //echo form_hidden($hidden);?>
										</div>
									</div>
									<div class="modal-footer">
										<button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>
										<button class='btn btn-info' name="submit"
										        onclick="update_itemForm('<?php echo $row['item_id']; ?>'); return false;">
											Update Item
										</button>

									</div>
								</div>
							</div>

						</div>
						<?php echo form_close() ?>
						<!--Triggers -->
						
						<?php
						if (isAdmin()) : ?>
							<a href="<?php echo base_url() . 'equipments/item_delete/' . $row['item_id'] . '/' . $group_data->group_id; ?>"
							class="btn btn-xs btn-danger pull-right m-l-sm item-delete"><i class="fa fa-trash-o"></i>
							</a>	
						<?php //echo anchor('equipments/item_delete/' . $row['item_id'] . '/' . $group_data->group_id, '<i class="fa fa-trash-o"></i>', 'class="btn btn-xs btn-danger pull-right m-l-sm"');    
						endif; ?>
						
						<a href="#addRepair<?php echo '-'.$row['item_id'];?>" role="button" class="btn btn-warning btn-xs m-l-sm pull-right" data-toggle="modal"><i class="fa fa-wrench"></i></a>
						<?php $this->load->view('equipments/repairs/add_repair_form'); ?>

						<a href="#<?php echo 'item_edit_' . $row['item_id']; ?>" role="button"
						   class="btn btn-default btn-xs pull-right m-l-sm" data-toggle="modal"><i class="fa fa-pencil"></i></a>	

						<a href="<?php echo base_url('equipments/profile') . '/' . $row['item_id']; ?>" role="button" class="btn btn-default btn-xs pull-right"
						   data-toggle="modal"><i class="fa fa-eye"></i></a>
					</td>
				</tr>
			<?php } ?>
		<?php } else { ?>
		<?php } ?>

		<tbody>
	</table>
    <input type="hidden" id="php-variable" value="<?php echo getJSDateFormat()?>" />
</section>
</section>

<script type="text/javascript">
var types = <?php echo json_encode($service_types); ?>;
var Gname = '';
var Gdescr = '';
var Gperiod = '';
$('.item-delete').click(function(){
	if(confirm('Are you sure?'))
	{
		location.href = $(this).attr('href');
	}
	return false;
});
$("document").ready(function () {
	
	$('#new_item_date').datepicker({format: $('#php-variable').val()});
	$('[data-date-format="yyyy-mm-dd"]').datepicker({format: $('#php-variable').val()});
	$('.activate').click(function () {
		var type = $(this).data('type');
		var item_id = $(this).data('item_id');
		var element = type + '_' + item_id;
		if (!$('#' + element).html()) {
			$.post(baseUrl + 'equipments/ajax_item_' + type, {item_id: item_id}, function (resp) {
				$('#' + element).html(resp.html);
				$('.slide').slideUp();
				$('#' + element).slideDown();
			}, 'json');
		}
		else {
			if ($('#' + element).is(':hidden')) {
				$('.slide').slideUp();
				$('#' + element).slideDown();
			}
			else {
				$('.slide').slideUp();
			}
		}
	});
	$(document).on('click', '.editService', function () {
		$('#cancelEdit').click();
		$('#cancelAdd').click();
		var service_id = $(this).parent().parent().data('service_id');
		var service_type_id = $(this).parent().parent().data('service_type_id');
		var item_id = $(this).parent().parent().data('item_id');
		Gname = $(this).text();
		//var date = $('[data-service_id="' + service_id + '"]' + ' .date').text();
		//var next = $('[data-service_id="' + service_id + '"]' + ' .next').text();
		Gdescr = $('[data-service_id="' + service_id + '"]' + ' .descr').text();
		Gperiod = parseInt($('[data-service_id="' + service_id + '"]' + ' .period').text());
		select = '<select id="serviceName" class="span1">';
		$.each(types, function(key,val){
			selected = "";
			if(val.equipment_service_id == service_type_id)
				selected = 'selected="selected"';
			select += '<option value="' + val.equipment_service_id + '" ' + selected + '>' + val.equipment_service_type + '</option>';
		});
		select += '</select>';
		$('[data-service_id="' + service_id + '"]' + ' .name').html(select);
		//$('[data-service_id="' + service_id + '"]' + ' .date').html('<input type="text" id="serviceDate" class="input-small form-control" value="' + date + '">');
		//$('[data-service_id="' + service_id + '"]' + ' .next').html('<input type="text" id="serviceNext" class="input-small form-control" value="' + next + '">');
		$('[data-service_id="' + service_id + '"]' + ' .period').html('<select id="servicePeriod" class="span1"><option selected value="">—</option><option value="1">One Month</option><option value="3">Three Month</option><option value="6">Six Month</option><option value="12">One Year</option><option value="24">Two Years</option><option value="36">Three Years</option></select>');
		if (Gperiod)
			$('#servicePeriod').val(Gperiod);
		$('[data-service_id="' + service_id + '"]' + ' .descr').html('<textarea id="serviceDescr" class="form-control" rows="2">' + Gdescr + '</textarea>');
		$('[data-service_id="' + service_id + '"]' + ' .action').html('<a href="#" class="saveService">Save</a><br><a href="#" id="cancelEdit">Cancel</a>');
		return false;
	});
	$(document).on('click', '.addService', function () {
		$('#cancelEdit').click();
		$('#cancelAdd').click();
		var obj = $(this).parent().parent().parent().next();
		var item_id = $(this).data('item_id');
		$(obj).prepend('<tr id="addServiceRow" data-item_id="' + item_id + '"></tr>');
		select = '<td><select id="serviceName" class="form-control">';
		$.each(types, function(key,val){
			if(key == 0)
				select += '<option value="" selected="selected">—</option>';
			select += '<option value="' + val.equipment_service_id + '">' + val.equipment_service_type + '</option>';
		});
		select += '</select></td>';
		
		$('#addServiceRow').append(select);
		$('#addServiceRow').append('<td><select id="servicePeriod" class="form-control"><option selected value="">—</option><option value="1">One Month</option><option value="3">Three Month</option><option value="6">Six Month</option><option value="12">One Year</option><option value="24">Two Years</option><option value="36">Three Years</option></select></td>');
		$('#addServiceRow').append('<td><input type="text" id="serviceDate" class="input-small form-control" value="' + getDate() + '"></td>');
		$('#addServiceRow').append('<td><input type="text" id="serviceNext" class="input-small form-control" value=""></td>');
		$('#addServiceRow').append('<td>new</td>');
		$('#addServiceRow').append('<td><textarea id="serviceDescr" class="form-control" rows="2"></textarea></td>');
		$('#addServiceRow').append('<td style="text-align:center;"><a href="#" class="saveService">Add</a><br><a href="#" id="cancelAdd">Cancel</a></td>');
		$('#serviceDate')
			.datepicker({format: $('#php-variable').val()})
			.on('changeDate', function (ev) {
				$(this).datepicker('hide');
				if ($('#servicePeriod').val() != '—')
					$('#serviceNext').val(getDate($('#serviceDate').val(), parseInt($('#servicePeriod').val())));
			});
		$('#serviceNext')
			.datepicker({format: $('#php-variable').val()})
			.on('changeDate', function (ev) {
				$(this).datepicker('hide');
			});
		return false;
	});
	$(document).on('click', '#cancelAdd', function () {
		$('#addServiceRow').remove();
	});
	$(document).on('click', '.saveService', function () {
		var obj = $('.saveService').parent().parent();
		var service_id = $(obj).data('service_id');
		var item_id = $('.saveService').parent().parent().data('item_id');
		var type_id = $('#serviceName').val();
		var date = $('#serviceDate').val();
		var next = $('#serviceNext').val();
		var descr = $('#serviceDescr').val();
		var period = $('#servicePeriod').val();
		if (service_id && period != Gperiod)
			next = getDate($(obj).children('.date').text(), parseInt(period));
		$.post(baseUrl + 'equipments/ajax_save_service', {service_id: service_id, item_id: item_id, type_id: type_id, date: date, next: next, descr: descr, period: period}, function (resp) {
			if (resp.status == 'ok') {
				$('.saveService').parent().parent().parent().find('td[colspan="5"]').parent().remove();
				$('.saveService').parent().parent().parent().find('td[colspan="7"]').parent().remove();
			}
			if (resp.status == 'ok' && typeof(service_id) !== 'undefined')
				$('[data-service_id="' + service_id + '"]').replaceWith(resp.html);
			if (resp.status == 'ok' && typeof(service_id) === 'undefined') {
				$('.saveService').parent().parent().parent().prepend(resp.html);
				$('#cancelAdd').click();
			}
			if (resp.status == 'error')
				alert('Error');
		}, 'json');
	});
	$(document).on('click', '#cancelEdit', function () {
		var service_id = $(this).parent().parent().data('service_id');
		$('[data-service_id="' + service_id + '"]' + ' .name').html('<a href="#" class="editService" data-item_id="' + service_id + '">' + Gname + '</a>');
		//$('[data-service_id="' + service_id + '"]' + ' .date').html(date);
		//$('[data-service_id="' + service_id + '"]' + ' .next').html(next);
		$('[data-service_id="' + service_id + '"]' + ' .descr').html(Gdescr);
		if (!Gperiod)
			Gperiod = 'N/A';
		$('[data-service_id="' + service_id + '"]' + ' .period').html(Gperiod);
		$('[data-service_id="' + service_id + '"]' + ' .action').html('<a href="#" class="btn btn-danger btn-xs deleteService" style="margin-top:5px;" data-service_id="' + service_id + '"><i class="fa fa-trash-o icon-white"></i></a>');
		return false;
	});
	$(document).on('click', '.deleteService', function () {
		var service_id = $(this).data('service_id');
		$.post(baseUrl + 'equipments/ajax_delete_service', {service_id: service_id}, function (resp) {
			if (resp.status == 'ok') {
				$('[data-service_id="' + service_id + '"]').fadeOut(function () {
					$('[data-service_id="' + service_id + '"]').remove();
				});
			}
		}, 'json');
		return false;
	});
	$(document).on('change', '#servicePeriod', function () {
		var obj = $(this).parent().parent();
		if ($('#servicePeriod').val() != '—') {
			var start = getDate();
			if ($('#serviceDate').val())
				start = $('#serviceDate').val();
			if ($('#serviceNext').length) {
				$('#serviceNext').val(getDate(start, parseInt($('#servicePeriod').val())));
				$('#serviceNext').attr('disabled', 'disabled');
			}
			else {
				$(obj).children('.next').text(getDate($(obj).children('.date').text(), parseInt($('#servicePeriod').val())));
			}
		}
		else
			$('#serviceNext').removeAttr('disabled');
	});
	$(document).on('click', '.editPart', function () {
		$('#cancelEditPart').click();
		$('#cancelAddPart').click();
		var part_id = $(this).parent().parent().data('part_id');
		var item_id = $(this).parent().parent().data('item_id');
		var name = $(this).text();
		var seller = $('[data-part_id="' + part_id + '"]' + ' .seller').text();
		var price = $('[data-part_id="' + part_id + '"]' + ' .price').text();
		var date = $('[data-part_id="' + part_id + '"]' + ' .date').text();
		$('[data-part_id="' + part_id + '"]' + ' .name').html('<input type="text" id="partName" class="input-small form-control" value="' + name + '">');
		$('[data-part_id="' + part_id + '"]' + ' .date').html('<input type="text" id="partDate" class="input-small form-control" value="' + date + '">');
		$('[data-part_id="' + part_id + '"]' + ' .price').html('<input type="text" id="partPrice" class="input-small form-control" value="' + price + '">');
		$('[data-part_id="' + part_id + '"]' + ' .seller').html('<textarea id="partSeller" class="form-control" rows="2">' + seller + '</textarea>');
		$('[data-part_id="' + part_id + '"]' + ' .action').html('<a href="#" class="savePart">Save</a><br><a href="#" id="cancelEditPart">Cancel</a>');
		$('#partDate').datepicker({ format: $('#php-variable').val() });
		return false;
	});
	$(document).on('click', '.addPart', function () {
		$('#cancelEditPart').click();
		$('#cancelAddPart').click();
		var obj = $(this).parent().parent().parent().next();
		var item_id = $(this).data('item_id');
		$(obj).prepend('<tr id="addPartRow" data-item_id="' + item_id + '"></tr>');
		$('#addPartRow').append('<td><input type="text" id="partName" class="input-small form-control" value=""></td>');
		$('#addPartRow').append('<td><textarea id="partSeller" class="form-control" rows="2"></textarea></td>');
		$('#addPartRow').append('<td><input type="text" id="partPrice" class="input-small form-control" value=""></td>');
		$('#addPartRow').append('<td><input type="text" id="partDate" class="input-small form-control" value="' + getDate() + '"></td>');
		$('#addPartRow').append('<td style="text-align:center;"><a href="#" class="savePart">Add</a><br><a href="#" id="cancelAddPart">Cancel</a></td>');
		$('#partDate').datepicker({ format: $('#php-variable').val() });
		return false;
	});
	$(document).on('click', '#cancelAddPart', function () {
		$('#addPartRow').remove();
	});
	$(document).on('click', '.savePart', function () {
		var part_id = $('.savePart').parent().parent().data('part_id');
		var item_id = $('.savePart').parent().parent().data('item_id');
		var name = $('#partName').val();
		var date = $('#partDate').val();
		var seller = $('#partSeller').val();
		var price = $('#partPrice').val();
		$.post(baseUrl + 'equipments/ajax_save_part', {part_id: part_id, item_id: item_id, name: name, date: date, seller: seller, price: price}, function (resp) {
			if (resp.status == 'ok')
				$('.savePart').parent().parent().parent().find('td[colspan="5"]').parent().remove();
			if (resp.status == 'ok' && typeof(part_id) !== 'undefined')
				$('[data-part_id="' + part_id + '"]').replaceWith(resp.html);
			if (resp.status == 'ok' && typeof(part_id) === 'undefined') {
				$('.savePart').parent().parent().parent().prepend(resp.html);
				$('#cancelAddPart').click();
			}
			if (resp.status == 'error')
				alert('Error');
		}, 'json');
	});
	$(document).on('click', '#cancelEditPart', function () {
		var part_id = $(this).parent().parent().data('part_id');
		var name = $('#partName').val();
		var date = $('#partDate').val();
		var seller = $('#partSeller').val();
		var price = $('#partPrice').val();
		$('[data-part_id="' + part_id + '"]' + ' .name').html('<a href="#" class="editPart" data-item_id="' + part_id + '">' + name + '</a>');
		$('[data-part_id="' + part_id + '"]' + ' .date').html(date);
		$('[data-part_id="' + part_id + '"]' + ' .seller').html(seller);
		$('[data-part_id="' + part_id + '"]' + ' .price').html(price);
		$('[data-part_id="' + part_id + '"]' + ' .action').html('<a href="#" class="btn btn-danger btn-xs deletePart" style="margin-top:5px;" data-part_id="' + part_id + '"><i class="icon-trash icon-white"></i></a>');
		return false;
	});
	$(document).on('click', '.deletePart', function () {
		var part_id = $(this).data('part_id');
		$.post(baseUrl + 'equipments/ajax_delete_part', {part_id: part_id}, function (resp) {
			if (resp.status == 'ok') {
				$('[data-part_id="' + part_id + '"]').fadeOut(function () {
					$('[data-part_id="' + part_id + '"]').remove();
				});
			}
		}, 'json');
		return false;
	});
	$(document).on('click', 'td.day', function () {
		$('.datepicker:visible').hide();
	});


	$('#submitBtn').click(function () {
		var item_name = checkEmpty('new_item_name');
		var item_code = checkEmpty('new_item_code');

		if (item_name && item_code) {
			$('#form_item').submit();
		} else {
			return false;
		}
	});
});
function update_itemForm(id) {
	var item_name = checkEmpty('edit_item_name_' + id);
	var item_code = checkEmpty('edit_item_code_' + id);
	if (item_name && item_code) {
		$('#itemForm_' + id).subsmit();
	} else {
		return false;
	}
}
function item_picker(id) {
	$('#' + id).datepicker();
	$('#' + id).on('changeDate', function (ev) {
		//close when viewMode='0' (days)
		if (ev.viewMode === 'days') {
			$('#' + id).datepicker('hide');
		}
	});
}

function showError(id) {
	$("#" + id).css("background", "none repeat scroll 0 0 #F2DEDE");
	$("#" + id).css("box-shadow", "0 1px 1px rgba(0, 0, 0, 0.075) inset");
	$("#" + id).css("border-color", "#B94A48");
}
function hideError(id) {
	$("#" + id).css("background", "none repeat scroll 0 0 #DFF0D8");
	$("#" + id).css("box-shadow", "0 1px 1px rgba(0, 0, 0, 0.075) inset");
	$("#" + id).css("border-color", "#468847");
}
function checkEmpty(id) {
	var field = $('#' + id).val();
	if (field == '') {
		showError(id);
		return false;
	}
	hideError(id);
	return true;
}
function getDate(fromDate, plusMonths) {
	plusMonths = plusMonths ? plusMonths : 0;
	fromDate = fromDate ? fromDate : 0;
	if (fromDate)
		var date = new Date(fromDate);
	else
		var date = new Date();
	var yyyy = date.getFullYear().toString();
	var mm = (date.getMonth() + 1 + plusMonths).toString();
	var dd = date.getDate().toString();
	var mmChars = mm.split('');
	if (mmChars[1] && mm > 12) {
		while (mmChars[1] && mm > 12) {
			mm -= 12;
			mm = mm.toString();
			delete mmChars;
			var mmChars = mm.split('');
			yyyy = (parseInt(yyyy) + 1).toString();
		}
	}
	var ddChars = dd.split('');
	var datestring = yyyy + '-' + (mmChars[1] ? mm : "0" + mmChars[0]) + '-' + (ddChars[1] ? dd : "0" + ddChars[0]);
	return datestring;
}
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
$(document).on('click', '.checkRepair', function(){
	var obj = $(this);
	var itemId = $(this).data('item_id');
	var repair = $(this).children().is('.fa-times') ? 0 : 1;
	$.post(baseUrl + 'equipments/ajax_repair_item', {item_id:itemId,item_repair:repair}, function(resp){
		if(resp.status == 'ok')
		{
			var remove = 'fa-check';
			var parentRemove = 'btn-success';
			var parentAdd = 'btn-danger';
			var newClass = 'fa-times';
			if(!repair)
			{
				remove = 'fa-times';
				newClass = 'fa-check';
				var parentRemove = 'btn-danger';
				var parentAdd = 'btn-success';
			}
			$(obj).find('i').addClass(newClass).removeClass(remove);
			$(obj).removeClass(parentRemove).addClass(parentAdd);
		}
	}, 'json');
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
					$('#fileToUpload_' + equipment_id).parent().parent().prev().append('<div><div class="divider-bottom p-top-5 m-bottom-5"></div>File: <a target="_blank"' + lightbox + 'href="' + baseUrl + data.filepath + '">' + data.filename + '</a><a class="btn btn-danger btn-xs pull-right delete_image" data-item_id="' + equipment_id + '" data-filename="' + data.filename + '" aria-hidden="true"><i class="icon-trash icon-white"></i></a></div>');
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
