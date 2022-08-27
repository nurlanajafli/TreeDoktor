var lat_longs = new Array();
var Clients = function(){
	var config = {
		autoscroll_enabled:false,
		ui:{
			scheduled_lead_date:'.scheduled_lead_date',
			datepicker:'.datepicker',
			select_schedule_date:'#select-schedule-date',
			task_author_id:'[name="task_author_id"]',
			task_date:'[name="task_date"]',

			appointment_modal:'#schedule-appointment-modal',
			get_appointment_modal:'#get-appointment-modal',
			get_edit_form_lead_id:'#get-edit-form [name="id"]',
			lead_details_modal:'#lead-details-modal',
			edit_lead_form_client_id: '.edit-lead-form [name="client_id"]',

			client_id: '#client_id',
			client_lat: '[name="client_lat"]',
			client_lon: '[name="client_lon"]',
			client_address: '[name="client_address"]',
			client_city: '[name="client_city"]',
			client_state: '[name="client_state"]',
			client_zip: '[name="client_zip"]',
			client_country: '[name="client_country"]',
			client_name: '#inputSuccess',
			client_contact_email: '.client-email',
			client_contact_phone: '.client-phone',
			client_new_address: '[name="new_client_address"]',
			contact_name: '.contact-name',

			appointment_error: '.appointment-error',
			/*-------------client, contact or lead address-------------------*/
			appointments_map:'#appointments-map',
			appointments_map_id:'appointments-map',
			appointments_map_main_check:'[name="new_add"]:checked',
			appointment_map_address:'[name="formatted_address"]',
			appointment_map_address_main:'[name="new_address"]',
			appointment_map_address_main_contact_checkbox:'[name="client_print[]"]:checked',

			appointment_map_lat:'[name="new_client_lat"]',
			appointment_map_lat_main:'[name="new_lat"]',
			appointment_map_lon:'[name="new_client_lon"]',
			appointment_map_lon_main:'[name="new_lon"]',
			/*-------------client, contact or lead address-------------------*/

			/*-------------appointment modal data ------------------*/
			appointment_address:'[name="appointment_address"]',
			appointment_lat:'[name="appointment_lat"]',
			appointment_lon:'[name="appointment_lon"]',
			appointment_lead_id:'#get-appointment-modal [name="lead_id"]',
			id_client:'[name="id_client"]',
			/*-------------appointment modal data ------------------*/

			appointments_estimators_list:'#appointments-estimators-list',
			free_schedule_times:'.free-schedule-times',
			checked_radio:'input[type="radio"]:checked',

			scheduled_first_step:'.steps li[data-target="#step1"]',
			scheduled_two_step:'.steps li[data-target="#step2"]',
			scheduled_last_step:'.steps li[data-target="#step3"]',

			/*wizard:'.wizard',*/
			schedule_end_time:'.schedule-duration-value.in input[name="schedule_interval_end"]',
			schedule_start_time:'.schedule-duration-value.in input[name="schedule_interval_start"]',
			schedule_duration_inputs:'.schedule-duration-value input[name="schedule_interval_end"], .schedule-duration-value input[name="schedule_interval_start"]',
			scheduled_block: '.scheduled-block',
			scheduled_block_username: '.scheduled-block-username',
			scheduled_block_date: '.scheduled-block-date',
			scheduled_block_start: '.scheduled-block-start',
			scheduled_block_end: '.scheduled-block-end',

			scheduled_input_username: '[name="scheduled_user_name"]',
			scheduled_input_user_id: '[name="scheduled_user_id"]',
			scheduled_input_date: '[name="scheduled_date"]',
			scheduled_input_start: '[name="scheduled_start_time"]',
			scheduled_input_end: '[name="scheduled_end_time"]',
			scheduled_input_task_category:'[name="task_category"]',
			remove_schedule: '.remove-schedule',

			appointment_recomendations:'.appointment-recomendations',
			lead_priority: "[name='new_lead_priority']",
			schedule_lead_priority: "[name='schedule_lead_priority']",

			preliminary_estimate: '[name="preliminary_estimate"]:checked',
			lead_preliminary_estimate: '[name="lead_preliminary_estimate"]',

			appointment_type_template:'#appointment-type-template',
			appointment_type:'#appointment-type',
			appointment_lead:'#appointment-lead',
			appointment_lead_template:'#appointment-lead-template',
			notification_checkbox:'.notification-checkbox',
			appointment_estimator_template:'#appointment-estimator-template',
			appointment_estimator:'#appointment-estimator',

			client_sms: '[name="notify_client_sms"]',
			client_email: '[name="notify_client_email"]',
			estimator_sms: '[name="notify_estimator_sms"]',
			estimator_email: '[name="notify_estimator_email"]',

			is_client_sms: '[name="is_client_sms"]',
			is_client_email: '[name="is_client_email"]',
			is_estimator_sms: '[name="is_estimator_sms"]',
			is_estimator_email: '[name="is_estimator_email"]',

			infowindow_container: '.infowindow-container',

			selected_appointment:'.selected-estimator-appointment-today',
			selected_appointmentClassName:'selected-estimator-appointment-today',
			appointment_today: '.estimator-appointment-today',
			marker_key: 'input[name="marker_key"]',
			estimator_input: '[name="estimators"]',
			items_div: '.items-div',
		},

		events:{
			scheduled_check:'.scheduledLead',
			scheduled_date:'[name="scheduled_date"]',
			scheduled_datepicker:'#scheduled-datepicker',
			scheduled_iterval_radio:'[name="time_interval"]',

			assigned_to:'[name="assigned_to"]',
			task_author_radio:'[name="task_author_radio"]',
			selected_estimator_name:'[name="selected_estimator_name"]',

			scheduled_next:'.scheduled-next.btn-next',
			scheduled_prev:'.scheduled-prev.btn-prev',
			estimator_appointment:'.estimator-appointment',

			add_appointment:'#add-appointment',
			add_appointment_task:'#add-appointment-task',
			create_appointment_modal:'.create_appointment_modal',

			another_address_checkbox:'.another-address-checkbox',

			project_edit: '.project-edit',
			project_create: '.project-create',
			project_delete: '.project-delete',

			/*client_name: '#inputSuccess',*/
			client_address: '[name="client_address"]',
			client_contact_email: '.client-email',
			client_contact_phone: '.client-phone',
			client_new_address: '[name="new_client_address"]',
			contact_name: '.contact-name',
			appointment_estimator : '#appointment-estimator',
			remove_schedule: '.remove-schedule',
		},
		route:{

		},

		templates:{
			infowindow: {
				task: '#task-infowindow-tmp'
			},
		},

		views: {
			infowindow:'#map-infowindow',
		},

		select2:[]
	}

	var infowindow;
	var assigned_user_id = 0;
	var create_task_form = false;

	var directionDisplay;
  	var directionsService = new google.maps.DirectionsService();
  	var map;
  	var date_points = {};
  	var parent_form = '';

  	var markers = {};
	var double_positions = {};
	var _private = {
		init:function(){
			_private.init_select2();
		},
		init_select2: function(){

			Common.init_select2(config.select2);
		},

		get_appointment_modal:function(){
			let address = false;
			let lat = false;
			let lon = false;
			let lead_id = '';

			$(config.ui.appointment_address+', '+config.ui.appointment_lat+', '+config.ui.appointment_lon).val('');

			if($(config.ui.appointment_map_address_main)!=undefined && $(config.ui.appointment_map_lat_main)!=undefined && $(config.ui.appointment_map_lon_main)!=undefined)
			{
				address = $(config.ui.appointment_map_address).val();
				lat = $(config.ui.appointment_map_lat).val();
				lon = $(config.ui.appointment_map_lon).val();

				if($(config.ui.appointment_map_address_main).val().length!==0 && $(config.ui.appointment_map_lat_main).val().length!==0 && $(config.ui.appointment_map_lon_main).val().length!==0)
				{
					address = $(config.ui.appointment_map_address_main).val();
					lat = $(config.ui.appointment_map_lat_main).val();
					lon = $(config.ui.appointment_map_lon_main).val();
				}

				if(address!=false && lat!=false && lon!=false){
					$(config.ui.appointment_address).val(address);
					$(config.ui.appointment_lat).val(lat);
					$(config.ui.appointment_lon).val(lon);
				}
			}
			priority = $(config.ui.lead_priority).val();
			$(config.ui.schedule_lead_priority).val(priority);
			if($(config.ui.client_id)!=undefined) {
				client_id = $(config.ui.client_id).val();
				if((client_id === undefined || client_id.trim() === '') && $(config.ui.edit_lead_form_client_id).val())
					client_id = $(config.ui.edit_lead_form_client_id).val();
				$(config.ui.id_client).val(client_id);
			}
			preliminary_estimate = $(config.ui.preliminary_estimate).val();
			$(config.ui.lead_preliminary_estimate).val(preliminary_estimate);
			if($(config.ui.lead_details_modal).is(':visible')) {
				lead_id = $(config.ui.get_edit_form_lead_id).val();
			} else if($('.editLeadForm').length) {
				lead_id = $('.editLeadForm input[name="lead_id"]').val();
			}
			$(config.ui.appointment_lead_id).val(lead_id);

			$(config.ui.get_appointment_modal).trigger('submit');
		},

		remove_schedule: function(){
			$(config.ui.appointment_modal+' input[type="text"]').val("");
			$(config.ui.appointment_modal+' input[type="number"]').val("");
			$(config.ui.appointment_modal+' '+config.ui.checked_radio).removeAttr('checked').trigger('change');
			$(config.events.scheduled_datepicker).val("").datepicker("update");
			$(config.ui.scheduled_first_step).trigger('click');

			$(config.ui.estimator_input).val('');
			$(config.ui.estimator_input).trigger('change');
			$(config.ui.estimator_input).select2("enable", true);
			$(config.ui.estimator_input).parent().find('.text-danger').remove();
			$(config.ui.scheduled_block+' input').val('');
			$(config.ui.scheduled_block).hide();
		},

		scheduled_check: function() {
			parent_form = $(this).closest('form');
			localStorage.removeItem('appointment_type');
			_private.get_appointment_modal();
			return;
			if($(parent_form).find(config.events.scheduled_check).prop('checked')==true){
				localStorage.removeItem('appointment_type');
				_private.get_appointment_modal();
			}
			else{

				$(config.ui.appointment_modal+' input[type="text"]').val("");
				$(config.ui.appointment_modal+' input[type="number"]').val("");
				$(config.ui.appointment_modal+' '+config.ui.checked_radio).removeAttr('checked').trigger('change');
				$(config.events.scheduled_datepicker).val("").datepicker("update");
				$(config.ui.scheduled_first_step).trigger('click');

				$(config.ui.scheduled_block+' input').val('');
				$(config.ui.scheduled_block).hide();
			}
			return false;
		},

		init_map:function(){

			directionsDisplay = new google.maps.DirectionsRenderer({
		      suppressMarkers: true
		    });
		    var centermap = new google.maps.LatLng($(config.ui.appointments_map).data('origin_lat'), $(config.ui.appointments_map).data('origin_lon'));
		    var myOptions = {
		      zoom: 8,
		      mapTypeId: google.maps.MapTypeId.ROADMAP,
		      center: centermap,
		    }
		    map = new google.maps.Map(document.getElementById(config.ui.appointments_map_id), myOptions);
		   	directionsDisplay.setMap(map);

		   	infowindow = new google.maps.InfoWindow({maxWidth: 600, minHeight: 900, buttons:{close:{visible: false}} });

		   	if(mapCircles !== undefined && mapCircles.length) {
		   		$.each(mapCircles, function (key, val) {
		   			if(val.lat === undefined || val.lng === undefined || val.radius === undefined)
		   				return false;

					var circleCenter = new google.maps.LatLng(val.lat, val.lng);

					lat_longs.push(new google.maps.LatLng(val.lat, val.lng));

					var circleOptions = {
						strokeColor: val.strokeColor !== undefined ? val.strokeColor : "#FF0000",
						strokeOpacity: val.strokeOpacity !== undefined ? val.strokeOpacity : 0.3,
						strokeWeight: val.strokeWeight !== undefined ? val.strokeWeight : 1,
						fillColor: val.fillColor !== undefined ? val.fillColor : "#FF0000",
						fillOpacity: val.fillOpacity !== undefined ? val.fillOpacity : 0.1,
						map: map,
						center: circleCenter,
						radius: val.radius
					};
					window.mapCirclesArr[key] = new google.maps.Circle(circleOptions);
				});
			}

			_private.setAppointments();
		},

		setAppointments:function(){

			if(Object.keys(markers).length!=0){
				$.each(markers, function(key, value){ markers[key].setMap(null); });
			}

			var pinLabel = '';
			markers = {};

			task_position = _private.getTaskPosition();
			var home_icon = 'data:image/svg+xml;base64,' + btoa('<svg version="1.1" width="35px" height="35px" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 512.533 512.533" style="enable-background:new 0 0 512.533 512.533;" xml:space="preserve"><path style="fill:#F3705B;" d="M406.6,62.4c-83.2-83.2-217.6-83.2-299.733,0c-83.2,83.2-83.2,216.533,0,299.733l149.333,150.4L405.533,363.2C488.733,280,488.733,145.6,406.6,62.4z"/><path style="fill:#F3F3F3;" d="M256.2,70.933c-77.867,0-141.867,62.933-141.867,141.867c0,77.867,62.933,141.867,141.867,141.867c77.867,0,141.867-62.933,141.867-141.867S334.066,70.933,256.2,70.933z"/><polygon style="fill:#FFD15D;" points="256.2,112.533 176.2,191.467 176.2,305.6 336.2,305.6 336.2,191.467 "/><g><rect x="229.533" y="241.6" style="fill:#435B6C;" width="54.4" height="64"/><path style="fill:#435B6C;" d="M356.466,195.733L264.733,104c-4.267-4.267-11.733-4.267-17.067,0l-91.733,91.733c-4.267,4.267-4.267,11.733,0,17.067c4.267,4.267,11.733,4.267,17.067,0l83.2-84.267l83.2,83.2c2.133,2.133,5.333,3.2,8.533,3.2c3.2,0,6.4-1.067,8.533-3.2C360.733,207.467,360.733,200,356.466,195.733z"/></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g></svg>');
			home = new google.maps.LatLng(window.office_position.lat, window.office_position.lon);
			new google.maps.Marker({
				position: home,
				map: map,
				icon: home_icon
			});

			var star_icon = 'data:image/svg+xml;base64,' + btoa('<svg xmlns="http://www.w3.org/2000/svg" width="25" height="52" viewBox="0 0 38 38"><path fill="#ffffff" stroke="#000" stroke-width="2" d="M34.305 16.234c0 8.83-15.148 19.158-15.148 19.158S3.507 25.065 3.507 16.1c0-8.505 6.894-14.304 15.4-14.304 8.504 0 15.398 5.933 15.398 14.438z"/><text transform="translate(15 15)" stroke="#000" stroke-width="1" fill="#fff378" x="3.5" y="10" style="font-family: Arial, sans-serif;font-weight:bold;text-align:center;" font-size="25" text-anchor="middle">&#9733;</text></svg>');
			if(task_position.lat && task_position.lon)
			{
				new google.maps.Marker({
					position: new google.maps.LatLng(task_position.lat, task_position.lon),
					map: map,
					icon: star_icon
				});
			}

			var est_appointments = window.appointments;
			$(config.ui.appointments_estimators_list).html('<li class="list-group-item text-center"><h4><i class="fa fa-map-marker"></i>&nbsp;No appointments</h4></li>');
			if(est_appointments.length==0)
				return;

			$(config.ui.appointments_estimators_list).html('');
			double_positions = {};
			let cnt = 0;
			$.each(est_appointments, function(key, point){

				//var marker_key = point.task_latitude+'_'+point.task_longitude;
				var marker_key = point.task_id;
				if(markers[marker_key]!=undefined){
					markers[marker_key].visible = false;
				}

				var formated_times = _private.formatedTaskTime(point);
				_private.placeMarker(point);

				current_appointment = new google.maps.LatLng(point.task_latitude, point.task_longitude);
				var request = {
				    origin: home,
				    destination: current_appointment,
				    waypoints: [],
				    optimizeWaypoints: true,
				    travelMode: google.maps.DirectionsTravelMode.DRIVING
				};
				directionsService.route(request, function(response, status) {
				  if (status == google.maps.DirectionsStatus.OK) {
				  	var current_lat = response.request.destination.location.lat();
				  	var current_lon = response.request.destination.location.lng();
				  	current = new google.maps.LatLng(current_lat, current_lon);
					distance = google.maps.geometry.spherical.computeDistanceBetween(home, current);

				    	var totalDist = 0;
				      	var totalTime = 0;
				      	var myroute = response.routes[0];
				      	for (i = 0; i < myroute.legs.length; i++) {
				       		totalDist += myroute.legs[i].distance.value;
				        	totalTime += myroute.legs[i].duration.value;
				      	}

				      	var travelTimeCalc = (totalTime / 3600).toFixed(2);
				      	if(travelTimeCalc<1)
				      		travelTimeCalc = (totalTime / 60).toFixed(2)+' mins.';
				      	else
				      		travelTimeCalc+=' hrs';

					    travelTimeCalc = 'Travel: '+travelTimeCalc;
					  	distance = (distance/1000).toFixed(2) + ' km.';

				  } else {
					  travelTimeCalc = '-';
					  distance = '-';
				  }

				  name = (key+1)+') '+point.ass_firstname+' '+point.ass_lastname;
				  dist = '<span class="label label-info">'+distance+'</span>';
				  time = '<span class="label label-success">' + formated_times.interval + '</span>';

				  travelTime = '<span class="label label-warning">'+travelTimeCalc+'</span>';
				  text = '<li class="list-group-item estimator-appointment-today p-10"  data-marker_key="'+marker_key+'" data-id="'+point.task_id+'" data-estimator="'+point.task_assigned_user+'" data-pos="'+(key+1)+'">'+name+' '+'<br>'+time+'&nbsp;&nbsp;'+dist+'&nbsp;&nbsp;'+travelTime+'</li>';
				  $(config.ui.appointments_estimators_list).append(text);

				  var items = $('.estimator-appointment-today');
				  items.sort(function(a, b){
				  	return +$(a).data('pos') - +$(b).data('pos');
				  });
				  items.appendTo(config.ui.appointments_estimators_list);

				  cnt++;
				  if($(config.ui.appointment_estimator).val() > 0 && cnt === est_appointments.length)
				  	$(config.ui.appointment_estimator).trigger('change');
				});
			});

			setTimeout(function () {
				var doubles = Object.values(double_positions).filter(positions => positions.length > 1);
				_private.update_double_positions(doubles);
			}, 1000);
		},

		placeMarker: function(marker){

			if(marker.task_latitude == undefined || marker.task_longitude == undefined)
				return false;

			if(double_positions[marker.task_latitude+'_'+marker.task_longitude]==undefined)
				double_positions[marker.task_latitude+'_'+marker.task_longitude] = [];

			double_positions[marker.task_latitude+'_'+marker.task_longitude].push(marker.task_id);

			var position = new google.maps.LatLng(marker.task_latitude, marker.task_longitude);
			var key = marker.task_id; //marker.task_latitude+'_'+marker.task_longitude;
			var formated_times = _private.formatedTaskTime(marker);

			pinLabel = marker.ass_firstname.charAt(0)+marker.ass_lastname.charAt(0);
			marker_icon_text = '<text transform="translate(245 150)" fill="#000" style="font-family: Arial, sans-serif;font-weight:bold;text-align:center;text-shadow: 0px 1px 1px rgba(0,0,0,0.55)" font-size="145" text-anchor="middle">'+pinLabel+'</text>';
			marker_icon_text += '<text transform="translate(240 270)" fill="#000" style="font-family: Arial, sans-serif;font-weight:bold;text-align:center;" font-size="110" text-anchor="middle">'+formated_times.formated_time_start+'</text>';
			marker_icon = '<svg xmlns="http://www.w3.org/2000/svg" height="37px" width="37px" viewBox="0 0 480 480"><path fill="' + marker.ass_color +'" stroke="#000000" stroke-width="5" fill-rule="nonzero" marker-start="" marker-mid="" marker-end="" id="svg_16" d="M11.186155649009088,476.0885041676267 L103.15873182946643,311.96152697217735 L103.15873182946643,311.96152697217735 C5.7211678769939365,262.4517019249397 -22.082518426899092,169.18577638907354 38.9245432416211,96.49309300509664 C99.93046014410076,23.800830456576065 229.78398858465704,-4.52719321136221 338.9146322808233,31.04856760666257 C448.04524461353026,66.62412580024365 500.74136732913627,154.45988688920258 460.65260040526954,233.97186576659885 C420.5659505145268,313.4838134709767 300.8158765142848,358.6497282889444 184.00005749974966,338.315473910676 L11.186155649009088,476.0885041676267 z" style="color: rgb(0, 0, 0);" class=""/>'+marker_icon_text+'</svg>';

			//'<svg xmlns="http://www.w3.org/2000/svg" width="25" height="52" viewBox="0 0 38 38"><path fill="' + point.ass_color + '" stroke="#000" stroke-width="2" d="M34.305 16.234c0 8.83-15.148 19.158-15.148 19.158S3.507 25.065 3.507 16.1c0-8.505 6.894-14.304 15.4-14.304 8.504 0 15.398 5.933 15.398 14.438z"/><text transform="translate(19 22)" fill="#000" style="font-family: Arial, sans-serif;font-weight:bold;text-align:center;" font-size="16" text-anchor="middle">' + pinLabel + '</text></svg>'

			markers[key] = new google.maps.Marker({
				position: position,
				map: map,
				icon: 'data:image/svg+xml;base64,' + btoa(marker_icon),
				label: {color: '#000', fontSize: '0px', fontWeight: '600', text: pinLabel}
			});

			markers[key]['point'] = marker;
			markers[key]['point']['marker_key'] = key;
			markers[key]['point']['formated_time_start'] = formated_times.formated_time_start;
			markers[key]['point']['formated_time_end'] = formated_times.formated_time_end;

			_private.marker_events(markers[key]);

		},

		marker_events: function (marker){
			marker.addListener('click', function() {
				_private.render_infowindow(marker);
				_private.select_task(marker);
			});
		},

		render_infowindow:function(marker, save_open){

			var marker_key = $(config.ui.infowindow_container).find(config.ui.marker_key).val();

			if(marker.point.marker_key!=undefined && marker_key!=undefined && marker.point.marker_key == marker_key && infowindow.getMap() && (save_open==undefined || save_open==false))
				return false;

			if(infowindow.getMap() && (save_open==undefined || save_open==false))
				infowindow.close();

			var renderView = {template_id:config.templates.infowindow.task, view_container_id:config.views.infowindow, data:[marker.point] , helpers:public.helpers};
			Common.renderView(renderView);
			form = $(config.views.infowindow).html();

			infowindow.setContent(form);

			if(save_open==undefined || save_open==false)
				infowindow.open(map, marker);
		},

		select_task: function(marker){
			//selected_appointment
			//selected_appointment:'.selected-estimator-appointment-today',
			$(config.ui.appointment_today).removeClass(config.ui.selected_appointmentClassName);
			$(config.ui.appointment_today+'[data-estimator="'+marker.point.ass_id+'"]').addClass(config.ui.selected_appointmentClassName);

			if(!infowindow.getMap()){
				_private.render_infowindow(marker);
				map.panTo(marker.position);
			}
		},

		finished_step: function(){
			_private.set_block_values();
			_private.clear_errors();
			if($(config.ui.schedule_start_time)==undefined || $(config.ui.schedule_end_time)==undefined || $(config.events.scheduled_iterval_radio+':checked').lenght==0)
				return false;


			var appointment_type = $(config.ui.appointment_type).val();
			if(appointment_type=='-1')
			{
				$(config.ui.appointment_type+'-error').text('Task type is required');
				return false;
			}

			$(config.ui.appointment_modal).modal('hide');
		},

		set_block_values: function(){
			//Task type is required
			$(config.ui.appointment_type+'-error').text('');

			$(config.events.add_appointment).show();
			$(config.events.add_appointment_task).hide();

			var time_start = $(config.ui.schedule_start_time).val();
			var time_end = $(config.ui.schedule_end_time).val();
			assigned_user_id = $(config.events.task_author_radio).val();
			var appointment_type = $(config.ui.appointment_type).val();
			localStorage.appointment_type = appointment_type;

			selected_date = $(config.events.scheduled_iterval_radio+':checked').data("date");
			if(selected_date==undefined)
				return;

			let  reversedSelected_date = selected_date.split('-').reverse().join('-');
			var $datepickerDate = $('<input type="text" id="datepickerHidden">').datepicker({
				format: dateFormatJS
			}).datepicker(
				'setDate', new Date(reversedSelected_date+'T00:00')
			).datepicker('getFormattedDate');
			var username = $(config.events.selected_estimator_name).val().replace('&nbsp;', ' ');
			var client_sms = $(config.ui.client_sms).prop("checked");
			var client_email = $(config.ui.client_email).prop("checked");
			var estimator_sms = $(config.ui.estimator_sms).prop("checked");
			var estimator_email = $(config.ui.estimator_email).prop("checked");
			create_task_form = false;

			if(time_start==undefined || time_end==undefined || selected_date=='' || assigned_user_id==undefined || appointment_type=='-1'){
				$(config.events.scheduled_check).prop('checked', false).trigger('change');
				$(config.events.scheduled_check).parent().removeClass('active');
				return;
			}

			if(!parent_form || parent_form==undefined)
				return;

			parent_form.find(config.ui.scheduled_block_username).text(username);
			parent_form.find(config.ui.scheduled_block_date).text($datepickerDate);
			parent_form.find(config.ui.scheduled_block_start).text(time_start);
			parent_form.find(config.ui.scheduled_block_end).text(time_end);

			parent_form.find(config.ui.scheduled_input_task_category).val(appointment_type);
			parent_form.find(config.ui.scheduled_input_username).val(username);
			parent_form.find(config.ui.scheduled_input_user_id).val(assigned_user_id);
			parent_form.find(config.ui.scheduled_input_date).val($datepickerDate);
			// parent_form.find(config.ui.scheduled_input_date).val(selected_date);
			parent_form.find(config.ui.scheduled_input_start).val(time_start);
			parent_form.find(config.ui.scheduled_input_end).val(time_end);

			parent_form.find(config.ui.is_client_sms).val(client_sms);
			parent_form.find(config.ui.is_client_email).val(client_email);
			parent_form.find(config.ui.is_estimator_sms).val(estimator_sms);
			parent_form.find(config.ui.is_estimator_email).val(estimator_email);
			parent_form.find(config.ui.estimator_input).val(assigned_user_id);
			parent_form.find(config.ui.estimator_input).trigger('change');
			parent_form.find(config.ui.estimator_input).select2("enable", false);
			parent_form.find(config.ui.estimator_input).parent().find('.text-danger').remove();
			parent_form.find(config.ui.estimator_input).after('<div class="text-danger"><strong>To enable you need to delete the appointment</strong></div>')
			parent_form.find(config.ui.scheduled_block).show();
		},

		select_assigned:function(){
			if(!parent_form || parent_form==undefined)
				return;
			parent_form.find(config.events.scheduled_check).prop("checked");
			if($(config.events.scheduled_check).prop("checked")==false)
				return;

			assigned_user_id = $(config.events.task_author_radio).val();

			if(parseInt(assigned_user_id)==0){
				$(config.ui.select_schedule_date+' '+config.ui.task_author_id).val('');
			}

			$(config.ui.select_schedule_date+' '+config.ui.task_author_id).val(assigned_user_id);

			var callback = function(){
				$('#step2').show();
			}

			selected_date = $(config.events.scheduled_datepicker).datepicker('getFormattedDate');
			$(config.ui.select_schedule_date+' '+config.ui.task_date).val(selected_date);

		},

		clear_errors:function(){
			$(config.ui.appointment_type+'-error').text('');
			$(config.ui.appointment_error).text('');
			$('.schedule-duration-value .error').text('');
		},

		select_estimator:function(e){

			if(e.target.className.indexOf("get-information-table")!=-1 || e.target.className.indexOf("information-table")!=-1){
				return false;
			}

			$(config.events.estimator_appointment).removeClass("selected-estimator");
			$(this).addClass("selected-estimator");

			id = $(this).data('estimator');
			assigned_user_id = $(config.events.task_author_radio).val();

			username = $(this).data('estimator-name');
			$(config.events.selected_estimator_name).val(username);
			$(config.events.task_author_radio).val(id).trigger('change');
		},

		render_recomendations:function(response){
			var renderView = {template_id:'#recomendations-template', empty_template_id:'#recomendations-template-empty', view_container_id:'#recomendations-result', data:response.data.appointments.appointment_recomendations, helpers:public.helpers};
			Common.renderView(renderView);
			Common.init_popover();
			Common.init_scroll();
			$('.slimScrollDiv').css({'position':''});
			setTimeout(function () {
				_private.activate_user_time();
				$('#recomendations-result .panel-collapse').on('shown.bs.collapse', function () {
					$('#recomendations-result').scrollTop($('#recomendations-result').scrollTop() + $(this).prev().position().top - 19);
				});

				if($(config.ui.scheduled_input_task_category).val()) {
					$(config.ui.appointment_type).val($(config.ui.scheduled_input_task_category).val());
				}

				$(config.ui.client_sms).prop('checked', $(config.ui.is_client_sms).val() === 'true').trigger('change');
				$(config.ui.client_email).prop('checked', $(config.ui.is_client_email).val() === 'true').trigger('change');
				$(config.ui.estimator_sms).prop('checked', $(config.ui.is_estimator_sms).val() === 'true').trigger('change');
				$(config.ui.estimator_email).prop('checked', $(config.ui.is_estimator_email).val() === 'true').trigger('change');

				if(
					$(config.ui.is_client_sms).val() !== 'true' &&
					$(config.ui.is_client_email).val() !== 'true' &&
					$(config.ui.is_estimator_sms).val() !== 'true' &&
					$(config.ui.is_estimator_email).val() !== 'true'
				) {
					$(config.ui.appointment_type).change();
				}
			},0);
			
			/*
			var items = $('#recomendations-result .panel');
			items.sort(function(a, b){
			    return +$(b).data('total') - +$(a).data('total');
			});
			items.appendTo('#recomendations-result');
			*/
		},

		create_appointment_modal:function(){
			_private.clear_errors();

			parent_form = $(this).closest('form');
			create_task_form = true;
			_private.get_appointment_modal();
		},

		add_appointment_task:function(){
			_private.clear_errors();

			var time_start = $(config.ui.schedule_start_time).val();
			var time_end = $(config.ui.schedule_end_time).val();
			assigned_user_id = $(config.events.task_author_radio).val();
			var appointment_type = $(config.ui.appointment_type).val();
			var appointment_lead = $(config.ui.appointment_lead).val();

			var client_lat = parseFloat($(config.ui.client_lat).val());
			var client_lon = parseFloat($(config.ui.client_lon).val());

			var client_sms = $(config.ui.client_sms).prop("checked");
			var client_email = $(config.ui.client_email).prop("checked");
			var estimator_sms = $(config.ui.estimator_sms).prop("checked");
			var estimator_email = $(config.ui.estimator_email).prop("checked");

			if(assigned_user_id=='' || time_start==undefined || time_end==undefined){
				$(config.ui.appointment_error).text('Please, select estimator and time');
				return false;
			}
			if(appointment_type=='-1')
			{
				$(config.ui.appointment_type+'-error').text('Task type is required');
				return false;
			}

			selected_date = $(config.events.scheduled_iterval_radio+':checked').data("date");
			var username = $(config.events.selected_estimator_name).val();
			var task_client_id = $(config.ui.client_id).val();
			var data = {
				'task_client_id':task_client_id,
				'new_task_assigned_user':assigned_user_id,
				'new_task_cat':appointment_type,
				'new_task_lead':appointment_lead,
				'scheduled':1,
				'from':selected_date,
				'start_time':time_start,
				'end_time':time_end,
				'new_task_status':'new',

				'new_task_address': $(config.ui.client_address).val(),
				'new_task_city': $(config.ui.client_city).val(),
				'new_task_state': $(config.ui.client_state).val(),
				'new_task_zip': $(config.ui.client_zip).val(),
				'new_task_country': $(config.ui.client_country).val(),

				'new_task_lat': client_lat,
				'new_task_lon': client_lon,

				'is_client_sms':client_sms,
				'is_client_email':client_email,
				'is_estimator_sms':estimator_sms,
				'is_estimator_email':estimator_email,
			};

			var f = document.createElement("form");
			f.setAttribute('method',"post");
			f.setAttribute('action',"/tasks/create_task");
			f.setAttribute('class', 'hidden');
			f.setAttribute('id', 'create-task-dinamic');

			$.each(data, function(key, value){
				var i = document.createElement("input");
				i.setAttribute('type',"hidden");
				i.setAttribute('name', key);
				i.setAttribute('value', value);
				f.appendChild(i);
			});

			var s = document.createElement("input");
			s.setAttribute('type',"submit");
			s.setAttribute('value',"Submit");
			f.appendChild(s);
			document.getElementsByTagName('body')[0].appendChild(f);
			$(this).addClass("disabled");
			$("#create-task-dinamic").trigger("submit");

		},

		another_address_checkbox:function(){
			var id = $(this).data('id');
			if($(this).prop('checked')==false){
				$(id).find('input').attr('disabled', 'disabled');
			}
			else{
				$(id).find('input').removeAttr('disabled');
			}

		},
		activate_user_time: function() {
			if($('[name="scheduled_date"]').val()) {
				$(config.events.estimator_appointment + '[data-estimator="' + $('[name="scheduled_user_id"]').val() + '"]')
					.parent()
					.find('.schedule-duration-value input[name="schedule_interval_start"][data-date="' + moment($('[name="scheduled_date"]').val(), MOMENT_DATE_FORMAT).format('DD-MM-YYYY') + '"]')
						.parents('.panel:first').find(config.events.estimator_appointment + ' .accordion-toggle')
					.click();
				$(config.events.estimator_appointment + '[data-estimator="' + $('[name="scheduled_user_id"]').val() + '"]').parent()
					.find('.schedule-duration-value input[name="schedule_interval_start"][data-date="' + moment($('[name="scheduled_date"]').val(), MOMENT_DATE_FORMAT)
						.format('DD-MM-YYYY') + '"][value="' + $('[name="scheduled_start_time"]').val() + '"]').parent().prev().addClass('active');
				$(config.events.estimator_appointment + '[data-estimator="' + $('[name="scheduled_user_id"]').val() + '"]').parent()
					.find('.schedule-duration-value input[name="schedule_interval_start"][data-date="' + moment($('[name="scheduled_date"]').val(), MOMENT_DATE_FORMAT)
						.format('DD-MM-YYYY') + '"][value="' + $('[name="scheduled_start_time"]').val() + '"]').parent().prev().find('input[name="time_interval"]').prop('checked', true);
				$(config.events.estimator_appointment + '[data-estimator="' + $('[name="scheduled_user_id"]').val() + '"]').parent()
					.find('.schedule-duration-value input[name="schedule_interval_start"][data-date="' + moment($('[name="scheduled_date"]').val(), MOMENT_DATE_FORMAT)
						.format('DD-MM-YYYY') + '"][value="' + $('[name="scheduled_start_time"]').val() + '"]').parent().addClass('in').removeClass('hidden');
			}
		},

		change_appointment_estimator: function(){
			if(infowindow.getMap()){
				infowindow.close();
			}

			let estimator_id = $(this).val();

			if(estimator_id == 0) {
				$('[data-filter_estimator_id]').show();
				$('.estimator-appointment-today[data-estimator]').show();
				$.each(markers, function (key, value) { value.setVisible(true); });
			}
			else{
				$.each($('[data-filter_estimator_id]'), function (key, val) {
					if(estimator_id != $(val).data('filter_estimator_id')) {
						$(val).hide();
					}else{
						$(val).show();
					}
				});
				$.each($('.estimator-appointment-today'), function (key, val) {
					if(estimator_id != $(val).data('estimator')) {
						$(val).hide();
					}else{
						$(val).show();
					}
				});

				$.each(markers, function (key, value) {
					value.setVisible(parseInt(value.point.ass_id) == parseInt(estimator_id));
				});
			}

		},

		getTaskPosition: function () {
			var position = {lat:'', lon:''};
			client_lat = $(config.ui.client_lat).val();
			client_lon = $(config.ui.client_lon).val();

			if(client_lat && client_lon){
				position = {lat:parseFloat(client_lat), lon:parseFloat(client_lon)};
			}

			var new_client_lat = $(config.ui.appointment_map_lat).val();
			var new_client_lon = $(config.ui.appointment_map_lon).val();
			if(new_client_lat && new_client_lon){
				position = {lat:new_client_lat, lon:new_client_lon};
			}

			var lead_data = $(config.ui.appointment_lead+' option:selected').data();
			if(lead_data.lat && lead_data.lon){
				position = {lat:lead_data.lat, lon:lead_data.lon};
			}

			var new_lead_lat = $(config.ui.lead_details_modal+' '+config.ui.appointment_map_lat_main).val();
			var new_lead_lon = $(config.ui.lead_details_modal+' '+config.ui.appointment_map_lon_main).val();
			if(new_lead_lat && new_lead_lon){
				position = {lat:new_lead_lat, lon:new_lead_lon};
			}

			return position;
		},

		formatedTaskTime: function(point){
			var result = {interval:'', formated_time_start:'', formated_time_end:''};

			if(point.task_start && point.task_end) {
				result.formated_time_start = point.task_start.substr(0, 5);
				result.formated_time_end = point.task_end.substr(0, 5);

				if(timeFormat == '12') {
					result.formated_time_start = moment(point.task_start.substr(0, 5), 'HH:mm').format('h:mm A');
					result.formated_time_end = moment(point.task_end.substr(0, 5), 'HH:mm').format('h:mm A');
				}
			}
			result.interval = result.formated_time_start+' - '+result.formated_time_end;
			return result;
		},

		/*search_client: function () {
			setTimeout(function() {
				_private.ajax_search_client($(config.ui.client_name).val());
			}, 500);
		},*/
		search_by_contact_name: function () {
			setTimeout(function() {
				_private.ajax_search_client($(config.ui.contact_name).val());
			}, 500);
		},
		search_client_by_contact_phone: function () {
			let phone_number = $(this).val();
			phone_number = phone_number.replace(/[\_\Ext.\(\)\-\/\s\\*]/g,'');
			_private.ajax_search_client(phone_number.trim());
		},
		search_client_by_contact_email: function () {
			if(_private.validateEmail($(this).val()) || $(this).val() == '')
				_private.ajax_search_client($(this).val());
		},
		search_client_by_contact_address: function () {
			setTimeout(function() {
				_private.ajax_search_client($(config.ui.client_new_address).val());
			}, 500);
		},
		validateEmail(email) {
			let re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
			return re.test(email);
		},
		ajax_search_client: function (search_query) {
			if(search_query && search_query.trim() !== '') {
				$.ajax({
					url: baseUrl + 'clients/search_clients',
					data: {search_query: search_query},
					dataType: 'json',
					global: false,
					method: 'POST',
					success: function (resp) {
						if (resp.status === 'success' && resp.count > 0) {
							let result = JSON.parse(resp.result);
							/*$('#check-client').parent().closest('div').show();*/
							$('.showSearch').addClass('blink');
							$('.showSearch').removeClass('hide');
							$('#check-client').html(result.html);
							$('#check-client').parent().closest('div').css('overflow', 'unset');
							$('#check-client').css('height', '250px').css('overflow', 'auto');
						} else {
							$('#check-client').parent().closest('div').hide();
							$('.showSearch').removeClass('blink');
							$('.showSearch').addClass('hide');
						}
						return false;
					}
				});
			} else {
				$('.showSearch').removeClass('blink');
				$('#check-client').parent().closest('div').hide();
				$('.showSearch').addClass('hide');
			}

		},

		project_edit: function () {
			let id = $(this).data('id');
			if(id) {
				$.ajax({
					method: "POST",
					url: 'tree_inventory/edit_project',
					dataType: 'JSON',
					data: {'tis_id': id},
					global: false
				}).done(function (msg) {
					if (msg.project !== undefined) {
						let project = msg.project;
						$('#new_project [name="tis_name"]').val(project.tis_name);
						$('#new_project [name="tis_address"]').val(project.tis_address);
						$('#new_project [name="tis_city"]').val(project.tis_city);
						$('#new_project [name="tis_state"]').val(project.tis_state);
						$('#new_project [name="tis_zip"]').val(project.tis_zip);
						$('#new_project [name="tis_country"]').val(project.tis_country);
						$('#new_project [name="tis_lat"]').val(project.tis_lat);
						$('#new_project [name="tis_lng"]').val(project.tis_lng);
						$('#new_project [name="tis_id"]').val(project.tis_id);
						$('#new_project .project-title').text("Edit project");
						$('#new_project').modal('show');
					}
				});
			}
		},

		project_create: function () {
			$('#new_project [name="tis_name"]').val('');
			$('#new_project [name="tis_address"]').val($(config.ui.client_address).val());
			$('#new_project [name="tis_city"]').val($(config.ui.client_city).val());
			$('#new_project [name="tis_state"]').val($(config.ui.client_state).val());
			$('#new_project [name="tis_zip"]').val($(config.ui.client_zip).val());
			$('#new_project [name="tis_country"]').val($(config.ui.client_country).val());
			$('#new_project [name="tis_lat"]').val($(config.ui.client_lat).val());
			$('#new_project [name="tis_lng"]').val($(config.ui.client_lon).val());
			$('#new_project [name="tis_id"]').val('');
			$('#new_project .project-title').text("Create new project");
		},

		project_delete: function () {
			// let result = confirm("Are you sure?");
			// let id = $(this).data('id');
			// let tr = $(this).closest('tr');
			// if(id && result) {
			// 	$.ajax({
			// 		method: "POST",
			// 		url: 'tree_inventory/delete_project',
			// 		dataType: 'JSON',
			// 		data: {'tis_id': id}
			// 	}).done(function (msg) {
			// 		if(msg.status == 'ok') {
			// 			successMessage('');
			// 			tr.remove();
			// 		}
			// 	});
			// }
		},

		update_double_positions: function(doubles){
			var lat = 0.0000250;
			var lon = -0.0000302;

			var start = 0, i = 0, j = 0;
			var origin_lat = 0, origin_lon = 0;

			doubles.forEach(function (double_array) {
				sqrt = Math.ceil(Math.sqrt(double_array.length));
				start = i = j = Math.ceil(sqrt / 2);

				double_array.forEach(function (double_point, key) {
					origin_lat = parseFloat(markers[double_point]['point'].task_latitude);
					origin_lon = parseFloat(markers[double_point]['point'].task_longitude);

					t_lat = origin_lat + (i * lat);
					t_lon = origin_lon + (j * lon);

					position = new google.maps.LatLng(t_lat, t_lon);

					markers[double_point].setPosition(position);

					i--;
					if (i == -(start - 1)) {
						i = start;
						j--;
					}
				});
			});
		}
	}

	var selected_date;
	var public = {

		init:function(){
			$(document).ready(function(){
			  	public.events();
			  	_private.init();
			});
		},
		helpers: {
			format_date: function (date, format) {
				return moment(date, format).format(MOMENT_DATE_FORMAT)
			},
		},
		events:function(){
			$(config.events.project_edit).click(_private.project_edit);
			$(config.events.project_create).click(_private.project_create);
			$(config.events.project_delete).click(_private.project_delete);
			$(config.events.scheduled_check).click(_private.scheduled_check);
			$(config.events.remove_schedule).click(_private.remove_schedule);
			/*$(config.events.client_name).change(_private.search_client);*/
			$(config.events.contact_name).change(_private.search_by_contact_name);
			$(config.events.client_new_address).change(_private.search_client_by_contact_address);
			$(config.events.client_contact_phone).change(_private.search_client_by_contact_phone);
			$(config.events.client_contact_email).change(_private.search_client_by_contact_email);

			$(document).delegate(config.events.add_appointment, 'click', _private.finished_step);

			$(config.events.another_address_checkbox).change(_private.another_address_checkbox);

			$(document).delegate(config.events.scheduled_iterval_radio, 'change', function(){
				_private.clear_errors();
				$('.schedule-duration-value').removeClass('in');
				$('.schedule-duration-value input').attr('disabled', 'disabled');

				$(this).parent().next().addClass("in");
				$(this).parent().next().find('input').removeAttr('disabled');
			});

			$(document).delegate(config.ui.schedule_start_time+', '+config.ui.schedule_end_time, 'change, keyup', function(){
				_private.clear_errors();
				$(config.events.scheduled_next).removeAttr('disabled');
			});

			$(document).delegate('#init_appointment_modal, .scheduled-block-info', 'click', function(){
				parent_form = $(this).closest('form');
				_private.get_appointment_modal();
			});

			$(document).delegate('.scheduledLead', 'change', function(){
				var lead_id = $(this).data('lead_id');
					scheduledProcess = true;
					result = 1;
					if($(this).is(':checked'))
						$(this).next().addClass('checked');
					else
					{
						$(this).next().removeClass('checked');
						result = 0;
					}
					$.post(baseUrl + 'clients/get_appointment_modal',{scheduled:result,lead_id:lead_id},function(response){
						if(response.status!='ok')
							return;

						public.init_appointment_modal(response)
					},'json');
				// parent_form = $(this).closest('form');
				// _private.get_appointment_modal();
			});

			$(config.events.create_appointment_modal).click(_private.create_appointment_modal);
			$(document).delegate(config.events.add_appointment_task, 'click', _private.add_appointment_task);


			$(document).delegate(config.ui.appointment_type, 'change', function(){
				$(config.ui.appointment_type+'-error').text('');
				if($(this).val()==7)
					$(config.ui.notification_checkbox).prop("checked", true);
				else
					$(config.ui.notification_checkbox).prop("checked", false);

				$(config.ui.notification_checkbox).trigger('change');
			});

			$(document).delegate(config.events.estimator_appointment, 'click', _private.select_estimator);
			$(document).delegate(config.events.appointment_estimator, 'change', _private.change_appointment_estimator);

			$(document).on('change', '#reffered', function(){
		        var val = $(this).val();
		        var obj = $(this);
				let refElement = $('#reff_id');
				refElement.select2('val', '');

				if($('option:selected', this).data('is_user_active') == 1 || $('option:selected', this).data('is_client_active') == 1) {
					refElement.select2("enable", true);
		            $('.other_comment').attr('disabled', 'disabled');
		            $('.other_comment').css('display', 'none');
		            $(this).next().css('display', 'inline-block');
					if (referer.id !== '' && referer.name !== '') {
						refElement.select2('data',{
							'id': referer.id,
							'text': referer.name,
						});
					}

					referer.id = '';
					referer.name = '';
		        }
		        else if(val == 'other')
		        {
					refElement.select2("enable", false);
		            $('.other_comment').removeAttr('disabled');
		            $('.other_comment').css('display', 'inline-block');
		            $(this).next().css('display', 'none');
		        }
		        else
		        {
					refElement.select2("enable", false);
		            $('.other_comment').attr('disabled', 'disabled');
		            $('.other_comment').css('display', 'none');
		            $(this).next().css('display', 'none');
		        }
		        return false;
		    });


			$(document).delegate(config.ui.appointment_today, 'click', function () {
				marker_key = $(this).data('marker_key');

				if(infowindow.getMap()){
					var window_marker_key = $(config.ui.infowindow_container).find(config.ui.marker_key).val();
					if(window_marker_key!=marker_key || $(config.ui.infowindow_container).data('id') != $(this).data('id')){
						infowindow.close();
					}
				}
				_private.select_task(markers[marker_key]);
			});
		},

		init_appointment_modal:function(response){

			if(response.status!='ok')
				return;

			$(config.ui.appointment_modal+' .modal-body').html(response.data.view);

			public.init_datapicker();

			$(config.events.task_author_radio).change(_private.select_assigned);


			_private.render_recomendations(response);
			var renderView = {template_id:config.ui.appointment_type_template, view_container_id:config.ui.appointment_type, data:response.data.appointment_types};
			Common.renderView(renderView);

			if(response.data.leads !== undefined && $('#new_lead').is(":visible") === false) {
				var renderView = {
					template_id:config.ui.appointment_lead_template,
					view_container_id:config.ui.appointment_lead,
					data:response.data.leads, render_method:'append'
				};
				Common.renderView(renderView);
				if(response.data.lead_id !== undefined && response.data.lead_id.length > 0 && $(config.ui.appointment_lead).has('[value="' + response.data.lead_id + '"]').length > 0) {
					$(config.ui.appointment_lead).val(response.data.lead_id);
				}
			}
			else{
				$(config.ui.appointment_lead).hide();
			}
			var renderView = {template_id:config.ui.appointment_estimator_template, view_container_id:config.ui.appointment_estimator, data:response.data.appointments.estimators, render_method:'append'};
			Common.renderView(renderView);

			_private.init_map();

			if(create_task_form==true){
				$(config.events.add_appointment).hide();
				$(config.events.add_appointment_task).show();
			}

			$('[name="notify_client_sms"]').prop('checked', $('[name="is_client_sms"]').val() === 'true').trigger('change');
			$('[name="notify_client_email"]').prop('checked', $('[name="is_client_email"]').val() === 'true').trigger('change');
			$('[name="notify_estimator_sms"]').prop('checked', $('[name="is_estimator_sms"]').val() === 'true').trigger('change');
			$('[name="notify_estimator_email"]').prop('checked', $('[name="is_estimator_email"]').val() === 'true').trigger('change');

			$(config.ui.appointment_modal).modal();
			//$(config.ui.appointment_type).trigger('change');
		},

		init_datapicker:function(){

			var dates_calendar = $(config.events.scheduled_datepicker).datepicker({
				format: 'dd-mm-yyyy',
				todayHighlight:true,
				showOn: "button",
  				buttonText: '<i class="fa fa-calendar"></i>',
			});

			dates_calendar.on('changeDate', function(e) {

				if(selected_date==$(config.events.scheduled_datepicker).datepicker('getFormattedDate'))
					return false;
				if($(config.ui.appointment_estimator).val() === '0')
					$(config.ui.select_schedule_date + ' ' + config.ui.task_author_id).val('');

				let date = $(config.events.scheduled_datepicker).datepicker('getDate');
				let formatted_date = $.datepicker.formatDate($('#schedule-appointment-modal #php-variable').val().replace('yyyy', 'yy'), date);
				$(config.ui.select_schedule_date+' '+config.ui.task_date).val(formatted_date);
				// $(config.ui.select_schedule_date+' '+config.ui.task_date).val($(config.events.scheduled_datepicker).datepicker('getFormattedDate'));
				selected_date = $(config.events.scheduled_datepicker).datepicker('getFormattedDate');

				date_points[$(config.events.scheduled_datepicker).datepicker('getFormattedDate')] = [];
				public.get_schedule_intervals($(config.events.scheduled_datepicker).datepicker('getFormattedDate'), {});
			});
		},

		set_free_schedule_times:function(response){

			if(response.data.appointments.schedule_appointments!=undefined)
				window.appointments = response.data.appointments.schedule_appointments;//JSON.stringify(response.data.appointments.schedule_appointments);

			if(response.data.appointments.appointment_lat && response.data.appointments.appointment_lon){
				$(config.ui.appointments_map).data('origin_lat', response.data.appointments.appointment_lat);
				$(config.ui.appointments_map).data('origin_lon', response.data.appointments.appointment_lon);
			}
			else{
				$(config.ui.appointments_map).data('origin_lat', response.data.appointments.origin_lat);
				$(config.ui.appointments_map).data('origin_lon', response.data.appointments.origin_lon);
			}

			$(config.ui.select_schedule_date+' '+config.ui.task_date).val(selected_date);
			_private.setAppointments();
			_private.render_recomendations(response);
			$(config.ui.appointment_estimator).trigger('change');
		},

		get_schedule_intervals: function(selected_date, callback){
			$(config.ui.select_schedule_date).trigger('submit');
		},

		project_callback: function (callback) {
			if(callback.status === 'ok') {
				$('#new_project').modal('toggle');
				successMessage('');
				location.reload();
			}
		}

	}

	public.init();
	return public;
}();

