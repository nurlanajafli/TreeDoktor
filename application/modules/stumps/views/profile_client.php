<?php $this->load->view('includes/header'); ?>
<?php
$users[] = ['value' => 0, 'text' => 'N/A'];
foreach ($active_users as $active_user)
	$users[] = ['value' => $active_user->id, 'text' => $active_user->firstname . " " . $active_user->lastname];
?>
<link href="<?php echo base_url('assets/vendors/notebook/js/bootstrap-editable/css/bootstrap-editable.css'); ?>" rel="stylesheet">

<link href="<?php echo base_url('assets/vendors/notebook/js/datetimepicker/datetimepicker.css'); ?>" rel="stylesheet">
<style type="text/css">
	.popover {
		max-width: 300px!important;
	}
</style>
<section class="scrollable p-sides-15 stumps">
	<ul class="breadcrumb no-border no-radius b-b b-light pull-in">
		<li><a href="<?php echo base_url(); ?>"><i class="fa fa-home"></i> Home</a></li>
		<li class="active">Stumps</li>
	</ul>
	<section class="panel panel-default">
		<header class="panel-heading"><?php echo isset($client[0]['cl_name']) ? $client[0]['cl_name'] : ''; ?> 
			<?php echo isset($client[0]['cl_lastname']) ? $client[0]['cl_lastname'] : ''; ?>&nbsp;
		 Stumps
			<div class="row">
				<div class="col-md-9" style="padding-top: 4px;">
					<?php if($this->session->userdata('STP') != 3) : ?>
						<a title="Stump" href="#newStump" role="button" class="btn btn-xs btn-success btn-mini pull-right m-l-sm" data-toggle="modal">
							<i class="fa fa-plus"></i><i class="fa fa-user"></i>
						</a>
					<?php endif; ?>
					<?php if(isset($stumps) && !empty($stumps)) : ?>
						<a href="<?php echo base_url('stumps/xlsx'); echo isset($client_id) ? '/' . $client_id : ''; ?>" class="btn btn-info btn-xs pull-right">XLSX Report</a>
					<?php endif; ?>
					<a href="<?php echo base_url('stumps/stumps_list/' . $status . '/all'); ?>" class="btn btn-warning btn-xs pull-right m-r-sm">Show All</a>
				</div>
				<div class="col-sm-3 pull-right" style="margin-top: 2px;">
					<!-- Search Estimates -->
					<form name="search" id="search" method="get" action="">
						<div class="input-group">
							<input name="q" id="search_tags" type="text" class="input-sm form-control"
								   placeholder="<?php if (!empty($placeholder)) : echo $placeholder;
								   else : ?>Name, Phone number, address...<?php endif; ?>"
								   value="<?php if (isset($search_keyword)) echo $search_keyword; ?>">
							<span class="input-group-btn">
								<a class="btn btn-sm btn-default" id="reset" onclick="location.href = location.protocol + '//' + location.host + location.pathname;"<?php if (!isset($search_keyword) || !$search_keyword) : ?> disabled="disabled"<?php endif; ?>>
									Reset!
								</a>
								<button class="btn btn-sm btn-default" type="submit" id="search">Go!</button>
							</span>
						</div>
					</form>	

				</div>
			</div>
			

		</header>
		
			<ul class="nav nav-tabs" data-type="invoices">
				<?php $href = base_url() . $this->uri->segment(1)  . '/' ;?>
				<?php
				if($this->uri->segment(2))
					$href .= $this->uri->segment(2) . '/';
				else
					$href .= 'stumps_list/';
				?>
				<?php if(isset($client[0]['cl_id']) && $client[0]['cl_id'])
						$href .= $client[0]['cl_id'] . '/';
				?>
				<?php foreach($statusesSelect as $key=>$val) : ?>
				<li <?php if (isset($status) && $val['value'] == $status): ?>class="active"<?php endif; ?>>
					<a href="<?php echo $href . $val['value']; ?><?php echo $this->input->get('q') ? '?q=' . $this->input->get('q') : ''; ?>"><?php echo ucfirst($val['text']); ?>
						<span class="badge<?php if (isset($status) && $val['value'] == $status): ?> bg-info<?php endif; ?>">
							<?php echo ($counter[$val['value'] . '_count']) ? $counter[$val['value'] . '_count'] : 0; ?>
						</span>
					</a>
				</li>
				<?php endforeach; ?>
			</ul>
					
			<div class="tabbable">
				<div class="table-responsive" id="tbl_Estimated">
					<table class="table b-t b-light" id="tbl_search_result">
						<thead>
						<tr>
							<th class="text-center b-l b-r pos-rlt" width="10px">
								<a href="<?php echo $href . $status; ?>/<?php echo $page == 'all' ? 'all' : '1'; ?>/id/<?php echo isset($field) && $field == 'id' && $type == 'asc' ? 'desc' : 'asc' ?><?php echo $this->input->get('q') ? '?q=' . $this->input->get('q') : ''; ?>">
									#
									<?php //echo isset($field) && $field == 'id' ? '<i class="fa fa-sort-' . $type . '" style="position: absolute;top: 13px;right: 5px;"></i>' : '' ?>
								</a>
							</th>
							<th class="text-center b-r" width="86px">
								<a href="<?php echo $href . $status; ?>/<?php echo $page == 'all' ? 'all' : '1'; ?>/grid/<?php echo isset($field) && $field == 'grid' && $type == 'asc' ? 'desc' : 'asc' ?><?php echo $this->input->get('q') ? '?q=' . $this->input->get('q') : ''; ?>">
									Block
								</a>
							</th>
							<th class="text-center b-r" width="220px">
								<a href="<?php echo $href . $status; ?>/<?php echo $page == 'all' ? 'all' : '1'; ?>/address/<?php echo isset($field) && $field == 'address' && $type == 'asc' ? 'desc' : 'asc' ?><?php echo $this->input->get('q') ? '?q=' . $this->input->get('q') : ''; ?>">
									Address
								</a>
							</th>
							<th class="text-center b-r" width="86px">Loc. Info</th>
							<th class="text-center b-r" width="86px">Dist.</th>
							<?php /*<th class="text-center b-r" width="400px">
								<a href="<?php echo $href . $status; ?>/1/status/<?php echo isset($field) && $field == 'status' && $type == 'asc' ? 'desc' : 'asc' ?><?php echo $this->input->get('q') ? '?q=' . $this->input->get('q') : ''; ?>">
									Info
								</a>
							</th>*/ ?>
							<th class="text-center b-r" width="75px">
								<a href="<?php echo $href . $status; ?>/<?php echo $page == 'all' ? 'all' : '1'; ?>/range/<?php echo isset($field) && $field == 'range' && $type == 'asc' ? 'desc' : 'asc' ?><?php echo $this->input->get('q') ? '?q=' . $this->input->get('q') : ''; ?>">
									Size
								</a>
							</th>
							<th class="text-center b-r" width="120px">
								<a href="<?php echo $href . $status; ?>/<?php echo $page == 'all' ? 'all' : '1'; ?>/locates/<?php echo isset($field) && $field == 'locates' && $type == 'asc' ? 'desc' : 'asc' ?><?php echo $this->input->get('q') ? '?q=' . $this->input->get('q') : ''; ?>">
									Locates
								</a>
							</th>
							<th class="text-center b-r" width="400px">Notes</th>
							<th class="text-center b-r">Status</th>
							<th class="text-center b-r">
								<a href="<?php echo $href . $status; ?>/<?php echo $page == 'all' ? 'all' : '1'; ?>/grind/<?php echo isset($field) && $field == 'grind' && $type == 'asc' ? 'desc' : 'asc' ?><?php echo $this->input->get('q') ? '?q=' . $this->input->get('q') : ''; ?>">
									Grinded/Injected
								</a>
							</th>
							<th class="text-center b-r">
								<a href="<?php echo $href . $status; ?>/<?php echo $page == 'all' ? 'all' : '1'; ?>/clean/<?php echo isset($field) && $field == 'clean' && $type == 'asc' ? 'desc' : 'asc' ?><?php echo $this->input->get('q') ? '?q=' . $this->input->get('q') : ''; ?>">
									Cleaned
								</a>
							</th>
						</tr>
						</thead>
						<tbody id="selectable">
							<?php if(isset($stumps) && !empty($stumps)) : ?>
								<?php $this->load->view('stump_row'); ?>
							<?php else : ?>
								<tr><td colspan="5">No record's found<td></tr>
							<?php endif; ?>
						</tbody>
					</table>
				</div>
			</div>
		<footer class="panel-footer">
			<div class="row">
				<div class="col-sm-5 text-right text-center-xs pull-right">
					<?php echo isset($links) ? $links : ''; ?>
				</div>
			</div>
		</footer>
	</section>
