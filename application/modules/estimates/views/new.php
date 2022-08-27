<?php $this->load->view('includes/header'); ?>
<script src="<?php echo base_url('/assets/js/html2canvas.js'); ?>"></script>
<script src="<?php echo base_url('/assets/js/jquery-ui.min.js'); ?>"></script>

<!-- Edit Client Details Modal Loader -->
<?php $this->load->view('clients/client_information_update_modal'); ?>
<!-- End of Edit Customer Details Modal-->

<section class="scrollable p-sides-15">
<ul class="breadcrumb no-border no-radius b-b b-light pull-in">
	<li><a href="<?php echo base_url(); ?>"><i class="fa fa-home"></i> Home</a></li>
	<li><a href="<?php echo base_url('leads'); ?>">Leads</a></li>
	<li class="active">New Estimate - <?php echo $lead->lead_id; ?>-E</li>
</ul>

<!-- Edit Client Details Modal Loader -->
<?php $this->load->view('clients/client_information_display'); ?>
<!-- End of Edit Customer Details Modal-->

<!-- Original lead display -->
<section class="col-md-12 panel panel-default p-n">
	<header class="panel-heading">
		Original Lead

		<?php $this->load->view('brands/partials/estimate_brands_dropdown', ['brand_style'=>'', 'class'=>'input-sm select2 p-n']); ?>
		<!--
		<?php if(isset($tree_inventory) && count($tree_inventory)): ?>
		<div class="pull-right m-bottom-0">
            <div class="checkbox m-n">
                <label class="checkbox-custom">
                    <input type="checkbox" name="tree_inventory_pdf" <?php if(isset($estimate_data) && $estimate_data->tree_inventory_pdf): ?>checked="checked"<?php endif;?> >
                    <i class="fa fa-fw fa-square-o"></i>
                    <strong style="color: #4aa700;text-decoration: underline;">Tree Inventory PDF</strong>&nbsp;&nbsp;
                </label>
                <i class="h4 fa fa-file-text-o"></i>
            </div>
        </div>
        <div class="clear"></div>
    	<?php endif; ?>
    	-->
	</header>
	<div class="p-10">
		Date:&nbsp;<?= $lead->lead_date_created; ?>, by&nbsp;<?= $lead->lead_created_by; ?>:<br/>
		<?= nl2br($lead->lead_body); ?>
	</div>
</section>
<!-- /Original lead display -->

<!-- Create estimate -->
<?php echo form_open_multipart('estimates/add_estimate', 'id="addEstimate"'); ?>

<span style="color:red;"><?php echo validation_errors(); ?></span>

<section class="col-md-12 panel panel-default p-n">
	<header class="panel-heading">Estimate Provided By:
		<select class="form-control" style="width:300px;display:inline-block;" name="provided">
				<option value="meeting">Meeting</option>
				<option value="schedule meeting">Schedule Meeting</option>
				<option value="printed">Printed</option>
				<option value="phone">Phone</option>
				<option value="email">Email</option>
		</select>
	</header>
</section>


<section class="col-md-12 panel panel-default p-n">
	<header class="panel-heading">New Estimate for&nbsp;<?php echo $client_data->client_name; ?>
		<select class="serviceSelecter form-control" style="width:300px;display:inline-block;" id="selectService">
			<option value=""><strong>Select Service</strong></option>
			<?php foreach ($services as $service) : ?>
				<option value="<?php echo $service->service_id; ?>"
				        data-description="<?php echo $service->service_description; ?>"
				        data-cost="<?php echo $service->service_cost; ?>"><?php echo $service->service_name;
					echo $service->service_cost ? ' ( ' . money($service->service_cost) . '/ hr.)' : ''; ?></option>
			<?php endforeach; ?>
		</select>
	</header>
</section>

<!-- Estimate Body -->
<!-- Hidden values required for lead status change -->
<?php  $hidden = array(
	'client_id' => $lead->client_id,
	'lead_id' => $lead->lead_id);
echo form_hidden($hidden); ?>
<div id="estimateServices"></div>


