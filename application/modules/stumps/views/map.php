<?php $this->load->view('includes/header'); ?>
<script async src="<?php echo base_url('assets/js/StyledMarker.js'); ?>"></script>
<script async src="<?php echo base_url('assets/js/label.js'); ?>"></script>
<?php
$usersSelect[] = ['value' => 0, 'text' => 'N/A'];
foreach ($users as $active_user)
	$usersSelect[] = ['value' => $active_user->id, 'text' => $active_user->firstname . " " . $active_user->lastname];
?>
<script src="https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/markerclusterer.js"></script>
<link href="https://vitalets.github.io/x-editable/assets/bootstrap-datetimepicker/css/datetimepicker.css" rel="stylesheet">

<style>
	.gm-style-iw{overflow: visible!important;}.popover{z-index: 99999;}
	div>.gm-style-iw, .gm-style-iw>div, .gm-style-iw>div>div{font-size: 20px; overflow: visible!important;}
</style>

<link href="<?php echo base_url('assets/vendors/notebook/js/bootstrap-editable/css/bootstrap-editable.css'); ?>" rel="stylesheet">
<section class="scrollable p-sides-15 p-n mapper" style="top: 9px;-webkit-transform: translate3d(0,0,0);">
	<?php echo $map['html']; ?>
	<div class="open" style="position: initial;">
		<ul class="dropdown-menu on" style="left: auto; right: 5px; overflow: auto; top: 0;">
			<?php $href = base_url() . $this->uri->segment(1)  . '/' ;?>
			<?php if($this->uri->segment(2))
					$href .= $this->uri->segment(2) . '/';
			?>
			<?php foreach($statusesSelect as $stat) : ?>
				<li<?php if($stat['value'] == $status) : ?> class="active"<?php endif; ?>>
					<a href="<?php echo $href . $stat['value'] . '/' . $client_id; ?>">
						<?php echo ucfirst($stat['text']); ?>
						<span class="badge<?php if($stat['value'] == $status) : ?>  bg-info<?php endif; ?>">
							<?php echo $counter[$stat['value'] . '_count']; ?>
						</span>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
<ul class="custom-menu" style="min-width: 100px;">
	<?php $statusesMenu = $statusesSelect; ?>
		<div class="selected-items text-center p-5 b-b bg-light">
			<strong>
				<span class="count"></span> ITEM(S)
			</strong>
		</div>
		<div class="divider"></div>
	<?php if($status == 'new' || $status == 'canceled' || $status == 'skipped') $statusesMenu[2]['disabled'] = true; ?>

	<?php if($this->session->userdata('STA') == 1 || $this->session->userdata('user_type') == "admin") : ?>
		<li>
			<a href="#" data-pk="" data-name="stump_assigned" data-value="" data-placement="left" data-type="select" data-source='<?php echo json_encode($usersSelect); ?>' class="assigned_menu" title="Grind Crew" data-url="<?php echo base_url('stumps/ajax_update_stumps_array'); ?>"></a>
		</li>
		<li>
			<a href="#" data-pk="" data-name="stump_clean_id" data-value="" data-placement="left" data-type="select" data-source='<?php echo json_encode($usersSelect); ?>' class="clean_assigned_menu" title="Clean Crew" data-url="<?php echo base_url('stumps/ajax_update_stumps_array'); ?>"></a>
		</li>
	<?php endif; ?>
	<li>
		<a href="#" data-pk="" data-name="stump_status" data-value="{}" data-placement="left" data-type="stump_status" data-source='<?php echo json_encode(['statuses'=>$statusesMenu, 'users'=>$usersSelect]); ?>' class="status_menu" title="Status" data-url="<?php echo base_url('stumps/ajax_update_stumps_array'); ?>"></a>
	</li>
	<li>
		<a href="#" data-pk="" data-name="stump_locates" data-value="" data-placement="left" data-type="text" class="locates_menu" title="Locates" data-url="<?php echo base_url('stumps/ajax_update_stumps_array'); ?>"></a>
	</li>
	<?php if($status == 'grinded' || $status == 'cleaned_up') : ?>
		<li>
			<a href="#" data-pk="" data-name="stump_removal" data-value="" data-placement="left" data-type="datetime" class="stump_removal_menu" title="Grind Date" data-url="<?php echo base_url('stumps/ajax_update_stumps_array'); ?>"></a>
		</li>
	<?php endif; ?>
	<?php if($status == 'cleaned_up') : ?>
		<li>
			<a href="#" data-pk="" data-name="stump_clean" data-value="" data-placement="left" data-type="datetime" class="stump_clean_menu" title="Clean Date" data-url="<?php echo base_url('stumps/ajax_update_stumps_array'); ?>"></a>
		</li>
	<?php endif; ?>