<!--    <input type="hidden" value="--><?//= getJSDateFormat() . ' ' . getTimeFormat(true) ?><!--" class="date-format" />-->
    <input type="hidden" value="<?= getJSDateFormat() . ' ' . (getIntTimeFormat() == 24 ? 'hh:ii' : 'HH:ii P')   ?>" class="date-format" />
    <input type="hidden" value="<?= getIntTimeFormat() == 12 ? true : false ?>" class="time-format" />
</section>
<ul class="custom-menu">
	<?php $statusesMenu = $statusesSelect; ?>
	
	<?php if($status == 'new' || $status == 'canceled' || $status == 'skipped') $statusesMenu[2]['disabled'] = true; ?>

	<?php if($this->session->userdata('STA') == 1 || $this->session->userdata('user_type') == "admin") : ?>
		<li>
			<a href="#" data-pk="" data-name="stump_assigned" data-value="" data-placement="left" data-type="select" data-source='<?php echo json_encode($users); ?>' class="assigned_menu" title="Grind Crew" data-url="<?php echo base_url('stumps/ajax_update_stumps_array'); ?>"></a>
		</li>
		<li>
			<a href="#" data-pk="" data-name="stump_clean_id" data-value="" data-placement="left" data-type="select" data-source='<?php echo json_encode($users); ?>' class="clean_assigned_menu" title="Clean Crew" data-url="<?php echo base_url('stumps/ajax_update_stumps_array'); ?>"></a>
		</li>
	<?php endif; ?>
	<li>
		<a href="#" data-pk="" data-name="stump_status" data-value="{}" data-placement="left" data-type="<?php /*select*/ ?>stump_status" data-source='<?php echo json_encode(['statuses'=>$statusesMenu, 'users'=>$users]); ?>' class="status_menu" title="Status" data-url="<?php echo base_url('stumps/ajax_update_stumps_array'); ?>"></a>
	</li>
	<li>
		<a href="#" data-name="stump_status_work" data-value="" data-placement="right" data-type="select" data-source="[{value:'0',text:'No Locates Yet'},{value:'1',text:'Locates Below'},{value:'2',text:'Clean To Dig'}]" data-pk="" class="status_work_menu" title="Status Work" data-url="<?php echo base_url('stumps/ajax_update_stumps_array'); ?>">Status Work</a>
	</li>
	<li>
		<a href="#" data-pk="" data-name="stump_locates" data-value="" data-placement="left" data-type="text" class="locates_menu" title="Locates" data-url="<?php echo base_url('stumps/ajax_update_stumps_array'); ?>"></a>
	</li>
	<?php if($status == 'grinded' || $status == 'cleaned_up') : ?>
		<li>
			<a href="#" data-pk="" data-name="stump_removal" data-value="" data-placement="left" data-type="date" class="stump_removal_menu" title="Grind Date" data-url="<?php echo base_url('stumps/ajax_update_stumps_array'); ?>"></a>
		</li>
	<?php endif; ?>
	<?php if($status == 'cleaned_up') : ?>
		<li>
			<a href="#" data-pk="" data-name="stump_clean" data-value="" data-placement="left" data-type="date" class="stump_clean_menu" title="Clean Date" data-url="<?php echo base_url('stumps/ajax_update_stumps_array'); ?>"></a>
		</li>
	<?php endif; ?>