<!--Notes on the estimate -->
<section class="col-md-12 panel panel-default p-n">
	<header class="panel-heading">Notes on the estimate</header>
	<div class="m-top-10 m-b">
		<div class="p-sides-15">
			<?php $options = array(
				'name' => 'estimate_item_note_estimate',
				'id' => 'estimate_item_note_estimate',
				'rows' => '4',
				'class' => 'form-control',
				//'required'  	  =>   'required',
				//'pattern'  	  =>   '[0-9]',
				'Placeholder' => 'Write some notes on the estimate. It will be printed for the client.',
				'value' => set_value('estimate_item_note_estimate'));

			echo form_textarea($options); ?>
		</div>
	</div>
	<!-- /Estmate items -->
</section>
<!-- /Notes on the estimate -->

<!--Crews-->
<?php $this->load->view('estimates/estimate_crews'); ?>
<!--END Crews-->

<!-- Work-order Details -->
<section class="col-md-12 panel panel-default p-n">
	<header class="panel-heading">Project Details</header>
	<div class="p-top-10">
		<div class="p-sides-15">
			<div class="control-group">
				<label class="checkbox"><input type="checkbox" name="new_add" id="new_add" value="1"
				                               onchange='showOrHide("new_add", "new_add_tab");'>Another Address</label>
			</div>
			<table id="new_add_tab" style="display:none;">
				<tr>
					<td class="w-200">
						<label class="control-label">Address:</label>
						<input type="text" class="form-control" id="route_1" name="new_address"
						       value="<?php if (isset($client_data->client_address)) : echo $client_data->client_address; endif; ?>"
						       autocomplete="off">
					</td>
				</tr>
				<tr>
					<td class="w-200">
						<label class="control-label">City:</label>
						<input type="text" class="form-control" id="locality_1" name="new_city"
						       value="<?php if (isset($client_data->client_city)) : echo $client_data->client_city; endif; ?>"
						       autocomplete="off">
					</td>
				</tr>
				<tr>
					<td class="w-200">
						<label class="control-label">State/Province:</label>
						<input type="text" class="form-control" name="new_state" autocomplete="off"
						       value="<?php if (isset($client_data->client_state)) : echo $client_data->client_state; endif; ?>"
						       id="administrative_area_level_1_1">
					</td>
				</tr>
				<tr>
					<td class="w-200">
						<label class="control-label">Zip/Postal:</label>
						<input type="text" class="form-control" name="new_zip" autocomplete="off"
						       value="<?php if (isset($client_data->client_zip)) : echo $client_data->client_zip; endif; ?>"
						       id="postal_code_1">
					</td>
				</tr>
			</table>
		</div>
	</div>

	<!-- Estimated time required for completion -->
	<div class="p-top-10">
		<div class="p-sides-15">
			<label>Estimated time required for completion:</label>
			<?php $options = array(
				'name' => 'estimate_item_estimated_time',
				'id' => 'estimate_item_estimated_time',
				'rows' => '4',
				'class' => 'form-control',
				//'required'  	  =>   'required',
				//'pattern'  	  =>   '[0-9]',
				'Placeholder' => 'Estimated time required for completion, or other description',
				'value' => set_value('estimate_item_estimated_time'));

			echo form_textarea($options); ?>
		</div>
		<!-- /Estmate items -->
	</div>
	<!-- /Estimated time required for completion -->

	<!--Equipment setup -->
	<div class="p-top-10">
		<div class="p-sides-15">
			<label>Equipment setup:</label>
			<?php $options = array(
				'name' => 'estimate_item_equipment_setup',
				'id' => 'estimate_item_equipment_setup',
				'rows' => '4',
				'class' => 'form-control',
				//'required'  	  =>   'required',
				//'pattern'  	  =>   '[0-9]',
				'Placeholder' => 'Equipment setup, special tools needed.',
				'value' => set_value('estimate_item_equipment_setup'));

			echo form_textarea($options); ?>
		</div>
		<!-- /Estmate items -->
	</div>
	<!-- /Equipment setup -->

	<!--Notes to the crew -->
	<div class="p-top-10">
		<div class="p-sides-15">
			<label>Notes to the crew:</label>
			<?php $options = array(
				'name' => 'estimate_item_note_crew',
				'id' => 'estimate_item_note_crew',
				'rows' => '4',
				'class' => 'form-control',
				'Placeholder' => 'Write some notes to the crew',
				'value' => set_value('estimate_item_note_crew'));

			echo form_textarea($options); ?>
		</div>
		<!-- /Estmate items -->
	</div>
	<!-- /Notes to the crew -->

	<!--Notes of payments -->
	<div class="p-top-10">
		<div class="p-sides-15">
			<label>Notes of payments:</label>
			<?php $options = array(
				'name' => 'estimate_item_note_payment',
				'id' => 'estimate_item_note_payment',
				'rows' => '4',
				'class' => 'form-control',
				//'required'  	  =>   'required',
				//'pattern'  	  =>   '[0-9]',
				'Placeholder' => 'Write some notes of payments',
				'value' => set_value('estimate_item_note_payment'));

			echo form_textarea($options); ?>
		</div>
		<!-- /Estmate items -->
	</div>
	<!-- /Notes of payments -->
	<div class="p-top-10">
		<?php $discount = isset($discount_data['discount_amount']) && $discount_data['discount_amount'] ? $discount_data['discount_amount'] : NULL; ?>
		<div class="p-sides-15 m-b-md col-sm-3">
			<label>Discount:</label>
			<?php $options = array('name' => 'discount', 'class' => 'form-control input-mini', 'value' => $discount); ?>
			<?php echo form_input($options); ?>
		</div>

	</div>