/*
$.each($('.dropzone-lead'), function(key, val){
	initDropzone($(val));
});
*/
var myDropzone = [];

function initDropzone(element = false)
{
	var id = $(element).closest('form').find('[name="lead_id"]');
	let error = '';
	if(id.length > 0){
		id = id.val();
	} else {
		id = 0;
	}

	var client_id = null;
	if($(element).closest('form').find('[name="client_id"]').length > 0){
		client_id = $(element).closest('form').find('[name="client_id"]').val();
	}

	if(!element)
		element = '.dropzone';

	Dropzone.autoDiscover = false;

	myDropzone[id] = new Dropzone(element.get(0), {
		acceptedFiles: 'image/*,application/pdf',
		accept: function(file, done) {
			var thumbnail = $('.dropzone .dz-preview.dz-file-preview .dz-image:last img');
			switch (file.type) {
			  case 'application/pdf':
				thumbnail.attr('src', baseUrl + '../../assets/vendors/notebook/images/pdf.png');
				break;
			}

			done();
		  },
		url: baseUrl + 'leads/preupload',
		params: function (files, xhr){
			var files_uuids = [];
			$.each(files, function (key, val) {
				files_uuids.push(val.upload.uuid);
			});
			return {
				files_uuids: files_uuids,
				id: id,
				client_id: client_id
			}
		},
		uploadMultiple: true,
		parallelUploads: 5,
		paramName: 'files',
		thumbnailWidth: 94,
		thumbnailHeight: 94,
		timeout: 300000,
		init: function() {

		},
		addRemoveLinks: true,
		ignoreHiddenFiles: true,
		autoProcessQueue: true,
		removedfile: function(a, b) {
			var thingId = $(a.previewElement).closest('form').find('[name="lead_id"]');
			if(thingId.length > 0){
				thingId = thingId.val();
			} else {
				thingId = 0;
			}

			if(a.filepath !== undefined) {
				$('input[name="pre_uploaded_files[' + thingId + '][]"][value="' + a.filepath + '"]').remove();
				$(a.previewElement).remove();
			} else if(a.size !== undefined) {
				$('input[name="pre_uploaded_files[' + thingId + '][]"][data-size="' + a.size + '"]:first').remove();
				$(a.previewElement).remove();
			} else {
				$('input[name="pre_uploaded_files[' + thingId + '][]"][data-name="' + a.name + '"]:first').remove();
				$(a.previewElement).remove();
			}

			return true;
		},
			complete: function(a) {
			var thingId = $(a.previewElement).closest('form').find('[name="lead_id"]');
			if(thingId.length > 0){
				thingId = thingId.val();
			} else {
				thingId = 0;
			}


			let response = a.xhr !== undefined ? $.parseJSON(a.xhr.response) : false;
			var needSync = false;
			var c = this;

			if(response) {
				var currentUploaded = response.data.filter(function(el) {
					if(el.error !== undefined)
						error = el.error;
					return el.uuid ===  a.upload.uuid;
				})[0];

				if(error && !currentUploaded) {
					errorMessage(error);
					return;
				}

				let thumbnailImageContainer = $(a.previewElement).find('.dz-image');
				let thumbnailImage = $(thumbnailImageContainer).find('img');

				if (currentUploaded.type.indexOf('image') === -1) {
					let htmlString = `
									<a href="${currentUploaded.url}" target="_blank" data-lead_file="${currentUploaded.name}">
											<img data-dz-thumbnail="" src="${location.origin + '/../../assets/vendors/notebook/images/pdf.png'}">
									</a>`;

					thumbnailImageContainer.html(htmlString);
					} else {
						let htmlString = `
										<a href="${currentUploaded.url}" data-lightbox="${'leadfile-' + currentUploaded.id}" data-lead_file="${currentUploaded.name}" style="cursor: pointer">
												${thumbnailImage.get(0).outerHTML}
										</a>`;

						thumbnailImageContainer.html(htmlString);
				}

				if(!$(a.previewElement).closest('form').find('input[value="' + currentUploaded.filepath + '"]').length) {
					if(!$('[name="id[' + id + ']"]').length) {
						$(a.previewElement).closest('form').append('<input type="hidden" data-lead_id="' + thingId + '" name="pre_uploaded_files[' + thingId + '][]" value="' + currentUploaded.filepath + '" data-uuid="' + currentUploaded.uuid + '" data-size="' + currentUploaded.size + '" data-url="' + currentUploaded.url + '" data-type="' + currentUploaded.type + '" data-name="' + currentUploaded.name + '">');
					}
				}
				const obj = id + "*#*" +  $.trim(currentUploaded.name) +  "*#*" + client_id + "#*#" ;
				if(!localStorage.remEl){
					localStorage.remEl = obj;
				}else{
					localStorage.remEl = localStorage.remEl + obj;
				}
				$('a.domfile[data-uuid="' + currentUploaded.uuid + '"]').attr('onclick', 'remove_lead_file(' + id + ', "' + $.trim(currentUploaded.name) + '", '+client_id+')').attr('data-name', currentUploaded.name);
			}
		},
		processing: function(a) {
			if (a.previewElement && (a.previewElement.classList.add("dz-processing"),
				a._removeLink))
				return a._removeLink.innerHTML = this.options.dictRemoveFile
		},
		queuecomplete: function(a,b) {
			$('input[type="submit"]').removeClass('disabled').removeAttr('disabled');
			if(error){
				clearErrorFileFromDropzone();
				error = '';
			}
		},
		addedfile: function(a) {
				$('input[type="submit"]').addClass('disabled').attr('disabled', 'disabled');
				var c = this;
				var b = Dropzone;
				if (this.element === this.previewsContainer && this.element.classList.add("dz-started"),
				this.previewsContainer) {
					a.previewElement = b.createElement(this.options.previewTemplate.trim()),
					a.previewTemplate = a.previewElement,
					this.previewsContainer.appendChild(a.previewElement);
					for (var d = a.previewElement.querySelectorAll("[data-dz-name]"), e = 0, d = d; ; ) {
						var f;
						if (e >= d.length)
							break;
						f = d[e++];
						var g = f;
						g.textContent = a.name
					}
					for (var h = a.previewElement.querySelectorAll("[data-dz-size]"), i = 0, h = h; !(i >= h.length); )
						g = h[i++],
						g.innerHTML = this.filesize(a.size);
					var tpl = '<a class="dz-remove" href="javascript:undefined;" onclick="remove_lead_file('+ id + ", '" + $.trim(a.name) + "', "+client_id+")" + '" data-name="'+ a.name +'" data-dz-remove>' + this.options.dictRemoveFile + "</a>";
					if(a instanceof File)
						tpl = '<a class="dz-remove domfile" href="javascript:undefined;" data-uuid="' + $.trim(a.upload.uuid) + '" data-dz-remove>' + this.options.dictRemoveFile + "</a>";
					this.options.addRemoveLinks && (a._removeLink = b.createElement(tpl),
					a.previewElement.appendChild(a._removeLink));
					for (var j = function(d) {
						if (a.status === b.SUCCESS) {
							return true;
						}
						return d.preventDefault(),
						d.stopPropagation(),
						a.status === b.UPLOADING ? b.confirm(c.options.dictCancelUploadConfirmation, function() {
							return c.removeFile(a)
						}) : c.options.dictRemoveFileConfirmation ? b.confirm(c.options.dictRemoveFileConfirmation, function() {
							return c.removeFile(a)
						}) : c.removeFile(a)
					}, k = a.previewElement.querySelectorAll("[data-dz-remove]"), l = 0, k = k; ; ) {
						var m;
						if (l >= k.length)
							break;
						m = k[l++];
						m.addEventListener("click", j)
					}
				}
			},
		//autoQueue: false
	});

}