</ul>
<?php //if(isset($client[0]['cl_id'])) : ?>
<div id="newStump" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
     aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content panel panel-default p-n">
			<header class="panel-heading">Add New Stump</header>
			<form data-type="ajax" data-url="<?php echo base_url('stumps/save_stump'); ?>" data-location="<?php echo current_url(); ?>" method="POST" class="p-10" action="">
				<div class="control-group m-b-xs">
					<label class="control-label">Address</label>
					<div class="controls">
						<input class="form-control" type="text" data-autocompleate="true" data-part-address="address" name="stump_address" data-toggle="tooltip" data-placement="top" title="" data-original-title="">
					</div>
				</div>
				<div class="control-group m-b-xs">
					<label class="control-label">City</label>
					<div class="controls">
						<input class="form-control" readonly type="text" data-part-address="locality" name="stump_city" data-toggle="tooltip" data-placement="top" title="" data-original-title="">
					</div>
				</div>
				<div class="control-group m-b-xs">
					<label class="control-label">State</label>
					<div class="controls">
						<input class="form-control" readonly data-part-address="administrative_area_level_1" type="text" name="stump_state" data-toggle="tooltip" data-placement="top" title="" data-original-title="">
					</div>
					<input type="hidden" name="stump_lat" data-part-address="lat">
					<input type="hidden" name="stump_lon" data-part-address="lon">
				</div>
				<div class="row m-b-xs">
					<div class="control-group col-md-4">
						<label class="control-label">Side</label>
						<div class="controls">
							<select name="stump_side" class="form-control" data-toggle="tooltip" data-placement="top" title="" data-original-title="">
								<option value="Front">Front</option>
								<option value="Side">Side</option>
								<option value="Opposite">Opposite</option>
								<option value="Rear">Rear</option>
								<option value="Adjacent">Adjacent</option>
							</select>
						</div>
					</div>
					<div class="control-group col-md-4">
						<label class="control-label">Range</label>
						<div class="controls">
							<input class="form-control" type="text" name="stump_range" data-toggle="tooltip" data-placement="top" title="" data-original-title="">
						</div>
					</div>
					<div class="control-group col-md-4">
						<label class="control-label">Block</label>
						<div class="controls">
							<input class="form-control" type="text" name="stump_map_grid" data-toggle="tooltip" data-placement="top" title="" data-original-title="">
						</div>
					</div>
				</div>
				<div class="row m-b-xs">
					<div class="control-group col-md-6">
						<label class="control-label">Stump Client</label>
						<div class="controls">
							<select name="stump_client_id" class="form-control" data-toggle="tooltip" data-placement="top" title="" data-original-title="">
								<?php foreach ($clients as $cl) : ?>
									<option<?php if(isset($client[0]['cl_id']) && $client[0]['cl_id'] == $cl['cl_id']) : ?> selected="selected"<?php endif; ?> value="<?php echo $cl['cl_id']; ?>"><?php echo $cl['cl_name'] . " " . $cl['cl_lastname']; ?></option>
								<?php endforeach; ?>
							</select>
						</div>
					</div>
					<div class="control-group col-md-6">
						<label class="control-label">Locates</label>
						<div class="controls">
							<input class="form-control" type="text" name="stump_locates" data-toggle="tooltip" data-placement="top" title="" data-original-title="">
						</div>
					</div>
				</div>
				<div class="control-group m-b-xs">
					<label class="control-label">Info</label>
					<div class="controls">
						<textarea class="form-control" type="text" name="stump_info" data-toggle="tooltip" data-placement="top" title="" data-original-title=""></textarea>
					</div>
				</div>
				<div class="control-group m-b-xs">
					<label class="control-label">Notes</label>
					<div class="controls">
						<textarea class="form-control" type="text" name="stump_desc" data-toggle="tooltip" data-placement="top" title="" data-original-title=""></textarea>
					</div>
				</div>
				<div class="modal-footer">
					<div class="pull-right ">
						<button class="btn btn-success m-right-5" id="addStump">
						Add</button>
						<button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>