</section>
<!-- /row -->
<!--Project Scheme-->
<section class="col-md-12 panel panel-default p-n" id="scheme">
	<header class="panel-heading">Project Scheme
	<span class="btn btn-info btn-xs pull-right" id="showHideScheme" onclick="showHideScheme()"><i class="fa fa-plus"></i></span>
	</header>
	<div class="m-top-10 m-b scheme-block" style="display:none">
		 <?php $this->load->view('estimates/scheme'); ?>
	</div>
	
</section>
<!--Project Scheme End-->
<section class="row">
<!-- Other Project Requrements -->
<section class="col-md-4">
	<section class="panel panel-default p-n">
		<header class="panel-heading">Team Requirements:</header>
		<!-- Team Requirements header -->

		<table>
			<!-- Arborist required Yes/NO -->
			<tr>
				<td>
					<?php $data = array(
						'name' => 'check_arborist',
						'id' => 'check_arborist',
						'value' => 'yes',
						'checked' => FALSE,
						'style' => 'margin:10px');

					echo form_checkbox($data); ?>
				</td>
				<td>
					ISA Certified Arborist(s)
				</td>
			</tr>
			<!-- Bucket truck required YES/NO -->
			<tr>
				<td>
					<?php $data = array(
						'name' => 'check_bucket_truck_operator',
						'id' => 'check_bucket_truck_operator',
						'value' => 'yes',
						'checked' => FALSE,
						'style' => 'margin:10px');

					echo form_checkbox($data); ?>
				</td>
				<td>
					Bucket Truck Operator(s)
				</td>
			</tr>
			<!--Climber required YES/NO -->
			<tr>
				<td>
					<?php $data = array(
						'name' => 'check_climber',
						'id' => 'check_climber',
						'value' => 'yes',
						'checked' => FALSE,
						'style' => 'margin:10px');

					echo form_checkbox($data); ?>
				</td>
				<td>
					Climber(s)
				</td>
			</tr>
			<!--Chainsaw or Chipper Operator(s) required YES/NO -->
			<tr>
				<td>
					<?php $data = array(
						'name' => 'check_chipper_operator',
						'id' => 'check_chipper_operator',
						'value' => 'yes',
						'checked' => FALSE,
						'style' => 'margin:10px');

					echo form_checkbox($data); ?>
				</td>
				<td>
					Chainsaw/Chipper Operator(s)
				</td>
			</tr>
			<!--Groundsperson(s) required YES/No -->
			<tr>
				<td>
					<?php $data = array(
						'name' => 'check_groundsmen',
						'id' => 'check_groundsmen',
						'value' => 'yes',
						'checked' => FALSE,
						'style' => 'margin:10px');

					echo form_checkbox($data); ?>
				</td>
				<td>
					Groundsperson(s)
				</td>
			</tr>
		</table>
	</section>
