<?php $this->load->view('includes/header'); ?>
<!-- Services -->

<section class="scrollable p-sides-15">
	<ul class="breadcrumb no-border no-radius b-b b-light pull-in">
		<li><a href="<?php echo base_url(); ?>"><i class="fa fa-home"></i> Home</a></li>
		<li><a href="<?php echo base_url('equipments'); ?>">Equipments</a></li>
		<li><a href="<?php echo base_url('equipments/details/' . $item[0]->group_id); ?>"><?php echo $item[0]->group_name; ?></a></li>
		<li class="active"><?php echo $item[0]->item_name; ?></li>
	</ul>

	<section class="col-sm-12 panel panel-default p-n">
		<header class="panel-heading">
			<p>
			Item Name: "<?php echo $item[0]->item_name; ?>"<br>

			Item Serial: "<?php echo $item[0]->item_serial; ?>"<br>

			Item Description: "<?php if ($item[0]->item_description) :
				echo $item[0]->item_description;
			else :
				echo 'N/A';
			endif; ?>"
			<?php if(isset($kmrs) && $kmrs && $item[0]->item_gps_start_counter != NULL) : ?>
			<br>Item GPS Counter: <?php echo ($item[0]->item_gps_start_counter + $kmrs); ?> km
			<?php endif; ?>
			<a href="#<?php echo 'item_edit_' . $item[0]->item_id; ?>" role="button"
						   class="btn btn-default btn-xs pull-right m-l-sm" data-toggle="modal"><i class="fa fa-pencil"></i>
			</a>
			<?php if(isset($item[0]->item_tracker_name) && $item[0]->item_tracker_name != '' && $item[0]->item_tracker_name != NULL) : ?>
				<a href="#gpsData" role="button"
							   class="btn btn-default btn-xs pull-right m-l-sm" data-toggle="modal"><i class="fa fa-truck"></i>
				</a>
			<?php endif; ?>
				<a href="#item_files_<?php echo $item[0]->item_id; ?>" 
							class="btn btn-default btn-xs pull-right m-r-xs" role="button" data-toggle="modal"><i class="fa fa-file-o"></i></a>
				<div id="item_files_<?php echo $item[0]->item_id; ?>" class="modal fade overflow"
					 tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
					<div class="modal-dialog" style="width: 900px;">
						<div class="modal-content panel panel-default p-n">
							<header class="panel-heading">Service
								for <?php echo $item[0]->item_name; ?></header>
							<div class="modal-body">
								<div class="p-10">
									<?php $item['row'] = (array) $item[0];
									$this->load->view('equipments/items/equipment_info', $item); ?>
								</div>
							</div>
							<div class="modal-footer">
								<button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
							</div>
						</div>
					</div>
				</div>	
			</p>
			
			<div id="item_edit_<?php echo $item[0]->item_id; ?>" class="modal fade" tabindex="-1"
					 role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
					<div class="modal-dialog">
						<div class="modal-content">
							<header class="panel-heading">Edit Item
								for&nbsp;<?php echo $group_data->group_name; ?></header>
							<?php echo form_open(base_url() . "equipments/item_edit/" . $item[0]->item_id, array('id' => 'itemForm_' . $item[0]->item_id)) ?>
							<!-- Client Files Header-->
								<div class="modal-body">
									<div class="p-5">
										<?php  $edit_item_name_options = array(
											'name' => 'edit_item_name_' . $item[0]->item_id,
											'value' => $item[0]->item_name,
											'placeholder' => 'Edit Item Name',
											'class' => 'form-control',
											'id' => 'edit_item_name_' . $item[0]->item_id);
										?>
										<?php echo form_input($edit_item_name_options) ?>
									</div>
									<div class="p-5">
										<?php $options = array();
										foreach ($groups as $group) {
											$options[$group['group_id']] = $group['group_name'];
										}
										?>
										<?php echo form_dropdown('edit_item_group_' . $item[0]->item_id, $options, $item[0]->group_id, 'class="form-control"'); ?>
									</div>
									<div class="p-5">
										<?php  $edit_item_serial_options = array(
											'name' => 'edit_item_serial_' . $item[0]->item_id,
											'value' => $item[0]->item_serial,
											'placeholder' => 'Edit Item Serial',
											'class' => 'form-control',
											'id' => 'edit_item_name_' . $item[0]->item_id);
										?>
										<?php echo form_input($edit_item_serial_options) ?>
									</div>
									<div class="p-5">
										<?php  $edit_item_code_options = array(
											'name' => 'edit_item_code_' . $item[0]->item_id,
											'value' => $item[0]->item_code,
											'placeholder' => 'Edit Item Code',
											'class' => 'form-control',
											'id' => 'edit_item_code_' . $item[0]->item_id);  ?>
										<?php echo form_input($edit_item_code_options) ?>
									</div>
									<div class="p-5">
										<div class="row">
											<div class="col-sm-6">
												<?php  $edit_item_counter_options = array(
													'name' => 'edit_item_counter_hours_' . $item[0]->item_id,
													'value' => $item[0]->counter_hours,
													'placeholder' => 'Hours Counter',
													'class' => 'form-control',
													'id' => 'edit_item_counter_hours_' . $item[0]->item_id);  ?>
												<?php echo form_input($edit_item_counter_options) ?>
											</div>
											<div class="col-sm-6">
												<?php  $edit_item_counter_options = array(
													'name' => 'edit_item_counter_kilometers_' . $item[0]->item_id,
													'value' => $item[0]->counter_kilometers,
													'placeholder' => 'Kilometers Counter',
													'class' => 'form-control',
													'id' => 'edit_item_counter_kilometers_' . $item[0]->item_id);  ?>
												<?php echo form_input($edit_item_counter_options) ?>
											</div>
										</div>
									</div>
									<div class="p-5">
										<?php  $edit_options = array(
											'name' => 'edit_item_description_' . $item[0]->item_id,
											'rows' => '5',
											'class' => 'form-control',
											'placeholder' => 'Edit Item Description. Get as much info as possible...',
											'value' => $item[0]->item_description,
											'id' => 'edit_item_description_' . $item[0]->item_id);
										?>
										<?php echo form_textarea($edit_options) ?>
									</div>
									<div class="p-5">
										<?php  $edit_item_code_options = array(

											'name' => 'edit_item_date_' . $item[0]->item_id,
											"data-date-format" => "yyyy-mm-dd",
											'value' => $item[0]->item_date ? date('Y-m-d', $item[0]->item_date) : "",
											'placeholder' => 'Edit Item Date',
											'class' => 'form-control',
											'id' => 'edit_item_date_' . $item[0]->item_id,

										);
										/*if($row['item_maintenance']=='No'){
											  $edit_item_code_options['disabled']='disable';
										  }*/
										?>
										<?php echo form_input($edit_item_code_options) ?>
									</div>
									<div class="p-5">
										<?php  $edit_item_tracker_name = array(

											'name' => 'edit_item_tracker_name_' . $item[0]->item_id,
											'value' => $item[0]->item_tracker_name,
											'placeholder' => 'Edit Item Tracker Name',
											'class' => 'form-control',
											'id' => 'edit_item_tracker_name_' . $item[0]->item_id,

										);
										echo form_input($edit_item_tracker_name) ?>
									</div>
									<div class="p-5 text-left">
										<label>Schedule Item: </label>
										<?php  $edit_item_schedule = array(
											'name'        => 'edit_item_schedule_' . $item[0]->item_id,
											'value'       => '1',
											'style'       => 'margin-left:10px',
											'checked'     => $item[0]->item_schedule ? TRUE : FALSE,
										);
										echo form_checkbox($edit_item_schedule) ?>
									</div>
									<div class="p-5 text-left">
										<label>Repair Item: </label>
										<?php  $edit_item_repair = array(
											'name'        => 'edit_item_repair_' . $item[0]->item_id,
											'value'       => '1',
											'style'       => 'margin-left:10px',
											'checked'     => $item[0]->item_repair ? TRUE : FALSE,
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
											onclick="update_itemForm('<?php echo $item[0]->item_id; ?>'); return false;">
										Update Item
									</button>

								</div>
							<?php echo form_close() ?>
						</div>
					</div>

			</div>
			<?php if(isset($item[0]->item_tracker_name) && $item[0]->item_tracker_name != '' && $item[0]->item_tracker_name != NULL) : ?>
				<div id="gpsData" class="modal fade" tabindex="-1"
					 role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
					<div class="modal-dialog">
						<div class="modal-content">
							<header class="panel-heading">Kmh
								for&nbsp;<?php echo $group_data->group_name; ?></header>								
							<!-- Client Files Header-->
								<div class="modal-body form-horizontal">
									<div class="form-group">
										<label class="col-sm-3 control-label">Counter Start Date</label>
										<div class="col-sm-9">
											<input type="text" name="gps_date" class="form-control datepicker" value="<?php echo ($item[0]->item_gps_start_date && $item[0]->item_gps_start_date != '0000-00-00') ? $item[0]->item_gps_start_date : date('Y-m-d'); ?>">
										</div>
									</div>
									<div class="line line-dashed line-lg"></div>
									<div class="form-group">
										<label class="col-sm-3 control-label">Counter Value</label>
										<div class="col-sm-9">
											<input type="text" name="gps_counter" class="form-control gps_counter" value="<?php  if($item[0]->item_gps_start_counter && $item[0]->item_gps_start_counter != '') : echo $item[0]->item_gps_start_counter; endif; ?>">
										</div>
									</div>
									
								</div>
								<div class="modal-footer">
									<button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>
									<button class='btn btn-info submitKms' name="submit"
											onclick="update_kms('<?php echo $item[0]->item_id; ?>'); return false;">
										Update Kmhs
									</button>
								</div>
						</div>
					</div>
				</div>
			<?php endif; ?>
		</header>
		<div class="tabbable p-10"> <!-- Only required for left/right tabs -->
			<ul class="nav nav-tabs" data-type="estimates" data-action="stop">
				<li>
					<a href="#tab1" data-toggle="tab">Services</a>
				</li>
				<li>
					<a href="#tab2" data-toggle="tab">Parts</a>
				</li>
				<li>
					<a href="#tab3" data-toggle="tab">Service Settings</a>
				</li>
				<li>
					<!--<a href="#tab4" data-toggle="tab">Old Services</a>-->
					<a href="<?php echo base_url('equipments/item_repairs') . '/' . $item[0]->item_id ;?>" >Repairs</a>
				</li>
				<?php if(isset($item[0]->item_tracker_name) && $item[0]->item_tracker_name != '' && $item[0]->item_tracker_name != NULL) : ?>
					<li>
						<a href="<?php echo base_url('equipments/item_distance') . '/' . $item[0]->item_id ;?>" >Distance Report</a>
					</li>
				<?php endif; ?>
				<?php /*<li>
					<a href="#tab4" data-toggle="tab">Files</a>
				</li>*/ ?>
			</ul>

			<div class="tab-content">
				<div class="tab-pane" id="tab1">
					<?php $this->load->view('equipments/services/item_services_tab.php'); ?>
				</div>
				<div class="tab-pane" id="tab2">
					<?php $this->load->view('equipments/items/equipment_parts_table.php'); ?>
				</div>
				<div class="tab-pane" id="tab3">
					<?php $this->load->view('equipments/services/item_service_settings.php'); ?>
				</div>
				<?php /*<div class="tab-pane" id="tab4">
					<?php $this->load->view('equipments/items/equipment_files.php'); ?>
				</div>*/ ?>
			</div>
		</div>

	</section>

    <input type="hidden" id="php-variable" value="<?php echo getJSDateFormat()?>" />
</section>

<script>
	$(document).ready(function(){		
		$('[name="report_kilometers"]').inputmask('decimal', {removeMaskOnSubmit: true, rightAlign: false});
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
		if (!location.hash)
		{			
			$('.tab-content .tab-pane#tab1').addClass('active');
			$('[data-type="estimates"] [href="#tab1"]').parent().addClass('active');
		}
		$('.gps_date').datepicker({ format: "yyyy-mm-dd" });
		
		$('.nav[data-type="estimates"] > li').click(function(){
			$('div.tab-pane').hide();
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
		$('#partDate').datepicker({ format: "yyyy-mm-dd" });
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
		// $('#addPartRow').append('<td><input type="text" id="partDate" class="input-small form-control" value="' + getDate() + '"></td>');
		$('#addPartRow').append('<td><input type="text" id="partDate" class="input-small form-control" value="' + getDate() + '"></td>');
		$('#addPartRow').append('<td style="text-align:center;"><a href="#" class="savePart">Add</a><br><a href="#" id="cancelAddPart">Cancel</a></td>');
		// $('#partDate').datepicker({ format: "yyyy-mm-dd" });
        $('#partDate').datepicker({format: $('#php-variable').val()});
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
		if(confirm('Are you sure'))
		{
			$.post(baseUrl + 'equipments/ajax_delete_part', {part_id: part_id}, function (resp) {
				if (resp.status == 'ok') {
					$('[data-part_id="' + part_id + '"]').fadeOut(function () {
						$('[data-part_id="' + part_id + '"]').remove();
					});
				}
			}, 'json');
		}
		return false;
	});
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
		var dateFormat = '<?= getDateFormat() ?>';
        let str = dateFormat.replace('Y', yyyy);
        let str2 = str.replace('m', (mmChars[1] ? mm : "0" + mmChars[0]));
        let str3 = str2.replace('d', (ddChars[1] ? dd : "0" + ddChars[0]));
		return str3;
		// return datestring;
	}
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
	function update_kms(id){
		$('#gpsData .submitKms').attr('disabled', 'disabled');
		var kms = $('#gpsData').find('[name="gps_counter"]').val();
		var date = $('#gpsData').find('[name="gps_date"]').val();
		console.log(id, kms, date);
		$.post(baseUrl + 'equipments/ajax_update_gps_data', {id:id, kms:kms, date:date}, function (resp) {
			if (resp.status == 'ok') {
				$('#gpsData .submitKms').removeAttr('disabled');
				$('#gpsData').modal('hide');
			}
		}, 'json');
		return false;
	}
</script>
<?php $this->load->view('includes/footer'); ?>