<?php //endif; ?>
<script>

	function initEditable(selector) {
		if(!selector)
			selector = '';
		$(selector + '.contractor_notes').editable({
			success: function(response, newValue) {
				response = $.parseJSON(response);
				if(response.id) {
					$('tr[data-id="' + response.id + '"]').replaceWith(response.html);
					initEditable('tr[data-id="' + response.id + '"] ');
				}
			}
		});
		$(selector + '.stump_clean').editable({
			success: function(response, newValue) {
				response = $.parseJSON(response);
				if(response.id) {
					$('tr[data-id="' + response.id + '"]').replaceWith(response.html);
					initEditable('tr[data-id="' + response.id + '"] ');
				}
			},
			params: function (params) {
				params.status = '<?php echo $status; ?>';
				params.user_id = '<?php echo $user_id; ?>';
				return params;
			}

		});
		$(selector + '.stump_removal').editable({
			/*format: 'mm/dd hh:ii',
	        datetimepicker: {
	            todayHighlight: true,
	            showMeridian: true,
	            minuteStep: 5
	        },*/
	        todayHighlight: true,
	        todayBtn: true,
	        clearBtn: true,
			success: function(response, newValue) {
				response = $.parseJSON(response);
				if(response.id) {
					$('tr[data-id="' + response.id + '"]').replaceWith(response.html);
					initEditable('tr[data-id="' + response.id + '"] ');
				}
			},
			params: function (params) {
				params.status = '<?php echo $status; ?>';
				params.user_id = '<?php echo $user_id; ?>';
				return params;
			}

		});
		$(selector + '.stump_locates').editable();
		$(selector + '.stump_range').editable();
		$(selector + '.stump_address').editable();
		$('.stump_address').on('shown', function(e, editable) {
			addressAuto.push(new google.maps.places.Autocomplete(
                ($('#stump-xeditable [data-part-address="address"]')[0]),
                { types: ['geocode'], componentRestrictions: {country: AUTOCOMPLETE_RESTRICTION} }
            ));
			autocompleteToInput($('#stump-xeditable [data-part-address="address"]'));
		});
		
		$(selector + '.stump_status').editable({
			success: function(response, newValue) {
				response = $.parseJSON(response);
				if(response.id) {
					$('tr[data-id="' + response.id + '"]').replaceWith(response.html);
					initEditable('tr[data-id="' + response.id + '"] ');
				}
			},
			params: function (params) {
				params.status = '<?php echo $status; ?>';
				params.user_id = '<?php echo $user_id; ?>';
				return params;
			},
			validate: function(value){
				if((value.stump_status == "grinded" || value.stump_status=='cleaned_up') && value.stump_assigned == null)
				{
					return '"Grind Crew" field is required';
				}
				if(value.stump_status=='cleaned_up' && value.stump_cleaned == null)
				{
					return '"Clean Crew" field is required';
				}
			}
		});
		
		$('.stump_status').on('shown', function(e, editable) {
			//autocompleteToInput($('[data-part-status="status"]'));
		});



		$(selector + '.assigned').editable({
			success: function(response, newValue) {
				response = $.parseJSON(response);
				if(response.id) {
					$('tr[data-id="' + response.id + '"]').replaceWith(response.html);
					initEditable('tr[data-id="' + response.id + '"] ');
				}
			},
			params: function (params) {
				params.status = '<?php echo $status; ?>';
				params.user_id = '<?php echo $user_id; ?>';
				return params;
			}
		});
		$(selector + '.clean_assigned').editable({
			success: function(response, newValue) {
				response = $.parseJSON(response);
				if(response.id) {
					$('tr[data-id="' + response.id + '"]').replaceWith(response.html);
					initEditable('tr[data-id="' + response.id + '"] ');
				}
			},
			params: function (params) {
				params.status = '<?php echo $status; ?>';
				params.user_id = '<?php echo $user_id; ?>';
				return params;
			}
		});
		$(selector + '.status_work').editable({
			success: function(response, newValue) {
				var text = $(this).text();
				$(this).editable('setValue', newValue);
				$(this).text(text);
				if(newValue == 0)
					$(this).parents('tr:first').css('background-color', '#FFFFFF');
				if(newValue == 1)
					$(this).parents('tr:first').css('background-color', '#95B3D7');
				if(newValue == 2)
					$(this).parents('tr:first').css('background-color', '#C3D69B');
				$('.editable-cancel').click();
				return false;
			}
			/*validate: function(value) {
				console.log(value);
			  	/*if($.trim(value) == '') 
			    return 'This field is required';
			}*/
		});

		$(selector + '.assigned_menu').editable({
			success: function(response, newValue) {
				response = $.parseJSON(response);
				if(response.status == 'ok') {
					$.each(response.data, function(key, val) {
						$('tr[data-id="' + val.stump_id + '"]').replaceWith(val.html);
						initEditable('tr[data-id="' + val.stump_id + '"] ');
					});
					$(".custom-menu").hide(100);
					$('.editable-cancel').click();
					selected = [];
				 }
				setTimeout(function(){
					$('.assigned_menu').editable('setValue', null);
				}, 50);
			},
			display: function(value) {
				var obj = $(this);
				$(this).text('Assign To Grind');
			},
			params: function (params) {
				params.status = '<?php echo $status; ?>';
				params.user_id = '<?php echo $user_id; ?>';
				params.ids = selected;
				return params;
			}
		});
		$(selector + '.clean_assigned_menu').editable({
			success: function(response, newValue) {
				response = $.parseJSON(response);
				if(response.status == 'ok') {
					$.each(response.data, function(key, val) {
						$('tr[data-id="' + val.stump_id + '"]').replaceWith(val.html);
						initEditable('tr[data-id="' + val.stump_id + '"] ');
					});
					$(".custom-menu").hide(100);
					$('.editable-cancel').click();
					selected = [];
				}
				setTimeout(function(){
					$('.clean_assigned_menu').editable('setValue', null);
				}, 50);
			},
			display: function(value) {
				var obj = $(this);
				$(this).text('Assign To Clean');
			},
			params: function (params) {
				params.status = '<?php echo $status; ?>';
				params.user_id = '<?php echo $user_id; ?>';
				params.ids = selected;
				return params;
			}
		});


		$(selector + '.status_work_menu').editable({
			success: function(response, newValue) {
				response = $.parseJSON(response);
				if(response.status == 'ok') {
					$.each(response.data, function(key, val) {
						$('tr[data-id="' + val.stump_id + '"]').replaceWith(val.html);
						initEditable('tr[data-id="' + val.stump_id + '"] ');
					});
					$(".custom-menu").hide(100);
					$('.editable-cancel').click();
					selected = [];
				 }
				setTimeout(function(){
					$('.status_work_menu').editable('setValue', null);
				}, 50);
			},
			display: function(value) {
				var obj = $(this);
				$(this).text('Status Work');
			},
			params: function (params) {
				params.status = '<?php echo $status; ?>';
				params.user_id = '<?php echo $user_id; ?>';
				params.ids = selected;
				return params;
			}
		});




		$(selector + '.status_menu').editable({
			success: function(response, newValue) {
				response = $.parseJSON(response);
				if(response.status == 'ok') {
					$.each(response.data, function(key, val) {
						$('tr[data-id="' + val.stump_id + '"]').replaceWith(val.html);
						initEditable('tr[data-id="' + val.stump_id + '"] ');
					});
					$(".custom-menu").hide(100);
					$('.editable-cancel').click();
					selected = [];
				}
				setTimeout(function(){
					$('.status_menu').editable('setValue', null);
				}, 50)
			},
			value: "<?php echo $status; ?>",
			display: function(value) {
				var obj = $(this);
				$(this).text('Change Status');
				return '<?php echo $status; ?>';
			},
			params: function (params) {
				params.status = '<?php echo $status; ?>';
				params.user_id = '<?php echo $user_id; ?>';
				params.ids = selected;
				return params;
			},
			validate: function(value){
				if((value.stump_status == "grinded" || value.stump_status=='cleaned_up') && value.stump_assigned == null)
				{
					return '"Grind Crew" field is required';
				}
				if(value.stump_status=='cleaned_up' && value.stump_cleaned == null)
				{
					return '"Clean Crew" field is required';
				}
			}
		});
		$(selector + '.stump_removal_menu').editable({
			savenochange: true,
			success: function(response, newValue) {
				response = $.parseJSON(response);
				if(response.status == 'ok') {
					$.each(response.data, function(key, val) {
						$('tr[data-id="' + val.stump_id + '"]').replaceWith(val.html);
						initEditable('tr[data-id="' + val.stump_id + '"] ');
					});
					$(".custom-menu").hide(100);
					$('.editable-cancel').click();
					selected = [];
				}
				setTimeout(function(){
					$('.stump_removal_menu').editable('setValue', null);
				}, 50);
			},
			display: function(value) {
				var obj = $(this);
				$(this).text('Grind Date');
			},
			params: function (params) {
				params.status = '<?php echo $status; ?>';
				params.user_id = '<?php echo $user_id; ?>';
				params.ids = selected;
				return params;
			}
		});
		$(selector + '.stump_clean_menu').editable({
			savenochange: true,
			success: function(response, newValue) {
				response = $.parseJSON(response);
				if(response.status == 'ok') {
					$.each(response.data, function(key, val) {
						$('tr[data-id="' + val.stump_id + '"]').replaceWith(val.html);
						initEditable('tr[data-id="' + val.stump_id + '"] ');
					});
					$(".custom-menu").hide(100);
					$('.editable-cancel').click();
					selected = [];
				}
				setTimeout(function(){
					$('.stump_clean_menu').editable('setValue', null);
				}, 50);
			},
			display: function(value) {
				var obj = $(this);
				$(this).text('Clean Date');
			},
			params: function (params) {
				params.status = '<?php echo $status; ?>';
				params.user_id = '<?php echo $user_id; ?>';
				params.ids = selected;
				return params;
			}
		});

		$(selector + '.locates_menu').editable({
			success: function(response, newValue) {
				response = $.parseJSON(response);
				if(response.status == 'ok') {
					$.each(response.data, function(key, val) {
						$('tr[data-id="' + val.stump_id + '"]').replaceWith(val.html);
						initEditable('tr[data-id="' + val.stump_id + '"] ');
					});
					$(".custom-menu").hide(100);
					$('.editable-cancel').click();
					selected = [];
				}
				setTimeout(function(){
					$('.locates_menu').editable('setValue', null);
				}, 50);
			},
			display: function(value) {
				var obj = $(this);
				$(this).text('Locates');
			},
			params: function (params) {
				params.status = '<?php echo $status; ?>';
				params.user_id = '<?php echo $user_id; ?>';
				params.ids = selected;
				return params;
			}
		});

	}

	$(document).ready(function () {
		initEditable();
		$.fn.editableform.errorGroupClass = null;
		$.fn.editableform.errorBlockClass = 'text-danger m-t m-b-none';

		$('.deleteStump').click(function () {
			var stump_id = $(this).parents('tr:first').data('id');
			
			if (confirm('Are you sure?')) {
				
				$.post(baseUrl + 'stumps/ajax_delete_stump', {stump_id : stump_id}, function (resp) {
					if (resp.status == 'ok') {
						location.reload();
						return false;
					}
					alert('Ooops! Error!');
				}, 'json');
			}
		});
		<?php if($this->session->userdata('STA') == 1 || $this->session->userdata('user_type') == "admin") : ?>
		$('#selectable').bind("contextmenu", function (event) {
			if(selected.length) {
				event.preventDefault();
				$(".custom-menu").finish().toggle(100).css({
					top: (event.pageY - $('.header').height() - 2) + "px",
					left: (event.pageX - $('#nav').width() - 2) + "px"
				});
				$('.custom-menu').find('[data-pk]').data('pk', JSON.stringify(selected));
			}
		});
		$(document).bind("mousedown", function (e) {
            if (!$(e.target).parents(".custom-menu").length > 0 && !$(e.target).parents(".datepicker").length > 0 && !$(e.target).parents(".datetimepicker").length > 0) {
				if($(".custom-menu").is(':visible')) {
					$(".custom-menu").hide(100);
					$('.editable-cancel').click();
				}
			}
		});
		$("#selectable").bind("mousedown", function(e) {
			if($(".custom-menu").is(':visible')) {
				$( "#selectable" ).selectable( "disable" );
				setTimeout(function(){
					$("#selectable").selectable("enable");
				}, 50)
			}
		}).selectable({
			filter: 'tr',
			stop: function() {
				selected = [];
				$( ".ui-selected").each(function(key, val) {
					var id = $(val).data('id');
					selected.push(id);
				});
			}
		});
		$(document).keyup(function(e) {
			if (e.keyCode === 27) {
				selected = [];
				$('#selectable .ui-selected').removeClass('ui-selected');
			}
			return false;
		});
		<?php endif; ?>
	});
	var selected = [];
</script>
<style type="text/css">
    @page {size: letter landscape;margin: 1cm 0.3cm;}
    .editable-row .editableform .editable-buttons {
        margin-top: 0;
        margin-right: 0;
    }
    .editable-row .editableform .editable-buttons .editable-submit {
          margin-left: 5px;
          margin-right: 0px;
    }

    .editable-row .editableform .editable-buttons .editable-cancel {
          margin-left: 5px;
    }
</style>
<?php $this->load->view('includes/footer'); ?>