</section>
<!-- /Team Requirements -->

<section class="col-md-4">
	<section class="panel panel-default p-n">
		<header class="panel-heading">Equipment Requirements:</header>
		<!-- Equipment Requirements header -->

		<table>
			<!-- Bucket Truck required Yes/NO -->
			<tr>
				<td>
					<?php $data = array(
						'name' => 'check_bucket_truck',
						'id' => 'check_bucket_truck',
						'value' => 'yes',
						'checked' => FALSE,
						'style' => 'margin:10px');

					echo form_checkbox($data); ?>
				</td>
				<td>
					Bucket Truck
				</td>
			</tr>
			<!-- Wood Chipper YES/NO -->
			<tr>
				<td>
					<?php $data = array(
						'name' => 'check_wood_chipper',
						'id' => 'check_wood_chipper',
						'value' => 'yes',
						'checked' => FALSE,
						'style' => 'margin:10px');

					echo form_checkbox($data); ?>
				</td>
				<td>
					Wood Chipper
				</td>
			</tr>
			<!-- Dump Truck required YES/NO -->
			<tr>
				<td>
					<?php $data = array(
						'name' => 'check_dump_truck',
						'id' => 'check_dump_truck',
						'value' => 'yes',
						'checked' => FALSE,
						'style' => 'margin:10px');

					echo form_checkbox($data); ?>
				</td>
				<td>
					Dump Truck
				</td>
			</tr>
			<!-- Crane required YES/NO -->
			<tr>
				<td>
					<?php $data = array(
						'name' => 'check_crane',
						'id' => 'check_crane',
						'value' => 'yes',
						'checked' => FALSE,
						'style' => 'margin:10px');

					echo form_checkbox($data); ?>
				</td>
				<td>
					Crane
				</td>
			</tr>
			<!-- Stump Grinder required YES/No -->
			<tr>
				<td>
					<?php $data = array(
						'name' => 'check_stump_grinder',
						'id' => 'check_stump_grinder',
						'value' => 'yes',
						'checked' => FALSE,
						'style' => 'margin:10px');

					echo form_checkbox($data); ?>
				</td>
				<td>
					Stump Grinder
				</td>
			</tr>
		</table>
	</section>
</section>
<!-- /Team Requirements -->

