<link rel="stylesheet" href="<?php echo base_url('assets/css/modules/schedule/schedule.css?v='.config_item('schedule.css')); ?>">
<?php echo $map1['js']; ?>
<?php /*<script src="http://www.xarg.org/download/pnglib.js"></script>*/ ?>
<script async src="<?php echo base_url('assets/js/StyledMarker.js'); ?>"></script>
<script async src="<?php echo base_url('assets/js/label.js'); ?>"></script>
<style>
	.col-md-4.text-right.b-a.m-l-sm.bg-light {
		line-height: 22px!important;
	    white-space: nowrap!important;
	    padding-left: 10px!important;
	}
</style>
<div class="dhx_cal_light dhx_cal_light_wide" style="display:none;"></div>
<script>
	var trackerItems = <?php echo json_encode($tracks); ?>;
	var mapClick = false;
	var labelClick = false;
	var infowindow = false;
	var objects = <?php echo $objects ?>;
	var vehMarkers = [];
	var objMarkers = [];
	google.maps.event.addDomListener(window, 'load', function(){
		setTimeout(function(){
			/***DISPLAY STATIC MAP OBJECTS***/
				$.each(objects, function(key, val){
					color = val.object_color;
					var latLng = new google.maps.LatLng(val.object_latitude,val.object_longitude);
					objMarkers[key] = new google.maps.Marker({
						position: latLng,
						map: map,
						title: val.object_desc,
						code: val.object_name,
						icon: 'data:image/svg+xml;base64,' + btoa('<svg xmlns="http://www.w3.org/2000/svg" width="25" height="52" viewBox="0 0 38 38"><path fill="' + color + '" stroke="#000" stroke-width="2" d="M34.305 16.234c0 8.83-15.148 19.158-15.148 19.158S3.507 25.065 3.507 16.1c0-8.505 6.894-14.304 15.4-14.304 8.504 0 15.398 5.933 15.398 14.438z"/><text transform="translate(19 25)" fill="#000" style="font-family: Arial, sans-serif;font-weight:bold;text-align:center;" font-size="12" text-anchor="middle">&#9899;</text></svg>')
						//icon: 'https://chart.apis.google.com/chart?chst=d_map_pin_letter&chld=%E2%80%A2|' + color
					});

					var label = new Label({
						map: map
					});
					label.bindTo('position', objMarkers[key], 'position');
					label.bindTo('text', objMarkers[key], 'code');

					google.maps.event.addListener(objMarkers[key], 'click', function() {
						if (infowindow) infowindow.close();
						infowindow = new google.maps.InfoWindow({
							content: '<div>' + objMarkers[key].title + '</div>',
						});
						infowindow.open(map, this);
					});
				});
				/***DISPLAY STATIC MAP OBJECTS***/

			$.each(markers, function (key, val) {
				var color = "#ffffff";
				var displayVehiclesdisplayVehicleswo_id = $(markers[key].content).data('map-wo-id');

				wo_id = displayVehiclesdisplayVehicleswo_id;
				if($('[data-event_wo_id="' + wo_id + '"]').length)
					color = $('[data-event_wo_id="' + wo_id + '"]').data('event_wo_color');
				var crewType = $(markers[key].content).data('map-crew-type') ? ' (' + $(markers[key].content).data('map-crew-type') + ')' : '';


				/*var styleMaker = new StyledMarker({
					styleIcon: new StyledIcon(StyledIconTypes.BUBBLE, {
						color: color,
						text: $(markers[key].content).data('map-wo-price') + crewType
					}),
					position: new google.maps.LatLng(markers[key].getPosition().lat(), markers[key].getPosition().lng()),
					content: markers[key].content,
					map: map
				});*/
				//console.log(markers[key]);
				var myLatlng = new google.maps.LatLng(markers[key].getPosition().lat(), markers[key].getPosition().lng());
				var styleMaker = new CustomMarker(myLatlng, map, {
					content: markers[key].content,
					position: new google.maps.LatLng(markers[key].getPosition().lat(), markers[key].getPosition().lng()),
					map: map,
					pinText:$(markers[key].content).data('map-wo-price') + crewType,
					icon: get_bubble_icon($(markers[key].content).data('map-crew-type'), $(markers[key].content).data('map-wo-price'))
				});

				markers[key] = styleMaker;

				google.maps.event.addListener(styleMaker, "click", function() {
					if (infowindow) infowindow.close();

					map.setZoom(15);
					map.panTo(markers[key].getPosition());
					map.setCenter(markers[key].getPosition());

					infowindow = new google.maps.InfoWindow({
						content: markers[key].content
					});
					infowindow.open(map, this);

					mapClick = true;
					if (!labelClick) {
						var wo_id = $(styleMaker.content).data('map-wo-id');
						var obj = $('.label-wo[data-label-wo-id="' + wo_id + '"]');
						$('[href="#' + $(obj).parents('.tab-pane').attr('id') + '"][data-toggle="tab"]').click();
						//$(obj).click();
					}
					$(obj).click();
					mapClick = false;
				});

				//console.log(wo_id);

				if(!$('[data-label-wo-id="' + wo_id + '"]').is(':visible'))
					markers[key].setVisible(false);
			});
		}, 2000);
		$(document).on('change', '.showStatus', function(){
			var statusId = $(this).data('status_id');
			var visible = $(this).is(':checked');
			if(infowindow) infowindow.close();
			if(!statusId)
			{
				$('[data-status_id]').prop('checked', visible);
				$.each(markers, function(key, val){
					markers[key].setVisible(visible);
				});
			}
			else
			{
				$.each(markers, function(key, val){
					var wo_status = $(val.content).data('map-wo-status');
					if(statusId == wo_status)
						markers[key].setVisible(visible);
					/*else
						markers[key].setVisible(false)*/
				});
			}
		});
		$(document).on('click', '#wo_status li a', function(){
			if($('[data-status_id="0"]').is(':checked'))
				return false;
			if($('[data-status_id]:checked').length == 1)
			{
				var obj = $('[data-status_id]:checked');
				$(obj).prop('checked', false);
				$(obj).change();
			}
			var statusId = $(this).data('statuid');
			$('[data-status_id="' + statusId + '"]').prop('checked', true);
			$('[data-status_id="' + statusId + '"]').change();
		});
		$(document).on('click', '#woSearchShow', function(){
			$('#searchSchedule').find('[name="search_keyword"]').focus();
			return false;
		});
		$(document).on('submit', '#searchSchedule', function(){
			var search_keyword = $('#searchSchedule').find('[name="search_keyword"]').val();
			$.post(baseUrl + 'schedule/ajax_workorders_search', {search_keyword:search_keyword}, function(resp){
				if(resp.status == 'ok')
					$('#schedulesearch [data-toggle="buttons"]').html(resp.html);
				else
					$('#schedulesearch [data-toggle="buttons"]').html('<div class="alert alert-danger" style="font-size: 13px;"><i class="fa fa-ban-circle"></i>No record found.</div>');
				$('#woSearch').find('.badge').addClass('bg-info').text($('#schedulesearch').find('label').length);
				$('[href="#schedulesearch"]').click();
				setTimeout(function(){
					$.each(markers, function(key, val){
						var wo_id = $(val.content).data('map-wo-id');
						if($('[data-label-wo-id="'+wo_id+'"]').is(':visible'))
							markers[key].setVisible(true);
						else
							markers[key].setVisible(false);
					});
				}, 500);
				return false;
			}, 'json');
			return false;
		});
		$(document).on('click', 'html', function(event) {
			if ($(event.target).closest('.dhx_cal_event').length || $(event.target).closest(".dhx_cal_light").length || $(event.target).closest("#scheduler_here").length) return true;
			event.stopPropagation();
		});

		$('html').on('click', function (e) {
			if ($(e.target).data('toggle') == 'popover') {
				$('.popover.fade.bottom.in').css({'left': '5px', 'max-width': '98%', 'width': '100%'});
				$('.popover.fade.bottom.in .arrow').css({'left': ($(e.target).offset().left - $(e.target).parent().offset().left - 10 + (($(e.target).width() + 24) / 2)) + 'px'});
				$('[data-original-title]').not(e.target).popover('hide');
			}
			/*if (typeof $(e.target).data('original-title') == 'undefined' && !$(e.target).parents().is('.popover.in') && !$(e.target).parents().is('#map_canvas')) {
			 $('[data-original-title]').popover('hide');
			 }*/
		});
		$(document).on('click', '.label-wo', function (e) {
			labelClick = true;
			var obj = $(this);
			var wo_id = $(this).data('label-wo-id');
			var wo_no = $(this).data('label-wo-no');
			var id = $('.dhx_cal_larea').data('id');
			$('.label-wo.active').removeClass('active');
			$(this).addClass('active');
			if(!$(obj).attr('data-content'))
			{
				$(obj).attr('data-original-title', '<button type="button" class="close pull-right" data-dismiss="popover">×</button><a href="' + baseUrl + wo_no + '" target="_blank">' + wo_no + '</a>');
				$.post(baseUrl + 'schedule/ajax_workorder_details', {wo_id:wo_id, id:id}, function(resp){
					if(resp.status == 'ok')
						$(obj).attr('data-content', resp.html);
					else
						alert('Ooops!!! Error');

					$('[data-label-wo-id="' + wo_id + '"]').popover('show');
					$('.popover.fade.bottom.in').css({'left': '5px', 'max-width': '98%', 'width': '100%'});
					$('.popover.fade.bottom.in .arrow').css({'left': ($(e.target).offset().left - $(e.target).parent().offset().left - 10 + (($(e.target).width() + 24) / 2)) + 'px'});
					return false;
				}, 'json');
			}
			if (!mapClick) {
				$.each(markers, function (key, val) {
					if ($(val.content).data('map-wo-id') == wo_id) {
						google.maps.event.trigger(markers[key], 'click', {
							latLng: new google.maps.LatLng(0, 0)
						});
						map.setZoom(15);
						map.panTo(markers[key].position);
						map.setCenter(markers[key].getPosition());
						return false;
					}
				});
			}
			labelClick = false;
			return false;
		});

		$(document).on('click', '.showFilter', function(){
			$('.filters').slideToggle();
			return false;
		});

		$(document).on('click', '.clearFilter', function(){
			$('.filters .filter').val('');
			$('.filter').trigger('keyup');
			return false;
		});

		$(document).on('keyup', '.filter', function(){
			var selector = 'label.label-wo';
			var addSelector = '';
			var count = 0;
			$(selector).hide();
			$.each($('.filters .filter'), function(key, val){
				var attr = 'data-' + $(val).data('name');
				if($(val).val()) {
					count++;
					addSelector += '[' + attr + '="' + $(val).val() + '"]';
				}
				selector += addSelector;
			});
			$(selector).show();
			if(count)
				$('.clearFilter').show();
			else
				$('.clearFilter').hide();
			$('.clearFilter .badge').text(count);

			var statusId = $('#wo_status li.active a').data('statuid');
			$.each(markers, function(key, val){
				var wo_status = $(val.content).data('map-wo-status');
				if((statusId == wo_status && $(val.content).is(addSelector)) || (statusId == wo_status && !addSelector)) {
					markers[key].setVisible(true);
				}
				else
					markers[key].setVisible(false);
			});
		});
		
		$.ajax({
			type: 'POST',
			url: baseUrl + 'schedule/ajax_get_traking_position',
			data: {trucks:trackerItems},
			global: false,
			success: function(resp){
				vehicles = resp;
				if(map !== undefined)
					displayVehicles(); 
				return false;
			},
			dataType: 'json'
		});
		google.maps.event.addDomListener(window, 'load', function(){
			//displayVehicles();
		});
	});

	$(document).on('click', '.popover-title a', function(){
		window.open($(this).attr('href'), '_blank');
		window.focus();
	});



	function CustomMarker(latlng, map, args) {
		this.latlng = latlng;	
		this.args = args;	
		this.setMap(map);	
	}

	CustomMarker.prototype = new google.maps.OverlayView();

	CustomMarker.prototype.draw = function() {
		
		var self = this;
		
		var div = this.div;

		//console.log(self.args);

		if (!div) {
		
			div = this.div = document.createElement('div');
			div.className = 'marker';
			div.style.position = 'absolute';
			div.style.cursor = 'pointer';
			div.style.display = 'block';
			div.style.width = 'auto';
			div.style.height = 'auto';
			div.style.background = '#fff';
			div.style.border = '1px solid rgb(146, 146, 146)';
			div.style.padding = '8px';
			div.style.color = '#000';
			div.style.borderRadius = '5px';
			div.style.fontSize = '14px';
			div.style.minWidth = '100px';
			div.style.textAlign = 'center';
			div.innerHTML = self.args.pinText;


			if (typeof(self.args.marker_id) !== 'undefined') {
				div.dataset.marker_id = self.args.marker_id;
			}
			
			
			google.maps.event.addDomListener(div, "click", function(event) {			
				google.maps.event.trigger(self, "click");
			});
			
			var panes = this.getPanes();
			panes.overlayImage.appendChild(div);
		}
		
		var point = this.getProjection().fromLatLngToDivPixel(this.latlng);
		
		if (point) {
			div.style.left = point.x-$(div).width()/2 + 'px';
			div.style.top = point.y + 'px';
		}
	};

	CustomMarker.prototype.remove = function() {
		if (this.div) {
			this.div.parentNode.removeChild(this.div);
			this.div = null;
		}	
	};

	CustomMarker.prototype.setVisible = function(visible) {
		if (this.div) {
			var display = 'block';
			if(!visible)
				display = 'none';
			this.div.style.display = display;
		}
	}

	CustomMarker.prototype.getPosition = function() {
		return this.latlng;	
	};

	CustomMarker.prototype.getContent = function() {
		return this.args.content;	
	};
</script>