function warningMessage(msg) {
	$('body').append('<div class="alert alert-warning alert-message" id="errorMessage" style="display:none;"><button type="button" class="close m-l-sm" data-dismiss="alert">×</button><strong>' + msg + '</strong></div>');
	$('#errorMessage').fadeIn();
	setTimeout(function () {
		$('#errorMessage').fadeOut(function () {
			$('#errorMessage').remove();
		});
	}, 10000);
}

$(document).ready(function() {

	Dropzone.autoDiscover = false;

	window.initDropzone = initDropzone;

	if($("#new_lead .dropzone-lead").length!=0)
		window.initDropzone($("#new_lead .dropzone-lead"));

	if($("#reffered").length!=0)
		$('#reffered').change();
	/*
	if($("#formData .dropzone-lead").length!=0)
		window.initDropzone($("#formData .dropzone-lead"));
	*/

	$(document).on('click', 'a.client_contact', function() {
		primaryKey = $(this).attr('data-pk');
	});

	$(document).on('click', '[name="manual_approve"]', function() {
		let el = $(this),
			approve_status = $(this).next('[name="cc_approve_status"]').attr('data-approve-status');

		let new_approve_status = approve_status == 0 ? 1 : 0;

		if (new_approve_status == 0) {
			$(el).text('Mark Email as correct')
				.removeClass('btn-danger')
				.addClass('btn-success');
		}

		if (new_approve_status == 1) {
			$(el).text('Mark Email as incorrect')
				.removeClass('btn-success')
				.addClass('btn-danger');
		}

		$(this).next('[name="cc_approve_status"]').attr('data-approve-status', new_approve_status);
	});

	$('.showSearch').on('click', function(){
		if($(this).hasClass('blink'))
			$('#check-client').parent().closest('div').slideToggle('right');
		else {
			$('#check-client').parent().closest('div').hide();
			$('.showSearch').addClass('hide');
		}
	})
});

function clearErrorFileFromDropzone(){
	let files = $('.dz-remove.domfile');
	if(files !== undefined && files.length){
		$.each(files, function (key, val) {
			if($(val).data('name') === undefined)
				$(val).closest('.dz-preview').remove();
		});
	}
}