<!-- Project Header -->
<section class="col-md-4">
	<section class="panel panel-default p-n">
		<header class="panel-heading">Project Requirements:</header>
		<table>
			<!-- Brush Removal Yes/NO -->
			<tr>
				<td>
					<input type="hidden" name="check_brush_disposal" value="no"/>
					<?php
					$data = array(
						'name' => 'check_brush_disposal',
						'id' => 'check_brush_disposal',
						'value' => 'yes',
						'checked' => FALSE,
						'style' => 'margin:10px');

					echo form_checkbox($data); ?>
				</td>
				<td>
					Brush Removal And Disposal
				</td>
				<!-- Leave wood for customer use Yes/NO -->
			<tr>
				<td>
					<input type="hidden" name="check_leave_wood" value="no"/>
					<?php
					$data = array(
						'name' => 'check_leave_wood',
						'id' => 'check_leave_wood',
						'value' => 'yes',
						'checked' => FALSE,
						'style' => 'margin:10px');

					echo form_checkbox($data); ?>
				</td>
				<td>
					Leave Wood For Customer Use
				</td>
				<!-- Full clean-up included Yes/NO -->
			<tr>
				<td>
					<input type="hidden" name="check_full_cleanup" value="no"/>
					<?php

					$data = array(
						'name' => 'check_full_cleanup',
						'id' => 'check_full_cleanup',
						'value' => 'yes',
						'checked' => FALSE,
						'style' => 'margin:10px');

					echo form_checkbox($data); ?>
				</td>
				<td>
					Full Clean-up Included
				</td>
				<!-- Stump Chips Hauled Yes/NO -->
			<tr>
				<td>
					<input type="hidden" name="check_stump_chips" value="no"/>
					<?php

					$data = array(
						'name' => 'check_stump_chips',
						'id' => 'check_stump_chips',
						'value' => 'yes',
						'checked' => FALSE,
						'style' => 'margin:10px');

					echo form_checkbox($data); ?>
				</td>
				<td>
					Stump Chips Hauled.
				</td>
				<!-- Arborist Report Yes/NO -->
			<tr>
				<td>
					<input type="hidden" name="estimate_scheme" value="">
					<input type="hidden" name="estimate_pict" value="">
					<input type="hidden" name="check_permit_required" value="no"/>
					<?php

					$data = array(
						'name' => 'check_permit_required',
						'id' => 'check_permit_required',
						'value' => 'yes',
						'checked' => FALSE,
						'style' => 'margin:10px');

					echo form_checkbox($data); ?>
				</td>
				<td>
					Permit Required.
				</td>

		</table>
	</section>
</section>
<!-- /Project Requirements -->
</section>

<!-- Preview and Create -->
<section class="col-md-12 panel panel-default p-n m-bottom-20">
	<div class="p-sides-15">
		<?php echo anchor('leads/', 'Cancel', 'class="btn btn-info pull-right m-top-10"');

		/*$data = array(
			'name' => 'submit',
			'id' => 'createSubmit',
			'value' => 'Create',
			'class' => 'btn btn-success pull-right',
			'style' => 'margin:10px');
		echo form_submit($data);*/

		?>
		<button id="addEstimateButton" class="btn btn-success pull-right" style="margin:10px">Create</button>
	</div>
</section>
</section><!-- /Preview and Create-->
<?php echo form_close(); ?>
<?php $this->load->view('includes/footer'); ?>