</ul>
<script>
	var html;
	var selectedMarkers = [], selected = [];

	function initEditable(selector) {
		if(!selector)
			selector = '';

		$(selector + '.stump_address').editable({
			success: function(response, newValue) {
				response = $.parseJSON(response);
				if(response.html) {
					iw.anchor.content = response.html;
					latlng = new google.maps.LatLng(response.lat,response.lon);
					iw.anchor.position = latlng;
					iw.anchor.setMap(map);
					$('.infowindow').replaceWith(response.html);
					initEditable();
				}
				else {
					iw.anchor.setMap(null);
				}
			},
			params: function (params) {
				params.map = 1;
				return params;
			}
		});
		$('.stump_address').on('shown', function(e, editable) {
			autocompleteToInput($('[data-part-address="address"]'));
		});

		$(selector + '.contractor_notes').editable({
			success: function(response, newValue) {
				response = $.parseJSON(response);
				if(response.html) {
					iw.anchor.content = response.html;
					$('.infowindow').replaceWith(response.html);
					initEditable();
				}
				else {
					iw.anchor.setMap(null);
				}
			},
			params: function (params) {
				params.map = 1;
				return params;
			}
		});

		$(selector + '.stump_locates').editable({
			success: function(response, newValue) {
				response = $.parseJSON(response);
				if(response.html) {
					iw.anchor.content = response.html;
					$('.infowindow').replaceWith(response.html);
					initEditable();
				}
				else {
					iw.anchor.setMap(null);
				}
			},
			params: function (params) {
				params.map = 1;
				return params;
			}
		});
		$(selector + '.status').editable({
			success: function(response, newValue) {
				response = $.parseJSON(response);
				if(response.html) {
					iw.anchor.content = response.html;
					$('.infowindow').replaceWith(response.html);
					initEditable();
				}
				else {
					iw.anchor.setMap(null);
				}
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
			},
			params: function (params) {
				params.status = '<?php echo $status; ?>';
				params.user_id = '<?php echo $user_id; ?>';
				params.map = 1;
				return params;
			}
		});


		$(selector + '.stump_clean').editable({
			success: function(response, newValue) {
				response = $.parseJSON(response);
				if(response.id) {
					iw.anchor.content = response.html;
					$('.infowindow').replaceWith(response.html);
					initEditable();
				}
			},
			params: function (params) {
				params.status = '<?php echo $status; ?>';
				params.user_id = '<?php echo $user_id; ?>';
				params.map = 1;
				return params;
			}

		});
		$(selector + '.stump_removal').editable({
			success: function(response, newValue) {
				response = $.parseJSON(response);
				if(response.id) {
					iw.anchor.content = response.html;
					$('.infowindow').replaceWith(response.html);
					initEditable();
				}
			},
			params: function (params) {
				params.status = '<?php echo $status; ?>';
				params.user_id = '<?php echo $user_id; ?>';
				params.map = 1;
				return params;
			}

		});


		$(selector + '.assigned').editable({
			success: function(response, newValue) {
				response = $.parseJSON(response);
				if(response.html) {
					iw.anchor.content = response.html;
					iw.anchor.setIcon(response.pin);
					$('.infowindow').replaceWith(response.html);
					initEditable();
				}
				else {
					iw.anchor.setMap(null);
				}
			},
			params: function (params) {
				params.status = '<?php echo $status; ?>';
				params.user_id = '<?php echo $user_id; ?>';
				params.map = 1;
				return params;
			}
		});
		$(selector + '.clean_assigned').editable({
			success: function(response, newValue) {
				response = $.parseJSON(response);
				if(response.html) {
					iw.anchor.content = response.html;
					$('.infowindow').replaceWith(response.html);
					initEditable();
				}
				else {
					iw.anchor.setMap(null);
				}
			},
			params: function (params) {
				params.status = '<?php echo $status; ?>';
				params.user_id = '<?php echo $user_id; ?>';
				params.map = 1;
				return params;
			}
		});
		$(selector + '.status_work').editable({
			success: function(response, newValue) {
				response = $.parseJSON(response);
				iw.anchor.content = response.html;
				iw.anchor.setIcon(response.pin);
				$('.infowindow').replaceWith(response.html);
				initEditable();
				$('.editable-cancel').click();
				return false;
			},
			params: function (params) {
				params.map = 1;
				return params;
			}
		});

		$(selector + '.assigned_menu').editable({
			success: function(response, newValue) {
				response = $.parseJSON(response);
				var num = 0;
				$.each(response.data, function(key, val) {

					var marker = selectedMarkers[num];
					while(!marker) {

						if(num >= selectedMarkers.length)
							break;

						num++;
						marker = selectedMarkers[num];
					}

					if(val.html) {
						marker.content = val.html;
						//marker.setIcon(marker.oldIcon);
						marker.setIcon(val.pin);
						if($('.infowindow').find('[data-pk="' + val.stump_id + '"]').length) {
							$('.infowindow').replaceWith(val.html);
							initEditable();
						}
					}
					else {
						marker.setMap(null);
					}
					num++;
				});
				setTimeout(function(){
					$('.assigned_menu').editable('setValue', null);
				}, 500)
				$(".custom-menu").hide(100);
				$('.editable-cancel').click();
				selectedMarkers = [];
			},
			display: function(value) {
				var obj = $(this);
				$(this).text('Assign To Grind');
			},
			params: function (params) {
				params.map = 1;
				params.status = '<?php echo $status; ?>';
				params.user_id = '<?php echo $user_id; ?>';
				params.ids = selected;
				return params;
			}
		});
		$(selector + '.clean_assigned_menu').editable({
			success: function(response, newValue) {
				response = $.parseJSON(response);
				var num = 0;
				$.each(response.data, function(key, val) {

					var marker = selectedMarkers[num];
					while(!marker) {

						if(num >= selectedMarkers.length)
							break;

						num++;
						marker = selectedMarkers[num];
					}

					if(val.html) {
						marker.content = val.html;
						marker.setIcon(marker.oldIcon);
						if($('.infowindow').find('[data-pk="' + val.stump_id + '"]').length) {
							$('.infowindow').replaceWith(val.html);
							initEditable();
						}
					}
					else {
						marker.setMap(null);
					}
					num++;
				});
				setTimeout(function(){
					$('.clean_assigned_menu').editable('setValue', null);
				}, 500);
				$(".custom-menu").hide(100);
				$('.editable-cancel').click();
				selectedMarkers = [];
			},
			display: function(value) {
				var obj = $(this);
				$(this).text('Assign To Clean');
			},
			params: function (params) {
				params.map = 1;
				params.status = '<?php echo $status; ?>';
				params.user_id = '<?php echo $user_id; ?>';
				params.ids = selected;
				return params;
			}
		});
		$(selector + '.status_menu').editable({
			success: function(response, newValue) {
				response = $.parseJSON(response);
				var num = 0;
				$.each(response.data, function(key, val) {

					var marker = selectedMarkers[num];
					while(!marker) {

						if(num >= selectedMarkers.length)
							break;

						num++;
						marker = selectedMarkers[num];
					}

					if(val.html) {
						marker.content = val.html;
						marker.setIcon(marker.oldIcon);
						if($('.infowindow').find('[data-pk="' + val.stump_id + '"]').length) {
							$('.infowindow').replaceWith(val.html);
							initEditable();
						}
					}
					else {
						marker.setMap(null);
					}
					num++;
				});
				setTimeout(function(){
					$('.status_menu').editable('setValue', null);
				}, 500);
				$(".custom-menu").hide(100);
				$('.editable-cancel').click();
				selectedMarkers = [];
			},
			value: "<?php echo $status; ?>",
			display: function(value) {
				var obj = $(this);
				$(this).text('Change Status');
				return '<?php echo $status; ?>';
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
			},
			params: function (params) {
				params.map = 1;
				params.status = '<?php echo $status; ?>';
				params.user_id = '<?php echo $user_id; ?>';
				params.ids = selected;
				return params;
			}
		});
		$(selector + '.stump_removal_menu').editable({
			success: function(response, newValue) {
				response = $.parseJSON(response);
				var num = 0;
				$.each(response.data, function(key, val) {

					var marker = selectedMarkers[num];
					while(!marker) {

						if(num >= selectedMarkers.length)
							break;

						num++;
						marker = selectedMarkers[num];
					}

					if(val.html) {
						marker.content = val.html;
						marker.setIcon(marker.oldIcon);
						if($('.infowindow').find('[data-pk="' + val.stump_id + '"]').length) {
							$('.infowindow').replaceWith(val.html);
							initEditable();
						}
					}
					else {
						marker.setMap(null);
					}
					num++;
				});
				setTimeout(function(){
					$('.stump_removal_menu').editable('setValue', null);
				}, 500);
				$(".custom-menu").hide(100);
				$('.editable-cancel').click();
				selectedMarkers = [];
			},
			display: function(value) {
				var obj = $(this);
				$(this).text('Grind Date');
			},
			params: function (params) {
				params.map = 1;
				params.status = '<?php echo $status; ?>';
				params.user_id = '<?php echo $user_id; ?>';
				params.ids = selected;
				return params;
			}
		});
		$(selector + '.stump_clean_menu').editable({
			success: function(response, newValue) {
				response = $.parseJSON(response);
				var num = 0;
				$.each(response.data, function(key, val) {

					var marker = selectedMarkers[num];
					while(!marker) {

						if(num >= selectedMarkers.length)
							break;

						num++;
						marker = selectedMarkers[num];
					}

					if(val.html) {
						marker.content = val.html;
						marker.setIcon(marker.oldIcon);
						if($('.infowindow').find('[data-pk="' + val.stump_id + '"]').length) {
							$('.infowindow').replaceWith(val.html);
							initEditable();
						}
					}
					else {
						marker.setMap(null);
					}
					num++;
				});
				setTimeout(function(){
					$('.stump_clean_menu').editable('setValue', null);
				}, 500);
				$(".custom-menu").hide(100);
				$('.editable-cancel').click();
				selectedMarkers = [];
			},
			display: function(value) {
				var obj = $(this);
				$(this).text('Clean Date');
			},
			params: function (params) {
				params.map = 1;
				params.status = '<?php echo $status; ?>';
				params.user_id = '<?php echo $user_id; ?>';
				params.ids = selected;
				return params;
			}
		});
		$(selector + '.locates_menu').editable({
			success: function(response, newValue) {
				response = $.parseJSON(response);
				var num = 0;
				$.each(response.data, function(key, val) {

					var marker = selectedMarkers[num];
					while(!marker) {

						if(num >= selectedMarkers.length)
							break;

						num++;
						marker = selectedMarkers[num];
					}

					if(val.html) {
						marker.content = val.html;
						marker.setIcon(marker.oldIcon);
						if($('.infowindow').find('[data-pk="' + val.stump_id + '"]').length) {
							$('.infowindow').replaceWith(val.html);
							initEditable();
						}
					}
					else {
						marker.setMap(null);
					}
					num++;
				});
				setTimeout(function(){
					$('.locates_menu').editable('setValue', null);
				}, 500);
				$(".custom-menu").hide(100);
				$('.editable-cancel').click();
				selectedMarkers = [];
			},
			display: function(value) {
				var obj = $(this);
				$(this).text('Locates');
			},
			params: function (params) {
				params.map = 1;
				params.status = '<?php echo $status; ?>';
				params.user_id = '<?php echo $user_id; ?>';
				params.ids = selected;
				return params;
			}
		});
	}

	function mapSelectable(marker, e) {
		if (window.event.ctrlKey === false) {
			iw.setContent(marker.get("content"));
			iw.open(map, marker);
			return;
		}
		var key = selectedMarkers.indexOf(marker);
		if(key == -1) {
			marker.oldIcon = marker.icon;
			marker.setIcon('<?php echo mappin_svg('#F39814', '', FALSE); ?>');
			selectedMarkers.push(marker);
		}
		else {
			delete selectedMarkers[key];
			marker.setIcon(marker.oldIcon);
		}
		return false;
	}

	$(document).ready(function () {
		initEditable();
		$.fn.editableform.errorGroupClass = null;
		$.fn.editableform.errorBlockClass = 'text-danger m-t m-b-none';
		google.maps.event.addListener(iw, 'domready', function() {
			if(!$('.gm-style-iw').find('.editable').length)
				initEditable();
			$('.gm-style-iw').parent().css('overflow', 'visible');
		});
		$.ajax({
			type: 'POST',
			url: baseUrl + 'schedule/ajax_get_traking_position',
			global: false,
			success: function(resp){
				vehicles = resp;
				if(map !== undefined)
					displayVehicles();
				return false;
			},
			dataType: 'json'
		});
	});
	$(document).keyup(function(e) {
		if (e.keyCode === 27) {
			$.each(selectedMarkers, function(key, marker){
				if(marker)
					marker.setIcon(marker.oldIcon);
			});
			selectedMarkers = [];
		}
		return false;
	});

	$('#content').bind("contextmenu", function (event) {
		event.preventDefault();
	});

	$(document).bind("mousedown", function (e) {
		if($(".custom-menu").is(':visible') && !$(e.target).parents(".custom-menu").length) {
			$(".custom-menu").hide(100);
			$('.editable-cancel').click();
		}
		else if(e.button == 2 && selectedMarkers.length) {
			selected = [];
			$(".custom-menu").finish().toggle(100).css({
				top: (event.pageY - $('.header').height() - 2) + "px",
				left: (event.pageX - $('#nav').width() - 2) + "px"
			});
			var count = 0;
			$.each(selectedMarkers, function(key, val){
				if(val && val.cursor != undefined) {
					selected.push(val.cursor);
					count++;
				}
			});
			$('.selected-items .count').text(count);
			$('.custom-menu').find('[data-pk]').data('pk', JSON.stringify(selected));
		}
	});

	var infowindow = false;
	var vehMarkers = [];
	var vehLabels = [];
</script>
<input type="hidden" class="date-format" value="<?php echo getJSDateFormat(); ?> <?php if(getIntTimeFormat()==12): ?>H:ii p<?php else: ?>hh:mm<?php endif; ?>">
<?php if(getIntTimeFormat()==12): ?>
<input type="hidden" class="time-format" value="true">
<?php endif; ?>

<img src="<?php echo mappin_svg('#F39814', '', FALSE); ?>" style="visibility: hidden;">
<?php $this->load->view('includes/footer'); ?>