<script type="text/javascript">
	var placeSearch, autocomplete;
	var componentForm = {
		street_number: 'short_name',
		route: 'long_name',
		locality: 'long_name',
		administrative_area_level_1: 'long_name',
		postal_code: 'short_name'
	};

	function initialize1() {
		autocomplete = new google.maps.places.Autocomplete(
			/** @type {HTMLInputElement} */(document.getElementById('route_1')),
			{ types: ['geocode'] }, componentRestrictions: {country: AUTOCOMPLETE_RESTRICTION});
		google.maps.event.addListener(autocomplete, 'place_changed', function () {
			fillInAddress1(autocomplete, '_1');
		});
	}
	function fillInAddress1(auto, suffix) {
		var place = auto.getPlace();

		for (var component in componentForm) {
			if ($('#' + component + suffix).length) {
				if (component != 'street_number') {
					component += suffix;
				}
			}
		}
		var num = '';
		for (var i = 0; i < place.address_components.length; i++) {
			var addressType = place.address_components[i].types[0];
			if (componentForm[addressType]) {
				if (addressType == 'street_number') {
					var num = place.address_components[i][componentForm[addressType]] + ' ';
					continue;
				}
				if (addressType == 'route')
					place.address_components[i][componentForm[addressType]] = num + place.address_components[i][componentForm[addressType]];
				var val = place.address_components[i][componentForm[addressType]];
				document.getElementById(addressType + '_1').value = val;
			}
		}
	}
	$(document).ready(function () {
		if ($('#check_climber').is(':checked')) {
			$('#crew_size').show();
		} else {
			$('#estimate_item_crewsize').val('');
			$('#crew_size').hide();
		}
		$('#check_climber').click(function () {
			if ($(this).is(':checked')) {
				$('#crew_size').show();
			} else {
				$('#estimate_item_crewsize').val('');
				$('#crew_size').hide();
			}
		});
		$('.btn-upload').change(function () {
			var obj = $(this).parent().next();
			$(obj).html('');
			$.each($(this)[0].files, function (key, val) {
				if (val.name)
					$(obj).html($(obj).html() + "<div style='padding: 2px;font-size:13px;'>" + val.name + "</div>");
			});
		});
		/*$('#createSubmit').click(function(){
			
		});*/
		$(document).on('change', '#selectService', function () {
			var obj = $('#selectService [value="' + $('#selectService').val() + '"]');
			var rand = Math.floor(Math.random() * (9999 - 1000 + 1)) + 1000;
			if ($('#selectService').val()) {
				var html = '<section class="col-md-12 panel panel-default p-n"><header class="panel-heading">' + $(obj).text();
				html += '<a class="btn btn-danger btn-xs pull-right deleteService" data-service_id="' + $('#selectService').val() + '"><i class="fa fa-trash-o"></i></a></header>';
				html += '<div style="position:relative"><textarea cols="20" class="form-control serviceDescription" name="service[description][' + $('#selectService').val() + '][' + rand + ']">' + $(obj).data('description') + '</textarea>';
				html += '<div class="col-sm-5 m-left-0 pull-right m-b-sm"><div class="pull-left"><strong>Estimated time:</strong>';
				html += '<input placeholder="Estimated Time" class="form-control input-mini time" type="text" data-cost="' + $(obj).data('cost') + '" name="service[time][' + $('#selectService').val() + '][' + rand + ']">';
				html += '<strong>Travel time:</strong><input placeholder="Travel Time" class="form-control input-mini traveltime" type="text" name="service[traveltime][' + $('#selectService').val() + '][' + rand + ']">';
				html += '<strong>Price:</strong><input placeholder="Price" class="form-control input-mini price" type="text" name="service[price][' + $('#selectService').val() + '][' + rand + ']"></div>';
				html += '<div class="pull-right m-t" style="width:220px;">';
				html += '<span class="btn btn-primary btn-file" style="margin-top:3px;display: block;">Choose Files...<input type="file" name="files[' + $('#selectService').val() + '][' + rand + '][]" multiple="multiple" class="btn-upload"></span>';
				html += '<span class="label label-info m-top-10 pull-left m-bottom-10" id="upload-file-info" style="width: 220px;overflow: hidden;"></span>';
				html += '</div></div><div style="clear:both;"></div></div></section>';
				$('#estimateServices').prepend(html);
				$('.btn-upload').change(function () {
					var obj = $(this).parent().next();
					$(obj).html('');
					$.each($(this)[0].files, function (key, val) {
						if (val.name)
							$(obj).html($(obj).html() + "<span class='label label-info' id='upload-file-info' style='display:block;'><div style='padding: 2px;font-size:13px;'>" + val.name + "</div></span>");
					});
				});
				$('#selectService').val('');
				$('#selectService').change();
			}
		});
		$(document).on('click', '.deleteService', function () {
			$(this).parent().parent().remove();
			return false;
		});
		$('#selectCrew').change(function () {
			var obj = $('#selectCrew [value="' + $('#selectCrew').val() + '"]');
			if ($('#selectCrew').val()) {
				var html = '<section class="col-md-4" id="crew_' + $('#selectCrew').val() + '"><section class="panel panel-default p-n"><header class="panel-heading">';
				html += '<div class="col-md-3" style="padding-top: 7px;">' + $(obj).text() + '</div>';
				html += '<div class="col-md-8"><input type="text" name="crew_team[' + $('#selectCrew').val() + '][estimate_crew_team]" placeholder="Team" class="form-control team_count"></div>';
				html += '<a class="btn btn-danger btn-xs pull-right deleteCrew m-t-xs" data-crew_id="' + $('#selectCrew').val() + '"><i class="fa fa-trash-o"></i></a>';
				html += '<div class="clear"></div></header>';
				html += '</section></section>';
				$('#estimateCrews').prepend(html);
				$('#selectCrew').val('');
				$('#selectCrew').change();
				$(obj).attr('disabled', 'disabled');
			}
			return false;
		});
		$(document).on('click', '.deleteCrew', function () {
			var crew_id = $(this).data('crew_id');
			if ($('[name="crew_team[' + crew_id + '][estimate_crew_id]"]'))
				$('#crew_' + crew_id).replaceWith('<input name="delete_crews[]" type="hidden" value="' + $('[name="crew_team[' + crew_id + '][estimate_crew_id]"]').val() + '">');
			else
				$('#crew_' + crew_id).remove();
			$('#selectCrew [value="' + crew_id + '"]').removeAttr('disabled');
			return false;
		});
		$(document).on('keyup', '.time', function () {
			/*var sum = parseInt($(this).data('cost') * $(this).val());
			 sum = (sum && sum != NaN) ? sum : 0;
			 if(sum || $(this).parent().find('.price').val())
			 $(this).parent().find('.price').val(sum);*/
			return false;
		});
		$(document).on("keypress keyup blur", '.time', function (event) {
			if ((event.which < 48 || event.which > 57) && (event.which != 46)) {
				event.preventDefault();
			}
		});
		$(document).on("keypress keyup blur", '.price', function (event) {
			if ((event.which < 48 || event.which > 57) && (event.which != 46)) {
				event.preventDefault();
			}
		});
		
		$('#addEstimateButton').click(function(){
			$('#processing-modal').modal();
			$('#createSubmit').attr('disabled', 'disabled');
			$('.incorrect').removeClass('incorrect');
			var img = $('#container img');
			var imagesStr = '';
			if($(img).length)
			{
				$('html').removeClass('app');
				html2canvas($("#container"), {
					onrendered: function(canvas) {
						var myImage = canvas.toDataURL("image/png");
						
						$.each(img, function(key, val){
							imagesStr += val.outerHTML;
						});
						$('#addEstimate [name="estimate_pict"]').val(imagesStr);
						$('#addEstimate [name="estimate_scheme"]').val(myImage);
						$('html').addClass('app');
						setTimeout(function(){
							$('#addEstimate').submit();
						}, 100);
					}
				});
			}
			else
			{
				$('#addEstimate [name="estimate_pict"]').val('');
				$('#addEstimate [name="estimate_scheme"]').val('');
				$('#addEstimate').submit();
			}
			return false;
		});
		
		$('#addEstimate').submit(function () {
			var success = true;
			$('#createSubmit').attr('disabled', 'disabled');
			$('.incorrect').removeClass('incorrect');
			if (!$('.time').length) {
				$('#selectService').addClass('incorrect');
				success = false;
			}
			if (!$('#estimateCrews').children('section').length) {
				$('#selectCrew').addClass('incorrect');
				success = false;
			}
			$.each($('.time'), function (key, val) {
				if ($(val).val() === '' || $(val).val() === '0') {
					if ($(val).parent().children('.price').val() === '0') {
						$(val).val('0');
						$(val).parent().children('.price').val('0');
					}
					else {
						$(val).addClass('incorrect');
						success = false;
					}
				}
			});
			if (!success) {
				$('#createSubmit').removeAttr('disabled');
				$('.scrollable.p-sides-15:first').animate({
						scrollTop: $(".incorrect:first").offset().top - $('.breadcrumb.no-border.no-radius.b-b.b-light.pull-in').offset().top - $('.breadcrumb.no-border.no-radius.b-b.b-light.pull-in').outerHeight()},
					'slow');
				$('#processing-modal').modal('hide');
				return false;
			}
		});
		setTimeout(function () {
			initialize1();
		}, 500);
	});

	function showHideScheme()
	{
		$('.scheme-block').toggle('slow');
		if($('#showHideScheme i').hasClass('fa-plus'))
		{
			$('#showHideScheme i').removeClass('fa-plus');
			$('#showHideScheme i').addClass('fa-minus');
		}
		else
		{
			$('#showHideScheme i').removeClass('fa-minus');
			$('#showHideScheme i').addClass('fa-plus')
		}
		return false;
	}
</script>
